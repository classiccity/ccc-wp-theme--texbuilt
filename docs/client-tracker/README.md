# Client Build/QA Tracker — the template

A **client-facing** status page for a multi-round build or QA cycle. One JSON
file in, one self-contained HTML file out, and a link you can send the client.

This folder is the **template**. It is generic on purpose: no client's data is
in it, and it is not wired to any site. A child theme takes a copy.

```
docs/client-tracker/
├── README.md                    ← you are here — how to USE it
├── DEVELOPING.md                ← how to CHANGE it: architecture, invariants, decisions
├── build-tracker.py             ← the generator
├── skins/
│   └── classic-city/            ← the default skin (used automatically — see its README)
├── tracker.example.json         ← schema + worked example (fictional client)
├── tracker.example.html         ← the rendered example (Classic City skin), committed so it is browsable
├── tracker.example.plain.html   ← the same example built with --no-skin
└── .gitignore                   ← *.internal.html — see below
```

> **Changing the generator rather than using it?** Read
> [`DEVELOPING.md`](./DEVELOPING.md) first. It covers how the generator is
> structured, where the two render modes diverge, how the icon sprite and the
> round-scoping JS work, the invariants that must not regress (the leak audit,
> zero external requests, Python 3.9, `docs-check` staying at 7), and the
> rationale behind the decisions that look arbitrary from the outside.

To see what it produces, build the example and open it:

```bash
python3 build-tracker.py tracker.example.json && open tracker.example.html
```

The **client** example page is committed, so the template is browsable without
running Python. The **internal** build never is: `*.internal.html` is
gitignored here and must be gitignored in every child too — the parent theme
deploys to every client site and is served publicly, so a file named
`*.internal.html` at a public URL contradicts the one promise this template
makes. See [`.gitignore`](./.gitignore) for the full reasoning.

---

## The rule: copy it, never edit it

**The parent holds the template. Children copy it and fill in their own data.
The template itself is never edited by a child.**

This is the core architectural rule of this folder and it is not negotiable,
for the same reason it applies to the rest of the parent theme: the parent is
subtree'd into every client repo. A change made here reaches *every* client on
their next `git subtree pull`. If `sg-northgate` "just tweaks" the generator to
add a Northgate-shaped field, that tweak either gets clobbered by the next
pull, or — worse — it lands upstream and every other client inherits a field
that means nothing to them.

So:

| You want to… | Do this |
|---|---|
| Track a client's build | **Copy** this folder into the child theme, edit the copy |
| Change the data for a client | Edit that child's JSON. Never this folder |
| Add a field every client needs | Edit the template **here**, in the parent, then subtree-pull into the children that want it |
| Add a field only one client needs | Put it in that child's copy of the generator, and accept that it is now a fork of the template |

The last row is a real, allowed outcome — a copy is a copy, and a client build
under deadline is not the time to re-architect the parent. Just be honest with
yourself about it: once a child's generator diverges, it no longer gets
template improvements for free. If you find two clients forking it the same
way, that is the signal to bring the change up here.

