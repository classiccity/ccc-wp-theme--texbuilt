<?php
/**
 * ACF field group for the Document Downloads block.
 *
 * @package ClassicCityCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

acf_add_local_field_group(
	array(
		'key'                   => 'group_block_document_downloads',
		'title'                 => __( 'Document Downloads', 'classic-city-core' ),
		'fields'                => array(
			array(
				'key'          => 'field_document_downloads_items',
				'label'        => __( 'Documents', 'classic-city-core' ),
				'name'         => 'documents',
				'type'         => 'repeater',
				'min'          => 1,
				'max'          => 12,
				'layout'       => 'block',
				'button_label' => __( 'Add Document', 'classic-city-core' ),
				'sub_fields'   => array(
					array(
						'key'           => 'field_document_downloads_type',
						'label'         => __( 'File Type', 'classic-city-core' ),
						'name'          => 'file_type',
						'type'          => 'select',
						'choices'       => array(
							'PDF'  => 'PDF',
							'DOC'  => 'DOC',
							'XLS'  => 'XLS',
							'FILE' => __( 'Generic File', 'classic-city-core' ),
						),
						'default_value' => 'PDF',
						'wrapper'       => array( 'width' => '20' ),
					),
					array(
						'key'      => 'field_document_downloads_title',
						'label'    => __( 'Title', 'classic-city-core' ),
						'name'     => 'title',
						'type'     => 'text',
						'required' => 1,
						'wrapper'  => array( 'width' => '40' ),
					),
					array(
						'key'           => 'field_document_downloads_file',
						'label'         => __( 'File', 'classic-city-core' ),
						'name'          => 'file',
						'type'          => 'file',
						'return_format' => 'array',
						'required'      => 1,
						'wrapper'       => array( 'width' => '40' ),
					),
					array(
						'key'      => 'field_document_downloads_body',
						'label'    => __( 'Description', 'classic-city-core' ),
						'name'     => 'body',
						'type'     => 'textarea',
						'rows'     => 2,
					),
				),
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'block',
					'operator' => '==',
					'value'    => 'classic-city-core/document-downloads',
				),
			),
		),
		'position'              => 'normal',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'active'                => true,
		'show_in_rest'          => 1,
	)
);
