<?php
/**
 * Plugin Name: Folders - Ultimate Folder Organizer
 * Plugin URI: https://1.envato.market/getfolders
 * Description: A better way to organize the media library, posts, pages, users & custom post types.
 * Version: 2.9.4
 * Requires at least: 4.6
 * Requires PHP: 7.4
 * Author: Matei Tudor
 * Author URI: https://mateitudor.com
 * License: GPLv3
 * Text Domain: folders
 * Domain Path: /languages
 */
namespace Folders;

defined( 'ABSPATH' ) || exit;

// Basic debugging
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    error_log( 'Folders Plugin: Main plugin file loaded' );
}

// Prevent multiple loading
if ( defined( 'FOLDERS_PLUGIN_LOADED' ) ) {
    return;
}

if ( class_exists( 'Folders\\Plugin' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/Fallbacks/plugin-exist.php';
    add_action( 'admin_init', function() { deactivate_plugins( plugin_basename( __FILE__ ) ); } );
    return;
}

if( get_option( 'folders_settings' ) ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ( isset( $_GET['folders_delete_old_plugin_data'] ) ) {
        require_once plugin_dir_path( __FILE__ ) . 'includes/Fallbacks/delete-old-plugin-data.php';
    } else {
        require_once plugin_dir_path( __FILE__ ) . 'includes/Fallbacks/plugin-incompatible.php';
        add_action( 'admin_init', function () { deactivate_plugins( plugin_basename( __FILE__ ) ); } );
        return;
    }
}



define( 'FOLDERS_PLUGIN_NAME', 'folders' );
define( 'FOLDERS_PLUGIN_VERSION', '2.9.1' );
define( 'FOLDERS_PLUGIN_DB_VERSION', '2.0.0');
define( 'FOLDERS_PLUGIN_DB_TABLE_PREFIX', 'folders' );
define( 'FOLDERS_PLUGIN_SHORTCODE_NAME', 'folders' );
define( 'FOLDERS_PLUGIN_BASE_NAME', plugin_basename( __FILE__ ) );
define( 'FOLDERS_PLUGIN_PATH', __DIR__ );
define( 'FOLDERS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FOLDERS_PLUGIN_REST_URL', 'folders/v1' );
define( 'FOLDERS_PLUGIN_PUBLIC_REST_URL', 'folders/public/v1' );

// Debug constants
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    error_log( 'Folders Plugin: Constants defined' );
}

register_activation_hook( __FILE__, [ 'Folders\\Plugin', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'Folders\\Plugin', 'deactivate' ] );

require_once( __DIR__ . '/vendor/autoload.php' );
require_once( __DIR__ . '/includes/autoload.php' );

if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    error_log( 'Folders Plugin: Autoload files included' );
}

// Mark plugin as loaded
define( 'FOLDERS_PLUGIN_LOADED', true );

// Note: WordPress.org update checks are disabled for this plugin to prevent conflicts
// with other plugins that have similar names in the WordPress.org repository

Plugin::run();

if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    error_log( 'Folders Plugin: Plugin::run() called' );
}