Same rule as the rest of the theme (`../../CLAUDE.md` §"When NOT to edit the
parent theme"): ask *should every Classic City client get this?* If no, it
belongs in the child.

---

## How a child adopts it

From the client repo root:

```bash
mkdir -p wp-content/themes/sg-{slug}/docs/qa
cp wp-content/themes/classic-city-core/docs/client-tracker/build-tracker.py \
   wp-content/themes/classic-city-core/docs/client-tracker/tracker.example.json \
   wp-content/themes/sg-{slug}/docs/qa/
cp -R wp-content/themes/classic-city-core/docs/client-tracker/skins \
   wp-content/themes/sg-{slug}/docs/qa/

cd wp-content/themes/sg-{slug}/docs/qa
mv tracker.example.json round-2.json      # name it for the round you are in
```

The `skins/` folder is what makes the default build come out branded — the
generator looks for `skins/classic-city/` next to itself. Skip copying it and
every build prints a warning and comes out unskinned.

Then:

1. Open `round-2.json`, delete the `_comment` key, and replace the example
   content with the client's. Keep the shape — the generator validates it.
2. **Fill in the `pages` registry first.** Everything groups by it, and the
   generator refuses to build if a record references a page you never
   declared. Five minutes here saves you from a flat, ungrouped changelog.
3. Build it: `python3 build-tracker.py round-2.json` — no flags needed; the
   Classic City skin is the default.
4. Commit **both** the JSON and the HTML. The HTML has to be in git because
   WP Engine serves it as a static file; the JSON has to be in git because it
   is the source of truth and the HTML is disposable.
5. Add the internal build to the child's `.gitignore` — see the warning below.

### Naming

One JSON per tracker, named for what the client will see in the URL. A single
`round-2.json` that *contains* rounds 1, 2 and 3 is usually right — rounds are
a field in the data, not separate files, which is what lets the client follow
one link for the life of the project. Use separate files only when you want
separate URLs (e.g. a build tracker and a distinct post-launch support
tracker).

### Keep `*.internal.html` out of the deployed theme

The internal build carries ownership, effort sizing and internal notes. The
child theme directory is **publicly served** on WP Engine. Those two facts
together mean an internal build committed to the child theme is an internal
build on the public internet.

Add this to the child's `.gitignore`:

```gitignore
# Internal tracker builds — never deploy: the theme dir is publicly served
docs/qa/*.internal.html
```

The generator writes internal builds to `*.internal.html` specifically so this
one gitignore line catches all of them, and so an internal build can never
overwrite the file the client has the link to.

---

## How it reaches the client

The child theme deploys to WP Engine, and WP Engine serves static files out of
the theme directory. So once the child theme is pushed, the file committed at
`sg-{slug}/docs/qa/round-2.html` is live at:

```
https://{site}/wp-content/themes/sg-{slug}/docs/qa/round-2.html
```

Send that URL. Re-running the generator and re-deploying updates the page in
place, so the link the client bookmarked always shows current status — which
is the entire reason this is a hosted page rather than an emailed attachment.

**Three things to know before you send it:**

1. **The URL is public.** It is unguessable-ish, not protected. There is no
   login. Anyone with the link can read it, and so can anyone who is handed
   the link. Put nothing in the JSON you would not put in an email that might
   get forwarded — no credentials, no third-party contact details, no
   commercial terms, no candid remarks about anyone.
2. **The page sets `noindex,nofollow`,** so it will not show up in search.
   That is a courtesy, not a security control.
3. **Deploys are not instant.** WP Engine's cache and the usual deploy latency
   apply (see `../WPE_PLATFORM.md`). If you update the page five minutes before
   a client call, hard-refresh and confirm before you say it is live.

If a client relationship needs the page genuinely private, the honest answers
are HTTP auth on the install, or emailing the HTML file as an attachment —
it is self-contained, so it works fine offline and every icon still renders.
Do not solve it by putting the tracker somewhere clever and hoping.

---

## Running it

```bash
python3 build-tracker.py round-2.json               # client build, Classic City skin → round-2.html
python3 build-tracker.py round-2.json --internal    # internal build → round-2.internal.html
python3 build-tracker.py round-2.json --no-skin     # plain, unskinned build → round-2.plain.html
python3 build-tracker.py round-2.json -o /tmp/x.html
```

Python 3.9+, standard library only. No dependencies, no build step, no network.

**The Classic City skin is the default.** The generator resolves
`skins/classic-city/` next to the script (that is why adopting the template
copies the `skins/` folder) — the skinned page IS the tracker, so it takes the
plain `round-2.html` name and the client's URL never changes. `--skin DIR`
substitutes any other skin directory; `--no-skin` builds the unskinned page,
written to `round-2.plain.html` so it cannot overwrite the file the client
has the link to. If the default skin folder is missing the build still
succeeds — one stderr warning, unskinned output.

The generator **validates before it renders** and refuses to write a broken
page. Errors name the exact record:

```
Data problem — round 2 section s2-pages task P-01: status='in-progress'
is not a known value. Valid: blocked, deferred, done, in_progress,
needs_client, queued, review

Data problem — changelog C-18: page='teem' is not declared in the root
`pages` registry. Declared: boarding, emergency, home, new-clients,
surgery, team, wellness
```

### The two render modes

Both modes read the **same JSON file**. There is deliberately no second data
file to keep in sync, because a second data file drifts, and a drifted client
tracker is worse than no client tracker.

| | Client build (default) | Internal build (`--internal`) |
|---|---|---|
| Task/section/blocker `summary` | ✅ | ✅ |
| Status, rounds, pages, blockers, changelog | ✅ | ✅ |
| Blocker `awaiting` ("waiting on you") | ✅ | ✅ |
| `internal_note` on any record | ❌ **never emitted** | ✅ |
| Task `owner` (agency/client) | ❌ **never emitted** | ✅ |
| Task `effort` (XS–XL) | ❌ **never emitted** | ✅ |
| Records flagged `internal_only` | ❌ **never emitted** | ✅ |
| "Internal build" warning banner | — | ✅ |
| Output filename | `round-2.html` | `round-2.internal.html` |

"Never emitted" means exactly that: the client build does not hide these with
CSS or a `display:none`, it never writes them into the file. Hidden content in
a file the client can download is not hidden. The client build does not even
ship the *stylesheet rules* for the internal furniture, so the audit is a
one-liner:

```bash
grep -c 't-owner\|t-effort\|internal-note\|mode-flag' round-2.html   # must print 0
```

**Ownership is the one asymmetry worth understanding.** Task-level `owner` is
workload allocation — who on which side is doing the work — and clients do not
need or want to see it. But a *blocker's* `awaiting` field is the opposite:
its whole meaning is "we cannot move until you answer", which is useless if
the client cannot see that it is theirs. So `owner` is internal-only and
`awaiting` is shown to everyone, by design.

### Writing for two audiences

Every record can carry both:

```json
{
  "summary": "The page layout is finished and waiting on the bios and photos.",
  "internal_note": "Chased 8 Jul, 17 Jul, 28 Jul. Sarah says Dr. Whitfield is the hold-up."
}
```

Three habits make this work:

- **`summary` is what you would say on a call.** No file paths, no block
  slugs, no ticket refs, no agent names, no shorthand. The prototype this was
  generalised from is full of `sg-trialport/style.css` and "Chris's ruling" —
  correct internally, unsendable externally.
- **`summary` is optional, and omitting it is safe.** If a record has no
  `summary`, the client build renders no prose for it — it does **not** fall
  back to the internal note. That fallback would be the exact leak this whole
  design exists to prevent, so it does not exist.
- **If a record has no client-safe form at all, flag the whole thing
  `internal_only`.** Do not write a euphemism. A vague client-facing line
  invites the question you were trying to avoid.

---

## The colour language

Colours are defined once as CSS custom properties and referenced by semantic
name. **Nothing in the generated markup carries a literal colour** — that was
the prototype's real failing, where amber meant "medium severity" in one place
and "rebuild" in another, and a reader learned nothing from it.

One rule governs the palette: **a colour means a state, never a topic.**

**There are four colours and a grey.** Seven statuses still exist — they are
still the right seven things to *say* — but only four of them are worth
spending a colour on. "In progress", "queued" and "deferred" all mean the same
thing to a client (*it is on our list and needs nothing from you*), so they
share one grey, and the page stops asking the reader to tell three near-blues
apart.

| Status key | Shown as | Token | Colour | Means |
|---|---|---|---|---|
| `done` | Done | `--t-done` | green | Finished and checked. Nothing further needed. |
| `review` | Review | `--t-review` | violet | Built and live to look at; we are waiting on a yes. |
| `needs_client` | Needs you | `--t-client` | **amber** | We cannot finish this without something from you. |
| `blocked` | Blocked | `--t-blocked` | red | Held by a dependency; the item says which. |
| `in_progress` | In progress | `--t-queued` | grey | Being worked on right now. |
| `queued` | Queued | `--t-queued` | grey | Agreed and scheduled, not started. |
| `deferred` | Deferred | `--t-deferred` | grey | Deliberately out of this round. Parked, not forgotten. |

The old blue `--t-progress` token is **gone**, not merely unused — including
from `AWAITING.agency` ("Waiting on us") and the banner's *In progress* pod,
which used it. If you find yourself wanting a fifth colour, the question to
answer first is which of these five it is *not*.

**Amber always and only means "your move."** It is used for nothing else,
anywhere on the page — not for warnings, not for medium severity, not for
decoration. That is what makes the promise at the top of the page true: *if
you see no amber, nothing is waiting on you.* Every future edit to this
template has to keep that promise. It is the single most valuable thing the
page does.

### Where colour is allowed to land

Colour is confined to **four surfaces**: the banner counters, the status chips
in the work table, the round tabs, and the one-glyph flag on a section header.
**Cards are near-neutral, and nothing carries a second copy of a state that is
already stated in words.**

That is a deliberate revision. A card sitting under a heading that already
says *Waiting on you*, carrying a chip that already says *Needs you*, does not
also need to be amber — that is one fact stated three times, and at that
density the page reads as a highlighter accident rather than a status report.
The section is the state; the chip is the state; the card is just the card.

Three things were removed for exactly this reason, and should not come back:

- **The "Urgent" chip on blockers.** Everything under *Waiting on you* or
  *Waiting on us* is urgent — that is what those headings mean. `severity` is
  still accepted and validated; it just does not draw anything.
- **The status dots on section headers.** A row of coloured dots was a density
  read nobody asked for, sitting beside a `1/6` that said it better. The
  header now carries at most one flag glyph.
- **Brand-coloured link text.** Links are bold body text with an underline.
  The accent is a client's own brand colour, chosen to look right on *their
  site*, and small text set in it on a white card was the least readable
  string on the page.

The one exception inside a card is the **`ask` block**, which gets an amber
left rule (not a filled panel). That is the literal "your move" sentence, and
losing it in grey would defeat the point.

Each status resolves a token triple — `--t-{name}` (ink), `--t-{name}-bg`
(tint), `--t-{name}-br` (border) — applied through one `.t-{name}` class. To
restyle a status you change three custom properties in one place, and every
chip, counter, flag and swatch on the page follows.

Two smaller vocabularies reuse the same tokens rather than inventing colours:
changelog entries (`shipped` / `partial` / `reverted` / `deferred`) and round
states (`open` / `closed` / `planned`).

The page ships a **light palette by default** — clients print these to PDF, and
a dark page prints as a black rectangle. A dark palette is provided under
`prefers-color-scheme: dark` for on-screen reading, and `@media print` forces
light, opens every accordion and reveals every round panel, because a printed
page cannot be clicked.

### Brand hook

The only intended per-client visual customisation is two accent colours, set
**from the JSON** so a child never has to edit the generator's CSS:

```json
"brand": { "accent": "#2f6f5e", "accent_2": "#c07a2c" }
```

They colour the progress meters, links, landmark icons and focus rings.
**Status colours are deliberately not brandable** — the colour language has to
mean the same thing on every client's tracker, or it stops being a language.

---

## Icons

The page uses **Font Awesome Pro 7 artwork, inlined as SVG path data** — a
`<symbol>` sprite emitted once into `<body>`, referenced by `<use href="#i-…">`.
Only the glyphs a given page actually uses are emitted — seventeen are defined,
sixteen reach the example's sprite, about 8 kB. (`triangle-exclamation` is the
odd one out: it drew the retired *Urgent* chip. It is kept in the dict rather
than deleted, because the next thing that needs a warning glyph will want it.)

