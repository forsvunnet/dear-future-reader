<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Thing;

/**
 * Tells if the session already ran `work:log` on the thing with success.
 */
class LoggedWorkInSession
{
    public function __construct(
        private ReadLog $readLog,
    ) {}

    public function __invoke(Thing $thing, string $session): bool
    {
        foreach (($this->readLog)($thing) as $entry) {
            if ($entry->command === 'work:log' && $entry->exitCode === 0 && $entry->session === $session) {
                return true;
            }
        }

        return false;
    }
}
