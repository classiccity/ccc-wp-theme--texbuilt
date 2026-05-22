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
npm start             # full pipeline (only Phase 1 implemented today)
npm run phase1        # just Phase 1 (creates WPE install)
```

## State

Each run writes to `state/{slug}.json` after each phase. Phases are idempotent
— re-running with state already populated logs and skips. Delete the state
file to force a re-run (only do this if you've also cleaned up downstream side
effects, e.g. deleted the WPE install).

## Phases

| # | File | Status | What it does |
|---|---|---|---|
| 1 | `phases/01-wpe-install.ts` | ✅ implemented | Create the WPE install via API; poll until ready; verify temp URL |
| 1b | (TBD) | ⏳ | SSH-based: install ACF Pro / Gravity Forms / Yoast SEO, set site meta + permalinks |
| 3 | (TBD) | ⏳ | `gh repo create` for `ccc-wp-theme--{slug}` |
| 4 | (manual) | ⏳ | Pull WPE install into Local (GUI step) |
| 5–9 | (TBD) | ⏳ | git init at site root, .gitignore, parent subtree, child theme scaffold, WPE Git Push deploy |
| 10 | (TBD) | ⏳ | Activate child theme via SSH + wp-cli |
