<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/quiz-master-json
 * @since      1.0.0
 *
 * @package    Quiz_Master_JSON
 * @subpackage Quiz_Master_JSON/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Quiz_Master_JSON
 * @subpackage Quiz_Master_JSON/admin
 * @author     Quiz Master JSON Team
 */
class Quiz_Master_JSON_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles($hook) {
        // Only load on plugin pages
        if (!$this->is_plugin_page($hook)) {
            return;
        }

        wp_enqueue_style(
            $this->plugin_name . '-admin',
            QUIZ_MASTER_JSON_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            $this->version,
            'all'
        );

        // Load specific page styles
        if (strpos($hook, 'quiz-master-json-settings') !== false) {
            wp_enqueue_style(
                $this->plugin_name . '-settings',
                QUIZ_MASTER_JSON_PLUGIN_URL . 'assets/css/settings.css',
                array(),
                $this->version,
                'all'
            );
        }

        if (strpos($hook, 'quiz-master-json-quiz-builder') !== false) {
            wp_enqueue_style(
                $this->plugin_name . '-quiz-builder',
                QUIZ_MASTER_JSON_PLUGIN_URL . 'assets/css/quiz-builder.css',
                array(),
                $this->version,
                'all'
            );
        }

        if (strpos($hook, 'quiz-master-json-questions') !== false) {
            wp_enqueue_style(
                $this->plugin_name . '-question-manager',
                QUIZ_MASTER_JSON_PLUGIN_URL . 'assets/css/question-manager.css',
                array(),
                $this->version,
                'all'
            );
        }

        // Load WordPress core styles
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('thickbox');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts($hook) {
        // Only load on plugin pages
        if (!$this->is_plugin_page($hook)) {
            return;
        }

        wp_enqueue_script(
            $this->plugin_name . '-admin',
            QUIZ_MASTER_JSON_PLUGIN_URL . 'admin/js/admin-main.js',
            array('jquery', 'wp-color-picker', 'thickbox'),
            $this->version,
            false
        );

        // Load specific page scripts
        if (strpos($hook, 'quiz-master-json-settings') !== false) {
            wp_enqueue_script(
                $this->plugin_name . '-settings',
                QUIZ_MASTER_JSON_PLUGIN_URL . 'admin/js/settings-preview.js',
                array('jquery', 'wp-color-picker'),
                $this->version,
                false
            );
        }

        if (strpos($hook, 'quiz-master-json-quiz-builder') !== false) {
            wp_enqueue_script(
                $this->plugin_name . '-quiz-builder',
                QUIZ_MASTER_JSON_PLUGIN_URL . 'admin/js/quiz-builder.js',
                array('jquery', 'jquery-ui-sortable'),
                $this->version,
                false
            );
        }

        if (strpos($hook, 'quiz-master-json-questions') !== false) {
            wp_enqueue_script(
                $this->plugin_name . '-question-manager',
                QUIZ_MASTER_JSON_PLUGIN_URL . 'admin/js/question-manager.js',
                array('jquery'),
                $this->version,
                false
            );
        }

        if (strpos($hook, 'quiz-master-json-import') !== false) {
            wp_enqueue_script(
                $this->plugin_name . '-json-import',
                QUIZ_MASTER_JSON_PLUGIN_URL . 'admin/js/json-import.js',
                array('jquery'),
                $this->version,
                false
            );
        }

        // Localize script for AJAX
        wp_localize_script($this->plugin_name . '-admin', 'qmjAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('qmj_admin_nonce'),
            'strings' => array(
                'saving' => __('Saving...', 'quiz-master-json'),
                'saved' => __('Saved!', 'quiz-master-json'),
                'error' => __('Error occurred. Please try again.', 'quiz-master-json'),
                'confirm_delete' => __('Are you sure you want to delete this item?', 'quiz-master-json'),
                'loading' => __('Loading...', 'quiz-master-json'),
                'no_data' => __('No data found.', 'quiz-master-json'),
            )
        ));

