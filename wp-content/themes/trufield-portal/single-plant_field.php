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

$assigned_rep_id = (int) get_post_meta( $post_id, 'assigned_sales_rep', true );
$assigned_rep    = $assigned_rep_id ? get_userdata( $assigned_rep_id ) : false;
$record_status   = get_post_meta( $post_id, 'record_status', true ) ?: 'active';
$retailer_name   = get_post_meta( $post_id, 'retailer_name', true );
$retailer_contact = get_post_meta( $post_id, 'retailer_key_contact', true );
$rsm_bam         = get_post_meta( $post_id, 'rsm_bam', true );
$fsa             = get_post_meta( $post_id, 'fsa', true );
$import_city     = get_post_meta( $post_id, 'import_city', true );
$import_state    = get_post_meta( $post_id, 'import_state', true );
$farm_name       = get_post_meta( $post_id, 'farm_name', true );
$field_location  = get_post_meta( $post_id, 'field_location_address', true );
$active_phases   = array_values( array_intersect( [ 1, 2, 3 ], TRUFIELD_ACTIVE_PHASES ) );
$phase_verified  = [];
$phase_statuses  = [];
$step_titles     = [
	1 => __( 'Phase 1 Setup', 'trufield-portal' ),
	2 => __( 'Application & Monitoring', 'trufield-portal' ),
	3 => __( 'Harvest & Engagement', 'trufield-portal' ),
];
$phase_titles = [
	1 => __( 'Trial Setup - Phase 1', 'trufield-portal' ),
	2 => __( 'Trial Setup - Phase 2', 'trufield-portal' ),
	3 => __( 'Trial Setup - Phase 3', 'trufield-portal' ),
];

