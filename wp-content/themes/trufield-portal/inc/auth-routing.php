<?php
/**
 * TruField Portal — Auth & Routing
 *
 * Gates frontend portal pages to authenticated users and redirects
 * unauthenticated visitors to the portal login page.
 *
 * Portal login page is identified by the 'Portal Login' page template.
 * A WordPress page with that template must exist.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the URL of the frontend login page.
 * Falls back to wp_login_url() if no page template match is found.
 *
 * @return string
 */
function trufield_login_url(): string {
	static $url = null;
	if ( $url !== null ) {
		return $url;
	}

	$login_page = get_pages( [
		'meta_key'   => '_wp_page_template',
		'meta_value' => 'page-templates/login.php',
		'number'     => 1,
	] );

	$url = ! empty( $login_page ) ? get_permalink( $login_page[0]->ID ) : wp_login_url();
	return $url;
}

/**
 * Get the URL of the frontend forgot-password page.
 * Falls back to the WP core lost-password URL if no page template match is found.
 *
 * @return string
 */
function trufield_forgot_password_url(): string {
	static $url = null;
	if ( $url !== null ) {
		return $url;
	}

	$page = get_pages( [
		'meta_key'   => '_wp_page_template',
		'meta_value' => 'page-templates/forgot-password.php',
		'number'     => 1,
	] );

	// Fallback uses site_url() directly to avoid triggering the lostpassword_url filter (which calls this function).
	$url = ! empty( $page )
		? get_permalink( $page[0]->ID )
		: add_query_arg( 'action', 'lostpassword', wp_login_url() );

	return $url;
}

/**
 * Get the URL of the frontend reset-password page.
 * Falls back to the WP login URL if no page template match is found.
 *
 * @return string
 */
function trufield_reset_password_url(): string {
	static $url = null;
	if ( $url !== null ) {
		return $url;
	}

	$page = get_pages( [
		'meta_key'   => '_wp_page_template',
		'meta_value' => 'page-templates/reset-password.php',
		'number'     => 1,
	] );

	$url = ! empty( $page ) ? get_permalink( $page[0]->ID ) : wp_login_url();
	return $url;
}

/**
 * Get the URL of the portal dashboard page.
 * Falls back to home_url() if no page template match is found.
 *
 * @return string
 */
function trufield_dashboard_url(): string {
	static $url = null;
	if ( $url !== null ) {
		return $url;
	}

	$page = get_pages( [
		'meta_key'   => '_wp_page_template',
		'meta_value' => 'page-templates/dashboard.php',
		'number'     => 1,
	] );

	$url = ! empty( $page ) ? get_permalink( $page[0]->ID ) : home_url( '/dashboard/' );
	return $url;
}

// ── Protect portal content ─────────────────────────────────────────────────
add_action( 'template_redirect', 'trufield_gate_portal_access' );
function trufield_gate_portal_access(): void {
	// Allow WP cron, REST, XML-RPC, etc. to pass through.
	if ( wp_doing_cron() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		return;
	}

	// If not a plant_field single or a portal page template, do nothing.
	$is_portal_page = is_singular( 'plant_field' ) || trufield_is_portal_template();
	if ( ! $is_portal_page ) {
		return;
	}

	// Allow public access to the login, forgot-password, and reset-password pages.
	if ( trufield_current_page_is_login() || trufield_current_page_is_forgot_password() || trufield_current_page_is_reset_password() ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( trufield_login_url() );
		exit;
	}

	// Logged-in user but no permission to view this specific plant_field.
	if ( is_singular( 'plant_field' ) ) {
		$user_id = get_current_user_id();
		if ( ! current_user_can( 'read_plant_field', get_the_ID() ) ) {
			wp_die(
				esc_html__( 'You do not have permission to view this record.', 'trufield-portal' ),
				esc_html__( 'Access Denied', 'trufield-portal' ),
				[ 'response' => 403 ]
			);
		}
	}
}

