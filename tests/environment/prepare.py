"""Build only disposable local paths from the reviewed manifest."""
import json
import os
from pathlib import Path
import secrets
import shutil
import subprocess

root = Path(__file__).resolve().parents[2]
os.chdir(root)
man = json.loads(Path('tests/environment/manifest.json').read_text())
line = os.environ['C01_PHP']
engine = os.environ.get('C01_DATABASE', 'mariadb')
assert line in ['83', '84', '85'] and engine in ['mariadb', 'mysql']
runtime = Path('.runtime')
runtime.mkdir(exist_ok=True)
images = man['images']
(runtime / 'compose.env').write_text('DATABASE_IMAGE=' + images[engine]['image'] + '\nNGINX_IMAGE=' + images['nginx']['image'] + '\n')
subprocess.run(['docker', 'build', '--build-arg', 'PHP_IMAGE=' + images['php' + line]['image'], '--build-arg', 'COMPOSER_IMAGE=' + images['composer']['image'], '-t', 'coagmentator-c01-php', '-f', 'tests/environment/Dockerfile', '.'], check=True)
subprocess.run(['docker', 'run', '--rm', '--network', 'none', 'coagmentator-c01-php', 'php', '-r', 'exit(PHP_VERSION === "' + images['php' + line]['version'] + '" ? 0 : 1);'], check=True)
# Dependency downloads occur only during construction, before isolated runtime tests.
for graph in ['quality', 'wp-tests']:
    subprocess.run(['docker', 'run', '--rm', '-v', str(root) + ':/workspace', '-w', '/workspace/tools/' + graph, 'coagmentator-c01-php', 'composer', 'install', '--no-interaction', '--no-progress', '--no-plugins', '--no-scripts'], check=True)
    subprocess.run(['docker', 'run', '--rm', '--network', 'none', '-v', str(root) + ':/workspace', '-w', '/workspace/tools/' + graph, 'coagmentator-c01-php', 'composer', 'check-platform-reqs'], check=True)
wp = runtime / 'wordpress'
for destination, repository, revision in [
    (runtime / 'wordpress-develop', man['wordpress']['repository'], man['wordpress']['commit']),
    (wp / 'src', man['wordpress']['runtime_repository'], man['wordpress']['runtime_commit']),
]:
    subprocess.run(['git', 'init', str(destination)], check=True)
    subprocess.run(['git', '-C', str(destination), 'fetch', '--depth=1', repository, revision], check=True)
    subprocess.run(['git', '-C', str(destination), 'checkout', '--detach', 'FETCH_HEAD'], check=True)
    assert subprocess.check_output(['git', '-C', str(destination), 'rev-parse', 'HEAD'], text=True).strip() == revision
if os.environ.get('C01_MODE') == 'unit':
    raise SystemExit(0)
secret_directory = runtime / 'secrets'
secret_directory.mkdir(mode=0o700)
for name in ['database-password', 'root-password']:
    p = secret_directory / name
    p.write_text(secrets.token_urlsafe(32))
    # Host directory is private. Compose mounts only the required file read-only,
    # allowing the unprivileged database/FPM processes to read their own secret.
    p.chmod(0o444)
(runtime / 'database-init.sql').write_text("CREATE DATABASE wp_tests CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\nGRANT ALL ON wp_tests.* TO 'c01'@'%';\n")
shutil.copyfile('tests/environment/wp-config.php', wp / 'src/wp-config.php')
shutil.copyfile('tests/fixtures/http-probe.php', wp / 'src/c01-probe.php')
shutil.copytree('wordpress/coagmentator', wp / 'src/wp-content/plugins/coagmentator', dirs_exist_ok=True)
shutil.copytree('wordpress/mu-plugins', wp / 'src/wp-content/mu-plugins', dirs_exist_ok=True)
(runtime / 'guard-config').mkdir(exist_ok=True)
(runtime / 'guard-config/registry.json').write_text('{"version":1,"protected_user_ids":[],"credential_uuids":[]}')
tls = runtime / 'tls'
tls.mkdir(exist_ok=True)
subprocess.run(['openssl', 'req', '-x509', '-newkey', 'rsa:2048', '-nodes', '-days', '2', '-subj', '/CN=Coagmentator Disposable Test CA', '-keyout', str(tls / 'ca.key'), '-out', str(tls / 'ca.crt')], check=True, capture_output=True)
subprocess.run(['openssl', 'req', '-newkey', 'rsa:2048', '-nodes', '-subj', '/CN=wordpress.test', '-keyout', str(tls / 'server.key'), '-out', str(tls / 'server.csr')], check=True, capture_output=True)
(tls / 'extensions.cnf').write_text('subjectAltName=DNS:wordpress.test\nextendedKeyUsage=serverAuth\nbasicConstraints=CA:FALSE\n')
subprocess.run(['openssl', 'x509', '-req', '-days', '2', '-in', str(tls / 'server.csr'), '-CA', str(tls / 'ca.crt'), '-CAkey', str(tls / 'ca.key'), '-CAcreateserial', '-extfile', str(tls / 'extensions.cnf'), '-out', str(tls / 'server.crt')], check=True, capture_output=True)
for name in ['ca.key', 'server.key']:
    (tls / name).chmod(0o600)
print('Pinned environment constructed. Runtime credentials and TLS keys are excluded from artifacts.')
