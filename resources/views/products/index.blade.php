<x-layouts.app title="Продукты">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-950">Продукты</h1>
            <p class="mt-2 text-sm text-zinc-600">Управление продуктами, ценами и локализованным контентом.</p>
        </div>
        <a href="{{ route('products.create') }}" class="inline-flex w-fit items-center rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
            Добавить продукт
        </a>
    </div>

    <form method="GET" action="{{ route('products.index') }}" class="mb-4 flex flex-col gap-3 rounded-md border border-zinc-200 bg-white p-4 shadow-sm sm:flex-row">
        <label for="q" class="sr-only">Поиск продуктов</label>
        <input
            id="q"
            name="q"
            type="search"
            value="{{ $search }}"
            placeholder="Поиск по названию, аккаунту, категории, внешнему ID или GGSEL"
            class="min-h-10 flex-1 rounded-md border border-zinc-300 px-3 py-2 text-sm text-zinc-950 placeholder:text-zinc-400 focus:border-zinc-500 focus:outline-none focus:ring-2 focus:ring-zinc-200"
        >
        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center justify-center rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
                Найти
            </button>
            @if ($search !== '')
                <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center rounded-md border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
                    Сбросить
                </a>
            @endif
        </div>
    </form>

    <div class="overflow-hidden rounded-md border border-zinc-200 bg-white shadow-sm">
        @if ($products->isEmpty())
            <div class="px-6 py-16 text-center">
                @if ($search !== '')
                    <h2 class="text-base font-semibold text-zinc-950">Ничего не найдено</h2>
                    <p class="mt-2 text-sm text-zinc-600">Попробуйте изменить запрос или сбросить поиск.</p>
                    <a href="{{ route('products.index') }}" class="mt-6 inline-flex rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
                        Сбросить поиск
                    </a>
                @else
                    <h2 class="text-base font-semibold text-zinc-950">Продуктов пока нет</h2>
                    <p class="mt-2 text-sm text-zinc-600">Создайте первый продукт для импорта и размещения.</p>
                    <a href="{{ route('products.create') }}" class="mt-6 inline-flex rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
                        Добавить продукт
                    </a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm">
                    <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Продукт</th>
                            <th scope="col" class="px-6 py-3">Аккаунт</th>
                            <th scope="col" class="px-6 py-3">Категория</th>
                            <th scope="col" class="px-6 py-3">Внешняя система</th>
                            <th scope="col" class="px-6 py-3">GGSEL</th>
                            <th scope="col" class="px-6 py-3">Цена</th>
                            <th scope="col" class="px-6 py-3">Изображения</th>
                            <th scope="col" class="px-6 py-3 text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($products as $product)
                            <tr class="align-middle">
                                <td class="px-6 py-4">
                                    <a href="{{ route('products.show', $product) }}" class="font-medium text-zinc-950 hover:underline">{{ $product->title_ru }}</a>
                                    <p class="mt-1 text-xs text-zinc-500">{{ $product->title_en }}</p>
                                </td>
                                <td class="px-6 py-4 text-zinc-700">{{ $product->account?->name ?: '-' }}</td>
                                <td class="px-6 py-4 text-zinc-700">{{ $product->placement_category }}</td>
                                <td class="px-6 py-4 text-zinc-700">{{ $product->external_reference ?: '-' }}</td>
                                <td class="px-6 py-4 text-zinc-700">{{ $product->ggsel_offer_id ?: '-' }}</td>
                                <td class="px-6 py-4 font-medium text-zinc-950">{{ number_format((float) $product->price, 2, '.', ' ') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="rounded-md px-2 py-1 {{ $product->imageRu() ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500' }}">RU</span>
                                        <span class="rounded-md px-2 py-1 {{ $product->imageEn() ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500' }}">EN</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('products.show', $product) }}" class="rounded-md px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">
                                            Просмотр
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}" class="rounded-md px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">
                                            Изменить
                                        </a>
                                        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Удалить этот продукт?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50">
                                                Удалить
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($products->hasPages())
                <div class="border-t border-zinc-200 px-6 py-4">
                    {{ $products->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
