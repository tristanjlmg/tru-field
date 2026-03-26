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

// Feedback messages from phase save redirects.
$success = sanitize_key( $_GET['tf_success'] ?? '' );
$error   = sanitize_text_field( urldecode( $_GET['tf_error'] ?? '' ) );
?>
<div class="tf-container">

	<?php if ( $success ) : ?>
		<div class="tf-alert tf-alert--success" role="alert">
			<?php
			if ( preg_match( '/^phase_(\d)_completed$/', $success, $m ) ) {
				printf(
					/* translators: %d = phase number */
					esc_html__( 'Phase %d submitted for admin verification.', 'trufield-portal' ),
					(int) $m[1]
				);
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
		</div>
		<div class="tf-dashboard-header__actions">
			<span class="tf-dashboard-header__count">
				<?php
				printf(
					/* translators: %d = record count */
					esc_html( _n( '%d record', '%d records', count( $fields ), 'trufield-portal' ) ),
					count( $fields )
				);
				?>
			</span>
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
		<div class="tf-field-grid">
			<?php foreach ( $fields as $plant_field ) :
				get_template_part( 'template-parts/plant-field-card', null, [ 'post' => $plant_field ] );
			endforeach; ?>
		</div>
	<?php endif; ?>

</div>
<?php get_footer(); ?>
