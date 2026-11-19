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
 */
class MeaningSearch extends Component
{
    #[Locked]
    public string $text = '';

    public bool $started = false;

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
            return view('dfr::meaning-search', ['installed' => true, 'matches' => null, 'error' => null]);
        }

        $names = [];

        foreach ($listThings() as $thing) {
            $names[$thing->id] = $thing->name;
        }

        try {
            // The index can still hold a thing that is gone.
            $matches = array_values(array_filter($searchByMeaning($this->text), fn (MeaningMatch $match) => isset($names[$match->thingId])));
            $error = null;
        } catch (RuntimeException $exception) {
            $matches = [];
            $error = $exception->getMessage();
        }

        return view('dfr::meaning-search', ['installed' => true, 'matches' => $matches, 'names' => $names, 'error' => $error]);
    }
}
