@csrf

<div class="space-y-6 rounded-md border border-zinc-200 bg-white p-6 shadow-sm">
    <div class="grid gap-6 md:grid-cols-[minmax(0,1fr)_160px]">
        <div>
            <label for="name" class="block text-sm font-medium text-zinc-800">Название промпта</label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name', $prompt->name) }}"
                required
                class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
            >
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sort_order" class="block text-sm font-medium text-zinc-800">Сортировка</label>
            <input
                id="sort_order"
                name="sort_order"
                type="number"
                min="0"
                value="{{ old('sort_order', $prompt->sort_order ?? 100) }}"
                required
                class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
            >
            @error('sort_order')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <label class="inline-flex items-center gap-2 text-sm text-zinc-800">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $prompt->is_active ?? true)) class="h-4 w-4 rounded border-zinc-300 text-zinc-950 focus:ring-zinc-950">
        Активен
    </label>

    <div>
        <label for="system_prompt" class="block text-sm font-medium text-zinc-800">System prompt</label>
        <textarea
            id="system_prompt"
            name="system_prompt"
            rows="14"
            required
            class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 font-mono text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
        >{{ old('system_prompt', $prompt->system_prompt) }}</textarea>
        @error('system_prompt')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="user_prompt_template" class="block text-sm font-medium text-zinc-800">User prompt template</label>
        <textarea
            id="user_prompt_template"
            name="user_prompt_template"
            rows="10"
            required
            class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 font-mono text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
        >{{ old('user_prompt_template', $prompt->user_prompt_template) }}</textarea>
        <p class="mt-2 text-xs text-zinc-500">Доступные переменные: {product_title}, {game_title}, {instructions}, {description_template}</p>
        @error('user_prompt_template')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description_template" class="block text-sm font-medium text-zinc-800">Шаблон описания</label>
        <textarea
            id="description_template"
            name="description_template"
            rows="14"
            class="mt-2 block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 font-mono text-sm text-zinc-950 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
        >{{ old('description_template', $prompt->description_template) }}</textarea>
        <p class="mt-2 text-xs text-zinc-500">Можно оставить пустым для универсального промпта без жёсткого шаблона.</p>
        @error('description_template')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
