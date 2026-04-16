<?php
/**
 * Template Name: Portal Dashboard
 *
 * Shows assigned plant_field records for Sales Reps, all records for
 * Leadership and Admins. Assign this template to a page (slug: /dashboard/).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$current_user = wp_get_current_user();
$fields       = trufield_get_visible_fields();
$is_sales_rep = in_array( 'sales_rep', (array) $current_user->roles, true );
$can_create   = current_user_can( 'publish_plant_fields' );
$leaderboard  = trufield_get_leaderboard();
$leaderboard_url = get_permalink( trufield_get_leaderboard_page_id() );

// Feedback messages from phase save redirects.
$success = sanitize_key( $_GET['tf_success'] ?? '' );
$error   = sanitize_text_field( urldecode( $_GET['tf_error'] ?? '' ) );
$created_post_id = (int) ( $_GET['tf_post_id'] ?? 0 );
$field_count = count( $fields );

$current_rank = null;
$current_score = null;
foreach ( $leaderboard as $index => $row ) {
	if ( (int) $row['user_id'] === (int) $current_user->ID ) {
		$current_rank  = $index + 1;
		$current_score = $row;
		break;
	}
}

$verified_phase_1_count = 0;
$verified_phase_2_count = 0;
foreach ( $fields as $field_post ) {
	if ( get_post_meta( $field_post->ID, 'phase_1_verified', true ) ) {
		$verified_phase_1_count++;
	}
	if ( get_post_meta( $field_post->ID, 'phase_2_verified', true ) ) {
		$verified_phase_2_count++;
	}
}

$active_rep_count = count(
	array_filter(
		$leaderboard,
		static function ( array $row ): bool {
			return (int) ( $row['field_count'] ?? 0 ) > 0;
		}
	)
);

$team_points = array_sum(
	array_map(
		static function ( array $row ): int {
			return (int) ( $row['points'] ?? 0 );
		},
		$leaderboard
	)
);
?>
<div class="tf-container tf-dashboard-page">

	<?php if ( $success ) : ?>
		<div class="tf-alert tf-alert--success" role="alert">
			<?php
			if ( 'trial_created' === $success && $created_post_id > 0 ) {
				printf(
					wp_kses(
						/* translators: %s = trial link. */
						__( 'New trial created. <a href="%s">Open the record</a>.', 'trufield-portal' ),
						[ 'a' => [ 'href' => [] ] ]
					),
					esc_url( get_permalink( $created_post_id ) )
				);
			} elseif ( preg_match( '/^phase_(\d)_completed$/', $success, $m ) ) {
				$phase = (int) $m[1];
				printf(
					/* translators: %d = phase number */
					esc_html__( trufield_phase_auto_verifies( $phase ) ? 'Phase %d saved. It will count once the required fields are complete.' : 'Phase %d submitted for admin verification.', 'trufield-portal' ),
					$phase
				);
			} elseif ( 'phase_1_autoverified' === $success ) {
				esc_html_e( 'Phase 1 counted as a valid grower entry once the required Phase 1 fields were completed.', 'trufield-portal' );
			} else {
				esc_html_e( 'Phase 1 progress saved.', 'trufield-portal' );
			}
			?>
		</div>
	<?php endif; ?>

	<?php if ( $error ) : ?>
		<div class="tf-alert tf-alert--error" role="alert">
			<?php echo esc_html( $error ); ?>
		</div>
	<?php endif; ?>

	<section class="tf-dashboard-shell">
		<header class="tf-dashboard-hero">
			<div class="tf-dashboard-hero__heading">
				<h1><?php esc_html_e( 'My Dashboard', 'trufield-portal' ); ?></h1>
				<p class="tf-dashboard-hero__support">
					<?php echo esc_html( $is_sales_rep ? __( 'Track your standing, review your active trials, and jump back into the current phase workflow.', 'trufield-portal' ) : __( 'Monitor team progress, scan active trials, and manage new trial creation from one dashboard.', 'trufield-portal' ) ); ?>
				</p>
			</div>

			<div class="tf-dashboard-topline">
				<section class="tf-dashboard-overview" id="tf-rankings-overview">
					<p class="tf-dashboard-overview__eyebrow"><?php esc_html_e( 'Rankings Overview', 'trufield-portal' ); ?></p>
					<h2 class="tf-dashboard-overview__title"><?php echo esc_html( $is_sales_rep ? __( 'Current Standing', 'trufield-portal' ) : __( 'Admin View', 'trufield-portal' ) ); ?></h2>
					<p class="tf-dashboard-overview__copy">
						<?php
						if ( $is_sales_rep && $current_score ) {
							echo esc_html( sprintf( __( 'You are ranked #%1$d with %2$s points and %3$s verified entries.', 'trufield-portal' ), (int) $current_rank, number_format_i18n( (int) $current_score['points'] ), number_format_i18n( (int) $current_score['valid_entries'] ) ) );
						} elseif ( $is_sales_rep ) {
							esc_html_e( 'Your ranking will appear here as soon as your assigned trials start earning verified points.', 'trufield-portal' );
						} else {
							echo esc_html( sprintf( __( 'The portal is tracking %1$s live trials across %2$s active reps, with %3$s total points awarded.', 'trufield-portal' ), number_format_i18n( $field_count ), number_format_i18n( $active_rep_count ), number_format_i18n( $team_points ) ) );
						}
						?>
					</p>

					<div class="tf-dashboard-overview__stats">
						<?php if ( $is_sales_rep ) : ?>
							<div class="tf-dashboard-stat">
								<span class="tf-dashboard-stat__label"><?php esc_html_e( 'Current Rank', 'trufield-portal' ); ?></span>
								<strong class="tf-dashboard-stat__value"><?php echo esc_html( $current_rank ? '#' . (int) $current_rank : '—' ); ?></strong>
							</div>
							<div class="tf-dashboard-stat">
								<span class="tf-dashboard-stat__label"><?php esc_html_e( 'Point Total', 'trufield-portal' ); ?></span>
								<strong class="tf-dashboard-stat__value"><?php echo esc_html( number_format_i18n( (int) ( $current_score['points'] ?? 0 ) ) ); ?></strong>
							</div>
							<div class="tf-dashboard-stat">
								<span class="tf-dashboard-stat__label"><?php esc_html_e( 'Valid Entries', 'trufield-portal' ); ?></span>
								<strong class="tf-dashboard-stat__value"><?php echo esc_html( number_format_i18n( (int) ( $current_score['valid_entries'] ?? 0 ) ) ); ?></strong>
							</div>
							<div class="tf-dashboard-stat">
								<span class="tf-dashboard-stat__label"><?php esc_html_e( 'Awarded Retailers', 'trufield-portal' ); ?></span>
								<strong class="tf-dashboard-stat__value"><?php echo esc_html( number_format_i18n( (int) ( $current_score['awarded_retailers'] ?? 0 ) ) ); ?></strong>
							</div>
						<?php else : ?>
							<div class="tf-dashboard-stat">
								<span class="tf-dashboard-stat__label"><?php esc_html_e( 'Total Team Points', 'trufield-portal' ); ?></span>
								<strong class="tf-dashboard-stat__value"><?php echo esc_html( number_format_i18n( $team_points ) ); ?></strong>
							</div>
							<div class="tf-dashboard-stat">
								<span class="tf-dashboard-stat__label"><?php esc_html_e( 'Active Reps', 'trufield-portal' ); ?></span>
								<strong class="tf-dashboard-stat__value"><?php echo esc_html( number_format_i18n( $active_rep_count ) ); ?></strong>
							</div>
							<div class="tf-dashboard-stat">
								<span class="tf-dashboard-stat__label"><?php esc_html_e( 'Verified Phase 1', 'trufield-portal' ); ?></span>
								<strong class="tf-dashboard-stat__value"><?php echo esc_html( number_format_i18n( $verified_phase_1_count ) ); ?></strong>
							</div>
							<div class="tf-dashboard-stat">
								<span class="tf-dashboard-stat__label"><?php esc_html_e( 'Verified Phase 2', 'trufield-portal' ); ?></span>
								<strong class="tf-dashboard-stat__value"><?php echo esc_html( number_format_i18n( $verified_phase_2_count ) ); ?></strong>
							</div>
						<?php endif; ?>
					</div>

					<div class="tf-dashboard-overview__actions">
						<?php if ( $leaderboard_url ) : ?>
							<a href="<?php echo esc_url( $leaderboard_url ); ?>" class="tf-btn tf-btn--primary tf-btn--sm"><?php esc_html_e( 'Leaderboard', 'trufield-portal' ); ?></a>
						<?php endif; ?>
						<a href="#tf-current-trials" class="tf-btn tf-btn--ghost tf-btn--sm"><?php esc_html_e( 'Current Trials', 'trufield-portal' ); ?></a>
						<?php if ( ! $is_sales_rep ) : ?>
							<a href="<?php echo esc_url( admin_url() ); ?>" class="tf-btn tf-btn--ghost tf-btn--sm"><?php esc_html_e( 'WordPress Admin', 'trufield-portal' ); ?></a>
						<?php endif; ?>
					</div>
				</section>

				<?php if ( $can_create ) : ?>
					<section id="create-trial-panel" class="tf-create-panel" aria-labelledby="create-trial-heading">
						<div class="tf-create-panel__header">
							<p class="tf-create-panel__eyebrow"><?php esc_html_e( 'Admin action', 'trufield-portal' ); ?></p>
							<h2 id="create-trial-heading" class="tf-create-panel__title"><?php esc_html_e( 'Create trial', 'trufield-portal' ); ?></h2>
							<p class="tf-create-panel__copy"><?php esc_html_e( 'Start a new field record and assign the details afterward.', 'trufield-portal' ); ?></p>
						</div>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="tf-quick-create-form">
							<?php wp_nonce_field( 'trufield_create_plant_field' ); ?>
							<input type="hidden" name="action" value="trufield_create_plant_field">
							<label class="screen-reader-text" for="trial_name"><?php esc_html_e( 'Trial Name', 'trufield-portal' ); ?></label>
							<input type="text" id="trial_name" name="trial_name" class="tf-input tf-quick-create-form__input" placeholder="<?php esc_attr_e( 'Trial name', 'trufield-portal' ); ?>">
							<button type="submit" class="tf-btn tf-btn--primary"><?php esc_html_e( 'Create Trial', 'trufield-portal' ); ?></button>
						</form>
					</section>
				<?php endif; ?>
			</div>
		</header>

		<section class="tf-dashboard-trials" id="tf-current-trials">
			<div class="tf-dashboard-trials__header">
				<div class="tf-dashboard-trials__heading">
					<p class="tf-dashboard-trials__eyebrow"><?php esc_html_e( 'Current Trials', 'trufield-portal' ); ?></p>
					<h2 class="tf-dashboard-trials__title"><?php echo esc_html( $is_sales_rep ? __( 'Current Trials', 'trufield-portal' ) : __( 'Current Trials', 'trufield-portal' ) ); ?></h2>
					<p class="tf-dashboard-trials__copy"><?php echo esc_html( $is_sales_rep ? __( 'Open a trial card to continue the active phase workflow.', 'trufield-portal' ) : __( 'Search and open any active trial to review progress or continue updates.', 'trufield-portal' ) ); ?></p>
				</div>
				<span class="tf-dashboard-header__count" data-tf-trial-count data-total-count="<?php echo esc_attr( (string) $field_count ); ?>" data-singular-label="<?php esc_attr_e( 'record', 'trufield-portal' ); ?>" data-plural-label="<?php esc_attr_e( 'records', 'trufield-portal' ); ?>">
					<?php
					printf(
						/* translators: %d = record count */
						esc_html( _n( '%d record', '%d records', $field_count, 'trufield-portal' ) ),
						$field_count
					);
					?>
				</span>
			</div>

			<?php if ( ! empty( $fields ) ) : ?>
				<div class="tf-trial-search" data-tf-trial-search>
					<label class="tf-trial-search__label" for="tf-trial-search-input"><?php esc_html_e( 'Search trials', 'trufield-portal' ); ?></label>
					<input
						type="search"
						id="tf-trial-search-input"
						class="tf-input tf-trial-search__input"
						placeholder="<?php esc_attr_e( 'Search by trial, retailer, farm, or address', 'trufield-portal' ); ?>"
						data-tf-trial-search-input
						autocomplete="off"
					>
					<p class="tf-trial-search__hint" data-tf-trial-search-hint><?php esc_html_e( 'Start typing to filter the visible field cards instantly.', 'trufield-portal' ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( empty( $fields ) ) : ?>
				<div class="tf-empty-state tf-dashboard-empty-state">
					<p>
						<?php
						if ( $is_sales_rep ) {
							esc_html_e( 'You do not have any assigned records yet. Check back later or contact the admin team if you expected a trial assignment.', 'trufield-portal' );
						} else {
							esc_html_e( 'No assigned plant field records are available yet. Records will appear here after the admin team sets them up and assigns them.', 'trufield-portal' );
						}
						?>
					</p>
				</div>
			<?php else : ?>
				<div class="tf-field-grid tf-field-grid--dashboard" data-tf-trial-grid>
					<?php foreach ( $fields as $plant_field ) :
						get_template_part( 'template-parts/plant-field-card', null, [ 'post' => $plant_field ] );
					endforeach; ?>
				</div>
				<div class="tf-empty-state tf-empty-state--search" data-tf-trial-empty hidden>
					<p><?php esc_html_e( 'No trials match that search yet. Try a different trial name, retailer, farm, or address.', 'trufield-portal' ); ?></p>
				</div>
			<?php endif; ?>
		</section>
	</section>

</div>
<?php get_footer(); ?>
