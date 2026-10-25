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

### Write

```shell
dfr $ID write [FILENAME]          # Write the file. Overwrites the file if it exists.
dfr $ID write:append [FILENAME]   # Same as write, but adds to the end of the file.
dfr $ID write:prepend [FILENAME]  # Same as write, but adds to the start of the file.
```

The filename is required. The content can come from a pipe, from arguments, or from both. With both, the pipe data comes first.

The command gives a warning if the required frontmatter is missing.

### Frontmatter

```shell
dfr $ID fm [FILENAME] [YAML]      # Write or replace all of the frontmatter.
dfr $ID fm:add [FILENAME] [YAML]  # Add or replace the given attributes. Keeps the other attributes.
```

### Todo

A todo list is a title or a text line in a file, followed by one or more Markdown todo items. The title must be unique.

```shell
dfr $ID todo "Setup" add "Define dependencies" "Install packages" "Run build command"
dfr $ID todo "Setup" add:after "install" "Verify installation"
dfr $ID todo "Setup" add:before "build" "Verify installation"
dfr $ID todo "Setup" tick "deps" "install" "build"
dfr $ID todo "Setup" untick "deps" "install" "build"
```

- `add` adds the items to the end of the list.
- `add:after` adds the items after the matching item.
- `add:before` adds the items before the matching item.
- `tick` ticks each matching item.
- `untick` unticks each matching item.

Each argument must match one unique line. If one argument does not match, the command fails and changes nothing.

### Files

```shell
dfr $ID files              # List the files.
dfr $ID files:reorder      # Put the files in a new order.
dfr $ID files:content      # Show the content of all files, in order.
dfr $ID files:archive      # Archive the given files.
```

`files:archive` takes filenames as arguments. Each name must match a file exactly. If one name does not match, the command fails and archives nothing.

## Skills

The skills are in the `skills/` folder. Copy or link them into `~/.claude/skills/`.

## Web UI

Start the Web UI:

```shell
php artisan serve
```

The Web UI lists each thing. You can read and edit the context files of each thing.

## License

MIT. See [LICENSE](LICENSE).
