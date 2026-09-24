# Developing the client tracker template

**Read this before changing `build-tracker.py`.** The
[README](./README.md) documents how to *use* the template — the JSON schema,
what the page shows a client, how a child theme adopts it. This file documents
how to *develop* it: how the generator is put together, which decisions are
load-bearing, and which invariants will silently break if you are not looking
for them.

Written 2026-07-31, immediately after a substantial UI revision, by the session
that did it, and revised the same day by the **simplification pass** — which
turned the work into a table, deleted the Resolved section, cut the palette to
four colours and a grey, and flattened History. Where the two disagree, this
file has been updated; §8 records why each thing went.

It assumes you know nothing about this codebase.

---

## 1. Orientation — what all these names are

You are somewhere inside a WordPress site directory. Here is the full picture,
because none of it is guessable:

| Name | What it actually is |
|---|---|
| **`trialport`** | One Classic City client. Their local dev site lives at `~/Local Sites/trialport/`, and the git repo root is `~/Local Sites/trialport/app/public/` — i.e. the WordPress webroot *is* the repo. Nothing about the tracker template is trialport-specific; trialport just happens to be the checkout this work was done in. |
| **`classic-city-core`** | The **shared parent theme** for every Classic City client site. A WordPress block theme (FSE) with ~41 custom ACF blocks. Canonical home: `github.com/classiccity/ccc-wp-theme`. It is **subtree'd** into every client repo at `wp-content/themes/classic-city-core` (remote `upstream-parent`; subtree, not submodule, because WP Engine's Git Push deploys submodules as empty directories). **This template lives in the parent**, at `docs/client-tracker/`. |
| **`sg-{slug}`** (e.g. `sg-trialport`) | The **child theme** for one client. Owns their palette, fonts and page content. This is what is actually activated on the live site. |
| **A "round"** | One cycle of a build or QA pass. Chris runs client work in numbered rounds — round 1 foundations, round 2 the rest, round 3 post-launch, and so on. The tracker is organised entirely around rounds. |
| **The tracker** | A single self-contained HTML file, generated from one JSON file, that Chris sends a client a link to so they can see where the project stands and what is waiting on them. |

### The one architectural rule

**The parent holds the template. Children copy it. The template is never
edited by a child.** Because the parent is subtree'd into every client repo,
any change you make here reaches *every* Classic City client on their next
`git subtree pull`. Before adding anything, ask: *should every Classic City
client get this?* If no, it belongs in that child's copy, and that copy becomes
a fork — which is allowed, but it stops receiving template improvements.

This rule is restated in the README because clients' agents read that one; it
is repeated here because it is the constraint most likely to be violated by
someone "just adding a field".

### Where the pieces are

```
wp-content/themes/classic-city-core/docs/client-tracker/
├── README.md                    ← how to USE it (schema, client-facing behaviour)
├── DEVELOPING.md                ← you are here
├── build-tracker.py             ← the whole generator, stdlib only
├── skins/
│   └── classic-city/            ← the DEFAULT skin (skin.py + assets/ + README)
├── tracker.example.json         ← the worked example; also the schema fixture
├── tracker.example.html         ← COMMITTED — the rendered client example (skinned)
├── tracker.example.plain.html   ← COMMITTED — the same example, --no-skin
└── .gitignore                   ← *.internal.html
```

The **client** example page is committed so the template is browsable without
running Python; regenerate it in the same commit as any generator change. The
**internal** build never is — see §7.5.

---

## 2. Run it

```bash
cd wp-content/themes/classic-city-core/docs/client-tracker

python3 build-tracker.py tracker.example.json              # → tracker.example.html (Classic City skin — the default)
python3 build-tracker.py tracker.example.json --internal   # → tracker.example.internal.html
python3 build-tracker.py tracker.example.json --no-skin    # → tracker.example.plain.html (unskinned)
```

