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
$report_rep_id     = absint( (string) ( $_GET['report_rep'] ?? 0 ) );
$selected_rep      = null;
$report_rep        = null;
$full_leaderboard  = trufield_get_leaderboard();

foreach ( $sales_reps as $sales_rep ) {
	if ( (int) $sales_rep->ID === $selected_rep_id ) {
		$selected_rep = $sales_rep;
	}

	if ( (int) $sales_rep->ID === $report_rep_id ) {
		$report_rep = $sales_rep;
	}
}

if ( ! $selected_rep ) {
	$selected_rep_id = 0;
}

if ( ! $report_rep ) {
	$report_rep_id = 0;
}

$leaderboard = trufield_get_leaderboard( $selected_rep_id );
$current_uid = get_current_user_id();
$top_rankings = array_slice( $full_leaderboard, 0, 3 );
$leaderboard_url = get_permalink();
$report_allowed  = $report_rep_id > 0 && trufield_user_can_view_rep_report( $report_rep_id, $current_uid );
$report_data     = $report_allowed ? trufield_get_rep_report( $report_rep_id ) : [];
$build_leaderboard_url = static function ( array $query_args = [] ) use ( $leaderboard_url, $selected_rep_id ): string {
	$args = [];

	if ( $selected_rep_id > 0 ) {
		$args['sales_rep'] = $selected_rep_id;
	}

	foreach ( $query_args as $key => $value ) {
		if ( 0 === $value || '0' === $value || '' === $value || null === $value ) {
			unset( $args[ $key ] );
			continue;
		}

		$args[ $key ] = $value;
	}

	return empty( $args ) ? $leaderboard_url : add_query_arg( $args, $leaderboard_url );
};
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
		esc_html__( 'Each rep earns %1$s points for every %2$s valid Phase 1 entries and %3$s points for each valid Phase 2 trial. Rankings are based on total points across all phases.', 'trufield-portal' ),
		number_format_i18n( trufield_get_retailer_points_award() ),
		number_format_i18n( trufield_get_retailer_points_threshold() ),
		number_format_i18n( trufield_get_phase_points_award( 2 ) )
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
	<a href="<?php echo esc_url( $build_leaderboard_url( [ 'sales_rep' => 0, 'report_rep' => $report_rep_id ] ) ); ?>" class="tf-btn tf-btn--ghost tf-btn--sm"><?php esc_html_e( 'Clear Filter', 'trufield-portal' ); ?></a>
	<?php endif; ?>
</form>
<?php if ( $selected_rep ) : ?>
<p class="tf-leaderboard-filters__summary"><?php echo esc_html( sprintf( __( 'Showing leaderboard results for %s.', 'trufield-portal' ), $selected_rep->display_name ) ); ?></p>
<?php endif; ?>
</section>

<?php if ( $report_rep_id > 0 && ! $report_allowed ) : ?>
<div class="tf-alert tf-alert--error" role="alert">
<p><?php esc_html_e( 'You can only view your own report unless you have leadership access.', 'trufield-portal' ); ?></p>
</div>
<?php endif; ?>

<?php if ( ! empty( $report_data ) ) : ?>
<?php $report_summary = $report_data['summary']; ?>
<section class="tf-leaderboard-report" aria-labelledby="tf-leaderboard-report-title">
<div class="tf-leaderboard-report__header">
<div>
<p class="tf-leaderboard-report__eyebrow"><?php esc_html_e( 'Sales Rep Report', 'trufield-portal' ); ?></p>
<h2 id="tf-leaderboard-report-title" class="tf-leaderboard-report__title">
	<?php
	printf(
		esc_html__( '%s point breakdown', 'trufield-portal' ),
		esc_html( $report_data['rep']['display_name'] )
	);
	?>
</h2>
<p class="tf-leaderboard-report__copy">
	<?php
	printf(
		esc_html__( '%1$s total points. Phase 1 earns %2$s points for every %3$s valid entries per retailer. Phase 2 earns %4$s points for each valid trial.', 'trufield-portal' ),
		number_format_i18n( (int) $report_summary['points'] ),
		number_format_i18n( (int) $report_summary['phase_1_award_points'] ),
		number_format_i18n( (int) $report_summary['phase_1_threshold'] ),
		number_format_i18n( (int) $report_summary['phase_2_award_points'] )
	);
	?>
