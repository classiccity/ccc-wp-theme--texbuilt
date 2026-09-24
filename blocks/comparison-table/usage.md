# Comparison Table — usage

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
