# Shared Contracts, Version 1.0

Accepted Gate 1 design, 2026-10-05. Normative design for the MCP server and WordPress bridge, not an implemented API. Tool inventory: [MCP-TOOLS.md](MCP-TOOLS.md). Authorization: [CAPABILITIES.md](CAPABILITIES.md). Failures: [ERRORS.md](ERRORS.md).

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

`site_id` is injected from the pinned server configuration, never requested from the model. `site_info` still has empty arguments. For fresh mutations MCP generates and persists `request_id` and `requested_at` once, then adds them to internal `arguments`. A supplied existing request handle selects that immutable record; it is not a new ID supplied by the model. The upload adapter replaces the external `file` object with validated bytes and server-derived fields, as specified below. Other editorial arguments are preserved. Mutation envelopes additionally require `policy_version: integer` (1..9007199254740991), injected from operator-pinned configuration and matched by the bridge before reservation/execution; read envelopes omit it. The operation comes from the fixed route, never a caller dispatch field. `actor_id` is a locally configured opaque identifier (1..64 ASCII letters, digits, underscore or hyphen) derived from the validated `(issuer, subject)` binding, never a tool argument or an authorization grant. The bridge binds it to its configured service user/Application Password identity. A compromised MCP server can impersonate this actor; WordPress capabilities, write-family policy and the selected bridge-side approval profile remain the ceiling. Only the strict profile adds independent per-intent approval against MCP compromise.

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
| `Base64` (internal bridge only) | Canonical RFC 4648 base64 with padding, no whitespace or data-URL prefix; decoded maximum 5 MiB, checked while decoding |
| `ClientFile` (external MCP only) | Closed object declaring `download_url`, `file_id`, `mime_type`, `file_name`, all strings; only the first two are required. URL at most 8,192 characters, opaque ID 1..512 characters, MIME hint at most 100, name hint at most 255; reject controls. Additional source/format checks below |

MCP request maximum is 2 MiB, including `upload_media` file-object requests. Internal bridge request maximum is 2 MiB except `upload_media`, which permits 7 MiB including base64 overhead. Enforce each transport ceiling before parsing/buffering; the MCP download has a separate 5 MiB streaming ceiling, independent of either JSON limit. Protected response maximum is 2 MiB. Lists are additionally capped at 1 MiB. Enforce decoded image maximum 25 million pixels, width/height each 10,000, and 20 seconds processing budget. Other operation deadline is 15 seconds; upload end-to-end deadline is 30 seconds. Lower host limits apply and are advertised by `site_info`; raising these design maxima needs a contract review.

Default rate ceilings at both server and bridge: 60 read calls/minute, 10 mutation submissions/minute, and 2 uploads/minute per authenticated binding; at most 4 active requests and 1 active write per binding. Count approval submissions and exact retries against admission limits. Apply a separate pre-auth IP ceiling of 120 requests/minute and reject excess concurrent work rather than queue indefinitely. Server/site totals default to the same ceilings in this one-operator MVP. These are protective limits, not billing or usage metering.

Maximum JSON nesting is 20; pending approvals 20, retained journal rows 10,000 and private pending-payload bytes 100 MiB per binding. At most 1,000 candidate objects are scanned per list request, with a continuation cursor when more remain. Quota exhaustion refuses new work without evicting protected deduplication records. Private approval and MCP continuity payloads are encrypted at rest using each component's host-specific key outside its database. Audit and journal retention is 90 days; payload expiry rules below are shorter.

## Shared shapes

Editorial shapes are shared unchanged across boundaries unless an explicit bridge/model projection is named. Each internal read success uses its corresponding shape (`BridgeSiteInfo`/`BridgeMutationStatus` where named); mutation success uses `BridgeMutationResult` plus `Receipt`. All fields in the following shapes are present unless explicitly called optional. `null` is distinct from a missing input. Additional output fields are prohibited at this contract revision.

