# Highlight Tiles — usage

**Purpose:** A bento wall of short brag-lines — 4 or 6 asymmetric colored tiles, each a punchy claim with an optional image, for "why us" marketing pitch moments.

**Content shape:** `card_count` (4 = 2×2 | 6 = 3×2, default 6) — supply EXACTLY that many rows (`cards` repeater caps at 6; extras ignored, missing tiles leave holes). Per tile: `headline` (textarea — author line breaks preserved; think "Backed by 40+ peer-reviewed studies"), `bg_color` (palette slug per tile, default panel), optional `image` with `image_treatment` (background = cover + scrim | inline = centered illustration). Text-only tiles are fine. Mobile stacks.

**Use when:**
- Source has 4 or 6 short, parallel value claims (stats, credentials, differentiators) each expressible in ≤2 lines — no body copy per item.
- The page needs a high-energy visual break where mixed tile sizes/colors ARE the design.
- Some claims have images and some don't — the block handles mixed tiles gracefully.

**Avoid when:**
- You have 3, 5, or 7+ items — the layout only knows 4 or 6; use `feature-grid` or `image-card-grid` (rule 19 spirit).
- Items need descriptions or links — tiles are headline-only and unlinked; use `image-link-cards` (linked) or `image-card-grid` (described).
- Claims are icon-shaped features — use `feature-grid` (rule 20).

**Pairs with:** detail-cards, framed-callout, stats; style guide runs detail-cards → highlight-tiles → framed-callout. Mind per-tile colors against rule 14's one-contrast-color-per-page.

**Core alternative:** None — the asymmetric 3fr/5fr/4fr grid and scrim system are bespoke. If content doesn't fit 4/6 punchy lines, pick a grid block instead.
