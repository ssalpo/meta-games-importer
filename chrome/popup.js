const API_BASE_URL = 'http://localhost:8088';
const STORAGE_SELECTED_ACCOUNT_ID = 'selected_account_id';

const statusElement = document.getElementById('status');
const productListElement = document.getElementById('productList');
const productTemplate = document.getElementById('productTemplate');
const refreshButton = document.getElementById('refreshButton');
const accountSelect = document.getElementById('accountSelect');
const publishAllButton = document.getElementById('publishAllButton');
const paginationElement = document.getElementById('pagination');
const prevPageButton = document.getElementById('prevPageButton');
const nextPageButton = document.getElementById('nextPageButton');
const pageInfoElement = document.getElementById('pageInfo');

let selectedAccountId = null;
let currentProducts = [];
let isBulkPublishing = false;
let currentPage = 1;
let paginationMeta = null;

const PRODUCTS_PER_PAGE = 10;

refreshButton.addEventListener('click', () => {
  initializePopup(currentPage);
});

publishAllButton.addEventListener('click', () => {
  publishAllProducts();
});

prevPageButton.addEventListener('click', () => {
  if (paginationMeta?.current_page > 1) {
    loadProducts(paginationMeta.current_page - 1);
  }
});

nextPageButton.addEventListener('click', () => {
  if (paginationMeta && paginationMeta.current_page < paginationMeta.last_page) {
    loadProducts(paginationMeta.current_page + 1);
  }
});

