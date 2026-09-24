#!/usr/bin/env python3
"""
Build the standalone HTML triage report for an Asana + Marker.io QA round.

Chris's call, 2026-08-19: **over ~10 tickets, this report IS the deliverable** —
three markdown tables in the terminal stop being readable somewhere around a
dozen rows, and a QA round is usually 40+. Ten or fewer, skip this entirely and
print the tables inline in chat; the file is overhead at that size.

**These reports are flat HTML committed to the client repo. Never publish one as
an Artifact.** See `classic-city-core/docs/ASANA_QA_PROCESS.md`.

Usage:
    python3 asana-triage-report.py tickets.json asana-qa-triage.html \
        --client "trialport" --date 2026-08-21

Layout, rebuilt 2026-08-21 on Chris's spec:

  * a **fixed navbar** carrying everything: the client name verbatim on the left
    (no suffix, no title-casing — trialport's brand is lowercase and any
    transform is a bug), the four consolidated status counts beside it — each a
    full-height divider-separated cell, and each a button that scrolls the list
    to its section — and two dropdowns on the right in cells of exactly the same
    construction, clickable edge to edge;
  * **master/detail, not an accordion** — the left 65% is the scannable list,
    the right 35% is a full-height panel, flush to the navbar and to the bottom
    of the viewport, showing the selected ticket in full. The first ticket is
    selected on load so the panel is never empty.

FILENAME SCHEME (2026-08-21). The current report is written to the **un-dated**
`asana-qa-triage.html` so a link to it never goes stale. Previous builds live
beside it as `asana-qa-triage-YYYY-MM-DD.html` and are the archive. Archiving is
automatic: every run stamps `<meta name="qa-build-date">` into the file, so the
next run reads the stamp off the file it is about to replace, copies it to its
dated name, rewrites that copy's date dropdown and superseded banner in place,
and only then writes the new build. Nothing for a human to remember; pass
`--no-archive` to opt out.

The date dropdown is a directory scan done at GENERATION time: a static file
cannot know about builds that do not exist yet, so at write time we list the
output file's own folder for sibling `asana-qa-triage-YYYY-MM-DD.html` files and
emit each as an option whose value is the filename. An archived copy also gets a
"Current report" option pointing back at the un-dated file, so no report is ever
a dead end.

Input JSON: a list of objects. Only `title` and `url` are required.

    {
      "num":    12,                          # THE ticket number — see TICKET
                  # NUMBERS below. Assigned once by this script and written back
                  # into this file; never edited by hand, never reused.
      "gid":    "1217546089213421",          # Asana task gid
      "url":    "https://app.asana.com/...", # permalink — becomes the row's link
      "title":  "Highlighted words collide with the next word",
                  # YOUR words, not the Marker.io title. Marker's titles are
                  # AI-generated and unreliable; never surface them to anyone.
      "page":   "Home — / (page 393)",       # free text; the bit before an em
                  # dash or backtick becomes the filter option
      "status": "shipped",                   # shipped | ready | waiting | partial | blocked
      "asked":  "What the client actually wants, from description + screenshot",
      "did":    "What was done, in NON-TECHNICAL words the client would read",
                  # optional, but it is THE client-facing field — see THE DIVIDER
                  # IS A CLIENT/INTERNAL BOUNDARY below. It NEVER falls back to
                  # `plan`; when it is absent the panel prints a one-line
                  # placeholder above the divider instead. `done` is a legacy alias.
      "plan":   "The technical plan — as long and as detailed as you like",
                  # INTERNAL. Always renders BELOW the divider, never above it.
      "shot":   "What the red ink actually marks — be specific about WHERE",
      "q":      "The single exact question for the client",
                  # optional, ANY status. Non-empty `q` puts the ticket in the
                  # navbar's "Has a question for the client" filter, which is the
                  # highest-value view in the report: it is what you owe them.
      "page_url": "https://staging.example.com/about/"
                  # the page the ticket is about. Marker.io puts this in every
                  # ticket description as "Source URL:" — pull it out with
                  #   re.search(r'Source URL:\s*(\S+)', task['notes'])
                  # Optional: the row just drops that icon if it is absent.
    }

TICKET NUMBERS. `num` is the small integer people actually use to refer to a
ticket — out loud on a call, in a commit message, in an Asana comment, in a
client email. Two properties make that usable, and both were learned the hard
way:

  * **Unique across the whole report.** Until 2026-08-21 rows were numbered per
    section, restarting at 1 in each, so "number 3" named four different
    tickets. Chris reversed that on 2026-08-21: a number that needs a section
    name beside it to mean anything is not a number, it is a coordinate.
  * **Assigned once, then stored.** It is a FIELD, not a render-time counter.
    Anything derived from list position changes the moment a ticket is added,
    deleted or re-sectioned — and every reference that has already left the
    building silently starts pointing at a different ticket. That is why this
    script WRITES numbers back into the input JSON instead of computing them.

The script gives a number to any ticket lacking one (the next integers above the
high-water mark, in file order), reports how many it assigned, and saves the
file. It **refuses to build on a duplicate** and names both tickets. The
high-water mark reads the sibling `asana-qa-triage*.json` rounds in the same
folder AND a two-line `asana-qa-triage.seq` ratchet file it maintains there, so a
deleted ticket's number retires with it rather than being handed to someone
else. Commit the .seq file with the JSON. `--no-number-writeback` renders
without touching the JSON — the numbers it would have assigned are still used in
that build.

A ticket does NOT need an Asana task. Plenty are raised inline in a chat with
Chris and go straight into this JSON: leave `gid`, `url` (and `page_url` if
there is no one page) out entirely. The row simply drops those icons and the
detail panel's Ticket cell reads "—". Numbering is identical either way.

STATUS VOCABULARY vs THE FOUR BUCKETS. The navbar always shows exactly four
consolidated counts — Done (green), Ready (blue), Waiting (yellow), Blocked
(red). A site may add sub-statuses, but **only Waiting takes them**: anything
that is neither shipped, nor startable, nor hard-blocked is somebody-waiting-on-
somebody. `partial` is the shipped example — it rolls up into Waiting in the
navbar while still rendering as its own "Partly shipped" section in the list.

Status is deliberately NOT the same axis as confidence. Before any work, grade
by confidence (can we do this without asking?) and map A->ready, B->waiting,
C->blocked. After a pass, regenerate with what actually happened — a report
still claiming "ready to ship" for shipped work is worse than no report, because
it reads as current.

THE DIVIDER IS A CLIENT/INTERNAL BOUNDARY (Chris, 2026-08-21). These reports go
to clients. The rule in the detail panel is not decoration — it is the line
between what the client reads and what we read, and it now says so ("internal
detail below").

  * ABOVE it, client-readable ONLY: the Question, the Request, and What was
    done. **No file paths, no CSS properties or class names, no block names, no
    media IDs, no page IDs, no commit hashes.**
  * BELOW it, internal: Screenshot details and The technical plan, as detailed
    as they like.

Only two fields render above the line, and they are the two you have to write
for a human who has never seen the codebase:

  * `asked` — what the client asked for.
  * `did` — **the client-facing field.** What was done, in the words you would
    say to them on the phone.

`plan` is **the internal field** and is rendered only below the divider. There
used to be a fallback that printed `plan` under "What we will do" whenever `did`
was missing; that fallback was the leak — it put "re-cut media 420 as a
transparent PNG", `background-image`, `gray-40` and `assets/blocks.css` straight
into the client's half of the panel. **It is gone. Do not restore it.** A ticket
with no `did` prints a quiet one-line placeholder above the line instead; an
honest gap is better than a leak, and `plan` still renders in full below.

The failure mode to watch for is not the missing `did` — it is the `did` written
as a summary of the diff. If it names a file, a selector or an ID, it belongs in
`plan`; rewrite it.
"""
import argparse, collections, datetime, html, json, os, re, sys

# ---- the four consolidated buckets. Fixed labels, fixed colours, always all
# four in the navbar even at zero. Sites do not get to add a fifth. ----
BUCKETS = ["done", "ready", "waiting", "blocked"]
BUCKET_LABEL = {"done": "Done", "ready": "Ready",
                "waiting": "Waiting", "blocked": "Blocked"}
BUCKET_CLS = {"done": "done", "ready": "redy", "waiting": "wait", "blocked": "blok"}

