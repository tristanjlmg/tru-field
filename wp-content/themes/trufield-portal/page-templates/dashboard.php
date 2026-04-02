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

// Feedback messages from phase save redirects.
$success = sanitize_key( $_GET['tf_success'] ?? '' );
$error   = sanitize_text_field( urldecode( $_GET['tf_error'] ?? '' ) );
$created_post_id = (int) ( $_GET['tf_post_id'] ?? 0 );
$field_count = count( $fields );
?>
<div class="tf-container">

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
				printf(
					/* translators: %d = phase number */
					esc_html__( 'Phase %d submitted for admin verification.', 'trufield-portal' ),
					(int) $m[1]
				);
			} elseif ( 'phase_1_autoverified' === $success ) {
				esc_html_e( 'Phase 1 verified automatically once all required details were completed.', 'trufield-portal' );
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

	<div class="tf-dashboard-header">
		<div class="tf-dashboard-header__content">
			<h1>
				<?php
				if ( $is_sales_rep ) {
					esc_html_e( 'My Fields', 'trufield-portal' );
				} else {
					esc_html_e( 'All Fields', 'trufield-portal' );
				}
				?>
			</h1>
			<p class="tf-dashboard-header__support">
				<?php
				if ( $is_sales_rep ) {
					esc_html_e( 'Open an assigned record to complete Phase 1. New records are assigned by the admin team.', 'trufield-portal' );
				} else {
					esc_html_e( 'Phase 1 is the active form right now. Sales reps complete assigned records, and new records are assigned by the admin team.', 'trufield-portal' );
				}
				?>
			</p>
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
		</div>
		<div class="tf-dashboard-header__actions">
			<span class="tf-dashboard-header__count" data-tf-trial-count data-total-count="<?php echo esc_attr( (string) $field_count ); ?>" data-singular-label="<?php esc_attr_e( 'record', 'trufield-portal' ); ?>" data-plural-label="<?php esc_attr_e( 'records', 'trufield-portal' ); ?>">
				<?php
				printf(
					/* translators: %d = record count */
					esc_html( _n( '%d record', '%d records', $field_count, 'trufield-portal' ) ),
					$field_count
				);
				?>
			</span>
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
	</div>

	<?php if ( empty( $fields ) ) : ?>
		<div class="tf-empty-state">
			<p>
				<?php
				if ( $is_sales_rep ) {
					esc_html_e( 'You do not have any assigned records yet. Check back later or contact the admin team if you expected a Phase 1 assignment.', 'trufield-portal' );
				} else {
					esc_html_e( 'No assigned plant field records are available yet. Records will appear here after the admin team sets them up and assigns them.', 'trufield-portal' );
				}
				?>
			</p>
		</div>
	<?php else : ?>
		<div class="tf-field-grid" data-tf-trial-grid>
			<?php foreach ( $fields as $plant_field ) :
				get_template_part( 'template-parts/plant-field-card', null, [ 'post' => $plant_field ] );
			endforeach; ?>
		</div>
		<div class="tf-empty-state tf-empty-state--search" data-tf-trial-empty hidden>
			<p><?php esc_html_e( 'No trials match that search yet. Try a different trial name, retailer, farm, or address.', 'trufield-portal' ); ?></p>
		</div>
	<?php endif; ?>

</div>
<?php get_footer(); ?>
