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
    "${container[@]}" php tools/quality/vendor/bin/phpstan analyse -c phpstan.neon.dist --no-progress
    "${container[@]}" php tools/quality/vendor/bin/phpcs --config-set installed_paths ../../wp-coding-standards/wpcs,../../phpcsstandards/phpcsextra,../../phpcsstandards/phpcsutils
    "${container[@]}" php tools/quality/vendor/bin/phpcs --standard=phpcs.xml.dist -s
    for graph in quality wp-tests; do
      docker run --rm -v "$PWD:/workspace" -w "/workspace/tools/$graph" coagmentator-c01-php composer audit --locked --format=json > ".runtime/evidence/$graph-audit.json"
    done
  fi
  exit 0
fi
compose=(docker compose --project-name "c01-${C01_PHP}-${C01_DATABASE}" --env-file .runtime/compose.env -f tests/environment/compose.yml)
trap '"${compose[@]}" down --volumes --remove-orphans' EXIT
"${compose[@]}" up -d database php edge
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
