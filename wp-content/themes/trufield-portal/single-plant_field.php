<?php
/**
 * TruField Portal — Single Plant Field Template
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

get_header();

if ( ! have_posts() ) {
get_footer();
return;
}

the_post();
$post_id  = get_the_ID();
$user_id  = get_current_user_id();
$is_admin = trufield_user_is_admin( $user_id );

$success = sanitize_key( $_GET['tf_success'] ?? '' );
$error   = sanitize_text_field( rawurldecode( $_GET['tf_error'] ?? '' ) );

$assigned_rep_id    = (int) get_post_meta( $post_id, 'assigned_sales_rep', true );
$assigned_rep       = $assigned_rep_id ? get_userdata( $assigned_rep_id ) : false;
$record_status      = get_post_meta( $post_id, 'record_status', true ) ?: 'active';
$retailer_name      = get_post_meta( $post_id, 'retailer_name', true );
$retailer_contact   = get_post_meta( $post_id, 'retailer_key_contact', true );
$rsm_bam            = get_post_meta( $post_id, 'rsm_bam', true );
$fsa                = get_post_meta( $post_id, 'fsa', true );
$import_city        = get_post_meta( $post_id, 'import_city', true );
$import_state       = get_post_meta( $post_id, 'import_state', true );
$farm_name          = get_post_meta( $post_id, 'farm_name', true );
$field_location     = get_post_meta( $post_id, 'field_location_address', true );
$phase_1_status     = trufield_get_phase_status( $post_id, 1 );
$phase_1_verified   = (bool) get_post_meta( $post_id, 'phase_1_verified', true );
$phase_1_missing    = trufield_get_missing_required_fields( $post_id, 1 );
$phase_1_required_ok = empty( $phase_1_missing );
$phase_1_validation_missing = trufield_get_missing_validation_fields( $post_id, 1 );
$phase_1_validation_ok = empty( $phase_1_validation_missing );
$phase_1_can_edit   = trufield_can_edit_phase( $post_id, 1, $user_id );
$phase_verified     = [];
$phase_statuses     = [];
$step_titles        = [
1 => __( 'Phase 1 Setup', 'trufield-portal' ),
2 => __( 'Application & Monitoring', 'trufield-portal' ),
3 => __( 'Harvest & Engagement', 'trufield-portal' ),
];

if ( $phase_1_verified ) {
	$phase_1_panel_title = __( 'Phase 1 verified', 'trufield-portal' );
	$phase_1_panel_copy  = __( 'The required Phase 1 details are complete and this record now counts as 1 valid grower entry.', 'trufield-portal' );
	$phase_1_panel_note  = __( 'Phases 2 and 3 are separate forms and will stay unavailable until a later rollout.', 'trufield-portal' );
} elseif ( $phase_1_status === 'completed' ) {
	$phase_1_panel_title = __( 'Phase 1 needs final details', 'trufield-portal' );
	$phase_1_panel_copy  = __( 'This record will count as a valid grower entry after the remaining required Phase 1 details are filled in and saved.', 'trufield-portal' );
	$phase_1_panel_note  = __( 'Finish the remaining Phase 1 fields below. Future phases remain placeholders for now.', 'trufield-portal' );
} elseif ( $phase_1_status === 'in_progress' && $phase_1_validation_ok ) {
	$phase_1_panel_title = __( 'Ready to count as a valid entry', 'trufield-portal' );
	$phase_1_panel_copy  = __( 'Every required Phase 1 field is present. Save once more if needed and this record will count as 1 valid grower entry.', 'trufield-portal' );
	$phase_1_panel_note  = __( 'You can still save your draft and return later if needed.', 'trufield-portal' );
	} elseif ( $phase_1_status === 'in_progress' && $phase_1_required_ok ) {
	$phase_1_panel_title = __( 'Required details are complete', 'trufield-portal' );
	$phase_1_panel_copy  = __( 'The required fields are done, but a valid grower entry still needs the remaining required Phase 1 details completed.', 'trufield-portal' );
	$phase_1_panel_note  = sprintf(
		/* translators: %d = number of remaining phase 1 fields for validation. */
		_n(
			'%d required Phase 1 field still needs attention before this record counts as a valid grower entry.',
			'%d required Phase 1 fields still need attention before this record counts as a valid grower entry.',
			count( $phase_1_validation_missing ),
			'trufield-portal'
		),
		count( $phase_1_validation_missing )
	);
} elseif ( $phase_1_status === 'in_progress' ) {
	$phase_1_panel_title = __( 'Phase 1 draft in progress', 'trufield-portal' );
	$phase_1_panel_copy  = __( 'Continue filling in the Phase 1 details for this assigned record. Save progress anytime and return later.', 'trufield-portal' );
	$phase_1_panel_note  = sprintf(
		/* translators: %d = number of missing required fields. */
		_n(
			'%d required Phase 1 field still needs attention before this record counts as a valid grower entry.',
			'%d required Phase 1 fields still need attention before this record counts as a valid grower entry.',
			count( $phase_1_validation_missing ),
			'trufield-portal'
		),
		count( $phase_1_validation_missing )
	);
} else {
	$phase_1_panel_title = __( 'Start Phase 1', 'trufield-portal' );
	$phase_1_panel_copy  = __( 'Phase 1 is the only active form for this record right now. Fill out the required Phase 1 fields below for this record to count as 1 valid grower entry.', 'trufield-portal' );
	$phase_1_panel_note  = $phase_1_can_edit
		? __( 'Optional details can be added before submission, but Phases 2 and 3 are not available yet.', 'trufield-portal' )
		: __( 'If this record should be assigned to you for Phase 1 work, contact the admin team.', 'trufield-portal' );
}

