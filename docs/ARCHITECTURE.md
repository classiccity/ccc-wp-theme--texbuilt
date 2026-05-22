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
                         │ git subtree (merged in, --squash)
                         │
        ┌────────────────┼────────────────┐
        │                │                │
        ▼                ▼                ▼
┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ ccc-wp-theme │ │ ccc-wp-theme │ │ ccc-wp-theme │
│ --texbuilt   │ │ --lumberock  │ │ --blackwood  │
│              │ │              │ │              │
│ Client repo. │ │ Client repo. │ │ Client repo. │
│ Repo root =  │ │ Repo root =  │ │ Repo root =  │
│ site root.   │ │ site root.   │ │ site root.   │
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

- `wp-content/themes/classic-city-core/` — the parent theme, merged in
  as a **git subtree** from `ccc-wp-theme`. The files live in the client
  repo as real tracked content (via squashed merge commits), NOT as a
  submodule pointer.
- `wp-content/themes/sg-{slug}/` — that client's child theme, versioned
  directly in the client repo.
- `wp-content/mu-plugins/ccc-fix-auth-header.php` — platform mu-plugin
  that restores `Authorization:` for REST API Basic Auth on WPE.

Everything else (WP core, `wp-config.php`, plugins, other mu-plugins,
uploads, cache) lives on WPE and in the install's admin — never in git.

## Why subtree (not submodule, not copy, not Composer)

**Copying** the parent theme into each client repo means every parent-
level fix is N simultaneous commits across N client repos. Quick way to
have clients drift out of sync.

**Composer / npm package** is overkill for WordPress theme code and
introduces a build step that WP Engine's Git Push doesn't natively run.

**Submodule** would be the cleanest design — one source of truth, client
pins to a commit, bump the pointer to adopt new parent code. **It does
not work with WP Engine's Git Push.** WPE's deploy runs a verification
pass it calls "checking submodules" but does NOT actually clone submodule
contents into the deploy target. Result: the parent-theme directory is
empty on the server after a push, WordPress can't find `style.css`, child
themes fail to activate. Confirmed during TexBuilt onboarding.

**Subtree** is the compromise that works:
- The parent theme's files are merged INTO the client repo (via
  `git subtree add --squash`). Real tracked files, real history.
- `git push wpe main` deploys them like any other file. No submodule
  gymnastics required.
- Upstream parent updates come in via `git subtree pull --squash`, which
  creates a single "merge from upstream" commit — client history stays
  clean.
- Each client is still pinned to a specific parent version (whatever the
  last `subtree pull` brought in).
- Trade-off: the client repo contains a full copy of the parent theme
  files, so it's bigger than a pure-pointer submodule setup would be.
  Acceptable cost.

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
1. Edit parent theme locally (most convenient: work directly in a
   client repo's `wp-content/themes/classic-city-core/` — it's a real
   tracked directory — commit there, then cherry-pick or apply to the
   `ccc-wp-theme` parent repo; OR keep a dedicated clone of
   `ccc-wp-theme` outside Local and edit there).
2. Commit + push to `ccc-wp-theme` main.
3. In each client repo that should adopt the change:
   ```
   git subtree pull --prefix=wp-content/themes/classic-city-core \
     upstream-parent main --squash
   git push origin main
   git push wpe main
   ```
   (`upstream-parent` here is a named git remote pointing at
   `git@github.com:classiccity/ccc-wp-theme.git`; add it once per client
   repo with `git remote add upstream-parent …`.)

**Child theme change (client-specific):**
1. Edit in that client's `wp-content/themes/sg-{slug}/` folder.
2. Commit + push to origin (GitHub) and wpe (WP Engine). Live in ~30s.

**Content change:**
- Done in WP admin on the WPE staging environment.
- Promoted via WP Engine's "Copy Environment" (staging → production)
  when ready.
- Never in git.

## Local dev model

**Sandbox** (`the-style-guide-wp.local`): style guide demo + content
import tooling. Parent theme + all child theme scaffolds live here for
rapid iteration. Not meant for per-client production content.

**Per-client local** (e.g., `texbuilt.local`): mirrors that client's
WPE install 1:1. Used for code work that needs to see real client
content/uploads. Created via Local's "Pull from WP Engine" integration.
The site root (`app/public/`) IS the client's git repo, with the
parent theme merged in as a subtree at `wp-content/themes/classic-city-core/`.

This means two Local sites exist per active client:
- The sandbox (shared, always)
- The client's local mirror (for code work against real content)
