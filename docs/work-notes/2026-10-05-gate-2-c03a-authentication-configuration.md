# Work Note: Gate 2 C03A Authentication Evidence and Configuration Foundation

Date: 2026-10-05 America/Indiana/Indianapolis (2026-10-06 UTC)
Roadmap gate: Gate 2, C03A
Status: PARTIAL, implementation prepared; single focused CI pending

## Goal

Implement only authentication evidence and the closed configuration/binding foundation. One implementation pass and one focused CI execution are authorized. A real CI failure or runner-infrastructure block ends this execution after recording evidence. No fix/push/rerun loop.

## Starting state

- Repository: `jimlunsford/coagmentator`.
- Verified remote/main HEAD: `11db731b9d6fbf016b0bf6dfe7d565a1d10732ed`.
- Verified complete tree: `6fd663cd18d2c1e3a03a069198d2da2c2bfea8a8`.
- Accepted C02 merge: `88bbb46ae2593a7722d8cc8a06503f20a5041738`; PR #4 independently reports merged.
- Gate 0, Gate 1, Gate 2 preparation, C01 and C02 ACCEPTED. C03 NOT STARTED before this work; Gate 2 IN PROGRESS; Gate 3 NOT STARTED.
- Fresh remote branches and all PRs show no prior C03 branch/implementation. Current Git objects, mandatory doctrine, plan, matrix and C02 closeout were read before changing files. The normal plugin was the dormant C01 skeleton; no runtime ReadController existed.
- No production identity/credential/access/deployment is recorded. Production was not contacted to inventory it; this is repository/test evidence and the session boundary, not a production audit.
- Branch created locally from exact main: `feature/gate-2-c03-auth-config-operations-foundation`. Main is not modified.

## Work completed

Normal-plugin components:

- `Config/Identity_Values`: canonical UUIDv4, safe integer ID and bounded actor syntax.
- `Config/Credential_Window`: one approved UUID or an explicit absolute two-UUID interval of at most 24 hours; each identity check revalidates expiry. Expired overlap requires trusted policy repair and does not auto-select a credential.
- `Config/Operator_Path`: bounded absolute path syntax and lexical/resolved exclusion of web/code roots, including existing ancestor symlinks; no future storage opened or created.
- `Config/Feature_Config`: 16 KiB canonical compact JSON, closed fields/types, independent registry consistency, enabled site/actor/service binding, UUID overlap, canonical origin/path, private-read flag, exact read-operation set, policy version/profile, writes false, and later cursor/audit/admission references. No HTTP-selected configuration path or secret field.
- `Auth/Authentication_Evidence`: observes only user ID/UUID from the authentic core success event, intersects with live guard admission/current-user equality/core availability, refreshes credential existence through the core API, rejects persistence and multiple/failed events.
- `Auth/Bridge_Identity`: fresh boolean identity check with exact site/actor/service/credential/policy binding. No caller-supplied user or UUID and no role/marker grant.
- Explicit `src/foundation.php` includes and normal-plugin observer loading. No Composer runtime dependency, route, capability policy, role provisioner, serializer or read operation.

The accepted C02 MU package is unchanged. Its route/object/depth/lifecycle fences remain authoritative. The identity check is not an external-dispatch grant. The normal observer registers after the guard, and it never registers for plaintext-password arguments, retains an entire credential record, or reads request Authorization/nonce/cookie fields.

The permanent [C03A configuration document](../C03A-CONFIGURATION.md) owns the exact schema and limitations. No accepted D-series decision changes.

## Tests added and focused workflow

