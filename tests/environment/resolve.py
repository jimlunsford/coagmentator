"""One-time C01 construction on Actions; output is reviewed before acceptance."""
import json
import pathlib
import re
import subprocess
import urllib.request

out = pathlib.Path('.c01-construction')
out.mkdir(exist_ok=True)

def run(*args):
    return subprocess.check_output(args, text=True).strip()

def fetch(url):
    with urllib.request.urlopen(url, timeout=60) as response:
        return json.load(response)

images = {}
for key, tag, command in [
    ('php83', 'php:8.3-fpm-bookworm', ['php', '-r', 'echo PHP_VERSION;']),
    ('php84', 'php:8.4-fpm-bookworm', ['php', '-r', 'echo PHP_VERSION;']),
    ('php85', 'php:8.5-fpm-bookworm', ['php', '-r', 'echo PHP_VERSION;']),
    ('mariadb', 'mariadb:10.11', ['mariadbd', '--version']),
    ('mysql', 'mysql:8.4', ['mysqld', '--version']),
    ('nginx', 'nginx:stable-bookworm', ['nginx', '-v']),
    ('composer', 'composer:2', ['composer', '--version', '--no-ansi']),
]:
    subprocess.run(['docker', 'pull', tag], check=True)
    info = json.loads(run('docker', 'image', 'inspect', tag))[0]
    digest = info['RepoDigests'][0]
    result = subprocess.run(['docker', 'run', '--rm', '--network', 'none', '--entrypoint', command[0], digest, *command[1:]], check=True, text=True, capture_output=True)
    images[key] = {'discovery_tag': tag, 'image': digest, 'image_id': info['Id'], 'version': (result.stdout + result.stderr).strip()}

manifest = {'schema': 1, 'resolved_date': '2026-10-05', 'platform': 'linux/amd64', 'wordpress': {'version': '7.1.2', 'repository': 'https://github.com/WordPress/wordpress-develop.git', 'commit': '0a106cde38df869e196a83eb4975ba38a4e5837b'}, 'images': images, 'packages': {}}
for graph, packages in [('quality', {'phpunit/phpunit': '12.', 'phpstan/phpstan': '2.', 'wp-coding-standards/wpcs': '3.', 'squizlabs/php_codesniffer': '3.'}), ('wp-tests', {'phpunit/phpunit': '9.6.', 'yoast/phpunit-polyfills': '1.'})]:
    requires = {'php': '>=8.3 <8.6'}
    for package, prefix in packages.items():
        versions = fetch('https://repo.packagist.org/p2/' + package + '.json')['packages'][package]
        candidates = [p['version'].lstrip('v') for p in versions if re.fullmatch(r'v?\d+\.\d+\.\d+', p['version']) and p['version'].lstrip('v').startswith(prefix)]
        requires[package] = max(candidates, key=lambda v: tuple(map(int, v.split('.'))))
    manifest['packages'][graph] = requires
    dest = out / 'tools' / graph
    dest.mkdir(parents=True)
    config = {'name': 'coagmentator/' + graph, 'description': 'Isolated development-only C01 tooling', 'license': 'AGPL-3.0-or-later', 'require-dev': requires, 'config': {'platform': {'php': '8.3.0'}, 'allow-plugins': False, 'sort-packages': True}}
    (dest / 'composer.json').write_text(json.dumps(config, indent=2) + '\n')
(out / 'manifest.json').write_text(json.dumps(manifest, indent=2) + '\n')
print(json.dumps(manifest, indent=2))
(out / 'Dockerfile').write_text('FROM ' + images['composer']['image'] + ' AS composer\nFROM ' + images['php84']['image'] + '\nRUN apt-get update && apt-get install -y --no-install-recommends git unzip libonig-dev libxml2-dev && docker-php-ext-install -j2 mysqli mbstring dom xml xmlwriter && rm -rf /var/lib/apt/lists/*\nCOPY --from=composer /usr/bin/composer /usr/local/bin/composer\n')
