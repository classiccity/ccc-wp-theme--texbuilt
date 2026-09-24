# Roadmap — code improvements queued for pickup

Ranked backlog of code-level improvements to this parent theme and its
tooling. Each item is written so a fresh session can start cold: context,
pointers, acceptance criteria. Audited 2026-07-09 (docs overhaul session);
the measurements quoted below are from that date.

Last verified: 2026-07-09.

**Protocol:** work top-down unless Chris says otherwise. When an item
lands, delete its section (git history keeps it) and update every doc the
change touches **in the same commit** — see "Deprecation discipline" in
[`../CLAUDE.md`](../CLAUDE.md). If you discover new items, add them in
rank order with the same structure. This file absorbs the
`standardization-direction` memory — the repo copy is canonical.

---

## 1. Child-theme generator v2 — generate on the canvas/panel/ink model

**Status:** not started · **Effort:** one focused session · **Value:** highest

**Problem.** The agreed direction is *generate child themes, don't copy
them* — but the only generator (`wp style-guide new-client` →
`inc/class-ccc-client-importer.php`, plus `scripts/onboard-client/phases/08-child-theme.ts`
and `config/defaults.json`) emits the **retired light/dark model** and is
deprecation-bannered. Track B of every build is therefore hand-authored
(3 files per client, error-prone, slow).

**The fix.** Rewrite generation on the canonical model per
[`THEME_TOKENS.md`](./THEME_TOKENS.md):

- **Input:** the schema-2.0 `client-config.yaml`
  ([`CHIEF_OF_STUFF_HANDOFF.md`](./CHIEF_OF_STUFF_HANDOFF.md) — brand
  pairs with `"auto"` alts, `neutrals.mode: light|dark`, `gray_tint`).
- **Ramp:** port `buildRamp(tintBase)` from sitemap-studio
  (`~/Local Sites/sitemap-studio/lib/brand.ts`) — the tinted
  `gray-10…100` generator. Small, pure function; port it into the
  generator rather than depending on the other repo.
- **Output:** `sg-{slug}/` containing `theme.json` (palette pairs +
  canvas/panel/ink/ink-soft bound to the ramp, opposites as gray-ramp
  refs, inherit parent gradients — don't re-declare arrays, **and a
  `settings.shadow.presets` block — REQUIRED**: the parent deliberately
  ships only the elevation *role* tokens, not the sm/md/lg/xl preset
  values; the contract is *parent owns the vocabulary, child owns the
  values*, so a child without its own presets breaks every
  `--wp--custom--elevation--*` reference. Chris explicitly rejected
  parent-as-single-source — children override theme.json broadly by
  design),
  `functions.php` (child bootstrap; webfont enqueue only if non-system
  fonts), `style.css` (header + empty client-CSS section), `CLAUDE.md`
  (from the sg-sherman-phalen structure), and an `AUTHORING_PATTERNS.md`
  stub (two-tier system, [`CONTENT_BUILDING_RULES.md`](./CONTENT_BUILDING_RULES.md)).
- **References:** `sg-sherman-phalen` (light) and `sg-trialport` (dark),
  each in its OWN client repo — registry in [`SITES.md`](./SITES.md).
  Never the stale sandbox `sg-*` copies.
- **Where it lives:** either revive the `wp style-guide new-client` CLI
  (PHP, runs in the sandbox) or a TS module in `scripts/onboard-client/`
  feeding Phase 8. Prefer wherever the YAML already is — probably TS, so
  Track A and Track B share one config load.

**Acceptance:** generating sherman-phalen's config reproduces a
theme.json equivalent to the real `sg-sherman-phalen/theme.json`; same
for trialport (dark). Then: delete `class-ccc-client-importer.php`, the
`new-client`/`import` CLI subcommands (keep `port-textures` — still
current), `08-child-theme.ts`'s copy logic, and `config/defaults.json`;
update `BLOCK_AUTHORING`/`CHIEF_OF_STUFF_HANDOFF` Track B, the command
surface table, `CLIENT_ONBOARDING` Phase 8, and `../CLAUDE.md`
non-negotiable #5 in the same commit. Run `npm run docs:check`.

