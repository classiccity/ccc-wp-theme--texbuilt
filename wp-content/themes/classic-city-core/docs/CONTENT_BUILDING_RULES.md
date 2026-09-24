# Content Building Rules — Classic City Core

The non-negotiable rules for **authoring/importing page content** and **building
blocks** on any Classic City site — this parent theme and every child theme.
**Read this before you write content or build a block, every time.** These rules
apply across all client repos (the parent theme is subtree'd into each one).

When the content comes from migrating an existing site, these rules apply within
the pipeline in [`MIGRATION_PIPELINE.md`](./MIGRATION_PIPELINE.md) (stage 6 maps
sections to blocks against them; stage 8 mirrors QA learnings back up here).

---

## The two-tier system: this file + per-site `AUTHORING_PATTERNS.md`

Rules live at two levels, and **both files are required reading before
content work on a client site**:

1. **Global (this file)** — rules that apply to every Classic City site.
2. **Local — `sg-{slug}/AUTHORING_PATTERNS.md` in the client's child theme.**
   Every client site keeps one. It holds the site-specific halves of these
   rules (e.g. *which* contrast color per rule 14, *which* card color per
   rule 17), the site's editorial patterns, a build-script defaults
   checklist, and a **living "QA rules" section appended after every QA
   round** with Chris. **Reference implementation:
   `sg-lumberock/AUTHORING_PATTERNS.md`** (in the Lumberock client repo).

**Protocols:**

- **Create it early.** If the client site has no `AUTHORING_PATTERNS.md`
  yet, creating it is part of starting content work (copy Lumberock's
  structure; start the QA-rules section empty).
- **Mirror up, same commit.** When a QA round produces a rule whose
  *generic* half applies to every site, add it to THIS file (numbered) in
  the same working session, and note the mirror in the local doc — that's
  how rules 8 and 15–22 got here. Site-specific enforcement details stay
  local.
- **Corrections flow both ways.** If a QA round *corrects* a global rule
  (as Lumberock's R13 corrected rule 8's padding), fix this file — don't
  fork a local variant.

---

## The build log: per-site `BUILD_LOG.md`

Every client site also keeps **`sg-{slug}/BUILD_LOG.md`** — a
newest-first decision journal covering **content AND design/block work
as one stream** (builds live-design while they build; the why behind a
design call is as load-bearing as the why behind a copy change).
Reference: `sg-lumberock/BUILD_LOG.md`.

**Entry format** (newest at the top, so a session reads the first ~10
entries and has the trajectory):

```markdown
## YYYY-MM-DD — Title of the change
One sentence on why. Optional refs: commit hash, WPE page ID.
```

**What to log:** anything DB-side (pages/content changed on WPE — git
never sees these), any decision with a *why* (chose block X over Y,
palette/token call, treatment tried and rejected), any reversal, any
client-driven change. **What to skip:** mechanical code commits git
already explains.

## The content-sources pointer: per-site `CONTENT-SOURCES.md`

Every client site also keeps **`sg-{slug}/CONTENT-SOURCES.md`** — the map
from the build repo to where the client's raw material actually lives.
Content importing runs from the client repo, but copy, brand kits, client
media, and decision history live OUTSIDE it (git is the wrong tool for
heavy media; business context lives in the Chief of Stuff vault). This
file is the bridge — **read it before building any page** so you don't
recreate material that already exists, or hunt for it blind.
Reference implementation: `sg-trialport/CONTENT-SOURCES.md` (trialport
client repo).

**Required contents:**

1. **The Chief of Stuff client folder** —
   `~/Chief of Stuff/Brain/Chief of Stuff/Classic City/Clients/{Client}/`
   (always `~`-relative — Chris works on two machines with different
   usernames). Call out at minimum: `README.md` (running context,
   required reading), `tracker.md` (status + contacts), `content/`
   (copy/sitemap/bios), `brand/`, and the asset catalog
   `source-assets/ASSET-MANIFEST.md` where present.
2. **Heavy-media sources** (Google Drive links, client shares) — or the
   pointer to the manifest that catalogs them. Heavy media is never
   committed; the share stays the source of truth.
3. **The vault's knowledge pages, when they exist** — the Wiki client
   page (`Brain/Chief of Stuff/Wiki/clients/{slug}.md`) and
   `Classic City/Meeting Transcriptions/` for decisions made in calls.
4. **Still-expected-from-client** — what's missing and which sections it
   blocks.

**Protocols:**

- **Create it at the start of content work** (same trigger as
  `AUTHORING_PATTERNS.md`).
- **New client-sent material** (emailed Drive links, attachments) gets
  logged in the CLIENT FOLDER's asset manifest — one catalog, not two;
  this file just points there.
- **Path-portable and secret-free** — client repos deploy to WPE and
  live on GitHub; `~` paths and shared links only, no credentials.

**The four per-site files answer different questions — keep them in
their lanes:**

| File | Question |
|---|---|
| `CLAUDE.md` | Where ARE we? (small, overwritten snapshot) |
| `BUILD_LOG.md` | How did we get here, and why? (append-only, newest-first) |
| `AUTHORING_PATTERNS.md` | What rules did we learn? (distilled conventions) |
| `CONTENT-SOURCES.md` | Where does the raw material live? (map to the vault + Drive) |

History pressure goes into the log — don't let CLAUDE.md's
current-state section grow into a history, and don't bury decisions in
QA-round prose that belongs in `AUTHORING_PATTERNS.md` only when it
became a *rule*.

---

## 1. Standard elements = core Gutenberg blocks

Headings, paragraphs, buttons, lists — use the **core block**, with **NO inline
styles and NO custom HTML inside them**. Both get wiped the instant the block is
edited in Gutenberg, so they are never acceptable.

## 2. Never override a standard tag

An `h3` is an `h3`. Don't add custom font-family / size / line-height / weight to
it — let the `theme.json` defaults do their job. Same for `p`. Stop re-declaring
what the tag already gives you.

## 3. Custom structure → an ACF block, coded in two layers

- When importing content, **use existing blocks as much as possible.** Choose
  them via the usage layer in `docs/BLOCKS.md` (each block's inlined
  `usage.md`), and check `docs/CORE_BLOCKS_USAGE.md` first — core may be the
  right tool.
- If you want to build something new, **surface the idea, get Chris's sign-off,
  then build it as an ACF block** (`block.json` + `fields.php` + `render.php` +
  `style.css`) — never raw HTML dropped into a page. Every new block — parent
  OR child theme — ships with a `blocks/{slug}/usage.md` (see
  `docs/BLOCK_AUTHORING.md` step 7; `npm run docs:blocks` fails without it).
- Build every new block **as if it will be promoted into this parent theme for
  use on all sites.** Write it in two clearly separated layers, and **do not mix
  them**:
  - **Foundation** — structural HTML + CSS. Theme-agnostic; any site can use it.
  - **Theme-specific** — client-specific overrides, written *after* the foundation.
  - Mark the split in the file (e.g. `/* STRUCTURAL */` … `/* {CLIENT}-SPECIFIC */`).

## 4. CSS variables for everything

- No hardcoded pixel or color values — shadows/elevation, radius, gap, border,
  spacing, color all come from variables.
- **Block-level CSS variables are encouraged.** Prefer composing them from
  existing variables: e.g. define `--{block}-default-space: var(--wp--preset--spacing--30)`
  once and reference `--{block}-default-space` throughout the block, instead of
  repeating the raw spacing token everywhere. One knob per block.
- **Only add a NEW `theme.json` variable** if it is genuinely necessary *and*
  useful across many blocks. If you think you need one, **surface it first with
  your reasoning** before adding it.

## 5. Change values at the core / theme.json level, not per-instance

Button padding, letter-spacing, etc. live in `theme.json` custom tokens. Move any
needed padding/border/radius out of the block markup and into CSS classes.

## 6. Button color hierarchy (for content)

- The page's **main** call-to-action → **CTA** background.
- A section's primary button that is **not** the page's main CTA → **Primary**
  background.
- **Two buttons in a row** → the second uses the **outline** style.

## 7. Colors on colored surfaces

Text on any `has-*-background-color` surface relies on the **automatic opposite
color**, never a manually set color.

## 8. Vertical padding is part of *banding*, not universal

A section gets symmetric top/bottom padding (a **spacing token**, set as a real
group padding attribute editable in the editor — not a CSS margin) **only when it
has a background color**, so the content breathes inside the color band. A
section with **no background flows on the normal block rhythm** — padding it too
just creates big dead gaps. Pick one section spacer for bands and use it
consistently.

> **Front-end gotcha:** `core/group` is a *static* block — the front end does
> NOT re-serialize `style.spacing.padding` from the block comment. Write the
> padding as a real **inline `style`** on the group's `<div>`, and verify at the
> render level (`do_blocks`), not in the markup.

## 9. A heading belongs to the block it introduces

If an eyebrow/heading introduces the block right below it, they are **one
section**. Never put a background behind the heading alone — it reads as a
separate strip, disconnected from what it introduces. If the section has a
background, it wraps **both** the heading and the block.

## 10. No background band without a heading

A colored band with no heading/description inside reads as an orphan strip.
Either give the band a real heading, or drop the background.

## 11. Link-rendering blocks need real links

Blocks that render each item as a link (link-pods, image-tiles) are only valid
when the items actually link somewhere. A placeholder `#` is **not** a link — it
ships a dead anchor. If the items have no destination, use a non-linked variant
(plain cards, or tiles rendered as `<div>`), not the linked block.

## 12. Icon blocks need real icons

A block that shows an icon (feature-grid, etc.) must be given a **valid
FontAwesome slug** — never a topical/placeholder name that renders an empty box.
If you don't have a real icon, don't use an icon block.

## 13. CTA background image = subtle texture

On CTA bands, the image `opacity` field is the **image** layer's opacity over the
brand color. Keep it low so the color dominates and the text stays legible — the
image is a texture behind the message, not a photo wall competing with it.

## 14. One contrast color per page

Pick a single brand "contrast" background color for a page and use it for every
contrasted section — don't mix multiple brand darks on one page. Which color, and
any topic-based exception (e.g. a water theme leaning to teal), is a per-site
choice; document it in the child theme's `AUTHORING_PATTERNS.md`.

## 15. No two colored bands back-to-back

Don't stack two colored/contrast bands directly on top of each other —
especially heavy colors. With only a gap between them they read as one broken
block. Separate them with a neutral (canvas) section: keep the more essential
band (a dark CTA/proof band) and let the more flexible one (stats, an intro
headline, a card grid) drop to the neutral background.

## 16. Put the background on the block that supports it

If a block has its own background support (`supports.color.background`), set the
color **on the block**, not on a wrapping colored group — wrapping double-applies
the color and fights the block's own padding/text handling. Only wrap a block in
a colored group when the block itself can't carry a background.

## 17. Some blocks require a background to look right

A block with a baked-in shadow / card treatment (e.g. a stats bar) only reads
correctly on a background — it must always sit on one, defaulting to the **card
color** (the site's panel neutral). Generic: the block *needs* a bg. Site-specific:
which color (record it in the child's `AUTHORING_PATTERNS.md`).

## 18. Every block gets a name

Give every block a `metadata.name` so the editor's list view / left sidebar is
scannable — a client (or the next person) can read the page structure at a glance
instead of a stack of bare "Group" / "Stats" labels.

## 19. Grid columns must fit the item count

A grid set to 4 columns with 3 items (a ragged short row) or 6 items (a 4 + 2
orphan row) looks broken. Pick a column count that **fits** the items: if there
are fewer items than columns, use the item count; otherwise use a divisor of the
item count (e.g. 6 → 3, not 4). Applies to any card/tile/feature grid.

## 20. Non-linked feature lists are icon cards, not a wall of text

When a set of "features" (title + short description, no link) is rendered as
plain text columns it reads as a wall of text. Render it as an **icon-card grid**
— a real icon per item (a FontAwesome glyph that matches the feature). See also
rule 12 (icon blocks need real icons) and rule 11 (link blocks need links — a
non-linked list should not use a linked block).

## 21. Two-column header (heading + button)

A section header with a call-to-action is a standard two-column row — heading on
the LEFT, button on the RIGHT — and it's just a columns block; it does **not**
need a wrapping group. Top-align the heading column; **bottom-align the button
column and right-justify the button**, so the CTA sits at the bottom-right corner
in line with the bottom of the heading (not floating in the vertical middle).

## 22. Recurring site-wide sections are SYNCED patterns, not per-page rebuilds

A boilerplate section that repeats across many pages (a "shop by collection"
grid, a cross-link footer band) is authored **once** as a synced pattern
(`wp_block` post) and every page references it:
`<!-- wp:block {"ref":N} /-->`. One Gutenberg edit propagates everywhere;
images wired once show everywhere.

- **Seed, don't own.** A script may create the block once (create-only);
  after it exists, **Gutenberg is the source of truth** — build tooling never
  rewrites it.
- **Copy must be page-neutral** (it renders on every page) and links use real
  slugs.
- **Gotcha:** group comment-level `blockGap` does not reliably render on the
  front end (same static-block issue as rule 8's padding) — if an explicit gap
  is needed inside the synced block, use a real block, not a group attribute.


## 23. Quote blocks are for actual quotations

`is-style-quote` (and quote markup generally) is for words somebody said.
A **labeled statement** — a mission, a vision, a promise, a guarantee —
rendered as a quote buries its label in the `<cite>` and leads with a
quote glyph that adds nothing. Give it a real heading (`h3` "Our mission")
followed by a paragraph, so the label is scannable as a headline.
(From Sherman & Phalen QA round 1.)

## 24. FAQs use the Yoast FAQ block

Every FAQ section on every site is a **`yoast/faq-block`** (Yoast SEO ships
on all CCC sites). It emits schema.org FAQPage structured data for free and
keeps Q&A pairs editable as one unit. Do NOT build FAQs from `core/details`
accordions or heading/paragraph runs — no schema, and the pairs drift apart
in the editor. (Chris's call, Sherman & Phalen QA round 2.)

**It renders as an accordion, and you get that for free.** Yoast's own save
output is a flat run of `<strong>` questions and `<p>` answers;
`inc/faq-accordion.php` rewrites that render into real `<details>` /
`<summary>` rows with a Font Awesome marker that turns on open. Nothing to
opt into and nothing to author differently — build the FAQ exactly as Yoast
wants it and the front end collapses it.

Two consequences worth knowing:

- **The schema is untouched.** Yoast builds its FAQPage graph from the parsed
  block attributes, not from the HTML, so rewriting the render changes
  nothing about what search engines read. That is exactly why this rule picked
  Yoast's block in the first place, and it survives.
- **It degrades to the old open list, not to a broken control.** The
  disclosure is `<details>`, so keyboard and screen-reader support are the
  browser's; the JavaScript only animates the open/close. A section Yoast
  serializes in an unexpected shape is left as a plain row rather than
  half-converted.

Tuning lives in CSS variables on `.schema-faq` (glyph, rotation, timing, hover
wash — see the knob list above the FAQ section in `assets/blocks.css`). A site
that genuinely wants every answer visible at once sets
`add_filter( 'ccc_faq_accordion', '__return_false' )`. (Chris's call,
trialport Round 1 QA.)

---

**Why:** editor-safety, consistency, and a real design system. Inline styles and
one-off CSS make blocks un-editable and drift from the tokens.

**Related:** the client's `sg-{slug}/AUTHORING_PATTERNS.md` (the local half —
see "The two-tier system" above), `docs/CLIENT_HOMEPAGE.md` (authoring a client
page from the block library), `docs/THEME_TOKENS.md` (the token system these
rules lean on), and `docs/BLOCK_AUTHORING.md` (building blocks, incl. the
`acf-innerblocks-container` trap).
