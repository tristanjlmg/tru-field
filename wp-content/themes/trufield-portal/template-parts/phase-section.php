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
$admin_only_phase_fields = trufield_admin_only_phase_fields( $phase );
$admin_phase_one_fields = [
	'rsm_bam' => [
		'input'       => 'user_select',
		'placeholder' => 'Select',
	],
	'fsa' => [
		'input'       => 'user_select',
		'placeholder' => 'Select',
	],
];

$field_groups = [
1 => [
'required' => [
'retailer_name'              => [ 'input' => 'text', 'placeholder' => 'Retailer name' ],
'retailer_branch_location'   => [ 'input' => 'text', 'placeholder' => 'Enter Branch Location' ],
'retailer_key_contact'       => [ 'input' => 'text', 'placeholder' => 'Enter' ],
'retailer_contact_phone'     => [ 'input' => 'text', 'placeholder' => 'Enter' ],
'retailer_address'           => [ 'input' => 'text', 'placeholder' => 'Enter Address' ],
'retailer_city'              => [ 'input' => 'text', 'placeholder' => 'Enter City' ],
'phase_1_state_region'       => [ 'input' => 'select', 'placeholder' => 'Select State' ],
'phase_1_treated_size_acres' => [ 'input' => 'number', 'placeholder' => 'Enter Size', 'step' => '0.01', 'min' => '0' ],
],
'optional' => [
'field_trial_contact'        => [ 'input' => 'text', 'placeholder' => 'Enter Name' ],
'contact_phone'              => [ 'input' => 'text', 'placeholder' => 'Enter Number' ],
'field_trial_contact_email'  => [ 'input' => 'email', 'placeholder' => 'Enter Email' ],
'farm_name'                  => [ 'input' => 'text', 'placeholder' => 'Enter farm name' ],
'field_name'                 => [ 'input' => 'text', 'placeholder' => 'Enter field name' ],
'phase_1_product_being_tested' => [ 'input' => 'select', 'placeholder' => 'Select Product' ],
'phase_1_application_type'   => [ 'input' => 'select' ],
'phase_1_application_date'   => [ 'input' => 'date' ],
'phase_1_application_rate'   => [ 'input' => 'text', 'placeholder' => 'Enter Amount' ],
'phase_1_trial_design'       => [ 'input' => 'select' ],
'phase_1_trial_type'         => [ 'input' => 'select', 'placeholder' => 'Select', 'step' => null, 'min' => null ],
'phase_1_growth_stage_at_application' => [ 'input' => 'text', 'placeholder' => 'e.g. V3' ],
'phase_1_weather_conditions_at_application' => [ 'input' => 'textarea', 'rows' => 2, 'placeholder' => 'Weather conditions at application' ],
'phase_1_soil_conditions_at_application' => [ 'input' => 'textarea', 'rows' => 2, 'placeholder' => 'Soil conditions at application' ],
'phase_1_carrier_volume_gal' => [ 'input' => 'number', 'step' => '0.1', 'min' => '0' ],
'phase_1_protocol_version'   => [ 'input' => 'select', 'placeholder' => 'Select' ],
'phase_1_application_timing' => [ 'input' => 'select', 'placeholder' => 'Select' ],
'phase_1_retailer_training_discussion_date' => [ 'input' => 'date' ],
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
'phase_2_rsm_visit_1_upload_photos'        => [ 'input' => 'file', 'accept' => 'image/*', 'help' => 'Select a photo type to enable the upload prompt.' ],
'phase_2_rsm_visit_2_date'                 => [ 'input' => 'date' ],
'phase_2_rsm_visit_2_upload_photos'        => [ 'input' => 'file', 'accept' => 'image/*', 'help' => 'Select a photo type to enable the upload prompt.' ],
],
'optional' => [
'phase_2_rsm_visit_1_photo_type'           => [ 'input' => 'select', 'placeholder' => 'Select photo type' ],
'phase_2_rsm_visit_2_photo_type'           => [ 'input' => 'select', 'placeholder' => 'Select photo type' ],
'phase_2_residue_degradation_observed'     => [ 'input' => 'select' ],
'phase_2_emergence_stand_collected'        => [ 'input' => 'select' ],
'phase_2_rsm_visit_3_date'                 => [ 'input' => 'date' ],
'phase_2_rsm_visit_3_upload_photos'        => [ 'input' => 'file', 'accept' => 'image/*', 'help' => 'Select a photo type to enable the upload prompt.' ],
'phase_2_rsm_visit_3_photo_type'           => [ 'input' => 'select', 'placeholder' => 'Select photo type' ],
'phase_2_rsm_visit_3_comments'             => [ 'input' => 'textarea', 'rows' => 3, 'placeholder' => 'Add notes for visit 3' ],
'phase_2_rsm_visit_4_date'                 => [ 'input' => 'date' ],
'phase_2_rsm_visit_4_upload_photos'        => [ 'input' => 'file', 'accept' => 'image/*', 'help' => 'Select a photo type to enable the upload prompt.' ],
'phase_2_rsm_visit_4_photo_type'           => [ 'input' => 'select', 'placeholder' => 'Select photo type' ],
'phase_2_rsm_visit_4_comments'             => [ 'input' => 'textarea', 'rows' => 3, 'placeholder' => 'Add notes for visit 4' ],
'phase_2_stand_count_1_treated'            => [ 'input' => 'number', 'min' => '0', 'step' => '0.01', 'attributes' => [ 'data-tf-stand-count-treated' => 'true' ] ],
'phase_2_stand_count_2_treated'            => [ 'input' => 'number', 'min' => '0', 'step' => '0.01', 'attributes' => [ 'data-tf-stand-count-treated' => 'true' ] ],
'phase_2_stand_count_3_treated'            => [ 'input' => 'number', 'min' => '0', 'step' => '0.01', 'attributes' => [ 'data-tf-stand-count-treated' => 'true' ] ],
'phase_2_stand_count_1_untreated'          => [ 'input' => 'number', 'min' => '0', 'step' => '0.01', 'attributes' => [ 'data-tf-stand-count-untreated' => 'true' ] ],
'phase_2_stand_count_2_untreated'          => [ 'input' => 'number', 'min' => '0', 'step' => '0.01', 'attributes' => [ 'data-tf-stand-count-untreated' => 'true' ] ],
'phase_2_stand_count_3_untreated'          => [ 'input' => 'number', 'min' => '0', 'step' => '0.01', 'attributes' => [ 'data-tf-stand-count-untreated' => 'true' ] ],
'phase_2_stand_count_data'                 => [ 'input' => 'text', 'placeholder' => 'Auto-calculated', 'readonly' => true, 'disabled' => true, 'help' => 'Calculated as the treated average minus the untreated average.', 'attributes' => [ 'data-tf-stand-count-delta' => 'true' ] ],
'phase_2_most_significant_visual_difference' => [ 'input' => 'textarea', 'rows' => 4, 'placeholder' => 'What is the most significant visual difference today?' ],
'phase_2_emergence_flag_test'              => [ 'input' => 'select', 'placeholder' => 'Select' ],
'phase_2_pictures_at_application'          => [ 'input' => 'select', 'placeholder' => 'Select' ],
'phase_2_pictures_at_application_upload'   => [ 'input' => 'file', 'accept' => 'image/*', 'help' => 'Upload an image from your device. JPG, PNG, GIF, and WebP are supported.', 'attributes' => [ 'data-tf-photo-upload-match' => 'yes', 'data-tf-photo-upload-hide-field' => 'true' ] ],
'phase_2_pictures_at_planting'             => [ 'input' => 'select', 'placeholder' => 'Select' ],
'phase_2_pictures_at_planting_upload'      => [ 'input' => 'file', 'accept' => 'image/*', 'help' => 'Upload an image from your device. JPG, PNG, GIF, and WebP are supported.', 'attributes' => [ 'data-tf-photo-upload-match' => 'yes', 'data-tf-photo-upload-hide-field' => 'true' ] ],
'phase_2_pictures_in_season_harvest'       => [ 'input' => 'select', 'placeholder' => 'Select' ],
'phase_2_pictures_in_season_harvest_upload'=> [ 'input' => 'file', 'accept' => 'image/*', 'help' => 'Upload an image from your device. JPG, PNG, GIF, and WebP are supported.', 'attributes' => [ 'data-tf-photo-upload-match' => 'yes', 'data-tf-photo-upload-hide-field' => 'true' ] ],
'phase_2_pictures_at_harvest'              => [ 'input' => 'select', 'placeholder' => 'Select' ],
'phase_2_drone_images_available'           => [ 'input' => 'select', 'placeholder' => 'Select' ],
'phase_2_drone_images_available_upload'    => [ 'input' => 'file', 'accept' => 'image/*', 'help' => 'Upload an image from your device. JPG, PNG, GIF, and WebP are supported.', 'attributes' => [ 'data-tf-photo-upload-match' => 'yes', 'data-tf-photo-upload-hide-field' => 'true' ] ],
'phase_2_grower_retailer_testimonials'     => [ 'input' => 'select', 'placeholder' => 'Select' ],
'phase_2_time_lapse_available'             => [ 'input' => 'select', 'placeholder' => 'Select' ],
'phase_2_grower_retailer_comments'         => [ 'input' => 'textarea', 'rows' => 3, 'placeholder' => 'Grower / Retailer Comments' ],
],
],
3 => [
'required' => [],
'optional' => [
'phase_3_event_type'           => [ 'input' => 'select', 'placeholder' => 'Select' ],
'phase_3_event_date'           => [ 'input' => 'date' ],
'phase_3_event_location'       => [ 'input' => 'text', 'placeholder' => 'Enter location' ],
'phase_3_attendee_count'       => [ 'input' => 'number', 'min' => '0', 'step' => '1' ],
'phase_3_tillage_type'      => [ 'input' => 'text' ],
'phase_3_soil_temp_f_at_application' => [ 'input' => 'number', 'min' => '0', 'step' => '0.1' ],
'phase_3_carrier_volume_gal' => [ 'input' => 'number', 'min' => '0', 'step' => '0.1' ],
'phase_3_tank_mix_partners' => [ 'input' => 'textarea', 'rows' => 3 ],
'phase_3_planting_date'     => [ 'input' => 'date' ],
'phase_3_hybrid_variety'    => [ 'input' => 'text' ],
'phase_3_planting_population' => [ 'input' => 'number', 'min' => '0', 'step' => '1' ],
'phase_3_row_spacing_in'    => [ 'input' => 'number', 'min' => '0', 'step' => '0.1' ],
'phase_3_planting_speed_mph' => [ 'input' => 'number', 'min' => '0', 'step' => '0.1' ],
'phase_3_plant_heights_avg_untreated_v7_in' => [ 'input' => 'number', 'min' => '0', 'step' => '0.01' ],
'phase_3_plant_heights_avg_treated_v7_in' => [ 'input' => 'number', 'min' => '0', 'step' => '0.01' ],
'phase_3_stalk_diameter_untreated_v7_mm' => [ 'input' => 'number', 'min' => '0', 'step' => '0.01' ],
'phase_3_stalk_diameter_treated_v7_mm2' => [ 'input' => 'number', 'min' => '0', 'step' => '0.01' ],
'phase_3_yield_untreated_bu_ac' => [ 'input' => 'number', 'min' => '0', 'step' => '0.01' ],
'phase_3_yield_treated_bu_ac' => [ 'input' => 'number', 'min' => '0', 'step' => '0.01' ],
'phase_3_moisture_untreated_percent' => [ 'input' => 'number', 'min' => '0', 'max' => '100', 'step' => '0.1' ],
'phase_3_moisture_treated_percent' => [ 'input' => 'number', 'min' => '0', 'max' => '100', 'step' => '0.1' ],
'phase_3_test_weight_untreated_lbs_bu' => [ 'input' => 'number', 'min' => '0', 'step' => '0.01' ],
'phase_3_test_weight_treated_lbs_bu' => [ 'input' => 'number', 'min' => '0', 'step' => '0.01' ],
'phase_3_as_applied_gis_data' => [ 'input' => 'select', 'placeholder' => 'Select' ],
'phase_3_planting_gis_data'  => [ 'input' => 'select', 'placeholder' => 'Select' ],
'phase_3_harvest_gis_data'   => [ 'input' => 'select', 'placeholder' => 'Select' ],
'phase_3_agronomy_comments'  => [ 'input' => 'textarea', 'rows' => 4 ],
],
],
];

