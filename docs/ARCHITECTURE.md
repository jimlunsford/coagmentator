# Architecture

## Overview

Coagmentator separates the AI protocol boundary from the WordPress boundary.

```text
+----------------------+
|      AI Client       |
+----------+-----------+
           |
           | MCP over HTTPS
           v
+----------------------+
| Coagmentator MCP     |
| Server               |
|                      |
| - MCP transport      |
| - client auth        |
| - tool schemas       |
| - orchestration      |
| - result shaping     |
| - audit correlation  |
+----------+-----------+
           |
           | authenticated HTTPS
           v
+----------------------+
| Coagmentator         |
| WordPress Bridge     |
|                      |
| - REST endpoints     |
| - capability checks  |
| - input validation   |
| - WordPress API use  |
| - mutation receipts  |
+----------+-----------+
           |
           v
+----------------------+
|      WordPress       |
+----------------------+
```

## Component 1: MCP server

Initial implementation target: TypeScript using the official MCP SDK or the current recommended SDK at implementation time.

Responsibilities:

- expose MCP-compatible tools
- enforce input schemas before forwarding requests
- authenticate AI clients according to current MCP client requirements
- map MCP tool calls to narrow WordPress bridge operations
- normalize error responses
- attach correlation identifiers to mutations
- avoid ever returning WordPress credentials to the client
- maintain service-level audit metadata

The MCP server should not directly modify the WordPress database or filesystem.

## Component 2: WordPress bridge plugin

Responsibilities:

- expose only Coagmentator REST endpoints
- authenticate the dedicated service identity
- enforce WordPress capabilities for every operation
- validate and sanitize WordPress-facing input
- call supported WordPress APIs
- return normalized results
- create mutation receipts suitable for verification and audit

The plugin should use WordPress APIs instead of direct database access wherever practical.

## Component 3: shared contracts

Shared contracts define stable request, response and error shapes between the MCP server and the WordPress bridge.

The goal is to prevent either side from relying on undocumented behavior.

Expected contract families:

- identity and site information
- content lookup
- content mutations
- taxonomy
- media
- metadata
- revisions
- error envelopes
- mutation receipts

## Trust boundaries

### Boundary A: AI client to MCP server

The MCP server must assume the client can request any exposed tool with any schema-valid values. Safety cannot depend on a model choosing not to request a dangerous operation.

Therefore exposed tools themselves must be safe and appropriately scoped.

### Boundary B: MCP server to WordPress

The WordPress bridge must not trust the MCP server merely because it is the expected caller. It must authenticate the caller and enforce WordPress authorization independently.

### Boundary C: WordPress internal operations

Even authenticated requests must pass explicit per-operation capability checks and validation.

## Authentication

### WordPress side

The initial preferred direction is a dedicated WordPress service user with the least privileges necessary and WordPress Application Password authentication over HTTPS.

This choice should be validated during the authentication design gate before implementation is locked.

### MCP client side

The exact MCP-facing authentication method is intentionally not frozen at project inception. It must be selected against the current authentication requirements of the target MCP clients when that gate begins.

## Initial tool surface

The MVP is expected to include some version of:

- `site_info`
- `search_posts`
- `get_post`
- `create_post`
- `update_post`
- `create_draft`
- `publish_post`
- `trash_post`
- `list_pages`
- `get_page`
- `update_page`
- `list_categories`
- `create_category`
- `list_tags`
- `search_media`
- `upload_media`
- `set_featured_image`
- `get_post_meta`
- `update_post_meta`
- `get_revisions`
- `restore_revision`

The exact list will be normalized during the contracts gate. Separate tools may be consolidated where one explicit schema can remain clear and safe.

## Mutation model

Mutations should follow this conceptual flow:

```text
request
  -> schema validation
  -> authentication
  -> WordPress authorization
  -> operation validation
  -> mutation
  -> post-mutation readback
  -> mutation receipt
  -> normalized result
```

A transport-level success alone is not sufficient proof of a successful mutation.

## Repository model

Start as a monorepo so both sides of the protocol evolve at the same revision.

Expected layout:

```text
apps/mcp-server/
wordpress/coagmentator/
packages/contracts/
docs/
tests/
```

Splitting repositories later is allowed only if repository scale or release independence creates a concrete benefit.
