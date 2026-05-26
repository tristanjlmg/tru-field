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
$field_name    = get_post_meta( $post_id, 'field_name', true );
$crop_specialist_contact = get_post_meta( $post_id, 'field_trial_contact', true );
$location      = get_post_meta( $post_id, 'field_location_address', true );
$product_tested = get_post_meta( $post_id, 'phase_1_product_being_tested', true );
$assigned_rep_id = (int) get_post_meta( $post_id, 'assigned_sales_rep', true );
$assigned_rep    = $assigned_rep_id ? get_userdata( $assigned_rep_id ) : false;
$is_sales_rep_user = in_array( 'sales_rep', (array) wp_get_current_user()->roles, true );
$record_status = get_post_meta( $post_id, 'record_status', true ) ?: 'active';
$trial_identifier = get_the_title( $post_id );
$card_title = '';
$active_phases   = array_values( array_intersect( [ 1, 2, 3 ], TRUFIELD_ACTIVE_PHASES ) );
$phase_statuses  = [];
$phase_verified  = [];
$phase_missing   = [];

foreach ( [ $field_name, $farm_name, $retailer, $product_tested, $trial_identifier ] as $card_title_candidate ) {
	$card_title_candidate = trim( (string) $card_title_candidate );
	if ( '' !== $card_title_candidate ) {
		$card_title = $card_title_candidate;
		break;
	}
}

$search_text      = strtolower(
	trim(
		implode(
			' ',
			array_filter(
				[
					$trial_identifier,
					(string) $product_tested,
					(string) $retailer,
					(string) $farm_name,
					(string) $field_name,
					(string) $crop_specialist_contact,
					$assigned_rep ? (string) $assigned_rep->display_name : '',
					(string) $location,
				],
				static fn( $value ): bool => trim( (string) $value ) !== ''
			)
		)
	)
);

foreach ( [ 1, 2, 3 ] as $phase ) {
	$phase_statuses[ $phase ] = trufield_get_phase_status( $post_id, $phase );
	$phase_verified[ $phase ] = (bool) get_post_meta( $post_id, "phase_{$phase}_verified", true );
	$phase_missing[ $phase ]  = trufield_get_missing_required_fields( $post_id, $phase );
}

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

	if ( $phase_verified[ $phase ] ) {
		$pips[] = [ 'class' => 'verified', 'icon' => '✓', 'label' => 'P' . $phase, 'title' => sprintf( __( 'Phase %d verified', 'trufield-portal' ), $phase ) ];
	} elseif ( $phase_statuses[ $phase ] === 'completed' ) {
		$pips[] = [ 'class' => 'completed', 'icon' => '●', 'label' => 'P' . $phase, 'title' => sprintf( __( 'Phase %d submitted', 'trufield-portal' ), $phase ) ];
	} elseif ( $phase_statuses[ $phase ] === 'in_progress' ) {
		$pips[] = [ 'class' => 'in_progress', 'icon' => '◑', 'label' => 'P' . $phase, 'title' => sprintf( __( 'Phase %d in progress', 'trufield-portal' ), $phase ) ];
	} else {
		$pips[] = [ 'class' => 'pending', 'icon' => '○', 'label' => 'P' . $phase, 'title' => sprintf( __( 'Phase %d not started', 'trufield-portal' ), $phase ) ];
	}
}

$current_phase = 1;
foreach ( array_reverse( $active_phases ) as $phase ) {
	if ( trufield_prerequisite_met( $post_id, $phase ) ) {
		$current_phase = $phase;
		break;
	}
}

$current_phase_status   = $phase_statuses[ $current_phase ] ?? 'pending';
$current_phase_verified = $phase_verified[ $current_phase ] ?? false;
$current_phase_missing  = $phase_missing[ $current_phase ] ?? [];

