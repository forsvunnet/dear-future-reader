<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;

/**
 * Lists the context files of a thing in their order.
 * Files that are on disk but not in the order come last, sorted by name.
 */
class OrderedFiles
{
    public function __construct(
        private ValidFileName $validFileName,
    ) {}

    /**
     * @return list<string>
     */
    public function __invoke(Thing $thing): array
    {
        $onDisk = array_values(array_filter(
            scandir($thing->folder) ?: [],
            fn (string $name) => ($this->validFileName)($name) && is_file("$thing->folder/$name"),
        ));

        return [
            ...array_intersect($thing->files, $onDisk),
            ...array_diff($onDisk, $thing->files),
        ];
    }
}
