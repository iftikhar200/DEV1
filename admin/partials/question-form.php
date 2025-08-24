<?php
/**
 * Question form template for adding/editing questions manually
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

$is_edit = isset($question) && !empty($question);
$question_data = $is_edit ? $question : array();

// Default values
$defaults = array(
    'question' => '',
    'category_id' => '',
    'options' => '{}',
    'correct_answer' => '',
    'explanation' => '',
    'difficulty' => 'medium',
    'tags' => '',
    'image_url' => '',
    'reference' => '',
);

$question_data = array_merge($defaults, $question_data);
$options = json_decode($question_data['options'], true) ?: array();
$question_type = 'multiple_choice'; // Default type

// Detect question type from options
if (isset($options['A']) && isset($options['B']) && count($options) <= 4) {
    $question_type = 'multiple_choice';
} elseif (isset($options['left']) && isset($options['right'])) {
    $question_type = 'matching';
} elseif (isset($options['answer'])) {
    $question_type = 'fill_blank';
}
?>

<div class="wrap qmj-question-form">
    <h1 class="wp-heading-inline">
        <?php echo $is_edit ? __('Edit Question', 'quiz-master-json') : __('Add New Question', 'quiz-master-json'); ?>
    </h1>
    
    <a href="<?php echo admin_url('admin.php?page=quiz-master-json-questions'); ?>" class="page-title-action">
        <?php _e('Back to Questions', 'quiz-master-json'); ?>
    </a>

    <hr class="wp-header-end">

    <form method="post" id="qmj-question-form" class="qmj-form">
        <?php 
        if ($is_edit) {
            wp_nonce_field('qmj_edit_question', 'qmj_edit_question_nonce');
        } else {
            wp_nonce_field('qmj_add_question', 'qmj_add_question_nonce');
        }
        ?>

        <div class="qmj-form-container">
            <!-- Question Type Selection -->
            <div class="qmj-form-section">
                <h3><?php _e('Question Type', 'quiz-master-json'); ?></h3>
                <div class="qmj-question-types">
                    <label class="qmj-type-option">
                        <input type="radio" name="question_type" value="multiple_choice" <?php checked($question_type, 'multiple_choice'); ?>>
                        <div class="qmj-type-card">
                            <span class="dashicons dashicons-list-view"></span>
                            <h4><?php _e('Multiple Choice', 'quiz-master-json'); ?></h4>
                            <p><?php _e('Traditional MCQ with 2-4 options', 'quiz-master-json'); ?></p>
                        </div>
                    </label>

                    <label class="qmj-type-option">
                        <input type="radio" name="question_type" value="true_false" <?php checked($question_type, 'true_false'); ?>>
                        <div class="qmj-type-card">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <h4><?php _e('True/False', 'quiz-master-json'); ?></h4>
                            <p><?php _e('Simple true or false question', 'quiz-master-json'); ?></p>
                        </div>
                    </label>

                    <label class="qmj-type-option">
                        <input type="radio" name="question_type" value="fill_blank" <?php checked($question_type, 'fill_blank'); ?>>
                        <div class="qmj-type-card">
                            <span class="dashicons dashicons-edit"></span>
                            <h4><?php _e('Fill in the Blank', 'quiz-master-json'); ?></h4>
                            <p><?php _e('Text input answer', 'quiz-master-json'); ?></p>
                        </div>
                    </label>

                    <label class="qmj-type-option">
                        <input type="radio" name="question_type" value="matching" <?php checked($question_type, 'matching'); ?>>
                        <div class="qmj-type-card">
                            <span class="dashicons dashicons-networking"></span>
                            <h4><?php _e('Matching Pairs', 'quiz-master-json'); ?></h4>
                            <p><?php _e('Match items from two lists', 'quiz-master-json'); ?></p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Main Question Content -->
            <div class="qmj-form-section">
                <h3><?php _e('Question Content', 'quiz-master-json'); ?></h3>
                
                <div class="qmj-form-row">
                    <label for="question" class="qmj-label-required">
                        <?php _e('Question Text', 'quiz-master-json'); ?>
                    </label>
                    <div class="qmj-editor-wrapper">
                        <?php
                        wp_editor($question_data['question'], 'question', array(
                            'textarea_name' => 'question',
                            'textarea_rows' => 6,
                            'media_buttons' => true,
                            'teeny' => false,
                            'quicktags' => true,
                            'tinymce' => array(
                                'toolbar1' => 'bold,italic,underline,link,unlink,bullist,numlist,blockquote',
                                'toolbar2' => 'alignleft,aligncenter,alignright,strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo'
                            )
                        ));
                        ?>
                    </div>
                </div>

                <div class="qmj-form-row">
                    <label for="category_id" class="qmj-label-required">
                        <?php _e('Category', 'quiz-master-json'); ?>
                    </label>
                    <div class="qmj-category-selector">
                        <select name="category_id" id="category_id" required>
                            <option value=""><?php _e('Select Category', 'quiz-master-json'); ?></option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo esc_attr($category['id']); ?>" 
                                        <?php selected($question_data['category_id'], $category['id']); ?>>
                                    <?php echo esc_html($category['name']); ?> 
                                    (<?php echo $category['question_count']; ?> <?php _e('questions', 'quiz-master-json'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <a href="<?php echo admin_url('admin.php?page=quiz-master-json-categories&action=add'); ?>" 
                           class="button button-secondary" target="_blank">
                            <?php _e('Add New Category', 'quiz-master-json'); ?>
                        </a>
                    </div>
                </div>

                <div class="qmj-form-row">
                    <label for="difficulty"><?php _e('Difficulty Level', 'quiz-master-json'); ?></label>
                    <select name="difficulty" id="difficulty">
                        <option value="easy" <?php selected($question_data['difficulty'], 'easy'); ?>>
                            <?php _e('Easy', 'quiz-master-json'); ?>
                        </option>
                        <option value="medium" <?php selected($question_data['difficulty'], 'medium'); ?>>
                            <?php _e('Medium', 'quiz-master-json'); ?>
                        </option>
                        <option value="hard" <?php selected($question_data['difficulty'], 'hard'); ?>>
                            <?php _e('Hard', 'quiz-master-json'); ?>
                        </option>
                    </select>
                </div>
            </div>

            <!-- Question Options -->
            <div class="qmj-form-section" id="question-options">
                <h3><?php _e('Answer Options', 'quiz-master-json'); ?></h3>

                <!-- Multiple Choice Options -->
                <div id="multiple-choice-options" class="qmj-options-container" style="display: none;">
                    <div class="qmj-option-row">
                        <div class="qmj-option-input">
                            <label for="option_a" class="qmj-label-required"><?php _e('Option A', 'quiz-master-json'); ?></label>
                            <input type="text" id="option_a" name="option_a" 
                                   value="<?php echo esc_attr($options['A'] ?? ''); ?>" 
                                   placeholder="<?php esc_attr_e('Enter option A...', 'quiz-master-json'); ?>">
                        </div>
                        <div class="qmj-correct-radio">
                            <label>
                                <input type="radio" name="correct_answer" value="A" 
                                       <?php checked($question_data['correct_answer'], 'A'); ?>>
                                <?php _e('Correct', 'quiz-master-json'); ?>
                            </label>
                        </div>
                    </div>

                    <div class="qmj-option-row">
                        <div class="qmj-option-input">
                            <label for="option_b" class="qmj-label-required"><?php _e('Option B', 'quiz-master-json'); ?></label>
                            <input type="text" id="option_b" name="option_b" 
                                   value="<?php echo esc_attr($options['B'] ?? ''); ?>" 
                                   placeholder="<?php esc_attr_e('Enter option B...', 'quiz-master-json'); ?>">
                        </div>
                        <div class="qmj-correct-radio">
                            <label>
                                <input type="radio" name="correct_answer" value="B" 
                                       <?php checked($question_data['correct_answer'], 'B'); ?>>
                                <?php _e('Correct', 'quiz-master-json'); ?>
                            </label>
                        </div>
                    </div>

                    <div class="qmj-option-row">
                        <div class="qmj-option-input">
                            <label for="option_c"><?php _e('Option C', 'quiz-master-json'); ?> <span class="qmj-optional">(<?php _e('Optional', 'quiz-master-json'); ?>)</span></label>
                            <input type="text" id="option_c" name="option_c" 
                                   value="<?php echo esc_attr($options['C'] ?? ''); ?>" 
                                   placeholder="<?php esc_attr_e('Enter option C...', 'quiz-master-json'); ?>">
                        </div>
                        <div class="qmj-correct-radio">
                            <label>
                                <input type="radio" name="correct_answer" value="C" 
                                       <?php checked($question_data['correct_answer'], 'C'); ?>>
                                <?php _e('Correct', 'quiz-master-json'); ?>
                            </label>
                        </div>
                    </div>

                    <div class="qmj-option-row">
                        <div class="qmj-option-input">
                            <label for="option_d"><?php _e('Option D', 'quiz-master-json'); ?> <span class="qmj-optional">(<?php _e('Optional', 'quiz-master-json'); ?>)</span></label>
                            <input type="text" id="option_d" name="option_d" 
                                   value="<?php echo esc_attr($options['D'] ?? ''); ?>" 
                                   placeholder="<?php esc_attr_e('Enter option D...', 'quiz-master-json'); ?>">
                        </div>
                        <div class="qmj-correct-radio">
                            <label>
                                <input type="radio" name="correct_answer" value="D" 
                                       <?php checked($question_data['correct_answer'], 'D'); ?>>
                                <?php _e('Correct', 'quiz-master-json'); ?>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- True/False Options -->
                <div id="true-false-options" class="qmj-options-container" style="display: none;">
                    <div class="qmj-tf-options">
                        <label class="qmj-tf-option">
                            <input type="radio" name="correct_answer" value="A" 
                                   <?php checked($question_data['correct_answer'], 'A'); ?>>
                            <span class="qmj-tf-label qmj-tf-true"><?php _e('True', 'quiz-master-json'); ?></span>
                        </label>
                        <label class="qmj-tf-option">
                            <input type="radio" name="correct_answer" value="B" 
                                   <?php checked($question_data['correct_answer'], 'B'); ?>>
                            <span class="qmj-tf-label qmj-tf-false"><?php _e('False', 'quiz-master-json'); ?></span>
                        </label>
                    </div>
                </div>

                <!-- Fill in the Blank -->
                <div id="fill-blank-options" class="qmj-options-container" style="display: none;">
                    <div class="qmj-form-row">
                        <label for="correct_answer_text" class="qmj-label-required">
                            <?php _e('Correct Answer', 'quiz-master-json'); ?>
                        </label>
                        <input type="text" id="correct_answer_text" name="correct_answer_text" 
                               value="<?php echo esc_attr($options['answer'] ?? ''); ?>" 
                               placeholder="<?php esc_attr_e('Enter the correct answer...', 'quiz-master-json'); ?>">
                        <p class="description">
                            <?php _e('Enter the exact text that should be considered correct. You can add multiple correct answers separated by semicolons (;).', 'quiz-master-json'); ?>
                        </p>
                    </div>
                </div>

                <!-- Matching Pairs -->
                <div id="matching-options" class="qmj-options-container" style="display: none;">
                    <div class="qmj-matching-container">
                        <div class="qmj-matching-column">
                            <h4><?php _e('Left Column', 'quiz-master-json'); ?></h4>
                            <div id="matching-left">
                                <?php 
                                $left_items = $options['left'] ?? array('', '', '', '');
                                for ($i = 0; $i < 4; $i++): 
                                ?>
                                    <div class="qmj-matching-item">
                                        <label><?php printf(__('Item %d', 'quiz-master-json'), $i + 1); ?></label>
                                        <input type="text" name="match_left[]" 
                                               value="<?php echo esc_attr($left_items[$i] ?? ''); ?>" 
                                               placeholder="<?php esc_attr_e('Enter left item...', 'quiz-master-json'); ?>">
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="qmj-matching-column">
                            <h4><?php _e('Right Column', 'quiz-master-json'); ?></h4>
                            <div id="matching-right">
                                <?php 
                                $right_items = $options['right'] ?? array('', '', '', '');
                                for ($i = 0; $i < 4; $i++): 
                                ?>
                                    <div class="qmj-matching-item">
                                        <label><?php printf(__('Item %d', 'quiz-master-json'), $i + 1); ?></label>
                                        <input type="text" name="match_right[]" 
                                               value="<?php echo esc_attr($right_items[$i] ?? ''); ?>" 
                                               placeholder="<?php esc_attr_e('Enter right item...', 'quiz-master-json'); ?>">
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    <p class="description">
                        <?php _e('Items will be automatically matched by their order (Item 1 left matches Item 1 right, etc.).', 'quiz-master-json'); ?>
                    </p>
                </div>
            </div>

            <!-- Media & Additional Content -->
            <div class="qmj-form-section">
                <h3><?php _e('Media & Additional Content', 'quiz-master-json'); ?></h3>
                
                <div class="qmj-form-row">
                    <label for="image_url"><?php _e('Question Image', 'quiz-master-json'); ?></label>
                    <div class="qmj-media-upload">
                        <input type="url" id="image_url" name="image_url" 
                               value="<?php echo esc_attr($question_data['image_url']); ?>" 
                               placeholder="<?php esc_attr_e('Image URL...', 'quiz-master-json'); ?>">
                        <button type="button" class="button qmj-upload-button">
                            <?php _e('Upload Image', 'quiz-master-json'); ?>
                        </button>
                        <button type="button" class="button qmj-remove-image" style="display: none;">
                            <?php _e('Remove', 'quiz-master-json'); ?>
                        </button>
                    </div>
                    <div class="qmj-image-preview" style="<?php echo empty($question_data['image_url']) ? 'display: none;' : ''; ?>">
                        <img src="<?php echo esc_url($question_data['image_url']); ?>" alt="<?php esc_attr_e('Question image', 'quiz-master-json'); ?>">
                    </div>
                </div>

                <div class="qmj-form-row">
                    <label for="explanation"><?php _e('Explanation', 'quiz-master-json'); ?></label>
                    <textarea id="explanation" name="explanation" rows="4" 
                              placeholder="<?php esc_attr_e('Optional explanation shown after answering...', 'quiz-master-json'); ?>"><?php echo esc_textarea($question_data['explanation']); ?></textarea>
                    <p class="description">
                        <?php _e('This explanation will be shown to users after they answer the question (if enabled in quiz settings).', 'quiz-master-json'); ?>
                    </p>
                </div>

                <div class="qmj-form-row">
                    <label for="tags"><?php _e('Tags', 'quiz-master-json'); ?></label>
                    <input type="text" id="tags" name="tags" 
                           value="<?php echo esc_attr($question_data['tags']); ?>" 
                           placeholder="<?php esc_attr_e('e.g., programming, javascript, loops', 'quiz-master-json'); ?>">
                    <p class="description">
                        <?php _e('Separate tags with commas. Tags help organize and search questions.', 'quiz-master-json'); ?>
                    </p>
                </div>

                <div class="qmj-form-row">
                    <label for="reference"><?php _e('Reference/Source', 'quiz-master-json'); ?></label>
                    <input type="text" id="reference" name="reference" 
                           value="<?php echo esc_attr($question_data['reference']); ?>" 
                           placeholder="<?php esc_attr_e('Book, URL, or other reference...', 'quiz-master-json'); ?>">
                </div>
            </div>

            <!-- Form Actions -->
            <div class="qmj-form-actions">
                <input type="submit" class="button button-primary button-large" 
                       value="<?php echo $is_edit ? esc_attr__('Update Question', 'quiz-master-json') : esc_attr__('Add Question', 'quiz-master-json'); ?>">
                <a href="<?php echo admin_url('admin.php?page=quiz-master-json-questions'); ?>" 
                   class="button button-secondary button-large">
                    <?php _e('Cancel', 'quiz-master-json'); ?>
                </a>
                
                <?php if ($is_edit): ?>
                    <a href="<?php echo wp_nonce_url(
                        admin_url('admin.php?page=quiz-master-json-questions&action=delete&id=' . $question_data['id']),
                        'qmj_delete_question_' . $question_data['id']
                    ); ?>" 
                       class="button button-link-delete"
                       onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this question?', 'quiz-master-json'); ?>')">
                        <?php _e('Delete Question', 'quiz-master-json'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<style>
/* Question Form Styles */
.qmj-question-form {
    background: white;
    margin: 0 -20px;
    padding: 20px;
}

