# Page Build Tracker

A per-client, self-contained HTML page that shows **content-migration
progress** — which pages are built, whether their real copy and images are
in, and how confident we are in each. It's generated from data we already
produce during a programmatic content import (see
[CLIENT_ONBOARDING.md](./CLIENT_ONBOARDING.md) Phase 12), and it regenerates
in seconds as pages land.

**What it's for:** an **internal deliverable you email to the client** so
they can see progress at a glance and click through to anything they want to
check. It is *not* a tool the client operates or a system they log into —
it's a snapshot. Treat it like a status report with links.

**Reference implementation (copy this):**
`wp-content/themes/sg-lumberock/content-files/image-library/build-tracker.py`
→ produces `page-tracker.html`, published to the client as a claude.ai
Artifact (shareable link) or attached to an email.

---

## What it shows

- **Hierarchy.** Pages grouped by nav column, with parent → child nesting
  (e.g. the Collections Hub with each collection page indented under it),
  matching the client's sitemap.
- **A five-column metric grid per page** (right-aligned, scannable):

  | Column | Type | Meaning |
  |---|---|---|
  | Status | checkbox | page exists in WordPress |
  | Content | checkbox | real (non-scaffold) copy imported |
  | Images | checkbox | real images wired in (not placeholders) |
  | Copy % | 0–100 | content confidence |
  | Img % | 0–100 | image confidence |
  | QA'd | checkbox | signed off in QA (a `QAD` set of slugs in the generator) |

- **Two icon-only links per row** (top level): the current live page and the new WPE page — open without toggling the accordion.
- **An accordion per page** (click the caret on the left) with the detail:
  the **old → new URL** mapping, links to the new page (WP Engine) and the
  old page (live site), a readiness breakdown, the per-image confidence
  tiers, and any content flags.

Design it on the **client's own brand** — pull the palette from the child
theme's `theme.json` so each tracker looks like the site it tracks. Keep it
**self-contained** (all CSS inline, no external assets, no thumbnails) so it
renders anywhere and publishes cleanly as an Artifact.

---

## Data contract

The generator needs five things per site. All of them are byproducts of a
Phase-12 content import — you're not creating new work, just emitting what
the import already knows.

| Input | Source |
|---|---|
| **Page tree** (columns → pages → children, labels, old URLs) | the client's sitemap / content-plan JSON |
| **Built map** `{slug: post_id}` | `wp post list --post_type=page --fields=ID,post_name,post_parent --format=csv` on the install |
| **Required image count per page** | length of each page JSON's `images[]` slot manifest |
| **Content flags per page** | the importer's content-quality scan (see below) |
| **Image assignment per page** | the Phase-1 image-assignment plan: `high` / `medium` / `low` per slot |

### Content flags (drives Content ✓ and Copy %)

Have the importer scan each page's copy at build time and emit flags. The
Lumberock importer does this in `lr_content_flags()`
(`sg-lumberock/scripts/build-page-from-json.php`); the heuristics generalize:

- **SCAFFOLD-COPY** — headings/eyebrows read like a brief, not customer copy
  ("What this page needs to do", "PAGE PURPOSE", "STRATEGIC NOTE",
  "placeholder", "lorem ipsum"). This is the important one: it catches pages
  the content team hasn't actually written yet.
- **NOTES-TODO** — the page's `notes` field still asks to pull/confirm content.
- **DEAD-LINKS** — every CTA/link target is a placeholder `#`.

### Image assignment (drives Images ✓ and Img %)

The Phase-1 assignment pass matches each image slot's `role`/`prompt`/aspect
against the site's image library and records a confidence tier per slot
(`high` / `medium` / `low`, or none → placeholder). See the image-library
pipeline in `sg-lumberock/content-files/image-library/` (manifest + INDEX +
`wire-images.py`).

---

## Confidence scoring

Keep these simple and defensible — they're shown to the client.

