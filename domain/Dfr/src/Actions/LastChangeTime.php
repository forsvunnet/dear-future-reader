<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;

/**
 * Finds when a session last changed a thing, from the log of the thing.
 */
class LastChangeTime
{
    public function __construct(
        private ReadLog $readLog,
    ) {}

    /**
     * @return ?string The time in UTC, or null when the session never changed the thing.
     */
    public function __invoke(Thing $thing, string $session): ?string
    {
        // These commands only show data, so they do not count as a change.
        $readCommands = ['read', 'read:all', 'files', 'log', 'todo next'];
        $last = null;

        foreach (($this->readLog)($thing) as $entry) {
            if ($entry->session === $session && $entry->exitCode === 0 && ! in_array($entry->command, $readCommands, true)) {
                $last = $entry->time;
            }
        }

        return $last;
    }
}
