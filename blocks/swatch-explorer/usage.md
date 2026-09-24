# Swatch Explorer — usage

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
