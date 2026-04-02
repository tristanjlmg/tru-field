<?php
/**
 * TruField Portal — Roles & Access Control
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

add_action( 'after_switch_theme', 'trufield_setup_roles' );
add_action( 'init', 'trufield_setup_roles' );
function trufield_setup_roles(): void {
trufield_maybe_add_role( 'sales_rep', __( 'Sales Rep', 'trufield-portal' ), trufield_sales_rep_caps() );
trufield_maybe_add_role( 'leadership', __( 'Leadership', 'trufield-portal' ), trufield_leadership_caps() );

$admin = get_role( 'administrator' );
if ( $admin ) {
foreach ( trufield_admin_plant_field_caps() as $cap ) {
$admin->add_cap( $cap );
}
}
}

function trufield_maybe_add_role( string $role, string $display_name, array $caps ): void {
$existing = get_role( $role );
if ( $existing ) {
foreach ( $caps as $cap => $grant ) {
if ( $grant ) {
$existing->add_cap( $cap );
} else {
$existing->remove_cap( $cap );
}
}
return;
}

add_role( $role, $display_name, $caps );
}

function trufield_sales_rep_caps(): array {
return [
'read'                          => true,
'read_plant_field'              => true,
'read_private_plant_fields'     => false,
'edit_plant_field'              => true,
'edit_plant_fields'             => true,
'edit_others_plant_fields'      => false,
'edit_published_plant_fields'   => false,
'publish_plant_fields'          => false,
'delete_plant_fields'           => false,
'delete_plant_field'            => false,
'trufield_save_phase'           => true,
'trufield_verify_phase'         => false,
];
}

function trufield_leadership_caps(): array {
return [
'read'                      => true,
'read_plant_field'          => true,
'read_private_plant_fields' => true,
'edit_plant_field'          => false,
'edit_plant_fields'         => false,
'trufield_save_phase'       => false,
'trufield_verify_phase'     => false,
];
}

function trufield_admin_plant_field_caps(): array {
return [
'read_plant_field',
'read_private_plant_fields',
'edit_plant_field',
'edit_plant_fields',
'edit_others_plant_fields',
'edit_published_plant_fields',
'publish_plant_fields',
'delete_plant_fields',
'delete_plant_field',
'delete_others_plant_fields',
'delete_published_plant_fields',
'trufield_save_phase',
'trufield_reopen_phase',
'trufield_verify_phase',
		'trufield_import_fields',
'trufield_export_csv',
];
}

add_filter( 'map_meta_cap', 'trufield_map_plant_field_caps', 10, 4 );
function trufield_map_plant_field_caps( array $caps, string $cap, int $user_id, array $args ): array {
if ( ! in_array( $cap, [ 'edit_plant_field', 'delete_plant_field', 'read_plant_field' ], true ) ) {
return $caps;
}

$post_id = $args[0] ?? 0;
$post    = $post_id ? get_post( $post_id ) : null;
if ( ! $post || $post->post_type !== 'plant_field' ) {
return $caps;
}

if ( $cap === 'read_plant_field' ) {
if ( user_can( $user_id, 'administrator' ) || user_can( $user_id, 'leadership' ) ) {
return [ 'read' ];
}

$assigned = (int) get_post_meta( $post->ID, 'assigned_sales_rep', true );
return ( $assigned === $user_id ) ? [ 'read' ] : [ 'do_not_allow' ];
}

if ( $cap === 'edit_plant_field' ) {
if ( user_can( $user_id, 'administrator' ) ) {
return [ 'edit_plant_fields' ];
}

$assigned = (int) get_post_meta( $post->ID, 'assigned_sales_rep', true );
return ( $assigned === $user_id ) ? [ 'edit_plant_fields' ] : [ 'do_not_allow' ];
}

if ( $cap === 'delete_plant_field' ) {
return user_can( $user_id, 'administrator' ) ? [ 'delete_plant_fields' ] : [ 'do_not_allow' ];
}

return $caps;
}

add_action( 'admin_init', 'trufield_block_frontend_roles_from_admin' );
function trufield_block_frontend_roles_from_admin(): void {
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
return;
}

$user = wp_get_current_user();
if ( ! $user->exists() ) {
return;
}

if ( array_intersect( [ 'sales_rep', 'leadership' ], (array) $user->roles ) ) {
wp_safe_redirect( home_url( '/dashboard/' ) );
exit;
}
}

add_action( 'after_setup_theme', 'trufield_hide_admin_bar_for_portal_roles' );
function trufield_hide_admin_bar_for_portal_roles(): void {
if ( ! is_user_logged_in() ) {
return;
}

$user = wp_get_current_user();
if ( array_intersect( [ 'sales_rep', 'leadership' ], (array) $user->roles ) ) {
add_filter( 'show_admin_bar', '__return_false' );
}
}
