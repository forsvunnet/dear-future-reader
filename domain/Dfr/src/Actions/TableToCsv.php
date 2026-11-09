<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use RuntimeException;

/**
 * Writes rows of cells as CSV. A cell with a comma, a quote or a line break is in quotes.
 */
class TableToCsv
{
    /**
     * @param  list<list<string>>  $rows
     */
    public function __invoke(array $rows): string
    {
        $handle = fopen('php://memory', 'r+');

        if ($handle === false) {
            throw new RuntimeException('Cannot open memory to write the CSV.');
        }

        foreach ($rows as $row) {
            // An empty escape character gives the quoting of RFC 4180.
            fputcsv($handle, $row, ',', '"', '');
        }

        rewind($handle);
        $csv = (string) stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
