<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Contracts\ReadSessionTitle;
use Domain\Dfr\Data\Thing;
use Domain\Dfr\Data\ThingSession;

/**
 * Finds the Claude Code sessions that ran commands on a thing, from its command log.
 * The sessions "web" and "unknown" are left out, because they are not Claude Code sessions.
 */
class ThingSessions
{
    public function __construct(
        private ReadLog $readLog,
        private ReadSessionTitle $readSessionTitle,
    ) {}

    /**
     * @return list<ThingSession> The session that was active last is first.
     */
    public function __invoke(Thing $thing): array
    {
        $lastTimes = [];

        foreach (($this->readLog)($thing) as $entry) {
            if ($entry->session === 'web' || $entry->session === 'unknown') {
                continue;
            }

            // Remove the session first, so it moves to the end of the list.
            unset($lastTimes[$entry->session]);
            $lastTimes[$entry->session] = $entry->time;
        }

        $sessions = [];

        foreach (array_reverse($lastTimes, true) as $id => $time) {
            $sessions[] = new ThingSession((string) $id, ($this->readSessionTitle)((string) $id), $time);
        }

        return $sessions;
    }
}
