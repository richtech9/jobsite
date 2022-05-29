var Chat = {
    BOSH_SERVICE: xmpp_helper.bosh_service_url,
    connection: null,
    connected: false,
    announcementMessages : [],
    debuggingMode: true,
    onConnected: function() {},
    onDisConnected: function() {},
    onJoinroom:function(){},
    onPresence:function(){},
    onUserTyping: function(){},
    onMessageLoaded: function() {},
    onSetUserTypingStop(){},
    onAnnouncement(){},
    onReceivedGroupMessage:function(){},
    onPeekMessage:function(){},
    setOnPeekMessage: function (cb) { Chat.onPeekMessage = cb; },
    setOnConnected: function (cb) { Chat.onConnected = cb; },
    setOnMessageLoaded: function (cb) { Chat.onMessageLoaded = cb; },
    setOnDisConnected: function (cb) { Chat.onDisConnected = cb; },
    setOnJoinroom:function(cb){ Chat.onJoinroom = cb;},
    setOnReceivedGroupMessage:function(cb){ Chat.onReceivedGroupMessage = cb;},
    setOnPresence:function(cb){ Chat.onPresence = cb;},
    setOnUserTyping:function(cb){ Chat.onUserTyping = cb;},
    setOnUserTypingStop:function(cb){ Chat.onSetUserTypingStop = cb;},
    setOnAnnouncement:function(cb){ Chat.onAnnouncement = cb;},
    connect: function (jid, password, BOSH_SERVICE, debugginMode) {
        //alert(password);
        Chat.BOSH_SERVICE = (BOSH_SERVICE) ? BOSH_SERVICE : Chat.BOSH_SERVICE;
        Chat.debuggingMode = (debugginMode) ? debugginMode : false; //set to false after done testing!!
            Chat.connection = new Strophe.Connection(Chat.BOSH_SERVICE);



        Chat.connection.flush();
        Chat.connection.disconnect();
        Chat.connected = false;
        fl_log_chat(0,null,'middle-chat',"disconnect",null);
        Chat.connection.connect(jid, password, Chat.onConnect);

    },
    disconnect: function () {
    },
    onConnect: function (status) {
       // debugger;
        fl_log_chat(0,null,'middle-chat',"onConnect",status);
        let authencation_failure_handler = null;
        if (status === Strophe.Status.CONNECTING) {
            Chat.log('Strophe is connecting.');
        }
        else if (status === Strophe.Status.AUTHENTICATING) {
            //code-notes where I put in hook for auth fail
            Chat.log('Strophe is authencating');
            authencation_failure_handler = Chat.connection._addSysHandler(function(msg){
                if (typeof freelinguist_refresh_chat_credentials === "function") {
                    // safe to use the function
                    freelinguist_refresh_chat_credentials(msg);
                }
                //remove handler, it will be re-added when we reboot chat, and there is another authentication fail
                Chat.connection.deleteHandler(authencation_failure_handler);
            },null,'failure',null,null);
        }
        else if (status === Strophe.Status.AUTHFAIL) {
            Chat.log('Strophe had an authencate fail');
        }
        else if (status === Strophe.Status.CONNFAIL) {
            Chat.log('Strophe failed to connect.');
        }
        else if (status === Strophe.Status.DISCONNECTING) {
            Chat.log('Strophe is disconnecting.');
            Chat.connected = false;
        }
        else if (status === Strophe.Status.DISCONNECTED) {
            Chat.log('Strophe is disconnected.');
            Chat.onDisConnected();
            Chat.connected = false;
        }
        else if (status === Strophe.Status.CONNECTED) {
            Chat.log('Strophe is connected.');
            Chat.onConnected();
            Chat.connected = true;
            Chat.sendPresence();
            Chat.connection.addHandler(Chat.receiveMessage, null, 'message', 'groupchat');
            Chat.connection.addHandler(function(msg){
                fl_log_chat(0,null,'middle-chat',"connection.addHandler AA",msg);
                var body = jQuery(msg).find('body').text();
                //var timestamp = jQuery(msg).find('delay').attr('stamp');

                var to = msg.getAttribute('to');
                var from = msg.getAttribute('from');
                var type = msg.getAttribute('type');
                var timestamp = jQuery(msg).find('delay').attr('stamp');
                var visibleTime = null;
                let da_date_object;

                //code-notes [room flags and peek] adding in unix timestamp for each message
                if( timestamp !== undefined) {
                    visibleTime = Chat._formatTimeStamp(timestamp);
                    da_date_object = new Date(timestamp);
                }
                else{
                    // var date = new Date();
                    //console.log(date.toISOString());
                    visibleTime = 'just now';
                    da_date_object = new Date();
                }

                var messageInfo = {
                    'to':to,
                    'from': 'project_alerts@'+from,
                    'sender': 'System',
                    'type': type,
                    'msg_time':visibleTime,
                    'message_raw_datetime': timestamp,
                    'message_date_object': da_date_object,
                    'message_timestamp' : Math.floor(da_date_object.getTime()/1000),
                    'message': body
                };
                //console.log("announcement received", messageInfo);
                fl_log_chat(0,null,'middle-chat',"AA pushing message", messageInfo);
                Chat.messages.push(messageInfo); //code-notes only updated on chat session start
                Chat.onAnnouncement(to,'project_alerts',body,'System',visibleTime);
                return true;
            }, null, 'message', 'headline');
            //Add ping handler ~noy working...
            Chat.connection.ping.addPingHandler(Chat.receivePing);
            //getRoster from server
           // var iq = $iq({type: 'get'}).c('query', {xmlns: 'jabber:iq:roster'});
           // Chat.connection.sendIQ(iq, Chat.rosterReceived);
            //GetPubSub Nodes
            Chat.discoverNodes();
            //getNodes user is subscribed to
            Chat.getSubscriptions();
        }
    },
    receivePing: function(ping) {
        Chat.connection.ping.pong(ping);
    },
    onRegister: function (status) {
        fl_log_chat(0,null,'middle-chat',"onRegister",status);
        if (status === Strophe.Status.REGISTER) {
            Chat.log("Registering");
            Chat.connection.register.fields.username = Chat.registerUserInfo.Jid;
            Chat.connection.register.fields.password = Chat.registerUserInfo.Password;
            Chat.connection.register.submit();
        } else if (status === Strophe.Status.REGISTERED) {
            Chat.log("Registered!");
            Chat.connection.authenticate();
        } else if (status === Strophe.Status.CONNECTED) {
            Chat.log("logged in!");
        } else {
            // every other status a connection.connect would receive
        }
    },
    registerUserInfo: {},
    registerUser: function (server, Jid, Password, BOSH_SERVICE) {
        //XEP-0077 InBand Registration
        Chat.registerUserInfo = {'Jid': Jid, 'Password': Password};
        //possibly more reg fields later... email,name

        Chat.connection = true;
        Chat.debuggingMode = false;
        Chat.BOSH_SERVICE = (BOSH_SERVICE) ? BOSH_SERVICE : Chat.BOSH_SERVICE;
        Chat.connection = new Strophe.Connection(Chat.BOSH_SERVICE);
        Chat.connection.register.connect(server, Chat.onRegister);
    },
    sendPriority: function (priority) {
        Chat.connection.send($pres()
                .c("priority").t(priority));
        Chat.log("Priority of " + priority + " sent to contacts.");
    },
    sendPresence: function () {
        fl_log_chat(0,null,'middle-chat',"sendPresence",null);
        Chat.connection.send($pres());
        Chat.log("Presence Sent.");
    },
    messages: [],
    chatStates: {},
    _formatTimeStamp: function(timestamp) {

       // console.log(timestamp);
        var inputDate = new Date(timestamp);
        var newDate = new Date(timestamp);
        var todaysDate = new Date();
        var time;
        if (inputDate.setHours(0, 0, 0, 0) === todaysDate.setHours(0, 0, 0, 0)) {
            time = newDate.toLocaleTimeString(navigator.language, {hour: '2-digit', minute: '2-digit'});
        } else {
            //code-notes, this was giving wrong month by not adding 1 to it (zero based month)
            time = newDate.getDate() + "/" + (newDate.getMonth() +1) + "/" + newDate.getFullYear() + ' ' +
                newDate.toLocaleTimeString(navigator.language, {hour: '2-digit', minute: '2-digit'});
        }

        return time;
    },
    receiveMessage: function (msg) {
        //console.log('msg recieved',msg);
        fl_log_chat(0,null,'middle-chat',"receiveMessage",msg);
        var to = msg.getAttribute('to');
        var from = msg.getAttribute('from');
        var type = msg.getAttribute('type');
        var elems = jQuery(msg).find('body').text();
        var timestamp = jQuery(msg).find('delay').attr('stamp');
        var visibleTime = null;
        let da_date_object;
        //code-notes [room flags and peek] adding in unix timestamp for each message
        if( timestamp !== undefined) {
            visibleTime = Chat._formatTimeStamp(timestamp);
            da_date_object = new Date(timestamp);
        }
        else{
           // var date = new Date();
            //console.log(date.toISOString());
            visibleTime = 'just now';
            da_date_object = new Date();
        }



        //pubsub message
         if (from === Chat.pubsubJid && msg.getElementsByTagName('summary').length) {
            Chat.log("pubsub message", msg.getElementsByTagName('summary')[0]);
            var items = msg.getElementsByTagName('items');
            var nodeName = items[0].getAttribute('node');
            Chat.pubsubMessages.push({
                "message": Strophe.getText(msg.getElementsByTagName('summary')[0]),
                "type": "received",
                "nodeName": nodeName
            });
        }
        else if (msg.getElementsByTagName('paused').length) {
            Chat.log("Sender is Paused");
            Chat.chatStates[from] = "paused";
        }
        else if (msg.getElementsByTagName('active').length) {
            Chat.log("Sender is Active");
            Chat.chatStates[from] = "active";
        }
        else if (msg.getElementsByTagName('composing').length) {
            Chat.log("Sender is composing");
            Chat.chatStates[from] = "composing";
            Chat.onUserTyping(Chat.chatStates[from]);
            //Chat.onSetUserTypingStart(Chat.chatStates[from]);
        }
        else if (msg.getElementsByTagName('body').length) {
            var body = msg.getElementsByTagName('body')[0];
             var str = from;
             var newstr = str.split('\\')[0];
            var fron_send = newstr.split('/')[1];

            var messageInfo = {
                'to': to,
                'from': from,
                'sender': fron_send,
                'type': type,
                'msg_time':visibleTime,
                'message_raw_datetime': timestamp,
                'message_date_object': da_date_object,
                'message_timestamp' : Math.floor(da_date_object.getTime()/1000),
                'message': Strophe.getText(body)
            };
            //console.log("new YY message received", msg);
            Chat.messages.push(messageInfo);
             if (type === 'groupchat' && jQuery(msg).find('delay').length === 0 && jQuery(msg).find('subject').length === 0) {
                 //console.log("Group Chat message", msg);
                 Chat.onReceivedGroupMessage(to,from,elems,fron_send,visibleTime);
             }

             //code-notes [room flags and peek] this is where we make a callback to the custom level so it can  check the room  with the property is_new_room
             Chat.onPeekMessage(to,from,elems,fron_send,visibleTime,messageInfo);
        }
        // we must return true to keep the handler alive.
        // returning false would remove it after it finishes.
        return true;
    },
    sendMessage: function (messgeTo, message, type) {
        fl_log_chat(0,null,'middle-chat',"sendMessage",{messgeTo:messgeTo, message:message, type: type});
        //console.log(mess)
        var messagetype = (type) ? type : 'chat';
        var reply;
        if (messagetype === 'groupchat') {
            reply = $msg({to: messgeTo,
                from: Chat.connection.jid,
                type: messagetype,
                id: Chat.connection.getUniqueId()
            }).c("body", {xmlns: Strophe.NS.CLIENT}).t(message);
        }
        else {
            reply = $msg({to: messgeTo,
                from: Chat.connection.jid,
                type: messagetype
            }).c("body").t(message);
        }
        Chat.connection.send(reply.tree());
        Chat.log('I sent ' + messgeTo + ': ' + message, reply.tree());
    },
    Roster: [],
    getRoster: function () {
        if (!Chat.Roster) {
            Chat.log("Roster Items not yet loaded!/No Contacts");
        }
        else
            return Chat.Roster;
    },
    rosterReceived: function (iq) {
        Chat.log(iq);
        jQuery(iq).find("item").each(function () {
            Chat.Roster.push({jid: jQuery(this).attr('jid'),
                name: jQuery(this).attr('name'),
                subscription: jQuery(this).attr('subscription')
            });
        });
        Chat.connection.addHandler(Chat.presenceReceived, null, "presence");
    },
    //add user to your roster
    addUser: function (Jid, name, groups) {
        if (!Chat.userExists(Jid)) {
            var groups = (groups) ? groups : '';
            Chat.connection.roster.add(Jid, name, groups, function (status) {
                Chat.Roster.push({'jid': Jid,
                    'name': name,
                    subscription: '' //NOTE:MIGHT BE ERROR PRONE TO NOT DECLARE SUBSCRIPTION...
                });
                Chat.log("User Added to roster: " + name, status, Chat.Roster);
            });
            Chat.log("Added user: " + Jid);
        } else
            Chat.log("Error adding new User");
    },
    //remove user from your roster
    removeUser: function (Jid) {
        if (Chat.userExists(Jid)) {
            //Chat.connection.roster.get();
            var iq = $iq({type: 'set'}).c('query', {xmlns: Strophe.NS.ROSTER}).c('item', {jid: Jid,
                subscription: "remove"});
            Chat.connection.sendIQ(iq, function (status) {
                Chat.log("Removed: " + Jid, status);
            });
            for (var i = Chat.Roster.length - 1; i >= 0; i--) {
                if (Chat.Roster[i].jid === Jid) {
                    Chat.Roster.splice(i, 1);
                    Chat.log(Chat.Roster);
                }
            }
        } else
            Chat.log("Error removing user");
    },
    authorizeUser: function (Jid, message) {
        if (Chat.userExists(Jid)) {
            Chat.connection.roster.authorize(Jid, message);
            Chat.log("Authorized: " + Jid);
        } else
            Chat.log("Error Authorizing");
    },
    unauthorizeUser: function (Jid, message) {
        if (Chat.userExists(Jid)) {
            Chat.connection.roster.unauthorize(Jid, message);
            Chat.log("Unauthorized: " + Jid);
        } else
            Chat.log("Error Unauthorizing");

    },
    subscribeUser: function (Jid, message) {
        if (Chat.userExists(Jid)) {
            Chat.connection.roster.subscribe(Jid, message);
            //May not need, but added anyways.
            Chat.Roster.push({'jid': Jid,
                'name': Jid,
                subscription: '' //NOTE:MIGHT BE ERROR PRONE TO NOT DECLARE SUBSCRIPTION...
            });
            Chat.log("Subscribed: " + Jid);
        } else
            Chat.log("Error subscribing user");
    },
    unsubscribeUser: function (Jid, message) {
        if (Chat.userExists(Jid)) {
            Chat.connection.roster.unsubscribe(Jid, message);
            Chat.log("Unsubscribed: " + Jid);
        } else
            Chat.log("Error unsubscribing");
    },
    userExists: function (Jid) {
        for (var i = Chat.Roster.length - 1; i >= 0; i--) {
            if (Chat.Roster[i].jid === Jid) {
                return true;
            }
        }
        return false;
    },
    //A list of all the contacts online
    presenceMessage: {},
    presenceReceived: function (presence) {
        //console.log('chat presence',presence);
        fl_log_chat(0,null,'middle-chat','presenceReceived',presence);
        if (true) {return;}
        //console.log(presence);
        var presence_type = jQuery(presence).attr('type'); // unavailable, subscribed, etc...
        if(presence_type === undefined ){
            Chat.mucPresenceReceived(presence);
            return true;
        }
        var from = jQuery(presence).attr('from'); // the jabber_id of the contact...
        //console.log(Strophe.getNodeFromJid(from),presence_type);
        if (!Chat.presenceMessage[from])
            Chat.log(presence);
        if (presence_type != 'error') {
            if (presence_type === 'unavailable') {
                Chat.log("Contact: ", jQuery(presence).attr('from'), " is offline");
                Chat.presenceMessage[from] = "offline";
                Chat.onPresence(Strophe.getNodeFromJid(from), Chat.presenceMessage[from]);
            } else {
                var show = jQuery(presence).find("show").text(); // this is what gives away, dnd, etc.
                if ((show === 'chat' || show === '' || show === 'groupchat') && (!Chat.presenceMessage[from])) {
                    // Mark contact as online
                    Chat.log("Contact: ", jQuery(presence).attr('from'), " is online");
                    Chat.presenceMessage[from] = "online";
                    Chat.sendPresence();
                    Chat.onPresence(Strophe.getNodeFromJid(from), Chat.presenceMessage[from]);
                } else if (show === 'away') {
                    Chat.log("Contact: ", jQuery(presence).attr('from'), " is offline");
                    Chat.presenceMessage[from] = "offline";
                    Chat.onPresence(Strophe.getNodeFromJid(from), Chat.presenceMessage[from]);
                }
            }
        }
        return true;
    },
    sendChatState: function (Jid, status, type) {
        var chatType = (type) ? type : "chat";
        fl_log_chat(0,null,'middle-chat','sendChatState',{Jid:Jid, status:status, type:type,chatType:chatType});
        if (Chat.connection && Jid) {
            Chat.connection.chatstates.init(Chat.connection);
            if (status === "active") {
                Chat.connection.chatstates.sendActive(Jid, chatType);
            } else if (status === "composing") {
                Chat.connection.chatstates.sendComposing(Jid, chatType);
            } else if (status === "paused") {
                Chat.connection.chatstates.sendPaused(Jid, chatType);
            } else
                Chat.log("Error, try again");

        } else {
            Chat.log("Error,sorry not connected")
        }
    },
    discoSuccess: {},
    discoInfo: function (Jid) {

        Chat.connection.disco.info(Jid, '',
                //Success callback
                        function (status) {
                            Chat.log("Disc Info Success", status);
                            console.log(status);
                            Chat.discoSuccess[Jid] = true;
                        },
                        //error callback
                                function (status) {
                                    Chat.log("Disc Info Error", status);
                                    Chat.discoSuccess[Jid] = false;
                                }
                        );
                    },
            Pings: {},
            ping: function (Jid) {
                Chat.connection.ping.ping(Jid,
                        function (status) {
                            Chat.log("Ping Success", status);
                            Chat.Pings[Jid] = true;
                        },
                        function (status) {
                            Chat.log("Ping Error", status);
                            Chat.Pings[Jid] = false;
                        }
                );
            },
            discoverNodes: function () {
                Chat.connection.pubsub.discoverNodes(
                        function (iq) {
                            jQuery(iq).find("item").each(function () {
                                Chat.pubsubNodes.push(jQuery(this).attr('node'));
                                if (!Chat.pubsubJid) {
                                    Chat.pubsubJid = jQuery(this).attr('jid');
                                }
                            });
                            Chat.log("success retreiving nodes, stored in array Chat.pubsubNodes", iq);
                        },
                        function (status) {
                            Chat.log("error", status)
                        }
                );
            },
            pubsubNodes: [],
            pubsubJid: false,
            pubsubMessages: [],
            //nodeArray
            createNode: function (nodeName, options) {
                var options = (options) ? options : {};
                Chat.connection.pubsub.createNode(
                        nodeName,
                        options,
                        function (status) {
                            Chat.log("Node created", status);
                            Chat.connection.send($pres());
                        }
                );
            },
            pubsubPublish: function (nodeName, message) {
                Chat.connection.pubsub.publish(
                        nodeName,
                        message,
                        Chat.onPublish
                        );
                Chat.pubsubMessages.push({
                    "message": message,
                    "type": "sent",
                    "nodeName": nodeName
                });
            },
            onPublish: function (status) {
                Chat.log("Message published", status);
                return true;
            },
            pubsubSubscribe: function (nodeName, options) {
                var options = (options) ? options : {};
                Chat.connection.pubsub.subscribe(
                        nodeName,
                        options,
                        Chat.messageReceived,
                        function (status) {
                            Chat.log("Subscribe node created", status)
                        },
                        function (status) {
                            console.log("error subscribing to node");
                        },
                        Chat.connection.jid
                        );
            },
            pubsubUnsubscribe: function (nodeName) {
                Chat.connection.pubsub.unsubscribe(
                        nodeName,
                        Chat.getSubJID(Chat.connection.jid),
                        '',
                        function (status) {
                            Chat.log("Succesfully unsubscribed from node", status);
                        },
                        function (status) {
                            Chat.log("Error unsubscribing from node", status);
                        }
                );
            },
            subscribedNodes: [],
            //get all the nodes an individual is subscribed to.
            getSubscriptions: function () {
                Chat.connection.pubsub.getSubscriptions(function (status) {
                    Chat.log("Got Subscriptions, stored in Chat.subscribedNodes");
                    jQuery(status).find("subscription").each(function () {
                        Chat.subscribedNodes.push(jQuery(this).attr('node'));
                    });
                });
            },
            getNodeSubscriptions: function (nodeName) {
                var subscribers = [];
                Chat.connection.pubsub.getNodeSubscriptions(nodeName, function (status) {
                    Chat.log("Got all subscribers to the node", status);
                    jQuery(status).find("subscription").each(function () {
                        subscribers.push(jQuery(this).attr('jid'));
                    });

                });
                return subscribers;
            },
            //Experimental Method
            mucSendPresence: function (roomName) {
                //http://xmpp.org/extensions/xep-0045.html#createroom
                var presence = $pres({
                    to: roomName
                }).c('x', {'xmlns': 'http://jabber.org/protocol/muc'});
                Chat.log(presence.tree());
                Chat.connection.send(presence.tree());
            },
            //Experimental Method
            mucCreateRoom: function (roomName) {
                //roomname must be of the format:
                //'roomName@conference.(yyy).xxxx.com/nickName'
                Chat.mucSendPresence(roomName);
                Chat.connection.muc.createInstantRoom(roomName,
                        function (status) {
                            Chat.log("Succesfully created ChatRoom", status,roomName);
                        },
                        function (status) {
                            Chat.log("Error creating ChatRoom", status,roomName);
                        }
                );
            },
            mucSessionInfo: {},
            mucPresenceReceived: function( presence ) {
                fl_log_chat(0,null,'middle-chat','mucPresenceReceived',presence);
                if (Chat.debuggingMode) {console.log(presence);}
                var presence_type = jQuery(presence).attr('type'); // unavailable, subscribed, etc...

                var from = jQuery(presence).attr('from'); // the jabber_id of the contact...
                var room_name, participant_name, role, affiliation, status_codes = [], error_code;
                room_name = Strophe.getNodeFromJid(from);
                participant_name = Strophe.getResourceFromJid(from);
                role = jQuery(presence).find('item').attr('role');
                affiliation = jQuery(presence).find('item').attr('affiliation');
                error_code =jQuery(presence).find('error').attr('code');
                jQuery(presence).find('status').each(function(){
                    status_codes.push(jQuery(this).attr('code'));
                });

                Chat.onPresence(room_name, participant_name, role, affiliation, status_codes, presence_type, error_code);

                return true;
            },
            mucJoin: function (roomName, nickname, password) {
                var nickname = (nickname) ? nickname : Chat.getSubJID(Chat.connection.jid);
                fl_log_chat(0,null,'middle-chat','mucJoin',{roomName:roomName, nickname:nickname, password:password});
                Chat.connection.muc.join(roomName, nickname, function(msgs){
                        //console.log("MUC ",roomName, msgs);
                        let _room_id = Strophe.getNodeFromJid(roomName);
                        fl_log_chat(0,null,'middle-chat','mucJoin::handler',{room_id:_room_id,msgs:msgs});
                        Chat.onMessageLoaded(_room_id);
                        return true;
                    },
                        Chat.mucPresenceReceived, Chat.rosterReceived, password);
                Chat.onJoinroom();//code-notes where the js joins the room and after that will start getting messages
                Chat.mucSessionInfo['roomName'] = roomName;
                Chat.mucSessionInfo['nickname'] = nickname;
            },
            mucBlockUser: function(roomName, nick) {
                fl_log_chat(0,null,'middle-chat','mucBlockUser',{roomName:roomName, nick:nick});
                Chat.connection.muc.ban(roomName,nick, "none",
                   function(resp){
                    console.log('Block success', resp);
                   },
                    function(resp){
                        console.log("Block failed", resp);
                    });
            },
            mucUnBlockUser: function(roomName, nick) {
                fl_log_chat(0,null,'middle-chat','mucUnBlockUser',{roomName:roomName, nick:nick});
                Chat.connection.muc.member(roomName,nick, "none",
                    function(resp){
                        console.log('UnBlock success', resp);
                    },
                    function(resp){
                        console.log("UnBlock failed", resp);
                    });
            },
            queryMucUsers: function(room_id, member_type) {
                var attrs, info;
                attrs = {
                    xmlns: Strophe.NS.MUC_OWNER
                };
                attrs_2 = {
                    affiliation: member_type
                };
                info = $iq({
                    from: Chat.connection.jid,
                    to: room_id + '@conference.' + Chat.connection.domain ,
                    type: 'get'
                }).c('query', attrs).c('item', attrs_2);

                fl_log_chat(0,null,'middle-chat','queryMucUsers',{room_id:room_id, member_type:member_type,info:info});
                console.log(info);
                return Chat.connection.sendIQ(info, function(a){
                    fl_log_chat(0,null,'middle-chat','queryMucUsers::success',{a:a, info:info});
                    console.log(a);
                }, function(b){
                    fl_log_chat(0,null,'middle-chat','queryMucUsers::error',{b:b, info:info});
                    console.log(b);
                });
            },
            mucLeave: function (exitMessage) {
                fl_log_chat(0,null,'middle-chat','mucLeave',exitMessage);
                Chat.connection.muc.leave(
                        Chat.mucSessionInfo['roomName'],
                        Chat.mucSessionInfo['nickname'],
                        Chat.presenceReceived,
                        exitMessage
                        );
            },
            mucListRooms: function () {
                Chat.connection.muc.listRooms(
                        Strophe.getDomainFromJid(Chat.connection.jid),
                        function (status) {
                            Chat.log("List of Chat Rooms", status);
                        },
                        function (status) {
                            Chat.log("Error getting Chat Rooms", status);
                        }
                );
            },
            mucQueryOccupants: function (roomName) {
                Chat.connection.muc.queryOccupants(
                        roomName,
                        function (status) {
                            fl_log_chat(0,null,'middle-chat','mucQueryOccupants::success',status);
                            console.log("Got Group Chat Members", status);
                        },
                        function (status) {
                            fl_log_chat(0,null,'middle-chat','mucQueryOccupants::error',status);
                            Chat.log("Error Getting Group Chat Members", status);
                        }
                );
            },
            //to send a muc message,
            //call Chat.sendMessage("chatRoom@conference.server","my message text","groupchat")

            log: function () {
                //If not connected

                //start gathering args for the extra logging
                let rem_args = [];
                for (let j = 0; j < arguments.length; j++) {
                    rem_args.push(arguments[j]);
                }
                fl_log_chat(0,null,'middle-chat',"log", rem_args);

                if (!Chat.connection) {
                    console.log("Error, not connected`, please enter credentials:\n " +
                            "Chat.connect('jid','password')");
                }
                if (Chat.debuggingMode) {
                    for (var i = 0; i < arguments.length; i++) {
                        console.log(arguments[i]);
                    }
                }
            },
            getSubJID: function (Jid) {
                //for parsing JID: ramon@chat.url.com/1234567
                // becomes...
                // ramon
                return Strophe.getNodeFromJid(Strophe.getBareJidFromJid(Jid));

            },
            onConnectInit : function(room_id,password)
            {
                fl_log_chat(0,null,'middle-chat','onConnectInit',{room_id:room_id,password:password});
                var jid = room_id, password = password;
                console.log(jid);
                console.log(password);
                var BOSH_SERVICE = Chat.BOSH_SERVICE;
                var debugginMode = false;
                Chat.connect(jid, password, BOSH_SERVICE, debugginMode);
            },
        }

