#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Build a client-facing build/QA tracker page from a JSON data file.

    python3 build-tracker.py tracker.json              # client build, Classic
                                                       # City skin (default)
    python3 build-tracker.py tracker.json --internal   # internal build
    python3 build-tracker.py tracker.json --no-skin    # plain build
                                                       # → {name}.plain.html

WHY THIS EXISTS
---------------
Chris runs multi-round QA/build cycles with clients. The internal version of
that tracker (`sg-trialport/docs/qa/`) proved the shape works, but it is one
long scroll full of file paths, block slugs and agent names — you cannot send
a client a link to it. This is the generalised, sendable version.

WHY ONE DATA FILE AND TWO RENDERS
---------------------------------
The obvious solution — keep a client copy and an internal copy of the data —
is the one that rots. Two files drift the first time a status changes on a
Friday afternoon, and the client is then reading a lie. So there is exactly
ONE JSON file. Every record may carry BOTH an internal note and a client-safe
summary; `--internal` is the only thing that decides which of the two you get,
plus ownership and effort. The client build does not hide those fields with
CSS — it never emits them into the HTML at all, because "hidden" in a file the
client can download is not hidden.

WHY THE ROUND IS THE WHOLE VIEW
-------------------------------
The round tabs live INSIDE the sticky banner, and selecting one switches every
section on the page: the counts in the banner, what is waiting on whom, the
work, the resolved table and the history. Rounds used to be an accordion in
the middle of the page *and* a tab strip at the top, which meant two different
controls both claimed to answer "what round are we in". One control, one
answer. There is no "the work, by round" section any more because every view
is already by round.

WHY GROUPED BY PAGE
-------------------
Chris: "in our summaries of things that we have completed, I like to group
them by the page that they are all on." A QA round is mostly page-by-page work
with a handful of genuinely site-wide fixes, and a flat date-ordered list
destroys that structure. So records carry an optional `page`, a root-level
`pages` registry gives each one a label and a URL, and history + resolved
group by it. SITE-WIDE COMES FIRST: a site-wide change is context for
everything below it (a header fix explains something the client would
otherwise re-report on four pages), and a fixed first position means the group
does not move as pages come and go.

WHY SELF-CONTAINED
------------------
The page is served as a static file out of the child theme on WP Engine
(`/wp-content/themes/sg-{slug}/docs/qa/round-2.html`). There is no build step
and no CDN whitelist on that path, so every byte of CSS, JS and iconography is
inline and there is not one external request. It also means the client can
save the page or mail it onward and it still works — which is why the icons
below are inline SVG path data and NOT a link to the theme's FontAwesome
webfont kit. See the README, § "Icons".

WHY NATIVE <details> AND HAND-ROLLED TABS
-----------------------------------------
No framework can be loaded (see above), and a disclosure widget is one of the
few things the platform already does correctly: `<details>`/`<summary>` is
keyboard-operable, screen-reader-correct, and — critically — Ctrl+F and
print-to-PDF can force it open. Only the round tabs are hand-rolled, because
there is no native tab element; those get full ARIA and arrow-key handling.

Python 3.9 compatible (the local interpreter is 3.9.6).
"""
from __future__ import annotations

import argparse
import html
import importlib.util
import json
import os
import re
import sys
from datetime import date

# ---------------------------------------------------------------------------
# The colour language.
#
# One rule governs the whole palette: A COLOUR MEANS A STATE, NEVER A TOPIC.
# The prototype scattered literal hex values through the markup, so "amber"
# meant "medium severity" in one place and "rebuild" in another, and a client
# reading it learned nothing. Here each status maps to exactly one token
# triple, the tokens are emitted as CSS custom properties, and nothing in the
# markup carries a literal colour.
#
# The load-bearing one is `client`: AMBER ALWAYS MEANS "YOUR MOVE". It is used
# for nothing else, anywhere, ever. If a client scans the page and sees no
# amber, there is nothing waiting on them — that promise is the whole point of
# sending them a link.
#
# WHERE COLOUR IS ALLOWED TO LAND (revised): the banner counters, the status
# chips, and the round tabs. NOT whole cards. A card sitting under a heading
# that already says "Waiting on you" does not also need to be amber — that is
# the same fact said three times, and it turns the page into a highlighter
# accident. Cards are near-neutral; the chip carries the state.
#
# HOW MANY COLOURS (revised again, 2026-07-31): four, plus grey. Chris: "I
# think we're using too much colour here... the only colours we need are green
# for done, purple for review, red for blocked." Amber survives as the fifth
# because it is not a status colour at all — it is the your-move signal the
# whole page is built on (see AWAITING below and the note on --t-client). The
# old blue `progress` tone is GONE: "in progress", "queued", "deferred" and
# "waiting on us" are all the same fact to a client — it is on our list and
# needs nothing from them — so they are all grey.
#
#   key            label shown       tone token   meaning
#   ------------   ---------------   ----------   ----------------------
#   done           Done              done         finished and verified
#   in_progress    In progress       queued       actively being worked
#   review         Review            review       built, awaiting sign-off
#   needs_client   Needs you         client       WE ARE WAITING ON YOU
#   blocked        Blocked           blocked      cannot proceed
#   queued         Queued            queued       agreed, not started
#   deferred       Deferred          deferred     deliberately not this round
# ---------------------------------------------------------------------------
STATUS = {
    "done":         ("Done",        "done",     "circle-check"),
    "in_progress":  ("In progress", "queued",   "circle-half-stroke"),
    "review":       ("Review",      "review",   "eye"),
    "needs_client": ("Needs you",   "client",   "circle-exclamation"),
    "blocked":      ("Blocked",     "blocked",  "circle-minus"),
    "queued":       ("Queued",      "queued",   "clock"),
    "deferred":     ("Deferred",    "deferred", "circle-pause"),
}

# Changelog entries use a smaller vocabulary — a log line is not a task.
CHANGE_STATE = {
    "shipped":  ("Shipped",     "done",     "circle-check"),
    "partial":  ("Partly done", "queued",   "circle-half-stroke"),
    "reverted": ("Reverted",    "blocked",  "circle-minus"),
    "deferred": ("Deferred",    "deferred", "circle-pause"),
}

# Blocker "awaiting" values. NOTE: unlike task `owner`, this IS shown to the
# client in both builds, deliberately — a blocker whose whole meaning is
# "we cannot move until you answer" is useless if the client cannot see that
# it is theirs. Task-level ownership is a different thing: it is workload
# allocation, which is internal.
AWAITING = {
    "client": ("Waiting on you", "client", "hand"),
    "agency": ("Waiting on us",  "queued", "screwdriver-wrench"),
}

ROUND_STATE = {
    "open":    ("Open",    "queued",   "circle-half-stroke"),
    "closed":  ("Closed",  "done",     "circle-check"),
    "planned": ("Planned", "queued",   "clock"),
}

EFFORT = {"xs": "XS", "s": "S", "m": "M", "l": "L", "xl": "XL"}
OWNER = {"agency": "AGENCY", "client": "CLIENT"}
# Still validated, still accepted in data — but no longer rendered. The
# "Urgent" chip it used to draw was noise: everything under "Waiting on you"
# is urgent by definition, which is the whole reason it is under that heading.
SEVERITY = ("high", "normal")

SITEWIDE_DEFAULT = "Site-wide"

E = html.escape
ID_SAFE = re.compile(r"[^A-Za-z0-9_-]+")


def esc(value) -> str:
    """Escape anything for text content. None becomes empty, never 'None'."""
    return E(str(value)) if value not in (None, "") else ""


def slug(value) -> str:
    """Stable DOM-id fragment from an arbitrary JSON id."""
    return ID_SAFE.sub("-", str(value)).strip("-").lower() or "x"


# Dates. The schema says YYYY-MM-DD, but a child's JSON is hand-written, so
# anything unparseable passes through raw rather than being dropped — a date
# we cannot format is still a date the client can read.
ISO = re.compile(r"^(\d{4})-(\d{2})-(\d{2})$")
MON = ("JAN", "FEB", "MAR", "APR", "MAY", "JUN",
       "JUL", "AUG", "SEP", "OCT", "NOV", "DEC")
MON_LONG = ("January", "February", "March", "April", "May", "June", "July",
            "August", "September", "October", "November", "December")


def short_date(value):
    """'2026-07-09' -> 'JUL 9'.

    The full ISO date in a table cell is ten monospaced characters of column
    width spent almost entirely on a year the reader already knows. The year
    is not lost — the cell is a <time datetime="..."> with a title, so it is
    one hover away and machine-readable."""
    m = ISO.match(str(value or ""))
    if not m:
        return esc(value)
    return "{} {}".format(MON[int(m.group(2)) - 1], int(m.group(3)))


def long_date(value):
    """'2026-07-30' -> 'July 30, 2026'. For prose, where a stamp would read
    as data rather than as part of a sentence."""
    m = ISO.match(str(value or ""))
    if not m:
        return esc(value)
    return "{} {}, {}".format(
        MON_LONG[int(m.group(2)) - 1], int(m.group(3)), m.group(1)
    )


# ---------------------------------------------------------------------------
# Iconography.
#
# WHY INLINE SVG AND NOT THE THEME'S FONTAWESOME KIT
# --------------------------------------------------
# The parent theme ships a FontAwesome Pro kit at `assets/fontawesome/` — but
# it is webfont-based: a stylesheet per style plus ~6 MB of woff2. Consuming it
# from here means one of two bad outcomes:
#
#   * <link> it by relative path. Works while the file is served from the theme
#     on WP Engine; produces a page full of empty boxes the moment anyone mails
#     the file, saves it, or opens it from a USB stick. The README offers
#     "mail the self-contained file" as the privacy-preserving alternative to a
#     public URL, so that failure mode is not hypothetical.
#   * base64 a whole woff2 into the page. Self-contained, but 260 kB of font
#     for eleven glyphs, on a page that is otherwise ~50 kB.
#
# So: the eleven glyphs the template actually uses are inlined here as SVG path
# data, lifted verbatim from FontAwesome Pro 7.2.0's own SVG distribution, and
# emitted once as an inline <symbol> sprite. Same artwork, same licence
# (Chris's FA Pro licence covers it), zero external requests, and it survives
# being emailed.
#
# THE TRADE-OFF WE ACCEPTED: adding a new icon is an edit to this dict, not a
# class name in the markup. That is the price of self-containment, and it is
# the right price for a template whose entire contract is "one file, no
# requests".
#
# THE RULE: every icon on this page is decorative. It sits next to a real text
# label, it is aria-hidden + focusable="false", and nothing on the page becomes
# ambiguous if the SVG fails to render. An icon is never the only thing telling
# you what a section is.
# ---------------------------------------------------------------------------
ICONS = {
    "hand": ("0 0 512 512", "M288 32c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 208c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-176c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 272c0 1.5 0 3.1 .1 4.6L67.6 283c-16-15.2-41.3-14.6-56.6 1.4S-3.6 325.7 12.4 341L124.8 448c43.1 41.1 100.4 64 160 64l19.2 0c97.2 0 176-78.8 176-176l0-208c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 112c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-176c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 176c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-208z"),
    "screwdriver-wrench": ("0 0 576 512", "M70.8-6.7c5.4-5.4 13.8-6.2 20.2-2L209.9 70.5c8.9 5.9 14.2 15.9 14.2 26.6l0 49.6 90.8 90.8c33.3-15 73.9-8.9 101.2 18.5L542.2 382.1c18.7 18.7 18.7 49.1 0 67.9l-60.1 60.1c-18.7 18.7-49.1 18.7-67.9 0L288.1 384c-27.4-27.4-33.5-67.9-18.5-101.2l-90.8-90.8-49.6 0c-10.7 0-20.7-5.3-26.6-14.2L23.4 58.9c-4.2-6.3-3.4-14.8 2-20.2L70.8-6.7zm145 303.5c-6.3 36.9 2.3 75.9 26.2 107.2l-94.9 95c-28.1 28.1-73.7 28.1-101.8 0s-28.1-73.7 0-101.8l135.4-135.5 35.2 35.1zM384.1 0c20.1 0 39.4 3.7 57.1 10.5 10 3.8 11.8 16.5 4.3 24.1L388.8 91.3c-3 3-4.7 7.1-4.7 11.3l0 41.4c0 8.8 7.2 16 16 16l41.4 0c4.2 0 8.3-1.7 11.3-4.7l56.7-56.7c7.6-7.5 20.3-5.7 24.1 4.3 6.8 17.7 10.5 37 10.5 57.1 0 43.2-17.2 82.3-45 111.1l-49.1-49.1c-33.1-33-78.5-45.7-121.1-38.4l-56.8-56.8 0-29.7-.2-5c-.8-12.4-4.4-24.3-10.5-34.9 29.4-35 73.4-57.2 122.7-57.3z"),
    "circle-check": ("0 0 512 512", "M256 512a256 256 0 1 1 0-512 256 256 0 1 1 0 512zM374 145.7c-10.7-7.8-25.7-5.4-33.5 5.3L221.1 315.2 169 263.1c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l72 72c5 5 11.8 7.5 18.8 7s13.4-4.1 17.5-9.8L379.3 179.2c7.8-10.7 5.4-25.7-5.3-33.5z"),
    "clock-rotate-left": ("0 0 576 512", "M288 64c106 0 192 86 192 192S394 448 288 448c-65.2 0-122.9-32.5-157.6-82.3-10.1-14.5-30.1-18-44.6-7.9s-18 30.1-7.9 44.6C124.1 468.6 201 512 288 512 429.4 512 544 397.4 544 256S429.4 0 288 0C202.3 0 126.5 42.1 80 106.7L80 80c0-17.7-14.3-32-32-32S16 62.3 16 80l0 112c0 17.7 14.3 32 32 32l24.6 0c.5 0 1 0 1.5 0l86 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-38.3 0C154.9 102.6 217 64 288 64zm24 88c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 104c0 6.4 2.5 12.5 7 17l72 72c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-65-65 0-94.1z"),
    "list-check": ("0 0 512 512", "M133.8 36.3c10.9 7.6 13.5 22.6 5.9 33.4l-56 80c-4.1 5.8-10.5 9.5-17.6 10.1S52 158 47 153L7 113C-2.3 103.6-2.3 88.4 7 79S31.6 69.7 41 79l19.8 19.8 39.6-56.6c7.6-10.9 22.6-13.5 33.4-5.9zm0 160c10.9 7.6 13.5 22.6 5.9 33.4l-56 80c-4.1 5.8-10.5 9.5-17.6 10.1S52 318 47 313L7 273c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l19.8 19.8 39.6-56.6c7.6-10.9 22.6-13.5 33.4-5.9zM224 96c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zm0 160c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zM160 416c0-17.7 14.3-32 32-32l288 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-288 0c-17.7 0-32-14.3-32-32zM64 376a40 40 0 1 1 0 80 40 40 0 1 1 0-80z"),
    "file-lines": ("0 0 384 512", "M0 64C0 28.7 28.7 0 64 0L213.5 0c17 0 33.3 6.7 45.3 18.7L365.3 125.3c12 12 18.7 28.3 18.7 45.3L384 448c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64L0 64zm208-5.5l0 93.5c0 13.3 10.7 24 24 24L325.5 176 208 58.5zM120 256c-13.3 0-24 10.7-24 24s10.7 24 24 24l144 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-144 0zm0 96c-13.3 0-24 10.7-24 24s10.7 24 24 24l144 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-144 0z"),
    "globe": ("0 0 512 512", "M351.9 280l-190.9 0c2.9 64.5 17.2 123.9 37.5 167.4 11.4 24.5 23.7 41.8 35.1 52.4 11.2 10.5 18.9 12.2 22.9 12.2s11.7-1.7 22.9-12.2c11.4-10.6 23.7-28 35.1-52.4 20.3-43.5 34.6-102.9 37.5-167.4zM160.9 232l190.9 0C349 167.5 334.7 108.1 314.4 64.6 303 40.2 290.7 22.8 279.3 12.2 268.1 1.7 260.4 0 256.4 0s-11.7 1.7-22.9 12.2c-11.4 10.6-23.7 28-35.1 52.4-20.3 43.5-34.6 102.9-37.5 167.4zm-48 0C116.4 146.4 138.5 66.9 170.8 14.7 78.7 47.3 10.9 131.2 1.5 232l111.4 0zM1.5 280c9.4 100.8 77.2 184.7 169.3 217.3-32.3-52.2-54.4-131.7-57.9-217.3L1.5 280zm398.4 0c-3.5 85.6-25.6 165.1-57.9 217.3 92.1-32.7 159.9-116.5 169.3-217.3l-111.4 0zm111.4-48C501.9 131.2 434.1 47.3 342 14.7 374.3 66.9 396.4 146.4 399.9 232l111.4 0z"),
    "arrow-up-right-from-square": ("0 0 512 512", "M320 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l82.7 0-201.4 201.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L448 109.3 448 192c0 17.7 14.3 32 32 32s32-14.3 32-32l0-160c0-17.7-14.3-32-32-32L320 0zM80 96C35.8 96 0 131.8 0 176L0 432c0 44.2 35.8 80 80 80l256 0c44.2 0 80-35.8 80-80l0-80c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 80c0 8.8-7.2 16-16 16L80 448c-8.8 0-16-7.2-16-16l0-256c0-8.8 7.2-16 16-16l80 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L80 96z"),
    "calendar": ("0 0 448 512", "M128 0C110.3 0 96 14.3 96 32l0 32-32 0C28.7 64 0 92.7 0 128l0 48 448 0 0-48c0-35.3-28.7-64-64-64l-32 0 0-32c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 32-128 0 0-32c0-17.7-14.3-32-32-32zM0 224L0 416c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-192-448 0z"),
    "clock": ("0 0 512 512", "M256 0a256 256 0 1 1 0 512 256 256 0 1 1 0-512zM232 120l0 136c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2 280 120c0-13.3-10.7-24-24-24s-24 10.7-24 24z"),
    "eye": ("0 0 576 512", "M288 32c-80.8 0-145.5 36.8-192.6 80.6-46.8 43.5-78.1 95.4-93 131.1-3.3 7.9-3.3 16.7 0 24.6 14.9 35.7 46.2 87.7 93 131.1 47.1 43.7 111.8 80.6 192.6 80.6s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1 3.3-7.9 3.3-16.7 0-24.6-14.9-35.7-46.2-87.7-93-131.1-47.1-43.7-111.8-80.6-192.6-80.6zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64-11.5 0-22.3-3-31.7-8.4-1 10.9-.1 22.1 2.9 33.2 13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-12.2-45.7-55.5-74.8-101.1-70.8 5.3 9.3 8.4 20.1 8.4 31.7z"),
    "circle-exclamation": ("0 0 512 512", "M256 512a256 256 0 1 1 0-512 256 256 0 1 1 0 512zm0-192a32 32 0 1 0 0 64 32 32 0 1 0 0-64zm0-192c-18.2 0-32.7 15.5-31.4 33.7l7.4 104c.9 12.6 11.4 22.3 23.9 22.3 12.6 0 23-9.7 23.9-22.3l7.4-104c1.3-18.2-13.1-33.7-31.4-33.7z"),
    "circle-minus": ("0 0 512 512", "M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM168 232l176 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-176 0c-13.3 0-24-10.7-24-24s10.7-24 24-24z"),
    "circle-pause": ("0 0 512 512", "M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM224 192l0 128c0 17.7-14.3 32-32 32s-32-14.3-32-32l0-128c0-17.7 14.3-32 32-32s32 14.3 32 32zm128 0l0 128c0 17.7-14.3 32-32 32s-32-14.3-32-32l0-128c0-17.7 14.3-32 32-32s32 14.3 32 32z"),
    "circle-half-stroke": ("0 0 512 512", "M448 256c0-106-86-192-192-192l0 384c106 0 192-86 192-192zM0 256a256 256 0 1 1 512 0 256 256 0 1 1 -512 0z"),
    "triangle-exclamation": ("0 0 512 512", "M256 0c14.7 0 28.2 8.1 35.2 21l216 400c6.7 12.4 6.4 27.4-.8 39.5S486.1 480 472 480L40 480c-14.1 0-27.2-7.4-34.4-19.5s-7.5-27.1-.8-39.5l216-400c7-12.9 20.5-21 35.2-21zm0 352a32 32 0 1 0 0 64 32 32 0 1 0 0-64zm0-192c-18.2 0-32.7 15.5-31.4 33.7l7.4 104c.9 12.5 11.4 22.3 23.9 22.3 12.6 0 23-9.7 23.9-22.3l7.4-104c1.3-18.2-13.1-33.7-31.4-33.7z"),
    "palette": ("0 0 512 512", "M512 256c0 .9 0 1.8 0 2.7-.4 36.5-33.6 61.3-70.1 61.3L344 320c-26.5 0-48 21.5-48 48 0 3.4 .4 6.7 1 9.9 2.1 10.2 6.5 20 10.8 29.9 6.1 13.8 12.1 27.5 12.1 42 0 31.8-21.6 60.7-53.4 62-3.5 .1-7 .2-10.6 .2-141.4 0-256-114.6-256-256S114.6 0 256 0 512 114.6 512 256zM128 288a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zm0-96a32 32 0 1 0 0-64 32 32 0 1 0 0 64zM288 96a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zm96 96a32 32 0 1 0 0-64 32 32 0 1 0 0 64z"),
}

_USED_ICONS = set()


def icon(name, cls="ic"):
    """A decorative icon. Registers it so only used glyphs reach the sprite.

    aria-hidden + focusable="false" is not optional: every call site has a real
    text label next to it, and a screen reader announcing "hand, Waiting on
    you" is strictly worse than "Waiting on you"."""
    if name not in ICONS:
        raise KeyError("unknown icon {!r}".format(name))
    _USED_ICONS.add(name)
    return (
        '<svg class="{}" aria-hidden="true" focusable="false">'
        '<use href="#i-{}"></use></svg>'.format(cls, name)
    )


