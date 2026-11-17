<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * A Claude Code session that ran dfr commands on a thing.
 */
readonly class ThingSession
{
    /**
     * @param  ?string  $title  The current title of the session, or null when it has none.
     * @param  string  $lastTime  The time in UTC of the last command of the session on the thing.
     */
    public function __construct(
        public string $id,
        public ?string $title,
        public string $lastTime,
    ) {}
}
