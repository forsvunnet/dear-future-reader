<?php

declare(strict_types=1);

namespace Domain\Dfr\Livewire;

use Domain\Dfr\Actions\FindTool;
use Domain\Dfr\Actions\MakeQmdIndex;
use Domain\Dfr\Actions\QmdEmbedRuns;
use Domain\Dfr\Actions\ReadQmdIndexStatus;
use Domain\Dfr\Actions\ReadToolStatus;
use Domain\Dfr\Actions\StartQmdEmbed;
use Domain\Dfr\Actions\UpdateQmdIndex;
use Domain\Dfr\ValueObjects\Qmd;
use Domain\Dfr\ValueObjects\QmdEmbedFiles;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use RuntimeException;

/**
 * The cog in the bottom right of every page. It opens a modal with the status of the search tools and the actions on the QMD index.
 */
class ToolsModal extends Component
{
    public bool $open = false;

    /** The output of the last action, or its error. */
    public string $output = '';

    public bool $failed = false;

    public function show(): void
    {
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
        $this->output = '';
        $this->failed = false;
    }

    public function makeIndex(MakeQmdIndex $makeQmdIndex): void
    {
        $this->run($makeQmdIndex(...));
    }

    public function updateIndex(UpdateQmdIndex $updateQmdIndex): void
    {
        $this->run($updateQmdIndex(...));
    }

    public function embed(StartQmdEmbed $startQmdEmbed): void
    {
        $this->run(function () use ($startQmdEmbed) {
            $startQmdEmbed();

            return 'The embed runs in the background.';
        });
    }

    /**
     * @param  callable(): string  $action
     */
    private function run(callable $action): void
    {
        try {
            $this->output = trim($action());
            $this->failed = false;
        } catch (RuntimeException $exception) {
            $this->output = $exception->getMessage();
            $this->failed = true;
        }
    }

    public function render(FindTool $findTool, ReadToolStatus $readToolStatus, Qmd $qmd, ReadQmdIndexStatus $readQmdIndexStatus, QmdEmbedRuns $qmdEmbedRuns, QmdEmbedFiles $embedFiles): View
    {
        if (! $this->open) {
            return view('dfr::tools-modal');
        }

        $indexStatus = null;
        $embedLog = '';

        if ($qmd->path !== null) {
            try {
                $indexStatus = trim($readQmdIndexStatus());
            } catch (RuntimeException $exception) {
                $indexStatus = $exception->getMessage();
            }

            $log = is_file($embedFiles->logPath) ? (string) file_get_contents($embedFiles->logPath) : '';
            // qmd draws its progress bar with carriage returns. Only the text after the last one in a line is on the screen.
            $lines = [];

            foreach (explode("\n", trim($log)) as $line) {
                $parts = explode("\r", $line);
                $lines[] = $parts[array_key_last($parts)];
            }

            $embedLog = implode("\n", array_slice($lines, -10));
        }

        return view('dfr::tools-modal', [
            'tools' => [$readToolStatus('rg', $findTool('rg')), $readToolStatus('qmd', $qmd->path, $qmd->environment())],
            'installed' => $qmd->path !== null,
            'indexStatus' => $indexStatus,
            'embedRuns' => $qmdEmbedRuns(),
            'embedLog' => $embedLog,
        ]);
    }
}
