# Work Note: Gate 2 C02 Independent MU Guard

Date: 2026-10-05
Roadmap gate: Gate 2, C02
Status: PARTIAL, bounded custom-server human-review correction pending its one permitted CI run; not human-accepted

## Goal and starting state

Implement only C02. Starting main HEAD `d25e08732577e4ff3e55c6d938acd26dd7abdeb3`, tree `dee181535214e8d20c7faafb3f00b7453075866c`. Fresh Git fetch and independent GitHub reads agreed. PR #3 merged at `5941f9e62865c160b33b40d0dfee02131a8f9265`; C01 accepted, C02 not started, Gate 2 IN PROGRESS and Gate 3 NOT STARTED. No conflicting branch/open PR, MU implementation or service credential fixture existed. Required doctrine, contracts, preparation and C01 histories read. Production was not contacted to inventory it.

Branch: `feature/gate-2-c02-independent-mu-guard`, based on that exact main. No main change, merge, tag, release or deployment authorized.

## Implementation candidate

The self-contained top-level MU loader retains emergency hooks even without its support directory. Support classes own a bounded denial registry, exact nine-route POST table, balanced external dispatch scope, guard-side user/UUID evidence and MU REST server. No runtime vendor or normal-plugin autoload dependency.

The operator fixes `COAGMENTATOR_GUARD_REGISTRY`, `COAGMENTATOR_GUARD_FEATURE_POLICY` and optionally `COAGMENTATOR_GUARD_REST_PREFIX` outside request input. Disposable containers mount registry/policy at `/run/coagmentator`, outside the web root. Registry v1 uses exact canonical JSON keys/order: `version`, `protected_user_ids`, `credential_uuids`; positive integer ID list (maximum 32), canonical UUIDv4 list (maximum two). Canonical-byte comparison rejects duplicate members, coercions and ambiguous representations. File size is bounded to 8 KiB. Missing/unreadable/invalid registry is an explicit emergency, never an empty deny list. The minimal feature-readiness file is exactly `{"version":1,"guard_api":1}`; it does not replace C03's complete policy/binding/rotation/capability checks.

Healthy IDs restrict even after marker removal/role promotion. `coagmentator_service` user metadata only adds denials. Emergency blocks remote credential paths and every bridge path while preserving real human password/cookie recovery and public anonymous traffic. Cookie authority is validated by core, never inferred from an admin URL. Failed authentication remains sticky to prevent anonymous fallback.

Fixed future callback convention is the already-loaded final `Coagmentator\Rest\ReadController` at the normal package's exact `src/Rest/ReadController.php` path, static operation-named callback and `authorize_guard_request` permission callback. Namespace/path registration alone is insufficient. C02 ships no such runtime class. The independent guard never loads it. A disposable test-only class returns a failure after internal-dispatch probes; it is not a read implementation or successful bridge operation.

Hooks: `wp_authenticate_application_password_errors` (three arguments, excludes plaintext), `application_password_did_authenticate` (copies only ID/UUID), `application_password_failed_authentication` (zero arguments), late `authenticate`, `determine_current_user`, `set_current_user`, `rest_authentication_errors`, `wp_rest_server_class`, `rest_pre_dispatch`, `rest_request_before_callbacks`, `rest_post_dispatch` failure finalization, final `rest_dispatch_request`, early/late `init`, `admin_init`, `login_init`. XML-RPC is fenced by the same independent authentication and early non-REST boundary. The wrapper bounds input before core buffering, matches raw transport to request route/method, owns the one-use object scope and balances core/guard dispatch state in finally. Classification lasts for the full PHP request; secrets/evidence are not reused across HTTP requests.

Route table: POST only, `/coagmentator/v1/` plus `site_info`, `search_content`, `get_content`, `list_terms`, `search_media`, `get_media`, `get_metadata`, `list_revisions`, `get_revision`. No routes are registered by the shipped guard. No C03 BridgeIdentity, capability framework, site/actor binding, admission/audit storage, rate/concurrency implementation or read handler exists.

## Disposable verification architecture

Preserve all C01 pins, both lockfiles, three PHP quality lanes and six PHP/database lanes. Extend the existing workflow's push branch filter; retain exact PR-head checkout and verify source commit/tree in every job. Keep GitHub-hosted runners, least permissions, isolated network and no production secrets.