# ---- the ticket-level vocabulary. Each entry: css class, section heading,
# section blurb, and the bucket it rolls up into. Extra site-specific statuses
# may be added here, but their bucket must be "waiting". ----
STATUS = {
    "shipped": ("done", "Shipped", "done",
        "Live, commented back to the client, and set to Client QA. Nothing here is "
        "marked complete — they await the client's review."),
    "ready": ("redy", "Ready to start", "ready",
        "Understood, costed, and unblocked. Nothing stands between these and the work."),
    "partial": ("part", "Partly shipped", "waiting",
        "The mechanical half is live; the rest needs an answer. Left open deliberately."),
    "waiting": ("wait", "Waiting on one answer", "waiting",
        "Each of these is understood and costed. One question stands between it and the work."),
    "blocked": ("blok", "Blocked", "blocked",
        "Needs something only the client can supply — most often a file that expired "
        "before we could download it."),
}
ORDER = ["shipped", "ready", "partial", "waiting", "blocked"]

# The current build always lives at this un-dated name; everything else in the
# folder matching BUILD_RE is an archived build.
CANON = "asana-qa-triage.html"
BUILD_RE = re.compile(r"^asana-qa-triage-(\d{4}-\d{2}-\d{2})\.html$")
ISO_RE = re.compile(r"^\d{4}-\d{2}-\d{2}$")
STAMP_RE = re.compile(r'<meta name="qa-build-date" content="(\d{4}-\d{2}-\d{2})"')

# Marked regions, so an archived copy can be rewritten in place without
# re-rendering it (we no longer have the JSON that produced it).
DT_OPEN, DT_CLOSE = "<!--dtsel-->", "<!--/dtsel-->"
SUP_OPEN, SUP_CLOSE = "<!--sup-->", "<!--/sup-->"
REGION_RE = {
    "dt": re.compile(re.escape(DT_OPEN) + r".*?" + re.escape(DT_CLOSE), re.S),
    "sup": re.compile(re.escape(SUP_OPEN) + r".*?" + re.escape(SUP_CLOSE), re.S),
}

# Hand-drawn so the page stays self-contained — these files are committed as
# standalone documents and opened over file://, where an icon font or a CDN
# sprite renders as nothing at all.
ICON_EXTERNAL = (
    '<svg viewBox="0 0 16 16" width="13" height="13" aria-hidden="true" focusable="false">'
    '<path d="M6.6 3H3.75A1.75 1.75 0 0 0 2 4.75v7.5C2 13.2 2.8 14 3.75 14h7.5A1.75 1.75 0 0 0 13 12.25V9.4" '
    'fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'
    '<path d="M9.6 2.4H13.6V6.4" fill="none" stroke="currentColor" stroke-width="1.5" '
    'stroke-linecap="round" stroke-linejoin="round"/>'
    '<path d="M13.6 2.4 7.7 8.3" fill="none" stroke="currentColor" stroke-width="1.5" '
    'stroke-linecap="round"/></svg>')
# Asana's mark is three dots in a triangle.
ICON_ASANA = (
    '<svg viewBox="0 0 16 16" width="13" height="13" aria-hidden="true" focusable="false">'
    '<circle cx="8" cy="4.3" r="2.55" fill="currentColor"/>'
    '<circle cx="3.7" cy="11.3" r="2.55" fill="currentColor"/>'
    '<circle cx="12.3" cy="11.3" r="2.55" fill="currentColor"/></svg>')

CHROME = ("Site chrome — nav", "Site chrome — footer", "Every page")

# The permanent ticket number lives in this field. Sibling rounds in the same
# folder are scanned for it too, so a retired number is never handed out twice.
NUM_KEY = "num"
JSON_SIBS = re.compile(r"^asana-qa-triage.*\.json$")
# The high-water mark, kept beside the report. It is what makes a number RETIRE
# with the ticket that had it: without it, deleting the highest-numbered ticket
# would hand its number to the next one added. Two lines of plain text, meant to
# be committed with the JSON; if it goes missing the script rebuilds it from the
# files it can see and only the very top of the range is at risk.
SEQ_FILE = "asana-qa-triage.seq"
SEQ_NOTE = ("# Highest QA ticket number ever assigned in this folder. Retired numbers are\n"
            "# never re-used. Written by classic-city-core/scripts/qa/asana-triage-report.py\n"
            "# — commit it with the JSON; do not edit by hand.\n")

# The page dropdown's first option. Not a page at all — it cuts across every
# page and selects the tickets that owe the client an answer.
Q_VALUE = "__q__"
# The little marker on a list row that carries a question. Same amber as Waiting.
QFLAG = ('<span class="qflag" title="Has a question for the client" '
         'aria-label="Has a question for the client">?</span>')

# The divider is the client/internal boundary (see the module docstring), so it
# is LABELLED rather than implied — a bare <hr> read as decoration, and the next
# person to write a `did` needs to see that there are two halves. Deliberately
# quiet: faint, small caps, a hairline either side.
DIVIDER = ('<div class="pdiv" role="separator" aria-label="Internal detail below">'
           '<span>internal detail below</span></div>')
# What prints above the line when a ticket has no `did`. The old behaviour was to
# print `plan` here, which leaked file paths and CSS class names into the
# client's half of the panel. An honest one-liner beats that every time.
PLACEHOLDER_DID = ('<span class="todo">Plain-English summary still to be written '
                   '&mdash; the technical detail is below the line.</span>')


def st(t):
    """The ticket's vocabulary entry, defaulting to waiting."""
    return STATUS[t.get("status", "waiting")]


def has_q(t):
    return bool((t.get("q") or "").strip())


def parse_num(v):
    """The stored ticket number as an int, or None when absent.

    A digit STRING is accepted and normalised: `12` and `"12"` must never be able
    to sit in the file as two different tickets, which is precisely the duplicate
    this field exists to prevent."""
    if v is None or v == "":
        return None
    if isinstance(v, bool):
        raise ValueError(f"{v!r} is not a ticket number")
    if isinstance(v, int):
        n = v
    elif isinstance(v, float) and v.is_integer():
        n = int(v)
    elif isinstance(v, str) and v.strip().isdigit():
        n = int(v.strip())
    else:
        raise ValueError(f"{v!r} is not a ticket number")
    if n < 1:
        raise ValueError(f"{n} is not a ticket number — they start at 1")
    return n


def num_of(t):
    """The ticket's number. Never falls back to a position — a missing number is
    assigned by number_tickets() before anything renders, so by render time every
    ticket has one."""
    return parse_num(t.get(NUM_KEY))


def _quiet_num(t):
    try:
        return num_of(t)
    except (ValueError, AttributeError):
        return None


def high_water(tickets, path):
    """The largest number ever handed out for this report, so a deleted ticket's
    number retires with it instead of being recycled onto a different ticket.

    Three sources, all of them committed alongside the report and all read, so the
    answer is the largest of:
      * the numbers in THIS file;
      * the numbers in every sibling `asana-qa-triage*.json` — the previous
        rounds' state files, which are kept beside the current one;
      * `asana-qa-triage.seq`, a two-line text file holding the mark itself.
    The .seq file is what covers the case the other two cannot: delete the
    highest-numbered ticket from the only JSON there is, and nothing left on disk
    remembers that the number was ever used. Lose the .seq file and the script
    rebuilds what it can from the JSONs — a degradation, not a failure."""
    top = max([n for n in (_quiet_num(t) for t in tickets) if n] or [0])
    folder = os.path.dirname(os.path.abspath(path))
    me = os.path.abspath(path)
    top = max(top, read_seq(folder))
    try:
        names = sorted(os.listdir(folder))
    except OSError:
        names = []
    for name in names:
        full = os.path.join(folder, name)
        if not JSON_SIBS.match(name) or full == me:
            continue
        try:
            with open(full, encoding="utf-8") as fh:
                other = json.load(fh)
        except (OSError, ValueError):
            continue
        if not isinstance(other, list):
            continue
        for t in other:
            if isinstance(t, dict):
                top = max(top, _quiet_num(t) or 0)
    return top


def read_seq(folder):
    """The stored high-water mark, or 0 if there is no file / it is unreadable."""
    try:
        with open(os.path.join(folder, SEQ_FILE), encoding="utf-8") as fh:
            m = re.search(r"^\s*(\d+)\s*$", fh.read(), re.M)
    except OSError:
        return 0
    return int(m.group(1)) if m else 0


