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
'ID',
'Field Name',
'Record Status',
'Validation Status',
'Assigned Rep',
'RSM/BAM',
'FSA',
'Retailer Name',
'Retailer Branch Location',
'Farm Name',
'Field Trial Contact',
'Contact Phone',
'Field Trial Contact Email',
'Field Name',
'Field Location',
'State',
'Phase 1 Status',
'Ph1 Verified',
'Ph1 Trial Type',
'Ph1 Treated Acres',
'Ph1 Applied Rate (oz/ac)',
'Ph1 Protocol',
'Ph1 Application Timing',
'Ph1 Application Date',
'Ph1 Retailer Product Training/Discussion Date',
'Phase 2 Status',
'Ph2 Verified',
'Ph2 RSM Visit 1 Date',
'Ph2 RSM Visit 1 Photos',
'Ph2 RSM Visit 1 Photos Taken Date',
'Ph2 RSM Visit 1 Photo Type',
'Ph2 RSM Visit 2 Date',
'Ph2 RSM Visit 2 Photos',
'Ph2 RSM Visit 2 Photos Taken Date',
'Ph2 RSM Visit 2 Photo Type',
'Ph2 RSM Visit 3 Date',
'Ph2 RSM Visit 3 Photos',
'Ph2 RSM Visit 3 Photos Taken Date',
'Ph2 RSM Visit 3 Photo Type',
'Ph2 RSM Visit 3 Comments',
'Ph2 RSM Visit 4 Date',
'Ph2 RSM Visit 4 Photos',
'Ph2 RSM Visit 4 Photos Taken Date',
'Ph2 RSM Visit 4 Photo Type',
'Ph2 RSM Visit 4 Comments',
'Ph2 Residue Degradation Observed',
'Ph2 Emergence Stand Collected',
'Ph2 Stand Count Deltas',
'Ph2 Avg Stand Count Treated',
'Ph2 Avg Stand Count Untreated',
'Ph2 Most Significant Visual Difference',
'Phase 3 Status',
'Ph3 Verified',
'Ph3 Yield (bu/ac)',
'Ph3 Moisture %',
'Ph3 Test Weight',
'Ph3 Event Date',
'Ph3 Event Type',
'Ph3 Event Location',
'Ph3 Attendees',
];
}

