# Theme Tokens & Color System

The single source of truth for how color, type, spacing, and the other
design tokens work in Classic City Core — and how a child theme overrides
them. Read this before editing any `theme.json` or wondering "where does
this CSS variable come from."

> **TL;DR on the color question:** the neutral system is
> **`canvas` / `panel` / `ink` / `ink-soft`** (backed by a `gray-10…100`
> scale). The old **`light` / `dark` / `light-alt` / `dark-alt`** slots have
> been **removed from Core** (palette, gradients, opposites, `blocks.css`,
> and the `ccc_palette_slug_choices()` picker). Body text binds to `ink`.
> Older child themes whose *content* still uses `has-light`/`has-dark` must
> self-provide those colors via a bridge in their own `style.css` before they
> pull the migrated Core — see "Legacy light/dark (removed)" below.

---

## The canonical color system (the standard for new themes)

This is the locked-in structure every **new** child theme should follow.
`sg-sherman-phalen/theme.json` is the reference implementation. Three rules:

1. **Every main color has an `-alt`.** Brand colors come in pairs — `cta`/
   `cta-alt`, `primary`/`primary-alt`, `secondary`/`secondary-alt`, `tertiary`/
   `tertiary-alt`. The `-alt` is the hover + gradient partner.
2. **Every main color has an `-opposite`, and the opposite is a GRAY** — never a
   raw `#FFFFFF`/`#000000`. The opposite is the *contrasting* end of the gray
   ramp: a **light** color (gold `cta`, `canvas`, `panel`) pairs with
   `--wp--custom--gray--90` (dark text); a **dark** color (`primary`,
   `secondary`, `tertiary`, `ink`, `ink-soft`) pairs with `--wp--custom--gray--20`
   (light text). Tying paired text to the (tintable) gray ramp instead of pure
   black/white is the whole point.
3. **Neutrals, grays, and shadows are a fixed spine.** `canvas` = page bg,
   `panel` = card surface; `ink` = `gray-90`, `ink-soft` = `gray-70`; the
   `gray-10…100` ramp backs everything (and can be tinted per brand — S&P's is
   green-tinted); four shadow depths `sm`/`md`/`lg`/`xl`.

### The full token set (S&P reference, as resolved CSS variables)

```css
/* Brand pairs — base + -alt */
--wp--preset--color--cta: #F7CB45;            --wp--preset--color--cta-alt: #F5C01E;
--wp--preset--color--primary: #1B3A2F;        --wp--preset--color--primary-alt: #2A5447;
--wp--preset--color--secondary: #1B7062;      --wp--preset--color--secondary-alt: #2D8979;
--wp--preset--color--tertiary: #1E3A6E;       --wp--preset--color--tertiary-alt: #2A4A85;

/* Neutrals — surfaces + text */
--wp--preset--color--canvas: #F2F4F1;         /* page background */
--wp--preset--color--panel:  #FFFFFF;         /* card / surface  */
--wp--preset--color--ink:      var(--wp--custom--gray--90);  /* body text     */
--wp--preset--color--ink-soft: var(--wp--custom--gray--70);  /* muted text    */

/* Gradients — one per brand color, 135deg base -> alt (named by the color slug) */
--wp--preset--gradient--cta:       linear-gradient(135deg, var(--wp--preset--color--cta) 0%,       var(--wp--preset--color--cta-alt) 100%);
--wp--preset--gradient--primary:   linear-gradient(135deg, var(--wp--preset--color--primary) 0%,   var(--wp--preset--color--primary-alt) 100%);
--wp--preset--gradient--secondary: linear-gradient(135deg, var(--wp--preset--color--secondary) 0%, var(--wp--preset--color--secondary-alt) 100%);
--wp--preset--gradient--tertiary:  linear-gradient(135deg, var(--wp--preset--color--tertiary) 0%,  var(--wp--preset--color--tertiary-alt) 100%);

/* Opposites — every main color, paired to a GRAY (gray-20 light / gray-90 dark) */
--wp--custom--color--cta-opposite:           var(--wp--custom--gray--90);   /* gold  -> dark text  */
--wp--custom--color--cta-alt-opposite:       var(--wp--custom--gray--90);
--wp--custom--color--primary-opposite:       var(--wp--custom--gray--20);   /* dark  -> light text */
--wp--custom--color--primary-alt-opposite:   var(--wp--custom--gray--20);
--wp--custom--color--secondary-opposite:     var(--wp--custom--gray--20);
--wp--custom--color--secondary-alt-opposite: var(--wp--custom--gray--20);
--wp--custom--color--tertiary-opposite:      var(--wp--custom--gray--20);
--wp--custom--color--tertiary-alt-opposite:  var(--wp--custom--gray--20);
--wp--custom--color--canvas-opposite:        var(--wp--custom--gray--90);   /* light surface -> dark text */
--wp--custom--color--panel-opposite:         var(--wp--custom--gray--90);
--wp--custom--color--ink-opposite:           var(--wp--custom--gray--20);   /* dark text -> light on it   */
--wp--custom--color--ink-soft-opposite:      var(--wp--custom--gray--20);

/* Gray ramp — the single neutral source (tint per brand). */
--wp--custom--gray--10: #ffffff;  --wp--custom--gray--20: #f7f9f8;  --wp--custom--gray--30: #ecf0ee;
--wp--custom--gray--40: #cad4d0;  --wp--custom--gray--50: #a5b6af;  --wp--custom--gray--60: #687f75;
--wp--custom--gray--70: #495952;  --wp--custom--gray--80: #333f3a;  --wp--custom--gray--90: #1c2220;
--wp--custom--gray--100: #0c0f0e;

/* Shadow depths */
--wp--preset--shadow--sm: 0 1px 2px rgb(0 0 0 / 0.06), 0 1px 1px rgb(0 0 0 / 0.04);
--wp--preset--shadow--md: 0 4px 12px rgb(0 0 0 / 0.08), 0 2px 4px rgb(0 0 0 / 0.06);
--wp--preset--shadow--lg: 0 10px 24px rgb(0 0 0 / 0.10), 0 4px 8px rgb(0 0 0 / 0.08);
--wp--preset--shadow--xl: 0 20px 40px rgb(0 0 0 / 0.12), 0 8px 16px rgb(0 0 0 / 0.08);
```

