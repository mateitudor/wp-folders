<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="folders-modal">
    <div class="folders-dialog">
        <div class="folders-header">
            <div class="folders-title" al-if="!Modal.data.item.id"><?php esc_html_e("New Security Rights", 'folders'); ?></div>
<div class="folders-title" al-if="Modal.data.item.id"><?php esc_html_e("Edit Security Rights", 'folders'); ?></div>
            <div class="folders-cancel" al-on.click="Modal.fn.close()"><i data-feather="x"></i></div>
        </div>
        <div class="folders-data">
            <div class="folders-loader" al-attr.class.folders-active="Modal.loading"></div>
            <div class="folders-input-group">
                <input class="folders-input folders-has-changer" type="text" readonly="readonly" al-value="Modal.data.item.owner.title" placeholder="<?php esc_html_e("Select user or role", 'folders'); ?>">
                <div class="folders-changer folders-icon" al-attr.class.folders-active="Modal.data.item.owner.type == 'user'" al-on.click="Modal.fn.selectUser()" title="<?php esc_html_e("Select user", 'folders'); ?>"> <i data-feather="user"></i></div>
                <div class="folders-changer folders-icon" al-attr.class.folders-active="Modal.data.item.owner.type == 'role'" al-on.click="Modal.fn.selectRole()" title="<?php esc_html_e("Select role", 'folders'); ?>"> <i data-feather="users"></i></div>
            </div>

            <select class="folders-select" al-select="Modal.data.item.access_type">
                <option al-option="Modal.data.access_types.none"><?php esc_html_e("None", 'folders'); ?></option>
                <option al-repeat="item in Modal.data.access_types.items" al-option="item">{{item.title}}</option>
            </select>
            <div class="folders-checklist">
                <label><input type="checkbox" al-checked="Modal.data.item.actions.create"><?php esc_html_e("Create", 'folders'); ?><span title="<?php esc_html_e("users can create folders and subfolders, don't forget to give the view permission too", 'folders'); ?>"><i data-feather="help-circle"></i></span></label>
                <label><input type="checkbox" al-checked="Modal.data.item.actions.view"><?php esc_html_e("View", 'folders'); ?><span title="<?php esc_html_e("users can view the folder tree", 'folders'); ?>"><i data-feather="help-circle"></i></span></label>
                <label><input type="checkbox" al-checked="Modal.data.item.actions.edit"><?php esc_html_e("Edit", 'folders'); ?><span title="<?php esc_html_e("users can edit folders (rename, drag & drop)", 'folders'); ?>"><i data-feather="help-circle"></i></span></label>
                <label><input type="checkbox" al-checked="Modal.data.item.actions.delete"><?php esc_html_e("Delete", 'folders'); ?><span title="<?php esc_html_e("users can delete folders", 'folders'); ?>"><i data-feather="help-circle"></i></span></label>
                <label><input type="checkbox" al-checked="Modal.data.item.actions.attach"><?php esc_html_e("Attach", 'folders'); ?><span title="<?php esc_html_e("users can attach items to folders, like media files, posts, pages, etc.", 'folders'); ?>"><i data-feather="help-circle"></i></span></label>
            </div>
        </div>
        <div class="folders-footer">
            <div class="folders-btn folders-cancel" al-on.click="Modal.fn.close()"><?php esc_html_e("Close", 'folders'); ?></div>
            <div class="folders-btn folders-submit" al-on.click="Modal.fn.submit()" al-if="Modal.data.changed"><?php esc_html_e("Submit", 'folders'); ?></div>
        </div>
    </div>
</div>