def sprite():
    """One inline <symbol> sheet, emitted first thing in <body>."""
    if not _USED_ICONS:
        return ""
    syms = "".join(
        '<symbol id="i-{n}" viewBox="{vb}"><path d="{d}"/></symbol>'.format(
            n=n, vb=ICONS[n][0], d=ICONS[n][1]
        )
        for n in sorted(_USED_ICONS)
    )
    return (
        '<svg class="sprite" xmlns="http://www.w3.org/2000/svg" '
        'aria-hidden="true" focusable="false"><defs>{}</defs></svg>'.format(syms)
    )


# ---------------------------------------------------------------------------
# Validation.
#
# A child theme fills this JSON in by hand, so the failure mode we actually
# have to design for is a typo in a status key at 6pm before a client call.
# Silently rendering an unstyled grey pill would let that ship; failing loudly
# with the list of legal values does not. Every check below exists because it
# is a mistake someone will make.
# ---------------------------------------------------------------------------
class DataError(Exception):
    pass


def _require(obj, keys, where):
    for k in keys:
        if k not in obj or obj[k] in (None, ""):
            raise DataError("{}: missing required field '{}'".format(where, k))


def _enum(value, table, field, where):
    if value not in table:
        raise DataError(
            "{}: {}={!r} is not a known value. Valid: {}".format(
                where, field, value, ", ".join(sorted(table))
            )
        )


def validate(data):
    _require(data, ["project", "rounds"], "root")
    _require(data["project"], ["client", "title"], "project")

    if not isinstance(data["rounds"], list) or not data["rounds"]:
        raise DataError("rounds: must be a non-empty list")

    # --- the page registry -------------------------------------------------
    # Page ids are validated the same way blocker ids are, and for the same
    # reason: a typo'd `"page": "hompage"` would otherwise silently invent a
    # group of one, which looks like a real answer and is not.
    pages = data.get("pages") or []
    if not isinstance(pages, list):
        raise DataError("pages: must be a list of {id, label, url?} objects")
    page_ids = set()
    for p in pages:
        pw = "pages entry {}".format(p.get("id", "?"))
        _require(p, ["id", "label"], pw)
        if p["id"] in page_ids:
            raise DataError("{}: duplicate page id '{}'".format(pw, p["id"]))
        page_ids.add(p["id"])

    def _page(rec, where):
        if rec.get("page") and rec["page"] not in page_ids:
            raise DataError(
                "{}: page='{}' is not declared in the root `pages` registry. "
                "Declared: {}".format(
                    where, rec["page"], ", ".join(sorted(page_ids)) or "(none)"
                )
            )

    numbers = []
    blocker_ids = set()

    for r in data["rounds"]:
        where = "round {}".format(r.get("number", "?"))
        _require(r, ["number", "label", "state"], where)
        _enum(r["state"], ROUND_STATE, "state", where)
        numbers.append(r["number"])

        for b in r.get("blockers", []):
            bw = "{} blocker {}".format(where, b.get("id", "?"))
            _require(b, ["id", "title", "awaiting", "impact"], bw)
            _enum(b["awaiting"], AWAITING, "awaiting", bw)
            _page(b, bw)
            if b.get("severity") and b["severity"] not in SEVERITY:
                raise DataError(
                    "{}: severity must be one of {}".format(bw, ", ".join(SEVERITY))
                )
            if b["id"] in blocker_ids:
                raise DataError("{}: duplicate blocker id '{}'".format(bw, b["id"]))
            blocker_ids.add(b["id"])

        for s in r.get("sections", []):
            sw = "{} section {}".format(where, s.get("id", "?"))
            _require(s, ["id", "title"], sw)
            _page(s, sw)
            for t in s.get("tasks", []):
                tw = "{} task {}".format(sw, t.get("id", "?"))
                _require(t, ["id", "title", "status"], tw)
                _enum(t["status"], STATUS, "status", tw)
                _page(t, tw)
                if t.get("owner"):
                    _enum(t["owner"], OWNER, "owner", tw)
                if t.get("effort"):
                    _enum(t["effort"], EFFORT, "effort", tw)
                # A blocked task that does not say what it is blocked on is
                # the single most common complaint about status trackers.
                if t.get("blocked_by") and t["blocked_by"] not in blocker_ids:
                    raise DataError(
                        "{}: blocked_by='{}' does not match any blocker id "
                        "declared so far (blockers must appear in the same "
                        "round or an earlier one)".format(tw, t["blocked_by"])
                    )

    if len(set(numbers)) != len(numbers):
        raise DataError("rounds: duplicate round numbers {}".format(numbers))

    for c in data.get("changelog", []):
        cw = "changelog {}".format(c.get("ref") or c.get("date", "?"))
        _require(c, ["date", "what", "state"], cw)
        _enum(c["state"], CHANGE_STATE, "state", cw)
        _page(c, cw)
        if c.get("round") is not None and c["round"] not in numbers:
            raise DataError(
                "{}: round={} is not a declared round".format(cw, c["round"])
            )


