# Prospect Demo Page Runbook

A "wow" demo for a potential client — a single flat `index.html + styles.css`
that **looks and behaves like the real Classic City Core (CCC) parent theme**
would on a finished site, but without spinning up WP Engine, a GitHub repo, or
a child theme. Drop in a zip, hand it to the prospect, close the deal.

The first one (TrialPort, 2026-06-02) was hand-built in roughly one session.
This runbook captures the pattern so the next one is ~30 minutes.

---

## When to use this

- A warm prospect needs a visual to react to before signing on
- You want to show "yes, WordPress can look like this" without committing
  to a full build
- You're A/B-pitching against Framer / Webflow / Squarespace and need to
  prove the parent theme's range with their own brand

**Don't use this** for finished work — once they sign, build a real
`sg-{slug}` child theme. This is sales collateral, not production.

---

## Output convention

| Thing | Location |
|---|---|
| HTML + CSS demo files | `~/Demos/{prospect-slug}/` (outside any repo) |
| This runbook | `docs/PROSPECT_DEMO.md` in the CCC parent (here) |
| Reference example | `~/Demos/trialport/` (first one built) |

The demo lives **outside this repo** on purpose — it's one-off prospect work,
not part of the parent theme's history. The runbook stays in the repo so
future-Claude finds it without being told to look.

---

## Intake checklist

Get these from the prospect (HubSpot form, email, brief — whatever) before
opening an editor. Missing things → reasonable placeholders + flag them.

**Brand**
- [ ] Prospect name + industry / what they actually do
- [ ] Logo file or wordmark style
- [ ] Hex codes for primary, secondary, accent/CTA, dark text, light bg.
      If they sent a brand guidelines doc, that doc is the source of truth.
- [ ] Google Font names (or fallback to system fonts)
- [ ] FontAwesome icon style — `solid` / `regular` / `light` / `sharp-light`

**Content per section** — same shape as the standard 4-block demo:
- [ ] Hero headline + supporting copy + primary CTA + bg image URL
- [ ] CTA thin band copy + button
- [ ] 4 audience/feature cards (icon, title, 1-sentence body each)
- [ ] About paragraph(s) + image
- [ ] 2–3 testimonial quotes (can be fabricated — flag clearly as placeholder)
- [ ] Closing CTA copy
- [ ] Footer links + social handles

**Reference material**
- [ ] Their current site URL (for content scrape + visual reference)
- [ ] Their app / product URL if applicable (for aesthetic match)

If the prospect's current site has decent imagery and you don't have
better, **use WebFetch to scrape the content outline** before writing
copy. Verbatim quotes from their existing site read more authentically
than anything you invent.

---

## Step-by-step

### 1. Scaffold

```bash
mkdir -p ~/Demos/{prospect-slug}
cp ~/Demos/trialport/index.html ~/Demos/{prospect-slug}/
cp ~/Demos/trialport/styles.css ~/Demos/{prospect-slug}/
```

TrialPort is the reference example. Always start from the most recent
working demo, not a blank file — the markup contracts for each block are
already there.

### 2. Map their brand to CCC's palette tokens

The CCC parent theme uses a canonical palette across **every** client
site. **This contract is non-negotiable** — don't invent new slot names.
Map the prospect's brand colors into these slots:

> ⚠️ **Neutrals note (this table is the legacy view).** The live theme's
> canonical neutrals are now **`canvas` / `panel` / `ink` / `ink-soft`**;
> the `light` / `light-alt` / `dark` / `dark-alt` rows below are
> **legacy** (kept for back-compat). For a *static demo* the legacy
> neutrals are still fine, but for a **real child theme** use the
> canonical neutrals — see [`THEME_TOKENS.md`](./THEME_TOKENS.md).

| Slot | Role | TrialPort got |
|---|---|---|
| `cta` | Primary action color — buttons, primary brand | Fuchsia `#E8178A` |
| `cta-alt` | CTA hover | Deep Fuchsia `#C4147A` |
| `primary` | Brand secondary — links, intelligence-feel | Purple `#9040B0` |
| `primary-alt` | Primary hover | Deep Purple `#6E2F8A` |
| `secondary` | Accent / success | Cyan `#04D9FF` |
| `secondary-alt` | Secondary hover | Navy `#045285` |
| `light` | Off-white surface | `#F5F4FA` |
| `light-alt` | White surface | `#FFFFFF` |
| `dark` | Body text + dark surfaces | `#0D0B1A` |
| `dark-alt` | Dark hover / variant | `#1A1729` |

For each slot, also declare a `{slug}-opposite` — the text color that
pairs with that bg so `.has-{slug}-background-color` is auto-readable.
This is the combined-helper convention. **Every slot must have one.**

If the prospect's brand needs a `tertiary` color, add `tertiary` /
`tertiary-alt` — the slot is in the canonical list
(`inc/enqueue.php:ccc_palette_slug_choices()`), it's just rarely used.

Edit the CSS variables at the top of `styles.css`:

```css
:root {
  --wp--preset--color--cta: #{their-primary};
  --wp--preset--color--cta-alt: #{their-primary-hover};
  --wp--custom--color--cta-opposite: #{their-paired-text};
  ...
}
```

### 3. Swap fonts

In `styles.css`:

```css
--wp--preset--font-family--heading: '{their-heading-font}', system-ui, sans-serif;
--wp--preset--font-family--body:    '{their-body-font}',    system-ui, sans-serif;
```

In `index.html`, update the Google Fonts `<link>` href.

If the prospect's brand uses a non-default weight (TrialPort used Roboto
Flex 500, not 700), update the heading block too:

```css
h1, h2, h3, h4, h5, h6 { font-weight: 500; }
```

And decide whether headings should be uppercase (CCC default for most
client themes) or sentence/title case (TrialPort). Set `text-transform`
accordingly.

### 4. Drop in content

Each section in `index.html` is labeled with the block it maps to and a
pointer to the block's `render.php` (the markup contract source). Replace
copy + image URLs in place. **Do not change class names** — the
`.sg-block-*` and `wp-block-classic-city-core-*` classes are what makes the
demo read as a real CCC site.

For images use Unsplash with `auto=format&fit=crop&w=1920&q=80` query
params. Pick imagery the prospect's industry would actually use.

### 5. Decide on a signature treatment (optional)

If the brand has a signature visual — TrialPort's case was the
fuchsia→purple→cyan gradient applied to logo text and key headlines —
you've got three paths, and you must pick one and **defend it** per the
parent-theme's block rule:

1. **Gutenberg block style** (registered via `register_block_style()`
   in `inc/block-styles.php`). Applies to a core block (`core/heading`,
   `core/paragraph`, `core/group`). No new block. This is how TrialPort's
   `is-style-gradient-text` works.
2. **ACF custom block w/ Gutenberg inside** — for treatments that wrap
   real content. Existing examples: `hero-full-image`, `cta-large`.
3. **Pure ACF custom block** — for fully field-driven sections. Examples:
   `cta-thin`, `stats`, `feature-grid`.

In the demo CSS, prototype the treatment with whatever class name you'd
register in step 1/2/3. The class name in the demo == the class name
when it ships. No throwaway names.

### 6. Sanity check

- Open `index.html` in a browser. Resize to mobile width — does the
  hero stack, the feature grid collapse, the nav links hide?
- Check every `has-{slug}-background-color` actually gets readable
  text (the opposite pair). If not, the `-opposite` variable is missing.
- Click each button. Hover. Does the `-alt` swap fire?
- Scroll the whole page. Does the rhythm feel like a real site, not a
  collage of disconnected blocks?

### 7. Deliver

```bash
cd ~/Demos
zip -r {prospect-slug}-demo.zip {prospect-slug}/
```

Email/Drive the zip. Tell them: "Open `index.html` in any browser. This
is fully static — no server needed."

If they want a hosted URL, Netlify Drop (`netlify.com/drop`) is the
fastest — drag the folder, get a `*.netlify.app` URL in 30 seconds. Don't
hand-roll a deploy for a demo.

---

## Section → block map (the standard 4-section pitch deck)

| Section | Block | render.php |
|---|---|---|
| Hero | `hero-full-image` | `blocks/hero-full-image/render.php` |
| CTA band | `cta-thin` | `blocks/cta-thin/render.php` |
| Stats / proof | `stats` | `blocks/stats/render.php` |
| Features / audiences | `feature-grid` | `blocks/feature-grid/render.php` |
| Logo proof | `logo-strip` | `blocks/logo-strip/render.php` |
| About / how | `split-50-50` | `blocks/split-50-50/render.php` |
| Testimonials | `testimonial-cards` | `blocks/testimonial-cards/render.php` |
| Closing CTA | `cta-large` | `blocks/cta-large/render.php` |

**Nav and footer are NOT blocks** in CCC — they're FSE template parts
(`templateParts` in theme.json). In the demo, build them inline. The
classes `sg-demo-nav` and `sg-demo-footer` carry "this is the demo
shell, not a content block" semantically.

