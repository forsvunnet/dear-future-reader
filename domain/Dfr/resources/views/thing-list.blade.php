<div>
    <h1 class="dfr-title mb-6">Things</h1>

    @if ($rows === [])
        <p>There are no things. Make one with <code>dfr init "name"</code>.</p>
    @else
        <table class="w-full text-left">
            <thead class="dfr-line border-b">
                <tr>
                    <th class="dfr-label py-2 pr-4">ID</th>
                    <th class="dfr-label py-2 pr-4">Name</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr class="dfr-line border-b">
                        <td class="dfr-muted py-2 pr-4">{{ $row->thing->id }}</td>
                        {{-- Each level of the tree has more indent. --}}
                        <td class="py-2 pr-4" style="padding-left: {{ $row->depth * 2 }}ch">
                            <a href="{{ route('dfr.thing', $row->thing->id) }}">{{ $row->thing->name }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
