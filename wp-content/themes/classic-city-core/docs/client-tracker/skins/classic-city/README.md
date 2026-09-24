# Classic City skin for the client tracker

The classiccity.com platformer look — a blue sky and cloud band at the top
of the page, rolling hills and a grass ground strip (with the owl perched on
it) at the bottom, chunky bordered cards, the pixel-dash link underline, and
Chris's headshot delivering the round letter — applied on top of the stock
tracker page. The scene is static apart from the owl's blink.

**This skin ships as the generator's DEFAULT.** A plain
`python3 build-tracker.py round-2.json` finds this folder next to the script
and produces the skinned page under the ordinary `round-2.html` name — no
flag needed:

```
python3 build-tracker.py tracker.example.json              # this skin, by default
python3 build-tracker.py tracker.example.json --internal
python3 build-tracker.py tracker.example.json --no-skin    # opt OUT → {name}.plain.html
```

**With `--no-skin` the generator's output is byte-identical to what the
unskinned build always produced** — the skin is four string hooks in
`skin.py` (`head_extra`, `body_start`, `body_end`, `css`), all of which are
empty when no skin is loaded.

Everything is inlined at build time as `data:` URIs (SVGs URL-encoded,
fonts and the Chris PNG base64), read from `assets/` next to `skin.py`.
The base page's invariants hold for skinned builds too: zero external
requests, no internal fields in the client build, print-friendly (the
scene, tilt and offset shadows all disappear under `@media print`), and the
skin is day-only — dark-OS users get the same daytime look on purpose.

## The one rule

The base tracker's promise is *amber always and only means "waiting on the
client"*. In this skin that slot is the **brand orange** — which is why no
other STATUS colour goes near it: links are sky blue (purple on hover),
done is CCC green, review is CCC purple, blocked is CCC red, and everything
queued/deferred is cool slate. The full-colour logo (and favicon) keep
their orange bolt — brand chrome, not a status.

## Where the assets came from

All copied from the classiccity.com theme, read-only:
`~/Local Sites/classic-city-consulting/app/public/wp-content/themes/classic-city-consulting/`
(THEME below).

| File | Source |
|---|---|
| `CulturesCarnival.woff2` | `THEME/fonts/CulturesCarnival.woff2` |
| `ArcadePixel-Regular.otf` | `THEME/fonts/ArcadePixel-Regular.otf` |
| `Quicksand-400.woff2`, `Quicksand-700.woff2` | downloaded from the two `fontFace` URLs in `THEME/theme.json` (`classiccity.com/wp-content/uploads/fonts/…`) |
| `Quicksand-500-600.woff2` | official Google Fonts latin file (`fonts.gstatic.com/s/quicksand/v37/…`, one variable-weight woff2 declared for 500–600) — the 500 body weight; 400 was too thin for a dense page |
| `logo-icon.svg` (favicon) | `THEME/images/logo-icon.svg` |
| `logo-full.svg` (banner) | `THEME/images/logo.svg`, verbatim — the full-colour wordmark with the orange gradient bolt (round 2 swapped this in over the earlier ink-only extraction from `THEME/parts/header.html`) |
| `headshot-chris.jpg` | downloaded from `classiccity.com/wp-content/uploads/2025/10/square-chris-headshot.jpg` (found via the homepage markup; the older `2024/11/headshot-chris.png` URL only serves an 86px thumbnail), downscaled to 260px with `sips`, JPEG q78, 13 KB. Replaced the pixel `chris.png` sprite in round 2 |
| `bg-clouds.svg`, `bg-rolling-hills.svg` | `THEME/images/` |
| `terrain_grass_block_top.svg` (+ `_left`/`_right`, copied for future caps but not currently inlined) | `THEME/platforms/Tiles/` |
| `coin_gold.svg`, `block_exclamation.svg`, `flag_red_a.svg`, `gem_green.svg`, `heart.svg` | `THEME/platforms/Tiles/` |
| `character_owl_idle.svg`, `character_owl_blink.svg` | `THEME/platforms/Characters/` |

The pixel-dash link underline technique is lifted verbatim from
`THEME/scss/_buttons.scss`.

## Licensing

- `platforms/*` artwork (terrain, coin, block, flag, gem, heart, owl) is
  Kenney game-asset art, **CC0** — free to use anywhere.
- **Cultures Carnival** and **ArcadePixel** are licensed to Classic City —
  **do not reuse outside CCC projects**.
- **Quicksand** is under the SIL Open Font License (OFL).
- The logo, Chris sprite, clouds and hills are Classic City's own artwork.