| Shape | Fields |
| --- | --- |
| `BridgeSuccess<T>` (internal) | `ok: true`, `contract_version: "1.0"`, `correlation_id: UUID`, `site_id: SiteId`, `data: T`, `receipt: Receipt \| null` (null for reads) |
| `Success<T>` (model) | `ok: true`, `data: T`. No internal envelope fields or duplicate receipt |
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
| `BridgeMutationResult` (internal) | `target: ResourceRef`, `version: Version`, `status: string`, `changed: boolean`. Status is content status, `active` for term, `inherit` for media |
| `MutationResult` (model) | The `MutationEvidence` projection below, with non-null target/resulting version/status, `verification.state: verified`, `write_state: applied`. Fetch a resource separately for its complete current content |
| `BridgeMutationStatus` (internal) | `request_id: UUID`, `operation: ToolName`, `state: awaiting_approval \| rejected \| executing \| completed \| failed \| indeterminate`, `write_state: not_applied \| applied \| partial \| unknown`, `receipt: Receipt \| null`, `error: Error \| null`, `approval: BridgeApprovalInfo \| null`, `updated_at: Timestamp` |
| `MutationStatus` (model) | `request_id: UUID`, `operation: ToolName`, same `state` and `write_state`, `result: MutationEvidence \| null`, `error: ModelError \| null`, `approval: ApprovalInfo \| null`. No journal update timestamp |

Content raw fields are stored source, including valid block comments, not rendered HTML. Do not run shortcodes, dynamic blocks, oEmbed, or remote URL fetches on reads. Strings and links from WordPress remain untrusted content, never instructions. Public URLs and media URLs are returned as data, never fetched by the bridge or MCP server. A plugin that exposes protected files must not be assumed compatible with public WordPress uploads.

`BridgeSiteInfo` contains exactly: `site_id`, `name` (plain text), `home_url` (canonical HTTPS), `timezone` (IANA name or WordPress fixed-offset string), `bridge_version` (implementation semver), `policy_version: integer`, `contract_versions: string[]`, `content_types: ContentType[]`, `taxonomies: Taxonomy[]`, `available_tools: ToolName[]`, `metadata_keys: MetadataKey[]`, `seo_mode: disabled | coagmentator_basic_v1`, `trash_retention_days: integer`, `limits: Limits`, `writes_enabled: boolean`, `approval_profile: strict | trusted_single_operator`, `approval_required_operations: ToolName[]`, and `approval_required_for_live_edits: boolean`.

`SiteInfo` is the model projection: omit `site_id`, `bridge_version`, `policy_version` and `contract_versions`; replace `limits` with `ModelLimits`. All other fields remain. `ModelLimits` includes only `request_bytes`, `response_bytes`, `list_response_bytes`, `body_bytes`, `excerpt_bytes`, `media_bytes`, `image_pixels`, `image_dimension`, `page_limit`, `term_ids`. These help construct bounded requests; journal capacity, internal upload envelope sizes, pre-auth limits and tracing settings stay operator-only. Domain/name identify the site to a human without an infrastructure UUID. No WordPress/PHP versions, plugins, user emails, server paths or credential UUIDs are exposed.

The bridge computes available tools from configuration/capabilities. MCP intersects `available_tools` with its configuration/scopes, filters the unconditional `approval_required_operations` to that set, ANDs `writes_enabled` with its own write switch, and reports the minimum of corresponding MCP/bridge content, request and response limits. Profile/policy must match the pinned deployment policy; mismatch disables writes. In strict mode the unconditional list is publication, Trash, upload, term update and restore, and the live-edit flag is true. In trusted mode the list is empty and the flag false. Neither mode enables a disabled family. `ToolName` is exactly an inventory name; `MetadataKey` is exactly a key in the metadata table. These are explicit allowlisted projections, never arbitrary response rewriting. Original bridge receipts remain immutable. `tools/list` discovery and step-up are in [AUTHENTICATION.md](AUTHENTICATION.md).

