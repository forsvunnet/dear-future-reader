<?php

declare(strict_types=1);

namespace Domain\Dfr\Contracts;

/**
 * Reads the current title of a Claude Code session.
 */
interface ReadSessionTitle
{
    /**
     * @return ?string Null when the session has no title, or when the session cannot be found.
     */
    public function __invoke(string $session): ?string;
}
