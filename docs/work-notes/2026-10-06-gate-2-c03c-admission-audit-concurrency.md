# Work Note: Gate 2 C03C Admission, Concurrency and Audit Foundation

Date: 2026-10-06 America/New_York
Roadmap gate: Gate 2, C03C
Status: PARTIAL

## Goal and starting state

Implement only the authorized C03C read operational foundation, publish one candidate and execute one focused CI checkpoint. No self-acceptance, merge, C03D, C04, real bridge route or production access.

- Starting main HEAD: `1053487d627622145b19232f1756643bada0b74c`.
- Starting complete tree: `dbe5eac1d9787e6fca71c78606639f8358606a65`.
- C03B merged PR #6: `61943fda724e6aa926809117078d61a3587ecdaa`, verified live.
- Branch: `feature/gate-2-c03c-admission-audit-concurrency`, created from that exact main.
- Fresh checkout matched the accepted identities and was clean. Mandatory repository context read. No conflicting C03C branch/PR, runtime ReadController, capability policy or Gate 3 implementation found.
- Repository evidence records no production identity/credential provisioning. Production was deliberately not contacted to inventory it.

## Work completed

Added `Operational_Failure`, `Server_Clock`, `Read_Deadline`, `Local_Store`, `Rate_Admission`, `Read_Slot`, `Read_Audit`, `Preauth_Admission` and `Read_Operation`. [C03C-OPERATIONS](../C03C-OPERATIONS.md) records the durable component design, preauth host integration, trusted keys, file schemas/caps, permission/readiness prerequisites, local filesystem requirements, nonblocking slots, deadline consumption, audit schema/integrity and 90-day maintenance/retention boundary.

C02 MU runtime and its fixture are unchanged. Existing C03A/C03B identity/transport semantics remain intact; the peer parser is reused through a new accessor. Closed configuration/schema version is unchanged, with getters for validated operational dependencies. No accepted pin or lock file changed. No new credential issuance path exists.

The test-only controller is separately installed in the disposable copy, never runtime source, and always returns failure. A single narrow duplicate-class PHPCS exception covers only that fixture, following the existing accepted fixture convention. Local descriptor IO/error-handling suppressions explain why mandatory flock semantics cannot use WordPress filesystem/cache alternatives; no existing exception or quality gate is weakened.

## Tests and evidence plan

- L01 unit cases: authenticated 60/61, preauth 120/121 with forged forwarding claims, deterministic rollover/backwards clock, fresh-process persistence of both counters, capped buckets with active retention and expired retirement, corrupt/full/unwritable state, missing/non-directory/unsafe/symlink/incompatible mounts, immediate lock contention, exact 15-second deadline and invalid clocks.
- Independent workers: four simultaneously hold real slots, fifth is denied promptly, release permits a new worker, SIGKILL frees the OS lock, and all four slots can be acquired after teardown. Workers use explicit timeouts and are reaped in finally.
- Audit/E02: exact safe schema, seeded secret/SQL/path/stack/email/body markers, capacity preflight with active evidence preserved, malformed/integrity/truncation failure, permissions, exact 90-day cutoff and idle maintenance.
- HTTP: real guard/C03A/C03B admission, early invalid-auth 120/121, authenticated 60/61, four genuinely simultaneous FPM callbacks/fifth denial, repeated ordinary/throwing callbacks and all-slot cleanup, wrong identity, bounded/full/unwritable/corrupt storage and audit pre-target failure. Independent protected-state snapshots remain mandatory.
- C01/C02/C03A/C03B regression assertions remain unchanged, followed by 17 C03C HTTP scenarios on PHP 8.4/MariaDB 10.11 only.
- C03C reuses already preflight-issued disposable C02 credentials. No additional identity/credential is issued. Existing verified revocation/deletion/secret removal and container/volume/network cleanup remain mandatory; the new outside-repository operational directory is removed after teardown.

## Verification before publication

