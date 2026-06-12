<?php
/**
 * TruField Portal — Workflow Helpers
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

define( 'TRUFIELD_VALID_STATUSES', [ 'pending', 'in_progress', 'completed' ] );

function trufield_get_phase_status( int $post_id, int $phase ): string {
$status = get_post_meta( $post_id, "phase_{$phase}_status", true );
return in_array( $status, TRUFIELD_VALID_STATUSES, true ) ? $status : 'pending';
}

function trufield_user_is_admin( int $user_id = 0 ): bool {
if ( $user_id > 0 ) {
$user = get_userdata( $user_id );
if ( ! $user ) {
return false;
}

return in_array( 'administrator', (array) $user->roles, true )
|| user_can( $user_id, 'manage_options' )
|| user_can( $user_id, 'administrator' );
}

return current_user_can( 'administrator' ) || current_user_can( 'manage_options' );
}

function trufield_state_region_options(): array {
return [
'AL' => 'Alabama',
'AK' => 'Alaska',
'AZ' => 'Arizona',
'AR' => 'Arkansas',
'CA' => 'California',
'CO' => 'Colorado',
'CT' => 'Connecticut',
'DE' => 'Delaware',
'FL' => 'Florida',
'GA' => 'Georgia',
'HI' => 'Hawaii',
'ID' => 'Idaho',
'IL' => 'Illinois',
'IN' => 'Indiana',
'IA' => 'Iowa',
'KS' => 'Kansas',
'KY' => 'Kentucky',
'LA' => 'Louisiana',
'ME' => 'Maine',
'MD' => 'Maryland',
'MA' => 'Massachusetts',
'MI' => 'Michigan',
'MN' => 'Minnesota',
'MS' => 'Mississippi',
'MO' => 'Missouri',
'MT' => 'Montana',
'NE' => 'Nebraska',
'NV' => 'Nevada',
'NH' => 'New Hampshire',
'NJ' => 'New Jersey',
'NM' => 'New Mexico',
'NY' => 'New York',
'NC' => 'North Carolina',
'ND' => 'North Dakota',
'OH' => 'Ohio',
'OK' => 'Oklahoma',
'OR' => 'Oregon',
'PA' => 'Pennsylvania',
'RI' => 'Rhode Island',
'SC' => 'South Carolina',
'SD' => 'South Dakota',
'TN' => 'Tennessee',
'TX' => 'Texas',
'UT' => 'Utah',
'VT' => 'Vermont',
'VA' => 'Virginia',
'WA' => 'Washington',
'WV' => 'West Virginia',
'WI' => 'Wisconsin',
'WY' => 'Wyoming',
];
}

function trufield_get_required_fields( int $phase ): array {
$required = [
1 => [
'retailer_name',
'retailer_key_contact',
'retailer_contact_phone',
'retailer_address',
'retailer_city',
'phase_1_state_region',
],
2 => [
'phase_2_rsm_visit_1_date',
'phase_2_rsm_visit_1_upload_photos',
'phase_2_rsm_visit_2_date',
'phase_2_rsm_visit_2_upload_photos',
'phase_2_stand_count_1_treated',
'phase_2_stand_count_2_treated',
'phase_2_stand_count_3_treated',
'phase_2_stand_count_1_untreated',
'phase_2_stand_count_2_untreated',
'phase_2_stand_count_3_untreated',
'phase_2_grower_retailer_testimonials',
'phase_2_grower_retailer_comments',
],
3 => [],
];

return $required[ $phase ] ?? [];
}

function trufield_get_phase_1_eligibility_fields(): array {
	return [
		'retailer_name',
		'retailer_key_contact',
		'retailer_contact_phone',
		'retailer_address',
		'retailer_city',
		'phase_1_state_region',
		'retailer_branch_location',
		'field_trial_contact',
		'contact_phone',
		'field_trial_contact_email',
		'field_location_lat',
		'field_location_lng',
		'phase_1_treated_size_acres',
		'phase_1_application_rate',
		'phase_1_trial_type',
		'phase_1_protocol_version',
		'phase_1_application_timing',
		'phase_1_application_date',
		'phase_1_retailer_training_discussion_date',
	];
}

function trufield_phase_validates_from_full_form( int $phase ): bool {
	return false;
}

function trufield_get_validation_fields( int $phase ): array {
	if ( 1 === $phase ) {
		return trufield_get_phase_1_eligibility_fields();
	}

	if ( ! trufield_phase_validates_from_full_form( $phase ) ) {
		return trufield_get_required_fields( $phase );
	}

	return array_values(
		array_filter(
			trufield_rep_editable_phase_fields( $phase ),
			static function ( string $field ): bool {
					return ! in_array( $field, [ 'field_location_manual_override', 'farm_name', 'field_name' ], true );
			}
		)
	);
}

function trufield_field_labels(): array {
return [
'rsm_bam'                              => 'RSM/BAM',
'fsa'                                  => 'FSA',
'retailer_name'                       => 'Retailer Name',
'retailer_branch_location'            => 'Retailer Branch Location',
'retailer_key_contact'                => 'Retailer Contact',
'retailer_contact_phone'              => 'Retailer Contact Number',
'retailer_address'                    => 'Retailer Address',
'retailer_city'                       => 'City',
'farm_name'                           => 'Farm Name',
'field_trial_contact'                 => 'Crop Specialist Contact (First Last)',
'contact_phone'                       => 'Crop Specialist Contact Phone Number',
'field_trial_contact_email'           => 'Field Trial Contact Email',
'field_name'                          => 'Field Name',
'field_location_address'              => 'Field Location Address',
'field_location_lat'                  => 'Field Trial Latitude',
'field_location_lng'                  => 'Field Trial Longitude',
'field_location_manual_override'      => 'Address unavailable - manual coordinate override',
'phase_1_state_region'                => 'State',
'phase_1_product_being_tested'        => 'Product Tested',
'phase_1_application_type'            => 'Application Type',
'phase_1_application_date'            => 'Application Date',
'phase_1_application_rate'            => 'Applied Rate (Oz)',
'phase_1_trial_design'                => 'Trial Design',
'phase_1_growth_stage_at_application' => 'Growth Stage at Application',
'phase_1_weather_conditions_at_application' => 'Weather Conditions at Application',
'phase_1_soil_conditions_at_application' => 'Soil Conditions at Application',
'phase_1_field_overview_photo'        => 'Field Overview Photo',
'phase_1_trial_type'                  => 'Trial Type',
'phase_1_treated_size_acres'          => 'Treated Size (Acres)',
'phase_1_carrier_volume_gal'          => 'Carrier Volume (Gal)',
'phase_1_protocol_version'            => 'Protocol Version',
'phase_1_application_timing'          => 'Application Timing',
'phase_1_retailer_training_discussion_date' => 'Product Training Date',
'phase_1_hybrid_variety'              => 'Hybrid Variety',
'phase_1_planting_date'               => 'Planting Date',
'phase_1_planting_population'         => 'Planting Population',
'phase_1_row_spacing'                 => 'Row Spacing',
'phase_1_planting_speed'              => 'Planting Speed',
'phase_2_rsm_visit_1_date'            => 'RSM Visit Date 1',
'phase_2_rsm_visit_1_upload_photos'   => 'RSM Visit 1 Date Photos Taken Treated/Untreated',
'phase_2_rsm_visit_1_photo_type'      => 'RSM Visit Date 1 Photo Type',
'phase_2_rsm_visit_2_date'            => 'RSM Visit Date 2',
'phase_2_rsm_visit_2_upload_photos'   => 'RSM Visit 2 Date Photos Taken Treated/Untreated',
'phase_2_rsm_visit_2_photo_type'      => 'RSM Visit Date 2 Photo Type',
'phase_2_rsm_visit_3_date'            => 'Optional Visit Date 3',
'phase_2_rsm_visit_3_upload_photos'   => 'Optional Visit 3 Upload Photos Treated/Untreated',
'phase_2_rsm_visit_3_photo_type'      => 'RSM Visit Date 3 Photo Type',
'phase_2_rsm_visit_3_comments'        => 'RSM Visit Date 3 Comments',
'phase_2_rsm_visit_4_date'            => 'Optional Visit Date 4',
'phase_2_rsm_visit_4_upload_photos'   => 'Optional Visit 4 Upload Photos Treated/Untreated',
'phase_2_rsm_visit_4_photo_type'      => 'RSM Visit Date 4 Photo Type',
'phase_2_rsm_visit_4_comments'        => 'RSM Visit Date 4 Comments',
'phase_2_residue_degradation_observed'=> 'Residue Degradation Observed',
'phase_2_emergence_stand_collected'   => 'Emergence, Stand Collected',
'phase_2_stand_count_1_treated'       => 'Stand Count 1 TREATED',
'phase_2_stand_count_2_treated'       => 'Stand Count 2 TREATED',
'phase_2_stand_count_3_treated'       => 'Stand Count 3 TREATED',
'phase_2_stand_count_1_untreated'     => 'Stand Count 1 UNTREATED',
'phase_2_stand_count_2_untreated'     => 'Stand Count 2 UNTREATED',
'phase_2_stand_count_3_untreated'     => 'Stand Count 3 UNTREATED',
'phase_2_stand_count_data'            => 'Stand Count Deltas',
'phase_2_most_significant_visual_difference' => 'Most Significant Visual Difference',
'phase_2_emergence_flag_test'         => 'Emergence (Flag Test) (Y/N)',
'phase_2_pictures_at_application'     => 'Pictures at Application (Y/N)',
'phase_2_pictures_at_application_upload' => 'Pictures at Application Upload',
'phase_2_pictures_at_planting'        => 'Pictures at Planting (Y/N)',
'phase_2_pictures_at_planting_upload' => 'Pictures at Planting Upload',
'phase_2_pictures_in_season_harvest'  => 'Pictures In Season (Y/N)',
'phase_2_pictures_in_season_harvest_upload' => 'Pictures In Season/Harvest Upload',
'phase_2_pictures_at_harvest'         => 'Pictures at Harvest (Y/N)',
'phase_2_drone_images_available'      => 'Drone Images Available (Y/N)',
'phase_2_drone_images_available_upload' => 'Drone Images Upload',
'phase_2_grower_retailer_testimonials'=> 'Grower / Retailer Testimonials (Y/N)',
'phase_2_time_lapse_available'        => 'Time Lapse Available (Y/N)',
'phase_2_grower_retailer_comments'    => 'Grower / Retailer Comments',
'phase_3_event_date'                  => 'TruField In Person Workshop/Demo Day Date Held',
'phase_3_event_type'                  => 'TruField In Person Workshop/Demo Day (Yes or No)',
'phase_3_event_location'              => 'TruField In Person Workshop/Demo Day Location',
'phase_3_attendee_count'              => 'TruField In Person Workshop/Demo Day Number of Attendees',
'phase_3_tillage_type'                => 'Tillage Type',
'phase_3_soil_temp_f_at_application'  => 'Soil Temp (F) at application',
'phase_3_carrier_volume_gal'          => 'Carrier Volume (Gal)',
'phase_3_tank_mix_partners'           => 'Tank Mix Partners',
'phase_3_planting_date'               => 'Planting Date',
'phase_3_hybrid_variety'              => 'Hybrid/Variety',
'phase_3_planting_population'         => 'Planting Population',
'phase_3_row_spacing_in'              => 'Row Spacing (in)',
'phase_3_planting_speed_mph'          => 'Planting Speed (mph)',
'phase_3_plant_heights_avg_untreated_v7_in' => 'Plant Heights Avg Untreated @ V7 (In)',
'phase_3_plant_heights_avg_treated_v7_in'   => 'Plant Heights Avg Treated @ V7 (In)',
'phase_3_stalk_diameter_untreated_v7_mm'    => 'Stalk Diameter Untreated @ V7 (mm)',
'phase_3_stalk_diameter_treated_v7_mm2'     => 'Stalk Diameter Treated @ V7 (mm)2',
'phase_3_yield_untreated_bu_ac'       => 'Yield Untreated (bu/ac)',
'phase_3_yield_treated_bu_ac'         => 'Yield Treated (bu/ac)',
'phase_3_moisture_untreated_percent'  => 'Moisture Untreated (%)',
'phase_3_moisture_treated_percent'    => 'Moisture Treated (%)',
'phase_3_test_weight_untreated_lbs_bu' => 'Test Weight Untreated (lb/bu)',
'phase_3_test_weight_treated_lbs_bu'   => 'Test Weight Treated (lb/bu)',
'phase_3_as_applied_gis_data'         => 'As Applied GIS Data (Y/N)',
'phase_3_planting_gis_data'           => 'Planting GIS Data (Y/N)',
'phase_3_harvest_gis_data'            => 'Harvest GIS Data (Y/N)',
'phase_3_agronomy_comments'           => 'Agronomy Comments',
];
}

function trufield_get_retailer_assignment_context( int $post_id ): array {
	$assigned_rep_id = (int) get_post_meta( $post_id, 'assigned_sales_rep', true );
	$assignment_label = '';

	if ( $assigned_rep_id > 0 ) {
		$user = get_userdata( $assigned_rep_id );
		if ( $user instanceof WP_User ) {
			$assignment_label = trim( (string) $user->display_name );
		}
	}

	if ( '' === $assignment_label ) {
		$assignment_label = trufield_resolve_assignment_user_label( get_post_meta( $post_id, 'rsm_bam', true ) );
	}

	return [
		'id'    => $assigned_rep_id > 0 ? (string) $assigned_rep_id : '',
		'label' => $assignment_label,
	];
}

function trufield_pick_first_non_empty_meta_value( int $post_id, array $meta_keys, string $fallback = '' ): string {
	foreach ( $meta_keys as $meta_key ) {
		$value = trim( (string) get_post_meta( $post_id, $meta_key, true ) );
		if ( '' !== $value ) {
			return $value;
		}
	}

	return trim( $fallback );
}

function trufield_normalize_retailer_branch_location( string $retailer_name, string $branch_location ): string {
	$normalized = trim( $branch_location );
	if ( '' === $normalized ) {
		return '';
	}

	$corrections = [
		'ag partners' => [
			'q' => 'Hiawatha',
		],
	];

	$retailer_key = strtolower( trim( $retailer_name ) );
	$branch_key   = strtolower( $normalized );

	if ( isset( $corrections[ $retailer_key ][ $branch_key ] ) ) {
		return $corrections[ $retailer_key ][ $branch_key ];
	}

	return $normalized;
}

function trufield_retailer_directory_option_key(): string {
	return 'trufield_retailer_directory';
}

function trufield_sanitize_retailer_directory_entries( $raw_entries ): array {
	$entries = is_array( $raw_entries ) ? $raw_entries : [];
	$states  = trufield_state_region_options();
	$sanitized = [];

	foreach ( $entries as $entry ) {
		if ( ! is_array( $entry ) ) {
			continue;
		}

		$name = trim( sanitize_text_field( (string) ( $entry['name'] ?? $entry['retailer_name'] ?? '' ) ) );
		if ( '' === $name ) {
			continue;
		}

		$branch_location = trim( sanitize_text_field( (string) ( $entry['retailer_branch_location'] ?? $entry['location'] ?? '' ) ) );
		$contact_name  = trim( sanitize_text_field( (string) ( $entry['retailer_key_contact'] ?? '' ) ) );
		$contact_phone = trufield_normalize_phone_value( (string) ( $entry['retailer_contact_phone'] ?? '' ) );
		$address       = trim( sanitize_text_field( (string) ( $entry['retailer_address'] ?? $entry['address'] ?? '' ) ) );
		$city          = trim( sanitize_text_field( (string) ( $entry['retailer_city'] ?? $entry['city'] ?? '' ) ) );
		$state         = trim( sanitize_text_field( (string) ( $entry['phase_1_state_region'] ?? '' ) ) );
		$rsm_bam       = trim( sanitize_text_field( (string) ( $entry['rsm_bam'] ?? '' ) ) );

		if ( '' !== $state && ! isset( $states[ $state ] ) ) {
			$state = '';
		}

		$sanitized[ $name ] = [
			'name'                     => $name,
			'retailer_branch_location' => $branch_location,
			'retailer_key_contact'     => $contact_name,
			'retailer_contact_phone'   => $contact_phone,
			'retailer_address'         => $address,
			'retailer_city'            => $city,
			'phase_1_state_region'     => $state,
			'rsm_bam'                  => $rsm_bam,
			'assignment_ids'           => [],
			'assignment_labels'        => [],
		];
	}

	ksort( $sanitized, SORT_NATURAL | SORT_FLAG_CASE );

	return $sanitized;
}

function trufield_build_retailer_directory_from_posts(): array {
	$directory = [];
	$post_ids  = get_posts(
		[
			'post_type'              => 'plant_field',
			'post_status'            => [ 'publish', 'pending', 'draft', 'private', 'future' ],
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
			'meta_query'             => [
				[
					'key'     => 'retailer_name',
					'value'   => '',
					'compare' => '!=',
				],
			],
		]
	);

	foreach ( $post_ids as $post_id ) {
		$retailer_name = trim( (string) get_post_meta( $post_id, 'retailer_name', true ) );
		if ( '' === $retailer_name ) {
			continue;
		}

		if ( ! isset( $directory[ $retailer_name ] ) ) {
			$directory[ $retailer_name ] = [
				'name'                     => $retailer_name,
				'retailer_branch_location' => '',
				'retailer_key_contact'     => '',
				'retailer_contact_phone'   => '',
				'retailer_address'         => '',
				'retailer_city'            => '',
				'phase_1_state_region'     => '',
				'rsm_bam'                  => '',
				'assignment_ids'           => [],
				'assignment_labels'        => [],
			];
		}

		$entry =& $directory[ $retailer_name ];

		if ( '' === $entry['retailer_key_contact'] ) {
			$entry['retailer_key_contact'] = trufield_pick_first_non_empty_meta_value( $post_id, [ 'retailer_key_contact', 'field_trial_contact' ] );
		}

		if ( '' === $entry['retailer_contact_phone'] ) {
			$entry['retailer_contact_phone'] = trufield_pick_first_non_empty_meta_value( $post_id, [ 'retailer_contact_phone', 'contact_phone' ] );
		}

		if ( '' === $entry['retailer_address'] ) {
			$entry['retailer_address'] = trufield_pick_first_non_empty_meta_value( $post_id, [ 'retailer_address', 'field_location_address' ] );
		}

		if ( '' === $entry['retailer_city'] ) {
			$entry['retailer_city'] = trufield_pick_first_non_empty_meta_value( $post_id, [ 'retailer_city', 'import_city' ] );
		}

		if ( '' === $entry['phase_1_state_region'] ) {
			$entry['phase_1_state_region'] = trufield_pick_first_non_empty_meta_value( $post_id, [ 'phase_1_state_region', 'import_state' ] );
		}

		unset( $entry );
	}

	ksort( $directory, SORT_NATURAL | SORT_FLAG_CASE );

	return $directory;
}

function trufield_retailer_directory_workbook_path(): string {
	$base_path = trailingslashit( ABSPATH );
	$matches   = glob( $base_path . 'RETAILER LIST UPDATED*.xlsx' );

	if ( is_array( $matches ) && [] !== $matches ) {
		usort(
			$matches,
			static function ( string $left, string $right ): int {
				return filemtime( $right ) <=> filemtime( $left );
			}
		);

		return (string) $matches[0];
	}

	return $base_path . 'retailerlist for autofil.xlsx';
}

function trufield_load_retailer_directory_from_workbook() {
	$workbook_path = trufield_retailer_directory_workbook_path();
	if ( ! file_exists( $workbook_path ) ) {
		return new WP_Error( 'trufield_retailer_directory_workbook_missing', __( 'The retailer workbook could not be found.', 'trufield-portal' ) );
	}

	$rows = trufield_parse_xlsx_rows( $workbook_path );
	if ( is_wp_error( $rows ) ) {
		return $rows;
	}

	if ( ! is_array( $rows ) ) {
		return new WP_Error( 'trufield_retailer_directory_workbook_invalid', __( 'The retailer workbook could not be read.', 'trufield-portal' ) );
	}

	return $rows;
}

function trufield_build_retailer_directory_entries_from_rows( array $rows ): array {
	$entries = [];
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$name = trim( (string) ( $row['Retailer'] ?? $row['Retailer '] ?? '' ) );
		if ( '' === $name ) {
			continue;
		}

		if ( ! isset( $entries[ $name ] ) ) {
			$entries[ $name ] = [
				'name'                     => $name,
				'retailer_branch_location' => trim( (string) ( $row['Location'] ?? '' ) ),
				'retailer_key_contact'     => trim( (string) ( $row['Key Contact'] ?? '' ) ),
				'retailer_contact_phone'   => trim( (string) ( $row['Contact Number'] ?? '' ) ),
				'retailer_address'         => trim( (string) ( $row['Address'] ?? '' ) ),
				'retailer_city'            => trim( (string) ( $row['City'] ?? '' ) ),
				'phase_1_state_region'     => trim( (string) ( $row['State'] ?? '' ) ),
				'rsm_bam'                  => trim( (string) ( $row['RSM/BAM'] ?? '' ) ),
			];
			continue;
		}

		foreach ( [
			'retailer_branch_location' => 'Location',
			'retailer_key_contact'     => 'Key Contact',
			'retailer_contact_phone'   => 'Contact Number',
			'retailer_address'         => 'Address',
			'retailer_city'            => 'City',
			'phase_1_state_region'     => 'State',
			'rsm_bam'                  => 'RSM/BAM',
		] as $entry_key => $column_name ) {
			if ( '' === trim( (string) ( $entries[ $name ][ $entry_key ] ?? '' ) ) ) {
				$entries[ $name ][ $entry_key ] = trim( (string) ( $row[ $column_name ] ?? '' ) );
			}
		}
	}

	return trufield_sanitize_retailer_directory_entries( array_values( $entries ) );
}

function trufield_build_retailer_directory_from_workbook(): array {
	$rows = trufield_load_retailer_directory_from_workbook();
	if ( is_wp_error( $rows ) ) {
		return [];
	}

	return trufield_build_retailer_directory_entries_from_rows( $rows );
}

function trufield_get_retailer_directory(): array {
	static $directory = null;

	if ( is_array( $directory ) ) {
		return $directory;
	}
	$directory = trufield_sanitize_retailer_directory_entries( get_option( trufield_retailer_directory_option_key(), [] ) );

	return $directory;
}

function trufield_get_retailer_name_options( int $post_id = 0 ): array {
	$directory = trufield_get_retailer_directory();
	$options   = [];

	foreach ( $directory as $retailer_name => $entry ) {
		$options[ $retailer_name ] = $retailer_name;
	}

	return $options;
}

function trufield_upsert_retailer_directory_entry( string $retailer_name, array $entry = [] ): string {
	$retailer_name = trim( sanitize_text_field( $retailer_name ) );
	if ( '' === $retailer_name ) {
		return '';
	}

	$directory = trufield_get_retailer_directory();
	$current   = isset( $directory[ $retailer_name ] ) && is_array( $directory[ $retailer_name ] ) ? $directory[ $retailer_name ] : [];
	$directory[ $retailer_name ] = array_merge(
		[
			'name'                     => $retailer_name,
			'retailer_branch_location' => '',
			'retailer_key_contact'     => '',
			'retailer_contact_phone'   => '',
			'retailer_address'         => '',
			'retailer_city'            => '',
			'phase_1_state_region'     => '',
			'rsm_bam'                  => '',
		],
		$current,
		$entry,
		[
			'name' => $retailer_name,
		]
	);

	$sanitized_directory = trufield_sanitize_retailer_directory_entries( array_values( $directory ) );
	update_option( trufield_retailer_directory_option_key(), array_values( $sanitized_directory ), false );

	return $retailer_name;
}

function trufield_assignment_user_roles_for_field( string $field ): array {
	$map = [
		'rsm_bam' => [ 'sales_rep', 'administrator' ],
		'fsa'     => [ 'fsa', 'administrator' ],
	];

	$roles = $map[ $field ] ?? [];

	return array_values(
		array_filter(
			apply_filters( 'trufield_assignment_user_roles', $roles, $field ),
			static fn( $role ): bool => is_string( $role ) && '' !== trim( $role )
		)
	);
}

function trufield_normalize_assignment_user_lookup_value( string $value ): string {
	$value = trim( sanitize_text_field( $value ) );

	return '' === $value ? '' : sanitize_title( remove_accents( $value ) );
}

function trufield_assignment_user_person_name_tokens( string $value ): array {
	$normalized = trufield_normalize_assignment_user_lookup_value( $value );

	if ( '' === $normalized ) {
		return [];
	}

	return array_values( array_filter( explode( '-', $normalized ) ) );
}

function trufield_assignment_person_name_matches( string $name, WP_User $user ): bool {
	$target_tokens = trufield_assignment_user_person_name_tokens( $name );
	if ( count( $target_tokens ) < 2 ) {
		return false;
	}

	$target_first = reset( $target_tokens );
	$target_last  = end( $target_tokens );
	$candidates   = [
		(string) $user->display_name,
		trim( (string) get_user_meta( $user->ID, 'first_name', true ) . ' ' . (string) get_user_meta( $user->ID, 'last_name', true ) ),
		trim( (string) get_user_meta( $user->ID, 'last_name', true ) . ' ' . (string) get_user_meta( $user->ID, 'first_name', true ) ),
	];

	foreach ( $candidates as $candidate ) {
		$candidate_tokens = trufield_assignment_user_person_name_tokens( $candidate );
		if ( count( $candidate_tokens ) < 2 ) {
			continue;
		}

		$candidate_first = reset( $candidate_tokens );
		$candidate_last  = end( $candidate_tokens );
		if ( $candidate_last !== $target_last ) {
			continue;
		}

		if ( $candidate_first === $target_first || str_starts_with( $candidate_first, $target_first ) || str_starts_with( $target_first, $candidate_first ) ) {
			return true;
		}
	}

	return false;
}

function trufield_assignment_user_lookup_values( WP_User $user ): array {
	$values = [
		(string) $user->display_name,
		(string) $user->user_login,
		(string) $user->user_nicename,
	];
	$first_name = trim( (string) get_user_meta( $user->ID, 'first_name', true ) );
	$last_name  = trim( (string) get_user_meta( $user->ID, 'last_name', true ) );

	if ( '' !== $first_name || '' !== $last_name ) {
		$values[] = trim( $first_name . ' ' . $last_name );
		$values[] = trim( $last_name . ' ' . $first_name );
	}

	$lookups = array_map( 'trufield_normalize_assignment_user_lookup_value', $values );
	$lookups = array_values( array_filter( array_unique( $lookups ) ) );

	return $lookups;
}

function trufield_assignment_user_has_role( WP_User $user, string $field ): bool {
	return [] !== array_intersect( trufield_assignment_user_roles_for_field( $field ), (array) $user->roles );
}

function trufield_assignment_dropdown_option_key( string $field ): string {
	return sprintf( 'trufield_assignment_dropdown_%s', sanitize_key( $field ) );
}

function trufield_default_assignment_dropdown_names( string $field ): array {
	if ( 'rsm_bam' === $field ) {
		return [
			'Anthony Finke',
			'Beau Matson',
			'Chris Person',
			'Chris Pevestorf',
			'Ethan Noll',
			'Jesse Wiant',
			'Joe Duck',
			'Lane Danielson',
			'Michael Edens',
			'Nick Thompson',
			'Peter White',
			'Quintin Leffel',
			'Tim Robie',
			'Zach Ekeler',
			'Zach Minnihan',
		];
	}

	if ( 'fsa' === $field ) {
		return [
			'Chad Becker',
			'Keith Byerly',
			'Kip Jacobs',
			'Roland Leatherwood',
			'Tryston Beyrer',
		];
	}

	return [];
}

function trufield_sanitize_assignment_dropdown_names( $raw_value, string $field ): array {
	$values = is_array( $raw_value ) ? $raw_value : preg_split( '/\r\n|\r|\n/', (string) $raw_value );
	$values = is_array( $values ) ? $values : [];
	$names  = [];

	foreach ( $values as $value ) {
		$name = trim( sanitize_text_field( (string) $value ) );
		if ( '' === $name ) {
			continue;
		}

		$names[ $name ] = $name;
	}

	if ( empty( $names ) ) {
		foreach ( trufield_default_assignment_dropdown_names( $field ) as $default_name ) {
			$names[ $default_name ] = $default_name;
		}
	}

	return array_values( $names );
}

function trufield_get_assignment_dropdown_names( string $field ): array {
	$stored = get_option( trufield_assignment_dropdown_option_key( $field ), [] );

	return trufield_sanitize_assignment_dropdown_names( $stored, $field );
}

function trufield_get_rsm_bam_display_names(): array {
	return trufield_get_assignment_dropdown_names( 'rsm_bam' );
}

function trufield_get_fsa_display_names(): array {
	return trufield_get_assignment_dropdown_names( 'fsa' );
}

function trufield_get_fsa_user_ids(): array {
	return array_map( 'intval', array_keys( trufield_get_assignment_user_options( 'fsa' ) ) );
}

function trufield_is_allowed_fsa_user_id( int $user_id ): bool {
	return $user_id > 0 && in_array( $user_id, trufield_get_fsa_user_ids(), true );
}

function trufield_get_rsm_bam_user_options(): array {
	static $options = null;

	if ( is_array( $options ) ) {
		return $options;
	}

	$allowed_names = trufield_get_rsm_bam_display_names();
	$users         = get_users(
		[
			'orderby' => 'display_name',
			'order'   => 'ASC',
			'fields'  => 'all',
		]
	);
	$users_by_name = [];

	foreach ( $users as $user ) {
		if ( ! $user instanceof WP_User ) {
			$user = get_userdata( (int) ( $user->ID ?? 0 ) );
		}

		if ( ! $user instanceof WP_User ) {
			continue;
		}

		foreach ( trufield_assignment_user_lookup_values( $user ) as $lookup_name ) {
			$existing_user = $users_by_name[ $lookup_name ] ?? null;

			if ( ! $existing_user instanceof WP_User ) {
				$users_by_name[ $lookup_name ] = $user;
				continue;
			}

			if ( ! trufield_assignment_user_has_role( $existing_user, 'rsm_bam' ) && trufield_assignment_user_has_role( $user, 'rsm_bam' ) ) {
				$users_by_name[ $lookup_name ] = $user;
			}
		}
	}

	$options = [];
	foreach ( $allowed_names as $name ) {
		$lookup_name = trufield_normalize_assignment_user_lookup_value( $name );
		$user        = $users_by_name[ $lookup_name ] ?? null;

		if ( ! $user instanceof WP_User ) {
			foreach ( $users as $candidate_user ) {
				if ( ! $candidate_user instanceof WP_User ) {
					$candidate_user = get_userdata( (int) ( $candidate_user->ID ?? 0 ) );
				}

				if ( ! $candidate_user instanceof WP_User || ! trufield_assignment_user_has_role( $candidate_user, 'rsm_bam' ) ) {
					continue;
				}

				if ( trufield_assignment_person_name_matches( $name, $candidate_user ) ) {
					$user = $candidate_user;
					break;
				}
			}
		}

		$user_id     = $user instanceof WP_User ? (int) $user->ID : 0;
		if ( $user_id <= 0 ) {
			continue;
		}

		$options[ $user_id ] = $name;
	}

	return $options;
}

function trufield_get_rsm_bam_user_ids(): array {
	return array_map( 'intval', array_keys( trufield_get_rsm_bam_user_options() ) );
}

function trufield_is_allowed_rsm_bam_user_id( int $user_id ): bool {
	return $user_id > 0 && in_array( $user_id, trufield_get_rsm_bam_user_ids(), true );
}

function trufield_get_assignment_user_options( string $field ): array {
	static $cache = [];

	if ( isset( $cache[ $field ] ) ) {
		return $cache[ $field ];
	}

	if ( 'rsm_bam' === $field ) {
		$cache[ $field ] = trufield_get_rsm_bam_user_options();

		return $cache[ $field ];
	}

	if ( 'fsa' === $field ) {
		$allowed_names = trufield_get_fsa_display_names();
		$users         = get_users(
			[
				'orderby' => 'display_name',
				'order'   => 'ASC',
				'fields'  => 'all',
			]
		);
		$users_by_name = [];

		foreach ( $users as $user ) {
			if ( ! $user instanceof WP_User ) {
				$user = get_userdata( (int) ( $user->ID ?? 0 ) );
			}

			if ( ! $user instanceof WP_User ) {
				continue;
			}

			foreach ( trufield_assignment_user_lookup_values( $user ) as $lookup_name ) {
				$existing_user = $users_by_name[ $lookup_name ] ?? null;

				if ( ! $existing_user instanceof WP_User ) {
					$users_by_name[ $lookup_name ] = $user;
					continue;
				}

				if ( ! trufield_assignment_user_has_role( $existing_user, 'fsa' ) && trufield_assignment_user_has_role( $user, 'fsa' ) ) {
					$users_by_name[ $lookup_name ] = $user;
				}
			}
		}

		$options = [];
		foreach ( $allowed_names as $name ) {
			$lookup_name = trufield_normalize_assignment_user_lookup_value( $name );
			$user        = $users_by_name[ $lookup_name ] ?? null;

			if ( ! $user instanceof WP_User ) {
				foreach ( $users as $candidate_user ) {
					if ( ! $candidate_user instanceof WP_User ) {
						$candidate_user = get_userdata( (int) ( $candidate_user->ID ?? 0 ) );
					}

					if ( ! $candidate_user instanceof WP_User ) {
						continue;
					}

					if ( trufield_assignment_person_name_matches( $name, $candidate_user ) ) {
						$user = $candidate_user;
						if ( trufield_assignment_user_has_role( $candidate_user, 'fsa' ) ) {
							break;
						}
					}
				}
			}

			$user_id = $user instanceof WP_User ? (int) $user->ID : 0;
			if ( $user_id <= 0 ) {
				continue;
			}

			$options[ $user_id ] = $name;
		}

		$cache[ $field ] = $options;

		return $cache[ $field ];
	}

	$options = [];
	$seen    = [];

	foreach ( trufield_assignment_user_roles_for_field( $field ) as $role ) {
		$users = get_users(
			[
				'role'    => $role,
				'orderby' => 'display_name',
				'order'   => 'ASC',
				'fields'  => [ 'ID', 'display_name' ],
			]
		);

		foreach ( $users as $user ) {
			$user_id = (int) $user->ID;
			if ( $user_id <= 0 || isset( $seen[ $user_id ] ) ) {
				continue;
			}

			$seen[ $user_id ]    = true;
			$options[ $user_id ] = $user->display_name;
		}
	}

	$cache[ $field ] = $options;

	return $options;
}

function trufield_get_assignment_user_id_by_name( string $field, string $name ): int {
	$name = trim( sanitize_text_field( $name ) );
	if ( '' === $name ) {
		return 0;
	}

	foreach ( trufield_get_assignment_user_options( $field ) as $user_id => $display_name ) {
		if ( 0 === strcasecmp( $name, (string) $display_name ) ) {
			return (int) $user_id;
		}
	}

	return 0;
}

function trufield_resolve_assignment_user_id( $value, string $field ): int {
	if ( is_numeric( $value ) ) {
		$user_id = absint( (string) $value );
		$options = trufield_get_assignment_user_options( $field );

		return isset( $options[ $user_id ] ) ? $user_id : 0;
	}

	return trufield_get_assignment_user_id_by_name( $field, (string) $value );
}

function trufield_resolve_assignment_user_label( $value ): string {
	if ( is_numeric( $value ) ) {
		$user = get_userdata( (int) $value );
		if ( $user ) {
			return (string) $user->display_name;
		}
	}

	return trim( sanitize_text_field( (string) $value ) );
}

function trufield_get_assigned_sales_rep_id( int $post_id ): int {
	$assigned = trufield_resolve_assignment_user_id( get_post_meta( $post_id, 'assigned_sales_rep', true ), 'rsm_bam' );
	if ( $assigned > 0 ) {
		return $assigned;
	}

	$rsm_bam = trufield_resolve_assignment_user_id( get_post_meta( $post_id, 'rsm_bam', true ), 'rsm_bam' );
	if ( $rsm_bam > 0 ) {
		return $rsm_bam;
	}

	$post = get_post( $post_id );
	if ( $post && trufield_is_allowed_rsm_bam_user_id( (int) $post->post_author ) ) {
		return (int) $post->post_author;
	}

	return 0;
}

function trufield_phase_field_schema(): array {
return [
'rsm_bam' => [ 'type' => 'user' ],
'fsa' => [ 'type' => 'user' ],
'field_location_address' => [ 'type' => 'text' ],
'field_location_lat' => [ 'type' => 'number' ],
'field_location_lng' => [ 'type' => 'number' ],
'field_location_manual_override' => [ 'type' => 'boolean' ],
'retailer_branch_location' => [ 'type' => 'text' ],
'retailer_contact_phone' => [ 'type' => 'phone' ],
'retailer_address' => [ 'type' => 'text' ],
'retailer_city' => [ 'type' => 'text' ],
'contact_phone' => [ 'type' => 'phone' ],
'field_trial_contact_email' => [ 'type' => 'email' ],
'phase_1_state_region' => [
'type'    => 'select',
'options' => trufield_state_region_options(),
],
'phase_1_product_being_tested' => [
'type'    => 'select',
'options' => trufield_get_product_tested_choices(),
],
'phase_1_application_type' => [
'type'    => 'select',
'options' => [
'in_furrow'      => 'In-Furrow',
'seed_treatment' => 'Seed Treatment',
'foliar'         => 'Foliar',
'other'          => 'Other',
],
],
'phase_1_application_date' => [ 'type' => 'date' ],
'phase_1_application_rate' => [ 'type' => 'text' ],
'phase_1_trial_design' => [
'type'    => 'select',
'options' => [
'strip'        => 'Strip',
'side_by_side' => 'Side-by-Side',
'demo'         => 'Demo',
],
],
'phase_1_growth_stage_at_application' => [ 'type' => 'text' ],
'phase_1_weather_conditions_at_application' => [ 'type' => 'textarea' ],
'phase_1_soil_conditions_at_application' => [ 'type' => 'textarea' ],
'phase_1_field_overview_photo' => [ 'type' => 'url' ],
'phase_1_trial_type' => [
'type'    => 'select',
'options' => [
'full_field'   => 'Full Field',
'side_by_side' => 'Side by Side',
],
],
'phase_1_treated_size_acres' => [ 'type' => 'number' ],
'phase_1_carrier_volume_gal' => [ 'type' => 'number' ],
'phase_1_protocol_version' => [
'type'    => 'select',
'options' => [
'corn_residue_spring'         => 'Corn Residue Spring',
'corn_residue_fall'           => 'Corn Residue Fall',
'corn_residue_preplant_soy'   => 'Corn Residue Pre-Plant Soy',
'wheat_residue_preplant_soy'  => 'Wheat Residue Pre-Plant Soy',
'soy_residue_spring'          => 'Soy Residue Spring',
'soybeans_double_crop'        => 'Soybeans Double Crop',
'other'                       => 'Other',
],
],
'phase_1_application_timing' => [
'type'    => 'select',
'options' => [
'spring_2026' => 'Spring 2026',
'fall_2026'   => 'Fall 2026',
],
],
'phase_1_retailer_training_discussion_date' => [ 'type' => 'date' ],
'phase_1_hybrid_variety' => [ 'type' => 'text' ],
'phase_1_planting_date' => [ 'type' => 'date' ],
'phase_1_planting_population' => [ 'type' => 'integer' ],
'phase_1_row_spacing' => [ 'type' => 'number' ],
'phase_1_planting_speed' => [ 'type' => 'number' ],
'phase_2_rsm_visit_1_date' => [ 'type' => 'date' ],
'phase_2_rsm_visit_1_upload_photos' => [ 'type' => 'url' ],
'phase_2_rsm_visit_1_photo_type' => [
'type'    => 'select',
'options' => [
'treated'        => 'Treated',
'untreated'      => 'Untreated',
'both'           => 'Both',
'at_application' => 'At Application',
'at_planting'    => 'At Planting',
'in_season'      => 'In Season',
'pre_harvest'    => 'Pre-Harvest',
'drone'          => 'Drone',
],
],
'phase_2_rsm_visit_2_date' => [ 'type' => 'date' ],
'phase_2_rsm_visit_2_upload_photos' => [ 'type' => 'url' ],
'phase_2_rsm_visit_2_photo_type' => [
'type'    => 'select',
'options' => [
'treated'        => 'Treated',
'untreated'      => 'Untreated',
'both'           => 'Both',
'at_application' => 'At Application',
'at_planting'    => 'At Planting',
'in_season'      => 'In Season',
'pre_harvest'    => 'Pre-Harvest',
'drone'          => 'Drone',
],
],
'phase_2_rsm_visit_3_date' => [ 'type' => 'date' ],
'phase_2_rsm_visit_3_upload_photos' => [ 'type' => 'url' ],
'phase_2_rsm_visit_3_photo_type' => [
'type'    => 'select',
'options' => [
'treated'        => 'Treated',
'untreated'      => 'Untreated',
'both'           => 'Both',
'at_application' => 'At Application',
'at_planting'    => 'At Planting',
'in_season'      => 'In Season',
'pre_harvest'    => 'Pre-Harvest',
'drone'          => 'Drone',
],
],
'phase_2_rsm_visit_3_comments' => [ 'type' => 'textarea' ],
'phase_2_rsm_visit_4_date' => [ 'type' => 'date' ],
'phase_2_rsm_visit_4_upload_photos' => [ 'type' => 'url' ],
'phase_2_rsm_visit_4_photo_type' => [
'type'    => 'select',
'options' => [
'treated'        => 'Treated',
'untreated'      => 'Untreated',
'both'           => 'Both',
'at_application' => 'At Application',
'at_planting'    => 'At Planting',
'in_season'      => 'In Season',
'pre_harvest'    => 'Pre-Harvest',
'drone'          => 'Drone',
],
],
'phase_2_rsm_visit_4_comments' => [ 'type' => 'textarea' ],
'phase_2_residue_degradation_observed' => [
'type'    => 'select',
'options' => [
'yes' => 'Yes',
'no'  => 'No',
],
],
'phase_2_emergence_stand_collected' => [
'type'    => 'select',
'options' => [
'yes' => 'Yes',
'no'  => 'No',
],
],
'phase_2_stand_count_data' => [ 'type' => 'text' ],
'phase_2_stand_count_1_treated' => [ 'type' => 'number' ],
'phase_2_stand_count_2_treated' => [ 'type' => 'number' ],
'phase_2_stand_count_3_treated' => [ 'type' => 'number' ],
'phase_2_stand_count_1_untreated' => [ 'type' => 'number' ],
'phase_2_stand_count_2_untreated' => [ 'type' => 'number' ],
'phase_2_stand_count_3_untreated' => [ 'type' => 'number' ],
'phase_2_most_significant_visual_difference' => [ 'type' => 'textarea' ],
'phase_2_emergence_flag_test' => [
'type'    => 'select',
'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
],
'phase_2_pictures_at_application' => [
'type'    => 'select',
'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
],
'phase_2_pictures_at_application_upload' => [ 'type' => 'url' ],
'phase_2_pictures_at_planting' => [
'type'    => 'select',
'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
],
'phase_2_pictures_at_planting_upload' => [ 'type' => 'url' ],
'phase_2_pictures_in_season_harvest' => [
'type'    => 'select',
'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
],
'phase_2_pictures_in_season_harvest_upload' => [ 'type' => 'url' ],
'phase_2_pictures_at_harvest' => [
'type'    => 'select',
'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
],
'phase_2_drone_images_available' => [
'type'    => 'select',
'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
],
'phase_2_drone_images_available_upload' => [ 'type' => 'url' ],
'phase_2_grower_retailer_testimonials' => [
'type'    => 'select',
'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
],
'phase_2_time_lapse_available' => [
'type'    => 'select',
'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
],
'phase_2_grower_retailer_comments' => [ 'type' => 'textarea' ],
'phase_3_event_date' => [ 'type' => 'date' ],
'phase_3_event_type' => [
'type'    => 'select',
'options' => [
'yes' => 'Yes',
'no'  => 'No',
],
],
'phase_3_event_location' => [ 'type' => 'text' ],
'phase_3_attendee_count' => [ 'type' => 'integer' ],
'phase_3_tillage_type' => [ 'type' => 'text' ],
'phase_3_soil_temp_f_at_application' => [ 'type' => 'number' ],
'phase_3_carrier_volume_gal' => [ 'type' => 'number' ],
'phase_3_tank_mix_partners' => [ 'type' => 'textarea' ],
'phase_3_planting_date' => [ 'type' => 'date' ],
'phase_3_hybrid_variety' => [ 'type' => 'text' ],
'phase_3_planting_population' => [ 'type' => 'integer' ],
'phase_3_row_spacing_in' => [ 'type' => 'number' ],
'phase_3_planting_speed_mph' => [ 'type' => 'number' ],
'phase_3_plant_heights_avg_untreated_v7_in' => [ 'type' => 'number' ],
'phase_3_plant_heights_avg_treated_v7_in' => [ 'type' => 'number' ],
'phase_3_stalk_diameter_untreated_v7_mm' => [ 'type' => 'number' ],
'phase_3_stalk_diameter_treated_v7_mm2' => [ 'type' => 'number' ],
'phase_3_yield_untreated_bu_ac' => [ 'type' => 'number' ],
'phase_3_yield_treated_bu_ac' => [ 'type' => 'number' ],
'phase_3_moisture_untreated_percent' => [ 'type' => 'number' ],
'phase_3_moisture_treated_percent' => [ 'type' => 'number' ],
'phase_3_test_weight_untreated_lbs_bu' => [ 'type' => 'number' ],
'phase_3_test_weight_treated_lbs_bu' => [ 'type' => 'number' ],
'phase_3_as_applied_gis_data' => [
'type'    => 'select',
'options' => [
'yes' => 'Yes',
'no'  => 'No',
],
],
'phase_3_planting_gis_data' => [
'type'    => 'select',
'options' => [
'yes' => 'Yes',
'no'  => 'No',
],
],
'phase_3_harvest_gis_data' => [
'type'    => 'select',
'options' => [
'yes' => 'Yes',
'no'  => 'No',
],
],
'phase_3_agronomy_comments' => [ 'type' => 'textarea' ],
];
}

function trufield_location_override_enabled( int $post_id ): bool {
	return (bool) get_post_meta( $post_id, 'field_location_manual_override', true );
}

function trufield_phase_3_workshop_requires_details( int $post_id ): bool {
	return 'yes' === strtolower( trim( (string) get_post_meta( $post_id, 'phase_3_event_type', true ) ) );
}

function trufield_get_missing_phase_field_keys( int $post_id, int $phase, array $fields ): array {
	$missing = [];

	foreach ( $fields as $field ) {
		if ( 3 === $phase && in_array( $field, [ 'phase_3_event_date', 'phase_3_event_location', 'phase_3_attendee_count' ], true ) && ! trufield_phase_3_workshop_requires_details( $post_id ) ) {
			continue;
		}

		$value = get_post_meta( $post_id, $field, true );
		if ( trim( (string) $value ) === '' ) {
			$missing[] = $field;
		}
	}

	return $missing;
}

function trufield_get_missing_required_field_keys( int $post_id, int $phase ): array {
	return trufield_get_missing_phase_field_keys( $post_id, $phase, trufield_get_required_fields( $phase ) );
}

function trufield_get_missing_validation_field_keys( int $post_id, int $phase ): array {
	return trufield_get_missing_phase_field_keys( $post_id, $phase, trufield_get_validation_fields( $phase ) );
}

function trufield_get_missing_required_fields( int $post_id, int $phase ): array {
	$labels  = trufield_field_labels();
	$missing = [];

	foreach ( trufield_get_missing_required_field_keys( $post_id, $phase ) as $field ) {
		$missing[] = $labels[ $field ] ?? $field;
	}

	return $missing;
}

function trufield_get_missing_validation_fields( int $post_id, int $phase ): array {
	$labels  = trufield_field_labels();
	$missing = [];

	foreach ( trufield_get_missing_validation_field_keys( $post_id, $phase ) as $field ) {
			$missing[] = $labels[ $field ] ?? $field;
	}

	return $missing;
}

function trufield_all_validation_fields_present( int $post_id, int $phase ): bool {
	return [] === trufield_get_missing_validation_fields( $post_id, $phase );
}

function trufield_all_required_fields_present( int $post_id, int $phase ): bool {
	return [] === trufield_get_missing_required_fields( $post_id, $phase );
}

function trufield_phase_auto_verifies( int $phase ): bool {
	return in_array( $phase, [ 1, 2 ], true );
}

function trufield_sync_phase_verification_state( int $post_id, int $phase ): array {
	$required_ok   = trufield_all_required_fields_present( $post_id, $phase );
	$validation_ok = trufield_all_validation_fields_present( $post_id, $phase );
	if ( 2 === $phase && function_exists( 'trufield_get_phase_2_trial_points' ) ) {
		$validation_ok = trufield_get_phase_2_trial_points( $post_id ) > 0;
	}

	$auto_verify   = trufield_phase_auto_verifies( $phase );
	$was_verified  = (bool) get_post_meta( $post_id, "phase_{$phase}_verified", true );
	$was_completed = trufield_get_phase_status( $post_id, $phase ) === 'completed';
	$just_verified = false;

	if ( ! $auto_verify ) {
		return [
			'auto_verify'   => false,
			'required_ok'   => $required_ok,
			'validation_ok' => $required_ok,
			'just_verified' => false,
			'is_verified'   => $was_verified,
			'is_completed'  => $was_completed,
		];
	}

	if ( $validation_ok ) {
		if ( ! $was_completed ) {
			update_post_meta( $post_id, "phase_{$phase}_status", 'completed' );
		}

		if ( '' === (string) get_post_meta( $post_id, "phase_{$phase}_completed_at", true ) ) {
			update_post_meta( $post_id, "phase_{$phase}_completed_at", current_time( 'mysql' ) );
		}

		if ( ! $was_verified ) {
			update_post_meta( $post_id, "phase_{$phase}_verified", 1 );
			update_post_meta( $post_id, "phase_{$phase}_verified_at", current_time( 'mysql' ) );
			$just_verified = true;
		}
	} else {
		if ( $was_verified ) {
			delete_post_meta( $post_id, "phase_{$phase}_verified" );
			delete_post_meta( $post_id, "phase_{$phase}_verified_at" );
		}

		if ( $was_completed ) {
			if ( 2 !== $phase ) {
				update_post_meta( $post_id, "phase_{$phase}_status", 'in_progress' );
				delete_post_meta( $post_id, "phase_{$phase}_completed_at" );
			}
		}
	}

	update_post_meta( $post_id, 'current_phase', min( 3, max( 1, $phase ) ) );

	return [
		'auto_verify'   => true,
		'required_ok'   => $required_ok,
		'validation_ok' => $validation_ok,
		'just_verified' => $just_verified,
		'is_verified'   => (bool) get_post_meta( $post_id, "phase_{$phase}_verified", true ),
		'is_completed'  => trufield_get_phase_status( $post_id, $phase ) === 'completed',
	];
}

function trufield_verify_phase( int $post_id, int $phase, int $user_id ) {
	if ( ! trufield_user_is_admin( $user_id ) ) {
		return new WP_Error( 'trufield_forbidden', __( 'Only administrators can verify a phase submission.', 'trufield-portal' ) );
	}

	if ( ! in_array( $phase, [ 1, 2, 3 ], true ) ) {
		return new WP_Error( 'trufield_invalid_phase', __( 'We could not find that phase.', 'trufield-portal' ) );
	}

	if ( trufield_get_phase_status( $post_id, $phase ) !== 'completed' ) {
		return new WP_Error( 'trufield_phase_not_completed', __( 'This phase must be marked complete before it can be verified.', 'trufield-portal' ) );
	}

update_post_meta( $post_id, "phase_{$phase}_verified", 1 );
update_post_meta( $post_id, "phase_{$phase}_verified_at", current_time( 'mysql' ) );

return true;
}

function trufield_prerequisite_met( int $post_id, int $phase ): bool {
if ( $phase <= 1 ) {
return true;
}

$previous_phase = $phase - 1;
$previous_status = trufield_get_phase_status( $post_id, $previous_phase );

return (bool) get_post_meta( $post_id, "phase_{$previous_phase}_verified", true ) || 'completed' === $previous_status;
}

function trufield_get_followup_phase_for_redirect( int $post_id, int $phase, int $user_id ): int {
	$next_phase = $phase + 1;

	if ( ! in_array( $next_phase, TRUFIELD_ACTIVE_PHASES, true ) ) {
		return 0;
	}

	if ( ! trufield_prerequisite_met( $post_id, $next_phase ) ) {
		return 0;
	}

	if ( ! trufield_can_edit_phase( $post_id, $next_phase, $user_id ) ) {
		return 0;
	}

	return $next_phase;
}

function trufield_can_edit_phase( int $post_id, int $phase, int $user_id ): bool {
	if ( trufield_user_is_admin( $user_id ) ) {
		return true;
	}

$user = get_userdata( $user_id );
if ( $user && in_array( 'leadership', (array) $user->roles, true ) ) {
return false;
}

	$assigned = trufield_get_assigned_sales_rep_id( $post_id );
if ( $assigned !== $user_id ) {
return false;
}

	if ( ! trufield_prerequisite_met( $post_id, $phase ) ) {
		return false;
	}

	return true;
}

function trufield_complete_phase( int $post_id, int $phase, int $user_id ) {
	if ( ! trufield_can_edit_phase( $post_id, $phase, $user_id ) ) {
		return new WP_Error( 'trufield_locked', __( 'This phase is not available to complete right now.', 'trufield-portal' ) );
	}

	if ( ! trufield_all_required_fields_present( $post_id, $phase ) ) {
		return new WP_Error(
			'trufield_required_fields',
			sprintf(
				/* translators: %d = phase number. */
				__( 'Before you can mark Phase %d complete, add the remaining required fields below.', 'trufield-portal' ),
				$phase
			)
		);
	}

