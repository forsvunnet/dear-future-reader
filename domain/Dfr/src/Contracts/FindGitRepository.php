<?php

declare(strict_types=1);

namespace Domain\Dfr\Contracts;

/**
 * Finds the git repository that contains a folder.
 */
interface FindGitRepository
{
    /**
     * @return ?string The path of the top folder of the repository, or null when the folder is not in a repository.
     */
    public function __invoke(string $folder): ?string;
}
