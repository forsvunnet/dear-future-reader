<div>
    @if ($installed)
        @if ($matches === null)
            <div class="px-4 py-2">
                <button type="button" wire:click="start" wire:loading.attr="disabled" class="dfr-button">Search with QMD</button>
                <p wire:loading wire:target="start" role="status" class="dfr-muted">QMD searches by meaning. This takes a few seconds.</p>
            </div>
        @else
            <p class="dfr-label dfr-line border-b px-4 py-2">QMD results</p>
            @if ($error !== null)
                <p role="alert" class="px-4 py-2">The QMD search failed: {{ $error }}</p>
            @elseif ($matches === [])
                <p class="px-4 py-2">QMD found nothing. Open the cog in the bottom right to make or update the index.</p>
            @else
                <ul>
                    @foreach ($matches as $match)
                        <li class="dfr-line border-b">
                            <a href="{{ route('dfr.thing', $match->thingId) }}" class="dfr-sidebar-link block px-4 py-2">
                                <span class="block">{{ $names[$match->thingId] }}</span>
                                <span class="dfr-muted block">{{ $match->thingId }} · {{ $match->file }}:{{ $match->line }}</span>
                                <span class="dfr-muted block truncate" title="{{ $match->text }}">{{ $match->text }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        @endif
    @endif
</div>