update_post_meta( $post_id, "phase_{$phase}_status", 'completed' );
update_post_meta( $post_id, "phase_{$phase}_completed_at", current_time( 'mysql' ) );
update_post_meta( $post_id, 'current_phase', min( 3, max( 1, $phase ) ) );

return true;
}

function trufield_reopen_phase( int $post_id, int $phase, int $user_id ) {
	if ( ! trufield_user_is_admin( $user_id ) ) {
		return new WP_Error( 'trufield_forbidden', __( 'Only administrators can reopen a submitted phase.', 'trufield-portal' ) );
	}

update_post_meta( $post_id, "phase_{$phase}_status", 'in_progress' );
delete_post_meta( $post_id, "phase_{$phase}_completed_at" );
delete_post_meta( $post_id, "phase_{$phase}_verified" );
delete_post_meta( $post_id, "phase_{$phase}_verified_at" );
update_post_meta( $post_id, 'current_phase', min( 3, max( 1, $phase ) ) );

return true;
}

function trufield_rep_editable_phase_fields( int $phase ): array {
$fields = [
1 => [
'retailer_name',
'retailer_branch_location',
'retailer_key_contact',
'retailer_contact_phone',
'retailer_address',
'retailer_city',
'farm_name',
'field_trial_contact',
'contact_phone',
'field_name',
'field_location_lat',
'field_location_lng',
'phase_1_state_region',
'phase_1_product_being_tested',
'phase_1_application_type',
'phase_1_trial_design',
'phase_1_growth_stage_at_application',
'phase_1_weather_conditions_at_application',
'phase_1_soil_conditions_at_application',
'phase_1_field_overview_photo',
'phase_1_treated_size_acres',
'phase_1_carrier_volume_gal',
'phase_1_hybrid_variety',
'phase_1_planting_date',
'phase_1_planting_population',
'phase_1_row_spacing',
'phase_1_planting_speed',
'field_trial_contact_email',
'phase_1_application_date',
'phase_1_application_rate',
'phase_1_trial_type',
'phase_1_protocol_version',
'phase_1_application_timing',
'phase_1_retailer_training_discussion_date',
],
2 => [
'phase_2_rsm_visit_1_date',
'phase_2_rsm_visit_1_upload_photos',
'phase_2_rsm_visit_1_photo_type',
'phase_2_rsm_visit_2_date',
'phase_2_rsm_visit_2_upload_photos',
'phase_2_rsm_visit_2_photo_type',
'phase_2_rsm_visit_3_date',
'phase_2_rsm_visit_3_upload_photos',
'phase_2_rsm_visit_3_photo_type',
'phase_2_rsm_visit_3_comments',
'phase_2_rsm_visit_4_date',
'phase_2_rsm_visit_4_upload_photos',
'phase_2_rsm_visit_4_photo_type',
'phase_2_rsm_visit_4_comments',
'phase_2_residue_degradation_observed',
'phase_2_emergence_stand_collected',
'phase_2_stand_count_1_treated',
'phase_2_stand_count_2_treated',
'phase_2_stand_count_3_treated',
'phase_2_stand_count_1_untreated',
'phase_2_stand_count_2_untreated',
'phase_2_stand_count_3_untreated',
'phase_2_most_significant_visual_difference',
'phase_2_emergence_flag_test',
'phase_2_pictures_at_application',
'phase_2_pictures_at_planting',
'phase_2_pictures_in_season_harvest',
'phase_2_pictures_at_harvest',
'phase_2_drone_images_available',
'phase_2_grower_retailer_testimonials',
'phase_2_time_lapse_available',
'phase_2_grower_retailer_comments',
],
3 => [
'phase_3_event_date',
'phase_3_event_type',
'phase_3_event_location',
'phase_3_attendee_count',
		'phase_3_tillage_type',
		'phase_3_soil_temp_f_at_application',
		'phase_3_carrier_volume_gal',
		'phase_3_tank_mix_partners',
		'phase_3_planting_date',
		'phase_3_hybrid_variety',
		'phase_3_planting_population',
		'phase_3_row_spacing_in',
		'phase_3_planting_speed_mph',
		'phase_3_plant_heights_avg_untreated_v7_in',
		'phase_3_plant_heights_avg_treated_v7_in',
		'phase_3_stalk_diameter_untreated_v7_mm',
		'phase_3_stalk_diameter_treated_v7_mm2',
		'phase_3_yield_untreated_bu_ac',
		'phase_3_yield_treated_bu_ac',
		'phase_3_moisture_untreated_percent',
		'phase_3_moisture_treated_percent',
		'phase_3_test_weight_untreated_lbs_bu',
		'phase_3_test_weight_treated_lbs_bu',
		'phase_3_as_applied_gis_data',
		'phase_3_planting_gis_data',
		'phase_3_harvest_gis_data',
		'phase_3_agronomy_comments',
],
];

$editable = $fields[ $phase ] ?? [];

return array_values( array_diff( $editable, trufield_admin_only_phase_fields( $phase ) ) );
}

