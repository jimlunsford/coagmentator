# Work Note: Gate 2 C03A Authentication Evidence and Configuration Foundation

Date: 2026-10-05 America/Indiana/Indianapolis (2026-10-06 UTC)
Roadmap gate: Gate 2, C03A
Status: BLOCKED, C03A partial after the single focused CI execution

## Goal

Implement only authentication evidence and the closed configuration/binding foundation. One implementation pass and one focused CI execution are authorized. A real CI failure or runner-infrastructure block ends this execution after recording evidence. No fix/push/rerun loop.

## Starting state

- Repository: `jimlunsford/coagmentator`.
- Verified remote/main HEAD: `11db731b9d6fbf016b0bf6dfe7d565a1d10732ed`.
- Verified complete tree: `6fd663cd18d2c1e3a03a069198d2da2c2bfea8a8`.
- Accepted C02 merge: `88bbb46ae2593a7722d8cc8a06503f20a5041738`; PR #4 independently reports merged.
- Gate 0, Gate 1, Gate 2 preparation, C01 and C02 ACCEPTED. C03 NOT STARTED before this work; Gate 2 IN PROGRESS; Gate 3 NOT STARTED.
- Fresh remote branches and all PRs show no prior C03 branch/implementation. Current Git objects, mandatory doctrine, plan, matrix and C02 closeout were read before changing files. The normal plugin was the dormant C01 skeleton; no runtime ReadController existed.
- No production identity/credential/access/deployment is recorded. Production was not contacted to inventory it; this is repository/test evidence and the session boundary, not a production audit.
- Branch created locally from exact main: `feature/gate-2-c03-auth-config-operations-foundation`. Main is not modified.

## Work completed

Normal-plugin components:

- `Config/Identity_Values`: canonical UUIDv4, safe integer ID and bounded actor syntax.
- `Config/Credential_Window`: one approved UUID or an explicit absolute two-UUID interval of at most 24 hours; each identity check revalidates expiry. Expired overlap requires trusted policy repair and does not auto-select a credential.
- `Config/Operator_Path`: bounded absolute path syntax and lexical/resolved exclusion of web/code roots, including existing ancestor symlinks; no future storage opened or created.
- `Config/Feature_Config`: 16 KiB canonical compact JSON, closed fields/types, independent registry consistency, enabled site/actor/service binding, UUID overlap, canonical origin/path, private-read flag, exact read-operation set, policy version/profile, writes false, and later cursor/audit/admission references. No HTTP-selected configuration path or secret field.
- `Auth/Authentication_Evidence`: observes only user ID/UUID from the authentic core success event, intersects with live guard admission/current-user equality/core availability, refreshes credential existence through the core API, rejects persistence and multiple/failed events.
- `Auth/Bridge_Identity`: fresh boolean identity check with exact site/actor/service/credential/policy binding. No caller-supplied user or UUID and no role/marker grant.
- Explicit `src/foundation.php` includes and normal-plugin observer loading. No Composer runtime dependency, route, capability policy, role provisioner, serializer or read operation.

The accepted C02 MU package is unchanged. Its route/object/depth/lifecycle fences remain authoritative. The identity check is not an external-dispatch grant. The normal observer registers after the guard, and it never registers for plaintext-password arguments, retains an entire credential record, or reads request Authorization/nonce/cookie fields.

The permanent [C03A configuration document](../C03A-CONFIGURATION.md) owns the exact schema and limitations. No accepted D-series decision changes.

## Tests added and focused workflow

- Unit `BindingConfigurationTest`: exact/disabled binding, wrong user/site/actor/UUID, malformed/missing/unknown fields, duplicates, unsafe paths/symlinks, registry mismatch, invalid profiles, write refusal under both profiles, bounds and no storage side effects.
- Unit `CredentialWindowTest`: one/two/more credentials, duplicate/malformed UUIDs, missing/unknown/timing types, future/expired/overlong intervals, exact 24-hour boundary and expiry recheck on an existing object.
- HTTP `ApplicationPasswordIdentityTest`: 17 trusted CLI scenarios, using actual core Application Password events and the normal plugin. Correct service, valid wrong Administrator/subscriber, wrong configured service, wrong UUID, UUID versus app_id, wrong site/actor, disabled binding, valid/expired overlap, promotion/marker removal, role-name-only authority denial, missing normal evidence, mismatched current user, revocation after event and subsequent revoked authentication.
- HTTP `AlternateAuthenticationTest`: anonymous, service/human cookie, nonce, cookie plus nonce, injected current user, JWT-like plugin identity, OAuth bearer, Basic without authentic event, normal login password and marker-only cookie. Each follows a separate positive authenticated foundation evaluation and independently asserts the normal foundation was invoked, denied identity, no success event and no synthetic callback. Protected editorial/user snapshots must remain equal.
- A disposable synthetic controller uses normal identity permission checks, always returns failure, and verifies serialization denial and stale evidence denial after guard finish. It lives only under tests and is copied into the disposable test installation. No runtime ReadController is added.
- Existing C01 smoke/TLS controls and complete C02 guard/preflight/no-effect tests remain unchanged and run on the single focused lane first.
- New push-only `c03a.yml`: three unit/lint lanes (8.3, 8.4, 8.5), PHP 8.4 PHPStan/WPCS/two locked Composer audits, and exactly PHP 8.4 + MariaDB 10.11 integration/HTTP. No PR is needed for this workflow. C01/C02 workflows, source/image pins, lockfiles and assertions are unchanged.

