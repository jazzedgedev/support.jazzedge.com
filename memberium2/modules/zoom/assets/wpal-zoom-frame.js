'use strict';

var $doc = document,
wpalUserID = 0,
wpalZoomData = {},
wpalZoomVersion,
wpalZoomMeeting,
$wpalZoomMeetingEl;

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

    if ( typeof window.parent.wpal_zoom_data !== "undefined" ) {
        wpalZoomData = window.parent.wpal_zoom_data;
        wpalUserID = wpalZoomData.user_id;
        wpalZoomVersion = wpalZoomData.zoom_version
        if( wpalZoomData.hasOwnProperty('zoom_meeting') ){
            wpalZoomMeeting = wpalZoomMeetingShortcode(wpalZoomData.zoom_meeting);
            wpalZoomMeeting.init();
        }
    }

});

// Zoom Meeting Shortcode
var wpalZoomMeetingShortcode = function(data){

    var thisMeeting,
        eventID = data.eventID,
        apiKey = data.apiKey,
        signature = data.signature,
        role = data.role,
        leaveUrl = data.leaveUrl,
        userEmail = data.userEmail,
        userName = data.userName,
        passWord = data.hasOwnProperty('passWord') ? data.passWord : false,
        buttonId = data.buttonId,
        $startBtn = $doc.getElementById(buttonId),
        $wpalEventContainer = $doc.querySelector('.wpal-event-container');

    return {
        init : function (){

            thisMeeting = this;

            ZoomMtg.preLoadWasm();
            ZoomMtg.prepareJssdk();

            $startBtn.onclick = function(e){
                e.preventDefault();
                thisMeeting.startMeeting();
                return false;
            };

        },
        startMeeting : function(){

            //$doc.documentElement.classList.remove("wpal-zoom");
            $wpalEventContainer.parentNode.removeChild($wpalEventContainer);

            ZoomMtg.init({
              isSupportAV: true,
              leaveUrl  : leaveUrl,
              success   : function success(res) {
                ZoomMtg.join({
                  meetingNumber : eventID,
                  userName  : userName,
                  userEmail : userEmail,
                  signature : signature,
                  apiKey    : apiKey,
                  passWord  : passWord,
                  success   : function success(res) {
                    console.log({
                        func:'ZoomMtg.join',
                        respose : res,
                    });
                  },
                  error: function error(res) {
                      console.log({
                          func:'ZoomMtg.join',
                          respose : res
                      });
                  }
                });
              },
              error: function error(res) {
                  console.log({
                      func:'ZoomMtg.init',
                      respose : res
                  });
              }
            });
        }
    };
};