<?php


class FreelinguistCronRedDotActions extends FreelinguistCronBaseRepeat
{
    const OPTION_NAME = 'freelinguist_cron_red_dot_actions';
    const ACTION_NAME = 'freelinguist_cron_red_dot_actions';
    const ACTION_GROUP_NAME = 'freelinguist_repeating_tasks';
    const STOP_ACTION_NAME = 'freelinguist_cron_red_dot_actions_command';

    const MY_VERY_OWN_ARGS = ['red-dot-actions'];



    public static function main($extra_command)
    {
        $current_page = -999;
        will_do_nothing($extra_command); //its always the same command for this repeating task
        $log = static::get_log_value();
        if (empty($log)) {
            $log = [];
        }
        if (!static::can_do_step()) {
            $log[] = static::get_log_prefix() . ' ' .
                will_send_to_error_log(" Was told to stop . Exiting with nothing done, and no follow up action",
                    FREELINGUIST_WILL_LOG_NO_VALUE, FREELINGUIST_WILL_RET_STRING);

            static::set_log($log);
            return ['success' => false, 'message' => "No permission to run", 'code' => 401];
        }


        try {

            $log = self::get_log();

            $current_page = static::get_loop(); //will throw if not set to a number

            $log[] = static::get_log_prefix() . ' ' .
                will_send_to_error_log("$current_page Iteration : Starting to call the Red Dot Actions! ",
                    '', FREELINGUIST_WILL_RET_STRING);

            try {
               FLRedDotFutureActions::do_red_dot_actions($log);


                return [
                    'success' => true,
                    'message' => static::get_log_prefix() . " Ended Red Dot Actions in [interation $current_page]",
                    'code' => 200
                ];
            } catch (Exception $e) {
                $log[] = static::get_log_prefix() . ' ' . will_send_to_error_log(
                        "Error " . $e->getMessage() . " Error RD Actions [interation $current_page],Will try again ",
                        will_get_exception_string($e), FREELINGUIST_WILL_RET_STRING, false);
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
                    will_get_exception_string($e), FREELINGUIST_WILL_RET_STRING, false);

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
    
    public static function process_cron_controls() {
        //cancel_red_dots_cron,resume_red_dots_cron,yes_i_want_to_update_red_dots_timer,red_dots_interval_in_seconds

        if (isset($_POST['yes_i_want_to_update_red_dots_timer']) ) {

            $unit_time_interval_in_seconds = (int)$_POST['red_dots_interval_in_seconds'];
            try {
                static::set_timer_seconds($unit_time_interval_in_seconds);
                if ($unit_time_interval_in_seconds) {
                    static::stop();
                    static::run();
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p>
                            The timed repeat for red dot actions has been scheduled to run every
                            <b><?= $unit_time_interval_in_seconds?></b> Seconds
                        </p>
                    </div>
                    <?php
                } else {
                    try {
                        static::stop();
                    } catch (Exception $e) {
                        ?>

                        <div class="notice notice-success is-dismissible">
                            <p>
                                Error Stopping Cron Job for Red Dots :
                                <span class="cron-log-error"><?=$e->getMessage()?></span>
                            </p>
                        </div>
                        <?php
                    }


                }


            }   catch(Exception $e) {
                ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Error setting the refresh time for the red dot actions:
                        <span class="cron-log-error"><?=$e->getMessage()?></span>
                    </p>
                </div>
                <?php
            }
        }

        if (isset($_POST['cancel_red_dots_cron'])) {
            try {
                static::stop();

                $all_logs = static::get_log();
                $log_size = count($all_logs);
                $log_line = '';
                if ($log_size) {
                    $log_line = $all_logs[$log_size-1];
                }
                ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Cron Job for Red Dot Actions will not run until started again
                        <span class="cron-log-line"><?=$log_line?></span>
                    </p>
                </div>
                <?php

            } catch (Exception $e) {
                ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Error Stopping Repeat Cron Job for Red Dot Actions :
                        <span class="cron-log-error"><?=$e->getMessage()?></span>
                    </p>
                </div>
                <?php
            }



        }

        if (isset($_POST['resume_red_dots_cron'])) {
            try {
                static::resume();
                $all_logs = static::get_log();
                $log_size = count($all_logs);
                $log_line = '';
                if ($log_size) {
                    $log_line = $all_logs[$log_size - 1];
                }

                ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Cron Job For Red Dot Actions will now Resume
                        <span class="cron-log-line"><?= $log_line ?></span>
                    </p>
                </div>
                <?php
            } catch (Exception $e) {
                ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Error Stopping Repeat Cron Job For Red Dot Actions :
                        <span class="cron-log-error"><?=$e->getMessage()?></span>
                    </p>
                </div>
                <?php
            }
        }
    }
}


FreelinguistCronRedDotActions::set_up_hook();