<?php
/**
 * TruField Portal — 404 Template
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<div class="tf-container tf-404">
	<h1><?php esc_html_e( 'Page Not Found', 'trufield-portal' ); ?></h1>
	<p><?php esc_html_e( 'The page you\'re looking for doesn\'t exist.', 'trufield-portal' ); ?></p>
	<a href="<?php echo esc_url( trufield_dashboard_url() ); ?>" class="tf-btn">
		<?php esc_html_e( '← Back to Dashboard', 'trufield-portal' ); ?>
	</a>
</div>
<?php get_footer(); ?>