def write_seq(folder, top):
    """Ratchet the stored high-water mark up. It never goes down — that is the
    whole job."""
    if top <= read_seq(folder):
        return
    try:
        with open(os.path.join(folder, SEQ_FILE), "w", encoding="utf-8") as fh:
            fh.write(SEQ_NOTE + f"{top}\n")
    except OSError:
        pass          # a lost ratchet degrades, it does not break the build


def duplicate_numbers(tickets):
    """Every number carried by more than one ticket, as (num, first_i, other_i).
    A silent duplicate is the whole bug — the caller exits non-zero on any."""
    seen, dupes = {}, []
    for i, t in enumerate(tickets):
        n = _quiet_num(t)
        if n is None:
            continue
        if n in seen:
            dupes.append((n, seen[n], i))
        else:
            seen[n] = i
    return dupes


def json_style(raw):
    """Match the input file's own formatting on write-back, so adding one field
    is a one-line diff per ticket and not a whole-file reformat."""
    m = re.match(r"^\s*\[\s*\n( +)\S", raw)
    indent = len(m.group(1)) if m else 2
    return indent, not any(ord(c) > 127 for c in raw)


def number_tickets(tickets, path, raw, write=True):
    """Give every ticket a permanent number and persist it.

    Tickets that already carry one keep it — that is the point; a number in a
    commit message or a client email has to still resolve to the same ticket
    next round. The rest take the next integers above the high-water mark, in
    file order, and the file is rewritten in place with `num` first in each
    object. Returns (count_assigned, note_for_the_operator)."""
    folder = os.path.dirname(os.path.abspath(path))
    missing = [t for t in tickets if _quiet_num(t) is None]
    have = [n for n in (_quiet_num(t) for t in tickets) if n]
    if not missing:
        if write and have:
            write_seq(folder, max(have))
        span = f" ({min(have)}\u2013{max(have)})" if have else ""
        return 0, f"all {len(tickets)} tickets already numbered{span}; nothing to assign"
    nxt = high_water(tickets, path) + 1
    first = nxt
    for t in missing:
        t[NUM_KEY] = nxt
        nxt += 1
    # `num` reads first in each object — it is the ticket's identity.
    for i, t in enumerate(tickets):
        tickets[i] = dict([(NUM_KEY, t[NUM_KEY])] +
                          [(k, v) for k, v in t.items() if k != NUM_KEY])
    span = f"{first}" if len(missing) == 1 else f"{first}\u2013{nxt - 1}"
    if not write:
        return len(missing), (f"would assign {len(missing)} number(s) ({span}) \u2014 "
                              f"--no-number-writeback, so {os.path.basename(path)} "
                              "was NOT changed")
    write_seq(folder, nxt - 1)
    indent, ascii_only = json_style(raw)
    body = json.dumps(tickets, indent=indent, ensure_ascii=ascii_only)
    if raw.endswith("\n"):
        body += "\n"
    with open(path, "w", encoding="utf-8") as fh:
        fh.write(body)
    return len(missing), (f"assigned {len(missing)} new ticket number(s) ({span}) and wrote "
                          f"them back to {os.path.basename(path)}")


def did_of(t):
    """The non-technical 'what was done' — the ONLY account of the work that is
    allowed above the divider, because above the divider is the client's half of
    the panel. It never falls back to `plan`; see THE DIVIDER IS A CLIENT/
    INTERNAL BOUNDARY in the module docstring. `done` is a legacy alias —
    trialport's JSON already carried it under that name before `did` was
    specified."""
    for k in ("did", "done"):
        v = t.get(k)
        if isinstance(v, str) and v.strip():
            return v
    return ""


def page_of(t):
    """Filter label for a ticket. Site chrome first — a nav or footer ticket carries
    the Source URL of whatever page the client happened to be on when they filed
    it, so grouping those by URL files them under a page they are not about."""
    p = (t.get("page") or "").strip()
    low = p.lower()
    for needle, label in (("header.html", "Site chrome — nav"),
                          ("nav", "Site chrome — nav"),
                          ("footer", "Site chrome — footer"),
                          ("all pages", "Every page"),   # NOT "All pages" — that
                          ("site-wide", "Every page")):  # is the reset option's label
        if needle in low:
            return label
    # "Reported against Blog (524). Directly concerns Insights (514)." and friends:
    # take the page the ticket is ABOUT, which is the last one named.
    p = re.sub(r"^(reported against|raised on|marked on)\s+", "", p, flags=re.I)
    name = re.split(r"—|`|\(|\.", p)[0].strip(" .,")
    return name or "Unfiled"


def rich(text, client=False):
    """Escape, then honour the small subset of markdown the agents actually emit.

    ``` fences are handled FIRST and are not code. Agents writing `asked` use them
    to hold the client's own verbatim replacement copy — 34 of trialport's 97 did
    — and the inline-backtick rule alone chewed those into a stray `` , a
    monospace slab and another `` , in the client's half of the panel. They are
    prose: they render as an indented quotation.

    `client=True` for anything ABOVE the divider. There, a single-backtick span
    is NOT set as code: what it holds up there is a menu label, a product name, a
    brand hex the client typed himself — his words, not ours — and a slab of
    monospace tells him he is looking at machinery. It renders as an italic
    literal instead. Below the line, <code> is exactly right and stays."""
    out = html.escape(text or "")
    out = re.sub(r"```[ \t]*\r?\n?(.+?)\r?\n?[ \t]*```",
                 r'<span class="verb">\1</span>', out, flags=re.S)
    if client:
        out = re.sub(r"`([^`]+)`", r'<span class="lit">\1</span>', out)
    else:
        out = re.sub(r"`([^`]+)`", r"<code>\1</code>", out)
    return re.sub(r"\*\*([^*]+)\*\*", r"<strong>\1</strong>", out)


# ---- the above-the-divider leak lint. Advisory: it prints, it never refuses.
# The generator can guarantee that `plan` stays below the line; it cannot stop
# someone writing `did` as a summary of the diff, and that is the way this rule
# actually gets broken. So every field that renders ABOVE the divider is checked
# for the things that must never appear there, and the build says so out loud.
# `did` supersedes the legacy `done`, so only ONE of them ever reaches the
# client. Warning about the other trains people to ignore the warning, which is
# the one failure mode an advisory lint cannot survive — so check what renders.
LEAK_FIELDS = ("asked", "q")


def leak_fields_of(t):
    """The fields that actually print above the divider for THIS ticket."""
    summary = "did" if str(t.get("did", "")).strip() else "done"
    return LEAK_FIELDS + (summary,)
LEAKS = [
    (re.compile(r"\.(css|scss|php|jsx?|tsx?|json|html?|jpe?g|png|svg|webp|woff2?)\b", re.I),
     "file name"),
    (re.compile(r"\b(wp-content|wp-admin|wp-includes|classic-city-core|blocks\.css)\b", re.I),
     "file path"),
    (re.compile(r"\bsg-[a-z0-9]+[/\\]|\bsg-block[a-z0-9-]*|\.sg-[a-z0-9-]+", re.I),
     "block or theme name"),
    (re.compile(r"--[a-z]+-[a-z0-9-]+|\bgray-\d+\b", re.I), "CSS token"),
    (re.compile(r"\b(media|attachment|image|post|page|id)\s+#?\d{2,}\b", re.I), "internal ID"),
    (re.compile(r"\b\d{3,5}\s*\((?=[^)]*\.(?:jpe?g|png|svg|webp))", re.I), "media ID"),
    (re.compile(r"\b(?=[0-9a-f]*\d)(?=[0-9a-f]*[a-f])[0-9a-f]{7,40}\b"), "commit hash"),
]


def build_stamp():
    """"August 21, 2026 at 4:00PM ET" — wall-clock at build time, not --date.

    --date is the ROUND's date and is deliberately day-only; this is when the
    file was last written, which is the only thing the footer is for. Eastern
    because that is where Chris is, and it is spelled out so nobody has to work
    out whose midnight it is. Falls back to naive local time if the tz database
    is missing rather than failing the build over a footer.
    """
    now = datetime.datetime.now()
    label = "ET"
    try:
        from zoneinfo import ZoneInfo
        now = datetime.datetime.now(ZoneInfo("America/New_York"))
        # "ET", not the %Z abbreviation. EDT/EST is more precise and nobody
        # wants it — Chris asked for ET and that is how people write it.
    except Exception:
        pass
    hour = now.hour % 12 or 12
    ampm = "AM" if now.hour < 12 else "PM"
    return f"{now.strftime('%B')} {now.day}, {now.year} at {hour}:{now.minute:02d}{ampm} {label}"


