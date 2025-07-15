<?php
namespace Folders\Models;

defined( 'ABSPATH' ) || exit;

class FoldersModel {
    public static function getUnregisteredTypes() {
        $data['items'] = [];
        		$data['items'][] = [ 'id' => 'attachment', 'title' => esc_html__( "Media", 'folders' ) ];
		$data['items'][] = [ 'id' => 'post', 'title' => esc_html__( "Posts", 'folders' ) ];
		$data['items'][] = [ 'id' => 'page', 'title' => esc_html__( "Pages", 'folders' ) ];
		$data['items'][] = [ 'id' => 'users', 'title' => esc_html__( "Users", 'folders' ) ];

        $items = get_post_types( [ 'public' => true, '_builtin' => false ], 'objects' );
        foreach ( $items as $item ) {
            $data['items'][] = [ 'id' => $item->name, 'title' => translate_user_role( $item->labels->singular_name ) ];
        }

        global $wpdb;
        $tableFolderTypes = HelperModel::getTableName( HelperModel::FOLDER_TYPES );

        $sql = "SELECT type FROM {$tableFolderTypes}";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $registered_types = array_column( $wpdb->get_results( $sql, 'ARRAY_A' ), 'type' );

        foreach ( $data['items'] as $key => $item ) {
            if ( in_array( $item['id'], $registered_types ) ) {
                unset( $data['items'][ $key ] );
            }
        }
        $data['items'] = array_values( $data['items'] );
        $data['total'] = count( $data['items'] );

        return $data;
    }

    public static function getCurrentType() {
        global $typenow;

        $php_self = isset($_SERVER['PHP_SELF']) ? sanitize_text_field(wp_unslash($_SERVER['PHP_SELF'])) : '';
        $page = basename($php_self);
        $type = 'attachment';

        switch ( $page ) {
            case 'plugins.php': $type = 'plugins'; break;
            case 'users.php': $type = 'users'; break;
            case 'edit.php': $type = $typenow; break;
        }

        return $type;
    }

    private static function sortTree( $items, &$out=[], $parent=0, $level=0) {
        foreach ( $items as $key => $item ) {
            if ( $item['parent'] == $parent ) {
                $item['level'] = $level;
                $out[] = $item;
                unset( $items[ $key ] );
                self::sortTree( $items, $out, $item['id'], $level + 1 );
            }
        }
        return $out;
    }

    private static function convertFlatToTree( &$folders, $parent = 0 ) {
        $branch = [];
        foreach( $folders as $key => $folder ) {
            if ( $folder['parent'] == $parent ) {
                $children = self::convertFlatToTree( $folders, $folder['id'] );

                if ( $children ) {
                    $folder['items'] = $children;
                }

                if ( array_key_exists( 'items', $folder ) && $folder['items'] ) {
                    $branch[] = [
                        'id' => $folder['id'],
                        'own' => $folder['owner'],
                        'title' => $folder['title'],
                        'items' => $folder['items'],
                        'color' => $folder['color'],
                        'count' => intval( $folder['count'] )
                    ];
                } else {
                    $branch[] = [
                        'id' => $folder['id'],
                        'own' => $folder['owner'],
                        'title' => $folder['title'],
                        'color' => $folder['color'],
                        'count' => intval( $folder['count'] )
                    ];
                }

                unset( $folders[$key] );
            }
        }
        return $branch;
    }

    private static function getChildFolders( $parent, &$out ) {
        global $wpdb;
        $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = $wpdb->prepare( "SELECT id FROM {$tableFolders} WHERE parent=%d ORDER BY ord", $parent );
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $items = $wpdb->get_results( $sql, 'ARRAY_A' );

        if ( $items ) {
            foreach ( $items as $item ) {
                $out[] = $item['id'];
                self::getChildFolders( $item['id'], $out );
            }
        }
    }

    private static function getParentAndChildFoldersForCopy( $parent, $level, &$out ) {
        global $wpdb;
        $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = $wpdb->prepare( "SELECT id, owner, title, type, color, ord FROM {$tableFolders} WHERE id=%d ORDER BY ord", $parent );
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $item = $wpdb->get_row( $sql, 'ARRAY_A' );

        if ( isset( $item ) ) {
            $item['level'] = $level;
            $out[] = $item;

            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $sql = $wpdb->prepare( "SELECT id, owner, title, type, color, ord FROM {$tableFolders} WHERE parent=%d ORDER BY ord", $parent );
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $items = $wpdb->get_results( $sql, 'ARRAY_A' );

            if ( $items ) {
                foreach( $items as $item ) {
                    self::getParentAndChildFoldersForCopy( $item['id'], $level + 1, $out );
                }
            }
        }
    }

    public static function getFolders( $type ) {
        $rights = UserModel::getRights( $type );

        if ( $rights && $rights['access_type'] && $rights['v'] ) {
            global $wpdb;
            $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );

            switch( $rights['access_type'] ) {
                case SecurityProfilesModel::COMMON_FOLDERS: {
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $sql = $wpdb->prepare( "SELECT id, title, parent, color, owner, count FROM {$tableFolders} WHERE type=%s AND owner=0 ORDER BY ord, created", $type );
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $folders = $wpdb->get_results( $sql, 'ARRAY_A' );

                    if ( !$wpdb->last_error ) {
                        return self::convertFlatToTree( $folders );
                    }
                } break;
                case SecurityProfilesModel::PERSONAL_FOLDERS: {
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $sql = $wpdb->prepare( "SELECT id, title, parent, color, owner, count FROM {$tableFolders} WHERE type=%s AND owner=%d ORDER BY ord, created", $type, get_current_user_id() );
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $folders = $wpdb->get_results( $sql, 'ARRAY_A' );

                    if ( !$wpdb->last_error ) {
                        return self::convertFlatToTree( $folders );
                    }
                } break;
            }
        }

