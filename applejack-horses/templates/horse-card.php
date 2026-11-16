<?php
/**
 * One horse in the search results. The whole card links to the horse's page.
 *
 * FIRST DRAFT, plain on purpose: photo, name, one line of facts, status.
 *
 * Available here: $horse_id
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$foal_date = (string) ajr_field( 'foal_date', $horse_id ); // 20260412

// The short line under the name. Remove or reorder entries to change it.
$summary = array_filter(
	array(
		ajr_horse_choice_label( $horse_id, 'sex' ),
		substr( $foal_date, 0, 4 ),
		implode( ', ', ajr_horse_term_names( $horse_id, 'horse_breed' ) ),
		implode( ', ', ajr_horse_term_names( $horse_id, 'horse_color' ) ),
		ajr_horse_height( $horse_id ),
	)
);

$status = ajr_horse_choice_label( $horse_id, 'status' );
?>

<a class="ajr-card" href="<?php echo esc_url( get_permalink( $horse_id ) ); ?>">

	<span class="ajr-card__photo">
		<?php
		if ( has_post_thumbnail( $horse_id ) ) {
			echo get_the_post_thumbnail( $horse_id, 'medium_large', array( 'loading' => 'lazy' ) );
		}
		?>
	</span>

	<span class="ajr-card__body">
		<span class="ajr-card__name"><?php echo esc_html( get_the_title( $horse_id ) ); ?></span>

		<?php if ( $summary ) : ?>
			<span class="ajr-card__summary"><?php echo esc_html( implode( ' · ', $summary ) ); ?></span>
		<?php endif; ?>

		<?php if ( $status ) : ?>
			<span class="ajr-card__status"><?php echo esc_html( $status ); ?></span>
		<?php endif; ?>
	</span>

</a>
