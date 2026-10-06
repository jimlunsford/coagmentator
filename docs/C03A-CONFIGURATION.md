# C03A Authentication Evidence and Configuration Foundation

C03A implements only identity evidence and closed host configuration. C03 remains IN PROGRESS and unaccepted. Transport/proxy enforcement (C03B), operational admission/audit/concurrency (C03C), the full C03 matrix (C03D), capabilities (C04) and real bridge read routes remain deferred. No runtime `ReadController` exists.

## Evidence and authority

The normal plugin registers an observer after the independent MU guard when that guard is present. `Authentication_Evidence` consumes core's `application_password_did_authenticate` event with exactly two arguments, retaining only the authenticated WordPress user ID and credential `uuid`. It never reads `app_id` as authority, registers for a plaintext password argument, or retains a credential record/hash/header. Multiple success events or a failed-auth event invalidate its request-local state.

Each `Bridge_Identity::allows()` call obtains fresh evidence. It requires the accepted MU guard's `admits()` result, equality with the current WordPress user, current core Application Password availability, and a fresh credential existence/UUID lookup after invalidating the user-meta cache. The guard's sticky failure, protected-ID registry, credential allowlist and handler checks remain conjunctive. A role, marker, cookie, nonce, injected user, header or claimed actor cannot replace an authentic event. The guard is unchanged.

The identity result is a boolean, not a reusable authorization token. Evidence cannot be serialized/unserialized. PHP request lifetime owns static event state; after the MU guard finishes its cycle, a captured event cannot pass a fresh identity check. The independent guarded REST server still owns external-request scope, object/depth checks and dispatch authorization. This foundation does not authorize a dispatch, supply capabilities, validate a request envelope or return a bridge success.

## Operator-only host file

`Feature_Config::load()` accepts no arguments. It reads only `COAGMENTATOR_FEATURE_CONFIG`, defined out of band, and independently reloads `COAGMENTATOR_GUARD_REGISTRY`. There is no HTTP configuration-path selection or fallback to request data/options. The configuration file is a regular readable absolute local file outside the web root and repository/package roots. Reading is bounded to 16 KiB plus one overflow byte. Failure returns null without reflecting file contents or paths.

Version 1 uses compact canonical JSON in the field order below, with unescaped slashes; a trailing newline is permitted. Decode/re-encode equality rejects duplicate member names, noncanonical numeric representations and unsupported formatting. This is a host file format, separate from future REST JSON decoding. Unknown/missing fields and incorrect primitive types reject the entire policy. Configuration contains no reusable Application Password or WordPress password.

| Field, in canonical order | Implemented rule |
| --- | --- |
| `version` | Integer 1 |
| `guard_api` | Integer 1, matching the accepted C02 interface |
| `enabled` | Boolean; false denies the binding |
| `site_id` | Canonical lowercase UUIDv4 |
| `actor_id` | 1 to 64 ASCII letters, digits, underscore or hyphen |
| `service_user_id` | One positive safe-integer active WordPress service ID |
| `protected_user_ids` | Unique list of 1 to 32 positive safe integers, including the active ID; each must also be protected by the independent registry. Additional registry IDs may remain denial-only |
| `credential_uuids` | Unique list of one or two canonical UUIDv4 values; each must be listed by the independent registry |
| `rotation` | Null for one UUID; for two, exactly `started_at` then `expires_at`, integer Unix seconds, with start at or before now, exclusive expiry after now, and interval at most 86,400 seconds |
| `home_origin` | Canonical lowercase HTTPS DNS origin; no userinfo, query, fragment or path. Optional nondefault port must be in range |
| `home_path` | Canonical slash-terminated installation path, at most 256 bytes, with ASCII alphanumeric/underscore/hyphen segments |
| `bridge_origin` | Exactly the home origin |
| `bridge_path` | Home path plus `wp-json/coagmentator/v1`, without a trailing slash; identity use also requires agreement with the guard's pinned REST prefix |
| `private_reads` | Boolean, represented only; no private-read capability implementation |
| `read_operations` | Unique subset of the nine accepted Gate 2 read names. No writes, `get_mutation`, generic dispatcher or unknown operation |
| `policy_version` | Positive safe integer |
| `approval_profile` | Exactly `strict` or `trusted_single_operator`; neither can enable writes in this gate |
| `writes_enabled` | Must be boolean false |
| `storage` | Exactly `cursor_key_file`, `audit_directory`, `admission_directory`, in that order, each an absolute bounded host path reference |

Storage references reject relative paths, traversal, ambiguous separators, URI wrappers, query/encoded/control characters, and lexical/resolved containment inside excluded roots. Existing ancestors are resolved to catch symlink aliases even when the referenced child does not yet exist. Parsing does not create/open future cursor/audit/admission storage, validate its locking semantics or claim operational readiness. Ownership/permissions, storage preflight and effective limit enforcement remain C03C work. Direct-TLS/trusted-proxy fields and transport checks remain C03B work; this schema cannot silently accept them before their reviewed implementation.

Every identity use rechecks the absolute rotation interval. Expired overlap denies both credentials until an operator replaces the policy with the remaining single approved UUID and revokes the retired credential through a trusted path. There is no rolling extension, auto-selection or credential-management endpoint.

## Focused verification boundary

Pure tests cover closed schema/type/UUID/identity/profile/path/rotation validation. Real disposable HTTP tests invoke the normal identity foundation, record safe booleans/counters, and prove both positive identity admission and denials. The installed synthetic controller exists only in the disposable test copy, always returns an error, and reads no editorial content. Existing C02 preflight and complete one-lane guard regression run before C03A's disposable overlap credential is created.

C03A's workflow runs unit/lint on PHP 8.3, 8.4 and 8.5; PHPStan, WPCS and both locked Composer audits on PHP 8.4; and integration/HTTP on PHP 8.4 with MariaDB 10.11 only. Existing C01/C02 pins, locks, workflows, MU code and assertions are unchanged. The work note owns exact candidate/run/result and cleanup evidence. No production access or provisioning is implied.
