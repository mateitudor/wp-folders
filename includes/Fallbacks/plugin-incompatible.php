<?php

defined( 'ABSPATH' ) || exit;

add_action(
    'admin_notices',
    function() {
        if ( !current_user_can( 'activate_plugins' ) ) {
            return;
        }

        ?>
        <div class="notice notice-error is-dismissible">
            <p>
            		<?php esc_html_e( "It looks like you have an old version of Folders installed.", 'folders' ); ?><br>
		<?php esc_html_e( "To use this new version, you must uninstall the old version completely and delete it with all data, because the new version is incompatible with the previous ones. Don't forget to back up your plugin data if necessary.", 'folders' ) ?><br>
		<?php esc_html_e( "You can delete these data here right now or if you have any questions, please contact our support team.", 'folders' ) ?>
            </p>
            			<p><a id="folders-delete-data" class="button-link-delete" href="#"><?php esc_html_e( "Delete all data of the old version of Folders and activate the new version.", 'folders' ) ?></a></p>

            <script>
                const $ = jQuery;
                 jQuery('#folders-delete-data').on('click', (e) => {
                     		if(confirm('<?php esc_html_e( "Are you sure you want to delete all data of the old version of Folders?", 'folders' ) ?>')) {
                         const url = $('#activate-folders').attr('href');
                         window.location.replace(url + '&folders_delete_old_plugin_data');
                     };
                     return false;
                 });
            </script>
        </div>
        <?php
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
);