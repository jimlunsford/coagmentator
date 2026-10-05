# Work Note: C01 Actions continuation

Date: 2026-10-05
Roadmap gate: Gate 2, C01 Package and Test Skeleton
Status: BLOCKED / PARTIAL (corrected candidate awaits new CI execution; not accepted)

## Goal

Resume C01 on GitHub-hosted Actions, preserving the earlier local-environment blocker. No C02, service identity, Application Password, bridge operation or production access.

## Starting state

- Main HEAD `ab4ccb3362ca13b006ff3f2887d4743af06ba035`, tree `9e83480e09bf6f81882b897827866a46e256d2a1`.
- Branch `feature/gate-2-c01-package-test-skeleton` HEAD `e2f7a0c3b4e959acdf556fedeb1700838755334b`, tree `d502ed75961836fc9ed0f53a72d556f6475b3fc6`.
- Fresh clone and connector reads verified identity, note-only branch, no implementation files/PR or C02 branch. Repository handoffs report no service identity, Application Password or production access. Production was not contacted to re-inventory it.
- Mandatory repository doctrine, contracts, preparation and handoffs read before changes.

## Work completed

- Published minimal read-only Actions preflight on Ubuntu 24.04.
- Run `37326523446`, job `111818573005`, attempt 1: terminal SUCCESS at `230bc1e4378477ee54d0401b2bf3d36311fe3a13`, tree `074a32690fb2ade62a46a169046c0c258b351990`.
- Verified hosted Linux x64, Ubuntu 24.04.5, Docker 28.0.4, Compose 2.38.2, exact checkout, public dependency/image access and disposable container start/stop. No production secrets.
- Construction run `37326910421`, job `111819884607`, at `88f6df211c38445f1eab2e56948511ec78ec9b90` resolved official public image digests and separately constructed two Composer locks. Artifact `11352642392`, SHA-256 `aacb35e732e82271f074fe60943b50ca6731aa710ed8483406d0b15285c2c105`, was downloaded and verified before incorporation.
- Both construction audits have zero advisories and zero abandoned packages. No audit ignores or platform-requirement bypasses.
- WordPress release archive still identifies 7.1.2. Matching upstream tag resolves to `0a106cde38df869e196a83eb4975ba38a4e5837b`. Core manifest/bootstrap were read at that exact revision.
- Package admission checks and separate unit/core/HTTP bootstraps drafted. Ordinary package remains dormant, with no hook, role, route, MU guard or service provisioning.
- CI candidate adds three unit/lint lanes and six real integration/HTTP lanes, read-only permissions, internal Compose networks and ephemeral CA trust.

## Files changed

Package and tests under `wordpress/coagmentator`, `tests`, isolated `tools/quality` and `tools/wp-tests` manifests/locks, environment manifest, PHPUnit/PHPStan/PHPCS configurations, C01 workflows, contracts/security boundary READMEs and this note. The original blocker note is unchanged.

## Verification and limits

First full run `37328161707` at `fe11dfe31930a0ff5b43c41b6bdc64aa6d7f74eb` reached terminal FAILURE, attempt 1. All three unit suites passed (4 tests/15 assertions each), all three lint/negative-load probes passed and PHPStan passed. Quality failed on WPCS formatting/PHPDoc and narrow test-fixture conventions. All six real core suites booted and created fixtures, but failed the exact runtime assertion: wordpress-develop contains `7.1.2-src`, not the release build. HTTP had not yet run. No failure is counted as passing.

Corrections preserve the exact release assertion: use the matching immutable WordPress/WordPress release commit `160387b7312c9407c7fe4b1d3dd2055206749a34` for runtime and retain the separately pinned wordpress-develop test library. Also set WP_INSTALLING explicitly for disposable setup, make Compose secrets readable by their unprivileged container consumers while keeping the host secret directory private, and fix WPCS findings with narrow documented fixture annotations.

Primary-source recheck found the old stable-bookworm Nginx alias was stale. Select current stable 1.30.5-trixie from the official image catalog and Docker Hub API, pinned by index digest. No accepted Nginx line was changed because preparation selected none. Do not reuse the old 1.28.0 result as final acceptance.

