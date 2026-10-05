# Decision Log

This file contains durable accepted decisions and clearly labeled review candidates. D-001 through D-008 remain accepted. Gate 1 entries are proposed until the owner accepts the gate; publication of a design PR is not acceptance. Work notes may explain how a decision was reached, but durable direction belongs here.

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

## D-009: Fixed editorial inventory and one-site binding

**Status:** Proposed for Gate 1 acceptance
**Date:** 2026-10-05

Adopt the 21-tool inventory in [MCP-TOOLS.md](MCP-TOOLS.md): ten reads and eleven writes. Unify post/page lookup, force creation to draft, separate publication and Trash, omit bulk/generic dispatch and support one site/operator/service identity per instance. Add a read-only mutation-status tool for uncertain outcomes. Fixed site/actor identity and timestamps are injected by MCP; new mutation handles are generated and durably tracked by Coagmentator, then returned for explicit lookup/resumption. Model results contain useful verified evidence; internal receipt/trace/identity fields remain protected. This finalizes the candidate inventory in the Gate 0 architecture without weakening D-006.

## D-010: Separate OAuth and WordPress service credentials

**Status:** Proposed for Gate 1 acceptance
**Date:** 2026-10-05

Use an established OAuth authorization server with authorization code, PKCE S256, issuer/audience/subject/scope checks, protected-resource discovery and short-lived tokens for the MCP boundary. Pre-registered clients are the MVP baseline; safe provider-supported CIMD is preferred as an additional mode; legacy DCR is disabled. WordPress uses a separate dedicated Application Password over HTTPS, protected at rest and independently revocable. No OAuth token passthrough or homegrown auth server. Details: [AUTHENTICATION.md](AUTHENTICATION.md).

## D-011: Native capabilities with custom narrowing and mandatory guard

**Status:** Proposed for Gate 1 acceptance
**Date:** 2026-10-05

Native WordPress capabilities remain the authority, with extra Coagmentator operation capabilities and configuration checks. A dedicated role holds only the needed primitives. Because those primitives can authorize native REST writes, a must-use guard blocks dedicated service users from all alternative REST/XML-RPC/self-management/authentication paths before credentials are issued. It grants no privileges and remains active without feature handlers. Revoke credentials before removal. Details: [CAPABILITIES.md](CAPABILITIES.md).

## D-012: Explicit approval profiles and honest mutation evidence

**Status:** Proposed for Gate 1 acceptance
**Date:** 2026-10-05

Human-review correction, 2026-10-05: support `strict` and `trusted_single_operator` policy profiles, with every write family initially disabled. Strict retains separate human WordPress approval for publication, Trash, uploads, term updates, revision restore and live/private edits, bound to complete intent/preconditions for ten minutes. Trusted is suitable for the reference workflow through explicitly enabled standing server authorization plus client confirmation behavior. Both enforce OAuth/scopes, fixed binding, Application Passwords, guard, native/custom capabilities, schemas, versions, deduplication and verification. Service identities cannot approve or change policy. Client confirmation is UX, not authorization or cryptographic intent proof; compromised MCP can exercise all enabled trusted-profile writes. Every write uses a durably tracked server-generated request key/timestamp and readback receipt, with explicit partial/unknown outcomes and no blind retry after uncertain execution. Versions and locks prevent stale/parallel bridge writes but do not claim atomicity against native WordPress writers or exactly-once hook effects. Details: [CONTRACTS.md](CONTRACTS.md), [ERRORS.md](ERRORS.md).

## D-013: Bounded raw content, media and metadata

**Status:** Proposed for Gate 1 acceptance
**Date:** 2026-10-05

Read raw stored source without execution; constrain writes to safe static HTML/core blocks. Human-review correction: expose supported ChatGPT top-level file parameters. MCP alone retrieves files through a reviewed source profile, strict SSRF/HTTPS/no-redirect/streaming-byte/decoder controls and computes the input digest. WordPress receives only the internal bounded byte contract, independently decodes/re-encodes and strips metadata. Arbitrary caller URLs/paths and bridge URL downloads remain prohibited; direct clients cannot bypass the file-source restrictions. Expose only `editorial.note`, `seo.title` and `seo.description`; SEO uses an optional bridge-owned basic renderer with verified sole ownership, not guessed vendor internals. Third-party SEO integration remains later compatibility work; actual ChatGPT file handoff requires Gate 5 evidence. Refuse Trash when core would permanently delete. Revision restore copies only selected editorial fields. Details and limitations: [CONTRACTS.md](CONTRACTS.md).

## D-014: Current MCP revision with explicit legacy adapter

**Status:** Proposed for Gate 1 acceptance
**Date:** 2026-10-05

Research on 2026-10-05 resolved the current MCP specification to `2026-07-28`. Target its stateless Streamable HTTP profile and an explicit `2025-11-25` compatibility profile, sharing the same tool/security contract. Do not implement deprecated HTTP+SSE, assume initialization is universal, or choose an SDK based solely on an old example. Pin/test the official SDK in Gate 4 and verify actual ChatGPT behavior in Gate 5. Source links and responsibility boundaries: [AUTHENTICATION.md](AUTHENTICATION.md).