function trufield_admin_only_phase_fields( int $phase ): array {
	$fields = [
		1 => [
			'rsm_bam',
			'fsa',
		],
	];

	return $fields[ $phase ] ?? [];
}

function trufield_validate_date_value( string $value ): string {
	$value = trim( sanitize_text_field( $value ) );
	if ( '' === $value ) {
		return '';
	}

	$formats = [
		'Y-m-d',
		'm/d/Y',
		'n/j/Y',
	];

	foreach ( $formats as $format ) {
		$date = DateTime::createFromFormat( $format, $value );
		$errors = DateTime::getLastErrors();
		if ( ! $date || ( is_array( $errors ) && ( ! empty( $errors['warning_count'] ) || ! empty( $errors['error_count'] ) ) ) ) {
			continue;
		}

		$normalized_input = trim( (string) $date->format( $format ) );
		if ( $normalized_input === $value ) {
			return $date->format( 'Y-m-d' );
		}
	}

	return '';
}

function trufield_normalize_phone_value( string $value ): string {
	$value = trim( $value );
	if ( '' === $value ) {
		return '';
	}

	if ( function_exists( 'trufield_import_sanitize_phone' ) ) {
		return trufield_import_sanitize_phone( $value );
	}

	$digits = preg_replace( '/\D+/', '', $value );
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

function trufield_validate_phase_field_submission( string $field, $raw_value ): string {
	$schema = trufield_phase_field_schema();
	$labels = trufield_field_labels();
	$type   = $schema[ $field ]['type'] ?? 'text';
	$value  = trim( is_string( $raw_value ) ? wp_unslash( $raw_value ) : (string) $raw_value );
	$label  = $labels[ $field ] ?? $field;

	if ( '' === $value ) {
		return '';
	}

	if ( 'email' === $type && ! is_email( $value ) ) {
		return sprintf( __( '%s must be a valid email address.', 'trufield-portal' ), $label );
	}

	if ( 'phone' === $type && '' === trufield_normalize_phone_value( $value ) ) {
		return sprintf( __( '%s must be a valid phone number.', 'trufield-portal' ), $label );
	}

	return '';
}

function trufield_sanitize_phase_field_value( string $field, $raw_value ) {
$schema = trufield_phase_field_schema();
$type   = $schema[ $field ]['type'] ?? 'text';
$value  = is_string( $raw_value ) ? wp_unslash( $raw_value ) : $raw_value;

switch ( $type ) {
case 'boolean':
	return ! empty( $value ) ? '1' : '';

case 'user':
	$value = trim( (string) $value );
	if ( $value === '' ) {
		return '';
	}

	$value = absint( $value );

	if ( 'rsm_bam' === $field && ! trufield_is_allowed_rsm_bam_user_id( $value ) ) {
		return '';
	}

	return $value;

case 'integer':
$value = trim( (string) $value );
return $value === '' ? '' : absint( $value );

case 'number':
$value = trim( (string) $value );
return $value === '' ? '' : (float) $value;

case 'url':
$value = trim( (string) $value );
return $value === '' ? '' : esc_url_raw( $value );

case 'email':
	$value = trim( (string) $value );
	return $value === '' || ! is_email( $value ) ? '' : sanitize_email( $value );

case 'phone':
	return trufield_normalize_phone_value( (string) $value );

case 'textarea':
$value = sanitize_textarea_field( (string) $value );
return trim( $value );

case 'select':
	$value   = trim( sanitize_text_field( (string) $value ) );
	$options = array_keys( $schema[ $field ]['options'] ?? [] );

	if ( in_array( $value, $options, true ) ) {
		return $value;
	}

	$normalized_value = sanitize_key( $value );
	foreach ( $options as $option_key ) {
		if ( sanitize_key( (string) $option_key ) === $normalized_value ) {
			return (string) $option_key;
		}
	}

	return '';

case 'date':
return trufield_validate_date_value( (string) $value );

case 'text':
default:
$value = sanitize_text_field( (string) $value );
return trim( $value );
}
}

function trufield_phase_photo_attachment_meta_key( string $field ): string {
	return $field . '_attachment_id';
}

function trufield_calculate_phase_2_stand_count_delta( $treated_value, $untreated_value ): string {
	$treated_values = is_array( $treated_value ) ? $treated_value : [ $treated_value ];
	$untreated_values = is_array( $untreated_value ) ? $untreated_value : [ $untreated_value ];
	$treated_numbers = [];
	$untreated_numbers = [];

	foreach ( $treated_values as $value ) {
		$value = trim( (string) $value );
		if ( '' === $value || ! is_numeric( $value ) ) {
			return '';
		}
		$treated_numbers[] = (float) $value;
	}

	foreach ( $untreated_values as $value ) {
		$value = trim( (string) $value );
		if ( '' === $value || ! is_numeric( $value ) ) {
			return '';
		}
		$untreated_numbers[] = (float) $value;
	}

	if ( [] === $treated_numbers || [] === $untreated_numbers ) {
		return '';
	}

	$treated_average = array_sum( $treated_numbers ) / count( $treated_numbers );
	$untreated_average = array_sum( $untreated_numbers ) / count( $untreated_numbers );
	$delta = round( $treated_average - $untreated_average, 2 );
	$formatted = number_format( $delta, 2, '.', '' );
	$formatted = rtrim( rtrim( $formatted, '0' ), '.' );

	return '-0' === $formatted ? '0' : $formatted;
}

function trufield_sync_phase_2_stand_count_delta( int $post_id ): void {
	$treated = [
		get_post_meta( $post_id, 'phase_2_stand_count_1_treated', true ),
		get_post_meta( $post_id, 'phase_2_stand_count_2_treated', true ),
		get_post_meta( $post_id, 'phase_2_stand_count_3_treated', true ),
	];
	$untreated = [
		get_post_meta( $post_id, 'phase_2_stand_count_1_untreated', true ),
		get_post_meta( $post_id, 'phase_2_stand_count_2_untreated', true ),
		get_post_meta( $post_id, 'phase_2_stand_count_3_untreated', true ),
	];
	$delta = trufield_calculate_phase_2_stand_count_delta( $treated, $untreated );

	if ( '' === $delta ) {
		delete_post_meta( $post_id, 'phase_2_stand_count_data' );
		return;
	}

	update_post_meta( $post_id, 'phase_2_stand_count_data', $delta );
}

function trufield_phase_file_fields( int $phase ): array {
	$fields = [
		1 => [ 'phase_1_field_overview_photo' ],
		2 => [
			'phase_2_rsm_visit_1_upload_photos',
			'phase_2_rsm_visit_2_upload_photos',
			'phase_2_rsm_visit_3_upload_photos',
			'phase_2_rsm_visit_4_upload_photos',
			'phase_2_pictures_at_application_upload',
			'phase_2_pictures_at_planting_upload',
			'phase_2_pictures_in_season_harvest_upload',
			'phase_2_drone_images_available_upload',
		],
		3 => [],
	];

	return $fields[ $phase ] ?? [];
}

function trufield_get_phase_step_count( int $phase ): int {
	$steps = [
		1 => 3,
		2 => 3,
		3 => 1,
	];

	return $steps[ $phase ] ?? 1;
}

function trufield_get_phase_step_field_map( int $phase ): array {
	$map = [
		1 => [
			1 => [
				'retailer_name',
				'retailer_branch_location',
				'retailer_key_contact',
				'retailer_contact_phone',
				'retailer_address',
				'retailer_city',
				'phase_1_state_region',
			],
			2 => [
				'field_trial_contact',
				'contact_phone',
				'field_trial_contact_email',
				'farm_name',
				'field_name',
				'field_location_lat',
				'field_location_lng',
			],
			3 => [
				'phase_1_treated_size_acres',
				'phase_1_application_rate',
				'phase_1_trial_type',
				'phase_1_protocol_version',
				'phase_1_application_timing',
				'phase_1_application_date',
				'phase_1_retailer_training_discussion_date',
			],
		],
		2 => [
			1 => [
				'phase_2_rsm_visit_1_date',
				'phase_2_rsm_visit_1_upload_photos',
				'phase_2_rsm_visit_2_date',
				'phase_2_rsm_visit_2_upload_photos',
			],
			2 => [
				'phase_2_stand_count_1_treated',
				'phase_2_stand_count_2_treated',
				'phase_2_stand_count_3_treated',
				'phase_2_stand_count_1_untreated',
				'phase_2_stand_count_2_untreated',
				'phase_2_stand_count_3_untreated',
			],
			3 => [
				'phase_2_grower_retailer_testimonials',
				'phase_2_grower_retailer_comments',
			],
		],
	];

	return $map[ $phase ] ?? [];
}

function trufield_get_phase_step_for_fields( int $phase, array $fields, int $fallback_step = 1 ): int {
	$step_map = trufield_get_phase_step_field_map( $phase );
	if ( [] === $step_map ) {
		return max( 1, $fallback_step );
	}

	foreach ( $step_map as $step => $step_fields ) {
		foreach ( $fields as $field ) {
			if ( in_array( $field, $step_fields, true ) ) {
				return (int) $step;
			}
		}
	}

	return max( 1, $fallback_step );
}

function trufield_get_field_input_id( string $field ): string {
	if ( 'retailer_name' === $field ) {
		return 'retailer_name_select';
	}

	foreach ( [ 1, 2, 3 ] as $phase ) {
		if ( in_array( $field, trufield_phase_file_fields( $phase ), true ) ) {
			return $field . '_upload';
		}
	}

	return $field;
}

function trufield_get_missing_field_navigation_items( int $phase, array $fields ): array {
	$labels = trufield_field_labels();
	$items  = [];

	foreach ( array_values( array_unique( $fields ) ) as $field ) {
		if ( ! is_string( $field ) || '' === $field ) {
			continue;
		}

		$items[] = [
			'field'     => $field,
			'label'     => $labels[ $field ] ?? $field,
			'target_id' => trufield_get_field_input_id( $field ),
			'step'      => trufield_get_phase_step_for_fields( $phase, [ $field ], 1 ),
		];
	}

	return $items;
}

function trufield_delete_phase_photo_value( int $post_id, string $field ): void {
	delete_post_meta( $post_id, $field );
	delete_post_meta( $post_id, trufield_phase_photo_attachment_meta_key( $field ) );
}

function trufield_get_max_upload_size_bytes(): int {
	return (int) wp_max_upload_size();
}

function trufield_get_max_upload_size_label(): string {
	$bytes = trufield_get_max_upload_size_bytes();

	return $bytes > 0 ? size_format( $bytes ) : __( 'server limit', 'trufield-portal' );
}

function trufield_phase_photo_type_field_for_upload( string $field ): string {
	if ( str_ends_with( $field, '_upload_photos' ) ) {
		return str_replace( '_upload_photos', '_photo_type', $field );
	}

	$mapping = [
		'phase_2_pictures_at_application_upload' => 'phase_2_pictures_at_application',
		'phase_2_pictures_at_planting_upload' => 'phase_2_pictures_at_planting',
		'phase_2_pictures_in_season_harvest_upload' => 'phase_2_pictures_in_season_harvest',
		'phase_2_drone_images_available_upload' => 'phase_2_drone_images_available',
	];

	return $mapping[ $field ] ?? '';
}

function trufield_get_phase_upload_prompt_label( string $field ): string {
	$labels = [
		'phase_2_rsm_visit_1_upload_photos' => __( 'the selected visit 1', 'trufield-portal' ),
		'phase_2_rsm_visit_2_upload_photos' => __( 'the selected visit 2', 'trufield-portal' ),
		'phase_2_rsm_visit_3_upload_photos' => __( 'the selected visit 3', 'trufield-portal' ),
		'phase_2_rsm_visit_4_upload_photos' => __( 'the selected visit 4', 'trufield-portal' ),
		'phase_2_pictures_at_application_upload' => __( 'the application image', 'trufield-portal' ),
		'phase_2_pictures_at_planting_upload' => __( 'the planting image', 'trufield-portal' ),
		'phase_2_pictures_in_season_harvest_upload' => __( 'the in season/harvest image', 'trufield-portal' ),
		'phase_2_drone_images_available_upload' => __( 'the drone image', 'trufield-portal' ),
	];

	return $labels[ $field ] ?? '';
}

function trufield_get_phase_upload_help_text( string $field, int $post_id = 0 ): string {
	unset( $post_id );

	if ( strpos( $field, 'phase_2_rsm_visit_' ) === 0 ) {
		return sprintf( __( 'The uploaded file will be renamed to the trial UUID plus the selected type. Max file size: %s.', 'trufield-portal' ), trufield_get_max_upload_size_label() );
	}

	return sprintf( __( 'Max file size: %s.', 'trufield-portal' ), trufield_get_max_upload_size_label() );
}

function trufield_build_phase_photo_filename( int $post_id, string $field, string $original_name ): string {
	$extension = strtolower( pathinfo( $original_name, PATHINFO_EXTENSION ) );
	$trial_uuid = trim( (string) get_post_meta( $post_id, 'trial_uuid', true ) );
	$trial_uuid = '' !== $trial_uuid ? $trial_uuid : trim( (string) get_the_title( $post_id ) );
	$photo_type_field = trufield_phase_photo_type_field_for_upload( $field );
	$photo_type_value = $photo_type_field ? (string) get_post_meta( $post_id, $photo_type_field, true ) : '';
	$schema = trufield_phase_field_schema();
	$photo_type_label = $schema[ $photo_type_field ]['options'][ $photo_type_value ] ?? $photo_type_value;
	$base_name = sanitize_title( trim( $trial_uuid ) . ' ' . trim( (string) $photo_type_label ) );

	if ( '' === $base_name ) {
		$base_name = sanitize_title( trim( $trial_uuid ) );
	}

	return '' !== $extension ? $base_name . '.' . $extension : $base_name;
}

function trufield_handle_phase_photo_upload( int $post_id, string $field, string $file_input ) {
	if ( empty( $_FILES[ $file_input ] ) || ! is_array( $_FILES[ $file_input ] ) ) {
		return null;
	}

	$file = $_FILES[ $file_input ];
	if ( (int) ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) === UPLOAD_ERR_NO_FILE ) {
		return null;
	}

	if ( ! empty( $file['error'] ) ) {
		return new WP_Error( 'trufield_upload_error', __( 'The photo upload could not be processed.', 'trufield-portal' ) );
	}

	$max_upload_size = trufield_get_max_upload_size_bytes();
	if ( $max_upload_size > 0 && (int) ( $file['size'] ?? 0 ) > $max_upload_size ) {
		return new WP_Error( 'trufield_upload_too_large', sprintf( __( 'The uploaded photo exceeds the maximum file size of %s.', 'trufield-portal' ), trufield_get_max_upload_size_label() ) );
	}

	$_FILES[ $file_input ]['name'] = trufield_build_phase_photo_filename( $post_id, $field, (string) ( $file['name'] ?? '' ) );

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$attachment_id = media_handle_upload( $file_input, $post_id );
	if ( is_wp_error( $attachment_id ) ) {
		return $attachment_id;
	}

	$image_url = wp_get_attachment_url( $attachment_id );
	if ( ! $image_url ) {
		return new WP_Error( 'trufield_upload_url_missing', __( 'The uploaded photo could not be linked to this trial.', 'trufield-portal' ) );
	}

	update_post_meta( $post_id, $field, esc_url_raw( $image_url ) );
	update_post_meta( $post_id, trufield_phase_photo_attachment_meta_key( $field ), $attachment_id );

	return $attachment_id;
}

