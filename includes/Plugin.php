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
        
        // Add custom Git repository update checker
        add_filter( 'site_transient_update_plugins', [ 'Folders\\Plugin', 'checkGitRepositoryForUpdates' ] );
        
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
    
    /**
     * Check Git repository for plugin updates
     */
    public static function checkGitRepositoryForUpdates( $transient ) {
        if ( ! $transient ) {
            return $transient;
        }
        
        $current_version = FOLDERS_PLUGIN_VERSION;
        $latest_version = self::getLatestVersionFromGit();
        
        if ( $latest_version && version_compare( $current_version, $latest_version, '<' ) ) {
            $update = new \stdClass();
            $update->id = FOLDERS_PLUGIN_BASE_NAME;
            $update->slug = 'folders';
            $update->plugin = FOLDERS_PLUGIN_BASE_NAME;
            $update->new_version = $latest_version;
            $update->url = 'https://github.com/mateitudor/wp-folders';
            $update->package = "https://github.com/mateitudor/wp-folders/archive/v{$latest_version}.zip";
            $update->tested = '6.8.1';
            $update->requires = '4.6';
            $update->requires_php = '7.4';
            $update->last_updated = date('Y-m-d');
            
            if ( ! isset( $transient->response ) ) {
                $transient->response = array();
            }
            $transient->response[FOLDERS_PLUGIN_BASE_NAME] = $update;
        }
        
        return $transient;
    }
    
    /**
     * Get the latest version from Git repository
     */
    private static function getLatestVersionFromGit() {
        // Cache for 1 hour
        $cache_key = 'folders_git_latest_version';
        $cached_version = get_transient( $cache_key );
        
        if ( $cached_version !== false ) {
            return $cached_version;
        }
        
        // GitHub API endpoint for releases
        $api_url = 'https://api.github.com/repos/mateitudor/wp-folders/releases/latest';
        
        $response = wp_remote_get( $api_url, array(
            'timeout' => 15,
            'headers' => array(
                'User-Agent' => 'WordPress/Folders-Plugin',
                'Accept' => 'application/vnd.github.v3+json'
            )
        ) );
        
        if ( is_wp_error( $response ) ) {
            return false;
        }
        
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        
        if ( ! $data || ! isset( $data['tag_name'] ) ) {
            return false;
        }
        
        // Extract version from tag (remove 'v' prefix if present)
        $version = ltrim( $data['tag_name'], 'v' );
        
        // Cache for 1 hour
        set_transient( $cache_key, $version, HOUR_IN_SECONDS );
        
        return $version;
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