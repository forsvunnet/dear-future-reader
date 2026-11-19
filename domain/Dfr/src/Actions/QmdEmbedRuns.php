<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\ValueObjects\QmdEmbedFiles;

/**
 * Tells whether the embed that StartQmdEmbed started still runs.
 */
class QmdEmbedRuns
{
    public function __construct(
        private QmdEmbedFiles $files,
    ) {}

    public function __invoke(): bool
    {
        $pid = is_file($this->files->pidPath) ? trim((string) file_get_contents($this->files->pidPath)) : '';

        // ponytail: signal 0 only checks that a process with the ID exists. After a reboot another process can have the same ID. To be sure, also compare the command of the process.
        return ctype_digit($pid) && posix_kill((int) $pid, 0);
    }
}
