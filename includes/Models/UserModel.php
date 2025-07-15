<?php
namespace Folders\Models;

defined( 'ABSPATH' ) || exit;

class UserModel {
    const META_DEFAULT = [
        'folder' => -1, // all items
        'collapsed' => null,
        'sort' => [
            'items' => null
        ]
    ];

    public static function hasAccess() {
        $config = ConfigModel::get();
        $roles = self::getRoles();

        if ( count( array_intersect( $roles, $config['roles'] ) ) ) {
            $type = FoldersModel::getCurrentType();

            if ( ( $type && FolderTypesModel::isFolderTypeEnabled( $type ) ) ) {
                $rights = self::getRights($type);

                if ( $rights && $rights['access_type'] && ( $rights['c'] || $rights['v'] ) ) {
                    return true;
                }
            } else if ( FolderTypesModel::isFolderTypeEnabled( 'attachment' ) ) {
                $rights = self::getRights('attachment');

                if ( $rights && $rights['access_type'] && ( $rights['c'] || $rights['v'] ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    // where can be several types at once
    public static function getPrimaryType() {
        $type = FoldersModel::getCurrentType();

        if ( ( $type && FolderTypesModel::isFolderTypeEnabled( $type ) ) ) {
            $rights = self::getRights($type);

            if ( $rights && $rights['access_type'] && ( $rights['c'] || $rights['v'] ) ) {
                return $type;
            }
        }

        return 'attachment';
    }

    public static function getRoles() {
        $user = get_user_by( 'id', get_current_user_id() );
        return $user ? $user->roles : [];
    }

    public static function getRights( $type ) {
        global $wpdb;
        $tableFolderTypes = HelperModel::getTableName( HelperModel::FOLDER_TYPES );

        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $sql = $wpdb->prepare("
            SELECT security_profile
            FROM {$tableFolderTypes}
            WHERE type=%s AND enabled",
            $type
        );
        $security_profile_id = $wpdb->get_var( $sql );
        $security_profile_id = $security_profile_id ? intval( $security_profile_id, 10 ) : null;
        // phpcs:enable

        if ( $security_profile_id ) {
            if ( SecurityProfilesModel::isPredefined( $security_profile_id ) ) {
                return [
                    'access_type' => $security_profile_id,
                    'c' => true,
                    'v' => true,
                    'e' => true,
                    'd' => true,
                    'a' => true
                ];
            } else {
                $tableSecurityProfiles = HelperModel::getTableName( HelperModel::SECURITY_PROFILES );
                $user_id = get_current_user_id();

                // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $sql = $wpdb->prepare("
                    SELECT access_type, c, v, e, d, a
                    FROM {$tableSecurityProfiles}
                    WHERE security_profile=%d AND user=%d
                    ORDER BY title
                    LIMIT 1",
                    $security_profile_id, $user_id
                );
                $rights = $wpdb->get_row( $sql, 'ARRAY_A' );
                // phpcs:enable

                if ( !$rights ) {
                    $user_roles = self::getRoles();
                    $user_roles = implode( ',', $user_roles );

                    // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                    $sql = $wpdb->prepare("
                        SELECT access_type, c, v, e, d, a
                        FROM {$tableSecurityProfiles}
                        WHERE security_profile=%d AND role IN (%s)
                        ORDER BY title
                        LIMIT 1",
                        $security_profile_id, $user_roles
                    );
                    $rights = $wpdb->get_row( $sql, 'ARRAY_A' );
                    // phpcs:enable
                }

                if ( $rights ) {
                    return [
                        'access_type' => $rights['access_type'],
                        'c' => boolval( $rights['c'] ),
                        'v' => boolval( $rights['v'] ),
                        'e' => boolval( $rights['e'] ),
                        'd' => boolval( $rights['d'] ),
                        'a' => boolval( $rights['a'] )
                    ];
                }
            }
        }

        return null;
    }

    public static function getMeta( $type ) {
        $user_id = get_current_user_id();
        $user_meta = (array) get_user_meta( $user_id, 'folders_settings', true );
        return isset( $user_meta['types'][$type] ) ? array_replace_recursive( self::META_DEFAULT, $user_meta['types'][$type] ) : self::META_DEFAULT;
    }

    public static function updateMeta( $type, $meta ) {
        $user_id = get_current_user_id();
        $user_meta = (array) get_user_meta( $user_id, 'folders_settings', true );
        $user_meta['types'][$type] = $meta;

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'Folders UserModel: updateMeta called for user ' . $user_id . ', type: ' . $type . ', meta: ' . print_r( $meta, true ) );
            error_log( 'Folders UserModel: Current user meta: ' . print_r( $user_meta, true ) );
        }

        $result = update_user_meta( $user_id, 'folders_settings', $user_meta );

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'Folders UserModel: updateMeta result: ' . ($result ? 'success' : 'failed') );
        }

        return $result;
    }
}