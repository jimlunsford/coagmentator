# Coagmentator Agent Instructions

This file is mandatory context for every AI-assisted work session in this repository.

## Source of truth

Do not rely on previous chat memory as project authority. The repository is the source of truth.

Before making changes, read in this order:

1. `AGENTS.md`
2. `docs/PROJECT.md`
3. `docs/ARCHITECTURE.md`
4. `docs/SECURITY.md`
5. `docs/ROADMAP.md`
6. `docs/DECISIONS.md`
7. The most recent relevant files in `docs/work-notes/`
8. Any files directly involved in the requested work

If repository state conflicts with a remembered discussion, repository state wins unless the owner explicitly changes direction.

## Work-session rule

Every substantive work session must leave a handoff note for the next work session.

Before declaring a task complete, create or update a dated file in `docs/work-notes/` containing:

- session goal
- starting repository state
- work completed
- files changed
- tests and verification performed
- decisions made
- assumptions that remain
- blockers or risks
- exact next recommended step
- branch, commit, PR, tag or release identifiers when applicable

Use the template in `docs/work-notes/README.md`.

## Durable documentation rule

Work notes are historical handoffs. Durable project truth belongs elsewhere.

When a session changes durable project truth, also update the appropriate file:

- project scope or product doctrine: `docs/PROJECT.md`
- architecture or trust boundaries: `docs/ARCHITECTURE.md`
- security invariants: `docs/SECURITY.md`
- ordered plan or phase status: `docs/ROADMAP.md`
- accepted durable decision: `docs/DECISIONS.md`

Never hide a permanent decision only inside a work note.

## Engineering boundaries

Until explicitly changed in a documented decision:

- Do not add arbitrary shell execution.
- Do not add arbitrary SQL execution.
- Do not add arbitrary PHP execution or `eval`.
- Do not add unrestricted filesystem writes.
- Do not expose WordPress credentials to MCP clients.
- Do not bypass WordPress capability checks.
- Do not make permanent deletion the default behavior.
- Do not broaden tool permissions merely for convenience.
- Do not copy WPVibe implementation code into Coagmentator.

Study external open-source implementations for interoperability, security concerns and architectural lessons, but implement Coagmentator independently.

## Change discipline

Prefer small, reviewable gates. Each implementation gate should have:

- explicit scope
- acceptance criteria
- tests
- verification evidence
- a handoff note

Do not silently begin the next gate after closing the current one.
