# Hero 3 Up — usage

**Purpose:** Editorial page-top hero — eyebrow + h1 left (2/3), description + button right (1/3), then a wide banner image below the intro row.

**Content shape:** Fully field-driven, no InnerBlocks. Required: `title` (renders as h1) and `image` (wide banner, ~1440×600; demo fallback on /style-guide only). Optional: `eyebrow` (small caps), `description` (2–4 sentences), `button_text`+`button_url` (renders as a CTA-colored button), `image_aspect_ratio` (none | 16/9 | 3/2 | 4/3 | 1/1 | 4/5 | 3/4). Mobile stacks to one column.

**Use when:**
- Source hero has a long/wide landscape photo that deserves full content width rather than a half-column crop.
- Hero copy splits naturally: big claim + a meatier supporting paragraph (the 1/3 column holds more prose than most heroes).
- You want a structured, no-editor-freedom hero — all content maps 1:1 to fields (good for scripted imports).

**Choosing among hero variants:** image full-width BELOW the copy → this block; image beside copy → `hero`; copy over a full-bleed image → `hero-full-image`; palette-washed image behind copy → `hero-gradient`.

**Avoid when:**
- The button URL doesn't exist yet — button only renders with both text and URL; fine to omit, never fake `#` (rule 11).
- Hero needs multiple buttons or custom inner content — use `hero` or `hero-gradient` (InnerBlocks variants).

**Pairs with:** feature-detail, icon-feature-row, logo-strip, stats — an editorial opener before feature sections.

**Core alternative:** Rule-21 two-column header (heading left, button right) + a core full-width image approximates it, but loses the 2/3–1/3 tuning and `sg-hero` marker — prefer the block for page tops.
