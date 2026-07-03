<x-layouts.app title="Создать продукт">
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-950">Создать продукт</h1>
            <p class="mt-2 text-sm text-zinc-600">Заполните основные данные и версии контента на русском и английском.</p>
        </div>
        <a href="{{ route('products.index') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-white">
            Назад
        </a>
    </div>

    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-6" x-data="productForm">
        @include('products._form')

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('products.index') }}" class="rounded-md px-4 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-100">
                Отмена
            </a>
            <button type="submit" class="rounded-md bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">
                Сохранить продукт
            </button>
        </div>
    </form>
</x-layouts.app>
