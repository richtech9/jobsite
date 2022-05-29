/*
SQL Schema for chat table
********************************************************************
CREATE TABLE `wp_fl_chat_rooms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `room_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `room_title` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `freelancer_id` int(11) DEFAULT NULL,
  `employer_id` int(11) DEFAULT NULL,
  `is_blocked` enum('true','false') COLLATE utf8_unicode_ci DEFAULT 'false',
  `project_type` enum('project','competition','content') COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `NewIndex1` (`room_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
********************************************************************
 */
//console.log(xmpp_helper);
//console.log(Chat.BOSH_SERVICE);
var init = true;
var rooms = [];
var chat_windows = [];

//code-notes [room flags and peek] chat flags defined
var FL_CHATROOM_FLAGS = {
    is_manually_opened: 'is_manually_opened' ,
    is_manually_closed: 'is_manually_closed' ,
    is_opened: 'is_opened',
    did_flash_room: 'did_flash_room',
    last_msg_unix_time: 'last_msg_unix_time',
    is_new_room : 'is_new_room'
};

//code-notes Create global vars for when tab has the focus, and is visible
//code-notes freelinguist_tab_has_focus will be true if the tab has focus
var freelinguist_tab_has_focus = false;

//code-notes freelinguist_tab_is_visible will be true if the tab is visible
var freelinguist_tab_is_visible = true;


function getRoomDetail(room_id) {
    for (var i in rooms) {
        if (rooms[i].room_id === room_id) {
            return rooms[i];
        }
    }
    fl_log_chat(0,null,'custom-xmpp','getRoomDetail-not found',null);
    return false;
}

//code-notes [room flags and peek] made new function to flag or un-flag rooms
function flagRoomDetail(room_id, flag,value,b_delete_flag) {

    if(b_delete_flag === undefined) {
        b_delete_flag = false;
    }

    if(value === undefined) {
        value = true;
    }

    for (var i in rooms) {
        if (rooms[i].room_id === room_id) {
            if (b_delete_flag) {
                if( rooms[i].hasOwnProperty(flag)) {
                    delete rooms[i][flag];
                    fl_log_chat(0,null,'custom-xmpp','flagRoomDetail remove flag',[room_id,flag]);
                    return
                }
            } else {
                rooms[i][flag] = value;
                fl_log_chat(0,null,'custom-xmpp','flagRoomDetail set flag',[room_id,flag,value]);
                return
            }

        }
    }

    fl_log_chat(0,null,'custom-xmpp','flagRoomDetail room flag not found',[room_id,flag]);
    return false;
}

function getRoomIndex(room_id) {
    for (var i in rooms) {
        if (rooms[i].room_id === room_id) {
            return i;
        }
    }
    fl_log_chat(0,null,'custom-xmpp','getRoomIndex-not found',null);
    return -1;
}

function updateChatWindow(room_id) {
    fl_log_chat(0,null,'custom-xmpp','updateChatWindow: start',{room_id:room_id,messages:  Chat.messages});
    if (jQuery("#" + room_id).length === 0) { //code-notes if the chat window is closed, it will not exist , so if the x is clicked, the openChatWindow will run again
        openChatWindow(room_id);
    }else{
        var room_info = getRoomDetail(room_id);
        jQuery("#" + room_id + ' .chat-messages').html("");
        var response = Chat.messages;
        let message_array = [];
        let latest_timestamp = 0;
        for (var i in response) {

            //code-notes where all the room messages are added again
            //check to see if more recent that the beginning of the last page load
            var bare_id = Strophe.getNodeFromJid(response[i].from);

            if (bare_id === room_id) {
                //console.log('response AA Update Chat Window',response[i],room_info);
                if (latest_timestamp < response[i].message_timestamp) {latest_timestamp = response[i].message_timestamp;}
                let src_data,sender_name,side_msg;
                var re = new RegExp(response[i].sender, "gi");
                if (room_info.username.match(re)) {
                    src_data = room_info.avatar; //second user
                    sender_name = room_info.nickname;
                    side_msg = 'right';

                } else {
                    src_data = xmpp_helper.login_profile_image; //self user
                    sender_name = 'me';
                    side_msg = 'left';
                }

                //code-notes changed the order of the html, icon is now inside bubble
                let msg = '<div class="message ' + side_msg + '">\n' +

                    '                <div class="bubble">\n' +
                    '                    <div class="circle"><img src="' + src_data + '"></div>\n' +
                                         response[i].message +

                    '                    <div class="corner"></div>\n' +
                    '                    <span class="small-text">' + response[i].msg_time + '</span>\n' +
                    '                </div>\n' +
                    '            </div>';
                //code-notes hook incoming message
                let new_msg = change_fl_view_link_for_unlogged(msg);
                message_array.push(new_msg);
                jQuery("#" + room_id + ' .chat-messages').append(new_msg);
            }
        }

        //code-notes [room flags and peek] set the most recent timestamp, latest_timestamp, when updating messages this room
        flagRoomDetail(room_id,FL_CHATROOM_FLAGS.last_msg_unix_time,latest_timestamp);
        fl_log_chat(0,null,'custom-xmpp','updateChatWindow',{room:room_info,messages:  message_array});
        // console.log('update chat window');
        // BeepSound(false);
        jQuery('#' + room_id + ' .chat-messages').stop()
            .animate({scrollTop: jQuery('#' + room_id + ' .chat-messages')[0].scrollHeight}, 0);
    }
    jQuery('textarea').addClass('dragon').css('width', '225px');
    
    jQuery('textarea').emojiPicker({
        width: '300px',
        height: '200px'
    });
}

