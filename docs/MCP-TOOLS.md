# MVP Tool Inventory

Accepted Gate 1 design, 2026-10-05. There are exactly **21 tools: 10 reads and 11 mutations**. No tools exist yet.

## Reading the inventory

[CONTRACTS.md](CONTRACTS.md) defines every named type, shared field, bound, envelope, precondition, and receipt. [CAPABILITIES.md](CAPABILITIES.md) maps each tool to OAuth scopes and WordPress checks. [ERRORS.md](ERRORS.md) supplies common failures. These requirements compose; a row never waives a shared rule.

- `R` means required and `O` means optional. An optional input has the stated default or is left unchanged. Undocumented properties, explicit nulls except where specified, and coercion between strings and numbers are rejected.
- No tool accepts `site_id`, `actor_id`, `correlation_id`, `requested_at`, policy selection, credentials or a general destination URL. The sole client-file locator is `file.download_url`, constrained as specified below. MCP injects the pinned identities and generates trusted timestamps internally. A fresh mutation normally has no request handle input: Coagmentator generates and durably records `request_id`. Every mutation accepts optional `request_id: UUID` only to resume/reconcile a previously issued handle with the same intent; it cannot allocate a caller-chosen ID. `get_mutation` requires that returned handle.
- Existing-target mutations require `expected_version: Version`, from a fresh read of that target. `create_draft`, `create_term`, and `upload_media` have no existing-target precondition. MCP retains the canonical request and timestamp for explicit resumption; clients retain the returned request handle. Lost-result recovery and duplicate suppression are specified in CONTRACTS.
- Model outputs use `Success<T>` or `Failure`, distinct from the internal bridge envelopes. Every mutation success returns `MutationResult`, the useful verified evidence projected from the protected receipt, including its durable `request_id`. Failures after request allocation also return that handle. Approval challenges report `write_state: not_applied`, never successful writes.
- `Ref` means `{type: "post" | "page", id: Id}`. A page and a post with the same numeric ID are not interchangeable. This is a single-site, single-operator MVP.

## Read tools

| Tool | Purpose and resources read | Required inputs, beyond shared fields | Optional inputs and defaults | Output `data` | Validation and important failures |
| --- | --- | --- | --- | --- | --- |
| `site_info` | Identify the configured WordPress site and supported contract features | None, input is `{}` | None | `SiteInfo` | Authenticated only; pinned bridge identity must match. `SITE_MISMATCH`, `UPSTREAM_AUTHENTICATION_FAILED`, `UNSUPPORTED_OPERATION` for incompatible contract |
| `search_content` | Find authorized posts/pages without downloading bodies | None | `types: ContentType[] = [post,page]`; `statuses: ContentStatus[] = [publish,draft,pending]`; `query: SearchText = ""`; `limit: PageLimit = 20`; `cursor: Cursor` | `Page<ContentSummary>` | Nonempty unique filter arrays; bounded search; permission-filter before results and pagination. No unfiltered total counts. Invalid cursor, unsupported type/status, limits |
| `get_content` | Retrieve complete stored editorial fields of one post/page | `ref: Ref` | None | `Content` | Requires raw/edit access, rejects password-protected content; body is complete or returns `RESOURCE_LIMIT`, never truncated. Wrong type or concealed resource is `NOT_FOUND` |
| `list_terms` | Look up categories or tags, including by name | `taxonomy: Taxonomy` | `query: SearchText = ""`; `parent_id: IdOrZero` (category only); `limit = 20`; `cursor` | `Page<Term>` | Taxonomy must be `category` or `post_tag`; no arbitrary taxonomy, hidden metadata, or content counts |
| `search_media` | Find authorized image attachments | None | `query: SearchText = ""`; `mime_types: ImageMime[] = all supported`; `limit = 20`; `cursor` | `Page<Media>` | Only eligible raster attachments, attachment and parent authorization, bounded results; no local paths, EXIF, or secrets |
| `get_media` | Inspect an image before reuse or verify a new upload | `media_id: Id` | None | `Media` | Same eligibility/authorization as media search; no arbitrary file read or image transformation |
| `get_metadata` | Retrieve the fixed public contract's approved metadata | `ref: Ref` | `keys: MetadataKey[] = all enabled keys` | `MetadataSnapshot` | Empty/duplicate/unknown keys rejected; explicitly requested disabled SEO keys yield `UNSUPPORTED_OPERATION`. No all-meta escape hatch |
| `list_revisions` | Discover saved revisions of authorized content | `ref: Ref` | `limit = 20`; `cursor` | `Page<RevisionSummary>` | Check parent before querying; saved revisions only, no autosaves; zero revisions is a valid empty page |
| `get_revision` | Read the complete restorable fields of one revision | `ref: Ref`; `revision_id: Id` | None | `Revision` | Revision must belong to the given parent; no cross-parent reads; full content or explicit limit failure |
| `get_mutation` | Resolve an approval, retry, timeout, or uncertain mutation | `request_id: UUID` | None | `MutationStatus` | Current actor, site, original operation scopes/caps, and target access required. No cross-actor journal lookup; unknown/expired IDs are `NOT_FOUND`, not proof that nothing happened |

