<?php
/**
 * Question Manager page template
 *
 * @link       https://github.com/quiz-master-json
 * @since      1.0.0
 *
 * @package    Quiz_Master_JSON
 * @subpackage Quiz_Master_JSON/admin/partials
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap qmj-questions-page">
    <h1 class="wp-heading-inline">
        <?php _e('Questions', 'quiz-master-json'); ?>
    </h1>
    
    <a href="<?php echo admin_url('admin.php?page=quiz-master-json-questions&action=add'); ?>" class="page-title-action">
        <?php _e('Add New Question', 'quiz-master-json'); ?>
    </a>
    
    <a href="<?php echo admin_url('admin.php?page=quiz-master-json-import'); ?>" class="page-title-action">
        <?php _e('Import from JSON', 'quiz-master-json'); ?>
    </a>

    <hr class="wp-header-end">

    <!-- Filters and Search -->
    <div class="qmj-filters-bar">
        <form method="get" id="qmj-questions-filter" class="qmj-filter-form">
            <input type="hidden" name="page" value="quiz-master-json-questions">
            
            <div class="qmj-filter-group">
                <label for="category-filter"><?php _e('Category:', 'quiz-master-json'); ?></label>
                <select name="category" id="category-filter">
                    <option value=""><?php _e('All Categories', 'quiz-master-json'); ?></option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo esc_attr($category['id']); ?>" <?php selected($category_filter, $category['id']); ?>>
                            <?php echo esc_html($category['name']); ?> (<?php echo $category['question_count']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="qmj-filter-group">
                <label for="difficulty-filter"><?php _e('Difficulty:', 'quiz-master-json'); ?></label>
                <select name="difficulty" id="difficulty-filter">
                    <option value=""><?php _e('All Difficulties', 'quiz-master-json'); ?></option>
                    <option value="easy" <?php selected($difficulty_filter, 'easy'); ?>><?php _e('Easy', 'quiz-master-json'); ?></option>
                    <option value="medium" <?php selected($difficulty_filter, 'medium'); ?>><?php _e('Medium', 'quiz-master-json'); ?></option>
                    <option value="hard" <?php selected($difficulty_filter, 'hard'); ?>><?php _e('Hard', 'quiz-master-json'); ?></option>
                </select>
            </div>

            <div class="qmj-filter-group">
                <label for="search-input"><?php _e('Search:', 'quiz-master-json'); ?></label>
                <input type="search" id="search-input" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search questions...', 'quiz-master-json'); ?>">
            </div>

            <div class="qmj-filter-group">
                <input type="submit" class="button" value="<?php esc_attr_e('Filter', 'quiz-master-json'); ?>">
                <a href="<?php echo admin_url('admin.php?page=quiz-master-json-questions'); ?>" class="button">
                    <?php _e('Clear', 'quiz-master-json'); ?>
                </a>
            </div>
        </form>
    </div>

    <?php if (empty($questions)): ?>
        <div class="qmj-empty-state">
            <div class="qmj-empty-icon">
                <span class="dashicons dashicons-editor-help"></span>
            </div>
            <h3><?php _e('No questions found', 'quiz-master-json'); ?></h3>
            <p>
                <?php if (!empty($search) || $category_filter || $difficulty_filter): ?>
                    <?php _e('No questions match your current filters.', 'quiz-master-json'); ?>
                    <a href="<?php echo admin_url('admin.php?page=quiz-master-json-questions'); ?>">
                        <?php _e('Clear filters', 'quiz-master-json'); ?>
                    </a>
                <?php else: ?>
                    <?php _e('Start building your question bank by adding questions manually or importing from JSON files.', 'quiz-master-json'); ?>
                <?php endif; ?>
            </p>
            <div class="qmj-empty-actions">
                <a href="<?php echo admin_url('admin.php?page=quiz-master-json-questions&action=add'); ?>" class="button button-primary button-large">
                    <?php _e('Add Your First Question', 'quiz-master-json'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=quiz-master-json-import'); ?>" class="button button-secondary button-large">
                    <?php _e('Import from JSON', 'quiz-master-json'); ?>
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Bulk Actions -->
        <form method="post" action="<?php echo admin_url('admin.php?page=quiz-master-json-questions&action=bulk'); ?>">
            <?php wp_nonce_field('qmj_bulk_action', 'qmj_bulk_nonce'); ?>
            
            <div class="qmj-bulk-actions">
                <select name="bulk_action" id="bulk-action-selector">
                    <option value=""><?php _e('Bulk Actions', 'quiz-master-json'); ?></option>
                    <option value="delete"><?php _e('Delete', 'quiz-master-json'); ?></option>
                    <option value="change_category"><?php _e('Change Category', 'quiz-master-json'); ?></option>
                    <option value="change_difficulty"><?php _e('Change Difficulty', 'quiz-master-json'); ?></option>
                    <option value="export"><?php _e('Export to JSON', 'quiz-master-json'); ?></option>
                </select>

                <!-- Additional fields for bulk actions -->
                <select name="new_category_id" id="bulk-category" style="display: none;">
                    <option value=""><?php _e('Select Category', 'quiz-master-json'); ?></option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo esc_attr($category['id']); ?>">
                            <?php echo esc_html($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="new_difficulty" id="bulk-difficulty" style="display: none;">
                    <option value=""><?php _e('Select Difficulty', 'quiz-master-json'); ?></option>
                    <option value="easy"><?php _e('Easy', 'quiz-master-json'); ?></option>
                    <option value="medium"><?php _e('Medium', 'quiz-master-json'); ?></option>
                    <option value="hard"><?php _e('Hard', 'quiz-master-json'); ?></option>
                </select>

                <input type="submit" class="button" value="<?php esc_attr_e('Apply', 'quiz-master-json'); ?>" disabled>
            </div>

            <!-- Questions Table -->
            <table class="wp-list-table widefat fixed striped qmj-questions-table">
                <thead>
                    <tr>
                        <td class="manage-column column-cb check-column">
                            <input type="checkbox" id="cb-select-all">
                        </td>
                        <th class="manage-column column-question column-primary">
                            <?php _e('Question', 'quiz-master-json'); ?>
                        </th>
                        <th class="manage-column column-category">
                            <?php _e('Category', 'quiz-master-json'); ?>
                        </th>
                        <th class="manage-column column-difficulty">
                            <?php _e('Difficulty', 'quiz-master-json'); ?>
                        </th>
                        <th class="manage-column column-options">
                            <?php _e('Options', 'quiz-master-json'); ?>
                        </th>
                        <th class="manage-column column-correct">
                            <?php _e('Correct Answer', 'quiz-master-json'); ?>
                        </th>
                        <th class="manage-column column-date">
                            <?php _e('Date', 'quiz-master-json'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $question): ?>
                        <?php 
                        $options = json_decode($question['options'], true);
                        $edit_url = admin_url('admin.php?page=quiz-master-json-questions&action=edit&id=' . $question['id']);
                        $delete_url = wp_nonce_url(
                            admin_url('admin.php?page=quiz-master-json-questions&action=delete&id=' . $question['id']),
                            'qmj_delete_question_' . $question['id']
                        );
                        ?>
                        <tr>
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="question_ids[]" value="<?php echo esc_attr($question['id']); ?>">
                            </th>
                            <td class="column-question column-primary">
                                <strong>
                                    <a href="<?php echo esc_url($edit_url); ?>">
                                        <?php echo esc_html(wp_trim_words(strip_tags($question['question']), 10)); ?>
                                    </a>
                                </strong>
                                
                                <?php if (!empty($question['image_url'])): ?>
                                    <span class="qmj-question-meta">
                                        <span class="dashicons dashicons-camera" title="<?php esc_attr_e('Has image', 'quiz-master-json'); ?>"></span>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($question['tags'])): ?>
                                    <div class="qmj-question-tags">
                                        <?php 
                                        $tags = array_map('trim', explode(',', $question['tags']));
                                        foreach ($tags as $tag): 
                                        ?>
                                            <span class="qmj-tag"><?php echo esc_html($tag); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="row-actions">
                                    <span class="edit">
                                        <a href="<?php echo esc_url($edit_url); ?>"><?php _e('Edit', 'quiz-master-json'); ?></a> |
                                    </span>
                                    <span class="delete">
                                        <a href="<?php echo esc_url($delete_url); ?>" 
                                           onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this question?', 'quiz-master-json'); ?>')">
                                            <?php _e('Delete', 'quiz-master-json'); ?>
                                        </a>
                                    </span>
                                </div>
                            </td>
                            <td class="column-category">
                                <span class="qmj-category-badge">
                                    <?php echo esc_html($question['category_name'] ?: __('Uncategorized', 'quiz-master-json')); ?>
                                </span>
                            </td>
                            <td class="column-difficulty">
                                <span class="qmj-difficulty-badge qmj-difficulty-<?php echo esc_attr($question['difficulty']); ?>">
                                    <?php echo esc_html(ucfirst($question['difficulty'])); ?>
                                </span>
                            </td>
                            <td class="column-options">
                                <div class="qmj-options-preview">
                                    <?php if (is_array($options)): ?>
                                        <?php foreach ($options as $key => $option): ?>
                                            <div class="qmj-option-item">
                                                <span class="qmj-option-key"><?php echo esc_html($key); ?>:</span>
                                                <span class="qmj-option-text"><?php echo esc_html(wp_trim_words($option, 5)); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="column-correct">
                                <span class="qmj-correct-answer">
                                    <?php echo esc_html($question['correct_answer']); ?>
                                </span>
                            </td>
                            <td class="column-date">
                                <?php echo date_i18n(get_option('date_format'), strtotime($question['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="qmj-pagination">
                <?php
                $pagination_links = paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo; ' . __('Previous', 'quiz-master-json'),
                    'next_text' => __('Next', 'quiz-master-json') . ' &raquo;',
                    'current' => $current_page,
                    'total' => $total_pages,
                    'type' => 'plain',
                ));
                echo $pagination_links;
                ?>
                
                <div class="qmj-pagination-info">
                    <?php
                    printf(
                        __('Showing %d-%d of %d questions', 'quiz-master-json'),
                        (($current_page - 1) * $per_page) + 1,
                        min($current_page * $per_page, $total_count),
                        $total_count
                    );
                    ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
/* Questions page styles */
.qmj-questions-page {
    background: white;
    margin: 0 -20px;
    padding: 20px;
}

