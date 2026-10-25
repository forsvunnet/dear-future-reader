<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;
use Domain\Dfr\ValueObjects\ThingsRoot;
use RuntimeException;

class CreateThing
{
    public function __construct(
        private ThingsRoot $root,
        private MakeId $makeId,
        private SaveThing $saveThing,
    ) {}

    /**
     * @param  ?string  $parent  The ID of an existing thing. A new thing has no children, so it cannot make a circle.
     */
    public function __invoke(string $name, ?string $parent = null): Thing
    {
        if (! is_dir($this->root->path) && ! @mkdir($this->root->path, 0777, true) && ! is_dir($this->root->path)) {
            throw new RuntimeException("Could not make the folder {$this->root->path}.");
        }

        // mkdir fails when the folder exists, so two sessions never get the same ID.
        while (true) {
            $id = ($this->makeId)();
            $folder = $this->root->folder($id);

            if (@mkdir($folder)) {
                break;
            }

            if (! is_dir($folder)) {
                throw new RuntimeException("Could not make the folder $folder.");
            }
        }

        $thing = new Thing(id: $id, folder: $folder, name: $name, parent: $parent, commits: [], files: []);
        ($this->saveThing)($thing);

        return $thing;
    }
}
