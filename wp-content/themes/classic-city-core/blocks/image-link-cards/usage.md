# Image Links with Icons — usage

**Purpose:** A navigation grid — square image cards, each fully clickable, with a colored footer bar (FontAwesome icon + title + arrow) telling the visitor "pick your path."

**Content shape:** Block-level eyebrow + h2 header (shared block-head partial, palette colors). Columns 1–6, default 4 — match to item count (rule 19). Cards repeater 1–24: title (required, 1–4 words), link URL (required — render SKIPS cards without one), image (required; 1:1 cover-crop; demo placeholder if missing), footer palette color (default primary), FontAwesome icon name. Footer text/icon auto-flip to the palette opposite.

**Use when:**
- Source lists category/audience/location entry points that each lead to their own page ("Mountains / Coastal / Suburbs…").
- Every item has a real destination URL AND a representative photo; copy per item is just a label.
- You can assign a genuine FontAwesome icon per card (rule 12) — the icon bar is the block's signature.

**Avoid when:**
- Items lack real URLs — rule 11: never ship `#`; use `image-columns` (non-linked cards with copy) or `image-tiles` (renders `<div>` tiles when unlinked).
- Items need body copy or per-item descriptions — footer bar holds a title only; use `image-card-grid` or `link-pods` (WYSIWYG description).
- No good square imagery — `link-pods` does the same nav job on color alone.
- No sensible icons exist — don't invent topical icon names; prefer `image-tiles` with links.

**Pairs with:** hero blocks above (it often IS the homepage router section), cta-thin below, link-pods (text-first sibling).

**Core alternative:** For 2–3 destinations without imagery, a two-column header row (rule 21) plus core buttons is honest and lighter than a card grid.
