<?php
/**
 * The question management functionality of the plugin.
 *
 * @link       https://github.com/quiz-master-json
 * @since      1.0.0
 *
 * @package    Quiz_Master_JSON
 * @subpackage Quiz_Master_JSON/admin
 */

/**
 * Question management class
 *
 * Handles both manual question creation and management
 *
 * @package    Quiz_Master_JSON
 * @subpackage Quiz_Master_JSON/admin
 * @author     Quiz Master JSON Team
 */
class Quiz_Master_JSON_Question_Manager {

    /**
     * Display the questions management page
     *
     * @since    1.0.0
     */
    public function display() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $question_id = isset($_GET['id']) ? absint($_GET['id']) : 0;

        switch ($action) {
            case 'add':
                $this->display_add_question_form();
                break;
            case 'edit':
                $this->display_edit_question_form($question_id);
                break;
            case 'delete':
                $this->handle_delete_question($question_id);
                break;
            case 'bulk':
                $this->handle_bulk_actions();
                break;
            default:
                $this->display_questions_list();
                break;
        }
    }

    /**
     * Display questions list
     *
     * @since    1.0.0
     */
    private function display_questions_list() {
        global $wpdb;

        // Handle search and filters
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $category_filter = isset($_GET['category']) ? absint($_GET['category']) : 0;
        $difficulty_filter = isset($_GET['difficulty']) ? sanitize_text_field($_GET['difficulty']) : '';

        // Build query
        $where_conditions = array('1=1');
        $query_params = array();

        if (!empty($search)) {
            $where_conditions[] = "(q.question LIKE %s OR q.explanation LIKE %s OR q.tags LIKE %s)";
            $query_params[] = "%{$search}%";
            $query_params[] = "%{$search}%";
            $query_params[] = "%{$search}%";
        }

        if ($category_filter > 0) {
            $where_conditions[] = "q.category_id = %d";
            $query_params[] = $category_filter;
        }

        if (!empty($difficulty_filter)) {
            $where_conditions[] = "q.difficulty = %s";
            $query_params[] = $difficulty_filter;
        }

        $where_clause = implode(' AND ', $where_conditions);

        // Get total count
        $total_query = "SELECT COUNT(*) FROM {$wpdb->prefix}qmj_questions q WHERE {$where_clause}";
        if (!empty($query_params)) {
            $total_count = $wpdb->get_var($wpdb->prepare($total_query, $query_params));
        } else {
            $total_count = $wpdb->get_var($total_query);
        }

        // Pagination
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        $total_pages = ceil($total_count / $per_page);

        // Get questions
        $questions_query = "
            SELECT q.*, c.name as category_name 
            FROM {$wpdb->prefix}qmj_questions q 
            LEFT JOIN {$wpdb->prefix}qmj_categories c ON q.category_id = c.id 
            WHERE {$where_clause} 
            ORDER BY q.created_at DESC 
            LIMIT %d OFFSET %d
        ";

        $query_params[] = $per_page;
        $query_params[] = $offset;

        if (!empty($query_params)) {
            $questions = $wpdb->get_results($wpdb->prepare($questions_query, $query_params), ARRAY_A);
        } else {
            $questions = $wpdb->get_results($questions_query, ARRAY_A);
        }

        // Get categories for filter
        $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}qmj_categories ORDER BY name", ARRAY_A);

        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/partials/question-manager-page.php';
    }

    /**
     * Display add question form
     *
     * @since    1.0.0
     */
    private function display_add_question_form() {
        if ($_POST && isset($_POST['qmj_add_question_nonce']) && wp_verify_nonce($_POST['qmj_add_question_nonce'], 'qmj_add_question')) {
            $result = $this->save_question();
            if ($result['success']) {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        global $wpdb;
        $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}qmj_categories ORDER BY name", ARRAY_A);
        
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/partials/question-form.php';
    }

    /**
     * Display edit question form
     *
     * @since    1.0.0
     * @param    int    $question_id
     */
    private function display_edit_question_form($question_id) {
        global $wpdb;

        if ($_POST && isset($_POST['qmj_edit_question_nonce']) && wp_verify_nonce($_POST['qmj_edit_question_nonce'], 'qmj_edit_question')) {
            $result = $this->save_question($question_id);
            if ($result['success']) {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        $question = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qmj_questions WHERE id = %d",
            $question_id
        ), ARRAY_A);

        if (!$question) {
            echo '<div class="notice notice-error"><p>' . __('Question not found.', 'quiz-master-json') . '</p></div>';
            return;
        }

        $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}qmj_categories ORDER BY name", ARRAY_A);
        
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/partials/question-form.php';
    }

    /**
     * Save question (add or edit)
     *
     * @since    1.0.0
     * @param    int    $question_id    Optional. Question ID for editing
     * @return   array                  Result array with success status and message
     */
    private function save_question($question_id = 0) {
        global $wpdb;

        // Validate required fields
        $required_fields = array('question', 'category_id', 'option_a', 'option_b', 'correct_answer');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                return array(
                    'success' => false,
                    'message' => sprintf(__('Field "%s" is required.', 'quiz-master-json'), $field)
                );
            }
        }

        // Sanitize and prepare data
        $question_data = array(
            'category_id' => absint($_POST['category_id']),
            'question' => wp_kses_post($_POST['question']),
            'correct_answer' => sanitize_text_field($_POST['correct_answer']),
            'explanation' => wp_kses_post($_POST['explanation']),
            'difficulty' => in_array($_POST['difficulty'], array('easy', 'medium', 'hard')) ? $_POST['difficulty'] : 'medium',
            'tags' => sanitize_text_field($_POST['tags']),
            'image_url' => esc_url_raw($_POST['image_url']),
            'reference' => sanitize_text_field($_POST['reference']),
        );

        // Handle options - support multiple question types
        $question_type = isset($_POST['question_type']) ? sanitize_text_field($_POST['question_type']) : 'multiple_choice';
        $options = array();

        switch ($question_type) {
            case 'multiple_choice':
                $options = array(
                    'A' => sanitize_text_field($_POST['option_a']),
                    'B' => sanitize_text_field($_POST['option_b']),
                );
                
                // Add optional options C and D if provided
                if (!empty($_POST['option_c'])) {
                    $options['C'] = sanitize_text_field($_POST['option_c']);
                }
                if (!empty($_POST['option_d'])) {
                    $options['D'] = sanitize_text_field($_POST['option_d']);
                }
                break;

            case 'true_false':
                $options = array(
                    'A' => __('True', 'quiz-master-json'),
                    'B' => __('False', 'quiz-master-json')
                );
                break;

            case 'fill_blank':
                $options = array(
                    'answer' => sanitize_text_field($_POST['correct_answer_text'])
                );
                $question_data['correct_answer'] = 'answer';
                break;

            case 'matching':
                // Handle matching pairs
                if (isset($_POST['match_left']) && isset($_POST['match_right'])) {
                    $left_items = array_map('sanitize_text_field', $_POST['match_left']);
                    $right_items = array_map('sanitize_text_field', $_POST['match_right']);
                    
                    $options = array(
                        'left' => $left_items,
                        'right' => $right_items,
                        'correct_matches' => array_combine(range(0, count($left_items) - 1), range(0, count($right_items) - 1))
                    );
                }
                break;
        }

        $question_data['options'] = json_encode($options);

        // Validate correct answer
        if ($question_type === 'multiple_choice' && !array_key_exists($question_data['correct_answer'], $options)) {
            return array(
                'success' => false,
                'message' => __('Invalid correct answer selected.', 'quiz-master-json')
            );
        }

        // Database operation
        if ($question_id > 0) {
            // Update existing question
            $result = $wpdb->update(
                $wpdb->prefix . 'qmj_questions',
                $question_data,
                array('id' => $question_id),
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
                array('%d')
            );

            if ($result === false) {
                return array(
                    'success' => false,
                    'message' => __('Failed to update question.', 'quiz-master-json')
                );
            }

            $message = __('Question updated successfully!', 'quiz-master-json');
        } else {
            // Insert new question
            $result = $wpdb->insert(
                $wpdb->prefix . 'qmj_questions',
                $question_data,
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            );

            if ($result === false) {
                return array(
                    'success' => false,
                    'message' => __('Failed to add question.', 'quiz-master-json')
                );
            }

            $message = __('Question added successfully!', 'quiz-master-json');
        }

        // Update category question count
        $this->update_category_question_count($question_data['category_id']);

        return array(
            'success' => true,
            'message' => $message
        );
    }

    /**
     * Handle question deletion
     *
     * @since    1.0.0
     * @param    int    $question_id
     */
    private function handle_delete_question($question_id) {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'qmj_delete_question_' . $question_id)) {
            wp_die(__('Security check failed.', 'quiz-master-json'));
        }

        global $wpdb;

        // Get category ID before deletion
        $category_id = $wpdb->get_var($wpdb->prepare(
            "SELECT category_id FROM {$wpdb->prefix}qmj_questions WHERE id = %d",
            $question_id
        ));

        $result = $wpdb->delete(
            $wpdb->prefix . 'qmj_questions',
            array('id' => $question_id),
            array('%d')
        );

        if ($result) {
            // Update category question count
            if ($category_id) {
                $this->update_category_question_count($category_id);
            }
            
            echo '<div class="notice notice-success"><p>' . __('Question deleted successfully!', 'quiz-master-json') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Failed to delete question.', 'quiz-master-json') . '</p></div>';
        }

        $this->display_questions_list();
    }

    /**
     * Handle bulk actions
     *
     * @since    1.0.0
     */
    private function handle_bulk_actions() {
        if (!isset($_POST['qmj_bulk_nonce']) || !wp_verify_nonce($_POST['qmj_bulk_nonce'], 'qmj_bulk_action')) {
            wp_die(__('Security check failed.', 'quiz-master-json'));
        }

        $action = sanitize_text_field($_POST['bulk_action']);
        $question_ids = array_map('absint', $_POST['question_ids']);

        if (empty($question_ids)) {
            echo '<div class="notice notice-error"><p>' . __('No questions selected.', 'quiz-master-json') . '</p></div>';
            $this->display_questions_list();
            return;
        }

        global $wpdb;

        switch ($action) {
            case 'delete':
                $placeholders = implode(',', array_fill(0, count($question_ids), '%d'));
                $result = $wpdb->query($wpdb->prepare(
                    "DELETE FROM {$wpdb->prefix}qmj_questions WHERE id IN ($placeholders)",
                    $question_ids
                ));

                if ($result) {
                    echo '<div class="notice notice-success"><p>' . sprintf(__('%d questions deleted successfully!', 'quiz-master-json'), $result) . '</p></div>';
                    
                    // Update all category question counts
                    $this->update_all_category_question_counts();
                } else {
                    echo '<div class="notice notice-error"><p>' . __('Failed to delete questions.', 'quiz-master-json') . '</p></div>';
                }
                break;

            case 'change_category':
                $new_category_id = absint($_POST['new_category_id']);
                if ($new_category_id > 0) {
                    $placeholders = implode(',', array_fill(0, count($question_ids), '%d'));
                    $result = $wpdb->query($wpdb->prepare(
                        "UPDATE {$wpdb->prefix}qmj_questions SET category_id = %d WHERE id IN ($placeholders)",
                        array_merge(array($new_category_id), $question_ids)
                    ));

                    if ($result) {
                        echo '<div class="notice notice-success"><p>' . sprintf(__('%d questions moved to new category!', 'quiz-master-json'), $result) . '</p></div>';
                        
                        // Update all category question counts
                        $this->update_all_category_question_counts();
                    } else {
                        echo '<div class="notice notice-error"><p>' . __('Failed to move questions.', 'quiz-master-json') . '</p></div>';
                    }
                }
                break;

            case 'change_difficulty':
                $new_difficulty = sanitize_text_field($_POST['new_difficulty']);
                if (in_array($new_difficulty, array('easy', 'medium', 'hard'))) {
                    $placeholders = implode(',', array_fill(0, count($question_ids), '%d'));
                    $result = $wpdb->query($wpdb->prepare(
                        "UPDATE {$wpdb->prefix}qmj_questions SET difficulty = %s WHERE id IN ($placeholders)",
                        array_merge(array($new_difficulty), $question_ids)
                    ));

                    if ($result) {
                        echo '<div class="notice notice-success"><p>' . sprintf(__('%d questions difficulty updated!', 'quiz-master-json'), $result) . '</p></div>';
                    } else {
                        echo '<div class="notice notice-error"><p>' . __('Failed to update difficulty.', 'quiz-master-json') . '</p></div>';
                    }
                }
                break;

            case 'export':
                $this->export_questions($question_ids);
                return;
        }

        $this->display_questions_list();
    }

    /**
     * Export questions to JSON
     *
     * @since    1.0.0
     * @param    array    $question_ids
     */
    private function export_questions($question_ids) {
        global $wpdb;

        $placeholders = implode(',', array_fill(0, count($question_ids), '%d'));
        $questions = $wpdb->get_results($wpdb->prepare(
            "SELECT q.*, c.name as category_name, c.slug as category_slug 
             FROM {$wpdb->prefix}qmj_questions q 
             LEFT JOIN {$wpdb->prefix}qmj_categories c ON q.category_id = c.id 
             WHERE q.id IN ($placeholders)",
            $question_ids
        ), ARRAY_A);

        $export_data = array(
            'export_info' => array(
                'plugin' => 'Quiz Master JSON',
                'version' => QUIZ_MASTER_JSON_VERSION,
                'export_date' => current_time('Y-m-d H:i:s'),
                'question_count' => count($questions),
            ),
            'questions' => array()
        );

        foreach ($questions as $question) {
            $export_data['questions'][] = array(
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
        }

        $filename = 'quiz-questions-export-' . date('Y-m-d-H-i-s') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate');
        
        echo json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Update category question count
     *
     * @since    1.0.0
     * @param    int    $category_id
     */
    private function update_category_question_count($category_id) {
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}qmj_questions WHERE category_id = %d",
            $category_id
        ));

        $wpdb->update(
            $wpdb->prefix . 'qmj_categories',
            array('question_count' => $count),
            array('id' => $category_id),
            array('%d'),
            array('%d')
        );
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
     * Display categories management
     *
     * @since    1.0.0
     */
    public function display_categories() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $category_id = isset($_GET['id']) ? absint($_GET['id']) : 0;

        switch ($action) {
            case 'add':
                $this->display_add_category_form();
                break;
            case 'edit':
                $this->display_edit_category_form($category_id);
                break;
            case 'delete':
                $this->handle_delete_category($category_id);
                break;
            default:
                $this->display_categories_list();
                break;
        }
    }

    /**
     * Display categories list
     *
     * @since    1.0.0
     */
    private function display_categories_list() {
        global $wpdb;

        $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}qmj_categories ORDER BY name", ARRAY_A);
        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/partials/categories-page.php';
    }

    /**
     * Display add category form
     *
     * @since    1.0.0
     */
    private function display_add_category_form() {
        if ($_POST && isset($_POST['qmj_add_category_nonce']) && wp_verify_nonce($_POST['qmj_add_category_nonce'], 'qmj_add_category')) {
            $result = $this->save_category();
            if ($result['success']) {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/partials/category-form.php';
    }

    /**
     * Display edit category form
     *
     * @since    1.0.0
     * @param    int    $category_id
     */
    private function display_edit_category_form($category_id) {
        global $wpdb;

        if ($_POST && isset($_POST['qmj_edit_category_nonce']) && wp_verify_nonce($_POST['qmj_edit_category_nonce'], 'qmj_edit_category')) {
            $result = $this->save_category($category_id);
            if ($result['success']) {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        $category = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}qmj_categories WHERE id = %d",
            $category_id
        ), ARRAY_A);

        if (!$category) {
            echo '<div class="notice notice-error"><p>' . __('Category not found.', 'quiz-master-json') . '</p></div>';
            return;
        }

        require_once QUIZ_MASTER_JSON_PLUGIN_PATH . 'admin/partials/category-form.php';
    }

    /**
     * Save category (add or edit)
     *
     * @since    1.0.0
     * @param    int    $category_id    Optional. Category ID for editing
     * @return   array                  Result array with success status and message
     */
    private function save_category($category_id = 0) {
        global $wpdb;

        // Validate required fields
        if (empty($_POST['name'])) {
            return array(
                'success' => false,
                'message' => __('Category name is required.', 'quiz-master-json')
            );
        }

        $name = sanitize_text_field($_POST['name']);
        $slug = sanitize_title($_POST['slug'] ?: $name);
        $description = wp_kses_post($_POST['description']);

        // Check for duplicate slug
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}qmj_categories WHERE slug = %s AND id != %d",
            $slug,
            $category_id
        ));

        if ($existing) {
            return array(
                'success' => false,
                'message' => __('Category slug already exists. Please choose a different one.', 'quiz-master-json')
            );
        }

        $category_data = array(
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
        );

        if ($category_id > 0) {
            // Update existing category
            $result = $wpdb->update(
                $wpdb->prefix . 'qmj_categories',
                $category_data,
                array('id' => $category_id),
                array('%s', '%s', '%s'),
                array('%d')
            );

            if ($result === false) {
                return array(
                    'success' => false,
                    'message' => __('Failed to update category.', 'quiz-master-json')
                );
            }

            $message = __('Category updated successfully!', 'quiz-master-json');
        } else {
            // Insert new category
            $result = $wpdb->insert(
                $wpdb->prefix . 'qmj_categories',
                $category_data,
                array('%s', '%s', '%s')
            );

            if ($result === false) {
                return array(
                    'success' => false,
                    'message' => __('Failed to add category.', 'quiz-master-json')
                );
            }

            $message = __('Category added successfully!', 'quiz-master-json');
        }

        return array(
            'success' => true,
            'message' => $message
        );
    }

    /**
     * Handle category deletion
     *
     * @since    1.0.0
     * @param    int    $category_id
     */
    private function handle_delete_category($category_id) {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'qmj_delete_category_' . $category_id)) {
            wp_die(__('Security check failed.', 'quiz-master-json'));
        }

        global $wpdb;

        // Check if category has questions
        $question_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}qmj_questions WHERE category_id = %d",
            $category_id
        ));

        if ($question_count > 0) {
            echo '<div class="notice notice-error"><p>' . sprintf(__('Cannot delete category. It contains %d questions. Please move or delete the questions first.', 'quiz-master-json'), $question_count) . '</p></div>';
            $this->display_categories_list();
            return;
        }

        $result = $wpdb->delete(
            $wpdb->prefix . 'qmj_categories',
            array('id' => $category_id),
            array('%d')
        );

        if ($result) {
            echo '<div class="notice notice-success"><p>' . __('Category deleted successfully!', 'quiz-master-json') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . __('Failed to delete category.', 'quiz-master-json') . '</p></div>';
        }

        $this->display_categories_list();
    }
}