# Thin CTA — usage

**Purpose:** A narrow one-line nudge strip — headline (+ optional subtext) left, single button right — for mid-page conversion moments.

**Content shape:** Fully field-driven, no InnerBlocks. Required: `headline` (one short line), `button_label`, `button_url`. Optional: `subtext` (one line), `headline_level` (bold-p or h2–h6, default h3), `bg_image` + `bg_opacity` (default 80), `has_texture`. Background color from the native picker. Renders nothing without headline or button.

**Use when:**
- Source has a one-liner like "Questions? Get in touch" with a single obvious link — no paragraph of copy.
- You want a conversion beat between content sections without the weight of a full `cta-large` band.
- The CTA copy is strictly headline + one clause; the L/R layout only fits short text.

**Use `headline_level`:** pick a heading tag that fits the outline, or bold-p if the page's heading hierarchy is already satisfied.

**Avoid when:**
- The pitch needs a real paragraph or two buttons — use `cta-large` (InnerBlocks give you that freedom).
- There's no real destination URL — rule 11; skip the section rather than link `#`.
- It would stack against another colored band — rule 15; keep a neutral section between.

**Pairs with:** center-content, cta-large, image-card-grid, portfolio-gallery (style guide runs center-content → cta-thin → cta-large).

**Core alternative:** The rule-21 two-column header (heading left, button bottom-right) in plain core columns — use that when the strip should sit on the page background instead of a colored band.