# ---------------------------------------------------------------------------
# Render helpers
# ---------------------------------------------------------------------------
def chip(label, tone, ic=None, cls="chip"):
    return '<span class="{} t-{}">{}{}</span>'.format(
        cls, tone, icon(ic) if ic else "", esc(label)
    )


def safe_url(url):
    """http(s):// and site-relative only. Refuses javascript: outright."""
    return bool(url) and (
        url.startswith("http://") or url.startswith("https://") or url.startswith("/")
    )


def anchor(url, label, external_icon=True):
    return '<a class="lnk" href="{}" rel="noopener">{}{}</a>'.format(
        E(url),
        icon("arrow-up-right-from-square") if external_icon else "",
        esc(label or url),
    )


def anchor_compact(url, label):
    """A link that reads "Link" on screen and keeps its real label underneath.

    In the work table the link label ("Emergency & After Hours") was the
    widest thing in a narrow column and wrapped to three lines, for a string
    the Item and Page context already implied. So the visible word is just
    "Link" — aria-hidden — and the real label is the accessible name plus the
    hover title. Nothing is lost; it stops costing a third of the row height.
    The print stylesheet swaps them back, because paper has no hover."""
    return (
        '<a class="lnk" href="{u}" rel="noopener" title="{l}">{ic}'
        '<span aria-hidden="true">Link</span>'
        '<span class="sr-only">{l}</span></a>'
    ).format(u=E(url), l=esc(label or url),
             ic=icon("arrow-up-right-from-square"))


def link_list(links):
    out = [anchor(l.get("url"), l.get("label")) for l in (links or [])
           if safe_url(l.get("url"))]
    return "".join(out)


class Pages:
    """The page registry: id -> {label, url}, plus a stable display order.

    Site-wide (page absent) is ALWAYS first. It is context for everything
    under it, and a fixed position means the group does not shuffle as the
    project grows pages."""

    def __init__(self, data):
        self.order = []
        self.by_id = {}
        for p in data.get("pages") or []:
            self.by_id[p["id"]] = p
            self.order.append(p["id"])
        self.sitewide = data.get("sitewide_label") or SITEWIDE_DEFAULT

    def label(self, pid):
        return self.by_id[pid]["label"] if pid in self.by_id else self.sitewide

    def url(self, pid):
        p = self.by_id.get(pid) or {}
        return p.get("url") if safe_url(p.get("url")) else None

    def group(self, records):
        """[(page_id|None, label, [records])] — site-wide first, then registry
        order. Pages with no records in this set are skipped entirely."""
        buckets = {}
        for r in records:
            buckets.setdefault(r.get("page") or None, []).append(r)
        out = []
        if None in buckets:
            out.append((None, self.sitewide, buckets[None]))
        for pid in self.order:
            if pid in buckets:
                out.append((pid, self.by_id[pid]["label"], buckets[pid]))
        return out

    def tag(self, pid):
        """The small 'which page' label on a card. Links to the page when the
        registry knows a URL, otherwise plain text — never a dead link."""
        if not pid:
            return ""
        lbl = self.label(pid)
        url = self.url(pid)
        inner = "{}{}".format(icon("file-lines"), esc(lbl))
        if url:
            return '<a class="ptag" href="{}" rel="noopener">{}</a>'.format(E(url), inner)
        return '<span class="ptag">{}</span>'.format(inner)


class Renderer:
    """Everything mode-dependent goes through here, so there is exactly one
    place to audit when asking 'can the client see this?'."""

    def __init__(self, internal):
        self.internal = internal

    def visible(self, items):
        """Drop records flagged internal_only from the client build.

        Belt and braces alongside per-field stripping: some whole items
        (an internal refactor, a note about a difficult conversation) have no
        client-safe form at all, and forcing an author to write one would just
        produce a lie."""
        if self.internal:
            return list(items or [])
        return [i for i in (items or []) if not i.get("internal_only")]

    def body(self, item):
        """The prose for a record.

        Client build: `summary` only, and NOTHING if summary is absent —
        falling back to an internal note would be exactly the leak this whole
        design exists to prevent. Internal build: summary, then the internal
        note in its own visually distinct block."""
        out = ""
        if item.get("summary"):
            out += '<p class="prose">{}</p>'.format(esc(item["summary"]))
        if self.internal and item.get("internal_note"):
            out += '<p class="internal-note"><b>Internal:</b> {}</p>'.format(
                esc(item["internal_note"])
            )
        return out

    def note(self, item):
        if self.internal and item.get("internal_note"):
            return '<p class="internal-note"><b>Internal:</b> {}</p>'.format(
                esc(item["internal_note"])
            )
        return ""

    def meta_pills(self, item):
        """Ownership + effort. Internal build only — emitted, not hidden."""
        if not self.internal:
            return ""
        out = ""
        if item.get("owner"):
            out += '<span class="t-owner o-{}">{}</span>'.format(
                item["owner"], OWNER[item["owner"]]
            )
        if item.get("effort"):
            out += '<span class="t-effort">{}</span>'.format(EFFORT[item["effort"]])
        return out


def h2_count(n, tone, noun):
    """The pill of N beside a landmark heading.

    The number is aria-hidden and re-stated in words, because a bare glyph
    inside a heading is concatenated into its accessible name — a screen
    reader would otherwise announce "Resolved 3", which reads as a heading
    called "Resolved 3"."""
    if not n:
        return ""
    return (
        '<span class="h2-count t-{t}" aria-hidden="true">{n}</span>'
        '<span class="sr-only">, {n} {noun}</span>'.format(t=tone, n=n, noun=noun)
    )


def card_foot(created, links):
    """THE consistent card footer: a rule, then the created date on the left
    and the associated link(s) on the right. Same shape on every card, so a
    reader's eye learns one place to look for 'when' and one for 'where'.

    NO WORD IN FRONT OF THE DATE. It used to read "Raised 2026-07-08"; the
    calendar glyph plus a date in the footer of a card already says when, and
    the verb was one of several bits of furniture that made the card look
    busier than the fact it carried. The stamp is `aria-label`led instead, so
    the meaning survives for a screen reader without printing it.

    Suppressed only when a card has neither — an empty bar is not consistency,
    it is a bar."""
    left = (
        '<span class="stamp" aria-label="Raised {}">{}{}</span>'.format(
            esc(created), icon("calendar"), esc(created)
        )
        if created
        else ""
    )
    right = link_list(links)
    if not left and not right:
        return ""
    return '<footer class="card-foot"><div class="cf-l">{}</div>' \
           '<div class="cf-r">{}</div></footer>'.format(left, right)


# ---------------------------------------------------------------------------
# Sections of the page
# ---------------------------------------------------------------------------
def count_tasks(rnd, R):
    """Per-status counts for one round, over client-visible tasks only when
    building for the client — otherwise the banner would advertise work the
    client cannot see anywhere on the page."""
    tally = dict((k, 0) for k in STATUS)
    total = 0
    for s in R.visible(rnd.get("sections")):
        for t in R.visible(s.get("tasks")):
            tally[t["status"]] += 1
            total += 1
    return tally, total


def round_stats(rnd, R):
    tally, total = count_tasks(rnd, R)
    pct = int(round(tally["done"] * 100.0 / total)) if total else 0
    return tally, total, pct


def client_asks(rnd, R):
    """THE definition of "waiting on you", and the only one.

    Returns (blockers, [(section, task), ...]). Two things genuinely wait on a
    client: an open blocker they own, and a task whose status IS "needs you".

    This function exists because those two facts used to be counted in three
    places with two different answers — the round tab badge said 4 (blockers +
    needs-you tasks) while the "Waiting on you" heading said 2 (blockers only),
    on the same round. Chris spotted it immediately, and he was right that they
    should be one data point. They are now: the badge, the heading count and
    the cards under it all come from here, so they cannot disagree again.

    The fix had to be additive rather than subtractive. Making the badge count
    blockers only would have squared the numbers while quietly breaking the
    page's core promise — a round whose only client-blocking item was a
    needs-you *task* would have shown no badge and printed "Nothing is waiting
    on you in this round" above an amber chip saying otherwise."""
    blockers = [
        b for b in R.visible(rnd.get("blockers"))
        if b["awaiting"] == "client" and not b.get("resolved_on")
    ]
    tasks = []
    for s in R.visible(rnd.get("sections")):
        for t in R.visible(s.get("tasks")):
            if t["status"] == "needs_client":
                tasks.append((s, t))
    return blockers, tasks


def render_banner(data, rounds, current, R):
    """The sticky banner, now carrying the round nav as a sub-sticky strip.

    Sticky rather than fixed. Chris asked for 'stays put while scrolling',
    which sticky delivers, without fixed's two costs: a body top-padding hack
    that breaks whenever the banner wraps on mobile, and a banner stamped over
    the content on every printed page.

    Everything round-scoped in here carries data-round and is toggled by the
    same JS that switches the panels, so the counts a client is reading always
    belong to the round they have selected."""
    proj = data["project"]
    ordered = sorted(rounds, key=lambda r: r["number"])

    stat_blocks, meters, subtitles, tabs = [], [], [], []
    for r in ordered:
        n = r["number"]
        rid = "r{}".format(slug(n))
        sel = n == current["number"]
        tally, total, pct = round_stats(r, R)
        # THREE PODS. It was five; the cut to two went one pod too far.
        # "Done" earned its place back — it is the counter clients most want
        # at a glance, and a green number is a friendlier opener than a bar.
        # "In progress" and "Needs you" stay out for the original reasons:
        # progress is answered by every section's own N/M count, and needs-you
        # by the amber badge on the round tab two inches away. The icon sits
        # on the same line as the label.
        stats = [
            ("done", "Done", tally["done"], "circle-check"),
            ("review", "Review", tally["review"], "eye"),
            ("blocked", "Blocked", tally["blocked"], "circle-minus"),
        ]
        stat_blocks.append(
            '<div class="banner-stats" data-round="{n}"{hid}>{s}</div>'.format(
                n=esc(n),
                hid="" if sel else " hidden",
                s="".join(
                    '<div class="stat t-{t}"><b>{v}</b>'
                    '<span class="stat-l">{ic}{l}</span></div>'.format(
                        t=tone, ic=icon(ic, "ic stat-ic"), v=v, l=label
                    )
                    for tone, label, v, ic in stats
                ),
            )
        )
        meters.append(
            '<div class="meter" data-round="{n}"{hid} role="img" '
            'aria-label="Round {n}: {p} percent of items complete">'
            '<i style="width:{p}%"></i></div>'.format(
                n=esc(n), hid="" if sel else " hidden", p=pct
            )
        )
        subtitles.append(
            '<span class="banner-sub" data-round="{n}"{hid}>Round {n} '
            "&middot; {lbl}</span>".format(
                n=esc(n), hid="" if sel else " hidden", lbl=esc(r["label"])
            )
        )

        # The count badge is decorative-with-meaning: sighted users read it as
        # a number next to the tab, but concatenated into the accessible name
        # it comes out as "Round 2 2". So hide the glyph and spell it out.
        # The NUMBER comes from client_asks() — the same call the "Waiting on
        # you" heading makes — so the tab and the section always agree.
        ask_b, ask_t = client_asks(r, R)
        n_you = len(ask_b) + len(ask_t)
        badge = (
            '<span class="tab-badge t-client" aria-hidden="true">{n}</span>'
            '<span class="sr-only">, {n} waiting on you</span>'.format(n=n_you)
            if n_you
            else ""
        )
        tabs.append(
            '<button type="button" role="tab" id="tab-{rid}" class="tab" '
            'data-round="{num}" aria-controls="panel-{rid}" aria-selected="{sel}" '
            'tabindex="{ti}">Round {num}{badge}</button>'.format(
                rid=rid,
                num=esc(n),
                sel="true" if sel else "false",
                ti="0" if sel else "-1",
                badge=badge,
            )
        )

    mode_flag = (
        '<span class="mode-flag">Internal build — not for the client</span>'
        if R.internal
        else ""
    )

    return """<header class="banner" role="banner">
  <div class="banner-in">
    <div class="banner-id">
      <h1>{client}</h1>
      <p><span class="proj-title">{title} &middot; </span><span
        class="proj-updated">updated {updated}</span>{mode}</p>
      <p>{subs}</p>
    </div>
    {stats}
  </div>
  {meters}
  <nav class="roundnav" aria-label="Rounds">
    <div class="tabs" role="tablist" aria-label="Select a round">{tabs}</div>
  </nav>
</header>""".format(
        client=esc(proj["client"]),
        title=esc(proj["title"]),
        updated=esc(proj.get("updated") or date.today().isoformat()),
        mode=(" " + mode_flag) if mode_flag else "",
        subs="".join(subtitles),
        stats="".join(stat_blocks),
        meters="".join(meters),
        tabs="".join(tabs),
    )


