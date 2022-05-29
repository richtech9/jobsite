
//code-bookmark-js when clicking on favorite button on content in the favorites page
function add_favorite_content(id) {

    ajaxindicatorstart('Loading Please Wait');


    var data = {'action': 'add_favorite_content', 'id': id};
    jQuery.ajax({
        type: 'POST',
        url: adminAjax.url,
        data: data,
        dataType: 'json',
        cache: false,
        success: function (response) {
            if (response.success === true) {
                //jQuery(".wishlist-itext").text( response.markup );
                $("#resultLoading").css({"display": "none"});
                //alert( response.text );
                $("#row_" + id).remove();
                jQuery('body').css('cursor', 'pointer');
            }
        }
    });
}

jQuery(function ($) {


    //code-bookmark-js helper function to awarding prizes
    function unloadPopupBox() {    // TO Unload the Popupbox

        jQuery('.awardResponse').text('');

        jQuery('#awardPops').fadeOut("slow");

        jQuery(".awardPrize").css({ // this is just for style

            "opacity": "1"

        });

    }



    //code-bookmark-js helper function to awarding prizes
    function loadProposalPopupBox() {    // To Load the Popupbox

        jQuery('.awardResponse').text('');

        jQuery('#awardPops').fadeIn("slow");

    }



    //code-bookmark-js when the customer is awarding a proposal in a contest
    jQuery('.awardProposalPrize').click(function () {

        var linguist = jQuery(this).attr('linguist');
        var proposal_id = jQuery(this).attr('proposal_id');

        if (proposal_id) {

            jQuery('#yes_proposal_btn').attr('linguId', linguist);
            jQuery('#yes_proposal_btn').attr('proposalId', proposal_id);

        }

        loadProposalPopupBox();

    });

    //code-bookmark-js when the customer clicks the award button
    jQuery('#awardDash').click(function () {

        unloadPopupBox();

    });


    /**
     * get a list of the proposals, count awarded
     *  If there are NO provious awards, award automatically
     * If there are  previous awarded proposals, show fee box, and award if use presses accept
     */
    //code-bookmark-js when the customer awards a proposal (clicks the award button)
    jQuery('#yes_proposal_btn').click(function () {

        //code-notes modified the ajax return to handle standard data
        //code-notes check to see if first proposal already paid out
        function callback() {
            var linguId = jQuery('#yes_proposal_btn').attr('linguId');

            var contestId = jQuery('#yes_proposal_btn').attr('contestId');

            var proposalId = jQuery('#yes_proposal_btn').attr('proposalId');

            var data = {

                'action': 'hz_awardprize_to_proposal',

                'contestId': contestId,

                'linguId': linguId,
                'proposalId': proposalId,

            };
            jQuery('#yes_proposal_btn').prop('disabled', true);
            jQuery.post(getObj.ajaxurl, data, function (response_raw) {
                /**
                 * @type {FreelinguistBasicAjaxResponse} response
                 */
                let response = freelinguist_safe_cast_to_object(response_raw);
                if (response.status === true) {
                    jQuery('.awardResponse').text(response.message);

                    setTimeout(function () {
                        jQuery('#yes_proposal_btn').prop('disabled', false);
                        window.location.reload(true);
                    }, 500);
                } else {
                    will_handle_ajax_error('Award Proposal', response.message);
                }


            });
        }//end callback

        let da_id = jQuery('#yes_proposal_btn').attr('contestId');

        var data = {
            action: 'freelinguist_proposal_list',
            contest_id: da_id
        };
        $.post(getObj.ajaxurl,
            data,
            /**
             * @param {string} response_raw
             */
            function (response_raw) {
                /**
                 * @type {FreelinguistProposalList} response
                 */
                let response = freelinguist_safe_cast_to_object(response_raw);
                if (response.status) {
                    let count_awarded = 0;
                    for (let i = 0; i < response.proposals.length; i++) {
                        let node = response.proposals[i];
                        if (node.awarded) {
                            count_awarded++;
                        }
                    }
                    if (count_awarded >= 1) {
                        //already awarded once at least
                        freelinguist_show_fee_box('customer_awarding_another_proposal', null, null, da_id, callback);
                    } else {
                        callback(); //first time award, no fee box
                    }
                } else {
                    $.notify("Error with listing proposals: \n" + response.message, "error");
                    console.error(response.message);
                }
            });

    });


    //code-bookmark-js when the customer cancels the award dialog box
    jQuery('#no_btn').click(function () {

        unloadPopupBox();

    });

});


