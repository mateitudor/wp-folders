<?php
namespace Folders\Models;

defined( 'ABSPATH' ) || exit;

class ImportModel {
    public const plugins = [
        'RML' => [
            'name' => 'WordPress Real Media Library',
            'author' => 'devowl.io',
            'db' => 'realmedialibrary',
            'folders' => 0
        ],
        'FB' => [
            'name' => 'FileBird',
            'author' => 'NinjaTeam',
            'db' => 'fbv',
            'folders' => 0
        ]
    ];

    public static function getPluginsToImport() {
        $plugins = [];

        foreach ( self::plugins as $key => $plugin ) {
            $count = self::getFolderCount( $key );

            if ( \intval( $count ) > 0 ) {
                $plugin['folders'] = \intval( $count );
                $plugin['key'] = $key;
                $plugins[] = $plugin;
            }
        }

        return count( $plugins ) > 0 ? $plugins : null;
    }

    public static function getFolderCount( $plugin ) {
        global $wpdb;
        switch ( $plugin ) {
            case 'RML':
            case 'FB': {
                $table = $wpdb->prefix . self::plugins[ $plugin ]['db'];
                if ( self::isTableExist( $table ) ) {
                    $sql = "SELECT COUNT(id) FROM {$table} WHERE type=0";
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    return $wpdb->get_var( $sql );
                }
            } break;
        }
        return 0;
    }

    public static function isTableExist( $table ) {
        global $wpdb;
        $sql = "SHOW TABLES LIKE '{$table}'";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_var( $sql ) == $table;
    }

    public static function importPluginData( $key ) {
        if ( !array_key_exists( $key, self::plugins ) ) {
            return false;
        }

        global $wpdb;
        $plugin = self::plugins[ $key ];
        $table = $wpdb->prefix . $plugin['db'];
        if ( !self::isTableExist( $table ) ) {
            return false;
        }

        switch ( $key ) {
            case 'RML':
            case 'FB': {
                $sql = "SELECT id, parent, name AS title, ord FROM {$table} WHERE type=0 ORDER BY ord";
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $folders = $wpdb->get_results( $sql, 'ARRAY_A' );

                $tableAttachments = $table . ( $key == 'RML' ? '_posts' : '_attachment_folder' );
                $doAttachments = self::isTableExist( $tableAttachments );
                $sql = ( $key == 'RML' ? "SELECT attachment as id FROM {$tableAttachments} WHERE fid = %d" : "SELECT attachment_id as id FROM {$tableAttachments} WHERE folder_id = %d" );

                foreach ( $folders as $key => $folder ) {
                    $folders[ $key ]['id'] = $folder['id'];
                    $folders[ $key ]['owner'] = 0;
                    $folders[ $key ]['title'] = $folder['title'];
                    $folders[ $key ]['parent'] = $folder['parent'] < 0 ? '0' : $folder['parent'];
                    $folders[ $key ]['type'] = 'attachment';
                    $folders[ $key ]['color'] = '';
                    $folders[ $key ]['ord'] = $folder['ord'];

                    if ( $doAttachments ) {
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                        $folders[ $key ]['attachments'] = $wpdb->get_col( $wpdb->prepare( $sql, $folder['id'] ) );
                    }
                }

                $folders = FoldersModel::importFolders( $folders, false, $doAttachments );
                return ( $folders !== null );
            } break;
        }

        return false;
    }
}
