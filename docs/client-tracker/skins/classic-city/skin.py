# -*- coding: utf-8 -*-
"""Classic City skin for build-tracker.py — the classiccity.com platformer look.

Loaded by `build-tracker.py` as its DEFAULT skin (resolved next to the
script; `--no-skin` opts out, `--skin DIR` substitutes another skin). The
generator calls four hooks:

    head_extra()  -> favicon <link> (bolt icon, data: URI)
    body_start()  -> fixed sky backdrop (clouds + rolling hills)
    body_end()    -> fixed grass ground strip + the perched owl
    css()         -> the whole skin stylesheet, every asset inlined

Everything is inlined as data: URIs at build time, read from this module's
own assets/ directory, so the output keeps the base page's hard invariant:
ZERO external requests. See README.md beside this file for where each asset
came from and its licence.

Python 3.9 compatible (no f-strings, no walrus, no match).
"""
import base64
import os
import re
from urllib.parse import quote

NAME = "classic-city"

_ASSETS = os.path.join(os.path.dirname(os.path.abspath(__file__)), "assets")


# ---------------------------------------------------------------------------
# Asset inlining
# ---------------------------------------------------------------------------
def _read_bytes(name):
    with open(os.path.join(_ASSETS, name), "rb") as fh:
        return fh.read()


def _b64_uri(name, mime):
    """Binary asset (font, PNG) as a base64 data: URI."""
    return "data:{};base64,{}".format(
        mime, base64.b64encode(_read_bytes(name)).decode("ascii")
    )


def _svg_uri(name, transform=None):
    """SVG as a URL-encoded data: URI — smaller than base64 for XML text.

    Whitespace is collapsed first; `#` and `"` MUST be encoded (fills and
    attribute quotes), so the safe set deliberately excludes them. Every
    url() in the CSS wraps the URI in double quotes, which is why single
    quotes and parens can stay literal.
    """
    with open(os.path.join(_ASSETS, name), "r") as fh:
        text = fh.read()
    if transform:
        text = transform(text)
    text = re.sub(r"\s+", " ", text).strip()
    return "data:image/svg+xml," + quote(text, safe="-_.~=:/,;()'!*")


def _hills_transparent(text):
    """The rolling-hills tile paints its own sky as solid WHITE (the site's
    page is white). Here the band sits on a gradient that can still be blue
    (or mid-fade) behind it, so the tile's white sky would print a hard
    horizontal edge against the gradient. Making those fills
    transparent lets the backdrop show through; the hill silhouettes
    themselves are the blue fills and are untouched."""
    return text.replace('fill="white"', 'fill="none"')


# ---------------------------------------------------------------------------
# Hooks
# ---------------------------------------------------------------------------
def head_extra():
    """Favicon: the bolt logo-icon, full colour. Like the banner logo, it is
    brand chrome — the orange here never reads as a status, so it cannot
    collide with amber-means-your-move."""
    return '<link rel="icon" href="{}">'.format(_svg_uri("logo-icon.svg"))


def body_start():
    """Backdrop, behind everything (z-index:-1), inert to clicks and to
    assistive tech: viewport-fixed sky + clouds below the banner,
    viewport-fixed hills at the bottom (see the CSS for the layout)."""
    return (
        '<div class="ccc-sky" aria-hidden="true">'
        '<div class="ccc-sky-clouds"></div>'
        '<div class="ccc-sky-hills"></div>'
        "</div>"
    )