C01 controls run first. Trusted local C02 provisioning then creates a subscriber service fixture with a random 48-byte ordinary password discarded after the negative interactive-login probe, a marker-only subscriber with its own discarded password, and an unrelated control administrator. No ordinary service password is exported. Initial registry has an empty UUID list. A separate process verifies the installed guard, zero existing credentials and zero target execution before creating any ephemeral Application Password. Runtime secrets are mode-0600 and never arguments/logs. The cleanup path revokes and verifies removal, deletes fixture users and destroys containers/volumes. Evidence scans run before secret-file destruction as well as at the workflow artifact boundary.

B02/G01/G02/G04 run separately for active/deactivated/absent/deleted plugin, malformed/missing policy, missing/malformed registry, promoted/unmarked service, missing support file and restored healthy configuration. G03 uses a separate synthetic failure callback reached via actual core Application Password authentication. Tests compare target counters and hashes of editorial, user and credential state, not merely HTTP status. Independent follow-up requests exercise cleanup. Pure tests exercise registry corruption, immutable route bytes and object/lifecycle reuse.

## Verification and evidence

Initial execution was pending when this note was created. The append-only candidate history below records actual tests, credential issuance and cleanup, including failures. C02 remains unaccepted pending human review. Local shell syntax and Git whitespace checks passed. WordPress runtime was independently fetched at the accepted immutable commit `160387b7312c9407c7fe4b1d3dd2055206749a34`; dispatch and Application Password hook signatures were inspected. Official API references also checked. No pin changed.

All six matrix lanes, lint, PHPStan, WPCS, audits, evidence scans and test-family counts are tied to actual candidate executions below. Failures and corrections will be appended, not erased. Final exact candidate/run identity will be recorded in the PR and session report to avoid self-referential Git hashes.

## Files changed

MU loader and five support classes; pure guard tests; real-HTTP B02/G01-G04 suites and synthetic fixtures; disposable setup/scenario/cleanup orchestration; existing workflow/bootstrap/quality configuration; this note and current status documentation. Original C01 blocker and continuation notes remain unchanged. Git's candidate diff is the complete file inventory.

## Decisions and risks

No accepted architecture or support-policy change. C02 remains unaccepted. These are candidate controls, not a production security claim. Installed PHP/host/database remain trusted, and trusted removal of all guard code still requires prior credential revocation. No persistent service identity or production credential is created by this work.

## Exact next step

Human-review the exact tested head of the unmerged C02 PR. Decide C02 acceptance separately; do not merge without explicit authorization. Do not start C03 or Gate 3. The final documentation commit must itself obtain green exact-head CI before the PR is opened.

## First execution and corrections

Initial candidate `8d775c2e926990e431e5e29d5a36dd8e0a487615`, tree `62e839c6035ac14efd110bd6ddbc4f91b5b0ac21`, push workflow `37338739582`: FAILURE. All nine job logs inspected. Unit suites pass 7 tests/80 assertions and lint/negative package probes pass on PHP 8.3/8.4/8.5. PHP 8.4 quality stops on two PHPStan findings (future class-string reflection and an unguarded WordPress constant); WPCS/audits not reached. All six WordPress suites pass 3/142 and original HTTP/TLS suites pass 3/10. All six C02 suites reach the active-plugin scenario and fail identically at the HEAD transport probe: curl custom method lacked NOBODY and waited for a body that HEAD correctly omits. Each reports 6 tests/403 assertions/one failure, not a pass for later scenarios.

All six preflights verified zero credentials and no target invocation, then issued disposable service/human Application Passwords. Evidence scanning failed because the mode-0600 file created by container root was unreadable to the host runner. This also interrupted the original exit trap before explicit revocation/Compose teardown. No cleanup success is claimed for that run; those credentials remained confined to the discarded GitHub-hosted job environments. No secret artifact uploaded from the six failed jobs because disclosure checks failed. Correct the fixture ownership while retaining mode 0600, and make cleanup collect errors while always attempting revocation and environment destruction. Do not suppress scan failures or loosen secret permissions.

Corrections also add final callback fencing after permission callbacks, pre-validation fixed-handler identity checks, complete core-stack cleanup on exceptions, and denial of credential-bearing non-REST requests before public callbacks. No accepted pin, test assertion, matrix lane or C03 boundary is relaxed. Subsequent exact-candidate execution remains required.

