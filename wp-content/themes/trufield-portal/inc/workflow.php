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

function trufield_get_required_fields( int $phase ): array {
$required = [
1 => [
'retailer_name',
'farm_name',
'field_name',
'field_trial_contact',
'phase_1_state_region',
'phase_1_product_being_tested',
'phase_1_application_type',
'phase_1_application_date',
'phase_1_application_rate',
'phase_1_trial_design',
'phase_1_growth_stage_at_application',
'phase_1_weather_conditions_at_application',
'phase_1_soil_conditions_at_application',
'phase_1_trial_type',
'phase_1_treated_size_acres',
'phase_1_carrier_volume_gal',
'phase_1_protocol_version',
],
2 => [
'phase_2_application_type',
'phase_2_application_date',
'phase_2_verify_acres_treated',
'phase_2_verify_carrier_volume_gal',
'phase_2_soil_temp_f',
'phase_2_emergence',
'phase_2_stand_count',
'phase_2_plant_heights',
'phase_2_required_photos',
],
3 => [
'phase_3_yield_bu_ac',
'phase_3_moisture_percent',
'phase_3_test_weight_lbs_bu',
'phase_3_event_date',
'phase_3_event_type',
'phase_3_event_location',
'phase_3_attendee_count',
'phase_3_required_event_media',
],
];

return $required[ $phase ] ?? [];
}

function trufield_field_labels(): array {
return [
'retailer_name'                       => 'Retailer Name',
'farm_name'                           => 'Grower Name / Farm Name',
'field_trial_contact'                 => 'Field Trial Contact',
'field_name'                          => 'Field Name / Field ID',
'field_location_address'              => 'Field Location Address',
'field_location_lat'                  => 'Field Latitude',
'field_location_lng'                  => 'Field Longitude',
'field_location_manual_override'      => 'Address unavailable - manual coordinate override',
'phase_1_state_region'                => 'State / Region',
'phase_1_product_being_tested'        => 'Product Being Tested',
'phase_1_application_type'            => 'Application Type',
'phase_1_application_date'            => 'Application Date',
'phase_1_application_rate'            => 'Application Rate',
'phase_1_trial_design'                => 'Trial Design',
'phase_1_growth_stage_at_application' => 'Growth Stage at Application',
'phase_1_weather_conditions_at_application' => 'Weather Conditions at Application',
'phase_1_soil_conditions_at_application' => 'Soil Conditions at Application',
'phase_1_field_overview_photo'        => 'Field Overview Photo',
'phase_1_trial_type'                  => 'Trial Type',
'phase_1_treated_size_acres'          => 'Treated Size (Acres)',
'phase_1_carrier_volume_gal'          => 'Carrier Volume (Gal)',
'phase_1_protocol_version'            => 'Protocol Version',
'phase_1_hybrid_variety'              => 'Hybrid Variety',
'phase_1_planting_date'               => 'Planting Date',
'phase_1_planting_population'         => 'Planting Population',
'phase_1_row_spacing'                 => 'Row Spacing',
'phase_1_planting_speed'              => 'Planting Speed',
'phase_2_application_type'            => 'Application Type',
'phase_2_application_date'            => 'Application Date',
'phase_2_verify_acres_treated'        => 'Verify Acres Treated',
'phase_2_verify_carrier_volume_gal'   => 'Verify Carrier Volume (Gal)',
'phase_2_soil_temp_f'                 => 'Soil Temp (F)',
'phase_2_emergence'                   => 'Emergence',
'phase_2_stand_count'                 => 'Stand Count',
'phase_2_plant_heights'               => 'Plant Heights',
'phase_2_required_photos'             => 'Required Photos',
'phase_2_emergence_stand_uniformity'  => 'Emergence / Stand Uniformity',
'phase_2_plant_vigor'                 => 'Plant Vigor',
'phase_2_residue_degradation'         => 'Residue Degradation',
'phase_2_app_machine_data'            => 'App Machine Data',
'phase_2_planting_machine_data'       => 'Planting Machine Data',
'phase_2_optional_video'              => 'Optional Video',
'phase_2_optional_timelapse'          => 'Optional Timelapse',
'phase_2_optional_drone_media'        => 'Optional Drone Media',
'phase_2_testimonial'                 => 'Testimonial',
'phase_2_pictures_at_application'     => 'Pictures at Application',
'phase_2_pictures_at_planting'        => 'Pictures at Planting',
'phase_3_yield_bu_ac'                 => 'Yield (bu/ac)',
'phase_3_moisture_percent'            => 'Moisture (%)',
'phase_3_test_weight_lbs_bu'          => 'Test Weight (lbs/bu)',
'phase_3_stalk_diameter'              => 'Stalk Diameter',
'phase_3_root_vigor'                  => 'Root Vigor',
'phase_3_harvest_photos'              => 'Harvest Photos',
'phase_3_comments'                    => 'Comments',
'phase_3_event_date'                  => 'Event Date',
'phase_3_event_type'                  => 'Event Type',
'phase_3_event_location'              => 'Event Location',
'phase_3_attendee_count'              => 'Attendee Count',
'phase_3_required_event_media'        => 'Required Event Media',
'phase_3_optional_video'              => 'Optional Video',
'phase_3_testimonial'                 => 'Testimonial',
];
}

