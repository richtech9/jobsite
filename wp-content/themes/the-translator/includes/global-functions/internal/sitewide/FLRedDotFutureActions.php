<?php

class FLRedDotFutureActions extends  FreelinguistDebugging {
    /*
     * current-php-code 2021-Jan-3
     * internal-call
     * input-sanitized :
     */

    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;

    const DEFAULT_LIMIT_PER_TYPE = 99999;
    const TEMP_TABLE_NAME_CONTENT_ASKED_TO_COMPLETE = 'temp_fl_reddot_content_asked_to_complete';
    const TEMP_TABLE_NAME_CONTENT_ASKED_TO_REJECT = 'temp_fl_reddot_content_asked_to_reject';
    const TEMP_TABLE_NAME_PROJECT_ASKED_TO_COMPLETE = 'temp_fl_reddot_project_asked_to_complete';
    const TEMP_TABLE_NAME_PROJECT_ASKED_TO_REJECT = 'temp_fl_reddot_project_asked_to_reject';
    const TEMP_TABLE_NAME_CONTEST_ASKED_TO_COMPLETE = 'temp_fl_reddot_contest_asked_to_complete';
    const TEMP_TABLE_NAME_CONTEST_ASKED_TO_REJECT = 'temp_fl_reddot_contest_asked_to_reject';

    public static function do_red_dot_actions(&$log,$limit_per_type = null) {
        $limit_per_type = (int)$limit_per_type;
        if (!$limit_per_type) {$limit_per_type = static::DEFAULT_LIMIT_PER_TYPE;}

        static::content_asked_to_complete($log,$limit_per_type);
        static::content_asked_to_reject($log,$limit_per_type);
        static::project_asked_to_complete($log,$limit_per_type);
        static::project_asked_to_reject($log,$limit_per_type);
        static::contest_asked_to_complete($log,$limit_per_type);
        static::contest_asked_to_reject($log,$limit_per_type);
    }

