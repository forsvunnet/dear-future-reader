<?php

declare(strict_types=1);

use Domain\Dfr\Actions\QmdEmbedRuns;
use Domain\Dfr\Actions\StartQmdEmbed;
use Domain\Dfr\Livewire\MeaningSearch;
use Domain\Dfr\Livewire\ThingList;
use Domain\Dfr\Livewire\ToolsModal;
use Domain\Dfr\ValueObjects\Qmd;
use Domain\Dfr\ValueObjects\QmdEmbedFiles;
use Domain\Dfr\ValueObjects\ThingsRoot;
use Livewire\Livewire;

beforeEach(function () {
    $this->app->instance(ThingsRoot::class, new ThingsRoot("$this->home/.dfr"));
    $this->app->instance(QmdEmbedFiles::class, new QmdEmbedFiles("$this->home/embed.pid", "$this->home/embed.log"));
});

/**
 * Makes a fake qmd that writes its arguments to qmd.args and runs the shell code for the command.
 * The command is the third argument, after "--index dfr".
 */
function fakeQmd(string $home, string $code): string
{
    $path = "$home/qmd";
    file_put_contents($path, "#!/bin/sh\necho \"\$@\" >> \"\$0.args\"\n$code\n");
    chmod($path, 0755);
    app()->instance(Qmd::class, new Qmd($path, 'dfr', null));

    return $path;
}

it('shows the things and files that QMD finds, and skips the things that are gone', function () {
    $id = $this->init('Login page');
    $json = json_encode([
        ['file' => "qmd://things/$id/plan.md?index=dfr", 'line' => 3, 'snippet' => "@@ -3,2 @@ (2 before, 0 after)\n\nThe login form sends the password."],
        ['file' => 'qmd://things/gone/plan.md?index=dfr', 'line' => 1, 'snippet' => 'Old text'],
    ]);
    $qmd = fakeQmd($this->home, "cat <<'EOF'\n$json\nEOF");

    Livewire::test(MeaningSearch::class, ['text' => 'how do people sign in'])
        ->assertSee('Search with QMD')
        ->call('start')
        ->assertSee('Login page')
        ->assertSee("$id · 1 part")
        ->assertSee("plan.md:3")
        ->assertSee('The login form sends the password.')
        ->assertDontSee('Old text');

    expect(file_get_contents("$qmd.args"))->toBe("--index dfr query how do people sign in --no-rerank --format json -n 20 -c things\n");
});

it('runs qmd with the node that the app found, also when node is not on the PATH of the web server', function () {
    // The fake node runs the fake qmd as a shell script. Another node fails on it.
    mkdir("$this->home/node-bin");
    file_put_contents("$this->home/node-bin/node", "#!/bin/sh\nexec /bin/sh \"\$@\"\n");
    chmod("$this->home/node-bin/node", 0755);
    file_put_contents("$this->home/qmd", "#!/usr/bin/env node\necho 'Indexed by the fake node'\n");
    chmod("$this->home/qmd", 0755);
    $this->app->instance(Qmd::class, new Qmd("$this->home/qmd", 'dfr', "$this->home/node-bin/node"));

    Livewire::test(ToolsModal::class)
        ->call('show')
        ->call('updateIndex')
        ->assertSee('Indexed by the fake node')
        ->assertSee("$this->home/qmd · Indexed by the fake node");

    app(StartQmdEmbed::class)();
    $deadline = microtime(true) + 5;
    while (app(QmdEmbedRuns::class)() && microtime(true) < $deadline) {
        usleep(100_000);
    }

    expect(file_get_contents("$this->home/embed.log"))->toBe("Indexed by the fake node\n");
});

it('starts the embed when the web server gives PHP no PATH', function () {
    fakeQmd($this->home, 'echo "Embedded"');
    $this->app->instance(Qmd::class, new Qmd("$this->home/qmd", 'dfr', '/opt/homebrew/bin/node'));
    $path = getenv('PATH');
    putenv('PATH');

    try {
        app(StartQmdEmbed::class)();
        $deadline = microtime(true) + 5;
        while (app(QmdEmbedRuns::class)() && microtime(true) < $deadline) {
            usleep(100_000);
        }
    } finally {
        putenv("PATH=$path");
    }

    expect(file_get_contents("$this->home/embed.log"))->toBe("Embedded\n");
});

