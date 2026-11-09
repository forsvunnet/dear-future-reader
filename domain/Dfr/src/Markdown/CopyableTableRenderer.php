<?php

declare(strict_types=1);

namespace Domain\Dfr\Markdown;

use Domain\Dfr\Actions\TableToCsv;
use Domain\Dfr\Actions\TableToTsv;
use InvalidArgumentException;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableRenderer;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Stringable;

/**
 * Renders a table with buttons that copy it as Markdown, CSV or TSV.
 * See domain/Dfr/resources/js/copy-table.js.
 */
class CopyableTableRenderer implements NodeRendererInterface
{
    public function __construct(
        private TableRenderer $tableRenderer,
        private TableCells $tableCells,
        private TableToCsv $tableToCsv,
        private TableToTsv $tableToTsv,
        private string $markdown,
    ) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable|string|null
    {
        if (! $node instanceof Table) {
            throw new InvalidArgumentException('The node must be a Table.');
        }

        $rows = ($this->tableCells)($node);
        $buttons = array_map(
            fn (string $format) => new HtmlElement('button', [
                'type' => 'button',
                'class' => 'dfr-button',
                'x-on:click' => "copy('{$format}')",
            ], 'Copy '.strtoupper($format)),
            ['md', 'csv', 'tsv'],
        );

        // HtmlElement escapes the attribute values.
        return new HtmlElement('div', [
            'x-data' => 'copyTable',
            'class' => 'dfr-table',
            'data-md' => $this->source($node),
            'data-csv' => ($this->tableToCsv)($rows),
            'data-tsv' => ($this->tableToTsv)($rows),
        ], [
            new HtmlElement('div', ['class' => 'dfr-table-actions'], [
                ...$buttons,
                new HtmlElement('span', ['role' => 'status', 'class' => 'dfr-muted', 'x-text' => 'message'], ''),
            ]),
            $this->tableRenderer->render($node, $childRenderer),
        ]);
    }

    /**
     * The lines of the Markdown that the table comes from, as they are in the file.
     */
    public function source(Table $table): string
    {
        $lines = preg_split('/\r\n|\n|\r/', $this->markdown) ?: [];
        $start = (int) $table->getStartLine();
        $end = (int) $table->getEndLine();

        return implode("\n", array_slice($lines, $start - 1, $end - $start + 1))."\n";
    }
}
