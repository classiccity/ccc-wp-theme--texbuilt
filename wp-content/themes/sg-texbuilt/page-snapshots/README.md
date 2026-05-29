# Page snapshots — TEXBUILT

Plain-HTML snapshots of every published page's `post_content` on the
live WPE install (`texbuilt1`), captured at known-good points. This is
a belt-and-suspenders safety net **in addition to** WP Engine's
backup-and-restore system.

## When this saved us

The kickoff snapshot in this folder was taken right after a near-miss
where a chained shell pipe wrote an empty file to WPE, then
`wp post update 21 <empty-file>` happily blanked the homepage content.
The recovery worked because the page state was reconstructible from
the live session, but a snapshot like this means "next time" is a 30-
second `cp` instead of a rebuild from memory.

## What's in here

One `page-<id>-<slug>.html` file per tracked page. The reusable blocks
that pages reference (`wp:block {"ref": N}`) are also snapshotted with
the `_reusable-block_<id>_<slug>` prefix — those affect every page
they're embedded in, so they're worth preserving alongside.

| File | What it is |
|---|---|
| `page-21-home.html` | Homepage |
| `page-57-company-who-we-are.html` | /company/who-we-are/ |
| `page-60-company-leadership.html` | /company/leadership/ |
| `page-63-company-culture-community.html` | /company/culture-community/ |
| `page-64-company-employee-owned.html` | /company/employee-owned/ |
| `page-65-services.html` | /services/ |
| `page-66-careers.html` | /careers/ |
| `page-67-become-a-trade-partner.html` | /become-a-trade-partner/ (was /become-a-subcontractor/) |
| `page-68-become-a-trade-partner_prequalification.html` | /become-a-trade-partner/prequalification/ |
| `page-69-company.html` | /company/ landing |
| `page-76-coming-soon.html` | /coming-soon/ holding page |
| `page-80-become-a-trade-partner_thank-you.html` | /become-a-trade-partner/thank-you/ post-submit landing |
| `page-62-contact-us.html` | /contact-us/ |
| `page-269-projects.html` | /projects/ landing (category grid) |
| `page-260-projects_medical.html` | /projects/medical/ |
| `page-261-projects_entertainment.html` | /projects/entertainment/ |
| `page-262-projects_fuel.html` | /projects/fuel/ |
| `page-263-projects_grocery.html` | /projects/grocery/ |
| `page-264-projects_hospitality-multifamily.html` | /projects/hospitality-multifamily/ |
| `page-265-projects_industrial.html` | /projects/industrial/ |
| `page-266-projects_municipal.html` | /projects/municipal/ |
| `page-267-projects_office.html` | /projects/office/ |
| `page-268-projects_retail.html` | /projects/retail/ |
| `page-54-...basic-cta` etc. | Reusable blocks referenced by multiple pages |

## How to refresh the snapshots

After a deploy you're happy with, from the project site root:

```bash
SNAP="wp-content/themes/sg-texbuilt/page-snapshots"
SPECS="21|home
57|company-who-we-are
60|company-leadership
63|company-culture-community
64|company-employee-owned
65|services
66|careers
67|become-a-trade-partner
68|become-a-trade-partner_prequalification
69|company
76|coming-soon
80|become-a-trade-partner_thank-you
62|contact-us
260|projects_medical
261|projects_entertainment
262|projects_fuel
263|projects_grocery
264|projects_hospitality-multifamily
265|projects_industrial
266|projects_municipal
267|projects_office
268|projects_retail
269|projects
54|_reusable-block_54_project-gallery
55|_reusable-block_55_basic-cta
56|_reusable-block_56_section-heading"

echo "$SPECS" | while IFS='|' read -r ID SLUG; do
  [ -z "$ID" ] && continue
  ssh -n texbuilt1@texbuilt1.ssh.wpengine.net \
    "cd /home/wpe-user/sites/texbuilt1 && wp post get $ID --field=post_content" \
    > "$SNAP/page-${ID}-${SLUG}.html"
done
git add "$SNAP/" && git commit -m "Refresh page snapshots"
```

Critical: `ssh -n` is required so the loop's stdin isn't consumed by
ssh — without `-n` only the first iteration runs.

## How to restore a single page from a snapshot

```bash
# Pick the file you want restored:
FILE="wp-content/themes/sg-texbuilt/page-snapshots/page-21-home.html"
ID=21

# Pipe the snapshot up to WPE and run wp post update against it
cat "$FILE" | ssh texbuilt1@texbuilt1.ssh.wpengine.net \
  "cat > /home/wpe-user/sites/texbuilt1/_restore.html"
ssh texbuilt1@texbuilt1.ssh.wpengine.net \
  "cd /home/wpe-user/sites/texbuilt1 && wp post update $ID _restore.html && rm _restore.html"

# Then trigger a Varnish purge by pushing any trivial change to the wpe remote
# (or hit "Purge all caches" in the WPE User Portal)
```

## What this is NOT

- **Not a full backup.** Media library, plugins, options, ACF field
  definitions, Gravity Forms forms/entries, users — none of that lives
  here. For full recovery, restore from a WP Engine snapshot.
- **Not auto-refreshed.** This folder is updated manually after
  significant content milestones. There's no hook keeping it in sync
  with the live install.
- **Not the source of truth.** The live WPE database is canonical.
  These files are a recovery aid only.