Second candidate `37a78d56d4afdbb0200724726416d663bb8fa4b4`, tree `3fecca04894c46b16d8b8e702b8db211b2f48b8c`, run `37339263631`: FAILURE. All nine logs inspected. Units/lint and six WordPress suites still pass. The new non-REST remote-credential denial correctly rejects C01's intentionally fake Authorization-header probe when the guard is installed, exposing a harness ordering conflict. Preserve that original header-forwarding assertion unchanged and install the MU bundle only after C01 controls, before C02 provisioning/preflight. No credential was created in this run; all six cleanup paths reached Compose destruction and evidence checks passed. PHPStan still reports the future class-string annotation issue; add an explicit class-string annotation after the verified class-existence check. Run quality checks independently so one failure cannot hide later WPCS/audit findings.

The next candidate additionally checks newly issued human cookies against actual wp-admin content, retains existing-cookie/nonce controls, and repeats both registry emergencies with the normal plugin active as well as deactivated. Add an unreadable-registry HTTP variant. These strengthen required coverage; no test is skipped or treated as a pass.

Third candidate `8c01aab131998a8f59b0d37236954b3d7f293c08`, tree `323d7cc64e8bb2db84fdc9a0f9c6daa0a7f152dc`, run `37339809554`: FAILURE. Both unchanged C01 integration/control suites pass again. Nginx provides an empty Authorization variable even for anonymous traffic; the guard incorrectly treated presence alone as remote credentials and returned 403 for public/control probes. Correct to nonempty credential values, without normalizing supplied nonempty credentials. PHPStan rejects the literal class-string annotation; use a typed reflection helper with an actual existence check. WPCS reports docblock, array-formatting and raw-input annotations, now addressed in source. Audits execute independently. All evidence scans and explicit revocation/verified deletion/Compose teardown complete in all six lanes.

The next iteration adds real no-credential HTTP preflight for healthy and emergency registries before issuance, a negative missing-loader issuance preflight, in-memory-only ordinary service-password interactive denial, valid XML-RPC multicall encoding and unrelated human XML-RPC controls. It adds the loader-owned closed failure encoder and `rest_post_dispatch` finalization, without a success serializer. HTTP route tests add literal/double-encoded backslashes and encoded dot segments. None of these is a claimed pass until the next exact-candidate run.

Fourth candidate `02c5da8df7ff5ebfb1ae882975e547b501437576`, tree `ce4b3527d32194532428d412b25be37a3e9cbb2e`, run `37341146444`: FAILURE. PHPStan is now clean; WPCS finds remaining formatting/Yoda issues. All six lanes pass three no-credential HTTP preflights (1 test/172 assertions each), reject credential issuance with the loader absent, and pass the first six healthy-registry scenarios (7 tests/615 assertions each). Missing-registry XML-RPC responses were an empty HTTP 200 because core's wp_die XML-RPC handler has no server instance during init. Explicitly set HTTP 403 before wp_die; preserve the denial assertion. All six cleanup/disclosure paths complete. Additional proof runs route/method negatives with the synthetic fixed handler actually loaded and tests replacement before argument validation, permission and callback execution. A marker-only subscriber demonstrates the marker cannot confer authority; it receives no Application Password.

Fifth candidate `cdea6c47d50267ef95cb6af75f72a3fdde16172d`, tree `19d5c3206282af31c8a06357ba49cdc411017793`, run `37341688109`: all six integration lanes PASS, PHP 8.3/8.5 unit-quality jobs PASS. PHP 8.4 has clean PHPStan and one WPCS failure: the required root MU entry point's fallback class conflicts with the class-file naming convention. Add only an exact filename-rule exclusion in the central ruleset; no guard/test file or other sniff is excluded. Each lane completes every scenario, actual internal-authentication/exception/next-request proof, replacement denial, pre-destruction secret scan, explicit credential revocation/verification/user deletion and container/volume destruction. No persistent production credential exists.

Final hardening unifies early denial with the same loader-owned closed failure JSON, avoiding core's unavailable early XML-RPC encoder. Tests assert the canonical failure shape, add a valid batch payload, and deny marker-only cookies on non-REST targets. The complete matrix must rerun because these are source changes. Security test documentation now describes the actual C02 harness rather than the historical C01 boundary.

Sixth candidate `34b72257f2fd99db623d0005c7ffc0c556bfe738`, tree `bcfd3f25c1e10f86f575fac2ba69164f5ad96650`, run `37342337818`: integration scenarios and closed-denial assertions pass; PHP 8.4 quality has one remaining WPCS blank-line formatting error in a test, corrected without changing behavior. PHPStan remains clean. Audit artifact `11359096906` was downloaded and verified against SHA-256 `9361c55818b84f54a1c8cb1286c40a0e0a18d5a80b123e45f4504b8af19c3e50`; its candidate SHA/tree match and both Composer audit payloads have empty advisories, abandoned and filter arrays. Final review also found the user digest omitted display-name/password fields; hash the full core user data object plus roles and credential UUID/hash tuples. No raw user data is returned or logged. This strengthens the required no-user-mutation assertion and requires the full matrix again.