accountSelect.addEventListener('change', async () => {
  selectedAccountId = accountSelect.value ? Number(accountSelect.value) : null;
  currentProducts = [];
  currentPage = 1;
  paginationMeta = null;
  updateBulkPublishButton();
  updatePagination();
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

async function initializePopup(page = 1) {
  setStatus('Загрузка аккаунтов...');
  currentProducts = [];
  paginationMeta = null;
  updateBulkPublishButton();
  updatePagination();
  productListElement.replaceChildren();

  try {
    const accounts = await loadAccounts();

    if (accounts.length === 0) {
      accountSelect.disabled = true;
      selectedAccountId = null;
      currentProducts = [];
      currentPage = 1;
      paginationMeta = null;
      updateBulkPublishButton();
      updatePagination();
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
      currentProducts = [];
      currentPage = 1;
      paginationMeta = null;
      updateBulkPublishButton();
      updatePagination();
      await chrome.storage.local.remove(STORAGE_SELECTED_ACCOUNT_ID);
      setStatus(savedAccountId ? 'Сохраненный аккаунт не найден. Выберите аккаунт.' : 'Выберите аккаунт.');
      return;
    }

    selectedAccountId = savedAccountId;
    accountSelect.value = String(savedAccountId);
    await loadProducts(page);
  } catch (error) {
    setStatus(`Не удалось загрузить аккаунты: ${error.message}`);
    currentProducts = [];
    paginationMeta = null;
    updateBulkPublishButton();
    updatePagination();
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

async function loadProducts(page = 1) {
  if (!selectedAccountId) {
    setStatus('Выберите аккаунт.');
    currentProducts = [];
    currentPage = 1;
    paginationMeta = null;
    updateBulkPublishButton();
    updatePagination();
    productListElement.replaceChildren();
    return;
  }

  setStatus('Загрузка...');
  currentProducts = [];
  paginationMeta = null;
  updateBulkPublishButton();
  updatePagination();
  productListElement.replaceChildren();

  try {
    const url = new URL(`${API_BASE_URL}/extension/products`);
    url.searchParams.set('account_id', String(selectedAccountId));
    url.searchParams.set('page', String(page));
    url.searchParams.set('per_page', String(PRODUCTS_PER_PAGE));

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
    currentProducts = products;
    paginationMeta = payload.meta ?? null;
    currentPage = paginationMeta?.current_page ?? page;
    updateBulkPublishButton();
    updatePagination();

    if (products.length === 0) {
      setStatus('Для выбранного аккаунта продуктов пока нет.');
      return;
    }

    setStatus('');
    renderProducts(products);
  } catch (error) {
    setStatus(`Не удалось загрузить продукты: ${error.message}`);
    currentProducts = [];
    paginationMeta = null;
    updateBulkPublishButton();
    updatePagination();
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

function updateBulkPublishButton() {
  const publishableCount = currentProducts.filter((product) => !product.ggsel_offer_id).length;
  publishAllButton.disabled = isBulkPublishing || !selectedAccountId || publishableCount === 0;
  publishAllButton.textContent = publishableCount > 0
    ? `Опубликовать на странице (${publishableCount})`
    : 'Опубликовать все';
}

function updatePagination() {
  if (!paginationMeta || paginationMeta.total === 0 || paginationMeta.last_page <= 1) {
    paginationElement.hidden = true;
    pageInfoElement.textContent = '';
    prevPageButton.disabled = true;
    nextPageButton.disabled = true;
    return;
  }

  paginationElement.hidden = false;
  pageInfoElement.textContent = `${paginationMeta.from}-${paginationMeta.to} из ${paginationMeta.total} · стр. ${paginationMeta.current_page}/${paginationMeta.last_page}`;
  prevPageButton.disabled = isBulkPublishing || paginationMeta.current_page <= 1;
  nextPageButton.disabled = isBulkPublishing || paginationMeta.current_page >= paginationMeta.last_page;
}

function setProductButtonsDisabled(disabled) {
  productListElement.querySelectorAll('.publish-button').forEach((button) => {
    button.disabled = disabled;
  });
}

async function publishProduct(productId, button) {
  const productSummary = currentProducts.find((product) => product.id === productId) ?? { id: productId };

  button.disabled = true;
  button.textContent = 'Публикация...';

  try {
    const offerId = await publishProductById(productId);

    setStatus(`Продукт опубликован, ID: ${offerId}`);
    await loadProducts();
  } catch (error) {
    button.disabled = false;
    button.textContent = 'Опубликовать';
    setStatus(`Ошибка публикации:\n${formatProductError(error, productSummary)}`);
  }
}

async function publishAllProducts() {
  if (isBulkPublishing) {
    return;
  }

  const productsToPublish = currentProducts.filter((product) => !product.ggsel_offer_id);

  if (productsToPublish.length === 0) {
    setStatus('Нет продуктов для публикации.');
    return;
  }

  isBulkPublishing = true;
  publishAllButton.disabled = true;
  accountSelect.disabled = true;
  refreshButton.disabled = true;
  updatePagination();
  setProductButtonsDisabled(true);

  let activeProduct = null;

  try {
    for (const [index, product] of productsToPublish.entries()) {
      activeProduct = product;
      setStatus(`Публикация ${index + 1} из ${productsToPublish.length}: ${productTitle(product)}`);
      await publishProductById(product.id);
    }

    setStatus(`Опубликовано продуктов: ${productsToPublish.length}.`);
    await loadProducts(currentPage);
  } catch (error) {
    const failedProduct = error.product
      ?? productsToPublish.find((product) => product.id === error.productId)
      ?? activeProduct;

    setStatus(`Массовая публикация остановлена:\n${formatProductError(error, failedProduct)}`);
  } finally {
    isBulkPublishing = false;
    accountSelect.disabled = false;
    refreshButton.disabled = false;
    setProductButtonsDisabled(false);
    updateBulkPublishButton();
    updatePagination();
  }
}

async function publishProductById(productId) {
  const product = await loadProduct(productId);

  try {
    const sellerBaseUrl = await getCurrentSiteBaseUrl();
    const draftUrl = `${sellerBaseUrl}/api/v1/offers/draft`;
    const response = await fetch(draftUrl, {
      method: 'POST',
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(buildDraftPayload(product)),
    });

    if (!response.ok) {
      throw await responseError(response, 'Создание черновика');
    }

    const payload = await response.json();
    const offerId = payload?.data?.id;

    if (!offerId) {
      throw new Error('Создание черновика: сервер не вернул data.id.');
    }

    await saveGgselOfferId(productId, offerId);
    await updateOfferQuantity(sellerBaseUrl, offerId);
    await updateOfferPrice(sellerBaseUrl, offerId, product.price);
    await updateOfferInstructions(sellerBaseUrl, offerId, product);
    await activateOffer(sellerBaseUrl, offerId);

    return offerId;
  } catch (error) {
    error.productId = product.id;
    error.product = product;
    throw error;
  }
}

async function syncProduct(productId, button) {
  let product = currentProducts.find((currentProduct) => currentProduct.id === productId) ?? { id: productId };

  button.disabled = true;
  button.textContent = 'Синхронизация...';

  try {
    product = await loadProduct(productId);

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
      throw await responseError(response, 'Синхронизация объявления');
    }

    await updateOfferPrice(sellerBaseUrl, product.ggsel_offer_id, product.price);
    await updateOfferInstructions(sellerBaseUrl, product.ggsel_offer_id, product);

    button.textContent = 'Синхронизировано';
    setStatus('Данные продукта синхронизированы.');
  } catch (error) {
    button.disabled = false;
    button.textContent = 'Синхронизировать';
    setStatus(`Ошибка синхронизации:\n${formatProductError(error, error.product ?? product)}`);
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
    throw await responseError(response, 'Получение продукта');
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
      category_id: 74823,
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
    category_id: 74823,
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
    throw await responseError(response, 'Сохранение GGSEL offer ID');
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
    throw await responseError(response, 'Обновление цены');
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
    throw await responseError(response, 'Обновление инструкций');
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
    throw await responseError(response, 'Активация объявления');
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
    throw await responseError(response, 'Обновление количества');
  }
}

async function responseError(response, action) {
  const body = await response.text().catch(() => '');
  const messageParts = [
    `${action}: HTTP ${response.status} ${response.statusText}`.trim(),
    `URL: ${response.url}`,
  ];

  if (body.trim()) {
    messageParts.push(`Ответ: ${truncateText(body.trim(), 800)}`);
  }

  return new Error(messageParts.join('\n'));
}

function formatProductError(error, fallbackProduct = null) {
  const product = error.product ?? fallbackProduct;
  const details = [];

  if (product) {
    details.push(`Продукт: #${product.id ?? '-'} — ${productTitle(product)}`);

    const meta = [
      product.account_name ? `аккаунт: ${product.account_name}` : null,
      product.price !== undefined && product.price !== null ? `цена: ${product.price}` : null,
      product.ggsel_offer_id ? `GGSEL offer ID: ${product.ggsel_offer_id}` : 'GGSEL offer ID: нет',
      product.external_reference ? `внешний ID: ${product.external_reference}` : null,
    ].filter(Boolean);

    if (meta.length > 0) {
      details.push(`Данные: ${meta.join('; ')}`);
    }
  }

  details.push(`Ошибка: ${error.message}`);

  return details.join('\n');
}

function productTitle(product) {
  return product?.title_ru || product?.title_en || `Продукт #${product?.id ?? '-'}`;
}

function truncateText(text, maxLength) {
  return text.length > maxLength ? `${text.slice(0, maxLength)}...` : text;
}
