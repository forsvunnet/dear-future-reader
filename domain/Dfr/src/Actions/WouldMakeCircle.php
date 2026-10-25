<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Checks if a thing would be its own parent at some level when it gets the given parent.
 */
class WouldMakeCircle
{
    public function __construct(
        private ReadThing $readThing,
    ) {}

    public function __invoke(string $id, string $parentId): bool
    {
        $visited = [];

        // Walk up from the new parent. The visited list stops the walk when the data already has a circle.
        for ($current = $parentId; $current !== null && ! isset($visited[$current]); $current = ($this->readThing)($current)?->parent) {
            if ($current === $id) {
                return true;
            }

            $visited[$current] = true;
        }

        return false;
    }
}
