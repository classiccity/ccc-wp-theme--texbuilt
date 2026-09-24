# Testimonial Cards — usage

**Purpose:** Multi-voice social proof — a grid of quote cards (quote glyph, quote text, name, title + company) showing that several real people vouch for the client.

**Content shape:** Pulls from the Testimonial CPT — the block itself stores only WHICH testimonials (post_object, multiple, required; selection order = display order), desktop columns 1–4 (default 3 — fit the count, rule 19), and mobile layout (`column-count` stack or `horizontal-scroll` carousel). Per-testimonial content (quote, company_name, job_title) lives on the CPT entry, so importing testimonials means CREATING CPT posts first, then selecting them here. Quotes of 2–4 sentences ballpark; wildly uneven lengths make ragged cards. Renders nothing until entries are selected.

**Use when:**
- Source page shows 2+ attributed customer quotes — names (ideally with role/company) attached to words they said.
- Testimonials will be reused across pages — the CPT makes them a shared library, not page-locked copy.
- Proof should feel like a chorus (several voices) rather than one big endorsement.

**Avoid when:**
- There's ONE standout quote — use core/quote with `is-style-quote` (the registered oversized pull-quote style, the style guide's "Large Testimonial").
- The "quotes" are actually mission/vision/guarantee statements — rule 23: labeled statements get a real heading + paragraph, not quote markup.
- Proof is logos or numbers, not words — use `logo-strip` or `stats`.

**Pairs with:** core/quote `is-style-quote` (its single-voice sibling directly above it on the style guide), image-tiles, logo-strip, cta-large.

**Core alternative:** core/quote (is-style-quote) for one testimonial; two or three core quotes stacked is acceptable on a text-light page, but loses the card grid and the reusable CPT library.
