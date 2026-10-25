<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\OrderedFiles;
use Domain\Dfr\Actions\ReadThing;
use Domain\Dfr\Actions\SplitFrontmatter;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class ReadAllCommand extends Command
{
    protected $signature = 'dfr:read:all {id} {--fm : Show only the frontmatter} {--frontmatter : Same as --fm}';

    protected $description = 'Show the content of all files, in order.';

    public function __construct(
        private ReadThing $readThing,
        private OrderedFiles $orderedFiles,
        private SplitFrontmatter $splitFrontmatter,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $id = $this->argument('id');
        $thing = ($this->readThing)($id);

        if ($thing === null) {
            $this->output->getErrorStyle()->writeln("No thing has the ID $id.");

            return 1;
        }

        $onlyFrontmatter = $this->option('fm') || $this->option('frontmatter');
        $sections = [];

        // The "==> name <==" header is the same as the header of `head` and `tail` with many files.
        foreach (($this->orderedFiles)($thing) as $fileName) {
            $content = (string) file_get_contents("$thing->folder/$fileName");

            if ($onlyFrontmatter) {
                $content = ($this->splitFrontmatter)($content)->frontmatter ?? '';
            }

            $sections[] = "==> $fileName <==\n".($content === '' ? '' : rtrim($content, "\n")."\n");
        }

        $this->output->write(implode("\n", $sections), false, OutputInterface::OUTPUT_RAW);

        return 0;
    }
}
