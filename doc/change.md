# Change

A change is work that makes the state of something outside the conversation different. The thing can be a file, a server, a binary, a daemon or data.

Examples:

- A new feature.
- A bug fix.
- A config edit on a server.
- A deleted file.
- A migration of data.

Each change has a before state, an after state, a blast radius and a requirement.

## Change and question

A question is work that gives knowledge. Research and an investigation are questions. A question changes nothing, so it has no blast radius and no after state to verify.

A question goes through align, research and answer.

An investigation can end in a change. Then the work continues as a change at the plan step. The research is already done.

## Steps

A change goes through five steps, in this order.

1. **Align.** Agree on what the change must do. The result is a requirement that the verify step can check.
2. **Research.** Find the current state of the things the change touches.
3. **Plan.** Write the steps of the change. Name the blast radius.
4. **Apply.** Do the steps of the plan.
5. **Verify.** Check that the after state meets the requirement. A test is one way to verify.

## Blast radius

The blast radius is the list of things that the change can affect. It is usually a list of files. It can also hold servers, binaries, daemons or data.

The plan names the blast radius before the apply step starts.
