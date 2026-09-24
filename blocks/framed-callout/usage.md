# Framed Callout — usage

**Purpose:** A gallery-matted hero/CTA — the colored panel sits inside a visible frame of page background, giving a premium inset look at up to full viewport height.

**Content shape:** All copy is InnerBlocks, seeded as eyebrow + h1 + large paragraph + CTA button — keep roughly that stack. ACF: `alignment` (left | center, default center), `height` (auto | 50–100vh minimums), optional `bg_image` (cover, inside the panel; demo fallback on /style-guide) + `bg_opacity` (default 100). Panel color/gradient from the native picker — render strips it off the outer frame so only the inner panel paints (rule 16 handled internally).

**Use when:**
- The page opener (or closing pitch) wants a full-height statement but the design language is "framed/inset," not edge-bleed.
- Source hero copy is a single centered stack — eyebrow, big headline, a line or two, one button.
- You want a photo behind hero copy WITH the page background still visible around it.

**Avoid when:**
- The image should bleed to the viewport edges — use `hero-full-image` (full-bleed photo) or `hero-gradient` (photo + palette wash).
- Content is a heading + body + image side-by-side — use `hero` or `image-hero-50-50`.
- It's a mid-page CTA band on the normal rhythm — use `cta-large`; the frame treatment reads as a page-level moment.

**Pairs with:** highlight-tiles, portfolio-gallery, logo-strip; works as either the top hero or a dramatic closer.

**Core alternative:** A core cover block approximates the panel but loses the frame, min-height presets, and opacity-over-color layering — prefer this block for any framed treatment.