foreach ( [ 1, 2, 3 ] as $phase ) {
	$phase_verified[ $phase ] = (bool) get_post_meta( $post_id, "phase_{$phase}_verified", true );
	$phase_statuses[ $phase ] = trufield_get_phase_status( $post_id, $phase );
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
$current_phase_missing  = trufield_get_missing_required_fields( $post_id, $current_phase );
$current_phase_can_edit = trufield_can_edit_phase( $post_id, $current_phase, $user_id );

if ( 2 === $current_phase ) {
	if ( $current_phase_verified ) {
		$phase_panel_title = __( 'Phase 2 verified', 'trufield-portal' );
		$phase_panel_copy  = __( 'The Phase 2 visit documentation and field observations have been verified by the admin team.', 'trufield-portal' );
		$phase_panel_note  = __( 'No additional Phase 2 updates are needed right now.', 'trufield-portal' );
	} elseif ( $current_phase_status === 'completed' ) {
		$phase_panel_title = __( 'Phase 2 submitted', 'trufield-portal' );
		$phase_panel_copy  = __( 'Phase 2 has been submitted and is read-only while the admin team reviews it.', 'trufield-portal' );
		$phase_panel_note  = __( 'If anything needs to change, the admin team can reopen this phase.', 'trufield-portal' );
	} elseif ( $current_phase_status === 'in_progress' ) {
		$phase_panel_title = __( 'Continue Phase 2', 'trufield-portal' );
		$phase_panel_copy  = __( 'Finish the visit dates, upload the required documentation, and capture the field observations below.', 'trufield-portal' );
		$phase_panel_note  = empty( $current_phase_missing )
			? __( 'Every required Phase 2 field is ready. Submit the form when you are ready for admin verification.', 'trufield-portal' )
			: sprintf(
				_n(
					'%d required Phase 2 field still needs attention before this phase can be submitted.',
					'%d required Phase 2 fields still need attention before this phase can be submitted.',
					count( $current_phase_missing ),
					'trufield-portal'
				),
				count( $current_phase_missing )
			);
	} else {
		$phase_panel_title = __( 'Start Phase 2', 'trufield-portal' );
		$phase_panel_copy  = __( 'Phase 1 is verified, so this trial is ready for the Phase 2 visit and observation workflow.', 'trufield-portal' );
		$phase_panel_note  = $current_phase_can_edit
			? __( 'Complete the two Phase 2 sections below and submit when all required details are ready.', 'trufield-portal' )
			: __( 'If this record should be editable for your Phase 2 work, contact the admin team.', 'trufield-portal' );
	}
} else {
	$phase_1_validation_missing = trufield_get_missing_validation_fields( $post_id, 1 );
	$phase_1_validation_ok      = empty( $phase_1_validation_missing );

	if ( $current_phase_verified ) {
		$phase_panel_title = __( 'Phase 1 verified', 'trufield-portal' );
		$phase_panel_copy  = __( 'The required Phase 1 details are complete and this record now counts as 1 valid grower entry.', 'trufield-portal' );
		$phase_panel_note  = __( 'Phase 2 is now the next active workflow for this trial.', 'trufield-portal' );
	} elseif ( $current_phase_status === 'completed' ) {
		$phase_panel_title = __( 'Phase 1 needs final details', 'trufield-portal' );
		$phase_panel_copy  = __( 'This record will count as a valid grower entry after the remaining required Phase 1 details are filled in and saved.', 'trufield-portal' );
		$phase_panel_note  = __( 'Finish the remaining Phase 1 fields below.', 'trufield-portal' );
	} elseif ( $current_phase_status === 'in_progress' && $phase_1_validation_ok ) {
		$phase_panel_title = __( 'Ready to count as a valid entry', 'trufield-portal' );
		$phase_panel_copy  = __( 'Every required Phase 1 field is present. Save once more if needed and this record will count as 1 valid grower entry.', 'trufield-portal' );
		$phase_panel_note  = __( 'You can still save your draft and return later if needed.', 'trufield-portal' );
	} elseif ( $current_phase_status === 'in_progress' ) {
		$phase_panel_title = __( 'Phase 1 draft in progress', 'trufield-portal' );
		$phase_panel_copy  = __( 'Continue filling in the Phase 1 details for this assigned record. Save progress anytime and return later.', 'trufield-portal' );
		$phase_panel_note  = sprintf(
			_n(
				'%d required Phase 1 field still needs attention before this record counts as a valid grower entry.',
				'%d required Phase 1 fields still need attention before this record counts as a valid grower entry.',
				count( $phase_1_validation_missing ),
				'trufield-portal'
			),
			count( $phase_1_validation_missing )
		);
	} else {
		$phase_panel_title = __( 'Start Phase 1', 'trufield-portal' );
		$phase_panel_copy  = __( 'Fill out the required Phase 1 fields below for this record to count as 1 valid grower entry.', 'trufield-portal' );
		$phase_panel_note  = $current_phase_can_edit
			? __( 'Optional details can be added before submission.', 'trufield-portal' )
			: __( 'If this record should be assigned to you for Phase 1 work, contact the admin team.', 'trufield-portal' );
	}
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
	echo esc_html( sprintf( __( 'Phase %d progress saved.', 'trufield-portal' ), $current_phase ) );
}
?>
</div>
<?php endif; ?>

<?php if ( $error ) : ?>
<div class="tf-alert tf-alert--error" role="alert"><?php echo esc_html( $error ); ?></div>
<?php endif; ?>

<div class="tf-record-header">
<a href="<?php echo esc_url( trufield_dashboard_url() ); ?>" class="tf-back-link">&larr; <?php esc_html_e( 'Dashboard', 'trufield-portal' ); ?></a>
<p class="tf-record-header__eyebrow"><?php esc_html_e( 'Active workflow', 'trufield-portal' ); ?></p>
<div class="tf-record-header__title-row">
<h1><?php echo esc_html( $phase_titles[ $current_phase ] ?? get_the_title() ); ?></h1>
<span class="tf-status-badge tf-status-badge--<?php echo esc_attr( $record_status ); ?>"><?php echo esc_html( ucwords( str_replace( '_', ' ', $record_status ) ) ); ?></span>
</div>
<p class="tf-record-header__support"><?php echo esc_html( 2 === $current_phase ? __( 'This trial is now in the Phase 2 workflow. Complete the two sections below and submit the phase when every required detail is ready.', 'trufield-portal' ) : __( 'This trial is currently in the Phase 1 workflow. Complete the active form below to move the trial forward.', 'trufield-portal' ) ); ?></p>

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
<h2 class="tf-phase-status-panel__title"><?php echo esc_html( $phase_panel_title ); ?></h2>
<p class="tf-phase-status-panel__copy"><?php echo esc_html( $phase_panel_copy ); ?></p>
<p class="tf-phase-status-panel__note"><?php echo esc_html( $phase_panel_note ); ?></p>
</div>
</div>

<div class="tf-steps" aria-label="<?php esc_attr_e( 'Phase timeline', 'trufield-portal' ); ?>">
<?php foreach ( [ 1, 2, 3 ] as $phase ) : ?>
<?php
if ( ! in_array( $phase, $active_phases, true ) ) {
	$state = 'upcoming';
	$icon  = '–';
	$note  = __( 'Future form', 'trufield-portal' );
} elseif ( ! trufield_prerequisite_met( $post_id, $phase ) ) {
	$state = 'upcoming';
	$icon  = '–';
	$note  = __( 'Locked until previous phase is verified', 'trufield-portal' );
} else {
	$verified = $phase_verified[ $phase ] ?? false;
	$status   = $phase_statuses[ $phase ] ?? 'pending';
	if ( $verified ) {
		$state = 'completed-verified';
		$icon  = '✓';
		$note  = __( 'Verified', 'trufield-portal' );
	} elseif ( $status === 'completed' ) {
		$state = 'completed-pending';
		$icon  = '✓';
		$note  = __( 'Submitted', 'trufield-portal' );
	} elseif ( $phase === $current_phase || $status === 'in_progress' ) {
		$state = 'active';
		$icon  = (string) $phase;
		$note  = __( 'Current form', 'trufield-portal' );
	} else {
		$state = 'upcoming';
		$icon  = (string) $phase;
		$note  = __( 'Not started yet', 'trufield-portal' );
	}
}
?>
<div class="tf-step tf-step--<?php echo esc_attr( $state ); ?>" data-step="<?php echo (int) $phase; ?>">
<div class="tf-step__circle"><?php echo esc_html( $icon ); ?></div>
<div class="tf-step__label">
<span class="tf-step__num"><?php echo esc_html( sprintf( __( 'Phase %d', 'trufield-portal' ), $phase ) ); ?></span>
<span class="tf-step__name"><?php echo esc_html( $step_titles[ $phase ] ); ?></span>
<span class="tf-step__note"><?php echo esc_html( $note ); ?></span>
</div>
</div>
<?php if ( $phase < 3 ) : ?>
<div class="tf-step__connector" aria-hidden="true"></div>
<?php endif; ?>
<?php endforeach; ?>
</div>

<?php
get_template_part(
'template-parts/phase-section',
null,
[
'post_id'        => $post_id,
'phase'          => $current_phase,
'phase_title'    => $phase_titles[ $current_phase ],
'user_id'        => $user_id,
'is_admin'       => $is_admin,
'phase_verified' => $phase_verified,
]
);
?>
</div>
<?php get_footer(); ?>
