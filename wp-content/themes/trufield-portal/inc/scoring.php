<?php
/**
 * TruField Portal — Scoring Helpers
 *
 * SCORING DEFERRED: No points are awarded in this version. Functions return
 * zeros. Leaderboard ranks by verified phase completions. Do not remove this
 * file; functions are referenced elsewhere.
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

define( 'TRUFIELD_PHASE_POINTS', [
1 => 0,
2 => 0,
3 => 0,
] );

function trufield_phase_is_verified( int $post_id, int $phase ): bool {
return (bool) get_post_meta( $post_id, "phase_{$phase}_verified", true );
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

return [
'pending'          => 0,
'verified'         => 0,
'total'            => 0,
'verified_phases'  => $verified_phases,
'completed_phases' => $completed_phases,
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
];

foreach ( $fields as $post ) {
$score = trufield_get_field_score( $post->ID );

$agg['verified_phases']  += $score['verified_phases'];
$agg['completed_phases'] += $score['completed_phases'];
$agg['field_count']++;

if ( $score['completed_phases'] > 0 ) {
$agg['completed_fields']++;
}
}

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
'total'            => 0,
'verified_phases'  => $score['verified_phases'],
'completed_phases' => $score['completed_phases'],
'completed_fields' => $score['completed_fields'],
'field_count'      => $score['field_count'],
];
}

usort(
$rows,
static function ( array $a, array $b ): int {
if ( $b['verified_phases'] !== $a['verified_phases'] ) {
return $b['verified_phases'] <=> $a['verified_phases'];
}

if ( $b['completed_phases'] !== $a['completed_phases'] ) {
return $b['completed_phases'] <=> $a['completed_phases'];
}

if ( $b['field_count'] !== $a['field_count'] ) {
return $b['field_count'] <=> $a['field_count'];
}

return strcasecmp( $a['display_name'], $b['display_name'] );
}
);

return $rows;
}