Local supplemental checks use PHP 8.3.6 and PHPUnit 12.5.38, local WPCS tooling from the accepted correction environment, and PHPStan 2.2.14. They are preparation checks, not substitutes for the exact locked/pinned CI matrix. The definitive candidate will run PHP 8.3/8.4/8.5 unit/lint, level-8 PHPStan, WPCS, both locked Composer audits and one PHP 8.4/MariaDB focused integration lane. No Docker runtime is available in the local scratch environment, so real WordPress/FPM evidence must come from Actions.

Final local checks pass: 28 unit tests / 597 assertions (retained 17 / 309 plus C03C 11 / 288), 76 PHP lint checks, WPCS and PHPStan level 8. Shell syntax and whitespace checks pass. A local PHPStan cold scan initially exceeded the supplemental interpreter's 128 MiB default; the local check completed with an explicit 512 MiB budget. These are not pinned CI results. Tested implementation HEAD/tree, CI run/jobs and cleanup evidence will be recorded in the documentation-only handoff after the one permitted execution. The implementation identity is the single commit first adding this note; its tree is that commit's tree. It is not yet claimed as CI-passing or human-review ready.

## Decisions and remaining boundaries

No new accepted architectural decision. The implementation follows the accepted local-lock design and narrows operational capacity to fixed bounded files. Preauth integration uses the existing explicit early host boundary, without modifying or weakening C02. Storage errors have no fallback.

The deadline is cooperative infrastructure for later bounded adapters, not forced process interruption. Retention is enforced during appends and explicit local maintenance; idle installations require an operator schedule. Unknown/network/tmpfs mounts fail readiness; local overlay backing/persistence needs operator verification. These operational prerequisites are documented, not a production deployment claim.

C03 remains IN PROGRESS. C03A and C03B remain ACCEPTED. C03C remains PARTIAL/unaccepted pending the single focused CI outcome and human review. C04 and Gate 3 remain NOT STARTED. No real bridge read route, content/site reader, capability policy, shared JSON envelope, projection, cursor, mutation journal, approval UI, credential provisioning or production change was added.

## Exact next step

Execute the one authorized focused C03C workflow at the published implementation HEAD. If it executes and fails, record the concrete failing step and stop without a fix/push/rerun cycle. If it passes, record exact evidence in a documentation-only `[skip ci]` commit and hand C03C to human review. Do not begin C03D or C04.

## Files changed

- `.github/workflows/c03c.yml`
- `README.md`
- `docs/ARCHITECTURE.md`
- `docs/C03C-OPERATIONS.md`
- `docs/ROADMAP.md`
- `docs/work-notes/2026-10-06-gate-2-c03c-admission-audit-concurrency.md`
- `phpcs.xml.dist`
- `tests/environment/c03c-compose.yml`
- `tests/environment/c03c-control.php`
- `tests/environment/run.sh`
- `tests/fixtures/c03c-bootstrap.php`
- `tests/fixtures/c03c-controller.php`
- `tests/fixtures/c03c-storage.php`
- `tests/fixtures/c03c-worker.php`
- `tests/security/OperationalAdmissionTest.php`
- `tests/security/c03c.xml`
- `tests/unit/LimitAdmissionTest.php`
- `wordpress/coagmentator/src/Config/class-feature-config.php`
- `wordpress/coagmentator/src/Config/class-transport-policy.php`
- `wordpress/coagmentator/src/Infrastructure/class-local-store.php`
- `wordpress/coagmentator/src/Infrastructure/class-operational-failure.php`
- `wordpress/coagmentator/src/Infrastructure/class-preauth-admission.php`
- `wordpress/coagmentator/src/Infrastructure/class-rate-admission.php`
- `wordpress/coagmentator/src/Infrastructure/class-read-audit.php`
- `wordpress/coagmentator/src/Infrastructure/class-read-deadline.php`
- `wordpress/coagmentator/src/Infrastructure/class-read-operation.php`
- `wordpress/coagmentator/src/Infrastructure/class-read-slot.php`
- `wordpress/coagmentator/src/Infrastructure/class-server-clock.php`
- `wordpress/coagmentator/src/foundation.php`