jQuery(function () {

    //code-bookmark-js when participate button is pressed in the contest page for the freelancer
    jQuery('#prt_accept').click(function () {

        //code-notes modified the ajax return to handle standard data
        var linguId = jQuery(this).attr('linguId');

        var contestId = jQuery(this).attr('contestId');
        var lang = jQuery(this).attr('lang');

        var data = {

            'action': 'hz_add_participate',

            'contestId': contestId,

            'linguId': linguId,
            'lang': lang,

        };

        jQuery.post(getObj.ajaxurl, data, function (response_raw) {

            /**
             * @type {FreelinguistLinkResponse} res
             */
            var res = freelinguist_safe_cast_to_object(response_raw);
            if (res.status === true) {
                jQuery('.awardResponse').text(res.message);
                if (res.url) {
                    window.location.href = res.url;
                } else {
                    bootbox.alert(res.message);
                }
            } else {
                will_handle_ajax_error('Add Particaption', response.message);
            }


        });

    });

    //code-bookmark-js when participate button is pressed in the job search page for a content
    jQuery('.prt_accept').click(function () {

        //code-notes modified the ajax return to handle standard data
        var linguId = jQuery(this).attr('linguId');

        var contestId = jQuery(this).attr('contestId');
        var lang = jQuery(this).attr('lang');

        var data = {

            'action': 'hz_add_participate',

            'contestId': contestId,

            'linguId': linguId,
            'lang': lang,

        };

        jQuery.post(getObj.ajaxurl, data, function (response_raw) {

            /**
             * @type {FreelinguistLinkResponse} res
             */
            var res = freelinguist_safe_cast_to_object(response_raw);
            if (res.status === true) {
                jQuery('.awardResponse').text(res.message);
                if (res.url) {
                    window.location.href = res.url;
                } else {
                    bootbox.alert(res.message);
                }
            } else {
                will_handle_ajax_error('Add Particaption', response.message);
            }

        });

    });

});

