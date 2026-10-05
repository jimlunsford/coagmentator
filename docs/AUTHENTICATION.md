# Authentication and Client Compatibility

Accepted Gate 1 design. Research checked **2026-10-05** against the primary sources below. These are design requirements, not evidence of a working connection. No credentials, users, deployments or live MCP connections were created.

## Selected direction

Use two independent credentials at two boundaries:

1. AI client to MCP: user-delegated OAuth authorization-code flow with PKCE, through an established authorization server; Coagmentator is the resource server.
2. MCP to WordPress: dedicated service user and individually revocable WordPress Application Password over verified HTTPS, with the must-use guard in [CAPABILITIES.md](CAPABILITIES.md).

There is no credential/token passthrough and no assumption that OAuth scopes limit a WordPress Application Password. Scope and capability checks compose. A single configured OAuth `(issuer, subject)` maps to one operator ID, one fixed site and one service identity. An arbitrary valid user at the same issuer does not gain access. Multiple operators, tenants, sites and shared-account selection are outside this MVP.

## What the current sources establish

| Source | Observed fact relevant to this design |
| --- | --- |
| [OpenAI file/annotation reference](https://developers.openai.com/plugins/reference) and [tool planning](https://developers.openai.com/plugins/plan/tools) | File inputs use top-level `openai/fileParams` with `download_url`/`file_id` required and declared optional `mime_type`/`file_name`. Annotations describe actual effects; external hosting alone does not make a bounded account read open-world. They are client hints, never authorization |
| [OpenAI authentication](https://developers.openai.com/plugins/build/auth) | Authenticated remote integrations use OAuth with discovery, PKCE S256 and resource-bound tokens. Clients can use CIMD, pre-registration or DCR. Callback mode depends on issuer-identification support; copy exact values from the management UI. ChatGPT does not supply arbitrary custom API keys or machine-to-machine client-credentials grants for this flow. OpenAI-managed mTLS identifies the client platform, not the end user |
| [MCP 2026-07-28 authorization](https://modelcontextprotocol.io/specification/2026-07-28/basic/authorization) | The current `/latest/` resolved to `2026-07-28`. Protected-resource metadata and authorization-server discovery are part of the authenticated HTTP contract. Resource indicators, per-request bearer validation and audience checking are required. DCR is now deprecated for compatibility; CIMD is preferred |
| [MCP discovery](https://modelcontextprotocol.io/specification/2026-07-28/basic/authorization/authorization-server-discovery) | Resource metadata identifies the authorization server; OAuth or OIDC discovery describes its endpoints. The client must discover the issuer through that relationship |
| [MCP registration](https://modelcontextprotocol.io/specification/2026-07-28/basic/authorization/client-registration) | Pre-registration remains a supported client-identification mechanism alongside CIMD and legacy DCR |
| [MCP Streamable HTTP](https://modelcontextprotocol.io/specification/2026-07-28/basic/transports/streamable-http) and [versioning](https://modelcontextprotocol.io/specification/2026-07-28/basic/versioning) | The current revision carries metadata per request, without a connection initialization handshake or protocol session. It has request-scoped JSON/SSE replies; old handshake-based clients need an explicit compatibility adapter |
| [WordPress REST authentication](https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/) | Core supports Application Password Basic authentication over HTTPS. Cookie/nonce authentication is a separate logged-in browser path, not remote service authentication |
| [WordPress Application Password administration](https://developer.wordpress.org/advanced-administration/security/application-passwords/) | Credentials are tied to a user, stored hashed by WordPress, displayed once, revocable independently and intended for APIs rather than interactive login |
| [Current WordPress authentication function](https://developer.wordpress.org/reference/functions/wp_authenticate_application_password/) | Core authenticates the corresponding user and permits additional constraints via hooks; REST and XML-RPC are authentication entry points. Coagmentator must add its own route and credential restrictions |

All configuration defaults and stricter controls below are Coagmentator decisions derived from those facts. The 2020 [integration guide](https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/) was supplementary history only; its future-roadmap statements were not treated as current capability guarantees.

## WordPress service authentication

Application Passwords are appropriate for a self-hosted server calling a known WordPress instance: core integration, no reuse of a human login password, and independent revocation. Their weaknesses are persistent bearer-like reuse via Basic auth, user-level privilege scope, and exposure if the MCP host is compromised. Custom JWT authentication would add another issuer/plugin dependency without removing those risks. WordPress OAuth is not required for the single fixed service identity; revisit if per-user delegated WordPress identities are introduced later.

Requirements:

- Provision the must-use guard and least-privilege role first. Bind the service-user ID and allowed credential UUID(s), not just a display name. Installers must verify the guard before allowing connection setup. Never create credentials from a tool.
- Store the reusable password only in a secret store or a service-readable secret file (owner-only permissions, outside the web root and repository). Inject at runtime; never place in URLs, CLI arguments, browser storage, tool schemas, client `_meta`, errors, fixtures, screenshots or work notes. Secret-bearing configuration and backups need access control and encryption at rest.
- WordPress stores a hash; the MCP service necessarily needs the reusable plaintext in protected runtime memory. This is not end-to-end protection against host compromise.
- Use a distinct password per environment/integration. Rotate at least every 90 days and immediately after suspected disclosure, operator departure or host compromise. Planned rotation permits two explicitly listed UUIDs for at most 24 hours: install new secret, verify with a read, revoke old, verify old is rejected. Emergency revocation has no overlap. Rotation is an out-of-band operator procedure, not an MCP tool.
- Require valid hostname/certificate chain and TLS 1.2 or newer at both boundaries. Never disable verification, downgrade to HTTP or follow redirects with credentials. An explicitly configured private bridge address may be used, but is fixed by the operator, not supplied by tool inputs.
- Accept forwarded TLS/host information only from an allowlisted reverse proxy that overwrites client-supplied forwarding headers. A forged `X-Forwarded-Proto` must not satisfy the HTTPS requirement.
- If core disables Application Passwords, the credential is missing/revoked, headers are stripped or identity introspection cannot be established, fail closed. Do not fall back to cookies, a human password, anonymous public REST, or a different service account.

WordPress nonces protect the separate browser approval POST against CSRF. They are not passwords, per-request uniqueness guarantees, replay protection, or evidence that the AI user approved a mutation. Deduplication and exact-intent approval come from bridge records, not nonce semantics.

## MCP transport and protocol profile

Expose a fixed HTTPS `/mcp` endpoint with Streamable HTTP. Support exactly `2026-07-28` and the `2025-11-25` compatibility profile; no legacy `2024-11-05` HTTP+SSE endpoint, unauthenticated stdio relay or automatic protocol downgrade on an auth failure. An official TypeScript SDK must handle protocol details when implementation starts; pin its version and verify both profiles in Gate 4. Do not infer supported revisions from an old OpenAI code snippet.

| Profile | Coagmentator obligation |
| --- | --- |
| `2026-07-28` | Handle each request independently. Validate required body metadata against `MCP-Protocol-Version`, `Mcp-Method` and, where required, `Mcp-Name` headers through the SDK. Reject missing/mismatched headers using that revision's errors. No session-ID authority, initialization dependence, GET stream, or resumable event stream. Ordinary completed tools return `resultType: complete` |
| `2025-11-25` | Support its `initialize`/`notifications/initialized` and negotiated-version behavior through a separate SDK compatibility path. Authenticate every call, including initialization. Do not require modern mirror headers for this era. Use stateless legacy request handling where possible, with no issued session ID; GET streaming and DELETE session termination may return 405 as that revision permits |

The legacy [transport specification](https://modelcontextprotocol.io/specification/2025-11-25/basic/transports) remains normative for that path. Unknown versions fail with the applicable protocol error and supported versions, never with a project tool success. No sampling, roots, prompts, subscriptions, task execution or tool-driven elicitation is needed for the MVP. A bounded JSON response is enough for these synchronous tools. If an SDK emits SSE, bound its lifetime and do not buffer final responses indefinitely at the proxy.

Validate a present `Origin` against an operator allowlist; reject an invalid origin with 403. Authenticated non-browser clients may omit it. Do not use permissive credentialed CORS. Network controls can allowlist ChatGPT egress or validate OpenAI-managed client certificates, using OpenAI's current CA guidance; these supplement OAuth. Other clients must explicitly pass the same OAuth/scoping profile rather than inherit access from the network.

## OAuth resource and provider contract

The canonical resource is the complete configured HTTPS MCP URL, for example `https://mcp.example.com/mcp`. The examples here use reserved illustrative hosts. Never derive the audience from an untrusted Host header. Pin one issuer, allowed algorithms, its discovery/JWKS or introspection endpoint, the authorized subject and the site binding in operator configuration.

| Endpoint / metadata | Owner and exact obligation |
| --- | --- |
| `GET /.well-known/oauth-protected-resource/mcp` | Coagmentator serves RFC 9728 metadata for the path-specific `/mcp` resource. Include exact `resource`, the single pinned `authorization_servers` issuer, and `scopes_supported: ["coagmentator.read"]` as the minimum initial scope. Additional scopes are requested on operation challenges |
| `WWW-Authenticate` | MCP 401 challenge identifies the absolute resource metadata URL and required scope; 403 insufficient-scope challenge names additional required scopes. No WordPress credential details |
| `/.well-known/oauth-authorization-server` or issuer-correct `/.well-known/openid-configuration` | Established provider serves discovery with exact `issuer`, `authorization_endpoint`, `token_endpoint`, `code_challenge_methods_supported: ["S256"]`, supported client authentication methods, grant/response types, supported scopes and signing-key discovery where applicable |
| Authorization endpoint | Provider handles user login/MFA, explicit client-and-scope consent, exact registered redirect URIs, single-use short-lived codes, PKCE S256, and resource binding. No implicit/password/client-credentials grant for ChatGPT linking |
| Token endpoint | Provider validates code, redirect, PKCE and `resource`, then issues an access token only for the configured MCP audience; refresh tokens, if offered, rotate with reuse detection |
| Revocation / signing keys | Provider manages revocation and key lifecycle. MCP validates token state per the selected access-token profile below, with local emergency deny controls |

Provider selection is a deployment/dependency choice, **not** permission to invent authentication. Prefer a maintained self-hostable implementation with an externally configured, pre-registered ChatGPT client as the required MVP path. Use PKCE with a public client (`none`) or a supported confidential-client method configured exactly in provider metadata. No Coagmentator-built authorization server, login password database or ad hoc shared bearer token.

CIMD is the preferred additional mode when the selected provider supports it safely. Enable it only for operator-approved client metadata URLs and redirects, with bounded HTTPS fetches, no private/link-local destinations or redirects, DNS checks at connection time and metadata size/time limits. DCR is disabled in the MVP; it is not required merely because older tutorials used it. An established provider missing a mandatory feature cannot be made compliant by claiming that feature in metadata.

Require RFC 9207 issuer identification: provider metadata advertises `authorization_response_iss_parameter_supported: true` and success/error responses include the exact issuer. During actual setup, copy the ChatGPT client ID/CIMD and callback URI displayed for that connection and permit those exact values. Do not assume one global callback string or use wildcard redirects. Gate 5 tests the platform's selected callback mode. These details follow the [current OpenAI auth guide](https://developers.openai.com/plugins/build/auth) and [MCP authorization-response rules](https://modelcontextprotocol.io/specification/2026-07-28/basic/authorization); platform UI behavior is not reproduced inside Coagmentator.

The provider may issue signed JWT access tokens (RS256 or ES256, explicitly configured, no `none`, token-supplied JWK URL or algorithm substitution), or opaque tokens validated by authenticated introspection at the pinned provider. This is an explicit two-profile contract, not arbitrary token acceptance. One profile is selected per deployment. For either, require issuer, exact configured audience membership, expiry, not-before where supplied, scopes and the authorized subject. Access-token lifetime is at most five minutes, clock allowance at most 60 seconds. Opaque-token positive introspection caching is capped at 30 seconds and token expiry; failures deny access. JWT revocation is bounded by expiry unless a local subject/token deny entry is applied. A local emergency binding-disable switch denies new requests immediately. IdP logout alone is not guaranteed immediate JWT revocation.

Initial linking grants `coagmentator.read`. Write scopes are exactly `coagmentator.edit`, `.publish`, `.trash`, `.terms`, `.media`, `.meta`, and `.restore`, with the full prefix on every scope. No wildcard or `admin` scope. Step-up asks for the operation's scopes from [CAPABILITIES.md](CAPABILITIES.md), including the conditional publish scope for live changes. Enabling a WordPress capability does not grant OAuth consent, and granting OAuth consent does not enable bridge writes.

## Platform versus Coagmentator responsibilities

| Participant | Responsibilities |
| --- | --- |
| ChatGPT or compatible client | Discover metadata, identify/register its OAuth client, manage PKCE/state and callback validation, run login/consent through the provider, store its tokens, attach access tokens, render tool results and any client approval UI |
| Authorization provider | Login/MFA, redirect and client validation, issuer response, code/token/refresh handling, audience and scope issuance, discovery, signing keys/introspection, revocation |
| MCP server | Verify every request token and binding; enforce operation scopes/configuration and limits; provide protected-resource metadata/challenges; publish per-tool `securitySchemes`; validate schemas; translate to the fixed WordPress target; protect service secrets; validate full receipts and project useful model evidence; own trusted request IDs/timestamps and controlled client-file retrieval |
| Bridge / must-use guard | Validate dedicated credential, route and site identity; enforce native/custom capabilities and the operator-pinned approval profile; own idempotency, WordPress mutation/readback and audit state |

Set per-tool OAuth `securitySchemes` with that tool's static required scopes; handle conditional live scopes at runtime. OpenAI tool-level auth recovery additionally uses `_meta["mcp/www_authenticate"]` with a sanitized Bearer challenge containing `error` and `error_description`. HTTP challenges remain the baseline for other clients. No tool has a `noauth` alternative. Public OAuth metadata exposes no site content.

`tools/list` may describe operator-enabled tools and their scope requirements to a valid read-authorized actor even before step-up, but must not expose disabled families. Calls remain fully authorized. `site_info.available_tools` reports tools currently available under the caller's scopes/configuration; resource-specific capability checks still occur when called. Optional profile/account UI is not required for this single-binding MVP and does not add a 22nd tool.

## Client files and approval policy

The reference ChatGPT media path uses the `ClientFile` schema and `_meta["openai/fileParams"]` in [CONTRACTS.md](CONTRACTS.md); the model does not send base64 or compute input digests. MCP performs the bounded retrieval and server-side digest, while WordPress receives only the internal byte envelope and independently sanitizes it. Optional filename/MIME hints are not trusted file identity. The OpenAI reference establishes the schema, not a cryptographic origin proof or permanent download-host allowlist.

An operator-reviewed file-source profile is required before any client registration may retrieve files. Validate registration identity from provider-verified token/introspection data when selecting the profile; never trust `openai/userAgent`, a session value, Origin or a claimed client name. All callers, including valid direct non-ChatGPT callers, face the same exact destination, DNS, redirect, size and decoder restrictions. Unsupported file sources fail closed; do not infer compatibility or relax SSRF controls from OAuth success. OpenAI-managed mTLS can add platform assurance where available, but does not authorize a file or replace these checks.

`strict` and `trusted_single_operator` approval profiles share OAuth and WordPress authorization. The strict WordPress cookie/nonce approval session remains independently owned and inaccessible to the service identity. The trusted reference workflow relies on standing server-side authorization plus client confirmation behavior for enabled families. Such confirmation is a UX safeguard, not a signature or proof against a compromised MCP host. Profile selection and family enablement are protected out-of-band operator configuration, never tool arguments or token scope side effects.

## Required evidence in later gates

- Gate 2: authentic Application Password identification, wrong/revoked credential denial, guarded alternate routes, each native-capability denial, no cookie/nonce-only bridge access, no accidental service approval rights.
- Gate 4: provider conformance evidence and exact dependency pins, both protocol profiles, discovery paths, PKCE S256, issuer mismatch/missing issuer, audience mismatch, token expiry, signing-key rotation, scope step-up and local emergency disable; trusted ID/timestamp ownership, durable lost-result recovery, model/audit projection, each annotation, and adversarial file-source/SSRF/streaming-limit handling. No real production identity required.
- Gate 5: one actual non-production ChatGPT connection, precise observed protocol/callback/auth mode, read/write round trips in both profiles, strict approval and service self-approval denial, actual file-parameter handoff, and hostile direct-client file-URL rejection. Test other clients only if explicitly added to the support matrix. Documentation does not establish account-specific UI entitlement or successful interoperability.
- Gate 6: separately authorized production provisioning, rotation/revocation drill and target-specific feature compatibility. Recheck current authoritative docs before implementation/connection; if requirements changed, revise the affected contract and decision before proceeding.

No unresolved authentication architecture is delegated to Gate 2. Provider product, dependency versions and real callback values are intentionally selected at their implementation/deployment gates within this fixed profile. Failure to meet the profile blocks that gate rather than weakening it.
