<?php
/**
 * Small functions the templates use to read a horse's data.
 *
 * Templates call these instead of reading fields directly, so the page
 * layout files stay short and the data rules live in one place.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** The contact form the Request Information button points at. */
const AJR_CONTACT_PAGE_PATH    = 'home/contact';
const AJR_CONTACT_FORM_ID      = 12; // WPForms "Contact Form".
const AJR_CONTACT_MESSAGE_FIELD = 4;  // Its "Message" field.

/** Reads one SCF field, or returns null if SCF is switched off. */
function ajr_field( $name, $post_id, $format = true ) {
	return function_exists( 'get_field' ) ? get_field( $name, $post_id, $format ) : null;
}

/** Photo attachment IDs, in the order set in the gallery. */
function ajr_horse_photo_ids( $post_id ) {
	$ids = ajr_field( 'photos', $post_id, false );
	return $ids ? array_values( array_filter( array_map( 'intval', (array) $ids ) ) ) : array();
}

/** Names of the terms a horse has in one taxonomy, e.g. its colors. */
function ajr_horse_term_names( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	return ( $terms && ! is_wp_error( $terms ) ) ? wp_list_pluck( $terms, 'name' ) : array();
}

/** The label of a dropdown field such as sex or status ("Sale Pending"). */
function ajr_horse_choice_label( $post_id, $field_name ) {
	$choice = ajr_field( $field_name, $post_id );
	return is_array( $choice ) && isset( $choice['label'] ) ? $choice['label'] : '';
}

/** Height as text: 16.2 becomes "16.2 hh". */
function ajr_horse_height( $post_id ) {
	$height = ajr_field( 'height', $post_id );
	return $height ? number_format( (float) $height, 1 ) . ' hh' : '';
}

/** Foal date as text: "April 12, 2025". */
function ajr_horse_foal_date( $post_id ) {
	$raw  = ajr_field( 'foal_date', $post_id ); // Stored as 20250412.
	$date = $raw ? DateTime::createFromFormat( 'Ymd', $raw ) : false;
	return $date ? $date->format( 'F j, Y' ) : '';
}

/**
 * The facts shown for a horse, as label => value, skipping anything empty.
 * Change the order here to change it everywhere.
 */
function ajr_horse_facts( $post_id ) {
	$facts = array(
		'Sex'            => ajr_horse_choice_label( $post_id, 'sex' ),
		'Foaled'         => ajr_horse_foal_date( $post_id ),
		'Breed or Cross' => implode( ', ', ajr_horse_term_names( $post_id, 'horse_breed' ) ),
		'Color'          => implode( ', ', ajr_horse_term_names( $post_id, 'horse_color' ) ),
		'Height'         => ajr_horse_height( $post_id ),
		'Discipline'     => implode( ', ', ajr_horse_term_names( $post_id, 'horse_discipline' ) ),
		'Status'         => ajr_horse_choice_label( $post_id, 'status' ),
	);

	return array_filter( $facts );
}

/** The sire's or dam's horse ID, or 0 if not set or not published. */
function ajr_horse_parent_id( $post_id, $which ) {
	$parent_id = (int) ajr_field( $which, $post_id, false ); // 'sire' or 'dam'.
	return ( $parent_id && 'publish' === get_post_status( $parent_id ) ) ? $parent_id : 0;
}

/**
 * Horses that list this one as sire or dam. Looked up on the fly, so there
 * is nothing to keep in sync when a foal's parents change.
 */
function ajr_horse_offspring_ids( $post_id ) {
	return get_posts(
		array(
			'post_type'      => 'horse',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'   => 'sire',
					'value' => $post_id,
				),
				array(
					'key'   => 'dam',
					'value' => $post_id,
				),
			),
		)
	);
}

/** Pulls the video ID out of any common YouTube address. */
function ajr_youtube_id( $url ) {
	$pattern = '~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{11})~';
	return preg_match( $pattern, (string) $url, $match ) ? $match[1] : '';
}

/** The horse's videos as a list of [ 'url' => ..., 'youtube_id' => ... ]. */
function ajr_horse_videos( $post_id ) {
	$videos = array();

	foreach ( (array) ajr_field( 'videos', $post_id, false ) as $row ) {
		$url = is_array( $row ) ? (string) reset( $row ) : '';
		if ( $url ) {
			$videos[] = array(
				'url'        => $url,
				'youtube_id' => ajr_youtube_id( $url ),
			);
		}
	}

	return $videos;
}

/** The horse's social links as a list of [ 'label' => ..., 'url' => ... ]. */
function ajr_horse_social_links( $post_id ) {
	$links = array();

	foreach ( (array) ajr_field( 'social_links', $post_id ) as $row ) {
		if ( ! empty( $row['url'] ) ) {
			$links[] = array(
				'label' => isset( $row['platform']['label'] ) ? $row['platform']['label'] : 'Link',
				'url'   => $row['url'],
			);
		}
	}

	return $links;
}

/**
 * Address of the contact page with this horse's name filled into the
 * message box. Needs "Enable Prefill by URL" switched on in the form's
 * settings (WPForms > Contact Form > Settings > General > Advanced).
 */
function ajr_request_info_url( $post_id ) {
	$page = get_page_by_path( AJR_CONTACT_PAGE_PATH );
	$url  = $page ? get_permalink( $page ) : home_url( '/' );

	$message = sprintf( "I'd like more information about %s.", get_the_title( $post_id ) );
	$key     = sprintf( 'wpf%d_%d', AJR_CONTACT_FORM_ID, AJR_CONTACT_MESSAGE_FIELD );

	return add_query_arg( $key, rawurlencode( $message ), $url );
}
