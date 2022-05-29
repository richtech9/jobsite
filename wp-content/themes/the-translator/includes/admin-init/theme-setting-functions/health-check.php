<?php

function freelinguist_do_healthcheck_gui_logic() {
    /*
     * set_freelinguist_health_check_time : health_check_interval_in_seconds
      set_freelinguist_health_check_email : freelinguist_health_check_email
        : FreelinguistCronHealthCheckRepeat
       please_stop_health_checks
     */
    if (isset($_POST['set_freelinguist_health_check_time'])  ) {

        if (isset($_POST['health_check_interval_in_seconds']) ) {

            $interval_in_seconds = (int)$_POST['health_check_interval_in_seconds'];
            try {
                FreelinguistCronHealthCheckRepeat::set_timer_seconds($interval_in_seconds);
                if ($interval_in_seconds) {
                    FreelinguistCronHealthCheckRepeat::stop();
                    FreelinguistCronHealthCheckRepeat::run();
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p>
                            The Health Timer has been scheduled to run every
                            <b><?= $interval_in_seconds?></b> Seconds
                        </p>
                    </div>
                    <?php
                } else {
                    try {
                        FreelinguistCronHealthCheckRepeat::stop();
                    } catch (Exception $e) {
                        ?>

                        <div class="notice notice-success is-dismissible">
                            <p>
                                Error Stopping Health Checks :
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
                        Error setting the Health Check timer :
                        <span class="cron-log-error"><?=$e->getMessage()?></span>
                    </p>
                </div>
                <?php
            }
        }
    }



    if (isset($_POST['set_freelinguist_health_check_email'])) {
        $health_email = FLInput::get('freelinguist_health_check_email','',
            FLInput::YES_I_WANT_CONVESION,
            FLInput::YES_I_WANT_DB_ESCAPING,
            FLInput::NO_HTML_ENTITIES
        );
        update_option('freelinguist_health_check_email',$health_email);
    }



    if (isset($_POST['please_stop_health_checks'])) {
        try {
            FreelinguistCronHealthCheckRepeat::stop();

            $all_logs = FreelinguistCronHealthCheckRepeat::get_log();
            $log_size = count($all_logs);
            $log_line = '';
            if ($log_size) {
                $log_line = $all_logs[$log_size-1];
            }
            ?>

            <div class="notice notice-success is-dismissible">
                <p>
                    Health Check will not run until started again
                    <span class="cron-log-line"><?=$log_line?></span>
                </p>
            </div>
            <?php

        } catch (Exception $e) {
            ?>

            <div class="notice notice-success is-dismissible">
                <p>
                    Error Stopping Health Check :
                    <span class="cron-log-error"><?=$e->getMessage()?></span>
                </p>
            </div>
            <?php
        }



    }
} //end function
freelinguist_do_healthcheck_gui_logic(); //call function
?>
<div class="freelinguist-admin-patrol-flight">
    <form
        id="health_check_form"
        method="POST"
        enctype="multipart/form-data"
        action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=xmpp'); ?>&lang=en"
    >

        <span  class="bold-and-blocking large-text">
            Schedule Health Checks
        </span>

        <div class="freelinguist-health-check-settings">
            <table class="form-table">
                <tbody>
                <tr>
                    <td>
                        <?php
                        try {
                            $current_timer_setting = FreelinguistCronHealthCheckRepeat::get_timer_seconds();
                        } catch (Exception $e) {
                            $current_timer_setting = 0;
                        }
                        ?>
                        <select title="Health Check Timer" name="health_check_interval_in_seconds">
                            <option value="0" <?=
                            ($current_timer_setting === 0) ? 'selected': ''
                            ?> >Off</option>

                            <option value="120" <?=
                            ($current_timer_setting === 120) ? 'selected': ''
                            ?> >2 Minutes</option>

                            <option value="600" <?=
                            ($current_timer_setting === 600) ? 'selected': ''
                            ?> >10 Minutes</option>

                            <option value="900" <?=
                            ($current_timer_setting === 900) ? 'selected': ''
                            ?> >15 Minutes</option>


                            <?php
                            for ($i = 1; $i <= 48; $i++) {
                                $seconds = 30 * 60 * $i; //half hour increments
                                $hour_int = $i - 1;
                                $half_string = '';
                                if ($i % 2) {
                                    $half_string = ' And a Half ';
                                }
                                $word = 'Hours';
                                if ($hour_int === 1) {$word = 'Hour';}
                                $full_option_string = "$hour_int $half_string $word ";
                                ?>
                                <option value="<?= $seconds ?>"
                                    <?=
                                    ($current_timer_setting === $seconds) ? 'selected': ''
                                    ?>
                                >
                                    <?= $full_option_string ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                        <input
                            type="submit"
                            value="Update Health Check Time"
                            name="set_freelinguist_health_check_time"
                            class="button button-primary  freelinguist-fix-submit-width">
                    </td>
                    <td>
                        <input
                            type="submit"
                            value="Stop Health Checks"
                            name="please_stop_health_checks"
                            class="button button-primary cron-cancel freelinguist-fix-submit-width">
                    </td>
                </tr>



                <tr>
                    <td colspan="2">
                        <span class="bold-and-blocking enhanced-text">Last Logs</span><br>
                        <?php
                        $user_short_logs = FreelinguistCronHealthCheckRepeat::get_last_n_logs(3);
                        if (empty($user_short_logs)) {
                            $user_short_logs[] = "No logs";
                        }
                        foreach ($user_short_logs as $user_short_log) {
                            ?>
                            <span class="cron-log-line"><?= $user_short_log ?></span>
                            <?php
                        }

                        ?>
                    </td>
                </tr>


                <tr>
                    <td>
                        <span class="bold-and-blocking large-text">
                               Email address to send alerts about failed checks
                         </span>
                        <input
                            title="Email address for failed checks"
                            type="email"
                            value="<?= get_option('freelinguist_health_check_email','')?>"
                            name="freelinguist_health_check_email"
                            class="">
                    </td>
                    <td>
                        <input
                            type="submit"
                            value="Update Email Setting"
                            name="set_freelinguist_health_check_email"
                            class="button button-primary  freelinguist-fix-submit-width">
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </form> <!-- /#health_check_form-->

</div> <!-- /.freelinguist-admin-patrol-flight -->


