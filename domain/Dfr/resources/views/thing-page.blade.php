{{-- On a narrow screen the commits are below the files. The poll shows the changes that sessions make to the files. --}}
{{-- ponytail: each poll runs one git process for each commit. See the comment in ThingPage::render() when the page gets slow. --}}
<div class="md:flex" wire:poll>
    <div class="min-w-0 flex-1">
        {{-- The copy button gives the text that a new session needs to continue the thing. --}}
        <header class="dfr-column-header dfr-line">
            <h1 class="dfr-title min-w-0 truncate" title="{{ $thing->name }}">{{ $thing->name }}</h1>
            <span class="dfr-muted shrink-0">{{ $thing->id }}</span>
            <button type="button" x-data x-on:click="copyText(@js('dfr ' . $thing->id))" class="dfr-button shrink-0">Copy</button>
        </header>

        <div class="px-4 py-6">
            @if ($thing->parent !== null || $projects !== [])
                <dl class="mb-6 grid grid-cols-[auto_1fr] gap-x-4">
                    @if ($thing->parent !== null)
                        <dt class="dfr-label">Parent</dt>
                        <dd>
                            @if ($parent !== null)
                                <a href="{{ route('dfr.thing', $parent->id) }}">{{ $parent->name }}</a>
                            @else
                                {{ $thing->parent }}
                            @endif
                        </dd>
                    @endif
                    @if ($projects !== [])
                        <dt class="dfr-label">Projects</dt>
                        <dd>
                            @foreach ($projects as $project)
                                <span class="block">{{ $project }}</span>
                            @endforeach
                        </dd>
                    @endif
                </dl>
            @endif

            @if ($tickError !== null)
                <p role="alert" class="dfr-danger mb-6">{{ $tickError }}</p>
            @endif

            {{-- A session can archive or remove the open file. Keep the editor, so the text is not lost. --}}
            @if ($editing !== null && ! array_key_exists($editing, $documents))
                <section class="dfr-line mb-6 border">
                    <h2 class="dfr-line border-b px-4 py-2">{{ $editing }}</h2>
                    @include('dfr::editor', ['fileName' => $editing])
                </section>
            @elseif ($documents === [])
                <p>The thing has no files.</p>
            @endif

            @foreach ($documents as $fileName => $document)
                @php($time = \Illuminate\Support\Carbon::createFromTimestamp($times[$fileName]))
                <section wire:key="{{ $fileName }}" @class(['dfr-line mb-6 border', 'dfr-changed-last' => $times[$fileName] === $lastTime])>
                    <header class="dfr-line flex items-center justify-between border-b px-4 py-2">
                        <h2>
                            {{ $fileName }}
                            @if (isset($progress[$fileName]))
                                <span class="dfr-muted" title="Ticked todo items">{{ $progress[$fileName] }}</span>
                            @endif
                            <time class="dfr-muted" datetime="{{ $time->toIso8601String() }}" title="{{ $time->toIso8601String() }}">{{ $time->diffForHumans() }}</time>
                            @if ($times[$fileName] === $lastTime)
                                <span class="sr-only">Changed last</span>
                            @endif
                        </h2>
                        @if ($editing !== $fileName)
                            <button type="button" wire:click="edit(@js($fileName))" class="dfr-button">Edit</button>
                        @endif
                    </header>

                    @if ($editing === $fileName)
                        @include('dfr::editor')
                    @else
                        @if ($document->frontmatter !== null)
                            <pre class="dfr-line dfr-muted overflow-x-auto border-b px-4 py-2">{{ $document->frontmatter }}</pre>
                        @endif
                        {{-- RenderBody escapes HTML in a file, so it cannot run in the browser. --}}
                        <div class="dfr-markdown px-4 py-4">
                            {!! $bodies[$fileName] !!}
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    </div>

    @include('dfr::commit-list')
    @include('dfr::commit-panel')
</div>
