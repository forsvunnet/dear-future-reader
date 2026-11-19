<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\ValueObjects\Qmd;
use Domain\Dfr\ValueObjects\QmdEmbedFiles;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Starts `qmd embed` in the background, so the request does not wait for it. An embed can take minutes.
 *
 * It does nothing when an embed already runs.
 */
class StartQmdEmbed
{
    public function __construct(
        private Qmd $qmd,
        private QmdEmbedFiles $files,
        private QmdEmbedRuns $qmdEmbedRuns,
    ) {}

    public function __invoke(): void
    {
        if (($this->qmdEmbedRuns)()) {
            return;
        }

        @mkdir(dirname($this->files->logPath), 0777, true);
        $command = implode(' ', array_map(escapeshellarg(...), $this->qmd->command(['embed'])));
        // The shell starts qmd and ends at once. qmd keeps running after the request, because the shell does not wait for it.
        $process = Process::fromShellCommandline("nohup $command > ".escapeshellarg($this->files->logPath).' 2>&1 < /dev/null & echo $!');
        $process->run();
        $pid = trim($process->getOutput());

        if (! $process->isSuccessful() || ! ctype_digit($pid)) {
            throw new RuntimeException('qmd embed did not start: '.trim($process->getErrorOutput()));
        }

        file_put_contents($this->files->pidPath, $pid);
    }
}
