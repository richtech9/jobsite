<?php



/*

 * Author Name: Lakhvinder Singh

 * Method:      the_title_trim

 * Description: the_title_trim

 *

 */

add_filter('the_title', 'post_title_trim');

function post_title_trim($title){

    $pattern[0] = '/Protected:/';

    $pattern[1] = '/Private:/';

    $replacement[0] = ''; // Enter some text to put in place of Protected:

    $replacement[1] = ''; // Enter some text to put in place of Private:



    return preg_replace($pattern, $replacement, $title);

}