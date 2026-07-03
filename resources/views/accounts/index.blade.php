<x-layouts.app title="Аккаунты">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-950">Аккаунты</h1>
            <p class="mt-2 text-sm text-zinc-600">Управление названиями аккаунтов и зашифрованными токенами доступа.</p>
        </div>
        <a href="{{ route('accounts.create') }}" class="inline-flex w-fit items-center rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
            Добавить аккаунт
        </a>
    </div>

    <div class="overflow-hidden rounded-md border border-zinc-200 bg-white shadow-sm">
        @if ($accounts->isEmpty())
            <div class="px-6 py-16 text-center">
                <h2 class="text-base font-semibold text-zinc-950">Аккаунтов пока нет</h2>
                <p class="mt-2 text-sm text-zinc-600">Создайте первый аккаунт, чтобы начать импорт игр.</p>
                <a href="{{ route('accounts.create') }}" class="mt-6 inline-flex rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
                    Добавить аккаунт
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm">
                    <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Название</th>
                            <th scope="col" class="px-6 py-3">Токен</th>
                            <th scope="col" class="px-6 py-3">Обновлен</th>
                            <th scope="col" class="px-6 py-3 text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($accounts as $account)
                            <tr class="align-middle">
                                <td class="px-6 py-4 font-medium text-zinc-950">
                                    <a href="{{ route('accounts.show', $account) }}" class="hover:underline">{{ $account->name }}</a>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-zinc-600">{{ $account->maskedAccessToken() }}</td>
                                <td class="px-6 py-4 text-zinc-600">{{ $account->updated_at->format('Y-m-d H:i') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('accounts.show', $account) }}" class="rounded-md px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">
                                            Просмотр
                                        </a>
                                        <a href="{{ route('accounts.edit', $account) }}" class="rounded-md px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">
                                            Изменить
                                        </a>
                                        <form method="POST" action="{{ route('accounts.destroy', $account) }}" onsubmit="return confirm('Удалить этот аккаунт?');">
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

            @if ($accounts->hasPages())
                <div class="border-t border-zinc-200 px-6 py-4">
                    {{ $accounts->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