function hasInputFocusChatWindow(room_id) {


    //if the tab is hidden we do not have the focus
    if (!freelinguist_tab_is_visible) {
        //console.log('hasInputFocusChatWindow is returning false because the tab is not visible');
        return false;
    }

    //if the tab itself does not have focus, we do not have the focus
    if (!freelinguist_tab_has_focus) {
        //console.log('hasInputFocusChatWindow is returning false because the tab does not have focus');
        return false;
    }

    let chatter = jQuery("#" + room_id);
    if (chatter.hasClass('freelinguist-chat-input-focus') ) {
        //console.log('hasInputFocusChatWindow is returning TRUE because the chat room id of #'+room_id+ ' has the freelinguist-chat-input-focus ');
        return true;
    }
    //console.log('hasInputFocusChatWindow is returning FALSE because the chat room id of #'+room_id+ ' does NOT have the freelinguist-chat-input-focus ');
    return false;

}

/**
 * @param {string} msg
 */
function change_fl_view_link_for_unlogged(msg) {
    //fl-view-link
    let da_msg = jQuery(msg);
    let logged_in_id = parseInt(adminAjax.logged_in_user_id);
    if (logged_in_id) { return msg; } //not changing it

    //got here, then unlogged
    let da_link = da_msg.find('a.fl-view-link');
    //does not matter if msg has no link like this, which is only found in alert messages, it will be ok to use empty
    da_link.attr("href", adminAjax.login_url);
    da_link.removeClass('fl-view-link');
    if (typeof msg === 'string' || msg instanceof String) {
        return da_msg.html();
    }
    return da_msg;
}

function flashChatWindow(room_id) {
    let chatter = jQuery("#" + room_id);
    if (chatter.length === 0) { return; }
    //get the title
    let da_title = chatter.find('div.windows-header');
    //dance!!
    da_title.effect("pulsate", { times:3 }, 2000); //use a jquery ui method

}

function openChatWindow(room_id) {
    if(chat_windows.length > 0) {
        //var first_window = chat_windows[0];
        if(jQuery('#' + room_id).index() > 2){
            //console.log('window is hidden');
            //window is hidden. needs to be brought on first position
            jQuery('.chat_boxes_area > .ejabber_chat:eq(0)').replaceWith(jQuery('#' + room_id));
            fl_log_chat(0,null,'custom-xmpp','openChatWindow:hidden',{room_id:room_id});
        }
    }
    var room_info = getRoomDetail(room_id);
    var new_message_array = [];
    if (jQuery("#" + room_id).length === 0) {
        fl_log_chat(0,null,'custom-xmpp','openChatWindow:making-chat-room',{room_id:room_id});
        var cloned = jQuery('#chatview').clone();
        //var cloned = jQuery('.ejabber_chat').clone();
        jQuery(cloned).attr('id', room_id);
        jQuery(cloned).find('.group_nickname').html(room_info.project_title.substring(0, 15));
        var msg = '';
        var src_data;
        var sender_name;
        var side_msg;
        var response = Chat.messages;
        let latest_timestamp = 0;
        for (var i in response) {


            var bare_id = Strophe.getNodeFromJid(response[i].from);

            if (bare_id === room_id) {
                //console.log('response BB Open Chat Window',response[i],room_info);
                if (latest_timestamp < response[i].message_timestamp) {latest_timestamp = response[i].message_timestamp;}
                //console.log(room_info.username,response[i].sender );
                var re = new RegExp(response[i].sender, "gi");
                if (room_info.username.match(re)) {

                     src_data = room_info.avatar; //second user
                    sender_name = room_info.nickname;
                    side_msg = 'right';

                } else {
                     src_data = xmpp_helper.login_profile_image; //self user
                    sender_name = 'me';
                    side_msg = 'left';
                }

                //code-notes changed the order of the html, icon is now inside bubble
                msg = '<div class="message ' + side_msg + '">\n' +

                    '                <div class="bubble">\n' +
                    '                   <div class="circle"><img src="' + src_data + '"></div>\n' +
                                        response[i].message +

                    '                    <div class="corner"></div>\n' +
                    '                    <span>' + response[i].msg_time + '</span>\n' +
                    '                </div>\n' +
                    '            </div>';
                //code-notes hook incoming message
                let new_msg = change_fl_view_link_for_unlogged(msg);
                jQuery(cloned).find('.chat-messages').append(new_msg);
                new_message_array.push(new_msg);

            }
        }//end for each response
        flagRoomDetail(room_id,FL_CHATROOM_FLAGS.last_msg_unix_time,latest_timestamp); //code-notes [room flags and peek] flag latest timestamp when opening window for first time
        fl_log_chat(0,null,'custom-xmpp','openChatWindow:adding-msges to new chat room window',{new_msg_array:new_message_array});
        jQuery(cloned).prependTo('.chat_boxes_area').show();

    }else{
        jQuery("#" + room_id).show();
        fl_log_chat(0,null,'custom-xmpp','openChatWindow:show',{room_id:room_id});
    }
    flagRoomDetail(room_id,FL_CHATROOM_FLAGS.is_opened); //code-notes [room flags and peek] flag window is opened
    flagRoomDetail(room_id,FL_CHATROOM_FLAGS.is_manually_closed,false);

    checkIfBlocked(room_id);
    if(room_info.readonly){
        jQuery("#" + room_id + ' .sendmessage').hide();
        fl_log_chat(0,null,'custom-xmpp','openChatWindow:readonly,hide',{room_id:room_id});
    }
	
	var lg = jQuery('.ejabber_chat').length;
		
	
	if(lg > 1){
		
		/* if (jQuery('.ejabber_chat').css('display') == 'none') {
			 jQuery('.ejabber_chat').show();
		}  */
		// jQuery('.close_window3').click();
		jQuery('.chat_boxes_area .ejabber_chat').addClass("chatbox_new");
		jQuery('.chat_boxes_area').addClass("chatbox_new");
	}else{
		jQuery('.chat_boxes_area .ejabber_chat').removeClass("chatbox_new");
		jQuery('.chat_boxes_area').removeClass("chatbox_new");
	}
    
	jQuery('textarea').addClass('dragon').css('width', '225px');
    
    jQuery('textarea').emojiPicker({
        width: '300px',
        height: '200px',
        top: '220px'
    });

}

