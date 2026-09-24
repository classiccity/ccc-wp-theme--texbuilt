# Writing Your Website Content — The 8 Section Types

A plain-language guide for writing content for your new website.

**You don't need to design anything.** Your site is built from a small set of
reusable **section types**. Your only job is to write your words into the
simple templates below — we handle all the layout, colors, spacing, and image
sizing on our end.

## How to use this

1. Skim the 8 section types below and picture your page as a stack of them
   (e.g. *Page Opener → Image Columns → Numbered Steps → Call-to-Action*).
2. For each section you want, **copy its template** into your own Google Doc.
3. Fill in the blanks. Where a template says *"copy this 2–4 times,"* duplicate
   that little block once per card/step/item you need.
4. Attach or describe images inline — you don't have to place or crop them.
5. Send it back. We assemble it.

Keep it loose. If something doesn't fit neatly into one of these, just write it
out and note what you're going for — we'll find the right home for it.

---

## 1. Page Opener

The first thing people see at the top of a page: one strong headline, a
sentence or two of support, and usually a button. Often paired with a photo.

> Use it to open any page.

```
SECTION: Page Opener

Headline (one short line):
Supporting sentence or two:
Button text (optional):
Button link (optional):
Second button text (optional):
Second button link (optional):
Image: (attach or describe the main image)
```

---

## 2. Call-to-Action Band

A short, bold moment — usually a colored band — with a headline, a sentence,
and a button that asks the visitor to do one thing.

> Use it to drive a single action (book, contact, sign up), or to make one
> strong standalone statement.

```
SECTION: Call-to-Action Band

Headline (one line):
Supporting sentence (optional):
Button text:
Button link:
```

---

## 3. Image Columns

A row of cards, side by side. Each card has an image on top, a headline, a
short paragraph (about three sentences), and an optional button.

> Use it to present a few parallel things — services, audiences, features —
> each with its own picture. Best with 2–4 cards.

```
SECTION: Image Columns

Section headline (optional):
Intro paragraph (optional):

--- CARD (copy this whole card 2–4 times) ---
Image: (attach or describe)
Headline:
Paragraph (about 3 sentences):
Button text (optional):
Button link (optional):
--- end card ---
```

---

## 4. Icon Highlights

A tidy row of short items — each with a small icon, a short headline, and a
sentence or two. No photos.

> Use it to list benefits, features, or key points quickly. Best with 3–5
> items.

```
SECTION: Icon Highlights

Section headline (optional):

--- ITEM (copy this 3–5 times) ---
Icon idea: (what it should represent — e.g. "shield," "clock," "heart")
Headline (a few words):
One or two sentences:
--- end item ---
```

---

## 5. Image + Text, Side by Side

A photo on one side, words on the other: a headline, a paragraph, an optional
short list, and an optional button. Stack several of these and the image
automatically alternates left/right down the page.

> Use it to explain things in a bit more depth, one idea at a time.

```
SECTION: Image + Text
(Copy this whole block once per idea. We alternate the image side automatically.)

Image: (attach or describe)
Headline:
Paragraph:
Optional short list (one item per line):
  -
  -
  -
Button text (optional):
Button link (optional):
```

---

## 6. Numbered Steps

A simple numbered sequence. Each step has a short title and a sentence or two.
The numbers are added for you.

> Use it to show how something works, or a process, in order. Best with 2–6
> steps.

```
SECTION: Numbered Steps

Section headline (optional):

--- STEP (copy this 2–6 times — numbering is automatic) ---
Step title:
One or two sentences:
--- end step ---
```

---

## 7. By the Numbers

A row of big numbers, each with a short label underneath.

> Use it to show impressive figures — results, reach, milestones. Best with
> 2–6 numbers.

```
SECTION: By the Numbers

Section headline (optional):

--- STAT (copy this 2–6 times) ---
Big number: (e.g. "12,000+", "98%", "3x")
Label:
--- end stat ---
```

---

## 8. Quotes / Testimonials

Real quotes from customers or partners, each with a name and their title or
company.

> Use it to add trust and social proof. Best with 1–4 quotes.

```
SECTION: Testimonials

--- QUOTE (copy this 1–4 times) ---
Quote:
Name:
Title & company:
--- end quote ---
```

---

## A few writing tips

