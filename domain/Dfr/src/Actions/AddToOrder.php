<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;

/**
 * Adds a file to the end of the order of the thing, when it is not in the order yet.
 * Call it only while the thing is locked.
 */
class AddToOrder
{
    public function __construct(
        private ReadThing $readThing,
        private SaveThing $saveThing,
    ) {}

    public function __invoke(Thing $thing, string $fileName): void
    {
        // Read the thing again, because another session can have changed it before the lock.
        $current = ($this->readThing)($thing->id) ?? $thing;

        if (in_array($fileName, $current->files, true)) {
            return;
        }

        ($this->saveThing)(new Thing(
            id: $current->id,
            folder: $current->folder,
            name: $current->name,
            parent: $current->parent,
            repository: $current->repository,
            projects: $current->projects,
            commits: $current->commits,
            files: [...$current->files, $fileName],
        ));
    }
}
