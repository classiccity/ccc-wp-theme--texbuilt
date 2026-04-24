# Architecture

Why the multi-repo, parent-plus-child structure is set up the way it is.
Short version so future-you (and future teammates) understand the "why"
behind the plumbing without archaeology.

---

## The shape

```
┌──────────────────────────────────────────────────────────┐
│   classiccity/ccc-wp-theme                               │
│   ───────────────────────                                │
│   Shared parent theme. All blocks, templates, styles,    │
│   FSE parts, and orchestration code.                     │
│   One git repo. Tagged versions.                         │
└──────────────────────────────────────────────────────────┘
                         ▲
                         │ git submodule
                         │
        ┌────────────────┼────────────────┐
        │                │                │
        ▼                ▼                ▼
┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ ccc-wp-theme │ │ ccc-wp-theme │ │ ccc-wp-theme │
│ --texbuilt   │ │ --lumberock  │ │ --blackwood  │
│              │ │              │ │              │
│ Client repo. │ │ Client repo. │ │ Client repo. │
│ Deploys to   │ │ Deploys to   │ │ Deploys to   │
│ their WPE.   │ │ their WPE.   │ │ their WPE.   │
└──────────────┘ └──────────────┘ └──────────────┘
```

Each **client repo** represents the **WordPress site root** on that
client's WP Engine install (the directory containing `wp-admin/`,
`wp-includes/`, `wp-content/`, `wp-config.php`, etc.). WP Engine's Git
Push always deploys to the site root and doesn't support a subdirectory
target, so the repo structure has to mirror the site layout. A
whitelist-style `.gitignore` keeps only the files we actually version:

- `wp-content/themes/classic-city-core/` — the parent theme, pulled in
  as a **git submodule** pointing at a pinned commit of `ccc-wp-theme`.
- `wp-content/themes/sg-{slug}/` — that client's child theme, versioned
  directly in the client repo.

Everything else (WP core, `wp-config.php`, plugins, mu-plugins, uploads,
cache) lives on WPE and in the install's admin — never in git.

## Why submodule (not copy, not package)

**Copying** the parent theme into each client repo means every parent-
level fix is N simultaneous commits across N client repos. Quick way to
have clients drift out of sync.

**Composer / npm package** is overkill for WordPress theme code and
introduces a build step that WP Engine's Git Push doesn't natively run.

**Submodule** gives the best of both:
- One source of truth for parent theme code.
- Each client pins to a specific parent commit (or tag), so a parent
  change doesn't silently break live sites — the client repo has to
  explicitly bump its pointer to adopt new parent code.
- Updating parents is two commits (parent repo + pointer bump in client
  repo), not N.

## Why per-client WP Engine installs (not multisite)

- Each client owns their data, uploads, users, plugins, and billing
  cleanly without "whose subsite is this" hand-wringing.
- Plugin/theme conflicts isolated per client.
- WP Engine's staging/production copy flow works per install and is
  well-tested; multisite staging is more fragile.
- Downside: N installs = N WP Engine line items. Acceptable cost for
  the separation.

## Why content gets authored on WP Engine staging (not migrated from sandbox)

- The style-guide local sandbox (`the-style-guide-wp.local`) is a
  **design prototype** for the block system, not a content source.
- Serialized block data with attachment IDs does not migrate cleanly
  between installs (IDs re-map, media reuploads break references).
- Authoring on the real staging environment means the client sees their
  real site being built — no "this is a mockup" disclaimer needed.
- The sandbox stays useful: it's where new block types and parent-theme
  features get designed before they ship to any client.

## How code flows

**Parent theme change:**
1. Edit parent theme locally (in any submodule checkout or a standalone
   clone of `ccc-wp-theme`).
2. Commit + push to `ccc-wp-theme` main.
3. In each client repo that should adopt the change:
   `git submodule update --remote classic-city-core`
   `git commit -am "Bump parent theme"`
   `git push wpe main`  (or origin, then wpe)

**Child theme change (client-specific):**
1. Edit in that client's `sg-{slug}/` folder.
2. Commit + push to the client's GitHub origin.
3. Push to WPE: `git push wpe main`. Live in ~30 seconds.

**Content change:**
- Done in WP admin on the WPE staging environment.
- Promoted via WP Engine's "Copy Environment" (staging → production) when
  ready.
- Never in git.

## Local dev model

**Sandbox** (`the-style-guide-wp.local`): style guide demo + content
import tooling. Parent theme + all child theme scaffolds live here for
rapid iteration. Not meant for per-client production content.

**Per-client local** (e.g., `texbuilt.local`): mirrors that client's
WPE install 1:1. Used for code work that needs to see real client
content/uploads. Created via Local's "Pull from WP Engine" integration.
The `wp-content/themes/` folder IS the client's git repo, with the
parent theme checked out as a submodule.

This means two Local sites exist per active client:
- The sandbox (shared, always)
- The client's local mirror (for code work against real content)
