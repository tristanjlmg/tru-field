<?php
/**
 * Template Name: Portal Forgot Password
 *
 * Frontend forgot password page. Uses WordPress core password reset emails via
 * a custom admin-post handler, with a local-only development shortcut.
 */
if ( ! defined( 'ABSPATH' ) ) {
exit;
}
get_header();

$fp_sent  = sanitize_text_field( wp_unslash( $_GET['fp_sent'] ?? '' ) ) === '1';
$fp_error = sanitize_key( wp_unslash( $_GET['fp_error'] ?? '' ) );
$fp_dev_token = sanitize_text_field( wp_unslash( $_GET['fp_dev_token'] ?? '' ) );
$fp_dev_reset_url = '';

if ( trufield_is_local_env() && '' !== $fp_dev_token ) {
	$stored_reset_url = get_transient( 'trufield_forgot_password_dev_' . $fp_dev_token );

	if ( is_string( $stored_reset_url ) ) {
		$fp_dev_reset_url = $stored_reset_url;
	}
}

$error_messages = [
'invalid_submission' => __( 'Please enter your username or email address to continue.', 'trufield-portal' ),
'invalid_nonce'      => __( 'Your session could not be verified. Please try again.', 'trufield-portal' ),
'retrieve_failed'    => __( 'We could not start the password reset request. Please try again.', 'trufield-portal' ),
'email_failed'       => __( 'We could not send the reset email right now. Please try again shortly.', 'trufield-portal' ),
];
?>
<div class="tf-container tf-login-wrap">
<div class="tf-login-card">
<h1 class="tf-login-card__title"><?php esc_html_e( 'Reset your password', 'trufield-portal' ); ?></h1>
<p class="tf-login-card__intro">
<?php esc_html_e( 'Enter your username or email address and we will email password reset instructions if an account matches.', 'trufield-portal' ); ?>
</p>

<?php if ( '' !== $fp_dev_reset_url ) : ?>
<div class="tf-alert tf-alert--info tf-dev-reset-alert" role="alert">
<strong><?php esc_html_e( 'Local development reset link ready.', 'trufield-portal' ); ?></strong>
<p><?php esc_html_e( 'Email is bypassed in local development. Use the direct reset link below to continue.', 'trufield-portal' ); ?></p>
<a class="tf-btn tf-btn--secondary tf-dev-reset-alert__link" href="<?php echo esc_url( $fp_dev_reset_url ); ?>">
<?php esc_html_e( 'Open password reset', 'trufield-portal' ); ?>
</a>
</div>
<?php elseif ( $fp_sent ) : ?>
<div class="tf-alert tf-alert--success" role="alert">
<?php esc_html_e( 'If an account matches that username or email, a password reset link has been sent.', 'trufield-portal' ); ?>
</div>
<?php endif; ?>

<?php if ( '' === $fp_dev_reset_url && isset( $error_messages[ $fp_error ] ) ) : ?>
<div class="tf-alert tf-alert--error" role="alert">
<?php echo esc_html( $error_messages[ $fp_error ] ); ?>
</div>
<?php endif; ?>

<form method="post"
      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
      class="tf-login-form"
      novalidate>
<?php wp_nonce_field( 'trufield_forgot_password' ); ?>
<input type="hidden" name="action" value="trufield_forgot_password">

<div class="tf-field-group">
<label for="tf-user-login"><?php esc_html_e( 'Username or Email', 'trufield-portal' ); ?></label>
<input type="text"
       id="tf-user-login"
       name="user_login"
       class="tf-input"
       required
       autocomplete="username"
       value="">
</div>

<button type="submit" class="tf-btn tf-btn--primary tf-btn--full">
<?php esc_html_e( 'Email reset link', 'trufield-portal' ); ?>
</button>
</form>

<p class="tf-login-card__back">
<a class="tf-back-link" href="<?php echo esc_url( trufield_login_url() ); ?>">
<?php esc_html_e( 'Back to sign in', 'trufield-portal' ); ?>
</a>
</p>
</div>
</div>
<?php get_footer(); ?>
