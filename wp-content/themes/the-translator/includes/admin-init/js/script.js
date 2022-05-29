
/*
 * Author Name: Lakhvinder Singh
 * Method: 		remove_file_by_admin_file
 * Description: remove_file_by_admin_file
 *
 */
//code-bookmark-js when in the post edit page there is an option to delete a file associated with this post
function remove_file_by_admin_file(attach_id){
	var r = confirm("Do you really want to delete this file?");
	if (r === true) {
		var data = {'action': 'remove_file_by_admin_file','attach_id': attach_id};
		jQuery.post(ajaxurl, data, function(response) {
			if(response === 'success'){
				alert('You have successfully deleted file.');
				window.location.reload(true);
			}else{
				alert('unauthorized user.');
			}
		});
	} else {
	   return false;
	} 
}



/*
 * Author Name: Lakhvinder Singh
 * Method:      getToCountryBySuperAdmin
 * Description: getToCountryBySuperAdmin
 *
 */
//code-bookmark-js get country information and control in the admin coordination page
function getToCountryBySuperAdmin(valueis){
    var data ={'action': 'getToCountryBySuperAdmin','country_from' : valueis};
    jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            global: false,
            success: function(response){
                jQuery(".country_to_div").html(response); 
            }
        }); 
}

/*
 * Author Name: Lakhvinder Singh
 * Method:      getToProcessingIdBySuperAdmin
 * Description: getToProcessingIdBySuperAdmin
 *
 */
//code-bookmark-js get processing information in the admin coordination page
function getToProcessingIdBySuperAdmin(valueis){
    var data ={'action': 'getToProcessingIdBySuperAdmin','from_processing_id' : valueis};
    jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            global: false,
            success: function(response){
                jQuery(".to_processing_id_div").html(response); 
            }
        }); 
}


/*
 * Author Name: Lakhvinder Singh
 * Method:      getCoverageToAlphabets
 * Description: To get translate language
 *
 */
//code-bookmark-js to get the country dropdown box for the wp admin user profile page
function getAssingCountryTo(valueis){
    var data ={'action': 'getAssignCountryTo','from_index' : valueis};
    jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            global: false,
            success: function(response){
                jQuery(".country_assign_to_div").html(response); 
            }
        }); 
}



