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
$farm_name          = get_post_meta( $post_id, 'farm_name', true );
$field_location     = get_post_meta( $post_id, 'field_location_address', true );
$phase_verified     = [];
$phase_statuses     = [];
$step_titles        = [
1 => __( 'Trial Setup', 'trufield-portal' ),
2 => __( 'Application & Monitoring', 'trufield-portal' ),
3 => __( 'Harvest & Engagement', 'trufield-portal' ),
];

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
echo esc_html( sprintf( __( 'Step %d submitted.', 'trufield-portal' ), (int) $matches[1] ) );
} else {
esc_html_e( 'Changes saved.', 'trufield-portal' );
}
?>
</div>
<?php endif; ?>

<?php if ( $error ) : ?>
<div class="tf-alert tf-alert--error" role="alert"><?php echo esc_html( $error ); ?></div>
<?php endif; ?>

<div class="tf-record-header">
<a href="<?php echo esc_url( trufield_dashboard_url() ); ?>" class="tf-back-link">&larr; <?php esc_html_e( 'Dashboard', 'trufield-portal' ); ?></a>
<div class="tf-record-header__title-row">
<h1><?php the_title(); ?></h1>
<span class="tf-status-badge tf-status-badge--<?php echo esc_attr( $record_status ); ?>"><?php echo esc_html( ucwords( str_replace( '_', ' ', $record_status ) ) ); ?></span>
</div>

<div class="tf-record-meta">
<?php if ( $assigned_rep ) : ?>
<span><strong><?php esc_html_e( 'Rep:', 'trufield-portal' ); ?></strong> <?php echo esc_html( $assigned_rep->display_name ); ?></span>
<?php endif; ?>
<?php if ( $retailer_name ) : ?>
<span><strong><?php esc_html_e( 'Retailer:', 'trufield-portal' ); ?></strong> <?php echo esc_html( $retailer_name ); ?></span>
<?php endif; ?>
<?php if ( $farm_name ) : ?>
<span><strong><?php esc_html_e( 'Farm:', 'trufield-portal' ); ?></strong> <?php echo esc_html( $farm_name ); ?></span>
<?php endif; ?>
<?php if ( $field_location ) : ?>
<span><strong><?php esc_html_e( 'Location:', 'trufield-portal' ); ?></strong> <?php echo esc_html( $field_location ); ?></span>
<?php endif; ?>
</div>
</div>

<div class="tf-steps" aria-label="<?php esc_attr_e( 'Progress', 'trufield-portal' ); ?>">
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
<span class="tf-step__num"><?php echo esc_html( sprintf( __( 'Step %d', 'trufield-portal' ), $phase ) ); ?></span>
<span class="tf-step__name"><?php echo esc_html( $step_titles[ $phase ] ); ?></span>
<?php if ( $phase === 1 && $state === 'completed-pending' ) : ?>
<span class="tf-step__note"><?php esc_html_e( 'Awaiting Admin Verification', 'trufield-portal' ); ?></span>
<?php elseif ( $phase > 1 ) : ?>
<span class="tf-step__note"><?php esc_html_e( 'Not yet active', 'trufield-portal' ); ?></span>
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
1 => __( 'Step 1 — Trial Setup', 'trufield-portal' ),
2 => __( 'Step 2 — Application & Monitoring', 'trufield-portal' ),
3 => __( 'Step 3 — Harvest & Engagement', 'trufield-portal' ),
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
<span class="tf-phase__status tf-phase__status--upcoming"><?php esc_html_e( 'Coming Soon', 'trufield-portal' ); ?></span>
</div>
<p class="tf-phase__upcoming-note">
<?php esc_html_e( 'This form is a separate submission that will be available in a future rollout phase. No action is needed here right now.', 'trufield-portal' ); ?>
</p>
</div>
</section>
<?php endforeach; ?>
</div>
<?php get_footer(); ?>
