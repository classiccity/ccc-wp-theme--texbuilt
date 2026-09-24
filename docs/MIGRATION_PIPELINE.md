# Site Migration Pipeline — Classic City Core

The canonical process for taking a client's **existing website** and rebuilding
its content on a Classic City child theme. Eight stages, each answering one
question and producing one artifact, running from the sales-close scaffold all
the way to a QA'd live site.

This file is the single source of truth for the **process**. It does not restate
block-level or build-level rules — those live in
[`CONTENT_BUILDING_RULES.md`](./CONTENT_BUILDING_RULES.md),
[`CORE_BLOCKS_USAGE.md`](./CORE_BLOCKS_USAGE.md), [`BLOCKS.md`](./BLOCKS.md), and
[`BULK_IMPORT.md`](./BULK_IMPORT.md). It runs as the **CONTENT track** alongside
the infrastructure track in [`CLIENT_ONBOARDING.md`](./CLIENT_ONBOARDING.md)
(Phases 1–12); the two tracks meet at stage 7.

---

## Stage-artifact summary

| # | Stage | Question it answers | Artifact | Runs |
|---|---|---|---|---|
| 1 | INTAKE | Who is this client, where does their folder live? | The scaffolded client folder (`README.md`, `tracker.md`, …) | CoS |
| 2 | SITE CAPTURE | What is actually on the existing site today? | `migration/site-capture/` — `sitemap.md`, `pages/{slug}.md`, `screenshots/{slug}.png`, `metadata.md` | CoS |
| 3 | MESSAGING DIRECTION | What should the site *say* — the StoryBrand spine? | `proposals/02-messaging-direction.md` | CoS |
| 4 | CONTENT INVENTORY | Where does each URL and each embedded item route? | `migration/content-routing.md` | CoS |
| 5 | PAGE DISPOSITION | Scrap, port, or rework each page — and merge into what? | `migration/page-disposition.md` | CoS (human gate) |
| 6 | BLOCK PLANNING + ENRICHMENT | Which block does each surviving section become? | `migration/block-plans/{slug}.md` | Either side |
| 7 | BUILD | Get it into WordPress. | (client repo — see [`BULK_IMPORT.md`](./BULK_IMPORT.md)) | Client repo |
| 8 | QA | Does it hold up, and what did we learn? | (client repo — QA loop + rule mirror-up) | Client repo |

Every path in stages 1–6 is relative to the **CoS client folder**
`~/Chief of Stuff/Brain/Chief of Stuff/Classic City/Clients/{Client}/` (always
`~`-relative — Chris works on two machines with different usernames).

---

## Where things live

- **Process** — this file. One source of truth for how a migration runs.
- **Per-client artifacts** — the CoS client folder above. Stages 1–6 write here;
  git is the wrong tool for heavy media and business context, so this never
  moves into the build repo.
- **The bridge** — the child theme's `CONTENT-SOURCES.md` (spec in
  [`CONTENT_BUILDING_RULES.md`](./CONTENT_BUILDING_RULES.md)) points the build
  repo at this client folder, so stage 7 finds every stage-1–6 artifact without
  hunting.
- **CoS entry point** — the `/migrate-site` command in the Chief of Stuff repo
  orchestrates stages 2–6 (INTAKE is `/new-client`). Start a migration there.

---

## Stage 1 — INTAKE  (CoS)

- **Question:** Who is this client, and where does their folder live?
- **Artifact:** the scaffolded client folder at
  `~/Chief of Stuff/Brain/Chief of Stuff/Classic City/Clients/{Client}/`
  (`README.md`, `tracker.md`, `content/`, `proposals/`, …).
- **Inputs:** the closed deal.
- **How:** run `/new-client {Client}` in the Chief of Stuff repo. Already exists.
- **Done when:** the folder exists with a populated `README.md` (running context,
  voice, URLs, stakeholders).
- **Handoff:** the folder is the write target for every later CoS-side stage.

## Stage 2 — SITE CAPTURE  (CoS)

- **Question:** What is actually on the existing site today?
- **Heavy-media note (learned on the first live run, Tower 2026-07-21):**
  `screenshots/` stays **gitignored and local to the capturing machine** — a
  full capture runs ~80 MB of JPEGs, and git is the wrong tool for heavy media
  (same rule as client assets). Markdown artifacts commit and sync; stage 6
  must therefore run on the machine that captured, or re-capture screenshots.
