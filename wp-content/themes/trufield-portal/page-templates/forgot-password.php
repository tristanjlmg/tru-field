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

$auth_mode = sanitize_key( wp_unslash( $_GET['mode'] ?? 'password' ) );
if ( ! in_array( $auth_mode, [ 'password', 'username' ], true ) ) {
      $auth_mode = 'password';
}

$fp_sent  = sanitize_text_field( wp_unslash( $_GET['fp_sent'] ?? '' ) ) === '1';
$fp_error = sanitize_key( wp_unslash( $_GET['fp_error'] ?? '' ) );
$fu_found = sanitize_text_field( wp_unslash( $_GET['fu_found'] ?? '' ) ) === '1';
$fu_error = sanitize_key( wp_unslash( $_GET['fu_error'] ?? '' ) );
$fu_username = sanitize_user( wp_unslash( $_GET['fu_username'] ?? '' ) );
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

$username_error_messages = [
      'invalid_submission' => __( 'Please enter the email address associated with your account.', 'trufield-portal' ),
      'invalid_nonce'      => __( 'Your session could not be verified. Please try again.', 'trufield-portal' ),
      'not_found'          => __( 'We could not find an account for that email address.', 'trufield-portal' ),
];
?>
<div class="tf-container tf-auth-shell">
<div class="tf-auth-card">
<div class="tf-auth-card__header">
<h1 class="tf-auth-card__title">
      <?php echo esc_html( 'username' === $auth_mode ? __( 'Forgot Username', 'trufield-portal' ) : __( 'Reset your password', 'trufield-portal' ) ); ?>
</h1>
<p class="tf-auth-card__intro">
      <?php echo esc_html( 'username' === $auth_mode ? __( 'Enter the email address tied to your account and we will show your username.', 'trufield-portal' ) : __( 'Enter your username or email address and we will email password reset instructions if an account matches.', 'trufield-portal' ) ); ?>
</p>
</div>

<?php if ( 'username' === $auth_mode ) : ?>
      <?php if ( $fu_found && '' !== $fu_username ) : ?>
      <div class="tf-alert tf-alert--success" role="alert">
            <?php printf( esc_html__( 'Your username is %s.', 'trufield-portal' ), esc_html( $fu_username ) ); ?>
      </div>
      <?php endif; ?>

      <?php if ( isset( $username_error_messages[ $fu_error ] ) ) : ?>
      <div class="tf-alert tf-alert--error" role="alert">
            <?php echo esc_html( $username_error_messages[ $fu_error ] ); ?>
      </div>
      <?php endif; ?>

      <form method="post"
            action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
            class="tf-login-form"
            novalidate>
      <?php wp_nonce_field( 'trufield_forgot_username' ); ?>
      <input type="hidden" name="action" value="trufield_forgot_username">

      <div class="tf-field-group">
      <label for="tf-user-email" class="screen-reader-text"><?php esc_html_e( 'Email address', 'trufield-portal' ); ?></label>
      <input type="email"
             id="tf-user-email"
             name="user_email"
             class="tf-input"
             required
             autocomplete="email"
             placeholder="<?php esc_attr_e( 'Email Address', 'trufield-portal' ); ?>"
             value="">
      </div>

      <button type="submit" class="tf-btn tf-btn--primary tf-auth-card__submit">
      <?php esc_html_e( 'Show username', 'trufield-portal' ); ?>
      </button>
      </form>
      <div class="tf-auth-card__links tf-auth-card__links--stacked">
            <a href="<?php echo esc_url( trufield_forgot_password_url() ); ?>">
                  <?php esc_html_e( 'Need to reset your password instead?', 'trufield-portal' ); ?>
            </a>
            <a href="<?php echo esc_url( trufield_login_url() ); ?>">
                  <?php esc_html_e( 'Back to sign in', 'trufield-portal' ); ?>
            </a>
      </div>
<?php else : ?>
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
      <label for="tf-user-login" class="screen-reader-text"><?php esc_html_e( 'Username or Email', 'trufield-portal' ); ?></label>
      <input type="text"
             id="tf-user-login"
             name="user_login"
             class="tf-input"
             required
             autocomplete="username"
             placeholder="<?php esc_attr_e( 'Username or Email', 'trufield-portal' ); ?>"
             value="">
      </div>

      <button type="submit" class="tf-btn tf-btn--primary tf-auth-card__submit">
      <?php esc_html_e( 'Email reset link', 'trufield-portal' ); ?>
      </button>
      </form>
      <div class="tf-auth-card__links tf-auth-card__links--stacked">
            <a href="<?php echo esc_url( trufield_forgot_username_url() ); ?>">
                  <?php esc_html_e( 'Forgot username?', 'trufield-portal' ); ?>
            </a>
            <a href="<?php echo esc_url( trufield_login_url() ); ?>">
                  <?php esc_html_e( 'Back to sign in', 'trufield-portal' ); ?>
            </a>
      </div>
<?php endif; ?>
</div>
</div>
<?php get_footer(); ?>
