<?php
/**
 * Template Name: Portal Login
 *
 * Frontend login page. Handles its own form POST via admin-post.php.
 * Assign this template to a page with slug /login/ (or similar).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

$login_error   = sanitize_text_field( wp_unslash( $_GET['login'] ?? '' ) ) === 'failed';
$reset_success = sanitize_text_field( wp_unslash( $_GET['reset'] ?? '' ) ) === 'success';
?>
<div class="tf-container tf-auth-shell">
	<div class="tf-auth-card tf-auth-card--login">
		<div class="tf-auth-card__header">
			<h1 class="tf-auth-card__title">
				<?php esc_html_e( 'Welcome Back', 'trufield-portal' ); ?>
			</h1>
			<p class="tf-auth-card__intro">
				<?php esc_html_e( 'Sign in with your username or email address to access the TruField Portal.', 'trufield-portal' ); ?>
			</p>
		</div>

		<?php if ( $reset_success ) : ?>
			<div class="tf-alert tf-alert--success" role="alert">
				<?php esc_html_e( 'Your password has been reset. You can sign in with your new password now.', 'trufield-portal' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $login_error ) : ?>
			<div class="tf-alert tf-alert--error" role="alert">
				<?php esc_html_e( 'Incorrect username or password. Please try again.', 'trufield-portal' ); ?>
			</div>
		<?php endif; ?>

		<form method="post"
		      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
		      class="tf-login-form"
		      novalidate>

			<?php wp_nonce_field( 'trufield_frontend_login' ); ?>
			<input type="hidden" name="action" value="trufield_frontend_login">

			<div class="tf-field-group">
				<label for="tf-log" class="screen-reader-text"><?php esc_html_e( 'Username or Email', 'trufield-portal' ); ?></label>
				<input type="text"
				       id="tf-log"
				       name="log"
				       class="tf-input"
				       required
				       autocomplete="username"
				       placeholder="<?php esc_attr_e( 'Email Address', 'trufield-portal' ); ?>"
				       value="<?php echo esc_attr( wp_unslash( $_POST['log'] ?? '' ) ); ?>">
			</div>

			<div class="tf-field-group">
				<label for="tf-pwd" class="screen-reader-text"><?php esc_html_e( 'Password', 'trufield-portal' ); ?></label>
				<input type="password"
				       id="tf-pwd"
				       name="pwd"
				       class="tf-input"
				       required
				       placeholder="<?php esc_attr_e( 'Password', 'trufield-portal' ); ?>"
				       autocomplete="current-password">
			</div>

			<button type="submit" class="tf-btn tf-btn--primary tf-auth-card__submit">
				<?php esc_html_e( 'Sign In', 'trufield-portal' ); ?>
			</button>
		</form>

		<div class="tf-auth-card__links" aria-label="<?php esc_attr_e( 'Sign in help links', 'trufield-portal' ); ?>">
			<a href="<?php echo esc_url( trufield_forgot_username_url() ); ?>">
				<?php esc_html_e( 'Forgot username?', 'trufield-portal' ); ?>
			</a>
			<a href="<?php echo esc_url( trufield_forgot_password_url() ); ?>">
				<?php esc_html_e( 'Forgot password?', 'trufield-portal' ); ?>
			</a>
		</div>
	</div>
</div>
<?php get_footer(); ?>
