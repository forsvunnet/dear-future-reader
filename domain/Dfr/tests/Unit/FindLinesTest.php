<?php

declare(strict_types=1);

use Domain\Dfr\Actions\FindLinesWithPhp;
use Domain\Dfr\Actions\FindLinesWithRipgrep;
use Domain\Dfr\Contracts\FindLines;
use Symfony\Component\Process\ExecutableFinder;

$rg = (new ExecutableFinder)->find('rg', null, ['/opt/homebrew/bin', '/usr/local/bin']);

dataset('finders', [
    'PHP' => fn () => new FindLinesWithPhp,
    'ripgrep' => fn () => $rg === null ? test()->markTestSkipped('ripgrep is not installed.') : new FindLinesWithRipgrep($rg),
]);

it('finds the lines with one or more of the words, with case ignored and the words as plain text', function (FindLines $findLines) {
    $folder = sys_get_temp_dir().'/dfr-find-lines-'.uniqid();
    mkdir($folder);
    file_put_contents("$folder/a.md", "The Login page\na.b\naxb\nnothing\n");
    file_put_contents("$folder/b.md", "add a BUTTON\n");

    $lines = $findLines(["$folder/a.md", "$folder/b.md"], ['login', 'a.b', 'button']);

    expect(array_map(fn ($line) => [basename($line->path), $line->line, $line->text], $lines))->toBe([
        ['a.md', 1, 'The Login page'],
        ['a.md', 2, 'a.b'],
        ['b.md', 1, 'add a BUTTON'],
    ]);
})->with('finders');
