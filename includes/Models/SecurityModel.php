<?php
namespace Folders\Models;

defined( 'ABSPATH' ) || exit;

class SecurityModel {
    // Security constants
    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    const MAX_CSV_SIZE = 5 * 1024 * 1024; // 5MB
    const MAX_CSV_ROWS = 10000;
    const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/png', 
        'image/gif',
        'image/webp'
    ];
    
    /**
     * Validate and sanitize file upload
     */
    public static function validateFileUpload( $file, $allowed_types = null, $max_size = null ) {
        if ( !$file || !is_array( $file ) ) {
            return false;
        }
        
        // Check for upload errors
        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            return false;
        }
        
        // Validate file exists and is readable
        if ( !is_file( $file['tmp_name'] ) || !is_readable( $file['tmp_name'] ) ) {
            return false;
        }
        
        // Check file size
        $max_size = $max_size ?: self::MAX_FILE_SIZE;
        if ( $file['size'] > $max_size ) {
            return false;
        }
        
        // Validate file type
        $allowed_types = $allowed_types ?: self::ALLOWED_IMAGE_TYPES;
        $file_type = mime_content_type( $file['tmp_name'] );
        if ( !in_array( $file_type, $allowed_types ) ) {
            return false;
        }
        
        // Additional security checks
        $finfo = finfo_open( FILEINFO_MIME_TYPE );
        $mime_type = finfo_file( $finfo, $file['tmp_name'] );
        finfo_close( $finfo );
        
        if ( $mime_type !== $file_type ) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate file path to prevent path traversal
     */
    public static function validateFilePath( $file_path, $base_path ) {
        $real_file_path = realpath( $file_path );
        $real_base_path = realpath( $base_path );
        
        if ( !$real_file_path || !$real_base_path ) {
            return false;
        }
        
        // Ensure file is within base directory
        if ( strpos( $real_file_path, $real_base_path ) !== 0 ) {
            return false;
        }
        
        return $real_file_path;
    }
    
    /**
     * Sanitize and validate hex color
     */
    public static function validateHexColor( $color ) {
        if ( !$color ) {
            return '';
        }
        
        // Remove any non-hex characters
        $color = preg_replace( '/[^0-9A-Fa-f]/', '', $color );
        
        // Validate hex color format
        if ( !preg_match( '/^[0-9A-Fa-f]{3}$|^[0-9A-Fa-f]{6}$/', $color ) ) {
            return '';
        }
        
        // Convert 3-character to 6-character format
        if ( strlen( $color ) === 3 ) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }
        
        return '#' . strtolower( $color );
    }
    
    /**
     * Validate folder ID and user access
     */
    public static function validateFolderAccess( $folder_id, $type ) {
        if ( !is_numeric( $folder_id ) || $folder_id <= 0 ) {
            return false;
        }
        
        global $wpdb;
        $tableFolders = HelperModel::getTableName( HelperModel::FOLDERS );
        
        $folder = $wpdb->get_row( $wpdb->prepare( 
            "SELECT id, type, owner FROM {$tableFolders} WHERE id = %d", 
            $folder_id 
        ) );
        
        if ( !$folder ) {
            return false;
        }
        
        // Check if folder type matches
        if ( $folder->type !== $type ) {
            return false;
        }
        
        // Check user permissions
        $rights = UserModel::getRights( $type );
        if ( !$rights ) {
            return false;
        }
        
        // For personal folders, check ownership
        if ( $rights['access_type'] === SecurityProfilesModel::PERSONAL_FOLDERS ) {
            if ( $folder->owner !== get_current_user_id() ) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Rate limiting for API requests
     */
    public static function checkRateLimit( $action, $limit = 100, $window = 3600 ) {
        $user_id = get_current_user_id();
        $key = "folders_rate_limit_{$action}_{$user_id}";
        
        $requests = get_transient( $key );
        if ( $requests === false ) {
            $requests = 0;
        }
        
        if ( $requests >= $limit ) {
            return false;
        }
        
        set_transient( $key, $requests + 1, $window );
        return true;
    }
    
    /**
     * Log security events
     */
    public static function logSecurityEvent( $event, $details = [] ) {
        if ( !defined( 'WP_DEBUG' ) || !WP_DEBUG ) {
            return;
        }
        
        $log_entry = [
            'timestamp' => current_time( 'mysql' ),
            'user_id' => get_current_user_id(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'event' => $event,
            'details' => $details
        ];
        
        error_log( 'Folders Security: ' . json_encode( $log_entry ) );
    }
} 