add_action( 'admin_post_trufield_save_phase', 'trufield_handle_save_phase' );
add_action( 'admin_post_nopriv_trufield_save_phase', 'trufield_handle_save_phase_nopriv' );
add_action( 'admin_post_trufield_reopen_phase', 'trufield_handle_reopen_phase' );
add_action( 'admin_post_trufield_verify_phase', 'trufield_handle_verify_phase' );

function trufield_admin_action_redirect_url( int $post_id, string $query_key, int $phase ): string {
	$referer = wp_get_referer();

	if ( is_string( $referer ) && $referer !== '' ) {
		$site_host    = wp_parse_url( home_url(), PHP_URL_HOST );
		$referer_host = wp_parse_url( $referer, PHP_URL_HOST );
		$referer_path = wp_parse_url( $referer, PHP_URL_PATH );
		$record_url   = get_permalink( $post_id );
		$record_path  = is_string( $record_url ) ? wp_parse_url( $record_url, PHP_URL_PATH ) : null;

		if ( ( $referer_host === null || $referer_host === $site_host ) && is_string( $referer_path ) && is_string( $record_path ) && untrailingslashit( $referer_path ) === untrailingslashit( $record_path ) ) {
			return add_query_arg( $query_key, $phase, $referer );
		}
	}

	return admin_url( "post.php?post={$post_id}&action=edit&{$query_key}={$phase}" );
}