**Content confidence (Copy %)**
```
not built            → — (n/a)
scaffold copy        → 15          (floor: real content still needed)
otherwise            → 100 − 10 × (open copy issues: notes-todo, dead-links, …)
```

**Image confidence (Img %)** — weighted across all slots:
```
not assessed         → — (n/a)
otherwise            → round( 100 × (high·1.0 + medium·0.6 + low·0.25) / required )
```
Placeholder slots (`required − assigned`) contribute 0, so a page with lots
of unmatched slots scores lower — which is exactly the signal you want.

Tune the weights per engagement; document any change in the generator so the
number stays reproducible.

---

## Generator skeleton

Trim of the reference implementation — enough to stand a new one up. Fill
`TREE`, `BUILT`, `REQ`, `FLAGS`, `ASSESSED` from the data contract above,
and `PALETTE` from the child `theme.json`.

```python
# build-tracker.py — emits a self-contained page-tracker.html
PALETTE = {"navy":"#0E2047", "teal":"#086D5F", "ok":"#0B9D88",
           "warn":"#e8a33d", "crit":"#d1495b", "canvas":"#eef2f8"}  # from theme.json
BUILT   = {"classic-collection": 327, ...}          # from `wp post list`
REQ     = {"classic-collection": 20, ...}           # len(page.images[])
FLAGS   = {"artisan": [("critical","Scaffold copy — no real content")], ...}
ASSESSED= {"classic-collection": {"high":9,"medium":11,"low":0,"details":[...]}}
TREE    = [("Collections", [("collections-hub","Collections Hub",None,[
              ("classic-collection","Classic Collection","/classic-collection/",[]),
          ])]), ...]

def content_conf(slug, built, flags):
    if not built: return None
    if any(s=="critical" for s,_ in flags): return 15
    return max(0, 100 - 10*sum(1 for s,l in flags if s=="warn" and "rebuild" not in l.lower()))

def image_conf(a, req):
    if not (a and req): return None
    return round(100*(a["high"]*1.0 + a["medium"]*0.6 + a["low"]*0.25)/req)

# render: caret (left) · page name · 5-col metric grid (right);
# accordion body = old→new URL + WPE/old links + readiness + image tiers + flags.
# Use <details>/<summary> (native, accessible, no JS). Write <style> + body only
# (no <html>/<head>) so it drops straight into a claude.ai Artifact.
```

The full version (row renderer, CSS, rollup header, column-header row,
mobile handling) is in the Lumberock file — copy it and swap the five data
dicts + palette.

---

## Produce & deliver

```bash
python3 build-tracker.py          # regenerates page-tracker.html from current data
```

Then either:
- **Publish as a claude.ai Artifact** — gives a shareable link to email
  (default-private; the client sees only what you send). Re-publishing the
  same file redeploys to the same URL, so the link stays stable as you
  regenerate.
- **Attach the HTML file** to an email — it's fully self-contained.

Regenerate whenever pages build or images get wired; the checkboxes and
confidence scores move on their own from the refreshed data.

---

## Per-site adaptation checklist

1. Pull the **palette** from the child theme's `theme.json` (navy/teal/CTA +
   the `canvas`/`panel`/`ink` neutrals) so it's on-brand.
2. Build the **page tree** from the client's sitemap (columns, parent/child,
   old URLs).
3. Snapshot the **built map** from `wp post list` on the install.
4. Make sure the importer emits **content flags** and the image pipeline
   emits **assignment tiers** — those are what make the tracker more than a
   checklist.
5. Generate, eyeball, publish, send.

---

## See also

- [CLIENT_ONBOARDING.md](./CLIENT_ONBOARDING.md) Phase 12 — programmatic
  content deploy (the import that feeds this tracker).
- [BULK_IMPORT.md](./BULK_IMPORT.md) — CPT bulk imports; shares the
  state-tracking + WPE-gotcha patterns (SSH quote mangling: import media via
  a PHP `media_handle_sideload` script run through `wp eval-file`, **not** a
  `$(wp media import …)` shell loop — the gateway shell mangles it).
