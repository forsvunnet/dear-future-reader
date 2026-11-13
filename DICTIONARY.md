# Dictionary

Each word has one meaning in this project.

## Blast radius

The list of things that a change can affect. It is usually a list of files. It can also hold servers, binaries, daemons or data. The plan step of a change names it. See [doc/change.md](doc/change.md).

## Change

Work that makes the state of something outside the conversation different. The thing can be a file, a server, a binary, a daemon or data. A new feature, a bug fix and a deleted file are each a change. A change goes through the steps align, research, plan, apply and verify. See [doc/change.md](doc/change.md).

## Ingest

The single place that receives any input and saves it as a saved item. An agent reads the saved items later. See [doc/ingest/overview.md](doc/ingest/overview.md).

## Inquiry

Work that gives knowledge and changes nothing. Research and an investigation are inquiries. An inquiry goes through align, research and answer. See [doc/change.md](doc/change.md).

## Kind

The sort of a point. A point is information, a complaint, a change request or a feature request. All points of one kind go through the same procedure. See [doc/ingest/implementation-notes.md](doc/ingest/implementation-notes.md).

## Origin

Where a saved item came from, for example the sender, the channel, the file path or the URL.

## Point

One separate subject in a saved item. A point links to its saved item, keeps a quote of the exact words, and has one kind. Points can link to each other.

## Provenance

The chain from a piece of work back to the exact words where it started: work → point → saved item → origin.

## Question

What the agent asks the user when context is missing. The questions go to the user in one short list each day. The answer is saved as a new saved item.

## Saved item

One input in the ingest, saved as it is. It has content, origin and received at. Nothing changes it after the save.

## Verify

The last step of a change. Observe the outcome in a real situation, through the whole flow, before and after the change, in a few different cases. In the ingest, the input gives the before state and the outcome gives the after state. See [doc/change.md](doc/change.md).