def leak_check(tickets):
    """Every (num, field, what, snippet) that would print internals above the
    divider. Client-facing half only — `plan` and `shot` are exempt by design."""
    out = []
    for t in tickets:
        for f in leak_fields_of(t):
            v = t.get(f)
            if not isinstance(v, str) or not v.strip():
                continue
            for rx, what in LEAKS:
                m = rx.search(v)
                if m:
                    s = max(0, m.start() - 28)
                    snip = re.sub(r"\s+", " ", v[s:m.end() + 28]).strip()
                    out.append((_quiet_num(t), f, what, snip))
                    break
    return out


def links_of(t):
    """The two icon links in a list row. Icon-only — there is no width for words."""
    out = ""
    for key, icon, label in (("page_url", ICON_EXTERNAL, "Open the page"),
                             ("url", ICON_ASANA, "Open in Asana")):
        if not t.get(key):
            continue
        out += (f'<a class="ic" href="{html.escape(t[key])}" target="_blank" '
                f'rel="noopener" title="{label}">{icon}<span class="sr">{label}</span></a>')
    return out


def row(t, n, i):
    """The left-column list row. `n` is the ticket's permanent `num` — unique
    across the WHOLE report, not a per-section counter (Chris, 2026-08-21,
    reversing the 2026-08-19 per-section scheme: four rows all numbered 3 made
    the number useless the moment it left the page). It is also not the Marker
    ref or the Asana gid — those mean nothing to a reader and plenty of tickets
    have neither, having been raised in a chat.

    The row is a wrapper holding a real <button> plus the two links as SIBLINGS of
    it — a link nested inside a button is invalid and browsers drop it, which is
    also why the old stopPropagation hack is gone."""
    return (f'<div class="tk t-{st(t)[0]}" data-page="{html.escape(page_of(t))}" '
            f'data-q="{1 if has_q(t) else 0}">'
            f'<button type="button" class="tkmain" id="tb{i}" aria-controls="tp{i}" '
            f'aria-pressed="false" tabindex="-1">'
            # No status dot: the row's 3px coloured left bar already carries the
            # status, and two of them side by side was one too many (2026-08-21).
            f'<span class="num">{n}</span>'
            f'<span class="ti">{rich(t["title"], client=True)}</span>{QFLAG if has_q(t) else ""}'
            f'</button>'
            f'<span class="meta">{links_of(t)}</span></div>')


def meta_cell(label, value):
    return f'<div class="mc"><span class="ml">{label}</span>{value}</div>'


def pane(t, n, i):
    """The right-column detail panel for one ticket. Only one is ever visible.

    `n` is the ticket's permanent `num`, and it is the first cell in the meta grid
    because it is the thing people say out loud.

    Body order is Chris's spec, 2026-08-21, and it is an order of decreasing
    urgency: the question the client owes an answer to, then what they asked for,
    then a plain-English account of what was done — then a labelled rule, and
    below it the long technical material nobody reads on a call.

    That rule is a HARD BOUNDARY, not a visual break: everything above it is
    written for the client and must contain no file path, CSS class, block name,
    media ID or page ID; everything below it is ours. `plan` therefore renders
    only below it, with no fallback above. See the module docstring."""
    cls, label, bucket, _ = st(t)
    number = f'<span class="mv">{n}</span>'
    status = (f'<span class="mv sv" title="{html.escape(label)}">'
              f'{BUCKET_LABEL[bucket]}</span>')
    pg = html.escape(page_of(t))
    if t.get("page_url"):
        page = (f'<a class="mv" href="{html.escape(t["page_url"])}" target="_blank" '
                f'rel="noopener" title="{pg}"><span class="tx">{pg}</span>{ICON_EXTERNAL}</a>')
    else:
        page = f'<span class="mv" title="{pg}"><span class="tx">{pg}</span></span>'
    if t.get("url"):
        ticket = (f'<a class="mv" href="{html.escape(t["url"])}" target="_blank" '
                  f'rel="noopener" title="Open in Asana"><span class="tx">Asana</span>'
                  f'{ICON_EXTERNAL}</a>')
    else:
        # No Asana task — raised inline in a chat. An icon here would be a dead
        # link, so there is no icon and the cell says so on hover.
        ticket = ('<span class="mv dimv" title="Raised in chat &mdash; no Asana task">'
                  '<span class="tx">&mdash;</span></span>')

    q = ""
    if has_q(t):
        q = (f'<div class="q"><span class="qlab">Question for the client</span>'
             f'<p>{rich(t["q"], client=True)}</p></div>')

    did, plan = did_of(t), (t.get("plan") or "").strip()
    did_label = ("What was done" if (did or t.get("status") in ("shipped", "partial"))
                 else "What we will do")
    # `plan` NEVER renders above the divider — that half of the panel is the
    # client's. There is no fallback: a ticket with no `did` gets the placeholder,
    # and `plan` renders below the line in full, as it always does.
    body_did = rich(did, client=True) if did else PLACEHOLDER_DID

    top = f'<dt>Request</dt><dd>{rich(t["asked"], client=True)}</dd>' if t.get("asked") else ""
    top += f'<dt>{did_label}</dt><dd>{body_did}</dd>'
    bot = "".join([f'<dt>{k}</dt><dd>{rich(v)}</dd>' for k, v in
                   (("Screenshot details", t.get("shot")),
                    ("The technical plan", plan)) if v])
    body = f'<dl class="pd">{top}</dl>' if top else ""
    if bot:
        body += DIVIDER + f'<dl class="pd">{bot}</dl>'
    return (f'<article class="pane t-{cls}" id="tp{i}" aria-labelledby="tb{i}" hidden>'
            f'<h3 class="ptitle">{rich(t["title"], client=True)}</h3>'
            f'<div class="pmeta">{meta_cell("No.", number)}'
            f'{meta_cell("Status", status)}{meta_cell("Page", page)}'
            f'{meta_cell("Ticket", ticket)}</div>'
            f'{q}{body}</article>')


