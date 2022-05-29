<?php

/*
    * current-php-code 2021-Jan-8
    * input-sanitized :
    * current-wp-template:  admin-screen  settings order
*/

function customer_theme_option_val()
{
    ?>
    <div class="freelng-set-panle">
    <div class="clear"></div>
    <div class="clear"></div>
    <div class="wrap">


        <?php
        if (isset($_REQUEST['typeis'])) {
            if ($_REQUEST['typeis'] == 'coupons') {
                $third_class = 'nav-tab nav-tab-active';
                $fourth_class = 'nav-tab';
                $fifth_class = 'nav-tab';
                $sixth_class = 'nav-tab';
            } elseif ($_REQUEST['typeis'] == 'mailchimpsetting') {

                $third_class = 'nav-tab';
                $fourth_class = 'nav-tab nav-tab-active';
                $fifth_class = 'nav-tab';
                $sixth_class = 'nav-tab';
            } elseif ($_REQUEST['typeis'] == 'others') {

                $third_class = 'nav-tab';
                $fourth_class = 'nav-tab';
                $fifth_class = 'nav-tab nav-tab-active';
                $sixth_class = 'nav-tab';
            } elseif ($_REQUEST['typeis'] == 'xmpp') {

                $third_class = 'nav-tab';
                $fourth_class = 'nav-tab';
                $fifth_class = 'nav-tab';
                $sixth_class = 'nav-tab nav-tab-active';
            } else {
                $third_class = 'nav-tab  ';
                $fourth_class = 'nav-tab';
                $fifth_class = 'nav-tab nav-tab-active';
                $sixth_class = 'nav-tab';
            }
        } else {


            $third_class = 'nav-tab  ';
            $fourth_class = 'nav-tab';
            $fifth_class = 'nav-tab nav-tab-active';
            $sixth_class = 'nav-tab';
        }
        ?>

        <a class="<?php echo $third_class; ?>"
           href="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=coupons'); ?>">Coupons Detail</a>
        <a class="<?php echo $fourth_class; ?>"
           href="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=mailchimpsetting'); ?>">Mailchimp
            Setting</a>
        <a class="<?php echo $sixth_class; ?>"
           href="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=xmpp'); ?>">Xmpp Setting</a>
        <a class="<?php echo $fifth_class; ?>"
           href="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>">Others</a>
        <div class="clear"></div>


        <?php
        if (isset($_POST['save_coupons'])) {
            global $wpdb;
            if (!isset($_REQUEST['coupon_id'])) {
                $wpdb->insert(
                    'wp_coupons',
                    array(
                        'coupon_code' => $_REQUEST['free_credit_coupon_code'],
                        'coupon_value' => $_REQUEST['free_credit_coupon_amount']
                    )
                );

            } else {
                $wpdb->update(
                    'wp_coupons',
                    array(
                        'coupon_code' => $_REQUEST['free_credit_coupon_code'],
                        'coupon_value' => $_REQUEST['free_credit_coupon_amount']
                    ),
                    array('id' => $_REQUEST['coupon_id'])
                );
            }

            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong>Settings saved.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }
        ?>

        <?php if ((isset($_REQUEST['typeis']) && ($_REQUEST['typeis'] == 'coupons'))) { ?>
            <script>
                function updateSlectedCouponsAdmin(valueis) {
                    var adminurl = "<?php echo admin_url('admin.php'); ?>";
                    window.location = adminurl + "?page=customer-theme-option-val&typeis=coupons&coupon_id=" + valueis;
                }
            </script>
        <?php
        global $wpdb;
        if (isset($_REQUEST['coupon_del'])) {
            $coupon_del = $_REQUEST['coupon_del'];
            $wpdb->delete('wp_coupons', array('id' => $coupon_del));
        }
        ?>
            <div class="nav-tab1 nav-tab-active1">
                <table class="form-table">
                    <tbody>
                    <tr>
                        <td colspan="6"><span class="bold-and-blocking larger-text">Coupon Detail</span></td>
                    </tr>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Free credit coupon List</th>
                        <td>
                            <select name="free_credit_coupons" onChange="updateSlectedCouponsAdmin(this.value);" title="Free Coupons">
                                <option value="">---- Select Coupon----</option>
                                <?php
                                global $wpdb;
                                $results = $wpdb->get_results("SELECT * FROM wp_coupons WHERE 1");
                                for ($i = 0; $i < count($results); $i++) {
                                    $coupon_code = $results[$i]->coupon_code;
                                    $coupon_id = $results[$i]->id;
                                    echo '<option value="' . $coupon_id . '">' . $coupon_code . '</option>';
                                }
                                ?>
                            </select>
                            <hr>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        <?php if (!isset($_REQUEST['coupon_id'])){ ?>
            <div class="nav-tab1 nav-tab-active1">
                <form id="setting_pannel" method="POST"
                      action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=coupons'); ?>&lang=en">
                    <table class="form-table">
                        <tbody>
                        <tr class="user-rich-editing-wrap">
                            <th scope="row">Add Free credit coupon</th>
                            <td>
                                <input type="text" placeholder="coupon code" name="free_credit_coupon_code" value="" title="Free Coupon Code">
                                <p class="description" id="">free_credit_coupon_code.</p>
                                <input type="number" placeholder="coupon Amount" step="any"
                                       name="free_credit_coupon_amount" value="">
                                <p class="description" id="">Coupon Amount.</p>
                                <hr>
                            </td>
                        </tr>
                        <tr class="user-rich-editing-wrap">
                            <th scope="row"><input type="submit" value="Add" class="button button-primary"
                                                   name="save_coupons"></th>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                </form>
                <?php } else { ?>
                    <?php
                    $coupon_row_id = $_REQUEST['coupon_id'];
                    $result_row = $wpdb->get_row("SELECT * FROM wp_coupons where id=$coupon_row_id");
                    $coupon_row_code = $result_row->coupon_code;
                    $coupon_row_value = $result_row->coupon_value;
                    ?>
                    <form id="setting_pannel" method="POST"
                          action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=coupons&coupon_id=' . $coupon_row_id); ?>&lang=en">
                        <table class="form-table">
                            <tbody>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">Update Free credit coupon</th>
                                <td>
                                    <input type="text" placeholder="coupon code" name="free_credit_coupon_code"
                                           value="<?php echo $coupon_row_code; ?>">
                                    <p class="description" id="">free_credit_coupon_code.</p>
                                    <input type="number" placeholder="coupon Amount" step="any"
                                           name="free_credit_coupon_amount" value="<?php echo $coupon_row_value; ?>">
                                    <p class="description" id="">Coupon Amount.</p>
                                    <hr>
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">
                                    <input type="submit" value="update" class="button button-primary"
                                           name="save_coupons">
                                </th>
                                <td>
                                    <a class="button1 button-primary1"
                                       href="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=coupons'); ?>">Add
                                        New</a>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <a class="button1 button-primary1"
                                       href="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=coupons&coupon_del=' . $coupon_row_id); ?>">Delete</a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                <?php } ?>
            </div>
        <?php } ?>
        <!-- END: Coupons-->
        <?php
        if (isset($_POST['save_w_9_form'])) {
            $w_9_form = media_handle_upload('w_9_form', 0);
            if (is_wp_error($w_9_form)) {
                $msg = "Error uploading file: " . $w_9_form->get_error_message();
            } else {
                $w_9_form_data = get_post($w_9_form);
                $path = explode('/', $w_9_form_data->guid);
                $file_name = $path[count($path) - 1];
                $file_path = date('Y/m') . '/' . $file_name;
                update_option('w_9_form_fileName', $file_name);
                update_option('w_9_form_filePath', $file_path);
                global $wpdb;
                $wpdb->delete('wp_posts', array('ID' => $w_9_form));
                $msg = "File upload successful!";
            }
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> ' . $msg . ' </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

        }
        if (isset($_POST['save_tax_form'])) {
            $tax_form = media_handle_upload('tax_form', 0);
            if (is_wp_error($tax_form)) {
                $msg = "Error uploading file: " . $tax_form->get_error_message();
            } else {
                $tax_form_data = get_post($tax_form);
                $path = explode('/', $tax_form_data->guid);
                $file_name = $path[count($path) - 1];
                $file_path = date('Y/m') . '/' . $file_name;
                update_option('tax_form_fileName', $file_name);
                global $wpdb;
                $wpdb->delete('wp_posts', array('ID' => $tax_form));
                update_option('tax_form_filePath', $file_path);
                $msg = "File upload successful!";
            }
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> ' . $msg . ' </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

        }
        if (isset($_POST['save_w_8ben_form'])) {
            $w_8ben_form = media_handle_upload('w_8ben_form', 0);
            if (is_wp_error($w_8ben_form)) {
                $msg = "Error uploading file: " . $w_8ben_form->get_error_message();
            } else {
                $w_8ben_form_data = get_post($w_8ben_form);
                $path = explode('/', $w_8ben_form_data->guid);
                $file_name = $path[count($path) - 1];
                $file_path = date('Y/m') . '/' . $file_name;
                update_option('w_8ben_form_fileName', $file_name);
                update_option('w_8ben_form_filePath', $file_path);
                global $wpdb;
                $wpdb->delete('wp_posts', array('ID' => $w_8ben_form));
                $msg = "File upload successful!";
            }
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> ' . $msg . ' </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

        }

        if (isset($_POST['save_mailchimp_info'])) {
            update_option('mailchimp_list_id', trim($_REQUEST['mailchimp_list_id']));
            update_option('mailchimp_api_key', trim($_REQUEST['mailchimp_api_key']));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> Setting Updated </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }

        if (isset($_POST['save_xmpp_info'])) {
            $option = array();
            $option["xmpp_domain"] = trim($_REQUEST['xmpp_domain']);
            $option["xmpp_api_address"] = trim($_REQUEST['xmpp_api_address']);
            $option["xmpp_token_type"] = trim($_REQUEST['xmpp_token_type']);
            $option["xmpp_auth_code"] = trim($_REQUEST['xmpp_auth_code']);
            $option["xmpp_port"] = (int)trim($_REQUEST['xmpp_port']);

            update_option('xmpp_settings', $option);
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                <p><strong> Setting Updated </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }

        if (isset($_POST['save_request_rejection'])) {
            update_option('request_rejection_days', trim($_REQUEST['request_rejection_days']));
            update_option('mediation_fee', trim($_REQUEST['mediation_fee']));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> Setting Updated </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }

        if (isset($_POST['footer_copyright_text'])) {
            update_option('footer_copyright_text', trim($_REQUEST['footer_copyright_text']));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> Setting Updated </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }

        if (isset($_POST['contest_fee'])) {
            update_option('contest_fee', trim($_REQUEST['contest_fee']));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> Setting Updated </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }
        if (isset($_POST['time_to_load_notification'])) {
            update_option('time_to_load_notification', trim($_REQUEST['time_to_load_notification']));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> Setting Updated </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }

        if (isset($_POST['client_referral_fee'])) {
            update_option('client_referral_fee', trim($_REQUEST['client_referral_fee']));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> Setting Updated </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }

        if (isset($_POST['linguist_referral_fee'])) {
            update_option('linguist_referral_fee', trim($_REQUEST['linguist_referral_fee']));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> Setting Updated </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }
        if (isset($_POST['linguist_flex_referral_fee'])) {
            update_option('linguist_flex_referral_fee', trim($_REQUEST['linguist_flex_referral_fee']));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> Setting Updated </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }
        if (isset($_POST['seal_fee'])) {
            update_option('seal_fee', trim($_REQUEST['seal_fee']));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> Setting Updated </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }

        if (isset($_POST['view_other_proposals_fee'])) {
            update_option('view_other_proposals_fee', trim($_REQUEST['view_other_proposals_fee']));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> Setting Updated: view_other_proposals_fee </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }


        if (isset($_POST['hire_mediator_fee'])) {
            update_option('hire_mediator_fee', trim($_REQUEST['hire_mediator_fee']));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> Setting Updated </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }
        if (isset($_POST['client_flex_referral_fee'])) {
            update_option('client_flex_referral_fee', trim($_REQUEST['client_flex_referral_fee']));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> Setting Updated </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }

        //contest_insurance_fee_base,contest_insurance_fee_rate
        if (isset($_POST['contest_insurance_fee_base'])) {
            update_option('contest_insurance_fee_base', trim($_REQUEST['contest_insurance_fee_base']));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                        <p><strong> Setting Updated for Insurance Fee Base </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }

        if (isset($_POST['contest_insurance_fee_rate'])) {
            update_option('contest_insurance_fee_rate', trim($_REQUEST['contest_insurance_fee_rate']));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                        <p><strong> Setting Updated for Insurance Fee Rate </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }

        if (isset($_POST['award_duration_hours'])) {
            update_option('award_duration_hours', floatval(trim($_REQUEST['award_duration_hours'])));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                        <p><strong> Setting Updated for Award Duration in Hours </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }

        if (isset($_POST['undo_cancel_contest_limit_hours'])) {
            update_option('undo_cancel_contest_limit_hours', floatval(trim($_REQUEST['undo_cancel_contest_limit_hours'])));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                        <p><strong> Setting Updated for Undo Contest Limit in Hours </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }


        if (isset($_POST['create_guest_account'])) {
            //code-notes create (or recreate) ejabber guest account
            $log = [];
            do_action('freelinguist_create_chat_guest_account', $log);
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                        <p><strong> Setting Updated : created guest account </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

        }

        if (isset($_POST['save_fl_log_chat_to_db'])) {
            //code-notes set logging for jabber chat
            $word = 'off';
            $log_chat_to_db = '';
            if (array_key_exists('fl_log_chat_to_db', $_POST)) {
                $int_what = (int)$_POST['fl_log_chat_to_db'];
                if ($int_what) {
                    $word = 'on';
                    $log_chat_to_db = 1;
                }
            }
            update_option('fl_log_chat_to_db', $log_chat_to_db);

            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                        <p><strong> Turned ' . $word . ' Chat Logging </strong></p>
                    <button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button>
                    </div>';

        }


        //
        if (isset($_POST['save_skip_mail_ssl_verification'])) {
            //code-notes save skip_mail_ssl_verification option
            $skip_mail_ssl_verification = '';
            if (array_key_exists('skip_mail_ssl_verification', $_POST)) {
                $int_what = (int)$_POST['skip_mail_ssl_verification'];
                if ($int_what) {
                    $skip_mail_ssl_verification = 1;
                }
            }
            update_option('skip_mail_ssl_verification', $skip_mail_ssl_verification);

            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                        <p><strong> Setting Updated : Skip Email SSL Verification </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

        }


        if (isset($_POST['save_log_smtp_connections'])) {
            //code-notes save log_smtp_connections option
            $log_smtp_connections = '';
            if (array_key_exists('log_smtp_connections', $_POST)) {
                $int_what = (int)$_POST['log_smtp_connections'];
                if ($int_what) {
                    $log_smtp_connections = 1;
                }
            }
            update_option('log_smtp_connections', $log_smtp_connections);

            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                        <p><strong> Setting Updated : Log SMTP Connections </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

        }


        if (isset($_POST['save_automatic_job_canceled_days'])) {
            //PRINT_R($_REQUEST); EXIT;

            $fl_describe_date_timezone = FLInput::get('fl_describe_date_timezone');
            $auto_job_rejected_for_linguist_hours = $_REQUEST['auto_job_rejected_for_linguist_hours'];
            $auto_job_approvel_customer_hours = $_REQUEST['auto_job_approvel_customer_hours'];


            $hours_val = array("1", "24", "48", "72", "96");
            /* if (in_array($auto_job_rejected_for_linguist_hours, $hours_val)){
                $auto_job_rejected_for_linguist_hours_is = $auto_job_rejected_for_linguist_hours;
            }else{
                $auto_job_rejected_for_linguist_hours_is = 24;
            } */

            if (isset($_REQUEST['auto_job_rejected_for_linguist_hours'])) {
                $auto_job_rejected_for_linguist_hours_is = $auto_job_rejected_for_linguist_hours;
            } else {
                $auto_job_rejected_for_linguist_hours_is = 24;
            }




            $hours_val = array("1", "120", "180", "240", "300");
            if (in_array($auto_job_approvel_customer_hours, $hours_val)) {
                $auto_job_approvel_customer_hours_is = $auto_job_approvel_customer_hours;
            } else {
                $auto_job_approvel_customer_hours_is = 120;
            }

            update_option('auto_job_rejected_for_linguist_hours', $auto_job_rejected_for_linguist_hours_is);
            update_option('auto_job_approvel_customer_hours', $auto_job_approvel_customer_hours_is);

            update_option('fl_describe_date_timezone', $fl_describe_date_timezone);


            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> Updated </strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

        }

        if (isset($_REQUEST['save_withdrawal_setup'])) {
            update_option('withdrawal_fee_percentage', floatval($_REQUEST['withdrawal_fee_percentage']));
            update_option('withdrawal_fee_base', floatval($_REQUEST['withdrawal_fee_base']));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> Updated Widthdrawal Options</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

        }


        if (isset($_REQUEST['save_refill_setup'])) {
            update_option('refill_fee_percentage', floatval($_REQUEST['refill_fee_percentage']));
            update_option('refill_fee_base', floatval($_REQUEST['refill_fee_base']));
            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong> Updated Refill Options</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

        }

        ?>

        <?php if (isset($_REQUEST['typeis']) && $_REQUEST['typeis'] == 'mailchimpsetting') { ?>
            <div class="nav-tab1 nav-tab-active1">
                <form id="tax_form_f" method="POST" enctype="multipart/form-data"
                      action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=mailchimpsetting'); ?>&lang=en">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <td colspan="6"><span class="bold-and-blocking larger-text">Mailchimp</span></td>
                        </tr>

                            <tr class="user-rich-editing-wrap">
                                <th scope="row">Mailchimp List Id</th>
                                <td>
                                    <input type="text" value="<?php echo get_option('mailchimp_list_id'); ?>" title="Mailchimp List ID"
                                           name="mailchimp_list_id">
                                    <p class="description" id="">Coupon Amount. eg- e8e9285199</p>
                                    <hr>
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">Mailchimp API Key</th>
                                <td>
                                    <input type="text" value="<?php echo get_option('mailchimp_api_key'); ?>" title="Mailchimp Api Key"
                                           name="mailchimp_api_key">
                                    <p class="description" id="">Mailchimp API key. eg-
                                        85f262971191a6ef11db5984569a70db-us14</p>
                                    <hr>
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row"></th>
                                <td>
                                    <input type="submit" value="Update" class="button button-primary"
                                           name="save_mailchimp_info">
                                    <hr>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </form>
            </div>
        <?php } ?>

        <?php if (isset($_REQUEST['typeis']) && $_REQUEST['typeis'] == 'xmpp') {
            $option = get_option('xmpp_settings');

            ?>
            <div class="nav-tab1 nav-tab-active1">
                <form id="tax_form_f" method="POST" enctype="multipart/form-data"
                      action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=xmpp'); ?>&lang=en">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <td colspan="6"><span class="bold-and-blocking larger-text">Xmpp</span></td>
                        </tr>

                            <tr class="user-rich-editing-wrap">
                                <th scope="row">Domain</th>
                                <td>
                                    <input type="text" title="xmpp domain"
                                           value="<?php echo(isset($option['xmpp_domain']) ? $option['xmpp_domain'] : ''); ?>"
                                           name="xmpp_domain" size="40">
                                    <p class="description" id=""></p>
                                    <hr>
                                </td>
                            </tr>

                            <tr class="user-rich-editing-wrap">
                                <th scope="row">Port</th>
                                <td>
                                    <input type="number" title="xmpp port"
                                           value="<?php echo(isset($option['xmpp_port']) ? $option['xmpp_port'] : '') ?>"
                                           name="xmpp_port">
                                    <p class="description" id=""></p>
                                    <hr>
                                </td>
                            </tr>

                            <tr class="user-rich-editing-wrap">
                                <th scope="row">API Address</th>
                                <td>
                                    <input type="text" title="xmpp api"
                                           value="<?php echo(isset($option['xmpp_api_address']) ? $option['xmpp_api_address'] : '') ?>"
                                           name="xmpp_api_address" size="40">
                                    <p class="description" id=""></p>
                                    <hr>
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">Token Type</th>
                                <td>
                                    <input type="text" title="xmpp token type"
                                           value="<?php echo(isset($option['xmpp_token_type']) ? $option['xmpp_token_type'] : '') ?>"
                                           name="xmpp_token_type">
                                    <p class="description" id=""></p>
                                    <hr>
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">Authentication Code</th>
                                <td>
                                    <input type="text" title="xmpp auth code"
                                           value="<?php echo(isset($option['xmpp_auth_code']) ? $option['xmpp_auth_code'] : '') ?>"
                                           name="xmpp_auth_code" size="40">
                                    <p class="description" id=""></p>
                                    <hr>
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row"></th>
                                <td>
                                    <input type="submit" value="Update" class="button button-primary" name="save_xmpp_info">
                                    <hr>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </form>

                <div class="freelinguist-admin-guest-panel">

                        <span class="bold-and-blocking large-text">
                            Enable Announcement Broadcaset to Guest Users
                        </span>
                    <?php
                    $guest_broadcast_settings_class = '';
                    $guest_broadcast_actions_class = '';
                    $guest_broadcast_user_name = '';
                    $guest_broadcast_user_password = '';

                    $guest_broadcast_settings = get_option('guest_broadcast_settings', []);
                    if (empty($guest_broadcast_settings) || !array_key_exists('account_name', $guest_broadcast_settings)) {
                        $guest_broadcast_settings_class = 'freelinguist_hide_guest_broadcast_settings';
                        $guest_button_text = 'Create Guest Account';
                    } else {
                        $guest_button_text = 'Reset Guest Account';
                        //$guest_broadcast_actions_class =  'freelinguist_hide_guest_broadcast_actions';
                        if (array_key_exists('account_name', $guest_broadcast_settings)) {
                            $guest_broadcast_user_name = $guest_broadcast_settings['account_name'];
                        }
                        if (array_key_exists('account_password', $guest_broadcast_settings)) {
                            $guest_broadcast_user_password = $guest_broadcast_settings['account_password'];
                        }
                    }
                    ?>
                    <div class="freelinguist-guest-broadcast-settings <?= $guest_broadcast_settings_class ?>">
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <td>
                                    <span class="bold-and-blocking enhanced-text">Guest User Ejabber Name</span>
                                </td>
                                <td>
                                            <span class="bold-and-blocking enhanced-text">
                                                <?= $guest_broadcast_user_name ?>
                                            </span>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <span class="bold-and-blocking enhanced-text">Guest User Ejabber Password</span>
                                </td>
                                <td>
                                            <span class="bold-and-blocking enhanced-text">
                                                 <?= $guest_broadcast_user_password ?>
                                            </span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="freelinguist-guest-broadcast-actions  <?= $guest_broadcast_actions_class ?>">
                        <form
                                id="anon_jabby_form"
                                method="POST"
                                enctype="multipart/form-data"
                                action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=xmpp'); ?>&lang=en"
                        >

                            <table class="form-table">
                                <tbody>
                                <tr>

                                    <td>
                                        <input type="submit"
                                               value="<?= $guest_button_text ?>"
                                               class="button button-primary enhanced-text"
                                               name="create_guest_account">
                                    </td>

                                    <td>
                                        <!-- empty ! -->
                                    </td>
                                </tr>


                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Log Chat to DB</th>
                                    <td colspan="2">

                                        <label>
                                            <input type="checkbox" name="fl_log_chat_to_db"
                                                   id="fl_log_chat_to_db"
                                                   class="freelinguist-fancy-checkbox freelinguist-orange-checkbox"
                                                <?= get_option('fl_log_chat_to_db', 0) ? 'checked' : '' ?>
                                                   value="1"
                                            >
                                            Start logging All Chat information to the database. This is only for
                                            debugging with very few users on the web server
                                        </label>
                                        <br><br>
                                        <input type="submit" value="Update Chat Logging Settings"
                                               class="button button-primary" name="save_fl_log_chat_to_db">
                                        <hr>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </form> <!-- /#anon_jabby_form -->
                    </div> <!-- /.freelinguist-guest-broadcast-actions -->
                </div> <!-- /.freelinguist-admin-guest-panel -->

                <!-- code-notes start gui section for health checks-->

                <?php
                require_once(ABSPATH .
                    'wp-content/themes/the-translator/includes/admin-init/theme-setting-functions/health-check.php');
                freelinguist_do_healthcheck_gui_logic();
                ?>


                <!-- code-notes end flight check section-->
            </div> <!-- /.nav-tab-active -->
        <?php } ?>

        <?php if ((isset($_REQUEST['typeis']) && $_REQUEST['typeis'] == 'others') || !isset($_REQUEST['typeis'])) { ?>
            <div class="nav-tab1 nav-tab-active1">
                <table class="form-table">
                    <tbody>
                    <tr>
                        <td colspan="6"><span class="bold-and-blocking larger-text">Other</span></td>
                    </tr>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Tax form for Linguist</th>
                        <td>
                            <?php echo get_option('tax_form_fileName'); ?><br>
                            <form id="tax_form_f" method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">
                                <input type="file" name="tax_form">
                                <p class="description" id="">Coupon Amount.</p>
                                <input type="submit" value="Upload" class="button button-primary" name="save_tax_form">
                                <hr>
                            </form>
                        </td>
                    </tr>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">W-9 Form</th>
                        <td>
                            <?php echo get_option('w_9_form_fileName'); ?><br>
                            <form id="w_9_form_f" method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">
                                <input type="file" name="w_9_form">
                                <p class="description" id="">W-9 Form.</p>
                                <input type="submit" value="Upload" class="button button-primary" name="save_w_9_form">
                                <hr>
                        </td>
                    </tr>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">W-8BEN Form</th>
                        <td>
                            <?php echo get_option('w_8ben_form_fileName'); ?><br>
                            <form id="save_w_8_f" method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">

                                <input type="file" name="w_8ben_form">
                                <p class="description" id="">W-8BEN Form.</p>
                                <input type="submit" value="Upload" class="button button-primary"
                                       name="save_w_8ben_form">
                                <hr>
                            </form>
                        </td>
                    </tr>
                    <tr>
                        <td>

                        </td>
                        <td>
                            <form id="automatic_job_canceled_days_f" method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">
                                <table>
                                    <tr class="user-rich-editing-wrap">
                                        <th scope="row">Select hours for automatic job rejected for linguist (hours)</th>
                                        <td>


                                            <input type="number" name="auto_job_rejected_for_linguist_hours" min="0.5" max="100" title="Hours to Auto Reject Linguuist"
                                                   value="<?php echo get_option('auto_job_rejected_for_linguist_hours'); ?>"
                                                   step="0.5">
                                            <p class="description" id="">When a customer Rejects a job, the linguist has to choose
                                                "Approve Rejection" or "Hire Mediator" within this hour limit. .</p>
                                        </td>
                                    </tr>


                                    <tr class="user-rich-editing-wrap">
                                        <th scope="row">Select hours for automatic job approvel if customer don't approve
                                            completion. <?PHP ECHO get_option('auto_job_approvel_customer_hours'); ?></th>
                                        <td>
                                            <select name="auto_job_approvel_customer_hours" title="Hours to Auto Approve Customer">
                                                <option <?php echo (get_option('auto_job_approvel_customer_hours') == 1) ? 'selected' : ''; ?>
                                                        value="1"> 1 hour
                                                </option>
                                                <option <?php echo (get_option('auto_job_approvel_customer_hours') == 120) ? 'selected' : ''; ?>
                                                        value="120"> 120 hours
                                                </option>
                                                <option <?php echo (get_option('auto_job_approvel_customer_hours') == 180) ? 'selected' : ''; ?>
                                                        value="180"> 180 hours
                                                </option>
                                                <option <?php echo (get_option('auto_job_approvel_customer_hours') == 240) ? 'selected' : ''; ?>
                                                        value="240"> 240 hours
                                                </option>
                                                <option <?php echo (get_option('auto_job_approvel_customer_hours') == 300) ? 'selected' : ''; ?>
                                                        value="300"> 300 hours
                                                </option>
                                            </select>
                                            <p class="description" id="">The job is automatically approvel if customer don't approve
                                                completion or request revision within mentioned hours.</p>
                                        </td>
                                    </tr>


                                    <tr class="user-rich-editing-wrap">
                                        <th scope="row">When does the date for projects and contests end ?</th>
                                        <td>

                                            <input title="Describe how a date ends"
                                                   type="text" name="fl_describe_date_timezone"
                                                   size="50"
                                                   value="<?php echo get_option('fl_describe_date_timezone'); ?>""
                                            >
                                            <p class="description" id="">
                                                Set any words describing when the date ends for projects and contests.<br>
                                                Whatever is typed here will be displayed near the calendar for the user's
                                                information
                                            </p>
                                        </td>
                                    </tr>


                                    <tr class="user-rich-editing-wrap">
                                        <th scope="row">&nbsp;</th>
                                        <td>


                                            <input type="submit" value="Update" class="button button-primary"
                                                   name="save_automatic_job_canceled_days">
                                        </td>
                                    </tr>

                                </table>
                            </form>
                        </td>
                    </tr>





                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Withdrawl Setup</th>
                        <td>
                            <form id="withdrawal_setup" method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">

                                <input type="number" step="any" value="<?php echo get_option('withdrawal_fee_base'); ?>" title="Withdraw Fee Base"
                                       name="withdrawal_fee_base">
                                <p class="description" id="" style="margin-bottom: 1em">Withdrawal fee base.</p>

                                <input type="number" step="any" name="withdrawal_fee_percentage" title="Withdraw Fee Percentage"
                                       value="<?php echo get_option('withdrawal_fee_percentage'); ?>">
                                <p class="description" id="">Withdrawal fee Percentage.</p>
                                <input type="submit" value="Update" class="button button-primary"
                                       name="save_withdrawal_setup">
                                <hr>
                            </form>
                        </td>
                    </tr>

                    <!-- code-notes new refill options-->

                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Refill Setup</th>
                        <td>
                            <form id="withdrawal_setup" method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">

                                <input type="number" step="any" value="<?php echo get_option('refill_fee_base'); ?>" title="Withdraw Fee Base"
                                       name="refill_fee_base">
                                <p class="description" id="" style="margin-bottom: 1em">Refill fee base. (USD)</p>

                                <input type="number" step="any" name="refill_fee_percentage" title="Withdraw Fee Percentage"
                                       value="<?php echo get_option('refill_fee_percentage'); ?>">
                                <p class="description" id="">Refill fee Percentage.</p>
                                <input type="submit" value="Update" class="button button-primary"
                                       name="save_refill_setup">
                                <hr>
                            </form>
                        </td>
                    </tr>
                    
                    
                    <!-- code-notes end of new refill options -->

                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Customer fixed referral Fee</th>
                        <td colspan="2">
                            <form method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">
                                <input type="number" name="client_referral_fee" id="client_referral_fee" title="Client Referral Fee"
                                       value="<?php echo get_option('client_referral_fee'); ?>">
                                <p class="description">Charged when START is clicked for each linguist. Referral fee,
                                    Non-refundable (on acceptance of HIRE, charge immediately)</p>
                                <input type="submit" value="update" class="button button-primary"
                                       name="save_client_referral_fee">
                                <hr>
                            </form>
                        </td>
                    </tr>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Client flex referral fee(%)</th>
                        <td colspan="2">
                            <form method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">
                                <input type="number" step="any" name="client_flex_referral_fee" title="Client Flex Referral Fee"
                                       id="client_flex_referral_fee"
                                       value="<?php echo get_option('client_flex_referral_fee'); ?>">
                                <p class="description">Paid when the payment is done.</p>
                                <input type="submit" value="update" class="button button-primary"
                                       name="save_payment_processing_fee">
                                <hr>
                            </form>
                        </td>
                    </tr>

                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Linguist fixed referral Fee</th>
                        <td colspan="2">
                            <form method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">
                                <input type="number" step="any" name="linguist_referral_fee" id="linguist_referral_fee" title="Linguist Referral Fee"
                                       value="<?php echo get_option('linguist_referral_fee'); ?>">
                                <p class="description">Paid when the linguist places a bid. Referral fee, Non-refundable
                                    (on acceptance of HIRE, charge immediately)</p>
                                <input type="submit" value="update" class="button button-primary"
                                       name="save_linguist_referral_fee">
                                <hr>
                            </form>
                        </td>
                    </tr>

                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Linguist flex referral Fee(%)</th>
                        <td colspan="2">
                            <form method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">
                                <input type="number" step="any" name="linguist_flex_referral_fee" title="Linguist Flex Referral Fee"
                                       id="linguist_flex_referral_fee"
                                       value="<?php echo get_option('linguist_flex_referral_fee'); ?>">
                                <p class="description">Paid when the linguist places a bid. Referral fee, Non-refundable
                                    (on acceptance of HIRE, charge immediately)</p>
                                <input type="submit" value="update" class="button button-primary"
                                       name="save_linguist_referral_fee">
                                <hr>
                            </form>
                        </td>
                    </tr>

                    <tr class="user-rich-editing-wrap">
                        <th scope="row">View other proposals($)</th>
                        <td colspan="2">
                            <form method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">
                                <input type="number" step="any" name="view_other_proposals_fee" title="View Other Proposals Fee"
                                       id="view_other_proposals_fee"
                                       value="<?php echo get_option('view_other_proposals_fee'); ?>">
                                <p class="description">Paid when the linguist chooses to look at other proposals</p>
                                <input type="submit" value="update" class="button button-primary"
                                       name="save_view_other_proposals_fee">
                                <hr>
                            </form>
                        </td>
                    </tr>

                    <!--   code-notes added new setting for view_Sealed    -->

                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Seal content fee($)</th>
                        <td colspan="2">
                            <form method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">
                                <input type="number" step="any" name="seal_fee" id="seal_fee" title="Seal Fee"
                                       value="<?php echo get_option('seal_fee'); ?>">
                                <p class="description">Paid when the linguist seal content</p>
                                <input type="submit" value="update" class="button button-primary" name="save_seal_fee">
                                <hr>
                            </form>
                        </td>
                    </tr>


                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Hire mediator fee($)</th>
                        <td colspan="2">
                            <form method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">
                                <input type="number" step="any" name="hire_mediator_fee" id="hire_mediator_fee" title="Mediator Fee"
                                       value="<?php echo get_option('hire_mediator_fee'); ?>">
                                <p class="description">Paid when the linguist hire mediator</p>
                                <input type="submit" value="update" class="button button-primary"
                                       name="save_hire_mediator_fee">
                                <hr>
                            </form>
                        </td>
                    </tr>

                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Competition Fee($)</th>
                        <td colspan="2">
                            <form id="footer_text" method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">

                                <input type="number" name="contest_fee" id="contest_fee" title="Contest Fee"
                                       value="<?php echo get_option('contest_fee'); ?>">
                                <p class="description" id="">Competition Fee($)</p>

                                <input type="submit" value="update" class="button button-primary"
                                       name="save_footer_copyright_info">
                                <hr>
                            </form>
                        </td>
                    </tr>

                    <!--                            code-notes added contest_insurance_fee_base,contest_insurance_fee_rate -->

                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Competition Insurance Fee Base($)</th>
                        <td colspan="2">
                            <form id="footer_text" method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">

                                <input type="number" name="contest_insurance_fee_base" id="contest_insurance_fee_base" title="Contest Insurance Fee Base"
                                       value="<?php echo get_option('contest_insurance_fee_base'); ?>">
                                <p class="description" id="">Competition Insurance Fee Base($)</p>

                                <input type="submit" value="update" class="button button-primary"
                                       name="save_competition_insurance_base">
                                <hr>
                            </form>
                        </td>
                    </tr>

                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Competition Insurance Percentage Rate(%)</th>
                        <td colspan="2">
                            <form id="footer_text" method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">

                                <input type="number" name="contest_insurance_fee_rate" id="contest_insurance_fee_rate" title="Contest Insurance Fee Rate"
                                       value="<?php echo get_option('contest_insurance_fee_rate'); ?>">
                                <p class="description" id="">Competition Insurance Percentage Rate(%)</p>

                                <input type="submit" value="update" class="button button-primary"
                                       name="save_competition_insurance_rate">
                                <hr>
                            </form>
                        </td>
                    </tr>

                    <!--   code-notes added award_duration_hours to option gui -->

                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Award Duration(hours)</th>
                        <td colspan="2">
                            <form id="footer_text" method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">

                                <input type="number" name="award_duration_hours" id="award_duration_hours" title="Award Duration Hours"
                                       value="<?php echo get_option('award_duration_hours'); ?>">
                                <p class="description" id="">How long to wait after the competition deadline before
                                    freelancers can self claim? (Hours)</p>

                                <input type="submit" value="update" class="button button-primary"
                                       name="save_award_duration_hours">
                                <hr>
                            </form>
                        </td>
                    </tr>


                    <!--   code-notes added undo_cancel_contest_limit_hours to option gui -->

                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Time allowed to undo contest decision(hours)</th>
                        <td colspan="2">
                            <form id="footer_text" method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">

                                <input title="Undo Hour Limit " type="number" name="undo_cancel_contest_limit_hours"
                                       id="undo_cancel_contest_limit_hours"
                                       value="<?php echo get_option('undo_cancel_contest_limit_hours'); ?>">
                                <p class="description" id="">How long can staff wait to undo a contest cancellation
                                    request after deciding? (Hours)</p>

                                <input type="submit" value="update" class="button button-primary"
                                       name="save_undo_cancel_competition_duration_hours">
                                <hr>
                            </form>
                        </td>
                    </tr>

                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Time to load notification(in min)</th>
                        <td colspan="2">
                            <form id="footer_text" method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">

                                <input type="number" name="time_to_load_notification" id="time_to_load_notification" title="Time To Load Notification"
                                       value="<?php echo get_option('time_to_load_notification'); ?>">
                                <p class="description" id="">Time to load notification(in min)</p>

                                <input type="submit" value="update" class="button button-primary"
                                       name="save_footer_copyright_info">
                                <hr>
                            </form>
                        </td>
                    </tr>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Footer Text</th>
                        <td colspan="2">
                            <form id="footer_text" method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">

                                <input type="text" style="width:600px;" name="footer_copyright_text" title="Footer Copywrite Text"
                                       id="footer_copyright_text"
                                       value="<?php echo get_option('footer_copyright_text'); ?>">
                                <p class="description" id="">Footer Copyright Information.</p>

                                <input type="submit" value="update" class="button button-primary"
                                       name="save_footer_copyright_info">
                                <hr>
                            </form>
                        </td>
                    </tr>

                    <!-- code-notes add new option to not verify ssl in smtp connections       -->
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Enable SMTP in XAMPP</th>
                        <td colspan="2">
                            <form id="footer_text" method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">

                                <label>
                                    <input type="checkbox" name="skip_mail_ssl_verification"
                                           id="skip_mail_ssl_verification"
                                           class="freelinguist-fancy-checkbox freelinguist-orange-checkbox"
                                        <?= get_option('skip_mail_ssl_verification', 0) ? 'checked' : '' ?>
                                           value="1"
                                    >
                                    Skip Verification of SSL connections for mail
                                </label>
                                <br><br>
                                <input type="submit" value="update" class="button button-primary"
                                       name="save_skip_mail_ssl_verification">
                                <hr>
                            </form>
                        </td>
                    </tr>


                    <!-- code-notes add new option to not verify ssl in smtp connections       -->
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Turn on SMTP Logging</th>
                        <td colspan="2">
                            <form id="footer_text" method="POST" enctype="multipart/form-data"
                                  action="<?php echo admin_url('admin.php?page=customer-theme-option-val&typeis=others'); ?>&lang=en">

                                <label>
                                    <input type="checkbox" name="log_smtp_connections"
                                           id="log_smtp_connections"
                                           class="freelinguist-fancy-checkbox freelinguist-green-checkbox"
                                        <?= get_option('log_smtp_connections', 0) ? 'checked' : '' ?>
                                           value="1"
                                    >
                                    Add communication talk between php and smtp server to wp-content/debug.log
                                </label>
                                <br><br>
                                <input type="submit" value="update" class="button button-primary"
                                       name="save_log_smtp_connections">
                                <hr>
                            </form>
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>
        <?php } ?>
        <!-- END: Others-->
    </div>

    <?php
}