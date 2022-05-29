<?php

/**
 * These three function work together,
 *  the @see date_formatted() is called many times to format a datetime string
 *   it tries to use a timezone, if there is not one set, will add in javascript to send it to the @ajax update_local_timezone
 *      so, the next page that is loaded will have the correct timezone
 */

/**
 * @param $date
 * @return string
 * Author Name: Lakhvinder Singh

 * Method:      date_formatted

 * Description: Format date
 * task-future-work make sure the date coming in is UTC, I think it needs to change to unix timestamp
 */
function date_formatted($date){

    /*
    * current-php-code 2020-Sep-30
    * input-sanitized :
    * internal-call
    */

    if(empty($date)){

        $newDate = "Not Exist";

    }else{

        $dt = new DateTime($date);

        $newDate = $dt->format('Y-m-d');

        $newDate = convert_utc_to_local_time($newDate);

    }

    return $newDate;

}

function convert_utc_to_local_time($utc_date,$format = false){

    /*
    * current-php-code 2020-Sep-30
    * internal-call
    * input-sanitized :
    */

    if($format == false){

        $format = 'Y-m-d';

    }
    if(!isset($_SESSION['session_time_zone']) || empty($_SESSION['session_time_zone'])){

        set_local_timezone();
        $timezone_offset_minutes = 0;
    } else {
        $timezone_offset_minutes = $_SESSION['session_time_zone'];
    }


    // Convert minutes to seconds

    $timezone_name = timezone_name_from_abbr("", $timezone_offset_minutes*60, false);

    $date = new DateTime($utc_date);

    $date->setTimezone(new DateTimeZone($timezone_name)); // +04

    return $date->format($format); // 2012-07-15 05:00:00


}

/**
 * code-notes this function can be called multiple times for one page, before the session var is set, so use flag to mark if one call made
 * task-future-work replace the ajax call with simple browser conversion of timestamp to local datetime strings in the js, simpler, less error prone and no bugs
 */
function set_local_timezone(){

    /*
    * current-php-code 2020-Sep-30
    * internal-call
    * input-sanitized :
    */

    if(( !isset($_SESSION['session_time_asking']) || empty($_SESSION['session_time_asking']) )&&
        (!isset($_SESSION['session_time_zone']) || empty($_SESSION['session_time_zone']))){

        $_SESSION['session_time_asking'] = time();
        ?>


        <script type="text/javascript">

            var timezone_offset_minutes = new Date().getTimezoneOffset();

            timezone_offset_minutes = timezone_offset_minutes === 0 ? 0 : -timezone_offset_minutes;

            var data = {'action': 'update_local_timezone','session_time_zone': timezone_offset_minutes};

            jQuery.ajax({

                type: 'POST',

                url: '<?php echo admin_url( "admin-ajax.php" ); ?>',

                data: data,

                global: false,

                success: function(response){

                    console.log(response);

                }

            });

        </script>

        <?php

    }

}