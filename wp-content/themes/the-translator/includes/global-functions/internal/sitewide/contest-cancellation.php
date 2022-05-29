<?php

//notes area

/*
 * current-php-code 2020-Oct-05
 * internal-call
 * input-sanitized :
 */

/**
 * A way to organize cancellations when displaying
 *

 */
class FreelinguistContestCancelNode
{

    /**
     * @var int $cancel_id
     */
    public $cancel_id = null;

    /**
     * @var int $contest_id
     */
    public $contest_id = null;

    /**
     * @var string $contest_url
     */
    public $contest_url = null;

    /**
     * @var string $modified_id
     */
    public $modified_id = null;

    /**
     * @var string $contest_title
     */
    public $contest_title = null;

    /**
     * @var int $customer_id
     */
    public $customer_id = null;

    /**
     * @var string $customer_email
     */
    public $customer_email = null;

    /**
     * @var string $customer_url
     */
    public $customer_url = null;

    /**
     * @var int $days_extended
     */
    public $days_extended = null;

    /**
     * null if no action take right now
     * true if fully or partially approved
     * false if disapproved
     * @var null|bool $has_been_approved
     */
    public $has_been_approved = null;

    /**
     * @var int $percentage (0..100)
     */
    public $percentage = null;

    /**
     * @var int $opened_ts (unix timestamp)
     */
    public $opened_ts = null;

    /**
     * @var int $original_deadline_ts (unix timestamp)
     */
    public $original_deadline_ts = null;

    /**
     * @var int $processed_ts (unix timestamp)
     */
    public $processed_ts = null;

    /**
     * @var int $posted_by_id
     */
    public $posted_by_id = null;

    /**
     * @var string $posted_by_email
     */
    public $posted_by_email = null;

    /**
     * @var string $posted_by_url
     */
    public $posted_by_url = null;

    /**
     * @var float $original_budget
     */
    public $original_budget = null;

}

class FreelinguistContestCancellation
{

    const STATUS_MESSAGES = [
        "start" => "Cancellation request in process",
        "accept" => "Cancellation request is accepted. Competition is cancelled.",
        "reject" => "Cancellation request is rejected. Competition is extended and resumed.",
        "percent" => "Competition is extended and [:percent:] partially refunded",
        "extended" => "Competition has been extended by [:days:]."
    ];

    const DEFAULT_UNDO_CANCEL_CONTEST_LIMIT_HOURS = 48;

    public function __construct()
    {
        add_action('wp_ajax_freelinguist_request_cancel_contest', array($this, 'public_ajax_request_cancel'));
        add_action('wp_ajax_freelinguist_claim_contest_prize', array($this, 'public_ajax_claim_prize'));
    }

    /**
     * @param string $action
     * When the button is pressed, it sends a job_id in the POST
     * prints outs json and dies -> the value of the new row for the cancel request from
     * @uses FreelinguistContestCancellation::create_new_cancellation()
     */
    public function public_ajax_request_cancel($action)
    {
        $job_id = intval(FLInput::get('job_id'));
        $response = ['status' => 0, 'message' => 'nothing done', 'action' => $action, 'log' => []];
        try {
            if ($job_id) {

                $log = [];
                $new_id = null;
                if ($job_id) {
                    $new_id = static::create_new_cancellation($job_id, $log, false);
                    $log[] = "new id returned is: $new_id";
                    $log[] = "ok to send out";
                    $response['log'] = $log;
                    $response['status'] = 1;
                    $response['message'] = static::STATUS_MESSAGES['start'];
                }
            }
        } catch (Exception $e) {
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            $response['log'] = $log;
        }


        echo wp_json_encode($response);
        wp_die();
    }

/**
     * @param string $action
     * When the button is pressed, it sends a contest_id in the POST
     * The current user is the freelancer claiming
     * prints outs json and dies -> the value of the new row for the cancel request from
     * @uses FreelinguistContestCancellation::claim_prize_after_award_date()
     */
    public function public_ajax_claim_prize($action)
    {
        $response = ['status' => 0, 'message' => 'nothing done', 'action' => $action, 'log' => []];
        $contest_id = intval(FLInput::get('contest_id'));
        try {
            if ($contest_id) {

                $log = [];
                $freelancer_id = get_current_user_id();

                $amount = static::claim_prize_after_award_date($contest_id,$freelancer_id,$log);
                $log[] = "No exceptions thrown, so must be ok";

                $response['log'] = $log;
                $response['status'] = 1;
                $response['amount'] = $amount;
                $response['message'] = 'Prize Claimed of $'. amount_format($amount);
            }
        } catch (Exception $e) {
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
            $response['log'] = $log;
        }


        echo wp_json_encode($response);
        wp_die();
    }



    /**
     * @param int $job_id
     * @param string $reason OUT PARAM , if cannot be shown will give reason
     * @param string[] $log debug log array
     * @return string|bool ,    false if nothing to be shown
     *                          true if can be shown and pressed,
     *                          string if cannot be pressed but there is a status
     *
     * A button can only be shown if:
     * 1) the job is a contest
     * 2) that has insurance
     * 3) the contest if over
     * 4) and nobody has been awarded
     * 5) and there is not already a cancel request
     */
    public static function can_cancel_button_be_shown($job_id, &$reason, &$log = [])
    {
        try {
            $reason = '';
            $log = [];
            $what = static::create_new_cancellation($job_id, $log, true,true,true);
            return $what;
        } catch (RuntimeException $r) {
            $reason = $r->getMessage();
            if ($r->getCode() === 100) { //already a request
                //code-notes see if anything done, if not then return 'Cancellation request in process'
                // else return 'Processed'
                $info = static::get_request_row((int)$job_id);
                $log[] = 'found row';
                $log[] = $info;
                if ($info->processed_when) {
                    $cancel_status = 'Cancellation Processed';
                } else {
                    $cancel_status = static::STATUS_MESSAGES['start'];
                }
                return get_custom_string_return($cancel_status);
            } else {
                return false;
            }

        }
    }

    /*
     * A contest is cancelled and ended if there is approval with no days extension
     * else if approval and new deadline is not over yet, this contest is not ended
     * else see if approval to decide
     */
    public static function is_contest_cancelled_and_ended($job_id) {
        $info = static::get_request_row($job_id);
        if (empty($info)) {return false;}
        $deadline_ts = $info->original_deadline_ts;
        $days_extended = (int)$info->days_extended;
        if ($days_extended) {
            $new_deadline_ts = ((34*60*60) * $days_extended) + $deadline_ts;
            $is_past_deadline = false;
            $current_time = time();
            if ($current_time < $new_deadline_ts) { $is_past_deadline = true;}
            return $info->is_approved && $is_past_deadline;
        }

        if ($info->is_approved) {
            return true;
        }

        return false;

    }

