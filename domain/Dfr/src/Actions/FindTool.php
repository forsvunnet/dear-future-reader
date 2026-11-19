<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Symfony\Component\Process\ExecutableFinder;

/**
 * Finds the full path of a command, or null when it is not installed.
 *
 * A web server like Herd runs PHP with a short PATH, so it also looks in the folders where Homebrew and other installers put commands.
 */
class FindTool
{
    public function __construct(
        private string $home,
    ) {}

    public function __invoke(string $name): ?string
    {
        return (new ExecutableFinder)->find($name, null, ['/opt/homebrew/bin', '/usr/local/bin', "$this->home/.local/bin"]);
    }
}
