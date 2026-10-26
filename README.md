# Dear Future Reader

Shared context files that let many agent sessions work on the same task.

## Why

Agents reach their session limit fast. When many sessions run at the same time, it is difficult to keep track of what goes in which session.

## How it works

Each thing gets its own folder. A thing can be a task, a feature, a bug fix, a planning session and so on.

All sessions on the same thing read, write and update the same context files. Context files can be plans, todos, affected files, prompts, instructions, progress, reports, facts and so on.

## Parts

Dear Future Reader comes in three parts:

- **CLI**: the `dfr` command to create and update the context files.
- **Skills**: Claude Code skills that teach agents how to use `dfr`.
- **Web UI**: a page that lists each thing and lets you read and edit its context files.

## Install

Dear Future Reader is a Laravel 13 app on the TALL stack: Tailwind, Alpine.js, Laravel and Livewire.

1. Clone the repository.
2. Run `composer install` and `npm install`.
3. Add the `dfr` script in the project root to your `PATH`.

The `dfr` script calls `php artisan` inside the project.

The things are in `~/.dfr/`. To use a different folder, set `things_path` in `~/.config/dfr/config.json`. See [doc/decisions.md](doc/decisions.md).

## Quick start

Create a thing:

```shell
ID="$(dfr init name-of-thing)"
```

The command returns an ID. Use this ID in all other commands for the thing.

Write a context file:

```shell
echo "Build the login page" | dfr $ID write plan.md
```

## CLI usage

### Things

```shell
dfr init [NAME]                         # Make a new thing and show its ID.
dfr init [NAME] --parent [PARENT_ID]    # Make a new thing as a child of another thing.
dfr list                                # List all things with their IDs.
dfr list --tree                         # Show the things as a tree, with each child under its parent.
dfr search [TEXT]                       # Find text in the context files of all things.
dfr $ID rename [NAME]                   # Change the name of the thing. The ID stays the same.
dfr $ID log                             # Show which session ran which command on the thing, and when.
dfr $ID parent [PARENT_ID]              # Make the thing a child of another thing.
dfr $ID parent:remove                   # Remove the parent of the thing.
dfr $ID git:add [HASH]                  # Connect a git commit to the thing.
dfr $ID git:remove [HASH]               # Remove the connection to a git commit.
dfr $ID project:add [PATH]              # Add the git repository of the folder to the projects. The current folder when no path.
dfr $ID project:remove [PATH]           # Remove a project that project:add added.
```

`init` stores the git repository of the current folder, if there is one.

A thing relates to projects. A project is a git repository: the repository where `init` ran, each repository from `project:add`, and the repository of each commit from `git:add`.

`list` shows one thing on each line: the ID, the name and the projects, with a tab between them. The projects show as folder names, with ", " between them. A thing with no projects has no projects column. `--tree` puts each child under its parent, with two spaces of indent for each level. `--tree` does not work together with `--json`.

`search` ignores case. It shows one match on each line: the ID, the filename, the line number and the line, with a tab between them. It does not search archived files.

`log` shows one command on each line: the time in UTC, the session, the exit code and the command, with a tab between them. A failed command is in the log too. `init` is not in the log.

Add `--json` to `list`, `search`, `read`, `read:all` and `log` to get the output as JSON. `list --json` gives the full path of each project. With `--json`, `read` and `read:all` give the frontmatter and the body of each file apart.

A thing has one parent at most. The parent must not be the thing itself, or a child of the thing at any level. If it is, the command fails and changes nothing.

`git:add` stores the full hash together with the path of the git repository in the current folder. If the commit does not exist in that repository, the command fails. `git:remove` takes the full hash or the first 4 or more characters of it.

### Read

```shell
dfr $ID read [FILENAME]   # Show one file.
dfr $ID read:all          # Show the content of all files, in order.
dfr $ID read:all --fm     # Show only the frontmatter of each file. Also --frontmatter.
```

When the thing has projects, `read:all` starts with a line like `Projects: /path/one, /path/two` and a blank line. `read:all --json` does not show the projects.

### Write