## Mutation tools

The resources column describes intended WordPress changes. Core hooks can cause additional effects, covered in [CONTRACTS.md](CONTRACTS.md). `Strict approval` means the independent WordPress exact-intent approval in the `strict` profile. The `trusted_single_operator` profile permits each explicitly enabled family under standing server authorization plus client confirmation behavior. Every other authorization, version, deduplication and verification check remains identical. Neither profile accepts a model-supplied confirmation boolean.

| Tool | Purpose and WordPress resources affected | Required inputs, beyond shared fields | Optional inputs and defaults | Verification and approval | Important operation failures |
| --- | --- | --- | --- | --- | --- |
| `create_draft` | Create one post/page owned by the dedicated service user | `type: ContentType`; `title: Title`; `content_raw: Body` | `excerpt_raw: Excerpt = ""`; `slug: Slug` (bridge derives and validates when absent) | Verify requested fields, author, type, and `draft` status; return target ID/version. No publication path; standing authorization in both profiles | Invalid/sanitized-away content, explicit slug collision, creation failure; no author, date, status, meta, or taxonomy override |
| `update_content` | Replace selected editorial fields of one existing item; core may create a revision | `ref`; `expected_version`; `patch: ContentPatch` | None | Verify every patch field and preservation of status/author/date/relationships/metadata; strict approval when current status is `publish` or `private` | Stale version, active editor lock, unsupported status, slug collision, empty patch, verification failure |
| `publish_content` | Publish one existing draft or pending item immediately | `ref`; `expected_version` | None | Strict approval; verify `publish`, publication time, unchanged editorial content and slug, and resulting version | Already published (unless replay), future/private/trash status, empty title/body, missing/invalid/colliding slug, stale approval; scheduling excluded |
| `trash_content` | Move one item to recoverable Trash; core also updates internal Trash metadata and associated comment status | `ref`; `expected_version` | None | Strict approval; verify object still exists with `trash` status and retained editorial values; report retention days | Trash disabled, already trashed (unless replay), future/unsupported status, protected system page, stale version. Never fall back to permanent deletion |
| `create_term` | Create one category or tag | `taxonomy`; `name: TermName` | `slug: Slug` (generated); `description: TermDescription = ""`; `parent_id: IdOrZero = 0` (category only) | Verify taxonomy, name, requested slug, description, parent; no content assignment; standing authorization in both profiles | Duplicate name/slug, missing parent, parent in wrong taxonomy, tag parent supplied |
| `update_term` | Rename or edit one category/tag, potentially changing public archives | `taxonomy`; `term_id: Id`; `expected_version`; `patch: TermPatch` | None | Strict approval; verify all patch fields and preserved identity/taxonomy | Duplicate slug/name, hierarchy cycle, wrong taxonomy, stale version, empty patch |
| `set_content_terms` | Replace one post's complete category or tag assignment | `ref` (post only); `taxonomy`; `term_ids: Id[]`; `expected_version` | None | Verify exact sorted term set and unchanged other taxonomy; strict approval for `publish`/`private` target | Missing/wrong-taxonomy term, term permission denial, duplicates, pages unsupported; empty categories rejected, empty tags clears |
| `upload_media` | Retrieve one client file at MCP; validate and forward bytes for WordPress re-encoding and attachment creation | `file: ClientFile`; `alt_text: AltText` | `title: Title` (safe filename stem, or `Image`, if absent) | Strict approval because uploads are normally public; both profiles verify stored digest, MIME, dimensions, attachment, title and alt text | Unapproved file source, forged/expired URL, private address/redirect, byte/pixel/decoder limits, MIME mismatch, unavailable safe decoder, partial file/DB failure |
| `set_featured_image` | Set or clear a post/page's featured-image relation | `ref`; `expected_version`; `media_id: Id \| null` | None | Verify relation, current attachment eligibility, and unchanged other fields; strict approval for `publish`/`private` target | Unsupported thumbnail feature, unauthorized/missing/non-image attachment, stale content; null clears, zero is invalid |
| `update_metadata` | Set/delete selected approved metadata keys on one item | `ref`; `expected_version`; `patch: MetadataPatch` | None | Verify presence and value of every key and preserved unrelated fields; strict approval for `publish`/`private` target | Unknown/disabled key, type/length failure, missing metadata authorization, incompatible SEO mode, partial failure |
| `restore_revision` | Copy selected historical editorial fields to the current parent | `ref`; `expected_version`; `revision_id`; `revision_version: Version`; `fields: RevisionField[]` | None | Strict approval; verify chosen source fields and preservation of current status, date, author, slug, terms, image and metadata | Wrong parent, autosave, missing/pruned revision, unsafe old content, stale source/parent, empty field list; no automatic republish or metadata rollback |

## Explicit choices and exclusions

