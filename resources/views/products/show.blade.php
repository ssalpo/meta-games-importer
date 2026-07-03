<x-layouts.app title="Просмотр продукта">
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-950">{{ $product->title_ru }}</h1>
            <p class="mt-2 text-sm text-zinc-600">{{ $product->placement_category }} · {{ number_format((float) $product->price, 2, '.', ' ') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('products.index') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-white">
                Назад
            </a>
            <a href="{{ route('products.edit', $product) }}" class="rounded-md bg-zinc-950 px-3 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
                Изменить
            </a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-6">
            <section class="rounded-md border border-zinc-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-zinc-950">Основные данные</h2>
                <dl class="mt-4 divide-y divide-zinc-100">
                    <div class="grid gap-1 py-3 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-zinc-600">Категория размещения</dt>
                        <dd class="text-sm text-zinc-950 sm:col-span-2">{{ $product->placement_category }}</dd>
                    </div>
                    <div class="grid gap-1 py-3 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-zinc-600">ID или название внешней системы</dt>
                        <dd class="text-sm text-zinc-950 sm:col-span-2">{{ $product->external_reference ?: '-' }}</dd>
                    </div>
                    <div class="grid gap-1 py-3 sm:grid-cols-3">
                        <dt class="text-sm font-medium text-zinc-600">Цена</dt>
                        <dd class="text-sm font-semibold text-zinc-950 sm:col-span-2">{{ number_format((float) $product->price, 2, '.', ' ') }}</dd>
                    </div>
                </dl>
            </section>

            @foreach (['ru' => 'Русская версия', 'en' => 'Английская версия'] as $locale => $label)
                <section class="rounded-md border border-zinc-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-zinc-950">{{ $label }}</h2>
                    <dl class="mt-4 divide-y divide-zinc-100">
                        <div class="grid gap-1 py-3 sm:grid-cols-3">
                            <dt class="text-sm font-medium text-zinc-600">Название</dt>
                            <dd class="text-sm text-zinc-950 sm:col-span-2">{{ $product->{'title_'.$locale} }}</dd>
                        </div>
                        <div class="grid gap-1 py-3 sm:grid-cols-3">
                            <dt class="text-sm font-medium text-zinc-600">Описание</dt>
                            <dd class="whitespace-pre-line text-sm text-zinc-950 sm:col-span-2">{{ $product->{'description_'.$locale} ?: '-' }}</dd>
                        </div>
                        <div class="grid gap-1 py-3 sm:grid-cols-3">
                            <dt class="text-sm font-medium text-zinc-600">Инструкция</dt>
                            <dd class="whitespace-pre-line text-sm text-zinc-950 sm:col-span-2">{{ $product->{'instruction_'.$locale} ?: '-' }}</dd>
                        </div>
                        <div class="grid gap-1 py-3 sm:grid-cols-3">
                            <dt class="text-sm font-medium text-zinc-600">Дополнительная информация</dt>
                            <dd class="whitespace-pre-line text-sm text-zinc-950 sm:col-span-2">{{ $product->{'additional_info_'.$locale} ?: '-' }}</dd>
                        </div>
                    </dl>
                </section>
            @endforeach
        </div>

        <aside class="space-y-6">
            @foreach (['ru' => ['label' => 'Изображение RU', 'media' => $product->imageRu()], 'en' => ['label' => 'Изображение EN', 'media' => $product->imageEn()]] as $item)
                <section class="rounded-md border border-zinc-200 bg-white p-4 shadow-sm">
                    <h2 class="text-sm font-semibold text-zinc-950">{{ $item['label'] }}</h2>
                    @if ($item['media'])
                        <img src="{{ $item['media']->getUrl() }}" alt="{{ $item['label'] }}" class="mt-3 aspect-square w-full rounded-md object-cover">
                        <p class="mt-3 truncate text-xs text-zinc-500">{{ $item['media']->file_name }}</p>
                    @else
                        <div class="mt-3 flex aspect-square items-center justify-center rounded-md border border-dashed border-zinc-300 bg-zinc-50 text-sm text-zinc-500">
                            Нет изображения
                        </div>
                    @endif
                </section>
            @endforeach
        </aside>
    </div>
</x-layouts.app>
