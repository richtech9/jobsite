<?php
/*
 * current-php-code 2021-Jan-15
 * input-sanitized :
 * current-wp-template:  admin-screen  for reminding
 */
function theme_reminder_email(){
    ?>
    <div class="freelng-set-panle">
        <div class="wrap">
            <h3>Set Reminder email</h3>
            <?php
            if(isset($_POST['save_reminder_options'])){
                $set_option =  $_POST;
                $reminder_x_days_before_the_deadline = $set_option['reminder_x_days_before_the_deadline'];
                $reminder_y_days_before_the_deadline = $set_option['reminder_y_days_before_the_deadline'];
                if (!filter_var($reminder_x_days_before_the_deadline, FILTER_VALIDATE_INT) === false) {
                }else{
                    $reminder_x_days_before_the_deadline = 0;
                }
                if (!filter_var($reminder_y_days_before_the_deadline, FILTER_VALIDATE_INT) === false) {
                }else{
                    $reminder_y_days_before_the_deadline = 0;
                }

                update_option('reminder_x_days_before_the_deadline', $reminder_x_days_before_the_deadline);
                update_option('reminder_y_days_before_the_deadline', $reminder_y_days_before_the_deadline);
                echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                <p><strong>Added successfully.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

            }

            ?>
            <p class="description">
                System will send reminder emails to the freelancers who are working on a job.
            </p>
            <form id="setting_pannel" method="POST" action="<?php echo admin_url('admin.php?page=freelinguist-admin-reminders'); ?>&lang=en">
                <table class="form-table">
                    <tbody>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">X Days (unused)</th>
                        <td>
                            <input type="number" value="<?php echo get_option('reminder_x_days_before_the_deadline'); ?>"
                                   name="reminder_x_days_before_the_deadline" id="reminder_x_days_before_the_deadline"
                                   title="X Days"
                            >
                            <p class="description">
                                X days before the deadline.
                            </p>
                        </td>
                        <td>
                        </td>
                    </tr>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Y days (unused)</th>
                        <td>
                            <input type="number" value="<?php echo get_option('reminder_y_days_before_the_deadline'); ?>"
                                   name="reminder_y_days_before_the_deadline" id="reminder_y_days_before_the_deadline"
                                   title="Y Days"
                            >
                            <p class="description">
                                Y days before the deadline.
                            </p>
                        </td>
                        <td>
                        </td>
                    </tr>

                    <tr class="user-rich-editing-wrap">
                        <th scope="row"><input class="button button-primary button-large" type="submit" name="save_reminder_options" value="Update"></th>
                        <td></td>
                    </tr>
                    </tbody>
                </table>

            </form>

            <div style=" text-align: center; font-weight: bold; font-size: 300%; background-color: yellow">
                This Admin page is still being implemented
            </div>
        </div>
    </div>
    <?php
}