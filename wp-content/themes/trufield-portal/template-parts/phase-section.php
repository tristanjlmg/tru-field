<?php
/**
 * TruField Portal — Template Part: Phase Section
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

$post_id        = (int) ( $args['post_id'] ?? 0 );
$phase          = (int) ( $args['phase'] ?? 1 );
$phase_title    = $args['phase_title'] ?? sprintf( 'Step %d', $phase );
$user_id        = (int) ( $args['user_id'] ?? 0 );
$is_admin       = (bool) ( $args['is_admin'] ?? false );
$phase_verified = (array) ( $args['phase_verified'] ?? [] );

$status       = trufield_get_phase_status( $post_id, $phase );
$is_verified  = isset( $phase_verified[ $phase ] ) ? (bool) $phase_verified[ $phase ] : (bool) get_post_meta( $post_id, "phase_{$phase}_verified", true );
$can_edit     = trufield_can_edit_phase( $post_id, $phase, $user_id );
$prereq_met   = trufield_prerequisite_met( $post_id, $phase );
$completed_at = get_post_meta( $post_id, "phase_{$phase}_completed_at", true );
$verified_at  = get_post_meta( $post_id, "phase_{$phase}_verified_at", true );
$missing      = trufield_get_missing_required_fields( $post_id, $phase );
$required_ok  = empty( $missing );
$validation_missing = trufield_get_missing_validation_fields( $post_id, $phase );
$validation_ok = empty( $validation_missing );
$labels       = trufield_field_labels();
$schema       = trufield_phase_field_schema();
$phase_label  = sprintf( __( 'Phase %d', 'trufield-portal' ), $phase );
$location_override = 1 === $phase ? trufield_location_override_enabled( $post_id ) : false;

$field_groups = [
1 => [
'required' => [
'retailer_name'              => [ 'input' => 'text', 'placeholder' => 'Retailer name' ],
'field_location_address'     => [ 'input' => 'textarea', 'rows' => 2 ],
'phase_1_state_region'       => [ 'input' => 'text', 'placeholder' => 'State' ],
'field_trial_contact'        => [ 'input' => 'text', 'placeholder' => 'First and last name' ],
'contact_phone'              => [ 'input' => 'text', 'placeholder' => '(555) 555-5555' ],
'field_trial_contact_email'  => [ 'input' => 'email', 'placeholder' => 'name@example.com' ],
'phase_1_application_date'   => [ 'input' => 'date' ],
'phase_1_application_rate'   => [ 'input' => 'text', 'placeholder' => 'e.g. 32 oz/ac' ],
'phase_1_trial_type'         => [ 'input' => 'select', 'placeholder' => '', 'step' => null, 'min' => null ],
'phase_1_treated_size_acres' => [ 'input' => 'number', 'step' => '0.01', 'min' => '0' ],
'phase_1_protocol_version'   => [ 'input' => 'select' ],
'phase_1_application_timing' => [ 'input' => 'select' ],
'phase_1_retailer_training_discussion_date' => [ 'input' => 'date' ],
],
'optional' => [
'retailer_key_contact'        => [ 'input' => 'text', 'placeholder' => 'Retailer contact name' ],
'farm_name'                  => [ 'input' => 'text', 'placeholder' => 'Grower or farm name' ],
'field_name'                 => [ 'input' => 'text', 'placeholder' => 'Field name or identifier' ],
'phase_1_product_being_tested' => [ 'input' => 'text', 'placeholder' => 'Product name' ],
'phase_1_application_type'   => [ 'input' => 'select' ],
'phase_1_trial_design'       => [ 'input' => 'select' ],
'phase_1_growth_stage_at_application' => [ 'input' => 'text', 'placeholder' => 'e.g. V3' ],
'phase_1_weather_conditions_at_application' => [ 'input' => 'textarea', 'rows' => 2, 'placeholder' => 'Weather conditions at application' ],
'phase_1_soil_conditions_at_application' => [ 'input' => 'textarea', 'rows' => 2, 'placeholder' => 'Soil conditions at application' ],
'phase_1_carrier_volume_gal' => [ 'input' => 'number', 'step' => '0.1', 'min' => '0' ],
'phase_1_hybrid_variety'      => [ 'input' => 'text' ],
'phase_1_planting_date'       => [ 'input' => 'date' ],
'phase_1_planting_population' => [ 'input' => 'number', 'min' => '0', 'step' => '1' ],
'phase_1_row_spacing'         => [ 'input' => 'number', 'min' => '0', 'step' => '0.1' ],
'phase_1_planting_speed'      => [ 'input' => 'number', 'min' => '0', 'step' => '0.1' ],
'phase_1_field_overview_photo' => [ 'input' => 'file', 'accept' => 'image/*', 'help' => 'Upload a field overview photo from your device. JPG, PNG, GIF, and WebP are supported.' ],
],
],
2 => [
'required' => [
'phase_2_rsm_visit_1_date'                 => [ 'input' => 'date' ],
'phase_2_rsm_visit_1_upload_photos'        => [ 'input' => 'file', 'accept' => 'image/*', 'help' => 'Upload the visit 1 photo for this trial.' ],
'phase_2_rsm_visit_2_date'                 => [ 'input' => 'date' ],
'phase_2_rsm_visit_2_upload_photos'        => [ 'input' => 'file', 'accept' => 'image/*', 'help' => 'Upload the visit 2 photo for this trial.' ],
'phase_2_residue_degradation_observed'     => [ 'input' => 'select' ],
'phase_2_emergence_stand_collected'        => [ 'input' => 'select' ],
'phase_2_stand_count_data'                 => [ 'input' => 'text', 'placeholder' => 'Describe the stand count data collected' ],
'phase_2_average_stand_count_treated'      => [ 'input' => 'number', 'min' => '0', 'step' => '0.01' ],
'phase_2_average_stand_count_untreated'    => [ 'input' => 'number', 'min' => '0', 'step' => '0.01' ],
'phase_2_most_significant_visual_difference' => [ 'input' => 'textarea', 'rows' => 4, 'placeholder' => 'What was the most significant visual difference you saw?' ],
],
'optional' => [],
],
3 => [
'required' => [
'phase_3_yield_bu_ac'          => [ 'input' => 'number', 'min' => '0', 'step' => '0.01' ],
'phase_3_moisture_percent'     => [ 'input' => 'number', 'min' => '0', 'max' => '100', 'step' => '0.1' ],
'phase_3_test_weight_lbs_bu'   => [ 'input' => 'number', 'min' => '0', 'step' => '0.01' ],
'phase_3_event_date'           => [ 'input' => 'date' ],
'phase_3_event_type'           => [ 'input' => 'select' ],
'phase_3_event_location'       => [ 'input' => 'text' ],
'phase_3_attendee_count'       => [ 'input' => 'number', 'min' => '0', 'step' => '1' ],
'phase_3_required_event_media' => [ 'input' => 'textarea', 'rows' => 2, 'help' => 'Enter event media URLs or descriptions, one per line.' ],
],
'optional' => [
'phase_3_stalk_diameter'    => [ 'input' => 'number', 'step' => '0.01' ],
'phase_3_root_vigor'        => [ 'input' => 'select' ],
'phase_3_harvest_photos'    => [ 'input' => 'textarea', 'rows' => 2, 'placeholder' => 'Harvest photo URLs' ],
'phase_3_comments'          => [ 'input' => 'textarea', 'rows' => 3 ],
'phase_3_optional_video'    => [ 'input' => 'url', 'placeholder' => 'Video URL (optional)' ],
'phase_3_testimonial'       => [ 'input' => 'textarea', 'rows' => 3 ],
],
],
];

$retailer_name_options = trufield_get_retailer_name_options();
$phase_one_substeps = [];
$phase_two_substeps = [];
$phase_initial_step = max( 1, min( trufield_get_phase_step_count( $phase ), (int) sanitize_key( wp_unslash( $_GET['phase_step'] ?? '1' ) ) ) );
$phase_substeps = [];

if ( 1 === $phase ) {
	$phase_one_substeps = [
		1 => [
			'key'            => 'retailer',
			'title'          => __( 'Retailer Information', 'trufield-portal' ),
			'description'    => __( 'Enter the retailer contact details and verify the field location.', 'trufield-portal' ),
			'required_fields'=> [
				'field_location_address',
				'field_location_lat',
				'field_location_lng',
				'retailer_name',
				'phase_1_state_region',
				'field_trial_contact',
				'contact_phone',
				'field_trial_contact_email',
			],
			'fields'         => [
				'retailer_name',
				'phase_1_state_region',
				'field_trial_contact',
				'contact_phone',
				'field_trial_contact_email',
			],
			'optional_fields'=> [ 'retailer_key_contact' ],
		],
		2 => [
			'key'            => 'field-trial',
			'title'          => __( 'Field Trial Information', 'trufield-portal' ),
			'description'    => __( 'Capture the field setup and trial structure for this record.', 'trufield-portal' ),
			'required_fields'=> [
				'phase_1_trial_type',
				'phase_1_treated_size_acres',
				'phase_1_protocol_version',
				'phase_1_application_timing',
			],
			'fields'         => [
				'phase_1_trial_type',
				'phase_1_treated_size_acres',
				'phase_1_protocol_version',
				'phase_1_application_timing',
			],
			'optional_fields'=> [
				'farm_name',
				'field_name',
				'phase_1_trial_design',
				'phase_1_product_being_tested',
				'phase_1_hybrid_variety',
				'phase_1_planting_date',
				'phase_1_planting_population',
				'phase_1_row_spacing',
				'phase_1_planting_speed',
			],
		],
		3 => [
			'key'            => 'trial',
			'title'          => __( 'Trial Information', 'trufield-portal' ),
			'description'    => __( 'Finish the application details and add any supporting notes or media.', 'trufield-portal' ),
			'required_fields'=> [
				'phase_1_application_date',
				'phase_1_application_rate',
				'phase_1_retailer_training_discussion_date',
			],
			'fields'         => [
				'phase_1_application_date',
				'phase_1_application_rate',
				'phase_1_retailer_training_discussion_date',
			],
			'optional_fields'=> [
				'phase_1_application_type',
				'phase_1_growth_stage_at_application',
				'phase_1_weather_conditions_at_application',
				'phase_1_soil_conditions_at_application',
				'phase_1_carrier_volume_gal',
				'phase_1_field_overview_photo',
			],
		],
	];
	$phase_substeps = $phase_one_substeps;
} elseif ( 2 === $phase ) {
	$phase_two_substeps = [
		1 => [
			'key'            => 'visits',
			'title'          => __( 'RSM Visits & Field Documentation', 'trufield-portal' ),
			'description'    => __( 'Record both RSM visit dates and upload the required visit photos.', 'trufield-portal' ),
			'required_fields'=> [
				'phase_2_rsm_visit_1_date',
				'phase_2_rsm_visit_1_upload_photos',
				'phase_2_rsm_visit_2_date',
				'phase_2_rsm_visit_2_upload_photos',
			],
			'fields'         => [
				'phase_2_rsm_visit_1_date',
				'phase_2_rsm_visit_1_upload_photos',
				'phase_2_rsm_visit_2_date',
				'phase_2_rsm_visit_2_upload_photos',
			],
			'optional_fields'=> [],
		],
		2 => [
			'key'            => 'observations',
			'title'          => __( 'Field Observations', 'trufield-portal' ),
			'description'    => __( 'Capture residue, stand-count observations, and the strongest visible difference in the field.', 'trufield-portal' ),
			'required_fields'=> [
				'phase_2_residue_degradation_observed',
				'phase_2_emergence_stand_collected',
				'phase_2_stand_count_data',
				'phase_2_average_stand_count_treated',
				'phase_2_average_stand_count_untreated',
				'phase_2_most_significant_visual_difference',
			],
			'fields'         => [
				'phase_2_residue_degradation_observed',
				'phase_2_emergence_stand_collected',
				'phase_2_stand_count_data',
				'phase_2_average_stand_count_treated',
				'phase_2_average_stand_count_untreated',
				'phase_2_most_significant_visual_difference',
			],
			'optional_fields'=> [],
		],
	];
	$phase_substeps = $phase_two_substeps;
}

$render_field = static function ( string $field, array $config, bool $required = false ) use ( $post_id, $labels, $schema ): void {
$value       = get_post_meta( $post_id, $field, true );
$label       = $labels[ $field ] ?? $field;
$input_type  = $config['input'] ?? 'text';
$placeholder = $config['placeholder'] ?? '';
$rows        = (int) ( $config['rows'] ?? 3 );
$min         = $config['min'] ?? null;
$max         = $config['max'] ?? null;
$step        = $config['step'] ?? null;
$accept      = $config['accept'] ?? '';
$help        = $config['help'] ?? '';
$attachment_id = (int) get_post_meta( $post_id, trufield_phase_photo_attachment_meta_key( $field ), true );
?>
<div class="tf-field-group">
<label for="<?php echo esc_attr( $field ); ?>">
<?php echo esc_html( $label ); ?>
<?php if ( $required ) : ?>
<span class="tf-required">*</span>
<?php endif; ?>
</label>
<?php if ( $input_type === 'select' ) : ?>
<select id="<?php echo esc_attr( $field ); ?>" name="<?php echo esc_attr( $field ); ?>" class="tf-select">
<option value=""><?php esc_html_e( 'Select…', 'trufield-portal' ); ?></option>
<?php foreach ( $schema[ $field ]['options'] ?? [] as $option_value => $option_label ) : ?>
<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( (string) $value, (string) $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
<?php endforeach; ?>
</select>
<?php elseif ( $input_type === 'textarea' ) : ?>
<textarea id="<?php echo esc_attr( $field ); ?>" name="<?php echo esc_attr( $field ); ?>" class="tf-textarea" rows="<?php echo esc_attr( (string) $rows ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"><?php echo esc_textarea( (string) $value ); ?></textarea>
<?php elseif ( $input_type === 'file' ) : ?>
<div class="tf-upload-field">
<?php if ( $value ) : ?>
<div class="tf-upload-field__preview">
<a href="<?php echo esc_url( (string) $value ); ?>" target="_blank" rel="noopener noreferrer" class="tf-upload-field__image-link">
<img src="<?php echo esc_url( (string) $value ); ?>" alt="<?php echo esc_attr( $label ); ?>" class="tf-upload-field__image">
</a>
<div class="tf-upload-field__meta">
<a href="<?php echo esc_url( (string) $value ); ?>" target="_blank" rel="noopener noreferrer" class="tf-upload-field__link"><?php esc_html_e( 'View current photo', 'trufield-portal' ); ?></a>
<?php if ( $attachment_id > 0 ) : ?>
<span class="tf-upload-field__caption"><?php esc_html_e( 'Stored in the WordPress media library.', 'trufield-portal' ); ?></span>
<?php endif; ?>
<label class="tf-upload-field__remove">
<input type="checkbox" name="<?php echo esc_attr( $field ); ?>_remove" value="1">
<span><?php esc_html_e( 'Remove current photo', 'trufield-portal' ); ?></span>
</label>
</div>
</div>
<?php endif; ?>
<input
type="file"
id="<?php echo esc_attr( $field ); ?>_upload"
name="<?php echo esc_attr( $field ); ?>_upload"
class="tf-input tf-input--file"
<?php echo $accept ? ' accept="' . esc_attr( $accept ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
>
</div>
<?php else : ?>
<input
type="<?php echo esc_attr( $input_type ); ?>"
id="<?php echo esc_attr( $field ); ?>"
name="<?php echo esc_attr( $field ); ?>"
class="tf-input"
value="<?php echo esc_attr( (string) $value ); ?>"
placeholder="<?php echo esc_attr( $placeholder ); ?>"
<?php echo $min !== null ? ' min="' . esc_attr( (string) $min ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $max !== null ? ' max="' . esc_attr( (string) $max ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $step !== null ? ' step="' . esc_attr( (string) $step ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
>
<?php endif; ?>
<?php if ( $help ) : ?>
<small><?php echo esc_html( $help ); ?></small>
<?php endif; ?>
</div>
<?php
};

$render_retailer_name_field = static function ( bool $required = false ) use ( $post_id, $labels, $retailer_name_options ): void {
	$current_value = trim( (string) get_post_meta( $post_id, 'retailer_name', true ) );
	$is_known      = '' !== $current_value && isset( $retailer_name_options[ $current_value ] );
	$selected      = $is_known ? $current_value : ( '' !== $current_value ? 'other' : '' );
	$manual_value  = $is_known ? '' : $current_value;
	?>
	<div class="tf-field-group tf-retailer-picker" data-tf-retailer-picker>
	<label for="retailer_name_select">
	<?php echo esc_html( $labels['retailer_name'] ?? 'Retailer Name' ); ?>
	<?php if ( $required ) : ?>
	<span class="tf-required">*</span>
	<?php endif; ?>
	</label>
	<select id="retailer_name_select" name="retailer_name" class="tf-select" data-tf-retailer-select>
	<option value=""><?php esc_html_e( 'Select…', 'trufield-portal' ); ?></option>
	<?php foreach ( $retailer_name_options as $option_value => $option_label ) : ?>
	<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $selected, $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
	<?php endforeach; ?>
	<option value="other" <?php selected( $selected, 'other' ); ?>><?php esc_html_e( 'Other', 'trufield-portal' ); ?></option>
	</select>
	<div class="tf-retailer-picker__manual" data-tf-retailer-manual <?php echo 'other' === $selected ? '' : 'hidden'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<label for="retailer_name_manual"><?php esc_html_e( 'Enter retailer name', 'trufield-portal' ); ?></label>
	<input
	type="text"
	id="retailer_name_manual"
	name="retailer_name_manual"
	class="tf-input"
	value="<?php echo esc_attr( $manual_value ); ?>"
	placeholder="<?php esc_attr_e( 'Retailer name', 'trufield-portal' ); ?>"
	data-tf-retailer-manual-input
	>
	</div>
	<small><?php esc_html_e( 'Choose Other if the retailer is not listed and enter it manually.', 'trufield-portal' ); ?></small>
	</div>
	<?php
};

$render_phase_one_location = static function () use ( $labels, $post_id, $location_override ): void {
	?>
	<div class="tf-phase-location<?php echo $location_override ? ' is-manual' : ''; ?>" data-tf-location>
	<div class="tf-phase-location__header">
	<div class="tf-phase-location__intro">
	<label class="tf-phase-location__label" for="field_location_address"><?php echo esc_html( $labels['field_location_address'] ?? 'Field Location Address' ); ?><?php if ( ! $location_override ) : ?> <span class="tf-required">*</span><?php endif; ?></label>
	<p class="tf-phase-location__help" data-tf-location-help><?php esc_html_e( 'Search and select a verified address to automatically fill latitude and longitude. If no address is available, turn on manual override and enter coordinates directly.', 'trufield-portal' ); ?></p>
	</div>
	<label class="tf-phase-location__toggle">
	<input type="hidden" name="field_location_manual_override" value="0">
	<input type="checkbox" name="field_location_manual_override" value="1" <?php checked( $location_override ); ?> data-tf-location-override>
	<span><?php esc_html_e( 'Address unavailable, enter coordinates manually', 'trufield-portal' ); ?></span>
	</label>
	</div>
	<div class="tf-phase-location__grid">
	<div class="tf-field-group tf-phase-location__address-wrap">
	<div class="tf-phase-location__address-row">
	<input
	type="text"
	id="field_location_address"
	name="field_location_address"
	class="tf-input"
	value="<?php echo esc_attr( (string) get_post_meta( $post_id, 'field_location_address', true ) ); ?>"
	placeholder="<?php esc_attr_e( 'Search for the field location', 'trufield-portal' ); ?>"
	autocomplete="street-address"
	data-tf-location-address
	>
	<button type="submit" name="phase_action" value="verify_address" class="tf-btn tf-btn--secondary tf-phase-location__verify" formnovalidate data-tf-location-verify><?php esc_html_e( 'Verify Address', 'trufield-portal' ); ?></button>
	</div>
	<small class="tf-phase-location__status" data-tf-location-status><?php esc_html_e( 'Coordinates are required before Phase 1 can be completed.', 'trufield-portal' ); ?></small>
	</div>
	<div class="tf-phase-location__coords">
	<div class="tf-field-group">
	<label for="field_location_lat"><?php echo esc_html( $labels['field_location_lat'] ?? 'Field Latitude' ); ?> <span class="tf-required">*</span></label>
	<input
	type="number"
	id="field_location_lat"
	class="tf-input"
	value="<?php echo esc_attr( (string) get_post_meta( $post_id, 'field_location_lat', true ) ); ?>"
	step="0.000001"
	placeholder="<?php esc_attr_e( 'Latitude', 'trufield-portal' ); ?>"
	<?php echo $location_override ? '' : ' disabled readonly tabindex="-1" aria-disabled="true"'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-tf-location-lat
	>
	<input type="hidden" name="field_location_lat" value="<?php echo esc_attr( (string) get_post_meta( $post_id, 'field_location_lat', true ) ); ?>" data-tf-location-lat-hidden>
	</div>
	<div class="tf-field-group">
	<label for="field_location_lng"><?php echo esc_html( $labels['field_location_lng'] ?? 'Field Longitude' ); ?> <span class="tf-required">*</span></label>
	<input
	type="number"
	id="field_location_lng"
	class="tf-input"
	value="<?php echo esc_attr( (string) get_post_meta( $post_id, 'field_location_lng', true ) ); ?>"
	step="0.000001"
	placeholder="<?php esc_attr_e( 'Longitude', 'trufield-portal' ); ?>"
	<?php echo $location_override ? '' : ' disabled readonly tabindex="-1" aria-disabled="true"'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-tf-location-lng
	>
	<input type="hidden" name="field_location_lng" value="<?php echo esc_attr( (string) get_post_meta( $post_id, 'field_location_lng', true ) ); ?>" data-tf-location-lng-hidden>
	</div>
	</div>
	<small class="tf-phase-location__lock-note" data-tf-location-lock-note><?php esc_html_e( 'Latitude and longitude stay locked until manual override is enabled.', 'trufield-portal' ); ?></small>
	<div class="tf-phase-location__map-wrap">
	<div class="tf-phase-location__map" data-tf-location-map aria-hidden="true"></div>
	<div class="tf-phase-location__map-note" data-tf-location-map-note><?php esc_html_e( 'Map preview will appear after the address is verified.', 'trufield-portal' ); ?></div>
	</div>
	</div>
	</div>
	<?php
};

$format_value = static function ( string $field, $value ) use ( $schema ): string {
if ( $value === '' || $value === null ) {
return '';
}

$type = $schema[ $field ]['type'] ?? 'text';
if ( $type === 'select' ) {
return $schema[ $field ]['options'][ $value ] ?? (string) $value;
}

if ( $type === 'date' ) {
$timestamp = strtotime( (string) $value );
return $timestamp ? wp_date( 'm/d/Y', $timestamp ) : (string) $value;
}

if ( $type === 'url' ) {
return (string) $value;
}

return (string) $value;
};

$readonly_fields = array_merge( array_keys( $field_groups[ $phase ]['required'] ), array_keys( $field_groups[ $phase ]['optional'] ) );
$phase_file_fields = trufield_phase_file_fields( $phase );

if ( 1 === $phase ) {
	$readonly_fields[] = 'field_location_lat';
	$readonly_fields[] = 'field_location_lng';
}

$readonly_pairs  = [];
foreach ( $readonly_fields as $field ) {
$value = get_post_meta( $post_id, $field, true );
if ( trim( (string) $value ) !== '' ) {
$readonly_pairs[ $field ] = $format_value( $field, $value );
}
}

$assigned_record_details = [];
if ( 1 === $phase ) {
	$assigned_detail_map = [
		'rsm_bam'             => 'RSM / BAM',
		'fsa'                 => 'FSA',
		'retailer_key_contact' => $labels['retailer_key_contact'] ?? 'Retailer Contact',
		'import_city'         => 'City',
		'import_state'        => 'Imported State',
	];

	foreach ( $assigned_detail_map as $field => $label ) {
		$value = trim( (string) get_post_meta( $post_id, $field, true ) );
		if ( $value !== '' ) {
			$assigned_record_details[ $label ] = $value;
		}
	}
}

$reopen_url = $is_admin ? wp_nonce_url( admin_url( "admin-post.php?action=trufield_reopen_phase&post_id={$post_id}&phase={$phase}" ), "trufield_reopen_phase_{$post_id}_{$phase}" ) : '';
$verify_url = $is_admin ? trufield_admin_phase_badge_verify_url( $post_id, $phase ) : '';
?>
<section class="tf-section tf-phase tf-phase--<?php echo esc_attr( $status ); ?>" id="<?php echo esc_attr( 'tf-phase-' . $phase ); ?>">
<div class="tf-phase__header">
<div class="tf-phase__title-row">
<h2 class="tf-phase__title"><?php echo esc_html( $phase_title ); ?></h2>
<span class="tf-phase__status tf-phase__status--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( ucwords( str_replace( '_', ' ', $status ) ) ); ?></span>
</div>

<?php if ( $is_verified && $verified_at ) : ?>
<div class="tf-phase__verified-badge">✓ <?php echo esc_html( sprintf( __( 'Verified on %s', 'trufield-portal' ), wp_date( 'm/d/Y g:i a', strtotime( $verified_at ) ) ) ); ?></div>
<?php elseif ( $is_verified ) : ?>
<div class="tf-phase__verified-badge">✓ <?php esc_html_e( 'Verified', 'trufield-portal' ); ?></div>
<?php elseif ( $status === 'completed' ) : ?>
<div class="tf-phase__awaiting-badge"><?php echo esc_html( trufield_phase_auto_verifies( $phase ) ? __( 'Saved — Missing Final Fields', 'trufield-portal' ) : __( 'Submitted — Awaiting Verification', 'trufield-portal' ) ); ?></div>
<?php endif; ?>

<?php if ( $completed_at ) : ?>
<p class="tf-phase__completed-at"><?php echo esc_html( sprintf( __( 'Completed: %s', 'trufield-portal' ), wp_date( 'm/d/Y g:i a', strtotime( $completed_at ) ) ) ); ?></p>
<?php endif; ?>

<?php if ( $status === 'completed' && ! $is_verified && ! $is_admin ) : ?>
<p class="tf-phase__blocked-note"><?php echo esc_html( sprintf( trufield_phase_auto_verifies( $phase ) ? __( '%s still needs the remaining required Phase 1 fields completed before it counts as a valid grower entry.', 'trufield-portal' ) : __( '%s has been submitted and is read-only while the admin team verifies it.', 'trufield-portal' ), $phase_label ) ); ?></p>
<?php elseif ( ! $prereq_met && ! $is_admin ) : ?>
<p class="tf-phase__blocked-note"><?php echo esc_html( sprintf( __( 'Phase %d must be verified before this form becomes available.', 'trufield-portal' ), $phase - 1 ) ); ?></p>
<?php endif; ?>

<?php if ( $is_admin && $status === 'completed' ) : ?>
<div class="tf-phase-form__actions">
<a href="<?php echo esc_url( $reopen_url ); ?>" class="tf-btn tf-btn--ghost tf-btn--sm" onclick="return confirm('<?php echo esc_js( __( 'Reopen this phase? Verification will be cleared.', 'trufield-portal' ) ); ?>');">
<?php esc_html_e( 'Reopen', 'trufield-portal' ); ?>
</a>
<?php if ( ! $is_verified && ! trufield_phase_auto_verifies( $phase ) ) : ?>
<a href="<?php echo esc_url( $verify_url ); ?>" class="tf-btn tf-btn--secondary tf-btn--sm"><?php esc_html_e( 'Verify', 'trufield-portal' ); ?></a>
<?php endif; ?>
</div>
<?php endif; ?>
</div>

<?php if ( ! $can_edit ) : ?>
<div class="tf-phase__readonly">
<?php if ( $is_verified ) : ?>
<p class="tf-phase__readonly-note"><?php echo esc_html( sprintf( trufield_phase_auto_verifies( $phase ) ? __( '%s counts as a valid grower entry. No further updates are needed right now.', 'trufield-portal' ) : __( '%s is verified. No further updates are needed right now.', 'trufield-portal' ), $phase_label ) ); ?></p>
<?php elseif ( $status === 'completed' ) : ?>
<p class="tf-phase__readonly-note"><?php echo esc_html( sprintf( trufield_phase_auto_verifies( $phase ) ? __( '%s is saved, but it will only count once the required Phase 1 fields are complete. You can review the saved details below.', 'trufield-portal' ) : __( '%s has been submitted and is waiting for admin verification. You can review the saved details below.', 'trufield-portal' ), $phase_label ) ); ?></p>
<?php elseif ( ! $prereq_met ) : ?>
<p class="tf-phase__readonly-note"><?php echo esc_html( sprintf( __( '%s is a separate form for a future workflow and will unlock after the previous phase is verified.', 'trufield-portal' ), $phase_label ) ); ?></p>
<?php endif; ?>
<?php if ( ! empty( $readonly_pairs ) ) : ?>
<dl class="tf-dl">
<?php foreach ( $readonly_pairs as $field => $value ) : ?>
<dt><?php echo esc_html( $labels[ $field ] ?? $field ); ?></dt>
<dd>
<?php if ( in_array( $field, $phase_file_fields, true ) ) : ?>
<div class="tf-readonly-photo">
<a href="<?php echo esc_url( $value ); ?>" target="_blank" rel="noopener noreferrer">
<img src="<?php echo esc_url( $value ); ?>" alt="<?php echo esc_attr( $labels[ $field ] ?? $field ); ?>" class="tf-readonly-photo__image">
</a>
<a href="<?php echo esc_url( $value ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open photo', 'trufield-portal' ); ?></a>
</div>
<?php elseif ( ( $schema[ $field ]['type'] ?? '' ) === 'url' ) : ?>
<a href="<?php echo esc_url( $value ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $value ); ?></a>
<?php else : ?>
<?php echo nl2br( esc_html( $value ) ); ?>
<?php endif; ?>
</dd>
<?php endforeach; ?>
</dl>
<?php elseif ( ! $prereq_met ) : ?>
<p class="tf-phase__empty"><?php esc_html_e( 'This separate form stays unavailable until the previous phase is verified and released.', 'trufield-portal' ); ?></p>
<?php else : ?>
<p class="tf-phase__empty"><?php echo esc_html( sprintf( __( '%s has not been started yet.', 'trufield-portal' ), $phase_label ) ); ?></p>
<?php endif; ?>
</div>
<?php else : ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="tf-phase-form" id="<?php echo esc_attr( 'tf-phase-form-' . $phase ); ?>" enctype="multipart/form-data">
<?php wp_nonce_field( "trufield_save_phase_{$post_id}_{$phase}" ); ?>
<input type="hidden" name="action" value="trufield_save_phase">
<input type="hidden" name="plant_field_id" value="<?php echo esc_attr( (string) $post_id ); ?>">
<input type="hidden" name="phase" value="<?php echo esc_attr( (string) $phase ); ?>">
<?php if ( ! empty( $phase_substeps ) ) : ?>
<input type="hidden" name="phase_step" value="<?php echo esc_attr( (string) $phase_initial_step ); ?>" data-tf-phase-step-input>
<?php endif; ?>

<div class="tf-phase-disclosures">
<details class="tf-phase-disclosure">
<summary class="tf-phase-disclosure__summary"><?php echo esc_html( 1 === $phase ? __( 'Phase 1 notes and assigned details', 'trufield-portal' ) : __( 'Phase 2 notes and assigned details', 'trufield-portal' ) ); ?></summary>
<div class="tf-phase-disclosure__content tf-phase__intro">
<p class="tf-phase__intro-copy"><?php echo esc_html( 1 === $phase ? __( 'Complete only the Phase 1 details for this assigned record right now. Future phases are separate forms and are not part of this submission.', 'trufield-portal' ) : __( 'Complete the Phase 2 visit documentation and observations for this trial. Phase 2 submits for admin verification after every required field is filled in.', 'trufield-portal' ) ); ?></p>
<?php if ( 1 === $phase && ! empty( $assigned_record_details ) ) : ?>
<div class="tf-assigned-details">
<p class="tf-assigned-details__title"><?php esc_html_e( 'Assigned record details', 'trufield-portal' ); ?></p>
<dl class="tf-assigned-details__list">
<?php foreach ( $assigned_record_details as $label => $value ) : ?>
<dt><?php echo esc_html( $label ); ?></dt>
<dd><?php echo esc_html( $value ); ?></dd>
<?php endforeach; ?>
</dl>
</div>
<?php endif; ?>
<div class="tf-phase__helper-notes">
<span class="tf-phase__helper-note"><?php esc_html_e( 'Required fields are marked with *.', 'trufield-portal' ); ?></span>
<?php if ( 1 === $phase ) : ?>
<span class="tf-phase__helper-note"><?php esc_html_e( 'Farm Name and Field Name are optional in Phase 1.', 'trufield-portal' ); ?></span>
<span class="tf-phase__helper-note"><?php esc_html_e( 'Field overview photos can be uploaded directly in Phase 1.', 'trufield-portal' ); ?></span>
<?php elseif ( 2 === $phase ) : ?>
<span class="tf-phase__helper-note"><?php esc_html_e( 'Use the upload fields to attach the two required Phase 2 visit photos.', 'trufield-portal' ); ?></span>
<span class="tf-phase__helper-note"><?php esc_html_e( 'Phase 2 stays editable until you mark it complete for admin verification.', 'trufield-portal' ); ?></span>
<?php endif; ?>
</div>
</div>
</details>

<?php if ( ! $validation_ok ) : ?>
<details class="tf-phase-disclosure tf-phase-disclosure--warning">
<summary class="tf-phase-disclosure__summary"><?php echo esc_html( sprintf( _n( '%d required field still missing before this counts as a valid entry', '%d required fields still missing before this counts as a valid entry', count( $validation_missing ), 'trufield-portal' ), count( $validation_missing ) ) ); ?></summary>
<div class="tf-phase-disclosure__content">
<div class="tf-missing-fields" role="status">
<strong><?php echo esc_html( 1 === $phase ? __( 'This record counts as 1 valid grower entry after the remaining required Phase 1 fields are filled in and saved.', 'trufield-portal' ) : __( 'Mark this phase complete after the remaining required details are filled in.', 'trufield-portal' ) ); ?></strong>
<span><?php echo esc_html( implode( ', ', 1 === $phase ? $validation_missing : $missing ) ); ?></span>
<span class="tf-missing-fields__note"><?php esc_html_e( 'If an assigned-record detail is missing and you cannot edit it here, contact the admin team.', 'trufield-portal' ); ?></span>
</div>
</div>
</details>
<?php endif; ?>
</div>

<?php if ( ! empty( $phase_substeps ) ) : ?>
<div class="tf-phase-substeps" data-tf-phase-substeps data-phase="<?php echo esc_attr( (string) $phase ); ?>" data-initial-step="<?php echo esc_attr( (string) $phase_initial_step ); ?>">
<ol class="tf-phase-substeps__nav" aria-label="<?php echo esc_attr( sprintf( __( 'Phase %d form sections', 'trufield-portal' ), $phase ) ); ?>">
<?php foreach ( $phase_substeps as $step_index => $step_config ) : ?>
<li class="tf-phase-substeps__nav-item">
<button type="button" class="tf-phase-substeps__tab<?php echo $step_index === $phase_initial_step ? ' is-active' : ''; ?>" data-tf-phase-step-tab data-step="<?php echo esc_attr( (string) $step_index ); ?>"<?php echo $step_index === $phase_initial_step ? ' aria-current="step"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
<span class="tf-phase-substeps__tab-dot"><?php echo esc_html( (string) $step_index ); ?></span>
<span class="tf-phase-substeps__tab-label"><?php echo esc_html( $step_config['title'] ); ?></span>
</button>
</li>
<?php endforeach; ?>
</ol>

<?php foreach ( $phase_substeps as $step_index => $step_config ) : ?>
<section
	class="tf-phase-substeps__panel"
	data-tf-phase-step-panel
	data-step="<?php echo esc_attr( (string) $step_index ); ?>"
	data-required-fields="<?php echo esc_attr( implode( ',', $step_config['required_fields'] ) ); ?>"
	<?php echo $step_index === $phase_initial_step ? '' : 'hidden'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
>
<div class="tf-phase-substeps__panel-header">
<p class="tf-phase-substeps__eyebrow"><?php echo esc_html( sprintf( __( 'Part %1$d of %2$d', 'trufield-portal' ), $step_index, count( $phase_substeps ) ) ); ?></p>
<h3 class="tf-phase-substeps__panel-title"><?php echo esc_html( $step_config['title'] ); ?></h3>
<p class="tf-phase-substeps__panel-copy"><?php echo esc_html( $step_config['description'] ); ?></p>
</div>

<div class="tf-missing-fields tf-missing-fields--inline" hidden data-tf-step-error></div>

<?php if ( 1 === $phase && 1 === $step_index ) : ?>
<?php $render_phase_one_location(); ?>
<?php endif; ?>

<div class="tf-form-grid tf-form-grid--phase-step">
<?php foreach ( $step_config['fields'] as $field ) : ?>
<?php if ( 'retailer_name' === $field ) : ?>
<?php $render_retailer_name_field( true ); ?>
<?php else : ?>
<?php $render_field( $field, $field_groups[ $phase ]['required'][ $field ] ?? $field_groups[ $phase ]['optional'][ $field ] ?? [], in_array( $field, $step_config['required_fields'], true ) ); ?>
<?php endif; ?>
<?php endforeach; ?>
</div>

<?php if ( ! empty( $step_config['optional_fields'] ) ) : ?>
<div class="tf-show-more tf-show-more--phase-step">
<button type="button" class="tf-show-more__toggle" aria-expanded="false" data-show-label="<?php esc_attr_e( 'Show additional fields', 'trufield-portal' ); ?>" data-hide-label="<?php esc_attr_e( 'Hide additional fields', 'trufield-portal' ); ?>">
<span class="tf-show-more__toggle-text"><?php esc_html_e( 'Show additional fields', 'trufield-portal' ); ?></span>
<span class="tf-show-more__icon">▼</span>
</button>
<div class="tf-show-more__content" hidden>
<div class="tf-form-grid tf-form-grid--phase-step">
<?php foreach ( $step_config['optional_fields'] as $field ) : ?>
<?php $render_field( $field, $field_groups[ $phase ]['optional'][ $field ] ?? [], false ); ?>
<?php endforeach; ?>
</div>
</div>
</div>
<?php endif; ?>

<div class="tf-phase-substeps__actions">
<?php if ( $step_index > 1 ) : ?>
<button type="button" class="tf-btn tf-btn--ghost" data-tf-phase-step-prev><?php esc_html_e( 'Back', 'trufield-portal' ); ?></button>
<?php endif; ?>
<button type="submit" name="phase_action" value="save" class="tf-btn tf-btn--secondary" formnovalidate><?php esc_html_e( 'Save Progress', 'trufield-portal' ); ?></button>
<?php if ( $step_index < count( $phase_substeps ) ) : ?>
<button type="button" class="tf-btn tf-btn--primary" data-tf-phase-step-next><?php esc_html_e( 'Next', 'trufield-portal' ); ?></button>
<?php else : ?>
<button type="submit" name="phase_action" value="<?php echo esc_attr( 1 === $phase ? 'save' : 'complete' ); ?>" class="tf-btn tf-btn--primary"<?php echo 2 === $phase ? ' onclick="return confirm(\'' . esc_js( sprintf( __( 'Submit Phase %d for admin verification? It will stay read-only until an admin reopens it.', 'trufield-portal' ), $phase ) ) . '\');"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( 1 === $phase ? __( 'Save Phase 1', 'trufield-portal' ) : __( 'Submit Phase 2', 'trufield-portal' ) ); ?></button>
<?php endif; ?>
</div>
</section>
<?php endforeach; ?>
</div>

<div class="tf-phase-form__action-help">
<div class="tf-phase-form__action-help-item">
<strong><?php esc_html_e( 'Save Progress', 'trufield-portal' ); ?></strong>
<span><?php echo esc_html( 1 === $phase ? __( 'Save at any point and return to the same Phase 1 section later.', 'trufield-portal' ) : __( 'Save either Phase 2 section and return to the same part later.', 'trufield-portal' ) ); ?></span>
</div>
<div class="tf-phase-form__action-help-item">
<strong><?php echo esc_html( 1 === $phase ? __( 'Valid Entry', 'trufield-portal' ) : __( 'Admin Verification', 'trufield-portal' ) ); ?></strong>
<span><?php echo esc_html( 1 === $phase ? __( 'Phase 1 becomes a valid grower entry automatically once every required field across all 3 sections is filled in and saved.', 'trufield-portal' ) : __( 'Once every required Phase 2 field is complete, submit the form for admin verification.', 'trufield-portal' ) ); ?></span>
</div>
</div>

<?php if ( $prereq_met && ! $validation_ok ) : ?>
<p class="tf-phase-form__complete-note"><?php echo esc_html( 1 === $phase ? __( 'Phase 1 will count as a valid grower entry after the required fields across all sections are filled in and saved.', 'trufield-portal' ) : __( 'Phase 2 cannot be submitted until the required fields across both sections are filled in.', 'trufield-portal' ) ); ?></p>
<?php endif; ?>
<?php else : ?>
<div class="tf-form-grid">
<?php if ( 1 === $phase ) : ?>
<?php $render_phase_one_location(); ?>
<?php endif; ?>
<?php foreach ( $field_groups[ $phase ]['required'] as $field => $config ) : ?>
<?php if ( 1 === $phase && 'field_location_address' === $field ) {
	continue;
} ?>
<?php if ( 'retailer_name' === $field ) : ?>
<?php $render_retailer_name_field( true ); ?>
<?php else : ?>
<?php $render_field( $field, $config, true ); ?>
<?php endif; ?>
<?php endforeach; ?>
</div>

<div class="tf-show-more">
<button type="button" class="tf-show-more__toggle" aria-expanded="false" data-show-label="<?php esc_attr_e( 'Show optional fields', 'trufield-portal' ); ?>" data-hide-label="<?php esc_attr_e( 'Hide optional fields', 'trufield-portal' ); ?>">
<span class="tf-show-more__toggle-text"><?php esc_html_e( 'Show optional fields', 'trufield-portal' ); ?></span>
<span class="tf-show-more__icon">▼</span>
</button>
<div class="tf-show-more__content" hidden>
<div class="tf-form-grid">
<?php foreach ( $field_groups[ $phase ]['optional'] as $field => $config ) : ?>
<?php $render_field( $field, $config, false ); ?>
<?php endforeach; ?>
</div>
</div>
</div>

<div class="tf-phase-form__action-help">
<div class="tf-phase-form__action-help-item">
<strong><?php esc_html_e( 'Save Progress', 'trufield-portal' ); ?></strong>
<span><?php esc_html_e( 'Keep your draft and come back later.', 'trufield-portal' ); ?></span>
</div>
<div class="tf-phase-form__action-help-item">
<strong><?php echo esc_html( 1 === $phase ? __( 'Valid Entry', 'trufield-portal' ) : __( 'Complete Phase', 'trufield-portal' ) ); ?></strong>
<span><?php echo esc_html( 1 === $phase ? __( 'Once every required Phase 1 field is present, saving the form makes this record count as 1 valid grower entry automatically.', 'trufield-portal' ) : __( 'Submit this form for verification once every required field is ready.', 'trufield-portal' ) ); ?></span>
</div>
</div>

<div class="tf-phase-form__actions">
<button type="submit" name="phase_action" value="save" class="tf-btn tf-btn--secondary" formnovalidate><?php esc_html_e( 'Save Progress', 'trufield-portal' ); ?></button>
<?php if ( $phase !== 1 && $prereq_met && $required_ok ) : ?>
<button type="submit" name="phase_action" value="complete" class="tf-btn tf-btn--primary" onclick="return confirm('<?php echo esc_js( sprintf( __( 'Submit Phase %d for admin verification? It will stay read-only until an admin reopens it.', 'trufield-portal' ), $phase ) ); ?>');">
<?php echo esc_html( sprintf( __( 'Mark Phase %d Complete', 'trufield-portal' ), $phase ) ); ?>
</button>
<?php endif; ?>
</div>
<?php if ( $prereq_met && ! $validation_ok ) : ?>
<p class="tf-phase-form__complete-note"><?php echo esc_html( 1 === $phase ? __( 'Phase 1 will count as a valid grower entry after the required fields above are filled in and saved.', 'trufield-portal' ) : __( 'This phase cannot be completed until all required fields above are filled in.', 'trufield-portal' ) ); ?></p>
<?php endif; ?>
<?php endif; ?>
</form>
<?php endif; ?>
</section>
