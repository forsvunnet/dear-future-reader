<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * One line of the .log.jsonl file of a thing.
 */
readonly class LogEntry
{
    /**
     * @param  string  $time  The time in UTC, like "2026-10-25T14:30:00Z".
     * @param  string  $command  The dfr command, like "write" or "todo tick".
     * @param  list<string>  $arguments  The arguments after the command, and the options as "--name=value".
     */
    public function __construct(
        public string $time,
        public string $session,
        public string $command,
        public array $arguments,
        public int $exitCode,
    ) {}
}