function checkIfBlocked(room_id){
    var room_index = getRoomIndex(room_id);
    if(rooms[room_index].isBlocked == 'true'){

        jQuery('#' + room_id).find('.txtMessage').attr('placeholder', 'This chat is blocked')
            .prop('disabled', true);
        jQuery('#' + room_id).find('.settings > .block_info > p > input').prop('checked', true);
        fl_log_chat(0,null,'custom-xmpp','checkIfBlocked:disabling',{room_id:room_id});
    }else{
        jQuery('#' + room_id).find('.txtMessage').attr('placeholder', 'Send Message...')
            .prop('disabled', false);
        jQuery('#' + room_id).find('.settings > .block_info > p > input').prop('checked', false);
        fl_log_chat(0,null,'custom-xmpp','checkIfBlocked:enabling',{room_id:room_id});
    }
}

Chat.setOnMessageLoaded(function(room_id) {
    fl_log_chat(0,null,'custom-xmpp','setOnMessageLoaded',{room_id: room_id});
    if(chat_windows.indexOf(room_id) > -1) {
        //console.log("room id ", room_id , ' needs update');
        updateChatWindow(room_id);
    }
});
Chat.setOnDisConnected(function(){
    fl_log_chat(0,null,'custom-xmpp','setOnDisConnected',null);
   // debugger; //code-notes restart the chat app by refreshing the page!!
    let this_page_no_hash = window.location.href.split('#')[0];
    window.location = this_page_no_hash;
    //jQuery('#chat_disconnected').modal(); //code-notes uncomment to show dialog
});
Chat.setOnConnected(function () {
    jQuery.ajax({
        type: "post",
        dataType: "json",
        url: adminAjax.url,
        data: {action: 'get_room_user'},
        success: function (response) {
            //console.log(response);
            fl_log_chat(1,null,'custom-xmpp','setOnConnected::get_room_user',response);
            if (response.length > 0) {
                var contact_div = jQuery('#friends');
                contact_div.html('');
                for (var i in response) {
                    var currentuser = response[i];
                    //  console.log(currentuser);
                    var room_id = currentuser.room_id;
                    var tmp_room = {
                        'project_title': currentuser.project_title.substring(0,20),
                        'username': currentuser.username,
                        'nickname': currentuser.nickname,
                        'avatar': currentuser.avatar,
                        'room_id': room_id,
                        'isBlocked': currentuser.isBlocked === 'false' ? false: true,
                        'readonly' : currentuser.readonly

                    };
                    rooms.push(tmp_room);

                        joinRoom(tmp_room);

                }
            } else {
                //alert(response.message);
            }

            check_for_new_rooms(0);
        }
    });
});
function joinRoom(_room){
    fl_log_chat(0,null,'custom-xmpp','joinRoom',_room);
    if(_room.readonly !== 1)
        Chat.mucJoin(_room.room_id + '@' + xmpp_helper.conference_domain);

    var contact_div = jQuery('#friends');
    var content = jQuery('<div class="friend" ' +
                                    'data-nickname="' + _room.nickname +
                                    '" data-roomid="' + _room.room_id +
                                    '" data-room="' + _room.room_id + '@'+ xmpp_helper.conference_domain+'">\n' +
        '                    <div class="circle"><img class="avtar_' + _room.room_id + '" src="' + _room.avatar + '"></div> \n' +
        '                    <p>\n' +
        '                        <strong class="enhanced-text">' + _room.nickname + '</strong>\n' +
        '                        <br>\n' +
        '                        <span>' + _room.project_title.substring(0,20) + '</span>\n' +
        '                    </p>\n' +
        '                    <div id="status_' + _room.room_id + '" class="status inactive"></div>\n' +
        '                </div>');
    jQuery(contact_div).append(content);
}
Chat.setOnAnnouncement(function (to, room_id, elems, fron_send, visibleTime) {
    //console.log('Announcement EE',to, room_id, elems, fron_send, visibleTime);
    fl_log_chat(0,null,'custom-xmpp','setOnAnnouncement',{to:to, room_id:room_id, elems:elems, fron_send:fron_send, visibleTime:visibleTime});
   // var room_id = Strophe.getNodeFromJid(from);
    var room_info = getRoomDetail(room_id);
    var src_data;
    var sender_name;
    var side_msg;
    var msg;
    if (jQuery("#" + room_id).length === 0) {

        if(chat_windows.indexOf(room_id) === -1) {
            chat_windows.push(room_id);
            Cookies.set('chat_windows', chat_windows);
        }
        //console.log('on announcement new window');
        BeepSound(true);
        openChatWindow(room_id);
        flashChatWindow(room_id); //code-notes flash system chat window immediately after creating it

    } else {
        if(jQuery('#' + room_id).index() > 2){
            jQuery('#status_' + room_id).parent().addClass('quadrat');
            jQuery('#chatbox .windows-header').addClass('quadrat');
        }
        checkIfBlocked(room_id);
        //console.log(room_info);
        // console.log(fron_send, room_info.user);

        src_data = room_info.avatar;
        sender_name = room_info.nickname;
        side_msg = 'right';
        //console.log('on announcement');
        BeepSound(true);
        //code-notes put icon inside chat bubble
        msg = '<div class="message ' + side_msg + '">\n' +

            '                <div class="bubble">\n' +
            '                <div class="circle"> <img src="' + src_data + '"></div>\n' +
                                elems +
            '                    <div class="corner"></div>\n' +
            '                    <span>' + visibleTime + '</span>\n' +
            '                </div>\n' +
            '            </div>';
        //code-notes hook incoming message
        let new_msg = change_fl_view_link_for_unlogged(msg);
        jQuery('#' + room_id + ' .chat-messages').append(new_msg);
        fl_log_chat(0,null,'custom-xmpp','setOnAnnouncement:adding-msg',{msg:msg,new_msg:new_msg});
        flashChatWindow(room_id); //code-notes flash system chat window when its opened and we are receiving a message
    }
    jQuery('#' + room_id + ' .chat-messages').stop().animate({scrollTop: jQuery('#' + room_id + ' .chat-messages')[0].scrollHeight}, 0);   
});


