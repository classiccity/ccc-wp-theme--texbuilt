# Claude Notes — TexBuilt Content Imports

Quick-start context for picking up TexBuilt content work in a fresh
session. **Read this whole file before doing anything**, then read the
referenced docs as needed.

---

## What this is

- **Client:** TexBuilt — commercial construction company (Texas).
  Subsidiary of EMJ Corp.
- **WordPress install on WP Engine:** `texbuilt1`
- **Live URL:** https://texbuilt1.wpengine.com/
- **Local mirror:** `/Users/chris/Local Sites/TexBuilt/` (Local by
  Flywheel; site root at `app/public/`)
- **Active theme:** `sg-texbuilt` (child) inheriting from
  `classic-city-core` (parent)

---

## First — orient yourself

1. Read **`wp-content/themes/classic-city-core/docs/ARCHITECTURE.md`**
   for the multi-repo + subtree topology.
2. Read **`wp-content/themes/classic-city-core/docs/CLIENT_ONBOARDING.md`**
   especially **Phase 12 — Deploy content programmatically**. That's
   the playbook for everything below.
3. Skim the **Common pitfalls** table in that same doc — at least 8
   WPE-specific gotchas are catalogued there with fixes.

---

## Authentication

### REST API

- **Username:** `chris@classiccity.com` (NOT `chrislafay` — WPE installs
  use email-as-login)
- **Application Password:** ask the user to provide. Past one was
  `QM6pcGi18RH0EUTN0yyWGtaj` (no spaces) — but app passwords can be
  rotated. Verify with:
  ```bash
  curl -u "chris@classiccity.com:APP_PASSWORD" \
    "https://texbuilt1.wpengine.com/wp-json/wp/v2/users/me"
  ```
  Should return id=2, name="Chris LaFay". If not, ask user for fresh
  app password from wp-admin → Users → Profile → Application Passwords.
- Save the auth file once obtained:
  ```bash
  printf 'chris@classiccity.com:APP_PASSWORD' | base64 > /tmp/texbuilt-wpe-auth.b64
  chmod 600 /tmp/texbuilt-wpe-auth.b64
  ```

### SSH Gateway (for wp-cli)

- **Host:** `texbuilt1@texbuilt1.ssh.wpengine.net`
- **WP install path on server:** `/home/wpe-user/sites/texbuilt1/`
- **Auth:** `~/.ssh/id_ed25519` (already registered on the install's
  SSH Gateway tab in WPE User Portal)
- Verify: `ssh texbuilt1@texbuilt1.ssh.wpengine.net "echo ok"`
- If denied: user needs to re-register the key in WPE User Portal →
  install → SSH Gateway tab.

### Git remotes

- **origin:** `git@github.com:classiccity/ccc-wp-theme--texbuilt.git`
  (private, SSH; source of truth for the client repo)
- **wpe:** `git@git.wpengine.com:production/texbuilt1.git`
  (deploys to the WPE production environment)
- **upstream-parent:** `git@github.com:classiccity/ccc-wp-theme.git`
  (the shared parent theme — pull in via `git subtree pull --squash`
  to bring in parent updates)

All three SSH-keyed; no credentials needed beyond `~/.ssh/id_ed25519`.

---

## The hybrid pattern (CRITICAL)

WPE's WAF blocks REST API writes for media + page creation:
- `POST /wp/v2/media` → 403 from nginx
- `POST /wp/v2/pages` → 403 from nginx
- Query strings with `.jpg`/`.png`/`.mp4` → 403 from nginx

So the playbook is:

| Operation | Mechanism |
|---|---|
| Auth check, list users, list themes, GET pages, etc | REST API |
| `POST /wp/v2/settings` (option updates) | REST API — works fine |
| Upload media | wp-cli over SSH Gateway |
| Create/update pages with block content | wp-cli over SSH Gateway |
| Activate themes | wp-cli over SSH Gateway (`wp theme activate`) |
| Permalink changes, plugin install/activate, license keys | wp-cli over SSH Gateway |

### Media upload pattern (used for the homepage deploy)

```bash
# Pipe-over-SSH because WPE SSH Gateway blocks scp's sftp subsystem
cat "$LOCAL_FILE" | \
  ssh texbuilt1@texbuilt1.ssh.wpengine.net \
  "cat > /home/wpe-user/sites/texbuilt1/$(basename $LOCAL_FILE)"

# Import via wp-cli — --porcelain returns just the new attachment ID
NEW_ID=$(ssh texbuilt1@texbuilt1.ssh.wpengine.net \
  "cd /home/wpe-user/sites/texbuilt1 && \
   wp media import $(basename $LOCAL_FILE) --porcelain && \
   rm $(basename $LOCAL_FILE)")
```

