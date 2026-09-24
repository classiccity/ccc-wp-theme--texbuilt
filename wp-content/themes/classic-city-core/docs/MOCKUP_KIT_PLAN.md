# Block-Library Mockup Kit — Plan

A future tool / workflow for assembling client mockups from the
parent theme's block library **without** spinning up WordPress, a
GitHub repo, or WP Engine. Conceptually a "static export of the
/style-guide page that you can rearrange and re-skin in minutes."

**Status:** Deferred. Captured here so it's pick-up-able later — by
Chris, by Claude, or by whichever automation we hand it off to.

---

## What problem this solves

[PROSPECT_DEMO.md](./PROSPECT_DEMO.md) already documents how to
hand-build a one-off prospect demo (`~/Demos/{slug}/index.html`). The
first one took a session; the runbook gets it to ~30 minutes. This
plan is the *next* compression — turn 30 minutes into 5, by giving
ourselves a kit where every block is pre-rendered and the
palette/font/spacing are knobs you turn on the fly.

Same use cases as the PROSPECT_DEMO.md workflow:

- Warm prospect needs a visual before signing
- A/B pitching against Framer / Webflow / Squarespace
- Internal mockups for new ideas without polluting a real client repo
- Showing "yes, the parent theme can do this" with someone else's brand

Different scope: this is about **tooling**, not a per-prospect runbook.

---

## Why this is feasible (status of the architecture)

The current codebase is already ~80% of the way there:

- **`/style-guide` already renders every block with sample data.**
  See `patterns/style-guide.php`. Each block is emitted with both a
  descriptor card (kind + usage) and a populated sample. Demo images
  fall through `inc/demo-image-placeholders.php` to bundled gallery
  photos so the page always renders cleanly without uploads.
- **Block render templates emit clean, self-contained HTML.** Helper
  classes (`has-{slug}-background-color`) and CSS custom properties
  (`var(--wp--preset--color--primary)`) decouple markup from palette.
- **`theme.json` is the single source of truth** for palette,
  typography, spacing, custom tokens. Child themes override just
  what's different. There's already an admin UI for editing tokens
  (`inc/class-ccc-style-guide-admin.php`) and a WP-CLI namespace
  (`inc/class-ccc-style-guide-cli.php`).
- **`assets/blocks.css` is monolithic and palette-agnostic.** Every
  color/font/spacing reference is a `var(--wp--preset--*)` token.
  Swap the token file → repaint the whole library, no rebuild.

Implication: we don't have to *build* a design system to make a
mockup kit — we just have to **package** what already exists.

---

## Three tiers

### Tier 1 — Export-and-paste kit (≈1 day)

Extend the existing `wp style-guide` WP-CLI namespace with an
`export-kit` subcommand. It:

1. Hits the locally-rendered `/style-guide` page (or invokes the
   pattern PHP directly and captures output).
2. Slices the resulting HTML on each block boundary — emits one
   `.html` snippet per block (e.g. `kit/blocks/hero.html`,
   `kit/blocks/detail-cards.html`).
3. Copies `assets/blocks.css` into `kit/blocks.css`.
4. For each active child theme (or for each one the user names),
   generates `kit/tokens-{client}.css` by reading that theme's
   `theme.json` and emitting only the CSS custom properties WP would
   normally inject (`--wp--preset--color--*`,
   `--wp--preset--font-family--*`, `--wp--preset--font-size--*`,
   spacing, custom tokens like `radius.default`).
5. Writes a top-level `kit/index.html` that links `blocks.css` + one
   `tokens-*.css` and includes every block snippet inline so you can
   visually scan the whole library.

Resulting folder:

```
kit/
  blocks/
    hero.html
    hero-3-up.html
    detail-cards.html
    highlight-tiles.html
    ...
  blocks.css
  tokens-texbuilt.css
  tokens-lumberock.css
  tokens-aspglobal.css
  index.html        ← <link blocks.css> + <link tokens-{x}.css>
```

**Workflow this unlocks:** open `index.html`, swap the `<link>` to
change palette, copy a block snippet into a fresh page, paste another
below it, repeat. Mockup composed in a text editor. No npm, no
build step, no WP running.

**Trade-off:** the snippets are frozen — when a block's markup changes
upstream, rerun `wp style-guide export-kit` to refresh. Fine for a
part-time tool, dealbreaker for anything that needs to track live
theme changes.

### Tier 2 — Interactive picker (≈2–3 days on top of Tier 1)

Same exported kit, plus a tiny vanilla-JS UI living inside the
`kit/index.html` file:

- **Sidebar:** list every block as a clickable thumbnail (with the
  block.json `title` + `description` already available in the
  descriptor markup).
- **Workspace:** an empty `<div id="mockup">` where clicking a block
  in the sidebar appends its snippet. Sortable.js for drag-to-reorder.
- **Palette picker:** dropdown of available `tokens-*.css` files; on
  change, swap the `<link>` href. (Or, fancier: read all token files
  at boot, expose every variable as an input, write to
  `document.documentElement.style.setProperty(...)` on change for
  live recoloring.)
- **Font picker:** same idea but for `--wp--preset--font-family--*`.
- **Export:** button that serializes the current `#mockup` HTML + the
  active token CSS into a downloadable `prospect-mockup.zip`. Lands
  exactly where PROSPECT_DEMO.md says the output should go
  (`~/Demos/{slug}/`).

**Workflow this unlocks:** 5-minute mockup. Click 6 blocks in order,
tweak palette, hit export. The hand-built PROSPECT_DEMO.md workflow
becomes the rare case (custom-shaped prospects), not the default.

