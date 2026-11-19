<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * A context file that the search by meaning finds, with the part of the file that matches best.
 */
readonly class MeaningMatch
{
    /**
     * @param  int  $line  The first line of the part, from 1.
     */
    public function __construct(
        public string $thingId,
        public string $file,
        public int $line,
        public string $text,
    ) {}
}
