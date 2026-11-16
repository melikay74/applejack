<?php
/**
 * Connects the horse page to its template file and stylesheet.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * When WordPress is about to show a single horse, hand it our template
 * instead of the theme's blog-post one. A single-horse.php file in the
 * theme folder would take priority, if one is ever added.
 */
function ajr_single_horse_template( $template ) {
	if ( ! is_singular( 'horse' ) ) {
		return $template;
	}

	$theme_template = locate_template( 'single-horse.php' );
	return $theme_template ? $theme_template : AJR_HORSES_DIR . 'templates/single-horse.php';
}
add_filter( 'single_template', 'ajr_single_horse_template' );

/** Load each stylesheet only on the pages that need it. */
function ajr_enqueue_horse_assets() {
	if ( is_singular( 'horse' ) ) {
		wp_enqueue_style(
			'ajr-horse',
			AJR_HORSES_URL . 'assets/css/horse.css',
			array(),
			filemtime( AJR_HORSES_DIR . 'assets/css/horse.css' ) // New version number whenever the file changes.
		);
	}

	// Registered here, switched on by the [horse_search] shortcode when it runs.
	wp_register_style(
		'ajr-horse-search',
		AJR_HORSES_URL . 'assets/css/horse-search.css',
		array(),
		filemtime( AJR_HORSES_DIR . 'assets/css/horse-search.css' )
	);

	// If we can tell early that the page uses the shortcode, load the styles
	// in the page head so the grid never appears unstyled for a moment.
	if ( ajr_page_has_horse_search() ) {
		wp_enqueue_style( 'ajr-horse-search' );
	}
}
add_action( 'wp_enqueue_scripts', 'ajr_enqueue_horse_assets' );

/** True if the current page's content, or its Elementor layout, contains [horse_search]. */
function ajr_page_has_horse_search() {
	$post = is_singular() ? get_queried_object() : null;
	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	return has_shortcode( $post->post_content, 'horse_search' )
		|| str_contains( (string) get_post_meta( $post->ID, '_elementor_data', true ), '[horse_search' );
}

/** Astra: horse pages are full width, with no blog sidebar. */
function ajr_horse_page_layout( $layout ) {
	return is_singular( 'horse' ) ? 'no-sidebar' : $layout;
}
add_filter( 'astra_page_layout', 'ajr_horse_page_layout' );