$retailer_name_options = trufield_get_retailer_name_options( $post_id );
$retailer_directory    = trufield_get_retailer_directory();
$retailer_assignment   = trufield_get_retailer_assignment_context( $post_id );
$phase_one_substeps = [];
$phase_two_substeps = [];
$phase_step_query_arg = sprintf( 'phase_%d_step', $phase );
$phase_initial_step = max( 1, min( trufield_get_phase_step_count( $phase ), (int) sanitize_key( wp_unslash( $_GET[ $phase_step_query_arg ] ?? '1' ) ) ) );
$phase_substeps = [];

if ( 1 === $phase ) {
	$phase_one_substeps = [
		1 => [
			'key'            => 'retailer',
			'title'          => __( 'Retailer Information', 'trufield-portal' ),
			'description'    => __( 'Enter the retailer contact details for this record.', 'trufield-portal' ),
			'required_fields'=> [
				'retailer_name',
				'retailer_key_contact',
				'retailer_contact_phone',
				'retailer_address',
				'retailer_city',
				'phase_1_state_region',
			],
			'fields'         => [
				'retailer_name',
				'retailer_branch_location',
				'retailer_key_contact',
				'retailer_contact_phone',
				'retailer_address',
				'retailer_city',
				'phase_1_state_region',
			],
			'admin_fields'   => [ 'rsm_bam', 'fsa' ],
			'optional_fields'=> [],
		],
		2 => [
			'key'            => 'field-trial',
			'title'          => __( 'Field Trial Information', 'trufield-portal' ),
			'description'    => __( 'Capture the crop specialist contact, farm name, field name, and coordinates for this record.', 'trufield-portal' ),
			'required_fields'=> [],
			'fields'         => [
				'field_trial_contact',
				'contact_phone',
				'field_trial_contact_email',
			],
			'optional_fields'=> [
				'farm_name',
				'field_name',
			],
		],
		3 => [
			'key'            => 'trial',
			'title'          => __( 'Trial Information', 'trufield-portal' ),
			'description'    => __( 'Finish the application details, including the protocol version, and add any supporting notes or media.', 'trufield-portal' ),
			'required_fields'=> [],
			'fields'         => [],
			'optional_fields'=> [
				'phase_1_treated_size_acres',
				'phase_1_trial_type',
				'phase_1_application_rate',
				'phase_1_protocol_version',
				'phase_1_application_timing',
				'phase_1_application_date',
				'phase_1_retailer_training_discussion_date',
			],
		],
	];
	$phase_substeps = $phase_one_substeps;
} elseif ( 2 === $phase ) {
	$phase_two_substeps = [
		1 => [
			'key'            => 'visits',
			'title'          => __( 'RSM Visits & Field Documentation', 'trufield-portal' ),
			'description'    => __( 'Record up to four RSM visits, including visit dates, uploaded photos, and photo details for each documented stop.', 'trufield-portal' ),
			'required_fields'=> [
				'phase_2_rsm_visit_1_date',
				'phase_2_rsm_visit_1_upload_photos',
				'phase_2_rsm_visit_2_date',
				'phase_2_rsm_visit_2_upload_photos',
			],
			'fields'         => [
				'phase_2_rsm_visit_1_date',
				'phase_2_rsm_visit_1_photo_type',
				'phase_2_rsm_visit_1_upload_photos',
				'phase_2_rsm_visit_2_date',
				'phase_2_rsm_visit_2_photo_type',
				'phase_2_rsm_visit_2_upload_photos',
				'phase_2_residue_degradation_observed',
				'phase_2_emergence_stand_collected',
			],
			'optional_fields'=> [
				'phase_2_rsm_visit_3_date',
				'phase_2_rsm_visit_3_photo_type',
				'phase_2_rsm_visit_3_upload_photos',
				'phase_2_rsm_visit_3_comments',
				'phase_2_rsm_visit_4_date',
				'phase_2_rsm_visit_4_photo_type',
				'phase_2_rsm_visit_4_upload_photos',
				'phase_2_rsm_visit_4_comments',
			],
		],
		2 => [
			'key'            => 'stand-counts',
			'title'          => __( 'Stand Counts & Delta Count', 'trufield-portal' ),
			'description'    => __( 'Enter the three treated and three untreated stand counts. The delta field is calculated automatically from the averages.', 'trufield-portal' ),
			'required_fields'=> [
				'phase_2_stand_count_1_treated',
				'phase_2_stand_count_2_treated',
				'phase_2_stand_count_3_treated',
				'phase_2_stand_count_1_untreated',
				'phase_2_stand_count_2_untreated',
				'phase_2_stand_count_3_untreated',
			],
			'fields'         => [
				'phase_2_stand_count_1_treated',
				'phase_2_stand_count_2_treated',
				'phase_2_stand_count_3_treated',
				'phase_2_stand_count_1_untreated',
				'phase_2_stand_count_2_untreated',
				'phase_2_stand_count_3_untreated',
				'phase_2_stand_count_data',
			],
			'optional_fields'=> [],
		],
		3 => [
			'key'            => 'visual-compliance',
			'title'          => __( 'Visual Evidence & Photo Compliance', 'trufield-portal' ),
			'description'    => __( 'Capture the screenshot scoring checks for visual evidence, photo compliance, and media proof.', 'trufield-portal' ),
			'required_fields'=> [
				'phase_2_grower_retailer_testimonials',
				'phase_2_grower_retailer_comments',
			],
			'fields'         => [
				'phase_2_emergence_flag_test',
				'phase_2_pictures_at_application',
				'phase_2_pictures_at_application_upload',
				'phase_2_pictures_at_planting',
				'phase_2_pictures_at_planting_upload',
				'phase_2_pictures_in_season_harvest',
				'phase_2_pictures_in_season_harvest_upload',
				'phase_2_pictures_at_harvest',
				'phase_2_drone_images_available',
				'phase_2_drone_images_available_upload',
				'phase_2_grower_retailer_testimonials',
				'phase_2_time_lapse_available',
				'phase_2_grower_retailer_comments',
				'phase_2_most_significant_visual_difference',
			],
			'optional_fields'=> [],
		],
	];
	$phase_substeps = $phase_two_substeps;
}

