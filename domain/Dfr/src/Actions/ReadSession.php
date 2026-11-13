<?php

declare(strict_types=1);

namespace Domain\Dfr\Actions;

use Domain\Dfr\Data\Session;
use Domain\Dfr\ValueObjects\ThingsRoot;

class ReadSession
{
    public function __construct(
        private ThingsRoot $root,
    ) {}

    /**
     * @param  string  $id  A session ID from ParseHookInput.
     * @return Session A session with no thing and no edit when the hooks have not seen the session yet.
     */
    public function __invoke(string $id): Session
    {
        $path = $this->root->sessionFile($id);
        $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
        $thing = is_array($data) ? ($data['thing'] ?? null) : null;
        $lastEdit = is_array($data) ? ($data['lastEdit'] ?? null) : null;

        return new Session(
            id: $id,
            thing: is_string($thing) ? $thing : null,
            lastEdit: is_string($lastEdit) ? $lastEdit : null,
        );
    }
}
