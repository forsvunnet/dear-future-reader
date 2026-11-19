<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\ValueObjects\Qmd;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Runs qmd on the index of the things and returns the output.
 */
class RunQmd
{
    public function __construct(
        private Qmd $qmd,
    ) {}

    /**
     * @param  list<string>  $arguments
     */
    public function __invoke(array $arguments): string
    {
        $process = new Process($this->qmd->command($arguments), null, $this->qmd->environment());
        // ponytail: a fixed limit stops a qmd that hangs. A query on a slow machine can need more.
        $process->setTimeout(60);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('qmd failed: '.trim($process->getErrorOutput() ?: $process->getOutput()));
        }

        return $process->getOutput();
    }
}