### Page creation via wp-cli

```bash
# Write rewritten block content to a file, pipe up, create page
cat content.html | \
  ssh texbuilt1@texbuilt1.ssh.wpengine.net \
  "cat > /home/wpe-user/sites/texbuilt1/content.html"

PAGE_ID=$(ssh texbuilt1@texbuilt1.ssh.wpengine.net \
  "cd /home/wpe-user/sites/texbuilt1 && \
   wp post create content.html \
     --post_type=page --post_title='Page Title' \
     --post_name='page-slug' --post_status=publish \
     --porcelain && rm content.html")
```

---

## Cache purging (CRITICAL)

WPE runs Varnish in front of WordPress. **wp-cli changes do NOT
auto-purge Varnish.** `wp cache flush` only clears WP's PHP object
cache, not Varnish.

After any wp-cli content deploy, force a purge by ONE of:

1. **Trivial git push** to the `wpe` remote — WPE's post-receive hook
   runs Varnish purge automatically. Add a whitespace change anywhere
   in the repo and push.
2. **WPE User Portal** → install → "Purge all caches" button (manual).
3. Cache-busted URL `?_cb=$(date +%s)` to verify origin renders
   correctly while waiting for Varnish TTL (usually minutes) — clean
   URL will catch up on its own.

The TexBuilt repo doesn't yet have a `.cache-bust-timestamp` file
helper — feel free to add one and use it in deploy scripts.

---

## ACF block markup format

ACF blocks store data in Gutenberg block-comment attributes with a
specific shape. Every field appears **twice** in the `data` object:

```html
<!-- wp:classic-city-core/hero-full-image {"name":"classic-city-core/hero-full-image","data":{
  "image": 99,
  "_image": "field_hero_full_image",
  "title_html": "Built Texas Tough.",
  "_title_html": "field_hero_full_title_html",
  "video": "0",
  "_video": "field_hero_full_video"
},"align":"full","mode":"preview"} /-->
```

