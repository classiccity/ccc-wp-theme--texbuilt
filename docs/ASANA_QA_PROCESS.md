# Asana + Marker.io QA — Process Playbook

Chris's standard workflow for running QA on a Classic City client site
from an Asana board of Marker.io tickets. This lives in the parent theme
so it rides the subtree into every client site — the path
`wp-content/themes/classic-city-core/docs/ASANA_QA_PROCESS.md` is the
same on every site.

**Trigger:** Chris hands over an Asana project/list URL for QA, or says
things like *"let's do QA on this Asana,"* *"QA this site,"* *"run
through the marker tickets,"* *"here's the Asana, let's go."* When that
happens, follow this end-to-end. (A user-level skill `asana-qa` points
here.)

---

## What you're given

- An Asana **board/project URL**. The project id is the long number
  after `/project/` in the URL.
- Tickets are Marker.io-generated, prefixed `[Marker.io]`. Each has:
  - A **description** = the client's actual comment. The `[Marker.io]`
    **title is AI-generated and unreliable** — never triage from the
    title alone.
  - An **Asana attachment** — an annotated screenshot on
    `asananusercontent.com` showing exactly where the change goes.
    **This is the source of truth.**
  - Often **off-site links**: `app.marker.io` (needs login, skip) and
    `files.marker.io` example attachments (**may be expired/dead** —
    flag as an open question if so).
  - A human ref like `#PUB-30` in the body, and a `Source URL` telling
    you which page the ticket is about.

---

## Step 0 — Connect

- Use the **Asana MCP** (`mcp__asana__*`).
- `asana_get_tasks {project}` with `opt_fields: name,completed,notes,permalink_url`
  → the full list.
- For each open task, `asana_get_attachments_for_object {parent: <task_gid>}`
  with `opt_fields: name,download_url` → download the screenshot (curl)
  and **actually look at it** (Read the image). The red-ink annotations
  carry the real instruction; the description is often terse ("Update",
  "Notes", "Lose this").

## Step 1 — Read every open ticket + its screenshot

- The QA queue is the **incomplete** tickets.
- Triangulate: description ↔ screenshot markup ↔ `Source URL` page.

## Step 2 — Age heuristic

- **Tickets older than ~10 days are probably already done.** Older
  rounds are frequently shipped but never closed in Asana. Newer tickets
  (days old) are the real remaining work.
- **Verify against the live site** before claiming something is done —
  `WebFetch`/`curl` the page. Note: WebFetch reads markdown, so it
  **can't judge colors/fonts** reliably; trust the client's own
  screenshots for visual state and WebFetch for text/structure.

## Step 3 — Triage into three confidence tables

Every row has: **Ticket # linked to its Asana permalink**, **the
client's request** (paraphrased from the description *and* screenshot),
**what you plan to accomplish**.

1. **100% confidence** — clear, self-contained, will complete with no
   further input. Fold in already-live items here, marked ✅.
