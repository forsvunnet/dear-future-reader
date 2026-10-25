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

it('lists the things with their status, with each child under its parent', function () {
    $parent = $this->init('Parent thing');
    $child = $this->init('Child thing');
    $this->dfr([$child, 'parent', $parent]);
    $this->dfr([$child, 'status', 'in progress']);

    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['Parent thing', 'Child thing', 'in progress'])
        ->assertSee('padding-left: 1.5rem', false)
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