def body_end():
    """Fixed grass strip along the bottom of the viewport, with the owl
    perched on it — plus the round-nav collapse script.

    THE ROUND STRIP HIDES BEHIND THE PILL. Most visits never change round,
    so the tab strip is banner height spent on a control nobody touches.
    When (and only when) there is more than one round, this script turns the
    visible "Round N · label" pill into a real disclosure button (role,
    tabindex, aria-expanded/-controls, Enter/Space) for the .roundnav.
    Deep links keep working while the strip is hidden: the base reveal()
    switches rounds by calling tab.click(), and a click() on a
    display:none button still runs its handlers. Choosing a round
    auto-closes the strip. Runs before the base script; both attach their
    own listeners, order does not matter."""
    script = (
        "(function(){"
        "var nav=document.querySelector('.roundnav');"
        "var tabs=document.querySelectorAll('[role=\"tab\"]');"
        "if(!nav||tabs.length<2){return;}"
        "if(!nav.id){nav.id='ccc-roundnav';}"
        "var pills=Array.prototype.slice.call("
        "document.querySelectorAll('.banner-sub'));"
        "nav.hidden=true;"
        "pills.forEach(function(p){"
        "p.setAttribute('role','button');"
        "p.tabIndex=0;"
        "p.setAttribute('aria-controls',nav.id);"
        "p.setAttribute('aria-expanded','false');"
        "});"
        "function setOpen(open){"
        "nav.hidden=!open;"
        "pills.forEach(function(p){"
        "p.setAttribute('aria-expanded',open?'true':'false');});"
        "}"
        "pills.forEach(function(p){"
        "p.addEventListener('click',function(){setOpen(nav.hidden);});"
        "p.addEventListener('keydown',function(e){"
        "if(e.key==='Enter'||e.key===' '){e.preventDefault();"
        "setOpen(nav.hidden);}});"
        "});"
        "nav.addEventListener('click',function(e){"
        "if(e.target.closest('[role=\"tab\"]')){setOpen(false);}});"
        "})();"
    )
    return (
        '<div class="ccc-ground" aria-hidden="true"></div>'
        '<div class="ccc-owl" aria-hidden="true"></div>'
        "<script>{}</script>".format(script)
    )


def css():
    out = _CSS
    for token, value in (
        ("__CULTURES__", _b64_uri("CulturesCarnival.woff2", "font/woff2")),
        ("__Q400__", _b64_uri("Quicksand-400.woff2", "font/woff2")),
        ("__QMID__", _b64_uri("Quicksand-500-600.woff2", "font/woff2")),
        ("__Q700__", _b64_uri("Quicksand-700.woff2", "font/woff2")),
        ("__ARCADE__", _b64_uri("ArcadePixel-Regular.otf", "font/otf")),
        ("__HEADSHOT__", _b64_uri("headshot-chris.jpg", "image/jpeg")),
        ("__LOGOFULL__", _svg_uri("logo-full.svg")),
        ("__CLOUDS__", _svg_uri("bg-clouds.svg")),
        ("__HILLS__", _svg_uri("bg-rolling-hills.svg", _hills_transparent)),
        ("__GRASS__", _svg_uri("terrain_grass_block_top.svg")),
        ("__BLOCK__", _svg_uri("block_exclamation.svg")),
        ("__HEART__", _svg_uri("heart.svg")),
        ("__FLAG__", _svg_uri("flag_red_a.svg")),
        ("__COIN__", _svg_uri("coin_gold.svg")),
        ("__GEM__", _svg_uri("gem_green.svg")),
        ("__OWL__", _svg_uri("character_owl_idle.svg")),
        ("__OWLB__", _svg_uri("character_owl_blink.svg")),
    ):
        out = out.replace(token, value)
    return out