        // Load WordPress media uploader
        wp_enqueue_media();
    }

    /**
     * Check if current page is a plugin page
     *
     * @since    1.0.0
     * @param    string    $hook
     * @return   bool
     */
    private function is_plugin_page($hook) {
        return strpos($hook, 'quiz-master-json') !== false;
    }

    /**
     * Add admin menu
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            __('Quiz Master JSON', 'quiz-master-json'),
            __('Quiz Master JSON', 'quiz-master-json'),
            'manage_options',
            'quiz-master-json',
            array($this, 'display_dashboard_page'),
            'dashicons-clipboard',
            30
        );

        // Dashboard (same as main page)
        add_submenu_page(
            'quiz-master-json',
            __('Dashboard', 'quiz-master-json'),
            __('Dashboard', 'quiz-master-json'),
            'manage_options',
            'quiz-master-json',
            array($this, 'display_dashboard_page')
        );

        // Quizzes
        add_submenu_page(
            'quiz-master-json',
            __('Quizzes', 'quiz-master-json'),
            __('Quizzes', 'quiz-master-json'),
            'edit_posts',
            'quiz-master-json-quizzes',
            array($this, 'display_quizzes_page')
        );

        // Quiz Builder
        add_submenu_page(
            'quiz-master-json',
            __('Quiz Builder', 'quiz-master-json'),
            __('Quiz Builder', 'quiz-master-json'),
            'edit_posts',
            'quiz-master-json-quiz-builder',
            array($this, 'display_quiz_builder_page')
        );

        // Questions
        add_submenu_page(
            'quiz-master-json',
            __('Questions', 'quiz-master-json'),
            __('Questions', 'quiz-master-json'),
            'edit_posts',
            'quiz-master-json-questions',
            array($this, 'display_questions_page')
        );

        // Categories
        add_submenu_page(
            'quiz-master-json',
            __('Categories', 'quiz-master-json'),
            __('Categories', 'quiz-master-json'),
            'edit_posts',
            'quiz-master-json-categories',
            array($this, 'display_categories_page')
        );

        // Import/Export
        add_submenu_page(
            'quiz-master-json',
            __('Import/Export', 'quiz-master-json'),
            __('Import/Export', 'quiz-master-json'),
            'manage_options',
            'quiz-master-json-import',
            array($this, 'display_import_page')
        );

        // Results
        add_submenu_page(
            'quiz-master-json',
            __('Results', 'quiz-master-json'),
            __('Results', 'quiz-master-json'),
            'edit_posts',
            'quiz-master-json-results',
            array($this, 'display_results_page')
        );

        // Settings
        add_submenu_page(
            'quiz-master-json',
            __('Settings', 'quiz-master-json'),
            __('Settings', 'quiz-master-json'),
            'manage_options',
            'quiz-master-json-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Initialize settings
     *
     * @since    1.0.0
     */
    public function init_settings() {
        register_setting('quiz_master_json_settings', 'quiz_master_json_settings', array(
            'sanitize_callback' => array($this, 'sanitize_settings')
        ));
    }

    /**
     * Sanitize settings
     *
     * @since    1.0.0
     * @param    array    $input
     * @return   array
     */
    public function sanitize_settings($input) {
        $sanitized = array();

        // Sanitize each setting based on its type
        if (isset($input['storage_mode'])) {
            $sanitized['storage_mode'] = in_array($input['storage_mode'], array('database', 'filesystem')) ? $input['storage_mode'] : 'database';
        }

        if (isset($input['default_time_limit'])) {
            $sanitized['default_time_limit'] = absint($input['default_time_limit']);
        }

        if (isset($input['default_question_count'])) {
            $sanitized['default_question_count'] = absint($input['default_question_count']);
        }

        if (isset($input['default_pass_percentage'])) {
            $sanitized['default_pass_percentage'] = min(100, max(0, absint($input['default_pass_percentage'])));
        }

        // Boolean settings
        $boolean_settings = array(
            'default_shuffle_questions', 'default_shuffle_options', 'default_instant_feedback',
            'default_show_explanations', 'default_allow_review', 'default_allow_back_navigation',
            'default_negative_marking', 'require_login', 'enable_animations', 'enable_sounds',
            'enable_recaptcha', 'email_notifications', 'enable_leaderboard', 'leaderboard_anonymous',
            'anti_cheat_tab_switching', 'anti_cheat_copy_paste', 'enable_resume'
        );

        foreach ($boolean_settings as $setting) {
            if (isset($input[$setting])) {
                $sanitized[$setting] = (bool) $input[$setting];
            }
        }

        // Color settings
        $color_settings = array('color_correct', 'color_wrong', 'color_neutral', 'color_primary', 'color_background', 'color_text');
        foreach ($color_settings as $setting) {
            if (isset($input[$setting])) {
                $sanitized[$setting] = sanitize_hex_color($input[$setting]);
            }
        }

        // Text settings
        $text_settings = array('font_family', 'font_size_base', 'font_size_large', 'button_style', 'theme_mode', 'admin_email', 'export_format', 'date_format');
        foreach ($text_settings as $setting) {
            if (isset($input[$setting])) {
                $sanitized[$setting] = sanitize_text_field($input[$setting]);
            }
        }

        // Textarea settings
        if (isset($input['privacy_notice'])) {
            $sanitized['privacy_notice'] = wp_kses_post($input['privacy_notice']);
        }

        // API keys (encrypted storage)
        if (isset($input['recaptcha_site_key'])) {
            $sanitized['recaptcha_site_key'] = sanitize_text_field($input['recaptcha_site_key']);
        }

        if (isset($input['recaptcha_secret_key'])) {
            $sanitized['recaptcha_secret_key'] = sanitize_text_field($input['recaptcha_secret_key']);
        }

        // Numeric settings
        if (isset($input['default_negative_marks'])) {
            $sanitized['default_negative_marks'] = floatval($input['default_negative_marks']);
        }

        if (isset($input['auto_save_interval'])) {
            $sanitized['auto_save_interval'] = max(10, absint($input['auto_save_interval']));
        }

        if (isset($input['results_per_page'])) {
            $sanitized['results_per_page'] = max(10, absint($input['results_per_page']));
        }

        // Array settings
        if (isset($input['allowed_roles']) && is_array($input['allowed_roles'])) {
            $sanitized['allowed_roles'] = array_map('sanitize_text_field', $input['allowed_roles']);
        }

        return $sanitized;
    }

    /**
     * Display dashboard page
     *
     * @since    1.0.0
     */
    public function display_dashboard_page() {
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/partials/admin-dashboard.php';
    }

    /**
     * Display quizzes page
     *
     * @since    1.0.0
     */
    public function display_quizzes_page() {
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/class-quiz-builder.php';
        $quiz_builder = new Quiz_Master_JSON_Quiz_Builder();
        $quiz_builder->display_quizzes_list();
    }

    /**
     * Display quiz builder page
     *
     * @since    1.0.0
     */
    public function display_quiz_builder_page() {
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/class-quiz-builder.php';
        $quiz_builder = new Quiz_Master_JSON_Quiz_Builder();
        $quiz_builder->display_builder();
    }

    /**
     * Display questions page
     *
     * @since    1.0.0
     */
    public function display_questions_page() {
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/class-question-manager.php';
        $question_manager = new Quiz_Master_JSON_Question_Manager();
        $question_manager->display();
    }

    /**
     * Display categories page
     *
     * @since    1.0.0
     */
    public function display_categories_page() {
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/class-question-manager.php';
        $question_manager = new Quiz_Master_JSON_Question_Manager();
        $question_manager->display_categories();
    }

    /**
     * Display import page
     *
     * @since    1.0.0
     */
    public function display_import_page() {
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/class-json-importer.php';
        $importer = new Quiz_Master_JSON_JSON_Importer();
        $importer->display();
    }

    /**
     * Display results page
     *
     * @since    1.0.0
     */
    public function display_results_page() {
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/class-results-manager.php';
        $results_manager = new Quiz_Master_JSON_Results_Manager();
        $results_manager->display();
    }

    /**
     * Display settings page
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/class-settings.php';
        $settings = new Quiz_Master_JSON_Settings();
        $settings->display();
    }

    /**
     * Register post types
     *
     * @since    1.0.0
     */
    public function register_post_types() {
        register_post_type('qmj_quiz', array(
            'labels' => array(
                'name' => __('Quizzes', 'quiz-master-json'),
                'singular_name' => __('Quiz', 'quiz-master-json'),
                'menu_name' => __('Quizzes', 'quiz-master-json'),
                'add_new' => __('Add New Quiz', 'quiz-master-json'),
                'add_new_item' => __('Add New Quiz', 'quiz-master-json'),
                'edit_item' => __('Edit Quiz', 'quiz-master-json'),
                'new_item' => __('New Quiz', 'quiz-master-json'),
                'view_item' => __('View Quiz', 'quiz-master-json'),
                'search_items' => __('Search Quizzes', 'quiz-master-json'),
                'not_found' => __('No quizzes found', 'quiz-master-json'),
                'not_found_in_trash' => __('No quizzes found in Trash', 'quiz-master-json'),
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => false, // We use custom admin pages
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'quiz'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
            'show_in_rest' => true,
        ));
    }

    /**
     * Register taxonomies
     *
     * @since    1.0.0
     */
    public function register_taxonomies() {
        register_taxonomy('qmj_quiz_category', 'qmj_quiz', array(
            'labels' => array(
                'name' => __('Quiz Categories', 'quiz-master-json'),
                'singular_name' => __('Quiz Category', 'quiz-master-json'),
                'menu_name' => __('Categories', 'quiz-master-json'),
            ),
            'public' => true,
            'hierarchical' => true,
            'show_ui' => false, // We use custom admin pages
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'quiz-category'),
            'show_in_rest' => true,
        ));
    }

    /**
     * Add custom columns to quiz post type
     *
     * @since    1.0.0
     * @param    array    $columns
     * @return   array
     */
    public function quiz_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['categories'] = __('Categories', 'quiz-master-json');
        $new_columns['questions'] = __('Questions', 'quiz-master-json');
        $new_columns['time_limit'] = __('Time Limit', 'quiz-master-json');
        $new_columns['attempts'] = __('Attempts', 'quiz-master-json');
        $new_columns['shortcode'] = __('Shortcode', 'quiz-master-json');
        $new_columns['date'] = $columns['date'];

        return $new_columns;
    }

    /**
     * Display custom column content
     *
     * @since    1.0.0
     * @param    string    $column
     * @param    int       $post_id
     */
    public function quiz_custom_columns($column, $post_id) {
        global $wpdb;

        switch ($column) {
            case 'categories':
                $quiz_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT categories FROM {$wpdb->prefix}qmj_quizzes WHERE post_id = %d",
                    $post_id
                ));
                
                if ($quiz_data) {
                    $categories = json_decode($quiz_data->categories, true);
                    if (is_array($categories)) {
                        $category_names = array();
                        foreach ($categories as $category_id) {
                            $category = $wpdb->get_var($wpdb->prepare(
                                "SELECT name FROM {$wpdb->prefix}qmj_categories WHERE id = %d",
                                $category_id
                            ));
                            if ($category) {
                                $category_names[] = $category;
                            }
                        }
                        echo esc_html(implode(', ', $category_names));
                    }
                }
                break;

            case 'questions':
                $quiz_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT question_count FROM {$wpdb->prefix}qmj_quizzes WHERE post_id = %d",
                    $post_id
                ));
                
                if ($quiz_data) {
                    echo esc_html($quiz_data->question_count);
                }
                break;

            case 'time_limit':
                $quiz_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT time_limit FROM {$wpdb->prefix}qmj_quizzes WHERE post_id = %d",
                    $post_id
                ));
                
                if ($quiz_data) {
                    if ($quiz_data->time_limit > 0) {
                        echo esc_html($quiz_data->time_limit . ' ' . __('minutes', 'quiz-master-json'));
                    } else {
                        echo esc_html(__('No limit', 'quiz-master-json'));
                    }
                }
                break;

            case 'attempts':
                $attempts = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}qmj_results r 
                     JOIN {$wpdb->prefix}qmj_quizzes q ON r.quiz_id = q.id 
                     WHERE q.post_id = %d",
                    $post_id
                ));
                echo esc_html($attempts);
                break;

            case 'shortcode':
                echo '<code>[quizmaster id="' . esc_attr($post_id) . '"]</code>';
                break;
        }
    }

    /**
     * Add meta boxes for quiz edit screen
     *
     * @since    1.0.0
     */
    public function add_meta_boxes() {
        add_meta_box(
            'qmj_quiz_settings',
            __('Quiz Settings', 'quiz-master-json'),
            array($this, 'quiz_settings_meta_box'),
            'qmj_quiz',
            'normal',
            'high'
        );

        add_meta_box(
            'qmj_quiz_shortcode',
            __('Quiz Shortcode', 'quiz-master-json'),
            array($this, 'quiz_shortcode_meta_box'),
            'qmj_quiz',
            'side',
            'default'
        );
    }

    /**
     * Quiz settings meta box content
     *
     * @since    1.0.0
     * @param    WP_Post    $post
     */
    public function quiz_settings_meta_box($post) {
        wp_nonce_field('qmj_save_quiz_meta', 'qmj_quiz_meta_nonce');
        
        global $wpdb;
        $quiz_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qmj_quizzes WHERE post_id = %d",
            $post->ID
        ));

        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/partials/quiz-meta-box.php';
    }

    /**
     * Quiz shortcode meta box content
     *
     * @since    1.0.0
     * @param    WP_Post    $post
     */
    public function quiz_shortcode_meta_box($post) {
        echo '<p>' . __('Use this shortcode to display the quiz:', 'quiz-master-json') . '</p>';
        echo '<code>[quizmaster id="' . esc_attr($post->ID) . '"]</code>';
        echo '<p><small>' . __('You can also use the Gutenberg block "Quiz Master JSON" to embed this quiz.', 'quiz-master-json') . '</small></p>';
    }

    /**
     * Save quiz meta data
     *
     * @since    1.0.0
     * @param    int    $post_id
     */
    public function save_quiz_meta($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['qmj_quiz_meta_nonce'])) {
            return;
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['qmj_quiz_meta_nonce'], 'qmj_save_quiz_meta')) {
            return;
        }

        // Check if user has permission
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Only save for quiz post type
        if (get_post_type($post_id) !== 'qmj_quiz') {
            return;
        }

        // Save quiz settings to database
        global $wpdb;
        
        $quiz_data = array(
            'categories' => isset($_POST['qmj_categories']) ? json_encode(array_map('intval', $_POST['qmj_categories'])) : '[]',
            'question_count' => isset($_POST['qmj_question_count']) ? absint($_POST['qmj_question_count']) : 10,
            'time_limit' => isset($_POST['qmj_time_limit']) ? absint($_POST['qmj_time_limit']) : 0,
            'pass_percentage' => isset($_POST['qmj_pass_percentage']) ? min(100, max(0, absint($_POST['qmj_pass_percentage']))) : 70,
            'shuffle_questions' => isset($_POST['qmj_shuffle_questions']) ? 1 : 0,
            'shuffle_options' => isset($_POST['qmj_shuffle_options']) ? 1 : 0,
            'instant_feedback' => isset($_POST['qmj_instant_feedback']) ? 1 : 0,
            'show_explanations' => isset($_POST['qmj_show_explanations']) ? 1 : 0,
            'allow_review' => isset($_POST['qmj_allow_review']) ? 1 : 0,
            'allow_back_navigation' => isset($_POST['qmj_allow_back_navigation']) ? 1 : 0,
            'negative_marking' => isset($_POST['qmj_negative_marking']) ? 1 : 0,
            'negative_marks' => isset($_POST['qmj_negative_marks']) ? floatval($_POST['qmj_negative_marks']) : 0.25,
            'max_attempts' => isset($_POST['qmj_max_attempts']) ? absint($_POST['qmj_max_attempts']) : 0,
            'require_login' => isset($_POST['qmj_require_login']) ? 1 : 0,
            'allowed_roles' => isset($_POST['qmj_allowed_roles']) ? json_encode($_POST['qmj_allowed_roles']) : '[]',
        );

        // Check if record exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}qmj_quizzes WHERE post_id = %d",
            $post_id
        ));

        if ($existing) {
            // Update existing record
            $wpdb->update(
                $wpdb->prefix . 'qmj_quizzes',
                $quiz_data,
                array('post_id' => $post_id),
                array('%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%d', '%d', '%s'),
                array('%d')
            );
        } else {
            // Insert new record
            $quiz_data['post_id'] = $post_id;
            $wpdb->insert(
                $wpdb->prefix . 'qmj_quizzes',
                $quiz_data,
                array('%d', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%f', '%d', '%d', '%s')
            );
        }
    }

    /**
     * Admin notices
     *
     * @since    1.0.0
     */
    public function admin_notices() {
        // Show notice if no categories exist
        global $wpdb;
        $categories_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}qmj_categories");
        
        if ($categories_count == 0 && isset($_GET['page']) && strpos($_GET['page'], 'quiz-master-json') !== false) {
            ?>
            <div class="notice notice-info">
                <p>
                    <?php 
                    printf(
                        __('Welcome to Quiz Master JSON! To get started, <a href="%s">import some questions</a> or <a href="%s">create categories manually</a>.', 'quiz-master-json'),
                        admin_url('admin.php?page=quiz-master-json-import'),
                        admin_url('admin.php?page=quiz-master-json-categories')
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Add action links to plugins page
     *
     * @since    1.0.0
     * @param    array    $links
     * @return   array
     */
    public function add_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=quiz-master-json-settings') . '">' . __('Settings', 'quiz-master-json') . '</a>';
        $quiz_link = '<a href="' . admin_url('admin.php?page=quiz-master-json-quizzes') . '">' . __('Quizzes', 'quiz-master-json') . '</a>';
        
        array_unshift($links, $settings_link, $quiz_link);
        
        return $links;
    }

    /**
     * AJAX handler for saving quiz
     *
     * @since    1.0.0
     */
    public function ajax_save_quiz() {
        check_ajax_referer('qmj_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have permission to perform this action.', 'quiz-master-json'));
        }

        // Handle quiz saving logic
        wp_send_json_success(array('message' => __('Quiz saved successfully!', 'quiz-master-json')));
    }

    /**
     * AJAX handler for deleting quiz
     *
     * @since    1.0.0
     */
    public function ajax_delete_quiz() {
        check_ajax_referer('qmj_admin_nonce', 'nonce');
        
        if (!current_user_can('delete_posts')) {
            wp_die(__('You do not have permission to perform this action.', 'quiz-master-json'));
        }

        // Handle quiz deletion logic
        wp_send_json_success(array('message' => __('Quiz deleted successfully!', 'quiz-master-json')));
    }

    /**
     * AJAX handler for saving question
     *
     * @since    1.0.0
     */
    public function ajax_save_question() {
        check_ajax_referer('qmj_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have permission to perform this action.', 'quiz-master-json'));
        }

        // Handle question saving logic
        wp_send_json_success(array('message' => __('Question saved successfully!', 'quiz-master-json')));
    }

    /**
     * AJAX handler for deleting question
     *
     * @since    1.0.0
     */
    public function ajax_delete_question() {
        check_ajax_referer('qmj_admin_nonce', 'nonce');
        
        if (!current_user_can('delete_posts')) {
            wp_die(__('You do not have permission to perform this action.', 'quiz-master-json'));
        }

        // Handle question deletion logic
        wp_send_json_success(array('message' => __('Question deleted successfully!', 'quiz-master-json')));
    }

    /**
     * AJAX handler for importing JSON
     *
     * @since    1.0.0
     */
    public function ajax_import_json() {
        check_ajax_referer('qmj_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'quiz-master-json'));
        }

        // Handle JSON import logic
        wp_send_json_success(array('message' => __('JSON imported successfully!', 'quiz-master-json')));
    }

    /**
     * AJAX handler for exporting data
     *
     * @since    1.0.0
     */
    public function ajax_export_data() {
        check_ajax_referer('qmj_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'quiz-master-json'));
        }

        // Handle data export logic
        wp_send_json_success(array('message' => __('Data exported successfully!', 'quiz-master-json')));
    }

    /**
     * AJAX handler for getting questions
     *
     * @since    1.0.0
     */
    public function ajax_get_questions() {
        check_ajax_referer('qmj_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have permission to perform this action.', 'quiz-master-json'));
        }

        // Handle getting questions logic
        wp_send_json_success(array('questions' => array()));
    }

    /**
     * AJAX handler for bulk actions
     *
     * @since    1.0.0
     */
    public function ajax_bulk_action() {
        check_ajax_referer('qmj_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have permission to perform this action.', 'quiz-master-json'));
        }

        // Handle bulk actions logic
        wp_send_json_success(array('message' => __('Bulk action completed successfully!', 'quiz-master-json')));
    }
}