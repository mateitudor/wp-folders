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
        // Clear any cached plugin update data to avoid stale wp.org entries
        delete_site_transient( 'update_plugins' );
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
        // Strip our entry both before and after update checks are computed
        add_filter( 'pre_set_site_transient_update_plugins', [ 'Folders\\Plugin', 'removePluginFromUpdateCheck' ] );
        add_filter( 'site_transient_update_plugins', [ 'Folders\\Plugin', 'removePluginFromUpdateCheck' ] );
        
        // Add custom Git repository update checker
        add_filter( 'site_transient_update_plugins', [ 'Folders\\Plugin', 'checkGitRepositoryForUpdates' ] );
        
        // Override plugin information popup
        add_filter( 'plugins_api', [ 'Folders\\Plugin', 'overridePluginInfo' ], 10, 3 );
        
        // Add auto-update control
        add_filter( 'auto_update_plugin', [ 'Folders\\Plugin', 'controlAutoUpdates' ], 10, 2 );
        
        // Add manual update check action (admins only)
        add_action( 'wp_ajax_folders_force_update_check', [ 'Folders\\Plugin', 'forceUpdateCheck' ] );
        
        // Add debug update status action (admins only)
        add_action( 'wp_ajax_folders_debug_update_status', [ 'Folders\\Plugin', 'debugUpdateStatus' ] );
        
        // Check if database tables exist, create them if they don't
        self::ensureTablesExist();
        
        load_plugin_textdomain( 'folders', false, dirname(FOLDERS_PLUGIN_BASE_NAME) . '/languages/' );

        new Rest\Routes();
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
            // Modify the body to remove our plugin from the update check
            if ( isset( $args['body']['plugins'] ) ) {
                $raw = $args['body']['plugins'];
                $decoded = json_decode( $raw, true );
                if ( is_array( $decoded ) ) {
                    if ( isset( $decoded['plugins'][FOLDERS_PLUGIN_BASE_NAME] ) ) {
                        unset( $decoded['plugins'][FOLDERS_PLUGIN_BASE_NAME] );
                    }
                    $args['body']['plugins'] = wp_json_encode( $decoded );
                } else {
                    // Fallback: some setups send serialized payloads
                    $maybe = @unserialize( $raw ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
                    if ( is_array( $maybe ) && isset( $maybe['plugins'][FOLDERS_PLUGIN_BASE_NAME] ) ) {
                        unset( $maybe['plugins'][FOLDERS_PLUGIN_BASE_NAME] );
                        $args['body']['plugins'] = serialize( $maybe ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
                    }
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
            $update->requires_php = '8.0';
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
                'User-Agent' => 'WordPress/Folders-Plugin/2.9.2',
                'Accept' => 'application/vnd.github.v3+json',
                'Cache-Control' => 'no-cache'
            )
        ) );
        
        if ( is_wp_error( $response ) ) {
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code( $response );
        
        // Check for rate limiting or other errors
        if ( $status_code !== 200 ) {
            // If rate limited, try again in 5 minutes instead of 1 hour
            set_transient( $cache_key, false, 5 * MINUTE_IN_SECONDS );
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
    
    /**
     * Control auto-updates for this plugin
     * Users can enable/disable auto-updates in the plugins table
     */
    public static function controlAutoUpdates( $update, $item ) {
        // Only control our plugin
        if ( $item->plugin !== FOLDERS_PLUGIN_BASE_NAME ) {
            return $update;
        }
        
        // Check if auto-updates are enabled for this plugin
        $auto_updates = get_option( 'auto_update_plugins', array() );
        $auto_update_enabled = in_array( FOLDERS_PLUGIN_BASE_NAME, $auto_updates );
        
        // If auto-updates are disabled, prevent automatic updates
        if ( ! $auto_update_enabled ) {
            return false;
        }
        
        // If auto-updates are enabled, allow the update
        return $update;
    }
    
    /**
     * Force a manual update check (AJAX endpoint)
     */
    public static function forceUpdateCheck() {
        // Check permissions and nonce (if provided)
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }
        if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'folders_admin_action' ) ) {
            // Nonce is optional for backward compatibility with existing JS; enforce if sent
            wp_die( 'Invalid nonce' );
        }
        
        // Clear all update caches
        delete_transient( 'folders_git_latest_version' );
        delete_site_transient( 'update_plugins' );
        
        // Force WordPress to check for updates
        wp_update_plugins();
        
        // Get the latest version
        $latest_version = self::getLatestVersionFromGit();
        $current_version = FOLDERS_PLUGIN_VERSION;
        
        $response = array(
            'success' => true,
            'current_version' => $current_version,
            'latest_version' => $latest_version,
            'update_available' => $latest_version && version_compare( $current_version, $latest_version, '<' ),
            'message' => $latest_version ? "Latest version: $latest_version" : "Could not fetch latest version"
        );
        
        wp_send_json( $response );
    }
    
    /**
     * Override plugin information popup to show our GitHub data
     */
    public static function overridePluginInfo( $result, $action, $args ) {
        // Only handle our plugin
        if ( $action !== 'plugin_information' || ! isset( $args->slug ) || $args->slug !== 'folders' ) {
            return $result;
        }
        
        if ( ! current_user_can( 'manage_options' ) ) {
            return $result;
        }

        // Get the latest version from GitHub
        $latest_version = self::getLatestVersionFromGit();
        
        // Create our own plugin information object
        $plugin_info = new \stdClass();
        $plugin_info->name = 'Folders - Ultimate Folder Organizer';
        $plugin_info->slug = 'folders';
        $plugin_info->version = $latest_version ?: FOLDERS_PLUGIN_VERSION;
        $plugin_info->author = 'Matei Tudor';
        $plugin_info->author_profile = 'https://mateitudor.com';
        $plugin_info->last_updated = date('Y-m-d');
        $plugin_info->requires = '4.6';
        $plugin_info->requires_php = '8.0';
        $plugin_info->tested = '6.8.1';
        $plugin_info->compatibility = array(
            '6.8.1' => array(
                '6.8.1' => array(
                    'rating' => 100,
                    'count' => 1
                )
            )
        );
        $plugin_info->rating = 100;
        $plugin_info->num_ratings = 1;
        $plugin_info->downloaded = 1;
        $plugin_info->active_installs = 1;
        $plugin_info->homepage = 'https://github.com/mateitudor/wp-folders';
        $plugin_info->sections = array(
            'description' => 'A better way to organize the media library, posts, pages, users & custom post types.',
            'installation' => 'Download and install via WordPress admin or upload manually.',
            'changelog' => 'See the commit history for detailed changes.',
            'screenshots' => '',
            'reviews' => ''
        );
        $plugin_info->download_link = $latest_version ? "https://github.com/mateitudor/wp-folders/archive/v{$latest_version}.zip" : '';
        
        return $plugin_info;
    }
    
    /**
     * Debug function to check update status
     * Can be called via AJAX or directly
     */
    public static function debugUpdateStatus() {
        if ( ! current_user_can( 'manage_options' ) ) {
            if ( wp_doing_ajax() ) {
                wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
            }
            return [ 'error' => 'unauthorized' ];
        }
        $current_version = FOLDERS_PLUGIN_VERSION;
        $latest_version = self::getLatestVersionFromGit();
        $cached_version = get_transient( 'folders_git_latest_version' );
        
        $debug_info = array(
            'current_version' => $current_version,
            'latest_version_from_api' => $latest_version,
            'cached_version' => $cached_version,
            'update_available' => $latest_version && version_compare( $current_version, $latest_version, '<' ),
            'cache_expires_in' => get_option( '_transient_timeout_folders_git_latest_version' ) - time(),
            'github_api_url' => 'https://api.github.com/repos/mateitudor/wp-folders/releases/latest'
        );
        
        if ( wp_doing_ajax() ) {
            wp_send_json( $debug_info );
        } else {
            return $debug_info;
        }
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