it('keeps the QMD results when a new page opens, and does not run qmd again', function () {
    $id = $this->init('Login page');
    $json = json_encode([['file' => "qmd://things/$id/plan.md?index=dfr", 'line' => 3, 'snippet' => 'The login form sends the password.']]);
    $qmd = fakeQmd($this->home, "cat <<'EOF'\n$json\nEOF");

    Livewire::test(MeaningSearch::class, ['text' => 'login'])->call('start');

    Livewire::test(MeaningSearch::class, ['text' => 'login'])
        ->assertSee('The login form sends the password.')
        ->assertDontSee('Search with QMD');
    Livewire::test(MeaningSearch::class, ['text' => 'other text'])->assertSee('Search with QMD');

    expect(file($qmd.'.args'))->toHaveCount(1);
});

it('shows the error when the QMD search fails', function () {
    fakeQmd($this->home, 'echo "no index" >&2; exit 1');

    Livewire::test(MeaningSearch::class, ['text' => 'login'])
        ->call('start')
        ->assertSee('The QMD search failed: qmd failed: no index');
});

it('shows no QMD button when qmd is not installed', function () {
    $this->app->instance(Qmd::class, new Qmd(null, 'dfr', null));

    Livewire::test(MeaningSearch::class, ['text' => 'login'])->assertDontSee('Search with QMD');
});

it('shows the QMD button under the word search results', function () {
    fakeQmd($this->home, 'exit 0');

    Livewire::test(ThingList::class)
        ->set('search', 'login')
        ->assertSeeLivewire(MeaningSearch::class);
});

it('shows the tools and the index status in the modal', function () {
    fakeQmd($this->home, '[ "$1" = --version ] && echo "qmd 9.9.9"; [ "$3" = status ] && echo "358 files indexed"; exit 0');

    Livewire::test(ToolsModal::class)
        ->assertDontSee('Search tools</h2>', false)
        ->call('show')
        ->assertSee("$this->home/qmd · qmd 9.9.9")
        ->assertSee('358 files indexed')
        ->call('close')
        ->assertDontSee('358 files indexed');
});

it('shows the state of the index next to each button', function () {
    $id = $this->init('Login page');
    $this->dfr([$id, 'write', 'plan.md'], "# Plan\n");
    $status = <<<'TEXT'
        Documents
          Total:    2 files indexed
          Vectors:  1 embedded
          Pending:  1 need embedding (run 'qmd embed')
          Updated:  12m ago

        Collections
          things (qmd://things/)
            Pattern:  */*.{md,log}
        TEXT;
    fakeQmd($this->home, "[ \"\$3\" = status ] && cat <<'EOF'\n$status\nEOF\nexit 0");
    $fileCount = count(glob($this->thingFolder($id).'/*.{md,log}', GLOB_BRACE) ?: []);

    Livewire::test(ToolsModal::class)
        ->call('show')
        ->assertSee('The index is made.')
        ->assertSee('The index has 2 files, updated 12m ago.')
        ->assertSee("The things have $fileCount ".($fileCount === 1 ? 'file' : 'files').', changed last')
        ->assertSee('1 file waits.');
});

it('shows when the index is not made and no file waits', function () {
    fakeQmd($this->home, '[ "$3" = status ] && echo "  Total:    0 files indexed"; exit 0');

    Livewire::test(ToolsModal::class)
        ->call('show')
        ->assertSee('The index is not made.')
        ->assertSee('No file waits.');
});

it('makes the index over the context files of the things', function () {
    $qmd = fakeQmd($this->home, 'echo "Indexed 3 files"');

    Livewire::test(ToolsModal::class)
        ->call('show')
        ->call('makeIndex')
        ->assertSee('Indexed 3 files');

    expect(file_get_contents("$qmd.args"))->toContain("--index dfr collection add $this->home/.dfr --name things --mask */*.{md,log}\n");
});

it('shows the error of a failed index action', function () {
    fakeQmd($this->home, '[ "$3" = update ] && { echo "locked" >&2; exit 1; }; exit 0');

    Livewire::test(ToolsModal::class)
        ->call('show')
        ->call('updateIndex')
        ->assertSee('qmd failed: locked');
});

it('runs the embed in the background and keeps its output', function () {
    fakeQmd($this->home, 'sleep 1; echo "Embedded 3 chunks"');

    app(StartQmdEmbed::class)();

    expect(app(QmdEmbedRuns::class)())->toBeTrue();

    // The fake embed takes one second.
    $deadline = microtime(true) + 5;
    while (app(QmdEmbedRuns::class)() && microtime(true) < $deadline) {
        usleep(100_000);
    }

    expect(app(QmdEmbedRuns::class)())->toBeFalse()
        ->and(file_get_contents("$this->home/embed.log"))->toBe("Embedded 3 chunks\n");
});
