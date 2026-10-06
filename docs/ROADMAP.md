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

The Gate 2 preparation plan was human-reviewed and accepted on 2026-10-05, then merged unchanged. The [implementation plan](GATE-2-IMPLEMENTATION-PLAN.md), [nine read route specifications](GATE-2-READ-ROUTES.md) and [support/test matrix](TEST-MATRIX.md) are accepted preparation. C01, C02, C03A and C03B are accepted; Gate 2 as a whole is not accepted. Gate 2 remains `IN PROGRESS`; C03 remains `IN PROGRESS`, with C03C the next unstarted checkpoint.

Preparation acceptance evidence:

- Accepted PR: [#2](https://github.com/jimlunsford/coagmentator/pull/2).
- Accepted preparation HEAD: `38bb913129703bed7fad6b511264e4542fbd49ac`.
- Accepted preparation tree: `01fa36b34c79b440462470c39e107f537d7c2560`.
- Pre-merge `main`: `31b9e9f0745ae48a3b61c060fbeb388431d837cd`, tree `873489e5343f610e14f775775147911a1a77817e`.
- Merge commit and immediate resulting `main` checkpoint: `71d98d9366390c0a9d2c3acf78d7db95cf9aed66`, tree `01fa36b34c79b440462470c39e107f537d7c2560`, identical to the accepted preparation tree.
- [Preparation closeout](work-notes/2026-10-05-gate-2-preparation-closeout.md) records verification and the final closeout checkpoint. The earlier [preparation handoff](work-notes/2026-10-05-gate-2-preparation.md) is historical evidence.
- At preparation closeout, the next checkpoint was C01. Its acceptance is recorded below. **C02: Independent MU Guard must be established and verified before any service Application Password is issued.**

### C01: Package and Test Skeleton, ACCEPTED

Human-reviewed and accepted on 2026-10-05; merged unchanged using a merge commit.

- Accepted PR: [#3](https://github.com/jimlunsford/coagmentator/pull/3), merged.
- Accepted HEAD: `3995b88902a6fa6b26bd2ab777d05c6a403118d8`.
- Accepted complete tree: `2e04fe9a9a03b7f2c61ceb2ff0f73e9721d4adad`.
- Pre-merge main: `ab4ccb3362ca13b006ff3f2887d4743af06ba035`, tree `9e83480e09bf6f81882b897827866a46e256d2a1`.
- Final workflow [37333044824](https://github.com/jimlunsford/coagmentator/actions/runs/37333044824): pull_request/synchronize, attempt 1, SUCCESS; all nine exact-candidate jobs SUCCESS. No rerun or workflow approval required.
- Merge commit and immediate post-merge main: `5941f9e62865c160b33b40d0dfee02131a8f9265`, tree `2e04fe9a9a03b7f2c61ceb2ff0f73e9721d4adad`, identical to the accepted tree.
- [C01 closeout](work-notes/2026-10-05-gate-2-c01-closeout.md) records jobs, pins, checks and the deterministic final documentation checkpoint.
- Both [environment blocker](work-notes/2026-10-05-gate-2-c01-environment-blocker.md) and [Actions continuation](work-notes/2026-10-05-gate-2-c01-actions-continuation.md) histories and the [CI recovery evidence JSON](work-notes/2026-10-05-gate-2-c01-ci-recovery-evidence.json) are preserved unchanged.
- **C02: Independent MU Guard, NOT STARTED.** Exact next checkpoint, in a separately authorized execution. No guard, bridge REST route, service user or Application Password exists in the C01 package/test environment. No production access or deployment occurred.

### C02: Independent MU Guard, ACCEPTED

Human-reviewed and accepted on 2026-10-05; merged unchanged using a normal merge commit.

- Accepted PR: [#4](https://github.com/jimlunsford/coagmentator/pull/4), merged.
- Accepted final documentation HEAD: `21402421f03c9ad2a08d5fe8b876b854ba86896a`; tree `ed714800c6d9306d97f04c90610d105046e31200`.
- Accepted runtime/test HEAD: `d039aed3c07ac357c6798d989ccbe3df76cec5d2`; tree `728d1c088587d53b9221bfd17b856cb941e0d44c`. The two later candidate commits change only the four authorized Markdown files.
- Final workflow [37364803455](https://github.com/jimlunsford/coagmentator/actions/runs/37364803455), attempt 7: SUCCESS. All three unit/quality jobs and six PHP/database lanes pass at the accepted runtime identity.
- Pre-merge main: `d25e08732577e4ff3e55c6d938acd26dd7abdeb3`; tree `dee181535214e8d20c7faafb3f00b7453075866c`.
- Merge commit: `88bbb46ae2593a7722d8cc8a06503f20a5041738`; immediate merge tree `ed714800c6d9306d97f04c90610d105046e31200`, exactly the accepted final PR tree. Ordered parents are the expected pre-merge main and accepted PR head.
- Custom-server compatibility correction accepted: preserve public/human traffic, refuse Coagmentator preflight and issuance with zero credentials, deny protected service/native and bridge requests, restore guarded behavior after conflict removal.
- [C02 closeout](work-notes/2026-10-05-gate-2-c02-closeout.md) records complete executed-job evidence and the final checkpoint. The earlier [C02 handoff](work-notes/2026-10-05-gate-2-c02-independent-mu-guard.md) remains unchanged, including failures, cancellations and recovery history.

### C03: Authentication/config/operations foundation, IN PROGRESS

C03 remains partial and is not ready for overall human acceptance. Full C03 verification is deferred to C03D. No real bridge read handler or production provisioning/access exists. Gate 3 remains NOT STARTED.

#### C03A: Authentication Evidence and Configuration Foundation, ACCEPTED

Human-reviewed and accepted on 2026-10-06; merged unchanged using a normal merge commit.

- Accepted PR: [#5](https://github.com/jimlunsford/coagmentator/pull/5), merged.
- Accepted branch: `feature/gate-2-c03-auth-config-operations-foundation`.
- Accepted final HEAD: `4a6c02725cd8eb14246ae4f747e0d00d26763740`; tree `06aafca640b6ea50a78fcd61e92afbbaa0ee04af`.
- Tested implementation HEAD: `1d6439f1a57bab979c85d717eaee11e58c137412`; tree `bf1463d1dfa06b3afa69c8bac40c67950ed5fba9`. The final candidate commit changes only the four specified Markdown files.
- Focused workflow [37399919186](https://github.com/jimlunsford/coagmentator/actions/runs/37399919186), attempt 1: SUCCESS. Jobs `112064578304`, `112064578468`, `112064578377` and `112064578170` all SUCCESS.
- Pre-merge main: `11db731b9d6fbf016b0bf6dfe7d565a1d10732ed`; tree `6fd663cd18d2c1e3a03a069198d2da2c2bfea8a8`.
- Merge commit: `f26cb88ae0a8324384e27d1e19cd19e678ac6344`; ordered parents are that pre-merge main and accepted final HEAD. Immediate merge tree: `06aafca640b6ea50a78fcd61e92afbbaa0ee04af`, exactly the accepted candidate.
- Authentication evidence and closed configuration are accepted within the [C03A schema/boundary](C03A-CONFIGURATION.md). The accepted quality correction preserves behavior and expectations. Test-only PHPCS exceptions remain limited to one serialization attempt, two Base64 header constructions and the duplicate-class sniff only for `tests/fixtures/c03a-controller.php`.
- [C03A closeout](work-notes/2026-10-06-gate-2-c03a-closeout.md) records verification and the final checkpoint. The original [work note](work-notes/2026-10-05-gate-2-c03a-authentication-configuration.md), including failed run `37398487754` and its correction history, remains unchanged.
- C02 MU guard and fixture remain unchanged. No bridge handler, production service identity/Application Password, production access or deployment was created by this work.

#### C03B: Transport and Proxy Foundation, ACCEPTED

Human-reviewed and accepted on 2026-10-06; merged unchanged using a normal merge commit.

- Accepted PR: [#6](https://github.com/jimlunsford/coagmentator/pull/6), merged.
- Accepted branch: `feature/gate-2-c03b-transport-proxy-foundation`.
- Accepted final HEAD: `a3607596b8d587b1bf3a93f4a7e6dc6ccb211bd0`; tree `147d7788633341fa5654655997c6c05ca7823fc6`.
- Tested implementation HEAD: `26e7e4a5c238668c1192b2db85ec793333f4c9e1`; tree `47e068bf21ff71499e78c4c2b061fea18e40e6e5`. Exactly two commits ahead and zero behind pre-merge main, 27 changed files; the final child changes only the four specified Markdown files.
- Focused workflow [37451804620](https://github.com/jimlunsford/coagmentator/actions/runs/37451804620), attempt 1: SUCCESS. Jobs `112229881036`, `112229881392`, `112229881324` and `112229881305` all SUCCESS.
- Pre-merge main: `e4c958310cf2f48e1a638cb79dceefdc48b399a7`; tree `4dfa5c389c42d199ce8a8da0cf22011949325768`.
- Merge commit: `61943fda724e6aa926809117078d61a3587ecdaa`; ordered parents are that pre-merge main and accepted final HEAD. Immediate merge tree: `147d7788633341fa5654655997c6c05ca7823fc6`, exactly the accepted candidate.
- Accepted [transport/proxy foundation](C03B-TRANSPORT.md): direct TLS, exact trusted immediate peer and forwarding authority, explicit early host bootstrap, canonical Authorization preservation and stripped/alternate-header denial, exact subdirectory binding, A04 transport evidence and A02 stripped-Authorization carryover. Global/per-user Application Password disablement denies admission.
- [C03B closeout](work-notes/2026-10-06-gate-2-c03b-closeout.md) records evidence and the final checkpoint. Original [implementation handoff](work-notes/2026-10-06-gate-2-c03b-transport-proxy.md) remains unchanged as historical evidence.
- C02 guard and C03A identity/configuration semantics remain intact. No real bridge read handler, production service identity/Application Password, production access or deployment was created by this work.

**C03C: NOT STARTED.** Admission/audit/concurrency is the exact next checkpoint, only in a separately authorized execution.

**C04: NOT STARTED.** No capability-policy or bridge-read implementation begins in this closeout.

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
