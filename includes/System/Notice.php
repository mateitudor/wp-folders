<?php
namespace Folders\System;

defined( 'ABSPATH' ) || exit;

class Notice {
    public function __construct() {
        add_action( 'init', [ $this, 'init' ] );
    }

    public function init() {
        add_action( 'admin_notices', [ $this, 'adminNotices' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
    }

    public function adminNotices() {
        if ( get_option( 'folders_dismiss_first_use_notification', false ) || ( get_current_screen() && get_current_screen()->base === 'upload' ) ) {
            return;
        }

        $classes = [
            'notice',
            'notice-info',
            'is-dismissible',
        ];
        		$msg = '<span>' . esc_html__( "Thanks for start using the plugin Folders. Let's create first folders.", 'folders' ) . ' <a href="' . esc_url( admin_url('/upload.php') ) . '">' . esc_html__( "Go to WordPress Media Library.", 'folders' ) . '</a></span>';

        printf( '<div id="folders-first-use-notification" class="%s"><p>%s</p></div>', esc_html( trim( implode( ' ', $classes ) ) ), wp_kses_post ( $msg ) );
    }

    public function enqueueScripts() {
        wp_enqueue_style( 'folders-notice', FOLDERS_PLUGIN_URL . 'assets/css/notice.css', [], FOLDERS_PLUGIN_VERSION );
        wp_enqueue_script( 'folders-notice', FOLDERS_PLUGIN_URL . 'assets/js/notice.js', ['jquery'], FOLDERS_PLUGIN_VERSION, false );
        wp_localize_script( 'folders-notice', 'folders_notice_globals', $this->getGlobals() );
    }

    private function getGlobals() {
        $globals = [
            'api' => [
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'url' => esc_url_raw( rest_url( FOLDERS_PLUGIN_REST_URL ) )
            ]
        ];
        return $globals;
    }
}