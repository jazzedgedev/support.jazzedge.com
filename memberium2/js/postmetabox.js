var haveHaveNotTagList = [];

jQuery( document ).ready( function() {
	
	if ( jQuery.fn.hasOwnProperty('wpalSelect2') ) {
		memberium_meta_update();

		jQuery(".tag-selector").wpalSelect2({
			tags: taglist
		});

		if( jQuery(".have-not-have-tag-selector").length ){
			for( var t in taglist ){
				if( taglist[t].id > 0 ){
					haveHaveNotTagList.push(taglist[t]);
					haveHaveNotTagList.push({
						id 	 : taglist[t].id - (taglist[t].id * 2),
						text : "Does Not Have " + taglist[t].text
					});
				}
			}
			jQuery(".have-not-have-tag-selector").wpalSelect2({
				tags: haveHaveNotTagList
			});
		}

		jQuery(".actionset-selector").wpalSelect2({});
	 }

	var memb_coursegrid_thumbnails = jQuery('.memb_coursegrid_thumbnail');
	if( memb_coursegrid_thumbnails.length ){
		ini_memberium_coursegrid_thumbnails();
	}

});

function memberium_meta_update() {

	var prohibited_action = jQuery( '#is4wp_prohibited_action option:selected' ).text();
	if ( prohibited_action !== 'Redirect' && prohibited_action !== 'Site Default (Redirect)' ) {
		jQuery( '#is4wp_redirect_url' ).prop( 'disabled', true );
	}

	if ( jQuery( "#is4wp_force_public" ).is(":checked") ) {
		jQuery( '.memberium_membership_checkbox' ).prop( 'checked', false );
		jQuery( '#is4wp_anymembership' ).prop( 'checked', false );
		jQuery( '#is4wp_anonymous_only' ).prop( 'checked', false );
		jQuery( '#is4wp_loggedin' ).prop( 'checked', false );
		jQuery( '.memb_redirect_options' ).hide();
		jQuery( '.memb_access_options' ).hide();
		jQuery( '#is4wp_redirect_url' ).prop( 'disabled', true );
	}

	if ( jQuery( "#is4wp_loggedin" ).is(":checked") ) {
		jQuery(".memberium_membership_checkbox").prop( "checked", false );
		jQuery("#is4wp_force_public").prop( "checked", false );
		jQuery("#is4wp_anymembership").prop( "checked", false );
		jQuery("#is4wp_anonymous_only").prop("checked", false );
	}

	if ( jQuery( "#is4wp_anonymous_only" ).is(":checked") ) {
		jQuery(".memberium_membership_checkbox").prop( "checked", false);
		jQuery("#is4wp_force_public").prop( "checked", false );
		jQuery("#is4wp_anymembership").prop("checked", false);
		jQuery("#is4wp_loggedin").prop("checked", false);
	}

	if ( jQuery( "#is4wp_anymembership" ).is(":checked") ) {
		jQuery(".memberium_membership_checkbox").prop( "checked", true);
		jQuery("#is4wp_anonymous_only").prop( "checked", false );
		jQuery("#is4wp_loggedin").prop( "checked", false );
	}

	if ( ! jQuery( ".memberium_membership_checkbox" ).is(":checked") ) {
		jQuery( '#is4wp_anymembership' ).prop( 'checked', false );
	}

	jQuery("#is4wp_prohibited_action").change( function() {
		var prohibited_action = jQuery("#is4wp_prohibited_action option:selected").text();
		if ( prohibited_action !== "Redirect" && prohibited_action !== "Site Default (Redirect)" ) {
			jQuery("#is4wp_redirect_url").prop( "disabled", true );
			jQuery("#is4wp_redirect_url").hide();
		}
		else {
			jQuery("#is4wp_redirect_url").prop( "disabled", false );
			jQuery("#is4wp_redirect_url").show();
		}
	});

	jQuery("#is4wp_force_public").change( function() {
		if ( jQuery( "#is4wp_force_public" ).is(":checked") ) {
			jQuery( '.memberium_membership_checkbox' ).prop( 'checked', false );
			jQuery( '#is4wp_anymembership' ).prop( 'checked', false );
			jQuery( '#is4wp_anonymous_only' ).prop( 'checked', false );
			jQuery( '#is4wp_loggedin' ).prop( 'checked', false );
			jQuery( '.memb_redirect_options' ).hide();
			jQuery( '.memb_access_options' ).hide();
			jQuery( '#is4wp_redirect_url' ).prop( 'disabled', true );
		}
		else {
			jQuery( '.memb_redirect_options' ).show();
			jQuery( '.memb_access_options' ).show();
			jQuery( '#is4wp_redirect_url' ).prop( 'disabled', false );
		}
	});

	jQuery("#is4wp_loggedin").change( function() {
		if ( jQuery( "#is4wp_loggedin" ).is(":checked") ) {
			jQuery( '#is4wp_force_public' ).prop( 'checked', false );
			jQuery(".memberium_membership_checkbox").prop( "checked", false );
			jQuery("#is4wp_anymembership").prop( "checked", false );
			jQuery("#is4wp_anonymous_only").prop("checked", false );
		}
	});

	jQuery("#is4wp_anonymous_only").change( function() {
		if ( jQuery( "#is4wp_anonymous_only" ).is(":checked") ) {
			jQuery(".memberium_membership_checkbox").prop( "checked", false);
			jQuery("#is4wp_anymembership").prop("checked", false);
			jQuery( '#is4wp_force_public' ).prop( 'checked', false );
			jQuery("#is4wp_loggedin").prop("checked", false);
		}
		else {
		}
	});

	jQuery(".memberium_membership_checkbox").change( function() {
		if ( jQuery( ".memberium_membership_checkbox" ).is(":checked") ) {
			jQuery( '#is4wp_force_public' ).prop( 'checked', false );
			jQuery("#is4wp_anonymous_only").prop("checked", false );
			jQuery("#is4wp_anymembership").prop( "checked", false );
			jQuery("#is4wp_loggedin").prop( "checked", false );
		}
		else {
		}
	});

	jQuery("#is4wp_anymembership").change( function() {
		if ( jQuery( "#is4wp_anymembership" ).is(":checked") ) {
			jQuery(".memberium_membership_checkbox").prop( "checked", true);
			jQuery( '#is4wp_force_public' ).prop( 'checked', false );
			jQuery("#is4wp_anonymous_only").prop( "checked", false );
			jQuery("#is4wp_loggedin").prop( "checked", false );
		}
		else {
			jQuery("#is4wp_anymembership").prop( "disabled", false );
			jQuery("#is4wp_loggedin").prop( "disabled", false );
		}
	});
}

