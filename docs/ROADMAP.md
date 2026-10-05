# Roadmap

This roadmap is ordered. Work sessions should not silently skip ahead.

Status values: `NOT STARTED`, `IN PROGRESS`, `BLOCKED`, `ACCEPTED`.

## Gate 0: Project foundation

**Status: ACCEPTED**

Goals:

- create the public `jimlunsford/coagmentator` repository
- establish AGPL-3.0-or-later licensing
- publish project doctrine and architecture
- establish security invariants
- establish the mandatory work-chat handoff process
- establish contribution and review conventions

Acceptance criteria:

- public repository exists
- foundational docs are committed to the default branch
- `AGENTS.md` requires work-note handoffs
- latest roadmap state is unambiguous

Acceptance evidence:

- public repository: `jimlunsford/coagmentator`
- default branch: `main`
- full AGPL-3.0 license text committed
- foundational project, architecture, security, roadmap, decision and work-chat documentation committed
- work-note template and initial handoff notes established

## Gate 1: Contracts and threat model

**Status: NOT STARTED**

Goals:

- finalize MVP tool inventory
- define request and response contracts
- define normalized error contract
- define mutation receipt contract
- document capability mapping for each tool
- validate WordPress-side authentication choice
- validate MCP-client authentication requirements
- document threat scenarios and mitigations

No production connection is permitted in this gate.

## Gate 2: WordPress bridge read foundation

**Status: NOT STARTED**

Goals:

- create WordPress plugin skeleton
- implement authentication integration
- implement capability framework
- implement read-only site/content endpoints
- implement normalized errors
- add automated tests

Candidate operations:

- site info
- search posts/pages
- get post/page
- categories/tags lookup
- media search
- revision lookup

## Gate 3: WordPress bridge editorial mutations

**Status: NOT STARTED**

Goals:

- create drafts
- update posts/pages
- publish eligible content
- move content to Trash
- taxonomy mutations needed by editorial workflows
- metadata allowlist
- revision restore
- media upload and featured-image assignment
- mutation receipts and readback verification

## Gate 4: MCP server foundation

**Status: NOT STARTED**

Goals:

- create TypeScript MCP service
- implement client authentication selected in Gate 1
- expose read tools
- expose write tools against shared contracts
- normalize WordPress errors
- add service audit correlation
- add automated tests

## Gate 5: End-to-end non-production integration

**Status: NOT STARTED**

Goals:

- deploy MCP server to controlled infrastructure
- install WordPress bridge on a non-production WordPress target
- verify authenticated read operations
- verify editorial write operations
- verify failure modes
- verify audit trail
- verify credential isolation

Production JimLunsford.com must not be the first end-to-end test target.

## Gate 6: JimLunsford.com production acceptance

**Status: NOT STARTED**

Goals:

- install production WordPress bridge
- create least-privilege service identity
- connect production to the MCP service
- start with read-only acceptance
- explicitly authorize write-tool enablement
- execute controlled draft/update/media tests
- verify rollback and revision paths
- document production configuration without secrets

## Gate 7: v0.1.0 release

**Status: NOT STARTED**

Goals:

- security review
- dependency review
- CI matrix
- installation documentation
- operator documentation
- contributor documentation
- release packaging
- immutable tag and GitHub Release

## Later roadmap

Potential later work, not authorized by the MVP roadmap:

- theme inspection and editing
- plugin lifecycle management
- narrowly allowlisted WP-CLI operations
- multisite support
- richer SEO plugin integrations
- multiple WordPress connections per MCP server
- additional MCP client compatibility
