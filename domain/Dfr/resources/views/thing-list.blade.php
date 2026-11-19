{{-- The poll keeps the times and the order up to date while sessions work. --}}
<nav aria-label="Things" wire:poll.30s>
    <div class="dfr-line border-b px-4 py-2">
        <label for="dfr-search" class="sr-only">Search the things</label>
        <input id="dfr-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Search" autocomplete="off" class="dfr-input w-full">
    </div>
    @if ($level === 'search')
        @if ($error !== null)
            <p role="alert" class="px-4 py-2">The search failed: {{ $error }}</p>
        @elseif ($results === [])
            <p class="px-4 py-2">No thing contains all the words.</p>
        @else
            <ul>
                @foreach ($results as $result)
                    <li class="dfr-line border-b">
                        <a
                            href="{{ route('dfr.thing', $result->id) }}"
                            @if ($result->id === $current) aria-current="page" @endif
                            class="dfr-sidebar-link block px-4 py-2"
                        >
                            <span class="block">{{ $result->name }}</span>
                            <span class="dfr-muted block">
                                {{ $result->id }}
                                @if ($result->matches !== [])
                                    · {{ count($result->matches) }} {{ count($result->matches) === 1 ? 'line' : 'lines' }}
                                @endif
                            </span>
                            {{-- ponytail: three lines keep the list short. To see all lines, run dfr search. --}}
                            @foreach (array_slice($result->matches, 0, 3) as $match)
                                <span class="dfr-muted block truncate" title="{{ $match->text }}">{{ $match->file }}:{{ $match->line }} {{ trim($match->text) }}</span>
                            @endforeach
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    @elseif ($level === 'projects')
        @if (! $hasThings)
            <p class="px-4 py-2">There are no things. Make one with <code>dfr init "name"</code>.</p>
        @else
            <ul>
                <li class="dfr-line border-b">
                    <button type="button" wire:click="selectProject(null)" class="dfr-sidebar-link block w-full cursor-pointer px-4 py-2 text-left">All projects</button>
                </li>
                @foreach ($projectPaths as $path)
                    <li class="dfr-line border-b">
                        <button type="button" wire:click="selectProject(@js($path))" title="{{ $path }}" class="dfr-sidebar-link block w-full cursor-pointer px-4 py-2 text-left">{{ basename($path) }}</button>
                    </li>
                @endforeach
            </ul>
        @endif
    @else
        <div class="dfr-line border-b">
            <button type="button" wire:click="showProjects" class="dfr-sidebar-link block w-full cursor-pointer px-4 py-2 text-left">
                <span class="dfr-muted block">← Projects</span>
                <span class="block" @if ($project !== null) title="{{ $project }}" @endif>{{ $project === null ? 'All projects' : basename($project) }}</span>
            </button>
        </div>
        @if ($rows === [])
            <p class="px-4 py-2">There are no things in this project.</p>
        @else
            <ul>
                @foreach ($rows as $row)
                    @php($time = \Illuminate\Support\Carbon::createFromTimestamp($times[$row->thing->id]))
                    <li class="dfr-line border-b">
                        {{-- Each level of the tree has more indent. --}}
                        <a
                            href="{{ route('dfr.thing', $row->thing->id) }}"
                            @if ($row->thing->id === $current) aria-current="page" @endif
                            class="dfr-sidebar-link block py-2 pr-4"
                            style="padding-left: calc(1rem + {{ $row->depth * 2 }}ch)"
                        >
                            <span class="block">{{ $row->thing->name }}</span>
                            <span class="dfr-muted block">
                                {{ $row->thing->id }} · <time datetime="{{ $time->toIso8601String() }}" title="{{ $time->toIso8601String() }}">{{ $time->diffForHumans() }}</time>
                            </span>
                            @if ($projects[$row->thing->id] !== [])
                                <span class="dfr-muted block">{{ implode(', ', $projects[$row->thing->id]) }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</nav>
