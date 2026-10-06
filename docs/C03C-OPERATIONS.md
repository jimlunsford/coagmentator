# C03C Admission, Concurrency and Read Audit

C03C is an implemented, partial/unaccepted candidate blocked by final disposable-directory cleanup failure in its single focused CI run. It is not ready for acceptance review. C03 and Gate 2 remain IN PROGRESS. C03A and C03B remain ACCEPTED. C03D full-matrix verification, C04, real bridge read routes and Gate 3 are not started. See the [C03C handoff](work-notes/2026-10-06-gate-2-c03c-admission-audit-concurrency.md) for executed evidence and current review readiness.

## Boundary and ordering

The independent C02 MU package is unchanged. The normal package loads infrastructure definitions but registers no read controller or route. The test controller is copied only into the disposable WordPress installation and always returns a failure without content/site data.

The host must explicitly call `Preauth_Admission::bootstrap()` alongside the accepted C03B early transport bootstrap, before `wp-settings.php`. This accounts for bridge ingress before WordPress can authenticate or reject a credential. It reads only C03B's validated canonical immediate `REMOTE_ADDR`; forwarded-for/Forwarded values never select a bucket. Broad raw-path classification under the host's fixed REST prefix only adds denial. It supplies no route, identity or capability authority. It leaves unrelated public/human paths alone. Missing/failed preauth accounting cannot pass `Read_Operation` even when authentication later succeeds. Early failure is a fixed generic 429/no-store response with Retry-After 60, without identity/config/schema detail. C05 still owns normalized envelopes and exact response projection.

`Read_Operation::run()` first rechecks `Bridge_Identity`, including C02 admission, authentic live C03A identity/configuration and C03B transport, then requires current-request preauth evidence. It starts a deadline, consumes authenticated rate, obtains a read slot, validates/preflights audit capacity and durably appends `started` before entering the callback. The callback boundary is reserved for later capability/read validation; it does not replace authorization. A `returned` or `error` record follows. Raw plugin exceptions are discarded. Every ordinary exit releases the slot through `finally`, including audit or callback failure. The slot spans the complete synchronous protected operation, including final audit; later callers must include their response construction and all protected work inside that scope. OS descriptor cleanup covers process exit, without a claim of catching OOM or SIGKILL.

## Persistent rates and slots

Authenticated accounting uses `read-` plus SHA-256 of the configured site UUID. Envelope claims, credential rotation and request headers cannot select another key. The single-binding store permits only one read-counter file. Each minute window is `intdiv(trusted_unix_seconds, 60)`: counts 1 through 60 pass and 61 denies. There is no rolling reset on PHP worker restart.

Preauth uses `peer-` plus SHA-256 of the canonical immediate peer. Counts 1 through 120 pass and 121 denies. At most 1,024 peer files exist. Creating a new bucket at capacity may retire one fully validated expired bucket under the same control lock; active accounting is never evicted. Full active capacity denies new peers. Existing buckets remain usable within their ceiling. Behind a trusted reverse proxy, its immediate peer address is deliberately the bucket identity, not a forwarded client address.

Counters are canonical JSON in exact `window,last,count` order with integer types and a trailing newline, at most 128 bytes. Duplicate/noncanonical/truncated/oversized/empty state denies, never resets to zero. A separately locked persistent wall-clock high-water file rejects backwards time across workers and restarts. A fixed empty `control.lock` serializes bounded accounting updates with LOCK_EX|LOCK_NB. Contention itself fails closed instead of sleeping/queueing. Exact writes, truncate, flush and fsync are checked; partial writes poison subsequent parsing rather than fabricate unused capacity.

Four fixed empty `slot-0` through `slot-3` files provide nonblocking flock ownership. No lease, timestamp, expiration, unlink, age reclamation or native WordPress writer lock exists. The fifth simultaneous holder fails immediately. This is a single PHP host design, not distributed coordination.

## Trusted local storage setup

C03A's closed configuration schema and version are unchanged. Its `admission_directory` and `audit_directory` are the only storage references. New getters preserve the validating factory's excluded roots; they do not accept HTTP paths. No upper-limit configuration was added.

Directories must already exist through trusted local setup, outside web/code roots, with exact canonical absolute paths, owner equal to the PHP worker UID and mode 0700. Ancestors must be root/worker-owned and not group/world writable, except a root-owned sticky temporary ancestor protecting the private child. Symlink aliases, wrappers, non-directory paths and unknown mounts deny. Files must be regular, same opened/path inode and device, singly linked, owned by the worker, mode 0600. Check before opening to refuse symlinks/FIFOs, then compare descriptor/path facts. This does not sandbox malicious code running as the trusted worker UID.