Corrected implementation matrix is NOT RUN. Correction commit `3a3cd5660914ac396491214ee0b82e9bffbe7f0a` has tree `023371f43d3af3c006bd8f6e70e169b349686046`. Environment README follow-up `23b6b2611195b2252f2503d7865b25a0b12b481f` has tree `c78d598a65f28bef9b07ce0b199ff74c7f3e89fd`.

Repeated branch/run/check-suite queries after both publications returned no new execution. Remote ref and independent Git fetch confirm publication. Public workflow metadata still reports all three workflows active. No GitHub rejection or disabled-Actions cause was returned, so the cause is UNDETERMINED, not asserted to be a permissions or runner failure. The connector can inspect/rerun old jobs but exposes no dispatch for a new exact candidate; rerunning the failed old HEAD cannot verify these corrections. No PR was opened as a trigger workaround because the owner requires completion before opening it.

Latest actual full-run evidence (run 37328161707, attempt 1, terminal FAILURE):

| Job | ID | Conclusion / actual evidence |
| --- | --- | --- |
| Unit/quality PHP 8.3 | 111824110475 | SUCCESS; 4 tests, 15 assertions; lint and positive/negative vendor-free package probes passed |
| Unit/quality PHP 8.4 | 111824110071 | FAILURE at WPCS; unit 4/15, lint, probes and PHPStan passed; final audits not reached |
| Unit/quality PHP 8.5 | 111824110400 | SUCCESS; 4/15, lint and probes passed |
| PHP 8.3 + MariaDB 10.11 | 111824110568 | FAILURE; 3 integration tests, 139 assertions, 1 exact release-version failure; HTTP not reached |
| PHP 8.3 + MySQL 8.4 | 111824110342 | Same failure and counts; HTTP not reached |
| PHP 8.4 + MariaDB 10.11 | 111824110351 | Same failure and counts; HTTP not reached |
| PHP 8.4 + MySQL 8.4 | 111824110291 | Same failure and counts; HTTP not reached |
| PHP 8.5 + MariaDB 10.11 | 111824110397 | Same failure and counts; HTTP not reached |
| PHP 8.5 + MySQL 8.4 | 111824110502 | Same failure and counts; HTTP not reached |

All nine job logs were inspected. No job was retried. Core's messages excluding its own ajax/ms-files/external-http groups are upstream bootstrap notices; the project ran its three defined integration tests, not the entire WordPress core suite. No assertion was removed to conceal a failure.

The final continuation publication changes only status/handoff documentation after the correction and environment README. Its exact HEAD/tree are recorded in the session report; resolve the introducing commit normally. No corrected implementation pass is claimed.

Additional local verification: JSON manifests/locks parse, XML test/quality configs parse, shell syntax and whitespace checks pass. These are static checks, not a substitute for Actions runtime evidence. Construction success is not C01 acceptance. Version/digest details are in `tests/environment/manifest.json`; both lockfiles contain the complete isolated dependency graphs. No runtime vendors ship with the plugin.

Preflight used checkout v4.2.2 and produced a Node 20 migration warning; subsequent checkout uses immutable v5.0.0. Upload-artifact v4.6.2 produced upstream Node deprecation notices. Docker's deprecated stop flag was corrected. These are infrastructure notices, not suppressed PHP test warnings.

Local Git HTTPS push lacked authentication; publication uses the authorized GitHub connector. This is not an Actions execution blocker.

## Decisions

No support-policy or architectural changes. GitHub-hosted Actions is the owner-selected authoritative C01 execution environment. Gate 2 remains IN PROGRESS. C02 and Gate 3 have not begun.

## Risks / unresolved items

New exact-candidate Actions execution is currently unavailable through the observed push path. All actual runtime acceptance evidence must pass before a PR is opened. No representative-lane substitution, guard claim, deployment or production readiness claim.

## Exact next step

Investigate/restore new-HEAD GitHub Actions triggering, then execute the complete corrected candidate in all nine jobs. Inspect terminal jobs/logs, correct remaining C01 defects, and only after exact green evidence open the implementation PR for human review. Stop on an accepted architecture/support contradiction. Do not merge or begin C02.

## References

