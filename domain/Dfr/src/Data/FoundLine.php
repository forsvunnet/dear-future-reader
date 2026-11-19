<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * A line in a file that contains one or more of the search words.
 */
readonly class FoundLine
{
    /**
     * @param  string  $path  The full path of the file.
     * @param  int  $line  The line number, from 1.
     */
    public function __construct(
        public string $path,
        public int $line,
        public string $text,
    ) {}
}
