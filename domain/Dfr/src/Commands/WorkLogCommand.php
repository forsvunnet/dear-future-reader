<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\AppendWorkLog;
use Domain\Dfr\Actions\ChangeFile;
use Domain\Dfr\Actions\CurrentSession;
use Domain\Dfr\Actions\LoggedWorkInSession;
use Domain\Dfr\Actions\ReadInput;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;

class WorkLogCommand extends Command
{
    protected $signature = 'dfr:work:log {id} {text?* : What you did, or the next step. With none, it comes from the pipe.}';

    protected $description = 'Add lines to work.log. The first line of a session gets a session header.';

    public function __construct(
        private RunOnThing $runOnThing,
        private ReadInput $readInput,
        private CurrentSession $currentSession,
        private LoggedWorkInSession $loggedWorkInSession,
        private ChangeFile $changeFile,
        private AppendWorkLog $appendWorkLog,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            $text = ($this->readInput)($this->argument('text'));

            if (trim($text) === '') {
                $this->output->getErrorStyle()->writeln('Give the text as arguments or through a pipe.');

                return 1;
            }

            // ponytail: with no CLAUDE_CODE_SESSION_ID, every run is the session "unknown", so it gets no new header.
            $session = ($this->currentSession)();
            $newSession = ! ($this->loggedWorkInSession)($thing, $session);
            // The Web UI puts the session ID in HTML, so only a plain ID goes in the line.
            $sessionId = $session !== 'unknown' && preg_match('/^[A-Za-z0-9-]+$/', $session) === 1 ? $session : null;
            ($this->changeFile)($thing, 'work.log', fn (string $oldContent) => ($this->appendWorkLog)($oldContent, $text, $newSession, $sessionId));

            return 0;
        });
    }
}
