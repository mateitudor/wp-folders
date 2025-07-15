<?php
namespace Folders\Rest\Controllers;

defined( 'ABSPATH' ) || exit;

use Folders\Models\ImportModel;

class ImportController {
    public function registerRestRoutes() {
        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/import/(?P<plugin>[a-zA-Z]+)',
            [
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'importPluginData' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );
    }

    public function canUploadFiles() {
        return current_user_can( 'upload_files' );
    }

    public function importPluginData( \WP_REST_Request $request ) {
        $plugin = strtoupper( sanitize_key( $request->get_param( 'plugin' ) ) );

        $data = ImportModel::importPluginData( $plugin );
        $response = [ 'success' => true ];

        return new \WP_REST_Response( $response );
    }
}
