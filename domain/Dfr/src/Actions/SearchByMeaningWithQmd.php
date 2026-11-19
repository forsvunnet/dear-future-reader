<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Contracts\SearchByMeaning;
use Domain\Dfr\Data\MeaningMatch;
use RuntimeException;

/**
 * Searches the QMD index of the things with `qmd query`.
 */
class SearchByMeaningWithQmd implements SearchByMeaning
{
    public function __construct(
        private RunQmd $runQmd,
    ) {}

    public function __invoke(string $text): array
    {
        // --no-rerank: the rerank makes a query about three times slower.
        $results = json_decode(($this->runQmd)(['query', $text, '--no-rerank', '--format', 'json', '-n', '20', '-c', 'things']), true);

        if (! is_array($results)) {
            throw new RuntimeException('qmd gave no JSON.');
        }

        $matches = [];

        foreach ($results as $result) {
            $file = data_get($result, 'file');
            $line = data_get($result, 'line');
            $snippet = data_get($result, 'snippet');

            // The file is like qmd://things/abc/plan.md?index=dfr. The first folder is the thing ID.
            if (! is_string($file) || ! preg_match('#^qmd://things/([^/]+)/([^/?]+)#', $file, $parts)) {
                continue;
            }

            $matches[] = new MeaningMatch($parts[1], $parts[2], is_int($line) ? $line : 1, is_string($snippet) ? $this->firstLine($snippet) : '');
        }

        return $matches;
    }

    /**
     * The first line of the snippet with text. The snippet starts with a line like "@@ -1,4 @@ (0 before, 1 after)".
     */
    private function firstLine(string $snippet): string
    {
        foreach (explode("\n", $snippet) as $line) {
            if (trim($line) !== '' && ! str_starts_with($line, '@@ ')) {
                return trim($line);
            }
        }

        return '';
    }
}
