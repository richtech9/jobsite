<?php

/*
    * current-php-code 2021-Jan-8
    * input-sanitized :
    * current-wp-template:  admin-screen  settings payment
*/
use Carbon\Carbon;
use Stripe\WebhookEndpoint;


function payment_info_val()
{
    $b_show_all_webhooks = intval(FLInput::get('all_stripe_hooks',0)) ? true : false;

    ?>
    <div class="wrap">
        <h3>Payment Settings</h3>

        <h6> 1. Stripe allows create a new account. For each server, create a new account/KEY to avoid sending one Stripe response to several test servers. A new webhook will be created for each KEY.</h6>
        <h6> 2. Productioin server settings: "Live", "Real URL", empty NGrok field </h6>
        <h6> 3. Localhost server like test.com: Test, "Fake URL", follow NGrok instructions and add URL to the NGrok text box</h6>
        <h6> 4. Real test server like staging server: Test, "Real URL", empty NGrok field</h6>
        <?php
        if (isset($_REQUEST['submit_payment'])) {
            update_option('payment_mode', $_REQUEST['payment_mode']);


            $b_regenerate_webhook = false;
            /*
             * stripe_live_publishable_key
             * stripe_live_secret_key
             * stripe_test_publishable_key
             * stripe_test_secret_key
             */

            $old_stripe_live_public = get_option('stripe_live_publishable_key');
            $old_stripe_live_secret = get_option('stripe_live_secret_key');
            $old_stripe_test_public = get_option('stripe_test_publishable_key');
            $old_stripe_test_secret = get_option('stripe_test_secret_key');



            $old_ngrok_value = get_option('fl_ngrok_testing',null);
            $old_is_fake_url = (int)get_option('fl_is_fake_url_for_development');

            $new_stripe_live_public = FLInput::get('stripe_live_publishable_key');
            $new_stripe_live_secret = FLInput::get('stripe_live_secret_key');
            $new_stripe_test_public = FLInput::get('stripe_test_publishable_key');
            $new_stripe_test_secret = FLInput::get('stripe_test_secret_key');



            $new_ngrok_value = FLInput::get('fl_ngrok_testing');
            $new_is_fake_url = intval(FLInput::get('fl_is_fake_url_for_development'));

            $new_paypal_email = FLInput::get('paypal_email');

            if ($old_ngrok_value !== $new_ngrok_value) {$b_regenerate_webhook = true;}
            else if ($old_stripe_live_public !== $new_stripe_live_public) {$b_regenerate_webhook = true;}
            else if ($old_stripe_live_secret !== $new_stripe_live_secret) {$b_regenerate_webhook = true;}
            else if ($old_stripe_test_public !== $new_stripe_test_public) {$b_regenerate_webhook = true;}
            else if ($old_stripe_test_secret !== $new_stripe_test_secret) {$b_regenerate_webhook = true;}
            else if ($old_is_fake_url !== $new_is_fake_url) {$b_regenerate_webhook = true;}

//            will_send_to_error_log('test',[
//                  '$b_regenerate_webhook' => $b_regenerate_webhook ,
//                  '$old_stripe_live_public' => $old_stripe_live_public,
//                  '$old_stripe_live_secret' => $old_stripe_live_secret,
//                  '$old_stripe_test_public' => $old_stripe_test_public,
//                  '$old_stripe_test_secret' =>$old_stripe_test_secret ,
//                  '$old_ngrok_value' => $old_ngrok_value,
//                  '$old_is_fake_url' => $old_is_fake_url,
//                  '$new_stripe_live_public' => $new_stripe_live_public,
//                  '$new_stripe_live_secret' => $new_stripe_live_secret,
//                  '$new_stripe_test_public' => $new_stripe_test_public,
//                  '$new_stripe_test_secret' => $new_stripe_test_secret,
//                  '$new_ngrok_value' => $new_ngrok_value,
//                  '$new_is_fake_url' => $new_is_fake_url,
//                  '$new_paypal_email' => $new_paypal_email,
//            ]);


            update_option('fl_is_fake_url_for_development',$new_is_fake_url );
            update_option('fl_ngrok_testing', $new_ngrok_value);

            update_option('stripe_live_publishable_key', $new_stripe_live_public);
            update_option('stripe_live_secret_key', $new_stripe_live_secret);
            update_option('stripe_test_publishable_key', $new_stripe_test_public);
            update_option('stripe_test_secret_key', $new_stripe_test_secret);


            update_option('paypal_email', $new_paypal_email);

            if ($b_regenerate_webhook ) {
                FLStripeWebHookManager::regenerate_webhook(trim($_REQUEST['fl_ngrok_testing']));
                //delete webhook, if existing, then create it, if live url or ngrok
            }

            //update gateway lists

            $gateway_options = [
                'enable_payments_stripe_credit_card' => ['list' => 'payments', 'value' => FLPaymentGateways::GATEWAY_STRIPE_CREDIT_CARD],
                'enable_withdraws_stripe_credit_card' => ['list' => 'withdraws', 'value' => FLPaymentGateways::GATEWAY_STRIPE_CREDIT_CARD],
                'enable_payments_alipay' => ['list' => 'payments', 'value' => FLPaymentGateways::GATEWAY_ALIPAY],
                'enable_withdraws_alipay' => ['list' => 'withdraws', 'value' => FLPaymentGateways::GATEWAY_ALIPAY],
                'enable_payments_paypal' => ['list' => 'payments', 'value' => FLPaymentGateways::GATEWAY_PAYPAL],
                'enable_withdraws_paypal' => ['list' => 'withdraws', 'value' => FLPaymentGateways::GATEWAY_PAYPAL],
            ];

            $my_payment_gateways = [];
            $my_withdraw_gateways = [];

            foreach ($gateway_options as $da_option_name => $dets) {
                $my_value = $dets['value'];
                $my_list = $dets['list'];
                if (isset($_POST[$da_option_name])) {
                    if ($my_list === 'payments') {
                        $my_payment_gateways[] = $my_value;
                    } else {
                        $my_withdraw_gateways[] = $my_value;
                    }
                }
            }
            update_option(FLPaymentGateways::OPTION_NAME_ENABLED_PAYMENT_GATEWAYS,$my_payment_gateways);
            update_option(FLPaymentGateways::OPTION_NAME_ENABLED_WITHDRAW_GATEWAYS,$my_withdraw_gateways);


            echo '  <div class="updated notice is-dismissible" id="setting-error-settings_updated">
    <p><strong>Updated.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';


        }

        $enabled_payment_gateways_array_of_names = get_option(FLPaymentGateways::OPTION_NAME_ENABLED_PAYMENT_GATEWAYS,FLPaymentGateways::DEFAULT_PAYMENT_GATEWAY_NAMES_ARRAY);
        $enabled_withdraw_gateways_array_of_names = get_option(FLPaymentGateways::OPTION_NAME_ENABLED_WITHDRAW_GATEWAYS,FLPaymentGateways::DEFAULT_WITHDRAW_GATEWAY_NAMES_ARRAY);


        if(FLInput::exists('clear_all_local_webhooks')) {
            $webhooks =  FLStripeWebHookManager::list_all_webhooks(true);

            $errors = [];
            //array of webhook ids
            $delete_these = [];
            foreach ($webhooks as$hook) {
                $id = $hook->id;
                $delete_these[] = $id;
            }
            foreach ($delete_these as $gone) {
                try {
                    FLStripeWebHookManager::delete_webhook($gone);
                } catch (Exception $e) {
                    $errors[] = $gone . ' ' . will_get_exception_string($e);
                   will_send_to_error_log('oops ',will_get_exception_string($e));
                }
            }
            if (count($errors)) {
                $all_errors = implode('<br>',$errors);
                ?>
                <div class="updated settings-error error notice is-dismissible" id="setting-error-settings_updated">
                    <p>
                        <strong>Errors Removing Stripe Webhooks.</strong>
                        <br>
                        <?= $all_errors ?>
                    </p>

                    <button class="notice-dismiss" type="button">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                    <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                </div>';

                <?php
            }
        }

        ?>
        <form id="setting_logo_form" method="POST" enctype="multipart/form-data"
              action="<?php echo admin_url('admin.php?page=payment-info-val'); ?>&lang=en">
            <table class="form-table">
                <tbody>
                <tr class="">
                    <th scope="row">Payment Mode</th>
                    <td scope="row">
                        <select name="payment_mode" id="payment_mode" title="Payment Mode">
                            <option value="Test" <?php echo (get_option('payment_mode') == 'Test') ? 'selected' : ''; ?>>
                                Test
                            </option>
                            <option value="Live" <?php echo (get_option('payment_mode') == 'Live') ? 'selected' : ''; ?>>
                                Live
                            </option>
                        </select>
                        <p class="description">Stripe Payment Mode</p>
                    </td>
                </tr>






                <tr class="">
                    <th scope="row">Enable Stripe Credit Cards</th>
                    <td scope="row">
                        <label>
                            <input type="checkbox" name="enable_payments_stripe_credit_card"
                                   class="freelinguist-fancy-checkbox freelinguist-green-checkbox"
                                   value="1"
                                   <?= in_array(FLPaymentGateways::GATEWAY_STRIPE_CREDIT_CARD,$enabled_payment_gateways_array_of_names) ? 'checked' : '' ?>
                            >
                            Enable Credit Cards For Wallet Refills
                        </label>
                    </td>
                    <td scope="row">
<!--                        <label>-->
<!--                            <input type="checkbox" name="enable_withdraws_stripe_credit_card"-->
<!--                                   class="freelinguist-fancy-checkbox freelinguist-green-checkbox"-->
<!--                                   value="1"-->
<!--                                --><?//= in_array(FLPaymentGateways::GATEWAY_STRIPE_CREDIT_CARD,$enabled_withdraw_gateways_array_of_names) ? 'checked' : '' ?>
<!--                            >-->
<!--                            Enable Stripe To Be Used as Withdraws-->
<!--                        </label>-->
                            <label>Stripe for Withdraws is not supported in the code</label>
                    </td>
                </tr>


                <tr class="">
                    <th scope="row">Enable AliPay Via Stripe</th>
                    <td scope="row">
                        <label>
                            <input type="checkbox" name="enable_payments_alipay"
                                   class="freelinguist-fancy-checkbox freelinguist-green-checkbox"
                                   value="1"
                                <?= in_array(FLPaymentGateways::GATEWAY_ALIPAY,$enabled_payment_gateways_array_of_names) ? 'checked' : '' ?>
                            >
                            Enable AliPay For Wallet Refills
                        </label>
                    </td>
                    <td scope="row">
                        <label>
                            <input type="checkbox" name="enable_withdraws_alipay"
                                   class="freelinguist-fancy-checkbox freelinguist-green-checkbox"
                                   value="1"
                                <?= in_array(FLPaymentGateways::GATEWAY_ALIPAY,$enabled_withdraw_gateways_array_of_names) ? 'checked' : '' ?>
                            >
                            Enable AliPay To Be Used as Withdraws
                        </label>
                    </td>
                </tr>



                <tr class="">
                    <th scope="row">Stripe publishable key</th>
                    <td scope="row">
                        <input size="50" type="text" name="stripe_live_publishable_key"  title="Stripe Live Publishable Key"
                               value="<?php echo get_option('stripe_live_publishable_key'); ?>">
                        <p class="description">Stripe Live publishable key. eg:- pk_test_mo7Ze4S2ZLdYooQx7vl1fC9a</p>
                    <td scope="row">
                        <input size="50" type="text" name="stripe_test_publishable_key"  title="Stripe Test Publishable Key"
                               value="<?php echo get_option('stripe_test_publishable_key'); ?>">
                        <p class="description">Stripe Test publishable key. eg:- pk_test_mo7Ze4S2ZLdYooQx7vl1fC9a</p>
                    </td>
                </tr>
                <tr class="">
                    <th scope="row">Stripe secret key</th>
                    <td scope="row">
                        <input size="50" type="password" name="stripe_live_secret_key"  title="Stripe Live Secret Key"
                               value="<?php echo get_option('stripe_live_secret_key'); ?>">
                        <p class="description">Stripe Live secret key. eg:- sk_test_Q7WTvSnfKPazDdcDCeljsAG9</p>
                    </td>
                    <td scope="row">
                        <input size="50" type="password" name="stripe_test_secret_key"  title="Stripe Test Secret Key"
                               value="<?php echo get_option('stripe_test_secret_key'); ?>">
                        <p class="description">Stripe Test secret key. eg:- sk_test_Q7WTvSnfKPazDdcDCeljsAG9</p>
                    </td>
                </tr>
                <tr>
                    <th>
                    </th>
                    <td>
                        <hr>
                    </td>
                    <td>
                        <hr>
                    </td>
                </tr>

                <tr class="">
                    <th scope="row">Enable PayPal</th>
                    <td scope="row">
                        <label>
                            <input type="checkbox" name="enable_payments_paypal"
                                   class="freelinguist-fancy-checkbox freelinguist-green-checkbox"
                                   value="1"
                                <?= in_array(FLPaymentGateways::GATEWAY_PAYPAL,$enabled_payment_gateways_array_of_names) ? 'checked' : '' ?>
                            >
                            Enable PayPal For Wallet Refills
                        </label>

                    </td>
                    <td scope="row">
                        <label>
                            <input type="checkbox" name="enable_withdraws_paypal"
                                   class="freelinguist-fancy-checkbox freelinguist-green-checkbox"
                                   value="1"
                                <?= in_array(FLPaymentGateways::GATEWAY_PAYPAL,$enabled_withdraw_gateways_array_of_names) ? 'checked' : '' ?>
                            >
                            Enable PayPal To Be Used as Withdraws
                        </label>
                    </td>
                </tr>
                <tr class="">
                    <th scope="row">Paypal email</th>
                    <td scope="row">
                        <input size="50" type="text" name="paypal_email" title="Paypal Email"
                               value="<?php echo get_option('paypal_email'); ?>">
                        <p class="description">Paypal email. eg:- lakhvinder.xicom-facilitator@gmail.com</p>
                        <div style="display: inline-block;float:right; padding-right: 2em">
                            <p class="description">
                                <u>To test, use this account for the sandbox seller in the inbox box:</u>
                                <br>
                                fcustomer1-facilitator@gmail.com
                                &nbsp;&nbsp;customerf1
                            </p>


                            <p class="description" >
                                <u>When paying,  this account for the sandbox buyer:</u>
                                <br>
                                fcustomer1-buyer@gmail.com
                                &nbsp;&nbsp;customerf1
                            </p>

                        </div>
                    </td>
                    <td>
                </tr>

                <tr class="">
                    <th scope="row">Local Development or Production Server</th>
                    <td scope="row">
                        <select name="fl_is_fake_url_for_development" id="fl_is_fake_url_for_development"
                                title="Is this a real url on this site?" style="width:fit-content; max-width: 1000px">
                            <option value="0" <?php echo (intval(get_option('fl_is_fake_url_for_development',0))) ? 'selected' : ''; ?>>
                                This site uses a real url that stripe.com can reach
                            </option>
                            <option value="1" <?php echo (intval(get_option('fl_is_fake_url_for_development',1))) ? 'selected' : ''; ?>>
                                This site uses a fake url like test.com at localhost
                            </option>
                        </select>
                        <p class="description">
                            During local development, e.g. test.com, stripe's payment hooks should not send data to the real url on the Internet.
                            <br> In thise, NGrok needs to be activated below, and then they use the grok to forward to localhost.
                        </p>
                    </td>
                </tr>

                <tr class="">
                    <th scope="row">NGrok (For payment test in LocalHost, to receive Stripe responses from Internet)</th>
                    <td scope="row">
                        <input size="50" type="text" name="fl_ngrok_testing" title="NGrok https tunnle"
                               value="<?php echo get_option('fl_ngrok_testing',''); ?>">
                        <p class="description">
                            For testing payment only. For anyother functions, or in production, must remove or blank it out.
                            <br>
                            Only needed for testing payment on localhost, do not need at all if running on real url with https cert<br>
                        </p>
                        <p>
                            Go to https://ngrok.com/ and download
                            <br>
                            Login using : fcustomer1@gmail.com
                            <br>not.2.complex.not.too.s55ft
                            <br>
                            Follow online instructions how to configure (its near the download link)
                            <br>
                            <strong> Then open CMD and run: <br>
                            (path to ngrok) ngrok http -host-header=rewrite (localhost root):80 </strong>
                            <br>
                            example: D:\tools\ngrok http -host-header=rewrite test.com:80 <br>
                            In the CMD, it'll display the session status.<br>
                            <strong> Copy AA in "Forwarding AA-> BB" into the text box above.</strong><br>
                            <strong>To test the payment, you need to use the URL AA.</strong><br>
                        </p>
                    </td>
                    <td>
                </tr>
                <tr class="">
                    <th scope="row"></th>
                    <td scope="row">
                        <input type="submit" name="submit_payment" value="Submit">
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
    <?php
    $webhooks =  FLStripeWebHookManager::list_all_webhooks(!$b_show_all_webhooks);
    $our_server_id = FLStripeWebHookManager::get_server_id();
    ?>
    <script>
        jQuery(function() {
           jQuery('button.fl-show-all-stripe-webhooks').click(function() {
               window.location.href = window.location.href + '&all_stripe_hooks=1';
           });
        });
    </script>
    <h1> Webhook to receive Stripe responses (only for dev to debug)</h1>
    <p>
        <span style="" class="enhanced-text">


            The Stripe account (determined by the above stripe key) lets you see the status of each webhook and the following info tells you the info of each webhook.<br>

            Below only shows the hooks setup for this computer. To see all webhooks for this Stripe account (sandbox or real) including other servers, press this button
        </span>
        <button type="button" class="button fl-show-all-stripe-webhooks">Show webhoooks to all servers</button>
        <br> Current Server ID for Stripe Webhooks is <code><?=$our_server_id ?></code> (auto generated)
    </p>
    <br>
    <form id="stripe-webhook-controrl" method="POST" enctype="multipart/form-data"
          action="<?php echo admin_url('admin.php?page=payment-info-val'); ?>&lang=en" style="max-width: 1000px">
        <table class="form-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Is Current</th>
                <th>Server ID</th>
                <th>When Created</th>
                <th>Description</th>
                <th style="width: 33%">URL</th>
            </tr>
        </thead>
        <tbody>

    <?php

    $current_id = null;
    /**
     * @var WebhookEndpoint $current_webhook
     */
    $current_webhook =  get_option(FLStripeWebHookManager::WEBHOOK_DATA_OPTION_NAME,null);
    if ($current_webhook) {
        $current_id = $current_webhook->id;
    }

    foreach ($webhooks as$hook) {
        $url = $hook->url;
        $carbon = Carbon::createFromTimestamp($hook->created,'America/Chicago');
        $human_time =  $carbon->toIso8601String();
        $description = $hook->description;
        $actions = $hook->enabled_events;
        $actions_to_read = implode('<br>',$actions);
        $description = $description . '<br>'. $actions_to_read;
        $id = $hook->id;
        $is_active = $current_id === $id;
        $server_id = '';
        if (isset($hook->metadata->server_guid)) {
            $server_id = $hook->metadata->server_guid;
        }
        if ($our_server_id !== $server_id) {
            $server_id_class = 'fl-different-server-id';
        } else {
            $server_id_class = 'fl-same-server-id';
        }
        ?>
        <tr>
            <td><?= $id?></td>
            <td><?= ($is_active? 'YES':'')?></td>
            <td class="<?= $server_id_class?>"><?= $server_id?></td>
            <td><?= $human_time?></td>
            <td><?= $description?></td>
            <td><?= $url?></td>
        </tr>
        <?php
    }
    if ($current_webhook) {
        $url = $current_webhook->url;
        $carbon = Carbon::createFromTimestamp($current_webhook->created,'America/Chicago');
        $human_time =  $carbon->toIso8601String();
        $description = $current_webhook->description;
        $actions = $current_webhook->enabled_events;
        $actions_to_read = implode('<br>',$actions);
        $description = $description . '<br>'. $actions_to_read;
        $id = $current_webhook->id;
        $is_active = $current_id === $id;
        $server_id = '';
        if (isset($current_webhook->metadata->server_guid)) {
            $server_id = $current_webhook->metadata->server_guid;
        }
        if ($our_server_id !== $server_id) {
            $server_id_class = 'fl-different-server-id';
        } else {
            $server_id_class = 'fl-same-server-id';
        }
        ?>
        <tr style="background-color: gainsboro; margin-top: 2em">
            <td><?= $id?></td>
            <td><?= ($is_active? 'Currently Used':'')?></td>
            <td class="<?= $server_id_class?>"><?= $server_id?></td>
            <td><?= $human_time?></td>
            <td><?= $description?></td>
            <td><?= $url?></td>
        </tr>
        <?php
    }
    ?>
        <tr >
            <td colspan="5">
                <input type="submit" name="clear_all_local_webhooks" value="Reset All Webhooks of Only This Server (remove all existing and create new one)">
                <br>    Click this button once and it'll be permanent and registered.If multipe servers use the same Stripe KEY above, they may share the same webhook.
            </td>
        </tr>
        </tbody>
        </table>
    </form>
    <?php

}