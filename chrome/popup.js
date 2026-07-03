const API_BASE_URL = 'http://localhost:8088';
const STORAGE_SELECTED_ACCOUNT_ID = 'selected_account_id';

const statusElement = document.getElementById('status');
const productListElement = document.getElementById('productList');
const productTemplate = document.getElementById('productTemplate');
const refreshButton = document.getElementById('refreshButton');
const accountSelect = document.getElementById('accountSelect');

let selectedAccountId = null;

refreshButton.addEventListener('click', () => {
  initializePopup();
});

accountSelect.addEventListener('change', async () => {
  selectedAccountId = accountSelect.value ? Number(accountSelect.value) : null;
  productListElement.replaceChildren();

  if (!selectedAccountId) {
    await chrome.storage.local.remove(STORAGE_SELECTED_ACCOUNT_ID);
    setStatus('Выберите аккаунт.');
    return;
  }

  await chrome.storage.local.set({
    [STORAGE_SELECTED_ACCOUNT_ID]: selectedAccountId,
  });

  await loadProducts();
});

initializePopup();

async function initializePopup() {
  setStatus('Загрузка аккаунтов...');
  productListElement.replaceChildren();

  try {
    const accounts = await loadAccounts();

    if (accounts.length === 0) {
      accountSelect.disabled = true;
      selectedAccountId = null;
      await chrome.storage.local.remove(STORAGE_SELECTED_ACCOUNT_ID);
      setStatus('Аккаунтов пока нет. Создайте аккаунт в панели.');
      return;
    }

    accountSelect.disabled = false;
    renderAccounts(accounts);

    const savedAccountId = await getSavedAccountId();
    const savedAccountExists = accounts.some((account) => account.id === savedAccountId);

    if (!savedAccountExists) {
      selectedAccountId = null;
      accountSelect.value = '';
      await chrome.storage.local.remove(STORAGE_SELECTED_ACCOUNT_ID);
      setStatus(savedAccountId ? 'Сохраненный аккаунт не найден. Выберите аккаунт.' : 'Выберите аккаунт.');
      return;
    }

    selectedAccountId = savedAccountId;
    accountSelect.value = String(savedAccountId);
    await loadProducts();
  } catch (error) {
    setStatus(`Не удалось загрузить аккаунты: ${error.message}`);
  }
}

