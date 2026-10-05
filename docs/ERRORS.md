# Normalized Errors

Gate 1 design candidate, 2026-10-05. This contract is shared by the bridge and MCP server. A failure code states the operation's outcome, not merely the HTTP transport result.

## Internal bridge envelope

```json
{
  "ok": false,
  "contract_version": "1.0",
  "correlation_id": "e197fc08-9f9e-4135-a64d-7aef47122ba4",
  "site_id": "f3858c09-8c56-48fd-99d1-ab75862bb955",
  "error": {
    "code": "VERSION_CONFLICT",
    "message": "The resource changed. Read it again before submitting a new operation.",
    "origin": "bridge",
    "retryable": false,
    "retry_after_seconds": null,
    "write_state": "not_applied",
    "details": {
      "fields": [],
      "reason": "stale_version",
      "approval": null
    }
  },
  "receipt": null
}
```

Every illustrated field is required. `site_id` may be null for a failure before a site binding exists. `origin` is `mcp`, `bridge`, `wordpress`, or `transport`. `details.fields` is at most 20 `{field: string, reason: string}` entries with schema-known field paths and stable reason tokens, never rejected values. `details.reason` is a short, allowlisted machine token or null. `details.approval` is `BridgeApprovalInfo | null` from [CONTRACTS.md](CONTRACTS.md). `receipt` is null before execution, or the observed failed/unknown receipt after a mutation. `Error` means the inner `error` shape in this document; `BridgeFailure` means this complete internal envelope. Reject unrecognized upstream error properties.

`write_state` is always `not_applied`, `applied`, `partial`, or `unknown`. Reads always use `not_applied`. An error after a mutation must not default to `not_applied`. `retryable` means the operation may be reconsidered after a transient condition; it never authorizes automatically replaying a write or generating a new key. Only `RATE_LIMITED`, transient `UPSTREAM_UNAVAILABLE`, `RESOURCE_BUSY`, and `MUTATION_IN_PROGRESS` may set it true; uncertain writes require lookup/reconciliation first.

## Codes and translation

| Code | HTTP at component detecting it | Meaning / client recovery |
| --- | --- | --- |
| `AUTHENTICATION_REQUIRED` | 401 | No acceptable authentication for this boundary. Reauthenticate at that boundary; MCP provides OAuth challenge |
| `AUTHENTICATION_FAILED` | 401 | Invalid, expired or revoked credential. No distinction between unknown username and wrong password |
| `AUTHORIZATION_DENIED` | 403 | Valid principal lacks scope, capability, operator enablement or approval authority; internal profile/version mismatch fails closed with `details.reason: policy_mismatch`. Insufficient OAuth scope includes the scoped Bearer challenge; native-cap denial does not trigger an OAuth loop |
| `VALIDATION_FAILED` | 400 | Invalid field/type/combination, stale internal first-seen timestamp, rejected file source/URL/MIME, unsupported content markup, or missing mandatory value; list only safe field/reason details |
| `NOT_FOUND` | 404 | Absent or concealed resource, wrong content type, revision parent mismatch, inaccessible mutation ID. Same response for existence-sensitive denials |
| `VERSION_CONFLICT` | 409 | Optimistic precondition or source revision version no longer matches; read again, review corrected intent, omit the old handle so Coagmentator allocates a new request |
| `STATE_CONFLICT` | 409 | Wrong current lifecycle state, duplicate explicit term/slug, hierarchy conflict or incompatible resource relationship |
| `SITE_MISMATCH` | 409 | Configured site, envelope or bridge response identity differs; stop all forwarding, operator must repair configuration |
| `IDEMPOTENCY_CONFLICT` | 409 | Same operation key with different intent; never silently overwrite its journal row |
| `APPROVAL_REQUIRED` | 403 | Strict profile requires an unexpired independent approval; include the bound review link, no write has occurred |
| `APPROVAL_REJECTED` | 403 | Human declined; do not resubmit automatically |
| `APPROVAL_EXPIRED` | 409 | Approval expired or binding invalidated; a new reviewed request is necessary |
| `UNSUPPORTED_OPERATION` | 422 | Known operation unavailable for this feature/status/provider/contract; e.g. disabled Trash, SEO, decoder, unconfigured client-file source profile or revision support. Unknown route is 404, not a generic-dispatch fallback |
| `RESOURCE_LIMIT` | 413 | Body, decoded media, result, scan or processing budget exceeded; no partial-success truncation |
| `RATE_LIMITED` | 429 | Admission ceiling reached; bounded positive `retry_after_seconds`, mirrored to `Retry-After` |
| `RESOURCE_BUSY` | 409 | Active editor or bridge lock; don't break it. Retry only after fresh read/authorization |
| `MUTATION_IN_PROGRESS` | 409 | Original key already executing; inspect `get_mutation`, do not compete for execution ownership |
| `WORDPRESS_INTERNAL_ERROR` | 500 | Unexpected WordPress API/hook/storage failure; preserve actual write state, keep tracing in protected logs |
| `INTERNAL_ERROR` | 500 | Bridge/MCP programming, policy storage or journal failure; fail closed, no raw exception |
| `UPSTREAM_AUTHENTICATION_FAILED` | 502 at MCP | Bridge returned an auth failure using stored WordPress credentials; operator rotates/repairs them. Do not ask the client to provide a WordPress password |
| `UPSTREAM_UNAVAILABLE` | 502 or 504 | Connection failure or deadline, known before any mutation was sent, or a read failure. Reads can use bounded retries |
| `WRITE_OUTCOME_UNKNOWN` | 502 or 504 | Write may have crossed the side-effect boundary, response missing/malformed, or crashed execution; `write_state: unknown`, retryable false, retain original key |
| `VERIFICATION_FAILED` | 500 | Write/readback disagrees or verification cannot finish; include observed receipt and accurate `applied`, `partial`, or `unknown` state. Never a successful mutation |

