<?php
/**
 * TruField Portal — CSV Export
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

add_action( 'admin_post_trufield_export_csv', 'trufield_handle_csv_export' );

function trufield_handle_csv_export(): void {
$nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) );
if ( ! wp_verify_nonce( $nonce, 'trufield_export_csv' ) ) {
wp_die( esc_html__( 'Security check failed.', 'trufield-portal' ), 403 );
}

if ( ! current_user_can( 'trufield_export_csv' ) ) {
wp_die( esc_html__( 'You do not have permission to export.', 'trufield-portal' ), 403 );
}

$fields = trufield_get_all_fields();

header( 'Content-Type: text/csv; charset=UTF-8' );
header( 'Content-Disposition: attachment; filename="plant-fields-' . gmdate( 'Y-m-d' ) . '.csv"' );
header( 'Pragma: no-cache' );
header( 'Expires: 0' );

$output = fopen( 'php://output', 'w' );
if ( ! $output ) {
wp_die( esc_html__( 'Could not open output stream.', 'trufield-portal' ) );
}

fwrite( $output, "\xEF\xBB\xBF" );
fputcsv( $output, trufield_csv_headers() );

foreach ( $fields as $post ) {
fputcsv( $output, trufield_csv_row( $post ) );
}

fclose( $output );
exit;
}

function trufield_csv_headers(): array {
return [
'RSM/BAM',
'FSA',
'Retailer',
'Retailer Contact',
'Retailer Contact Phone #',
'Retailer Address',
'City',
'State',
'Retailer Branch Location',
'Crop Specialist/Field Trial Contact (First Last) ',
'Crop Specialist/ Field Trial Contact Phone',
'Field Trial Contact email',
'Lat/Long of Trial',
'Farm Name (Optional) ',
'Field Name (Optional)',
'Treated Size (ac)',
'Applied Rate (oz/ac)',
'Trial Type',
'Protocol Version',
'Application Timing',
'Application Date',
'Retailer Product Training/Discussion',
'RSM Visit Date 1',
'RSM Visit 1 Date Photos Taken Treated/Untreated',
'RSM Visit Date 2',
'RSM Visit 2 Date Photos Taken Treated/Untreated',
'Optional Visit Date 3',
'Optional Visit 3 Date Photos Taken Treated/Untreated',
'Visit 3 Notes',
'Optional Visit Date 4',
'Optional Visit 4 Date Photos Taken Treated/Untreated',
'Visit 4 Notes',
'Residue Degradation Observed? Y/N',
'Emergence, Stand collected (Y/N)',
'Stand Count 1 TREATED (30" ROW = 17.5\' & 15" ROW  =34.9\')',
'Stand Count 2 TREATED (30" ROW = 17.5\' & 15" ROW  =34.9\')',
'Stand Count 3 TREATED (30" ROW = 17.5\' & 15" ROW  =34.9\')',
'Stand Count 1 UNTREATED (30" ROW = 17.5\' & 15" ROW  =34.9\')',
'Stand Count 2 UNTREATED (30" ROW = 17.5\' & 15" ROW  =34.9\')',
'Stand Count 3 UNTREATED (30" ROW = 17.5\' & 15" ROW  =34.9\')',
'Stand Count Deltas (plt/A)',
'What is the most significant visual difference today (e.g., even emergence, residue breakdown)?',
'Emergence (Flag Test) (Y/N)',
'Pictures at Application (Y/N)',
'Pictures at Planting (Y/N)',
'Pictures In season/ Harvest (Y/N)',
'Drone Images Available (Y/N)',
'Grower / Retailer Testimonials (Y/N)',
'Grower / Retailer Comments',
'TruField In Person Workshop/Demo Day (Yes or No)',
'TruField In Person Workshop/Demo Day Date Held ',
'TruField In Person Workshop/Demo Day Location',
'TruField In Person Workshop/Demo Day             Number of Attendees',
'Tillage Type',
'Soil Temp (F) at application',
'Carrier Volume (Gal)',
'Tank Mix Partners',
'Planting Date',
'Hybrid/Variety',
'Planting Population',
'Row Spacing (in)',
'Planting Speed (mph)',
'Plant Heights Avg Untreated @ V7 (In)',
'Plant Heights Avg Treated @ V7 (In)',
'Stalk Diameter Untreated @ V7 (mm)',
'Stalk DiameterTreated @ V7 (mm)2',
'Yield Untreated (bu/ac)',
'Yield Treated (bu/ac)',
'Moisture Untreated (%)',
'Moisture Treated (%)',
'Test Weight Untreated (lbs/bu)',
'Test Weight Treated (lbs/bu)',
'As Applied GIS Data (Y/N)',
'Planting GIS Data (Y/N)',
'Harvest GIS Data (Y/N)',
'Agronomy Comments',
];
}

function trufield_csv_row( WP_Post $post ): array {
$id      = $post->ID;
$schema  = trufield_phase_field_schema();
$yes_no  = static fn( bool $value ): string => $value ? 'Yes' : 'No';
$select_label = static function ( string $field, string $value ) use ( $schema ): string {
if ( $value === '' ) {
return '';
}
return $schema[ $field ]['options'][ $value ] ?? $value;
};
	$lat = trim( (string) get_post_meta( $id, 'field_location_lat', true ) );
	$lng = trim( (string) get_post_meta( $id, 'field_location_lng', true ) );
	$lat_lng = '';
	if ( '' !== $lat || '' !== $lng ) {
		$lat_lng = trim( $lat . ', ' . $lng, ', ' );
	}

return [
trufield_resolve_assignment_user_label( get_post_meta( $id, 'rsm_bam', true ) ),
trufield_resolve_assignment_user_label( get_post_meta( $id, 'fsa', true ) ),
get_post_meta( $id, 'retailer_name', true ),
get_post_meta( $id, 'retailer_key_contact', true ),
get_post_meta( $id, 'retailer_contact_phone', true ),
get_post_meta( $id, 'retailer_address', true ),
get_post_meta( $id, 'retailer_city', true ),
get_post_meta( $id, 'phase_1_state_region', true ),
get_post_meta( $id, 'retailer_branch_location', true ),
get_post_meta( $id, 'field_trial_contact', true ),
get_post_meta( $id, 'contact_phone', true ),
get_post_meta( $id, 'field_trial_contact_email', true ),
	$lat_lng,
get_post_meta( $id, 'farm_name', true ),
get_post_meta( $id, 'field_name', true ),
get_post_meta( $id, 'phase_1_treated_size_acres', true ),
get_post_meta( $id, 'phase_1_application_rate', true ),
$select_label( 'phase_1_trial_type', (string) get_post_meta( $id, 'phase_1_trial_type', true ) ),
$select_label( 'phase_1_protocol_version', (string) get_post_meta( $id, 'phase_1_protocol_version', true ) ),
$select_label( 'phase_1_application_timing', (string) get_post_meta( $id, 'phase_1_application_timing', true ) ),
get_post_meta( $id, 'phase_1_application_date', true ),
get_post_meta( $id, 'phase_1_retailer_training_discussion_date', true ),
get_post_meta( $id, 'phase_2_rsm_visit_1_date', true ),
get_post_meta( $id, 'phase_2_rsm_visit_1_upload_photos', true ),
get_post_meta( $id, 'phase_2_rsm_visit_2_date', true ),
get_post_meta( $id, 'phase_2_rsm_visit_2_upload_photos', true ),
get_post_meta( $id, 'phase_2_rsm_visit_3_date', true ),
get_post_meta( $id, 'phase_2_rsm_visit_3_upload_photos', true ),
get_post_meta( $id, 'phase_2_rsm_visit_3_comments', true ),
get_post_meta( $id, 'phase_2_rsm_visit_4_date', true ),
get_post_meta( $id, 'phase_2_rsm_visit_4_upload_photos', true ),
get_post_meta( $id, 'phase_2_rsm_visit_4_comments', true ),
$select_label( 'phase_2_residue_degradation_observed', (string) get_post_meta( $id, 'phase_2_residue_degradation_observed', true ) ),
$select_label( 'phase_2_emergence_stand_collected', (string) get_post_meta( $id, 'phase_2_emergence_stand_collected', true ) ),
get_post_meta( $id, 'phase_2_stand_count_1_treated', true ),
get_post_meta( $id, 'phase_2_stand_count_2_treated', true ),
get_post_meta( $id, 'phase_2_stand_count_3_treated', true ),
get_post_meta( $id, 'phase_2_stand_count_1_untreated', true ),
get_post_meta( $id, 'phase_2_stand_count_2_untreated', true ),
get_post_meta( $id, 'phase_2_stand_count_3_untreated', true ),
get_post_meta( $id, 'phase_2_stand_count_data', true ),
get_post_meta( $id, 'phase_2_most_significant_visual_difference', true ),
$select_label( 'phase_2_emergence_flag_test', (string) get_post_meta( $id, 'phase_2_emergence_flag_test', true ) ),
$select_label( 'phase_2_pictures_at_application', (string) get_post_meta( $id, 'phase_2_pictures_at_application', true ) ),
$select_label( 'phase_2_pictures_at_planting', (string) get_post_meta( $id, 'phase_2_pictures_at_planting', true ) ),
$select_label( 'phase_2_pictures_in_season_harvest', (string) get_post_meta( $id, 'phase_2_pictures_in_season_harvest', true ) ),
$select_label( 'phase_2_pictures_at_harvest', (string) get_post_meta( $id, 'phase_2_pictures_at_harvest', true ) ),
$select_label( 'phase_2_drone_images_available', (string) get_post_meta( $id, 'phase_2_drone_images_available', true ) ),
$select_label( 'phase_2_grower_retailer_testimonials', (string) get_post_meta( $id, 'phase_2_grower_retailer_testimonials', true ) ),
get_post_meta( $id, 'phase_2_grower_retailer_comments', true ),
$select_label( 'phase_3_event_type', (string) get_post_meta( $id, 'phase_3_event_type', true ) ),
get_post_meta( $id, 'phase_3_event_date', true ),
get_post_meta( $id, 'phase_3_event_location', true ),
get_post_meta( $id, 'phase_3_attendee_count', true ),
	get_post_meta( $id, 'phase_3_tillage_type', true ),
	get_post_meta( $id, 'phase_3_soil_temp_f_at_application', true ),
	get_post_meta( $id, 'phase_3_carrier_volume_gal', true ),
	get_post_meta( $id, 'phase_3_tank_mix_partners', true ),
	get_post_meta( $id, 'phase_3_planting_date', true ),
	get_post_meta( $id, 'phase_3_hybrid_variety', true ),
	get_post_meta( $id, 'phase_3_planting_population', true ),
	get_post_meta( $id, 'phase_3_row_spacing_in', true ),
	get_post_meta( $id, 'phase_3_planting_speed_mph', true ),
	get_post_meta( $id, 'phase_3_plant_heights_avg_untreated_v7_in', true ),
	get_post_meta( $id, 'phase_3_plant_heights_avg_treated_v7_in', true ),
	get_post_meta( $id, 'phase_3_stalk_diameter_untreated_v7_mm', true ),
	get_post_meta( $id, 'phase_3_stalk_diameter_treated_v7_mm2', true ),
	get_post_meta( $id, 'phase_3_yield_untreated_bu_ac', true ),
	get_post_meta( $id, 'phase_3_yield_treated_bu_ac', true ),
	get_post_meta( $id, 'phase_3_moisture_untreated_percent', true ),
	get_post_meta( $id, 'phase_3_moisture_treated_percent', true ),
	get_post_meta( $id, 'phase_3_test_weight_untreated_lbs_bu', true ),
	get_post_meta( $id, 'phase_3_test_weight_treated_lbs_bu', true ),
	$select_label( 'phase_3_as_applied_gis_data', (string) get_post_meta( $id, 'phase_3_as_applied_gis_data', true ) ),
	$select_label( 'phase_3_planting_gis_data', (string) get_post_meta( $id, 'phase_3_planting_gis_data', true ) ),
	$select_label( 'phase_3_harvest_gis_data', (string) get_post_meta( $id, 'phase_3_harvest_gis_data', true ) ),
	get_post_meta( $id, 'phase_3_agronomy_comments', true ),
];
}
