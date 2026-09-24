# Feature Showcase — usage

**Purpose:** A two-beat pitch: colored panel with centered title + copy + product image on top, then a row of icon pods that break the offer into parts.

**Content shape:** Top: required `top_title`, optional `top_description` (WYSIWYG, basic formatting), optional `top_image` (sits flush to the panel's bottom edge — a product/app shot works best; demo fallback on /style-guide only). Bottom: `pods` repeater 1–8 (`pod_columns` 2–4 — match count, rule 19). Per pod: required FA `icon_name` + `title` + `body`; optional `button_text`+`button_url` (outline buttons, one `pod_button_color` slug for all). Panel color from the native picker.

**Use when:**
- Source has a "here's the product" hero-ish intro AND 2–4 supporting capability pods that belong together as one section.
- A mid-page product spotlight needs its own colored panel plus a breakdown row — one block instead of hero + feature-grid.
- Pods each have a real FA icon (rule 12) and, if buttons are used, real URLs (rule 11).

**Avoid when:**
- You only need the pod row — use `icon-feature-row` or `feature-grid`.
- You only need the top panel — use `cta-large` (InnerBlocks) or `center-content`.
- It's the page's actual top hero — use a hero variant (`hero`, `hero-full-image`, `hero-gradient`, `hero-3-up`); this block doesn't carry the `sg-hero` marker.

**Pairs with:** logo-strip, testimonial-cards, cta-large; the style guide runs it as a late-page showcase on `backgroundColor: primary`.

**Core alternative:** None clean — the flush-bottom image panel + pod row is bespoke. If content doesn't fill both beats, split into simpler blocks instead.
