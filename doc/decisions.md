# Decisions

## Location of the things

Each thing has its own folder in `~/.dfr/`. The folder name is the ID of the thing. `rename` does not move the folder.

The config file `~/.config/dfr/config.json` can change the location:

```json
{
    "things_path": "~/.dfr"
}
```

## ID

The ID uses lowercase letters and digits. It does not use the lookalike characters `0`, `o`, `1`, `l` and `i`. This leaves 31 characters.

An ID has 3 random characters. When all 3-character IDs are used, new IDs get 4 characters. When all 4-character IDs are used, new IDs get 5, and so on.

`init` makes the folder with `mkdir`. If the folder exists, `mkdir` fails and `init` tries a new ID. Two sessions cannot get the same ID.

## Data of a thing

The data of a thing is in files in its folder:

- `.thing.json` holds the name, the parent, the git commits and the order of the files.
- `.log.jsonl` gets one line for each command on the thing.

A file or folder whose name starts with `.` is never a context file.

## Frontmatter

Frontmatter is optional. No field is required.

## Session

`dfr` reads the session ID from the env var `CLAUDE_CODE_SESSION_ID`. Claude Code sets this variable in each Bash call. When the variable is not set, the log line has the session `unknown`.

With the session ID, you can open the transcript of the session, or continue it with `claude --resume <id>`.

## Order of the files

The order of the files is a list in `.thing.json`. A new file goes to the end of the list.

`files:reorder` takes filenames in the new order. The files that are not given come after them, in their old order.

## Archive

`files:archive` moves the file to `.archive/` in the folder of the thing. It also removes the file from the order. `files`, `read:all` and `search` do not show archived files.

If `.archive/` already has a file with that name, a number goes before the extension: `plan.md`, then `plan (2).md`, then `plan (3).md`.
