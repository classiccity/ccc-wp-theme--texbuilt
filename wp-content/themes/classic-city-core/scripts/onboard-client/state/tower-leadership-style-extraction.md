# Tower Leadership — Brand & Style Extraction

Extracted 2026-07-16 from `~/Downloads/TowerLeadership.html` (a JS-bundled
single-file export of their designed page). Decoded assets live at
`~/Downloads/tower-leadership-extracted/` (fonts/, images/, and
`leadership-page-source.html` — the unpacked markup + full CSS).

**Note:** the sample page is NOT a homepage. It is "Leadership & Client
Delivery" (Meet the Team / About). Its nav references sibling pages that were
not provided: Who We Are, Our Founder, Our Approach, What Makes Us Different,
Who We Help, Services, Events, Resources, Careers, Contact.

## Who they are

Dental-practice business consulting (advisory + consulting + accounting for
"elite dental entrepreneurs"). CEO: Matthew Maffei (co-host, Dental Wealth
Podcast). Three tiered programs, each with its own accent:

| Program | Role | On-white | On-black | Gradient (90deg, light→dark) |
|---|---|---|---|---|
| Momentum | entry | `#4067B0` | `#3EA8DE` | `#6FC6EE → #3EA8DE → #1E5F9C` |
| Mastery | mid | `#F68E1F` | `#F9AC1B` | `#FFD27A → #F9AC1B → #C56F0E` |
| Multiplier | top | `#121012` | `#A6A8AB` | `#C8CACC → #6E6E70 → #121012` |

## Core palette

| Token (theirs) | Hex | CCC mapping suggestion |
|---|---|---|
| Tower Black | `#110E11` | dark-section bg / ink |
| Paper White | `#F7F7F7` | **canvas** (page bg — the site is LIGHT) |
| Pure White | `#FFFFFF` | **panel** |
| Tower Primary Blue (dark bgs only) | `#3EA8DE` | accent-on-dark / link-on-dark |
| Tower Secondary Blue (light bgs only) | `#4067B0` | **primary accent / buttons / links** |
| Neutral ramp 50→950 | `#F7F7F7 #F0EFF0 #E2E0E2 #C8C5C8 #9A969A #6E6A6E #4A464A #2C292C #1E1C1E #110E11 #0B0A0B` | gray-10…100 (tinted toward Tower Black — purple-ish gray) |
| Footer bg | `#0B0A0B` (neutral-950) | |

Critical brand rule: the two blues are **surface-conditional** — `#3EA8DE`
only on black, `#4067B0` only on white. Button primary on light: bg
`#4067B0`, hover `#345897`. Button primary on dark: bg `#3EA8DE`, text
Tower Black, hover `#5FBCE8`, blue glow shadow
`0 0 0 1px rgba(62,168,222,.35), 0 14px 38px -10px rgba(62,168,222,.45)`.
Ghost buttons: transparent, 1.5px border (black on light / white@60% on dark).

## Typography

- **Primary: Gotham** (real licensed Hoefler OTFs bundled — weights
  100/200/300/400/450/500/700/800/900 + italics 100/200/400/700).
  Body = Book 400; headlines = Bold 700 / Black 800.
- **Accent: Operetta 18** (licensed OTFs, weights 200–900). Usage rule from
  their CSS: *italic, weight 900, ≤3 consecutive words*, typically colored
  program blue. E.g. hero: "The leaders *driving* client success".
- Fallbacks: `"Gotham","Helvetica Neue",Arial,sans-serif`;
  `"Operetta","Didot","Bodoni Moda",Georgia,serif`; mono for step numbers
  (`01 02 03…`) and footer legal.
- **All headings UPPERCASE**, tight tracking (−0.01 to −0.015em), tight
  leading (0.95–1.2). Display `clamp(56px,8vw,112px)`; h1
  `clamp(40px,5vw,72px)`; h2 `clamp(32px,4vw,52px)`; h3 28px; h4 22px.
- Eyebrows: 12–13px, 700, letter-spacing 0.16–0.18em, uppercase, colored
  secondary-blue on light / primary-blue on dark.
- Body 17px / 1.6; lead paragraphs 18–20px; body color = neutral-600
  (`#4A464A`) on light, `rgba(255,255,255,0.74–0.8)` on dark.
- Buttons: 13px / 700 / tracking 0.14em / uppercase, padding 16×28,
  radius 4px.

## Signature visual moves

1. **The "Tower frame"**: image containers with two square + two 48px-round
   corners — `border-radius: 0 48px 0 48px` — wrapped in a 2px
   **gradient border** (135deg Momentum gradient). Used on portraits
   (3:4 / 4:5). Team-grid photos use a milder `0 24px 0 24px`.
2. **6px gradient top-bar** on cards (program gradient), card = pure white,
   1px `#E2E0E2` border, no radius; hover lifts −4px + shadow-lg.
3. **Dark inverse sections**: Tower Black bg for hero / CTA band / (footer
   `#0B0A0B`), with white@74–80% body text and primary-blue accents.
4. Hero: full-bleed photo + left-to-right dark gradient overlay
   (`rgba(17,14,17,.92) → .2` at 90deg), eyebrow + uppercase display with
   one Operetta-italic word.
5. Numbered process list: 2-col grid, 1px top border per item, mono `01`
   numbers in secondary blue.
6. Shadows are Tower-Black-tinted, e.g. lg =
   `0 18px 40px -12px rgba(17,14,17,.22), 0 2px 6px rgba(17,14,17,.08)`.
7. Motion: `cubic-bezier(.2,.7,.1,1)` ("assertive"), 140/240/420ms.
8. Radii scale: 2/4/8/16/24/48(tower)/pill. Spacing scale 4→128px.
   Section padding ~116px vertical, 56px horizontal (64/24 mobile).

## Page structure of the sample (block-mapping fodder)

1. Sticky black header — logo, uppercase nav, Member Login + Contact button
2. Hero — bg photo, gradient overlay, eyebrow, h1 w/ accent word, 2 buttons
   (primary + ghost)
3. CEO split feature — Tower-frame portrait left, eyebrow/h2/role/3 paras/
   2 buttons right
4. Team grid — neutral-100 bg, intro block, 3 portrait cards (photo w/
   `0 24px 0 24px` radius, name, blue role, bio, "Focus areas" footnote
   with top border)
5. Three pillars — white cards, 6px Momentum-gradient bar, h3 + body
   (Advisory / Consulting & Implementation / Financial Infrastructure)
6. Process — neutral-100 bg, intro, 2×2 numbered steps (01–04)
7. "Who we work with" split — Tower-frame photo + eyebrow/h2/2 paras
8. CTA band — Tower Black, centered, eyebrow/h2/sub/2 buttons
9. Footer — `#0B0A0B`, brand + tagline + 5 social circles, 4 link columns,
   mono legal row

## Assets inventory (`~/Downloads/tower-leadership-extracted/`)

- `images/tower-logo.png` — white/blue logo (dark-bg version)
- `images/hero-team.jpg` — team photo used behind hero
- `images/matthew-maffei.png`, `lisa-gotsis.jpg`, `jordan-blackmon.png`,
  `melissa-williamson.png` — portraits
- `images/practice-owner.jpg` — "who we work with" photo
- `fonts/` — Gotham ×13 + Operetta ×8 OTFs (decompressed, weight-named)

## Licensing flag

Gotham (Hoefler&Co) and Operetta (TypeMates) are commercial fonts. The
client bundled their licensed files; self-hosting on their site is
presumably covered by THEIR license, but confirm before shipping to
production. For the demo build, self-host in the child theme.