function trufield_temp_submit_log( string $event, array $context = [] ): void {
	if ( defined( 'TRUFIELD_TEMP_SUBMIT_LOGGING' ) && ! TRUFIELD_TEMP_SUBMIT_LOGGING ) {
		return;
	}

	$payload = wp_json_encode( $context );
	if ( ! is_string( $payload ) ) {
		$payload = '{}';
	}

	error_log( '[TruField Temp Submit] ' . $event . ' ' . $payload );
}

function trufield_handle_save_phase_nopriv(): void {
wp_safe_redirect( wp_login_url( wp_get_referer() ) );
exit;
}

function trufield_handle_save_phase(): void {
$nonce   = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) );
$post_id = (int) ( $_POST['plant_field_id'] ?? 0 );
$phase   = (int) ( $_POST['phase'] ?? 0 );

	if ( ! wp_verify_nonce( $nonce, "trufield_save_phase_{$post_id}_{$phase}" ) ) {
		wp_die( esc_html__( 'Your session check failed. Please refresh the page and try again.', 'trufield-portal' ), 403 );
	}

	if ( ! $post_id || ! in_array( $phase, [ 1, 2, 3 ], true ) ) {
		wp_die( esc_html__( 'We could not process that request.', 'trufield-portal' ), 400 );
	}

	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		wp_die( esc_html__( 'Please sign in to continue.', 'trufield-portal' ), 403 );
	}

	$should_temp_log = in_array( $phase, [ 2, 3 ], true );
	$log_request_id  = 'p' . $phase . '-post' . $post_id . '-u' . $user_id . '-' . substr( wp_hash( (string) microtime( true ) . ':' . (string) wp_rand() ), 0, 8 );

	$phase_auto_verifies = trufield_phase_auto_verifies( $phase );
	$was_verified        = (bool) get_post_meta( $post_id, "phase_{$phase}_verified", true );

	if ( ! trufield_can_edit_phase( $post_id, $phase, $user_id ) ) {
		wp_die( esc_html__( 'You do not have permission to update this phase.', 'trufield-portal' ), 403 );
	}

	$redirect_base = wp_get_referer() ?: get_permalink( $post_id );
	$phase_step    = max( 1, min( trufield_get_phase_step_count( $phase ), (int) ( $_POST['phase_step'] ?? 1 ) ) );
	$phase_step_query_arg = sprintf( 'phase_%d_step', $phase );
	$redirect_clean = remove_query_arg( [ 'phase_step', $phase_step_query_arg ], $redirect_base );
	$redirect      = trufield_get_phase_step_count( $phase ) > 1 ? add_query_arg( $phase_step_query_arg, $phase_step, $redirect_clean ) : $redirect_clean;
	$action_source = $_POST['phase_action'] ?? ( $_POST['phase_action_intent'] ?? 'save' );
	$action        = sanitize_key( (string) $action_source );
	if ( ! in_array( $action, [ 'save', 'complete', 'verify_address' ], true ) ) {
		$action = 'save';
	}

	if ( $should_temp_log ) {
		trufield_temp_submit_log(
			'handle_save_phase:start',
			[
				'request_id'    => $log_request_id,
				'phase'         => $phase,
				'post_id'       => $post_id,
				'user_id'       => $user_id,
				'action'        => $action,
				'phase_action'  => sanitize_key( (string) ( $_POST['phase_action'] ?? '' ) ),
				'action_intent' => sanitize_key( (string) ( $_POST['phase_action_intent'] ?? '' ) ),
				'phase_step'    => $phase_step,
			]
		);
	}

	if ( 1 === $phase && array_key_exists( 'retailer_name', $_POST ) ) {
		$retailer_selection = trim( sanitize_text_field( wp_unslash( $_POST['retailer_name'] ) ) );
		$retailer_manual    = trim( sanitize_text_field( wp_unslash( $_POST['retailer_name_manual'] ?? '' ) ) );

		if ( 'other' === strtolower( $retailer_selection ) ) {
			$directory_entry = [
				'retailer_branch_location' => sanitize_text_field( wp_unslash( $_POST['retailer_branch_location'] ?? '' ) ),
				'retailer_key_contact'     => sanitize_text_field( wp_unslash( $_POST['retailer_key_contact'] ?? '' ) ),
				'retailer_contact_phone'   => wp_unslash( $_POST['retailer_contact_phone'] ?? '' ),
				'retailer_address'         => sanitize_text_field( wp_unslash( $_POST['retailer_address'] ?? '' ) ),
				'retailer_city'            => sanitize_text_field( wp_unslash( $_POST['retailer_city'] ?? '' ) ),
				'phase_1_state_region'     => sanitize_text_field( wp_unslash( $_POST['phase_1_state_region'] ?? '' ) ),
				'rsm_bam'                  => (string) absint( wp_unslash( $_POST['rsm_bam'] ?? get_post_meta( $post_id, 'rsm_bam', true ) ) ),
			];

			$_POST['retailer_name'] = trufield_upsert_retailer_directory_entry( $retailer_manual, $directory_entry );
		} else {
			$_POST['retailer_name'] = $retailer_selection;
		}
	}

	$editable = trufield_rep_editable_phase_fields( $phase );
	if ( trufield_user_is_admin( $user_id ) ) {
		$editable = array_merge( $editable, trufield_admin_only_phase_fields( $phase ) );
	}

	$field_errors = [];
	$invalid_fields = [];
	foreach ( $editable as $field ) {
		if ( ! array_key_exists( $field, $_POST ) ) {
			continue;
		}

		$field_error = trufield_validate_phase_field_submission( $field, $_POST[ $field ] );
		if ( '' !== $field_error ) {
			$field_errors[] = $field_error;
			$invalid_fields[] = $field;
		}
	}

	if ( [] !== $field_errors ) {
		$error_step = trufield_get_phase_step_for_fields( $phase, $invalid_fields, $phase_step );
		$error_redirect = trufield_get_phase_step_count( $phase ) > 1 ? add_query_arg( $phase_step_query_arg, $error_step, $redirect_clean ) : $redirect;
		if ( $should_temp_log ) {
			trufield_temp_submit_log(
				'handle_save_phase:field_validation_error',
				[
					'request_id'      => $log_request_id,
					'phase'           => $phase,
					'action'          => $action,
					'invalid_fields'  => $invalid_fields,
					'field_error_cnt' => count( $field_errors ),
					'redirect'        => $error_redirect,
				]
			);
		}
		wp_safe_redirect( add_query_arg( 'tf_error', rawurlencode( implode( ' ', $field_errors ) ), $error_redirect ) );
		exit;
	}

