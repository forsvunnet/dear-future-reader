# Phases

The steps from the README to a finished first release. Each phase ends with a check that tells if the phase is done.

## Phase 0: Decisions

Make the decisions that the code depends on. Write each decision in the README or in its own file.

- Where the folders of the things are on disk.
- The format of the ID.
- Where the data of a thing is kept: status, parent, git commits and the log. Options are a file in the folder of the thing, or a database.
- The frontmatter that each context file must have.
- How `dfr` knows which session makes a change, for `log`.
- Where the order of the files is kept, for `files:reorder`.
- What `files:archive` does with an archived file.

Done when: each item above has a written answer.

## Phase 1: Project setup

1. Make a new Laravel 13 app in this repository.
2. Add Livewire, Alpine.js and Tailwind.
3. Add the `dfr` script in the project root. The script calls `php artisan`.
4. Set up the test suite.

Done when: `dfr` runs from any folder and shows the list of commands.

## Phase 2: Core CLI

The commands that a session needs to start and continue work on a thing.

- `init`
- `list`
- `read` and `read:all`
- `write`, `write:append` and `write:prepend`
- `fm` and `fm:add`

Done when: a session can make a thing, write files to it, and read them back. Tests cover each command.

## Phase 3: Todo commands

- `todo add`, `add:after` and `add:before`
- `todo tick`, `untick` and `next`
- The `--list` flag, and the rules for a missing `--list`.

Done when: tests cover each command, with and without `--list`, and each case where the command must fail.

## Phase 4: Rest of the CLI

- `status` and `list --status`
- `parent`, `parent:remove`, `init --parent` and `list --tree`, with the rule against circles.
- `rename`
- `search`
- `log`
- `git:add` and `git:remove`
- `files`, `files:reorder` and `files:archive`
- The `--json` flag.

Done when: each command in the README works and has tests.

## Phase 5: Skills

1. Write the skills in the `skills/` folder.
2. Install the skills in `~/.claude/skills/`.
3. Let an agent do a real task with the skills, over two or more sessions.

Done when: a new session finds the thing, reads the context and continues the work without help.

## Phase 6: Web UI

- A page that lists the things, with status and tree.
- A page that shows the context files of a thing.
- Editing of the context files.

Done when: you can find a thing, read its files and edit them in the browser.
