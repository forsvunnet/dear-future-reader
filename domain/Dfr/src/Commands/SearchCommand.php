<?php

declare(strict_types=1);

namespace Domain\Dfr\Commands;

use Domain\Dfr\Actions\EncodeJson;
use Domain\Dfr\Actions\SearchThings;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCommand extends Command
{
    protected $signature = 'dfr:search {text : The text to find. Case is ignored.} {--json : Show the output as JSON}';

    protected $description = 'Find text in the context files of all things.';

    public function __construct(
        private SearchThings $searchThings,
        private EncodeJson $encodeJson,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $text = $this->argument('text');

        if (trim($text) === '') {
            $this->output->getErrorStyle()->writeln('Give a text to find.');

            return 1;
        }

        $matches = ($this->searchThings)($text);

        if ($this->option('json')) {
            $this->output->writeln(($this->encodeJson)($matches), OutputInterface::OUTPUT_RAW);

            return 0;
        }

        foreach ($matches as $match) {
            $this->output->writeln("$match->id\t$match->file\t$match->line\t$match->text", OutputInterface::OUTPUT_RAW);
        }

        return 0;
    }
}
