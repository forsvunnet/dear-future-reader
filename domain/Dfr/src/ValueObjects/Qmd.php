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
     */
    public function __construct(
        public ?string $path,
        public string $index,
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
}