`Limits` is a closed object of positive integer effective ceilings with exactly these keys and default maxima:

| Keys | Values, in the same order |
| --- | --- |
| `request_bytes`, `bridge_upload_request_bytes`, `response_bytes`, `list_response_bytes` | 2097152, 7340032, 2097152, 1048576 |
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

`TermPatch` is a nonempty subset of `name`, `slug`, `description`, `parent_id`. Term creation derives a valid bounded ASCII slug from the name if omitted; if none can be derived, require an explicit slug. Reject duplicate name/slug in the relevant taxonomy/parent namespace; never silently suffix a colliding term. Parent changes are category-only and must form an acyclic hierarchy. Terms and relations use validated integer IDs, never names that implicitly create a term. Relation arrays have at most 100 unique IDs and are treated as sets. Posts must have at least one category, preventing WordPress's default-category substitution from silently changing intent. Pages expose empty taxonomy arrays and reject assignment operations.

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

### External file input and controlled retrieval

The external `upload_media` descriptor has the following file schema. Editorial fields retain the inventory limits; the optional request handle is for previously allocated requests only. This is documentation, not implementation code.

```json
{
  "name": "upload_media",
  "inputSchema": {
    "type": "object",
    "properties": {
      "file": {
        "type": "object",
        "properties": {
          "download_url": {"type": "string", "maxLength": 8192},
          "file_id": {"type": "string", "minLength": 1, "maxLength": 512},
          "mime_type": {"type": "string", "maxLength": 100},
          "file_name": {"type": "string", "maxLength": 255}
        },
        "required": ["download_url", "file_id"],
        "additionalProperties": false
      },
      "alt_text": {"type": "string", "maxLength": 2000},
      "title": {"type": "string", "minLength": 1, "maxLength": 300},
      "request_id": {"type": "string", "format": "uuid"}
    },
    "required": ["file", "alt_text"],
    "additionalProperties": false
  },
  "_meta": {"openai/fileParams": ["file"]},
  "annotations": {
    "readOnlyHint": false,
    "destructiveHint": false,
    "openWorldHint": true,
    "idempotentHint": false
  }
}
```