# --- blockers ---------------------------------------------------------------
def render_blocker_card(b, R, P):
    """An OPEN blocker. Resolved ones are simply not rendered.

    No status chip in front of the title: the <h2> the card sits under already
    says 'Waiting on you'. Repeating it on every card is the same fact three
    times. NO "URGENT" CHIP EITHER, for the same reason one level up — if it
    is under "Waiting on you" or "Waiting on us" then it is urgent by
    definition, so `severity` is accepted in the data and never drawn. The card
    is near-neutral: colour is carried by the heading, the banner counter and
    the tab badge."""
    ask = (
        '<div class="ask"><b>What we need:</b> {}</div>'.format(esc(b["ask"]))
        if b.get("ask")
        else ""
    )
    return (
        '<article class="card blocker" id="{aid}">'
        '<div class="card-head"><h3 class="card-title">{title}</h3>{meta}</div>'
        "{ptag}"
        '<p class="prose">{impact}</p>{ask}{note}{foot}'
        "</article>"
    ).format(
        aid=slug(b["id"]),
        title=esc(b["title"]),
        meta=R.meta_pills(b),
        ptag='<div class="ptags">{}</div>'.format(P.tag(b.get("page")))
        if b.get("page")
        else "",
        impact=esc(b["impact"]),
        ask=ask,
        note=R.note(b),
        foot=card_foot(b.get("raised_on"), b.get("links")),
    )


def render_ask_card(s, t, R, P):
    """A task whose status IS "needs you", shown under Waiting on you.

    Deliberately the SAME card shape as a blocker: a client should not have to
    learn two layouts for "things I have to do". The ref is a link to the row
    in the work table below, which is what keeps this a pointer rather than a
    second copy of the record — click P-05 and you land on P-05."""
    return (
        '<article class="card">'
        '<div class="card-head"><h3 class="card-title">{title}</h3>'
        '<a class="ref reflink" href="#{aid}">{ref}</a>{meta}</div>'
        "{ptag}{body}{foot}</article>"
    ).format(
        aid=slug(t["id"]),
        ref=esc(t["id"]),
        title=esc(t["title"]),
        meta=R.meta_pills(t),
        ptag='<div class="ptags">{}</div>'.format(
            P.tag(t.get("page") or s.get("page"))
        ) if (t.get("page") or s.get("page")) else "",
        body=R.body(t),
        foot=card_foot(t.get("created"), t.get("links")),
    )


def render_waiting(rnd, awaiting, R, P):
    """One <h2> landmark per side. Both are ALWAYS rendered, even when empty:
    'Waiting on you — nothing right now' is the single most valuable sentence
    on the page, and a landmark that disappears is a landmark you cannot
    learn.

    The client side lists blockers AND needs-you tasks, both from
    client_asks() — see the long note there for why the count and the contents
    have to come from one place."""
    label, tone, ic = AWAITING[awaiting]
    if awaiting == "client":
        blockers, tasks = client_asks(rnd, R)
        cards = [render_blocker_card(b, R, P) for b in blockers]
        cards += [render_ask_card(s, t, R, P) for s, t in tasks]
    else:
        blockers = [
            b for b in R.visible(rnd.get("blockers"))
            if b["awaiting"] == "agency" and not b.get("resolved_on")
        ]
        cards = [render_blocker_card(b, R, P) for b in blockers]

    n = len(cards)
    if cards:
        inner = '<div class="cards">{}</div>'.format("".join(cards))
    else:
        inner = '<p class="empty">{}</p>'.format(
            "Nothing is waiting on you in this round."
            if awaiting == "client"
            else "Nothing is outstanding on our side in this round."
        )
    # Same trap as the tab badge: a bare number inside the heading concatenates
    # into the accessible name and comes out as "Waiting on you 2". Hide the
    # glyph, spell the fact out.
    count = h2_count(n, tone, "item" if n == 1 else "items")
    return (
        '<section class="lm" aria-labelledby="{aid}">'
        '<h2 class="lm-h" id="{aid}">{ic}<span>{label}</span>{count}</h2>{inner}</section>'
    ).format(aid="h-{}-{}".format(awaiting, slug(rnd["number"])),
             ic=icon(ic, "ic lm-ic"), label=label, count=count, inner=inner)


# --- the work ---------------------------------------------------------------
#
# A SECTION IS (USUALLY) A PAGE, AND ITS TASKS ARE A TABLE.
#
# Chris: "these groups, I would like them to, most of the time, be actual
# pages, and the items underneath would be all the changes we made to these
# pages... with the actual tasks, I'm actually thinking about this more like a
# table."
#
# The cards this replaced carried the same six facts as a table row, but each
# one had to be found in a different place on every card, so reading twelve of
# them was twelve separate hunts. A table puts each fact in a fixed column and
# a reader learns the layout once. Two columns are conditional, because a
# column that is empty on every row is worse than no column:
#
#   * "Held on" appears only if something in the section is blocked.
#   * "Page" appears only if a row points somewhere the section header does
#     not already point — which is Chris's "if all of these things are on the
#     same page, we don't need that last column", falling out of the data
#     rather than needing a flag.
# ---------------------------------------------------------------------------
def task_page_cell(t, s, P):
    """Where this ONE row points, if that is not simply 'the section's page'.

    Own links win (they are the specific thing to look at); otherwise the
    registry URL for a page the row does not share with its section. A row on
    the section's own page returns "" — which is what collapses the column."""
    own = [l for l in (t.get("links") or []) if safe_url(l.get("url"))]
    if own:
        return "".join(anchor_compact(l["url"], l.get("label")) for l in own)
    pid = t.get("page")
    if pid and pid != s.get("page") and P.url(pid):
        return anchor_compact(P.url(pid), P.label(pid))
    return ""


def task_held_cell(t, blockers_by_id):
    """What this row is waiting on, and whose move it is."""
    if not t.get("blocked_by"):
        return ""
    b = blockers_by_id.get(t["blocked_by"])
    if not b:
        return ""
    aw_label, aw_tone, _ic = AWAITING[b["awaiting"]]
    return '<span class="held t-{}">{}</span><span class="held-t">{}</span>'.format(
        aw_tone, esc(aw_label), esc(b["title"])
    )


def status_cell(t):
    """Status as a bare glyph, in the FIRST column, with no column heading.

    Three separate reductions, all Chris's: move it left, drop the header
    label, drop the words. It works because it is the one column that is
    identical in shape on every row — the eye reads a stripe of glyphs down
    the left edge instead of re-reading a word per row — and because the
    colour is never doing the work alone: each of the seven statuses has its
    own distinct glyph, a `title` for hover, and an .sr-only word. The column
    heading is .sr-only rather than absent, so the table still announces seven
    columns and screen-reader column navigation still works."""
    label, tone, ic = STATUS[t["status"]]
    return (
        '<td class="c-status"><span class="st t-{tone}" title="{lbl}">{ic}'
        '<span class="sr-only">{lbl}</span></span></td>'
    ).format(tone=tone, lbl=esc(label), ic=icon(ic))


def task_row(t, s, blockers_by_id, R, P, show_held, show_page):
    tone = STATUS[t["status"]][1]
    created = t.get("created")
    cells = [
        status_cell(t),
        # Plain text, no .ref chip: a bordered pill per row turned the Ref
        # column into a stack of buttons nobody can press. The chip class
        # survives for reflinks on cards, where it IS a link.
        '<td class="c-ref">{}</td>'.format(esc(t["id"])),
        '<th scope="row" class="c-item">{}{}</th>'.format(
            esc(t["title"]), R.meta_pills(t)
        ),
        '<td class="c-detail">{}</td>'.format(R.body(t)),
    ]
    if show_held:
        cells.append(
            '<td class="c-held">{}</td>'.format(task_held_cell(t, blockers_by_id))
        )
    cells.append(
        '<td class="c-date">{}</td>'.format(
            '<time datetime="{iso}" title="{iso}">{short}</time>'.format(
                iso=E(str(created)), short=short_date(created)
            )
            if created
            else "&mdash;"
        )
    )
    if show_page:
        cells.append('<td class="c-page">{}</td>'.format(task_page_cell(t, s, P)))
    return '<tr id="{}" class="s-{}">{}</tr>'.format(
        slug(t["id"]), tone, "".join(cells)
    )


def section_meta(s, tally, done, total, P):
    """The right-hand end of a section header, in Chris's stated order:
    flag, then the page link, then the count.

    THE FLAG IS NOW ONLY AN ICON. It used to be a full chip ("Needs you"),
    which put a second amber pill on a page whose amber already has one job.
    An icon in the header is a pointer — "there is something for you inside" —
    and the row it refers to still carries the words.

    Both controls carry an .sr-only label, because the page's own rule is that
    no icon is ever the sole carrier of meaning (see the ICONS note above)."""
    flag = ""
    if tally["needs_client"]:
        flag = (
            '<span class="flag t-client" title="Something here needs you">{}'
            '<span class="sr-only">Needs you</span></span>'
        ).format(icon("circle-exclamation"))
    elif tally["review"]:
        flag = (
            '<span class="flag t-review" title="Ready for your review">{}'
            '<span class="sr-only">Ready for your review</span></span>'
        ).format(icon("eye"))

    # The section's page, as a link, when the registry knows a URL for it.
    # This is the "these groups are actual pages" idea made clickable: the
    # header links to the page, the rows describe what changed on it.
    link = ""
    url = P.url(s.get("page"))
    if url:
        link = (
            '<a class="pagelink" href="{u}" rel="noopener" title="Open {l}">{ic}'
            '<span class="sr-only">Open {l}</span></a>'
        ).format(u=E(url), l=esc(P.label(s["page"])),
                 ic=icon("arrow-up-right-from-square"))

    count = (
        '<span class="tally"><span aria-hidden="true">{d}/{t}</span>'
        '<span class="sr-only">{d} of {t} done</span></span>'
    ).format(d=done, t=total)
    return '<span class="sect-meta">{}{}{}</span>'.format(flag, link, count)


