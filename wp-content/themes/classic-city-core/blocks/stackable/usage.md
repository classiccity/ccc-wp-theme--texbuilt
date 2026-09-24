# Stackable — usage

**Purpose:** A scroll-theater wrapper: children pin at a sticky offset and each later one slides up to cover the last — turns a series of sections into a paced, one-at-a-time reveal.

**Content shape:** No content of its own — pure InnerBlocks wrapper plus ONE field: sticky offset (spacing slug 10–100, default 40; bump under tall navbars). Canonical use: ONE Stackable containing 3–4 children, most commonly `split-50-50`s with alternating background colors and media sides (the style-guide sample). Children MUST bring their own opaque backgrounds — a transparent child lets the covered section show through. Legacy sibling-Stackables still work via CSS `:has()` detection.

**Use when:**
- Source presents 3–4 parallel pitches meant to be absorbed one at a time (awareness → conversion → retention) and the page can afford a premium scroll moment.
- The children are uniform full-width sections (splits, feature panels) with solid backgrounds.
- The page is a marketing/landing page where scroll delight fits the brand.

**Avoid when:**
- Content should be scannable at a glance or compared side by side — use a card grid (`image-columns`, `detail-cards`); sticky stacking hides everything but the current panel.
- Only one section exists — a single-child Stackable is pointless overhead; place the section directly.
- Any ancestor uses `overflow: hidden/auto` or `transform` — sticky breaks (documented gotcha); verify before committing.

**Pairs with:** split-50-50 (its canonical children), a heading intro section above, cta-large after the stack resolves.

**Core alternative:** Just stack the sections normally — Stackable is presentation-only; if the scroll effect isn't clearly wanted from the source/design intent, plain flow is the safer import.
