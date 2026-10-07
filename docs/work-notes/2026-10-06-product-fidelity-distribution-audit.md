# Work Note: Product Fidelity and ChatGPT Distribution Architecture Audit

Date: 2026-10-06 America/New_York; sources retrieved 2026-10-07 UTC
Roadmap gate: Emergency product/distribution review, before further Gate 2 implementation
Status: COMPLETE research; implementation remains FROZEN pending owner review

## Goal

Determine whether Coagmentator can deliver the owner-authorized normal ChatGPT connection experience for independently hosted WordPress sites without Developer Mode, a metered gateway, or a hosted SaaS operational relay. Evaluate distribution before choosing protocol plumbing. Record findings only; this note does not accept a new architecture or amend historical doctrine.

## Starting state

Repository: `jimlunsford/coagmentator`.

| Checkpoint | Observed HEAD | Observed complete tree |
| --- | --- | --- |
| Accepted main | `1053487d627622145b19232f1756643bada0b74c` | `dbe5eac1d9787e6fca71c78606639f8358606a65` |
| Unaccepted `feature/gate-2-c03c-admission-audit-concurrency` | `c4e2f01e90e60f4993473bb89256cdd71f5361cc` | `0f6d1578ee66fa0190c37697cb5f04200184569d` |
| C03C tested implementation | `f1b09e82e610e2a7362c63338ef5b3e31878b739` | `8dce0fce188be335465d57d60f33988cb1cb12ec` |

Live GitHub branch/commit/tree reads matched the supplied checkpoints. Open PR search returned zero. Gate 0, Gate 1, Gate 2 preparation, C01, C02, C03A and C03B are accepted. Gate 2 and C03 remain in progress. C03C is PARTIAL / BLOCKED / UNACCEPTED. C03D, C04 and Gate 3 have not started. Main's reference to C03C as unstarted describes accepted main, not the separate unaccepted candidate. Some historical checkpoint paragraphs still describe their then-next checkpoint as unstarted; those do not override later explicit acceptance and closeout records.

The main tree inventory and plugin loader/foundation confirm no runtime bridge read controller, TypeScript MCP implementation, or WordPress mutation implementation. Repository records report no production service identity, Application Password, OAuth setup, deployment or ChatGPT connection. This audit did not contact production to independently inventory it; claiming a live production audit would be false. No evidence contradicting the supplied production state was found.

