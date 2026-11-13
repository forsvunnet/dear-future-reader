<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * What the hooks know about one Claude Code session.
 */
readonly class Session
{
    /**
     * @param  ?string  $thing  The ID of the thing the session works on.
     * @param  ?string  $lastEdit  The time in UTC of the last file edit in the session, like "2026-10-25T14:30:00Z".
     */
    public function __construct(
        public string $id,
        public ?string $thing,
        public ?string $lastEdit,
    ) {}
}
