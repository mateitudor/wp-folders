<?php
namespace Folders\Rest\Controllers;

defined( 'ABSPATH' ) || exit;

use Folders\Models\HelperModel;

class HelperController {
    public function registerRestRoutes() {
        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/roles',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'getRoles' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/media-hover-details',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'getMediaHoverDetails' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/users',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'getUsers' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/template',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'getTemplate' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/contextmenu',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'getContextMenu' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );



        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/uninstall',
            [
                [
                    'methods' => \WP_REST_Server::EDITABLE,
                    'callback' => [ $this, 'uninstall' ],
                    'permission_callback' => [ $this, 'canDeletePlugins' ]
                ]
            ]
        );
    }

    public function canUploadFiles() {
        return current_user_can( 'upload_files' );
    }

    public function canDeletePlugins() {
        return current_user_can( 'delete_plugins' );
    }

    public function getRoles( \WP_REST_Request $request ) {
        $response = [
            'success' => true,
            'data' => HelperModel::getRoles()
        ];

        return new \WP_REST_Response( $response ); // return the response as json format
    }

    public function getMediaHoverDetails( \WP_REST_Request $request ) {
        $response = [
            'success' => true,
            'data' => HelperModel::getMediaHoverDetails()
        ];

        return new \WP_REST_Response( $response ); // return the response as json format
    }

    public function getUsers( \WP_REST_Request $request ) {
        $response = [
            'success' => true,
            'data' => HelperModel::getUsers()
        ];

        return new \WP_REST_Response( $response );
    }

    public function getTemplate( \WP_REST_Request $request ) {
        $name = sanitize_file_name( $request->get_param( 'name' ) );
        $data = HelperModel::getTemplate( $name );

        $response = isset( $data ) ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function getContextMenu( \WP_REST_Request $request ) {
        $response = [
            'success' => true,
            'data' => HelperModel::getContextMenu()
        ];

        return new \WP_REST_Response( $response );
    }



    public function uninstall( \WP_REST_Request $request ) {
        $data = HelperModel::uninstall();

        $response = $data ? [ 'success' => true, 'data' => admin_url() ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }
}