### Why not the theme's FontAwesome kit

The parent ships a kit at `assets/fontawesome/`, but it is **webfont-based**: a
stylesheet per style plus ~6 MB of woff2 across seventeen files. Consuming it
from a page whose whole contract is "one self-contained file" leaves two bad
options:

| Option | Cost |
|---|---|
| `<link>` the kit CSS by relative path | Works while WP Engine serves the file out of the theme. Produces a page of empty boxes the moment anyone mails it, saves it, or opens it from a USB stick — and §"How it reaches the client" above offers *"email the self-contained file"* as the privacy-preserving alternative to a public URL. That failure is not hypothetical. |
| base64 a whole woff2 into the page | Self-contained, but ~260 kB of font for a dozen glyphs, on a page that is otherwise ~50 kB. |
| A CDN | Not an option. The page makes **zero** external requests, full stop. |

So the glyphs are lifted verbatim from FontAwesome's own SVG distribution into
the `ICONS` dict at the top of `build-tracker.py`. Same artwork, same licence
(Chris's FA Pro licence covers it), zero requests, and it survives being
emailed.

**The trade-off accepted:** adding a new icon is an edit to a Python dict, not
a class name in the markup. Copy `viewBox` and the single `d` attribute out of
`fontawesome/svgs/{style}/{name}.svg` and add a row. That friction is the
price of self-containment, and for this template it is the right price.

### Every icon is decorative

**Icons are never the sole carrier of meaning.** Every one of them:

- sits next to a real text label — *"Waiting on you"*, *"History"*, *"Site-wide"*;
- is `aria-hidden="true" focusable="false"`, so a screen reader never reads
  "hand, Waiting on you";
- can fail to render without the page becoming ambiguous.

**Two icons on a section header look like exceptions and are not.** The flag
glyph and the page link have no visible text beside them, so each carries an
`.sr-only` label (*"Needs you"*, *"Open Our Team"*) and a `title` for a hover
tooltip. The flag is never the only place a state is stated — the row it
points at carries a full status chip — and the page link's `.sr-only` label is
un-hidden by the print stylesheet, because a printed page needs the words.

If you add an icon, hold it to the same rule. An icon-only control with no
accessible name is a bug.

---

## Layout

The page is **one round at a time**.

1. **Sticky banner** — client, title, last-updated, the selected round's label,
   and **three** counters for that round: *Done*, *Review* and *Blocked*.
   Always visible while scrolling. It used to carry five. *Done* earned its
   place back — it is the number clients most want at a glance. *In progress*
   is said better by every section's own `N/M`; *Needs you* is said by the
   amber badge on the round tab two inches away. What is left is what they
   finished, what is waiting for their eyes, and what is stuck.
2. **The round nav, inside the banner** — a sub-sticky tab strip. It is part of
   the same sticky unit, so the round you are reading and the counts for that
   round can never scroll apart from each other.
3. **Everything below is one round panel.** Selecting a round swaps the panel
   *and* the counters, the meter and the round label in the banner. There is no
   longer a separate "the work, by round" accordion — a round tab is already a
   round view, and having two controls that both claim to answer "what round am
   I in" is how a status page starts lying.

Inside a round panel, first **the round's letter** — a short pod with one
paragraph on where things stand, signed and dated. Then, in order:

| `<h2>` landmark | What it is |
|---|---|
| **Waiting on you** | Open blockers where `awaiting: client`, **plus every task whose `status` is `needs_client`**. Always rendered, even when empty — *"nothing is waiting on you"* is the most valuable sentence on the page, and a landmark that disappears is a landmark nobody learns. |
| **Waiting on us** | Open blockers where `awaiting: agency`. Also always rendered. |
| **The work in this round** | One accordion per section; inside each, its tasks as a **table**. |
| **History** | The changelog **for this round**, as one closed accordion per page, each holding a work-style **table** (state glyph, ref, what, why, date, link). Allowed to be long. Omitted when the round has none. |

Then, once, below the panels: **What the colours mean** (the key) and the
footer.

There is **no generic intro paragraph** and **no expand/collapse-all button**.
The intro said the same thing on every client's tracker and on every round of
it, explaining controls the reader had already used by the time they reached
it; the round letter says something true about *this* round in that space
instead. The expand-all button was justified by printing and by Ctrl+F, and is
needed for neither — `@media print` forces every accordion open, and browsers
expand a closed `<details>` automatically when find-in-page matches inside it.

### "Waiting on you" is one number, computed once

The round tab badge, the *Waiting on you* heading count, and the cards under
that heading all come from a single function (`client_asks`). They cannot
disagree, which they previously did — a badge saying 4 above a heading saying
2 on the same round.

Two things genuinely wait on a client, and both are counted: **an open blocker
they own**, and **a task whose status is `needs_client`**. A needs-you task
renders as a card in the same shape as a blocker, with its ref as a link down
to its row in the work table — a pointer, not a second copy.

Counting blockers only would have squared the numbers and quietly broken the
page's core promise: a round whose only client-blocking item was a needs-you
*task* would have shown no badge and printed *"Nothing is waiting on you in
this round"* directly above an amber chip saying otherwise.

The `<h2>`s are real headings, large and ruled off, with generous space above.
They are the page's signposts. The heading hierarchy runs `h1` (client) → `h2`
(landmark) → `h3` (card title / section title), with no levels skipped. Task
titles are `<th scope="row">` cells, not headings — they are table rows now.

### A section is (usually) a page, and its tasks are a table

A section header carries, pinned to its right-hand end and always in this
order:

1. **A flag glyph**, if anything inside needs the client (amber `!`) or is
   ready for their review (violet eye). Icon only — the row it points at still
   says it in words.
2. **A link to the page**, if the section declares a `page` the registry knows
   a URL for. This is what makes "these groups are pages" real: the header
   opens the page, the rows describe what changed on it.
3. **The count** — `1/6` done.

Inside, one row per task. Two columns are conditional, so a column that would
be empty on every row never appears:

| Column | Source | Shown when |
|---|---|---|
| Status | `status`, as a **bare glyph in the first column with no visible heading** | always |
| Ref | `id` | always |
| Item | `title` | always |
| Detail | `summary` (+ `internal_note` in the internal build) | always |
| Held on | `blocked_by` → who is waiting, and on what | any task in the section is blocked |
| Added | `created`, as **`JUL 9`** | always |
| Page | the task's own `links`, else the registry URL for a `page` it does **not** share with its section — labelled just **"Link"** | any row points somewhere the header does not |

Three of those are compressions worth understanding, because each looks like
information loss and is not:

- **Status is a glyph, first, unlabelled.** It is the one column identical in
  shape on every row, so the eye reads a stripe down the left edge instead of
  re-reading a word per row. Colour is never doing the work alone: each of the
  seven statuses has a distinct glyph, a `title` for hover, and an `.sr-only`
  word. The column *heading* is `.sr-only` rather than absent, so the table
  still announces the right number of columns.
- **`JUL 9`, not `2026-07-09`.** Ten monospaced characters of column width
  spent mostly on a year the reader knows. The cell is a
  `<time datetime="2026-07-09" title="2026-07-09">`, so the full date is one
  hover away and still machine-readable.
- **"Link", not the page's name.** *"Emergency & After Hours"* was the widest
  thing in a narrow column and wrapped to three lines. The real label is the
  link's accessible name and its hover title.

All three swap back on paper — the print stylesheet un-hides the status word
and the link's real label, because paper has no hover.

The Page column disappearing when everything is on one page is not a special
case — it falls out of the data. A task that simply inherits its section's page
produces an empty cell, and a column of empty cells is dropped.

The table scrolls inside its own `overflow-x` wrapper on narrow screens; the
page itself never scrolls sideways.

### There is no "Resolved" section

There used to be, listing blockers that had been cleared. Once the work became
a table with its own Status column it was the third place on one page telling
a client that something had finished — after the Done chips and after History.
Setting `resolved_on` now does exactly one thing: it takes the blocker off the
board. What actually changed belongs in the changelog, which is where a client
looks to ask *"what happened to the thing I sent you?"*

Practical consequence when you write data: if a resolved blocker's `resolution`
is worth the client reading, **write it as a changelog entry**. The field is
still accepted; it is no longer rendered anywhere, in either build.

Accordions are native `<details>`/`<summary>`: keyboard-operable and
screen-reader-correct for free, and Ctrl+F and print-to-PDF can force them
open. Only the round tabs are hand-rolled, because there is no native tab
element, so they carry full ARIA (`role`, `aria-selected`, `aria-controls`,
roving `tabindex`) and Arrow/Home/End key handling.
`prefers-reduced-motion` is respected.

Every task, blocker and round has a stable DOM id, so you can link a client
straight to one item — `…/round-2.html#p-04` **switches to the round that item
lives in**, opens the accordion around it, and scrolls to it.

---

## Grouping by page

> *"In our summaries of things that we have completed, I like to group them by
> the page that they are all on. Obviously within a round QA there might be
> some global fixes, but a lot of them will be page by page."*

That is what the `pages` registry is for. Declare each page once at the root
with a label and a URL, then tag records with `"page": "{id}"`.

```json
"pages": [
  { "id": "home", "label": "Homepage", "url": "https://example.com/preview/" },
  { "id": "team", "label": "Our Team", "url": "https://example.com/preview/team" }
]
```

- **History groups by page** — one fold per page, plus site-wide. A section
  that declares a `page` gets a link to it in its header; a task that points
  somewhere its section does not gets a link in the table's Page column;
  blockers show the page as a small tag on the card.
- **Records with no `page` fall into the site-wide group,** labelled
  *Site-wide* by default (override with the root `sitewide_label`).
- **Site-wide comes first.** Three reasons: a site-wide change is context for
  everything under it (a header fix explains something the client would
  otherwise re-report on four separate pages); its position is fixed, so the
  group does not shuffle as the project grows pages; and it is the group most
  likely to be non-empty, so the section never opens on what looks like an
  empty list. Named pages follow in **registry order** — which means *you*
  control the order by ordering the `pages` array, so the client's most
  important page can sit at the top.
- **A round with no page associations at all renders flat.** No lonely
  "Site-wide" wrapper around everything: the group furniture only appears once
  there is more than one group. Round 3 in the example demonstrates this.
- **A section can carry a `page`, and its tasks inherit it** unless they set
  their own. Sections in a QA round usually *are* pages, so this saves
  repeating the field on every row — and it is what puts the page link in the
  section header and collapses the table's Page column.

**Undeclared page ids are a build error**, not a silently-invented group of
one. The message lists what you did declare.

---

## JSON schema

Full worked example: [`tracker.example.json`](./tracker.example.json). Fields
marked **internal** are never written into the client build.

### Root

| Field | Req | Type | Notes |
|---|---|---|---|
| `schema_version` | | int | Currently `2` (adds `pages` / `page`). Informational; nothing branches on it. |
| `project` | ✅ | object | See below. |
| `current_round` | | int | Which round is "now" — the selected tab on load, the counters in the banner, and which sections open. Defaults to the highest round number. |
| `pages` | | array | The page registry. See below. |
| `sitewide_label` | | string | What to call the "no page" group. Default `"Site-wide"`. Use `"Global"` if that is the client's word for it. |
| `rounds` | ✅ | array | At least one. See below. |
| `changelog` | | array | See below. |
| `_comment` | | string | Ignored. Delete it from your copy. |

### `project`

| Field | Req | Type | Notes |
|---|---|---|---|
| `client` | ✅ | string | Client name, as they write it. Shown in the banner and the `<title>`. |
| `title` | ✅ | string | e.g. `"Website Build Tracker"`. |
| `slug` | | string | Site slug. Documentation only; nothing derives from it. |
| `updated` | | date | `YYYY-MM-DD`. Defaults to today's date at build time. Set it by hand if you want it to mean "data current as of", not "script last run". |
| `author` | | string | Who signs the round letters, e.g. `"Chris LaFay"`. A round can override with `note_by`. |
| `contact` | | string | Who to ask. Rendered in the footer. |
| `intro` | | string | **Retired — accepted, never rendered.** It was one generic paragraph, identical on every client's tracker and every round of it. The per-round letter replaced it. Delete it from your data; leaving it in breaks nothing. |
| `brand` | | object | `{ "accent": "#hex", "accent_2": "#hex" }`. |

### `pages[]`

The registry that page grouping resolves against. Order here is display order.

| Field | Req | Type | Notes |
|---|---|---|---|
| `id` | ✅ | string | Unique. What records put in their `page` field. Keep it short and typo-resistant (`team`, not `our-team-page-v2`). |
| `label` | ✅ | string | What the client sees: *"Our Team"*, *"Emergency & After Hours"*. Their words, not your slug. |
| `url` | | url | The live/preview page. Puts the **link glyph in a section header**, fills the table's **Page** column, and makes every page tag a link. `http(s)://` or `/` only. Worth setting — a row the client can click through to is the difference between "you say it is fixed" and "I can see it is fixed". |

### `rounds[]`

| Field | Req | Type | Notes |
|---|---|---|---|
| `number` | ✅ | int | Unique. Sorts the tabs (ascending, left to right) and links records to rounds. |
| `label` | ✅ | string | What this round is about, in a phrase. Shown in the banner beside the round number. |
| `state` | ✅ | enum | `open` \| `closed` \| `planned`. **Validated but not rendered** — it used to draw a chip on the round lede, which repeated what the selected tab already said. |
| `opened` / `closed` | | date | `YYYY-MM-DD`. No longer printed as a stamp; `closed` (then `opened`) is the fallback date on the letter's signature. |
| `summary` | | string | **The round's letter.** One client-safe paragraph on where things stand — what got done, what is waiting, what is next. Write it like a note at the top of a report, not like a scope line; it is the first thing anyone reads and the only place on the page you get to say something in your own voice. Omit it and the letter pod is not rendered at all. |
| `note_by` | | string | Who signs this round's letter. Defaults to `project.author`. |
| `note_on` | | date | The letter's date. Defaults to the round's `closed`, then `opened`, then `project.updated`. |
| `internal_note` | | string | **internal** — appended inside the letter pod. |
| `internal_only` | | bool | **internal** — omits the whole round, and its tab, from the client build. |
| `blockers` | | array | What is waiting on whom in this round. |
| `sections` | | array | The work, grouped. |

A `planned` round is a useful thing to include: it shows the client where the
project is heading without implying anything is underway.

### `rounds[].blockers[]`

Anything that needs a decision, an asset or an answer before work can finish.
While open it is a **card** under *Waiting on you* / *Waiting on us*; once
`resolved_on` is set it stops rendering.

| Field | Req | Type | Notes |
|---|---|---|---|
| `id` | ✅ | string | Unique across the whole file. Referenced by `blocked_by`, and the deep-link anchor. |
| `title` | ✅ | string | Short, concrete. Rendered as the card's `<h3>`. |
| `awaiting` | ✅ | enum | `client` \| `agency`. **Shown in both builds** — see above. Decides which `<h2>` the card lands under, and what the *Held on* column says for any task blocked by it. |
| `impact` | ✅ | string | Client-safe: what this holds up, and why they should care. |
| `page` | | string | A `pages[].id`. Shows a page tag on the card. Omit for site-wide. |
| `ask` | | string | Client-safe: *exactly* what you need. Rendered with the amber rule. Be specific and make it small — "a phone photo against a plain wall is fine" gets you photos; "headshots" does not. |
| `severity` | | enum | `high` \| `normal` (default). **Validated but not rendered.** It used to add an *Urgent* chip; everything under *Waiting on you* is urgent by definition, so the chip was one fact said twice. |
| `raised_on` | | date | `YYYY-MM-DD`. The date in the card footer — calendar glyph and the date, **no word in front of it**. (The word is still in the `aria-label`, so a screen reader hears "Raised 2026-07-08".) Set it on every blocker; a card with no date has a lopsided footer. |
| `resolved_on` | | date | Presence of this field takes the blocker off the board — it stops rendering, in **both** builds. |
| `resolution` | | string | **Accepted, no longer rendered** (see *There is no "Resolved" section"* above). If the client should read what happened, write it as a `changelog` entry. |
| `internal_note` | | string | **internal** — chase history, vendor detail, fallback plan. Note this disappears along with the card once `resolved_on` is set; the JSON keeps it, the page does not show it. |
| `internal_only` | | bool | **internal** |
| `links` | | array | `[{ "label": …, "url": … }]`. `http(s)://` or `/` only. Renders on the **right of the card footer**. |

### `rounds[].sections[]`

Accordion groups within a round's *The work in this round*. Group by the
client's mental model (page, feature, phase), not by yours.

| Field | Req | Type | Notes |
|---|---|---|---|
| `id` | ✅ | string | Unique-ish. Used for the DOM id. |
| `title` | ✅ | string | Rendered as an `<h3>` inside the accordion summary. If the section *is* a page, use the page's name. |
| `page` | | string | A `pages[].id`. Puts a **link to that page in the section header**, and **its tasks inherit it** unless they set their own — which also collapses the table's Page column. |
| `summary` | | string | Client-safe. |
| `open` | | bool | Force the accordion open/closed. Default: open if it is in the current round and has unfinished work. |
| `internal_note` | | string | **internal** |
| `internal_only` | | bool | **internal** — omits the whole section. |
| `tasks` | | array | |

### `rounds[].sections[].tasks[]`

One task is one table row. Which column each field lands in is listed under
*A section is (usually) a page, and its tasks are a table* above.

| Field | Req | Type | Notes |
|---|---|---|---|
| `id` | ✅ | string | Short ref like `P-04`. **Shown to the client** in the *Ref* column — it is how they refer to an item in an email, and it is the deep-link anchor. |
| `title` | ✅ | string | Client-safe. The *Item* cell. Keep it short; this column is narrow by design so *Detail* can be wide. |
| `status` | ✅ | enum | One of the seven statuses above. The first-column glyph, and the only thing carrying colour in the row. **`needs_client` also puts the task under *Waiting on you* as a card** and adds it to the round tab badge — that is the field's whole job, so use it only when you actually need something from the client. |
| `page` | | string | A `pages[].id`. Inherited from the section when omitted. Only produces a *Page* link when it **differs** from the section's page — otherwise the header already points there. |
| `summary` | | string | Client-safe detail — the *Detail* cell, and the widest column. Omitted → empty cell. Never falls back to `internal_note`. |
| `created` | | date | `YYYY-MM-DD`. The *Added* cell, shown as `JUL 9`. Omitted → an em-dash. |
| `blocked_by` | | string | A blocker `id`. Fills the *Held on* cell with who is waiting and on what. Validated: an unknown id is a build error. One task with this set makes the column appear for the whole section. |
| `owner` | | enum | `agency` \| `client`. **internal** — a pill after the title. |
| `effort` | | enum | `xs` \| `s` \| `m` \| `l` \| `xl`. **internal** — a pill after the title. |
| `internal_note` | | string | **internal** — rendered under the summary in the *Detail* cell. |
| `internal_only` | | bool | **internal** |
| `links` | | array | As above. Fills the *Page* cell, winning over the registry URL. Shown as "Link"; the `label` you write becomes the accessible name and the hover title, so still write a real one. A "Preview the page" link on a `review` item is the highest-value link on the page. |

### `changelog[]`

Long is fine — that is the point of a changelog. It sits at the bottom of the
round, as one **closed** accordion per page, so it is never the first thing a
client sees but reaching one page's history is a single click. (It used to be
a closed wrapper *containing* those folds; two lids to open one list was one
lid too many.) Inside each fold the entries render as a **table in the same
layout as the work section** — state glyph, ref, what, why, date and an
optional Link column — because they are the same kind of record and a second
layout for them was just height.

Now that resolved blockers no longer render, **this is the only place a client
can read what happened to something they sent you.** Write the entry.

| Field | Req | Type | Notes |
|---|---|---|---|
| `date` | ✅ | date | `YYYY-MM-DD`. Sorted newest-first within a page group. |
| `what` | ✅ | string | Client-safe: what changed. |
| `state` | ✅ | enum | `shipped` \| `partial` \| `reverted` \| `deferred`. |
| `why` | | string | Client-safe: **why**. Write this one. It is the field that makes the changelog worth reading and turns "you changed my slider" into "you removed my slider because it hurt the page on phones". |
| `round` | | int | **Which round's History this appears in.** Must match a declared round. Set it — History is per-round now. Entries with no `round` are genuinely project-wide (kickoff, hosting, contract) and appear in *every* round's History under a collapsed *"Not tied to a specific round"* fold. |
| `page` | | string | A `pages[].id`. **This is the grouping key for History.** Omit for site-wide. |
| `ref` | | string | e.g. `C-17`. |
| `area` | | string | Legacy free-text label. **Validated but no longer rendered** (superseded by `page`, which groups) — kept in the schema so old data still builds. |
| `links` | | array | As above; renders as the table's *Link* column — which only appears when some entry in the fold has one. |
| `internal_note` | | string | **internal** |
| `internal_only` | | bool | **internal** |

### Upgrading data written for schema v1

Nothing was removed and nothing changed shape — a v1 file still builds. To get
the new behaviour:

1. Add a root `pages` array.
2. Add `"page": "{id}"` to changelog entries, blockers, sections and tasks.
3. Add `"round": N` to any changelog entry that belongs to a round (v1 files
   often omitted it; those entries now land in the *"Not tied to a specific
   round"* fold instead of a round's own History).
4. Optionally add `"created"` to tasks and `raised_on` to any blocker missing
   one, so every card footer has a date.

Skip all four and you get a working page with one flat, ungrouped History per
round — which is exactly what v1 produced.

Also worth doing, though nothing breaks without it:

5. Add `project.author`, and rewrite each round's `summary` as a **letter** —
   a paragraph on where things stand, not a scope line. It is now the first
   thing on the page, and it is where `project.intro` used to be.
6. Delete `project.intro`. It is ignored.

---

## House conventions this follows

- **JSON is the source of truth; the HTML is disposable.** Never hand-edit the
  output — the next run reverts you silently. Same contract as
  `build-asset-tracker.py` and the QA tracker it generalises.
- **Self-contained output.** All CSS, JS and iconography inline, zero external
  requests, so it works served statically, saved locally, or mailed as an
  attachment.
- **The generator explains *why* in its comments,** not just what.
- Content rules for anything client-facing still apply — see
  [`../CONTENT_BUILDING_RULES.md`](../CONTENT_BUILDING_RULES.md).

## Related

- [`../PAGE_BUILD_TRACKER.md`](../PAGE_BUILD_TRACKER.md) — the other
  client-facing HTML deliverable: per-page content-migration progress during a
  migration. That one answers *"is my content in yet?"*; this one answers
  *"where is the project, and what do you need from me?"* Use both if a
  migration is running inside a build.
- [`../ASANA_QA_PROCESS.md`](../ASANA_QA_PROCESS.md) — the Marker.io/Asana QA
  loop. Where round-by-round QA findings come from.
- [`../WPE_PLATFORM.md`](../WPE_PLATFORM.md) — deploy and caching behaviour for
  the URL above.
