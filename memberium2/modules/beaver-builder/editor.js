var wpalbb_params = wpalbb_params || {};

(function( $ ) {
	'use strict';
	var $doc = document,
	wpalbb_settings_pop_selector = '.fl-builder-settings-lightbox',
	wpalbb_settings_form_selector = '.fl-builder-settings',
	wpalbb_settings_section_selector = '#fl-builder-settings-section-wpal-blocks',
	$wpalbb_settings_pop = null,
	$wpalbb_settings_form = null,
	$wpalbb_section = null,
	wpalbb_settings_pop_previous_display = 'none',
	wpalbb_popped = false,
	wpalbb_current_node = false,
	wpalbb_settings = {},
	//Observers
	wpalbb_pop_observer,
	wpalbb_pop_config = {
		attributes	: true,
		childList	: true,
		subtree		: true
	};

	/**
	 * Observe Beaver Builder Pop Up
	*/
	function wpalbb_pop_subscriber(mutations) {
		mutations.forEach(function(mutation) {
			var current_display = $wpalbb_settings_pop.style.display;
			//Node Changes
			if (mutation.type == 'childList') {
				$wpalbb_settings_form = $doc.querySelector(wpalbb_settings_form_selector);
				if($wpalbb_settings_form !== null){
					var node = $wpalbb_settings_form.dataset.node,
					node_changed = ( node != wpalbb_current_node ) ? true : false;
					if(node_changed){
						wpalbb_current_node = node;
						init_wpalbb_controls();
					}
				}
			}
			//Attribute Changes
			else if (mutation.type == 'attributes') {
				if( wpalbb_settings_pop_previous_display !== current_display ){
					//Listen For Display Bock Change
					if (wpalbb_settings_pop_previous_display !== "block" && current_display === "block") {
						init_wpalbb_controls();
					}
					//Listen For Display None
					else if ( wpalbb_settings_pop_previous_display === "block" && current_display !== "block" ) {
						wpalbb_popped = false;
						$wpalbb_section = null;
						wpalbb_settings = {};
					}
					wpalbb_settings_pop_previous_display = $wpalbb_settings_pop.style.display;
				}
			}
		});
	}

	/**
	 * Initiate Observers
	*/
	var wpalbb_ready = function () {
		var $wpalbb_settings_pop_jquery = $(wpalbb_settings_pop_selector);
		if ( $wpalbb_settings_pop_jquery.length ) {
			$wpalbb_settings_pop = $wpalbb_settings_pop_jquery[ 0 ];
			wpalbb_settings_pop_previous_display = $wpalbb_settings_pop.style.display;
			wpalbb_pop_observer = new MutationObserver(wpalbb_pop_subscriber);
			wpalbb_pop_observer.observe($wpalbb_settings_pop, wpalbb_pop_config);
			// Return so that we don't call requestAnimationFrame() again
			return;
		}
		// If the body element isn't found, run wpalbb_ready() again at the next pain
		window.requestAnimationFrame(wpalbb_ready);
	};
	// Initialize our wpalbb_ready() function
	window.requestAnimationFrame(wpalbb_ready);

	/**
	 * Initiate WPAL Block Controls and Settings
	*/
	function init_wpalbb_controls(){
		$wpalbb_section = $wpalbb_settings_pop.querySelector(wpalbb_settings_section_selector);
		if( $wpalbb_section !== null ){
			wpalbb_map_controls();
		}
	}

	/**
	 * Map WPAL Block Controls
	*/
	function wpalbb_map_controls(){

		wpalbb_settings.controls = wpalbb_params.controls;
		Object.keys(wpalbb_params.controls).forEach(function(control) {
			var config = wpalbb_params.controls[control],
				control_id = config.name,
				control_sanitize = ( config.sanitize ) ? config.sanitize : '';

			if( control_sanitize > '' ){
				var type = ( config.type === 'textarea' ) ? 'textarea' : 'input';
				wpalbb_settings.controls[control].$el = $wpalbb_section.querySelector(type+'[name="'+control_id+'"]');
				wpalbb_settings.controls[control].$el.onkeyup = function(){
					this.value = wpal_blocks_sanitize( this.value, control_sanitize );
				};
				if(control_sanitize === 'number-csv'){
					wpalbb_settings.controls[control].$el.onblur = function(){
						this.value = this.value.replace(/(,$)/g, "");
					};
				}
			}

			switch (config.type) {
				case 'wpal_blocks_toggle':
					wpalbb_settings.controls[control].$input = $wpalbb_section.querySelector('input[name="'+control_id+'"]');
					wpalbb_settings.controls[control].$input.onchange = function() {
						var checked = wpalbb_settings.controls[control].$input.checked,
						key = ( checked ) ? 'on' : 'off',
						toggles = ( config.toggles[key] ) ? config.toggles[key] : false;
						if( toggles ){
							Object.keys(toggles).forEach(function(t_id) {
								var t_check = ( toggles[t_id] && toggles[t_id] !== 'false') ? true : false;
								wpalbb_toggle_switch( t_id, t_check );
							});
						}
					}
					break;
					case 'text':
						if( config.class === 'bb-wpal-blocks-select2'){
							wpalbb_settings.controls[control].$input = $wpalbb_section.querySelector('input[name="'+control_id+'"]');
							wpalbb_settings.controls[control].$container = wpalbb_settings.controls[control].$input.closest('.fl-field-control-wrapper');
							wpalbb_manage_select2(control);
						}
					break;
				default:
			}
		});
	}

	/**
	 * Manage Select2
	*/
	function wpalbb_manage_select2( control ){

	  var cleansed_value = cleanse_select_values(control);
	  wpalbb_init_select2(control);

	  if( cleansed_value.removed.length > 0 ){

		  var $notice = wpalbb_settings.controls[control].$container.querySelector('.wpal-blocks-notice'),
		   message = wpalbb_params.WPAL_BLOCKS_KEYS_REMOVED_TEXT;
		  message += ' ' + cleansed_value.removed;
		  if( $notice != null ){
			  $notice.innerHTML = message;
		  }
		  else {
			  wpalbb_settings.controls[control].$input.insertAdjacentHTML("beforebegin", '<span class="wpal-blocks-notice">'+message+'</span>');
		  }

		  setTimeout(function(){
			  $(wpalbb_settings.controls[control].$input).wpalSelect2("destroy");
			  wpalbb_settings.controls[control].$input.value = cleansed_value.value;
			  wpalbb_trigger_event( wpalbb_settings.controls[control].$input, 'input' );
			  wpalbb_init_select2(control);
		  }, 3000);
	  }

	}

	/**
	 * Initiate Select2
	*/
	function wpalbb_init_select2(control){
		$(wpalbb_settings.controls[control].$input).wpalSelect2({
			multiple: true,
			data: wpalbb_params.tags,
			dropdownParent: $(wpalbb_settings.controls[control].$container)
	  	}).on("wpalSelect2:selecting", function(e) {
			wpalbb_update_tags( 'add', e.currentTarget, e.params.args.data.id );
	  	}).on("wpalSelect2:unselecting", function(e) {
			wpalbb_update_tags( 'remove', e.currentTarget, e.params.args.data.id );
		});
	}


	/**
	 * Toggle Yes / No Switches Action
	*/
	function wpalbb_toggle_switch( control_id, check ){
		var el_multi = ( control_id == wpalbb_params.WPAL_BLOCKS_PREFIX + '_membership_levels' ) ? '^' : '';
		//multiple
		if( el_multi > '' ){
				Object.keys(wpalbb_settings.controls).forEach(function(t_control) {
					var t_config = wpalbb_settings.controls[t_control];
					if( t_config.level && t_config.level > '' ){
						var multi_checked = wpalbb_settings.controls[t_control].$input.checked;
						if( multi_checked != check ){
							wpalbb_settings.controls[t_control].$input.checked = check;
						}
					}
				});
		}
		else {
			Object.keys(wpalbb_settings.controls).forEach(function(t_control) {
				var config = wpalbb_settings.controls[t_control],
				control = ( config.name === control_id) ? config.$input : false;
				if( control ){
					var checked = control.checked;
					if( checked != check ){
						wpalbb_settings.controls[t_control].$input.checked = check;
					}
				}
			});
		}
	}

	/**
	 * Update Input
	*/
	function wpalbb_update_tags(action, $el, id){
		var data = $($el).wpalSelect2('data'),
		selected = [];
		if( data.length > 0 ){
			for ( var d = 0; d < data.length; d++) {
				var selected_id = data[d].id;
				if( action === 'remove' ){
					if( id != selected_id ){
						selected.push(selected_id);
					}
				}
				else {
					if( ! selected.includes(selected_id) ){
						selected.push(selected_id);
					}
				}
			}
			if( action === 'add' ){
				if( ! selected.includes(selected_id) ){
					selected.push(id);
				}
			}
		}
		$el.value = ( selected.length > 0 ) ? selected.join() : '';
		wpalbb_trigger_event($el,'input');
	}

	/**
	 * Trigger Event
	*/
	function wpalbb_trigger_event( $el, event_name ){
	  var event = new Event(event_name, { bubbles: true });
	  $el.dispatchEvent(event);
	}

	/**
	 * WPAL Block Sanitize
	 * @param {mixed} value - value of setting
	 * @param {string} validate - validation type
	 * @returns {value}
	 */
	var wpal_blocks_sanitize = function( value, validate ){
		switch (validate) {
	        case 'slugify':
	            value = wpal_block_slugify(value);
	            break;
	        case 'number-csv':
	            //Remove all except numbers and commas
	            value = wpal_blocks_number_csv(value);
	            break;
	        default:
	            break;
	    }
	    return value;
	}

	/**
	 * WPAL Block Slugify
	 * @param {mixed} value - value of setting
	 * @returns {value}
	 */
	var wpal_block_slugify = function( value ){
	    var a = 'àáäâãåăæçèéëêǵḧìíïîḿńǹñòóöôœṕŕßśșțùúüûǘẃẍÿź·/,:;';
	    var b = 'aaaaaaaaceeeeghiiiimnnnoooooprssstuuuuuwxyz------';
	    var p = new RegExp(a.split('').join('|'), 'g');
	    return value.toString().toLowerCase()
	        .replace(/\s+/g, '_') // Replace spaces with _
	        .replace(p, c => b.charAt(a.indexOf(c))) // Replace special characters
	        .replace(/&/g, '_and_') // Replace & with 'and'
	        .replace(/[^\w\_]+/g, '') // Remove all non-word characters
	        .replace(/\_\_+/g, '_') // Replace multiple - with single -
	        .replace(/^_+/, ''); // Trim - from start of text
	        //.replace(/_+$/, '') // Trim - from end of text
	}

	/**
	 * WPAL Block Number CSV
	 * @param {mixed} value - value of setting
	 * @returns {value}
	 */
	var wpal_blocks_number_csv = function( value ){
	    return value.replace(/[^0-9,]/g, '')
	    //Remove multiple commas
	    .replace(/,+/g,',')
	    //Remove leading and trailing commas
	    //.replace(/(^,)|(,$)/g, "");
	    .replace(/(^,)/g, "");
	}

	/**
	 * Remove Saved IDs that no longer Exist
	 * @param string control
	 * @return object { value, removed }
	*/
	function cleanse_select_values(control){
		var value = wpalbb_settings.controls[control].$input.value,
		removed = [],
		cleansed = [],
		tags = ( wpalbb_params.tags > '' ) ? wpalbb_params.tags : [];
		if( value > '' ){
			if( tags.length > 0 ){
				var tag_ids = value.split(',');
				if( tag_ids.length > 0 ){
					tag_ids.forEach(function (tag_id) {
						var found = false;
						for(var i = 0; i < tags.length; i++) {
							if (tags[i].id == parseInt(tag_id)) {
								found = true;
								cleansed.push(tag_id);
								break;
							}
						}
						if(!found){
							removed.push(tag_id);
						}
					});
				}
			}
			else{
				return {
					value : '',
					removed : value
				};
			}
		}
		return {
			value : ( cleansed.length > 0 ) ? cleansed.join() : '',
			removed : ( removed.length > 0 ) ? removed.join() : ''
		};
	}

})( jQuery );
