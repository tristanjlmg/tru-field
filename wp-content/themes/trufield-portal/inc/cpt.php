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
		'show_in_rest'       => true,
	];

	register_post_type( 'plant_field', $args );
}

add_action( 'rest_api_init', 'trufield_register_plant_field_rest_fields' );
function trufield_register_plant_field_rest_fields(): void {
	register_rest_field(
		'plant_field',
		'portal_meta',
		[
			'get_callback' => 'trufield_get_plant_field_rest_meta',
			'schema'       => [
				'description'          => __( 'Non-protected custom fields for this plant field.', 'trufield-portal' ),
				'type'                 => 'object',
				'context'              => [ 'view', 'edit' ],
				'readonly'             => true,
				'additionalProperties' => true,
			],
		]
	);
}

function trufield_get_plant_field_rest_meta( array $post_arr ): array {
	$post_id = isset( $post_arr['id'] ) ? (int) $post_arr['id'] : 0;
	if ( $post_id <= 0 || ! current_user_can( 'read_plant_field', $post_id ) ) {
		return [];
	}

	$meta    = get_post_meta( $post_id );
	$payload = [];

	foreach ( $meta as $meta_key => $values ) {
		if ( is_protected_meta( (string) $meta_key, 'post' ) ) {
			continue;
		}

		$normalized_values = array_map( 'maybe_unserialize', (array) $values );
		$payload[ $meta_key ] = 1 === count( $normalized_values )
			? $normalized_values[0]
			: $normalized_values;
	}

	return $payload;
}

add_filter( 'rest_request_before_callbacks', 'trufield_restrict_plant_field_rest_requests', 10, 3 );
function trufield_restrict_plant_field_rest_requests( $response, array $handler, WP_REST_Request $request ) {
	unset( $handler );

	if ( ! trufield_is_plant_field_rest_request( $request ) ) {
		return $response;
	}

	if ( ! is_user_logged_in() ) {
		return new WP_Error(
			'rest_forbidden',
			__( 'Authentication is required for the plant field API.', 'trufield-portal' ),
			[ 'status' => rest_authorization_required_code() ]
		);
	}

	$post_id = trufield_get_plant_field_rest_request_post_id( $request );
	if ( $post_id > 0 && in_array( $request->get_method(), [ 'GET', 'HEAD' ], true ) && ! current_user_can( 'read_plant_field', $post_id ) ) {
		return new WP_Error(
			'rest_forbidden',
			__( 'You do not have permission to view this plant field.', 'trufield-portal' ),
			[ 'status' => rest_authorization_required_code() ]
		);
	}

	return $response;
}

function trufield_is_plant_field_rest_request( WP_REST_Request $request ): bool {
	return 0 === strpos( $request->get_route(), '/wp/v2/plant_field' );
}

function trufield_get_plant_field_rest_request_post_id( WP_REST_Request $request ): int {
	foreach ( [ 'id', 'parent' ] as $param_name ) {
		$param_value = $request->get_param( $param_name );
		if ( is_numeric( $param_value ) ) {
			return absint( (string) $param_value );
		}
	}

	return 0;
}

add_filter( 'rest_plant_field_query', 'trufield_limit_plant_field_rest_query', 10, 2 );
function trufield_limit_plant_field_rest_query( array $args, WP_REST_Request $request ): array {
	unset( $request );

	$user = wp_get_current_user();
	if ( ! $user->exists() || array_intersect( [ 'administrator', 'leadership' ], (array) $user->roles ) ) {
		return $args;
	}

	$visible_args                   = $args;
	$visible_args['posts_per_page'] = -1;
	$visible_args['paged']          = 1;
	unset( $visible_args['offset'] );

	$visible_ids = array_map(
		'absint',
		wp_list_pluck( trufield_get_visible_fields( $visible_args ), 'ID' )
	);

	$args['post__in'] = ! empty( $visible_ids ) ? $visible_ids : [ 0 ];

	return $args;
}

function trufield_generate_trial_uuid(): string {
	return wp_generate_uuid4();
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

	$product_tested = sanitize_text_field( wp_unslash( $_POST['phase_1_product_being_tested'] ?? '' ) );
	$rsm_bam = absint( wp_unslash( $_POST['rsm_bam'] ?? 0 ) );
	$fsa     = absint( wp_unslash( $_POST['fsa'] ?? 0 ) );
	$product_choices = trufield_get_product_tested_choices();
	$is_admin_user   = trufield_user_is_admin( $user_id );
	$title           = trufield_generate_trial_uuid();

	if ( '' === $product_tested || ! isset( $product_choices[ $product_tested ] ) ) {
		wp_safe_redirect( add_query_arg( 'tf_error', rawurlencode( __( 'Select a valid product tested before creating the trial.', 'trufield-portal' ) ), trufield_dashboard_url() ) );
		exit;
	}

	if ( ! $is_admin_user ) {
		$rsm_bam = $user_id;
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
	update_post_meta( $post_id, 'trial_uuid', $title );
	update_post_meta( $post_id, 'phase_1_product_being_tested', $product_tested );

	wp_safe_redirect( add_query_arg( 'phase_1_step', 1, get_permalink( $post_id ) ) );
	exit;
}