var fl_b_do_not_ask_about_content_rejection = false;
jQuery(function () {



    //code-bookmark-js helper function when the report button is pressed
    function openCCboxReport() {
        jQuery('#openCCboxReport').fadeIn("slow");

        jQuery(".approvecompletion").css({ // this is just for style

            "opacity": "1"

        });


    }



    //code-bookmark-js when the report button is pressed
    jQuery('#report_button').click(function () {
        openCCboxReport();
    });



    //code-bookmark-js when the linguist approves completion for a proposal in a contest
    jQuery('#ccyes_proposal').not('.dont-call-complete').click(function () {

        //code-notes modified the ajax return to handle standard data
        jQuery('.contest_status').text('');

        var authorid = jQuery(this).attr('cusid');

        var contestId = jQuery(this).attr('contestId');
        var proposalId = jQuery(this).attr('proposalId');

        var data = {

            'action': 'hz_complete_contest_proposal',

            'contestId': contestId,
            'proposalId': proposalId,

            'authorid': authorid,

        };
        //ajax-entry-protected hz_complete_contest_proposal
        data = freelinguist_add_security_keys(data, 'hz_complete_contest_proposal');
        if (data === false) {
            return false;
        }

        jQuery.post(getObj.ajaxurl, data, function (response_raw) {
            /**
             * @type {FreelinguistBasicAjaxResponse} response
             */
            let response = freelinguist_safe_cast_to_object(response_raw);
            if (response.status === true) {
                jQuery('.contest_status').text(response.message);
                window.location.reload(true);
            } else {
                will_handle_ajax_error('Completing Contest Proposal', response.message);
            }


        });

    });

    //code-bookmark-js when the proposal status is changed
    //code-notes modified the ajax return to handle standard data
    jQuery('.change_proposal_status').click(function () {
        //ajax-entry-protected  hz_change_status_contest_proposal

        function callback() {
            jQuery('.contest_status').text('');

            var contestId = jQuery(this).attr('contestId');
            var proposalId = jQuery(this).attr('proposalId');
            var proposal_status = jQuery(this).attr('status');
            var revision_text = jQuery('#revision_text').val();
            var rejection_txt = jQuery('#rejection_txt').val();

            var data = {

                'action': 'hz_change_status_contest_proposal',

                'contestId': contestId,
                'proposalId': proposalId,

                'proposal_status': proposal_status,
                'revision_text': revision_text,
                'rejection_txt': rejection_txt,

            };

            data = freelinguist_add_security_keys(data, 'hz_change_status_contest_proposal', true);
            if (data === false) {
                return false;
            }

            jQuery.post(getObj.ajaxurl, data, function (response_raw) {

                /**
                 * @type {FreelinguistBasicAjaxResponse} response
                 */
                let response = freelinguist_safe_cast_to_object(response_raw);
                if (response.status) {
                    //code-notes added check for success before reloading
                    jQuery('.contest_status').text(response.message);
                    location.reload(true);
                } else {
                    will_handle_ajax_error('Changing Proposal Status', response.message)
                }

            });
        }

        //if this action costs something, then use the wrapper, else call it directly
        let dat_status = jQuery(this).attr('status');
        let that_context = this;
        let call_me_ishmael = callback.bind(that_context);
        if (dat_status === 'hire_mediator') {
            freelinguist_show_fee_box('freelancer_asking_proposal_mediation', null, null, null, call_me_ishmael);
            return false;
        } else {
            if (dat_status === 'rejected' || dat_status === 'cancelled') {
                bootbox.confirm({

                    message: "Are you sure you want to cancel this proposal? After the cancellation, the fund will be returned to the client.The cancellation is FINAL.",

                    buttons: {

                        confirm: {
                            label: 'Yes',
                            className: 'btn-success'
                        },

                        cancel: {
                            label: "No",
                            className: 'btn-danger'
                        }

                    },

                    callback: function (result) {

                        if (result === true) {
                            call_me_ishmael();
                            return true;
                        }
                        return true;
                    }
                });
                return false;
            }//end if rejected
            return call_me_ishmael();
        }

    });

    // code-bookmark-js calls  hz_change_status_content when the content status is chagned
    jQuery('.change_content_status').click(function () {

        //code-notes modified the ajax return to handle standard data

        function callback() {
            jQuery('.contest_status').text('');

            var contentId = jQuery(this).attr('contentId');

            var content_status = jQuery(this).attr('status');
            var revision_text = jQuery('#revision_text').val();
            var rejection_txt = jQuery('#rejection_txt').val();

            var data = {

                'action': 'hz_change_status_content',

                'contentId': contentId,

                'content_status': content_status,
                'revision_text': revision_text,
                'rejection_txt': rejection_txt,

            };

            //ajax-entry-protected hz_change_status_content
            data = freelinguist_add_security_keys(data, 'hz_change_status_content', false);
            if (data === false) {
                return false;
            }

            jQuery.post(getObj.ajaxurl, data, function (response_raw) {

                /**
                 * @type {FreelinguistBasicAjaxResponse} response
                 */
                let response = freelinguist_safe_cast_to_object(response_raw);
                if (response.status) {
                    //code-notes added check for success before reloading

                    jQuery('.contest_status').text(response.message);
                    location.reload(true);
                } else {
                    will_handle_ajax_error('Content Status', response.message)
                }

                return false;//to avoid default actions
            });
        }

        //if this action costs something, then use the wrapper, else call it directly
        let dat_status = jQuery(this).attr('status');
        let that_context = this;
        let call_me_ishmael = callback.bind(that_context);
        if (dat_status === 'hire_mediator') {
            freelinguist_show_fee_box('freelancer_asking_content_mediation', null, null, null, call_me_ishmael);
            return false;
        } else {
            if (fl_b_do_not_ask_about_content_rejection) {
                fl_b_do_not_ask_about_content_rejection = false; //one time thing each set
                return call_me_ishmael();
            }
            else if((dat_status === 'rejected') || (dat_status === 'cancelled')) {
                bootbox.confirm({

                    message: "Are you sure you want to cancel this sale? The cancellation will be final.",

                    buttons: {

                        confirm: {
                            label: 'Yes',
                            className: 'btn-success'
                        },

                        cancel: {
                            label: "No",
                            className: 'btn-danger'
                        }

                    },

                    callback: function (result) {

                        if (result === true) {
                            call_me_ishmael();
                            return true;
                        }
                        return true;
                    }
                });
                return false;
            } //end if rejected or cancelled
            return call_me_ishmael();
        }


    });


    //code-notes updating the handler for posting content discussion to use standard ajax
    //code-bookmark-js this is where the handler for the content discussion (customer) is at
    jQuery("#content_discussion").on("submit", function () {
        var form_data = $(this).serializeArray(); // convert form to array
        form_data = freelinguist_add_security_keys(form_data, 'hz_post_fl_content_discussion', true);
        if (form_data === false) {
            return false;
        }
        var param_data = $.param(form_data);
        jQuery(this).closest('form').find("textarea[name='comment']").val('');
        //code-notes empty the form input after serialize
        var data = {'action': 'hz_post_fl_content_discussion', 'data': param_data};
        //ajax-entry-protected hz_post_fl_content_discussion
        jQuery.post(adminAjax.url, data, function (response_raw) {

            /**
             * @type {FreelinguistDiscussionResponse} response
             */
            let response = freelinguist_safe_cast_to_object(response_raw);
            if (response.status === true) {
                window.location.reload(true);
            } else {
                will_handle_ajax_error('Content Discussion', response.message);
            }

        }, 'json');

        return false;

    });

    //code-bookmark-js this is where the handler for the content discussion (customer) is at
    jQuery("#freelancer-content-discussion-input button").on("click", function () {
        let params = {};
        debugger;
        let key_return = freelinguist_add_security_keys(params, 'hz_post_fl_content_discussion', true);
        if (key_return === false) {
            return false;
        }
        let par = $('div#freelancer-content-discussion-input');
        params.comment = par.find("textarea[name='comment']").val();
        params.comment_to = par.find("input[name='comment_to']").val();
        params.content_id = par.find("input[name='content_id']").val();
        params.parent_comment = null;
        let param_data = $.param(params);
        par.find("input[name='comment']").val('');

        //comment
        //code-notes empty the form input after serialize
        let data = {'action': 'hz_post_fl_content_discussion', 'data': param_data};
        //ajax-entry-protected hz_post_fl_content_discussion
        jQuery.post(adminAjax.url, data, function (response_raw) {

            /**
             * @type {FreelinguistDiscussionResponse} response
             */
            let response = freelinguist_safe_cast_to_object(response_raw);
            if (response.status === true) {
                window.location.reload(true);
            } else {
                will_handle_ajax_error('Content Discussion', response.message);
            }

        }, 'json');

        return false;

    });


    //code-bookmark-js posts discussions between people in the public comments for the content
    jQuery("#content_public_discussion").on("submit", function(e){
        //ajax-entry-protected hz_post_fl_discussion
        e.preventDefault();
        e.stopPropagation();
        var comment =  jQuery("#content_public_discussion").find('input[name="comment"]').val();
        if($.trim(comment) === ''){
            jQuery("#content_public_discussion").find('.commentEmptyMessageMain').html('Please enter your comment.');
            return false;
        } else{
            jQuery("#content_public_discussion").find('.commentEmptyMessageMain').html(' ');

        }


        var form_data = jQuery("#content_public_discussion").serializeArray(); // convert form to array
        form_data = freelinguist_add_security_keys(form_data,'content_public_discussion',true);
        if (form_data === false) {return false;}
        var param_data = $.param(form_data);
        jQuery("#content_public_discussion").find('input[name="comment"]').val('');//code-notes empty the form input
        var data = { 'action': 'hz_post_fl_discussion','data': param_data };
        jQuery.post( adminAjax.url, data, function(response_raw){
            /**
             * @type {FreelinguistDiscussionResponse} response
             */
            var response = freelinguist_safe_cast_to_object(response_raw);
            if(!response.is_login){
                var redirect = window.location.href;
                window.location.href = devscript_getsiteurl.getsiteurl+"/login/?redirect_to="+redirect;
            }
            if (response.status === true) {
                $(".comments-list").prepend( response.context );
                window.location.reload();
            } else {
                will_handle_ajax_error('Adding to the  Discussion',response.message);
            }


        },'json');

        return false;

    });



});


