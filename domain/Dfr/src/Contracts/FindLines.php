<?php

declare(strict_types=1);

namespace Domain\Dfr\Contracts;

use Domain\Dfr\Data\FoundLine;

/**
 * Finds the lines in files that contain one or more of the words, with case ignored.
 */
interface FindLines
{
    /**
     * @param  list<string>  $paths  The full paths of the files to search.
     * @param  list<string>  $words  The words to find, as plain text.
     * @return list<FoundLine>
     */
    public function __invoke(array $paths, array $words): array;
}
