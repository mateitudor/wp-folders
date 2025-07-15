<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="folders-modal">
    <div class="folders-dialog">
        <div class="folders-header">
            <div class="folders-title" al-if="!Modal.data.item.id"><?php esc_html_e("New Security Profile", 'folders'); ?></div>
<div class="folders-title" al-if="Modal.data.item.id"><?php esc_html_e("Edit Security Profile", 'folders'); ?></div>
            <div class="folders-cancel" al-on.click="Modal.fn.close()"><i data-feather="x"></i></div>
        </div>
        <div class="folders-data">
            <div class="folders-loader" al-attr.class.folders-active="Modal.loading"></div>
            <div class="folders-input-group">
                <input class="folders-input" al-value="Modal.data.item.title" type="text" placeholder="<?php esc_html_e("Title", 'folders'); ?>">
            </div>
            <textarea class="folders-textarea" al-value="Modal.data.item.description" placeholder="<?php esc_html_e("Description", 'folders'); ?>"></textarea>
            <div class="folders-table">
                <div class="folders-table-header">
                    <div class="folders-left-group">
                        <div class="folders-btn" al-on.click="Modal.fn.create()" title="<?php esc_html_e("Add new profile", 'folders'); ?>">
                            <i data-feather="plus"></i>
                        </div>
                        <div class="folders-btn" al-attr.class.folders-lock="!Modal.data.item.rights.selected" al-on.click="Modal.fn.edit()" title="<?php esc_html_e("Edit rights", 'folders'); ?>">
                            <i data-feather="edit-3"></i>
                        </div>
                        <div class="folders-btn folders-red" al-attr.class.folders-lock="!Modal.data.item.rights.checked" al-on.click="Modal.fn.delete()" title="<?php esc_html_e("Delete selected rights", 'folders'); ?>">
                            <i data-feather="trash-2"></i>
                        </div>
                    </div>
                    <div class="folders-right-group">
                    </div>
                </div>
                <div class="folders-table-body">
                    <table>
                        <colgroup>
                            <col class="folders-field-check"/>
                            <col class="folders-field-user-role"/>
                            <col class="folders-field-access-type"/>
                            <col class="folders-field-action"/>
                            <col class="folders-field-action"/>
                            <col class="folders-field-action"/>
                            <col class="folders-field-action"/>
                            <col class="folders-field-action"/>
                        </colgroup>
                        <thead>
                        <tr>
                            <th><input type="checkbox" al-checked="Modal.data.item.rights.checked" al-on.change="App.fn.selectAll($event, Modal.data.item.rights.checked, Modal.data.item.rights, Modal.scope)"></th>
                            <th><?php esc_html_e("User / Role", 'folders'); ?></th>
                            <th><?php esc_html_e("Access Type", 'folders'); ?></th>
                            <th class="folders-center"><?php esc_html_e("Create", 'folders'); ?></th>
                            <th class="folders-center"><?php esc_html_e("View", 'folders'); ?></th>
                            <th class="folders-center"><?php esc_html_e("Edit", 'folders'); ?></th>
                            <th class="folders-center"><?php esc_html_e("Delete", 'folders'); ?></th>
                            <th class="folders-center"><?php esc_html_e("Attach", 'folders'); ?></th>
                        </tr>
                        </thead>
                        <tbody al-if="Modal.data.item.rights.items.length">
                        <tr al-repeat="item in Modal.data.item.rights.items" al-attr.class.folders-selected="Modal.data.item.rights.selected == item.id" al-on.click.noprevent="Modal.fn.select(item)" al-on.dblclick="Modal.fn.dblclick(item)">
                            <td><input type="checkbox" al-checked="item.checked" al-on.change="App.fn.selectOne($event, item.checked, Modal.data.item.rights, Modal.scope)"></td>
                            <td><div class="folders-icon" al-if="item.owner.id" al-attr.class.folders-user="item.owner.type=='user'" al-attr.class.folders-role="item.owner.type=='role'"></div><span>{{item.owner.title}}</span></td>
                            <td><div class="folders-label" al-if="item.access_type.id">{{item.access_type.title}}</div></td>
                            <td><input type="checkbox" al-checked="item.actions.create"></td>
                            <td><input type="checkbox" al-checked="item.actions.view"></td>
                            <td><input type="checkbox" al-checked="item.actions.edit"></td>
                            <td><input type="checkbox" al-checked="item.actions.delete"></td>
                            <td><input type="checkbox" al-checked="item.actions.attach"></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="folders-table-footer">
                    <div class="folders-info folders-left" al-if="!Modal.data.item.rights.items.length"><?php esc_html_e("The table is empty", 'folders'); ?></div>
                </div>
            </div>
        </div>
        <div class="folders-footer">
            <div class="folders-btn folders-cancel" al-on.click="Modal.fn.close()"><?php esc_html_e("Close", 'folders'); ?></div>
            <div class="folders-btn folders-submit" al-on.click="Modal.fn.submit()" al-if="Modal.data.changed"><?php esc_html_e("Submit", 'folders'); ?></div>
        </div>
    </div>
</div>
