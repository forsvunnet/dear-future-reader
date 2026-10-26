# Dear Future Reader

Read [README.md](README.md) to learn what the project does.

## Style

Follow the rules in @STYLE.md for all code.

## Phases

The work is split into the phases in [PHASES.md](PHASES.md). Do the phases in order.

When a phase is done, write a report in `doc/report/`. Start the filename with the time in UTC, in this format: `20261025T143000Z-phase-0.md`.

Each report has these sections:

- **Done**: what the phase built or changed.
- **Decisions**: each decision and the reason for it.
- **Open**: problems and questions that are not solved yet.

After the report, update "Current status" below.

## Current status

- The README describes all the commands of the CLI.
- PHASES.md lists the phases.
- Phase 0 is done. The decisions are in [doc/decisions.md](doc/decisions.md).
- Phase 1 is done. The Laravel app, Pest, Larastan and the `dfr` script are set up.
- Run the tests with `./vendor/bin/pest`. Run Larastan with `./vendor/bin/phpstan analyse`.
- Phase 2 is done. The core commands are in the Dfr domain in `domain/Dfr/`.
- Phase 3 is done. The todo commands are in the Dfr domain too.
- Phase 4 is done. All commands in the README work. Each command on a thing goes through `RunOnThing`, which checks the ID and writes the log.
- Phase 5 is done. The `dfr` skill is in `skills/dfr/`. Thing `mpg` ran over two sessions with the skill.
- Phase 6 is done. The Web UI is in the Dfr domain. Start it with `php artisan serve`.
- All phases are done.
- Each thing shows its projects: the git repository where `init` ran, the repositories from `project:add` and the repositories of its commits. See `ThingProjects`.
