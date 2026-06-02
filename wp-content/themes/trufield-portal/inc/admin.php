<?php
/**
 * TruField Portal — Admin Enhancements
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

function trufield_product_tested_option_key(): string {
	return 'trufield_product_tested_options';
}

function trufield_default_product_tested_options(): array {
	return [ 'MOS218' ];
}

function trufield_sanitize_product_tested_options( $raw_value ): array {
	$values = is_array( $raw_value ) ? $raw_value : preg_split( '/\r\n|\r|\n/', (string) $raw_value );
	$values = is_array( $values ) ? $values : [];
	$choices = [];

	foreach ( $values as $value ) {
		$label = trim( sanitize_text_field( (string) $value ) );
		if ( '' === $label ) {
			continue;
		}

		$choices[ $label ] = $label;
	}

	if ( empty( $choices ) ) {
		foreach ( trufield_default_product_tested_options() as $default_choice ) {
			$choices[ $default_choice ] = $default_choice;
		}
	}

	return $choices;
}

function trufield_get_product_tested_choices(): array {
	$stored = get_option( trufield_product_tested_option_key(), [] );

	return trufield_sanitize_product_tested_options( $stored );
}

function trufield_retailer_directory_admin_url( array $args = [] ): string {
	return add_query_arg( $args, admin_url( 'edit.php?post_type=plant_field&page=trufield-retailers' ) );
}

function trufield_retailer_import_admin_url( array $args = [] ): string {
	return add_query_arg( $args, admin_url( 'edit.php?post_type=plant_field&page=trufield-import-retailers' ) );
}

add_action( 'admin_post_trufield_save_product_tested_options', 'trufield_save_product_tested_options' );
function trufield_save_product_tested_options(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Access denied.', 'trufield-portal' ) );
	}

	check_admin_referer( 'trufield_save_product_tested_options' );

	$choices = trufield_sanitize_product_tested_options( wp_unslash( $_POST['product_tested_options'] ?? [] ) );
	update_option( trufield_product_tested_option_key(), array_values( $choices ), false );

	wp_safe_redirect(
		add_query_arg(
			'tf_products_updated',
			'1',
			admin_url( 'edit.php?post_type=plant_field&page=trufield-products' )
		)
	);
	exit;
}

add_action( 'admin_post_trufield_save_retailer_directory', 'trufield_save_retailer_directory' );
function trufield_save_retailer_directory(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Access denied.', 'trufield-portal' ) );
	}

	check_admin_referer( 'trufield_save_retailer_directory' );

	$names          = wp_unslash( $_POST['retailer_name'] ?? [] );
	$branch_locations = wp_unslash( $_POST['retailer_branch_location'] ?? [] );
	$contact_names  = wp_unslash( $_POST['retailer_key_contact'] ?? [] );
	$contact_phones = wp_unslash( $_POST['retailer_contact_phone'] ?? [] );
	$addresses      = wp_unslash( $_POST['retailer_address'] ?? [] );
	$cities         = wp_unslash( $_POST['retailer_city'] ?? [] );
	$states         = wp_unslash( $_POST['phase_1_state_region'] ?? [] );
	$row_count      = max( count( (array) $names ), count( (array) $branch_locations ), count( (array) $contact_names ), count( (array) $contact_phones ), count( (array) $addresses ), count( (array) $cities ), count( (array) $states ) );
	$rows           = [];

	for ( $index = 0; $index < $row_count; $index++ ) {
		$rows[] = [
			'name'                   => $names[ $index ] ?? '',
			'retailer_branch_location' => $branch_locations[ $index ] ?? '',
			'retailer_key_contact'   => $contact_names[ $index ] ?? '',
			'retailer_contact_phone' => $contact_phones[ $index ] ?? '',
			'retailer_address'       => $addresses[ $index ] ?? '',
			'retailer_city'          => $cities[ $index ] ?? '',
			'phase_1_state_region'   => $states[ $index ] ?? '',
		];
	}

	update_option( trufield_retailer_directory_option_key(), array_values( trufield_sanitize_retailer_directory_entries( $rows ) ), false );

	wp_safe_redirect(
		trufield_retailer_directory_admin_url(
			[
				'tf_retailers_notice' => 'saved',
			]
		)
	);
	exit;
}

add_action( 'admin_post_trufield_import_retailer_directory', 'trufield_import_retailer_directory' );
function trufield_import_retailer_directory(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Access denied.', 'trufield-portal' ) );
	}

	check_admin_referer( 'trufield_import_retailer_directory' );

	$rows = trufield_load_retailer_directory_from_workbook();
	if ( is_wp_error( $rows ) ) {
		$status = 'trufield_retailer_directory_workbook_missing' === $rows->get_error_code() ? 'missing_source' : 'import_failed';

		wp_safe_redirect(
			trufield_retailer_import_admin_url(
				[
					'tf_retailers_notice' => $status,
				]
			)
		);
		exit;
	}

	$entries = trufield_build_retailer_directory_from_workbook();
	update_option( trufield_retailer_directory_option_key(), array_values( $entries ), false );

	wp_safe_redirect(
		trufield_retailer_import_admin_url(
			[
				'tf_retailers_notice' => 'imported',
			]
		)
	);
	exit;
}

add_action( 'admin_post_trufield_clear_retailer_directory', 'trufield_clear_retailer_directory' );
function trufield_clear_retailer_directory(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Access denied.', 'trufield-portal' ) );
	}

	check_admin_referer( 'trufield_clear_retailer_directory' );

	delete_option( trufield_retailer_directory_option_key() );

	wp_safe_redirect(
		trufield_retailer_import_admin_url(
			[
				'tf_retailers_notice' => 'cleared',
			]
		)
	);
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
		__( 'Import Trial Data', 'trufield-portal' ),
		__( 'Import Trial Data', 'trufield-portal' ),
		'trufield_import_fields',
		'trufield-import',
		'trufield_import_page_render'
	);

	add_submenu_page(
		'edit.php?post_type=plant_field',
		__( 'Import Retailers', 'trufield-portal' ),
		__( 'Import Retailers', 'trufield-portal' ),
		'manage_options',
		'trufield-import-retailers',
		'trufield_retailer_import_page_render'
	);

	add_submenu_page(
		'edit.php?post_type=plant_field',
__( 'Export CSV', 'trufield-portal' ),
__( 'Export CSV', 'trufield-portal' ),
'trufield_export_csv',
'trufield-export',
'trufield_export_page_render'
);

	add_submenu_page(
		'edit.php?post_type=plant_field',
		__( 'Retailers', 'trufield-portal' ),
		__( 'Retailers', 'trufield-portal' ),
		'manage_options',
		'trufield-retailers',
		'trufield_retailer_directory_page_render'
	);

	add_submenu_page(
		'edit.php?post_type=plant_field',
		__( 'Products', 'trufield-portal' ),
		__( 'Products', 'trufield-portal' ),
		'manage_options',
		'trufield-products',
		'trufield_product_tested_page_render'
	);
}

function trufield_retailer_import_page_render(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Access denied.', 'trufield-portal' ) );
	}

	$notice          = sanitize_key( wp_unslash( $_GET['tf_retailers_notice'] ?? '' ) );
	$workbook_path   = trufield_retailer_directory_workbook_path();
	$workbook_exists = file_exists( $workbook_path );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import Retailers', 'trufield-portal' ); ?></h1>
		<?php if ( 'imported' === $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Retailer auto-fill list imported from the workbook.', 'trufield-portal' ); ?></p></div>
		<?php elseif ( 'cleared' === $notice ) : ?>
			<div class="notice notice-warning is-dismissible"><p><?php esc_html_e( 'Retailer auto-fill list cleared.', 'trufield-portal' ); ?></p></div>
		<?php elseif ( 'missing_source' === $notice ) : ?>
			<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Retailer workbook not found. Add the updated XLSX file to the site root and try again.', 'trufield-portal' ); ?></p></div>
		<?php elseif ( 'import_failed' === $notice ) : ?>
			<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Retailer workbook could not be imported.', 'trufield-portal' ); ?></p></div>
		<?php endif; ?>

		<p><?php esc_html_e( 'Import the retailer workbook used to populate the Phase 1 retailer auto-fill list. This import updates retailer name, contact, phone, address, city, and state values.', 'trufield-portal' ); ?></p>
		<?php if ( $workbook_exists ) : ?>
			<p><em><?php echo esc_html( sprintf( __( 'Workbook source detected: %s. Importing will replace the current retailer auto-fill list with the rows from that file.', 'trufield-portal' ), basename( $workbook_path ) ) ); ?></em></p>
		<?php else : ?>
			<p><em><?php esc_html_e( 'No retailer workbook is currently detected in the site root.', 'trufield-portal' ); ?></em></p>
		<?php endif; ?>

		<div style="margin:16px 0;">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block; margin-right:8px;">
				<?php wp_nonce_field( 'trufield_import_retailer_directory' ); ?>
				<input type="hidden" name="action" value="trufield_import_retailer_directory">
				<?php submit_button( __( 'Import Retailer Workbook', 'trufield-portal' ), 'primary', 'submit', false, [ 'disabled' => ! $workbook_exists ] ); ?>
			</form>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;" onsubmit="return window.confirm('<?php echo esc_js( __( 'Clear the current retailer auto-fill list?', 'trufield-portal' ) ); ?>');">
				<?php wp_nonce_field( 'trufield_clear_retailer_directory' ); ?>
				<input type="hidden" name="action" value="trufield_clear_retailer_directory">
				<?php submit_button( __( 'Clear Retailer List', 'trufield-portal' ), 'delete', 'submit', false ); ?>
			</form>
		</div>

		<p><a href="<?php echo esc_url( trufield_retailer_directory_admin_url() ); ?>" class="button button-secondary"><?php esc_html_e( 'Manage Retailer Directory', 'trufield-portal' ); ?></a></p>
	</div>
	<?php
}

function trufield_retailer_directory_page_render(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Access denied.', 'trufield-portal' ) );
	}

	$rows            = array_values( trufield_get_retailer_directory() );
	$states          = trufield_state_region_options();
	$notice = sanitize_key( wp_unslash( $_GET['tf_retailers_notice'] ?? '' ) );

	for ( $index = count( $rows ); $index < 8; $index++ ) {
		$rows[] = [
			'name'                   => '',
			'retailer_key_contact'   => '',
			'retailer_contact_phone' => '',
			'phase_1_state_region'   => '',
		];
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Retailer Directory', 'trufield-portal' ); ?></h1>
		<?php if ( 'saved' === $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Retailer directory updated.', 'trufield-portal' ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'Manage the retailer dropdown and the auto-fill values used in Phase 1. Selecting a retailer auto-fills the contact name, contact number, address, city, and state. Branch/location remains the manual entry field for sales reps.', 'trufield-portal' ); ?></p>
		<p><a href="<?php echo esc_url( trufield_retailer_import_admin_url() ); ?>" class="button button-secondary"><?php esc_html_e( 'Go To Retailer Import', 'trufield-portal' ); ?></a></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'trufield_save_retailer_directory' ); ?>
			<input type="hidden" name="action" value="trufield_save_retailer_directory">
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Retailer Name', 'trufield-portal' ); ?></th>
						<th><?php esc_html_e( 'Branch / Location', 'trufield-portal' ); ?></th>
						<th><?php esc_html_e( 'Contact Name', 'trufield-portal' ); ?></th>
						<th><?php esc_html_e( 'Contact Number', 'trufield-portal' ); ?></th>
						<th><?php esc_html_e( 'Address', 'trufield-portal' ); ?></th>
						<th><?php esc_html_e( 'City', 'trufield-portal' ); ?></th>
						<th><?php esc_html_e( 'State', 'trufield-portal' ); ?></th>
					</tr>
				</thead>
				<tbody id="tf-retailer-directory-rows">
					<?php foreach ( $rows as $row ) : ?>
						<tr>
							<td><input type="text" name="retailer_name[]" class="regular-text" value="<?php echo esc_attr( (string) ( $row['name'] ?? '' ) ); ?>"></td>
							<td><input type="text" name="retailer_branch_location[]" class="regular-text" value="<?php echo esc_attr( (string) ( $row['retailer_branch_location'] ?? '' ) ); ?>"></td>
							<td><input type="text" name="retailer_key_contact[]" class="regular-text" value="<?php echo esc_attr( (string) ( $row['retailer_key_contact'] ?? '' ) ); ?>"></td>
							<td><input type="text" name="retailer_contact_phone[]" class="regular-text" value="<?php echo esc_attr( (string) ( $row['retailer_contact_phone'] ?? '' ) ); ?>"></td>
							<td><input type="text" name="retailer_address[]" class="regular-text" value="<?php echo esc_attr( (string) ( $row['retailer_address'] ?? '' ) ); ?>"></td>
							<td><input type="text" name="retailer_city[]" class="regular-text" value="<?php echo esc_attr( (string) ( $row['retailer_city'] ?? '' ) ); ?>"></td>
							<td>
								<select name="phase_1_state_region[]">
									<option value=""><?php esc_html_e( 'Select State', 'trufield-portal' ); ?></option>
									<?php foreach ( $states as $state_value => $state_label ) : ?>
										<option value="<?php echo esc_attr( $state_value ); ?>" <?php selected( (string) ( $row['phase_1_state_region'] ?? '' ), (string) $state_value ); ?>><?php echo esc_html( $state_label ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p><button type="button" class="button" id="tf-add-retailer-row"><?php esc_html_e( 'Add Retailer Row', 'trufield-portal' ); ?></button></p>
			<?php submit_button( __( 'Save Retailers', 'trufield-portal' ) ); ?>
		</form>
	</div>
	<script>
	(function () {
		var addRowButton = document.getElementById('tf-add-retailer-row');
		var rowContainer = document.getElementById('tf-retailer-directory-rows');

		if (!addRowButton || !rowContainer) {
			return;
		}

		var stateOptions = <?php echo wp_json_encode( array_map( 'esc_html', $states ) ); ?>;

		addRowButton.addEventListener('click', function () {
			var row = document.createElement('tr');
			var options = '<option value=""><?php echo esc_js( __( 'Select State', 'trufield-portal' ) ); ?></option>';

			Object.keys(stateOptions).forEach(function (value) {
				options += '<option value="' + value.replace(/"/g, '&quot;') + '">' + stateOptions[value] + '</option>';
			});

			row.innerHTML = '' +
				'<td><input type="text" name="retailer_name[]" class="regular-text"></td>' +
				'<td><input type="text" name="retailer_branch_location[]" class="regular-text"></td>' +
				'<td><input type="text" name="retailer_key_contact[]" class="regular-text"></td>' +
				'<td><input type="text" name="retailer_contact_phone[]" class="regular-text"></td>' +
				'<td><input type="text" name="retailer_address[]" class="regular-text"></td>' +
				'<td><input type="text" name="retailer_city[]" class="regular-text"></td>' +
				'<td><select name="phase_1_state_region[]">' + options + '</select></td>';

			rowContainer.appendChild(row);
		});
	})();
	</script>
	<?php
}

function trufield_product_tested_page_render(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Access denied.', 'trufield-portal' ) );
	}

	$choices = trufield_get_product_tested_choices();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Product Tested Options', 'trufield-portal' ); ?></h1>
		<?php if ( ! empty( $_GET['tf_products_updated'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Product options updated.', 'trufield-portal' ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'Enter one product name per line. These values populate the Product Tested dropdown used when a trial is created and edited.', 'trufield-portal' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'trufield_save_product_tested_options' ); ?>
			<input type="hidden" name="action" value="trufield_save_product_tested_options">
			<textarea name="product_tested_options" rows="10" class="large-text code"><?php echo esc_textarea( implode( "\n", array_values( $choices ) ) ); ?></textarea>
			<?php submit_button( __( 'Save Products', 'trufield-portal' ) ); ?>
		</form>
	</div>
	<?php
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
