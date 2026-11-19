<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Contracts\FindLines;
use Domain\Dfr\Data\FoundLine;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Finds lines in files with the ripgrep command, `rg`.
 */
class FindLinesWithRipgrep implements FindLines
{
    public function __invoke(array $paths, array $words): array
    {
        if ($paths === [] || $words === []) {
            return [];
        }

        $patterns = array_merge(...array_map(fn (string $word) => ['-e', $word], $words));
        // --fixed-strings: the words are plain text, not regex. --no-config: a user config must not change the output.
        $process = new Process(['rg', '--json', '--fixed-strings', '--ignore-case', '--no-config', ...$patterns, '--', ...$paths]);
        $process->run();

        // Exit code 1 means that no line matches.
        if ($process->getExitCode() === 1) {
            return [];
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException('ripgrep failed: '.trim($process->getErrorOutput()));
        }

        $lines = [];

        foreach (explode("\n", $process->getOutput()) as $json) {
            $event = json_decode($json, true);
            $path = data_get($event, 'data.path.text');
            $number = data_get($event, 'data.line_number');
            $text = data_get($event, 'data.lines.text');

            // A path or a line that is not valid UTF-8 comes as "bytes" and not as "text". Such a line is skipped.
            if (data_get($event, 'type') !== 'match' || ! is_string($path) || ! is_int($number) || ! is_string($text)) {
                continue;
            }

            $lines[] = new FoundLine($path, $number, rtrim($text, "\r\n"));
        }

        return $lines;
    }
}
