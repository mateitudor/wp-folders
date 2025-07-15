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
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'getConfig' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ],
                [
                    'methods'             => \WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'setConfig' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ],
                ]
            ]
        );
    }

    public function canUploadFiles() {
        return current_user_can( 'upload_files' );
    }

    public function getConfig( \WP_REST_Request $request ) {
        $response = [
            'success' => true,
            'data' => ConfigModel::get()
        ];

        return new \WP_REST_Response( $response );
    }

    public function setConfig( \WP_REST_Request $request ) {
        $response = [
            'success' => ConfigModel::set( $request->get_json_params() )
        ];

        return new \WP_REST_Response( $response );
    }
}
