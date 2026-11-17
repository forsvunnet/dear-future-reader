<?php

declare(strict_types=1);

namespace Domain\Dfr\Contracts;

/**
 * Opens a folder in the file manager of the computer that runs the Web UI.
 */
interface OpenFolder
{
    public function __invoke(string $folder): void;
}
