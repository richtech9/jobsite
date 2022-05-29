<?php

function job_rating($rating){
    /*
     * current-php-code 2020-Oct-05
     * internal-call
     * input-sanitized :
     */
    $data = '';

    $data .= '<table class="star-table">

                <tr>

                    <td valign="top">

                        <div id="tutorial-1">                            

                            <ul>';

    for($i=1;$i<6;$i++){

        if($i <= $rating){

            $data .= '<li class="selected large-text">&#9733;</li>';

        }else{

            $data .= '<li class=" large-text">&#9733;</li>';

        }

    }

    $data .= '              <ul>

                        </div>

                    </td>

                </tr>

            </table>';

    return $data;

}