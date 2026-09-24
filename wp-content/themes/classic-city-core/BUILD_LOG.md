# Classic City Core — Build Log

Newest-first decision journal for the parent theme (`ccc-wp-theme`).
One entry per meaningful task: what changed and why, not a diff.
Protocol: user-scope CLAUDE.md § "Automatic after-task logging".

## 2026-07-31 — image-hero-50-50: dynamic-height was defeated by tall images

A hero with "Full height" switched OFF still rendered full height on trialport
/biopharma. The class was correct and on the element — the bug was in the CSS,
and it had been there since the block shipped.

`min-height: 0` on the media cell removes the automatic MINIMUM size; it does
nothing about the maximum. The media is `height: 100%` of a grid row whose height
is what is being computed, so during intrinsic sizing that percentage is
indefinite and falls back to auto — the media sizes to its content, and its
content is an image at INTRINSIC height. The content column never gets to drive
the row, which is exactly what the old comment claimed it did.

It stayed hidden because it only bites with a TALL image. Half a 1440px viewport
is ~720px wide: a 3:2 landscape photo lands ~480px tall, shorter than a typical
content column, so the content wins by accident. The same block with a 3:4
portrait lands ~960px — taller than the viewport. /biopharma's image is 960x1280;
/communities', on identical markup, is 2560x1707, which is why one looked broken
and the other looked fine.

Fix: take the media out of flow in dynamic-height mode, so it contributes nothing
to the row and covers whatever height the content produces. Two details cost a
round trip each and are written into the comment so they are not rediscovered:
(1) an <img> is a REPLACED element, so an absolutely positioned one with
`width: auto` takes its intrinsic width and ignores the opposing inset — `inset:`
alone positioned the image correctly but left it 1280px tall; top/left plus an
explicit calc size is what actually works. (2) Percentages on an out-of-flow box
resolve against the PADDING box, so the size subtracts twice the media padding to
land on the content box and keep the media-spacing gap and its drop shadow. The
padding is now a variable (`--ccc-hero-media-pad`) so the offset and the gap
cannot drift apart.

Measured in-browser rather than reasoned about, on a 1440x820 viewport: portrait
hero 960px → 409px (content height), landscape 296px, media gap exactly 24px on
three sides with spacing on and 0 with it off, no horizontal overflow; mobile
keeps its 16/9 media with the image filling it and the content stacked below.
Scoped to `.dynamic-height` — the 100vh path has the same latent behaviour but
nothing has asked for it to change, and there it only grows a block whose job is
to be tall.

## 2026-07-31 — Yoast FAQ block becomes an accordion (inc/faq-accordion.php)

Reverses the "deliberately not an accordion" call recorded one entry below —
Chris asked for collapsing FAQs with an animated marker, which is exactly the
"block-level decision, not a CSS afterthought" that entry said would be needed.
Built the way that entry demanded: `render_block_yoast/faq-block` rewrites the
render SERVER-SIDE into `<details>` / `<summary>` rows, so the browser is handed
a real disclosure widget rather than having JS re-parent Yoast's DOM after paint.
That buys implicit `aria-expanded`, keyboard operation and screen-reader support
from the platform instead of hand-maintained ARIA, and answers the original
objection (an accordion whose trigger is a bare `<strong>` is a control AT users
cannot operate) rather than ignoring it. **The `<strong>` is kept inside the
`<summary>`**, not swapped for a span — `strong.schema-faq-question` in blocks.css
is tag-qualified precisely so it stays off Yoast's editor markup, and that guard
had to survive.

`assets/faq-accordion.js` adds ONLY the animation: `<details>` cannot be
transitioned portably (a closed one gives its panel no box to animate out of), so
the JS owns the `open` attribute — set it first and grow from zero on the way
open, shrink to zero and only then unset it on the way shut. With JS off, broken
or still loading, every answer still opens; it just opens instantly. An
`is-closing` class drives the marker back at the START of a close so the chevron
travels with the panel instead of snapping after it, and `--ccc-faq-motion-duration`
is READ BY THE JS so timing lives in CSS next to the transition it must match.
Deep links still land: a closed row would swallow `#faq-question-…`, so the target
is opened and re-scrolled (the browser aimed at it while collapsed, leaving the
landing short by the height of the answer).

Fail-open by construction — every transform returns the original section unless it
matched cleanly, the accordion class is only added if at least one section
converted, and the `id` is required to match (it is the deep-link hook and what the
front-end-only `[id]` guard keys on). If Yoast changes its save output the worst
case is the old flat list, still styled and still deep-linkable. Schema is
untouched: Yoast builds its FAQPage graph from parsed block attributes, not from
the HTML — which is the whole reason rule 24 chose this block, and it survives.
Default ON for every site; `add_filter( 'ccc_faq_accordion', '__return_false' )`
keeps the open list. Nine new `--ccc-faq-*` knobs (glyph, size, colour, gap,
rotation, panel space, duration, hover wash). Verified against the real saved
markup of pages 488/489/491 — 11/11 sections converted, inner links and emphasis
preserved, id-less and malformed sections left untouched.

