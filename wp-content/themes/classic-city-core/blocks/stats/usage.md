# Stats — usage

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
