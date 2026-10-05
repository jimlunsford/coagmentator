# Work Note: Gate 2 C02 Independent MU Guard

Date: 2026-10-05
Roadmap gate: Gate 2, C02
Status: PARTIAL, implementation candidate awaiting mandatory execution

## Goal and starting state

Implement only C02. Starting main HEAD `d25e08732577e4ff3e55c6d938acd26dd7abdeb3`, tree `dee181535214e8d20c7faafb3f00b7453075866c`. Fresh Git fetch and independent GitHub reads agreed. PR #3 merged at `5941f9e62865c160b33b40d0dfee02131a8f9265`; C01 accepted, C02 not started, Gate 2 IN PROGRESS and Gate 3 NOT STARTED. No conflicting branch/open PR, MU implementation or service credential fixture existed. Required doctrine, contracts, preparation and C01 histories read. Production was not contacted to inventory it.

Branch: `feature/gate-2-c02-independent-mu-guard`, based on that exact main. No main change, merge, tag, release or deployment authorized.

## Implementation candidate

The self-contained top-level MU loader retains emergency hooks even without its support directory. Support classes own a bounded denial registry, exact nine-route POST table, balanced external dispatch scope, guard-side user/UUID evidence and MU REST server. No runtime vendor or normal-plugin autoload dependency.

The operator fixes `COAGMENTATOR_GUARD_REGISTRY`, `COAGMENTATOR_GUARD_FEATURE_POLICY` and optionally `COAGMENTATOR_GUARD_REST_PREFIX` outside request input. Disposable containers mount registry/policy at `/run/coagmentator`, outside the web root. Registry v1 uses exact canonical JSON keys/order: `version`, `protected_user_ids`, `credential_uuids`; positive integer ID list (maximum 32), canonical UUIDv4 list (maximum two). Canonical-byte comparison rejects duplicate members, coercions and ambiguous representations. File size is bounded to 8 KiB. Missing/unreadable/invalid registry is an explicit emergency, never an empty deny list. The minimal feature-readiness file is exactly `{"version":1,"guard_api":1}`; it does not replace C03's complete policy/binding/rotation/capability checks.

Healthy IDs restrict even after marker removal/role promotion. `coagmentator_service` user metadata only adds denials. Emergency blocks remote credential paths and every bridge path while preserving real human password/cookie recovery and public anonymous traffic. Cookie authority is validated by core, never inferred from an admin URL. Failed authentication remains sticky to prevent anonymous fallback.

Fixed future callback convention is the already-loaded final `Coagmentator\Rest\ReadController` at the normal package's exact `src/Rest/ReadController.php` path, static operation-named callback and `authorize_guard_request` permission callback. Namespace/path registration alone is insufficient. C02 ships no such runtime class. The independent guard never loads it. A disposable test-only class returns a failure after internal-dispatch probes; it is not a read implementation or successful bridge operation.

Hooks: `wp_authenticate_application_password_errors` (three arguments, excludes plaintext), `application_password_did_authenticate` (copies only ID/UUID), `application_password_failed_authentication` (zero arguments), late `authenticate`, `determine_current_user`, `set_current_user`, `rest_authentication_errors`, `wp_rest_server_class`, `rest_pre_dispatch`, `rest_request_before_callbacks`, final `rest_dispatch_request`, early/late `init`, `admin_init`, `login_init`. XML-RPC is fenced by the same independent authentication and early non-REST boundary. The wrapper bounds input before core buffering, matches raw transport to request route/method, owns the one-use object scope and balances core/guard dispatch state in finally. Classification lasts for the full PHP request; secrets/evidence are not reused across HTTP requests.

Route table: POST only, `/coagmentator/v1/` plus `site_info`, `search_content`, `get_content`, `list_terms`, `search_media`, `get_media`, `get_metadata`, `list_revisions`, `get_revision`. No routes are registered by the shipped guard. No C03 BridgeIdentity, capability framework, site/actor binding, admission/audit storage, rate/concurrency implementation or read handler exists.

## Disposable verification architecture

Preserve all C01 pins, both lockfiles, three PHP quality lanes and six PHP/database lanes. Extend the existing workflow's push branch filter; retain exact PR-head checkout and verify source commit/tree in every job. Keep GitHub-hosted runners, least permissions, isolated network and no production secrets.

