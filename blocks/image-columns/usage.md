# Image Columns — usage

**Purpose:** Presents 2–5 parallel offerings (services, product lines, benefits) as equal-weight cards, each with an image, a short pitch, and its own optional button.

**Content shape:** Repeater 1–12 items (render clamps grid to 2–5 columns; default 3 — pick a column count that fits the item count, rule 19). Per item: image (required in editor, resolves to demo placeholder if missing), heading (short, ~2–6 words), body (2–3 sentences), optional button label + URL (button renders only when BOTH are present). Block-level: aspect ratio (horizontal/square/vertical), one button color for all cards, card-body background via native picker.

**Use when:**
- Source page presents 3–4 services/products/benefits side by side, each with an image AND a paragraph of copy.
- Items have individual "learn more" destinations but the copy (not the link) is the point — this is a content grid, not a nav grid.
- You need per-card buttons plus real body text; feature-grid covers icon-only items, image-tiles covers image-only ones.

**Avoid when:**
- Items are navigation (title + destination, thin copy) — use `image-link-cards` (whole-card link, icon footer) or `link-pods` (no dominant image).
- Cards should link from title/footer with a category eyebrow, blog-card style — use `image-card-grid`.
- Items have no images — use `feature-grid` or `icon-feature-row` with real FontAwesome icons (rules 12, 20).
- No real per-card URLs exist: omit the button fields rather than shipping `#` buttons (spirit of rule 11).

**Pairs with:** stats, feature-grid, split-50-50, cta-thin (style-guide neighbors: heading intro above, stats band below).

**Core alternative:** For 2–3 items that are mostly prose with no card treatment, plain core columns with heading + paragraph + button per column (rules 1, 21) reads lighter than forcing cards.
