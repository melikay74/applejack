<?php
/**
 * Plugin Name: Applejack Horses
 * Description: Horse records for Applejack Ridge Farm: the Horse entry form, each horse's own page, and (later) the search and filters.
 * Version: 0.1.0
 * Requires PHP: 8.0
 * Requires Plugins: secure-custom-fields
 * Author: Melissa DeMund
 * Text Domain: applejack-horses
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AJR_HORSES_VERSION', '0.1.0' );
define( 'AJR_HORSES_DIR', plugin_dir_path( __FILE__ ) );
define( 'AJR_HORSES_URL', plugin_dir_url( __FILE__ ) );

require_once AJR_HORSES_DIR . 'includes/post-type.php';
require_once AJR_HORSES_DIR . 'includes/fields.php';
require_once AJR_HORSES_DIR . 'includes/helpers.php';
require_once AJR_HORSES_DIR . 'includes/templates.php';
require_once AJR_HORSES_DIR . 'includes/search.php';

/**
 * On activation: register everything once so the /horses/ addresses work
 * straight away, and create the three page categories.
 */
function ajr_horses_activate() {
	ajr_register_horse_post_type();
	ajr_register_horse_taxonomies();
	ajr_create_default_terms();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'ajr_horses_activate' );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
