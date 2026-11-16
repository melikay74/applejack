<?php
/**
 * The [horse_search] shortcode: a search box, filters and a grid of horses.
 *
 * Put it on any page (in Elementor, with the Shortcode widget):
 *
 *   [horse_search]                                         every horse
 *   [horse_search category="new-foals" orderby="newest"]   one tab's horses, youngest first
 *   [horse_search category="notables" filters="sex,breed"] only some filters
 *   [horse_search category="new-foals" filters="none" search="no"]   just the grid
 *
 * category: new-foals, available-horses or notables (several allowed, comma separated).
 * orderby:  name (default) or newest.
 * filters:  any of sex, year, breed, color, height, discipline, status; or none.
 * search:   yes (default) or no, for the search-by-name box.
 *
 * The form sends its choices in the page address (?hs_sex=mare&hs_color=bay),
 * so a filtered view can be bookmarked or shared, and it works without JavaScript.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Height filter choices. Heights are in hands, so .3 is the highest decimal. */
const AJR_HEIGHT_RANGES = array(
	'under-14-2' => array( 'label' => 'Under 14.2 hh', 'min' => 0, 'max' => 14.1 ),
	'14-2-15-3'  => array( 'label' => '14.2 to 15.3 hh', 'min' => 14.2, 'max' => 15.3 ),
	'16-0-16-3'  => array( 'label' => '16.0 to 16.3 hh', 'min' => 16.0, 'max' => 16.3 ),
	'17-0-up'    => array( 'label' => '17.0 hh and over', 'min' => 17.0, 'max' => 99 ),
);

/** The seven filters, in display order: key => label. */
function ajr_search_filter_labels() {
	return array(
		'sex'        => 'Sex',
		'year'       => 'Foal Year',
		'breed'      => 'Breed or Cross',
		'color'      => 'Color',
		'height'     => 'Height',
		'discipline' => 'Discipline',
		'status'     => 'Status',
	);
}

/** What the visitor asked for, read from the page address and cleaned. */
function ajr_search_request() {
	$request = array();

	foreach ( array_merge( array( 'q' ), array_keys( ajr_search_filter_labels() ) ) as $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a public, read-only search.
		$raw             = isset( $_GET[ 'hs_' . $key ] ) ? wp_unslash( $_GET[ 'hs_' . $key ] ) : '';
		$request[ $key ] = is_string( $raw ) ? sanitize_text_field( $raw ) : '';
	}

	return $request;
}

/** The query for "every horse this shortcode covers", before any filtering. */
function ajr_search_base_args( $atts ) {
	$args = array(
		'post_type'      => 'horse',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'tax_query'      => array(),
		'meta_query'     => array(),
	);

	$categories = array_filter( array_map( 'sanitize_title', explode( ',', $atts['category'] ) ) );
	if ( $categories ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'horse_category',
			'field'    => 'slug',
			'terms'    => $categories,
		);
	}

	if ( 'newest' === $atts['orderby'] ) {
		// Foal dates are stored as 20260412, so sorting them as text is also sorting by date.
		$args['meta_key'] = 'foal_date';
		$args['orderby']  = array(
			'meta_value' => 'DESC',
			'title'      => 'ASC',
		);
	}

	return $args;
}

/**
 * The choices to offer for one filter, as value => label. Only values that
 * at least one horse on this page actually has, so no choice leads nowhere.
 */
function ajr_search_filter_options( $filter, $scope_ids ) {
	if ( ! $scope_ids ) {
		return array();
	}

	switch ( $filter ) {
		case 'sex':
		case 'status':
			$field   = function_exists( 'acf_get_field' ) ? acf_get_field( 'field_ajr_' . $filter ) : null;
			$choices = isset( $field['choices'] ) ? $field['choices'] : array();
			$in_use  = array();
			foreach ( $scope_ids as $id ) {
				$in_use[ (string) get_post_meta( $id, $filter, true ) ] = true;
			}
			return array_intersect_key( $choices, $in_use );

		case 'year':
			$years = array();
			foreach ( $scope_ids as $id ) {
				$year = substr( (string) get_post_meta( $id, 'foal_date', true ), 0, 4 );
				if ( $year ) {
					$years[ $year ] = $year;
				}
			}
			krsort( $years );
			return $years;

		case 'breed':
		case 'color':
		case 'discipline':
			$terms = get_terms(
				array(
					'taxonomy'   => 'horse_' . $filter,
					'object_ids' => $scope_ids,
					'orderby'    => 'name',
				)
			);
			return is_wp_error( $terms ) ? array() : wp_list_pluck( $terms, 'name', 'slug' );

		case 'height':
			$ranges = array();
			foreach ( AJR_HEIGHT_RANGES as $key => $range ) {
				foreach ( $scope_ids as $id ) {
					$height = (float) get_post_meta( $id, 'height', true );
					if ( $height && $height >= $range['min'] && $height <= $range['max'] ) {
						$ranges[ $key ] = $range['label'];
						break;
					}
				}
			}
			return $ranges;
	}

	return array();
}

