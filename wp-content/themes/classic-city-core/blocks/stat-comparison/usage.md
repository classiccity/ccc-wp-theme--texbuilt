# Stat Comparison — usage

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
