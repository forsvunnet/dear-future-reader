<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ParseHookInput;
use Domain\Dfr\Actions\ReadSession;
use Domain\Dfr\Actions\SaveSession;
use Domain\Dfr\Data\Session;
use Illuminate\Console\Command;

/**
 * The PostToolUse hook for the tools that edit files. It stores the time of the edit, so the stop hook can compare it.
 */
class HookEditCommand extends Command
{
    protected $signature = 'dfr:hook:edit';

    protected $description = 'Hook for PostToolUse on Edit and Write: store the time of the edit. Reads the hook JSON on stdin.';

    public function __construct(
        private ParseHookInput $parseHookInput,
        private ReadSession $readSession,
        private SaveSession $saveSession,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $input = ($this->parseHookInput)((string) stream_get_contents(STDIN));

        if ($input === null) {
            $this->output->getErrorStyle()->writeln('dfr: the hook input is not valid JSON with a session_id.');

            return 1;
        }

        $session = ($this->readSession)($input->session);
        ($this->saveSession)(new Session($session->id, $session->thing, gmdate('Y-m-d\TH:i:s\Z')));

        return 0;
    }
}
