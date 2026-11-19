<?php

declare(strict_types=1);

it('finds the lines that contain the text, with case ignored', function () {
    $first = $this->init('first');
    $second = $this->init('second');
    $this->dfr([$first, 'write', 'plan.md'], "Build the Login page\nThen test it\n");
    $this->dfr([$second, 'write', 'notes.md'], "nothing here\nlogin works\n");

    expect($this->dfr(['search', 'login'])->getOutput())
        ->toBe("$first\tfirst\n$first\tplan.md\t1\tBuild the Login page\n$second\tsecond\n$second\tnotes.md\t2\tlogin works\n");
});

it('finds a thing when each word is in it, also on different lines and in different files', function () {
    $both = $this->init('both');
    $one = $this->init('one');
    $this->dfr([$both, 'write', 'plan.md'], "the login page\n");
    $this->dfr([$both, 'write', 'todo.md'], "- [ ] add a button\n");
    $this->dfr([$one, 'write', 'plan.md'], "the login page\n");

    expect($this->dfr(['search', 'login', 'BUTTON'])->getOutput())
        ->toBe("$both\tboth\n$both\tplan.md\t1\tthe login page\n$both\ttodo.md\t1\t- [ ] add a button\n");
});

it('finds the words in the name and the project names of the thing', function () {
    $id = $this->init('Checkout flow');
    $this->dfr([$id, 'write', 'plan.md'], "Add a button\n");
    mkdir("$this->home/shop");
    exec('git -C '.escapeshellarg("$this->home/shop").' init --quiet');
    $this->dfr([$id, 'project:add', "$this->home/shop"]);

    expect($this->dfr(['search', 'checkout', 'shop', 'button'])->getOutput())
        ->toBe("$id\tCheckout flow\n$id\tplan.md\t1\tAdd a button\n");
});

it('shows the things with a name match first, then the things with the most matched lines', function () {
    $few = $this->init('few');
    $many = $this->init('many');
    $named = $this->init('login page');
    $this->dfr([$few, 'write', 'plan.md'], "login\n");
    $this->dfr([$many, 'write', 'plan.md'], "login\nlogin again\n");

    $ids = array_map(
        fn (array $result) => $result['id'],
        json_decode($this->dfr(['search', 'login', '--json'])->getOutput(), true),
    );

    expect($ids)->toBe([$named, $many, $few]);
});

it('takes the words as plain text, not as a pattern', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md'], "a.b\naxb\n");

    expect($this->dfr(['search', 'a.b'])->getOutput())->toBe("$id\ttest\n$id\tplan.md\t1\ta.b\n");
});

it('skips archived files and the data files of the thing', function () {
    $id = $this->init('thing');
    $this->dfr([$id, 'write', 'old.md', 'login']);
    $this->dfr([$id, 'files:archive', 'old.md']);

    expect($this->dfr(['search', 'login'])->getOutput())->toBe('');
});

it('shows the matches as JSON, grouped by thing', function () {
    $id = $this->init('thing');
    $this->dfr([$id, 'write', 'plan.md', 'login page']);

    expect(json_decode($this->dfr(['search', 'login', '--json'])->getOutput(), true))
        ->toBe([['id' => $id, 'name' => 'thing', 'matches' => [['file' => 'plan.md', 'line' => 1, 'text' => 'login page']]]]);
});

it('shows nothing and exits with 0 when nothing matches', function () {
    $id = $this->init();
    $this->dfr([$id, 'write', 'plan.md', 'text']);
    $process = $this->dfr(['search', 'login']);

    expect($process->getExitCode())->toBe(0)
        ->and($process->getOutput())->toBe('');
});

it('fails on an empty text', function () {
    $process = $this->dfr(['search', ' ']);

    expect($process->getExitCode())->toBe(1)
        ->and($process->getErrorOutput())->toContain('Give a text to find.');
});