/** Narrows the base query by whatever the visitor picked. */
function ajr_search_apply_request( $args, $request, $filters ) {
	if ( '' !== $request['q'] ) {
		$args['s'] = $request['q']; // Matches the horse's name.
	}

	foreach ( $filters as $filter => $options ) {
		$value = $request[ $filter ];

		// Ignore anything that isn't one of the offered choices.
		if ( '' === $value || ! isset( $options[ $value ] ) ) {
			continue;
		}

		switch ( $filter ) {
			case 'sex':
			case 'status':
				$args['meta_query'][] = array(
					'key'   => $filter,
					'value' => $value,
				);
				break;

			case 'year':
				$args['meta_query'][] = array(
					'key'     => 'foal_date',
					'value'   => array( $value . '0101', $value . '1231' ),
					'compare' => 'BETWEEN',
					'type'    => 'NUMERIC',
				);
				break;

			case 'breed':
			case 'color':
			case 'discipline':
				$args['tax_query'][] = array(
					'taxonomy' => 'horse_' . $filter,
					'field'    => 'slug',
					'terms'    => $value,
				);
				break;

			case 'height':
				$range                = AJR_HEIGHT_RANGES[ $value ];
				$args['meta_query'][] = array(
					'key'     => 'height',
					'value'   => array( $range['min'], $range['max'] ),
					'compare' => 'BETWEEN',
					'type'    => 'DECIMAL(4,1)',
				);
				break;
		}
	}

	return $args;
}

/** The shortcode itself: works out the results, then hands over to the template. */
function ajr_horse_search_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'category' => '',
			'orderby'  => 'name',
			'filters'  => implode( ',', array_keys( ajr_search_filter_labels() ) ),
			'search'   => 'yes',
		),
		$atts,
		'horse_search'
	);

	$request   = ajr_search_request();
	$base_args = ajr_search_base_args( $atts );

	// Every horse this shortcode covers, used to work out the filter choices.
	$scope_ids = get_posts( array_merge( $base_args, array( 'fields' => 'ids' ) ) );
	update_meta_cache( 'post', $scope_ids ); // One database trip for all their details.

	// Filters to show: key => [ 'label' => ..., 'options' => [ value => label ] ].
	$wanted  = array_map( 'trim', explode( ',', strtolower( $atts['filters'] ) ) );
	$filters = array();
	$options = array();
	foreach ( ajr_search_filter_labels() as $filter => $label ) {
		if ( ! in_array( $filter, $wanted, true ) ) {
			continue;
		}
		$choices = ajr_search_filter_options( $filter, $scope_ids );
		if ( $choices ) {
			$options[ $filter ] = $choices;
			$filters[ $filter ] = array(
				'label'   => $label,
				'options' => $choices,
			);
		}
	}

	$show_search = 'no' !== strtolower( $atts['search'] );
	if ( ! $show_search ) {
		$request['q'] = '';
	}

	$is_filtered = (bool) array_filter( $request );
	$horse_ids   = $is_filtered
		? get_posts( array_merge( ajr_search_apply_request( $base_args, $request, $options ), array( 'fields' => 'ids' ) ) )
		: $scope_ids;

	$page_url = get_permalink( get_queried_object_id() );
	$page_url = $page_url ? $page_url : home_url( '/' );

	wp_enqueue_style( 'ajr-horse-search' );

	ob_start();
	include AJR_HORSES_DIR . 'templates/horse-search.php';
	return ob_get_clean();
}
add_shortcode( 'horse_search', 'ajr_horse_search_shortcode' );