The `_<fieldname>` entries point to the ACF field key (declared in
each block's `fields.php` in the parent theme). ACF needs both to
save correctly.

For repeaters: `<repeater_name>` is the row count (integer), and each
row's fields live as flat keys named `<repeater_name>_<idx>_<subfield>`.

Field-key registry for every parent-theme block lives in
**`/Users/chris/Chief of Stuff/Brain/Chief of Stuff/Classic City/Clients/TexBuilt/proposals/05-content-import-learnings.md`**
(user's personal notes folder). Read that file's "Field-key registry
(cheatsheet)" section before constructing any ACF block markup. It
covers all 13 blocks in the parent theme.

---

## ID remapping when moving pre-built content

When you have block content with attachment IDs from a different
install (e.g., the style-guide sandbox at `the-style-guide-wp.local`),
you MUST upload the media to TexBuilt first, capture new IDs, then
rewrite the block content's `"image":<N>` literals.

Python pattern:

```python
import re
# Sandbox-to-WPE ID mapping built from upload responses
id_map = {99: 9, 101: 10, 108: 12, ...}

new_content = original_content
# Sort longest-first so substring collisions don't matter
for sandbox_id, wpe_id in sorted(id_map.items(), key=lambda kv: -kv[0]):
    # Negative lookbehind for `":` and lookahead for `,` or `}` —
    # only matches when the ID is a JSON number value, not part of
    # alt text, positions, etc.
    pattern = re.compile(rf'(?<=":){sandbox_id}(?=[,}}])')
    new_content = pattern.sub(str(wpe_id), new_content)
```

Sandbox upload IDs (in `the-style-guide-wp.local`): see
`05-content-import-learnings.md`. Currently uploaded to TexBuilt: see
"Current state on WPE" below.

---

## Current state on WPE (as of 2026-04-24)

### Pages
- Home page (id=21, slug=`home`, set as front page)
  - Renders all ACF block types: hero-full-image, icon-feature-row,
    star-divider, split-50-50 (×3), image-tiles, cta-thin
  - All inline images pull from attachment IDs 8–20 (mapped from
    sandbox IDs 97, 99, 101, 104, 108-116)

### Media library (attachment IDs)
- 8 = `texbuilt-groundbreaking-crew.jpg`
- 9 = `texbuilt-site-frisco-drone-1.jpg`
- 10 = `texbuilt-site-frisco-interior.jpg`
- 11 = `texbuilt-site-plano-drone-2.jpg`
- 12-20 = industry tiles (grocery, medical, office, multifamily,
  entertainment, retail, senior-living, data-centers, government)
- 23 = `texbuilt-logo-white.png` (821×165, set as `site_logo` option +
  `custom_logo` theme mod)

Source files in
`/Users/chris/Local Sites/the-style-guide-wp/app/public/wp-content/uploads/2026/04/`
(prefixed `texbuilt-`).

### Site settings
- `blogname`: TexBuilt
- `blogdescription`: A website for TexBuilt
- `permalink_structure`: `/%postname%/`
- `show_on_front`: page
- `page_on_front`: 21

### Plugins active
- ACF Pro 6.7.0.2 (license set; admin may show "please activate" nag)
- Gravity Forms 2.10.0 (license set; same nag possibility)
- Yoast SEO 27.4
- mu-plugin: `ccc-fix-auth-header.php` (restores HTTP_AUTHORIZATION
  for REST API Basic Auth — required on WPE)

---

## Brand reference

From `theme.json`:

- **CTA:** `#F68B1F` (orange)
- **CTA-alt:** `#D97109`
- **Primary:** `#373D46` (dark slate)
- **Primary-alt:** `#4D5663`
- **Secondary:** `#C2C1B8` (warm grey)
- **Light:** `#FBF7EE` (cream — slightly different from default)
- **Dark:** `#202122`
- **Body bg:** `#FDF9F2`

- **Heading font:** `Barlow Condensed` (Google Fonts)
- **Body font:** `Source Sans 3` (Google Fonts)
- **Accent font:** `Mitha Script` (custom self-hosted at
  `fonts/mitha-script.ttf`) — used via the "Accent Script" block
  style on heading/paragraph/quote. **The CSS class is
  `is-style-accent-font`** (not `is-style-accent-script` — the editor
  label and the internal class name differ; the class is what you
  set in `className` when authoring blocks programmatically). The
  style only swaps the typeface — font size stays at whatever the
  block's typography setting is. Defaults are too small for cursive;
  set an explicit `style.typography.fontSize` per use.
- **Border default-width:** 3px
- **Radius default:** 0 (sharp corners — TexBuilt aesthetic)
- **Shadow style:** flat offset (e.g., `2px 2px 0 0 rgba(0,0,0,0.1)`)
- **FontAwesome icon style:** sharp-light

---

## Working from this site

- Repo root is `/Users/chris/Local Sites/TexBuilt/app/public/`
- The git repo is at the **site root**, not the themes folder
- Whitelist `.gitignore` versions only:
  - `wp-content/themes/classic-city-core/` (parent theme via subtree)
  - `wp-content/themes/sg-texbuilt/` (this child theme)
  - `wp-content/mu-plugins/ccc-fix-auth-header.php`
- Anything else (WP core, plugins, uploads) is untracked

To deploy code changes: edit → `git add` → commit → `git push origin
main && git push wpe main`.

---

## Outstanding manual TODO for the user (before content imports)

- ⚠️ **Pull from WP Engine** in Local once to sync ACF Pro + Gravity
  Forms + Yoast plugin files down. Without this, Local won't have the
  plugins and you'll see ACF blocks fail to render in Local previews
  (though they'll still work on WPE).
- (optional) Clean up leftover `/sg-texbuilt/` and `/classic-city-core/`
  folders at WPE site root from the original wrong-layout push. WPE
  File Manager or SFTP. Harmless to leave.

---

## What NOT to do

- ❌ Don't commit `keys.txt`, app passwords, or any other secret to git
- ❌ Don't try to `POST /wp/v2/media` — WAF will 403 you
- ❌ Don't try to `POST /wp/v2/pages` for new content — same
- ❌ Don't use `scp` or `sftp` against the SSH Gateway — restricted.
  Use pipe-over-ssh: `cat file | ssh "cat > remote"`
- ❌ Don't forget the cache purge after wp-cli content changes
- ❌ Don't edit files inside `wp-content/themes/classic-city-core/` and
  expect them to update the parent repo automatically. Subtree changes
  there commit to the **client repo** by default. To push them
  upstream: `git subtree push --prefix=wp-content/themes/classic-city-core upstream-parent main`

---

## Key reference files

- **Parent theme runbook:** `wp-content/themes/classic-city-core/docs/CLIENT_ONBOARDING.md` (Phase 12 has the content-deploy playbook)
- **Parent theme architecture:** `wp-content/themes/classic-city-core/docs/ARCHITECTURE.md`
- **Content import learnings (personal notes):** `~/Chief of Stuff/Brain/Chief of Stuff/Classic City/Clients/TexBuilt/proposals/05-content-import-learnings.md` (ACF block field-key registry)
- **TexBuilt project notes (contracts, briefs):** `~/Chief of Stuff/Brain/Chief of Stuff/Classic City/Clients/TexBuilt/`
