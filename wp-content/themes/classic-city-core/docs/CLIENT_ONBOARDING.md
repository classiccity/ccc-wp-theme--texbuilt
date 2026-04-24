# Client Onboarding Runbook

Step-by-step for spinning up a new client site. Target state at the end:
- A WP Engine install running the parent theme + a new client-specific
  child theme.
- A GitHub repo holding the client's **site-root** directory (with the
  parent theme merged in as a git subtree and the child theme versioned
  directly).
- A Local site mirroring the WPE install for local dev.

> **Important architectural note:** The client repo root is the
> **WordPress site root** (the folder containing `wp-admin/`, `wp-includes/`,
> `wp-content/`, `wp-config.php`, etc.), NOT the themes folder. This is
> because WP Engine's Git Push always deploys to the site root — there's
> no built-in subpath config. A whitelist `.gitignore` constrains what's
> actually versioned down to just the themes we care about.

---

## Prerequisites (one-time setup per developer machine)

- **macOS** with Homebrew.
- **Local by Flywheel** (desktop app) signed into a WP Engine account so
  "Pull from WP Engine" works.
- **GitHub CLI:**
  ```bash
  brew install gh
  gh auth login   # GitHub.com → HTTPS → web browser
  ```
- **SSH key registered on GitHub** so `git@github.com:...` URLs work:
  ```bash
  ssh-keygen -t ed25519 -C "your-label-here"
  pbcopy < ~/.ssh/id_ed25519.pub
  # GitHub → Settings → SSH keys → paste
  ssh -T git@github.com   # should say "Hi <username>!"
  ```
- **Same SSH key registered on WP Engine** (per-install, see Phase 9).
  WPE uses SSH keys for Git Push, separately keyed from the SSH
  Gateway. **Note:** WPE SSH Gateway access (for `ssh texbuilt1@…`
  shell access) uses a DIFFERENT key registration than Git Push. Adding
  a key to Git Push alone does not grant shell access to the install.

---

## Phase 1 — Create the WP Engine install

