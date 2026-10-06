# C03B Transport and Proxy Foundation

C03B is ACCEPTED and merged unchanged through [PR #6](https://github.com/jimlunsford/coagmentator/pull/6); see the [acceptance closeout](work-notes/2026-10-06-gate-2-c03b-closeout.md). C03 and Gate 2 both remain IN PROGRESS. It adds transport evidence only. C03A remains ACCEPTED; C03C, C04 and Gate 3 remain NOT STARTED. No runtime ReadController or bridge data handler is added.

## Closed configuration extension

The existing host-managed Feature_Config schema requires one new field, `transport`, immediately after `bridge_path`. It is exactly `{mode, trusted_proxies}`, in that order. `mode` is `direct_tls` or `trusted_proxy`. Direct TLS requires an empty list. Proxy mode requires 1 to 16 unique canonical exact IPv4/IPv6 addresses. No DNS names, wildcard, CIDR, zones, unspecified addresses, mapped-IPv4 aliases, duplicate peers, inferred trust or unknown fields are accepted. IPv6 uses the lowercase compressed `inet_ntop` representation. The operator selects peers out of band; request headers never extend that list.

Existing `home_origin`, `home_path`, `bridge_origin` and `bridge_path` remain authoritative. Origin is canonical HTTPS, and bridge origin must equal home origin. Path is the canonical home path followed by `wp-json/coagmentator/v1`. `Transport_Policy` checks an exact known operation, POST, raw URI, empty query, guard-prefix consistency and canonical authority. It never accepts a generic path prefix. Existing identity, rotation, policy and storage-reference checks remain unchanged. The unreleased schema still has version 1, now requiring the explicit transport object; an earlier file lacking it fails closed. No production migration or credential-bearing example is introduced.

## Direct TLS and proxy facts

Direct mode requires the web server's actual `HTTPS` value (`on` or `1`) and exact `HTTP_HOST`. Port 443 alone cannot prove TLS. Nonempty `Forwarded`, `X-Forwarded-Proto`, `X-Forwarded-Host` or `X-Forwarded-Port` is rejected, so forwarding claims cannot substitute for direct TLS.

Proxy mode checks the immediate HTTP peer in `REMOTE_ADDR` against the exact configured list before using forwarding facts. The only accepted forwarded authority is one exact `X-Forwarded-Proto: https` and one exact `X-Forwarded-Host` matching the configured authority. Host must also match. Comma lists, whitespace/case variants, wrong ports, missing values, conflicting `Forwarded` and separate forwarded-port representations fail closed. `X-Forwarded-For` never establishes peer identity. The web server must preserve the immediate socket peer rather than rewriting it from an untrusted header.

The trusted edge overwrites scheme/host, clears alternate Forwarded/port/for representations and preserves the canonical Authorization header. Its normal configuration never appends caller authority values. It rejects a wrong incoming Host instead of laundering it into the configured host. Application Password validation remains WordPress core's job; no header becomes a user, UUID or identity grant. Alternate authorization headers are cleared. No Authorization value is logged or returned.

## Early host boundary and the independent guard

C02 MU files are unchanged. A trusted-proxy deployment must explicitly install host bootstrap before `wp-settings.php`, loading fixed local definitions and the operator-selected policy. `Transport_Evidence::bootstrap()` validates peer/scheme/authority before setting HTTPS for WordPress. Failed proxy validation sets HTTPS off and port 80 so core's port-only fallback cannot upgrade an untrusted request. Direct mode retains the web server's actual HTTPS state. The method validates connection facts for normal WordPress paths too; it does not grant a bridge path. The later transport evaluation independently requires the exact configured bridge operation URI.

The disposable host bootstrap is a fixture, not an automatically installed production configuration or deployment recipe. A real operator must separately review the early include locations, host file permissions, immediate-peer preservation, ordinary human HTTPS/session behavior during host failures, private backend isolation and edge configuration before deployment. This step does not claim production readiness or add a feature dependency to the independent guard. Missing feature handlers still cannot create a guard-approved callback.

`Bridge_Identity::allows()` now requires Transport_Evidence as an additional conjunct. Authentic Application Password event, current-user equality, live credential lookup, UUID window, site/actor binding, guard readiness and request-local evidence remain mandatory. Transport returns a boolean and supplies no authentication identity. Capabilities and admission/audit/concurrency remain later work.

## Disposable real-edge evidence design

The original C01 topology and certificate negative controls run first, followed by all C02 and C03A assertions. The direct Nginx fixture no longer fabricates forwarded scheme/host values for its direct TLS connection. It continues to pass canonical Authorization and actual server HTTPS.

The C03B phase adds an internal-only Nginx HTTP backend using the same pinned image. The TLS edge connects to that backend, whose FastCGI REMOTE_ADDR is the edge's immediate HTTP socket address. Trusted CLI setup resolves that fixed service once and writes its exact address into host configuration. Tests also connect directly from the untrusted client to the backend while forging all relevant headers. There are no published ports, production destinations or new external runtime egress.

The canonical subdirectory fixture sets WordPress home/site URL to `https://wordpress.test/journal`, host guard prefix to `/journal/wp-json`, and feature bridge path to `/journal/wp-json/coagmentator/v1`. Nginx internally maps that installation location to the disposable code directory while preserving the original REQUEST_URI. Actual WordPress routing must accept the exact subdirectory request; the root bridge alias remains denied. No route match is relaxed.

The existing synthetic C03A controller is reused unchanged. It observes identity admission and returns a closed 403 failure without site/content data. Independent snapshots compare protected editorial/user/credential state and target counters. The actual core event and synthetic callback counters distinguish transport admission from authentication or callback success.

A separate same-CA certificate covers the redirect destination without modifying C01's original certificate or wrong-hostname control. A credential-free control proves that destination works. The credential-bearing bridge request receives a 307; the client disables redirect following, reports zero followed redirects and leaves the destination counter unchanged. Certificate failures use the actual verifying client before HTTP admission, not simulated PHP variables.

## Coverage map

| Requirement | Real HTTP scenario or retained control |
| --- | --- |
| A04 1-5 | direct, direct-http, invalid-certificate, wrong-certificate-host, redirect; retained C01 TLS controls |
| A04 6-7 | direct-forged-proto, direct-forged-host |
| A04 8-11 | proxy, proxy-spoof, actual core event and identity/callback counters |
| A04 12 | proxy-untrusted; peer spoofing via X-Forwarded-For cannot help |
| A04 13 | proxy-multiple-scheme, proxy-malformed-scheme, proxy-multiple-host, proxy-malformed-host, proxy-conflicting-forwarded |
| A04 14-15 | wrong-configured-host, wrong-request-host, wrong-configured-path |
| A04 16 | subdirectory, subdirectory-alias, WordPress home/site URL and prefix observations |
| A04 17 | verified independent redirect destination, zero redirect count and no destination callback/replay |
| A02 carryover | proxy-strip, proxy-strip-cookie (real human cookie/nonce), proxy-strip-injected, proxy-alternate-authorization |
| Availability carryover | app-disabled, app-user-disabled, with zero core success events and no identity/callback |
| HTTP downgrade | proxy-http, valid peer but non-HTTPS scheme denied |

Pure unit vectors separately cover closed mode/list parsing, exact IP representations, direct TLS state, ambiguous authorities and subdirectory/encoding aliases. Real HTTP results, exact totals, candidate identities and cleanup belong to the dated work note. One new narrowly scoped PHPCS exception permits the single runtime Basic encoding used to prove valid alternate authorization headers cannot replace the canonical header. Existing accepted exceptions are unchanged.

References rechecked 2026-10-06: [Nginx proxy header handling](https://nginx.org/en/docs/http/ngx_http_proxy_module.html#proxy_set_header), [WordPress is_ssl](https://developer.wordpress.org/reference/functions/is_ssl/). These establish upstream behavior, not acceptance of this candidate.