2. **Confident once one question is answered** — state the *exact*
   question (e.g., "one phone number or two?", "cleared to license these
   123RF IDs, or will you send the files?").
3. **No confidence** — needs client substance you can't supply (e.g.,
   authoritative spec data you can't verify).

### Where those tables go — the 10-ticket rule

**Chris's call, 2026-08-19.** Markdown tables in a terminal stop being
readable somewhere around a dozen rows, and a real QA round is 40–90
tickets.

- **10 or fewer tickets → print the tables inline in chat.** A file is
  overhead at that size and Chris has to go open it.
- **More than 10 → the HTML report IS the deliverable.** Build it with
  `classic-city-core/scripts/qa/asana-triage-report.py` and write it to
  the client theme's **`docs/qa/asana-qa-triage.html`** — un-dated, so a
  link to the current report never goes stale (see *The filename scheme*
  below). It commits with the work. Keep the JSON beside it, dated
  (`asana-qa-triage-YYYY-MM-DD.json`) — the next session picks up the
  state without re-deriving it. In chat, give the path plus the headline
  counts and the decisions Chris actually has to make; do not also paste
  90 rows.

> **NEVER publish these as an Artifact.** Chris's standing rule, set
> 2026-08-21: *"I ALWAYS want these files as flat HTML files that are
> committed in the repo and never an artifact."* A QA report is a project
> record — it belongs in the client's repo next to the work it describes,
> versioned with it and diffable against the previous round. An Artifact
> lives somewhere else, drifts out of date the moment the report is
> regenerated, and cannot be read by the next session. Hand over the file
> path. Do not offer a hosted link as a convenience.
>
> **Superseded reports stamp themselves.** On 2026-08-21 Chris opened
> the previous day's file and read its stale counts as current. Archived
> builds now carry an amber banner naming the current report, and the
> date dropdown in the navbar exists for the same reason. **The script
> writes both — there is nothing to remember.** Beware the count that
> looks unchanged: that day "waiting" stayed at 11 while its contents
> almost entirely turned over.

The script's `--fragment` flag emits a head-less fragment for embedding
in a page that supplies its own doctype. It is **not** for Artifacts (see
above) and is rarely what you want — the default standalone file is.
Run `python3 asana-triage-report.py --help` for the
input schema; it validates status values and titles and refuses rather
than emitting a broken page.

**Four fields that are easy to miss** (`num` is a fifth, but you never
write it — see *Ticket numbers* below):

- **`page_url`** — Marker.io puts the page the ticket is about in every
  description as `Source URL:`. Pull it out
  (`re.search(r'Source URL:\s*(\S+)', task['notes'])`) and pass it
  through; each row then carries a direct link to the page beside its
  Asana link. Every ticket in the trialport round had one.
- **`title`** — YOUR words, never the `[Marker.io]` title. Those are
  AI-generated and unreliable, and the whole point of the report is that
  someone read the screenshot.
- **`did`** *(added 2026-08-21)* — **what was done, in non-technical
  words the client would actually read.** It is the third thing in the
  detail panel, right under the request, and it is the line that gets
  pasted into the Asana comment and the client email. It is **the
  client-facing field** — see *The divider is a client/internal
  boundary* below, which is the rule you are most likely to break. Keep
  it to a sentence or two, name no file paths, no CSS, no block names,
  no media IDs, no page IDs, no commit hashes — `plan` is where all of
  that belongs. **It does NOT fall back to `plan`**; a ticket without a
  `did` prints a one-line placeholder above the divider instead.
  **`done` is accepted as a legacy alias** — trialport's round already
  carried the field under that name. `did` wins if both are present.
- **`q`** — the single exact question for the client, and by far the most
  load-bearing optional field. **Any status may carry one** (a shipped
  ticket often ships the mechanical half and still owes an answer), and
  every ticket with a non-empty `q` lands in the report's
  *"❓ Has a question for the client"* filter — the first option in the
  page dropdown, and the view Chris opens to see what he owes the client.
  Write it so the client could read it and reply to it directly.

Both links live in the row as icons (external-link and Asana's three
dots), drawn inline as SVG — these files are committed as standalone
documents and opened over `file://`, so an icon font or a CDN sprite
renders as nothing at all. They sit
*beside* the row's button rather than inside it: a link nested in a
`<button>` is invalid and browsers drop it.

### Ticket numbers — one number space, assigned once, stored in the JSON

**Chris's call, 2026-08-21. This REVERSES the 2026-08-19 per-section
numbering — do not "restore" it as a regression fix.** The old scheme
numbered rows *within* each section, restarting at 1, on the reasoning
that *"the third one down under Blocked"* is how people refer to a list
on a call. In practice that put a `1`, a `2` and a `3` in every one of
the four sections, and the moment a number left the page — into a commit
message, an Asana comment, a client email, a phone call — it named four
different tickets at once. A number that needs a section name beside it
to disambiguate it is not a number, it is a coordinate. **Numbers are now
unique across the whole report.**

Four properties, all of them non-negotiable:

1. **Globally unique** in a report. Two tickets never share a number.
2. **Stable across regenerations.** The report is rebuilt after every
   work pass. A number derived from list position is therefore banned
   outright: ticket 43 becoming ticket 51 because something above it was
   added or re-sectioned silently rots every reference already out in the
   world.
3. **On every ticket** — including the ones with no Asana task at all.
4. **Never re-used.** A deleted ticket's number retires with it.

The only scheme that satisfies all four is an explicit field, so:

- **`num` is an integer field on each ticket in the JSON.** It is the
  ticket's identity and reads first in the object.
- **The script assigns it, and writes it back into your input JSON** —
  any ticket lacking one takes the next integer above the high-water
  mark, in file order. It prints what it did
  (`numbers: assigned 12 new ticket number(s) (86–97) and wrote them back
  to …`). `--no-number-writeback` renders a dry run without touching the
  file; the numbers it *would* assign still appear in that build.
- **Never edit `num` by hand, and never re-use one.** Adding a ticket
  means adding it without a `num` and letting the script number it.
- **Two tickets carrying the same number is a hard failure.** The script
  exits non-zero, names both by index and title, and refuses to build.
  The silent duplicate is the bug this whole scheme exists to kill.
- **The high-water mark is `asana-qa-triage.seq`**, two lines of text the
  script maintains beside the report, backed up by a scan of every
  sibling `asana-qa-triage*.json`. It is what makes a retired number stay
  retired — without it, deleting the highest-numbered ticket would hand
  its number to the next one added. **Commit it with the JSON.** If it is
  ever lost, the script rebuilds what it can from the JSONs it can see;
  that is a degradation, not a failure.

The number appears in exactly two places: the list row, and the **No.**
cell of the detail panel's meta grid. Section badges keep showing a
**count** ("4 Blocked") — that is a count, not a number, and it is
unaffected by any of this.

### Tickets that came from a conversation, not from Marker

Chris raises plenty of tickets inline in chat, and they go into the same
JSON as the Marker ones. **They are numbered identically** — that is the
point of property 3 above.

To add one: append an object with `title`, `page`, `status`, `asked` and
`plan`, and simply **leave `gid`, `url` and `page_url` out** (or empty).
Everything tolerates their absence and nothing renders a dead link:

- the row shows its number and title with the Asana icon **omitted** —
  no icon, not a broken one — and drops the page icon too if there is no
  `page_url`;
- the meta grid's **Ticket** cell reads `—`, with "Raised in chat — no
  Asana task" on hover;
- an empty `page` files the ticket under **Unfiled** in the page
  dropdown, so give it a page name if it has one.

Do **not** invent a fake Asana URL to make a row look uniform, and do not
skip Step 6/7 bookkeeping for the Marker tickets just because a chat one
has none to do.

### The layout — fixed navbar + master/detail

**Chris's spec, 2026-08-21 (revised the same evening).** The report used
to be a single column of `<details>` accordions under an `<h1>` and a
paragraph of preamble. It is now:

- **A fixed navbar that carries everything.** Left: the client name,
  `--client` rendered **verbatim** — it is the document's only `<h1>`.
  Never append "QA Triage" to it and never title-case it; trialport's
  brand is deliberately lowercase, so any transform is a bug. Beside it,
  the four status counts. Right, two dropdowns: page, then date.
- **The counts are controls, not chips** *(2026-08-21)*. They used to be
  pills with tinted backgrounds and a coloured left border. They are now
  built **exactly like the dropdown cells**: real `<button>`s, full
  navbar height, a 1px rule on each left edge, no background, no border,
  no radius — so the navbar has one vocabulary instead of two. **Only
  the number carries the status colour**; the word is ordinary navbar
  text, because four coloured words in a row read as decoration and stop
  meaning anything. Clicking one **scrolls the list to that section**,
  offset by the navbar height. The yellow bucket consolidates `partial`
  and `waiting`, so it targets the **first** of them in render order
  ("Partly shipped"). A bucket at zero has no section to scroll to and
  its button is `disabled` rather than a control that silently does
  nothing. The last section cannot reach the top of the viewport — the
  document ends — so clicking **Blocked** scrolls to the document end;
  that is correct, not a bug.
- **No status dot on the list rows** *(2026-08-21)*. The row's 3px
  coloured left bar already carries the status; two of them side by side
  was one too many.
- **No page-level `<h1>`, no preamble paragraph, and no counts strip.**
  The counts moved *into* the navbar and the separate strip below it is
  gone. The `<title>` carries client + date.
- **The two dropdown cells.** Each `<select>` sits in a full-height cell
  with a 1px rule on its left edge — so there is a divider before the
  first and between the two. The `<select>` itself has **no box**: it is
  borderless, radius-free and fills its cell, which makes the whole cell
  the click target and makes both dropdowns identical by construction.
  Do not restyle one of them individually; that is the bug this replaced.
- **Master/detail at 65 / 35, not an accordion.** Left 65% is the
  scannable ticket list. Right 35% is the detail panel: **flush to the
  bottom of the navbar, flush to the bottom of the viewport and flush to
  the right edge, with no border-radius and no gap** — its only edge is
  the 1px rule down its left side. The **whole panel is one scrolling
  column**; nothing inside it is pinned. The first ticket is selected on
  load so the panel is never empty. Rows are real `<button>`s with a
  roving tabindex: the list is one tab stop, and ↑/↓/Home/End move the
  selection. Under 900px the columns stack, panel first and stuck flush
  to the navbar at 58vh (full viewport height there would hide the list).
- **Do NOT put `overflow-x: hidden` on `html` or `body`.** It makes
  `body` the nearest scrolling ancestor for `position: sticky`, which
  pushes the panel down by the body padding and then lets it scroll away
  entirely. Found and fixed 2026-08-21. Horizontal overflow is prevented
  by `min-width: 0` on the grid tracks and the flex children instead.

### The four statuses — and the sub-statuses that roll into Waiting

The navbar shows **exactly four consolidated counts, always all four,
even at zero**, with fixed one-word labels and fixed colours:

| Label | Colour | Meaning |
|---|---|---|
| **Done** | green | work shipped |
| **Ready** | blue | ready to start, not begun |
| **Waiting** | yellow | waiting on someone |
| **Blocked** | red | cannot proceed |

A site may define extra ticket-level statuses, but **only Waiting takes
them** — anything that is neither shipped, nor startable, nor
hard-blocked is somebody waiting on somebody. `partial` ("Partly
shipped") is the shipped example: it rolls up into **Waiting** in the
navbar while still rendering as **its own section with its own label** in
the list. Only the navbar consolidates.

The current vocabulary and its mapping lives in `STATUS` at the top of
the script: `shipped`→Done, `ready`→Ready, `waiting`→Waiting,
`partial`→Waiting, `blocked`→Blocked. Adding a status means adding a row
there with `"waiting"` as its bucket; the script refuses to build on a
value it does not know.

*(This also fixed a real bug: the old counts strip added `partial` into
Blocked, so it read 8 where the sections underneath said 4 and 4.)*

### The page dropdown — and the question filter above it

**The first option is not a page.** It is
**"❓ Has a question for the client (N)"**, separated from the rest by a
rule, and it selects every ticket with a non-empty `q` **regardless of
page or status**. It is the highest-value view in the report — it is what
we owe the client — so it sits above "All pages". Rows carrying a
question also show a small `?` marker in the list.

Below it, the page options come from each ticket's `page` field through
`page_of()`, which shortens "Research sites & clinicians —
`/research-sites/` (ID 488)" to "Research sites & clinicians" and files
nav/footer/site-wide tickets under site chrome instead of whatever page
the client happened to be on. Each option carries its ticket count, real
pages and site-wide buckets are separate `<optgroup>`s (a round routinely
names 15–20 pages), and "All pages" is the default.

Filtering rewrites the section count badges to what is actually shown —
"78 Shipped" above four rows is a lie — but **row numbers never change**,
because a row's number is the ticket's own permanent `num` and not its
position in whatever happens to be visible.

### The detail panel — meta grid, then a fixed body order

**Title first.** Directly under it, a **four-column grid, full width,
16 / 28 / 28 / 28, with vertical rules between them**:

| | | | |
|---|---|---|---|
| **No.** | **Status** | **Page** | **Ticket** |
| its permanent `num`, unique across the whole report | the consolidated one-word label, in its status colour | the page name, truncated with an ellipsis, linked, external-link icon | linked to Asana, external-link icon — or a dimmed `—` when the ticket was raised in chat |

The number is first deliberately: it is the thing people say out loud on
a call, and since 2026-08-21 saying it is enough — it identifies one
ticket in the report, with no section name needed. The site's own sub-status
("Partly shipped") rides in the Status cell's tooltip rather than as a
second status line, so the body order below stays exactly as specified.

**The body is then exactly this, in this order:**

1. **Question for the client** — *only* when `q` is non-empty.
   Highlighted in amber, because it is the thing that needs an answer.
   Write it so the client could read it and reply to it directly.
2. **Request** — plain, simple, short: what the client asked for.
   Source `asked`.
3. **What was done** — a short, **non-technical** summary. Source `did`
   (or its legacy alias `done`). Labelled "What we will do" on a ticket
   that has not been done. **No `did` → a one-line placeholder**, never
   `plan`.
4. **The divider** — labelled *"internal detail below"*.
5. **Screenshot details** — may be long. Source `shot`.
6. **The technical plan** — may be long, as detailed as you like. Source
   `plan`. **Always below the line, always rendered.**

Every subheading carries ~50% more air above it than it used to
(2026-08-21) — the sections were running together. Body copy in the
panel is the **ink** token, not the dim one; `code` spans are dim text
on a faint border. The meta grid is **16 / 28 / 28 / 28**, not four
quarters — "No." is three digits at most and the room belongs to Page.

### The divider is a client/internal boundary

**Chris's rule, 2026-08-21. This is the most important rule on this
page, and the easiest one to break by accident.**

> *"This is probably going to be client-facing. They are NOT going to
> know about Media ID numbers, page numbers, CSS rules, CSS classes,
> etc… I'm fine with that level of detail being shown, however, it
> should all go below the divider line."*

The rule in the detail panel is not a visual break. It is the line
between **their half and ours**, and since 2026-08-21 it says so — a
quiet *"internal detail below"* label sits in the rule itself.

| | Renders | Contains |
|---|---|---|
| **Above the line** | Question, Request, What was done | **Client-readable only.** No file paths, no CSS properties or class names, no block names, no media IDs, no page IDs, no commit hashes. |
| **Below the line** | Screenshot details, The technical plan | Internal. As detailed as it likes. |

Two fields, and only two, decide what the client reads:

- **`did` is the client-facing field.** Write it for someone who has
  never seen the codebase — the words you would say on the phone.
- **`plan` is the internal field.** It renders *only* below the line.

**There is no fallback and you must not add one back.** Until
2026-08-21 a ticket with no `did` rendered `plan` under "What we will
do" — above the divider. That is how the client came to be reading
*"re-cut media 420 as a transparent PNG"*, `background-image`,
`gray-40`, `.sg-block-hero-image` and
`classic-city-core/assets/blocks.css`. The fallback is gone. A ticket
with no `did` now prints:

> *Plain-English summary still to be written — the technical detail is
> below the line.*

An honest gap beats a leak, and `plan` still renders in full below.
77 of trialport's 97 tickets hit this on the day the rule landed; that
is a backlog of `did` fields to write, not a bug.

**The failure mode is not the missing `did` — it is the `did` written
as a summary of the diff.** If it names a file, a selector, an
attachment number or an ID, it belongs in `plan`. Rewrite it.

**The build tells you when you have broken it.** Every field that
renders above the line (`asked`, `did`/`done`, `q`) is scanned for file
names, paths, block/theme names, CSS tokens, internal IDs and commit
hashes, and the run prints:

```
WARNING: 6 field(s) put internal detail ABOVE the divider, which is the
client's half of the panel:
  ticket 55 · done: internal ID — …Hero image placed (attachment 625)…
```

It is **advisory — it warns, it never refuses**, because the report is
still worth generating with a leak in it and a hard failure at 5pm on a
Friday helps nobody. Clear the warnings before the link goes to the
client.

One consequence worth knowing: **backticks are rendered differently
above and below the line.** Above it, a `` `backtick span` `` is a menu
label, a product name or a brand hex *the client typed himself*, so it
sets as an italic literal — a slab of monospace in his half of the
panel tells him he is looking at machinery. Below the line it is
`<code>`, as it should be. A ```` ``` fenced block ```` is the client's
verbatim replacement copy in both halves and renders as an indented
quotation; it was previously chewed into a stray `` `` ``, a monospace
slab and another `` `` ``.

### The filename scheme — a stable URL plus a dated archive

**Chris's call, 2026-08-21.** A link to "the report" must never go stale,
so:

- **The current build is `docs/qa/asana-qa-triage.html` — un-dated.**
  That is the path you hand over, every round, forever.
- **Previous builds are `asana-qa-triage-YYYY-MM-DD.html`** beside it.
  That is the archive.
- **`asana-qa-triage.seq`** also lives there: the ticket-number
  high-water mark (see *Ticket numbers* above). Commit it.

**Rotation is automatic — there is no convention for a human to
remember.** Every run stamps `<meta name="qa-build-date">` into the file.
The next run reads that stamp off the file it is about to replace and, if
the date differs, copies it to its dated name first. The copy is then
rewritten in place through two marked regions (`<!--dtsel-->` and
`<!--sup-->`): its date dropdown is regenerated so it points back at the
current report, and its superseded banner is filled in. Only then is the
new build written. Regenerating the *same* date replaces in place and
archives nothing. `--no-archive` opts out.

**`--date` must be `YYYY-MM-DD`** (it defaults to today) — the whole
scheme keys off it, so the script refuses anything else rather than
producing an archive it cannot name.

**The date dropdown** is a **directory scan done at generation time**. A
static file cannot know about builds that do not exist yet, so at write
time the script lists the *output file's own folder* for sibling dated
files, sorts them newest first, and emits each as an option whose value
is the filename; picking one simply navigates to it. Two shapes:

- the **current** build shows `"21 Aug 2026 · current"` selected, then
  every dated archive beneath it;
- an **archived** build shows **"Current report"** first — so an old file
  is never a dead end — then its own date selected, then the archives
  that existed when it was made.

The consequence is worth knowing and is fine: an older report only lists
the siblings that existed when it was generated. The current report is
regenerated every round and always carries the complete list.

In `--fragment` mode there are no siblings to navigate to (the fragment
is one page on a host that knows nothing about the folder), so it shows
this build only.

`position: fixed` inside an embedding iframe resolves against the iframe
viewport, which is what we want; `body` carries a `padding-top` of the
navbar height so the first rows are never underneath it. Verified in a
real browser at 375–1440px, not assumed: no horizontal overflow at any
width, zero external requests (icons are inline SVG — these files open
over `file://`), panel flush at 54px top and viewport-bottom, and the
question filter, keyboard nav and per-section numbering all live.

**Regenerate it after every execution pass.** The report's axis changes
once work starts: before, the useful sort is *confidence* (can we do this
without asking?) — grade A→`ready`, B→`waiting`, C→`blocked`. After, it
is *status* (shipped / ready / partly shipped / waiting on one answer /
blocked). A report still saying "ready to ship" for work that shipped an
hour ago is worse than no report, because it reads as current. Shipped
rows should say what you DID — **fill in their `did`, in the client's
words** (a shipped ticket with no `did` shows the placeholder, and the
build warns if the `did` you wrote leaks internals — see *The divider is
a client/internal boundary*) — and **clear the `q` of any question the
work actually answered**. Leave `q` in place when the
work shipped but still owes the client an answer; that is precisely what
the question filter is for.

## Step 4 — Prep the client email

- **Recipient = the ticket reporter** (the Marker.io "Reported by"
  person), not an internal address.
- **No ticket numbers** — the client doesn't know them.
- Group by **page**: each page name is a **link to the live page**,
  followed by 1–2 sentences on what changed (this proves we read the
  ticket and did the work).
- Two required sections: **Knocked out** (done) and **Still have
  questions** (needs their input). A short **Underway / no action
  needed** list is fine too.
- Warm, plain voice. Offer a quick call for the open items.

### The reference email — copy this shape every time

**Chris's call, 2026-09-16:** *"This is the perfect email voice and tone for
QA status reports for clients."* Sent to TimberBLDR after their first Marker
round. Reuse the shape verbatim; only the bullets change.

```
Rebecca!

Knocked out everything from your first round of comments. Here's the rundown
(hard-refresh if you still see the old version anywhere):

- Footer: the line under the logo now reads TIMBER INNOVATION.
- Hero: gave the video a good bit more height so it isn't cut short, and the
  nav bar is untouched. If you want it taller still, say the word.
- Hero copy: now reads "plans, procures, and erects."
- Who We Are: heading is now INNOVATORS OF THE TIMBER AGE with your new
  paragraph underneath, word for word.
- PDX: pulled it. Heads up, we had two shots from that airport project (the
  curved ceiling and the slat roof), so I removed both from the homepage and
  the Projects page to be safe.
- Risinger Build: that card is now DATA CENTER, and I renamed the matching
  tile on the Projects page so they line up.
- Team: Vittorio's title is now Vice President and his bio says "PhD in Mass
  Timber Construction." I also re-cropped both headshots so his and Simon's
  eyes sit on the same line, and gave Simon a touch more presence in the
  frame so he doesn't read shorter.

One quick question: is "Vice President" the full title for Vittorio, or
should it be something like "Vice President of Design & Project Delivery"?
Easy change either way.

Everything is live here: https://timberbldrdev.wpenginepowered.com/

Keep the comments coming whenever you're ready for round two!

I appreciate you ✌🏻
```

What makes it work, so the next one lands the same way:

- **First name + exclamation mark, on its own line.** Never "Hi Rebecca,".
- **One-sentence lead that says it's done** ("Knocked out everything…"),
  with the hard-refresh note tucked in parentheses. No "I hope this finds
  you well," no recap of what they asked.
- **One bullet per section of the site, bold label first**, then one or two
  plain sentences in the words they used on the ticket. Their capitalised
  labels (TIMBER INNOVATION, DATA CENTER) come back capitalised. No ticket
  numbers, no file names, no CSS, no media IDs.
- **Judgment calls are flagged inside the bullet** ("Heads up, we had two
  shots…") so they see the reasoning without being asked to decide.
- **Exactly one question, in its own paragraph, with the easy default
  stated** ("Easy change either way"). If there are two questions, the
  email is not done; fold one into a bullet as a call you made.
- **One live link, then an invitation to the next round**, then
  "I appreciate you ✌🏻". Nothing after the sign-off.
- **No em dashes.** Commas and parentheses carry the asides. About 200
  words; if it runs long, cut the bullets, not the question.
- In HTML mail, the labels are `<strong>` and the list is a real `<ul>`.
  Create it as a **draft** on the existing thread, replying to Chris's own
  last message, addressed to the reporter only; Chris sends it.

## Step 5 — Execute (after Chris says go)

Deploy mechanics are the **`CLIENT_ONBOARDING.md` Phase 12** playbook
plus the client's `sg-{slug}/CLAUDE.md`. Field-tested essentials:

- **Theme / CSS / block-template changes** are git-tracked → work on a
  branch, verify, merge to `main`, then `git push origin main && git
  push wpe main`. The `wpe` push auto-purges Varnish.
- **Page content + media live in the WPE DB** → edit via **wp-cli over
  SSH** (`{slug}@{slug}.ssh.wpengine.net`, install at
  `/home/wpe-user/sites/{slug}/`). WPE's WAF blocks REST writes.
- **WPE SSH latency is brutal (~40s/handshake).** Open ONE **SSH
  ControlMaster** socket and reuse it for every command:
  `ssh -o ControlMaster=yes -o ControlPath=/tmp/wpe.sock -o
  ControlPersist=600 -fN <host>` (keep ControlPath short — the socket
  path has a ~104-char limit).
- **Never run PHP inline over SSH** (nested-quote hell). Write a `.php`
  file locally, pipe it up, run `wp eval-file file.php`.
- **The shell here is zsh** — unquoted `$var` does NOT word-split like
  bash. Use arrays: `files=(a b c); for f in $files; ...`.
- **Editing page block markup:** `wp post get <id> --field=content` to
  dump raw, edit locally, pipe back, `wp post update <id> file`. Confirm
  every `"image":<id>` literal and block boundary against the raw dump.
  Prefer line-based or tightly-anchored edits — **beware greedy DOTALL
  regex** (it backtracks and over-deletes).
- **Images:** optimize before upload — longest side ~1500px (full-bleed
  banners ~2000px), medium JPG: `sips -s format jpeg -s formatOptions 70
  -Z 1500 in.jpg --out out.jpg`. Upload via pipe-over-ssh, then `wp media
  import <file> --porcelain` for the new attachment id. **Check for an
  existing attachment first** (`wp db query` on `guid`) to avoid dupes,
  then remap `"image":<id>` literals in the block content.
- **Generative image expand** (portrait → wide banner, when the ticket
  allows it): pad to the target aspect with `sips --padToHeightWidth H W
  --padColor FFFFFF`, then outpaint the blank panels with the
  nano-banana `edit_image` / `continue_editing` tool. Get client sign-off
  before it stays live.
- **Gravity Forms** config is in the DB and the `wp gf` CLI is usually
  NOT installed on WPE. **Back up the form first**
  (`GFAPI::get_form(<id>)` → JSON to disk), then modify via `wp eval-file`
  + `GFAPI::update_form($form)`. Notifications are keyed by a unique id;
  subject-based routing = one notification per option with
  `conditionalLogic` on the subject field id.
- **After any wp-cli content change, purge Varnish** — a trivial
  cache-bust commit pushed to `wpe` triggers the purge hook (or use the
  WPE portal button). Then **verify on the live origin** with
  `?_cb=<timestamp>` curls.
- **Production gate:** build + verify on a branch / locally, then
  **confirm with Chris before pushing to `wpe` or SSH-mutating
  production.** A blanket "do it" is the go-ahead.

## Step 6 — Comment the work back onto each ticket

**Every ticket you touch gets a comment before its status changes.** The
client email is the roll-up; the Asana comment is the per-ticket receipt,
and it's what the reporter sees when the status-change notification hits
their inbox. A ticket that flips to "Client QA" with no comment reads as
"someone clicked a button."

Post with `asana_create_task_story {task_id, text: "..."}` (plain text —
bare URLs auto-link). Use `html_text` instead only when you want bold or
a list; allowed tags are `<body> <strong> <em> <u> <s> <code> <ol> <ul>
<li> <a> <blockquote> <pre>` wrapped in a single `<body>` root — **no**
`<h1>`, `<h2>`, `<hr/>`, or `<img>` in a comment (400s).

**Write it for the client, not for us.** Same voice as the email: no file
paths, no commit hashes, no block/CSS names, no ticket numbers. Say what
changed, where to look, and what's still needed. Three shapes:

- **Done this session** —
  > Updated the hero headline to "…" and swapped the banner image for the
  > wider crop. Live here: {live page URL} — hard-refresh if you still see
  > the old version.
- **Already live before this session** (the >10-day ones you verified) —
  > Verified this one is already live — looks like it shipped in an
  > earlier round and the ticket just stayed open. Closing it out.
  > {live page URL}
- **Blocked / needs their input** — lead with the ask, one question only:
  > Ready to do this one — just need to confirm: should the footer show
  > one phone number or both? The example attachment on this ticket has
  > expired on Marker's end, so I can't pull it from there.

Rules:

- **Comments are outward-facing.** They post during Step 5 execution,
  **after Chris says go** — never while triaging. A blanket "do it"
  covers the comments along with the work.
- **One comment per ticket per session.** Before posting, check
  `asana_get_stories_for_task {task_id}` for a comment you already left —
  a re-run of this workflow must not double-post.
- **Link the live page, not staging**, and only if the change is actually
  live. If it's merged but not yet deployed, say so ("queued for the next
  push") rather than linking something that hasn't changed.
- **Don't comment on tickets you didn't touch.** Silence is fine for
  parked items you're not asking anything about.

## Step 7 — Asana bookkeeping (Chris's convention, at the end)

Find the project's **"Task Status"** custom field + its option gids
(they differ per project):
`asana_get_project {project_id, opt_fields:
custom_field_settings.custom_field.name,custom_field_settings.custom_field.gid,custom_field_settings.custom_field.enum_options.name,custom_field_settings.custom_field.enum_options.gid}`.

Comment first (Step 6), then set status — so the notification the
reporter gets already has the explanation attached to it.

- **Tickets already done before this session** (the >10-day ones you
  verified live) → comment the "already live" note, then **mark
  complete** (`asana_update_task {completed:true}`).
- **Tickets you completed this session** → comment what changed + the
  live link, then set **Task Status = "Client QA"** via
  `asana_update_task {custom_fields: '{"<field_gid>":"<Client QA
  option_gid>"}'}`. Leave `completed:false` — they await client review.
- **Blocked** (e.g., dead attachment) or **parked** items → leave open;
  put the question in the comment (that's the whole point of it), and
  optionally set status "Blocked" / "Waiting on Client".

---

## Field notes worth keeping

- Marker AI titles are noise; the annotated screenshot is truth.
- `files.marker.io` example links expire — flag dead ones and ask for a
  re-send rather than guessing.
- Confirm inferred emails/addresses/asset access; state assumptions in
  the client email.
- One page write is cleaner than many — batch all edits to a page into a
  single `wp post update`.
- The per-ticket comment is what makes the board readable a month later.
  Asana's automatic activity log records *that* the status changed, never
  *what shipped* — if it isn't in a comment, it isn't anywhere the client
  can see.
