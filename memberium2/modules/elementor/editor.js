var wpale_params = wpale_params || {},
WPAL_BLOCKS_PREFIX = wpale_params.WPAL_BLOCKS_PREFIX;
(function( $ ) {
	'use strict';
  $( window ).on( 'elementor:init', function() {
		var $panel = $('#elementor-panel'),
			$control_wrap = false,
			select2s = [];

		var observer = new MutationObserver(function(mutations) {
	      //loop through the detected mutations(added controls)
	      mutations.forEach(function(mutation) {
	        if (mutation && mutation.addedNodes) {
	          mutation.addedNodes.forEach(function($node) {
				  //If control stack check for open WPAL Settings Tab
				  if( is_control_stack($node) ){
					  $node.childNodes.forEach(function($childNode) {
						  if( $childNode.id && $childNode.id === 'elementor-controls' ){
							  var wpal_settings_open = false;
							  $childNode.childNodes.forEach(function($els) {
								  if( $els.classList ){
									  if( $els.classList.contains('elementor-control-wpal-blocks') ){
										  if( $els.classList.contains('elementor-open') ){
											  wpal_settings_open = true;
										  }
									  }
									  else{
										  if( is_access_tag($els) ){
											  manage_access_tags($els);
										  }
									  }
								  }
   							  });
						  }
					  });
				  }
				  //Just Check for our Custom Select2s
				  else {
					  if( is_access_tag($node) ){
						  manage_access_tags($node);
					  }
				  }
			  });
	  		}
			});
	    });

		/**
		 * Fire up a mutation observer
		*/
		observer.observe($panel[0], {
		  childList: true,
		  subtree: true,
		  attributes: true,
		});

		/**
		 * Check if Node is Access Tag
		*/
		var is_access_tag = function ( $el ){
			var result = false;
			if( $el.classList ){
				if( $el.classList.contains('elementor-control-' + WPAL_BLOCKS_PREFIX + '_access_tags') ){
					result = true;
				}
				else if ( $el.classList.contains('elementor-control-' + WPAL_BLOCKS_PREFIX + '_access_tags2') ){
					result = true;
				}
			}
			return result;
		}

		/**
		 * Check if Node is Control Stack
		*/
		var is_control_stack = function ( $el ){
			var result = false;
			if( $el.classList ){
				result = $el.classList.contains('elementor-controls-stack');
			}
			return result;
		}

		/**
		 * Manage Select2 Inputs
		*/
		var manage_access_tags = function ( $wrapper ){
			var $label = $wrapper.querySelector("label.elementor-control-title"),
			$el = $wrapper.querySelector("input[data-setting]"),
			id	= $el.id,
			cleansed_value = cleanse_select_values($el.value,wpale_params.tags);
			//Check Removed
			if( cleansed_value.removed.length > 0 ){
				$el.value = cleansed_value.value;
				wpale_trigger_event( $el, 'input' );
				var message = wpale_params.WPAL_BLOCKS_KEYS_REMOVED_TEXT;
				message += ' ' + cleansed_value.removed;
				$label.insertAdjacentHTML("afterend", '<span class="wpal-blocks-notice">'+message+'</span>');
	  	  	}
			$($el).select2({
				multiple: true,
				data: wpale_params.tags,
				dropdownParent: $panel,
			}).on('change.select2', function (e) {
				var $target = e.currentTarget,
				trimValue = $target.value.replace(/(^,)|(,$)/g, ""),
				valueArray = ( trimValue > '' ) ? trimValue.split(',') : false,
				unique = (valueArray) ? valueArray.filter(function (x, i, a) {
					return a.indexOf(x) == i;
				}) : '';
				$target.value = ( unique > '' ) ? unique.join(",") : '';
				wpale_trigger_event($target,'input');
			});
		}

		/**
		 * Map Toggle Controls
		*/
		Object.keys(wpale_params.controls).forEach(function(control) {
			var config = wpale_params.controls[control],
				control_id = config.name,
				control_toggles = config.toggles,
				control_sanitize = ( config.sanitize ) ? config.sanitize : '';
			if( control_sanitize > '' ){
				var type = ( config.type === 'textarea' ) ? 'textarea' : 'input';
				$panel.on('keyup', type + '[data-setting="'+control_id+'"]', function( e ) {
					this.value = wpal_blocks_sanitize( this.value, control_sanitize );
				});
				if(control_sanitize === 'number-csv'){
					$panel.on('blur', type + '[data-setting="'+control_id+'"]', function( e ) {
						//Remove Trailing Comma
						this.value = this.value.replace(/(,$)/g, "");
					});
				}
			}
			$panel.on('change', 'input[data-setting="'+control_id+'"]', function( e ) {
				//Check if toggling other inputs
				var $input = $(this),
					key = ( $input.is(":checked") ) ? 'on' : 'off',
					toggles = ( control_toggles && control_toggles[key] ) ? control_toggles[key] : false;
				if( toggles ){
					Object.keys(toggles).forEach(function(t_id) {
						var memberships = ( t_id == WPAL_BLOCKS_PREFIX + '_membership_levels' ) ? true : false,
							t_check = ( toggles[t_id] && toggles[t_id] !== 'false') ? true : false;
						//Multiple Memberships
						if(memberships){
							Object.keys(wpale_params.controls).forEach(function(wpal_control) {
								var wpal_config = wpale_params.controls[wpal_control];
								if( wpal_config.level && wpal_config.level > '' ){
									var $wpal_input = $( 'input[data-setting="'+wpal_config.name+'"]', $panel ),
										wpal_checked = $wpal_input.is(":checked");
									if( wpal_checked != t_check ){
										$wpal_input.prop( "checked", t_check ).trigger('change');
									}
								}
							});
						}
						//Single Input
						else{
							var $t_input = $( 'input[data-setting="'+t_id+'"]', $panel ),
								is_checked = $t_input.is(":checked");
							if( is_checked != t_check ){
								$t_input.prop( "checked", t_check ).trigger('change');
							}
						}
					});
				}
			});
		});
	});

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
	 * Trigger Event
	*/
	function wpale_trigger_event( $el, event_name ){
	  var event = new Event(event_name, { bubbles: true });
	  $el.dispatchEvent(event);
	}

	/**
	 * Remove Saved IDs that no longer Exist
	 * @param string value
	 * @param array tags
	 * @return object { value, removed }
	*/
	function cleanse_select_values(value, tags){
		var removed = [],
		cleansed = [],
		tags = ( tags > '' ) ? tags : [];
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