CSS = """<style>
:root{--bg:#f7f6f9;--surf:#fff;--line:#e2dfe8;--ink:#191622;--dim:#615c70;--faint:#8b8699;
--acc:#0d7f99;
--done:#1f6b4a;--done-b:#e6f2ec;--redy:#1d5fb3;--redy-b:#e7eefb;
--wait:#8a5a06;--wait-b:#fbf1de;--blok:#a8324f;--blok-b:#fbebee;
--navh:54px;
--mono:ui-monospace,"SF Mono",Menlo,Consolas,monospace;
--serif:"Iowan Old Style","Palatino Linotype",Palatino,Charter,Georgia,serif;
--sans:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;}
@media(prefers-color-scheme:dark){:root:not([data-theme="light"]){--bg:#131019;--surf:#1c1826;
--line:#2e2939;--ink:#ece9f2;--dim:#a49eb4;--faint:#7a7389;--acc:#04D9FF;
--done:#57c894;--done-b:#16281f;--redy:#7cb2ff;--redy-b:#141d2e;
--wait:#e8a94a;--wait-b:#2b2113;--blok:#f2779a;--blok-b:#2c1620;}}
:root[data-theme="dark"]{--bg:#131019;--surf:#1c1826;--line:#2e2939;--ink:#ece9f2;--dim:#a49eb4;
--faint:#7a7389;--acc:#04D9FF;--done:#57c894;--done-b:#16281f;--redy:#7cb2ff;--redy-b:#141d2e;
--wait:#e8a94a;--wait-b:#2b2113;--blok:#f2779a;--blok-b:#2c1620;}
*{box-sizing:border-box}
/* Do NOT put overflow-x:hidden on html or body here. It makes body the nearest
   scrolling ancestor for position:sticky, which pushes the detail panel down by
   the body padding and then lets it scroll away entirely. Horizontal overflow is
   prevented by min-width:0 on the grid tracks instead. Verified 2026-08-21. */
body{background:var(--bg);color:var(--ink);font:16px/1.6 var(--sans);margin:0;
padding:var(--navh) 0 0}
.wrap{width:100%;margin:0}

/* ---- fixed navbar. It now carries EVERYTHING: name, the four consolidated
   counts, and the two dropdown cells. There is no separate strip below it.
   position:fixed inside an embedding iframe resolves against the iframe
   viewport, which is what we want; body padding-top keeps the first rows out
   from under it. ---- */
.nav{position:fixed;top:0;left:0;right:0;height:var(--navh);z-index:100;
background:var(--surf);border-bottom:1px solid var(--line);
display:flex;align-items:center;gap:0;padding:0 0 0 20px}
/* The navbar name is the document's only h1 — Chris's call, 2026-08-21: the page
   title duplicated it, so the h1 moved up here rather than leaving the document
   headingless. Section <h2>s sit under it, pane titles are <h3>. */
h1.brand{font:600 19px/1 var(--serif);letter-spacing:-.01em;white-space:nowrap;
overflow:hidden;text-overflow:ellipsis;flex:0 1 auto;min-width:0;margin:0;padding-right:18px}
/* The counts are CONTROLS, and they are built exactly like the dropdown cells on
   the right: full-height, a 1px rule on the left edge (so there is a rule between
   the brand and the first, and between each pair), no box of their own. No pill,
   no tint, no border. Only the NUMBER is coloured — four coloured words in a row
   read as decoration and stop meaning anything. */
.counts{display:flex;align-items:stretch;height:100%;flex:0 1 auto;min-width:0}
.ct{display:inline-flex;align-items:center;gap:5px;white-space:nowrap;height:100%;
border:0;border-left:1px solid var(--line);border-radius:0;background:none;
padding:0 13px;font:inherit;color:inherit;cursor:pointer}
.ct:hover:not([disabled]){background:var(--bg)}
.ct:focus-visible{outline:2px solid var(--acc);outline-offset:-2px}
.ct[disabled]{cursor:default}
.ct b{font:600 14px/1 var(--mono);color:var(--c);font-variant-numeric:tabular-nums}
.ct i{font:600 10.5px var(--sans);font-style:normal;letter-spacing:.05em;
text-transform:uppercase;color:var(--ink)}
.ct.zero{opacity:.42}
.c-done{--c:var(--done)}.c-redy{--c:var(--redy)}
.c-wait{--c:var(--wait)}.c-blok{--c:var(--blok)}
.navr{margin-left:auto;display:flex;align-items:stretch;height:100%;min-width:0;flex:0 1 auto}
/* Each dropdown is a full-height CELL with a divider on its left edge (so there
   is a rule before the first one and between the two). The <select> has no box
   of its own and fills the cell, which makes the whole cell the hit target and
   makes both dropdowns identical by construction. */
.navcell{position:relative;display:flex;align-items:center;height:100%;
border-left:1px solid var(--line);min-width:0;flex:0 1 auto}
.navcell:hover,.navcell:focus-within{background:var(--bg)}
.navcell::after{content:"";position:absolute;right:13px;top:50%;width:7px;height:7px;
border-right:1.6px solid var(--faint);border-bottom:1.6px solid var(--faint);
transform:translateY(-70%) rotate(45deg);pointer-events:none}
.navsel{appearance:none;-webkit-appearance:none;height:100%;width:100%;
max-width:min(310px,30vw);min-width:0;border:0;border-radius:0;background:transparent;
color:var(--ink);font:500 13px var(--sans);padding:0 32px 0 14px;cursor:pointer;
text-overflow:ellipsis}
.navsel:focus-visible{outline:2px solid var(--acc);outline-offset:-2px}
/* The counts SHRINK before they lose their words — four bare coloured numbers
   are unreadable without the labels. Only under ~700px, where nothing else fits,
   do the words go (the colour, the title and the sections below still carry it). */
@media(max-width:1080px){
h1.brand{font-size:17px;padding-right:12px}
.nav{padding-left:14px}
.ct{padding:0 9px;gap:4px}
.ct b{font-size:13px}
.ct i{font-size:9px;letter-spacing:.03em}
.navsel{max-width:min(230px,26vw);padding:0 26px 0 11px;font-size:12.5px}
.navcell::after{right:10px}}
@media(max-width:700px){.nav{padding-left:10px}.ct i{display:none}
.ct{padding:0 7px}h1.brand{padding-right:9px}
.navsel{max-width:33vw;padding:0 22px 0 9px}.navcell::after{right:7px}}

/* ---- 65 / 35 master-detail. gap:0 — the right panel is flush to the navbar,
   to the bottom of the viewport and to the right edge; its only edge is the
   1px rule on its left. ---- */
.cols{display:grid;grid-template-columns:65fr 35fr;gap:0;align-items:start}
.col{min-width:0}
.left{padding:4px 20px 56px}
.right{position:sticky;top:var(--navh);height:calc(100vh - var(--navh));
overflow:auto;overscroll-behavior:contain;background:var(--surf);
border:0;border-left:1px solid var(--line);border-radius:0}
/* Under ~900px the columns stack rather than crush. The panel goes ABOVE the
   list and stays stuck flush to the navbar — offset by even a few pixels and the
   list is seen sliding through the gap. Full viewport height is not available
   here without hiding the list entirely, so it takes 58vh. */
@media(max-width:900px){
.cols{grid-template-columns:1fr}
.right{order:-1;height:58vh;border-left:0;border-bottom:1px solid var(--line)}
.left{padding:4px 14px 56px}
}
.supersede{background:var(--wait-b);border:1px solid var(--wait);border-left:3px solid var(--wait);
border-radius:3px;color:var(--wait);padding:10px 14px;margin:14px 0 0;font-size:13.5px}
.supersede a{color:inherit}
.showing{font-size:12.5px;color:var(--faint);margin:16px 0 -16px;min-height:1px}
.sec{margin:32px 0 0}.sec[hidden]{display:none}.sh{margin:0 0 12px}
.sh h2{font:600 19px/1.2 var(--serif);margin:0;display:flex;align-items:baseline;gap:9px}
.sh .n{font:600 15px var(--mono);color:var(--surf);background:var(--sc);border-radius:3px;
padding:2px 8px;font-variant-numeric:tabular-nums}
.sh p{margin:5px 0 0;color:var(--faint);font-size:13.5px;max-width:70ch}
.s-done{--sc:var(--done)}.s-redy{--sc:var(--redy)}.s-part{--sc:var(--wait)}
.s-wait{--sc:var(--wait)}.s-blok{--sc:var(--blok)}
.list{display:flex;flex-direction:column;gap:5px}
.tk{display:flex;align-items:center;gap:7px;padding-right:11px;
background:var(--surf);border:1px solid var(--line);border-radius:3px;border-left:3px solid var(--tc)}
.t-done{--tc:var(--done);--tb:var(--done-b)}.t-redy{--tc:var(--redy);--tb:var(--redy-b)}
.t-part{--tc:var(--wait);--tb:var(--wait-b)}.t-wait{--tc:var(--wait);--tb:var(--wait-b)}
.t-blok{--tc:var(--blok);--tb:var(--blok-b)}
.tk[hidden]{display:none}
.tk:hover{border-color:var(--acc)}
.tk.is-sel{background:var(--tb);border-color:var(--acc);box-shadow:0 0 0 1px var(--acc)}
.tkmain{flex:1;min-width:0;display:flex;align-items:center;gap:11px;padding:11px 4px 11px 12px;
background:none;border:0;color:inherit;font:inherit;text-align:left;cursor:pointer}
.tkmain:focus-visible{outline:2px solid var(--acc);outline-offset:-2px}
/* Wide enough for three digits so the titles stay on one left edge — the numbers
   are report-wide now, not per-section, so a big round reaches into the 100s. */
.num{font:600 11.5px var(--mono);color:var(--faint);font-variant-numeric:tabular-nums;
flex:none;min-width:2.4em;text-align:right}
.ti{flex:1;font-size:14.5px;min-width:0;overflow-wrap:anywhere}
.qflag{flex:none;width:17px;height:17px;border-radius:50%;background:var(--wait-b);
color:var(--wait);border:1px solid var(--wait);font:700 11px/15px var(--sans);text-align:center}
.meta{display:flex;gap:7px;flex:none;align-items:center}
.ic{display:inline-flex;align-items:center;justify-content:center;width:26px;height:24px;
border:1px solid var(--line);border-radius:3px;color:var(--faint);background:var(--bg);
text-decoration:none;flex:none}
.ic:hover{color:var(--acc);border-color:var(--acc)}
.ic:focus-visible{outline:2px solid var(--acc);outline-offset:1px}
.ic svg{display:block}
.sr{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;
clip:rect(0 0 0 0);white-space:nowrap;border:0}

/* ---- detail panel. 35% is narrow, so everything in here wraps hard: long URLs,
   code spans and screenshot descriptions must never widen the column. The whole
   panel is one scrolling column — nothing inside it is pinned. ---- */
.pane{padding:20px 20px 48px;overflow-wrap:anywhere}
.pane[hidden]{display:none}
.ptitle{font:600 18px/1.35 var(--serif);margin:0;text-wrap:pretty}
/* Four columns with rules between them: the number people say out loud, the
   consolidated status, the page, the ticket. 16/28/28/28 rather than four equal
   quarters — "No." is never more than three digits, and the quarter it used to
   take was stolen from Page, which is the one cell that actually needs the room
   (Chris, 2026-08-21). */
.pmeta{display:grid;grid-template-columns:16% 28% 28% 28%;width:100%;margin:15px 0 0;
border-top:1px solid var(--line);border-bottom:1px solid var(--line)}
.mc{min-width:0;padding:9px 10px;border-left:1px solid var(--line)}
.mc:first-child{border-left:0;padding-left:0}
.ml{display:block;font:600 9.5px var(--mono);letter-spacing:.09em;text-transform:uppercase;
color:var(--faint);margin:0 0 3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mv{display:flex;align-items:center;gap:4px;min-width:0;font:600 13px var(--sans);
color:var(--ink);text-decoration:none;font-variant-numeric:tabular-nums}
.mv .tx{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0}
.mv svg{flex:none}
a.mv{color:var(--acc)}
a.mv:hover .tx{text-decoration:underline}
.sv{color:var(--tc)}
.dimv{color:var(--faint)}
/* Chris's call, 2026-08-21: the sections used to run together. Every subheading
   now carries ~50% more air above it than it did. */
.q{background:var(--wait-b);border-left:3px solid var(--wait);border-radius:3px;
padding:14px 16px;margin:26px 0 0}
.qlab{font:600 10.5px var(--mono);letter-spacing:.09em;text-transform:uppercase;color:var(--wait)}
.q p{margin:6px 0 0;font-size:14.5px;color:var(--ink)}
.pd{margin:24px 0 0}
.pd dt{font:600 10.5px var(--mono);letter-spacing:.08em;text-transform:uppercase;
color:var(--faint);margin:21px 0 5px}
.pd dt:first-child{margin-top:0}
/* Body copy is --ink, the full-strength ink token. It was --dim, which is the
   secondary-text token and made the panel — the actual content of this report —
   read as an aside (Chris, 2026-08-21). --dim now does the job it is named for:
   the placeholder, and code spans. */
.pd dd{margin:0;font-size:14px;color:var(--ink)}
.pd dd strong{color:var(--ink);font-weight:600}
.todo{color:var(--dim);font-style:italic}
/* ``` fenced ``` in a field is the CLIENT'S OWN verbatim copy, not code. Quoted,
   not monospaced — a slab of mono in the client's half of the panel reads as
   something they are not meant to understand. */
.verb{display:block;margin:9px 0;padding:1px 0 1px 13px;border-left:2px solid var(--line);
color:var(--ink)}
/* A backtick span ABOVE the divider — a menu label, a product name, a hex the
   client typed. His words: italic, not monospace. <code> lives below the line. */
.lit{font-style:italic}
/* The labelled divider. It is the client/internal boundary, so it says so — but
   quietly: a hairline either side of small faint caps. */
.pdiv{display:flex;align-items:center;gap:10px;margin:28px 0 0;
font:600 9.5px var(--mono);letter-spacing:.09em;text-transform:uppercase;color:var(--faint)}
.pdiv::before,.pdiv::after{content:"";flex:1;height:1px;background:var(--line)}
.pdiv span{flex:none}
code{font:12.5px var(--mono);background:var(--bg);border:1px solid var(--faint);border-radius:2px;
padding:1px 4px;color:var(--dim);overflow-wrap:anywhere}
.foot{margin:48px 0 0;padding:20px 0 0;border-top:1px solid var(--line);
color:var(--faint);font-size:13px}
.foot p{margin:0 0 8px;max-width:70ch}
</style>"""

