<x-layouts.app title="Импортированные игры">
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-950">Импортированные игры</h1>
            <p class="mt-2 text-sm text-zinc-600">Список игр и дополнений из Meta Games API. Уникальность импорта идет по external_id.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('meta-games.index') }}" class="mb-6 grid gap-3 rounded-md border border-zinc-200 bg-white p-4 shadow-sm md:grid-cols-[minmax(0,1fr)_180px_auto]">
        <input
            name="search"
            type="search"
            value="{{ $filters['search'] ?? '' }}"
            placeholder="Поиск по названию, parent title или external_id"
            class="block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
        >

        <select
            name="type"
            class="block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
        >
            <option value="">Все типы</option>
            <option value="game" @selected(($filters['type'] ?? '') === 'game')>Игры</option>
            <option value="addon" @selected(($filters['type'] ?? '') === 'addon')>Дополнения</option>
        </select>

        <button type="submit" class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
            Найти
        </button>
    </form>

    <div class="overflow-hidden rounded-md border border-zinc-200 bg-white shadow-sm">
        @if ($metaGames->isEmpty())
            <div class="px-6 py-16 text-center">
                <h2 class="text-base font-semibold text-zinc-950">Импортированных игр пока нет</h2>
                <p class="mt-2 text-sm text-zinc-600">После запуска команды импорта здесь появятся игры и дополнения.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm">
                    <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Название</th>
                            <th scope="col" class="px-6 py-3">External ID</th>
                            <th scope="col" class="px-6 py-3">Тип</th>
                            <th scope="col" class="px-6 py-3">Цена</th>
                            <th scope="col" class="px-6 py-3">Обновлено в источнике</th>
                            <th scope="col" class="px-6 py-3 text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($metaGames as $metaGame)
                            <tr class="align-middle">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($metaGame->image_square)
                                            <img
                                                src="{{ $metaGame->imageSquareUrl() }}"
                                                alt="{{ $metaGame->full_title }}"
                                                class="h-12 w-12 rounded-md object-cover"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="h-12 w-12 rounded-md bg-zinc-100"></div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="font-medium text-zinc-950">{{ $metaGame->full_title }}</p>
                                            @if ($metaGame->parent_title)
                                                <p class="mt-1 text-xs text-zinc-500">Parent: {{ $metaGame->parent_title }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-zinc-600">{{ $metaGame->external_id }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-md px-2 py-1 text-xs {{ $metaGame->is_addon ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">
                                        {{ $metaGame->is_addon ? 'Дополнение' : 'Игра' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-medium text-zinc-950">
                                    {{ $metaGame->effectivePrice() !== null ? number_format((float) $metaGame->effectivePrice(), 2, '.', ' ') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-zinc-600">
                                    {{ $metaGame->source_updated_at?->format('Y-m-d H:i') ?: '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($metaGame->product)
                                            <a href="{{ route('products.edit', $metaGame->product) }}" class="rounded-md px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">
                                                Открыть продукт
                                            </a>
                                        @else
                                            <form method="POST" action="{{ route('meta-games.create-product', $metaGame) }}">
                                                @csrf
                                                <button type="submit" class="rounded-md bg-zinc-950 px-3 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
                                                    Создать продукт
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($metaGames->hasPages())
                <div class="border-t border-zinc-200 px-6 py-4">
                    {{ $metaGames->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
