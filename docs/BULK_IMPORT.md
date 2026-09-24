# Bulk Content Import Runbook

> **Upstream process:** this doc is stage 7 (BUILD) of
> [`MIGRATION_PIPELINE.md`](./MIGRATION_PIPELINE.md) — the block plans and
> routed content it imports come from that pipeline's CoS-side stages.

> **WPE platform behavior** (SSH gateway limits, WAF/UA blocking, quoting,
> caching) is canonically documented in
> [`WPE_PLATFORM.md`](./WPE_PLATFORM.md). This runbook applies those rules
> to bulk imports — if the two ever disagree, `WPE_PLATFORM.md` wins.

Lessons from importing 237 product posts (with galleries, taxonomy, and
featured images) into a WPE install in ~11 minutes. Applies to any
many-rows-per-CPT import — products, team members, case studies,
testimonials, news posts, etc.

The TL;DR you'll want to internalize before reading anything else:

> **Use the REST API, resize images locally, track state in a JSON file,
> and never call wp-cli more than a handful of times per row.**

For the two most common shapes, jump straight to **[Mass image uploads to
WPE](#mass-image-uploads-to-wpe-harvest--resize--alt-text--upload)** and
**[Migrating posts from a live source](#migrating-posts-from-a-live-source-scrape--structure--fast-import)**
below — they wrap these rules into a concrete pipeline, plus the WPE SSH-gateway
realities (scp blocked, long sessions killed, concurrency throttled) in
[§4 of the hybrid fallback](#4-wpes-ssh-gateway-also-blocks-scp-kills-long-sessions-throttles-concurrency).

---

## Command surface (agent quick-reference)

| Command | Safety | Notes |
|---|---|---|
| REST endpoint probes (tiny POST + `DELETE …?force=true` cleanup) | safe | Always probe before designing the import. |
| REST `GET` anything | safe | Parallelize freely — local-side work has no WPE bottleneck. |
| `wp user application-password create … --porcelain` | safe | Mint per job; store in gitignored `.env`. |
| REST writes / `wp eval-file` importer against **staging** | safe | Single-boot, batched, resumable; **serialize** all WPE-SSH work. |
| REST writes / importer against **production** | **confirm-first** | Re-confirm with Chris before the first write. |
| Deleting existing posts/terms/media in bulk | **stop — ask Chris** | Upserts (find-by-slug, update) are the safe default. |
| Committing `.env` / `import-state.json` | **never** | Gitignored working artifacts. |

## The single biggest mistake — and how to avoid it

The wrong instinct is "I'll write a Python (or Node) script that loops over
the data and calls `wp` over SSH for each operation." Each `wp` invocation
boots PHP and all of WordPress, which costs **5–10 seconds before any work
happens**. For a 200-row import with 3 wp-cli calls per row, that's
**30–100 minutes of pure interpreter boot overhead** before counting any
real work.

`wp media import` adds insult to injury — it generates every thumbnail
size synchronously, which on a high-res image can add another 20–30
seconds per file. A 200-row import with multi-image galleries on this
pipeline can easily run 3–4 hours.

**Don't do that.** Use the REST API end-to-end:

| Operation | Endpoint | Notes |
|---|---|---|
| Upload media | `POST /wp/v2/media` | **Thumbnails are deferred** — generated lazily on first request. |
| Create term | `POST /wp/v2/<taxonomy>` | Pass `parent` for hierarchy. |
| Look up term by slug | `GET /wp/v2/<taxonomy>?slug=…` | |
| Create / update post | `POST /wp/v2/<post-type>[/<id>]` | Set `featured_media`, taxonomy IDs, content in one call. |
| Find post by slug | `GET /wp/v2/<post-type>?slug=…` | For upsert logic. |

A single Python `requests.Session` keeps the HTTP connection alive across
hundreds of calls. The whole import becomes I/O-bound on WPE, not on local
interpreter startup.

For the import this runbook is based on, the median per-row time landed at
**2.8 seconds**, including downloading the source image, resizing it
locally, uploading it via REST, taxonomy lookups, and the post upsert.

---

## Before you write any import code: probe the install

WPE's WAF blocks REST writes on **some** installs but not others. Don't
assume — probe each endpoint you plan to use **before** committing to a
design. One curl per endpoint, takes 30 seconds.

```bash
USER="email@client.com"
PASS="xxxx-xxxx-xxxx-xxxx-xxxx-xxxx"
BASE="https://INSTALL.wpengine.com"

# Media upload
curl -X POST -u "$USER:$PASS" \
  -H "Content-Type: image/png" \
  -H "Content-Disposition: attachment; filename=probe.png" \
  --data-binary "@/tmp/some-tiny.png" \
  "$BASE/?rest_route=/wp/v2/media" -w "\n%{http_code}\n"

# Post create (your CPT)
curl -X POST -u "$USER:$PASS" \
  -H "Content-Type: application/json" \
  -d '{"title":"probe","status":"draft"}' \
  "$BASE/?rest_route=/wp/v2/YOUR_CPT" -w "\n%{http_code}\n"

# Term create (your taxonomy)
curl -X POST -u "$USER:$PASS" \
  -H "Content-Type: application/json" \
  -d '{"name":"probe","slug":"probe","parent":0}' \
  "$BASE/?rest_route=/wp/v2/YOUR_TAXONOMY" -w "\n%{http_code}\n"
```

All three should return **201**. Clean up the probes afterward (`DELETE
/wp/v2/...?force=true`).

If any of them returns **403 from nginx**, REST is blocked for that
endpoint on that install. Two options:
1. Open a WPE support ticket to tune the WAF rule.
2. Fall back to SSH + wp-cli for *just that operation* (see the appendix).

---

## Auth: WP Application Passwords

Don't use the admin user's real password over Basic Auth. Mint an
Application Password per import job:

```bash
# Via wp-cli over SSH (one-liner, 1 second):
ssh INSTALL@INSTALL.ssh.wpengine.net \
  "cd /home/wpe-user/sites/INSTALL && \
   wp user application-password create EMAIL@CLIENT.COM 'bulk-import' --porcelain"
```

Returns a 24-char password. Store it in a `scripts/.env` file (gitignored,
never committed). Two values:

```dotenv
WP_USER=email@client.com         # often the email, not a short username
WP_APP_PASSWORD=tqndi3iVGUfCVDMSJfeig5K5
```

Quirk: WPE often provisions admin users with `user_login` = the email
address. If REST auth fails with `rest_not_logged_in`, check with
`GET /wp/v2/users/me` before chasing other rabbit holes.

---

## Gotcha: WPE's WAF blocks the default `python-requests` User-Agent

A nasty silent failure: `requests.get(URL, auth=...)` returns **403 from
nginx** even though the same request via curl works fine. Cause: the
default `python-requests/2.x` UA string is on a WAF blocklist.

Fix:

```python
session = requests.Session()
session.headers["User-Agent"] = "Your-Importer/1.0"
```

Any non-default UA passes. Cost: one line. Time to discover without
knowing this: hours.

---

## Always resize images locally before upload

Two reasons:

1. **Bandwidth** — A 3000-px PNG product photo is multi-MB. A 1000-px
   JPG of the same image is ~25 KB. Uploading 230 images becomes a
   not-noticeable fraction of total time.

2. **Reduced server load** — WP will still generate thumbnails, just
   lazily. Smaller source images = smaller / faster thumbnails when they
   do get generated.

Pillow handles this in a few lines:

```python
from PIL import Image, ImageOps

def resize_for_upload(src, dest, max_dim=1000, quality=82):
    img = Image.open(src)
    img = ImageOps.exif_transpose(img)               # honor EXIF rotation
    if img.mode in ("RGBA", "LA", "P"):              # flatten alpha
        bg = Image.new("RGB", img.size, (255, 255, 255))
        bg.paste(img, mask=img.convert("RGBA").split()[-1])
        img = bg
    elif img.mode != "RGB":
        img = img.convert("RGB")
    img.thumbnail((max_dim, max_dim), Image.Resampling.LANCZOS)
    img.save(dest, "JPEG", quality=quality, optimize=True)
```

For product / catalog-style content, **1000 px JPG at quality 82** is the
sweet spot: smaller than the typical WP `large` thumbnail, no visible
quality drop on cards or detail pages, ~75% smaller than the source.

If the use case is large hero imagery (full-bleed editorial), bump to
1600–2000 px.

---

## State tracking is non-negotiable

Any bulk import that takes more than a minute needs a state file. Reason:
the process WILL get interrupted (network, terminal closing, you sleeping
on it). Without state, you have no idea what's done and re-running blindly
either duplicates work or skips real failures.

Use a JSON file keyed by the unique import identifier (slug, source ID,
external URL — whatever you have). For each row track:

```json
{
  "row-identifier-here": {
    "status": "done" | "in_progress" | "failed" | "pending",
    "post_id": 123,
    "term_ids": [4, 7, 12],
    "image_ids": [125, 126, 127],
    "attempts": 1,
    "duration_s": 3.2,
    "last_error": "...",      // when failed
    "completed_at": 1750000000,
    "tried_at": 1750000005
  }
}
```

Write atomically (write to `.tmp`, rename) **after every row** so a kill
mid-run doesn't corrupt the file:

```python
def save(self):
    tmp = self.path.with_suffix(".tmp")
    with open(tmp, "w") as f:
        json.dump(self.data, f, indent=2, sort_keys=True)
    tmp.rename(self.path)   # atomic on POSIX
```

Then your CLI should support:

- `--status` — print a one-screen summary (counts + failures with reasons)
- `--resume` *(default)* — skip `done`, attempt `pending` and `failed`
- `--retry-failed` — only re-try the failed rows
- `--all` — ignore state, treat every row as pending
- Positional slugs — import just specific rows

Idempotent upsert: look up by slug first, PUT if found, POST if not.

The state file should be **gitignored**. It's a per-machine working
artifact, not source-of-truth content.

---

## Per-row order of operations

Inside one import-row function, do them in this order:

```
1. Download remote assets to a local tmpdir (parallel via ThreadPool)
2. Resize / transform locally
3. Upload assets via REST → collect (id, url) per asset
4. Build the taxonomy term chain (parent-first), creating missing terms
5. Compose the post content with the real attachment IDs / URLs
6. POST (or PUT) the post with content + featured_media + taxonomy IDs
7. Write state: done + post_id + term_ids + image_ids + duration
```

Catch any exception around steps 1–6, dump the row's error into
`state[id].last_error`, increment `attempts`. The script keeps going to
the next row; you sort failures out at the end via `--retry-failed`.

Per-step download + resize is cheap (sub-second per image with parallel
download). The REST calls are the long pole — that's where wall-time
goes.

---

## Progress display

Every line in the main loop, print one short line with everything you
need to gauge health and ETA:

```
[ 42/237] ✓ some-product-slug                     3img    2.4s  post=156  avg 2.5s/p  ETA 8m
[ 43/237] ✗ broken-product-slug                   1img    0.8s  post=—    avg 2.5s/p  ETA 8m
      → 400 Bad Request: term not found...
```

Columns to include:
- Row index / total
- Success marker (✓ or ✗)
- Slug or identifier (left-padded so things align)
- Asset count + per-row duration
- Resulting post ID
- Rolling average per-row time
- ETA based on rolling average

Always pass `flush=True` to `print` so the output file is tail-able while
the script runs in a background task.

---

## Don't forget: cleanup on the way out

After a successful row, delete the local tmpdir. Otherwise a 237-row
import leaves 237 directories under `/tmp` consuming disk.

```python
try:
    # ... all the import work ...
finally:
    shutil.rmtree(tmpdir, ignore_errors=True)
```

If the script writes intermediate files server-side (it shouldn't for a
pure-REST flow, but might if you're falling back to wp-cli), clean those
up too.

---

## Hybrid fallback — when REST is blocked

Even if `POST /wp/v2/media` is blocked, you can still do an OK job with
SSH + wp-cli. Two patterns matter:

### 1. SSH connection multiplexing

Without multiplexing, each `subprocess.run(["ssh", host, cmd])` does a
fresh TCP+SSH handshake (~2s on WPE). With multiplexing, the second-onward
connection reuses the master socket (~50ms).

Add this to `~/.ssh/config`:

```
Host INSTALL.ssh.wpengine.net
    ControlMaster auto
    ControlPath ~/.ssh/cm/%C
    ControlPersist 5m
```

Then `mkdir -p ~/.ssh/cm` once. Subsequent ssh calls from any process
reuse the tunnel transparently.

### 2. Batch wp-cli calls per row

A single `wp media import file1 file2 file3 --porcelain` imports all the
images and outputs IDs in order — **one wp-cli boot instead of N**.

```bash
mapfile -t IDS < <(wp media import file1.jpg file2.jpg file3.jpg --porcelain)
```

For URL lookups after import, use `wp eval` once with the ID list baked in:

```bash
ID_CSV=$(IFS=,; echo "${IDS[*]}")
URLS=$(wp eval "foreach ([${ID_CSV}] as \$id) { echo \$id . chr(9) . wp_get_attachment_url(\$id) . PHP_EOL; }")
```

### 3. WPE's SSH gateway mangles command quoting

This one wastes hours if you don't know:

```bash
# This silently corrupts: "PATIENT CARE" becomes two args "PATIENT" + "CARE"
ssh INSTALL@INSTALL.ssh.wpengine.net 'wp term create TAX "PATIENT CARE" --slug=patient-care'
```

The fix: pipe the command via **stdin** instead of passing it as ssh args.

```bash
echo 'wp term create TAX "PATIENT CARE" --slug=patient-care' \
  | ssh INSTALL@INSTALL.ssh.wpengine.net bash
```

Quotes survive intact because ssh forwards stdin byte-for-byte to the
remote shell.

In Python:

```python
subprocess.run(["ssh", host, "bash"], input=remote_command, text=True)
```

---

### 4. WPE's SSH gateway also: blocks scp, kills long sessions, throttles concurrency

Three more gateway behaviors that each cost real time on the trialport build:

- **`scp` / `sftp` are blocked** (`Connection closed by remote host`, exit 255).
  To move a file onto the install, pipe it through an ssh command instead — the
  same stdin trick as the quoting fix, applied to a file:
  ```bash
  ssh INSTALL@INSTALL.ssh.wpengine.net \
    "cat > '/home/wpe-user/sites/INSTALL/wp-content/uploads/_import/bundle.tgz'" \
    < local-bundle.tgz
  ```
  Upload under `wp-content/uploads/...` — always writable. Extract server-side.

- **Long-running sessions get killed.** A loop of 40+ `wp media import` calls in
  one ssh session is cut off partway (each call re-boots WP *and* regenerates
  every image sub-size = several seconds each). **Don't loop wp-cli over one ssh
  session.** Upload a server-side PHP script + data and run it in **one WP boot**,
  in **resumable batches** so no single session runs too long:
  ```php
  // importer.php — run via: ssh host "cd SITE && wp eval-file uploads/_import/importer.php"
  // ONE bootstrap; processes up to N rows; skips rows already recorded in a map
  // file so it can be re-invoked until complete. Use media_handle_sideload() for
  // images (copy + insert + metadata in-process) and wp_insert_post() for posts.
  ```
  Drive it from a bash loop that re-invokes the eval-file until the done-count
  hits the total (each call is a fresh, short session; the script resumes via its
  map file). This turns 43 WP boots into ~3 short sessions.

- **Never run two SSH-heavy tasks against the same install at once.** The gateway
  rate-limits concurrent connections per install; two parallel importers starve
  each other. Observed on trialport: a trivial command took **6m47s** while two
  tasks contended, and one importer looked "hung for 17 minutes" — it was just
  starved. **Serialize all WPE-SSH work.** Local-only work (REST *pulls*, image
  resizing, HTML cleaning) parallelizes freely — the SSH gateway is the bottleneck.

---

## Mass image uploads to WPE (harvest → resize → alt text → upload)

Importing a library of images (a media harvest, a photo set) with good,
*searchable* metadata:

1. **Download** the sources locally, in parallel. **Dedupe by source URL** — each
   unique image is downloaded and uploaded once, even if referenced by many rows.
2. **Resize before upload — always.** No-dependency option on macOS is `sips`
   (check dimensions first so you don't upscale a small image):
   ```bash
   sips -Z 1500 -s format jpeg -s formatOptions 78 in.ext --out out.jpg
   ```
   Pillow's `img.thumbnail((max,max), LANCZOS)` is the cross-platform equal. Pick
   the max edge by role: ~1000px cards/catalog, 1500–2000px hero/editorial.
3. **Author alt text + descriptions by LOOKING at each image.** When the sources
   have no usable alt, run a **subagent that VIEWS each downloaded file** and
   writes: a specific alt (describe what's literally shown, no "image of…"), a
   title, and a 1–2 sentence description in the brand's context. This is the one
   step automation can't fake — it needs a model that can see the image. Good
   alt/title/description is what makes the media library searchable later.
4. **Upload.** Prefer REST `POST /wp/v2/media` (probe first; fastest, thumbnails
   deferred). If REST writes are blocked, use the SSH path: `cat >`-pipe a tarball
   of the processed files + a metadata TSV to the install, then a **single-boot**
   `wp eval-file` that `media_handle_sideload()`s each file and sets its title /
   caption / description + `_wp_attachment_image_alt` — batched + resumable (§4).

---

## Migrating posts from a live source (scrape → structure → fast import)

For migrating a blog (or any post type) off a live site while preserving content,
metadata, and **URLs**. The winning shape: **do all the heavy, WP-free work
locally first, emit a structured bundle, then import in one fast solo pass.**
Never build the whole migration on live SSH calls.

**Phase 1 — Scrape + structure (LOCAL, no target WP, fully parallelizable):**
- Pull the source via its **REST API** when it's WordPress:
  `/wp-json/wp/v2/posts?per_page=100&_embed` gives `content.rendered`,
  `excerpt.rendered`, `date`/`date_gmt`, `slug`, author, `categories`/`tags`
  (names+slugs via `_embedded.wp:term`), `featured_media`
  (`_embedded.wp:featuredmedia`), and — key — **`yoast_head_json`** (SEO title,
  description, canonical, og/twitter). Request a **WXR export** from the client as
  a reconciliation net (it also carries drafts + Yoast focus keywords the head
  JSON omits).
- **Clean each post's HTML to simple content:** strip all inline `style=`,
  unwrap/delete custom wrapper divs (TOC, author-box, disclaimers, page-builder
  chrome), drop stale `srcset/sizes/lazy/width/height` attrs, keep only clean
  semantic elements. Do **not** carry over custom blocks or colorations.
- **Process images locally** (download + resize + dedupe, above) and **replace
  each `<img src>` with a placeholder token** (`__IMG__<key>__`) in the cleaned
  HTML. The importer swaps the token for the real uploaded URL after it sideloads
  the image — this decouples cleaning from uploading.
- **Generate missing excerpts** (2 sentences, within WP's bound) and flag them.
- **Emit a bundle:** `posts.json` (per post: slug, title, excerpt, date(s),
  author, cats/tags, cleaned `content_html` with tokens, featured-image key,
  image keys, `yoast{}`), `terms.json` (unique cats+tags), `images-manifest.json`,
  and the processed `images/` dir.

**Phase 2 — Import fast (ONE solo pass against the target):**
- **Preserve URLs:** set the target permalink to match the source (usually
  `/%postname%/`) and create each post with its **exact source slug**. Pre-clear
  conflicts so WP doesn't append `-2`.
- Prefer **REST writes** (probe `POST /wp/v2/posts` first). If blocked, `cat >`-pipe
  the bundle and run a **single-boot** `wp eval-file` importer that: creates terms;
  `media_handle_sideload()`s each image once (reuse IDs); `wp_insert_post()`s each
  post (title/slug/date/author/content); swaps `__IMG__` tokens for the sideloaded
  URLs; sets featured image; attaches terms; writes `_yoast_wpseo_*` meta. Batched,
  resumable, and **run alone** (§4).
- State-track by slug so a killed session resumes cleanly.

Because Phase 1 is local + parallel and Phase 2 is one tight server-side pass, a
100-post blog with images migrates in minutes — not the hours a per-row-over-SSH
loop would take.

---

## Concrete starter (Python)

Use this as the skeleton. Fill in the per-row composition logic for your
specific CPT.

```python
#!/usr/bin/env python3
import argparse, json, os, re, shutil, tempfile, time, urllib.request
from collections import Counter
from pathlib import Path

import requests
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry
from PIL import Image, ImageOps

# --- config ---
BASE_URL = "https://INSTALL.wpengine.com"
SCRIPT_DIR = Path(__file__).parent
STATE_FILE = SCRIPT_DIR / "import-state.json"

# --- env ---
for line in (SCRIPT_DIR / ".env").read_text().splitlines():
    if "=" in line and not line.startswith("#"):
        k, _, v = line.partition("=")
        os.environ.setdefault(k.strip(), v.strip().strip('"').strip("'"))

# --- HTTP session ---
def make_session():
    s = requests.Session()
    s.auth = (os.environ["WP_USER"], os.environ["WP_APP_PASSWORD"])
    s.headers["User-Agent"] = "Importer/1.0"
    retry = Retry(total=5, backoff_factor=2,
                  status_forcelist=[502, 503, 504, 429],
                  allowed_methods=["GET", "POST", "PUT", "DELETE"])
    s.mount("https://", HTTPAdapter(max_retries=retry, pool_maxsize=4))
    return s

# --- state ---
class State:
    def __init__(self, path):
        self.path = path
        self.data = json.load(open(path)) if path.exists() else {}
    def get(self, k): return self.data.get(k, {"status": "pending"})
    def update(self, k, **kw):
        cur = self.data.get(k, {}); cur.update(kw); self.data[k] = cur
        tmp = self.path.with_suffix(".tmp")
        with open(tmp, "w") as f:
            json.dump(self.data, f, indent=2, sort_keys=True)
        tmp.rename(self.path)
    def counts(self):
        return Counter(v.get("status", "?") for v in self.data.values())

# --- per-row import ---
def import_row(sess, row, state):
    rid = row["id"]
    t0 = time.time()
    state.update(rid, status="in_progress",
                 attempts=state.get(rid).get("attempts", 0) + 1)
    tmpdir = Path(tempfile.mkdtemp())
    try:
        # 1. Download + resize images
        # 2. Upload via POST /wp/v2/media → collect (id, url)
        # 3. Build taxonomy chain via GET-or-POST /wp/v2/<tax>
        # 4. Compose post_content with real ids/urls
        # 5. Find post by slug + POST (create) or POST to /<id> (update)
        # 6. state.update with done + post_id + ids + duration
        ...
    except Exception as e:
        err = f"{type(e).__name__}: {e}"
        if isinstance(e, requests.HTTPError) and e.response is not None:
            err += f" | body: {e.response.text[:300]}"
        state.update(rid, status="failed", last_error=err[:1000],
                     duration_s=round(time.time() - t0, 1))
        return False
    finally:
        shutil.rmtree(tmpdir, ignore_errors=True)
```

---

## Final realistic numbers

For reference, an import that exercises every pattern above (download,
resize, upload, taxonomy hierarchy, post upsert, state tracking):

| Per row | Notes |
|---|---|
| 1 image | ~1.5 s |
| 3 images | ~3 s |
| 5 images | ~6 s |
| 11 images | ~8 s |
| Average across 230 mixed rows | **2.8 s** |

Plan for ~3 seconds per row plus a steady-state pause for any
sub-second-flaky parts of the network. A 1000-row import is comfortably
under an hour. A 5000-row import takes a few hours of wall time but
runs unattended.

---

## What NOT to do (quick list)

- ❌ Don't call `wp media import` per file when the REST endpoint works.
- ❌ Don't make >5 separate SSH connections per row. Either batch via
  one ssh-bash-stdin session or use REST.
- ❌ Don't use bash heredocs to ship large base64-embedded files; parsing
  multi-MB heredocs is slow.
- ❌ Don't trust `--skip-existing` alone — keep a state file.
- ❌ Don't upload raw source images. Resize first.
- ❌ Don't run with the default Python `requests` UA against WPE. 403.
- ❌ Don't pass quoted strings as `ssh host '... "value"'` args. Pipe via
  stdin.
- ❌ Don't `scp`/`sftp` to a WPE install — the gateway blocks it. Pipe files
  via `ssh host "cat > path" < file` into `wp-content/uploads/…`.
- ❌ Don't loop wp-cli over one long ssh session — the gateway kills it partway.
  Use a single-boot `wp eval-file` (`media_handle_sideload` / `wp_insert_post`),
  batched + resumable.
- ❌ Don't run two SSH-heavy tasks against one install at once — the gateway
  throttles concurrency and they starve each other. Serialize WPE-SSH work.
- ❌ Don't build a migration on live SSH calls — scrape + clean + resize locally
  into a bundle first (tokenize image `src`s), then import in one solo pass.
- ❌ Don't commit `.env` or `import-state.json`.
- ❌ Don't run a long import in the foreground of your terminal session.
  Use a background process with output piped to a log file, and tail it
  if you want to watch.
