<?php
namespace Folders\System;

defined( 'ABSPATH' ) || exit;

use Folders\Models\HelperModel;
use Folders\Models\ConfigModel;
use Folders\Models\ImportModel;
use Folders\Models\SecurityProfilesModel;

class Settings {
    public function __construct() {
        add_action( 'init', [ $this, 'init' ] );
    }

    public function init() {
        add_action( 'admin_menu', [ $this, 'adminMenu' ] );
        add_action( 'in_admin_header', [ $this, 'removeNotices' ] );
    }

    public function adminMenu() {
       add_submenu_page(
            'options-general.php',
            'Folders Settings',
            'Folders',
            'manage_options',
            'folders-settings',
            [ $this, 'adminMenuPageSettings' ]
        );
    }

    public function adminMenuPageSettings() {
        $page = sanitize_key( filter_input( INPUT_GET, 'page', FILTER_DEFAULT ) );
        if ( $page === 'folders-settings' ) {
            $globals = [
                'data' => [
                    'version' => FOLDERS_PLUGIN_VERSION,
                    'accesstypes' => [
                        'commonfolders' => [
                            'id' => SecurityProfilesModel::COMMON_FOLDERS,
                            'title' => SecurityProfilesModel::getPredefinedTitle( SecurityProfilesModel::COMMON_FOLDERS )
                        ],
                        'personalfolders' => [
                            'id' => SecurityProfilesModel::PERSONAL_FOLDERS,
                            'title' => SecurityProfilesModel::getPredefinedTitle( SecurityProfilesModel::PERSONAL_FOLDERS )
                        ],
                    ],
                    'plugins_to_import' => ImportModel::getPluginsToImport(),
                    'token' => ConfigModel::getToken()
                ],
                'msg' => HelperModel::getMessagesForSettings(),
                'api' => [
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                    'url' => esc_url_raw( rest_url( FOLDERS_PLUGIN_REST_URL ) )
                ]
            ];

            wp_enqueue_script( 'folders-feather-icons', FOLDERS_PLUGIN_URL . 'assets/vendor/feather-icons/feather.js', [], FOLDERS_PLUGIN_VERSION, false );
            wp_enqueue_script( 'folders-angular-light', FOLDERS_PLUGIN_URL . 'assets/vendor/angular-light/angular-light.js', [], FOLDERS_PLUGIN_VERSION, false );
            wp_enqueue_script( 'folders-cookies', FOLDERS_PLUGIN_URL . 'assets/vendor/cookie/cookie.js', [], FOLDERS_PLUGIN_VERSION, false );

            wp_enqueue_style( 'folders-notify', FOLDERS_PLUGIN_URL . 'assets/css/notify.css', [], FOLDERS_PLUGIN_VERSION );
            wp_enqueue_script( 'folders-notify', FOLDERS_PLUGIN_URL . 'assets/js/notify.js', ['jquery'], FOLDERS_PLUGIN_VERSION, false );

            wp_enqueue_style( 'folders-colorpicker', FOLDERS_PLUGIN_URL . 'assets/css/colorpicker.css', [], FOLDERS_PLUGIN_VERSION );
            wp_enqueue_script( 'folders-colorpicker', FOLDERS_PLUGIN_URL . 'assets/js/colorpicker.js', ['jquery'], FOLDERS_PLUGIN_VERSION, false );

            wp_enqueue_style( 'folders-settings', FOLDERS_PLUGIN_URL . 'assets/css/settings.css', [], FOLDERS_PLUGIN_VERSION );
            wp_enqueue_script( 'folders-settings', FOLDERS_PLUGIN_URL . 'assets/js/settings.js', ['jquery'], FOLDERS_PLUGIN_VERSION, false );
            wp_localize_script( 'folders-settings', 'folders_settings_globals', $globals);

            require_once( FOLDERS_PLUGIN_PATH . '/includes/Views/settings.php' );
        }
    }

    public function removeNotices() {
        $page = sanitize_key( filter_input( INPUT_GET, 'page', FILTER_DEFAULT ) );
        if ( $page === 'folders-settings' ) {
            remove_all_actions( 'admin_notices' );
            remove_all_actions( 'all_admin_notices' );
        }
    }
}
