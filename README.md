Dear Future Reader

Agents quickly reach their session limit and when working with multiple concurrent session it's difficutl to keep track of what goes in which session.

Dear future reader offers a simple yet effective remedy

Each thing, be it task, feature, bugfix, planning session etc.. get's it's own folder, and session or sessions on the same thing will read, write and update the same context files. Context files could be plan, todo, affected files, prompts, instructions, progress, reports, facts, etc....

It comes in three parts:

- **CLI**: commands to create and update the context files.
- **Skills**: instructions that teach agents how to use the CLI.
- **Web UI**: a page to browse and read the context files.

To begin:

```shell
ID="$(dft init name-of-thing)"
```

The output of the commad will return an id that you must use to do things in the name of the thing.

```shell
dtf $ID write [FILENAME] # You can pipe into write, or use arguments, or both. in the case of both it will be concattenated with the pipe data first. Filename is required. If the file exists it will be overwritten. Will give warnings if required frontmatter is not present.

dtf $ID write:append [FILENAME] # Same as write, but will append instead of overwrite

dtf $ID write:prepend [FILENAME] # Same as write, but will prepend instead of overwrite

dtf $ID fm [FILENAME] [YAML] # Frontmatter: write or replace frontmatter
dtf $ID fm:add [FILENAME] [YAML] # Frontmatter: add or replace frontmatter. existing attributes will be overwritten or kept, new attributes will be added.

dft $ID todo "Setup" add "Define dependencies" "Install packages" "Run build command" # Will append three items to the "Setup" todolist. "Setup" must be unique and it has to be a title or text line in one of the files followed by one or more markdown todo items.
dft $ID todo "Setup" add:after "install" "Verify installation" # same as todo $NAME add, but will instead add items after the install step. Useful if you want to inject items in the middle of a todo.
dft $ID todo "Setup" add:before "build" "Verify installation" # same as after, but inserts before instead.
dft $ID todo "Setup" tick "deps" "install" "build" # Will tick the three tasks inside setup if it finds unique lines for each of the args. if one is not found the command will error and nothing gets ticked.
dft $ID todo "Setup" untick "deps" "install" "build" # Will tick the three tasks inside setup if it finds unique lines for each of the args. if one is not found the command will error and nothing gets ticked.
```

Other commands

dft $ID files - lists the files
dft $ID files:reorder - put the files in new order
dft $ID files:content - returns all filecontent content everything in order


dft $ID files:archive - archive files. args are filenames and they must match. if any file doesn't match exactly then the command will error and no file will be archived