        return null;
    }

    public static function createFolders( $type, $parent, $names, $color ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'FoldersModel::createFolders called with type: ' . $type . ', parent: ' . $parent . ', names: ' . print_r( $names, true ) . ', color: ' . $color );
        }
        
        $rights = UserModel::getRights( $type );
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'FoldersModel::createFolders - user rights: ' . print_r( $rights, true ) );
        }

        if ( $rights && $rights['access_type'] && $rights['c'] ) {
            $folders = [];
            global $wpdb;

            foreach ($names as $name) {
                $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );
                $owner = $rights['access_type'] == SecurityProfilesModel::COMMON_FOLDERS ? 0 : get_current_user_id();
                
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( 'FoldersModel::createFolders - table name: ' . $tableFolders . ', owner: ' . $owner );
                }

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                $wpdb->insert(
                    $tableFolders,
                    [
                        'parent' => $parent,
                        'type' => $type,
                        'owner' => $owner,
                        'title' => $name,
                        'color' => $color,
                        'ord' => PHP_INT_MAX,
                        'created_by' => get_current_user_id(),
                        'modified_by' => get_current_user_id(),
                        'created' => current_time( 'mysql', 1 ),
                        'modified' => current_time( 'mysql', 1 )
                    ]
                );

                if ( !$wpdb->last_error ) {
                    $folders[] = [
                        'id' => strval( $wpdb->insert_id ),
                        'title' => $name,
                        'color' => $color,
                        'parent' => strval( $parent )
                    ];
                    
                    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                        error_log( 'FoldersModel::createFolders - successfully created folder with ID: ' . $wpdb->insert_id );
                    }
                } else {
                    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                        error_log( 'FoldersModel::createFolders - database error: ' . $wpdb->last_error );
                    }
                    break;
                }
            }

            if ( !$wpdb->last_error ) {
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( 'FoldersModel::createFolders - returning folders: ' . print_r( $folders, true ) );
                }
                return $folders;
            }
        } else {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'FoldersModel::createFolders - user does not have create rights' );
            }
        }

        return null;
    }

    public static function updateFolders( $type, $action, $ids, $attrs ) {
        $rights = UserModel::getRights( $type );

        if ( $rights && $rights['access_type'] && $rights['e'] ) {
            if ( is_array( $ids ) && count( $ids ) > 0 ) {
                global $wpdb;
                $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );

                $folders_to_edit = [];
                $ids = implode( ',', array_map( 'intval', $ids ) );

                switch( $rights['access_type'] ) {
                    case SecurityProfilesModel::COMMON_FOLDERS: {
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
                        $sql = $wpdb->prepare( "SELECT id FROM {$tableFolders} WHERE type=%s AND owner=0 AND id IN(%1s)", $type, $ids );
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                        $folders_to_edit = array_column( $wpdb->get_results( $sql, 'ARRAY_A' ), 'id' );
                    } break;
                    case SecurityProfilesModel::PERSONAL_FOLDERS: {
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
                        $sql = $wpdb->prepare( "SELECT id FROM {$tableFolders} WHERE type=%s AND owner=%d AND id IN(%1s)", $type, get_current_user_id(), $ids );
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                        $folders_to_edit = array_column( $wpdb->get_results( $sql, 'ARRAY_A' ), 'id' );
                    } break;
                }

                if ( !empty( $folders_to_edit ) ) {
                    $ids = implode( ',', array_map( 'intval', $folders_to_edit ) );

                    switch ( $action ) {
                        case 'rename': {
                            // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                            $sql = $wpdb->prepare("
                                UPDATE {$tableFolders} 
                                SET title=%s, modified_by=%d, modified=%s 
                                WHERE id IN(%1s)",
                                $attrs['name'], get_current_user_id(), current_time( 'mysql', 1 ), $ids
                            );
                            $wpdb->query( $sql );
                            // phpcs:enable

                            if ( !$wpdb->last_error ) {
                                return $folders_to_edit;
                            }
                        } break;
                        case 'color': {
                            // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                            $sql = $wpdb->prepare("
                                UPDATE {$tableFolders} 
                                SET color=%s, modified_by=%d, modified=%s 
                                WHERE id IN(%1s)",
                                $attrs['color'], get_current_user_id(), current_time( 'mysql', 1 ), $ids
                            );
                            $wpdb->query( $sql );
                            // phpcs:enable

                            if ( !$wpdb->last_error ) {
                                return $folders_to_edit;
                            }
                        } break;
                        case 'move': {
                            $parent = null;
                            switch( $rights['access_type'] ) {
                                case SecurityProfilesModel::COMMON_FOLDERS: {
                                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                                    $sql = $wpdb->prepare( "SELECT id FROM {$tableFolders} WHERE type=%s AND owner=0 AND id=%d", $type, $attrs['parent'] );
                                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                                    $parent = $wpdb->get_var( $sql );
                                } break;
                                case SecurityProfilesModel::PERSONAL_FOLDERS: {
                                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                                    $sql = $wpdb->prepare( "SELECT id FROM {$tableFolders} WHERE type=%s AND owner=%d AND id=%d", $type, get_current_user_id(), $attrs['parent'] );
                                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                                    $parent = $wpdb->get_var( $sql );
                                } break;
                            }
                            $parent = empty( $parent ) ? 0 : $parent;

                            // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                            $sql = $wpdb->prepare("
                                UPDATE {$tableFolders}
                                SET parent=%d, modified_by=%d, modified=%s 
                                WHERE id IN(%1s)",
                                $parent, get_current_user_id(), current_time( 'mysql', 1 ), $ids
                            );
                            $wpdb->query( $sql );
                            // phpcs:enable

                            $sort = 0;
                            foreach ( $attrs['sorting'] as $id ) {
                                // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                                $sql = $wpdb->prepare("
                                    UPDATE {$tableFolders}
                                    SET ord=%d
                                    WHERE id=%d",
                                    $sort++, $id
                                );
                                $wpdb->query( $sql );
                                // phpcs:enable
                            }

                            if ( !$wpdb->last_error ) {
                                return $folders_to_edit;
                            }
                        } break;
                    }
                }
            }
        }

        return null;
    }

    public static function deleteFolders( $type, $ids, $deleteAttachments ) {
        $rights = UserModel::getRights( $type );

        if ( $rights && $rights['access_type'] && $rights['d'] ) {
            if ( is_array( $ids ) && count( $ids ) > 0 ) {
                global $wpdb;
                $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );
                $tableAttachments = HelperModel::getTableName( HelperModel::ATTACHMENTS );

                $folders_to_delete = [];
                $ids = implode( ',', array_map( 'intval', $ids ) );

                switch( $rights['access_type'] ) {
                    case SecurityProfilesModel::COMMON_FOLDERS: {
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
                        $sql = $wpdb->prepare( "SELECT id FROM {$tableFolders} WHERE type=%s AND owner=0 AND id IN(%1s)", $type, $ids );
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                        $folders_to_delete = array_column( $wpdb->get_results( $sql, 'ARRAY_A' ), 'id' );
                    } break;
                    case SecurityProfilesModel::PERSONAL_FOLDERS: {
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
                        $sql = $wpdb->prepare( "SELECT id FROM {$tableFolders} WHERE type=%s AND owner=%d AND id IN(%1s)", $type, get_current_user_id(), $ids );
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                        $folders_to_delete = array_column( $wpdb->get_results( $sql, 'ARRAY_A' ), 'id' );
                    } break;
                }

                if ( !empty( $folders_to_delete ) ) {
                    $folders = [];
                    foreach ( $folders_to_delete as $id ) {
                        $folders[] = $id;
                        self::getChildFolders( $id, $folders );
                    }
                    $folders = array_values( array_unique( $folders ) );

                    $ids = implode( ',', array_map( 'intval', $folders ) );

                    if ( $deleteAttachments ) {
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
                        $sql = $wpdb->prepare( "SELECT attachment_id as id FROM {$tableAttachments} WHERE folder_id IN(%1s)", $ids );
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                        $attachment_ids = $wpdb->get_col( $sql );

                        foreach ( $attachment_ids as $attachment_id ) {
                            wp_delete_post( $attachment_id );
                        }
                    }

                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
                    $sql = $wpdb->prepare( "DELETE FROM {$tableAttachments} WHERE folder_id IN(%1s)", $ids );
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->query( $sql );

                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
                    $sql = $wpdb->prepare( "DELETE FROM {$tableFolders} WHERE id IN(%1s)", $ids );
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $wpdb->query( $sql );

                    if( !$wpdb->last_error ) {
                        return $folders;
                    }
                }
            }
        }

        return null;
    }

    public static function copyFolder( $type, $src, $dst ) {
        $rights = UserModel::getRights( $type );

        if ( $rights && $rights['access_type'] && $rights['c'] ) {
            global $wpdb;
            $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );

            if ( $dst != 0 ) {
                switch ( $rights['access_type'] ) {
                    case SecurityProfilesModel::COMMON_FOLDERS: {
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                        $sql = $wpdb->prepare( "SELECT id FROM {$tableFolders} WHERE type=%s AND owner=0 AND id=%d", $type, $dst );
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                        $dst = $wpdb->get_var( $sql );
                    } break;
                    case SecurityProfilesModel::PERSONAL_FOLDERS: {
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                        $sql = $wpdb->prepare( "SELECT id FROM {$tableFolders} WHERE type=%s AND owner=%d AND id=%d", $type, get_current_user_id(), $dst );
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                        $dst = $wpdb->get_var( $sql );
                    } break;
                }
            }

            if ( isset( $dst ) ) {
                $folders = [];
                $folders_to_copy = [];
                self::getParentAndChildFoldersForCopy( $src, 0, $folders_to_copy );
                $folders_to_copy_length = count( $folders_to_copy );

                $folder_parents[] = $dst;
                $level = 0;
                foreach ( $folders_to_copy as $key => $folder ) {
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                    $wpdb->insert(
                        $tableFolders,
                        [
                            'owner' => $folder['owner'],
                            'title' => $folder['title'],
                            'parent' => $folder_parents[ $level ],
                            'type' => $folder['type'],
                            'color' => $folder['color'],
                            'created_by' => get_current_user_id(),
                            'modified_by' => get_current_user_id(),
                            'created' => current_time( 'mysql', 1 ),
                            'modified' => current_time( 'mysql', 1 ),
                            'ord' => PHP_INT_MAX
                        ]
                    );

                    if ( !$wpdb->last_error ) {
                        $folders[] = [
                            'id' => strval( $wpdb->insert_id ),
                            'title' => $folder['title'],
                            'parent' => $folder_parents[ $level ],
                            'color' => $folder['color']
                        ];
                    } else {
                        break;
                    }

                    $next = $key + 1;
                    if ( $next < $folders_to_copy_length ) {
                        $folder_next = $folders_to_copy[ $next ];

                        if ( $folder_next['level'] > $level ) {
                            array_push( $folder_parents, strval( $wpdb->insert_id ) );
                            $level++;
                        } else if( $folder_next['level'] < $level ) {
                            array_pop( $folder_parents );
                            $level--;
                        }
                    }
                }

                return $folders;
            }
        }

        return null;
    }

    public static function attachToFolder( $type, $id, $attachments ) {
        $rights = UserModel::getRights( $type );

        if ( $rights && $rights['access_type'] && $rights['a'] ) {
            global $wpdb;
            $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );
            $tableAttachments = HelperModel::getTableName( HelperModel::ATTACHMENTS );

            if (  is_array( $attachments ) && count( $attachments ) > 0) {
                $folder_dest = null;
                if( !( $id == -1 || $id == -2 ) ) {
                    switch( $rights['access_type'] ) {
                        case SecurityProfilesModel::COMMON_FOLDERS: {
                            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                            $sql = $wpdb->prepare( "SELECT id FROM {$tableFolders} WHERE type=%s AND owner=0 AND id=%d", $type, $id );
                            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                            $folder_dest = $wpdb->get_var( $sql );
                        } break;
                        case SecurityProfilesModel::PERSONAL_FOLDERS: {
                            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                            $sql = $wpdb->prepare( "SELECT id FROM {$tableFolders} WHERE type=%s AND owner=%d AND id=%d", $type, get_current_user_id(), $id );
                            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                            $folder_dest = $wpdb->get_var( $sql );
                        } break;
                    }
                }

                // folders to refresh after the update
                $folders_to_refresh = [];
                $ids = implode( ',', array_map( 'intval', $attachments ) );
                $owner = $rights['access_type'] == SecurityProfilesModel::COMMON_FOLDERS ? 0 : get_current_user_id();

                // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $sql = $wpdb->prepare("
                    SELECT DISTINCT A.folder_id as id 
                    FROM {$tableAttachments} AS A 
                    LEFT JOIN {$tableFolders} AS F 
                    ON F.id=A.folder_id AND F.owner=%d
                    WHERE A.attachment_id IN(%1s)",
                    $owner, $ids
                );
                $folders_to_refresh = array_column( $wpdb->get_results( $sql, 'ARRAY_A' ), 'id' );
                $folders_to_refresh[] = $folder_dest;
                // phpcs:enable

                // delete previous attachments
                // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $sql = $wpdb->prepare("
                    DELETE A.* 
                    FROM {$tableAttachments} AS A 
                    LEFT JOIN {$tableFolders} AS F 
                    ON F.id=A.folder_id AND F.owner=%d
                    WHERE A.attachment_id IN(%1s)",
                    $owner, $ids
                );
                $wpdb->query( $sql );
                // phpcs:enable

                // add new attachments
                if ( isset( $folder_dest ) && !( $folder_dest == -1 || $folder_dest == -2 ) ) {
                    foreach( $attachments as $attachment ) {
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                        $wpdb->insert(
                            $tableAttachments,
                            [
                                'folder_id' => $folder_dest,
                                'attachment_id' => $attachment
                            ]
                        );
                    }
                }

                // update the attachment count
                if ( !empty( $folders_to_refresh ) ) {
                    $ids = implode( ',', array_map( 'intval', $folders_to_refresh ) );

                    // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $sql = $wpdb->prepare("
                        UPDATE {$tableFolders} AS F 
                        SET count = (SELECT COUNT(folder_id) FROM {$tableAttachments} AS A WHERE A.folder_id=F.id) 
                        WHERE id IN(%1s)",
                        $ids
                    );
                    $wpdb->query( $sql );
                    // phpcs:enable
                }

                if ( !$wpdb->last_error ) {
                    return $folders_to_refresh;
                }
            }
        }

        return null;
    }

    public static function getAttachmentCounters( $type, $ids ) {
        $rights = UserModel::getRights( $type );

        if ( $rights && $rights['access_type'] && $rights['v'] ) {
            global $wpdb;
            $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );
            $tableAttachments = HelperModel::getTableName( HelperModel::ATTACHMENTS );

            $owner = $rights['access_type'] == SecurityProfilesModel::COMMON_FOLDERS ? 0 : get_current_user_id();

            // folders to refresh after the update
            $folders_to_refresh = [];
            if ( is_array( $ids ) && count( $ids ) > 0 ) {
                $ids = implode( ',', array_map( 'intval', $ids ) );

                // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $sql = $wpdb->prepare("
                    SELECT id, count 
                    FROM {$tableFolders} 
                    WHERE type=%s AND owner=%d AND id IN(%1s)",
                    $type, $owner, $ids
                );
                $folders_to_refresh = $wpdb->get_results( $sql, 'ARRAY_A' );
                // phpcs:enable
            }

            switch ( $type ) {
                case 'users': {
                    $sql = "SELECT COUNT(id) FROM {$wpdb->users}";
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $folders_to_refresh[] = [ 'id' => '-1', 'count' => $wpdb->get_var( $sql ) ];

                    // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $sql = $wpdb->prepare("
                        SELECT COUNT(id)
                        FROM {$wpdb->users}
                        WHERE id NOT IN (
                            SELECT attachment_id 
                            FROM {$tableAttachments} AS A 
                            LEFT JOIN {$tableFolders} AS F 
                            ON F.id = A.folder_id 
                            WHERE F.type=%s AND F.owner=%d
                        )",
                        $type, $owner
                    );
                    $folders_to_refresh[] = [ 'id' => '-2', 'count' => $wpdb->get_var( $sql ) ];
                    // phpcs:enable
                } break;
                default: {
                    $sql = $wpdb->prepare("
                        SELECT COUNT(id)
                        FROM {$wpdb->posts}
                        WHERE post_type=%s",
                        $type
                    );
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $folders_to_refresh[] = [ 'id' => '-1', 'count' => $wpdb->get_var( $sql ) ];

                    // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $sql = $wpdb->prepare("
                        SELECT COUNT(id)
                        FROM {$wpdb->posts}
                        WHERE post_type=%s AND id NOT IN (
                            SELECT attachment_id 
                            FROM {$tableAttachments} AS A 
                            LEFT JOIN {$tableFolders} AS F 
                            ON F.id = A.folder_id 
                            WHERE F.type=%s AND F.owner=%d
                        )",
                        $type, $type, $owner
                    );
                    $folders_to_refresh[] = [ 'id' => '-2', 'count' => $wpdb->get_var( $sql ) ];
                    // phpcs:enable
                } break;
            }

            return $folders_to_refresh;
        }

        return null;
    }

    public static function updateAttachmentCounters() {
        global $wpdb;
        $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );
        $tableAttachments = HelperModel::getTableName( HelperModel::ATTACHMENTS );

        $sql = "DELETE FROM {$tableAttachments} WHERE attachment_id NOT IN (
					SELECT A.attachment_id 
					FROM (SELECT attachment_id, folder_id FROM {$tableAttachments}) AS A
					LEFT JOIN {$tableFolders} AS F
					ON A.folder_id = F.id
					LEFT JOIN {$wpdb->posts} AS P
					ON A.attachment_id = P.ID AND F.type = P.post_type
					WHERE P.post_type IS NOT NULL
                )";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query( $sql );

        $sql = "UPDATE {$tableFolders} AS F SET count = (SELECT COUNT(folder_id) FROM {$tableAttachments} AS A WHERE A.folder_id=F.id)";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query( $sql );

        if ( !$wpdb->last_error )  {
            return true;
        }

        return false;
    }

    public static function getAttachments( $id, $max ) {
        global $wpdb;
        $tableAttachments = HelperModel::getTableName(HelperModel::ATTACHMENTS);
        $tableFolders = HelperModel::getTableName(HelperModel::FOLDERS);

        // Debug information (only in development)
        $debug_info = '';
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $debug_info .= 'Folders getAttachments Debug - Table: ' . $tableAttachments . ', Folder ID: ' . $id . ', Max: ' . $max . '<br>';
        }

        // Check if user has permission to access this folder
        $rights = UserModel::getRights( 'attachment' );
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $debug_info .= 'User rights: ' . print_r( $rights, true ) . '<br>';
        }
        
        if ( !$rights || !$rights['access_type'] || !$rights['v'] ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                $debug_info .= 'User does not have view permissions<br>';
                error_log( 'Folders getAttachments Debug: ' . $debug_info );
            }
            return [];
        }

        // Build the query based on user permissions
        $owner = $rights['access_type'] == SecurityProfilesModel::COMMON_FOLDERS ? 0 : get_current_user_id();
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $debug_info .= 'Owner: ' . $owner . '<br>';
        }
        
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = $wpdb->prepare( 
            "SELECT A.attachment_id as id 
             FROM {$tableAttachments} AS A 
             LEFT JOIN {$tableFolders} AS F ON F.id = A.folder_id 
             WHERE A.folder_id = %d AND F.owner = %d 
             LIMIT %d", 
            $id, $owner, $max 
        );
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $ids = $wpdb->get_col( $sql );

        // Debug information (only in development)
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $debug_info .= 'SQL: ' . $sql . '<br>';
            $debug_info .= 'Results: ' . print_r( $ids, true ) . '<br>';
            $debug_info .= 'Last Error: ' . $wpdb->last_error . '<br>';
            error_log( 'Folders getAttachments Debug: ' . $debug_info );
        }

        return $ids;
    }

    /**
     * Check database integrity and return debug information
     */
    public static function checkDatabaseIntegrity() {
        global $wpdb;
        $tableFolders = HelperModel::getTableName(HelperModel::FOLDERS);
        $tableAttachments = HelperModel::getTableName(HelperModel::ATTACHMENTS);
        
        $debug_info = [
            'folders_table' => $tableFolders,
            'attachments_table' => $tableAttachments,
            'folders_exist' => false,
            'attachments_exist' => false,
            'folders_count' => 0,
            'attachments_count' => 0,
            'user_id' => get_current_user_id(),
            'user_roles' => UserModel::getRoles(),
            'attachment_rights' => UserModel::getRights('attachment')
        ];
        
        // Check if tables exist
        $folders_table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$tableFolders}'") == $tableFolders;
        $attachments_table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$tableAttachments}'") == $tableAttachments;
        
        $debug_info['folders_exist'] = $folders_table_exists;
        $debug_info['attachments_exist'] = $attachments_table_exists;
        
        if ( $folders_table_exists ) {
            $debug_info['folders_count'] = $wpdb->get_var("SELECT COUNT(*) FROM {$tableFolders}");
        }
        
        if ( $attachments_table_exists ) {
            $debug_info['attachments_count'] = $wpdb->get_var("SELECT COUNT(*) FROM {$tableAttachments}");
        }
        
        return $debug_info;
    }

    /**
     * Debug methods for REST API
     */
    public static function foldersTableExists() {
        global $wpdb;
        $tableFolders = HelperModel::getTableName(HelperModel::FOLDERS);
        return $wpdb->get_var("SHOW TABLES LIKE '{$tableFolders}'") == $tableFolders;
    }

    public static function foldersTableVersion() {
        global $wpdb;
        $tableFolders = HelperModel::getTableName(HelperModel::FOLDERS);
        return $wpdb->get_var("SELECT version FROM {$tableFolders} LIMIT 1");
    }

    public static function foldersTableColumns() {
        global $wpdb;
        $tableFolders = HelperModel::getTableName(HelperModel::FOLDERS);
        return $wpdb->get_results("SHOW COLUMNS FROM {$tableFolders}", 'ARRAY_A');
    }

    public static function foldersTableIndexes() {
        global $wpdb;
        $tableFolders = HelperModel::getTableName(HelperModel::FOLDERS);
        return $wpdb->get_results("SHOW INDEX FROM {$tableFolders}", 'ARRAY_A');
    }

    public static function foldersTableSize() {
        global $wpdb;
        $tableFolders = HelperModel::getTableName(HelperModel::FOLDERS);
        return $wpdb->get_var("SELECT COUNT(*) FROM {$tableFolders}");
    }

    public static function foldersTableStatus() {
        global $wpdb;
        $tableFolders = HelperModel::getTableName(HelperModel::FOLDERS);
        return $wpdb->get_results("SHOW TABLE STATUS LIKE '{$tableFolders}'", 'ARRAY_A');
    }

    public static function foldersTableErrors() {
        global $wpdb;
        return $wpdb->last_error;
    }

    public static function foldersTableWarnings() {
        global $wpdb;
        return $wpdb->last_query;
    }

    public static function foldersTableDebug() {
        return self::checkDatabaseIntegrity();
    }

    public static function getAttachmentFiles( $id ) {
        global $wpdb;
        $tableAttachments = HelperModel::getTableName( HelperModel::ATTACHMENTS );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = $wpdb->prepare( "SELECT attachment_id FROM {$tableAttachments} WHERE folder_id = %d", $id );
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $ids = $wpdb->get_col( $sql );

        $files = [];
        if( count( $ids ) > 0 ) {
            $ids = implode( ',', $ids );

            // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
            $sql = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND post_id IN(%1s)", $ids );
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $files = $wpdb->get_col( $sql );
        }

        return $files;
    }

    public static function exportCSV() {
        global $wpdb;
        $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );
        $tableAttachments = HelperModel::getTableName( HelperModel::ATTACHMENTS );

        $sql = "SELECT id, owner, title, parent, type, color, ord FROM {$tableFolders} ORDER BY ord, created";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $folders = $wpdb->get_results( $sql, 'ARRAY_A' );

        foreach ( $folders as $key => $folder ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $sql = $wpdb->prepare( "SELECT attachment_id FROM {$tableAttachments} WHERE folder_id = %d", $folder['id'] );
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $folders[$key]['attachments'] = $wpdb->get_col( $sql );
        }

        return $folders;
    }

    public static function importCSV( $file, $clear = false, $attachments = false ) {
        // Validate file exists and is readable
        if ( !is_file( $file ) || !is_readable( $file ) ) {
            return false;
        }
        
        // Validate file size (max 5MB for CSV)
        $file_size = filesize( $file );
        if ( $file_size > 5 * 1024 * 1024 ) {
            return false;
        }
        
        $folders = [];
        $columns = [];
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
        $handle = \fopen( $file, 'r' );

        if ( $handle !== false ) {
            $count = 1;
            $max_rows = 10000; // Prevent memory exhaustion
            
            while ( $count <= $max_rows ) {
                $row = fgetcsv( $handle, 0 );
                if ( $count === 1 ) {
                    // Validate and sanitize column headers
                    $columns = array_map( 'sanitize_key', $row );
                    $count++;
                    continue;
                }
                if ( $row === false ) {
                    break;
                }
                
                $data = [];
                foreach ( $columns as $key => $col ) {
                    if ( isset( $row[ $key ] ) ) {
                        // Sanitize data based on column type
                        switch ( $col ) {
                            case 'id':
                            case 'parent':
                            case 'owner':
                            case 'ord':
                                $data[ $col ] = intval( $row[ $key ] );
                                break;
                            case 'title':
                                $data[ $col ] = sanitize_text_field( $row[ $key ] );
                                break;
                            case 'type':
                                $data[ $col ] = sanitize_key( $row[ $key ] );
                                break;
                            case 'color':
                                $data[ $col ] = HelperModel::filterColor( $row[ $key ] );
                                break;
                            case 'attachments':
                                $data[ $col ] = sanitize_text_field( $row[ $key ] );
                                break;
                            default:
                                $data[ $col ] = sanitize_text_field( $row[ $key ] );
                        }
                    }
                }
                
                // Validate required fields
                if ( !empty( $data['title'] ) && !empty( $data['type'] ) ) {
                    $folders[] = $data;
                }
                
                $count++;
            }
        }
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
        \fclose( $handle );

        return self::importFolders( $folders, $clear, $attachments );
    }

    public static function importFolders( $folders, $clear = false, $attachments = false ) {
        global $wpdb;
        $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );
        $tableAttachments = HelperModel::getTableName( HelperModel::ATTACHMENTS );

        if ( $clear ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query( "DELETE FROM {$tableFolders}" );
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->query( "DELETE FROM {$tableAttachments}" );
        }

        $out = [];
        self::sortTree( $folders, $out );
        $folders = $out;

        foreach ( $folders as $key => $folder ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $wpdb->insert(
                $tableFolders,
                [
                    'owner' => $folder['owner'],
                    'title' => $folder['title'],
                    'parent' => $folders[ $key ]['parent'],
                    'type' => $folder['type'],
                    'color' => $folder['color'],
                    'created_by' => get_current_user_id(),
                    'modified_by' => get_current_user_id(),
                    'created' => current_time( 'mysql', 1 ),
                    'modified' => current_time( 'mysql', 1 ),
                    'ord' => $folder['ord']
                ]
            );

            if ( !$wpdb->last_error ) {
                $id = strval( $wpdb->insert_id );

                // find the old identifier of the parent and change it to the new value
                for ( $i = $key + 1; $i < count( $folders ); $i++ ) {
                    if ( $folders[ $i ]['parent'] == $folders[ $key ]['id'] ) {
                        $folders[ $i ]['parent'] = $id;
                    }
                }

                $folders[ $key ]['id'] = $id;

                if ( $attachments && $folder['attachments'] !== '') {
                   if ( is_array( $folder['attachments'] ) ) {
                       self::importAttachments( $id, $folder['type'], $folder['owner'], $folder['attachments'] );
                   } else {
                       self::importAttachments( $id, $folder['type'], $folder['owner'], explode('|', $folder['attachments'] ) );
                   }
                }
            } else {
                return null;
            }
        }

        self::updateAttachmentCounters();

        return $folders;
    }

    public static function importAttachments( $id, $type, $owner, $attachments ) {
        if ( is_array( $attachments ) && count( $attachments ) > 0 ) {
            global $wpdb;
            $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );
            $tableAttachments = HelperModel::getTableName( HelperModel::ATTACHMENTS );

            $ids = implode( ',', array_map( 'intval', $attachments ) );

            // folders to refresh after the update
            // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $sql = $wpdb->prepare("
                SELECT DISTINCT A.folder_id as id 
                FROM {$tableAttachments} AS A 
                LEFT JOIN {$tableFolders} AS F 
                ON F.id=A.folder_id AND F.type=%s AND F.owner=%d
                WHERE A.attachment_id IN(%1s)",
                $type, $owner, $ids
            );
            $folders_to_refresh = array_column( $wpdb->get_results( $sql, 'ARRAY_A' ), 'id' );
            // phpcs:enable

            // delete previous attachments
            // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $sql = $wpdb->prepare("
                DELETE A.* 
                FROM {$tableAttachments} AS A 
                LEFT JOIN {$tableFolders} AS F 
                ON F.id=A.folder_id AND F.type=%s AND F.owner=%d
                WHERE A.attachment_id IN(%1s)",
                $type, $owner, $ids
            );
            $wpdb->query( $sql );
            // phpcs:enable

            // add new attachments
            foreach ( $attachments as $attachment ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                $wpdb->insert(
                    $tableAttachments,
                    [
                        'folder_id' => $id,
                        'attachment_id' => $attachment
                    ]
                );
            }

            // update the attachment count
            // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $sql = $wpdb->prepare("
                UPDATE {$tableFolders} AS F 
                SET count = (SELECT COUNT(folder_id) FROM {$tableAttachments} AS A WHERE A.folder_id=F.id) 
                WHERE F.id=%d",
                $id
            );
            $wpdb->query( $sql );
            // phpcs:enable

            // update the attachment count
            if ( !empty( $folders_to_refresh ) ) {
                $ids = implode( ',', array_map( 'intval', $folders_to_refresh ) );

                // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $sql = $wpdb->prepare("
                    UPDATE {$tableFolders} AS F 
                    SET count = (SELECT COUNT(folder_id) FROM {$tableAttachments} AS A WHERE A.folder_id=F.id) 
                    WHERE id IN(%1s)",
                    $ids
                );
                $wpdb->query( $sql );
                // phpcs:enable
            }
        }
    }

    public static function replaceMedia( $file, $attachment ) {
        if ( !empty( $attachment ) && is_numeric( $attachment ) ) {
            // Validate attachment exists and is accessible
            $attachment_post = get_post( $attachment );
            if ( !$attachment_post || $attachment_post->post_type !== 'attachment' ) {
                return false;
            }
            
            // Validate file exists and is readable
            if ( !is_file( $file ) || !is_readable( $file ) ) {
                return false;
            }
            
            // Validate file size (max 10MB)
            $file_size = filesize( $file );
            if ( $file_size > 10 * 1024 * 1024 ) {
                return false;
            }
            
            // Validate file type
            $allowed_types = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' );
            $file_type = mime_content_type( $file );
            if ( !in_array( $file_type, $allowed_types ) ) {
                return false;
            }
            
            $attachmentFile  = get_attached_file( $attachment );
            $attachmentPathInfo = pathinfo( $attachmentFile );
            $dirname = $attachmentPathInfo['dirname'];

            if ( !is_file( $attachmentFile ) ) {
                return false;
            }

            // Use WordPress file handling functions for security
            $upload_dir = wp_upload_dir();
            $temp_file = wp_tempnam( basename( $attachmentFile ) );
            
            if ( copy( $file, $temp_file ) ) {
                // Verify the copied file is valid
                if ( !is_file( $temp_file ) || filesize( $temp_file ) !== $file_size ) {
                    @unlink( $temp_file );
                    return false;
                }
                
                // Move to final location
                if ( rename( $temp_file, $attachmentFile ) ) {
                    if ( !function_exists( 'wp_generate_attachment_metadata' ) ) {
                        require_once( ABSPATH . 'wp-admin/includes/image.php' );
                    }

                    // remove prev thumbnails
                    $metadata = wp_get_attachment_metadata( $attachment );
                    if ( isset( $metadata ) && isset( $metadata['sizes'] ) ) {
                        foreach ( $metadata['sizes'] as $properties ) {
                            $thumbnail_path = $dirname . '/' . $properties['file'];
                            if ( is_file( $thumbnail_path ) ) {
                                HelperModel::deleteFile( $thumbnail_path );
                            }
                        }
                    }

                    $metadata = wp_generate_attachment_metadata( $attachment, $attachmentFile );
                    wp_update_attachment_metadata( $attachment, $metadata );

                    return true;
                }
            }
        }
        return false;
    }

    public static function getDownloadFoldersUrl( $type, $ids ) {
        if ( $type !== 'attachment') {
            return null;
        }

        $rights = UserModel::getRights( $type );

        if ( $rights && $rights['access_type'] && $rights['v'] ) {
            if ( is_array( $ids ) && count( $ids ) > 0 ) {
                $transientId = uniqid();
                set_transient( $transientId, $ids, HOUR_IN_SECONDS );

                return rest_url( FOLDERS_PLUGIN_REST_URL . '/folders/download/' . $transientId, is_ssl() ? 'https' : 'http' );
            }
        }

        return null;
    }

    public static function downloadFolders( $transientId ) {
        $folders = get_transient( $transientId );
        if ( $folders === false ) {
            return null;
        }
        delete_transient( $transientId );

        if ( function_exists( 'set_time_limit' ) ) {
            @set_time_limit( 0 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
        } else if ( function_exists( 'ini_set' ) ) {
            @ini_set( 'max_execution_time', 0 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
        }

        $folders_to_zip = [];
        foreach( $folders as $folder ) {
            self::getParentAndChildFoldersForCopy( $folder, 0, $folders_to_zip );
        }

        $folders = [];
        $folder_parents = [];
        $level = 0;
        $flag = false;
        foreach ( $folders_to_zip as $folderA ) {
            if ( $folderA['level'] == 0 ) {
                $flag = true;
                foreach ( $folders_to_zip as $folderB ) {
                    if ( $folderA['id'] == $folderB['id'] && $folderB['level'] !== 0 ) {
                        $flag = false;
                        break;
                    }
                }
            }
            if ( $flag ) {
                if ( $folderA['level'] == 0 ) {
                    $folder_parents = [];
                } else if ( $folderA['level'] <= $level ) {
                    $count = $level - $folderA['level'] + 1;
                    while ( $count-- ) {
                        array_pop( $folder_parents );
                    }
                }

                array_push( $folder_parents, $folderA['title'] );
                $level = $folderA['level'];

                $folderA['path'] = join( '/', $folder_parents ) . '/';

                $folders[] = $folderA;
            }
        }
        $folders_to_zip = $folders;

        $filename = 'folders-' . gmdate('d-m-Y') . '-' . uniqid() . '.zip';

        $cfg = new \ZipStream\Option\Archive();
        $cfg->setSendHttpHeaders( true );
        $cfg->setEnableZip64( false );

        $zip = new \ZipStream\ZipStream( $filename, $cfg );
        foreach ( $folders_to_zip as $folder ) {
            $zip->addFile( $folder['path'], '' );

            $files = self::getAttachmentFiles( $folder['id'] );

            $uploads = wp_get_upload_dir();
            if ( $uploads['error'] === false ) {
                foreach( $files as $file ) {
                    // Enhanced path validation to prevent path traversal
                    if ( strpos( $file, '/' ) !== 0 && !preg_match( '|^.:\\\|', $file ) ) {
                        $file_path = $uploads['basedir'] . '/' . $file;
                        
                        // Validate file path is within uploads directory
                        $real_file_path = realpath( $file_path );
                        $real_uploads_path = realpath( $uploads['basedir'] );
                        
                        if ( $real_file_path && $real_uploads_path && 
                             strpos( $real_file_path, $real_uploads_path ) === 0 && 
                             is_file( $real_file_path ) && 
                             is_readable( $real_file_path ) ) {
                            
                            $zip->addFileFromPath( $folder['path'] . \basename( $file ), $real_file_path );
                        }
                    }
                }
            }
        }
        $zip->finish();

        exit();
    }
}