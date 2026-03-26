<?php
/**
 * TruField Portal — Header Template
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$current_user = wp_get_current_user();
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
	<header class="tf-header">
		<div class="tf-header__inner">
			<a href="<?php echo esc_url( trufield_dashboard_url() ); ?>" class="tf-header__logo">
				<?php bloginfo( 'name' ); ?>
				<span class="tf-header__portal-badge">Portal</span>
			</a>

			<?php if ( is_user_logged_in() ) : ?>
			<nav class="tf-nav" aria-label="<?php esc_attr_e( 'Portal navigation', 'trufield-portal' ); ?>">
				<a href="<?php echo esc_url( trufield_dashboard_url() ); ?>"
				   class="tf-nav__link<?php echo trufield_is_portal_template() && get_page_template_slug() === 'page-templates/dashboard.php' ? ' tf-nav__link--active' : ''; ?>">
					<?php esc_html_e( 'Dashboard', 'trufield-portal' ); ?>
				</a>
				<a href="<?php echo esc_url( get_permalink( trufield_get_leaderboard_page_id() ) ); ?>"
				   class="tf-nav__link<?php echo get_page_template_slug() === 'page-templates/leaderboard.php' ? ' tf-nav__link--active' : ''; ?>">
					<?php esc_html_e( 'Leaderboard', 'trufield-portal' ); ?>
				</a>
			</nav>
			<div class="tf-header__user">
				<span><?php echo esc_html( $current_user->display_name ); ?></span>
				<a href="<?php echo esc_url( wp_logout_url() ); ?>" class="tf-btn tf-btn--sm tf-btn--ghost">
					<?php esc_html_e( 'Log Out', 'trufield-portal' ); ?>
				</a>
			</div>
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
