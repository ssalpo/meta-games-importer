<x-layouts.app title="Account details">
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-950">{{ $account->name }}</h1>
            <p class="mt-2 text-sm text-zinc-600">Account details and token status.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('accounts.index') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-white">
                Back
            </a>
            <a href="{{ route('accounts.edit', $account) }}" class="rounded-md bg-zinc-950 px-3 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
                Edit
            </a>
        </div>
    </div>

    <dl class="max-w-2xl divide-y divide-zinc-200 rounded-md border border-zinc-200 bg-white shadow-sm">
        <div class="grid gap-1 px-6 py-4 sm:grid-cols-3 sm:gap-4">
            <dt class="text-sm font-medium text-zinc-600">Name</dt>
            <dd class="text-sm text-zinc-950 sm:col-span-2">{{ $account->name }}</dd>
        </div>
        <div class="grid gap-1 px-6 py-4 sm:grid-cols-3 sm:gap-4">
            <dt class="text-sm font-medium text-zinc-600">Access token</dt>
            <dd class="font-mono text-sm text-zinc-950 sm:col-span-2">{{ $account->maskedAccessToken() }}</dd>
        </div>
        <div class="grid gap-1 px-6 py-4 sm:grid-cols-3 sm:gap-4">
            <dt class="text-sm font-medium text-zinc-600">Created</dt>
            <dd class="text-sm text-zinc-950 sm:col-span-2">{{ $account->created_at->format('Y-m-d H:i') }}</dd>
        </div>
        <div class="grid gap-1 px-6 py-4 sm:grid-cols-3 sm:gap-4">
            <dt class="text-sm font-medium text-zinc-600">Updated</dt>
            <dd class="text-sm text-zinc-950 sm:col-span-2">{{ $account->updated_at->format('Y-m-d H:i') }}</dd>
        </div>
    </dl>
</x-layouts.app>
