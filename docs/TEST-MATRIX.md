# Gate 2 Support and Test Matrix

Research date: **2026-10-05**. Status: **preparation human-reviewed and accepted on 2026-10-05 at PR #2; no packages installed and no implementation tests run**. This document selects the future environment and acceptance strategy for [GATE-2-IMPLEMENTATION-PLAN](GATE-2-IMPLEMENTATION-PLAN.md) and [GATE-2-READ-ROUTES](GATE-2-READ-ROUTES.md).

## Upstream requirements versus project support

| Subject | Current primary-source evidence | Accepted preparation choice |
| --- | --- | --- |
| Current WordPress | Release archive identifies **7.1.2**, released **2026-09-22**, as latest; it identifies the latest 7.1 patch as actively maintained [S01] | Minimum supported WordPress **7.1.2**, current acceptance version **7.1.2**. Same floor/current version is intentional for a new security-sensitive project |
| Core PHP minimum | The 7.1.2 source declares PHP **7.4** and required `json`/`hash`; the WordPress compatibility table includes PHP 8.3, 8.4 and 8.5 [S02, S03] | Minimum PHP language/runtime line **8.3**, test **8.3, 8.4, 8.5** with current security patches. Core's older compatibility floor is not this project's support promise |
| Recommended host | WordPress recommends PHP **8.3+**, MariaDB **10.11+** or MySQL **8.0+**, and HTTPS [S04] | Cover the PHP 8.3/MariaDB 10.11 self-hosted baseline plus newer PHP and MySQL LTS. No production inspection or claim that the target already satisfies the WordPress floor |
| Core database floor | 7.1.2 `version.php` declares MySQL **5.5.5**, distinct from the recommended host versions [S02] | Do not add legacy/EOL database jobs merely because core permits them |
| PHP 8.2 | Security support through **2026-12-31** [S05] | Exclude: near end of security support and unnecessary extra compatibility burden for a new project |
| PHP 8.3 | Security-fixes-only; active support ended **2025-12-31**, security support ends **2027-12-31** [S05] | Required minimum-runtime lane; retaining it is deliberate, not a claim it is in active bugfix support |
| PHP 8.4 | Active support through **2026-12-31**, security through **2028-12-31** [S05] | Required lane and default development/quality runtime |
| PHP 8.5 | Stable release line, active through **2027-12-31**, security through **2029-12-31**; WordPress 7.1 compatibility is documented [S03, S05] | Required, blocking compatibility coverage. Do not label it experimental based on an older WordPress beta-support article |
| MariaDB | 10.11 Community maintenance through **2028-02-16** [S06] | Required **10.11** database line and deliberate MariaDB support floor |
| MySQL | 8.4 is an LTS release model [S07] | Required **8.4 LTS**, deliberately above WordPress's host recommendation floor. No MySQL 8.0/innovation-series support claim |

Initially, the supported WordPress acceptance target is the exact latest stable patch above, not an unbounded promise about all future versions. Advance security patches promptly through review and repeat the affected matrix; never leave CI floating at `latest`. Supporting an older WordPress branch requires an explicit reason and review. Future core/PHP prereleases may later get a nonblocking reconnaissance job, but they are not part of this Gate 2 acceptance matrix. No extra nightly job is required to accept this preparation.

PHP patch releases and database image patch/digest pins are resolved from maintained upstream sources at implementation C01 and recorded in a committed environment manifest. This accepted plan selects the supported lines, not fictitious exact patches or unexecuted binary compatibility. Recheck the WordPress latest stable and PHP lifetimes before implementation; material changes return for plan review rather than silently changing the reviewed support floor. JimLunsford.com's actual WordPress/plugins/SEO/proxy versions remain unknown in this execution. Target inventory and any production upgrade need later authorization.

## Mandatory implementation matrix

| Lane | WordPress | PHP runtime | Database | Required suites |
| --- | --- | --- | --- | --- |
| M1 | 7.1.2 | 8.3, pinned current patch | MariaDB 10.11, pinned patch/digest | Core integration plus real HTTP/security, including guard-only/deactivated cases |
| M2 | 7.1.2 | 8.3, same pin | MySQL 8.4 LTS, pinned patch/digest | Same full suites |
| M3 | 7.1.2 | 8.4, pinned current patch | MariaDB 10.11, same pin | Same full suites |
| M4 | 7.1.2 | 8.4, same pin | MySQL 8.4 LTS, same pin | Same full suites |
| M5 | 7.1.2 | 8.5, pinned current patch | MariaDB 10.11, same pin | Same full suites |
| M6 | 7.1.2 | 8.5, same pin | MySQL 8.4 LTS, same pin | Same full suites |

