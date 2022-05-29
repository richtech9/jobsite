<?php

/**
 * This is the cron task which clears out the older unit templates and makes new ones
 * Most of the work is done by @see FreelinguistUnitGenerator
 *
 * Class FreelinguistCronTopUnitsGenerate
 * @uses FreelinguistUnitGenerator::generate_units()
 * @uses FreelinguistUnitGenerator::get_current_top_tags()
 * @uses FreelinguistUnitGenerator::clear_out_templates()
 */
class FreelinguistCronTopUnitsGenerate extends FreelinguistCronBase
{
    const OPTION_NAME = 'freelinguist_cron_top_units';
    const ACTION_NAME = 'freelinguist_cron_top_units';
    const STOP_ACTION_NAME = 'freelinguist_cron_top_units_command';

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
                        will_send_to_error_log(" Beginning To Recalcuate Top Tags, because this is Step $current_page",
                            FREELINGUIST_WILL_LOG_NO_VALUE, $debug_string_command, false);


                    FreelinguistUnitGenerator::generate_units($log,[],[],false);


                    $current_top_tags = FreelinguistUnitGenerator::get_current_top_tags();

                    $to_log = static::get_log_prefix() . ' ' .
                        will_send_to_error_log(" Finished Regenerating Units
                     \nThe current top tags are",
                            $current_top_tags, $debug_string_command, false);

                    $cleaner_to_log = str_replace("Array\n(", '', $to_log);
                    $cleaner_to_log = str_replace("\n)", "\n", $cleaner_to_log);
                    $log[] = $cleaner_to_log;

                    static::stop(); //stopping at step 0
                    $next_page = $current_page+1;
                    static::set_next_loop($next_page);

                    return [
                            'success' => true,
                            'message' => static::get_log_prefix() . " Did Step " . $current_page,
                             'code' => 201
                    ];

                    break;


                default:
                    throw new InvalidArgumentException("Step Not Recognized");
            }

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
                'code' => $e->getCode()];
        } finally {
            static::set_log($log);
        }
    }
}


FreelinguistCronTopUnitsGenerate::set_up_hook();