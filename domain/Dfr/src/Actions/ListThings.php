<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;
use Domain\Dfr\ValueObjects\ThingsRoot;

class ListThings
{
    public function __construct(
        private ThingsRoot $root,
        private ReadThing $readThing,
    ) {}

    /**
     * @return list<Thing> The things, sorted by name.
     */
    public function __invoke(): array
    {
        $things = [];

        foreach (glob($this->root->path.'/*/.thing.json') ?: [] as $dataPath) {
            $thing = ($this->readThing)(basename(dirname($dataPath)));

            if ($thing !== null) {
                $things[] = $thing;
            }
        }

        usort($things, fn (Thing $a, Thing $b) => strnatcasecmp($a->name, $b->name));

        return $things;
    }
}
