# Ingest

A single point that receives everything and saves it. An agent reads it later.

## Why

Source material, like an email from a client, often depends on context that nobody wrote down. It is difficult to add that context up front.

An agent that reads the material can see what it does not know. It can ask about it.

## The daily loop

1. The user puts things into the ingest and forgets them.
2. Over night, the agent sorts the pile and prepares questions about what is still unclear.
3. The next day, the user answers the questions.
4. The agent does the work. The work usually ends in pull requests.
5. The pull requests get a review. The agent merges some of them itself.

## Provenance

Provenance is the main goal. Each piece of work must lead back to where it started.

The chain goes:

work → point → saved item → origin

From any task, bug fix or feature, a reader can follow the chain back. The reader finds the exact words where the work was first thought about.

The links are loose. Work can link to many points, and points can link to each other. This keeps the start and each later turn in the work.

## Flow

1. **Receive.** The ingest point takes any input: email, report, phone call, chat, link, loose notes.
2. **Save.** The ingest point saves the input as it is, with its origin.
3. **Sift.** An agent reads the saved input and tries to understand it.
4. **Ask.** When context is missing, the agent asks the user a question.
5. **Record.** The answer is saved as a new source too.

## Step one: save with origin

This is the first step to build. Each saved item has:

- **Content**: the raw input. Nothing changes it after the save.
- **Origin**: where it came from, for example the sender, the channel, the file path or the URL.
- **Received at**: the time of the save.

## Step two: split and sort

One saved item usually holds many points. An email can confirm a delivery, report a bug and ask for a feature.

The agent splits the item into points. Each point links back to the item it came from. Each point also keeps a quote of the exact words in the item. Each point gets one kind:

- **Information**: a fact for later, or a confirmation that something was done.
- **Complaint**: something went wrong, for example a bug.
- **Change request**: change something that exists.
- **Feature request**: add something new. A feature request leads to tasks.

## Step three: process each kind

Each kind is a bucket. All points in one bucket go through the same procedure. Skills or agents can do the bulk of this work, and handle many points without the user.

Before an agent acts on a point, it checks two things:

1. **Enough information?** If not, the agent asks.
2. **Should it be done?** A client often asks for the thing they think they want. The real need can be different. The agent looks for the need behind the request.

A point that passes both checks can be handled automatically. Other points go to the user as questions.

### The work cycle

Today the user drives each piece of work by hand:

1. The user says what they want.
2. The agent outlines what it understood. The user confirms.
3. The agent makes a plan and finds what the change can affect. The user confirms.
4. The agent builds it.

To automate this, a second agent takes the place of the user. It confirms the outline and the plan. When it is not sure, it sends a question to the user's morning list.

## Step four: pull requests and merge

The work on a point usually ends in a pull request. The pull request links back to its points.

The agent can merge a pull request itself only when it is completely safe and harmless. Strict rules decide this. All other pull requests wait for the user.

## Open

- What are the strict merge rules?

- What are the first input channels?
- Does an item belong to a thing, or is the ingest point outside the things?
- How does the agent ask, and where does the user answer?
- How many questions are acceptable before the agent becomes noise?