Failure HTTP mapping is preserved between bridge and MCP except boundary-specific credential translation and network uncertainty. In particular, a bridge 401 becomes `UPSTREAM_AUTHENTICATION_FAILED` in a tool result, not a client OAuth 401. A bridge 403 for capabilities remains authorization denial. A proxy HTML response, invalid JSON, wrong contract/site/key, or untrusted receipt becomes upstream failure for reads, unknown outcome for a submitted write. Never infer non-execution from an HTTP 500.

Before JSON parsing, a reverse proxy or TLS stack may produce a response outside this envelope. The MCP server normalizes it with a new correlation ID if necessary; it must not forward that body. An opaque upstream correlation may only be retained after UUID validation. HTTP status alone never establishes whether a mutation happened.

## MCP mapping and disclosure policy

The model `Failure` is a separate closed object: `{ok: false, request_id: UUID | null, error: ModelError, mutation: MutationEvidence | null}`. `ModelError` contains exactly the internal `Error` fields `code`, `message`, `retryable`, `retry_after_seconds`, `write_state`, and `details`, with `details.approval` projected to model `ApprovalInfo` (no internal ID). Project `details.fields` to external tool-schema paths only; envelope identity/timestamp/policy failures use a safe operator-configuration message without internal values or instructions for the model to repair them. Omit internal `origin`, envelope `contract_version`/`correlation_id`/`site_id`, and the full receipt. Keep the original upstream envelope/receipt in protected records; discard raw upstream error bodies.

Return the durable request handle after allocation, even for pre-write failures, approval challenges and unknown outcomes. Null is valid only before allocation or for ordinary read errors; a failed `get_mutation` may echo the caller's syntactically valid handle without asserting it exists. `mutation` is the exact useful projection of observed receipt evidence or null when no receipt exists, never a fabricated success. Lost/malformed bridge responses to a submitted write carry the persisted handle and `write_state: unknown`. A retrieval failure proven before bridge dispatch carries `not_applied`. A client-file URL, query string, file ID or rejected value is never reflected in an error.

Expected operation failures become MCP tool results with `isError: true`, this model `Failure` in `structuredContent`, and a short safe summary without extra tracing fields. OAuth transport failures use HTTP 401/403 with `WWW-Authenticate`; when a client supports tool-level auth challenges, provide `_meta["mcp/www_authenticate"]` as documented in [AUTHENTICATION.md](AUTHENTICATION.md). Malformed JSON-RPC, unknown method/tool, or protocol/header mismatch use the negotiated MCP protocol's own errors. Do not change protocol codes to project operation codes.

Use stable project messages and an allowlist translating WordPress errors; no pass-through `WP_Error` messages/data, traces, SQL, paths, user emails, tokens, authorization headers, request bodies, EXIF, private URLs or unbounded plugin responses. Missing native privileges that would disclose existence map to `NOT_FOUND` after basic authentication; a broadly missing operation scope/capability is `AUTHORIZATION_DENIED` before resource lookup. Logs use the same sanitized codes plus protected correlation metadata.

## Required later verification

Exercise every class at the boundary where it originates: expired client token, revoked bridge credential, denied native cap, invalid schema, hidden resource, stale version, duplicate key, approval lifecycle, disabled feature, rate/size limits, WordPress exception, lost response after write, and readback mismatch. Test loss both between MCP and bridge and between client and MCP; neither may create a second mutation. Cover strict/trusted policy mismatch and service self-approval denial, forged file URLs/redirects/DNS/size violations, trusted timestamp ownership and redaction of internal receipt/identity/time fields in both successful and failed model results. These are design requirements, not tests claimed as run in Gate 1.