Chat.setOnPeekMessage(function (to, from, elems, fron_send, visibleTime,messageInfo) {

    var room_id = Strophe.getNodeFromJid(from);
    var room_info = getRoomDetail(room_id);
    //console.log('OnPeekMessage ', {to:to, from: from, elems:elems, fron_send: fron_send,
    //    visibleTime: visibleTime,room_id: room_id,message: messageInfo,room_info:room_info});
    //code-notes [room flags and peek] here the custom level sees if this message belongs to a new room, if so we show and update the chat window
    if (room_info.hasOwnProperty( FL_CHATROOM_FLAGS.is_new_room)) {
        if (room_info.is_new_room) {
            //console.log ("updating chat window because of new room flag");
            if(chat_windows.indexOf(room_id) === -1) {
                chat_windows.push(room_id);
                Cookies.set('chat_windows', chat_windows);
            }
            //flagRoomDetail(room_id,FL_CHATROOM_FLAGS.is_new_room,false);
            updateChatWindow(room_id);
        }
    }

    /*
       code-notes [room flags and peek] Logic to flash once for new messages in opened windows

        First we set up some flags and values for the logic to work with in the peek function
        1) When the user clicks on a room to open it, a flag is set to show that it is manually opened, its called is_manually_opened
        2) When a chat window is opened for any reason, a flag is set to show the room is opened, its called is_opened, the flag is_manually_closed is set to false
        3) When the user closes a chat room, the three flags are unset (is_manually_opened,is_opened,did_flash_room) and the flag is_manually_closed is added
        4) When a room is opened, or re-opened, a flag is set to record the last message time, in terms of a unix timestamp, for that room, This flag is called last_msg_unix_time

        During the logic, we are going to use a flag called did_flash_room, to show if we flashed a room

        Logic using the above In the peek message
        5) IF the peek message has the following conditions then we flash the room
                a) is for a room that is open
                b) the flag for did_flash_room is not set for that room
                c) and the new incoming message unix timestamp is greater than than the room's last_msg_unix_time
                d) and its not our own message

           THEN
                d) we flash and beep
                e) set the flag did_flash_room for that room









     */
    //if a room does not have the flag of has_incoming_message then beep, and add the flag
    if (!room_info.hasOwnProperty( FL_CHATROOM_FLAGS.is_opened)) { return; }
    if (!room_info[FL_CHATROOM_FLAGS.is_opened]) { return; }

    if (room_info.hasOwnProperty( FL_CHATROOM_FLAGS.did_flash_room)) {
        if (room_info[FL_CHATROOM_FLAGS.did_flash_room]) { return; }
    }

    let our_username = xmpp_helper.username.split('@')[0];
    if (messageInfo.sender === our_username) {
        return;
    }

    if (messageInfo.message_timestamp >= room_info[FL_CHATROOM_FLAGS.last_msg_unix_time]) {

        let has_input_focus = hasInputFocusChatWindow(room_id);
        if (has_input_focus) {
            //console.log('chatroom of '+ room_id + ' HAS the special class, so will be quiet ...');
        } else {
            //console.log('chatroom of '+ room_id + ' does not have the special class, so go to town and make noise!');
            BeepSound(false);
            flashChatWindow(room_id); //code-notes flash chat window when its opened and we are receiving the first new message for the room
        }


        flagRoomDetail(room_id,FL_CHATROOM_FLAGS.did_flash_room,true);
        //console.log('did flash',room_info);
    }



});


