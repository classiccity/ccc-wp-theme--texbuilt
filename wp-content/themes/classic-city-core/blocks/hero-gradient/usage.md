# Hero — Gradient — usage

**Purpose:** Brand-saturated page-top hero — photo dimmed to 70% over a palette color plus a bottom-left gradient, so copy always reads and the brand color dominates.

**Content shape:** Fields: `bg_color` (palette slug, default primary — drives the wash, the gradient, AND the auto text-color cascade, rule 7), `bg_image` (photo; demo fallback on /style-guide only), `content_width` (content | narrow), `content_alignment` (left default | center). All copy is InnerBlocks — canonical stack: eyebrow paragraph + h1 + body paragraph + button(s); text sits bottom-left where the gradient is strongest.

**Use when:**
- The available hero photo is mediocre or busy — the 70% wash + gradient makes ANY image legible and on-brand.
- The page should open in the brand's contrast color (rule 14 — match the page's single contrast slug).
- Source hero copy is a standard eyebrow/headline/paragraph/CTA stack with left-aligned text.

**Choosing among hero variants:** brand-color wash over the photo → this block; photo shown at full strength → `hero-full-image`; photo beside copy → `hero`; photo below a 2-col intro → `hero-3-up`; half photo/half color panel → `image-hero-50-50`.

**Avoid when:**
- The photo itself is the selling point and shouldn't be tinted — use `hero-full-image` or `hero`.
- The section isn't the page top — for mid-page color bands use `cta-large`; this emits the `sg-hero` marker.

**Pairs with:** logo-strip, feature-grid, stats, highlight-tiles; follow with a neutral section, not another heavy color band (rule 15).

**Core alternative:** Core cover with a duotone/overlay comes close but loses the palette pair-helper text cascade and the locked directional gradient — use this block.
