<?php
/**
 * The JSON import functionality of the plugin.
 *
 * @link       https://github.com/quiz-master-json
 * @since      1.0.0
 *
 * @package    Quiz_Master_JSON
 * @subpackage Quiz_Master_JSON/admin
 */

/**
 * JSON import management class
 *
 * Handles JSON file uploads and question imports
 *
 * @package    Quiz_Master_JSON
 * @subpackage Quiz_Master_JSON/admin
 * @author     Quiz Master JSON Team
 */
class Quiz_Master_JSON_JSON_Importer {

    /**
     * Display the import page
     *
     * @since    1.0.0
     */
    public function display() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'import';

        switch ($action) {
            case 'export':
                $this->display_export_page();
                break;
            case 'history':
                $this->display_import_history();
                break;
            default:
                $this->display_import_page();
                break;
        }
    }

    /**
     * Display JSON import page
     *
     * @since    1.0.0
     */
    private function display_import_page() {
        // Handle file upload
        if ($_POST && isset($_POST['qmj_import_nonce']) && wp_verify_nonce($_POST['qmj_import_nonce'], 'qmj_import_json')) {
            $result = $this->handle_json_import();
            
            if ($result['success']) {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
                if (isset($result['details'])) {
                    echo '<div class="qmj-import-details">' . $result['details'] . '</div>';
                }
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
                if (isset($result['errors']) && is_array($result['errors'])) {
                    echo '<div class="qmj-import-errors"><ul>';
                    foreach ($result['errors'] as $error) {
                        echo '<li>' . esc_html($error) . '</li>';
                    }
                    echo '</ul></div>';
                }
            }
        }

        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/partials/json-import-page.php';
    }

    /**
     * Display export page
     *
     * @since    1.0.0
     */
    private function display_export_page() {
        // Handle export request
        if ($_POST && isset($_POST['qmj_export_nonce']) && wp_verify_nonce($_POST['qmj_export_nonce'], 'qmj_export_data')) {
            $this->handle_data_export();
            return;
        }

        global $wpdb;
        $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}qmj_categories ORDER BY name", ARRAY_A);
        
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/partials/data-export-page.php';
    }

    /**
     * Display import history
     *
     * @since    1.0.0
     */
    private function display_import_history() {
        // Get import history from options or custom table
        $import_history = get_option('qmj_import_history', array());
        $import_history = array_reverse($import_history); // Show newest first
        
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/partials/import-history-page.php';
    }

    /**
     * Handle JSON file import
     *
     * @since    1.0.0
     * @return   array    Result with success status and message
     */
    private function handle_json_import() {
        // Check file upload
        if (!isset($_FILES['json_file']) || $_FILES['json_file']['error'] !== UPLOAD_ERR_OK) {
            return array(
                'success' => false,
                'message' => __('Please select a valid JSON file.', 'quiz-master-json')
            );
        }

        $file = $_FILES['json_file'];
        
        // Validate file type
        $file_type = wp_check_filetype($file['name']);
        if (!in_array($file_type['ext'], array('json', 'txt'))) {
            return array(
                'success' => false,
                'message' => __('Only JSON files are allowed.', 'quiz-master-json')
            );
        }

        // Validate file size (10MB max)
        if ($file['size'] > 10 * 1024 * 1024) {
            return array(
                'success' => false,
                'message' => __('File size is too large. Maximum allowed size is 10MB.', 'quiz-master-json')
            );
        }

        // Read and parse JSON
        $json_content = file_get_contents($file['tmp_name']);
        if (empty($json_content)) {
            return array(
                'success' => false,
                'message' => __('The uploaded file is empty.', 'quiz-master-json')
            );
        }

        $data = json_decode($json_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                'success' => false,
                'message' => __('Invalid JSON format: ', 'quiz-master-json') . json_last_error_msg()
            );
        }

        // Validate JSON structure
        $validation_result = $this->validate_json_structure($data);
        if (!$validation_result['valid']) {
            return array(
                'success' => false,
                'message' => $validation_result['message'],
                'errors' => $validation_result['errors']
            );
        }

        // Get import options
        $import_options = array(
            'create_categories' => isset($_POST['create_categories']) ? true : false,
            'skip_duplicates' => isset($_POST['skip_duplicates']) ? true : false,
            'update_existing' => isset($_POST['update_existing']) ? true : false,
            'default_category' => isset($_POST['default_category']) ? absint($_POST['default_category']) : 0,
        );

        // Import the data
        return $this->import_questions_from_data($data, $import_options, $file['name']);
    }

    /**
     * Validate JSON structure
     *
     * @since    1.0.0
     * @param    array    $data
     * @return   array    Validation result
     */
    private function validate_json_structure($data) {
        $errors = array();
        
        // Check for required fields
        if (!isset($data['questions']) || !is_array($data['questions'])) {
            $errors[] = __('JSON must contain a "questions" array.', 'quiz-master-json');
        }

        if (empty($data['questions'])) {
            $errors[] = __('No questions found in the JSON file.', 'quiz-master-json');
        }

        // Validate individual questions
        if (isset($data['questions']) && is_array($data['questions'])) {
            foreach ($data['questions'] as $index => $question) {
                $question_errors = $this->validate_question_data($question, $index + 1);
                $errors = array_merge($errors, $question_errors);
            }
        }

        return array(
            'valid' => empty($errors),
            'message' => empty($errors) ? __('JSON structure is valid.', 'quiz-master-json') : __('JSON validation failed.', 'quiz-master-json'),
            'errors' => $errors
        );
    }

    /**
     * Validate individual question data
     *
     * @since    1.0.0
     * @param    array    $question
     * @param    int      $index
     * @return   array    Validation errors
     */
    private function validate_question_data($question, $index) {
        $errors = array();
        $prefix = sprintf(__('Question %d: ', 'quiz-master-json'), $index);

        // Required fields
        $required_fields = array('question', 'options', 'correct_answer');
        foreach ($required_fields as $field) {
            if (!isset($question[$field]) || empty($question[$field])) {
                $errors[] = $prefix . sprintf(__('Missing required field "%s".', 'quiz-master-json'), $field);
            }
        }

        // Validate options structure
        if (isset($question['options'])) {
            if (!is_array($question['options'])) {
                $errors[] = $prefix . __('Options must be an array.', 'quiz-master-json');
            } elseif (count($question['options']) < 2) {
                $errors[] = $prefix . __('At least 2 options are required.', 'quiz-master-json');
            } else {
                // Check if correct answer exists in options
                if (isset($question['correct_answer'])) {
                    if (!array_key_exists($question['correct_answer'], $question['options'])) {
                        $errors[] = $prefix . __('Correct answer does not match any option key.', 'quiz-master-json');
                    }
                }
            }
        }

        // Validate optional fields
        if (isset($question['difficulty']) && !in_array($question['difficulty'], array('easy', 'medium', 'hard'))) {
            $errors[] = $prefix . __('Difficulty must be "easy", "medium", or "hard".', 'quiz-master-json');
        }

        if (isset($question['image_url']) && !empty($question['image_url']) && !filter_var($question['image_url'], FILTER_VALIDATE_URL)) {
            $errors[] = $prefix . __('Image URL is not valid.', 'quiz-master-json');
        }

        return $errors;
    }

    /**
     * Import questions from validated data
     *
     * @since    1.0.0
     * @param    array    $data
     * @param    array    $options
     * @param    string   $filename
     * @return   array    Import result
     */
    private function import_questions_from_data($data, $options, $filename) {
        global $wpdb;

        $imported_count = 0;
        $skipped_count = 0;
        $updated_count = 0;
        $error_count = 0;
        $category_map = array();
        $import_log = array();

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            foreach ($data['questions'] as $index => $question_data) {
                $question_number = $index + 1;
                
                // Get or create category
                $category_id = $this->get_or_create_category($question_data, $options, $category_map);
                
                if (!$category_id) {
                    $import_log[] = sprintf(__('Question %d: Failed to resolve category.', 'quiz-master-json'), $question_number);
                    $error_count++;
                    continue;
                }

                // Prepare question data
                $prepared_question = array(
                    'category_id' => $category_id,
                    'question' => wp_kses_post($question_data['question']),
                    'options' => json_encode($question_data['options']),
                    'correct_answer' => sanitize_text_field($question_data['correct_answer']),
                    'explanation' => isset($question_data['explanation']) ? wp_kses_post($question_data['explanation']) : '',
                    'difficulty' => isset($question_data['difficulty']) ? sanitize_text_field($question_data['difficulty']) : 'medium',
                    'tags' => isset($question_data['tags']) ? sanitize_text_field($question_data['tags']) : '',
                    'image_url' => isset($question_data['image_url']) ? esc_url_raw($question_data['image_url']) : '',
                    'reference' => isset($question_data['reference']) ? sanitize_text_field($question_data['reference']) : '',
                );

                // Check for duplicates
                if ($options['skip_duplicates'] || $options['update_existing']) {
                    $existing_id = $this->find_duplicate_question($prepared_question);
                    
                    if ($existing_id) {
                        if ($options['update_existing']) {
                            // Update existing question
                            $result = $wpdb->update(
                                $wpdb->prefix . 'qmj_questions',
                                $prepared_question,
                                array('id' => $existing_id),
                                array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
                                array('%d')
                            );

                            if ($result !== false) {
                                $updated_count++;
                                $import_log[] = sprintf(__('Question %d: Updated existing question.', 'quiz-master-json'), $question_number);
                            } else {
                                $error_count++;
                                $import_log[] = sprintf(__('Question %d: Failed to update existing question.', 'quiz-master-json'), $question_number);
                            }
                        } else {
                            // Skip duplicate
                            $skipped_count++;
                            $import_log[] = sprintf(__('Question %d: Skipped duplicate question.', 'quiz-master-json'), $question_number);
                        }
                        continue;
                    }
                }

                // Insert new question
                $result = $wpdb->insert(
                    $wpdb->prefix . 'qmj_questions',
                    $prepared_question,
                    array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
                );

                if ($result) {
                    $imported_count++;
                    $import_log[] = sprintf(__('Question %d: Successfully imported.', 'quiz-master-json'), $question_number);
                } else {
                    $error_count++;
                    $import_log[] = sprintf(__('Question %d: Database insert failed.', 'quiz-master-json'), $question_number);
                }
            }

            // Update category question counts
            $this->update_all_category_question_counts();

            // Commit transaction
            $wpdb->query('COMMIT');

            // Log import history
            $this->log_import_history($filename, $imported_count, $updated_count, $skipped_count, $error_count, $import_log);

            // Prepare success message
            $details = '';
            if ($imported_count > 0) {
                $details .= '<p><strong>' . sprintf(__('Imported: %d questions', 'quiz-master-json'), $imported_count) . '</strong></p>';
            }
            if ($updated_count > 0) {
                $details .= '<p><strong>' . sprintf(__('Updated: %d questions', 'quiz-master-json'), $updated_count) . '</strong></p>';
            }
            if ($skipped_count > 0) {
                $details .= '<p>' . sprintf(__('Skipped: %d duplicates', 'quiz-master-json'), $skipped_count) . '</p>';
            }
            if ($error_count > 0) {
                $details .= '<p style="color: #d63638;"><strong>' . sprintf(__('Errors: %d questions', 'quiz-master-json'), $error_count) . '</strong></p>';
            }

            if (!empty($category_map)) {
                $details .= '<h4>' . __('Categories Created/Used:', 'quiz-master-json') . '</h4><ul>';
                foreach ($category_map as $category_name => $category_id) {
                    $details .= '<li>' . esc_html($category_name) . '</li>';
                }
                $details .= '</ul>';
            }

            return array(
                'success' => true,
                'message' => sprintf(__('Import completed successfully! Processed %d questions.', 'quiz-master-json'), $imported_count + $updated_count + $skipped_count + $error_count),
                'details' => $details
            );

        } catch (Exception $e) {
            // Rollback on error
            $wpdb->query('ROLLBACK');
            
            return array(
                'success' => false,
                'message' => __('Import failed due to database error: ', 'quiz-master-json') . $e->getMessage()
            );
        }
    }

    /**
     * Get or create category for question
     *
     * @since    1.0.0
     * @param    array    $question_data
     * @param    array    $options
     * @param    array    &$category_map
     * @return   int|false Category ID or false on failure
     */
    private function get_or_create_category($question_data, $options, &$category_map) {
        global $wpdb;

        // Use default category if no category specified
        if (empty($question_data['category']) && $options['default_category'] > 0) {
            return $options['default_category'];
        }

        // Get category name
        $category_name = isset($question_data['category']) ? sanitize_text_field($question_data['category']) : 'Imported Questions';
        
        // Check if we already processed this category
        if (isset($category_map[$category_name])) {
            return $category_map[$category_name];
        }

        // Try to find existing category
        $existing_category = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}qmj_categories WHERE name = %s OR slug = %s",
            $category_name,
            sanitize_title($category_name)
        ));

        if ($existing_category) {
            $category_map[$category_name] = $existing_category->id;
            return $existing_category->id;
        }

        // Create new category if allowed
        if ($options['create_categories']) {
            $category_slug = sanitize_title($category_name);
            $category_description = isset($question_data['category_description']) ? 
                wp_kses_post($question_data['category_description']) : 
                sprintf(__('Category created during JSON import for "%s"', 'quiz-master-json'), $category_name);

            $result = $wpdb->insert(
                $wpdb->prefix . 'qmj_categories',
                array(
                    'name' => $category_name,
                    'slug' => $category_slug,
                    'description' => $category_description,
                    'question_count' => 0
                ),
                array('%s', '%s', '%s', '%d')
            );

            if ($result) {
                $category_id = $wpdb->insert_id;
                $category_map[$category_name] = $category_id;
                return $category_id;
            }
        }

        return false;
    }

    /**
     * Find duplicate question
     *
     * @since    1.0.0
     * @param    array    $question_data
     * @return   int|false Question ID if found, false otherwise
     */
    private function find_duplicate_question($question_data) {
        global $wpdb;

        // Check for exact question text match in the same category
        $existing_question = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}qmj_questions 
             WHERE category_id = %d AND question = %s",
            $question_data['category_id'],
            $question_data['question']
        ));

        return $existing_question ? intval($existing_question) : false;
    }

    /**
     * Update all category question counts
     *
     * @since    1.0.0
     */
    private function update_all_category_question_counts() {
        global $wpdb;

        $wpdb->query("
            UPDATE {$wpdb->prefix}qmj_categories c 
            SET question_count = (
                SELECT COUNT(*) 
                FROM {$wpdb->prefix}qmj_questions q 
                WHERE q.category_id = c.id
            )
        ");
    }

    /**
     * Log import history
     *
     * @since    1.0.0
     * @param    string   $filename
     * @param    int      $imported
     * @param    int      $updated
     * @param    int      $skipped
     * @param    int      $errors
     * @param    array    $log
     */
    private function log_import_history($filename, $imported, $updated, $skipped, $errors, $log) {
        $history = get_option('qmj_import_history', array());
        
        $import_record = array(
            'timestamp' => current_time('mysql'),
            'filename' => $filename,
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
            'total_processed' => $imported + $updated + $skipped + $errors,
            'user_id' => get_current_user_id(),
            'log' => $log
        );

        // Keep only last 50 imports
        $history[] = $import_record;
        if (count($history) > 50) {
            $history = array_slice($history, -50);
        }

        update_option('qmj_import_history', $history);
    }

    /**
     * Handle data export
     *
     * @since    1.0.0
     */
    private function handle_data_export() {
        global $wpdb;

        $export_type = sanitize_text_field($_POST['export_type']);
        $categories = isset($_POST['export_categories']) ? array_map('absint', $_POST['export_categories']) : array();
        $include_results = isset($_POST['include_results']);
        $format = sanitize_text_field($_POST['export_format']);

        $export_data = array(
            'export_info' => array(
                'plugin' => 'Quiz Master JSON',
                'version' => QUIZ_MASTER_JSON_VERSION,
                'export_date' => current_time('Y-m-d H:i:s'),
                'export_type' => $export_type,
                'site_url' => get_site_url(),
            )
        );

        // Export categories
        if ($export_type === 'all' || $export_type === 'categories') {
            $categories_query = "SELECT * FROM {$wpdb->prefix}qmj_categories ORDER BY name";
            $export_data['categories'] = $wpdb->get_results($categories_query, ARRAY_A);
        }

        // Export questions
        if ($export_type === 'all' || $export_type === 'questions') {
            $where_clause = '';
            $query_params = array();

            if (!empty($categories)) {
                $placeholders = implode(',', array_fill(0, count($categories), '%d'));
                $where_clause = "WHERE q.category_id IN ($placeholders)";
                $query_params = $categories;
            }

            $questions_query = "
                SELECT q.*, c.name as category_name, c.slug as category_slug 
                FROM {$wpdb->prefix}qmj_questions q 
                LEFT JOIN {$wpdb->prefix}qmj_categories c ON q.category_id = c.id 
                $where_clause
                ORDER BY c.name, q.created_at
            ";

            if (!empty($query_params)) {
                $questions = $wpdb->get_results($wpdb->prepare($questions_query, $query_params), ARRAY_A);
            } else {
                $questions = $wpdb->get_results($questions_query, ARRAY_A);
            }

            // Format questions for export
            $formatted_questions = array();
            foreach ($questions as $question) {
                $formatted_question = array(
                    'category' => $question['category_name'],
                    'category_slug' => $question['category_slug'],
                    'question' => $question['question'],
                    'options' => json_decode($question['options'], true),
                    'correct_answer' => $question['correct_answer'],
                    'explanation' => $question['explanation'],
                    'difficulty' => $question['difficulty'],
                    'tags' => $question['tags'],
                    'image_url' => $question['image_url'],
                    'reference' => $question['reference'],
                );
                $formatted_questions[] = $formatted_question;
            }

            $export_data['questions'] = $formatted_questions;
            $export_data['export_info']['question_count'] = count($formatted_questions);
        }

        // Export results (if requested)
        if ($include_results && ($export_type === 'all' || $export_type === 'results')) {
            $results_query = "
                SELECT r.*, q.post_id, p.post_title as quiz_title
                FROM {$wpdb->prefix}qmj_results r
                JOIN {$wpdb->prefix}qmj_quizzes q ON r.quiz_id = q.id
                JOIN {$wpdb->posts} p ON q.post_id = p.ID
                ORDER BY r.completed_at DESC
                LIMIT 1000
            ";

            $export_data['results'] = $wpdb->get_results($results_query, ARRAY_A);
        }

        // Generate filename
        $timestamp = date('Y-m-d-H-i-s');
        $filename = "quiz-master-json-export-{$export_type}-{$timestamp}";

        // Export based on format
        if ($format === 'json') {
            $this->export_as_json($export_data, $filename);
        } else {
            $this->export_as_csv($export_data, $filename);
        }
    }

    /**
     * Export data as JSON
     *
     * @since    1.0.0
     * @param    array    $data
     * @param    string   $filename
     */
    private function export_as_json($data, $filename) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '.json"');
        header('Cache-Control: must-revalidate');
        
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Export data as CSV
     *
     * @since    1.0.0
     * @param    array    $data
     * @param    string   $filename
     */
    private function export_as_csv($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Cache-Control: must-revalidate');
        
        $output = fopen('php://output', 'w');

        if (isset($data['questions'])) {
            // CSV headers for questions
            fputcsv($output, array(
                'Category',
                'Question',
                'Option A',
                'Option B',
                'Option C',
                'Option D',
                'Correct Answer',
                'Explanation',
                'Difficulty',
                'Tags',
                'Image URL',
                'Reference'
            ));

            // Export questions
            foreach ($data['questions'] as $question) {
                $options = $question['options'];
                fputcsv($output, array(
                    $question['category'],
                    strip_tags($question['question']),
                    $options['A'] ?? '',
                    $options['B'] ?? '',
                    $options['C'] ?? '',
                    $options['D'] ?? '',
                    $question['correct_answer'],
                    strip_tags($question['explanation']),
                    $question['difficulty'],
                    $question['tags'],
                    $question['image_url'],
                    $question['reference']
                ));
            }
        }

        fclose($output);
        exit;
    }
}