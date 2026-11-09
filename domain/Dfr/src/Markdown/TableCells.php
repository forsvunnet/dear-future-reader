<?php

declare(strict_types=1);

namespace Domain\Dfr\Markdown;

use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Extension\Table\TableRow;
use League\CommonMark\Node\StringContainerHelper;

/**
 * Reads the plain text of each cell of a Markdown table, row by row. The head row comes first.
 */
class TableCells
{
    /**
     * @return list<list<string>>
     */
    public function __invoke(Table $table): array
    {
        $rows = [];

        foreach ($table->iterator() as $row) {
            if (! $row instanceof TableRow) {
                continue;
            }

            $cells = [];

            foreach ($row->children() as $cell) {
                if ($cell instanceof TableCell) {
                    $cells[] = StringContainerHelper::getChildText($cell);
                }
            }

            $rows[] = $cells;
        }

        return $rows;
    }
}
