# onboard-client

Automation for spinning up a new Classic City client site, implementing the
runbook at `../../docs/CLIENT_ONBOARDING.md`.

Built **incrementally** — each phase is its own module under `phases/`. We
implement, run, and verify one phase at a time before moving to the next.

## Setup

```bash
# Once, from this directory:
npm install
```

Credentials live at user scope, NOT in this repo:

```
~/.config/wpe/credentials.env
  WPE_API_USERNAME=<UUID>
  WPE_API_PASSWORD=<32-char token>
```

(`chmod 600` the file, `chmod 700` the `~/.config/wpe/` directory.)

## Run

The current driver is hardcoded in `onboard-client.ts` for the active client.
Edit the `CONFIG` constant, then:

```bash
npm start             # full pipeline (all phases; completed ones skip via state)
npm run phase1        # just Phase 1 (creates WPE install)
# Any single phase directly:
npx tsx phases/03-github-repo.ts <slug>
```

Phases are idempotent (skip when their state key exists), so you can run
individual phases out of band — useful when an install already exists (skip
Phase 1 and hand-author its `phase1` state block) or when a manual gate
(Phase 4 Local pull) sits in the middle.

## State

Each run writes to `state/{slug}.json` after each phase. Phases are idempotent
— re-running with state already populated logs and skips. Delete the state
file to force a re-run (only do this if you've also cleaned up downstream side
effects, e.g. deleted the WPE install).

## Phases

| # | File | Status | What it does |
|---|---|---|---|
| 1 | `phases/01-wpe-install.ts` | ✅ | Create the WPE install via API; poll until ready; verify temp URL. **If the install already exists, this throws on the name collision — skip it and hand-author the `phase1` state block from the existing install's API data.** |
| 1b | `phases/1b-plugins-meta.ts` | ✅ | SSH: install + license ACF Pro / Gravity Forms / Yoast, set blogname + post-name permalinks, drop default plugins. Needs the plugin stash in `~/Downloads/default-plugins/` (zips + `keys.txt`) and the SSH Gateway key registered. |
| 3 | `phases/03-github-repo.ts` | ✅ | `gh repo create` for `ccc-wp-theme--{slug}` (private) |
| 4 | `phases/04-local-pull.ts` | ✅ (verifies a **manual** step) | You Pull the install into Local (GUI); this verifies the site root exists. **Hard gate — phases 5–9 operate on the pulled root.** |
| 5–6 | `05-git-init.ts`, `06-gitignore.ts` | ✅ | git init at site root, set `origin`, whitelist `.gitignore` + initial commit |
| 7, 7b | `07-parent-subtree.ts`, `07b-mu-plugins.ts` | ✅ | Add the parent theme as a subtree from `upstream-parent`; copy mu-plugins |
| 8 | `phases/08-child-theme.ts` | ✅ | Scaffold `sg-{slug}` by copying the template child (`sg-texbuilt`) + substituting name/slug/install strings. **Then rebrand: overwrite `theme.json` (palette/fonts/gradients), swap the Google-Fonts URL in `functions.php`, replace `style.css`/`parts/`, strip template-client artifacts.** |
| 9 | `phases/09-wpe-deploy.ts` | ✅ | Add the `wpe` remote, push `origin` + `wpe`. Needs the **per-install Git Push key** (WPE portal). The HTTP verify may 401 on new installs (WPE password protection) — that's not a deploy failure. |
| 10 | (manual / wp-cli) | ⏳ | Activate the child theme: `wp theme activate sg-{slug}` over SSH. (Not yet a phase script.) |

After Phase 9, build the homepage and pages per `docs/CLIENT_HOMEPAGE.md`.
