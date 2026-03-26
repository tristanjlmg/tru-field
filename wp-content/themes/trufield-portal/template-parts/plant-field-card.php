<?php
/**
 * TruField Portal — Template Part: Plant Field Card
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

$post = $args['post'] ?? null;
if ( ! $post instanceof WP_Post ) {
return;
}

$post_id       = $post->ID;
$retailer      = get_post_meta( $post_id, 'retailer_name', true );
$farm_name     = get_post_meta( $post_id, 'farm_name', true );
$location      = get_post_meta( $post_id, 'field_location_address', true );
$record_status = get_post_meta( $post_id, 'record_status', true ) ?: 'active';
$phase_1_status   = trufield_get_phase_status( $post_id, 1 );
$phase_1_verified = (bool) get_post_meta( $post_id, 'phase_1_verified', true );
$phase_1_missing  = trufield_get_missing_required_fields( $post_id, 1 );
$phase_1_ready    = empty( $phase_1_missing );

$pips = [];
foreach ( [ 1, 2, 3 ] as $phase ) {
	if ( $phase > 1 ) {
		// Phases 2 & 3 are not yet active in the current rollout.
		$pips[] = [
			'class' => 'upcoming',
			'icon'  => '–',
			'label' => 'P' . $phase,
			'title' => sprintf(
				/* translators: %d = phase number. */
				__( 'Phase %d — Future form', 'trufield-portal' ),
				$phase
			),
		];
		continue;
	}

	if ( $phase_1_verified ) {
		$pips[] = [ 'class' => 'verified', 'icon' => '✓', 'label' => 'P1', 'title' => __( 'Phase 1 verified', 'trufield-portal' ) ];
	} elseif ( $phase_1_status === 'completed' ) {
		$pips[] = [ 'class' => 'completed', 'icon' => '●', 'label' => 'P1', 'title' => __( 'Phase 1 submitted', 'trufield-portal' ) ];
	} elseif ( $phase_1_status === 'in_progress' ) {
		$pips[] = [ 'class' => 'in_progress', 'icon' => '◑', 'label' => 'P1', 'title' => __( 'Phase 1 in progress', 'trufield-portal' ) ];
	} else {
		$pips[] = [ 'class' => 'pending', 'icon' => '○', 'label' => 'P1', 'title' => __( 'Phase 1 not started', 'trufield-portal' ) ];
	}
}

if ( $phase_1_verified ) {
	$phase_1_summary = __( 'Phase 1 verified by the admin team.', 'trufield-portal' );
} elseif ( $phase_1_status === 'completed' ) {
	$phase_1_summary = __( 'Phase 1 submitted and awaiting admin verification.', 'trufield-portal' );
} elseif ( $phase_1_status === 'in_progress' && $phase_1_ready ) {
	$phase_1_summary = __( 'Phase 1 draft is ready to mark complete.', 'trufield-portal' );
} elseif ( $phase_1_status === 'in_progress' ) {
	$phase_1_summary = sprintf(
		/* translators: %d = number of missing required fields. */
		_n(
			'Phase 1 draft in progress — %d required detail remaining.',
			'Phase 1 draft in progress — %d required details remaining.',
			count( $phase_1_missing ),
			'trufield-portal'
		),
		count( $phase_1_missing )
	);
} else {
	$phase_1_summary = __( 'Open this record to start the Phase 1 form.', 'trufield-portal' );
}
?>
<article class="tf-field-card tf-field-card--<?php echo esc_attr( $record_status ); ?>">
<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="tf-field-card__link" aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>">
<header class="tf-field-card__header">
<h2 class="tf-field-card__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h2>
<span class="tf-status-badge tf-status-badge--<?php echo esc_attr( $record_status ); ?>"><?php echo esc_html( ucwords( str_replace( '_', ' ', $record_status ) ) ); ?></span>
</header>

<div class="tf-field-card__meta">
<?php if ( $retailer ) : ?>
<span class="tf-field-card__meta-item"><?php echo esc_html( $retailer ); ?></span>
<?php endif; ?>
<?php if ( $farm_name ) : ?>
<span class="tf-field-card__meta-item"><?php echo esc_html( $farm_name ); ?></span>
<?php endif; ?>
<?php if ( $location ) : ?>
<span class="tf-field-card__meta-item"><?php echo esc_html( $location ); ?></span>
<?php endif; ?>
</div>

<p class="tf-field-card__phase-title"><?php esc_html_e( 'Phase 1 progress', 'trufield-portal' ); ?></p>
<div class="tf-field-card__phases">
<?php foreach ( $pips as $pip ) : ?>
<span class="tf-phase-pip tf-phase-pip--<?php echo esc_attr( $pip['class'] ); ?>" title="<?php echo esc_attr( $pip['title'] ); ?>">
<?php echo esc_html( $pip['icon'] ); ?>
<?php echo esc_html( $pip['label'] ); ?>
</span>
<?php endforeach; ?>
</div>
<p class="tf-field-card__summary"><?php echo esc_html( $phase_1_summary ); ?></p>
</a>
</article>