    protected static function get_request_row($job_id)
    {
        global $wpdb;
        $res = $wpdb->get_results(
            "SELECT id,contest_id,is_approved,percentage_partial,
                days_extended,admin_wp_user_id,original_deadline_when,opened_when,
                processed_when,original_budget,
                UNIX_TIMESTAMP(original_deadline_when) as original_deadline_ts
 
                from wp_contest_insurance_refund WHERE contest_id= $job_id; ");

        if ($wpdb->last_error) {
            throw new RuntimeException("Error getting data from contest_insurance_refund row: " . $wpdb->last_error);
        }

        if ($res === false) {
            throw new RuntimeException("Unknown error getting data for the contest_insurance_refund row ");
        }

        if (!empty($res)) {
            $out = $res[0];
            if ($out->is_approved !== null) {
                $out->is_approved = (int) $out->is_approved;
            }
            if ($out->percentage_partial !== null) {
                $out->percentage_partial = (int) $out->percentage_partial;
            }
            if ($out->days_extended !== null) {
                $out->days_extended = (int) $out->days_extended;
            }
            if ($out->id !== null) {
                $out->id = (int) $out->id;
            }
            if ($out->original_budget !== null) {
                $out->original_budget = (float)$out->original_budget;
            }
            return $out;
        }
        return null;

    }

    /**
     * @param int $job_id
     * @param string[] $log
     * @param bool $b_check_only , if true, will see if can create new request, but will not add to db
     *                  how it works is, if this function returns without throwing an exception its ok
     * @param bool $b_check_after_award_deadline, default true
     * @param bool $b_check_existing if true, will check for the existing row, default false
     * code-notes added in a new param to check for existing cancel requests when checking to see if cancellation can be made
     * @return int|true
     * check to make sure:
     * 1) its a contest
     * 2) there is insurance on it
     * 3) the contest is over
     * 4) nobody was awarded
     * 5) and does not already have a cancel request going on
     *
     * If things are okay then create a new row and return the id of the wp_contest_insurance_refund
     * and add a meta value for the post : content-cancel-status
     */
    public static function create_new_cancellation($job_id, &$log, $b_check_only = false,
                                                   $b_check_after_award_deadline = true,$b_check_existing=false)
    {
        global $wpdb;
        $log = [];
        $job_id = intval($job_id);
        $b_is_contest = static::is_a_valid_contest($job_id,$log);
        if (!$b_is_contest) { static::throw_helper($log,"Not a competition");}

        $b_is_past_deadline = static::is_after_deadline_date($job_id,$log,$contest_deadline_ts,$diff_in_seconds);
        if (!$b_is_past_deadline) {
            static::throw_helper($log,"Allowed after deadline");
        }

        if ($b_check_after_award_deadline) {
            $b_is_past_award_time = static::is_after_award_date($job_id, $log, $award_period_ends_at_ts);
            if ($b_is_past_award_time) {
                static::throw_helper($log, "Not allowed after Award deadline");
            }
        }

        $b_has_insurance = intval(get_post_meta($job_id, 'is_guaranted', true));
        if (!$b_has_insurance) {
            static::throw_helper($log,"No insurance");
        }



        $awarded = get_post_meta($job_id, 'contest_awardedProposalPrizes', true);
        if ($awarded) {
            static::throw_helper($log,"One Proposal Already Awarded");
        }

        $res = $wpdb->get_results(/** @lang text */
            "SELECT id from {$wpdb->prefix}contest_insurance_refund WHERE contest_id= $job_id; ");

        if ($wpdb->last_error) {
            static::throw_helper($log,"Error getting data from contest_insurance_refund row: " . $wpdb->last_error);
        }

        if ($res === false) {
            static::throw_helper($log,"Unknown error getting data for the contest_insurance_refund row ");
        }


        if ($b_check_only && !$b_check_existing) {
            $log[] = "Finished checking, will not check for existing cancellations";
            return true;
        } else {
            if (!empty($res)) {
                //$request_id = $res[0]->id;
                static::throw_helper($log,"Requested before", 100);
            }
        }

        //code-notes added condition in checking, so that the function to display the cancel button to the customer can use this code
        if ($b_check_existing) {
            if (!empty($res)) {
                $log[] = "Found an existing cancellation";
                static::throw_helper($log,"Requested before", 100);
            }
            return true;
        }

        $original_budget =  floatval(get_post_meta($job_id,'estimated_budgets',true));

        //finally, insert the new row

        $sql =
            "INSERT INTO wp_contest_insurance_refund 
                        (contest_id,original_deadline_when,opened_when,original_budget) 
                        VALUES ($job_id,FROM_UNIXTIME($contest_deadline_ts), NOW(),$original_budget)
                         ";
        $log[] = $sql;

        $res = $wpdb->query($sql);

        if ($wpdb->last_error) {
            static::throw_helper($log,"Error when inserting the contest_insurance_refund row of ($job_id): " . $wpdb->last_error);
        }

        if ($res === false) {
            static::throw_helper($log,"Unknown error when insert the contest_insurance_refund row of ($job_id)");
        }
        $last_id = $wpdb->insert_id;
        $log[] = 'contest_insurance_refund id ' . $last_id;
        update_post_meta($job_id, 'content-cancel-status', static::STATUS_MESSAGES['start']);
        return $last_id;
    }

    /**
     * @param $cancel_id
     * @param $log
     *
     * resets original deadline
     *  date('Y-m-d', timestamp of original deadline);
     *
     * adds back in insurance flag
     *  is_guaranted to 1
     *
     *
     * removes post meta keys about the cancellation :
     *  is_cancellation_approved
     *  cancellation_processed
     *  content-cancel-status
     *
     * changes customer again (may result in negative balance)
     * and sets the contest estimated_budgets meta to  what it was
     *
     * sets null some columns in the wp_contest_insurance_refund table
     *  is_approved
     *  percentage_partial
     *  days_extended
     *  admin_wp_user_id
     *  processed_when
     */
    public static function undo_cancellation($cancel_id, &$log) {
        global $wpdb;

        $log[] = "called cancel for $cancel_id";
        $nodes = static::list_data($cancel_id,null,null,null,null);
        if (empty($nodes)) {
            static::throw_helper($log,"Cannot undo: Cannot find cancel id of $cancel_id");
        }
        $node = $nodes[0];
        $original_deadline_string = date('Y-m-d', $node->original_deadline_ts);
        $log[] = "setting original deadline: $original_deadline_string";
        update_post_meta( $node->contest_id, 'job_standard_delivery_date', will_validate_string_date_or_make_future($original_deadline_string));
        $log[] = "adding is_guaranted flag";
        update_post_meta( $node->contest_id, 'is_guaranted', 1);
        $log[] = "removing is_cancellation_approved flag";
        delete_post_meta($node->contest_id,'is_cancellation_approved');
        $log[] = "removing cancellation_processed flag";
        delete_post_meta($node->contest_id,'cancellation_processed');
        $log[] = "removing content-cancel-status flag";
        delete_post_meta($node->contest_id,'content-cancel-status');


        if($node->has_been_approved === 1) {
            $log[] = "charging customer again what was refunded (calculating this)";
            if ($node->percentage) {
                $percent_to_client = (int)$node->percentage;
                if ($percent_to_client > 100) { $percent_to_client = 100;}
                if ($percent_to_client < 0) { $percent_to_client = 0;}
            } else {
                $percent_to_client = 100;
            }
            $percent_to_client = floatval($percent_to_client);
            $log[] = "Percent to Client: $percent_to_client";
            $original_contest_budget = (float)$node->original_budget;
            $charge = round($original_contest_budget * $percent_to_client/100,2);
            if ($charge) {
                $log[] = "charge to client will be: $charge";
                fl_transaction_insert( '-'.$charge, 'done', 'undo_refund',  $node->customer_id, NULL,
                    "Undoing Refund of $percent_to_client% competition budget amount", 'wallet',
                    '', $node->contest_id, NULL,NULL );
            } else {
                $log[] = "Nothing to charge client: $charge";
            }

            $log[] = "restored contest budget: $original_contest_budget";
            update_post_meta($node->contest_id,'estimated_budgets',$original_contest_budget); //do not format string here
        } else {
            $log[] = "This was not approved, so nothing to charge customer";
        }
        $the_id = $node->cancel_id;
        //update request
        $sql = /** @lang text */
            "
            UPDATE {$wpdb->prefix}contest_insurance_refund 
            SET 
            is_approved = NULL,
            percentage_partial = NULL,
            days_extended = NULL,
            admin_wp_user_id = NULL,
            processed_when = NULL
            WHERE
            id = $the_id
        ";
        $log[] = "resetting request fields";
        $log[] = $sql;

        $res = $wpdb->query($sql);

        if ($wpdb->last_error) {
            static::throw_helper($log,"Error setting data to contest_insurance_refund row: " . $wpdb->last_error);
        }

        if ($res === false) {
            static::throw_helper($log,"Unknown error setting data for the contest_insurance_refund row ");
        }



    }
    /**
     * @param int $contest_id
     * @param bool $b_approval
     * @param int $days_extended
     * @param int $percentage
     * @param string[] $log
     * @return void
     */
    public static function decide_cancellation($contest_id,$b_approval,$days_extended,$percentage,&$log=[]) {
        global $wpdb;
        $log = [];
        $contest_id = (int)$contest_id;
        $log[] = "deciding cancelation on contest id: $contest_id";

        //code-notes check to make sure that things can still be cancelled before we make a decision and after the request has been made
        $log[] = "Rechecking contest status";
        $existing = static::get_request_row($contest_id);
        $log[] = "Trying to get existing";
        $log[] = $existing;
        try {
            static::create_new_cancellation($contest_id, $log, true,false);
        } catch(RuntimeException $r) {
            //code-notes If existing but something makes this invalid, then close it out then throw error
            if (empty($existing)) {
                static::throw_helper($log,"Cannot update cancel request of of $contest_id. It does not exist");
            } else {
                static::throw_helper($log,"There is an issue with updating this request:". $r->getMessage(). ' ['.$r->getCode() . ']');
                $log[] = 'Closing out this request';
            }
        }


        if (empty($existing)) {
            static::throw_helper($log,"The job of $contest_id does not have a cancel request");
        }
        if ($existing->is_approved !== null) {
            static::throw_helper($log,"Cannot update cancellation once a decision has been made");
        }
        $the_id = $existing->id;
        //sanity check, set/check special flag to make sure we only do this once per contest
        $processed_id = get_post_meta($contest_id, 'cancellation_processed', true);
        if ($processed_id) {
            static::throw_helper($log,"Cannot change the contest per cancellation request of [{$the_id}] because it was already done by [$processed_id]");
        }

        $log[] = "Contest status ok for update and action";

        $days_extended = (int)$days_extended;
        $percentage = (int)$percentage;
        if ($percentage > 100) { $percentage = 100;}
        $poster_id = get_current_user_id();

        if ($b_approval) {
            $b_approval = 1;
        } else {
            $b_approval = 0;
        }


        if ($percentage && !$b_approval) {
            static::throw_helper($log,"Canot deny request and set percentage");
        }

        $status_type = null; // start|accept|reject|percent|extended
        if ($b_approval) {
            if ($percentage) {$status_type = 'percent';}
            else if ($days_extended) {$status_type = 'extended';}
            else {$status_type = 'accept';}
        } else {
            $status_type = 'reject';
        }

        $status_message = static::STATUS_MESSAGES[$status_type];

        $days_substring = "";
        if ($days_extended) {
            if ($days_extended > 1) { $days_substring = "$days_extended Days";}
            else {
                $days_substring = "$days_extended Day";
            }
        }

        $percentage_substring = '';
        if ($percentage) {
            $percentage_substring = "$percentage%";
        }
        $status_message = str_replace('[:percent:]',$percentage_substring,$status_message);
        $status_message = str_replace('[:days:]',$days_substring,$status_message);

        $da_date = date("F d Y ");
        $status_message = $da_date . ' : ' . $status_message;

        if (empty($days_extended)) {$days_extended = 'NULL';}
        if (empty($percentage)) {$percentage = 'NULL';}
        if (empty($poster_id)) {$poster_id = 'NULL';}
        $log[] = "days extended id: $days_extended";
        $log[] = "percentage id: $percentage";
        $log[] = "poster id: $poster_id";


        $sql = /** @lang text */
            "
            UPDATE {$wpdb->prefix}contest_insurance_refund 
            SET 
            is_approved = $b_approval,
            percentage_partial = $percentage,
            days_extended = $days_extended,
            admin_wp_user_id = $poster_id,
            processed_when = NOW()
            WHERE
            id = $the_id
        ";
        $log[] = $sql;

        $res = $wpdb->query($sql);

        if ($wpdb->last_error) {
            static::throw_helper($log,"Error getting data from contest_insurance_refund row: " . $wpdb->last_error);
        }

        if ($res === false) {
            static::throw_helper($log,"Unknown error getting data for the contest_insurance_refund row ");
        }

        update_post_meta($contest_id, 'content-cancel-status', $status_message);
        $log[] = $status_message;

        $nodes = static::list_data($the_id,null,null,null,null);
        if (empty($nodes)) {
            static::throw_helper($log,"For some reason cannot get the contest request row we just made (id of $the_id)");
        }
        if (is_null($existing->is_approved)) {
            $log[] = "Was not a previous decision so doing logic now: approval is ".$existing->is_approved? 'true': 'false' ;
            static::do_cancel_logic_on_contest($nodes[0],$log);
        } else {
            $log[] = "Cannot change contest as is_approved = ";
            static::throw_helper($log,"Cannot change contest id $contest_id of because it was already approved or denied");
        }


    }

    /**
     * @param FreelinguistContestCancelNode $node
     * @param string[] $log IN OUT REF
     *
     *
     * When this code is executed the requirements still stand. The contest deadline has passed and no proposals have been awarded
     *
     *  After receiving Cancellation Request, Admin can choose on the the following three:
     *      WHERE x% is percentage_partial (given to freelancers)
            Partial Approval:  X% of prize is successful, refund the remaining 1-X% to customer.
            Cancel is Approved: 0% Partial Approval.allowed only if no proposal has been awarded.
                Only here, 			set 	isCancelled=true.
            Cancel is Denied: 100% Partial Approval.


        X% Partial Approval by Admin:
            a. Refund (1-X%)* original price
            b. Record transaction in Wallet history.
            c. price = price * X%.
            d.  If X%>0:
                I). New Deadline Date = current date + Extension_Days.

     * code-notes Added all contest processing, as well as wallet changes, here in @see FreelinguistContestCancellation::do_cancel_logic_on_contest()

     */
    
    protected static function do_cancel_logic_on_contest($node,&$log = []) {
        $log[] = "doing cancel logic";
        if (!$node->contest_id) {static::throw_helper($log,"Contest id is null");}
        if (!$node->customer_id) {static::throw_helper($log,"Customer id is null");}
        $the_id = $node->contest_id;
        if ($node->has_been_approved) {
            $log[] = "There was approval, so doing money logic";
            if ($node->percentage) {
                $percent_to_client = (int)$node->percentage;
                if ($percent_to_client > 100) {
                    $percent_to_client = 100;
                }
                if ($percent_to_client < 0) {
                    static::throw_helper($log, "Percentage cannot be negative when doing cancellation requests (cancel id $the_id ");
                }
            } else {
                $percent_to_client = 100;
            }
            $log[] = "Percent to Client: $percent_to_client";
            $contest_id = $node->contest_id;
            //will be between 0 and 100
            $percent_to_client = floatval($percent_to_client);
            $percent_to_freelancer = floatval(100 - $percent_to_client);
            $log[] = "Percent to Freelancer: $percent_to_freelancer";
            $contest_budget = (float)get_post_meta($contest_id, 'estimated_budgets', true);
            $log[] = "Contest Budget: $contest_budget";
            if ($percent_to_client  > 0) {
                $log[] = "Giving back something to client";
                //send this much back to wallet first
                $refund = round($contest_budget * $percent_to_client / 100, 2);
                $log[] = "refund: $refund";
                $current_user_balance = (float)get_user_meta($node->customer_id, 'total_user_balance', true);
                $log[] = "current user balance: $current_user_balance";
                fl_transaction_insert($refund, 'done', 'refund', $node->customer_id, NULL,
                    "Refund of $percent_to_client% competition budget amount", 'wallet',
                    '', $node->contest_id, NULL,NULL);
                $new_user_balance = $current_user_balance + $refund;
                $log[] = "new user balance: $new_user_balance";
                update_user_meta($node->customer_id, 'total_user_balance', amount_format($new_user_balance));
                $new_contest_budget = round($contest_budget - $refund, 2);
                $log[] = "new contest budget: $new_contest_budget";
                update_post_meta($node->contest_id, 'estimated_budgets', $new_contest_budget); //do not format string here
            }

            if ($percent_to_freelancer > 0) {
                $log[] = "removing is_guaranted";
                delete_post_meta($node->contest_id, 'is_guaranted'); //code-notes remove is_guaranted if any amount goes to freelancer
            }

        } else {
            $log[] = "There was not approval, so nothing changes with money";
        }

        if ($node->days_extended) {
            $log[] = "Resetting the contest deadline to current date + days extended";
            //set new deadline date
            $now_ts = time();
            $log[] = "days to extend: " . $node->days_extended;
            $seconds_to_extend = 60 * 60 * 24 * $node->days_extended;
            $log[] = "seconds to extend: " . $seconds_to_extend;
            $future_ts = $now_ts + $seconds_to_extend;
            $log[] = "timestamp to extend to: " . $future_ts;
            $date_string = date('Y-m-d', $future_ts);
            $log[] = "new date string for new deadline: " . $date_string;
            update_post_meta($node->contest_id, 'job_standard_delivery_date', will_validate_string_date_or_make_future($date_string));

        }

        //at the end, set the flags
        update_post_meta($node->contest_id, 'cancellation_processed', $node->cancel_id);
        $log[] = "new post meta cancellation_processed: ".$node->cancel_id ;
        if ($node->has_been_approved && (intval($node->days_extended) === 0)) {
            $log[] = "Was approved, so new post meta is_cancellation_approved: 1" ;
            update_post_meta($node->contest_id, 'is_cancellation_approved', 1);
        } else {
            $log[] = "Was not approved, so DELETING post meta is_cancellation_approved" ;
            delete_post_meta($node->contest_id, 'is_cancellation_approved'); //just in case its run twice in testing
        }
    }

    public static function throw_helper(&$log,$message,$code = 0) {
        if (is_array($log)) {$log[] = 'ERROR: ' . $message;}
        throw new RuntimeException($message,$code);
    }

    /**
     * Gets the total count for pagination
     * @return int
     */
    public static function count_requests() {
        $ret = 0;
        return $ret;
    }


    public static function get_valid_sort_direction($what='') {
        $allowed_order = [
            'asc','desc'
        ];

        $order = 'desc';
        // If orderby is set, use this as the sort column
        if (!empty($what)) {
            $order_maybe = strtolower($what);
            if (array_key_exists($order_maybe,$allowed_order) ) {
                $order = $order_maybe;
            }
        }
        return $order;
    }
    /**
     * @param int|null $cancel_id , if set will only return this row
     * @param int $offset , if null is ignored
     * @param int $limit , if null is ignored
     * @param string $order , must be one of the property names in this object
     * @param string $order_by , if null then orders by id
     *
     * @return FreelinguistContestCancelNode[]
     *
     * code-notes now can generate user links for customers if they are the poster
     */
    public static function list_data($cancel_id,$offset, $limit,$order,$order_by)
    {
        global $wpdb;
        $last_few_limit = '';
        if ( $limit && $offset ) {
            $last_few_int   = intval( $limit );
            $offset_int     = intval( $offset );
            $last_few_limit = " LIMIT $offset_int, $last_few_int";
        } elseif ( $limit ) {
            $last_few_int   = intval( $limit );
            $last_few_limit = " LIMIT  $last_few_int";
        }
        $order = static::get_valid_sort_direction($order);

        $wheres = [];
        $cancel_id = (int)$cancel_id;
        if ($cancel_id) {
            $wheres[] = "(r.id = $cancel_id )";
        }
        $where_clause = "";
        if (!empty($wheres)) {
            $where_clause = 'AND ' . implode(' AND ',$wheres);
        }



        $template = new FreelinguistContestCancelNode();
        $public_fields = array_keys(get_object_vars($template));
        $use_order_by = 'cancel_id';
        if (array_key_exists(strtolower($order_by),$public_fields)) {
            $use_order_by = $order_by;
        }
        $res = $wpdb->get_results(/** @lang text */
            "
        SELECT

          r.id                                   as cancel_id,
          r.contest_id                           as contest_id,
          title_info.meta_value                  as contest_title,
          r.contest_id                           as contest_url,
          customer.user_email                    as customer_email,
          customer.id                            as customer_id,
          customer.id                            as customer_url,
          customer.user_nicename                 as customer_nice_name,
          days_extended                          as days_extended,
          is_approved                            as has_been_approved,
          UNIX_TIMESTAMP(opened_when)            as opened_ts,
          UNIX_TIMESTAMP(original_deadline_when) as original_deadline_ts,
          percentage_partial                     as percentage,
          admin.user_email                       as posted_by_email,
          admin_wp_user_id                       as posted_by_id,
          admin.ID                               as posted_by_url,
          admin.user_nicename                    as posted_nice_name,
          meta_caps.meta_value                   as posted_serialized_caps ,  
          UNIX_TIMESTAMP(processed_when)         as processed_ts,
          original_budget                        as original_budget,
          modified_id_info.meta_value            as modified_id
        
        
        FROM {$wpdb->prefix}contest_insurance_refund r
          LEFT JOIN {$wpdb->prefix}users as admin ON admin.ID = r.admin_wp_user_id
          LEFT JOIN {$wpdb->prefix}usermeta as meta_caps ON meta_caps.user_id = admin.ID AND meta_caps.meta_key = 'wp_capabilities'
          LEFT JOIN {$wpdb->prefix}posts as contest ON contest.ID = r.contest_id
          LEFT JOIN {$wpdb->prefix}postmeta as title_info ON title_info.post_id = contest.id AND title_info.meta_key = 'project_title'
          LEFT JOIN {$wpdb->prefix}postmeta as modified_id_info ON modified_id_info.post_id = contest.id AND modified_id_info.meta_key = 'modified_id'
          LEFT JOIN {$wpdb->prefix}users as customer ON customer.ID = contest.post_author
        WHERE 1 $where_clause
        ORDER BY $use_order_by $order
        $last_few_limit;
        ",OBJECT
        );

        if ($wpdb->last_error) {
            throw new RuntimeException("Error getting data from contest_insurance_refund row: " . $wpdb->last_error);
        }

        if ($res === false) {
            throw new RuntimeException("Unknown error getting data for the contest_insurance_refund row ");
        }
        $data = [];

        $languge_verb = '';
        $lang = FLInput::get('lang','en');
        if ($lang) {
            if ($lang === 'all') { $lang = 'en';}
            $languge_verb = 'lang='.$lang . '&';
        }

        foreach ($res as $row) {
            $node = new FreelinguistContestCancelNode();
            foreach ($row as $key => $value) {
                if (property_exists($node,$key)) {
                    $node->$key = $value;
                }
            }
            //get the post and user links
            $node->contest_url = get_permalink($node->contest_id);

            $posted_caps_serialized = $row->posted_serialized_caps;
            $posted_caps = maybe_unserialize($posted_caps_serialized);

            //code-notes added in the link for a poster to be a customer, using WP capabilities
            $b_is_posted_admin = true;
            if ($posted_caps && is_array($posted_caps)) {
                if (in_array('customer',$posted_caps) || in_array('translator',$posted_caps)) {
                    $b_is_posted_admin = false;
                }
            }
            if ($b_is_posted_admin) {
                $node->posted_by_url =  "mailto:". $node->posted_by_email; //can't get to admin profiles yet, so email
            } else {
                $node->posted_by_url =   site_url()."/user-account/?$languge_verb".'profile_type=translator&user='.$row->posted_nice_name;
            }

            $node->customer_url =  site_url()."/user-account/?$languge_verb".'profile_type=translator&user='.$row->customer_nice_name;
            if (! is_null($node->has_been_approved) ) {
                $node->has_been_approved = intval($node->has_been_approved);
            }

            //$href = site_url().'/user-account/?lang=en&user='.$user_info->user_nicename;

            if ($node->percentage !== null) {
                $node->percentage = (int) $node->percentage;
            }
            if ($node->days_extended !== null) {
                $node->days_extended = (int) $node->days_extended;
            }

            if ($node->original_budget !== null) {
                $node->original_budget = (float)$node->original_budget;
            }

            $data[] = $node;

        }
        return $data;

    }

    public static function safely_close_potential_requests($contest_id) {
        try {
            $log = [];
            FreelinguistContestCancellation::decide_cancellation($contest_id,false,null,null,$log);
        } catch( RuntimeException $r) {
            //do nothing if there is an error, which means here that either the request does not exist, is invalid, or already acted on
        }
    }


    /**
     * @param int $contest_id
     * @param string[] $log , OUT REF not reset
     * @return bool , returns true if this is a valid contest
     * //code-notes Adds a public way to check if a valid contest
     */
    public static function is_a_valid_contest($contest_id,&$log=[])
    {
        $job_id = intval($contest_id);
        $job_type = get_post_meta($job_id, 'fl_job_type', true);
        if ($job_type !== 'contest') {
            $log[] = "Not a contest. is a ". $job_type;
            return false;
        } else {
            $log[] = "Is a contest.". $job_type;
            return true;
        }
    }

    /**
     * code-notes Made function to determine if a contest can be undone. Checks time limit and if any freelancers claimed award
     * @param integer $cancel_id
     * @param array $log REF
     * @return bool
     *
     * cannot undo if no decision made
     * cannot undo if undo_cancel_contest_limit_hours or default is after decision made
     * cannot undo if any freelancers claimed prize money
     *
     * otherwise can undo and will return true
     */
    public static function can_undo_contest($cancel_id, &$log) {
        $nodes = static::list_data($cancel_id,null,null,null,null);
        if (empty($nodes)) {
            $log[] = "This is not a contest id ! $cancel_id";
            return false;
        }
        $hours_after = (float)get_option('undo_cancel_contest_limit_hours',static::DEFAULT_UNDO_CANCEL_CONTEST_LIMIT_HOURS);
        $log[] = "hours_after = $hours_after";
        $node = $nodes[0];
        $log[] = $node;
        $ts_decision_made = $node->processed_ts;
        $ts_deadline = $ts_decision_made + ($hours_after * 60 * 60);
        $log[] = "ts_decision_made $ts_decision_made";
        $log[] = "ts_deadline $ts_deadline";
        $ts_time_now = time();
        if ($ts_time_now > $ts_deadline) {
            $log[] = "after deadline, so cannot undo";
            return false;
        }
        static::get_claim_info_for_contest($node->contest_id,$log,$b_is_past_award,$count_proposals,
            $customer_balance,$total_self_awarded,
            $self_awarded_people,$award_period_ends);
        if ($total_self_awarded > 0) {
            $log[] = "Self awards already made of $total_self_awarded";
            return false;
        }
        return true;
    }

    /**
     * @param integer $contest_id
     * @param array $log REF
     * @param int $customer_balance OUT REF
     * @param bool $b_show_js_console
     * @return bool
     */
    public static function is_contest_and_negative_wallet($contest_id,&$log,&$customer_balance,$b_show_js_console=false) {
        $is_contest = static::is_a_valid_contest($contest_id,$log);
        if (!$is_contest) {return false;}
        $author = get_post_field( 'post_author', $contest_id );
        $is_wallet_negative = false;
        $customer_balance = get_user_meta($author,'total_user_balance',true);
        if (($customer_balance === '') || ($customer_balance === false)) {
            $is_wallet_negative = true;
            will_log_in_wp_log_and_js_console(
                "total_user_balance is missing from $author, hiding job $contest_id ",
                $b_show_js_console);
        }
        $customer_balance = floatval($customer_balance);
        if ($customer_balance < 0) {
            $is_wallet_negative = true;
        }
        return $is_wallet_negative;
    }

    /**
     * @param int $contest_id
     * @param string[] $log , OUT REF not reset
     * @param int $award_period_ends_at_ts OUT REF
     * @return bool , returns true if past award grace period for a contest
     * //code-notes Adds a public way to check when the award time ends
     */
    public static function is_after_award_date($contest_id,&$log,&$award_period_ends_at_ts)
    {
        //get the award wait duration, make sure we can do this now
        $award_duration_in_seconds = (float)get_option('award_duration_hours', 24) * 60 * 60;
        static::is_after_deadline_date($contest_id,$log,$contest_end_ts,$diff_in_seconds);
        $log[] = "contest ends at $contest_end_ts";
        $award_period_ends_at_ts = $contest_end_ts + $award_duration_in_seconds;
        $log[] = "award period ends at $award_period_ends_at_ts";
        $now = time();
        $log[] = "current time is  $now";
        if ($now < $award_period_ends_at_ts) {
            $log[] = "The award period is still open";
            return false;
        } else {
            $log[] = "Its after the award time";
            return true;
        }
    }

    /**
     * @param int $contest_id
     * @param string[] $log , OUT REF not reset
     * @param int $contest_end_ts OUT REF
     * @param int $diff_in_seconds OUT REF
     * @return bool , returns true if past deadline period for a contest
     * //code-notes Adds a public way to check when deadline is
     */
    public static function is_after_deadline_date($contest_id,&$log,&$contest_end_ts,&$diff_in_seconds)
    {
        global $wpdb;
        if (!isset($log) || empty($log)) {$log = [];}
        $log[] = "doing is after deadline date calcs";
        $ending_date = get_post_meta($contest_id, 'job_standard_delivery_date', true);
        if (!$ending_date) {
            self::throw_helper($log, "Cannot find an ending time for the contest");
        }
        $ending_date_time_string = trim($ending_date). ' 23:59:59';
        //code-notes now figuring out the ending time based on the contest ending date in the db timezone
        $time_sql = "SELECT IF(STR_TO_DATE('$ending_date_time_string','%Y-%m-%d %H:%i:%s') > NOW(),1,0) as time_check,
                    UNIX_TIMESTAMP(STR_TO_DATE('$ending_date_time_string','%Y-%m-%d %H:%i:%s')) as ending_ts,
                    UNIX_TIMESTAMP(NOW()) as now_ts;";
        $log[] = "Sql for figuring out if end :\n $time_sql";
        $time_results = $wpdb->get_results($time_sql);
        try {
            will_throw_on_wpdb_error($wpdb);
        } catch (Exception $e) {
            self::throw_helper($log, "Sql issue in selection for time compare\n ". $e->getMessage());
        }

        if (empty($time_results)) {
            self::throw_helper($log, "Cannot get selection for time compare");
        }

        $n_is_time_before = intval($time_results[0]->time_check);
        $contest_end_ts = intval($time_results[0]->ending_ts);
        $now_ts = intval($time_results[0]->now_ts);
        $diff_in_seconds = $contest_end_ts - $now_ts ;
        $log[] = "Ending date time string is $ending_date_time_string";
        $log[] = "contest end ts  $contest_end_ts";
        $log[] = "now ts  $now_ts";
        $log[] = "php now ts -->". time();
        $log[] = "end flag is  $n_is_time_before";
        $log[] = "diff in seconds is  $diff_in_seconds";


        if ($n_is_time_before) {
            $log[] = "Contest is NOT ended";
            $log[] = "Contest will end in ". $diff_in_seconds/60/60 ." hours";
            return false;
        } else {
            $log[] = "Contest is ended";
            $log[] = "Contest ended ". -$diff_in_seconds/60/60 ." hours ago";
            return true;
        }
    }

    /**
     * @param int $contest_id
     * @param int $freelancer_id
     * @param string[] $log OUT REF
     *
     * @return float (the amount awarded) or will throw


     * //code-notes method that an ajax call can use to pay out
     */
    public static function claim_prize_after_award_date($contest_id, $freelancer_id, &$log=[]) {
        global $wpdb;
        if (!is_array($log)) {$log=[];}
        static::can_claim_award_be_given($contest_id,$freelancer_id,$log,$count_proposals,
            $count_freelancers_proposals,$customer_balance,$previously_awarded,$all_awarded_array,$proposal_ids_to_use);

        if ($count_freelancers_proposals === 0) {return 0;}
        //if got there, this freelancer has not claimed prize for this contest, has at least one submitted entry, and the customer has a positive balance
        $estimated_budgets = floatval(get_post_meta($contest_id,'estimated_budgets',true));
        $log[] = "Estimated budgets: $estimated_budgets";
        //Total Prize * # of Proposals by this Freelancer / Total # of submitted
        $value_of_award_base = round($estimated_budgets /floatval($count_proposals),2);
        $log[] = "value_of_award_base: $value_of_award_base";

        $linguist_referral_fee = floatval(get_option('linguist_referral_fee',15.0)) ;
        $log[] = "linguist_referral_fee: $linguist_referral_fee";
        $linguist_referral_flex_fee = floatval(get_option('linguist_flex_referral_fee',15.0))/100;
        $log[] = "linguist_referral_flex_fee: $linguist_referral_flex_fee";

        $total_amount_awarded_before_fees = $count_freelancers_proposals * $value_of_award_base;
        $log[] = "total_amount_awarded_before_fees: $total_amount_awarded_before_fees";
        $value_of_award = $total_amount_awarded_before_fees - $linguist_referral_fee - ($linguist_referral_flex_fee*$total_amount_awarded_before_fees );

        $log[] = "value_of_award: $value_of_award";
        $value_of_award = max($value_of_award,0);
        $log[] = "value_of_award after min: $value_of_award";
        $linguMoney = floatval(get_user_meta( $freelancer_id, 'total_user_balance', true ));
        $log[] = "freelancer balance: $linguMoney";
        $addTolingu = $linguMoney+$value_of_award;
        $log[] = "new freelancer balance: $addTolingu";
        update_user_meta( $freelancer_id, 'total_user_balance', amount_format( $addTolingu ) );

        $earnings_per_proposal = round($value_of_award/$count_freelancers_proposals,2);
        foreach ($proposal_ids_to_use as $a_proposal_id) {
            $new_txn_id = fl_transaction_insert( $earnings_per_proposal, 'done', 'claim_award', $freelancer_id, NULL,
                "Earnings from competition for proposal $a_proposal_id",
                'wallet', '', $contest_id, NULL,NULL );

            $sql = "UPDATE wp_fl_transaction set proposal_id = $a_proposal_id  WHERE id = $new_txn_id";
            $wpdb->query($sql);
            will_throw_on_wpdb_error($wpdb,'setting proposal id to the new trx id of '.$new_txn_id);
        }


        //mark awarded
        $all_awarded_array[$contest_id] = $value_of_award;
        update_user_meta($freelancer_id,'is_prize_money_claimed',$all_awarded_array);

        delete_post_meta($contest_id, 'is_guaranted'); //code-notes , remove guaranteed flag after paying out
        return $value_of_award;
    }

    /**
     * @param int $contest_id
     * @param string[] $log OUT REF
     * @param bool $b_is_past_award_time OUT REF
     * @param int $count_proposals OUT REF
     * @param float $customer_balance OUT REF
     * @param float $total_self_awarded_amount OUT REF
     * @param int $count_self_awarded_people OUT REF
     * @param int $award_period_ends_at_ts OUT REF
     *
     * @return bool IF true, then there is some relevant information here
     * code-notes public method for getting all claim info for a contest
     */
    public static function  get_claim_info_for_contest($contest_id, &$log,
                                                       &$b_is_past_award_time, &$count_proposals,
                                                       &$customer_balance, &$total_self_awarded_amount,
                                                       &$count_self_awarded_people,
                                                        &$award_period_ends_at_ts)
    {
        global $wpdb;
        if (!is_array($log)) {$log=[];}
        $count_proposals = 0;
        $customer_balance = 0.0;
        $total_self_awarded_amount = 0.0;
        $count_self_awarded_people = 0;

        $contest_id = (int) $contest_id;
        $b_is_contest = static::is_a_valid_contest($contest_id,$log);
        if (!$b_is_contest) { static::throw_helper($log,"Cannot claim award prize. This is not a contest");}

        $b_is_past_award_time = static::is_after_award_date($contest_id,$log,$award_period_ends_at_ts);
        if (!$b_is_past_award_time) {
            $log[] = "Its not past the award time";
            return false;
        }

        //check to see if any proposals have been awarded
        $awarded_proposals_string = trim(get_post_meta($contest_id,'contest_awardedProposalPrizes',true));
        $log[] = "awarded proposals string: ". $awarded_proposals_string;
        $awarded_proposals_raw_array = explode(',',$awarded_proposals_string);
        $contest_awarded_proposals = [];
        foreach($awarded_proposals_raw_array as $int_with_space) {
            $maybe_int = trim($int_with_space);
            if ($maybe_int) {
                $contest_awarded_proposals[] = (int)$maybe_int;
            }
        }
        if (count($contest_awarded_proposals)> 0) {
            $log[] = "There are awarded proposals";
            return false;
        }


        $sql =
            "
            SELECT id,by_user FROM wp_proposals WHERE post_id = $contest_id
        ";
        $log[] = $sql;
        //get all proposals for this contest, the contest will be the post_id in the proposals table
        $res = $wpdb->get_results($sql,OBJECT);

        if ($wpdb->last_error) {
            static::throw_helper($log,"Error getting data from proposals: " . $wpdb->last_error);
        }

        if ($res === false) {
            static::throw_helper($log,"Unknown error getting data from proposals ");
        }

        if (empty($res)) {
            $log[] = "No proposals submitted ";
            return false;
        }


        $rem_users = [];
        foreach($res as $row) {
            $count_proposals++;
            $freelancer_id = (int)$row->by_user;

            if (array_key_exists($freelancer_id,$rem_users) || empty($freelancer_id)) {
                continue;
            }

            $prize_money_claimed_array =  get_user_meta($freelancer_id,'is_prize_money_claimed',true);
            if (empty($prize_money_claimed_array)) {continue;}
            if (!is_array($prize_money_claimed_array)) {
                static::throw_helper($log,"is_prize_money_claimed is not an array");
            }

            if (array_key_exists($contest_id,$prize_money_claimed_array)) {
                $rem_users[$freelancer_id] = (float)$prize_money_claimed_array[$contest_id];
                $total_self_awarded_amount += (float)$prize_money_claimed_array[$contest_id] ;
                $count_self_awarded_people ++;
            }

        }


        //check user wallet for a positive balance
        $customer_id =  get_post_field( 'post_author', $contest_id );
        $customer_balance = get_user_meta($customer_id,'total_user_balance',true);
        if (($customer_balance === '') || ($customer_balance === false)) {
            static::throw_helper($log,"Cannot claim award prize. Customer [$customer_id] does not have a balance entry ");
        }
        $customer_balance = floatval($customer_balance);
        return true;

    }

    /**
     * @param int $contest_id
     * @param int $freelancer_id
     * @param string[] $log OUT REF
     * @param int $count_proposals OUT REF  the total proposals submitted to this contest
     * @param int $count_freelancers_proposals OUT_REF the total number of proposals this user submitted
     * @param int $customer_balance OUT REF  the balance of the contest owner
     * @param int $previously_awarded OUT REF if >= 0 then is the amount awarded before
     * @param array  $all_awarded_array OUTREF array of key post_id, value is float amount awarded
     * @param int[] $proposals_ids_to_award OUTREF
     *
     * Only is seen when:
     *  A) the contest is after the award period
     *  B) the freelancer (current user) has submitted at least one proposal
     *  C) the contest has not awarded anything
     *  D) the freelancer has not claimed before from this competition
     *  E) the wallet balance of the contest owner is not negative
     */
    public static function can_claim_award_be_given($contest_id, $freelancer_id, &$log,
                                               &$count_proposals,&$count_freelancers_proposals,
                                                &$customer_balance, &$previously_awarded,&$all_awarded_array,
                                                &$proposals_ids_to_award=[]) {
        global $wpdb;
        if (!is_array($log)) {$log=[];}
        $proposals_ids_to_award = [];
        $previously_awarded = -1;
        $freelancer_id = (int) $freelancer_id;
        if (!$freelancer_id) {
            static::throw_helper($log,"Cannot claim award prize.User ID is empty");
        }
        $contest_id = (int) $contest_id;
        $b_is_contest = static::is_a_valid_contest($contest_id,$log);
        if (!$b_is_contest) { static::throw_helper($log,"Cannot claim award prize. This is not a contest");}

        $b_is_past_award_time = static::is_after_award_date($contest_id,$log,$award_period_ends_at_ts);
        if (!$b_is_past_award_time) { static::throw_helper($log,"Cannot claim award prize. Still in award period");}

        //check to see if any proposals have been awarded
        $awarded_proposals_string = trim(get_post_meta($contest_id,'contest_awardedProposalPrizes',true));
        $awarded_proposals_raw_array = explode(',',$awarded_proposals_string);
        $contest_awarded_proposals = [];
        foreach($awarded_proposals_raw_array as $int_with_space) {
            $maybe_int = trim($int_with_space);
            if ($maybe_int) {
                $contest_awarded_proposals[] = (int)$maybe_int;
            }
        }
        if (count($contest_awarded_proposals) > 0) {
            static::throw_helper($log,"Cannot claim award prize. There are ". count($contest_awarded_proposals). " proposals that were awarded");
        }
        //has this user already been awarded here?
        $all_awarded_array = get_user_meta($freelancer_id,'is_prize_money_claimed',true);
        $log[] = "already awardeded meta below:";
        $log[] = $all_awarded_array;
        // freelancer can ask for awards for different competitions
        if (empty($all_awarded_array)) {
            $all_awarded_array = [];
        }

        if (!is_array($all_awarded_array)) {
            static::throw_helper($log,"is_prize_money_claimed is not an array");
        }

        if (array_key_exists($contest_id,$all_awarded_array)) {
            $previously_awarded = $all_awarded_array[$contest_id];
            static::throw_helper($log,"Award Claimed Already");
        }

        $sql =
            "
            SELECT id,by_user FROM wp_proposals WHERE post_id = $contest_id
        ";
        $log[] = $sql;
        //get all proposals for this contest, the contest will be the post_id in the proposals table
        $res = $wpdb->get_results($sql,OBJECT);
        $log[] = $res;

        if ($wpdb->last_error) {
            static::throw_helper($log,"Error getting data from proposals: " . $wpdb->last_error);
        }

        if ($res === false) {
            static::throw_helper($log,"Unknown error getting data from proposals ");
        }

        if (empty($res)) {
            static::throw_helper($log,"Cannot claim award prize. No proposals submitted ");
        }

        $count_proposals = 0;
        $count_freelancers_proposals = 0;
        foreach($res as $row) {
            $count_proposals++;
            if (intval($row->by_user) === $freelancer_id) { $count_freelancers_proposals++;}
        }
        $log[] = "total counted proposals is : $count_proposals";
        $log[] = "total proposals for this freelancer is : $count_freelancers_proposals";

        if (empty($count_freelancers_proposals)) {
            static::throw_helper($log,"Cannot claim award prize. Freelancer [$freelancer_id] did not submit any proposals ");
        }

        //check user wallet for a positive balance
        $customer_id =  get_post_field( 'post_author', $contest_id );
        $customer_balance = get_user_meta($customer_id,'total_user_balance',true);
        if (($customer_balance === '') || ($customer_balance === false)) {
            static::throw_helper($log,"Cannot claim award prize. Customer [$customer_id] does not have a balance entry ");
        }
        $customer_balance = floatval($customer_balance);
        if ($customer_balance < 0) {
            static::throw_helper($log,"Cannot claim award prize. Customer [$customer_id] has a negative ($customer_balance) balance entry ");
        }
        $log[] = "customer_balance : $customer_balance";
    }

    /**
     * code-notes Made a central place to calculate what to say about the deadlines
     * @param int $contest_id
     * @param string[] $log
     * @param string $deadline_string OUT REF
     * @param string $award_string OUT REF
     */
    public static function make_words_about_contest_deadlines($contest_id,&$log,&$deadline_string,&$award_string) {
        //code-notes putting in words about when the contest deadline and award period ends
        FreelinguistContestCancellation::is_after_deadline_date($contest_id,$log,$contest_deadline_ts,$diff_in_seconds);
        FreelinguistContestCancellation::is_after_award_date($contest_id,$log,$contest_award_ts);
        $today_ts = time();

        $seconds_for_deadline_difference = $contest_deadline_ts - $today_ts;
        $days_for_deadline = floor($seconds_for_deadline_difference / 86400);
        $day_word = 'days';
        if (abs($days_for_deadline) === 1) { $day_word = 'day';}
        $deadline_string = "Proposal submission: $days_for_deadline $day_word left.";
        if ($days_for_deadline === (float)0) {
            $hours_for_deadline = floor($seconds_for_deadline_difference/3600);
            $hour_word = 'hours';
            if (abs($hours_for_deadline) === 1) { $hour_word = 'hour';}
            $deadline_string = "Proposal submission: $hours_for_deadline $hour_word left.";
        }

        $seconds_for_award_difference = $contest_award_ts - $today_ts;
        $days_for_award = floor($seconds_for_award_difference / 86400);
        $day_word = 'days';
        if (abs($days_for_award) === 1) { $day_word = 'day';}
        $award_string = "Award selection: $days_for_award $day_word left.";

        if ($days_for_award === (float)0) {
            $hours_for_award = floor($seconds_for_award_difference/3600);
            $hour_word = 'hours';
            if (abs($hours_for_award) === 1) { $hour_word = 'hour';}
            $award_string = "Award selection: $hours_for_award $hour_word left.";
        }
    }

}

new FreelinguistContestCancellation();
