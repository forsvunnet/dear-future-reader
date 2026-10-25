<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\ChangeFile;
use Domain\Dfr\Actions\JoinFrontmatter;
use Domain\Dfr\Actions\ParseYaml;
use Domain\Dfr\Actions\ReadInput;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Actions\SplitFrontmatter;
use Domain\Dfr\Actions\ValidFileName;
use Domain\Dfr\Data\Document;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;

class FmCommand extends Command
{
    protected $signature = 'dfr:fm {id} {file} {yaml?* : The YAML. Piped data comes first.}';

    protected $description = 'Write or replace all of the frontmatter.';

    public function __construct(
        private RunOnThing $runOnThing,
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
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            $fileName = $this->argument('file');
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
        });
    }
}
