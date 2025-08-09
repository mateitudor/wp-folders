<?php
namespace Folders\Rest\Controllers;

defined( 'ABSPATH' ) || exit;

use Folders\Models\FoldersModel;
use Folders\Models\HelperModel;
use Folders\Models\UserModel;
use Folders\Models\SecurityProfilesModel;

class FoldersController {
    public function registerRestRoutes() {
        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/foldertypes/unregistered',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'getUnregisteredFolderTypes' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ],
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/meta',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'getMeta' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ],
                [
                    'methods' => \WP_REST_Server::EDITABLE,
                    'callback' => [ $this, 'updateMeta' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/folders',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'getFolders' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ],
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'createFolders' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ],
                [
                    'methods' => \WP_REST_Server::EDITABLE,
                    'callback' => [ $this, 'updateFolders' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ],
                [
                    'methods' => \WP_REST_Server::DELETABLE,
                    'callback' => [ $this, 'deleteFolders' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/copyfolder',
            [
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'copyFolder' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/attach',
            [
                [
                    'methods' => \WP_REST_Server::EDITABLE,
                    'callback' => [ $this, 'attachToFolder' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/attachment/counters',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'getAttachmentCounters' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ],
                [
                    'methods' => \WP_REST_Server::EDITABLE,
                    'callback' => [ $this, 'updateAttachmentCounters' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/export-csv',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'exportCSV' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/import-csv',
            [
                'methods' => \WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'importCSV' ],
                'permission_callback' => [ $this, 'canUploadFiles' ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/replace-media',
            [
                'methods' => \WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'replaceMedia' ],
                'permission_callback' => [ $this, 'canUploadFiles' ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/folders/download/url',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'getDownloadFoldersUrl' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/folders/download/(?P<id>[A-Za-z0-9]{32})',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'downloadFolders' ],
                    'permission_callback' => [ $this, 'canDownloadFolders' ]
                ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/debug',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'debugInfo' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );

        register_rest_route(
            FOLDERS_PLUGIN_REST_URL,
            '/folders/filter-options',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [ $this, 'getFolderFilterOptions' ],
                    'permission_callback' => [ $this, 'canUploadFiles' ]
                ]
            ]
        );
    }

    public function canUploadFiles() {
        return current_user_can( 'upload_files' );
    }

    public function getUnregisteredFolderTypes( \WP_REST_Request $request ) {
        $data = FoldersModel::getUnregisteredTypes();
        $response = [ 'success' => true, 'data' => $data ];

        return new \WP_REST_Response( $response );
    }

    public function getMeta( \WP_REST_Request $request ) {
        $type  = sanitize_key( $request->get_param( 'type' ) );

        $data = UserModel::getMeta( $type );
        $response = isset( $data ) ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function updateMeta( \WP_REST_Request $request ) {
        $type  = sanitize_key( $request->get_param( 'type' ) );
        $meta  = $request->get_param( 'meta' );

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'Folders REST API: updateMeta called with type: ' . $type . ', meta: ' . print_r( $meta, true ) );
        }

        $data = UserModel::updateMeta( $type, $meta );
        $response = isset( $data ) ? [ 'success' => true ] : [ 'success' => false ];

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'Folders REST API: updateMeta result: ' . print_r( $response, true ) );
        }

        return new \WP_REST_Response( $response );
    }

    public function getFolders( \WP_REST_Request $request ) {
        $type  = sanitize_key( $request->get_param( 'type' ) );

        $data = FoldersModel::getFolders( $type );
        $response = isset( $data ) ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function createFolders( \WP_REST_Request $request ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'FoldersController::createFolders called' );
        }
        
        $type  = sanitize_key( $request->get_param( 'type' ) );
        $parent = intval( $request->get_param( 'parent' ) );
        
        // Handle both 'names' and 'folders' parameters for compatibility
        $names = $request->get_param( 'names' );
        if ( empty( $names ) ) {
            $names = $request->get_param( 'folders' );
        }
        $names = array_map( 'sanitize_text_field', $names ? $names : [] );
        
        $color = HelperModel::filterColor( $request->get_param( 'color' ) );
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'FoldersController::createFolders - params: type=' . $type . ', parent=' . $parent . ', names=' . print_r( $names, true ) . ', color=' . $color );
        }

        $data = FoldersModel::createFolders( $type, $parent, $names, $color );
        $response = isset( $data ) ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'FoldersController::createFolders - response: ' . print_r( $response, true ) );
        }

        return new \WP_REST_Response( $response );
    }

    public function updateFolders( \WP_REST_Request $request ) {
        $type  = sanitize_key( $request->get_param( 'type' ) );
        $action  = sanitize_key( $request->get_param( 'action' ) );
        $ids = $request->has_param( 'folders' ) ? array_map( 'intval', $request->get_param( 'folders' ) ) : [];
        $attrs = [
            'name' => sanitize_text_field( $request->get_param( 'name' ) ),
            'color' => HelperModel::filterColor( $request->get_param( 'color' ) ),
            'parent' => intval( $request->get_param( 'parent' ) ),
            'sorting' => $request->has_param( 'sorting' ) ? array_map( 'intval', $request->get_param( 'sorting' ) ) : []
        ];

        $data = FoldersModel::updateFolders( $type, $action, $ids, $attrs );
        $response = isset( $data ) ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function copyFolder( \WP_REST_Request $request ) {
        $type  = sanitize_key( $request->get_param( 'type' ) );
        $src = intval( $request->get_param( 'src' ) );
        $dst = intval( $request->get_param( 'dst' ) );

        $data = FoldersModel::copyFolder( $type, $src, $dst );
        $response = isset( $data ) ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function deleteFolders( \WP_REST_Request $request ) {
        $type  = sanitize_key( $request->get_param( 'type' ) );
        $ids = $request->has_param( 'folders' ) ? array_map( 'intval', $request->get_param( 'folders' ) ) : [];
        $deleteAttachments = $request->has_param( 'deleteAttachments' ) ? boolval( $request->get_param( 'deleteAttachments' ) ) : false;

        $data = FoldersModel::deleteFolders( $type, $ids, $deleteAttachments );
        $response = isset( $data ) ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function attachToFolder( \WP_REST_Request $request ) {
        $type  = sanitize_key( $request->get_param( 'type' ) );
        $id  = sanitize_key( $request->get_param( 'folder' ) );
        $attachments = $request->has_param( 'attachments' ) ? array_map( 'intval', $request->get_param( 'attachments' ) ) : [];

        $data = FoldersModel::attachToFolder( $type, $id, $attachments );
        $response = isset( $data ) ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function getAttachmentCounters( \WP_REST_Request $request ) {
        $type  = sanitize_key( $request->get_param( 'type' ) );
        $ids = array_map( 'intval', $request->get_param( 'folders' ) ? $request->get_param( 'folders' ) : [] );

        $data = FoldersModel::getAttachmentCounters( $type, $ids );
        $response = isset( $data ) ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function updateAttachmentCounters( \WP_REST_Request $request ) {
        $response = [ 'success' => FoldersModel::updateAttachmentCounters() ];
        return new \WP_REST_Response( $response );
    }

    public function exportCSV( \WP_REST_Request $request ) {
        $data = FoldersModel::exportCSV();
        $response = isset( $data ) ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function importCSV( \WP_REST_Request $request ) {
        $params  = $request->get_file_params();
        $file = $params['file']['tmp_name'];
        $clear  = filter_var( $request->get_param( 'clear' ), FILTER_VALIDATE_BOOLEAN );
        $attachments  = filter_var( $request->get_param( 'attachments' ), FILTER_VALIDATE_BOOLEAN );

        $data = FoldersModel::importCSV( $file, $clear, $attachments );
        $response = isset( $data ) ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function replaceMedia( \WP_REST_Request $request ) {
        $params  = $request->get_file_params();
        $file = $params['file']['tmp_name'];
        $attachment = intval( $request->get_param( 'attachment' ) );

        $response = [ 'success' => FoldersModel::replaceMedia( $file, $attachment ) ];
        return new \WP_REST_Response( $response );
    }

    public function getDownloadFoldersUrl( \WP_REST_Request $request ) {
        $type = sanitize_key( $request->get_param( 'type' ) );
        $ids = $request->has_param( 'folders' ) ? array_map( 'intval', $request->get_param( 'folders' ) ) : [];

        $data = FoldersModel::getDownloadFoldersUrl( $type, $ids  );
        $response = isset( $data ) ? [ 'success' => true, 'data' => $data ] : [ 'success' => false ];

        return new \WP_REST_Response( $response );
    }

    public function downloadFolders( \WP_REST_Request $request ) {
        $id = sanitize_key( $request->get_param( 'id' ) );
        FoldersModel::downloadFolders( $id );
        return new \WP_REST_Response( null, 404 );
    }

    /**
     * Ensure the request is authenticated and bound to the user who created the download token
     */
    public function canDownloadFolders( \WP_REST_Request $request ) {
        $token = sanitize_key( $request['id'] );
        $data = get_transient( $token );
        if ( $data === false || ! is_array( $data ) ) {
            return false;
        }
        return is_user_logged_in() && get_current_user_id() === intval( $data['user'] );
    }

    public function debugInfo( \WP_REST_Request $request ) {
        $response = [
            'success' => true,
            'data' => [
                'version' => FOLDERS_PLUGIN_VERSION,
                'db_version' => FOLDERS_PLUGIN_DB_VERSION,
                'rest_url' => FOLDERS_PLUGIN_REST_URL,
                'plugin_url' => FOLDERS_PLUGIN_URL,
                'plugin_path' => FOLDERS_PLUGIN_PATH,
                'database_info' => FoldersModel::checkDatabaseIntegrity(),
                'user_rights' => UserModel::getRights( 'attachment' ),
                'tables_migrated' => get_option( 'folders_tables_migrated', false )
            ]
        ];

        return new \WP_REST_Response( $response );
    }

    public function getFolderFilterOptions( \WP_REST_Request $request ) {
        global $wpdb;
        $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );
        $tableAttachments = HelperModel::getTableName( HelperModel::ATTACHMENTS );
        
        // Check if tables exist
        $folders_table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$tableFolders}'") == $tableFolders;
        $attachments_table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$tableAttachments}'") == $tableAttachments;
        
        if ( !$folders_table_exists || !$attachments_table_exists ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'Folders Plugin: Tables do not exist for filter options' );
            }
            return new \WP_REST_Response( [ 'success' => false, 'data' => [] ] );
        }
        
        $rights = UserModel::getRights( 'attachment' );
        if ( !$rights || !$rights['access_type'] || !$rights['v'] ) {
            return new \WP_REST_Response( [ 'success' => false, 'data' => [] ] );
        }
        
        $owner = $rights['access_type'] == SecurityProfilesModel::COMMON_FOLDERS ? 0 : get_current_user_id();
        
        // Get folders with attachment counts
        $sql = $wpdb->prepare( "
            SELECT F.id, F.title, COUNT(A.attachment_id) as count
            FROM {$tableFolders} AS F
            LEFT JOIN {$tableAttachments} AS A ON F.id = A.folder_id
            WHERE F.type = 'attachment' AND F.owner = %d
            GROUP BY F.id
            ORDER BY F.title ASC
        ", $owner );
        
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $folders = $wpdb->get_results( $sql, ARRAY_A );
        
        $formatted_folders = [];
        foreach ( $folders as $folder ) {
            $formatted_folders[] = [
                'id' => $folder['id'],
                'title' => $folder['title'] . ' (' . $folder['count'] . ')'
            ];
        }
        
        $response = [
            'success' => true,
            'data' => $formatted_folders
        ];

        return new \WP_REST_Response( $response );
    }
}