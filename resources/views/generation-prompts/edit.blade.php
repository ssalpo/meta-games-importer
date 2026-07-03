<x-layouts.app title="Редактировать промпт">
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-950">Редактировать промпт</h1>
            <p class="mt-2 text-sm text-zinc-600">{{ $prompt->name }}</p>
        </div>
        <a href="{{ route('generation-prompts.index') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-white">
            Назад
        </a>
    </div>

    <form method="POST" action="{{ route('generation-prompts.update', $prompt) }}" class="space-y-6">
        @method('PUT')
        @include('generation-prompts._form')

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('generation-prompts.index') }}" class="rounded-md px-4 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-100">Отмена</a>
            <button type="submit" class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">Обновить</button>
        </div>
    </form>
</x-layouts.app>
