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
