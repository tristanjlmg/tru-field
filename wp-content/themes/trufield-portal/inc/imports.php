<?php
/**
 * TruField Portal — XLSX Import
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_post_trufield_import_fields', 'trufield_handle_field_import' );
add_action( 'admin_post_trufield_save_google_maps_key', 'trufield_handle_save_google_maps_key' );

function trufield_import_page_render(): void {
	if ( ! current_user_can( 'trufield_import_fields' ) ) {
		wp_die( esc_html__( 'Access denied.', 'trufield-portal' ) );
	}

	$results = null;
	$maps_key = get_option( 'trufield_google_maps_api_key', '' );
	$maps_notice = sanitize_key( wp_unslash( $_GET['tf_maps_key'] ?? '' ) );
	if ( isset( $_GET['tf_import_results'] ) ) {
		$results = get_transient( 'trufield_import_results_' . get_current_user_id() );
		if ( is_array( $results ) ) {
			delete_transient( 'trufield_import_results_' . get_current_user_id() );
		}
	}

	$action_url = wp_nonce_url( admin_url( 'admin-post.php?action=trufield_import_fields' ), 'trufield_import_fields' );
	$maps_action_url = wp_nonce_url( admin_url( 'admin-post.php?action=trufield_save_google_maps_key' ), 'trufield_save_google_maps_key' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import Trial Data', 'trufield-portal' ); ?></h1>
		<p><?php esc_html_e( 'Upload the trial-data XLSX sheet to create or update Plant Field records in bulk. Imports preserve existing values when the incoming row is blank, and Phase 1 or Phase 2 will auto-complete when the imported row already satisfies that phase.', 'trufield-portal' ); ?></p>

		<?php if ( 'saved' === $maps_notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Google Maps API key saved.', 'trufield-portal' ); ?></p></div>
		<?php elseif ( 'cleared' === $maps_notice ) : ?>
			<div class="notice notice-warning is-dismissible"><p><?php esc_html_e( 'Google Maps API key cleared.', 'trufield-portal' ); ?></p></div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Google Maps Configuration', 'trufield-portal' ); ?></h2>
		<p><?php esc_html_e( 'Add the production Google Maps API key here to enable Places autocomplete and address verification without editing wp-config.php.', 'trufield-portal' ); ?></p>
		<form method="post" action="<?php echo esc_url( $maps_action_url ); ?>">
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="trufield-google-maps-api-key"><?php esc_html_e( 'Google Maps API Key', 'trufield-portal' ); ?></label></th>
						<td>
							<input
								type="text"
								id="trufield-google-maps-api-key"
								name="trufield_google_maps_api_key"
								class="regular-text code"
								value="<?php echo esc_attr( is_string( $maps_key ) ? $maps_key : '' ); ?>"
								autocomplete="off"
							>
							<p class="description"><?php esc_html_e( 'This key is stored in the WordPress database option trufield_google_maps_api_key. Leave it blank and save to remove it.', 'trufield-portal' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button( __( 'Save Google Maps Key', 'trufield-portal' ), 'secondary', 'submit', false ); ?>
		</form>

		<?php if ( is_array( $results ) ) : ?>
			<div class="notice notice-success">
				<p>
					<?php
					printf(
						esc_html__( 'Import finished. Created: %1$d. Updated: %2$d. Unchanged: %3$d. Skipped: %4$d. Warnings: %5$d.', 'trufield-portal' ),
						(int) ( $results['created'] ?? 0 ),
						(int) ( $results['updated'] ?? 0 ),
						(int) ( $results['unchanged'] ?? 0 ),
						(int) ( $results['skipped'] ?? 0 ),
						(int) ( $results['warnings'] ?? 0 )
					);
					?>
				</p>
			</div>

			<?php if ( ! empty( $results['rows'] ) ) : ?>
				<table class="widefat striped" style="max-width:1200px;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Row', 'trufield-portal' ); ?></th>
							<th><?php esc_html_e( 'Status', 'trufield-portal' ); ?></th>
							<th><?php esc_html_e( 'Record', 'trufield-portal' ); ?></th>
							<th><?php esc_html_e( 'Details', 'trufield-portal' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $results['rows'] as $row_result ) : ?>
							<tr>
								<td><?php echo esc_html( (string) ( $row_result['row'] ?? '' ) ); ?></td>
								<td><?php echo esc_html( (string) ( $row_result['status'] ?? '' ) ); ?></td>
								<td><?php echo esc_html( (string) ( $row_result['title'] ?? '' ) ); ?></td>
								<td><?php echo esc_html( implode( ' | ', array_map( 'strval', $row_result['messages'] ?? [] ) ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( $action_url ); ?>" enctype="multipart/form-data">
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="trufield-import-file"><?php esc_html_e( 'Workbook', 'trufield-portal' ); ?></label></th>
						<td>
							<input type="file" id="trufield-import-file" name="trufield_import_file" accept=".xlsx" required>
							<p class="description"><?php esc_html_e( 'Expected worksheet: Retailer Demo List or the latest Lamark trial-data workbook first sheet (.xlsx only). Use the separate Import Retailers page for retailer auto-fill workbook updates.', 'trufield-portal' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button( __( 'Import Trial Data', 'trufield-portal' ) ); ?>
		</form>

		<p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=plant_field&page=trufield-import-retailers' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Go To Retailer Import', 'trufield-portal' ); ?></a></p>

		<h2><?php esc_html_e( 'Trial Import Behavior', 'trufield-portal' ); ?></h2>
		<ul>
			<li><?php esc_html_e( 'Creates new Plant Field records for rows that do not match existing trials.', 'trufield-portal' ); ?></li>
			<li><?php esc_html_e( 'Updates matched trials with non-empty import values and preserves existing values when the import cell is blank.', 'trufield-portal' ); ?></li>
			<li><?php esc_html_e( 'Leaves matched trials unchanged when no imported values differ.', 'trufield-portal' ); ?></li>
			<li><?php esc_html_e( 'Auto-completes Phase 1 and Phase 2 when the imported row already includes the required data for that phase.', 'trufield-portal' ); ?></li>
			<li><?php esc_html_e( 'Matches the Email column to an existing WordPress user and assigns the record when a match is found.', 'trufield-portal' ); ?></li>
			<li><?php esc_html_e( 'Uses Address as the primary geocode input and falls back to the full mailing address when needed.', 'trufield-portal' ); ?></li>
			<li><?php esc_html_e( 'Stores shipping and logistics columns as import metadata on the record.', 'trufield-portal' ); ?></li>
		</ul>
	</div>
	<?php
}

function trufield_handle_save_google_maps_key(): void {
	$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ?? '' ) );
	if ( ! wp_verify_nonce( $nonce, 'trufield_save_google_maps_key' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'trufield-portal' ), 403 );
	}

	if ( ! current_user_can( 'trufield_import_fields' ) ) {
		wp_die( esc_html__( 'You do not have permission to update the Google Maps key.', 'trufield-portal' ), 403 );
	}

	$key = trim( sanitize_text_field( wp_unslash( $_POST['trufield_google_maps_api_key'] ?? '' ) ) );
	if ( '' === $key ) {
		delete_option( 'trufield_google_maps_api_key' );
		$status = 'cleared';
	} else {
		update_option( 'trufield_google_maps_api_key', $key, false );
		$status = 'saved';
	}

	wp_safe_redirect( add_query_arg( 'tf_maps_key', $status, admin_url( 'edit.php?post_type=plant_field&page=trufield-import' ) ) );
	exit;
}

function trufield_handle_field_import(): void {
	$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ?? '' ) );
	if ( ! wp_verify_nonce( $nonce, 'trufield_import_fields' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'trufield-portal' ), 403 );
	}

	if ( ! current_user_can( 'trufield_import_fields' ) ) {
		wp_die( esc_html__( 'You do not have permission to import records.', 'trufield-portal' ), 403 );
	}

	if ( empty( $_FILES['trufield_import_file'] ) || ! is_array( $_FILES['trufield_import_file'] ) ) {
		wp_die( esc_html__( 'Choose an XLSX file to import.', 'trufield-portal' ), 400 );
	}

	$file = $_FILES['trufield_import_file'];
	if ( (int) ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) !== UPLOAD_ERR_OK ) {
		wp_die( esc_html__( 'The upload did not complete successfully.', 'trufield-portal' ), 400 );
	}

	$filename = (string) ( $file['name'] ?? '' );
	if ( strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) !== 'xlsx' ) {
		wp_die( esc_html__( 'Only .xlsx files are supported.', 'trufield-portal' ), 400 );
	}

	$tmp_name = (string) ( $file['tmp_name'] ?? '' );
	if ( $tmp_name === '' || ! is_uploaded_file( $tmp_name ) ) {
		wp_die( esc_html__( 'Uploaded file could not be read.', 'trufield-portal' ), 400 );
	}

	$rows = trufield_parse_retailer_demo_xlsx( $tmp_name );
	if ( is_wp_error( $rows ) ) {
		wp_die( esc_html( $rows->get_error_message() ), 400 );
	}

	$results = trufield_import_retailer_demo_rows( $rows, get_current_user_id() );
	set_transient( 'trufield_import_results_' . get_current_user_id(), $results, 10 * MINUTE_IN_SECONDS );

	wp_safe_redirect( admin_url( 'edit.php?post_type=plant_field&page=trufield-import&tf_import_results=1' ) );
	exit;
}

function trufield_parse_retailer_demo_xlsx( string $file_path ) {
	return trufield_parse_xlsx_rows( $file_path, [ 'Retailer Demo List', 'RETAILER DEMO LIST' ] );
}

function trufield_parse_xlsx_rows( string $file_path, array $preferred_sheet_names = [] ) {
	$spreadsheet_ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

	if ( ! class_exists( 'ZipArchive' ) ) {
		return new WP_Error( 'trufield_import_zip_missing', __( 'The ZipArchive PHP extension is required for XLSX imports.', 'trufield-portal' ) );
	}

	$zip = new ZipArchive();
	if ( true !== $zip->open( $file_path ) ) {
		return new WP_Error( 'trufield_import_open_failed', __( 'The workbook could not be opened.', 'trufield-portal' ) );
	}

	$shared_strings = trufield_import_read_shared_strings( $zip );
	$worksheet_path = trufield_import_find_worksheet_path( $zip, $preferred_sheet_names );
	if ( is_wp_error( $worksheet_path ) ) {
		$zip->close();
		return $worksheet_path;
	}

	$worksheet_xml = $zip->getFromName( $worksheet_path );
	$zip->close();
	if ( ! is_string( $worksheet_xml ) || $worksheet_xml === '' ) {
		return new WP_Error( 'trufield_import_sheet_missing', __( 'The worksheet could not be read from the workbook.', 'trufield-portal' ) );
	}

	$sheet = simplexml_load_string( $worksheet_xml );
	if ( ! $sheet ) {
		return new WP_Error( 'trufield_import_sheet_invalid', __( 'The workbook sheet format was not recognized.', 'trufield-portal' ) );
	}

	$sheet_children = $sheet->children( $spreadsheet_ns );
	if ( ! isset( $sheet_children->sheetData ) ) {
		return new WP_Error( 'trufield_import_sheet_invalid', __( 'The workbook sheet format was not recognized.', 'trufield-portal' ) );
	}

	$row_nodes = $sheet_children->sheetData->row;
	if ( ! $row_nodes || count( $row_nodes ) === 0 ) {
		return new WP_Error( 'trufield_import_sheet_invalid', __( 'The workbook sheet format was not recognized.', 'trufield-portal' ) );
	}

	$headers       = [];
	$header_lookup = [];
	$rows          = [];

	foreach ( $row_nodes as $row ) {
		$row_attrs   = $row->attributes();
		$row_number  = isset( $row_attrs['r'] ) ? (int) $row_attrs['r'] : 0;
		$values     = [];

		$cell_nodes = $row->children( $spreadsheet_ns );
		if ( ! isset( $cell_nodes->c ) ) {
			continue;
		}

		foreach ( $cell_nodes->c as $cell ) {
			$cell_attrs = $cell->attributes();
			$cell_ref   = isset( $cell_attrs['r'] ) ? (string) $cell_attrs['r'] : '';
			$column_ref = preg_replace( '/\d+/', '', $cell_ref );
			if ( ! is_string( $column_ref ) || $column_ref === '' ) {
				continue;
			}

			$column_index            = trufield_import_column_to_index( $column_ref );
			$values[ $column_index ] = trufield_import_cell_value( $cell, $shared_strings );
		}

		if ( [] === $values ) {
			continue;
		}

		ksort( $values );

		if ( [] === $headers ) {
			foreach ( $values as $column_index => $value ) {
				$value = trim( $value );
				if ( $value === '' ) {
					continue;
				}

				$headers[]                     = $value;
				$header_lookup[ $column_index ] = $value;
			}
			continue;
		}

		$row_data = [ '__row_number' => $row_number ];
		foreach ( $header_lookup as $column_index => $header ) {
			$row_data[ $header ] = isset( $values[ $column_index ] ) ? trim( $values[ $column_index ] ) : '';
		}

		$non_empty_values = array_filter(
			$row_data,
			static fn( $value, $key ): bool => '__row_number' !== $key && trim( (string) $value ) !== '',
			ARRAY_FILTER_USE_BOTH
		);

		if ( [] !== $non_empty_values ) {
			$rows[] = $row_data;
		}
	}

	return $rows;
}

function trufield_import_read_shared_strings( ZipArchive $zip ): array {
	$spreadsheet_ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

	$xml = $zip->getFromName( 'xl/sharedStrings.xml' );
	if ( ! is_string( $xml ) || $xml === '' ) {
		return [];
	}

	$shared_strings_xml = simplexml_load_string( $xml );
	if ( ! $shared_strings_xml ) {
		return [];
	}

	$shared_string_children = $shared_strings_xml->children( $spreadsheet_ns );
	if ( ! isset( $shared_string_children->si ) ) {
		return [];
	}

	$strings = [];
	foreach ( $shared_string_children->si as $item ) {
		$item_children = $item->children( $spreadsheet_ns );
		if ( isset( $item_children->t ) ) {
			$strings[] = (string) $item_children->t;
			continue;
		}

		$text = '';
		if ( isset( $item_children->r ) ) {
			foreach ( $item_children->r as $run ) {
				$run_children = $run->children( $spreadsheet_ns );
				$text        .= isset( $run_children->t ) ? (string) $run_children->t : '';
			}
		}

		$strings[] = $text;
	}

	return $strings;
}

function trufield_import_normalize_lookup_key( string $value ): string {
	return sanitize_key( str_replace( [ '/', '-', '&' ], ' ', $value ) );
}

function trufield_import_find_worksheet_path( ZipArchive $zip, array $preferred_sheet_names = [] ) {
	$workbook_xml = $zip->getFromName( 'xl/workbook.xml' );
	$rels_xml     = $zip->getFromName( 'xl/_rels/workbook.xml.rels' );
	if ( ! is_string( $workbook_xml ) || ! is_string( $rels_xml ) || $workbook_xml === '' || $rels_xml === '' ) {
		return new WP_Error( 'trufield_import_workbook_invalid', __( 'The workbook metadata could not be read.', 'trufield-portal' ) );
	}

	$workbook = simplexml_load_string( $workbook_xml );
	$rels     = simplexml_load_string( $rels_xml );
	if ( ! $workbook || ! $rels ) {
		return new WP_Error( 'trufield_import_workbook_invalid', __( 'The workbook metadata format was not recognized.', 'trufield-portal' ) );
	}

	$document_ns = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
	$workbook->registerXPathNamespace( 'main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main' );
	$workbook->registerXPathNamespace( 'r', $document_ns );
	$rels->registerXPathNamespace( 'rel', 'http://schemas.openxmlformats.org/package/2006/relationships' );

	$sheet_nodes = $workbook->xpath( '/main:workbook/main:sheets/main:sheet' );
	if ( ! is_array( $sheet_nodes ) || empty( $sheet_nodes[0] ) ) {
		return new WP_Error( 'trufield_import_sheet_missing', __( 'No worksheets were found in the workbook.', 'trufield-portal' ) );
	}

	$target_sheet = $sheet_nodes[0];
	$normalized_preferred_names = array_values(
		array_filter(
			array_map(
				static fn( $sheet_name ): string => trufield_import_normalize_lookup_key( (string) $sheet_name ),
				$preferred_sheet_names
			)
		)
	);

	if ( [] !== $normalized_preferred_names ) {
		foreach ( $sheet_nodes as $sheet_node ) {
			$sheet_name = isset( $sheet_node['name'] ) ? (string) $sheet_node['name'] : '';
			if ( in_array( trufield_import_normalize_lookup_key( $sheet_name ), $normalized_preferred_names, true ) ) {
				$target_sheet = $sheet_node;
				break;
			}
		}
	}

	$relationship_id = (string) $target_sheet->attributes( $document_ns )['id'];
	if ( $relationship_id === '' ) {
		return new WP_Error( 'trufield_import_sheet_missing', __( 'The workbook sheet relationship was missing.', 'trufield-portal' ) );
	}

	$relationship_nodes = $rels->xpath( "/rel:Relationships/rel:Relationship[@Id='{$relationship_id}']" );
	if ( ! is_array( $relationship_nodes ) || empty( $relationship_nodes[0] ) ) {
		return new WP_Error( 'trufield_import_sheet_missing', __( 'The workbook sheet could not be resolved.', 'trufield-portal' ) );
	}

	$target = (string) $relationship_nodes[0]['Target'];
	if ( $target === '' ) {
		return new WP_Error( 'trufield_import_sheet_missing', __( 'The worksheet target path was missing.', 'trufield-portal' ) );
	}

	return 0 === strpos( $target, 'xl/' ) ? $target : 'xl/' . ltrim( $target, '/' );
}

function trufield_import_row_value( array $row, array $keys ): string {
	$normalized_row = [];

	foreach ( $row as $header => $value ) {
		if ( ! is_string( $header ) || 0 === strpos( $header, '__' ) ) {
			continue;
		}

		$normalized_row[ trufield_import_normalize_lookup_key( $header ) ] = (string) $value;
	}

	foreach ( $keys as $key ) {
		$normalized_key = trufield_import_normalize_lookup_key( (string) $key );
		$value          = $normalized_row[ $normalized_key ] ?? '';
		if ( trim( (string) $value ) !== '' ) {
			return (string) $value;
		}
	}

	return '';
}

function trufield_import_resolve_assignment_user( string $field, string $email, string $name ): ?WP_User {
	$email = sanitize_email( $email );
	$name  = trim( sanitize_text_field( $name ) );
	$roles = trufield_assignment_user_roles_for_field( $field );

	if ( '' !== $email ) {
		$user = get_user_by( 'email', $email );
		if ( $user instanceof WP_User && [] !== array_intersect( $roles, (array) $user->roles ) ) {
			return $user;
		}
	}

	if ( '' === $name ) {
		return null;
	}

	$users = get_users(
		[
			'role__in' => $roles,
			'search'   => $name,
			'number'   => 20,
		]
	);

	foreach ( $users as $user ) {
		if ( ! $user instanceof WP_User ) {
			$user = get_userdata( (int) ( $user->ID ?? 0 ) );
		}

		if ( ! $user instanceof WP_User ) {
			continue;
		}

		if ( 0 === strcasecmp( $name, (string) $user->display_name ) || ( function_exists( 'trufield_assignment_person_name_matches' ) && trufield_assignment_person_name_matches( $name, $user ) ) ) {
			return $user;
		}
	}

	return null;
}

function trufield_import_resolve_sales_rep_user( string $rep_email, string $rep_name ): ?WP_User {
	return trufield_import_resolve_assignment_user( 'rsm_bam', $rep_email, $rep_name );
}

function trufield_import_normalize_state_region( string $value ): string {
	$value = trim( preg_replace( '/\s+/', ' ', sanitize_text_field( $value ) ) );
	if ( '' === $value ) {
		return '';
	}

	$upper_value = strtoupper( $value );
	$states      = function_exists( 'trufield_state_region_options' ) ? trufield_state_region_options() : [];
	if ( isset( $states[ $upper_value ] ) ) {
		return $upper_value;
	}

	foreach ( $states as $state_code => $state_label ) {
		if ( strtolower( $state_label ) === strtolower( $value ) ) {
			return (string) $state_code;
		}
	}

	return $value;
}

function trufield_import_column_to_index( string $column_ref ): int {
	$column_ref = strtoupper( $column_ref );
	$length     = strlen( $column_ref );
	$index      = 0;

	for ( $i = 0; $i < $length; $i++ ) {
		$index = ( $index * 26 ) + ( ord( $column_ref[ $i ] ) - 64 );
	}

	return $index;
}

function trufield_import_cell_value( SimpleXMLElement $cell, array $shared_strings ): string {
	$spreadsheet_ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
	$cell_attrs      = $cell->attributes();
	$type            = isset( $cell_attrs['t'] ) ? (string) $cell_attrs['t'] : '';
	$cell_children = $cell->children( $spreadsheet_ns );

	if ( $type === 'inlineStr' ) {
		if ( isset( $cell_children->is ) ) {
			$inline_children = $cell_children->is->children( $spreadsheet_ns );
			if ( isset( $inline_children->t ) ) {
				return (string) $inline_children->t;
			}
		}
	}

	if ( $type === 's' ) {
		$index = isset( $cell_children->v ) ? (int) $cell_children->v : -1;
		return $shared_strings[ $index ] ?? '';
	}

	if ( $type === 'b' ) {
		return isset( $cell_children->v ) && (string) $cell_children->v === '1' ? '1' : '0';
	}

	if ( $type === 'str' ) {
		return isset( $cell_children->v ) ? (string) $cell_children->v : '';
	}

	return isset( $cell_children->v ) ? (string) $cell_children->v : '';
}

function trufield_import_normalize_match_component( string $value ): string {
	$value = strtolower( trim( $value ) );
	$value = preg_replace( '/[^a-z0-9]+/', ' ', $value );
	if ( ! is_string( $value ) ) {
		return '';
	}

	return trim( preg_replace( '/\s+/', ' ', $value ) );
}

function trufield_import_match_key_from_meta( array $meta ): string {
	$retailer = trufield_import_normalize_match_component( (string) ( $meta['retailer_name'] ?? '' ) );
	if ( '' === $retailer ) {
		return '';
	}

	$branch = trufield_import_normalize_match_component( (string) ( $meta['retailer_branch_location'] ?? '' ) );
	$state  = trufield_import_normalize_match_component( (string) ( $meta['phase_1_state_region'] ?? '' ) );
	$seed   = '';

	foreach ( [ 'field_name', 'farm_name', 'field_location_address' ] as $location_key ) {
		$candidate = trufield_import_normalize_match_component( (string) ( $meta[ $location_key ] ?? '' ) );
		if ( '' !== $candidate ) {
			$seed = $candidate;
			break;
		}
	}

	if ( '' === $seed ) {
		$seed = trufield_import_normalize_match_component( (string) ( $meta['retailer_address'] ?? '' ) );
	}

	if ( '' === $seed ) {
		return '';
	}

	return implode( '|', [ $retailer, $branch, $seed, $state ] );
}

function trufield_import_find_existing_post_id( array $prepared ): int {
	$match_key = (string) ( $prepared['match_key'] ?? '' );
	if ( '' !== $match_key ) {
		$matched_ids = get_posts(
			[
				'post_type'      => 'plant_field',
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => 1,
				'meta_key'       => 'import_match_key_v1',
				'meta_value'     => $match_key,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			]
		);

		if ( ! empty( $matched_ids ) ) {
			return (int) $matched_ids[0];
		}
	}

	$retailer = (string) ( $prepared['meta']['retailer_name'] ?? '' );
	if ( '' === $retailer ) {
		return 0;
	}

	$meta_query = [
		[
			'key'   => 'retailer_name',
			'value' => $retailer,
		],
	];

	$branch = (string) ( $prepared['meta']['retailer_branch_location'] ?? '' );
	if ( '' !== $branch ) {
		$meta_query[] = [
			'key'   => 'retailer_branch_location',
			'value' => $branch,
		];
	}

	$candidate_ids = get_posts(
		[
			'post_type'      => 'plant_field',
			'post_status'    => 'any',
			'fields'         => 'ids',
			'posts_per_page' => 200,
			'meta_query'     => $meta_query,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		]
	);

	if ( empty( $candidate_ids ) ) {
		return 0;
	}

	foreach ( $candidate_ids as $candidate_id ) {
		$candidate_id = (int) $candidate_id;
		$existing_key = trim( (string) get_post_meta( $candidate_id, 'import_match_key_v1', true ) );
		if ( '' === $existing_key ) {
			$existing_key = trufield_import_match_key_from_meta(
				[
					'retailer_name'             => (string) get_post_meta( $candidate_id, 'retailer_name', true ),
					'retailer_branch_location'  => (string) get_post_meta( $candidate_id, 'retailer_branch_location', true ),
					'field_name'                => (string) get_post_meta( $candidate_id, 'field_name', true ),
					'farm_name'                 => (string) get_post_meta( $candidate_id, 'farm_name', true ),
					'field_location_address'    => (string) get_post_meta( $candidate_id, 'field_location_address', true ),
					'retailer_address'          => (string) get_post_meta( $candidate_id, 'retailer_address', true ),
					'phase_1_state_region'      => (string) get_post_meta( $candidate_id, 'phase_1_state_region', true ),
				]
			);
		}

		if ( '' !== $match_key && $existing_key === $match_key ) {
			return $candidate_id;
		}
	}

	return 0;
}

function trufield_import_meta_is_empty( $value ): bool {
	if ( null === $value ) {
		return true;
	}

	if ( is_string( $value ) ) {
		return '' === trim( $value );
	}

	if ( is_array( $value ) ) {
		return [] === $value;
	}

	return false;
}

function trufield_import_apply_meta_updates( int $post_id, array $meta ): int {
	$changes = 0;

	foreach ( $meta as $meta_key => $meta_value ) {
		if ( trufield_import_meta_is_empty( $meta_value ) ) {
			continue;
		}

		$current_value = get_post_meta( $post_id, $meta_key, true );
		if ( is_numeric( $current_value ) && is_numeric( $meta_value ) ) {
			if ( (string) +$current_value === (string) +$meta_value ) {
				continue;
			}
		} elseif ( (string) $current_value === (string) $meta_value ) {
			continue;
		}

		update_post_meta( $post_id, $meta_key, $meta_value );
		$changes++;
	}

	return $changes;
}

function trufield_import_retailer_demo_rows( array $rows, int $user_id ): array {
	$results = [
		'created'   => 0,
		'updated'   => 0,
		'unchanged' => 0,
		'skipped'   => 0,
		'warnings'  => 0,
		'rows'      => [],
	];

	$api_key = trufield_get_google_maps_api_key();

	foreach ( $rows as $row ) {
		$row_number = (int) ( $row['__row_number'] ?? 0 );
		$prepared   = trufield_prepare_import_row( $row, $api_key );

		if ( is_wp_error( $prepared ) ) {
			$results['skipped']++;
			$results['rows'][] = [
				'row'      => $row_number,
				'status'   => 'Skipped',
				'title'    => (string) ( $row['Location'] ?? '' ),
				'messages' => [ $prepared->get_error_message() ],
			];
			continue;
		}

		$match_key = (string) ( $prepared['match_key'] ?? '' );
		$existing_post_id = trufield_import_find_existing_post_id( $prepared );

		if ( $existing_post_id > 0 ) {
			$changed_fields = trufield_import_apply_meta_updates( $existing_post_id, $prepared['meta'] );
			if ( '' !== $match_key ) {
				update_post_meta( $existing_post_id, 'import_match_key_v1', $match_key );
			}

			trufield_sync_imported_phase_states( $existing_post_id );

			$results['warnings'] += count( $prepared['warnings'] );
			$messages = [];
			if ( $changed_fields > 0 ) {
				$results['updated']++;
				$messages[] = sprintf(
					/* translators: %d = number of meta fields changed. */
					__( 'Record updated (%d fields changed).', 'trufield-portal' ),
					$changed_fields
				);
				$status = [] === $prepared['warnings'] ? 'Updated' : 'Updated with warnings';
			} else {
				$results['unchanged']++;
				$messages[] = __( 'No changes were needed for this existing record.', 'trufield-portal' );
				$status = [] === $prepared['warnings'] ? 'Unchanged' : 'Unchanged with warnings';
			}

			if ( [] !== $prepared['warnings'] ) {
				$messages = array_merge( $messages, $prepared['warnings'] );
			}

			$results['rows'][] = [
				'row'      => $row_number,
				'status'   => $status,
				'title'    => get_the_title( $existing_post_id ),
				'messages' => $messages,
			];
			continue;
		}

		$post_id = wp_insert_post(
			[
				'post_type'   => 'plant_field',
				'post_status' => 'publish',
				'post_title'  => $prepared['post_title'],
				'post_author' => $user_id,
			],
			true
		);

		if ( is_wp_error( $post_id ) ) {
			$results['skipped']++;
			$results['rows'][] = [
				'row'      => $row_number,
				'status'   => 'Skipped',
				'title'    => $prepared['post_title'],
				'messages' => [ $post_id->get_error_message() ],
			];
			continue;
		}

		trufield_import_apply_meta_updates( (int) $post_id, $prepared['meta'] );
		if ( '' !== $match_key ) {
			update_post_meta( (int) $post_id, 'import_match_key_v1', $match_key );
		}

		trufield_sync_imported_phase_states( $post_id );

		$results['created']++;
		$results['warnings'] += count( $prepared['warnings'] );
		$messages = [ __( 'Record created.', 'trufield-portal' ) ];
		if ( [] !== $prepared['warnings'] ) {
			$messages = array_merge( $messages, $prepared['warnings'] );
		}

		$results['rows'][] = [
			'row'      => $row_number,
			'status'   => [] === $prepared['warnings'] ? 'Created' : 'Created with warnings',
			'title'    => $prepared['post_title'],
			'messages' => $messages,
		];
	}

	return $results;
}