jQuery(function () {

    //code-bookmark-js setup to the input to give a price for an offer to buy content (from customer to freelancer)
    jQuery("input#offershoot").numeric();

    //code-bookmark-js when the freelancer is creating content and selects the sale type
    jQuery('#content_sale_type').change(function () {

        var offerSel = jQuery(this).val();

        if (offerSel === 'Offer') {

            jQuery('#max_to_be_sold').css("display", "none");

            jQuery('#content_amount').css("display", "none");

        }

        else {

            jQuery('#max_to_be_sold').css("display", "block");

            jQuery('#content_amount').css("display", "block");

        }

    });


});


jQuery(function () {



    //code-notes notes the js for the accept/reject content was updated
    //code-bookmark-js when the freelancer accepts an offer for the content
    jQuery(document).on('click', '.accpetrejOffer', function () {

        var cusid = jQuery(this).attr('uid');

        var contestId = jQuery(this).attr('cid');

        var request = jQuery(this).attr('todo');

        var dataObj = function ($action, $contestId, $cusid, $request) {

            this.action = $action;

            this.contestId = $contestId;

            this.cusid = $cusid;

            this.request = $request;

        };

        var data = new dataObj('hz_Offer_accept_reject', contestId, cusid, request);

        jQuery.post(getObj.ajaxurl, data, function (response_raw) {
            /**
             * @type {FreelinguistBasicAjaxResponse} response
             */
            let response = freelinguist_safe_cast_to_object(response_raw);
            jQuery('.respoReply' + cusid).html(response.message);
            if (response.status === true) {
                window.location.reload(true);
            } else {
                will_handle_ajax_error('Customer Feedback for Content', response.message);
            }


        });

    });
});

//code-bookmark-js when the user clicks to submit a report
function submit_report() {
    //ajax-entry-protected hz_submit_report
    var form_data = jQuery('#report_form').serializeArray(); // convert form to array
    form_data = freelinguist_add_security_keys(form_data, 'hz_submit_report', true);
    if (form_data === false) {
        return false;
    }
    var data = $.param(form_data);

    if (jQuery("#report_note").val() === "") {
        bootbox.alert("Please enter report note");
        return false;
    } else {
        jQuery.post(getObj.ajaxurl, data, function (response) {
            if (jQuery.trim(response) === "success") {
                jQuery('div.freelinguist-after-submit-report span').html("<p style=\"font-size:1.3em \">The report has been submitted successfully.</p>");
                setTimeout(function () {
                    window.location.reload();
                    return true;
                }, 3000);

            } else {
                //bootbox.alert("error occured");
                window.location.reload();
                return false;
            }


        });
    }
}
