# Feature Detail — usage

**Purpose:** Deep-dive panel on ONE feature/product/collection — image with a floating caption card beside headline, description, and icon-labeled detail rows.

**Content shape:** Required `image` (demo fallback on /style-guide only). Optional caption card: `image_subtitle` (small caps, "FOR WATERFRONT") + `image_title` ("Coastal Collection"). Content side: `headline`, `description` (2–3 sentences), optional `button_text`+`button_url`, `detail_rows` repeater 0–8 (each: required `label`, FontAwesome `icon_name` like `droplet`, 1–2 line `description`). `detail_color` palette slug tints row bars + icons. No built-in section header — wrap in a core group with your own heading if needed.

**Use when:**
- Source devotes a whole section to one thing with 3–6 attribute bullets (durability specs, service inclusions) that can each carry a real FA icon (rule 12).
- A product/collection page needs the "one image + attribute breakdown" panel.
- Content matches one tab of `product-feature-toggles` but there's only one item — this is the standalone single-panel version.

**Avoid when:**
- You have several parallel features to toggle between — use `product-feature-toggles`.
- The section is image + free-form prose without attribute rows — use `split-50-50` (InnerBlocks freedom).
- Attributes have no honest icons — use `split-50-50` with a core list instead of forcing icons (rule 12).

**Pairs with:** hero-3-up, icon-feature-row, image-card-grid, document-downloads; precede with a core-group section header per its own convention (rule 9).

**Core alternative:** Core columns (image | heading + paragraph + list + button) when there are no icon detail rows or floating caption to justify the block.