JS = """<script>
(function(){
var rows=[].slice.call(document.querySelectorAll('.tk'));
var panes=[].slice.call(document.querySelectorAll('.pane'));
var secs=[].slice.call(document.querySelectorAll('.sec'));
var panel=document.getElementById('panel');
var left=document.getElementById('left');
var pgsel=document.getElementById('pgsel');
var dtsel=document.getElementById('dtsel');
var showing=document.getElementById('showing');
var QV='__q__';
var cur=-1;
function btn(r){return r.querySelector('.tkmain');}
function vis(){return rows.filter(function(r){return !r.hidden;});}
// Master/detail: one pane visible at a time, roving tabindex on the rows so the
// list is one tab stop and not ninety.
function select(i,focus){
  if(i<0||i>=rows.length){return;}
  cur=i;
  rows.forEach(function(r,k){
    var on=(k===i),b=btn(r);
    r.classList.toggle('is-sel',on);
    b.setAttribute('aria-pressed',on?'true':'false');
    b.tabIndex=on?0:-1;
  });
  panes.forEach(function(p,k){p.hidden=(k!==i);});
  if(panel){panel.scrollTop=0;}
  if(focus){btn(rows[i]).focus();}
}
rows.forEach(function(r,i){btn(r).addEventListener('click',function(){select(i,false);});});
if(left){left.addEventListener('keydown',function(e){
  var k=e.key;
  if(k!=='ArrowDown'&&k!=='ArrowUp'&&k!=='Home'&&k!=='End'){return;}
  var v=vis();
  if(!v.length){return;}
  var at=cur<0?-1:v.indexOf(rows[cur]),nx;
  if(k==='Home'){nx=v[0];}
  else if(k==='End'){nx=v[v.length-1];}
  else if(at<0){nx=v[0];}
  else{nx=v[Math.min(v.length-1,Math.max(0,at+(k==='ArrowDown'?1:-1)))];}
  e.preventDefault();
  select(rows.indexOf(nx),true);
});}
// One dropdown, two kinds of filter: the question filter cuts across every page
// and is the reason the dropdown exists; the rest are page names.
function filter(){
  var f=pgsel?pgsel.value:'';
  rows.forEach(function(r){
    var ok=!f||(f===QV?r.getAttribute('data-q')==='1':r.getAttribute('data-page')===f);
    r.hidden=!ok;
  });
  secs.forEach(function(s){
    var n=s.querySelectorAll('.tk:not([hidden])').length,b=s.querySelector('.sh .n');
    s.hidden=!n;
    // With a filter on, "78 Shipped" above four rows is a lie; show what is shown.
    // Row numbers never change — they are the ticket's own permanent number, not
    // its position in whatever is currently visible.
    if(b){b.textContent=f?n:b.getAttribute('data-n');}
  });
  var v=vis();
  if(showing){
    var lab='';
    if(f&&pgsel.selectedIndex>=0){
      lab=pgsel.options[pgsel.selectedIndex].text.replace(/\\s*\\(\\d+\\)\\s*$/,'');
    }
    showing.textContent=f?('Showing '+v.length+' of '+rows.length+' tickets \\u2014 '+lab):'';
  }
  if(v.length&&(cur<0||rows[cur].hidden)){select(rows.indexOf(v[0]),false);}
}
if(pgsel){pgsel.addEventListener('change',filter);}
// The date options are sibling FILENAMES written at generation time; picking one
// just navigates to that file. The current build's option has an empty value.
if(dtsel){dtsel.addEventListener('change',function(){
  if(dtsel.value){location.href=dtsel.value;}
});}
// The navbar counts are controls: each scrolls the list to its section. The
// navbar is position:fixed, so the target has to be offset by its height or the
// section heading lands underneath it. A section hidden by the page filter is
// not a scroll target — nothing to show.
var NAVH=54;
[].slice.call(document.querySelectorAll('.ct[data-target]')).forEach(function(b){
  b.addEventListener('click',function(){
    var s=document.getElementById(b.getAttribute('data-target'));
    if(!s||s.hidden){return;}
    var y=s.getBoundingClientRect().top+(window.pageYOffset||0)-NAVH-12;
    window.scrollTo({top:y>0?y:0,behavior:'smooth'});
  });
});
select(0,false);
filter();
})();
</script>"""


