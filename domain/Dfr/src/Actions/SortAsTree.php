<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;
use Domain\Dfr\Data\TreeRow;

/**
 * Puts each child under its parent. The children keep the order they have in the given list.
 */
class SortAsTree
{
    /**
     * @param  list<Thing>  $things
     * @return list<TreeRow>
     */
    public function __invoke(array $things): array
    {
        $ids = array_flip(array_map(fn (Thing $thing) => $thing->id, $things));
        $children = [];
        $roots = [];

        foreach ($things as $thing) {
            // A thing whose parent is missing goes to the top.
            if ($thing->parent === null || ! isset($ids[$thing->parent])) {
                $roots[] = $thing;

                continue;
            }

            $children[$thing->parent][] = $thing;
        }

        $rows = [];
        $visited = [];
        $this->add($roots, 0, $children, $rows, $visited);

        // Things in a circle have no root above them. Show them at the top, so no thing is lost.
        $this->add($things, 0, $children, $rows, $visited);

        return $rows;
    }

    /**
     * @param  list<Thing>  $things
     * @param  array<string, list<Thing>>  $children
     * @param  list<TreeRow>  $rows
     * @param  array<string, true>  $visited
     */
    private function add(array $things, int $depth, array $children, array &$rows, array &$visited): void
    {
        foreach ($things as $thing) {
            if (isset($visited[$thing->id])) {
                continue;
            }

            $visited[$thing->id] = true;
            $rows[] = new TreeRow($depth, $thing);
            $this->add($children[$thing->id] ?? [], $depth + 1, $children, $rows, $visited);
        }
    }
}
