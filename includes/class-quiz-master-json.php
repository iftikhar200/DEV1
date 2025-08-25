<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/quiz-master-json
 * @since      1.0.0
 *
 * @package    Quiz_Master_JSON
 * @subpackage Quiz_Master_JSON/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Quiz_Master_JSON
 * @subpackage Quiz_Master_JSON/includes
 * @author     Quiz Master JSON Team
 */
class Quiz_Master_JSON {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Quiz_Master_JSON_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('QUIZ_MASTER_JSON_VERSION')) {
            $this->version = QUIZ_MASTER_JSON_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'quiz-master-json';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_rest_hooks();
        $this->define_block_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Quiz_Master_JSON_Loader. Orchestrates the hooks of the plugin.
     * - Quiz_Master_JSON_i18n. Defines internationalization functionality.
     * - Quiz_Master_JSON_Admin. Defines all hooks for the admin area.
     * - Quiz_Master_JSON_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/class-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'public/class-public.php';

        /**
         * The class responsible for REST API endpoints.
         */
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-rest-api.php';

        /**
         * Core model classes
         */
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-database.php';
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-quiz.php';
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-question.php';
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-category.php';
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-result.php';
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-json-handler.php';
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-security.php';
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-validator.php';

        /**
         * Helper functions
         */
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/functions.php';

        $this->loader = new Quiz_Master_JSON_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Quiz_Master_JSON_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new Quiz_Master_JSON_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new Quiz_Master_JSON_Admin($this->get_plugin_name(), $this->get_version());

