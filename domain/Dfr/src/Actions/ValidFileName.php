<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Checks that a name can be a context file: no path, and no "." at the start.
 */
class ValidFileName
{
    public function __invoke(string $name): bool
    {
        return $name !== ''
            && ! str_starts_with($name, '.')
            && ! str_contains($name, '/')
            && ! str_contains($name, '\\')
            && ! str_contains($name, "\0");
    }
}
