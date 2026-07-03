@csrf

<div class="space-y-6">
    <div>
        <label for="name" class="block text-sm font-medium text-zinc-800">Name</label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $account->name) }}"
            required
            autofocus
            class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
        >
        @error('name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="access_token" class="block text-sm font-medium text-zinc-800">Access token</label>
        <textarea
            id="access_token"
            name="access_token"
            rows="6"
            {{ $account->exists ? '' : 'required' }}
            class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 font-mono text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
        >{{ old('access_token') }}</textarea>
        <p class="mt-2 text-xs text-zinc-500">
            @if ($account->exists)
                Leave empty to keep the current token.
            @else
                The token will be encrypted before it is saved.
            @endif
        </p>
        @error('access_token')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