C01 controls run first. Trusted local C02 provisioning then creates a subscriber service fixture with a random 48-byte ordinary password that is immediately discarded, plus an unrelated control administrator. No ordinary service password is exported. Initial registry has an empty UUID list. A separate process verifies the installed guard, zero existing credentials and zero target execution before creating any ephemeral Application Password. Runtime secrets are mode-0600 and never arguments/logs. The cleanup path revokes and verifies removal, deletes fixture users and destroys containers/volumes. Evidence scans run before secret-file destruction as well as at the workflow artifact boundary.

B02/G01/G02/G04 run separately for active/deactivated/absent/deleted plugin, malformed/missing policy, missing/malformed registry, promoted/unmarked service, missing support file and restored healthy configuration. G03 uses a separate synthetic failure callback reached via actual core Application Password authentication. Tests compare target counters and hashes of editorial, user and credential state, not merely HTTP status. Independent follow-up requests exercise cleanup. Pure tests exercise registry corruption, immutable route bytes and object/lifecycle reuse.

## Verification and evidence

Runtime execution is pending. No passing C02 result, human readiness, credential creation or successful cleanup is claimed before execution. Local shell syntax and Git whitespace checks passed. WordPress runtime was independently fetched at the accepted immutable commit `160387b7312c9407c7fe4b1d3dd2055206749a34`; dispatch and Application Password hook signatures were inspected. Official API references also checked. No pin changed.

All six matrix lanes, lint, PHPStan, WPCS, audits, evidence scans and test-family counts must be recorded after actual execution. Failures and corrections will be appended, not erased. Final exact candidate/run identity will be recorded in the PR and session report to avoid self-referential Git hashes.

## Files changed

MU loader and five support classes; pure guard tests; real-HTTP B02/G01-G04 suites and synthetic fixtures; disposable setup/scenario/cleanup orchestration; existing workflow/bootstrap/quality configuration; this note and current status documentation. Original C01 blocker and continuation notes remain unchanged. Git's candidate diff is the complete file inventory.

## Decisions and risks

No accepted architecture or support-policy change. C02 remains unaccepted. These are candidate controls, not a production security claim. Installed PHP/host/database remain trusted, and trusted removal of all guard code still requires prior credential revocation. No persistent service identity or production credential is created by this work.

## Exact next step

Execute and inspect the complete mandatory C02 matrix on this candidate, correct failures within C02, then publish exact-HEAD evidence and open an unmerged review PR only after every mandatory criterion is met. Do not start C03 or Gate 3.

## First execution and corrections

Initial candidate `8d775c2e926990e431e5e29d5a36dd8e0a487615`, tree `62e839c6035ac14efd110bd6ddbc4f91b5b0ac21`, push workflow `37338739582`: FAILURE. All nine job logs inspected. Unit suites pass 7 tests/80 assertions and lint/negative package probes pass on PHP 8.3/8.4/8.5. PHP 8.4 quality stops on two PHPStan findings (future class-string reflection and an unguarded WordPress constant); WPCS/audits not reached. All six WordPress suites pass 3/142 and original HTTP/TLS suites pass 3/10. All six C02 suites reach the active-plugin scenario and fail identically at the HEAD transport probe: curl custom method lacked NOBODY and waited for a body that HEAD correctly omits. Each reports 6 tests/403 assertions/one failure, not a pass for later scenarios.

All six preflights verified zero credentials and no target invocation, then issued disposable service/human Application Passwords. Evidence scanning failed because the mode-0600 file created by container root was unreadable to the host runner. This also interrupted the original exit trap before explicit revocation/Compose teardown. No cleanup success is claimed for that run; those credentials remained confined to the discarded GitHub-hosted job environments. No secret artifact uploaded from the six failed jobs because disclosure checks failed. Correct the fixture ownership while retaining mode 0600, and make cleanup collect errors while always attempting revocation and environment destruction. Do not suppress scan failures or loosen secret permissions.

Corrections also add final callback fencing after permission callbacks, pre-validation fixed-handler identity checks, complete core-stack cleanup on exceptions, and denial of credential-bearing non-REST requests before public callbacks. No accepted pin, test assertion, matrix lane or C03 boundary is relaxed. Subsequent exact-candidate execution remains required.