Six lanes cover both supported SQL engines across all three PHP lines without a historical WordPress-version Cartesian expansion. Real database behavior matters even for read-only tools: WordPress author/status capability queries, metadata, taxonomy, revision selection, collations, keyset comparisons and core Application Password storage must work. Use isolated InnoDB/utf8mb4 databases, normal strict engine defaults and WordPress's normal `$wpdb` setup; do not conceal failures by globally relaxing SQL settings. Application code uses WordPress APIs, not a second database abstraction for content.

Run pure unit tests and PHP lint once per PHP version, independent of DB lanes. Run PHPStan, coding standards, dependency audit and coverage on PHP 8.4. These tooling runtimes do not raise the installed package's PHP 8.3 minimum. Require 64-bit PHP for the accepted safe-integer ID range. Required runtime extensions beyond core include Sodium for opaque authenticated cursors, mbstring for Unicode bounds, mysqli for WordPress, and fileinfo for local media validation; validate availability before enabling handlers. Test-only XML/DOM and coverage extensions stay out of runtime requirements unless actually needed by production code.

## Tool selection

| Area | Selected approach | Why and limits |
| --- | --- | --- |
| Pure unit and HTTP/security runner | **PHPUnit 12.x**, current compatible patch locked at C01 | Maintained bugfix line supporting PHP 8.3 [S08, S09]; one runner works across all project PHP lines. PHPUnit 13 requires PHP 8.4, so it would unnecessarily raise the developer floor |
| WordPress integration runner | **PHPUnit 9.6.x**, isolated under `tools/wp-tests`, with core-compatible **Yoast PHPUnit Polyfills 1.x** and the official **7.1.2 core test library** | WordPress's dated compatibility table still specifies PHPUnit 9 for WordPress 7.1 on PHP 8.3-8.5 [S10]; 7.1.2's Composer manifest requests Polyfills `^1.1.0` [S11]. Do not claim the core library supports PHPUnit 12 just because PHP does |
| Legacy-runner risk | PHPUnit 9 is in life support, not active bugfix support; never ship it or expose a test web endpoint | Official PHPUnit policy distinguishes these states [S08]. Audit/lock its dependencies separately; prove it boots on PHP 8.5. An unresolved vulnerability/incompatibility blocks C01, not an excuse to suppress the failure or copy core audit ignores |
| Core test library | Download/check out `WordPress/wordpress-develop` at the exact 7.1.2 revision, use `tests/phpunit/includes`, official bootstrap and `WP_UnitTestCase` factories | Real hooks/mapping/database behavior [S12]. Pin the source commit/hash. Match library and actual core version; do not mix trunk tests with stable runtime. No WPVibe source or scaffolding |
| Environment | **Docker Compose** with explicit PHP-FPM, Nginx, TLS and MariaDB/MySQL services; local setup scripts using fixed test destinations | Tests actual Authorization forwarding, request body limits, proxy/TLS handling, simultaneous workers and plugin/MU packaging. Same Compose topology locally and eventually in Actions. No public ports required in CI [S17] |
| Alternative evaluated: `@wordpress/env` | Not selected as acceptance harness | Official tool is convenient for WordPress development [S13], but brings Node tooling and defaults while this task needs explicit TLS/proxy and two-engine failure controls. No Node or JavaScript is otherwise needed for Gate 2 |
| Alternative evaluated: Playground/SQLite | Not selected for acceptance | Helpful for interactive demonstrations, but would not supply this MariaDB/MySQL and PHP-FPM/HTTP evidence. No separate demonstration environment is needed |
| Static analysis | **PHPStan 2.x**, level 8 for new bridge/guard code, configured PHP target 8.3 | Typed internal boundaries; scan pinned core declarations or narrowly reviewed stubs, exclude third-party source from project findings. Explicit small dynamic-WordPress adapters, no blanket baseline hiding new errors [S14] |
| Syntax checks | `php -l` over every project PHP file on 8.3, 8.4, 8.5 | Syntax compatibility independent of a modern tool runner; no claimed PHP 8.2 support |
| Coding standard | **WordPress Coding Standards 3.x** with its compatible PHP_CodeSniffer, exact locked pair at C01 | Match WordPress integration conventions, including escaping/sanitization checks, without assuming core's full dev stack is mandatory [S15]. Review context-specific exclusions for raw read source; never sanitize away contract data just to satisfy a sniff |
| Dependencies | **Composer 2**, separate manifests/locks for modern quality tools and WordPress integration runner | Avoid incompatible PHPUnit majors in one dependency graph. Use locked installs, platform checks and audit; narrowly allow required Composer plugins only. No runtime vendor requirement [S16] |
| CI | Future GitHub Actions workflow for quality, three unit/lint lanes and six integration/HTTP lanes | Pin action revisions and environment artifacts, least token permissions, no production secrets, no deployments. Upload sanitized JUnit/coverage/version manifests. No workflow is created by preparation |

