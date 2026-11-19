<?php

declare(strict_types=1);

namespace Domain\Dfr\ValueObjects;

use RuntimeException;

/**
 * The qmd command and the QMD index that holds the things.
 */
readonly class Qmd
{
    /**
     * @param  ?string  $path  The full path of the qmd command, or null when qmd is not installed.
     * @param  string  $index  The name of the QMD index. The things have their own index, so the default index of the user stays as it is.
     * @param  ?string  $node  The full path of the node command, or null when node is not installed. qmd is a Node script.
     */
    public function __construct(
        public ?string $path,
        public string $index,
        public ?string $node,
    ) {}

    /**
     * @param  list<string>  $arguments
     * @return list<string>
     */
    public function command(array $arguments): array
    {
        if ($this->path === null) {
            throw new RuntimeException('qmd is not installed.');
        }

        return [$this->path, '--index', $this->index, ...$arguments];
    }

    /**
     * The environment variables for a qmd process. qmd starts with "#!/usr/bin/env node", so the folder of node goes first on the PATH.
     * A web server like Herd runs PHP with a short PATH that does not have node.
     *
     * @return array<string, string>
     */
    public function environment(): array
    {
        if ($this->node === null) {
            return [];
        }

        return ['PATH' => dirname($this->node).PATH_SEPARATOR.getenv('PATH')];
    }
}
