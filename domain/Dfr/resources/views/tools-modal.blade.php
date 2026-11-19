<div>
    <button type="button" wire:click="show" aria-label="Search tools" title="Search tools" class="dfr-cog dfr-line dfr-body border">⚙</button>

    @if ($open)
        {{-- A click on Close, outside the modal or on Escape closes it. The poll shows the progress while an embed runs. --}}
        <div
            class="fixed inset-0 z-30 flex p-8"
            wire:click.self="close"
            x-data
            x-on:keydown.escape.window="$wire.close()"
            @if ($embedRuns) wire:poll.5s @endif
        >
            <article role="dialog" aria-modal="true" aria-labelledby="dfr-tools-title" class="dfr-body dfr-line flex w-full flex-col overflow-y-auto border">
                <header class="dfr-line flex items-center justify-between border-b">
                    <h2 id="dfr-tools-title" class="dfr-title truncate p-2">Search tools</h2>
                    <button type="button" wire:click="close" autofocus class="dfr-line dfr-label self-stretch border-l px-4">Close</button>
                </header>

                <section class="dfr-line border-b p-2">
                    <h3 class="dfr-label">Tools</h3>
                    <ul>
                        @foreach ($tools as $tool)
                            <li>
                                {{ $tool->name }}:
                                @if ($tool->path === null)
                                    not installed
                                @else
                                    {{ $tool->path }} · {{ $tool->version ?? 'unknown version' }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <p class="dfr-muted">The word search uses rg, or PHP when rg is not installed. The search by meaning needs qmd.</p>
                </section>

                @if ($installed)
                    <section class="dfr-line border-b p-2">
                        <h3 class="dfr-label">QMD index</h3>
                        <div class="flex flex-wrap gap-2 py-2">
                            <button type="button" wire:click="makeIndex" wire:loading.attr="disabled" class="dfr-button" title="Add the things to the index and index the files">Make index</button>
                            <button type="button" wire:click="updateIndex" wire:loading.attr="disabled" class="dfr-button" title="Index the new and changed files">Update</button>
                            <button type="button" wire:click="embed" wire:loading.attr="disabled" @disabled($embedRuns) class="dfr-button" title="Make the vectors for the search by meaning. It runs in the background.">Embed</button>
                        </div>
                        <p wire:loading wire:target="makeIndex,updateIndex,embed" role="status" class="dfr-muted">qmd runs…</p>
                        @if ($output !== '')
                            <pre @if ($failed) role="alert" @endif class="dfr-log">{{ $output }}</pre>
                        @endif
                    </section>

                    @if ($embedRuns || $embedLog !== '')
                        <section class="dfr-line border-b p-2">
                            <h3 class="dfr-label">Embed · {{ $embedRuns ? 'runs' : 'done' }}</h3>
                            <pre class="dfr-log">{{ $embedLog }}</pre>
                        </section>
                    @endif

                    <section class="p-2">
                        <h3 class="dfr-label">Index status</h3>
                        <pre class="dfr-log">{{ $indexStatus }}</pre>
                    </section>
                @endif
            </article>
        </div>
    @endif
</div>
