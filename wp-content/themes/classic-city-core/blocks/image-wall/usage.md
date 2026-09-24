# Image Wall — usage

**Purpose:** Pure ambience: two full-width marquee rows of photos auto-scrolling in opposite directions — communicates volume and vibe ("look how much we've made"), not specific information.

**Content shape:** Images repeater, min 4 / max 24 (image only — no captions, no links, no text anywhere). Rows alternate by index: 1st/3rd/5th → top row, 2nd/4th/6th → bottom. 8–16 varied images is the sweet spot; each row's set is doubled for a seamless loop, so too few images makes the repetition obvious. Demo placeholders fill any missing image. Forced align full.

**Use when:**
- Source has a large pile of decent photos with no individual story — event shots, product spreads, portfolio overflow.
- The page needs an energetic visual divider between content sections or before the closing CTA.
- The message is abundance/credibility-by-volume rather than any single image.

**Avoid when:**
- Visitors should stop and examine images (titles, captions, lightbox) — use `portfolio-gallery`.
- Images represent categories or destinations — use `image-tiles` (linked) or `image-link-cards`.
- The images are client/partner logos — use `logo-strip` (its scroller mode is the logo version of this effect).
- Fewer than 4 usable images exist — the field minimum blocks it, and the loop would look broken anyway.

**Pairs with:** cta-large (style guide runs the wall right after the closing CTA), hero-full-image, center-content.

**Core alternative:** A core gallery if motion is unwanted or the images deserve individual attention; never rebuild the marquee with custom HTML (rule 3).
