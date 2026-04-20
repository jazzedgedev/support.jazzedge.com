(function($) {

    // we create a copy of the WP inline edit post function
    var wp_inline_edit = inlineEditPost.edit;

    // and then we overwrite the function with our own code
    inlineEditPost.edit = function(id) {
        // "call" the original WP edit function
        // we don't want to leave WordPress hanging
        wp_inline_edit.apply(this, arguments);
        var post_id = 0; // get the post ID

        if (typeof(id) == 'object') {
            post_id = parseInt(this.getId(id), 10);
        }

        if (post_id > 0) {
            var edit_row = jQuery('#edit-' + post_id);

            var memberships = jQuery('#memb-memberships-' + post_id).val().split(',');
            jQuery.each(memberships, function(index, value) {
                if (value > 0) {
                    var checkbox = edit_row.find('#memb_membership_' + value);
                    checkbox.attr('checked', 'checked');
                }
            });

            var memb_anonymousonly = jQuery('#memb-anonymousonly-' + post_id).val();
            var memb_google1stclick = jQuery('#memb-google1stclick-' + post_id).val();
            var memb_facebookcrawler = jQuery('#memb-facebookcrawler-' + post_id).val();
            var memb_prohibitedaction = jQuery('#memb-prohibitedaction-' + post_id).val();
            var memb_redirecturl = jQuery('#memb-redirecturl-' + post_id).val();

            if (memb_anonymousonly == 1) {
                edit_row.find('#memb_anonymousonly').attr('checked', 'checked');
            }
            if (memb_google1stclick == 1) {
                edit_row.find('#memb_google1stclick').attr('checked', 'checked');
            }
            if (memb_facebookcrawler == 1) {
                edit_row.find('#memb_facebookcrawler').attr('checked', 'checked');
            }
            edit_row.find('#memb_prohibitedaction').val(memb_prohibitedaction);
            edit_row.find('#memb_redirecturl').val(memb_redirecturl);
        }
    };

    $(document).on('click', '#bulk-edit', function() {
        var bulk_row = $('#bulk-edit'); // define the bulk edit row

        // get the selected post ids that are being edited
        // var post_ids = new Array();
        var post_ids = new Array();

        bulk_row.find('#bulk-titles').children().each(function() {
            post_ids.push($(this).attr('id').replace(/^(ttle)/i, ''));
        });

        // Start Custom Data Collection
        // Iterate list of memberships
        var membership_list = bulk_row.find('#bulkedit-membershiplist').val().split(',');
        var memberium_memberships = new Array();

        jQuery.each(membership_list, function(index, value) {
            if (value > 0) {
                var checkbox = bulk_row.find('#memb_membership_level_' + value);

                if (checkbox.prop('checked')) {
                    memberium_memberships.push(checkbox.val());
                }
            }
        });

        var data_memberships = memberium_memberships.join(',');
        var data_anyloggedinuser = bulk_row.find('#memb_anyloggedinuser:checked').val();
        var data_usexisting = bulk_row.find('#memb_useexisting:checked').val();
        var data_anonymousonly = bulk_row.find('#memb_anonymousonly:checked').val();
        var data_google1stclick = bulk_row.find('#memb_google1stclick:checked').val();
        var data_facebookcrawler = bulk_row.find('#memb_facebookcrawler:checked').val();
        var data_prohibitedaction = bulk_row.find('#memb_prohibitedaction').val();
        var data_redirecturl = bulk_row.find('#memb_redirecturl').val();
        // End Custom Data Collection

        // save the data
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            async: false,
            cache: false,
            data: {
                action: 'memberium_save_bulk_edit',
                post_ids: post_ids,
                memb_memberships: data_memberships,
                memb_anonymousonly: data_anonymousonly,
                memb_anyloggedinuser: data_anyloggedinuser,
                memb_google1stclick: data_google1stclick,
                memb_facebookcrawler: data_facebookcrawler,
                memb_prohibitedaction: data_prohibitedaction,
                memb_redirecturl: data_redirecturl,
                memb_useexisting: data_usexisting
            }
        });
    });

})(jQuery);