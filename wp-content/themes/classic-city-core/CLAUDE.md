# Claude Notes — Classic City Core (parent theme)

Orientation for any session in this repo. This file is deliberately
thin: it routes you to the right doc and states the non-negotiables.
Deep knowledge lives in `docs/` — trust those files over memory.

Last verified: 2026-07-09. Run `npm run docs:check` after doc or
comment changes; run `npm run docs:blocks` after any block change.

---

## ⚠️ Before writing or importing ANY page content — or building a block

**Read [`docs/CONTENT_BUILDING_RULES.md`](docs/CONTENT_BUILDING_RULES.md) first,
every time.** It is the non-negotiable ruleset for content + blocks on every
Classic City site: core-blocks-first (no inline styles / no custom HTML on
standard tags), custom structure → ACF block coded in a Foundation vs
Theme-specific split, CSS variables for everything, and the button-color
hierarchy. This applies in every client repo — the parent is subtree'd into all
of them.

---

## What this is

- The **shared parent theme** for every Classic City client website:
  a WordPress block theme (FSE) with **41 custom ACF blocks** (registry:
  `docs/BLOCKS.md`), FSE templates/parts, and the client-onboarding
  automation under `scripts/onboard-client/`.
- Lives at `classiccity/ccc-wp-theme` on GitHub. Client sites consume it
  via **git subtree** (NOT submodule — WPE Git Push deploys submodules
  as empty dirs; see `docs/WPE_PLATFORM.md`).
- **Never activated directly on production** — always through a child
  theme (`sg-{slug}`), which owns the client's palette/fonts via
  theme.json overrides.

## Route by task — read the matching doc FIRST

| Task | Read |
|---|---|
| **Writing/importing ANY page content, or building a block** | `docs/CONTENT_BUILDING_RULES.md` — **first, every time** (see the callout above) — PLUS the client's local half: `sg-{slug}/AUTHORING_PATTERNS.md` (create from the Lumberock reference if missing). Resume + record via `sg-{slug}/BUILD_LOG.md` (read top ~10 entries at start; append every decision-with-a-why). Copy/media/decisions live OUTSIDE the repo — `sg-{slug}/CONTENT-SOURCES.md` maps to the Chief of Stuff client folder + Drive (create at content-work start; spec in CONTENT_BUILDING_RULES) |
| **New client build kickoff** | `docs/NEW_SITE_CHECKLIST.md` — your FIRST response is its checklist + putting the GitHub pubkey on the clipboard. Deep contract: `docs/CHIEF_OF_STUFF_HANDOFF.md` |
| Child theme, palette, tokens, theme.json | `docs/THEME_TOKENS.md` (the **canvas/panel/ink** model) |
| **Picking a typeface for a client** | Chris owns a licensed 126-family library in its OWN repo — read `$HOME/Local Sites/ccc-font-library/README.md` **before** reaching for Google Fonts (`fonts.json` to parse, `_specimens/` to eyeball). Moved out of the Chief of Stuff vault 2026-07-30; that path is stale. Ship the one family's `{slug}/web/*.woff2` into the child theme; never copy the library. The index flags caps-only faces, missing accents, fake-monospace, and the per-project Envato registration each font needs before launch. To try faces live on a site, use the **Font Lab** (`inc/class-ccc-font-lab.php`) |
| Add or edit a block | `docs/BLOCK_AUTHORING.md` — every block (parent or child) ships `blocks/{slug}/usage.md`; `npm run docs:blocks` fails without it |
| Field keys for authoring block content | `docs/BLOCKS.md` (generated — never guess keys) |
| **Choosing which block a piece of content becomes** | `docs/BLOCKS.md` usage layer (per-block Purpose/Use-when) + `docs/CORE_BLOCKS_USAGE.md` (core-first decision ladder) |
| Author/edit pages on a client install | `docs/CLIENT_HOMEPAGE.md` |
| Migrating an existing site's content | `docs/MIGRATION_PIPELINE.md` — the 8-stage pipeline; stages 2–6 run CoS-side, entry point `/migrate-site` |
| Bulk content import / migration | `docs/BULK_IMPORT.md` |
| Hand the client a plain-language "how to write your content" guide | `docs/CLIENT_CONTENT_STRUCTURES.md` — 8 copy-paste section templates (strip the team appendix first) |
| Anything touching WP Engine | `docs/WPE_PLATFORM.md` (canonical platform behavior) |
| Which client sites exist / safe references | `docs/SITES.md` |
| Onboarding phase detail | `docs/CLIENT_ONBOARDING.md` |
| Marker.io / Asana QA pass on a client site | `docs/ASANA_QA_PROCESS.md` — triggered by "let's do QA on this Asana" (user-level `asana-qa` skill points here) |
| Client content-migration progress deliverable | `docs/PAGE_BUILD_TRACKER.md` |
| **Client-facing build/QA status page** ("send the client a link") | `docs/client-tracker/README.md` — a TEMPLATE (generator + example JSON). Children **copy** it into `sg-{slug}/docs/qa/` and fill in their own data; the template itself is never edited by a child. One JSON renders two ways: a client build (default) and `--internal` (adds ownership + effort + internal notes). Amber always and only means "waiting on the client" |
| **Changing the client-tracker generator itself** (not just using it) | `docs/client-tracker/DEVELOPING.md` — architecture, where the two render modes diverge, the icon sprite, the round-scoping JS, the decision log, and the invariants that must not regress (leak audit, zero external requests, Python 3.9, docs-check staying at 7). Read it before editing `build-tracker.py` |
| Static prospect demo | `docs/PROSPECT_DEMO.md` |
| Picking up queued improvement work ("what's next?") | `docs/ROADMAP.md` — ranked backlog; work top-down, delete sections as they land |
| Wrapping up a work set (deploy pushed / WPE content changed) | § "Build updates → Chief of Stuff" below — log it, don't wait to be asked |
| Why the architecture looks like this | `docs/ARCHITECTURE.md` |

