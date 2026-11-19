<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * The text and the matches of the last search by meaning. The session keeps it, so a new page shows the matches without a new search.
 */
readonly class LastMeaningSearch
{
    /**
     * @param  list<MeaningMatch>  $matches
     */
    public function __construct(
        public string $text,
        public array $matches,
    ) {}
}
