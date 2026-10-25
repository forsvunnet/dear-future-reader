<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\LogEntry;
use Domain\Dfr\Data\Thing;

/**
 * Reads the .log.jsonl file of a thing. Lines that are not valid are skipped.
 */
class ReadLog
{
    /**
     * @return list<LogEntry> The oldest entry first.
     */
    public function __invoke(Thing $thing): array
    {
        $path = "$thing->folder/.log.jsonl";
        $entries = [];

        foreach (is_file($path) ? (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []) : [] as $line) {
            $data = json_decode($line, true);

            if (! is_array($data)
                || ! is_string($data['time'] ?? null)
                || ! is_string($data['session'] ?? null)
                || ! is_string($data['command'] ?? null)
                || ! is_array($data['arguments'] ?? null)
                || ! is_int($data['exitCode'] ?? null)) {
                continue;
            }

            $entries[] = new LogEntry(
                $data['time'],
                $data['session'],
                $data['command'],
                array_values(array_filter($data['arguments'], is_string(...))),
                $data['exitCode'],
            );
        }

        return $entries;
    }
}
