<?php


class FreelinguistCronTopUnitsClear extends FreelinguistCronBase
{
    const OPTION_NAME = 'freelinguist_cron_top_units_clear';
    const ACTION_NAME = 'freelinguist_cron_top_units_clear';
    const STOP_ACTION_NAME = 'freelinguist_cron_top_units_clear_command';

    public static function main($extra_command)
    {
        $current_page = -999;
        will_do_nothing($extra_command);
        $debug_string_command = static::get_debug_string_command();
        $log = static::get_log_value();
        if (empty($log)) {
            $log = [];
        }
        if (!static::can_do_step()) {
            $log[] = static::get_log_prefix() . ' ' .
                will_send_to_error_log(" Was told to stop . Exiting with nothing done, and no follow up action",
                    FREELINGUIST_WILL_LOG_NO_VALUE, $debug_string_command);

            static::set_log($log);
            return ['success' => false, 'message' => "No permission to run", 'code' => 401];
        }


        try {

            $log = self::get_log();
            $log[] = static::get_log_prefix() . ' ' . will_send_to_error_log("Starting partial cron job for top units!",
                    '', $debug_string_command);

            $current_page = static::get_loop(); //will throw if not set to a number


            switch ($current_page) {
                case 0:
                    $log[] = static::get_log_prefix() . ' ' .
                        will_send_to_error_log(
                            " Beginning Step $current_page to clear Templates",
                            $current_page, $debug_string_command, true);

                    FreelinguistUnitGenerator::clear_out_templates($log);

                    $log[] = static::get_log_prefix() . ' ' .
                        will_send_to_error_log(
                            " Finished Clearing Templates",
                            $current_page, $debug_string_command, true);


                    static::stop();
                    $next_page = $current_page+1;
                    static::set_next_loop($next_page);
                    return [
                        'success' => true,
                        'message' => static::get_log_prefix() . " Did step " . $current_page,
                        'code' => 201
                    ];

                    break;


                default:
                    throw new InvalidArgumentException("Step Not Recognized");
            } //end switch

            /** @noinspection PhpUnreachableStatementInspection */
            throw new Exception("This version of phpstorm is dumb with some inspections.
            Added this to quiet inspection errors Will never get here");
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
            static::set_log($log);
        }
    }
}


FreelinguistCronTopUnitsClear::set_up_hook();