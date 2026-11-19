<?php

declare(strict_types=1);

namespace Domain\Dfr\Livewire;

use Domain\Dfr\Actions\ListThings;
use Domain\Dfr\Contracts\SearchByMeaning;
use Domain\Dfr\Data\MeaningMatch;
use Domain\Dfr\ValueObjects\Qmd;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use RuntimeException;

/**
 * The search by meaning under the word search results. It runs only when the user asks for it, because it takes seconds.
 *
 * The sidebar gives each search text its own copy of this component. A poll of the sidebar does not render it again, so the search does not run again.
 * The session keeps the matches of the last search, so a new page shows them without a new search.
 */
class MeaningSearch extends Component
{
    #[Locked]
    public string $text = '';

    public bool $started = false;

    public function mount(): void
    {
        $this->started = $this->lastSearch() !== null;
    }

    public function start(): void
    {
        $this->started = true;
    }

    public function render(Qmd $qmd, SearchByMeaning $searchByMeaning, ListThings $listThings): View
    {
        if ($qmd->path === null) {
            return view('dfr::meaning-search', ['installed' => false]);
        }

        if (! $this->started) {
            return view('dfr::meaning-search', ['installed' => true, 'groups' => null, 'error' => null]);
        }

        $names = [];

        foreach ($listThings() as $thing) {
            $names[$thing->id] = $thing->name;
        }

        $matches = $this->lastSearch();
        $error = null;

        if ($matches === null) {
            try {
                $matches = $searchByMeaning($this->text);
                $this->saveLastSearch($matches);
            } catch (RuntimeException $exception) {
                $matches = [];
                $error = $exception->getMessage();
            }
        }

        // The matches of one thing go together, in the order of the best match of each thing.
        $groups = [];

        foreach ($matches as $match) {
            // The index can still hold a thing that is gone.
            if (isset($names[$match->thingId])) {
                $groups[$match->thingId][] = $match;
            }
        }

        return view('dfr::meaning-search', ['installed' => true, 'groups' => $groups, 'names' => $names, 'error' => $error]);
    }

    /**
     * The session stores its values as JSON, so the matches go in as plain lists.
     *
     * @param  list<MeaningMatch>  $matches
     */
    private function saveLastSearch(array $matches): void
    {
        session()->put('dfr.meaning', [
            'text' => $this->text,
            'matches' => array_map(fn (MeaningMatch $match) => [$match->thingId, $match->file, $match->line, $match->text, $match->score], $matches),
        ]);
    }

    /**
     * The matches of the last search, or null when the last search had another text.
     *
     * @return list<MeaningMatch>|null
     */
    private function lastSearch(): ?array
    {
        $last = session('dfr.meaning');

        if (data_get($last, 'text') !== $this->text || ! is_array(data_get($last, 'matches'))) {
            return null;
        }

        $matches = [];

        foreach (data_get($last, 'matches') as $match) {
            // A session from an older version of the app can hold another shape.
            if (! is_array($match) || ! is_string($match[0] ?? null) || ! is_string($match[1] ?? null) || ! is_int($match[2] ?? null) || ! is_string($match[3] ?? null) || ! is_numeric($match[4] ?? null)) {
                return null;
            }

            // JSON gives a score of 1.0 back as 1.
            $matches[] = new MeaningMatch($match[0], $match[1], $match[2], $match[3], (float) $match[4]);
        }

        return $matches;
    }
}
