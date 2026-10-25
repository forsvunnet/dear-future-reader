@if ($openCommit !== null)
    {{-- The panel covers the files and the commits. The list of things stays visible. --}}
    <section
        wire:key="panel-{{ $openCommit }}"
        role="dialog"
        aria-modal="true"
        aria-labelledby="commit-title"
        tabindex="-1"
        x-data
        x-init="$el.focus()"
        x-on:keydown.escape.window="$wire.closeCommit()"
        class="dfr-panel dfr-line fixed inset-y-0 right-0 left-0 z-10 overflow-y-auto md:left-72 md:border-l"
    >
        <header class="dfr-panel dfr-line sticky top-0 flex items-center justify-between gap-4 border-b px-4 py-3">
            <h2 id="commit-title" class="dfr-title min-w-0">{{ $openHeader?->subject ?? substr($openCommit, 0, 7) }}</h2>
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
                    {{-- Like GitHub, a file with many changed lines is closed until you open it. --}}
                    <details
                        wire:key="change-{{ $loop->index }}"
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
    </section>
@endif
