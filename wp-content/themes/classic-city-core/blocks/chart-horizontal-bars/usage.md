# Chart: Horizontal Bars — usage

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
