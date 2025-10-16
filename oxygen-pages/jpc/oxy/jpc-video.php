<?php
global $vimeo_id, $user_id, $is_active_member, $curriculum_id, $step_id;
if ($is_active_member === 'true' || $is_active_member === 'false' && $curriculum_id < 2) {
  echo do_shortcode( "[fvplayer src='https://vimeo.com/$vimeo_id' ]" );
} else { 
  echo '<a href="/#signup"><img class="responsive-img" src="https://jazzedge.academy/wp-content/uploads/2024/07/Jazzedge-Academy-Upgrade.png" /></a>';
}
?>

<script>
jQuery('.flowplayer').on('fv_track_start', function(e, api, video_name) {
    <?php 
    $type = 'jpc_replay';
    ?>

    // Additional metadata for tracking
    var video_title = api.video.title || video_name;  // Use video title if available, fallback to video_name
    var video_duration = api.video.duration;
    var video_poster = api.video.poster;

    console.log('V2 Tracking video: ' + video_title);

    var tracking = {
        "post_id": <?php echo $step_id; ?>,
        "video_name": <?php echo $curriculum_id; ?>,
        "video_duration": video_duration,
        "user_id": <?php echo $user_id; ?>,
        "type": '<?php echo $type; ?>',
        "video_poster": video_poster
    };

    jQuery.ajax({
        data: tracking,
        type: 'POST',
        url: 'https://jazzedge.academy/willie/analytics/ajax_ja_video_analytics.php',
        success: function(response) {
            console.log('V2 Video tracking successful', response);
        },
        error: function(xhr, status, error) {
            console.error('V2 Error tracking video', error);
        }
    });
});
</script>