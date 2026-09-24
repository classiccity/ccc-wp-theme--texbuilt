# Image Card Grid — usage

**Purpose:** The general-purpose card grid — image on top, tag/title/description below — for collections, services, testimonials-with-photos, or any titled visual list.

**Content shape:** `cards` repeater 1–24 (`column_count` 1–4 — fit the count, rule 19). Per card, all optional: `image` (demo fallback on /style-guide only), `tag` (small-caps eyebrow), `title`, `description` (2–3 sentences), `footer_title` + `footer_subtitle` (e.g. name + location), `button_text` + `button_link` (per-card CTA). Block-level: `mobile_layout` (stack | scroll), `image_aspect_ratio` (6 ratios, default 16/9), `card_bg_color` (palette slug for every card, default panel).

**Use when:**
- Source lists 2–12 parallel items each with a photo AND real descriptive copy — collections, case studies, team, locations.
- Items optionally need individual CTAs — only fill `button_*` with real destinations (rule 11); cards render fine without buttons.
- Attributed quotes with photos where the CPT-driven `testimonial-cards` isn't set up — footer_title/subtitle carry name + location.

**Avoid when:**
- Every card is a navigation link and the whole card should be clickable — use `image-link-cards` or `image-tiles` (rule 11).
- Items are icon-shaped features without photos — use `feature-grid` (rule 20).
- You only have 3 items with heavy uniform CTAs — `image-columns` (3-up, full-width buttons) is the tighter fit.

**Pairs with:** hero variants, swatch-explorer, detail-cards, cta-thin; lead with a core-group section header (rule 9).

**Core alternative:** Core columns of image + heading + paragraph when there are exactly 2–3 one-off items and no tag/footer/aspect control is needed.
