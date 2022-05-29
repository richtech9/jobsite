<?php

/*

Font Awesome icons

*/

function freelinguist_fa_icons($key = ''){
    /*
     * current-php-code 2020-Oct-07
     * internal-call
     * input-sanitized :
     */
    $icons = array(

        'approve' => 'fa fa-check',

        'requested' => 'fa fa-clock-o',

        'reject' => 'fa fa-share-square-o',

        'dispute' => 'fa fa-question-circle',

        'approved_rejection' => 'fa fa-check'

    );

    return $icons[$key];

}