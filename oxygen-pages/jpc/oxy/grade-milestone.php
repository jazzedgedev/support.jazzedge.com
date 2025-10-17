<?php
global $wpdb, $user_id;

// Get the action from either POST or GET request
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if ($action === 'submit_milestone') {
    $error = null;
    $youtube_url = sanitize_text_field($_POST['youtube_url'] ?? '');
    $curriculum_id = intval($_POST['curriculum_id'] ?? 0);

    if (empty($youtube_url)) {
        $error = '<span class="error">YouTube URL was blank. Please resubmit.</span>';
    }

    if (empty($error)) {
        $data = array(
            'user_id' => $user_id,
            'curriculum_id' => $curriculum_id,
            'video_url' => $youtube_url,
            'submission_date' => current_time('mysql'),
        );
        $format = array('%d', '%d', '%s', '%s');
        $wpdb->insert($wpdb->prefix . 'jph_jpc_milestone_submissions', $data, $format);

        wp_redirect('/submit-milestone?curriculum_id=' . $curriculum_id . '&submitted=y');
        exit;
    }
}

$curriculum_id = intval($_GET['curriculum_id'] ?? 0);
$f = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}jph_jpc_curriculum WHERE id = %d", $curriculum_id));

if ($f) {
    echo "<div style='background: #f8f9fa; border: 1px solid #e1e5e9; border-radius: 8px; padding: 20px; margin-bottom: 20px;'>";
    echo "<h3 style='margin: 0 0 10px 0; color: #1f2937; font-size: 1.25rem;'>" . esc_html($f->focus_title) . "</h3>";
    echo "<p style='margin: 0; color: #6b7280; font-size: 14px;'><strong>Tempo:</strong> " . esc_html($f->tempo) . " BPM (do your best but go slower if you need to!)</p>";
    echo "</div>";
}
?>

<form method="post">
    <?php if (!empty($error)) echo $error; ?>
    <p>YouTube URL: <input type="text" name="youtube_url" size="60" value="" /></p>
    <input type="hidden" name="curriculum_id" value="<?php echo $curriculum_id; ?>" />
    <input type="hidden" name="action" value="submit_milestone" />
    <button type="submit" class="je-button">Submit Your Video To Be Graded</button>
</form>
<br />
<a href="/jpc">Cancel, go back to JPC</a>