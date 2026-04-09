<?php
/**
 * Template Name: Portal Leaderboard
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

get_header();
$leaderboard = trufield_get_leaderboard();
$current_uid = get_current_user_id();
?>
<div class="tf-container">
<h1><?php esc_html_e( 'Leaderboard', 'trufield-portal' ); ?></h1>

<div class="tf-alert tf-alert--info tf-leaderboard-placeholder">
<strong><?php esc_html_e( '5-Grower Threshold', 'trufield-portal' ); ?></strong><br>
<?php
	printf(
		esc_html__( 'Each retailer awards %1$d Phase 1 points once a rep reaches at least %2$d valid grower entries for that retailer. Rankings are based on total points across all phases.', 'trufield-portal' ),
		trufield_get_retailer_points_award(),
		trufield_get_retailer_points_threshold()
	);
	?>
</div>

<?php if ( empty( $leaderboard ) ) : ?>
<p><?php esc_html_e( 'No rep activity yet.', 'trufield-portal' ); ?></p>
<?php else : ?>
<div class="tf-leaderboard">
<table class="tf-table tf-table--leaderboard">
<thead>
<tr>
<th scope="col" class="tf-table__rank"><?php esc_html_e( 'Rank', 'trufield-portal' ); ?></th>
<th scope="col"><?php esc_html_e( 'Rep', 'trufield-portal' ); ?></th>
<th scope="col" class="tf-table__num"><?php esc_html_e( 'Total Points', 'trufield-portal' ); ?></th>
<th scope="col" class="tf-table__num"><?php esc_html_e( 'Valid Entries', 'trufield-portal' ); ?></th>
<th scope="col" class="tf-table__num"><?php esc_html_e( 'Retailers Awarded', 'trufield-portal' ); ?></th>
<th scope="col" class="tf-table__num"><?php esc_html_e( 'Fields', 'trufield-portal' ); ?></th>
</tr>
</thead>
<tbody>
<?php foreach ( $leaderboard as $rank => $row ) : ?>
<?php $is_me = ( (int) $row['user_id'] === (int) $current_uid ); ?>
<tr class="tf-leaderboard__row<?php echo $is_me ? ' tf-leaderboard__row--me' : ''; ?>">
<td class="tf-table__rank">
<?php if ( $rank === 0 ) : ?>
<span class="tf-badge tf-badge--gold">#1</span>
<?php elseif ( $rank === 1 ) : ?>
<span class="tf-badge tf-badge--silver">#2</span>
<?php elseif ( $rank === 2 ) : ?>
<span class="tf-badge tf-badge--bronze">#3</span>
<?php else : ?>
#<?php echo (int) ( $rank + 1 ); ?>
<?php endif; ?>
</td>
<td>
<?php echo esc_html( $row['display_name'] ); ?>
<?php if ( $is_me ) : ?>
<span class="tf-badge tf-badge--me"><?php esc_html_e( 'You', 'trufield-portal' ); ?></span>
<?php endif; ?>
</td>
<td class="tf-table__num tf-table__num--highlight"><strong><?php echo (int) $row['points']; ?></strong></td>
<td class="tf-table__num"><?php echo (int) $row['valid_entries']; ?></td>
<td class="tf-table__num"><?php echo (int) $row['awarded_retailers']; ?></td>
<td class="tf-table__num"><?php echo (int) $row['field_count']; ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</div>
<?php get_footer(); ?>
