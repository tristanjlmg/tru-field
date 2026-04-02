<?php
/**
 * TruField Portal — XLSX Import
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_post_trufield_import_fields', 'trufield_handle_field_import' );

function trufield_import_page_render(): void {
	if ( ! current_user_can( 'trufield_import_fields' ) ) {
		wp_die( esc_html__( 'Access denied.', 'trufield-portal' ) );
	}

	$results = null;
	if ( isset( $_GET['tf_import_results'] ) ) {
		$results = get_transient( 'trufield_import_results_' . get_current_user_id() );
		if ( is_array( $results ) ) {
			delete_transient( 'trufield_import_results_' . get_current_user_id() );
		}
	}

	$action_url = wp_nonce_url( admin_url( 'admin-post.php?action=trufield_import_fields' ), 'trufield_import_fields' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import Plant Fields', 'trufield-portal' ); ?></h1>
		<p><?php esc_html_e( 'Upload the retailer demo XLSX sheet to create Plant Field records in bulk. Imports are create-only and leave Phase 1 as an in-progress draft.', 'trufield-portal' ); ?></p>

		<?php if ( is_array( $results ) ) : ?>
			<div class="notice notice-success">
				<p>
					<?php
					printf(
						esc_html__( 'Import finished. Created: %1$d. Skipped: %2$d. Warnings: %3$d.', 'trufield-portal' ),
						(int) ( $results['created'] ?? 0 ),
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
							<p class="description"><?php esc_html_e( 'Expected worksheet: Retailer Demo List (.xlsx only).', 'trufield-portal' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button( __( 'Import Records', 'trufield-portal' ) ); ?>
		</form>

		<h2><?php esc_html_e( 'Import behavior', 'trufield-portal' ); ?></h2>
		<ul>
			<li><?php esc_html_e( 'Creates new Plant Field records only. Re-importing the same workbook will create duplicates.', 'trufield-portal' ); ?></li>
			<li><?php esc_html_e( 'Matches the Email column to an existing WordPress user and assigns the record when a match is found.', 'trufield-portal' ); ?></li>
			<li><?php esc_html_e( 'Uses Address as the primary geocode input and falls back to the full mailing address when needed.', 'trufield-portal' ); ?></li>
			<li><?php esc_html_e( 'Stores shipping and logistics columns as import metadata on the record.', 'trufield-portal' ); ?></li>
		</ul>
	</div>
	<?php
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
	$spreadsheet_ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

	if ( ! class_exists( 'ZipArchive' ) ) {
		return new WP_Error( 'trufield_import_zip_missing', __( 'The ZipArchive PHP extension is required for XLSX imports.', 'trufield-portal' ) );
	}

	$zip = new ZipArchive();
	if ( true !== $zip->open( $file_path ) ) {
		return new WP_Error( 'trufield_import_open_failed', __( 'The workbook could not be opened.', 'trufield-portal' ) );
	}

	$shared_strings = trufield_import_read_shared_strings( $zip );
	$worksheet_path = trufield_import_first_worksheet_path( $zip );
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

function trufield_import_first_worksheet_path( ZipArchive $zip ) {
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

	$relationship_id = (string) $sheet_nodes[0]->attributes( $document_ns )['id'];
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

function trufield_import_retailer_demo_rows( array $rows, int $user_id ): array {
	$results = [
		'created'  => 0,
		'skipped'  => 0,
		'warnings' => 0,
		'rows'     => [],
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

		foreach ( $prepared['meta'] as $meta_key => $meta_value ) {
			if ( $meta_value === '' || $meta_value === null ) {
				continue;
			}

			update_post_meta( $post_id, $meta_key, $meta_value );
		}

		if ( function_exists( 'trufield_sync_phase_verification_state' ) ) {
			trufield_sync_phase_verification_state( $post_id, 1 );
		}

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

function trufield_prepare_import_row( array $row, string $api_key ) {
	$location      = sanitize_text_field( (string) ( $row['Location'] ?? '' ) );
	$retailer      = sanitize_text_field( (string) ( $row['Retailer'] ?? '' ) );
	$address       = sanitize_text_field( (string) ( $row['Address'] ?? '' ) );
	$city          = sanitize_text_field( (string) ( $row['City'] ?? '' ) );
	$state         = sanitize_text_field( (string) ( $row['State'] ?? '' ) );
	$zip           = sanitize_text_field( (string) ( $row['Zip'] ?? '' ) );
	$key_contact   = sanitize_text_field( (string) ( $row['Key Contact'] ?? '' ) );
	$contact_phone = trufield_import_sanitize_phone( (string) ( $row['Contact Number'] ?? '' ) );
	$rep_email     = sanitize_email( (string) ( $row['Email'] ?? '' ) );
	$rsm_bam       = sanitize_text_field( (string) ( $row['RSM/BAM'] ?? '' ) );
	$warnings      = [];

	if ( $location === '' ) {
		return new WP_Error( 'trufield_import_location_missing', __( 'Location is required.', 'trufield-portal' ) );
	}

	if ( $retailer === '' ) {
		return new WP_Error( 'trufield_import_retailer_missing', __( 'Retailer is required.', 'trufield-portal' ) );
	}

	$meta = [
		'record_status'               => 'active',
		'validation_status'           => 'pending',
		'current_phase'               => 1,
		'phase_1_status'              => 'in_progress',
		'field_name'                  => $location,
		'retailer_name'               => $retailer,
		'field_location_address'      => $address,
		'field_trial_contact'         => $key_contact,
		'contact_phone'               => $contact_phone,
		'rsm_bam'                     => $rsm_bam,
		'import_source_email'         => $rep_email,
		'import_city'                 => $city,
		'import_state'                => $state,
		'import_zip'                  => $zip,
		'phase_1_state_region'        => $state,
		'import_number_of_pallets'    => trufield_import_sanitize_integer( (string) ( $row['Number of Pallets'] ?? '' ) ),
		'phase_1_treated_size_acres'  => trufield_import_sanitize_number( (string) ( $row['Acres of Product'] ?? '' ) ),
		'import_offered'              => trufield_import_sanitize_yes_no( (string) ( $row['Offered Y/N'] ?? '' ) ),
		'import_ready_to_ship'        => trufield_import_sanitize_yes_no( (string) ( $row['Ready to Ship Y/N'] ?? '' ) ),
		'import_shipped'              => trufield_import_sanitize_yes_no( (string) ( $row['Shipped Y/N'] ?? '' ) ),
		'import_bol'                  => sanitize_text_field( (string) ( $row['BOL'] ?? '' ) ),
		'import_notes'                => sanitize_textarea_field( (string) ( $row['Notes'] ?? '' ) ),
	];

	if ( $rep_email !== '' ) {
		$user = get_user_by( 'email', $rep_email );
		if ( $user instanceof WP_User ) {
			$meta['assigned_sales_rep'] = $user->ID;
		} else {
			$warnings[] = sprintf(
				/* translators: %s = email address. */
				__( 'No WordPress user matched %s, so the record was left unassigned.', 'trufield-portal' ),
				$rep_email
			);
		}
	} else {
		$warnings[] = __( 'Email was blank, so the record was left unassigned.', 'trufield-portal' );
	}

	if ( $contact_phone === '' && trim( (string) ( $row['Contact Number'] ?? '' ) ) !== '' ) {
		$warnings[] = __( 'Contact Number could not be normalized and was stored as blank.', 'trufield-portal' );
	}

	if ( $address !== '' && $api_key !== '' ) {
		$geocode = trufield_import_geocode_address( $address, $city, $state, $zip, $api_key );
		if ( is_array( $geocode ) && isset( $geocode['lat'], $geocode['lng'] ) ) {
			$meta['field_location_lat'] = (float) $geocode['lat'];
			$meta['field_location_lng'] = (float) $geocode['lng'];
		} else {
			$warnings[] = __( 'Address could not be geocoded, so latitude and longitude were left blank.', 'trufield-portal' );
		}
	} elseif ( $address !== '' ) {
		$warnings[] = __( 'Google Maps is not configured, so latitude and longitude were not imported.', 'trufield-portal' );
	}

	return [
		'post_title' => $location,
		'meta'       => $meta,
		'warnings'   => $warnings,
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

function trufield_import_sanitize_yes_no( string $value ): string {
	$value = strtolower( trim( $value ) );
	if ( in_array( $value, [ 'y', 'yes', '1', 'true' ], true ) ) {
		return 'yes';
	}

	if ( in_array( $value, [ 'n', 'no', '0', 'false' ], true ) ) {
		return 'no';
	}

	return '';
}