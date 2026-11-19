<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * A thing that matches the search, with the lines in its files that contain one or more of the words.
 */
readonly class ThingSearchResult
{
    /**
     * @param  list<SearchMatch>  $matches  In the order of the files.
     */
    public function __construct(
        public string $id,
        public string $name,
        public array $matches,
    ) {}
}
