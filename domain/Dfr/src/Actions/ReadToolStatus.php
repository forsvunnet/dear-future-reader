<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\ToolStatus;
use Symfony\Component\Process\Process;

/**
 * Asks a command for its version.
 */
class ReadToolStatus
{
    /**
     * @param  ?string  $path  The full path of the command, or null when it is not installed.
     */
    public function __invoke(string $name, ?string $path): ToolStatus
    {
        if ($path === null) {
            return new ToolStatus($name, null, null);
        }

        $process = new Process([$path, '--version']);
        $process->run();
        $version = strtok(trim($process->getOutput()), "\n");

        return new ToolStatus($name, $path, $process->isSuccessful() && $version !== false ? $version : null);
    }
}
