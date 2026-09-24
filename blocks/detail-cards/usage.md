# Detail Cards — usage

**Purpose:** Editorial data cards — a stat + label pair over a photo, description anchored at the bottom — proof points with visual atmosphere.

**Content shape:** `cards` repeater, 1–24 (practical: match `column_count` 1–4 per rule 19, or go long with `autoscroll` marquee). Per card: `stat` (short value, "80", "0.93"), `stat_title` (short label, "Vitamin D"), `description` (1–2 small sentences), `bg_image` (optional per card — demo fallback fires on /style-guide only). Block-level: `bg_opacity`, `autoscroll`, `mobile_layout` (stack|scroll), `aspect_ratio` (default 3/4 portrait). Card color = native picker, propagated per-card.

**Use when:**
- Source pairs numbers with imagery — lab results, measurements, per-product specs where each stat has its own photo.
- 3–4 proof points each need a value, a label, AND a sentence of context (more than `stats` gives you).
- A long set (6+) suits a scrolling marquee showcase (`autoscroll`).

**Avoid when:**
- The numbers need no imagery or per-item copy — use the `stats` block (big-number row on a gradient).
- Items are feature claims, not data — use `feature-grid` (icons) or `image-card-grid` (titled cards).
- You have no real photos — cards without images are just tinted boxes; production has no placeholder fallback.

**Pairs with:** stats, image-card-grid, highlight-tiles; style guide gives it `backgroundColor: primary` — cards want a solid palette slug so the scrim gradient works (rule 17 spirit).

**Core alternative:** None good — core columns of heading+paragraph lose the layered image/scrim treatment. If it's plain numbers, that's `stats`, not core blocks.
