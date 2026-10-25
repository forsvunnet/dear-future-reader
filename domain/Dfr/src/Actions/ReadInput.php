<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

/**
 * Gets the content for a command from the pipe and from the arguments. The pipe data comes first.
 */
class ReadInput
{
    public function __construct(
        private AppendContent $appendContent,
    ) {}

    /**
     * @param  array<string>  $arguments  The words of the content. They become one line.
     */
    public function __invoke(array $arguments): string
    {
        // ponytail: a pipe that stays open with no data makes this wait. Claude Code gives /dev/null, so it does not wait.
        $piped = stream_isatty(STDIN) ? '' : (string) stream_get_contents(STDIN);

        if ($arguments === []) {
            return $piped;
        }

        return ($this->appendContent)($piped, implode(' ', $arguments)."\n");
    }
}