def render_section(s, rnd_is_current, blockers_by_id, R, P):
    tasks = R.visible(s.get("tasks"))
    if not tasks and not s.get("summary"):
        return ""

    # A task with no page of its own inherits the section's. Sections in a QA
    # round usually ARE pages, so this saves repeating `"page"` on every row.
    # Copied, not mutated — the caller's data is not ours to edit.
    if s.get("page"):
        tasks = [dict(t, page=t.get("page") or s["page"]) for t in tasks]

    tally = dict((k, 0) for k in STATUS)
    for t in tasks:
        tally[t["status"]] += 1
    done = tally["done"]

    # Default open state: only the current round's unfinished sections. A
    # section where everything is done is noise on first read, and a closed
    # round should open to a one-line answer, not 40 rows.
    explicit = s.get("open")
    is_open = explicit if explicit is not None else (
        rnd_is_current and done < len(tasks)
    )

    show_held = any(task_held_cell(t, blockers_by_id) for t in tasks)
    show_page = any(task_page_cell(t, s, P) for t in tasks)

    head = ['<th scope="col" class="c-status"><span class="sr-only">Status</span></th>',
            '<th scope="col">Ref</th>', '<th scope="col">Item</th>',
            '<th scope="col">Detail</th>']
    if show_held:
        head.append('<th scope="col">Held on</th>')
    head.append('<th scope="col">Added</th>')
    if show_page:
        head.append('<th scope="col">Page</th>')

    body = ""
    if tasks:
        body = (
            '<div class="tablewrap"><table class="wtable">'
            '<caption class="sr-only">{cap}</caption>'
            "<thead><tr>{head}</tr></thead><tbody>{rows}</tbody></table></div>"
        ).format(
            cap="Items in {}".format(esc(s["title"])),
            head="".join(head),
            rows="".join(
                task_row(t, s, blockers_by_id, R, P, show_held, show_page)
                for t in tasks
            ),
        )

    return (
        '<details class="sect" id="sect-{sid}"{op}>'
        '<summary><h3 class="sect-title">{title}</h3>{meta}</summary>'
        '<div class="sect-body">{summary}{body}</div>'
        "</details>"
    ).format(
        sid=slug(s["id"]),
        op=" open" if is_open else "",
        title=esc(s["title"]),
        meta=section_meta(s, tally, done, len(tasks), P),
        summary=R.body(s),
        body=body,
    )


def render_work(rnd, is_current, blockers_by_id, R, P):
    sections = "".join(
        render_section(s, is_current, blockers_by_id, R, P)
        for s in R.visible(rnd.get("sections"))
    )
    if not sections:
        sections = '<p class="empty">Nothing is scheduled in this round yet.</p>'
    aid = "h-work-{}".format(slug(rnd["number"]))
    return (
        '<section class="lm" aria-labelledby="{aid}">'
        '<h2 class="lm-h" id="{aid}">{ic}<span>The work in this round</span></h2>'
        "{sections}</section>"
    ).format(aid=aid, ic=icon("list-check", "ic lm-ic"), sections=sections)


# --- history ----------------------------------------------------------------
#
# THERE IS NO "RESOLVED" SECTION ANY MORE. It was a table of blockers that had
# been cleared, and once the work became a table with its own Status column it
# was the third place on one page telling a client that something finished —
# after the Done chips and after History. Chris: "with the edits to the work in
# this round section, it makes me think that we don't need the resolved section
# at all." A blocker with `resolved_on` set simply stops rendering: it leaves
# "Waiting on you", and what actually changed is in History, which is where a
# client goes to ask "what happened to the thing I sent you".
#
# `resolved_on` therefore still does exactly one job — it is what takes a
# blocker off the board. `resolution` is now unused by the renderer; it is left
# in the schema because it is worth writing into the matching changelog entry.
# ---------------------------------------------------------------------------
def changelog_state_cell(c):
    """State as a bare first-column glyph — the exact status_cell pattern,
    over the changelog's own smaller vocabulary. Same class, same sr-only
    word, same title, so the print stylesheet's glyph-to-word swap and any
    skin recolouring apply here for free."""
    label, tone, ic = CHANGE_STATE[c["state"]]
    return (
        '<td class="c-status"><span class="st t-{tone}" title="{lbl}">{ic}'
        '<span class="sr-only">{lbl}</span></span></td>'
    ).format(tone=tone, lbl=esc(label), ic=icon(ic))


def changelog_link_cell(c):
    """The entry's own links, compacted to 'Link' — same treatment as the
    work table's Page column."""
    links = [l for l in (c.get("links") or []) if safe_url(l.get("url"))]
    return "".join(anchor_compact(l["url"], l.get("label")) for l in links)


def changelog_row(c, R, show_link):
    """One history line as a table row.

    History used to be a stack of cards; the work section is a table. Chris
    read them side by side and the cards lost: same kind of record, two
    layouts, twice the height. So history reuses the work table's classes
    verbatim (.wtable/.c-status/.c-ref/.c-item/.c-detail/.c-date/.c-page) —
    which is not laziness but the point: the skin and the print stylesheet
    were written against those classes and now apply here automatically.
    The row class is the changelog's own state key (`s-shipped`…), so the
    work table's done/deferred dimming does not blanket a table whose every
    row is in the past."""
    why = '<p class="prose">{}</p>'.format(esc(c["why"])) if c.get("why") else ""
    why += R.note(c)
    cells = [
        changelog_state_cell(c),
        '<td class="c-ref">{}</td>'.format(
            esc(c["ref"]) if c.get("ref") else "&mdash;"
        ),
        '<th scope="row" class="c-item">{}</th>'.format(esc(c["what"])),
        '<td class="c-detail">{}</td>'.format(why),
        '<td class="c-date">{}</td>'.format(
            '<time datetime="{iso}" title="{iso}">{short}</time>'.format(
                iso=E(str(c["date"])), short=short_date(c["date"])
            )
            if c.get("date")
            else "&mdash;"
        ),
    ]
    if show_link:
        cells.append('<td class="c-page">{}</td>'.format(changelog_link_cell(c)))
    return '<tr class="s-{}">{}</tr>'.format(esc(c["state"]), "".join(cells))


def changelog_table(recs, caption, R):
    """A fold's worth of history, newest first, as a work-style table. The
    Link column only exists when some row in THIS fold has a link — a column
    of empty cells is width spent saying nothing."""
    recs = sorted(recs, key=lambda x: x.get("date", ""), reverse=True)
    show_link = any(changelog_link_cell(c) for c in recs)
    head = [
        '<th scope="col" class="c-status"><span class="sr-only">State</span></th>',
        '<th scope="col">Ref</th>', '<th scope="col">What</th>',
        '<th scope="col">Why</th>', '<th scope="col">Date</th>',
    ]
    if show_link:
        head.append('<th scope="col">Link</th>')
    return (
        '<div class="tablewrap"><table class="wtable">'
        '<caption class="sr-only">{cap}</caption>'
        "<thead><tr>{head}</tr></thead><tbody>{rows}</tbody></table></div>"
    ).format(
        cap=esc(caption),
        head="".join(head),
        rows="".join(changelog_row(c, R, show_link) for c in recs),
    )


def render_history(rnd, entries, loose, R, P):
    """The full changelog FOR THE SELECTED ROUND, grouped by page.

    ONE LAYER OF ACCORDION, NOT TWO. This used to be a closed "Every change we
    made in round N" wrapper containing a fold per page — so reading one page's
    history was two clicks, and the outer wrapper's only job was to keep the
    section short. The page folds do that themselves, and they say which page
    while they do it. So the wrapper is gone and the folds sit directly under
    the <h2>, closed: the section is one screen of page names with counts, and
    opening one is one click.

    The old rule survives intact — a client opening the page sees status first
    and history only if they go looking — it is now enforced by the folds being
    closed rather than by a lid on top of them."""
    if not entries and not loose:
        return ""

    def fold(pid, label, recs, extra=""):
        return (
            '<details class="fold{ex}"><summary><span class="grow-lbl">{ic}{lbl}'
            '</span><span class="tally">{n}</span></summary>'
            "<div>{table}</div></details>"
        ).format(
            ex=extra,
            ic=icon("globe" if pid is None else "file-lines", "ic grow-ic"),
            lbl=esc(label),
            n=len(recs),
            table=changelog_table(recs, "Changes for {}".format(label), R),
        )

    groups = P.group(entries)
    if not entries:
        inner = '<p class="empty">Nothing logged against this round yet.</p>'
    elif len(groups) == 1 and groups[0][0] is None:
        # A round with no page associations at all: skip the group furniture
        # entirely rather than render one lonely "Site-wide" fold around
        # everything, which says nothing and costs a click.
        inner = changelog_table(entries, "Changes in this round", R)
    else:
        inner = "".join(fold(pid, lbl, recs) for pid, lbl, recs in groups)

    # Entries with no `round` are genuinely not round-scoped (kickoff, hosting,
    # contracts). They appear in every round's history, clearly labelled,
    # rather than vanishing because History is now per-round.
    if loose:
        inner += fold(None, "Not tied to a specific round", loose, extra=" loose")

    aid = "h-log-{}".format(slug(rnd["number"]))
    return (
        '<section class="lm" aria-labelledby="{aid}">'
        '<h2 class="lm-h" id="{aid}">{ic}<span>History</span>{n}</h2>'
        '<p class="lm-sub">Every change we made in round {num}, and why.</p>'
        '<div class="folds">{inner}</div></section>'
    ).format(
        aid=aid,
        ic=icon("clock-rotate-left", "ic lm-ic"),
        num=esc(rnd["number"]),
        # Grey, not green: this is a count of log lines, and some of them are
        # "Reverted" or "Deferred". A green 9 next to History would claim nine
        # things shipped.
        n=h2_count(len(entries), "queued",
                   "entry" if len(entries) == 1 else "entries"),
        inner=inner,
    )


# --- the round panel --------------------------------------------------------
def render_lede(rnd, proj, R):
    """The round's opening note — a short letter, not a status row.

    It used to be a state chip, a date stamp and a paragraph sharing one flex
    row: three unrelated things in a line, led by a chip that repeated what
    the selected tab already said. Chris asked for "an introductory letter in
    its own little pod that gives a one-paragraph overview of everything
    that's been done, who the note was written by, and then the date. No
    statuses, no nothing."

    So: the paragraph, then a signature. `state` is still required and still
    validated — it just no longer draws anything, same as `severity`.

    The signature degrades in order: `note_by` on the round, else the
    project's `author`; `note_on`, else the round's `closed` or `opened` date,
    else the project's `updated`. If none of that resolves, the pod is just
    the paragraph rather than an empty signature rule."""
    body = R.body(rnd)
    if not body:
        return ""
    by = rnd.get("note_by") or proj.get("author")
    on = (rnd.get("note_on") or rnd.get("closed") or rnd.get("opened")
          or proj.get("updated"))
    sig = ""
    if by or on:
        bits = []
        if by:
            bits.append("<b>{}</b>".format(esc(by)))
        if on:
            bits.append('<time datetime="{}">{}</time>'.format(
                E(str(on)), long_date(on)))
        sig = '<p class="letter-sig">&mdash; {}</p>'.format(
            " &middot; ".join(bits)
        )
    return '<section class="letter">{}{}</section>'.format(body, sig)


def render_panels(data, rounds, current, blockers_by_id, R, P):
    """One tabpanel per round. Selecting a tab swaps the whole page."""
    entries = R.visible(data.get("changelog"))
    loose = [c for c in entries if c.get("round") is None]

    proj = data["project"]
    out = []
    for r in sorted(rounds, key=lambda x: x["number"]):
        n = r["number"]
        rid = "r{}".format(slug(n))
        sel = n == current["number"]
        lede = render_lede(r, proj, R)
        mine = [c for c in entries if c.get("round") == n]
        out.append(
            '<div role="tabpanel" id="panel-{rid}" class="tabpanel" '
            'data-round="{n}" data-round-label="Round {n} &mdash; {lbl}" '
            'aria-labelledby="tab-{rid}"{hid}>{lede}{you}{us}{work}{log}</div>'.format(
                rid=rid,
                n=esc(n),
                lbl=esc(r["label"]),
                hid="" if sel else " hidden",
                lede=lede,
                you=render_waiting(r, "client", R, P),
                us=render_waiting(r, "agency", R, P),
                work=render_work(r, sel, blockers_by_id, R, P),
                log=render_history(r, mine, loose, R, P),
            )
        )
    return "".join(out)


def render_legend(R):
    """A client who does not know what the colours mean gets no value from
    them. One accordion, closed, explaining the language.

    FOUR COLOURS AND A GREY, and this list has to stay exactly as long as the
    number of colours the page actually uses. It listed seven when the palette
    had seven; a key that names a colour the page never shows is worse than no
    key, because it teaches a distinction the reader then goes looking for."""
    rows = "".join(
        '<li><span class="swatch t-{tone}">{ic}</span><b>{label}</b>'
        "<span>{desc}</span></li>".format(
            tone=tone, ic=icon(ic, "ic sw-ic"), label=label, desc=desc
        )
        for label, tone, ic, desc in [
            ("Done", "done", "circle-check",
             "Finished and checked. Nothing further needed."),
            ("Review", "review", "eye",
             "Built and live for you to look at — we are waiting on your yes."),
            ("Needs you", "client", "circle-exclamation",
             "We cannot finish this without something from you. Amber on this "
             "page always and only means your move."),
            ("Blocked", "blocked", "circle-minus",
             "Held up by something else. The row says what."),
            ("Everything else", "queued", "circle-half-stroke",
             "In progress, queued or deferred. It is on our list and there is "
             "nothing for you to do."),
        ]
    )
    return (
        '<section class="lm" aria-labelledby="key-h">'
        '<h2 class="lm-h" id="key-h">{ic}<span>What the colours mean</span></h2>'
        '<details class="logwrap"><summary><span class="rlabel">Open the key'
        '</span></summary><div class="round-body">'
        '<ul class="legend">{rows}</ul></div></details></section>'
    ).format(ic=icon("palette", "ic lm-ic"), rows=rows)


