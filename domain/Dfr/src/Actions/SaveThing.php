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
        private EncodeJson $encodeJson,
    ) {}

    public function __invoke(Thing $thing): void
    {
        $json = ($this->encodeJson)([
            'name' => $thing->name,
            'parent' => $thing->parent,
            'commits' => $thing->commits,
            'files' => $thing->files,
        ]);

        ($this->writeFile)("$thing->folder/.thing.json", $json."\n");
    }
}
