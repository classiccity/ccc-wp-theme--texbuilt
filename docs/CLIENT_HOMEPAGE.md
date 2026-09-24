# Building a Client Homepage (and other pages)

How to author a real client page from the block library and get it live on
the client's WP Engine install. This is the **production** path — distinct
from `PROSPECT_DEMO.md` (static HTML sales collateral) and `BULK_IMPORT.md`
(bulk CPT/term import). Read `THEME_TOKENS.md` first for the color system.

---

## Command surface (agent quick-reference)

| Command | Safety | Notes |
|---|---|---|
| SSH reads: `wp post get/list`, `wp eval-file` render checks | safe | |
| `wp media import` (sideload imagery) | safe on staging; **confirm-first** on production | |
| `wp post create` (new page) | safe on staging; **confirm-first** on production | |
| `wp post update` (existing page) | **read-modify-write ONLY** | The WPE DB is canonical and Chris edits pages by hand: fetch current `post_content` in the same session, modify it, write it back. **Never** publish a locally-authored file over a page you haven't just read. |
| `wp option update show_on_front` / `page_on_front` | safe on staging; **confirm-first** on production | |
| Snapshot to `sg-{slug}/page-snapshots/` + commit | safe | Do this after every content session. |
| Deleting pages/media, DNS, `--force` anything | **stop — ask Chris** | |

## Where page content actually lives

Client pages are **WordPress Pages in the WPE database** (the live DB is
canonical). They are **not** theme files. The repo holds:
- the **theme** (`theme.json`, `parts/`, `functions.php`, child blocks/CSS), and
- **`page-snapshots/page-<id>-<slug>.html`** — plain-HTML backups of each
  page's `post_content`, captured via SSH. See that folder's `README.md` for
  the refresh/restore loop.

You author block markup, push it into the DB via SSH + wp-cli, set the front
page, then snapshot it back into the repo.

Header/footer are **FSE template parts** (`parts/header.html`,
`parts/footer.html`) — NOT blocks. The scaffold copies the template client's
parts; replace their nav/branding.

---

## Starting from a sitemap-studio export

New clients arrive with a **sitemap-studio export** at
`~/Local Sites/sitemap-studio/exports/{slug}-{timestamp}.json`. It has two
parts:

- **`brand`** → drives the child `theme.json` (palette, gray scale, opposites,
  gradients, fonts, radius/border/icons/shadows). See the "from a sitemap-studio
  export" notes in `THEME_TOKENS.md`.
- **`homepage`** → `sectionOrder` + `sections[]`, each with a `blockType`, a
  `container`, and `content`.

`blockType` is an **abstraction**, not a real block name. Map each to a CCC
block (confirm against `render.php`; selection depends on whether the section
has an image / paints its own bg):

| export `blockType` | CCC block | notes |
|---|---|---|
| `hero` (text on color) | `framed-callout` | text hero on a color panel; add `className:"sg-hero"`. (`hero`/`hero-full-image` *require* an image — use those only when the section has one.) |
| `success-pods` | `icon-feature-row` | icon + label chips; `body` left empty |
| `two-column` | `split-50-50` | `image_side` = `left`/`right` |
| `thin-cta` | `cta-thin` | field-driven slim bar |
| `image-cards` | `image-card-grid` | eyebrow/heading go in core blocks above it |
| `content-cta` | `cta-large` | centered InnerBlocks on a color |
| `process` | `process-steps` | eyebrow/heading in core blocks above |
| `cta-close` | `cta-large` | final CTA; carry the hero's anchor target (e.g. `anchor:"close-cta"`) |

The `container` object (`width`, `paddingY`, `bg`, `inset`, `rounded`,
`shadow`) maps via the "section background / container conventions" below —
either onto the block (if it paints its own bg) or onto a wrapping `wp:group`.

## ACF block markup format

Custom blocks are ACF blocks. Each is a Gutenberg block comment whose `data`
object carries **every field twice** — once as `name: value`, once as
`_name: "field_<key>"` (the ACF field key, used to map the value to the field):

