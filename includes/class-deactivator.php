<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://github.com/quiz-master-json
 * @since      1.0.0
 *
 * @package    Quiz_Master_JSON
 * @subpackage Quiz_Master_JSON/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Quiz_Master_JSON
 * @subpackage Quiz_Master_JSON/includes
 * @author     Quiz Master JSON Team
 */
class Quiz_Master_JSON_Deactivator {

    /**
     * Plugin deactivation handler
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Clear scheduled events
        self::clear_scheduled_events();
        
        // Clear transients
        self::clear_transients();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log deactivation
        quiz_master_json_debug_log('Plugin deactivated');
    }

    /**
     * Plugin uninstall handler (called from uninstall.php)
     *
     * @since    1.0.0
     */
    public static function uninstall() {
        // Check if user really wants to delete all data
        if (!get_option('quiz_master_json_delete_data_on_uninstall', false)) {
            return;
        }
        
        // Remove database tables
        self::drop_tables();
        
        // Remove all plugin options
        self::remove_options();
        
        // Remove post types and their posts
        self::remove_post_types();
        
        // Remove upload directories
        self::remove_directories();
        
        // Clear all transients
        self::clear_transients();
        
        // Clear scheduled events
        self::clear_scheduled_events();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log uninstall
        quiz_master_json_debug_log('Plugin uninstalled completely');
    }

