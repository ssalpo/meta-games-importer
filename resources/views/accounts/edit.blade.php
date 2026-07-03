<x-layouts.app title="Редактировать аккаунт">
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-950">Редактировать аккаунт</h1>
            <p class="mt-2 text-sm text-zinc-600">{{ $account->name }}</p>
        </div>
        <a href="{{ route('accounts.index') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-white">
            Назад
        </a>
    </div>

    <form method="POST" action="{{ route('accounts.update', $account) }}" class="max-w-2xl rounded-md border border-zinc-200 bg-white p-6 shadow-sm">
        @method('PUT')
        @include('accounts._form')

        <div class="mt-8 flex items-center justify-end gap-3">
            <a href="{{ route('accounts.index') }}" class="rounded-md px-4 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-100">
                Отмена
            </a>
            <button type="submit" class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
                Обновить аккаунт
            </button>
        </div>
    </form>
</x-layouts.app>
