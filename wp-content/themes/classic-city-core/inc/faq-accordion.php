<?php
/**
 * Yoast FAQ block → accordion.
 *
 * Content rule 24 makes `yoast/faq-block` the house FAQ pattern. Yoast
 * renders it as a flat run of <strong> questions and <p> answers. This
 * file rewrites that render into a real disclosure widget.
 *
 * WHY THIS IS A SERVER-SIDE REWRITE AND NOT A PILE OF JAVASCRIPT.
 *
 * The FAQ CSS in assets/blocks.css used to carry a note explaining why
 * the block was deliberately NOT an accordion: an accordion whose
 * trigger is a bare <strong> is a control keyboard and screen-reader
 * users cannot operate, and getting there by having JS re-parent Yoast's
 * DOM on load is worse still — the page renders one way and then jumps.
 * That note also said that if a collapsing FAQ was ever wanted, it had
 * to be a deliberate decision taken AT THE BLOCK LEVEL. This file is
 * that decision, taken the way the note demanded.
 *
 * So the trigger is a real <summary> inside a real <details>, present in
 * the HTML the browser receives:
 *
 *   · Keyboard, screen readers and the accessibility tree get a genuine
 *     disclosure widget with implicit aria-expanded, for free, from the
 *     platform. No ARIA to hand-maintain and get subtly wrong.
 *   · With JavaScript OFF — or broken, or still loading — every answer
 *     is still reachable. <details> toggles natively. The JS in
 *     assets/faq-accordion.js ONLY adds the open/close animation; remove
 *     it and the accordion still works, just instantly.
 *   · Nothing moves after paint.
 *
 * WHAT THIS DOES NOT TOUCH: the schema. Yoast builds its FAQPage
 * structured data from the PARSED BLOCK ATTRIBUTES, not from the
 * rendered HTML, so rewriting the render leaves the JSON-LD graph
 * byte-identical. That is the whole reason rule 24 picked Yoast's block
 * over a hand-rolled one, and it survives this change intact.
 *
 * FAIL-OPEN BY CONSTRUCTION. Every transform is a preg_replace_callback
 * that returns the ORIGINAL section markup unless it matched cleanly,
 * and the root class is only added if at least one section actually
 * converted. If Yoast changes its save output in a future release, the
 * worst case is the old flat list — styled, readable, deep-linkable —
 * not a broken page.
 *
 * OPTING OUT: add_filter( 'ccc_faq_accordion', '__return_false' ) in a
 * child theme keeps the open Q&A list. The list styling in blocks.css
 * is shared by both modes.
 *
 * @package Classic_City_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is the accordion treatment on for this site?
 *
 * Default TRUE — the accordion is the house treatment. A child that
 * wants every answer visible at once (long-form policy FAQs, print-
 * oriented pages) turns it off with the filter.
 *
 * @return bool
 */
function ccc_faq_accordion_enabled() {
	return (bool) apply_filters( 'ccc_faq_accordion', true );
}

/**
 * Rewrite a rendered Yoast FAQ block into <details>/<summary> rows.
 *
 * @param string $content Rendered block HTML.
 * @return string
 */
function ccc_faq_accordion_render( $content ) {
	// The editor renders this block client-side, so this filter should
	// never fire there — but ServerSideRender and other REST consumers
	// can reach it, and they want Yoast's own markup, not ours.
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $content;
	}

	if ( ! ccc_faq_accordion_enabled() ) {
		return $content;
	}

	if ( false === strpos( $content, 'schema-faq-section' ) ) {
		return $content;
	}

	$converted = 0;

	/*
	 * One section. Written against Yoast's SAVE output, verified off a
	 * live render:
	 *
	 *   <div class="schema-faq-section" id="faq-question-…">
	 *     <strong class="schema-faq-question">…</strong>
	 *     <p class="schema-faq-answer">…</p>
	 *   </div>
	 *
	 * `[^>]*` around the id tolerates attribute reordering. The two
	 * inner captures are lazy: a question is phrasing content and an
	 * answer is a single RichText paragraph, so neither can contain the
	 * closing tag being matched. The `id` is REQUIRED to match — it is
	 * the deep-link hook, it is what the front-end-only `[id]` guard in
	 * blocks.css keys on, and a section without one is not something we
	 * recognise, so it is left alone.
	 */
	$pattern = '~<div\s+class="schema-faq-section"[^>]*\sid="([^"]+)"[^>]*>\s*'
		. '<strong\s+class="schema-faq-question">(.*?)</strong>\s*'
		. '<p\s+class="schema-faq-answer">(.*?)</p>\s*'
		. '</div>~s';

	$rewritten = preg_replace_callback(
		$pattern,
		function ( $m ) use ( &$converted ) {
			$converted++;

			// The <strong> is kept, not swapped for a heading or a span:
			// blocks.css targets `strong.schema-faq-question`, and that
			// tag qualifier is load-bearing (it is what keeps the rule
			// off Yoast's editor markup, where the question is a <p>).
			// Inside a <summary> it is still phrasing content, so this
			// stays valid HTML.
			return sprintf(
				'<details class="schema-faq-section" id="%1$s">'
					. '<summary class="ccc-faq-trigger">'
					. '<strong class="schema-faq-question">%2$s</strong>'
					. '</summary>'
					. '<div class="ccc-faq-panel">'
					. '<div class="ccc-faq-panel__inner">'
					. '<p class="schema-faq-answer">%3$s</p>'
					. '</div></div>'
					. '</details>',
				esc_attr( $m[1] ),
				$m[2],
				$m[3]
			);
		},
		$content
	);

	// preg_replace_callback returns null on failure (catastrophic
	// backtracking, bad UTF-8). Keep what we were given.
	if ( null === $rewritten || 0 === $converted ) {
		return $content;
	}

	/*
	 * Mark the list as converted. This is what the JS scopes to and what
	 * the accordion-only CSS hangs off, so it must NOT be added when
	 * nothing converted — a half-matched block would otherwise get
	 * accordion styling over flat markup.
	 */
	$rewritten = preg_replace(
		'~(<div\s+class="schema-faq)(\s|")~',
		'$1 is-ccc-faq-accordion$2',
		$rewritten,
		1
	);

	return $rewritten;
}
add_filter( 'render_block_yoast/faq-block', 'ccc_faq_accordion_render', 10, 1 );
