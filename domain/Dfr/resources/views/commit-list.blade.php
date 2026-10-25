@if ($commits !== [])
    <aside class="dfr-line border-t md:sticky md:top-0 md:h-screen md:w-72 md:shrink-0 md:overflow-y-auto md:border-t-0 md:border-l">
        <header class="dfr-line dfr-title border-b px-4 py-3">Commits</header>
        <nav aria-label="Commits">
            <ul>
                @foreach ($commits as $index => $commit)
                    @php($header = $headers[$index])
                    <li wire:key="commit-{{ $commit->hash }}" class="dfr-line border-b">
                        <button
                            type="button"
                            wire:click="showCommit(@js($commit->hash))"
                            aria-current="{{ $commit->hash === $openCommit ? 'true' : 'false' }}"
                            class="dfr-sidebar-link block w-full px-4 py-2 text-left"
                        >
                            <span class="block">{{ $header?->subject ?? 'The repository no longer has this commit.' }}</span>
                            <span class="dfr-muted block">
                                {{ substr($commit->hash, 0, 7) }} · {{ basename($commit->repository) }}
                                @if ($header !== null)
                                    @php($time = \Illuminate\Support\Carbon::parse($header->time))
                                    · <time datetime="{{ $header->time }}" title="{{ $header->time }}">{{ $time->diffForHumans() }}</time>
                                @endif
                            </span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </nav>
    </aside>
@endif
