<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\LastChangeTime;
use Domain\Dfr\Actions\ParseHookInput;
use Domain\Dfr\Actions\ReadSession;
use Domain\Dfr\Actions\ReadThing;
use Illuminate\Console\Command;

/**
 * The Stop hook. It blocks the end of the turn when the session edited files after its last change to the thing.
 */
class HookStopCommand extends Command
{
    protected $signature = 'dfr:hook:stop';

    protected $description = 'Hook for Stop: block the stop until the thing is updated after the last edit. Reads the hook JSON on stdin.';

    public function __construct(
        private ParseHookInput $parseHookInput,
        private ReadSession $readSession,
        private ReadThing $readThing,
        private LastChangeTime $lastChangeTime,
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

        // The agent already continues because of this hook. A second block could loop without end.
        if ($input->stopHookActive) {
            return 0;
        }

        $session = ($this->readSession)($input->session);
        $thing = $session->thing === null ? null : ($this->readThing)($session->thing);

        if ($thing === null || $session->lastEdit === null) {
            return 0;
        }

        // ponytail: the times have whole seconds, so a change in the same second as the edit counts as after it.
        // Edits through Bash, like sed, are not seen. Only the Edit, Write and NotebookEdit tools reach hook:edit.
        $lastChange = ($this->lastChangeTime)($thing, $session->id);

        if ($lastChange !== null && $lastChange >= $session->lastEdit) {
            return 0;
        }

        $this->line((string) json_encode([
            'decision' => 'block',
            'reason' => "You edited files after the last update of the dfr thing $thing->id. Update the thing before you stop: "
                ."append to work-log.md, tick the done todo items, write new decisions in plan.md, and connect each new commit with dfr $thing->id git:add HEAD.",
        ], JSON_UNESCAPED_SLASHES));

        return 0;
    }
}