## Disposable credentials and cleanup plan

Only the accepted disposable Actions topology may issue test credentials. The unchanged C02 preflight creates the primary service/human credentials after guard controls pass. C03A creates one extra disposable service credential with a deliberately different UUID/app_id only after that preflight and primary fixture exist. Secrets stay in the existing mode-0600 runtime fixture/memory, are excluded from artifacts and are scanned before cleanup. No production credential or service identity is created.

The existing C02 cleanup deletes all credentials for each fixture user, verifies empty credential lists, deletes users and the protected fixture, then tears down containers/volumes/networks through the EXIT trap. The overlap credential is owned by the same service user and included in that revocation. Executed cleanup evidence must be recorded below; planned cleanup is not a pass claim.

## Verification before publication

- Exact starting Git state and remote/PR metadata verified.
- Shell syntax and workflow topology inspected: three unit jobs, one database job, no six-lane matrix for C03A.
- `git diff --check` and scope/pin/guard preservation checked before publication.
- Local PHP, Composer and Docker are unavailable in this Work shell. No local runtime execution is claimed; the one accepted Actions run supplies all runtime/quality evidence.
- Official WordPress success-event and UUID-lookup references were rechecked, without changing accepted core/environment pins. Credential UUID, not app_id, is the core lookup identity.

## Exact candidate and CI evidence

Published runtime/test HEAD: `7b6de0ca631557c031d1ae2ccd8f8d7873cb8778`.

Complete tested tree: `873236fa2952378fbc764c1548defcc76c94b5d4`.

Sole parent is the exact starting main. The local reviewed tree and GitHub-created tree match exactly. Direct Git push lacked local authentication; the authorized connector published the same complete tree and created the branch. A fresh Git fetch independently confirms it. The earlier local commit differed only in commit metadata and is not claimed as the tested source.

