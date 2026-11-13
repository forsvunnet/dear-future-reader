<?php

declare(strict_types=1);

namespace Domain\Dfr\Livewire;

use Domain\Dfr\Actions\LastActive;
use Domain\Dfr\Actions\ListThings;
use Domain\Dfr\Actions\ProjectsByRecentActivity;
use Domain\Dfr\Actions\SortAsTree;
use Domain\Dfr\Actions\SortByRecentActivity;
use Domain\Dfr\Actions\ThingProjects;
use Domain\Dfr\Data\Thing;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The sidebar in two levels. The first level lists the projects. The second level lists the things of the selected project,
 * with each child under its parent and the most recently active first.
 *
 * The session keeps the selected project, so the sidebar stays on the same level when a new page opens.
 * Without the session key the sidebar shows the projects. A null value selects all projects.
 */
class ThingList extends Component
{
    /** The ID of the thing that the page shows, or null on the start page. */
    #[Locked]
    public ?string $current = null;

    public function selectProject(?string $path): void
    {
        session()->put('dfr.project', $path);
    }

    public function showProjects(): void
    {
        session()->forget('dfr.project');
    }

    public function render(ListThings $listThings, LastActive $lastActive, SortByRecentActivity $sortByRecentActivity, SortAsTree $sortAsTree, ThingProjects $thingProjects, ProjectsByRecentActivity $projectsByRecentActivity): View
    {
        $things = $listThings();
        $times = [];
        $paths = [];

        foreach ($things as $thing) {
            $times[$thing->id] = $lastActive($thing);
            $paths[$thing->id] = $thingProjects($thing);
        }

        // session()->has() is false for a null value, so it cannot tell "all projects" from "no selection".
        if (! session()->exists('dfr.project')) {
            return view('dfr::thing-list', [
                'level' => 'projects',
                'hasThings' => $things !== [],
                'projectPaths' => $projectsByRecentActivity($paths, $times),
            ]);
        }

        $project = session('dfr.project');

        if ($project !== null) {
            $things = array_values(array_filter($things, fn (Thing $thing) => in_array($project, $paths[$thing->id], true)));
        }

        return view('dfr::thing-list', [
            'level' => 'things',
            'project' => $project,
            'rows' => $sortAsTree($sortByRecentActivity($things, $times)),
            'times' => $times,
            'projects' => array_map(fn (array $list) => array_map(basename(...), $list), $paths),
        ]);
    }
}
