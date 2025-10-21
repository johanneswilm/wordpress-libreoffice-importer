<?php
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @package    LibreOffice_Importer
 * @subpackage LibreOffice_Importer/includes
 */

class LibreOffice_Importer_Deactivator {

    /**
     * Deactivate the plugin.
     *
     * Clean up any temporary files or transients if needed.
     */
    public static function deactivate() {
        // Clean up any temporary upload directories
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/libreoffice-importer-temp';
        
        if (file_exists($temp_dir)) {
            self::delete_directory($temp_dir);
        }
        
        // Delete any transients
        delete_transient('libreoffice_importer_cache');
    }
    
    /**
     * Recursively delete a directory and its contents.
     *
     * @param string $dir The directory path to delete.
     * @return bool True on success, false on failure.
     */
    private static function delete_directory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            
            if (!self::delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        
        return rmdir($dir);
    }
}