foreach ( $editable as $field ) {
if ( ! array_key_exists( $field, $_POST ) ) {
continue;
}

$sanitized = trufield_sanitize_phase_field_value( $field, $_POST[ $field ] );
if ( $sanitized === '' ) {
delete_post_meta( $post_id, $field );
continue;
}

update_post_meta( $post_id, $field, $sanitized );
}

	if ( 1 === $phase && trufield_user_is_admin( $user_id ) && array_key_exists( 'rsm_bam', $_POST ) ) {
		$assigned_rep_id = absint( (string) $_POST['rsm_bam'] );

		if ( $assigned_rep_id > 0 ) {
			update_post_meta( $post_id, 'assigned_sales_rep', $assigned_rep_id );
		} else {
			delete_post_meta( $post_id, 'assigned_sales_rep' );
		}
	}

	foreach ( trufield_phase_file_fields( $phase ) as $file_field ) {
		if ( ! empty( $_POST[ $file_field . '_remove' ] ) ) {
			trufield_delete_phase_photo_value( $post_id, $file_field );
		}

		$upload_result = trufield_handle_phase_photo_upload( $post_id, $file_field, $file_field . '_upload' );
		if ( is_wp_error( $upload_result ) ) {
			if ( $should_temp_log ) {
				trufield_temp_submit_log(
					'handle_save_phase:upload_error',
					[
						'request_id' => $log_request_id,
						'phase'      => $phase,
						'action'     => $action,
						'file_field' => $file_field,
						'error'      => $upload_result->get_error_message(),
					]
				);
			}
			wp_safe_redirect( add_query_arg( 'tf_error', rawurlencode( $upload_result->get_error_message() ), $redirect ) );
			exit;
		}
	}

	if ( 2 === $phase ) {
		trufield_sync_phase_2_stand_count_delta( $post_id );
	}

