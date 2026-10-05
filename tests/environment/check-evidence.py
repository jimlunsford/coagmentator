"""Refuse publication if disposable secret values appear in test artifacts."""
from pathlib import Path
import json
import subprocess
root = Path('.runtime')
for item in (root / 'evidence').glob('*'):
    data = item.read_bytes()
    assert item.suffix in ['.xml', '.json'], 'Unapproved artifact type'
    assert b'PRIVATE KEY' not in data, 'Private key in evidence'
    for name in ['database-password', 'root-password']:
        secret = root / name
        if secret.exists():
            assert secret.read_bytes() not in data, 'Disposable password in evidence'
manifest = json.loads(Path('tests/environment/manifest.json').read_text())
manifest['candidate_sha'] = subprocess.check_output(['git', 'rev-parse', 'HEAD'], text=True).strip()
manifest['candidate_tree'] = subprocess.check_output(['git', 'rev-parse', 'HEAD^{tree}'], text=True).strip()
(root / 'evidence/environment.json').write_text(json.dumps(manifest, indent=2) + '\n')
print('Evidence allowlist and secret scan passed.')
