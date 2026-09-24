# Icon Feature Row — usage

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