Integration test files use the core-compatible PHPUnit API; modern runner tests use PHPUnit 12. Neither runner autoloads the other's vendor tree. Keep any shared fixtures as ordinary data/builders, not classes extending two incompatible PHPUnit versions. Mock pure adapters for unit tests; do not mock `current_user_can`, core Application Password validation or the REST server and call that security acceptance. Core-only integration tests may deliberately set users to isolate policy mapping, but that is explicitly not authentication proof. HTTP tests must traverse actual core authentication.

Composer patch selection and solving are C01 work; this preparation adds no manifests, lockfiles, dependencies or fixtures. Do not copy the upstream core manifest's `audit.ignore`, `lock: false` or arbitrary package choices into this project.

## Test environment and evidence architecture

Three layers supply distinct evidence:

1. **Unit:** deterministic schemas, duplicate/depth/encoding rejection, canonical hashes, encrypted cursor integrity/binding/expiry, projections, error translation and bounded counters using controlled clocks. Golden expected bytes/hashes are independently calculated, not generated by the function under test at assertion time.
2. **WordPress integration:** real 7.1.2 bootstrap/database, real roles/users/posts/pages/terms/attachments/revisions, actual native capability filters, and test instrumentation around queries/rendering. Load the MU guard before the ordinary plugin. Separate databases and table prefixes per process; never point `WP_TESTS_CONFIG_FILE_PATH` at an installed personal/production site.
3. **Real HTTP/security:** disposable WordPress behind a real TLS edge/PHP-FPM, actual Application Passwords issued only after guard verification, correct Authorization handling, multiple parallel clients and real failure responses. Run plugin-active, deactivated, removed, malformed-feature-config and guard-only environments. Cases which require PHP constants/bootstrap variations use fresh processes/containers, not attempts to redefine constants in one test process.

Use an ephemeral test CA and verify its trust; include wrong hostname/untrusted certificate/HTTP downgrade and redirect cases. The HTTP runner's trust settings apply only to the isolated test network. No `verify=false`, `curl -k`, production hostname, production data or external account. Permit fixture/asset downloads only during environment construction; runtime tests deny outbound traffic. Disable automatic cron/update/mail side effects in the isolated environment and instrument attempted HTTP requests so raw-content/media tests prove the handler did not try to fetch anything. No Selenium/browser UI suite is necessary for a headless read bridge.

Fixtures include two ordinary authors, service user and a control administrator; all six accepted content statuses, password-protected items, wrong post types, privacy-policy page, absent objects, static JPEG/PNG/WebP plus ineligible/animated media, supported/inaccessible/missing/foreign parents, empty/populated terms, owned/unknown meta, saved revisions/autosaves/cross-parent IDs, Unicode/boundary-size/raw hostile content and more than 1,000 candidates. Fixture generation is local test setup, never a bridge write route.

After each HTTP read/bypass test compare editorial state: posts/pages/revisions, term relationships, approved and unrelated metadata, attachment records and upload directory. Core Application Password last-use accounting and explicitly allowed audit/admission counters are incidental operational changes; do not incorrectly call their presence an editorial write failure. Instrument prohibited callbacks and rendering/network functions with sentinels. A 403 alone is insufficient evidence if the target callback or side effect already ran.