/**
 * Whether the current page uses one of the portal page templates.
 */
function trufield_is_portal_template(): bool {
	if ( ! is_page() ) {
		return false;
	}
	$template = get_page_template_slug( get_the_ID() );
	return in_array( $template, [
		'page-templates/dashboard.php',
		'page-templates/leaderboard.php',
		'page-templates/login.php',
		'page-templates/forgot-password.php',
		'page-templates/reset-password.php',
	], true );
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
 * Whether the current page is the portal forgot-password page.
 */
function trufield_current_page_is_forgot_password(): bool {
	if ( ! is_page() ) {
		return false;
	}
	return get_page_template_slug( get_the_ID() ) === 'page-templates/forgot-password.php';
}

/**
 * Whether the current page is the portal reset-password page.
 */
function trufield_current_page_is_reset_password(): bool {
	if ( ! is_page() ) {
		return false;
	}
	return get_page_template_slug( get_the_ID() ) === 'page-templates/reset-password.php';
}

// ── Redirect already-logged-in users away from auth-only pages ─────────────
add_action( 'template_redirect', 'trufield_redirect_logged_in_from_login' );
function trufield_redirect_logged_in_from_login(): void {
	if ( ! is_user_logged_in() ) {
		return;
	}
	if ( trufield_current_page_is_login() || trufield_current_page_is_forgot_password() || trufield_current_page_is_reset_password() ) {
		wp_safe_redirect( trufield_dashboard_url() );
		exit;
	}
}

// ── Handle WP login form redirect ──────────────────────────────────────────
// When users log in via wp-login.php, send frontend roles to the portal dashboard.
add_filter( 'login_redirect', 'trufield_login_redirect', 10, 3 );
function trufield_login_redirect( string $redirect_to, string $requested_redirect_to, $user ): string {
	if ( is_wp_error( $user ) || ! $user instanceof WP_User ) {
		return $redirect_to;
	}

	$frontend_roles = [ 'sales_rep', 'leadership' ];
	if ( array_intersect( $frontend_roles, (array) $user->roles ) ) {
		return trufield_dashboard_url();
	}

	// Admins go to wp-admin as usual.
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
	$password = wp_unslash( $_POST['pwd'] ?? '' ); // Passwords must not be sanitized.
	$remember = ! empty( $_POST['rememberme'] );

	$user = wp_signon( [
		'user_login'    => $username,
		'user_password' => $password,
		'remember'      => $remember,
	], false );

	if ( is_wp_error( $user ) ) {
		$login_url = trufield_login_url();
		wp_safe_redirect( add_query_arg( 'login', 'failed', $login_url ) );
		exit;
	}

	wp_safe_redirect( trufield_dashboard_url() );
	exit;
}

// ── Frontend logout ────────────────────────────────────────────────────────
add_action( 'wp_logout', 'trufield_on_logout' );
function trufield_on_logout(): void {
	wp_safe_redirect( trufield_login_url() );
	exit;
}

// ── Point wp_lostpassword_url() at the custom page ─────────────────────────
add_filter( 'lostpassword_url', 'trufield_filter_lostpassword_url', 10, 2 );
function trufield_filter_lostpassword_url( string $url, string $redirect ): string {
	return trufield_forgot_password_url();
}

// ── Redirect wp-login.php password flows to custom frontend pages ───────────
// Fires at the top of wp-login.php before any output, making redirect safe.
add_action( 'login_init', 'trufield_redirect_wp_login_password_flows' );
function trufield_redirect_wp_login_password_flows(): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only routing, no state change.
	$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ?? '' ) );

	if ( $action === 'lostpassword' ) {
		// Guard against a loop in case the custom page falls back to the WP URL.
		$forgot_url = trufield_forgot_password_url();
		if ( strpos( $forgot_url, 'wp-login.php' ) !== false ) {
			return;
		}
		wp_safe_redirect( $forgot_url );
		exit;
	}

	if ( in_array( $action, [ 'rp', 'resetpass' ], true ) ) {
		$reset_url = trufield_reset_password_url();
		if ( strpos( $reset_url, 'wp-login.php' ) !== false ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$key   = sanitize_text_field( wp_unslash( $_GET['key']   ?? '' ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$login = sanitize_text_field( wp_unslash( $_GET['login'] ?? '' ) );
		if ( $key && $login ) {
			wp_safe_redirect( add_query_arg( [ 'key' => $key, 'login' => $login ], $reset_url ) );
			exit;
		}
	}
}

// ── Handle forgot-password form submission ─────────────────────────────────
add_action( 'admin_post_nopriv_trufield_forgot_password', 'trufield_handle_forgot_password' );
add_action( 'admin_post_trufield_forgot_password',        'trufield_handle_forgot_password' );
function trufield_handle_forgot_password(): void {
	$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) );
	if ( ! wp_verify_nonce( $nonce, 'trufield_forgot_password' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'trufield-portal' ), 403 );
	}

	$user_login = sanitize_text_field( wp_unslash( $_POST['user_login'] ?? '' ) );
	if ( $user_login === '' ) {
		wp_safe_redirect( add_query_arg( 'fp_error', 'invalid_username', trufield_forgot_password_url() ) );
		exit;
	}

	$result = retrieve_password( $user_login );

	if ( is_wp_error( $result ) ) {
		$not_found_codes = [ 'invalid_username', 'invalid_email', 'invalidcombo' ];
		$fp_error        = in_array( $result->get_error_code(), $not_found_codes, true )
			? 'invalid_username'
			: 'retrieve_failed';
		wp_safe_redirect( add_query_arg( 'fp_error', $fp_error, trufield_forgot_password_url() ) );
		exit;
	}

	// Success: send user to login with a friendly notice.
	wp_safe_redirect( add_query_arg( [ 'reset' => 'requested', 'fp_sent' => '1' ], trufield_forgot_password_url() ) );
	exit;
}

