# Background Texture — usage

**Purpose:** Purely decorative — projects a registered brand texture behind neighboring sections; communicates nothing on its own.

**Content shape:** No copy, no images to supply. Two fields: `texture_slug` (select, populated from the child theme's `settings.custom.textures` in theme.json — renders NOTHING if the theme registers no textures) and `arrangement` (`bottom` | `top` | `middle`). Zero-height block; texture sits at `z-index: -1`, visible only where adjacent sections have a transparent background.

**Use when:**
- The child theme has textures registered in theme.json AND a page stretch of unbanded (transparent-bg) sections feels flat.
- The design calls for brand texture bleeding behind a seam between two sections (drop at the seam, pick `middle`).
- You want atmosphere without adding a colored band (rule 10 — no orphan color strips).

**Avoid when:**
- The child theme registers no textures — the block bails silently; skip it entirely.
- The neighboring sections carry opaque backgrounds — the texture is invisible behind them; use the block's own `has_texture` toggle (cta-large, cta-thin) or the section's background instead.
- You're importing content — this is a design-polish drop-in, not a content container; never map source copy to it.

**Pairs with:** center-content, cta-large, cta-thin, hero-gradient (any texture-aware section); placed as a sibling between full-width sections.

**Core alternative:** None — core blocks can't do this. The alternative is simply omitting it; it is always optional.
