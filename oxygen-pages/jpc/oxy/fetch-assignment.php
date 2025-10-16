<?php
global $wpdb, $user_id, $jpc;

// Fetch the most recent assignment for the user
$jpc = $wpdb->get_row($wpdb->prepare(
    "SELECT jpca.*, 
       jpcs.curriculum_id, 
       jpcs.key_sig, 
       jpcs.key_sig_name, 
       jpcs.vimeo_id, 
       jpc.focus_title as instructions, 
       jpc.focus_order as focus,
       jpc.resource_pdf,
       jpc.resource_ireal,
       jpc.resource_mp3,
       jpc.tempo
    FROM jpc_student_assignments jpca
    JOIN je_practice_curriculum_steps jpcs 
    ON jpca.step_id = jpcs.step_id
    JOIN je_practice_curriculum jpc
    ON jpcs.curriculum_id = jpc.ID
    WHERE jpca.user_id = %d
    AND jpca.deleted_at IS NULL
    ORDER BY jpca.ID DESC
    LIMIT 1;",
    $user_id
));

if ($jpc) {
    global $vimeo_id, $key_sig, $key_sig_name, $resource_pdf, $resource_ireal, $resource_mp3, $curriculum_id, $tempo, $instructions, $step_id, $focus;
    $step_id = $jpc->step_id;
    $vimeo_id = $jpc->vimeo_id;
    $key_sig = $jpc->key_sig;
    $key_sig_name = $jpc->key_sig_name;
    $resource_pdf = $jpc->resource_pdf;
    $resource_ireal = $jpc->resource_ireal;
    $resource_mp3 = $jpc->resource_mp3;
    $curriculum_id = $jpc->curriculum_id;
    $instructions = $jpc->instructions;
    $tempo = $jpc->tempo;
    $focus = $jpc->focus;
    
    // For debugging purposes
  /*
    echo "Vimeo ID: " . $vimeo_id . "<br />";
    echo "Key Signature: " . $key_sig . "<br />";
    echo "Key Signature Name: " . $key_sig_name . "<br />";
    echo "Resource: " . $resource . "<br />";
    echo "Curriculum ID: " . $curriculum_id . "<br />";
  */
} else { // setup their JPC
  
$insert_result = $wpdb->insert(
    'jpc_student_assignments',
        [
            'user_id' => $user_id,
            'date' => current_time('mysql'),
            'step_id' => 1,
            'curriculum_id' => 1,
            'deleted_at' => NULL
        ],
        [
            '%d',
            '%s',
            '%d',
            '%d',
            '%s'
        ]
    );
    if ($insert_result === false) {
        echo 'Error: Failed to insert the new assignment.';
    } else {
      wp_redirect('/jpc'); 
	  exit;
    }
}
?>