---

## 2. Onboarding automation gaps (three small items)

**Status:** not started · **Effort:** ~1 hour combined · **Value:** high, immediate

All in `scripts/onboard-client/`. State shape: `lib/state.ts`;
runbook: [`CLIENT_ONBOARDING.md`](./CLIENT_ONBOARDING.md).

- **2a. Phase 10 — activate the child theme.** One SSH command:
  `ssh {install}@{install}.ssh.wpengine.net "cd /home/wpe-user/sites/{install} && wp theme activate sg-{slug}"`.
  New `phases/10-activate.ts` + npm script + state record + verify
  (active theme == `sg-{slug}` via `wp theme list --status=active`).
  Has been "⏳ next" in the runbook since April.
- **2b. `adopt` command.** When Chris created the WPE install by hand,
  Phase 1 collision-errors and [`NEW_SITE_CHECKLIST.md`](./NEW_SITE_CHECKLIST.md)
  tells the agent to hand-author `state/{slug}.json`. Script it:
  read-only WPE API verify (install exists, status, URLs) → seed the
  state file exactly as Phase 1 would have. `npm run adopt -- {slug}`.
- **2c. `--config client-config.yaml`.** The driver
  (`onboard-client.ts`) has a hardcoded `CONFIG` constant edited per
  client (the handoff doc has anticipated `--config` since May; Chris's
  working tree usually carries an inline-config edit as evidence of the
  friction). Parse the schema-2.0 YAML, validate per the handoff
  preflight rules, derive per-phase inputs. Update
  `CHIEF_OF_STUFF_HANDOFF.md` Track A text when done.

---

## 3. Finish the token/elevation sweep in `assets/blocks.css`

**Status:** partially done (June 2026 standardization pass) · **Effort:** half a session + visual check · **Value:** medium-high

**Governing principle (agreed):** presentation is either **author-chosen**
(bg color, gradient — Gutenberg block supports; editor-injected
`has-{slug}` classes are fine) or **system-owned** (elevation, link
colors, radius, spacing rhythm — semantic token + CSS, never a hardcoded
palette class in render output). Children retune by remapping the token,
no `!important` war.

