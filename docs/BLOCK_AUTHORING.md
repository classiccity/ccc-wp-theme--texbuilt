# Adding / Editing a Block — Authoring Guide

How to add block #36 so it behaves like the first 35. This is the
canonical home for the block-system conventions that used to live only
in `CLAUDE.md` and in tribal knowledge.

Related: [`CONTENT_BUILDING_RULES.md`](./CONTENT_BUILDING_RULES.md)
(**read first** — the non-negotiable content + block ruleset, incl. the
core-blocks-first rule and the Foundation vs Theme-specific split) ·
[`BLOCKS.md`](./BLOCKS.md) (generated per-block field-key registry) ·
[`THEME_TOKENS.md`](./THEME_TOKENS.md) (color/token system) ·
[`CLIENT_HOMEPAGE.md`](./CLIENT_HOMEPAGE.md) (authoring pages *with*
blocks).

Last verified: 2026-07-09.

---

## First: do you actually need a new block?

Every visual treatment must be one of three defensible patterns
(same rule as `PROSPECT_DEMO.md`'s "defending a new section"):

1. **A block style on a core block** (`register_block_style()` in
   `inc/block-styles.php`) — for visual variants of heading /
   paragraph / group / buttons (e.g. `is-style-eyebrow`,
   `is-style-section`).
2. **A hybrid ACF block** (fields + `<InnerBlocks />`) — a new section
   type wrapping editor-flexible content.
3. **A pure field-driven ACF block** — fully structured content
   (repeater rows), no editor freedom.

If it fits none of these, re-scope. If it's a style-only change for one
client, it belongs in the **child theme**, not here (see "When NOT to
edit the parent theme" in `CLAUDE.md`).

---

## Anatomy + auto-registration

Each block lives at `blocks/{slug}/`:

| File | Role |
|---|---|
| `block.json` | Manifest. Registers `classic-city-core/{slug}`, points at `render.php`. Category `theme`. |
| `fields.php` | One `acf_add_local_field_group()` call, keys namespaced `field_{slug}_…`, located to this block. |
| `render.php` | Server-side template reading ACF fields. |
| `view.js` | Optional front-end JS (only 3 blocks have one). |

`inc/blocks.php` scans `blocks/*/` and auto-registers anything with a
`block.json`; `fields.php` loads via `acf/include_fields`. **Never
register a block manually in functions.php.** Fastest start: copy a
close-match block folder and rename everything.

---

## The new-block checklist

1. **Copy a template block** — a hybrid (`cta-large`, `split-50-50`) or
   a field-driven one (`cta-thin`, `stats`), whichever matches.
2. **block.json** — set `name` (`classic-city-core/{slug}`), `title`,
   `description`.
3. **fields.php** — new group key `group_block_{slug}`; every field key
   `field_{slug}_{name}`. For the standard eyebrow + h2 intro, use the
   shared partial: `ccc_block_head_fields( '{slug}' )` from
   `partials/block-head.php` instead of redeclaring.
4. **render.php:**
   - Outermost wrapper gets `sg-block-{slug}` plus any WP-supplied
     classes/anchor.
   - **Hero variants MUST also emit the `sg-hero` marker class** on the
     outermost wrapper. It is a marker only — never write CSS against
     `.sg-hero` itself; each variant owns its own padding/full-bleed
     treatment via its variant class.
   - Image fields: resolve through `ccc_resolve_image_or_demo()`
     (`inc/demo-image-placeholders.php`) so the /style-guide page renders
     without uploads.
   - Shared markup used by 2+ blocks → extract to `partials/` (see
     below), don't copy-paste.
   - If the block wraps `<InnerBlocks />`, apply the canonical layout
     pattern (next section) or spacing will be broken.
5. **CSS** — all `.sg-block-{slug}` rules go in `assets/blocks.css`
   (one authoritative file), **palette-agnostic**: every color, font,
   radius, shadow, spacing value must be a `var(--wp--preset--*)` /
   `var(--wp--custom--*)` token so any child palette works. If the block
   paints a palette background, it must also set the paired
   `{slug}-opposite` text color (that's what keeps text readable on dark
   sections). Palette-driven hover rules are emitted dynamically from
   `inc/enqueue.php` (`ccc_build_color_pair_helpers_css()`) — and need
   `!important` because WP core emits its
   `.has-{slug}-background-color` utilities with `!important`.
6. **Style guide sample** — add a populated sample of the block to
   `patterns/style-guide.php` so it appears on the `/style-guide` page.
   That page is the visual regression surface for every block; a block
   that isn't on it doesn't get looked at.
7. **Write `blocks/{slug}/usage.md`** — the block's semantic manifest
   entry: Purpose (one sentence) · Content shape (item counts, copy
   lengths, image needs) · Use when / Avoid when (heuristics phrased
   against source content, naming what to use instead) · Pairs with ·
   Core alternative. Copy an existing block's `usage.md` as the
   template (if you copied a template block in step 1, REWRITE the
   copied usage.md — a stale one is worse than none). This is what
   content-import sessions use to pick blocks; a block without it is
   invisible to them. **This applies to child-theme blocks too** —
   the block folder convention travels wherever the block is built.
8. **Regenerate the registry** — `npm run docs:blocks` to refresh
   [`BLOCKS.md`](./BLOCKS.md) with the new block's field keys and
   inlined usage entry. **The generator FAILS if any block is missing
   its `usage.md`** — that's the enforcement, not an optional nicety.
9. **Verify** on the sandbox: `http://the-style-guide-wp.local/style-guide`
   — check desktop + mobile widths, and a dark-palette child if the
   block paints backgrounds.

---

## The `acf-innerblocks-container` trap (hybrid blocks)

ACF renders `<InnerBlocks />` on the front end as
`<div class="acf-innerblocks-container">…</div>`. That div carries **no**
`is-layout-flow`/`is-layout-constrained`, so two things break when
authors put the typical eyebrow + heading + paragraph + buttons inside:

1. **No sibling spacing** — WP's flow-layout
   `> * + * { margin-block-start: block-gap }` never fires; authored
   siblings sit flush.
2. **Eyebrow/heading overlap** — the parent's
   `.is-style-eyebrow + :is(h1..h6)` collapse rule has two variants:
   - With a layout-flow ancestor: `margin-block-start: spacing--10`
     (10px). Clean.
   - Without (the default): `margin-block-start:
     calc(spacing--10 - block-gap)` ≈ **-20px**, assuming a flex parent
     with `gap: block-gap`. Inside `acf-innerblocks-container` that
     assumption fails and the heading rides up over the eyebrow.

**Canonical fix** (the pattern `hero`, `hero-full-image`,
`hero-gradient`, `image-hero-50-50`, `split-50-50`, `image-overlay`,
`center-content`, `cta-large` already use):

1. The wrapper around `<InnerBlocks />` in render.php gets
   `display: flex; flex-direction: column;
   gap: var(--wp--style--block-gap);`.
2. Add that wrapper's selector to the `display: contents` list near the
   top of `assets/blocks.css` (search for the comment "ACF Pro wraps
   frontend InnerBlocks"). The `acf-innerblocks-container` then collapses
   out of the layout tree and the authored blocks become real flex
   children of your wrapper.

Net effect: 30px flex gap between siblings, and the default-variant
eyebrow rule's -20px collapse yields the intended 10px eyebrow→heading
spacing.

**For shared partials** (markup not inside an `<InnerBlocks />`): add
`is-layout-flow` to the element that directly parents the
eyebrow + heading instead — that triggers the clean variant of the
collapse rule. Used by `partials/block-head.php` (`.sg-block-head`) and
`partials/image-card.php` (`.sg-image-card__heading-group`).

---

## Shared partials (`partials/`)

If you're copy-pasting markup between two blocks' render.php, extract a
partial. Existing ones (loaded from `functions.php`):

- `partials/block-head.php` — canonical eyebrow + h2 intro
  (`ccc_block_head_fields()` + `ccc_render_block_head()`).
- `partials/image-card.php` — image-on-top card
  (`ccc_render_image_card()`).
- `partials/feature-detail-section.php` — 2-col feature panel
  (`ccc_render_feature_detail_section()`), shared by
  `product-feature-toggles` and `feature-detail`.

Convention: multi-block render helpers → `partials/`; pure
infrastructure (loaders, enqueue, CPTs, admin, CLI) → `inc/`.

---

## Field-key discipline

- Keys are the contract with saved content: block data in the DB stores
  `"_name":"field_{key}"` pairs. **Never rename or reuse an ACF field
  key once any client site has content using the block** — renames
  silently blank already-saved blocks. Additive changes only; removing
  a field means leaving its key retired forever.
- The authoritative key list for every block is generated into
  [`BLOCKS.md`](./BLOCKS.md); the source of truth is each block's
  `fields.php`. Never guess a key — a wrong `_name` key renders the
  block **blank** with no error.
- Repeaters serialize as: parent key = row count (int), rows as flat
  `{repeater}_{i}_{subfield}` keys. See the format primer at the top of
  `BLOCKS.md`.

---

## What NOT to do

- ❌ CSS against `.sg-hero` (marker only).
- ❌ Child-theme-specific styling in `assets/blocks.css` — parent CSS
  must work for any palette; client styling goes in the child's
  `style.css`.
- ❌ Hard-coded hexes, px radii, or shadows in block CSS — tokens only.
- ❌ Manual block registration.
- ❌ Renaming ACF field keys (see above).
- ❌ A new block without a `/style-guide` sample, a `usage.md`, and a
  `BLOCKS.md` regeneration (the generator refuses to pass without the
  usage entry).
