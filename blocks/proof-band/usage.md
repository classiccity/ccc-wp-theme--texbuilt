# Proof Band — usage

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
