# Work Note: Gate 2 C03B Transport and Proxy Foundation

Date: 2026-10-06 America/New_York
Roadmap gate: Gate 2, C03B
Status: PARTIAL

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
- Focused CI, both locked audits, supported PHP-line results and actual disposable cleanup: PENDING.

## Candidate and execution evidence

The implementation candidate is the commit introducing this note, with sole parent the starting main above; its complete tree is that commit's tree. Exact literal tested HEAD/tree and workflow/job results will be added in a documentation-only handoff after the one execution, without altering runtime/test files. This avoids a self-referential commit hash.

No PR is required for this branch checkpoint; no merge is authorized. Publication of this exact implementation/test/documentation candidate is authorized by the task and the repository's open-development decision.

## Credential and cleanup boundary

C02 preflight still precedes every credential issuance. Runtime-only service/human credentials and cookies stay in the existing protected fixture or client memory. Assertions contain fixed case IDs, booleans, counters and safe state hashes, never Authorization. The independent redirect certificate uses a protected runtime key and does not change C01 certificate verification. Only allowlisted sanitized XML/JSON evidence is uploaded after seeded-secret scanning.

The runner retains cleanup on success and failure: revoke all disposable Application Passwords, verify no credentials remain, delete fixture users and secret file, scan evidence and destroy the Compose containers/volumes/network including the new backend. Actual execution evidence is pending, not presumed.

## Decisions and limits

No new accepted D-series decision. C03 and Gate 2 remain IN PROGRESS; C03A ACCEPTED; C03B is an unaccepted candidate. C03C, C04 and Gate 3 remain NOT STARTED. Capability/admission/audit/locking/journal/serializer work is absent. No production connection, service identity, credential, deployment, tag or release.

## Risks / unresolved items

Focused CI has not executed yet. Local preliminary checks do not establish real-edge acceptance. Host/bootstrap configuration is a disposable reviewed fixture, not a production installer. Full C03 multi-lane acceptance remains deferred to C03D.

## Exact next step

Publish this candidate and observe exactly one focused CI execution. On actual failure, record the concrete failing step and stop without a fix/push/rerun cycle. On success, record exact tested and final documentation identities, then hand C03B to human review without accepting or merging it. C03C must remain unstarted.
