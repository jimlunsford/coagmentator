# Decision Log

This file contains durable accepted decisions. Work notes may explain how a decision was reached, but accepted direction belongs here.

## D-001: Project name

**Status:** Accepted  
**Date:** 2026-10-05

The project is named **Coagmentator**.

The name reflects the project's role as the component that joins AI clients and WordPress while controlling that connection.

## D-002: Open development

**Status:** Accepted  
**Date:** 2026-10-05

Coagmentator will be developed publicly on GitHub from project inception.

Architecture, roadmap, decisions, security doctrine and work-session notes should be public unless a specific item contains credentials, personal data, an active vulnerability that requires responsible disclosure, or another concrete reason not to publish it.

Secrets must never be committed merely for the sake of transparency.

## D-003: Repository as project memory

**Status:** Accepted  
**Date:** 2026-10-05

The repository, not conversational memory, is the durable source of project direction.

Every substantive AI work session must leave a repository handoff note. Durable decisions must also update the relevant doctrine file.

## D-004: Start as a monorepo

**Status:** Accepted  
**Date:** 2026-10-05

The MCP server, WordPress bridge, shared contracts, tests and documentation will begin in one repository so protocol changes remain synchronized.

Repository splitting requires a later explicit decision.

## D-005: Separate MCP and WordPress responsibilities

**Status:** Accepted  
**Date:** 2026-10-05

The MCP server handles MCP transport, client-facing authentication, tool schemas and orchestration.

The WordPress plugin handles WordPress-facing authentication, capability checks, WordPress logic and mutation verification.

The MCP server does not directly edit the WordPress database or filesystem.

## D-006: Narrow tool surface before generic administration

**Status:** Accepted  
**Date:** 2026-10-05

The MVP prioritizes explicit editorial operations. Generic remote execution, unrestricted WP-CLI, SQL, PHP execution, filesystem writes and broad administration are excluded.

## D-007: Independent implementation

**Status:** Accepted  
**Date:** 2026-10-05

WPVibe and other open-source systems may be used as references for architecture, interoperability, WordPress behavior and security lessons.

Coagmentator will not be implemented as a copy or disguised fork of WPVibe.

## D-008: Project license

**Status:** Accepted  
**Date:** 2026-10-05

Coagmentator is licensed under **AGPL-3.0-or-later**.

The canonical full license text is committed at the repository root in `LICENSE`.
