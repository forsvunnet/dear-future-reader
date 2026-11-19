<?php

declare(strict_types=1);

namespace Domain\Dfr\ValueObjects;

/**
 * The files where the embed that runs in the background keeps its process ID and its output.
 */
readonly class QmdEmbedFiles
{
    public function __construct(
        public string $pidPath,
        public string $logPath,
    ) {}
}
