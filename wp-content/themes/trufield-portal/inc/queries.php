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

function trufield_ajax_grower_search(): void {
check_ajax_referer( 'trufield_grower_search', 'nonce' );

$search  = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
$results = trufield_search_grower_names( $search, 15 );

wp_send_json_success( $results );
}
