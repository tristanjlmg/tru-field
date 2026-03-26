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

$pips = [];
foreach ( [ 1, 2, 3 ] as $phase ) {
if ( $phase > 1 ) {
// Phases 2 & 3 are not yet active in the current rollout.
$pips[] = [ 'class' => 'upcoming', 'icon' => '–', 'label' => 'S' . $phase, 'title' => sprintf( 'Step %d — Coming Soon', $phase ) ];
continue;
}

$verified = (bool) get_post_meta( $post_id, "phase_{$phase}_verified", true );
$status   = trufield_get_phase_status( $post_id, $phase );

if ( $verified ) {
$pips[] = [ 'class' => 'verified', 'icon' => '✓', 'label' => 'S' . $phase, 'title' => sprintf( 'Step %d verified', $phase ) ];
} elseif ( $status === 'completed' ) {
$pips[] = [ 'class' => 'completed', 'icon' => '●', 'label' => 'S' . $phase, 'title' => sprintf( 'Step %d completed', $phase ) ];
} elseif ( $status === 'in_progress' ) {
$pips[] = [ 'class' => 'in_progress', 'icon' => '◑', 'label' => 'S' . $phase, 'title' => sprintf( 'Step %d in progress', $phase ) ];
} else {
$pips[] = [ 'class' => 'pending', 'icon' => '○', 'label' => 'S' . $phase, 'title' => sprintf( 'Step %d pending', $phase ) ];
}
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

<div class="tf-field-card__phases">
<?php foreach ( $pips as $pip ) : ?>
<span class="tf-phase-pip tf-phase-pip--<?php echo esc_attr( $pip['class'] ); ?>" title="<?php echo esc_attr( $pip['title'] ); ?>">
<?php echo esc_html( $pip['icon'] ); ?>
<?php echo esc_html( $pip['label'] ); ?>
</span>
<?php endforeach; ?>
</div>
</a>
</article>
