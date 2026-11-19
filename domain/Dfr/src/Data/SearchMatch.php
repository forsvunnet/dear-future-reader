<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * A line in a context file that contains one or more of the search words.
 */
readonly class SearchMatch
{
    /**
     * @param  int  $line  The line number, from 1.
     */
    public function __construct(
        public string $file,
        public int $line,
        public string $text,
    ) {}
}