Chat.setOnReceivedGroupMessage(function (to, from, elems, fron_send, visibleTime) {
    //console.log('recieved group message  DD', to, from, elems, fron_send, visibleTime);
    fl_log_chat(1,null,'custom-xmpp','setOnReceivedGroupMessage',
                        {to:to, from:from, elems:elems, fron_send:fron_send, visibleTime:visibleTime});
    var room_id = Strophe.getNodeFromJid(from);
    var room_info = getRoomDetail(room_id);
    var src_data;
    var sender_name;
    var side_msg;
    var msg;

    let our_username = xmpp_helper.username.split('@')[0];
    let from_username_parts = from.split('/');
    let from_username = from_username_parts[from_username_parts.length-1];

    let is_our_own_message = (our_username === from_username);
    // if (is_our_own_message) {
    //     console.log('is own message');
    // } else {
    //     console.log('is someone else\'s message');
    // }

    if (jQuery("#" + room_id).length === 0) {

        //code-notes [room flags and peek] do not open if room manually closed and this message is from us
        if (room_info.is_manually_closed && is_our_own_message) {
            console.log("not opening window because its our own message and we closed the window");
            return;
        }
        if(chat_windows.indexOf(room_id) === -1) {
            chat_windows.push(room_id);
            Cookies.set('chat_windows', chat_windows);
        }
            openChatWindow(room_id);


    } else {
        if(jQuery('#' + room_id).index() > 2){
            jQuery('#status_' + room_id).parent().addClass('quadrat');
            jQuery('#chatbox .windows-header').addClass('quadrat');
        }
        checkIfBlocked(room_id);
        //console.log(room_info);
       // console.log(fron_send, room_info.user);
        var re = new RegExp(fron_send, "gi");
        if (room_info.username.match(re)) {

            src_data = room_info.avatar;
            sender_name = room_info.nickname;
            side_msg = 'right';
           //code-notes we are now flashing messages in the peek callback
        } else {
            src_data = xmpp_helper.login_profile_image;
            sender_name = 'me';
            side_msg = 'left';
        }

        //code-notes put icon inside of chat bubble
        msg = '<div class="message ' + side_msg + '">\n' +

            '                <div class="bubble">\n' +
            '                <div class="circle"> <img src="' + src_data + '"></div>\n' +
                                elems +
            '                    <div class="corner"></div>\n' +
            '                    <span>' + visibleTime + '</span>\n' +
            '                </div>\n' +
            '            </div>';
        //code-notes hook incoming message
        let new_msg = change_fl_view_link_for_unlogged(msg);
        fl_log_chat(0,null,'custom-xmpp','setOnReceivedGroupMessage:adding-msg',{msg:msg,new_msg:new_msg});
        jQuery('#' + room_id + ' .chat-messages').append(new_msg);

    }
    jQuery('#' + room_id + ' .chat-messages').stop().animate({scrollTop: jQuery('#' + room_id + ' .chat-messages')[0].scrollHeight}, 0);
});

