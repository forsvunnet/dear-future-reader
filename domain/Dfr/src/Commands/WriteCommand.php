<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ChangeFile;
use Domain\Dfr\Actions\ReadInput;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Actions\ValidFileName;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;

class WriteCommand extends Command
{
    protected $signature = 'dfr:write {id} {file} {content?* : The content. Piped data comes first.}';

    protected $description = 'Write the file. Overwrites the file if it exists.';

    public function __construct(
        private RunOnThing $runOnThing,
        private ValidFileName $validFileName,
        private ReadInput $readInput,
        private ChangeFile $changeFile,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            $fileName = $this->argument('file');
            if (! ($this->validFileName)($fileName)) {
                $this->output->getErrorStyle()->writeln("$fileName is not a valid filename. It must not contain \"/\" or start with \".\".");

                return 1;
            }

            $content = ($this->readInput)($this->argument('content'));
            ($this->changeFile)($thing, $fileName, fn () => $content);

            return 0;
        });
    }
}