The Classic City skin is the **default** (resolved as `skins/classic-city/`
next to the script; `--skin DIR` overrides it, a missing default degrades to
an unskinned build with one stderr warning — see the decision log, §8 "The
skin is the default").

Python 3.9, standard library only. No venv, no dependencies, no network, no
build step. **3.9 is a hard floor** — it is the interpreter on Chris's machine
(`/usr/bin/python3`, 3.9.6). No f-strings, no walrus, no `match`, no
`X | Y` type unions at runtime. The file opens with
`from __future__ import annotations` so annotations are safe, but everything
else must be 3.9-legal.

### Looking at the output

`open tracker.example.html` works, but note that a `file://` page in some
preview harnesses renders as a static snapshot and will not scroll or respond
to clicks. To exercise it properly, serve it:

```bash
python3 -m http.server 8791 --directory .
# then browse http://localhost:8791/tracker.example.html
```

Serving it also lets you confirm the **one network request** invariant (§7)
in devtools.

---

## 3. How the generator is put together

One file, top to bottom, in dependency order. Approximate line anchors are
given because the file is long; they will drift, so search by name.

| Region | ~Line | What lives there |
|---|---|---|
| Module docstring | 1–95 | The *why*: one data file / two renders, why the round is the whole view, why grouped by page, why self-contained, why native `<details>`. Read it. |
| **Vocabularies** | ~110–160 | `STATUS`, `CHANGE_STATE`, `AWAITING`, `ROUND_STATE`, `EFFORT`, `OWNER`, `SEVERITY`. Each status maps to a `(label, tone, icon)` triple. **This is the single source of truth for the colour language** — the tone name becomes a `t-{tone}` class, which resolves three CSS custom properties. Nothing in the markup carries a literal colour. There are now **four tones plus grey**; `progress` is gone. |
| `esc` / `slug` | | Escape-everything and DOM-id-safe helpers. `esc(None)` is `""`, never the string `"None"`. |
| `short_date` / `long_date` | | `'2026-07-09'` → `JUL 9` / `July 9, 2026`. **Both pass anything non-ISO straight through** — the schema says `YYYY-MM-DD` but a child's JSON is hand-written, and a date you cannot format is still a date the client can read. Never make these raise. |
| **`ICONS` + sprite** | | See §4. |
| **Validation** | | See §6. |
| Small render helpers | | `chip()`, `safe_url()`, `anchor()`, `anchor_compact()`, `link_list()`. `safe_url` is the allow-list: `http://`, `https://`, `/` only — it exists to refuse `javascript:`. `anchor_compact` is the table's "Link": visible word `aria-hidden`, real label in `.sr-only` + `title`, swapped back by the print stylesheet. |
| **`Pages`** | | The page registry and the grouping algorithm. `Pages.group(records)` returns `[(page_id_or_None, label, records)]` with **site-wide first**, then registry order. `Pages.tag(pid)` renders the small page label, linked when the registry knows a URL. |
| **`Renderer`** | | **The mode boundary.** Every client-vs-internal decision goes through this class. See §5. |
| `h2_count`, `card_foot` | | Two shared bits of furniture. `card_foot` is the "same shape on every card" footer: rule, date left, links right — **no word in front of the date**, only an `aria-label`. |
| Counting | | `count_tasks` / `round_stats` — per-round tallies, computed over *mode-visible* records only, so the banner never advertises work the client cannot find. |
| **`client_asks`** | | **THE definition of "waiting on you", and the only one.** Returns `(blockers, [(section, task), …])`. Read the docstring before touching anything that counts. |
| `render_banner` | | The sticky banner **and** the round tab strip inside it. Emits per-round counters/meter/subtitle, each tagged `data-round` and `hidden` unless selected. **Three** stat pods (Done, Review, Blocked), each number-then-`.stat-l` (icon + label on one line). The tab badge calls `client_asks`. No expand-all button. |
| Blockers | | `render_blocker_card` (open blockers only — a `resolved_on` blocker renders nowhere), `render_ask_card` (a `needs_client` task in the same card shape), and `render_waiting` (one `<h2>` landmark per side). |
| `render_lede` | | The round's letter: paragraph + signature. Not a status row. |
| **The work** | | `task_page_cell` / `task_held_cell` / `status_cell` → `task_row` → `section_meta` → `render_section` → `render_work`. The two column-deciding helpers are called **twice** each per section — once to decide whether the column exists, once to fill it. Keep them pure and cheap; that is the whole trick behind the conditional columns. |
| History | | `changelog_row`/`changelog_table` → `render_history` — this round's changelog, one closed `<details>` fold per page, each fold a work-style `.wtable` (state glyph / Ref / What / Why / Date / conditional Link). Reuses the work table's cell classes on purpose: skin + print rules apply automatically. No outer wrapper. |
| `render_panels` | | Assembles one `role="tabpanel"` per round out of the **four** landmark sections (was five; Resolved is gone). |
| `render_legend` | | The colour key. Must list exactly the colours the page uses — five rows for five tones. |
| **`CSS`** | | One plain string (deliberately *not* an f-string, so nobody has to double every brace in a stylesheet). |
| **`CSS_INTERNAL`** | | Concatenated onto `CSS` **only** in internal mode. See §5. |
| **`JS`** | | ~85 lines, no framework: tabs, `aria-expanded` mirroring, deep-link reveal. (No expand-all — see §8.) |
| `build()` | | Validate → render content → assemble the document. Content is rendered *before* the sprite is emitted, because the sprite only carries glyphs that were actually used. |
| `main()` | | Argparse, file IO, the summary line printed to stdout. |

Line anchors were dropped from this table in the simplification pass — they
were wrong within a day of being written. Search by name.

### Data flow

```
JSON ─▶ validate()            fail loud, name the record, never render a broken page
     ─▶ Renderer(internal)    the mode switch
     ─▶ Pages(data)           registry + grouping
     ─▶ render_banner()       sticky header + round tabs (per-round, data-round tagged)
     ─▶ render_panels()       one panel per round; each panel = 4 <h2> landmarks
     ─▶ render_legend()
     ─▶ sprite()              only the glyphs the above actually asked for
     ─▶ single HTML string
```

---

## 4. The icon sprite

### How it works

`ICONS` is a dict of `name → (viewBox, path_d)`. Calling `icon("circle-check")`
returns `<svg class="ic" aria-hidden="true" focusable="false"><use href="#i-circle-check"></use></svg>`
and records the name in the module-level `_USED_ICONS` set. After all content
is rendered, `sprite()` emits one `<svg class="sprite">` containing a
`<symbol>` per used glyph, injected as the first thing in `<body>`.

`build()` calls `_USED_ICONS.clear()` on entry so two builds in one process
cannot contaminate each other.

### Adding a glyph

The parent theme's FontAwesome kit at `assets/fontawesome/` is **webfont-only**
— it ships `css/` and `webfonts/` and contains no SVG at all. The SVG
distribution of the same FA Pro package does exist on Chris's machine, in
another local site:

```
~/Local Sites/homebase/fontawesome/svgs/{solid,regular,light}/{name}.svg
```

(`~/Local Sites/site-script/public/fontawesome/svgs/` is a second copy.) Those
directories are FA **Pro 7.2.0**, matching the kit the parent ships.

To add one:

```bash
cat "$HOME/Local Sites/homebase/fontawesome/svgs/solid/circle-info.svg"
```

Copy the `viewBox` and the single `d` attribute into `ICONS`. The template uses
the **solid** style throughout; mixing styles is possible but you would be
adding a second visual weight for no stated reason.

If a glyph has more than one `<path>`, the extraction is not a one-liner —
either pick a single-path icon or concatenate the `d` values and check the
result renders (duotone icons in particular will not survive this).

### The rule you must not break

**Every icon is decorative.** Every icon is `aria-hidden="true"
focusable="false"`, and the page must stay unambiguous if the SVG fails to
render entirely. The two count badges (the tab badge and the `h2-count` pill)
follow the same discipline for a different reason — see §8, "Numbers inside
headings".

**The two exceptions on a section header, and what they must carry.** The flag
glyph and the page link have no visible text beside them. They are legal
because:

- each wraps an `.sr-only` label (*"Needs you"*, *"Open Our Team"*) plus a
  `title` for a hover tooltip, so both have an accessible name;
- the flag is never the only statement of a state — the row it points at
  carries a full status chip with the word on it;
- the page link's `.sr-only` label is **un-hidden by the print stylesheet**
  (`.pagelink .sr-only{position:static;…}`), because a printed page cannot be
  hovered and needs the words.

If you add another icon-only control, it needs all three. An icon-only control
with no accessible name is a bug, not a feature.

---

## 5. Where the two render modes diverge

This is the part that must never regress, because getting it wrong means
mailing a client your internal notes.

**Everything mode-dependent goes through `Renderer`.** There is exactly one
place to audit. The methods:

| Method | Client build | Internal build |
|---|---|---|
| `visible(items)` | drops any record with `internal_only: true` | returns everything |
| `body(item)` | `summary` only — **and nothing at all if `summary` is missing** | `summary`, then `internal_note` in a distinct block |
| `note(item)` | `""` | the `internal_note` block |
| `meta_pills(item)` | `""` | the `owner` and `effort` pills |

Plus two things outside the class:

- `CSS_INTERNAL` is concatenated onto `CSS` **only** when `internal` is true.
- The `mode-flag` banner is only emitted when `internal` is true.

### The two design rules behind it

1. **"Never emitted" means never emitted.** The client build does not hide
   internal fields with CSS or `display:none` — it never writes them into the
   file. Hidden content in a file the client can download is not hidden.
2. **The client build does not even ship the internal stylesheet.** A `.t-owner`
   rule sitting unused in a client's file is a question waiting to be asked,
   *and* it defeats the one-line grep audit. This is why `CSS_INTERNAL` is a
   separate string rather than a block inside `CSS`.
3. **`summary` never falls back to `internal_note`.** That fallback is the
   exact leak the whole design exists to prevent, so it does not exist. A
   record with no client-safe form should be flagged `internal_only`, not given
   a euphemism.

### The audit (run it after any change to a renderer)

```bash
cd docs/client-tracker
python3 build-tracker.py tracker.example.json
python3 build-tracker.py tracker.example.json --internal

for m in t-owner t-effort internal-note mode-flag; do
  printf "%-14s client=%s internal=%s\n" "$m" \
    "$(grep -o "$m" tracker.example.html      | wc -l | tr -d ' ')" \
    "$(grep -o "$m" tracker.example.internal.html | wc -l | tr -d ' ')"
done
```

Expected as of 2026-07-31 (client must be **0** in every row; internal counts
will move as the example data changes, and only need to be non-zero):

```
t-owner        client=0 internal=30
t-effort       client=0 internal=29
internal-note  client=0 internal=18
mode-flag      client=0 internal=2
```

And the stronger check — every `internal_note` *string* in the data, plus the
`internal_only` records:

```bash
python3 - <<'PY'
import json
data = json.load(open("tracker.example.json"))
client = open("tracker.example.html").read()
internal = open("tracker.example.internal.html").read()
def walk(o):
    if isinstance(o, dict):
        for k, v in o.items():
            if k == "internal_note" and isinstance(v, str):
                yield v
            for x in walk(v): yield x
    elif isinstance(o, list):
        for i in o:
            for x in walk(i): yield x
notes = list(walk(data))
print("notes:", len(notes),
      "leaked:", len([n for n in notes if n[:40] in client]),
      "in internal:", len([n for n in notes if n[:40] in internal]))
print("internal_only leaked:", "Build tooling" in client or "X-01" in client)
PY
```

Expect `notes: 18 leaked: 0 in internal: 16` and `internal_only leaked: False`.

**Why 16 and not 18.** Two of the example's `internal_note`s hang off blockers
that have a `resolved_on`, and a resolved blocker now renders nowhere — not
even in the internal build. That is a real, accepted consequence of deleting
the Resolved section (§8): the note is still in the JSON, it is just no longer
on the page. **The number that matters is `leaked: 0`.** If `in internal` ever
*exceeds* the number of notes on unresolved records you have a different bug;
if it drops, check whether you resolved something rather than broke something.

---

## 6. Validation

All in `validate(data)`, one pass, raising `DataError`. `main()` catches it and
prints `Data problem — {message}` to stderr with exit code 1. **The page is
never written when validation fails** — a half-valid tracker in front of a
client is worse than no tracker.

Three helpers do the work:

- `_require(obj, keys, where)` — presence, with the record named.
- `_enum(value, table, field, where)` — membership, and it **prints the legal
  values**. That is not decoration: the failure mode designed for is a typo in
  a status key at 6pm before a client call, and a list of valid values turns a
  two-minute hunt into a five-second fix.
- `_page(rec, where)` — a `page` must exist in the root `pages` registry.

Every check exists because it is a mistake someone will actually make. The
`where` string is built up as you descend (`round 2 section s2-pages task
P-01`), so every message points at exactly one record.

If you add a field, add its check here in the same style. Current coverage,
all verified to produce record-precise messages:

| Mistake | Message names |
|---|---|
| Unknown `status` | round, section, task + the seven valid values |
| Dangling `blocked_by` | round, section, task + why (blockers must be declared in the same round or earlier) |
| Unknown `page` on a task / blocker / section / changelog entry | the record + every declared page id |
| Duplicate page id | the offending `pages` entry |
| `pages` entry with no `id` / no `label` | the entry |
| Unknown `awaiting` | the blocker + `agency, client` |
| Unknown changelog `round` | the entry |
| Duplicate round number / duplicate blocker id | the collection |
| Bad `severity`, `owner`, `effort` | the record + valid values |

**`severity` is still validated even though nothing renders it.** That is
deliberate, not an oversight: silently accepting `"severity": "urgnt"` teaches
an author that the field does something, and the day someone gives severity a
job again they inherit a corpus of typos. Same reasoning applies to any field
you retire — retire the *rendering*, keep the *validation*.

---

## 7. Invariants — things that must never regress

Run all of these before you consider a change done.

### 7.1 The leak audit
Section §5. Client build: `0` for `t-owner`, `t-effort`, `internal-note`,
`mode-flag`, and every `internal_note` string.

### 7.2 Zero external requests
The page is served as a static file out of a child theme on WP Engine, with no
build step and no CDN whitelist on that path — and it must survive being
emailed, saved, or opened offline. So:

```bash
for p in 'src=' '<link' '@import' 'url(' '//cdn' 'fontawesome'; do
  printf "%-14s %s\n" "$p" "$(grep -o "$p" tracker.example.html | wc -l | tr -d ' ')"
done
```

All must be `0`. In a browser, exactly **one** network request per page load
(the document itself; there is not even a favicon request). `href="#i-…"`
occurrences are internal sprite references, and `href="http…"` occurrences are
the client's own data links — neither is a fetch.

### 7.3 Python 3.9
```bash
python3 --version          # 3.9.6
python3 -m py_compile build-tracker.py && rm -rf __pycache__
```
Zero f-strings, zero walrus, zero `match`.

### 7.4 `docs-check` stays at 7
```bash
cd wp-content/themes/classic-city-core && node scripts/docs-check.mjs
# docs-check: 7 problem(s)
```
**7 is the pre-existing baseline**, not a target of zero. All seven are
`scripts/generate-block-docs.php` referencing `usage.md` — an unrelated
false-positive in a different subsystem. If your change makes it 8, you broke a
relative markdown link; fix it. If it makes it 6, you fixed something unrelated
by accident — fine, but say so.

Practical consequence: **every relative link you add to a markdown file in this
folder must resolve.** That is what pushed the count up during development
twice.

### 7.5 The internal build never enters git; the client example always does
`.gitignore` in this folder is `*.internal.html` — **not** `*.html`. That
changed after this file was first written: the rendered *client* example is
committed on purpose, so the template is reviewable on GitHub or in a fresh
clone without running Python, and its data is fictional so there is nothing to
leak. The *internal* build stays out, because the parent theme deploys to every
client site and is served publicly, and a file named `*.internal.html` at a
public URL contradicts the one promise this template makes.

**So regenerate `tracker.example.html` in the same commit as any generator
change.** A stale committed example is a lie that survives review.

Verify with `git status --porcelain -uall docs/client-tracker/` — only
`.gitignore`, `README.md`, `DEVELOPING.md`, `build-tracker.py`,
`tracker.example.json`, `tracker.example.html` (the skinned build — the
default IS the deliverable), `tracker.example.plain.html` (the `--no-skin`
build) and the `skins/` tree (`skins/classic-city/`: `skin.py`, `README.md`,
`assets/*`) should appear, and neither `tracker.example.internal.html` nor
`tracker.example.plain.internal.html` may **ever** appear (`*.internal.html`
covers both).

### 7.6 Copy-don't-edit
§1. A child never edits this folder.

### 7.7 Accessibility floor
- Round tabs: real `<button type="button">` with `role="tab"`,
  `aria-selected`, `aria-controls`, roving `tabindex`, and Arrow/Home/End.
- `<details>`/`<summary>` for every accordion; `aria-expanded` mirrored onto
  the summary by JS (it only ever *reports* the native state, so it can never
  contradict it).
- Heading chain h1 → h2 → h3 with no skipped levels. Task titles are
  `<th scope="row">`, not headings — the h4 level went away with the cards.
- Every table has a `<caption class="sr-only">` and `scope` on every header
  cell.
- Icon-only controls carry an `.sr-only` name — see §4.
- `prefers-reduced-motion` respected; `:focus-visible` styled.
- Print CSS forces the light palette, opens every accordion, reveals every
  round panel, and prints a per-panel round heading via
  `content: attr(data-round-label)` — because a printed page cannot be clicked
  and the tab strip is hidden.

### 7.8 Print actually opens the accordions
Not a nice-to-have: half the page is inside `<details>`, so if this regresses
a printed tracker is a list of headings.

**This broke once already, from underneath.** The old UA rule was
`details:not([open]) > *:not(summary){display:none}`, which the print
stylesheet beat with `display:block!important`. Current Chrome instead hides a
`::details-content` pseudo-element using `content-visibility`, which `display`
cannot touch — so the override silently stopped working and print regressed to
collapsed headers. Both rules are now in `@media print`; **keep both.**

Check it, do not assume it:

```bash
"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" --headless \
  --disable-gpu --no-sandbox --no-pdf-header-footer --virtual-time-budget=2500 \
  --print-to-pdf=/tmp/t.pdf http://localhost:8791/tracker.example.html
```

A correct build is ~14 pages. **If it comes out around 7, the accordions are
shut** — that page-count halving is the cheapest possible regression test.

### 7.9 No horizontal overflow
At 1280 and 375, `document.documentElement.scrollWidth === window.innerWidth`.
Wide content — the work tables — scrolls inside its own
`.tablewrap { overflow-x: auto }`, never the page. On paper that wrapper is
`overflow: visible` with the min-widths and font size dropped, because
`overflow-x:auto` guillotines the Status column when there is nothing to
scroll.

---

## 8. Decision log — the things that look arbitrary and are not

Each of these is a decision a newcomer would plausibly "fix". Do not, without
reading the reason and disagreeing with it on purpose.

### Inline SVG sprite instead of linking the theme's FontAwesome kit
The parent ships a kit at `assets/fontawesome/`, but it is webfont-based: a
stylesheet per style plus ~6 MB of woff2. Linking its CSS by relative path
works while WP Engine serves the file out of the theme — and produces a page of
empty boxes the moment anyone emails it, saves it, or opens it from a USB
stick. The README explicitly offers *"email the self-contained file"* as the
privacy-preserving alternative to a public URL, so that failure is not
hypothetical. base64'ing a whole woff2 is ~260 kB of font for seventeen glyphs
on an ~83 kB page. A CDN is not an option at all (§7.2). **Accepted cost:**
adding an icon is a Python-dict edit rather than a class name in markup.

### Site-wide comes first in every page grouping
Three reasons, all of which have to fail before you flip it: (1) a site-wide
change is *context* for everything under it — "the header phone number is now
tappable" explains something the client would otherwise re-report on four
separate pages; (2) a fixed first position means the group does not shuffle as
the project grows pages, so a returning reader knows where to look; (3) it is
the group most likely to be non-empty, so the section never opens on what looks
like an empty list. Named pages then follow in **registry order**, which means
the author controls prominence by ordering the `pages` array.

### The work is a table, and two of its columns are conditional
The cards this replaced carried the same six facts as a table row, but each
one landed in a different place on every card depending on which optional
fields were present — so reading twelve items was twelve separate hunts. A
table fixes each fact in a column and the reader learns the layout once.

*Held on* and *Page* appear only when a section has something to put in them,
because a column that is empty on every row is worse than no column. That is
not a flag in the data: `task_held_cell()` and `task_page_cell()` are called
once to test and once to fill, and a task that simply inherits its section's
page returns `""`. Chris's "if all of these things are on the same page, we
don't need that last column" therefore falls out of the data rather than
needing to be declared. **Keep those helpers pure** — that double call is the
whole mechanism.

### There is no "Resolved" section
There was, and it listed blockers with a `resolved_on`. Once the work became a
table with its own Status column, it was the third place on one page saying
something had finished — after the Done chips and after History. Chris: *"with
the edits to the work in this round section, it makes me think that we don't
need the resolved section at all."*

`resolved_on` now does exactly one thing: it takes the blocker off the board.
Two consequences to know before you "restore" it:

- **`resolution` is dead weight in the renderer.** It is still validated and
  still in the schema, because the text is worth writing — as a changelog
  entry. The README says so in the field table.
- **A resolved blocker's `internal_note` also stops rendering,** including in
  the internal build. That is why the leak audit's `in internal` count is 16
  and not 18 (§5). It is accepted, not a bug.

If you later want completed *tasks* rolled up by page, that is a **new**
section, and it should not reuse this shape.

### Changelog entries with no `round` appear in every round's History
History became per-round in this revision. An entry with no `round` (kickoff,
hosting, the contract) genuinely is not round-scoped, so it would otherwise
vanish from the page entirely. The alternative — making `round` required —
would break the existing schema shape for every child that has already adopted
the template. So they render in each round's History inside a clearly labelled,
collapsed *"Not tied to a specific round"* fold. The duplication is honest and
cheap; the disappearance would not be.

### Cards are near-neutral
A card sitting under an `<h2>` that already says *Waiting on you*, carrying a
chip that already says *Needs you*, does not also need to be amber. That
is one fact stated three times, and at that density the page reads as a
highlighter accident rather than a status report. Colour is confined to four
surfaces: **the banner counters, the status chips, the round tabs, and the
one-glyph section flag.** The one survivor inside a card is the `ask` block —
an amber left rule, not a filled panel — because that is the literal "your
move" sentence.

### Four colours and a grey — the blue `progress` tone was deleted
Not deprecated, deleted: the custom properties, the `.t-progress` class, and
every reference (`STATUS.in_progress`, `AWAITING.agency`, `ROUND_STATE.open`,
`CHANGE_STATE.partial`, the banner's *In progress* pod). Chris: *"the only
colours we need are green for done, purple for review, red for blocked."*

Amber is the fifth and it is not a status colour — it is the your-move signal
the whole page is built on, and `needs_client` keeps it for exactly that
reason. "In progress", "queued", "deferred" and "waiting on us" all say the
same thing to a client (*on our list, nothing needed from you*), and three
near-identical blues asking to be told apart was the noise this pass removed.

`render_legend()` must list **exactly** the colours the page shows — it went
from seven rows to five. A key naming a colour the page never draws is worse
than no key, because it teaches a distinction the reader then hunts for.

### Three things were removed for "one fact, one place", and should stay gone
- **The *Urgent* chip** on high-severity blockers. Everything under *Waiting on
  you* / *Waiting on us* is urgent — that is what the heading means.
  `severity` is still validated so existing data keeps building; it draws
  nothing.
- **The status dots** on section headers — a coloured-dot density read sitting
  next to a `1/6` that said it better, and a per-status `title` attribute no
  screen reader was going to enumerate.
- **The word before the date** in `card_foot`. "Raised 2026-07-08" in a footer
  under a calendar glyph is the glyph's job done twice. The word moved into
  `aria-label`, so nothing was lost for a screen reader.

### Links are bold ink with an underline, not the brand accent
`--accent` is the client's own brand colour, picked to look right on *their
site*. Set at 13px on a white card it produced the least readable string on the
page (Chris, on a green "Upload folder": *"kind of hard to read"*). Weight plus
an underline is a stronger link affordance than hue anyway, and — the reason it
is the right *template* decision rather than a taste one — it stays legible
whatever accent the next child theme brings, which a hue-based rule cannot
promise.

### "Waiting on you" is computed in exactly one place
`client_asks()` returns the open client-owned blockers and the `needs_client`
tasks for a round, and everything that states that number consumes it: the tab
badge, the `<h2>` count, and the cards themselves. This exists because the
badge and the heading previously computed it independently, differently — 4 vs
2 on the same round — and Chris caught it on sight.

Note the direction of the fix: the *section* was widened to match the badge,
not the badge narrowed to match the section. Blockers-only would have made the
numbers agree while letting a round whose only client-blocking item is a
needs-you task print *"Nothing is waiting on you"* over an amber chip. If you
add a third thing that counts as "waiting on you", it goes in `client_asks()`
and nowhere else.

### The banner has three pods
Five became two (Review, Blocked), then Done earned its place back: it is the
counter clients most want at a glance, and asking them to read a progress bar
for it failed the "at a glance" test in practice. In progress and Needs-you
stay out for the original reasons — progress is answered by every section's
own `N/M`, and needs-you by the amber tab badge inside the same sticky unit.
A dashboard row that repeats its own screen is decoration. If someone asks
for a fourth pod, first ask which existing statement of that number they
failed to see — that is usually the real bug.

### The round opens with a letter, not a status row
`render_lede()` used to emit chip + date-stamp + paragraph in one flex row —
three unrelated things, led by a chip repeating the selected tab. It is now a
pod: the round `summary` as prose, then `— {note_by} · {note_on}`. `state` is
still required and validated but draws nothing (the `severity` precedent, see
§6). The signature falls back `note_by → project.author` and
`note_on → closed → opened → project.updated`, and the pod is skipped entirely
when there is no summary — a signature under nothing is not a letter.

This is also why `project.intro` is dead: one generic paragraph identical on
every tracker, replaced by a per-round paragraph that has to actually say
something. `intro` is still accepted and ignored, same retire-the-rendering
rule as everything else.

### The status column is a first-column glyph with no visible heading
Three reductions in one (move left, drop the header word, drop the chip
words), all safe for the same reason: the glyph is not colour-coded only —
each of the seven statuses has a **distinct shape**, plus `title` and an
`.sr-only` word, and the column heading is `.sr-only` rather than absent so
the table still announces seven columns. On paper the glyph is hidden and the
word is shown instead (`@media print` swaps them), because a printed page has
neither hover nor a legend in view. If a new status is ever added, its glyph
must be distinguishable in shape from the existing seven at 1em — that is now
a hard requirement, not a nicety.

### Table dates are `JUL 9`; table links are "Link"
Both are width reclaimed for the Detail column, and both keep the full
information one layer down: the date cell is `<time datetime title>`, the link
keeps its real label as accessible name + `title` (`anchor_compact()`), and
the print stylesheet un-hides both. `short_date()`/`long_date()` pass
unparseable input through untouched rather than raising — hand-written JSON,
and a date we cannot format is still a date the client can read.

### There is no expand/collapse-all
Its two justifications evaporated: print forces every accordion open via CSS,
and browsers auto-expand a closed `<details>` when find-in-page matches inside
(`hidden=until-found` behaviour). What remained was a control whose only job
was to fight the page's own deliberate default state. Do not bring it back
without a third justification.

### History is one layer of accordion, not two
It was a closed *"Every change we made in round N"* wrapper containing a fold
per page: two clicks to read one page's history, and the wrapper's only job was
to stop History dominating the page. The folds do that themselves, and they say
which page while they do it. So the wrapper went and the folds render closed,
directly under the `<h2>`. The old rule is intact — status first, history only
if you go looking — it is now enforced by the folds rather than by a lid on
top of them.

### Amber means "your move" and nothing else
`--t-client` is used for exactly one thing, everywhere on the page: *we cannot
proceed without something from you.* Not warnings, not medium severity, not
decoration. That is what makes the page's implicit promise true: **if a client
scans it and sees no amber, nothing is waiting on them.** It is the single most
valuable thing the page does, and every future edit has to keep it. If you need
a "caution" colour for something else, you need a different token, not this one.

### Sticky, not fixed
`position: sticky` gives "stays put while scrolling" without `fixed`'s two
costs: a `body { padding-top }` hack that breaks whenever the banner wraps on
mobile, and a banner stamped over the content on every printed page. The print
stylesheet sets it back to `static`.

### The round tabs are inside the banner
Before this revision, the banner and the round tabs were separate, and the page
*also* had a "the work, by round" accordion. Three controls, all claiming to
answer "what round am I looking at". Now: one sticky unit, and selecting a round
swaps the panel *and* the banner's counters, meter and round label. The "work,
by round" section was deleted outright — a round tab is already a round view.

### Tabs run ascending left-to-right
The previous version put the *current* round first. Rounds are chronological,
and a nav whose order changes as the project advances is disorienting for
someone who bookmarked the page in round 2. Ascending order, current round
selected.

### Numbers inside headings are `aria-hidden` and re-stated
A bare `2` inside `<h2>Waiting on you</h2>` concatenates into the accessible
name, and a screen reader announces *"Waiting on you 2"* — which reads as a
heading called "Waiting on you 2". So the pill is `aria-hidden="true"` and an
`.sr-only` span says `, 2 items`. Same trick, same reason, on the tab badge
(`, 4 waiting on you`). `h2_count()` exists to make this the default.

### "Waiting on you" and "Waiting on us" always render, even when empty
*"Nothing is waiting on you in this round"* is the most valuable sentence on
the page, and a landmark that disappears when empty is a landmark nobody
learns to look for. *History* is omitted when empty, because its absence
carries no information.

### `[hidden] { display: none !important }` is load-bearing
Near the top of `CSS`. The round switcher toggles the `hidden` attribute, and
half the things it toggles are flex containers — whose `display: flex` beats
the UA's `[hidden] { display: none }`. Without that one line every round's
counters render at once. This was an actual bug during development; it looks
like a redundant reset and it is not.

The print stylesheet deliberately *out-specifies* it with
`.tabpanel[hidden] { display: block !important }` (specificity 0-2-1 beats
0-1-0 at equal `!important`), which is how every round prints while the
selected round's banner counters stay the only ones on paper.

### The skin is the default
Building with no flags gets the Classic City skin, because the tracker is a
Classic City deliverable — the branded page IS the product a client is sent,
and the plain build is the escape hatch (`--no-skin` →
`{name}.plain.html`), not the product. Resolution is **script-relative**
(`skins/classic-city/` next to `build-tracker.py`, never the cwd or the
JSON's directory), so a child gets the default by copying one folder —
`build-tracker.py` plus `skins/` — with no flag to remember and no path to
get wrong. And a missing default skin degrades to a **warning plus an
unskinned build**, never an error, because a status page that fails to build
on the afternoon of a client call is worse than an unbranded one; only an
*explicitly passed* `--skin` that is broken is allowed to fail the build,
since that one is a typo, not a degraded environment.

---

## 9. How the round switching is wired

The contract is one attribute: **`data-round="{n}"`**.

- Everything that belongs to exactly one round carries it: the tab buttons, the
  tab panels, each `.banner-stats` block, each `.meter`, each `.banner-sub`
  label.
- `select(tab)` in `JS` reads `tab.dataset.round`, then hides/shows every
  `[data-round]:not([role="tab"])` on the page by comparing attributes. One
  loop; nothing enumerates *which* things are round-scoped.
- Tabs themselves are excluded from that loop (they are never hidden) and get
  `aria-selected` + roving `tabindex` instead.

**If you add anything round-scoped, tag it `data-round` and it works.** If you
add something that must *not* switch — the intro paragraph, the colour key, the
footer — leave the attribute off and put it outside the panels.

Deep links (`…/round-2.html#p-04`) are handled by `reveal()`: it walks up from
the target opening every ancestor `<details>`, and when it hits a `.tabpanel`
it clicks that panel's tab — so a link into a *non-selected* round switches
rounds first, then opens the accordion, then scrolls. The target is now a
`<tr>`, which is why `:target`'s box-shadow is replaced by an inset outline for
table rows — a box-shadow around a `<tr>` renders as a floating rectangle.

`:target` carries `scroll-margin-top: var(--banner-h)` so it does not land
underneath the sticky header. `--banner-h` is **145px desktop / 195px mobile**
and must be re-measured whenever the banner changes shape:

```js
document.querySelector('.banner').getBoundingClientRect().height
```

at 1280 and at 375 (currently 135 and 235; the token rounds up).

Gotcha when testing this by hand: setting `location.hash` to a value it already
has does **not** fire `hashchange`, and `reveal()` runs asynchronously. Clear
the hash first and allow a tick, or you will diagnose a bug that is not there.

---

## 10. Testing checklist

No test suite — it is a static-page generator, and the meaningful assertions
are visual and behavioural. This is the pass that was run on 2026-07-31; repeat
it after any non-trivial change.

**Generation**
- [ ] Both modes build without error.
- [ ] Leak audit clean (§5) — `leaked: 0` is the line that matters.
- [ ] `py_compile` passes on 3.9 (§7.3).
- [ ] `node scripts/docs-check.mjs` prints 7 (§7.4).
- [ ] `tracker.example.html` regenerated and staged; no
      `tracker.example.internal.html` in `git status` (§7.5).

**Validation** — corrupt a copy of the example JSON and confirm each message
names the record: bad `status`, dangling `blocked_by`, typo'd `page` on a task
/ blocker / changelog entry, duplicate page id, `pages` entry with no `id`,
bad `awaiting`, unknown changelog `round`.

**In a browser, served over HTTP**
- [ ] Round switching by mouse: panel, banner counters, meter and round label
      all follow.
- [ ] Round switching by keyboard: Arrow Left/Right, Home, End; roving
      `tabindex`; `aria-selected` correct.
- [ ] Accordions toggle by mouse and by keyboard; `aria-expanded` matches
      `open` (check *after* a tick — the `toggle` event is async).
- [ ] The tab badge and the *Waiting on you* count agree on every round, and
      the cards under the heading match the number (blockers + needs-you
      tasks). A needs-you card's ref links to its table row.
- [ ] Ctrl+F for text inside a **closed** accordion still reveals it (the
      browser's own `<details>` auto-expand — this replaced expand-all).
- [ ] Deep link on cold load into a non-selected round selects that round,
      opens the accordion, and lands clear of the sticky header. Verify with
      `document.getElementById('b-03').getBoundingClientRect().top >
      document.querySelector('.banner').getBoundingClientRect().height`.
- [ ] A section header's page link **navigates without collapsing the
      section** (see the note in `JS` — it needs no guard, but check it).
- [ ] Conditional columns are right (status column included in the count).
      Round 2 should give 7 / 6 / 5 columns for `s2-pages` / `s2-booking` /
      `s2-launch`; round 1 should give 5 / 5 / 6 for `s1-brand` / `s1-home` /
      `s1-services`, and only `s1-home` should have a `.pagelink`.
- [ ] Table compressions hold: first column is a bare glyph with an `.sr-only`
      heading; Added reads `JUL 9` with the ISO date on hover; Page reads
      "Link" with the real label as `title`. Print shows the words instead.
- [ ] Zero console errors; exactly one network request.
- [ ] No horizontal page overflow at 1280 and at 375; tables scroll inside
      `.tablewrap`.
- [ ] Dark mode (`prefers-color-scheme: dark`) is legible.
- [ ] Print preview: light palette, all rounds shown, each with its own round
      heading, **all accordions open** (§7.8 — count the pages), tab strip
      gone, and no table clipped at the right margin.

**Data-shape edge cases the example is built to exercise** — keep these
working, and keep the example demonstrating them:
- A round with **no page associations at all** (round 3): History renders flat,
  with no lone "Site-wide" wrapper.
- A section **that is a page** (round 1, `s1-home`): page link in the header,
  and no *Page* column because every row inherits it.
- A section **that is not a page but whose tasks are** (round 1,
  `s1-services`; round 2, `s2-pages`): no header link, *Page* column present.
- A section with **neither** (round 2, `s2-launch`): five columns, no flag.
- A **blocked task** (`P-02`, `B-02`, `B-03`): makes the *Held on* column
  appear for its whole section.
- An **`internal_only` section** (round 2, `s2-internal` / task `X-01`): absent
  from the client build.
- A **changelog entry with no `round`** (`Build started`): appears in every
  round's History in the trailing *"Not tied to a specific round"* fold.
- **Resolved blockers** (`b1-logo`, `b2-prices`, `b2-erhospital`,
  `b2-favicon`): render **nowhere**, in either build. They are still in the
  example on purpose — they are the fixture for that behaviour.

---

## 11. What I would do next

Roughly in the order I would pick them up. None of these are started.

1. **A `--check` / dry-run flag.** Validate and print the summary without
   writing a file. Useful in a pre-commit hook in a child theme, so a broken
   `round-2.json` cannot be committed.
2. **A "completed work by page" summary.** Chris's original phrasing was about
   *"summaries of things that we have completed"*. Right now page grouping
   lands on History; done **tasks** are visible in the work table's Status
   column but only inside their section accordion. A per-page roll-up would
   finish the thought — but it needs a real decision about whether it replaces
   or duplicates the section view, which is why I did not guess.
3. **Per-page progress.** The registry knows every page; a small "3 of 5 items
   done" per page group is cheap and is the number a client actually asks for.
4. **Re-measure `--banner-h` automatically.** It is currently two hard-coded
   values (145px / 195px) that have already been wrong twice. A tiny
   `ResizeObserver` writing the real height into the custom property would stop
   deep links landing under the header if the banner ever changes shape again.
5. **A per-page anchor.** `#page-team` scrolling to that page's fold in
   History would let an email say "here is everything we did on Our Team".
6. **A card layout for the work table below ~600px.** It scrolls horizontally
   inside `.tablewrap` today, which is honest but not lovely on a phone. Only
   worth it if a client reads these on mobile — check before building it,
   because it reintroduces the two-layout problem the table just solved.

### Deliberately left undone (and why)

- **No test suite.** The assertions that matter here are visual; a snapshot
  test of generated HTML would break on every cosmetic change and teach people
  to regenerate the snapshot without reading it. The checklist in §10 is the
  substitute.
- **No `page` on rounds.** A round spans pages by definition.
- **No sorting/filtering UI.** Every control added is a control that can
  disagree with the round tabs. The page has exactly one primary control on
  purpose.
- **`area` (the old free-text label) was kept, not removed.** It still renders;
  `page` is what groups. Removing it would break child themes that already use
  it. The README documents it as superseded.

---

## 12. Outstanding chores from the 2026-07-31 session

Two standing conventions were **not** carried out, deliberately. They are
outstanding, not forgotten — decide on them rather than assuming they happened.

### No `BUILD_LOG.md` entry was written
Chris's standing convention is that a repo with a `BUILD_LOG.md` gets a
newest-first entry appended after any meaningful task
(`## YYYY-MM-DD — headline` plus 1–3 lines of *what changed and why*).
`wp-content/themes/classic-city-core/BUILD_LOG.md` exists and qualifies.

It was skipped for two reasons: the session was explicitly instructed **not to
run `git commit`, `git push` or any deploy**, and that convention pairs the log
entry with a commit; and `BUILD_LOG.md` was already showing as modified by
parallel work in the same session, so appending concurrently risked clobbering
someone else's edit.

**If you are picking this up:** an entry covering the tracker revision (round
tabs into the sticky banner, page grouping, inline SVG icons, neutral cards) is
still owed — **and so is one for the simplification pass** that followed it the
same day: work-as-a-table with conditional columns, Resolved deleted, palette
cut to four colours and a grey, History flattened to one accordion layer, links
de-branded. One combined entry is fine; they are one day's work on one file.

### No Chief of Stuff `cos-update` was pushed
The same convention pushes a build-status block to Chris's Chief of Stuff vault
via `"$HOME/Chief of Stuff/bin/cos-update"`. That script commits **and pushes**
— to a different repo, but still a push — which conflicts with the "no commits,
no pushes" instruction the session was working under. Skipped and flagged
rather than done unilaterally.

**If you are picking this up:** confirm with Chris that a push is wanted, then:

```bash
"$HOME/Chief of Stuff/bin/cos-update" \
  --headline "client tracker template: round tabs in the sticky banner, work as a table, palette cut to four colours" \
  --body "Revised the parent-theme client tracker template, then simplified it. Round tabs live in the sticky banner and switch every section; history groups by page with site-wide first, as one accordion layer rather than two. A round's work is now a table (ref, item, detail, held-on, added, page, status) with the held-on and page columns appearing only when a section has something to put in them; section headers carry a needs-you flag glyph, a link to the page, and a done/total count. The Resolved section was deleted — a cleared blocker just leaves the board and the changelog carries what happened. Palette is down to green/purple/amber/red plus grey, links are bold ink rather than the brand accent, and the urgent chip and status dots are gone. Icons stay inlined FontAwesome SVG so the page is self-contained. Leak audit, zero-request and print-opens-accordions invariants all hold." \
  --milestone "parent theme tooling"
```

### Also note
Nothing under `wp-content/themes/sg-trialport/docs/qa/` was touched. That is a
**live tracker for in-flight client work** — an earlier, hand-maintained
instance of this same idea — and it was reference-only for the revision. It is
not a copy of the current template and should not be assumed to match it.