if ( $action === 'verify_address' ) {
	$address = trim( (string) get_post_meta( $post_id, 'field_location_address', true ) );
	if ( '' === $address ) {
		wp_safe_redirect( add_query_arg( 'tf_error', rawurlencode( __( 'Enter a field location address before verifying it.', 'trufield-portal' ) ), $redirect ) );
		exit;
	}

	$result = trufield_lookup_address_coordinates( $address, trufield_get_google_maps_api_key() );
	if ( ! $result || ! isset( $result['lat'], $result['lng'] ) ) {
		wp_safe_redirect( add_query_arg( 'tf_error', rawurlencode( __( 'We could not verify that address right now.', 'trufield-portal' ) ), $redirect ) );
		exit;
	}

	update_post_meta( $post_id, 'field_location_address', (string) ( $result['address'] ?? $address ) );
	update_post_meta( $post_id, 'field_location_lat', (float) $result['lat'] );
	update_post_meta( $post_id, 'field_location_lng', (float) $result['lng'] );
	delete_post_meta( $post_id, 'field_location_manual_override' );

	if ( $was_verified && $phase_auto_verifies && ! trufield_user_is_admin( $user_id ) ) {
		trufield_sync_phase_verification_state( $post_id, $phase );
	}

	if ( trufield_get_phase_status( $post_id, $phase ) === 'pending' ) {
		update_post_meta( $post_id, "phase_{$phase}_status", 'in_progress' );
	}
	update_post_meta( $post_id, 'current_phase', min( 3, max( 1, $phase ) ) );
	wp_safe_redirect( add_query_arg( 'tf_success', 'address_verified', $redirect ) );
	exit;
}

