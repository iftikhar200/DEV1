<?php
/**
 * Provide a admin area view for the plugin dashboard
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

// Get statistics
$stats = array();
$stats['categories'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}qmj_categories");
$stats['questions'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}qmj_questions");
$stats['quizzes'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}qmj_quizzes");
$stats['results'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}qmj_results");

// Get recent activity
$recent_results = $wpdb->get_results(
    "SELECT r.*, q.post_id, p.post_title 
     FROM {$wpdb->prefix}qmj_results r
     JOIN {$wpdb->prefix}qmj_quizzes q ON r.quiz_id = q.id
     JOIN {$wpdb->posts} p ON q.post_id = p.ID
     ORDER BY r.completed_at DESC 
     LIMIT 5",
    ARRAY_A
);

// Get popular quizzes
$popular_quizzes = $wpdb->get_results(
    "SELECT q.post_id, p.post_title, COUNT(r.id) as attempt_count
     FROM {$wpdb->prefix}qmj_quizzes q
     JOIN {$wpdb->posts} p ON q.post_id = p.ID
     LEFT JOIN {$wpdb->prefix}qmj_results r ON q.id = r.quiz_id
     WHERE p.post_status = 'publish'
     GROUP BY q.id
     ORDER BY attempt_count DESC
     LIMIT 5",
    ARRAY_A
);

// Get category distribution
$category_stats = $wpdb->get_results(
    "SELECT c.name, c.question_count, COUNT(DISTINCT r.id) as total_attempts
     FROM {$wpdb->prefix}qmj_categories c
     LEFT JOIN {$wpdb->prefix}qmj_questions qs ON c.id = qs.category_id
     LEFT JOIN {$wpdb->prefix}qmj_results r ON JSON_CONTAINS(
         (SELECT categories FROM {$wpdb->prefix}qmj_quizzes WHERE id = r.quiz_id), 
         CAST(c.id AS JSON)
     )
     GROUP BY c.id
     ORDER BY c.question_count DESC",
    ARRAY_A
);

?>

<div class="wrap qmj-dashboard">
    <h1 class="wp-heading-inline"><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php if ($stats['categories'] == 0): ?>
    <div class="notice notice-info">
        <h3><?php _e('Welcome to Quiz Master JSON!', 'quiz-master-json'); ?></h3>
        <p><?php _e('Get started by adding your first questions and creating quizzes.', 'quiz-master-json'); ?></p>
        <p>
            <a href="<?php echo admin_url('admin.php?page=quiz-master-json-import'); ?>" class="button button-primary">
                <?php _e('Import JSON Questions', 'quiz-master-json'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=quiz-master-json-questions&action=add'); ?>" class="button button-secondary">
                <?php _e('Add Questions Manually', 'quiz-master-json'); ?>
            </a>
        </p>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="qmj-stats-grid">
        <div class="qmj-stat-card qmj-stat-categories">
            <div class="qmj-stat-icon">
                <span class="dashicons dashicons-category"></span>
            </div>
            <div class="qmj-stat-content">
                <h3><?php echo number_format($stats['categories']); ?></h3>
                <p><?php _e('Categories', 'quiz-master-json'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=quiz-master-json-categories'); ?>" class="qmj-stat-link">
                    <?php _e('Manage Categories', 'quiz-master-json'); ?>
                </a>
            </div>
        </div>

        <div class="qmj-stat-card qmj-stat-questions">
            <div class="qmj-stat-icon">
                <span class="dashicons dashicons-editor-help"></span>
            </div>
            <div class="qmj-stat-content">
                <h3><?php echo number_format($stats['questions']); ?></h3>
                <p><?php _e('Questions', 'quiz-master-json'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=quiz-master-json-questions'); ?>" class="qmj-stat-link">
                    <?php _e('Manage Questions', 'quiz-master-json'); ?>
                </a>
            </div>
        </div>

        <div class="qmj-stat-card qmj-stat-quizzes">
            <div class="qmj-stat-icon">
                <span class="dashicons dashicons-clipboard"></span>
            </div>
            <div class="qmj-stat-content">
                <h3><?php echo number_format($stats['quizzes']); ?></h3>
                <p><?php _e('Quizzes', 'quiz-master-json'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=quiz-master-json-quizzes'); ?>" class="qmj-stat-link">
                    <?php _e('Manage Quizzes', 'quiz-master-json'); ?>
                </a>
            </div>
        </div>

        <div class="qmj-stat-card qmj-stat-results">
            <div class="qmj-stat-icon">
                <span class="dashicons dashicons-chart-area"></span>
            </div>
            <div class="qmj-stat-content">
                <h3><?php echo number_format($stats['results']); ?></h3>
                <p><?php _e('Quiz Attempts', 'quiz-master-json'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=quiz-master-json-results'); ?>" class="qmj-stat-link">
                    <?php _e('View Results', 'quiz-master-json'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="qmj-dashboard-section">
        <h2><?php _e('Quick Actions', 'quiz-master-json'); ?></h2>
        <div class="qmj-quick-actions">
            <div class="qmj-action-card">
                <h3><?php _e('Create New Quiz', 'quiz-master-json'); ?></h3>
                <p><?php _e('Build a new quiz with our drag-and-drop quiz builder.', 'quiz-master-json'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=quiz-master-json-quiz-builder'); ?>" class="button button-primary">
                    <?php _e('Create Quiz', 'quiz-master-json'); ?>
                </a>
            </div>

            <div class="qmj-action-card">
                <h3><?php _e('Add Questions', 'quiz-master-json'); ?></h3>
                <p><?php _e('Add questions manually or import from JSON files.', 'quiz-master-json'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=quiz-master-json-questions&action=add'); ?>" class="button button-secondary">
                    <?php _e('Add Manually', 'quiz-master-json'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=quiz-master-json-import'); ?>" class="button button-secondary">
                    <?php _e('Import JSON', 'quiz-master-json'); ?>
                </a>
            </div>

            <div class="qmj-action-card">
                <h3><?php _e('Customize Appearance', 'quiz-master-json'); ?></h3>
                <p><?php _e('Customize colors, fonts, and quiz behavior with live preview.', 'quiz-master-json'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=quiz-master-json-settings'); ?>" class="button button-secondary">
                    <?php _e('Customize', 'quiz-master-json'); ?>
                </a>
            </div>

            <div class="qmj-action-card">
                <h3><?php _e('View Analytics', 'quiz-master-json'); ?></h3>
                <p><?php _e('Analyze quiz performance and user engagement.', 'quiz-master-json'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=quiz-master-json-results'); ?>" class="button button-secondary">
                    <?php _e('View Analytics', 'quiz-master-json'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Dashboard Layout -->
    <div class="qmj-dashboard-grid">
        <!-- Recent Activity -->
        <div class="qmj-dashboard-widget">
            <h3><?php _e('Recent Quiz Attempts', 'quiz-master-json'); ?></h3>
            <div class="qmj-widget-content">
                <?php if (!empty($recent_results)): ?>
                    <table class="qmj-recent-table">
                        <thead>
                            <tr>
                                <th><?php _e('Quiz', 'quiz-master-json'); ?></th>
                                <th><?php _e('User', 'quiz-master-json'); ?></th>
                                <th><?php _e('Score', 'quiz-master-json'); ?></th>
                                <th><?php _e('Date', 'quiz-master-json'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_results as $result): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=quiz-master-json-quiz-builder&id=' . $result['post_id']); ?>">
                                        <?php echo esc_html($result['post_title']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php 
                                    if ($result['user_name']) {
                                        echo esc_html($result['user_name']);
                                    } elseif ($result['user_email']) {
                                        echo esc_html($result['user_email']);
                                    } else {
                                        echo __('Anonymous', 'quiz-master-json');
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="qmj-score-badge qmj-score-<?php echo $result['pass_status']; ?>">
                                        <?php echo number_format($result['percentage'], 1); ?>%
                                    </span>
                                </td>
                                <td><?php echo date_i18n(get_option('date_format'), strtotime($result['completed_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="qmj-widget-footer">
                        <a href="<?php echo admin_url('admin.php?page=quiz-master-json-results'); ?>">
                            <?php _e('View All Results', 'quiz-master-json'); ?> &rarr;
                        </a>
                    </div>
                <?php else: ?>
                    <div class="qmj-empty-state">
                        <span class="dashicons dashicons-chart-area"></span>
                        <p><?php _e('No quiz attempts yet. Create your first quiz to start collecting results!', 'quiz-master-json'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Popular Quizzes -->
        <div class="qmj-dashboard-widget">
            <h3><?php _e('Popular Quizzes', 'quiz-master-json'); ?></h3>
            <div class="qmj-widget-content">
                <?php if (!empty($popular_quizzes)): ?>
                    <div class="qmj-popular-list">
                        <?php foreach ($popular_quizzes as $quiz): ?>
                        <div class="qmj-popular-item">
                            <div class="qmj-popular-info">
                                <h4>
                                    <a href="<?php echo admin_url('admin.php?page=quiz-master-json-quiz-builder&id=' . $quiz['post_id']); ?>">
                                        <?php echo esc_html($quiz['post_title']); ?>
                                    </a>
                                </h4>
                                <p><?php printf(__('%d attempts', 'quiz-master-json'), $quiz['attempt_count']); ?></p>
                            </div>
                            <div class="qmj-popular-actions">
                                <a href="<?php echo get_permalink($quiz['post_id']); ?>" target="_blank" class="button button-small">
                                    <?php _e('View', 'quiz-master-json'); ?>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="qmj-widget-footer">
                        <a href="<?php echo admin_url('admin.php?page=quiz-master-json-quizzes'); ?>">
                            <?php _e('Manage All Quizzes', 'quiz-master-json'); ?> &rarr;
                        </a>
                    </div>
                <?php else: ?>
                    <div class="qmj-empty-state">
                        <span class="dashicons dashicons-clipboard"></span>
                        <p><?php _e('No quizzes created yet.', 'quiz-master-json'); ?></p>
                        <a href="<?php echo admin_url('admin.php?page=quiz-master-json-quiz-builder'); ?>" class="button button-primary">
                            <?php _e('Create Your First Quiz', 'quiz-master-json'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Category Overview -->
        <div class="qmj-dashboard-widget qmj-widget-full">
            <h3><?php _e('Category Overview', 'quiz-master-json'); ?></h3>
            <div class="qmj-widget-content">
                <?php if (!empty($category_stats)): ?>
                    <div class="qmj-category-grid">
                        <?php foreach ($category_stats as $category): ?>
                        <div class="qmj-category-card">
                            <h4><?php echo esc_html($category['name']); ?></h4>
                            <div class="qmj-category-stats">
                                <div class="qmj-stat">
                                    <span class="qmj-stat-number"><?php echo number_format($category['question_count']); ?></span>
                                    <span class="qmj-stat-label"><?php _e('Questions', 'quiz-master-json'); ?></span>
                                </div>
                                <div class="qmj-stat">
                                    <span class="qmj-stat-number"><?php echo number_format($category['total_attempts']); ?></span>
                                    <span class="qmj-stat-label"><?php _e('Attempts', 'quiz-master-json'); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="qmj-widget-footer">
                        <a href="<?php echo admin_url('admin.php?page=quiz-master-json-categories'); ?>">
                            <?php _e('Manage Categories', 'quiz-master-json'); ?> &rarr;
                        </a>
                    </div>
                <?php else: ?>
                    <div class="qmj-empty-state">
                        <span class="dashicons dashicons-category"></span>
                        <p><?php _e('No categories created yet.', 'quiz-master-json'); ?></p>
                        <a href="<?php echo admin_url('admin.php?page=quiz-master-json-categories'); ?>" class="button button-primary">
                            <?php _e('Create Categories', 'quiz-master-json'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="qmj-dashboard-section">
        <h2><?php _e('System Status', 'quiz-master-json'); ?></h2>
        <div class="qmj-system-status">
            <div class="qmj-status-item">
                <span class="qmj-status-icon qmj-status-good">
                    <span class="dashicons dashicons-yes-alt"></span>
                </span>
                <div class="qmj-status-content">
                    <h4><?php _e('Database Connection', 'quiz-master-json'); ?></h4>
                    <p><?php _e('Working properly', 'quiz-master-json'); ?></p>
                </div>
            </div>

            <div class="qmj-status-item">
                <span class="qmj-status-icon qmj-status-good">
                    <span class="dashicons dashicons-yes-alt"></span>
                </span>
                <div class="qmj-status-content">
                    <h4><?php _e('File Permissions', 'quiz-master-json'); ?></h4>
                    <p><?php _e('Upload directory is writable', 'quiz-master-json'); ?></p>
                </div>
            </div>

            <div class="qmj-status-item">
                <span class="qmj-status-icon qmj-status-good">
                    <span class="dashicons dashicons-yes-alt"></span>
                </span>
                <div class="qmj-status-content">
                    <h4><?php _e('PHP Version', 'quiz-master-json'); ?></h4>
                    <p><?php echo PHP_VERSION; ?> <?php _e('(Compatible)', 'quiz-master-json'); ?></p>
                </div>
            </div>

            <div class="qmj-status-item">
                <span class="qmj-status-icon qmj-status-good">
                    <span class="dashicons dashicons-yes-alt"></span>
                </span>
                <div class="qmj-status-content">
                    <h4><?php _e('WordPress Version', 'quiz-master-json'); ?></h4>
                    <p><?php echo get_bloginfo('version'); ?> <?php _e('(Compatible)', 'quiz-master-json'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Help & Support -->
    <div class="qmj-dashboard-section">
        <h2><?php _e('Help & Support', 'quiz-master-json'); ?></h2>
        <div class="qmj-help-grid">
            <div class="qmj-help-card">
                <h3><?php _e('Getting Started', 'quiz-master-json'); ?></h3>
                <ul>
                    <li><a href="#" target="_blank"><?php _e('Quick Start Guide', 'quiz-master-json'); ?></a></li>
                    <li><a href="#" target="_blank"><?php _e('Creating Your First Quiz', 'quiz-master-json'); ?></a></li>
                    <li><a href="#" target="_blank"><?php _e('JSON Import Tutorial', 'quiz-master-json'); ?></a></li>
                </ul>
            </div>

            <div class="qmj-help-card">
                <h3><?php _e('Advanced Features', 'quiz-master-json'); ?></h3>
                <ul>
                    <li><a href="#" target="_blank"><?php _e('Customizing Quiz Appearance', 'quiz-master-json'); ?></a></li>
                    <li><a href="#" target="_blank"><?php _e('Setting Up Analytics', 'quiz-master-json'); ?></a></li>
                    <li><a href="#" target="_blank"><?php _e('Theme Integration', 'quiz-master-json'); ?></a></li>
                </ul>
            </div>

            <div class="qmj-help-card">
                <h3><?php _e('Support', 'quiz-master-json'); ?></h3>
                <ul>
                    <li><a href="#" target="_blank"><?php _e('Documentation', 'quiz-master-json'); ?></a></li>
                    <li><a href="#" target="_blank"><?php _e('Community Forum', 'quiz-master-json'); ?></a></li>
                    <li><a href="#" target="_blank"><?php _e('Contact Support', 'quiz-master-json'); ?></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard Styles */
