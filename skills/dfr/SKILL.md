---
name: dfr
description: Keep the context of a task in shared files with the `dfr` CLI, so that a later session can continue the work. Use when the user says dfr or "dear future reader", gives a dfr thing ID, or asks to make work that continues over many sessions.
---

# Dear Future Reader

A thing is one piece of work, like a task, a feature or a bug fix. Each thing has an ID and a set of context files. All sessions on the thing read and write the same files.

Write each file for a future reader who was not in this session. That reader can be you after the session limit.

## Start

When the user gives an ID, continue that thing:

```shell
dfr $ID read:all
```

Read all of the output before you do other work. Then continue from the next step in the files.

When the user gives no ID, make a new thing:

```shell
dfr init "short name of the task"
```

The command shows the ID. Tell the user the ID, so that a new session can continue the work.

Use `dfr list` and `dfr search [TEXT]` only when the user asks you to find a thing.

Shell variables do not stay between Bash calls. Write the ID itself in each command.

## Files

Use these files. Add other files when the work needs them.

- `plan.md`: the goal, the approach and the decisions with their reasons.
- `todo.md`: the steps as a Markdown todo list.
- `progress.md`: what each session did, and the next step.

Write the content through a pipe with a heredoc:

```shell
dfr $ID write plan.md <<'EOF'
# Plan
...
EOF
```

`write` replaces the file. `write:append` adds to the end of the file. Do not use `write` on `progress.md`. Use `write:append`, so that the history stays.

## While you work

A session can stop at any time. Write to the files as you go. Do not wait for the end of the session.

- Tick each todo item when it is done: `dfr $ID todo tick "item"`.
- Add a step when you find one: `dfr $ID todo add "item"`.
- Write each decision in `plan.md` when you make it.
- Connect each commit you make: `dfr $ID git:add HEAD`.

Before you stop, append to `progress.md`: what you did, what is not solved, and the next step.

## More commands

Run `dfr` with no arguments to see all commands. The main ones:

```shell
dfr $ID read [FILENAME]               # Show one file.
dfr $ID files                         # List the files.
dfr $ID files:archive [FILENAME...]   # Archive files that are no longer correct.
dfr $ID todo next                     # Show the first item that is not ticked.
dfr $ID log                           # Show which session ran which command, and when.
dfr init "name" --parent [PARENT_ID]  # Make a child thing for a large part of the work.
```

When a thing has more than one todo list, give `--list [NAME]` to `todo add` and `todo next`.