function trufield_sync_imported_phase_states( int $post_id ): void {
	if ( ! function_exists( 'trufield_sync_phase_verification_state' ) ) {
		return;
	}

	foreach ( [ 1, 2 ] as $phase ) {
		trufield_sync_phase_verification_state( $post_id, $phase );
	}
}

function trufield_prepare_import_row( array $row, string $api_key ) {
	$field_name          = sanitize_text_field( trufield_import_row_value( $row, [ 'field_name', 'Field Name', 'Field Name (Optional)', 'Location' ] ) );
	$farm_name           = sanitize_text_field( trufield_import_row_value( $row, [ 'farm_name', 'Farm Name', 'Farm Name (Optional)' ] ) );
	$retailer_location   = sanitize_text_field( trufield_import_row_value( $row, [ 'retailer_branch_location', 'Retailer Branch Location', 'Branch Location' ] ) );
	$retailer            = sanitize_text_field( trufield_import_row_value( $row, [ 'retailer_name', 'Retailer Name', 'Retailer' ] ) );
	$address             = sanitize_text_field( trufield_import_row_value( $row, [ 'retailer_address', 'Retailer Address', 'field_location_address', 'Field Location Address', 'Address' ] ) );
	$city                = sanitize_text_field( trufield_import_row_value( $row, [ 'retailer_city', 'Retailer City', 'City' ] ) );
	$state               = trufield_import_normalize_state_region( trufield_import_row_value( $row, [ 'phase_1_state_region', 'Retailer State', 'State', 'State Region' ] ) );
	$zip                 = sanitize_text_field( trufield_import_row_value( $row, [ 'Zip', 'ZIP', 'Postal Code' ] ) );
	$key_contact         = sanitize_text_field( trufield_import_row_value( $row, [ 'retailer_key_contact', 'Retailer Contact', 'Retailer Contact Name', 'Key Contact' ] ) );
	$contact_phone       = trufield_import_sanitize_phone( trufield_import_row_value( $row, [ 'retailer_contact_phone', 'Retailer Contact Phone #', 'Retailer Contact Number', 'RetailerContactPhone', 'Contact Number' ] ) );
	$field_trial_contact = sanitize_text_field( trufield_import_row_value( $row, [ 'field_trial_contact', 'Field Trial Contact', 'Crop Specialist/Field Trial Contact (First Last)', 'Crop Specialist Field Trial Contact (First Last)', 'retailer_key_contact', 'Retailer Contact', 'Key Contact' ] ) );
	$field_trial_phone   = trufield_import_sanitize_phone( trufield_import_row_value( $row, [ 'contact_phone', 'Field Trial Contact Phone', 'Crop Specialist/ Field Trial Contact Phone', 'Crop Specialist Field Trial Contact Phone' ] ) );
	$field_trial_email   = sanitize_email( trufield_import_row_value( $row, [ 'field_trial_contact_email', 'Field Trial Contact Email', 'Crop Specialist/ Field Trial Email', 'Crop Specialist Field Trial Email' ] ) );
	$field_address       = sanitize_text_field( trufield_import_row_value( $row, [ 'field_location_address', 'Field Location Address', 'retailer_address', 'Retailer Address', 'Address' ] ) );
	$lat_lng_value       = trufield_import_row_value( $row, [ 'Lat/Long of Trial', 'Lat Long of Trial' ] );
	$rep_email           = sanitize_email( trufield_import_row_value( $row, [ 'RSM/BAM ID', 'Email', 'Assigned Sales Rep Email' ] ) );
	$rsm_bam             = sanitize_text_field( trufield_import_row_value( $row, [ 'RSM/BAM', 'RSM BAM', 'rsm_bam' ] ) );
	$fsa_name            = sanitize_text_field( trufield_import_row_value( $row, [ 'FSA', 'fsa' ] ) );
	$sales_rep_user      = trufield_import_resolve_sales_rep_user( $rep_email, $rsm_bam );
	$fsa_user            = trufield_import_resolve_assignment_user( 'fsa', '', $fsa_name );
	$warnings            = [];
	$post_title          = $field_name;
	$parsed_lat_lng      = trufield_import_parse_lat_lng( $lat_lng_value );

	if ( $retailer === '' ) {
		return new WP_Error( 'trufield_import_retailer_missing', __( 'Retailer is required.', 'trufield-portal' ) );
	}

	if ( '' === $post_title ) {
		$post_title = $farm_name;
	}

	if ( '' === $post_title ) {
		$post_title = trim( $retailer . ( $retailer_location !== '' ? ' - ' . $retailer_location : '' ) );
	}

	if ( '' === $post_title ) {
		return new WP_Error( 'trufield_import_location_missing', __( 'A record title could not be derived from the workbook row.', 'trufield-portal' ) );
	}

	$retailer_location = trufield_normalize_retailer_branch_location( $retailer, $retailer_location );

	$meta = [
		'record_status'               => 'active',
		'validation_status'           => 'pending',
		'current_phase'               => 1,
		'phase_1_status'              => 'in_progress',
		'field_name'                  => $field_name,
		'farm_name'                   => $farm_name,
		'retailer_name'               => $retailer,
		'retailer_branch_location'    => $retailer_location,
		'retailer_key_contact'        => $key_contact,
		'retailer_contact_phone'      => $contact_phone,
		'retailer_address'            => $address,
		'retailer_city'               => $city,
		'field_location_address'      => $field_address,
		'field_trial_contact'         => $field_trial_contact,
		'contact_phone'               => $field_trial_phone,
		'field_trial_contact_email'   => $field_trial_email,
		'rsm_bam'                     => $sales_rep_user instanceof WP_User ? $sales_rep_user->ID : $rsm_bam,
		'import_source_email'         => $rep_email,
		'import_city'                 => $city,
		'import_state'                => $state,
		'import_zip'                  => $zip,
		'phase_1_state_region'        => $state,
		'import_number_of_pallets'    => trufield_import_sanitize_integer( trufield_import_row_value( $row, [ 'Number of Pallets' ] ) ),
		'phase_1_treated_size_acres'  => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Acres of Product', 'Treated Size (ac)' ] ) ),
		'phase_1_application_rate'    => sanitize_text_field( trufield_import_row_value( $row, [ 'Applied Rate (oz/ac)', 'Applied Rate', 'phase_1_application_rate' ] ) ),
		'phase_1_trial_type'          => sanitize_text_field( trufield_import_row_value( $row, [ 'Trial Type', 'phase_1_trial_type' ] ) ),
		'phase_1_protocol_version'    => sanitize_text_field( trufield_import_row_value( $row, [ 'Protocol Version', 'phase_1_protocol_version' ] ) ),
		'phase_1_application_timing'  => sanitize_text_field( trufield_import_row_value( $row, [ 'Application Timing', 'phase_1_application_timing' ] ) ),
		'phase_1_application_date'    => trufield_import_sanitize_date( trufield_import_row_value( $row, [ 'Application Date', 'phase_1_application_date' ] ) ),
		'phase_1_retailer_training_discussion_date' => trufield_import_sanitize_date( trufield_import_row_value( $row, [ 'Retailer Product Training/Discussion Date', 'Retailer Product Training/Discussion', 'Product Training Date', 'phase_1_retailer_training_discussion_date' ] ) ),
		'phase_2_rsm_visit_1_date'    => trufield_import_sanitize_date( trufield_import_row_value( $row, [ 'RSM Visit Date 1', 'phase_2_rsm_visit_1_date' ] ) ),
		'phase_2_rsm_visit_1_upload_photos' => esc_url_raw( trufield_import_row_value( $row, [ 'RSM Visit 1 Date Photos Taken Treated/Untreated', 'PHOTOS Visit 1', 'phase_2_rsm_visit_1_upload_photos' ] ) ),
		'phase_2_rsm_visit_2_date'    => trufield_import_sanitize_date( trufield_import_row_value( $row, [ 'RSM Visit Date 2', 'phase_2_rsm_visit_2_date' ] ) ),
		'phase_2_rsm_visit_2_upload_photos' => esc_url_raw( trufield_import_row_value( $row, [ 'RSM Visit 2 Date Photos Taken Treated/Untreated', 'PHOTOS Visit 2', 'phase_2_rsm_visit_2_upload_photos' ] ) ),
		'phase_2_rsm_visit_3_date'    => trufield_import_sanitize_date( trufield_import_row_value( $row, [ 'Optional Visit Date 3', 'phase_2_rsm_visit_3_date' ] ) ),
		'phase_2_rsm_visit_3_upload_photos' => esc_url_raw( trufield_import_row_value( $row, [ 'Optional Visit 3 Date Photos Taken Treated/Untreated', 'phase_2_rsm_visit_3_upload_photos' ] ) ),
		'phase_2_rsm_visit_3_comments' => sanitize_textarea_field( trufield_import_row_value( $row, [ 'Visit 3 Notes', 'phase_2_rsm_visit_3_comments' ] ) ),
		'phase_2_rsm_visit_4_date'    => trufield_import_sanitize_date( trufield_import_row_value( $row, [ 'Optional Visit Date 4', 'phase_2_rsm_visit_4_date' ] ) ),
		'phase_2_rsm_visit_4_upload_photos' => esc_url_raw( trufield_import_row_value( $row, [ 'Optional Visit 4 Date Photos Taken Treated/Untreated', 'phase_2_rsm_visit_4_upload_photos' ] ) ),
		'phase_2_rsm_visit_4_comments' => sanitize_textarea_field( trufield_import_row_value( $row, [ 'Visit 4 Notes', 'phase_2_rsm_visit_4_comments' ] ) ),
		'phase_2_residue_degradation_observed' => trufield_import_sanitize_yes_no( trufield_import_row_value( $row, [ 'Residue Degradation Observed? Y/N', 'phase_2_residue_degradation_observed' ] ) ),
		'phase_2_emergence_stand_collected' => trufield_import_sanitize_yes_no( trufield_import_row_value( $row, [ 'Emergence, Stand collected (Y/N)', 'phase_2_emergence_stand_collected' ] ) ),
		'phase_2_stand_count_1_treated' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Stand Count 1 TREATED (30" ROW = 17.5\' & 15" ROW  =34.9\')', 'phase_2_stand_count_1_treated' ] ) ),
		'phase_2_stand_count_2_treated' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Stand Count 2 TREATED (30" ROW = 17.5\' & 15" ROW  =34.9\')', 'phase_2_stand_count_2_treated' ] ) ),
		'phase_2_stand_count_3_treated' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Stand Count 3 TREATED (30" ROW = 17.5\' & 15" ROW  =34.9\')', 'phase_2_stand_count_3_treated' ] ) ),
		'phase_2_stand_count_1_untreated' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Stand Count 1 UNTREATED (30" ROW = 17.5\' & 15" ROW  =34.9\')', 'phase_2_stand_count_1_untreated' ] ) ),
		'phase_2_stand_count_2_untreated' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Stand Count 2 UNTREATED (30" ROW = 17.5\' & 15" ROW  =34.9\')', 'phase_2_stand_count_2_untreated' ] ) ),
		'phase_2_stand_count_3_untreated' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Stand Count 3 UNTREATED (30" ROW = 17.5\' & 15" ROW  =34.9\')', 'phase_2_stand_count_3_untreated' ] ) ),
		'phase_2_stand_count_data'    => sanitize_text_field( trufield_import_row_value( $row, [ 'Stand Count Deltas (plt/A)', 'phase_2_stand_count_data' ] ) ),
		'phase_2_most_significant_visual_difference' => sanitize_textarea_field( trufield_import_row_value( $row, [ 'What is the most significant visual difference today (e.g., even emergence, residue breakdown)?', 'phase_2_most_significant_visual_difference' ] ) ),
		'phase_2_emergence_flag_test' => trufield_import_sanitize_yes_no( trufield_import_row_value( $row, [ 'Emergence (Flag Test) (Y/N)', 'phase_2_emergence_flag_test' ] ) ),
		'phase_2_pictures_at_application' => trufield_import_sanitize_yes_no( trufield_import_row_value( $row, [ 'Pictures at Application (Y/N)', 'phase_2_pictures_at_application' ] ) ),
		'phase_2_pictures_at_planting' => trufield_import_sanitize_yes_no( trufield_import_row_value( $row, [ 'Pictures at Planting (Y/N)', 'phase_2_pictures_at_planting' ] ) ),
		'phase_2_pictures_in_season_harvest' => trufield_import_sanitize_yes_no( trufield_import_row_value( $row, [ 'Pictures In season/ Harvest (Y/N)', 'phase_2_pictures_in_season_harvest' ] ) ),
		'phase_2_pictures_at_harvest' => trufield_import_sanitize_yes_no( trufield_import_row_value( $row, [ 'Pictures In season/ Harvest (Y/N)', 'phase_2_pictures_at_harvest' ] ) ),
		'phase_2_drone_images_available' => trufield_import_sanitize_yes_no( trufield_import_row_value( $row, [ 'Drone Images Available (Y/N)', 'phase_2_drone_images_available' ] ) ),
		'phase_2_grower_retailer_testimonials' => trufield_import_sanitize_yes_no( trufield_import_row_value( $row, [ 'Grower / Retailer Testimonials (Y/N)', 'phase_2_grower_retailer_testimonials' ] ) ),
		'phase_2_grower_retailer_comments' => sanitize_textarea_field( trufield_import_row_value( $row, [ 'Grower / Retailer Comments', 'phase_2_grower_retailer_comments' ] ) ),
		'phase_3_event_type'          => trufield_import_sanitize_yes_no( trufield_import_row_value( $row, [ 'TruField In Person Workshop/Demo Day (Yes or No)', 'phase_3_event_type' ] ) ),
		'phase_3_event_date'          => trufield_import_sanitize_date( trufield_import_row_value( $row, [ 'TruField In Person Workshop/Demo Day Date Held', 'TruField In Person Workshop/Demo Day Date Held ', 'phase_3_event_date' ] ) ),
		'phase_3_event_location'      => sanitize_text_field( trufield_import_row_value( $row, [ 'TruField In Person Workshop/Demo Day Location', 'phase_3_event_location' ] ) ),
		'phase_3_attendee_count'      => trufield_import_sanitize_integer( trufield_import_row_value( $row, [ 'TruField In Person Workshop/Demo Day Number of Attendees', 'TruField In Person Workshop/Demo Day             Number of Attendees', 'phase_3_attendee_count' ] ) ),
		'phase_3_tillage_type'        => sanitize_text_field( trufield_import_row_value( $row, [ 'Tillage Type', 'phase_3_tillage_type' ] ) ),
		'phase_3_soil_temp_f_at_application' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Soil Temp (F) at application', 'phase_3_soil_temp_f_at_application' ] ) ),
		'phase_3_carrier_volume_gal'  => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Carrier Volume (Gal)', 'phase_3_carrier_volume_gal' ] ) ),
		'phase_3_tank_mix_partners'   => sanitize_textarea_field( trufield_import_row_value( $row, [ 'Tank Mix Partners', 'phase_3_tank_mix_partners' ] ) ),
		'phase_3_planting_date'       => trufield_import_sanitize_date( trufield_import_row_value( $row, [ 'Planting Date', 'phase_3_planting_date' ] ) ),
		'phase_3_hybrid_variety'      => sanitize_text_field( trufield_import_row_value( $row, [ 'Hybrid/Variety', 'phase_3_hybrid_variety' ] ) ),
		'phase_3_planting_population' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Planting Population', 'phase_3_planting_population' ] ) ),
		'phase_3_row_spacing_in'      => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Row Spacing (in)', 'phase_3_row_spacing_in' ] ) ),
		'phase_3_planting_speed_mph'  => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Planting Speed (mph)', 'phase_3_planting_speed_mph' ] ) ),
		'phase_3_plant_heights_avg_untreated_v7_in' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Plant Heights Avg Untreated @ V7 (In)', 'phase_3_plant_heights_avg_untreated_v7_in' ] ) ),
		'phase_3_plant_heights_avg_treated_v7_in' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Plant Heights Avg Treated @ V7 (In)', 'phase_3_plant_heights_avg_treated_v7_in' ] ) ),
		'phase_3_stalk_diameter_untreated_v7_mm' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Stalk Diameter Untreated @ V7 (mm)', 'phase_3_stalk_diameter_untreated_v7_mm' ] ) ),
		'phase_3_stalk_diameter_treated_v7_mm2' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Stalk DiameterTreated @ V7 (mm)2', 'phase_3_stalk_diameter_treated_v7_mm2' ] ) ),
		'phase_3_yield_untreated_bu_ac' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Yield Untreated (bu/ac)', 'phase_3_yield_untreated_bu_ac' ] ) ),
		'phase_3_yield_treated_bu_ac' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Yield Treated (bu/ac)', 'phase_3_yield_treated_bu_ac' ] ) ),
		'phase_3_moisture_untreated_percent' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Moisture Untreated (%)', 'phase_3_moisture_untreated_percent' ] ) ),
		'phase_3_moisture_treated_percent' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Moisture Treated (%)', 'phase_3_moisture_treated_percent' ] ) ),
		'phase_3_test_weight_untreated_lbs_bu' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Test Weight Untreated (lbs/bu)', 'phase_3_test_weight_untreated_lbs_bu' ] ) ),
		'phase_3_test_weight_treated_lbs_bu' => trufield_import_sanitize_number( trufield_import_row_value( $row, [ 'Test Weight Treated (lbs/bu)', 'phase_3_test_weight_treated_lbs_bu' ] ) ),
		'phase_3_as_applied_gis_data' => trufield_import_sanitize_yes_no( trufield_import_row_value( $row, [ 'As Applied GIS Data (Y/N)', 'phase_3_as_applied_gis_data' ] ) ),
		'phase_3_planting_gis_data'   => trufield_import_sanitize_yes_no( trufield_import_row_value( $row, [ 'Planting GIS Data (Y/N)', 'phase_3_planting_gis_data' ] ) ),
		'phase_3_harvest_gis_data'    => trufield_import_sanitize_yes_no( trufield_import_row_value( $row, [ 'Harvest GIS Data (Y/N)', 'phase_3_harvest_gis_data' ] ) ),
		'phase_3_agronomy_comments'   => sanitize_textarea_field( trufield_import_row_value( $row, [ 'Agronomy Comments', 'phase_3_agronomy_comments' ] ) ),
		'import_offered'              => trufield_import_sanitize_yes_no( trufield_import_row_value( $row, [ 'Offered Y/N' ] ) ),
		'import_ready_to_ship'        => trufield_import_sanitize_yes_no( trufield_import_row_value( $row, [ 'Ready to Ship Y/N' ] ) ),
		'import_shipped'              => trufield_import_sanitize_yes_no( trufield_import_row_value( $row, [ 'Shipped Y/N' ] ) ),
		'import_bol'                  => sanitize_text_field( trufield_import_row_value( $row, [ 'BOL' ] ) ),
		'import_notes'                => sanitize_textarea_field( trufield_import_row_value( $row, [ 'Notes', 'Column1' ] ) ),
	];

	$match_key = trufield_import_match_key_from_meta( $meta );
	if ( '' === $match_key ) {
		$warnings[] = __( 'A stable trial match key could not be derived from this row, so future updates may create a new record.', 'trufield-portal' );
	}

	if ( '' === $meta['contact_phone'] ) {
		$meta['contact_phone'] = $contact_phone;
	}

	if ( $sales_rep_user instanceof WP_User ) {
		$meta['assigned_sales_rep'] = $sales_rep_user->ID;
	} elseif ( $rep_email !== '' ) {
			$warnings[] = sprintf(
				/* translators: %s = sales rep email. */
				__( 'No sales rep user matched %s, so the record was left unassigned.', 'trufield-portal' ),
				$rep_email
			);
	} elseif ( $rsm_bam !== '' ) {
		$warnings[] = sprintf(
			/* translators: %s = sales rep name. */
			__( 'No sales rep user matched %s, so the record was left unassigned.', 'trufield-portal' ),
			$rsm_bam
		);
	} else {
		$warnings[] = __( 'RSM/BAM details were blank, so the record was left unassigned.', 'trufield-portal' );
	}

	if ( $fsa_user instanceof WP_User ) {
		$meta['fsa'] = $fsa_user->ID;
	} elseif ( $fsa_name !== '' ) {
		$warnings[] = sprintf(
			/* translators: %s = FSA name. */
			__( 'No FSA user matched %s, so the imported FSA was stored as blank.', 'trufield-portal' ),
			$fsa_name
		);
	}

	if ( $contact_phone === '' && trim( trufield_import_row_value( $row, [ 'retailer_contact_phone', 'Retailer Contact Phone #', 'Retailer Contact Number', 'Contact Number' ] ) ) !== '' ) {
		$warnings[] = __( 'Contact Number could not be normalized and was stored as blank.', 'trufield-portal' );
	}

	if ( is_array( $parsed_lat_lng ) ) {
		$meta['field_location_lat'] = $parsed_lat_lng['lat'];
		$meta['field_location_lng'] = $parsed_lat_lng['lng'];
	} elseif ( $field_address !== '' && $api_key !== '' ) {
		$geocode = trufield_import_geocode_address( $field_address, $city, $state, $zip, $api_key );
		if ( is_array( $geocode ) && isset( $geocode['lat'], $geocode['lng'] ) ) {
			$meta['field_location_lat'] = (float) $geocode['lat'];
			$meta['field_location_lng'] = (float) $geocode['lng'];
		} else {
			$warnings[] = __( 'Address could not be geocoded, so latitude and longitude were left blank.', 'trufield-portal' );
		}
	} elseif ( $field_address !== '' ) {
		$warnings[] = __( 'Google Maps is not configured, so latitude and longitude were not imported.', 'trufield-portal' );
	}

	return [
		'post_title' => $post_title,
		'meta'       => $meta,
		'match_key'  => $match_key,
		'warnings'   => $warnings,
	];
}