function ini_memberium_coursegrid_thumbnails(){

	var memb_media_frame,
		memb_media_attachment,
		memb_media_i18n,
		$memb_media_wrapper,
		$memb_media_upload,
		$memb_media_remove,
		$memb_media_preview,
		$memb_media_url,
		$memb_media_id;

	jQuery(document).on('click', '.memb_coursegrid_thumbnail_remove', function(e) {
		$memb_media_wrapper = jQuery(this).parents('.memb_coursegrid_thumbnail');
		jQuery('.memb_coursegrid_preview', $memb_media_wrapper).html('');
		jQuery('.memb_coursegrid_thumbnail_url', $memb_media_wrapper).val('');
		jQuery('.memb_coursegrid_thumbnail_id', $memb_media_wrapper).val('');
		jQuery(this).remove();
	});

	jQuery(document).on('click', '.memb_coursegrid_thumbnail_upload', function(e) {

        e.preventDefault();

        $memb_media_upload = jQuery(this);
		$memb_media_wrapper = $memb_media_upload.parents('.memb_coursegrid_thumbnail');
		$memb_media_preview = jQuery('.memb_coursegrid_preview', $memb_media_wrapper);
		$memb_media_remove = jQuery('.memb_coursegrid_thumbnail_remove', $memb_media_wrapper);
		$memb_media_url = jQuery('.memb_coursegrid_thumbnail_url', $memb_media_wrapper);
		$memb_media_id = jQuery('.memb_coursegrid_thumbnail_id', $memb_media_wrapper);
		memb_media_i18n = memb_coursegrid_i18n[$memb_media_wrapper.data('key')];

        if (memb_media_frame) {
            memb_media_frame.open();
            return;
        }

        memb_media_frame = wp.media.frames.memb_media_frame = wp.media({
			title		: memb_media_i18n.title,
			library		: { type: 'image' },
            button		: { text: memb_media_i18n.button },
			multiple	: false
        });

		memb_media_frame.on('open', function () {
			var selectedID = $memb_media_id.val();
			if( parseInt(selectedID) > 0 ){
				memb_media_frame.state().get('selection').add(wp.media.attachment(selectedID));
			}
		});

        memb_media_frame.on('select', function () {
            memb_media_attachment = memb_media_frame.state().get('selection').first().toJSON();
			$memb_media_url.val(memb_media_attachment.url);
			$memb_media_id.val(memb_media_attachment.id);
			$memb_media_preview.html('<button class="memb_coursegrid_thumbnail_upload"><image src="'+memb_media_attachment.url+'"/></button>');
			if( ! $memb_media_remove.length ){
				$memb_media_wrapper.prepend('<a class="memb_coursegrid_thumbnail_remove" href="#"><span class="dashicons dashicons-dismiss"></span><span class="screen-reader-text">'+memb_media_i18n.remove+'</span></a>');
			}
        });

        memb_media_frame.open();

    });
}