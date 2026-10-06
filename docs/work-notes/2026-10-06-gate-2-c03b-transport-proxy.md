# Work Note: Gate 2 C03B Transport and Proxy Foundation

Date: 2026-10-06 America/New_York
Roadmap gate: Gate 2, C03B
Status: COMPLETE (implementation and focused verification; human acceptance pending)

## Goal

Implement only C03B, publish one candidate and execute one focused CI run. Stop on an executed CI failure or hosted-runner blocker. No C03C, bridge data handlers, C04, merge or production work.

## Starting state

- Repository: `jimlunsford/coagmentator`.
- Exact accepted main HEAD: `e4c958310cf2f48e1a638cb79dceefdc48b399a7`.
- Exact accepted main tree: `4dfa5c389c42d199ce8a8da0cf22011949325768`.
- PR #5 independently reverified CLOSED and merged at `f26cb88ae0a8324384e27d1e19cd19e678ac6344`.
- Branch: `feature/gate-2-c03b-transport-proxy-foundation`, created from that exact main. No conflicting branch/PR existed.
- Mandatory repository doctrine, contracts, test plan, C03A schema and closeout were read before edits. Repository evidence confirms C03A ACCEPTED, C03B previously NOT STARTED, no runtime ReadController and no admission/audit/concurrency implementation. Repository evidence records no production identity/credential; production was not contacted to inventory it.

## Work completed

Added Transport_Policy and Transport_Evidence, a required closed `transport` object in Feature_Config, and an additional conjunct in Bridge_Identity. Direct TLS uses real server HTTPS plus canonical authority. Trusted proxy mode uses exact canonical immediate-peer addresses and one exact scheme/host pair. No CIDR, wildcard, request-selected trust, inferred origin, alternate authority or reusable credential field exists. Raw path/method/query and the fixed guard operation table remain exact. See [C03B-TRANSPORT](../C03B-TRANSPORT.md) for the complete model and coverage map.

C02 MU files and the synthetic C03A controller are unchanged. Host bootstrap explicitly normalizes proxy HTTPS before WordPress only after peer/scheme/authority validation, with no port-only fallback on failure. Normal-plugin identity still requires authentic core evidence, current user equality, live UUID, configured identity/rotation and guard readiness. No runtime bridge route or read implementation was created.

The disposable phase reuses the existing guarded service credential after all C01/C02/C03A controls. It adds a real same-pin Nginx proxy/backend hop, untrusted direct-backend tests, header overwrite/strip/malformed scenarios, global/per-user Application Password disablement, fixed `/journal/` WordPress configuration and an independent same-CA redirect destination. No request controls host scenario/configuration. No new credential is issued by C03B. The accepted C03A overlap credential and its revocation tests still run before this phase.

Four new pure unit tests cover closed parsing, exact address classification, direct/server evidence, ambiguous forwarding authority and exact subdirectory paths. Twenty-seven new focused HTTP scenarios cover A04's 17 obligations, the remaining stripped-Authorization A02 case (including real human cookie/nonce and injected-current-user variants), alternate authorization headers and Application Password availability. These are implemented cases, not passing-result claims until CI completes.

## Files changed

- Runtime: `class-transport-policy.php`, `class-transport-evidence.php`, Feature_Config, Bridge_Identity and fixed foundation includes.
- New fixtures: C03B bootstrap, observer, redirect sink and trusted CLI control; extended existing C03A policy/instrumentation with the required transport field and safe observation/availability controls. No accepted test assertion is removed or weakened.
- Environment: direct Nginx stops inventing forwarding values; C03B Compose override, backend config, edge scenario generator, separate-certificate/bootstrap preparation and bounded runner phase.
- Tests/workflow: TransportPolicyTest, TransportTest, its focused XML suite and `.github/workflows/c03b.yml`.
- Documentation: README, ARCHITECTURE, ROADMAP, C03A-CONFIGURATION, C03B-TRANSPORT and this handoff.