function trufield_import_parse_lat_lng( string $value ): ?array {
	$value = trim( $value );
	if ( '' === $value ) {
		return null;
	}

	if ( ! preg_match( '/(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/', $value, $matches ) ) {
		return null;
	}

	return [
		'lat' => (float) $matches[1],
		'lng' => (float) $matches[2],
	];
}

function trufield_import_geocode_address( string $address, string $city, string $state, string $zip, string $api_key ): ?array {
	$attempts = [ $address ];
	$full     = trim( implode( ', ', array_filter( [ $address, $city, $state, $zip ] ) ) );
	if ( $full !== '' && $full !== $address ) {
		$attempts[] = $full;
	}

	foreach ( $attempts as $attempt ) {
		$result = trufield_lookup_address_coordinates( $attempt, $api_key );
		if ( is_array( $result ) && isset( $result['lat'], $result['lng'] ) ) {
			return $result;
		}
	}

	return null;
}

function trufield_import_sanitize_phone( string $phone ): string {
	$phone = trim( $phone );
	if ( $phone === '' ) {
		return '';
	}

	$digits = preg_replace( '/\D+/', '', $phone );
	if ( ! is_string( $digits ) ) {
		return '';
	}

	if ( strlen( $digits ) === 11 && 0 === strpos( $digits, '1' ) ) {
		$digits = substr( $digits, 1 );
	}

	if ( strlen( $digits ) !== 10 ) {
		return '';
	}

	return sprintf( '%s-%s-%s', substr( $digits, 0, 3 ), substr( $digits, 3, 3 ), substr( $digits, 6, 4 ) );
}

