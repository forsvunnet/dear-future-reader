<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Things' }} · Dear Future Reader</title>
        @vite(['resources/css/app.css', 'resources/js/app.js', 'domain/Dfr/resources/css/dfr.css', 'domain/Dfr/resources/js/highlight.js'])
    </head>
    <body class="dfr-body md:flex">
        {{-- On a narrow screen the sidebar is above the page. --}}
        <aside class="dfr-line border-b md:sticky md:top-0 md:h-screen md:w-72 md:shrink-0 md:overflow-y-auto md:border-r md:border-b-0">
            <header class="dfr-line border-b px-4 py-3">
                <a href="{{ route('dfr.things') }}" class="dfr-title">Dear Future Reader</a>
            </header>
            @livewire(\Domain\Dfr\Livewire\ThingList::class, ['current' => request()->route('id')])
        </aside>
        {{-- A page sets its own padding, so the thing page can add a sidebar on the right. --}}
        <main class="min-w-0 flex-1">
            {{ $slot }}
        </main>
    </body>
</html>