# ---------------------------------------------------------------------------
# CSS — kept as a plain string (not an f-string) so nobody has to double every
# brace in a stylesheet. Brand hooks are injected separately.
# ---------------------------------------------------------------------------
CSS = """
:root{
  /* Surfaces + text. Light-first: this page is printed to PDF by clients,
     and a dark page prints as a black rectangle or a washed-out mess. */
  --canvas:#f5f6f8; --surface:#fff; --surface-2:#f0f2f5;
  --ink:#1b2230; --ink-2:#5a6474; --line:#dde2e9;
  --radius:10px; --shadow:0 1px 2px rgba(20,28,44,.06),0 4px 14px rgba(20,28,44,.05);
  /* Deep-link landing offset. Must clear the sticky banner INCLUDING the round
     nav, or #p-04 lands underneath it. RE-MEASURE THESE if the banner grows a
     row: `document.querySelector('.banner').getBoundingClientRect().height`
     at 1280 and at 375. Currently 135 / 184, rounded up. Err HIGH: landing a
     little low is invisible, landing under the banner hides the thing you
     linked to. */
  --banner-h:145px;

  /* Brand hook — overridden from JSON, never by editing this template. */
  --accent:#1f5f9e; --accent-2:#1f7a4d;

  /* THE COLOUR LANGUAGE. Each state gets ink / tint / border.
     Nothing in the markup may use a literal colour.
     FOUR COLOURS AND A GREY — green done, purple review, amber your-move,
     red blocked, grey everything else. The old blue `progress` tone was
     deleted, not merely unused: "in progress" and "queued" say the same thing
     to a client as "waiting on us", and three greys reading as three states
     is exactly the noise this revision set out to remove. */
  --t-done:#1a6f47;      --t-done-bg:#e6f4ed;      --t-done-br:#bfe0d0;
  --t-review:#63489f;    --t-review-bg:#efeafa;    --t-review-br:#d5c9ee;
  --t-client:#9a5a00;    --t-client-bg:#fdf1dd;    --t-client-br:#f0d3a3;
  --t-blocked:#a82f2a;   --t-blocked-bg:#fbe9e7;   --t-blocked-br:#f0c4c0;
  --t-queued:#4d5768;    --t-queued-bg:#eceff3;    --t-queued-br:#d3d9e2;
  --t-deferred:#78828f;  --t-deferred-bg:#f2f4f7;  --t-deferred-br:#dfe3e9;
}
.t-done{--tone:var(--t-done);--tone-bg:var(--t-done-bg);--tone-br:var(--t-done-br)}
.t-review{--tone:var(--t-review);--tone-bg:var(--t-review-bg);--tone-br:var(--t-review-br)}
.t-client{--tone:var(--t-client);--tone-bg:var(--t-client-bg);--tone-br:var(--t-client-br)}
.t-blocked{--tone:var(--t-blocked);--tone-bg:var(--t-blocked-bg);--tone-br:var(--t-blocked-br)}
.t-queued{--tone:var(--t-queued);--tone-bg:var(--t-queued-bg);--tone-br:var(--t-queued-br)}
.t-deferred{--tone:var(--t-deferred);--tone-bg:var(--t-deferred-bg);--tone-br:var(--t-deferred-br)}

@media (prefers-color-scheme:dark){
  :root{
    --canvas:#14171d; --surface:#1b1f27; --surface-2:#22272f;
    --ink:#e8ebf0; --ink-2:#9aa4b4; --line:#2c323c;
    --shadow:0 1px 2px rgba(0,0,0,.4);
    --t-done:#5fc796;      --t-done-bg:#12291f;      --t-done-br:#26523c;
    --t-review:#b096e8;    --t-review-bg:#1f1a2e;    --t-review-br:#3d3159;
    --t-client:#ecac52;    --t-client-bg:#2b2113;    --t-client-br:#5c4520;
    --t-blocked:#ef8c86;   --t-blocked-bg:#2c1817;   --t-blocked-br:#5c2f2b;
    --t-queued:#9aa4b4;    --t-queued-bg:#22272f;    --t-queued-br:#39404b;
    --t-deferred:#7c8695;  --t-deferred-bg:#1e232a;  --t-deferred-br:#333a44;
  }
}

*,*::before,*::after{box-sizing:border-box}
/* The round switcher toggles the `hidden` attribute, and half the things it
   toggles are flex containers — whose `display` would otherwise beat `hidden`
   and leave every round's counters on screen at once. This one line is what
   makes "one round at a time" true. */
[hidden]{display:none!important}
html{-webkit-text-size-adjust:100%}
body{margin:0;background:var(--canvas);color:var(--ink);overflow-x:hidden;
  font:15px/1.55 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif}
h1,h2,h3,h4{margin:0;font-weight:650;line-height:1.25}
p{margin:0}
.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;
  clip:rect(0 0 0 0);white-space:nowrap;border:0}
.sprite{position:absolute;width:0;height:0;overflow:hidden}
.wrap{max-width:1080px;margin:0 auto;padding:1.4rem 1.1rem 4rem}
.prose{color:var(--ink-2);font-size:.9rem;margin-top:.3rem;max-width:76ch}
.empty{color:var(--ink-2);font-size:.92rem;background:var(--surface);
  border:1px solid var(--line);border-radius:var(--radius);padding:.8rem .95rem}
.ref{font:600 11px/1.4 ui-monospace,SFMono-Regular,Menlo,monospace;
  color:var(--ink-2);background:var(--surface-2);border:1px solid var(--line);
  border-radius:4px;padding:1px 5px;white-space:nowrap}
a.reflink{text-decoration:none}
a.reflink:hover{color:var(--accent);border-color:var(--accent)}
.stamp{display:inline-flex;align-items:center;gap:.3rem;font-size:11.5px;color:var(--ink-2)}
a{color:var(--accent)}
/* Links read as BOLD BODY TEXT WITH AN UNDERLINE, not as a brand colour.
   The accent is a client's own brand green, chosen to look right on their
   site — and "Upload folder" set in it, at 13px, against a white card, was
   the hardest-to-read string on the page. Weight plus an underline is a
   stronger link affordance than hue anyway, and it survives whatever accent
   the next child theme brings. */
.lnk{display:inline-flex;align-items:center;gap:.3rem;font-size:.82rem;
  font-weight:700;color:var(--ink);text-decoration:underline;
  text-underline-offset:2px;text-decoration-thickness:1px}
.lnk:hover{text-decoration-thickness:2px}
:focus-visible{outline:2px solid var(--accent);outline-offset:2px;border-radius:4px}

/* ---- icons. Decorative, always beside a text label. ---- */
.ic{width:1em;height:1em;fill:currentColor;flex:0 0 auto;vertical-align:-.125em}
.lm-ic{width:.86em;height:.86em;color:var(--accent);opacity:.85}
.stat-ic{width:.8em;height:.8em;color:var(--tone);opacity:.8}
.grow-ic{color:var(--ink-2)}
.sw-ic{width:.72em;height:.72em;color:var(--tone)}

/* ---- banner (sticky) + the round nav that lives inside it ---- */
.banner{position:sticky;top:0;z-index:20;background:var(--surface);
  border-bottom:1px solid var(--line);box-shadow:var(--shadow)}
.banner-in{max-width:1080px;margin:0 auto;padding:.7rem 1.1rem;
  display:flex;gap:1rem;align-items:center;flex-wrap:wrap}
.banner-id h1{font-size:1.05rem}
.banner-id{min-width:0;flex:1 1 15rem}
.banner-id p{font-size:.79rem;color:var(--ink-2);margin-top:.1rem}
.banner-sub{font-weight:600;color:var(--ink)}
.banner-stats{display:flex;gap:.35rem;flex-wrap:wrap;margin-left:auto}
.stat{background:var(--tone-bg);border:1px solid var(--tone-br);border-radius:8px;
  padding:.25rem .55rem;min-width:66px;text-align:center;line-height:1.15}
.stat b{display:block;font-size:1.02rem;color:var(--tone)}
/* Icon on the same baseline as the label — it used to sit alone above the
   number, which read as a third row of the pod rather than as part of it. */
.stat-l{display:inline-flex;align-items:center;justify-content:center;gap:.25rem;
  font-size:10.5px;color:var(--ink-2);white-space:nowrap}
.meter{height:3px;background:var(--surface-2)}
.meter i{display:block;height:100%;
  background:linear-gradient(90deg,var(--accent),var(--accent-2))}
.roundnav{background:var(--surface-2);border-top:1px solid var(--line)}
.tabs{max-width:1080px;margin:0 auto;padding:.35rem 1.1rem;display:flex;gap:.3rem;
  overflow-x:auto;scrollbar-width:thin}
.tab{font:inherit;font-size:.85rem;font-weight:600;cursor:pointer;color:var(--ink-2);
  background:transparent;border:1px solid transparent;border-radius:8px;
  padding:.32rem .75rem;display:inline-flex;align-items:center;gap:.35rem;
  white-space:nowrap;flex:0 0 auto}
.tab:hover{color:var(--ink);background:var(--surface)}
.tab[aria-selected=true]{background:var(--surface);color:var(--ink);
  border-color:var(--line);box-shadow:var(--shadow)}
.tab-badge{background:var(--tone-bg);color:var(--tone);border:1px solid var(--tone-br);
  border-radius:999px;font-size:10.5px;font-weight:700;padding:0 5px;min-width:17px;
  text-align:center}

/* ---- the h2 landmarks. These are the page's signposts: big, spaced, and
       ruled off, because the old small-caps labels read as captions. ---- */
.lm-h{display:flex;align-items:center;gap:.55rem;
  font-size:clamp(1.32rem,1.05rem + 1.1vw,1.72rem);letter-spacing:-.01em;
  margin:3.6rem 0 1.05rem;padding-bottom:.5rem;border-bottom:1px solid var(--line)}
.lm:first-of-type>.lm-h{margin-top:2rem}
.lm-sub{color:var(--ink-2);font-size:.88rem;margin:-.6rem 0 .8rem}
.h2-count{font-size:.68em;font-weight:700;line-height:1.7;color:var(--tone);
  background:var(--tone-bg);border:1px solid var(--tone-br);border-radius:999px;
  padding:0 .55em;margin-left:.1em}

/* ---- the round letter (what this round is, under the tabs) ----
       A pod, not a status row. Narrower than full width on purpose: it reads
       as prose, and prose past ~76ch stops being read. */
.letter{background:var(--surface);border:1px solid var(--line);
  border-radius:var(--radius);box-shadow:var(--shadow);
  padding:1rem 1.15rem;margin-top:1.6rem;max-width:46rem}
.letter .prose{font-size:.95rem;color:var(--ink);margin-top:0;max-width:none}
.letter .prose+.prose{margin-top:.5rem}
.letter-sig{margin-top:.7rem;font-size:.84rem;color:var(--ink-2)}
.letter-sig b{color:var(--ink);font-weight:650}

/* ---- chips ---- */
.chip{display:inline-flex;align-items:center;gap:.28rem;background:var(--tone-bg);
  color:var(--tone);border:1px solid var(--tone-br);border-radius:999px;
  padding:1px 8px;font-size:11.5px;font-weight:600;white-space:nowrap}
.swatch{display:inline-flex;align-items:center;justify-content:center;width:18px;
  height:18px;border-radius:4px;background:var(--tone-bg);border:1px solid var(--tone);
  flex:0 0 auto}
.tally{font:600 11.5px ui-monospace,Menlo,monospace;color:var(--ink-2);
  margin-left:auto;white-space:nowrap}

/* ---- cards. NEAR-NEUTRAL BY DESIGN: the <h2> above already says which
       state these are in, so painting them repeats it. ---- */
.cards{display:grid;gap:.7rem;grid-template-columns:repeat(auto-fill,minmax(320px,1fr))}
.card{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);
  padding:.85rem .95rem;box-shadow:var(--shadow);display:flex;flex-direction:column}
/* flex-start, not center: a centred ref chip floats mid-height beside a
   two-line card title. */
.card-head{display:flex;gap:.45rem;align-items:flex-start;flex-wrap:wrap}
.card-title{font-size:1rem;flex:1 1 12rem}
.ptags{margin-top:.4rem}
.ptag{display:inline-flex;align-items:center;gap:.3rem;font-size:11.5px;
  color:var(--ink-2);background:var(--surface-2);border:1px solid var(--line);
  border-radius:5px;padding:1px 6px;text-decoration:none}
a.ptag:hover{color:var(--accent)}
/* The one place amber still lands inside a card — the actual ask. A rule,
   not a filled panel: enough to find, not enough to shout. */
.ask{margin-top:.55rem;font-size:.88rem;padding:.1rem 0 .1rem .65rem;
  border-left:3px solid var(--t-client);color:var(--ink)}
.card-foot{margin-top:auto;padding-top:.55rem;border-top:1px solid var(--line);
  display:flex;align-items:center;gap:.6rem;flex-wrap:wrap}
.card-foot .cf-l{margin-right:auto}
.card-foot .cf-r{display:flex;gap:.7rem;flex-wrap:wrap;justify-content:flex-end}
.card>.prose,.card>.ask,.card>.ptags{margin-bottom:.45rem}

/* ---- accordions ---- */
details{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius)}
.logwrap{margin-bottom:.6rem;box-shadow:var(--shadow);overflow:hidden}
summary{cursor:pointer;list-style:none;display:flex;align-items:center;gap:.55rem;
  padding:.7rem .9rem;font-weight:600;user-select:none}
summary::-webkit-details-marker{display:none}
summary::before{content:"";flex:0 0 auto;width:8px;height:8px;border-right:2px solid var(--ink-2);
  border-bottom:2px solid var(--ink-2);transform:rotate(-45deg);margin-right:.15rem;
  transition:transform .15s ease}
details[open]>summary::before{transform:rotate(45deg)}
summary:hover{background:var(--surface-2)}
.rlabel{font-size:.95rem;font-weight:600}
.round-body{padding:.2rem .9rem 1rem;border-top:1px solid var(--line)}
/* overflow:hidden (here and on .fold): the <summary> hover background is a
   full-width rectangle, and without clipping its corners paint square over
   the container's rounded ones. An element's own box-shadow is outside its
   own overflow clip, so skins that shadow these are unaffected. */
.sect{margin-top:.6rem;border-radius:8px;background:var(--surface-2);
  overflow:hidden}
.sect>summary{padding:.55rem .7rem}
.sect-title{font-size:.95rem;flex:0 1 auto}
.sect-body{padding:.1rem .7rem .7rem;border-top:1px solid var(--line)}
/* The right-hand end of a section header: flag, page link, count. Pinned
   right as one unit so the count stays in the same place on every row of
   headers no matter how long the title is. */
.sect-meta{margin-left:auto;display:inline-flex;align-items:center;gap:.55rem}
.sect-meta .tally{margin-left:0}
.flag{display:inline-flex;color:var(--tone);font-size:1rem;line-height:1}
.pagelink{display:inline-flex;color:var(--ink-2);font-size:.85rem;line-height:1;
  text-decoration:none;padding:2px}
.pagelink:hover{color:var(--accent)}
.folds{display:grid;gap:.5rem}
.fold{background:var(--surface-2);border-radius:8px;overflow:hidden}
.folds>.fold{background:var(--surface);box-shadow:var(--shadow)}
.fold>summary{padding:.55rem .8rem;font-size:.92rem}
.fold>summary+*{padding:0 .7rem .7rem}
.grow-lbl{display:inline-flex;align-items:center;gap:.4rem}
.fold.loose>summary{color:var(--ink-2);font-weight:500}

/* ---- the work table ----
   One row per item, one fact per column, so reading twelve items is one
   learned layout rather than twelve separate hunts around a card. */
.tablewrap{overflow-x:auto;background:var(--surface);border:1px solid var(--line);
  border-radius:8px;margin-top:.55rem}
.wtable{border-collapse:collapse;width:100%;min-width:40rem;font-size:.86rem}
.wtable th,.wtable td{text-align:left;padding:.5rem .7rem;vertical-align:top;
  border-bottom:1px solid var(--line)}
.wtable thead th{font-size:10.5px;letter-spacing:.06em;text-transform:uppercase;
  color:var(--ink-2);background:var(--surface-2);white-space:nowrap}
.wtable tr:last-child th,.wtable tr:last-child td{border-bottom:0}
.wtable tr.s-done,.wtable tr.s-deferred{opacity:.72}
/* Plain muted text, not a chip — see task_row(). */
.c-ref{width:1%;white-space:nowrap;font-size:11px;color:var(--ink-2)}
.c-item{font-weight:650;width:22%;min-width:9rem}
.c-detail{color:var(--ink-2);min-width:16rem}
.c-detail .prose{margin-top:0;font-size:.86rem}
.c-held{width:1%;min-width:9rem}
.held{display:block;color:var(--tone);font-weight:650;font-size:11.5px}
.held-t{display:block;color:var(--ink-2);font-size:.82rem;margin-top:.1rem}
.c-date{width:1%;white-space:nowrap;color:var(--ink-2);
  font:11.5px/1.6 ui-monospace,SFMono-Regular,Menlo,monospace}
/* Page labels wrap ("Emergency & After Hours"), and a centred flex icon then
   floats between the two lines. Pin it to the first line instead. */
/* "Link", not the page's full name — see anchor_compact(). */
.c-page{width:1%;white-space:nowrap}
/* Status: first column, glyph only, no visible column heading. */
.c-status{width:1%;padding-right:.25rem}
.st{display:inline-flex;color:var(--tone);font-size:1.05rem;line-height:1}
.c-status{width:1%;white-space:nowrap}
/* A deep link lands on a row, and a box-shadow round a <tr> renders as a
   floating rectangle in most engines. Outline-inset instead. */
:target{scroll-margin-top:var(--banner-h);box-shadow:0 0 0 3px var(--accent)}
.wtable tr:target{box-shadow:none;background:var(--surface-2);
  outline:2px solid var(--accent);outline-offset:-2px}

/* ---- changelog: none. History renders as a work-style table (see
        changelog_row) — the old .chg card styles went with the cards. ---- */
.legend{list-style:none;margin:.4rem 0 0;padding:0;display:grid;gap:.5rem}
.legend li{display:flex;gap:.55rem;align-items:center;font-size:.88rem}
.legend b{flex:0 0 11rem}
.legend span:last-child{color:var(--ink-2)}
.foot{margin-top:2.5rem;padding-top:1rem;border-top:1px solid var(--line);
  color:var(--ink-2);font-size:.8rem}

@media (max-width:860px){
  :root{--banner-h:195px}
  .banner-in{padding:.55rem .9rem;gap:.5rem}
  .banner-stats{margin-left:0;width:100%;gap:.3rem}
  .stat{flex:1 1 auto;min-width:0}
  .stat-l{font-size:9.5px}
  .letter{padding:.85rem .95rem;margin-top:1.2rem}
  .cards{grid-template-columns:1fr}
  .legend b{flex:0 0 100%}
  .legend li{display:block}
  .tabs{padding:.35rem .9rem}
}
@media (max-width:420px){
  .lm-h{gap:.4rem}
  .card,.task{padding-left:.7rem;padding-right:.7rem}
}

@media (prefers-reduced-motion:reduce){
  *,*::before,*::after{transition:none!important;animation:none!important;
    scroll-behavior:auto!important}
}

/* ---- print / save-as-PDF ----
   Clients print this. Force the light palette, open every accordion, and
   reveal every round panel — a printed page cannot be clicked. Each panel
   prints its own round heading, because the tab strip is gone. */
@media print{
  :root{--canvas:#fff;--surface:#fff;--surface-2:#fff;--ink:#000;--ink-2:#444;
    --line:#bbb;--shadow:none}
  body{background:#fff;font-size:11pt}
  .banner{position:static;box-shadow:none}
  .roundnav{display:none!important}
  .tabpanel[hidden]{display:block!important}
  .tabpanel{break-before:page}
  .tabpanel::before{content:attr(data-round-label);font-weight:700;font-size:1.3em;
    display:block;margin:1rem 0 .3rem}
  .lm-h{margin-top:1.6rem;break-after:avoid}
  details{border:1px solid #bbb;break-inside:avoid}
  /* FORCE EVERY ACCORDION OPEN ON PAPER — a printed page cannot be clicked.
     Two mechanisms, because browsers changed under us: the old UA rule was
     `details:not([open]) > *:not(summary){display:none}`, which `display:block`
     beat; current Chrome instead hides a `::details-content` pseudo-element
     with `content-visibility`, which `display` cannot touch at all. Keep both
     — dropping the first breaks older engines, dropping the second silently
     prints a page of collapsed headers. */
  details>summary+*,.round-body,.sect-body{display:block!important}
  details::details-content{content-visibility:visible!important;
    display:block!important;block-size:auto!important;height:auto!important}
  .card,.wtable tr{break-inside:avoid}
  /* On screen the work table scrolls inside its wrapper; on paper there is
     nothing to scroll, so `overflow-x:auto` just guillotines the last column.
     Let it reflow to the page width instead. */
  .tablewrap{overflow:visible}
  /* Seven columns on a portrait page is tight — the screen min-widths and
     padding push it past the printable width and clip the Status column.
     Drop both for print; the type is still readable at 8.5pt. */
  .wtable{min-width:0;font-size:8.5pt}
  .wtable th,.wtable td{padding:.32rem .4rem}
  .c-page,.c-held,.c-detail,.c-item{min-width:0}
  /* And DO NOT print the href after a link inside the table. A 45-character
     URL in a column the table has already decided is narrow prints as a
     one-character-per-line ladder, and every fix for that (break-all,
     table-layout:fixed, hard column percentages) buys it by crushing the
     Detail column, which is the one people actually read. The link label
     names the page; the URL still prints on the section header, on cards and
     in the changelog, where there is a full-width line to put it on. */
  .wtable a[href^="http"]::after{content:none}
  a[href^="http"]::after{content:" (" attr(href) ")";font-size:9pt;color:#555;
    margin-left:.3em}
  .lnk .ic,.ptag .ic{display:none}
  /* Paper has no hover and no tooltip, so every control whose meaning lives
     in a title attribute has to spell itself out: the section page link, the
     status glyph, and the table's "Link" (whose real label is the .sr-only
     one — swap the two round). */
  .pagelink .sr-only,.c-status .sr-only,.c-page .lnk .sr-only{
    position:static;width:auto;height:auto;clip:auto;margin:0;
    white-space:normal}
  .c-page .lnk [aria-hidden="true"]{display:none}
  /* Word only, no glyph, no wrapping. Keeping both put "In progress" on two
     lines in a column narrow enough to make every row taller than it needs
     to be, and the ink the glyph costs is better spent on Detail. */
  .c-status .ic{display:none}
  .c-status .sr-only{white-space:nowrap;font-weight:600}
}
"""

