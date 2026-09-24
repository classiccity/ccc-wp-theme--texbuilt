# Classic City Core — Docs

Documentation for the shared parent theme and the client-onboarding
workflow that depends on it.

## Contents

- **[CONTENT_BUILDING_RULES.md](./CONTENT_BUILDING_RULES.md)** — the
  non-negotiable rules for authoring/importing page content and building
  blocks (core-blocks-first, no tag overrides, ACF Foundation/Theme split,
  CSS variables, button hierarchy). **Read before any content or block work.**

- **[ARCHITECTURE.md](./ARCHITECTURE.md)** — why the multi-repo +
  parent/child + subtree setup looks the way it does. Read this first
  if you're new to the project.

- **[CLIENT_ONBOARDING.md](./CLIENT_ONBOARDING.md)** — step-by-step
  runbook for spinning up a new client site end-to-end: WP Engine
  install, GitHub repo, Local mirror, parent-theme subtree wiring,
  child-theme scaffolding, deploy setup, content workflow. Includes a
  pitfalls table and the automation roadmap. (The automation lives in
  `scripts/onboard-client/` — all phases are implemented.)

- **[BLOCK_AUTHORING.md](./BLOCK_AUTHORING.md)** — how to add or edit a
  block correctly: the three defensible patterns, folder anatomy +
  auto-registration, the `sg-hero` marker rule, the
  `acf-innerblocks-container` layout trap and its canonical fix, shared
  partials, field-key discipline, and the new-block checklist.

- **[BLOCKS.md](./BLOCKS.md)** — **generated** per-block registry: every
  block's kind (hybrid vs field-driven), every ACF field name + key, AND
  the block's inlined `usage.md` (Purpose / Content shape / Use when /
  Avoid when — the semantic layer content-import sessions map with).
  Regenerate with `npm run docs:blocks`; never edit by hand — edit the
  block's own `blocks/{slug}/usage.md` and regenerate. The run FAILS if
  any block lacks a usage.md.

- **[CORE_BLOCKS_USAGE.md](./CORE_BLOCKS_USAGE.md)** — when NOT to reach
  for an ACF block: the core-first decision ladder, the registered
  `is-style-*` inventory, canonical core patterns (section-intro group,
  banding, two-column headers), and the "when core is wrong" list.

- **[THEME_TOKENS.md](./THEME_TOKENS.md)** — the color/type/spacing token
  system and how a child `theme.json` overrides it: the palette
  (brand pairs + the canonical `canvas`/`panel`/`ink` neutrals + gray
  scale + opposites), gradients, fonts, heading-case override, and the
  theme.json merge rules (palette/gradients/fontFamilies REPLACE,
  `custom`/`styles` deep-merge). **Read this for the `light`/`dark` →
  `canvas`/`panel`/`ink` story** — why the legacy slugs still exist and
  how to build a new child on the canonical neutrals.

- **[CLIENT_HOMEPAGE.md](./CLIENT_HOMEPAGE.md)** — authoring a real
  client page from the block library and getting it live on WPE: ACF
  block markup format + where to get field keys, hybrid vs field-driven
  blocks, section background/container conventions, sideloading images to
  attachment IDs, pushing the page + setting the front page via wp-cli,
  rendering-verification with `do_blocks`, and page snapshots. (Distinct
  from PROSPECT_DEMO, which is static sales collateral.)

- **[SITES.md](./SITES.md)** — the authoritative registry of real client
  sites (name / Local folder / repo / WPE install / theme model), plus
  the ground rules: never copy an old site as a baseline, the sandbox
  `sg-*` copies are stale, Georgia SEB is never a sample. Append a row
  at Phase 3 of every new build.

- **[CHIEF_OF_STUFF_HANDOFF.md](./CHIEF_OF_STUFF_HANDOFF.md)** — the
  agent contract between Chris's Chief of Stuff system and this repo.
  Defines the client-config YAML schema CoS produces when a deal closes,
  the two-track execution model, stop conditions, and the canonical
  command surface. Worked example handoff at
  [`client-config.example.yaml`](./client-config.example.yaml).

- **[WPE_PLATFORM.md](./WPE_PLATFORM.md)** — **canonical** WP Engine
  platform-behavior reference: the three access systems (SSH Gateway /
  Git Push / REST) and their separate key registrations, SSH gateway
  limits (scp blocked, quoting mangled, sessions killed, concurrency
  throttled), WAF blocks, Varnish behavior, deploy semantics. If any
  runbook disagrees with this file, this file wins.

- **[BULK_IMPORT.md](./BULK_IMPORT.md)** — runbook for importing
  hundreds-to-thousands of CPT rows into a client install (products,
  team members, news posts, etc.). Covers the REST-vs-wp-cli decision,
  state tracking for resumability, image pre-processing, WPE-specific
  gotchas (WAF + UA blocking, SSH quote mangling), and a Python
  starter skeleton. Includes a hybrid fallback when REST is blocked.