**Trade-off:** still frozen snippets; still need to rerun the export
when the parent theme ships new blocks. But the UI itself is a single
HTML file with zero build step — easy to ship, easy to fix.

### Tier 3 — Hosted mockup tool (≈1–2 weeks)

A separate small app (Next.js or Astro both work; Astro is lighter and
fits the "static-ish" feel better). Same kit format as Tier 1/2 as
its input. Adds:

- **Saved mockups per prospect** — local-first, sync to GitHub or
  Supabase optional.
- **Shareable URLs** — `mockups.classiccity.com/trialport` → preview
  link sent to the prospect.
- **PNG / PDF export** for proposals (puppeteer or playwright in a
  Vercel/Cloudflare function).
- **Live mode** — instead of consuming a frozen export, the app
  fetches `/style-guide` from a running CCC install at view-time, so
  parent-theme changes appear instantly without a rerun.
- **Per-block field editing** — instead of pasting the sample copy,
  the app surfaces each block's ACF schema and lets you edit the
  fields. Probably needs a small JSON sidecar per block in the kit
  describing its editable surface.

**Workflow this unlocks:** prospect-facing tool. "Here's your
mockup, branded with your palette, with your copy — what would you
like changed?" Possibly chargeable as a productized service.

**Trade-off:** real product, real maintenance. Don't reach for this
until Tier 1/2 prove the workflow.

---

## Recommended sequence

**Start with Tier 1.** It's the dependency for everything else and
the artifact (a folder of HTML + CSS) is already useful even if
nothing else gets built. It's also bounded enough that a single
session can finish it.

If after a month of using the static kit it's clear the
copy-paste step is the bottleneck, build Tier 2. The sidebar +
drag-to-reorder is the actual leverage moment — most of the value
of Tier 3 already exists at that point, minus the prospect-facing
surface.

Only build Tier 3 if mockups become a real revenue line, not just an
internal accelerator. Otherwise the maintenance overhead of a hosted
app isn't justified.

---

## Decisions to make before starting Tier 1

1. **One block per snippet, or one giant page?**
   - Per-snippet (`kit/blocks/hero.html` × N) → composable, easier to
     diff, plays nice with future Tier 2/3 ingestion.
   - One page (`kit/style-guide.html`) → easier to scan visually,
     identical to current `/style-guide`.
   - Probably ship BOTH: per-snippet under `kit/blocks/`, plus an
     `index.html` that includes all of them so the page-style still
     works.

2. **How dynamic should the palette/font swap be?**
   - **Drop-in CSS files** (one per client) — simple, requires
     pre-generating a token CSS for every palette you want to support.
   - **Live JS rewriting** of `:root` variables — one tool that
     handles any tokens you throw at it, slightly more code.
   - Tier 1 → drop-in. Tier 2 → live JS makes sense.

3. **Which themes seed the initial `tokens-*.css` set?**
   - The current child themes in the sandbox (`sg-texbuilt`,
     `sg-lumberock`, `sg-aspglobal`) are the obvious starter set.
   - Could also include a "neutral" preset palette derived straight
     from the parent's defaults for "no client picked yet" mockups.

4. **Where does `kit/` live?**
   - Probably `~/Demos/_kit/` so it sits next to the per-prospect
     mockup folders PROSPECT_DEMO.md already uses.
   - Alternative: in a separate git repo (`ccc-mockup-kit`) so
     non-engineers can clone it without grabbing the whole theme.
   - Default to `~/Demos/_kit/` for Tier 1; promote to a repo if/when
     it becomes a team tool.

---

## Implementation pointers

When the time comes, the relevant files to start from:

| Area | File |
|---|---|
| WP-CLI namespace to extend | `inc/class-ccc-style-guide-cli.php` |
| Style-guide pattern (source of all the block samples) | `patterns/style-guide.php` |
| Style-guide template wrapper | `templates/page-style-guide.html` |
| Demo image fallback used by every block | `inc/demo-image-placeholders.php` |
| Block CSS to bundle into `kit/blocks.css` | `assets/blocks.css` |
| Palette / token source per child theme | `wp-content/themes/sg-{slug}/theme.json` |
| Existing prospect-demo runbook to plug into | `docs/PROSPECT_DEMO.md` |
| Block manifest format (for the Tier 3 schema sidecar idea) | `blocks/{slug}/block.json` + `blocks/{slug}/fields.php` |

The token-extraction step is the only non-trivial new code in Tier 1:
WordPress normally generates the `--wp--preset--*` properties from
theme.json at runtime. For a standalone kit we need to reproduce that
generation in PHP (or pre-render the document and scrape the
`<style>` it emits). A reasonable starting point is to load each
child theme's theme.json, walk the `settings.color.palette`,
`settings.typography.fontFamilies`, `settings.typography.fontSizes`,
`settings.spacing.spacingSizes`, and `settings.custom` trees, and
emit a single `:root { ... }` block. Same logic the WP source does in
`block-supports/global-styles-css.php` — but only for the keys our
blocks actually reference.

---

## Out of scope (intentionally)

- **Replacing PROSPECT_DEMO.md.** That runbook is for hand-built
  custom-shaped demos. The kit handles the standardized case; the
  runbook handles the bespoke 10%.
- **A block editor inside the kit.** Tier 3 hints at this, but no
  tier should rebuild the WP block editor — the kit is for assembly
  and re-skinning, not authoring net-new blocks.
- **Live two-way sync with a real client site.** A mockup is a
  marketing/sales artifact. Once a client signs, the real workflow
  is `sg-{slug}` child theme → WPE → live. The kit doesn't try to
  bridge that.
