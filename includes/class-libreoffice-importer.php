<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @package    LibreOffice_Importer
 * @subpackage LibreOffice_Importer/includes
 */

class LibreOffice_Importer {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @var      LibreOffice_Importer_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct() {
        if (defined('LIBREOFFICE_IMPORTER_VERSION')) {
            $this->version = LIBREOFFICE_IMPORTER_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'libreoffice-importer';

        $this->load_dependencies();
        $this->define_admin_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the core plugin.
         */
        require_once LIBREOFFICE_IMPORTER_PLUGIN_DIR . 'includes/class-loader.php';

        /**
         * The class responsible for parsing ODT files.
         */
        require_once LIBREOFFICE_IMPORTER_PLUGIN_DIR . 'includes/class-odt-parser.php';

        /**
         * The class responsible for parsing HTML/rich text content.
         */
        require_once LIBREOFFICE_IMPORTER_PLUGIN_DIR . 'includes/class-html-parser.php';

        /**
         * The class responsible for creating WordPress posts from parsed content.
         */
        require_once LIBREOFFICE_IMPORTER_PLUGIN_DIR . 'includes/class-post-creator.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once LIBREOFFICE_IMPORTER_PLUGIN_DIR . 'admin/class-admin.php';

        $this->loader = new LibreOffice_Importer_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $plugin_admin = new LibreOffice_Importer_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        $this->loader->add_action('wp_ajax_libreoffice_import_odt', $plugin_admin, 'handle_odt_upload');
        $this->loader->add_action('wp_ajax_libreoffice_import_html', $plugin_admin, 'handle_html_import');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    LibreOffice_Importer_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}