- **[MIGRATION_PIPELINE.md](./MIGRATION_PIPELINE.md)** — the canonical
  8-stage pipeline for rebuilding an existing client site's content on a
  child theme: INTAKE → SITE CAPTURE → MESSAGING DIRECTION → CONTENT
  INVENTORY → PAGE DISPOSITION → BLOCK PLANNING+ENRICHMENT → BUILD → QA.
  Stages 1–6 run Chief-of-Stuff-side (entry point `/migrate-site`) writing
  per-client artifacts; stages 7–8 hand off to `BULK_IMPORT.md` and the QA
  loop. The single source of truth for migration PROCESS.

- **[PAGE_BUILD_TRACKER.md](./PAGE_BUILD_TRACKER.md)** — how to generate a
  per-client content-migration progress tracker: a self-contained HTML page
  (built vs not, real copy in, images in, copy/image confidence 0–100, with
  old→new URLs in each accordion) that you email to the client as a progress
  deliverable. Data comes free from a Phase-12 import; includes the confidence
  formulas, a generator skeleton, and a per-site adaptation checklist.
  Reference implementation in `sg-lumberock/content-files/image-library/`.

- **[client-tracker/](./client-tracker/README.md)** — the **template** for a
  client-facing build/QA status page you can send a client a link to: a
  generator (`build-tracker.py`), an example/schema JSON, and the docs. The
  round tabs live in the sticky banner and switch the **whole** page: waiting
  on you / waiting on us, the work, a resolved table and that round's full
  history, all grouped by the page they belong to (site-wide first). Icons are
  inlined FontAwesome SVG so the file stays self-contained, and the colour
  language is documented in CSS custom properties (amber always and only
  means "waiting on the client"). **One JSON, two render modes** — a client
  build and `--internal`, which adds task ownership, effort and internal notes
  and is never deployed. Children **copy** this folder into
  `sg-{slug}/docs/qa/`; the template in the parent is never edited by a child.
  Distinct from PAGE_BUILD_TRACKER, which reports content-migration progress.
  **Changing the generator itself?** Read
  [client-tracker/DEVELOPING.md](./client-tracker/DEVELOPING.md) first —
  architecture, the decision log, and the invariants that must not regress.

- **[PROSPECT_DEMO.md](./PROSPECT_DEMO.md)** — runbook for building a
  flat-HTML "wow" demo for a potential client without spinning up WP
  Engine or a real child theme. Demo files live in `~/Demos/{slug}/`
  outside this repo; the runbook captures the brand-to-token mapping
  pattern, the section→block map, the "defend any new block" rule,
  and delivery as a zip. Reference example: `~/Demos/trialport/`.

- **[ROADMAP.md](./ROADMAP.md)** — ranked backlog of code-level
  improvements queued for pickup (child-theme generator v2 on the
  canvas/panel/ink model, onboarding automation gaps, blocks.css token
  sweep, CI, and the deliberate-decision items). Each item is written so
  a fresh session can start cold; work top-down, delete sections as they
  land.

- **[MOCKUP_KIT_PLAN.md](./MOCKUP_KIT_PLAN.md)** — deferred plan for a
  static export of the `/style-guide` page that you can rearrange and
  re-skin in minutes — the "next compression" of the PROSPECT_DEMO
  workflow from ~30 min to ~5 min. Three tiers (export-and-paste kit,
  interactive picker, hosted mockup tool) with implementation
  pointers and open decisions captured for pickup.

- **[ASANA_QA_PROCESS.md](./ASANA_QA_PROCESS.md)** — Chris's standard
  Marker.io QA workflow for a client site: connect to the Asana board,
  read each ticket's annotated screenshot, triage into three confidence
  tables, draft the client email, execute the fixes (theme + wp-cli
  content deploy), and update the Asana Task Status field. Kicked off by
  saying "let's do QA on this Asana" (backed by the user-level `asana-qa`
  skill, which just reads this file).

## Keeping docs current

When you run an onboarding and something doesn't match the runbook,
update `CLIENT_ONBOARDING.md` in the same PR / commit that captures the
fix. The runbook has a `## Change log` section at the bottom — append a
short note dated entry there whenever content shifts.

**Deprecation rule:** when a convention is retired, the same commit must
fix or banner *every* doc and code comment that teaches the old way —
never correct doc A only by adding a warning in doc B.

**Automated drift check:** `npm run docs:check` verifies every relative
link in `docs/` + `CLAUDE.md`, every `.md` file referenced from code
comments, and that `docs/BLOCKS.md` matches `blocks/`. Run it after doc
edits; `npm run docs:blocks` regenerates the block registry.
