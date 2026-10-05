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

The MCP server authenticates AI clients with OAuth over HTTPS and calls a separate WordPress bridge using a dedicated service identity. It accepts supported client-file parameters, retrieves files under a restricted policy and sends only validated bytes to WordPress. WordPress enforces capabilities, verifies writes and returns receipts. A must-use guard limits the service identity to the documented operations. Strict and trusted single-operator approval profiles share the same server authorization; model results expose useful mutation evidence while protected records retain the audit details. See [architecture and trust boundaries](docs/ARCHITECTURE.md).

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

**Gate 0 and Gate 1 are accepted. Gate 2 preparation is accepted. Gate 2 remains IN PROGRESS.**

C01 package/test implementation is partial on `feature/gate-2-c01-package-test-skeleton`; it is not ready for acceptance. See the [Actions continuation handoff](docs/work-notes/2026-10-05-gate-2-c01-actions-continuation.md). The [Gate 2 implementation plan](docs/GATE-2-IMPLEMENTATION-PLAN.md), [read route specifications](docs/GATE-2-READ-ROUTES.md) and [support/test matrix](docs/TEST-MATRIX.md) were human-reviewed and accepted on 2026-10-05. C02 has not begun; no service identity, Application Password or bridge route exists.

Read `AGENTS.md` and `docs/ROADMAP.md` before starting implementation.

Gate 1 review documents:

- [21-tool MVP inventory](docs/MCP-TOOLS.md)
- [Shared contracts, approvals and receipts](docs/CONTRACTS.md)
- [Capability and service-user mapping](docs/CAPABILITIES.md)
- [Authentication and current MCP compatibility](docs/AUTHENTICATION.md)
- [Normalized errors](docs/ERRORS.md)
- [Threat model](docs/THREAT-MODEL.md)

## License

Coagmentator is licensed under the **GNU Affero General Public License v3.0 or later (AGPL-3.0-or-later)**.

See `LICENSE` for the full license text.
