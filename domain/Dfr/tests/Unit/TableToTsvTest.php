<?php

declare(strict_types=1);

use Domain\Dfr\Actions\TableToTsv;

it('replaces a tab or a line break in a cell with a space', function () {
    $tsv = (new TableToTsv)([['name', 'note'], ["a\tb", "one\r\ntwo\nthree"], ['', 'x']]);

    expect($tsv)->toBe("name\tnote\na b\tone two three\n\tx\n");
});
