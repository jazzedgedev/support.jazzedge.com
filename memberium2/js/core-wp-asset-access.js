window.membCoreAssetsAccessData = window.membCoreAssetsAccessData || {};
window.membCoreAssetsAccess = window.membCoreAssetsAccess || {};
document.addEventListener('DOMContentLoaded', function(){
    'use strict';
    membCoreAssetsAccess.init();
}, false);

/**
 * Manage Core Assets Access ( Menu, Legacy Widgets and Taxonomies )
 * @namespace membCoreAssetsAccess
 * @type {Object}
*/
window.membCoreAssetsAccess = {
    type    : membCoreAssetsAccessData.type,
    I18n    : membCoreAssetsAccessData.I18n,
    options : membCoreAssetsAccessData.select2Data,
    init    : function (){
        var t = membCoreAssetsAccess;
        if( t.type === 'widget' ){
            t.addListenersWidget();
        }
        else if ( t.type === 'menu' ){
            t.addListenersMenu();
        }
        else if( t.type === 'taxonomy' ){
            t.initializeTaxonomy();
        }
    },
    addListenersMenu : function(){
        // Initialize Select2 on new Menu Items
    	jQuery('#update-nav-menu').on('click', '.item-edit', function(e) {
            e.preventDefault();
    		var $parent = jQuery(this).closest('.menu-item');
            if ($parent.hasClass('select2-rendered') ) {
                return;
            }
            // Find all Select2 Inputs
            jQuery('input[data-memb-asset-select2]', $parent).each( function() {
                var $el      = jQuery(this),
                    dataName = $el.attr("data-memb-asset-select2");
                membCoreAssetsAccess.initializeSelect2($el, $parent, dataName);
            });
            $parent.addClass('select2-rendered');
    	});
    },
    addListenersWidget : function(){
        var t = membCoreAssetsAccess;
        // Initialize Select2 when adding Widgets
    	jQuery(document).on('widget-added', function(event, $widget) {
    		t.initializeWidget($widget);
    	});

    	// Initialize Select2 when updating Widgets
    	jQuery(document).on('widget-updated', function(event, $widget) {
    		$widget.removeClass('select2-rendered');
    		t.initializeWidget($widget);
    	});

        // Initialize Select2 when opening Widgets
    	jQuery('.widget-liquid-right').on('click', '.widget-action, .widget-title.ui-sortable-handle', function(e){
            e.preventDefault();
    		var $widget = jQuery(this).closest('.widget');
    		t.initializeWidget($widget);
    	});
    },
    initializeWidget : function( $widget ){
        var t = membCoreAssetsAccess;
        if( $widget.hasClass('select2-rendered') ){
            return;
        }
        // Find all Select2 Inputs
        jQuery('input[data-memb-asset-select2]', $widget).each( function(){
            var $el   = jQuery(this),
                data  = jQuery(this).attr("data-memb-asset-select2"),
                $wrap = jQuery(this).closest(".memb-asset-access-field-control");
            t.initializeSelect2($el, $wrap, data);
        });
        $widget.addClass('select2-rendered');
    },
    initializeTaxonomy : function(){
        var $fieldset = jQuery(".memb-asset-access-fieldset");
        if( null !== $fieldset ){
            jQuery("input[data-memb-asset-select2]", $fieldset).each(function () {
                var $el      = jQuery(this),
                    dataName = jQuery(this).attr("data-memb-asset-select2"),
                    $parent  = jQuery(this).closest(".memb-asset-access-field-control");
                membCoreAssetsAccess.initializeSelect2($el, $parent, dataName);
            });
        }
    },
    initializeSelect2 : function( $el, $parent, dataName ){
        var t = membCoreAssetsAccess,
            data = t.options.hasOwnProperty(dataName)
                ? t.options[dataName] : [],
            cleansed = t.select2Cleanse($el.val(), data),
            args = {
                data           : data,
                dropdownParent : $parent
            },
            multiple = $el.attr("data-multiple"),
            onChange = $el.attr("data-change"),
            disableSearch = $el.attr("data-disable-search");
        if( parseInt(multiple) > 0 ){
            args.multiple = true;
        }
        if (parseInt(disableSearch) > 0) {
          args.minimumResultsForSearch = -1;
        }

        // Check Removed
        if( cleansed.removed.length > 0 ){
           $el.val(cleansed.value);
           var message = t.I18n.ids_removed,
               $label = jQuery("label[for='" + $el.attr('id') + "']");
           message += ' ' + cleansed.removed,
           jQuery('<span class="memb-asset-access-notice">'+message+'</span>').insertAfter($label);
        }

        // Init
        $el.wpalSelect2(args);

        // On Change trigger
        if( onChange > '' ){
            if( typeof t[onChange] === "function" ){
                $el.on('change.wpalSelect2', function () {
                    t[onChange]($el);
                });
            }
        }
    },
    resetSelect2 : function( $el, $parent ){
        $el = ($el instanceof jQuery) ? $el[0] : $el;
        $el.value = '';
        $el.dispatchEvent(new Event('change'));
        // Destroy Existing
        jQuery($el).wpalSelect2('destroy');
        var t = membCoreAssetsAccess,
            dataName = jQuery($el).attr("data-memb-asset-select2");
        t.initializeSelect2(jQuery($el), $parent, dataName);
    },
    setSelect2Value : function( $el, $parent, value ){
        jQuery($el).wpalSelect2('destroy');
        $el[0].value = value;
        $el[0].dispatchEvent(new Event('change'));
        var t = membCoreAssetsAccess,
            dataName = jQuery($el).attr("data-memb-asset-select2");
        t.initializeSelect2(jQuery($el), $parent, dataName);
    },
    // Remove Saved Key IDs that no longer Exist
    select2Cleanse : function(value, tags){
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
 							if (tags[i].id == tag_id) {
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
 			value   : cleansed.length > 0 ? cleansed.join() : '',
 			removed : removed.length > 0  ? removed.join()  : ''
 		};
 	},
    // Update Fieldset Data Attr & Remove Values if not logged in
    statusToggle   : function( $el ){
        var t          = membCoreAssetsAccess,
            $fieldset  = jQuery( $el.closest('.memb-asset-access-fieldset') ),
    		userStatus = parseInt($el.val()),
    		keys       = membCoreAssetsAccessData.loggedInOnlyKeys;
    	$fieldset.attr('data-user-status', userStatus);
    	for( var k in keys ){
    		var key = keys[k],
    			$wrap = jQuery('.memb-asset-access-field-control[data-setting="'+key+'"]', $fieldset),
    			$input = $wrap ? jQuery('input[data-memb-asset-select2]', $wrap) : null;

    		if( $input && $input !== null ){
    			// Not Logged In Clear Values
    			if( userStatus !== 1 ){
    				t.resetSelect2( $input, $wrap );
    			}
    		}
    	}
    },
    // Set to Logged In Users
    contactToggle : function( $el ){
        if( $el.val() > '' ){
            var $fieldset  = jQuery( $el.closest('.memb-asset-access-fieldset') ),
    			$wrap      = jQuery('.memb-asset-access-field-control[data-setting="status"]', $fieldset),
                $status    = jQuery('[data-change="statusToggle"]', $fieldset),
                userStatus = $status.val();
            if( userStatus != "1" ){
                membCoreAssetsAccess.setSelect2Value($status, $wrap, '1');
            }
        }
    },
    prohibitedActionToggle : function( $el ){
        var $tbody = jQuery($el.closest('.memberium-taxonomy-access-tbody'));
        $tbody.attr("data-prohibited-action", $el.val());
    }
};