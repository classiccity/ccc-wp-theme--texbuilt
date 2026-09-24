# Hero — usage

**Purpose:** The default page-top hero — headline, body, and buttons on one side, a photo on the other; the safest opener when in doubt.

**Content shape:** One required `image` (side photo, ~960×720 crop; demo fallback on /style-guide only) + `image_side` (right default | left). All copy is InnerBlocks: typically h1 + 1–2 sentence paragraph + buttons (main CTA gets the CTA color, second button outline — rule 6). Mobile stacks image below content. No background support.

**Use when:**
- Source page opens with headline + intro copy + CTA and has one decent supporting photo that can sit BESIDE the text (not behind it).
- The photo matters as content (product, place, people) and shouldn't be dimmed or overlaid.
- You need the most conservative hero — no color band, sits on the page background.

**Choosing among hero variants:** photo beside text → this block; text OVER a full-bleed photo → `hero-full-image`; palette-washed photo + gradient → `hero-gradient`; half photo / half colored panel → `image-hero-50-50`; field-driven 2-col intro row + wide image BELOW → `hero-3-up`; framed inset panel → `framed-callout`.

**Avoid when:**
- No usable image exists — don't rely on the demo placeholder in production; use a core group with h1 + paragraph + buttons.
- The image is a wide landscape that would crop badly in a half column — use `hero-3-up` (image renders full-width below) or `hero-full-image`.

**Pairs with:** logo-strip, stats, feature-grid, icon-feature-row — the style guide leads every page flow with a hero variant.

**Core alternative:** Core columns (text | image) for a hero-less interior page top; use this block whenever it's a true page hero (it emits the `sg-hero` marker).
