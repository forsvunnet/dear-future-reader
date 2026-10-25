<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\EncodeJson;
use Domain\Dfr\Actions\OrderedFiles;
use Domain\Dfr\Actions\RunOnThing;
use Domain\Dfr\Actions\SplitFrontmatter;
use Domain\Dfr\Data\Thing;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class ReadAllCommand extends Command
{
    protected $signature = 'dfr:read:all {id}
        {--fm : Show only the frontmatter}
        {--frontmatter : Same as --fm}
        {--json : Show the files as JSON, with the frontmatter and the body apart}';

    protected $description = 'Show the content of all files, in order.';

    public function __construct(
        private RunOnThing $runOnThing,
        private OrderedFiles $orderedFiles,
        private SplitFrontmatter $splitFrontmatter,
        private EncodeJson $encodeJson,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        return ($this->runOnThing)($this, $this->argument('id'), function (Thing $thing) {
            $onlyFrontmatter = $this->option('fm') || $this->option('frontmatter');
            $sections = [];
            $files = [];

            foreach (($this->orderedFiles)($thing) as $fileName) {
                $content = (string) file_get_contents("$thing->folder/$fileName");
                $document = ($this->splitFrontmatter)($content);

                $files[] = $onlyFrontmatter
                    ? ['file' => $fileName, 'frontmatter' => $document->frontmatter]
                    : ['file' => $fileName, 'frontmatter' => $document->frontmatter, 'body' => $document->body];

                if ($onlyFrontmatter) {
                    $content = $document->frontmatter ?? '';
                }

                // The "==> name <==" header is the same as the header of `head` and `tail` with many files.
                $sections[] = "==> $fileName <==\n".($content === '' ? '' : rtrim($content, "\n")."\n");
            }

            if ($this->option('json')) {
                $this->output->writeln(($this->encodeJson)($files), OutputInterface::OUTPUT_RAW);

                return 0;
            }

            $this->output->write(implode("\n", $sections), false, OutputInterface::OUTPUT_RAW);

            return 0;
        });
    }
}
