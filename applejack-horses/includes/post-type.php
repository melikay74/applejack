<?php
/**
 * The Horse record type and the lists it is sorted by.
 *
 * A "post type" is a kind of record. WordPress ships with Posts and Pages;
 * this adds Horses, with its own menu in the dashboard and its own page
 * address (/horses/horse-name/) for every record.
 *
 * A "taxonomy" is a list of labels a record can be tagged with. Breed, color
 * and discipline are taxonomies so the client can add a new value herself
 * from the horse form, and so the search filters always match exactly.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ajr_register_horse_post_type() {
	register_post_type(
		'horse',
		array(
			'labels'       => array(
				'name'               => 'Horses',
				'singular_name'      => 'Horse',
				'add_new'            => 'Add Horse',
				'add_new_item'       => 'Add Horse',
				'edit_item'          => 'Edit Horse',
				'new_item'           => 'New Horse',
				'view_item'          => 'View Horse',
				'search_items'       => 'Search Horses',
				'not_found'          => 'No horses found',
				'not_found_in_trash' => 'No horses found in Trash',
				'all_items'          => 'All Horses',
				'menu_name'          => 'Horses',
			),
			'public'       => true,
			'show_in_rest' => false, // Keeps the simple form screen instead of the block editor.
			'has_archive'  => false, // Listings come from the search shortcode, not an automatic archive.
			'rewrite'      => array(
				'slug'       => 'horses',
				'with_front' => false,
			),
			'menu_icon'    => 'dashicons-pets',
			'menu_position' => 20,
			// No 'editor': the description is a field in the form, so everything is entered in one place.
			'supports'     => array( 'title', 'thumbnail', 'revisions' ),
		)
	);
}
add_action( 'init', 'ajr_register_horse_post_type' );

function ajr_register_horse_taxonomies() {
	$taxonomies = array(
		// key => [ plural, singular, address slug ]
		'horse_category'   => array( 'Page Categories', 'Page Category', 'horse-category' ),
		'horse_breed'      => array( 'Breeds', 'Breed', 'horse-breed' ),
		'horse_color'      => array( 'Colors', 'Color', 'horse-color' ),
		'horse_discipline' => array( 'Disciplines', 'Discipline', 'horse-discipline' ),
	);

	foreach ( $taxonomies as $key => $names ) {
		list( $plural, $singular, $slug ) = $names;

		register_taxonomy(
			$key,
			'horse',
			array(
				'labels'            => array(
					'name'          => $plural,
					'singular_name' => $singular,
					'menu_name'     => $plural,
					'all_items'     => 'All ' . $plural,
					'edit_item'     => 'Edit ' . $singular,
					'add_new_item'  => 'Add ' . $singular,
					'search_items'  => 'Search ' . $plural,
				),
				'hierarchical'      => true,
				'public'            => false, // No public "all bay horses" pages; the search handles that.
				'show_ui'           => true,  // Still manageable under the Horses menu.
				'show_admin_column' => true,  // Shows as a column in the All Horses list.
				'show_in_rest'      => false,
				'meta_box_cb'       => false, // Hidden on the form: the Horse Details fields set these instead.
				'rewrite'           => false,
			)
		);
	}
}
add_action( 'init', 'ajr_register_horse_taxonomies' );

/**
 * The three page categories match the site's three tabs. A horse can be in
 * more than one.
 */
function ajr_create_default_terms() {
	$categories = array(
		'new-foals'        => 'New Foals',
		'available-horses' => 'Available Horses',
		'notables'         => 'Broodmares, Stallions, and Other Notables',
	);

	foreach ( $categories as $slug => $name ) {
		if ( ! term_exists( $slug, 'horse_category' ) ) {
			wp_insert_term( $name, 'horse_category', array( 'slug' => $slug ) );
		}
	}
}

/**
 * Form tidy-ups: prompt for the horse's name in the title box, and hide the
 * separate Featured Image box (the first gallery photo is used automatically).
 */
function ajr_horse_title_placeholder( $text, $post ) {
	return 'horse' === $post->post_type ? 'Horse name' : $text;
}
add_filter( 'enter_title_here', 'ajr_horse_title_placeholder', 10, 2 );

function ajr_remove_featured_image_box() {
	remove_meta_box( 'postimagediv', 'horse', 'side' );
}
add_action( 'add_meta_boxes_horse', 'ajr_remove_featured_image_box' );