**Already done (don't redo):** all 12 resting card/panel surfaces route
through `var(--wp--custom--elevation--card, …)`; the parent/child shadow
contract landed (parent = role tokens only, child = preset values — see
item 1's output note); `partials/image-card.php` had its hardcoded
`has-secondary-color` / `has-cta-color` stripped in favor of semantic
classes.

**Remaining, needs per-case visual decisions** (values currently differ
— that's why it wasn't finished mechanically):

- Hover lifts use an ad-hoc md/lg/xl mix (link-pod + image-link-cards
  = md; doc + image-tile = lg; image-wall + portfolio = xl). Decide a
  role (e.g. `elevation.raised`) per family, then migrate.
- Overlays (overlay-content, hero-full-card = xl) → `elevation.overlay`.
- Hand-rolled raw shadows at the scroller edge + modal (`0 20px 60px`),
  and a mismatched fallback at image-link-cards:hover (`0 4px 10px`).
- The hardcoded blue focus ring `.sg-inspector-text:focus`
  (`rgba(59,130,246,…)`) ignores the palette.
- ~43 raw hex literals overall (audit each; some are legit shadow
  `rgb()` colors).

**Intentionally left — do NOT "fix" without the prerequisite work:**

- Button bg classes hardcoded at `blocks/hero-3-up/render.php` and
  `partials/feature-detail-section.php` are **entangled with the
  palette-driven hover-CSS generator in `inc/enqueue.php`** (it keys off
  `has-{slug}-background-color`). Stripping them without redesigning
  that generator breaks button hovers.
- The intentionally-subtle `sm` cases (`.is-style-section`, open FAQ,
  step-number badge) are not cards — leave them.

The file must stay **palette-agnostic** (works on light AND dark
children; `sg-trialport` is the dark reference). **Verify on
`/style-guide`** on a light child and a dark child before committing.

**Parked, needs Chris's explicit go:** the `partials/` redesign
(whether "card" is one partial-with-variants or a small family) was
never processed — **do not build or refactor partials** as part of this
item.

---

## 4. CI — the repo has no `.github/` at all

**Status:** not started · **Effort:** under an hour · **Value:** high leverage

One workflow (`.github/workflows/checks.yml`) on push/PR:

1. `npm run docs:check` (drift linter — `scripts/docs-check.mjs`).
2. `php -l` across all tracked `.php` files.
3. BLOCKS.md freshness: `npm run docs:blocks && git diff --exit-code docs/BLOCKS.md`.

This converts "agents are told to run the linter" into "drift is
uncommittable." Note for step 3: `scripts/generate-block-docs.php` needs
a PHP binary — trivial on ubuntu-latest (`shivammathur/setup-php` or the
preinstalled php), see item 6a for the local-machine equivalent.
Optional: a local pre-commit hook running the same three.

---

## 5. Deliberate-decision items (don't start without Chris's go)

### 5a. Split `blocks.css` into per-block stylesheets

`assets/blocks.css` is a 4,745-line monolith loaded on every page for
all 35 blocks; a typical page uses 6–8. `block.json` supports a per-block
`style` handle that WP loads only when the block renders. Real payload
win, but: it's a large refactor, shared/base rules (reset, section
rhythm, hscroll, style-guide chrome) need a home, and the **Next.js
style-guide mirror syncs against the single file** (see the header
comment in `blocks.css` re `npm run sync-blocks-css`). Do it as its own
project or not at all. If done: `BLOCK_AUTHORING.md` §CSS and
`../CLAUDE.md` CSS conventions must be rewritten in the same commit.

### 5b. `inc/class-ccc-style-guide-admin.php` — finish or trim

1,462 lines; currently a read-only token diagnostic plus save hooks
(`ccc_sg_save_palette`, `ccc_sg_save_typography`, …) that are registered
but unimplemented ("Phase B3.2a" of an editor that stalled). It reads as
more capable than it is — agent-bait. Either implement the editor phases
or trim to the diagnostic and delete the dead hooks. Trimming is the
cheap default; ask Chris which.

---

## 6. Small items

- **6a. `npm run docs:blocks` assumes `php` on PATH.** On this machine
  PHP comes from Local (`~/Library/Application Support/Local/lightning-services/php-*/bin/darwin-arm64/bin/php`).
  Make the npm script (or a tiny wrapper) resolve: `php` on PATH →
  newest Local lightning-services binary → error with instructions.
- **6b. `scripts/onboard-client/state/aspglobal-sitemap.md`** — a 13KB
  markdown note parked in the JSON-state directory (untracked). It's
  Chris's file: ask, then move it to the aspglobal client repo or
  delete. Don't act without asking.
- **6c. Smoke-render test (CI stage 2).** Render every block via
  `wp eval 'do_blocks(...)'` over the style-guide pattern
  (`patterns/style-guide.php`) on the sandbox install and fail on PHP
  notices/warnings. Catches broken `render.php` before it ships to a
  client. Needs a WP runtime in CI (wp-env) or stays a local pre-push
  script against the sandbox — decide when picking up.

---

## Done (context for the ranking above)

- **2026-07-09** — docs overhaul: CLAUDE.md rewritten as thin router;
  BLOCKS.md generated registry + `docs:check`/`docs:blocks` linters;
  BLOCK_AUTHORING/WPE_PLATFORM/SITES docs created; handoff schema v2.0
  (canvas/panel/ink); deprecation banners on the old-model generator
  (the code item 1 replaces); two-tier content-rules system codified
  (CONTENT_BUILDING_RULES + per-site AUTHORING_PATTERNS.md).
