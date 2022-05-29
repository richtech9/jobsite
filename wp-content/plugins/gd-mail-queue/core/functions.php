<?php

if (!defined('ABSPATH')) { exit; }

/** @return PHPMailer */
function gdmaq_get_phpmailer_global() {
    global $phpmailer;

    if (!($phpmailer instanceof PHPMailer)) {
        require_once(ABSPATH.WPINC.'/class-phpmailer.php');
        require_once(ABSPATH.WPINC.'/class-smtp.php');

        $phpmailer = new PHPMailer(true);
    }

    return $phpmailer;
}

function gdmaq_htmlfy_content($args = array(), $atts = array()) {
    $defaults = array(
        'subject' => '',
        'plain' => ''
    );

    $args = wp_parse_args($args, $defaults);

    return gdmaq_htmlfy()->htmlfy_content($args['plain'], $args['subject'], $atts);
}

function gdmaq_default_from() {
    $sitename = strtolower( $_SERVER['SERVER_NAME'] );

    if (substr( $sitename, 0, 4 ) == 'www.') {
        $sitename = substr($sitename, 4);
    }

    return array(
        'email' => 'wordpress@'.$sitename,
        'name' => get_option('blogname'));
}

function gdmaq_flat_email_from_array($in) {
    $out = array();

    foreach ($in as $email) {
        $out[] = $email[0].(!empty($email[1]) ? ' <'.$email[1].'>' : '');
    }

    return $out;
}

function gdmaq_normalize_email($in) {
    $con = array('email' => '', 'name' => '');

    if (is_string($in)) {
        $con['email'] = $in;
    } else if (is_array($in)) {
        $con['email'] = isset($to['email']) ? $in['email'] : $in[0];
        $con['name'] = isset($to['name']) ? $in['name'] : $in[1];
    }

    return $con;
}

function gdmaq_mail_to_queue($args = array()) {
    $defaults = array(
        'to' => array(),
        'from' => array(),
        'subject' => '',
        'plain' => '',
        'html' => '',
        'type' => 'mail',
        'headers' => array(),
        'attachments' => array(),
        'extras' => array()
    );

    $args = wp_parse_args($args, $defaults);

    if (!is_array($args['to']) && is_string($args['to'])) {
        $args['to'] = explode(',', $args['to']);
    }
    //code-bookmark gdmaq_mail_to_queue_args filter
    $args = apply_filters('gdmaq_mail_to_queue_args', $args);

    if (empty($args['from'])) {
        $args['from'] = gdmaq_mailer()->get_from();
    }

    $args['extras'] = (object)$args['extras'];

    if (!isset($args['extras']->ContentType)) {
        $args['extras']->ContentType = empty($args['html']) ? 'text/plain' : 'text/html';
    }

    if (!isset($args['extras']->CharSet)) {
        $args['extras']->CharSet = 'UTF-8';
    }

    $args['extras']->From = $args['from']['email'];

    if (!empty($args['from']['name'])) {
        $args['extras']->FromName = $args['from']['name'];
    }

    $item = $args;
    $item['extras'] = json_encode($item['extras']);
    $item['headers'] = json_encode($item['headers']);
    $item['attachments'] = json_encode($item['attachments']);

    unset($item['to'], $item['from']);

    $added = 0;
    foreach ($args['to'] as $to) {
        $_em = gdmaq_normalize_email($to);

        $item['to_email'] = $_em['email'];
        $item['to_name'] = $_em['name'];

        if (!empty($item['to_email'])) {
            gdmaq_db()->add_mail_to_queue($item);

            $added++;
        }
    }

    gdmaq_settings()->update_statistics('mail_to_queue_calls', 1);
    gdmaq_settings()->update_statistics('mails_added_to_queue', $added);

    gdmaq_settings()->update_statistics_for_type($item['type'], 'mail_to_queue_calls', 1);
    gdmaq_settings()->update_statistics_for_type($item['type'], 'mails_added_to_queue', $added);

    gdmaq_settings()->save('statistics');
}
