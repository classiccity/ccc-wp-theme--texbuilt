# Image Hero 50/50 — usage

**Purpose:** Full-bleed page-top hero: media (photo or looping video) fills one half of the viewport, a branded color/gradient panel with the page's opening pitch fills the other.

**Content shape:** One image (required; demo placeholder if missing), optional video file (mp4/webm/mov — takes precedence, image becomes poster). Content side is free-form InnerBlocks — the standard fill is eyebrow + h1 + paragraph + buttons. Toggles: media side (left/right), media spacing (padded panel + shadow vs edge-to-edge), full viewport height (default on), background texture. Panel color comes from the native background picker — set one; an uncolored panel defeats the block. Forced align full; emits the `sg-hero` marker.

**Use when:**
- The chunk is the FIRST section of a page and has one strong photo/video plus headline-level copy.
- Source hero splits roughly half media, half message (as opposed to text overlaid on a photo).
- The client has hero-quality video — this is the primary video-hero block (with split-50-50).

**Avoid when:**
- The same image+content split appears mid-page — use `split-50-50` (wide/normal align, sized to content, panel-surface content side).
- The hero is a single full-width photo with text on top — use `hero-full-image`; text on a gradient with no photo — use `hero-gradient`; image beside text inside the content column — use `hero`.
- The section is a structured product panel (caption card, detail list) — use `feature-detail`.

**Pairs with:** logo-strip or stats directly below (proof under the hero); cta-large as the page's closing echo.

**Core alternative:** None for a true hero — heroes always come from the hero family. If the page opener is text-only, `hero-gradient` or a heading + paragraph on the canvas beats faking it with core columns.