- Preflight: https://github.com/jimlunsford/coagmentator/actions/runs/37326523446
- Construction: https://github.com/jimlunsford/coagmentator/actions/runs/37326910421
- WordPress archive: https://wordpress.org/download/releases/
- Implementation PR, release, tag, deployment: none. Main remains `ab4ccb3362ca13b006ff3f2887d4743af06ba035`.

## Trigger recovery continuation, 2026-10-05

This appended section preserves the historical observations above and supersedes their latest-state assumptions. C01 remains PARTIAL/BLOCKED and unaccepted pending a green exact-candidate run.

### Reverified starting checkpoint and changed evidence

Main remains `ab4ccb3362ca13b006ff3f2887d4743af06ba035`, tree `9e83480e09bf6f81882b897827866a46e256d2a1`. Starting branch HEAD was `4af70d64091dfc37a63422663dfa6f22330be1cd`, tree `036a1fa364c00aff98ad847bc1ab95cd10e68895`. Remote Git-data reads and local objects agree. No C01 PR existed before this session.

All three exact-tip workflows were fetched and inspected. YAML and embedded shell syntax passed; GitHub's public workflow API reported all three active. The main C01 workflow retains its matching unfiltered push branch and existing pull_request path filters. All three commits after `fe11dfe31930a0ff5b43c41b6bdc64aa6d7f74eb` were inspected, without skip-CI annotations. The release-runtime/test-source split, WPCS changes, WP_INSTALLING, secret readability, Nginx 1.30.5 digest and HTTP setup corrections were present.

The earlier absent executions are now visible:
- `3a3cd5660914ac396491214ee0b82e9bffbe7f0a`: push matrix run `37329897651` FAILURE; preflight `37329897576` SUCCESS; construction `37329897639` SUCCESS, created at 15:05:03 UTC.
- `23b6b2611195b2252f2503d7865b25a0b12b481f`: push matrix run `37329831311` FAILURE, created at 15:04:33 UTC.
- `4af70d64091dfc37a63422663dfa6f22330be1cd`: push matrix run `37329860596` FAILURE, created at 15:04:46 UTC; suite `101110095326`.

Strongest supported classification: delayed observable push execution, not a continuing absence of push execution. The underlying delay cause is UNPROVEN. No outage, disabled Actions, YAML, token-suppression, permission or runner root cause is claimed. All nine latest-tip push job logs were inspected: unit suites passed 4 tests/15 assertions each; lint/negative-load probes and PHPStan passed; remaining WPCS formatting findings blocked quality/audits. All six integration suites now pass 3 tests/142 assertions; HTTP reached 3 tests/8 assertions but errored twice on undefined `CURLE_PEER_FAILED_VERIFICATION`.

### Independent PR event

