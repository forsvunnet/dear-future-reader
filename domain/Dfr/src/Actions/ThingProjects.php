<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;

/**
 * Finds the projects of a thing: the git repository where it was made and the repositories of its commits.
 */
class ThingProjects
{
    /**
     * @return list<string> The paths of the repositories, each one once.
     */
    public function __invoke(Thing $thing): array
    {
        $paths = array_map(fn ($commit) => $commit->repository, $thing->commits);

        if ($thing->repository !== null) {
            array_unshift($paths, $thing->repository);
        }

        return array_values(array_unique($paths));
    }
}
