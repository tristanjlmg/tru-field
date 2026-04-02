<?php
/**
 * TruField Portal — Scoring Helpers
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

define( 'TRUFIELD_PHASE_POINTS', [
1 => 10,
2 => 0,
3 => 0,
] );

define( 'TRUFIELD_RETAILER_VALID_ENTRY_THRESHOLD', 5 );

function trufield_get_retailer_points_threshold(): int {
	return TRUFIELD_RETAILER_VALID_ENTRY_THRESHOLD;
}

function trufield_get_retailer_points_award(): int {
	return (int) ( TRUFIELD_PHASE_POINTS[1] ?? 0 );
}

function trufield_phase_is_verified( int $post_id, int $phase ): bool {
return (bool) get_post_meta( $post_id, "phase_{$phase}_verified", true );
}

function trufield_normalize_retailer_key( string $retailer_name ): string {
	$retailer_name = sanitize_text_field( $retailer_name );
	$retailer_name = strtolower( trim( preg_replace( '/\s+/', ' ', $retailer_name ) ) );
	return $retailer_name;
}

function trufield_get_field_score( int $post_id ): array {
$verified_phases  = 0;
$completed_phases = 0;

foreach ( [ 1, 2, 3 ] as $phase ) {
if ( trufield_get_phase_status( $post_id, $phase ) === 'completed' ) {
++$completed_phases;
}

if ( trufield_phase_is_verified( $post_id, $phase ) ) {
++$verified_phases;
}
}

$retailer_name = (string) get_post_meta( $post_id, 'retailer_name', true );
$phase_1_valid = trufield_phase_is_verified( $post_id, 1 );

return [
'pending'          => 0,
'verified'         => $phase_1_valid ? trufield_get_retailer_points_award() : 0,
'total'            => $phase_1_valid ? trufield_get_retailer_points_award() : 0,
'verified_phases'  => $verified_phases,
'completed_phases' => $completed_phases,
'valid_phase_1'    => $phase_1_valid ? 1 : 0,
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
'awarded_retailers'=> 0,
'retailer_count'   => 0,
'points'           => 0,
];
	$retailer_valid_counts = [];

foreach ( $fields as $post ) {
$score = trufield_get_field_score( $post->ID );

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
				if ( ! isset( $retailer_valid_counts[ $retailer_key ] ) ) {
					$retailer_valid_counts[ $retailer_key ] = [
						'name'  => $retailer_name,
						'count' => 0,
					];
				}

				$retailer_valid_counts[ $retailer_key ]['count']++;
			}

			$agg['valid_entries']++;
		}
}

	$threshold = trufield_get_retailer_points_threshold();
	$award     = trufield_get_retailer_points_award();

	foreach ( $retailer_valid_counts as $retailer_data ) {
		if ( (int) $retailer_data['count'] >= $threshold ) {
			$agg['awarded_retailers']++;
		}
	}

	$agg['retailer_count'] = count( $retailer_valid_counts );
	$agg['points']         = $agg['awarded_retailers'] * $award;
	$agg['verified']       = $agg['points'];
	$agg['total']          = $agg['points'];

return $agg;
}

function trufield_get_leaderboard(): array {
$reps = get_users( [
'role'    => 'sales_rep',
'orderby' => 'display_name',
'order'   => 'ASC',
'fields'  => [ 'ID', 'display_name' ],
] );

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

if ( $b['awarded_retailers'] !== $a['awarded_retailers'] ) {
return $b['awarded_retailers'] <=> $a['awarded_retailers'];
}

if ( $b['valid_entries'] !== $a['valid_entries'] ) {
return $b['valid_entries'] <=> $a['valid_entries'];
}

if ( $b['field_count'] !== $a['field_count'] ) {
return $b['field_count'] <=> $a['field_count'];
}

return strcasecmp( $a['display_name'], $b['display_name'] );
}
);

return $rows;
}
