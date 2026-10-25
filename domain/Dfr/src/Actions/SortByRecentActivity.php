<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;

/**
 * Sorts the things by their last activity, the most recent first.
 * A parent counts as active when a child at any level is active.
 */
class SortByRecentActivity
{
    /**
     * @param  list<Thing>  $things
     * @param  array<string, int>  $lastActive  The Unix time of the last command on each thing, by ID.
     * @return list<Thing>
     */
    public function __invoke(array $things, array $lastActive): array
    {
        $parents = [];

        foreach ($things as $thing) {
            $parents[$thing->id] = $thing->parent;
        }

        $newest = $lastActive;

        foreach ($things as $thing) {
            $visited = [];

            // The visited list stops the walk when the parents make a circle.
            for ($id = $thing->parent; $id !== null && isset($newest[$id]) && ! isset($visited[$id]); $id = $parents[$id] ?? null) {
                $visited[$id] = true;
                $newest[$id] = max($newest[$id], $lastActive[$thing->id] ?? 0);
            }
        }

        usort($things, fn (Thing $a, Thing $b) => ($newest[$b->id] ?? 0) <=> ($newest[$a->id] ?? 0));

        return $things;
    }
}
