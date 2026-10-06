"""Additional TLS destination and host bootstrap for the focused C03B phase."""
from pathlib import Path
import shutil
import subprocess

root = Path('.runtime/wordpress/src')
config = root / 'wp-config.php'
text = config.read_text()
old = "define( 'WP_HOME', 'https://wordpress.test' );\ndefine( 'WP_SITEURL', 'https://wordpress.test' );"
assert text.count(old) == 1
config.write_text(text.replace(old, "require '/workspace/tests/fixtures/c03b-bootstrap.php';"))
for name in ['c03b-sink.php', 'c03b-observe.php']:
    shutil.copyfile(Path('tests/fixtures') / name, root / name)
tls = Path('.runtime/tls')
subprocess.run(['openssl', 'req', '-newkey', 'rsa:2048', '-nodes', '-subj', '/CN=redirect.test', '-keyout', str(tls / 'redirect.key'), '-out', str(tls / 'redirect.csr')], check=True, capture_output=True)
(tls / 'redirect.cnf').write_text('subjectAltName=DNS:redirect.test\nextendedKeyUsage=serverAuth\nbasicConstraints=CA:FALSE\n')
subprocess.run(['openssl', 'x509', '-req', '-days', '2', '-in', str(tls / 'redirect.csr'), '-CA', str(tls / 'ca.crt'), '-CAkey', str(tls / 'ca.key'), '-CAcreateserial', '-extfile', str(tls / 'redirect.cnf'), '-out', str(tls / 'redirect.crt')], check=True, capture_output=True)
(tls / 'redirect.key').chmod(0o600)
print('C03B disposable host bootstrap and separate redirect certificate prepared.')
