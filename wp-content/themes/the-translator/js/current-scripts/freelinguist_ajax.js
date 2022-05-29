



jQuery(function($) {

    //code-bookmark-js make sure all links that point to # do not trigger the link
    jQuery('a[href="#"]').not('.freelinguist-download-button').click(function(e) {
        e.preventDefault ? e.preventDefault() : e.returnValue = false;
    });

    /**
     * @var {AjaxHelperObject} getObj
     */
    //code-bookmark-js When the customer clicks the cancel contest button
    jQuery('span.cancel-contest-button').click(function(){
        let that = $(this);
        if (that.hasClass('disable-cancel-contest-button')) {return;}
        let job_id = that.data('contestid');
        if (job_id) {
            let b_what = confirm("Do you really want to request cancellation of the competition?" );
            if (!b_what) {return;}
            var data = {
                'action': 'freelinguist_request_cancel_contest',
                'job_id': job_id,
            };

            $.post(getObj.ajaxurl,
                data,
                /**
                 *
                 * @param {string} response_raw
                 */
                function (response_raw) {
                    /**
                     * @type {AjaxContestCancelResponse} response
                     */
                    let response = JSON.parse(response_raw);
                    that.text(response.message);
                    that.addClass('disable-cancel-contest-button')

                }
            );
        } else {
            alert("job id is not defined for this event handler");
        }

    });

    //code-bookmark-js When the freelancer claims prize money after not being awarded on time
    jQuery("div.fl-freelancer-claim-prize span.da_clicker").click(function(){
        let that = $(this);
        if (that.hasClass('disable-cancel-contest-button')) {return;}
        let contest_id = that.data('contestid');
        if (contest_id) {
            var data = {
                'action': 'freelinguist_claim_contest_prize',
                'contest_id': contest_id,
            };

            $.post(getObj.ajaxurl,
                data,
                /**
                 *
                 * @param {string} response_raw
                 */
                function (response_raw) {
                    /**
                     * @type {FreelinguistBasicAjaxResponse} response
                     */
                    let response = JSON.parse(response_raw);
                    if (response.status) {
                        that.text(response.message);
                        that.addClass('award-disabled');
                        that.removeClass('da_clicker');
                    } else {
                        that.text(response.message);
                        that.addClass('award-error');
                    }

                    console.debug(response);


                }
            );
        } else {
            alert("job id is not defined for this event handler");
        }

    });

    //code-bookmark-js When the freelancer deletes a submitted proposal in a contest
    jQuery('span.freelinguist-delete-proposal-file').click(function(){
        let me = $(this);
        let file_id = parseInt((me.data('fid').toString()));
        if (!file_id) {file_id = 0;}
        let proposal_id = parseInt((me.data('pid').toString()));
        if (!proposal_id) {file_id = 0;}
        fl_delete_proposal(proposal_id,file_id);
    });

    /**
     * calls ajax to either
     * a) remove an entire proposal by specifying an id
     * b) remove a single file in a proposal, if that proposal only has one file, the proposal is deleted too
     *
     * After the ajax responds, it will try to remove the display of what was deleted, without refreshing the screen
     * If there is an error, it will show that error in each display box that is affected by it
     *
     * @param {number} proposal_id
     * @param {number} proposal_file_id
     */
    //code-bookmark-js helping delete proposal above
    function fl_delete_proposal(proposal_id ,proposal_file_id) {
        var data = {
            'action': 'freelinguist_proposal_file_delete',
            'file_id': proposal_file_id,
            'proposal_id': proposal_id,
        };

        $.post(getObj.ajaxurl,
            data,
            /**
             *
             * @param {string} response_raw
             */
            function (response_raw) {
                /**
                 * @type {FreelinguistProposalFileDeleteResponse} response
                 */
                let response = freelinguist_safe_cast_to_object(response_raw);

                if (response.deleted_proposal_id) {
                    let rem = $('div.fl-proposal-box[data-pid="'+response.deleted_proposal_id+'"]');
                    rem.remove();
                }

                if (response.deleted_file_ids && Array.isArray(response.deleted_file_ids)) {
                    for(let i = 0; i < response.deleted_file_ids.length; i++) {
                        let rem = $('div.fl-proposal-file-box[data-fid="'+response.deleted_file_ids[i] +'"]');
                        rem.remove();
                    }

                }

                $('span.fl_proposal_error').remove();
                if (response.error_proposal_id) {
                    let notices = $('div.fl-proposal-box[data-pid="'+response.error_proposal_id+'"]');
                    let oops = $('<span class="fl_proposal_error">Cannot Delete: '+response.message+'</span>');
                    notices.prepend(oops);
                }

                if (response.error_file_ids && Array.isArray(response.error_file_ids)) {
                    for(let i = 0; i < response.error_file_ids.length; i++) {
                        let this_file_message ;
                        if ((!Array.isArray(response.error_file_messages)) ||
                            (response.error_file_messages.length <= i)      ||
                            (!response.error_file_messages[i])
                        ) {
                            this_file_message = 'File Deletion Error Not Given';
                        } else {
                            this_file_message = response.error_file_messages[i];
                        }

                        let oops = $('<span class="fl_proposal_error">Cannot Delete: '+this_file_message+'</span>');
                        let note_to = $('div.fl-proposal-file-box[data-fid="' + response.error_file_ids[i]  + '"]');
                        note_to.prepend(oops);
                    }
                }

                if(!response.status &&
                    !response.error_file_ids.length &&
                    !response.error_proposal_id
                ) {
                    let oops = $('<span class="fl_proposal_error">General Error: '+response.message+'</span>');
                    let note_to = $('header');
                    note_to.append(oops);
                }


                console.debug(response);


            });
    }

    //code-bookmark-js When the freelancer buys protetion against others from looking at his proposal
    jQuery('button.fl-proposals-seal').click(function() {

        function callback() {
            let man = $(this);
            let contest_id = man.data('jobid');
            let parent = man.closest('div.fl-seal-content');
            let inner_span = parent.find('span.fl-inner-dets');
            let error_span = parent.find('span.fl-error');
            error_span.hide();
            error_span.html('');

            var data = {
                action: 'freelinguist_proposal_seal',
                contest_id: contest_id,
            };

            $.post(getObj.ajaxurl,
                data,
                /**
                 * @param {string} response_raw
                 */
                function (response_raw) {
                    /**
                     * @type {FreelinguistBasicAjaxResponse} response
                     */
                    let response = freelinguist_safe_cast_to_object(response_raw);
                    if (response.status) {
                        inner_span.html(response.message);
                        man.hide();
                    } else {
                        error_span.html(response.message);
                        error_span.show();
                    }
                }
            );
        }

        let that_context = this;
        let call_me_ishmael = callback.bind(that_context);
        freelinguist_show_fee_box('freelancer_sealing_own_proposal',null,null,null,call_me_ishmael);
    });

    //code-bookmark-js When the freelancer buys the ability to see other proposals in the contest that have no protection
    jQuery('button.fl-proposals-view').click(function() {

        function callback() {
            let man = $(this);
            let contest_id = man.data('jobid');
            let parent = man.closest('div.fl-proposals-view-buy');

            let error_span = parent.find('span.fl-error');
            error_span.hide();
            error_span.html('');

            var data = {
                action: 'freelinguist_proposals_view',
                contest_id: contest_id,
            };

            $.post(getObj.ajaxurl,
                data,
                /**
                 * @param {string} response_raw
                 */
                function (response_raw) {
                    /**
                     * @type {FreelinguistBasicAjaxResponse} response
                     */
                    let response = freelinguist_safe_cast_to_object(response_raw);
                    if (response.status) {
                        parent.hide();
                        window.location.reload(true);
                    } else {
                        error_span.html(response.message);
                        error_span.show();
                    }
                }
            );
        }
        let that_context = this;
        let call_me_ishmael = callback.bind(that_context);
        freelinguist_show_fee_box('freelancer_viewing_other_proposals',null,null,null,call_me_ishmael);
    });



    //code-bookmark-js When the download button is clicked, makes sure double actions cannot be done on it
    jQuery( "a.freelinguist-download-button" ).one( "click", freelinguist_get_download );

}); //end on load function

