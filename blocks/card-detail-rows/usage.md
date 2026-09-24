# Card with Detail Rows — usage

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