// Only Phase 1 is active in the current rollout.
foreach ( [ 1 ] as $phase ) {
$phase_verified[ $phase ] = (bool) get_post_meta( $post_id, "phase_{$phase}_verified", true );
$phase_statuses[ $phase ] = trufield_get_phase_status( $post_id, $phase );
}
?>
<div class="tf-container tf-plant-field">

<?php if ( $success ) : ?>
<div class="tf-alert tf-alert--success" role="alert">
<?php
if ( preg_match( '/^phase_(\d)_completed$/', $success, $matches ) ) {
	$phase = (int) $matches[1];
	echo esc_html( sprintf( trufield_phase_auto_verifies( $phase ) ? __( 'Phase %d saved. It will count once the required fields are complete.', 'trufield-portal' ) : __( 'Phase %d submitted for admin verification.', 'trufield-portal' ), $phase ) );
	} elseif ( 'phase_1_autoverified' === $success ) {
		esc_html_e( 'Phase 1 counted as a valid grower entry once the required Phase 1 fields were completed.', 'trufield-portal' );
} elseif ( 'address_verified' === $success ) {
	esc_html_e( 'Field location verified. Latitude and longitude were updated.', 'trufield-portal' );
} else {
	esc_html_e( 'Phase 1 progress saved.', 'trufield-portal' );
}
?>
</div>
<?php endif; ?>

<?php if ( $error ) : ?>
<div class="tf-alert tf-alert--error" role="alert"><?php echo esc_html( $error ); ?></div>
<?php endif; ?>

<div class="tf-record-header">
<a href="<?php echo esc_url( trufield_dashboard_url() ); ?>" class="tf-back-link">&larr; <?php esc_html_e( 'Dashboard', 'trufield-portal' ); ?></a>
<p class="tf-record-header__eyebrow"><?php esc_html_e( 'Phase 1 rollout', 'trufield-portal' ); ?></p>
<div class="tf-record-header__title-row">
<h1><?php the_title(); ?></h1>
<span class="tf-status-badge tf-status-badge--<?php echo esc_attr( $record_status ); ?>"><?php echo esc_html( ucwords( str_replace( '_', ' ', $record_status ) ) ); ?></span>
</div>
<p class="tf-record-header__support"><?php esc_html_e( 'Phase 1 is the only active form for this record right now. Phases 2 and 3 are shown below as future timeline placeholders only.', 'trufield-portal' ); ?></p>

<div class="tf-record-meta">
<?php if ( $assigned_rep ) : ?>
<span><strong><?php esc_html_e( 'Rep:', 'trufield-portal' ); ?></strong> <?php echo esc_html( $assigned_rep->display_name ); ?></span>
<?php endif; ?>
<?php if ( $retailer_name ) : ?>
<span><strong><?php esc_html_e( 'Retailer:', 'trufield-portal' ); ?></strong> <?php echo esc_html( $retailer_name ); ?></span>
<?php endif; ?>
<?php if ( $retailer_contact ) : ?>
<span><strong><?php esc_html_e( 'Retailer Contact:', 'trufield-portal' ); ?></strong> <?php echo esc_html( $retailer_contact ); ?></span>
<?php endif; ?>
<?php if ( $rsm_bam ) : ?>
<span><strong><?php esc_html_e( 'RSM / BAM:', 'trufield-portal' ); ?></strong> <?php echo esc_html( $rsm_bam ); ?></span>
<?php endif; ?>
<?php if ( $fsa ) : ?>
<span><strong><?php esc_html_e( 'FSA:', 'trufield-portal' ); ?></strong> <?php echo esc_html( $fsa ); ?></span>
<?php endif; ?>
<?php if ( $farm_name ) : ?>
<span><strong><?php esc_html_e( 'Farm:', 'trufield-portal' ); ?></strong> <?php echo esc_html( $farm_name ); ?></span>
<?php endif; ?>
<?php if ( $import_city ) : ?>
<span><strong><?php esc_html_e( 'City:', 'trufield-portal' ); ?></strong> <?php echo esc_html( $import_city ); ?></span>
<?php endif; ?>
<?php if ( $import_state ) : ?>
<span><strong><?php esc_html_e( 'Imported State:', 'trufield-portal' ); ?></strong> <?php echo esc_html( $import_state ); ?></span>
<?php endif; ?>
<?php if ( $field_location ) : ?>
<span><strong><?php esc_html_e( 'Location:', 'trufield-portal' ); ?></strong> <?php echo esc_html( $field_location ); ?></span>
<?php endif; ?>
</div>

