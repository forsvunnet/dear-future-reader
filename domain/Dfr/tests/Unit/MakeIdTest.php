<?php

declare(strict_types=1);

use Domain\Dfr\Actions\MakeId;
use Domain\Dfr\ValueObjects\ThingsRoot;

it('uses 3 characters until all 3-character IDs are used', function (array $usedLengths, int $length) {
    // With 2 characters there are 8 IDs of 3 characters and 16 IDs of 4 characters.
    expect((new MakeId(new ThingsRoot('/nowhere')))->length(2, $usedLengths))->toBe($length);
})->with([
    'none used' => [[], 3],
    '7 of 8 used' => [array_fill(0, 7, 3), 3],
    'all 8 used' => [array_fill(0, 8, 3), 4],
    'all 8 and all 16 used' => [[...array_fill(0, 8, 3), ...array_fill(0, 16, 4)], 5],
]);
