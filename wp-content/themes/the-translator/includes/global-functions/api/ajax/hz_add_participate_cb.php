<?php

add_action( 'wp_ajax_hz_add_participate',  'hz_add_participate_cb'  );

/**
 * code-notes Only when the user to be added is the same as the logged in user
 */
function hz_add_participate_cb(){

    /*
   * current-php-code 2020-Oct-10
   * ajax-endpoint  hz_add_participate
   * input-sanitized : contestId,lang, linguId,contestId
   */
    try {
        $lang = FLInput::get('lang', 'en');
        $linguId = (int)FLInput::get('linguId');
        $contestId = (int)FLInput::get('contestId');


        $author = (int)get_post_field('post_author', $contestId);

        $allparticipants = get_post_meta($contestId, 'all_contest_paricipants', true);

        $lingPresent = explode(',', $allparticipants);
        $url =add_query_arg(  ['action'=> 'proposals','lang'=>$lang], get_permalink($contestId));

        if (get_current_user_id() !== $linguId) {
            throw new RuntimeException("Can only add yourself to a contest, not another user");
        }
        $msg = '';
        if ($linguId === $author) {
            throw new RuntimeException('You Cannot Participate On Your Own Contest .');
        }


        if (in_array($linguId, $lingPresent)) {
            throw new RuntimeException('Linguist already awarded for this contest.');
        }

        if ($allparticipants != '') {

            $allParts = $allparticipants . ',' . $linguId;

            if (update_post_meta($contestId, 'all_contest_paricipants', $allParts)) {
                $msg = 'yes';
            }
            FLPostLookupDataHelpers::add_user_lookup_participant($contestId, $linguId);

        } else {

            $allParts = $linguId;

            if (update_post_meta($contestId, 'all_contest_paricipants', $allParts)) {
                $msg = 'yes';
            }
            FLPostLookupDataHelpers::add_user_lookup_participant($contestId, $linguId);

        }
        wp_send_json( ['status' => true, 'message' => $msg,'url'=>$url]);
        exit;



    } catch (Exception $e) {
        will_send_to_error_log('hz_add_participate',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage(),'url'=>null]);
    }



}