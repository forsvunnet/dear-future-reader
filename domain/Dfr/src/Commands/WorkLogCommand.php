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
    protected $signature = 'dfr:work:log {id} {text?* : What you did, or the next step. Piped data comes first.}';

    protected $description = 'Add lines to work-log.md. The first line of a session gets a session header.';

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
            $newSession = ! ($this->loggedWorkInSession)($thing, ($this->currentSession)());
            ($this->changeFile)($thing, 'work-log.md', fn (string $oldContent) => ($this->appendWorkLog)($oldContent, $text, $newSession));

            return 0;
        });
    }
}
