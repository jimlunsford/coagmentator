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
