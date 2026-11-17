<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Contracts\ReadSessionTitle;

/**
 * Reads the title of a session from its transcript, `projects/FOLDER/SESSION-ID.jsonl` in the Claude Code folder.
 * The title is the last line with the type "custom-title". Claude Code does not document this format.
 */
class ReadSessionTitleFromClaudeCode implements ReadSessionTitle
{
    /**
     * @param  string  $claudeFolder  The Claude Code folder, like "/Users/me/.claude".
     */
    public function __construct(
        private string $claudeFolder,
    ) {}

    public function __invoke(string $session): ?string
    {
        // The session ID goes into a glob pattern, so it must not contain "/", "*" or "..".
        if (preg_match('/^[A-Za-z0-9-]+$/', $session) !== 1) {
            return null;
        }

        $path = (glob("$this->claudeFolder/projects/*/$session.jsonl") ?: [null])[0];

        if ($path === null) {
            return null;
        }

        // ponytail: reads the whole transcript on each render, and a transcript can be many MB. Cache the title by the mtime of the file when the page gets slow.
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach (array_reverse($lines) as $line) {
            if (! str_contains($line, 'custom-title')) {
                continue;
            }

            $data = json_decode($line, true);

            if (is_array($data) && ($data['type'] ?? null) === 'custom-title' && is_string($data['customTitle'] ?? null)) {
                return $data['customTitle'];
            }
        }

        return null;
    }
}
