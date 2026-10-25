<?php

declare(strict_types=1);

namespace Domain\Dfr\Livewire;

use Domain\Dfr\Actions\ListThings;
use Domain\Dfr\Actions\SortAsTree;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * The page that lists all things, with each child under its parent.
 */
#[Layout('dfr::layout')]
#[Title('Things')]
class ThingList extends Component
{
    public function render(ListThings $listThings, SortAsTree $sortAsTree): View
    {
        return view('dfr::thing-list', [
            'rows' => $sortAsTree($listThings()),
        ]);
    }
}