# Styling for the internal-only furniture. Concatenated onto CSS *only* in
# internal mode — not because a stylesheet leaks data, but because the client
# build should contain no trace of the internal layer at all. A `.t-owner`
# rule sitting unused in the client's file is a question waiting to be asked,
# and it defeats the one-line audit ("grep the client build for t-owner").
CSS_INTERNAL = """
.mode-flag{display:inline-block;background:var(--t-blocked);color:#fff;
  border-radius:4px;padding:0 6px;font-weight:700;font-size:.72rem;
  text-transform:uppercase;letter-spacing:.04em}
.t-owner{font:700 10px/1.5 ui-monospace,Menlo,monospace;letter-spacing:.06em;
  border-radius:4px;padding:1px 5px;border:1px dashed var(--t-review);
  color:var(--t-review)}
.t-owner.o-client{border-color:var(--t-client);color:var(--t-client)}
.t-effort{font:600 10px/1.5 ui-monospace,Menlo,monospace;color:var(--ink-2);
  border:1px solid var(--line);border-radius:4px;padding:1px 5px}
.internal-note{margin-top:.4rem;padding:.35rem .55rem;
  border-left:3px solid var(--t-review);background:var(--t-review-bg);
  color:var(--ink);font-size:.84rem;border-radius:0 5px 5px 0}
/* In the work table the pills follow the title inline, so they need their own
   gap — the cell has no flex row to space them. */
.c-item .t-owner,.c-item .t-effort{margin-left:.3rem;white-space:nowrap}
.c-detail .internal-note{margin-top:.35rem;font-weight:400}
"""

