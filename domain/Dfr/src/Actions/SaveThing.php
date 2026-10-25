<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;

/**
 * Writes a thing to the .thing.json file in its folder.
 */
class SaveThing
{
    public function __construct(
        private WriteFileAtomically $writeFile,
    ) {}

    public function __invoke(Thing $thing): void
    {
        $json = json_encode(
            ['name' => $thing->name, 'files' => $thing->files],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );

        ($this->writeFile)("$thing->folder/.thing.json", $json."\n");
    }
}
