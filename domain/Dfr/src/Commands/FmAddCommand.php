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
use Symfony\Component\Yaml\Yaml;

class FmAddCommand extends Command
{
    protected $signature = 'dfr:fm:add {id} {file} {yaml?* : The YAML. Piped data comes first.}';

    protected $description = 'Add or replace the given attributes. Keeps the other attributes.';

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

            $newValues = ($this->parseYaml)(($this->readInput)($this->argument('yaml')));

            if ($newValues->values === null) {
                $this->output->getErrorStyle()->writeln("The YAML is not valid: $newValues->error");

                return 1;
            }

            // The old frontmatter is read inside the lock. When it is not valid, the file stays as it is.
            $oldError = null;

            ($this->changeFile)($thing, $fileName, function (string $oldContent) use ($newValues, &$oldError) {
                $document = ($this->splitFrontmatter)($oldContent);
                $oldValues = ($this->parseYaml)($document->frontmatter ?? '');

                if ($oldValues->values === null) {
                    $oldError = $oldValues->error;

                    return $oldContent;
                }

                $yaml = rtrim(Yaml::dump(array_replace($oldValues->values, $newValues->values), 4, 2), "\n");

                return ($this->joinFrontmatter)(new Document($yaml, $document->body));
            });

            if ($oldError !== null) {
                $this->output->getErrorStyle()->writeln("The frontmatter in $fileName is not valid, so nothing changed: $oldError");

                return 1;
            }

            return 0;
        });
    }
}
