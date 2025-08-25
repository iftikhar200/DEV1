<?php
/**
 * Fired during plugin activation
 *
 * @link       https://github.com/quiz-master-json
 * @since      1.0.0
 *
 * @package    Quiz_Master_JSON
 * @subpackage Quiz_Master_JSON/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Quiz_Master_JSON
 * @subpackage Quiz_Master_JSON/includes
 * @author     Quiz Master JSON Team
 */
class Quiz_Master_JSON_Activator {

    /**
     * Plugin activation handler
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Check WordPress and PHP versions
        self::check_requirements();
        
        // Create database tables
        self::create_tables();
        
        // Create default options
        self::create_default_options();
        
        // Create upload directories
        self::create_directories();
        
        // Create default categories and demo data
        self::create_demo_data();
        
        // Flush rewrite rules
        self::flush_rewrite_rules();
        
        // Set activation flag for admin notice
        set_transient('quiz_master_json_activated', true, 30);
        
        // Update version
        update_option('quiz_master_json_version', QUIZ_MASTER_JSON_VERSION);
        update_option('quiz_master_json_db_version', QUIZ_MASTER_JSON_DB_VERSION);
        
        // Log activation
        quiz_master_json_debug_log('Plugin activated successfully');
    }

    /**
     * Check system requirements
     *
     * @since    1.0.0
     */
    private static function check_requirements() {
        global $wp_version;
        
        $php_version = phpversion();
        $wp_version_clean = preg_replace('/[^0-9.].*/', '', $wp_version);
        
        if (version_compare($php_version, QUIZ_MASTER_JSON_MIN_PHP, '<')) {
            deactivate_plugins(QUIZ_MASTER_JSON_BASENAME);
            wp_die(
                sprintf(
                    __('Quiz Master JSON requires PHP version %s or higher. Your current PHP version is %s.', 'quiz-master-json'),
                    QUIZ_MASTER_JSON_MIN_PHP,
                    $php_version
                ),
                __('Plugin Activation Error', 'quiz-master-json'),
                array('back_link' => true)
            );
        }
        
        if (version_compare($wp_version_clean, QUIZ_MASTER_JSON_MIN_WP, '<')) {
            deactivate_plugins(QUIZ_MASTER_JSON_BASENAME);
            wp_die(
                sprintf(
                    __('Quiz Master JSON requires WordPress version %s or higher. Your current WordPress version is %s.', 'quiz-master-json'),
                    QUIZ_MASTER_JSON_MIN_WP,
                    $wp_version_clean
                ),
                __('Plugin Activation Error', 'quiz-master-json'),
                array('back_link' => true)
            );
        }
    }

