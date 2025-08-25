<?php
/**
 * JSON Import page template
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

global $wpdb;
$categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}qmj_categories ORDER BY name", ARRAY_A);
?>

<div class="wrap qmj-import-page">
    <h1 class="wp-heading-inline">
        <?php _e('Import & Export', 'quiz-master-json'); ?>
    </h1>

    <hr class="wp-header-end">

    <!-- Navigation Tabs -->
    <nav class="nav-tab-wrapper qmj-nav-tabs">
        <a href="<?php echo admin_url('admin.php?page=quiz-master-json-import'); ?>" 
           class="nav-tab nav-tab-active">
            <?php _e('Import JSON', 'quiz-master-json'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=quiz-master-json-import&action=export'); ?>" 
           class="nav-tab">
            <?php _e('Export Data', 'quiz-master-json'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=quiz-master-json-import&action=history'); ?>" 
           class="nav-tab">
            <?php _e('Import History', 'quiz-master-json'); ?>
        </a>
    </nav>

    <div class="qmj-import-container">
        <!-- JSON Format Guide -->
        <div class="qmj-format-guide">
            <h2><?php _e('JSON Import', 'quiz-master-json'); ?></h2>
            <p><?php _e('Upload a JSON file containing questions to import them into your question bank. The file must follow the correct format structure.', 'quiz-master-json'); ?></p>
            
            <details class="qmj-format-details">
                <summary><strong><?php _e('View JSON Format Requirements', 'quiz-master-json'); ?></strong></summary>
                
                <div class="qmj-format-content">
                    <h3><?php _e('Required JSON Structure:', 'quiz-master-json'); ?></h3>
                    <pre class="qmj-code-block"><code>{
  "questions": [
    {
      "category": "Category Name",
      "question": "What is the correct answer?",
      "options": {
        "A": "First option",
        "B": "Second option", 
        "C": "Third option",
        "D": "Fourth option"
      },
      "correct_answer": "A",
      "explanation": "Optional explanation text",
      "difficulty": "medium",
      "tags": "tag1, tag2, tag3",
      "image_url": "https://example.com/image.jpg",
      "reference": "Source or reference"
    }
  ]
}</code></pre>

                    <h3><?php _e('Field Descriptions:', 'quiz-master-json'); ?></h3>
                    <table class="qmj-fields-table">
                        <thead>
                            <tr>
                                <th><?php _e('Field', 'quiz-master-json'); ?></th>
                                <th><?php _e('Required', 'quiz-master-json'); ?></th>
                                <th><?php _e('Description', 'quiz-master-json'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>category</code></td>
                                <td><?php _e('Optional', 'quiz-master-json'); ?></td>
                                <td><?php _e('Category name. Will be created if it doesn\'t exist.', 'quiz-master-json'); ?></td>
                            </tr>
                            <tr>
                                <td><code>question</code></td>
                                <td><strong><?php _e('Required', 'quiz-master-json'); ?></strong></td>
                                <td><?php _e('The question text. HTML is allowed.', 'quiz-master-json'); ?></td>
                            </tr>
                            <tr>
                                <td><code>options</code></td>
                                <td><strong><?php _e('Required', 'quiz-master-json'); ?></strong></td>
                                <td><?php _e('Object with answer options. Keys can be A, B, C, D or custom.', 'quiz-master-json'); ?></td>
                            </tr>
                            <tr>
                                <td><code>correct_answer</code></td>
                                <td><strong><?php _e('Required', 'quiz-master-json'); ?></strong></td>
                                <td><?php _e('Key of the correct option (e.g., "A", "B", "C", "D").', 'quiz-master-json'); ?></td>
                            </tr>
                            <tr>
                                <td><code>explanation</code></td>
                                <td><?php _e('Optional', 'quiz-master-json'); ?></td>
                                <td><?php _e('Explanation shown after answering.', 'quiz-master-json'); ?></td>
                            </tr>
                            <tr>
                                <td><code>difficulty</code></td>
                                <td><?php _e('Optional', 'quiz-master-json'); ?></td>
                                <td><?php _e('Difficulty level: "easy", "medium", or "hard".', 'quiz-master-json'); ?></td>
                            </tr>
                            <tr>
                                <td><code>tags</code></td>
                                <td><?php _e('Optional', 'quiz-master-json'); ?></td>
                                <td><?php _e('Comma-separated tags for organizing questions.', 'quiz-master-json'); ?></td>
                            </tr>
                            <tr>
                                <td><code>image_url</code></td>
                                <td><?php _e('Optional', 'quiz-master-json'); ?></td>
                                <td><?php _e('URL of an image to display with the question.', 'quiz-master-json'); ?></td>
                            </tr>
                            <tr>
                                <td><code>reference</code></td>
                                <td><?php _e('Optional', 'quiz-master-json'); ?></td>
                                <td><?php _e('Source or reference information.', 'quiz-master-json'); ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <h3><?php _e('Alternative Question Types:', 'quiz-master-json'); ?></h3>
                    
                    <h4><?php _e('True/False Questions:', 'quiz-master-json'); ?></h4>
                    <pre class="qmj-code-block"><code>{
  "question": "The sky is blue.",
  "options": {
    "A": "True",
    "B": "False"
  },
  "correct_answer": "A"
}</code></pre>

                    <h4><?php _e('Fill in the Blank:', 'quiz-master-json'); ?></h4>
                    <pre class="qmj-code-block"><code>{
  "question": "The capital of France is ___.",
  "options": {
    "answer": "Paris"
  },
  "correct_answer": "answer"
}</code></pre>

                    <h4><?php _e('Matching Pairs:', 'quiz-master-json'); ?></h4>
                    <pre class="qmj-code-block"><code>{
  "question": "Match the countries with their capitals:",
  "options": {
    "left": ["France", "Germany", "Italy"],
    "right": ["Paris", "Berlin", "Rome"]
  },
  "correct_answer": "matching"
}</code></pre>
                </div>
            </details>
        </div>

        <!-- Import Form -->
        <div class="qmj-import-form-section">
            <h3><?php _e('Upload JSON File', 'quiz-master-json'); ?></h3>
            
            <form method="post" enctype="multipart/form-data" class="qmj-import-form" id="qmj-import-form">
                <?php wp_nonce_field('qmj_import_json', 'qmj_import_nonce'); ?>
                
                <!-- File Upload -->
                <div class="qmj-upload-area" id="qmj-upload-area">
                    <div class="qmj-upload-content">
                        <span class="dashicons dashicons-upload"></span>
                        <h4><?php _e('Drag & Drop JSON File Here', 'quiz-master-json'); ?></h4>
                        <p><?php _e('or click to browse files', 'quiz-master-json'); ?></p>
                        <input type="file" name="json_file" id="json_file" accept=".json,.txt" required>
                    </div>
                    <div class="qmj-file-info" id="qmj-file-info" style="display: none;">
                        <span class="dashicons dashicons-media-document"></span>
                        <div class="qmj-file-details">
                            <div class="qmj-file-name"></div>
                            <div class="qmj-file-size"></div>
                        </div>
                        <button type="button" class="qmj-remove-file" id="qmj-remove-file">
                            <span class="dashicons dashicons-no"></span>
                        </button>
                    </div>
                </div>

                <!-- Import Options -->
                <div class="qmj-import-options">
                    <h4><?php _e('Import Options', 'quiz-master-json'); ?></h4>
                    
                    <div class="qmj-option-group">
                        <label class="qmj-checkbox-label">
                            <input type="checkbox" name="create_categories" value="1" checked>
                            <?php _e('Automatically create categories if they don\'t exist', 'quiz-master-json'); ?>
                        </label>
                        <p class="description">
                            <?php _e('When enabled, new categories will be created for any category names that don\'t already exist.', 'quiz-master-json'); ?>
                        </p>
                    </div>

                    <div class="qmj-option-group">
                        <label class="qmj-checkbox-label">
                            <input type="checkbox" name="skip_duplicates" value="1" checked>
                            <?php _e('Skip duplicate questions', 'quiz-master-json'); ?>
                        </label>
                        <p class="description">
                            <?php _e('Skip questions that already exist (based on question text and category).', 'quiz-master-json'); ?>
                        </p>
                    </div>

                    <div class="qmj-option-group">
                        <label class="qmj-checkbox-label">
                            <input type="checkbox" name="update_existing" value="1">
                            <?php _e('Update existing questions', 'quiz-master-json'); ?>
                        </label>
                        <p class="description">
                            <?php _e('Update existing questions with new data instead of skipping them.', 'quiz-master-json'); ?>
                        </p>
                    </div>

                    <?php if (!empty($categories)): ?>
                    <div class="qmj-option-group">
                        <label for="default_category"><?php _e('Default category for questions without category:', 'quiz-master-json'); ?></label>
                        <select name="default_category" id="default_category">
                            <option value=""><?php _e('Create new category', 'quiz-master-json'); ?></option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo esc_attr($category['id']); ?>">
                                    <?php echo esc_html($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Import Button -->
                <div class="qmj-import-actions">
                    <button type="submit" class="button button-primary button-large" id="qmj-import-button">
                        <span class="dashicons dashicons-upload"></span>
                        <?php _e('Import Questions', 'quiz-master-json'); ?>
                    </button>
                    <div class="qmj-import-progress" id="qmj-import-progress" style="display: none;">
                        <div class="qmj-progress-bar">
                            <div class="qmj-progress-fill"></div>
                        </div>
                        <p><?php _e('Importing questions...', 'quiz-master-json'); ?></p>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sample Files -->
        <div class="qmj-sample-files">
            <h3><?php _e('Sample Files', 'quiz-master-json'); ?></h3>
            <p><?php _e('Download sample JSON files to understand the format:', 'quiz-master-json'); ?></p>
            
            <div class="qmj-samples-grid">
                <div class="qmj-sample-card">
                    <h4><?php _e('Basic MCQ Sample', 'quiz-master-json'); ?></h4>
                    <p><?php _e('Simple multiple choice questions with 4 options each.', 'quiz-master-json'); ?></p>
                    <button type="button" class="button" onclick="downloadSample('basic')">
                        <?php _e('Download Sample', 'quiz-master-json'); ?>
                    </button>
                </div>

                <div class="qmj-sample-card">
                    <h4><?php _e('Advanced Sample', 'quiz-master-json'); ?></h4>
                    <p><?php _e('Questions with all optional fields including images and explanations.', 'quiz-master-json'); ?></p>
                    <button type="button" class="button" onclick="downloadSample('advanced')">
                        <?php _e('Download Sample', 'quiz-master-json'); ?>
                    </button>
                </div>

                <div class="qmj-sample-card">
                    <h4><?php _e('Mixed Types Sample', 'quiz-master-json'); ?></h4>
                    <p><?php _e('Different question types: MCQ, True/False, Fill blanks.', 'quiz-master-json'); ?></p>
                    <button type="button" class="button" onclick="downloadSample('mixed')">
                        <?php _e('Download Sample', 'quiz-master-json'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Tips -->
        <div class="qmj-tips-section">
            <h3><?php _e('Import Tips', 'quiz-master-json'); ?></h3>
            <div class="qmj-tips-grid">
                <div class="qmj-tip">
                    <span class="dashicons dashicons-lightbulb"></span>
                    <h4><?php _e('File Size Limit', 'quiz-master-json'); ?></h4>
                    <p><?php _e('Maximum file size is 10MB. For larger imports, split into multiple files.', 'quiz-master-json'); ?></p>
                </div>

                <div class="qmj-tip">
                    <span class="dashicons dashicons-shield-alt"></span>
                    <h4><?php _e('Backup First', 'quiz-master-json'); ?></h4>
                    <p><?php _e('Always backup your existing questions before importing new ones.', 'quiz-master-json'); ?></p>
                </div>

                <div class="qmj-tip">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <h4><?php _e('Validate JSON', 'quiz-master-json'); ?></h4>
                    <p><?php _e('Use a JSON validator to check your file format before importing.', 'quiz-master-json'); ?></p>
                </div>

                <div class="qmj-tip">
                    <span class="dashicons dashicons-images-alt2"></span>
                    <h4><?php _e('Image URLs', 'quiz-master-json'); ?></h4>
                    <p><?php _e('Use publicly accessible image URLs. Upload images to your media library first.', 'quiz-master-json'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Import page styles */