Do not publish raw request headers, temporary credentials, user emails, source bodies or unrestricted logs as artifacts. Publish safe case IDs, outcomes/counts, exact Git/core/tool/image versions, environment hashes and redacted reason codes. Verify sanitized artifacts against seeded secret markers before upload. A full coverage percentage cannot replace the negative matrix. Any unsupported/skip in a mandatory security case must be an explicit failed acceptance criterion until resolved.

## Registry emergency recovery acceptance

Repeat missing-registry and malformed-registry variants separately in all six mandatory lanes, with the feature package active and guard-only/deactivated. B02 owns recovery; A01/A02 and G01/G03/G04 supply actual authentication/dispatch evidence. These are additional cases within the existing 26 families, not runtime pass claims.

1. Missing registry blocks all nine Coagmentator bridge routes before callbacks/resource queries.
2. Malformed registry independently blocks all nine routes. No operation treats corruption as an empty protected-user list.
3. Actual otherwise-valid Application Passwords, for service and control human users, cannot authenticate in either emergency state. No success evidence is recorded or reused.
4. Those credential-bearing requests cannot fall through to native REST reads/writes, self-management, batch, plugin or Abilities callbacks, including routes normally available anonymously. Denial survives user clearing/switching; compare target sentinels and editorial state.
5. XML-RPC, including multicall and ordinary-password authentication, cannot bypass denial. Exercise configured alternate remote-auth handlers with an unidentified service account, including a missing marker; an unsupported path blocks installation rather than being accepted untested.
6. Anonymous, cookie-only, nonce-only and cookie-plus-nonce requests cannot access any bridge route, including an authenticated human administrator and internal dispatch. Preserving administration never creates an unauthenticated bridge or recovery endpoint.
7. A normal non-service human administrator can complete real password authentication through normal `wp-login.php`, receive a valid cookie and start a new session in both emergency variants.
8. Existing and newly issued human cookies retain authorized `wp-admin` recovery and required core cookie/nonce requests under normal capabilities and CSRF protection. Verify actual authorized admin content/actions, not merely a login redirect or HTTP 200; unauthorized/anonymous requests gain no admin authority. Host-managed configuration still requires its normal trusted repair path.
9. Ordinary public anonymous traffic remains usable. Distinguish a genuinely anonymous request from a rejected credential-bearing request; never clear an auth failure into public success.
10. A recognizable user-level service marker prevents interactive authentication and service-cookie/admin access in both emergency variants, even after a role change. A test-only known-password fixture may exercise rejection; operational provisioning still generates and discards the ordinary service password and never relies on it.
11. Restore valid registry/configuration through trusted setup and use fresh requests: the approved service UUID can reach authorized bridge reads only; native REST/XML-RPC/interactive bypasses remain denied; wrong/revoked UUID and cookie/nonce bridge attempts remain denied. Ordinary unrelated human behavior returns to the documented healthy state.

Also prove healthy-registry IDs still restrict a service account after marker removal, and a marker alone grants no bridge authority. Check provisioning does not retain/log/export its generated ordinary password. Deliberately tampering with both registry and marker is documented WordPress compromise/operator tampering, not a promised guard bypass defense or a reason to lock all human operators out.

## Coverage ownership and acceptance cases

These are planned test families, not existing classes or passing tests. `U` is unit, `W` real WordPress integration, `H` real HTTP/security. Component names refer to the implementation plan. Every family must assert both the intended outcome and relevant no-disclosure/no-effect property.

