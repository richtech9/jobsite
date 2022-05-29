<?php

// this function is used for to calculate the rating of translator

//code-bookmark function to display average rating stars
function translater_rating($user_id,$width,$role,$b_double_line= true,$value_provided = null){

    /*
     * current-php-code 2020-Oct-07
     * internal-call
     * input-sanitized :
     */
    switch($role) {
        case 'translator': {
            $role_meta = 'average_rating_freelancer_role';
            break;
        }
        case 'customer'    : {
            $role_meta = 'average_rating_customer_role';
            break;
        }
        default: {
            throw new RuntimeException("Did not expect role of [$role] expected translator|customer");
        }
    }
    if ($value_provided) {
        $rating = $value_provided;
    } else {
        $rating = get_user_meta($user_id,$role_meta,true);
    }

    if(empty($rating)) { $rating = 0;}


    if ($b_double_line) {
        if (empty($rating)) {

            $result_rating = get_custom_string_return('No Rating');

        } else {
            $rating = round(number_format($rating, 2));
            $result_rating = ' ' . $rating . ' ' . get_custom_string_return('Rating');
        }
    } else {
        $result_rating = '';
    }

    $data = '';

    //code-notes including rate-yo css in the regular wp style section
    $data .= '<div class="rateyo_combine"><div class="rateyo-readonly-widg'.$user_id.'"></div>'.$result_rating.'</div>';



    $data .= '<script>

                      jQuery(function () {

                        var rating = '.$rating.'; //point A

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