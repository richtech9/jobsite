<?php

/*
* current-php-code 2020-Dec-9
* input-sanitized : content_id
* current-wp-template:  favorite button for user
*/

/**
 * makes a favorite heart control for the current user to mark the for_user_id on and off
 * works by using the fa-favorite in the heart, which clicked, triggers existing javascript handlers,
 * so just need to setup the data attributes and we are good to go
 *  (and define the css to work anywhere)
 *
 *  set_query_var( 'for_user_id', XXX );
 *     // required set to user id you want this button to be for
 *
 * @usage  get_template_part('includes/user/author-user-info/translator', 'button-favorite');
 */
if (isset($for_user_id)) {
    $for_user_id = (int)$for_user_id;
} else {
    will_send_to_error_log("Cannot show the user favorite button, the for_user_id was not set in the quary vars");
    return; //bug out if do not have the info!!
}

if (!$for_user_id) {
    will_send_to_error_log("Cannot show the user favorite button, the for_user_id was set in the quary vars, but not numeric");
    return; //bug out if do not have the info!!
}

if (!get_current_user_id()) {
    return; //do not display the favorite button if the user is not logged in
}

$favorite_users_as_comma_delimited_string = get_user_meta(get_current_user_id(), '_favorite_translator', true);
//is cached so okay to always call

$favorite_users_as_string_id_array = explode(',', $favorite_users_as_comma_delimited_string);
$favorite_user_ids = [];
foreach ($favorite_users_as_string_id_array as $ooh_i_am_a_string) {
    $maybe_int = (int)$ooh_i_am_a_string;
    if ($maybe_int) {
        $favorite_user_ids[] = $maybe_int;
    }
}
if (in_array($for_user_id, $favorite_user_ids)) {
    $b_is_fav = true;
    $fav_title = "Remove from Favorites";
} else {
    $fav_title = "Add To Favorites";
    $b_is_fav = false;
}

$job_type = 'translator';

?>

<div class="freelinguist-heart <?=($b_is_fav ? 'freelinguist-heart-active' : ''); ?>"
    title = "<?= $fav_title ?>"
>
    <span>
        <i
                class="fa fa-heart<?php echo(!$b_is_fav ? '-o' : '');?> fa-favorite larger-text"
                data-fav="<?= ($b_is_fav? '0': '1') ?>"
                data-job_id="<?= $for_user_id ?>"
                data-id="<?= $for_user_id ?>"
                data-c_type="<?= $job_type ?>"
                data-login="<?= get_current_user_id() ?>"
        ></i>
    </span>
</div>
