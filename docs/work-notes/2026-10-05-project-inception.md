# Work Note: Project Inception

Date: 2026-10-05  
Roadmap gate: Gate 0  
Status: PARTIAL

## Goal

Define Coagmentator as a public open-source project and establish enough repository documentation that future AI work sessions can preserve project direction without relying on previous chat memory.

## Starting state

- Project name selected: Coagmentator
- No Coagmentator repository existed yet
- No implementation existed
- The target reference use case was replacing a metered third-party AI-to-WordPress bridge for JimLunsford.com with self-hosted infrastructure

## Work completed

- Locked the project name as Coagmentator.
- Chose public open development.
- Chose a monorepo starting model.
- Defined a two-part architecture: MCP server plus WordPress bridge plugin.
- Defined shared contracts as a separate architectural concern.
- Defined least-privilege and explicit-tool principles.
- Excluded arbitrary shell, SQL, PHP execution and unrestricted filesystem writes from the MVP.
- Established AGPL-3.0-or-later as the license direction.
- Created a phased roadmap from repository foundation through production acceptance and v0.1.0.
- Established mandatory work-chat handoff notes.
- Established the rule that durable doctrine must be updated in permanent docs rather than only in session notes.
- Documented WPVibe as reference material only, with Coagmentator remaining an independent implementation.

## Files prepared

- `README.md`
- `AGENTS.md`
- `docs/PROJECT.md`
- `docs/ARCHITECTURE.md`
- `docs/SECURITY.md`
- `docs/ROADMAP.md`
- `docs/DECISIONS.md`
- `docs/WORK-CHAT-PROTOCOL.md`
- `docs/work-notes/README.md`
- `docs/work-notes/2026-10-05-project-inception.md`

## Verification

- Documentation reviewed for internal consistency.
- No code or production systems were changed.

## Decisions

See `docs/DECISIONS.md` entries D-001 through D-008.

## Risks / unresolved items

- The public GitHub repository must still be created before these files can become the shared source of truth.
- The canonical full AGPL license text still needs to be added.
- MCP-facing client authentication requirements must be verified during Gate 1 before implementation is locked.
- The WordPress Application Password approach is the current preferred direction, but requires Gate 1 validation.

## Exact next step

Create the empty public GitHub repository `jimlunsford/coagmentator`, then commit the Gate 0 documentation scaffold to the default branch and verify the public repository state.
