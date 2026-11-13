<?php

declare(strict_types=1);

namespace Domain\Dfr\Data;

/**
 * The JSON that Claude Code gives a hook on stdin.
 */
readonly class HookInput
{
    /**
     * @param  string  $session  The session ID. It contains only letters, digits and "-".
     * @param  string  $cwd  The current folder of the session.
     * @param  string  $prompt  The prompt of the user. Empty for hooks that get no prompt.
     * @param  bool  $stopHookActive  True when the agent continues because a stop hook blocked the stop.
     */
    public function __construct(
        public string $session,
        public string $cwd,
        public string $prompt,
        public bool $stopHookActive,
    ) {}
}
