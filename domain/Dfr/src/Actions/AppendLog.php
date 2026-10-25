<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\LogEntry;
use Domain\Dfr\Data\Thing;
use RuntimeException;

/**
 * Adds a line to the .log.jsonl file of a thing.
 */
class AppendLog
{
    public function __invoke(Thing $thing, LogEntry $entry): void
    {
        $line = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";

        if (file_put_contents("$thing->folder/.log.jsonl", $line, FILE_APPEND | LOCK_EX) === false) {
            throw new RuntimeException("Could not write the log of $thing->id.");
        }
    }
}
