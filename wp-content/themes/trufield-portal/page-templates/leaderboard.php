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
$top_rankings = array_slice( $leaderboard, 0, 3 );
$rank_labels  = [
	1 => __( '1st Rank', 'trufield-portal' ),
	2 => __( '2nd Rank', 'trufield-portal' ),
	3 => __( '3rd Rank', 'trufield-portal' ),
];
?>
<div class="tf-container tf-leaderboard-page">
<header class="tf-leaderboard-hero">
<h1 class="tf-leaderboard-hero__title"><?php esc_html_e( 'Leaderboard', 'trufield-portal' ); ?></h1>
<p class="tf-leaderboard-hero__copy">
<?php
	printf(
		esc_html__( 'Each retailer awards %1$s points once a rep reaches at least %2$s valid grower entries for that retailer. Rankings are based on total points across all phases.', 'trufield-portal' ),
		number_format_i18n( trufield_get_retailer_points_award() ),
		number_format_i18n( trufield_get_retailer_points_threshold() )
	);
	?>
</p>
</header>

<?php if ( empty( $leaderboard ) ) : ?>
<div class="tf-empty-state tf-empty-state--leaderboard">
<p><?php esc_html_e( 'No rep activity yet.', 'trufield-portal' ); ?></p>
</div>
<?php else : ?>
<?php if ( ! empty( $top_rankings ) ) : ?>
<section class="tf-leaderboard-podium" aria-label="<?php esc_attr_e( 'Top ranking reps', 'trufield-portal' ); ?>">
<?php foreach ( $top_rankings as $rank => $row ) : ?>
	<?php
	$is_me       = ( (int) $row['user_id'] === (int) $current_uid );
	$rank_number = $rank + 1;
	?>
<article class="tf-leaderboard-podium__card tf-leaderboard-podium__card--rank-<?php echo (int) $rank_number; ?><?php echo $is_me ? ' tf-leaderboard-podium__card--me' : ''; ?>">
<div class="tf-leaderboard-podium__medal">#<?php echo (int) $rank_number; ?></div>
<p class="tf-leaderboard-podium__name">
	<?php echo esc_html( $row['display_name'] ); ?>
	<?php if ( $is_me ) : ?>
	<span class="tf-badge tf-badge--me"><?php esc_html_e( 'You', 'trufield-portal' ); ?></span>
	<?php endif; ?>
</p>
<p class="tf-leaderboard-podium__designation"><?php esc_html_e( 'Sales Rep', 'trufield-portal' ); ?></p>
<p class="tf-leaderboard-podium__points"><?php echo esc_html( number_format_i18n( (int) $row['points'] ) ); ?> <?php esc_html_e( 'points', 'trufield-portal' ); ?></p>
<p class="tf-leaderboard-podium__rank-label"><?php echo esc_html( $rank_labels[ $rank_number ] ); ?></p>
</article>
<?php endforeach; ?>
</section>
<?php endif; ?>

<section class="tf-leaderboard-board" data-tf-leaderboard-search>
<div class="tf-leaderboard-search">
<label class="screen-reader-text" for="tf-leaderboard-search-input"><?php esc_html_e( 'Search leaderboard', 'trufield-portal' ); ?></label>
<input
	type="search"
	id="tf-leaderboard-search-input"
	class="tf-input tf-leaderboard-search__input"
	placeholder="<?php esc_attr_e( 'Search here', 'trufield-portal' ); ?>"
	autocomplete="off"
	data-tf-leaderboard-search-input
>
</div>

<div class="tf-leaderboard" role="region" aria-label="<?php esc_attr_e( 'Leaderboard standings', 'trufield-portal' ); ?>">
<table class="tf-table tf-table--leaderboard">
<thead>
<tr>
<th scope="col" class="tf-table__rank"><?php esc_html_e( 'Rank', 'trufield-portal' ); ?></th>
<th scope="col"><?php esc_html_e( 'Name', 'trufield-portal' ); ?></th>
<th scope="col"><?php esc_html_e( 'Designation', 'trufield-portal' ); ?></th>
<th scope="col" class="tf-table__num"><?php esc_html_e( 'Point Total', 'trufield-portal' ); ?></th>
</tr>
</thead>
<tbody>
<?php foreach ( $leaderboard as $rank => $row ) : ?>
<?php $is_me = ( (int) $row['user_id'] === (int) $current_uid ); ?>
<tr
	class="tf-leaderboard__row<?php echo $is_me ? ' tf-leaderboard__row--me' : ''; ?>"
	data-tf-leaderboard-row
	data-tf-search="<?php echo esc_attr( strtolower( $row['display_name'] . ' sales rep ' . $row['points'] . ' #' . ( $rank + 1 ) ) ); ?>"
>
<td class="tf-table__rank">#<?php echo (int) ( $rank + 1 ); ?></td>
<td>
	<?php echo esc_html( $row['display_name'] ); ?>
	<?php if ( $is_me ) : ?>
	<span class="tf-badge tf-badge--me"><?php esc_html_e( 'You', 'trufield-portal' ); ?></span>
	<?php endif; ?>
</td>
<td><?php esc_html_e( 'Sales Rep', 'trufield-portal' ); ?></td>
<td class="tf-table__num tf-table__num--highlight"><strong><?php echo esc_html( number_format_i18n( (int) $row['points'] ) ); ?></strong></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<div class="tf-empty-state tf-empty-state--leaderboard-search" hidden data-tf-leaderboard-empty>
<p><?php esc_html_e( 'No leaderboard entries matched that search.', 'trufield-portal' ); ?></p>
</div>
</section>
<?php endif; ?>
</div>
<?php get_footer(); ?>