# ---------------------------------------------------------------------------
# JS — everything the page needs, and nothing else. No framework.
# ---------------------------------------------------------------------------
JS = """
(function () {
  'use strict';

  /* --- Round tabs. Hand-rolled because there is no native tab element, so
     this is the one place ARIA has to be written by hand: roving tabindex,
     arrow / Home / End keys, aria-selected on the button, hidden on the
     panel. Selecting a round ALSO swaps the round-scoped furniture in the
     sticky banner (counters, meter, subtitle) — every [data-round] on the
     page belongs to exactly one round, so one loop keeps the whole page
     honest about which round you are reading. --- */
  var list = document.querySelector('[role="tablist"]');
  if (list) {
    var tabs = Array.prototype.slice.call(list.querySelectorAll('[role="tab"]'));
    var scoped = Array.prototype.slice.call(
      document.querySelectorAll('[data-round]:not([role="tab"])')
    );
    function select(tab, focus) {
      var round = tab.getAttribute('data-round');
      tabs.forEach(function (t) {
        var on = t === tab;
        t.setAttribute('aria-selected', on ? 'true' : 'false');
        t.tabIndex = on ? 0 : -1;
      });
      scoped.forEach(function (el) {
        el.hidden = el.getAttribute('data-round') !== round;
      });
      if (focus) { tab.focus(); }
    }
    list.addEventListener('click', function (e) {
      var t = e.target.closest('[role="tab"]');
      if (t) { select(t, false); }
    });
    list.addEventListener('keydown', function (e) {
      var i = tabs.indexOf(document.activeElement);
      if (i < 0) { return; }
      var n = null;
      if (e.key === 'ArrowRight') { n = tabs[(i + 1) % tabs.length]; }
      else if (e.key === 'ArrowLeft') { n = tabs[(i - 1 + tabs.length) % tabs.length]; }
      else if (e.key === 'Home') { n = tabs[0]; }
      else if (e.key === 'End') { n = tabs[tabs.length - 1]; }
      if (n) { e.preventDefault(); select(n, true); }
    });
  }

  /* --- aria-expanded on <summary>.
     Browsers already expose the open/closed state of <details> natively; this
     mirrors it onto an explicit attribute so auditing tools and older screen
     readers agree with what the page is doing. It only ever reports the real
     state, so it can never contradict the native semantics. --- */
  var summaries = Array.prototype.slice.call(document.querySelectorAll('details > summary'));
  function sync(d) {
    var s = d.querySelector(':scope > summary');
    if (s) { s.setAttribute('aria-expanded', d.open ? 'true' : 'false'); }
  }
  summaries.forEach(function (s) {
    var d = s.parentNode;
    sync(d);
    d.addEventListener('toggle', function () { sync(d); });
  });

  /* --- NOTE, so nobody "fixes" this: the section header's page link sits
     inside a <summary>, and clicking it does NOT toggle the accordion. That
     is not luck and it does not need a guard — an <a href> has its own
     activation behaviour, so it becomes the click's activation target and
     <summary>'s never runs. A stopPropagation() guard here would be worse
     than useless: propagation and activation behaviour are different things,
     so it would not prevent a toggle in an engine that did toggle, and it
     would silently kill any future listener on those links. Verified in
     Chrome; it is what the HTML spec requires. --- */

  /* --- There is no expand/collapse-all button. It was justified by printing
     and by Ctrl+F, and it is not needed for either: @media print forces every
     accordion open (see the CSS), and browsers now expand a closed <details>
     automatically when find-in-page matches inside it. That left a control
     whose only remaining job was to undo the page's own default state. --- */

  /* --- Deep links. Every item carries an id, so an email can say "see H-04"
     and link straight to it. Opening a link into a collapsed accordion — or
     into a round that is not the selected one — would otherwise land on a
     blank space, so switch rounds first, then open every ancestor. --- */
  function reveal() {
    var id = location.hash.slice(1);
    if (!id) { return; }
    var el = document.getElementById(id);
    if (!el) { return; }
    var node = el;
    while (node && node !== document.body) {
      if (node.tagName === 'DETAILS') { node.open = true; }
      if (node.classList && node.classList.contains('tabpanel')) {
        var tab = document.getElementById(node.getAttribute('aria-labelledby'));
        if (tab) { tab.click(); }
      }
      node = node.parentNode;
    }
    el.scrollIntoView({ block: 'center' });
  }
  window.addEventListener('hashchange', reveal);
  reveal();
})();
"""


# ---------------------------------------------------------------------------
# Page assembly
# ---------------------------------------------------------------------------
def build(data, internal, skin=None):
    validate(data)
    _USED_ICONS.clear()
    R = Renderer(internal)
    P = Pages(data)
    proj = data["project"]

    rounds = R.visible(data["rounds"])
    if not rounds:
        raise DataError("rounds: every round is internal_only — nothing to render")

    cur_num = data.get("current_round")
    current = next((r for r in rounds if r["number"] == cur_num), None)
    if current is None:
        current = max(rounds, key=lambda r: r["number"])

    blockers_by_id = {}
    for r in data["rounds"]:
        for b in r.get("blockers", []):
            blockers_by_id[b["id"]] = b

    brand = proj.get("brand") or {}
    brand_css = ""
    if brand.get("accent") or brand.get("accent_2"):
        css_parts = []
        if brand.get("accent"):
            css_parts.append("--accent:{}".format(brand["accent"]))
        if brand.get("accent_2"):
            css_parts.append("--accent-2:{}".format(brand["accent_2"]))
        brand_css = ":root{" + ";".join(css_parts) + "}"

    # `project.intro` is NOT rendered. It was one generic paragraph, identical
    # on every client's tracker and on every round of it, explaining controls
    # a reader had already used by the time they reached it. Chris: "there's a
    # generic paragraph that's the same across the board... we should delete
    # that." The per-round letter (render_lede) says something true about
    # *this* round instead, which is what that space was worth.
    contact = ""
    if proj.get("contact"):
        contact = "<p>Questions on anything here: {}</p>".format(esc(proj["contact"]))

    # Render content BEFORE the banner-independent sprite so the sprite only
    # carries glyphs the page actually uses.
    banner = render_banner(data, rounds, current, R)
    panels = render_panels(data, rounds, current, blockers_by_id, R, P)
    legend = render_legend(R)

    # An optional visual skin (see --skin). Four hooks, all pure strings:
    # extra CSS appended INSIDE the same <style> (after everything else, so
    # its declarations win ties), extra <head> markup, and fixed decor at the
    # top and bottom of <body>. With no skin every hook is the empty string,
    # so the output is byte-for-byte what this script always produced.
    skin_css = skin.css() if skin else ""
    skin_head = skin.head_extra() if skin else ""
    skin_body_start = skin.body_start() if skin else ""
    skin_body_end = skin.body_end() if skin else ""

    parts = [
        '<!doctype html>',
        '<html lang="en"><head><meta charset="utf-8">',
        '<meta name="viewport" content="width=device-width,initial-scale=1">',
        "<title>{} &mdash; {}</title>".format(esc(proj["client"]), esc(proj["title"])),
        '<meta name="robots" content="noindex,nofollow">',
        "<style>{}{}{}{}</style>{}</head><body>".format(
            CSS, CSS_INTERNAL if internal else "", brand_css, skin_css, skin_head
        ),
        sprite(),
        skin_body_start,
        banner,
        '<main class="wrap">',
        panels,
        legend,
        '<footer class="foot">',
        contact,
        "<p>Status page for {client}. Generated {when}{mode}.</p>".format(
            client=esc(proj["client"]),
            when=esc(date.today().isoformat()),
            mode=" &mdash; INTERNAL BUILD" if internal else "",
        ),
        "</footer></main>",
        skin_body_end,
        "<script>{}</script>".format(JS),
        "</body></html>",
    ]
    return "".join(parts)


def main(argv=None):
    ap = argparse.ArgumentParser(
        description="Build a client-facing build/QA tracker page from JSON.",
        epilog="The JSON is the source of truth; the HTML is disposable. "
               "Never hand-edit the output.",
    )
    ap.add_argument("data", help="path to the tracker JSON file")
    ap.add_argument(
        "--internal",
        action="store_true",
        help="internal build: adds task ownership, effort sizing and internal "
             "notes, and includes internal_only records. Writes to "
             "{name}.internal.html so it can never be confused with, or "
             "overwrite, the file the client has the link to.",
    )
    sg = ap.add_mutually_exclusive_group()
    sg.add_argument(
        "--skin",
        metavar="DIR",
        help="path to a skin directory containing skin.py. Overrides the "
             "default skin (skins/classic-city, resolved next to this "
             "script). Output name is unchanged: the skinned page IS the "
             "tracker.",
    )
    sg.add_argument(
        "--no-skin",
        action="store_true",
        help="build the plain, unskinned page. Writes {name}.plain.html "
             "(.plain.internal.html with --internal) so it can never "
             "overwrite the skinned file the client has the link to.",
    )
    ap.add_argument("-o", "--out", help="output path (default: alongside the JSON)")
    args = ap.parse_args(argv)

    try:
        with open(args.data, "r") as fh:
            data = json.load(fh)
    except (IOError, OSError) as exc:
        sys.stderr.write("Cannot read {}: {}\n".format(args.data, exc))
        return 1
    except ValueError as exc:
        sys.stderr.write("{} is not valid JSON: {}\n".format(args.data, exc))
        return 1

    # A skin is a plain directory with a skin.py in it — loaded by path, not
    # from sys.path, so skins can live anywhere (a client repo can carry its
    # own). The module contract: NAME, head_extra(), body_start(),
    # body_end(), css(). See skins/classic-city/ for the reference skin.
    #
    # THE CLASSIC CITY SKIN IS THE DEFAULT: a child that copies this folder
    # and builds round-2.json gets the skinned page without asking. It is
    # resolved relative to THIS SCRIPT (not the cwd or the JSON), so the one
    # `cp -R` of the template folder is all a child needs. An explicitly
    # passed --skin that is broken is an error; the missing DEFAULT skin
    # only warns and degrades to the plain build — never block a build.
    skin = None
    skin_dir = None
    if args.no_skin:
        pass
    elif args.skin:
        if not os.path.isfile(os.path.join(args.skin, "skin.py")):
            sys.stderr.write("No skin.py in {}\n".format(args.skin))
            return 1
        skin_dir = args.skin
    else:
        default_dir = os.path.join(
            os.path.dirname(os.path.abspath(__file__)), "skins", "classic-city"
        )
        if os.path.isfile(os.path.join(default_dir, "skin.py")):
            skin_dir = default_dir
        else:
            sys.stderr.write(
                "default skin not found at {}; building unskinned — copy the "
                "skins/ folder from the parent template, or pass --no-skin "
                "to silence this\n".format(default_dir)
            )
    if skin_dir is not None:
        spec = importlib.util.spec_from_file_location(
            "tracker_skin", os.path.join(skin_dir, "skin.py")
        )
        skin = importlib.util.module_from_spec(spec)
        spec.loader.exec_module(skin)

    try:
        page = build(data, args.internal, skin)
    except DataError as exc:
        sys.stderr.write("Data problem — {}\n".format(exc))
        return 1

    # Naming: the skinned page IS the tracker, so the default (and any
    # explicit --skin) writes plain {stem}.html — the client URL stays
    # round-2.html. Only the deliberate --no-skin build takes a suffix
    # ({stem}.plain.html) so it can sit alongside without overwriting.
    # The degraded missing-default-skin build keeps {stem}.html: it is the
    # same deliverable, just unskinned until skins/ gets copied in.
    if args.out:
        out = args.out
    else:
        stem = os.path.splitext(os.path.abspath(args.data))[0]
        if args.no_skin:
            stem = stem + ".plain"
        out = stem + (".internal.html" if args.internal else ".html")

    with open(out, "w") as fh:
        fh.write(page)

    R = Renderer(args.internal)
    rounds = R.visible(data["rounds"])
    tasks = sum(
        len(R.visible(s.get("tasks")))
        for r in rounds
        for s in R.visible(r.get("sections"))
    )
    blockers = sum(len(R.visible(r.get("blockers"))) for r in rounds)
    pages = len(data.get("pages") or [])

    print("Wrote {}".format(out))
    print(
        "  mode: {}  ·  skin: {}  ·  {} rounds  ·  {} items  ·  "
        "{} open-item records  ·  {} pages".format(
            "INTERNAL" if args.internal else "client",
            skin.NAME if skin is not None else "plain",
            len(rounds), tasks, blockers, pages,
        )
    )
    if args.internal:
        print(
            "  ! Internal build. Do NOT deploy this file — keep *.internal.html\n"
            "    out of the child theme's git history (see the README)."
        )
    return 0


if __name__ == "__main__":
    sys.exit(main())
