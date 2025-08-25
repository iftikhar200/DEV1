<?php
/**
 * Quiz Master JSON
 *
 * @package           QuizMasterJSON
 * @author            Quiz Master JSON Team
 * @copyright         2024 Quiz Master JSON
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Quiz Master JSON
 * Plugin URI:        https://github.com/quiz-master-json/quiz-master-json
 * Description:       A comprehensive WordPress quiz plugin that allows importing MCQs from JSON files, building customizable quizzes by category, and displaying them with modern UI. Features include timer support, randomization, detailed results, and deep customization options.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Quiz Master JSON Team
 * Author URI:        https://github.com/quiz-master-json
 * Text Domain:       quiz-master-json
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Network:           true
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define('QUIZ_MASTER_JSON_VERSION', '1.0.0');

/**
 * Plugin root file path
 */
define('QUIZ_MASTER_JSON_PLUGIN_FILE', __FILE__);

/**
 * Plugin root directory path
 */
define('QUIZ_MASTER_JSON_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * Plugin root directory URL
 */
define('QUIZ_MASTER_JSON_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Plugin basename
 */
define('QUIZ_MASTER_JSON_BASENAME', plugin_basename(__FILE__));

/**
 * Plugin text domain
 */
define('QUIZ_MASTER_JSON_TEXT_DOMAIN', 'quiz-master-json');

/**
 * Database version for schema updates
 */
define('QUIZ_MASTER_JSON_DB_VERSION', '1.0.0');

/**
 * Minimum PHP version required
 */
define('QUIZ_MASTER_JSON_MIN_PHP', '7.4');

/**
 * Minimum WordPress version required
 */
define('QUIZ_MASTER_JSON_MIN_WP', '5.0');

/**
 * Check if WordPress and PHP versions meet requirements
 */
function quiz_master_json_check_requirements() {
    global $wp_version;
    
    $php_version = phpversion();
    $wp_version_clean = preg_replace('/[^0-9.].*/', '', $wp_version);
    
    $errors = array();
    
    if (version_compare($php_version, QUIZ_MASTER_JSON_MIN_PHP, '<')) {
        $errors[] = sprintf(
            __('Quiz Master JSON requires PHP version %s or higher. Your current PHP version is %s.', 'quiz-master-json'),
            QUIZ_MASTER_JSON_MIN_PHP,
            $php_version
        );
    }
    
    if (version_compare($wp_version_clean, QUIZ_MASTER_JSON_MIN_WP, '<')) {
        $errors[] = sprintf(
            __('Quiz Master JSON requires WordPress version %s or higher. Your current WordPress version is %s.', 'quiz-master-json'),
            QUIZ_MASTER_JSON_MIN_WP,
            $wp_version_clean
        );
    }
    
    if (!empty($errors)) {
        add_action('admin_notices', function() use ($errors) {
            foreach ($errors as $error) {
                echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
            }
        });
        
        // Deactivate the plugin
        add_action('admin_init', function() {
            deactivate_plugins(QUIZ_MASTER_JSON_BASENAME);
        });
        
        return false;
    }
    
    return true;
}

// Check requirements before loading
if (!quiz_master_json_check_requirements()) {
    return;
}

/**
 * Load plugin textdomain for translations
 */
function quiz_master_json_load_textdomain() {
    load_plugin_textdomain(
        'quiz-master-json',
        false,
        dirname(QUIZ_MASTER_JSON_BASENAME) . '/languages/'
    );
}
add_action('plugins_loaded', 'quiz_master_json_load_textdomain');

/**
 * The code that runs during plugin activation.
 */
function activate_quiz_master_json() {
    require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-activator.php';
    Quiz_Master_JSON_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_quiz_master_json() {
    require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-deactivator.php';
    Quiz_Master_JSON_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_quiz_master_json');
register_deactivation_hook(__FILE__, 'deactivate_quiz_master_json');

/**
 * Autoloader for plugin classes
 */
spl_autoload_register(function($class_name) {
    // Only autoload classes from this plugin
    if (strpos($class_name, 'Quiz_Master_JSON') !== 0) {
        return;
    }
    
    // Convert class name to file path
    $class_file = strtolower(str_replace(array('Quiz_Master_JSON_', '_'), array('', '-'), $class_name));
    $class_file = 'class-' . $class_file . '.php';
    
    // Look in different directories
    $directories = array(
        QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/',
        QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/',
        QUIZ_MASTER_JSON_PLUGIN_PATH . 'public/',
    );
    
    foreach ($directories as $directory) {
        $file_path = $directory . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
            return;
        }
    }
});

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-quiz-master-json.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function run_quiz_master_json() {
    $plugin = new Quiz_Master_JSON();
    $plugin->run();
}

/**
 * Initialize the plugin
 */
add_action('plugins_loaded', 'run_quiz_master_json');

/**
 * Add action links to plugin page
 */
function quiz_master_json_action_links($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=quiz-master-json-settings') . '">' . __('Settings', 'quiz-master-json') . '</a>';
    $quiz_link = '<a href="' . admin_url('admin.php?page=quiz-master-json-quizzes') . '">' . __('Quizzes', 'quiz-master-json') . '</a>';
    
    array_unshift($links, $settings_link, $quiz_link);
    
    return $links;
}
add_filter('plugin_action_links_' . QUIZ_MASTER_JSON_BASENAME, 'quiz_master_json_action_links');

/**
 * Add meta links to plugin page
 */
function quiz_master_json_meta_links($links, $file) {
    if ($file === QUIZ_MASTER_JSON_BASENAME) {
        $links[] = '<a href="https://github.com/quiz-master-json/quiz-master-json/wiki" target="_blank">' . __('Documentation', 'quiz-master-json') . '</a>';
        $links[] = '<a href="https://github.com/quiz-master-json/quiz-master-json/issues" target="_blank">' . __('Support', 'quiz-master-json') . '</a>';
        $links[] = '<a href="https://github.com/quiz-master-json/quiz-master-json" target="_blank">' . __('GitHub', 'quiz-master-json') . '</a>';
    }
    
    return $links;
}
add_filter('plugin_row_meta', 'quiz_master_json_meta_links', 10, 2);

/**
 * Check for updates and handle database migrations
 */
function quiz_master_json_check_version() {
    $installed_version = get_option('quiz_master_json_version', '0.0.0');
    
    if (version_compare($installed_version, QUIZ_MASTER_JSON_VERSION, '<')) {
        // Run upgrade routines
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-activator.php';
        Quiz_Master_JSON_Activator::upgrade($installed_version);
        
        // Update version
        update_option('quiz_master_json_version', QUIZ_MASTER_JSON_VERSION);
    }
}
add_action('plugins_loaded', 'quiz_master_json_check_version');

/**
 * Add admin notice for successful activation
 */
function quiz_master_json_activation_notice() {
    if (get_transient('quiz_master_json_activated')) {
        delete_transient('quiz_master_json_activated');
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php 
                printf(
                    __('Quiz Master JSON has been activated successfully! <a href="%s">Configure your settings</a> to get started.', 'quiz-master-json'),
                    admin_url('admin.php?page=quiz-master-json-settings')
                );
                ?>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'quiz_master_json_activation_notice');

/**
 * Handle plugin uninstall
 */
if (!function_exists('quiz_master_json_uninstall')) {
    function quiz_master_json_uninstall() {
        // This function is called from uninstall.php
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-deactivator.php';
        Quiz_Master_JSON_Deactivator::uninstall();
    }
}

/**
 * Security check - ensure we're in WordPress context
 */
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

/**
 * Plugin debug helper (only in development)
 */
if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
    function quiz_master_json_debug_log($message, $data = null) {
        $log_message = '[Quiz Master JSON] ' . $message;
        if ($data !== null) {
            $log_message .= ' | Data: ' . print_r($data, true);
        }
        error_log($log_message);
    }
} else {
    function quiz_master_json_debug_log($message, $data = null) {
        // Do nothing in production
    }
}

/**
 * Global helper functions
 */

/**
 * Get plugin option with default value
 */
function qmj_get_option($option_name, $default = '') {
    return get_option('quiz_master_json_' . $option_name, $default);
}

/**
 * Update plugin option
 */
function qmj_update_option($option_name, $value) {
    return update_option('quiz_master_json_' . $option_name, $value);
}

/**
 * Get plugin upload directory
 */
function qmj_get_upload_dir() {
    $upload_dir = wp_upload_dir();
    $qmj_dir = $upload_dir['basedir'] . '/quiz-master-json/';
    
    if (!file_exists($qmj_dir)) {
        wp_mkdir_p($qmj_dir);
    }
    
    return $qmj_dir;
}

/**
 * Get plugin upload URL
 */
function qmj_get_upload_url() {
    $upload_dir = wp_upload_dir();
    return $upload_dir['baseurl'] . '/quiz-master-json/';
}

/**
 * Check if current user can manage quizzes
 */
function qmj_current_user_can_manage() {
    return current_user_can('manage_options') || current_user_can('edit_posts');
}

/**
 * Sanitize quiz data
 */
function qmj_sanitize_quiz_data($data) {
    if (is_array($data)) {
        return array_map('qmj_sanitize_quiz_data', $data);
    } else {
        return sanitize_text_field($data);
    }
}

/**
 * Get quiz by ID
 */
function qmj_get_quiz($quiz_id) {
    require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-quiz.php';
    return Quiz_Master_JSON_Quiz::get_by_id($quiz_id);
}

/**
 * Get all quiz categories
 */
function qmj_get_categories() {
    require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-category.php';
    return Quiz_Master_JSON_Category::get_all();
}