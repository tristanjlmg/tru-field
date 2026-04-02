<?php
/**
 * TruField Portal — ACF Local Field Groups
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

add_action( 'acf/init', 'trufield_register_acf_field_groups' );
function trufield_register_acf_field_groups(): void {
if ( ! function_exists( 'acf_add_local_field_group' ) ) {
return;
}

acf_add_local_field_group( [
'key'                   => 'group_tf_assignment',
'title'                 => 'Assignment / Record',
'fields'                => [
[
'key'           => 'field_tf_assigned_sales_rep',
'label'         => 'Assigned Sales Rep',
'name'          => 'assigned_sales_rep',
'type'          => 'user',
'role'          => [ 'sales_rep' ],
'allow_null'    => 1,
'multiple'      => 0,
'return_format' => 'id',
],
[
'key'   => 'field_tf_rsm_bam',
'label' => 'RSM / BAM',
'name'  => 'rsm_bam',
'type'  => 'text',
],
[
'key'   => 'field_tf_fsa',
'label' => 'FSA',
'name'  => 'fsa',
'type'  => 'text',
],
[
'key'           => 'field_tf_record_status',
'label'         => 'Record Status',
'name'          => 'record_status',
'type'          => 'select',
'choices'       => [
'active'   => 'Active',
'archived' => 'Archived',
'on_hold'  => 'On Hold',
],
'default_value' => 'active',
'return_format' => 'value',
],
[
'key'           => 'field_tf_validation_status',
'label'         => 'Validation Status',
'name'          => 'validation_status',
'type'          => 'select',
'choices'       => [
'pending'  => 'Pending',
'verified' => 'Verified',
'rejected' => 'Rejected',
],
'default_value' => 'pending',
'return_format' => 'value',
],
[
'key'   => 'field_tf_admin_notes',
'label' => 'Admin Notes',
'name'  => 'admin_notes',
'type'  => 'textarea',
'rows'  => 3,
],
],
'location'              => trufield_acf_location_rule(),
'menu_order'            => 10,
'position'              => 'side',
'style'                 => 'default',
'label_placement'       => 'top',
'instruction_placement' => 'label',
'active'                => true,
] );

acf_add_local_field_group( [
'key'                   => 'group_tf_field_identity',
'title'                 => 'Field Identity & Contacts',
'fields'                => [
[
'key'      => 'field_tf_retailer_name',
'label'    => 'Retailer Name',
'name'     => 'retailer_name',
'type'     => 'text',
'required' => 1,
],
[
'key'   => 'field_tf_retailer_key_contact',
'label' => 'Retailer Key Contact',
'name'  => 'retailer_key_contact',
'type'  => 'text',
],
[
'key'      => 'field_tf_farm_name',
'label'    => 'Grower Name / Farm Name',
'name'     => 'farm_name',
'type'     => 'text',
'required' => 1,
],
[
'key'      => 'field_tf_field_trial_contact',
'label'    => 'Field Trial Contact',
'name'     => 'field_trial_contact',
'type'     => 'text',
'required' => 1,
],
[
'key'   => 'field_tf_contact_phone',
'label' => 'Contact Phone',
'name'  => 'contact_phone',
'type'  => 'text',
],
[
'key'      => 'field_tf_field_name',
'label'    => 'Field Name / Field ID',
'name'     => 'field_name',
'type'     => 'text',
'required' => 1,
],
// The Google Places map field below will automatically populate the address, latitude, and longitude fields when a location is selected
[
'key'      => 'field_tf_field_location_address',
'label'    => 'Field Location Address',
'name'     => 'field_location_address',
'type'     => 'textarea',
'rows'     => 2,
'required' => 1,
],
[
'key'          => 'field_tf_field_location_google_place',
'label'        => 'Field Location (Google Places)',
'name'         => 'field_location_google_place',
'type'         => 'google_map',
'instructions' => 'Search for the field location with Google Places. Address, latitude, and longitude are synced automatically.',
'required'     => 0,
'center_lat'   => '39.8283',
'center_lng'   => '-98.5795',
'zoom'         => 14,
'height'       => 320,
],
[
'key'   => 'field_tf_field_location_lat',
'label' => 'Field Location Lat',
'name'  => 'field_location_lat',
'type'  => 'number',
'step'  => '0.000001',
],
[
'key'   => 'field_tf_field_location_lng',
'label' => 'Field Location Lng',
'name'  => 'field_location_lng',
'type'  => 'number',
'step'  => '0.000001',
],
],
'location'              => trufield_acf_location_rule(),
'menu_order'            => 20,
'position'              => 'normal',
'style'                 => 'default',
'label_placement'       => 'top',
'instruction_placement' => 'label',
'active'                => true,
] );

acf_add_local_field_group( [
'key'                   => 'group_tf_phase_1',
'title'                 => 'Phase 1 — Trial Setup',
'fields'                => [
[
'key'      => 'field_tf_phase_1_state_region',
'label'    => 'State / Region',
'name'     => 'phase_1_state_region',
'type'     => 'text',
'required' => 1,
],
[
'key'      => 'field_tf_phase_1_product_being_tested',
'label'    => 'Product Being Tested',
'name'     => 'phase_1_product_being_tested',
'type'     => 'text',
'required' => 1,
],
[
'key'           => 'field_tf_phase_1_application_type',
'label'         => 'Application Type',
'name'          => 'phase_1_application_type',
'type'          => 'select',
'choices'       => [
'in_furrow'      => 'In-Furrow',
'seed_treatment' => 'Seed Treatment',
'foliar'         => 'Foliar',
'other'          => 'Other',
],
'allow_null'    => 1,
'return_format' => 'value',
'required'      => 1,
],
[
'key'            => 'field_tf_phase_1_application_date',
'label'          => 'Application Date',
'name'           => 'phase_1_application_date',
'type'           => 'date_picker',
'display_format' => 'm/d/Y',
'return_format'  => 'Y-m-d',
'required'       => 1,
],
[
'key'         => 'field_tf_phase_1_application_rate',
'label'       => 'Application Rate',
'name'        => 'phase_1_application_rate',
'type'        => 'text',
'required'    => 1,
'placeholder' => 'e.g. 32 oz/ac',
],
[
'key'           => 'field_tf_phase_1_trial_design',
'label'         => 'Trial Design',
'name'          => 'phase_1_trial_design',
'type'          => 'select',
'choices'       => [
'strip'        => 'Strip',
'side_by_side' => 'Side-by-Side',
'demo'         => 'Demo',
],
'allow_null'    => 1,
'return_format' => 'value',
'required'      => 1,
],
[
'key'         => 'field_tf_phase_1_growth_stage_at_application',
'label'       => 'Growth Stage at Application',
'name'        => 'phase_1_growth_stage_at_application',
'type'        => 'text',
'required'    => 1,
'placeholder' => 'e.g. V3',
],
[
'key'      => 'field_tf_phase_1_weather_conditions_at_application',
'label'    => 'Weather Conditions at Application',
'name'     => 'phase_1_weather_conditions_at_application',
'type'     => 'textarea',
'rows'     => 2,
'required' => 1,
],
[
'key'      => 'field_tf_phase_1_soil_conditions_at_application',
'label'    => 'Soil Conditions at Application',
'name'     => 'phase_1_soil_conditions_at_application',
'type'     => 'textarea',
'rows'     => 2,
'required' => 1,
],
[
'key'           => 'field_tf_phase_1_trial_type',
'label'         => 'Trial Type',
'name'          => 'phase_1_trial_type',
'type'          => 'select',
'choices'       => [
'standard'    => 'Standard Trial',
'split_field' => 'Split Field',
'replicated'  => 'Replicated',
'on_farm'     => 'On-Farm',
],
'allow_null'    => 1,
'return_format' => 'value',
'required'      => 1,
],
[
'key'      => 'field_tf_phase_1_treated_size_acres',
'label'    => 'Treated Size (Acres)',
'name'     => 'phase_1_treated_size_acres',
'type'     => 'number',
'required' => 1,
'min'      => 0,
'step'     => '0.01',
],
[
'key'      => 'field_tf_phase_1_carrier_volume_gal',
'label'    => 'Carrier Volume (Gal)',
'name'     => 'phase_1_carrier_volume_gal',
'type'     => 'number',
'required' => 1,
'min'      => 0,
'step'     => '0.1',
],
[
'key'         => 'field_tf_phase_1_protocol_version',
'label'       => 'Protocol Version',
'name'        => 'phase_1_protocol_version',
'type'        => 'text',
'required'    => 1,
'placeholder' => 'e.g. v2.1',
],
[
'key'   => 'field_tf_phase_1_hybrid_variety',
'label' => 'Hybrid Variety',
'name'  => 'phase_1_hybrid_variety',
'type'  => 'text',
],
[
'key'            => 'field_tf_phase_1_planting_date',
'label'          => 'Planting Date',
'name'           => 'phase_1_planting_date',
'type'           => 'date_picker',
'display_format' => 'm/d/Y',
'return_format'  => 'Y-m-d',
],
[
'key'   => 'field_tf_phase_1_planting_population',
'label' => 'Planting Population',
'name'  => 'phase_1_planting_population',
'type'  => 'number',
'min'   => 0,
],
[
'key'   => 'field_tf_phase_1_row_spacing',
'label' => 'Row Spacing',
'name'  => 'phase_1_row_spacing',
'type'  => 'number',
'min'   => 0,
'step'  => '0.1',
],
[
'key'   => 'field_tf_phase_1_planting_speed',
'label' => 'Planting Speed',
'name'  => 'phase_1_planting_speed',
'type'  => 'number',
'min'   => 0,
'step'  => '0.1',
],
[
'key'         => 'field_tf_phase_1_field_overview_photo',
'label'       => 'Field Overview Photo',
'name'        => 'phase_1_field_overview_photo',
'type'        => 'url',
'placeholder' => 'Photo URL',
'instructions'=> 'This field stores the uploaded photo URL. Use the portal Phase 1 form to upload or replace the image.',
],
],
'location'              => trufield_acf_location_rule(),
'menu_order'            => 50,
'position'              => 'normal',
'style'                 => 'default',
'label_placement'       => 'top',
'instruction_placement' => 'label',
'active'                => true,
] );

// Phase 2 is inactive until added to TRUFIELD_ACTIVE_PHASES.
acf_add_local_field_group( [
'key'                   => 'group_tf_phase_2',
'title'                 => 'Phase 2 — Application & Monitoring',
'fields'                => [
[
'key'           => 'field_tf_phase_2_application_type',
'label'         => 'Application Type',
'name'          => 'phase_2_application_type',
'type'          => 'select',
'choices'       => [
'in_furrow'      => 'In-Furrow',
'seed_treatment' => 'Seed Treatment',
'foliar'         => 'Foliar',
],
'allow_null'    => 1,
'return_format' => 'value',
'required'      => 1,
],
[
'key'            => 'field_tf_phase_2_application_date',
'label'          => 'Application Date',
'name'           => 'phase_2_application_date',
'type'           => 'date_picker',
'display_format' => 'm/d/Y',
'return_format'  => 'Y-m-d',
'required'       => 1,
],
[
'key'      => 'field_tf_phase_2_verify_acres_treated',
'label'    => 'Verify Acres Treated',
'name'     => 'phase_2_verify_acres_treated',
'type'     => 'number',
'required' => 1,
'min'      => 0,
'step'     => '0.01',
],
[
'key'      => 'field_tf_phase_2_verify_carrier_volume_gal',
'label'    => 'Verify Carrier Volume (Gal)',
'name'     => 'phase_2_verify_carrier_volume_gal',
'type'     => 'number',
'required' => 1,
'min'      => 0,
'step'     => '0.1',
],
[
'key'      => 'field_tf_phase_2_soil_temp_f',
'label'    => 'Soil Temp (F)',
'name'     => 'phase_2_soil_temp_f',
'type'     => 'number',
'required' => 1,
'step'     => '0.1',
],
[
'key'         => 'field_tf_phase_2_emergence',
'label'       => 'Emergence',
'name'        => 'phase_2_emergence',
'type'        => 'text',
'required'    => 1,
'placeholder' => 'e.g. 85%',
],
[
'key'      => 'field_tf_phase_2_stand_count',
'label'    => 'Stand Count',
'name'     => 'phase_2_stand_count',
'type'     => 'number',
'required' => 1,
'min'      => 0,
],
[
'key'         => 'field_tf_phase_2_plant_heights',
'label'       => 'Plant Heights',
'name'        => 'phase_2_plant_heights',
'type'        => 'text',
'required'    => 1,
'placeholder' => 'e.g. 4-6 inches',
],
[
'key'          => 'field_tf_phase_2_required_photos',
'label'        => 'Required Photos',
'name'         => 'phase_2_required_photos',
'type'         => 'textarea',
'required'     => 1,
'rows'         => 2,
'instructions' => 'Enter photo URLs or descriptions, one per line. Required for phase completion.',
],
[
'key'           => 'field_tf_phase_2_emergence_stand_uniformity',
'label'         => 'Emergence / Stand Uniformity',
'name'          => 'phase_2_emergence_stand_uniformity',
'type'          => 'select',
'choices'       => [
'excellent' => 'Excellent',
'good'      => 'Good',
'fair'      => 'Fair',
'poor'      => 'Poor',
],
'allow_null'    => 1,
'return_format' => 'value',
],
[
'key'           => 'field_tf_phase_2_plant_vigor',
'label'         => 'Plant Vigor',
'name'          => 'phase_2_plant_vigor',
'type'          => 'select',
'choices'       => [
'excellent' => 'Excellent',
'good'      => 'Good',
'fair'      => 'Fair',
'poor'      => 'Poor',
],
'allow_null'    => 1,
'return_format' => 'value',
],
[
'key'           => 'field_tf_phase_2_residue_degradation',
'label'         => 'Residue Degradation',
'name'          => 'phase_2_residue_degradation',
'type'          => 'select',
'choices'       => [
'yes' => 'Yes',
'no'  => 'No',
],
'allow_null'    => 1,
'return_format' => 'value',
],
[
'key'         => 'field_tf_phase_2_app_machine_data',
'label'       => 'App Machine Data',
'name'        => 'phase_2_app_machine_data',
'type'        => 'url',
'placeholder' => 'Machine data URL',
],
[
'key'         => 'field_tf_phase_2_planting_machine_data',
'label'       => 'Planting Machine Data',
'name'        => 'phase_2_planting_machine_data',
'type'        => 'url',
'placeholder' => 'Planting machine data URL',
],
[
'key'         => 'field_tf_phase_2_optional_video',
'label'       => 'Optional Video',
'name'        => 'phase_2_optional_video',
'type'        => 'url',
'placeholder' => 'Video URL (optional)',
],
[
'key'         => 'field_tf_phase_2_optional_timelapse',
'label'       => 'Optional Timelapse',
'name'        => 'phase_2_optional_timelapse',
'type'        => 'url',
'placeholder' => 'Timelapse URL (optional)',
],
[
'key'         => 'field_tf_phase_2_optional_drone_media',
'label'       => 'Optional Drone Media',
'name'        => 'phase_2_optional_drone_media',
'type'        => 'url',
'placeholder' => 'Drone media URL (optional)',
],
[
'key'   => 'field_tf_phase_2_testimonial',
'label' => 'Testimonial',
'name'  => 'phase_2_testimonial',
'type'  => 'textarea',
'rows'  => 3,
],
[
'key'         => 'field_tf_phase_2_pictures_at_application',
'label'       => 'Pictures at Application',
'name'        => 'phase_2_pictures_at_application',
'type'        => 'textarea',
'rows'        => 2,
'placeholder' => 'Application photo URLs',
],
[
'key'         => 'field_tf_phase_2_pictures_at_planting',
'label'       => 'Pictures at Planting',
'name'        => 'phase_2_pictures_at_planting',
'type'        => 'textarea',
'rows'        => 2,
'placeholder' => 'Planting photo URLs',
],
],
'location'              => trufield_acf_location_rule(),
'menu_order'            => 60,
'position'              => 'normal',
'style'                 => 'default',
'label_placement'       => 'top',
'instruction_placement' => 'label',
'active'                => in_array( 2, TRUFIELD_ACTIVE_PHASES, true ),
] );

// Phase 3 is inactive until added to TRUFIELD_ACTIVE_PHASES.
acf_add_local_field_group( [
'key'                   => 'group_tf_phase_3',
'title'                 => 'Phase 3 — Harvest & Engagement',
'fields'                => [
[
'key'      => 'field_tf_phase_3_yield_bu_ac',
'label'    => 'Yield (bu/ac)',
'name'     => 'phase_3_yield_bu_ac',
'type'     => 'number',
'required' => 1,
'min'      => 0,
'step'     => '0.01',
],
[
'key'      => 'field_tf_phase_3_moisture_percent',
'label'    => 'Moisture (%)',
'name'     => 'phase_3_moisture_percent',
'type'     => 'number',
'required' => 1,
'min'      => 0,
'max'      => 100,
'step'     => '0.1',
],
[
'key'      => 'field_tf_phase_3_test_weight_lbs_bu',
'label'    => 'Test Weight (lbs/bu)',
'name'     => 'phase_3_test_weight_lbs_bu',
'type'     => 'number',
'required' => 1,
'min'      => 0,
'step'     => '0.01',
],
[
'key'   => 'field_tf_phase_3_stalk_diameter',
'label' => 'Stalk Diameter',
'name'  => 'phase_3_stalk_diameter',
'type'  => 'number',
'step'  => '0.01',
],
[
'key'           => 'field_tf_phase_3_root_vigor',
'label'         => 'Root Vigor',
'name'          => 'phase_3_root_vigor',
'type'          => 'select',
'choices'       => [
'excellent' => 'Excellent',
'good'      => 'Good',
'fair'      => 'Fair',
'poor'      => 'Poor',
],
'allow_null'    => 1,
'return_format' => 'value',
],
[
'key'         => 'field_tf_phase_3_harvest_photos',
'label'       => 'Harvest Photos',
'name'        => 'phase_3_harvest_photos',
'type'        => 'textarea',
'rows'        => 2,
'placeholder' => 'Harvest photo URLs',
],
[
'key'   => 'field_tf_phase_3_comments',
'label' => 'Comments',
'name'  => 'phase_3_comments',
'type'  => 'textarea',
'rows'  => 3,
],
[
'key'            => 'field_tf_phase_3_event_date',
'label'          => 'Event Date',
'name'           => 'phase_3_event_date',
'type'           => 'date_picker',
'display_format' => 'm/d/Y',
'return_format'  => 'Y-m-d',
'required'       => 1,
],
[
'key'           => 'field_tf_phase_3_event_type',
'label'         => 'Event Type',
'name'          => 'phase_3_event_type',
'type'          => 'select',
'choices'       => [
'field_day'      => 'Field Day',
'grower_meeting' => 'Grower Meeting',
'dealer_meeting' => 'Dealer Meeting',
'other'          => 'Other',
],
'allow_null'    => 1,
'return_format' => 'value',
'required'      => 1,
],
[
'key'      => 'field_tf_phase_3_event_location',
'label'    => 'Event Location',
'name'     => 'phase_3_event_location',
'type'     => 'text',
'required' => 1,
],
[
'key'      => 'field_tf_phase_3_attendee_count',
'label'    => 'Attendee Count',
'name'     => 'phase_3_attendee_count',
'type'     => 'number',
'required' => 1,
'min'      => 0,
],
[
'key'          => 'field_tf_phase_3_required_event_media',
'label'        => 'Required Event Media',
'name'         => 'phase_3_required_event_media',
'type'         => 'textarea',
'required'     => 1,
'rows'         => 2,
'instructions' => 'Enter event media URLs or descriptions, one per line. Required for phase completion.',
],
[
'key'         => 'field_tf_phase_3_optional_video',
'label'       => 'Optional Video',
'name'        => 'phase_3_optional_video',
'type'        => 'url',
'placeholder' => 'Video URL (optional)',
],
[
'key'   => 'field_tf_phase_3_testimonial',
'label' => 'Testimonial',
'name'  => 'phase_3_testimonial',
'type'  => 'textarea',
'rows'  => 3,
],
],
'location'              => trufield_acf_location_rule(),
'menu_order'            => 70,
'position'              => 'normal',
'style'                 => 'default',
'label_placement'       => 'top',
'instruction_placement' => 'label',
'active'                => in_array( 3, TRUFIELD_ACTIVE_PHASES, true ),
] );

acf_add_local_field_group( [
'key'                   => 'group_tf_workflow',
'title'                 => 'Workflow',
'fields'                => [
[
'key'           => 'field_tf_current_phase',
'label'         => 'Current Phase',
'name'          => 'current_phase',
'type'          => 'number',
'min'           => 1,
'max'           => 3,
'default_value' => 1,
],
[
'key'           => 'field_tf_phase_1_status',
'label'         => 'Phase 1 Status',
'name'          => 'phase_1_status',
'type'          => 'select',
'choices'       => [
'pending'     => 'Pending',
'in_progress' => 'In Progress',
'completed'   => 'Completed',
],
'default_value' => 'pending',
'return_format' => 'value',
],
[
'key'           => 'field_tf_phase_2_status',
'label'         => 'Phase 2 Status',
'name'          => 'phase_2_status',
'type'          => 'select',
'choices'       => [
'pending'     => 'Pending',
'in_progress' => 'In Progress',
'completed'   => 'Completed',
],
'default_value' => 'pending',
'return_format' => 'value',
],
[
'key'           => 'field_tf_phase_3_status',
'label'         => 'Phase 3 Status',
'name'          => 'phase_3_status',
'type'          => 'select',
'choices'       => [
'pending'     => 'Pending',
'in_progress' => 'In Progress',
'completed'   => 'Completed',
],
'default_value' => 'pending',
'return_format' => 'value',
],
[
'key'            => 'field_tf_phase_1_completed_at',
'label'          => 'Phase 1 Completed At',
'name'           => 'phase_1_completed_at',
'type'           => 'date_time_picker',
'display_format' => 'm/d/Y g:i a',
'return_format'  => 'Y-m-d H:i:s',
'instructions'   => 'Read-only reference field. Set automatically when Phase 1 is completed.',
],
[
'key'            => 'field_tf_phase_2_completed_at',
'label'          => 'Phase 2 Completed At',
'name'           => 'phase_2_completed_at',
'type'           => 'date_time_picker',
'display_format' => 'm/d/Y g:i a',
'return_format'  => 'Y-m-d H:i:s',
'instructions'   => 'Read-only reference field. Set automatically when Phase 2 is completed.',
],
[
'key'            => 'field_tf_phase_3_completed_at',
'label'          => 'Phase 3 Completed At',
'name'           => 'phase_3_completed_at',
'type'           => 'date_time_picker',
'display_format' => 'm/d/Y g:i a',
'return_format'  => 'Y-m-d H:i:s',
'instructions'   => 'Read-only reference field. Set automatically when Phase 3 is completed.',
],
[
'key'           => 'field_tf_phase_1_verified',
'label'         => 'Phase 1 Verified',
'name'          => 'phase_1_verified',
'type'          => 'true_false',
'default_value' => 0,
'ui'            => 1,
],
[
'key'           => 'field_tf_phase_2_verified',
'label'         => 'Phase 2 Verified',
'name'          => 'phase_2_verified',
'type'          => 'true_false',
'default_value' => 0,
'ui'            => 1,
],
[
'key'           => 'field_tf_phase_3_verified',
'label'         => 'Phase 3 Verified',
'name'          => 'phase_3_verified',
'type'          => 'true_false',
'default_value' => 0,
'ui'            => 1,
],
[
'key'            => 'field_tf_phase_1_verified_at',
'label'          => 'Phase 1 Verified At',
'name'           => 'phase_1_verified_at',
'type'           => 'date_time_picker',
'display_format' => 'm/d/Y g:i a',
'return_format'  => 'Y-m-d H:i:s',
],
[
'key'            => 'field_tf_phase_2_verified_at',
'label'          => 'Phase 2 Verified At',
'name'           => 'phase_2_verified_at',
'type'           => 'date_time_picker',
'display_format' => 'm/d/Y g:i a',
'return_format'  => 'Y-m-d H:i:s',
],
[
'key'            => 'field_tf_phase_3_verified_at',
'label'          => 'Phase 3 Verified At',
'name'           => 'phase_3_verified_at',
'type'           => 'date_time_picker',
'display_format' => 'm/d/Y g:i a',
'return_format'  => 'Y-m-d H:i:s',
],
],
'location'              => trufield_acf_location_rule(),
'menu_order'            => 80,
'position'              => 'side',
'style'                 => 'default',
'label_placement'       => 'top',
'instruction_placement' => 'label',
'active'                => true,
] );
}

function trufield_acf_location_rule(): array {
return [
[
[
'param'    => 'post_type',
'operator' => '==',
'value'    => 'plant_field',
],
],
];
}

add_action( 'acf/init', 'trufield_configure_google_maps_api_key', 20 );
function trufield_configure_google_maps_api_key(): void {
if ( ! function_exists( 'acf_update_setting' ) ) {
return;
}

$api_key = '';

if ( defined( 'TRUFIELD_GOOGLE_MAPS_API_KEY' ) ) {
$api_key = (string) TRUFIELD_GOOGLE_MAPS_API_KEY;
} elseif ( getenv( 'TRUFIELD_GOOGLE_MAPS_API_KEY' ) ) {
$api_key = (string) getenv( 'TRUFIELD_GOOGLE_MAPS_API_KEY' );
}

if ( $api_key !== '' ) {
acf_update_setting( 'google_api_key', $api_key );
}
}

add_filter( 'acf/fields/google_map/api', 'trufield_acf_google_map_api' );
function trufield_acf_google_map_api( array $api ): array {
$api_key = '';

if ( defined( 'TRUFIELD_GOOGLE_MAPS_API_KEY' ) ) {
$api_key = (string) TRUFIELD_GOOGLE_MAPS_API_KEY;
} elseif ( getenv( 'TRUFIELD_GOOGLE_MAPS_API_KEY' ) ) {
$api_key = (string) getenv( 'TRUFIELD_GOOGLE_MAPS_API_KEY' );
}

if ( $api_key !== '' ) {
$api['key'] = $api_key;
}

return $api;
}

add_filter( 'acf/load_value/name=field_location_google_place', 'trufield_prefill_google_place_from_legacy_fields', 10, 3 );
function trufield_prefill_google_place_from_legacy_fields( $value, $post_id, array $field ) {
if ( ! empty( $value ) ) {
return $value;
}

$post_id = is_numeric( $post_id ) ? (int) $post_id : 0;
if ( $post_id <= 0 ) {
return $value;
}

$address = (string) get_post_meta( $post_id, 'field_location_address', true );
$lat     = get_post_meta( $post_id, 'field_location_lat', true );
$lng     = get_post_meta( $post_id, 'field_location_lng', true );

if ( $address === '' && $lat === '' && $lng === '' ) {
return $value;
}

return [
'address' => $address,
'lat'     => $lat !== '' ? (float) $lat : '',
'lng'     => $lng !== '' ? (float) $lng : '',
'zoom'    => 14,
];
}

add_filter( 'acf/update_value/name=field_location_google_place', 'trufield_sync_google_place_to_legacy_fields', 10, 3 );
function trufield_sync_google_place_to_legacy_fields( $value, $post_id, array $field ) {
$post_id = is_numeric( $post_id ) ? (int) $post_id : 0;
if ( $post_id <= 0 || ! is_array( $value ) ) {
return $value;
}

$address = isset( $value['address'] ) ? sanitize_textarea_field( (string) $value['address'] ) : '';
$lat     = isset( $value['lat'] ) ? trim( (string) $value['lat'] ) : '';
$lng     = isset( $value['lng'] ) ? trim( (string) $value['lng'] ) : '';

if ( $address !== '' ) {
update_post_meta( $post_id, 'field_location_address', $address );
}

if ( $lat !== '' ) {
update_post_meta( $post_id, 'field_location_lat', (float) $lat );
}

if ( $lng !== '' ) {
update_post_meta( $post_id, 'field_location_lng', (float) $lng );
}

return $value;
}