Chat.setOnPresence(function (room_name, participant_name, role, affiliation, status_codes, type, error_code) {
   // console.log(room_name, participant_name, role, affiliation, status_codes, Strophe.getNodeFromJid(xmpp_helper.username));

    if(participant_name !== Strophe.getNodeFromJid(xmpp_helper.username)){
        if(type !== 'unavailable') {
            jQuery('#status_' + room_name).removeClass('inactive')
                .addClass('available');
        }else{
            jQuery('#status_' + room_name).removeClass('available')
                .addClass('inactive');
        }
    }
    var room_index = getRoomIndex(room_name);
    if(type === 'error'){
        if(error_code === '403'){

            rooms[room_index].isBlocked = 'true';
            console.log("user banned", participant_name, room_name);
        }
    }
    //console.log(status_codes.indexOf('110'));
    //console.log(status_codes.indexOf('301'));
    if(status_codes.indexOf('110') > -1){
        //console.log('self presence');
        if( status_codes.indexOf('301') > -1 ) {
            console.log('you are blocked');
            rooms[room_index].isBlocked = 'true';
            checkIfBlocked(room_name);
        }
    }else if( status_codes.indexOf('301') > -1 ) {
        console.log('blocked', participant_name);
    }

});
function BeepSound(is_system) {

    if (!fl_user_interacted_with_window) {
        //console.log('user did not interact with window first, cannot beep');
        return;
    }
    is_system = !!is_system;
    if((Cookies.get('chatBellStatus') === undefined) || (Cookies.get('chatBellStatus') === 'on') ){
        let alert_url;
        if (is_system) {
            alert_url = xmpp_helper.alert_sound_system;
        } else {
            alert_url = xmpp_helper.alert_sound;
        }
        var audio = new Audio(alert_url);
        // code-notes changed sound to use local, was "https://www.f-cdn.com/assets/main/en/assets/sounds/message.mp3"
        //console.log("I beeped",alert_url);
        audio.play();
    }
}
function removeMainChatBlink(){
    var hasMore = false;
    jQuery('.friend').each(function(){
        if(jQuery(this).hasClass('quadrat')){
            hasMore = true;
        }
    });
    if(!hasMore){
        jQuery('#chatbox .windows-header').removeClass('quadrat');
    }
}
jQuery(document).on('click', '.friend', function () { //code-bookmark when  clicking room name in master list
   // console.log('click room');
   // var room_name = jQuery(this).attr('data-room');
    var room_id = jQuery(this).attr('data-roomid');
    if(jQuery(this).hasClass('quadrat')){
        jQuery(this).removeClass('quadrat');
        removeMainChatBlink();
    }
    //console.log(chat_windows);
    if(chat_windows.indexOf(room_id) === -1) {
        chat_windows.push(room_id);
        Cookies.set('chat_windows', chat_windows);
    }
    //code-notes [room flags and peek] add flag is_manually_opened for manual window open
    flagRoomDetail(room_id,FL_CHATROOM_FLAGS.is_manually_opened);
    openChatWindow(room_id);
    //add code to scroll down when window opened
    let chat_messages = jQuery('#' + room_id + ' .chat-messages');
    if (chat_messages.length) {
        chat_messages.stop().animate({scrollTop: chat_messages[0].scrollHeight}, 0);
    }
	
//    jQuery('#' + room_id + ' .chat-messages').stop().animate({scrollTop: jQuery('#' + room_id + ' .chat-messages')[0].scrollHeight}, 0);
	
	jQuery('.chat_boxes_area .ejabber_chat').addClass("chatbox_new");
	jQuery('.chat_boxes_area').addClass("chatbox_new");

});
jQuery(document).on('keydown', '.txtMessage', function (e) {
    if( !Chat.connected ) return;
    if (e.keyCode === 13) {
       // console.log('press');
        var body = jQuery(this).val();
        jQuery(this).val('');
        var obj = jQuery(this).parent().siblings('.chat-messages');
        var jid = jQuery(this).parents('.ejabber_chat').attr('id') + '@conference.' + xmpp_helper.xmpp_host;
        if (body !== '') {
            var messgeTo = jQuery(this).parents('.ejabber_chat').attr('data-nameto');
            Chat.sendMessage(jid, body, 'groupchat');

        }
    }

});
jQuery(document).on('click', '.send', function () {
    if( !Chat.connected ) return;
    var body = jQuery(this).siblings().val();
    var obj = jQuery(this).parent().siblings('.chat-messages');
    var jid = jQuery(this).parents('.ejabber_chat').attr('id') + '@conference.' + xmpp_helper.xmpp_host;
    if (body !== '') {
        var messgeTo = jQuery(this).parents('.ejabber_chat').attr('data-nameto');
        Chat.sendMessage(jid, body, 'groupchat');
        jQuery(this).siblings().val('');

    } else {
        //var jid = jQuery(this).parents('.ejabber_chat').attr('id')+'@conference.' + xmpp_helper.xmpp_host;
        //Chat.sendChatState(jid,'composing','groupchat');
    }
});
jQuery(document).on('click', '.close_window3', function () {
    //code-bookmark this is where the close chat window is called when the user clicks to close
//    console.log('remove ejabber chat');
    var room_id = jQuery(this).parent().parent().attr('id');
    if(room_id !== ''){

        var window_index = chat_windows.indexOf(room_id);
        if(window_index > -1) {
            chat_windows.splice(window_index, 1);
            Cookies.set('chat_windows', chat_windows);
        }
        //console.log("remoing window ", room_id, window_index);
        //code-notes [room flags and peek] clear room flags when the room is closed
        flagRoomDetail(room_id,FL_CHATROOM_FLAGS.is_opened,false);
        flagRoomDetail(room_id,FL_CHATROOM_FLAGS.is_manually_opened,false);
        flagRoomDetail(room_id,FL_CHATROOM_FLAGS.did_flash_room,false);
        flagRoomDetail(room_id,FL_CHATROOM_FLAGS.is_manually_closed,true);
    }

    jQuery(this).parent().parent().remove();
    var first_visible_room = jQuery('.ejabber_chat:eq(0)').attr('id');
    var second_visible_room = jQuery('.ejabber_chat:eq(1)').attr('id');
    var third_visible_room = jQuery('.ejabber_chat:eq(2)').attr('id');
    jQuery('#status_' + first_visible_room).parent().removeClass('quadrat');
    jQuery('#status_' + second_visible_room).parent().removeClass('quadrat');
    jQuery('#status_' + third_visible_room).parent().removeClass('quadrat');
	
	var lg = jQuery('.ejabber_chat').length;
	//console.log("lg is "+lg);
	if(lg === 1){
		jQuery('.chat_boxes_area .ejabber_chat').removeClass("chatbox_new");
		jQuery('.chat_boxes_area').removeClass("chatbox_new");
	}
	

});
jQuery(document).on('click', '.chat_settings', function () {
    jQuery(this).parent().siblings('.chat_settings_window').toggleClass('hidden');
});
//chat box js

