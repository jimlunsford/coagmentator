# Shared Contracts, Version 1.0

Gate 1 design candidate, 2026-10-05. Normative design for the MCP server and WordPress bridge, not an implemented API. Human Gate 1 acceptance is pending. Tool inventory: [MCP-TOOLS.md](MCP-TOOLS.md). Authorization: [CAPABILITIES.md](CAPABILITIES.md). Failures: [ERRORS.md](ERRORS.md).

## Wire boundary

The bridge has one fixed POST route per tool, `/wp-json/coagmentator/v1/<tool_name>`, with exactly the 21 names in the inventory. This is an explicit route allowlist, not a generic action/REST dispatcher. Read routes are POST only to keep queries out of access logs; they do not mutate editorial data. Audit/access counters are incidental to reads.

MCP arguments follow the inventory. The MCP server creates this bridge JSON request after client authorization:

```json
{
  "contract_version": "1.0",
  "correlation_id": "e197fc08-9f9e-4135-a64d-7aef47122ba4",
  "site_id": "f3858c09-8c56-48fd-99d1-ab75862bb955",
  "actor_id": "operator-1",
  "arguments": {
    "ref": {"type": "post", "id": 42}
  }
}
```

`site_id` is removed from tool arguments and placed in the envelope; all other tool arguments are preserved. `site_info` has empty arguments, with the operator-pinned site ID still in the bridge envelope. `actor_id` is a locally configured opaque identifier (1..64 ASCII letters, digits, underscore or hyphen) derived from the validated `(issuer, subject)` binding, never a tool argument or an authorization grant. The bridge binds it to its configured service user/Application Password identity. A compromised MCP server can impersonate this actor; WordPress capabilities and independent approvals remain the ceiling.

Use UTF-8 `application/json`, HTTPS, Application Password Basic authentication, and `Cache-Control: no-store` on all protected responses. No redirects, content negotiation into HTML, compressed request bodies, JSON batches, duplicate JSON member names, or query parameters. Reject method/route mismatches before dispatch. Authenticate before detailed validation errors. Proxies must preserve `Authorization`; clients never resend credentials to a redirect target.

`contract_version` is an exact supported string, independent of the MCP protocol revision. Unknown versions fail closed. Additive fields require a documented minor contract revision and an explicit shared supported-version set; breaking changes require a new REST major namespace. There is no silent best-effort interpretation.

## Primitive types and bounds

| Type | Exact rule |
| --- | --- |
| `Id`, `IdOrZero` | JSON integer 1..9007199254740991; `IdOrZero` additionally allows 0. IDs never authorize access |
| `UUID`, `SiteId` | Canonical lowercase UUIDv4 string; IDs are random, not secrets. Site ID generated once by the bridge and pinned out of band before connection |
| `Timestamp` | UTC RFC 3339 string with whole seconds, `YYYY-MM-DDTHH:mm:ssZ` |
| `Digest`, `Version` | `sha256:` followed by exactly 64 lowercase hexadecimal characters; `Version` is the resource projection hash below |
| `ContentType` | `post` or `page` |
| `ContentStatus` | Read: `draft`, `pending`, `publish`, `private`, `future`, `trash`. Write targets: `draft`, `pending`, `publish`, `private`, subject to operation restrictions. Future and Trash are read-only |
| `Taxonomy` | `category` or `post_tag`, attached to posts only |
| `Title`, `TermName` | Plain text, 1..300 and 1..200 Unicode scalar values respectively; reject markup, control characters and whitespace-only strings |
| `Body`, `Excerpt` | Stored raw editor text, at most 1 MiB and 64 KiB UTF-8 respectively. Empty body is valid for drafts, invalid for publication. Reject NUL and invalid Unicode |
| `Slug` | 1..200 ASCII characters matching `[a-z0-9]+(?:-[a-z0-9]+)*`; no path, scheme, query, percent encoding, or implicit suffix changes for an explicit slug |
| `SearchText` | Plain text, 0..200 characters; no regex, SQL or query-language interpretation |
| `TermDescription` | Plain text, at most 2,000 characters |
| `AltText` | Plain text, at most 2,000 characters; empty string explicitly allows a decorative image |
| `PageLimit`, `Cursor` | Limit 1..50. Opaque authenticated cursor at most 2,048 characters, expires after 15 minutes |
| `ImageMime` | `image/jpeg`, `image/png`, `image/webp`; static images only |
| `SafeFilename` | ASCII basename, 1..120 characters, letters/digits/period/underscore/hyphen; first character alphanumeric; one final allowed extension `.jpg`, `.jpeg`, `.png`, `.webp`; reject `..`, extra extension segments, separators, hidden names and controls. WordPress still chooses the actual storage basename |
| `Base64` | Canonical RFC 4648 base64 with padding, no whitespace or data-URL prefix; decoded maximum 5 MiB, checked while decoding |

