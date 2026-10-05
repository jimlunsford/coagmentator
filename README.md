# Coagmentator

Coagmentator is open-source infrastructure for securely connecting AI clients to WordPress through the Model Context Protocol (MCP).

The project exists to provide a self-hosted, auditable alternative to metered third-party AI-to-WordPress bridges. The initial reference deployment is JimLunsford.com, but the project is designed as general-purpose open-source infrastructure.

## Name

**Coagmentator** is derived from Neo-Latin usage for one who joins or connects things together. The name reflects the project's role as the controlled connection between an AI client and WordPress.

## Project goals

- Self-host the AI-to-WordPress connection.
- Keep WordPress authority inside WordPress capability checks.
- Expose narrow, explicit MCP tools instead of arbitrary remote execution.
- Make every write auditable and verifiable.
- Keep the architecture useful beyond a single site.
- Maintain complete project continuity in the repository so future work sessions do not depend on chat memory.

## Initial architecture

```text
AI client
   |
   | MCP over HTTPS
   v
Coagmentator MCP Server
   |
   | authenticated WordPress requests
   v
Coagmentator WordPress Bridge
   |
   v
WordPress
```

The MCP server and WordPress bridge are separate components with a shared contract layer.

## Repository layout

```text
apps/
  mcp-server/              MCP transport, tool exposure, client-side auth and orchestration
wordpress/
  coagmentator/            WordPress plugin and REST-side capability enforcement
packages/
  contracts/               Shared request, response and error contracts
docs/
  PROJECT.md               Scope and product doctrine
  ARCHITECTURE.md          Technical architecture and trust boundaries
  ROADMAP.md               Ordered implementation plan and current state
  DECISIONS.md             Durable architecture and product decisions
  SECURITY.md              Security model and non-negotiable restrictions
  WORK-CHAT-PROTOCOL.md    Required process for AI work sessions
  work-notes/              Dated handoff notes from completed work sessions
AGENTS.md                   Mandatory repository instructions for AI coding agents
```

The code directories will be created as implementation begins. Documentation is intentionally established first.

## Status

**Project inception. No implementation has begun.**

Read `AGENTS.md` and `docs/ROADMAP.md` before starting implementation.

## License

Planned license: **GNU Affero General Public License v3.0 or later (AGPL-3.0-or-later)**.

The full license text must be added before the first public code release.
