/**
	* Define Variables
	*/
var memberium_data = false,
	mvp_params = false,
	apis_loaded = [],
	generated_ids = [],
	oembed_str = 'feature=oembed',
	memb_videos = {};

(function($) {
	'use strict';

	/**
    * Window Loaded
    */
	window.addEventListener('load', function () {
		memberium_data = ( window.memberium_data ) ? window.memberium_data : {};
		mvp_params = ( memberium_data.mvp_params ) ? memberium_data.mvp_params : {};
		mvp_init();
	}, false);

	/**
    * Init
    */
	function mvp_init(){
		if( mvp_params ){
			var src_array = get_src_array();
			Object.keys(mvp_params).forEach(function(v) {
				var vid_config = mvp_params[v],
					src = vid_config.src,
					type = vid_config.type,
					el_id = false;
				if( !memb_videos ){
					memb_videos = {};
				}
				memb_videos[v] = {};
				memb_videos[v].interval = false;
				memb_videos[v].src = vid_config.src;
				memb_videos[v].type = type;
				memb_videos[v].timer = false;
				memb_videos[v].time = 0;
				memb_videos[v].finished_tag_event = [];
				memb_videos[v].time_tags = vid_config.time_tags;
				memb_videos[v].video_id = '';
				memb_videos[v].$el = null;
				memb_videos[v].el_id = '';

				if (type === 'youtube') {
					memb_videos[v].video_id = get_YouTube_id(src);
					memb_videos[v] = get_node_info(v, src_array);
					//Check for oembed
					var v_src = memb_videos[v].$el.src;
					if( v_src.indexOf(oembed_str) !== -1 ){
						var oembed_replace = '',
							enablejsapi = false;
						//Check for enablejsapi param
						if( v_src.indexOf('enablejsapi') === -1 ){
							enablejsapi = true;
							oembed_replace += 'enablejsapi=1';
						}
						//Check for origin param
						if( v_src.indexOf('origin') === -1 ){
							oembed_replace += (enablejsapi) ? '&' : '';
							oembed_replace += 'origin='+memberium_data.home_url;
						}
						//Update Params
						v_src = v_src.replace(oembed_str, oembed_replace);
						//Make sure url does not have ?&
						v_src = v_src.replace('?&', '?');
						memb_videos[v].$el.src = v_src;
					}
					//Load Youtube script once
					if( apis_loaded.indexOf(type) === -1 ){
						load_api_script(type, 'https://www.youtube.com/player_api');
						apis_loaded.push(type);
					}
				}
				if (type === 'vimeo') {
					memb_videos[v].video_id = get_vimeo_id(src);
					memb_videos[v] = get_node_info(v, src_array);
					//Load API Script
					if( apis_loaded.indexOf(type) === -1 ){
						load_api_script(type,'https://player.vimeo.com/api/player.js');
						apis_loaded.push(type);
					}
				}
			});
		}
	}

	/**
		* Youtube Player Ready
		*/
	window.onYouTubePlayerAPIReady = function() {
		//TODO Add debounce function
		Object.keys(memb_videos).forEach(function(v) {
			if( memb_videos[v].type === 'youtube' ){
				memb_videos[v].player = new YT.Player(memb_videos[v].el_id, {
					videoId : memb_videos[v].video_id,
				});
				memb_videos[v].player.addEventListener('onStateChange', function(e) {
					var state = e.data;
					/*
					-1 (unstarted)
					0 (ended)
					1 (playing)
					2 (paused)
					3 (buffering)
					5 (video cued).
					*/
					//Playing
					if(e.data === 1){
						memb_videos[v].time = memb_videos[v].player.getCurrentTime();
						youtube_video_progress(v);
					}
					else{
						clearTimeout(memb_videos[v].timer);
					}
				});
			}
		});
	}

	/**
		* Vimeo Player Ready
		*/
	function onVimeoPlayerAPIReady(){
		Object.keys(memb_videos).forEach(function(v) {
			if( memb_videos[v].type === 'vimeo' ){
				var time_tags = memb_videos[v].time_tags;
				memb_videos[v].player = new Vimeo.Player(memb_videos[v].el_id, {
					id : memb_videos[v].video_id
				});
				//Time Update
				memb_videos[v].player.on('timeupdate', function(data) {
					memb_videos[v].time = data.seconds;
					Object.keys(time_tags).forEach(function(t) {
						if (memb_videos[v].time >= time_tags[t].time ){
							maybe_timestamp_reached( v, time_tags[t].tag_id );
						}
					});
				});
			}
		});
	}

	/**
    * Map Node Info
    */
	function get_node_info(v, src_array){
		if(src_array.length){
			Object.keys(src_array).forEach(function(s) {
				var video_obj = src_array[s],
					v_src = video_obj.src,
					src_id = v_src.indexOf(memb_videos[v].video_id);
				if( v_src === memb_videos[v].src || src_id !== -1 ){
					memb_videos[v].$el = video_obj.$el;
					memb_videos[v].el_id = video_element_id(v, video_obj.$el);
				}
			});
		}
		return memb_videos[v];
	}

	/**
    * Youtube Video Progress
    */
	function youtube_video_progress(v){
		clearTimeout(memb_videos[v].timer);
		var video_type = memb_videos[v].type,
			time_tags = memb_videos[v].time_tags;
		if( time_tags && time_tags.length ){
			Object.keys(time_tags).forEach(function(t) {
				var time_tag = time_tags[t],
					dif  = time_tag.time - memb_videos[v].time;
				if (dif > 0) {
					memb_videos[v].timer = setTimeout(function() {
						maybe_timestamp_reached( v, time_tag.tag_id );
	        }, dif * 1000);
				}
				else {
					maybe_timestamp_reached( v, time_tag.tag_id );
	      }
			})
		}
	}

	/**
    * Check if time tag has already been applied
    */
	function maybe_timestamp_reached( v, tag_id ){
		//Has not been processed
		if( !memb_videos[v].finished_tag_event.includes(tag_id) ){
			video_timestamp_reached( tag_id );
			memb_videos[v].finished_tag_event.push(tag_id);
			console.log({
				video_type:memb_videos[v].type,
				video_index:v,
				tag_id:tag_id
			});
		}
	}

	/**
    * Video Timestamps
    */
   function video_timestamp_reached( tag_id ) {
		 var c_id = memberium_data.contact_id,
		 cl = c_id.toString().length,
		 tl = tag_id.length,
		 payload = 'a:2:{s:10:"contact_id";s:'+cl+':"'+c_id+'";s:6:"tag_id";s:'+tl+':"'+tag_id+'";}';
		 var mvp_progress = $.ajax({
        url				: memberium_data.ajax_url,
        method		: 'POST',
				dataType	: 'json',
        data			: {
          action  : 'memb_ajax_actions',
					payload : btoa(payload)
        },
      });
			mvp_progress.done(function( response ) {});
   }

	 /**
		 * Generate an array of Elements and their src
		 */
	 function get_src_array(){
		 var srcNodeList = document.querySelectorAll('[src]'),
		 nodeList = [],
		 video_types = [ 'VIDEO', 'IFRAME' ];
		 for (var i = 0; i < srcNodeList.length; ++i) {
			 var $item = srcNodeList[i],
			 	type = $item.nodeName,
				src = $item.getAttribute('src');
			 if( src !== null && video_types.indexOf(type) >= 0 ){
				 nodeList.push({
					 $el : $item,
					 src : src,
					 type : type
				 })
			 }
		 }
		 return nodeList;
	 }

	 /**
     * Get Or Set Element ID
		 * @params int index
		 * @params object $el
     */
	 function video_element_id( index, $el ){
		 if( !$el.hasAttribute('id') ){
			 $el.id = 'memb_video_'+index;
		 }
		 return $el.id;
	 }

	 /**
     * Get Youtube ID
     */
	 function get_YouTube_id(url){
		 url = url.split(/(vi\/|v%3D|v=|\/v\/|youtu\.be\/|\/embed\/)/);
		 return undefined !== url[2]?url[2].split(/[^0-9a-z_\-]/i)[0]:url[0];
	 }

	 /**
	 	* Vimeo videos can use one of three URL schemes:
		* https://vimeo.com/*
		* https://vimeo.com/channels/{any}*
		* https://vimeo.com/groups/{any}/videos*
		*/
	function get_vimeo_id(url) {
		var regExp = /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/;
		var match = url.match(regExp);
		return ( match[5] ) ? match[5] : false;
	};

	 /**
     * Load API Script
     */
	 function load_api_script(api, src){
		 var script   = document.createElement('script');
		 script.type  = 'text/javascript';
		 script.async = false;
		 script.src   = src;
		 document.head.append(script);
		 script.onload = function() {
			 switch (api) {
			 	case 'vimeo':
			 		onVimeoPlayerAPIReady();
			 		break;
			 	default:
			 }
		 };
	 }

})(jQuery);
