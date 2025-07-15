<?php

defined( 'ABSPATH' ) || exit;

$options = [
    'folders_dismiss_first_use_notification',
    'folders_plugins_store',
    'folders_settings',
    'folders_state'
];

foreach( $options as $option ) {
    delete_option( $option );
}

delete_metadata( 'user', 0, 'folders_states', '', true );

global $wpdb;
$tables = [
    $wpdb->prefix . 'folders_folders',
    $wpdb->prefix . 'folders_attachments',
    $wpdb->prefix . 'folders_folder_types',
    $wpdb->prefix . 'folders_access',
    $wpdb->prefix . 'folders_groups',
    $wpdb->prefix . 'folders_rights'
];

foreach($tables as $table) {
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
    $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}