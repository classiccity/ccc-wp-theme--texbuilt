# Logo Strip — usage

**Purpose:** Social proof at a glance — a row of client/partner/press logos saying "these people trust us" without a word of copy.

**Content shape:** Logos repeater, min 2 / max 20 (logo image only; demo placeholder if missing) — 5–10 is typical. Optional eyebrow line above ("Trusted by teams across Georgia"). Two layouts: static grid (default; wraps rows, 5-col desktop / 2-col mobile) or marquee scroller (single infinite row; speed slow/medium/fast, direction, pause-on-hover; honors prefers-reduced-motion). Two appearance toggles, **both off by default**: Light Plate Behind Logos and Desaturate Logos. Logos are not linked.

**Use when:**
- Source shows a "trusted by / as seen in / our partners" logo row — this is the only block for that job.
- You have 5+ logos and want motion — scroller mode; the marquee implies "too many to fit."
- A hero needs immediate credibility under it — logo strip is the classic under-hero proof band.

**Avoid when:**
- Fewer than ~4 logos — a sparse strip undermines the proof; fold the names into a paragraph or a testimonial instead.
- Each logo needs a caption, quote, or link — use `testimonial-cards` (attributed quotes) or `image-link-cards` (linked cards).
- The images aren't logos (photos, badges with copy) — use `image-tiles` or `image-wall`.

## Light Plate Behind Logos (`light_plate`, default OFF)

Paints a pale panel — white, via `--sg-logo-plate-bg` — behind the whole strip, with the shared 6px radius. Turn it on when the section behind the strip is dark. Leave it off on a light section, where it would draw a white box on a white page for no reason.

**One plate for the strip, not one per logo.** Logo files arrive as a mix: some transparent, some with the organisation's own flat white background baked into the PNG. Against a single white plate that baked-in background is invisible — the seam disappears. Per-logo plates would instead frame each of those baked-in white boxes inside a second box, and any tone mismatch between plate and artwork would show as a visible rectangle around some marks and not others. On trialport's Live Network page, six of fifteen marks ship with a baked-in white background; with the single plate you cannot tell which six.

The plate takes inline padding in grid mode only. In scroller mode the clip box *is* the padding box, so inline padding would not hold the marquee off the edge — it would only make the plate wider. Logos running edge to edge is correct there.

## Desaturate Logos (`grayscale`, default OFF)

Applies `filter: grayscale(1)` to the row. **Until 2026-08-21 this was unconditional CSS on `.sg-block-logos-row` with no way to switch it off.** It is a field now, and off by default, for two reasons.

**1. These are usually registered marks, and their colour is part of them.** Desaturating a third party's trademark is not a site-level styling decision to make in a stylesheet on their behalf. If a site wants the muted look, someone should have agreed to it; the toggle is where that agreement gets recorded.

**2. On a dark canvas it does not merely dim marks — it deletes parts of them.** Measured on trialport's canvas (`#1b1b23`) with the filter on: The Ehlers-Danlos Society came out at **1.11:1** and 100% of its ink under 3:1 — an empty space where a logo should be. Queen Mary measured **1.37:1** on the same basis.

**The durable lesson is the partial failures, not the total ones.** An invisible logo is a hole in the row; a reader sees a gap and moves on. A *half*-vanished logo is worse, because it still reads — as something else. Greyscaled on that canvas, Rare Revolution Magazine lost 43% of its ink and rendered as the single word "REVOLUTION". VWD Alliance lost 58%, dropping the word "Alliance". The Spark Global lost 64%, keeping the globe and losing the name. A logo strip on a page about trust that silently renames three of the organisations on it is a worse outcome than one that shows nothing at all. Whenever you turn greyscale on, check what survives, not just whether it looks tasteful.

**Greyscale and the light plate are independent.** Greyscale on a plated strip is legible; the plate is what fixes contrast, and it fixes it for every mark at once **without altering a single one**, which is the point. Reach for the plate first.

## Markup contract

```
.sg-block-logos[.--plated][.--grayscale]
  └ p.sg-block-eyebrow            (optional)
  └ .sg-block-logos-viewport      (always emitted)
      └ .sg-block-logos-row       (logos; emitted twice in scroller mode)
```

The viewport is always present and does two jobs: it is the scroller's `overflow: hidden` clip box, and it is the surface the plate paints on. The plate cannot live on `.sg-block-logos` (the eyebrow would land on the plate) nor on `.sg-block-logos-row` (the marquee translates that element, so its background would slide away with it).

**Child-theme hooks:** `--sg-logo-max-height`, `--sg-logo-gap`, `--sg-logo-scroll-dur`, `--sg-logo-plate-bg`, `--sg-logo-plate-pad`.

**Sizing caveat:** the strip caps every logo to one height (60px in scroller mode), so a wide wordmark reads large and a square mark reads small. That is normal for a logo strip, but with a very mixed set it can make the square marks look like afterthoughts. There is no per-logo scale field; if you need one, that is a block change, not a CSS override.

**Pairs with:** any hero directly above, testimonial-cards, stats — the proof cluster; give it a heading or eyebrow rather than an orphan band (rules 9, 10).

**Core alternative:** None worth it — core gallery/columns can't normalize logo sizing; if there are only 2–3 logos, mention the names in body copy instead.
