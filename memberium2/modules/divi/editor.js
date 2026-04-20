//closest() Polyfill
window.Element&&!Element.prototype.closest&&(Element.prototype.closest=function(e){var t,o=(this.document||this.ownerDocument).querySelectorAll(e),n=this;do{for(t=o.length;0<=--t&&o.item(t)!==n;);}while(t<0&&(n=n.parentElement));return n});

var wpald_params = wpald_params || {};

console.log({
	wpald_params:wpald_params
});

(function( $ ) {
	'use strict';

	var $doc = document,
	$control_wrap = false,
	$ifrm = null,
	$et_container = null,
	$et_fb = null,
	et_fb = false,
	divi_pop_class = '.et_pb_modal_settings_container',
	wpald_on_class = 'et_pb_on_state',
	wpald_toggle_container = '.et-pb-option[data-option_name]',
	wpald_toggle_field = 'select.yes_no_button',
	wpald_toggle_event = 'change',
	wpald_toggle_wrap = '.et_pb_yes_no_button_wrapper',
	wpald_toggle = '.et_pb_yes_no_button',
	wpald_settings_group = 'et-fb-form__toggle-opened',
	wpald_select2_wrap = '.et-pb-option',
	et_prefix = 'et_pb_',
	$divi_pop = null,
	$wpald_toggle = null,
	wpald_settings = {},
	divi_popped = false,
	wpal_pop_observer_config = {
		attributes: true,
		childList: true,
		subtree: true,
		characterData: true,
	},
	wpald_toggle_observer_config = {
		attributes: true,
		attributeOldValue: true,
		attributeFilter: ['class']
	},
	wpald_class_config = {
		attributes: true,
		attributeFilter: ['class'],
		subtree: false
	};

	/**
	 * Listen For Divi Pop Up
	*/
	function wpald_pop_subscriber(mutations) {
		$divi_pop = $doc.querySelector(divi_pop_class);
		if ( $divi_pop !== null ) {
			if( !divi_popped ){
				if( !et_fb ){
					var $toggle_container = $('.et-pb-options-tab-custom_css h3.et-pb-option-toggle-title:contains("'+wpald_params.WPAL_BLOCKS_SETTINGS_TITLE+'")').closest('.et-pb-options-toggle-container');
					$wpald_toggle = $toggle_container[0];
				}
				else {
					$wpald_toggle = $divi_pop.querySelector('.et-fb-form__toggle[data-name="wpal-blocks"]');
				}

				if( $wpald_toggle !== null ){
					wpald_settings.opened = false;
					wpald_settings.select2 = false;
					if( et_fb ){
						wpald_toggle_observer.observe($wpald_toggle, wpald_toggle_observer_config);
					}
					else {
						wpald_tab_observer.observe( $('.et_pb_options_tab_custom_css')[0], wpald_toggle_observer_config);
					}
					divi_popped = true;
				}
			}
		}
		else if ( divi_popped ) {
			divi_popped = false;
			$wpald_toggle = null;
			wpald_settings = {};
			wpald_toggle_observer.disconnect();
		}
	}

	/**
	 * Listen For Classic Tab Change
	*/
	function wpald_tab_subscriber(mutations) {
		if( !wpald_settings.select2 ){
			mutations.forEach(function(mutation) {
				var el = mutation.target,
				open_class = ( el.classList && el.classList.contains('et-pb-options-tabs-links-active') ) ? true : false;
				if ( (!mutation.oldValue || !mutation.oldValue.match(/\bet-pb-options-tabs-links-active\b/)) && open_class ){
					wpald_map_controls();
				}
			});
		}
	}

	/**
	 * Listen For FB Module WPAL Block Settings Toggle Change
	*/
	function wpald_toggle_subscriber(mutations) {
		//Check if tab is opened
		mutations.forEach(function(mutation) {
	    var $el = mutation.target,
			was_open = ( mutation.oldValue.indexOf(wpald_settings_group) !== -1 ) ? true : false,
			is_open = ( $el.classList.contains(wpald_settings_group) ) ? true : false;
			//Opening Initiate
			if( is_open && !was_open ){
				wpald_map_controls();
				//Hide Support Warnings
				var warnings = $wpald_toggle.querySelectorAll(".et-fb-no-vb-support-warning");
				for (var w = 0; w < warnings.length; w++) {
					warnings[w].style.display = 'none';
				}
				//Hide Help
				var help = $wpald_toggle.querySelectorAll(".et-fb-form__help");
				for (var h = 0; h < help.length; h++) {
					help[h].style.display = 'none';
				}
				//Hide Other Right Button
				var right_button = $wpald_toggle.querySelectorAll(".et-fb-button--right-menu");
				for (var r = 0; r < right_button.length; r++) {
					right_button[r].style.display = 'none';
				}

				//Disable Right Click Menus
				$($wpald_toggle).on("contextmenu",function(e){
					return false;
				});
			}
			//Disabling
			else if( !is_open && was_open ){
				wpald_settings = {};
			}
			else {
				//Nothing doing
			}
		});
	}

	/**
	 * Listen For Toggle Switch Changes
	*/
	function wpald_yes_no_subscriber(mutations) {

		mutations.forEach(function(mutation) {
			var $el = mutation.target,
			is_on = ($el.classList.contains(wpald_on_class)) ? true : false,
			key = ( is_on ) ? 'on' : 'off',
			$container = $el.closest(wpald_toggle_container),
			$field = $container.querySelector(wpald_toggle_field);
			var control_id = ( et_fb ) ? $field.name : $container.getAttribute('data-option_name'),
			control = wpald_settings.controls[control_id],
			toggles = ( control.wpald_toggles && control.wpald_toggles[key] ) ? control.wpald_toggles[key] : false;
			if( toggles ){
				Object.keys(toggles).forEach(function(t_id) {
					var t_check = ( toggles[t_id] && toggles[t_id] !== 'false') ? true : false;
					wpald_toggle_switch( t_id, t_check );
				});
			}
		});
	}


	/**
	 * Initiate observers
	*/
	var wpald_pop_observer = new MutationObserver(wpald_pop_subscriber),
		wpald_tab_observer = new MutationObserver(wpald_tab_subscriber),
		wpald_toggle_observer = new MutationObserver(wpald_toggle_subscriber),
		wpald_yes_no_observer = new MutationObserver(wpald_yes_no_subscriber);
	wpald_pop_observer.observe($doc.body, wpal_pop_observer_config);
	//Catch New Divi FB
	$(window).on('et_builder_api_ready', function (event, API) {

	  $ifrm = window.frameElement;
	  $doc = $ifrm.ownerDocument;
		if( !et_fb ){
			wpald_pop_observer.disconnect();
		}
		et_fb = true;
		divi_pop_class = '.et-fb-modal';
		et_prefix = 'et-fb-';
		wpald_on_class = 'et-core-control-toggle--on';
		wpald_toggle_container = '.et-fb-option-container';
		wpald_toggle_wrap = '.et-fb-option-container';
		wpald_toggle = '.et-core-control-toggle';
		wpald_toggle_field = 'input[type="hidden"]';
		wpald_toggle_event = 'input';
		wpald_select2_wrap = '.et-fb-form__group';
		wpald_pop_observer.observe($doc.body, wpal_pop_observer_config);

		// Catch the Row to Column Change and reset our global popped variables
		$doc.addEventListener('click', function (e) {
			var $el = e.target;
			if ( $el !== null && $el.matches('.et-fb-settings-button--back-to-parent')) {
				$divi_pop = null;
				divi_popped = false;
			}
		});
	});

	/**
	 * Map WPAL Block Controls
	*/
	function wpald_map_controls(){
		wpald_settings.controls = wpald_params.controls;
		Object.keys(wpald_params.controls).forEach(function(control) {
			var config = wpald_params.controls[control],
				control_id = '#'+et_prefix+''+control,
				control_sanitize = ( config.sanitize ) ? config.sanitize : '';
			if( control_sanitize > '' ){
				wpald_settings.controls[control].$el = $wpald_toggle.querySelector(control_id);
				wpald_settings.controls[control].$el.onkeyup = function(){
					this.value = wpal_blocks_sanitize( this.value, control_sanitize );
				};
				if(control_sanitize === 'number-csv'){
					wpald_settings.controls[control].$el.onblur = function(){
						this.value = this.value.replace(/(,$)/g, "");
					};
				}
			}

			switch (config.type) {
				case 'yes_no_button':
					wpald_settings.controls[control].$el = $wpald_toggle.querySelector(control_id);
					wpald_settings.controls[control].$container = wpald_settings.controls[control].$el.closest( wpald_toggle_wrap );
					wpald_settings.controls[control].$toggle = wpald_settings.controls[control].$container.querySelector( wpald_toggle );
					wpald_yes_no_observer.observe(wpald_settings.controls[control].$toggle, wpald_class_config);
					break;
				case 'text':
					if( config.wpald === 'select2' ){
						wpald_settings.controls[control].$el = $wpald_toggle.querySelector(control_id);
						wpald_settings.controls[control].$container = wpald_settings.controls[control].$el.closest(wpald_select2_wrap);
						wpald_settings.controls[control].tags = wpald_tag_array( wpald_settings.controls[control].$el.value );
						wpald_init_select2(control);
						wpald_settings.select2 = true;
					}
					break;
				default:
			}
		});
	}

	/**
	 * Initiate Select2
	*/
	function wpald_init_select2( control ){
	  var $el = wpald_settings.controls[control].$el,
	  	$jEl = $($el),
		cleansed_value = cleanse_select_values($el.value,wpald_params.tags);
	  wpald_settings.controls[control].value = cleansed_value.value;
	  if( cleansed_value.removed.length > 0 ){
		  $el.value = cleansed_value.value;
		  wpald_trigger_event( $el, 'input' );
		  var message = wpald_params.WPAL_BLOCKS_KEYS_REMOVED_TEXT;
		  message += ' ' + cleansed_value.removed;
		  $el.insertAdjacentHTML("beforebegin", '<span class="wpal-blocks-notice">'+message+'</span>');
	  }
	  //Getting around the new builder iFrame / Classic Builder BS
	  var jQ = ( et_fb ) ? window.top.jQuery : $;
	  if( et_fb ){
		  //This won't be defined when classic editor is enabled and
		  //editing Divi with the new builder.
		  if ( ! jQ.isFunction(window.wpalSelect2)) {
    		  jQ = $;
    	  }
	  }

	  jQ($jEl).wpalSelect2({
		  multiple: true,
		  data: wpald_params.tags,
		  dropdownParent: $(wpald_settings.controls[control].$container)
	  }).on("wpalSelect2:selecting", function(e) {
		  if( et_fb ){
			  wpald_update_tags( 'add', control, e.params.args.data.id );
		  }
	  }).on("wpalSelect2:unselecting", function(e) {
		  if( et_fb ){
			  wpald_update_tags( 'remove', control, e.params.args.data.id );
		  }
	  });
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


	/**
	 * Toggle Yes / No Switches Action
	*/
	function wpald_toggle_switch( control_id, check ){
		var el_multi = ( control_id == wpald_params.WPAL_BLOCKS_PREFIX + '_membership_levels' ) ? '^' : '',
		new_value = ( check ) ? 'on' : 'off';
		//multiple
		if( el_multi > '' ){
			Object.keys(wpald_settings.controls).forEach(function(t_control) {
				var t_config = wpald_settings.controls[t_control];
				if( t_config.wpald_level && t_config.wpald_level > '' ){
					if( t_config.$el.value !== new_value ){
						wpald_settings.controls[t_control].$el.value = new_value
						if( et_fb ){
							wpald_trigger_event( t_config.$toggle, 'click');
						}
						wpald_trigger_event( t_config.$el, wpald_toggle_event);
					}
				}
			});
		}
		else {
			var control = ( wpald_settings.controls.hasOwnProperty( control_id ) ) ? wpald_settings.controls[control_id] : false;
			if( control ){
				if( control.$el.value !== new_value ){
					wpald_settings.controls[control_id].$el.value = new_value
					if( et_fb ){
						wpald_trigger_event( wpald_settings.controls[control_id].$toggle, 'click');
					}
					wpald_trigger_event( control.$el, wpald_toggle_event );
				}
			}
		}
	}

	/**
	 * Convert CSV To Array
	*/
	function wpald_tag_array( value ){
	  var result = [];
	  if( value > '' ){
	    if( value.indexOf(',') > -1 ){
	      result = value.split(',');
	    }
		else {
	      result.push(value);
	    }
	  }
	  return result;
	}

	/**
	 * Update Tags
	*/
	function wpald_update_tags( action, control, id ){
	  var el = wpald_settings.controls[control],
	  	$el = el.$el,
		tags = ( el.tags ) ? el.tags : [],
	    new_val = ( el.value ) ? el.value : '';
	  switch (action) {
	    case 'add':
			tags.push(id);
	      break;
	    case 'remove':
			var tags_length = tags.length;
			if( tags_length > 1 ){
				var key = tags.indexOf(id);
				if(key > -1){
					tags.splice(key, 1);
				}
			}
			else {
				tags = [];
			}
	      break;
	    default:
	      break;
	  }
	  new_val = tags.join();
	  if( new_val !== wpald_settings.controls[control].value ){
		  wpald_settings.controls[control].tags = tags;
		  wpald_settings.controls[control].value = new_val;
		  if( et_fb ){
			  wpald_settings.controls[control].$el.value = new_val;
			  wpald_trigger_event( wpald_settings.controls[control].$el, 'input' );
		  }
	  }
  	}

	/**
	 * Trigger Event
	*/
	function wpald_trigger_event( $el, event_name ){
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


})( jQuery );
