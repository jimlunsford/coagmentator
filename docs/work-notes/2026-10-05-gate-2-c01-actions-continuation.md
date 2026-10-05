# Work Note: C01 Actions continuation

Date: 2026-10-05
Roadmap gate: Gate 2, C01 Package and Test Skeleton
Status: PARTIAL (candidate being exercised; not accepted)

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

Corrected implementation matrix is pending. Construction success is not C01 acceptance. Version/digest details are in `tests/environment/manifest.json`; both lockfiles contain the complete isolated dependency graphs. No runtime vendors ship with the plugin.

Preflight used checkout v4.2.2 and produced a Node 20 migration warning; subsequent checkout uses immutable v5.0.0. Upload-artifact v4.6.2 produced upstream Node deprecation notices. Docker's deprecated stop flag was corrected. These are infrastructure notices, not suppressed PHP test warnings.

Local Git HTTPS push lacked authentication; publication uses the authorized GitHub connector. This is not an Actions execution blocker.

## Decisions

No support-policy or architectural changes. GitHub-hosted Actions is the owner-selected authoritative C01 execution environment. Gate 2 remains IN PROGRESS. C02 and Gate 3 have not begun.

## Risks / unresolved items

All actual runtime acceptance evidence must pass before a PR is opened. No representative-lane substitution, guard claim, deployment or production readiness claim.

## Exact next step

Run the complete candidate in Actions, inspect terminal jobs and actual logs, correct only C01 defects, and publish an exact green candidate for human review. Stop on an accepted architecture/support contradiction. Do not merge or begin C02.

## References

- Preflight: https://github.com/jimlunsford/coagmentator/actions/runs/37326523446
- Construction: https://github.com/jimlunsford/coagmentator/actions/runs/37326910421
- WordPress archive: https://wordpress.org/download/releases/
- Implementation PR, release, tag, deployment: none.