Accepted core/image/action pins, Composer manifests and lock files are unchanged. The new workflow copies the accepted focused topology: PHP 8.3/8.4/8.5 unit/lint, PHP 8.4 quality/audits, PHP 8.4 + MariaDB 10.11 integration only. No six-lane matrix or PR trigger is introduced. One new line-scoped PHPCS exception permits the runtime-only alternate-Authorization denial vector; existing exceptions are unchanged.

## Verification before publication

- Local PHP 8.3.6 syntax: 60 project PHP files pass (pre-publication development check, not the supported patch matrix).
- Exact pinned PHPCS/WPCS stack available from the prior isolated scratch setup: zero unsuppressed findings after formatting the new code.
- Preliminary PHPStan 2.2.14, level 8, against the exact accepted WordPress 7.1.2 runtime commit: zero errors. The required locked PHPStan 2.2.17 result remains CI-owned. Initial local invocation needed its Phar extension and a 512 MiB memory limit; no repository quality setting was changed.
- Shell syntax, Python compilation and whitespace checks pass.
- Local Docker/Composer and the exact PHPUnit runner are unavailable. A direct phar fetch returned HTTP 403; no local unit or HTTP pass is claimed. No elevated access or production environment is used to compensate.
- The following executed evidence supersedes the pre-publication pending state; no runtime/test changes followed the tested candidate.

## Candidate and execution evidence