.qmj-dashboard {
    background: #f1f1f1;
    margin: 0 -20px;
    padding: 20px;
}

.qmj-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.qmj-stat-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.qmj-stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.qmj-stat-categories .qmj-stat-icon { background: #e3f2fd; color: #1976d2; }
.qmj-stat-questions .qmj-stat-icon { background: #f3e5f5; color: #7b1fa2; }
.qmj-stat-quizzes .qmj-stat-icon { background: #e8f5e8; color: #388e3c; }
.qmj-stat-results .qmj-stat-icon { background: #fff3e0; color: #f57c00; }

.qmj-stat-content h3 {
    margin: 0;
    font-size: 28px;
    font-weight: 600;
    color: #1d2327;
}

.qmj-stat-content p {
    margin: 5px 0;
    color: #646970;
    font-size: 14px;
}

.qmj-stat-link {
    color: #0073aa;
    text-decoration: none;
    font-size: 12px;
}

.qmj-stat-link:hover {
    text-decoration: underline;
}

.qmj-dashboard-section {
    margin: 30px 0;
}

.qmj-dashboard-section h2 {
    margin-bottom: 20px;
    color: #1d2327;
}

.qmj-quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.qmj-action-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.qmj-action-card h3 {
    margin-top: 0;
    color: #1d2327;
}

.qmj-action-card p {
    color: #646970;
    margin-bottom: 15px;
}

.qmj-dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 20px 0;
}

.qmj-dashboard-widget {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.qmj-widget-full {
    grid-column: 1 / -1;
}

.qmj-dashboard-widget h3 {
    margin: 0;
    padding: 20px 20px 0;
    color: #1d2327;
}

.qmj-widget-content {
    padding: 20px;
}

.qmj-recent-table {
    width: 100%;
    border-collapse: collapse;
}

.qmj-recent-table th,
.qmj-recent-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #f0f0f1;
}

.qmj-recent-table th {
    font-weight: 600;
    color: #646970;
    font-size: 12px;
    text-transform: uppercase;
}

.qmj-score-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.qmj-score-pass {
    background: #d4edda;
    color: #155724;
}

.qmj-score-fail {
    background: #f8d7da;
    color: #721c24;
}

.qmj-empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #646970;
}

.qmj-empty-state .dashicons {
    font-size: 48px;
    opacity: 0.3;
    margin-bottom: 10px;
}

.qmj-widget-footer {
    padding: 15px 20px;
    border-top: 1px solid #f0f0f1;
    background: #f9f9f9;
    border-radius: 0 0 8px 8px;
}

.qmj-widget-footer a {
    color: #0073aa;
    text-decoration: none;
    font-size: 13px;
}

.qmj-category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.qmj-category-card {
    background: #f9f9f9;
    border-radius: 6px;
    padding: 15px;
    text-align: center;
}

.qmj-category-card h4 {
    margin: 0 0 10px;
    color: #1d2327;
}

.qmj-category-stats {
    display: flex;
    justify-content: space-around;
}

.qmj-stat {
    text-align: center;
}

.qmj-stat-number {
    display: block;
    font-size: 18px;
    font-weight: 600;
    color: #0073aa;
}

.qmj-stat-label {
    font-size: 11px;
    color: #646970;
    text-transform: uppercase;
}

.qmj-system-status {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.qmj-status-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: white;
    padding: 15px;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.qmj-status-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.qmj-status-good {
    background: #d4edda;
    color: #155724;
}

.qmj-status-content h4 {
    margin: 0;
    font-size: 14px;
    color: #1d2327;
}

.qmj-status-content p {
    margin: 2px 0 0;
    font-size: 12px;
    color: #646970;
}

.qmj-help-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.qmj-help-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.qmj-help-card h3 {
    margin-top: 0;
    color: #1d2327;
}

.qmj-help-card ul {
    margin: 0;
    padding-left: 20px;
}

.qmj-help-card li {
    margin-bottom: 8px;
}

.qmj-help-card a {
    color: #0073aa;
    text-decoration: none;
}

.qmj-help-card a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .qmj-dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .qmj-stats-grid {
        grid-template-columns: 1fr;
    }
    
    .qmj-quick-actions {
        grid-template-columns: 1fr;
    }
}
</style>