In `theme.json` these live as: brand pairs + `canvas`/`panel`/`ink`/`ink-soft`
in `settings.color.palette`; the gray ramp in `settings.custom.gray`; the
opposites in `settings.custom.color.{slug}-opposite`; the gradients inherited
from the parent (don't re-declare them — see "Gradients"); shadows in
`settings.shadow.presets`. `ink`/`ink-soft` reference the gray ramp rather than
hard-coding a hex, so they re-tint automatically with the ramp.

---

## Where tokens live

| Layer | File | Role |
|---|---|---|
| Parent baseline | `theme.json` | Declares the full palette, gradients, type scale, spacing, shadows, layout, and `settings.custom.*` tokens. Children inherit everything they don't override. |
| Per-client overrides | `sg-{slug}/theme.json` | Overrides palette colors, fonts, `custom.*` tokens (radius, border, gray scale, opposites), and any `styles.elements` tweaks. |
| Runtime palette CSS | `inc/enqueue.php` | `ccc_build_color_pair_helpers_css()` emits the `.has-{slug}-background-color` + paired-text rules and button-hover rules from the active palette. `ccc_palette_slug_choices()` is the canonical slug list. |
| WP default stripping | `inc/strip-wp-defaults.php` | Removes WP core's default palette/gradients/font-sizes so the theme.json is authoritative. |

---

## Palette

Slugs (emitted as `--wp--preset--color--{slug}`):

**Brand pairs** — each has a base + `-alt` (used for hovers and gradients):
- `cta` / `cta-alt` — primary action color (buttons, key CTAs)
- `primary` / `primary-alt`
- `secondary` / `secondary-alt`
- `tertiary` / `tertiary-alt`

**Neutrals (canonical):**
- `canvas` — page background (a near-white tint; = `gray-20`)
- `panel` — card/surface background (usually white; = `gray-10`)
- `ink` — body text + dark surfaces (= `gray-90`)
- `ink-soft` — muted text (= `gray-70`)

**Custom gray scale** — `settings.custom.gray.10 … 100` (`--wp--custom--gray--N`).
`canvas`/`panel`/`ink`/`ink-soft` are defined as references into this scale in
the parent; child themes can tint the whole scale (e.g. S&P uses green-tinted
grays) and the neutrals follow.

**Opposites** — `settings.custom.color.{slug}-opposite` declares the readable
text color paired with each background slug. This drives the combined helper:
`.has-cta-background-color` automatically gets `cta-opposite` text. **Every
slug you use as a background needs an opposite, and the opposite is a GRAY-ramp
value (`gray-20` or `gray-90`) — never raw `#fff`/`#000`** (see "The canonical
color system" above). E.g. S&P's gold `cta` pairs with `gray-90` dark text.

### theme.json merge semantics (important, non-obvious)

- **`settings.color.palette` is an ARRAY → the child REPLACES the parent's
  entirely.** A child palette must list **every slug it uses** (the brand pairs
  + `canvas`/`panel`/`ink`/`ink-soft`). Omitting a slug that Core's `styles`/CSS
  references leaves that `--wp--preset--color--{slug}` undefined.
- **`settings.custom.*` is an OBJECT → deep-merged.** You can override a single
  token (e.g. `custom.radius.default`) and inherit the rest.
- **`settings.typography.fontFamilies` is an ARRAY → replaced.** List every
  family you want (heading, body, …).
- **`styles.*` is deep-merged.** You can override just `styles.color.text` or a
  single `styles.elements.h2.typography` property and inherit the rest.

---

## The three font ROLES (and the array-replace trap)

The parent declares three font families by ROLE, not by typeface name:
`heading`, `body`, and `accent`. Every element rule in the generated stylesheet
resolves through the role variable — `h1 { font-family:
var(--wp--preset--font-family--heading) }` — which is what lets the Font Lab
(`inc/class-ccc-font-lab.php`) restyle a whole site by reassigning one custom
property.

`accent` defaults to `var(--wp--preset--font-family--body)`, so adding it is a
zero-regression change: it computes identically until something overrides it.

**The trap:** `settings.typography.fontFamilies` is an ARRAY, and child arrays
REPLACE the parent's rather than merging into it. A child that declares only
`heading` and `body` silently drops `accent` — the preset variable is never
emitted and anything referencing it falls back to nothing. **Every child theme
must declare all three roles itself.** This is the same rule as
`color.palette`; see the merge notes above.


## Legacy `light` / `dark` (removed)

`light` / `light-alt` / `dark` / `dark-alt` were the **old** neutral system,
predating canvas/panel/ink. They have been **removed from Core**: gone from the
`theme.json` palette + gradients + opposites, from `ccc_palette_slug_choices()`
(so the block bg-color pickers no longer offer them), and from every rule in
`assets/blocks.css` (migrated to `canvas`/`panel`/`ink`/`ink-soft` + `gray-40`).
`styles.color.text` binds to `ink`. New children build on canvas/panel/ink only
— no legacy aliases needed (`sg-sherman-phalen` is the reference).

### Older child themes that still use `has-light`/`has-dark` in content

Some launched sites' **content** (e.g. TexBuilt — 5 pages) uses
`has-light`/`has-dark` background helpers. WP core still emits the *background*
color for those as long as the child's own `theme.json` keeps `light`/`dark` in
its palette, but Core's pair-helper no longer emits the paired **text** color,
and Core's `blocks.css` now references `canvas`/`panel`/`ink`/`ink-soft`/
`gray-40` which such a child doesn't define. So **before an old child pulls the
migrated Core**, add a bridge to its own `style.css` that (a) maps the new
neutral tokens to the child's existing palette and (b) re-asserts the
`has-light`/`has-dark` text pairing. `sg-texbuilt/style.css` is the reference
bridge:

```css
:root {
  --wp--preset--color--canvas:   var(--wp--preset--color--light);
  --wp--preset--color--panel:    #ffffff;
  --wp--preset--color--ink:      var(--wp--preset--color--dark);
  --wp--preset--color--ink-soft: var(--wp--preset--color--dark-alt);
  --wp--custom--gray--40:        var(--wp--preset--color--light-alt);
  --wp--custom--color--canvas-opposite:   var(--wp--custom--color--light-opposite);
  --wp--custom--color--panel-opposite:    #000;
  --wp--custom--color--ink-opposite:      var(--wp--custom--color--dark-opposite);
  --wp--custom--color--ink-soft-opposite: var(--wp--custom--color--dark-alt-opposite);
}
.has-light-background-color     { color: var(--wp--custom--color--light-opposite,#000) !important; }
.has-light-alt-background-color { color: var(--wp--custom--color--light-alt-opposite,#000) !important; }
.has-dark-background-color      { color: var(--wp--custom--color--dark-opposite,#fff) !important; }
.has-dark-alt-background-color  { color: var(--wp--custom--color--dark-alt-opposite,#fff) !important; }
```

**Propagation is per-repo and deliberate.** Each client repo carries its own
`classic-city-core` subtree; the Core change reaches a client only when that
repo runs `git subtree pull --prefix=… upstream-parent main --squash` and
redeploys. Nothing live changes until then — so add the bridge first, then
pull. (Lumberock has near-zero content and no `has-light`/`has-dark` usage, so
it needs no bridge.)

---

## Dark themes

The neutral system is direction-agnostic: `canvas`/`panel`/`ink`/`ink-soft` are
**roles**, not fixed light values. A **light** theme points `canvas`/`panel` at
the light end of the ramp and `ink`/`ink-soft` at the dark end (S&P: canvas=
gray-20, panel=gray-10, ink=gray-90, ink-soft=gray-70). A **dark** theme flips
which end each role reads — nothing else changes:

- `canvas` = a dark stop (gray-80/90), `panel` = one step lighter (gray-70/80,
  the "elevated" surface); `ink` = a light stop (gray-10/20), `ink-soft` = a
  muted light (gray-30/40).
- **Opposites flip too.** An opposite is the readable text color *on* that slug
  as a background: dark surfaces (canvas/panel + dark brand colors) → light text
  (`gray-20`); light/bright slugs → dark text (`gray-90`). **Watch bright
  accents:** a light cyan `tertiary` needs `tertiary-opposite: gray-90` (dark
  text) even when the other brand colors all take `gray-20`.
- Brand pairs, gradients (inherited), and the tinted ramp are unchanged.
  `styles.color.background` = `var(--…--canvas)` and `styles.color.text` =
  `var(--…--ink)` already, so **a dark canvas + light ink IS the dark theme** —
  no `styles` gymnastics. New dark children need no `light`/`dark` bridge (that's
  only for legacy content, above).

**`sg-trialport` is the dark reference** — canvas=gray-90 `#1b1b23`, panel=gray-80
`#323241`, ink=gray-10 `#fff`, ink-soft=gray-40, on a purple-tinted ramp
(`buildRamp('#9998B0')`). Caveat: the parent's `assets/blocks.css` was authored
against light surfaces in spots; dark-mode block fixes belong in the child
`style.css`, layered in during the style-guide restyle (SOW step 1).

---

## Gradients

Defined in `settings.color.gradients`. The parent ships `cta`, `primary`,
`secondary`, `tertiary` as 135° `base → alt` ramps, **named by the color slug**
(so `--wp--preset--gradient--cta` pairs with `--wp--preset--color--cta`). New
child themes should **inherit these — don't re-declare a gradients array** (a
child gradients array *replaces* the parent's entirely, and naming them by hue
like `gold`/`forest`/`teal` decouples them from the color slugs). The standard
is: gradient name == color name.

---

## Typography

- **Fonts:** declare families in `theme.json` `settings.typography.fontFamilies`
  (slugs `heading`, `body`), and **enqueue the webfont in the child's
  `functions.php`** under the `sg-{slug}-google-fonts` handle. theme.json names
  the family; functions.php loads it. (theme.json `fontFace` with `src` is only
  used for self-hosted files.)
- **Heading case:** the parent forces **UPPERCASE, weight 700** on `h1`–`h6`
  (`styles.elements.h*`). A client with a serif/editorial face (e.g. S&P's
  Gentium Plus) must override `styles.elements.h1…h6.typography` with
  `textTransform: "none"`, the desired `fontWeight`, and a looser `lineHeight`.
  Inherit `fontSize`/`fontFamily` by only overriding the properties you change.
- **Heading size scale:** `settings.custom.fs.h-1 … h-6` (+ `-min` variants for
  the fluid `clamp()`). The parent provides both; a child overriding only
  `h-1` keeps the parent's `h-1-min`.

---

## Other custom tokens (`settings.custom`)

- `radius.default`, `border.default-width`, `border.color`
- `heading.letter-spacing`, `heading.base-font-size`, `eyebrow.letter-spacing`
- `body.base-font-size` (the body **background** is `styles.color.background` →
  `var(--wp--preset--color--canvas)`; there is no separate `body.bg` token)
- `btn.padding-x`, `btn.padding-y`
- `layout.narrow-size`
- `icons.style` — FontAwesome family (`solid` | `regular` | `light` |
  `sharp-light` | …), resolved in `inc/enqueue.php`
- `textures.{slug}` — drives `has-bg-texture-{slug}` (see `inc/textures.php`)

---

## From a sitemap-studio export

A new client's brand arrives as the `brand` block of the sitemap-studio export
(`~/Local Sites/sitemap-studio/exports/{slug}-*.json`). It is pre-resolved —
map it straight into the child `theme.json`:

| Export field | → theme.json |
|---|---|
| `brand.resolvedCssVars.--color--{slug}` | `settings.color.palette` (cta, primary, secondary, tertiary + `canvas`/`panel`); set `ink` → `var(--wp--custom--gray--90)` and `ink-soft` → `var(--wp--custom--gray--70)` (reference the ramp, don't hard-code the hex) |
| `brand.resolvedCssVars.--color--gray-N` | `settings.custom.gray.N` (the tinted ramp; neutrals + opposites reference it) |
| `brand.resolvedCssVars.--color--{slug}--opposite` | `settings.custom.color.{slug}-opposite` — **convert the hex to the matching gray-ramp value**: `gray-20` where the export gives near-white, `gray-90` where it gives near-black (never raw `#fff`/`#000`) |
| `brand.gradients.items[]` | **Don't re-declare** — inherit the parent's gradients (named `cta`/`primary`/`secondary`/`tertiary` by color slug). |
| `brand.headingFont` / `bodyFont` | `settings.typography.fontFamilies` (heading/body) |
| `brand.fontHref` | the Google-Fonts `wp_enqueue_style` URL in the child `functions.php` |
| `brand.headingFont.transform: "sentence"` | override `styles.elements.h1…h6` → `textTransform:"none"` (Core defaults to uppercase) |
| `brand.radius` / `borderWidth` / `iconStyle` | `custom.radius.default` / `custom.border.default-width` / `custom.icons.style` |
| `brand.shadows.{small,medium,large}` | `settings.shadow.presets` (`sm`/`md`/`lg`; extrapolate `xl`) |

Build on canvas/panel/ink/ink-soft only — no legacy `light`/`dark` slugs.
`sg-sherman-phalen/theme.json` is the worked reference. The export's `homepage`
block → see `CLIENT_HOMEPAGE.md`.

## Child-theme CSS token discipline (review checklist)

The bar a child `style.css` must clear before review (standard set on the
Tower Leadership build — `sg-tower-leadership` is the worked reference):

- [ ] No raw `px`/hex/`rgba()` where a token exists — padding/borders →
  spacing/border tokens; font sizes → existing `fs`/`font-size` presets.
- [ ] Snap values to the **existing shared scale** (spacing presets, fs
  presets, border tokens) rather than minting comp-exact custom tokens —
  consistency over comp fidelity.
- [ ] New `settings.custom` tokens only for **genuinely new concepts**
  (e.g. a frame width), never to preserve a comp-exact value.
- [ ] Component-internal spacing that tracks type size (button padding)
  stays in **em**, per the parent's own convention — never override an
  em token with px.
- [ ] Gradient stops reference palette/custom color vars, not literal hexes.
- [ ] Shadow and hairline alphas via `color-mix()` over palette vars, not
  `rgba()` literals.
- [ ] No dead zeroed properties (a `border-radius: 0` that overrides
  nothing is code to delete).
- [ ] Cards with a hover shadow also carry the rest-state
  `elevation--card` (under `elevation--raised`).
- [ ] No per-heading text colors — headings inherit; the pair-helpers
  color them per surface.
- [ ] Only literals left in the file: the theme header and media-query
  breakpoints.

## See also

- `docs/CLIENT_HOMEPAGE.md` — authoring a client homepage from blocks
- `docs/CLIENT_ONBOARDING.md` — spinning up the WPE install + repo + child theme
- `inc/enqueue.php` — the pair-helper + FontAwesome runtime CSS
- `../CLAUDE.md` — the `acf-innerblocks-container` flow-layout trap and the
  `.sg-hero` marker convention
