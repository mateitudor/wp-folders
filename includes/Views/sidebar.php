<?php
defined( 'ABSPATH' ) || exit;
?>
<div id="folders-toolbar" class="folders-toolbar">
    <div class="folders-left-group">
        <div id="folders-btn-create" class="folders-btn folders-active" title="<?php esc_html_e('create folder', 'folders'); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d=" M 21 7 L 10.393 7 C 10.176 7 9.921 6.842 9.824 6.648 L 9 5 L 3.098 4.991 L 3 19 L 21 19 L 21 7 L 21 7 L 21 7 Z  M 1.786 21 L 22.214 21 C 22.648 21 23 20.648 23 20.214 L 23 5.786 C 23 5.352 22.648 5 22.214 5 L 11 5 L 10.176 3.361 C 10.079 3.167 9.824 3.009 9.607 3.009 L 1.786 3.001 C 1.352 3 1 3.352 1 3.786 L 1 20.214 C 1 20.648 1.352 21 1.786 21 L 1.786 21 Z  M 13 12 L 13 9 L 11 9 L 11 12 L 8 12 L 8 14 L 11 14 L 11 17 L 13 17 L 13 14 L 16 14 L 16 12 L 13 12 L 13 12 Z " fill-rule="evenodd"/>
            </svg>
        </div>
    </div>
    <div class="folders-right-group">
        <div id="folders-btn-sort" class="folders-btn folders-active" title="<?php esc_html_e('sort folder items', 'folders'); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d=" M 5.151 2.731 L 4.971 3.272 L 3.375 7.674 L 3.349 7.674 L 3.349 7.726 L 2.576 9.863 L 2.525 9.992 L 2.525 10.97 L 4.173 10.97 L 4.173 10.275 L 4.508 9.322 L 7.134 9.322 L 7.468 10.275 L 7.468 10.97 L 9.116 10.97 L 9.116 9.992 L 9.065 9.863 L 8.292 7.726 L 8.292 7.674 L 8.267 7.674 L 6.67 3.272 L 6.49 2.731 L 5.151 2.731 Z  M 16.532 2.731 L 16.532 18.128 L 14.394 15.991 L 13.236 17.149 L 16.763 20.703 L 17.355 21.269 L 17.948 20.703 L 21.475 17.149 L 20.316 15.991 L 18.179 18.128 L 18.179 2.731 L 16.532 2.731 Z  M 5.821 5.743 L 6.516 7.674 L 5.125 7.674 L 5.821 5.743 Z  M 2.525 12.618 L 2.525 14.266 L 7.108 14.266 L 2.757 18.617 L 2.525 18.875 L 2.525 20.857 L 9.116 20.857 L 9.116 19.209 L 4.533 19.209 L 8.885 14.858 L 9.116 14.6 L 9.116 12.618 L 2.525 12.618 Z "/>
            </svg>
        </div>
    </div>
</div>

<div id="folders-notice-create" class="folders-notice">
    <?php esc_html_e('Click the "Create" button above to add your first folder, then start drag & drop items.', 'folders'); ?>
</div>

<div id="folders-form-create" class="folders-form">
    <div class="folders-header">
        <div class="folders-title"><?php esc_html_e('Add New Folders', 'folders'); ?></div>
        <div class="folders-close">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d=" M 7.734 6.281 L 6.328 7.688 L 10.609 11.969 L 6.266 16.313 L 7.672 17.719 L 12.016 13.375 L 16.328 17.688 L 17.734 16.281 L 13.422 11.969 L 17.672 7.719 L 16.266 6.313 L 12.016 10.563 L 7.734 6.281 Z " />
            </svg>
        </div>
    </div>
    <div class="folders-data">
        <div class="folders-row">
            <input id="folders-folder-name" class="folders-text" type="text" placeholder="Folder 1, Folder 2, etc." value="">
            <div class="folders-color-picker-wrap"><div id="folders-folder-color" class="folders-color-picker"></div></div>
        </div>
        <div class="folders-row">
            <select id="folders-folder-parent" class="folders-select"></select>
        </div>
    </div>
    <div class="folders-footer">
        <div class="folders-btn folders-close"><?php esc_html_e('Cancel', 'folders'); ?></div>
<div class="folders-btn folders-submit"><?php esc_html_e('Create', 'folders'); ?></div>
    </div>
</div>

