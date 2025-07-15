<?php
namespace Folders\Models;

defined( 'ABSPATH' ) || exit;

class SecurityProfilesModel {
    const COMMON_FOLDERS = -1;
    const PERSONAL_FOLDERS = -2;

    public static function init() {
        global $wpdb;
        $tableSecurityProfiles = HelperModel::getTableName( HelperModel::SECURITY_PROFILES );

        $records = [
            [ 'id' => self::COMMON_FOLDERS ],
            [ 'id' => self::PERSONAL_FOLDERS ]
        ];

        foreach ( $records as $record ) {
            // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $sql = $wpdb->prepare("
                INSERT IGNORE INTO {$tableSecurityProfiles}
                (id) VALUES (%d)",
                $record['id']
            );
            $wpdb->query( $sql );
            // phpcs:enable
        }
    }

    public static function isPredefined( $id ) {
        return ( $id == self::COMMON_FOLDERS || $id == self::PERSONAL_FOLDERS );
    }

    public static function getPredefinedTitle( $id ) {
        switch ( $id ) {
            case self::COMMON_FOLDERS: {
                		return __( "Common Folders", 'folders' );
            } break;
            case self::PERSONAL_FOLDERS: {
                		return __( "Personal Folders", 'folders' );
            } break;
        }
        return null;
    }

    public static function getPredefinedDescription( $id ) {
        switch ( $id ) {
            case self::COMMON_FOLDERS: {
                		return __( "Users can create, view, and edit each other's folders if they are granted such access.", 'folders' );
            } break;
            case self::PERSONAL_FOLDERS: {
                		return __( "Users can only create, view and edit their personal folders if they are granted such access.", 'folders' );
            } break;
        }
        return null;
    }

    public static function getPredefinedItems() {
        global $wpdb;
        $tableSecurityProfiles = HelperModel::getTableName( HelperModel::SECURITY_PROFILES );

        $sql = "
            SELECT SP.id, SP.title, SP.description
            FROM {$tableSecurityProfiles} AS SP
            WHERE IFNULL(SP.role,'')='' AND IFNULL(SP.user,0)=0 AND IFNULL(SP.security_profile,0)=0 AND id<0
            ORDER BY FIELD(id,-1,-2) DESC, modified DESC, title
        ";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $items = $wpdb->get_results( $sql, 'ARRAY_A' );

        $data = null;
        if ( !$wpdb->last_error ) {
            foreach ( $items as $key => $item ) {
                switch ( $item['id'] ) {
                    case self::COMMON_FOLDERS: {
                        $items[ $key ]['title'] = self::getPredefinedTitle( self::COMMON_FOLDERS );
                        $items[ $key ]['description'] = self::getPredefinedDescription( self::COMMON_FOLDERS );
                    } break;
                    case self::PERSONAL_FOLDERS: {
                        $items[ $key ]['title'] = self::getPredefinedTitle( self::PERSONAL_FOLDERS );
                        $items[ $key ]['description'] = self::getPredefinedDescription( self::PERSONAL_FOLDERS );
                    } break;
                }
            }
            $data['items'] = $items;
        }
        return $data;
    }

    public static function getAllItems() {
        global $wpdb;
        $tableSecurityProfiles = HelperModel::getTableName( HelperModel::SECURITY_PROFILES );

        $sql = "
            SELECT SP.id, SP.title, SP.description
            FROM {$tableSecurityProfiles} AS SP
            WHERE IFNULL(SP.role,'')='' AND IFNULL(SP.user,0)=0 AND IFNULL(SP.security_profile,0)=0
            ORDER BY FIELD(id,-1,-2) DESC, modified DESC, title
        ";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $items = $wpdb->get_results( $sql, 'ARRAY_A' );

        $data = null;
        if ( !$wpdb->last_error ) {
            foreach ( $items as $key => $item ) {
                switch ( $item['id'] ) {
                    case self::COMMON_FOLDERS: {
                        $items[ $key ]['title'] = self::getPredefinedTitle( self::COMMON_FOLDERS );
                        $items[ $key ]['description'] = self::getPredefinedDescription( self::COMMON_FOLDERS );
                    } break;
                    case self::PERSONAL_FOLDERS: {
                        $items[ $key ]['title'] = self::getPredefinedTitle( self::PERSONAL_FOLDERS );
                        $items[ $key ]['description'] = self::getPredefinedDescription( self::PERSONAL_FOLDERS );
                    } break;
                }
            }
            $data['items'] = $items;
        }
        return $data;
    }

    public static function getItems( $page, $perpage = 10 ) {
        global $wpdb;
        $tableSecurityProfiles = HelperModel::getTableName( HelperModel::SECURITY_PROFILES );

        $page = intval( $page, 10 );
        $page = $page == 0 ? 1 : $page;
        $count = intval( $perpage, 10 );
        $offset = ( $page - 1 ) * $count;

        $sql = "SELECT COUNT(*) as total FROM {$tableSecurityProfiles} WHERE IFNULL(security_profile,0)=0";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $total = $wpdb->get_var( $sql );

        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $sql = $wpdb->prepare("
            SELECT SP.id, SP.title, SP.description
            FROM {$tableSecurityProfiles} AS SP
            WHERE IFNULL(SP.security_profile,0)=0
            ORDER BY FIELD(id,-1,-2) DESC, created ASC, title 
            LIMIT %d, %d",
            $offset, $count
        );
        $items = $wpdb->get_results( $sql, 'ARRAY_A' );
        // phpcs:enable

        $data = null;
        if ( !$wpdb->last_error ) {
            foreach( $items as $key => $item ) {
                switch ( $item['id'] ) {
                    case self::COMMON_FOLDERS: {
                        $items[ $key ]['title'] = self::getPredefinedTitle( self::COMMON_FOLDERS );
                        $items[ $key ]['description'] = self::getPredefinedDescription( self::COMMON_FOLDERS );
                    } break;
                    case self::PERSONAL_FOLDERS: {
                        $items[ $key ]['title'] = self::getPredefinedTitle( self::PERSONAL_FOLDERS );
                        $items[ $key ]['description'] = self::getPredefinedDescription( self::PERSONAL_FOLDERS );
                    } break;
                }
            }

            $data['total'] = intval( $total, 10 );
            $data['pages'] = $count ? ceil( $data['total'] / $count ) : 1;
            $data['page'] = $page;
            $data['items'] = $items;
        }
        return $data;
    }

    public static function deleteItems( $ids ) {
        if ( is_array( $ids ) && count( $ids ) > 0 ) {
            $ids = array_diff( $ids, [ SecurityProfilesModel::COMMON_FOLDERS, SecurityProfilesModel::PERSONAL_FOLDERS ] );

            if ( count( $ids ) > 0 ) {
                $ids = array_map( 'intval', $ids );
                $ids = implode( ',', $ids );

                global $wpdb;
                $tableSecurityProfiles = HelperModel::getTableName( HelperModel::SECURITY_PROFILES );
                $tableFolderTypes = HelperModel::getTableName( HelperModel::FOLDER_TYPES );

                $sql = "DELETE FROM {$tableSecurityProfiles} WHERE id IN ({$ids}) OR security_profile IN ({$ids})";
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->query( $sql );

                $sql = "UPDATE {$tableFolderTypes} SET security_profile = 0 WHERE security_profile IN ({$ids})";
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->query( $sql );

                if ( !$wpdb->last_error ) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function createItem( $data ) {
        if ( is_array( $data ) ) {
            global $wpdb;
            $tableSecurityProfiles = HelperModel::getTableName( HelperModel::SECURITY_PROFILES );

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $wpdb->insert(
                $tableSecurityProfiles,
                [
                    'title' => sanitize_text_field( $data['title'] ),
                    'description' => sanitize_textarea_field( $data['description'] ),
                    'created_by' => get_current_user_id(),
                    'modified_by' => get_current_user_id(),
                    'created' => current_time( 'mysql', 1 ),
                    'modified' => current_time( 'mysql', 1 )
                ]
            );

            if ( !$wpdb->last_error ) {
                $security_profile_id = $wpdb->insert_id;
                $rights = $data['rights'];

                foreach ( $rights as $right ) {
                    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange
                    $wpdb->insert(
                        $tableSecurityProfiles,
                        [
                            'security_profile' => $security_profile_id,
                            'title' => $right['owner']['id'] ? $right['owner']['title'] : '',
                            'user' => $right['owner']['type'] == 'user' ? $right['owner']['id'] : 0,
                            'role' => $right['owner']['type'] == 'role' ? $right['owner']['id'] : '',
                            'access_type' => intval( $right['access_type']['id'], 10 ),
                            'c' => $right['actions']['create'],
                            'v' => $right['actions']['view'],
                            'e' => $right['actions']['edit'],
                            'd' => $right['actions']['delete'],
                            'a' => $right['actions']['attach'],
                            'created_by' => get_current_user_id(),
                            'modified_by' => get_current_user_id(),
                            'created' => current_time( 'mysql', 1 ),
                            'modified' => current_time( 'mysql', 1 )
                        ]
                    );
                    // phpcs:enable
                }

                return [ 'id' => $security_profile_id ];
            }
        }
        return null;
    }

    public static function getItem( $id ) {
        global $wpdb;
        $tableSecurityProfiles = HelperModel::getTableName( HelperModel::SECURITY_PROFILES );

        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $sql = $wpdb->prepare("
            SELECT SP.id, SP.title, SP.description
            FROM {$tableSecurityProfiles} AS SP
            WHERE SP.id = %d",
            $id
        );
        $item = $wpdb->get_row( $sql, 'ARRAY_A' );
        // phpcs:enable

        $data = null;
        if ( !$wpdb->last_error ) {
            $data['id'] = $item['id'];
            $data['title'] = $item['title'];
            $data['description'] = $item['description'];

            // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $sql = $wpdb->prepare("
                SELECT SP.id, SP.title, SP.user, SP.role, SP.access_type, SP.c, SP.v, SP.e, SP.d, SP.a
                FROM {$tableSecurityProfiles} AS SP
                WHERE SP.security_profile = %d",
                $id
            );
            $items = $wpdb->get_results( $sql, 'ARRAY_A' );
            // phpcs:enable

            $data['rights'] = [];
            foreach( $items as $item ) {
                $right = [];
                $right['id'] = $item['id'];
                $right['owner'] = [
                    'type' => $item['user'] ? 'user' : ( $item['role'] ? 'role' : null ),
                    'id' => $item['user'] ?: ( $item['role'] ?: null ),
                    'title' => $item['title'] ?: null
                ];
                $right['access_type'] = [
                    'id' => null,
                    'title' => null
                ];
                switch ( $item['access_type'] ) {
                    case self::COMMON_FOLDERS: {
                        $right['access_type']['id'] = strval( self::COMMON_FOLDERS );
                        $right['access_type']['title'] = self::getPredefinedTitle( self::COMMON_FOLDERS );
                    } break;
                    case self::PERSONAL_FOLDERS: {
                        $right['access_type']['id'] = strval( self::PERSONAL_FOLDERS );
                        $right['access_type']['title'] = self::getPredefinedTitle( self::PERSONAL_FOLDERS );
                    } break;
                }
                $right['actions'] = [
                    'create' => boolval( $item['c'] ),
                    'view' => boolval( $item['v'] ),
                    'edit' => boolval( $item['e'] ),
                    'delete' => boolval( $item['d'] ),
                    'attach' => boolval( $item['a'] )
                ];

                $data['rights'][] = $right;
            }
        }
        return $data;
    }

    public static function updateItem( $id, $data ) {
        if ( is_array( $data ) ) {
            global $wpdb;
            $tableSecurityProfiles = HelperModel::getTableName( HelperModel::SECURITY_PROFILES );

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->update(
                $tableSecurityProfiles,
                [
                    'title' => sanitize_text_field( $data['title'] ),
                    'description' => sanitize_textarea_field( $data['description'] ),
                    'modified_by' => get_current_user_id(),
                    'modified' => current_time( 'mysql', 1 )
                ],
                [
                    'id' => $id
                ]
            );

            if ( !$wpdb->last_error ) {
                $security_profile_id = $id;
                $rights = $data['rights'];

                // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $sql = $wpdb->prepare( "SELECT SP.id FROM {$tableSecurityProfiles} AS SP WHERE SP.security_profile=%d", $id );
                $ids_registered = array_column( $wpdb->get_results( $sql, 'ARRAY_A' ), 'id' );
                // phpcs:enable

                $ids_updated = [];
                foreach ( $rights as $right ) {
                    $id = $right['id'];
                    if ( $id < 0 ) {
                        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange
                        $wpdb->insert(
                            $tableSecurityProfiles,
                            [
                                'security_profile' => $security_profile_id,
                                'title' => $right['owner']['id'] ? $right['owner']['title'] : '',
                                'user' => $right['owner']['type'] == 'user' ? $right['owner']['id'] : 0,
                                'role' => $right['owner']['type'] == 'role' ? $right['owner']['id'] : '',
                                'access_type' => intval( $right['access_type']['id'] ),
                                'c' => $right['actions']['create'],
                                'v' => $right['actions']['view'],
                                'e' => $right['actions']['edit'],
                                'd' => $right['actions']['delete'],
                                'a' => $right['actions']['attach'],
                                'created_by' => get_current_user_id(),
                                'modified_by' => get_current_user_id(),
                                'created' => current_time( 'mysql', 1 ),
                                'modified' => current_time( 'mysql', 1 )
                            ]
                        );
                        // phpcs:enable
                    } else {
                        $ids_updated[] = $id;

                        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
                        $wpdb->update(
                            $tableSecurityProfiles,
                            [
                                'security_profile' => $security_profile_id,
                                'title' => $right['owner']['id'] ? $right['owner']['title'] : null,
                                'user' => $right['owner']['type'] == 'user' ? $right['owner']['id'] : null,
                                'role' => $right['owner']['type'] == 'role' ? $right['owner']['id'] : null,
                                'access_type' => intval( $right['access_type']['id'] ),
                                'c' => $right['actions']['create'],
                                'v' => $right['actions']['view'],
                                'e' => $right['actions']['edit'],
                                'd' => $right['actions']['delete'],
                                'a' => $right['actions']['attach'],
                                'modified_by' => get_current_user_id(),
                                'modified' => current_time( 'mysql', 1 )
                            ],
                            [
                                'id' => $id
                            ]
                        );
                        // phpcs:enable
                    }
                }

                $ids_to_delete = array_diff( $ids_registered, $ids_updated );
                if ( is_array( $ids_to_delete ) && count( $ids_to_delete ) > 0 ) {
                    $ids = implode( ',', array_map( 'absint', $ids_to_delete ) );

                    // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $sql = $wpdb->prepare( "DELETE FROM {$tableSecurityProfiles} WHERE id IN(%1s)", $ids );
                    $wpdb->query( $sql );
                    // phpcs:enable
                }

                return true;
            }
        }
        return false;
    }
}