<?php

declare(strict_types=1);

namespace Domain\Dfr\Livewire;

use Domain\Dfr\Actions\ListThings;
use Domain\Dfr\Contracts\SearchByMeaning;
use Domain\Dfr\Data\LastMeaningSearch;
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

        $matches = $this->lastSearch()?->matches;
        $error = null;

        if ($matches === null) {
            try {
                $matches = $searchByMeaning($this->text);
                session()->put('dfr.meaning', new LastMeaningSearch($this->text, $matches));
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

    private function lastSearch(): ?LastMeaningSearch
    {
        $last = session('dfr.meaning');

        return $last instanceof LastMeaningSearch && $last->text === $this->text ? $last : null;
    }
}