- **Artifact:** `migration/site-capture/`, containing:
  - `sitemap.md` — full URL inventory, including orphans and redirects.
  - `pages/{slug}.md` — clean per-page markdown (one file per URL).
  - `screenshots/{slug}.png` — full-page screenshot per URL.
  - `metadata.md` — per-URL table of title / meta description / H1 / HTTP status.
- **Inputs:** the live site URL.
- **How:** tool-agnostic — Firecrawl, a browser tool, whatever crawls cleanly.
  **The FORMAT above is the contract, not the tool.** Screenshots are
  **required**: markdown is layout-lossy, and the stage-6 block planner is
  multimodal and reads them.
- **Done when:** every URL in `sitemap.md` has a matching `pages/*.md` and
  `screenshots/*.png`, and `metadata.md` covers every row.
- **Handoff:** the raw corpus stages 4–6 read from.

## Stage 3 — MESSAGING DIRECTION  (CoS)

- **Question:** What should the site *say* — the StoryBrand spine?
- **Artifact:** `proposals/02-messaging-direction.md`.
- **Inputs:** `/prep-sales-call` output + discovery-call transcripts.
- **How:** already produced by the sales-prep workflow (StoryBrand lens: hero
  headline, homepage section direction). This pipeline **consumes** it — it is
  the yardstick stages 5 and 6 judge pages against.
- **Done when:** it exists and reflects the discovery calls.
- **Handoff:** the editorial north star for disposition and block planning.

## Stage 4 — CONTENT INVENTORY  (CoS)

- **Question:** Where does each URL route, and what collection content is buried
  inside the pages?
- **Artifact:** `migration/content-routing.md`.
- **Inputs:** `migration/site-capture/` (stage 2).
- **How:** two passes.
  1. **Per-URL routing** — each URL routes to a **page** or a **CPT** (blog
     posts, team, the testimonial CPT, document downloads).
  2. **Embedded-collection harvest** — pull collection content quoted *inside*
     pages (testimonials on service pages, bios on the about page) into their CPT
     lanes. **Deduplicate corpus-wide:** the same quote on three pages is **one**
     entry with three page references, not three entries.
- **Done when:** every URL has a route, every embedded item is harvested and
  deduped, and a human has reviewed it. **Nothing imports before this review.**
- **Handoff:** stage 6 pulls proof (testimonials, stats) from this harvested,
  deduped corpus.

## Stage 5 — PAGE DISPOSITION  (CoS — human gate)

- **Question:** For each page: scrap, port, or rework — and merge into what?
- **Artifact:** `migration/page-disposition.md` — a ranked table.
- **Inputs:** `proposals/02-messaging-direction.md` (3), `sitemap.md` (2), and analytics when
  available.
- **How:** per page, one verdict:
  - **scrap** — does not survive the migration.
  - **port** — survives (still runs enrichment, stage 6 — port is never a
    photocopy).
  - **rework** — survives but the message/structure changes materially.
  - Plus a **merge-into** target where an old sprawling site consolidates
    several pages into one.
  Ranked table Chris dictates over — this is a **human gate**, not an automated
  verdict.
- **Done when:** every page has a verdict and merge targets are named; Chris has
  signed off.
- **Handoff:** stage 6 plans blocks only for **port** and **rework** survivors.

## Stage 6 — BLOCK PLANNING + ENRICHMENT  (either side — needs the theme docs)

- **Question:** Which block does each surviving section become?
- **Artifact:** `migration/block-plans/{slug}.md` — ~10 lines per surviving page.
- **Inputs:** the survivor's `pages/{slug}.md` **and** `screenshots/{slug}.png`
  (stage 2), `migration/content-routing.md` (4), `migration/page-disposition.md` (5).