.qmj-import-page {
    background: white;
    margin: 0 -20px;
    padding: 20px;
}

.qmj-nav-tabs {
    margin-bottom: 20px;
}

.qmj-import-container {
    max-width: 1200px;
    margin: 0 auto;
}

.qmj-format-guide {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
}

.qmj-format-details {
    margin-top: 15px;
}

.qmj-format-details summary {
    cursor: pointer;
    padding: 10px;
    background: #e9ecef;
    border-radius: 4px;
    margin-bottom: 10px;
}

.qmj-format-content {
    padding: 20px;
    background: white;
    border-radius: 6px;
    border: 1px solid #ddd;
}

.qmj-code-block {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 15px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    overflow-x: auto;
    white-space: pre;
}

.qmj-fields-table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
}

.qmj-fields-table th,
.qmj-fields-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.qmj-fields-table th {
    background: #f1f3f4;
    font-weight: 600;
}

.qmj-fields-table code {
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
}

.qmj-import-form-section {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 30px;
    margin-bottom: 30px;
}

.qmj-upload-area {
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    margin-bottom: 20px;
    position: relative;
}

.qmj-upload-area:hover,
.qmj-upload-area.dragover {
    border-color: #0073aa;
    background: #f0f8ff;
}

.qmj-upload-content .dashicons {
    font-size: 48px;
    color: #666;
    margin-bottom: 15px;
}