Exactly one workflow was triggered: [37398487754](https://github.com/jimlunsford/coagmentator/actions/runs/37398487754), `push`, attempt 1. All four jobs received GitHub-hosted runners and verified the exact source SHA/tree. No PR was opened. No rerun, second CI execution, code correction or test weakening occurred after the real failure.

| Job | Actual job ID | Observed result |
| --- | --- | --- |
| PHP 8.3 unit/lint | 112060034925 | SUCCESS; 13 tests / 235 assertions; 52 PHP lint checks and package-load controls pass |
| PHP 8.4 unit/quality | 112060035015 | FAILURE in Unit, lint, negative load and quality; unit 13/235 and all 52 lint checks pass; PHPStan/WPCS fail |
| PHP 8.5 unit/lint | 112060034978 | SUCCESS; 13 tests / 235 assertions; 52 PHP lint checks and package-load controls pass |
| PHP 8.4 + MariaDB 10.11 integration/HTTP | 112060034742 | SUCCESS; 140 tests / 13,446 assertions total, including 18 C03A tests / 637 assertions |

PHPStan level 8 reports exactly one error at `Config/class-feature-config.php:139`: the upper-port comparison is always true after `parse_url` has returned its documented integer range (`smallerOrEqual.alwaysTrue`). No baseline or analysis setting was changed.

WPCS reports 27 errors and five warnings. Findings cover PHPDoc type/spacing compatibility (`list<string>` versus PHP `array` annotations), two Yoda comparisons, the always-throwing serialization method's return documentation, two file-docblock classifications, associative-array layout, one assignment alignment, intentional serialization/Base64 test operations and duplicate test-only `ReadController` class names. The duplicate-class finding is caused by the new isolated C03A fixture; the accepted C02 fixture remains byte-identical. These findings are recorded, not corrected in this execution.

Both locked Composer audits still executed despite those quality failures. Downloaded artifact `11383743284`, SHA-256 `d1f4139f2e324842a5dd1be681b6a529e7fc720833b3ab1d11f3f54b90933c3b`, independently verifies both audit JSON files have empty `advisories`, `abandoned` and `filter` arrays. Its environment manifest matches the exact tested HEAD/tree. All three unit jobs passed the evidence disclosure scan and uploaded sanitized artifacts. No dependency pin or lock changed.

The focused integration job completed successfully. Its unchanged C01/C02 portion passed 122 tests / 12,809 assertions. The new C03A portion passed all 17 identity scenarios plus the alternate-authentication test, totaling 18 tests / 637 assertions. The full lane passed 140 tests / 13,446 assertions. The custom-server and absent-guard preflight controls proved zero credentials before the restored guard preflight allowed fixture creation. No second database lane ran. Sanitized integration evidence was uploaded as artifact `11383539101`.

Executed cleanup evidence, all on 2026-10-06 UTC:

- At 01:21:57.379, the evidence allowlist and secret scan passed before cleanup.
- At 01:21:57.669, cleanup confirmed credentials revoked, revocation verified, disposable identities deleted and the runtime secret fixture removed. Revocation-after-event and revoked-UUID scenarios also passed independently before cleanup.
- The overlap credential belonged to the same disposable service user and was covered by the delete-all/recheck cleanup. No production credential or identity was used.
- At 01:21:57.707, the cleanup evidence scan passed again.
- The existing EXIT trap ran Compose teardown with `--volumes --remove-orphans`. Edge, PHP and database containers and the isolated network were removed by 01:21:58.352.
- At 01:21:58.399, the workflow evidence scan passed. The integration job concluded SUCCESS.

The overall run concluded FAILURE solely in the PHP 8.4 quality step. This is a real quality failure, not a runner-infrastructure block. Work stopped without correcting source/tests, changing quality settings or executing another CI run.

## Documentation-only handoff checkpoint

The final handoff is a direct child of the tested HEAD above. It changes only `README.md`, `docs/ARCHITECTURE.md`, `docs/ROADMAP.md` and this work note to record the observed result. Its commit message is `docs: record partial C03A focused CI evidence [skip ci]`. Runtime, test, workflow, pin and lock blobs remain identical to the tested tree. No runtime verification is claimed for a second candidate. The final report records the resulting documentation commit and complete tree literally; embedding a commit's own hash in its tracked contents would be self-referential.

## Risks and deviations

C03 is partial and unaccepted. No C03B transport/proxy enforcement, C03C rate/concurrency/audit/flock behavior, full C03D matrix, C04 capability policy, bridge reads or Gate 3 work is implemented. Stored paths/flags are configuration references, not operational claims. C02 guard-only failure finalization remains unchanged. No production access, deployment, merge, tag or release.

## Exact next action

A separately authorized, short C03A continuation must correct the recorded PHPStan/WPCS findings, preserve the accepted guard/pins/locks and scope, then repeat the focused verification. There is no observed integration blocker. C03A is not complete or accepted. C03 remains IN PROGRESS; C03B is NOT STARTED and must not begin as a correction workaround.


## Files added/changed

- `.github/workflows/c03a.yml`
- `README.md`
- `docs/ARCHITECTURE.md`
- `docs/C03A-CONFIGURATION.md`
- `docs/ROADMAP.md`
- `docs/work-notes/2026-10-05-gate-2-c03a-authentication-configuration.md`
- `tests/bootstrap/unit.php`
- `tests/environment/c03a-control.php`
- `tests/environment/run.sh`
- `tests/fixtures/c03a-controller.php`
- `tests/fixtures/c03a-instrumentation.php`
- `tests/fixtures/c03a-observe.php`
- `tests/fixtures/c03a-policy.php`
- `tests/security/AlternateAuthenticationTest.php`
- `tests/security/ApplicationPasswordIdentityTest.php`
- `tests/security/c03a-alternate.xml`
- `tests/security/c03a.xml`
- `tests/unit/BindingConfigurationTest.php`
- `tests/unit/CredentialWindowTest.php`
- `wordpress/coagmentator/coagmentator.php`
- `wordpress/coagmentator/src/Auth/class-authentication-evidence.php`
- `wordpress/coagmentator/src/Auth/class-bridge-identity.php`
- `wordpress/coagmentator/src/Config/class-credential-window.php`
- `wordpress/coagmentator/src/Config/class-feature-config.php`
- `wordpress/coagmentator/src/Config/class-identity-values.php`
- `wordpress/coagmentator/src/Config/class-operator-path.php`
- `wordpress/coagmentator/src/foundation.php`
