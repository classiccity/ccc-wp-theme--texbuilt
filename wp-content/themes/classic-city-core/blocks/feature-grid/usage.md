# Feature Grid — usage

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
