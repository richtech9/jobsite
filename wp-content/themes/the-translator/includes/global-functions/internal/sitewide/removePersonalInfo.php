<?php



/*

 * Author Name: Lakhvinder Singh

 * Method:      removePersonalInfo

 * Description: removePersonalInfo

 *

 */

function removePersonalInfo($string){

    /*
     * current-php-code 2020-Oct-07
     * internal-call
     * input-sanitized :
     */

    $pattern = "/[^@\s]*@[^@\s]*\.[^@\s]*/";

    $replacement = "[removed]";

    $string = preg_replace($pattern, $replacement, $string);

    $string = preg_replace("/[a-zA-Z]*[:\/\/]*[A-Za-z0-9\-_]+\.+[A-Za-z0-9\.\/%&=\?\-_]+/i", $replacement, $string);

    $message = preg_replace('/\+?\d{9,13}/', '[removed]', $string);

    $message  = strip_tags($message);

    return $message; //task-future-work these regular expression filters need need to be fixed up a lot

}