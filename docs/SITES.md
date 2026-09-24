# Sites Registry

The authoritative list of REAL client sites built on this system. This
file exists so any agent — on any machine, in any session — can confirm
facts about existing sites instead of guessing, and so nobody ever pulls
an old site as a build baseline. (It supersedes the per-machine
`sites-registry` Claude-memory entry; memory is a cache of this file.)

**Append protocol:** add a row the moment a new site's **GitHub repo is
created (Phase 3)** — see [`NEW_SITE_CHECKLIST.md`](./NEW_SITE_CHECKLIST.md).
Local path = `/Users/chris/Local Sites/{folder}` (site root:
`…/app/public`; the desktop machine uses `/Users/christopherlafay/…`).
GitHub = `github.com/classiccity/{repo}` unless noted.

---

## Ground rules

- **Never copy an old client site as a build baseline.** Referencing a
  site to confirm/deny a detail is fine; using one as the starting point
  is not. Build references: **`sg-sherman-phalen`** (light) and
  **`sg-trialport`** (dark) — each in its OWN client repo below.
- **The `sg-*` folders co-located with the parent theme in the
  style-guide sandbox** (`sg-texbuilt`, `sg-lumberock`, `sg-acme`,
  `sg-aeon-laser`, plus the stray `classic-city-core-2`) are **stale
  sandbox test copies, not canonical client code**. Real child themes
  live in each client's own repo.
- **Georgia SEB is heavily customized — never use it as a sample.**
- Per-machine build state (including `phase4.localSiteRoot`) lives in
  `scripts/onboard-client/state/{slug}.json` (gitignored). List that dir
  to find what's been built on the current machine.

## The registry

| Site | Local folder | GitHub repo | WPE install | Created | Theme model | Notes |
|---|---|---|---|---|---|---|
| TexBuilt | `TexBuilt` | `ccc-wp-theme--texbuilt` | `texbuilt1` | 2026-04-24 | legacy light/dark (bridged) | First build; live content uses `has-light`/`has-dark` — needs the style.css bridge before Core pulls (see `THEME_TOKENS.md`) |
| Georgia SEB | `georgia-state-election-board` | `ccc-wp-theme--georgiaseb` | `georgiaseb` | 2026-05-01 | legacy | ⛔ **heavily customized — NEVER a sample** |
| Lumberock | `lumberock` | `ccc-wp-theme--lumberock` | `lumberock` | 2026-05-15 | legacy (near-zero content, no bridge needed) | |
| Publicom | `publicom` | `ccc-wp-theme--publicom` | `publicom` | 2026-06-01 | — | Publicom Inc. |
| WallPro | `wallpro` | `ccc-wp-theme--wallproprod` | `wallproprod` | 2026-06-20 | — | ⚠️ folder `wallpro` but slug/install/repo = `wallproprod` |
| Sherman & Phalen | `sherman-phalen` | `ccc-wp-theme--sherman-phalen` | `sandplaw` | 2026-06-20 | canvas/panel/ink | ✅ **light-theme reference**; install `sandplaw` ≠ slug |
| trialport | `trialport` | `ccc-wp-theme--trialport` | `trialport` | 2026-07-01 | canvas/panel/ink (dark) | ✅ **dark-theme reference**; name always lowercase |
| Tower Leadership | `tower-leadership` | `ccc-wp-theme--tower-leadership` | — (Local-only prospect) | 2026-07-16 | canvas/panel/ink | Prospect demo build (dental consulting); no WPE until they sign |
| TimberBLDR | `timberbldr` | `ccc-wp-theme--timberbldr` | `timberbldrdev` | 2026-08-28 | canvas/panel/ink | Mass-timber builder; homepage mimics bldrcorp.com; Projects + Blog from TexBuilt. **Deployed 2026-09-01 — WPE canonical for content.** ⚠️ install NOT visible to the classiccity WPE API key; Git Push key pending (deploys via SSH-gateway cat-pipe; Varnish purge = portal only) |
| Fortera Insurance | `forterains` | `ccc-wp-theme--forterains` | `forterains` | 2026-09-01 | canvas/panel/ink (dark) | Insurance for construction; **splash-screen-only** build (logo over looping construction b-roll), WallPro Coming Soon pattern. Palette = official brand-guidelines PDF, not the brand-board PNG (they differ — see child `CLAUDE.md`). ⚠️ install NOT visible to the classiccity WPE API key; SSH Gateway works, **Git Push key still not registered** — deploys are SSH-side for now |

## Unconfirmed / partial builds

Installs with state files but no confirmed folder/date — promote to the
table above (Chris confirms dates) before treating as built sites:

- `aspglobal` — ASP Global (state file + sitemap notes exist)
- `bldrcorp` / `bldrcorp1` — BLDR Holdings; the live one-pager runs on
  the **emjcorp** WPE account, install `bldrcorpstg` (staging), slug
  `bldrcorp` — supersedes the classiccity `bldrcorp1` install.
