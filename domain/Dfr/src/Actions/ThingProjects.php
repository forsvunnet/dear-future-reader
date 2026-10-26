<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;

/**
 * Finds the projects of a thing: the git repository where it was made, the added projects and the repositories of its commits.
 */
class ThingProjects
{
    /**
     * @return list<string> The paths of the repositories, each one once.
     */
    public function __invoke(Thing $thing): array
    {
        $paths = [
            ...($thing->repository === null ? [] : [$thing->repository]),
            ...$thing->projects,
            ...array_map(fn ($commit) => $commit->repository, $thing->commits),
        ];

        return array_values(array_unique($paths));
    }
}