## 2026-07-31 — Classic City tracker skin, now the default
The client tracker got a full classiccity.com platformer reskin, then the skin became the default build. `build-tracker.py` gained a `--skin DIR` hook (importlib module: css + head/body injection) and now auto-resolves `skins/classic-city/` NEXT TO ITSELF — so a child adopts the look by copying one folder, `round-2.html` keeps its clean URL, and a missing skins/ folder degrades to a stderr warning + plain build, never a blocked deploy. `--no-skin` writes `{stem}.plain.html`. The skin itself, over five review rounds with Chris live: sky/cloud/hills scenery (viewport-fixed like the site; the cloud tile's own white bottom does the blending — two engineered gradient/mask attempts lost to Chris's three-line diagnosis, recorded in skin.py so nobody re-adds them), grass ground + blinking owl, headshot speech-bubble letter, HUD banner with the round pill as a hidden round-selector toggle, ink table headers, CCC fonts (Quicksand 400–700, Cultures Carnival, ArcadePixel) and every status tone derived from the site palette — brand orange = "your move" and nothing else, links sky blue to protect that. All assets inline (~460 KB single file, zero external requests); Kenney sprites CC0, fonts licensed via CCC. Base examples: `tracker.example.html` is now the skinned build, `tracker.example.plain.html` the escape hatch.

## 2026-07-31 — Tracker simplification pass: work-as-table, one "waiting on you" number, round letters
Two same-day revision rounds on `docs/client-tracker/` driven by Chris's live review. Round one: a round's work became a per-section TABLE (status glyph first, ref, item, detail, conditional held-on/added/page columns that only render when a row needs them), the Resolved section was deleted outright (`resolved_on` now just takes a blocker off the board; the changelog carries what happened), History flattened to one accordion layer, the palette cut to four colours + grey (blue `progress` tone deleted — amber still only ever means "your move"), links de-branded to bold ink + underline, Urgent chips and status dots removed. Round two: banner pods cut 5→2 (Review/Blocked — everything else was already said by the meter, tab badge, or section counts), expand-all and the generic intro deleted, the round lede rewritten as a signed letter pod (`note_by`/`note_on`, falls back to `project.author`), and the tab-badge-vs-heading count mismatch fixed at the root with `client_asks()` — the ONE definition of "waiting on you" (open client blockers + needs_client tasks, which now also render as cards). Retired fields (`severity`, `state`, `resolution`, `intro`) stay validated but draw nothing, so no child's data breaks. Print regression found and fixed: Chrome now hides collapsed `<details>` via `::details-content`/`content-visibility`, which the old `display:block!important` couldn't touch — page-count halving (14→7) is the cheap print test. Client example HTML now COMMITTED (gitignore is `*.internal.html` only) so the template is browsable without running Python.

## 2026-07-31 — Front-end styles for the Yoast FAQ block (assets/blocks.css)
Content rule 24 makes `yoast/faq-block` the house FAQ pattern on every CCC site, but Yoast registers an `editorStyle` and nothing else — there has never been any front-end CSS for it, so FAQs rendered as an unstyled run of bold text and paragraphs. New STRUCTURAL section at the end of `assets/blocks.css` (palette-agnostic, 0 hex / 0 rgb / 0 raw px, 18 `--ccc-faq-*` knobs, currentColor `color-mix()` for every tint so it works on light and dark children alike). Three decisions worth keeping: (1) the question is an inline `<strong>`, not a heading or a button, so `display: block` is load-bearing and it is styled to *read* as a heading without entering the document outline — the schema.org FAQPage data carries the semantics; (2) **deliberately not an accordion** — there is no `<details>`, `<button>` or `aria-expanded` in Yoast's output, so building one means JS rewriting Yoast's DOM, and an accordion whose trigger is a bare `<strong>` is a control keyboard and AT users cannot operate; if a collapsing FAQ is ever wanted it is a block-level decision, not a CSS afterthought; (3) every section carries an `id`, so `--ccc-faq-scroll-offset` gives deep links clearance under a fixed header and `:target` washes the row that was linked. Two traps handled: the answer IS a `<p>` and WP emits `:root :where(p){font-size:…}`, an explicit value that inherited sizes can never beat, so both element rules are tag-qualified at 0-1-1; and blocks.css is *also* an editor stylesheet while these class names belong to Yoast, whose editorStyle uses `.schema-faq-section{padding-left:32px}` as the gutter for its block-mover controls — the row rules are therefore written `.schema-faq-section[id]`, since Yoast's save output ids every section and its edit component does not. Verified against the real render of trialport page 491 plus an editor-shaped control: front-end rows styled, editor-shaped rows untouched.

