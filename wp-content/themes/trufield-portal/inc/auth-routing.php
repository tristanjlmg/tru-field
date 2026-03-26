<?php
/**
 * TruField Portal — Auth & Routing
 *
 * Gates frontend portal pages to authenticated users and routes frontend auth
 * requests through branded theme templates while relying on WordPress core reset
 * password mechanics.
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Get the URL of a page assigned to a given template.
 */
function trufield_get_page_url_by_template( string $template, string $fallback ): string {
static $urls = [];

if ( isset( $urls[ $template ] ) ) {
return $urls[ $template ];
}

$page = get_pages(
[
'meta_key'   => '_wp_page_template',
'meta_value' => $template,
'number'     => 1,
]
);

$urls[ $template ] = ! empty( $page ) ? get_permalink( $page[0]->ID ) : $fallback;

return $urls[ $template ];
}

/**
 * Whether a URL points to wp-login.php.
 */
function trufield_url_uses_wp_login( string $url ): bool {
$url_path      = wp_parse_url( $url, PHP_URL_PATH );
$wp_login_path = wp_parse_url( wp_login_url(), PHP_URL_PATH );

if ( ! is_string( $url_path ) || ! is_string( $wp_login_path ) ) {
return false;
}

return untrailingslashit( $url_path ) === untrailingslashit( $wp_login_path );
}

/**
 * Get the URL of the frontend login page.
 */
function trufield_login_url(): string {
return trufield_get_page_url_by_template( 'page-templates/login.php', wp_login_url() );
}

/**
 * Get the URL of the frontend forgot password page.
 */
function trufield_forgot_password_url(): string {
	return trufield_get_page_url_by_template(
		'page-templates/forgot-password.php',
		network_site_url( 'wp-login.php?action=lostpassword', 'login' )
	);
}

/**
 * Get the URL of the frontend reset password page.
 */
function trufield_reset_password_url(): string {
return trufield_get_page_url_by_template( 'page-templates/reset-password.php', wp_login_url() );
}

/**
 * Whether the current environment is local development.
 */
function trufield_is_local_env(): bool {
	return in_array( wp_get_environment_type(), [ 'local', 'development' ], true );
}

/**
 * Get the URL of the portal dashboard page.
 */
function trufield_dashboard_url(): string {
return trufield_get_page_url_by_template( 'page-templates/dashboard.php', home_url( '/dashboard/' ) );
}

/**
 * Whether the current page uses one of the portal page templates.
 */
function trufield_is_portal_template(): bool {
if ( ! is_page() ) {
return false;
}

$template = get_page_template_slug( get_the_ID() );

return in_array(
$template,
[
'page-templates/dashboard.php',
'page-templates/leaderboard.php',
'page-templates/login.php',
'page-templates/forgot-password.php',
'page-templates/reset-password.php',
],
true
);
}

/**
 * Whether the current page is the portal login page.
 */
function trufield_current_page_is_login(): bool {
if ( ! is_page() ) {
return false;
}

return get_page_template_slug( get_the_ID() ) === 'page-templates/login.php';
}

/**
 * Whether the current page is the portal forgot password page.
 */
function trufield_current_page_is_forgot_password(): bool {
if ( ! is_page() ) {
return false;
}

return get_page_template_slug( get_the_ID() ) === 'page-templates/forgot-password.php';
}

/**
 * Whether the current page is the portal reset password page.
 */
function trufield_current_page_is_reset_password(): bool {
if ( ! is_page() ) {
return false;
}

return get_page_template_slug( get_the_ID() ) === 'page-templates/reset-password.php';
}

// ── Protect portal content ─────────────────────────────────────────────────
add_action( 'template_redirect', 'trufield_gate_portal_access' );
function trufield_gate_portal_access(): void {
if ( wp_doing_cron() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
return;
}

$is_portal_page = is_singular( 'plant_field' ) || trufield_is_portal_template();
if ( ! $is_portal_page ) {
return;
}

if (
trufield_current_page_is_login()
|| trufield_current_page_is_forgot_password()
|| trufield_current_page_is_reset_password()
) {
return;
}

if ( ! is_user_logged_in() ) {
wp_safe_redirect( trufield_login_url() );
exit;
}

if ( is_singular( 'plant_field' ) && ! current_user_can( 'read_plant_field', get_the_ID() ) ) {
wp_die(
esc_html__( 'You do not have permission to view this record.', 'trufield-portal' ),
esc_html__( 'Access Denied', 'trufield-portal' ),
[ 'response' => 403 ]
);
}
}

