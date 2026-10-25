<div>
    <h1 class="text-2xl font-semibold">{{ $thing->name }}</h1>

    <dl class="mt-2 mb-6 grid grid-cols-[auto_1fr] gap-x-4 text-sm">
        <dt class="text-stone-500">ID</dt>
        <dd class="font-mono">{{ $thing->id }}</dd>
        <dt class="text-stone-500">Status</dt>
        <dd>{{ $thing->status }}</dd>
        @if ($thing->parent !== null)
            <dt class="text-stone-500">Parent</dt>
            <dd>
                @if ($parent !== null)
                    <a href="{{ route('dfr.thing', $parent->id) }}" class="underline">{{ $parent->name }}</a>
                @else
                    <span class="font-mono">{{ $thing->parent }}</span>
                @endif
            </dd>
        @endif
    </dl>

    {{-- A session can archive or remove the open file. Keep the editor, so the text is not lost. --}}
    @if ($editing !== null && ! array_key_exists($editing, $documents))
        <section class="mb-6 rounded border border-stone-200 dark:border-stone-800">
            <h2 class="border-b border-stone-200 px-4 py-2 font-mono text-sm font-semibold dark:border-stone-800">{{ $editing }}</h2>
            @include('dfr::editor', ['fileName' => $editing])
        </section>
    @elseif ($documents === [])
        <p>The thing has no files.</p>
    @endif

    @foreach ($documents as $fileName => $document)
        <section wire:key="{{ $fileName }}" class="mb-6 rounded border border-stone-200 dark:border-stone-800">
            <header class="flex items-center justify-between border-b border-stone-200 px-4 py-2 dark:border-stone-800">
                <h2 class="font-mono text-sm font-semibold">{{ $fileName }}</h2>
                @if ($editing !== $fileName)
                    <button type="button" wire:click="edit(@js($fileName))" class="text-sm underline">Edit</button>
                @endif
            </header>

            @if ($editing === $fileName)
                @include('dfr::editor')
            @else
                @if ($document->frontmatter !== null)
                    <pre class="overflow-x-auto border-b border-stone-200 bg-stone-100 px-4 py-2 text-sm dark:border-stone-800 dark:bg-stone-900">{{ $document->frontmatter }}</pre>
                @endif
                {{-- HTML in a file is escaped, so it cannot run in the browser. --}}
                <div class="markdown px-4 py-2">
                    {!! str($document->body)->markdown(['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}
                </div>
            @endif
        </section>
    @endforeach
</div>
