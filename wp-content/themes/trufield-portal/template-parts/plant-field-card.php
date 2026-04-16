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
$assigned_rep_id = (int) get_post_meta( $post_id, 'assigned_sales_rep', true );
$assigned_rep    = $assigned_rep_id ? get_userdata( $assigned_rep_id ) : false;
$is_sales_rep_user = in_array( 'sales_rep', (array) wp_get_current_user()->roles, true );
$record_status = get_post_meta( $post_id, 'record_status', true ) ?: 'active';
$phase_1_status   = trufield_get_phase_status( $post_id, 1 );
$phase_1_verified = (bool) get_post_meta( $post_id, 'phase_1_verified', true );
$phase_1_missing  = trufield_get_missing_required_fields( $post_id, 1 );
$phase_1_ready    = empty( $phase_1_missing );
$phase_2_status   = trufield_get_phase_status( $post_id, 2 );
$phase_2_verified = (bool) get_post_meta( $post_id, 'phase_2_verified', true );
$phase_2_missing  = trufield_get_missing_required_fields( $post_id, 2 );
$search_text      = strtolower(
	trim(
		implode(
			' ',
			array_filter(
				[
					get_the_title( $post_id ),
					(string) $retailer,
					(string) $farm_name,
					(string) $location,
				],
				static fn( $value ): bool => trim( (string) $value ) !== ''
			)
		)
	)
);