jQuery(function ($) {
    //code-bookmark Here the chat system starts on the web page
    fl_log_chat(0,null,'custom-xmpp','init',null);
    Chat.connect(xmpp_helper.username, xmpp_helper.password, xmpp_helper.bosh_service_url, false);
    if(Cookies.get('chatWindowStatus') === 'minimized'){
        var boj = jQuery('#close_window').parent().parent();
        if (boj.height() >= 300) {
            boj.css({'height': 44});
            Cookies.set('chatWindowStatus', 'minimized')

        }
    }
    if((Cookies.get('chatBellStatus') === undefined) || (Cookies.get('chatBellStatus') === 'on') ){
        jQuery('#material-icons-notification').html('notifications');
    }else{
        jQuery('#material-icons-notification').html('notifications_off');
    }

    /****
     * Restoring opened chat windows
     */
    var _chat_cookies = Cookies.get('chat_windows');
    if( _chat_cookies) {
        var temp = JSON.parse(_chat_cookies);
        //var temp =  Cookies.get('chat_windows') ;
        //console.log(temp);
        if (temp.length > 0) {
            chat_windows = temp;

        } else {
            chat_windows = [];
        }
    }else{
        chat_windows = [];
    }

    //code-bookmark the handler for the remove button on the announcement, which is set in the php ajax
    jQuery(document).on("click", ".close_window2", function () {
        //jQuery(this).parent().addClass("bottom_zero");
        var boj = jQuery(this).parent().parent();
        //console.log(boj);
        if (boj.height() >= 484) {
            boj.css({'height': 0});
            //boj.animate({ height: "40px" });
            jQuery(this).parent().addClass("bottom_zero");
        }
        else if (boj.height() == 0) {
            boj.css({'height': 484});
            //boj.animate({ height: "484px" });
            jQuery(this).parent().removeClass("bottom_zero");
        }
    });
    jQuery(document).on("click", "#close_window", function () {
        //jQuery(this).parent().addClass("bottom_zero");
        var boj = jQuery(this).parent().parent();
        if (boj.height() >= 300) {
            boj.css({'height': 44});
            Cookies.set('chatWindowStatus', 'minimized')

        }
        else if (boj.height() <= 44) {
            boj.css({'height': 300});
            Cookies.set('chatWindowStatus', 'maximized')

        }
    });

    /*
        code-notes without cookie, the bell will now be on. So, clicking this without a cookie will make a new cookie with value of off
     */
    jQuery(document).on("click", "#bell_notification", function () {
        //jQuery(this).parent().addClass("bottom_zero");

         if((Cookies.get('chatBellStatus') === 'on') || (Cookies.get('chatBellStatus') === undefined)){
            Cookies.set('chatBellStatus', 'off',{ expires: 365 });
            jQuery('#material-icons-notification').html('notifications_off');
        }else{
            Cookies.set('chatBellStatus', 'on',{ expires: 365 });
            jQuery('#material-icons-notification').html('notifications');
        }

    });


    jQuery(document).on("click", '.block_info > p > input', function() {
       //alert(jQuery(this).prop('checked'));
        var room_id = jQuery(this).parents('.ejabber_chat').attr('id');
        var room_info = getRoomDetail(room_id);
        var room_index = getRoomIndex(room_id);
        var room_address =  room_id + '@conference.' + xmpp_helper.xmpp_host;
        var jid =  room_info.nickname + '@' + xmpp_helper.xmpp_host;
        if(jQuery(this).prop('checked') === true) {

            Chat.connection.muc.ban(room_address,jid, "none",
                function(resp){
                    updateBlockStatus(room_id, 'true');
                    rooms[room_index].isBlocked = 'true';
                    checkIfBlocked(room_id);
                    console.log('Block success', resp);
                },
                function(resp){
                    console.log("Block failed", resp);
                });
        }
        else{

            Chat.connection.muc.member(room_address,jid, "none",
                function(resp){
                    updateBlockStatus(room_id, 'false');
                    rooms[room_index].isBlocked = 'false';
                    checkIfBlocked(room_id);
                    console.log('UnBlock success', resp);
                },
                function(resp){
                    console.log("UnBlock failed", resp);
                });
        }


    });




    //code-notes add a class  freelinguist-chat-input-focus to the outer chatroom div if any of its children are clicke; remove any other freelinguist-chat-input-focus
    jQuery("body").click(function(e) {
        let that = jQuery(e.target);
        let top_chat_div = null;
        if (that.is('div') && that.hasClass('ejabber_chat')) {
            top_chat_div = that;
        } else if(that.closest('div.ejabber_chat').length) {
            top_chat_div = that.closest('div.ejabber_chat');
        }
        if (that.hasClass('freelinguist-chat-input-focus') || that.closest('div.freelinguist-chat-input-focus').length) {
            //console.debug("clicked inside a focused chat area");
        } else {
            //remove all existing freelinguist-chat-input-focus
            jQuery('div.ejabber_chat').removeClass('freelinguist-chat-input-focus');
            //console.debug("So removing any freelinguist-chat-input-focus");
            if (top_chat_div) {
                //console.debug("adding focus class of  freelinguist-chat-input-focus");
                top_chat_div.addClass('freelinguist-chat-input-focus');
            }
        }
    });

    //code-notes we need to know when the tab is focused, blurred, hidden, shown
    $(window).focus(function () {
        freelinguist_tab_has_focus = true;
        //console.log("The tab has become focused, freelinguist_tab_has_focus is set to true");
    });

    $(window).blur(function () {
        freelinguist_tab_has_focus = false;
        //console.log("The tab has become blurred, freelinguist_tab_has_focus is set to false");
    });

    // Set the name of the hidden property and the change event for visibility
    let hidden, visibilityChange;
    if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support
        hidden = "hidden";
        visibilityChange = "visibilitychange";
    } else if (typeof document.msHidden !== "undefined") {
        hidden = "msHidden";
        visibilityChange = "msvisibilitychange";
    } else if (typeof document.webkitHidden !== "undefined") {
        hidden = "webkitHidden";
        visibilityChange = "webkitvisibilitychange";
    }

    function handleVisibilityChange() {
        if (document[hidden]) {
            freelinguist_tab_is_visible = false;
            //console.log("The tab has become hidden,freelinguist_tab_is_visible is set to false");
        } else {
            freelinguist_tab_is_visible = true;
            //console.log("The tab has become shown, freelinguist_tab_is_visible is set to true");
        }
    }

    document.addEventListener(visibilityChange, handleVisibilityChange, false);


});

