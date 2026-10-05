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

**Status: ACCEPTED**

Human review accepted the exact revised candidate on 2026-10-05; merged unchanged. Gate 2 was `NOT STARTED` at that closeout; its current preparation status is below.

Acceptance evidence:

- Accepted PR: [#1](https://github.com/jimlunsford/coagmentator/pull/1).
- Accepted candidate HEAD: `3a0979faf3156a94173c73b89e8861e3d8816594`.
- Accepted complete tree: `bf7db607bf913f727b5e24de9972bc06c0e7f6c5`.
- Merge commit and immediate post-merge `main` checkpoint: `4a99e61fc920983da86c53a0b2b47f5a9b5a7c2d`, with the identical accepted tree.
- D-009 through D-014 accepted without substantive design changes; [closeout handoff](work-notes/2026-10-05-gate-1-closeout.md) records verification and the Gate 2 preparation boundary.

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

Objective acceptance criteria:

- [x] A fixed tool inventory gives every operation's purpose, required/optional inputs, output, affected resources, validation and failures: [MCP-TOOLS.md](MCP-TOOLS.md), 21 tools.
- [x] Both sides share explicit envelopes, versioning, shapes, bounded fields, pagination, mutation inputs, status transitions and capability-aware response behavior: [CONTRACTS.md](CONTRACTS.md).
- [x] Every mutation has exact retry/approval/precondition rules, a readback strategy and a receipt, including no-op, partial and unknown outcomes; trusted server ownership of request IDs/timestamps and MCP/bridge lost-response recovery are explicit.
- [x] A single normalized error model covers auth, permissions, validation, missing resources, conflicts, unsupported features, limits, WordPress/transport failures and verification: [ERRORS.md](ERRORS.md).
- [x] Every tool has OAuth, custom and native capability mapping; service privileges and alternative-native-API bypass prevention are specified: [CAPABILITIES.md](CAPABILITIES.md).
- [x] Current primary-source WordPress and MCP/ChatGPT requirements are dated, linked and separated from project choices: [AUTHENTICATION.md](AUTHENTICATION.md).
- [x] Concrete threats identify assets, attack paths, impact, control ownership, residual risk and a later verification gate: [THREAT-MODEL.md](THREAT-MODEL.md), 30 scenarios.
- [x] Architecture, security, project scope and decision log agree with the focused documents. No security-critical mechanism is left for Gate 2 to invent.
- [x] Current official OpenAI file parameters, controlled retrieval with no arbitrary URL path, per-tool annotations, model/audit separation and both approval profiles have explicit contracts and consistency evidence. Strict self-approval remains prohibited; trusted compromised-MCP exposure is documented.
- [x] Documentation-only scope and internal-consistency checks are recorded in the [Gate 1 work note](work-notes/2026-10-05-gate-1-contracts-threat-model.md).
- [x] Human reviewer accepted the exact candidate and its explicit limits, identified above.
- [x] Authorized closeout records acceptance and merge evidence, promotes D-009 through D-014 to accepted, and sets Gate 1 to `ACCEPTED`.

Review specifically: strict versus trusted approval policy and residual compromised-MCP exposure; must-use guard installation; native-editor concurrency risk; external file parameters versus internal byte-only bridge input; trusted request-handle ownership/lost-result recovery; compact model evidence versus protected audit records; individual annotations; static content subset; and optional basic SEO without third-party vendor compatibility. These are known design limits, not hidden "TBD" mechanisms. A change to any requires corresponding contract/capability/threat updates.

No unmade architecture choice blocks review. Provider product, SDK/core support versions and actual callback values are selected and proven in their implementation/deployment gates within the specified contracts. Current-source research is not end-to-end integration evidence.

## Gate 2: WordPress bridge read foundation

**Status: IN PROGRESS**

The Gate 2 preparation plan was human-reviewed and accepted on 2026-10-05, then merged unchanged. The [implementation plan](GATE-2-IMPLEMENTATION-PLAN.md), [nine read route specifications](GATE-2-READ-ROUTES.md) and [support/test matrix](TEST-MATRIX.md) are accepted preparation. Gate 2 implementation is partial at C01 and is not accepted. Gate 2 remains `IN PROGRESS`, not `ACCEPTED`; C01 continuation is recorded below; C02 has not begun.

Preparation acceptance evidence:

- Accepted PR: [#2](https://github.com/jimlunsford/coagmentator/pull/2).
- Accepted preparation HEAD: `38bb913129703bed7fad6b511264e4542fbd49ac`.
- Accepted preparation tree: `01fa36b34c79b440462470c39e107f537d7c2560`.
- Pre-merge `main`: `31b9e9f0745ae48a3b61c060fbeb388431d837cd`, tree `873489e5343f610e14f775775147911a1a77817e`.
- Merge commit and immediate resulting `main` checkpoint: `71d98d9366390c0a9d2c3acf78d7db95cf9aed66`, tree `01fa36b34c79b440462470c39e107f537d7c2560`, identical to the accepted preparation tree.
- [Preparation closeout](work-notes/2026-10-05-gate-2-preparation-closeout.md) records verification and the final closeout checkpoint. The earlier [preparation handoff](work-notes/2026-10-05-gate-2-preparation.md) is historical evidence.
- Next: **Begin Gate 2 implementation at C01: Package and Test Skeleton.** Then **C02: Independent MU Guard must be established and verified before any service Application Password is issued.** Neither checkpoint began during closeout.

### C01 implementation checkpoint (partial, not accepted)

The [Actions continuation handoff](work-notes/2026-10-05-gate-2-c01-actions-continuation.md) records a successful hosted preflight and dependency construction, followed by failed initial full-matrix acceptance. Corrections are published, but subsequent branch HEADs have no observable workflow run/check suite. C01 remains blocked on execution of the corrected candidate and resolution of any remaining failures. No implementation PR is open. Main remains the accepted preparation checkpoint. No C02 or production work is authorized by this status entry.

Goals:

- create WordPress plugin skeleton
- establish mandatory service-identity must-use guard before any test credential is issued
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

Acceptance must include native-capability denial by author/status, raw-result privacy, identity/site mismatch, and REST/XML-RPC/batch/alternate-auth bypass tests. No write endpoint is enabled or implemented in this gate. A mutation-status read is deferred with its journal to Gate 3; do not invent a stub success. Select and document a maintained WordPress/PHP test matrix before implementation, and verify that current core mappings satisfy the Gate 1 profile.

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
- durable mutation journal, both server-side approval profiles, strict exact-intent human approval UI, and read-only mutation-status lookup

Acceptance must cover the Gate 1 verification obligations for all eleven writes: conflict/ownership/replay/crash behavior, no automatic retry of uncertain effects, live/private authority, both policy profiles, strict approval substitution/CSRF/self-approval denial, trusted-policy capability ceilings and policy-downgrade refusal, disabled Trash, safe limited-field revision restore, raw-content restrictions, safe media, metadata and basic SEO ownership, quotas, readback mismatch, and honest partial outcomes.

## Gate 4: MCP server foundation

**Status: NOT STARTED**

Goals:

- create TypeScript MCP service
- implement client authentication selected in Gate 1
- expose read tools
- expose write tools against shared contracts
- normalize WordPress errors
- add protected service audit correlation and allowlisted model projections
- implement durable server-owned handles/timestamps and lost-result duplicate admission
- implement bounded client-file retrieval and the internal byte adapter
- add automated tests

Pin a conforming established OAuth provider configuration and official SDK version; prove `2026-07-28` and `2025-11-25` behavior, discovery, PKCE, issuer/audience/subject/scope checks, callback modes, challenges, token/key rotation, redaction and emergency disable. Prove each individual tool annotation and the official top-level file schema, no arbitrary URL path, source-profile denial for direct callers, DNS rebinding/redirect/private-address defenses, before-buffer byte limits, internal-byte-only WordPress routing, identity/telemetry rejection in model schemas, and lost-response handle recovery. Recheck current primary sources; document changed requirements before implementation rather than weakening the profile.

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

Record the actual ChatGPT client protocol/auth/callback path, prove the actual client file-parameter handoff and controlled MCP download, verify the must-use guard against direct hostile callers, test both approval profiles, strict self-approval denial and uncertain-outcome reconciliation, and review installed hook/media execution behavior. Missing file-transfer/source-profile compatibility is a failed workflow criterion, not grounds to allow arbitrary URLs or WordPress fetching.

Production JimLunsford.com must not be the first end-to-end test target.

## Gate 6: JimLunsford.com production acceptance

**Status: NOT STARTED**

Goals:

- install production WordPress bridge
- create least-privilege service identity
- connect production to the MCP service
- start with read-only acceptance
- explicitly select the approval profile and authorize each write family; the reference plan is `trusted_single_operator`, with residual risk reviewed
- execute controlled draft/update/media tests
- verify rollback and revision paths
- document production configuration without secrets

Resolve actual content-format and SEO-owner compatibility before claiming those workflows accepted. Production authorization remains a separate decision; Gate 1 does not inspect or change JimLunsford.com.

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
