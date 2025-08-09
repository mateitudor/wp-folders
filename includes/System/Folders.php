<?php
namespace Folders\System;

defined( 'ABSPATH' ) || exit;

use Folders\Models\SecurityProfilesModel;
use Folders\Models\FoldersModel;
use Folders\Models\HelperModel;
use Folders\Models\ConfigModel;
use Folders\Models\UserModel;

class Folders {
    public function __construct() {
        add_action( 'init', [ $this, 'init' ] );
    }

    public function init() {
        $config = ConfigModel::get();
        if ( array_key_exists( 'infinite_scrolling', $config ) && $config['infinite_scrolling'] ) {
            add_filter( 'media_library_infinite_scrolling', '__return_true' );
        }

        if ( array_key_exists( 'replace_media', $config ) && $config['replace_media'] ) {
            add_action( 'edit_attachment', [ $this, 'replaceMediaEditAttachment' ] );
            add_filter( 'attachment_fields_to_edit', [ $this, 'replaceMediaAttachmentFields' ], null, 2 );
        }

        if ( array_key_exists( 'media_hover_details', $config ) && $config['media_hover_details'] ) {
            add_filter( 'wp_prepare_attachment_for_js', [ $this, 'prepareAttachment' ], 99, 5 );
        }

        add_action( 'admin_enqueue_scripts', [ $this, 'sidebarScripts' ] );
        if ( defined( 'AVADA_VERSION' ) ) {
            add_action( 'fusion_enqueue_live_scripts', [ $this, 'sidebarScripts' ] );
        } else if ( defined( 'ELEMENTOR_VERSION' ) ) {
            add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'sidebarScripts' ] );
        } else if ( defined( 'BRIZY_VERSION' ) ) {
            add_action( 'brizy_editor_enqueue_scripts', [ $this, 'sidebarScripts' ] );
        } else {
            add_action( 'wp_enqueue_scripts', [ $this, 'sidebarScripts' ] );
        }

        add_action( 'delete_post', [ $this, 'deletePost' ] );
        add_action( 'add_attachment', [ $this, 'addAttachment' ] );
        add_filter( 'posts_clauses', [ $this, 'postsСlauses' ], 10, 2 );
        add_filter( 'pre_user_query', [ $this, 'preUserQuery'] );
        
        // Add folder filter to media library
        add_action( 'restrict_manage_posts', [ $this, 'addFolderFilter' ] );
        add_action( 'admin_head', [ $this, 'addFolderFilterStyles' ] );
    }

    public function replaceMediaEditAttachment() {
    }

    public function replaceMediaAttachmentFields( $fields, $post ) {
        $screen = get_current_screen();
        if ( $screen && $screen->id === 'attachment' ) {
            return $fields;
        }

        $fields['folders-replace-media'] = [
            'label' => '',
            'input' => 'html',
            'html'  => "
                <button type='button' class='button-secondary button-large' onclick='IFOLDERS.APP.fn.replacemedia.open(this)' data-attachment-id='{$post->ID}'>Replace Image</button>
                <p><strong>Warning:</strong> Replacing this image with another will permanently delete the current file and overwrite it with the new one. It is also recommended to use the same image size for the new image as the image being replaced, otherwise the recreated thumbnails will have different sizes and names, which may cause links to the old thumbnails to become broken.</p>"
        ];
        return $fields;
    }

    public function sidebarScripts() {
       if ( UserModel::hasAccess() ) {
            add_action( 'admin_head', [ $this, 'adminHead' ] );

            wp_enqueue_script( 'folders-cookie', FOLDERS_PLUGIN_URL . 'assets/vendor/cookie/cookie.js', [], FOLDERS_PLUGIN_VERSION, false );
            wp_enqueue_script( 'folders-url', FOLDERS_PLUGIN_URL . 'assets/vendor/url/url.js', [], FOLDERS_PLUGIN_VERSION, false );

            wp_enqueue_style( 'folders-overlayscrollbars', FOLDERS_PLUGIN_URL . 'assets/vendor/overlayscrollbars/overlayscrollbars.css', [], FOLDERS_PLUGIN_VERSION, false );
            wp_enqueue_script( 'folders-overlayscrollbars', FOLDERS_PLUGIN_URL . 'assets/vendor/overlayscrollbars/overlayscrollbars.js', [], FOLDERS_PLUGIN_VERSION, false );

            wp_enqueue_style( 'folders-colorpicker', FOLDERS_PLUGIN_URL . 'assets/css/colorpicker.css', [], FOLDERS_PLUGIN_VERSION );
            wp_enqueue_script( 'folders-colorpicker', FOLDERS_PLUGIN_URL . 'assets/js/colorpicker.js', ['jquery'], FOLDERS_PLUGIN_VERSION, false );

            wp_enqueue_style( 'folders-notify', FOLDERS_PLUGIN_URL . 'assets/css/notify.css', [], FOLDERS_PLUGIN_VERSION );
            wp_enqueue_script( 'folders-notify', FOLDERS_PLUGIN_URL . 'assets/js/notify.js', ['jquery'], FOLDERS_PLUGIN_VERSION, false );

            wp_enqueue_script( 'folders-tree', FOLDERS_PLUGIN_URL . 'assets/js/tree.js', ['jquery'], FOLDERS_PLUGIN_VERSION, false );

            wp_enqueue_style( 'folders-sidebar', FOLDERS_PLUGIN_URL . 'assets/css/sidebar.css', [], FOLDERS_PLUGIN_VERSION );
            wp_enqueue_script( 'folders-sidebar', FOLDERS_PLUGIN_URL . 'assets/js/sidebar.js', ['jquery'], FOLDERS_PLUGIN_VERSION, false );
            wp_localize_script( 'folders-sidebar', 'folders_sidebar_globals', $this->getGlobals() );
       }
    }

    public function postsСlauses( $clauses, $query ) {
        if ( !UserModel::hasAccess() ) {
            return $clauses;
        }

        $type = FoldersModel::getCurrentType();
        if ( $query->get( 'post_type' ) == $type && strpos( $clauses['where'], $type ) ) {
            $action = sanitize_key( filter_input( INPUT_POST, 'action', FILTER_DEFAULT ) );
            $mode = sanitize_key( filter_input( INPUT_POST, 'folders_mode', FILTER_DEFAULT ) );
            if ( $action === 'query-attachments' && $mode !== 'grid' ) {
                return $clauses;
            }

            global $wpdb;
            $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );
            $tableAttachments = HelperModel::getTableName( HelperModel::ATTACHMENTS );

            $rights = UserModel::getRights( $type );
            $meta = UserModel::getMeta( $type );
            $folder = $meta['folder'];
            
            // Check for folder filter from media library
            $filter_folder = isset( $_GET['folders_folder'] ) ? intval( $_GET['folders_folder'] ) : 0;
            
            // Only apply folder filter if it's explicitly set and greater than 0
            if ( $filter_folder > 0 ) {
                $folder = $filter_folder;
            } else if ( $filter_folder === 0 && isset( $_GET['folders_folder'] ) ) {
                // If filter is explicitly set to 0 (All Folders), show all items
                $folder = -1;
            }
            // If no filter is set, use the existing folder logic

            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'Folders postsСlauses: action=' . $action . ', mode=' . $mode . ', type=' . $type . ', folder=' . $folder . ', filter_folder=' . $filter_folder );
                error_log( 'Folders postsСlauses: meta=' . print_r( $meta, true ) );
            }

            if ($folder > 0) {
                $clauses['join'] .= " LEFT JOIN {$tableAttachments} AS ATTACHMENTS ON ({$wpdb->posts}.ID = ATTACHMENTS.attachment_id)";
                $clauses['where'] = " AND (ATTACHMENTS.folder_id = $folder) " . $clauses['where'];
                
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( 'Folders postsСlauses: Filtering by folder ID: ' . $folder );
                    error_log( 'Folders postsСlauses: Modified WHERE clause: ' . $clauses['where'] );
                }
            } else if ($folder == -2) { // uncategorized
                switch( $rights['access_type'] ) {
                    case SecurityProfilesModel::COMMON_FOLDERS: {
                        $clauses['where'] .= " AND ({$wpdb->posts}.ID NOT IN (SELECT ATTACHMENTS.attachment_id FROM {$tableAttachments} AS ATTACHMENTS LEFT JOIN {$tableFolders} AS FOLDERS ON FOLDERS.id=ATTACHMENTS.folder_id WHERE FOLDERS.owner=0))";
                    } break;
                    case SecurityProfilesModel::PERSONAL_FOLDERS: {
                        $user_id = get_current_user_id();
                        $clauses['where'] .= " AND ({$wpdb->posts}.ID NOT IN (SELECT ATTACHMENTS.attachment_id FROM {$tableAttachments} AS ATTACHMENTS LEFT JOIN {$tableFolders} AS FOLDERS ON FOLDERS.id=ATTACHMENTS.folder_id WHERE FOLDERS.owner={$user_id}))";
                    } break;
                }
            }
            // If folder is -1 (All items), don't add any filtering

            switch( $meta['sort']['items'] ) {
                case 'name-asc': {
                    $clauses['orderby'] = " {$wpdb->posts}.post_title ASC, " . $clauses['orderby'];
                } break;
                case 'name-desc': {
                    $clauses['orderby'] = " {$wpdb->posts}.post_title DESC, " . $clauses['orderby'];
                } break;
                case 'date-asc': {
                    $clauses['orderby'] = " {$wpdb->posts}.post_date ASC, " . $clauses['orderby'];
                } break;
                case 'date-desc': {
                    $clauses['orderby'] = " {$wpdb->posts}.post_date DESC, " . $clauses['orderby'];
                } break;
                case 'mod-asc': {
                    $clauses['orderby'] = " {$wpdb->posts}.post_modified ASC, " . $clauses['orderby'];
                } break;
                case 'mod-desc': {
                    $clauses['orderby'] = " {$wpdb->posts}.post_modified DESC, " . $clauses['orderby'];
                } break;
                case 'author-asc': {
                    $clauses['orderby'] = " {$wpdb->posts}.post_author ASC, " . $clauses['orderby'];
                } break;
                case 'author-desc': {
                    $clauses['orderby'] = " {$wpdb->posts}.post_author DESC, " . $clauses['orderby'];
                } break;
            }
        }

        return $clauses;
    }

    public function preUserQuery( $query ) {
        global $current_screen;
        if ( 'users' != $current_screen->id ) {
            return;
        }

        if ( !UserModel::hasAccess() ) {
            return;
        }

        global $wpdb;
        $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );
        $tableAttachments = HelperModel::getTableName( HelperModel::ATTACHMENTS );

        $type = FoldersModel::getCurrentType();
        $rights = UserModel::getRights( $type );
        $meta = UserModel::getMeta( $type );
        $folder = $meta['folder'];

        if ( $folder > 0 ) {
            $query->query_from .= " LEFT JOIN {$tableAttachments} AS ATTACHMENTS ON ({$wpdb->users}.ID = ATTACHMENTS.attachment_id)";
            $query->query_where .= " AND (ATTACHMENTS.folder_id = $folder)";
        } else if($folder == -2) { // uncategorized
            switch( $rights['access_type'] ) {
                case SecurityProfilesModel::COMMON_FOLDERS: {
                    $query->query_where .= " AND ($wpdb->users.ID NOT IN (SELECT ATTACHMENTS.attachment_id FROM {$tableAttachments} AS ATTACHMENTS LEFT JOIN {$tableFolders} AS FOLDERS ON FOLDERS.id=ATTACHMENTS.folder_id WHERE FOLDERS.owner=0))";
                } break;
                case SecurityProfilesModel::PERSONAL_FOLDERS: {
                    $user_id = get_current_user_id();
                    $query->query_where .= " AND ($wpdb->users.ID NOT IN (SELECT ATTACHMENTS.attachment_id FROM {$tableAttachments} AS ATTACHMENTS LEFT JOIN {$tableFolders} AS FOLDERS ON FOLDERS.id=ATTACHMENTS.folder_id WHERE FOLDERS.owner={$user_id}))";
                } break;
            }
        }

        return $query;
    }

    public function deletePost( $post_id ) {
        global $wpdb;
        $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );
        $tableAttachments = HelperModel::getTableName( HelperModel::ATTACHMENTS );

        // folders to refresh after the update
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = $wpdb->prepare( "SELECT DISTINCT folder_id as id FROM {$tableAttachments} WHERE attachment_id=%d", $post_id );
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $folders_to_edit = array_column( $wpdb->get_results( $sql, 'ARRAY_A' ), 'id' );

        // remove a post from the attachments table
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = $wpdb->prepare("DELETE FROM {$tableAttachments} WHERE attachment_id=%d", $post_id);
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query( $sql );

        // update the attachment count
        if ( !empty( $folders_to_edit ) ) {
            $ids = implode( ',', array_map( 'intval', $folders_to_edit ) );

            // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $sql = $wpdb->prepare("
                UPDATE {$tableFolders} AS F
                SET count = (SELECT COUNT(folder_id) FROM {$tableAttachments} AS A WHERE A.folder_id = F.id)
                WHERE id IN(%1s)",
                $ids
            );
            $wpdb->query( $sql );
            // phpcs:enable
        }
    }

    public function addAttachment( $attachment_id ) {
        // For uploads, we don't need to verify nonce as WordPress handles security
        // The nonce verification was preventing automatic folder assignment
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'Folders: addAttachment called for attachment ID: ' . $attachment_id );
            error_log( 'Folders: REQUEST data: ' . print_r( $_REQUEST, true ) );
        }
        
        if ( isset( $_REQUEST['folder'] ) ) {
            $folder_id = intval( $_REQUEST['folder'] );

            // Validate folder exists and user has access
            if ( $folder_id <= 0 ) {
                return;
            }

            global $wpdb;
            $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );
            $tableAttachments = HelperModel::getTableName( HelperModel::ATTACHMENTS );

            // Verify folder exists and user has access
            $folder_exists = $wpdb->get_var( $wpdb->prepare( 
                "SELECT id FROM {$tableFolders} WHERE id = %d", 
                $folder_id 
            ) );
            
            if ( !$folder_exists ) {
                return;
            }

            // add new attachments
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $wpdb->insert(
                $tableAttachments,
                [
                    'folder_id' => $folder_id,
                    'attachment_id' => $attachment_id
                ]
            );

            // update the attachment count
            // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $sql = $wpdb->prepare("
                UPDATE {$tableFolders} AS F
                SET count = (SELECT COUNT(folder_id) FROM {$tableAttachments} AS A WHERE A.folder_id = F.id)
                WHERE id=%d",
                $folder_id
            );
            $wpdb->query( $sql );
            // phpcs:enable
        }
    }

    /**
     * Add folder filter dropdown to media library
     */
    public function addFolderFilter() {
        global $pagenow, $typenow;
        
        // Only show on media library page
        if ( $pagenow !== 'upload.php' || $typenow !== 'attachment' ) {
            return;
        }
        
        // Check if user has access
        if ( !UserModel::hasAccess() ) {
            return;
        }
        
        $folders = $this->getFoldersForFilter();
        $current_folder = isset( $_GET['folders_folder'] ) ? intval( $_GET['folders_folder'] ) : '';
        
        ?>
        <select name="folders_folder" id="folders-folder-filter">
            <option value=""><?php esc_html_e( 'Move to Folder...', 'folders' ); ?></option>
            <?php foreach ( $folders as $folder ) : ?>
                <option value="<?php echo esc_attr( $folder['id'] ); ?>" <?php selected( $current_folder, $folder['id'] ); ?>>
                    <?php echo esc_html( $folder['title'] ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Add styles for the folder filter
     */
    public function addFolderFilterStyles() {
        global $pagenow, $typenow;
        
        // Only show on media library page
        if ( $pagenow !== 'upload.php' || $typenow !== 'attachment' ) {
            return;
        }
        
        // Styles are now in the CSS file
    }

    /**
     * Get folders for the filter dropdown
     */
    private function getFoldersForFilter() {
        global $wpdb;
        $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );
        $tableAttachments = HelperModel::getTableName( HelperModel::ATTACHMENTS );
        
        $rights = UserModel::getRights( 'attachment' );
        if ( !$rights || !$rights['access_type'] || !$rights['v'] ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'Folders getFoldersForFilter: User does not have access rights' );
            }
            return [];
        }
        
        $owner = $rights['access_type'] == SecurityProfilesModel::COMMON_FOLDERS ? 0 : get_current_user_id();
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'Folders getFoldersForFilter: Owner = ' . $owner . ', Access type = ' . $rights['access_type'] );
        }
        
        // Get folders with attachment counts
        $sql = $wpdb->prepare( "
            SELECT F.id, F.title, COUNT(A.attachment_id) as count
            FROM {$tableFolders} AS F
            LEFT JOIN {$tableAttachments} AS A ON F.id = A.folder_id
            WHERE F.type = 'attachment' AND F.owner = %d
            GROUP BY F.id
            ORDER BY F.title ASC
        ", $owner );
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'Folders getFoldersForFilter: SQL = ' . $sql );
        }
        
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $folders = $wpdb->get_results( $sql, ARRAY_A );
        
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'Folders getFoldersForFilter: Found ' . count( $folders ) . ' folders' );
            error_log( 'Folders getFoldersForFilter: Folders = ' . print_r( $folders, true ) );
        }
        
        $formatted_folders = [];
        foreach ( $folders as $folder ) {
            $formatted_folders[] = [
                'id' => $folder['id'],
                'title' => $folder['title'] . ' (' . $folder['count'] . ')'
            ];
        }
        
        return $formatted_folders;
    }

    public function prepareAttachment( $response, $attachment ) {
        if ( !isset( $attachment->ID ) && !isset( $attachment->id ) ) {
            return $response;
        }

        $attachment_id = isset($attachment->ID) ? $attachment->ID : $attachment->id;

        $config = ConfigModel::get();
        if ( array_key_exists( 'media_hover_details', $config ) && $config['media_hover_details'] ) {
            $list = $config['media_hover_details_list'] && count( (array)$config['media_hover_details_list'] ) > 0 ? $config['media_hover_details_list'] : [];

            if ( count( $list ) > 0 ) {
                $preview_details = '<div class="folders-preview-details">';

                if ( in_array( 'title', $list ) ) {
                    $preview_details .= '<p>' . esc_html__( "Title", 'folders' ) . ': ' . esc_html( $attachment->post_title ) . '</p>';
                }
                if ( in_array( 'alternative_text', $list ) ) {
                    $alt = get_post_meta( $attachment_id , '_wp_attachment_image_alt', true );
                    $preview_details .= '<p>' . esc_html__( "Alternative text", 'folders' ) . ': ' . esc_html( $alt ) . '</p>';
                }

                if ( in_array( 'file_url', $list ) ) {
                    $preview_details .= '<p>' . esc_html__( "File URL", 'folders' ) .': ' . esc_url( $attachment->guid ) . '</p>';
                }

                if ( in_array( 'dimension', $list ) ) {
                    $attachment_meta = wp_get_attachment_metadata( $attachment_id );
                    if ( isset( $attachment_meta ) && !empty( $attachment_meta ) && array_key_exists( 'width', $attachment_meta ) ) {
                        		$preview_details .= '<p>' . esc_html__( "Dimension", 'folders' ) . ': ' . $attachment_meta['width'] . ' x ' . $attachment_meta['height'] . '</p>';
                    }
                }

                if ( in_array( 'size', $list ) ) {
                    $file_path_for_size = get_attached_file( $attachment->ID );
                    $size_human = is_string( $file_path_for_size ) && is_file( $file_path_for_size ) ? size_format( filesize( $file_path_for_size ), 0 ) : '';
                    $preview_details .= '<p>' . esc_html__( "Size", 'folders' ) . ': ' . esc_html( $size_human ) . '</p>';
                }

                if ( in_array( 'filename', $list ) ) {
                    $preview_details .= '<p>' . esc_html__( "Filename", 'folders' ) . ': ' . esc_html( $attachment->post_name ) . '</p>';
                }

                if ( in_array( 'type', $list ) ) {
                    $preview_details .= '<p>' . esc_html__( "Type", 'folders' ) . ': ' . esc_html( $attachment->post_mime_type ) . '</p>';
                }

                if ( in_array( 'date', $list ) ) {
                    		$preview_details .= '<p>' . esc_html__( "Date", 'folders' ) . ': ' . date_i18n( get_option( 'date_format' ), strtotime( $attachment->post_date ) ) . '</p>';
                }

                if ( in_array( 'uploaded_by', $list ) ) {
                    		$preview_details .= '<p>' . esc_html__( "Uploaded by", 'folders' ) . ': ' . get_the_author_meta('display_name', $attachment->post_author) . '</p>';
                }

                $preview_details .= '</div>';

                $response['preview_details'] = $preview_details;
            }
        }

        return $response;
    }

    public function adminHead() {
        echo '<style>#screen-meta-links {position: absolute; right: 0;} .wrap {margin-top: 15px;}</style>';
    }

    private function getGlobals() {
        $type = UserModel::getPrimaryType();
        $meta = UserModel::getMeta( $type );
        $rights = UserModel::getRights( $type );
        $rights = $rights ? array_diff_key( $rights, ['access_type' => null] ) : null;
        $config = ConfigModel::get();

        $globals = [
            'data' => [
                'version' => FOLDERS_PLUGIN_VERSION,
                'type' => $type,
                'meta' => $meta,
                'rights' => $rights,
                'default_color' => $config['default_color'],
                'disable_counter' => $config['disable_counter'],
                'disable_ajax' => $config['disable_ajax'],
                'disable_search_bar' => $config['disable_search_bar'],
                'media_hover_details' => $config['media_hover_details'],
                'ticket' => (bool) ConfigModel::getTicket(),
                'max_upload_size' => size_format( wp_max_upload_size() )
            ],
            'api' => [
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'url' => esc_url_raw( rest_url( FOLDERS_PLUGIN_REST_URL ) )
            ],
            'msg' => HelperModel::getMessagesForSidebar()
        ];

        return $globals;
    }
}