.qmj-filters-bar {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
    margin: 20px 0;
}

.qmj-filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: end;
}

.qmj-filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.qmj-filter-group label {
    font-weight: 500;
    font-size: 13px;
    color: #646970;
}

.qmj-filter-group select,
.qmj-filter-group input[type="search"] {
    min-width: 150px;
}

.qmj-empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #f9f9f9;
    border-radius: 8px;
    margin: 20px 0;
}

.qmj-empty-icon .dashicons {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 20px;
}

.qmj-empty-state h3 {
    color: #646970;
    margin-bottom: 10px;
}

.qmj-empty-actions {
    margin-top: 20px;
}

.qmj-empty-actions .button {
    margin: 0 10px;
}

.qmj-bulk-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    margin: 15px 0;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 4px;
}

.qmj-questions-table {
    border: 1px solid #ddd;
    border-radius: 6px;
}

.qmj-questions-table th {
    background: #f1f1f1;
    font-weight: 600;
}

.qmj-question-meta {
    margin-left: 10px;
    color: #666;
}

.qmj-question-tags {
    margin-top: 8px;
}

.qmj-tag {
    display: inline-block;
    background: #e0e0e0;
    color: #555;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 3px;
    margin-right: 4px;
}

.qmj-category-badge {
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.qmj-difficulty-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
}

