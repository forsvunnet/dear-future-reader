<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Gets the content for a command from the arguments, or from the pipe when there are no arguments.
 */
class ReadInput
{
    /**
     * @param  array<string>  $arguments  The words of the content. They become one line.
     */
    public function __invoke(array $arguments): string
    {
        // Arguments never read the pipe. Some shells give a pipe that stays open with no data, and the read would never end.
        if ($arguments !== []) {
            return implode(' ', $arguments)."\n";
        }

        // ponytail: with no arguments, a pipe that stays open with no data still makes this wait.
        return stream_isatty(STDIN) ? '' : (string) stream_get_contents(STDIN);
    }
}