//code-bookmark-js used to handle errors in many ajax returns
//code-notes JQuery will automatically cast json to an object or array if the server returns a json content header. This varies a lot in the code, and we need a safe way to cast if we want to hook into all code
function freelinguist_safe_cast_to_object(response) {
    if (typeof response === 'string' || response instanceof String) {
        //try to cast it to json, if fails return the response back unchanged
        try {
            response = response.trim();
            var obj = JSON.parse( response ? response : '{}' ); //response can be an empty string
            return obj;
        } catch (e) {
            return response;
        }
    } else {
        return response;
    }
}

//code-bookmark-js Part of the debugging and hook into the chat system, here is when authentication is happening
function freelinguist_refresh_chat_credentials(msg) {
    console.log("my message handler",msg);
}

//code-bookmark-js Part of the debugging and hook into the chat system, used to do console checks
// noinspection JSUnusedGlobalSymbols
function fl_test_chat(test_command,test_user_id,any_username,any_password) {
    var data = {
        action: 'freelinguist_refresh_chat_credentials',
        test_command: test_command,
        test_user_id: test_user_id,
        any_username: any_username,
        any_password: any_password
    };

    $.post(getObj.ajaxurl,
        data,

        function (response_raw) {


            console.log(response_raw);

        }
    );
}


//code-bookmark-js not used, but can be helpful for debugging later
// noinspection JSUnusedGlobalSymbols
function freelinguist_get_user_local_time(user_id,element) {

    var data = {
        action: 'freelinguist_user_get_local_time',
        user_id: user_id,
    };

    $.post(getObj.ajaxurl,
        data,
        /**
         * @param {string} response_raw
         */
        function (response_raw) {
            /**
             * @type {FreelinguistBasicAjaxResponse} response
             */
            let response = freelinguist_safe_cast_to_object(response_raw);
            if (response.status) {
                if (element) {
                    element.text(response.message);
                } else {
                    console.log('User id local time is ',response.message);
                }

            } else {
                $.notify("Error with user local time: \n" + response.message, "error");
                console.error(response.message);
            }
        }
    );
}

