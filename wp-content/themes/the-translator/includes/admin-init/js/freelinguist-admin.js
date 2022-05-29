jQuery(function($) {
    //code-bookmark-js when deleting a user file from wp admin user profile page
    jQuery('.deleteProFile').click(function(){
        var r = confirm("Do you really want to delete this file?");
        if (r === true) {
            var data = {'action': 'delete_profile_attachment','attach_id': $(this).attr('id')};
            $.post(ajaxurl, data, function(response_raw) {
                /**
                 * @type {FreelinguistBasicAjaxResponse} response
                 */
                let response = freelinguist_safe_cast_to_object(response_raw);
                if (response.status === true) {
                    location.reload(true);
                } else {
                    will_handle_ajax_error('Delete Profile Attachment', response.message);
                }

            });
        } else {
            return false;
        }

    });

    //code-bookmark-js the input for the search form in the admin homepage tag edit screen
    jQuery(".tag-search").typeahead({
        name: 'id',
        displayKey: 'name',
        source: function (query, process) {

            return jQuery.post(ajaxurl, {action:'get_custom_tags', query: query }, function (data) {
                jQuery('#resultLoading').fadeOut(300);
                data = JSON.parse(data);
                return process(data);
            });
        }, afterSelect: function (item) {
            if(item && item.id){
                jQuery('#send_Homepage_Interest_f input[name=tag_id]').val(item.id);
                jQuery('#send_Homepage_Interest_f input[name=send_interest_btn]').removeAttr('disabled');
            }
        }
    });

    //code-bookmark-js the input for the admin search form above
    $(".tag-search").keydown(function () {
        jQuery('#send_Homepage_Interest_f input[name=send_interest_btn]').attr('disabled','disabled');

    });

    //code-bookmark-js when content admin is deleting content
    jQuery(".delete_content").click(function(){
        var content_id = jQuery(this).data("content_id");
        var data = {
            "action": "hz_con_delete",
            "content_id": content_id,

        };
        jQuery.post(ajaxurl, data, function(response_raw){

            /**
             * @type {FreelinguistBasicAjaxResponse} response
             */
            let response = freelinguist_safe_cast_to_object(response_raw);
            if(response.status === true){
                jQuery("#delete_content_"+content_id).parent().parent().remove();
            }else{
                will_handle_ajax_error(response.message);
            }
        });

    });

    //code-bookmark-js when content admin is showing or hiding content
    jQuery(".show_hide_content").click(function(){
        var content_id = jQuery(this).data("content_id");
        var show_content = jQuery(this).data("show_content");
        var data = {
            "action": "hz_con_show_hide",
            "content_id": content_id,
            "show_content": show_content,

        };
        jQuery.post(ajaxurl, data, function(response){
            if(response.trim()!=="fail"){
                if(parseInt(response.trim()) === 1){
                    jQuery("#show_hide_"+content_id).html("Hide");
                    jQuery("#show_hide_"+content_id).data("show_content",0);
                }else{
                    jQuery("#show_hide_"+content_id).html("Publish");
                    jQuery("#show_hide_"+content_id).data("show_content",1);
                }

            }else{

            }
        });

    });
});