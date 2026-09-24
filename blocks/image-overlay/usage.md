# Image + Content Overlay — usage

**Purpose:** A large landscape photo anchors the section while a colored content card floats over one edge — an editorial "moment" that pulls the eye from imagery into a message or CTA.

**Content shape:** One image (required; demo placeholder if missing; landscape ~3:2 works best). Card side left/right (default right). Card content is free-form InnerBlocks — typical fill: eyebrow + heading + short paragraph + one button; keep it card-sized (heading + 2–3 sentences max). Card color from the native background picker (applied to the card, not the section — set one so the card reads as a card); optional card texture. Forced align full.

**Use when:**
- Source pairs one hero-quality photo with a short punchy message — an invitation, a single CTA, a brand statement.
- The page needs a mid-page visual break between denser sections (it sits between card grids and CTA bands on the style guide).
- The image matters as much as the words; a 50/50 split would dilute it.

**Avoid when:**
- Content is longer than a card's worth — use `split-50-50`, which gives copy a full half.
- It's the page opener — use `image-hero-50-50` or `hero-full-image` (heroes own the top slot).
- The message is CTA-only with no strong photo — use `cta-large` / `cta-thin` (image there is a low-opacity texture, rule 13).

**Pairs with:** center-content, cta-thin, portfolio-gallery — style guide runs it after the callout/gallery cluster as a closing visual beat.

**Core alternative:** Core cover block with a heading is flatter but acceptable for a quick banner; if the "overlay" in the source is really just text ON the image (not a card overhanging it), cover or hero-full-image is the truer match.
