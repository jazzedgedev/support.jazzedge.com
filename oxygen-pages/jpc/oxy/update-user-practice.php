<?php

global $wpdb, $user_id, $curriculum_id;
$action = $_POST['action'] ?? $_GET['action'] ?? null;
if ($action == 'delete_milestone_submission') {
    $curriculum_id = intval($_GET['id']);
    $wpdb->delete( 'je_practice_milestone_submissions', array( 'curriculum_id' => $curriculum_id, 'user_id' => $user_id ) );
    wp_redirect('//jazzedge.academy/jpc'); 
    exit;
}

if ($action === 'reset_jpc_focus_progress') {
    $curriculum_id = isset($_GET['id']) ? intval($_GET['id']) : null;
    if ($curriculum_id !== null) {
        jpc_delete_curriculum_data($user_id, $curriculum_id);
        wp_redirect('//jazzedge.academy/jpc'); 
        exit;
    }
}

?>