<div>
    @if ($installed)
        @if ($groups === null)
            <div class="px-4 py-2">
                <button type="button" wire:click="start" wire:loading.attr="disabled" class="dfr-button">Search with QMD</button>
                <p wire:loading wire:target="start" role="status" class="dfr-muted">QMD searches by meaning. This takes about ten seconds.</p>
            </div>
        @else
            <p class="dfr-label dfr-line border-b px-4 py-2">QMD results</p>
            @if ($error !== null)
                <p role="alert" class="px-4 py-2">The QMD search failed: {{ $error }}</p>
            @elseif ($groups === [])
                <p class="px-4 py-2">QMD found nothing. The cog in the bottom right shows the state of the QMD index.</p>
            @else
                <ul>
                    @foreach ($groups as $thingId => $matches)
                        <li class="dfr-line border-b">
                            <a href="{{ route('dfr.thing', $thingId) }}" class="dfr-sidebar-link block px-4 py-2">
                                <span class="block">{{ $names[$thingId] }}</span>
                                <span class="dfr-muted block">{{ $thingId }} · {{ count($matches) }} {{ count($matches) === 1 ? 'part' : 'parts' }}</span>
                                @foreach ($matches as $match)
                                    <span class="mt-1 block">
                                        <span class="dfr-muted block">{{ $match->file }}:{{ $match->line }} · score {{ number_format($match->score, 2) }}</span>
                                        {{-- ponytail: three lines keep the list short. QMD cuts the snippet at about 300 characters. --}}
                                        <span class="dfr-muted line-clamp-3 block whitespace-pre-line" title="{{ $match->text }}">{{ $match->text }}</span>
                                    </span>
                                @endforeach
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        @endif
    @endif
</div>
