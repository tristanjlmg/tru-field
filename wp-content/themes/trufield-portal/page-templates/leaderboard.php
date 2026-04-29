<?php
/**
 * Template Name: Portal Leaderboard
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

get_header();
$sales_reps        = trufield_get_sales_rep_users();
$selected_rep_id   = absint( (string) ( $_GET['sales_rep'] ?? 0 ) );
$selected_rep      = null;

foreach ( $sales_reps as $sales_rep ) {
	if ( (int) $sales_rep->ID === $selected_rep_id ) {
		$selected_rep = $sales_rep;
		break;
	}
}

if ( ! $selected_rep ) {
	$selected_rep_id = 0;
}

$leaderboard = trufield_get_leaderboard( $selected_rep_id );
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
		esc_html__( 'Each rep earns %1$s points for every %2$s valid Phase 1 entries. Rankings are based on total points across all phases.', 'trufield-portal' ),
		number_format_i18n( trufield_get_retailer_points_award() ),
		number_format_i18n( trufield_get_retailer_points_threshold() )
	);
	?>
</p>
</header>

<section class="tf-leaderboard-filters" aria-label="<?php esc_attr_e( 'Leaderboard filters', 'trufield-portal' ); ?>">
<form method="get" class="tf-leaderboard-filters__form">
	<label class="tf-leaderboard-filters__field" for="tf-sales-rep-filter">
		<span class="tf-leaderboard-filters__label"><?php esc_html_e( 'Sales Rep', 'trufield-portal' ); ?></span>
		<select id="tf-sales-rep-filter" name="sales_rep" class="tf-select tf-leaderboard-filters__select" onchange="this.form.submit()">
			<option value="0"><?php esc_html_e( 'All Sales Reps', 'trufield-portal' ); ?></option>
			<?php foreach ( $sales_reps as $sales_rep ) : ?>
			<option value="<?php echo esc_attr( (string) $sales_rep->ID ); ?>" <?php selected( $selected_rep_id, (int) $sales_rep->ID ); ?>><?php echo esc_html( $sales_rep->display_name ); ?></option>
			<?php endforeach; ?>
		</select>
	</label>
	<noscript>
		<button type="submit" class="tf-btn tf-btn--secondary tf-btn--sm"><?php esc_html_e( 'Apply', 'trufield-portal' ); ?></button>
	</noscript>
	<?php if ( $selected_rep_id > 0 ) : ?>
	<a href="<?php echo esc_url( get_permalink() ); ?>" class="tf-btn tf-btn--ghost tf-btn--sm"><?php esc_html_e( 'Clear Filter', 'trufield-portal' ); ?></a>
	<?php endif; ?>
</form>
<?php if ( $selected_rep ) : ?>
<p class="tf-leaderboard-filters__summary"><?php echo esc_html( sprintf( __( 'Showing leaderboard results for %s.', 'trufield-portal' ), $selected_rep->display_name ) ); ?></p>
<?php endif; ?>
</section>

<?php if ( empty( $leaderboard ) ) : ?>
<div class="tf-empty-state tf-empty-state--leaderboard">
<p><?php echo esc_html( $selected_rep ? sprintf( __( 'No leaderboard activity yet for %s.', 'trufield-portal' ), $selected_rep->display_name ) : __( 'No rep activity yet.', 'trufield-portal' ) ); ?></p>
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