| ID / planned test owner | Component owner | Layers | Required cases and objective acceptance |
| --- | --- | --- | --- |
| B01 BootstrapTest | MU loader, Bootstrap | W,H | Normal plugin loads; missing/incompatible guard disables bridge; unsupported PHP/WP/extension environment fails safely; guard loads without normal plugin |
| B02 GuardIndependenceTest | GuardConfig, emergency loader | W,H | Plugin deactivated/deleted, invalid feature config, missing handler/class, malformed/missing ID registry, missing support file; service credentials never gain native API reach; all emergency recovery cases above are mandatory, including human wp-login/wp-admin, public controls and restoration |
| A01 ApplicationPasswordIdentityTest | AuthenticationEvidence, BridgeIdentity | H | Correct user/approved UUID succeeds; valid wrong user (including admin), wrong UUID, revoked UUID, expired rotation overlap denied; check that matching uses credential UUID, not app_id; revoke after capture before dispatch also denied |
| A02 AlternateAuthenticationTest | Guard, BridgeIdentity | W,H | Anonymous, cookies only, nonce only, cookie plus nonce, main login password, injected user/JWT-like plugin auth, OAuth bearer, missing event and mismatched event/current user denied; Authorization stripped by proxy does not fall back; no identity state survives into another request |
| A03 BindingConfigurationTest | GuardConfig, EnvelopeValidator | U,W,H | Wrong site/actor/contract; disabled Application Passwords globally/per user; changed user/role marker; malformed identity/config; unknown config fields; profile switch cannot enable a write; no credentials issued if preflight fails |
| A04 TransportTest | GuardedRestServer, edge/HTTP runner | H | Trusted TLS passes; HTTP, invalid cert, wrong hostname, redirects, forged forwarded TLS/host headers and untrusted proxy denied; trusted proxy strips attacker headers and preserves auth; canonical subdirectory installation exercised |
| G01 NativeRestBypassTest | RouteBoundary | H | Native read/edit/create/delete, core discovery, batch outer route, Application Password list/create/delete/introspect, user self-edit, plugin and Abilities endpoints denied; promotion to Administrator does not bypass protected-ID restriction |
| G02 RouteNormalizationTest | RouteBoundary, guarded server | U,H | Wrong verbs including GET/HEAD/OPTIONS/PUT/DELETE; `_method`/override headers; extra/trailing/double slash, encoded/double-encoded slash/backslash/dot segments, case changes, query `rest_route`, JSONP/envelope/embed flags; no permitted alias or route-registration overwrite |
| G03 InternalDispatchTest | GuardedRestServer | W,H | From authenticated callbacks attempt nested core/bridge `rest_do_request`, direct dispatch, same/clone/reused request object, batch inner dispatch, second serve cycle, user-switch/clear then dispatch, invalid-route/exception cleanup and sequential redispatch. All denied before target callback; next independent HTTP request still works |
| G04 NonRestBypassTest | Guard authentication filters | H | XML-RPC including multicall, wp-login, admin/admin-ajax/admin-post and synthetic alternative auth handler denied for service; ordinary anonymous public reads and normal human password/cookie wp-login/wp-admin recovery remain usable both when healthy and during registry emergency; recognizable service-marked identities remain denied; emergency XML-RPC/remote-auth denial follows B02 |
| C01 NativeCapabilityMatrixTest | CapabilityPolicy | W,H | Own/other × post/page × draft,pending,publish,private,future,trash; remove read/edit/edit-others/edit-published/read-private/edit-private separately; private opt-in off/on; password exclusion; altered native mappings and privacy-policy page; no authority from role name |
| C02 ConcealedResourceTest | CapabilityPolicy, ErrorMapper | W,H | Missing versus inaccessible/wrong-type/password-protected content share safe 404 shape/message/reason; search contains no forbidden resource; broad BASE/type/taxonomy denial occurs before resource query |
| S01 SearchPrivacyTest | ContentReader, Projection | W,H | Authorized filtering before output; body/excerpt/meta/email omitted; no total or X-WP-Total headers; full source change alters summary version; private status explicit/default cases; no hidden result counts |
| S02 PaginationTest | query adapters, CursorCodec | U,W,H | Tied modified times/IDs, deterministic order, 0/1/50/invalid limits, >1,000 candidates, empty page with continuation, no authorized item skipped by scan advancement, end traversal, live edit/capability removal between pages; bounded query counts/scan volume |
| S03 CursorSecurityTest | CursorCodec | U,H | Tamper, truncation, excessive length, expiry, rotated key, wrong site/actor/route/filter/limit/ref; inspect encoded token to confirm no plaintext denied IDs/sort keys; authentication before detailed cursor errors |
| R01 RawContentTest | ContentReader, RevisionReader | W,H | Shortcode/dynamic block/embed and tool-like instructions returned byte-faithfully; render/network sentinels remain untouched; full body/excerpt or explicit error; no normalization, truncation or preview execution |
| R02 VersionProjectionTest | VersionHasher/serializers | U,W | RFC 8785 independent vectors, UTF-16 key order, safe integers, Unicode, null/set sorting; all projection fields affect version, excluded URL/revision ID do not; metadata subset version equals parent's full version |
| T01 TaxonomyReadTest | TermReader | W,H | Category/tag only, actual assign_terms permission, shared/custom mapping, parent omitted/zero/positive and wrong parent; hide_empty false; no count/meta; tag parent/custom taxonomy denied; bounded id-ascending pages |
| M01 MediaAuthorizationTest | MediaReader, CapabilityPolicy | W,H | Own/other attachment, unattached, allowed/inaccessible/private/password/missing/foreign-type parent; no upload_files needed when read/edit authority exists; capability removal; wrong object ID; list/direct policy agree |
| M02 MediaProjectionTest | MediaReader/local inspector | U,W,H | Static raster allowlist, animation/MIME mismatch/truncation, unknown dimensions/bytes, digest null where allowed; no EXIF/local path/subsizes/body; reject symlink/stream-wrapper/arbitrary file access; no URL fetch or file transformation; bounded inspection |
| D01 MetadataReadTest | MetadataReadPolicy, MetadataReader | U,W,H | Exact three logical names recognized, only editorial.note enabled; disabled SEO explicit versus omitted; raw storage key/wildcard/empty/duplicate keys denied; no all-meta call; absent/null versus empty; corrupt nonstring/multivalue data fails; denied parent queried first |
| V01 RevisionReadTest | RevisionReader, CapabilityPolicy | W,H | Parent authorized before any revision query, cross-parent denied, autosaves excluded, missing/pruned revision, empty list, post-type support unavailable, disabled saving with retained revisions; exact full raw source/hash; no restore side effects |
| E01 ErrorContractTest | ErrorMapper, ResponseFactory | U,W,H | Every Gate 2-relevant code/status/retry rule; authentication precedes schema detail; safe correlation handling; complete closed failures; reads always not_applied/null receipt; no raw WP_Error/exception/proxy-body forwarding |
| E02 DisclosureTest | all projections, audit sink | U,H | Seed credentials, SQL, paths, stack, email and plugin-response markers in failures; none in results/logs/artifacts; hidden IDs/cursor payloads/totals absent; useful authorized source remains data, not automatically redacted |
| L01 LimitAdmissionTest | ingress/admission/deadline/audit | U,H | Declared/missing/dishonest length, chunked body, compressed request, depth 20/21, duplicate members, Unicode bytes and response escaping; 60/61 reads, 120/121 preauth, 4/5 concurrent workers, window rollover/restart, full/corrupt counter/audit store, process failure releases lock slots; no unbounded queue or fail-open |
| I01 RouteInventoryTest | RouteRegistry, SiteReader | W,H | Exactly nine POST handlers; matching independent guard table; no get_mutation/write/approval/provisioning/generic endpoint; writes false and truthful tools/limits/metadata; strict/trusted informational policy shape preserved |

