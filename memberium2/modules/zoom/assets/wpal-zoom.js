'use strict';

// Embedded Frame
var $doc = document,
$zoomFrame = false;

// Document ready
var wpalZoomReady = function wpalZoomReady(callBack) {
    if ($doc.readyState !== 'loading') {
        callBack();
    }
    else if ($doc.addEventListener) {
        $doc.addEventListener('DOMContentLoaded', callBack);
    }
    else {
        $doc.attachEvent('onreadystatechange', function() {
            if ($doc.readyState === 'complete') {
                callBack();
            }
        });
    }
};

// Dom Ready
wpalZoomReady(function() {
    if ( typeof wpal_zoom_data !== "undefined" ) {
        var meeting = wpal_zoom_data.zoom_meeting,
        frameId = meeting.frameId;
        $zoomFrame = frameId ? $doc.getElementById(frameId) : false;
        if( $zoomFrame ){
            var src = $zoomFrame.getAttribute('data-src');
            $zoomFrame.removeAttribute('data-src');
            $zoomFrame.setAttribute('src', src);
        }
    }
});