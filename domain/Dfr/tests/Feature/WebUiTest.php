<?php

declare(strict_types=1);

use Domain\Dfr\Livewire\ThingPage;
use Domain\Dfr\ValueObjects\ThingsRoot;
use Illuminate\Foundation\Console\ServeCommand;
use Livewire\Livewire;

beforeEach(function () {
    // The dfr script reads the things from the temporary home folder. The Web UI must read the same folder.
    $this->app->instance(ThingsRoot::class, new ThingsRoot("$this->home/.dfr"));
});

it('lets php artisan serve pass the home folder to the Web UI', function () {
    expect(ServeCommand::$passthroughVariables)->toContain('HOME');
});

it('lists the things with each child under its parent', function () {
    $parent = $this->init('Parent thing');
    $child = $this->init('Child thing');
    $this->dfr([$child, 'parent', $parent]);

    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['Parent thing', 'Child thing'])
        ->assertSee('padding-left: calc(1rem + 2ch)', false)
        ->assertSee("/things/$child", false);
});

it('shows the files of a thing in their order', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "---\nowner: web\n---\n# The plan\n");
    $this->dfr([$id, 'write', 'todo.md'], "- [ ] First step\n");
    $this->dfr([$id, 'files:reorder', 'todo.md']);

    $this->get("/things/$id")
        ->assertOk()
        ->assertSeeInOrder(['todo.md', 'First step', 'plan.md', 'owner: web', '<h1>The plan</h1>'], false);
});

it('shows how many todo items of a file are ticked', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'todo.md'], "- [x] First\n- [ ] Second\n- [x] Third\n");
    $this->dfr([$id, 'write', 'plan.md'], "No items\n");

    $this->get("/things/$id")
        ->assertOk()
        ->assertSeeInOrder(['todo.md', '2/3', 'plan.md'])
        ->assertDontSee('0/0');
});

it('escapes HTML in a file', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'notes.md'], "<script>alert(1)</script>\n");

    $this->get("/things/$id")
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;', false);
});

it('does not show archived files', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'old.md'], 'Old text');
    $this->dfr([$id, 'files:archive', 'old.md']);

    $this->get("/things/$id")->assertOk()->assertDontSee('Old text');
});

it('shows 404 for an ID that no thing has', function () {
    $this->get('/things/nope')->assertNotFound();
});

it('saves an edited file and writes a log line', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "Old plan\n");

    Livewire::test(ThingPage::class, ['id' => $id])
        ->call('edit', 'plan.md')
        ->assertSet('content', "Old plan\n")
        ->set('content', "New plan\r\nSecond line\r\n")
        ->call('save')
        ->assertSet('editing', null)
        ->assertSee('New plan');

    expect(file_get_contents($this->thingFolder($id).'/plan.md'))->toBe("New plan\nSecond line\n");

    $log = json_decode($this->dfr([$id, 'log', '--json'])->getOutput(), true);
    expect(end($log))->toMatchArray(['session' => 'web', 'command' => 'write', 'arguments' => ['plan.md'], 'exitCode' => 0]);
});

it('does not save when a session changed the file after it was opened', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "Old plan\n");

    $page = Livewire::test(ThingPage::class, ['id' => $id])
        ->call('edit', 'plan.md')
        ->set('content', "Text from the browser\n");

    $this->dfr([$id, 'write:append', 'plan.md'], 'Line from a session');

    $page->call('save')
        ->assertSet('editing', 'plan.md')
        ->assertSet('content', "Text from the browser\n")
        ->assertSee('changed after you opened it');

    expect(file_get_contents($this->thingFolder($id).'/plan.md'))->toContain('Line from a session');
});

it('does not save when the file was archived after it was opened', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "Old plan\n");

    $page = Livewire::test(ThingPage::class, ['id' => $id])->call('edit', 'plan.md');
    $this->dfr([$id, 'files:archive', 'plan.md']);
    $page->set('content', 'New plan')->call('save')->assertSee('changed after you opened it');

    expect($this->thingFolder($id).'/plan.md')->not->toBeFile();
});

it('opens only the context files of the thing', function (string $fileName) {
    $id = $this->init();

    Livewire::test(ThingPage::class, ['id' => $id])
        ->call('edit', $fileName)
        ->assertSet('editing', null)
        ->assertSet('content', '');
})->with(['.thing.json', '../../.bashrc', 'missing.md']);

