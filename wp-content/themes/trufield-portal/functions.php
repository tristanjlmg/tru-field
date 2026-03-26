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

/**
 * Phase rollout control.
 * Add 2 and/or 3 to this array when those phases are ready to go live.
 * Drives ACF field group visibility and admin column display.
 */
define( 'TRUFIELD_ACTIVE_PHASES', [ 1 ] );

// ── Modular includes ────────────────────────────────────────────────────────
$trufield_includes = [
	'inc/cpt.php',
	'inc/roles.php',
	'inc/acf-fields.php',
	'inc/queries.php',
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
function trufield_enqueue_assets(): void {
	wp_enqueue_style(
		'trufield-portal',
		TRUFIELD_THEME_URI . '/assets/css/portal.css',
		[],
		TRUFIELD_VERSION
	);

	wp_enqueue_script(
		'trufield-portal',
		TRUFIELD_THEME_URI . '/assets/js/portal.js',
		[],
		TRUFIELD_VERSION,
		true
	);

	// Pass data needed by JS.
	wp_localize_script( 'trufield-portal', 'TruField', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'trufield_grower_search' ),
	] );
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
