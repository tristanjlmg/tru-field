<?php
/**
 * TruField Portal — Index / Fallback Template
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<div class="tf-container">
	<h1><?php esc_html_e( 'TruField Portal', 'trufield-portal' ); ?></h1>
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h2><?php the_title(); ?></h2>
			<div><?php the_content(); ?></div>
		</article>
	<?php endwhile; endif; ?>
</div>
<?php get_footer(); ?>
