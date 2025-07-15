<?php
namespace Folders\Models;

defined( 'ABSPATH' ) || exit;

class FolderTypesModel {
    public static function init() {
        global $wpdb;
        $tableFolderTypes = HelperModel::getTableName( HelperModel::FOLDER_TYPES );

        $records = [
            [ 'type' => 'attachment', 'title' => 'Media', 'security_profile' => SecurityProfilesModel::COMMON_FOLDERS, 'enabled' => true ],
            [ 'type' => 'post', 'title' => 'Posts', 'security_profile' => null, 'enabled' => false ],
            [ 'type' => 'page', 'title' => 'Pages', 'security_profile' => null, 'enabled' => false ],
            [ 'type' => 'users', 'title' => 'Users', 'security_profile' => SecurityProfilesModel::COMMON_FOLDERS, 'enabled' => true ]
        ];

        foreach( $records as $record ) {
            // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $sql = $wpdb->prepare("
                INSERT IGNORE INTO {$tableFolderTypes}
                (type,title,security_profile,enabled) VALUES (%s,%s,%d,%d)",
                $record['type'], $record['title'], $record['security_profile'], $record['enabled']
            );
            $wpdb->query( $sql );
            // phpcs:enable
        }
    }

    public static function getItems( $page, $perpage = 10 ) {
        global $wpdb;
        $tableFolderTypes = HelperModel::getTableName( HelperModel::FOLDER_TYPES );
        $tableSecurityProfiles = HelperModel::getTableName( HelperModel::SECURITY_PROFILES );

        $page = intval( $page );
        $count = intval( $perpage );
        $offset = ( $page - 1 ) * $count;

        $sql = "SELECT COUNT(*) as total FROM {$tableFolderTypes}";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $total = $wpdb->get_var( $sql );

        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $sql = $wpdb->prepare("
            SELECT FC.id, FC.type, FC.title, FC.enabled, SP.id AS security_profile_id, SP.title AS security_profile_title, FC.created, FC.modified
            FROM {$tableFolderTypes} AS FC
            LEFT JOIN {$tableSecurityProfiles} AS SP
            ON FC.security_profile = SP.id
            ORDER BY FC.created ASC, FC.title 
            LIMIT %d, %d",
            $offset, $count
        );
        $items = $wpdb->get_results( $sql, 'ARRAY_A' );
        // phpcs:enable

        $data = null;
        if ( !$wpdb->last_error ) {
            foreach( $items as $key => $item ) {
                $items[ $key ]['enabled'] = boolval( $item['enabled'] );
                $items[ $key ]['security_profile'] = [
                    'id' => $item['security_profile_id'] ?: null,
                    'title' => $item['security_profile_id'] < 0 ? SecurityProfilesModel::getPredefinedTitle( $item['security_profile_id'] ) : $item['security_profile_title']
                ];

                unset( $items[ $key ]['security_profile_id'], $items[ $key ]['security_profile_title'] );
            }
            $data['total'] = intval( $total );
            $data['pages'] = $count ? ceil( $data['total'] / $count ) : 1;
            $data['page'] = $page;
            $data['items'] = $items;
        }
        return $data;
    }

    public static function createItem( $data ) {
        if ( is_array( $data ) ) {
            global $wpdb;
            $tableFolderTypes = HelperModel::getTableName( HelperModel::FOLDER_TYPES );

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $wpdb->insert(
                $tableFolderTypes,
                [
                    'type' => sanitize_text_field( $data['type'] ),
                    'title' => sanitize_text_field( $data['title'] ),
                    'security_profile' => intval( $data['security_profile']['id'] ),
                    'enabled' => rest_sanitize_boolean( $data['enabled'] ),
                    'created_by' => get_current_user_id(),
                    'modified_by' => get_current_user_id(),
                    'created' => current_time( 'mysql', 1 ),
                    'modified' => current_time( 'mysql', 1 )
                ]
            );

            if ( !$wpdb->last_error ) {
                return [ 'id' => $wpdb->insert_id ];
            }
        }
        return null;
    }

    public static function deleteItems( $ids ) {
        if ( is_array( $ids ) && count( $ids ) > 0 ) {
            $ids = implode( ',', array_map( 'intval', $ids ) );

            global $wpdb;
            $tableFolderTypes = HelperModel::getTableName( HelperModel::FOLDER_TYPES );

            $sql = "DELETE FROM {$tableFolderTypes} WHERE id IN ({$ids})";
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query( $sql );

            if( !$wpdb->last_error ) {
                return true;
            }
        }
        return false;
    }

    public static function getItem( $id ) {
        global $wpdb;
        $tableFolderTypes = HelperModel::getTableName( HelperModel::FOLDER_TYPES );
        $tableSecurityProfiles = HelperModel::getTableName( HelperModel::SECURITY_PROFILES );

        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $sql = $wpdb->prepare("
            SELECT FC.id, FC.type, FC.title, FC.enabled, SP.id AS security_profile_id, SP.title AS security_profile_title
            FROM {$tableFolderTypes} AS FC
            LEFT JOIN {$tableSecurityProfiles} AS SP
            ON FC.security_profile = SP.id
            WHERE FC.id = %d",
            $id
        );
        $item = $wpdb->get_row( $sql, 'ARRAY_A' );
        // phpcs:enable

        $data = null;
        if ( !$wpdb->last_error ) {
            $data['id'] = $item['id'];
            $data['type'] = $item['type'];
            $data['title'] = $item['title'];
            $data['enabled'] = boolval( $item['enabled'] );
            $data['security_profile'] = [
                'id' => $item['security_profile_id'] ?: null,
                'title' => $item['security_profile_id'] < 0 ? SecurityProfilesModel::getPredefinedTitle( $item['security_profile_id'] ) : $item['security_profile_title']
            ];
        }
        return $data;
    }

    public static function updateItem( $id, $data ) {
        if ( is_array( $data ) && count( $data ) > 0 ) {
            global $wpdb;
            $tableFolderTypes = HelperModel::getTableName( HelperModel::FOLDER_TYPES );

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->update(
                $tableFolderTypes,
                [
                    'title' => sanitize_text_field( $data['title'] ),
                    'security_profile' => intval( $data['security_profile']['id'] ),
                    'enabled' => rest_sanitize_boolean ( $data['enabled'] ),
                    'modified_by' => get_current_user_id(),
                    'modified' => current_time( 'mysql', 1 )
                ],
                [
                    'id' => $id
                ]
            );

            if ( !$wpdb->last_error ) {
                return true;
            }
        }
        return false;
    }

    public static function isFolderTypeEnabled( $type ) {
        global $wpdb;
        $tableFolderTypes = HelperModel::getTableName( HelperModel::FOLDER_TYPES );

        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $sql = $wpdb->prepare("
            SELECT enabled
            FROM {$tableFolderTypes}
            WHERE type=%s",
            $type
        );
        return boolval( $wpdb->get_var( $sql ) );
        // phpcs:enable
    }
}
