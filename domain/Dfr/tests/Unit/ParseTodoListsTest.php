<?php

declare(strict_types=1);

use Domain\Dfr\Actions\BodyFirstLine;
use Domain\Dfr\Actions\ParseTodoLists;
use Domain\Dfr\Actions\SplitFrontmatter;
use Domain\Dfr\Data\TodoList;

function parseTodoLists(string $content): array
{
    return array_map(
        fn (TodoList $list) => [$list->title, array_map(fn ($item) => [$item->line, $item->text, $item->ticked], $list->items)],
        (new ParseTodoLists(new BodyFirstLine(new SplitFrontmatter)))('todo.md', $content),
    );
}

it('uses the nearest line above the items as the title, without "#" and ":"', function () {
    $content = "## Setup\n\n- [ ] Define dependencies\n- [x] Install packages\n\nBefore the release:\n* [X] Run build command\n";

    expect(parseTodoLists($content))->toBe([
        ['Setup', [[2, 'Define dependencies', false], [3, 'Install packages', true]]],
        ['Before the release', [[6, 'Run build command', true]]],
    ]);
});

it('keeps blank lines inside a list and ends the list at a text line', function () {
    $content = "Setup\n- [ ] one\n\n- [ ] two\nSome text\n- [ ] three\n";

    expect(parseTodoLists($content))->toBe([
        ['Setup', [[1, 'one', false], [3, 'two', false]]],
        ['Some text', [[5, 'three', false]]],
    ]);
});

it('gives a list at the start of the file no title', function () {
    expect(parseTodoLists("- [ ] one\n"))->toBe([[null, [[0, 'one', false]]]]);
});

it('never sees items in the frontmatter or in code blocks', function () {
    $content = "---\nnote: \"- [ ] no\"\n---\n```\nTitle\n- [ ] in code\n```\n- [ ] after code\n";

    expect(parseTodoLists($content))->toBe([[null, [[7, 'after code', false]]]]);
});