- Unified content tools replace duplicated post/page tools. There is no general `create_post` with a status argument: creation always produces a draft, and publication is separate.
- `set_content_terms` replaces one relationship set, not the term objects. Taxonomy deletion and bulk mutations are excluded. Trash restoration remains a human WordPress operation in this MVP.
- Media upload exposes one top-level `file` object and `_meta["openai/fileParams"]: ["file"]`. The model never constructs base64 or a digest. Only the MCP server retrieves an authorized client-file locator under the bounded source policy in CONTRACTS; arbitrary remote-media URLs and paths remain prohibited. The internal WordPress upload route accepts bytes and a server-computed digest, never a URL. Gate 5 must prove the actual ChatGPT handoff and hostile direct-caller rejection.
- Metadata keys and the opt-in basic SEO mode are fixed in [CONTRACTS.md](CONTRACTS.md). No vendor-specific SEO internals are guessed. Supporting existing third-party SEO providers is later work.
- No user tools, site-setting changes, custom post types, multisite, scheduling, permanent deletion, theme/plugin administration, dynamic Abilities dispatch, REST proxy, SQL, WP-CLI, shell, PHP, or filesystem tools.

## MCP descriptions and annotations

Tool schemas are closed JSON Schema 2020-12 objects. The following values are explicit per-tool descriptor values, not runtime guesses about one invocation. `readOnlyHint` describes absence of resource mutations; `destructiveHint` covers deletion, overwriting and difficult-to-reverse effects; `openWorldHint` covers public or open-ended effects, not the mere use of HTTPS. `idempotentHint` describes repeated *model arguments*, not an internal bridge retry key. Incidental protected logging does not make reads writes.

| Tool | `readOnlyHint` | `destructiveHint` | `openWorldHint` | `idempotentHint` | Behavior justifying these values |
| --- | --- | --- | --- | --- | --- |
| `site_info` | true | false | false | true | Bounded configuration read, no changes or external discovery |
| `search_content` | true | false | false | true | Search only authorized content on the pinned site |
| `get_content` | true | false | false | true | Stored content read, no render or outbound fetch |
| `list_terms` | true | false | false | true | Bounded site taxonomy lookup |
| `search_media` | true | false | false | true | Read attachment records, never fetch returned media URLs |
| `get_media` | true | false | false | true | Read one authorized attachment record |
| `get_metadata` | true | false | false | true | Read only enabled logical keys on the pinned site |
| `list_revisions` | true | false | false | true | Read authorized saved revision summaries |
| `get_revision` | true | false | false | true | Read source text without executing it |
| `get_mutation` | true | false | false | true | Status lookup cannot execute, approve or repair a mutation |
| `create_draft` | false | false | false | false | Additive private draft; fresh calls can create multiple drafts |
| `update_content` | false | true | true | true | Overwrites fields and can edit published content; the same full-version precondition prevents a second write |
| `publish_content` | false | true | true | true | Public disclosure and syndication can be irreversible; original version/status prevents repeat publication |
| `trash_content` | false | true | true | true | Removes content, including public content; original version/status prevents repeat Trash |
| `create_term` | false | false | true | true | Additive public taxonomy object; locked duplicate-name/slug checks reject an identical second creation |
| `update_term` | false | true | true | true | Overwrites taxonomy data/public archives, guarded by full version |
| `set_content_terms` | false | true | true | true | Replaces relationships and can alter public presentation, guarded by full version |
| `upload_media` | false | false | true | false | Adds an attachment/file normally accessible publicly; a fresh call can upload another copy |
| `set_featured_image` | false | true | true | true | Replaces or clears a relationship that can be public, guarded by full version |
| `update_metadata` | false | true | true | true | Overwrites/deletes allowed values, including public SEO, guarded by full version |
| `restore_revision` | false | true | true | true | Overwrites selected current fields, possibly public; parent/source versions constrain repeat effects |

A tool covering both draft and live edits declares the live effect because that behavior is reachable; `create_draft` cannot publish and is therefore classified separately. A new term or media file is additive rather than destructive, while public disclosure through publication warrants the stronger label. Before enabling a deployment, review installed hooks: if draft creation emits public effects or a nominally additive operation overwrites data, constrain that behavior or revise the affected descriptor and acceptance evidence. Do not label every site interaction open-world because of unspecified hypothetical hooks.

For existing-target writes, `idempotentHint: true` assumes the documented full-version precondition, no-op avoidance and bridge serialization: an identical repeat either conflicts, returns the prior evidence, or verifies a no-op without calling a write API. It does not mean repeating the call is the right recovery action or guarantee exactly-once external hook effects. New IDs are never a workaround for uncertainty. `create_draft` and `upload_media` remain false because they can add distinct objects with identical fresh inputs. `create_term` is true because its serialized duplicate-name/slug rejection supplies the equivalent no-extra-effect boundary; no silent term suffixing is allowed. All tools still enforce OAuth and server policy independently of annotations and client confirmation.

Sources checked 2026-10-05: [OpenAI reference: file inputs and annotations](https://developers.openai.com/plugins/reference), [OpenAI tool planning](https://developers.openai.com/plugins/plan/tools), [MCP tool definitions](https://modelcontextprotocol.io/specification/2026-07-28/server/tools). The schema follows the OpenAI file contract; the per-tool risk classifications and controls are Coagmentator decisions.