```html
<!-- wp:classic-city-core/split-50-50 {"name":"classic-city-core/split-50-50","data":{
  "image":4,"_image":"field_split_image",
  "image_side":"left","_image_side":"field_split_side",
  "has_texture":0,"_has_texture":"field_split_has_texture"
},"align":"full","mode":"preview"} -->
  ... InnerBlocks (for hybrid blocks) ...
<!-- /wp:classic-city-core/split-50-50 -->
```

- **Field-driven blocks** (no editor content) are self-closing: `… /-->`.
- **Hybrid blocks** wrap `<InnerBlocks />` — put the authored content
  (eyebrow + heading + body + buttons) between the open/close comments.
- **Repeaters:** the parent key is the row count, plus flat per-row keys:
  ```
  "cards":3,"_cards":"field_image_card_grid_cards",
  "cards_0_title":"…","_cards_0_title":"field_image_card_grid_card_title",
  "cards_1_title":"…","_cards_1_title":"field_image_card_grid_card_title", …
  ```

### Get field keys from the source — never guess

Wrong `_field` keys render the block **blank**. The authoritative key for every
field is in the block's own `fields.php`:

```bash
grep -oE "'key'[^,]*|'name'[^,]*" blocks/<slug>/fields.php
```

Also useful: the parent's `patterns/style-guide.php` (its `$keys` arrays are a
per-block registry) and any existing `page-snapshots/*.html` as worked examples.

### Hybrid vs field-driven blocks

- **Hybrid (InnerBlocks):** `hero`, `hero-full-image`, `hero-gradient`,
  `image-hero-50-50`, `split-50-50`, `cta-large`, `framed-callout`,
  `center-content`. The typical inner stack is:
  ```html
  <!-- wp:paragraph {"className":"is-style-eyebrow"} --><p class="is-style-eyebrow">Eyebrow</p><!-- /wp:paragraph -->
  <!-- wp:heading --><h2 class="wp-block-heading">Heading</h2><!-- /wp:heading -->
  <!-- wp:paragraph --><p>Body…</p><!-- /wp:paragraph -->
  <!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"cta"} --><div class="wp-block-button"><a class="wp-block-button__link has-cta-background-color has-background wp-element-button" href="#">Label</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
  ```
- **Field-driven:** `cta-thin`, `icon-feature-row`, `image-card-grid`,
  `process-steps`, `stats`, `feature-grid`, `testimonial-cards`,
  `detail-cards`. Everything is a field or repeater row.

---

## Section background / container conventions

**Do NOT hand-code `border-radius`, `box-shadow`, or margins inline in page
markup.** They aren't repeatable and they vanish the moment an editor touches
the block. Padding inline is fine. Sections come in two shapes:

1. **Blocks that paint their own section panel** (e.g. `framed-callout`,
   `cta-large`): just set the bg with the native `"backgroundColor":"…"` +
   `"align":"full"`. **Radius and shadow are already baked into the block CSS**
   (`.sg-block-cta`, `framed-callout`'s inner panel) — don't add a `style`
   block for them. `framed-callout` is the text-hero-on-color (add
   `"className":"sg-hero"` for the page-hero marker).
2. **Blocks that only style their own inner cards** (`icon-feature-row`,
   `image-card-grid`, `process-steps`, `cta-thin`): to put them on a colored
   rounded panel, wrap in a full-width `wp:group` with the **Section Panel
   block style** — `"className":"is-style-section"`. That class supplies the
   radius + shadow (defined once in `blocks.css`); set the panel color with the
   group's `backgroundColor` (which also pairs the text), and padding with the
   group's Dimensions (`style.spacing.padding`) — no inline radius/shadow/margin:
   ```html
   <!-- wp:group {"align":"full","backgroundColor":"panel","className":"is-style-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
   <div class="wp-block-group alignfull has-panel-background-color has-background is-style-section" style="padding:…">… block …</div>
   <!-- /wp:group -->
   ```

**Section spacing is automatic** — `--ccc-section-gap` puts vertical rhythm
between top-level page sections (`blocks.css`), so never add `margin-top`/
`margin-bottom` to space sections. Tune the gap per child theme by overriding
`--ccc-section-gap`.

**Card-esque blocks default to `panel`** (paired text included): `split-50-50`,
`image-card-grid`, `process-steps`. You don't need to set their bg unless you
want a different color. Any block/CSS that paints a palette background MUST also
set the paired `-opposite` text color, or text goes invisible inside dark
sections (this was the process-steps bug).

Use the **neutral tokens** (`panel`, `canvas`, `ink`) and brand slugs for bg —
the legacy `light`/`dark` slugs are gone (see `THEME_TOKENS.md`).

---

## Images = attachment IDs (sideload first)

ACF image fields store **integer attachment IDs**, not URLs. Sample/lifestyle
imagery must be imported into the WPE media library first.

`wp media import` of a bare Unsplash URL fails two ways: the URL has **no file
extension** (WP rejects the type) and the `&` in the query string breaks the
shell. Fix: **curl to a `.jpg` on the server, then import that file.** Pipe the
script via stdin to avoid quoting hell:

```bash
cat > /tmp/sideload.sh <<'EOF'
cd /home/wpe-user/sites/<install>
echo "key|https://images.unsplash.com/photo-…?auto=format&fit=crop&w=1400&q=75" |
while IFS='|' read -r key url; do
  curl -sL "$url" -o "/tmp/$key.jpg"
  id=$(wp media import "/tmp/$key.jpg" --title="Sample — $key" --porcelain)
  echo "$key=$id"; rm -f "/tmp/$key.jpg"
done
EOF
ssh -o BatchMode=yes <install>@<install>.ssh.wpengine.net 'bash -s' < /tmp/sideload.sh
```

Then substitute the returned IDs into the markup (`"image":<ID>`, no quotes).

---

## Push the page live + set the front page

```bash
# Pipe the markup file up (pipe-over-ssh; WPE blocks scp/sftp)
cat home.html | ssh <install>@<install>.ssh.wpengine.net \
  "cat > /home/wpe-user/sites/<install>/_home.html"

# Create the page and set it as the static front page — ONE session
ssh -n <install>@<install>.ssh.wpengine.net "cd /home/wpe-user/sites/<install> && \
  ID=\$(wp post create _home.html --post_type=page --post_status=publish --post_title='Home' --porcelain) && \
  wp option update show_on_front page && wp option update page_on_front \$ID && \
  wp rewrite flush && echo PAGE_ID=\$ID && rm _home.html"
```

> **WPE SSH load-balances across containers** — `/tmp` and uploaded files are
> per-container. Keep any "write a file then use it" sequence inside a **single**
> `ssh` invocation, or it'll vanish between calls.

---

## Verify the render (no browser needed)

New WPE installs sit behind **Basic-Auth password protection** (HTTP 401), so
you usually can't fetch the rendered page over HTTP. Render server-side instead
and check for fatals / blank blocks:

```bash
ssh -n <install>@<install>.ssh.wpengine.net "cd /home/wpe-user/sites/<install> && \
  wp eval 'echo do_blocks(get_post(<ID>)->post_content);' 2>/tmp/err >/tmp/out; \
  echo BYTES=\$(wc -c </tmp/out) ERR=\$(wc -c </tmp/err)"
```

`ERR=0` = no PHP errors. Then `grep` `/tmp/out` for expected markers
(`is-style-eyebrow`, `has-<slug>-background-color`, image `src=` / `background-image`,
your headings) to confirm fields resolved.

---

## Snapshot it into the repo

```bash
SNAP="wp-content/themes/sg-<slug>/page-snapshots"
ssh -n <install>@<install>.ssh.wpengine.net \
  "cd /home/wpe-user/sites/<install> && wp post get <ID> --field=post_content" \
  > "$SNAP/page-<ID>-home.html"
git add "$SNAP/" && git commit -m "homepage snapshot"
```

---

## Manual gates & gotchas (the things that block you)

> Canonical WPE platform reference: [`WPE_PLATFORM.md`](./WPE_PLATFORM.md)
> — the list below is the page-authoring subset.

- **Local pull (onboarding Phase 4)** is a GUI step in the Local app — only the
  user can do it; phases 5–9 operate on the pulled site root.
- **WPE Git Push key** is registered **per-install** in the WPE portal (Git Push
  tab) — separate from the SSH Gateway key and from the API. `git push wpe` fails
  with `publickey`/`DENIED by fallthru` until it's added.
- **WPE password protection** (401) on new installs — a portal toggle (Utilities
  → Password Protection). Disable it (or share creds) before sharing a review URL.
- **Cache:** wp-cli changes don't purge Varnish. `git push wpe main` runs the
  purge hook; or use the portal's "Purge all caches."
- **Slug vs install name** can differ (e.g. repo/theme slug `sherman-phalen`,
  WPE install `sandplaw`). The onboarding state file tracks both.

## See also

- `docs/THEME_TOKENS.md` — palette/fonts/tokens and the light/dark→canvas/panel/ink status
- `docs/CLIENT_ONBOARDING.md` — WPE install + repo + child theme scaffold (phases)
- `scripts/onboard-client/` — the phase-by-phase automation (all phases implemented)
- `sg-<slug>/page-snapshots/README.md` — snapshot refresh/restore loop

---

## Build conventions & gotchas (hard-won)

**Page structure**
- Use a `wp:group` **only for a contrasting section** (a colored/panel band). For
  content that stays on the page canvas, leave blocks ungrouped, or wrap the
  intro + block in a **transparent** group (no `backgroundColor`) purely for
  Gutenberg spacing. Don't wrap canvas content in a panel-bg group with canvas
  cards on top — the cards look like they're floating.
- **Card-esque blocks default to `panel`** (white): `split-50-50`,
  `image-card-grid`, `process-steps`, `icon-feature-row`. On the canvas, cards
  should be `panel`; *inside* a panel-colored section, set cards to `canvas` so
  they stay visible.
- **Never inline `border-radius` / `box-shadow` / `margin`** in page markup —
  they vanish when an editor touches the block. Radius + shadow come from the
  `is-style-section` block style (panels) or the block's own CSS
  (`cta-large`, `framed-callout`). Inline **padding is fine**. Section spacing is
  automatic via `--ccc-section-gap`. Full-width CTAs get `0` radius.
- Add `"metadata":{"name":"…"}` to every top-level block so the List View reads
  cleanly (Hero, Stats, Who We Help, …).

**S&P-piloted patterns (candidates to promote to the parent)**
- **Interaction standard:** one hover effect via `--fx-*` tokens (lift `-6px`,
  scale `1.03`, `shadow--md`, ~250ms ease) on buttons + linked image cards;
  buttons also slide a doubled (`main→alt→main`, `background-size:200%`) gradient
  on hover. In `sg-sherman-phalen/style.css`.
- **Brand duotone on photos:** an SVG filter (`#sp-duotone`, injected via the
  child `functions.php`) maps shadows→`ink`, highlights→`canvas`, applied to
  `.sg-image-card__image` + `.sg-block-split-image`. Same mechanism as WP-native
  duotone; tune via the `feFunc*` `tableValues` (shadow then highlight, 0–1 RGB).

**SSH / wp-cli gotchas (these cost real time)**
- Inline `wp eval "…do_blocks(get_post(N)->post_content)…"` breaks on `->` and
  nested quotes — use **`wp eval-file`** with a PHP file piped up over SSH.
- `wp post create --post_title='Two Words'` breaks remote arg parsing (the space
  splits) — run it via a heredoc piped to `bash -s`, or use a `--post_name`
  without spaces.
- `wp media import <url>` rejects extensionless URLs (Unsplash) — `curl` to a
  `.jpg` first; and `&` in a URL breaks shell loops (pipe a script via stdin).
- `for f in $(ls *.png)` breaks on spaces in filenames — use `for f in *.png`.
- `git subtree pull` needs a **clean working tree** — stash uncommitted edits
  first, pull, then pop.

**Misc**
- A standalone `.svg` is parsed as strict XML — escape `&` as `&amp;` (e.g. in
  `aria-label`). Inkscape 1.4 can't trace via CLI; use `potrace` + `mkbitmap`
  (convert PNG→PGM with PIL first).
