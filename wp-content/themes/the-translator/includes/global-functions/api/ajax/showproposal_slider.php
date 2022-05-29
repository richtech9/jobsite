<?php

add_action('wp_ajax_showproposal_slider', 'showproposal_slider');


function showproposal_slider()
{
    /*
    * current-php-code 2020-Oct-1
    * ajax-endpoint showproposal_slider
    * input-sanitized : job_id
    */

    $proposal_id = (int)FLInput::get('proposal_id');
    $linguid = (int)FLInput::get('linguid');
    $post_id = (int)FLInput::get('post_id');

    if ($proposal_id) {



        global $wpdb;
        $uploads = wp_upload_dir();

        $fileUrl = array();

        $up_loads = $wpdb->get_results("SELECT * FROM wp_files WHERE `post_id` = $post_id AND proposal_id = $proposal_id AND by_user=$linguid");

        foreach ($up_loads as $uploaded) {

            $fileUrl[] = $uploads['baseurl'] . '/' . $uploaded->file_path;

        }


        $html = '<div class="carousel-inner">';
        $sl = 1;

        foreach ($fileUrl as $key => $value) {

            $extension = pathinfo($value, PATHINFO_EXTENSION);


            if ($sl == 1):
                $html .= '<div class="item active">';
            else:
                $html .= '<div class="item">';
            endif;
            if ($extension == 'pdf') {
                $html .= '<embed src="' . $value . '" width="100%" height="600" type="application/pdf">';
            } //elseif($extension == 'txt'){ echo '<embed src="'.$value.'" width="100%" height="600" type="text/plain">'; }

            elseif ($extension == 'txt') {
                $html .= '<iframe src="' . $value . '" width="100%" height="600"></iframe>';
            } elseif ($extension == 'mp3') {
                $html .= '<audio controls><source src="' . $value . '" type="audio/mpeg"></audio> ';
            } elseif ($extension == 'doc' || $extension == 'docx') {
                $html .= '<object data="' . $value . '" type="application/msword"></object>';
            } elseif ($extension == 'mp4' || $extension == 'mov' || $extension == '3gp') {

                $html .= '<video width="400" controls>

         <source src="' . $value . '" type="video/mp4">

         Your browser does not support HTML5 video.

         </video>';
            } else {
                $html .= '<img src="' . $value . '" alt="Cannot open ' . $extension . '" style="width:100%;">';
            }


            $html .= '</div>';


            $sl++;

        }
        $html .= '</div>';
        $html .= /** @lang text */
            '<a class="left carousel-control" href="#myCarousel" data-slide="prev">

     <span class="flex-nav-prev"></span>

     </a>

     <a class="right carousel-control" href="#myCarousel" data-slide="next">

     <span class="flex-nav-next"></span>

     </a>';

        echo $html;
        exit;

    } else {

        echo 'Please select proposal';

        exit;

    }

}