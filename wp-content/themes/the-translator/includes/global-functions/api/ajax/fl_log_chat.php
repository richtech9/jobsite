<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      delete_content_chapter

 * Description: delete_content_chapter

 *

 */

add_action('wp_ajax_fl_log_chat',[ 'FreelinguistLogChat','fl_log_chat']);

add_action('init',[ 'FreelinguistLogChat','fl_log_chat_url']);

class FreelinguistLogChat
{
    static function fl_log_chat_url() {
        /*
        * current-php-code 2020-Dec-22
        * ajax-endpoint  fl_log_chat
        * input-sanitized :action,chat_users,page_sessions,time_end,time_start
        */

        global $wpdb;

        $action = FLInput::get('action');
        if ($action !== 'chat_log') {return;}
        try {
            if (!current_user_can('administrator')) {
                throw new RuntimeException("Only admin users can see this url. You are not an admin user");
            }

            $do_truncate = FLInput::get('fl-truncate-logs',null);
            if ($do_truncate) {
                $sql_to_truncate = "Truncate wp_fl_chat_logs";
                $wpdb->query($sql_to_truncate);
            }

            $sql_for_user_sessions = "
                select count(*) as da_count,
                    chat_user_text_id,page_session, floor(min(ts_sent)/1000) as start_ts, floor(max(ts_sent)/1000) as end_ts
                    from wp_fl_chat_logs where 1 group by chat_user_text_id,page_session
                    order by start_ts;
            ";

            $user_sessions_res = $wpdb->get_results($sql_for_user_sessions);
            $all_user_sessions_hash = [];
            $all_user_sessions = [];
            foreach ($user_sessions_res as $row) {
                $combined = $row->chat_user_text_id . '|'.$row->page_session;
                $row->combined = $combined;
                $all_user_sessions_hash[$combined] = $row;
                $all_user_sessions[] = $row;
            }

            $sql_for_source_action = "
                select count(*) as da_count,data_source,data_action FROM wp_fl_chat_logs WHERE 1
                group by data_source,data_action
                order by da_count desc ,data_source,data_action;
            ";

            $source_action_res = $wpdb->get_results($sql_for_source_action);

            $all_source_action_hash = [];
            $all_source_actions = [];
            foreach ($source_action_res as $row) {
                $combined = $row->data_source . '|'.$row->data_action;
                $row->combined = $combined;
                $all_source_action_hash[$combined] = $row;
                $all_source_actions[] = $row;
            }


            // user|session
            $selected_user_sessions = FLInput::get('chat_user_session',[]);

            //each is source|action
            $selected_source_actions = FLInput::get('source_action',[]);

            //tbis-task if empty do not do query return empty form

            $where_source_actions_array = [];
            foreach ($selected_source_actions as $combined) {
                list($source,$action) = explode('|',$combined);
                $where_source_actions_array[] = "(data_source= '$source' AND data_action= '$action')";
            }
            $where_source_actions = '1';
            if (!empty($where_source_actions_array)) {
                $where_source_actions = ' ('.implode(' OR ',$where_source_actions_array) . ')';
            }


            $where_user_sessions_array = [];
            foreach ($selected_user_sessions as $combined) {
                list($user,$session) = explode('|',$combined);
                $where_user_sessions_array[] = "(chat_user_text_id= '$user' AND page_session= '$session')";
            }
            $where_user_sessions = '1';
            if (!empty($where_user_sessions_array)) {
                $where_user_sessions = ' ('.implode(' OR ',$where_user_sessions_array) . ')';
            }


            if (empty($where_user_sessions_array) && empty($where_source_actions_array)) {
                $selected_res = [];
            } else {
                $sql_to_get_results = "
            select
            id,ts_sent,is_being_sent_to_page,page_session_counter,
            floor((ts_sent)/1000) as at_ts,
                chat_user_text_id,page_session,data_source,data_action,
                data_in_json
            from wp_fl_chat_logs
            where 1
            AND $where_source_actions 
            AND $where_user_sessions
            order by ts_sent asc
            ;
            ";


                $selected_res = $wpdb->get_results($sql_to_get_results);
            }

            /*
             * logs are optionally filtered by :
             *    page_session
             *    chat_user_text_id, can use first few letters
             *    time range
             *    exclude data source, data action list
             *
             * and ordered by ts_sent desc
             *
             *  so indexes on table are needed for:
             *       ts_sent,page_session,chat_user_text_id
             *      compound (data source, data action)
             *
             * Going to print out each log as a table
             *  table first 1/3 are the meta
             *      time (local time for browser) using the ts_sent/10000 for unix timestamp
             *      chat_user_text_id
             *      data_source: data_action
             *      page_session/counter
             *  table last 2/3 is the json
             *      data_in_json (in hidden span)
             *
             * then print out a script to show the times, and make the json pretty
             */


//            will_send_to_error_log('post',$_POST);
            http_response_code(200);
            ?>
            <html>
                <head>
                    <script src="<?= get_site_url() . '/wp-includes/js/jquery/jquery.js'?>"></script>
                    <script src="<?= get_template_directory_uri() . '/js/moment.js'?>"></script>

                    <link rel='stylesheet' id='bootstrap-style-css'  href='<?= get_template_directory_uri() . '/css/lib/bootstrap.css'?>' media='all' />
                    <script src="<?= get_template_directory_uri() . '/js/bootstrap.js'?>"></script>

                    <link rel='stylesheet' id='bootstrap-style-css'  href='<?= get_template_directory_uri() . '/js/pretty-print-json/pretty-print-json.css'?>' media='all' />
                    <script src="<?= get_template_directory_uri() . '/js/pretty-print-json/pretty-print-json.js'?>"></script>

                    <style>
                        div.fl-json {
                            background-color: white;
                            border: 5px solid silver;
                            color: black;
                            padding: 1em;
                        }

                        div.row {
                            margin-bottom: 0.5em;
                        }

                        div.from-client {
                            border: 5px solid blue;
                        }

                        div.from-server {
                            border: 5px solid orange;
                        }

                        span.fl-session-counter {
                            float:right;
                            color: gainsboro;
                            font-size: 75%
                        }

                        span.fl-keyword {
                            font-weight: bold;
                            font-size: 110%;
                            text-decoration: underline;
                            margin-right: 0.5em;
                        }

                        span.fl-session {
                            color:grey;
                        }

                        span.fl-da-time {
                            font-size: 90%;
                            display: block;
                        }

                        li {
                            list-style-type: none;
                        }
                    </style>
                </head>
                <body>
<!--                --><?php //will_send_to_error_log('sql',$sql_to_get_results); ?>
                    <form method="post">
                        <input type="hidden" name="action" value="chat_log">


                        <div class="container">
                            <div class="row">
                                <div class="col-md-2"></div>
                                <div class="col-md-2">
                                    <button class="btn btn-lg btn-primary" type="submit">Get Logs</button>
                                </div>
                                <div class="col-md-2"></div>
                                <div class="col-md-2"></div>
                                <div class="col-md-2">
                                    <input class="btn btn-lg btn-danger" name="fl-truncate-logs"
                                           value="Erase all Logs and never see them again" type="submit">
                                </div>
                                <div class="col-md-2"></div>
                            </div>
                            <div class="row">
                                <?php if (empty($all_source_actions) && empty($all_user_sessions)) {?>
                                    <h2 style="text-align: center">No Logs Recorded</h2>
                                <?php } else {?>
                                    <h2 style="text-align: center">Check Which Ones to Show / Press Get Logs To See Them</h2>
                                <?php } ?>

                                <div class="col-md-8">

                                    <ul>
                                    <?php foreach ($all_user_sessions as $user_session) {
                                        $checked = '';
                                        if (empty($selected_user_sessions)) {$checked = "CHECKED";}
                                        else if (in_array($user_session->combined,$selected_user_sessions)) {$checked = "CHECKED";}
                                        ?>
                                        <li>
                                            <label>
                                                <input type="checkbox"
                                                       name="chat_user_session[]"
                                                       value="<?= $user_session->combined?>"
                                                       autocomplete="off"
                                                       <?=$checked?>
                                                />
                                                <span class=""><?= $user_session->da_count ?></span>
                                                <span class=""><?= $user_session->chat_user_text_id ?></span>
                                                <span class=""><?= $user_session->page_session ?></span>
                                                <br>
                                                   From  <span style="margin-left: 2em" data-ts="<?= $user_session->start_ts ?>" class="a-timestamp-full-date-time "></span>
                                                    <br>
                                                   To&nbsp;&nbsp; <span style="margin-left: 2em" data-ts="<?= $user_session->end_ts ?>" class=" a-timestamp-full-date-time "></span>
                                            </label>
                                        </li>
                                     <?php } ?>
                                    </ul>
                                </div>
                                    <!--   form has list of source actions to include, if  source actions are empty check all
                                                                    else just check what was selected , make a hash to do that
                                                                               -->

                                    <!--   form has list of users/sessions/times to include, if  empty check all
                                            else just check what was selected , make a hash to do that
                                                       -->
                                <div class="col-md-4">
                                    <ul>
                                        <?php foreach ($all_source_actions as $source_action_thing) {
                                            $checked = '';
                                            if (empty($selected_source_actions)) {$checked = "CHECKED";}
                                            else if (in_array($source_action_thing->combined,$selected_source_actions)) {$checked = "CHECKED";}
                                            ?>
                                            <li>
                                                <label>
                                                    <input type="checkbox"
                                                           name="source_action[]"
                                                           value="<?= $source_action_thing->combined?>"
                                                           autocomplete="off"
                                                           <?=$checked?>
                                                    />
                                                    <span class=""><?= $source_action_thing->da_count ?></span>
                                                    <span class=""><?= $source_action_thing->data_source ?></span>
                                                    <span class=""><?= $source_action_thing->data_action ?></span>
                                                </label>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                       <div class="container">
                           <?php foreach ($selected_res as $row) {
                               $direction_flag = (int)$row->is_being_sent_to_page;
                               $direction_class = '';
                               if ($direction_flag === -1) {$direction_class = 'from-client';}
                               if ($direction_flag === 1) {$direction_class = 'from-server';}
                               ?>
                                <div class="row">
                                    <div class="col-md-3 fl-log-specs <?= $direction_class ?>">
                                        <span class="fl-keyword"><?= $row->data_source ?></span>
                                        <span class="fl-keyword"><?= $row->data_action ?></span>
                                        <span class=""><?= $row->chat_user_text_id ?></span>
                                        <span class="fl-session"><?= $row->page_session ?></span>
                                        <span data-ts="<?= $row->at_ts ?>" class="a-timestamp-full-date-time fl-da-time"></span>

                                        <span class="fl-session-counter"><?= $row->page_session_counter ?></span>


                                    </div>

                                    <div class="col-md-9">
<!--                                        --><?php //will_send_to_error_log('what',$row->data_in_json);?>
                                        <div class="fl-json <?= $direction_class ?>"><?= $row->data_in_json?></div>
                                    </div>
                                </div>
                            <?php } ?>
                       </div>

                        
                        
                    </form>
                <script>
                    jQuery(function($) {
                        jQuery(".a-timestamp-full-date-time").each(function () {
                            var qthis = $(this);
                            var ts = $(this).data('ts');
                            if (ts === 0 || ts === '0' || ts === undefined || ts === '') {
                                qthis.text('');
                            } else {
                                var ts_number = parseInt(ts.toString());
                                var m = moment(ts_number * 1000);
                                qthis.text(m.format('MMM D YYYY H:mm'));
                            }
                        });

                        $('div.fl-json').each(function() {

                            function is_json_string(str) {
                                try {
                                    JSON.parse(str);
                                } catch (e) {
                                    return false;
                                }
                                return true;
                            }


                            let that = $(this);
                           let words = that.html();

                            if (is_json_string(words)) {

                                let json_words = prettyPrintJson.toHtml(JSON.parse(words),{ quoteKeys:false});
                                that.html(json_words);
                                //console.log(json_words);
                            }
                        });
                    });
                </script>
                </body>
            </html>
            <?php
            die();
        } catch (Exception $e) {
            wp_die($e->getMessage(),'Problem',['response'=>502]);
        }
    }
    static function fl_log_chat()
    {
        /*
         * current-php-code 2020-Dec-21
         * ajax-endpoint  fl_log_chat
         * input-sanitized :
         */


        try {

            global $wpdb;

            $can_do_this = (int)get_option('fl_log_chat_to_db', 0);
            if (!$can_do_this) {
                throw new RuntimeException("Option for fl_log_chat_to_db is set to off ");
            }
            /*
              is_being_sent_to_page
              chat_user_text_id
              chat_room_text_id
              data_source
              data_in_json
             */
            $return = array();

            $user_id = get_current_user_id();
            $is_being_sent_to_page = (int)FLInput::get('is_being_sent_to_page', -1);
            $ts_sent = (int)FLInput::get('ts_sent', 0);
            $page_session_counter = (int)FLInput::get('page_session_counter', 0);
            $chat_user_text_id = FLInput::get('chat_user_text_id', null);
            $chat_room_text_id = FLInput::get('chat_room_text_id', null);
            $page_session = FLInput::get('page_session', null);
            $data_source = FLInput::get('data_source', null);
            $data_action = FLInput::get('data_action', null);
            $data_as_maybe_array = FLInput::get('data_in_json', null);
            if (is_array($data_as_maybe_array)) {
                $data_in_json = json_encode($data_as_maybe_array, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
                $data_in_json = esc_sql($data_in_json);
            } else {
                $data_in_json = $data_as_maybe_array;
            }

            $wpdb->query("INSERT INTO wp_fl_chat_logs ( user_id, ts_sent,is_being_sent_to_page,page_session_counter,
                                                    page_session, chat_user_text_id,
                                                    chat_room_text_id, data_source, 
                                                    data_action,data_in_json)
                                                    VALUES($user_id,$ts_sent,$is_being_sent_to_page,$page_session_counter,
                                                          '$page_session','$chat_user_text_id',
                                                          '$chat_room_text_id','$data_source','$data_action'
                                                          ,'$data_in_json') ");

            $log_id = will_get_last_id($wpdb, 'inserting chat log');


            $return['log_id'] = $log_id;
            $return['message'] = get_custom_string_return('Logged ok');

            $return['status'] = true;


            $return['scrollToElement'] = true;

            wp_send_json($return);

            exit;
        } catch (Exception $e) {
            will_send_to_error_log('fl_log_chat ajax', will_get_exception_string($e));

            $resp = array('status' => false, 'message' => $e->getMessage(), 'scrollToElement' => true);
            wp_send_json($resp);
            die();//above dies, but phpstorm does not know that, so adding it here for editing
        }

    }
}