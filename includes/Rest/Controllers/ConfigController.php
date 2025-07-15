<?php
namespace Folders\Rest\Controllers;

defined( 'ABSPATH' ) || exit;

use Folders\Models\ConfigModel;

class ConfigController {
    public function registerRestRoutes() {
        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/config',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'getConfig' ],
                    'permission_callback' => [ $this, 'canManageOptions' ]
                ],
                [
                    'methods' => \WP_REST_Server::EDITABLE,
                    'callback' => [ $this, 'setConfig' ],
                    'permission_callback' => [ $this, 'canManageOptions' ]
                ]
            ]
        );
        
        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/config/auto-updates',
            [
                [
                    'methods' => \WP_REST_Server::EDITABLE,
                    'callback' => [ $this, 'updateAutoUpdates' ],
                    'permission_callback' => [ $this, 'canManageOptions' ]
                ]
            ]
        );
    }

    public function canUploadFiles() {
        return current_user_can( 'upload_files' );
    }
    
    public function canManageOptions() {
        return current_user_can( 'manage_options' );
    }

    public function getConfig( \WP_REST_Request $request ) {
        $response = [
            'success' => true,
            'data' => ConfigModel::get()
        ];

        return new \WP_REST_Response( $response );
    }

    public function setConfig( \WP_REST_Request $request ) {
        $data = $request->get_json_params();
        $response = [ 'success' => ConfigModel::set( $data ) ];

        return new \WP_REST_Response( $response );
    }
    
    /**
     * Handle auto-update setting changes
     */
    public function updateAutoUpdates( \WP_REST_Request $request ) {
        $data = $request->get_json_params();
        $auto_updates_enabled = isset( $data['auto_updates'] ) ? (bool) $data['auto_updates'] : false;
        
        // Get current auto-update settings
        $auto_update_plugins = get_option( 'auto_update_plugins', array() );
        
        if ( $auto_updates_enabled ) {
            // Add plugin to auto-update list
            if ( ! in_array( FOLDERS_PLUGIN_BASE_NAME, $auto_update_plugins ) ) {
                $auto_update_plugins[] = FOLDERS_PLUGIN_BASE_NAME;
            }
        } else {
            // Remove plugin from auto-update list
            $auto_update_plugins = array_diff( $auto_update_plugins, array( FOLDERS_PLUGIN_BASE_NAME ) );
        }
        
        // Save auto-update settings
        $saved = update_option( 'auto_update_plugins', $auto_update_plugins );
        
        // Also save to plugin config
        $config = ConfigModel::get();
        $config['auto_updates'] = $auto_updates_enabled;
        ConfigModel::set( $config );
        
        $response = [ 
            'success' => $saved,
            'auto_updates_enabled' => $auto_updates_enabled
        ];

        return new \WP_REST_Response( $response );
    }
}