## Threat-model traceability

Every Gate 1 scenario retains its original later-gate owner. This table assigns the Gate 2 portions; it does not claim the whole threat is solved by reads.

| Threat IDs | Gate 2 implementation/test owner | Remaining boundary |
| --- | --- | --- |
| T01, T26 | No Gate 2 OAuth implementation; A02 rejects bearer-token substitution at WordPress | Provider/token/CIMD/PKCE behavior Gate 4, real client Gate 5 |
| T02 | Independent guard; A01-A03, G01-G04, B01-B02 | Direct hostile-client deployment evidence Gate 5; guard removal residual operator risk remains |
| T03, T21, T22 | I01 proves no mutation paths; C01 read-only authority separation | Editorial writes, publication/Trash/restore safety Gate 3 |
| T04 | R01 returns hostile stored instructions only as data; no new capabilities | Actual client prompt-injection boundary Gate 5 |
| T05 | Closed schemas and native checks; C01-C02, D01, T01 | Write-field escalation Gate 3 |
| T06, T23 | A03 and S03 fixed site/actor/cursor bindings | OAuth audience/subject Gate 4; cloned deployment Gate 5 |
| T07, T08, T24, T28 | I01 proves no journal/receipt/retry/write implementation; L01 covers read operational storage only | Mutation concurrency, journal/replay/crash/readback Gate 3 and MCP recovery Gate 4 |
| T09 | R02/E01 exact read identity/version/envelope; no receipts invented | Mutation receipt verification/projection Gate 3/4 |
| T10, T11 | E02, C02, S01, M01-M02, D01, V01 | MCP model/audit projection Gate 4, deployed logs Gate 5 |
| T12, T13 | M02 read eligibility/path boundaries only | Upload decode/re-encode/storage attacks Gate 3, deployment execution rules Gate 5 |
| T14, T27 | R01/M02 no rendering or URL fetching on reads | Write/restore policy Gate 3, file retrieval/OAuth metadata Gate 4, installed hooks Gate 5 |
| T15, T16 | L01, S02, E01 read ingress/output/scan/deadline/rate/lock/audit bounds | Upload/payload/journal quotas Gate 3/4 and deployed exhaustion Gate 5 |
| T17, T25 | A02/G04 no cookie/nonce/service approval shortcut; I01 no approval UI | Independent human approval UI/CSRF/intent binding Gate 3 |
| T18 | G01-G04 constrain stolen-service read reach; no writes available | Both approval profiles with direct hostile callers Gate 5; readable data remains exposed to a stolen authorized credential |
| T19 | M1-M6 and quality checks verify supported environment, not host integrity | Trusted WordPress/plugin/DB compromise and recovery remain Gate 5 operational limits |
| T20 | A04 HTTPS/proxy/header and real-client certificate tests | MCP network implementation Gate 4; deployed trust paths Gate 5 |
| T29 | I01/M02 prove no client-file URL/upload path in Gate 2 | File object/SSRF/source/decoder tests Gate 4/5 |
| T30 | A03/I01 reject policy/bookkeeping injection and expose no enabling endpoint | Policy enforcement, handle ownership, model evidence and strict approval Gate 3/4 |

