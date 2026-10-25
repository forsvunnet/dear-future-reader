<?php

declare(strict_types=1);

namespace Domain\Dfr\Livewire;

use Domain\Dfr\Actions\AppendLog;
use Domain\Dfr\Actions\OrderedFiles;
use Domain\Dfr\Actions\ReadThing;
use Domain\Dfr\Actions\SaveFileIfUnchanged;
use Domain\Dfr\Actions\SplitFrontmatter;
use Domain\Dfr\Data\LogEntry;
use Domain\Dfr\Data\Thing;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The page that shows the context files of a thing, and lets you edit one file at a time.
 */
#[Layout('dfr::layout')]
class ThingPage extends Component
{
    #[Locked]
    public string $id;

    /** The file that is open in the editor, or null when no file is open. */
    #[Locked]
    public ?string $editing = null;

    /** The sha1 of the file when it was opened in the editor. */
    #[Locked]
    public string $oldHash = '';

    public string $content = '';

    public ?string $error = null;

    private ReadThing $readThing;

    private OrderedFiles $orderedFiles;

    private SplitFrontmatter $splitFrontmatter;

    private SaveFileIfUnchanged $saveFile;

    private AppendLog $appendLog;

    // Livewire makes the component itself, so the dependencies come in through boot instead of the constructor.
    public function boot(
        ReadThing $readThing,
        OrderedFiles $orderedFiles,
        SplitFrontmatter $splitFrontmatter,
        SaveFileIfUnchanged $saveFile,
        AppendLog $appendLog,
    ): void {
        $this->readThing = $readThing;
        $this->orderedFiles = $orderedFiles;
        $this->splitFrontmatter = $splitFrontmatter;
        $this->saveFile = $saveFile;
        $this->appendLog = $appendLog;
    }

    public function edit(string $fileName): void
    {
        $thing = $this->thing();

        // Only a file in the list can be opened, so the name cannot point outside the folder of the thing.
        if (! in_array($fileName, ($this->orderedFiles)($thing), true)) {
            return;
        }

        $this->editing = $fileName;
        $this->content = (string) file_get_contents("$thing->folder/$fileName");
        $this->oldHash = sha1($this->content);
        $this->error = null;
    }

    public function cancel(): void
    {
        $this->editing = null;
        $this->content = '';
        $this->error = null;
    }

    public function save(): void
    {
        if ($this->editing === null) {
            return;
        }

        $thing = $this->thing();

        // The textarea sends line breaks as "\r\n". The files from the CLI use "\n".
        $content = str_replace("\r\n", "\n", $this->content);

        if (! ($this->saveFile)($thing, $this->editing, $this->oldHash, $content)) {
            // Keep the text in the editor, so the person does not lose it.
            $this->error = "$this->editing changed after you opened it. Copy your text, then cancel and open the file again.";

            return;
        }

        ($this->appendLog)($thing, new LogEntry(
            time: gmdate('Y-m-d\TH:i:s\Z'),
            session: 'web',
            command: 'write',
            arguments: [$this->editing],
            exitCode: 0,
        ));

        $this->cancel();
    }

    public function render(): View
    {
        $thing = $this->thing();
        $documents = [];

        foreach (($this->orderedFiles)($thing) as $fileName) {
            $documents[$fileName] = ($this->splitFrontmatter)((string) file_get_contents("$thing->folder/$fileName"));
        }

        return view('dfr::thing-page', [
            'thing' => $thing,
            'parent' => $thing->parent === null ? null : ($this->readThing)($thing->parent),
            'documents' => $documents,
        ])->title($thing->name);
    }

    private function thing(): Thing
    {
        return ($this->readThing)($this->id) ?? abort(404);
    }
}