<div class="tf-phase-status-panel">
<p class="tf-phase-status-panel__eyebrow"><?php esc_html_e( 'What to do next', 'trufield-portal' ); ?></p>
<h2 class="tf-phase-status-panel__title"><?php echo esc_html( $phase_1_panel_title ); ?></h2>
<p class="tf-phase-status-panel__copy"><?php echo esc_html( $phase_1_panel_copy ); ?></p>
<p class="tf-phase-status-panel__note"><?php echo esc_html( $phase_1_panel_note ); ?></p>
</div>
</div>

<div class="tf-steps" aria-label="<?php esc_attr_e( 'Phase timeline', 'trufield-portal' ); ?>">
<?php foreach ( [ 1, 2, 3 ] as $phase ) : ?>
<?php
if ( $phase === 1 ) {
	$verified = $phase_verified[1];
	$status   = $phase_statuses[1];
	if ( $verified ) {
		$state = 'completed-verified';
		$icon  = '✓';
	} elseif ( $status === 'completed' ) {
		$state = 'completed-pending';
		$icon  = '✓';
	} else {
		$state = 'active';
		$icon  = '1';
	}
} else {
	// Phase 2 & 3 are not yet part of the current rollout.
	$state = 'upcoming';
	$icon  = '–';
}
?>
<div class="tf-step tf-step--<?php echo esc_attr( $state ); ?>" data-step="<?php echo (int) $phase; ?>">
<div class="tf-step__circle"><?php echo esc_html( $icon ); ?></div>
<div class="tf-step__label">
<span class="tf-step__num"><?php echo esc_html( sprintf( __( 'Phase %d', 'trufield-portal' ), $phase ) ); ?></span>
<span class="tf-step__name"><?php echo esc_html( $step_titles[ $phase ] ); ?></span>
<?php if ( $phase === 1 && $state === 'completed-pending' ) : ?>
<span class="tf-step__note"><?php esc_html_e( 'Needs remaining Phase 1 fields', 'trufield-portal' ); ?></span>
<?php elseif ( $phase === 1 ) : ?>
<span class="tf-step__note"><?php esc_html_e( 'Current form', 'trufield-portal' ); ?></span>
<?php elseif ( $phase > 1 ) : ?>
<span class="tf-step__note"><?php esc_html_e( 'Separate form — not available yet', 'trufield-portal' ); ?></span>
<?php endif; ?>
</div>
</div>
<?php if ( $phase < 3 ) : ?>
<div class="tf-step__connector" aria-hidden="true"></div>
<?php endif; ?>
<?php endforeach; ?>
</div>

<?php
$phase_titles = [
1 => __( 'Phase 1 — Trial Setup', 'trufield-portal' ),
2 => __( 'Phase 2 — Application & Monitoring', 'trufield-portal' ),
3 => __( 'Phase 3 — Harvest & Engagement', 'trufield-portal' ),
];

// Render the active Phase 1 form.
get_template_part(
'template-parts/phase-section',
null,
[
'post_id'        => $post_id,
'phase'          => 1,
'phase_title'    => $phase_titles[1],
'user_id'        => $user_id,
'is_admin'       => $is_admin,
'phase_verified' => $phase_verified,
]
);

// Phases 2 & 3 are not part of the current rollout — show informational placeholders.
foreach ( [ 2, 3 ] as $future_phase ) :
?>
<section class="tf-section tf-phase tf-phase--upcoming" id="<?php echo esc_attr( 'tf-phase-' . $future_phase ); ?>">
<div class="tf-phase__header">
<div class="tf-phase__title-row">
<h2 class="tf-phase__title"><?php echo esc_html( $phase_titles[ $future_phase ] ); ?></h2>
<span class="tf-phase__status tf-phase__status--upcoming"><?php esc_html_e( 'Future Form', 'trufield-portal' ); ?></span>
</div>
<p class="tf-phase__upcoming-note">
<?php esc_html_e( 'This is a separate form for a future workflow. It is not available during the current Phase 1 rollout, and no action is needed here right now.', 'trufield-portal' ); ?>
</p>
</div>
</section>
<?php endforeach; ?>
</div>
<?php get_footer(); ?>
