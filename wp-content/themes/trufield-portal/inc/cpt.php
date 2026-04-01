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

add_action( 'admin_post_trufield_create_plant_field', 'trufield_handle_create_plant_field' );
function trufield_handle_create_plant_field(): void {
	$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) );
	if ( ! wp_verify_nonce( $nonce, 'trufield_create_plant_field' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'trufield-portal' ), 403 );
	}

	$user_id = get_current_user_id();
	if ( ! $user_id || ! user_can( $user_id, 'publish_plant_fields' ) ) {
		wp_die( esc_html__( 'You do not have permission to create a trial.', 'trufield-portal' ), 403 );
	}

	$title = sanitize_text_field( wp_unslash( $_POST['trial_name'] ?? '' ) );
	if ( '' === $title ) {
		wp_safe_redirect( add_query_arg( 'tf_error', rawurlencode( __( 'Trial name is required.', 'trufield-portal' ) ), trufield_dashboard_url() ) );
		exit;
	}

	$post_id = wp_insert_post(
		[
			'post_type'   => 'plant_field',
			'post_status' => 'publish',
			'post_title'  => $title,
			'post_author' => $user_id,
		],
		true
	);

	if ( is_wp_error( $post_id ) ) {
		wp_safe_redirect( add_query_arg( 'tf_error', rawurlencode( $post_id->get_error_message() ), trufield_dashboard_url() ) );
		exit;
	}

	update_post_meta( $post_id, 'record_status', 'active' );
	update_post_meta( $post_id, 'current_phase', 1 );
	update_post_meta( $post_id, 'phase_1_status', 'pending' );

	wp_safe_redirect(
		add_query_arg(
			[
				'tf_success' => 'trial_created',
				'tf_post_id' => $post_id,
			],
			trufield_dashboard_url()
		)
	);
	exit;
}
