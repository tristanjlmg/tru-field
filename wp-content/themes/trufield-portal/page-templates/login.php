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

$login_error = sanitize_text_field( wp_unslash( $_GET['login'] ?? '' ) ) === 'failed';
?>
<div class="tf-container tf-login-wrap">
	<div class="tf-login-card">
		<h1 class="tf-login-card__title">
			<?php esc_html_e( 'Sign in to TruField', 'trufield-portal' ); ?>
		</h1>

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
				<label for="tf-log"><?php esc_html_e( 'Username or Email', 'trufield-portal' ); ?></label>
				<input type="text"
				       id="tf-log"
				       name="log"
				       class="tf-input"
				       required
				       autocomplete="username"
				       value="<?php echo esc_attr( wp_unslash( $_POST['log'] ?? '' ) ); ?>">
			</div>

			<div class="tf-field-group">
				<label for="tf-pwd"><?php esc_html_e( 'Password', 'trufield-portal' ); ?></label>
				<input type="password"
				       id="tf-pwd"
				       name="pwd"
				       class="tf-input"
				       required
				       autocomplete="current-password">
			</div>

			<div class="tf-field-group tf-field-group--inline">
				<label>
					<input type="checkbox" name="rememberme" value="forever">
					<?php esc_html_e( 'Remember me', 'trufield-portal' ); ?>
				</label>
			</div>

			<button type="submit" class="tf-btn tf-btn--primary tf-btn--full">
				<?php esc_html_e( 'Sign In', 'trufield-portal' ); ?>
			</button>
		</form>

		<p class="tf-login-card__forgot">
			<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
				<?php esc_html_e( 'Forgot your password?', 'trufield-portal' ); ?>
			</a>
		</p>
	</div>
</div>
<?php get_footer(); ?>
