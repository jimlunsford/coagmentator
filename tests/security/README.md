# C02 security verification

The MU bundle is `wordpress/mu-plugins/coagmentator-guard.php` plus its entire `coagmentator-guard/src` directory. It requires no normal plugin, autoloader or Composer vendor. Install it before creating a protected service identity or Application Password. Its presence does not authorize a bridge operation.

The disposable harness runs the unchanged C01 controls first, then installs the MU bundle. Local CLI setup generates random service passwords, exercises interactive denial without exporting the ordinary password, and discards it. A separate marker-only subscriber has no bridge credential. No public provisioning endpoint exists. Test files under `tests/fixtures` are never part of either runtime package.

Before credential issuance, verified HTTPS preflight exercises an empty credential allowlist, native/bridge/cookie denial and actual new/existing human login/admin recovery in healthy, missing-registry and malformed-registry states. Missing-loader preflight must refuse issuance. Only a subsequent successful local preflight can issue disposable service/human Application Passwords. Secret/UUID values remain in a mode-0600 runtime file, excluded from Git and artifacts. Cleanup scans evidence before destroying secrets, revokes and verifies credential removal, deletes all three users, then destroys isolated containers and volumes even after failures.

| Suite | Guard proof |
| --- | --- |
| B02 GuardIndependenceTest | All nine names deny without fixed handlers; plugin/component scenarios; marker-only nonauthority; emergency credential/alternate-auth rejection; restoration |
| G01 NativeRestBypassTest | Native editorial/user/credential routes, valid batch request, plugin/Abilities routes; repeat with promoted unmarked protected ID |
| G02 RouteNormalizationTest | Verbs, raw/encoded/double-encoded path aliases, dot/backslash/case, query aliases, JSONP/embed/envelope, body and header method overrides |
| G03 InternalDispatchTest | Actual core Application Password success reaches only a synthetic failure callback; same/clone/native/batch nested dispatch, second serve, clear/switch, exception and next independent request |
| G04 NonRestBypassTest | Service/marker cookies, alternate injection, admin/AJAX/admin-post/login, valid XML-RPC single/multicall; healthy unrelated human XML-RPC and emergency human recovery |
| ReplacementTest | Fixed class present but callback replaced at the same path; validation, permission and target sentinels remain unchanged |

Each denial compares an independent snapshot of callback counters, editorial tables, users/roles and credential UUID/hash records. Successful human login sessions and Application Password last-use accounting are excluded from the protected-state digest. This is an assertion probe inside disposable test infrastructure, not a production endpoint. Canonical bridge denials also assert the minimal closed failure envelope.

The runner repeats B02/G01/G02/G04 in separate fresh PHP processes for active, deactivated, absent/deleted plugin; malformed/missing policy; missing/malformed registry with plugin active and inactive; unreadable registry; protected ID promoted and unmarked; missing guard support file; restored configuration. G01/G02/G04 run again with the synthetic fixed handler loaded so handler absence cannot mask an ineffective route fence. That fixture never implements a real read or returns bridge success.

Every required lane runs on GitHub-hosted disposable infrastructure: PHP 8.3/8.4/8.5 crossed with MariaDB 10.11 and MySQL 8.4. Exact C01 patch versions, images, source commits, locks and TLS verification remain pinned. See the C02 work note for actual candidate/run evidence. C03 and later checkpoints remain unstarted.
