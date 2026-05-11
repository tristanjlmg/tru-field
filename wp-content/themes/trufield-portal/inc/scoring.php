<?php
/**
 * TruField Portal — Scoring Helpers
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

define( 'TRUFIELD_PHASE_POINTS', [
1 => 10,
2 => 10,
3 => 0,
] );

define( 'TRUFIELD_RETAILER_VALID_ENTRY_THRESHOLD', 5 );

function trufield_get_retailer_points_threshold(): int {
	return TRUFIELD_RETAILER_VALID_ENTRY_THRESHOLD;
}

function trufield_get_retailer_points_award(): int {
	return (int) ( TRUFIELD_PHASE_POINTS[1] ?? 0 );
}

function trufield_get_phase_points_award( int $phase ): int {
	return (int) ( TRUFIELD_PHASE_POINTS[ $phase ] ?? 0 );
}

function trufield_get_valid_entry_award_count( int $valid_entries ): int {
	$threshold = trufield_get_retailer_points_threshold();

	if ( $threshold <= 0 || $valid_entries < $threshold ) {
		return 0;
	}

	return (int) floor( $valid_entries / $threshold );
}

function trufield_phase_uses_retailer_threshold_scoring( int $phase ): bool {
	return 1 === $phase;
}

function trufield_phase_is_verified( int $post_id, int $phase ): bool {
return (bool) get_post_meta( $post_id, "phase_{$phase}_verified", true );
}

function trufield_get_phase_2_scoring_required_visit_fields(): array {
	return [
		'phase_2_rsm_visit_1_date',
		'phase_2_rsm_visit_2_date',
	];
}

function trufield_get_phase_2_scoring_completion_fields(): array {
	return [
		'phase_2_rsm_visit_1_date',
		'phase_2_rsm_visit_2_date',
		'phase_2_rsm_visit_3_date',
		'phase_2_rsm_visit_4_date',
		'phase_2_stand_count_1_treated',
		'phase_2_stand_count_2_treated',
		'phase_2_stand_count_3_treated',
		'phase_2_stand_count_1_untreated',
		'phase_2_stand_count_2_untreated',
		'phase_2_stand_count_3_untreated',
		'phase_2_stand_count_data',
		'phase_2_most_significant_visual_difference',
		'phase_2_pictures_at_application',
		'phase_2_pictures_at_planting',
		'phase_2_pictures_in_season_harvest',
		'phase_2_pictures_at_harvest',
	];
}

function trufield_get_phase_2_scoring_photo_compliance_fields(): array {
	return [
		'phase_2_pictures_at_application',
		'phase_2_pictures_at_planting',
		'phase_2_pictures_in_season_harvest',
		'phase_2_pictures_at_harvest',
	];
}

function trufield_get_phase_2_scoring_media_fields(): array {
	return [
		'phase_2_drone_images_available',
		'phase_2_grower_retailer_testimonials',
		'phase_2_time_lapse_available',
	];
}

function trufield_get_phase_2_scoring_field_value( int $post_id, string $field ): string {
	$value = trim( (string) get_post_meta( $post_id, $field, true ) );

	if ( '' !== $value ) {
		return $value;
	}

	if ( 'phase_2_pictures_at_harvest' === $field ) {
		return trim( (string) get_post_meta( $post_id, 'phase_2_pictures_in_season_harvest', true ) );
	}

	return '';
}

function trufield_phase_2_scoring_field_present( int $post_id, string $field ): bool {
	return '' !== trufield_get_phase_2_scoring_field_value( $post_id, $field );
}

function trufield_phase_2_scoring_field_is_yes( int $post_id, string $field ): bool {
	return 'yes' === strtolower( trufield_get_phase_2_scoring_field_value( $post_id, $field ) );
}

function trufield_get_phase_2_scoring_status( int $post_id ): array {
	$required_visits_ok = true;
	foreach ( trufield_get_phase_2_scoring_required_visit_fields() as $field ) {
		if ( ! trufield_phase_2_scoring_field_present( $post_id, $field ) ) {
			$required_visits_ok = false;
			break;
		}
	}

	$row_fields_ok = true;
	foreach ( [
		'phase_2_stand_count_1_treated',
		'phase_2_stand_count_2_treated',
		'phase_2_stand_count_3_treated',
		'phase_2_stand_count_1_untreated',
		'phase_2_stand_count_2_untreated',
		'phase_2_stand_count_3_untreated',
		'phase_2_stand_count_data',
		'phase_2_most_significant_visual_difference',
	] as $field ) {
		if ( ! trufield_phase_2_scoring_field_present( $post_id, $field ) ) {
			$row_fields_ok = false;
			break;
		}
	}

	$photo_compliance_ok = true;
	foreach ( trufield_get_phase_2_scoring_photo_compliance_fields() as $field ) {
		if ( ! trufield_phase_2_scoring_field_is_yes( $post_id, $field ) ) {
			$photo_compliance_ok = false;
			break;
		}
	}

	$media_proof_ok = true;
	foreach ( trufield_get_phase_2_scoring_media_fields() as $field ) {
		if ( ! trufield_phase_2_scoring_field_is_yes( $post_id, $field ) ) {
			$media_proof_ok = false;
			break;
		}
	}

	$completion_ok = true;
	foreach ( trufield_get_phase_2_scoring_completion_fields() as $field ) {
		if ( ! trufield_phase_2_scoring_field_present( $post_id, $field ) ) {
			$completion_ok = false;
			break;
		}
	}

	$valid_trial = trufield_prerequisite_met( $post_id, 2 )
		&& $required_visits_ok
		&& $row_fields_ok
		&& $photo_compliance_ok
		&& $media_proof_ok;

	return [
		'required_visits_ok'  => $required_visits_ok,
		'row_fields_ok'       => $row_fields_ok,
		'photo_compliance_ok' => $photo_compliance_ok,
		'media_proof_ok'      => $media_proof_ok,
		'completion_ok'       => $completion_ok,
		'valid_trial'         => $valid_trial,
		'points'              => ( $valid_trial && $completion_ok ) ? trufield_get_phase_points_award( 2 ) : 0,
	];
}

function trufield_get_phase_2_trial_points( int $post_id ): int {
	$status = trufield_get_phase_2_scoring_status( $post_id );

	return (int) $status['points'];
}

function trufield_normalize_retailer_key( string $retailer_name ): string {
	$retailer_name = sanitize_text_field( $retailer_name );
	$retailer_name = strtolower( trim( preg_replace( '/\s+/', ' ', $retailer_name ) ) );
	return $retailer_name;
}

function trufield_get_field_score( int $post_id ): array {
$verified_phases  = 0;
$completed_phases = 0;
	$verified_points  = 0;
	$phase_2_points    = trufield_get_phase_2_trial_points( $post_id );
	$phase_2_status    = trufield_get_phase_2_scoring_status( $post_id );

foreach ( [ 1, 2, 3 ] as $phase ) {
if ( trufield_get_phase_status( $post_id, $phase ) === 'completed' ) {
++$completed_phases;
}

if ( trufield_phase_is_verified( $post_id, $phase ) ) {
++$verified_phases;
		if ( ! trufield_phase_uses_retailer_threshold_scoring( $phase ) ) {
			$verified_points += trufield_get_phase_points_award( $phase );
		}
}
}

	if ( $phase_2_points > 0 && ! trufield_phase_is_verified( $post_id, 2 ) ) {
		$verified_points += $phase_2_points;
	}

$retailer_name = (string) get_post_meta( $post_id, 'retailer_name', true );
$phase_1_valid = trufield_phase_is_verified( $post_id, 1 );

return [
'pending'          => 0,
'verified'         => $verified_points,
'total'            => $verified_points,
'verified_phases'  => $verified_phases,
'completed_phases' => $completed_phases,
'valid_phase_1'    => $phase_1_valid ? 1 : 0,
		'valid_phase_2'    => ! empty( $phase_2_status['valid_trial'] ) ? 1 : 0,
		'phase_2_points'   => $phase_2_points,
'retailer_name'    => $retailer_name,
];
}

function trufield_get_rep_score( int $rep_user_id ): array {
$fields = trufield_get_assigned_fields( $rep_user_id );
$agg    = [
'pending'          => 0,
'verified'         => 0,
'total'            => 0,
'verified_phases'  => 0,
'completed_phases' => 0,
'completed_fields' => 0,
'field_count'      => 0,
'valid_entries'    => 0,
		'valid_phase_2_trials' => 0,
'awarded_retailers'=> 0,
'retailer_count'   => 0,
'points'           => 0,
];
	$retailer_keys = [];

foreach ( $fields as $post ) {
$score = trufield_get_field_score( $post->ID );

		$agg['verified']         += $score['verified'];
		$agg['total']            += $score['total'];
$agg['verified_phases']  += $score['verified_phases'];
$agg['completed_phases'] += $score['completed_phases'];
$agg['field_count']++;

if ( $score['completed_phases'] > 0 ) {
$agg['completed_fields']++;
}

		if ( ! empty( $score['valid_phase_1'] ) ) {
			$retailer_name = (string) ( $score['retailer_name'] ?? '' );
			$retailer_key  = trufield_normalize_retailer_key( $retailer_name );
			if ( $retailer_key !== '' ) {
				$retailer_keys[ $retailer_key ] = true;
			}

			$agg['valid_entries']++;
		}

		if ( ! empty( $score['valid_phase_2'] ) ) {
			$agg['valid_phase_2_trials']++;
		}
}

	$award_blocks           = trufield_get_valid_entry_award_count( (int) $agg['valid_entries'] );
	$award                  = trufield_get_retailer_points_award();
	$agg['awarded_retailers'] = $award_blocks;
	$agg['retailer_count']  = count( $retailer_keys );
	$agg['points']         = $agg['total'] + ( $agg['awarded_retailers'] * $award );
	$agg['verified']      += $agg['awarded_retailers'] * $award;
	$agg['total']          = $agg['points'];

return $agg;
}

function trufield_get_leaderboard( int $sales_rep_id = 0 ): array {
	$reps = trufield_get_sales_rep_users();

	if ( $sales_rep_id > 0 ) {
		$reps = array_values(
			array_filter(
				$reps,
				static function ( $rep ) use ( $sales_rep_id ): bool {
					return isset( $rep->ID ) && (int) $rep->ID === $sales_rep_id;
				}
			)
		);
	}

$rows = [];
foreach ( $reps as $rep ) {
$score  = trufield_get_rep_score( (int) $rep->ID );
$rows[] = [
'user_id'          => (int) $rep->ID,
'display_name'     => $rep->display_name,
'verified'         => 0,
'pending'          => 0,
'total'            => $score['points'],
'verified_phases'  => $score['verified_phases'],
'completed_phases' => $score['completed_phases'],
'completed_fields' => $score['completed_fields'],
'field_count'      => $score['field_count'],
'valid_entries'    => $score['valid_entries'],
'awarded_retailers'=> $score['awarded_retailers'],
'retailer_count'   => $score['retailer_count'],
'points'           => $score['points'],
];
}

usort(
$rows,
static function ( array $a, array $b ): int {
if ( $b['points'] !== $a['points'] ) {
return $b['points'] <=> $a['points'];
}

return strcasecmp( $a['display_name'], $b['display_name'] );
}
);

return $rows;
}
