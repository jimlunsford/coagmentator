# Work Note: Gate 1 Contracts and Threat Model

Date: 2026-10-05
Roadmap gate: Gate 1
Status: COMPLETE (design preparation only; human acceptance pending)

## Goal

Make Gate 1 reviewable with exact MVP tools, shared contracts, WordPress authorization, current authentication requirements, mutation evidence and a concrete threat model. Do not implement Gate 2, deploy, connect MCP or touch production.

## Starting state

- Repository: `jimlunsford/coagmentator`, public.
- Starting branch: `main`.
- Starting commit: `810727bbbd00e672ecb81cd22c285650ee45f357`.
- Starting tree: `b2dc5c63040fd14a7d0c4021387080697f46b421`.
- No open pull requests at the initial remote check.
- Gate 0 `ACCEPTED`; Gate 1 and Gate 2 `NOT STARTED`.
- Complete tracked tree contained documentation/license only; no conflicting implementation.
- Read `AGENTS.md`, project, architecture, security, roadmap, decisions, current work notes and work-chat protocol before editing. Repository content was authoritative.

## Work completed

- Created dedicated branch `docs/gate-1-contracts-threat-model` from the verified baseline.
- Defined 21 tools (10 reads, 11 mutations) and every tool's inputs, output, resources, validation, failure and native/custom/OAuth authorization.
- Defined versioned envelopes, bounded resource shapes, cursor semantics, content lifecycle, fixed metadata, raster uploads and limited revision restoration.
- Defined exact-intent WordPress approvals, durable request ownership/deduplication, state hashes, readback receipts and honest partial/unknown outcomes.
- Chose dedicated Application Password authentication plus a mandatory must-use service-route guard, preserving native WordPress capability authority.
- Chose OAuth authorization code with PKCE through an established provider, current MCP `2026-07-28` Streamable HTTP and explicit `2025-11-25` compatibility.
- Documented 28 threat scenarios, mitigations, residual risks, trust boundaries and later test obligations.
- Updated permanent project/architecture/security/decision/roadmap truth and README navigation/status.
- Left Gate 1 `IN PROGRESS`, design ready for human review. Gate 0 remains accepted; Gate 2 remains not started.

## Files changed

- `README.md`
- `docs/PROJECT.md`
- `docs/ARCHITECTURE.md`
- `docs/SECURITY.md`
- `docs/ROADMAP.md`
- `docs/DECISIONS.md`
- `docs/MCP-TOOLS.md`
- `docs/CONTRACTS.md`
- `docs/ERRORS.md`
- `docs/CAPABILITIES.md`
- `docs/AUTHENTICATION.md`
- `docs/THREAT-MODEL.md`
- `docs/work-notes/2026-10-05-gate-1-contracts-threat-model.md`

## Research performed

Primary sources only, checked 2026-10-05 and linked in the permanent documents:

- Current OpenAI remote MCP authentication/server guidance: discovery, PKCE, client registration/callback modes, issuer validation and division of platform/server responsibility.
- Current MCP specification: `/latest/` resolved to `2026-07-28`; authorization, discovery, registration, stateless Streamable HTTP, versioning, tools and security guidance; `2025-11-25` compatibility transport.
- WordPress core documentation/source references: REST/Application Password authentication and administration, `current_user_can`/`map_meta_cap`, term assignment/create/edit, attachment creation, revision reads/restore and disabled-Trash permanent-deletion behavior.
- RFC 8785 canonical JSON for stable resource/request hashes.
- No WPVibe implementation was copied or needed; no third-party implementation code was added.

## Verification

- Baseline local commit/tree matched the independently fetched GitHub main branch; working tree was initially clean.
- Reviewed each tool against capability mapping and each mutation against precondition, approval, deduplication, error and receipt requirements.
- Cross-checked modern versus legacy MCP envelopes, WordPress versus client OAuth failures, direct-native-API bypass prevention, disabled Trash, safe revision restore and independent approval ownership.
- Reviewed explicit limits: native concurrent-writer races, external WordPress hook effects, compromised-component receipt trust, static content subset, client media transfer and optional basic SEO ownership.
- Documentation checks passed: 21 unique tool rows matched 21 capability rows; 10 reads and 11 writes; 28 unique threats; eight accepted and six proposed decisions; all eight gate states preserved; 54 local links resolved; 16 Markdown tables aligned; two JSON examples parsed.
- `git diff --check` passed after removing new trailing whitespace; Markdown union pipes were escaped for correct table rendering. Exactly 13 changed files are Markdown under the documented scope. Basic secret-pattern scan and manual publication-content review found no credentials or private source material.
- The temporary documentation-check script is outside the repository and is not implementation/test scaffolding in this candidate. These structural checks supplement manual review; no runtime or live-integration success is claimed.
- No implementation tests or CI existed at the baseline. Gate 1 adds no application code, dependencies, workflows, test scaffolds, production configuration or credentials.

## Decisions

- D-001 through D-008 remain accepted; none was silently superseded.
- D-009 through D-014 contain the proposed Gate 1 design decisions and remain explicitly proposed pending human acceptance.
- Publication/readiness does not accept the gate or authorize Gate 2.

## Risks / unresolved items

- No unresolved security-critical architecture question blocks design review.
- Human acceptance is outstanding, including the user-visible approval flow and documented MVP limits.
- OAuth provider product, maintained WordPress/PHP/SDK version pins and actual platform callback values require evidence at their named implementation/deployment gates, within the fixed contract.
- Actual ChatGPT interoperability and image-byte handoff have not been tested. They are Gate 5 requirements.
- Existing JimLunsford.com content/SEO compatibility is uninspected and cannot be claimed. Vendor SEO support is outside the MVP basic renderer.
- WordPress-native concurrent edits can race bridge writes; external hook effects cannot be made exactly-once; compromised WordPress can forge receipts. These are disclosed design limits requiring human review.

## Exact next step

Human-review the exact Gate 1 branch/PR candidate against the roadmap checklist and accept it or request corrections. After explicit acceptance, a separate closeout should reverify the accepted commit, record the acceptance, authorize/perform the merge and update proposed decision statuses. Do not implement Gate 2 during this review/closeout execution.

## References

- Base branch/commit: `main` at `810727bbbd00e672ecb81cd22c285650ee45f357`.
- Candidate branch: `docs/gate-1-contracts-threat-model`.
- Candidate commit and PR: to be recorded in the publication handoff update; this initial note is part of the design candidate itself.
- Workflow run: none, documentation-only repository with no workflows.
- Release/tag/deployment: none.
