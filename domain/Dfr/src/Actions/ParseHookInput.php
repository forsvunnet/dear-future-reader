<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\HookInput;

class ParseHookInput
{
    /**
     * @return ?HookInput Null when the JSON is not valid or has no usable session ID.
     */
    public function __invoke(string $json): ?HookInput
    {
        $data = json_decode($json, true);

        if (! is_array($data) || ! is_string($data['session_id'] ?? null)) {
            return null;
        }

        // The session ID becomes a filename, so it must not contain "/" or "..".
        if (preg_match('/^[A-Za-z0-9-]+$/', $data['session_id']) !== 1) {
            return null;
        }

        $cwd = $data['cwd'] ?? null;
        $prompt = $data['prompt'] ?? null;

        return new HookInput(
            session: $data['session_id'],
            cwd: is_string($cwd) ? $cwd : (string) getcwd(),
            prompt: is_string($prompt) ? $prompt : '',
            stopHookActive: ($data['stop_hook_active'] ?? false) === true,
        );
    }
}