## Non-negotiables

1. **Color model is canvas/panel/ink** (+ tinted `gray-10…100` ramp).
   The legacy `light`/`dark` slugs are **removed from Core** — never
   build a new child on them. Spec: `docs/THEME_TOKENS.md`.
2. **Never copy an old client site as a build baseline.** Read-only
   references: `sg-sherman-phalen` (light) and `sg-trialport` (dark),
   each in its OWN client repo. The `sg-*` folders co-located in the
   sandbox are stale test copies. Georgia SEB is never a sample.
   Registry: `docs/SITES.md`.
3. **The WPE database is canonical for page content.** Edit pages
   read-modify-write in the same session; never publish a
   locally-authored file over content you haven't just fetched.
   Snapshot to `sg-{slug}/page-snapshots/` afterward.
4. **Never rename or reuse an ACF field key** once client content
   exists — saved blocks reference keys directly and go blank.
5. **Deprecated tooling** (emits the retired light/dark model):
   `wp style-guide new-client`, `wp style-guide import`,
   `inc/class-ccc-client-importer.php`, and the onboarding
   `phases/08-child-theme.ts` scaffold. Child themes are hand-authored
   per `docs/THEME_TOKENS.md`.

## Repo contexts

| Context | Path | What to do |
|---|---|---|
| **Style-guide sandbox** (usual parent-theme dev) | `~/Local Sites/the-style-guide-wp/app/public/wp-content/themes/classic-city-core/` | **This directory IS a `ccc-wp-theme` clone** — commit and push to origin directly. Live preview: `http://the-style-guide-wp.local/style-guide`. |
| Standalone clone | wherever cloned | Edit, commit, push to origin. |
| Subtree inside a client repo | `~/Local Sites/{client}/app/public/wp-content/themes/classic-city-core/` | Commits go to the **client repo**. Push upstream: `git subtree push --prefix=wp-content/themes/classic-city-core upstream-parent main` from the site root. |

**Parent → clients propagation:** push here, then per adopting client:
`git subtree pull --prefix=wp-content/themes/classic-city-core
upstream-parent main --squash` && push origin + wpe. Nothing reaches a
client until that repo pulls — plan bridges first for legacy-content
sites (see `docs/THEME_TOKENS.md` § legacy light/dark).

## Block system (summary — details in docs/BLOCK_AUTHORING.md)

- `inc/blocks.php` auto-registers every `blocks/{slug}/` containing a
  `block.json`; ACF groups load from each block's `fields.php`. Never
  register manually.
- One block per folder: `block.json` + `fields.php` + `render.php`
  (+ optional `view.js`).
- Every hero variant emits the **`.sg-hero` marker class** (marker
  only — no CSS on it; pages are expected to start with a hero).
- Hybrid blocks (`<InnerBlocks />`) need the canonical flex +
  `display: contents` layout pattern or spacing breaks — the
  `acf-innerblocks-container` trap, fully explained in
  `docs/BLOCK_AUTHORING.md`.
- Shared render helpers live in `partials/` (`block-head`,
  `image-card`, `feature-detail-section`); infrastructure lives in
  `inc/`.

## Key inc/ files

- `inc/blocks.php` — block + ACF auto-registration
- `inc/enqueue.php` — enqueues `assets/blocks.css`; emits palette
  pair-helper CSS (`ccc_build_color_pair_helpers_css()`,
  `ccc_palette_slug_choices()`); FontAwesome
