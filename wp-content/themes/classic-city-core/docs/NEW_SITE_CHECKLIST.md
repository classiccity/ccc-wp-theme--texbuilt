# New Site Build — Kickoff Checklist

**This is the first thing the agent outputs when Chris says "we're building a
new site" and hands over the config/docs.** Show this checklist back to him
*before* doing anything so you're both on the same page. Chris got burned on the
last few builds by the agent hunting across docs and referencing the wrong
(stale sandbox) child themes — this doc exists so that never happens again.

Deeper detail: [`CHIEF_OF_STUFF_HANDOFF.md`](./CHIEF_OF_STUFF_HANDOFF.md) (agent
contract) · [`CLIENT_ONBOARDING.md`](./CLIENT_ONBOARDING.md) (phase prose) ·
[`THEME_TOKENS.md`](./THEME_TOKENS.md) (color/token model).

---

## Kickoff protocol — the agent's FIRST response, every time

1. **Output this checklist** (the Steps + Phases below) so Chris can confirm
   we're aligned.
2. **Put Chris's GitHub public key on his clipboard** — `pbcopy < ~/.ssh/id_ed25519.pub`.
   He pastes it into the new WP Engine install's **Git Push tab** (and SSH
   Gateway tab). This is a common early action; do it up front.
3. **Read the docs he sent** in the kickoff message (typically the client-config
   YAML + a build/content map).
4. **Ask clarifying questions** before starting the build.
5. Only then begin.

**Ground rules (do not violate):**
- Build the child theme on the **canvas/panel/ink + gray-ramp** model
  ([`THEME_TOKENS.md`](./THEME_TOKENS.md)). The `wp style-guide new-client` CLI
  and `08-child-theme.ts` are **stale** (old light/dark) — don't use them.
- **Never copy an old client site as a baseline.** Reference the registry
  ([`SITES.md`](./SITES.md)) to confirm facts; build from the documented
  reference (`sg-sherman-phalen` light / `sg-trialport` dark) in their OWN repos,
  never the stale `sg-*` copies next to the parent in the sandbox.
- Georgia SEB (`georgiaseb`) is heavily customized — **never** a sample.

---

## The Steps (high-level arc)

- **Step 0 — Kickoff:** run the protocol above (checklist → public key → read →
  clarify).
- **Step 1 — Preflight (read-only):** `gh auth status`, `ssh -T git@github.com`,
  WPE creds at `~/.config/wpe/credentials.env`, plugin stash at
  `~/Downloads/default-plugins/`. Reconcile what already exists (did Chris make
  the WPE install by hand? does the repo exist? has Local been pulled?) — verify,
  don't assume the script created it.
- **Step 2 — Infrastructure (Track A):** run Phases 1–9 (below).
- **Step 3 — Log the site** in [`docs/SITES.md`](./SITES.md) (commit the row) —
  **at Phase 3**, when the GitHub repo is created. That's the moment a site
  enters the registry. (Also refresh the `sites-registry` memory if present —
  the repo file is canonical, memory is a cache.)
- **Step 4 — Child theme (Track B):** hand-author `sg-{slug}` on the token model
  (feeds Phase 8).
- **Step 4½ — Build-update hook:** run
  `python3 wp-content/themes/classic-city-core/scripts/hooks/install-build-update-hook.py`
  from the site root, whitelist `/.claude/settings.json` in the repo's
  `.gitignore` (copy the stanza from any existing client repo), and commit the
  settings file. This wires the Chief of Stuff build-update reminder
  (parent `CLAUDE.md` § "Build updates → Chief of Stuff") into every session
  on every machine.
- **Step 5 — Style Guide site (SOW step 1):** restyle the ~15 blocks to the brand
  (this is the discovery gate).
- **Step 6 — Content build:** nav + footer → hand-crafted pages → block-assemble
  the rest → blog import → media/screenshots. Content work follows
  [`CONTENT_BUILDING_RULES.md`](./CONTENT_BUILDING_RULES.md) + the site's
  `sg-{slug}/AUTHORING_PATTERNS.md` (create it at the start of content work —
  copy Lumberock's structure; append QA rules per round, mirror generic halves
  up to the parent rules). Log every decision-with-a-why (content AND design)
  in `sg-{slug}/BUILD_LOG.md`, newest-first — spec in
  [`CONTENT_BUILDING_RULES.md`](./CONTENT_BUILDING_RULES.md) § "The build log".
  Also create `sg-{slug}/CONTENT-SOURCES.md` at the same moment — the map to
  the Chief of Stuff client folder (README, tracker, content/, brand/, asset
  manifest), Drive media, and Wiki/meeting-transcription pages — spec in
  [`CONTENT_BUILDING_RULES.md`](./CONTENT_BUILDING_RULES.md) § "The
  content-sources pointer".

---

## The Phases (Track A infra pipeline — `scripts/onboard-client/`)

Run per-phase: `npm run phaseN -- {slug}`. State lives in `state/{slug}.json`
(resumable).

- **Phase 1 — WPE site + install** (billable; confirm first). If Chris already
  made the install by hand, `runPhase1` will collision-error — instead **seed
  `state/{slug}.json`** from a read-only WPE API verify (adopt the existing
  install).
- **Phase 1b — Plugins + meta:** ACF Pro + Gravity Forms (from the stash) + Yoast;
  site meta; strip default plugins. Over SSH Gateway.
- **Phase 3 — GitHub repo:** `classiccity/ccc-wp-theme--{slug}` (private).
  **→ Log the site in [`docs/SITES.md`](./SITES.md) here.**
- **Phase 4 — Local pull:** *Chris* clicks "Pull from WP Engine" in Local
  (manual — agent can't click). Creates `~/Local Sites/{folder}`.
- **Phase 5 — git init** at the site root (`main`) + `origin` remote.
- **Phase 6 — whitelist `.gitignore`** + initial commit.
- **Phase 7 — parent theme subtree** (`git subtree add … upstream-parent main --squash`).
- **Phase 7b — mu-plugin** (`ccc-fix-auth-header.php`).
- **Phase 8 — child theme into the repo + commit.** Use the **hand-authored**
  `sg-{slug}` from Track B (NOT the script's texbuilt-clone).
- **→ Git Push key** must be registered on the install's Git Push tab (manual,
  no API) before the first `git push wpe main`. Stop condition — confirm with
  Chris.
- **Phase 9 — deploy:** `git push origin main` (backup) → `git push wpe main`
  (staging deploy + Varnish purge).
- **Phase 10 — activate** `sg-{slug}` over SSH.

---

## Running WP-CLI against a Local site (no global `wp`)

The site must be started in Local. Then:

```bash
PHP="$HOME/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php"
WPCLI="/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar"
SOCK="$HOME/Library/Application Support/Local/run/{run-hash}/mysql/mysqld.sock"  # run-hash = site id in Local's sites.json
PUB="/Users/chris/Local Sites/{folder}/app/public"
"$PHP" -d mysqli.default_socket="$SOCK" -d pdo_mysql.default_socket="$SOCK" "$WPCLI" --path="$PUB" <command>
```
