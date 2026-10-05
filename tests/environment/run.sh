#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../.."
mkdir -p .runtime/evidence
python3 tests/environment/prepare.py
container=(docker run --rm --network none -v "$PWD:/workspace" coagmentator-c01-php)
if [[ ${C01_MODE:-} == unit ]]; then
  "${container[@]}" php tools/quality/vendor/bin/phpunit -c phpunit.xml.dist --log-junit .runtime/evidence/unit.xml
  while IFS= read -r -d '' file; do
    "${container[@]}" php -l "$file"
  done < <(find wordpress tests -type f -name '*.php' -print0)
  "${container[@]}" php tests/fixtures/package-load.php 7.1.2
  "${container[@]}" php tests/fixtures/package-load.php 7.1.1 reject
  "${container[@]}" php -n tests/fixtures/package-load.php 7.1.2 reject
  if [[ $C01_PHP == 84 ]]; then
    quality_status=0
    "${container[@]}" php tools/quality/vendor/bin/phpstan analyse -c phpstan.neon.dist --no-progress || quality_status=1
    "${container[@]}" php tools/quality/vendor/bin/phpcs --config-set installed_paths ../../wp-coding-standards/wpcs,../../phpcsstandards/phpcsextra,../../phpcsstandards/phpcsutils
    "${container[@]}" php tools/quality/vendor/bin/phpcs --standard=phpcs.xml.dist -s || quality_status=1
    for graph in quality wp-tests; do
      docker run --rm -v "$PWD:/workspace" -w "/workspace/tools/$graph" coagmentator-c01-php composer audit --locked --format=json > ".runtime/evidence/$graph-audit.json" || quality_status=1
    done
    exit "$quality_status"
  fi
  exit 0
fi
compose=(docker compose --project-name "c01-${C01_PHP}-${C01_DATABASE}" --env-file .runtime/compose.env -f tests/environment/compose.yml)
cleanup() {
  original_status=$?
  trap - EXIT
  set +e
  python3 tests/environment/check-evidence.py
  scan_status=$?
  "${compose[@]}" exec -T php php tests/environment/c02-control.php cleanup
  revoke_status=$?
  "${compose[@]}" down --volumes --remove-orphans
  destroy_status=$?
  if (( original_status || scan_status || revoke_status || destroy_status )); then
    exit 1
  fi
}
trap cleanup EXIT
"${compose[@]}" up -d database php edge
"${compose[@]}" exec -T edge nginx -v
# Bounded readiness probe, without printing connection errors or passwords.
for attempt in $(seq 1 60); do
  if "${compose[@]}" exec -T php php -r 'mysqli_report(MYSQLI_REPORT_OFF); $db = @new mysqli("database", "c01", trim(file_get_contents("/run/secrets/database_password")), "wordpress"); exit($db->connect_errno ? 1 : 0);' 2>/dev/null; then
    break
  fi
  if [[ $attempt == 60 ]]; then
    printf 'Disposable database readiness failed.\n' >&2
    exit 1
  fi
  sleep 2
done
"${compose[@]}" exec -T php php tests/environment/install.php
"${compose[@]}" exec -T php php tools/wp-tests/vendor/bin/phpunit -c tests/wordpress/phpunit.xml --log-junit .runtime/evidence/wordpress.xml
"${compose[@]}" run --rm client php tools/quality/vendor/bin/phpunit -c tests/http/phpunit.xml --log-junit .runtime/evidence/http.xml
network="c01-${C01_PHP}-${C01_DATABASE}_isolated"
test "$(docker network inspect --format '{{.Internal}}' "$network")" = true
printf 'Internal test network verified; no published host ports.\n'

# C02 begins only after the original C01 smoke/control suite passes.
cp -R wordpress/mu-plugins .runtime/wordpress/src/wp-content/mu-plugins
cp tests/fixtures/c02-observe.php .runtime/wordpress/src/c02-observe.php
cp tests/fixtures/c02-target.php .runtime/wordpress/src/c02-target.php
cp tests/fixtures/c02-instrumentation.php .runtime/wordpress/src/wp-content/mu-plugins/zz-c02-fixture.php
"${compose[@]}" exec -T php php tests/environment/c02-control.php setup
for scenario in restored missing-registry malformed-registry; do
  "${compose[@]}" exec -T php php tests/environment/c02-control.php scenario "$scenario"
  "${compose[@]}" restart php
  "${compose[@]}" run --rm client php tools/quality/vendor/bin/phpunit -c tests/security/preflight.xml --log-junit ".runtime/evidence/c02-preflight-$scenario.xml"
done
"${compose[@]}" exec -T php php tests/environment/c02-control.php scenario restored
mv .runtime/wordpress/src/wp-content/mu-plugins/coagmentator-guard.php .runtime/c02-loader-held.php
if "${compose[@]}" exec -T php php tests/environment/c02-control.php preflight; then
  printf 'Credential issuance did not stop on absent guard.\n' >&2
  exit 1
fi
mv .runtime/c02-loader-held.php .runtime/wordpress/src/wp-content/mu-plugins/coagmentator-guard.php
"${compose[@]}" exec -T php php tests/environment/c02-control.php preflight
for scenario in active deactivated absent deleted bad-policy missing-policy missing-registry malformed-registry missing-registry-active malformed-registry-active unreadable-registry promoted-unmarked missing-support restored; do
  "${compose[@]}" exec -T php php tests/environment/c02-control.php scenario "$scenario"
  if [[ $scenario == absent || $scenario == deleted ]]; then
    mv .runtime/wordpress/src/wp-content/plugins/coagmentator .runtime/c02-plugin-held
  fi
  if [[ $scenario == missing-support ]]; then
    mv .runtime/wordpress/src/wp-content/mu-plugins/coagmentator-guard/src/class-guard.php .runtime/c02-guard-held.php
  fi
  "${compose[@]}" restart php
  "${compose[@]}" run --rm client php tools/quality/vendor/bin/phpunit -c tests/security/phpunit.xml --log-junit ".runtime/evidence/c02-$scenario.xml"
  if [[ -d .runtime/c02-plugin-held ]]; then
    mv .runtime/c02-plugin-held .runtime/wordpress/src/wp-content/plugins/coagmentator
  fi
  if [[ -f .runtime/c02-guard-held.php ]]; then
    mv .runtime/c02-guard-held.php .runtime/wordpress/src/wp-content/mu-plugins/coagmentator-guard/src/class-guard.php
  fi
done
"${compose[@]}" exec -T php php tests/environment/c02-control.php scenario internal
mkdir -p .runtime/wordpress/src/wp-content/plugins/coagmentator/src/Rest
cp tests/fixtures/c02-controller.php .runtime/wordpress/src/wp-content/plugins/coagmentator/src/Rest/ReadController.php
printf '%s\n' '<?php require WP_PLUGIN_DIR . "/coagmentator/src/Rest/ReadController.php";' > .runtime/wordpress/src/wp-content/mu-plugins/zy-c02-controller.php
"${compose[@]}" restart php
"${compose[@]}" run --rm client php tools/quality/vendor/bin/phpunit -c tests/security/internal.xml --log-junit .runtime/evidence/c02-internal.xml
python3 tests/environment/check-evidence.py
"${compose[@]}" exec -T php php tests/environment/c02-control.php cleanup
