<?php

add_action( 'wp_ajax_fl_content_new_chapter_content_form_part',  'fl_content_new_chapter_content_form_part'  );

/**
 * code-notes Only when the user to be added is the same as the logged in user
 */
function fl_content_new_chapter_content_form_part(){

    /*
   * current-php-code 2021-March-29
   * ajax-endpoint  fl_content_new_chapter_content_form_part
   * input-sanitized : words
   */
    try {

        $words = FLInput::get('words', '', FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);

        $b_convert_to_bb_code = (int)FLInput::get('b_convert_to_bb_code');
        //task-future-work convert to bb_code from html

        ob_start();
        set_query_var( 'content_chapter_id', 0 );
        set_query_var( 'content_chapter_words', $words );
        get_template_part('includes/user/contentdetail/contentdetail', 'bb-code-editor');
        $form = ob_get_clean();

        wp_send_json( [
            'status' => true,
            'message' => $form
        ]);

        exit;



    } catch (Exception $e) {
        will_send_to_error_log('fl_content_new_chapter_content_form_part',will_get_exception_string($e));
        wp_send_json( ['status' => false, 'message' => $e->getMessage()]);
    }



}