Request maximum is 2 MiB except `upload_media`, which permits 7 MiB including base64 overhead. Protected response maximum is 2 MiB. Lists are additionally capped at 1 MiB. Enforce decoded image maximum 25 million pixels, width/height each 10,000, and 20 seconds processing budget. Other operation deadline is 15 seconds; upload end-to-end deadline is 30 seconds. Lower host limits apply and are advertised by `site_info`; raising these design maxima needs a contract review.

Default rate ceilings at both server and bridge: 60 read calls/minute, 10 mutation submissions/minute, and 2 uploads/minute per authenticated binding; at most 4 active requests and 1 active write per binding. Count approval submissions and exact retries against admission limits. Apply a separate pre-auth IP ceiling of 120 requests/minute and reject excess concurrent work rather than queue indefinitely. Server/site totals default to the same ceilings in this one-operator MVP. These are protective limits, not billing or usage metering.

Maximum JSON nesting is 20; pending approvals 20, retained journal rows 10,000 and private pending-payload bytes 100 MiB per binding. At most 1,000 candidate objects are scanned per list request, with a continuation cursor when more remain. Quota exhaustion refuses new work without evicting protected deduplication records. Private approval payloads are encrypted at rest with a bridge-host key outside the database. Audit and journal retention is 90 days; payload expiry rules below are shorter.

## Shared shapes

All fields in the following shapes are present unless explicitly called optional. `null` is distinct from a missing input. Additional output fields are prohibited at this contract revision.

