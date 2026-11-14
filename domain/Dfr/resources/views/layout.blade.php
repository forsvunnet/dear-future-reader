<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Things' }} · Dear Future Reader</title>
        @vite(['resources/css/app.css', 'resources/js/app.js', 'domain/Dfr/resources/css/dfr.css', 'domain/Dfr/resources/js/highlight.js', 'domain/Dfr/resources/js/copy-table.js', 'domain/Dfr/resources/js/toaster.js', 'domain/Dfr/resources/js/copy-text.js', 'domain/Dfr/resources/js/splitter.js'])
    </head>
    <body class="dfr-body md:flex">
        {{-- On a narrow screen the sidebar is above the page. --}}
        <aside class="dfr-line border-b md:sticky md:top-0 md:h-screen md:w-72 md:shrink-0 md:overflow-y-auto md:border-r md:border-b-0">
            <header class="dfr-column-header dfr-line">
                <a href="{{ route('dfr.things') }}" class="dfr-title">Dear Future Reader</a>
            </header>
            @livewire(\Domain\Dfr\Livewire\ThingList::class, ['current' => request()->route('id')])
        </aside>
        {{-- A page sets its own padding, so the thing page can add a sidebar on the right. --}}
        <main class="min-w-0 flex-1">
            {{ $slot }}
        </main>

        {{-- Alpine owns the content of the toaster. Show a toast from any element with window.toast({ message, description }). --}}
        <div x-data="toaster" aria-live="polite" class="dfr-toaster">
            <template x-for="t in toasts" :key="t.id">
                <div class="dfr-toast dfr-line" :class="t.leaving && 'dfr-toast-leaving'">
                    <div class="min-w-0 flex-1">
                        <p class="dfr-title" x-text="t.message"></p>
                        <template x-if="t.description">
                            <p class="dfr-muted break-all" x-text="t.description"></p>
                        </template>
                    </div>
                    <button type="button" aria-label="Dismiss" x-on:click="dismiss(t.id)" class="dfr-muted">×</button>
                </div>
            </template>
        </div>
    </body>
</html>
