<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Session;
use Domain\Dfr\ValueObjects\ThingsRoot;
use RuntimeException;

class SaveSession
{
    public function __construct(
        private ThingsRoot $root,
        private WriteFileAtomically $writeFileAtomically,
    ) {}

    public function __invoke(Session $session): void
    {
        $path = $this->root->sessionFile($session->id);
        $folder = dirname($path);

        if (! is_dir($folder) && ! @mkdir($folder, 0777, true) && ! is_dir($folder)) {
            throw new RuntimeException("Could not make the folder $folder.");
        }

        ($this->writeFileAtomically)($path, json_encode(
            ['thing' => $session->thing, 'lastEdit' => $session->lastEdit],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        )."\n");
    }
}
