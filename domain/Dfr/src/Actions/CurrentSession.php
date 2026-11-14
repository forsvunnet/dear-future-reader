<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * The ID of the Claude Code session that runs the command, or "unknown".
 */
class CurrentSession
{
    public function __invoke(): string
    {
        $session = getenv('CLAUDE_CODE_SESSION_ID');

        return is_string($session) && $session !== '' ? $session : 'unknown';
    }
}