async function loadAccounts() {
  const response = await fetch(`${API_BASE_URL}/extension/accounts`, {
    headers: {
      Accept: 'application/json',
    },
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }

  const payload = await response.json();

  return payload.data ?? [];
}

function renderAccounts(accounts) {
  accountSelect.replaceChildren(new Option('Выберите аккаунт', ''));

  for (const account of accounts) {
    accountSelect.append(new Option(account.name, String(account.id)));
  }
}

async function getSavedAccountId() {
  const stored = await chrome.storage.local.get(STORAGE_SELECTED_ACCOUNT_ID);
  const accountId = Number(stored[STORAGE_SELECTED_ACCOUNT_ID]);

  return Number.isInteger(accountId) && accountId > 0 ? accountId : null;
}

async function loadProducts() {
  if (!selectedAccountId) {
    setStatus('Выберите аккаунт.');
    productListElement.replaceChildren();
    return;
  }

  setStatus('Загрузка...');
  productListElement.replaceChildren();

  try {
    const url = new URL(`${API_BASE_URL}/extension/products`);
    url.searchParams.set('account_id', String(selectedAccountId));

    const response = await fetch(url, {
      headers: {
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const payload = await response.json();
    const products = payload.data ?? [];

    if (products.length === 0) {
      setStatus('Для выбранного аккаунта продуктов пока нет.');
      return;
    }

    setStatus('');
    renderProducts(products);
  } catch (error) {
    setStatus(`Не удалось загрузить продукты: ${error.message}`);
  }
}

function renderProducts(products) {
  const fragment = document.createDocumentFragment();

  for (const product of products) {
    const item = productTemplate.content.cloneNode(true);
    const title = item.querySelector('.product-title');
    const meta = item.querySelector('.product-meta');
    const button = item.querySelector('.publish-button');

    title.textContent = product.title_ru || product.title_en || `Продукт #${product.id}`;
    meta.textContent = [
      product.account_name ? `Аккаунт: ${product.account_name}` : 'Без аккаунта',
      product.price !== null ? `Цена: ${product.price}` : null,
    ].filter(Boolean).join(' · ');

    if (product.ggsel_offer_id) {
      button.textContent = 'Синхронизировать';
      button.addEventListener('click', () => {
        syncProduct(product.id, button);
      });
    } else {
      button.textContent = 'Опубликовать';
      button.addEventListener('click', () => {
        publishProduct(product.id, button);
      });
    }

    fragment.append(item);
  }

  productListElement.append(fragment);
}

async function publishProduct(productId, button) {
  button.disabled = true;
  button.textContent = 'Публикация...';

  try {
    const product = await loadProduct(productId);
    const sellerBaseUrl = await getCurrentSiteBaseUrl();
    const response = await fetch(`${sellerBaseUrl}/api/v1/offers/draft`, {
      method: 'POST',
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(buildDraftPayload(product)),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const payload = await response.json();
    const offerId = payload?.data?.id;

    if (!offerId) {
      throw new Error('Сервер не вернул data.id.');
    }

    await saveGgselOfferId(productId, offerId);
    await updateOfferQuantity(sellerBaseUrl, offerId);
    await updateOfferPrice(sellerBaseUrl, offerId, product.price);
    await updateOfferInstructions(sellerBaseUrl, offerId, product);
    await activateOffer(sellerBaseUrl, offerId);

    setStatus(`Продукт опубликован, ID: ${offerId}`);
    await loadProducts();
  } catch (error) {
    button.disabled = false;
    button.textContent = 'Опубликовать';
    setStatus(`Ошибка публикации: ${error.message}`);
  }
}

async function syncProduct(productId, button) {
  button.disabled = true;
  button.textContent = 'Синхронизация...';

  try {
    const product = await loadProduct(productId);

    if (!product.ggsel_offer_id) {
      throw new Error('У продукта нет GGSEL offer ID.');
    }

    const sellerBaseUrl = await getCurrentSiteBaseUrl();
    const response = await fetch(`${sellerBaseUrl}/api/v1/offers/${product.ggsel_offer_id}`, {
      method: 'PUT',
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(buildSyncPayload(product)),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    await updateOfferPrice(sellerBaseUrl, product.ggsel_offer_id, product.price);
    await updateOfferInstructions(sellerBaseUrl, product.ggsel_offer_id, product);

    button.textContent = 'Синхронизировано';
    setStatus('Данные продукта синхронизированы.');
  } catch (error) {
    button.disabled = false;
    button.textContent = 'Синхронизировать';
    setStatus(`Ошибка синхронизации: ${error.message}`);
  }
}

function setStatus(message) {
  statusElement.textContent = message;
}

async function loadProduct(productId) {
  const response = await fetch(`${API_BASE_URL}/extension/products/${productId}`, {
    headers: {
      Accept: 'application/json',
    },
  });

  if (!response.ok) {
    throw new Error(`Не удалось получить продукт: HTTP ${response.status}`);
  }

  const payload = await response.json();

  return payload.data;
}

async function getCurrentSiteBaseUrl() {
  const [tab] = await chrome.tabs.query({
    active: true,
    currentWindow: true,
  });

  if (!tab?.url) {
    throw new Error('Не удалось определить текущий сайт.');
  }

  const url = new URL(tab.url);

  if (!['http:', 'https:'].includes(url.protocol)) {
    throw new Error('Откройте сайт продавца во вкладке Chrome.');
  }

  return url.origin;
}

function buildDraftPayload(product) {
  return {
    offer: {
      title_ru: product.title_ru || '',
      title_en: product.title_en || '',
      description_ru: product.description_ru || '',
      description_en: product.description_en || '',
      category_id: 32616,
      autoselling: false,
      cover_image_attributes: {
        attachment_data_uri: product.image_ru_data_uri || product.image_en_data_uri || null,
      },
      delivery_kind: 'auto',
      cover_image_en_attributes: {
        attachment_data_uri: product.image_en_data_uri || product.image_ru_data_uri || null,
      },
      check_unique_code_url: null,
    },
  };
}

function buildSyncPayload(product) {
  return {
    title_ru: product.title_ru || '',
    title_en: product.title_en || '',
    description_ru: product.description_ru || '',
    description_en: product.description_en || '',
    autoselling: false,
    check_unique_code_url: null,
    category_id: 32616,
    delivery_kind: 'auto',
  };
}

async function saveGgselOfferId(productId, offerId) {
  const response = await fetch(`${API_BASE_URL}/extension/products/${productId}/ggsel-offer-id`, {
    method: 'PATCH',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      ggsel_offer_id: Number(offerId),
    }),
  });

  if (!response.ok) {
    throw new Error(`Не удалось сохранить GGSEL offer ID: HTTP ${response.status}`);
  }
}

async function updateOfferPrice(sellerBaseUrl, offerId, price) {
  const response = await fetch(`${sellerBaseUrl}/api/v1/offers/${offerId}/update_price`, {
    method: 'PATCH',
    credentials: 'include',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      offer: {
        price: Number(price ?? 0),
      },
    }),
  });

  if (!response.ok) {
    throw new Error(`Не удалось обновить цену: HTTP ${response.status}`);
  }
}

async function updateOfferInstructions(sellerBaseUrl, offerId, product) {
  const instructions = {};
  const instructionRu = product.instruction_ru?.trim();
  const instructionEn = product.instruction_en?.trim();

  if (instructionRu) {
    instructions.instructions_ru = instructionRu;
  }

  if (instructionEn) {
    instructions.instructions_en = instructionEn;
  }

  if (Object.keys(instructions).length === 0) {
    return;
  }

  const response = await fetch(`${sellerBaseUrl}/api/v1/offers/${offerId}/update_instructions`, {
    method: 'PATCH',
    credentials: 'include',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      offer: instructions,
    }),
  });

  if (!response.ok) {
    throw new Error(`Не удалось обновить инструкции: HTTP ${response.status}`);
  }
}

async function activateOffer(sellerBaseUrl, offerId) {
  const response = await fetch(`${sellerBaseUrl}/api/v1/offers/${offerId}/activate`, {
    method: 'POST',
    credentials: 'include',
    headers: {
      Accept: 'application/json',
    },
  });

  if (!response.ok) {
    throw new Error(`Не удалось опубликовать объявление: HTTP ${response.status}`);
  }
}

async function updateOfferQuantity(sellerBaseUrl, offerId) {
  const response = await fetch(`${sellerBaseUrl}/api/v1/offers/${offerId}`, {
    method: 'PUT',
    credentials: 'include',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      quantity: null,
      unlimited_quantity: true,
    }),
  });

  if (!response.ok) {
    throw new Error(`Не удалось обновить количество: HTTP ${response.status}`);
  }
}