//code-bookmark-js used in many js functions to standardized the error handling process
function will_handle_ajax_error(prefix,error_message) {
    let string_prefix = prefix.toString();
    $.notify(string_prefix + ": \n" + error_message, "error");
    console.error(prefix,error_message);
}

//code-bookmark-js when a download button is pressed
function freelinguist_get_download() {

    let that = $(this);
    if (that.attr('href') !== '#') {return ;}

    let job_file_id = that.data('job_file_id');
    if (!job_file_id) {job_file_id = null;}

    let content_file_id = that.data('content_file_id');
    if (!content_file_id) {content_file_id = null;}

    let content_id = that.data('content_id');
    if (!content_id) {content_id = null;}


    var data = {

        action: 'freelinguist_get_file_download_url',
        job_file_id: job_file_id,
        content_file_id : content_file_id,
    };

    jQuery.post(getObj.ajaxurl, data, function(response_raw){

        /**
         * @type {FreelinguistLinkResponse} res
         */
        var response = freelinguist_safe_cast_to_object(response_raw);
        if (response.status === true) {
            that.attr('href',response.url);
            that.addClass('freelinguist-ready-to-download');
            setTimeout(function() {
                that[0].click();
            },500);
        } else {
            that.one( "click", freelinguist_get_download ); //allow for repeated clicks if get error
            will_handle_ajax_error('Download File',response.message);
        }

    });

    return false;

}


//code-bookmark-js Part of the debugging and hook into the chat system
var fl_log_chat_session_counter = 1;
var fl_log_chat_page_session =  '';
var fl_log_chat_had_error = false;
var fl_log_chat_show_in_console = false; //this-task-notes turn on this flag for js console for chat
/**
 *
 * @param is_incoming
 * @param chat_room_text_id
 * @param data_source
 * @param {string} data_action
 * @param mixed_data, can be in xml string, json string, js object, or dom
 *
 */
//code-bookmark-js Part of the debugging and hook into the chat system
function fl_log_chat(is_incoming,chat_room_text_id,data_source,data_action,mixed_data) {

    if (fl_log_chat_show_in_console) {
        console.debug(is_incoming,chat_room_text_id,data_source,data_action,mixed_data);
    }

     let can_log_with_db = parseInt(xmpp_helper.can_log_with_db);
    if (!can_log_with_db) {return;}
    if (fl_log_chat_had_error) {return;}

    function makeid(length) {
        var result           = '';
        var characters       = 'ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        var charactersLength = characters.length;
        for ( var i = 0; i < length; i++ ) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
    }

    function IsJsonString(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }

    if (!fl_log_chat_page_session) {
        let my_id = makeid(10);
        fl_log_chat_page_session =  my_id + '-' + Date.now();
    }
    let data_in_json;
    //test if mixed_data is a string, if so try to use xml_str2json, if not valid xml it will return null, if null submit original data
    if (typeof mixed_data === 'string' || mixed_data instanceof String) {
        if (IsJsonString(mixed_data)) {
            data_in_json = mixed_data;
        } else {
            let x2js = new X2JS();
            let maybe_was_xml_now_json = x2js.xml_str2json( mixed_data ) ;
            if (maybe_was_xml_now_json) {
                data_in_json = maybe_was_xml_now_json;
            } else {
                data_in_json = mixed_data; //regular or misformed string ok
            }
        }
    } else {
        if (mixed_data instanceof Element) {
            let x2js = new X2JS();
            data_in_json = x2js.xml2json( mixed_data )
        } else if (typeof mixed_data === 'object' && mixed_data !== null){
            let x2js = new X2JS();
            let node = {};
            for(let k in mixed_data) {
                if (!mixed_data.hasOwnProperty(k)) {continue;}
                if (mixed_data[k] instanceof Element) {
                    let trans = x2js.xml2json( mixed_data[k] );
                    node[k] = trans;
                } else {
                    node[k] =  mixed_data[k]
                }
            }
            data_in_json = node;
        } else {
            data_in_json = mixed_data;
        }

    }
    //other types of data okay

    let is_being_sent_to_page = is_incoming;
    let chat_user_text_id = xmpp_helper.username;
    let now_object = new Date();
    let now_time = now_object.valueOf();
    let data = {
        action: 'fl_log_chat',
        page_session: fl_log_chat_page_session,
        page_session_counter: fl_log_chat_session_counter ++,
        ts_sent: now_time,
        is_being_sent_to_page : is_being_sent_to_page,
        chat_user_text_id : chat_user_text_id,
        chat_room_text_id : chat_room_text_id,
        data_source : data_source,
        data_action: data_action,
        data_in_json : data_in_json
    };

    jQuery.post(getObj.ajaxurl, data, function(response_raw){

        /**
         * @type {FreelinguistBasicAjaxResponse} res
         */
        var response = freelinguist_safe_cast_to_object(response_raw);
        if (response.status === true) {
            console.debug(response);
        } else {
            if (!fl_log_chat_had_error) {
                will_handle_ajax_error('Cannot Log Chat ',response.message);
            }

            fl_log_chat_had_error= true;
        }

    });
}