// ── Handle reset-password form submission ──────────────────────────────────
add_action( 'admin_post_nopriv_trufield_reset_password', 'trufield_handle_reset_password' );
add_action( 'admin_post_trufield_reset_password',        'trufield_handle_reset_password' );
function trufield_handle_reset_password(): void {
	$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) );
	if ( ! wp_verify_nonce( $nonce, 'trufield_reset_password' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'trufield-portal' ), 403 );
	}

	$rp_key   = sanitize_text_field( wp_unslash( $_POST['rp_key']   ?? '' ) );
	$rp_login = sanitize_text_field( wp_unslash( $_POST['rp_login'] ?? '' ) );
	$pass1    = wp_unslash( $_POST['pass1'] ?? '' ); // Passwords must not be sanitized.
	$pass2    = wp_unslash( $_POST['pass2'] ?? '' );

	// Build the base URL to redirect back with key/login preserved on error.
	$error_base = add_query_arg( [ 'key' => $rp_key, 'login' => $rp_login ], trufield_reset_password_url() );

	if ( strlen( $pass1 ) < 8 ) {
		wp_safe_redirect( add_query_arg( 'rp_error', 'password_too_short', $error_base ) );
		exit;
	}

	if ( $pass1 !== $pass2 ) {
		wp_safe_redirect( add_query_arg( 'rp_error', 'password_mismatch', $error_base ) );
		exit;
	}

	$user = check_password_reset_key( $rp_key, $rp_login );
	if ( is_wp_error( $user ) ) {
		wp_safe_redirect( add_query_arg( 'rp_error', 'reset_failed', $error_base ) );
		exit;
	}

	reset_password( $user, $pass1 );

	wp_safe_redirect( add_query_arg( 'reset', 'success', trufield_login_url() ) );
	exit;
}
