<?php
/**
 * TruField Portal — Admin Enhancements
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

add_filter( 'manage_plant_field_posts_columns', 'trufield_admin_columns' );
function trufield_admin_columns( array $columns ): array {
unset( $columns['date'] );

$columns['assigned_rep']  = __( 'Assigned Rep', 'trufield-portal' );
$columns['record_status'] = __( 'Status', 'trufield-portal' );
$columns['phase_1_status'] = __( 'Phase 1', 'trufield-portal' );

if ( in_array( 2, TRUFIELD_ACTIVE_PHASES, true ) ) {
$columns['phase_2_status'] = __( 'Phase 2', 'trufield-portal' );
}

if ( in_array( 3, TRUFIELD_ACTIVE_PHASES, true ) ) {
$columns['phase_3_status'] = __( 'Phase 3', 'trufield-portal' );
}

$columns['verifications'] = __( 'Verifications', 'trufield-portal' );
$columns['date']          = __( 'Date', 'trufield-portal' );

return $columns;
}

add_action( 'manage_plant_field_posts_custom_column', 'trufield_admin_column_content', 10, 2 );
function trufield_admin_column_content( string $column, int $post_id ): void {
switch ( $column ) {
case 'assigned_rep':
$rep_id = (int) get_post_meta( $post_id, 'assigned_sales_rep', true );
if ( $rep_id ) {
$user = get_userdata( $rep_id );
echo $user ? esc_html( $user->display_name ) : esc_html__( '(deleted)', 'trufield-portal' );
} else {
echo '<span style="color:#aaa;">—</span>';
}
break;

case 'record_status':
$status = get_post_meta( $post_id, 'record_status', true ) ?: 'active';
$labels = [ 'active' => 'Active', 'archived' => 'Archived', 'on_hold' => 'On Hold' ];
$colors = [ 'active' => '#00a32a', 'archived' => '#aaa', 'on_hold' => '#d63638' ];
printf(
'<span style="color:%s;font-weight:600;">%s</span>',
esc_attr( $colors[ $status ] ?? '#888' ),
esc_html( $labels[ $status ] ?? $status )
);
break;

case 'phase_1_status':
case 'phase_2_status':
case 'phase_3_status':
$phase = (int) substr( $column, 6, 1 );
trufield_admin_phase_badge( $post_id, $phase, trufield_get_phase_status( $post_id, $phase ) );
break;

case 'verifications':
$badges = [];
foreach ( TRUFIELD_ACTIVE_PHASES as $phase ) {
$verified = (bool) get_post_meta( $post_id, "phase_{$phase}_verified", true );
$status   = trufield_get_phase_status( $post_id, $phase );
if ( $verified ) {
$badges[] = '<span style="display:inline-block;margin:0 6px 6px 0;padding:2px 8px;border-radius:999px;background:#eaf5ec;color:#2d7a3c;font-weight:700;">' . esc_html( "P{$phase} ✓" ) . '</span>';
} elseif ( $status === 'completed' ) {
$badges[] = '<span style="display:inline-block;margin:0 6px 6px 0;padding:2px 8px;border-radius:999px;background:#fdf4d9;color:#c5910a;font-weight:700;">' . esc_html( "P{$phase} ⬤" ) . '</span>';
} else {
$badges[] = '<span style="display:inline-block;margin:0 6px 6px 0;padding:2px 8px;border-radius:999px;background:#f5f6fa;color:#999;font-weight:700;">' . esc_html( "P{$phase} ○" ) . '</span>';
}
}
echo implode( '', $badges ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
break;
}
}

function trufield_admin_phase_badge( int $post_id, int $phase, string $status ): void {
$verified    = (bool) get_post_meta( $post_id, "phase_{$phase}_verified", true );
$verify_url  = trufield_admin_phase_badge_verify_url( $post_id, $phase );
$reopen_url  = wp_nonce_url( admin_url( "admin-post.php?action=trufield_reopen_phase&post_id={$post_id}&phase={$phase}" ), "trufield_reopen_phase_{$post_id}_{$phase}" );
$color_map   = [ 'pending' => '#999', 'in_progress' => '#c5910a', 'completed' => ( $verified ? '#2d7a3c' : '#c5910a' ) ];
$label_map   = [ 'pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed' ];
$status_text = $label_map[ $status ] ?? $status;

printf(
'<span style="color:%s;font-weight:600;">%s</span>',
esc_attr( $color_map[ $status ] ?? '#888' ),
esc_html( $status_text )
);

if ( $verified ) {
echo ' <span style="color:#2d7a3c;font-weight:700;">✓</span>';
}

if ( $status === 'completed' ) {
printf(
' <a href="%s" style="font-size:11px;" onclick="return confirm(\'%s\');">%s</a>',
esc_url( $reopen_url ),
esc_js( sprintf( __( 'Reopen phase %d?', 'trufield-portal' ), $phase ) ),
esc_html__( 'Reopen', 'trufield-portal' )
);

if ( ! $verified ) {
printf(
' <a href="%s" style="font-size:11px;">%s</a>',
esc_url( $verify_url ),
esc_html__( 'Verify', 'trufield-portal' )
);
}
}
}

function trufield_admin_phase_badge_verify_url( int $post_id, int $phase ): string {
return wp_nonce_url(
admin_url( "admin-post.php?action=trufield_verify_phase&post_id={$post_id}&phase={$phase}" ),
"trufield_verify_phase_{$post_id}_{$phase}"
);
}

add_filter( 'manage_edit-plant_field_sortable_columns', 'trufield_sortable_columns' );
function trufield_sortable_columns( array $cols ): array {
$cols['assigned_rep']  = 'assigned_sales_rep';
$cols['record_status'] = 'record_status';
return $cols;
}

add_action( 'admin_menu', 'trufield_admin_menu' );
function trufield_admin_menu(): void {
add_submenu_page(
'edit.php?post_type=plant_field',
__( 'Export CSV', 'trufield-portal' ),
__( 'Export CSV', 'trufield-portal' ),
'trufield_export_csv',
'trufield-export',
'trufield_export_page_render'
);
}

function trufield_export_page_render(): void {
if ( ! current_user_can( 'trufield_export_csv' ) ) {
wp_die( esc_html__( 'Access denied.', 'trufield-portal' ) );
}

$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=trufield_export_csv' ), 'trufield_export_csv' );
$total      = wp_count_posts( 'plant_field' )->publish ?? 0;
?>
<div class="wrap">
<h1><?php esc_html_e( 'Export Plant Fields', 'trufield-portal' ); ?></h1>
<p>
<?php
printf(
esc_html__( 'Export all %d published plant field records as a CSV file.', 'trufield-portal' ),
(int) $total
);
?>
</p>
<p>
<a href="<?php echo esc_url( $export_url ); ?>" class="button button-primary"><?php esc_html_e( 'Download CSV', 'trufield-portal' ); ?></a>
</p>
<h2><?php esc_html_e( 'What\'s included', 'trufield-portal' ); ?></h2>
<ul>
<li><?php esc_html_e( 'Assignment, record, and validation metadata', 'trufield-portal' ); ?></li>
<li><?php esc_html_e( 'Field identity and contact information', 'trufield-portal' ); ?></li>
<li><?php esc_html_e( 'Phase 1–3 status, verification, and required data points', 'trufield-portal' ); ?></li>
</ul>
</div>
<?php
}

add_action( 'admin_notices', 'trufield_admin_notices' );
function trufield_admin_notices(): void {
$screen = get_current_screen();
if ( ! $screen || $screen->post_type !== 'plant_field' ) {
return;
}

$reopened = (int) ( $_GET['tf_reopened'] ?? 0 );
$verified = (int) ( $_GET['tf_verified'] ?? 0 );

if ( $reopened ) {
printf(
'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
esc_html( sprintf( __( 'Phase %d has been reopened.', 'trufield-portal' ), $reopened ) )
);
}

if ( $verified ) {
printf(
'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
esc_html( sprintf( __( 'Phase %d verified.', 'trufield-portal' ), $verified ) )
);
}
}
