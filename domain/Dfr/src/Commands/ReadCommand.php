<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\EncodeJson;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Actions\SplitFrontmatter;
use Domain\Dfr\Actions\ValidFileName;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class ReadCommand extends Command
{
    protected $signature = 'dfr:read {id} {file} {--json : Show the file as JSON, with the frontmatter and the body apart}';

    protected $description = 'Show one file.';

    public function __construct(
        private RunOnThing $runOnThing,
        private ValidFileName $validFileName,
        private SplitFrontmatter $splitFrontmatter,
        private EncodeJson $encodeJson,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            $fileName = $this->argument('file');
            if (! ($this->validFileName)($fileName) || ! is_file("$thing->folder/$fileName")) {
                $this->output->getErrorStyle()->writeln("The thing has no file $fileName.");

                return 1;
            }

            $content = (string) file_get_contents("$thing->folder/$fileName");

            if (! $this->option('json')) {
                $this->output->write($content, false, OutputInterface::OUTPUT_RAW);

                return 0;
            }

            $document = ($this->splitFrontmatter)($content);
            $json = ($this->encodeJson)(['file' => $fileName, 'frontmatter' => $document->frontmatter, 'body' => $document->body]);
            $this->output->writeln($json, OutputInterface::OUTPUT_RAW);

            return 0;
        });
    }
}