$pips = [];
foreach ( [ 1, 2, 3 ] as $phase ) {
	if ( ! in_array( $phase, TRUFIELD_ACTIVE_PHASES, true ) ) {
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

	if ( ! trufield_prerequisite_met( $post_id, $phase ) ) {
		$pips[] = [ 'class' => 'upcoming', 'icon' => '–', 'label' => 'P' . $phase, 'title' => sprintf( __( 'Phase %d locked until the previous phase is verified', 'trufield-portal' ), $phase ) ];
		continue;
	}

	if ( $phase_1_verified ) {
		if ( 1 === $phase ) {
			$pips[] = [ 'class' => 'verified', 'icon' => '✓', 'label' => 'P1', 'title' => __( 'Phase 1 verified', 'trufield-portal' ) ];
		} elseif ( $phase_2_verified ) {
			$pips[] = [ 'class' => 'verified', 'icon' => '✓', 'label' => 'P2', 'title' => __( 'Phase 2 verified', 'trufield-portal' ) ];
		} elseif ( $phase_2_status === 'completed' ) {
			$pips[] = [ 'class' => 'completed', 'icon' => '●', 'label' => 'P2', 'title' => __( 'Phase 2 submitted', 'trufield-portal' ) ];
		} elseif ( $phase_2_status === 'in_progress' ) {
			$pips[] = [ 'class' => 'in_progress', 'icon' => '◑', 'label' => 'P2', 'title' => __( 'Phase 2 in progress', 'trufield-portal' ) ];
		} else {
			$pips[] = [ 'class' => 'pending', 'icon' => '○', 'label' => 'P2', 'title' => __( 'Phase 2 not started', 'trufield-portal' ) ];
		}
	} elseif ( $phase_1_status === 'completed' ) {
		$pips[] = [ 'class' => 'completed', 'icon' => '●', 'label' => 'P1', 'title' => __( 'Phase 1 submitted', 'trufield-portal' ) ];
	} elseif ( $phase_1_status === 'in_progress' ) {
		$pips[] = [ 'class' => 'in_progress', 'icon' => '◑', 'label' => 'P1', 'title' => __( 'Phase 1 in progress', 'trufield-portal' ) ];
	} else {
		$pips[] = [ 'class' => 'pending', 'icon' => '○', 'label' => 'P1', 'title' => __( 'Phase 1 not started', 'trufield-portal' ) ];
	}
}

if ( $phase_2_verified ) {
	$phase_summary = __( 'Phase 2 verified.', 'trufield-portal' );
} elseif ( $phase_2_status === 'completed' ) {
	$phase_summary = __( 'Phase 2 is complete and waiting on admin verification.', 'trufield-portal' );
} elseif ( trufield_prerequisite_met( $post_id, 2 ) && $phase_2_status === 'in_progress' ) {
	$phase_summary = sprintf(
		_n(
			'Phase 2 draft in progress — %d required detail remaining.',
			'Phase 2 draft in progress — %d required details remaining.',
			count( $phase_2_missing ),
			'trufield-portal'
		),
		count( $phase_2_missing )
	);
} elseif ( $phase_1_verified ) {
	$phase_summary = __( 'Phase 1 verified. Open this trial to start Phase 2.', 'trufield-portal' );
} elseif ( $phase_1_status === 'completed' ) {
	$phase_summary = __( 'Phase 1 is complete and locked.', 'trufield-portal' );
} elseif ( $phase_1_status === 'in_progress' && $phase_1_ready ) {
	$phase_summary = __( 'Phase 1 is ready to verify on the next save.', 'trufield-portal' );
} elseif ( $phase_1_status === 'in_progress' ) {
	$phase_summary = sprintf(
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
	$phase_summary = __( 'Open this record to start the Phase 1 form.', 'trufield-portal' );
}
?>
<article class="tf-field-card tf-field-card--<?php echo esc_attr( $record_status ); ?>" data-tf-trial-card data-tf-search="<?php echo esc_attr( $search_text ); ?>">
<div class="tf-field-card__body">
<header class="tf-field-card__header">
<h2 class="tf-field-card__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h2>
<span class="tf-status-badge tf-status-badge--<?php echo esc_attr( $record_status ); ?>"><?php echo esc_html( ucwords( str_replace( '_', ' ', $record_status ) ) ); ?></span>
</header>

<dl class="tf-field-card__details">
<?php if ( ! $is_sales_rep_user && $assigned_rep ) : ?>
<div class="tf-field-card__detail-row">
<dt><?php esc_html_e( 'Rep', 'trufield-portal' ); ?></dt>
<dd><?php echo esc_html( $assigned_rep->display_name ); ?></dd>
</div>
<?php endif; ?>
<?php if ( $retailer ) : ?>
<div class="tf-field-card__detail-row">
<dt><?php esc_html_e( 'Retailer', 'trufield-portal' ); ?></dt>
<dd><?php echo esc_html( $retailer ); ?></dd>
</div>
<?php endif; ?>
<?php if ( $farm_name ) : ?>
<div class="tf-field-card__detail-row">
<dt><?php esc_html_e( 'Farm', 'trufield-portal' ); ?></dt>
<dd><?php echo esc_html( $farm_name ); ?></dd>
</div>
<?php endif; ?>
<?php if ( $location ) : ?>
<div class="tf-field-card__detail-row">
<dt><?php esc_html_e( 'Location', 'trufield-portal' ); ?></dt>
<dd><?php echo esc_html( $location ); ?></dd>
</div>
<?php endif; ?>
<div class="tf-field-card__detail-row tf-field-card__detail-row--workflow">
<dt><?php esc_html_e( 'Workflow', 'trufield-portal' ); ?></dt>
<dd>
<div class="tf-field-card__phases">
<?php foreach ( $pips as $pip ) : ?>
<span class="tf-phase-pip tf-phase-pip--<?php echo esc_attr( $pip['class'] ); ?>" title="<?php echo esc_attr( $pip['title'] ); ?>">
<?php echo esc_html( $pip['icon'] ); ?>
<?php echo esc_html( $pip['label'] ); ?>
</span>
<?php endforeach; ?>
</div>
</dd>
</div>
</dl>

<p class="tf-field-card__summary"><?php echo esc_html( $phase_summary ); ?></p>

<div class="tf-field-card__footer">
<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="tf-btn tf-btn--secondary tf-btn--sm tf-field-card__cta" aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>">
	<?php esc_html_e( 'View Trial', 'trufield-portal' ); ?>
</a>
</div>
</div>
</article>
