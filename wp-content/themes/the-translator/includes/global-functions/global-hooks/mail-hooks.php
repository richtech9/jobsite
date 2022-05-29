<?php
/** @noinspection PhpIncludeInspection */
require_once(get_template_directory() . '/includes/ejabber/vendor/autoload.php');
use Cielu\Ejabberd\EjabberdClient;

/*
     * current-php-code 2020-Jan-11
     * current-hook
     * input-sanitized :
     */

//code-notes This defines an email address that will not be sent out, used to send other emails to a queue by adding this as a bcc
define("FREELINGUIST_DUMMY_BCC_FOP_QUEUE",'dummy@peerok.com');

add_action('wp_loaded',function() {
    //code-notes add a filter which is called early, that can set the ssl for smtp. There are several places this needs to be added
    add_action( 'phpmailer_init', 'freelinguist_phpmailer_init' );
    add_action( 'gdmaq_queue_phpmailer_init', 'freelinguist_phpmailer_init' );
    add_action( 'gdmaq_phpmailer_prepare_engine', 'freelinguist_phpmailer_init' );
    add_action( 'gdmaq_mailer_init', 'freelinguist_phpmailer_init' );
    add_action( 'gdmaq_phpmailer_ready_to_send', 'freelinguist_phpmailer_init' );

    //code-notes the email queue plugin has a filter which allows us to remove some copies before they are queued.
    add_filter('gdmaq_mail_to_queue_args','freelinguist_pluck_bcc_from_mail_queue');

    //code-notes add custom action handler to allow broadcasts to be scheduled
    add_action( 'freelinguist_broadcast_admin_ejabber', 'freelinguist_broadcast_admin_ejabber',10,2 );

});

/**
 * code-notes used to remove the dummy bcc email from the email queue
 * @param array $args
 * @return array
 */
function freelinguist_pluck_bcc_from_mail_queue($args) {

    /*
     * current-php-code 2020-Oct-17
     * internal-call
     * input-sanitized :
    */

    $copy_args = $args;
    unset($copy_args['plain']);
    unset($copy_args['html']);
    unset($copy_args['attachments']);

//    will_send_to_error_log("gdmaq_mail_to_queue_args filter hook called. Original is  ",$copy_args);
    if (empty($args) || !isset($args['to']) || empty($args['to'])) {return $args;}
    $new_to = [];
    foreach ($args['to'] as $address) {

        if (is_array($address)) {
            $temp_check = implode("|",$address);
        } else {
            $temp_check = $address;
        }
        if ( strpos($temp_check,FREELINGUIST_DUMMY_BCC_FOP_QUEUE) === false  ) {
            $new_to[] = $address;
        }
    }
    $args['to'] = $new_to;
//    will_send_to_error_log("gdmaq_mail_to_queue_args filter hook Finished with: ",$args['to']);
    return $args;
}

//code-notes function is used to add ssl flags, and/or logging when option is set in the PeerOK options page
function freelinguist_phpmailer_init( &$phpmailer) {

    /*
     * current-php-code 2020-Oct-17
     * internal-call
     * input-sanitized :
    */
    $b_add_ssl_flags =(int) get_option('skip_mail_ssl_verification',0);

    if ($b_add_ssl_flags) {
//        will_send_to_error_log("skip_mail_ssl_verification is set! : ".current_action());
        //check to see if the following flags are added, if not add them
        if (!property_exists($phpmailer,'SMTPOptions')) {
            will_send_to_error_log("adding on property of SMTPOptions: ".current_action());
            $phpmailer->SMTPOptions = [];
        } else {
            if (!is_array($phpmailer->SMTPOptions)) {
                will_send_to_error_log("SMTPOptions is already set and not an array! : ".current_action(),$phpmailer->SMTPOptions);
                return;
            }
        }
        if (!array_key_exists('ssl',$phpmailer->SMTPOptions)) {
            $phpmailer->SMTPOptions['ssl'] = [];
        }
        if (!is_array($phpmailer->SMTPOptions['ssl'])) {
//            will_send_to_error_log("SMTPOptions['ssl'] is not an array! : ".current_action(),$phpmailer->SMTPOptions['ssl']);
            return;
        }
        $phpmailer->SMTPOptions['ssl']['verify_peer'] = false;
        $phpmailer->SMTPOptions['ssl']['verify_peer_name'] = false;
        $phpmailer->SMTPOptions['ssl']['allow_self_signed'] = true ;

//        will_send_to_error_log("added ssl options",$phpmailer->SMTPOptions);
    }

    $b_log_talk = (int)get_option('log_smtp_connections',0);
    if ($b_log_talk) {
//        will_send_to_error_log("log_smtp_connections is set! : ".current_action());
        $phpmailer->SMTPDebug = 4;
        $phpmailer->Debugoutput = 'error_log';
    }
}

function freelinguist_broadcast_admin_ejabber($message, $subject) {

    /*
     * current-php-code 2020-Oct-17
     * internal-call
     * input-sanitized :
    */
    $options = get_option('xmpp_settings');
    $host = empty($options['xmpp_domain'])?'': $options['xmpp_domain'];
    $uri = empty($options['xmpp_api_address']) ? '' : $options['xmpp_api_address'];
    $type = empty($options['xmpp_token_type']) ? '' : $options['xmpp_token_type'];
    $code = empty($options['xmpp_auth_code']) ? '' : $options['xmpp_auth_code'];

    if (empty($host)) {
        will_send_to_error_log("Chat url is not set in options! Cannot send broadcast");
        return;
    }
    try {

        //will_send_to_error_log("subject for broadcast call ",$subject,true,true,true);
        //will_send_to_error_log("message for broadcast call ",$message,true,true,true);
        $restClient = new EjabberdClient([
            'baseUri' => $uri, // must use http or https
            'authorization' => $type . " " . $code
        ]);
        $url_for_broadcast = $host . '/announce/online';
     //   $res =
            $restClient->sendMessage('admin@' . $host,
            $url_for_broadcast,
            $subject,
            $message,
            'headline'
        );
      //  will_send_to_error_log('jabber /announce/online api says',[$url_for_broadcast,$res],false,false);
    } catch (Exception $e) {
        will_send_to_error_log("freelinguist_broadcast_admin_ejabber cannot create client: ", [$e->getMessage(),$e->getCode(),get_class($e)],false,true);

    }
}