// ── Redirect already-logged-in users away from auth-only pages ─────────────
add_action( 'template_redirect', 'trufield_redirect_logged_in_from_auth_pages' );
function trufield_redirect_logged_in_from_auth_pages(): void {
if ( ! is_user_logged_in() ) {
return;
}

if (
trufield_current_page_is_login()
|| trufield_current_page_is_forgot_password()
|| trufield_current_page_is_reset_password()
) {
wp_safe_redirect( trufield_dashboard_url() );
exit;
}
}

// ── Route core lost/reset entry points to branded frontend pages ───────────
add_filter( 'lostpassword_url', 'trufield_filter_lostpassword_url', 10, 2 );
function trufield_filter_lostpassword_url( string $lostpassword_url, string $redirect ): string {
$forgot_url = trufield_forgot_password_url();

if ( trufield_url_uses_wp_login( $forgot_url ) ) {
return $lostpassword_url;
}

if ( '' !== $redirect ) {
$forgot_url = add_query_arg( 'redirect_to', $redirect, $forgot_url );
}

return $forgot_url;
}

add_action( 'login_init', 'trufield_route_wp_login_password_actions' );
function trufield_route_wp_login_password_actions(): void {
$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login';

if ( 'lostpassword' === $action ) {
$forgot_url = trufield_forgot_password_url();
if ( trufield_url_uses_wp_login( $forgot_url ) ) {
return;
}

$redirect_to = sanitize_text_field( wp_unslash( $_REQUEST['redirect_to'] ?? '' ) );
if ( '' !== $redirect_to ) {
$forgot_url = add_query_arg( 'redirect_to', $redirect_to, $forgot_url );
}

wp_safe_redirect( $forgot_url );
exit;
}

if ( ! in_array( $action, [ 'rp', 'resetpass' ], true ) ) {
return;
}

$reset_url = trufield_reset_password_url();
if ( trufield_url_uses_wp_login( $reset_url ) ) {
return;
}

$key   = sanitize_text_field( wp_unslash( $_REQUEST['key'] ?? '' ) );
$login = sanitize_text_field( wp_unslash( $_REQUEST['login'] ?? $_REQUEST['rp_login'] ?? '' ) );
$args  = [];

if ( '' !== $key ) {
$args['key'] = $key;
}

if ( '' !== $login ) {
$args['login'] = $login;
}

wp_safe_redirect( add_query_arg( $args, $reset_url ) );
exit;
}

/**
 * Build a redirect URL back to the reset page and preserve a valid key/login.
 */
function trufield_get_reset_password_redirect_url( string $error, string $key = '', string $login = '' ): string {
$base_url = trufield_reset_password_url();
$args     = [ 'rp_error' => $error ];

if ( '' !== $key && '' !== $login ) {
$user = check_password_reset_key( $key, $login );
if ( $user instanceof WP_User ) {
$args['key']   = $key;
$args['login'] = $login;
}
}

if ( trufield_url_uses_wp_login( $base_url ) && isset( $args['key'], $args['login'] ) ) {
$args['action'] = 'rp';
}

return add_query_arg( $args, $base_url );
}

// ── Handle WP login form redirect ──────────────────────────────────────────
add_filter( 'login_redirect', 'trufield_login_redirect', 10, 3 );
function trufield_login_redirect( string $redirect_to, string $requested_redirect_to, $user ): string {
if ( is_wp_error( $user ) || ! ( $user instanceof WP_User ) ) {
return $redirect_to;
}

$frontend_roles = [ 'sales_rep', 'leadership' ];
if ( array_intersect( $frontend_roles, (array) $user->roles ) ) {
return trufield_dashboard_url();
}

return $redirect_to;
}

// ── Handle frontend login form submission ──────────────────────────────────
add_action( 'admin_post_nopriv_trufield_frontend_login', 'trufield_handle_frontend_login' );
function trufield_handle_frontend_login(): void {
$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) );
if ( ! wp_verify_nonce( $nonce, 'trufield_frontend_login' ) ) {
wp_die( esc_html__( 'Security check failed.', 'trufield-portal' ), 403 );
}

$username = sanitize_user( wp_unslash( $_POST['log'] ?? '' ) );
$password = wp_unslash( $_POST['pwd'] ?? '' );
$remember = ! empty( $_POST['rememberme'] );

$user = wp_signon(
[
'user_login'    => $username,
'user_password' => $password,
'remember'      => $remember,
],
false
);

if ( is_wp_error( $user ) ) {
wp_safe_redirect( add_query_arg( 'login', 'failed', trufield_login_url() ) );
exit;
}

wp_safe_redirect( trufield_dashboard_url() );
exit;
}

