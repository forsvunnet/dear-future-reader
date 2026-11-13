<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Lists the projects of the things, the project with the most recently active thing first.
 */
class ProjectsByRecentActivity
{
    /**
     * @param  array<string, list<string>>  $projects  The project paths of each thing, by ID.
     * @param  array<string, int>  $lastActive  The Unix time of the last command on each thing, by ID.
     * @return list<string> The project paths, each one once.
     */
    public function __invoke(array $projects, array $lastActive): array
    {
        $newest = [];

        foreach ($projects as $id => $paths) {
            foreach ($paths as $path) {
                $newest[$path] = max($newest[$path] ?? 0, $lastActive[$id] ?? 0);
            }
        }

        arsort($newest);

        return array_map(strval(...), array_keys($newest));
    }
}