```shell
dfr $ID write [FILENAME]          # Write the file. Overwrites the file if it exists.
dfr $ID write:append [FILENAME]   # Same as write, but adds to the end of the file.
dfr $ID write:prepend [FILENAME]  # Same as write, but adds to the start of the file, after the frontmatter.
```

The filename is required. The content can come from a pipe, from arguments, or from both. With both, the pipe data comes first. The arguments become one line, with a space between each argument.

A filename must not contain `/` or start with `.`.

### Frontmatter

```shell
dfr $ID fm [FILENAME] [YAML]      # Write or replace all of the frontmatter.
dfr $ID fm:add [FILENAME] [YAML]  # Add or replace the given attributes. Keeps the other attributes.
```

### Todo

A todo list is a run of Markdown todo items, like `- [ ] Install packages`. Blank lines between the items are part of the list. The first other line ends the list.

The title of the list is the nearest line above the first item that is not blank. The `#` marks and a `:` at the end are not part of the title. Lines in the frontmatter and in code blocks are never items.

```shell
dfr $ID todo add [ITEM...] --list [NAME]
```

The `--list` flag is optional. It gives the name of the todo list.

- With `--list`, the command looks only in that todo list.
- Without `--list`, the command looks in all todo lists. Each argument must then match one unique line across all lists.
- Without `--list`, `add` and `next` need exactly one todo list in the thing. If there are more, the command fails.
- When no list matches `--list`, `add` makes a new list with a `##` title at the end of `todo.md`.

An argument matches an item when the item contains the argument, with case ignored. When an item is equal to the argument, only that item matches. The `--list` name matches a title with the same rule.

Examples:

```shell
dfr $ID todo add "Define dependencies" "Install packages" "Run build command" --list "Setup"
dfr $ID todo add:after "install" "Verify installation" --list "Setup"
dfr $ID todo add:before "build" "Clear the cache" --list "Setup"
dfr $ID todo tick "dependencies" "packages" "build" --list "Setup"
dfr $ID todo untick "dependencies" "packages" "build" --list "Setup"
dfr $ID todo next --list "Setup"
dfr $ID todo tick "verify"
```

- `add` adds the items to the end of the list.
- `add:after` adds the items after the matching item.
- `add:before` adds the items before the matching item.
- `tick` ticks each matching item.
- `untick` unticks each matching item.
- `next` shows the first item that is not ticked. When all items are ticked, it shows nothing.

Each argument must match one unique line. If one argument does not match, the command fails and changes nothing.

### Files

```shell
dfr $ID files              # List the files.
dfr $ID files:reorder      # Put the files in a new order.
dfr $ID files:archive      # Archive the given files.
```

`files:reorder` takes filenames in the new order. The files that are not given come after them, in their old order.

`files:archive` takes filenames as arguments. Each name must match a file exactly. If one name does not match, the command fails and archives nothing.

## Skills

The skills are in the `skills/` folder. Copy or link them into `~/.claude/skills/`:

```shell
ln -s "$PWD/skills/dfr" ~/.claude/skills/dfr
```

The `dfr` skill makes a new thing when you give no ID. When you give an ID, it reads the context files of that thing and continues the work.

## Web UI

Start the Web UI:

```shell
php artisan serve
```

Then open http://localhost:8000. Run `npm run build` first if `public/build/` does not exist.

- The sidebar lists the things as a tree, with the ID, the name and the time of the last command. The most recently active thing is first. A parent moves up when one of its children is active. The sidebar updates every 30 seconds.
- The sidebar shows the folder names of the projects of each thing.
- The page of a thing shows the full paths of its projects.
- The page of a thing shows its context files in order. The body shows as Markdown, and the frontmatter as YAML. Archived files do not show.
- The header of a file with todo items shows how many are ticked, like `2/3`.
- A click on the checkbox of a todo item ticks or unticks it. The click fails when the file changed after the page loaded. The page then shows the new content.
- **Edit** opens the full content of a file, with its frontmatter. **Save** writes the file.

Save fails when the file changed after you opened it, for example when a session wrote to it. Your text stays in the editor, so you can copy it.

Each save and each tick writes a log line with the session `web`.

The Web UI has no login. `php artisan serve` listens only on your own machine.

## License

MIT. See [LICENSE](LICENSE).
