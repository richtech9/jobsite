<?php

/*
* current-php-code 2020-Nov-12
* input-sanitized : content_id
* current-wp-template:  favorite button for content for customer view
*/

/**
 * set_query_var( 'for_content_id', XXX );
 *     // optional set to content id instead of using http input
 * @usage get_template_part('includes/user/contentdetail/contentdetail', 'customer-button-favorite');
 */


if (!get_current_user_id()) {
    return; //do not display the favorite button if the user is not logged in
}

$content_id_encoded = FLInput::get('content_id', 0);
$content_id_from_http =  FreelinguistContentHelper::decode_id($content_id_encoded);

if (isset($for_content_id)) {
    $content_id = (int)$for_content_id;
} else {
    $content_id = $content_id_from_http;
}
if (!$content_id) {return;}

$favorite_contents_as_comma_delimited_string = get_user_meta(get_current_user_id(), '_favorite_content', true);
//is cached so okay to always call

$favorite_contents_as_string_id_array = explode(',', $favorite_contents_as_comma_delimited_string);
$favorite_content_ids = [];
foreach ($favorite_contents_as_string_id_array as $ooh_i_am_a_string) {
    $maybe_int = (int)$ooh_i_am_a_string;
    if ($maybe_int) {
        $favorite_content_ids[] = $maybe_int;
    }
}
if (in_array($content_id, $favorite_content_ids)) {
    $b_is_fav = true;
    $fav_title = "Remove from Favorites";
} else {
    $b_is_fav = false;
    $fav_title = "Add To Favorites";
}

$job_type = 'content';

?>

<div class="freelinguist-heart <?=($b_is_fav ? 'freelinguist-heart-active' : ''); ?>"
     title = "<?= $fav_title ?>"
>
    <span>
        <i
                class="fa fa-heart<?php echo(!$b_is_fav ? '-o' : '');?> fa-favorite larger-text"
                data-fav="<?= ($b_is_fav? '0': '1') ?>"
                data-job_id="<?= $content_id ?>"
                data-id="<?= $content_id ?>"
                data-c_type="<?= $job_type ?>"
                data-login="<?= get_current_user_id() ?>"
        ></i>
    </span>
</div>


