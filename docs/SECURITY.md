# Security Model

Coagmentator translates natural-language requests into privileged WordPress actions. Treat malformed input, stolen credentials, hostile content and mistaken requests as normal threat conditions. Detailed Gate 1 controls are a human-review candidate, not implemented protections.

## Invariants

1. WordPress credentials never reach AI clients. OAuth and Application Passwords are independent boundaries; no token passthrough.
2. Every protected request authenticates, and every operation enforces scopes/configuration at MCP plus native and custom capabilities inside WordPress. Never grant Administrator for convenience.
3. Expose only the 21 documented operations, with closed, bounded schemas. No arbitrary execution or generic administrative proxy.
4. Service credentials are usable only through the bridge route allowlist enforced by a must-use guard. Block native REST, XML-RPC, batch, Abilities, self-management and alternate authentication bypasses.
5. Bind site, operator, resource type and request identity through configuration, envelopes, versions, approvals and receipts. Inputs cannot select destinations or privileged identities.
6. Write families start disabled. Require independent, exact-intent, expiring human approval for all designated high-impact operations. Service credentials can never approve their own requests.
7. All mutations have a durable deduplication key; existing-target mutations also have a version precondition. Never blindly retry a write with an unknown outcome or reclaim execution ownership after side effects may have begun.
8. Mutation success means verified readback of requested and preserved state. Partial, transformed and unknown writes are errors with accurate evidence, never transport-derived success.
9. Reads return authorized stored data, without executing shortcodes, rendering dynamic blocks or fetching content URLs. WordPress text is untrusted data, not tool instructions.
10. No permanent deletion tool. Refuse Trash when WordPress would delete permanently, and disclose native Trash retention. Revisions are limited-field recovery, not a complete backup.
11. Media accepts bounded raster bytes, re-encodes and strips metadata, rejects paths/URLs and unsafe formats, and requires approval because uploads can be public immediately.
12. Metadata is an exact logical-key allowlist. Basic SEO is opt-in with one verified renderer; unsupported vendors fail closed.
13. Fail closed on missing guard, broken configuration, failed authorization, TLS verification, quotas, and pre-write journal/audit failure. A post-write failure is reported as uncertain or partial.
14. No secrets or raw request bodies in logs, work notes, fixtures, URLs, exceptions or tool `_meta`. Separate private approval storage from audit logs.
15. Safety does not depend on model compliance, tool annotations or client confirmation UI. These may improve usability but cannot grant authority.

## Prohibited surface

No shell, WP-CLI, SQL, PHP/eval, generic REST proxy, dynamic Abilities execution, unrestricted filesystem access, `wp-config.php` reading, plugin/theme install/edit/activation, user/credential administration, arbitrary options/meta, hard deletion, bulk operations, custom post types, multisite or multiple-site routing in the MVP.

Future privileged features require a new documented capability/contract/threat review. They are not extensions to a catch-all tool.

## Credential and network controls

Use the dedicated service-user model in [CAPABILITIES.md](CAPABILITIES.md), including its mandatory guard. Store secrets outside the repository/web root, with service-only access and protected encrypted backups. Require verified HTTPS, no credential-bearing redirects, pinned bridge/issuer destinations, and trusted-proxy handling. Application Password rotation is at least every 90 days; suspected compromise triggers immediate revocation. MCP access tokens last at most five minutes and support local emergency disable. Details and client/platform responsibilities are in [AUTHENTICATION.md](AUTHENTICATION.md).

No tool can fetch a user-supplied URL. Provider discovery and optional CIMD fetching remain SSRF surfaces and must use fixed/approved destinations, bounded requests and connection-time address checks. Operator-configured private bridge networks are explicit deployment bindings, not exceptions a tool caller may invoke.

## Audit, payloads and availability

Audit only timestamp, operation, validated correlation/request/receipt IDs, site/actor/service identity, resource IDs, outcome/code, before/after hashes, changed field names and approval decision ID. Audit hashes are operational evidence, not anonymization. Do not log titles, bodies, metadata values, filenames, media bytes, raw tokens/headers, passwords, SQL or stack traces. Application Password UUIDs may be recorded in restricted operator audit for credential attribution but are omitted from client results.

Retain protected audit/journal records 90 days, with operator-controlled export/retention outside the tool surface. The immutable original receipt is returned on replay; later resource changes do not rewrite that evidence. Restrict access and bound storage. The system is not a cryptographically signed audit service.

Exact payloads temporarily needed for human review are sensitive private application data. Encrypt them at rest using a bridge-host secret outside the database, show only to an authorized human, escape text and avoid rendering submitted HTML, and remove payloads after execution/rejection/expiry within 24 hours. The approval UI uses cookie/CSRF protection and frame restrictions. It never places payload data or bearer approval tokens in URLs.

The contract's byte/pixel/depth, rate, timeout, concurrency and storage limits are mandatory. Maximum JSON nesting is 20, pending approvals 20 per binding, journal rows 10,000 per binding, and pending payload storage 100 MiB per binding. On exhaustion return a limit error without executing a write; never evict active deduplication records to make room. Alert the operator through protected operational logs, not a new messaging tool.

## Verification limits and incident posture

Readback proves a particular observed state, not perpetual correctness. Bridge locks do not serialize native WordPress writers; use controlled editing windows for live changes. WordPress hooks may emit emails, syndication or other external effects which cannot be rolled back. Pending/private text or uploads can contain sensitive information even when technically authorized. Compromised WordPress code/database can forge state and receipts; a compromised MCP host can disclose its readable data and mislead a client.

On suspected compromise, disable the binding, revoke the relevant credentials, stop new writes, preserve protected evidence, and inspect WordPress/journal state through a separate trusted path before recovery. Do not automatically restore, delete or replay uncertain operations. Backups and revision availability must be validated in later gates, not assumed by the receipt contract.

See [THREAT-MODEL.md](THREAT-MODEL.md) for 28 concrete scenarios, residual risks and assigned verification gates. Security acceptance in Gate 1 concerns completeness of this design only.