function trufield_csv_row( WP_Post $post ): array {
$id      = $post->ID;
$rep_id  = (int) get_post_meta( $id, 'assigned_sales_rep', true );
$rep     = $rep_id ? get_userdata( $rep_id ) : false;
$schema  = trufield_phase_field_schema();
$yes_no  = static fn( bool $value ): string => $value ? 'Yes' : 'No';
$select_label = static function ( string $field, string $value ) use ( $schema ): string {
if ( $value === '' ) {
return '';
}
return $schema[ $field ]['options'][ $value ] ?? $value;
};

return [
$id,
$post->post_title,
get_post_meta( $id, 'record_status', true ),
get_post_meta( $id, 'validation_status', true ),
$rep ? $rep->display_name : '',
trufield_resolve_assignment_user_label( get_post_meta( $id, 'rsm_bam', true ) ),
trufield_resolve_assignment_user_label( get_post_meta( $id, 'fsa', true ) ),
get_post_meta( $id, 'retailer_name', true ),
get_post_meta( $id, 'retailer_branch_location', true ),
get_post_meta( $id, 'farm_name', true ),
get_post_meta( $id, 'field_trial_contact', true ),
get_post_meta( $id, 'contact_phone', true ),
get_post_meta( $id, 'field_trial_contact_email', true ),
get_post_meta( $id, 'field_name', true ),
get_post_meta( $id, 'field_location_address', true ),
get_post_meta( $id, 'phase_1_state_region', true ),
trufield_get_phase_status( $id, 1 ),
$yes_no( (bool) get_post_meta( $id, 'phase_1_verified', true ) ),
$select_label( 'phase_1_trial_type', (string) get_post_meta( $id, 'phase_1_trial_type', true ) ),
get_post_meta( $id, 'phase_1_treated_size_acres', true ),
get_post_meta( $id, 'phase_1_application_rate', true ),
$select_label( 'phase_1_protocol_version', (string) get_post_meta( $id, 'phase_1_protocol_version', true ) ),
$select_label( 'phase_1_application_timing', (string) get_post_meta( $id, 'phase_1_application_timing', true ) ),
get_post_meta( $id, 'phase_1_application_date', true ),
get_post_meta( $id, 'phase_1_retailer_training_discussion_date', true ),
trufield_get_phase_status( $id, 2 ),
$yes_no( (bool) get_post_meta( $id, 'phase_2_verified', true ) ),
get_post_meta( $id, 'phase_2_rsm_visit_1_date', true ),
get_post_meta( $id, 'phase_2_rsm_visit_1_upload_photos', true ),
get_post_meta( $id, 'phase_2_rsm_visit_1_photos_taken_date', true ),
$select_label( 'phase_2_rsm_visit_1_photo_type', (string) get_post_meta( $id, 'phase_2_rsm_visit_1_photo_type', true ) ),
get_post_meta( $id, 'phase_2_rsm_visit_2_date', true ),
get_post_meta( $id, 'phase_2_rsm_visit_2_upload_photos', true ),
get_post_meta( $id, 'phase_2_rsm_visit_2_photos_taken_date', true ),
$select_label( 'phase_2_rsm_visit_2_photo_type', (string) get_post_meta( $id, 'phase_2_rsm_visit_2_photo_type', true ) ),
get_post_meta( $id, 'phase_2_rsm_visit_3_date', true ),
get_post_meta( $id, 'phase_2_rsm_visit_3_upload_photos', true ),
get_post_meta( $id, 'phase_2_rsm_visit_3_photos_taken_date', true ),
$select_label( 'phase_2_rsm_visit_3_photo_type', (string) get_post_meta( $id, 'phase_2_rsm_visit_3_photo_type', true ) ),
get_post_meta( $id, 'phase_2_rsm_visit_3_comments', true ),
get_post_meta( $id, 'phase_2_rsm_visit_4_date', true ),
get_post_meta( $id, 'phase_2_rsm_visit_4_upload_photos', true ),
get_post_meta( $id, 'phase_2_rsm_visit_4_photos_taken_date', true ),
$select_label( 'phase_2_rsm_visit_4_photo_type', (string) get_post_meta( $id, 'phase_2_rsm_visit_4_photo_type', true ) ),
get_post_meta( $id, 'phase_2_rsm_visit_4_comments', true ),
$select_label( 'phase_2_residue_degradation_observed', (string) get_post_meta( $id, 'phase_2_residue_degradation_observed', true ) ),
$select_label( 'phase_2_emergence_stand_collected', (string) get_post_meta( $id, 'phase_2_emergence_stand_collected', true ) ),
get_post_meta( $id, 'phase_2_stand_count_data', true ),
get_post_meta( $id, 'phase_2_average_stand_count_treated', true ),
get_post_meta( $id, 'phase_2_average_stand_count_untreated', true ),
get_post_meta( $id, 'phase_2_most_significant_visual_difference', true ),
trufield_get_phase_status( $id, 3 ),
$yes_no( (bool) get_post_meta( $id, 'phase_3_verified', true ) ),
get_post_meta( $id, 'phase_3_yield_bu_ac', true ),
get_post_meta( $id, 'phase_3_moisture_percent', true ),
get_post_meta( $id, 'phase_3_test_weight_lbs_bu', true ),
get_post_meta( $id, 'phase_3_event_date', true ),
$select_label( 'phase_3_event_type', (string) get_post_meta( $id, 'phase_3_event_type', true ) ),
get_post_meta( $id, 'phase_3_event_location', true ),
get_post_meta( $id, 'phase_3_attendee_count', true ),
];
}
