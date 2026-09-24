# Text Callout — usage

**Purpose:** The scroll-stopper. One or two sentences set at heading scale but written as prose — a mission line, a promise, a refusal — dropped between sections to make a reader pause. It carries no card, no background and no icon; the size and the space around it do all the work.

**Content shape:** Two fields. `width` (`narrow` default | `content` | `wide` | `full`) and `content` (WYSIWYG, basic toolbar, required). Keep it to 1–2 sentences — this block's whole effect comes from being short at a large size, and a paragraph of body copy set this big just reads as broken. Bold the 2–3 words carrying the idea; each child theme styles `p > strong` with its own emphasis treatment, so bolding is how you mark meaning here, not how you shout (rule 7 — never hand-set a color on the run).

Renders a bare `<div>` wrapping the paragraph. The div is the child theme's extension point for `::before` / `::after` flourishes. Reserves `spacing--60` above itself **and above whatever follows** (rule 8: the breathing room is the block's, not the neighbour's problem) — override with `--ccc-tc-space`.

**Use when:**
- Source copy contains a line that IS the argument — a positioning statement, a principle, a plain-language promise — and burying it in a paragraph would waste it.
- Two dense sections need a beat between them, and a colored band would be the wrong kind of loud (rule 15: no two colored bands back-to-back — this is the quiet alternative).
- A page is running long on cards and grids and needs a change of texture that isn't another container.

**Avoid when:**
- The line is a quotation attributed to a person — use `core/quote` with `is-style-quote` (rule 23: quote blocks are for actual quotations) or `testimonial-cards`.
- You have more than two sentences — use a core paragraph, or split the idea and callout only the sharp half.
- It would be the section's heading — use a real `h2` in a core group (rules 9, 18). This block is prose set large, not a heading; it carries no heading semantics and screen readers will not treat it as one.
- Several would land on one page. Two is a rhythm; four is a gimmick, and each one steals impact from the rest.

**Pairs with:** Anything — it's a punctuation mark between sections. Natural neighbours are `split-50-50`, `feature-grid`, `card-detail-rows`; often sits just before a closing `cta-large`.

**Core alternative:** A core paragraph with a large font-size preset gets close, but capping the measure at narrow, reserving space on both sides, and giving `p > strong` a per-theme treatment all need CSS that would have to be inline on the paragraph (rules 1, 2, 4). Use this block instead.
