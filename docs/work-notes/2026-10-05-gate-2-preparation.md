# Work Note: Gate 2 Preparation

Date: 2026-10-05
Roadmap gate: Gate 2
Status: COMPLETE (preparation only; proposed plan awaits human acceptance)

## Goal

Prepare the WordPress Bridge Read Foundation implementation plan, supported environment matrix, package layout, authentication/guard/capability mechanics, nine read routes and automated acceptance strategy. No implementation or production work.

## Starting state

- Repository: `jimlunsford/coagmentator`.
- Starting branch: `main`.
- Starting HEAD: `31b9e9f0745ae48a3b61c060fbeb388431d837cd`.
- Starting complete tree: `873489e5343f610e14f775775147911a1a77817e`.
- Gate 1 accepted candidate: `3a0979faf3156a94173c73b89e8861e3d8816594`.
- Gate 1 merge: `4a99e61fc920983da86c53a0b2b47f5a9b5a7c2d`; PR #1 freshly verified merged/closed.
- Gate 0/Gate 1 ACCEPTED; D-009 through D-014 Accepted; Gate 2 and Gates 3-7 NOT STARTED.
- Fresh GitHub branch inventory contained only `main` and the accepted Gate 1 branch; open-PR collection was empty. No unreviewed Gate 2 implementation branch/PR.
- Fresh clone matched the exact HEAD/tree, clean worktree, and 22-file Markdown/license-only inventory. No code, dependencies, tests, workflows or WordPress package existed.
- Read AGENTS, mandatory permanent docs/contracts, Gate 1 closeout, work-chat protocol/template, contributor instructions and affected README.

## Work completed

- Created `feature/gate-2-wordpress-read-foundation` from the exact accepted main.
- Researched current official WordPress/PHP lifecycle, host requirements, native auth/capability/REST/query APIs, testing guidance, PHPUnit support, Composer, PHPStan, WPCS and container tooling. Source URLs, dates and immutable core refs are recorded in TEST-MATRIX and the plan.
- Inspected WordPress 7.1.2 `version.php`, `composer.json`, REST server and capability mapper through the GitHub connector in addition to official reference documentation. Distinguished upstream requirements from project support choices.
- Proposed minimum/current WordPress 7.1.2, PHP 8.3 minimum with mandatory 8.3/8.4/8.5, and all six PHP × MariaDB 10.11/MySQL 8.4 LTS combinations. No claim about current production WordPress or runtime compatibility.
- Selected isolated PHPUnit 12 unit/HTTP and PHPUnit 9.6 plus core-compatible Polyfills/core test-library integration runners; explicit life-support caveat, no copied audit exceptions. Proposed Docker Compose, PHPStan 2, native lint, WPCS and Composer locks, to be installed/pinned only during implementation.
- Defined a self-contained MU package, independent protected identity registry and normal feature package. Proposed WordPress server lifecycle fencing plus auth hooks and fixed callback checks to close native/nested/alternate dispatch paths.
- Specified native object checks, private default denial, parent-sensitive media/revisions, exact metadata keys, raw projections, full version hashes, authenticated encrypted cursors and operational admission/audit boundaries.
- Mapped exactly nine POST read routes to accepted capability rules and output/error types. Deferred get_mutation, every write, approval UI, journal, SEO renderer and MCP/OAuth to their accepted later gates.
- Defined 13 implementation checkpoints and test-family ownership for bootstrap, authentication, guard bypass, capabilities/privacy, pagination, raw content, terms, media, metadata, revisions, errors and limits. Traced all 30 threats to Gate 2 portions or retained later owners.
- Updated roadmap to Gate 2 IN PROGRESS for preparation only and kept the README consistent. Gates 0/1 stay ACCEPTED; Gates 3-7 stay NOT STARTED. Accepted Gate 1 contracts/decisions remain unchanged.

## Files changed

- `README.md`
- `docs/ROADMAP.md`
- `docs/GATE-2-IMPLEMENTATION-PLAN.md`
- `docs/GATE-2-READ-ROUTES.md`
- `docs/TEST-MATRIX.md`
- `docs/work-notes/2026-10-05-gate-2-preparation.md`

## Verification