.qmj-upload-content h4 {
    margin: 10px 0;
    color: #1d2327;
}

.qmj-upload-content p {
    color: #646970;
    margin-bottom: 0;
}

.qmj-upload-area input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.qmj-file-info {
    display: flex;
    align-items: center;
    gap: 15px;
    background: #e8f5e8;
    border: 1px solid #4caf50;
    border-radius: 6px;
    padding: 15px;
}

.qmj-file-info .dashicons {
    font-size: 24px;
    color: #4caf50;
}

.qmj-file-details {
    flex: 1;
}

.qmj-file-name {
    font-weight: 600;
    color: #1d2327;
}

.qmj-file-size {
    font-size: 12px;
    color: #646970;
}

.qmj-remove-file {
    background: none;
    border: none;
    color: #d63638;
    cursor: pointer;
    padding: 5px;
}

.qmj-import-options {
    border-top: 1px solid #eee;
    padding-top: 20px;
}

.qmj-import-options h4 {
    margin-bottom: 20px;
    color: #1d2327;
}

.qmj-option-group {
    margin-bottom: 20px;
}

.qmj-checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    cursor: pointer;
}

.qmj-checkbox-label input[type="checkbox"] {
    margin: 0;
}

.qmj-option-group .description {
    margin: 8px 0 0 26px;
    font-size: 13px;
    color: #646970;
}

