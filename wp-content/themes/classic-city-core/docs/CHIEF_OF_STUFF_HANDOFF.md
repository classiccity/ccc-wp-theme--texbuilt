# Chief of Stuff → Classic City Core Handoff

**Schema version:** 1.0
**Doc last updated:** 2026-05-18
**Audience:** Claude agent in a session opened (by Chris or by the Chief of
Stuff system) the moment a client moves to *closed-won* and a build needs to
begin.

> This document is the **agent contract** between Chris LaFay's Chief of
> Stuff system (the source of truth for clients, transcripts, proposals,
> preferences) and the Classic City Core parent theme repo (the build
> system). It tells an agent what input to expect, what commands to run,
> when to pause for human confirmation, and how to recover from failure.
>
> The human-readable runbook for the same flow is
> [`CLIENT_ONBOARDING.md`](./CLIENT_ONBOARDING.md). This doc wraps it —
> read this for the agent contract, jump into `CLIENT_ONBOARDING.md` for
> the prose explanation of any phase you're about to execute.

---

## How to use this document

When a session starts on a closed-won client, in order:

1. **Read this file fully.** Don't skim the stop conditions.
2. **Read the client-config YAML** Chris (or CoS) handed you. If no YAML
   was provided, STOP and ask Chris for one — never improvise the inputs.
   The schema lives in the next section and a worked example is at
   [`client-config.example.yaml`](./client-config.example.yaml).
3. **Verify the schema version** in the YAML matches this doc's schema
   version (top of file). If they differ, ask Chris before proceeding.
4. **Run preflight checks** (below) before touching any infrastructure.
5. **Execute the two tracks** described in the Execution Plan section.
6. **Pause at every stop condition** without exception.

---

## The handoff trigger

When a client moves to closed-won inside Chief of Stuff, CoS is expected to:

1. Produce a `client-config.yaml` for the build, populated from the
   client's transcripts, proposal, and brand inputs.
