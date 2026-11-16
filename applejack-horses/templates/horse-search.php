<?php
/**
 * Markup for the [horse_search] shortcode: the form, then the grid of cards.
 *
 * FIRST DRAFT, plain on purpose. The card itself is in horse-card.php.
 *
 * Available here (set up in includes/search.php):
 *   $request      what the visitor picked, e.g. [ 'q' => '', 'sex' => 'mare', ... ]
 *   $filters      filters to show: key => [ 'label' => ..., 'options' => [ value => label ] ]
 *   $show_search  whether to show the search-by-name box
 *   $horse_ids    IDs of the horses to list
 *   $is_filtered  true if a search or filter is active
 *   $page_url     address of the page the shortcode is on
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$count = count( $horse_ids );
?>

<div class="ajr-search" id="horse-search">

	<?php if ( $show_search || $filters ) : ?>
		<form class="ajr-search__form" method="get" action="<?php echo esc_url( $page_url ); ?>#horse-search" role="search">

			<?php if ( $show_search ) : ?>
				<p class="ajr-search__field ajr-search__field--q">
					<label for="hs_q">Search by name</label>
					<input type="search" id="hs_q" name="hs_q" value="<?php echo esc_attr( $request['q'] ); ?>">
				</p>
			<?php endif; ?>

			<?php foreach ( $filters as $filter => $details ) : ?>
				<p class="ajr-search__field">
					<label for="hs_<?php echo esc_attr( $filter ); ?>"><?php echo esc_html( $details['label'] ); ?></label>
					<select id="hs_<?php echo esc_attr( $filter ); ?>" name="hs_<?php echo esc_attr( $filter ); ?>">
						<option value="">Any</option>
						<?php foreach ( $details['options'] as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $request[ $filter ], (string) $value ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>
			<?php endforeach; ?>

			<p class="ajr-search__actions">
				<button type="submit" class="ajr-search__submit">Search</button>
				<?php if ( $is_filtered ) : ?>
					<a class="ajr-search__clear" href="<?php echo esc_url( $page_url ); ?>#horse-search">Clear</a>
				<?php endif; ?>
			</p>

		</form>
	<?php endif; ?>

	<p class="ajr-search__count" aria-live="polite">
		<?php
		if ( 0 === $count ) {
			echo $is_filtered ? 'No horses match. Try removing a filter.' : 'No horses listed yet.';
		} else {
			echo esc_html( sprintf( _n( '%d horse', '%d horses', $count, 'applejack-horses' ), $count ) );
		}
		?>
	</p>

	<?php if ( $horse_ids ) : ?>
		<ul class="ajr-search__results">
			<?php foreach ( $horse_ids as $horse_id ) : ?>
				<li class="ajr-search__result">
					<?php include AJR_HORSES_DIR . 'templates/horse-card.php'; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

</div>
