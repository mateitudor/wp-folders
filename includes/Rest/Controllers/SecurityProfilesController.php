<?php
namespace Folders\Rest\Controllers;

defined( 'ABSPATH' ) || exit;

use Folders\Models\SecurityProfilesModel;

class SecurityProfilesController {
    public function registerRestRoutes() {
        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/securityprofiles',
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
            '/securityprofiles/all',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'getAllItems' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/securityprofiles/predefined',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'getPredefinedItems' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/securityprofiles/(?P<id>[\d]+)',
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
        $page = intval( $request->get_param( 'page' ), 10 );
        $perpage = intval( $request->get_param( 'perpage' ), 10 );

        $data = SecurityProfilesModel::getItems( $page, $perpage);
        $response = $data ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function getAllItems( \WP_REST_Request $request ) {
        $data = SecurityProfilesModel::getAllItems();
        $response = $data ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function getPredefinedItems( \WP_REST_Request $request ) {
        $data = SecurityProfilesModel::getPredefinedItems();
        $response = $data ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function createItem( \WP_REST_Request $request ) {
        $data = SecurityProfilesModel::createItem( $request->get_json_params() );
        $response = $data ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function deleteItems( \WP_REST_Request $request ) {
        $ids = $request->get_param( 'ids' );
        $ids = is_array( $ids ) ? array_map( 'intval', $ids ) : intval( $ids );

        $response = [
            'success' => SecurityProfilesModel::deleteItems( $ids )
        ];

        return new \WP_REST_Response( $response );
    }

    public function getItem( \WP_REST_Request $request ) {
        $id = intval( $request->get_param( 'id' ) );

        $data = SecurityProfilesModel::getItem( $id );
        $response = $data ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function updateItem( \WP_REST_Request $request ) {
        $id = intval( $request->get_param( 'id' ) );

        $response = [
            'success' => SecurityProfilesModel::updateItem( $id, $request->get_json_params() )
        ];

        return new \WP_REST_Response( $response );
    }
}
