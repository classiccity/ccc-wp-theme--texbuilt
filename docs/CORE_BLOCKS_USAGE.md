# Core Blocks Usage — when NOT to reach for an ACF block

The companion to [`BLOCKS.md`](./BLOCKS.md) (the ACF block registry): what core
Gutenberg gives you, when it's the right tool, and the canonical core patterns
this theme expects. Numbered rules cited here live in
[`CONTENT_BUILDING_RULES.md`](./CONTENT_BUILDING_RULES.md) — that file is law;
this one is the field guide. Per-site specifics live in the child theme's
`AUTHORING_PATTERNS.md` (reference: `sg-lumberock`).

Audience: any session authoring or importing page content — especially a
content-import mapper deciding what a chunk of scraped/wireframed source
becomes.

---

## The decision ladder

For every section of source content, walk down; stop at the first fit:

1. **Core text flow** — heading + paragraph(s) + list + buttons. Rule 1: standard
   elements are ALWAYS core blocks, no inline styles, no custom HTML.
2. **Core composition** — group / columns arrangements (patterns below) for
   intros, two-column headers, banded sections.
3. **A block style on a core block** — the registered `is-style-*` variants
   (table below) before any custom treatment.
4. **An ACF block** — when the content is *structured and repeating* (cards,
   steps, stats, logos, testimonials) or the treatment is a real designed
   section. Choose via the usage layer in `BLOCKS.md`.
5. **Propose a new block** — rule 3: surface it, get Chris's sign-off, build it
   per `BLOCK_AUTHORING.md` (which now requires a `usage.md`).

**The Lumberock heuristic:** if you're about to apply more than ~2 style
overrides to force an ACF block to match a design, stop — build it as a
`wp:group` composition instead.

---

## Registered block styles (the whole list — never invent one)

| Style | On | What it does / when |
| --- | --- | --- |
| `is-style-eyebrow` | paragraph | The small over-heading label. Always paired with the heading it introduces (rule 9). |
| `is-style-quote` | quote | Actual quotations only — words somebody said. Labeled statements (mission, promise) get a real heading + paragraph instead (rule 23). |
| `is-style-section` | group | "Section Panel" — the panel/card treatment for a contained sub-section. |
| `is-style-bg-texture` / `is-style-bg-texture-sand` | group | Texture wash behind a band (see also the `background-texture` ACF block for projected textures). |
| `is-style-shadow` | image | Drop shadow on a standalone image. |
| `is-style-flush-top` / `is-style-overlap-up` / `is-style-underlap` | group, columns, cover | Vertical-rhythm adjusters for pulling a section against/over its neighbor. Use sparingly, on purpose. |

---

## Canonical core patterns

### 1. Section intro: `wp:group` is the section primitive

Default shape for any section opening — NOT the `center-content` ACF block,
whose rigid eyebrow+h2+p+button shape is the exception, not the default:

```html
<!-- wp:group {"align":"full","layout":{"type":"constrained"}} -->
  <!-- wp:paragraph {"className":"is-style-eyebrow","textColor":"secondary"} -->
  <!-- wp:heading {"level":2} -->
  <!-- wp:paragraph -->
<!-- /wp:group -->
```

### 2. Two-column section header with a CTA (rule 21)

Heading LEFT, button RIGHT — a plain columns block, no wrapping group. Heading
column top-aligned; button column bottom-aligned, button right-justified.

### 3. Banding (rules 8, 9, 10, 15, 16)

- A colored band = a `wp:group` with a background color AND symmetric top/bottom
  spacing-token padding **written as a real inline `style` on the div** (static
  block — comment-level padding doesn't re-serialize; rule 8 gotcha).
- No background → **no** vertical padding; let block rhythm flow.
- The band wraps the heading AND the content it introduces (9); no heading-only
  bands, no headingless bands (10); never two colored bands back-to-back (15).
- One block with its own background support → set the color ON the block
  (16). A multi-block zone that reads as one region → one wrapping group
  carries the background, children stay unpainted.

### 4. Buttons (rule 6)

Page's main CTA → **CTA** background · a section's primary (non-main) button →
**Primary** · second button in a row → **outline** style. Colors on colored
surfaces come from the automatic opposite pairing (rule 7), never set manually.

### 5. FAQs → `yoast/faq-block`, always (rule 24)

Never `core/details` accordions or heading/paragraph runs.

### 6. Repeating site-wide sections → synced patterns (rule 22)

Authored once as a `wp_block`, referenced by `<!-- wp:block {"ref":N} /-->`.
Seeded by script at most once; Gutenberg owns it after.

### 7. Every block gets a `metadata.name` (rule 18)

So the list view reads like a page outline, not a stack of "Group"s.

---

## When core is the WRONG tool

- **Repeating structured items** — 3+ peers of (icon/image + title + blurb):
  that's a field-driven ACF block (`feature-grid`, `image-card-grid`,
  `detail-cards`, …). A hand-built columns grid of these drifts and breaks the
  moment counts change (rule 19 lives in the blocks for free).
- **Non-linked feature lists as text columns** — rule 20: render as an
  icon-card grid, not a wall of text.
- **Testimonials** — the `testimonial` CPT + `testimonial-cards`, not
  hand-quoted blockquotes.
- **Anything needing per-item links or icons** — only via blocks that render
  them, and only with REAL links (rule 11) and REAL FontAwesome icons (rule 12).

---

*Cross-links: [`BLOCKS.md`](./BLOCKS.md) · [`CONTENT_BUILDING_RULES.md`](./CONTENT_BUILDING_RULES.md) ·
[`BLOCK_AUTHORING.md`](./BLOCK_AUTHORING.md) · [`CLIENT_HOMEPAGE.md`](./CLIENT_HOMEPAGE.md) ·
child themes' `AUTHORING_PATTERNS.md`.*
