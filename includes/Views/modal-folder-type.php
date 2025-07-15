<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="folders-modal">
    <div class="folders-dialog">
        <div class="folders-header">
            <div class="folders-title" al-if="!Modal.data.item.id"><?php esc_html_e("New Folder Type", 'folders'); ?></div>
<div class="folders-title" al-if="Modal.data.item.id"><?php esc_html_e("Edit Folder Type", 'folders'); ?></div>
            <div class="folders-cancel" al-on.click="Modal.fn.close()"><i data-feather="x"></i></div>
        </div>
        <div class="folders-data">
            <div class="folders-loader" al-attr.class.folders-active="Modal.loading"></div>
            <div class="folders-input-group">
                <input class="folders-input" al-value="Modal.data.item.title" type="text" placeholder="<?php esc_html_e("Title", 'folders'); ?>">
            </div>
            <select class="folders-select" al-select="Modal.data.item.security_profile">
                <option al-option="Modal.data.securityprofiles.none"><?php esc_html_e("None", 'folders'); ?></option>
                <option al-repeat="item in Modal.data.securityprofiles.items" al-option="item">{{item.title}}</option>
            </select>
            <div al-toggle="Modal.data.item.enabled"></div>
        </div>
        <div class="folders-footer">
            <div class="folders-btn folders-cancel" al-on.click="Modal.fn.close()"><?php esc_html_e("Close", 'folders'); ?></div>
<div class="folders-btn folders-submit" al-on.click="Modal.fn.submit()" al-if="Modal.data.changed"><?php esc_html_e("Submit", 'folders'); ?></div>
        </div>
    </div>
</div>