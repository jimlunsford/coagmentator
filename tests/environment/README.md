# C01 disposable environment

GitHub-hosted Ubuntu 24.04 runners are the authoritative C01 execution environment. The same Compose harness may run on a disposable Linux x64 development host with Docker Engine/Compose, Python 3, Git and OpenSSL. Never run these scripts on a production host or against an existing WordPress installation.

## Commands

From the repository root, use a fresh checkout per lane:

- Unit/lint/package negative tests: `C01_PHP=83 C01_MODE=unit bash tests/environment/run.sh` (repeat 84 and 85).
- Quality, including PHPStan level 8, WPCS and both Composer audits: the same command with `C01_PHP=84`.
- Real WordPress and HTTP/TLS: `C01_PHP=83 C01_DATABASE=mariadb bash tests/environment/run.sh`. Repeat the Cartesian product of 83/84/85 and mariadb/mysql.

The environment refuses missing/unknown lane selectors. PHP-FPM and the client use the digest-pinned PHP image plus test-only extensions/dependencies. Composer graphs are separate, installed from committed locks, with plugins/scripts disabled and platform checks enabled. WPCS standards are registered explicitly without executing a Composer plugin. Development vendors never ship with the WordPress package.

## Immutable inputs

`manifest.json` records image digests, tool versions, the release runtime commit and matching core test-library commit. Runtime uses WordPress/WordPress 7.1.2; tests use wordpress-develop's matching 7.1.2 library. The latter's src tree labels itself 7.1.2-src and is intentionally not substituted for the release runtime.

Official upstream evidence: [WordPress release archive](https://wordpress.org/download/releases/), [PHP support](https://www.php.net/supported-versions.php), [PHPUnit support](https://phpunit.de/supported-versions.html), [WPCS](https://github.com/WordPress/WordPress-Coding-Standards), [Docker official image catalog](https://github.com/docker-library/official-images), [Nginx releases](https://nginx.org/), and the Composer package registry URLs used by `resolve.py`.

Construction run 37326910421 produced the reviewed locks and initial image manifest. The Nginx stable-bookworm alias resolved to an older image; the final manifest instead pins official 1.30.5-trixie by its Docker Hub index digest. Floating discovery tags are provenance only, never acceptance image selectors. Re-running the construction helper does not authorize adopting its output. Review changes, commit the resulting manifest/locks, and rerun all lanes.

## Isolation and scope

Compose has an internal network and no published host ports. Construction downloads precede runtime isolation. A private host directory contains disposable database secrets; only required individual files are mounted read-only into the database and FPM containers so their unprivileged processes can read them. TLS uses a fresh short-lived test CA; only the test client explicitly trusts it. Certificate/hostname validation is never disabled, and wrong-host/untrusted-chain controls must fail.

Normal WordPress setup creates an ordinary disposable control administrator with a generated, discarded password. It creates no service identity or Application Password. Cron/updates/external HTTP are disabled; mail is intercepted during installation. Core integration uses a separate database and PHPUnit 9.6 vendor graph. The HTTP probe is synthetic test-only code copied into the disposable runtime, never into the shipped package.

C01 contains no MU guard, bridge route, capability provisioning, read handler or mutation behavior. The normal package only checks prerequisites and remains dormant. Unit facts cover unsupported PHP, WordPress, integer width, missing required extensions and multisite; fresh processes additionally prove actual unsupported package loads return false without vendors.

Only allowlisted JUnit/JSON evidence passes the disclosure check for upload. Runtime files, raw server logs, secret files and private keys are excluded. Compose teardown runs on test failures. GitHub job cancellation also destroys the hosted runner; this is not a production cleanup mechanism.