if ( $action === 'complete' ) {
	if ( $should_temp_log ) {
		trufield_temp_submit_log(
			'handle_save_phase:complete_requested',
			[
				'request_id' => $log_request_id,
				'phase'      => $phase,
				'post_id'    => $post_id,
				'user_id'    => $user_id,
			]
		);
	}
$result = trufield_complete_phase( $post_id, $phase, $user_id );
if ( is_wp_error( $result ) ) {
	$missing_fields = trufield_get_missing_required_field_keys( $post_id, $phase );
	$error_step = trufield_get_phase_step_for_fields( $phase, $missing_fields, $phase_step );
	$error_redirect = trufield_get_phase_step_count( $phase ) > 1 ? add_query_arg( $phase_step_query_arg, $error_step, $redirect_clean ) : $redirect;
	if ( $should_temp_log ) {
		trufield_temp_submit_log(
			'handle_save_phase:complete_failed',
			[
				'request_id'     => $log_request_id,
				'phase'          => $phase,
				'error'          => $result->get_error_message(),
				'missing_fields' => $missing_fields,
				'redirect'       => $error_redirect,
			]
		);
	}
	wp_safe_redirect(
		add_query_arg(
			[
				'tf_error'          => rawurlencode( $result->get_error_message() ),
				'tf_error_phase'    => $phase,
				'tf_missing_fields' => implode( ',', $missing_fields ),
			],
			$error_redirect
		)
	);
exit;
}

	$followup_phase = trufield_get_followup_phase_for_redirect( $post_id, $phase, $user_id );
	$completion_redirect_base = remove_query_arg( [ 'phase_step', 'phase_1_step', 'phase_2_step', 'phase_3_step' ], $redirect_clean );
	if ( $followup_phase > 0 && trufield_get_phase_step_count( $followup_phase ) > 1 ) {
		$completion_redirect_base = add_query_arg( sprintf( 'phase_%d_step', $followup_phase ), 1, $completion_redirect_base );
	}

	if ( trufield_phase_auto_verifies( $phase ) ) {
		$phase_state = trufield_sync_phase_verification_state( $post_id, $phase );
		$completion_redirect = add_query_arg( 'tf_success', ! empty( $phase_state['just_verified'] ) ? "phase_{$phase}_autoverified" : "phase_{$phase}_completed", $completion_redirect_base );
		if ( $followup_phase > 0 ) {
			$completion_redirect .= '#tf-phase-' . $followup_phase;
		}
		if ( $should_temp_log ) {
			trufield_temp_submit_log(
				'handle_save_phase:complete_success_auto_verify',
				[
					'request_id'    => $log_request_id,
					'phase'         => $phase,
					'followup_phase'=> $followup_phase,
					'redirect'      => $completion_redirect,
				]
			);
		}
		wp_safe_redirect( $completion_redirect );
		exit;
	}

	if ( $was_verified && ! trufield_user_is_admin( $user_id ) ) {
		delete_post_meta( $post_id, "phase_{$phase}_verified" );
		delete_post_meta( $post_id, "phase_{$phase}_verified_at" );
	}

	$completion_redirect = add_query_arg( 'tf_success', "phase_{$phase}_completed", $completion_redirect_base );
	if ( $followup_phase > 0 ) {
		$completion_redirect .= '#tf-phase-' . $followup_phase;
	}
	if ( $should_temp_log ) {
		trufield_temp_submit_log(
			'handle_save_phase:complete_success',
			[
				'request_id'    => $log_request_id,
				'phase'         => $phase,
				'followup_phase'=> $followup_phase,
				'redirect'      => $completion_redirect,
			]
		);
	}
	wp_safe_redirect( $completion_redirect );
exit;
}

if ( $was_verified && ! trufield_user_is_admin( $user_id ) ) {
	if ( $phase_auto_verifies ) {
		trufield_sync_phase_verification_state( $post_id, $phase );
	} else {
		delete_post_meta( $post_id, "phase_{$phase}_verified" );
		delete_post_meta( $post_id, "phase_{$phase}_verified_at" );
		update_post_meta( $post_id, "phase_{$phase}_status", 'in_progress' );
		delete_post_meta( $post_id, "phase_{$phase}_completed_at" );
	}
}

if ( trufield_get_phase_status( $post_id, $phase ) === 'pending' ) {
update_post_meta( $post_id, "phase_{$phase}_status", 'in_progress' );
}
update_post_meta( $post_id, 'current_phase', min( 3, max( 1, $phase ) ) );

if ( $should_temp_log ) {
	trufield_temp_submit_log(
		'handle_save_phase:save_success',
		[
			'request_id' => $log_request_id,
			'phase'      => $phase,
			'action'     => $action,
			'redirect'   => add_query_arg( 'tf_success', 'saved', $redirect ),
		]
	);
}

wp_safe_redirect( add_query_arg( 'tf_success', 'saved', $redirect ) );
exit;
}

function trufield_handle_reopen_phase(): void {
$nonce   = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) );
$post_id = (int) ( $_GET['post_id'] ?? 0 );
$phase   = (int) ( $_GET['phase'] ?? 0 );

	if ( ! wp_verify_nonce( $nonce, "trufield_reopen_phase_{$post_id}_{$phase}" ) ) {
		wp_die( esc_html__( 'Your session check failed. Please refresh the page and try again.', 'trufield-portal' ), 403 );
	}

$result = trufield_reopen_phase( $post_id, $phase, get_current_user_id() );
if ( is_wp_error( $result ) ) {
wp_die( esc_html( $result->get_error_message() ), 403 );
}

wp_safe_redirect( trufield_admin_action_redirect_url( $post_id, 'tf_reopened', $phase ) );
exit;
}

function trufield_handle_verify_phase(): void {
$post_id = (int) ( $_GET['post_id'] ?? 0 );
$phase   = (int) ( $_GET['phase'] ?? 0 );
$nonce   = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) );

	if ( ! wp_verify_nonce( $nonce, "trufield_verify_phase_{$post_id}_{$phase}" ) ) {
		wp_die( esc_html__( 'Your session check failed. Please refresh the page and try again.', 'trufield-portal' ), 403 );
	}

$result = trufield_verify_phase( $post_id, $phase, get_current_user_id() );
if ( is_wp_error( $result ) ) {
wp_die( esc_html( $result->get_error_message() ), 403 );
}

wp_safe_redirect( trufield_admin_action_redirect_url( $post_id, 'tf_verified', $phase ) );
exit;
}
