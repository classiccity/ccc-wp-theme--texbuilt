# Image Portfolio Gallery — usage

**Purpose:** Browsable proof of work — a dense grid of project images that open in a lightbox with title and caption, for visitors who want to inspect, not just glance.

**Content shape:** Items repeater 1–48 (8–12 fills the grid nicely). Per item: image (required; demo placeholder if missing), title (required — shown on the tile and in the lightbox), optional caption (1–2 lines, lightbox only). Tiles are lightbox buttons, NOT links out — nothing here navigates to another page. Needs `assets/portfolio-lightbox.js` (auto-wired).

**Use when:**
- Source is a portfolio/gallery/our-work page section where individual images have names ("Coastal Retreat", "Modern Kitchen") and deserve a closer look.
- Images benefit from an enlarged view — detail shots, before/afters, finished installs.
- You have per-image titles; a title-less pile is better served elsewhere.

**Avoid when:**
- Each project should link to its own case-study page — use `image-link-cards` or linked `image-tiles` (rule 11 territory: this block cannot link out).
- Images are ambient decoration with no individual identity — use `image-wall`.
- One image should dominate with the rest as selectable thumbnails — use `highlighted-image-gallery`.
- Items need real body copy or CTAs — use `image-card-grid` / `image-columns`.

**Pairs with:** framed-callout or a heading intro above (rule 9), center-content, cta-large below — show the work, then ask for the job.

**Core alternative:** Core gallery block when a simple static grid with visible captions is enough and the lightbox/title treatment isn't wanted.
