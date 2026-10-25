<?php

declare(strict_types=1);

use Domain\Dfr\Actions\LoadThingsRoot;

it('fails when the home folder is not known', function () {
    // Without the check, the things would be read from "/.dfr".
    (new LoadThingsRoot)('');
})->throws(RuntimeException::class);
