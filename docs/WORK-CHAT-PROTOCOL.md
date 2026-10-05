# Work Chat Protocol

## Purpose

AI coding sessions are temporary. The repository must preserve enough context that a new work session can continue correctly without access to the previous conversation.

This protocol is mandatory for substantive work.

## Start-of-session procedure

Before proposing or applying changes, the work session must:

1. read `AGENTS.md`
2. read `docs/PROJECT.md`
3. read `docs/ARCHITECTURE.md`
4. read `docs/SECURITY.md`
5. read `docs/ROADMAP.md`
6. read `docs/DECISIONS.md`
7. inspect the latest relevant work notes
8. verify the current Git branch, commit and open PR state when relevant
9. identify the exact roadmap gate being worked

The session should state the gate it is operating in before implementation begins.

## During-session discipline

A work session should keep its scope narrow enough that another reviewer can understand exactly what changed and why.

If the requested work changes the accepted architecture, security model or roadmap, update those documents in the same body of work rather than leaving the new direction only in chat.

If a new decision conflicts with an old decision, do not silently rewrite history. Add a new decision entry that supersedes the prior one and explain the reason.

## End-of-session procedure

Before declaring completion, the work session must create or update a work note in `docs/work-notes/`.

The work note must answer:

- What was the goal?
- What repository state did the session start from?
- What exactly changed?
- What files changed?
- What was tested?
- What evidence proves the result?
- What decisions were made?
- What remains unresolved?
- What is the next exact step?

If the roadmap state changed, update `docs/ROADMAP.md`.

If architecture changed, update `docs/ARCHITECTURE.md`.

If security invariants changed, update `docs/SECURITY.md`.

If a durable decision was accepted, update `docs/DECISIONS.md`.

## Notes are not a dumping ground

A work note is a handoff artifact, not a raw transcript.

Do not paste hidden reasoning, secrets, credentials or irrelevant conversational material.

Record decisions, evidence, state and next actions in concise engineering language.

## Naming convention

Use:

`YYYY-MM-DD-short-topic.md`

If multiple sessions on the same topic occur on the same date, append a sequence:

`YYYY-MM-DD-short-topic-02.md`

## Completion rule

A substantive work session is not complete until its handoff note exists in the same repository state intended for the next session.
