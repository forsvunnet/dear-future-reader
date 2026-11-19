<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Contracts\FindLines;
use Domain\Dfr\Data\FoundLine;

/**
 * Finds lines in files with plain PHP. The search uses it when ripgrep is not installed.
 */
class FindLinesWithPhp implements FindLines
{
    public function __invoke(array $paths, array $words): array
    {
        $lines = [];

        foreach ($paths as $path) {
            $fileLines = preg_split('/\R/', (string) file_get_contents($path)) ?: [];

            foreach ($fileLines as $index => $text) {
                foreach ($words as $word) {
                    if (mb_stripos($text, $word) !== false) {
                        $lines[] = new FoundLine($path, $index + 1, $text);

                        break;
                    }
                }
            }
        }

        return $lines;
    }
}
