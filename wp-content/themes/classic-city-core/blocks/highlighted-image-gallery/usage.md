# Highlighted Image Gallery — usage

**Purpose:** Product-page style gallery — one large featured image with a clickable thumbnail row that swaps images into the primary slot.

**Content shape:** One required `images` gallery field (min 1, no max; first image = initial primary; single image renders with no thumb row) and `columns` (thumbnails per row, 2–10, default 5). No copy fields at all. Demo images fall back on /style-guide only — production needs real uploads. Interactive (view.js) — thumbnails are buttons.

**Use when:**
- Source is a product/project detail page with 4–8 photos of ONE subject from different angles — the swap interaction implies "same thing, more views."
- The design wants one dominant image rather than an even grid.
- Roughly `columns + 1` images are available so the thumb row fills a clean single row (rule 19 spirit).

**Avoid when:**
- Images are of DIFFERENT subjects (portfolio, project roster) — use `portfolio-gallery` (lightbox grid) or `image-tiles` (linked tiles).
- Images need captions or titles — this block has none; `portfolio-gallery` carries title + caption.
- It's a decorative photo strip — use `image-wall` (scrolling rows) or a core gallery.

**Pairs with:** feature-detail, split-50-50, document-downloads, cta-thin — product-detail contexts; introduce with a core heading (rule 9).

**Core alternative:** Core gallery block when a static grid is fine and no primary/thumbnail interaction is wanted.
