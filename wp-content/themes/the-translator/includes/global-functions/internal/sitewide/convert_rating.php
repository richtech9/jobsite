<?php

function convert_rating($rating,$width,$show = null,$user_id = ''){

    /*
     * current-php-code 2020-Oct-05
     * internal-call
     * input-sanitized :
     */
    if(empty($rating)){

        $result_rating = get_custom_string_return('No Rating');

    }else{
        if($show){
            $result_rating = '';
        }else{
            $result_rating = ' ' .$rating.' '. get_custom_string_return('Rating');
        }


    }

    $data = '';

    //code-notes including rate-yo css in the regular wp style section
    $data .= '<div class="rateyo_combine"><div class="rateyo-readonly-widg'.$user_id.'"></div>'.$result_rating.'</div>';



    $data .= '<script>

                      jQuery(function () {

                        var rating = '.$rating.';

                        jQuery(".rateyo-readonly-widg'.$user_id.'").rateYo({

                          starWidth: "'.$width.'px",

                          rating: rating,

                          numStars: 5,

                          precision: 2,

                          minValue: 1,

                          maxValue: 5,

                          readOnly: true

                        })

                        

                      });

                    </script>';



    return $data;

}