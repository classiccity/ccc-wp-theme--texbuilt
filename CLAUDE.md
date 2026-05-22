# Claude Notes — Classic City Core (parent theme)

Quick-start context for working on this parent theme in a fresh
session. **Read this first**, then dive into `docs/`.

---

## What this is

- The **shared parent theme** for every Classic City client website.
- A WordPress block theme (FSE) that registers ~13 custom ACF blocks
  plus full-site editing templates.
- Lives at `classiccity/ccc-wp-theme` on GitHub.
- Used by client sites via **git subtree** (NOT submodule — submodules
  don't deploy correctly via WP Engine's Git Push). Each client repo
  has this whole theme merged into `wp-content/themes/classic-city-core/`
  as real tracked files.
- **Never activated directly** on production — always activated through
  a child theme like `sg-texbuilt`, `sg-lumberock`, etc.

---

## Read these first

- **`docs/ARCHITECTURE.md`** — why the multi-repo + parent/child +
  subtree topology exists. Read for the "why."
- **`docs/CLIENT_ONBOARDING.md`** — full step-by-step runbook for
  spinning up a new client site, including all the WP Engine quirks
  we've hit. Read for the "how."
- **`docs/README.md`** — index of the above.

These docs cover the WPE WAF blocking REST writes, cache purge
behavior, SSH key registration nuances, ACF block markup format, and
~10 other gotchas worth knowing before doing anything programmatic.

---

## Repo location and dev workflow

This theme is a separate git repo. You'll likely encounter it in three
contexts:

| Context | Path | What to do |
|---|---|---|
| **Standalone clone** for parent-theme dev | wherever you cloned it | Edit, commit, push to `origin` (`ccc-wp-theme` GitHub repo) |
| **Inside the style-guide sandbox** | `~/Local Sites/the-style-guide-wp/app/public/wp-content/themes/classic-city-core/` | This is where parent-theme dev usually happens — sandbox has every child theme co-located for visual testing. The `wp-content/` folder there is its own monorepo, not connected to `ccc-wp-theme` directly. Edits here need to be COPIED to the parent-theme clone before push. |
| **As a subtree inside a client repo** | `~/Local Sites/{client}/app/public/wp-content/themes/classic-city-core/` | Edits here commit to the **client repo**, not the parent. To push to parent: `git subtree push --prefix=wp-content/themes/classic-city-core upstream-parent main` from the client repo's site root. |

**Recommended:** for parent-theme work, edit in the standalone clone or
in the style-guide sandbox (the sandbox gives you a live preview at
`http://the-style-guide-wp.local/style-guide`).

---

## Block system architecture

### Auto-registration

`inc/blocks.php` scans `blocks/*/` and auto-registers any directory
containing a `block.json`. ACF field groups (one per block in
`fields.php`) load via `acf/include_fields`. **No manual registration
in functions.php** — drop a folder in, it works.

### One-block-per-folder convention

Each block lives at `blocks/{slug}/`:

- `block.json` — block manifest, registers the block name
  (`classic-city-core/{slug}`) and points to render.php
- `fields.php` — ACF field group definition for the block
- `render.php` — server-side template that reads ACF fields and
  outputs HTML

To add a new block, copy an existing one (e.g., `cta-thin/`) as a
template and edit. The auto-loader picks it up on the next request.

### ACF block data format

ACF blocks store data in Gutenberg block-comment attributes. Every
field appears **twice** in the `data` object — once as `<name>: value`,
once as `_<name>: "field_<acf_key>"`. The `_<name>` entry tells ACF
which field key to save the value to.

Field-key registry for every block lives in
`docs/CLIENT_ONBOARDING.md` (Phase 12 area) and a fuller version in
the user's personal notes at
`~/Chief of Stuff/Brain/Chief of Stuff/Classic City/Clients/TexBuilt/proposals/05-content-import-learnings.md`.

For repeaters: `<repeater>` is the row count (integer), each row's
fields are flat keys like `<repeater>_<idx>_<subfield>`.

---

## Key inc/ files

- **`inc/blocks.php`** — block + ACF field auto-registration.
- **`inc/enqueue.php`** — enqueues `assets/blocks.css` site-wide. Also
  generates the **palette pair-helper CSS** dynamically from the
  active theme.json palette. Also enqueues FontAwesome.
- **`inc/setup.php`** — theme supports, editor styles.
- **`inc/strip-wp-defaults.php`** — strips WP's default palette,
  gradients, shadows, font-sizes (so the child themes' theme.json is
  authoritative).
- **`inc/acf-validations.php`** — ACF URL field accepts bare `#`
  fragments (so `#contact` placeholder URLs save without errors).
- **`inc/textures.php`** — dynamic `has-bg-texture-{slug}` CSS
  generation from `settings.custom.textures` in theme.json.
- **`inc/cpt-testimonial.php`** + **`inc/acf-testimonial.php`** — the
  Testimonial CPT and its ACF fields.
