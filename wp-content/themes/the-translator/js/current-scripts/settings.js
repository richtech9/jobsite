
jQuery(function($) {
    $("form#email_notification_settings_form").validate({


        submitHandler: function (/*form*/) {

            update_user_email_notifications();
            return false;

        }

    });

    function update_user_email_notifications(){
        var data = jQuery('#email_notification_settings_form').serializeArray();

        data.push({'name': 'action', 'value': 'email_send_all_notifications'});


        $.post(adminAjax.url, data, function(response_raw) {

            /**
             * @type {FreelinguistBasicAjaxResponse} response
             */
            let response = freelinguist_safe_cast_to_object(response_raw);
            if (response.status === true) {
                jQuery("span.email_send_all_notifications_status").html(response.message).removeClass('alert-warning').addClass('alert alert-success');
            } else {
                jQuery("span.email_send_all_notifications_status").html(response.message).removeClass('alert-success').addClass('alert alert-warning');
                will_handle_ajax_error('Setting Email Notifications',response.message);
            }
        });

    }
});

