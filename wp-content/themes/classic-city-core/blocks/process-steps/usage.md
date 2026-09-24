# Process Steps — usage

**Purpose:** Shows "how it works" as a numbered sequence — step cards with auto-generated number badges that promise the visitor a clear, finite path.

**Content shape:** Steps repeater, min 2 / max 10. Per step: heading (required, 2–5 words) + body (required, 1–2 short sentences). Numbers come from item order — never write "Step 1" into the heading. Block-level: desktop columns 2–6 (default 5 — match the step count or a divisor, rule 19), number-circle palette color (default primary), card background via native picker (solid or gradient; one choice paints every card). No images, icons, or links.

**Use when:**
- Source lists sequential phases of a process — onboarding, build phases, "what happens next" (order matters; that's the tell vs. a feature list).
- 3–6 steps each explainable in a sentence or two.
- The section answers "what's it like to work with you?" — classic services-page material.

**Avoid when:**
- Items are unordered features/benefits — use `feature-grid` or `icon-feature-row` (rule 20; numbering unordered items implies false sequence).
- Each step needs an image, link, or long copy — use `image-columns` (cards with media/CTA) or stacked `split-50-50` sections for one-section-per-step depth.
- Steps are questions/answers — that's a FAQ; use the Yoast FAQ block (rule 24).

**Pairs with:** icon-feature-row above, link-pods or cta-thin below ("here's the process — start it"); heading intro belongs inside the same section (rule 9).

**Core alternative:** A core numbered list for a compact inline process mention inside prose; the block earns its keep only when the process IS the section.
