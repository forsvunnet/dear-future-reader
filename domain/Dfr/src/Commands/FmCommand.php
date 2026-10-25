<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ChangeFile;
use Domain\Dfr\Actions\JoinFrontmatter;
use Domain\Dfr\Actions\ParseYaml;
use Domain\Dfr\Actions\ReadInput;
use Domain\Dfr\Actions\ReadThing;
use Domain\Dfr\Actions\SplitFrontmatter;
use Domain\Dfr\Actions\ValidFileName;
use Domain\Dfr\Data\Document;
use Illuminate\Console\Command;

class FmCommand extends Command
{
    protected $signature = 'dfr:fm {id} {file} {yaml?* : The YAML. Piped data comes first.}';

    protected $description = 'Write or replace all of the frontmatter.';

    public function __construct(
        private ReadThing $readThing,
        private ValidFileName $validFileName,
        private ReadInput $readInput,
        private ParseYaml $parseYaml,
        private ChangeFile $changeFile,
        private SplitFrontmatter $splitFrontmatter,
        private JoinFrontmatter $joinFrontmatter,
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

        $yaml = rtrim(($this->readInput)($this->argument('yaml')), "\n");
        $result = ($this->parseYaml)($yaml);

        if ($result->error !== null) {
            $this->output->getErrorStyle()->writeln("The YAML is not valid: $result->error");

            return 1;
        }

        // The YAML is kept as it was given, so comments and formatting stay.
        ($this->changeFile)($thing, $fileName, fn (string $oldContent) => ($this->joinFrontmatter)(
            new Document($yaml, ($this->splitFrontmatter)($oldContent)->body),
        ));

        return 0;
    }
}
