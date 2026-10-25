{{-- The poll keeps the times and the order up to date while sessions work. --}}
<nav aria-label="Things" wire:poll.30s>
    @if ($rows === [])
        <p class="px-4 py-2">There are no things. Make one with <code>dfr init "name"</code>.</p>
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
</nav>
