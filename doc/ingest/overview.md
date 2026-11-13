# Ingest overview

## The problem

Work starts in many places: emails, reports, phone calls, loose notes. Each one must be read, understood and turned into work by hand.

The sources often depend on context that nobody wrote down. Requests often ask for what the client thinks they want, which can differ from the real need.

When the work is done, it is difficult to find where it started and why. So it is difficult to check that the work solved the problem.

A change can be large. Most of it can be trivial, like styling. A small part can be risky, like a number that changes from an integer to a float. The risky part is easy to miss among the trivial parts.

## The idea

One place takes in everything and keeps it as it is. The material is understood, sorted and turned into work with as little effort from the user as possible.

The user is asked only about what is truly unclear. Everything that is clear and safe gets done without the user.

## The main goal

Automate the path from input to outcome, so that the user can easily verify the outcome without the noise.

The user puts the input next to the outcome and sees if the work solved the problem. This is the verify step of a change, as in [change.md](../change.md). The input gives the before state, and the outcome gives the after state. To make this possible:

- **Provenance.** Each piece of work can be followed back to the exact words where it started, and to each later turn.
- **Focus on what matters.** The outcome shows the important and risky changes first. Trivial changes stay out of the way.

## The documents

- [idea.md](idea.md): the why, the desired outcome and the requirements.
- [implementation-notes.md](implementation-notes.md): early notes on how to build it.