    /**
     * Create database tables
     *
     * @since    1.0.0
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Categories table
        $table_categories = $wpdb->prefix . 'qmj_categories';
        $sql_categories = "CREATE TABLE $table_categories (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text,
            question_count bigint(20) unsigned DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY name (name)
        ) $charset_collate;";
        
        // Questions table
        $table_questions = $wpdb->prefix . 'qmj_questions';
        $sql_questions = "CREATE TABLE $table_questions (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            category_id bigint(20) unsigned NOT NULL,
            question text NOT NULL,
            options longtext NOT NULL,
            correct_answer varchar(10) NOT NULL,
            explanation text,
            difficulty enum('easy','medium','hard') DEFAULT 'medium',
            tags varchar(500),
            image_url varchar(500),
            reference varchar(500),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category_id (category_id),
            KEY difficulty (difficulty),
            FULLTEXT KEY question_search (question, explanation, tags)
        ) $charset_collate;";
        
        // Quizzes table (metadata for post type)
        $table_quizzes = $wpdb->prefix . 'qmj_quizzes';
        $sql_quizzes = "CREATE TABLE $table_quizzes (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            categories longtext NOT NULL,
            question_count int(11) DEFAULT 10,
            time_limit int(11) DEFAULT 0,
            pass_percentage int(3) DEFAULT 70,
            shuffle_questions tinyint(1) DEFAULT 1,
            shuffle_options tinyint(1) DEFAULT 1,
            instant_feedback tinyint(1) DEFAULT 1,
            show_explanations tinyint(1) DEFAULT 1,
            allow_review tinyint(1) DEFAULT 1,
            allow_back_navigation tinyint(1) DEFAULT 1,
            negative_marking tinyint(1) DEFAULT 0,
            negative_marks decimal(3,2) DEFAULT 0.25,
            max_attempts int(11) DEFAULT 0,
            require_login tinyint(1) DEFAULT 0,
            allowed_roles longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY post_id (post_id),
            KEY question_count (question_count),
            KEY time_limit (time_limit)
        ) $charset_collate;";
        
        // Results table
        $table_results = $wpdb->prefix . 'qmj_results';
        $sql_results = "CREATE TABLE $table_results (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            quiz_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            user_name varchar(255),
            user_email varchar(255),
            session_id varchar(100),
            ip_address varchar(45),
            total_questions int(11) NOT NULL,
            correct_answers int(11) NOT NULL,
            wrong_answers int(11) NOT NULL,
            unanswered int(11) DEFAULT 0,
            score decimal(5,2) NOT NULL,
            percentage decimal(5,2) NOT NULL,
            pass_status enum('pass','fail') NOT NULL,
            time_taken int(11) DEFAULT 0,
            answers longtext,
            started_at datetime DEFAULT CURRENT_TIMESTAMP,
            completed_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY quiz_id (quiz_id),
            KEY user_id (user_id),
            KEY session_id (session_id),
            KEY ip_address (ip_address),
            KEY percentage (percentage),
            KEY completed_at (completed_at)
        ) $charset_collate;";
        
        // Quiz attempts (for attempt limiting)
        $table_attempts = $wpdb->prefix . 'qmj_attempts';
        $sql_attempts = "CREATE TABLE $table_attempts (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            quiz_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            session_id varchar(100),
            ip_address varchar(45),
            attempt_count int(11) DEFAULT 1,
            last_attempt datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_attempt (quiz_id, user_id, session_id, ip_address),
            KEY quiz_id (quiz_id),
            KEY user_id (user_id),
            KEY last_attempt (last_attempt)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_categories);
        dbDelta($sql_questions);
        dbDelta($sql_quizzes);
        dbDelta($sql_results);
        dbDelta($sql_attempts);
        
        // Add foreign key constraints (if supported)
        self::add_foreign_keys();
        
        quiz_master_json_debug_log('Database tables created successfully');
    }

    /**
     * Add foreign key constraints
     *
     * @since    1.0.0
     */
    private static function add_foreign_keys() {
        global $wpdb;
        
        // Check if foreign keys are supported
        $result = $wpdb->get_var("SELECT @@foreign_key_checks");
        if (!$result) {
            return;
        }
        
        $table_questions = $wpdb->prefix . 'qmj_questions';
        $table_categories = $wpdb->prefix . 'qmj_categories';
        $table_quizzes = $wpdb->prefix . 'qmj_quizzes';
        $table_results = $wpdb->prefix . 'qmj_results';
        $table_attempts = $wpdb->prefix . 'qmj_attempts';
        
        // Add foreign keys if they don't exist
        $wpdb->query("
            ALTER TABLE $table_questions 
            ADD CONSTRAINT fk_questions_category 
            FOREIGN KEY (category_id) REFERENCES $table_categories(id) ON DELETE CASCADE
        ");
        
        $wpdb->query("
            ALTER TABLE $table_quizzes 
            ADD CONSTRAINT fk_quizzes_post 
            FOREIGN KEY (post_id) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE
        ");
        
        $wpdb->query("
            ALTER TABLE $table_results 
            ADD CONSTRAINT fk_results_quiz 
            FOREIGN KEY (quiz_id) REFERENCES $table_quizzes(id) ON DELETE CASCADE
        ");
        
        $wpdb->query("
            ALTER TABLE $table_attempts 
            ADD CONSTRAINT fk_attempts_quiz 
            FOREIGN KEY (quiz_id) REFERENCES $table_quizzes(id) ON DELETE CASCADE
        ");
    }

    /**
     * Create default plugin options
     *
     * @since    1.0.0
     */
    private static function create_default_options() {
        $default_options = array(
            'storage_mode' => 'database', // 'database' or 'filesystem'
            'default_time_limit' => 30,
            'default_question_count' => 10,
            'default_pass_percentage' => 70,
            'default_shuffle_questions' => true,
            'default_shuffle_options' => true,
            'default_instant_feedback' => true,
            'default_show_explanations' => true,
            'default_allow_review' => true,
            'default_allow_back_navigation' => true,
            'default_negative_marking' => false,
            'default_negative_marks' => 0.25,
            'require_login' => false,
            'allowed_roles' => array('administrator', 'editor', 'author'),
            'theme_mode' => 'auto', // 'light', 'dark', 'auto'
            'color_correct' => '#22c55e',
            'color_wrong' => '#ef4444',
            'color_neutral' => '#6b7280',
            'color_primary' => '#3b82f6',
            'color_background' => '#ffffff',
            'color_text' => '#1f2937',
            'font_family' => 'system-ui, -apple-system, sans-serif',
            'font_size_base' => '16px',
            'font_size_large' => '20px',
            'button_style' => 'rounded',
            'enable_animations' => true,
            'enable_sounds' => false,
            'privacy_notice' => '',
            'enable_recaptcha' => false,
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => '',
            'email_notifications' => false,
            'admin_email' => get_option('admin_email'),
            'enable_leaderboard' => false,
            'leaderboard_anonymous' => false,
            'anti_cheat_tab_switching' => false,
            'anti_cheat_copy_paste' => false,
            'enable_resume' => true,
            'auto_save_interval' => 30,
            'export_format' => 'csv',
            'date_format' => 'Y-m-d H:i:s',
            'results_per_page' => 20,
        );
        
        foreach ($default_options as $key => $value) {
            qmj_update_option($key, $value);
        }
        
        quiz_master_json_debug_log('Default options created');
    }

    /**
     * Create necessary directories
     *
     * @since    1.0.0
     */
    private static function create_directories() {
        $upload_dir = qmj_get_upload_dir();
        
        $directories = array(
            $upload_dir . 'categories/',
            $upload_dir . 'questions/',
            $upload_dir . 'exports/',
            $upload_dir . 'images/',
            $upload_dir . 'backups/',
        );
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
                
                // Create index.php for security
                file_put_contents($dir . 'index.php', '<?php // Silence is golden');
            }
        }
        
        // Create .htaccess for security
        $htaccess_content = "Options -Indexes\n";
        $htaccess_content .= "<Files *.json>\n";
        $htaccess_content .= "    Order allow,deny\n";
        $htaccess_content .= "    Deny from all\n";
        $htaccess_content .= "</Files>\n";
        
        file_put_contents($upload_dir . '.htaccess', $htaccess_content);
        
        quiz_master_json_debug_log('Directories created successfully');
    }

    /**
     * Create demo data for testing
     *
     * @since    1.0.0
     */
    private static function create_demo_data() {
        global $wpdb;
        
        // Create demo categories
        $categories = array(
            array(
                'name' => 'Computer Science',
                'slug' => 'computer-science',
                'description' => 'Programming, algorithms, data structures, and computer science fundamentals.',
            ),
            array(
                'name' => 'Mathematics',
                'slug' => 'mathematics',
                'description' => 'Basic mathematics, algebra, geometry, and calculus questions.',
            ),
            array(
                'name' => 'General Knowledge',
                'slug' => 'general-knowledge',
                'description' => 'General knowledge questions covering various topics.',
            ),
        );
        
        $table_categories = $wpdb->prefix . 'qmj_categories';
        
        foreach ($categories as $category) {
            $wpdb->insert(
                $table_categories,
                $category,
                array('%s', '%s', '%s')
            );
        }
        
        // Create demo questions
        $questions = array(
            // Computer Science questions
            array(
                'category_id' => 1,
                'question' => 'What does HTML stand for?',
                'options' => json_encode(array(
                    'A' => 'Hypertext Markup Language',
                    'B' => 'High Tech Modern Language',
                    'C' => 'Hyperlink and Text Markup Language',
                    'D' => 'Home Tool Markup Language'
                )),
                'correct_answer' => 'A',
                'explanation' => 'HTML stands for Hypertext Markup Language, which is the standard markup language for creating web pages.',
                'difficulty' => 'easy',
                'tags' => 'html, web development, markup',
            ),
            array(
                'category_id' => 1,
                'question' => 'Which of the following is NOT a programming paradigm?',
                'options' => json_encode(array(
                    'A' => 'Object-Oriented Programming',
                    'B' => 'Functional Programming',
                    'C' => 'Procedural Programming',
                    'D' => 'Circular Programming'
                )),
                'correct_answer' => 'D',
                'explanation' => 'Circular Programming is not a recognized programming paradigm. The others are well-established paradigms.',
                'difficulty' => 'medium',
                'tags' => 'programming, paradigms, concepts',
            ),
            
            // Mathematics questions
            array(
                'category_id' => 2,
                'question' => 'What is the value of π (pi) approximately?',
                'options' => json_encode(array(
                    'A' => '3.14159',
                    'B' => '2.71828',
                    'C' => '1.41421',
                    'D' => '1.61803'
                )),
                'correct_answer' => 'A',
                'explanation' => 'π (pi) is approximately 3.14159. It represents the ratio of a circle\'s circumference to its diameter.',
                'difficulty' => 'easy',
                'tags' => 'pi, mathematics, geometry',
            ),
            array(
                'category_id' => 2,
                'question' => 'What is the derivative of x²?',
                'options' => json_encode(array(
                    'A' => 'x',
                    'B' => '2x',
                    'C' => 'x²',
                    'D' => '2x²'
                )),
                'correct_answer' => 'B',
                'explanation' => 'The derivative of x² is 2x, following the power rule: d/dx(xⁿ) = nxⁿ⁻¹',
                'difficulty' => 'medium',
                'tags' => 'calculus, derivatives, mathematics',
            ),
            
            // General Knowledge questions
            array(
                'category_id' => 3,
                'question' => 'What is the capital of France?',
                'options' => json_encode(array(
                    'A' => 'London',
                    'B' => 'Berlin',
                    'C' => 'Paris',
                    'D' => 'Madrid'
                )),
                'correct_answer' => 'C',
                'explanation' => 'Paris is the capital and largest city of France.',
                'difficulty' => 'easy',
                'tags' => 'geography, capitals, france',
            ),
            array(
                'category_id' => 3,
                'question' => 'Which planet is known as the Red Planet?',
                'options' => json_encode(array(
                    'A' => 'Venus',
                    'B' => 'Mars',
                    'C' => 'Jupiter',
                    'D' => 'Saturn'
                )),
                'correct_answer' => 'B',
                'explanation' => 'Mars is known as the Red Planet due to iron oxide (rust) on its surface.',
                'difficulty' => 'easy',
                'tags' => 'astronomy, planets, mars',
            ),
        );
        
        $table_questions = $wpdb->prefix . 'qmj_questions';
        
        foreach ($questions as $question) {
            $wpdb->insert(
                $table_questions,
                $question,
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
            );
        }
        
        // Update question counts in categories
        $wpdb->query("
            UPDATE $table_categories c 
            SET question_count = (
                SELECT COUNT(*) 
                FROM $table_questions q 
                WHERE q.category_id = c.id
            )
        ");
        
        quiz_master_json_debug_log('Demo data created successfully');
    }

    /**
     * Flush rewrite rules for custom post types
     *
     * @since    1.0.0
     */
    private static function flush_rewrite_rules() {
        // Register post types first
        self::register_post_types();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        quiz_master_json_debug_log('Rewrite rules flushed');
    }

    /**
     * Register custom post types (needed for activation)
     *
     * @since    1.0.0
     */
    private static function register_post_types() {
        // Quiz post type
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
            'show_ui' => false, // We'll use custom admin pages
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'quiz'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
            'show_in_rest' => true,
        ));
    }

    /**
     * Handle plugin upgrades
     *
     * @since    1.0.0
     * @param    string    $installed_version
     */
    public static function upgrade($installed_version) {
        // Handle database schema updates
        $db_version = get_option('quiz_master_json_db_version', '0.0.0');
        
        if (version_compare($db_version, QUIZ_MASTER_JSON_DB_VERSION, '<')) {
            self::create_tables(); // This will update existing tables
            update_option('quiz_master_json_db_version', QUIZ_MASTER_JSON_DB_VERSION);
        }
        
        // Handle specific version upgrades
        if (version_compare($installed_version, '1.0.0', '<')) {
            // Upgrade routines for version 1.0.0
            self::upgrade_to_1_0_0();
        }
        
        quiz_master_json_debug_log('Plugin upgraded from version ' . $installed_version . ' to ' . QUIZ_MASTER_JSON_VERSION);
    }

    /**
     * Upgrade to version 1.0.0
     *
     * @since    1.0.0
     */
    private static function upgrade_to_1_0_0() {
        // Add any specific upgrade routines for version 1.0.0
        // For example, migrating old data format, updating options, etc.
        
        quiz_master_json_debug_log('Upgraded to version 1.0.0');
    }
}