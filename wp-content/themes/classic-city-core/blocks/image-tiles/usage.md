# Image Tiles — usage

**Purpose:** Photo-first tile row where each image carries a one-line blurb over a gradient — mood/category tiles that let photography do the talking.

**Content shape:** Tiles repeater 1–12. Per tile: image (required; demo placeholder if missing), blurb (required — ONE short line, e.g. "Crafted by hand"; it's an overlay, not a paragraph), optional link URL. With a link the tile renders as an `<a>`; without, it renders as a `<div>` (no dead anchors). Block-level: desktop columns 1–4 (default 4 — fit to count, rule 19), tile aspect vertical/horizontal, optional desktop carousel (mobile always scroll-snaps).

**Use when:**
- Source shows a strip of 3–4 lifestyle/category photos each captioned with a short phrase.
- Items either all link somewhere real, or are pure atmosphere (the block handles both — but don't mix `#` in; leave link empty instead, rule 11).
- Copy per item is a caption, not a description — if you're tempted to cram sentences into the blurb, it's the wrong block.

**Avoid when:**
- Items need heading + body copy or buttons — use `image-columns` (content cards) or `image-card-grid`.
- The grid is navigation with labeled destinations — use `image-link-cards` (icon footer bar reads as "click me" more strongly).
- Images are proof-of-work to browse in detail — use `portfolio-gallery` (lightbox) instead of hover blurbs.
- You want pure moving decoration with no text at all — use `image-wall`.

**Pairs with:** testimonial-cards, document-downloads, link-pods (style-guide neighbors); a heading intro above per rule 9.

**Core alternative:** Core gallery block for a plain static image set with visible captions; use it when there's no blurb-overlay intent at all.
