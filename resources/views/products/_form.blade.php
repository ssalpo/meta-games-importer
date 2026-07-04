@csrf

<div class="space-y-8">
    <section class="rounded-md border border-zinc-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-zinc-950">Основные данные</h2>

        <div class="mt-6 grid gap-6 md:grid-cols-2">
            <div>
                <label for="account_id" class="block text-sm font-medium text-zinc-800">Аккаунт</label>
                <select
                    id="account_id"
                    name="account_id"
                    class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                >
                    <option value="">Без аккаунта</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" @selected($selectedAccountId === $account->id)>
                            {{ $account->name }}
                        </option>
                    @endforeach
                </select>
                @if ($accounts->isEmpty())
                    <p class="mt-2 text-sm text-zinc-500">Аккаунтов пока нет. Их можно добавить в разделе “Аккаунты”.</p>
                @endif
                @error('account_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="placement_category" class="block text-sm font-medium text-zinc-800">Категория размещения</label>
                <input
                    id="placement_category"
                    name="placement_category"
                    type="text"
                    value="{{ old('placement_category', $product->placement_category) }}"
                    required
                    class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                >
                @error('placement_category')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="external_reference" class="block text-sm font-medium text-zinc-800">ID внешней системы или название</label>
                <input
                    id="external_reference"
                    name="external_reference"
                    type="text"
                    value="{{ old('external_reference', $product->external_reference) }}"
                    class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                >
                @error('external_reference')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="ggsel_offer_id" class="block text-sm font-medium text-zinc-800">GGSEL offer ID</label>
                <input
                    id="ggsel_offer_id"
                    name="ggsel_offer_id"
                    type="number"
                    min="1"
                    value="{{ old('ggsel_offer_id', $product->ggsel_offer_id) }}"
                    class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                >
                @error('ggsel_offer_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-zinc-800">Цена</label>
                <input
                    id="price"
                    name="price"
                    type="number"
                    min="0"
                    step="0.01"
                    value="{{ old('price', $product->price) }}"
                    required
                    class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                >
                @error('price')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    @foreach (['ru' => 'Русская версия', 'en' => 'Английская версия'] as $locale => $label)
        @php
            $collection = 'image_'.$locale;
            $image = $locale === 'ru' ? $product->imageRu() : $product->imageEn();
            $instructionField = 'instruction_'.$locale;
            $additionalInfoField = 'additional_info_'.$locale;
            $instructionValue = old($instructionField, $product->{$instructionField} ?: ($lastReusableProductTexts[$instructionField] ?? null));
            $additionalInfoValue = old($additionalInfoField, $product->{$additionalInfoField} ?: ($lastReusableProductTexts[$additionalInfoField] ?? null));
        @endphp

        <section class="rounded-md border border-zinc-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <h2 class="text-base font-semibold text-zinc-950">{{ $label }}</h2>

                @if ($locale === 'ru')
                    <div class="w-full md:max-w-xl">
                        <label for="generation_prompt_id" class="block text-sm font-medium text-zinc-800">Промпт генерации</label>
                        <select
                            id="generation_prompt_id"
                            class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        >
                            @foreach ($generationPrompts as $prompt)
                                <option value="{{ $prompt->id }}" @selected((int) $selectedGenerationPromptId === $prompt->id)>
                                    {{ $prompt->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($generationPrompts->isEmpty())
                            <p class="mt-2 text-sm text-red-600">
                                Нет активных промптов. Создайте промпт в разделе “Промпты”.
                            </p>
                        @endif

                        <label for="deepseek_game_title" class="mt-4 block text-sm font-medium text-zinc-800">Название игры для генерации</label>
                        <input
                            id="deepseek_game_title"
                            type="text"
                            value="{{ old('title_ru', $product->title_ru ? str_replace(' для Meta / Oculus Quest', '', $product->title_ru) : '') }}"
                            maxlength="{{ \App\Models\Product::TITLE_MAX_LENGTH }}"
                            class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            placeholder="Например: Pavlov Shack"
                        >

                        <label for="deepseek_instructions" class="mt-4 block text-sm font-medium text-zinc-800">Дополнительные инструкции</label>
                        <textarea
                            id="deepseek_instructions"
                            rows="3"
                            class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            placeholder="Например: жанры Shooter, Action, Tactical; режимы Multiplayer, Online PvP; акцент на командной игре."
                        ></textarea>
                        <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                            <button
                                type="button"
                                x-on:click="generateEnglishCopy"
                                x-bind:disabled="isGeneratingEnglish"
                                class="inline-flex w-fit items-center justify-center rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800 disabled:cursor-not-allowed disabled:bg-zinc-400"
                            >
                                <span x-show="!isGeneratingEnglish">Сгенерировать RU/EN название и описание</span>
                                <span x-cloak x-show="isGeneratingEnglish">Генерация...</span>
                            </button>
                            <p x-cloak x-show="generationError" x-text="generationError" class="text-sm text-red-600"></p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-6 space-y-6">
                <div>
                    <label for="title_{{ $locale }}" class="block text-sm font-medium text-zinc-800">Название</label>
                    <input
                        id="title_{{ $locale }}"
                        name="title_{{ $locale }}"
                        type="text"
                        value="{{ old('title_'.$locale, $product->{'title_'.$locale}) }}"
                        maxlength="{{ \App\Models\Product::TITLE_MAX_LENGTH }}"
                        required
                        class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                    >
                    @error('title_'.$locale)
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description_{{ $locale }}" class="block text-sm font-medium text-zinc-800">Описание</label>
                    <textarea
                        id="description_{{ $locale }}"
                        name="description_{{ $locale }}"
                        rows="5"
                        class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                    >{{ old('description_'.$locale, $product->{'description_'.$locale}) }}</textarea>
                    @error('description_'.$locale)
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="{{ $collection }}" class="block text-sm font-medium text-zinc-800">Изображение</label>
                    @if ($image)
                        <div class="mt-2 flex flex-col gap-3 rounded-md border border-zinc-200 bg-zinc-50 p-3 sm:flex-row sm:items-center">
                            <img src="{{ $image->getUrl() }}" alt="{{ $product->{'title_'.$locale} }}" class="h-24 w-24 rounded-md object-cover">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-900">{{ $image->file_name }}</p>
                                <p class="mt-1 text-xs text-zinc-500">{{ number_format($image->size / 1024, 1) }} КБ</p>
                                <label class="mt-3 inline-flex items-center gap-2 text-sm text-red-700">
                                    <input type="checkbox" name="remove_{{ $collection }}" value="1" class="h-4 w-4 rounded border-zinc-300 text-red-600 focus:ring-red-600">
                                    Удалить текущее изображение
                                </label>
                            </div>
                        </div>
                    @endif
                    <input
                        id="{{ $collection }}"
                        name="{{ $collection }}"
                        type="file"
                        accept="image/*"
                        class="mt-3 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-zinc-950 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800"
                    >
                    @error($collection)
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="instruction_{{ $locale }}" class="block text-sm font-medium text-zinc-800">Инструкция</label>
                    <textarea
                        id="instruction_{{ $locale }}"
                        name="instruction_{{ $locale }}"
                        rows="5"
                        class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                    >{{ $instructionValue }}</textarea>
                    @error('instruction_'.$locale)
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="additional_info_{{ $locale }}" class="block text-sm font-medium text-zinc-800">Дополнительная информация</label>
                    <textarea
                        id="additional_info_{{ $locale }}"
                        name="additional_info_{{ $locale }}"
                        rows="4"
                        class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                    >{{ $additionalInfoValue }}</textarea>
                    @error('additional_info_'.$locale)
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>
    @endforeach
</div>
