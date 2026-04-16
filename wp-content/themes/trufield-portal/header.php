<?php
/**
 * TruField Portal — Header Template
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$current_user = wp_get_current_user();
$is_public_auth = trufield_current_page_is_public_auth() && ! is_user_logged_in();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="tf-portal-wrap">
	<header class="tf-header<?php echo $is_public_auth ? ' tf-header--public-auth' : ''; ?>">
		<div class="tf-header__inner">
			<?php if ( $is_public_auth ) : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tf-header__logo tf-header__logo--auth" aria-label="<?php esc_attr_e( 'TruField Portal home', 'trufield-portal' ); ?>">
					<span class="tf-brand-wordmark" aria-hidden="true">
						<span class="tf-brand-wordmark__name">TruField</span>
						<span class="tf-brand-wordmark__badge">Portal</span>
					</span>
				</a>
			<?php else : ?>
			<a href="<?php echo esc_url( trufield_dashboard_url() ); ?>" class="tf-header__logo">
				<?php bloginfo( 'name' ); ?>
				<span class="tf-header__portal-badge">Portal</span>
			</a>

			<?php if ( is_user_logged_in() ) : ?>
			<button
				type="button"
				class="tf-nav-toggle"
				aria-expanded="false"
				aria-controls="tf-portal-nav"
				aria-label="<?php esc_attr_e( 'Toggle portal navigation', 'trufield-portal' ); ?>"
			>
				<span class="tf-nav-toggle__icon" aria-hidden="true">
					<span></span>
					<span></span>
					<span></span>
				</span>
			</button>
			<nav id="tf-portal-nav" class="tf-nav" aria-label="<?php esc_attr_e( 'Portal navigation', 'trufield-portal' ); ?>">
				<a href="<?php echo esc_url( trufield_dashboard_url() ); ?>"
				   class="tf-nav__link<?php echo trufield_is_portal_template() && get_page_template_slug() === 'page-templates/dashboard.php' ? ' tf-nav__link--active' : ''; ?>">
					<?php esc_html_e( 'Dashboard', 'trufield-portal' ); ?>
				</a>
				<a href="<?php echo esc_url( get_permalink( trufield_get_leaderboard_page_id() ) ); ?>"
				   class="tf-nav__link<?php echo get_page_template_slug() === 'page-templates/leaderboard.php' ? ' tf-nav__link--active' : ''; ?>">
					<?php esc_html_e( 'Leaderboard', 'trufield-portal' ); ?>
				</a>
				<?php if ( current_user_can( 'manage_options' ) ) : ?>
					<a href="<?php echo esc_url( admin_url() ); ?>" class="tf-nav__link">
						<?php esc_html_e( 'WP Admin', 'trufield-portal' ); ?>
					</a>
				<?php endif; ?>
				<a href="<?php echo esc_url( wp_logout_url() ); ?>" class="tf-nav__link tf-nav__link--logout">
					<?php esc_html_e( 'Log Out', 'trufield-portal' ); ?>
				</a>
			</nav>
			<div class="tf-header__user">
				<span class="tf-header__greeting"><?php printf( esc_html__( 'Hi %s', 'trufield-portal' ), esc_html( $current_user->display_name ) ); ?></span>
				<a href="<?php echo esc_url( wp_logout_url() ); ?>" class="tf-btn tf-btn--sm tf-btn--ghost tf-header__logout">
					<?php esc_html_e( 'Log Out', 'trufield-portal' ); ?>
				</a>
			</div>
			<?php endif; ?>
			<?php endif; ?>
		</div>
	</header>

	<main class="tf-main" id="tf-main">
<?php

/**
 * Helper: get leaderboard page ID (cached).
 */
function trufield_get_leaderboard_page_id(): int {
	static $id = null;
	if ( $id !== null ) {
		return $id;
	}
	$pages = get_pages( [
		'meta_key'   => '_wp_page_template',
		'meta_value' => 'page-templates/leaderboard.php',
		'number'     => 1,
	] );
	$id = ! empty( $pages ) ? (int) $pages[0]->ID : 0;
	return $id;
}
