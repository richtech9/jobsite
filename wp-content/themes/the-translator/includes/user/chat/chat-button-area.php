<?php
/*
* current-php-code 2021-Feb-11
* input-sanitized :
* current-wp-template:  displays chat button
*/
/**
 * @usage
 *
 * required settings
 *  set_query_var( 'job_id', '87995' ); //the number id of the content, competition or project
 *  set_query_var( 'to_user_id', '5877' );
 *  set_query_var( 'job_type', 'content|competition|project' );
 *
 *  sometimes required
 * set_query_var( 'fl_job_id', '555' ); //the number id of project's job, not to be confused with the job_id above, needed when this is a project
 * set_query_var( 'proposal_id', '555' ); //the number id of competition's proposal,  needed when this is a competition
 *
 * optional settings
 *  set_query_var( 'b_show_name', 0 );
 *     // if missing will show the name
 *     // set to 0 or empty string to hide the name
 *
 * set_query_var( 'b_name_left', 0 );
 *     // if missing will show the name to the left
 *     // set to 0 or empty string to show the name on the right
 *
 * set_query_var( 'b_name_top', 1 );
 *      //only if name is left
 *      //if not set or empty then ignored
 *
 *  * set_query_var( 'b_name_bottom', 1 );
 *      //only if name is right
 *      //if not set or empty then ignored
 *
 * project_id,freelancer_id, project_type = 'project', prefix=''
 *
 *
 * get_template_part('includes/user/chat/chat', 'button-area');
 */
global $wpdb;
if (!isset($job_id) || empty(trim($job_id)) || !intval($job_id)) {
    throw new RuntimeException("job_id needs to be set for the chat-button-area using set_query_var ");
}

$job_id = (int)$job_id;


if (isset($to_user_id)) {
    $to_user_id = (int)$to_user_id;
} else {
    throw new RuntimeException("to_user_id needs to be set for the chat-button-area using set_query_var ");
}

if (!isset($job_type) || empty(trim($job_type))) {
    throw new RuntimeException("job_type needs to be set for the chat-button-area using set_query_var ");
}

if (isset($b_show_name)) {
    $b_show_name = (int)$b_show_name;
} else {
    $b_show_name = 1;
}

if (isset($b_name_left)) {
    $b_name_left = (int)$b_name_left;
} else {
    $b_name_left = 1;
}

if (isset($b_name_bottom) && !$b_name_left) {
    $b_name_bottom = (int)$b_name_bottom;
} else {
    $b_name_bottom = 0;
}

if (isset($b_name_top) && $b_name_left) {
    $b_name_top = (int)$b_name_top;
} else {
    $b_name_top = 0;
}


if (isset($proposal_id) && $proposal_id) {
    $proposal_id = (int)$proposal_id;
} else {
    $proposal_id = 0;
}

if (isset($fl_job_id) && $fl_job_id) {
    $fl_job_id = (int)$fl_job_id;
} else {
    $fl_job_id = 0;
}

$content_id = 0;

//get the chat room id and set it in the button
switch ($job_type) {
    case 'content': {
        $content_id = $job_id;
        $sql_chat_room = "SELECT root.chat_room_id , chat.room_id  as room_string_identifier 
                          FROM wp_linguist_content root
                          INNER JOIN wp_fl_chat_rooms chat on root.chat_room_id = chat.id
                          WHERE root.id = $job_id";
        break;
    }
    case 'competition': {
        $sql_chat_room = "SELECT root.chat_room_id, chat.room_id as room_string_identifier  
                          FROM wp_proposals root 
                          INNER JOIN wp_fl_chat_rooms chat on root.chat_room_id = chat.id
                          WHERE root.id = $proposal_id";
        break;
    }
    case 'project': {
        $sql_chat_room = "SELECT root.chat_room_id , chat.room_id as room_string_identifier 
                          FROM wp_fl_job root
                          INNER JOIN wp_fl_chat_rooms chat on root.chat_room_id = chat.id
                          WHERE root.id = $fl_job_id";
        break;
    }
    default: {
        throw new InvalidArgumentException("For the Chat button, need to set the job_type to content|competition|project only");
    }
}

$maybe_chatroom_res = $wpdb->get_results($sql_chat_room);
will_throw_on_wpdb_error($wpdb,'getting chat room id in the chat button area');
//will_send_to_error_log('chat button sql',$sql_chat_room);
if (empty($maybe_chatroom_res)) {
    $chatroom_id = null;
    $room_string_identifier = null;
} else {
    $chatroom_id = $maybe_chatroom_res[0]->chat_room_id;
    $room_string_identifier = $maybe_chatroom_res[0]->room_string_identifier;
}


if (is_user_logged_in()) {
    $options = get_option('xmpp_settings');
    $prefix = '';
    if (array_key_exists('xmpp_prefix', $options)) {
        $prefix = $options['xmpp_prefix'];
    }
    $da_name = get_da_name($to_user_id);

    ?>
    <div class="fl-chat-button-area">

        <?php if ($b_show_name && $b_name_left) { ?>
            <span class="fl-user-display-name fl-user-display-name-left <?= ($b_name_top? 'fl-user-display-name-top':'')?>">
                <?= $da_name ?>
            </span>
        <?php } ?>

        <button
           onclick="return show_chat_room(this,'<?=$room_string_identifier ?>','<?=$job_id ?>', <?= $to_user_id?>,'<?= $job_type?>','<?= $prefix?>')"
           class="red-btn-no-hover fl-chat-button" type="button"
           data-chatroom_id = "<?= $chatroom_id?>"
           data-fl_job_id = "<?= $fl_job_id?>"
           data-proposal_id = "<?= $proposal_id?>"
           data-content_id = "<?= $content_id?>"
        >
            <?php get_custom_string('Chat'); ?>

        </button>

        <?php if ($b_show_name && !$b_name_left) { ?>
            <span class="fl-user-display-name fl-user-display-name-right <?= ($b_name_top? 'fl-user-display-name-bottom':'')?>">
                <?= $da_name ?>
            </span>
        <?php } ?>

    </div>
    <?php
}