</p>
</div>
<a href="<?php echo esc_url( $build_leaderboard_url( [ 'report_rep' => 0 ] ) ); ?>" class="tf-btn tf-btn--ghost tf-btn--sm"><?php esc_html_e( 'Close Report', 'trufield-portal' ); ?></a>
</div>

<div class="tf-leaderboard-report__rules" aria-label="<?php esc_attr_e( 'Scoring rules', 'trufield-portal' ); ?>">
<article class="tf-leaderboard-report__rule">
<p class="tf-leaderboard-report__rule-label"><?php esc_html_e( 'Phase 1 Rule', 'trufield-portal' ); ?></p>
<p class="tf-leaderboard-report__rule-value">
	<?php
	printf(
		esc_html__( '%1$s valid entries = %2$s points', 'trufield-portal' ),
		number_format_i18n( (int) $report_summary['phase_1_threshold'] ),
		number_format_i18n( (int) $report_summary['phase_1_award_points'] )
	);
	?>
</p>
<p class="tf-leaderboard-report__rule-note"><?php esc_html_e( 'Counted separately for each retailer', 'trufield-portal' ); ?></p>
</article>
<article class="tf-leaderboard-report__rule">
<p class="tf-leaderboard-report__rule-label"><?php esc_html_e( 'Phase 2 Rule', 'trufield-portal' ); ?></p>
<p class="tf-leaderboard-report__rule-value">
	<?php
	printf(
		esc_html__( '1 valid trial = %s points', 'trufield-portal' ),
		number_format_i18n( (int) $report_summary['phase_2_award_points'] )
	);
	?>
</p>
<p class="tf-leaderboard-report__rule-note"><?php esc_html_e( 'Each completed valid trial scores on its own', 'trufield-portal' ); ?></p>
</article>
</div>

<div class="tf-leaderboard-report__stats">
<article class="tf-leaderboard-report__stat">
<p class="tf-leaderboard-report__stat-label"><?php esc_html_e( 'Total Points', 'trufield-portal' ); ?></p>
<p class="tf-leaderboard-report__stat-value"><?php echo esc_html( number_format_i18n( (int) $report_summary['points'] ) ); ?></p>
<p class="tf-leaderboard-report__stat-note"><?php esc_html_e( 'Leaderboard score', 'trufield-portal' ); ?></p>
</article>
<article class="tf-leaderboard-report__stat">
<p class="tf-leaderboard-report__stat-label"><?php esc_html_e( 'Phase 1 Points', 'trufield-portal' ); ?></p>
<p class="tf-leaderboard-report__stat-value"><?php echo esc_html( number_format_i18n( (int) $report_summary['phase_1_points'] ) ); ?></p>
<p class="tf-leaderboard-report__stat-note">
	<?php
	printf(
		esc_html__( '%1$s entries across %2$s blocks', 'trufield-portal' ),
		number_format_i18n( (int) $report_summary['valid_phase_1_entries'] ),
		number_format_i18n( (int) $report_summary['phase_1_awards'] )
	);
	?>
</p>
</article>
<article class="tf-leaderboard-report__stat">
<p class="tf-leaderboard-report__stat-label"><?php esc_html_e( 'Phase 2 Points', 'trufield-portal' ); ?></p>
<p class="tf-leaderboard-report__stat-value"><?php echo esc_html( number_format_i18n( (int) $report_summary['phase_2_points'] ) ); ?></p>
<p class="tf-leaderboard-report__stat-note">
	<?php
	printf(
		esc_html__( '%s valid Phase 2 trials', 'trufield-portal' ),
		number_format_i18n( (int) $report_summary['valid_phase_2_trials'] )
	);
	?>
</p>
</article>
<article class="tf-leaderboard-report__stat">
<p class="tf-leaderboard-report__stat-label"><?php esc_html_e( 'Retailers', 'trufield-portal' ); ?></p>
<p class="tf-leaderboard-report__stat-value"><?php echo esc_html( number_format_i18n( (int) $report_summary['retailer_count'] ) ); ?></p>
<p class="tf-leaderboard-report__stat-note">
	<?php
	printf(
		esc_html__( '%s assigned records', 'trufield-portal' ),
		number_format_i18n( (int) $report_summary['field_count'] )
	);
	?>