<div id="folders-form-delete" class="folders-form">
    <div class="folders-header">
        <div class="folders-title"><?php esc_html_e('Delete Folders', 'folders'); ?></div>
        <div class="folders-close">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d=" M 7.734 6.281 L 6.328 7.688 L 10.609 11.969 L 6.266 16.313 L 7.672 17.719 L 12.016 13.375 L 16.328 17.688 L 17.734 16.281 L 13.422 11.969 L 17.672 7.719 L 16.266 6.313 L 12.016 10.563 L 7.734 6.281 Z " />
            </svg>
        </div>
    </div>
    <div class="folders-data">
        <div class="folders-row">
            <p><?php esc_html_e('Are you sure you want to delete selected folders and all subfolders?', 'folders'); ?></p>
<p><label><input type="checkbox" id="folders-delete-attachments" /><?php esc_html_e('Delete items attached to selected folders too', 'folders'); ?></label></p>
        </div>
    </div>
    <div class="folders-footer">
        <div class="folders-btn folders-close"><?php esc_html_e('Cancel', 'folders'); ?></div>
<div class="folders-btn folders-submit folders-red"><?php esc_html_e('Delete', 'folders'); ?></div>
    </div>
</div>

<div id="folders-form-replace-media" class="folders-form">
    <div class="folders-loader">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="folders-header">
        <div class="folders-title"><?php esc_html_e('Upload a new file', 'folders'); ?></div>
        <div class="folders-close">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d=" M 7.734 6.281 L 6.328 7.688 L 10.609 11.969 L 6.266 16.313 L 7.672 17.719 L 12.016 13.375 L 16.328 17.688 L 17.734 16.281 L 13.422 11.969 L 17.672 7.719 L 16.266 6.313 L 12.016 10.563 L 7.734 6.281 Z " />
            </svg>
        </div>
    </div>
    <div class="folders-data">
        <div class="folders-row">
            <form class="folders-file-drop-zone">
                <input class="folders-file-upload" type="file" name="file" accept="image/*" />
                <div class="folders-image-preview-wrap">
                    <img src="#" class="folders-image-preview">
                </div>
                <div class="folders-start">
                    <p><strong><?php esc_html_e('Drop file here', 'folders'); ?></strong></p>
<p><?php esc_html_e('or', 'folders'); ?></p>
<button type="button" class="button-primary folders-file-select"><?php esc_html_e('Select file', 'folders'); ?></button><br>
                </div>
            </form>
        </div>
    </div>
    <div class="folders-footer">
        <div class="folders-btn folders-close"><?php esc_html_e('Cancel', 'folders'); ?></div>
<div class="folders-btn folders-submit folders-hidden"><?php esc_html_e('Replace', 'folders'); ?></div>
    </div>
</div>

<div id="folders-form-sort" class="folders-form">
    <div class="folders-header">
        <div class="folders-title"><?php esc_html_e('Sort Folder Items', 'folders'); ?></div>
        <div class="folders-close">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d=" M 7.734 6.281 L 6.328 7.688 L 10.609 11.969 L 6.266 16.313 L 7.672 17.719 L 12.016 13.375 L 16.328 17.688 L 17.734 16.281 L 13.422 11.969 L 17.672 7.719 L 16.266 6.313 L 12.016 10.563 L 7.734 6.281 Z " />
            </svg>
        </div>
    </div>
    <div class="folders-data">
        <ul class="folders-sort-list">
            <li>
                <div class="folders-sort-title"><?php esc_html_e('By Name', 'folders'); ?></div>
                <div class="folders-sort-types">
                    <div class="folders-sort-type" data="name-asc" title="sort by ascending"><span class="dashicons dashicons-arrow-up"></span></div>
                    <div class="folders-sort-type" data="name-desc" title="sort by descending"><span class="dashicons dashicons-arrow-down"></span></div>
                </div>
            </li>
            <li>
                <div class="folders-sort-title"><?php esc_html_e('By Date', 'folders'); ?></div>
                <div class="folders-sort-types">
                    <div class="folders-sort-type" data="date-asc" title="sort by ascending"><span class="dashicons dashicons-arrow-up"></span></div>
                    <div class="folders-sort-type" data="date-desc" title="sort by descending"><span class="dashicons dashicons-arrow-down"></span></div>
                </div>
            </li>
            <li>
                <div class="folders-sort-title"><?php esc_html_e('By Modified', 'folders'); ?></div>
                <div class="folders-sort-types">
                    <div class="folders-sort-type" data="mod-asc" title="sort by ascending"><span class="dashicons dashicons-arrow-up"></span></div>
                    <div class="folders-sort-type" data="mod-desc" title="sort by descending"><span class="dashicons dashicons-arrow-down"></span></div>
                </div>
            </li>
            <li>
                <div class="folders-sort-title"><?php esc_html_e('By Author', 'folders'); ?></div>
                <div class="folders-sort-types">
                    <div class="folders-sort-type" data="author-asc" title="sort by ascending"><span class="dashicons dashicons-arrow-up"></span></div>
                    <div class="folders-sort-type" data="author-desc" title="sort by descending"><span class="dashicons dashicons-arrow-down"></span></div>
                </div>
            </li>
        </ul>
    </div>
    <div class="folders-footer">
        <div class="folders-btn folders-close"><?php esc_html_e('Close', 'folders'); ?></div>
    </div>
