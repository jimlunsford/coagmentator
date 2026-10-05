# Security Model

Coagmentator is security-sensitive infrastructure because it turns natural-language intent into authenticated changes on a WordPress installation.

The security model assumes mistakes, malformed requests and hostile input are possible.

## Core invariants

1. The AI client never receives reusable WordPress credentials.
2. Every WordPress operation requires explicit authorization.
3. Tool exposure is an allowlist, not a blocklist.
4. Schema validation occurs before an operation reaches WordPress logic.
5. WordPress capability checks occur inside WordPress for every protected operation.
6. Mutation responses include verification of resulting state where practical.
7. Secrets are never written to logs, work notes, fixtures or test snapshots.
8. Permanent destructive actions are excluded from the MVP.
9. Generic remote execution is excluded from the MVP.
10. Security controls must not depend on prompt wording or model obedience.

## Explicitly prohibited in the MVP

- arbitrary shell commands
- arbitrary WP-CLI commands
- arbitrary SQL
- arbitrary PHP or `eval`
- arbitrary filesystem writes
- arbitrary plugin installation or activation
- arbitrary theme installation or activation
- arbitrary user or credential management
- reading `wp-config.php`
- returning raw server secrets
- hard-deleting posts as the default delete behavior

## Dedicated service identity

The WordPress bridge should use a dedicated service identity with the narrowest capabilities that support the enabled tool set.

The service identity should not reuse a human administrator password.

## Logging

Audit records should include:

- timestamp
- operation
- correlation ID
- target resource identifiers
- authenticated service identity
- outcome
- relevant before/after identifiers or hashes when useful

Audit records must not include passwords, tokens, application passwords, authentication headers or secret environment values.

## Write verification

After important mutations, Coagmentator should read the changed resource back through WordPress and compare relevant fields before reporting success.

## Future privileged capabilities

If future versions add theme editing, plugin operations, WP-CLI or other high-risk features, those capabilities must be separated from the editorial tool surface and require a new documented threat-model review before implementation.
