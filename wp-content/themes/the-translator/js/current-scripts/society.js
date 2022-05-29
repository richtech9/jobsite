
jQuery(function ($) {

    jQuery('.fl-society-generate-user-referral-code').click(function(){
        var data = {
            'action': 'fl_generate_user_referral_code'
        };

        $.post(getObj.ajaxurl,
            data,
            /**
             *
             * @param {string} response_raw
             */
            function (response_raw) {
                /**
                 * @type {FreelinguistGenerateReferralResponse} response
                 */
                let response = freelinguist_safe_cast_to_object(response_raw);
                if (response.status) {
                    $('span.fl-society-referral-code-title').text(response.message);
                    $('span.fl-society-referral-code-shown').text(response.referral_code).removeClass('fl-society-referral-no-code-shown');
                    $('button.fl-society-generate-user-referral-code').hide();
                } else {
                    will_handle_ajax_error('Generating Referral Code',response.message);
                }

                console.debug(response);


            });
    });
});