The owner authorized one draft PR solely for CI recovery despite the prior pre-green PR prohibition. [PR #3](https://github.com/jimlunsford/coagmentator/pull/3) was opened DRAFT at 15:16:35 UTC, base main and the existing C01 branch. Its title/body explicitly said partial/blocked, not ready, do not merge, C02 not begun and production untouched. No merge or human acceptance occurred.

Checkout-only correction `c6c6bf54e05f39f1f112cad7d97a7e4014897a4d`, tree `f9975ca3416d03594ca191ab6166afb8848fc3ba`, makes both matrix jobs explicitly select `github.event.pull_request.head.sha` for PR events and `github.sha` for push. Every job prints event/activity, expected/actual SHA and tree, then fails on mismatch. No assertion, pin, permission or matrix change.

First PR run: [37331691984](https://github.com/jimlunsford/coagmentator/actions/runs/37331691984), event `pull_request`, activity `opened`, attempt 1, created 15:18:02 UTC, terminal FAILURE. All nine logs verify the exact head/tree above, not synthetic merge commit `809ef0c7fbce823ac59e9da1315f8575cfb8669f`. No workflow approval was required or reported. Concurrent push run `37331474288` also executed that head; no reruns were requested.

| First PR-run job | Job ID | Conclusion |
| --- | --- | --- |
| Unit and quality PHP 83 | 111836105477 | SUCCESS |
| Unit and quality PHP 84 | 111836105704 | FAILURE |
| Integration and HTTP PHP 83 mysql | 111836105773 | FAILURE |
| Integration and HTTP PHP 84 mysql | 111836105849 | FAILURE |
| Integration and HTTP PHP 84 mariadb | 111836105955 | FAILURE |
| Integration and HTTP PHP 83 mariadb | 111836105963 | FAILURE |
| Integration and HTTP PHP 85 mariadb | 111836105999 | FAILURE |
| Unit and quality PHP 85 | 111836106003 | SUCCESS |
| Integration and HTTP PHP 85 mysql | 111836106004 | FAILURE |

All first PR-run job logs were inspected. Results match the newly visible push evidence: every unit suite 4/15 PASS; six WordPress suites 3/142 PASS; six HTTP suites error on the same missing PHP constant; PHP 8.4 WPCS fails nine formatting findings. These are actual test/quality failures, not approval-required or trigger failures.

### C01-scoped corrections after the first PR run

- Correct PHPDoc type alignment, duplicate blank lines, and add descriptive comments before two require statements. The pinned Squiz FileComment sniff treats a docblock immediately preceding require as that statement's documentation, so a separate descriptive comment distinguishes the existing file docblock. No sniff is disabled.
- Use PHP's documented `CURLE_SSL_CACERT` name in both TLS negative assertions. Both still require curl_exec false and the exact certificate-verification error; trust/hostname validation remains enabled. No fallback accepting arbitrary errors.
- All existing runtime/test-source/image/dependency pins and the six-lane matrix are unchanged.
- Files: `wordpress/coagmentator/src/class-environment.php`, `tests/bootstrap/unit.php`, `tests/fixtures/http-probe.php`, `tests/unit/EnvironmentTest.php`, `tests/wordpress/BootstrapTest.php`, `tests/http/HttpTest.php`, this appended note. Earlier checkout change touched only `.github/workflows/c01.yml`.
- Local diff/whitespace verification passed. Runtime evidence for this correction must come from its new PR synchronize run, not the older head.

Sources: [PHP cURL constants](https://www.php.net/manual/en/curl.constants.php), [libcurl errors](https://curl.se/libcurl/c/libcurl-errors.html), pinned PHP_CodeSniffer 3.13.6 `Squiz/Sniffs/Commenting/FileCommentSniff.php`, and [checkout PR-head guidance](https://github.com/actions/checkout#checkout-pull-request-head-commit-instead-of-merge-commit).

### Next step and boundaries

Publish this bounded correction on the existing branch; verify the changed PR head and a new pull_request/synchronize run; inspect all terminal jobs and logs before declaring readiness. No C02, MU guard, bridge route, service user or Application Password was implemented or issued. Ordinary disposable WordPress control-admin fixtures remain test-only. Production was neither contacted nor modified. Main, releases and tags remain untouched.

## C01 internal verification completed, 2026-10-05

This is the current C01 handoff, superseding the blocked latest-state statements in the historical entries above. **C01 is internally verified and awaits human acceptance. Gate 2 remains IN PROGRESS.** No merge or C02 authorization is implied.

Correction commit `4cac2d6689a973d6c96596f2d31c981bb272df69`, complete tree `3ae31f56608b4e16d0e86c870f55cf4dd7bf690b`, produced PR run [37332251616](https://github.com/jimlunsford/coagmentator/actions/runs/37332251616), attempt 1, terminal SUCCESS. Every job printed `pull_request` / `synchronize`, verified that exact source SHA, and printed the matching tree. No approval was required; no jobs were retried.

| Corrected PR-run job | Job ID | Conclusion |
| --- | --- | --- |
| Unit and quality PHP 84 | 111838011805 | SUCCESS |
| Unit and quality PHP 85 | 111838012111 | SUCCESS |
| Integration and HTTP PHP 84 mariadb | 111838012196 | SUCCESS |
| Integration and HTTP PHP 84 mysql | 111838012359 | SUCCESS |
| Integration and HTTP PHP 83 mariadb | 111838012370 | SUCCESS |
| Unit and quality PHP 83 | 111838012373 | SUCCESS |
| Integration and HTTP PHP 83 mysql | 111838012380 | SUCCESS |
| Integration and HTTP PHP 85 mariadb | 111838012471 | SUCCESS |
| Integration and HTTP PHP 85 mysql | 111838012574 | SUCCESS |

### Final implementation evidence

- PHPUnit 12.5.38 unit suites: 4 tests / 15 assertions on each of PHP 8.3.35, 8.4.26 and 8.5.11.
- PHP lint and vendor-free positive/unsupported-version/missing-extension load probes: PASS on all three lines. Unit assertions separately cover unsupported PHP, WordPress, integer width, each required extension and multisite.
- PHPStan 2.2.17 level 8: PASS. WPCS 3.4.1 with PHP_CodeSniffer 3.13.6: PASS, no newly disabled sniff.
- Both Composer 2.10.3 locked-graph audits: zero advisories and zero abandoned packages. Downloaded audit JSON independently inspected.
- WordPress 7.1.2 / PHPUnit 9.6.38 / Polyfills 1.1.5: 3 tests / 142 assertions in each of all six PHP/database lanes.
- HTTP/TLS / PHPUnit 12.5.38: 3 tests / 10 assertions in each of all six lanes. Actual Nginx/FPM/WordPress/database and Authorization-header probe passed; untrusted CA and wrong hostname each fail with the exact certificate error. TLS verification stays enabled.
- All six networks verified internal, with no published host ports. Test teardown completed. No skipped project tests or unresolved project warnings/deprecations were accepted.
- All nine job logs inspected. All nine artifact ZIPs downloaded, SHA-256 checked against the API digest, and their exact candidate SHA/tree plus complete environment manifests checked. Parsed all JUnit suites, including zero failures/errors/skips (and zero WordPress warnings), and both audit results.
- Environment manifest, seven digest-selected images, runtime/test-library source revisions and both lockfiles are unchanged from the starting corrected candidate. Exact manifest/lock package versions agree. Quality lock SHA-256 `5f35eca05b1896efdbba2ce444d3b1a664e9119a7e8337a0fd40eb765468b711`; WordPress-test lock SHA-256 `acd1f10d0a7de7638cb5408e2f947ffe48d8508324bdd7fc14e87af39b90518f`.
- Nonblocking upstream/tool notices remain identified: upload-artifact v4.6.2 Node deprecation notices, core test-library notices about its own excluded groups, and Composer's fallback root-package version notice when Git refuses cross-UID repository ownership inside the container. No PHP project deprecation was suppressed and dependency/platform checks passed.

[Machine-readable evidence](2026-10-05-gate-2-c01-ci-recovery-evidence.json) preserves the first failed PR run, green implementation run, job identifiers, relevant verified log excerpts, artifact identities/digests, parsed test cases, both audit payloads and exact pins. The GitHub artifacts expire after 14 days; these recorded verification facts remain in Git.

### Final documentation checkpoint and review procedure

This final publication changes only README, architecture/status documentation, this appended handoff, and the evidence JSON. It does not change implementation, workflows, tests, assertions, dependency locks or environment pins after the green run above. The original environment-blocker note remains byte-for-byte unchanged; earlier continuation entries remain historical evidence.

To avoid self-referential commit identifiers or endless evidence-only commits, this note records the fully tested implementation checkpoint. The introducing final documentation commit is separately tested with the same complete workflow. Its exact HEAD/tree, final run, nine job IDs and conclusions are recorded in PR #3 and the execution report. Do not mark the PR ready unless that exact final checkpoint is green. No older run is substituted for the newer HEAD.

Once the final exact-HEAD run passes, mark PR #3 ready for human review, leave it open and unmerged, and stop. The next owner action is human acceptance review of C01. Do not begin C02. Any merge or C02 execution requires a separate instruction.

### Boundaries confirmed

No service identity or Application Password was created or found by disposable fixture inventory. The only ordinary users are core's disposable test/control users, not Coagmentator service users. No MU guard, authentication, bridge route, C02 behavior or production connection was added. Production was neither contacted nor modified. Main remains `ab4ccb3362ca13b006ff3f2887d4743af06ba035`, tree `9e83480e09bf6f81882b897827866a46e256d2a1`; no merge, tag, release or deployment occurred. No architecture/support-policy change.
