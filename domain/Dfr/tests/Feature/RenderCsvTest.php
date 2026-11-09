<?php

declare(strict_types=1);

use Domain\Dfr\Actions\RenderCsv;

it('renders a CSV file as a table with the first row as the head', function () {
    $html = app(RenderCsv::class)("Name,Note\n\"a, b\",\"<b>x</b>\"\n\nc,\"two\nlines\"\n");

    expect($html)
        ->toContain('<thead><tr><th>Name</th><th>Note</th></tr></thead>')
        ->toContain('<tr><td>a, b</td><td>&lt;b&gt;x&lt;/b&gt;</td></tr>')
        ->toContain("<tr><td>c</td><td>two\nlines</td></tr>")
        ->not->toContain('<b>x</b>');
});

it('gives the table its CSV and its TSV to copy', function () {
    $csv = "Name,Note\n\"a, b\",c\n";

    $html = app(RenderCsv::class)($csv);
    preg_match_all('/data-(csv|tsv)="([^"]*)"/', $html, $matches);
    $data = array_map(fn (string $value) => html_entity_decode($value, ENT_QUOTES), array_combine($matches[1], $matches[2]));

    expect($data)->toBe([
        'csv' => $csv,
        'tsv' => "Name\tNote\na, b\tc\n",
    ]);
});

it('renders an empty CSV file as an empty text', function () {
    expect(app(RenderCsv::class)(''))->toBe('');
});