    /**
     * Drop all plugin database tables
     *
     * @since    1.0.0
     */
    private static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'qmj_attempts',
            $wpdb->prefix . 'qmj_results',
            $wpdb->prefix . 'qmj_quizzes',
            $wpdb->prefix . 'qmj_questions',
            $wpdb->prefix . 'qmj_categories',
        );
        
        // Disable foreign key checks temporarily
        $wpdb->query('SET FOREIGN_KEY_CHECKS = 0');
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        
        // Re-enable foreign key checks
        $wpdb->query('SET FOREIGN_KEY_CHECKS = 1');
        
        quiz_master_json_debug_log('Database tables dropped');
    }

    /**
     * Remove all plugin options
     *
     * @since    1.0.0
     */
    private static function remove_options() {
        global $wpdb;
        
        // Remove all options starting with 'quiz_master_json_'
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                'quiz_master_json_%'
            )
        );
        
        // Remove specific options
        $options_to_remove = array(
            'quiz_master_json_version',
            'quiz_master_json_db_version',
            'quiz_master_json_activation_date',
            'quiz_master_json_settings',
        );
        
        foreach ($options_to_remove as $option) {
            delete_option($option);
        }
        
        quiz_master_json_debug_log('Plugin options removed');
    }

    /**
     * Remove custom post types and their posts
     *
     * @since    1.0.0
     */
    private static function remove_post_types() {
        global $wpdb;
        
        // Get all quiz posts
        $quiz_posts = get_posts(array(
            'post_type' => 'qmj_quiz',
            'posts_per_page' => -1,
            'post_status' => 'any',
        ));
        
        // Delete all quiz posts and their meta
        foreach ($quiz_posts as $post) {
            wp_delete_post($post->ID, true); // Force delete, bypass trash
        }
        
        // Remove any remaining post meta for the post type
        $wpdb->query(
            "DELETE pm FROM {$wpdb->postmeta} pm
             LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
             WHERE p.post_type = 'qmj_quiz' OR p.ID IS NULL"
        );
        
        quiz_master_json_debug_log('Custom post types and posts removed');
    }

    /**
     * Remove upload directories and files
     *
     * @since    1.0.0
     */
    private static function remove_directories() {
        $upload_dir = qmj_get_upload_dir();
        
        if (is_dir($upload_dir)) {
            self::delete_directory($upload_dir);
        }
        
        quiz_master_json_debug_log('Upload directories removed');
    }

    /**
     * Recursively delete a directory and its contents
     *
     * @since    1.0.0
     * @param    string    $dir
     * @return   bool
     */
    private static function delete_directory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($path)) {
                self::delete_directory($path);
            } else {
                unlink($path);
            }
        }
        
        return rmdir($dir);
    }

    /**
     * Clear all plugin transients
     *
     * @since    1.0.0
     */
    private static function clear_transients() {
        global $wpdb;
        
        // Remove all transients starting with 'qmj_' or 'quiz_master_json_'
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_qmj_%' 
             OR option_name LIKE '_transient_timeout_qmj_%'
             OR option_name LIKE '_transient_quiz_master_json_%'
             OR option_name LIKE '_transient_timeout_quiz_master_json_%'"
        );
        
        // Clear site transients for multisite
        if (is_multisite()) {
            $wpdb->query(
                "DELETE FROM {$wpdb->sitemeta} 
                 WHERE meta_key LIKE '_site_transient_qmj_%' 
                 OR meta_key LIKE '_site_transient_timeout_qmj_%'
                 OR meta_key LIKE '_site_transient_quiz_master_json_%'
                 OR meta_key LIKE '_site_transient_timeout_quiz_master_json_%'"
            );
        }
        
        quiz_master_json_debug_log('Plugin transients cleared');
    }

    /**
     * Clear scheduled events
     *
     * @since    1.0.0
     */
    private static function clear_scheduled_events() {
        // Clear any scheduled hooks related to the plugin
        $scheduled_hooks = array(
            'qmj_cleanup_expired_sessions',
            'qmj_backup_database',
            'qmj_clean_temp_files',
            'qmj_update_statistics',
        );
        
        foreach ($scheduled_hooks as $hook) {
            $timestamp = wp_next_scheduled($hook);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $hook);
            }
        }
        
        quiz_master_json_debug_log('Scheduled events cleared');
    }

    /**
     * Clean up temporary data on deactivation
     *
     * @since    1.0.0
     */
    private static function cleanup_temporary_data() {
        global $wpdb;
        
        // Clean up expired quiz sessions (older than 24 hours)
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}qmj_results 
                 WHERE completed_at IS NULL 
                 AND started_at < %s",
                date('Y-m-d H:i:s', strtotime('-24 hours'))
            )
        );
        
        // Clean up temporary files
        $temp_dir = qmj_get_upload_dir() . 'temp/';
        if (is_dir($temp_dir)) {
            $files = glob($temp_dir . '*');
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < strtotime('-1 hour')) {
                    unlink($file);
                }
            }
        }
        
        quiz_master_json_debug_log('Temporary data cleaned up');
    }

    /**
     * Export data before uninstall (if requested)
     *
     * @since    1.0.0
     */
    private static function export_data_before_uninstall() {
        // Check if user wants to export data
        if (!get_option('quiz_master_json_export_before_uninstall', false)) {
            return;
        }
        
        $export_data = array();
        
        // Export categories
        $export_data['categories'] = qmj_get_categories();
        
        // Export questions
        global $wpdb;
        $questions = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}qmj_questions ORDER BY category_id, id",
            ARRAY_A
        );
        $export_data['questions'] = $questions;
        
        // Export quizzes
        $quizzes = get_posts(array(
            'post_type' => 'qmj_quiz',
            'posts_per_page' => -1,
            'post_status' => 'any',
        ));
        
        $export_quizzes = array();
        foreach ($quizzes as $quiz) {
            $quiz_data = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}qmj_quizzes WHERE post_id = %d",
                    $quiz->ID
                ),
                ARRAY_A
            );
            $quiz_data['post_data'] = array(
                'title' => $quiz->post_title,
                'content' => $quiz->post_content,
                'status' => $quiz->post_status,
                'date' => $quiz->post_date,
            );
            $export_quizzes[] = $quiz_data;
        }
        $export_data['quizzes'] = $export_quizzes;
        
        // Export results (last 30 days only to avoid huge files)
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}qmj_results 
                 WHERE completed_at > %s 
                 ORDER BY completed_at DESC",
                date('Y-m-d H:i:s', strtotime('-30 days'))
            ),
            ARRAY_A
        );
        $export_data['results'] = $results;
        
        // Save export file
        $export_file = qmj_get_upload_dir() . 'quiz-master-json-export-' . date('Y-m-d-H-i-s') . '.json';
        file_put_contents($export_file, json_encode($export_data, JSON_PRETTY_PRINT));
        
        // Notify admin
        $admin_email = get_option('admin_email');
        $subject = __('Quiz Master JSON - Data Export Before Uninstall', 'quiz-master-json');
        $message = sprintf(
            __('Your Quiz Master JSON data has been exported before uninstalling the plugin. The export file is available at: %s', 'quiz-master-json'),
            $export_file
        );
        
        wp_mail($admin_email, $subject, $message);
        
        quiz_master_json_debug_log('Data exported before uninstall: ' . $export_file);
    }

    /**
     * Check if plugin data should be preserved
     *
     * @since    1.0.0
     * @return   bool
     */
    private static function should_preserve_data() {
        return !get_option('quiz_master_json_delete_data_on_uninstall', false);
    }

    /**
     * Show admin notice about data preservation
     *
     * @since    1.0.0
     */
    public static function deactivation_notice() {
        if (self::should_preserve_data()) {
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-info">
                    <p>
                        <?php 
                        printf(
                            __('Quiz Master JSON has been deactivated but your data is preserved. You can reactivate the plugin anytime. To completely remove all data, go to <a href="%s">plugin settings</a> and enable "Delete all data on uninstall" before uninstalling.', 'quiz-master-json'),
                            admin_url('admin.php?page=quiz-master-json-settings')
                        );
                        ?>
                    </p>
                </div>
                <?php
            });
        }
    }

    /**
     * Get plugin statistics for final report
     *
     * @since    1.0.0
     * @return   array
     */
    private static function get_plugin_statistics() {
        global $wpdb;
        
        $stats = array();
        
        // Count categories
        $stats['categories'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}qmj_categories"
        );
        
        // Count questions
        $stats['questions'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}qmj_questions"
        );
        
        // Count quizzes
        $stats['quizzes'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}qmj_quizzes"
        );
        
        // Count quiz attempts
        $stats['attempts'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}qmj_results"
        );
        
        // Plugin usage duration
        $activation_date = get_option('quiz_master_json_activation_date');
        if ($activation_date) {
            $stats['usage_days'] = floor((time() - strtotime($activation_date)) / (24 * 60 * 60));
        }
        
        return $stats;
    }
}