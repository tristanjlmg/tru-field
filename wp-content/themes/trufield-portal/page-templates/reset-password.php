<?php
/**
 * Template Name: Portal Reset Password
 *
 * Frontend reset-password page. Validates the WP reset key/login pair from
 * the email link, then lets the user set a new password via admin-post.php.
 * Assign this template to a page with slug /reset-password/.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rp_key   = sanitize_text_field( wp_unslash( $_GET['key']      ?? '' ) );
$rp_login = sanitize_text_field( wp_unslash( $_GET['login']    ?? '' ) );
$rp_error = sanitize_text_field( wp_unslash( $_GET['rp_error'] ?? '' ) );

// Validate the reset key on every page load so the form only renders for valid keys.
$key_user    = false;
$key_invalid = false;

if ( $rp_key && $rp_login ) {
	$key_check = check_password_reset_key( $rp_key, $rp_login );
	if ( is_wp_error( $key_check ) ) {
		$key_invalid = true;
	} else {
		$key_user = $key_check;
	}
} else {
	$key_invalid = true;
}

get_header();
?>
<div class="tf-container tf-login-wrap">
	<div class="tf-login-card">
		<h1 class="tf-login-card__title">
			<?php esc_html_e( 'Set New Password', 'trufield-portal' ); ?>
		</h1>

		<?php if ( $key_invalid ) : ?>
			<div class="tf-alert tf-alert--error" role="alert">
				<?php esc_html_e( 'This reset link is invalid or has expired. Please request a new one.', 'trufield-portal' ); ?>
			</div>
			<p class="tf-login-card__forgot">
				<a href="<?php echo esc_url( trufield_forgot_password_url() ); ?>">
					<?php esc_html_e( 'Request a new reset link', 'trufield-portal' ); ?>
				</a>
			</p>
		<?php else : ?>

			<?php if ( $rp_error === 'password_mismatch' ) : ?>
				<div class="tf-alert tf-alert--error" role="alert">
					<?php esc_html_e( 'The passwords you entered do not match. Please try again.', 'trufield-portal' ); ?>
				</div>
			<?php elseif ( $rp_error === 'password_too_short' ) : ?>
				<div class="tf-alert tf-alert--error" role="alert">
					<?php esc_html_e( 'Password must be at least 8 characters long.', 'trufield-portal' ); ?>
				</div>
			<?php elseif ( $rp_error === 'reset_failed' ) : ?>
				<div class="tf-alert tf-alert--error" role="alert">
					<?php esc_html_e( 'Password reset failed. Please request a new link.', 'trufield-portal' ); ?>
				</div>
			<?php endif; ?>

			<form method="post"
			      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
			      class="tf-login-form"
			      novalidate>

				<?php wp_nonce_field( 'trufield_reset_password' ); ?>
				<input type="hidden" name="action"   value="trufield_reset_password">
				<input type="hidden" name="rp_key"   value="<?php echo esc_attr( $rp_key ); ?>">
				<input type="hidden" name="rp_login" value="<?php echo esc_attr( $rp_login ); ?>">

				<div class="tf-field-group">
					<label for="tf-pass1">
						<?php esc_html_e( 'New Password', 'trufield-portal' ); ?>
						<small><?php esc_html_e( '(min. 8 characters)', 'trufield-portal' ); ?></small>
					</label>
					<input type="password"
					       id="tf-pass1"
					       name="pass1"
					       class="tf-input"
					       required
					       autocomplete="new-password"
					       minlength="8">
				</div>

				<div class="tf-field-group">
					<label for="tf-pass2">
						<?php esc_html_e( 'Confirm New Password', 'trufield-portal' ); ?>
					</label>
					<input type="password"
					       id="tf-pass2"
					       name="pass2"
					       class="tf-input"
					       required
					       autocomplete="new-password"
					       minlength="8">
				</div>

				<button type="submit" class="tf-btn tf-btn--primary tf-btn--full">
					<?php esc_html_e( 'Set New Password', 'trufield-portal' ); ?>
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
