<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Writes rows of cells as TSV. TSV has no escape, so a tab or a line break in a cell becomes a space.
 */
class TableToTsv
{
    /**
     * @param  list<list<string>>  $rows
     */
    public function __invoke(array $rows): string
    {
        $lines = array_map(
            fn (array $row) => implode("\t", array_map(fn (string $cell) => str_replace(["\r\n", "\t", "\n", "\r"], ' ', $cell), $row)),
            $rows,
        );

        return implode("\n", $lines)."\n";
    }
}
