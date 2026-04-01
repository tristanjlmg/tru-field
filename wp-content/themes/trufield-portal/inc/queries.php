<?php
/**
 * TruField Portal — Query Helpers
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

function trufield_get_assigned_fields( int $rep_user_id, array $extra_args = [] ): array {
$defaults = [
'post_type'      => 'plant_field',
'posts_per_page' => -1,
'post_status'    => 'publish',
'meta_query'     => [
[
'key'     => 'assigned_sales_rep',
'value'   => $rep_user_id,
'compare' => '=',
'type'    => 'NUMERIC',
],
],
'orderby'        => 'title',
'order'          => 'ASC',
];

$query = new WP_Query( wp_parse_args( $extra_args, $defaults ) );
return $query->posts;
}

function trufield_get_all_fields( array $extra_args = [] ): array {
$defaults = [
'post_type'      => 'plant_field',
'posts_per_page' => -1,
'post_status'    => 'publish',
'orderby'        => 'title',
'order'          => 'ASC',
];

$query = new WP_Query( wp_parse_args( $extra_args, $defaults ) );
return $query->posts;
}

function trufield_get_visible_fields(): array {
$user = wp_get_current_user();
if ( ! $user->exists() ) {
return [];
}

if ( in_array( 'administrator', (array) $user->roles, true ) || in_array( 'leadership', (array) $user->roles, true ) ) {
return trufield_get_all_fields();
}

return trufield_get_assigned_fields( $user->ID );
}

function trufield_search_grower_names( string $search = '', int $limit = 20 ): array {
unset( $search, $limit );
return [];
}

add_action( 'wp_ajax_trufield_grower_search', 'trufield_ajax_grower_search' );
add_action( 'wp_ajax_nopriv_trufield_grower_search', 'trufield_ajax_grower_search' );
add_action( 'wp_ajax_trufield_geocode_address', 'trufield_ajax_geocode_address' );
add_action( 'wp_ajax_nopriv_trufield_geocode_address', 'trufield_ajax_geocode_address' );

function trufield_ajax_grower_search(): void {
check_ajax_referer( 'trufield_grower_search', 'nonce' );

$search  = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
$results = trufield_search_grower_names( $search, 15 );

wp_send_json_success( $results );
}

function trufield_ajax_geocode_address(): void {
	check_ajax_referer( 'trufield_geocode_address', 'nonce' );

	$address = sanitize_text_field( wp_unslash( $_GET['address'] ?? '' ) );
	if ( '' === $address ) {
		wp_send_json_error( [ 'message' => __( 'Address is required.', 'trufield-portal' ) ], 400 );
	}

	$api_key = trufield_get_google_maps_api_key();
	if ( '' === $api_key ) {
		wp_send_json_error( [ 'message' => __( 'Google Maps is not configured.', 'trufield-portal' ) ], 400 );
	}

	$result = trufield_lookup_address_coordinates( $address, $api_key );

	if ( ! $result ) {
		wp_send_json_error( [ 'message' => __( 'Address could not be verified.', 'trufield-portal' ) ], 422 );
	}

	wp_send_json_success(
		[
			'address' => (string) ( $result['address'] ?? $address ),
			'lat'     => isset( $result['lat'] ) ? (float) $result['lat'] : null,
			'lng'     => isset( $result['lng'] ) ? (float) $result['lng'] : null,
		]
	);
}

function trufield_lookup_address_coordinates( string $address, string $api_key ): ?array {
	$geocode_response = wp_remote_get(
		add_query_arg(
			[
				'address' => $address,
				'key'     => $api_key,
			],
			'https://maps.googleapis.com/maps/api/geocode/json'
		),
		[
			'timeout' => 10,
		]
	);

	if ( ! is_wp_error( $geocode_response ) ) {
		$geocode_body = json_decode( wp_remote_retrieve_body( $geocode_response ), true );
		if ( is_array( $geocode_body ) && ( $geocode_body['status'] ?? '' ) === 'OK' && ! empty( $geocode_body['results'][0]['geometry']['location'] ) ) {
			$result   = $geocode_body['results'][0];
			$location = $result['geometry']['location'];

			return [
				'address' => (string) ( $result['formatted_address'] ?? $address ),
				'lat'     => isset( $location['lat'] ) ? (float) $location['lat'] : null,
				'lng'     => isset( $location['lng'] ) ? (float) $location['lng'] : null,
			];
		}
	}

	$places_response = wp_remote_get(
		add_query_arg(
			[
				'input'     => $address,
				'inputtype' => 'textquery',
				'fields'    => 'formatted_address,geometry',
				'key'       => $api_key,
			],
			'https://maps.googleapis.com/maps/api/place/findplacefromtext/json'
		),
		[
			'timeout' => 10,
		]
	);

	if ( is_wp_error( $places_response ) ) {
		return null;
	}

	$places_body = json_decode( wp_remote_retrieve_body( $places_response ), true );
	if ( ! is_array( $places_body ) || ( $places_body['status'] ?? '' ) !== 'OK' || empty( $places_body['candidates'][0]['geometry']['location'] ) ) {
		return null;
	}

	$candidate = $places_body['candidates'][0];
	$location  = $candidate['geometry']['location'];

	return [
		'address' => (string) ( $candidate['formatted_address'] ?? $address ),
		'lat'     => isset( $location['lat'] ) ? (float) $location['lat'] : null,
		'lng'     => isset( $location['lng'] ) ? (float) $location['lng'] : null,
	];
}
