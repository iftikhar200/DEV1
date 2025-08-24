<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://github.com/quiz-master-json
 * @since      1.0.0
 *
 * @package    Quiz_Master_JSON
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Security check - make sure this is called by WordPress
if (!defined('ABSPATH')) {
    exit;
}

// Check if the current user has permission to uninstall plugins
if (!current_user_can('activate_plugins')) {
    exit;
}

// Check for the plugin being uninstalled
if (!defined('WP_UNINSTALL_PLUGIN') || WP_UNINSTALL_PLUGIN !== 'quiz-master-json/quiz-master-json.php') {
    exit;
}

// Define plugin constants if not already defined
if (!defined('QUIZ_MASTER_JSON_PLUGIN_PATH')) {
    define('QUIZ_MASTER_JSON_PLUGIN_PATH', plugin_dir_path(__FILE__));
}

if (!defined('QUIZ_MASTER_JSON_PLUGIN_URL')) {
    define('QUIZ_MASTER_JSON_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (!defined('QUIZ_MASTER_JSON_TEXT_DOMAIN')) {
    define('QUIZ_MASTER_JSON_TEXT_DOMAIN', 'quiz-master-json');
}

/**
 * Load required files for uninstall
 */
require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'includes/class-deactivator.php';

/**
 * Helper function to get plugin option
 */
if (!function_exists('qmj_get_option')) {
    function qmj_get_option($option_name, $default = '') {
        return get_option('quiz_master_json_' . $option_name, $default);
    }
}

/**
 * Helper function to get upload directory
 */
if (!function_exists('qmj_get_upload_dir')) {
    function qmj_get_upload_dir() {
        $upload_dir = wp_upload_dir();
        return $upload_dir['basedir'] . '/quiz-master-json/';
    }
}

/**
 * Handle multisite uninstall
 */
if (is_multisite()) {
    // Get all sites in the network
    $sites = get_sites(array(
        'number' => 0, // Get all sites
        'deleted' => 0,
    ));
    
    foreach ($sites as $site) {
        switch_to_blog($site->blog_id);
        
        // Check if plugin should delete data for this site
        if (qmj_get_option('delete_data_on_uninstall', false)) {
            quiz_master_json_uninstall_site();
        }
        
        restore_current_blog();
    }
    
    // Clean up network-wide data if needed
    quiz_master_json_uninstall_network();
} else {
    // Single site uninstall
    if (qmj_get_option('delete_data_on_uninstall', false)) {
        quiz_master_json_uninstall_site();
    }
}

/**
 * Uninstall data for a single site
 */
function quiz_master_json_uninstall_site() {
    global $wpdb;
    
    // Log the uninstall process
    error_log('[Quiz Master JSON] Starting uninstall process for site: ' . get_site_url());
    
    try {
        // Export data if requested
        if (qmj_get_option('export_before_uninstall', false)) {
            quiz_master_json_export_before_uninstall();
        }
        
        // Use the deactivator class to handle uninstall
        Quiz_Master_JSON_Deactivator::uninstall();
        
        error_log('[Quiz Master JSON] Successfully completed uninstall for site: ' . get_site_url());
        
    } catch (Exception $e) {
        error_log('[Quiz Master JSON] Error during uninstall: ' . $e->getMessage());
    }
}

/**
 * Uninstall network-wide data (multisite)
 */
function quiz_master_json_uninstall_network() {
    // Remove network options if any
    delete_site_option('quiz_master_json_network_version');
    delete_site_option('quiz_master_json_network_settings');
    
    // Clear network transients
    delete_site_transient('quiz_master_json_network_data');
    
    error_log('[Quiz Master JSON] Network-wide uninstall completed');
}

/**
 * Export data before uninstall
 */
function quiz_master_json_export_before_uninstall() {
    global $wpdb;
    
    try {
        $export_data = array();
        $export_data['export_info'] = array(
            'plugin_version' => get_option('quiz_master_json_version', '1.0.0'),
            'export_date' => current_time('mysql'),
            'site_url' => get_site_url(),
            'wordpress_version' => get_bloginfo('version'),
        );
        
        // Export categories
        $categories_table = $wpdb->prefix . 'qmj_categories';
        if ($wpdb->get_var("SHOW TABLES LIKE '$categories_table'") === $categories_table) {
            $export_data['categories'] = $wpdb->get_results("SELECT * FROM $categories_table", ARRAY_A);
        }
        
        // Export questions
        $questions_table = $wpdb->prefix . 'qmj_questions';
        if ($wpdb->get_var("SHOW TABLES LIKE '$questions_table'") === $questions_table) {
            $export_data['questions'] = $wpdb->get_results("SELECT * FROM $questions_table", ARRAY_A);
        }
        
        // Export quiz configurations
        $quizzes_table = $wpdb->prefix . 'qmj_quizzes';
        if ($wpdb->get_var("SHOW TABLES LIKE '$quizzes_table'") === $quizzes_table) {
            $export_data['quiz_configs'] = $wpdb->get_results("SELECT * FROM $quizzes_table", ARRAY_A);
        }
        
        // Export quiz posts
        $quiz_posts = get_posts(array(
            'post_type' => 'qmj_quiz',
            'posts_per_page' => -1,
            'post_status' => 'any',
        ));
        
        $export_quiz_posts = array();
        foreach ($quiz_posts as $post) {
            $export_quiz_posts[] = array(
                'ID' => $post->ID,
                'post_title' => $post->post_title,
                'post_content' => $post->post_content,
                'post_status' => $post->post_status,
                'post_date' => $post->post_date,
                'post_modified' => $post->post_modified,
                'meta_data' => get_post_meta($post->ID),
            );
        }
        $export_data['quiz_posts'] = $export_quiz_posts;
        
        // Export recent results (last 30 days)
        $results_table = $wpdb->prefix . 'qmj_results';
        if ($wpdb->get_var("SHOW TABLES LIKE '$results_table'") === $results_table) {
            $recent_results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $results_table WHERE completed_at > %s ORDER BY completed_at DESC LIMIT 1000",
                    date('Y-m-d H:i:s', strtotime('-30 days'))
                ),
                ARRAY_A
            );
            $export_data['recent_results'] = $recent_results;
        }
        
        // Export plugin settings
        $settings = array();
        $options = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
                'quiz_master_json_%'
            ),
            ARRAY_A
        );
        
        foreach ($options as $option) {
            $settings[$option['option_name']] = maybe_unserialize($option['option_value']);
        }
        $export_data['settings'] = $settings;
        
        // Create export file
        $upload_dir = qmj_get_upload_dir();
        if (!file_exists($upload_dir)) {
            wp_mkdir_p($upload_dir);
        }
        
        $export_filename = 'quiz-master-json-export-' . date('Y-m-d-H-i-s') . '.json';
        $export_path = $upload_dir . $export_filename;
        
        $json_data = json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (file_put_contents($export_path, $json_data)) {
            // Try to email the admin about the export
            $admin_email = get_option('admin_email');
            if ($admin_email) {
                $subject = __('Quiz Master JSON - Data Export Before Uninstall', 'quiz-master-json');
                $message = sprintf(
                    __("Your Quiz Master JSON data has been exported before uninstalling the plugin.\n\nExport file: %s\nExport date: %s\nSite: %s\n\nPlease download this file before it gets deleted with the plugin files.", 'quiz-master-json'),
                    $export_path,
                    current_time('mysql'),
                    get_site_url()
                );
                
                wp_mail($admin_email, $subject, $message);
            }
            
            error_log('[Quiz Master JSON] Data exported successfully: ' . $export_path);
        } else {
            error_log('[Quiz Master JSON] Failed to create export file: ' . $export_path);
        }
        
    } catch (Exception $e) {
        error_log('[Quiz Master JSON] Error during data export: ' . $e->getMessage());
    }
}

/**
 * Final cleanup and logging
 */
function quiz_master_json_final_cleanup() {
    // Log final statistics
    $stats = array(
        'uninstall_date' => current_time('mysql'),
        'wordpress_version' => get_bloginfo('version'),
        'php_version' => phpversion(),
        'plugin_version' => get_option('quiz_master_json_version', 'unknown'),
    );
    
    error_log('[Quiz Master JSON] Final uninstall statistics: ' . json_encode($stats));
    
    // Clear any remaining WordPress caches
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    
    // Clear object cache if available
    if (function_exists('wp_cache_flush_group')) {
        wp_cache_flush_group('quiz-master-json');
    }
}

// Run final cleanup
quiz_master_json_final_cleanup();

// Log completion
error_log('[Quiz Master JSON] Plugin uninstall process completed successfully');