- Manual consistency review: all nine read operations use accepted POST routes, BASE/native capability mapping, closed envelopes, typed projections, privacy and normalized errors.
- Reviewed guard independence, pre-credential provisioning, current-request user/credential evidence, authentication before detailed schema errors, nested dispatch lifecycle and alternate-auth denial. WordPress internal dispatch does not re-run HTTP authentication; the plan explicitly accounts for that.
- Reviewed WordPress's PHPUnit 9 requirement against the current PHPUnit lifecycle and selected separate dependency graphs rather than pretending modern PHPUnit works with the core library.
- Complete test-family and T01-T30 ownership review; bounded scans, opaque bound cursors, complete raw data/hash, no rendering/fetching, no unauthorized totals, exact logical keys and parent-first revisions are included.
- Structural checks passed: nine exact route/capability mappings, all 30 threat IDs assigned, 13 implementation checkpoints, six required environment lanes, 26 planned test families, 36 local links and 12 consistently shaped Markdown tables. Roadmap statuses match the intended gate boundary; accepted contracts/decisions and local main are unchanged. Final remote publication checks are recorded below after the candidate commit.
- Runtime/CI tests are not applicable to this documentation-only preparation and were not run.
- No production inspection/change, credentials or users created, dependencies installed, fixtures/code/workflows added, tests dispatched, deployment performed, tags/releases created or merge performed. Main remains the accepted starting checkpoint.

## Decisions

No new accepted D-series decision and no accepted Gate 1 decision modified. This candidate proposes the implementation choices listed above for human acceptance. Documentation-only preparation is complete; Gate 2 implementation has not begun and Gate 2 is not accepted.

## Risks / unresolved items

No known accepted-contract contradiction blocks preparation review. Runtime guard efficacy, exact dependency patch/image/source pins, PHPUnit 9.6 on the selected PHP patches and installed plugin/server compatibility still require the planned implementation evidence. The single-host local-lock requirement and registry-corruption emergency denial are explicit review choices. PHPUnit 9 life support is a scoped development dependency risk, not a runtime dependency. Target-site inventory/SEO compatibility and production authorization remain later work.

## Exact next step

Human-review the exact preparation PR HEAD/tree. Accept the plan or request corrections. Only after acceptance and explicit implementation authorization, begin C01 (package/test skeleton and exact environment/tool pins), followed by independent guard C02 before any service Application Password is issued. Do not merge or implement as part of this preparation execution.

## References

- Preparation branch: `feature/gate-2-wordpress-read-foundation`.
- PR: [#2, Gate 2 preparation](https://github.com/jimlunsford/coagmentator/pull/2), open, ready for review and unmerged.
- Candidate commit/tree: recorded in the publication checkpoint below and final PR description; the commit containing a note cannot embed its own resulting hash.
- Workflow run: none.
- Release/tag/deployment: none.

## Publication checkpoint

- Published preparation design HEAD: `f42785a76508b197354f22a04b62608e5a02e501`.
- Published preparation complete tree: `7f2833160b648abc6d512c202ee1b5a05b13b801`, exactly equal to the locally reviewed tree.
- Its sole parent is accepted starting main `31b9e9f0745ae48a3b61c060fbeb388431d837cd`.
- PR #2 freshly verified OPEN, ready for review, mergeable, unmerged, base main, correct preparation branch and six changed Markdown files. At this checkpoint it contained one design commit.
- GitHub API publication used the reviewed complete tree. A fresh local fetch and complete-tree diff confirmed equality; the working branch was aligned with the published commit without changing file contents.
- Whitespace and added-content secret/private-reference scans passed. Full inventory remains Markdown and LICENSE only, with no code/dependency/workflow/fixture additions. No accepted Gate 1 contract changed.
- Remote main was freshly reverified at its unchanged accepted SHA before publication. Final verification must recheck it and the final PR tip after this note-only follow-up.
- This handoff-only follow-up changes no preparation design. The final PR description and session report pin its final HEAD/tree; retrieve the exact reviewed checkpoint there to avoid a self-referential hash in this note.
- Final state remains Gate 2 IN PROGRESS, preparation ready for human review. No merge or implementation authorized by this publication.