Linux mount metadata is read with a 1 MiB bound. Only ext4, XFS, Btrfs and a local container overlay are supported. Tmpfs, network/distributed/unknown mounts fail readiness. An operator must verify that an overlay's backing store is local and that the intended persistent volume survives host/container replacement. A filesystem name cannot attest to a malicious host or a remote backing configuration. No DB/options/transients/object-cache/process-memory fallback exists. POSIX UID inspection and reliable local flock are required; unavailable support fails closed.

Trusted setup creates these fixed files before HTTP, never reinitializing an existing store:

| Store | Initial files | Bounds |
| --- | --- | --- |
| Admission | Empty `control.lock`, `slot-0` through `slot-3`; `clock` containing `0` plus newline | 1,031 files maximum, including one binding counter and 1,024 peer counters; counter 128 bytes, clock 13 bytes, locks empty |
| Audit | Empty `control.lock`, empty `events`; `clock` containing `0` plus newline; `state` containing `Read_Audit::fingerprint('')` | Four files; events 8 MiB; fingerprint 100 bytes; clock 13 bytes; lock empty |

Before enabling the host boundary, trusted local setup must exercise each lock with separately opened descriptors and separate processes: second holder must lose immediately, and a later process must acquire after release/termination. Disposable setup performs descriptor probes; L01 supplies the independent-process proof. Missing fixed files deny instead of HTTP recreating them. Only bounded hashed counters can be created during requests, exclusively under the control lock with a private umask. Installation/provisioning and production scheduling remain separate work.

## Deadline

`Read_Deadline` uses server `hrtime(true)` and an immutable 15,000,000,000 ns budget. `remaining()` rejects expiration, invalid/overflowing or backwards ticks and latches failure. Each instance is request-local. A trusted-code-only clock seam supports deterministic boundary tests; no request timestamp, configuration field or client clock extends the deadline. The wrapper checks before and after the callback; downstream C04+ adapters must pass the remaining budget to bounded IO and check during work. This is a deadline foundation, not asynchronous interruption of arbitrary blocking PHP or a claim to catch an uncatchable host failure.

## Audit and retention

Records contain exactly four fields: trusted Unix `timestamp`, fixed read `operation`, configured `site_id`, and fixed lifecycle `outcome` (`started`, `returned`, `error`). Each canonical JSON line is at most 256 bytes. No generic metadata map exists. Actor, service user, credential UUID, correlation and resource IDs are intentionally omitted at this stage, as are every body/secret/header/error/SQL/path/email/title/content/media field. The sink is protected operator evidence, never client/model output and never a mutation journal.

The audit control lock protects the complete preflight/append operation. All retained records are bounded and schema-validated; the size/SHA-256 state detects truncation, partial writes or append/maintenance interruption. It is corruption detection inside the host trust domain, not a cryptographic attestation against that host. Mismatches fail closed and require trusted reconciliation, never automatic state repair.

A start requires space for five maximum-sized records: itself plus the maximum four outstanding slot-holder completions. Slots remain held during audit writes. Capacity denial occurs before target execution. Evidence still inside retention is not removed to create room. Normal writes append; retention compacts only records at least 7,776,000 seconds old (90 days), preserving exact newer lines. Every append enforces this cutoff. `maintain()` performs the same locked validation/expiration without inventing an operation record, for trusted local scheduled maintenance during idle periods. Operators must schedule maintenance to enforce wall-clock retention even when no requests arrive; no HTTP export/rotation/reset endpoint or scheduler is installed by this foundation. A full store remains unavailable until evidence expires or an independently reviewed operator action resolves it.

Failures after a read callback still deny the request and release its slot. Reads have no editorial side effects to undo. No Gate 3 replay, tombstones, idempotency or mutation admission is implemented.

## Verification ownership

`LimitAdmissionTest` owns deterministic L01 rate/window/clock/storage/audit cases, independent-process restart persistence, four genuinely simultaneous slot holders, immediate fifth rejection and SIGKILL lock release. It also owns the E02 audit schema/seeded-marker subset. `OperationalAdmissionTest` adds actual WordPress authentication plus C03B transport, early invalid-auth 120/121, authenticated 60/61, four simultaneous PHP-FPM callback holders, fifth rejection, later acquisition, six ordinary/error requests and an all-four-free check, pre-target storage/audit denials and disclosure checks. Its synthetic callback always fails and returns no site data.

The focused workflow preserves all earlier tests and runs three unit/lint lanes plus only PHP 8.4/MariaDB 10.11 integration. C05 JSON/depth/Unicode/envelope/response-size/cursor work and the C03D six-lane matrix remain deferred. Exact counts, results and any blockers belong to the handoff.
