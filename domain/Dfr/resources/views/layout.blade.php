<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Things' }} · Dear Future Reader</title>
        @vite(['resources/css/app.css', 'resources/js/app.js', 'domain/Dfr/resources/css/dfr.css'])
    </head>
    <body class="dfr-body">
        <header class="dfr-line border-b">
            <nav class="mx-auto max-w-4xl px-4 py-3">
                <a href="{{ route('dfr.things') }}" class="dfr-title">Dear Future Reader</a>
            </nav>
        </header>
        <main class="mx-auto max-w-4xl px-4 py-6">
            {{ $slot }}
        </main>
    </body>
</html>
