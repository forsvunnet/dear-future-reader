<div>
    <button type="button" wire:click="show" aria-label="Search tools" title="Search tools" class="dfr-cog dfr-line dfr-body border">
        {{-- The cog is a drawing, because the design gives all text one small size. The path is the cog-6-tooth icon of Heroicons, MIT licence. --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
    </button>

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
                        <p class="dfr-muted">The search by meaning reads this index. The index holds the .md and .log files of the things, but not the archived files.</p>
                        <dl class="grid grid-cols-[auto_1fr] items-start gap-x-2 gap-y-3 py-2">
                            <dt><button type="button" wire:click="makeIndex" wire:loading.attr="disabled" class="dfr-button w-full">Make index</button></dt>
                            <dd>
                                <p>Adds the folder of the things to QMD and reads all their files.</p>
                                @if ($indexState !== null)
                                    <p class="dfr-muted">{{ $indexState->made ? 'The index is made.' : 'The index is not made.' }}</p>
                                @endif
                            </dd>
                            <dt><button type="button" wire:click="updateIndex" wire:loading.attr="disabled" class="dfr-button w-full">Update</button></dt>
                            <dd>
                                <p>Reads the files that are new, changed or removed since the last update.</p>
                                @if ($indexState !== null)
                                    <p class="dfr-muted">
                                        The index has {{ $indexState->files }} {{ $indexState->files === 1 ? 'file' : 'files' }}{{ $indexState->updated === null ? '' : ', updated '.$indexState->updated }}.
                                        The things have {{ $thingFileCount }} {{ $thingFileCount === 1 ? 'file' : 'files' }}{{ $lastChange === null ? '' : ', changed last '.$lastChange->diffForHumans() }}.
                                    </p>
                                @endif
                            </dd>
                            <dt><button type="button" wire:click="embed" wire:loading.attr="disabled" @disabled($embedRuns) class="dfr-button w-full">Embed</button></dt>
                            <dd>
                                <p>Works out the meaning of each file in the index that has no meaning yet. The search by meaning finds only these files. It takes minutes and runs in the background.</p>
                                <p class="dfr-muted">
                                    @if ($embedRuns)
                                        <span class="dfr-spinner" aria-hidden="true"></span>The embed runs now.
                                    @elseif ($indexState !== null)
                                        {{ $indexState->pending === 0 ? 'No file waits.' : $indexState->pending.' '.($indexState->pending === 1 ? 'file waits.' : 'files wait.') }}
                                    @endif
                                </p>
                            </dd>
                        </dl>
                        <p wire:loading wire:target="makeIndex,updateIndex,embed" role="status" class="dfr-muted"><span class="dfr-spinner" aria-hidden="true"></span>qmd runs…</p>
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
