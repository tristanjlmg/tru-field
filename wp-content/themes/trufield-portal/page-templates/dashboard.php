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
					esc_html__( 'Phase %d marked as completed!', 'trufield-portal' ),
					(int) $m[1]
				);
			} else {
				esc_html_e( 'Changes saved.', 'trufield-portal' );
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
		<h1>
			<?php
			if ( in_array( 'sales_rep', (array) $current_user->roles, true ) ) {
				esc_html_e( 'My Fields', 'trufield-portal' );
			} else {
				esc_html_e( 'All Fields', 'trufield-portal' );
			}
			?>
		</h1>
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

	<?php if ( empty( $fields ) ) : ?>
		<div class="tf-empty-state">
			<p><?php esc_html_e( 'No plant fields have been assigned to you yet.', 'trufield-portal' ); ?></p>
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