Live Actions metadata confirms [run 37539689554](https://github.com/jimlunsford/coagmentator/actions/runs/37539689554), attempt 1, completed with failure at the tested implementation. The C03C handoff records substantially successful assertions followed by failed removal of the worker-owned bind-mount root under sticky `/tmp`. The failure is real; functional evidence does not make the checkpoint accepted. No fix, rerun or acceptance occurred here.

## Work completed

Read the required main documentation, inception, Gate 0/1 and Gate 2 preparation histories, C01 through C03B closeouts, and both specified C03C documents at their exact checkpoints. Inspected runtime entry points, tree inventory, workflow triggers and repository rulesets. Researched current primary OpenAI distribution/authentication documentation, newer plugin mechanisms, WPVibe's published architecture/pricing, the official WordPress adapter release and source, and WordPress Abilities behavior. No implementation or executable integration experiment was performed.

### Explicit conclusions

| Decision output | Classification |
| --- | --- |
| Product fidelity | **PROJECT IS PARTIALLY TRUE BUT HAS A CRITICAL DISTRIBUTION GAP** |
| OpenAI distribution viability | **ONLY A CENTRAL RELAY IS CURRENTLY GENERALLY PUBLISHABLE** |
| WordPress MCP Adapter impact | **CONTROLLED PIVOT TO OFFICIAL ADAPTER RECOMMENDED** |
| Overall next direction | **FREEZE IMPLEMENTATION PENDING OPENAI DISTRIBUTION ACCESS** |
| Is a trusted OpenAI relationship truly required for the required product? | **PUBLIC DOCUMENTATION IS INSUFFICIENT TO KNOW** |

The distribution classification is scoped to **one public Coagmentator listing serving independently hosted installations**, using the documented remote-MCP publication models. It means the only generally documented operational pattern found for that scope is a universal service relaying site requests. It does not mean a fixed endpoint cannot serve its own single site directly, or that an undocumented routing mechanism is technically impossible. Restricted templates are a promising direct path, but their suitability for arbitrary owner domains and personal accounts is not established.

OpenAI currently does not publicly document a generally available way for this one public listing to send normal users directly to arbitrary independent self-hosted MCP endpoints. A universal relay is not an acceptable fallback under the current mission. Template capability is the only documented variable-endpoint publication mechanism found, but obtaining it has not been shown sufficient to preserve every requirement. Implementation should remain frozen until the product's distribution permission and endpoint model are confirmed.

The adapter recommendation is a **conditional technical direction**, not permission to pivot now. Prefer a dedicated adapter server with explicit Coagmentator abilities after distribution access and authentication/media fit are demonstrated. Preserve the two-tier option if that bounded evaluation shows it is safer or easier to operate.

### Original product and what project memory preserved

The owner-authoritative requirements supplied for this audit are the original product truth. Missing text in the repository is a memory defect, not evidence that the owner authorized a different product.

The intended experience is: install/configure Coagmentator for a WordPress site, discover/connect the public Coagmentator offering in normal ChatGPT, authenticate, and use a narrow editorial surface. Ordinary users must not enable Developer Mode or manually create a developer integration. Development and private integration testing may use it.

The project was created to replace a metered third-party AI-to-WordPress bridge for JimLunsford.com with owner-controlled infrastructure. It must remain portable, open source and self-hosted-first. It excludes mandatory third-party per-operation/day metering, paid hosted gateways, hosted SaaS operational relays, billing and subscriptions. Owner-operated resource protection is not vendor usage monetization. Neither self-hosting nor this requirement eliminates ChatGPT's own account limits or ordinary hosting costs.

The repository successfully preserves explicit editorial tools, fixed site/actor binding, WordPress final authority, least privilege, independent bypass prevention, server-side approvals, verified writes, honest partial/unknown outcomes, safe content/media, and the prohibition on arbitrary shell, SQL, PHP, WP-CLI and filesystem administration. D-007 also preserves independent implementation rather than copying WPVibe.

The lost requirement is a shipping distribution contract: **one ordinary ChatGPT discovery/connect experience across independent installations, without Developer Mode or an operational SaaS relay**. `PROJECT.md` generalizes the client to AI clients through MCP. D-005 chooses separate MCP/WordPress responsibilities; D-009 fixes each instance to a site; D-010 solves two credential boundaries; D-014 solves protocol revisions. None establishes how the public listing selects that instance.

### Exactly where the process failed

1. **Gate 0 captured infrastructure and security, but omitted the shipping journey.** The inception note records replacing the metered bridge and selecting two components. It does not preserve no-Developer-Mode public onboarding. Gate 0 acceptance checks repository/docs/handoffs, not the user's successful connection journey.
2. **Gate 1 ratified a technically detailed architecture without distribution feasibility.** PR #1 accepted candidate `3a0979faf3156a94173c73b89e8861e3d8816594`, tree `bf7db607bf913f727b5e24de9972bc06c0e7f6c5`, merged as `4a99e61fc920983da86c53a0b2b47f5a9b5a7c2d`. The checklist covers contracts, OAuth, threats, file handoff and internal consistency. It has no public-listing/per-install endpoint/access condition. The statement that no unmade architecture choice blocks review was too strong.
3. **Gate 5 became the first real ChatGPT check.** Actual client integration belongs there, but discovering whether the product can be distributed belongs before implementation. Gate 4's SDK/OAuth compatibility is not publication approval. Gate 5's actual non-production connection and Gate 6's reference-site acceptance do not specify public installation with Developer Mode off.
4. **Yes, current acceptance criteria could declare success while ordinary users still require Developer Mode.** They can demonstrate the tools through a custom connection without demonstrating the required shipping journey. No accepted decision explicitly authorizes that tradeoff; it is an omission, not an intentional product change.
5. `docs/PROJECT.md` should own the immutable journey and economic constraints. `ARCHITECTURE.md` should own endpoint selection and central-component boundaries. `ROADMAP.md` should require distribution evidence in Gate 1 before Gate 2. `AUTHENTICATION.md` should separate resource selection from authentication. `DECISIONS.md` should record any approved supersession.
6. A new blocking distribution feasibility checkpoint must now precede **C03C correction, C03D, C04, Gate 3 and Gate 4**. Later Gate 5/6 checks must use the approved shipping path, supported account types, independent domains and Developer Mode off. Future acceptance should trace every mandatory product invariant to a source, responsible component and concrete evidence, with an explicit failed/unknown result rather than an implicit pass.

This is process correction without blame. Historical acceptance remains factual. This research note does not rewrite it.

### Current OpenAI publication and user experience

The current documentation describes plugins as packages that can contain skills and connected apps. Users browse the public directory, install, connect and authorize the included service. Directory visibility spans plans, but availability depends on the particular capability, surface, account, region and workspace policy. A public installation does not require the user to author a custom MCP connection. [O2, O3]

The public submission flow accepts a plugin ZIP, verifies the publishing identity and endpoint domain, scans the remote tool definitions, reviews the package and requires publication after approval. Current upload constraints include one connected MCP server per plugin; local lifecycle hooks and bundled references to existing apps are not equivalent public remote-MCP submissions. Domain verification uses the displayed challenge at `/.well-known/openai-apps-challenge` on the permitted host. A reachable review account and representative tests are required. This is a review path, not guaranteed approval or template entitlement. [O1, O2]

For public remote tools, a stable HTTPS service is the baseline. Current review guidance treats an origin change as a new plugin and a path change as a new-version matter. The submission page instead says the current update flow cannot change the URL and directs developers to support. That live discrepancy does not establish runtime origin switching. Ordinary account selection chooses credentials, not a documented replacement server. [O1, O2, O6]

| Surface/account | What current evidence supports | What it does not prove |
| --- | --- | --- |
| Free / Go | Public directory available; individual plugin capabilities vary. Extension web support is described as coming soon for these plans. | Generic arbitrary-host template access, all writes/files on every surface, or Coagmentator eligibility. |
| Plus / Pro personal | Public plugins; Snowflake and Databricks provider-specific template guides include Plus/Pro. | A self-service third-party Coagmentator template or arbitrary WordPress origin support. |
| Business | Admin controls and workspace-specific apps; members can use published workspace apps. | One public listing for unrelated personal accounts. |
| Enterprise / Edu | Managed configuration, role controls and action/access policies. | A developer's entitlement to publish a generic template. |
| Custom MCP connection | New API guide documents manually entering a server URL and creating/installing a plugin with read/write tools. | Public discovery of one configurable Coagmentator listing. The guide omits a Developer Mode toggle and does not establish universal account entitlement. |
| Legacy Developer Mode path | Help documents developer/admin testing and workspace publication; its Pro section still requires Developer Mode for custom read/fetch apps. | A restriction on all public-plugin writes or proof that newer custom-plugin creation has identical availability. |

Sources: [O3, O4, O5, O8, O9, O10, O12]. The custom-MCP API guide and legacy Help article are not perfectly synchronized. Do not flatten them into “all MCP requires Developer Mode” or “all personal custom MCP writes are now generally available.” This uncertainty does not solve the one-listing requirement. A manually authored per-site connection is specifically outside the required shipping journey even if a particular account no longer shows that toggle.

Authenticated private data and writes require the documented OAuth resource-server contract. WordPress Application Password support alone does not supply it. OpenAI documents PKCE, metadata discovery, resource binding, token checks and client registration options. CIMD can simplify client registration; it does not confer publication access or change the MCP destination. Enterprise domain restrictions can require OIDC verified email/UserInfo support. [O6]

Tool annotations describe behavior; the server must enforce authorization. Confirmation depends on app permissions, action and context. It is not a cryptographic authorization receipt. The existing strict/standing-policy distinction remains necessary. [O4, O7]

File inputs use top-level `_meta["openai/fileParams"]` fields. Each file object declares `download_url`, `file_id`, `mime_type`, and `file_name`; only the first two are required within the object. Receiving a temporary URL is not permission to fetch arbitrary URLs. Keep bounded retrieval, source validation, no redirects, decoding/re-encoding and the separate media acceptance proof. [O7]

### Template MCP URLs and restricted access

The review page requires a real review endpoint and a corresponding template. Variables use unique `{name}` placeholders, beginning with a letter and containing letters, numbers or underscores. Its example varies a workspace subdomain under one provider domain, and describes workspace-admin configuration. It says exactly:

> We only support template-based URLs for trusted developers with whom we have an established relationship.

This is separate from routine individual/business identity verification. The published grammar does not establish permission for an arbitrary registrable domain, entire origin, varying WordPress subdirectory or site-owned OAuth issuer. It also does not state that variables are limited to subdomains. Both blanket claims would go beyond the evidence. [O1]

General managed-template guidance says those templates do not appear in personal workspaces. However, the Snowflake guide explicitly includes personal Plus/Pro users and a full managed MCP URL with account/region host prefix, database, schema and server components. Databricks also documents a personal Plus/Pro path. These are provider-specific exceptions, not proof of a generic template publisher program. GitHub Enterprise demonstrates organization-specific hosts but uses its own provider integration. Do not infer arbitrary WordPress-host eligibility from those examples. [O8, O9, O10, O11]

OAuth-backed templates and actions are documented for providers. No blanket template prohibition on writes was found. Conversely, no source establishes that a new Coagmentator template would support its full write/file contract on every intended plan. That remains a qualification and compatibility question.

**OPENAI DOES NOT PUBLICLY DOCUMENT THE QUALIFICATION PATH**

The reviewed primary sources and targeted searches do not define trust criteria, an application form, an independent open-source eligibility rule, an invitation-only rule, a normal-review-to-template progression, timeline, approval probability, or whether entitlement attaches to a person, organization, plugin or server domain. The wording describes a relationship with developers; it does not define its administrative scope. No authoritative example found proves an independent third-party open-source publisher obtained arbitrary-host templates through a reproducible public process.

A general OpenAI support route for submission questions is documented. It is not a documented template application or a promise of access. Identity verification, a verified badge, enhanced distribution and restricted template entitlement are different concepts. This audit contacted nobody and created no submission. The negative finding is bounded to the cited public sources and searches, not proof that no private program exists.

Trusted-relationship conclusion: **PUBLIC DOCUMENTATION IS INSUFFICIENT TO KNOW** whether the entire required product can ship. A relationship is explicitly required if Coagmentator uses the documented template mechanism. It is not yet proven that access would cover arbitrary site domains and personal accounts, nor that no other future mechanism could. Therefore access and endpoint eligibility must become prerequisites, not assumptions funded by months of implementation.

### Every investigated route, including newer mechanisms

Classifications concern the described mechanism and its ability to satisfy the requested public product. “UNCLEAR” means no authoritative support contract found, not a claim of technical impossibility.

| Candidate route | Classification | Central operational relay? | Evidence and result |
| --- | --- | --- | --- |
| Public universal MCP service selecting a WordPress site per authenticated account | REQUIRES CENTRAL RELAY | Yes | Standard remote publication plus server-owned site routing. Solves directory UX but violates current relay invariant. [O1, O2, V2] |
| Public Template MCP URL | SUPPORTED BUT RESTRICTED | Not inherently | Actual templated site MCP can be the destination. Generic arbitrary-domain/personal eligibility remains unproven. [O1, O8, O9] |
| Per-user arbitrary MCP URL field on a universal public listing | UNCLEAR | Would avoid relay only if host rebinds | No documented universal installation parameter found. Provider template fields are a different mechanism. Claude `userConfig` interpolation is explicitly not implemented for public plugin migration. [O13] |
| OAuth login selects another WordPress MCP resource | UNCLEAR | Yes if the original server forwards | OAuth authorizes access to an intended resource; it does not document replacement of the installed endpoint. [O6, S1, S2] |
| Universal endpoint redirects MCP to another host | UNCLEAR | Bootstrap only if destination truly persists; otherwise continuing routing dependency | HTTP semantics permit some redirects, but no ChatGPT publication contract was found for cross-origin per-user MCP rebinding. [O1, S1, S3] |
| A tool or onboarding skill installs a second arbitrary MCP connection | NOT SUPPORTED | No supported direct path established | No install/rebind client primitive in the reviewed plugin API. Onboarding invokes a packaged skill, not an endpoint registration API. User-driven custom creation is separate. [O5, O12, O14] |
| Native plugin settings storing a site URL | SUPPORTED AND DOCUMENTED for settings, not destination switching | Yes if same server uses it to call WordPress | Read/update settings are tools on the same MCP server; persistence belongs to that server. A URL value is application data. [O14] |
| Widget performs HTTP against the site | UNCLEAR as the proposed backend replacement | Potentially no central data relay for the browser request | Browser fetch, CSP/CORS, cookie context and widget lifetime differ from host MCP execution. `callTool` invokes the connected server's named tool, not an arbitrary MCP URL. No persistent backend rebind is documented. [O7, O12] |
| Legacy custom MCP developer integration | DEVELOPER MODE ONLY for the documented legacy route | No, can be direct | Suitable for development. Workspace publication can spare ordinary members the toggle, but needs per-workspace setup and lacks one public listing. [O4] |
| New “Add custom MCP server” flow | SUPPORTED AND DOCUMENTED as manual custom setup | No, can be direct | New guide permits URL entry and read/write tools, without mentioning the toggle. Does not turn a public listing into per-site configuration. [O5] |
| New plugin packages, skills and Git marketplaces | SUPPORTED AND DOCUMENTED in their supported surfaces | Not inherently | Instructions/package distribution is not generic remote endpoint provisioning. [O2, O3, O13] |
| Public plugin containing local MCP | UNCLEAR for generally available public distribution | No hosted relay inherently | Local MCP use is documented on Desktop. Public packaging guidance directs local-server publishers to an OpenAI contact if they cannot deploy a remote HTTPS endpoint. No general eligibility process was found. [O3, O13] |
| Secure MCP Tunnel | NOT SUPPORTED for public plugin distribution | Ongoing tunnel forwarding | Official guide explicitly excludes public submission/distribution. Private networking does not solve public installation. [O15] |
| ChatGPT Sites-hosted plugin | SUPPORTED AND DOCUMENTED for personal/workspace use | Yes if hosted tools forward WordPress operations | Server runs on Sites. Workspace sharing is supported; personal plugin sharing is restricted. This is neither direct WordPress execution nor one public arbitrary-host listing. [O16] |
| Desktop WebMCP/site tools | SUPPORTED AND DOCUMENTED on eligible accounts/pages | No third-party WordPress relay inherently | Tools belong to an open page in the built-in desktop browser; no separate connection, but disappear when the page closes. This is a real no-Developer-Mode alternative interaction model, not the specified public plugin connection. [O17] |
| Separate public plugin per site | SUPPORTED AND DOCUMENTED under ordinary publication rules | No | Each endpoint can be direct, but every owner would become a publisher with review/domain obligations. Fails one-listing/practical installation. [O1, O2] |
| Custom GPT actions or a separate Responses API client | UNCLEAR for this product's endpoint-switch requirement | A universal action backend still relays | Different packaging/client path. No current primary evidence found for one public plugin dynamically adopting arbitrary per-user MCP endpoints through these mechanisms. They cannot be credited as a solution by analogy. |

OAuth reasoning: protected-resource metadata identifies the resource and associated authorization servers. RFC 8707 resource indicators constrain intended token use. A separate identity provider is compatible with direct site traffic, but site selection in an authorization page is not an MCP connection update. Forwarding a token issued for the universal service to an unrelated site would break audience isolation unless a separately designed authorization contract explicitly permits the correct target. [S1, S2]

Redirect reasoning: 307/308 preserve a method; 301/302 may change POST behavior. Sensitive headers need origin-aware handling, not blind forwarding. New-host metadata, token audience, review/domain ownership and connection/session lifecycle would all need an explicit supported contract. Under the 2025 profile, session IDs cannot be assumed portable across servers; the 2026 sessionless profile removes that session issue, not authentication or publication constraints. No redirect experiment was performed. [S1, S3, W4]

Widget/onboarding reasoning: UI state may collect site preferences and an onboarding skill may explain setup, but neither creates a documented transport destination. The extensions source explicitly uses same-server settings tools. A browser-only WordPress interaction could be designed separately, but cannot be credited as a durable model-callable MCP backend or safe automatic file handoff without evidence. [O7, O14]

### Central bootstrap versus operational relay

| Central responsibility | Can remain outside ongoing WordPress tool traffic? | Consequence |
| --- | --- | --- |
| Static documentation/discovery metadata | Yes | Low-data distribution support; does not select a new installed endpoint by itself. |
| OAuth identity provider | Yes | Tokens may be issued centrally while calls go directly to the correct site. Availability/privacy dependency remains; owner-controlled/self-hostable choice preferred. |
| Installation registration/bootstrap | Yes, if the client has a supported final direct binding | The missing client-binding contract is decisive. Registration alone is insufficient. |
| Configuration storage | Yes in principle | If it only supplies configuration, not calls. Storage cannot instruct ChatGPT to rebind without a supported mechanism. |
| Repeated routing or redirects | Usually remains an operational dependency | Even without response-body relay, it is not one-time bootstrap when every call depends on it. Cross-host rebinding is unproven. |
| Reverse proxy / MCP termination / WordPress request forwarding | No | Operational relay regardless of ownership claims, source license, price or statelessness. |
| Central tool execution | No | Expands authority and data exposure beyond the site. |
| File/media forwarding | No | Relay with bandwidth, storage and malicious-file handling costs. |
| Audit service | Optional, but centralizes protected evidence if used | Keep mandatory journals/audits site-owned. Adapter diagnostics are not mutation receipts. |
| Usage meter | Not required technically | A commercial per-call/day dependency violates the mission; owner-side abuse limits remain necessary. |

Option E can be open source and operator-funded with no billing. That does not make it non-relay or guarantee unlimited free capacity. Costs include compute, ingress/egress, media bandwidth, credential storage, authentication, abuse response, patching, monitoring, backups and support. No defensible dollar forecast exists without load and operating assumptions. It adds centralized credential/content exposure, cross-tenant isolation duties, a shared outage point and ongoing funding pressure. It occupies WPVibe's architectural category even if its tool surface and economics differ. A central service that does only bootstrap is compatible in principle, but no supported post-bootstrap arbitrary endpoint transition was established.

### WPVibe reference experience and economics

WPVibe's client-specific guide documents its directory listing: connect, email-code sign-in, authorize the WordPress site through wp-admin, then use tools. Manual custom connector setup is an alternative, not its normal directory experience. Its published remote endpoint is `https://mcp.wpvibe.ai/mcp`, shared across clients/sites. [V1, V3]

WPVibe explicitly describes its Cloudflare Worker as a hosted relay. It authenticates accounts, enforces plan/safety limits, calls WordPress over HTTPS and returns results. It stores site registrations and encrypted Application Passwords, plus account/approval/usage records. WordPress core REST or its installed plugin performs the site operation under the authorized WordPress user. Site content may pass through the Worker. These are vendor statements, not a source-code or live-traffic security audit. [V2, V4]

| Current displayed tier | Annual price | Tool calls per rolling 24 hours |
| --- | ---: | ---: |
| Free | $0 | 100 |
| Pro | $99 | 500 |
| Power | $299 | 2,000 |
| Agency | $599 | 5,000 |
| Scale | $899 | 10,000 |

The pricing page's rendered annual view was inspected; standalone monthly billing prices were not established. Limits apply per account, not per site. Failed actions and specified connection/site-list/skill operations do not count; a first-week bonus is documented. Its AI subscription is separate. This is metered service even though it does not charge for model tokens. [V5]

Preserve the simple normal ChatGPT connection, authentication, natural editorial work and understandable results. Eliminate the mandatory central WordPress gateway, vendor credential custody, plan enforcement and subscription dependence. Coagmentator also intentionally narrows operations compared with WPVibe's broader administration/file/WP-CLI surface. No WPVibe implementation was copied.

### Official WordPress stack, researched after distribution

Current latest non-prerelease adapter release: **0.7.0**, published **2026-10-02 14:39:48 UTC**, tag commit `54ed266a8c46b71fdb8e5b8a7d35668f90fb8994`. This is the first WordPress.org-directory release, a canonical/community plugin, not incorporation of the adapter into WordPress core. Plugin metadata requires WordPress 6.9+ and PHP 7.4+; the directory reports tested through 7.1.3. Coagmentator's accepted narrower WP 7.1.2/PHP 8.3-8.5 matrix is not automatically changed. Abilities API is in core from 6.9. [W1, W2, W3]

| Area | Source finding | Coagmentator consequence |
| --- | --- | --- |
| Protocol revisions | Exact schema support for `2026-07-28` and `2025-11-25`. Legacy identifiers `2025-06-18` and `2024-11-05` map to the latter; `2025-03-26` is not an equivalent supported batch profile. [W4] | Aligns with D-014's chosen revisions. Older release articles describing only 2025-06-18 are not current evidence. |
| Transports/lifecycle | HTTP plus WP-CLI STDIO. Modern HTTP is sessionless; 2025 uses sessions. HTTP POST supported; GET streaming/SSE is not implemented and returns 405. Batches rejected. [W4, W5] | Suitable protocol substrate to evaluate for remote ChatGPT; no need to expose WP-CLI. Actual client interoperability remains untested. |
| Authentication | Default HTTP check is `current_user_can('read')` after WordPress authentication. Custom transport permission callback is supported. Guide shorthand says logged-in, but source is more precise. [W5] | Core Application Passwords can authenticate HTTP clients; adapter does not install the complete ChatGPT OAuth system. No anonymous or merely logged-in access is sufficient for Coagmentator. |
| Server registration | `mcp_adapter_init` and `create_server()` accept explicit tools/resources/prompts, transports and handlers. [W6, W7] | Dedicated Coagmentator endpoint with fixed inventory, no generic execute dispatcher and empty resources/prompts unless explicitly approved. |
| Default exposure | Default endpoint `/wp-json/mcp/mcp-adapter-default-server` exposes three discovery/info/execute meta-tools, with public resources/prompts discovered too. [W6, W8] | Unrelated plugins' opted-in abilities may become reachable through the generic surface. It is not the 21-tool contract. |
| Exposure flags | Explicit `meta.mcp.public` wins; otherwise `meta.public` supplies the default. Malformed MCP metadata fails closed. Private-by-default does not mean only Coagmentator is exposed. [W9] | Keep Coagmentator abilities private to generic discovery and explicitly list them on its custom server. Avoid accidental core REST exposure from `meta.public`. |
| Disable default | `mcp_adapter_create_default_server` can disable the default server and built-in meta-abilities when registered early enough. [W7] | Prefer disable for a dedicated installation; when coexistence is required, independently deny the Coagmentator principal access to every alternative surface. Activation order and other plugins need proof. |
| Ability contracts | Namespaced registration, category, input/output schemas, permission and execute callbacks. Core validation uses a JSON Schema draft-4 subset. [W10] | Translate and verify existing contracts rather than assuming every JSON Schema feature is enforced identically. Explicit caps/site/actor checks remain Coagmentator code. |
| Execution | Ability-backed tools call permission checks and then `WP_Ability::execute()`, which normally validates input, checks permission, runs callback and validates output. Current core also has a pre-execute short-circuit hook. [W11, W12] | Permissions must be side-effect-free; admission counters cannot be charged once per permission invocation. Hook behavior belongs in the threat model. Schema success after a write is not readback verification. |
| Tool schemas/metadata | Conversion preserves object contracts, wraps non-object input/output as `input`/`result`, maps annotations and carries `meta.mcp._meta`. [W13, W14] | Use object-root contracts and prove exact names, scopes, annotations, file metadata and receipt shape. Wrapping must not change the public API silently. |
| Naming | Ability `/` becomes MCP `-`; filter `mcp_adapter_tool_name` permits valid explicit mapping. [W13, W15] | Retain existing 21 public tool names through a reviewed map and collision checks. No reason to change user-facing operations for convenience. |
| Errors/audit | Protocol errors differ from `isError` tool failures; execution `WP_Error` messages can reach clients and handlers. Default server uses error logging and no-op observability. Invalid final projection fails. [W12, W16, W4] | Coagmentator must sanitize errors and diagnostics, retain structured recoverable outcomes, and implement durable protected audit/journal records. Default logs are not safe receipts. |
| Upgrades | 0.7.0 removes several internal DTO/validator APIs, requires wire orchestration for custom transports and deprecates Composer bundling. [W2, W4] | Use canonical plugin dependency, reviewed versions and compatibility checks. Do not embed a stale adapter copy or silently inherit broad dependency support. |

Can it replace custom plumbing without weakening the product? **Yes, conditionally for MCP negotiation, wire handling, registration and ability dispatch. No, not by simply activating the default server.** It supplies neither public ChatGPT distribution nor Coagmentator's narrow authority and mutation policy. A direct WordPress endpoint still needs an established OAuth provider/resource-server integration, correct challenges/scopes, safe identity mapping and revocation. Mapping OAuth to an unrestricted human admin would undermine least privilege.

The adapter also does not implement SSRF-safe ChatGPT media retrieval, approvals, idempotency, version conflicts, authoritative readback or uncertain-outcome reconciliation. D-013 currently forbids WordPress URL downloads and assigns retrieval to the separate MCP process. Moving that function into PHP needs explicit supersession, not an accidental expansion. A small owner-hosted authentication/media component might remain justified; it must not become a mandatory central relay.

The preferred future target is D with a dedicated server, explicit Coagmentator abilities and an independent enforcement boundary. It may remove the custom REST hop and most proposed TypeScript protocol work. A same-site helper could remain optional. Full deletion of TypeScript cannot responsibly be promised before OAuth, media and hosting fit are evaluated. Retaining B is reasonable if process separation and existing contracts provide better security or installation economics.

### Concrete architecture comparison

| Option | Address selection and normal installation | Relay | Judgment |
| --- | --- | --- | --- |
| A. Current TS MCP plus bridge, ordinary public URL | A fixed public URL addresses one deployment. Multiple independently owned instances have no documented selector. Centralizing that URL transforms A into E. | No for one site; yes when centralized across sites | Fails required distribution as presently designed. |
| B. Current TS MCP plus template | Template could address each owner's TS endpoint. | No central operational relay needed | Product-compatible candidate if access, arbitrary domains, account support and practical two-service setup are proven. |
| C. Direct official adapter | Per-site WordPress endpoint works for configured clients; public listing selection remains missing. | No | Protocol convenience alone does not make it shippable. |
| D. Official adapter plus template | Template could select the site's dedicated Coagmentator MCP endpoint. | No central operational relay needed | Preferred conditional target: fewer owner-operated layers, but OAuth/media/enforcement and distribution remain gates. |
| E. Universal Coagmentator service | Directory connection goes to one hosted service that chooses/calls the site. | **Yes** | Generally documented publication pattern; **fails mandatory no-relay requirement**. Not recommended without owner changing product direction. |
| F. Minimal central bootstrap | Could collect registration, serve metadata and issue credentials, then disappear from tool path only if ChatGPT binds directly. | No in intended design | No supported final rebind established. Does not currently solve distribution. |
| G1. Admin-published private workspace app | Admin sets site endpoint; members use workspace app. | No inherently | Useful private deployment, not one normal public listing for ordinary independent site owners. |
| G2. Desktop WebMCP | User opens site and signs in; page supplies tools. | No inherently | Real alternative worth owner consideration only as a changed product journey; not an MCP-directory solution. |
| G3. Sites-hosted WordPress tools | Personal or workspace-created plugin pointing to a Sites service. | Yes, if it calls the owner's WordPress site | Supported creation path, but violates no-relay and does not establish public arbitrary-site distribution. |
| G4. Local MCP plugin | Desktop plugin executes a local MCP client/server for the owner and can address the owner's WordPress site. | No hosted relay inherently | Potential owner-controlled alternative, but public local-MCP distribution requires an undocumented contact/support path. Desktop-only use is a product limitation for owner review, not an invented prohibition. |
| G5. Separate public listing per site | Every owner submits a fixed endpoint and completes publication. | No | Fails the required single listing and practical ordinary-owner installation. |
| G6. Skills-only public package | Instructions guide setup or invoke available tools. | Depends on tools | No documented safe, persistent arbitrary-site MCP connection is supplied by instructions alone. |

### Hard invariant matrix

All rows are mandatory. **P** means the proposed design can preserve it, not implemented/tested acceptance. **C** means a specific access or engineering condition remains. **F** means the described architecture fails it. **U** means evidence is insufficient. A is the multi-owner product, not a single-site demonstration. The first matrix includes G1, the most concrete private alternative; the second covers G2 through G6.

| Requirement | A | B | C | D | E | F | G1 |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Ordinary user does not enable Developer Mode | F | C | F | C | P | U | P for members |
| Normal public ChatGPT discovery/connect | F | C | F | C | P | U | F |
| No third-party per-call/day metering dependency | P | P | P | P | C | P | P |
| No mandatory paid hosted gateway | P | P | P | P | C | P | P |
| No hosted SaaS operational relay | P | P | P | P | **F** | C | P |
| Open source | P | P | P | P | P possible | P | P |
| Self-hosted WordPress execution/control | P | P | P | P | C, central orchestration | P | P |
| Explicit narrow tool surface | P | P | C | C | C | C | C |
| WordPress final authorization authority | P | P | C | C | C | C | C |
| Least privilege | P | P | C | C | C | C | C |
| Auditability | P | P | C | C | C | C | C |
| Verified writes/receipts | P | P | C | C | C | C | C |
| Safe media/file handling | C | C | C | C | C | C | C |
| Writes, not only reads | C | C | C | C | P possible | U | C |
| Practical ordinary-owner installation | F | C, TS plus WP | C, auth/setup | C, auth/setup | P possible | U | F as public product |
| Portable beyond JimLunsford.com | C | C | C | C | P possible | U | C, workspace-limited |

The other concrete G variants are scored separately to avoid implying that their shared limitations are identical:

| Mandatory requirement | G2 WebMCP | G3 Sites | G4 local MCP | G5 per-site listings | G6 skills-only |
| --- | --- | --- | --- | --- | --- |
| Ordinary user does not enable Developer Mode | P on eligible desktop | P | C | P | P for skill installation |
| Normal public ChatGPT discovery/connect | F, open-page journey | F for required listing | U, local public distribution | F, separate listings | F for WordPress connection |
| No third-party per-call/day metering dependency | P | C, hosted dependency | P | P | U |
| No mandatory paid hosted gateway | P | C | P | P | U |
| No hosted SaaS operational relay | P | **F** | P | P | U |
| Open source | P possible | P possible | P possible | P | P |
| Self-hosted WordPress execution/control | P | C | P | P | U |
| Explicit narrow tool surface | C | C | C | P possible | U |
| WordPress final authorization authority | C | C | C | P possible | U |
| Least privilege | C, browser principal | C | C | P possible | U |
| Auditability | C | C | C | P possible | U |
| Verified writes/receipts | C | C | C | P possible | U |
| Safe media/file handling | C | C | C | C | U |
| Writes, not only reads | P supported mechanism | P supported mechanism | C | C | U |
| Practical ordinary-owner installation | C, desktop/page requirement | C, per-owner construction | C, local setup | F, publisher burden | F without connection mechanism |
| Portable beyond JimLunsford.com | C | C | C | C | U |

C/D's security cells become failures if the default broad server, raw admin identity or unverified generic abilities are used. B/D's distribution cells cannot become passes merely because the template form exists. G2 requires a new browser-based identity/media/approval design. No row or architecture is accepted by either table.

### Completed-work reuse

| Checkpoint | Concept | Exact implementation under preferred D | Recommendation |
| --- | --- | --- | --- |
| C01 | Reusable testing/package discipline | ADAPTABLE: PHP/WordPress fixtures and pinned matrix remain useful; package/dependency and adapter lanes need review. Existing historical evidence remains unchanged. | Retain; do not broaden matrix or rerun now. |
| C02 MU guard | REUSABLE UNCHANGED as an independent anti-bypass requirement | ADAPTABLE, not drop-in: exact POST routes and pinned `Coagmentator\Rest\ReadController` methods differ from one adapter JSON-RPC route and callback. Native REST/XML-RPC/Abilities/default/other MCP servers must remain denied to service principals. | Retain independence; redesign trusted dispatch proof before credentials. Do not replace it with adapter transport permissions alone. |
| C03A | Site/actor binding, revocation evidence, immutable operator config and readiness remain valid | ADAPTABLE: core Application Password event/UUID evidence fits B or a helper hop. Direct OAuth needs a different validated identity chain and live revocation readiness, never fabricated AppPassword evidence. | Reuse config ownership and binding tests; do not carry a now-unused credential requirement blindly. |
| C03B | TLS, trusted immediate proxy, origin integrity and Authorization preservation remain valid | ADAPTABLE: current exact bridge paths, canonical host and `/journal` subdirectory cases must cover adapter endpoint, metadata/challenges and modern MCP headers. | Preserve anti-spoofing checks and subdirectory evidence; reprove routing after architecture choice. |
| C03C rate limits | Authenticated binding-key limits and independent pre-auth protection remain valid | ADAPTABLE: currently 60/min per site binding and 120/min immediate peer; JSON-RPC discovery/auth and calls need deliberate accounting boundaries. | Do not count permission callbacks as tool executions. |
| C03C concurrency/deadline | Bounded admission, four nonblocking slots and cooperative 15-second budgets remain useful | ADAPTABLE: wrapper is bridge-read/identity-specific. Scope must cover actual protected execution; discovery, nested ability calls and mutation outcomes differ. | Retain concepts; deadline is not forced cancellation. |
| C03C storage/audit | Persistent bounded storage, fail-closed corruption/capacity behavior and protected minimal records remain useful | ADAPTABLE: worker-owned POSIX local files/flock and supported mounts exclude many ordinary hosting environments. Four-field 90-day read audit is not a write journal or full adapter observability solution. | Explicitly evaluate ordinary-owner installation. Do not quietly substitute transients or unbounded logging. |
| C03C candidate as a whole | Useful design and unaccepted evidence | ADAPTABLE, PARTIAL / BLOCKED / UNACCEPTED | Fixing cleanup now does not resolve the distribution decision and risks validating plumbing that will change. Preserve branch/evidence, defer repair. Not wholly wasted work. |
| Future Gate 4 | MCP/auth contract verification remains necessary | REPLACED substantially by official protocol handling if D passes; TypeScript shrinks or becomes optional for site-owned auth/media separation | Do not build the planned TS MCP server unchanged now, and do not promise every helper disappears. |

The same concepts have higher unchanged reuse under B. The recommended pause concerns implementation investment, not rejection of the security work already accepted.

### Accepted-decision impact

These are proposed impacts for owner review, not amendments. Historical entries remain untouched.

| Decision | Classification | Required later treatment |
| --- | --- | --- |
| D-003 Repository as memory | UNCHANGED | Repair missing product truth in permanent docs only after authorization; retain this audit as history. |
| D-004 Monorepo | UNCHANGED | Keep project components/contracts together; an external canonical plugin dependency does not require splitting Coagmentator. |
| D-005 Separate MCP and WP responsibilities | NEEDS SUPERSEDING DECISION | If D chosen, distinguish logical responsibilities from separate processes; define any surviving helper, OAuth and file boundary. Under B it can remain. |
| D-006 Narrow surface | UNCHANGED | Explicit tools and administration prohibitions remain binding. |
| D-009 Fixed inventory/site binding | VALID PRINCIPLE, IMPLEMENTATION CHANGES | Preserve 21 operations and trusted per-install binding. Specify template installation selection, tool-name map and request-handle owner; do not add model-selected arbitrary site URLs. |
| D-010 OAuth/service credentials | NEEDS SUPERSEDING DECISION | Direct resource-server auth must explicitly replace any eliminated MCP-to-WP AppPassword hop. Keep established auth, PKCE, audience isolation, narrow principals and revocation. B may retain the two credentials. |
| D-011 Capabilities/MU guard | VALID PRINCIPLE, IMPLEMENTATION CHANGES | Preserve independent alternative-path denial; revise route/callback and OAuth-principal evidence, not the least-privilege goal. |
| D-012 Approval/mutation evidence | VALID PRINCIPLE, IMPLEMENTATION CHANGES | Preserve both profiles, disabled defaults, human-only policy authority, journaling/readback/unknown outcomes; name the new execution owner and any removed credential dependency. |
| D-013 Content/media/metadata | NEEDS SUPERSEDING DECISION | Direct PHP file retrieval conflicts with explicit current placement. Either retain a site-owned bounded media component or approve and prove a new boundary. Content/metadata restrictions remain. |
| D-014 Revision/adapter strategy | NEEDS SUPERSEDING DECISION | Preserve exact revisions; replace TS-SDK implementation assumptions with canonical adapter/version/profile evidence if D selected. |

A new decision must also explicitly establish the shipping distribution prerequisite, eligible accounts/domains, and the no-relay/no-Developer-Mode journey. None of D-003 through D-014 is retroactively declared obsolete or unaccepted by this audit.

## Files changed

Exactly one project file:

- `docs/work-notes/2026-10-06-product-fidelity-distribution-audit.md`

Research branch: `research/product-fidelity-distribution-audit`, based on exact accepted main above. Commit message: `docs: audit Coagmentator product fidelity and distribution [skip ci]`.

The note's introducing commit and its complete tree are the research checkpoint; literal hashes are returned in the session handoff after publication, avoiding an impossible self-referential commit hash. No existing project file, accepted doctrine, source, test, workflow, dependency or lock is changed. No PR or merge is part of this execution.

## Verification

- Live GitHub checkpoint, recursive-tree, CI-metadata and open-PR inspection; required docs and selected public source read.
- Ruleset collection returned no repository rulesets. AGENTS permits public research notes and requires handoffs. The owner's one-file scope takes precedence over promoting any unaccepted recommendation into doctrine.
- All existing main workflows were inspected. Push triggers target named implementation branches; this research branch is outside them. The documentation commit additionally uses `[skip ci]`.
- Documentation checks cover requested sections/classifications, source links, no em dashes, and one-file content. Final publication verification compares full trees and unchanged main/C03C refs, with results in the handoff.
- No implementation CI, test execution, C03C rerun, prototype, production connection, deployment, credential creation, ChatGPT setting change, submission or OpenAI contact occurred.

## Decisions

No new accepted durable decision. Recommendations require owner review. Freeze remains in force. Restricted access is a product dependency; elegant protocol implementation is not permission to ship.

## Risks / unresolved items

The strongest argument against the recommendation is that the separate TypeScript boundary already has detailed contracts, isolates OAuth/media work from WordPress and could reuse most accepted controls unchanged. A small cleanup repair is cheap, and a provider-template exception or the newer custom-plugin UX may make distribution easier than the generic docs suggest. Pivoting to a young adapter adds upgrade and authentication work without granting access. These are valid reasons to keep B as the fallback, but not reasons to assume a public arbitrary-site connection model or resume implementation before confirming it.

Unresolved evidence that can change the decision:

1. Template entitlement for this publisher, its administrative scope and obtainable qualification route.
2. Arbitrary unrelated site domains, HTTPS origins/ports, WordPress subdirectories and site-specific OAuth issuers in one approved template; domain verification responsibilities.
3. Generic third-party template access on personal Free/Go/Plus/Pro versus managed Business/Enterprise/Edu, including write/file availability and actual no-toggle onboarding.
4. Whether a supported redirect/rebind/bootstrap contract exists outside the reviewed documentation, or public local-MCP support could provide an acceptable Desktop-only alternative. Absence from reviewed docs is not a prohibition invented by this audit.
5. Actual installed client revision/auth/callback behavior, annotation scan, file handoff and update compatibility with adapter 0.7.0.
6. Maintained self-hostable OAuth integration and safe principal mapping for a direct WordPress resource server; public per-tool auth metadata may require integration beyond automatic ability conversion.
7. Exact preservation of receipts/errors, admission boundaries, independent guard coverage, disabled generic exposure, media isolation and supported ordinary hosting under D.
8. Current-source documentation conflicts, especially generic managed templates versus provider personal guides and legacy Developer Mode versus the new custom-server guide. No dated archival comparison was obtained, so this audit does not claim exactly when wording changed relative to Gate 1.

No unsupported promise of future template access, approval or a free central service is made.

## Exact next step

**Next bounded Work execution: owner-reviewed distribution feasibility decision, documentation only.** Read this published audit and reverify the frozen checkpoints. Incorporate the owner's explicit response and any authoritative eligibility evidence supplied by the owner. Produce a concrete decision packet answering: permitted accounts, arbitrary-domain/subdirectory support, OAuth issuer/resource rules, template eligibility, no-relay routing, and exact user journey. Do not repair C03C, alter credentials/settings, submit a plugin or contact OpenAI unless the owner separately authorizes that specific action. If no new access evidence exists, report the distribution gate BLOCKED and stop without implementation. Any later doctrine amendment must be explicitly authorized and add superseding decisions rather than rewriting accepted history.

**Smallest later executable proof, not authorized or performed here:** after access and a supported configuration path are confirmed, use one review-approved/non-production listing and two disposable owner-controlled endpoints on unrelated domains, one with a WordPress subdirectory. On an explicitly supported ordinary personal account with Developer Mode off, install from the listing, configure/authenticate each site using the supported flow, and call one harmless site-identity read returning different fixed values. Sanitized destination logs must demonstrate direct calls to each endpoint, no ongoing central WordPress forwarding, correctly bound resources/tokens and rejection of a token for the other site. Failure to select either endpoint is a distribution-gate failure. A custom developer connection is not an equivalent pass.

That read-only proof answers the routing question with minimum implementation. Only after it passes, separately authorize one reversible draft write with authoritative readback and one harmless image handoff through the exact published file schema. Those are necessary before declaring the full write/media product viable, but are not prerequisites to learning whether the fundamental route exists. The adapter fit assessment follows, comparing D against B using the same tool contracts. Nothing in this note authorizes those experiments.

## References

All external sources below were retrieved **2026-10-07 UTC (2026-10-06 America/New_York)**. Live documentation may change. Facts are distinguished above from architectural inference and unverified behavior. Search scope included official developer/help documentation for template trust, application/request paths, configurable endpoints, OAuth/resource discovery, redirects, bootstrap, settings/onboarding and newer plugin/tunnel/Sites/site-tool mechanisms. No community opinion was treated as OpenAI permission.

### Repository evidence

- [Accepted main tree](https://github.com/jimlunsford/coagmentator/tree/1053487d627622145b19232f1756643bada0b74c): README, AGENTS, PROJECT, ARCHITECTURE, SECURITY, ROADMAP, DECISIONS, AUTHENTICATION, CAPABILITIES, CONTRACTS, ERRORS, MCP-TOOLS, THREAT-MODEL, GATE-2-IMPLEMENTATION-PLAN, GATE-2-READ-ROUTES, TEST-MATRIX, C03A-CONFIGURATION and C03B-TRANSPORT.
- [Inception](https://github.com/jimlunsford/coagmentator/blob/1053487d627622145b19232f1756643bada0b74c/docs/work-notes/2026-10-05-project-inception.md) and [work-note history](https://github.com/jimlunsford/coagmentator/tree/1053487d627622145b19232f1756643bada0b74c/docs/work-notes): Gate 0 closeout, Gate 1 contracts/closeout, Gate 2 preparation/closeout, C01/C02/C03A/C03B closeouts and relevant implementation handoffs.
- [C03C operations](https://github.com/jimlunsford/coagmentator/blob/c4e2f01e90e60f4993473bb89256cdd71f5361cc/docs/C03C-OPERATIONS.md), [C03C handoff](https://github.com/jimlunsford/coagmentator/blob/c4e2f01e90e60f4993473bb89256cdd71f5361cc/docs/work-notes/2026-10-06-gate-2-c03c-admission-audit-concurrency.md), [tested implementation](https://github.com/jimlunsford/coagmentator/commit/f1b09e82e610e2a7362c63338ef5b3e31878b739), and [focused CI](https://github.com/jimlunsford/coagmentator/actions/runs/37539689554).

### OpenAI and standards

- O1: [Remote MCP review and Template MCP URLs](https://developers.openai.com/plugins/deploy/app-review).
- O2: [Upload and submit a plugin](https://developers.openai.com/plugins/deploy/submission).
- O3: [Plugins in ChatGPT](https://help.openai.com/en/articles/20001256-plugins-in-chatgpt) and [plugin learning guide](https://learn.chatgpt.com/docs/plugins).
- O4: [Developer mode and MCP apps](https://help.openai.com/en/articles/12584461-developer-mode-and-mcp-apps-in-chatgpt).
- O5: [Add custom MCP server](https://developers.openai.com/api/docs/guides/custom-mcp-server) and [connect/test](https://developers.openai.com/plugins/deploy/connect-chatgpt).
- O6: [Authentication](https://developers.openai.com/plugins/build/auth).
- O7: [Plugin reference, file parameters and UI tools](https://developers.openai.com/plugins/reference).
- O8: [Managed app templates](https://help.openai.com/en/articles/20001247-chatgpt-app-templates).
- O9: [Snowflake template](https://help.openai.com/en/articles/20001249-set-up-the-snowflake-app-template-in-chatgpt).
- O10: [Databricks template](https://help.openai.com/en/articles/20001250-set-up-the-databricks-app-template-in-chatgpt).
- O11: [GitHub Enterprise template](https://help.openai.com/en/articles/20001248-set-up-the-github-enterprise-app-template-in-chatgpt).
- O12: [Plugin extensions](https://developers.openai.com/plugins/build/extensions) and [changelog](https://developers.openai.com/plugins/changelog).
- O13: [Claude plugin migration, including userConfig limits](https://developers.openai.com/plugins/guides/submit-claude-plugin) and [plugin packaging](https://developers.openai.com/plugins/build/plugins).
- O14: [OpenAI MCP extension specification](https://github.com/openai/mcp-extensions/blob/main/docs/spec.md) and [same-server settings implementation](https://github.com/openai/mcp-extensions/blob/main/typescript/src/server/settings.ts); inspected source tree `0ba30cd5e3aa17265685969508e0ca7e39011a3a`.
- O15: [Secure MCP Tunnel](https://developers.openai.com/api/docs/guides/secure-mcp-tunnels).
- O16: [Hosting a plugin with Sites](https://help.openai.com/en/articles/20001547-hosting-a-plugin-with-chatgpt-sites).
- O17: [Desktop site tools/WebMCP](https://help.openai.com/en/articles/20001423-using-site-tools-in-the-chatgpt-desktop-app).
- S1: [MCP 2026-07-28 authorization](https://modelcontextprotocol.io/specification/2026-07-28/basic/authorization).
- S2: [RFC 8707 resource indicators](https://www.rfc-editor.org/rfc/rfc8707).
- S3: [RFC 9110, HTTP redirects and credential scope](https://www.rfc-editor.org/rfc/rfc9110.html#section-15.4).

### WPVibe primary material

- V1: [ChatGPT connection guide](https://wpvibe.ai/connect/chatgpt/).
- V2: [Security and data, dated 2026-08-24](https://wpvibe.ai/security/).
- V3: [Getting started and endpoint](https://wpvibe.ai/docs/getting-started/).
- V4: [WordPress authorization](https://wpvibe.ai/docs/connect-a-wordpress-site/).
- V5: [Current pricing](https://mcp.wpvibe.ai/pricing).

### Official WordPress documentation and pinned source

Adapter source links use the inspected 0.7.0 tag commit, not moving trunk.

- W1: [WordPress.org MCP Adapter](https://wordpress.org/plugins/mcp-adapter/).
- W2: [0.7.0 release](https://github.com/WordPress/mcp-adapter/releases/tag/v0.7.0).
- W3: [Core Abilities API](https://developer.wordpress.org/apis/abilities-api/) and [adapter plugin header](https://github.com/WordPress/mcp-adapter/blob/54ed266a8c46b71fdb8e5b8a7d35668f90fb8994/mcp-adapter.php).
- W4: [0.7.0 migration](https://github.com/WordPress/mcp-adapter/blob/54ed266a8c46b71fdb8e5b8a7d35668f90fb8994/docs/migration/v0.7.0.md) and [revision negotiator](https://github.com/WordPress/mcp-adapter/blob/54ed266a8c46b71fdb8e5b8a7d35668f90fb8994/includes/Core/McpVersionNegotiator.php).
- W5: [HTTP transport source](https://github.com/WordPress/mcp-adapter/blob/54ed266a8c46b71fdb8e5b8a7d35668f90fb8994/includes/Transport/HttpTransport.php) and [transport permission guide](https://github.com/WordPress/mcp-adapter/blob/54ed266a8c46b71fdb8e5b8a7d35668f90fb8994/docs/guides/transport-permissions.md).
- W6: [Default versus custom servers](https://github.com/WordPress/mcp-adapter/blob/54ed266a8c46b71fdb8e5b8a7d35668f90fb8994/docs/guides/default-server.md).
- W7: [Adapter lifecycle, custom registration and default disable](https://github.com/WordPress/mcp-adapter/blob/54ed266a8c46b71fdb8e5b8a7d35668f90fb8994/includes/Core/McpAdapter.php).
- W8: [Default server factory](https://github.com/WordPress/mcp-adapter/blob/54ed266a8c46b71fdb8e5b8a7d35668f90fb8994/includes/Servers/DefaultServerFactory.php).
- W9: [Exposure resolver](https://github.com/WordPress/mcp-adapter/blob/54ed266a8c46b71fdb8e5b8a7d35668f90fb8994/includes/Abilities/McpAbilityExposure.php).
- W10: [wp_register_ability and schema/exposure rules](https://developer.wordpress.org/reference/functions/wp_register_ability/).
- W11: [Core ability execution source](https://developer.wordpress.org/reference/classes/wp_ability/execute/).
- W12: [McpTool dispatch](https://github.com/WordPress/mcp-adapter/blob/54ed266a8c46b71fdb8e5b8a7d35668f90fb8994/includes/Domain/Tools/McpTool.php) and [tool handler](https://github.com/WordPress/mcp-adapter/blob/54ed266a8c46b71fdb8e5b8a7d35668f90fb8994/includes/Handlers/Tools/ToolsHandler.php).
- W13: [Ability-to-tool conversion](https://github.com/WordPress/mcp-adapter/blob/54ed266a8c46b71fdb8e5b8a7d35668f90fb8994/includes/Domain/Tools/RegisterAbilityAsMcpTool.php).
- W14: [Schema transformation](https://github.com/WordPress/mcp-adapter/blob/54ed266a8c46b71fdb8e5b8a7d35668f90fb8994/includes/Domain/Utils/SchemaTransformer.php).
- W15: [MCP name sanitizer](https://github.com/WordPress/mcp-adapter/blob/54ed266a8c46b71fdb8e5b8a7d35668f90fb8994/includes/Domain/Utils/McpNameSanitizer.php).
- W16: [Error logging](https://github.com/WordPress/mcp-adapter/blob/54ed266a8c46b71fdb8e5b8a7d35668f90fb8994/includes/Infrastructure/ErrorHandling/ErrorLogMcpErrorHandler.php).
