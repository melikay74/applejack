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

/** Load the horse stylesheet only on horse pages. */
function ajr_enqueue_horse_assets() {
	if ( is_singular( 'horse' ) ) {
		wp_enqueue_style(
			'ajr-horse',
			AJR_HORSES_URL . 'assets/css/horse.css',
			array(),
			filemtime( AJR_HORSES_DIR . 'assets/css/horse.css' ) // New version number whenever the file changes.
		);
	}
}
add_action( 'wp_enqueue_scripts', 'ajr_enqueue_horse_assets' );

/** Astra: horse pages are full width, with no blog sidebar. */
function ajr_horse_page_layout( $layout ) {
	return is_singular( 'horse' ) ? 'no-sidebar' : $layout;
}
add_filter( 'astra_page_layout', 'ajr_horse_page_layout' );