    /**
     * @param string[] $log OUT REF
     * @param int $limit_per_type
     *
     * Completes all content that is timed out, after the freelancer requested completion
     * Unlike the php version of this in the @see hz_change_status_content() we are doing everything through batch sql,
     * so that many contents can be completed in a short time
     *
     * To complete the content
     * 1) the status of the content needs to be changed
     * 2) the money needs to be released to the freelancer by
     * 2a) make a new transaction
     * 2b) change the freelancer's balance
     *
     * LOGIC:
     *  we get the first N action red dots of type content_asked_to_complete (where $limit_per_type is N)
     *  which has expired action times
     *  and we put all the information needed from those, to complete the action, into a temporary table
     *  So, now we have this temporary table which drives all the other sql
     *
     * If there is nothing to do, then we return
     * Else, if there are actions waiting, we do batch sql to do the following
     * A) insert new transactions into the wp_fl_transaction table
     * B) update the content (which will also update the red dot as being done via trigger)
     * C) update the user balance
     *
     * these three steps are rolled into a transaction, so that if something goes wrong, we reset the data to before we started to change it here
     */
    protected static function content_asked_to_complete(&$log,$limit_per_type ) {
        global $wpdb;

        try {
            $event_name = FLRedDot::EVENT_CONTENT_ASKED_TO_COMPLETE;


            //------------------------------------------------------------------------------------------------------
            //code-notes drop the temp table if its still being used

            $temp_table_name = static::TEMP_TABLE_NAME_CONTENT_ASKED_TO_COMPLETE;
            $sql = "DROP TEMPORARY TABLE IF EXISTS  $temp_table_name";
            static::log(static::LOG_DEBUG,"dropping of temp table $temp_table_name in content_asked_to_complete",$sql);
            $wpdb->query($sql);


            //------------------------------------------------------------------------------------------------------
            //code-notes Create and fill in the temp table
            $sql = "CREATE TEMPORARY TABLE $temp_table_name 
                (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 content_id int,
                amount decimal(15,2),
                user_id bigint unsigned,
                old_customer_balance decimal(15,2),
                old_freelancer_balance decimal(15,2),
                customer_id bigint unsigned,
                freelancer_id bigint unsigned,
                payment_status varchar(120),
                type varchar(42),
                description text,
                gateway varchar(20),
                gateway_txn_id varchar(200),
                txn_id varchar(40),
                da_message varchar(120),
                INDEX my_content_index (content_id), INDEX my_user_index (user_id))
                ENGINE=MyISAM 
                SELECT 
                dot.content_id as content_id,
                cast(c.purchase_amount as decimal(15,2))  as amount, -- cast to decimal
                c.user_id as user_id,
                cast(look_customer.total_user_balance as decimal(15,2)) as old_customer_balance,
                cast(look_freelancer.total_user_balance as decimal(15,2)) as old_freelancer_balance,
                look_customer.user_id as customer_id,
                look_freelancer.user_id as freelancer_id,
                'done' as payment_status,
                'contentWinner' as type,
                'Earnings from content sales' as description,
                'wallet' as gateway,
                '' as gateway_txn_id,
                concat(UNIX_TIMESTAMP(NOW()),'-u-',c.user_id) as txn_id,
                CONCAT('Content completed by ',user_freelancer.user_nicename) as da_message
                FROM wp_fl_red_dots dot
                INNER JOIN wp_linguist_content c on dot.content_id = c.id AND c.user_id IS NOT NULL 
                INNER JOIN wp_fl_user_data_lookup look_freelancer ON look_freelancer.user_id = c.user_id
                INNER JOIN wp_fl_user_data_lookup look_customer ON look_customer.user_id = c.purchased_by
                INNER JOIN wp_users user_freelancer ON user_freelancer.ID = look_freelancer.user_id
                INNER JOIN wp_users user_customer ON user_customer.ID = look_customer.user_id
                WHERE dot.event_name = '$event_name' AND dot.is_future_action > 0 and dot.future_timestamp < NOW()
                        and look_customer.total_user_balance  >= 0 AND c.purchase_amount IS NOT NULL   AND c.user_id IS NOT NULL 
                ORDER BY dot.id LIMIT $limit_per_type";

            static::log(static::LOG_DEBUG,"creation of temp table $temp_table_name in content_asked_to_complete",$sql);
            $wpdb->query($sql);
            will_throw_on_wpdb_error($wpdb);

            $sql = /** @lang text */
                "SELECT * FROM $temp_table_name where 1 order by id";
            $res = $wpdb->get_results($sql);
            will_send_to_error_log('temp table dump',$res);

            $sql = "SELECT count(*) as da_count FROM $temp_table_name";
            static::log(static::LOG_DEBUG,"counting of temp table $temp_table_name in content_asked_to_complete",$sql);
            $res = $wpdb->get_results($sql);
            $total_to_process = intval($res[0]->da_count);
            $msg =  "Found $total_to_process red dots for content_asked_to_complete";
            $log[] = $msg;
            static::log(static::LOG_DEBUG,$msg);
            if (!$total_to_process) {return;}


            try {

                //------------------------------------------------------------------------------------------------------
                //code-notes we want to wrap up all the changes in a transaction in case something goes wrong
                $sql = "START TRANSACTION;";
                static::log(static::LOG_DEBUG,"starting transaction in content_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);



                //------------------------------------------------------------------------------------------------------
                //code-notes update the content with the new status
                $sql= /** @lang text */
                    "UPDATE wp_linguist_content c
                    INNER JOIN $temp_table_name j ON j.content_id = c.id 
                    SET c.status = 'completed',c.updated_at = NOW() 
                    where 1";
                static::log(static::LOG_DEBUG,"updating of content in content_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);
                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected wp_linguist_content in  content_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);



                //------------------------------------------------------------------------------------------------------
                //code-notes make transactions for all amounts greater than zero
                $sql = /** @lang text */
                    "
                INSERT INTO wp_fl_transaction (txn_id,user_id,content_id,amount,payment_status,type,description,gateway,gateway_txn_id)
                SELECT txn_id,user_id,content_id,amount,payment_status,type,description,gateway,gateway_txn_id
                FROM $temp_table_name WHERE amount > 0;
              
                ";
                static::log(static::LOG_DEBUG,"inserting new wp_fl_transaction in content_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Inserted $rows_affected wp_fl_transaction in content_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);



                //------------------------------------------------------------------------------------------------------
                //code-notes update balance for all amounts > 0
                $sql= /** @lang text */
                    "UPDATE wp_usermeta m
                    INNER JOIN (
                        SELECT user_id, sum(amount) as total_amount_per_user ,old_freelancer_balance
                        FROM $temp_table_name
                        WHERE amount > 0
                        GROUP BY user_id 
                        )j ON j.user_id = m.user_id 
                    SET m.meta_value = ROUND(j.old_freelancer_balance + j.total_amount_per_user,2)
                    where m.meta_key = 'total_user_balance' ";
                static::log(static::LOG_DEBUG,"updating total_user_balance wp_usermeta  in content_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected freelancer user balances in total_user_balance wp_usermeta in content_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);


                //------------------------------------------------------------------------------------------------------
                //code-notes insert wp_message_history

                $sql = /** @lang text */
                    "
                INSERT INTO wp_message_history (message,content_id,added_by,created_at)
                SELECT da_message,content_id,customer_id,NOW()
                FROM $temp_table_name WHERE 1;
              
                ";
                static::log(static::LOG_DEBUG,"inserting new wp_message_history in content_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Inserted $rows_affected wp_message_history in content_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);






                //------------------------------------------------------------------------------------------------------
                //code-notes commit the transaction
                $sql = " COMMIT;";
                static::log(static::LOG_DEBUG,"comitted content_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);
                $line = "Saved all changes in content_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);
            } catch (Exception $e) {
                $sql = "ROLLBACK;";
                static::log(static::LOG_DEBUG,"rolling back changes in content_asked_to_complete",$sql);
                $wpdb->query($sql);
                if ($wpdb->last_error !== '') {
                    $msg =  'content_asked_to_complete cannot rollback '. $wpdb->last_error;
                    $log[] = $msg;
                    static::log(static::LOG_DEBUG,$msg);
                } else {
                    $msg = "Rolled Back changes in content_asked_to_complete";
                    $log[] = $msg;
                    static::log(static::LOG_DEBUG,$msg);
                }

                throw $e;
            }

        } catch (Exception $e) {
            $log[] =  'error in content_asked_to_complete '. will_get_exception_string($e);
            static::log(static::LOG_ERROR,'error in content_asked_to_complete',will_get_exception_string($e));
        }

    }

    /**
     * @param string[] $log OUT REF
     * @param int $limit_per_type
     *
     * Rejects all content that is timed out, after the freelancer requested rejection
     * Unlike the php version of this in the @see hz_change_status_content() we are doing everything through batch sql,
     * so that many contents can be rejected in a short time
     *
     * To reject the content (notice this is very similar the the completion above, just swapping the roles)
     * 1) the status of the content needs to be changed to rejected
     * 2) the money needs to be returned back to the customer by
     * 2a) make a new transaction
     * 2b) change the customer's balance
     *
     * LOGIC:
     *  we get the first N action red dots of type content_asked_to_reject (where $limit_per_type is N)
     *  that has expired action times
     *  and we put all the information needed from those, to complete the action, into a temporary table
     *  So, now we have this temporary table which drives all the other sql
     *
     * If there is nothing to do, then we return
     * Else, if there are actions waiting, we do batch sql to do the following
     * A) insert new transactions into the wp_fl_transaction table
     * B) update the content (which will also update the red dot as being done via trigger)
     * C) update the user balance
     *
     * these three steps are rolled into a transaction, so that if something goes wrong, we reset the data to before we started to change it here
     */
    protected static function content_asked_to_reject(&$log,$limit_per_type) {
        global $wpdb;

        try {
            $event_name = FLRedDot::EVENT_CONTENT_ASKED_TO_REJECT;


            //------------------------------------------------------------------------------------------------------
            //code-notes drop the temp table if its still being used

            $temp_table_name = static::TEMP_TABLE_NAME_CONTENT_ASKED_TO_REJECT;
            $sql = "DROP TEMPORARY TABLE IF EXISTS  $temp_table_name";
            static::log(static::LOG_DEBUG,"dropping of temp table $temp_table_name in content_asked_to_reject",$sql);
            $wpdb->query($sql);


            //------------------------------------------------------------------------------------------------------
            //code-notes Create and fill in the temp table
            $sql = "CREATE TEMPORARY TABLE $temp_table_name 
                (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 content_id int,
                amount decimal(15,2),
                user_id bigint unsigned,
                old_customer_balance decimal(15,2),
                old_freelancer_balance decimal(15,2),
                customer_id bigint unsigned,
                freelancer_id bigint unsigned,
                payment_status varchar(120),
                type varchar(42),
                description text,
                gateway varchar(20),
                gateway_txn_id varchar(200),
                txn_id varchar(40),
                da_message varchar(120),
                INDEX my_content_index (content_id), INDEX my_user_index (user_id))
                ENGINE=MyISAM 
                SELECT 
                dot.content_id as content_id,
                cast(c.purchase_amount as decimal(15,2))  as amount, -- cast to decimal
                c.purchased_by as user_id,
                cast(look_customer.total_user_balance as decimal(15,2)) as old_customer_balance,
                cast(look_freelancer.total_user_balance as decimal(15,2)) as old_freelancer_balance,
                look_customer.user_id as customer_id,
                look_freelancer.user_id as freelancer_id,
                'done' as payment_status,
                'contentRejected' as type,
                'Content was rejected' as description,
                'wallet' as gateway,
                '' as gateway_txn_id,
                concat(UNIX_TIMESTAMP(NOW()),'-u-',c.purchased_by) as txn_id,
                CONCAT('Content rejected by ',user_customer.user_nicename) as da_message
                FROM wp_fl_red_dots dot
                INNER JOIN wp_linguist_content c on dot.content_id = c.id AND c.user_id IS NOT NULL
                INNER JOIN wp_fl_user_data_lookup look_freelancer ON look_freelancer.user_id = c.user_id
                INNER JOIN wp_fl_user_data_lookup look_customer ON look_customer.user_id = c.purchased_by
                INNER JOIN wp_users user_freelancer ON user_freelancer.ID = look_freelancer.user_id
                INNER JOIN wp_users user_customer ON user_customer.ID = look_customer.user_id
                WHERE dot.event_name = '$event_name' AND dot.is_future_action > 0 and dot.future_timestamp < NOW()
                         AND c.purchase_amount IS NOT NULL  
                ORDER BY dot.id LIMIT $limit_per_type";

            static::log(static::LOG_DEBUG,"creation of temp table $temp_table_name in content_asked_to_reject",$sql);
            $wpdb->query($sql);
            will_throw_on_wpdb_error($wpdb);

            $sql = /** @lang text */
                "SELECT * FROM $temp_table_name where 1 order by id";
            $res = $wpdb->get_results($sql);
            will_send_to_error_log('temp table dump',$res);

            $sql = "SELECT count(*) as da_count FROM $temp_table_name";
            static::log(static::LOG_DEBUG,"counting of temp table $temp_table_name in content_asked_to_reject",$sql);
            $res = $wpdb->get_results($sql);
            $total_to_process = intval($res[0]->da_count);
            $msg =  "Found $total_to_process red dots for content_asked_to_reject";
            $log[] = $msg;
            static::log(static::LOG_DEBUG,$msg);
            if (!$total_to_process) {return;}


            try {

                //------------------------------------------------------------------------------------------------------
                //code-notes we want to wrap up all the changes in a transaction in case something goes wrong
                $sql = "START TRANSACTION;";
                static::log(static::LOG_DEBUG,"starting transaction in content_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);



                //------------------------------------------------------------------------------------------------------
                //code-notes update the content with the new status
                $sql= /** @lang text */
                    "UPDATE wp_linguist_content c
                    INNER JOIN $temp_table_name j ON j.content_id = c.id 
                    SET c.status = 'rejected',c.updated_at = NOW() 
                    where 1";
                static::log(static::LOG_DEBUG,"updating of content in content_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);
                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected wp_linguist_content in  content_asked_to_reject";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);



                //------------------------------------------------------------------------------------------------------
                //code-notes make transactions for all amounts greater than zero
                $sql = /** @lang text */
                    "
                INSERT INTO wp_fl_transaction (txn_id,user_id,content_id,amount,payment_status,type,description,gateway,gateway_txn_id)
                SELECT txn_id,user_id,content_id,amount,payment_status,type,description,gateway,gateway_txn_id
                FROM $temp_table_name WHERE amount > 0;
              
                ";
                static::log(static::LOG_DEBUG,"inserting new wp_fl_transaction in content_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Inserted $rows_affected wp_fl_transaction in content_asked_to_reject";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);



                //------------------------------------------------------------------------------------------------------
                //code-notes update balance for all amounts > 0
                $sql= /** @lang text */
                    "UPDATE wp_usermeta m
                    INNER JOIN (
                        SELECT user_id,sum(amount) as total_amount_per_user ,old_customer_balance
                        FROM $temp_table_name
                        WHERE amount > 0
                        GROUP BY user_id 
                        )j ON j.user_id = m.user_id 
                    SET m.meta_value = ROUND(j.old_customer_balance + j.total_amount_per_user,2)
                    where m.meta_key = 'total_user_balance' ";
                static::log(static::LOG_DEBUG,"updating total_user_balance wp_usermeta  in content_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected customer user balances in total_user_balance wp_usermeta in content_asked_to_reject";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);


                //------------------------------------------------------------------------------------------------------
                //code-notes insert wp_message_history

                $sql = /** @lang text */
                    "
                INSERT INTO wp_message_history (message,content_id,added_by,created_at)
                SELECT da_message,content_id,freelancer_id,NOW()
                FROM $temp_table_name WHERE 1;
              
                ";
                static::log(static::LOG_DEBUG,"inserting new wp_message_history in content_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Inserted $rows_affected wp_message_history in content_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);




                //------------------------------------------------------------------------------------------------------
                //code-notes commit the transaction
                $sql = " COMMIT;";
                static::log(static::LOG_DEBUG,"comitted content_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);
                $line = "Saved all changes in content_asked_to_reject";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);
            } catch (Exception $e) {
                $sql = "ROLLBACK;";
                static::log(static::LOG_DEBUG,"rolling back changes in content_asked_to_reject",$sql);
                $wpdb->query($sql);
                if ($wpdb->last_error !== '') {
                    $msg =  'content_asked_to_reject cannot rollback '. $wpdb->last_error;
                    $log[] = $msg;
                    static::log(static::LOG_DEBUG,$msg);
                } else {
                    $msg = "Rolled Back changes in content_asked_to_reject";
                    $log[] = $msg;
                    static::log(static::LOG_DEBUG,$msg);
                }

                throw $e;
            }

        } catch (Exception $e) {
            $log[] =  'error in content_asked_to_reject '. will_get_exception_string($e);
            static::log(static::LOG_ERROR,'error in content_asked_to_reject',will_get_exception_string($e));
        }
    }


    /**
     * @param string[] $log OUT REF
     * @param int $limit_per_type
     *
     * Completes all milestones that are timed out, after the freelancer requested completion
     * Unlike the php version of this in the @see hz_approve_milestone_cb()
     * we are doing everything through batch sql,
     * so that many milestones can be completed in a short time
     *
     * To complete the milestone
     * 1) the status of the milestone needs to be changed to completed
     * 2) the money needs to be released to the freelancer by
     * 2a) make a new transaction
     * 2b) change the freelancer's balance
     * 3) A message needs to be made for the project
     * 4) the project status needs to be changed
     *
     * LOGIC:
     *  we get the first N action red dots of type project_asked_to_complete (where $limit_per_type is N)
     *  which has expired action times
     *  and we put all the information needed from those, to do all the steps listed above, into a temporary table
     *  So, now we have this temporary table which drives all the other sql
     *
     * If there is nothing to do, then we return
     * Else, if there are actions waiting, we do batch sql to do the following
     * A) insert new transactions into the wp_fl_transaction table
     * B) update the milestone status and timestamp (which will also update the red dot as being done via trigger)
     * C) update the user balance (minus the fee, calculated in the temp table)
     * D) send a message to the project
     * E) change the project status
     *
     * these three steps are rolled into a transaction, so that if something goes wrong, we reset the data to before we started to change it here
     */
    protected static function project_asked_to_complete(&$log,$limit_per_type) {
        global $wpdb;

        try {
            $event_name = FLRedDot::EVENT_PROJECT_ASKED_TO_COMPLETE;


            //------------------------------------------------------------------------------------------------------
            //code-notes drop the temp table if its still being used

            $temp_table_name = static::TEMP_TABLE_NAME_PROJECT_ASKED_TO_COMPLETE;
            $sql = "DROP TEMPORARY TABLE IF EXISTS  $temp_table_name";
            static::log(static::LOG_DEBUG,"dropping of temp table $temp_table_name in project_asked_to_complete",$sql);
            $wpdb->query($sql);

            //code-notes get fee percentage first
            $percentage = floatval(get_option('linguist_flex_referral_fee',15) );


            //------------------------------------------------------------------------------------------------------
            //code-notes Create and fill in the temp table
            $sql = "CREATE TEMPORARY TABLE $temp_table_name 
                (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 milestone_id bigint,
                 job_id  bigint,
                 project_id bigint unsigned,
                amount decimal(15,2),
                user_id bigint unsigned,
                old_customer_balance decimal(15,2),
                old_freelancer_balance decimal(15,2),
                customer_id bigint unsigned,
                freelancer_id bigint unsigned,
                payment_status varchar(120),
                type varchar(42),
                description text,
                gateway varchar(20),
                gateway_txn_id varchar(200),
                txn_id varchar(40),
                da_name_freelancer varchar(50),
                da_name_customer varchar(50),
                da_message varchar(120),
                da_status varchar(120),
                INDEX my_milestone_index (milestone_id), INDEX my_user_index (user_id))
                ENGINE=MyISAM 
                SELECT DISTINCT 
                dot.milestone_id as milestone_id,
                dot.job_id as job_id,
                dot.project_id as project_id,
                cast(ROUND(m.amount - (m.amount * $percentage / 100),2) as decimal(15,2))  as amount, -- cast to decimal
                m.linguist_id as user_id,
                cast(look_customer.total_user_balance as decimal(15,2)) as old_customer_balance,
                cast(look_freelancer.total_user_balance as decimal(15,2)) as old_freelancer_balance,
                look_customer.user_id as customer_id,
                look_freelancer.user_id as freelancer_id,
                'done' as payment_status,
                'milestone_completed' as type,
                'Milestone Completed' as description,
                'wallet' as gateway,
                '' as gateway_txn_id,
                concat(UNIX_TIMESTAMP(NOW()),'-u-',m.linguist_id) as txn_id,
                user_freelancer.user_nicename as da_name_freelancer,
                user_customer.user_nicename as da_name_customer,
                CONCAT('Milestone completed by ',user_customer.user_nicename) as da_message,
                CONCAT(user_customer.user_nicename,', milestone ',m.ID,': Completed') as da_status
                FROM wp_fl_red_dots dot
                INNER JOIN wp_fl_milestones m on dot.milestone_id = m.ID
                INNER JOIN wp_posts post on m.project_id = post.ID
                INNER JOIN wp_fl_user_data_lookup look_freelancer ON look_freelancer.user_id = m.linguist_id
                INNER JOIN wp_fl_user_data_lookup look_customer ON look_customer.user_id = post.post_author
                INNER JOIN wp_users user_freelancer ON user_freelancer.ID = look_freelancer.user_id
                INNER JOIN wp_users user_customer ON user_customer.ID = look_customer.user_id
                WHERE dot.event_name = '$event_name' AND dot.is_future_action > 0 and dot.future_timestamp < NOW()
                         AND look_customer.total_user_balance  >= 0
                ORDER BY dot.id LIMIT $limit_per_type";

            static::log(static::LOG_DEBUG,"creation of temp table $temp_table_name in project_asked_to_complete",$sql);
            $wpdb->query($sql);
            will_throw_on_wpdb_error($wpdb);

            $sql = /** @lang text */
                "SELECT * FROM $temp_table_name where 1 order by id";
            $res = $wpdb->get_results($sql);
            will_send_to_error_log('temp table dump',$res);

            $sql = "SELECT count(*) as da_count FROM $temp_table_name";
            static::log(static::LOG_DEBUG,"counting of temp table $temp_table_name in project_asked_to_complete",$sql);
            $res = $wpdb->get_results($sql);
            $total_to_process = intval($res[0]->da_count);
            $msg =  "Found $total_to_process red dots for project_asked_to_complete";
            $log[] = $msg;
            static::log(static::LOG_DEBUG,$msg);
            if (!$total_to_process) {return;}


            try {

                //------------------------------------------------------------------------------------------------------
                //code-notes we want to wrap up all the changes in a transaction in case something goes wrong
                $sql = "START TRANSACTION;";
                static::log(static::LOG_DEBUG,"starting transaction in project_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);



                //------------------------------------------------------------------------------------------------------
                //code-notes update the milestone with the new status
                $sql= /** @lang text */
                    "UPDATE wp_fl_milestones m
                    INNER JOIN $temp_table_name j ON j.milestone_id = m.ID 
                    SET m.status = 'completed',m.updated_at = NOW() 
                    where 1";
                static::log(static::LOG_DEBUG,"updating of milestone in project_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);
                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected wp_fl_milestones in  project_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);



                //------------------------------------------------------------------------------------------------------
                //code-notes make transactions for all amounts greater than zero
                $sql = /** @lang text */
                    "
                INSERT INTO wp_fl_transaction (txn_id,user_id,milestone_id,job_id,project_id,
                                                      amount,payment_status,type,description,gateway,gateway_txn_id)
                SELECT txn_id,user_id,milestone_id,job_id,project_id,amount,payment_status,type,description,gateway,gateway_txn_id
                FROM $temp_table_name WHERE amount > 0;
              
                ";
                static::log(static::LOG_DEBUG,"inserting new wp_fl_transaction in project_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Inserted $rows_affected wp_fl_transaction in project_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);



                //------------------------------------------------------------------------------------------------------
                //code-notes update balance for all amounts > 0
                $sql= /** @lang text */
                    "UPDATE wp_usermeta m
                    INNER JOIN (
                        SELECT user_id,sum(amount) as total_amount_per_user ,old_freelancer_balance
                        FROM $temp_table_name
                        WHERE amount > 0
                        GROUP BY user_id 
                        )j ON j.user_id = m.user_id 
                    SET m.meta_value = ROUND(j.old_freelancer_balance + j.total_amount_per_user,2)
                    where m.meta_key = 'total_user_balance' ";
                static::log(static::LOG_DEBUG,"updating total_user_balance wp_usermeta  in project_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected freelancer user balances in total_user_balance wp_usermeta in project_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);


                //------------------------------------------------------------------------------------------------------
                //code-notes project status for all projects
                $sql=
                    /** @lang text */
                    "UPDATE wp_postmeta m
                    INNER JOIN $temp_table_name j ON j.project_id = m.post_id
                    SET m.meta_value = j.da_status
                    where m.meta_key = 'project_new_status' ";
                static::log(static::LOG_DEBUG,"updating project_new_status wp_postmeta  in project_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected project_new_status in wp_postmeta in project_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);

                //------------------------------------------------------------------------------------------------------
                //code-notes insert wp_message_history

                $sql = /** @lang text */
                    "
                INSERT INTO wp_message_history (message,milestone_id,added_by,created_at)
                SELECT da_message,milestone_id,customer_id,NOW()
                FROM $temp_table_name WHERE 1;
              
                ";
                static::log(static::LOG_DEBUG,"inserting new wp_message_history in project_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Inserted $rows_affected wp_message_history in project_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);





                //------------------------------------------------------------------------------------------------------
                //code-notes commit the transaction
                $sql = " COMMIT;";
                static::log(static::LOG_DEBUG,"comitted project_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);
                $line = "Saved all changes in project_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);
            } catch (Exception $e) {
                $sql = "ROLLBACK;";
                static::log(static::LOG_DEBUG,"rolling back changes in project_asked_to_complete",$sql);
                $wpdb->query($sql);
                if ($wpdb->last_error !== '') {
                    $msg =  'project_asked_to_complete cannot rollback '. $wpdb->last_error;
                    $log[] = $msg;
                    static::log(static::LOG_DEBUG,$msg);
                } else {
                    $msg = "Rolled Back changes in project_asked_to_complete";
                    $log[] = $msg;
                    static::log(static::LOG_DEBUG,$msg);
                }

                throw $e;
            }

        } catch (Exception $e) {
            $log[] =  'error in project_asked_to_complete '. will_get_exception_string($e);
            static::log(static::LOG_ERROR,'error in project_asked_to_complete',will_get_exception_string($e));
        }
    }



    /**
     * @param string[] $log OUT REF
     * @param int $limit_per_type
     *
     * Rejects all milestones that are timed out, after the customer requested rejection
     * Unlike the php version of this in the @see hz_manage_milestone_cb()
     * we are doing everything through batch sql,
     * so that many milestones can be completed in a short time
     *
     * To reject the milestone
     * 1) the status of the milestone needs to be changed to approved_rejection
     * 2) the money needs to be released to the customer by
     * 2a) make a new transaction
     * 2b) change the customer's balance
     * 3) A message needs to be made for the project
     * 4) the project status needs to be changed
     *
     * LOGIC:
     *  we get the first N action red dots of type project_asked_to_reject (where $limit_per_type is N)
     *  which has expired action times
     *  and we put all the information needed from those, to do all the steps listed above, into a temporary table
     *  So, now we have this temporary table which drives all the other sql
     *
     * If there is nothing to do, then we return
     * Else, if there are actions waiting, we do batch sql to do the following
     * A) insert new transactions into the wp_fl_transaction table
     * B) update the milestone status and timestamp (which will also update the red dot as being done via trigger)
     * C) update the user balance
     * D) send a message to the project
     * E) change the project status
     *
     * these three steps are rolled into a transaction, so that if something goes wrong, we reset the data to before we started to change it here
     */
    protected static function project_asked_to_reject(&$log,$limit_per_type) {
        global $wpdb;

        try {
            $event_name = FLRedDot::EVENT_PROJECT_ASKED_TO_REJECT;


            //------------------------------------------------------------------------------------------------------
            //code-notes drop the temp table if its still being used

            $temp_table_name = static::TEMP_TABLE_NAME_PROJECT_ASKED_TO_REJECT;
            $sql = "DROP TEMPORARY TABLE IF EXISTS  $temp_table_name";
            static::log(static::LOG_DEBUG,"dropping of temp table $temp_table_name in project_asked_to_reject",$sql);
            $wpdb->query($sql);


            //------------------------------------------------------------------------------------------------------
            //code-notes Create and fill in the temp table
            $sql = "CREATE TEMPORARY TABLE $temp_table_name 
                (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 milestone_id bigint,
                 job_id  bigint,
                 project_id bigint unsigned,
                amount decimal(15,2),
                user_id bigint unsigned,
                old_customer_balance decimal(15,2),
                old_freelancer_balance decimal(15,2),
                customer_id bigint unsigned,
                freelancer_id bigint unsigned,
                payment_status varchar(120),
                type varchar(42),
                description text,
                gateway varchar(20),
                gateway_txn_id varchar(200),
                txn_id varchar(40),
                da_name_freelancer varchar(50),
                da_name_customer varchar(50),
                da_message varchar(120),
                da_status varchar(120),
                INDEX my_milestone_index (milestone_id), INDEX my_user_index (user_id))
                ENGINE=MyISAM 
                SELECT DISTINCT 
                dot.milestone_id as milestone_id,
                dot.job_id as job_id,
                dot.project_id as project_id,
                cast(ROUND(m.amount ,2) as decimal(15,2))  as amount, -- cast to decimal
                post.post_author as user_id,
                cast(look_customer.total_user_balance as decimal(15,2)) as old_customer_balance,
                cast(look_freelancer.total_user_balance as decimal(15,2)) as old_freelancer_balance,
                look_customer.user_id as customer_id,
                look_freelancer.user_id as freelancer_id,
                'done' as payment_status,
                'milestoneRejected' as type,
                'Milestone rejected by freelancer' as description,
                'wallet' as gateway,
                '' as gateway_txn_id,
                concat(UNIX_TIMESTAMP(NOW()),'-u-',post.post_author) as txn_id,
                user_freelancer.user_nicename as da_name_freelancer,
                user_customer.user_nicename as da_name_customer,
                CONCAT('Rejection approved by ',user_freelancer.user_nicename) as da_message,
                CONCAT(user_customer.user_nicename,', milestone ',m.ID,': Rejected') as da_status
                FROM wp_fl_red_dots dot
                INNER JOIN wp_fl_milestones m on dot.milestone_id = m.ID
                INNER JOIN wp_posts post on m.project_id = post.ID
                INNER JOIN wp_fl_user_data_lookup look_freelancer ON look_freelancer.user_id = m.linguist_id
                INNER JOIN wp_fl_user_data_lookup look_customer ON look_customer.user_id = post.post_author
                INNER JOIN wp_users user_freelancer ON user_freelancer.ID = look_freelancer.user_id
                INNER JOIN wp_users user_customer ON user_customer.ID = look_customer.user_id
                WHERE dot.event_name = '$event_name' AND dot.is_future_action > 0 and dot.future_timestamp < NOW()
                         
                ORDER BY dot.id LIMIT $limit_per_type";

            static::log(static::LOG_DEBUG,"creation of temp table $temp_table_name in project_asked_to_reject",$sql);
            $wpdb->query($sql);
            will_throw_on_wpdb_error($wpdb);

            $sql = /** @lang text */
                "SELECT * FROM $temp_table_name where 1 order by id";
            $res = $wpdb->get_results($sql);
            will_send_to_error_log('temp table dump',$res);

            $sql = "SELECT count(*) as da_count FROM $temp_table_name";
            static::log(static::LOG_DEBUG,"counting of temp table $temp_table_name in project_asked_to_reject",$sql);
            $res = $wpdb->get_results($sql);
            $total_to_process = intval($res[0]->da_count);
            $msg =  "Found $total_to_process red dots for project_asked_to_reject";
            $log[] = $msg;
            static::log(static::LOG_DEBUG,$msg);
            if (!$total_to_process) {return;}


            try {

                //------------------------------------------------------------------------------------------------------
                //code-notes we want to wrap up all the changes in a transaction in case something goes wrong
                $sql = "START TRANSACTION;";
                static::log(static::LOG_DEBUG,"starting transaction in project_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);



                //------------------------------------------------------------------------------------------------------
                //code-notes update the milestone with the new status
                $sql= /** @lang text */
                    "UPDATE wp_fl_milestones m
                    INNER JOIN $temp_table_name j ON j.milestone_id = m.ID 
                    SET m.status = 'approved_rejection',m.updated_at = NOW() 
                    where 1";
                static::log(static::LOG_DEBUG,"updating of milestone in project_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);
                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected wp_fl_milestones in  project_asked_to_reject";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);



                //------------------------------------------------------------------------------------------------------
                //code-notes make transactions for all amounts greater than zero
                $sql = /** @lang text */
                    "
                INSERT INTO wp_fl_transaction (txn_id,user_id,milestone_id,job_id,project_id,
                                                      amount,payment_status,type,description,gateway,gateway_txn_id)
                SELECT txn_id,user_id,milestone_id,job_id,project_id,amount,payment_status,type,description,gateway,gateway_txn_id
                FROM $temp_table_name WHERE amount > 0;
              
                ";
                static::log(static::LOG_DEBUG,"inserting new wp_fl_transaction in project_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Inserted $rows_affected wp_fl_transaction in project_asked_to_reject";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);



                //------------------------------------------------------------------------------------------------------
                //code-notes update balance for all amounts > 0
                $sql= /** @lang text */
                    "UPDATE wp_usermeta m
                    INNER JOIN (
                        SELECT user_id,sum(amount) as total_amount_per_user ,old_customer_balance
                        FROM $temp_table_name
                        WHERE amount > 0
                        GROUP BY user_id 
                        )j ON j.user_id = m.user_id 
                    SET m.meta_value = ROUND(j.old_customer_balance + j.total_amount_per_user,2)
                    where m.meta_key = 'total_user_balance' ";
                static::log(static::LOG_DEBUG,"updating total_user_balance wp_usermeta  in project_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected customer user balances in total_user_balance wp_usermeta in project_asked_to_reject";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);


                //------------------------------------------------------------------------------------------------------
                //code-notes project status for all projects
                $sql=
                    /** @lang text */
                    "UPDATE wp_postmeta m
                    INNER JOIN $temp_table_name j ON j.project_id = m.post_id
                    SET m.meta_value = j.da_status
                    where m.meta_key = 'project_new_status' ";
                static::log(static::LOG_DEBUG,"updating project_new_status wp_postmeta  in project_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected project_new_status in wp_postmeta in project_asked_to_reject";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);

                //------------------------------------------------------------------------------------------------------
                //code-notes insert wp_message_history

                $sql = /** @lang text */
                    "
                INSERT INTO wp_message_history (message,milestone_id,added_by,created_at)
                SELECT da_message,milestone_id,freelancer_id,NOW()
                FROM $temp_table_name WHERE 1;
              
                ";
                static::log(static::LOG_DEBUG,"inserting new wp_message_history in project_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Inserted $rows_affected wp_message_history in project_asked_to_reject";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);





                //------------------------------------------------------------------------------------------------------
                //code-notes commit the transaction
                $sql = " COMMIT;";
                static::log(static::LOG_DEBUG,"comitted project_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);
                $line = "Saved all changes in project_asked_to_reject";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);
            } catch (Exception $e) {
                $sql = "ROLLBACK;";
                static::log(static::LOG_DEBUG,"rolling back changes in project_asked_to_reject",$sql);
                $wpdb->query($sql);
                if ($wpdb->last_error !== '') {
                    $msg =  'project_asked_to_reject cannot rollback '. $wpdb->last_error;
                    $log[] = $msg;
                    static::log(static::LOG_DEBUG,$msg);
                } else {
                    $msg = "Rolled Back changes in project_asked_to_reject";
                    $log[] = $msg;
                    static::log(static::LOG_DEBUG,$msg);
                }

                throw $e;
            }

        } catch (Exception $e) {
            $log[] =  'error in project_asked_to_reject '. will_get_exception_string($e);
            static::log(static::LOG_ERROR,'error in project_asked_to_reject',will_get_exception_string($e));
        }
    }

    /**
     * @param string[] $log OUT REF
     * @param int $limit_per_type
     *
     * Completes all proposals that are timed out, after the freelancer requested completion
     * Unlike the php version of this in the @see hz_complete_contest_proposal_cb()
     * we are doing everything through batch sql,
     * so that many proposals can be completed in a short time
     *
     * To complete the proposal
     * 1) the status of the proposal needs to be changed to completed
     * 2) the money (minus the fee) needs to be released to the freelancer by
     * 2a) make a new transaction
     * 2b) change the freelancer's balance
     * 3) A message needs to be made for the proposal
     * 4) the contest status needs to be changed
     *
     * LOGIC:
     *  we get the first N action red dots of type contest_asked_to_complete (where $limit_per_type is N)
     *  which has expired action times
     *  and we put all the information needed from those, to do all the steps listed above, into a temporary table
     *  So, now we have this temporary table which drives all the other sql
     *
     * If there is nothing to do, then we return
     * Else, if there are actions waiting, we do batch sql to do the following
     * A) insert new transactions into the wp_fl_transaction table
     * B) update the proposal status and timestamp (which will also update the red dot as being done via trigger)
     * C) update the user balance (minus the fee, calculated in the temp table)
     * D) change the contest status
     * E) update to, or add , the proposal id to the comma delimited string of the post meta contest_completed_proposals
     *
     * these three steps are rolled into a transaction, so that if something goes wrong, we reset the data to before we started to change it here
     */
    protected static function contest_asked_to_complete(&$log,$limit_per_type) {
        global $wpdb;

        try {
            $event_name = FLRedDot::EVENT_CONTEST_ASKED_TO_COMPLETE;


            //------------------------------------------------------------------------------------------------------
            //code-notes drop the temp table if its still being used

            $temp_table_name = static::TEMP_TABLE_NAME_CONTEST_ASKED_TO_COMPLETE;
            $sql = "DROP TEMPORARY TABLE IF EXISTS  $temp_table_name";
            static::log(static::LOG_DEBUG,"dropping of temp table $temp_table_name in contest_asked_to_complete",$sql);
            $wpdb->query($sql);

            //code-notes get fee constants first
            $linguist_referral_fee = floatval(get_option('linguist_referral_fee',15)) ;
            $linguist_flex_referral_fee = floatval(get_option('linguist_flex_referral_fee',15)) ;

            $awarded_flag_value = FLPostLookupDataHelpers::POST_USER_DATA_FLAG_AWARDED_CONTEST;


            //------------------------------------------------------------------------------------------------------
            //code-notes Create and fill in the temp table
            $sql = "CREATE TEMPORARY TABLE $temp_table_name 
                (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                proposal_id int,
                contest_id bigint unsigned,
                estimated_budget decimal(15,2),
                amount decimal(15,2),
                user_id bigint unsigned,
                old_customer_balance decimal(15,2),
                old_freelancer_balance decimal(15,2),
                customer_id bigint unsigned,
                freelancer_id bigint unsigned,
                completed_meta_id bigint unsigned,
                payment_status varchar(120),
                type varchar(42),
                description text,
                gateway varchar(20),
                gateway_txn_id varchar(200),
                txn_id varchar(40),
                da_name_freelancer varchar(50),
                da_name_customer varchar(50),
                da_message varchar(120),
                da_status varchar(120),
                INDEX my_contest_index (contest_id), INDEX my_user_index (user_id))
                ENGINE=MyISAM 
                SELECT DISTINCT 
                dot.proposal_id as proposal_id,
                dot.contest_id as contest_id,
                 IF(budget_meta.meta_value IS NULL OR budget_meta.meta_value = '',
                  0,
                  cast(budget_meta.meta_value as decimal(15,2))
                ) as estimated_budget,
                
                cast(ROUND(
                        IF(budget_meta.meta_value IS NULL OR budget_meta.meta_value = '',
                          0,
                          cast(budget_meta.meta_value as decimal(15,2))
                        ) 
                        - $linguist_referral_fee -  (
                                                      IF(budget_meta.meta_value IS NULL OR budget_meta.meta_value = '',
                                                      0,
                                                      cast(budget_meta.meta_value as decimal(15,2))
                                                      ) 
                                                      * $linguist_flex_referral_fee / 100
                                                    )
                        ,2) as decimal(15,2))  as amount, -- cast to decimal
                
                prop.by_user as user_id,
                cast(look_customer.total_user_balance as decimal(15,2)) as old_customer_balance,
                cast(look_freelancer.total_user_balance as decimal(15,2)) as old_freelancer_balance,
                look_customer.user_id as customer_id,
                look_freelancer.user_id as freelancer_id,
                completed_meta.meta_id as completed_meta_id,
                'done' as payment_status,
                'contestWinner' as type,
                'Earnings from competition' as description,
                'wallet' as gateway,
                '' as gateway_txn_id,
                concat(UNIX_TIMESTAMP(NOW()),'-u-',prop.by_user) as txn_id,
                user_freelancer.user_nicename as da_name_freelancer,
                user_customer.user_nicename as da_name_customer,
                CONCAT('Proposal completed by ',user_customer.user_nicename) as da_message,
                CONCAT('Completed') as da_status
                FROM wp_fl_red_dots dot
                INNER JOIN wp_proposals prop on dot.proposal_id = prop.id
                INNER JOIN wp_posts post on prop.post_id = post.ID
                INNER JOIN wp_fl_user_data_lookup look_freelancer ON look_freelancer.user_id = prop.by_user
                INNER JOIN wp_fl_user_data_lookup look_customer ON look_customer.user_id = post.post_author
                INNER JOIN wp_users user_freelancer ON user_freelancer.ID = look_freelancer.user_id
                INNER JOIN wp_users user_customer ON user_customer.ID = look_customer.user_id
                LEFT JOIN wp_postmeta completed_meta ON completed_meta.post_id = post.ID and completed_meta.meta_key = 'contest_completed_proposals'
                LEFT JOIN wp_postmeta budget_meta ON budget_meta.post_id = post.ID and budget_meta.meta_key = 'estimated_budgets'
                INNER JOIN wp_fl_post_user_lookup awarded_flag ON 
                  awarded_flag.post_id = post.ID AND awarded_flag.lookup_flag = $awarded_flag_value AND awarded_flag.lookup_val = prop.id 
                WHERE dot.event_name = '$event_name' AND dot.is_future_action > 0 and dot.future_timestamp < NOW()
                         AND look_customer.total_user_balance  >= 0
                ORDER BY dot.id LIMIT $limit_per_type";

            static::log(static::LOG_DEBUG,"creation of temp table $temp_table_name in contest_asked_to_complete",$sql);
            $wpdb->query($sql);
            will_throw_on_wpdb_error($wpdb);

            $sql = /** @lang text */
                "SELECT * FROM $temp_table_name where 1 order by id";
            $res = $wpdb->get_results($sql);
            will_send_to_error_log('temp table dump',$res);

            $sql = "SELECT count(*) as da_count FROM $temp_table_name";
            static::log(static::LOG_DEBUG,"counting of temp table $temp_table_name in contest_asked_to_complete",$sql);
            $res = $wpdb->get_results($sql);
            $total_to_process = intval($res[0]->da_count);
            $msg =  "Found $total_to_process red dots for contest_asked_to_complete";
            $log[] = $msg;
            static::log(static::LOG_DEBUG,$msg);
            if (!$total_to_process) {return;}

            try {

                //------------------------------------------------------------------------------------------------------
                //code-notes we want to wrap up all the changes in a transaction in case something goes wrong
                $sql = "START TRANSACTION;";
                static::log(static::LOG_DEBUG,"starting transaction in contest_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);



                //------------------------------------------------------------------------------------------------------
                //code-notes update the milestone with the new status
                $sql=
                    /** @lang text */
                    "UPDATE wp_proposals prop
                    INNER JOIN $temp_table_name j ON j.proposal_id = prop.id 
                    SET prop.status = 'completed',prop.updated_at = NOW() 
                    where 1";
                static::log(static::LOG_DEBUG,"updating of proposal in contest_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);
                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected wp_proposals in  contest_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);



                //------------------------------------------------------------------------------------------------------
                //code-notes make transactions for all amounts greater than zero
                $sql = /** @lang text */
                    "
                INSERT INTO wp_fl_transaction (txn_id,user_id,project_id,
                                                      amount,payment_status,type,description,gateway,gateway_txn_id,proposal_id)
                SELECT txn_id,user_id,contest_id,amount,payment_status,type,description,gateway,gateway_txn_id,proposal_id
                FROM $temp_table_name WHERE amount > 0;
              
                ";
                static::log(static::LOG_DEBUG,"inserting new wp_fl_transaction in contest_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Inserted $rows_affected wp_fl_transaction in contest_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);



                //------------------------------------------------------------------------------------------------------
                //code-notes update balance for all amounts > 0
                $sql= /** @lang text */
                    "UPDATE wp_usermeta m
                    INNER JOIN (
                        SELECT user_id,sum(amount) as total_amount_per_user ,old_freelancer_balance
                        FROM $temp_table_name
                        WHERE amount > 0
                        GROUP BY user_id 
                        )j ON j.user_id = m.user_id 
                    SET m.meta_value = ROUND(j.old_freelancer_balance + j.total_amount_per_user,2)
                    where m.meta_key = 'total_user_balance' ";
                static::log(static::LOG_DEBUG,"updating total_user_balance wp_usermeta  in contest_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected user balances in total_user_balance wp_usermeta in contest_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);


                //------------------------------------------------------------------------------------------------------
                //code-notes project status for all projects
                $sql=
                    /** @lang text */
                    "UPDATE wp_postmeta m
                    INNER JOIN $temp_table_name j ON j.contest_id = m.post_id
                    SET m.meta_value = j.da_status
                    where m.meta_key = 'project_new_status' ";
                static::log(static::LOG_DEBUG,"updating project_new_status wp_postmeta  in contest_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected project_new_status in wp_postmeta in contest_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);

                //------------------------------------------------------------------------------------------------------
                //code-notes update post meta contest_completed_proposals for each post,
                // but if multiple proposals for a post only do one update


                $sql=
                    /** @lang text */
                    "UPDATE wp_postmeta m
                    INNER JOIN (
                        SELECT contest_id,
                        GROUP_CONCAT(proposal_id SEPARATOR ', ') as new_ids
                        FROM $temp_table_name
                        WHERE completed_meta_id IS NOT NULL
                        GROUP BY contest_id 
                        )j ON j.contest_id = m.post_id 
                    SET m.meta_value = IF(m.meta_value IS NULL OR m.meta_value = '',
                                          j.new_ids,
                                          CONCAT(m.meta_value,', ',j.new_ids)
                                        )
                    where m.meta_key = 'contest_completed_proposals' ";
                static::log(static::LOG_DEBUG,"updating contest_completed_proposals wp_postmeta  in contest_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected contest_completed_proposals wp_postmeta in contest_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);


                //------------------------------------------------------------------------------------------------------
                //code-notes insert post meta contest_completed_proposals for each post, if it was not added yet (first time awards completing)
                // but if multiple proposals for a post only do one update


                $sql=
                    /** @lang text */
                    "
                    INSERT INTO wp_postmeta(post_id,meta_key, meta_value)
                    SELECT 
                            contest_id                               as post_id,
                            'contest_completed_proposals'            as meta_key,
                        GROUP_CONCAT(proposal_id SEPARATOR ', ')     as meta_value
                        FROM $temp_table_name
                        WHERE completed_meta_id IS NULL
                        GROUP BY contest_id 
                        
                    ";
                static::log(static::LOG_DEBUG,"updating contest_completed_proposals wp_postmeta  in contest_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Inserted $rows_affected  contest_completed_proposals wp_postmeta in contest_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);



                //------------------------------------------------------------------------------------------------------
                //code-notes commit the transaction
                $sql = " COMMIT;";
                static::log(static::LOG_DEBUG,"comitted contest_asked_to_complete",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);
                $line = "Saved all changes in contest_asked_to_complete";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);
            } catch (Exception $e) {
                $sql = "ROLLBACK;";
                static::log(static::LOG_DEBUG,"rolling back changes in contest_asked_to_complete",$sql);
                $wpdb->query($sql);
                if ($wpdb->last_error !== '') {
                    $msg =  'contest_asked_to_complete cannot rollback '. $wpdb->last_error;
                    $log[] = $msg;
                    static::log(static::LOG_DEBUG,$msg);
                } else {
                    $msg = "Rolled Back changes in contest_asked_to_complete";
                    $log[] = $msg;
                    static::log(static::LOG_DEBUG,$msg);
                }

                throw $e;
            }

        } catch (Exception $e) {
            $log[] =  'error in contest_asked_to_complete '. will_get_exception_string($e);
            static::log(static::LOG_ERROR,'error in contest_asked_to_complete',will_get_exception_string($e));
        }
    }

    /**
     * @param string[] $log OUT REF
     * @param int $limit_per_type
     *
     * Rejects all proposals that are timed out, after the customer requested rejection
     * Unlike the php version of this in the @see hz_change_status_contest_proposal()
     * we are doing everything through batch sql,
     * so that many proposals can be rejected in a short time
     *
     * To reject the proposal
     * 1) the status of the proposal needs to be changed to rejected
     * 2) the money needs to be returned to the customer
     * 2a) make a new transaction
     * 2b) change the customer's balance
     * 3) A message needs to be made for the proposal
     * 4) the contest status needs to be changed
     *
     * LOGIC:
     *  we get the first N action red dots of type contest_asked_to_reject (where $limit_per_type is N)
     *  which has expired action times
     *  and we put all the information needed from those, to do all the steps listed above, into a temporary table
     *  So, now we have this temporary table which drives all the other sql
     *
     * If there is nothing to do, then we return
     * Else, if there are actions waiting, we do batch sql to do the following
     * A) insert new transactions into the wp_fl_transaction table
     * B) update the proposal status and timestamp (which will also update the red dot as being done via trigger)
     * C) update the user balance
     * D) send a message to the proposal
     * E) change the contest status
     *
     * these three steps are rolled into a transaction, so that if something goes wrong, we reset the data to before we started to change it here
     */
    protected static function contest_asked_to_reject(&$log,$limit_per_type) {
        global $wpdb;

        try {
            $event_name = FLRedDot::EVENT_CONTEST_ASKED_TO_REJECT;


            //------------------------------------------------------------------------------------------------------
            //code-notes drop the temp table if its still being used

            $temp_table_name = static::TEMP_TABLE_NAME_CONTEST_ASKED_TO_REJECT;
            $sql = "DROP TEMPORARY TABLE IF EXISTS  $temp_table_name";
            static::log(static::LOG_DEBUG,"dropping of temp table $temp_table_name in contest_asked_to_reject",$sql);
            $wpdb->query($sql);

            //code-notes get fee constants first
            $awarded_flag_value = FLPostLookupDataHelpers::POST_USER_DATA_FLAG_AWARDED_CONTEST;

            //------------------------------------------------------------------------------------------------------
            //code-notes Create and fill in the temp table
            $sql = "CREATE TEMPORARY TABLE $temp_table_name 
                (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                proposal_id int,
                contest_id bigint unsigned,
                estimated_budget decimal(15,2),
                amount decimal(15,2),
                user_id bigint unsigned,
                old_customer_balance decimal(15,2),
                old_freelancer_balance decimal(15,2),
                customer_id bigint unsigned,
                freelancer_id bigint unsigned,
                payment_status varchar(120),
                type varchar(42),
                description text,
                gateway varchar(20),
                gateway_txn_id varchar(200),
                txn_id varchar(40),
                da_name_freelancer varchar(50),
                da_name_customer varchar(50),
                da_message varchar(120),
                da_status varchar(120),
                INDEX my_contest_index (contest_id), INDEX my_user_index (user_id))
                ENGINE=MyISAM 
                SELECT DISTINCT 
                dot.proposal_id as proposal_id,
                dot.contest_id as contest_id,
                 IF(budget_meta.meta_value IS NULL OR budget_meta.meta_value = '',
                  0,
                  cast(budget_meta.meta_value as decimal(15,2))
                ) as estimated_budget,
                
                cast(ROUND(
                        IF(budget_meta.meta_value IS NULL OR budget_meta.meta_value = '',
                          0,
                          cast(budget_meta.meta_value as decimal(15,2))
                        ) 
                        ,2) as decimal(15,2))  as amount, -- cast to decimal
                
                post.post_author as user_id,
                cast(look_customer.total_user_balance as decimal(15,2)) as old_customer_balance,
                cast(look_freelancer.total_user_balance as decimal(15,2)) as old_freelancer_balance,
                look_customer.user_id as customer_id,
                look_freelancer.user_id as freelancer_id,
                'done' as payment_status,
                'contestRejected' as type,
                'The competition is cancelled' as description,
                'wallet' as gateway,
                '' as gateway_txn_id,
                concat(UNIX_TIMESTAMP(NOW()),'-u-',post.post_author) as txn_id,
                user_freelancer.user_nicename as da_name_freelancer,
                user_customer.user_nicename as da_name_customer,
                CONCAT('Proposal rejected by ',user_customer.user_nicename) as da_message,
                CONCAT('Proposal ',prop.number,': Rejected ') as da_status
                FROM wp_fl_red_dots dot
                INNER JOIN wp_proposals prop on dot.proposal_id = prop.id
                INNER JOIN wp_posts post on prop.post_id = post.ID
                INNER JOIN wp_fl_user_data_lookup look_freelancer ON look_freelancer.user_id = prop.by_user
                INNER JOIN wp_fl_user_data_lookup look_customer ON look_customer.user_id = post.post_author
                INNER JOIN wp_users user_freelancer ON user_freelancer.ID = look_freelancer.user_id
                INNER JOIN wp_users user_customer ON user_customer.ID = look_customer.user_id
                LEFT JOIN wp_postmeta budget_meta ON budget_meta.post_id = post.ID and budget_meta.meta_key = 'estimated_budgets'
                INNER JOIN wp_fl_post_user_lookup awarded_flag ON 
                  awarded_flag.post_id = post.ID AND awarded_flag.lookup_flag = $awarded_flag_value AND awarded_flag.lookup_val = prop.id 
                WHERE dot.event_name = '$event_name' AND dot.is_future_action > 0 and dot.future_timestamp < NOW()
                         AND look_customer.total_user_balance  >= 0
                ORDER BY dot.id LIMIT $limit_per_type";

            static::log(static::LOG_DEBUG,"creation of temp table $temp_table_name in contest_asked_to_reject",$sql);
            $wpdb->query($sql);
            will_throw_on_wpdb_error($wpdb);

            $sql = /** @lang text */
                "SELECT * FROM $temp_table_name where 1 order by id";
            $res = $wpdb->get_results($sql);
            will_send_to_error_log('temp table dump',$res);

            $sql = "SELECT count(*) as da_count FROM $temp_table_name";
            static::log(static::LOG_DEBUG,"counting of temp table $temp_table_name in contest_asked_to_reject",$sql);
            $res = $wpdb->get_results($sql);
            $total_to_process = intval($res[0]->da_count);
            $msg =  "Found $total_to_process red dots for contest_asked_to_reject";
            $log[] = $msg;
            static::log(static::LOG_DEBUG,$msg);
            if (!$total_to_process) {return;}

            try {

                //------------------------------------------------------------------------------------------------------
                //code-notes we want to wrap up all the changes in a transaction in case something goes wrong
                $sql = "START TRANSACTION;";
                static::log(static::LOG_DEBUG,"starting transaction in contest_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);



                //------------------------------------------------------------------------------------------------------
                //code-notes update the milestone with the new status
                $sql=
                    /** @lang text */
                    "UPDATE wp_proposals prop
                    INNER JOIN $temp_table_name j ON j.proposal_id = prop.id 
                    SET prop.status = 'rejected',prop.updated_at = NOW() 
                    where 1";
                static::log(static::LOG_DEBUG,"updating of proposal to rejected in contest_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);
                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected wp_proposals in  contest_asked_to_reject";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);



                //------------------------------------------------------------------------------------------------------
                //code-notes make transactions for all amounts greater than zero
                $sql = /** @lang text */
                    "
                INSERT INTO wp_fl_transaction (txn_id,user_id,project_id,
                                                      amount,payment_status,type,description,gateway,gateway_txn_id,proposal_id)
                SELECT txn_id,user_id,contest_id,amount,payment_status,type,description,gateway,gateway_txn_id,proposal_id
                FROM $temp_table_name WHERE amount > 0;
              
                ";
                static::log(static::LOG_DEBUG,"inserting new wp_fl_transaction for customer in contest_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Inserted $rows_affected wp_fl_transaction in contest_asked_to_reject";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);



                //------------------------------------------------------------------------------------------------------
                //code-notes update balance for all amounts > 0
                $sql= /** @lang text */
                    "UPDATE wp_usermeta m
                    INNER JOIN (
                        SELECT user_id,sum(amount) as total_amount_per_user ,old_customer_balance
                        FROM $temp_table_name
                        WHERE amount > 0
                        GROUP BY user_id 
                        )j ON j.user_id = m.user_id 
                    SET m.meta_value = ROUND(j.old_customer_balance + j.total_amount_per_user,2)
                    where m.meta_key = 'total_user_balance' ";
                static::log(static::LOG_DEBUG,"updating total_user_balance wp_usermeta  in contest_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected customer user balances in total_user_balance wp_usermeta in contest_asked_to_reject";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);


                //------------------------------------------------------------------------------------------------------
                //code-notes project status for all projects
                $sql=
                    /** @lang text */
                    "UPDATE wp_postmeta m
                    INNER JOIN $temp_table_name j ON j.contest_id = m.post_id
                    SET m.meta_value = j.da_status
                    where m.meta_key = 'project_new_status' ";
                static::log(static::LOG_DEBUG,"updating project_new_status wp_postmeta  in contest_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Updated $rows_affected project_new_status in wp_postmeta in contest_asked_to_reject";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);

                
                
                //------------------------------------------------------------------------------------------------------
                //code-notes insert wp_message_history

                $sql = /** @lang text */
                    "
                INSERT INTO wp_message_history (message,proposal_id,added_by,created_at)
                SELECT da_message,proposal_id,freelancer_id,NOW()
                FROM $temp_table_name WHERE 1;
              
                ";
                static::log(static::LOG_DEBUG,"inserting new wp_message_history in contest_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                $rows_affected = $wpdb->rows_affected;
                $line = "Inserted $rows_affected wp_message_history in project_asked_to_reject";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);





                //------------------------------------------------------------------------------------------------------
                //code-notes commit the transaction
                $sql = " COMMIT;";
                static::log(static::LOG_DEBUG,"comitted contest_asked_to_reject",$sql);
                $wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);
                $line = "Saved all changes in contest_asked_to_reject";
                $log[] = $line;
                static::log(static::LOG_DEBUG,$line);
            } catch (Exception $e) {
                $sql = "ROLLBACK;";
                static::log(static::LOG_DEBUG,"rolling back changes in contest_asked_to_reject",$sql);
                $wpdb->query($sql);
                if ($wpdb->last_error !== '') {
                    $msg =  'contest_asked_to_reject cannot rollback '. $wpdb->last_error;
                    $log[] = $msg;
                    static::log(static::LOG_DEBUG,$msg);
                } else {
                    $msg = "Rolled Back changes in contest_asked_to_reject";
                    $log[] = $msg;
                    static::log(static::LOG_DEBUG,$msg);
                }

                throw $e;
            }

        } catch (Exception $e) {
            $log[] =  'error in contest_asked_to_reject '. will_get_exception_string($e);
            static::log(static::LOG_ERROR,'error in contest_asked_to_reject',will_get_exception_string($e));
        }
    }

}
FLRedDotFutureActions::turn_on_debugging(FreelinguistDebugging::LOG_WARNING);
