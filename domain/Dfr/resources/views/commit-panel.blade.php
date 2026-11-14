@if ($openCommit !== null)
    {{-- On a narrow screen the panel covers the page. On a wide screen the panel takes the place of the commits, next to the files. --}}
    {{-- Alpine owns the width, so wire:ignore.self keeps it when the page polls. --}}
    <section
        wire:key="panel-{{ $openCommit }}"
        wire:ignore.self
        role="dialog"
        aria-labelledby="commit-title"
        tabindex="-1"
        x-data="splitter"
        x-init="$el.focus({ preventScroll: true })"
        x-bind:aria-modal="wide ? 'false' : 'true'"
        x-bind:style="wide && { width: width + '%' }"
        x-on:keydown.escape.window="$wire.closeCommit()"
        class="dfr-panel dfr-line fixed inset-0 z-10 flex md:sticky md:h-screen md:shrink-0 md:border-l"
    >
        <div
            wire:ignore
            role="separator"
            tabindex="0"
            aria-orientation="vertical"
            aria-label="Width of the commit panel"
            x-bind:aria-valuenow="width"
            x-bind:aria-valuemin="min"
            x-bind:aria-valuemax="max"
            x-on:pointerdown="start"
            x-on:pointermove="drag"
            x-on:keydown="key"
            class="dfr-splitter hidden md:block"
        ></div>

        <div class="min-w-0 flex-1 overflow-y-auto">
            <header class="dfr-column-header dfr-line justify-between">
                <h2 id="commit-title" class="dfr-title min-w-0 truncate">{{ $openHeader?->subject ?? substr($openCommit, 0, 7) }}</h2>
                <button type="button" wire:click="closeCommit" class="dfr-button shrink-0">Close</button>
            </header>

            <div class="px-4 py-6">
                @if ($openHeader === null)
                    <p>The repository no longer has this commit.</p>
                @else
                    <dl class="mb-6 grid grid-cols-[auto_1fr] gap-x-4">
                        <dt class="dfr-label">Hash</dt>
                        <dd class="break-all">{{ $openCommit }}</dd>
                        <dt class="dfr-label">Author</dt>
                        <dd>{{ $openHeader->author }}</dd>
                        <dt class="dfr-label">Time</dt>
                        <dd><time datetime="{{ $openHeader->time }}">{{ $openHeader->time }}</time></dd>
                        <dt class="dfr-label">Project</dt>
                        <dd>{{ $openHeader->commit->repository }}</dd>
                    </dl>

                    @if ($openHeader->body !== '')
                        <pre class="mb-6 whitespace-pre-wrap">{{ $openHeader->body }}</pre>
                    @endif

                    @foreach ($openChanges as $change)
                        {{-- Like GitHub, a file with many changed lines is closed until you open it. wire:ignore.self keeps the open state that the user set when the page polls. --}}
                        <details
                            wire:key="change-{{ $loop->index }}"
                            wire:ignore.self
                            @if ($change->added === null || $change->added + $change->deleted <= 400) open @endif
                            class="dfr-line mb-6 border"
                        >
                            <summary class="dfr-line cursor-pointer px-4 py-2">
                                @if ($change->oldPath !== null)
                                    {{ $change->oldPath }} → {{ $change->path }}
                                @else
                                    {{ $change->path }}
                                @endif
                                @if ($change->added !== null)
                                    <span class="dfr-muted">+{{ $change->added }} −{{ $change->deleted }}</span>
                                @endif
                            </summary>

                            @if ($change->added === null)
                                <p class="dfr-muted dfr-line border-t px-4 py-2">Binary file.</p>
                            @elseif ($change->patch === '')
                                <p class="dfr-muted dfr-line border-t px-4 py-2">The content did not change.</p>
                            @else
                                {{-- ponytail: a closed file still sends its whole patch. Load the patch on open when a commit is too large for the page. --}}
                                <div x-data x-highlight data-diff data-file="{{ $change->path }}" wire:ignore class="dfr-diff dfr-line overflow-x-auto border-t"><pre><code>{{ $change->patch }}</code></pre></div>
                            @endif
                        </details>
                    @endforeach
                @endif
            </div>
        </div>
    </section>
@endif