function updateBlockStatus(room_id, status){
    jQuery.ajax({
        type: "post",
        dataType: "json",
        url: adminAjax.url,
        data: {action: 'update_chat_block_status', 'room_id': room_id,'status': status},
        success: function (response) {

        }
    });
}
function show_chat_room(that,room_string_identifier,project_id,freelancer_id, project_type, prefix='')
{
    fl_log_chat(0,null,'custom-xmpp','show_chat_room', {
        room_string_identifier:room_string_identifier,project_id:project_id,
        freelancer_id:freelancer_id, project_type:project_type,prefix:prefix
    });

    var room_index = -1;
    if (room_string_identifier) {
        room_index = getRoomIndex(room_string_identifier);
    }


    if(room_index < 0) {

        let da_button = jQuery(that);
        var data = {
            action: 'create_chat_room',
            project_id: project_id,
            freelancer_id: freelancer_id,
            project_type: project_type,
            proposal_id: da_button.data('proposal_id') ,
            fl_job_id: da_button.data('fl_job_id')
        };

        jQuery.post(adminAjax.url, data, function (response_raw) {
            fl_log_chat(1,null,'custom-xmpp','show_chat_room:created',response_raw);

            /**
             * @type {FreelinguistCreateChatroomResponse} response
             */
            let response = freelinguist_safe_cast_to_object(response_raw);
            if (response.status ) {
                let new_room_string_identifier = response.data.room_string_identifier;
                rooms.push(response.data);
                joinRoom(response.data);
                if(chat_windows.indexOf(response.data.room_string_identifier) === -1) {
                    chat_windows.push(response.data.room_string_identifier);
                    Cookies.set('chat_windows', chat_windows);
                }
                openChatWindow(new_room_string_identifier);
                jQuery('#' + new_room_string_identifier + ' .chat-messages').stop().
                    animate({scrollTop: jQuery('#' + new_room_string_identifier + ' .chat-messages')[0].scrollHeight}, 0);

            } else {
                will_handle_ajax_error('Opening Chat Room',response.message);
            }

        });
    }else{


        if(chat_windows.indexOf(room_string_identifier) === -1) {
            chat_windows.push(room_string_identifier);
            Cookies.set('chat_windows', chat_windows);
        }
        openChatWindow(room_string_identifier);
        jQuery('#' + room_string_identifier + ' .chat-messages').stop().
            animate({scrollTop: jQuery('#' + room_string_identifier + ' .chat-messages')[0].scrollHeight}, 0);
    }
	
	var lg = jQuery('.ejabber_chat').length;
	if(lg > 0){
		// jQuery('.close_window3').click();
		jQuery('.chat_boxes_area .ejabber_chat').addClass("chatbox_new");
		jQuery('.chat_boxes_area').addClass("chatbox_new");
	}else{
		jQuery('.chat_boxes_area .ejabber_chat').removeClass("chatbox_new");
		jQuery('.chat_boxes_area').removeClass("chatbox_new");
	}
    // jQuery('textarea').addClass('dragon').css('width', '225px');
    // jQuery('textarea').emojiPicker({
    //     width: '300px',
    //     height: '200px'
    // });
}

//code-notes currently not used anywhere, but useful
// noinspection JSUnusedGlobalSymbols
function test_broadcast(){
    jQuery.ajax({
        type: "post",
        dataType: "json",
        url: adminAjax.url,
        data: {action: 'send_broadcast'},
        success: function (response) {
            console.log(response);
        }
    });
}
function check_for_new_rooms(duration){
    //code-notes change this to not fire at all for unlogged in users, and put in a new timer algorithm
    if (! parseInt(xmpp_helper.logged_in_id.toString())) {return;}

    let start_duration = 10000;
    let max_duration=320000; //320sec longest

    if (!duration) {
        duration = start_duration;
    }

    setTimeout(function(){
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: adminAjax.url,
            data: {action: 'get_room_user'},
            success: function (response) {
                fl_log_chat(1,null,'custom-xmpp','check_for_new_rooms:get_room_user',response);
                let number_new_rooms = 0;
               if( response.length > 0){
                   for (let i in response) {
                       let currentuser = response[i];
                       currentuser.is_new_room = true; //code-notes [room flags and peek] here we flag the new room so we can popup its window when we get a message
                       //  console.log(currentuser);
                       let room_id = currentuser.room_id;
                       if( getRoomIndex(room_id) < 0){
                           number_new_rooms++;
                           rooms.push(currentuser);
                           joinRoom(currentuser);
                       } //end if room not added yet
                   }//end for loop (rooms in response)
               } //end if response.length

                if (number_new_rooms) {
                   duration = start_duration;
                } else {
                   duration = Math.min(2*duration, max_duration);
                }

               check_for_new_rooms(duration);
            }});
    }, duration)
}