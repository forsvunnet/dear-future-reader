<?php

declare(strict_types=1);

use Domain\Dfr\Actions\TableToCsv;

it('puts a cell in quotes only when it needs them', function () {
    $csv = (new TableToCsv)([['name', 'note'], ['plain', 'a, b'], ['quote', 'say "hi"'], ['break', "one\ntwo"]]);

    expect($csv)->toBe("name,note\nplain,\"a, b\"\nquote,\"say \"\"hi\"\"\"\nbreak,\"one\ntwo\"\n");
});

it('keeps a backslash before a quote as it is', function () {
    expect((new TableToCsv)([['a\\"b']]))->toBe("\"a\\\"\"b\"\n");
});
