<?php


class FreelinguistCronHealthCheckRepeat extends FreelinguistCronBaseRepeat
{
    const OPTION_NAME = 'freelinguist_cron_health_check';
    const ACTION_NAME = 'freelinguist_cron_health_check';
    const ACTION_GROUP_NAME = 'freelinguist_repeating_tasks';
    const STOP_ACTION_NAME = 'freelinguist_cron_health_check_command';

    const MY_VERY_OWN_ARGS = ['health-check'];


    /**
     * @param string[] $problems IN
     * @param string $email_used OUT REF
     * @return bool|string
     */
    protected static function email_problems($problems,&$email_used) {
        $email_used = get_option('freelinguist_health_check_email','');
        if (empty($email_used)) {return "no email set";}

        $word = "Problems";
        $number_problems = count($problems);
        if ($number_problems === 1) {$word = 'Problem';}
        $url = get_site_url();
        $subject = "$number_problems $word in $url";

        $current_time = static::get_log_time();

        $enumerated_problems_as_array = [];
        foreach ($problems as $problem) {
            $enumerated_problems_as_array[] = "<li>".$problem."</li>";
        }
        $enumerated_problems = implode("\n",$enumerated_problems_as_array);
        $body = <<<HERE
    At $current_time , Health Check found $number_problems $word at $url
    <br>
    <ul>
        $enumerated_problems
    </ul>
HERE;
        $from_email = 'no-reply@peerok.com';
        $headers = '';
        $headers    .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
        $headers    .= "From: $from_email";
        $b_mail_ok = wp_mail( $email_used, $subject, $body,$headers);
        if ($b_mail_ok) {
            $b_email_success =  true;
        } else {
            $b_email_success = "There was a problem sending email, check debug.log";
        }
        return $b_email_success;
    }

    public static function main($extra_command)
    {
        $current_page = -999;
        will_do_nothing($extra_command); //its always the same command for this repeating task
        $debug_string_command = static::get_debug_string_command();
        $log = static::get_log_value();
        if (empty($log)) {
            $log = [];
        }
        if (!static::can_do_step()) {
            $log[] = static::get_log_prefix() . ' ' .
                will_send_to_error_log(" Stopping. No more health checks",
                    FREELINGUIST_WILL_LOG_NO_VALUE, $debug_string_command);

            static::set_log($log);
            return ['success' => false, 'message' => "No permission to run", 'code' => 401];
        }


        try {

            $log = self::get_log();

            $current_page = static::get_loop(); //will throw if not set to a number

            $log[] = static::get_log_prefix() . ' ' .
                will_send_to_error_log("[interation $current_page] : Starting to call the health check method ",
                    '', $debug_string_command);

            try {
                FreeLinguistPreFlightCheck::do_not_do_admin_notices();
                FreeLinguistPreFlightCheck::log_notices();
                $perhaps_issues = FreeLinguistPreFlightCheck::run_checks();

                if (empty($perhaps_issues)) {
                    $log[] = static::get_log_prefix() . ' ' .
                        will_send_to_error_log(" No issues found this run [interation $current_page]",
                            '', $debug_string_command);
                } else {
                    $word = "Problems";
                    $number_problems = count($perhaps_issues);
                    if ($number_problems === 1) {$word = 'Problem';}



                    $email_status = static::email_problems($perhaps_issues,$email_used);
                    if ($email_status === true) {
                        $email_description = "Sent email to $email_used";
                    } else {
                        $email_description = "Issue sending email: ".$email_status;
                    }

                    will_send_to_error_log(" $number_problems $word. $email_description [interation $current_page]",
                        $perhaps_issues, $debug_string_command);

                    $log[] = static::get_log_prefix() . ' ' .
                        will_send_to_error_log(" $number_problems $word. $email_description [interation $current_page]",
                            $perhaps_issues, $debug_string_command);
                }


                return [
                    'success' => true,
                    'message' => static::get_log_prefix() . " Started Top Unit Generation in [interation $current_page]",
                    'code' => 200
                ];
            } catch (Exception $e) {
                $log[] = static::get_log_prefix() . ' ' . will_send_to_error_log(
                        "Error " . $e->getMessage() . " in starting Top Unit Generation in [interation $current_page],Will try again ",
                        will_get_exception_string($e), $debug_string_command, false);
                return [
                    'success' => false,
                    'message' => static::get_log_prefix() . " Error in Step $current_page : " . $e->getMessage(),
                    'code' => $e->getCode()
                ];
            }

        } catch (Exception $e) {
            static::stop();
            $log[] = static::get_log_prefix() . ' ' . will_send_to_error_log(
                    "Error " . $e->getMessage() . " in current step $current_page, not rescheduling action ",
                    will_get_exception_string($e), $debug_string_command, false);

            return [
                'success' => false,
                'message' => static::get_log_prefix() . " Error in Step $current_page : " . $e->getMessage(),
                'code' => $e->getCode()
            ];
        } finally {
            $next_page = $current_page+1;
            static::set_next_loop($next_page);
            static::set_log($log);
        }

        return [
            'success' => false,
            'message' => static::get_log_prefix() . " Never gets here $current_page ",
            'code' => 0
        ];
    }
}


FreelinguistCronHealthCheckRepeat::set_up_hook();