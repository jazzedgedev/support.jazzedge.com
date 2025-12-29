<?php
/**
 * Test Permalink Generation
 * 
 * This script tests how lesson permalinks are generated
 * Access via: https://jazzedge.academy/wp-content/plugins/academy-ai-assistant/test-permalink.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Security: Only allow admins
if (!current_user_can('manage_options')) {
    die('Access denied');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Lesson Permalink Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .error { color: red; }
        .success { color: green; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <h1>Lesson Permalink Test</h1>
    
    <?php
    global $wpdb;
    
    // Get a few sample lessons
    if (class_exists('ALM_Database')) {
        $alm_db = new ALM_Database();
        $lessons_table = $alm_db->get_table_name('lessons');
        
        $lessons = $wpdb->get_results(
            "SELECT ID, lesson_title, post_id, slug FROM {$lessons_table} WHERE post_id > 0 LIMIT 10",
            ARRAY_A
        );
        
        echo '<h2>Sample Lessons with Permalinks</h2>';
        echo '<table>';
        echo '<tr><th>Lesson ID</th><th>Title</th><th>Post ID</th><th>Slug</th><th>get_permalink() Result</th><th>Post Status</th><th>Post Type</th></tr>';
        
        foreach ($lessons as $lesson) {
            $post_id = absint($lesson['post_id']);
            $post = get_post($post_id);
            
            echo '<tr>';
            echo '<td>' . esc_html($lesson['ID']) . '</td>';
            echo '<td>' . esc_html($lesson['lesson_title']) . '</td>';
            echo '<td>' . esc_html($post_id) . '</td>';
            echo '<td>' . esc_html($lesson['slug']) . '</td>';
            
            if ($post) {
                $permalink = get_permalink($post_id);
                echo '<td class="' . ($permalink ? 'success' : 'error') . '">';
                echo $permalink ? esc_html($permalink) : 'NULL';
                echo '</td>';
                echo '<td>' . esc_html($post->post_status) . '</td>';
                echo '<td>' . esc_html($post->post_type) . '</td>';
            } else {
                echo '<td class="error">Post not found</td>';
                echo '<td>-</td>';
                echo '<td>-</td>';
            }
            
            echo '</tr>';
        }
        
        echo '</table>';
        
        // Test a specific lesson if provided
        if (isset($_GET['lesson_id'])) {
            $test_lesson_id = absint($_GET['lesson_id']);
            echo '<h2>Detailed Test for Lesson ID: ' . $test_lesson_id . '</h2>';
            
            $test_lesson = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$lessons_table} WHERE ID = %d",
                $test_lesson_id
            ));
            
            if ($test_lesson) {
                echo '<pre>';
                echo "Lesson ID: {$test_lesson->ID}\n";
                echo "Title: {$test_lesson->lesson_title}\n";
                echo "Post ID: {$test_lesson->post_id}\n";
                echo "Slug: {$test_lesson->slug}\n";
                
                if ($test_lesson->post_id) {
                    $post = get_post($test_lesson->post_id);
                    if ($post) {
                        echo "\nPost Details:\n";
                        echo "  Post ID: {$post->ID}\n";
                        echo "  Post Type: {$post->post_type}\n";
                        echo "  Post Status: {$post->post_status}\n";
                        echo "  Post Name: {$post->post_name}\n";
                        
                        $permalink = get_permalink($post->ID);
                        echo "\nPermalink: " . ($permalink ? $permalink : 'NULL') . "\n";
                        
                        // Check rewrite rules
                        $post_type_obj = get_post_type_object($post->post_type);
                        if ($post_type_obj) {
                            echo "\nPost Type Rewrite Rules:\n";
                            if (isset($post_type_obj->rewrite)) {
                                print_r($post_type_obj->rewrite);
                            } else {
                                echo "  No rewrite rules defined\n";
                            }
                        }
                    } else {
                        echo "\nPost not found for post_id: {$test_lesson->post_id}\n";
                    }
                } else {
                    echo "\nNo post_id set for this lesson\n";
                }
                
                echo '</pre>';
            } else {
                echo '<p class="error">Lesson not found</p>';
            }
        }
        
    } else {
        echo '<p class="error">ALM_Database class not found</p>';
    }
    ?>
    
    <h2>Test Specific Lesson</h2>
    <form method="get">
        <label>Lesson ID: <input type="number" name="lesson_id" value="<?php echo isset($_GET['lesson_id']) ? esc_attr($_GET['lesson_id']) : ''; ?>"></label>
        <button type="submit">Test</button>
    </form>
</body>
</html>