## Acceptance and publication strategy

Preparation acceptance means review of these documents only. Implementation acceptance later requires all mandatory lanes passing at the exact candidate HEAD/tree, with per-family counts, no unresolved skipped security obligations, no uncaught warnings/deprecations attributable to project code, and published sanitized evidence. Record any upstream warning with its version/source and narrow treatment; do not globally silence PHP 8.5 deprecations.

Future Actions should run on PRs and branch pushes with read-only repository permissions, no production secrets and no privileged execution of untrusted fork code. Cache by lock/core/image hashes; isolated database/volume per job; bounded timeouts and cleanup. Pin each workflow action to an immutable commit. Environment setup verifies pinned core source/version and guard state before issuing credentials. A failed preflight must demonstrate zero issued credentials.

The implementation candidate must also pass an explicit route/file inventory, native-API no-effect checks, secret/disclosure audit, complete-source/version/cursor vectors, runtime package load with development vendors absent, and documentation handoff review. No production acceptance follows from CI. Human review and separate gate acceptance remain required. No workflow, CI run, test fixture, credential, user or deployment was created in this preparation execution.

## Primary sources and dates

All sources below were inspected on **2026-10-05**. Rolling pages are evidence as observed on that date; fixed 7.1.2 source was read through GitHub at the stated ref. Facts from these sources inform the project choices above, rather than endorsing Coagmentator.

