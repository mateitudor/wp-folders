<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="folders-modal">
    <div class="folders-dialog">
        <div class="folders-header">
            <div class="folders-title"><?php esc_html_e("Confirm", 'folders'); ?></div>
            <div class="folders-cancel" al-on.click="Modal.fn.close()"><i data-feather="x"></i></div>
        </div>
        <div class="folders-data">
            <div class="folders-loader" al-attr.class.folders-active="Modal.loading"></div>
            <p><?php esc_html_e("Are you sure you want to deactivate the plugin and delete all its data?", 'folders'); ?></p>
        </div>
        <div class="folders-footer">
            <div class="folders-btn folders-cancel" al-on.click="Modal.fn.close()"><?php esc_html_e("Cancel", 'folders'); ?></div>
<div class="folders-btn folders-delete" al-on.click="Modal.fn.submit()"><?php esc_html_e("Delete", 'folders'); ?></div>
        </div>
    </div>
</div>