        // Admin menu and pages
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'init_settings');

        // Admin scripts and styles
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // AJAX handlers for admin
        $this->loader->add_action('wp_ajax_qmj_save_quiz', $plugin_admin, 'ajax_save_quiz');
        $this->loader->add_action('wp_ajax_qmj_delete_quiz', $plugin_admin, 'ajax_delete_quiz');
        $this->loader->add_action('wp_ajax_qmj_save_question', $plugin_admin, 'ajax_save_question');
        $this->loader->add_action('wp_ajax_qmj_delete_question', $plugin_admin, 'ajax_delete_question');
        $this->loader->add_action('wp_ajax_qmj_import_json', $plugin_admin, 'ajax_import_json');
        $this->loader->add_action('wp_ajax_qmj_export_data', $plugin_admin, 'ajax_export_data');
        $this->loader->add_action('wp_ajax_qmj_get_questions', $plugin_admin, 'ajax_get_questions');
        $this->loader->add_action('wp_ajax_qmj_bulk_action', $plugin_admin, 'ajax_bulk_action');

        // Custom post type for quizzes
        $this->loader->add_action('init', $plugin_admin, 'register_post_types');
        $this->loader->add_action('init', $plugin_admin, 'register_taxonomies');

        // Add custom columns to quiz post type
        $this->loader->add_filter('manage_qmj_quiz_posts_columns', $plugin_admin, 'quiz_columns');
        $this->loader->add_action('manage_qmj_quiz_posts_custom_column', $plugin_admin, 'quiz_custom_columns', 10, 2);

        // Meta boxes for quiz edit screen
        $this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_meta_boxes');
        $this->loader->add_action('save_post', $plugin_admin, 'save_quiz_meta');

        // Admin notices
        $this->loader->add_action('admin_notices', $plugin_admin, 'admin_notices');

        // Settings page hooks
        $this->loader->add_filter('plugin_action_links_' . QUIZ_MASTER_JSON_BASENAME, $plugin_admin, 'add_action_links');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new Quiz_Master_JSON_Public($this->get_plugin_name(), $this->get_version());

        // Public scripts and styles
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // Shortcode registration
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');

        // AJAX handlers for public (both logged in and logged out users)
        $this->loader->add_action('wp_ajax_qmj_get_quiz_data', $plugin_public, 'ajax_get_quiz_data');
        $this->loader->add_action('wp_ajax_nopriv_qmj_get_quiz_data', $plugin_public, 'ajax_get_quiz_data');
        
        $this->loader->add_action('wp_ajax_qmj_submit_answer', $plugin_public, 'ajax_submit_answer');
        $this->loader->add_action('wp_ajax_nopriv_qmj_submit_answer', $plugin_public, 'ajax_submit_answer');
        
        $this->loader->add_action('wp_ajax_qmj_submit_quiz', $plugin_public, 'ajax_submit_quiz');
        $this->loader->add_action('wp_ajax_nopriv_qmj_submit_quiz', $plugin_public, 'ajax_submit_quiz');
        
        $this->loader->add_action('wp_ajax_qmj_get_quiz_results', $plugin_public, 'ajax_get_quiz_results');
        $this->loader->add_action('wp_ajax_nopriv_qmj_get_quiz_results', $plugin_public, 'ajax_get_quiz_results');

        // Template system
        $this->loader->add_filter('template_include', $plugin_public, 'template_include');
        $this->loader->add_action('wp_head', $plugin_public, 'add_quiz_meta_tags');

        // Custom query vars for quiz functionality
        $this->loader->add_filter('query_vars', $plugin_public, 'add_query_vars');
        $this->loader->add_action('parse_request', $plugin_public, 'parse_request');
    }

    /**
     * Register REST API endpoints
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_rest_hooks() {
        $plugin_rest = new Quiz_Master_JSON_Rest_API($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('rest_api_init', $plugin_rest, 'register_routes');
    }

    /**
     * Register Gutenberg block hooks
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_block_hooks() {
        // Register block on init
        $this->loader->add_action('init', $this, 'register_blocks');

        // Enqueue block assets
        $this->loader->add_action('enqueue_block_assets', $this, 'block_assets');
        $this->loader->add_action('enqueue_block_editor_assets', $this, 'block_editor_assets');
    }

    /**
     * Register Gutenberg blocks
     *
     * @since    1.0.0
     */
    public function register_blocks() {
        // Check if Gutenberg is available
        if (!function_exists('register_block_type')) {
            return;
        }

        // Register the quiz block
        register_block_type(QUIZ_MASTER_JSON_PLUGIN_PATH . 'blocks/quiz-block/block.json');
    }

    /**
     * Enqueue block assets
     *
     * @since    1.0.0
     */
    public function block_assets() {
        // Frontend block styles
        wp_enqueue_style(
            'quiz-master-json-blocks',
            QUIZ_MASTER_JSON_PLUGIN_URL . 'blocks/build/style-index.css',
            array(),
            $this->version
        );
    }

    /**
     * Enqueue block editor assets
     *
     * @since    1.0.0
     */
    public function block_editor_assets() {
        // Block editor scripts
        wp_enqueue_script(
            'quiz-master-json-blocks-editor',
            QUIZ_MASTER_JSON_PLUGIN_URL . 'blocks/build/index.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            $this->version,
            true
        );

        // Block editor styles
        wp_enqueue_style(
            'quiz-master-json-blocks-editor',
            QUIZ_MASTER_JSON_PLUGIN_URL . 'blocks/build/index.css',
            array('wp-edit-blocks'),
            $this->version
        );

        // Localize script for block editor
        wp_localize_script('quiz-master-json-blocks-editor', 'quizMasterJsonBlocks', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('qmj_block_nonce'),
            'quizzes' => $this->get_quizzes_for_block(),
            'categories' => $this->get_categories_for_block(),
        ));
    }

    /**
     * Get quizzes for block editor
     *
     * @since    1.0.0
     * @return   array
     */
    private function get_quizzes_for_block() {
        $quizzes = get_posts(array(
            'post_type' => 'qmj_quiz',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));

        $options = array();
        foreach ($quizzes as $quiz) {
            $options[] = array(
                'label' => $quiz->post_title,
                'value' => $quiz->ID,
            );
        }

        return $options;
    }

    /**
     * Get categories for block editor
     *
     * @since    1.0.0
     * @return   array
     */
    private function get_categories_for_block() {
        $categories = qmj_get_categories();
        $options = array();
        
        foreach ($categories as $category) {
            $options[] = array(
                'label' => $category->name,
                'value' => $category->slug,
            );
        }

        return $options;
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Quiz_Master_JSON_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Get plugin instance
     *
     * @since     1.0.0
     * @return    Quiz_Master_JSON
     */
    public static function get_instance() {
        static $instance = null;
        
        if (null === $instance) {
            $instance = new self();
        }
        
        return $instance;
    }
}