| ID | Primary source | Publication/update or version context |
| --- | --- | --- |
| S01 | [WordPress release archive](https://wordpress.org/download/releases/), [7.1.2 release record](https://wordpress.org/documentation/wordpress-version/version-7-1-2/) | 7.1.2 released 2026-09-22; archive inspected 2026-10-05 |
| S02 | [WordPress 7.1.2 version.php](https://github.com/WordPress/wordpress-develop/blob/7.1.2/src/wp-includes/version.php) | Fixed 7.1.2 ref; PHP 7.4, MySQL 5.5.5, core extensions json/hash |
| S03 | [PHP compatibility and WordPress versions](https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/) | Last updated 2026-08-19; 7.1 supports PHP 8.3/8.4/8.5 |
| S04 | [WordPress host requirements](https://wordpress.org/about/requirements/) | Rolling page, inspected 2026-10-05; recommendations distinguished from source minimums |
| S05 | [PHP supported versions](https://www.php.net/supported-versions.php) | Rolling upstream lifecycle table, inspected 2026-10-05 |
| S06 | [MariaDB maintenance policy](https://mariadb.org/about/#maintenance-policy) | Rolling lifecycle table, inspected 2026-10-05 |
| S07 | [MySQL Innovation and LTS releases](https://dev.mysql.com/doc/refman/8.4/en/mysql-releases.html) | MySQL 8.4 reference manual, inspected 2026-10-05 |
| S08 | [PHPUnit supported versions](https://phpunit.de/supported-versions.html) | Rolling lifecycle table, inspected 2026-10-05; distinguishes bugfix/life support |
| S09 | [PHPUnit 12 installation](https://docs.phpunit.de/en/12.5/installation.html) | 12.5 manual, inspected 2026-10-05; PHP 8.3 minimum |
| S10 | [PHPUnit compatibility and WordPress versions](https://make.wordpress.org/core/handbook/references/phpunit-compatibility-and-wordpress-versions/) | Last updated 2026-08-19; 7.1 integration suite uses PHPUnit 9 |
| S11 | [WordPress 7.1.2 Composer manifest](https://github.com/WordPress/wordpress-develop/blob/7.1.2/composer.json), [PHPUnit Polyfills](https://github.com/Yoast/PHPUnit-Polyfills) | Fixed core ref plus maintainer documentation, inspected 2026-10-05 |
| S12 | [WordPress core PHPUnit handbook](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/) | Rolling official testing instructions, inspected 2026-10-05 |
| S13 | [WordPress env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/) | Rolling official package documentation, inspected 2026-10-05 |
| S14 | [PHPStan getting started](https://phpstan.org/user-guide/getting-started) | Maintainer documentation, inspected 2026-10-05; core 7.1.2 also uses PHPStan 2.x, but project level/configuration is our choice |
| S15 | [WordPress Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards) | Maintainer repository, inspected 2026-10-05; choose compatible pair and lock at C01 |
| S16 | [Composer dependency/lock guidance](https://getcomposer.org/doc/01-basic-usage.md) | Maintainer documentation, inspected 2026-10-05 |
| S17 | [Docker Compose](https://docs.docker.com/compose/) | Maintainer documentation, inspected 2026-10-05 |

Security/API research also inspected the fixed 7.1.2 [REST server](https://github.com/WordPress/wordpress-develop/blob/7.1.2/src/wp-includes/rest-api/class-wp-rest-server.php) and [capability mapper](https://github.com/WordPress/wordpress-develop/blob/7.1.2/src/wp-includes/capabilities.php), plus current official references for [Application Password validation](https://developer.wordpress.org/reference/functions/wp_authenticate_application_password/), [pre-success constraints](https://developer.wordpress.org/reference/hooks/wp_authenticate_application_password_errors/), [success event](https://developer.wordpress.org/reference/hooks/application_password_did_authenticate/), [REST auth errors](https://developer.wordpress.org/reference/hooks/rest_authentication_errors/), [internal dispatch](https://developer.wordpress.org/reference/functions/rest_do_request/), [server selection](https://developer.wordpress.org/reference/functions/rest_get_server/), [current-user filter](https://developer.wordpress.org/reference/hooks/determine_current_user/), [authentication filter](https://developer.wordpress.org/reference/hooks/authenticate/), [raw get_post](https://developer.wordpress.org/reference/functions/get_post/), [meta registration](https://developer.wordpress.org/reference/functions/register_post_meta/), [attachment metadata](https://developer.wordpress.org/reference/functions/wp_get_attachment_metadata/), [managed attachment file](https://developer.wordpress.org/reference/functions/get_attached_file/), [term queries](https://developer.wordpress.org/reference/classes/wp_term_query/__construct/), [revision permissions](https://developer.wordpress.org/reference/classes/wp_rest_revisions_controller/get_items_permissions_check/), [revision queries](https://developer.wordpress.org/reference/functions/wp_get_post_revisions/), [autosave detection](https://developer.wordpress.org/reference/functions/wp_is_post_autosave/) and [RFC 8785](https://www.rfc-editor.org/rfc/rfc8785).

These source reads establish API behavior and the basis for the plan. They do not establish that our future code works. No outside implementation, including WPVibe, was copied.
