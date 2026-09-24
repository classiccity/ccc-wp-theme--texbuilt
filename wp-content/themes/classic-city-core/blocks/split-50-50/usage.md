# Split 50/50 — usage

**Purpose:** The workhorse mid-page section: media on one half, free-form content on the other — one topic, one image, one message per instance.

**Content shape:** One image (required; demo placeholder if missing), optional video (mp4/webm/mov — takes precedence; image becomes poster). Media side left/right. Content side is InnerBlocks — canonical fill: eyebrow + h2 + paragraph + buttons. Content side takes the native background picker and DEFAULTS to the panel surface, so it reads card-like even unstyled (rule 16: set color on the block, don't wrap it). Optional texture. Align wide or full; sized to content, not viewport.

**Use when:**
- Source alternates image-beside-text sections down a page — the classic zig-zag; alternate the media side per instance.
- A topic has one supporting image and a paragraph-plus of copy with an optional CTA.
- Copy is free-form (any mix of core blocks) rather than a fixed spec-list shape.

**Avoid when:**
- It's the page's FIRST section at full viewport impact — use `image-hero-50-50` (full-bleed, 100vh, `sg-hero`).
- The section needs the structured product-panel shape (floating caption card, icon detail rows, spec list) — use `feature-detail`.
- The image should dominate with only a card of text floating over it — use `image-overlay`.
- There are 4+ short parallel items — that's a card grid (`image-columns`), not four splits.

**Pairs with:** stats, logo-strip, testimonial-cards between splits for rhythm; it is the canonical child of `stackable` (style guide nests three). Avoid two colored splits back-to-back (rule 15).

**Core alternative:** Core columns (image + heading/paragraph/buttons) when you want plain flow with no panel/card treatment — fine for text-lite pages; the block earns its keep via the media handling and panel styling.
