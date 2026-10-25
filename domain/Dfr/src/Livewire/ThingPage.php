<?php

declare(strict_types=1);

namespace Domain\Dfr\Livewire;

use Domain\Dfr\Actions\AppendLog;
use Domain\Dfr\Actions\OrderedFiles;
use Domain\Dfr\Actions\ParseTodoLists;
use Domain\Dfr\Actions\ReadThing;
use Domain\Dfr\Actions\RenderBody;
use Domain\Dfr\Actions\SaveFileIfUnchanged;
use Domain\Dfr\Actions\SplitFrontmatter;
use Domain\Dfr\Actions\ToggleTodoItem;
use Domain\Dfr\Data\LogEntry;
use Domain\Dfr\Data\Thing;
use Domain\Dfr\Data\TodoList;
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

    /** The reason the last tick from the browser failed. */
    #[Locked]
    public ?string $tickError = null;

    private ReadThing $readThing;

    private OrderedFiles $orderedFiles;

    private SplitFrontmatter $splitFrontmatter;

    private SaveFileIfUnchanged $saveFile;

    private AppendLog $appendLog;

    private ParseTodoLists $parseTodoLists;

    private RenderBody $renderBody;

    private ToggleTodoItem $toggleTodoItem;

    // Livewire makes the component itself, so the dependencies come in through boot instead of the constructor.
    public function boot(
        ReadThing $readThing,
        OrderedFiles $orderedFiles,
        SplitFrontmatter $splitFrontmatter,
        SaveFileIfUnchanged $saveFile,
        AppendLog $appendLog,
        ParseTodoLists $parseTodoLists,
        RenderBody $renderBody,
        ToggleTodoItem $toggleTodoItem,
    ): void {
        $this->readThing = $readThing;
        $this->orderedFiles = $orderedFiles;
        $this->splitFrontmatter = $splitFrontmatter;
        $this->saveFile = $saveFile;
        $this->appendLog = $appendLog;
        $this->parseTodoLists = $parseTodoLists;
        $this->renderBody = $renderBody;
        $this->toggleTodoItem = $toggleTodoItem;
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

    /**
     * @param  int  $line  The index of the line of the todo item in the file.
     * @param  string  $hash  The sha1 of the file when the page loaded.
     */
    public function tick(string $fileName, int $line, string $hash): void
    {
        $thing = $this->thing();

        // Only a file in the list can change, so the name cannot point outside the folder of the thing.
        if (! in_array($fileName, ($this->orderedFiles)($thing), true)) {
            $this->tickError = "$fileName is not a file of this thing.";

            return;
        }

        $result = ($this->toggleTodoItem)($thing, $fileName, $line, $hash);
        $this->tickError = $result->errors[0] ?? null;

        if ($result->items === []) {
            return;
        }

        ($this->appendLog)($thing, new LogEntry(
            time: gmdate('Y-m-d\TH:i:s\Z'),
            session: 'web',
            command: $result->items[0]->ticked ? 'todo untick' : 'todo tick',
            arguments: [$result->items[0]->text],
            exitCode: 0,
        ));
    }

    public function render(): View
    {
        $thing = $this->thing();
        $documents = [];
        $bodies = [];
        $progress = [];

        foreach (($this->orderedFiles)($thing) as $fileName) {
            $content = (string) file_get_contents("$thing->folder/$fileName");
            $documents[$fileName] = ($this->splitFrontmatter)($content);

            $items = array_merge(...array_map(fn (TodoList $list) => $list->items, ($this->parseTodoLists)($fileName, $content)));
            $bodies[$fileName] = ($this->renderBody)($fileName, $content, $items);

            if ($items !== []) {
                $progress[$fileName] = count(array_filter($items, fn ($item) => $item->ticked)).'/'.count($items);
            }
        }

        return view('dfr::thing-page', [
            'thing' => $thing,
            'parent' => $thing->parent === null ? null : ($this->readThing)($thing->parent),
            'documents' => $documents,
            'bodies' => $bodies,
            'progress' => $progress,
        ])->title($thing->name);
    }

    private function thing(): Thing
    {
        return ($this->readThing)($this->id) ?? abort(404);
    }
}
