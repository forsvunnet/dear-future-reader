<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\CreateThing;
use Domain\Dfr\Actions\FindThingIdInPrompt;
use Domain\Dfr\Actions\ParseHookInput;
use Domain\Dfr\Actions\ReadSession;
use Domain\Dfr\Actions\ReadThing;
use Domain\Dfr\Actions\SaveSession;
use Domain\Dfr\Contracts\FindGitRepository;
use Domain\Dfr\Data\Session;
use Illuminate\Console\Command;

/**
 * The UserPromptSubmit hook. It connects the session to a thing and tells the agent which thing.
 * The output goes into the context of the agent.
 */
class HookPromptCommand extends Command
{
    protected $signature = 'dfr:hook:prompt';

    protected $description = 'Hook for UserPromptSubmit: connect the session to a thing. Reads the hook JSON on stdin.';

    public function __construct(
        private ParseHookInput $parseHookInput,
        private ReadSession $readSession,
        private SaveSession $saveSession,
        private ReadThing $readThing,
        private CreateThing $createThing,
        private FindThingIdInPrompt $findThingIdInPrompt,
        private FindGitRepository $findGitRepository,
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
        $named = ($this->findThingIdInPrompt)($input->prompt);

        if ($named !== null && ($this->readThing)($named) === null) {
            $this->line("dfr: no thing has the ID $named. Ask the user for the right ID.");

            return 0;
        }

        if ($named !== null) {
            ($this->saveSession)(new Session($session->id, $named, $session->lastEdit));
            $this->line($this->reminder($named));

            return 0;
        }

        if ($session->thing !== null && ($this->readThing)($session->thing) !== null) {
            $this->line($this->reminder($session->thing));

            return 0;
        }

        $thing = ($this->createThing)('unnamed', null, ($this->findGitRepository)($input->cwd));
        ($this->saveSession)(new Session($session->id, $thing->id, $session->lastEdit));
        $this->line("dfr: a hook made the thing $thing->id for this session. When you know the task, name it: dfr $thing->id rename \"short name\".");
        $this->line($this->reminder($thing->id));

        return 0;
    }

    public function reminder(string $id): string
    {
        return "dfr: this session works on the thing $id. Use the dfr skill. "
            ."After each change, update the thing: append to work-log.md, tick the todo items, write decisions in plan.md.";
    }
}