- `inc/strip-wp-defaults.php` — strips WP default presets so theme.json
  is authoritative
- `inc/textures.php` — `has-bg-texture-{slug}` CSS from
  `settings.custom.textures`
- `inc/block-styles.php` — core-block styles (eyebrow, quote, section…)
- `inc/acf-validations.php` — URL fields accept `#fragment`, `/path`,
  `mailto:`, `tel:`
- `inc/demo-image-placeholders.php` — `/style-guide` renders without
  uploads (`ccc_resolve_image_or_demo()`)
- `inc/cpt-testimonial.php` + `inc/acf-testimonial.php` — Testimonial CPT
- `inc/class-ccc-font-lab.php` — dev-only font-swap overlay (OFF unless a
  child calls `add_theme_support('ccc-font-lab')`, AND env is non-production,
  AND user can `edit_theme_options`). Library: `classiccity/ccc-font-library`
- `inc/class-ccc-demo-page.php` — ensures the `/style-guide` page exists
- `inc/class-ccc-style-guide-admin.php` — Appearance → Style Guide Tokens
- `inc/class-ccc-tutorial-videos.php` — Tools → Tutorial Videos
- `inc/class-ccc-style-guide-cli.php` / `inc/class-ccc-client-importer.php`
  — ⚠️ deprecated scaffolding (see Non-negotiables #5);
  `port-textures` subcommand still current

## CSS conventions

- All `.sg-block-*` rules live in `assets/blocks.css` — one
  authoritative, **palette-agnostic** file (tokens only, works for any
  child palette, light or dark).
- Palette-driven rules (bg+text pairing, button hovers) are emitted at
  runtime from `inc/enqueue.php`; hover overrides need `!important`
  because WP core emits its bg utilities with `!important`.
- Section rhythm is automatic (`--ccc-section-gap`); panels get radius +
  shadow from the `is-style-section` block style — never inline
  radius/shadow/margins in page markup.

## theme.json conventions

Parent declares the structural baseline; children override palette,
fonts, and select `settings.custom.*` tokens (objects deep-merge;
**arrays like `color.palette` and `typography.fontFamilies` REPLACE** —
full merge rules in `docs/THEME_TOKENS.md`).

`settings.custom` groups actually in the parent: `color.{slug}-opposite`
(gray-ramp values), `gray.10…100`, `fs.h-1…h-6` (+ `-min`),
`radius.default`, `border.default-width` / `border.color`,
`heading.letter-spacing` / `heading.base-font-size`,
`eyebrow.letter-spacing`, `body.base-font-size`, `btn.padding-x/y`,
`layout.narrow-size`, `icons.style`, `bg.blur`, `overlap.desktop/mobile`,
`elevation.card/raised/overlay`, `textures.{slug}`.
(There is **no** `body.bg` token — page background is
`styles.color.background` → `canvas`.)

## Build updates → Chief of Stuff (wrap-up rule, every client repo)

When a work set wraps in a client repo — a deploy went to WPE, WPE content
was changed over SSH/REST (DB-only changes never hit git!), or a
milestone-worthy chunk landed — **append a build-update block to
`$HOME/Chief of Stuff/Brain/Chief of Stuff/build-updates.md` without being
asked**, then commit + push just that file. Block format and placement
(directly under the header, above the `↑ unsynced ↑` divider) are documented
in that file's own header; it is a write-only inbox — never read or interlink
the rest of the vault. One block per wrapped work set, not per push.

This is enforced, not just remembered: the parent ships a Claude Code hook
(`scripts/hooks/build-update-reminder.py`, wired per repo via
`scripts/hooks/install-build-update-hook.py` → committed
`.claude/settings.json`). It nudges when a deploy/WPE-write happens and blocks
the first wrap-up that would end an armed session unlogged. If a repo isn't
nagging, its settings file is probably missing — run the installer.

## When NOT to edit the parent theme

Ask: *should every CCC client — including launched ones — get this?*
If no, it belongs in the child theme (children can override helper
colors, block CSS, template parts, even whole blocks). Style changes
almost always belong in the child; structural changes and shared-logic
fixes can belong here. When unsure, propose both options in chat.

## What NOT to do

- ❌ Activate this theme directly on production.
- ❌ Child-specific CSS in `assets/blocks.css`.
- ❌ Rename ACF field keys (see Non-negotiables).
- ❌ Commit child themes or plugin zips to this repo.
- ❌ Manual block registration.
- ❌ Build a new child on the retired light/dark slugs or the
  deprecated scaffolding CLI.