function trufield_import_sanitize_integer( string $value ) {
	$value = trim( $value );
	if ( $value === '' ) {
		return '';
	}

	return absint( $value );
}

function trufield_import_sanitize_number( string $value ) {
	$value = trim( $value );
	if ( $value === '' ) {
		return '';
	}

	return (float) $value;
}

function trufield_import_sanitize_date( string $value ): string {
	$value = trim( $value );
	if ( '' === $value ) {
		return '';
	}

	if ( is_numeric( $value ) ) {
		$serial = (float) $value;
		if ( $serial > 0 ) {
			$timestamp = (int) round( ( $serial - 25569 ) * DAY_IN_SECONDS );
			if ( $timestamp > 0 ) {
				return gmdate( 'Y-m-d', $timestamp );
			}
		}
	}

	$timestamp = strtotime( $value );
	if ( false === $timestamp ) {
		return '';
	}

	return gmdate( 'Y-m-d', $timestamp );
}

function trufield_import_sanitize_yes_no( string $value ): string {
	$value = strtolower( trim( $value ) );
	if ( in_array( $value, [ 'y', 'yes', '1', 'true' ], true ) ) {
		return 'yes';
	}

	if ( preg_match( '/^(y|yes)\b/', $value ) ) {
		return 'yes';
	}

	if ( in_array( $value, [ 'n', 'no', '0', 'false' ], true ) ) {
		return 'no';
	}

	if ( preg_match( '/^(n|no)\b/', $value ) ) {
		return 'no';
	}

	return '';
}