function trufield_phase_field_schema(): array {
return [
'field_location_address' => [ 'type' => 'text' ],
'field_location_lat' => [ 'type' => 'number' ],
'field_location_lng' => [ 'type' => 'number' ],
'field_location_manual_override' => [ 'type' => 'boolean' ],
'phase_1_state_region' => [ 'type' => 'text' ],
'phase_1_product_being_tested' => [ 'type' => 'text' ],
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
'standard'    => 'Standard Trial',
'split_field' => 'Split Field',
'replicated'  => 'Replicated',
'on_farm'     => 'On-Farm',
],
],
'phase_1_treated_size_acres' => [ 'type' => 'number' ],
'phase_1_carrier_volume_gal' => [ 'type' => 'number' ],
'phase_1_protocol_version' => [ 'type' => 'text' ],
'phase_1_hybrid_variety' => [ 'type' => 'text' ],
'phase_1_planting_date' => [ 'type' => 'date' ],
'phase_1_planting_population' => [ 'type' => 'integer' ],
'phase_1_row_spacing' => [ 'type' => 'number' ],
'phase_1_planting_speed' => [ 'type' => 'number' ],
'phase_2_application_type' => [
'type'    => 'select',
'options' => [
'in_furrow'      => 'In-Furrow',
'seed_treatment' => 'Seed Treatment',
'foliar'         => 'Foliar',
],
],
'phase_2_application_date' => [ 'type' => 'date' ],
'phase_2_verify_acres_treated' => [ 'type' => 'number' ],
'phase_2_verify_carrier_volume_gal' => [ 'type' => 'number' ],
'phase_2_soil_temp_f' => [ 'type' => 'number' ],
'phase_2_emergence' => [ 'type' => 'text' ],
'phase_2_stand_count' => [ 'type' => 'integer' ],
'phase_2_plant_heights' => [ 'type' => 'text' ],
'phase_2_required_photos' => [ 'type' => 'textarea' ],
'phase_2_emergence_stand_uniformity' => [
'type'    => 'select',
'options' => [
'excellent' => 'Excellent',
'good'      => 'Good',
'fair'      => 'Fair',
'poor'      => 'Poor',
],
],
'phase_2_plant_vigor' => [
'type'    => 'select',
'options' => [
'excellent' => 'Excellent',
'good'      => 'Good',
'fair'      => 'Fair',
'poor'      => 'Poor',
],
],
'phase_2_residue_degradation' => [
'type'    => 'select',
'options' => [
'yes' => 'Yes',
'no'  => 'No',
],
],
'phase_2_app_machine_data' => [ 'type' => 'url' ],
'phase_2_planting_machine_data' => [ 'type' => 'url' ],
'phase_2_optional_video' => [ 'type' => 'url' ],
'phase_2_optional_timelapse' => [ 'type' => 'url' ],
'phase_2_optional_drone_media' => [ 'type' => 'url' ],
'phase_2_testimonial' => [ 'type' => 'textarea' ],
'phase_2_pictures_at_application' => [ 'type' => 'textarea' ],
'phase_2_pictures_at_planting' => [ 'type' => 'textarea' ],
'phase_3_yield_bu_ac' => [ 'type' => 'number' ],
'phase_3_moisture_percent' => [ 'type' => 'number' ],
'phase_3_test_weight_lbs_bu' => [ 'type' => 'number' ],
'phase_3_stalk_diameter' => [ 'type' => 'number' ],
'phase_3_root_vigor' => [
'type'    => 'select',
'options' => [
'excellent' => 'Excellent',
'good'      => 'Good',
'fair'      => 'Fair',
'poor'      => 'Poor',
],
],
'phase_3_harvest_photos' => [ 'type' => 'textarea' ],
'phase_3_comments' => [ 'type' => 'textarea' ],
'phase_3_event_date' => [ 'type' => 'date' ],
'phase_3_event_type' => [
'type'    => 'select',
'options' => [
'field_day'      => 'Field Day',
'grower_meeting' => 'Grower Meeting',
'dealer_meeting' => 'Dealer Meeting',
'other'          => 'Other',
],
],
'phase_3_event_location' => [ 'type' => 'text' ],
'phase_3_attendee_count' => [ 'type' => 'integer' ],
'phase_3_required_event_media' => [ 'type' => 'textarea' ],
'phase_3_optional_video' => [ 'type' => 'url' ],
'phase_3_testimonial' => [ 'type' => 'textarea' ],
];
}

