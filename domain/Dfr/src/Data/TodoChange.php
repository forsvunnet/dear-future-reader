<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * The new content of the files that a todo command changes, or the errors that stop the change.
 */
readonly class TodoChange
{
    /**
     * @param  array<string, string>  $files  The new content for each filename.
     * @param  list<string>  $errors
     */
    public function __construct(
        public array $files,
        public array $errors,
    ) {}
}