| Shape | Fields |
| --- | --- |
| `Success<T>` | `ok: true`, `contract_version: "1.0"`, `correlation_id: UUID`, `site_id: SiteId`, `data: T`, `receipt: Receipt \| null` (null for reads) |
| `Ref` | `type: ContentType`, `id: Id` |
| `ResourceRef` | `type: post \| page \| term \| media`, `id: Id`, `taxonomy: Taxonomy \| null` (non-null only for terms) |
| `Page<T>` | `items: T[]`, `next_cursor: Cursor \| null`. No total count |
| `ContentSummary` | `ref: Ref`, `status: ContentStatus`, `title: string`, `slug: string`, `modified_gmt: Timestamp`, `version: Version`, `public_url: string \| null` (only published content) |
| `Content` | All `ContentSummary` fields, plus `content_raw: Body`, `excerpt_raw: Excerpt`, `author_id: Id`, `parent_id: IdOrZero`, `date_gmt: Timestamp \| null`, `category_ids: Id[]`, `tag_ids: Id[]`, `featured_media_id: Id \| null`, `latest_revision_id: Id \| null` |
| `Term` | `id: Id`, `taxonomy: Taxonomy`, `name: string`, `slug: string`, `description: string`, `parent_id: IdOrZero`, `version: Version`. No usage count |
| `Media` | `id: Id`, `mime_type: ImageMime`, `title: string`, `alt_text: string`, `url: string`, `width: integer`, `height: integer`, `bytes: integer`, `stored_sha256: Digest \| null`, `parent: Ref \| null`, `version: Version`. Existing media may have null digest if not safely obtainable within limits; new uploads require one |
| `MetadataSnapshot` | `ref: Ref`, `version: Version` (parent's complete projection), `values: map<MetadataKey, string \| null>`. Null means key absent, empty string means stored empty value |
| `RevisionSummary` | `id: Id`, `parent: Ref`, `created_gmt: Timestamp`, `version: Version`, `available_fields: RevisionField[]` |
| `Revision` | All `RevisionSummary` fields plus `title: string`, `content_raw: Body`, `excerpt_raw: Excerpt` |
| `MutationResult` | `target: ResourceRef`, `version: Version`, `status: string`, `changed: boolean`. Status is content status, `active` for term, `inherit` for media. Fetch a resource separately for its complete current content |
| `MutationStatus` | `request_id: UUID`, `operation: ToolName`, `state: awaiting_approval \| rejected \| executing \| completed \| failed \| indeterminate`, `write_state: not_applied \| applied \| partial \| unknown`, `receipt: Receipt \| null`, `error: Error \| null`, `approval: ApprovalInfo \| null`, `updated_at: Timestamp` |

Content raw fields are stored source, including valid block comments, not rendered HTML. Do not run shortcodes, dynamic blocks, oEmbed, or remote URL fetches on reads. Strings and links from WordPress remain untrusted content, never instructions. Public URLs and media URLs are returned as data, never fetched by the bridge or MCP server. A plugin that exposes protected files must not be assumed compatible with public WordPress uploads.

`SiteInfo` contains exactly: `site_id`, `name` (plain text), `home_url` (canonical HTTPS), `timezone` (IANA name or WordPress fixed-offset string), `bridge_version` (implementation semver), `contract_versions: string[]`, `content_types: ContentType[]`, `taxonomies: Taxonomy[]`, `available_tools: ToolName[]`, `metadata_keys: MetadataKey[]`, `seo_mode: disabled | coagmentator_basic_v1`, `trash_retention_days: integer`, `limits: Limits`, `writes_enabled: boolean`, and `approval_required_operations: ToolName[]`. The last list includes unconditional approval tools; conditional live-content approvals remain in the inventory. `ToolName` is exactly a name in the 21-tool inventory; `MetadataKey` is exactly a key in the metadata table. No WordPress/PHP versions, plugins, user emails, server paths, or credential UUIDs are exposed.

The bridge computes available tools from its configuration and capabilities. The MCP server further intersects `available_tools` with its own configuration/scopes, filters `approval_required_operations` to that set, and ANDs `writes_enabled` with its own write switch. This is the only permitted successful-data narrowing, besides dropping already-unauthorized whole results; it cannot add a tool or elevate a flag. Receipts are never rewritten. `tools/list` discovery and step-up are described separately in [AUTHENTICATION.md](AUTHENTICATION.md).

`Limits` is a closed object of positive integer effective ceilings with exactly these keys and default maxima:

| Keys | Values, in the same order |
| --- | --- |
| `request_bytes`, `upload_request_bytes`, `response_bytes`, `list_response_bytes` | 2097152, 7340032, 2097152, 1048576 |
| `body_bytes`, `excerpt_bytes`, `media_bytes`, `image_pixels`, `image_dimension` | 1048576, 65536, 5242880, 25000000, 10000 |
| `page_limit`, `scan_items`, `json_depth`, `term_ids` | 50, 1000, 20, 100 |
| `read_per_minute`, `mutation_per_minute`, `upload_per_minute`, `preauth_per_minute` | 60, 10, 2, 120 |
| `concurrent_requests`, `concurrent_writes`, `operation_seconds`, `upload_seconds`, `image_processing_seconds` | 4, 1, 15, 30, 20 |
| `pending_approvals`, `journal_rows`, `pending_payload_bytes` | 20, 10000, 104857600 |

Other scalar/string/time bounds in this document are fixed contract constants, not remotely configurable limits.

Pagination sorts content by `(modified_gmt DESC, id DESC)`, media and revisions by `(id DESC)`, and terms by `(id ASC)`. The bridge signs cursors and binds them to site, actor, route, normalized filters, limit, last scanned sort key and expiry. Reauthorize each page. Filter authorization before selecting returned items; bounded scanning can yield a short/empty page with a next cursor. Never infer completion from `items.length`. These are live traversals, not snapshots; edits during traversal can move results and require a fresh search.

## Mutation input details

`ContentPatch` is a nonempty subset of `title`, `content_raw`, `excerpt_raw`, `slug`. Types above apply. No status, author, dates, parent, password, sticky, template, comment settings, metadata or relations can be smuggled into it. Omitted fields are preserved. Empty excerpt/body clears that field; title and slug cannot be cleared.

For draft creation with no slug, the bridge derives an ASCII slug from the title using WordPress sanitization, falling back to `draft-` plus the request UUID if that produces no valid `Slug`. It collision-checks with the intended post/page namespace, including unpublished items, and can append a bounded numeric suffix (at most 100 candidates, all within 200 characters). Core can otherwise leave a draft slug empty, so do not assume core generated one. An explicitly supplied slug is never silently suffixed. Publication requires a nonempty valid, noncolliding existing slug and must preserve it; an imported draft with no eligible slug needs a separate reviewed edit first. All checks are subject to the explicitly documented external-writer race limit.

`TermPatch` is a nonempty subset of `name`, `slug`, `description`, `parent_id`. Parent changes are category-only and must form an acyclic hierarchy. Terms and relations use validated integer IDs, never names that implicitly create a term. Relation arrays have at most 100 unique IDs and are treated as sets. Posts must have at least one category, preventing WordPress's default-category substitution from silently changing intent. Pages expose empty taxonomy arrays and reject assignment operations.

`RevisionField` is one of `title`, `content_raw`, `excerpt_raw`. Restore requires a nonempty unique subset and a saved revision of that exact parent. Apply only those source fields through the same validated content-update path as an ordinary edit. Do not blindly call unrestricted revision restoration: core/plugin restore hooks can also restore revisioned metadata. This MVP's restore operation deliberately does not trigger a broad metadata restore. Record the source revision separately from any resulting revision. Disabled/pruned revisions are an explicit unsupported/not-found result; never promise a revision will always exist.

## Metadata and basic SEO

| Logical key | Storage owned by bridge | Value | Enabled behavior |
| --- | --- | --- | --- |
| `editorial.note` | `_coagmentator_editorial_note` | Plain text, maximum 4,000 characters | Available with metadata access; editorial data, not a secret store; no front-end output |
| `seo.title` | `_coagmentator_seo_title` | Plain text, maximum 300 characters | Optional `coagmentator_basic_v1` mode overrides the document title for this single post/page |
| `seo.description` | `_coagmentator_seo_description` | Plain text, maximum 500 characters | Optional `coagmentator_basic_v1` mode emits one escaped description meta element for this single post/page |

`MetadataPatch` is a nonempty object containing only these logical keys, with string values or null to delete. No arbitrary key registration, regex/wildcard keys, serialized data, HTML, PHP objects, options, SEO templates, canonical URLs or robots directives. The bridge registers its storage keys with explicit types and authorization callbacks. Each set/delete also checks the corresponding WordPress meta capability with the actual storage key.

SEO mode defaults to disabled. A human operator may enable the bridge-owned basic renderer only after checking that the theme and SEO plugins do not also own those outputs. No automatic detection is sufficient proof of compatibility. It does not read/write Yoast, Rank Math, or any existing site's undocumented keys; it does not add an indexable database or vendor adapter. If existing SEO ownership prevents this mode, the SEO keys remain unsupported until a separately reviewed adapter is designed. This is an explicit MVP compatibility limit, not an assumption about JimLunsford.com. Core editorial metadata still works. Gate 3 tests output escaping and single-owner rendering; Gate 6 must resolve target-site SEO compatibility before claiming SEO acceptance.

## Safe content and media handling

Never grant `unfiltered_html` or `unfiltered_upload`. Validate raw content with WordPress's allowed HTML policy and a bridge block/shortcode policy. Reject input that would lose meaningful content through sanitization; return field/reason codes without echoing a large or malicious payload. Preserve safe existing block source on reads. Writes support plain safe HTML and the static core blocks `core/paragraph`, `core/heading`, `core/list`, `core/list-item`, `core/quote`, `core/image`, `core/separator`, `core/code`, and `core/preformatted`. Block attributes must follow the registered block schema, with safe URL protocols and no handlers/scripts. Unknown or dynamic blocks, embeds, executable shortcodes and server-side render instructions make a body write/restore unsupported. Title-only changes may preserve an unsupported existing body unchanged. Publishing a body that fails this policy is refused. These limits must be visible in tool descriptions.

Image uploads are bytes only. Require actual decoder format, MIME, filename extension, size, dimensions and input digest to agree. Decode/re-encode static raster images into the same allowed format in a bounded worker, strip EXIF/location metadata, use generated safe storage names through WordPress media APIs, and disallow SVG, GIF/animation, archives, documents and executable formats. Re-encoding changes bytes, so input and stored digests are distinct. Do not trust MIME headers or merely rename an extension. No tool controls a directory or filesystem path. A failed write can leave a partial attachment/file; record it, never claim rollback or perform unapproved broad cleanup. An operator resolves residue in WordPress.

Prepare the sanitized image privately before human approval; bind its digest, dimensions and MIME to the approval alongside the original request hash. Publish those approved sanitized bytes after checking their digest again, rather than producing a different preview/artifact after approval. Both retained input and sanitized bytes count against the private-payload quota. Generated WordPress subsizes must stay under the same decoder/processing constraints; if host settings cannot ensure this, disable uploads pending operator correction.

## Versions and concurrency

Versions are SHA-256 of UTF-8 RFC 8785 canonical JSON for a fixed projection, including `contract_version` and `site_id`. Numbers are safe integers, strings are exact stored Unicode (no Unicode normalization), set-valued ID arrays are sorted, and absent values use explicit null. Use the exact field names in the shapes above.

- Content projection: `ref`, `status`, `title`, `slug`, `content_raw`, `excerpt_raw`, `author_id`, `parent_id`, `date_gmt`, `modified_gmt`, `category_ids`, `tag_ids`, `featured_media_id`, and `metadata` (all enabled metadata keys, including nulls). Exclude URLs and revision IDs.
- Term projection: every `Term` field except `version`.
- Media projection: every `Media` field except `version` and `url`; include stored digest if known.
- Revision projection: `id`, `parent`, `created_gmt`, `title`, `content_raw`, `excerpt_raw`; exclude `available_fields` and `version`.

Hash domain is `{contract_version, site_id, resource_type, state}` where `resource_type` is `post`, `page`, `term`, `media`, or `revision`, and `state` is that projection. Clients treat versions as opaque. Hashes are not authorization tokens or proof against a compromised server.

For existing-target writes, authenticate/authorize, acquire a bounded bridge resource lock, read the current projection freshly, reject stale `expected_version`, check active WordPress editing locks, then recheck immediately before write. Lock key is site plus resource type plus ID (taxonomy for terms). Creating terms also locks taxonomy plus normalized name/slug. A binding-level write lock enforces the concurrency ceiling. Never break another user's editor lock.

These locks serialize Coagmentator writers only. Native WordPress editors, cron and third-party plugins do not honor bridge locks. Optimistic checks and readback detect many races but cannot guarantee an atomic compare-and-swap against every external writer. An external write in the final check/write window can still be overwritten. Live acceptance therefore requires an operator-controlled editing window; the receipt attests to a bounded observation, not perpetual state or serializable isolation. Stronger global coordination is a future architecture change, not an undocumented promise for Gate 3.

## Approval boundary

All writes start disabled in server and bridge configuration. A trusted human enables narrowly selected operation families and service capabilities out of band. Draft edits, new drafts, new terms, and non-live relations/metadata may then run within that standing authorization. It is a documented exposure to prompt-driven misuse, not proof of per-call human intent.

Always require an independent approval for publication, Trash, term updates, media upload, revision restore, and any update of `publish` or `private` content (including metadata, terms and featured images). The bridge stores the exact canonical payload privately and returns `APPROVAL_REQUIRED` with `ApprovalInfo`:

`{id: UUID, review_url: HTTPS URL, state: pending | approved | rejected | expired, expires_at: Timestamp}`.

The review URL uses the configured WordPress admin origin and path `/wp-admin/admin.php?page=coagmentator-approvals&approval_id=<UUID>`. Only that page selector and opaque record ID are allowed; the MCP server validates the origin/path/query shape before returning the link. It is not a bearer approval token and contains no requested content, secret or return URL. Visiting it cannot approve. A human with a separate WordPress cookie session, `coagmentator_approve`, and the underlying operation capabilities reviews the site, target/status, exact diff or media preview, public effects, and expiry. Explicit POST with WordPress CSRF nonce records approval. Approval lifetime is 10 minutes. The service user never has approval capability or interactive login access. MCP tools cannot approve, mint an approval, or supply a `confirmed` override. Client-side confirmations are helpful but not substitutes.

Approval binds site, service user, actor, operation, request ID, payload hash, all preconditions, policy version, and expiry. The payload hash covers the complete normalized arguments including `requested_at`; base64 bytes may be represented by their verified input digest in the hash, but no other mutable input is omitted. Approval is invalidated by changed arguments, resource version, scope/capability loss, policy change or expiry. Retry the same exact mutation after approval; do not generate a new ID. The bridge atomically claims the approval and journal execution right together before executing. No automatic execution occurs just because a human approved. Replays after successful execution return the original receipt. Declines/expiry do not mutate editorial data.

## Deduplication and uncertain outcomes

`request_id` is the idempotency key, not the per-attempt `correlation_id` or JSON-RPC ID. Uniqueness key: `(site_id, service_user_id, actor_id, request_id)` across all operations. A stored canonical request hash includes operation, arguments and preconditions. A reused key with any changed value is `IDEMPOTENCY_CONFLICT`. Reauthorize every retry before returning evidence.

Before side effects, reserve the key durably with a unique constraint in a bridge-owned journal; use conditional state transitions with an execution owner token. Record execution intent before calling WordPress. Concurrent duplicate requests cannot both own execution. An `executing` record cannot be stolen after a timeout. If a worker dies after intent was recorded, mark `indeterminate`; reconciliation may read state but must never blindly run that write again. Failed no-effect validation can be stored as terminal; fixing input requires a new request ID. `awaiting_approval` is the only normal pre-execution resumable state.

Terminal records store the original `MutationResult`, receipt or normalized error, and request hash for 90 days. Private approval payloads/media are deleted after execution, rejection or expiry, at most 24 hours after submission; they never enter audit logs. A first-seen request must have `requested_at` within the preceding 24 hours and no more than 60 seconds in the future. Thus exact replay after journal expiry is rejected as too old. Reusing a forgotten ID with a new timestamp is new intent and is not covered by deduplication. Fresh IDs with identical content are not automatically deduplicated.

`get_mutation` never executes, repairs, approves or retries anything. Its current authorization check reuses the original operation's access requirements, not its old status/version/editor-lock/approval preconditions; a successfully published or trashed item must remain reconcilable. If the target is gone, require the recorded native primitive requirements as well as current site/actor/scopes/custom policy, and disclose only sanitized status/receipt. Unknown/expired lookup does not prove non-execution. The MCP server automatically retries reads at most twice with bounded backoff; it never automatically repeats a mutation. After a transport loss it can look up the original key and return the recorded outcome or `WRITE_OUTCOME_UNKNOWN`. The operator/client may explicitly resubmit the exact key when the record permits it. Disconnect/cancellation after a write starts is not rollback.

WordPress hooks, files, notifications and database changes do not form one atomic transaction. Validate all requested fields and permissions before any mutation, but represent partial/unknown outcomes honestly. No automatic compensating publication, deletion, rollback or second upload. Storage and audit unavailability fail closed before a write; loss after a write makes the outcome indeterminate. The journal is bridge-owned persistent storage with narrowly prepared internal queries, never exposed SQL or direct edits to core content tables.

## Receipts and readback

`Receipt` has exactly these fields:

| Field | Meaning |
| --- | --- |
| `receipt_id: UUID`, `request_id: UUID`, `correlation_id: UUID` | Bridge receipt, stable operation key, and original execution-attempt correlation. A replay envelope has a new correlation; its receipt retains the original |
| `site_id: SiteId`, `actor_id: string`, `operation: ToolName`, `target: ResourceRef` | Bound identity and exact operation |
| `previous_version: Version \| null`, `resulting_version: Version \| null` | Null previous only for creation; null result permitted only for failed/unknown verification |
| `resulting_status: string \| null`, `changed: boolean \| null` | Actual observed state; unknown is null |
| `requested_fields: string[]`, `affected_fields: string[]` | Canonical field paths requested and actual changed projection fields, including known WordPress-generated changes; arrays unique and sorted |
| `source_revision_id: Id \| null`, `resulting_revision_id: Id \| null` | Distinct source and result; result null if no saved revision was produced |
| `timestamp: Timestamp` | Time of receipt construction after readback attempt |
| `verification` | `{state: verified \| failed \| unknown, method: wordpress_readback, verified_at: Timestamp \| null, checked_fields: string[], mismatched_fields: string[]}` |
| `write_state` | `applied`, `partial`, or `unknown`; a verified no-op is `applied` with `changed: false` |
| `input_sha256: Digest \| null`, `stored_sha256: Digest \| null` | Upload-only hashes; null otherwise |
| `trash_retention_days: integer \| null` | Trash-only retention at execution; null otherwise |

Fresh readback uses WordPress APIs after cache invalidation where necessary, plus bounded byte verification for a new upload. Compare every requested field to its validated intended value, every operation invariant, and all supposedly preserved fields in the projection. Do not merely trust the write API return value or compare two identical cached objects. A generated ID alone is insufficient proof. Unexpected transformation/hook changes cause `VERIFICATION_FAILED` with the observed receipt, not success. Known generated fields (timestamps, creation slug when not supplied, revision ID) are explicitly accounted for. Existing-item no-ops avoid calling write APIs and still perform fresh readback.

Creation verifies type/author/draft state and provided fields; publication verifies status plus current UTC publication time within the recorded execution interval; Trash verifies continued existence and retained editorial state, allowing WordPress's documented Trash slug transformation. Relation, term, metadata and restore verification are field-specific as listed in the inventory. Upload verifies the re-encoded stored file digest, MIME, dimensions and attachment fields. Generated image subsizes are checked for completion, but their individual hashes are not part of this receipt.

MCP validates receipt schema, requested operation/key/site/actor/target, and verification state before relaying it verbatim. It cannot manufacture a successful bridge receipt from an HTTP status. Receipts are persisted on the bridge and can be looked up independently by a trusted operator. TLS and access-controlled storage protect them in the MVP; there is no signature or non-repudiation claim. A compromised MCP can lie to a client, and a compromised WordPress installation can forge both state and receipts. Compare with the bridge through a separately trusted path after suspected compromise.

## Protocol adaptation

MCP `structuredContent` is the complete bridge envelope, including errors; `content` contains one short, escaped text summary and no extra sensitive data. Set `isError: true` for operation failures. The current MCP `2026-07-28` adapter also supplies `resultType: complete`; the legacy `2025-11-25` adapter uses that revision's result envelope. Both advertise an output schema covering the success/failure union. Authentication HTTP challenges and malformed JSON-RPC errors are transport/protocol responses, not fake tool successes. See [AUTHENTICATION.md](AUTHENTICATION.md) for version and OAuth handling.

Sources checked 2026-10-05: [RFC 8785](https://www.rfc-editor.org/rfc/rfc8785), [WordPress Trash behavior](https://developer.wordpress.org/reference/functions/wp_trash_post/), [revision restoration](https://developer.wordpress.org/reference/functions/wp_restore_post_revision/), [post insertion](https://developer.wordpress.org/reference/functions/wp_insert_post/), [slug uniqueness](https://developer.wordpress.org/reference/functions/wp_unique_post_slug/), [MCP tools](https://modelcontextprotocol.io/specification/2026-07-28/server/tools). These supply interoperability facts; the shapes and controls in this document are Coagmentator decisions.
