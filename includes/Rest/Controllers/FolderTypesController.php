<?php
namespace Folders\Rest\Controllers;

defined( 'ABSPATH' ) || exit;

use Folders\Models\FolderTypesModel;

class FolderTypesController {
    public function registerRestRoutes() {
        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/foldertypes',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'getItems' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ],
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'createItem' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ],
                [
                    'methods' => \WP_REST_Server::DELETABLE,
                    'callback' => [ $this, 'deleteItems' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/foldertypes/(?P<id>[\d]+)',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'getItem' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ],
                [
                    'methods' => \WP_REST_Server::EDITABLE,
                    'callback' => [ $this, 'updateItem' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );
    }

    public function canUploadFiles() {
        return current_user_can( 'upload_files' );
    }

    public function getItems( \WP_REST_Request $request ) {
        $page = intval( $request->get_param( 'page' ) );
        $perpage = intval( $request->get_param( 'perpage' ) );

        $data = FolderTypesModel::getItems( $page, $perpage);
        $response = isset( $data ) ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function createItem( \WP_REST_Request $request ) {
        $data = FolderTypesModel::createItem( $request->get_json_params() );
        $response = isset( $data ) ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function deleteItems( \WP_REST_Request $request ) {
        $ids = array_map( 'intval', $request->get_param( 'ids' ) );
        $response = [ 'success' => FolderTypesModel::deleteItems( $ids ) ];

        return new \WP_REST_Response( $response );
    }

    public function getItem( \WP_REST_Request $request ) {
        $id = intval( $request->get_param( 'id' ) );

        $data = FolderTypesModel::getItem( $id );
        $response = $data ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function updateItem( \WP_REST_Request $request ) {
        $id = intval( $request->get_param( 'id' ) );
        $response = [ 'success' => FolderTypesModel::updateItem( $id, $request->get_json_params() ) ];

        return new \WP_REST_Response( $response );
    }
}
