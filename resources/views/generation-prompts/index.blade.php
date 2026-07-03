<x-layouts.app title="Промпты генерации">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-950">Промпты генерации</h1>
            <p class="mt-2 text-sm text-zinc-600">Шаблоны для генерации карточек товара через DeepSeek.</p>
        </div>
        <a href="{{ route('generation-prompts.create') }}" class="inline-flex w-fit items-center rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
            Добавить промпт
        </a>
    </div>

    <div class="overflow-hidden rounded-md border border-zinc-200 bg-white shadow-sm">
        @if ($prompts->isEmpty())
            <div class="px-6 py-16 text-center">
                <h2 class="text-base font-semibold text-zinc-950">Промптов пока нет</h2>
                <p class="mt-2 text-sm text-zinc-600">Создайте первый шаблон генерации.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm">
                    <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Название</th>
                            <th scope="col" class="px-6 py-3">Статус</th>
                            <th scope="col" class="px-6 py-3">Сортировка</th>
                            <th scope="col" class="px-6 py-3 text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($prompts as $prompt)
                            <tr>
                                <td class="px-6 py-4 font-medium text-zinc-950">{{ $prompt->name }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-md px-2 py-1 text-xs {{ $prompt->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500' }}">
                                        {{ $prompt->is_active ? 'Активен' : 'Выключен' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-zinc-600">{{ $prompt->sort_order }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('generation-prompts.edit', $prompt) }}" class="rounded-md px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">Изменить</a>
                                        <form method="POST" action="{{ route('generation-prompts.destroy', $prompt) }}" onsubmit="return confirm('Удалить этот промпт?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Удалить</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($prompts->hasPages())
        <div class="mt-6">{{ $prompts->links() }}</div>
    @endif
</x-layouts.app>