## 2026-07-31 — Client-facing build/QA tracker template (docs/client-tracker/)
Generalised the working trialport QA tracker (`sg-trialport/docs/qa/`, untouched) into a sendable template in the parent: `build-tracker.py` + `tracker.example.json` + README. The prototype proved the shape but was unsendable — one long scroll full of file paths, block slugs and "Chris's ruling". New page pins blockers at the top in a round-tabbed panel (waiting-on-you first), makes rounds first-class accordions so round 4 stays readable, and collapses the changelog. Deliberately ONE JSON with two renders (`--internal` adds owner + effort + internal notes) rather than two data files: two files drift, and a drifted client tracker is worse than none. The client build never *emits* internal fields — not CSS-hidden, since hidden content in a downloadable file isn't hidden — and doesn't even ship the stylesheet rules for them, so the audit is `grep -c 't-owner\|internal-note'` → 0. Colour language is CSS custom properties with one rule: amber always and only means "waiting on the client", so "no amber = nothing on you" is a promise the page can keep. Lives in the parent as a TEMPLATE that children COPY into `sg-{slug}/docs/qa/` and never edit in place — the parent is subtree'd everywhere, so an edit here reaches every client. Generated HTML gitignored in the parent (it deploys publicly to every install); children commit theirs, since that file is the deliverable, but gitignore `*.internal.html`.

## 2026-07-30 — Route typeface selection to the Chief of Stuff font library
CLAUDE.md gained a "Route by task" row pointing at `$HOME/Chief of Stuff/assets/fonts/README.md`, so a build session checks Chris's licensed 126-family library before defaulting to Google Fonts. The library was consolidated into the CoS repo the same day (126 families, every one carrying a build-ready woff2) and is indexed by category, mood, weight, and body-vs-headline safety. Rule attached to the row: ship the one family's `{slug}/web/*.woff2` into the child theme, never copy the library — keeping the parent theme thin is why the fonts live in the vault repo rather than here. The index also flags caps-only faces, families missing accented characters, mono-named faces that aren't fixed-pitch, and the per-project Envato registration each font needs before a commercial launch. A parallel pointer lives in user-scope CLAUDE.md for repos that aren't this one.

## 2026-07-28 — Asana QA: comment the work back onto each ticket
ASANA_QA_PROCESS.md gained a Step 6 (bookkeeping renumbered to 7): every ticket touched gets an `asana_create_task_story` comment before its status flips, so the notification the reporter receives carries the explanation. Asana's activity log records that a status changed but never what shipped — without a comment, the work is invisible to the client on the board. Three comment shapes (done / already-live / blocked-with-the-question), client voice, no ticket numbers or file paths; posts during Step 5 execution after Chris's go, deduped against existing stories so a re-run can't double-post. User-level `asana-qa` skill summary updated to match.

## 2026-07-21 — MIGRATION_PIPELINE.md: canonical 8-stage site-migration process
Single source of truth for migrating an existing client site onto a child theme: intake → site capture → messaging → content inventory → page disposition (human gate) → block planning + enrichment checklist → build → QA. Stages 2–6 run CoS-side via the new /migrate-site command writing Clients/{Client}/migration/ artifacts; hands off to BULK_IMPORT at stage 7. Cross-wired into docs index, CLAUDE.md routing, BULK_IMPORT, and CONTENT_BUILDING_RULES.

## 2026-07-21 — Doc-drift fixes from the usage.md audit
Fixed four drift items the usage-layer audit surfaced: feature-grid and image-columns block.json descriptions claimed fixed 3-col layouts (fields allow 2–5), logo-strip's description omitted the marquee scroller mode, and stats claimed a baked-in brand gradient (background is the native picker, optional). Style-guide pattern: testimonial-cards sample seeded an invalid mobile_layout value ('stack' — valid values are column-count / horizontal-scroll), cta-thin's sample button_url '#' now points at /style-guide per content rule 11, and post-card gets a descriptor-only entry explaining its ancestor restriction to core/post-template. BLOCKS.md regenerated.

## 2026-07-21 — Semantic usage layer: per-block usage.md, enforced by the docs generator
All 35 blocks now carry blocks/{slug}/usage.md (Purpose / Content shape / Use when / Avoid when / Pairs with / Core alternative) — the manifest layer that lets content-import sessions map scraped or wireframed source onto blocks. generate-block-docs.php inlines them into BLOCKS.md (new Purpose column in the index) and exits 1 when any block lacks one, so a new block — parent or child theme — can't ship without its entry. New docs/CORE_BLOCKS_USAGE.md: core-first decision ladder, registered is-style-* inventory, canonical core patterns. Authoring checklist step 7 + content rules + CLAUDE.md wired.

## 2026-07-16 — Opted the parent repo into REPO_HANDOFF auto-logging

Added this BUILD_LOG.md, a `.cos.json` pinning client/repo for `cos-update`,
and a committed `.claude/settings.json` wiring the build-update-reminder hook.
The hook path differs from client repos: here the repo root IS the theme, so
the hook lives at `scripts/hooks/build-update-reminder.py` (the installer
script assumes the client-site layout and can't be used verbatim).

## 2026-07-16 — Child-theme CSS token-discipline checklist in THEME_TOKENS.md

Codified Chris's Tower Leadership review standard as a review checklist:
tokens over literals, snap to the shared scale over comp-exact custom tokens,
em for type-tracking component spacing, color-mix() alphas, rest-state
elevation under hover shadows, headings inherit (pair-helpers color per
surface). Only permitted literals: theme header + media-query breakpoints.
