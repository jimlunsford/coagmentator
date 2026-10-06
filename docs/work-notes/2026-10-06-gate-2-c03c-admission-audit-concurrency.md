# Work Note: Gate 2 C03C Admission, Concurrency and Audit Foundation

Date: 2026-10-06 America/New_York
Roadmap gate: Gate 2, C03C
Status: PARTIAL / BLOCKED after the single authorized CI execution

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

C03 remains IN PROGRESS. C03A and C03B remain ACCEPTED. C03C remains PARTIAL/unaccepted and blocked by the failed cleanup step below. It is not ready for acceptance review. C04 and Gate 3 remain NOT STARTED. No real bridge read route, content/site reader, capability policy, shared JSON envelope, projection, cursor, mutation journal, approval UI, credential provisioning or production change was added.

## Published candidate and single CI outcome

- Tested implementation HEAD: `f1b09e82e610e2a7362c63338ef5b3e31878b739`.
- Tested implementation tree: `8dce0fce188be335465d57d60f33988cb1cb12ec`.
- [Focused run 37539689554](https://github.com/jimlunsford/coagmentator/actions/runs/37539689554), attempt 1, push event, began 2026-10-06 22:18:00 UTC and completed FAILURE at 22:24:31 UTC. All four jobs verified the exact candidate checkout and tree.
- Ordinary git push lacked a write credential. The GitHub connector published the identical implementation tree, and local/remote identities were reconciled before evaluating CI. No PR or merge was created. The branch workflow ran only three unit/quality jobs and the one authorized PHP 8.4/MariaDB lane.
- This result is recorded in a documentation-only child of the tested implementation, with `[skip ci]`. Its exact HEAD/tree are supplied in the final handoff response, rather than an impossible self-referential commit hash inside its own contents. Runtime, tests, workflow, dependencies and locks are identical to the tested candidate. No fix or rerun followed the failure.

| Job | Job ID | Outcome |
| --- | --- | --- |
| Unit and quality PHP 83 | `112529361728` | SUCCESS |
| Unit and quality PHP 84 | `112529361706` | SUCCESS |
| Unit and quality PHP 85 | `112529361729` | SUCCESS |
| Integration and HTTP PHP 84 mariadb | `112529361353` | FAILURE in `Real WordPress integration and verified TLS`, during final directory cleanup |

Each pinned PHP lane (8.3.35, 8.4.26, 8.5.11) passed 28 unit tests / 597 assertions: retained 17 / 309 plus C03C 11 / 288. Each passed all 76 PHP lint checks, negative package-load checks, PHPStan 2.2.17 level 8, WPCS and both locked Composer audits. The downloaded PHP 8.4 evidence confirms both audit JSON files contain empty `advisories`, `abandoned` and `filter` arrays. Evidence allowlist/disclosure scans passed in all four jobs, including the failed HTTP job, and sanitized artifacts uploaded successfully.

The focused integration lane executed real WordPress 7.1.2, PHP 8.4.26 FPM, pinned MariaDB 10.11 and verified TLS. Totals below were independently summed from the 86 JUnit XML files in its downloaded artifact. Every XML reports zero failures, errors and skips. These passing assertions do not override the failed job outcome.

| Executed suite | Tests | Assertions |
| --- | ---: | ---: |
| C01/C02 regressions | 122 | 12,809 |
| C03A regressions | 18 | 637 |
| C03B regressions | 27 | 413 |
| Retained integration subtotal | 167 | 13,859 |
| C03C HTTP scenarios | 17 | 1,285 |
| Focused integration total | 184 | 15,144 |

## Executed C03C evidence

| Control | Evidence from this exact candidate |
| --- | --- |
| Authenticated 60/61 | `c03c-rate.xml`: 1 test / 562 assertions; first 60 callbacks enter and request 61 is denied before the target. Deterministic unit boundary and minute rollover also pass. |
| Preauth 120/121 | `c03c-preauth.xml`: 1 / 250; 120 invalid-auth ingress attempts account early and 121 denies at the host boundary. Unit tests retain the canonical immediate-peer key despite forged forwarding claims. |
| Four/five concurrency | `c03c-parallel.xml`: 1 / 57; four actual simultaneous FPM callbacks hold slots, fifth denies within the test's two-second bound, then a fresh request acquires after explicit release. Independent CLI workers prove the same fixed four-slot behavior. |
| Nonblocking and cleanup | Unit control-lock contention and fifth-slot acquisition fail promptly with LOCK_NB. Healthy and throwing HTTP scenarios each pass 1 / 70, including six separate requests and an all-four-slots-free check. |
| Restart persistence | Unit tests invoke fresh independent PHP interpreters against the same persistent authenticated and preauth counters, retaining exhaustion rather than resetting on worker replacement. |
| Process failure | Unit tests terminate a real holding worker with SIGKILL, then acquire through a new independent worker and verify all slots free after teardown. This is OS lock-release evidence, not a claim to catch OOM/SIGKILL in PHP. |
| Storage failures and identity | Twelve remaining HTTP scenarios each pass 1 / 23: wrong-site, read-corrupt, read-full, read-unwritable, audit-unwritable, audit-corrupt, audit-clock-corrupt, audit-full, audit-missing, slot-corrupt, preauth-corrupt and preauth-unwritable. All storage/audit denials use fixed target sentinels to prove pre-target refusal. Unit cases add capped preauth buckets, active retention/expired retirement, corrupt/truncated state, unsafe paths/permissions/symlinks and unsupported mounts. |
| Deadline | Unit evidence covers exact 15-second expiration, remaining budget, sticky failure and invalid/backwards clocks. This is cooperative enforcement for later adapters, not forced interruption. |
| Audit and disclosure | Unit schema/seed-marker assertions, HTTP audit scans and artifact allowlist/secret scans pass. Corrupt, full, missing and unwritable audit state denies before the callback. Retained audit capacity is preserved; exact 90-day expiration and explicit idle maintenance pass unit checks. No bodies, credentials, Authorization values, raw SQL, paths, emails or stack traces are admitted to the four-field audit schema. |

The twelve storage/identity scenarios contribute 12 tests / 276 assertions. Healthy, throw, rate, preauth and parallel contribute 5 / 1,009. C03C HTTP totals are therefore 17 / 1,285. C03C unit totals are 11 / 288 on each of the three PHP lines.

## Concrete blocker and cleanup evidence

At 22:24:26 UTC the log reports completion of all C03C scenarios, a successful evidence scan, then `C02 credentials revoked, revocation verified, disposable identities and secret file removed.` C03C reused those disposable credentials and issued none. A second evidence scan passed. Docker then removed the origin, edge, database and PHP containers and the isolated network. The teardown command includes `--volumes --remove-orphans` and reports no Docker teardown error.

At 22:24:27.620 UTC the host command `rmdir "$C03C_STORAGE"` in `tests/environment/run.sh` failed exactly as follows:

```text
rmdir: failed to remove '/tmp/coagmentator-c03c-http-sOarpAEw': Operation not permitted
```

The EXIT trap correctly propagated `destroy_status=1`, producing exit code 1 for `Real WordPress integration and verified TLS`. Immediately afterward, the script unconditionally printed `C03C outside-repository operational fixture removed.` That line is misleading and is not cleanup success evidence. Complete removal of the operational fixture root was not achieved. The preceding network-disabled cleanup container reported no error deleting its contents; the log contains no independent post-removal inventory, so no stronger cleanup claim is made.

The concrete cause is the fixture ownership boundary: `c03c-control.php` changes the bind-mounted root to worker UID 33, while final `rmdir` executes as the unprivileged runner under sticky `/tmp`. The final cleanup container deletes children but does not remove the mount root or restore runner ownership. This source/log-supported diagnosis is recorded without changing the harness. The job runner has finished; no access to its leftover directory is available from this session.

Nonfatal environment notices also appeared: Docker build-argument defaults, already-loaded mbstring/shtool terminal notices, Composer dubious-ownership/root-version fallback, and upload-artifact Node/punycode/url.parse deprecations. The accepted pins/locks and quality gates were not changed or suppressed. None changes the concrete cleanup failure above.

## Retained CI artifacts

Artifacts use the existing 14-day retention, expiring 2026-10-20. Downloaded unit-84 and integration ZIP digests match the logged upload digests. The integration archive has 87 files (86 JUnit plus environment metadata), 40,131 bytes. Durable numerical and failure evidence is recorded here because Actions artifacts expire.

| Artifact | Artifact ID | ZIP SHA-256 |
| --- | --- | --- |
| unit-83 | `11447613366` | `da4f493b3a3dad9ebe7e01c4a46c65062d1a719f1e145792dd157664aa2c4faf` |
| unit-84 | `11448072705` | `ea380ed60e60974934ca989c06d8b12cd05e95344fe25bf2885d7a6ec87b0f65` |
| unit-85 | `11448247251` | `f72bc75680c07cc3971350b91d169134821c9b9b98c70260b1082738d3c95172` |
| integration-http-84-mariadb | `11447669150` | `cd26d134a8a008285892d30252c8dd94fc44dd7ce1cb7a74d79d49d9ec8b4bff` |

## Exact next step

Stop at this failed C03C checkpoint. The owner must authorize a separate, narrowly scoped correction of disposable-directory cleanup/accurate cleanup reporting and a new focused execution before more implementation or CI work. No correction, rerun, acceptance or merge is authorized by this completed pass. Do not begin C03D, the full matrix, C04 or bridge routes.

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
