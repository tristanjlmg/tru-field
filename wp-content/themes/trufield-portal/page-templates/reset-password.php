<?php
/**
 * Template Name: Portal Reset Password
 *
 * Frontend reset password page. Validates core reset keys before rendering the
 * password form.
 */
if ( ! defined( 'ABSPATH' ) ) {
exit;
}
get_header();

$key      = sanitize_text_field( wp_unslash( $_GET['key'] ?? '' ) );
$login    = sanitize_text_field( wp_unslash( $_GET['login'] ?? '' ) );
$rp_error = sanitize_key( wp_unslash( $_GET['rp_error'] ?? '' ) );

$error_messages = [
'invalid_nonce'     => __( 'Your session could not be verified. Please try again.', 'trufield-portal' ),
'invalid_password'  => __( 'Please choose a password that is at least 8 characters long.', 'trufield-portal' ),
'password_mismatch' => __( 'The password confirmation did not match. Please try again.', 'trufield-portal' ),
'invalid_key'       => __( 'This password reset link is invalid or has expired. Request a new reset link to continue.', 'trufield-portal' ),
'reset_failed'      => __( 'We could not reset your password right now. Please try again.', 'trufield-portal' ),
];

$reset_user = null;
if ( '' !== $key && '' !== $login ) {
$checked_user = check_password_reset_key( $key, $login );
if ( $checked_user instanceof WP_User ) {
$reset_user = $checked_user;
}
}

$invalid_link = ! ( $reset_user instanceof WP_User );
?>
<div class="tf-container tf-auth-shell">
<div class="tf-auth-card">
<div class="tf-auth-card__header">
<h1 class="tf-auth-card__title"><?php esc_html_e( 'Choose a new password', 'trufield-portal' ); ?></h1>
</div>

<?php if ( $invalid_link ) : ?>
<div class="tf-alert tf-alert--error" role="alert">
<?php echo esc_html( $error_messages['invalid_key'] ); ?>
</div>
<div class="tf-auth-card__links tf-auth-card__links--stacked">
<a href="<?php echo esc_url( trufield_forgot_password_url() ); ?>">
<?php esc_html_e( 'Back to password reset request', 'trufield-portal' ); ?>
</a>
</div>
<?php else : ?>
<p class="tf-auth-card__intro">
<?php esc_html_e( 'Create a new password for your account below.', 'trufield-portal' ); ?>
</p>

<?php if ( isset( $error_messages[ $rp_error ] ) && 'invalid_key' !== $rp_error ) : ?>
<div class="tf-alert tf-alert--error" role="alert">
<?php echo esc_html( $error_messages[ $rp_error ] ); ?>
</div>
<?php endif; ?>

<form method="post"
      action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
      class="tf-login-form"
      novalidate>
<?php wp_nonce_field( 'trufield_reset_password' ); ?>
<input type="hidden" name="action" value="trufield_reset_password">
<input type="hidden" name="key" value="<?php echo esc_attr( $key ); ?>">
<input type="hidden" name="login" value="<?php echo esc_attr( $login ); ?>">

<div class="tf-field-group">
<label for="tf-pass1"><?php esc_html_e( 'New password', 'trufield-portal' ); ?></label>
<input type="password"
       id="tf-pass1"
       name="pass1"
       class="tf-input"
       required
       minlength="8"
       autocomplete="new-password">
</div>

<div class="tf-field-group">
<label for="tf-pass2"><?php esc_html_e( 'Confirm new password', 'trufield-portal' ); ?></label>
<input type="password"
       id="tf-pass2"
       name="pass2"
       class="tf-input"
       required
       minlength="8"
       autocomplete="new-password">
</div>

<button type="submit" class="tf-btn tf-btn--primary tf-auth-card__submit">
<?php esc_html_e( 'Reset password', 'trufield-portal' ); ?>
</button>
</form>

<div class="tf-auth-card__links tf-auth-card__links--stacked">
<a href="<?php echo esc_url( trufield_login_url() ); ?>">
<?php esc_html_e( 'Back to sign in', 'trufield-portal' ); ?>
</a>
</div>
<?php endif; ?>
</div>
</div>
<?php get_footer(); ?>
