<div>
    <h1 class="mb-4 text-2xl font-semibold">Things</h1>

    @if ($rows === [])
        <p>There are no things. Make one with <code>dfr init "name"</code>.</p>
    @else
        <table class="w-full text-left">
            <thead class="border-b border-stone-300 text-sm text-stone-500 dark:border-stone-700">
                <tr>
                    <th class="py-2 pr-4 font-medium">ID</th>
                    <th class="py-2 pr-4 font-medium">Name</th>
                    <th class="py-2 font-medium">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr class="border-b border-stone-200 dark:border-stone-800">
                        <td class="py-2 pr-4 font-mono text-sm">{{ $row->thing->id }}</td>
                        {{-- Each level of the tree has more indent. --}}
                        <td class="py-2 pr-4" style="padding-left: {{ $row->depth * 1.5 }}rem">
                            <a href="{{ route('dfr.thing', $row->thing->id) }}" class="underline">{{ $row->thing->name }}</a>
                        </td>
                        <td class="py-2">{{ $row->thing->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
