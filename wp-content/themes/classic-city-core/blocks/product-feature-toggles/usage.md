# Product Feature Toggles — usage

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
