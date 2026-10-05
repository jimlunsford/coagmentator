# Authorization and Service Identity

Accepted Gate 1 design, 2026-10-05. All checks are conjunctive. OAuth scope, operator enablement, selected bridge approval policy, custom WordPress capability and native WordPress capability must all allow a call. An AI instruction, receipt, client ID, role name, nonce or approval cannot replace these checks.

## Native authority plus custom narrowing

Use `current_user_can()` with the object ID for meta capabilities. Resolve post-type and taxonomy capability names from the registered WordPress objects, not hard-coded role labels. Native WordPress capabilities are sufficient to authorize the underlying operations, but are too broad to represent Coagmentator's independent read, publish, Trash and approval switches. Add the custom primitive capabilities below as **additional restrictions**, never as replacements for native checks. Do not grant `edit_post`, `read_post`, `delete_post`, or other meta capabilities directly to a role, and do not synthesize a native grant during a bridge request.

This follows WordPress's documented distinction between [capability checking](https://developer.wordpress.org/reference/functions/current_user_can/) and [meta-capability mapping](https://developer.wordpress.org/reference/functions/map_meta_cap/). The checks below are a deliberately narrower Coagmentator policy.

## Common checks

- `BASE`: dedicated configured service user, authenticated using an approved Application Password UUID, correct site/actor binding, active must-use guard, and `coagmentator_read` plus `read`. No cookie-authenticated bridge calls and no other WordPress identities.
- `READ(ref)`: recognized post/page and status; `current_user_can('read_post', id)` **and** `current_user_can('edit_post', id)`. Raw content access intentionally requires editing authority. Password-protected content is excluded regardless of capability. Private content also requires operator opt-in and the native private-content capabilities. The same test filters search rows before output.
- `EDIT(ref)`: `READ(ref)` plus `current_user_can('edit_post', id)`, supported mutation status, no active native editor lock, and valid version. Do not confuse `edit_posts` with permission to edit every author's post.
- `MEDIA(id)`: eligible image attachment; native `read_post` and `edit_post` for the attachment, and `READ(parent)` when attached to a supported parent. A parent of any other type, or an inaccessible parent, excludes the attachment. Unattached images still require attachment edit authority.
- `ASSIGN(taxonomy, IDs)`: registered taxonomy attached to the target post type; its `cap->assign_terms` and `current_user_can('assign_term', term_id)` for each term. Check even when an operation clears a set. [Core assignment behavior](https://developer.wordpress.org/reference/classes/wp_rest_posts_controller/check_assign_terms_permission/) is the reference.
- `LIVE(ref)`: if current status is `publish` or `private`, require native `cap->publish_posts`, custom `coagmentator_publish`, OAuth `coagmentator.publish`, and strict-profile independent approval in addition to the operation's normal checks. Trusted policy removes only that second human approval, never the publish authority checks. This prevents draft-edit authority from modifying live content through metadata or relationships.

## Complete tool mapping

All scopes in this table are required in addition to `coagmentator.read`. All native/custom checks are in addition to `BASE`. `cap` is the registered object's capabilities, using the appropriate post/page or taxonomy object. Dynamic conditions are checked on the server and bridge, not encoded solely in tool annotations.

| Tool | Additional OAuth scope | Additional custom capability | Native WordPress requirement |
| --- | --- | --- | --- |
| `site_info` | None | None | `read`; return safe configuration subset only |
| `search_content` | None | None | Type's `cap->edit_posts` before query, then `READ` for each result |
| `get_content` | None | None | `READ(ref)` |
| `create_draft` | `coagmentator.edit` | `coagmentator_edit` | Type's `cap->create_posts` and `cap->edit_posts`; force author to authenticated service user |
| `update_content` | `coagmentator.edit` | `coagmentator_edit` | `EDIT(ref)` and `LIVE(ref)` when applicable |
| `publish_content` | `coagmentator.publish` | `coagmentator_publish` | `EDIT(ref)` plus type's `cap->publish_posts`; strict-profile approval |
| `trash_content` | `coagmentator.trash` | `coagmentator_trash` | `READ(ref)` plus `current_user_can('delete_post', id)`; live/private also `LIVE(ref)`; strict approval for every Trash |
| `list_terms` | None | None | Taxonomy's `cap->assign_terms` |
| `create_term` | `coagmentator.terms` | `coagmentator_terms` | Taxonomy's `cap->edit_terms`; this is intentionally stricter than any core tag-creation shortcut using assignment authority |
| `update_term` | `coagmentator.terms` | `coagmentator_terms` | Taxonomy's `cap->edit_terms` and `current_user_can('edit_term', term_id)`; strict-profile approval |
| `set_content_terms` | `coagmentator.edit` | `coagmentator_edit` | `EDIT(ref)` plus `ASSIGN` and conditional `LIVE(ref)` |
| `search_media` | None | None | `MEDIA` for every result; no `upload_files` grant needed just to read |
| `get_media` | None | None | `MEDIA(media_id)` |
| `upload_media` | `coagmentator.media` | `coagmentator_media` | `upload_files` and attachment type's `cap->create_posts`; strict-profile approval |
| `set_featured_image` | `coagmentator.edit` | `coagmentator_edit` | `EDIT(ref)`, `MEDIA(media_id)` unless clearing, native `edit_post_meta`/`delete_post_meta` for `_thumbnail_id` as applicable, conditional `LIVE(ref)` |
| `get_metadata` | None | None | `READ(ref)` and registered metadata read policy; never enumerate arbitrary storage keys |
| `update_metadata` | `coagmentator.meta` | `coagmentator_meta` | `EDIT(ref)` and `edit_post_meta`, `add_post_meta`, or `delete_post_meta` for each exact storage key and operation; registered callback must also allow; conditional `LIVE(ref)` |
| `list_revisions` | None | None | `READ(ref)` and parent `edit_post` before revision lookup |
| `get_revision` | None | None | Same as list, plus exact parent/revision relationship |
| `restore_revision` | `coagmentator.restore` | `coagmentator_restore` | `EDIT(ref)`, parent `edit_post`, source-read authority, conditional `LIVE(ref)`; strict-profile approval |
| `get_mutation` | Original operation's scopes, including conditional scopes recorded at submission | Original operation's custom caps | Current original target requirements if target exists; if gone, same actor plus original operation primitives may receive only sanitized journal status/receipt. No body or private approval payload returned |

`LIVE` also applies to `trash_content` as shown, including publish scope and capability. It is not required for new unattached media or term updates because their separate scopes, capabilities and enabled families authorize those distinct public effects, with independent approval additionally required under strict policy. Taxonomy admin grants are resolved through WordPress's actual mapping; do not assume tags and categories always have independent primitive caps. References: [term creation](https://developer.wordpress.org/reference/classes/wp_rest_terms_controller/create_item_permissions_check/), [term editing](https://developer.wordpress.org/reference/classes/wp_rest_terms_controller/update_item_permissions_check/), [revision reads](https://developer.wordpress.org/reference/classes/wp_rest_revisions_controller/get_items_permissions_check/), [media creation](https://developer.wordpress.org/reference/classes/wp_rest_attachments_controller/create_item_permissions_check/). Checked 2026-10-05.

## Approval policy is an additional control

`strict` and `trusted_single_operator` use the identical tool/capability mapping above. Both enforce Application Password identity, must-use guard, fixed binding, native plus narrowing capabilities, closed schemas, versions, journal ownership and readback. Write families default off in both MCP and bridge; family enablement is separate from granting scopes/capabilities. A bridge-host operator selects and versions the policy out of band. The service role has no configuration, policy-change, approval or credential-administration route.

Strict policy retains separate exact-intent WordPress approval for the rows marked strict, including conditional live edits. A human approver must be a different identity even if the service user accidentally acquires an approval cap. Trusted policy permits explicitly enabled families through standing server authorization and client confirmation behavior; it does not require an approval token or a model `confirmed` flag. A valid stolen service credential or compromised MCP host can exercise those enabled trusted-policy writes. Client confirmation cannot replace the server checks or prove intent cryptographically. Full mechanics: [CONTRACTS.md](CONTRACTS.md).

## Least-privilege provisioning

Do not assign Administrator or Editor as a shortcut. Create a dedicated role with a documented explicit primitive-capability set for enabled tools and content ownership. No production role/user is created in Gate 1.

The non-authorizing role marker in the table is named `coagmentator_service`. Guard decisions use configured service-user IDs even if that marker is removed; changing a role must not disable the guard.

| Provisioning profile | Native primitives to evaluate and grant only as needed | Bridge capabilities |
| --- | --- | --- |
| Raw editorial reader, own content | `read`, `edit_posts`, `edit_pages`; taxonomy assignment mappings as needed | `coagmentator_read`, service marker |
| Reader for the site's existing authors and published items | Above plus `edit_others_posts`, `edit_others_pages`, `edit_published_posts`, `edit_published_pages`; these are needed for raw edit-context access | Same read-only bridge cap |
| Draft editor | The reader set and the primitives to which type `create_posts` maps | Add `coagmentator_edit` |
| Publisher/live editor | Appropriate reader/editor set plus `publish_posts`/`publish_pages` | Add `coagmentator_publish`; actual publication needs independent approval under strict policy |
| Trash operator | Only needed `delete_posts/pages`, `delete_others_posts/pages`, `delete_published_posts/pages`; private equivalents only when opted in | Add `coagmentator_trash`; live Trash also publisher profile |
| Taxonomy editor | Exact native primitive(s) reached from the enabled taxonomy's `edit_terms`, often shared category administration authority | Add `coagmentator_terms` |
| Media uploader | `upload_files` and the attachment creation mapping | Add `coagmentator_media` |
| Metadata/revision editor | Parent edit/private/published authority and approved metadata callbacks as applicable | Add `coagmentator_meta` and/or `coagmentator_restore`; live targets also publisher profile |

Private reads/edits are disabled by default. An operator must intentionally add the relevant `read_private_posts/pages`, `edit_private_posts/pages`, and, if needed, `delete_private_posts/pages`, plus configure private access. Do not assume role examples are the final effective permissions: inspect the target's registered mappings and test author/status combinations in Gate 2.

Never grant `manage_options`, `manage_privacy_options`, `edit_users`, `promote_users`, installation/update/activation capabilities, `edit_files`, `unfiltered_html`, `unfiltered_upload`, network administration or `coagmentator_approve` to the service user. Protected front/posts/privacy pages that demand higher native authority remain inaccessible to the corresponding destructive operation. The human approver holds `coagmentator_approve` plus the underlying native and relevant bridge operation caps under their separate interactive identity. The service-user exclusion from approval is explicit even if someone accidentally grants that cap.

## Prevent native-API bypass

Core editing primitives can authorize writes outside the bridge even when the custom bridge role is read-only. Application Passwords identify users; they do not inherently limit a credential to these 21 endpoints. The bridge package therefore includes a minimal **must-use guard** installed before any credential is issued. This is a Gate 2 requirement, not optional deployment hardening.

The guard keeps the configured service-user IDs and approved Application Password UUIDs in operator-controlled configuration, independent of removable feature handlers. For those identities it:

1. Rejects XML-RPC and other non-REST Application Password use.
2. Rejects every REST route/method except the explicit Coagmentator route table. Reject encoded/normalized path ambiguities, `_method`/method-override tricks, core batch requests, nested internal REST dispatch, plugin/Abilities endpoints, user self-edit and Application Password management routes.
3. Requires the approved Application Password authentication event for bridge access, refusing cookie or alternate-auth shortcuts.
4. Disallows service-user interactive login and human-approval actions, and rejects direct use if feature handlers/configuration are absent or invalid.

Ordinary public anonymous WordPress traffic is unaffected. The guard does not grant any permission; each allowed bridge handler still runs all checks. An operator must revoke credentials before uninstalling the guard. If someone removes all guard code while leaving a credential active, the credential regains its user's ordinary API reach. Treat that as a deployment breach and residual trusted-operator risk, never describe the credential itself as natively route-scoped. Gate 5 must attempt these bypasses before any production connection.
