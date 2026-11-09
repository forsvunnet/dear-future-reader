<?php

declare(strict_types=1);

use Domain\Dfr\Actions\RenderBody;

it('gives a table its Markdown lines, its CSV and its TSV to copy', function () {
    $content = "---\nname: x\n---\nintro\n\n| Name | Note |\n|---|---|\n| `a\\|b` | [link](https://example.com) |\n| c, d | |\n\nafter\n";

    $html = app(RenderBody::class)('servers.md', $content, []);
    preg_match_all('/data-(md|csv|tsv)="([^"]*)"/', $html, $matches);
    $data = array_map(fn (string $value) => html_entity_decode($value, ENT_QUOTES), array_combine($matches[1], $matches[2]));

    expect($data)->toBe([
        'md' => "| Name | Note |\n|---|---|\n| `a\\|b` | [link](https://example.com) |\n| c, d | |\n",
        'csv' => "Name,Note\na|b,link\n\"c, d\",\n",
        'tsv' => "Name\tNote\na|b\tlink\nc, d\t\n",
    ]);
});
