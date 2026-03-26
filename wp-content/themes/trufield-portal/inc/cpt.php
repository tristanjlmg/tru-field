<?php
/**
 * TruField Portal — CPT Registration
 *
 * Registers the `plant_field` custom post type.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'trufield_register_cpt_plant_field' );
function trufield_register_cpt_plant_field(): void {
	$labels = [
		'name'                  => _x( 'Plant Fields', 'post type general name', 'trufield-portal' ),
		'singular_name'         => _x( 'Plant Field', 'post type singular name', 'trufield-portal' ),
		'menu_name'             => __( 'Plant Fields', 'trufield-portal' ),
		'add_new'               => __( 'Add New', 'trufield-portal' ),
		'add_new_item'          => __( 'Add New Plant Field', 'trufield-portal' ),
		'edit_item'             => __( 'Edit Plant Field', 'trufield-portal' ),
		'new_item'              => __( 'New Plant Field', 'trufield-portal' ),
		'view_item'             => __( 'View Plant Field', 'trufield-portal' ),
		'search_items'          => __( 'Search Plant Fields', 'trufield-portal' ),
		'not_found'             => __( 'No plant fields found.', 'trufield-portal' ),
		'not_found_in_trash'    => __( 'No plant fields found in trash.', 'trufield-portal' ),
		'all_items'             => __( 'All Plant Fields', 'trufield-portal' ),
		'archives'              => __( 'Plant Field Archives', 'trufield-portal' ),
	];

	$args = [
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => [ 'slug' => 'plant-field', 'with_front' => false ],
		'capability_type'    => 'plant_field',
		'map_meta_cap'       => true,
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-location-alt',
		'supports'           => [ 'title', 'revisions', 'custom-fields' ],
		'show_in_rest'       => false, // Portal uses classic templates; REST not needed for v1.
	];

	register_post_type( 'plant_field', $args );
}