[OpenAI's file-input reference](https://developers.openai.com/plugins/reference) was checked on 2026-10-05: declare all four file properties, require exactly `download_url` and `file_id`, keep `mime_type`/`file_name` optional, and declare the top-level field in `openai/fileParams`. Coagmentator's limits and source controls are additional requirements. Neither that metadata nor possession of a plausible `file_id` proves who supplied a URL.

MCP performs controlled client-file retrieval; WordPress never downloads the file URL. The retrieval sequence is:

1. Authenticate the OAuth operator and media scope; check enabled family, fixed binding, admission limits and an operator-installed file-source profile. No URL fetch occurs during `tools/list`, validation of unrelated tools or `get_mutation`.
2. A source profile enumerates exact approved file-delivery HTTPS origins (host and port 443), bounded object-path patterns and permitted signed-download query structure for the client file service. No arbitrary sites, wildcard cloud-provider domains, URL-to-URL proxy endpoints, caller headers or caller-selected profile. Unknown origins/paths fail closed. Gates 4/5 must record actual observed ChatGPT delivery origins and verify their behavior before enabling uploads; a changed origin requires out-of-band operator review, never automatic learning from a failed call. This is not a claim that OpenAI publishes a permanent download-host list.
3. Parse canonically: HTTPS only, no userinfo, fragment, IP-literal host, alternate port, backslash, ambiguous encoding or nested destination. Validate the complete destination against the profile. Resolve every address; reject loopback, private, link-local, multicast, unspecified, reserved/non-global and IPv4-mapped bypasses. Pin the approved resolved public address to the connection while preserving verified TLS hostname/SNI; revalidate on every new connection. An egress policy denies private/metadata networks independently. No proxy environment inheritance or second DNS resolution that permits rebinding.
4. Reject every redirect and every non-200 response. Send no OAuth token, WordPress credential, cookies or Referer to file storage; only the capability already present in the approved temporary URL. Disable HTTP content decompression, request identity encoding, and reject unexpected content encoding. Limit response headers to 32 KiB and use a 3-second connect plus 10-second total download deadline within the 30-second upload budget.
5. Reject declared `Content-Length` over 5 MiB and abort the streaming read before accepting byte 5 MiB + 1, including chunked/missing/false-length responses. Bound concurrency and private temporary storage before opening the connection. Never fully buffer and then check size. Do not log or return the signed URL, its query, the file object or response bodies. A timeout/expiry before bridge dispatch is a no-write failure; expiry after dispatch never justifies another upload.
6. In a bounded decoder worker, validate actual raster format, dimensions, pixel budget and successful complete decode; reject animation, truncation and unsupported formats. Compute SHA-256 of the original accepted bytes server-side. A supplied MIME hint must agree with the decoded format; hints and HTTP headers never authorize a format. Filename is an optional untrusted display hint, never a path: reject path/control/extra-extension tricks, normalize a safe basename, and use a generated `image-<digest-prefix>.<decoded-extension>` when absent or not representable. A supplied recognized raster extension that contradicts decoded format fails. Derive internal MIME/extension from verified bytes, and validate title/alt text before bridge dispatch.

Direct non-ChatGPT clients receive no generic downloader. File retrieval is disabled for a client registration unless the operator explicitly binds it to a reviewed source profile. Where client registration is used for this selection, require a provider-validated client identifier in the access token/introspection result; user-agent, Origin, MCP session metadata and a caller's claim to be ChatGPT are insufficient. Even a caller holding a legitimate token, or imitating a supported client, receives **the same origin/path, address, redirect, byte and decoder restrictions**. An arbitrary public URL or private destination cannot pass by being wrapped in `ClientFile`; `file_id` alone is never an authorization credential. A forged storage capability fails at the approved storage service. This bounds retrieval; it does not cryptographically attest that ChatGPT selected a file or prove ownership beyond the delegated download capability. If the provider/source profile cannot be validated, reject upload with `UNSUPPORTED_OPERATION`; other supported tools remain available.

### Internal byte contract and WordPress sanitization

MCP maps the external file to internal bridge `upload_media.arguments`: `request_id`, `requested_at`, `filename: SafeFilename`, `mime_type: ImageMime`, `data_base64: Base64`, `input_sha256: Digest`, `alt_text: AltText`, and `title: Title` (MCP resolves the external default once). This schema rejects `file`, `file_id`, `download_url`, all URL/path fields and caller-controlled headers. The bridge's only upload source is the bounded bytes in this authenticated envelope. No URL downloader, URL fallback or sideload endpoint is added.

WordPress independently verifies the decoded byte limit, input digest, actual decoder format, MIME, extension and dimensions, then decodes/re-encodes into the same static raster format and strips metadata. Use a bounded worker and generated storage names through WordPress media APIs; reject SVG, GIF/animation, archives, documents and executable formats. Input and stored digests differ after re-encoding. Generated subsizes must satisfy the same resource constraints; otherwise disable uploads. A partial file/DB write is journaled honestly and reconciled by the operator, never automatically deleted or uploaded again.

In strict mode prepare the sanitized image privately before human approval and bind its digest/dimensions/MIME with the immutable request. Publish those exact sanitized bytes only after checking the digest again. Trusted mode uses the same validation/re-encoding without the approval wait. Retained input and sanitized bytes count against the 100 MiB private-payload quota at each component; encrypted temporary payloads expire after execution/rejection/expiry and within 24 hours. Neither file locators nor bytes enter audit logs.

## Versions and concurrency

Versions are SHA-256 of UTF-8 RFC 8785 canonical JSON for a fixed projection, including `contract_version` and `site_id`. Numbers are safe integers, strings are exact stored Unicode (no Unicode normalization), set-valued ID arrays are sorted, and absent values use explicit null. Use the exact field names in the shapes above.

- Content projection: `ref`, `status`, `title`, `slug`, `content_raw`, `excerpt_raw`, `author_id`, `parent_id`, `date_gmt`, `modified_gmt`, `category_ids`, `tag_ids`, `featured_media_id`, and `metadata` (all enabled metadata keys, including nulls). Exclude URLs and revision IDs.
- Term projection: every `Term` field except `version`.
- Media projection: every `Media` field except `version` and `url`; include stored digest if known.
- Revision projection: `id`, `parent`, `created_gmt`, `title`, `content_raw`, `excerpt_raw`; exclude `available_fields` and `version`.

Hash domain is `{contract_version, site_id, resource_type, state}` where `resource_type` is `post`, `page`, `term`, `media`, or `revision`, and `state` is that projection. Clients treat versions as opaque. Hashes are not authorization tokens or proof against a compromised server.

For existing-target writes, authenticate/authorize, acquire a bounded bridge resource lock, read the current projection freshly, reject stale `expected_version`, check active WordPress editing locks, then recheck immediately before write. Lock key is site plus resource type plus ID (taxonomy for terms). Creating terms also locks taxonomy plus normalized name/slug. A binding-level write lock enforces the concurrency ceiling. Never break another user's editor lock.

These locks serialize Coagmentator writers only. Native WordPress editors, cron and third-party plugins do not honor bridge locks. Optimistic checks and readback detect many races but cannot guarantee an atomic compare-and-swap against every external writer. An external write in the final check/write window can still be overwritten. Live acceptance therefore requires an operator-controlled editing window; the receipt attests to a bounded observation, not perpetual state or serializable isolation. Stronger global coordination is a future architecture change, not an undocumented promise for Gate 3.

## Approval policy profiles

All writes start disabled independently at MCP and WordPress. A trusted human configures the profile, enabled write families and capabilities out of band. Configuration is versioned on both sides; bridge enforcement is authoritative, mismatched policy versions fail closed, and a policy change invalidates pending approvals/requests before execution. Neither a tool input, an OAuth scope nor a service credential can change profile, enable a family or grant approval rights.

| Profile | Per-intent independent WordPress approval | Standing authorization and intended use |
| --- | --- | --- |
| `strict` (installation default) | Required for publication, Trash, uploads, term updates, revision restore, and updates/metadata/relations/images on `publish` or `private` content | Explicitly enabled draft/non-live edits and draft/term creation may run under standing authorization; preserves the originally proposed extra boundary |
| `trusted_single_operator` | No second WordPress approval for explicitly enabled families | Suitable for the JimLunsford.com reference workflow after separate deployment authorization: fixed operator/site plus standing server permissions and the MCP client's confirmation behavior |

Both profiles enforce OAuth authentication/scopes, fixed operator/site binding, Application Password authentication, the must-use guard, native and narrowing capabilities, write-family enablement, closed schemas, expected versions, deduplication, readback verification, accurate evidence, quotas and no blind retry. `LIVE` still requires publish scope/capability under trusted policy; switching profiles never converts draft authority into live authority.

Client confirmation is a user-intent/UX safeguard whose exact behavior belongs to the client. It is not server authorization, a signed per-intent grant, or cryptographic proof against a compromised MCP host. Under trusted policy a compromised MCP host or stolen service credential can perform **every enabled write family**, including public/destructive effects when enabled, without a separate human approval. The WordPress guard and capabilities still bound it, but cannot distinguish the legitimate MCP host from an attacker using that credential. Strict policy retains independent exact-intent approval for operators who want that additional defense. Neither profile can protect against a compromised WordPress trust domain.

### Strict exact-intent approval

For an operation requiring strict approval, the bridge stores its canonical payload privately and returns `APPROVAL_REQUIRED` with `BridgeApprovalInfo`:

`{id: UUID, review_url: HTTPS URL, state: pending | approved | rejected | expired, expires_at: Timestamp}`.

The model `ApprovalInfo` omits the internal `id`, retaining `review_url`, `state`, and `expires_at` because those support the user's next action. The record ID necessarily remains in the review URL; it is a non-bearer locator, not a second reconciliation handle.

The review URL has the configured WordPress admin origin and path `/wp-admin/admin.php?page=coagmentator-approvals&approval_id=<UUID>`. MCP validates its origin/path/query before returning it. No content, secret, bearer approval token or return URL appears there. GET never approves. A separate cookie-authenticated human with `coagmentator_approve` and the operation's native/custom capabilities reviews the target/status, exact diff or sanitized media preview, public effects and expiry. Only an explicit CSRF-protected POST records approval. The service identity cannot log in interactively or approve, even if accidentally granted `coagmentator_approve`; no MCP approval tool or `confirmed` override exists in either profile.

Approval lasts ten minutes and binds site, service user, actor, operation, MCP-generated request ID and timestamp, canonical payload/preconditions, sanitized media digest when applicable, policy version and expiry. Changed intent/version, revoked scope/capability, policy change or expiry invalidates it. MCP resumes the same stored request only after an explicit repeat using the returned handle; approval alone never executes. The bridge atomically claims the approval and execution right together. Completed replays return original evidence; declines/expiry change no editorial data.

## Deduplication and uncertain outcomes

### Trusted ownership and MCP admission

`request_id` is an unpredictable UUIDv4 generated by Coagmentator for a new mutation. `requested_at` is MCP's trusted UTC allocation time, generated once; bridge clock validation still applies. Neither field is inferred from model prose or a caller timestamp. MCP durably records the handle, binding, normalized intent/hash, timestamp, policy version and dispatch state **before any bridge submission**. It also generates a fresh internal `correlation_id` per attempt. JSON-RPC IDs are transport correlation, never durable mutation keys.

An optional model `request_id` must refer to a prior Coagmentator-issued record for the same binding/operation. Unknown or expired handles return `NOT_FOUND` and cannot create a journal entry. Changed editorial arguments or preconditions return `IDEMPOTENCY_CONFLICT`; they never overwrite the original record. MCP reuses the original timestamp, request hash and bridge payload for an allowed explicit resume, not a reconstructed timestamp or fresh key. A provider-refreshed upload locator is transport data, not new intent: require the same file ID and editorial arguments; while immutable bytes are retained, use those bytes without another download. If bytes must be re-retrieved before first dispatch, enforce the whole download policy and compare the saved input digest. No changed bytes can inherit approval or a mutation key. Missing retained payload after dispatch allows lookup only, not a new download/re-execution.

MCP persists a binding-scoped canonical intent fingerprint before dispatch (operation/editorial arguments/preconditions; for uploads include the file ID but never the expiring URL or optional filename/MIME transport hints). Keep this lookup fingerprint immutable; bind the independently validated byte digest and derived fields in the separate saved bridge-payload hash before submission. For lookup, use fixed markers for omitted server-derived defaults, including an omitted upload title, so refreshed hints cannot change the fingerprint. Resolve those defaults once for the saved bridge payload and retain them; a later change of hints cannot change the title or canonical payload of an existing handle. Admission is serialized. A handle-less repeat matching an existing fingerprint within a 24-hour lost-result window returns that handle's recorded result or status instead of allocating another mutation. Concurrent duplicates share no execution ownership. An unresolved `executing`/`indeterminate` fingerprint remains blocked beyond that window until independent operator reconciliation. The same-arguments guard is intentionally conservative: an identical second creation within the window requires waiting after confirmed completion or out-of-band operator reconciliation; the model cannot bypass it with a chosen ID. Different file IDs or changed intent are not guaranteed deduplicated, so clients must never change arguments to evade uncertainty. If MCP tracking is lost/corrupt/restored without continuity, disable mutations and reconcile the bridge journal through a trusted operator path before accepting fresh writes.

Every result after request allocation, including approval, pre-write failure and uncertain outcome, returns `request_id`. Failures before allocation use null. If the entire response is lost, the persisted fingerprint supplies the handle on an identical repeat. There is no exactly-once guarantee for arbitrary new intents or client behavior; loss of response must never be treated as evidence of non-execution. MCP journal fingerprints, timestamps and compact evidence are protected for 90 days; exact payloads/bytes are encrypted, quota-bounded and deleted within 24 hours, sooner on completion/rejection/expiry. Unresolved key/hash tombstones are retained until operator reconciliation rather than evicted on a timer. Quota exhaustion stops new mutations.

### Bridge reservation and retry rules

The bridge's unique key is `(site_id, service_user_id, actor_id, request_id)` across all operations. Its canonical request hash includes operation, normalized byte-contract/editorial arguments, original `requested_at`, preconditions and policy version. Base64 is represented by the independently verified input digest; no mutable editorial input is omitted. A reused key with a different hash is `IDEMPOTENCY_CONFLICT`. Authentication/current scopes and native/custom authorization are rechecked before returning evidence, including replay.

Before WordPress side effects, reserve the key with a durable unique constraint, use conditional transitions and an execution owner token, then record execution intent before invoking WordPress. Concurrent duplicates cannot both execute. An `executing` reservation is never stolen after a timeout; worker death after intent leads to `indeterminate`, not automatic replay. Terminal no-effect validation failures need a fresh, deliberately reviewed intent after correction. `awaiting_approval` is the only normal pre-execution resumable bridge state.

Terminal records retain original result/receipt/error and hash for 90 days. First-seen bridge requests require the MCP-generated timestamp within the preceding 24 hours and no more than 60 seconds ahead. Exact old envelopes are therefore rejected after retention expires. The MCP rejects unknown handles rather than refreshing timestamps, and retains unresolved tombstones until reconciliation. A hostile MCP could generate new keys/times; deduplication is not authorization against a compromised host. The selected approval profile and WordPress capabilities bound that separate threat.

`get_mutation` never executes, repairs, approves or retries. It checks the original operation's current access requirements, without reapplying old status/version/editor-lock/approval preconditions; a published/trashed item must remain reconcilable. For a removed target require recorded native primitives plus current binding/scopes/custom policy, returning sanitized evidence only. A bridge `NOT_FOUND`, including an expired record, is not proof of non-execution. A persisted MCP record can establish a pre-dispatch failure only if its durable state proves no bridge submission began; otherwise report unknown and require reconciliation.

Automatic reads allow at most two bounded retries. MCP never automatically repeats a mutation POST, including after timeout, malformed response, cancellation or apparent transport recovery. Recovery of a never-dispatched local failure uses `state: failed`, `write_state: not_applied`, no receipt and the original safe error; a local admission still in progress returns `MUTATION_IN_PROGRESS` and its handle without another dispatch. It may look up the original bridge key and return validated evidence or `WRITE_OUTCOME_UNKNOWN`. An explicit same-handle resubmission may return completed evidence or resume an approved `awaiting_approval` request; it cannot reclaim `executing`/`indeterminate` ownership. A locally proven never-dispatched request may be explicitly dispatched once while its original timestamp/payload remains valid. Disconnect/cancellation is not rollback.

WordPress hooks, files, notifications and database writes are not one transaction. Prevalidate every field/capability, but preserve partial/unknown outcomes. No automatic compensating publish/delete/rollback/second upload. Storage/audit failure before execution stops it; failure after side effects makes the outcome uncertain. The bridge journal uses prepared internal storage operations, never a public SQL path or direct edits to core content tables.

## Receipts and readback

`Receipt` is the protected internal bridge/audit record, not the model result. It has exactly these fields:

| Field | Meaning |
| --- | --- |
| `receipt_id: UUID`, `request_id: UUID`, `correlation_id: UUID` | Bridge receipt, stable operation key, and original execution-attempt correlation. A replay envelope has a new correlation; its receipt retains the original |
| `site_id: SiteId`, `actor_id: string`, `operation: ToolName`, `target: ResourceRef \| null` | Bound identity and exact operation; null only when failed/unknown creation produced no verifiable target ID |
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

MCP validates the complete original receipt schema, requested operation/key/site/actor/target, and verification state before projecting the model result. Retain that original unchanged in the protected journal; never fabricate or edit bridge evidence to fit a successful result. It cannot manufacture a successful bridge receipt from an HTTP status. Receipts are persisted on the bridge and can be looked up independently by a trusted operator. TLS and access-controlled storage protect them in the MVP; there is no signature or non-repudiation claim. A compromised MCP can lie to a client, and a compromised WordPress installation can forge both state and receipts. Compare with the bridge through a separately trusted path after suspected compromise.

## Model evidence and disclosure boundary

`MutationEvidence` is an allowlisted projection of the validated receipt: `request_id`, `operation`, `target`, `previous_version`, `resulting_version`, `resulting_status`, `changed`, `requested_fields`, `affected_fields`, `source_revision_id`, `resulting_revision_id`, `write_state`, `input_sha256`, `stored_sha256`, `trash_retention_days`, and `verification: {state, method, checked_fields, mismatched_fields}`. Preserve exact values and nulls; omit only `verification.verified_at`. Success requires the verified non-null fields defined by `MutationResult`; failed/unknown evidence never acquires invented versions or target IDs. These fields support independent resource reads, reconciliation, changed-field inspection and validation of uploaded bytes without exposing tracing internals.

Keep `correlation_id`, internal `actor_id`/`site_id`, service/credential identity, `receipt_id`, journal `updated_at`, receipt `timestamp`, `verified_at`, request allocation/execution times, approval decision IDs and policy versions in protected logs/journals. Do not duplicate them into text, `structuredContent` or routine client `_meta`. A future authenticated operator channel may use non-model metadata only after a separate disclosure review; `_meta` alone is not access control. Model-side `request_id` is the single durable reconciliation handle.

Resource `date_gmt`, `modified_gmt`, revision `created_gmt` and WordPress `author_id` remain because they describe editorial state rather than internal tracing. Approval expiry and review link remain actionable. The original receipt preserves full audit linkage, request hash, identity and execution chronology through protected journal records; trimming the model projection does not trim the audit trail. Replaying a handle returns original evidence, not a newly claimed observation of current resource state.

## Protocol adaptation

MCP `structuredContent` contains `Success<T>` or model `Failure`, never a verbatim bridge envelope. `content` contains one short escaped summary without extra telemetry. Mutation successes use `MutationResult`; `get_mutation` projects `BridgeMutationStatus` to `MutationStatus`; errors map `Error` to `ModelError` as defined in [ERRORS.md](ERRORS.md). Set `isError: true` for operation failures. The `2026-07-28` adapter supplies `resultType: complete`; the `2025-11-25` adapter uses that revision's result envelope. Both advertise closed output schemas for the model success/failure union. Authentication HTTP challenges and malformed JSON-RPC errors remain protocol responses, not fake tool successes. See [AUTHENTICATION.md](AUTHENTICATION.md).

Sources checked 2026-10-05: [OpenAI reference](https://developers.openai.com/plugins/reference), [OpenAI tool planning](https://developers.openai.com/plugins/plan/tools), [RFC 8785](https://www.rfc-editor.org/rfc/rfc8785), [WordPress Trash behavior](https://developer.wordpress.org/reference/functions/wp_trash_post/), [revision restoration](https://developer.wordpress.org/reference/functions/wp_restore_post_revision/), [post insertion](https://developer.wordpress.org/reference/functions/wp_insert_post/), [slug uniqueness](https://developer.wordpress.org/reference/functions/wp_unique_post_slug/), [MCP tools](https://modelcontextprotocol.io/specification/2026-07-28/server/tools). These supply interoperability facts; the shapes and controls in this document are Coagmentator decisions.