</p>
</article>
</div>

<?php if ( empty( $report_data['retailers'] ) ) : ?>
<div class="tf-empty-state tf-empty-state--leaderboard">
<p><?php esc_html_e( 'No reportable activity has been recorded for this rep yet.', 'trufield-portal' ); ?></p>
</div>
<?php else : ?>
<div class="tf-leaderboard-report__groups">
<?php foreach ( $report_data['retailers'] as $retailer ) : ?>
<article class="tf-leaderboard-report__group">
<div class="tf-leaderboard-report__group-header">
<div>
<h3 class="tf-leaderboard-report__group-title"><?php echo esc_html( $retailer['name'] ); ?></h3>
<p class="tf-leaderboard-report__group-summary">
	<?php
	printf(
		esc_html__( 'Phase 1: %1$s entries, %2$s points. Phase 2: %3$s trials, %4$s points.', 'trufield-portal' ),
		number_format_i18n( (int) $retailer['phase_1_entries'] ),
		number_format_i18n( (int) $retailer['phase_1_points'] ),
		number_format_i18n( (int) $retailer['phase_2_trials'] ),
		number_format_i18n( (int) $retailer['phase_2_points'] )
	);
	?>
</p>
</div>
<p class="tf-leaderboard-report__group-points"><?php echo esc_html( number_format_i18n( (int) $retailer['total_points'] ) ); ?> <?php esc_html_e( 'pts', 'trufield-portal' ); ?></p>
</div>

<div class="tf-leaderboard-report__phase-grid">
<section class="tf-leaderboard-report__phase">
<h4 class="tf-leaderboard-report__phase-title"><?php esc_html_e( 'Phase 1', 'trufield-portal' ); ?></h4>
<p class="tf-leaderboard-report__phase-copy">
	<?php
	printf(
		esc_html__( '%1$s entries -> %2$s scoring block(s) -> %3$s points.', 'trufield-portal' ),
		number_format_i18n( (int) $retailer['phase_1_entries'] ),
		number_format_i18n( (int) $retailer['phase_1_awards'] ),
		number_format_i18n( (int) $retailer['phase_1_points'] )
	);
	?>
</p>
<?php if ( empty( $retailer['phase_1_records'] ) ) : ?>
<p class="tf-leaderboard-report__empty"><?php esc_html_e( 'No Phase 1 entries yet.', 'trufield-portal' ); ?></p>
<?php else : ?>
<details class="tf-leaderboard-report__detail">
<summary class="tf-leaderboard-report__detail-summary">
	<span class="tf-leaderboard-report__detail-title">
		<?php
		printf(
			esc_html__( 'View %s Phase 1 records', 'trufield-portal' ),
			number_format_i18n( count( $retailer['phase_1_records'] ) )
		);
		?>
	</span>
	<span class="tf-leaderboard-report__detail-meta"><?php esc_html_e( 'Counts as 1 valid entry each', 'trufield-portal' ); ?></span>
</summary>
<ul class="tf-leaderboard-report__record-list">
	<?php foreach ( $retailer['phase_1_records'] as $record ) : ?>
	<li class="tf-leaderboard-report__record">
		<?php if ( ! empty( $record['permalink'] ) ) : ?>
		<a href="<?php echo esc_url( $record['permalink'] ); ?>" class="tf-leaderboard-report__record-link"><?php echo esc_html( $record['title'] ); ?></a>
		<?php else : ?>
		<span class="tf-leaderboard-report__record-link"><?php echo esc_html( $record['title'] ); ?></span>
		<?php endif; ?>
		<span class="tf-leaderboard-report__record-meta"><?php esc_html_e( 'Counts as 1 valid entry', 'trufield-portal' ); ?></span>
	</li>
	<?php endforeach; ?>
</ul>
</details>
<?php endif; ?>
</section>

