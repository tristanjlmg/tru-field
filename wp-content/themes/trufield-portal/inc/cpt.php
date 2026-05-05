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
add_action( 'admin_post_trufield_create_phase_3_test_record', 'trufield_handle_create_phase_3_test_record' );
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
	$rsm_bam = absint( wp_unslash( $_POST['rsm_bam'] ?? 0 ) );
	$fsa     = absint( wp_unslash( $_POST['fsa'] ?? 0 ) );
	if ( '' === $title ) {
		wp_safe_redirect( add_query_arg( 'tf_error', rawurlencode( __( 'Trial name is required.', 'trufield-portal' ) ), trufield_dashboard_url() ) );
		exit;
	}

	if ( ! trufield_is_allowed_rsm_bam_user_id( $rsm_bam ) ) {
		wp_safe_redirect( add_query_arg( 'tf_error', rawurlencode( __( 'Select a valid RSM / BAM before creating the trial.', 'trufield-portal' ) ), trufield_dashboard_url() ) );
		exit;
	}

	$fsa_options = trufield_get_assignment_user_options( 'fsa' );
	if ( $fsa <= 0 || ! isset( $fsa_options[ $fsa ] ) ) {
		wp_safe_redirect( add_query_arg( 'tf_error', rawurlencode( __( 'Select a valid FSA before creating the trial.', 'trufield-portal' ) ), trufield_dashboard_url() ) );
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
	update_post_meta( $post_id, 'rsm_bam', $rsm_bam );
	update_post_meta( $post_id, 'assigned_sales_rep', $rsm_bam );
	update_post_meta( $post_id, 'fsa', $fsa );

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

function trufield_handle_create_phase_3_test_record(): void {
	$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) );
	if ( ! wp_verify_nonce( $nonce, 'trufield_create_phase_3_test_record' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'trufield-portal' ), 403 );
	}

	$user_id = get_current_user_id();
	if ( ! $user_id || ! user_can( $user_id, 'publish_plant_fields' ) ) {
		wp_die( esc_html__( 'You do not have permission to create a Phase 3 test record.', 'trufield-portal' ), 403 );
	}

	$title = sprintf(
		/* translators: %s = timestamp. */
		__( 'Phase 3 Test Record %s', 'trufield-portal' ),
		wp_date( 'Y-m-d H:i:s' )
	);

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

	$meta = [
		'record_status'                         => 'active',
		'validation_status'                     => 'pending',
		'current_phase'                         => 3,
		'phase_1_status'                        => 'completed',
		'phase_2_status'                        => 'completed',
		'phase_3_status'                        => 'in_progress',
		'phase_1_verified'                      => 1,
		'phase_2_verified'                      => 1,
		'phase_1_verified_at'                   => current_time( 'mysql' ),
		'phase_2_verified_at'                   => current_time( 'mysql' ),
		'phase_1_completed_at'                  => current_time( 'mysql' ),
		'phase_2_completed_at'                  => current_time( 'mysql' ),
		'retailer_name'                         => 'Phase 3 Test Retailer',
		'retailer_key_contact'                  => 'Phase 3 Tester',
		'retailer_contact_phone'                => '555-555-1212',
		'retailer_address'                      => '123 Test Lane',
		'retailer_city'                         => 'Nashville',
		'phase_1_state_region'                  => 'TN',
		'field_trial_contact'                   => 'Phase 3 Tester',
		'contact_phone'                         => '555-555-3434',
		'field_trial_contact_email'             => 'phase3tester@example.com',
		'phase_1_treated_size_acres'            => 10,
		'phase_1_application_rate'              => '32 oz/ac',
		'phase_1_trial_type'                    => 'full_field',
		'phase_1_protocol_version'              => 'corn_residue_spring',
		'phase_1_application_timing'            => 'spring_2026',
		'phase_1_application_date'              => current_time( 'Y-m-d' ),
		'phase_1_retailer_training_discussion_date' => current_time( 'Y-m-d' ),
		'field_location_address'                => '123 Test Lane, Nashville, TN',
		'field_location_lat'                    => '36.1627',
		'field_location_lng'                    => '-86.7816',
	];

	$rsm_options = trufield_get_rsm_bam_user_options();
	$fsa_options = trufield_get_assignment_user_options( 'fsa' );

	if ( ! empty( $rsm_options ) ) {
		$rsm_id = (int) array_key_first( $rsm_options );
		$meta['rsm_bam'] = $rsm_id;
		$meta['assigned_sales_rep'] = $rsm_id;
	}

	if ( ! empty( $fsa_options ) ) {
		$meta['fsa'] = (int) array_key_first( $fsa_options );
	}

	foreach ( $meta as $meta_key => $meta_value ) {
		update_post_meta( $post_id, $meta_key, $meta_value );
	}

	wp_safe_redirect(
		add_query_arg(
			[
				'tf_success' => 'phase_3_test_record_created',
				'tf_post_id' => $post_id,
			],
			trufield_dashboard_url()
		)
	);
			exit;
}
