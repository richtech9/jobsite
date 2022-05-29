<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      change_the_pending_job_id

 * Description: change the format of job id

 *

 */

function change_the_pending_job_id($id,&$da_number){
    /*
     * current-php-code 2020-Oct-07
     * internal-call
     * input-sanitized :
     */
    will_do_nothing($id);
    $temporary_job_id = empty(get_option('temporary_job_id')) ? 1 : get_option('temporary_job_id');

    $new_temp_id = $temporary_job_id+1;
    $da_number = $new_temp_id;
    update_option('temporary_job_id',$new_temp_id);

    $number_of_digits = strlen($new_temp_id);

    if($number_of_digits == 1){

        $new_temp_id = '000'.$new_temp_id;

    }elseif($number_of_digits == 2){

        $new_temp_id = '00'.$new_temp_id;

    }elseif($number_of_digits == 3){

        $new_temp_id = '0'.$new_temp_id;

    }

    $hexa_inc =  substr($new_temp_id, 0, -4);

    $string     = 'AAAA';

    $last_digit = substr($new_temp_id, -4);

    for($i=0;$i<($hexa_inc);$i++){

        $string++;

    }

    $job = 'T_'.$string.$last_digit;

    return $job;

}