// ── Handle forgot password form submission ─────────────────────────────────
add_action( 'admin_post_nopriv_trufield_forgot_password', 'trufield_handle_forgot_password' );

/**
 * Handle local development forgot-password flow without sending email.
 */
function trufield_handle_forgot_password_dev( WP_User $user ): void {
	$forgot_url = trufield_forgot_password_url();
	$reset_key  = get_password_reset_key( $user );

	if ( is_wp_error( $reset_key ) ) {
		wp_safe_redirect( add_query_arg( 'fp_error', 'retrieve_failed', $forgot_url ) );
		exit;
	}

	$reset_url = add_query_arg(
		[
			'key'   => $reset_key,
			'login' => $user->user_login,
		],
		trufield_reset_password_url()
	);
	$token     = wp_generate_password( 32, false, false );

	set_transient( 'trufield_forgot_password_dev_' . $token, $reset_url, 5 * MINUTE_IN_SECONDS );

	wp_safe_redirect( add_query_arg( 'fp_dev_token', $token, $forgot_url ) );
	exit;
}

function trufield_handle_forgot_password(): void {
	$forgot_url = trufield_forgot_password_url();
	$nonce      = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) );

if ( ! wp_verify_nonce( $nonce, 'trufield_forgot_password' ) ) {
wp_safe_redirect( add_query_arg( 'fp_error', 'invalid_nonce', $forgot_url ) );
exit;
}

$identifier = sanitize_text_field( wp_unslash( $_POST['user_login'] ?? '' ) );
if ( '' === $identifier ) {
wp_safe_redirect( add_query_arg( 'fp_error', 'invalid_submission', $forgot_url ) );
exit;
}

$user = get_user_by( 'email', $identifier );
if ( ! ( $user instanceof WP_User ) ) {
$user = get_user_by( 'login', $identifier );
}

	if ( $user instanceof WP_User ) {
		if ( trufield_is_local_env() ) {
			trufield_handle_forgot_password_dev( $user );
		}

		$result = retrieve_password( $user->user_login );
		if ( is_wp_error( $result ) ) {
			$error = in_array( 'retrieve_password_email_failure', $result->get_error_codes(), true )
? 'email_failed'
: 'retrieve_failed';

wp_safe_redirect( add_query_arg( 'fp_error', $error, $forgot_url ) );
exit;
}
}

wp_safe_redirect( add_query_arg( 'fp_sent', '1', $forgot_url ) );
exit;
}

// ── Handle reset password form submission ──────────────────────────────────
add_action( 'admin_post_nopriv_trufield_reset_password', 'trufield_handle_reset_password' );
function trufield_handle_reset_password(): void {
$key   = sanitize_text_field( wp_unslash( $_POST['key'] ?? '' ) );
$login = sanitize_text_field( wp_unslash( $_POST['login'] ?? '' ) );
$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) );

if ( ! wp_verify_nonce( $nonce, 'trufield_reset_password' ) ) {
wp_safe_redirect( trufield_get_reset_password_redirect_url( 'invalid_nonce', $key, $login ) );
exit;
}

if ( '' === $key || '' === $login ) {
wp_safe_redirect( trufield_get_reset_password_redirect_url( 'invalid_key' ) );
exit;
}

$user = check_password_reset_key( $key, $login );
if ( is_wp_error( $user ) || ! ( $user instanceof WP_User ) ) {
wp_safe_redirect( trufield_get_reset_password_redirect_url( 'invalid_key' ) );
exit;
}

$pass1 = wp_unslash( $_POST['pass1'] ?? '' );
$pass2 = wp_unslash( $_POST['pass2'] ?? '' );

if ( strlen( $pass1 ) < 8 ) {
wp_safe_redirect( trufield_get_reset_password_redirect_url( 'invalid_password', $key, $login ) );
exit;
}

if ( $pass1 !== $pass2 ) {
wp_safe_redirect( trufield_get_reset_password_redirect_url( 'password_mismatch', $key, $login ) );
exit;
}

$confirmed_user = get_userdata( $user->ID );
if ( ! ( $confirmed_user instanceof WP_User ) ) {
wp_safe_redirect( trufield_get_reset_password_redirect_url( 'reset_failed', $key, $login ) );
exit;
}

reset_password( $user, $pass1 );
wp_safe_redirect( add_query_arg( 'reset', 'success', trufield_login_url() ) );
exit;
}

// ── Frontend logout ────────────────────────────────────────────────────────
add_action( 'wp_logout', 'trufield_on_logout' );
function trufield_on_logout(): void {
wp_safe_redirect( trufield_login_url() );
exit;
}
