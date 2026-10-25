<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ChangeFile;
use Domain\Dfr\Actions\PrependContent;
use Domain\Dfr\Actions\ReadInput;
use Domain\Dfr\Actions\ReadThing;
use Domain\Dfr\Actions\ValidFileName;
use Illuminate\Console\Command;

class WritePrependCommand extends Command
{
    protected $signature = 'dfr:write:prepend {id} {file} {content?* : The content. Piped data comes first.}';

    protected $description = 'Add to the start of the file, after the frontmatter.';

    public function __construct(
        private ReadThing $readThing,
        private ValidFileName $validFileName,
        private ReadInput $readInput,
        private ChangeFile $changeFile,
        private PrependContent $prependContent,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $id = $this->argument('id');
        $fileName = $this->argument('file');
        $thing = ($this->readThing)($id);

        if ($thing === null) {
            $this->output->getErrorStyle()->writeln("No thing has the ID $id.");

            return 1;
        }

        if (! ($this->validFileName)($fileName)) {
            $this->output->getErrorStyle()->writeln("$fileName is not a valid filename. It must not contain \"/\" or start with \".\".");

            return 1;
        }

        $content = ($this->readInput)($this->argument('content'));
        ($this->changeFile)($thing, $fileName, fn (string $oldContent) => ($this->prependContent)($oldContent, $content));

        return 0;
    }
}