function connect()
{
    var jid = localStorage.getItem("jid"), password = localStorage.getItem("password");
    console.log(jid);
    console.log(password);
    var BOSH_SERVICE = Chat.BOSH_SERVICE;
    var debugginMode = false;
    Chat.connect(jid, password, BOSH_SERVICE, debugginMode);
}

function createRoom()
{
    var room_name = jQuery('#room').val() + '@'+ xmpp_helper.conference_domain + '/' + localStorage.getItem("username");;
    Chat.mucCreateRoom(room_name);
}
function getRoomList()
{
    Chat.mucListRooms();
}
function joinGroup()
{
    var room_name = $('#room').val() + '@' + xmpp_helper.conference_domain;
    var nickname = localStorage.getItem("user_name");;
    var password = localStorage.getItem("password");;
    Chat.mucJoin(room_name, nickname, password);
}

function sendGroupMsg()
{
    var room_name = jQuery('#room').val() + '@' + + xmpp_helper.conference_domain;
    var msg = jQuery('#msg').val();
    Chat.sendMessage(room_name, msg, "groupchat");
}
function login()
{
    var jid = jQuery('#user_name').val() + '@' + xmpp_helper.xmpp_host, password = jQuery('#password').val();
    localStorage.setItem("user_name", jQuery('#user_name').val());
    localStorage.setItem("jid", jid);
    localStorage.setItem("password", password);  
    window.location="chat.html";
    
}


function disconnect()
{
    localStorage.removeItem('user_name');
    localStorage.removeItem('jid');
  localStorage.removeItem('password'); 
  Chat.disconnect();
    window.location="index.html";
    
}

jQuery(function() {
    if (!xmpp_helper.xmpp_host) { jQuery('#chat_not_there').modal(); /* show message*/ }
});
