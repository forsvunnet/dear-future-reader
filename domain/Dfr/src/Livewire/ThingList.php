<?php

declare(strict_types=1);

namespace Domain\Dfr\Livewire;

use Domain\Dfr\Actions\LastActive;
use Domain\Dfr\Actions\ListThings;
use Domain\Dfr\Actions\SortAsTree;
use Domain\Dfr\Actions\SortByRecentActivity;
use Domain\Dfr\Actions\ThingProjects;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The sidebar that lists all things, with each child under its parent and the most recently active first.
 */
class ThingList extends Component
{
    /** The ID of the thing that the page shows, or null on the start page. */
    #[Locked]
    public ?string $current = null;

    public function render(ListThings $listThings, LastActive $lastActive, SortByRecentActivity $sortByRecentActivity, SortAsTree $sortAsTree, ThingProjects $thingProjects): View
    {
        $things = $listThings();
        $times = [];
        $projects = [];

        foreach ($things as $thing) {
            $times[$thing->id] = $lastActive($thing);
            $projects[$thing->id] = array_map(basename(...), $thingProjects($thing));
        }

        return view('dfr::thing-list', [
            'rows' => $sortAsTree($sortByRecentActivity($things, $times)),
            'times' => $times,
            'projects' => $projects,
        ]);
    }
}
