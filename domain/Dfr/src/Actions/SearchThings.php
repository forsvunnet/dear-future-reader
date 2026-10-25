<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\SearchMatch;

/**
 * Finds the lines in the context files of all things that contain a text, with case ignored.
 */
class SearchThings
{
    public function __construct(
        private ListThings $listThings,
        private OrderedFiles $orderedFiles,
    ) {}

    /**
     * @return list<SearchMatch>
     */
    public function __invoke(string $text): array
    {
        $matches = [];

        foreach (($this->listThings)() as $thing) {
            foreach (($this->orderedFiles)($thing) as $fileName) {
                $lines = preg_split('/\R/', (string) file_get_contents("$thing->folder/$fileName")) ?: [];

                foreach ($lines as $index => $line) {
                    if (mb_stripos($line, $text) !== false) {
                        $matches[] = new SearchMatch($thing->id, $fileName, $index + 1, $line);
                    }
                }
            }
        }

        return $matches;
    }
}