def pretty_date(iso, long=False):
    try:
        d = datetime.datetime.strptime(iso, "%Y-%m-%d")
    except (ValueError, TypeError):
        return iso or ""
    return d.strftime("%-d %B %Y") if long else d.strftime("%-d %b %Y")


def sibling_builds(out_path):
    """Every `asana-qa-triage-YYYY-MM-DD.html` sitting in the output file's own
    folder, newest first, as (filename, iso-date). These are the ARCHIVE; the
    current build is the un-dated CANON file. A static page cannot discover
    builds made after it, so this list is a snapshot — fine, because the current
    report is always regenerated and always carries the complete list."""
    folder = os.path.dirname(os.path.abspath(out_path))
    try:
        names = os.listdir(folder)
    except OSError:
        names = []
    found = [(n, BUILD_RE.match(n).group(1)) for n in names if BUILD_RE.match(n)]
    return sorted(found, key=lambda x: x[1], reverse=True)


def date_select(out_path, date, fragment):
    """The right-hand dropdown. No label — Chris's call; the dates speak for
    themselves. Options are FILENAMES, so picking one navigates to that file;
    this build's own option has an empty value and is the selected one.

    Two shapes, because there are two kinds of file:
      * the current build (`asana-qa-triage.html`) shows "<date> · current"
        selected, then every dated archive beneath it;
      * an archived build shows "Current report" first (so an old file is never
        a dead end), then its own date selected, then the older archives.

    In --fragment mode there are no siblings to navigate to (the fragment is one
    page on a host that knows nothing about the folder), so it shows this build
    only."""
    here = os.path.basename(out_path)
    mine = BUILD_RE.match(here)
    my_iso = mine.group(1) if mine else (date or "")
    is_current = (here == CANON)
    opts = []
    if not fragment and not is_current:
        opts.append(f'<option value="{CANON}">Current report</option>')
    lab = (pretty_date(my_iso) or "This build") + (" · current" if is_current else "")
    opts.append(f'<option value="" selected>{html.escape(lab)}</option>')
    if not fragment:
        for name, iso in sibling_builds(out_path):
            if name == here or (my_iso and iso == my_iso):
                continue
            opts.append(f'<option value="{html.escape(name)}">'
                        f'{html.escape(pretty_date(iso))}</option>')
    return (DT_OPEN + '<select id="dtsel" class="navsel" aria-label="Report build date">'
            + "".join(opts) + '</select>' + DT_CLOSE)


def page_select(tickets):
    """Options come from the tickets' own `page` field via page_of(). Those raw
    values are long ("Research sites & clinicians — `/research-sites/` (ID 488)"),
    so the option carries the short name plus its ticket count. Real pages and
    site-wide buckets are split into optgroups — a QA round routinely names 15–20
    pages, and a flat list that long is a wall.

    The FIRST option is not a page at all: it selects every ticket carrying a
    question for the client, whatever page it sits on. Chris's call, 2026-08-21 —
    it is the highest-value view in the report, because it is what we owe them."""
    c = collections.Counter(page_of(t) for t in tickets)
    nq = sum(1 for t in tickets if has_q(t))
    site = [p for p in CHROME if p in c]
    pages = sorted([p for p in c if p not in site], key=lambda p: (-c[p], p.lower()))
    def opt(p):
        return f'<option value="{html.escape(p)}">{html.escape(p)} ({c[p]})</option>'
    out = (f'<option value="{Q_VALUE}">❓ Has a question for the client ({nq})</option>'
           f'<option disabled>──────────</option>'
           f'<option value="" selected>All pages ({sum(c.values())})</option>')
    if site:
        out += ('<optgroup label="Pages">' + "".join(opt(p) for p in pages) + '</optgroup>'
                '<optgroup label="Site-wide">' + "".join(opt(p) for p in site) + '</optgroup>')
    else:
        out += "".join(opt(p) for p in pages)
    return '<select id="pgsel" class="navsel" aria-label="Filter tickets">' + out + '</select>'


def counts_html(tickets):
    """The four consolidated counts, in the navbar, always all four even at zero.
    Sub-statuses roll up here (partial -> Waiting) while keeping their own section
    in the list below. This is also the bug fix: the old strip added `partial`
    into Blocked, so it read 8 where the sections said 4 and 4.

    They are CONTROLS, not chips (Chris, 2026-08-21). Each is a real <button>
    that scrolls the list to its section, drawn as a full-height cell with a rule
    on its left edge — the same construction as the dropdown cells on the right,
    so the navbar has one vocabulary and not two. No pill, no tint, no border:
    only the NUMBER carries the status colour; the word is ordinary navbar text.

    A bucket with two sections (Waiting takes both `partial` and `waiting`)
    targets the FIRST of them in ORDER — that is where the reader's eye should
    land. A bucket at zero has no section to scroll to, so its button is
    `disabled` rather than a control that silently does nothing."""
    c = collections.Counter(st(t)[2] for t in tickets)
    # bucket -> the id of the first section in render order that rolls into it
    target = {}
    for key in ORDER:
        b = STATUS[key][2]
        if b not in target and any(t.get("status", "waiting") == key for t in tickets):
            target[b] = f"sec-{key}"
    out = ""
    for b in BUCKETS:
        n = c.get(b, 0)
        sec = target.get(b)
        attrs = (f' data-target="{sec}" title="Jump to the {BUCKET_LABEL[b]} section '
                 f'({n} across the whole report)"' if sec
                 else f' disabled title="No {BUCKET_LABEL[b]} tickets in this report"')
        out += (f'<button type="button" class="ct c-{BUCKET_CLS[b]}'
                f'{"" if n else " zero"}"{attrs}>'
                f'<b>{n}</b><i>{BUCKET_LABEL[b]}</i></button>')
    return f'<div class="counts" role="group" aria-label="Ticket counts">{out}</div>'


def build(tickets, client, date, fragment=False, out_path=CANON):
    secs, panes, i = "", "", 0
    for key in ORDER:
        items = [t for t in tickets if t.get("status", "waiting") == key]
        if not items:
            continue
        rws = ""
        for t in items:
            n = num_of(t)          # the stored number, never the loop position
            rws += row(t, n, i)
            panes += pane(t, n, i)
            i += 1
        # The id is the scroll target for the navbar's count buttons.
        secs += (f'<section class="sec s-{STATUS[key][0]}" id="sec-{key}"><header class="sh">'
                 f'<h2><span class="n" data-n="{len(items)}">{len(items)}</span> '
                 f'{STATUS[key][1]}</h2>'
                 f'<p>{STATUS[key][3]}</p></header>'
                 f'<div class="list">{rws}</div></section>')
    # Left: the client name verbatim. NOT title-cased, NOT suffixed — a brand that
    # is deliberately lowercase stays lowercase.
    # Counts live INSIDE .navr, so the whole control group — four counts, page
    # filter, date — sits flush right as one run of divided cells. Chris,
    # 2026-08-21: "flush to the right with vertical bars between them like the
    # others". Putting them next to the brand instead reads as a title block,
    # not as controls, and they ARE controls now.
    nav = (f'<nav class="nav"><h1 class="brand">{html.escape(client)}</h1>'
           f'<div class="navr">'
           f'{counts_html(tickets)}'
           f'<div class="navcell">{page_select(tickets)}</div>'
           f'<div class="navcell">{date_select(out_path, date, fragment)}</div>'
           f'</div></nav>')
    # Just a timestamp. Chris, 2026-08-21: the old footer explained the method
    # in two paragraphs — how tickets were read, why Marker's titles were
    # ignored, why nothing is marked complete. That is a note to ourselves, and
    # this report is client-facing. The one thing a reader needs from the foot
    # of the page is how fresh it is.
    foot = f"""<footer class="foot"><p>Last updated on {html.escape(build_stamp())}</p></footer>"""
    body = f"""<div class="cols">
<div class="col left" id="left">{SUP_OPEN}{SUP_CLOSE}<p class="showing" id="showing"></p>
{secs}
{foot}</div>
<aside class="col right" id="panel" aria-label="Ticket detail">{panes}</aside>
</div>"""
    # No page-level h1 any more (the navbar name is it), so the title carries
    # the client AND the build date — it is the tab label.
    title = f"{client} QA Triage" + (f" — {pretty_date(date)}" if date else "")
    stamp = f'<meta name="qa-build-date" content="{html.escape(date)}">' if date else ""
    if fragment:                      # for a host page that supplies the skeleton
        return (f"<title>{html.escape(title)}</title>\n{stamp}\n{CSS}\n{nav}\n"
                f"<div class=\"wrap\">\n{body}\n</div>\n{JS}")
    return f"""<!doctype html>
<html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{html.escape(title)}</title>
{stamp}
{CSS}
</head><body>
{nav}
<div class="wrap">
{body}
</div>
{JS}
</body></html>"""


