<?php
namespace Folders\System;

defined( 'ABSPATH' ) || exit;

use Folders\Models\ConfigModel;
use Folders\Models\FolderTypesModel;
use Folders\Models\HelperModel;
use Folders\Models\SecurityProfilesModel;

class Installer {
    public function __construct() {
        self::init();
    }

    private function init() {
        self::initConfig();

        if ( version_compare( get_option( 'folders_version' ), FOLDERS_PLUGIN_VERSION, '<' ) ) {
            self::updateVersion();
        }

        self::createDbTables();
        self::initDbTables();

        if ( version_compare( get_option( 'folders_db_version' ), FOLDERS_PLUGIN_DB_VERSION, '<' ) ) {
            self::updateDbVersion();
        }
    }

    private function initConfig() {
        ConfigModel::init();
    }

    private function updateVersion() {
        update_option( 'folders_version', FOLDERS_PLUGIN_VERSION );
    }

    private function getDbSchema() {
        global $wpdb;

        $charsetCollate = $wpdb->has_cap( 'collation' ) ? $wpdb->get_charset_collate() : '';

        $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );
        $tableAttachments = HelperModel::getTableName( HelperModel::ATTACHMENTS );
        $tableFolderTypes = HelperModel::getTableName( HelperModel::FOLDER_TYPES );
        $tableSecurityProfiles = HelperModel::getTableName( HelperModel::SECURITY_PROFILES );

        $tables = "
            CREATE TABLE {$tableFolderTypes} (
                id bigint unsigned NOT NULL AUTO_INCREMENT,
                type varchar(20) NOT NULL,
                title varchar(255) NOT NULL DEFAULT '',
                security_profile bigint NOT NULL DEFAULT 0,
                enabled tinyint NOT NULL DEFAULT 1,
                created_by bigint NOT NULL DEFAULT 0,
                modified_by bigint NOT NULL DEFAULT 0,
                created datetime NOT NULL,
                modified datetime NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY `type` (`type`)
		    ) {$charsetCollate};

            CREATE TABLE {$tableSecurityProfiles} (
                id bigint NOT NULL AUTO_INCREMENT,
                title varchar(255) NOT NULL DEFAULT '',
                description longtext NOT NULL,
                security_profile bigint NOT NULL DEFAULT 0,
                role varchar(255) NOT NULL DEFAULT '',
                user bigint unsigned NOT NULL DEFAULT 0,
                access_type tinyint NOT NULL DEFAULT 0,
                c tinyint NOT NULL DEFAULT 0,
                v tinyint NOT NULL DEFAULT 0,
                e tinyint NOT NULL DEFAULT 0,
                d tinyint NOT NULL DEFAULT 0,
                a tinyint NOT NULL DEFAULT 0,
                created_by bigint NOT NULL DEFAULT 0,
                modified_by bigint NOT NULL DEFAULT 0,
                created datetime NOT NULL,
                modified datetime NOT NULL,
                PRIMARY KEY (id)
            ) {$charsetCollate};

            CREATE TABLE {$tableFolders} (
                id bigint unsigned NOT NULL AUTO_INCREMENT,
                parent bigint unsigned NOT NULL DEFAULT 0,
                type varchar(20) NOT NULL DEFAULT 'attachment',
                owner bigint NOT NULL DEFAULT 0,
                title varchar(255) NOT NULL DEFAULT '',
                color varchar(7) NOT NULL DEFAULT '',
                ord bigint unsigned NOT NULL DEFAULT 0,
                count bigint unsigned NOT NULL DEFAULT 0,
                created_by bigint NOT NULL DEFAULT 0,
                modified_by bigint NOT NULL DEFAULT 0,
                created datetime NOT NULL,
                modified datetime NOT NULL,
                PRIMARY KEY (id)
		    ) {$charsetCollate};

            CREATE TABLE {$tableAttachments} (
                folder_id bigint unsigned NOT NULL,
                attachment_id bigint unsigned NOT NULL,
                UNIQUE KEY `folder_attachment` (`folder_id`,`attachment_id`)
            ) {$charsetCollate};
        ";

        return $tables;
    }

    private function createDbTables() {
        global $wpdb;
        $wpdb->hide_errors();

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( self::getDbSchema() );
    }

    private function initDbTables() {
        FolderTypesModel::init();
        SecurityProfilesModel::init();
    }

    private function updateDbVersion() {
        update_option( 'folders_db_version', FOLDERS_PLUGIN_DB_VERSION );
    }
}