- **Headlines**: short — a handful of words beats a full sentence.
- **Paragraphs**: 2–4 sentences. Say the important thing first.
- **Buttons**: 2–4 words, action-first — *"Find your trial," "Talk with us,"
  "See how it works."*
- **Repeat freely**: copy any *CARD / ITEM / STEP / STAT / QUOTE* block as many
  times as the note allows.
- **Images**: don't worry about size, crop, or placement — attach the file or
  describe what you want, and we'll handle it.
- **You're writing, not designing.** Pour your words into these shapes and
  we'll build the polished version.

---

<!-- ==================================================================
     FOR THE CLASSIC CITY TEAM — not part of the client-facing guide.
     Which block(s) each section type is assembled from (registry:
     docs/BLOCKS.md). A section may map to several blocks; pick by fit.
     ================================================================== -->

## For the Classic City team — section → block map

*(Internal — strip this section before handing the guide to a client.)*

A section type does NOT map to one block. "Image + Text, Side by Side" alone
accounts for ~1 in 4 sections across a typical delivery, and picking a different
block for each occurrence is how a site ends up looking assembled rather than
designed. **Decide with the discriminator, not by taste** — and record the call
in the client's `AUTHORING_PATTERNS.md` so the next page build matches.

The discriminator is almost always a FIELD THE CLIENT EITHER FILLED IN OR LEFT
BLANK. That is the point of the intake format: the shape of what comes back
tells you which block it is.

| Client section | Discriminator — what to look at | Block |
|---|---|---|
| **1. Page Opener** | full-bleed image or video | `hero-full-image` |
| | image sits *beside* the copy | `image-hero-50-50` |
| | no image supplied | `hero-gradient` |
| | three parallel sub-items | `hero-3-up` |
| **2. CTA Band** | **`Button text: N/A`** — a statement, not an ask | **`text-callout`** |
| | headline + sentence + button, wants a band | `cta-large` |
| | one line + button, compact | `cta-thin` |
| **3. Image Columns** | cut-out people, no rectangular frame | `overlay-cards` *(child)* |
| | image + copy + a button **per card** | `image-columns` |
| | whole card is the link, no button | `image-link-cards` / `link-pods` |
| **4. Icon Highlights** | 1–3 sentences per item | `feature-grid` |
| | one line per item, tight | `icon-feature-row` |
| **5. Image + Text** | **`Optional short list` filled in** | **`feature-detail`** |
| | no list — image, headline, paragraph, button | `split-50-50` |
| | several variants the reader toggles between | `product-feature-toggles` |
| **6. Numbered Steps** | sequential, order matters | `process-steps` |
| **7. By the Numbers** | bare number + label | `stats` (+ `is-style-pods` / `is-style-strip`) |
| | each figure needs a sentence | `card-detail-rows` |
| **8. Quotes** | business testimonial (company, job title) | `testimonial-cards` |
| | patient/personal voice (location, condition) | `voice-card` *(child)* |

### Evidence sections — beyond the 8

Clients who send proof points rather than prose need blocks the intake guide
does not describe. Recognise them by what the copy is doing:

| What the copy is | Block |
|---|---|
| values that partition a whole (by country, by source) | `chart-horizontal-bars` |
| a claim plus its receipts — label → value rows | `card-detail-rows` |
| one figure before and after a change | `stat-comparison` |
| kicker + 2–4 figures + a quote + a CTA, as one band | `proof-band` |
| us against a peer average, row by row | `comparison-table` |

### Two traps

**Icon Highlights needs REAL icons.** Clients write an "icon idea" in prose
("a person and family exploring together"). Content rule 12: if it does not
resolve to an actual FontAwesome glyph, do not fake it — use `image-card-grid`
or plain core columns instead. An invented icon is worse than none.

**Grid columns must fit the item count** (rule 19). Four cards in a 3-column
grid leaves a ragged orphan. Set the column count from what the client sent,
rather than sending the count back to be padded.

**Mostly-visual blocks** (little to no client copy — we place them directly, no
template needed): `logo-strip`, `highlighted-image-gallery`, `portfolio-gallery`,
`image-wall`, `image-tiles`, `image-link-cards`, `document-downloads`,
`background-texture`, `swatch-explorer`, `stackable`, `post-card`.