function trufield_location_override_enabled( int $post_id ): bool {
	return (bool) get_post_meta( $post_id, 'field_location_manual_override', true );
}

function trufield_get_missing_required_fields( int $post_id, int $phase ): array {
$labels  = trufield_field_labels();
$missing = [];

foreach ( trufield_get_required_fields( $phase ) as $field ) {
$value = get_post_meta( $post_id, $field, true );
if ( trim( (string) $value ) === '' ) {
$missing[] = $labels[ $field ] ?? $field;
}
}

	if ( 1 === $phase ) {
		$address  = trim( (string) get_post_meta( $post_id, 'field_location_address', true ) );
		$lat      = trim( (string) get_post_meta( $post_id, 'field_location_lat', true ) );
		$lng      = trim( (string) get_post_meta( $post_id, 'field_location_lng', true ) );
		$override = trufield_location_override_enabled( $post_id );

		if ( ! $override && '' === $address ) {
			$missing[] = $labels['field_location_address'] ?? 'Field Location Address';
		}

		if ( '' === $lat ) {
			$missing[] = $labels['field_location_lat'] ?? 'Field Latitude';
		}

		if ( '' === $lng ) {
			$missing[] = $labels['field_location_lng'] ?? 'Field Longitude';
		}
	}

return $missing;
}

function trufield_all_required_fields_present( int $post_id, int $phase ): bool {
	return [] === trufield_get_missing_required_fields( $post_id, $phase );
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
return (bool) get_post_meta( $post_id, "phase_{$previous_phase}_verified", true );
}

function trufield_can_edit_phase( int $post_id, int $phase, int $user_id ): bool {
if ( trufield_user_is_admin( $user_id ) ) {
return true;
}

$user = get_userdata( $user_id );
if ( $user && in_array( 'leadership', (array) $user->roles, true ) ) {
return false;
}

$assigned = (int) get_post_meta( $post_id, 'assigned_sales_rep', true );
if ( $assigned !== $user_id ) {
return false;
}

if ( ! trufield_prerequisite_met( $post_id, $phase ) ) {
return false;
}

return trufield_get_phase_status( $post_id, $phase ) !== 'completed';
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
				__( 'Before you can mark Phase %d complete, add the remaining required fields: ', 'trufield-portal' ),
				$phase
			) . implode( ', ', trufield_get_missing_required_fields( $post_id, $phase ) )
		);
	}

	$current = trufield_get_phase_status( $post_id, $phase );
	if ( $current === 'completed' && ! trufield_user_is_admin( $user_id ) ) {
		return new WP_Error( 'trufield_already_completed', __( 'This phase has already been submitted.', 'trufield-portal' ) );
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
'farm_name',
'field_trial_contact',
'field_name',
'field_location_address',
'field_location_lat',
'field_location_lng',
'field_location_manual_override',
'phase_1_state_region',
'phase_1_product_being_tested',
'phase_1_application_type',
'phase_1_application_date',
'phase_1_application_rate',
'phase_1_trial_design',
'phase_1_growth_stage_at_application',
'phase_1_weather_conditions_at_application',
'phase_1_soil_conditions_at_application',
'phase_1_field_overview_photo',
'phase_1_trial_type',
'phase_1_treated_size_acres',
'phase_1_carrier_volume_gal',
'phase_1_protocol_version',
'phase_1_hybrid_variety',
'phase_1_planting_date',
'phase_1_planting_population',
'phase_1_row_spacing',
'phase_1_planting_speed',
],
2 => [
'phase_2_application_type',
'phase_2_application_date',
'phase_2_verify_acres_treated',
'phase_2_verify_carrier_volume_gal',
'phase_2_soil_temp_f',
'phase_2_emergence',
'phase_2_stand_count',
'phase_2_plant_heights',
'phase_2_emergence_stand_uniformity',
'phase_2_plant_vigor',
'phase_2_residue_degradation',
'phase_2_app_machine_data',
'phase_2_planting_machine_data',
'phase_2_required_photos',
'phase_2_optional_video',
'phase_2_optional_timelapse',
'phase_2_optional_drone_media',
'phase_2_testimonial',
'phase_2_pictures_at_application',
'phase_2_pictures_at_planting',
],
3 => [
'phase_3_yield_bu_ac',
'phase_3_moisture_percent',
'phase_3_test_weight_lbs_bu',
'phase_3_stalk_diameter',
'phase_3_root_vigor',
'phase_3_harvest_photos',
'phase_3_comments',
'phase_3_event_date',
'phase_3_event_type',
'phase_3_event_location',
'phase_3_attendee_count',
'phase_3_required_event_media',
'phase_3_optional_video',
'phase_3_testimonial',
],
];

