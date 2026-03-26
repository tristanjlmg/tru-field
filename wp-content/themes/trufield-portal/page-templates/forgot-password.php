<?php
/**
 * Template Name: Portal Forgot Password
 *
 * Frontend forgot-password page. Submits via admin-post.php.
 * Assign this template to a page with slug /forgot-password/.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$fp_error = sanitize_text_field( wp_unslash( $_GET['fp_error'] ?? '' ) );
$fp_sent  = sanitize_text_field( wp_unslash( $_GET['fp_sent']  ?? '' ) ) === '1';
?>
<div class="tf-container tf-login-wrap">
	<div class="tf-login-card">
		<h1 class="tf-login-card__title">
			<?php esc_html_e( 'Reset Your Password', 'trufield-portal' ); ?>
		</h1>

		<?php if ( $fp_sent ) : ?>
			<div class="tf-alert tf-alert--success" role="alert">
				<?php esc_html_e( 'Check your email — a reset link is on its way.', 'trufield-portal' ); ?>
			</div>
		<?php else : ?>

			<?php if ( $fp_error === 'invalid_username' ) : ?>
				<div class="tf-alert tf-alert--error" role="alert">
					<?php esc_html_e( 'No account found with that username or email address.', 'trufield-portal' ); ?>
				</div>
			<?php elseif ( $fp_error === 'retrieve_failed' ) : ?>
				<div class="tf-alert tf-alert--error" role="alert">
					<?php esc_html_e( 'Unable to send the reset email. Please try again or contact support.', 'trufield-portal' ); ?>
				</div>
			<?php endif; ?>

			<p class="tf-login-card__forgot">
				<?php esc_html_e( 'Enter your username or email address and we\'ll send you a reset link.', 'trufield-portal' ); ?>
			</p>

			<form method="post"
			      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
			      class="tf-login-form"
			      novalidate>

				<?php wp_nonce_field( 'trufield_forgot_password' ); ?>
				<input type="hidden" name="action" value="trufield_forgot_password">

				<div class="tf-field-group">
					<label for="tf-user-login">
						<?php esc_html_e( 'Username or Email Address', 'trufield-portal' ); ?>
					</label>
					<input type="text"
					       id="tf-user-login"
					       name="user_login"
					       class="tf-input"
					       required
					       autocomplete="username">
				</div>

				<button type="submit" class="tf-btn tf-btn--primary tf-btn--full">
					<?php esc_html_e( 'Send Reset Link', 'trufield-portal' ); ?>
				</button>
			</form>

		<?php endif; ?>

		<p class="tf-login-card__forgot tf-login-card__back-link">
			<a href="<?php echo esc_url( trufield_login_url() ); ?>">
				<?php esc_html_e( '&#8592; Back to sign in', 'trufield-portal' ); ?>
			</a>
		</p>
	</div>
</div>
<?php get_footer(); ?>