.qmj-option-group select {
    width: 100%;
    max-width: 300px;
}

.qmj-import-actions {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.qmj-import-progress {
    margin-top: 20px;
}

.qmj-progress-bar {
    width: 100%;
    height: 8px;
    background: #f0f0f1;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 10px;
}

.qmj-progress-fill {
    height: 100%;
    background: #0073aa;
    width: 0%;
    transition: width 0.3s ease;
    animation: progress-animation 1s ease-in-out infinite;
}

@keyframes progress-animation {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.qmj-sample-files {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 30px;
    margin-bottom: 30px;
}

.qmj-samples-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.qmj-sample-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 20px;
    text-align: center;
}

.qmj-sample-card h4 {
    margin-top: 0;
    color: #1d2327;
}

.qmj-sample-card p {
    color: #646970;
    font-size: 13px;
    margin-bottom: 15px;
}

.qmj-tips-section {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 30px;
}

.qmj-tips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.qmj-tip {
    background: white;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 20px;
    text-align: center;
}

.qmj-tip .dashicons {
    font-size: 32px;
    color: #0073aa;
    margin-bottom: 10px;
}

.qmj-tip h4 {
    margin: 10px 0;
    color: #1d2327;
}

.qmj-tip p {
    color: #646970;
    font-size: 13px;
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .qmj-samples-grid,
    .qmj-tips-grid {
        grid-template-columns: 1fr;
    }
    
    .qmj-upload-area {
        padding: 20px;
    }
    
    .qmj-file-info {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('qmj-upload-area');
    const fileInput = document.getElementById('json_file');
    const fileInfo = document.getElementById('qmj-file-info');
    const uploadContent = document.querySelector('.qmj-upload-content');
    const removeButton = document.getElementById('qmj-remove-file');
    const importForm = document.getElementById('qmj-import-form');
    const importButton = document.getElementById('qmj-import-button');
    const importProgress = document.getElementById('qmj-import-progress');

    // Drag and drop functionality
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            showFileInfo(files[0]);
        }
    });

    // File input change
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            showFileInfo(this.files[0]);
        }
    });

    // Remove file
    removeButton.addEventListener('click', function() {
        fileInput.value = '';
        hideFileInfo();
    });

    // Show file information
    function showFileInfo(file) {
        const fileName = file.name;
        const fileSize = formatFileSize(file.size);
        
        document.querySelector('.qmj-file-name').textContent = fileName;
        document.querySelector('.qmj-file-size').textContent = fileSize;
        
        uploadContent.style.display = 'none';
        fileInfo.style.display = 'flex';
        
        // Validate file type
        if (!fileName.toLowerCase().endsWith('.json') && !fileName.toLowerCase().endsWith('.txt')) {
            showValidationError('Please select a JSON file (.json or .txt extension).');
        } else {
            clearValidationError();
        }
    }

    // Hide file information
    function hideFileInfo() {
        uploadContent.style.display = 'block';
        fileInfo.style.display = 'none';
        clearValidationError();
    }

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Form submission
    importForm.addEventListener('submit', function(e) {
        if (fileInput.files.length === 0) {
            e.preventDefault();
            showValidationError('Please select a JSON file to import.');
            return;
        }

        // Show progress
        importButton.style.display = 'none';
        importProgress.style.display = 'block';
        
        // Simulate progress (since we can't track actual upload progress)
        let progress = 0;
        const progressBar = document.querySelector('.qmj-progress-fill');
        const progressInterval = setInterval(function() {
            progress += Math.random() * 10;
            if (progress > 90) progress = 90;
            progressBar.style.width = progress + '%';
        }, 200);

        // Clean up on form submission complete (handled by PHP)
        setTimeout(function() {
            clearInterval(progressInterval);
        }, 5000);
    });

    // Validation error handling
    function showValidationError(message) {
        clearValidationError();
        const errorDiv = document.createElement('div');
        errorDiv.className = 'notice notice-error inline';
        errorDiv.innerHTML = '<p>' + message + '</p>';
        uploadArea.parentNode.insertBefore(errorDiv, uploadArea.nextSibling);
    }

    function clearValidationError() {
        const existingError = uploadArea.parentNode.querySelector('.notice-error');
        if (existingError) {
            existingError.remove();
        }
    }

    // Checkbox dependencies
    const skipDuplicates = document.querySelector('input[name="skip_duplicates"]');
    const updateExisting = document.querySelector('input[name="update_existing"]');

    if (skipDuplicates && updateExisting) {
        skipDuplicates.addEventListener('change', function() {
            if (this.checked) {
                updateExisting.checked = false;
            }
        });

        updateExisting.addEventListener('change', function() {
            if (this.checked) {
                skipDuplicates.checked = false;
            }
        });
    }
});