return $fields[ $phase ] ?? [];
}

function trufield_validate_date_value( string $value ): string {
$value = sanitize_text_field( $value );
if ( $value === '' ) {
return '';
}

$date = DateTime::createFromFormat( 'Y-m-d', $value );
$errors = DateTime::getLastErrors();
if ( ! $date || ( is_array( $errors ) && ( ! empty( $errors['warning_count'] ) || ! empty( $errors['error_count'] ) ) ) ) {
return '';
}

return $date->format( 'Y-m-d' ) === $value ? $value : '';
}

function trufield_sanitize_phase_field_value( string $field, $raw_value ) {
$schema = trufield_phase_field_schema();
$type   = $schema[ $field ]['type'] ?? 'text';
$value  = is_string( $raw_value ) ? wp_unslash( $raw_value ) : $raw_value;

switch ( $type ) {
case 'boolean':
	return ! empty( $value ) ? '1' : '';

case 'integer':
$value = trim( (string) $value );
return $value === '' ? '' : absint( $value );

case 'number':
$value = trim( (string) $value );
return $value === '' ? '' : (float) $value;

case 'url':
$value = trim( (string) $value );
return $value === '' ? '' : esc_url_raw( $value );

case 'textarea':
$value = sanitize_textarea_field( (string) $value );
return trim( $value );

case 'select':
$value   = sanitize_key( (string) $value );
$options = array_keys( $schema[ $field ]['options'] ?? [] );
return in_array( $value, $options, true ) ? $value : '';

case 'date':
return trufield_validate_date_value( (string) $value );

case 'text':
default:
$value = sanitize_text_field( (string) $value );
return trim( $value );
}
}

add_action( 'admin_post_trufield_save_phase', 'trufield_handle_save_phase' );
add_action( 'admin_post_nopriv_trufield_save_phase', 'trufield_handle_save_phase_nopriv' );
add_action( 'admin_post_trufield_reopen_phase', 'trufield_handle_reopen_phase' );
add_action( 'admin_post_trufield_verify_phase', 'trufield_handle_verify_phase' );

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

	if ( ! trufield_can_edit_phase( $post_id, $phase, $user_id ) ) {
		wp_die( esc_html__( 'You do not have permission to update this phase.', 'trufield-portal' ), 403 );
	}

$editable = trufield_rep_editable_phase_fields( $phase );
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

$redirect = wp_get_referer() ?: get_permalink( $post_id );
$action   = sanitize_key( $_POST['phase_action'] ?? 'save' );

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

	if ( trufield_get_phase_status( $post_id, $phase ) === 'pending' ) {
		update_post_meta( $post_id, "phase_{$phase}_status", 'in_progress' );
	}
	update_post_meta( $post_id, 'current_phase', min( 3, max( 1, $phase ) ) );

	wp_safe_redirect( add_query_arg( 'tf_success', 'address_verified', $redirect ) );
	exit;
}

if ( $action === 'complete' ) {
$result = trufield_complete_phase( $post_id, $phase, $user_id );
if ( is_wp_error( $result ) ) {
wp_safe_redirect( add_query_arg( 'tf_error', rawurlencode( $result->get_error_message() ), $redirect ) );
exit;
}

wp_safe_redirect( add_query_arg( 'tf_success', "phase_{$phase}_completed", $redirect ) );
exit;
}

if ( trufield_get_phase_status( $post_id, $phase ) === 'pending' ) {
update_post_meta( $post_id, "phase_{$phase}_status", 'in_progress' );
}
update_post_meta( $post_id, 'current_phase', min( 3, max( 1, $phase ) ) );

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

wp_safe_redirect( admin_url( "post.php?post={$post_id}&action=edit&tf_reopened={$phase}" ) );
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

wp_safe_redirect( admin_url( "post.php?post={$post_id}&action=edit&tf_verified={$phase}" ) );
exit;
}