2. Drop that YAML somewhere the agent can read (typical paths: the
   client's CoS folder, or piped directly into the kickoff prompt).
3. Open a Claude Code session with the working directory set to either:
   - **this parent-theme repo** (preferred — has the onboarding scripts
     and the canonical `wp style-guide new-client` CLI), or
   - the **style-guide sandbox** (`~/Local Sites/the-style-guide-wp/app/public/wp-content/themes/classic-city-core/`)
     where this parent theme is co-located with every existing child theme.

The agent (you) takes it from there.

---

## The client-config schema

The YAML is the **single source of truth** for the build. Every command in
the Execution Plan reads from it. No improvising.

```yaml
schema_version: 1.0                    # must match this doc's schema version
generated_at: 2026-05-18T14:30:00Z     # ISO-8601 timestamp from CoS
generated_by: chief-of-stuff           # free-text source label

client:
  display_name: string                 # "Acme Roofing" — human-readable
  slug: string                         # "acmeroofing" — lowercase, ≤14 chars, no hyphens
                                       # used as WPE install name AND sg-{slug} suffix
  industry: string                     # "construction" — drives demo seeds
  primary_contact:                     # optional but recommended
    name: string
    email: string
    phone: string
  notes_doc: string                    # absolute path to the CoS client folder/notes

infrastructure:
  wpe:
    account_name: string               # "classiccity" — WPE account/billing scope
    site_name: string                  # "Acme Roofing" — WPE Site (groups installs)
    install_name: string               # MUST equal client.slug
    environment: enum                  # production | staging | development
  github:
    org: string                        # "classiccity"
    repo_name: string                  # "ccc-wp-theme--{slug}" — double-hyphen convention
    visibility: enum                   # private | public
  local:
    expected_folder: string            # kebab-case of wpe.site_name — what Local will create

brand:
  palette:                             # 12 colors, every "*-alt" can be "auto"
    cta: hex                           # primary action color
    cta-alt: hex | "auto"              # paired text color; "auto" lets the helper compute it
    primary: hex
    primary-alt: hex | "auto"
    secondary: hex
    secondary-alt: hex | "auto"
    tertiary: hex
    tertiary-alt: hex | "auto"
    light: hex
    light-alt: hex | "auto"
    dark: hex
    dark-alt: hex | "auto"
  typography:
    heading:
      family: string                   # "Inter", "Roboto", etc.
      type: enum                       # google | local | system
      weights: [number]                # [600, 700, 800]
    body:
      family: string
      type: enum
      weights: [number]
  custom_tokens:                       # all optional — parent defaults apply if omitted
    radius: string                     # "6px"
    border_default_width: string       # "1px"
    icon_style: enum                   # solid | regular | light | sharp-light
    button_padding:
      x: string                        # "1.5em"
      y: string                        # "0.75em"

source_material:                       # CoS pointers — read these if any brand field is ambiguous
  transcripts:                         # don't guess colors; check the source
    - id: string                       # e.g., "fireflies://meeting/abc123"
      label: string
  brand_docs:
    - path: string                     # absolute path to a PDF/MD brand guide in CoS
  figma:
    - url: string
      note: string

overrides_json_path: string | null     # optional: path to a JSON file with extra
                                       # theme.json overrides (typeScale, spacing,
                                       # shadows, layout.contentSize, etc.) —
                                       # passed straight to `wp style-guide new-client --overrides`

deployment:
  initial_deploy_target: enum          # MUST be "staging" unless Chris has explicitly
                                       # authorized production-first. STOP if "production".
  auto_activate_child_theme: bool      # true → run `wp theme activate sg-{slug}` after deploy
  seed_demo_content: bool              # true → pass through to `wp style-guide new-client`
                                       # (seeds the /style-guide demo page in the child theme)
```

A complete worked example lives at
[`client-config.example.yaml`](./client-config.example.yaml).

---

## Preflight checks (run before Phase 1)

Refuse to proceed if any of these fail. Report what's missing to Chris.

1. **YAML parses** and `schema_version` matches the top of this file.
2. **Required fields** are populated. Missing
   `client.slug`, `client.display_name`, `infrastructure.wpe.*`,
   `infrastructure.github.*`, or `brand.palette` is a hard stop.
3. **Slug validity:** lowercase ASCII, no hyphens, ≤14 chars (WPE install
   name limit). Reject otherwise.
4. **`client.slug` == `infrastructure.wpe.install_name`** — they must match.
5. **WPE credentials present** at `~/.config/wpe/credentials.env`
   (`chmod 600`). The TypeScript script reads from there.
6. **GitHub CLI authenticated:** `gh auth status` exits 0.
7. **SSH to GitHub works:** `ssh -T git@github.com` says `Hi <username>!`.
8. **No existing WPE install with the same `install_name`** under that
   account — query the WPE API; abort if there's a collision.
9. **No existing GitHub repo** at `{org}/{repo_name}` — `gh repo view` should
   404. If it exists, ask Chris whether to reuse or pick a new name.
10. **Local plugin stash** at `~/Downloads/default-plugins/` contains the
    expected ACF Pro and Gravity Forms zips plus `keys.txt`. (Required for
    Phase 1b. **Never read or echo `keys.txt`'s contents** — the bash
    script extracts them internally.)

---

## Execution plan — two tracks

The build has two logical tracks. They share the YAML but live in
different systems.

### Track A — Infrastructure pipeline

The TypeScript automation at [`scripts/onboard-client/`](../scripts/onboard-client/)
handles every infrastructure phase. It already exists and is hardened
against the WPE quirks listed in `CLIENT_ONBOARDING.md`.

Mapping from the YAML to the script's per-phase inputs is straightforward;
the script's `Phase1Input` interface already mirrors the YAML's
`client.*` + `infrastructure.wpe.*` shape.

**To run the full Track A:**

```bash
cd "/Users/chris/Local Sites/the-style-guide-wp/app/public/wp-content/themes/classic-city-core/scripts/onboard-client"
npm install              # first time only
# Today: script edits its target client inline at the top of each phase file.
# Future: invoke with `--config /path/to/client-config.yaml`. Until then, the
# agent's job is to either edit the inline config to match the YAML, or hand
# back to Chris if the script doesn't yet accept the YAML directly.
npm run phase1           # WPE site + install (billable — see stop conditions)
npm run phase1b          # plugins + site meta
npm run phase3           # gh repo create
npm run phase4           # Local pull verification (Chris must click "Pull from WPE" in Local — manual)
npm run phase5           # git init at site root
npm run phase6           # whitelist .gitignore + initial commit
npm run phase7           # parent-theme subtree add
npm run phase7b          # mu-plugins (ccc-fix-auth-header.php)
npm run phase8           # child-theme scaffold (see Track B note)
npm run phase9           # WPE Git Push deploy
```

**Phase-by-phase detail:** [`CLIENT_ONBOARDING.md`](./CLIENT_ONBOARDING.md)
phases 1 through 12.

**Status:** as of 2026-05-18, phases 1, 1b, 3, 4, 5, 6, 7, 7b, 8, 9 are
implemented end-to-end. Phase 10 (auto-activate child theme over
SSH+wp-cli) is the next gap. Phase 12 (programmatic content deploy) is
manual today.

### Track B — Brand & theme scaffold

Track B produces the `sg-{slug}/` child theme directory that Track A's
Phase 8 expects.

There are two acceptable paths:

#### B-1 (preferred) — `wp style-guide new-client`

The parent theme ships a WP-CLI command that builds a complete
`sg-{slug}/` theme from CLI flags + an optional overrides JSON. Defined
in [`inc/class-ccc-style-guide-cli.php`](../inc/class-ccc-style-guide-cli.php).

Run it against the **style-guide sandbox install** (not a client install):

```bash
cd "/Users/chris/Local Sites/the-style-guide-wp/app/public"
wp style-guide new-client {slug} \
  --name="{client.display_name}" \
  --industry={client.industry} \
  --colors=cta:{hex},cta-alt:auto,primary:{hex},primary-alt:auto,secondary:{hex},secondary-alt:auto,tertiary:{hex},tertiary-alt:auto,light:{hex},light-alt:auto,dark:{hex},dark-alt:auto \
  --heading-font="{brand.typography.heading.family}" \
  --body-font="{brand.typography.body.family}" \
  --icon-style={brand.custom_tokens.icon_style} \
  [--overrides=/tmp/{slug}-overrides.json] \
  [--no-seed-demo]
```

This writes `wp-content/themes/sg-{slug}/` into the sandbox. The child
theme is now buildable and visible at `http://the-style-guide-wp.local/style-guide`
for visual verification before it goes anywhere near a client install.

Then, when Track A's Phase 8 needs the child theme, copy it into the
client repo:

```bash
cp -R "/Users/chris/Local Sites/the-style-guide-wp/app/public/wp-content/themes/sg-{slug}" \
      "/Users/chris/Local Sites/{infrastructure.local.expected_folder}/app/public/wp-content/themes/sg-{slug}"
find "/Users/chris/Local Sites/{infrastructure.local.expected_folder}/app/public/wp-content/themes/sg-{slug}" -name ".DS_Store" -delete
```

#### B-2 (fallback) — copy + hand-edit an existing child theme

Used when Track B-1 can't reach the desired output (rare). Documented
under Phase 8 of `CLIENT_ONBOARDING.md`. The agent must STOP and ask
Chris before falling back, because B-2 is error-prone (manual string
substitution across many files).

#### Always-required final step in Track B: write the child-theme `CLAUDE.md`

Every `sg-{slug}/` must include a `CLAUDE.md` so any future Claude
session opening the client repo has immediate orientation. Copy the
structure from `sg-texbuilt/CLAUDE.md` (the model) and substitute:

- install slug, WPE temp URL, GitHub repo URL
- brand reference (palette + fonts pulled from the YAML)
- current-state section (start empty — fills in during content build)
- local mirror path

---

## Stop conditions — pause and hand back to Chris

**The agent runs autonomously up to deploy.** It MUST stop and explicitly
wait for human confirmation at the following moments:

1. **`deployment.initial_deploy_target` is `production`.** Default is
   staging. If the YAML asks for production-first, stop and confirm with
   Chris before any `git push wpe main` command runs.
2. **First `git push wpe main` to any install** — even staging — until
   you've confirmed Chris registered the SSH key on that install's Git
   Push tab in the WPE User Portal. The WPE API has no endpoint for
   this; it's a manual portal step. Symptom of skipping:
   `FATAL: W any production/{install} {other-install}-{user} DENIED by fallthru`.
3. **DNS cutover** — never edit DNS automatically. Always hand back to
   Chris with the WPE temp URL working and the steps to point the
   client domain.
4. **Renaming or removing any ACF field key** on a child theme whose
   parent install has live content. Existing block content references
   field keys directly; renames silently break already-saved blocks.
5. **Anything that would overwrite uncommitted work** in a client repo.
   If `git status` shows modifications you didn't author, stop.
6. **Editing or reading another client's `sg-*/CLAUDE.md`** for context.
   Each client is sandboxed. If you need a pattern, copy from
   `sg-texbuilt` or `sg-lumberock` (the documented model templates) and
   nothing else.
7. **`git push --force`** anywhere except inside the documented
   "Site-root restructure" or "Submodule-to-subtree migration"
   appendices of `CLIENT_ONBOARDING.md`, and only after Chris confirms.
8. **Reading, copying, or echoing `keys.txt` contents** (Phase 1b
   secrets). The Phase 1b bash block extracts these internally; never
   put them into chat output, scratch files, or commit messages.
9. **Any failure whose symptom isn't in the
   `CLIENT_ONBOARDING.md` "Common pitfalls" table.** Report what you
   observed and stop — don't try fixes that aren't documented.
10. **A WPE install creation (Phase 1) that would be the second
    attempted with the same slug.** A first attempt that silently
    succeeded but you can't see (provisioning lag, API 504) is more
    likely than a real collision. Re-query, wait, ask Chris.

---

## Command surface

Canonical commands the agent is permitted to run. Anything not listed —
ask first.

| Command | Track | Safety | Notes |
|---|---|---|---|
| `npm run phase1` in `scripts/onboard-client/` | A | **confirm-first** | Creates a billable WPE install. Confirm before first run per client. |
| `npm run phase1b` | A | safe | Idempotent-ish; re-runs deactivate-then-delete safely. |
| `gh repo create {org}/{repo_name} --private` | A | safe | Refuses if repo exists. |
| `git init -b main` in site root | A | safe | Phase 5. |
| Write whitelist `.gitignore` + commit | A | safe | Phase 6. Initial commit required before Phase 7. |
| `git subtree add --prefix=wp-content/themes/classic-city-core upstream-parent main --squash` | A | safe | One-time per client repo. |
| `git subtree pull --prefix=... upstream-parent main --squash` | Maintenance | safe | Pull parent updates into a client repo. Always `--squash`. |
| `wp style-guide new-client {slug} --name=... --industry=... --colors=... --heading-font=... --body-font=... --icon-style=... [--overrides=...] [--no-seed-demo]` | B | safe | Run in sandbox install only. |
| `cp -R` child theme from sandbox → client repo | B | safe | Strip `.DS_Store` after. |
| `git add wp-content/themes/sg-{slug} wp-content/mu-plugins/ccc-fix-auth-header.php && git commit` | A | safe | Phase 8 commit. |
| `git push -u origin main` | A | safe | GitHub backup; source of truth. |
| `git push wpe main` to **staging** install | A | safe | Triggers WPE deploy + Varnish purge. |
| `git push wpe main` to **production** install | A | **confirm-first** | Always re-confirm Chris's go. |
| `wp theme activate sg-{slug}` over SSH Gateway | A | safe | Phase 10 (currently manual but scriptable). |
| `ssh {install}@{install}.ssh.wpengine.net "<wp-cli command>"` | A/12 | safe for reads, **confirm-first** for writes on production | Use for media imports + page creation (WAF blocks REST writes). |
| `git push --force` | — | **destructive** | Only inside documented restructure appendix. Confirm first. |
| `wp option update`, `wp post create`, `wp media import` | 12 | safe on staging, **confirm-first** on production | Programmatic content deploy. |
| Touch `.cache-bust-timestamp` + push | 12 | safe | Documented Varnish-purge lever. |

---

## Failure protocol

When something breaks:

1. **Don't retry destructively.** Re-running a Phase 7 `git subtree add`
   without resetting state can fail in confusing ways. Read the phase's
   section in `CLIENT_ONBOARDING.md` first.
2. **Check the "Common pitfalls" table** at the bottom of
   `CLIENT_ONBOARDING.md`. ~20 documented symptoms with their fixes —
   most failures match a row exactly.
3. **If the symptom isn't in the table:** stop. Write up exactly what
   you observed (command, full error, what state the repo/install is
   in) and hand back to Chris. Adding a new pitfalls row is Chris's
   call — the doc evolves through real incidents.
4. **Never `--force` your way past a check** (no `--no-verify`, no
   `--force` push, no `git reset --hard` to "start over"). Each of
   these has lost work in the past — the documented restructure path
   exists precisely so we don't take ad-hoc shortcuts.
5. **State persists.** The TS automation writes per-client state to
   `scripts/onboard-client/state/{slug}.json`. Phases are resumable
   from the last successful step — re-running a phase will skip what
   it already did and pick up from where it failed.

---

## Update protocol — keeping this doc in sync

This doc is the agent contract. It only earns trust if it's accurate.

**Bump `schema_version` and add a changelog entry** when any of these
change:

- The client-config YAML schema (new required field, renamed field,
  removed field, type change).
- The two-track execution model (a new track, a track removal, a
  reorganization).
- A stop condition is added or removed.
- The command surface table changes meaningfully (new canonical
  command, deprecated command, safety classification change).
- A failure-recovery path changes in a way that affects autonomy.

**Don't bump** for typo fixes, prose clarifications, or doc-internal
edits that don't change behavior.

**Chief of Stuff's side of the contract:** when CoS generates a
`client-config.yaml`, it should record the schema version it generated
against. The agent compares that to the version in this doc's header
at preflight. Mismatch → stop and ask Chris.

**The CoS pointer.** Chief of Stuff should have a single line somewhere
(e.g., in its client-pipeline docs, or in a "closed-won checklist")
that reads roughly:

> When a client moves to closed-won, generate a `client-config.yaml`
> for the build (schema in `classic-city-core/docs/CHIEF_OF_STUFF_HANDOFF.md`)
> and open a Claude Code session in `classic-city-core/` with that YAML
> as the kickoff input.

The pointer lives in CoS, not here — this doc just describes what
should be on the other end.

---

## Changelog

- **2026-05-18 — v1.0** — Initial agent-handoff contract. Establishes
  the client-config YAML schema, the two-track execution model
  (Infrastructure via `scripts/onboard-client/` + Brand via
  `wp style-guide new-client`), the 10 stop conditions, the command
  surface table with safety classifications, and the failure protocol.
  Wraps [`CLIENT_ONBOARDING.md`](./CLIENT_ONBOARDING.md) — the runbook
  is still the source of truth for phase-by-phase detail. Worked
  example handoff at [`client-config.example.yaml`](./client-config.example.yaml).
