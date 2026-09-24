# Large CTA — usage

**Purpose:** The page's big conversion band — a full/wide color or gradient band carrying a heading, short pitch, and button(s).

**Content shape:** All content is InnerBlocks (Hybrid): typically h2 + one 2–3 sentence paragraph + a buttons row. ACF fields are background-only: optional `bg_image` (no demo fallback — omit rather than fake it), `bg_opacity` (0–100, default 80), `has_texture` toggle. Color/gradient comes from the native block picker (rule 16 — set it on the block, not a wrapping group).

**Use when:**
- Source page ends (or a section run ends) with a "contact us / get started / schedule" pitch that deserves a full band.
- The CTA needs more than one line of supporting copy, or two buttons (second button = outline style, rule 6).
- A photo should sit behind the pitch — keep `bg_opacity` low so it reads as texture, not a photo wall (rule 13).

**Avoid when:**
- The CTA is a single line + one button — use `cta-thin` instead; this block would look empty.
- The button has no real destination yet — don't ship a `#` CTA (rule 11); hold the section.
- It would sit directly against another colored band — separate with a neutral section (rule 15).

**Pairs with:** cta-thin (the small sibling), center-content, image-wall, testimonial-cards; conventionally the last section before the footer. Page's main CTA button gets the CTA background color (rule 6).

**Core alternative:** A core group with a background color + heading/paragraph/buttons works when no bg image/texture is wanted, but this block is the house pattern for CTA bands — prefer it.