// Sample file downloads
function downloadSample(type) {
    let sampleData = {};
    
    switch (type) {
        case 'basic':
            sampleData = {
                "export_info": {
                    "description": "Basic MCQ sample for Quiz Master JSON",
                    "question_count": 3
                },
                "questions": [
                    {
                        "category": "General Knowledge",
                        "question": "What is the capital of France?",
                        "options": {
                            "A": "London",
                            "B": "Berlin", 
                            "C": "Paris",
                            "D": "Madrid"
                        },
                        "correct_answer": "C"
                    },
                    {
                        "category": "Science",
                        "question": "What is the chemical symbol for water?",
                        "options": {
                            "A": "H2O",
                            "B": "CO2",
                            "C": "O2",
                            "D": "NaCl"
                        },
                        "correct_answer": "A"
                    },
                    {
                        "category": "Mathematics", 
                        "question": "What is 2 + 2?",
                        "options": {
                            "A": "3",
                            "B": "4",
                            "C": "5", 
                            "D": "6"
                        },
                        "correct_answer": "B"
                    }
                ]
            };
            break;
            
        case 'advanced':
            sampleData = {
                "export_info": {
                    "description": "Advanced sample with all optional fields",
                    "question_count": 2
                },
                "questions": [
                    {
                        "category": "Programming",
                        "question": "Which of the following is <strong>NOT</strong> a programming language?",
                        "options": {
                            "A": "JavaScript",
                            "B": "Python",
                            "C": "HTML",
                            "D": "Java"
                        },
                        "correct_answer": "C",
                        "explanation": "HTML is a markup language, not a programming language. It's used for structuring web content.",
                        "difficulty": "medium",
                        "tags": "programming, web development, languages",
                        "image_url": "https://example.com/programming-image.jpg",
                        "reference": "Web Development Fundamentals"
                    },
                    {
                        "category": "Geography",
                        "question": "Which continent has the most countries?",
                        "options": {
                            "A": "Asia",
                            "B": "Africa", 
                            "C": "Europe",
                            "D": "South America"
                        },
                        "correct_answer": "B",
                        "explanation": "Africa has 54 recognized sovereign states, making it the continent with the most countries.",
                        "difficulty": "hard",
                        "tags": "geography, continents, countries",
                        "reference": "World Geography Atlas 2024"
                    }
                ]
            };
            break;
            
        case 'mixed':
            sampleData = {
                "export_info": {
                    "description": "Mixed question types sample",
                    "question_count": 4
                },
                "questions": [
                    {
                        "category": "Science",
                        "question": "Is the sun a star?",
                        "options": {
                            "A": "True",
                            "B": "False"
                        },
                        "correct_answer": "A",
                        "explanation": "The sun is indeed a star - specifically a G-type main-sequence star."
                    },
                    {
                        "category": "Literature",
                        "question": "Who wrote the novel '1984'?",
                        "options": {
                            "answer": "George Orwell"
                        },
                        "correct_answer": "answer",
                        "explanation": "George Orwell wrote the dystopian novel '1984', published in 1949."
                    },
                    {
                        "category": "History",
                        "question": "Match the historical events with their years:",
                        "options": {
                            "left": ["World War II ended", "Moon landing", "Berlin Wall fell"],
                            "right": ["1945", "1969", "1989"]
                        },
                        "correct_answer": "matching"
                    },
                    {
                        "category": "Mathematics",
                        "question": "What is the square root of 64?",
                        "options": {
                            "A": "6",
                            "B": "7",
                            "C": "8",
                            "D": "9"
                        },
                        "correct_answer": "C"
                    }
                ]
            };
            break;
    }
    
    const jsonString = JSON.stringify(sampleData, null, 2);
    const blob = new Blob([jsonString], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = `quiz-sample-${type}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>