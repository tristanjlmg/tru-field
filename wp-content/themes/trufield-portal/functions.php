<?php
/**
 * TruField Portal — Theme Bootstrap
 *
 * Loads all modular include files and registers core WordPress hooks.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TRUFIELD_THEME_DIR', get_template_directory() );
define( 'TRUFIELD_THEME_URI', get_template_directory_uri() );
define( 'TRUFIELD_VERSION', '1.0.0' );

function trufield_asset_version( string $relative_path ): string {
	$file_path = TRUFIELD_THEME_DIR . '/' . ltrim( $relative_path, '/' );
	if ( file_exists( $file_path ) ) {
		$modified = filemtime( $file_path );
		if ( false !== $modified ) {
			return (string) $modified;
		}
	}

	return TRUFIELD_VERSION;
}

/**
 * Phase rollout control.
 * Add 2 and/or 3 to this array when those phases are ready to go live.
 * Drives ACF field group visibility and admin column display.
 */
define( 'TRUFIELD_ACTIVE_PHASES', [ 1, 2 ] );

// ── Modular includes ────────────────────────────────────────────────────────
$trufield_includes = [
	'inc/cpt.php',
	'inc/roles.php',
	'inc/acf-fields.php',
	'inc/queries.php',
	'inc/imports.php',
	'inc/workflow.php',
	'inc/scoring.php',
	'inc/auth-routing.php',
	'inc/exports.php',
	'inc/admin.php',
];

foreach ( $trufield_includes as $file ) {
	require_once TRUFIELD_THEME_DIR . '/' . $file;
}

// ── Theme setup ─────────────────────────────────────────────────────────────
add_action( 'after_setup_theme', 'trufield_theme_setup' );
function trufield_theme_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'gallery', 'caption', 'script', 'style' ] );
	load_theme_textdomain( 'trufield-portal', TRUFIELD_THEME_DIR . '/languages' );
}

// ── Enqueue assets ──────────────────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', 'trufield_enqueue_assets' );
function trufield_get_google_maps_api_key(): string {
	static $api_key = null;

	if ( null !== $api_key ) {
		return $api_key;
	}

	$env_sources = [
		getenv( 'TRUFIELD_GOOGLE_MAPS_API_KEY' ),
		$_ENV['TRUFIELD_GOOGLE_MAPS_API_KEY'] ?? null,
		$_SERVER['TRUFIELD_GOOGLE_MAPS_API_KEY'] ?? null,
	];

	if ( defined( 'TRUFIELD_GOOGLE_MAPS_API_KEY' ) ) {
		$api_key = (string) TRUFIELD_GOOGLE_MAPS_API_KEY;
	} else {
		$api_key = '';

		foreach ( $env_sources as $candidate ) {
			if ( is_string( $candidate ) && trim( $candidate ) !== '' ) {
				$api_key = trim( $candidate );
				break;
			}
		}

		if ( '' === $api_key ) {
			$option_key = get_option( 'trufield_google_maps_api_key', '' );
			if ( is_string( $option_key ) && trim( $option_key ) !== '' ) {
				$api_key = trim( $option_key );
			}
		}
	}

	$api_key = trim( (string) apply_filters( 'trufield_google_maps_api_key', $api_key ) );

	return $api_key;
}

function trufield_enqueue_assets(): void {
	wp_enqueue_style(
		'trufield-portal',
		TRUFIELD_THEME_URI . '/assets/css/portal.css',
		[],
		trufield_asset_version( 'assets/css/portal.css' )
	);

	wp_enqueue_script(
		'trufield-portal',
		TRUFIELD_THEME_URI . '/assets/js/portal.js',
		[],
		trufield_asset_version( 'assets/js/portal.js' ),
		true
	);

	// Pass data needed by JS.
	wp_localize_script( 'trufield-portal', 'TruField', [
		'ajaxUrl'            => admin_url( 'admin-ajax.php' ),
		'nonce'              => wp_create_nonce( 'trufield_grower_search' ),
		'geocodeNonce'       => wp_create_nonce( 'trufield_geocode_address' ),
		'googlePlacesEnabled' => '' !== trufield_get_google_maps_api_key(),
	] );

	if ( is_singular( 'plant_field' ) ) {
		$api_key = trufield_get_google_maps_api_key();

		if ( '' !== $api_key ) {
			wp_enqueue_script(
				'trufield-google-maps',
				add_query_arg(
					[
						'key'       => $api_key,
						'libraries' => 'places',
					],
					'https://maps.googleapis.com/maps/api/js'
				),
				[ 'trufield-portal' ],
				null,
				true
			);

			wp_script_add_data( 'trufield-google-maps', 'defer', true );
		}
	}
}

add_action( 'admin_enqueue_scripts', 'trufield_enqueue_admin_assets' );
function trufield_enqueue_admin_assets( string $hook ): void {
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'plant_field' ) {
		return;
	}

	wp_enqueue_script(
		'trufield-portal-admin',
		TRUFIELD_THEME_URI . '/assets/js/admin-location-sync.js',
		[ 'jquery', 'acf-input' ],
		trufield_asset_version( 'assets/js/admin-location-sync.js' ),
		true
	);
}

// ── Register page templates (page-templates/ subdir) ────────────────────────
add_filter( 'theme_page_templates', 'trufield_register_page_templates' );
function trufield_register_page_templates( array $templates ): array {
	$templates['page-templates/dashboard.php']       = __( 'Portal Dashboard', 'trufield-portal' );
	$templates['page-templates/leaderboard.php']     = __( 'Portal Leaderboard', 'trufield-portal' );
	$templates['page-templates/login.php']           = __( 'Portal Login', 'trufield-portal' );
	$templates['page-templates/forgot-password.php'] = __( 'Portal Forgot Password', 'trufield-portal' );
	$templates['page-templates/reset-password.php']  = __( 'Portal Reset Password', 'trufield-portal' );
	return $templates;
}
