<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use League\CommonMark\Util\HtmlElement;
use RuntimeException;

/**
 * Renders a CSV file as an HTML table for the Web UI, with buttons that copy it as CSV or TSV.
 * The first row is the head. The text of each cell is escaped, so it cannot run in the browser.
 * See domain/Dfr/resources/js/copy-table.js.
 */
class RenderCsv
{
    public function __construct(
        private TableToTsv $tableToTsv,
    ) {}

    public function __invoke(string $content): string
    {
        $rows = $this->rows($content);

        if ($rows === []) {
            return '';
        }

        $cells = fn (array $row, string $tag) => new HtmlElement('tr', [], array_map(
            fn (string $cell) => new HtmlElement($tag, [], e($cell)),
            $row,
        ));
        $buttons = array_map(
            fn (string $format) => new HtmlElement('button', [
                'type' => 'button',
                'class' => 'dfr-button',
                'x-on:click' => "copy('{$format}')",
            ], 'Copy '.strtoupper($format)),
            ['csv', 'tsv'],
        );

        // HtmlElement escapes the attribute values.
        return (string) new HtmlElement('div', [
            'x-data' => 'copyTable',
            'class' => 'dfr-table',
            'data-csv' => $content,
            'data-tsv' => ($this->tableToTsv)($rows),
        ], [
            new HtmlElement('div', ['class' => 'dfr-table-actions'], [
                ...$buttons,
                new HtmlElement('span', ['role' => 'status', 'class' => 'dfr-muted', 'x-text' => 'message'], ''),
            ]),
            new HtmlElement('table', [], [
                new HtmlElement('thead', [], $cells($rows[0], 'th')),
                new HtmlElement('tbody', [], array_map(fn (array $row) => $cells($row, 'td'), array_slice($rows, 1))),
            ]),
        ]);
    }

    /**
     * The cells of each row. A cell in quotes can hold a comma, a quote or a line break. Empty lines are skipped.
     *
     * @return list<list<string>>
     */
    public function rows(string $content): array
    {
        $handle = fopen('php://memory', 'r+');

        if ($handle === false) {
            throw new RuntimeException('Cannot open memory to read the CSV.');
        }

        fwrite($handle, $content);
        rewind($handle);
        $rows = [];

        // An empty escape character gives the quoting of RFC 4180.
        while (($row = fgetcsv($handle, null, ',', '"', '')) !== false) {
            if ($row === [null]) {
                continue;
            }

            $rows[] = array_map(fn (?string $cell) => (string) $cell, $row);
        }

        fclose($handle);

        return $rows;
    }
}
