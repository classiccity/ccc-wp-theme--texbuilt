# Hero: Full Image — usage

**Purpose:** Cinematic page-top hero — a big headline over a full-bleed background photo (or looping video), with supporting copy/buttons in a card on top.

**Content shape:** Required: `image` (full-bleed bg, ~1440×720+; also the video poster; demo fallback on /style-guide only) and `title_html` (h1; inline `<span>`/`<em>`/`<strong>` allowed to style words). Optional: `video` (mp4/webm — muted/looped, takes precedence over image), `gradient_color` (palette slug for the bottom fade; default fades to page bg), `card_width` (narrow | content). Body + buttons are InnerBlocks inside the overlay card.

**Use when:**
- Source has one strong, wide, atmospheric photo (or a background video) that should own the whole viewport width behind the headline.
- The brand moment is image-first: short punchy headline, minimal supporting copy in the card.
- A background video hero was requested — this is the only hero variant with video support.

**Choosing among hero variants:** text OVER a full-bleed photo → this block; photo washed with a brand color for text legibility → `hero-gradient`; photo beside text → `hero`; wide photo below the copy → `hero-3-up`; inset/framed panel → `framed-callout`.

**Avoid when:**
- The photo is busy and text legibility depends on dimming it — use `hero-gradient` (70% image over palette color).
- No high-quality wide image exists — a cropped-up small photo ruins this; fall back to `hero` or a core group.

**Pairs with:** logo-strip, stats, icon-feature-row, feature-grid; the bottom gradient fades into the next section's background — match `gradient_color` to it.

**Core alternative:** Core cover block gives text-over-image but without the headline/card split, video-with-poster handling, or palette bottom fade — use this block for real hero moments.
