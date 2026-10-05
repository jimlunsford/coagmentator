# Work Note: Gate 1 Contracts and Threat Model

Date: 2026-10-05
Roadmap gate: Gate 1
Status: COMPLETE (initial design and human-review correction preparation; revised candidate acceptance pending)

The original preparation below is historical. The human-review correction section records the current revised design; Gate 1 remains `IN PROGRESS`.

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
- Published design commit `e3949dd0f4e6613fca12dfbcb3a20612ad76afec`; its tree `1ba4c32b2a4c81cb729bf97a711a303e819b18ea` exactly matched the locally reviewed complete tree.
- Opened [PR #1](https://github.com/jimlunsford/coagmentator/pull/1) against the unchanged baseline `main`; its initial diff was exactly 13 Markdown files and one design commit. This follow-up only records publication identifiers in this note. The PR description records the final handoff HEAD/tree, avoiding an impossible self-referential commit hash inside its own file.

## Decisions

- D-001 through D-008 remain accepted; none was silently superseded.
- D-009 through D-014 contain the proposed Gate 1 design decisions and remain explicitly proposed pending human acceptance.
- Publication/readiness does not accept the gate or authorize Gate 2.

## Risks / unresolved items

- No unresolved security-critical architecture question blocks design review.
- Human acceptance of the revised candidate is outstanding, including both approval profiles, trusted-profile compromised-MCP exposure, file-source restrictions and documented MVP limits.
- OAuth provider product, maintained WordPress/PHP/SDK version pins and actual platform callback values require evidence at their named implementation/deployment gates, within the fixed contract.
- Actual ChatGPT interoperability, file-parameter handoff and reviewed download-source configuration have not been tested. They are Gate 5 requirements.
- Existing JimLunsford.com content/SEO compatibility is uninspected and cannot be claimed. Vendor SEO support is outside the MVP basic renderer.
- WordPress-native concurrent edits can race bridge writes; external hook effects cannot be made exactly-once; compromised WordPress can forge receipts. These are disclosed design limits requiring human review.

## Human-review correction pass, 2026-10-05

### Goal and verified starting state

Apply the three requested human-review corrections to the existing Gate 1 branch and PR. Publication is authorized; merging and Gate 2 are explicitly prohibited.

- Reviewed starting HEAD: `5a1e1451798fff58e3953e453f8a628f15db6fdf`.
- Reviewed starting tree: `277414d04bcb4c7ef066875b8374b15860ff8886`.
- Branch: `docs/gate-1-contracts-threat-model`; PR #1 open, ready for review, mergeable and unmerged at start.
- Remote `main` independently verified at `810727bbbd00e672ecb81cd22c285650ee45f357`; no changes authorized there.
- Used a clean isolated local copy of the reviewed Git history. Read the mandatory repository context and current affected files before editing. No stale file version was substituted.

### Corrections completed

1. External media now uses a top-level `ClientFile` with `openai/fileParams`, the four declared properties and only `download_url`/`file_id` required. MCP owns controlled retrieval, pre-buffer/streaming limits, HTTPS/public-address pinning, no redirects, source-profile enforcement, decoder validation and input digest. Direct callers cannot turn the field into an arbitrary downloader. WordPress accepts only the authenticated internal byte envelope and independently re-encodes/strips metadata. No source-host list or live integration is invented.
2. MCP injects site/actor identity and owns new durable mutation handles/timestamps. Added explicit lost-client-response fingerprint admission, immutable replay payloads/timestamps, expiry/tombstone handling and fail-closed recovery of missing tracking state. Preserved bridge reservation ownership, honest partial/unknown results and no blind retries. Model evidence retains the request handle, target, outcome, versions/status, changed fields and verification; internal receipt/identity/trace details remain in protected audit records.
3. Added `strict` and `trusted_single_operator` policy profiles, with write families disabled initially and the complete authorization/capability/version/deduplication/verification stack in both. Strict approvals remain independent and cannot be service-approved. Trusted reference use relies on standing server authority and client confirmation UX, with all-enabled-write exposure under MCP compromise explicitly documented. Each of the 21 tools now has individual annotation values and a behavior-based justification.

Updated all 13 files listed in this note, including README and ERRORS in addition to the requested permanent documents. Retained the 21-tool surface, OAuth provider direction, current/compatibility MCP profiles, mandatory guard, WordPress authority, content/SEO limitations and native-editor concurrency disclosure. Added T29/T30 within the existing threat-table structure, bringing it to 30 scenarios.

### Consistency and source verification

The correction review covers the complete Gate 1 checklist, not runtime implementation acceptance:

- Official OpenAI reference and tool-planning pages were searched and opened on 2026-10-05, including the actual file-input and annotation sections. Confirmed top-level metadata, all four declared file properties and exact required set. Sources are linked in MCP-TOOLS, CONTRACTS and AUTHENTICATION.
- Reviewed all 21 tool input/output/resource/error rows against the 21 OAuth/native/custom mappings and all 21 individual annotation rows. Bounded reads are closed-world; additive draft/media/term creation are not blanket destructive; public effects are open-world. Idempotence reflects actual repeat behavior and never permits blind retry.
- Traced arbitrary/forged/expired client URLs, redirect and private-address/DNS rebinding cases, pre-buffer limits, decoder/MIME/dimension checks, digest ownership and internal byte-only WordPress validation. These are specified controls with Gate 4/5 tests assigned, not attacks tested against nonexistent code.
- Reviewed every model schema/projection for fixed site-ID requirements, caller timestamps, fresh caller-chosen IDs and unnecessary tracing fields. Checked successful, failed, approval and mutation-lookup projections against protected audit retention.
- Walked client-response loss, bridge-response loss, concurrent repeats, approval resumption, changed payloads, stale versions, original timestamps, expired records, lost tracking state and uncertain reservations. No automatic mutation POST retry, fresh-ID workaround or lease stealing is permitted.
- Checked both approval profiles against every authorization layer, strict non-service approver/CSRF/exact-intent enforcement, policy-downgrade denial and trusted-profile residual risk. Confirmed all eleven mutations retain readback and honest partial/unknown evidence.
- Rechecked the remaining Gate 1 boundaries: raw-source reads, static write policy, native capability mapping/guard, metadata/SEO ownership, publication/Trash/restore restrictions, rate/storage ceilings, privacy/redaction, version/concurrency limits and unchanged protocol compatibility choices.
- Structural verification passed: 21 tools, 21 capability mappings, 21 annotation rows, 30 threats, 57 local links, 18 aligned Markdown tables, 3 parsed JSON examples, eight accepted/six proposed decisions, and all gate states preserved. `git diff --check` and added-content secret-pattern checks passed. Exactly 13 Markdown files changed; zero implementation, dependency, workflow or test files were added. Exact publication identifiers are recorded with the published correction checkpoint below. No runtime tests/CI are applicable: the repository still contains documentation/license only.

### Correction publication checkpoint

- Correction design commit: `53eb7513cba2416f33bcd0aba790b9d220b2d36d`.
- Correction complete design tree: `1e615690191170d7bf3d1fa08d8a4628b008d4b3`, exactly matching the locally reviewed tree.
- Parent reviewed HEAD: `5a1e1451798fff58e3953e453f8a628f15db6fdf`.
- Destination: existing `docs/gate-1-contracts-threat-model` branch and PR #1, based on unchanged `main` at `810727bbbd00e672ecb81cd22c285650ee45f357`.
- The following single publication-handoff commit changes only this note to record these immutable identifiers. Its final HEAD/tree are pinned in PR #1's description and the human-review handoff, avoiding a self-referential hash inside its own tree. Verify those identifiers before acceptance.
- The complete revised candidate contains four commits over main (two original Gate 1 commits and these two correction commits), still exactly 13 Markdown files. No merge, tag, release or deployment is part of this correction.

### Decisions, limitations and next action

D-001 through D-008 remain accepted. D-009 through D-014 remain proposed, with the requested corrections recorded in their relevant entries; this is not Gate 1 acceptance. Actual file-source origins and platform/provider behavior require later non-production evidence. No production deployment, credentials, tags, releases or Gate 2 scaffolding were added.

Next action: human-review the exact revised PR #1 HEAD/tree. Keep Gate 1 `IN PROGRESS` and Gate 2 `NOT STARTED`. Do not merge this PR during this execution.

## Exact next step

Human-review the exact Gate 1 branch/PR candidate against the roadmap checklist and accept it or request corrections. After explicit acceptance, a separate closeout should reverify the accepted commit, record the acceptance, authorize/perform the merge and update proposed decision statuses. Do not implement Gate 2 during this review/closeout execution.

## Original publication references

- Base branch/commit: `main` at `810727bbbd00e672ecb81cd22c285650ee45f357`.
- Candidate branch: `docs/gate-1-contracts-threat-model`.
- Published design commit: `e3949dd0f4e6613fca12dfbcb3a20612ad76afec`.
- Published design tree: `1ba4c32b2a4c81cb729bf97a711a303e819b18ea`.
- PR: [#1, Gate 1: define contracts, authentication, and threat model](https://github.com/jimlunsford/coagmentator/pull/1).
- Final handoff revision: the commit adding this publication record; exact branch HEAD/tree are pinned in the PR description and must be reverified before acceptance/merge.
- Workflow run: none, documentation-only repository with no workflows.
- Release/tag/deployment: none.
