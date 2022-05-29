<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      change_transaction_id

 * Description: change the format of Transaction id/comment id

 *

 */

function change_transaction_id( $user_id, $job_id , &$da_number) {

    /*
     * current-php-code 2020-Jan-3
     * internal-call
     * input-sanitized :
     */

    //code-notes this was simplified from before
    $time = time();

    if($job_id == false || empty($job_id)){

        $id_format = $time . '-u-'.$user_id;

    }else{

        $id_format = $time . '-p-'.$user_id;

    }

    $da_number = $time;

    return $id_format;

}