- **How:** map each source section → a block, chosen via the **usage layer** in
  [`BLOCKS.md`](./BLOCKS.md) and the core-first ladder in
  [`CORE_BLOCKS_USAGE.md`](./CORE_BLOCKS_USAGE.md). Read the markdown and the
  screenshot **together** — the screenshot is **source intent, never target
  design** (do not photocopy the old layout).
  **Every port and rework page also runs the ENRICHMENT CHECKLIST below.** Every
  enrichment is flagged in the plan as an **ADDITION**, so review can tell
  original content from editorial additions at a glance.
- **Side effect:** each plan emits a per-page **image-needs list** (every
  enrichment that adds a visual slot lands here).
- **Done when:** every survivor has a plan mapping all sections to blocks, all
  additions are flagged, and the image-needs list is compiled.
- **Handoff:** the plans + image-needs lists feed the build (stage 7). This is
  the last CoS-side stage; it may also run in the client repo since it needs the
  theme docs.

### The enrichment checklist

Run this on **every port and rework page**. "Port" never means photocopy — a
straight lift of dated content onto new blocks wastes the rebuild. Each item
below is an editorial ADDITION; flag it as such in the plan.

1. **Long uninterrupted text run** (more than ~3 paragraphs) → break it at a
   topic seam into a [`split-50-50`](./BLOCKS.md#split-50-50) or an
   intro-group + block (the section-intro pattern, `CORE_BLOCKS_USAGE.md`).
   Each break **ADDS an entry to the image-needs list**.
2. **No conversion point within a scroll-depth** → insert a
   [`cta-thin`](./BLOCKS.md#cta-thin) at the natural pause; respect the
   page-level CTA hierarchy (rule 6, `CONTENT_BUILDING_RULES.md`).
3. **Claims without nearby proof** → pull from the stage-4 harvested collections:
   place [`testimonial-cards`](./BLOCKS.md#testimonial-cards) or
   [`stats`](./BLOCKS.md#stats) adjacent to the assertion.
4. **Q&A-shaped prose** → a `yoast/faq-block` (rule 24) — never `core/details`
   accordions or heading/paragraph runs.
5. **Feature lists as text walls** → an icon-card treatment (rule 20), with
   **real FontAwesome icons only** (rule 12) — never placeholder icon names.

## Stage 7 — BUILD  (client repo)

- **Question:** How does the plan become live WordPress content?
- **Runs in:** the client build repo, on existing machinery. This pipeline
  **hands off here and does not restate those rules.**
- **Read there:** [`BULK_IMPORT.md`](./BULK_IMPORT.md) (import mechanics), the
  child theme's `AUTHORING_PATTERNS.md` (create from the Lumberock reference if
  missing), `BUILD_LOG.md` (the decision journal), and `CONTENT-SOURCES.md` (the
  bridge back to the stage-1–6 artifacts).
- **Inputs:** `migration/block-plans/{slug}.md` + `migration/image-needs.md` (6), the routed CPT
  content (4), `migration/site-capture/pages/*.md` (2).
- **Done when:** the pages and CPTs are built and deployed per
  [`CLIENT_ONBOARDING.md`](./CLIENT_ONBOARDING.md) Phases 11–12.

## Stage 8 — QA  (client repo)

- **Question:** Does it hold up, and what did we learn?
- **Runs in:** the client build repo — the Marker.io / `asana-qa` loop
  ([`ASANA_QA_PROCESS.md`](./ASANA_QA_PROCESS.md)).
- **How:** work the QA tickets; **learnings append to the child theme's
  `AUTHORING_PATTERNS.md`**, and any learning whose generic half applies to
  every site **mirrors up to `CONTENT_BUILDING_RULES.md`** in the same session —
  the two-tier protocol documented there.
- **Done when:** QA tickets are resolved and rule learnings are filed at both
  tiers.

---

**Related:** [`CLIENT_ONBOARDING.md`](./CLIENT_ONBOARDING.md) (the infra track
that runs in parallel) · [`CONTENT_BUILDING_RULES.md`](./CONTENT_BUILDING_RULES.md)
(the law) · [`CORE_BLOCKS_USAGE.md`](./CORE_BLOCKS_USAGE.md) ·
[`BLOCKS.md`](./BLOCKS.md) · [`BULK_IMPORT.md`](./BULK_IMPORT.md) ·
[`PAGE_BUILD_TRACKER.md`](./PAGE_BUILD_TRACKER.md) (the progress deliverable a
build produces).
