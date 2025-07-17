<?php
namespace Folders\Models;

defined( 'ABSPATH' ) || exit;

class HelperModel {
    public const FOLDERS = 'folders';
    public const ATTACHMENTS = 'attachments';
    public const FOLDER_TYPES = 'folder_types';
    public const SECURITY_PROFILES = 'security_profiles';

    public static function getTableName( $table ) {
        global $wpdb;
        return $wpdb->prefix . FOLDERS_PLUGIN_DB_TABLE_PREFIX . '_' . $table;
    }



    public static function uninstall() {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        if ( \is_plugin_active( FOLDERS_PLUGIN_BASE_NAME ) ) {
            \deactivate_plugins( FOLDERS_PLUGIN_BASE_NAME );

            $options = [
                'folders_db_version',
                'folders_version',
                'folders_config'
            ];

            foreach( $options as $option ) {
                delete_option( $option );
            }

            delete_metadata( 'user', 0, 'folders_settings', '', true );

            global $wpdb;
            $tables = [
                self::getTableName( self::FOLDERS ),
                self::getTableName( self::ATTACHMENTS ),
                self::getTableName( self::FOLDER_TYPES ),
                self::getTableName( self::SECURITY_PROFILES )
            ];

            foreach( $tables as $table ) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
            }

            return true;
        }

