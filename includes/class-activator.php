<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package    LibreOffice_Importer
 * @subpackage LibreOffice_Importer/includes
 */

class LibreOffice_Importer_Activator {

    /**
     * Plugin activation hook.
     *
     * Check for required PHP extensions and set up any necessary options.
     */
    public static function activate() {
        // Check for required PHP extensions
        if (!extension_loaded('zip')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                __('LibreOffice Importer requires the PHP ZIP extension to be installed and enabled.', 'libreoffice-importer'),
                __('Plugin Activation Error', 'libreoffice-importer'),
                array('back_link' => true)
            );
        }

        if (!extension_loaded('xml')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                __('LibreOffice Importer requires the PHP XML extension to be installed and enabled.', 'libreoffice-importer'),
                __('Plugin Activation Error', 'libreoffice-importer'),
                array('back_link' => true)
            );
        }

        // Set default options
        $default_options = array(
            'version' => LIBREOFFICE_IMPORTER_VERSION,
            'auto_extract_author' => true,
            'auto_extract_abstract' => true,
            'abstract_max_paragraphs' => 3,
            'default_post_status' => 'draft',
            'import_images' => true,
            'import_footnotes' => true,
            'preserve_formatting' => true,
        );

        add_option('libreoffice_importer_options', $default_options);

        // Clear the permalinks
        flush_rewrite_rules();
    }
}