</div>

<div id="folders-panel" class="folders-panel">
    <div id="folders-predefined-tree" class="folders-predefined-tree">
        <div class="folders-tree-nodes">
            <div class="folders-tree-node">
                <div class="folders-tree-item folders-folder" data-id="-1">
                    <div class="folders-tree-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M 1.786 21 L 22.214 21 C 22.648 21 23 20.648 23 20.214 L 23 5.786 C 23 5.352 22.648 5 22.214 5 L 11 5 L 10.176 3.361 C 10.079 3.167 9.824 3.009 9.607 3.009 L 1.786 3.001 C 1.352 3 1 3.352 1 3.786 L 1 20.214 C 1 20.648 1.352 21 1.786 21 Z " fill="currentColor" style="filter:invert(0.05) brightness(0.6)"/>
                            <path d="M 1,7 V 20.159624 C 1,20.624144 1.3511577,21 1.7851562,21 H 22.214844 C 22.648841,21 23,20.624144 23,20.159624 V 7.08571 Z " fill="currentColor" style="filter:invert(0.05)"/>
                        </svg>
                    </div>
                    <div class="folders-tree-title"><?php esc_html_e('All items', 'folders'); ?></div>
                    <div class="folders-tree-label">0</div>
                </div>
            </div>
            <div class="folders-tree-node">
                <div class="folders-tree-item folders-folder" data-id="-2">
                    <div class="folders-tree-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M 1.786 21 L 22.214 21 C 22.648 21 23 20.648 23 20.214 L 23 5.786 C 23 5.352 22.648 5 22.214 5 L 11 5 L 10.176 3.361 C 10.079 3.167 9.824 3.009 9.607 3.009 L 1.786 3.001 C 1.352 3 1 3.352 1 3.786 L 1 20.214 C 1 20.648 1.352 21 1.786 21 Z " fill="currentColor" style="filter:invert(0.05) brightness(0.6)"/>
                            <path d="M 1,7 V 20.159624 C 1,20.624144 1.3511577,21 1.7851562,21 H 22.214844 C 22.648841,21 23,20.624144 23,20.159624 V 7.08571 Z " fill="currentColor" style="filter:invert(0.05)"/>
                        </svg>
                    </div>
                    <div class="folders-tree-title"><?php esc_html_e('Uncategorized', 'folders'); ?></div>
                    <div class="folders-tree-label">0</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="folders-panel-search" class="folders-panel">
    <div id="folders-search" class="folders-search">
        <input id="folders-search-input" class="folders-search-input" placeholder="<?php esc_html_e('Search folders...', 'folders'); ?>" type="text">
        <div class="folders-search-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d=" M 18.341 15.876 L 22.138 19.69 L 20.676 21.041 L 20.676 21.041 L 16.941 17.29 C 15.58 18.361 13.864 19 12 19 C 7.585 19 4 15.415 4 11 L 4 11 C 4 6.585 7.585 3 12 3 C 16.415 3 20 6.585 20 11 C 20 12.835 19.381 14.526 18.341 15.876 Z  M 6 11 C 6 7.689 8.689 5 12 5 C 15.311 5 18 7.689 18 11 C 18 14.311 15.311 17 12 17 C 8.689 17 6 14.311 6 11 L 6 11 Z " fill-rule="evenodd" fill="currentColor"/>
            </svg>
        </div>
        <div id="folders-search-clear" class="folders-search-clear">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d=" M 7.734 6.281 L 6.328 7.688 L 10.609 11.969 L 6.266 16.313 L 7.672 17.719 L 12.016 13.375 L 16.328 17.688 L 17.734 16.281 L 13.422 11.969 L 17.672 7.719 L 16.266 6.313 L 12.016 10.563 L 7.734 6.281 Z " fill="currentColor" />
            </svg>
        </div>
    </div>
</div>

<div id="folders-panel-tree" class="folders-panel-tree">
    <div id="folders-tree" class="folders-tree"></div>
</div>
