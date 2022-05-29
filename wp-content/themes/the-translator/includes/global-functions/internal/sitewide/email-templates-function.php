<?php
//code-notes added a new param $b_use_dummy_bcc to by pass queue
function emailTemplateForUser($email,$template_id,$variables,$attachments = array(),$b_use_dummy_bcc = true){
    /*
     * current-php-code 2020-Oct-07
     * internal-call
     * input-sanitized : lang
     */

    $lang = FLInput::get('lang'.'en');


    /* get Email template and send email */

    $get_email_template_data = get_email_template($template_id);
    if (empty($get_email_template_data)) {
        will_send_to_error_log("emailTemplateForUser: no such template id, so will not email ",$template_id);
        return;
    }

    $htmlContent = '
                    <html>
                    <head>
                        <title>'.get_option('blogname','').'</title>
                        <meta http-equiv="Content-Type"  content="text/html charset=UTF-8" >
                        <meta charset="UTF-8">
                    </head>
                    <body>'.$get_email_template_data->content.
                    '</body>
                    </html>';

    $variables['header'] = /** @lang text */
        '
            <div class="logo" style="">
                <a href="'.get_site_url().'" target="_blank" style="text-decoration: none">
                    <img src="'.get_template_directory_uri().'/images/logo-1000-by-200.png'.'" alt="" height="35" border="0" >
                </a>
            </div>
            ';

    $variables['footer'] = /** @lang text */
        '
    <div style="text-align: center; font-size: 13px;padding-top: 52px">
     If you do not want to receive these emails from PeerOK. Please visit your
        <a href="'.get_site_url()."/setting/?lang=$lang#notifications".'" target="_blank">
            email settings page
         </a>
      </div>
    ';

    $variables['site_url'] = get_site_url();
    $variables['logo_url'] = get_template_directory_uri().'/images/logo-1000-by-200.png';
    $variables['notification_settings_url'] = get_site_url()."/setting/?lang=$lang#notifications";
    $variables['login_url'] = get_site_url()."/login/?lang=$lang";

    $subject = $get_email_template_data->subject;
    $body    = stripslashes($htmlContent);
    foreach($variables as $key => $value){
        if($key == 'activation_link'){
            $value_anchor = "<a href='".$value."'>Click here to activate account</a>";
            $body = str_replace('{{'.$key.'}}', $value_anchor, $body);
        }else{
            $body = str_replace('{{'.$key.'}}', $value, $body);
        }
    }


    $subject     = " PeerOK | $subject";
    $headers = '';
    //$headers     = 'MIME-Version: 1.0'."\r\n";
    $headers    .= 'Content-type: text/html; charset=UTF-8'."\r\n";
    if( $_SERVER['SERVER_NAME'] == 'www.wenren8.com'){
        $from_email = 'technical@wenren8.com';
    }else{
        $from_email = 'no-reply@peerok.com';//code-notes changed from address from 'technical@peerok.com';
    }
    $headers    .= "From: $from_email";     
    //$mail_sent=mail($to, $strSubject, $var, $headers);
    //code-notes add bcc FREELINGUIST_DUMMY_BCC_FOP_QUEUE if $b_use_dummy_bcc
    if ($b_use_dummy_bcc) {
        $headers .= "\r\n". 'Bcc: '. FREELINGUIST_DUMMY_BCC_FOP_QUEUE;
        //will_send_to_error_log('adding in bcc header. ',$headers );
    }
    wp_mail( $email, $subject, $body,$headers,$attachments); 

}


function send_custom_message($email,$subject,$body,$attachments = array()){    
    $subject     = "PeerOK | $subject";
    $headers     = '';
    //$headers     = 'MIME-Version: 1.0'."\r\n";
    $headers    .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
    if( $_SERVER['SERVER_NAME'] == 'www.wenren8.com'){
        $from_email = 'technical@wenren8.com';
    }else{
        $from_email = 'technical@peerok.com';
    }
    $headers    .= "From: $from_email";     
    //$mail_sent=mail($to, $strSubject, $var, $headers); 
    wp_mail( $email, $subject, $body,$headers,$attachments); 
}

function get_email_template($template_id){
    global $wpdb;  
    $edit_data = $wpdb->get_row(
        "SELECT * FROM wp_email_templates WHERE id = $template_id" );
    return $edit_data;
}