# ---------------------------------------------------------------------------
# The stylesheet. Appended AFTER the base CSS (+ CSS_INTERNAL + brand_css),
# so plain declarations here win ties — the base sheet is never edited.
# ---------------------------------------------------------------------------
_CSS = """
/* ========================================================================
   CLASSIC CITY SKIN — the classiccity.com platformer look.
   Day scene only; the page's semantics are untouched. The one rule that
   must survive any edit: IN THE UI, ORANGE MEANS "YOUR MOVE" AND NOTHING
   ELSE. Links are blue, accents are blue/green, and the gold !-block sits
   on the "waiting on you" landmark. The logo's orange bolt is the sole
   branded exception — it is chrome, not a status.
   ===================================================================== */

/* ---- fonts. Cultures Carnival is display-only (h1 + the h2 landmarks);
        ArcadePixel ("Video Game") is HUD-only; Quicksand carries the body.
        400/700 only — this is dense data, no 300. ---- */
@font-face{font-family:"Cultures Carnival";font-style:normal;font-weight:100;
  font-display:swap;src:url("__CULTURES__") format("woff2")}
@font-face{font-family:Quicksand;font-style:normal;font-weight:400;
  font-display:swap;src:url("__Q400__") format("woff2")}
/* 500-600 from Google Fonts' official latin file (one variable-weight woff2
   covering the range) — 400 was too thin as the body face of a dense page. */
@font-face{font-family:Quicksand;font-style:normal;font-weight:500 600;
  font-display:swap;src:url("__QMID__") format("woff2")}
@font-face{font-family:Quicksand;font-style:normal;font-weight:700;
  font-display:swap;src:url("__Q700__") format("woff2")}
@font-face{font-family:"Video Game";font-style:normal;font-weight:400;
  font-display:swap;src:url("__ARCADE__") format("opentype")}

/* ---- tokens: the CCC palette mapped onto the base page's colour language.
        One meaning per colour. Amber/orange stays the your-move signal —
        now the brand orange. Accents and focus rings are sky blue so no
        STATUS ever competes with it (the logo bolt is chrome, not status).
        Inner hairlines stay LIGHT (--line is
        a quiet slate, not ink) or every table row gets heavy. ---- */
:root{
  --canvas:#eef4fa; --surface:#fff; --surface-2:#f8f9fa;
  --ink:#343a40; --ink-2:#6b727a; --line:#c8d0d6;
  --radius:8px;
  --accent:#0693e3; --accent-2:#1a7c34;
  /* Each triple is derived from ONE site colour: green #1a7c34, purple
     #7a4095, orange #f08828 (ink darkened to hold AA on white), red
     #d51010 (same), slate #6b727a. Queued, in-progress and deferred share
     the one slate family — three greys reading as three states was the
     noise the base page already removed once. Every base --t-* custom
     property, -bg and -br included, is overridden here: one leaked base
     value is a colour that does not match the site. */
  --t-done:#1a7c34;     --t-done-bg:#e6f2e9;     --t-done-br:#bcdcc5;
  --t-review:#7a4095;   --t-review-bg:#f3eaf7;   --t-review-br:#d8c2e4;
  --t-client:#a4570a;   --t-client-bg:#fdf1e5;   --t-client-br:#f8cda4;
  --t-blocked:#b90e0e;  --t-blocked-bg:#fbe7e7;  --t-blocked-br:#f0abab;
  --t-queued:#6b727a;   --t-queued-bg:#f0f1f3;   --t-queued-br:#d3d6d9;
  --t-deferred:#6b727a; --t-deferred-bg:#f0f1f3; --t-deferred-br:#d3d6d9;
  /* Deep-link offset, re-measured with the compact banner (no meter, no
     visible tab strip): 104px at 1280, 224px at 390. Err high (base
     sheet's rule: landing low is invisible, landing under the banner
     hides the target). Note the collapsed round strip can add ~45px when
     open, but a deep link never lands with it open. */
  --banner-h:120px;
}

/* THE SKIN IS DAY-ONLY. A fixed daytime sky behind dark-mode surfaces reads
   as a rendering bug, so dark-OS users get the same day look: re-assert the
   light tokens inside the dark media query (this sheet loads after the base
   one, so these win). */
@media (prefers-color-scheme:dark){
  :root{
    --canvas:#eef4fa; --surface:#fff; --surface-2:#f8f9fa;
    --ink:#343a40; --ink-2:#6b727a; --line:#c8d0d6;
    --shadow:0 1px 2px rgba(20,28,44,.06),0 4px 14px rgba(20,28,44,.05);
    --t-done:#1a7c34;     --t-done-bg:#e6f2e9;     --t-done-br:#bcdcc5;
    --t-review:#7a4095;   --t-review-bg:#f3eaf7;   --t-review-br:#d8c2e4;
    --t-client:#a4570a;   --t-client-bg:#fdf1e5;   --t-client-br:#f8cda4;
    --t-blocked:#b90e0e;  --t-blocked-bg:#fbe7e7;  --t-blocked-br:#f0abab;
    --t-queued:#6b727a;   --t-queued-bg:#f0f1f3;   --t-queued-br:#d3d6d9;
    --t-deferred:#6b727a; --t-deferred-bg:#f0f1f3; --t-deferred-br:#d3d6d9;
  }
}

/* ---- base type + the room the ground strip needs ---- */
body{font-family:Quicksand,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,
  Helvetica,Arial,sans-serif;
  font-weight:500;     /* 400 Quicksand is too thin for a dense data page */
  background:#fff;     /* white base; the sky div only covers the top */
  padding-bottom:7rem} /* content must clear the fixed ground strip */
h1,h2,h3,h4{font-weight:700}
b,strong{font-weight:700} /* keep real bold distinct from the 500 body */

/* ---- the backdrop: viewport-FIXED sky + clouds (classiccity.com's
   parallax layers are fixed, so the clouds persist however far you
   scroll), hills fixed at the bottom of the viewport. The page base is
   WHITE, like the site.

   HOW THE SEAM WAS ACTUALLY SOLVED (Chris's own diagnosis, after two
   engineered attempts fought the artwork): the cloud tile's bottom is a
   solid WHITE rect ON PURPOSE — the artwork is designed to sit at the
   top of a white page and blend out by itself. So: no gradient on the
   sky, no masks on the band, no banner offset. The tile carries its own
   sky blue up top (behind the banner too, so no white strip), and its
   white bottom lands on the white page. Static on purpose — no drift
   (Chris's call: no motion back here). */
.ccc-sky{position:fixed;inset:0;z-index:-1;pointer-events:none}
/* Cloud tile at natural size (256x361) — the band sits at viewport top,
   tucked behind the opaque banner, exactly like the site. */
.ccc-sky-clouds{position:absolute;top:0;left:0;right:0;
  height:400px;
  background:url("__CLOUDS__") repeat-x;background-size:256px auto;
  background-position:0 bottom}
/* Hills stay pinned to the viewport bottom (they pair with the fixed
   ground strip). White sky fills are stripped at encode time (see
   _hills_transparent) so the band sits on the white base. */
.ccc-sky-hills{position:fixed;bottom:0;left:0;right:0;height:380px;opacity:.9;
  background:url("__HILLS__") repeat-x;background-size:auto 100%;
  background-position:0 bottom}
/* No reduced-motion block needed for the owl or the link animation: the
   BASE sheet's prefers-reduced-motion rule is
   `*,*::before,*::after{animation:none!important}` — it already outranks
   every plain animation declaration in this skin. */

/* ---- the ground strip + the owl perched on it. z-index 15: above content,
        below the sticky banner (20). ---- */
.ccc-ground{position:fixed;left:0;right:0;bottom:0;height:56px;z-index:15;
  pointer-events:none;
  background:url("__GRASS__") repeat-x;background-size:56px 56px;
  background-position:0 bottom}
.ccc-owl{position:fixed;right:3rem;bottom:52px;width:48px;height:48px;z-index:15;
  pointer-events:none;background:url("__OWL__") no-repeat center/contain;
  animation:ccc-owl-blink 6s step-end infinite}
/* step-end holds each frame until the next keyframe: idle 0-96%, blink for
   ~204ms, idle again. Under reduced motion the animation dies (see above)
   and the static background-image — the idle frame — remains. */
@keyframes ccc-owl-blink{
  0%{background-image:url("__OWL__")}
  96%{background-image:url("__OWLB__")}
  99.4%{background-image:url("__OWL__")}
}
@media (max-width:640px){
  .ccc-ground{height:40px;background-size:40px 40px}
  .ccc-owl{right:1.2rem;bottom:36px;width:40px;height:40px}
  body{padding-bottom:6rem}
}

/* ---- banner → game HUD, full width, three zones:
        logo far left | identity centered | pods right. ---- */
/* Sticky bar: hard 3px rule, but NO offset shadow — a hard shadow under a
   sticky bar looks broken the moment content scrolls past beneath it. */
.banner{border-bottom:3px solid #343a40;box-shadow:0 1px 0 rgba(52,58,64,.10)}
.banner-in{max-width:none;padding:.7rem 2rem}
/* The full-colour wordmark — the site's own logo.svg, orange bolt and all.
   The one on-page appearance of brand orange that is not "your move": it is
   the logo, and it lives in chrome, not in a status. */
.banner-in::before{content:"";width:140px;height:44px;flex:0 0 auto;
  background:url("__LOGOFULL__") no-repeat center/contain}
.banner-id{text-align:center}
.banner-id h1{font-family:"Cultures Carnival",Quicksand,sans-serif;
  font-weight:100;font-size:1.5rem;line-height:1.15;letter-spacing:.01em}
/* Compaction: the project title phrase is hidden (the h1 above and the page
   <title> both carry it); "updated YYYY-MM-DD" stays. */
.proj-title{display:none}
/* The round pill. It already says "Round 2 · label", so no ZONE prefix.
   When the collapse script upgrades it to a disclosure button for the
   hidden round strip it gets a caret; the caret flips while open. */
.banner-sub{display:inline-block;font-family:"Video Game",ui-monospace,Menlo,monospace;
  font-weight:400;font-size:10px;text-transform:uppercase;letter-spacing:.05em;
  color:var(--ink);background:var(--surface-2);border:2px solid #343a40;
  border-radius:6px;padding:3px 8px;margin-top:3px}
.banner-sub[role="button"]{cursor:pointer}
.banner-sub[role="button"]:hover{background:#fff}
.banner-sub[role="button"]::after{content:"";display:inline-block;margin-left:7px;
  width:0;height:0;border-left:4px solid transparent;
  border-right:4px solid transparent;border-top:5px solid currentColor;
  vertical-align:1px;transition:transform .12s ease}
.banner-sub[aria-expanded="true"]::after{transform:rotate(180deg)}
.stat{border:2px solid #343a40;border-radius:6px}
/* Big HUD numbers; the label row stays small so the pods stay compact. */
.stat b{font-family:"Video Game",ui-monospace,Menlo,monospace;font-weight:400;
  font-size:1.55rem;padding-top:2px}
/* No progress meter in the skin: with the Done pod back in the banner the
   bar was the same number twice. The base build keeps it. */
.meter{display:none}
.roundnav{background:#eef4fa;border-top:1px solid var(--line)}
.tabs{max-width:none;padding-left:2rem;padding-right:2rem}
.tab{font-weight:700;font-size:.78rem;text-transform:uppercase;letter-spacing:.03em;
  border:2px solid transparent;border-radius:6px;transition:transform .08s ease}
.tab:hover{transform:translate(-1px,-1px);background:var(--surface)}
.tab[aria-selected=true]{border-color:#343a40;background:#fff;color:var(--ink);
  box-shadow:3px 3px 0 rgba(0,0,0,.2)}
.tab-badge{font-family:"Video Game",ui-monospace,Menlo,monospace;font-weight:400;
  letter-spacing:.05em}

/* ---- the chunky card language: OUTER containers only. Inner rows, chips
        and the nested tablewrap keep the base 1px hairlines — 3px ink on
        every table row would bury the data. (.tablewrap is always nested
        inside a chunky .sect here, so it deliberately stays a hairline.) */
.card,.sect,.letter,.folds>.fold,details.logwrap,.empty{
  border:3px solid #343a40;border-radius:8px;box-shadow:7px 7px 0 rgba(0,0,0,.18)}
.sect{margin-top:1rem}
/* Work/history table headers: ink band, canvas text — the base sheet keeps
   the small uppercase treatment, only the colours flip. The sr-only Status
   cell is a real th, so it takes the band too and the bar runs edge to
   edge with no notch; .tablewrap's overflow + radius clip the corners.
   Reverted to the light base style under @media print (bottom of file) —
   dark bands waste ink. */
.wtable thead th{background:#343a40;color:#fff}
.cards{gap:1.1rem}
.folds{gap:1rem}
details.logwrap{margin-bottom:1rem}

/* ---- links: the site's signature pixel-dash underline (verbatim from the
        theme's _buttons.scss). Blue at rest, purple + marching on hover.
        NOT on .reflink or the tabs. ---- */
.lnk,.pagelink,.foot a{color:var(--ink);font-weight:700;text-decoration:none;
  background-image:linear-gradient(to right,#0693e3 50%,transparent 50%);
  background-repeat:repeat-x;background-position:0 100%;background-size:6px 3px;
  padding-bottom:.15em}
.lnk:hover,.pagelink:hover,.foot a:hover{color:var(--ink);
  background-image:linear-gradient(to right,#7a4095 50%,transparent 50%);
  animation:ccc-link-pixels 1.6s linear infinite}
@keyframes ccc-link-pixels{to{background-position:-12px 100%}}

/* ---- h2 landmarks: Cultures Carnival + a sprite each. The inline FA glyph
        is hidden, not removed — the markup is shared with the unskinned
        build. Icon semantics: gold !-block = your move; heart = on us;
        red flag = the work/level; gold coin = what you collected (history);
        green gem = the colour key. ---- */
/* Landmarks rule off in the same 3px ink as the card borders, and take a
   deep top margin so each landmark reads as a new screen. The base sheet's
   .lm:first-of-type override (higher specificity) still pulls the first
   one up under the letter. */
.lm-h{font-family:"Cultures Carnival",Quicksand,sans-serif;font-weight:100;
  font-size:clamp(1.55rem,1.2rem + 1.4vw,2.05rem);letter-spacing:.01em;
  border-bottom:3px solid #343a40;margin-top:7.5rem}
.lm-h .lm-ic{display:none}
.lm-h::before{content:"";width:1.2em;height:1.2em;flex:0 0 auto;
  background:no-repeat center/contain}
.lm-h[id^="h-client"]::before{background-image:url("__BLOCK__")}
.lm-h[id^="h-agency"]::before{background-image:url("__HEART__")}
.lm-h[id^="h-work"]::before{background-image:url("__FLAG__")}
.lm-h[id^="h-log"]::before{background-image:url("__COIN__")}
#key-h::before{background-image:url("__GEM__")}
.h2-count{font-family:Quicksand,sans-serif;font-weight:700;letter-spacing:0}

/* ---- HUD counters: section tallies get a coin, refs go pixel ---- */
.tally{font-family:"Video Game",ui-monospace,Menlo,monospace;font-weight:400;
  font-size:11px;letter-spacing:.05em}
.sect-meta .tally::before{content:"";display:inline-block;width:1em;height:1em;
  margin-right:.35em;vertical-align:-.15em;
  background:url("__COIN__") no-repeat center/contain}

/* ---- NO MONOSPACE ANYWHERE. ArcadePixel is confined to the round pill,
        .tally, .tab-badge and the pod numbers; everything else the base
        sheet set in ui-monospace comes back to Quicksand. Table refs
        (.c-ref) are plain muted text and deliberately NOT pixel-fonted —
        tiny pixel type was the unreadable thing. ---- */
.ref{font-family:Quicksand,sans-serif;font-weight:600;font-size:11px}
.c-date{font-family:Quicksand,sans-serif;font-weight:500}
/* (The internal build's ownership/effort pills keep the base monospace on
   purpose: naming their classes here would plant internal-layer tokens in
   the CLIENT build's stylesheet and break the one-line leak audit.) */

/* ---- the round letter → speech bubble from Chris. The real headshot,
        framed like the site's character card: rounded, 3px ink border,
        hard offset shadow. ---- */
.letter{position:relative;margin-left:158px;transform:rotate(-.6deg);
  background:#fff}
.letter::before{content:"";position:absolute;left:-152px;top:50%;
  transform:translateY(-50%);width:120px;height:120px;border-radius:12px;
  border:3px solid #343a40;box-shadow:5px 5px 0 rgba(0,0,0,.18);
  background:url("__HEADSHOT__") no-repeat center/cover}
/* Speech tail: a rotated square whose bordered corner points at the
   headshot; its unbordered half sits over the letter and whites out the
   border there. */
.letter::after{content:"";position:absolute;left:-11px;top:50%;margin-top:-8px;
  width:16px;height:16px;background:#fff;transform:rotate(45deg);
  border-left:3px solid #343a40;border-bottom:3px solid #343a40}
@media (max-width:700px){
  .letter{margin-left:0;transform:none}
  .letter::before,.letter::after{display:none}
}

/* ---- narrow screens: the pods wrap under the identity block
        (measurements in the :root note above). ---- */
@media (max-width:860px){
  :root{--banner-h:240px}
}

/* ---- print: the scene stays on screen. No sky, no ground, no owl, no
        offset shadows, no tilt — and re-assert the base print palette,
        because this sheet's :root tokens load later and would otherwise
        override the base @media print ones. ---- */
@media print{
  :root{--canvas:#fff;--surface:#fff;--surface-2:#fff;--ink:#000;--ink-2:#444;
    --line:#bbb;--shadow:none}
  .ccc-sky,.ccc-ground,.ccc-owl{display:none!important}
  body{padding-bottom:0;background:#fff}
  .card,.sect,.letter,.folds>.fold,details.logwrap,.empty{box-shadow:none}
  .tab[aria-selected=true]{box-shadow:none}
  .letter{transform:none;margin-left:0}
  .letter::before,.letter::after{content:none}
  /* Back to the base print header (white band, muted ink) — the screen
     rule above would print a solid dark stripe per table. */
  .wtable thead th{background:var(--surface-2);color:var(--ink-2)}
}
"""
