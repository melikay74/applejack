<?php
/**
 * Field setup for Secure Custom Fields (SCF).
 *
 * The fields themselves live in acf-json/group_ajr_horse_details.json. SCF
 * reads that file, so the horse form works without anything stored in the
 * database. To change fields in the dashboard: SCF > Field Groups > Sync
 * available > Sync, then edit; saving writes the change back to that file.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Tell SCF to load field definitions from this plugin's acf-json folder. */
function ajr_scf_json_load_paths( $paths ) {
	$paths[] = AJR_HORSES_DIR . 'acf-json';
	return $paths;
}
add_filter( 'acf/settings/load_json', 'ajr_scf_json_load_paths' );

/** And to save edits to the Horse Details group back into that folder. */
function ajr_scf_json_save_path( $path ) {
	return AJR_HORSES_DIR . 'acf-json';
}
add_filter( 'acf/settings/save_json/key=group_ajr_horse_details', 'ajr_scf_json_save_path' );

/**
 * After a horse is saved, use its first gallery photo as the featured image.
 * The featured image is what the dashboard list, search cards and social
 * sharing previews use, so the client never has to set it separately.
 */
function ajr_sync_featured_image( $post_id ) {
	if ( 'horse' !== get_post_type( $post_id ) ) {
		return;
	}

	$photo_ids = ajr_horse_photo_ids( $post_id );

	if ( $photo_ids ) {
		set_post_thumbnail( $post_id, $photo_ids[0] );
	} else {
		delete_post_thumbnail( $post_id );
	}
}
add_action( 'acf/save_post', 'ajr_sync_featured_image', 20 );

/** Warn in the dashboard if SCF is switched off, since the form depends on it. */
function ajr_scf_missing_notice() {
	if ( function_exists( 'get_field' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p><strong>Applejack Horses</strong> needs the Secure Custom Fields plugin to be active.</p></div>';
}
add_action( 'admin_notices', 'ajr_scf_missing_notice' );
