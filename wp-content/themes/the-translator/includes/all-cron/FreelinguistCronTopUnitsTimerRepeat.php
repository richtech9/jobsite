<?php


class FreelinguistCronTopUnitsTimerRepeat extends FreelinguistCronBaseRepeat
{
    const OPTION_NAME = 'freelinguist_cron_top_units_timer';
    const ACTION_NAME = 'freelinguist_cron_top_units_timer';
    const ACTION_GROUP_NAME = 'freelinguist_repeating_tasks';
    const STOP_ACTION_NAME = 'freelinguist_cron_top_units_timer_command';

    const MY_VERY_OWN_ARGS = ['unit-generation'];



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
                will_send_to_error_log(" Was told to stop . Exiting with nothing done, and no follow up action",
                    FREELINGUIST_WILL_LOG_NO_VALUE, $debug_string_command);

            static::set_log($log);
            return ['success' => false, 'message' => "No permission to run", 'code' => 401];
        }


        try {

            $log = self::get_log();

            $current_page = static::get_loop(); //will throw if not set to a number

            $log[] = static::get_log_prefix() . ' ' .
                will_send_to_error_log("$current_page Iteration : Starting to call the Unit Generation Task! ",
                    '', $debug_string_command);

            try {
                FreelinguistCronTopUnitsGenerate::run();

                $log[] = static::get_log_prefix() . ' ' .
                        will_send_to_error_log(" Finished Top Unit Generation in [interation $current_page]",
                        '', $debug_string_command);

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


FreelinguistCronTopUnitsTimerRepeat::set_up_hook();