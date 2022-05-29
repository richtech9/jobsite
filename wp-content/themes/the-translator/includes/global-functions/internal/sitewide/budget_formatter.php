<?php

/*

Project Budget Formatter

*/

function budget_formatter($string = ''){

    /*
     * current-php-code 2020-Oct-7
     * internal-call
     * input-sanitized :
    */

    if (empty($string)) {

        return false;

    }

    $budget = explode('_', $string);
    if (count($budget) === 0) {
        return '';
    } elseif (count($budget) === 1) {
        $min = $budget[0];
        return '$'. $min ;
    } else {
        $min = $budget[0];
        $max = $budget[1];
        return '$'. $min . '-' . $max;
    }







}