- Tested runtime/test HEAD: `26e7e4a5c238668c1192b2db85ec793333f4c9e1`.
- Tested complete tree: `47e068bf21ff71499e78c4c2b061fea18e40e6e5`.
- Sole parent: exact starting main `e4c958310cf2f48e1a638cb79dceefdc48b399a7`.
- Exactly 27 changed files, 1,025 insertions and 23 deletions at the tested candidate.
- Authorized GitHub connector publication produced the identical reviewed local tree. Independent Git fetch verifies the remote candidate and clean checkout. Main is unchanged.
- Workflow [37451804620](https://github.com/jimlunsford/coagmentator/actions/runs/37451804620), push event, attempt 1, **SUCCESS**. Exactly one focused CI execution, no rerun and no six-lane expansion.

| Job | Job ID | Conclusion |
| --- | --- | --- |
| PHP 8.3 unit/lint | 112229881036 | SUCCESS |
| PHP 8.4 unit/quality | 112229881392 | SUCCESS |
| PHP 8.5 unit/lint | 112229881324 | SUCCESS |
| PHP 8.4 + MariaDB 10.11 integration/HTTP | 112229881305 | SUCCESS |

All jobs independently verified the exact tested HEAD/tree. Each supported PHP lane passed **17 unit tests / 309 assertions**, all **60 PHP syntax checks**, negative package-load controls and disclosure scanning. Locked PHPStan 2.2.17 at level 8 reported zero errors; WPCS reported zero unsuppressed findings. Both locked Composer audits completed successfully with empty advisories and abandoned-package arrays.

| Focused real WordPress/HTTP group | Tests | Assertions | Failures/errors/skips |
| --- | --- | --- | --- |
| C01/C02 retained regression | 122 | 12,809 | 0/0/0 |
| C03A retained identity/configuration regression | 18 | 637 | 0/0/0 |
| C03B real transport | 27 | 413 | 0/0/0 |
| Total | 167 | 13,859 | 0/0/0 |

C03B's four new pure tests contribute 73 assertions; the existing closed-configuration test adds one required-field assertion for the new transport object. Earlier assertions remain intact. The A04 coverage map in C03B-TRANSPORT was fully executed, including real certificate-chain/hostname rejection, HTTP and untrusted-peer denial, forwarding overwrite/malformed/conflicting authority, canonical host/path binding, the actual subdirectory WordPress request and root-alias denial, and the verified independent redirect destination with zero credential-bearing redirects or replay. Valid direct/proxy/subdirectory requests construct only the foundation identity and reach the synthetic failure callback; no bridge success or site data is produced.

A02 Authorization stripping passes for bare stripped requests, real human cookie/nonce fallback and injected-current-user fallback. All have zero core success events, no valid bridge identity and zero synthetic callbacks. Valid alternate Authorization headers also cannot substitute. Both globally and per-user disabled core Application Password support fail closed in the executed fixture, so that carryover is completed rather than deferred to C03C.

Downloaded evidence was independently checked against GitHub's archive digest and embedded candidate identity:

- Unit PHP 8.4 artifact `11407103315`: SHA-256 `02f4db9e755b9eb268aa45472df9484dab3a7b573b1fa74938de13c3f48f7aab`. Both audit JSON files contain `advisories: []`, `abandoned: []`, `filter: []`.
- Integration artifact `11406909441`: SHA-256 `dae7da785677d8a39f55858006990e29f8a04ee39d34995e4a2f869408561776`. All JUnit files were aggregated independently to the totals above. All 27 named C03B scenarios are present, with no failure/error/skip.
- Artifact retention remains the accepted 14 days, expiring 2026-10-20; durable counts, identities and controls are recorded here.

No PR is required for this checkpoint and none was created. No merge is authorized or performed.

The final handoff commit is the single documentation-only child of the tested HEAD, with message `docs: record verified C03B transport handoff [skip ci]`. It changes only README.md, docs/ARCHITECTURE.md, docs/ROADMAP.md and this note. Its HEAD/tree are distinguished from the tested identities above and recorded literally in the session report. Resolve it with `git log -1 --format=%H -- docs/work-notes/2026-10-06-gate-2-c03b-transport-proxy.md`, then `git rev-parse <commit>^{tree}`. All runtime, tests, configuration and workflow blobs remain identical to the passing candidate. No additional runtime CI is warranted for that documentation-only child.

## Credential and cleanup boundary

C02 preflight still precedes every credential issuance. Runtime-only service/human credentials and cookies stay in the existing protected fixture or client memory. Assertions contain fixed case IDs, booleans, counters and safe state hashes, never Authorization. The independent redirect certificate uses a protected runtime key and does not change C01 certificate verification. Only allowlisted sanitized XML/JSON evidence is uploaded after seeded-secret scanning.

The runner retains cleanup on success and failure: revoke all disposable Application Passwords, verify no credentials remain, delete fixture users and secret file, scan evidence and destroy the Compose containers/volumes/network including the new backend. Execution verified cleanup at 10:49:14 UTC: credential revocation succeeded and was checked, all disposable identities and the secret fixture were removed. The EXIT trap ran `down --volumes --remove-orphans`; logs confirm removal of edge, origin, PHP and database containers and the isolated network. Secret/evidence scanning passed before revocation, after revocation and at job completion. No secret or raw Authorization appeared in uploaded evidence.

## Decisions and limits

No new accepted D-series decision. C03 and Gate 2 remain IN PROGRESS; C03A ACCEPTED; C03B is READY FOR HUMAN REVIEW and remains unaccepted. C03C, C04 and Gate 3 remain NOT STARTED. Capability/admission/audit/locking/journal/serializer work is absent. No production connection, service identity, credential, deployment, tag or release.

## Risks / unresolved items

No failing check or C03B acceptance blocker remains in the required focused matrix. Existing nonblocking diagnostics remain: Docker default-build-argument warnings, Composer ownership/root-version fallback, WordPress test-group notices and artifact-action Node deprecations. None was suppressed or used to waive a requirement. Host/bootstrap configuration remains a disposable reviewed fixture, not a production installer. Full C03 multi-lane acceptance remains deferred to C03D.

## Exact next step

Human review of the exact final C03B branch, with runtime/test evidence bound to the tested HEAD/tree above. Do not self-accept or merge. Any correction requires its own bounded execution. Only after separate human acceptance and closeout may C03C admission/audit/concurrency begin. C04, real bridge routes and production remain outside this checkpoint.