$render_field = static function ( string $field, array $config, bool $required = false ) use ( $post_id, $labels, $schema ): void {
$value       = get_post_meta( $post_id, $field, true );
	if ( 'retailer_branch_location' === $field ) {
		$value = trufield_normalize_retailer_branch_location(
			(string) get_post_meta( $post_id, 'retailer_name', true ),
			(string) $value
		);
	}
$label       = $labels[ $field ] ?? $field;
$input_type  = $config['input'] ?? 'text';
$placeholder = $config['placeholder'] ?? '';
$rows        = (int) ( $config['rows'] ?? 3 );
$min         = $config['min'] ?? null;
$max         = $config['max'] ?? null;
$step        = $config['step'] ?? null;
$accept      = $config['accept'] ?? '';
$help        = $config['help'] ?? '';
$readonly    = ! empty( $config['readonly'] );
$disabled    = ! empty( $config['disabled'] );
$static_value = $config['static_value'] ?? null;
$attributes  = (array) ( $config['attributes'] ?? [] );
$attachment_id = (int) get_post_meta( $post_id, trufield_phase_photo_attachment_meta_key( $field ), true );
$required_markup = $required ? ' required aria-required="true"' : '';
$input_type_markup = $input_type;
$validation_markup = '';

if ( $input_type === 'file' ) {
	$help = trim( $help . ' ' . trufield_get_phase_upload_help_text( $field, $post_id ) );
}

if ( in_array( $field, [ 'retailer_contact_phone', 'contact_phone' ], true ) ) {
	$input_type_markup = 'tel';
	$validation_markup = ' inputmode="tel" autocomplete="tel" pattern="(?:\\+?1[ .-]?)?(?:\\([0-9]{3}\\)|[0-9]{3})[ .-]?[0-9]{3}[ .-]?[0-9]{4}" title="Enter a valid phone number"';
}

$attribute_markup = '';
foreach ( $attributes as $attribute_name => $attribute_value ) {
	if ( ! is_string( $attribute_name ) || '' === $attribute_name ) {
		continue;
	}

	$attribute_markup .= ' ' . esc_attr( $attribute_name ) . '="' . esc_attr( (string) $attribute_value ) . '"';
}

if ( ( '' === trim( (string) $value ) || null === $value ) && null !== $static_value ) {
	$value = $static_value;
}
?>
<div class="tf-field-group">
<label for="<?php echo esc_attr( $field ); ?>">
<?php echo esc_html( $label ); ?>
<?php if ( $required ) : ?>
<span class="tf-required">*</span>
<?php endif; ?>
</label>
<?php if ( $input_type === 'select' ) : ?>
<?php $select_options = $schema[ $field ]['options'] ?? []; ?>
<?php if ( '' !== trim( (string) $value ) && ! isset( $select_options[ (string) $value ] ) ) {
	$select_options[ (string) $value ] = (string) $value;
} ?>
<select id="<?php echo esc_attr( $field ); ?>" name="<?php echo esc_attr( $field ); ?>" class="tf-select"<?php echo $required_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo $attribute_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
<option value=""><?php echo esc_html( $placeholder ?: __( 'Select…', 'trufield-portal' ) ); ?></option>
<?php foreach ( $select_options as $option_value => $option_label ) : ?>
<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( (string) $value, (string) $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
<?php endforeach; ?>
</select>
<?php elseif ( $input_type === 'user_select' ) : ?>
<?php $user_options = trufield_get_assignment_user_options( $field ); ?>
<?php $selected_user_value = (string) trufield_resolve_assignment_user_id( $value, $field ); ?>
<select id="<?php echo esc_attr( $field ); ?>" name="<?php echo esc_attr( $field ); ?>" class="tf-select"<?php echo $required_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
<option value=""><?php echo esc_html( $placeholder ?: __( 'Select…', 'trufield-portal' ) ); ?></option>
<?php foreach ( $user_options as $option_value => $option_label ) : ?>
<option value="<?php echo esc_attr( (string) $option_value ); ?>" <?php selected( $selected_user_value, (string) $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
<?php endforeach; ?>
</select>
<?php elseif ( $input_type === 'textarea' ) : ?>
<textarea id="<?php echo esc_attr( $field ); ?>" name="<?php echo esc_attr( $field ); ?>" class="tf-textarea" rows="<?php echo esc_attr( (string) $rows ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"<?php echo $required_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo $readonly ? ' readonly' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo $disabled ? ' disabled' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo $attribute_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_textarea( (string) $value ); ?></textarea>
<?php elseif ( $input_type === 'file' ) : ?>
<?php $file_prompt = trufield_get_phase_upload_prompt_label( $field ); ?>
<?php $photo_type_field = trufield_phase_photo_type_field_for_upload( $field ); ?>
<?php $selected_photo_type = $photo_type_field ? trim( (string) get_post_meta( $post_id, $photo_type_field, true ) ) : ''; ?>
<div class="tf-upload-field">
<?php if ( $file_prompt ) : ?>
<p class="tf-upload-field__prompt" data-tf-upload-prompt <?php echo '' !== $selected_photo_type ? '' : 'hidden'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( sprintf( __( 'Upload %1$s image. Max file size: %2$s.', 'trufield-portal' ), $file_prompt, trufield_get_max_upload_size_label() ) ); ?></p>
<?php endif; ?>
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
<?php echo $photo_type_field ? ' data-tf-photo-upload-field="' . esc_attr( $photo_type_field ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $required && ! $value ? ' required aria-required="true"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $accept ? ' accept="' . esc_attr( $accept ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $attribute_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
>
</div>
<?php elseif ( $input_type === 'date' ) : ?>
<?php $date_placeholder = $placeholder ?: __( 'MM/DD/YYYY', 'trufield-portal' ); ?>
<input
type="date"
id="<?php echo esc_attr( $field ); ?>"
name="<?php echo esc_attr( $field ); ?>"
class="tf-input tf-input--date"
value="<?php echo esc_attr( (string) $value ); ?>"
placeholder="<?php echo esc_attr( $date_placeholder ); ?>"
inputmode="numeric"
autocomplete="off"
data-tf-date-input
data-date-placeholder="<?php echo esc_attr( $date_placeholder ); ?>"
<?php echo $required_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $readonly ? ' readonly' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $disabled ? ' disabled' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $attribute_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $min !== null ? ' min="' . esc_attr( (string) $min ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $max !== null ? ' max="' . esc_attr( (string) $max ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $step !== null ? ' step="' . esc_attr( (string) $step ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
>
<?php else : ?>
<input
type="<?php echo esc_attr( $input_type_markup ); ?>"
id="<?php echo esc_attr( $field ); ?>"
name="<?php echo esc_attr( $field ); ?>"
class="tf-input"
value="<?php echo esc_attr( (string) $value ); ?>"
placeholder="<?php echo esc_attr( $placeholder ); ?>"
<?php echo $required_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $readonly ? ' readonly' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $disabled ? ' disabled' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $validation_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $attribute_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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

$render_retailer_name_field = static function ( bool $required = false ) use ( $post_id, $labels, $retailer_name_options, $retailer_directory, $retailer_assignment ): void {
	$current_value = trim( (string) get_post_meta( $post_id, 'retailer_name', true ) );
	$is_known      = '' !== $current_value && isset( $retailer_name_options[ $current_value ] );
	$selected      = $is_known ? $current_value : ( '' !== $current_value ? 'other' : '' );
	$manual_value  = $is_known ? '' : $current_value;
	$directory_json = wp_json_encode( $retailer_directory );
	?>
	<div
		class="tf-field-group tf-retailer-picker"
		data-tf-retailer-picker
		data-tf-retailer-directory="<?php echo esc_attr( is_string( $directory_json ) ? $directory_json : '{}' ); ?>"
		data-tf-assigned-rep-id="<?php echo esc_attr( $retailer_assignment['id'] ); ?>"
		data-tf-assigned-rep-label="<?php echo esc_attr( $retailer_assignment['label'] ); ?>"
		data-tf-assignment-control="rsm_bam"
	>
	<label for="retailer_name_select">
	<?php echo esc_html( $labels['retailer_name'] ?? 'Retailer Name' ); ?>
	<?php if ( $required ) : ?>
	<span class="tf-required">*</span>
	<?php endif; ?>
	</label>
	<select id="retailer_name_select" name="retailer_name" class="tf-select"<?php echo $required ? ' required aria-required="true"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-tf-retailer-select>
	<option value=""><?php esc_html_e( 'Select Retailer', 'trufield-portal' ); ?></option>
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
	<small><?php esc_html_e( 'Select a retailer to auto-fill the contact, phone, address, city, and state. Choose Other if the retailer is not listed and enter it manually.', 'trufield-portal' ); ?></small>
	</div>
	<?php
};

$render_phase_one_location = static function () use ( $labels, $post_id ): void {
	?>
	<div class="tf-phase-location tf-phase-location--coords-only">
	<div class="tf-phase-location__coords">
	<div class="tf-field-group">
	<label for="field_location_lat"><?php echo esc_html( $labels['field_location_lat'] ?? 'Field Latitude' ); ?></label>
	<input
	type="number"
	id="field_location_lat"
	name="field_location_lat"
	class="tf-input"
	value="<?php echo esc_attr( (string) get_post_meta( $post_id, 'field_location_lat', true ) ); ?>"
	step="0.000001"
	placeholder="<?php esc_attr_e( 'Latitude', 'trufield-portal' ); ?>"
	required
	>
	</div>
	<div class="tf-field-group">
	<label for="field_location_lng"><?php echo esc_html( $labels['field_location_lng'] ?? 'Field Longitude' ); ?></label>
	<input
	type="number"
	id="field_location_lng"
	name="field_location_lng"
	class="tf-input"
	value="<?php echo esc_attr( (string) get_post_meta( $post_id, 'field_location_lng', true ) ); ?>"
	step="0.000001"
	placeholder="<?php esc_attr_e( 'Longitude', 'trufield-portal' ); ?>"
	required
	>
	</div>
	</div>
	<small class="tf-phase-location__status"><?php esc_html_e( 'Sales reps can edit longitude and latitude directly.', 'trufield-portal' ); ?></small>
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

if ( $type === 'user' ) {
	return trufield_resolve_assignment_user_label( $value );
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

if ( ! $is_admin ) {
	$readonly_fields = array_values( array_diff( $readonly_fields, trufield_admin_only_phase_fields( $phase ) ) );
}

$readonly_pairs  = [];
foreach ( $readonly_fields as $field ) {
$value = get_post_meta( $post_id, $field, true );
	if ( 'retailer_branch_location' === $field ) {
		$value = trufield_normalize_retailer_branch_location(
			(string) get_post_meta( $post_id, 'retailer_name', true ),
			(string) $value
		);
	}
if ( trim( (string) $value ) !== '' ) {
$readonly_pairs[ $field ] = $format_value( $field, $value );
}
}

$assigned_record_details = [];
if ( 1 === $phase ) {
	$assigned_detail_map = [
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

$submit_confirmation = '';
if ( 3 === $phase ) {
	$submit_confirmation = sprintf( __( 'Submit Phase %d for admin verification? It will stay read-only until an admin reopens it.', 'trufield-portal' ), $phase );
} elseif ( 2 === $phase ) {
	$submit_confirmation = __( 'Submit Phase 2? It will lock after save and award points once every scoring field is complete.', 'trufield-portal' );
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
<div class="tf-phase__awaiting-badge"><?php echo esc_html( 1 === $phase ? __( 'Saved — Missing Final Fields', 'trufield-portal' ) : ( 2 === $phase ? __( 'Saved — Missing Scoring Fields', 'trufield-portal' ) : __( 'Submitted — Awaiting Verification', 'trufield-portal' ) ) ); ?></div>
<?php endif; ?>

<?php if ( $completed_at ) : ?>
<p class="tf-phase__completed-at"><?php echo esc_html( sprintf( __( 'Completed: %s', 'trufield-portal' ), wp_date( 'm/d/Y g:i a', strtotime( $completed_at ) ) ) ); ?></p>
<?php endif; ?>

<?php if ( $status === 'completed' && ! $is_verified && ! $is_admin && ! $can_edit ) : ?>
<p class="tf-phase__blocked-note"><?php echo esc_html( 1 === $phase ? sprintf( __( '%s still needs the remaining required Phase 1 fields completed before it counts as a valid grower entry.', 'trufield-portal' ), $phase_label ) : ( 2 === $phase ? sprintf( __( '%s still needs the remaining Phase 2 scoring fields completed before it awards points.', 'trufield-portal' ), $phase_label ) : sprintf( __( '%s has been submitted and is read-only while the admin team verifies it.', 'trufield-portal' ), $phase_label ) ) ); ?></p>
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

<?php if ( $can_edit && $status === 'completed' && ! $is_verified ) : ?>
<p class="tf-phase__editable-note"><?php echo esc_html( 1 === $phase ? __( 'This phase has been submitted, but it can still be revised and resubmitted until the required Phase 1 fields are verified.', 'trufield-portal' ) : ( 2 === $phase ? __( 'This phase has been submitted, but it can still be revised and resubmitted until the Phase 2 scoring fields are verified.', 'trufield-portal' ) : __( 'This phase has been submitted, but it can still be revised and resubmitted until an admin verifies it.', 'trufield-portal' ) ) ); ?></p>
<?php endif; ?>

<?php if ( ! $can_edit ) : ?>
<div class="tf-phase__readonly">
<?php if ( $is_verified ) : ?>
<p class="tf-phase__readonly-note"><?php echo esc_html( 1 === $phase ? sprintf( __( '%s counts as a valid grower entry. No further updates are needed right now.', 'trufield-portal' ), $phase_label ) : ( 2 === $phase ? sprintf( __( '%s is complete and has awarded its Phase 2 points. No admin approval is needed.', 'trufield-portal' ), $phase_label ) : sprintf( __( '%s is verified. No further updates are needed right now.', 'trufield-portal' ), $phase_label ) ) ); ?></p>
<?php elseif ( $status === 'completed' ) : ?>
<p class="tf-phase__readonly-note"><?php echo esc_html( 1 === $phase ? sprintf( __( '%s is saved, but it will only count once the required Phase 1 fields are complete. You can review the saved details below.', 'trufield-portal' ), $phase_label ) : ( 2 === $phase ? sprintf( __( '%s is saved, but it will only award points once every Phase 2 scoring field is complete. You can review the saved details below.', 'trufield-portal' ), $phase_label ) : sprintf( __( '%s has been submitted and is waiting for admin verification. You can review the saved details below.', 'trufield-portal' ), $phase_label ) ) ); ?></p>
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
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="tf-phase-form" id="<?php echo esc_attr( 'tf-phase-form-' . $phase ); ?>" enctype="multipart/form-data"<?php echo ! empty( $phase_substeps ) ? ' novalidate' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
<?php wp_nonce_field( "trufield_save_phase_{$post_id}_{$phase}" ); ?>
<input type="hidden" name="action" value="trufield_save_phase">
<input type="hidden" name="plant_field_id" value="<?php echo esc_attr( (string) $post_id ); ?>">
<input type="hidden" name="phase" value="<?php echo esc_attr( (string) $phase ); ?>">
<input type="hidden" name="phase_action_intent" value="save" data-tf-phase-action-input>
<?php if ( ! empty( $phase_substeps ) ) : ?>
<input type="hidden" name="phase_step" value="<?php echo esc_attr( (string) $phase_initial_step ); ?>" data-tf-phase-step-input>
<?php endif; ?>

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

<?php if ( ! empty( $step_config['admin_fields'] ) && $is_admin ) : ?>
<div class="tf-phase-admin-fields">
<div class="tf-phase-admin-fields__header">
<p class="tf-phase-admin-fields__eyebrow"><?php esc_html_e( 'Admin Only', 'trufield-portal' ); ?></p>
<p class="tf-phase-admin-fields__copy"><?php esc_html_e( 'Assignment details stay editable for admins here and remain hidden from the assigned rep.', 'trufield-portal' ); ?></p>
</div>
<div class="tf-form-grid tf-form-grid--phase-step tf-form-grid--admin-fields">
<?php foreach ( $step_config['admin_fields'] as $field ) : ?>
<?php $render_field( $field, $admin_phase_one_fields[ $field ] ?? [], true ); ?>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>

<div class="tf-form-grid tf-form-grid--phase-step">
<?php foreach ( $step_config['fields'] as $field ) : ?>
<?php if ( ! $is_admin && in_array( $field, $admin_only_phase_fields, true ) ) {
	continue;
} ?>
<?php if ( 'retailer_name' === $field ) : ?>
<?php $render_retailer_name_field( true ); ?>
<?php else : ?>
<?php $render_field( $field, $field_groups[ $phase ]['required'][ $field ] ?? $field_groups[ $phase ]['optional'][ $field ] ?? [], in_array( $field, $step_config['required_fields'], true ) ); ?>
<?php endif; ?>
<?php endforeach; ?>
</div>

<?php if ( 1 === $phase && 2 === $step_index ) : ?>
<?php $render_phase_one_location(); ?>
<?php endif; ?>

<?php if ( ! empty( $step_config['optional_fields'] ) ) : ?>
<div class="tf-form-grid tf-form-grid--phase-step">
<?php foreach ( $step_config['optional_fields'] as $field ) : ?>
<?php if ( ! $is_admin && in_array( $field, $admin_only_phase_fields, true ) ) {
	continue;
} ?>
<?php $render_field( $field, $field_groups[ $phase ]['optional'][ $field ] ?? [], false ); ?>
<?php endforeach; ?>
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
<button type="submit" name="phase_action" value="complete" class="tf-btn tf-btn--primary"<?php echo '' !== $submit_confirmation ? ' onclick="return confirm(\'' . esc_js( $submit_confirmation ) . '\');"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $status === 'completed' ? __( 'Resubmit', 'trufield-portal' ) : ( 1 === $phase ? __( 'Submit', 'trufield-portal' ) : sprintf( __( 'Submit Phase %d', 'trufield-portal' ), $phase ) ) ); ?></button>
<?php endif; ?>
</div>
</section>
<?php endforeach; ?>
</div>

<?php else : ?>
<div class="tf-form-grid">
<?php if ( 1 === $phase ) : ?>
<?php $render_phase_one_location(); ?>
<?php endif; ?>
<?php foreach ( $field_groups[ $phase ]['required'] as $field => $config ) : ?>
<?php if ( 1 === $phase && 'field_location_address' === $field ) {
	continue;
} ?>
<?php if ( ! $is_admin && in_array( $field, $admin_only_phase_fields, true ) ) {
	continue;
} ?>
<?php
	$is_field_required = true;
	if ( 3 === $phase && in_array( $field, [ 'phase_3_event_date', 'phase_3_event_location', 'phase_3_attendee_count' ], true ) ) {
		$is_field_required = 'yes' === strtolower( trim( (string) get_post_meta( $post_id, 'phase_3_event_type', true ) ) );
	}
?>
<?php if ( 'retailer_name' === $field ) : ?>
<?php $render_retailer_name_field( true ); ?>
<?php else : ?>
<?php $render_field( $field, $config, $is_field_required ); ?>
<?php endif; ?>
<?php endforeach; ?>
</div>

<?php if ( ! empty( $field_groups[ $phase ]['optional'] ) ) : ?>
<div class="tf-form-grid">
<?php foreach ( $field_groups[ $phase ]['optional'] as $field => $config ) : ?>
<?php if ( ! $is_admin && in_array( $field, $admin_only_phase_fields, true ) ) {
	continue;
} ?>
<?php $render_field( $field, $config, false ); ?>
<?php endforeach; ?>
</div>
<?php endif; ?>

<div class="tf-phase-form__actions">
<button type="submit" name="phase_action" value="save" class="tf-btn tf-btn--secondary" formnovalidate><?php esc_html_e( 'Save Progress', 'trufield-portal' ); ?></button>
<?php if ( $phase !== 1 && $prereq_met && $required_ok ) : ?>
<button type="submit" name="phase_action" value="complete" class="tf-btn tf-btn--primary"<?php echo '' !== $submit_confirmation ? ' onclick="return confirm(\'' . esc_js( $submit_confirmation ) . '\');"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
<?php echo esc_html( $status === 'completed' ? __( 'Resubmit', 'trufield-portal' ) : sprintf( __( 'Mark Phase %d Complete', 'trufield-portal' ), $phase ) ); ?>
</button>
<?php endif; ?>
</div>
<?php endif; ?>
</form>
<?php endif; ?>
</section>
