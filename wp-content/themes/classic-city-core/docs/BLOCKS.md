<!-- GENERATED FILE — do not edit by hand. Regenerate: npm run docs:blocks -->

# Block Field-Key Registry

Generated from `blocks/*/block.json`, `blocks/*/fields.php`, `blocks/*/render.php`,
and the authored `blocks/*/usage.md` by `scripts/generate-block-docs.php`. If this
file disagrees with the code, regenerate it.

## How ACF block data is stored

ACF blocks persist their data inside the Gutenberg block comment as a `data` object.
Every field appears **twice**:

```
"image": 123,                     ← field name : value
"_image": "field_hero_image"      ← _name : the ACF field KEY
```

The `_name` entry tells ACF which field definition the value belongs to. **A wrong
field key renders blank on the front end — always use the keys from this file.**

**Repeaters** flatten: the repeater's own entry is the row count (integer), and each
row's fields are flat keys `{repeater}_{i}_{subfield}` with matching
`_{repeater}_{i}_{subfield}` key entries (e.g. `cards_0_title` / `_cards_0_title`).

**Hybrid vs field-driven:** *Hybrid (InnerBlocks)* blocks render an `<InnerBlocks />`
slot, so authored child blocks live between the opening and closing block comments.
*Field-driven* blocks have no slot and are written self-closing (`<!-- wp:… /-->`).

## Choosing blocks (the usage layer)

Every block folder carries an authored `usage.md` — purpose, content shape,
use-when / avoid-when — inlined into its section below. That layer is what a
content-import session uses to map source content onto blocks. Before reaching
for ANY ACF block, check whether plain core blocks are the right tool:
[`CORE_BLOCKS_USAGE.md`](./CORE_BLOCKS_USAGE.md). The non-negotiable composition
rules live in [`CONTENT_BUILDING_RULES.md`](./CONTENT_BUILDING_RULES.md).

## Index

