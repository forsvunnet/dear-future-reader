<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Things' }} · Dear Future Reader</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-stone-50 text-stone-900 dark:bg-stone-950 dark:text-stone-100">
        <header class="border-b border-stone-200 dark:border-stone-800">
            <nav class="mx-auto max-w-4xl px-4 py-3">
                <a href="{{ route('dfr.things') }}" class="font-semibold">Dear Future Reader</a>
            </nav>
        </header>
        <main class="mx-auto max-w-4xl px-4 py-6">
            {{ $slot }}
        </main>
    </body>
</html>
