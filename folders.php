<?php
/**
 * Plugin Name: Folders - Ultimate Folder Organizer
 * Plugin URI: https://1.envato.market/getfolders
 * Description: A better way to organize the media library, posts, pages, users & custom post types.
 * Version: 2.9.17
 * Requires at least: 4.6
 * Requires PHP: 8.0
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

// Only check for truly incompatible versions, not just existing data
$old_version = get_option( 'folders_version' );
if( $old_version && version_compare( $old_version, '2.0.0', '<' ) ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ( isset( $_GET['folders_delete_old_plugin_data'] ) ) {
        require_once plugin_dir_path( __FILE__ ) . 'includes/Fallbacks/delete-old-plugin-data.php';
    } else {
        require_once plugin_dir_path( __FILE__ ) . 'includes/Fallbacks/plugin-incompatible.php';
        add_action( 'admin_init', function () { deactivate_plugins( plugin_basename( __FILE__ ) ); } );
        return;
    }
}

/**
 * Get plugin version from Git tag or header comment
 */
function get_plugin_version() {
    static $version = null;
    
    if ( $version === null ) {
        // Try to get version from Git tag first
        $git_version = get_git_version();
        if ( $git_version ) {
            $version = $git_version;
        } else {
            // Fallback to header comment
            $plugin_data = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
            $version = $plugin_data['Version'] ?? '1.0.0';
        }
    }
    
    return $version;
}

/**
 * Get version from Git tag
 */
function get_git_version() {
    $git_dir = __DIR__ . '/.git';
    
    // Check if we're in a Git repository
    if ( ! is_dir( $git_dir ) ) {
        return false;
    }
    
    // Try to get the latest tag
    $output = array();
    $return_var = 0;
    
    // Get the latest tag
    exec( 'cd ' . escapeshellarg( __DIR__ ) . ' && git describe --tags --abbrev=0 2>/dev/null', $output, $return_var );
    
    if ( $return_var === 0 && ! empty( $output[0] ) ) {
        $tag = trim( $output[0] );
        // Remove 'v' prefix if present
        return ltrim( $tag, 'v' );
    }
    
    return false;
}

define( 'FOLDERS_PLUGIN_NAME', 'folders' );
define( 'FOLDERS_PLUGIN_VERSION', get_plugin_version() );
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