<section class="tf-leaderboard-report__phase">
<h4 class="tf-leaderboard-report__phase-title"><?php esc_html_e( 'Phase 2', 'trufield-portal' ); ?></h4>
<p class="tf-leaderboard-report__phase-copy">
	<?php
	printf(
		esc_html__( '%1$s trials -> %2$s points.', 'trufield-portal' ),
		number_format_i18n( (int) $retailer['phase_2_trials'] ),
		number_format_i18n( (int) $retailer['phase_2_points'] )
	);
	?>
</p>
<?php if ( empty( $retailer['phase_2_records'] ) ) : ?>
<p class="tf-leaderboard-report__empty"><?php esc_html_e( 'No Phase 2 trial points yet.', 'trufield-portal' ); ?></p>
<?php else : ?>
<details class="tf-leaderboard-report__detail">
<summary class="tf-leaderboard-report__detail-summary">
	<span class="tf-leaderboard-report__detail-title">
		<?php
		printf(
			esc_html__( 'View %s Phase 2 records', 'trufield-portal' ),
			number_format_i18n( count( $retailer['phase_2_records'] ) )
		);
		?>
	</span>
	<span class="tf-leaderboard-report__detail-meta">
		<?php
		printf(
			esc_html__( '%s points each', 'trufield-portal' ),
			number_format_i18n( (int) $report_summary['phase_2_award_points'] )
		);
		?>
	</span>
</summary>
<ul class="tf-leaderboard-report__record-list">
	<?php foreach ( $retailer['phase_2_records'] as $record ) : ?>
	<li class="tf-leaderboard-report__record">
		<?php if ( ! empty( $record['permalink'] ) ) : ?>
		<a href="<?php echo esc_url( $record['permalink'] ); ?>" class="tf-leaderboard-report__record-link"><?php echo esc_html( $record['title'] ); ?></a>
		<?php else : ?>
		<span class="tf-leaderboard-report__record-link"><?php echo esc_html( $record['title'] ); ?></span>
		<?php endif; ?>
		<span class="tf-leaderboard-report__record-meta"><?php echo esc_html( sprintf( __( '%s points', 'trufield-portal' ), number_format_i18n( (int) $record['points'] ) ) ); ?></span>
	</li>
	<?php endforeach; ?>
</ul>
</details>
<?php endif; ?>
</section>
</div>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>
</section>
<?php endif; ?>

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
	$report_url  = trufield_user_can_view_rep_report( (int) $row['user_id'], $current_uid ) ? $build_leaderboard_url( [ 'report_rep' => (int) $row['user_id'] ] ) : '';
	?>
<article class="tf-leaderboard-podium__card tf-leaderboard-podium__card--rank-<?php echo (int) $rank_number; ?><?php echo $is_me ? ' tf-leaderboard-podium__card--me' : ''; ?>">
<div class="tf-leaderboard-podium__medal">#<?php echo (int) $rank_number; ?></div>
<p class="tf-leaderboard-podium__name">
	<?php if ( $report_url ) : ?>
	<a href="<?php echo esc_url( $report_url ); ?>" class="tf-leaderboard__person-link"><?php echo esc_html( $row['display_name'] ); ?></a>
	<?php else : ?>
	<?php echo esc_html( $row['display_name'] ); ?>
	<?php endif; ?>
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
<?php
$is_me      = ( (int) $row['user_id'] === (int) $current_uid );
$report_url = trufield_user_can_view_rep_report( (int) $row['user_id'], $current_uid ) ? $build_leaderboard_url( [ 'report_rep' => (int) $row['user_id'] ] ) : '';
?>
<tr
	class="tf-leaderboard__row<?php echo $is_me ? ' tf-leaderboard__row--me' : ''; ?>"
	data-tf-leaderboard-row
	data-tf-search="<?php echo esc_attr( strtolower( $row['display_name'] . ' sales rep ' . $row['points'] . ' #' . ( $rank + 1 ) ) ); ?>"
>
<td class="tf-table__rank">#<?php echo (int) ( $rank + 1 ); ?></td>
<td>
	<?php if ( $report_url ) : ?>
	<a href="<?php echo esc_url( $report_url ); ?>" class="tf-leaderboard__person-link"><?php echo esc_html( $row['display_name'] ); ?></a>
	<?php else : ?>
	<?php echo esc_html( $row['display_name'] ); ?>
	<?php endif; ?>
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
