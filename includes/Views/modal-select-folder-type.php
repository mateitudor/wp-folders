<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="folders-modal">
    <div class="folders-dialog">
        <div class="folders-header">
            <div class="folders-title"><?php esc_html_e('Select Folder Type', 'folders'); ?></div>
            <div class="folders-cancel" al-on.click="Modal.fn.close()"><i data-feather="x"></i></div>
        </div>
        <div class="folders-data">
            <div class="folders-loader" al-attr.class.folders-active="Modal.loading"></div>
            <p><?php esc_html_e('Select a registered built-in or custom WordPress post type from the list below if you want to create a new folder type item.', 'folders'); ?></p>
            <select class="folders-select" al-select="Modal.data.selected">
                <option al-option="null"><?php esc_html_e('None', 'folders'); ?></option>
                <option al-repeat="item in Modal.data.items" al-option="item">{{item.title}}</option>
            </select>
        </div>
        <div class="folders-footer">
            <div class="folders-btn folders-cancel" al-on.click="Modal.fn.close()"><?php esc_html_e('Close', 'folders'); ?></div>
<div class="folders-btn folders-submit" al-on.click="Modal.fn.submit()" al-if="Modal.data.selected"><?php esc_html_e('Select', 'folders'); ?></div>
        </div>
    </div>
</div>