it('gives each todo item a checkbox that ticks the line of the item', function () {
    $id = $this->init();
    $content = "---\nowner: web\n---\n# Todo\n\n- [ ] First\n- [x] Second\n";
    $this->dfr([$id, 'write', 'todo.md'], $content);
    $hash = sha1($content);

    // The frontmatter has 3 lines, so "First" has the index 5 in the file.
    $this->get("/things/$id")
        ->assertOk()
        ->assertSee("wire:click.prevent=\"tick(&quot;todo.md&quot;, 5, &quot;$hash&quot;)\"", false)
        ->assertSee("wire:click.prevent=\"tick(&quot;todo.md&quot;, 6, &quot;$hash&quot;)\"", false);
});

it('puts the text of a todo item in the label of its checkbox', function (string $content) {
    $id = $this->init();
    $this->dfr([$id, 'write', 'todo.md'], $content);

    // A click on the text ticks the item, because the browser sends a click on a label to its checkbox.
    $this->get("/things/$id")
        ->assertOk()
        ->assertSeeInOrder(['<label><input type="checkbox"', '> First</label>'], false);
})->with([
    'tight list' => "- [ ] First\n- [ ] Second\n",
    'loose list' => "- [ ] First\n\n- [ ] Second\n",
]);

it('keeps a checkbox disabled when it is not a todo item for dfr', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'todo.md'], "1. [ ] Numbered\n");

    $this->get("/things/$id")
        ->assertOk()
        ->assertSee('disabled', false)
        ->assertDontSee('tick(', false);
});

it('ticks and unticks a todo item from the browser and writes a log line', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'todo.md'], "- [ ] First\n- [ ] Second\n");
    $path = $this->thingFolder($id).'/todo.md';

    $page = Livewire::test(ThingPage::class, ['id' => $id])
        ->call('tick', 'todo.md', 1, sha1((string) file_get_contents($path)))
        ->assertSet('tickError', null);

    expect(file_get_contents($path))->toBe("- [ ] First\n- [x] Second\n");

    $page->call('tick', 'todo.md', 1, sha1((string) file_get_contents($path)));

    expect(file_get_contents($path))->toBe("- [ ] First\n- [ ] Second\n");

    $log = json_decode($this->dfr([$id, 'log', '--json'])->getOutput(), true);
    expect(array_slice($log, -2))->sequence(
        fn ($entry) => $entry->toMatchArray(['session' => 'web', 'command' => 'todo tick', 'arguments' => ['Second']]),
        fn ($entry) => $entry->toMatchArray(['session' => 'web', 'command' => 'todo untick', 'arguments' => ['Second']]),
    );
});

it('does not tick when the file changed after the page loaded', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'todo.md'], "- [ ] First\n");
    $oldHash = sha1("- [ ] First\n");
    $this->dfr([$id, 'write', 'todo.md'], "- [ ] New first\n- [ ] First\n");

    Livewire::test(ThingPage::class, ['id' => $id])
        ->call('tick', 'todo.md', 0, $oldHash)
        ->assertSet('tickError', 'todo.md changed after the page loaded. The page now shows the new content.');

    expect(file_get_contents($this->thingFolder($id).'/todo.md'))->toBe("- [ ] New first\n- [ ] First\n");
});

it('does not tick in a file that is not a context file of the thing', function () {
    $id = $this->init();

    Livewire::test(ThingPage::class, ['id' => $id])
        ->call('tick', '../other/todo.md', 0, 'hash')
        ->assertSet('tickError', '../other/todo.md is not a file of this thing.');
});

it('shows the things in the sidebar, the most recently active first', function () {
    $old = $this->init('Old parent');
    $child = $this->init('Active child');
    $middle = $this->init('Middle thing');
    $this->dfr([$child, 'parent', $old]);

    $setTime = function (string $id, int $time) {
        foreach (['.thing.json', '.log.jsonl'] as $file) {
            touch($this->thingFolder($id)."/$file", $time);
        }
    };
    $setTime($old, time() - 3 * 86400);
    $setTime($middle, time() - 2 * 86400);
    $setTime($child, time() - 120);

    // The parent goes first, because its child is the most recently active thing.
    $this->get("/things/$middle")
        ->assertOk()
        ->assertSeeInOrder(['Old parent', '3 days ago', 'Active child', '2 minutes ago', 'Middle thing', '2 days ago'])
        ->assertSee('aria-current="page"', false);
});