Other available blocks (use when the brief calls for them, but the
standard 4-section pitch doesn't need them):

`center-content`, `document-downloads`, `hero`, `highlighted-image-gallery`,
`icon-feature-row`, `image-columns`, `image-hero-50-50`, `image-overlay`,
`image-tiles`, `image-wall`, `link-pods`, `portfolio-gallery`,
`process-steps`.

---

## Defending a new section

**Don't invent new blocks just for the demo.** Every visual treatment
must already exist in the parent theme OR be defendable as one of three
patterns the parent supports. If you find yourself reaching for
something new, ask: "Is this option 1, 2, or 3?" — if you can't answer,
use an existing block instead.

| Defense | When to use | Implementation in the demo |
|---|---|---|
| **(1) Gutenberg + block style** | Visual variant of an existing core block (heading, paragraph, group, button) | Add `is-style-{name}` to the element. Style it in `styles.css` under a clear "Block style: ..." comment header. |
| **(2) ACF block w/ Gutenberg inside** | New section type that wraps editor-flexible content (text + buttons + images) | Use one of the existing `hero-*`/`split-*`/`cta-large` structures as the markup model. The `<InnerBlocks />` slot becomes the editable area. |
| **(3) Pure ACF custom block** | Fully field-driven section with no editor flexibility (grid of repeater rows) | Use one of `cta-thin`/`feature-grid`/`stats`/`testimonial-cards` as the markup model. Everything author-editable is a flat field or repeater row. |

If a treatment fits NONE of these, that's a smell — re-scope the demo.

---

## Optional: ambient background splotches

For brands with a signature gradient or strong accent palette (TrialPort,
anything SaaS-y), layered low-opacity radial gradients on `<body>` create
a subtle ambient color wash that reinforces brand identity without
competing with content. The trick is that the splotches show through
wherever a block has a transparent background (the natural gaps between
sections) and get hidden under any block with its own bg — so the effect
appears in the rhythm of the page, not the content itself.

Pattern:

```css
:root {
  --sg-bg-splotch-cta:       rgba(R, G, B, 0.08);  /* primary accent */
  --sg-bg-splotch-primary:   rgba(R, G, B, 0.07);  /* secondary accent */
  --sg-bg-splotch-secondary: rgba(R, G, B, 0.06);  /* tertiary accent */
}

body {
  background:
    radial-gradient(70vw 70vw at 92% 3%,   var(--sg-bg-splotch-cta),       transparent 60%),
    radial-gradient(60vw 60vw at  8% 22%,  var(--sg-bg-splotch-primary),   transparent 60%),
    radial-gradient(80vw 80vw at 100% 48%, var(--sg-bg-splotch-secondary), transparent 65%),
    radial-gradient(55vw 55vw at  5% 72%,  var(--sg-bg-splotch-cta),       transparent 60%),
    radial-gradient(50vw 50vw at 88% 92%,  var(--sg-bg-splotch-primary),   transparent 60%),
    var(--wp--custom--body--bg);
}
```

**Defensibility (for moving into the parent theme):** this is a child-theme
`style.css` rule, not a block — it lives outside the block contract entirely.
A child theme can declare the three splotch variables in theme.json's
`settings.custom.bg.splotch.*` and reference them in `styles.css`, or just
inline the rule. Either way, the parent theme doesn't need to know.

**Tuning:** raise alphas to 0.10–0.14 for "very visible," drop to 0.04–0.06
for "barely there." For brands without a gradient signature (more grounded
clients — construction, legal, manufacturing), skip this entirely.

## Gotchas (learned the hard way)

- **`has-{slug}-background-color` must include color on the same rule.**
  In production, this is emitted by `ccc_build_color_pair_helpers_css()`
  at runtime. In the demo, hand-author the bg+color pair. If you forget
  the color half, text becomes unreadable against a colored bg and the
  prospect notices immediately.
- **Button hover needs `!important` on bg-color + color.** WP core
  auto-emits the base bg-color with `!important`; without matching, the
  hover swap loses the cascade. The demo CSS already does this — don't
  remove it.
- **Headings on dark backgrounds need an explicit `color: #FFFFFF`** —
  the global body color rule wins on heading elements unless overridden.
  Both `.sg-block-cta-thin h3` and `.sg-block-cta h2` handle this.
- **`alignfull` only works inside a parent that gives it room to break out.**
  In WP, `useRootPaddingAwareAlignments: true` does this. In the demo,
  `.alignfull` uses `margin: calc(50% - 50vw)` to break out of `<main>`.
- **FontAwesome free CDN is fine for demos**, but the production parent
  loads FA Pro 7 with per-style CSS. Pick icons that exist in both so the
  swap is free.
- **Imagery: Unsplash with cropping params** (`?auto=format&fit=crop&w=1920&q=80`)
  is the fastest path to "looks like a real site." Avoid generic stock-photo
  smiles — pick imagery that reads as their industry.

---

## Reference: TrialPort (2026-06-02)

First demo built using this pattern. Lives at `~/Demos/trialport/`.

- **Source brief:** HubSpot form submission + brand guidelines HTML
  (`~/Downloads/BRAND_GUIDELINES.html`) + scraped content from
  `trialport.com` via WebFetch.
- **Signature treatment:** `is-style-gradient-text` block style applied
  to a `<strong>` inside the hero H1 and the wordmark. Defended as
  Option 1.
- **Audience:** Clinical trial platform — Patients, Communities,
  Sponsors/CROs, Research Sites. Feature grid maps audiences 1:1.
- **Imagery:** Unsplash. Hero photo `1576091160550-2173dba999ef`
  (clinical/hands); about photo `1559757148-5c350d0d3c56` (community
  hands).
- **Time to build:** ~one session, hand-authored (no runbook to follow
  yet — future builds should be substantially faster).
