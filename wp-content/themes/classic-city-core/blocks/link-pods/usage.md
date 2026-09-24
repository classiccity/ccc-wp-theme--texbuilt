# Link Pods — usage

**Purpose:** Text-first navigation cards on brand color — each pod pitches a destination in a sentence or two and the whole card is the link ("Our story", "Find a dealer", "Get a quote").

**Content shape:** Columns 1–4 (default 3 — fit to item count, rule 19). Pods repeater 1–24. Per pod: title (required; title tag h2–h6, default h3), optional short description (basic WYSIWYG, 1–2 sentences), link text (required — renders with an arrow), link URL (required — render SKIPS pods without one), palette background color, optional background image behind the content. Text auto-flips against the pod color. No icons, no dominant imagery.

**Use when:**
- Source lists 3–4 pathways/next-steps where a sentence of context per item earns the click — richer than a button row, lighter than content cards.
- Every item has a REAL destination (rule 11 — this block is the named example; `#` is a dead anchor).
- No strong per-item photography exists — pods carry themselves on palette color alone.

**Avoid when:**
- Items have no destinations — use `detail-cards` or `feature-grid` for non-linked title+description sets (rule 20: give them real icons).
- Photography should lead — use `image-link-cards` (square image + icon footer) or linked `image-tiles`.
- There's only ONE next step — that's a `cta-thin` / `cta-large` band, not a one-pod grid.

**Pairs with:** process-steps above (steps → pathways), image-link-cards (image-first sibling), cta-thin.

**Core alternative:** For 2–3 destinations needing no description, core buttons under a heading (rules 6, 21) — a pod without a description is just a slow button.