| Block (slug) | Title | Kind | Purpose |
| --- | --- | --- | --- |
| [`background-texture`](#background-texture) | Background Texture | Field-driven | Purely decorative — projects a registered brand texture behind neighboring sections; communicates nothing on its own. |
| [`card-detail-rows`](#card-detail-rows) | Card with Detail Rows | Field-driven | The evidence card — a claim with its receipts attached. Each card names an outcome, then backs it with a short ledger of label → value pairs ("Applications 19 / Screened 15 / Enrolled 8") and a sentence of context. Where `stats` compresses proof to bare figures, this keeps each figure labeled and in sequence, so a reader can follow a funnel, a comparison, or a spec list without prose. |
| [`center-content`](#center-content) | Center Content | Hybrid (InnerBlocks) | A centered, breathing "statement" section — one image overhanging the top, then centered headline + short copy + optional CTA. |
| [`chart-horizontal-bars`](#chart-horizontal-bars) | Chart: Horizontal Bars | Field-driven | Distribution made visible — "where did the 281 visitors come from?" answered as four bars a reader takes in without doing arithmetic. The bar IS the percentage, so the block draws the proportion and prints only the raw figure beside it. |
| [`comparison-table`](#comparison-table) | Comparison Table | Field-driven | Us against a benchmark, metric by metric — "13 min 09 sec against a peer average of 5 min 15 sec", drawn as two bars so the gap is the thing you see first. Each metric is scaled against its own pair, never against a shared total, which is what lets a duration, a page count and an authority score sit on one card without pretending they add up. |
| [`cta-large`](#cta-large) | Large CTA | Hybrid (InnerBlocks) | The page's big conversion band — a full/wide color or gradient band carrying a heading, short pitch, and button(s). |
| [`cta-thin`](#cta-thin) | Thin CTA | Field-driven | A narrow one-line nudge strip — headline (+ optional subtext) left, single button right — for mid-page conversion moments. |
| [`detail-cards`](#detail-cards) | Detail Cards | Field-driven | Editorial data cards — a stat + label pair over a photo, description anchored at the bottom — proof points with visual atmosphere. |
| [`document-downloads`](#document-downloads) | Document Downloads | Field-driven | Hands visitors a list of downloadable files — specs, warranties, brochures, forms — each as a full-card download link. |
| [`feature-detail`](#feature-detail) | Feature Detail | Field-driven | Deep-dive panel on ONE feature/product/collection — image with a floating caption card beside headline, description, and icon-labeled detail rows. |
| [`feature-grid`](#feature-grid) | Feature Grid | Field-driven | The workhorse "what you get" section — a grid of icon-topped cards, each naming a feature/benefit with a short blurb. |
| [`feature-showcase`](#feature-showcase) | Feature Showcase | Field-driven | A two-beat pitch: colored panel with centered title + copy + product image on top, then a row of icon pods that break the offer into parts. |
| [`framed-callout`](#framed-callout) | Framed Callout | Hybrid (InnerBlocks) | A gallery-matted hero/CTA — the colored panel sits inside a visible frame of page background, giving a premium inset look at up to full viewport height. |
| [`hero`](#hero) | Hero | Hybrid (InnerBlocks) | The default page-top hero — headline, body, and buttons on one side, a photo on the other; the safest opener when in doubt. |
| [`hero-3-up`](#hero-3-up) | Hero 3 Up | Field-driven | Editorial page-top hero — eyebrow + h1 left (2/3), description + button right (1/3), then a wide banner image below the intro row. |
| [`hero-full-image`](#hero-full-image) | Hero: Full Image | Hybrid (InnerBlocks) | Cinematic page-top hero — a big headline over a full-bleed background photo (or looping video), with supporting copy/buttons in a card on top. |
| [`hero-gradient`](#hero-gradient) | Hero — Gradient | Hybrid (InnerBlocks) | Brand-saturated page-top hero — photo dimmed to 70% over a palette color plus a bottom-left gradient, so copy always reads and the brand color dominates. |
| [`highlight-tiles`](#highlight-tiles) | Highlight Tiles | Field-driven | A bento wall of short brag-lines — 4 or 6 asymmetric colored tiles, each a punchy claim with an optional image, for "why us" marketing pitch moments. |
| [`highlighted-image-gallery`](#highlighted-image-gallery) | Highlighted Image Gallery | Field-driven | Product-page style gallery — one large featured image with a clickable thumbnail row that swaps images into the primary slot. |
| [`icon-feature-row`](#icon-feature-row) | Icon Feature Row | Field-driven | A compact strip of icon + heading (+ optional one-liner) items — quick trust/capability signals without the visual weight of full cards. |
| [`image-card-grid`](#image-card-grid) | Image Card Grid | Field-driven | The general-purpose card grid — image on top, tag/title/description below — for collections, services, testimonials-with-photos, or any titled visual list. |
| [`image-columns`](#image-columns) | Image Columns | Field-driven | Presents 2–5 parallel offerings (services, product lines, benefits) as equal-weight cards, each with an image, a short pitch, and its own optional button. |
| [`image-hero-50-50`](#image-hero-50-50) | Image Hero 50/50 | Hybrid (InnerBlocks) | Full-bleed page-top hero: media (photo or looping video) fills one half of the viewport, a branded color/gradient panel with the page's opening pitch fills the other. |
| [`image-link-cards`](#image-link-cards) | Image Links with Icons | Field-driven | A navigation grid — square image cards, each fully clickable, with a colored footer bar (FontAwesome icon + title + arrow) telling the visitor "pick your path." |
| [`image-overlay`](#image-overlay) | Image + Content Overlay | Hybrid (InnerBlocks) | A large landscape photo anchors the section while a colored content card floats over one edge — an editorial "moment" that pulls the eye from imagery into a message or CTA. |
| [`image-tiles`](#image-tiles) | Image Tiles | Field-driven | Photo-first tile row where each image carries a one-line blurb over a gradient — mood/category tiles that let photography do the talking. |
| [`image-wall`](#image-wall) | Image Wall | Field-driven | Pure ambience: two full-width marquee rows of photos auto-scrolling in opposite directions — communicates volume and vibe ("look how much we've made"), not specific information. |
| [`link-pods`](#link-pods) | Link Pods | Field-driven | Text-first navigation cards on brand color — each pod pitches a destination in a sentence or two and the whole card is the link ("Our story", "Find a dealer", "Get a quote"). |
| [`logo-strip`](#logo-strip) | Logo Strip | Field-driven | Social proof at a glance — a row of client/partner/press logos saying "these people trust us" without a word of copy. |
| [`portfolio-gallery`](#portfolio-gallery) | Image Portfolio Gallery | Field-driven | Browsable proof of work — a dense grid of project images that open in a lightbox with title and caption, for visitors who want to inspect, not just glance. |
| [`post-card`](#post-card) | Post Card | Field-driven | Renders the CURRENT post inside a Query Loop as a standard image card (featured image, category eyebrow, linked title, excerpt, read-more) — the blog/news listing card. |
| [`process-steps`](#process-steps) | Process Steps | Field-driven | Shows "how it works" as a numbered sequence — step cards with auto-generated number badges that promise the visitor a clear, finite path. |
| [`product-feature-toggles`](#product-feature-toggles) | Product Feature Toggles | Field-driven | An interactive comparison of product variants/categories — a row of icon tabs swaps a two-column panel (image + caption card on the left, headline + description + spec rows + CTA on the right). |
| [`proof-band`](#proof-band) | Proof Band | Field-driven | The page's proof beat, delivered in one breath — where the evidence came from, two to four figures that carry it, the sentence a human said about it, and the one link to the full story. It exists because that sequence keeps getting rebuilt out of a stats band, a quote block and a button that then drift apart on the page; here they are one surface with one background and one rhythm. |
| [`split-50-50`](#split-50-50) | Split 50/50 | Hybrid (InnerBlocks) | The workhorse mid-page section: media on one half, free-form content on the other — one topic, one image, one message per instance. |
| [`stackable`](#stackable) | Stackable | Hybrid (InnerBlocks) | A scroll-theater wrapper: children pin at a sticky offset and each later one slides up to cover the last — turns a series of sections into a paced, one-at-a-time reveal. |
| [`stat-comparison`](#stat-comparison) | Stat Comparison | Field-driven | One number moved. "14.3% → 79.1%" set at display scale beside the sentence that says what it measures — the whole block is a single before/after claim, sized so a scanner takes the direction from the shape alone and a reader gets the caveat from the paragraph. |
| [`stats`](#stats) | Stats | Field-driven | A big-number proof band — "12k+ projects, 98% satisfaction, 24/7 support" — credibility compressed into figures a visitor absorbs in two seconds. |
| [`swatch-explorer`](#swatch-explorer) | Swatch Explorer | Field-driven | An interactive color/material picker: scenario tabs (Morning / Overcast / Evening, or product lines) frame a big featured photo that swaps as the visitor clicks swatches in a scrollable row — "see it in YOUR color." |
| [`testimonial-cards`](#testimonial-cards) | Testimonial Cards | Field-driven | Multi-voice social proof — a grid of quote cards (quote glyph, quote text, name, title + company) showing that several real people vouch for the client. |
| [`text-callout`](#text-callout) | Text Callout | Field-driven | The scroll-stopper. One or two sentences set at heading scale but written as prose — a mission line, a promise, a refusal — dropped between sections to make a reader pause. It carries no card, no background and no icon; the size and the space around it do all the work. |

---

### background-texture

**Background Texture** (`classic-city-core/background-texture`) — Drop-in texture overlay. The block itself has effectively zero height; a positioned texture image projects up, down, or both directions from the block's drop point. Source textures come from theme.json's settings.custom.textures, so adding new texture options to the dropdown is a child-theme registration step (no per-block work).

- Kind: Field-driven
- view.js: no

**Purpose:** Purely decorative — projects a registered brand texture behind neighboring sections; communicates nothing on its own.

**Content shape:** No copy, no images to supply. Two fields: `texture_slug` (select, populated from the child theme's `settings.custom.textures` in theme.json — renders NOTHING if the theme registers no textures) and `arrangement` (`bottom` | `top` | `middle`). Zero-height block; texture sits at `z-index: -1`, visible only where adjacent sections have a transparent background.

**Use when:**
- The child theme has textures registered in theme.json AND a page stretch of unbanded (transparent-bg) sections feels flat.
- The design calls for brand texture bleeding behind a seam between two sections (drop at the seam, pick `middle`).
- You want atmosphere without adding a colored band (rule 10 — no orphan color strips).

**Avoid when:**
- The child theme registers no textures — the block bails silently; skip it entirely.
- The neighboring sections carry opaque backgrounds — the texture is invisible behind them; use the block's own `has_texture` toggle (cta-large, cta-thin) or the section's background instead.
- You're importing content — this is a design-polish drop-in, not a content container; never map source copy to it.

**Pairs with:** center-content, cta-large, cta-thin, hero-gradient (any texture-aware section); placed as a sibling between full-width sections.

**Core alternative:** None — core blocks can't do this. The alternative is simply omitting it; it is always optional.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `texture_slug` | `field_background_texture_slug` | select | — |  |
| `arrangement` | `field_background_texture_arrangement` | select | — | choices: `bottom` / `top` / `middle`; default: `bottom` |

### card-detail-rows

**Card with Detail Rows** (`classic-city-core/card-detail-rows`) — Grid of data cards. Each card stacks an eyebrow, an h3, a hairline-separated list of label → value detail rows, a WYSIWYG description, and an optional button pinned to the bottom edge. The block-level color picker paints each CARD (not the grid), and the card border defaults to that color's palette partner (its “alt”).

- Kind: Field-driven
- view.js: no

**Purpose:** The evidence card — a claim with its receipts attached. Each card names an outcome, then backs it with a short ledger of label → value pairs ("Applications 19 / Screened 15 / Enrolled 8") and a sentence of context. Where `stats` compresses proof to bare figures, this keeps each figure labeled and in sequence, so a reader can follow a funnel, a comparison, or a spec list without prose.

**Content shape:** `cards` repeater, 1–12 (set `desktop_columns` 1–4 to FIT the count — rule 19). Per card, everything below the eyebrow is optional, so one field set covers plain cards and dense ones: `eyebrow` (2–6 words, renders as `.is-style-eyebrow`), `heading` (fixed h3 — cards live under a section h2, rule 9), a nested `rows` repeater of `label` + `value` (0–10), `description` (WYSIWYG, small font size), and `button_text` / `button_link` / `button_style` (`fill` | `outline` | `text`, rule 6). `value` is TEXT, not a number — "within 18 hours", "5,000+" and "$75k–$150k" are all valid; nothing is summed or charted. When a button is present it pins to the card's bottom edge so buttons align across a row of uneven cards. `mobile_layout` is `stack` or `scroll`.

Card background is ONE block-level native-picker choice (color or gradient) applied to every card — render.php strips it off the grid root and re-applies per card, so the grid behind stays transparent. The card border auto-derives to that color's palette partner (`primary` card → `primary-alt` border); gradients and the unpaired `canvas` / `panel` / `ink` / `ink-soft` slugs fall back to the default border token.

**Use when:**
- Source copy pairs a claim with 2–4 supporting figures that each need a name — funnel steps, before/after counts, tiers, spec rows.
- Several parallel case studies or offerings need the same skeleton but carry different amounts of detail (one has a ledger, one is all prose).
- Numbers are non-uniform in kind — durations, currency, counts and percentages side by side — which `chart-horizontal-bars` can't take because it must sum them.

**Avoid when:**
- The values are one comparable quantity and the story is their relative size — use `chart-horizontal-bars`, which draws the proportion instead of making the reader do arithmetic.
- The figures need no labels and should read as a headline proof band — use `stats` (rule 17: give it a background).
- Each item is a feature/benefit with an icon and a blurb, no figures — use `feature-grid` (rule 20).
- Items should link as a whole rather than carry a button — use `link-pods` or `image-link-cards` (rule 11).

**Pairs with:** `chart-horizontal-bars` (the same evidence, drawn rather than listed), `stats`, `cta-large` to close. Lead it with a core-group section header — eyebrow + h2 (rules 9, 10); the per-card eyebrow is a card label, not the section's.

**Core alternative:** Core columns of heading + list get close, but the label/value row needs a two-column hairline layout that core lists can't produce without inline styles (rules 1, 2), and nothing in core pins a button to a card's bottom edge across a row.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `desktop_columns` | `field_card_detail_rows_desktop_columns` | select | — | choices: `1` / `2` / `3` / `4`; default: `3` |
| `mobile_layout` | `field_card_detail_rows_mobile_layout` | select | — | choices: `stack` / `scroll`; default: `stack` |
| `cards` | `field_card_detail_rows_cards` | repeater | — | rows: min 1, max 12; value = row count (int); rows serialize flat as `cards_{i}_{subfield}` |
| cards → `eyebrow` | `field_card_detail_rows_eyebrow` | text | — | serialized key: `cards_{i}_eyebrow` |
| cards → `heading` | `field_card_detail_rows_heading` | text | — | serialized key: `cards_{i}_heading` |
| cards → `rows` | `field_card_detail_rows_rows` | repeater | — | rows: min 0, max 10; value = row count (int); rows serialize flat as `cards_{i}_rows_{i}_{subfield}` |
| cards → rows → `label` | `field_card_detail_rows_row_label` | text | Yes | serialized key: `cards_{i}_rows_{i}_label` |
| cards → rows → `value` | `field_card_detail_rows_row_value` | text | Yes | serialized key: `cards_{i}_rows_{i}_value` |
| cards → `description` | `field_card_detail_rows_description` | wysiwyg | — | serialized key: `cards_{i}_description` |
| cards → `button_text` | `field_card_detail_rows_button_text` | text | — | serialized key: `cards_{i}_button_text` |
| cards → `button_link` | `field_card_detail_rows_button_link` | url | — | serialized key: `cards_{i}_button_link` |
| cards → `button_style` | `field_card_detail_rows_button_style` | select | — | choices: `fill` / `outline` / `text`; default: `fill`; serialized key: `cards_{i}_button_style` |

### center-content

**Center Content** (`classic-city-core/center-content`) — Centered copy block with an overhang image and optional background texture.

- Kind: Hybrid (InnerBlocks)
- view.js: no

**Purpose:** A centered, breathing "statement" section — one image overhanging the top, then centered headline + short copy + optional CTA.

**Content shape:** One required image (`image`; resolves to a demo placeholder on /style-guide only — supply a real upload on client pages). Everything else is InnerBlocks (Hybrid): typically heading + 1 short paragraph + buttons, all centered. Full-align; background texture layer baked in. Copy budget: one h2 and 2–3 sentences.

**Use when:**
- Source has a single mission/values/brand statement with one supporting image — not a list, not a grid.
- A page needs a calm centered beat between two busier sections (grids, heroes).
- The image is decorative/atmospheric rather than informational (it renders as a background-style overhang, cropped).

**Avoid when:**
- There's no image at all — use a plain core group with centered heading + paragraph + buttons instead (rule 1).
- The copy is a labeled statement that wants a quote treatment — it isn't one; still use a real heading per rule 23, which this block supports.
- Content splits into image-beside-text — use `split-50-50` or `image-overlay` instead.

**Pairs with:** cta-thin, cta-large, feature-grid, image-card-grid (style guide places it between portfolio-gallery and cta-thin).

**Core alternative:** If the section is text-only (no hero image), a core group with centered heading/paragraph/buttons is lighter and preferred.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `image` | `field_center_content_image` | image | Yes | return_format: `array` |

### chart-horizontal-bars

**Chart: Horizontal Bars** (`classic-city-core/chart-horizontal-bars`) — A card containing a horizontal bar chart. Each repeater row is a label, a proportional bar, and its value; bar widths are computed server-side as each value's share of the total, so authors type real numbers and never a percentage. Rows can be flagged as highlighted. The block-level color picker paints the card, and its border is auto-derived to that color's palette partner (its “alt”).

- Kind: Field-driven
- view.js: no

**Purpose:** Distribution made visible — "where did the 281 visitors come from?" answered as four bars a reader takes in without doing arithmetic. The bar IS the percentage, so the block draws the proportion and prints only the raw figure beside it.

**Content shape:** One card holding `title` (h3), optional `description` (WYSIWYG, small), a `bars` repeater (1–20), and an optional `footnote` (WYSIWYG, small — source or caveat). Per bar: `label` (required), `value` (required, NUMBER — decimals allowed), `is_highlight` (true/false). **Bar widths are computed server-side as each value's share of the SUM of all values** — authors enter real figures (205 / 41 / 23 / 12), never percentages, and no percentage field exists to drift out of sync with the numbers beside it. A zero total yields 0% bars rather than dividing by zero, so the block degrades to a labeled list.

A flagged row gets `.is-highlighted`, which paints its bar with the CTA color (rule 14: that's the page's one contrast color — flag one row, not four). Background color/gradient via the native picker; the card border auto-derives to that color's palette partner (`primary` → `primary-alt`), with gradients and the unpaired `canvas` / `panel` / `ink` / `ink-soft` slugs falling back to the border token. Repaint bars via `--ccc-chb-fill` rather than out-specifying selectors — every rule is a single class by design.

**Use when:**
- Values are ONE comparable quantity that genuinely partitions a whole — visitors by country, applications by source, budget by line. Share-of-total is only honest when the parts belong to the same total.
- The relative sizes ARE the point, and a reader shouldn't have to compare digits to see them.
- There are 3–8 rows. Two bars is a sentence; past ~12 the labels crowd and a table reads better.

**Avoid when:**
- The values don't partition a whole — three unrelated KPIs, or an us-vs-them comparison where each side is its own 100%. Share-of-total will draw a misleading picture; use `card-detail-rows` or `stats` until this block gains a scale-mode field.
- Values aren't summable — durations, currency ranges, "within 18 hours". Use `card-detail-rows`, whose `value` is free text.
- The figures need no proportion, just prominence — use `stats` (rule 17: give it a background).
- You'd be flagging most rows as highlighted — that's no highlight at all, and it burns the page's contrast color (rule 14).

**Pairs with:** `card-detail-rows` (the same evidence listed rather than drawn — a natural before/after), `stats`, `cta-large`. Lead it with a core-group section header (rules 9, 10); the block's own `title` is the chart's label, not the section's.

**Core alternative:** None. Core has no bar primitive, and faking one with sized groups means hand-computed widths as inline styles (rules 1, 4) that silently rot the moment a number changes — which is exactly what computing widths server-side prevents.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `title` | `field_chart_horizontal_bars_title` | text | — |  |
| `description` | `field_chart_horizontal_bars_description` | wysiwyg | — |  |
| `bars` | `field_chart_horizontal_bars_bars` | repeater | — | rows: min 1, max 20; value = row count (int); rows serialize flat as `bars_{i}_{subfield}` |
| bars → `label` | `field_chart_horizontal_bars_label` | text | Yes | serialized key: `bars_{i}_label` |
| bars → `value` | `field_chart_horizontal_bars_value` | number | Yes | serialized key: `bars_{i}_value` |
| bars → `is_highlight` | `field_chart_horizontal_bars_is_highlight` | true_false | — | default: `0`; serialized key: `bars_{i}_is_highlight` |
| `footnote` | `field_chart_horizontal_bars_footnote` | wysiwyg | — |  |

### comparison-table

**Comparison Table** (`classic-city-core/comparison-table`) — Us against them, one metric at a time. Each row names a metric and draws two paired bars — ours and theirs — scaled against each other rather than against a shared total, so every metric keeps its own units. Bar lengths are derived from the values server-side, and a row whose two values are not in the same unit prints without bars instead of drawing a false picture.

- Kind: Field-driven
- view.js: no

**Purpose:** Us against a benchmark, metric by metric — "13 min 09 sec against a peer average of 5 min 15 sec", drawn as two bars so the gap is the thing you see first. Each metric is scaled against its own pair, never against a shared total, which is what lets a duration, a page count and an authority score sit on one card without pretending they add up.

**Content shape:** One card holding `title` (h3), optional `description` (WYSIWYG, small), `our_label` / `their_label` (the two side names, declared ONCE and repeated on every row — "trialport" / "peer average"), a `rows` repeater (1–10), and a `footnote` (WYSIWYG, small). Per row: `label` (required — what is measured, "Average visit duration"), `our_value` and `their_value` (both required, both TEXT, printed exactly as typed).

**Bar lengths are derived from the values, not authored.** The block parses the first number out of each ("13 min 09 sec" → 13, "79.1%" → 79.1) and scales the pair so the larger fills its track. There is no bar-width field to drift out of sync with the figure beside it. Two things then guard the drawing: both sides must reduce to the same non-numeric residue (`"minsec"` vs `"minsec"` ✓, `"hrmin"` vs `"min"` ✗), and the larger of the pair must be positive. Fail either and the row prints its label and both figures with **no bars** — a quiet omission instead of a confidently wrong picture. So: express both sides in the same unit and the same shape, and keep ranges and caveats ("46, in a range of 22–56") out of the value field and in the footnote, or the row silently loses its bars.

The "ours" bar takes `cta` and the comparator takes a muted `currentColor` tint; background color/gradient via the native picker, with the card border auto-deriving to that color's palette partner. Retune with `--ccc-ct-fill` per row, `--ccc-ct-fill-muted`, `--ccc-ct-side-width` and `--ccc-ct-value-width` rather than out-specifying selectors.

**Use when:**
- Source copy compares the same 2–6 metrics between two named sides, and the gap is the argument.
- The metrics are in different units, so each row needs its own scale — the case `chart-horizontal-bars`' usage.md explicitly sends you away for.
- There is a real, citable source for the comparator. Fill the footnote; a benchmark claim without a source is the weakest thing on the page.

**Avoid when:**
- Values genuinely partition one whole (visitors by country, budget by line) — use `chart-horizontal-bars`, which draws share-of-total.
- It is one metric with two sides — use `stat-comparison`, which sets the pair at display scale.
- There is no comparator, just your own figures — use `stats` or `card-detail-rows`.
- The comparator is a named competitor. Name the category ("peer average", "industry benchmark") and cite it; naming a company turns a proof section into a claim you have to defend.
- Any row would need "lower is better" to read correctly. The block always draws the bigger number as the longer bar; if a metric inverts, say so in the metric label or leave it out.

**Pairs with:** `chart-horizontal-bars` (share-of-total for the rows that DO partition a whole — the two read consistently side by side, same bar treatment and same mobile stack), `stat-comparison`, `proof-band`. Lead it with a core-group section header (rules 9, 10); the card's own `title` is the chart's label, not the section's.

**Core alternative:** A core table can hold the figures, but it draws nothing — and the gap between "13 min 09 sec" and "5 min 15 sec" is a bar's worth of story that a reader otherwise has to do in their head. Faking the bars in core means hand-computed widths as inline styles (rules 1, 4) that rot the moment a number changes, which is the exact failure deriving them server-side prevents.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `title` | `field_comparison_table_title` | text | — |  |
| `description` | `field_comparison_table_description` | wysiwyg | — |  |
| `our_label` | `field_comparison_table_our_label` | text | — | default: `us` |
| `their_label` | `field_comparison_table_their_label` | text | — | default: `peer average` |
| `rows` | `field_comparison_table_rows` | repeater | — | rows: min 1, max 10; value = row count (int); rows serialize flat as `rows_{i}_{subfield}` |
| rows → `label` | `field_comparison_table_row_label` | text | Yes | serialized key: `rows_{i}_label` |
| rows → `our_value` | `field_comparison_table_row_our_value` | text | Yes | serialized key: `rows_{i}_our_value` |
| rows → `their_value` | `field_comparison_table_row_their_value` | text | Yes | serialized key: `rows_{i}_their_value` |
| `footnote` | `field_comparison_table_footnote` | wysiwyg | — |  |

### cta-large

**Large CTA** (`classic-city-core/cta-large`) — Full-width call-to-action band with brand color/gradient, optional background image, and InnerBlocks content.

- Kind: Hybrid (InnerBlocks)
- view.js: no

**Purpose:** The page's big conversion band — a full/wide color or gradient band carrying a heading, short pitch, and button(s).

**Content shape:** All content is InnerBlocks (Hybrid): typically h2 + one 2–3 sentence paragraph + a buttons row. ACF fields are background-only: optional `bg_image` (no demo fallback — omit rather than fake it), `bg_opacity` (0–100, default 80), `has_texture` toggle. Color/gradient comes from the native block picker (rule 16 — set it on the block, not a wrapping group).

**Use when:**
- Source page ends (or a section run ends) with a "contact us / get started / schedule" pitch that deserves a full band.
- The CTA needs more than one line of supporting copy, or two buttons (second button = outline style, rule 6).
- A photo should sit behind the pitch — keep `bg_opacity` low so it reads as texture, not a photo wall (rule 13).

**Avoid when:**
- The CTA is a single line + one button — use `cta-thin` instead; this block would look empty.
- The button has no real destination yet — don't ship a `#` CTA (rule 11); hold the section.
- It would sit directly against another colored band — separate with a neutral section (rule 15).

**Pairs with:** cta-thin (the small sibling), center-content, image-wall, testimonial-cards; conventionally the last section before the footer. Page's main CTA button gets the CTA background color (rule 6).

**Core alternative:** A core group with a background color + heading/paragraph/buttons works when no bg image/texture is wanted, but this block is the house pattern for CTA bands — prefer it.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `bg_image` | `field_cta_large_bg_image` | image | — | return_format: `array` |
| `bg_opacity` | `field_cta_large_bg_opacity` | range | — | default: `80` |
| `has_texture` | `field_cta_large_has_texture` | true_false | — | default: `0` |

### cta-thin

**Thin CTA** (`classic-city-core/cta-thin`) — Narrow full-width call-to-action strip — headline + subtext on the left, button on the right. Fully field-driven; no inner content.

- Kind: Field-driven
- view.js: no

**Purpose:** A narrow one-line nudge strip — headline (+ optional subtext) left, single button right — for mid-page conversion moments.

**Content shape:** Fully field-driven, no InnerBlocks. Required: `headline` (one short line), `button_label`, `button_url`. Optional: `subtext` (one line), `headline_level` (bold-p or h2–h6, default h3), `bg_image` + `bg_opacity` (default 80), `has_texture`. Background color from the native picker. Renders nothing without headline or button.

**Use when:**
- Source has a one-liner like "Questions? Get in touch" with a single obvious link — no paragraph of copy.
- You want a conversion beat between content sections without the weight of a full `cta-large` band.
- The CTA copy is strictly headline + one clause; the L/R layout only fits short text.

**Use `headline_level`:** pick a heading tag that fits the outline, or bold-p if the page's heading hierarchy is already satisfied.

**Avoid when:**
- The pitch needs a real paragraph or two buttons — use `cta-large` (InnerBlocks give you that freedom).
- There's no real destination URL — rule 11; skip the section rather than link `#`.
- It would stack against another colored band — rule 15; keep a neutral section between.

**Pairs with:** center-content, cta-large, image-card-grid, portfolio-gallery (style guide runs center-content → cta-thin → cta-large).

**Core alternative:** The rule-21 two-column header (heading left, button bottom-right) in plain core columns — use that when the strip should sit on the page background instead of a colored band.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `headline` | `field_cta_thin_headline` | text | Yes |  |
| `headline_level` | `field_cta_thin_headline_level` | select | — | choices: `p` / `h2` / `h3` / `h4` / `h5` / `h6`; default: `h3` |
| `subtext` | `field_cta_thin_subtext` | text | — |  |
| `button_label` | `field_cta_thin_button_label` | text | Yes |  |
| `button_url` | `field_cta_thin_button_url` | url | Yes |  |
| `bg_image` | `field_cta_thin_bg_image` | image | — | return_format: `array` |
| `bg_opacity` | `field_cta_thin_bg_opacity` | range | — | default: `80` |
| `has_texture` | `field_cta_thin_has_texture` | true_false | — | default: `0` |

### detail-cards

**Detail Cards** (`classic-city-core/detail-cards`) — Row of image-backgrounded data cards. Each card stacks a stat + stat title at the top (separated by a hairline border) over a background image, with a description anchored at the bottom. Optional infinite-marquee autoscroll.

- Kind: Field-driven
- view.js: no

**Purpose:** Editorial data cards — a stat + label pair over a photo, description anchored at the bottom — proof points with visual atmosphere.

**Content shape:** `cards` repeater, 1–24 (practical: match `column_count` 1–4 per rule 19, or go long with `autoscroll` marquee). Per card: `stat` (short value, "80", "0.93"), `stat_title` (short label, "Vitamin D"), `description` (1–2 small sentences), `bg_image` (optional per card — demo fallback fires on /style-guide only). Block-level: `bg_opacity`, `autoscroll`, `mobile_layout` (stack|scroll), `aspect_ratio` (default 3/4 portrait). Card color = native picker, propagated per-card.

**Use when:**
- Source pairs numbers with imagery — lab results, measurements, per-product specs where each stat has its own photo.
- 3–4 proof points each need a value, a label, AND a sentence of context (more than `stats` gives you).
- A long set (6+) suits a scrolling marquee showcase (`autoscroll`).

**Avoid when:**
- The numbers need no imagery or per-item copy — use the `stats` block (big-number row on a gradient).
- Items are feature claims, not data — use `feature-grid` (icons) or `image-card-grid` (titled cards).
- You have no real photos — cards without images are just tinted boxes; production has no placeholder fallback.

**Pairs with:** stats, image-card-grid, highlight-tiles; style guide gives it `backgroundColor: primary` — cards want a solid palette slug so the scrim gradient works (rule 17 spirit).

**Core alternative:** None good — core columns of heading+paragraph lose the layered image/scrim treatment. If it's plain numbers, that's `stats`, not core blocks.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `column_count` | `field_detail_cards_column_count` | select | — | choices: `1` / `2` / `3` / `4`; default: `3` |
| `bg_opacity` | `field_detail_cards_bg_opacity` | number | — | default: `100` |
| `autoscroll` | `field_detail_cards_autoscroll` | true_false | — | default: `0` |
| `mobile_layout` | `field_detail_cards_mobile_layout` | select | — | choices: `stack` / `scroll`; default: `stack` |
| `aspect_ratio` | `field_detail_cards_aspect_ratio` | select | — | choices: `3/4` / `4/5` / `1/1` / `4/3` / `3/2` / `16/9`; default: `3/4` |
| `cards` | `field_detail_cards_cards` | repeater | — | rows: min 1, max 24; value = row count (int); rows serialize flat as `cards_{i}_{subfield}` |
| cards → `stat` | `field_detail_cards_card_stat` | text | — | serialized key: `cards_{i}_stat` |
| cards → `stat_title` | `field_detail_cards_card_stat_title` | text | — | serialized key: `cards_{i}_stat_title` |
| cards → `description` | `field_detail_cards_card_description` | textarea | — | serialized key: `cards_{i}_description` |
| cards → `bg_image` | `field_detail_cards_card_bg_image` | image | — | return_format: `array`; serialized key: `cards_{i}_bg_image` |

### document-downloads

**Document Downloads** (`classic-city-core/document-downloads`) — List of downloadable documents. Each item links to a file and shows its type icon.

- Kind: Field-driven
- view.js: no

**Purpose:** Hands visitors a list of downloadable files — specs, warranties, brochures, forms — each as a full-card download link.

**Content shape:** `documents` repeater, 1–12. Per item: `file_type` select (PDF | DOC | XLS | FILE — drives a fixed FontAwesome glyph, no icon field to fill), required `title`, required `file` upload (items without a file URL are skipped at render), optional 1–2 line `body`. Every card renders a "Download →" affordance and links directly to the file.

**Use when:**
- Source page lists real downloadable assets (spec sheets, install guides, warranty PDFs) that you have files for or the client will upload.
- A resources/documentation section needs scannable file cards rather than inline text links.

**Avoid when:**
- The "documents" are actually pages/URLs, not files — use `link-pods` (whole-pod links to destinations).
- Files don't exist yet — the block silently drops fileless rows (rule 11 spirit: no dead download links); stub the section as a core heading + paragraph note until files arrive.
- There's only one file — a core paragraph with a styled core button linking the file is lighter.

**Pairs with:** icon-feature-row, process-steps, feature-detail; typically deep on resource/product pages, introduced by a core heading (rule 9 — heading belongs with the block).

**Core alternative:** For 1–2 files, a core heading + core buttons linking the uploads. Use this block once there are 3+ files worth a uniform card list.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `documents` | `field_document_downloads_items` | repeater | — | rows: min 1, max 12; value = row count (int); rows serialize flat as `documents_{i}_{subfield}` |
| documents → `file_type` | `field_document_downloads_type` | select | — | choices: `PDF` / `DOC` / `XLS` / `FILE`; default: `PDF`; serialized key: `documents_{i}_file_type` |
| documents → `title` | `field_document_downloads_title` | text | Yes | serialized key: `documents_{i}_title` |
| documents → `file` | `field_document_downloads_file` | file | Yes | return_format: `array`; serialized key: `documents_{i}_file` |
| documents → `body` | `field_document_downloads_body` | textarea | — | serialized key: `documents_{i}_body` |

### feature-detail

**Feature Detail** (`classic-city-core/feature-detail`) — Single feature presentation panel — image with overlapping caption card on one side, headline + description + detail rows + optional CTA on the other. Shares its rendered HTML with one tab's content panel in Product Feature Toggles via partials/feature-detail-section.php.

- Kind: Field-driven
- view.js: no

**Purpose:** Deep-dive panel on ONE feature/product/collection — image with a floating caption card beside headline, description, and icon-labeled detail rows.

**Content shape:** Required `image` (demo fallback on /style-guide only). Optional caption card: `image_subtitle` (small caps, "FOR WATERFRONT") + `image_title` ("Coastal Collection"). Content side: `headline`, `description` (2–3 sentences), optional `button_text`+`button_url`, `detail_rows` repeater 0–8 (each: required `label`, FontAwesome `icon_name` like `droplet`, 1–2 line `description`). `detail_color` palette slug tints row bars + icons. No built-in section header — wrap in a core group with your own heading if needed.

**Use when:**
- Source devotes a whole section to one thing with 3–6 attribute bullets (durability specs, service inclusions) that can each carry a real FA icon (rule 12).
- A product/collection page needs the "one image + attribute breakdown" panel.
- Content matches one tab of `product-feature-toggles` but there's only one item — this is the standalone single-panel version.

**Avoid when:**
- You have several parallel features to toggle between — use `product-feature-toggles`.
- The section is image + free-form prose without attribute rows — use `split-50-50` (InnerBlocks freedom).
- Attributes have no honest icons — use `split-50-50` with a core list instead of forcing icons (rule 12).

**Pairs with:** hero-3-up, icon-feature-row, image-card-grid, document-downloads; precede with a core-group section header per its own convention (rule 9).

**Core alternative:** Core columns (image | heading + paragraph + list + button) when there are no icon detail rows or floating caption to justify the block.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `image` | `field_feature_detail_image` | image | Yes | return_format: `array` |
| `detail_color` | `field_feature_detail_detail_color` | select | — | choices: palette slugs (dynamic, per child theme); default: `secondary` |
| `image_subtitle` | `field_feature_detail_image_subtitle` | text | — |  |
| `image_title` | `field_feature_detail_image_title` | text | — |  |
| `headline` | `field_feature_detail_headline` | text | — |  |
| `description` | `field_feature_detail_description` | textarea | — |  |
| `button_text` | `field_feature_detail_button_text` | text | — |  |
| `button_url` | `field_feature_detail_button_url` | url | — |  |
| `detail_rows` | `field_feature_detail_rows` | repeater | — | rows: min 0, max 8; value = row count (int); rows serialize flat as `detail_rows_{i}_{subfield}` |
| detail_rows → `label` | `field_feature_detail_row_label` | text | Yes | serialized key: `detail_rows_{i}_label` |
| detail_rows → `icon_name` | `field_feature_detail_row_icon_name` | text | — | serialized key: `detail_rows_{i}_icon_name` |
| detail_rows → `description` | `field_feature_detail_row_description` | textarea | — | serialized key: `detail_rows_{i}_description` |

### feature-grid

**Feature Grid** (`classic-city-core/feature-grid`) — Grid of features (2-5 columns on desktop), each with an icon, heading, and body.

- Kind: Field-driven
- view.js: no

**Purpose:** The workhorse "what you get" section — a grid of icon-topped cards, each naming a feature/benefit with a short blurb.

**Content shape:** `features` repeater, 1–12 (pick `desktop_columns` 2–5 to FIT the count — rule 19: fewer items than columns looks broken, 6 items → 3 cols not 4). Per feature, all required: `icon_name` (bare FontAwesome slug, e.g. `fa-star` — must be a real glyph, rule 12), `heading` (2–5 words), `body` (1–3 short sentences). No images, no links. Card background = one native-picker color applied to every card; icon chip auto-inverts.

**Use when:**
- Source lists 3–12 non-linked capability/benefit claims, each summarizable in a heading + a couple sentences.
- A features/services wall of text needs converting to icon cards — rule 20 says non-linked feature lists ARE icon cards, and every item maps to an obvious FA icon.
- Items are parallel in weight; no single feature deserves more space than the others.

**Avoid when:**
- Items should LINK somewhere — use `link-pods` or `image-link-cards` (rule 11: linked lists use linked blocks).
- You can't find honest FontAwesome icons for the items — rule 12; use `image-card-grid` or plain core columns instead.
- Copy per item is one line and space is tight — use `icon-feature-row` (the tighter sibling).

**Pairs with:** stats, split-50-50, logo-strip, cta-large; lead it with a core-group section header (eyebrow + h2, rule 9).

**Core alternative:** Only when items are prose-heavy narrative paragraphs, use core columns of heading + paragraph — but per rule 20 the icon-card grid is the default for feature lists.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `desktop_columns` | `field_feature_grid_desktop_columns` | select | — | choices: `1` / `2` / `3` / `4` / `5`; default: `3` |
| `features` | `field_feature_grid_items` | repeater | — | rows: min 1, max 12; value = row count (int); rows serialize flat as `features_{i}_{subfield}` |
| features → `icon_name` | `field_feature_grid_icon_name` | text | Yes | serialized key: `features_{i}_icon_name` |
| features → `heading` | `field_feature_grid_heading` | text | Yes | serialized key: `features_{i}_heading` |
| features → `body` | `field_feature_grid_body` | textarea | Yes | serialized key: `features_{i}_body` |

### feature-showcase

**Feature Showcase** (`classic-city-core/feature-showcase`) — Centered hero panel (title, copy, image) with a row of icon-feature pods below.

- Kind: Field-driven
- view.js: no

**Purpose:** A two-beat pitch: colored panel with centered title + copy + product image on top, then a row of icon pods that break the offer into parts.

**Content shape:** Top: required `top_title`, optional `top_description` (WYSIWYG, basic formatting), optional `top_image` (sits flush to the panel's bottom edge — a product/app shot works best; demo fallback on /style-guide only). Bottom: `pods` repeater 1–8 (`pod_columns` 2–4 — match count, rule 19). Per pod: required FA `icon_name` + `title` + `body`; optional `button_text`+`button_url` (outline buttons, one `pod_button_color` slug for all). Panel color from the native picker.

**Use when:**
- Source has a "here's the product" hero-ish intro AND 2–4 supporting capability pods that belong together as one section.
- A mid-page product spotlight needs its own colored panel plus a breakdown row — one block instead of hero + feature-grid.
- Pods each have a real FA icon (rule 12) and, if buttons are used, real URLs (rule 11).

**Avoid when:**
- You only need the pod row — use `icon-feature-row` or `feature-grid`.
- You only need the top panel — use `cta-large` (InnerBlocks) or `center-content`.
- It's the page's actual top hero — use a hero variant (`hero`, `hero-full-image`, `hero-gradient`, `hero-3-up`); this block doesn't carry the `sg-hero` marker.

**Pairs with:** logo-strip, testimonial-cards, cta-large; the style guide runs it as a late-page showcase on `backgroundColor: primary`.

**Core alternative:** None clean — the flush-bottom image panel + pod row is bespoke. If content doesn't fill both beats, split into simpler blocks instead.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `top_title` | `field_feature_showcase_top_title` | text | Yes |  |
| `top_description` | `field_feature_showcase_top_description` | wysiwyg | — |  |
| `top_image` | `field_feature_showcase_top_image` | image | — | return_format: `array` |
| `pod_columns` | `field_feature_showcase_pod_columns` | select | — | choices: `2` / `3` / `4`; default: `3` |
| `pod_button_color` | `field_feature_showcase_pod_button_color` | select | — | choices: palette slugs (dynamic, per child theme); default: `primary` |
| `pods` | `field_feature_showcase_pods` | repeater | — | rows: min 1, max 8; value = row count (int); rows serialize flat as `pods_{i}_{subfield}` |
| pods → `icon_name` | `field_feature_showcase_pod_icon` | text | Yes | serialized key: `pods_{i}_icon_name` |
| pods → `title` | `field_feature_showcase_pod_title` | text | Yes | serialized key: `pods_{i}_title` |
| pods → `body` | `field_feature_showcase_pod_body` | textarea | Yes | serialized key: `pods_{i}_body` |
| pods → `button_text` | `field_feature_showcase_pod_button_text` | text | — | serialized key: `pods_{i}_button_text` |
| pods → `button_url` | `field_feature_showcase_pod_button_url` | url | — | serialized key: `pods_{i}_button_url` |

### framed-callout

**Framed Callout** (`classic-city-core/framed-callout`) — Full-width hero / CTA section. Outer container holds a uniform padding 'frame' on all four sides; the inner panel inside that frame carries the bg color/gradient (Gutenberg native picker), an optional bg image, and a constrained-narrow InnerBlocks stack (eyebrow + h1 + paragraph + CTA button) vertically centered and left- or center-aligned.

- Kind: Hybrid (InnerBlocks)
- view.js: no

**Purpose:** A gallery-matted hero/CTA — the colored panel sits inside a visible frame of page background, giving a premium inset look at up to full viewport height.

**Content shape:** All copy is InnerBlocks, seeded as eyebrow + h1 + large paragraph + CTA button — keep roughly that stack. ACF: `alignment` (left | center, default center), `height` (auto | 50–100vh minimums), optional `bg_image` (cover, inside the panel; demo fallback on /style-guide) + `bg_opacity` (default 100). Panel color/gradient from the native picker — render strips it off the outer frame so only the inner panel paints (rule 16 handled internally).

**Use when:**
- The page opener (or closing pitch) wants a full-height statement but the design language is "framed/inset," not edge-bleed.
- Source hero copy is a single centered stack — eyebrow, big headline, a line or two, one button.
- You want a photo behind hero copy WITH the page background still visible around it.

**Avoid when:**
- The image should bleed to the viewport edges — use `hero-full-image` (full-bleed photo) or `hero-gradient` (photo + palette wash).
- Content is a heading + body + image side-by-side — use `hero` or `image-hero-50-50`.
- It's a mid-page CTA band on the normal rhythm — use `cta-large`; the frame treatment reads as a page-level moment.

**Pairs with:** highlight-tiles, portfolio-gallery, logo-strip; works as either the top hero or a dramatic closer.

**Core alternative:** A core cover block approximates the panel but loses the frame, min-height presets, and opacity-over-color layering — prefer this block for any framed treatment.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `alignment` | `field_framed_callout_alignment` | select | — | choices: `left` / `center`; default: `center` |
| `height` | `field_framed_callout_height` | select | — | choices: `auto` / `50` / `60` / `70` / `80` / `90` / `100`; default: `auto` |
| `bg_image` | `field_framed_callout_bg_image` | image | — | return_format: `array` |
| `bg_opacity` | `field_framed_callout_bg_opacity` | range | — | default: `100` |

### hero

**Hero** (`classic-city-core/hero`) — Headline + body + buttons on one side, image on the other. Mobile: image below content.

- Kind: Hybrid (InnerBlocks)
- view.js: no
- Emits `.sg-hero` marker

**Purpose:** The default page-top hero — headline, body, and buttons on one side, a photo on the other; the safest opener when in doubt.

**Content shape:** One required `image` (side photo, ~960×720 crop; demo fallback on /style-guide only) + `image_side` (right default | left). All copy is InnerBlocks: typically h1 + 1–2 sentence paragraph + buttons (main CTA gets the CTA color, second button outline — rule 6). Mobile stacks image below content. No background support.

**Use when:**
- Source page opens with headline + intro copy + CTA and has one decent supporting photo that can sit BESIDE the text (not behind it).
- The photo matters as content (product, place, people) and shouldn't be dimmed or overlaid.
- You need the most conservative hero — no color band, sits on the page background.

**Choosing among hero variants:** photo beside text → this block; text OVER a full-bleed photo → `hero-full-image`; palette-washed photo + gradient → `hero-gradient`; half photo / half colored panel → `image-hero-50-50`; field-driven 2-col intro row + wide image BELOW → `hero-3-up`; framed inset panel → `framed-callout`.

**Avoid when:**
- No usable image exists — don't rely on the demo placeholder in production; use a core group with h1 + paragraph + buttons.
- The image is a wide landscape that would crop badly in a half column — use `hero-3-up` (image renders full-width below) or `hero-full-image`.

**Pairs with:** logo-strip, stats, feature-grid, icon-feature-row — the style guide leads every page flow with a hero variant.

**Core alternative:** Core columns (text | image) for a hero-less interior page top; use this block whenever it's a true page hero (it emits the `sg-hero` marker).

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `image` | `field_hero_image` | image | Yes | return_format: `array` |
| `image_side` | `field_hero_image_side` | button_group | — | choices: `right` / `left`; default: `right` |

### hero-3-up

**Hero 3 Up** (`classic-city-core/hero-3-up`) — Hero with a 2-column intro row (eyebrow + heading on the left, description + button on the right) and a hero image below. Fully field-driven; no Inner Blocks. The wrapper carries .sg-hero so it self-identifies as the page's top section.

- Kind: Field-driven
- view.js: no
- Emits `.sg-hero` marker

**Purpose:** Editorial page-top hero — eyebrow + h1 left (2/3), description + button right (1/3), then a wide banner image below the intro row.

**Content shape:** Fully field-driven, no InnerBlocks. Required: `title` (renders as h1) and `image` (wide banner, ~1440×600; demo fallback on /style-guide only). Optional: `eyebrow` (small caps), `description` (2–4 sentences), `button_text`+`button_url` (renders as a CTA-colored button), `image_aspect_ratio` (none | 16/9 | 3/2 | 4/3 | 1/1 | 4/5 | 3/4). Mobile stacks to one column.

**Use when:**
- Source hero has a long/wide landscape photo that deserves full content width rather than a half-column crop.
- Hero copy splits naturally: big claim + a meatier supporting paragraph (the 1/3 column holds more prose than most heroes).
- You want a structured, no-editor-freedom hero — all content maps 1:1 to fields (good for scripted imports).

**Choosing among hero variants:** image full-width BELOW the copy → this block; image beside copy → `hero`; copy over a full-bleed image → `hero-full-image`; palette-washed image behind copy → `hero-gradient`.

**Avoid when:**
- The button URL doesn't exist yet — button only renders with both text and URL; fine to omit, never fake `#` (rule 11).
- Hero needs multiple buttons or custom inner content — use `hero` or `hero-gradient` (InnerBlocks variants).

**Pairs with:** feature-detail, icon-feature-row, logo-strip, stats — an editorial opener before feature sections.

**Core alternative:** Rule-21 two-column header (heading left, button right) + a core full-width image approximates it, but loses the 2/3–1/3 tuning and `sg-hero` marker — prefer the block for page tops.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `title` | `field_hero_3_up_title` | text | Yes |  |
| `eyebrow` | `field_hero_3_up_eyebrow` | text | — |  |
| `description` | `field_hero_3_up_description` | textarea | — |  |
| `button_text` | `field_hero_3_up_button_text` | text | — |  |
| `button_url` | `field_hero_3_up_button_url` | url | — |  |
| `image_aspect_ratio` | `field_hero_3_up_image_aspect_ratio` | select | — | choices: `none` / `16/9` / `3/2` / `4/3` / `1/1` / `4/5` / `3/4`; default: `none` |
| `image` | `field_hero_3_up_image` | image | Yes | return_format: `array` |

### hero-full-image

**Hero: Full Image** (`classic-city-core/hero-full-image`) — Full-width hero with a headline over a full-bleed background image, plus a card for body/button on top.

- Kind: Hybrid (InnerBlocks)
- view.js: no
- Emits `.sg-hero` marker

**Purpose:** Cinematic page-top hero — a big headline over a full-bleed background photo (or looping video), with supporting copy/buttons in a card on top.

**Content shape:** Required: `image` (full-bleed bg, ~1440×720+; also the video poster; demo fallback on /style-guide only) and `title_html` (h1; inline `<span>`/`<em>`/`<strong>` allowed to style words). Optional: `video` (mp4/webm — muted/looped, takes precedence over image), `gradient_color` (palette slug for the bottom fade; default fades to page bg), `card_width` (narrow | content). Body + buttons are InnerBlocks inside the overlay card.

**Use when:**
- Source has one strong, wide, atmospheric photo (or a background video) that should own the whole viewport width behind the headline.
- The brand moment is image-first: short punchy headline, minimal supporting copy in the card.
- A background video hero was requested — this is the only hero variant with video support.

**Choosing among hero variants:** text OVER a full-bleed photo → this block; photo washed with a brand color for text legibility → `hero-gradient`; photo beside text → `hero`; wide photo below the copy → `hero-3-up`; inset/framed panel → `framed-callout`.

**Avoid when:**
- The photo is busy and text legibility depends on dimming it — use `hero-gradient` (70% image over palette color).
- No high-quality wide image exists — a cropped-up small photo ruins this; fall back to `hero` or a core group.

**Pairs with:** logo-strip, stats, icon-feature-row, feature-grid; the bottom gradient fades into the next section's background — match `gradient_color` to it.

**Core alternative:** Core cover block gives text-over-image but without the headline/card split, video-with-poster handling, or palette bottom fade — use this block for real hero moments.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `image` | `field_hero_full_image` | image | Yes | return_format: `array` |
| `video` | `field_hero_full_video` | file | — | return_format: `array` |
| `title_html` | `field_hero_full_title_html` | textarea | Yes |  |
| `gradient_color` | `field_hero_full_gradient_color` | select | — | choices: palette slugs (dynamic, per child theme) |
| `card_width` | `field_hero_full_card_width` | button_group | — | choices: `narrow` / `content`; default: `narrow` |

### hero-gradient

**Hero — Gradient** (`classic-city-core/hero-gradient`) — Full-width hero. Background image dimmed to 70% so the selected palette background-color shows through, plus a directional gradient (transparent top-right → palette slug bottom-left). Entire content stack is InnerBlocks — eyebrow + h1 + paragraph + CTA button.

- Kind: Hybrid (InnerBlocks)
- view.js: no
- Emits `.sg-hero` marker

**Purpose:** Brand-saturated page-top hero — photo dimmed to 70% over a palette color plus a bottom-left gradient, so copy always reads and the brand color dominates.

**Content shape:** Fields: `bg_color` (palette slug, default primary — drives the wash, the gradient, AND the auto text-color cascade, rule 7), `bg_image` (photo; demo fallback on /style-guide only), `content_width` (content | narrow), `content_alignment` (left default | center). All copy is InnerBlocks — canonical stack: eyebrow paragraph + h1 + body paragraph + button(s); text sits bottom-left where the gradient is strongest.

**Use when:**
- The available hero photo is mediocre or busy — the 70% wash + gradient makes ANY image legible and on-brand.
- The page should open in the brand's contrast color (rule 14 — match the page's single contrast slug).
- Source hero copy is a standard eyebrow/headline/paragraph/CTA stack with left-aligned text.

**Choosing among hero variants:** brand-color wash over the photo → this block; photo shown at full strength → `hero-full-image`; photo beside copy → `hero`; photo below a 2-col intro → `hero-3-up`; half photo/half color panel → `image-hero-50-50`.

**Avoid when:**
- The photo itself is the selling point and shouldn't be tinted — use `hero-full-image` or `hero`.
- The section isn't the page top — for mid-page color bands use `cta-large`; this emits the `sg-hero` marker.

**Pairs with:** logo-strip, feature-grid, stats, highlight-tiles; follow with a neutral section, not another heavy color band (rule 15).

**Core alternative:** Core cover with a duotone/overlay comes close but loses the palette pair-helper text cascade and the locked directional gradient — use this block.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `bg_color` | `field_hero_gradient_bg_color` | select | — | choices: palette slugs (dynamic, per child theme); default: `primary` |
| `bg_image` | `field_hero_gradient_bg_image` | image | — | return_format: `array` |
| `content_width` | `field_hero_gradient_content_width` | select | — | choices: `content` / `narrow`; default: `content` |
| `content_alignment` | `field_hero_gradient_content_alignment` | select | — | choices: `left` / `center`; default: `left` |

### highlight-tiles

**Highlight Tiles** (`classic-city-core/highlight-tiles`) — Marketing tile grid — asymmetric bento of headline-led cards over a 3fr/5fr/4fr column system × 3 rows, with per-column tall+short tile pairings. 6-tile uses all three columns; 4-tile drops the rightmost. Per-card palette color and optional image with Background (full-bleed cover + scrim) or Inline (centered, constrained-width) treatment.

- Kind: Field-driven
- view.js: no

**Purpose:** A bento wall of short brag-lines — 4 or 6 asymmetric colored tiles, each a punchy claim with an optional image, for "why us" marketing pitch moments.

**Content shape:** `card_count` (4 = 2×2 | 6 = 3×2, default 6) — supply EXACTLY that many rows (`cards` repeater caps at 6; extras ignored, missing tiles leave holes). Per tile: `headline` (textarea — author line breaks preserved; think "Backed by 40+ peer-reviewed studies"), `bg_color` (palette slug per tile, default panel), optional `image` with `image_treatment` (background = cover + scrim | inline = centered illustration). Text-only tiles are fine. Mobile stacks.

**Use when:**
- Source has 4 or 6 short, parallel value claims (stats, credentials, differentiators) each expressible in ≤2 lines — no body copy per item.
- The page needs a high-energy visual break where mixed tile sizes/colors ARE the design.
- Some claims have images and some don't — the block handles mixed tiles gracefully.

**Avoid when:**
- You have 3, 5, or 7+ items — the layout only knows 4 or 6; use `feature-grid` or `image-card-grid` (rule 19 spirit).
- Items need descriptions or links — tiles are headline-only and unlinked; use `image-link-cards` (linked) or `image-card-grid` (described).
- Claims are icon-shaped features — use `feature-grid` (rule 20).

**Pairs with:** detail-cards, framed-callout, stats; style guide runs detail-cards → highlight-tiles → framed-callout. Mind per-tile colors against rule 14's one-contrast-color-per-page.

**Core alternative:** None — the asymmetric 3fr/5fr/4fr grid and scrim system are bespoke. If content doesn't fit 4/6 punchy lines, pick a grid block instead.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `card_count` | `field_highlight_tiles_card_count` | select | — | choices: `4` / `6`; default: `6` |
| `cards` | `field_highlight_tiles_cards` | repeater | — | rows: min 1, max 6; value = row count (int); rows serialize flat as `cards_{i}_{subfield}` |
| cards → `headline` | `field_highlight_tiles_card_headline` | textarea | — | serialized key: `cards_{i}_headline` |
| cards → `bg_color` | `field_highlight_tiles_card_bg_color` | select | — | choices: palette slugs (dynamic, per child theme); default: `panel`; serialized key: `cards_{i}_bg_color` |
| cards → `image_treatment` | `field_highlight_tiles_card_image_treatment` | select | — | choices: `background` / `inline`; default: `background`; serialized key: `cards_{i}_image_treatment` |
| cards → `image` | `field_highlight_tiles_card_image` | image | — | return_format: `array`; serialized key: `cards_{i}_image` |

### highlighted-image-gallery

**Highlighted Image Gallery** (`classic-city-core/highlighted-image-gallery`) — Featured image above a thumbnail row. Click any thumbnail to make it the primary image. Default 5 columns of thumbnails; configurable per instance. Uses the site default border-radius and block gap.

- Kind: Field-driven
- view.js: yes

**Purpose:** Product-page style gallery — one large featured image with a clickable thumbnail row that swaps images into the primary slot.

**Content shape:** One required `images` gallery field (min 1, no max; first image = initial primary; single image renders with no thumb row) and `columns` (thumbnails per row, 2–10, default 5). No copy fields at all. Demo images fall back on /style-guide only — production needs real uploads. Interactive (view.js) — thumbnails are buttons.

**Use when:**
- Source is a product/project detail page with 4–8 photos of ONE subject from different angles — the swap interaction implies "same thing, more views."
- The design wants one dominant image rather than an even grid.
- Roughly `columns + 1` images are available so the thumb row fills a clean single row (rule 19 spirit).

**Avoid when:**
- Images are of DIFFERENT subjects (portfolio, project roster) — use `portfolio-gallery` (lightbox grid) or `image-tiles` (linked tiles).
- Images need captions or titles — this block has none; `portfolio-gallery` carries title + caption.
- It's a decorative photo strip — use `image-wall` (scrolling rows) or a core gallery.

**Pairs with:** feature-detail, split-50-50, document-downloads, cta-thin — product-detail contexts; introduce with a core heading (rule 9).

**Core alternative:** Core gallery block when a static grid is fine and no primary/thumbnail interaction is wanted.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `images` | `field_highlighted_image_gallery_images` | gallery | Yes | return_format: `array` |
| `columns` | `field_highlighted_image_gallery_columns` | number | — | default: `5` |

### icon-feature-row

**Icon Feature Row** (`classic-city-core/icon-feature-row`) — Horizontal row of icon-topped features — tighter than Feature Grid.

- Kind: Field-driven
- view.js: no

**Purpose:** A compact strip of icon + heading (+ optional one-liner) items — quick trust/capability signals without the visual weight of full cards.

**Content shape:** `features` repeater, 2–8 (`desktop_columns` 2–6, default 4 — match the count or a divisor, rule 19). Per item: required FA `icon_name` (bare slug, e.g. `fa-star` — real glyphs only, rule 12), required `heading` (2–4 words), optional `body` (one short line; leave blank for icon + heading only). Items render inline (icon beside heading), divided by hairlines. Item background = one native-picker color for all.

**Use when:**
- Source has 3–6 terse selling points ("Free shipping", "10-year warranty", "Made in USA") — a phrase each, not a paragraph.
- You need a trust-bar directly under a hero, or a quick capability strip between heavier sections.
- Items are icon-able one-liners; if most need 2–3 sentences, that's `feature-grid` territory.

**Avoid when:**
- Copy per item exceeds a line or two — use `feature-grid` (icon-topped cards with room for body text).
- Items can't carry honest FontAwesome icons — rule 12; use a core list or columns instead.
- Items should link — use `link-pods` (rule 11).

**Pairs with:** hero variants (as the immediate trust bar below), process-steps, document-downloads, cta-thin; style guide runs icon-feature-row → process-steps.

**Core alternative:** A core columns row of bold paragraph + small paragraph — only when icons genuinely don't exist for the items; otherwise rule 20 favors this block.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `desktop_columns` | `field_icon_feature_row_desktop_columns` | select | — | choices: `2` / `3` / `4` / `5` / `6`; default: `4` |
| `features` | `field_icon_feature_row_items` | repeater | — | rows: min 2, max 8; value = row count (int); rows serialize flat as `features_{i}_{subfield}` |
| features → `icon_name` | `field_icon_feature_row_icon` | text | Yes | serialized key: `features_{i}_icon_name` |
| features → `heading` | `field_icon_feature_row_heading` | text | Yes | serialized key: `features_{i}_heading` |
| features → `body` | `field_icon_feature_row_body` | textarea | — | serialized key: `features_{i}_body` |

### image-card-grid

**Image Card Grid** (`classic-city-core/image-card-grid`) — Grid of image cards using the shared image-card partial. Configurable columns, mobile layout (stack or horizontal scroll), and image aspect ratio. Each card has a tag, title, image, description, optional footer title + subtitle (e.g., name + location for testimonials), and CTA button.

- Kind: Field-driven
- view.js: no

**Purpose:** The general-purpose card grid — image on top, tag/title/description below — for collections, services, testimonials-with-photos, or any titled visual list.

**Content shape:** `cards` repeater 1–24 (`column_count` 1–4 — fit the count, rule 19). Per card, all optional: `image` (demo fallback on /style-guide only), `tag` (small-caps eyebrow), `title`, `description` (2–3 sentences), `footer_title` + `footer_subtitle` (e.g. name + location), `button_text` + `button_link` (per-card CTA). Block-level: `mobile_layout` (stack | scroll), `image_aspect_ratio` (6 ratios, default 16/9), `card_bg_color` (palette slug for every card, default panel).

**Use when:**
- Source lists 2–12 parallel items each with a photo AND real descriptive copy — collections, case studies, team, locations.
- Items optionally need individual CTAs — only fill `button_*` with real destinations (rule 11); cards render fine without buttons.
- Attributed quotes with photos where the CPT-driven `testimonial-cards` isn't set up — footer_title/subtitle carry name + location.

**Avoid when:**
- Every card is a navigation link and the whole card should be clickable — use `image-link-cards` or `image-tiles` (rule 11).
- Items are icon-shaped features without photos — use `feature-grid` (rule 20).
- You only have 3 items with heavy uniform CTAs — `image-columns` (3-up, full-width buttons) is the tighter fit.

**Pairs with:** hero variants, swatch-explorer, detail-cards, cta-thin; lead with a core-group section header (rule 9).

**Core alternative:** Core columns of image + heading + paragraph when there are exactly 2–3 one-off items and no tag/footer/aspect control is needed.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `column_count` | `field_image_card_grid_column_count` | select | — | choices: `1` / `2` / `3` / `4`; default: `3` |
| `mobile_layout` | `field_image_card_grid_mobile_layout` | select | — | choices: `stack` / `scroll`; default: `stack` |
| `image_aspect_ratio` | `field_image_card_grid_image_aspect_ratio` | select | — | choices: `16/9` / `3/2` / `4/3` / `1/1` / `4/5` / `3/4`; default: `16/9` |
| `card_bg_color` | `field_image_card_grid_card_bg_color` | select | — | choices: palette slugs (dynamic, per child theme); default: `panel` |
| `cards` | `field_image_card_grid_cards` | repeater | — | rows: min 1, max 24; value = row count (int); rows serialize flat as `cards_{i}_{subfield}` |
| cards → `title` | `field_image_card_grid_card_title` | text | — | serialized key: `cards_{i}_title` |
| cards → `tag` | `field_image_card_grid_card_tag` | text | — | serialized key: `cards_{i}_tag` |
| cards → `image` | `field_image_card_grid_card_image` | image | — | return_format: `array`; serialized key: `cards_{i}_image` |
| cards → `description` | `field_image_card_grid_card_description` | textarea | — | serialized key: `cards_{i}_description` |
| cards → `footer_title` | `field_image_card_grid_card_footer_title` | text | — | serialized key: `cards_{i}_footer_title` |
| cards → `footer_subtitle` | `field_image_card_grid_card_footer_subtitle` | text | — | serialized key: `cards_{i}_footer_subtitle` |
| cards → `button_text` | `field_image_card_grid_card_button_text` | text | — | serialized key: `cards_{i}_button_text` |
| cards → `button_link` | `field_image_card_grid_card_button_link` | url | — | serialized key: `cards_{i}_button_link` |

### image-columns

**Image Columns** (`classic-city-core/image-columns`) — Card grid with 2-5 desktop columns. Each card has an image, heading, body, and a full-width CTA button.

- Kind: Field-driven
- view.js: no

**Purpose:** Presents 2–5 parallel offerings (services, product lines, benefits) as equal-weight cards, each with an image, a short pitch, and its own optional button.

**Content shape:** Repeater 1–12 items (render clamps grid to 2–5 columns; default 3 — pick a column count that fits the item count, rule 19). Per item: image (required in editor, resolves to demo placeholder if missing), heading (short, ~2–6 words), body (2–3 sentences), optional button label + URL (button renders only when BOTH are present). Block-level: aspect ratio (horizontal/square/vertical), one button color for all cards, card-body background via native picker.

**Use when:**
- Source page presents 3–4 services/products/benefits side by side, each with an image AND a paragraph of copy.
- Items have individual "learn more" destinations but the copy (not the link) is the point — this is a content grid, not a nav grid.
- You need per-card buttons plus real body text; feature-grid covers icon-only items, image-tiles covers image-only ones.

**Avoid when:**
- Items are navigation (title + destination, thin copy) — use `image-link-cards` (whole-card link, icon footer) or `link-pods` (no dominant image).
- Cards should link from title/footer with a category eyebrow, blog-card style — use `image-card-grid`.
- Items have no images — use `feature-grid` or `icon-feature-row` with real FontAwesome icons (rules 12, 20).
- No real per-card URLs exist: omit the button fields rather than shipping `#` buttons (spirit of rule 11).

**Pairs with:** stats, feature-grid, split-50-50, cta-thin (style-guide neighbors: heading intro above, stats band below).

**Core alternative:** For 2–3 items that are mostly prose with no card treatment, plain core columns with heading + paragraph + button per column (rules 1, 21) reads lighter than forcing cards.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `desktop_columns` | `field_image_columns_desktop_columns` | select | — | choices: `2` / `3` / `4` / `5`; default: `3` |
| `aspect_ratio` | `field_image_columns_aspect_ratio` | button_group | — | choices: `horizontal` / `square` / `vertical`; default: `horizontal` |
| `cta_color` | `field_image_columns_cta_color` | select | — | choices: palette slugs (dynamic, per child theme); default: `cta` |
| `columns` | `field_image_columns_items` | repeater | — | rows: min 1, max 12; value = row count (int); rows serialize flat as `columns_{i}_{subfield}` |
| columns → `image` | `field_image_columns_image` | image | Yes | return_format: `array`; serialized key: `columns_{i}_image` |
| columns → `heading` | `field_image_columns_heading` | text | Yes | serialized key: `columns_{i}_heading` |
| columns → `body` | `field_image_columns_body` | textarea | Yes | serialized key: `columns_{i}_body` |
| columns → `cta_text` | `field_image_columns_cta_text` | text | — | serialized key: `columns_{i}_cta_text` |
| columns → `cta_url` | `field_image_columns_cta_url` | url | — | serialized key: `columns_{i}_cta_url` |

### image-hero-50-50

**Image Hero 50/50** (`classic-city-core/image-hero-50-50`) — Full-bleed hero with an image on one side and a branded color/gradient panel on the other.

- Kind: Hybrid (InnerBlocks)
- view.js: no
- Emits `.sg-hero` marker

**Purpose:** Full-bleed page-top hero: media (photo or looping video) fills one half of the viewport, a branded color/gradient panel with the page's opening pitch fills the other.

**Content shape:** One image (required; demo placeholder if missing), optional video file (mp4/webm/mov — takes precedence, image becomes poster). Content side is free-form InnerBlocks — the standard fill is eyebrow + h1 + paragraph + buttons. Toggles: media side (left/right), media spacing (padded panel + shadow vs edge-to-edge), full viewport height (default on), background texture. Panel color comes from the native background picker — set one; an uncolored panel defeats the block. Forced align full; emits the `sg-hero` marker.

**Use when:**
- The chunk is the FIRST section of a page and has one strong photo/video plus headline-level copy.
- Source hero splits roughly half media, half message (as opposed to text overlaid on a photo).
- The client has hero-quality video — this is the primary video-hero block (with split-50-50).

**Avoid when:**
- The same image+content split appears mid-page — use `split-50-50` (wide/normal align, sized to content, panel-surface content side).
- The hero is a single full-width photo with text on top — use `hero-full-image`; text on a gradient with no photo — use `hero-gradient`; image beside text inside the content column — use `hero`.
- The section is a structured product panel (caption card, detail list) — use `feature-detail`.

**Pairs with:** logo-strip or stats directly below (proof under the hero); cta-large as the page's closing echo.

**Core alternative:** None for a true hero — heroes always come from the hero family. If the page opener is text-only, `hero-gradient` or a heading + paragraph on the canvas beats faking it with core columns.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `image` | `field_image_hero_image` | image | Yes | return_format: `array` |
| `video` | `field_image_hero_video` | file | — | return_format: `array` |
| `image_side` | `field_image_hero_image_side` | button_group | — | choices: `left` / `right`; default: `left` |
| `media_spacing` | `field_image_hero_media_spacing` | true_false | — | default: `1` |
| `full_height` | `field_image_hero_full_height` | true_false | — | default: `1` |
| `has_texture` | `field_image_hero_has_texture` | true_false | — | default: `0` |

### image-link-cards

**Image Links with Icons** (`classic-city-core/image-link-cards`) — Grid of clickable image cards. Each card has a fixed-aspect image on top and a palette-colored footer bar with a FontAwesome icon, title, and right-arrow affordance. Block-level eyebrow + header lead the grid.

- Kind: Field-driven
- view.js: no

**Purpose:** A navigation grid — square image cards, each fully clickable, with a colored footer bar (FontAwesome icon + title + arrow) telling the visitor "pick your path."

**Content shape:** Block-level eyebrow + h2 header (shared block-head partial, palette colors). Columns 1–6, default 4 — match to item count (rule 19). Cards repeater 1–24: title (required, 1–4 words), link URL (required — render SKIPS cards without one), image (required; 1:1 cover-crop; demo placeholder if missing), footer palette color (default primary), FontAwesome icon name. Footer text/icon auto-flip to the palette opposite.

**Use when:**
- Source lists category/audience/location entry points that each lead to their own page ("Mountains / Coastal / Suburbs…").
- Every item has a real destination URL AND a representative photo; copy per item is just a label.
- You can assign a genuine FontAwesome icon per card (rule 12) — the icon bar is the block's signature.

**Avoid when:**
- Items lack real URLs — rule 11: never ship `#`; use `image-columns` (non-linked cards with copy) or `image-tiles` (renders `<div>` tiles when unlinked).
- Items need body copy or per-item descriptions — footer bar holds a title only; use `image-card-grid` or `link-pods` (WYSIWYG description).
- No good square imagery — `link-pods` does the same nav job on color alone.
- No sensible icons exist — don't invent topical icon names; prefer `image-tiles` with links.

**Pairs with:** hero blocks above (it often IS the homepage router section), cta-thin below, link-pods (text-first sibling).

**Core alternative:** For 2–3 destinations without imagery, a two-column header row (rule 21) plus core buttons is honest and lighter than a card grid.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `eyebrow_text` | `field_image_link_cards_eyebrow_text` | text | — |  |
| `eyebrow_color` | `field_image_link_cards_eyebrow_color` | select | — | choices: palette slugs (dynamic, per child theme); default: `secondary` |
| `header_text` | `field_image_link_cards_header_text` | text | — |  |
| `header_color` | `field_image_link_cards_header_color` | select | — | choices: palette slugs (dynamic, per child theme); default: `primary` |
| `description` | `field_image_link_cards_description` | textarea | — |  |
| `columns` | `field_image_link_cards_columns` | select | — | choices: `1` / `2` / `3` / `4` / `5` / `6`; default: `4` |
| `cards` | `field_image_link_cards_items` | repeater | — | rows: min 1, max 24; value = row count (int); rows serialize flat as `cards_{i}_{subfield}` |
| cards → `title` | `field_image_link_cards_title` | text | Yes | serialized key: `cards_{i}_title` |
| cards → `bg_color` | `field_image_link_cards_bg_color` | select | — | choices: palette slugs (dynamic, per child theme); default: `primary`; serialized key: `cards_{i}_bg_color` |
| cards → `icon_name` | `field_image_link_cards_icon_name` | text | — | serialized key: `cards_{i}_icon_name` |
| cards → `link_url` | `field_image_link_cards_link_url` | url | Yes | serialized key: `cards_{i}_link_url` |
| cards → `image` | `field_image_link_cards_image` | image | Yes | return_format: `array`; serialized key: `cards_{i}_image` |

### image-overlay

**Image + Content Overlay** (`classic-city-core/image-overlay`) — Large image with a content card that overlaps it. Switch which side the content card sits on.

- Kind: Hybrid (InnerBlocks)
- view.js: no

**Purpose:** A large landscape photo anchors the section while a colored content card floats over one edge — an editorial "moment" that pulls the eye from imagery into a message or CTA.

**Content shape:** One image (required; demo placeholder if missing; landscape ~3:2 works best). Card side left/right (default right). Card content is free-form InnerBlocks — typical fill: eyebrow + heading + short paragraph + one button; keep it card-sized (heading + 2–3 sentences max). Card color from the native background picker (applied to the card, not the section — set one so the card reads as a card); optional card texture. Forced align full.

**Use when:**
- Source pairs one hero-quality photo with a short punchy message — an invitation, a single CTA, a brand statement.
- The page needs a mid-page visual break between denser sections (it sits between card grids and CTA bands on the style guide).
- The image matters as much as the words; a 50/50 split would dilute it.

**Avoid when:**
- Content is longer than a card's worth — use `split-50-50`, which gives copy a full half.
- It's the page opener — use `image-hero-50-50` or `hero-full-image` (heroes own the top slot).
- The message is CTA-only with no strong photo — use `cta-large` / `cta-thin` (image there is a low-opacity texture, rule 13).

**Pairs with:** center-content, cta-thin, portfolio-gallery — style guide runs it after the callout/gallery cluster as a closing visual beat.

**Core alternative:** Core cover block with a heading is flatter but acceptable for a quick banner; if the "overlay" in the source is really just text ON the image (not a card overhanging it), cover or hero-full-image is the truer match.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `image` | `field_overlay_image` | image | Yes | return_format: `array` |
| `content_side` | `field_overlay_content_side` | button_group | — | choices: `right` / `left`; default: `right` |
| `card_texture` | `field_overlay_texture` | select | — | choices: (empty) / `has-bg-texture` / `has-bg-texture-sand` |

### image-tiles

**Image Tiles** (`classic-city-core/image-tiles`) — Grid of image tiles with overlay blurbs and links.

- Kind: Field-driven
- view.js: no

**Purpose:** Photo-first tile row where each image carries a one-line blurb over a gradient — mood/category tiles that let photography do the talking.

**Content shape:** Tiles repeater 1–12. Per tile: image (required; demo placeholder if missing), blurb (required — ONE short line, e.g. "Crafted by hand"; it's an overlay, not a paragraph), optional link URL. With a link the tile renders as an `<a>`; without, it renders as a `<div>` (no dead anchors). Block-level: desktop columns 1–4 (default 4 — fit to count, rule 19), tile aspect vertical/horizontal, optional desktop carousel (mobile always scroll-snaps).

**Use when:**
- Source shows a strip of 3–4 lifestyle/category photos each captioned with a short phrase.
- Items either all link somewhere real, or are pure atmosphere (the block handles both — but don't mix `#` in; leave link empty instead, rule 11).
- Copy per item is a caption, not a description — if you're tempted to cram sentences into the blurb, it's the wrong block.

**Avoid when:**
- Items need heading + body copy or buttons — use `image-columns` (content cards) or `image-card-grid`.
- The grid is navigation with labeled destinations — use `image-link-cards` (icon footer bar reads as "click me" more strongly).
- Images are proof-of-work to browse in detail — use `portfolio-gallery` (lightbox) instead of hover blurbs.
- You want pure moving decoration with no text at all — use `image-wall`.

**Pairs with:** testimonial-cards, document-downloads, link-pods (style-guide neighbors); a heading intro above per rule 9.

**Core alternative:** Core gallery block for a plain static image set with visible captions; use it when there's no blurb-overlay intent at all.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `desktop_columns` | `field_image_tiles_desktop_columns` | select | — | choices: `1` / `2` / `3` / `4`; default: `4` |
| `desktop_carousel` | `field_image_tiles_desktop_carousel` | true_false | — | default: `0` |
| `aspect_ratio` | `field_image_tiles_aspect` | button_group | — | choices: `vertical` / `horizontal`; default: `vertical` |
| `tiles` | `field_image_tiles_items` | repeater | — | rows: min 1, max 12; value = row count (int); rows serialize flat as `tiles_{i}_{subfield}` |
| tiles → `image` | `field_image_tiles_image` | image | Yes | return_format: `array`; serialized key: `tiles_{i}_image` |
| tiles → `blurb` | `field_image_tiles_blurb` | textarea | Yes | serialized key: `tiles_{i}_blurb` |
| tiles → `link_url` | `field_image_tiles_link` | url | — | serialized key: `tiles_{i}_link_url` |

### image-wall

**Image Wall** (`classic-city-core/image-wall`) — Two infinite-scroll rows of images moving in opposite directions.

- Kind: Field-driven
- view.js: no

**Purpose:** Pure ambience: two full-width marquee rows of photos auto-scrolling in opposite directions — communicates volume and vibe ("look how much we've made"), not specific information.

**Content shape:** Images repeater, min 4 / max 24 (image only — no captions, no links, no text anywhere). Rows alternate by index: 1st/3rd/5th → top row, 2nd/4th/6th → bottom. 8–16 varied images is the sweet spot; each row's set is doubled for a seamless loop, so too few images makes the repetition obvious. Demo placeholders fill any missing image. Forced align full.

**Use when:**
- Source has a large pile of decent photos with no individual story — event shots, product spreads, portfolio overflow.
- The page needs an energetic visual divider between content sections or before the closing CTA.
- The message is abundance/credibility-by-volume rather than any single image.

**Avoid when:**
- Visitors should stop and examine images (titles, captions, lightbox) — use `portfolio-gallery`.
- Images represent categories or destinations — use `image-tiles` (linked) or `image-link-cards`.
- The images are client/partner logos — use `logo-strip` (its scroller mode is the logo version of this effect).
- Fewer than 4 usable images exist — the field minimum blocks it, and the loop would look broken anyway.

**Pairs with:** cta-large (style guide runs the wall right after the closing CTA), hero-full-image, center-content.

**Core alternative:** A core gallery if motion is unwanted or the images deserve individual attention; never rebuild the marquee with custom HTML (rule 3).

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `images` | `field_image_wall_images` | repeater | — | rows: min 4, max 24; value = row count (int); rows serialize flat as `images_{i}_{subfield}` |
| images → `image` | `field_image_wall_image` | image | Yes | return_format: `array`; serialized key: `images_{i}_image` |

### link-pods

**Link Pods** (`classic-city-core/link-pods`) — Grid of link pods. Each pod has a title, description, and a link — the entire pod is clickable. Pod-level background color and optional background image.

- Kind: Field-driven
- view.js: no

**Purpose:** Text-first navigation cards on brand color — each pod pitches a destination in a sentence or two and the whole card is the link ("Our story", "Find a dealer", "Get a quote").

**Content shape:** Columns 1–4 (default 3 — fit to item count, rule 19). Pods repeater 1–24. Per pod: title (required; title tag h2–h6, default h3), optional short description (basic WYSIWYG, 1–2 sentences), link text (required — renders with an arrow), link URL (required — render SKIPS pods without one), palette background color, optional background image behind the content. Text auto-flips against the pod color. No icons, no dominant imagery.

**Use when:**
- Source lists 3–4 pathways/next-steps where a sentence of context per item earns the click — richer than a button row, lighter than content cards.
- Every item has a REAL destination (rule 11 — this block is the named example; `#` is a dead anchor).
- No strong per-item photography exists — pods carry themselves on palette color alone.

**Avoid when:**
- Items have no destinations — use `detail-cards` or `feature-grid` for non-linked title+description sets (rule 20: give them real icons).
- Photography should lead — use `image-link-cards` (square image + icon footer) or linked `image-tiles`.
- There's only ONE next step — that's a `cta-thin` / `cta-large` band, not a one-pod grid.

**Pairs with:** process-steps above (steps → pathways), image-link-cards (image-first sibling), cta-thin.

**Core alternative:** For 2–3 destinations needing no description, core buttons under a heading (rules 6, 21) — a pod without a description is just a slow button.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `columns` | `field_link_pods_columns` | select | — | choices: `1` / `2` / `3` / `4`; default: `3` |
| `pods` | `field_link_pods_items` | repeater | — | rows: min 1, max 24; value = row count (int); rows serialize flat as `pods_{i}_{subfield}` |
| pods → `title` | `field_link_pods_title` | text | Yes | serialized key: `pods_{i}_title` |
| pods → `title_tag` | `field_link_pods_title_tag` | select | — | choices: `h2` / `h3` / `h4` / `h5` / `h6`; default: `h3`; serialized key: `pods_{i}_title_tag` |
| pods → `description` | `field_link_pods_description` | wysiwyg | — | serialized key: `pods_{i}_description` |
| pods → `link_text` | `field_link_pods_link_text` | text | Yes | serialized key: `pods_{i}_link_text` |
| pods → `link_url` | `field_link_pods_link_url` | url | Yes | serialized key: `pods_{i}_link_url` |
| pods → `bg_image` | `field_link_pods_bg_image` | image | — | return_format: `array`; serialized key: `pods_{i}_bg_image` |
| pods → `bg_color` | `field_link_pods_bg_color` | select | — | choices: palette slugs (dynamic, per child theme); serialized key: `pods_{i}_bg_color` |

### logo-strip

**Logo Strip** (`classic-city-core/logo-strip`) — Client/partner logos in two layouts: static grid (5-col desktop, 2-col mobile) or infinite marquee scroller with speed, direction, and pause-on-hover controls.

- Kind: Field-driven
- view.js: no

**Purpose:** Social proof at a glance — a row of client/partner/press logos saying "these people trust us" without a word of copy.

**Content shape:** Logos repeater, min 2 / max 20 (logo image only; demo placeholder if missing) — 5–10 is typical. Optional eyebrow line above ("Trusted by teams across Georgia"). Two layouts: static grid (default; wraps rows, 5-col desktop / 2-col mobile) or marquee scroller (single infinite row; speed slow/medium/fast, direction, pause-on-hover; honors prefers-reduced-motion). Two appearance toggles, **both off by default**: Light Plate Behind Logos and Desaturate Logos. Logos are not linked.

**Use when:**
- Source shows a "trusted by / as seen in / our partners" logo row — this is the only block for that job.
- You have 5+ logos and want motion — scroller mode; the marquee implies "too many to fit."
- A hero needs immediate credibility under it — logo strip is the classic under-hero proof band.

**Avoid when:**
- Fewer than ~4 logos — a sparse strip undermines the proof; fold the names into a paragraph or a testimonial instead.
- Each logo needs a caption, quote, or link — use `testimonial-cards` (attributed quotes) or `image-link-cards` (linked cards).
- The images aren't logos (photos, badges with copy) — use `image-tiles` or `image-wall`.

## Light Plate Behind Logos (`light_plate`, default OFF)

Paints a pale panel — white, via `--sg-logo-plate-bg` — behind the whole strip, with the shared 6px radius. Turn it on when the section behind the strip is dark. Leave it off on a light section, where it would draw a white box on a white page for no reason.

**One plate for the strip, not one per logo.** Logo files arrive as a mix: some transparent, some with the organisation's own flat white background baked into the PNG. Against a single white plate that baked-in background is invisible — the seam disappears. Per-logo plates would instead frame each of those baked-in white boxes inside a second box, and any tone mismatch between plate and artwork would show as a visible rectangle around some marks and not others. On trialport's Live Network page, six of fifteen marks ship with a baked-in white background; with the single plate you cannot tell which six.

The plate takes inline padding in grid mode only. In scroller mode the clip box *is* the padding box, so inline padding would not hold the marquee off the edge — it would only make the plate wider. Logos running edge to edge is correct there.

## Desaturate Logos (`grayscale`, default OFF)

Applies `filter: grayscale(1)` to the row. **Until 2026-08-21 this was unconditional CSS on `.sg-block-logos-row` with no way to switch it off.** It is a field now, and off by default, for two reasons.

**1. These are usually registered marks, and their colour is part of them.** Desaturating a third party's trademark is not a site-level styling decision to make in a stylesheet on their behalf. If a site wants the muted look, someone should have agreed to it; the toggle is where that agreement gets recorded.

**2. On a dark canvas it does not merely dim marks — it deletes parts of them.** Measured on trialport's canvas (`#1b1b23`) with the filter on: The Ehlers-Danlos Society came out at **1.11:1** and 100% of its ink under 3:1 — an empty space where a logo should be. Queen Mary measured **1.37:1** on the same basis.

**The durable lesson is the partial failures, not the total ones.** An invisible logo is a hole in the row; a reader sees a gap and moves on. A *half*-vanished logo is worse, because it still reads — as something else. Greyscaled on that canvas, Rare Revolution Magazine lost 43% of its ink and rendered as the single word "REVOLUTION". VWD Alliance lost 58%, dropping the word "Alliance". The Spark Global lost 64%, keeping the globe and losing the name. A logo strip on a page about trust that silently renames three of the organisations on it is a worse outcome than one that shows nothing at all. Whenever you turn greyscale on, check what survives, not just whether it looks tasteful.

**Greyscale and the light plate are independent.** Greyscale on a plated strip is legible; the plate is what fixes contrast, and it fixes it for every mark at once **without altering a single one**, which is the point. Reach for the plate first.

## Markup contract

```
.sg-block-logos[.--plated][.--grayscale]
  └ p.sg-block-eyebrow            (optional)
  └ .sg-block-logos-viewport      (always emitted)
      └ .sg-block-logos-row       (logos; emitted twice in scroller mode)
```

The viewport is always present and does two jobs: it is the scroller's `overflow: hidden` clip box, and it is the surface the plate paints on. The plate cannot live on `.sg-block-logos` (the eyebrow would land on the plate) nor on `.sg-block-logos-row` (the marquee translates that element, so its background would slide away with it).

**Child-theme hooks:** `--sg-logo-max-height`, `--sg-logo-gap`, `--sg-logo-scroll-dur`, `--sg-logo-plate-bg`, `--sg-logo-plate-pad`.

**Sizing caveat:** the strip caps every logo to one height (60px in scroller mode), so a wide wordmark reads large and a square mark reads small. That is normal for a logo strip, but with a very mixed set it can make the square marks look like afterthoughts. There is no per-logo scale field; if you need one, that is a block change, not a CSS override.

**Pairs with:** any hero directly above, testimonial-cards, stats — the proof cluster; give it a heading or eyebrow rather than an orphan band (rules 9, 10).

**Core alternative:** None worth it — core gallery/columns can't normalize logo sizing; if there are only 2–3 logos, mention the names in body copy instead.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `eyebrow` | `field_logo_strip_eyebrow` | text | — |  |
| `layout` | `field_logo_strip_layout` | button_group | — | choices: `grid` / `scroller`; default: `grid` |
| `scroll_speed` | `field_logo_strip_scroll_speed` | select | — | choices: `slow` / `medium` / `fast`; default: `medium` |
| `scroll_direction` | `field_logo_strip_scroll_direction` | button_group | — | choices: `left` / `right`; default: `left` |
| `pause_on_hover` | `field_logo_strip_pause_on_hover` | true_false | — | default: `1` |
| `light_plate` | `field_logo_strip_light_plate` | true_false | — | default: `0` |
| `grayscale` | `field_logo_strip_grayscale` | true_false | — | default: `0` |
| `logos` | `field_logo_strip_items` | repeater | — | rows: min 2, max 20; value = row count (int); rows serialize flat as `logos_{i}_{subfield}` |
| logos → `logo_image` | `field_logo_strip_image` | image | Yes | return_format: `array`; serialized key: `logos_{i}_logo_image` |

### portfolio-gallery

**Image Portfolio Gallery** (`classic-city-core/portfolio-gallery`) — Clickable portfolio grid with a lightbox.

- Kind: Field-driven
- view.js: no

**Purpose:** Browsable proof of work — a dense grid of project images that open in a lightbox with title and caption, for visitors who want to inspect, not just glance.

**Content shape:** Items repeater 1–48 (8–12 fills the grid nicely). Per item: image (required; demo placeholder if missing), title (required — shown on the tile and in the lightbox), optional caption (1–2 lines, lightbox only). Tiles are lightbox buttons, NOT links out — nothing here navigates to another page. Needs `assets/portfolio-lightbox.js` (auto-wired).

**Use when:**
- Source is a portfolio/gallery/our-work page section where individual images have names ("Coastal Retreat", "Modern Kitchen") and deserve a closer look.
- Images benefit from an enlarged view — detail shots, before/afters, finished installs.
- You have per-image titles; a title-less pile is better served elsewhere.

**Avoid when:**
- Each project should link to its own case-study page — use `image-link-cards` or linked `image-tiles` (rule 11 territory: this block cannot link out).
- Images are ambient decoration with no individual identity — use `image-wall`.
- One image should dominate with the rest as selectable thumbnails — use `highlighted-image-gallery`.
- Items need real body copy or CTAs — use `image-card-grid` / `image-columns`.

**Pairs with:** framed-callout or a heading intro above (rule 9), center-content, cta-large below — show the work, then ask for the job.

**Core alternative:** Core gallery block when a simple static grid with visible captions is enough and the lightbox/title treatment isn't wanted.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `items` | `field_portfolio_items` | repeater | — | rows: min 1, max 48; value = row count (int); rows serialize flat as `items_{i}_{subfield}` |
| items → `image` | `field_portfolio_image` | image | Yes | return_format: `array`; serialized key: `items_{i}_image` |
| items → `title` | `field_portfolio_title` | text | Yes | serialized key: `items_{i}_title` |
| items → `caption` | `field_portfolio_caption` | textarea | — | serialized key: `items_{i}_caption` |

### post-card

**Post Card** (`classic-city-core/post-card`) — Renders the current Query Loop post (featured image, category, title, excerpt, read-more) using the shared image-card markup. Built to drop into a Query Loop's Post Template — not for standalone use.

- Kind: Field-driven
- view.js: no

**Purpose:** Renders the CURRENT post inside a Query Loop as a standard image card (featured image, category eyebrow, linked title, excerpt, read-more) — the blog/news listing card.

**Content shape:** Takes NO content of its own — image, category, title, excerpt, and permalink all come from the post in the loop. It must sit inside a `core/post-template` (declared via `ancestor` in block.json); outside a loop it renders nothing. Authorable presentation only: image aspect ratio (six choices, default 16:9), show category toggle, show excerpt toggle, read-more label (always links to the post). Markup is byte-identical to an `image-card-grid` card via the shared `ccc_render_image_card()` partial.

**Use when:**
- Building a blog index, news section, or "latest posts" homepage strip — core Query Loop supplies the posts, this block styles each one.
- Source shows post listings styled like the site's other image cards — this keeps listing cards and hand-authored `image-card-grid` cards visually identical.
- Posts should surface automatically (newest first, by category, etc.) rather than being hand-picked.

**Avoid when:**
- Content is hand-authored, not posts — use `image-card-grid` (same card, editable fields).
- Items are pages/services/products without a post type behind them — again `image-card-grid` or `image-columns`.
- You're tempted to place it outside a Query Loop — it silently renders nothing; it is not a standalone card.

**Pairs with:** core/query + core/post-template (its required home), a two-column section header with a "View all" button above (rule 21).

**Core alternative:** Core post-title/post-excerpt/post-featured-image blocks inside the post template — acceptable but loses the card treatment and drifts from the site's card system.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `image_aspect_ratio` | `field_post_card_image_aspect_ratio` | select | — | choices: `16/9` / `3/2` / `4/3` / `1/1` / `4/5` / `3/4`; default: `16/9` |
| `read_more_text` | `field_post_card_read_more_text` | text | — | default: `Read more` |
| `show_category` | `field_post_card_show_category` | true_false | — | default: `1` |
| `show_excerpt` | `field_post_card_show_excerpt` | true_false | — | default: `1` |

### process-steps

**Process Steps** (`classic-city-core/process-steps`) — Numbered sequence of steps. Numbers auto-generate from item index.

- Kind: Field-driven
- view.js: no

**Purpose:** Shows "how it works" as a numbered sequence — step cards with auto-generated number badges that promise the visitor a clear, finite path.

**Content shape:** Steps repeater, min 2 / max 10. Per step: heading (required, 2–5 words) + body (required, 1–2 short sentences). Numbers come from item order — never write "Step 1" into the heading. Block-level: desktop columns 2–6 (default 5 — match the step count or a divisor, rule 19), number-circle palette color (default primary), card background via native picker (solid or gradient; one choice paints every card). No images, icons, or links.

**Use when:**
- Source lists sequential phases of a process — onboarding, build phases, "what happens next" (order matters; that's the tell vs. a feature list).
- 3–6 steps each explainable in a sentence or two.
- The section answers "what's it like to work with you?" — classic services-page material.

**Avoid when:**
- Items are unordered features/benefits — use `feature-grid` or `icon-feature-row` (rule 20; numbering unordered items implies false sequence).
- Each step needs an image, link, or long copy — use `image-columns` (cards with media/CTA) or stacked `split-50-50` sections for one-section-per-step depth.
- Steps are questions/answers — that's a FAQ; use the Yoast FAQ block (rule 24).

**Pairs with:** icon-feature-row above, link-pods or cta-thin below ("here's the process — start it"); heading intro belongs inside the same section (rule 9).

**Core alternative:** A core numbered list for a compact inline process mention inside prose; the block earns its keep only when the process IS the section.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `desktop_columns` | `field_process_steps_desktop_columns` | select | — | choices: `2` / `3` / `4` / `5` / `6`; default: `5` |
| `number_bg` | `field_process_steps_number_bg` | select | — | choices: palette slugs (dynamic, per child theme) |
| `steps` | `field_process_steps_items` | repeater | — | rows: min 2, max 10; value = row count (int); rows serialize flat as `steps_{i}_{subfield}` |
| steps → `heading` | `field_process_steps_heading` | text | Yes | serialized key: `steps_{i}_heading` |
| steps → `body` | `field_process_steps_body` | textarea | Yes | serialized key: `steps_{i}_body` |

### product-feature-toggles

**Product Feature Toggles** (`classic-city-core/product-feature-toggles`) — Tabbed feature switcher — a row of icon toggles drives a two-column panel showing a product image with caption (left) and headline + description + detail-feature list + CTA button (right). Each tab is its own product variant or category.

- Kind: Field-driven
- view.js: yes

**Purpose:** An interactive comparison of product variants/categories — a row of icon tabs swaps a two-column panel (image + caption card on the left, headline + description + spec rows + CTA on the right).

**Content shape:** Block-level eyebrow + h2 (shared block-head partial). Toggles repeater 1–8 (2–4 is the sweet spot; 1 defeats the tabs). Per toggle: label (required) + FontAwesome icon name (rule 12 — real icons only), image (demo fallback on /style-guide ONLY — production needs a real upload), image subtitle + title (floating caption card), headline, description (2–3 sentences), button text/URL, accent palette color, and a nested detail repeater 0–8 (label required + icon + 1–2 line description). Panel = the same shared partial as `feature-detail`.

**Use when:**
- Source presents 2–4 product lines/collections/tiers each with its own photo, pitch, and spec bullets ("Genesis Collection / Horizon Collection…").
- Content per variant is parallel in shape — tabs demand symmetry.
- Each variant has a real destination for its CTA button.

**Avoid when:**
- There's only ONE product/feature panel — use `feature-detail` (identical layout, no tabs).
- "Variants" are just a feature list, not switchable things — use `feature-grid` or `detail-cards`.
- No real FontAwesome icons fit the toggle labels (rule 12) or no per-variant imagery exists — tabs without icons/images fall flat; consider stacked `split-50-50` sections instead.
- Users must compare variants side by side — tabs hide the others; use `image-columns`.

**Pairs with:** image-link-cards above (category router → detail switcher), swatch-explorer (its visual-selection sibling), cta-thin below.

**Core alternative:** One `split-50-50` per variant stacked down the page — more scrolling, but honest when variants are few or asymmetric.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `eyebrow_text` | `field_product_feature_toggles_eyebrow_text` | text | — |  |
| `eyebrow_color` | `field_product_feature_toggles_eyebrow_color` | select | — | choices: palette slugs (dynamic, per child theme); default: `secondary` |
| `header_text` | `field_product_feature_toggles_header_text` | text | — |  |
| `header_color` | `field_product_feature_toggles_header_color` | select | — | choices: palette slugs (dynamic, per child theme); default: `primary` |
| `description` | `field_product_feature_toggles_description` | textarea | — |  |
| `toggles` | `field_product_feature_toggles_items` | repeater | — | rows: min 1, max 8; value = row count (int); rows serialize flat as `toggles_{i}_{subfield}` |
| toggles → `label` | `field_product_feature_toggles_label` | text | Yes | serialized key: `toggles_{i}_label` |
| toggles → `icon_name` | `field_product_feature_toggles_icon_name` | text | — | serialized key: `toggles_{i}_icon_name` |
| toggles → `left_image` | `field_product_feature_toggles_left_image` | image | — | return_format: `array`; serialized key: `toggles_{i}_left_image` |
| toggles → `left_image_subtitle` | `field_product_feature_toggles_left_image_subtitle` | text | — | serialized key: `toggles_{i}_left_image_subtitle` |
| toggles → `left_image_title` | `field_product_feature_toggles_left_image_title` | text | — | serialized key: `toggles_{i}_left_image_title` |
| toggles → `right_headline` | `field_product_feature_toggles_right_headline` | text | — | serialized key: `toggles_{i}_right_headline` |
| toggles → `right_description` | `field_product_feature_toggles_right_description` | textarea | — | serialized key: `toggles_{i}_right_description` |
| toggles → `right_button_text` | `field_product_feature_toggles_right_button_text` | text | — | default: `View Product`; serialized key: `toggles_{i}_right_button_text` |
| toggles → `right_button_url` | `field_product_feature_toggles_right_button_url` | url | — | serialized key: `toggles_{i}_right_button_url` |
| toggles → `right_detail_color` | `field_product_feature_toggles_right_detail_color` | select | — | choices: palette slugs (dynamic, per child theme); default: `secondary`; serialized key: `toggles_{i}_right_detail_color` |
| toggles → `right_details` | `field_product_feature_toggles_right_details` | repeater | — | rows: min 0, max 8; value = row count (int); rows serialize flat as `toggles_{i}_right_details_{i}_{subfield}` |
| toggles → right_details → `label` | `field_product_feature_toggles_detail_label` | text | Yes | serialized key: `toggles_{i}_right_details_{i}_label` |
| toggles → right_details → `icon_name` | `field_product_feature_toggles_detail_icon_name` | text | — | serialized key: `toggles_{i}_right_details_{i}_icon_name` |
| toggles → right_details → `description` | `field_product_feature_toggles_detail_description` | textarea | — | serialized key: `toggles_{i}_right_details_{i}_description` |

### proof-band

**Proof Band** (`classic-city-core/proof-band`) — A self-contained evidence band: an uppercase kicker, 2–4 headline stats whose values cycle through three accent slots, a pull-quote behind a left rule, and one closing link. Everything sits on a single rounded surface painted by the block-level color picker; the card border auto-derives to that color's palette partner (its “alt”).

- Kind: Field-driven
- view.js: no

**Purpose:** The page's proof beat, delivered in one breath — where the evidence came from, two to four figures that carry it, the sentence a human said about it, and the one link to the full story. It exists because that sequence keeps getting rebuilt out of a stats band, a quote block and a button that then drift apart on the page; here they are one surface with one background and one rhythm.

**Content shape:** One band holding `kicker` (2–6 words, uppercase, renders as `.is-style-eyebrow` — names the SOURCE, "From one recent study"), a `stats` repeater (2–4), `quote` + `quote_attribution`, and `link_text` + `link_url`. Per stat: `value` (required, TEXT — "183%", "73%", "~2 days" are all valid; it never wraps, so keep it short) and `label` (required — a full sentence of context, longer than the 2–4 words the `stats` block takes). Everything except the stats is optional; a band with neither stats nor a quote renders nothing at all.

The quote is a real `<blockquote>` + `<cite>` (rule 23 — this field is for words somebody said, not a mission statement). Quotation marks are added by the block; do not type them. The link renders as a standard theme button, so it inherits the site's button treatment.

Stat values cycle through three accent slots in author order — 1st and 4th take slot 1 (`cta` by default), 2nd takes slot 2, 3rd takes slot 3. That is why the repeater caps at 4: past it the cycle repeats without meaning and the quote stops reading as the payoff. Background color/gradient via the native picker; the band border auto-derives to that color's palette partner (`ink` → `ink-alt`), with gradients and the unpaired `canvas` / `panel` / `ink` / `ink-soft` slugs falling back to the border token. Retune via `--ccc-pb-accent-1/2/3`, `--ccc-pb-quote-rule-color` and `--ccc-pb-link-radius` (set it to a pill if the child wants one) rather than out-specifying selectors.

**Use when:**
- Source copy has a named evidence source, a handful of figures, AND a supporting quote that all belong to the same claim — the band is what keeps them attached.
- A long page needs one high-contrast moment of proof between explanatory sections (rule 15: give it neutral neighbors).
- The figures are non-uniform in kind — a percentage, a ratio and a duration side by side — which `chart-horizontal-bars` cannot take because it must sum them.

**Avoid when:**
- There is no quote and no link, just numbers — use `stats` (`is-style-pods` if there are five or six), which is the same figures without the ceremony.
- Each figure needs its own labeled ledger of sub-values — use `card-detail-rows`.
- The "quote" is really a mission, promise or guarantee — rule 23: give it a heading and a paragraph instead, and this block is the wrong shape.
- The band would sit directly against another colored band — rule 15.
- You have more than four figures, or you want two links. Both are signs the section is doing two jobs; split it.

**Pairs with:** `stat-comparison` (the single before→after figure that the band's percentages usually imply), `comparison-table`, `card-detail-rows` for the full case studies the link points at. Introduce it with a core-group section header — eyebrow + h2 (rules 9, 10); the band's own `kicker` is a source label, not the section's heading.

**Core alternative:** None. Core can stack a group, a stats-shaped columns row, a quote and a button, but the four end up as four sibling blocks an editor can reorder, unpaint or half-delete, and the accent cycle across the figures needs per-index color that core columns can only do as inline styles (rules 1, 4).

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `kicker` | `field_proof_band_kicker` | text | — |  |
| `stats` | `field_proof_band_stats` | repeater | — | rows: min 2, max 4; value = row count (int); rows serialize flat as `stats_{i}_{subfield}` |
| stats → `value` | `field_proof_band_stat_value` | text | Yes | serialized key: `stats_{i}_value` |
| stats → `label` | `field_proof_band_stat_label` | text | Yes | serialized key: `stats_{i}_label` |
| `quote` | `field_proof_band_quote` | textarea | — |  |
| `quote_attribution` | `field_proof_band_quote_attribution` | text | — |  |
| `link_text` | `field_proof_band_link_text` | text | — |  |
| `link_url` | `field_proof_band_link_url` | url | — |  |

### split-50-50

**Split 50/50** (`classic-city-core/split-50-50`) — Half image, half content. Free-form InnerBlocks on the content side.

- Kind: Hybrid (InnerBlocks)
- view.js: no

**Purpose:** The workhorse mid-page section: media on one half, free-form content on the other — one topic, one image, one message per instance.

**Content shape:** One image (required; demo placeholder if missing), optional video (mp4/webm/mov — takes precedence; image becomes poster). Media side left/right. Content side is InnerBlocks — canonical fill: eyebrow + h2 + paragraph + buttons. Content side takes the native background picker and DEFAULTS to the panel surface, so it reads card-like even unstyled (rule 16: set color on the block, don't wrap it). Optional texture. Align wide or full; sized to content, not viewport.

**Use when:**
- Source alternates image-beside-text sections down a page — the classic zig-zag; alternate the media side per instance.
- A topic has one supporting image and a paragraph-plus of copy with an optional CTA.
- Copy is free-form (any mix of core blocks) rather than a fixed spec-list shape.

**Avoid when:**
- It's the page's FIRST section at full viewport impact — use `image-hero-50-50` (full-bleed, 100vh, `sg-hero`).
- The section needs the structured product-panel shape (floating caption card, icon detail rows, spec list) — use `feature-detail`.
- The image should dominate with only a card of text floating over it — use `image-overlay`.
- There are 4+ short parallel items — that's a card grid (`image-columns`), not four splits.

**Pairs with:** stats, logo-strip, testimonial-cards between splits for rhythm; it is the canonical child of `stackable` (style guide nests three). Avoid two colored splits back-to-back (rule 15).

**Core alternative:** Core columns (image + heading/paragraph/buttons) when you want plain flow with no panel/card treatment — fine for text-lite pages; the block earns its keep via the media handling and panel styling.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `image` | `field_split_image` | image | Yes | return_format: `array` |
| `video` | `field_split_video` | file | — | return_format: `array` |
| `image_side` | `field_split_side` | button_group | — | choices: `left` / `right`; default: `left` |
| `has_texture` | `field_split_has_texture` | true_false | — | default: `0` |

### stackable

**Stackable** (`classic-city-core/stackable`) — Position: sticky wrapper. Drop multiple Stackables back-to-back and they stack on top of each other as the page scrolls (each later one covers earlier ones). InnerBlocks accept any content. Sticky offset defaults to spacing--40 below the viewport top — adjust per-instance if a navbar or other fixed chrome needs more clearance.

- Kind: Hybrid (InnerBlocks)
- view.js: no

**Purpose:** A scroll-theater wrapper: children pin at a sticky offset and each later one slides up to cover the last — turns a series of sections into a paced, one-at-a-time reveal.

**Content shape:** No content of its own — pure InnerBlocks wrapper plus ONE field: sticky offset (spacing slug 10–100, default 40; bump under tall navbars). Canonical use: ONE Stackable containing 3–4 children, most commonly `split-50-50`s with alternating background colors and media sides (the style-guide sample). Children MUST bring their own opaque backgrounds — a transparent child lets the covered section show through. Legacy sibling-Stackables still work via CSS `:has()` detection.

**Use when:**
- Source presents 3–4 parallel pitches meant to be absorbed one at a time (awareness → conversion → retention) and the page can afford a premium scroll moment.
- The children are uniform full-width sections (splits, feature panels) with solid backgrounds.
- The page is a marketing/landing page where scroll delight fits the brand.

**Avoid when:**
- Content should be scannable at a glance or compared side by side — use a card grid (`image-columns`, `detail-cards`); sticky stacking hides everything but the current panel.
- Only one section exists — a single-child Stackable is pointless overhead; place the section directly.
- Any ancestor uses `overflow: hidden/auto` or `transform` — sticky breaks (documented gotcha); verify before committing.

**Pairs with:** split-50-50 (its canonical children), a heading intro section above, cta-large after the stack resolves.

**Core alternative:** Just stack the sections normally — Stackable is presentation-only; if the scroll effect isn't clearly wanted from the source/design intent, plain flow is the safer import.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `sticky_offset` | `field_stackable_sticky_offset` | select | — | choices: `10` / `20` / `30` / `40` / `50` / `60` / `70` / `80` / `90` / `100`; default: `40` |

### stat-comparison

**Stat Comparison** (`classic-city-core/stat-comparison`) — One before → after figure set beside the sentence that explains it: “14.3% → 79.1%”, then a heading and a paragraph of context. The arrow is decorative — the pair is announced to screen readers as a single “from X to Y” label. Surface color comes from the block-level color picker; the border auto-derives to that color's palette partner (its “alt”).

- Kind: Field-driven
- view.js: no

**Purpose:** One number moved. "14.3% → 79.1%" set at display scale beside the sentence that says what it measures — the whole block is a single before/after claim, sized so a scanner takes the direction from the shape alone and a reader gets the caveat from the paragraph.

**Content shape:** `from_value` and `to_value` (both required, both TEXT — "14.3%", "5 min 15 sec", "2 in 10"; keep them short, they never wrap), `heading` (required, renders as an h3 — a full sentence is fine and usually better than a fragment), and `body` (WYSIWYG, optional — the context, and the honest place for "early data, small sample"). Both figures or nothing: half a comparison renders as no block.

The arrow is decorative and hidden from assistive tech; the figure is announced once as "{from} to {to}" via `role="img"` + `aria-label`, because screen readers announce a bare "→" inconsistently or not at all. There is deliberately no third value and no computed "% change" field — the two figures ARE the comparison, and a printed delta is one more number to keep in sync. Background color/gradient via the native picker; the panel border auto-derives to that color's palette partner, with gradients and the unpaired surface slugs falling back to the border token.

By default the "after" figure takes `cta` and the "before" steps back to a muted `currentColor` mix, so the improvement reads without knowing the palette. Swap them with `--ccc-sc-from-color` / `--ccc-sc-to-color`; resize with `--ccc-sc-figure-size`.

**Use when:**
- Source copy contains exactly one metric measured at two points — before/after a launch, this year against last, with the product against without it.
- The change is the argument, and the figures are in the same unit so the arrow between them is honest.
- The claim needs a sentence of qualification right next to it; the body field is what stops a bare number overclaiming.

**Avoid when:**
- There are several metrics, each with two sides — use `comparison-table`, which draws every pair as bars and keeps them on one axis.
- The two numbers are in different units, or one is a range. The arrow asserts they are comparable; if they are not, write a sentence.
- It is one figure with no "before" — use `stats` (rule 17: give it a background) or fold it into a heading.
- The figures partition a whole (sources, regions, budget lines) — use `chart-horizontal-bars`.

**Pairs with:** `proof-band` above it (the band's percentages usually imply exactly this movement), `comparison-table`, `card-detail-rows`. Two instances of this block on one page is a normal pattern — the same figure restated for a second audience — but give each its own section heading (rules 9, 10) so they don't read as a duplicate.

**Core alternative:** Core columns of a heading and a paragraph get close, but the figure is display type at a size no heading level provides without overriding the tag (rule 2), the from/arrow/to need three separately-colored runs inside one line, and the arrow needs hiding from assistive tech with a replacement label — none of which core markup can carry without inline styles or custom HTML (rule 1).

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `from_value` | `field_stat_comparison_from_value` | text | Yes |  |
| `to_value` | `field_stat_comparison_to_value` | text | Yes |  |
| `heading` | `field_stat_comparison_heading` | text | Yes |  |
| `body` | `field_stat_comparison_body` | wysiwyg | — |  |

### stats

**Stats** (`classic-city-core/stats`) — Row of big-number stats. Admin provides a repeater of value + label pairs; background color or gradient comes from the block's native color picker (optional).

- Kind: Field-driven
- view.js: no

**Purpose:** A big-number proof band — "12k+ projects, 98% satisfaction, 24/7 support" — credibility compressed into figures a visitor absorbs in two seconds.

**Content shape:** Stats repeater, min 2 / max 8 (3–4 is canonical). Per stat: value (required — a big number or SHORT token: "12k+", "98%", "24/7", "15yr") + label (required, 2–4 words). Desktop columns 2–6 (default 4) — match the stat count exactly (rule 19). Background color/gradient via the native picker; the style-guide sample uses a brand gradient. Rule 17: this block's card treatment REQUIRES a background — never leave it on bare canvas.

**Three treatments, one field set.** Pick with the Styles panel:

- **Default** — the shadowed card. The block paints its own surface, so it needs its own background color/gradient. Centered cells.
- **`is-style-pods`** — translucent bordered pods on an auto-fit grid: each figure gets its own outlined pod, the grid reflows by pod width instead of a column count, and values alternate between two accent slots. This is the style for **five or six figures**, where a fixed column count either rags or forces the author to work out a divisor. The pod fill and border are `currentColor` mixes, so one rule works on a dark band and a light one. Left-aligned, and the labels can run to a short sentence.
- **`is-style-strip`** — hairline-separated cells, no gaps: one rule between neighbors, one border around the set. The quietest option, and the one to reach for when the section already carries a colored band above or below it (rule 15). Left-aligned, no accent cycle.

Both alternative styles strip the default card's padding and shadow: the treatment lives on the CELLS and the band behind them belongs to the section. Rule 17 still applies — put them on a section background rather than bare canvas. Retune either through variables on `.sg-block-stats` (`--ccc-stats-accent-a` / `-b`, `--ccc-stats-cell-padding`, `--ccc-stats-cell-rule-color`, `--ccc-stats-pod-min`) rather than out-specifying the style class; every cell rule is unconditional and variable-driven for exactly that reason.

**Use when:**
- Source copy contains 3–4 quantifiable claims (years, counts, percentages) that can compress to value + label.
- The page needs a short proof beat between content sections — under a hero, between a split and a card grid.
- Numbers are real and specific — invented or vague stats ("many happy clients") read worse as big type.
- Five or six figures need to sit together without ragging — `is-style-pods`.
- A row of context figures has to sit under a heading without adding a second colored band — `is-style-strip`.

**Avoid when:**
- Claims need a sentence of explanation each — use `feature-grid` / `icon-feature-row` (rule 20) or `detail-cards`.
- Each figure needs its own labeled sub-ledger — use `card-detail-rows`.
- The figures come with a quote and a link that belong to the same claim — use `proof-band`, which keeps the three attached.
- Proof is testimonial-shaped (people said things) — use `testimonial-cards` or `logo-strip`.
- Fewer than 2 real numbers exist — fold the one number into a heading instead of a one-stat band.
- The adjacent section is already a colored band — rule 15: no two colored bands back-to-back; either move stats to where a neutral neighbor frames it, or use `is-style-strip`, which does not paint a band of its own.

**Pairs with:** image-columns, feature-grid, split-50-50 (style-guide neighbors); `proof-band` and `comparison-table` on evidence pages; any hero above. Give the band its own heading or attach it to the section it proves (rules 9, 10).

**Core alternative:** None good — headings faking big numbers in core columns lose the band treatment and violate rule 2 sizing overrides. If numbers are weak, write a sentence.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `desktop_columns` | `field_stats_desktop_columns` | select | — | choices: `2` / `3` / `4` / `5` / `6`; default: `4` |
| `stats` | `field_stats_items` | repeater | — | rows: min 2, max 8; value = row count (int); rows serialize flat as `stats_{i}_{subfield}` |
| stats → `value` | `field_stats_value` | text | Yes | serialized key: `stats_{i}_value` |
| stats → `label` | `field_stats_label` | text | Yes | serialized key: `stats_{i}_label` |

### swatch-explorer

**Swatch Explorer** (`classic-city-core/swatch-explorer`) — Full-width swatch showcase. A row of scenario toggles (e.g. time-of-day, lighting condition, product line) drives a panel with a big featured image and a horizontal scrollable row of selectable color/material swatches. Each swatch swaps the featured image when clicked.

- Kind: Field-driven
- view.js: yes

**Purpose:** An interactive color/material picker: scenario tabs (Morning / Overcast / Evening, or product lines) frame a big featured photo that swaps as the visitor clicks swatches in a scrollable row — "see it in YOUR color."

**Content shape:** Block-level eyebrow + h2 + description (shared block-head partial). Toggles repeater 1–6: label (required) + FontAwesome icon name. Nested swatches repeater per toggle, 1–24: swatch name (required), square swatch thumbnail, and the big image that swaps in when selected. Image fields aren't editor-required, BUT the demo fallback only fires on /style-guide — production content needs a real swatch image + big image per swatch or panels render empty. Forced align full; interactivity via view.js.

**Use when:**
- Source is a product configurator/visualizer: finishes, colors, or materials each with photography of the product in that option (decking, siding, countertops).
- The client supplied per-option photo pairs (thumbnail + large shot) — the block is only as good as its image library.
- Options group naturally into 2+ scenarios (lighting, collection, setting) — with one grouping, a single toggle row is fine but the tabs add little.

**Avoid when:**
- Variants differ by FEATURES/specs, not appearance — use `product-feature-toggles` (headline + detail rows + CTA per tab).
- You have one hero image plus alternates with no "option" semantics — use `highlighted-image-gallery` (same swap interaction, no swatch/scenario framing).
- Per-option photography doesn't exist — don't fake swatches; a `portfolio-gallery` of what does exist is honest.

**Pairs with:** product-feature-toggles (style-guide neighbor — features tabs, then colors), split-50-50 intro above, cta-large below ("found your color? get a quote").

**Core alternative:** None — this is irreducibly interactive. Without swap-worthy imagery, fall back to a core gallery under a heading.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `eyebrow_text` | `field_swatch_explorer_eyebrow_text` | text | — |  |
| `eyebrow_color` | `field_swatch_explorer_eyebrow_color` | select | — | choices: palette slugs (dynamic, per child theme); default: `secondary` |
| `header_text` | `field_swatch_explorer_header_text` | text | — |  |
| `header_color` | `field_swatch_explorer_header_color` | select | — | choices: palette slugs (dynamic, per child theme); default: `primary` |
| `description` | `field_swatch_explorer_description` | textarea | — |  |
| `toggles` | `field_swatch_explorer_toggles` | repeater | — | rows: min 1, max 6; value = row count (int); rows serialize flat as `toggles_{i}_{subfield}` |
| toggles → `label` | `field_swatch_explorer_toggle_label` | text | Yes | serialized key: `toggles_{i}_label` |
| toggles → `icon_name` | `field_swatch_explorer_toggle_icon_name` | text | — | serialized key: `toggles_{i}_icon_name` |
| toggles → `swatches` | `field_swatch_explorer_swatches` | repeater | — | rows: min 1, max 24; value = row count (int); rows serialize flat as `toggles_{i}_swatches_{i}_{subfield}` |
| toggles → swatches → `swatch_image` | `field_swatch_explorer_swatch_image` | image | — | return_format: `array`; serialized key: `toggles_{i}_swatches_{i}_swatch_image` |
| toggles → swatches → `swatch_name` | `field_swatch_explorer_swatch_name` | text | Yes | serialized key: `toggles_{i}_swatches_{i}_swatch_name` |
| toggles → swatches → `big_image` | `field_swatch_explorer_swatch_big_image` | image | — | return_format: `array`; serialized key: `toggles_{i}_swatches_{i}_big_image` |

### testimonial-cards

**Testimonial Cards** (`classic-city-core/testimonial-cards`) — Grid of testimonial cards pulled from the Testimonial CPT. Admin picks which testimonials, columns per desktop, and how they collapse on mobile.

- Kind: Field-driven
- view.js: no

**Purpose:** Multi-voice social proof — a grid of quote cards (quote glyph, quote text, name, title + company) showing that several real people vouch for the client.

**Content shape:** Pulls from the Testimonial CPT — the block itself stores only WHICH testimonials (post_object, multiple, required; selection order = display order), desktop columns 1–4 (default 3 — fit the count, rule 19), and mobile layout (`column-count` stack or `horizontal-scroll` carousel). Per-testimonial content (quote, company_name, job_title) lives on the CPT entry, so importing testimonials means CREATING CPT posts first, then selecting them here. Quotes of 2–4 sentences ballpark; wildly uneven lengths make ragged cards. Renders nothing until entries are selected.

**Use when:**
- Source page shows 2+ attributed customer quotes — names (ideally with role/company) attached to words they said.
- Testimonials will be reused across pages — the CPT makes them a shared library, not page-locked copy.
- Proof should feel like a chorus (several voices) rather than one big endorsement.

**Avoid when:**
- There's ONE standout quote — use core/quote with `is-style-quote` (the registered oversized pull-quote style, the style guide's "Large Testimonial").
- The "quotes" are actually mission/vision/guarantee statements — rule 23: labeled statements get a real heading + paragraph, not quote markup.
- Proof is logos or numbers, not words — use `logo-strip` or `stats`.

**Pairs with:** core/quote `is-style-quote` (its single-voice sibling directly above it on the style guide), image-tiles, logo-strip, cta-large.

**Core alternative:** core/quote (is-style-quote) for one testimonial; two or three core quotes stacked is acceptable on a text-light page, but loses the card grid and the reusable CPT library.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `testimonials` | `field_testimonial_cards_posts` | post_object | Yes |  |
| `desktop_columns` | `field_testimonial_cards_desktop_columns` | select | — | choices: `1` / `2` / `3` / `4`; default: `3` |
| `mobile_layout` | `field_testimonial_cards_mobile_layout` | select | — | choices: `column-count` / `horizontal-scroll`; default: `column-count` |

### text-callout

**Text Callout** (`classic-city-core/text-callout`) — A one-to-two sentence statement set large enough to stop a scroll. Renders a bare <div> wrapping the authored paragraph — no card, no background — so each child theme owns the look: style `p > strong` for a branded emphasis treatment, and use the div for ::before / ::after flourishes. Width is an ACF field (narrow / content / wide / full), and the block reserves spacing-60 above itself AND above whatever follows.

- Kind: Field-driven
- view.js: no

**Purpose:** The scroll-stopper. One or two sentences set at heading scale but written as prose — a mission line, a promise, a refusal — dropped between sections to make a reader pause. It carries no card, no background and no icon; the size and the space around it do all the work.

**Content shape:** Two fields. `width` (`narrow` default | `content` | `wide` | `full`) and `content` (WYSIWYG, basic toolbar, required). Keep it to 1–2 sentences — this block's whole effect comes from being short at a large size, and a paragraph of body copy set this big just reads as broken. Bold the 2–3 words carrying the idea; each child theme styles `p > strong` with its own emphasis treatment, so bolding is how you mark meaning here, not how you shout (rule 7 — never hand-set a color on the run).

Renders a bare `<div>` wrapping the paragraph. The div is the child theme's extension point for `::before` / `::after` flourishes. Reserves `spacing--60` above itself **and above whatever follows** (rule 8: the breathing room is the block's, not the neighbour's problem) — override with `--ccc-tc-space`.

**Use when:**
- Source copy contains a line that IS the argument — a positioning statement, a principle, a plain-language promise — and burying it in a paragraph would waste it.
- Two dense sections need a beat between them, and a colored band would be the wrong kind of loud (rule 15: no two colored bands back-to-back — this is the quiet alternative).
- A page is running long on cards and grids and needs a change of texture that isn't another container.

**Avoid when:**
- The line is a quotation attributed to a person — use `core/quote` with `is-style-quote` (rule 23: quote blocks are for actual quotations) or `testimonial-cards`.
- You have more than two sentences — use a core paragraph, or split the idea and callout only the sharp half.
- It would be the section's heading — use a real `h2` in a core group (rules 9, 18). This block is prose set large, not a heading; it carries no heading semantics and screen readers will not treat it as one.
- Several would land on one page. Two is a rhythm; four is a gimmick, and each one steals impact from the rest.

**Pairs with:** Anything — it's a punctuation mark between sections. Natural neighbours are `split-50-50`, `feature-grid`, `card-detail-rows`; often sits just before a closing `cta-large`.

**Core alternative:** A core paragraph with a large font-size preset gets close, but capping the measure at narrow, reserving space on both sides, and giving `p > strong` a per-theme treatment all need CSS that would have to be inline on the paragraph (rules 1, 2, 4). Use this block instead.

| Field name | ACF key | Type | Required | Notes |
| --- | --- | --- | --- | --- |
| `width` | `field_text_callout_width` | select | — | choices: `narrow` / `content` / `wide` / `full`; default: `narrow` |
| `content` | `field_text_callout_content` | wysiwyg | Yes |  |
