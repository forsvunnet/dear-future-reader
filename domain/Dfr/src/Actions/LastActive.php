<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;

/**
 * Finds the Unix time of the last command on a thing.
 */
class LastActive
{
    public function __invoke(Thing $thing): int
    {
        // ponytail: the time of a file changes when the folder is copied. Read the last log line if that matters.
        // Each command adds a line to the log. `init` does not, so .thing.json gives the time of a new thing.
        $times = array_map(fn (string $path) => filemtime($path) ?: 0, array_filter(["$thing->folder/.log.jsonl", "$thing->folder/.thing.json"], is_file(...)));

        return max([0, ...$times]);
    }
}