- **`inc/block-styles.php`** — `register_block_style()` for eyebrow,
  quote, bg-texture, etc.
- **`inc/class-ccc-style-guide-admin.php`** — Appearance → Style Guide
  Tokens admin UI for editing theme.json palette + tokens through wp-admin.
- **`inc/class-ccc-client-importer.php`** — WP-CLI commands for
  scaffolding new child themes from JSON config.
- **`inc/class-ccc-style-guide-cli.php`** — `wp style-guide …` WP-CLI
  commands.

---

## CSS conventions

- **All `.sg-block-*` rules live in `assets/blocks.css`** — one
  authoritative file. The Next.js style-guide preview at
  `the-style-guide` (separate repo) has a mirror that gets overwritten
  when you sync; WP is canonical.
- **Palette-driven rules** (button hovers, inverse-icon chips) are
  emitted dynamically from `inc/enqueue.php` so they track each
  child theme's palette without manual editing.
- **!important** is required on palette-driven hover overrides
  because WP core adds `!important` to its auto-emitted
  `.has-{slug}-background-color` utility classes — see the comment
  block in `ccc_build_color_pair_helpers_css()` in `inc/enqueue.php`.

---

## theme.json conventions

The parent theme.json declares the structural baseline (typography
scale, spacing scale, layout sizes, etc.). Each child theme overrides
just the bits that differ for that client (palette, fonts, custom
tokens like `radius.default`, `border.default-width`). Children
inherit everything else.

Tokens custom to this theme system (under `settings.custom`):

- `color.{slug}-opposite` — paired text color for combined-helper
  bg+text utilities (e.g., `cta` background pairs with `cta-opposite`
  text)
- `radius.default` — site-wide rounded-corner radius
- `border.default-width` — site-wide border-width token
- `fs.h-1`..`h-6` — heading font-size scale (separate from
  `typography.fontSizes`)
- `body.bg`, `body.base-font-size`
- `btn.padding-x`, `btn.padding-y`
- `layout.narrow-size` — used by hero-full-image card-width variant
- `eyebrow.letter-spacing`
- `heading.letter-spacing`, `heading.base-font-size`
- `icons.style` — FontAwesome family (`solid`, `regular`, `light`,
  `sharp-light`)

---

## How parent changes propagate to clients

Two paths:

**From a standalone parent-repo clone:**
```bash
# In the parent repo clone
git add … && git commit && git push origin main
# Then in each client repo that should adopt:
cd ~/Local\ Sites/{client}/app/public
git remote add upstream-parent git@github.com:classiccity/ccc-wp-theme.git  # one-time
git subtree pull --prefix=wp-content/themes/classic-city-core upstream-parent main --squash
git push origin main && git push wpe main
```

**From a client repo's subtree checkout (less common):**
```bash
# After editing files in wp-content/themes/classic-city-core/ inside a client repo
cd ~/Local\ Sites/{client}/app/public
git add wp-content/themes/classic-city-core
git commit -m "Parent theme tweak"
git push origin main && git push wpe main
# Then split the change up to the parent repo
git subtree push --prefix=wp-content/themes/classic-city-core upstream-parent main
```

---

## Local dev environment

The "sandbox" is at **`~/Local Sites/the-style-guide-wp/`**. It has:

- This parent theme co-located with EVERY child theme (sg-texbuilt,
  sg-lumberock, etc.) for cross-client visual testing
- A `/style-guide` page that renders every block type — useful for
  visually validating block changes
- The original `wp-content/` is a single git repo (NOT subtree-based),
  predates the multi-repo split. Edits here need to be deliberately
  copied/synced to the parent repo if they should ship.

For TexBuilt-specific content work, use the `~/Local Sites/TexBuilt/`
mirror instead — that one mirrors the WPE production install 1:1.

---

## What NOT to do

- ❌ **Don't activate this theme directly on production** — always
  through a child theme. Direct activation breaks the "client owns
  their look-and-feel via theme.json overrides" model.
- ❌ **Don't add child-theme-specific CSS to `assets/blocks.css`** —
  put it in the child theme's `style.css`. Parent CSS should work
  for any palette/font combination.
- ❌ **Don't edit ACF field keys** without coordinating — block content
  on existing client sites references field keys directly; renaming
  breaks already-saved content.
- ❌ **Don't commit child theme files to this repo** — those live
  in client repos.
- ❌ **Don't manually register blocks** — the auto-loader finds them.

---

## Pointers

- **Onboarding runbook (start here for any new-client work):**
  `docs/CLIENT_ONBOARDING.md`
- **Architectural rationale:** `docs/ARCHITECTURE.md`
- **Per-client CLAUDE.md** lives in each client's `sg-{slug}/`
  directory (e.g., `wp-content/themes/sg-texbuilt/CLAUDE.md`).
  Look there for client-specific WPE install info, current state,
  and content-import patterns.
