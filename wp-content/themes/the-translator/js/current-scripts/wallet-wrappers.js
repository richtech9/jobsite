//these js check to see if the fees and costs are okay, then continue with the original ajax
// because all server responses are asynchronous, call the new ajax first to find the fees and costs, then call the original function
//calls api freelinguist_get_charge_list
//code-bookmark-js Shown in several places when a fee box is needed before an action which costs money
function freelinguist_show_fee_box(charge_type,amount,flag,id,callback_on_approval) {
    var data = {
        action: 'freelinguist_get_charge_list',
        charge_type: charge_type,
        amount : amount,
        flag : flag,
        id : id
    };
    $.post(getObj.ajaxurl,
        data,
        /**
         * @param {string} response_raw
         */
        function (response_raw) {
            /**
             * @type {FreelinguistGetChargeListResponse} response
             */
            let response = freelinguist_safe_cast_to_object(response_raw);
            console.log(response);
            let thing;
            if (response.status) {
                let template = $('div.copy-fee-modal-template').find('div.fee-modal');
                thing = template.clone(true);
                let liner_template = thing.find('tr.fee-liner-template');
                liner_template.detach();
                let insert_lines_here = thing.find('tbody.insert-lines-here');
                console.log(liner_template);
                //we add the lines backward because we are prepending them to the top, to have the total line stay at the bottom
                for (let i = response.charges.length - 1; i >= 0; i--) {
                    let det = response.charges[i];
                    let line = liner_template.clone();
                    line.removeClass('fee-liner-template');
                    let title_node = line.find('span.fee-modal-line-title');
                    let content_node = line.find('span.fee-modal-line-content');
                    let description_node = line.find('span.fee-modal-line-description');
                    title_node.html(det.charge_name);
                    content_node.html(det.amount_formatted);
                    description_node.html(det.description);
                    insert_lines_here.prepend(line);
                }

                let total_node = thing.find('span.fee-modal-total-content');
                total_node.html(response.total_formatted);

                let pre_wallet_node = thing.find('span.fee-modal-pre-wallet-content');
                pre_wallet_node.html(response.wallet_balance_formatted);

                let post_wallet_node = thing.find('span.fee-modal-post-wallet-content');
                post_wallet_node.html(response.post_balance_formatted);

                let overall_description_node = thing.find('div.fee-description');
                overall_description_node.html(response.html_general_description);

                let title_node = thing.find('span.fee-modal-title');
                title_node.html(response.message);

                let yes_button = thing.find('button.fee-modal-confirm');
                let no_button = thing.find('button.fee-modal-deny');

                yes_button.one( "click", function() {
                    console.log('yes');
                    if (callback_on_approval) {
                        callback_on_approval();
                    }
                });

                no_button.one( "click", function() {
                    console.log('no');
                });

                thing.modal();
            } else {
                $.notify("Error with fee display: \n" + response.message, "error");
                console.error(response.message);
            }

            //code-notes set up a one time listener for the approval button, and when clicked, do the callback
        }
        ); //end post function
}

/**
 * Called as a on-submit handler when contest creation submit is clicked
 * @param e
 * @param thisform
 */
//code-bookmark-js shown before a contest is placed
function wrap_wallet_contest(e,thisform) {
    function callback() {
        generateOrderByCustomerNew(e,thisform);
    }
    let amount = jQuery('#estimated_budgets').val();
    let flag = (jQuery("#is_guaranted").prop('checked')) ? 1 : 0;
    freelinguist_show_fee_box('customer_creating_contest',amount,flag,null,callback);
    return false;
}

//code-bookmark-js shown before milestones are acted on in the projects
function wrap_wallet_hz_manage_milestone( mid, message_confirm, yes, no, fight ) {
    let that_context = this;
    function callback() {
        let call_me_ishmael = hz_manage_milestone.bind(that_context);
        call_me_ishmael(mid, message_confirm, yes, no, fight);
    }
    if (fight ==='approve') {

        freelinguist_show_fee_box('customer_approving_milestone',null,null,mid,callback);
        return false;
    } else if (fight === 'hire_mediator') {
        freelinguist_show_fee_box('freelancer_asking_project_mediation',null,null,mid,callback);
        return false;
    }

}

//code-bookmark-js shown a job is started, to the freelancer
function wrap_wallet_hz_start_job(act, job_id, message) {
    let that_context = this;
    function callback() {
        let call_me_ishmael = hz_start_job.bind(that_context);
        call_me_ishmael(act, job_id, message);
    }

    if (act === 'start') {
        freelinguist_show_fee_box('freelancer_starting_job',null,null,null,callback);
        return false;
    }
}