if ( $current_phase_verified ) {
	$phase_summary = sprintf( __( 'Phase %d verified.', 'trufield-portal' ), $current_phase );
} elseif ( $current_phase_status === 'completed' ) {
	$phase_summary = sprintf( __( 'Phase %d is complete and waiting on admin verification.', 'trufield-portal' ), $current_phase );
} elseif ( $current_phase_status === 'in_progress' && empty( $current_phase_missing ) ) {
	$phase_summary = sprintf( __( 'Phase %d is ready to verify on the next save.', 'trufield-portal' ), $current_phase );
} elseif ( $current_phase_status === 'in_progress' ) {
	$phase_summary = sprintf(
		/* translators: 1: phase number, 2: number of missing required fields. */
		_n(
			'Phase %1$d draft in progress — %2$d required detail remaining.',
			'Phase %1$d draft in progress — %2$d required details remaining.',
			count( $current_phase_missing ),
			'trufield-portal'
		),
		$current_phase,
		count( $current_phase_missing )
	);
	if ( $current_phase === 3 ) {
		$phase_summary = sprintf(
			/* translators: 1: phase number, 2: number of missing required fields. */
			_n(
				'Phase %1$d in progress — %2$d required detail remaining.',
				'Phase %1$d in progress — %2$d required details remaining.',
				count( $current_phase_missing ),
				'trufield-portal'
			),
			$current_phase,
			count( $current_phase_missing )
		);
	}

	$next_phase = $current_phase + 1;
	if ( in_array( $next_phase, $active_phases, true ) && ( $phase_verified[ $current_phase ] ?? false ) ) {
		$phase_summary = sprintf( __( 'Phase %1$d verified. Open this trial to start Phase %2$d.', 'trufield-portal' ), $current_phase, $next_phase );
	}
} else {
	$phase_summary = sprintf( __( 'Open this record to start the Phase %d form.', 'trufield-portal' ), $current_phase );
}
?>
<article class="tf-field-card tf-field-card--<?php echo esc_attr( $record_status ); ?>" data-tf-trial-card data-tf-search="<?php echo esc_attr( $search_text ); ?>">
<div class="tf-field-card__body">
<header class="tf-field-card__header">
<h2 class="tf-field-card__title" title="<?php echo esc_attr( $trial_identifier ); ?>"><?php echo esc_html( $card_title ); ?></h2>
<span class="tf-status-badge tf-status-badge--<?php echo esc_attr( $record_status ); ?>"><?php echo esc_html( ucwords( str_replace( '_', ' ', $record_status ) ) ); ?></span>
</header>

<dl class="tf-field-card__details">
<?php if ( $retailer ) : ?>
<div class="tf-field-card__detail-row">
<dt><?php esc_html_e( 'Retailer', 'trufield-portal' ); ?></dt>
<dd><?php echo esc_html( $retailer ); ?></dd>
</div>
<?php endif; ?>
<?php if ( $farm_name ) : ?>
<div class="tf-field-card__detail-row">
<dt><?php esc_html_e( 'Farm Name', 'trufield-portal' ); ?></dt>
<dd><?php echo esc_html( $farm_name ); ?></dd>
</div>
<?php endif; ?>
<?php if ( $field_name ) : ?>
<div class="tf-field-card__detail-row">
<dt><?php esc_html_e( 'Field Name', 'trufield-portal' ); ?></dt>
<dd><?php echo esc_html( $field_name ); ?></dd>
</div>
<?php endif; ?>
<?php if ( $crop_specialist_contact ) : ?>
<div class="tf-field-card__detail-row">
<dt><?php esc_html_e( 'Crop Specialist Contact', 'trufield-portal' ); ?></dt>
<dd><?php echo esc_html( $crop_specialist_contact ); ?></dd>
</div>
<?php endif; ?>
<?php if ( $product_tested ) : ?>
<div class="tf-field-card__detail-row">
<dt><?php esc_html_e( 'Product Name', 'trufield-portal' ); ?></dt>
<dd><?php echo esc_html( $product_tested ); ?></dd>
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
