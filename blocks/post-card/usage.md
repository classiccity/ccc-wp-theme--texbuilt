# Post Card — usage

**Purpose:** Renders the CURRENT post inside a Query Loop as a standard image card (featured image, category eyebrow, linked title, excerpt, read-more) — the blog/news listing card.

**Content shape:** Takes NO content of its own — image, category, title, excerpt, and permalink all come from the post in the loop. It must sit inside a `core/post-template` (declared via `ancestor` in block.json); outside a loop it renders nothing. Authorable presentation only: image aspect ratio (six choices, default 16:9), show category toggle, show excerpt toggle, read-more label (always links to the post). Markup is byte-identical to an `image-card-grid` card via the shared `ccc_render_image_card()` partial.

**Use when:**
- Building a blog index, news section, or "latest posts" homepage strip — core Query Loop supplies the posts, this block styles each one.
- Source shows post listings styled like the site's other image cards — this keeps listing cards and hand-authored `image-card-grid` cards visually identical.
- Posts should surface automatically (newest first, by category, etc.) rather than being hand-picked.

**Avoid when:**
- Content is hand-authored, not posts — use `image-card-grid` (same card, editable fields).
- Items are pages/services/products without a post type behind them — again `image-card-grid` or `image-columns`.
- You're tempted to place it outside a Query Loop — it silently renders nothing; it is not a standalone card.

**Pairs with:** core/query + core/post-template (its required home), a two-column section header with a "View all" button above (rule 21).

**Core alternative:** Core post-title/post-excerpt/post-featured-image blocks inside the post template — acceptable but loses the card treatment and drifts from the site's card system.
