<?php
/**
 * Plugin Name: LibreOffice Importer
 * Plugin URI: https://github.com/johanneswilm/wordpress-libreoffice-importer
 * Description: Import WordPress posts from LibreOffice documents (ODT files or copy/paste). Automatically extracts title, author, abstract, images, footnotes, and formatting.
 * Version: 1.0.0
 * Author: Johannes Wilm
 * Author URI: https://github.com/johanneswilm
 * License: GPL v3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: libreoffice-importer
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('LIBREOFFICE_IMPORTER_VERSION', '1.0.0');
define('LIBREOFFICE_IMPORTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LIBREOFFICE_IMPORTER_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_libreoffice_importer() {
    require_once LIBREOFFICE_IMPORTER_PLUGIN_DIR . 'includes/class-activator.php';
    LibreOffice_Importer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_libreoffice_importer() {
    require_once LIBREOFFICE_IMPORTER_PLUGIN_DIR . 'includes/class-deactivator.php';
    LibreOffice_Importer_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_libreoffice_importer');
register_deactivation_hook(__FILE__, 'deactivate_libreoffice_importer');

/**
 * The core plugin class.
 */
require LIBREOFFICE_IMPORTER_PLUGIN_DIR . 'includes/class-libreoffice-importer.php';

/**
 * Begins execution of the plugin.
 */
function run_libreoffice_importer() {
    $plugin = new LibreOffice_Importer();
    $plugin->run();
}

run_libreoffice_importer();
