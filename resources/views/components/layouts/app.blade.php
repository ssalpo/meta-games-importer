<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Metea Games Importer') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-950 antialiased">
        <div class="min-h-screen">
            <header class="border-b border-zinc-200 bg-white">
                <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ route('products.index') }}" class="text-base font-semibold text-zinc-950">
                        Metea Games Importer
                    </a>

                    <nav class="flex items-center gap-1 sm:gap-2">
                        <a
                            href="{{ route('products.index') }}"
                            class="rounded-md px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 hover:text-zinc-950"
                        >
                            Продукты
                        </a>
                        <a
                            href="{{ route('accounts.index') }}"
                            class="rounded-md px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 hover:text-zinc-950"
                        >
                            Аккаунты
                        </a>
                        <a
                            href="{{ route('products.create') }}"
                            class="rounded-md bg-zinc-950 px-3 py-2 text-sm font-semibold text-white hover:bg-zinc-800"
                        >
                            Добавить продукт
                        </a>
                    </nav>
                </div>
            </header>

            <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
                @if (session('status'))
                    <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
