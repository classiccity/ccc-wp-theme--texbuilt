<?php
/**
 * ACF field group for the Testimonial CPT.
 *
 * Registers the three fields that back the Testimonial Cards block (Phase 3):
 *   - Company Name  (text)
 *   - Job Title     (text)
 *   - Quote         (textarea)
 *
 * The person's name comes from the post title; the headshot comes from the
 * featured image. Those are registered on the CPT itself (see cpt-testimonial.php).
 *
 * Fields are set to show_in_rest => 1 so the testimonial-cards block can
 * fetch them via the REST API when rendering on the frontend.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/include_fields', 'ccc_register_testimonial_fields' );
function ccc_register_testimonial_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_testimonial_fields',
			'title'                 => __( 'Testimonial Details', 'classic-city-core' ),
			'fields'                => array(
				array(
					'key'           => 'field_testimonial_company',
					'label'         => __( 'Company Name', 'classic-city-core' ),
					'name'          => 'company_name',
					'type'          => 'text',
					'instructions'  => __( 'The organization this person works for. Shown under the quote alongside their job title.', 'classic-city-core' ),
					'required'      => 1,
					'show_in_rest'  => 1,
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_testimonial_job_title',
					'label'         => __( 'Job Title', 'classic-city-core' ),
					'name'          => 'job_title',
					'type'          => 'text',
					'instructions'  => __( 'The person\'s role at the company.', 'classic-city-core' ),
					'required'      => 1,
					'show_in_rest'  => 1,
					'wrapper'       => array( 'width' => '50' ),
				),
				array(
					'key'           => 'field_testimonial_quote',
					'label'         => __( 'Quote', 'classic-city-core' ),
					'name'          => 'quote',
					'type'          => 'textarea',
					'instructions'  => __( 'The testimonial text itself. Keep punctuation clean — no surrounding quote marks; the block adds a styled quote glyph automatically.', 'classic-city-core' ),
					'required'      => 1,
					'rows'          => 5,
					'new_lines'     => 'br',
					'show_in_rest'  => 1,
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'testimonial',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
			'show_in_rest'          => 1,
		)
	);
}
