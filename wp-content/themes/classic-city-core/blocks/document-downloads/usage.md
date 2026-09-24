# Document Downloads — usage

**Purpose:** Hands visitors a list of downloadable files — specs, warranties, brochures, forms — each as a full-card download link.

**Content shape:** `documents` repeater, 1–12. Per item: `file_type` select (PDF | DOC | XLS | FILE — drives a fixed FontAwesome glyph, no icon field to fill), required `title`, required `file` upload (items without a file URL are skipped at render), optional 1–2 line `body`. Every card renders a "Download →" affordance and links directly to the file.

**Use when:**
- Source page lists real downloadable assets (spec sheets, install guides, warranty PDFs) that you have files for or the client will upload.
- A resources/documentation section needs scannable file cards rather than inline text links.

**Avoid when:**
- The "documents" are actually pages/URLs, not files — use `link-pods` (whole-pod links to destinations).
- Files don't exist yet — the block silently drops fileless rows (rule 11 spirit: no dead download links); stub the section as a core heading + paragraph note until files arrive.
- There's only one file — a core paragraph with a styled core button linking the file is lighter.

**Pairs with:** icon-feature-row, process-steps, feature-detail; typically deep on resource/product pages, introduced by a core heading (rule 9 — heading belongs with the block).

**Core alternative:** For 1–2 files, a core heading + core buttons linking the uploads. Use this block once there are 3+ files worth a uniform card list.
