<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\ValueObjects\ThingsRoot;

/**
 * Adds the folder of the things to the QMD index as the collection "things", and indexes the files.
 *
 * The mask takes the context files of each thing. It leaves out the archived files, because they are one folder deeper.
 */
class MakeQmdIndex
{
    public function __construct(
        private RunQmd $runQmd,
        private ThingsRoot $thingsRoot,
    ) {}

    public function __invoke(): string
    {
        return ($this->runQmd)(['collection', 'add', $this->thingsRoot->path, '--name', 'things', '--mask', '*/*.{md,log}']);
    }
}