def archive_previous(out_path, new_iso):
    """Before overwriting the un-dated current report, copy the build that is
    already sitting there to its own dated filename — so the stable URL always
    holds the newest build and nothing is ever lost.

    The old build's date comes from the `<meta name="qa-build-date">` stamp every
    run writes, so there is NO convention for a human to remember. The copy is
    then rewritten in place: its date dropdown is regenerated (it becomes an
    archive that points back at the current report) and its superseded banner is
    filled in. Returns (archived_filename_or_None, human_note)."""
    if os.path.basename(out_path) != CANON or not os.path.exists(out_path):
        return None, ""
    try:
        txt = open(out_path, encoding="utf-8").read()
    except OSError as e:
        return None, f"could not read the existing report to archive it ({e})"
    m = STAMP_RE.search(txt)
    if m:
        old, how = m.group(1), "its build stamp"
    else:
        old = datetime.date.fromtimestamp(os.path.getmtime(out_path)).isoformat()
        how = "file mtime — the old file predates the build stamp"
    if old == new_iso:
        return None, f"same build date ({old}); replaced in place, nothing archived"
    dst = os.path.join(os.path.dirname(os.path.abspath(out_path)),
                       f"asana-qa-triage-{old}.html")
    new_dt = date_select(dst, old, False)
    banner = (SUP_OPEN + f'<p class="supersede">Archived build from '
              f'{html.escape(pretty_date(old, long=True))}. A newer report exists &mdash; '
              f'<a href="{CANON}">open the current report</a>.</p>' + SUP_CLOSE)
    txt, ndt = REGION_RE["dt"].subn(lambda _m: new_dt, txt)
    txt, nsup = REGION_RE["sup"].subn(lambda _m: banner, txt)
    try:
        open(dst, "w", encoding="utf-8").write(txt)
    except OSError as e:
        return None, f"could not write the archive copy ({e})"
    warn = "" if (ndt and nsup) else "  (old file had no marked regions to rewrite)"
    return os.path.basename(dst), f"archived the previous build ({old}, dated from {how}){warn}"


def main():
    ap = argparse.ArgumentParser(description=__doc__,
                                 formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("tickets")
    ap.add_argument("out", help=f"output path; use the un-dated {CANON} for the current build")
    ap.add_argument("--client", default="Client")
    ap.add_argument("--date", default="", help="build date, YYYY-MM-DD (default: today)")
    ap.add_argument("--no-archive", action="store_true",
                    help="do not copy the report being replaced to its dated archive name")
    ap.add_argument("--no-number-writeback", action="store_true",
                    help="render without saving newly assigned ticket numbers back "
                         "into the input JSON (dry run; the build still uses them)")
    ap.add_argument("--fragment", action="store_true",
                    help="emit without doctype/head, for embedding in a host page. "
                         "NOT for Artifacts — these reports are never published as one.")
    a = ap.parse_args()
    date = a.date or datetime.date.today().isoformat()
    if not ISO_RE.match(date):
        sys.exit(f"--date must be YYYY-MM-DD (got {date!r})")
    with open(a.tickets, encoding="utf-8") as fh:
        raw = fh.read()
    tickets = json.loads(raw)
    if not isinstance(tickets, list):
        sys.exit("tickets JSON must be a list of objects")
    bad = [t.get("status") for t in tickets
           if t.get("status", "waiting") not in STATUS]
    if bad:
        sys.exit(f"unknown status values: {sorted(set(bad))}; expected {sorted(STATUS)}")
    missing = [i for i, t in enumerate(tickets) if not t.get("title")]
    if missing:
        sys.exit(f"tickets at index {missing[:5]} have no title")

    # ---- ticket numbers. Unique across the whole report, stored not derived. ----
    for i, t in enumerate(tickets):
        try:
            num_of(t)
        except ValueError as e:
            sys.exit(f'ticket at index {i} ("{(t.get("title") or "")[:60]}"): {NUM_KEY} {e}')
    dupes = duplicate_numbers(tickets)
    if dupes:
        def where(k):
            return f'index {k} \u2014 "{(tickets[k].get("title") or "")[:70]}"'
        lines = [f"duplicate ticket numbers in {a.tickets}; refusing to build:"]
        for n, i, j in dupes:
            lines.append(f"  {NUM_KEY} {n} is on BOTH {where(i)} and {where(j)}")
        lines.append("  A ticket number is a permanent identifier — it appears in commit "
                     "messages, Asana comments and client email.")
        lines.append(f"  Fix the JSON: give the newer ticket a fresh {NUM_KEY} above the "
                     "current maximum. Never re-use a retired one.")
        sys.exit("\n".join(lines))
    assigned, note = number_tickets(tickets, a.tickets, raw,
                                    write=not a.no_number_writeback)
    print(f"numbers: {note}")

    if not a.fragment and not a.no_archive:
        name, note = archive_previous(a.out, date)
        if note:
            print(f"archive: {note}")
    open(a.out, "w", encoding="utf-8").write(
        build(tickets, a.client, date, a.fragment, a.out))

    c = collections.Counter(st(t)[2] for t in tickets)
    v = collections.Counter(t.get("status", "waiting") for t in tickets)
    print(f"{a.out}: {len(tickets)} tickets — " +
          ", ".join(f"{c.get(b, 0)} {BUCKET_LABEL[b]}" for b in BUCKETS))
    print("  statuses: " + ", ".join(f"{n} {k}" for k, n in v.most_common()))
    print(f"  {sum(1 for t in tickets if has_q(t))} carry a question for the client")
    nums = [num_of(t) for t in tickets]
    print(f"  numbered {min(nums)}\u2013{max(nums)}, all unique"
          + (f" ({assigned} assigned this run)" if assigned else ""))
    chat = sum(1 for t in tickets if not t.get("url"))
    if chat:
        print(f"  {chat} ticket(s) have no Asana task (raised in chat) \u2014 "
              "rendered without the Asana icon")
    if not a.fragment:
        sibs = [n for n, _ in sibling_builds(a.out) if n != os.path.basename(a.out)]
        print(f"  date dropdown: this build + {len(sibs)} archived report(s) in the same folder")
        if os.path.basename(a.out) != CANON:
            print(f"  NOTE: the current build is meant to live at the un-dated {CANON} "
                  "so links to it never go stale.")
    nodid = [num_of(t) for t in tickets if not did_of(t)]
    if nodid:
        print(f"  {len(nodid)} ticket(s) have no `did` — they show the placeholder above "
              "the divider; `plan` is NOT used above the line")
    leaks = leak_check(tickets)
    if leaks:
        print(f"WARNING: {len(leaks)} field(s) put internal detail ABOVE the divider, "
              "which is the client's half of the panel:")
        for n, f, what, snip in leaks[:12]:
            print(f"  ticket {n} · {f}: {what} — …{snip}…")
        if len(leaks) > 12:
            print(f"  …and {len(leaks) - 12} more")
        print("  Move it into `plan` (which renders below the line) and rewrite the "
              "field in the words you would say to the client on the phone.")
    if len(tickets) <= 10:
        print("NOTE: 10 or fewer tickets — inline tables in chat are usually the better "
              "deliverable at this size. See the header of this script.")


if __name__ == "__main__":
    main()