- Unit `BindingConfigurationTest`: exact/disabled binding, wrong user/site/actor/UUID, malformed/missing/unknown fields, duplicates, unsafe paths/symlinks, registry mismatch, invalid profiles, write refusal under both profiles, bounds and no storage side effects.
- Unit `CredentialWindowTest`: one/two/more credentials, duplicate/malformed UUIDs, missing/unknown/timing types, future/expired/overlong intervals, exact 24-hour boundary and expiry recheck on an existing object.
- HTTP `ApplicationPasswordIdentityTest`: 17 trusted CLI scenarios, using actual core Application Password events and the normal plugin. Correct service, valid wrong Administrator/subscriber, wrong configured service, wrong UUID, UUID versus app_id, wrong site/actor, disabled binding, valid/expired overlap, promotion/marker removal, role-name-only authority denial, missing normal evidence, mismatched current user, revocation after event and subsequent revoked authentication.
- HTTP `AlternateAuthenticationTest`: anonymous, service/human cookie, nonce, cookie plus nonce, injected current user, JWT-like plugin identity, OAuth bearer, Basic without authentic event, normal login password and marker-only cookie. Each follows a separate positive authenticated foundation evaluation and independently asserts the normal foundation was invoked, denied identity, no success event and no synthetic callback. Protected editorial/user snapshots must remain equal.
- A disposable synthetic controller uses normal identity permission checks, always returns failure, and verifies serialization denial and stale evidence denial after guard finish. It lives only under tests and is copied into the disposable test installation. No runtime ReadController is added.
- Existing C01 smoke/TLS controls and complete C02 guard/preflight/no-effect tests remain unchanged and run on the single focused lane first.
- New push-only `c03a.yml`: three unit/lint lanes (8.3, 8.4, 8.5), PHP 8.4 PHPStan/WPCS/two locked Composer audits, and exactly PHP 8.4 + MariaDB 10.11 integration/HTTP. No PR is needed for this workflow. C01/C02 workflows, source/image pins, lockfiles and assertions are unchanged.

## Disposable credentials and cleanup plan

Only the accepted disposable Actions topology may issue test credentials. The unchanged C02 preflight creates the primary service/human credentials after guard controls pass. C03A creates one extra disposable service credential with a deliberately different UUID/app_id only after that preflight and primary fixture exist. Secrets stay in the existing mode-0600 runtime fixture/memory, are excluded from artifacts and are scanned before cleanup. No production credential or service identity is created.

The existing C02 cleanup deletes all credentials for each fixture user, verifies empty credential lists, deletes users and the protected fixture, then tears down containers/volumes/networks through the EXIT trap. The overlap credential is owned by the same service user and included in that revocation. Executed cleanup evidence must be recorded below; planned cleanup is not a pass claim.

## Verification before publication

- Exact starting Git state and remote/PR metadata verified.
- Shell syntax and workflow topology inspected: three unit jobs, one database job, no six-lane matrix for C03A.
- `git diff --check` and scope/pin/guard preservation checked before publication.
- Local PHP, Composer and Docker are unavailable in this Work shell. No local runtime execution is claimed; the one accepted Actions run supplies all runtime/quality evidence.
- Official WordPress success-event and UUID-lookup references were rechecked, without changing accepted core/environment pins. Credential UUID, not app_id, is the core lookup identity.

## Exact candidate and CI evidence

Pending publication of this implementation commit. The immutable implementation checkpoint is the first commit introducing this work note, with sole parent the starting main above. The literal tested HEAD/tree, run/job IDs, results and cleanup evidence will be appended in a documentation-only handoff commit after the one focused execution. No self-referential Git hash is embedded in its own commit.

## Risks and deviations

C03 is partial and unaccepted. No C03B transport/proxy enforcement, C03C rate/concurrency/audit/flock behavior, full C03D matrix, C04 capability policy, bridge reads or Gate 3 work is implemented. Stored paths/flags are configuration references, not operational claims. C02 guard-only failure finalization remains unchanged. No production access, deployment, merge, tag or release.

## Exact next action

Publish this bounded candidate on the C03 branch and execute the one focused workflow. If any job actually fails, inspect and record the failing step and stop for a separate C03A continuation. If runner infrastructure blocks execution, record that and stop without code churn. Only a verified C03A pass can make separately authorized C03B the next implementation substep; do not begin it here.
