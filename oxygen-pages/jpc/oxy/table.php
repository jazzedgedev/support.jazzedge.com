<?php 
global $wpdb, $user_id, $user_membership_level, $user_membership_level_num, $pass_or_redo, $is_active_member;
?>

<div class='rg-container hover-black'>
    <table class='rg-table zebra' summary='Hed'>
        <thead>
            <tr>
                <th class='text'>ID</th>
                <th class='text' width="40%">Focus</th>
                <th class='text center'>C</th> 
                <th class='text center'>F</th>
                <th class='text center'>G</th>
                <th class='text center'>D</th>
                <th class='text center'>Bb</th>
                <th class='text center'>A</th>
                <th class='text center'>Eb</th>
                <th class='text center'>E</th>
                <th class='text center'>Ab</th>
                <th class='text center'>Db</th>
                <th class='text center'>F#</th>
                <th class='text center'>B</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $query = $wpdb->prepare("
            SELECT jp.*, jpc.focus_title, jpc.focus_order 
            FROM jpc_student_progress jp
            JOIN je_practice_curriculum jpc ON jp.curriculum_id = jpc.ID
            WHERE jp.user_id = %d
            ORDER BY jp.curriculum_id ASC
        ", $user_id);
        $q = $wpdb->get_results($query);
        foreach ($q as $r) {
            $curriculum_id = $r->curriculum_id;
            $focus_order = $r->focus_order;
            $focuses_completed = jpc_check_all_steps_complete($user_id,$curriculum_id);
            $ja_milestone_graded = ja_milestone_graded($curriculum_id);
            $resources = $wpdb->get_row($wpdb->prepare("SELECT resource_pdf,resource_ireal,resource_mp3 FROM je_practice_curriculum WHERE id = $curriculum_id"));
            echo '<tr>';
            echo '<td><a href="?action=reset_jpc_focus_progress&id='.$curriculum_id.'" onclick="return confirm(\'Are you sure you want to delete all of your progress for this practice focus? This will delete EVERYTHING from ID: '.$curriculum_id.' and GREATER. For example, if you delete ID #2 and you have progress for ID #4,5 and 6, this will delete ID #2-6.\n\n(This can not be undone)\')"><i class="fa-sharp fa-solid fa-trash"></i></a> '.$focus_order.'</td>';
            echo '<td>'.$r->focus_title.'<br/>';
            if (!empty($resources->resource_pdf)){
              echo "<a href='/jpc_resources/$resources->resource_pdf' target='_blank'><i class='fa-sharp fa-solid fa-music'></i> Download PDF</a>&nbsp;
            ";
            }
            if (!empty($resources->resource_ireal)){
              echo "<a href='/jpc_resources/$resources->resource_ireal' target='_blank'><i class='fa-sharp fa-solid fa-music'></i> Download iRealPro</a>&nbsp;
            ";
            }
            if (!empty($resources->resource_mp3)){
              echo "<a href='/jpc_resources/$resources->resource_mp3' target='_blank'><i class='fa-sharp fa-solid fa-music'></i> Download MP3</a>&nbsp;
            ";
            }
            if (!empty($ja_milestone_graded)) {
                echo $ja_milestone_graded;
            }          
            echo '</td>';
            for ($i = 1; $i <= 12; $i++) {
                $step_column = 'step_' . $i;
                $jpc_status = is_null($r->$step_column) ? 'incomplete' : 'complete';
                $status_color = ($jpc_status == 'incomplete') ? 'gray' : 'green';
              //  $jpc_step_id = ja_jpc_step_id($curriculum_id, $i);
                $jpc_link = ($jpc_status == 'incomplete') ? '' : '<a href="/jpc-lesson/?step_id=' . $r->$step_column . '&cid=' . $curriculum_id . '" target="_blank" class="">';
                $jpc_link_close = !empty($jpc_link) ? '</a>' : '';
                echo '<td class="center">' . $jpc_link . '<i class="fa-sharp ' . $status_color . ' fa-solid fa-circle-play"></i>' . $jpc_link_close . '</td>';
            }
            if ($focuses_completed == 12) {
                $submission_date = ja_milestone_submitted($curriculum_id);
                $video_url = $delete_submission = '';
                if (empty($ja_milestone_graded)) {
                    $video_url = ja_milestone_submission_video_url($curriculum_id);
                    $delete_submission = ' <a href="?action=delete_milestone_submission&id='.$curriculum_id.'" onclick="return confirm(\'Are you sure you want to delete your milestone submission? If you click OK, this milestone submission will be deleted and you will be redirected back to this page.\')"><i class="fa-sharp fa-solid fa-trash"></i></a>';
                }
                if (!empty($submission_date)) {
                    echo '<td style="text-align:center">'.$video_url.' Submitted: ' . $submission_date . $delete_submission;
                    if ($pass_or_redo == 'redo') {
                        $resubmission_date = $wpdb->get_var($wpdb->prepare(
                            "SELECT submission_date FROM je_practice_milestone_submissions WHERE user_id = %d AND curriculum_id = %d AND grade IS NULL ORDER BY submission_date DESC LIMIT 1",
                            $user_id, $curriculum_id
                        ));
                        if (empty($resubmission_date)) {
                            echo '<br /><a href="/submit-milestone/?curriculum_id='.$curriculum_id.'" class="hover-black">
                                <button type="link" class="milestone"><i class="fa-sharp fa-solid fa-upload"></i> Resubmit Milestone</button></a>';
                        }
                    }
                    echo '</td>';
                } else {
                    if ($is_active_member === 'true') {
                        echo '<td style="text-align:center"><a href="/submit-milestone/?curriculum_id='.$curriculum_id.'" class="hover-black">
                            <button type="link" class="milestone">Get Graded</button></a></td>';
                    } else {
                        echo '<td style="text-align:center"><a href="#" onclick="return confirm(\'Only members of the site can get their milestones graded.\n\nIf you would like to know if you are on track with your Jazzedge Practice Curriculum, and get feedback from a real teacher, please consider upgrading to a full membership.\')" class="hover-black">
                            <button type="link" class="milestone">Get Graded</button></a></td>';
                      
                    }
                }
            } else {
                echo '<td></td>';
            }
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
</div>
