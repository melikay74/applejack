<?php
/**
 * The page for one horse: /horses/horse-name/
 *
 * FIRST DRAFT. This prints every piece of a horse's data in plain markup so
 * the data flow can be checked end to end. The real layout, the photo
 * carousel, click-to-load videos and the sire/dam cards come later.
 *
 * get_header() and get_footer() pull in the site's normal header and footer,
 * and the #primary wrapper is what Astra expects around page content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div id="primary" <?php function_exists( 'astra_primary_class' ) ? astra_primary_class() : print 'class="content-area"'; ?>>
	<main id="main" class="site-main">

		<?php
		while ( have_posts() ) :
			the_post();

			$horse_id  = get_the_ID();
			$photo_ids = ajr_horse_photo_ids( $horse_id );
			$facts     = ajr_horse_facts( $horse_id );
			$videos    = ajr_horse_videos( $horse_id );
			$links     = ajr_horse_social_links( $horse_id );
			$offspring = ajr_horse_offspring_ids( $horse_id );
			$parents   = array_filter(
				array(
					'Sire' => ajr_horse_parent_id( $horse_id, 'sire' ),
					'Dam'  => ajr_horse_parent_id( $horse_id, 'dam' ),
				)
			);
			?>

			<article id="horse-<?php echo esc_attr( $horse_id ); ?>" class="ajr-horse">

				<header class="ajr-horse__header">
					<h1 class="ajr-horse__name"><?php the_title(); ?></h1>
				</header>

				<?php if ( $photo_ids ) : ?>
					<div class="ajr-horse__photos">
						<?php foreach ( $photo_ids as $photo_id ) : ?>
							<?php echo wp_get_attachment_image( $photo_id, 'large', false, array( 'class' => 'ajr-horse__photo' ) ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( $facts ) : ?>
					<dl class="ajr-horse__facts">
						<?php foreach ( $facts as $label => $value ) : ?>
							<div class="ajr-horse__fact">
								<dt><?php echo esc_html( $label ); ?></dt>
								<dd><?php echo esc_html( $value ); ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>

				<?php if ( ajr_field( 'description', $horse_id ) ) : ?>
					<div class="ajr-horse__description">
						<?php echo wp_kses_post( ajr_field( 'description', $horse_id ) ); ?>
					</div>
				<?php endif; ?>

				<p class="ajr-horse__cta">
					<a class="ajr-horse__button" href="<?php echo esc_url( ajr_request_info_url( $horse_id ) ); ?>">Request Information</a>
				</p>

				<?php if ( $videos ) : ?>
					<section class="ajr-horse__section ajr-horse__videos">
						<h2>Videos</h2>
						<?php foreach ( $videos as $video ) : ?>
							<div class="ajr-horse__video">
								<?php
								// Placeholder: a normal YouTube embed. Click-to-load players replace this later.
								$embed = wp_oembed_get( $video['url'] );
								echo $embed ? $embed : '<a href="' . esc_url( $video['url'] ) . '">' . esc_html( $video['url'] ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
								?>
							</div>
						<?php endforeach; ?>
					</section>
				<?php endif; ?>

				<?php if ( $parents ) : ?>
					<section class="ajr-horse__section ajr-horse__parents">
						<h2>Sire &amp; Dam</h2>
						<ul>
							<?php foreach ( $parents as $label => $parent_id ) : ?>
								<li>
									<?php echo esc_html( $label ); ?>:
									<a href="<?php echo esc_url( get_permalink( $parent_id ) ); ?>"><?php echo esc_html( get_the_title( $parent_id ) ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

				<?php if ( $offspring ) : ?>
					<section class="ajr-horse__section ajr-horse__offspring">
						<h2>Offspring</h2>
						<ul>
							<?php foreach ( $offspring as $foal_id ) : ?>
								<li><a href="<?php echo esc_url( get_permalink( $foal_id ) ); ?>"><?php echo esc_html( get_the_title( $foal_id ) ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

				<?php if ( $links ) : ?>
					<section class="ajr-horse__section ajr-horse__social">
						<h2>Follow <?php the_title(); ?></h2>
						<ul>
							<?php foreach ( $links as $link ) : ?>
								<li><a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $link['label'] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

			</article>

		<?php endwhile; ?>

	</main>
</div>

<?php
get_footer();
