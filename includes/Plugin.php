<?php
namespace Folders;

defined( 'ABSPATH' ) || exit;

class Plugin {
	public static function run() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Folders Plugin: run() method called' );
		}
		add_action( 'plugins_loaded', [ 'Folders\\Plugin', 'pluginsLoaded' ] );
	}

	public static function activate() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Folders Plugin: activate() method called' );
		}
		new System\Installer();
	}

	public static function deactivate() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Folders Plugin: deactivate() method called' );
		}
	}

	    public static function pluginsLoaded() {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'Folders Plugin: pluginsLoaded() method called' );
        }
        
        // Prevent multiple initialization using a more robust method
        static $initialized = false;
        if ( $initialized ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'Folders Plugin: Already initialized, skipping' );
            }
            return;
        }
        $initialized = true;
        
        // Disable WordPress.org update checks for this plugin
        add_filter( 'http_request_args', [ 'Folders\\Plugin', 'disableWordPressOrgUpdates' ], 10, 2 );
        add_filter( 'site_transient_update_plugins', [ 'Folders\\Plugin', 'removePluginFromUpdateCheck' ] );
        
        // Check if database tables exist, create them if they don't
        self::ensureTablesExist();
        
        load_plugin_textdomain( 'folders', false, dirname(FOLDERS_PLUGIN_BASE_NAME) . '/languages/' );

        new Rest\Routes();
        new System\Notice();
        new System\Folders();
        new System\Settings();
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'Folders Plugin: All components initialized' );
        }
    }
    
    /**
     * Disable WordPress.org update checks for this plugin
     * This prevents conflicts with other plugins that have the same name
     */
    public static function disableWordPressOrgUpdates( $args, $url ) {
        // Check if this is a WordPress.org API request for plugin updates
        if ( strpos( $url, 'api.wordpress.org/plugins/update-check' ) !== false ) {
            // Decode the body to modify the plugins list
            if ( isset( $args['body']['plugins'] ) ) {
                $plugins = json_decode( $args['body']['plugins'], true );
                
                // Remove our plugin from the update check
                if ( isset( $plugins['plugins'][FOLDERS_PLUGIN_BASE_NAME] ) ) {
                    unset( $plugins['plugins'][FOLDERS_PLUGIN_BASE_NAME] );
                    $args['body']['plugins'] = json_encode( $plugins );
                }
            }
        }
        
        return $args;
    }
    
    /**
     * Remove this plugin from WordPress update checks
     * This prevents WordPress from showing update notifications for other plugins with the same name
     */
    public static function removePluginFromUpdateCheck( $transient ) {
        if ( $transient && isset( $transient->response ) ) {
            // Remove our plugin from the update response
            if ( isset( $transient->response[FOLDERS_PLUGIN_BASE_NAME] ) ) {
                unset( $transient->response[FOLDERS_PLUGIN_BASE_NAME] );
            }
        }
        
        return $transient;
    }
	
	private static function ensureTablesExist() {
		global $wpdb;
		
		$tableFolders = $wpdb->prefix . 'folders_folders';
		$tableExists = $wpdb->get_var("SHOW TABLES LIKE '{$tableFolders}'") == $tableFolders;
		
		if ( !$tableExists ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Folders Plugin: Database tables not found, creating them...' );
			}
			new System\Installer();
		}
	}
}