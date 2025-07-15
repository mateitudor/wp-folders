<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="folders-wrap">
    <div class="folders-app-settings" id="folders-app-settings" style="display:none">
        <div class="folders-main-header">
            <div class="folders-title">Folders</div>
            <div class="folders-tabs">
                <div class="folders-tab" al-attr.class.folders-active="App.ui.tabs.fn.is('general')" al-on.click="App.ui.tabs.fn.click($element, 'general')"><span><?php esc_html_e("General", 'folders'); ?></span></div>
                <div class="folders-tab" al-attr.class.folders-active="App.ui.tabs.fn.is('permissions')" al-on.click="App.ui.tabs.fn.click($element, 'permissions')"><span><?php esc_html_e("Permissions", 'folders'); ?></span></div>
                <div class="folders-tab" al-attr.class.folders-active="App.ui.tabs.fn.is('tools')" al-on.click="App.ui.tabs.fn.click($element, 'tools')"><span><?php esc_html_e("Tools", 'folders'); ?></span></div>
            </div>
        </div>
        <div class="folders-main-container">
            <div class="folders-content">
                <div class="folders-loader folders-active" id="folders-loader">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="folders-ph-panel">
                    <div class="folders-ph-option">
                        <div class="folders-ph-description">
                            <div class="folders-ph-title"></div>
                            <div class="folders-ph-text"></div>
                        </div>
                        <div class="folders-ph-data">
                            <div class="folders-ph-control"></div>
                        </div>
                    </div>
                    <div class="folders-spacer"></div>
                    <div class="folders-ph-option">
                        <div class="folders-ph-description">
                            <div class="folders-ph-title"></div>
                            <div class="folders-ph-text"></div>
                        </div>
                        <div class="folders-ph-data">
                            <div class="folders-ph-control"></div>
                        </div>
                    </div>
                    <div class="folders-spacer"></div>
                    <div class="folders-ph-option">
                        <div class="folders-ph-description">
                            <div class="folders-ph-title"></div>
                            <div class="folders-ph-text"></div>
                        </div>
                        <div class="folders-ph-data">
                            <div class="folders-ph-control"></div>
                        </div>
                    </div>
                    <div class="folders-spacer"></div>
                    <div class="folders-ph-option">
                        <div class="folders-ph-description">
                            <div class="folders-ph-title"></div>
                            <div class="folders-ph-text"></div>
                        </div>
                        <div class="folders-ph-data">
                            <div class="folders-ph-control"></div>
                        </div>
                    </div>
                </div>
                <div class="folders-panel" al-attr.class.folders-active="App.ui.tabs.fn.is('general')">
                    <div class="folders-option">
                        <div class="folders-description">
                            <div class="folders-title"><?php esc_html_e("Default folder color", 'folders'); ?></div>
                            <div class="folders-text"><?php esc_html_e("Set the default color for all folders that don't have their own colors.", 'folders'); ?></div>
                        </div>
                        <div class="folders-data">
                            <div class="folders-color-picker-wrap"><div id="folders-default-folder-color" class="folders-color-picker" al-on.click="App.fn.config.onColorClick($event)"></div></div>
                        </div>
                    </div>
                    <div class="folders-spacer"></div>
                    <div class="folders-option">
                        <div class="folders-description">
                            <div class="folders-title"><?php esc_html_e("Disable folder counter", 'folders'); ?></div>
                            <div class="folders-text"><?php esc_html_e("Disable the display of the number of items attached to each folder.", 'folders'); ?></div>
                        </div>
                        <div class="folders-data">
                            <div al-toggle="App.data.config.disable_counter" al-on.click.stop="App.fn.config.onCheckboxChange($event)"></div>
                        </div>
                    </div>
                    <div class="folders-spacer"></div>
                    <div class="folders-option">
                        <div class="folders-description">
                            <div class="folders-title"><?php esc_html_e("Disable ajax refresh", 'folders'); ?></div>
                            <div class="folders-text"><?php esc_html_e("Disable ajax refresh in list view. Set when there are problems with using plugins along with Folders that change the media library list view.", 'folders'); ?></div>
                        </div>
                        <div class="folders-data">
                            <div al-toggle="App.data.config.disable_ajax" al-on.click.stop="App.fn.config.onCheckboxChange($event)"></div>
                        </div>
                    </div>
                    <div class="folders-spacer"></div>
                    <div class="folders-option">
                        <div class="folders-description">
                            <div class="folders-title"><?php esc_html_e("Infinite scrolling", 'folders'); ?></div>
                            <div class="folders-text"><?php esc_html_e("Enable infinite media library scrolling instead of the 'Load More' button.", 'folders'); ?></div>
                        </div>
                        <div class="folders-data">
                            <div al-toggle="App.data.config.infinite_scrolling" al-on.click.stop="App.fn.config.onCheckboxChange($event)"></div>
                        </div>
                    </div>
                    <div class="folders-spacer"></div>
                    <div class="folders-option">
                        <div class="folders-description">
                            <div class="folders-title"><?php esc_html_e("Show media details on hover", 'folders'); ?></div>
                            <div class="folders-text"><?php esc_html_e("View essential metadata, including title, size, type, date, and dimensions, by simply hovering your cursor over an image.", 'folders'); ?></div>
                        </div>
                        <div class="folders-data">
                            <div al-toggle="App.data.config.media_hover_details" al-on.click.stop="App.fn.config.onCheckboxChange($event)"></div>
                            <div class="folders-checklist folders-margin-top" al-if="App.data.config.media_hover_details">
                                <label al-repeat="detail in App.data.media_hover_details"><input type="checkbox" value="{{detail.id}}" al-on.change="App.fn.config.onMediaDetailsChange($event, detail)" al-attr.checked="App.fn.config.isMediaDetailsChecked(detail)">{{detail.title}}</label>
                            </div>
                        </div>
                    </div>
                    <div class="folders-spacer"></div>
                    <div class="folders-option">
                        <div class="folders-description">
                            <div class="folders-title"><?php esc_html_e("Disable search bar", 'folders'); ?></div>
                            <div class="folders-text"><?php esc_html_e("Disable the display of the folder search bar.", 'folders'); ?></div>
                        </div>
                        <div class="folders-data">
                            <div al-toggle="App.data.config.disable_search_bar" al-on.click.stop="App.fn.config.onCheckboxChange($event)"></div>
                        </div>
                    </div>
                    <div class="folders-spacer"></div>
                    <div class="folders-option">
                        <div class="folders-description">
                            <div class="folders-title"><?php esc_html_e("Replace media", 'folders'); ?></div>
                            <div class="folders-text"><?php esc_html_e("Adds tools to the 'Attachment details' screen that can be used to select or upload an image to replace the current image while preserving its URL and properties.", 'folders'); ?></div>
                        </div>
                        <div class="folders-data">
                            <div al-toggle="App.data.config.replace_media"></div>
                            <div class="folders-note" al-if="App.data.config.replace_media">
                                <?php esc_html_e("Note: Disable your browser cache and any WordPress caching plugins before use. Otherwise, you may find that this feature is not working properly.", 'folders'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="folders-spacer"></div>
                    <div class="folders-option">
                        <div class="folders-description">
                            <div class="folders-title"><?php esc_html_e("Auto-updates", 'folders'); ?></div>
                            <div class="folders-text"><?php esc_html_e("Enable automatic updates for this plugin. When enabled, the plugin will automatically update to new versions when they become available.", 'folders'); ?></div>
                        </div>
                        <div class="folders-data">
                            <div al-toggle="App.data.config.auto_updates" al-on.click.stop="App.fn.config.onAutoUpdateChange($event)"></div>
                            <div class="folders-note">
                                <?php esc_html_e("Note: Auto-updates can be controlled globally in WordPress Settings → General → Auto-updates, or individually in the Plugins page.", 'folders'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="folders-spacer"></div>
                    <br>
                    <br>
                    <br>
                    <div class="folders-button folders-select" al-on.click="App.fn.config.save()"><?php esc_html_e("Save", 'folders'); ?></div>
                </div>
                <div class="folders-panel" al-attr.class.folders-active="App.ui.tabs.fn.is('permissions')">
                    <div class="folders-description">
                        <div class="folders-title"><?php esc_html_e("Description", 'folders'); ?></div>
                        <div class="folders-text"><?php esc_html_e("Use this section to control who can view and edit folders. Simply create specific permissions for users and roles, then select a security profile for each folder type and apply it.", 'folders'); ?></div>
                    </div>
                    <div class="folders-spacer"></div>
                    <div class="folders-option">
                        <div class="folders-description">
                            <div class="folders-title"><?php esc_html_e("Access roles", 'folders'); ?></div>
                            <div class="folders-text"><?php esc_html_e("Only selected user roles have access to folders. These are general settings, use the permissions tab to grant users additional personal or general permissions.", 'folders'); ?></div>
                        </div>
                        <div class="folders-data">
                            <div class="folders-checklist">
                                <label al-repeat="role in App.data.roles"><input type="checkbox" value="{{role.id}}" al-on.change="App.fn.accessroles.onChange($event, role)" al-attr.checked="App.fn.accessroles.isChecked(role)">{{role.title}}</label>
                            </div>
                        </div>
                    </div>
                    <div class="folders-spacer"></div>
                    <div class="folders-option">
                        <div class="folders-description">
                            <div class="folders-title"><?php esc_html_e("Folder Types", 'folders'); ?></div>
                            <div class="folders-text"><?php esc_html_e("This table shows the types of folders (media, pages, posts, etc.) supported by the plugin. To allow a user to create and edit folders of a specific folder type, you must select a security profile for that type from the list. If you don't find the desired folder type, try adding it manually.", 'folders'); ?></div>
                        </div>
                        <div class="folders-data">
                            <div class="folders-table">
                                <div class="folders-table-header">
                                    <div class="folders-left-group">
                                        <div class="folders-btn" al-on.click="App.fn.foldertypes.create()" title="<?php esc_html_e("Add new folder type", 'folders'); ?>">
                                            <i data-feather="plus"></i>
                                        </div>
                                        <div class="folders-btn" al-on.click="App.fn.foldertypes.edit()" title="<?php esc_html_e("Edit folder type", 'folders'); ?>">
                                            <i data-feather="edit-3"></i>
                                        </div>
                                        <div class="folders-btn folders-red" al-on.click="App.fn.foldertypes.delete()" title="<?php esc_html_e("Delete selected folder types", 'folders'); ?>">
                                            <i data-feather="trash-2"></i>
                                        </div>
                                    </div>
                                    <div class="folders-right-group">
                                        <div class="folders-btn" al-on.click="App.fn.foldertypes.prev()">
                                            <i data-feather="chevron-left"></i>
                                        </div>
                                        <div class="folders-btn" al-on.click="App.fn.foldertypes.next()">
                                            <i data-feather="chevron-right"></i>
                                        </div>
                                        <div class="folders-btn" al-on.click="App.fn.foldertypes.load()" title="<?php esc_html_e("Refresh", 'folders'); ?>">
                                            <i data-feather="refresh-cw"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="folders-table-body">
                                    <table>
                                        <colgroup>
                                            <col class="folders-field-check"/>
                                            <col class="folders-field-title"/>
                                            <col class="folders-field-security-profile"/>
                                            <col class="folders-field-status"/>
                                        </colgroup>
                                        <thead>
                                        <tr>
                                            <th><input type="checkbox" al-checked="App.data.foldertypes.checked" al-on.change="App.fn.selectAll($event, App.data.foldertypes.checked, App.data.foldertypes, App.scope)"></th>
                                            <th><?php esc_html_e("Folder type", 'folders'); ?></th>
                                            <th><?php esc_html_e("Security profile", 'folders'); ?></th>
                                            <th><?php esc_html_e("Status", 'folders'); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr al-repeat="item in App.data.foldertypes.items" al-attr.class.folders-selected="App.data.foldertypes.selected == item.id" al-on.click.noprevent="App.fn.foldertypes.select(item)" al-on.dblclick="App.fn.foldertypes.dblclick(item)">
                                            <td><input type="checkbox" al-checked="item.checked" al-on.change="App.fn.selectOne($event, item.checked, App.data.foldertypes, App.scope)"></td>
                                            <td>{{item.title}}</td>
                                            <td><div class="folders-label" al-attr.class.folders-custom="item.security_profile.id > 0" al-if="item.security_profile.id">{{item.security_profile.title}}</div></td>
                                            <td><div class="folders-status" al-attr.class.folders-active="item.enabled">{{item.enabled ? '<?php esc_html_e("enabled", 'folders'); ?>' : '<?php esc_html_e("disabled", 'folders'); ?>'}}</div></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="folders-table-footer">
                                    <div class="folders-info folders-left" al-if="!App.data.foldertypes.items.length"><?php esc_html_e("The table is empty", 'folders'); ?></div>
                                    <div class="folders-info" al-if="App.data.foldertypes.items.length">{{App.data.foldertypes.view.first}} - {{App.data.foldertypes.view.last}} of {{App.data.foldertypes.view.total}}</div>
                                </div>
                                <div class="folders-loader" al-attr.class.folders-active="App.data.foldertypes.loading"></div>
                            </div>
                        </div>
                    </div>
                    <div class="folders-spacer"></div>
                                    <div class="folders-option">
                    <div class="folders-description">
                        <div class="folders-title"><?php esc_html_e("Security profiles", 'folders'); ?></div>
                        <div class="folders-text"><?php esc_html_e("This table is used to create and manage security profiles that can be selected and linked in the table above. Custom security profiles allow you to set permissions for each user or role to work with folders, including creating, viewing, editing, deleting, and attaching items to a folder.", 'folders'); ?></div>
                    </div>
                        <div class="folders-data">
                            <div class="folders-table">
                                <div class="folders-table-header">
                                    <div class="folders-left-group">
                                        <div class="folders-btn" al-on.click="App.fn.securityprofiles.create()" title="<?php esc_html_e("Add new security profile", 'folders'); ?>">
                                            <i data-feather="plus"></i>
                                        </div>
                                        <div class="folders-btn" al-on.click="App.fn.securityprofiles.edit()" title="<?php esc_html_e("Edit security profile", 'folders'); ?>">
                                            <i data-feather="edit-3"></i>
                                        </div>
                                        <div class="folders-btn folders-red" al-on.click="App.fn.securityprofiles.delete()" title="<?php esc_html_e("Delete selected security profiles", 'folders'); ?>">
                                            <i data-feather="trash-2"></i>
                                        </div>
                                    </div>
                                    <div class="folders-right-group">
                                        <div class="folders-btn" al-on.click="App.fn.securityprofiles.prev()">
                                            <i data-feather="chevron-left"></i>
                                        </div>
                                        <div class="folders-btn" al-on.click="App.fn.securityprofiles.next()">
                                            <i data-feather="chevron-right"></i>
                                        </div>
                                        <div class="folders-btn" al-on.click="App.fn.securityprofiles.load()" title="<?php esc_html_e("Refresh", 'folders'); ?>">
                                            <i data-feather="refresh-cw"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="folders-table-body">
                                    <table>
                                        <colgroup>
                                            <col class="folders-field-check"/>
                                            <col class="folders-field-title"/>
                                            <col class="folders-field-description"/>
                                        </colgroup>
                                        <thead>
                                        <tr>
                                            <th><input type="checkbox" al-checked="App.data.securityprofiles.checked" al-on.change="App.fn.selectAll($event, App.data.securityprofiles.checked, App.data.securityprofiles, App.scope)"></th>
                                            <th><?php esc_html_e("Security profile", 'folders'); ?></th>
                                            <th><?php esc_html_e("Description", 'folders'); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <tr al-repeat="item in App.data.securityprofiles.items" al-attr.class.folders-selected="App.data.securityprofiles.selected == item.id" al-on.click.noprevent="App.fn.securityprofiles.select(item)" al-on.dblclick="App.fn.securityprofiles.dblclick(item)">
                                                <td><input type="checkbox" al-checked="item.checked" al-on.change="App.fn.selectOne($event, item.checked, App.data.securityprofiles, App.scope)"></td>
                                                <td>{{item.title}}</td>
                                                <td>{{item.description}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="folders-table-footer">
                                    <div class="folders-info folders-left" al-if="!App.data.securityprofiles.items.length"><?php esc_html_e("The table is empty", 'folders'); ?></div>
                                    <div class="folders-info" al-if="App.data.securityprofiles.items.length">{{App.data.securityprofiles.view.first}} - {{App.data.securityprofiles.view.last}} of {{App.data.securityprofiles.view.total}}</div>
                                </div>
                                <div class="folders-loader" al-attr.class.folders-active="App.data.securityprofiles.loading"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="folders-panel" al-attr.class.folders-active="App.ui.tabs.fn.is('tools')">
                    <div class="folders-option" al-if="App.data.import.plugins">
                        <div class="folders-description">
                            <div class="folders-title"><?php esc_html_e("Import from other plugins", 'folders'); ?></div>
                            <div class="folders-text"><?php esc_html_e("Import folders and attachments from third-party plugins for the media library.", 'folders'); ?></div>
                        </div>
                        <div class="folders-data">
                            <div class="folders-importlist">
                                <div class="folders-importlist-item" al-repeat="plugin in App.data.import.plugins" al-attr.class.folders-lock="plugin.lock">
                                    <div>
                                        <div class="folders-title">{{plugin.name}} (by {{plugin.author}})</div>
                                        <div class="folders-description">{{plugin.folders}} folders to import</div>
                                    </div>
                                    <button class="button button-primary" al-on.click="App.fn.tools.importFromPlugin(plugin.key)"><?php esc_html_e("Import now", 'folders'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="folders-spacer" al-if="App.data.import.plugins"></div>
                    <div class="folders-option">
                        <div class="folders-description">
                            <div class="folders-title"><?php esc_html_e("Export", 'folders'); ?></div>
                            <div class="folders-text"><?php esc_html_e("The current folder structure with attachments will be exported to a CSV file.", 'folders'); ?></div>
                        </div>
                        <div class="folders-data">
                            <div class="folders-button folders-export" al-on.click="App.fn.tools.export()"><?php esc_html_e("Export Now", 'folders'); ?></div>
                            <a class="folders-download-file" download="{{App.data.export.filename}}" href="{{App.data.export.url}}" al-if="App.data.export.url"><?php esc_html_e("Download file", 'folders'); ?></a>
                        </div>
                    </div>
                    <div class="folders-spacer"></div>
                    <div class="folders-option">
                        <div class="folders-description">
                            <div class="folders-title"><?php esc_html_e("Import", 'folders'); ?></div>
                            <div class="folders-text"><?php esc_html_e("Select a CSV file with the folder structure and attachments to import.", 'folders'); ?></div>
                        </div>
                        <div class="folders-data">
                            <input class="folders-button" type="file" accept=".csv" al-on.change="App.fn.tools.onFileToImportChange($element)">
                            <div al-if="App.data.import.file">
                                <div class="folders-checklist">
                                    <label><input type="checkbox" al-checked="App.data.import.clear"><?php esc_html_e("Clearing all existing folders before import", 'folders'); ?></label>
                                    <label><input type="checkbox" al-checked="App.data.import.attachments"><?php esc_html_e("Import attachments", 'folders'); ?></label>
                                </div>
                                <div class="folders-button folders-import" al-on.click="App.fn.tools.import()"><?php esc_html_e("Import Now", 'folders'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="folders-spacer"></div>
                    <div class="folders-option">
                        <div class="folders-description">
                            <div class="folders-title"><?php esc_html_e("Folder counters recalculation", 'folders'); ?></div>
                            <div class="folders-text"><?php esc_html_e("This action will completely recalculate all item counters that are attached to folders.", 'folders'); ?></div>
                        </div>
                        <div class="folders-data">
                            <div class="folders-button folders-export" al-on.click="App.fn.tools.recalculate()"><?php esc_html_e("Recalculate", 'folders'); ?></div>
                        </div>
                    </div>
                    <div class="folders-spacer"></div>
                    <div class="folders-option">
                        <div class="folders-description">
                            <div class="folders-title"><?php esc_html_e("Clear data", 'folders'); ?></div>
                            <div class="folders-text"><?php esc_html_e("This action will deactivate the plugin Folders and delete all its data and settings and return you to the default WordPress state before installing the plugin.", 'folders'); ?></div>
                        </div>
                        <div class="folders-data">
                            <div class="folders-button folders-deactivate" al-on.click="App.fn.tools.clear()"><?php esc_html_e("Clear now", 'folders'); ?></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>