# Project Definition

## Mission

Coagmentator is an open-source, self-hosted bridge that allows AI clients to perform controlled WordPress operations through MCP without requiring a metered third-party gateway.

The project should make common WordPress editorial and site-management work available to an AI assistant while preserving WordPress permissions, explicit tool boundaries, auditability and operator control.

## Initial reference use case

The first real deployment target is JimLunsford.com.

Typical desired interactions include:

- search existing posts and pages
- read complete post or page content
- create drafts
- update drafts and published content
- publish content
- move content to Trash
- inspect and restore revisions
- work with categories and tags
- search and upload media
- set featured images
- read and update approved post metadata, including SEO metadata
- inspect basic site information

The product should make these operations feel natural from an AI client while keeping every operation constrained by the bridge's explicit tool contract.

## Gate 1 MVP scope candidate

Pending human Gate 1 acceptance, [MCP-TOOLS.md](MCP-TOOLS.md) fixes the surface at 21 operations. The first deployment model has one site, one OAuth operator and one dedicated WordPress service user. All writes start disabled. Two explicit server-side approval profiles are supported: `strict` retains independent exact-intent WordPress approval for designated public/destructive operations; `trusted_single_operator` permits individually enabled write families under standing authorization plus MCP client confirmation behavior. The latter is intended for the JimLunsford.com reference workflow, subject to later deployment authorization. Both preserve OAuth/scopes, binding, Application Passwords, the must-use guard, native/custom capabilities, versions, deduplication and verified receipts. Client confirmation is a UX safeguard, not server authorization or proof against a compromised MCP host.

Post/page reads return complete stored source within documented bounds. Creation is draft-only, publishing is separate, and delete means recoverable Trash with an explicit check that Trash is enabled. There are no bulk operations, scheduling, multisite, custom post types or generic REST/Abilities tools.

The MVP accepts a top-level ChatGPT file parameter for raster uploads and supports safe static HTML/core-block edits. MCP alone retrieves client files through a reviewed source profile with strict HTTPS, SSRF, redirect, byte and decoder limits; arbitrary remote-media URLs remain prohibited. WordPress receives only validated bytes, independently re-encodes them and strips metadata. Neither component renders content for previews. Actual file handoff and hostile direct-client rejection must be demonstrated in non-production acceptance.

Tools do not ask the model for fixed site/actor identifiers or request timestamps. Coagmentator owns new mutation handles and timestamps, retains them durably across uncertainty, and returns the handle plus useful verified state. Internal audit identities, receipt IDs and trace times remain in protected records, separate from model output.

Approved metadata has three fixed logical keys: `editorial.note`, `seo.title`, and `seo.description`. The latter two require the opt-in bridge-owned basic SEO mode and verified sole ownership of their front-end outputs. Third-party SEO integration remains later work, so compatibility with the reference site is not assumed. These are explicit limits on the initial use case, documented fully in [CONTRACTS.md](CONTRACTS.md).

## Non-goals for the MVP

The first release is not intended to provide:

- arbitrary WP-CLI execution
- arbitrary shell access
- arbitrary SQL
- arbitrary PHP execution
- unrestricted plugin or theme modification
- unrestricted filesystem access
- unrestricted WordPress administration
- a hosted SaaS relay
- billing, subscriptions or usage metering

These are intentionally excluded to keep the first trust boundary narrow.

## Product principles

### Self-hosted first

A site owner should be able to run the bridge on infrastructure they control.

### Least privilege

The system should expose only the operations required for the intended workflow.

### WordPress remains authoritative

WordPress capability checks and WordPress data models remain the final authority for WordPress operations.

### Explicit operations over remote execution

Prefer `update_post` over a generic command runner. Prefer `set_featured_image` over filesystem manipulation.

### Verify writes

A successful mutation should return enough verified state to prove what actually changed.

### Auditability

Security-relevant and content-changing operations should produce structured audit records without logging secrets.

### Portable architecture

JimLunsford.com is the reference deployment, not a hard-coded dependency.

### Repository as memory

Architecture, decisions, roadmap state and work-session handoffs must live in Git so later work sessions can recover project direction without depending on conversational memory.

## Independence from WPVibe

WPVibe and other open-source WordPress/MCP systems may be studied as references for interoperability, WordPress behavior, threat surfaces, safety mechanisms and product lessons.

Coagmentator is an independent implementation. Its architecture, contracts, code, tests and documentation are developed independently.