.qmj-form-container {
    max-width: 1200px;
    margin: 0 auto;
}

.qmj-form-section {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.qmj-form-section h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #1d2327;
    border-bottom: 2px solid #0073aa;
    padding-bottom: 10px;
}

.qmj-question-types {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.qmj-type-option {
    cursor: pointer;
}

.qmj-type-option input[type="radio"] {
    display: none;
}

.qmj-type-card {
    background: white;
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
    height: 120px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.qmj-type-card .dashicons {
    font-size: 32px;
    color: #666;
    margin-bottom: 10px;
}

.qmj-type-card h4 {
    margin: 5px 0;
    color: #1d2327;
}

.qmj-type-card p {
    margin: 0;
    font-size: 12px;
    color: #646970;
}

.qmj-type-option input[type="radio"]:checked + .qmj-type-card {
    border-color: #0073aa;
    background: #e3f2fd;
}

.qmj-type-option input[type="radio"]:checked + .qmj-type-card .dashicons {
    color: #0073aa;
}

.qmj-form-row {
    margin-bottom: 20px;
}

.qmj-form-row label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #1d2327;
}

.qmj-label-required::after {
    content: ' *';
    color: #d63638;
}

.qmj-optional {
    font-weight: normal;
    color: #646970;
    font-size: 12px;
}

.qmj-form-row input[type="text"],
.qmj-form-row input[type="url"],
.qmj-form-row select,
.qmj-form-row textarea {
    width: 100%;
    max-width: 600px;
}

.qmj-editor-wrapper {
    max-width: 800px;
}

.qmj-category-selector {
    display: flex;
    gap: 10px;
    align-items: center;
}

.qmj-category-selector select {
    flex: 1;
    max-width: 400px;
}

.qmj-options-container {
    background: white;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 20px;
}

.qmj-option-row {
    display: flex;
    gap: 15px;
    align-items: end;
    margin-bottom: 15px;
}

.qmj-option-input {
    flex: 1;
}

.qmj-option-input label {
    margin-bottom: 5px;
}

.qmj-correct-radio {
    flex-shrink: 0;
    padding-bottom: 5px;
}

.qmj-correct-radio label {
    display: flex;
    align-items: center;
    gap: 5px;
    background: #f0f0f1;
    padding: 8px 12px;
    border-radius: 4px;
    margin: 0;
    font-weight: normal;
    cursor: pointer;
}

.qmj-correct-radio input[type="radio"]:checked + label,
.qmj-correct-radio label:has(input[type="radio"]:checked) {
    background: #d4edda;
    color: #155724;
}

.qmj-tf-options {
    display: flex;
    gap: 20px;
    justify-content: center;
}

.qmj-tf-option {
    cursor: pointer;
}

.qmj-tf-option input[type="radio"] {
    display: none;
}

.qmj-tf-label {
    display: block;
    padding: 15px 30px;
    border: 2px solid #ddd;
    border-radius: 8px;
    text-align: center;
    font-weight: 600;
    transition: all 0.3s ease;
}

.qmj-tf-true {
    background: #f8f9fa;
}

.qmj-tf-false {
    background: #f8f9fa;
}

.qmj-tf-option input[type="radio"]:checked + .qmj-tf-true {
    background: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.qmj-tf-option input[type="radio"]:checked + .qmj-tf-false {
    background: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
}

.qmj-matching-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.qmj-matching-column h4 {
    margin-bottom: 15px;
    text-align: center;
    padding: 10px;
    background: #e9ecef;
    border-radius: 4px;
}

.qmj-matching-item {
    margin-bottom: 15px;
}

.qmj-matching-item label {
    font-size: 12px;
    color: #646970;
    margin-bottom: 5px;
}

.qmj-media-upload {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
}

.qmj-image-preview {
    max-width: 300px;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    background: white;
}

.qmj-image-preview img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
}

.qmj-form-actions {
    text-align: center;
    padding: 30px 20px;
    background: #f9f9f9;
    border-radius: 8px;
    margin-top: 30px;
}

.qmj-form-actions .button {
    margin: 0 10px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .qmj-question-types {
        grid-template-columns: 1fr;
    }
    
    .qmj-option-row {
        flex-direction: column;
        gap: 10px;
    }
    
    .qmj-correct-radio {
        align-self: flex-start;
    }
    
    .qmj-matching-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .qmj-category-selector {
        flex-direction: column;
        align-items: stretch;
    }
    
    .qmj-media-upload {
        flex-direction: column;
        align-items: stretch;
    }
    
    .qmj-tf-options {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const questionTypeInputs = document.querySelectorAll('input[name="question_type"]');
    const optionsContainers = document.querySelectorAll('.qmj-options-container');
    
    // Initialize form
    showOptionsForType(document.querySelector('input[name="question_type"]:checked').value);
    
    // Handle question type changes
    questionTypeInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.checked) {
                showOptionsForType(this.value);
            }
        });
    });
    
    function showOptionsForType(type) {
        // Hide all options containers
        optionsContainers.forEach(container => {
            container.style.display = 'none';
        });
        
        // Show relevant container
        const targetContainer = document.getElementById(type.replace('_', '-') + '-options');
        if (targetContainer) {
            targetContainer.style.display = 'block';
        }
        
        // Update required fields
        updateRequiredFields(type);
    }
    
    function updateRequiredFields(type) {
        // Remove all required attributes first
        document.querySelectorAll('.qmj-options-container input, .qmj-options-container textarea').forEach(input => {
            input.removeAttribute('required');
        });
        
        // Add required attributes based on type
        switch (type) {
            case 'multiple_choice':
                document.getElementById('option_a').setAttribute('required', '');
                document.getElementById('option_b').setAttribute('required', '');
                break;
            case 'fill_blank':
                document.getElementById('correct_answer_text').setAttribute('required', '');
                break;
        }
    }
    
    // Media upload functionality
    const uploadButton = document.querySelector('.qmj-upload-button');
    const removeButton = document.querySelector('.qmj-remove-image');
    const imageInput = document.getElementById('image_url');
    const imagePreview = document.querySelector('.qmj-image-preview');
    
    uploadButton.addEventListener('click', function(e) {
        e.preventDefault();
        
        const mediaUploader = wp.media({
            title: 'Select Question Image',
            button: {
                text: 'Use this image'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        mediaUploader.on('select', function() {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            imageInput.value = attachment.url;
            updateImagePreview(attachment.url);
        });
        
        mediaUploader.open();
    });
    
    removeButton.addEventListener('click', function() {
        imageInput.value = '';
        updateImagePreview('');
    });
    
    imageInput.addEventListener('input', function() {
        updateImagePreview(this.value);
    });
    
    function updateImagePreview(url) {
        if (url) {
            imagePreview.querySelector('img').src = url;
            imagePreview.style.display = 'block';
            removeButton.style.display = 'inline-block';
        } else {
            imagePreview.style.display = 'none';
            removeButton.style.display = 'none';
        }
    }
    
    // Form validation
    document.getElementById('qmj-question-form').addEventListener('submit', function(e) {
        const questionType = document.querySelector('input[name="question_type"]:checked').value;
        
        // Validate based on question type
        if (questionType === 'multiple_choice') {
            const correctAnswer = document.querySelector('input[name="correct_answer"]:checked');
            if (!correctAnswer) {
                e.preventDefault();
                alert('Please select the correct answer.');
                return;
            }
        }
        
        if (questionType === 'true_false') {
            const correctAnswer = document.querySelector('input[name="correct_answer"]:checked');
            if (!correctAnswer) {
                e.preventDefault();
                alert('Please select True or False as the correct answer.');
                return;
            }
        }
        
        if (questionType === 'fill_blank') {
            const correctAnswerText = document.getElementById('correct_answer_text').value.trim();
            if (!correctAnswerText) {
                e.preventDefault();
                alert('Please enter the correct answer.');
                return;
            }
        }
        
        if (questionType === 'matching') {
            const leftItems = document.querySelectorAll('input[name="match_left[]"]');
            const rightItems = document.querySelectorAll('input[name="match_right[]"]');
            
            let hasLeftItems = false;
            let hasRightItems = false;
            
            leftItems.forEach(item => {
                if (item.value.trim()) hasLeftItems = true;
            });
            
            rightItems.forEach(item => {
                if (item.value.trim()) hasRightItems = true;
            });
            
            if (!hasLeftItems || !hasRightItems) {
                e.preventDefault();
                alert('Please fill in at least one item in both left and right columns.');
                return;
            }
        }
    });
    
    // Auto-save functionality (optional)
    let autoSaveTimeout;
    const formInputs = document.querySelectorAll('#qmj-question-form input, #qmj-question-form textarea, #qmj-question-form select');
    
    formInputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(autoSaveFormData, 2000);
        });
    });
    
    function autoSaveFormData() {
        const formData = new FormData(document.getElementById('qmj-question-form'));
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        localStorage.setItem('qmj_question_draft', JSON.stringify(data));
        
        // Show save indicator
        const indicator = document.createElement('div');
        indicator.textContent = 'Draft saved';
        indicator.style.cssText = 'position: fixed; top: 32px; right: 20px; background: #28a745; color: white; padding: 8px 16px; border-radius: 4px; z-index: 9999;';
        document.body.appendChild(indicator);
        
        setTimeout(() => {
            indicator.remove();
        }, 2000);
    }
    
    // Load draft data if available
    const draftData = localStorage.getItem('qmj_question_draft');
    if (draftData && !<?php echo $is_edit ? 'true' : 'false'; ?>) {
        const data = JSON.parse(draftData);
        
        Object.keys(data).forEach(key => {
            const input = document.querySelector(`[name="${key}"]`);
            if (input) {
                if (input.type === 'radio' || input.type === 'checkbox') {
                    if (input.value === data[key]) {
                        input.checked = true;
                    }
                } else {
                    input.value = data[key];
                }
            }
        });
        
        // Trigger change events to update UI
        document.querySelector('input[name="question_type"]:checked')?.dispatchEvent(new Event('change'));
    }
    
    // Clear draft on successful submit
    document.getElementById('qmj-question-form').addEventListener('submit', function() {
        localStorage.removeItem('qmj_question_draft');
    });
});
</script>