.qmj-difficulty-easy {
    background: #d4edda;
    color: #155724;
}

.qmj-difficulty-medium {
    background: #fff3cd;
    color: #856404;
}

.qmj-difficulty-hard {
    background: #f8d7da;
    color: #721c24;
}

.qmj-options-preview {
    font-size: 12px;
}

.qmj-option-item {
    margin-bottom: 3px;
}

.qmj-option-key {
    font-weight: 600;
    color: #666;
}

.qmj-option-text {
    color: #333;
}

.qmj-correct-answer {
    background: #d4edda;
    color: #155724;
    padding: 3px 6px;
    border-radius: 3px;
    font-weight: 600;
}

.qmj-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 6px;
}

.qmj-pagination-info {
    color: #646970;
    font-size: 13px;
}

/* Responsive design */
@media (max-width: 768px) {
    .qmj-filter-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .qmj-filter-group {
        width: 100%;
    }
    
    .qmj-bulk-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .qmj-questions-table .column-options,
    .qmj-questions-table .column-date {
        display: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bulk actions functionality
    const bulkActionSelect = document.getElementById('bulk-action-selector');
    const bulkCategorySelect = document.getElementById('bulk-category');
    const bulkDifficultySelect = document.getElementById('bulk-difficulty');
    const applyButton = document.querySelector('.qmj-bulk-actions input[type="submit"]');
    const selectAllCheckbox = document.getElementById('cb-select-all');
    const individualCheckboxes = document.querySelectorAll('input[name="question_ids[]"]');

    // Show/hide additional fields based on bulk action
    bulkActionSelect.addEventListener('change', function() {
        const action = this.value;
        
        // Hide all additional fields
        bulkCategorySelect.style.display = 'none';
        bulkDifficultySelect.style.display = 'none';
        
        // Show relevant field
        if (action === 'change_category') {
            bulkCategorySelect.style.display = 'inline-block';
        } else if (action === 'change_difficulty') {
            bulkDifficultySelect.style.display = 'inline-block';
        }
        
        updateApplyButton();
    });

    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        individualCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateApplyButton();
    });

    // Individual checkbox functionality
    individualCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('input[name="question_ids[]"]:checked').length;
            selectAllCheckbox.checked = checkedCount === individualCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < individualCheckboxes.length;
            updateApplyButton();
        });
    });

    // Update apply button state
    function updateApplyButton() {
        const hasAction = bulkActionSelect.value !== '';
        const hasSelection = document.querySelectorAll('input[name="question_ids[]"]:checked').length > 0;
        applyButton.disabled = !(hasAction && hasSelection);
    }

    // Confirm bulk delete
    document.querySelector('.qmj-bulk-actions').addEventListener('submit', function(e) {
        if (bulkActionSelect.value === 'delete') {
            const checkedCount = document.querySelectorAll('input[name="question_ids[]"]:checked').length;
            if (!confirm('Are you sure you want to delete ' + checkedCount + ' questions? This action cannot be undone.')) {
                e.preventDefault();
            }
        }
    });
});
</script>