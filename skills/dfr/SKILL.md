---
name: dfr
description: Keep the context of a task in shared files with the `dfr` CLI, so that a later session can continue the work. Use when the user says dfr or "dear future reader", gives a dfr thing ID, when a hook names the dfr thing of the session, or asks to make work that continues over many sessions.
---

# Dear Future Reader

A thing is one piece of work, like a task, a feature or a bug fix. Each thing has an ID and a set of context files. All sessions on the thing read and write the same files.

Write each file for a future reader who was not in this session. That reader can be you after the session limit.

## Keep the thing updated

A session can stop at any time. A change that is not in the thing is lost for the next session. After each change, before you go on:

1. Append what you did and the next step to `work-log.md`: `dfr $ID write:append work-log.md`.
2. Tick the todo items that are done.
3. Write each new decision and its reason in `plan.md`.
4. Connect each new commit: `dfr $ID git:add HEAD`.

Do not wait for the end of the task.

## Start

A hook can name the thing of the session, in a line that starts with "dfr:". Use that thing. If the hook made the thing, read the prompt, then give the thing a short name: `dfr $ID rename "short name"`.

When the user gives an ID, continue that thing:

```shell
dfr $ID read:all
```

Read all of the output before you do other work. Then continue from the next step in the files.

The first line of the output can name the projects of the thing. A project is the folder of a git repository. Work in those folders.

When the user gives no ID and no hook names a thing, make a new thing:

```shell
dfr init "short name of the task"
```

Run `init` in the folder of the project. The thing stores the git repository of that folder.

When the work is about more than one project, add each other project: `dfr $ID project:add [PATH]`. Also add a project that the work only reads.

The command shows the ID. Tell the user the ID, so that a new session can continue the work.

Use `dfr list` and `dfr search [TEXT]` only when the user asks you to find a thing.

Shell variables do not stay between Bash calls. Write the ID itself in each command.

## Files

Keep files short, focused and concise. New information and new work may change the direction of the work, and the files may need complete rewrites. Keeping them short makes this easier to manage.

Suggested filenames and content

- `plan.md`: the plan, the approach and the decisions with their reasons.
- `todo.md`: the steps as a Markdown todo list.
- `files.md`: the list of files relevant to the thing.
- `edited-files.md`: the list of files that have been edited.
- `work-log.md`: what each session did, and the next step.
- `review-instructions.md`: the instructions for reviewing the work. This is the verbatim text used to spawn a fresh agent for the review.
- `review.md`: the review result and feedback on the work.
- `qa.md`: the questions and answers related to the work.
- `goal.md`: the goal of the work.

More files for each kind of work. Use them when the work is of that kind:

- Planning: `constraints.md` for the limits, `options.md` for the possible paths.
- Investigation: `question.md` for the question to answer, `facts.md` for what is known, `findings.md` for what the work finds.
- Bug fix: `symptom.md` for what goes wrong, `reproduce.md` for the exact steps, `root-cause.md` for the cause.
- Change of behaviour: `current-behaviour.md` and `wanted-behaviour.md`.
- Code change: `blast-radius.md` for the code and behaviour that the change can affect, `verify.md` for the steps that prove the change works.

Write the content through a pipe with a heredoc:

```shell
dfr $ID write plan.md <<'EOF'
# Plan
...
EOF
```

`write` replaces the file. `write:append` adds to the end of the file.

## While you work

Follow "Keep the thing updated" after each change. Also:

- Tick a todo item with `dfr $ID todo tick "item"`.
- Add a step when you find one: `dfr $ID todo add "item"`.
- Add each new project you work in: `dfr $ID project:add [PATH]`.

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