        return false;
    }

    public static function getRoles() {
        $data['items'] = [];
        $roles = wp_roles()->roles;

        foreach ( $roles as $key => $role ) {
            if ( array_key_exists( 'upload_files', $role['capabilities'] ) ) {
                $data['items'][] = [ 'id' => $key, 'title' => translate_user_role( $role['name'] ) ];
            }
        }
        $data['total'] = count( $data['items'] );

        return $data;
    }

    public static function getMediaHoverDetails() {
        $data['items'] = [];

        		$data['items'][] = [ 'id' => 'title', 'title' => esc_html__( "Title", 'folders' ) ];
		$data['items'][] = [ 'id' => 'alternative_text', 'title' => esc_html__( "Alternative text", 'folders' ) ];
		$data['items'][] = [ 'id' => 'file_url', 'title' => esc_html__( "File URL", 'folders' ) ];
		$data['items'][] = [ 'id' => 'dimension', 'title' => esc_html__( "Dimension", 'folders' ) ];
		$data['items'][] = [ 'id' => 'size', 'title' => esc_html__( "Size", 'folders' ) ];
		$data['items'][] = [ 'id' => 'filename', 'title' => esc_html__( "Filename", 'folders' ) ];
		$data['items'][] = [ 'id' => 'type', 'title' => esc_html__( "Type", 'folders' ) ];
		$data['items'][] = [ 'id' => 'date', 'title' => esc_html__( "Date", 'folders' ) ];
		$data['items'][] = [ 'id' => 'uploaded_by', 'title' => esc_html__( "Uploaded by", 'folders' ) ];

        $data['total'] = count( $data['items'] );

        return $data;
    }

    public static function getUsers() {
        $data['items'] = [];

        global $wpdb;
        $sql = "SELECT id FROM {$wpdb->users} ORDER BY user_nicename";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $ids = array_column( $wpdb->get_results( $sql, 'ARRAY_A' ), 'id' );

        foreach ( $ids as $id ) {
            $user = new \WP_User( $id );
            if ( $user->has_cap( 'upload_files' ) ) {
                $data['items'][] = [ 'id' => $user->ID, 'title' => $user->display_name ];
            }
        }
        $data['total'] = count( $data['items'] );

        return $data;
    }

    public static function getMessagesForSidebar() {
        $messages = [
            'success' => esc_html__( "The operation completed successfully", 'folders' ),
            'failed' => esc_html__( "The operation failed", 'folders' ),
            'parent_folder' => esc_html__( "Parent Folder", 'folders' ),
            'new_folder' => esc_html__( "New Folder", 'folders' )
        ];
        return $messages;
    }

    public static function getMessagesForSettings() {
        $messages = [
            'success' => esc_html__( "The operation completed successfully", 'folders' ),
            'failed'  => esc_html__( "The operation failed", 'folders' ),
            'builtin' => esc_html__( "These plugin settings are built-in and cannot be modified", 'folders' ),
            'status' => [
                '400' => esc_html__( 'The license is already registered for other domain', 'folders' ),
                '401' => esc_html__( 'Failed to connect to license server', 'folders' ),
                '402' => esc_html__( 'The personal token is invalid or has been deleted', 'folders' ),
                '403' => esc_html__( 'The personal token is missing the required permission for license server', 'folders' ),
                '404' => esc_html__( 'Invalid purchase code', 'folders' ),
                '405' => esc_html__( 'Something goes wrong, try again shortly', 'folders' ),
                '406' => esc_html__( 'Parsing error, try again shortly', 'folders' ),
                '407' => esc_html__( 'Error updating license data', 'folders' ),
            ]
        ];
        return $messages;
    }

    public static function getTemplate( $name ) {
        if ( $name ) {
            $file = FOLDERS_PLUGIN_PATH . '/includes/Views/' . $name . '.php';
            $data = null;

            if ( file_exists( $file ) ) {
                ob_start(); // turn on buffering
                require_once( $file );
                $data = ob_get_contents(); // get the buffered content into a var
                ob_end_clean(); // clean buffer
            }

            return $data;
        }

        return null;
    }

    public static function getContextMenu() {
        return [
            [
                'id' => 'create',
                'right' => 'c',
                'title' => esc_html__( "New Folder", 'folders' ),
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d=" M 21 7 L 10.393 7 C 10.176 7 9.921 6.842 9.824 6.648 L 9 5 L 3.098 4.991 L 3 19 L 21 19 L 21 7 L 21 7 L 21 7 Z  M 1.786 21 L 22.214 21 C 22.648 21 23 20.648 23 20.214 L 23 5.786 C 23 5.352 22.648 5 22.214 5 L 11 5 L 10.176 3.361 C 10.079 3.167 9.824 3.009 9.607 3.009 L 1.786 3.001 C 1.352 3 1 3.352 1 3.786 L 1 20.214 C 1 20.648 1.352 21 1.786 21 L 1.786 21 Z  M 13 12 L 13 9 L 11 9 L 11 12 L 8 12 L 8 14 L 11 14 L 11 17 L 13 17 L 13 14 L 16 14 L 16 12 L 13 12 L 13 12 Z " fill-rule="evenodd"/></svg>'
            ],
            [
                'id' => 'color',
                'right' => 'e',
                'title' => esc_html__( "Color", 'folders' ),
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d=" M 1.313 12 L 9.197 19.875 L 18.197 10.875 L 10.322 3 L 1.313 12 L 1.313 12 Z  M 6.384 9.75 L 10.316 5.677 L 14.298 9.75 L 6.384 9.75 Z  M 19.312 12 C 21.562 14.508 22.687 16.382 22.687 17.625 C 22.687 19.489 21.176 21 19.312 21 C 17.449 21 15.937 19.489 15.937 17.625 C 15.937 16.382 17.063 14.508 19.312 12 L 19.312 12 Z " /></svg>'
            ],
            [
                'id' => 'rename',
                'right' => 'e',
                'title' => esc_html__( "Rename", 'folders' ),
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d=" M 7.774 8.994 L 5.887 12.768 C 5.868 12.8 5.851 12.833 5.837 12.868 L 5.084 14.373 C 4.991 14.56 4.975 14.77 5.037 14.972 C 5.107 15.167 5.247 15.33 5.426 15.416 C 5.613 15.509 5.823 15.525 6.026 15.463 C 6.22 15.393 6.384 15.253 6.469 15.074 L 7.033 13.945 L 9.967 13.945 L 9.967 13.945 L 10.531 15.074 C 10.616 15.253 10.78 15.393 10.974 15.463 C 11.177 15.525 11.387 15.509 11.574 15.416 C 11.753 15.33 11.893 15.167 11.963 14.972 C 12.025 14.77 12.009 14.56 11.916 14.373 L 9.193 8.926 C 9.107 8.747 8.944 8.607 8.749 8.537 C 8.547 8.475 8.337 8.491 8.15 8.584 C 7.981 8.665 7.847 8.814 7.774 8.994 Z  M 9.189 12.389 L 8.5 11.012 L 7.811 12.389 L 9.189 12.389 L 9.189 12.389 L 9.189 12.389 Z " fill-rule="evenodd" /><path d=" M 14 7 L 3.098 7 L 3 17 L 14 17 L 14 19 L 1.786 19 L 1.786 19 C 1.352 19 1 18.648 1 18.214 L 1 5.795 C 1 5.361 1.352 5.009 1.786 5.01 L 14 5.006 L 14 7 L 14 7 Z " /><path d=" M 15 19.996 L 14 19.996 L 14 20.996 L 18 21 L 18 21 L 18 21 L 18 20 L 17 20 L 17 4 L 18 4 L 18 3.004 L 14 2.994 L 14 3.994 L 15 3.994 L 15 19.996 Z " /><path d=" M 18 17 L 21 17 L 21 7 L 21 7 L 18 7 L 18 5.008 L 22.214 5 C 22.648 5 23 5.352 23 5.786 L 23 18.214 C 23 18.648 22.648 19 22.214 19 L 18 19.003 L 18 19.004 L 18 17 Z " /></svg>'
            ],
            [
                'id' => 'copy',
                'right' => 'e',
                'title' => esc_html__( "Copy", 'folders' ),
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d=" M 15 5 L 15 2.786 C 15 2.352 14.648 2 14.214 2 L 2.786 2.01 C 2.352 2.009 2 2.361 2 2.795 L 2 17.214 C 2 17.648 2.352 18 2.786 18 L 2.786 18 L 8 18 L 8 16 L 4 16 L 4.098 4 L 13 4 L 13 4 L 13 5 L 15 5 L 15 5 Z " /><path d=" M 21.245 6 C 21.662 6 22 6.352 22 6.786 L 22 21.214 C 22 21.648 21.662 22 21.245 22 L 9.755 22 L 9.755 22 C 9.338 22 9 21.648 9 21.214 L 9 6.795 C 9 6.361 9.338 6.009 9.755 6.01 L 21.245 6 Z  M 20 8 L 11 8 L 11 20 L 20 20 L 20 8 Z " fill-rule="evenodd" /></svg>'
            ],
            [
                'id' => 'paste',
                'right' => 'e',
                'title' => esc_html__( "Paste", 'folders' ),
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d=" M 7 3 L 4 3 C 3.448 3 3 3.448 3 4 L 3 20 C 3 20.552 3.448 21 4 21 L 12 21 L 12 21 L 12 21 L 12 19 L 5 19 L 5 5 L 7 5 L 7 6 C 7 6.552 7.448 7 8 7 L 16 7 C 16.552 7 17 6.552 17 6 L 17 5 L 19 5 L 19 10 L 21 10 L 21 10 L 21 10 L 21 4 C 21 3.448 20.552 3 20 3 L 17 3 L 17 2 C 17 1.448 16.552 1 16 1 L 8 1 C 7.448 1 7 1.448 7 2 L 7 3 Z  M 9 4 L 9 4 L 9 4 L 9 5 L 15 5 L 15 4 L 15 4 L 15 4 L 15 3 L 9 3 L 9 3 L 9 4 Z " fill-rule="evenodd" /><path d=" M 14.101 11 L 21.899 11 C 22.507 11 23 11.493 23 12.101 L 23 21.899 C 23 22.507 22.507 23 21.899 23 L 14.101 23 C 13.493 23 13 22.507 13 21.899 L 13 12.101 C 13 11.493 13.493 11 14.101 11 Z  M 21 21 L 15 21 L 15 13 L 21 13 L 21 21 Z " fill-rule="evenodd" /></svg>'
            ],
            [
                'id' => 'delete',
                'right' => 'd',
                'title' => esc_html__( "Delete", 'folders' ),
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d=" M 10.423 3 C 10.01 3 9.588 3.145 9.289 3.444 C 8.99 3.742 8.846 4.164 8.846 4.577 L 8.846 5.366 L 4.114 5.366 L 4.114 6.943 L 4.902 6.943 L 4.902 19.561 C 4.902 20.858 5.971 21.927 7.268 21.927 L 16.732 21.927 C 18.029 21.927 19.098 20.858 19.098 19.561 L 19.098 6.943 L 19.886 6.943 L 19.886 5.366 L 15.154 5.366 L 15.154 4.577 C 15.154 4.164 15.01 3.742 14.711 3.444 C 14.412 3.145 13.99 3 13.577 3 L 10.423 3 Z  M 10.423 4.577 L 13.577 4.577 L 13.577 5.366 L 10.423 5.366 L 10.423 4.577 Z  M 6.48 6.943 L 17.52 6.943 L 17.52 19.561 C 17.52 19.998 17.169 20.35 16.732 20.35 L 7.268 20.35 C 6.831 20.35 6.48 19.998 6.48 19.561 L 6.48 6.943 Z  M 8.057 9.309 L 8.057 17.984 L 9.634 17.984 L 9.634 9.309 L 8.057 9.309 Z  M 11.211 9.309 L 11.211 17.984 L 12.789 17.984 L 12.789 9.309 L 11.211 9.309 Z  M 14.366 9.309 L 14.366 17.984 L 15.943 17.984 L 15.943 9.309 L 14.366 9.309 Z "/></svg>'
            ],
            [
                'id' => 'download',
                'right' => 'v',
                'title' => esc_html__( "Download", 'folders' ),
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d=" M 21 7 L 10.393 7 C 10.176 7 9.921 6.842 9.824 6.648 L 9 5 L 3.098 4.991 L 3 19 L 21 19 L 21 7 L 21 7 L 21 7 L 21 7 Z  M 1.786 21 L 22.214 21 C 22.648 21 23 20.648 23 20.214 L 23 5.786 C 23 5.352 22.648 5 22.214 5 L 11 5 L 10.176 3.361 C 10.079 3.167 9.824 3.009 9.607 3.009 L 1.786 3.001 C 1.352 3 1 3.352 1 3.786 L 1 20.214 C 1 20.648 1.352 21 1.786 21 L 1.786 21 L 1.786 21 Z " fill-rule="evenodd" /><path d=" M 11.176 9 L 11.176 13.859 L 9.038 11.722 L 7.88 12.88 L 11.999 17 L 16.12 12.88 L 14.96 11.722 L 12.824 13.859 L 12.824 9 L 11.176 9 L 11.176 9 Z " /></svg>'
            ]
        ];
    }

    public static function filterColor( $color ) {
        if ( !$color ) {
            return '';
        }
        
        // Remove any non-hex characters and ensure proper format
        $color = preg_replace( '/[^0-9A-Fa-f]/', '', $color );
        
        // Validate hex color format (3 or 6 characters)
        if ( !preg_match( '/^[0-9A-Fa-f]{3}$|^[0-9A-Fa-f]{6}$/', $color ) ) {
            return '';
        }
        
        // Ensure 6-character format
        if ( strlen( $color ) === 3 ) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }
        
        return '#' . strtolower( $color );
    }

    public static function deleteFile( $file ) {
        if ( !file_exists( $file ) || !is_file( $file ) ) {
            // let's touch a fake file to try to `really` remove the media file.
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_touch
            touch( $file );
        }
        return wp_delete_file( $file );
    }
}
