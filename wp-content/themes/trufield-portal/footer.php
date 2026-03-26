<?php
/**
 * TruField Portal — Footer Template
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	</main><!-- .tf-main -->

	<footer class="tf-footer">
		<div class="tf-footer__inner">
			<p>
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
				<?php bloginfo( 'name' ); ?> &mdash; TruField Portal
			</p>
		</div>
	</footer>
</div><!-- .tf-portal-wrap -->

<?php wp_footer(); ?>
</body>
</html>