_(If you've already done this manually, skip to Phase 3.)_

1. Log into [my.wpengine.com](https://my.wpengine.com).
2. **Add Install** → short lowercase install name like `{slug}1`
   (e.g., `texbuilt1`, `blackwood1`). The `1` suffix leaves room for
   staging/experimental installs later.
3. Pick the environment (Production).
4. Wait for WP Engine to provision (2–5 minutes).
5. The install's temp URL is `https://{install}.wpengine.com`.

**Verification:** you can log into the install's wp-admin.

---

## Phase 1b — Install default plugins + site meta

Every client install gets the same baseline: three default plugins
(ACF Pro, Gravity Forms, Yoast SEO), site name + description set, and
any unused WPE-default plugins removed. All of this runs in a single
SSH session and takes about 30 seconds.

Plugins aren't committed to any git repo:
- **License terms** for ACF Pro and Gravity Forms discourage
  redistribution, and committing them to a client repo + WPE deploy
  technically distributes plugin code.
- **Self-update noise** — plugins update themselves via wp-admin, which
  would create drift between the repo and live install.
- **Update ergonomics** — WPE handles plugin updates cleanly in admin.

### 1b-prereqs

- **Local plugin stash** at `~/Downloads/default-plugins/` (or anywhere
  consistent). Expected contents:
  - `advanced-custom-fields-pro*.zip` (paid plugin, local upload)
  - `gravityforms*.zip` (paid plugin, local upload)
  - `keys.txt` with:
    ```
    ACF: <base64-encoded ACF Pro license key>
    Gravity Forms: <32-char Gravity Forms license key>
    ```
  - Yoast SEO is NOT included here — it's free on WordPress.org and
    `wp plugin install wordpress-seo` fetches it directly.

- **SECURITY — `keys.txt` contains secrets.** Never commit it. Never
  upload it to WPE. Never paste the contents into a PR or chat that
  ends up logged. If this file lives under a git-tracked folder (e.g.,
  if `~/Downloads` ever gets versioned), add it to your global
  `.gitignore` explicitly. Consider `chmod 600 keys.txt` to restrict
  read access to your user.

- **SSH Gateway key registered on the install** — different tab from
  Git Push. Per-install, manual ~30s: WPE User Portal → install →
  SSH Gateway → paste `~/.ssh/id_ed25519.pub`. Verify with
  `ssh {install}@{install}.ssh.wpengine.net "echo ok"`.

### 1b-script

Single bash block. Replace the two `{…}` placeholders before running.

```bash
INSTALL={install}
CLIENT_NAME="{Client Display Name}"
PLUGINS_DIR=~/Downloads/default-plugins

ACF_KEY=$(grep '^ACF:' "$PLUGINS_DIR/keys.txt" | sed 's/^ACF: //')
GF_KEY=$(grep '^Gravity Forms:' "$PLUGINS_DIR/keys.txt" | sed 's/^Gravity Forms: //')

# Upload the paid-plugin zips INTO the WP install directory. wp-cli
# can't reach /home/wpe-user/ paths directly; /home/wpe-user/sites/$INSTALL/
# is where it can see files. Pipe-over-ssh because WPE SSH Gateway
# blocks scp's sftp subsystem.
cat "$PLUGINS_DIR"/advanced-custom-fields-pro*.zip | \
  ssh $INSTALL@$INSTALL.ssh.wpengine.net \
  "cat > /home/wpe-user/sites/$INSTALL/acf-pro.zip"

cat "$PLUGINS_DIR"/gravityforms_*.zip | \
  ssh $INSTALL@$INSTALL.ssh.wpengine.net \
  "cat > /home/wpe-user/sites/$INSTALL/gravityforms.zip"

# Install, activate, license, set meta, clean up defaults, cleanup zips
# — one round-trip.
ssh $INSTALL@$INSTALL.ssh.wpengine.net bash -s <<EOF
cd /home/wpe-user/sites/$INSTALL

# Paid plugins from uploaded zips.
wp plugin install ./acf-pro.zip --activate --force
wp plugin install ./gravityforms.zip --activate --force

# Free plugin from WordPress.org.
wp plugin install wordpress-seo --activate

# License keys. Direct option updates — plugin features work, but the
# plugins may show a "Please activate" nag until someone clicks their
# admin Activate button once (they handshake with their license API
# only when triggered by the admin form).
wp option update acf_pro_license '$ACF_KEY'
wp option update rg_gforms_key '$GF_KEY'

# Site meta.
wp option update blogname '$CLIENT_NAME'
wp option update blogdescription 'A website for $CLIENT_NAME'

# Permalinks: post name only. Fresh WP installs default to "Plain"
# (?p=123) which breaks /wp-json/ REST API routes — requests hit the
# homepage instead of the API handler. Post-name permalinks both look
# nicer AND enable standard REST URLs to work.
wp rewrite structure '/%postname%/'
wp rewrite flush

# Remove WPE default plugins we don't want. Conditional so we don't
# error when they're not there (some WPE install templates don't
# ship them).
if wp plugin is-installed genesis-blocks 2>/dev/null; then
  wp plugin delete genesis-blocks --deactivate
fi
if wp plugin is-installed akismet 2>/dev/null; then
  wp plugin delete akismet --deactivate
fi

# Done — remove the uploaded zips so they don't clutter the install.
rm -f acf-pro.zip gravityforms.zip
EOF
```

### 1b-verification

```bash
ssh $INSTALL@$INSTALL.ssh.wpengine.net bash -s <<EOF
cd /home/wpe-user/sites/$INSTALL
echo "--- active plugins ---"
wp plugin list --status=active --fields=name,version --format=table
echo ""
echo "blogname:        \$(wp option get blogname)"
echo "blogdescription: \$(wp option get blogdescription)"
echo "permalink:       \$(wp option get permalink_structure)"
EOF
```

Expected: three active plugins (ACF Pro, Gravity Forms, Yoast SEO),
site name = `$CLIENT_NAME`, description = "A website for $CLIENT_NAME",
permalink structure = `/%postname%/`.

### 1b-gotchas learned during TexBuilt onboarding

- **Don't use plain `scp`** — WPE's SSH Gateway restricts the SFTP
  subsystem. SCP fails with "Connection closed." Use pipe-over-SSH
  (`cat file | ssh "cat > remote"`) instead.
- **Don't upload to `/home/wpe-user/`** for wp-cli's consumption —
  wp-cli can't resolve `~` across nested shells AND runs with
  restricted path access outside the WP install directory. Upload
  into `/home/wpe-user/sites/{install}/` and reference with a
  relative path from that `cd`.
- **License options aren't full "activated" state.** We set the
  `acf_pro_license` and `rg_gforms_key` options directly, which makes
  the plugins functional. ACF and GF may still show "Please activate"
  in admin because they haven't handshaken with their license APIs.
  Either accept the cosmetic banner, or click Activate once in admin
  per install to dismiss it for good.
- **Don't use `wp eval` with complex PHP inside a bash heredoc** —
  bash `$…` expansion mangles PHP variables. If license activation
  via a plugin's own function is needed, put the PHP in a standalone
  `.php` file and `wp eval-file`.
- **Conditional plugin removal matters.** Genesis Blocks and Akismet
  aren't always installed on fresh WPE installs; the template used
  for the install determines what's pre-loaded. The `is-installed`
  check keeps the script clean when they're not there.

---

## Phase 2 — Parent theme repo (already exists)

The parent theme lives at `classiccity/ccc-wp-theme` on GitHub. Skip
this phase unless rebuilding from scratch.

---

## Phase 3 — Create the client GitHub repo

Naming convention: `ccc-wp-theme--{slug}` (double hyphen before slug so
the parent's name and the client repo sort adjacent alphabetically).

```bash
gh repo create classiccity/ccc-wp-theme--{slug} --private --confirm
```

**Don't** initialize with README / license / .gitignore — they will
conflict with Phase 6.

---

## Phase 4 — Pull the WP Engine install into Local

1. In **Local**, click **Connect to WP Engine** (if not already signed
   in).
2. **Pull from WP Engine** → pick the install created in Phase 1 →
   Local creates a new site (e.g., `{slug}` → `{slug}.local`).
3. Wait for the pull to finish (5–10 min for a fresh install).
4. Start the site in Local, confirm `{slug}.local` loads the default
   WP Engine landing page.

**Verification:** you can browse `http://{slug}.local` and see a
WordPress install.

---

## Phase 5 — Initialize the SITE ROOT as a git repo

**Key point:** the git repo root is the site root — `app/public/` —
**not** the themes folder.

```bash
cd "/Users/chris/Local Sites/{SITE_NAME}/app/public"

# Remove any stale .git if one exists, then init fresh.
rm -rf .git
git init -b main

# Set origin to the repo created in Phase 3. Use SSH so the upstream
# parent fetch (Phase 7) and future deploys don't prompt for credentials.
git remote add origin git@github.com:classiccity/ccc-wp-theme--{slug}.git
```

**Verification:** `git remote -v` shows the client repo URL (SSH form).

---

## Phase 6 — Write the whitelist `.gitignore`

At the site root, create `.gitignore` that ignores everything by default
and un-ignores only the paths we version:

```gitignore
# Ignore everything at every level.
/*

# Un-ignore repo metadata.
!/.gitignore

# Un-ignore the path down to our versioned theme folders.
!/wp-content/
/wp-content/*
!/wp-content/themes/
/wp-content/themes/*
!/wp-content/themes/classic-city-core
!/wp-content/themes/sg-{slug}

# macOS cruft should never be tracked, even inside whitelisted folders.
**/.DS_Store
**/._*
```

After saving it, `git status` from the site root should list only the
.gitignore and the two theme paths as untracked. Everything else (WP
core, wp-config.php, uploads, plugins, mu-plugins, cache) stays invisible
to git.

---

## Phase 7 — Add the parent theme as a subtree

**Not a submodule.** WP Engine's Git Push has a "checking submodules"
step in its deploy pipeline but does NOT actually clone submodule
content into the deploy target, so submoduled parent themes deploy as
empty directories. Subtree merges the parent theme's files directly
into the client repo as real tracked content, which WPE pushes fine.

```bash
cd "/Users/chris/Local Sites/{SITE_NAME}/app/public"

# Add a named remote for the upstream parent so later `subtree pull`
# commands can reference it by name instead of retyping the URL.
git remote add upstream-parent git@github.com:classiccity/ccc-wp-theme.git

# Merge the parent theme into wp-content/themes/classic-city-core as a
# squashed subtree. --squash collapses all upstream history into a
# single "Squashed ... content from commit <sha>" commit, keeping the
# client repo's history clean.
git subtree add --prefix=wp-content/themes/classic-city-core \
  upstream-parent main --squash
```

After this, `wp-content/themes/classic-city-core/` contains real files
(not a submodule pointer). The parent theme is now part of the client
repo's working tree and history.

**Verification:**
```bash
ls wp-content/themes/classic-city-core/style.css   # should exist
git log --oneline -3                               # shows two commits:
                                                   # the merge, and the squash
```

---

## Phase 8 — Scaffold the child theme

Until there's a dedicated child theme template repo, copy from an
existing child theme (e.g., `sg-texbuilt` or `sg-lumberock`) and
substitute the client-specific values.

```bash
# From the style-guide sandbox, copy a close-match template:
cp -R "/Users/chris/Local Sites/the-style-guide-wp/app/public/wp-content/themes/sg-lumberock" \
   "/Users/chris/Local Sites/{SITE_NAME}/app/public/wp-content/themes/sg-{slug}"

# Clean macOS cruft from the copy
find "/Users/chris/Local Sites/{SITE_NAME}/app/public/wp-content/themes/sg-{slug}" -name ".DS_Store" -delete
```

**Then hand-edit these files** for the new client:

- **`style.css`** — update the theme header: `Theme Name`,
  `Description`, `Text Domain`.
- **`theme.json`** — palette colors, typography (font families + Google
  Fonts URL), any custom tokens.
- **`functions.php`** — swap Google Fonts URL if fonts changed; update
  text-domain string.
- **`landing/index.html`** (if present) — update brand copy, contact
  details, image URLs.

Commit the child theme and the gitignore:

```bash
cd "/Users/chris/Local Sites/{SITE_NAME}/app/public"
git add .gitignore wp-content/themes/sg-{slug}
git commit -m "Add sg-{slug} child theme"
```

(The parent-theme subtree was already committed in Phase 7, so we
don't need to re-add it here.)

**Verification:** `git log --oneline` shows three commits: the
subtree-squash, the subtree merge, and the child theme add.

---

## Phase 9 — Wire up WP Engine Git Push deploy

### 9a. Add your SSH key to the WP Engine install's Git Push

1. In the WP Engine User Portal → the install → **Git Push** tab.
2. Paste the public key (`~/.ssh/id_ed25519.pub`) — same one registered
   with GitHub.
3. Fill in developer name + email (email needs to match your WP Engine
   account email on some installs).
4. Save.

**Verification:**
```bash
ssh -T git@git.wpengine.com
# Should say: "hello {install}-{your-dev-name}\n R W  {install}"
```

### 9b. Add WPE as a second git remote + push

```bash
cd "/Users/chris/Local Sites/{SITE_NAME}/app/public"
git remote add wpe git@git.wpengine.com:production/{install}.git
git push -u origin main
git push wpe main
```

First push takes ~1 minute while WPE provisions the remote.

**Verification:**
```bash
/usr/bin/curl -sL -o /dev/null -w "%{http_code}\n" \
  "https://{install}.wpengine.com/wp-content/themes/sg-{slug}/style.css"
# Should return 200
```

If the response is 200, the themes are in the right place and WordPress
will be able to find them.

If the response is 404 but the push reported success, the likely cause
is the repo was scoped to the themes folder instead of the site root —
see the Restructure appendix below.

---

## Phase 10 — Activate the child theme on WP Engine

Log into the install's wp-admin → **Appearance → Themes** → activate the
new child theme (`sg-{slug}`).

**Verification:** the temp URL (`https://{install}.wpengine.com/`)
renders with the child theme's branding (colors, fonts, logo
placeholders).

---

## Phase 11 — Start authoring content

From here on, content work happens in WPE admin on the **staging**
environment. Production stays clean until explicitly promoted via
**Copy Environment** in the WPE User Portal.

Nothing about content / media / menus goes into git — only code.

---

## Ongoing workflow

**Editing code (styles, blocks, templates):**
```bash
cd "/Users/chris/Local Sites/{SITE_NAME}/app/public"
# edit files in wp-content/themes/sg-{slug}/
git add wp-content/themes/sg-{slug}
git commit -m "Describe the change"
git push origin main   # backup to GitHub (source of truth)
git push wpe main      # deploy to WPE
```

Both pushes are required — origin is the source of truth on GitHub;
wpe is the deploy remote.

**Pulling parent theme updates into this client:**
```bash
cd "/Users/chris/Local Sites/{SITE_NAME}/app/public"

# If you haven't already, add the named remote pointing at the parent
# repo — one-time, per client checkout:
# git remote add upstream-parent git@github.com:classiccity/ccc-wp-theme.git

git subtree pull --prefix=wp-content/themes/classic-city-core \
  upstream-parent main --squash

git push origin main
git push wpe main
```

`--squash` is important — without it, you get the full parent-repo
history merged into the client repo on every pull, which makes `git log`
messy fast. With `--squash`, each upstream pull collapses to a single
merge commit regardless of how many upstream commits it brings in.

**Pulling changes authored by another teammate on GitHub:**
```bash
cd "/Users/chris/Local Sites/{SITE_NAME}/app/public"
git pull origin main
```

No submodule dance needed — the parent theme files are part of the
client repo's own tree.

---

## REST API / programmatic access setup

To let a Claude session (or any external script) talk to the WPE install's
REST API with an Application Password, **two things matter on WPE**:

1. **Ship the `ccc-fix-auth-header.php` mu-plugin.** WP Engine's
   nginx→PHP-FPM config doesn't reliably populate `$_SERVER['HTTP_AUTHORIZATION']`
   from the incoming `Authorization:` header. Without the mu-plugin,
   Basic Auth silently fails as `rest_not_logged_in` 401. The mu-plugin
   lives in every client repo at
   `wp-content/mu-plugins/ccc-fix-auth-header.php` and is whitelisted in
   the site-root `.gitignore`. Copy it in during the Phase 7 child-theme
   scaffold (or refactor later to a shared mu-plugin source). See
   [Phase 7 addendum](#phase-7-addendum--mu-plugins) below.

2. **Use the `?rest_route=…` URL form**, not `/wp-json/…`. Fresh WP
   installs default to "Plain" permalinks, and in that mode the
   `/wp-json/` URL rewrite doesn't fire — WP serves the homepage HTML
   instead of routing to the REST API. The query-string form works
   regardless of permalink structure, so lean on it:

   ```bash
   # Works everywhere:
   curl -u "USER:APP_PASSWORD" \
     "https://{install}.wpengine.com/?rest_route=/wp/v2/users/me"

   # Only works after switching to non-plain permalinks:
   curl -u "USER:APP_PASSWORD" \
     "https://{install}.wpengine.com/wp-json/wp/v2/users/me/"
   ```

**Username gotcha:** when you create the Application Password in wp-admin,
the username for Basic Auth is the user's `user_login` field — which on
a fresh WPE install is often the **email address** (WPE provisions admin
users with email-as-login). Always verify with `/wp/v2/users/me` before
baking a username into scripts. Hitting the endpoint with the wrong
username returns a generic `rest_not_logged_in` error that looks like an
auth header problem — easy to chase down the wrong rabbit hole.

---

### Phase 7 addendum — mu-plugins

The child-theme scaffold in Phase 7 isn't the only content we put in the
client repo. The site-root `.gitignore` also whitelists specific files
in `wp-content/mu-plugins/`. Copy these in at the same time:

- `wp-content/mu-plugins/ccc-fix-auth-header.php` — REST API auth header
  restoration. Ship with every client. Source: copy from the TexBuilt
  repo or (preferably, once it exists) a shared `ccc-mu-plugins` repo.

Whitelist addition in `.gitignore`:

```gitignore
!/wp-content/mu-plugins/
/wp-content/mu-plugins/*
!/wp-content/mu-plugins/ccc-fix-auth-header.php
```

---

## Common pitfalls

| Symptom | Cause | Fix |
|---|---|---|
| `Repository not found` on `git subtree add` or `git clone` over HTTPS | Private repo, no cached credentials | Use SSH form (`git@github.com:...`) and verify `ssh -T git@github.com` works |
| `Permission denied (publickey)` on GitHub | No SSH key registered | `ssh-keygen` → add `.pub` to GitHub → verify |
| Push to WPE succeeds but parent theme is missing on the server (child theme can't activate) | Parent theme was added as a submodule, not a subtree. WPE's "checking submodules" step doesn't actually clone submodule content. | Remove the submodule (`git submodule deinit`, `git rm`, delete `.gitmodules`) and re-add via `git subtree add --squash`. See the "Submodule-to-subtree migration" appendix. |
| Parent theme changes don't show up on a client site | Forgot to pull the subtree update | `git subtree pull --prefix=wp-content/themes/classic-city-core upstream-parent main --squash` then push to both remotes |
| Push to WPE succeeds but themes are 404 at `/wp-content/themes/...` | Repo is scoped to the themes folder instead of site root | Restructure: git repo root needs to be `app/public/`, with `wp-content/themes/...` paths inside. See the "Site-root restructure" appendix. |
| Leftover files at site root after a restructured push | WPE Git Push is additive — never deletes files removed between pushes | SFTP in (or WPE File Manager in User Portal) and delete the stale folders manually |
| Pushing to `wpe` remote hangs or denies | SSH key not on Git Push tab for that install | WPE User Portal → Install → Git Push → add `~/.ssh/id_ed25519.pub` |
| `ssh {install}@{install}.ssh.wpengine.net` says publickey denied | SSH Gateway uses a different key registration than Git Push | WPE User Portal → Install → SSH Gateway → add the same key separately |
| Hash URLs like `#contact` won't save in ACF fields | ACF's URL field rejects bare fragments | Already handled by `inc/acf-validations.php` in the parent theme |
| `wp theme activate` via WPE SSH fails | SSH Gateway not set up or WP-CLI not in default PATH | Set up SSH Gateway key first, then SSH and run `wp theme activate` in the install directory |
| REST API returns HTML instead of JSON for `/wp-json/…` | Permalinks are still set to "Plain" (Phase 1b's `wp rewrite structure` didn't run or got reverted) | Either rerun `wp rewrite structure '/%postname%/'` via SSH, flip it in Settings → Permalinks, OR use the `?rest_route=…` URL form which works under any permalink structure. |
| REST API returns `rest_not_logged_in` 401 even with correct credentials | WPE strips `Authorization` header before PHP sees it | Deploy `ccc-fix-auth-header.php` mu-plugin (restores HTTP_AUTHORIZATION + PHP_AUTH_USER/PW from WPE's CGI variables) |
| REST API still 401 after the mu-plugin | Wrong username (using the name vs the email-login) | Hit `/wp/v2/users/me` with both forms — WPE often provisions admins with `user_login = email address` |
| Theme activation not supported via REST API | WP core's `/wp/v2/themes` is read-only | Either activate manually in wp-admin, use `wp theme activate` via SSH Gateway, or ship a bootstrap mu-plugin that auto-activates on first load |

---

## Site-root restructure appendix

If you already pushed a repo scoped to the themes folder (like TexBuilt's
first attempt) and want to move to site-root scope:

```bash
cd "/Users/chris/Local Sites/{SITE_NAME}/app/public"

# Save a backup of the child theme
cp -R wp-content/themes/sg-{slug} /tmp/sg-{slug}-backup-$(date +%s)

# Nuke the old themes-folder repo state
rm -rf wp-content/themes/.git wp-content/themes/.gitmodules wp-content/themes/.gitignore
rm -rf wp-content/themes/classic-city-core

# Init fresh at site root and follow Phases 5–8 of the main runbook.
git init -b main
# ... write the whitelist .gitignore from Phase 6 ...
git remote add upstream-parent git@github.com:classiccity/ccc-wp-theme.git
git subtree add --prefix=wp-content/themes/classic-city-core upstream-parent main --squash
git add .gitignore wp-content/themes/sg-{slug}
git commit -m "Add sg-{slug} child theme"

# Wire remotes + force push (replaces the old GitHub + WPE branches)
git remote add origin git@github.com:classiccity/ccc-wp-theme--{slug}.git
git remote add wpe git@git.wpengine.com:production/{install}.git
git push --force -u origin main
git push --force wpe main
```

Expect ~5 minutes of manual cleanup afterward: the old misplaced files
at the WPE site root aren't auto-deleted by git push, so SFTP in (or
use WPE File Manager) and delete them.

---

## Submodule-to-subtree migration appendix

If you already have a client repo with the parent theme as a submodule
and want to convert to subtree (because WPE Git Push doesn't extract
submodule content — leaves the parent theme empty on the server):

```bash
cd "/Users/chris/Local Sites/{SITE_NAME}/app/public"

# De-register the submodule, remove its files, remove the .gitmodules file.
git submodule deinit -f wp-content/themes/classic-city-core
git rm -f wp-content/themes/classic-city-core
rm -rf .git/modules/wp-content/themes/classic-city-core
rm -f .gitmodules
git add -A .gitmodules
git commit -m "Remove classic-city-core submodule (converting to subtree)"

# Add upstream remote + pull parent in as a squashed subtree.
git remote add upstream-parent git@github.com:classiccity/ccc-wp-theme.git 2>/dev/null || true
git subtree add --prefix=wp-content/themes/classic-city-core \
  upstream-parent main --squash

# Push to both origins. No --force needed — this is a forward migration.
git push origin main
git push wpe main
```

**Verification:** after the WPE deploy completes,
`curl -I https://{install}.wpengine.com/wp-content/themes/classic-city-core/style.css`
returns 200. The WP admin → Appearance → Themes panel lists both the
parent and child themes, and the child theme activates without
reverting.

---

## Automation roadmap

Most of Phases 3–9 are scriptable. A command like:

```
onboard-client --slug=blackwood \
               --name="Blackwood Construction" \
               --wpe-install=blackwood1 \
               --template=construction \
               --brand-color=#B45E10
```

could run the automatable phases with pause points where the user needs
to click something in Local or the WPE portal. Realistic scope:

**Fully automatable:**
- `gh repo create` for the client repo
- `git init` at site root
- Writing the `.gitignore` with substituted slug
- `git subtree add --squash` the parent theme (named remote pre-added)
- Copy child theme template, substitute placeholders (slug, name,
  colors, font stack)
- Commit + push to GitHub
- Add WPE remote (URL from `--wpe-install` arg)
- Push to WPE

**Semi-automatable (WP Engine API):**
- Creating the install itself (plan-tier-gated)
- Activating the child theme via `wp theme activate` over SSH Gateway

**Manual today:**
- Pulling from WPE → Local (Local's CLI is experimental)
- Registering SSH keys on WPE's Git Push and SSH Gateway tabs (UI-only)

Build this after the **second** real client — you'll have seen what
actually varies across clients (and what doesn't need a flag) only
after two examples. A good home for the script is
`scripts/onboard-client.sh` in the parent theme repo.

---

## Change log

- **2026-04-24** — Initial runbook drafted during TexBuilt onboarding.
  Phases 1–10 confirmed working end-to-end. TexBuilt's first WPE push
  used a themes-folder-scoped repo which deploys files to site root —
  restructured mid-onboarding to the site-root layout documented above.
  Added the Restructure appendix capturing that fix path. Documented
  the WPE "Git Push is additive, doesn't delete removed files" gotcha.
  Documented that WPE SSH Gateway requires a separate key registration
  from Git Push.
- **2026-04-24 (pm)** — Added "REST API / programmatic access setup"
  section after discovering three more WPE gotchas while wiring Claude
  automation:
    1. WPE strips `Authorization` header → mu-plugin
       `ccc-fix-auth-header.php` ships with every client repo.
    2. Fresh WP installs default to Plain permalinks, which break
       `/wp-json/` routing — use `?rest_route=…` URL form for
       host-agnostic REST calls.
    3. WPE admin users often have `user_login = email address`, not
       a short name. Verify via `/wp/v2/users/me` before baking into
       automation.
  Also noted that WP core's REST API doesn't support theme activation;
  documented the three fallback paths (manual click / SSH+WP-CLI /
  bootstrap mu-plugin).
- **2026-04-24 (evening)** — Added **Phase 1b — Install default plugins
  + licenses** after TexBuilt needed ACF Pro + Gravity Forms on the
  WPE install. Executed via SSH Gateway + `wp-cli` with four notable
  gotchas documented inline: (1) WPE's SSH Gateway blocks SCP's SFTP
  subsystem, use pipe-over-SSH; (2) wp-cli can't see `/home/wpe-user/`
  paths, upload to `/home/wpe-user/sites/{install}/` instead;
  (3) `wp option update` for license keys is sufficient for plugin
  functionality but doesn't trigger ACF/GF's license-API handshake,
  so their admin screens may still show a nag banner; (4) bash
  heredoc + `wp eval` with complex PHP is fragile — use `wp eval-file`
  with a separate `.php` file when PHP is required. SSH Gateway key
  registration is a separate per-install manual step from Git Push.
- **2026-04-24 (evening, follow-up)** — Expanded Phase 1b to also
  install Yoast SEO (from wp.org, no zip needed), set `blogname` +
  `blogdescription` options (site name = client display name;
  description = "A website for {client name}"), and conditionally
  remove WPE-default plugins Genesis Blocks and Akismet when they
  exist. Added a security callout that `keys.txt` contains secrets
  and must never be committed or uploaded.
- **2026-04-24 (evening, follow-up 2)** — Added permalink structure
  to Phase 1b: `wp rewrite structure '/%postname%/'` runs with the
  other site meta commands. Nice side effect — this also fixes the
  REST API `/wp-json/` routing issue that was documented earlier as
  a Plain-permalinks gotcha. The `?rest_route=` fallback URL is
  still valid and more resilient (works under any permalink
  structure), but the canonical `/wp-json/` URLs now work too.
- **2026-04-24 (late pm)** — **Converted parent theme from submodule to
  subtree** across the entire runbook. WP Engine's Git Push pipeline
  has a "checking submodules" step but does NOT actually clone
  submodule content into the deploy target — result was the parent
  theme directory was empty on WPE and `sg-texbuilt` couldn't activate.
  TexBuilt was migrated mid-session using `git subtree add --squash`;
  Phases 7 + "Ongoing workflow" + pitfalls rewritten; added the
  Submodule-to-subtree migration appendix for any future client repos
  set up the old way; ARCHITECTURE.md "Why submodule" section rewritten
  as "Why subtree" with the WPE limitation explained.