## Completed implementation evidence

Runtime candidate HEAD `6f96ae2eeba65fa700deb5da0162006c39ef16ed`, tree `db26ebbcca3ba7e844a23782dccbffcf6f0c5504`. [Workflow run 37342913936](https://github.com/jimlunsford/coagmentator/actions/runs/37342913936) is SUCCESS. All nine job logs were inspected and each verifies that exact SHA/tree. No lane was substituted, skipped or waived.

| Job | Job ID | Conclusion |
| --- | --- | --- |
| Unit and quality PHP 8.3 | 111874212192 | SUCCESS |
| Unit and quality PHP 8.4 | 111874212727 | SUCCESS |
| Unit and quality PHP 8.5 | 111874212609 | SUCCESS |
| PHP 8.3 + MariaDB 10.11 | 111874212633 | SUCCESS |
| PHP 8.3 + MySQL 8.4 | 111874212608 | SUCCESS |
| PHP 8.4 + MariaDB 10.11 | 111874213086 | SUCCESS |
| PHP 8.4 + MySQL 8.4 | 111874212793 | SUCCESS |
| PHP 8.5 + MariaDB 10.11 | 111874212590 | SUCCESS |
| PHP 8.5 + MySQL 8.4 | 111874212680 | SUCCESS |

Each unit lane passes 7 tests/80 assertions, lints all 34 PHP files and passes the three C01 package-load controls. PHP 8.4 additionally passes PHPStan level 8, WPCS and both locked Composer audits. The MU filename exception is confined to one required filename and one naming-rule diagnostic; no code file or security sniff is excluded wholesale.

Each integration lane passes these independently executed suites:

| Phase | Tests | Assertions | Result |
| --- | ---: | ---: | --- |
| Preserved WordPress integration | 3 | 142 | PASS |
| Preserved verified-TLS HTTP controls | 3 | 10 | PASS |
| Empty-credential preflight, each of 3 states | 1 | 186 | PASS |
| B02/G01/G02/G04, each of 8 healthy-registry scenarios | 7 | 762 | PASS |
| B02/G01/G02/G04, each of 6 registry/support emergencies | 7 | 772 | PASS |
| G03 plus native/normalization/non-REST controls with fixed synthetic handler loaded | 6 | 525 | PASS |
| Fixed callback replacement | 1 | 7 | PASS |

That is 108 C02 test executions/11,818 assertions per database lane, plus the six preserved C01 tests/152 assertions. The internal suite proves the admitted synthetic outer callback is reached in all three independent requests, including one deliberately thrown exception, while all eleven internal probes deny. It never returns bridge success. Every hostile-target test also checks callback counters and editorial/user/credential state. The final snapshot hashes the complete core user record, roles and credential UUID/hash tuples; it exposes no record contents.

Every lane scans protected runtime values before destruction, explicitly revokes both users' Application Passwords, verifies zero remaining credentials, deletes all three disposable users and removes the secret file. The exit trap independently attempts scan, revocation and Compose volume/network teardown and propagates any failure. All six successful logs contain the explicit revocation-verification marker and three successful evidence scans. Earlier cleanup failures remain recorded above rather than rewritten as successes.

No accepted pin, image digest, WordPress source commit, Composer manifest/lock or ordinary plugin file changed. The original C01 WordPress/HTTP controls and C01 historical notes are unchanged. Fresh main verification still yields starting HEAD `d25e08732577e4ff3e55c6d938acd26dd7abdeb3`; it is the branch merge-base. No production access occurred and no production service user or credential was created. C03, Gate 3 and real bridge read handlers remain unstarted.

## Final review identity and publication

This final handoff/status edit is documentation-only relative to the green runtime candidate above. A commit cannot embed its own SHA/tree without changing them. Therefore the final review HEAD/tree are bound explicitly in the PR body and in every final job's checked source and `environment.json` artifact. Publication must verify the final documentation commit has all nine green jobs before opening the PR, and freshly compare its HEAD to the PR head. No merge, release, tag or deployment is part of this handoff. Working-tree cleanliness and unchanged main must be checked again at publication.

## Complete candidate file inventory

```text
M	.github/workflows/c01.yml
M	README.md
M	docs/ARCHITECTURE.md
M	docs/ROADMAP.md
A	docs/work-notes/2026-10-05-gate-2-c02-independent-mu-guard.md
M	phpcs.xml.dist
M	phpstan.neon.dist
A	tests/bootstrap/security.php
M	tests/bootstrap/unit.php
M	tests/environment/README.md
A	tests/environment/c02-control.php
M	tests/environment/check-evidence.py
M	tests/environment/compose.yml
M	tests/environment/prepare.py
M	tests/environment/run.sh
M	tests/environment/wp-config.php
A	tests/fixtures/c02-controller.php
A	tests/fixtures/c02-instrumentation.php
A	tests/fixtures/c02-observe.php
A	tests/fixtures/c02-target.php
A	tests/security/GuardHttpCase.php
A	tests/security/GuardIndependenceTest.php
A	tests/security/InternalDispatchTest.php
A	tests/security/NativeRestBypassTest.php
A	tests/security/NonRestBypassTest.php
A	tests/security/PreflightTest.php
M	tests/security/README.md
A	tests/security/ReplacementTest.php
A	tests/security/RouteNormalizationTest.php
A	tests/security/internal.xml
A	tests/security/phpunit.xml
A	tests/security/preflight.xml
A	tests/security/replacement.xml
A	tests/unit/GuardBoundaryTest.php
A	wordpress/mu-plugins/coagmentator-guard.php
A	wordpress/mu-plugins/coagmentator-guard/src/class-dispatch-scope.php
A	wordpress/mu-plugins/coagmentator-guard/src/class-guard-config.php
A	wordpress/mu-plugins/coagmentator-guard/src/class-guard.php
A	wordpress/mu-plugins/coagmentator-guard/src/class-guarded-rest-server.php
A	wordpress/mu-plugins/coagmentator-guard/src/class-route-boundary.php
```


## Bounded human-review correction: competing REST server

Date: 2026-10-05. Starting PR #4 HEAD `5833c19b66e42236e0d7cf7e3220f9838286439d`, tree `aab1cce23e4ede304107ff914e53c394b2879cb2`. Fresh remote PR/ref reads and Git agree; PR is open, ready, mergeable and unmerged. Main remains `d25e08732577e4ff3e55c6d938acd26dd7abdeb3`. This correction supersedes the earlier ready-for-review status; C02 and Gate 2 remain IN PROGRESS and unaccepted.

Goal: preserve an ordinary custom REST server while rejecting its use as Coagmentator service authority. `Guard::server_class()` now returns an unsupported competing selection without setting the request-wide authentication-failure latch. The existing actual-server checks in handler readiness, preflight and dispatch still reject bridge/service access. Default selection continues to install the final guarded server. No registry, route, UUID, marker, callback, emergency or dispatch-scope design changes.

Added disposable `C02_Custom_REST_Server`, selected through `wp_rest_server_class`. The new HTTP suite asserts the actual selected class and credential count, denies bridge/native service and marker-cookie requests, retains target/state sentinels, and verifies public native REST plus an identified legitimate human cookie/nonce request. The fixed synthetic controller is loaded during both conflict phases. The first phase follows an actual rejected issuance preflight and requires zero Application Passwords; the second exercises already-issued credentials. Removing the fixture restores successful normal preflight/issuance and reruns the original internal-dispatch/native/normalization/non-REST suite unchanged.

Files: guard class; test-only custom server, observer and CustomServerTest/XML suite; disposable preflight and run orchestration; security harness README; architecture; this handoff; one workflow trigger line. Only the duplicate C02 branch push trigger is removed, leaving the existing exact-head PR trigger and every job/lane/control/pin/lock unchanged. This enforces the owner's one-full-execution limit for the open PR. A later evidence-only documentation update will not launch another matrix, because documentation is outside the existing PR paths filter.

Local verification before publication: shell syntax and Git whitespace checks. No local PHP/container runtime is available; runtime, lint and quality evidence must come from the single complete exact-correction-head CI execution. No CI pass is claimed here. One implementation pass and one CI execution are authorized; on failure inspect the failing step, append its evidence and stop without source fixes or reruns. Any post-run handoff commit is documentation-only and must distinguish the tested correction SHA/tree from the final documentation SHA/tree rather than claim that the latter was separately run.

Decision: honor the accepted availability boundary without trusting another server. No C03 or production work. Next action: publish this correction to PR #4, inspect its one nine-job/six-lane exact-head run, record the outcome, and stop for human review (or report the remaining failure). Final identities/run/job evidence belong in the result appendix and PR description after they exist.
