<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      amount_format

 * Description: amount_format

 *

 */


/**
 * @param $foo
 * @return string
 */
function amount_format($foo){

    /*
     * current-php-code 2020-Oct-7
     * internal-call
     * input-sanitized :
    */
    return number_format((float)$foo, 2, '.', '');

}