# Ingest

## Why

Work starts in many places: emails, reports, phone calls, loose notes. Today each piece must be read, understood and turned into work by hand.

Source material often depends on context that nobody wrote down. It is difficult to add that context up front.

Clients often ask for what they think they want. The real need can be different.

When work is done, it is difficult to find where it started and why.

## Desired outcome

The user puts things into one pile and forgets them. The next day, a short list of questions waits. The user answers them. After that, the work gets done, and usually ends in pull requests.

Each piece of work can be followed back to the exact words where it started.

## Requirements

1. **Take anything.** The pile accepts any input, in any form.
2. **Keep the source.** Each input is saved as it is. Nothing changes it later.
3. **Know the origin.** Each input records where it came from and when.
4. **Trace the work.** Each piece of work leads back to the exact words in its sources. This includes the start and each later turn.
5. **Split mixed input.** One input can hold many separate points. Each point is handled on its own.
6. **Sort by kind.** Each point is information, a complaint, a change request or a feature request.
7. **Ask about gaps.** When context is missing, the user gets a question. The answer is saved as a new source.
8. **Find the real need.** A request is checked for the need behind it before work starts.
9. **Few questions.** The user gets a short list at one time of day. The list must not become noise.
10. **Verify against the input.** Each change is verified as in [change.md](../change.md). The input gives the before state, and the outcome gives the after state. The user puts the two side by side and sees if the problem is solved.
11. **Show what matters.** The outcome separates important and risky changes from trivial ones.
12. **Work without the user.** Clear points go from the pile to finished work without the user.
13. **Safe merges only.** A change is merged without the user only when it is completely safe.

Notes on how to build this are in [implementation-notes.md](implementation-notes.md).
