<?php

/*
 * current-php-code 2021-Jan-10
 * input-sanitized :refill_amount
 * current-wp-template:  admin-screen  for manually adding to wallet balance
 */

function refill_account_manually()
{
    global $wpdb;
    $refill_amount = floatval(FLInput::get('refill_amount'));
    $refill_message = FLInput::get('refill_message','Refill by support',FLInput::YES_I_WANT_CONVESION,
        FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);
    ?>
    <div class="freelng-set-panle">
        <div class="wrap">
            <h3>Refill Account</h3>
            <?php
            if (FLInput::exists('save_refill_options')) {
                try {
                    $current_user = wp_get_current_user();
                    if ( in_array('administrator',$current_user->roles) || in_array('administrator_for_client',$current_user->roles)) {
                        $author__in = array();
                    } else {
                        $author__in = getReportedUserByUserId();
                    }

                    $user_email = $_REQUEST['user_email_is'];
                    $user = get_user_by('email', $user_email);
                    if (!empty($user)) {
                        $selected_user = $user->ID;

                        if (!empty($selected_user) && $refill_amount >= 0 && $refill_amount <= 1000000000) {
                            if (empty($author__in) || in_array($selected_user, $author__in)) {
                                $user_balance = floatval(get_user_meta($selected_user, 'total_user_balance', true));
                                if ($user_balance === false) {
                                    throw new RuntimeException("User does not have a balance meta");
                                }
                                $new_balance = $user_balance + $refill_amount;
                                $new_transaction_row_id = fl_transaction_insert(amount_format($refill_amount), 'done', 'refill',
                                    $selected_user, get_current_user_id(), $refill_message,'manual');

                                update_user_meta($selected_user, 'total_user_balance', amount_format($new_balance));

                                $sql_for_trx = "SELECT txn_id FROM wp_fl_transaction where ID = $new_transaction_row_id";

                                $res_for_txn = $wpdb->get_results($sql_for_trx);
                                $txn_number = $res_for_txn[0]->txn_id;

                                $transactionTitle = 'Refill amount: '.amount_format($refill_amount);
                                $transaction_id = transaction_updated($transactionTitle,$selected_user,amount_format($refill_amount),
                                    $refill_message,FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_REFILL]);

                                $post_txn_id = get_post_meta($transaction_id,FLTransactionLookup::META_KEY_MODIFIED_TRANSACTION_ID,true);
                                $sql_update_transaction = "UPDATE wp_fl_transaction SET transaction_post_id = $transaction_id,gateway_txn_id='$post_txn_id'
                                                            WHERE id = $new_transaction_row_id";
                                $wpdb->query($sql_update_transaction);
                                will_throw_on_wpdb_error($wpdb);

                                $payment_data = array(
                                    'txn_id' => $txn_number,
                                    'transaction_post_id' => $transaction_id,
                                    'payment_amount' => $refill_amount,
                                    'payment_status' => 'Completed',
                                    'item_name' => 'Refill',
                                    'user_id' => $selected_user,
                                    'payment_type' => 'Manual',
                                    'description'=> $refill_message,
                                    'refill_by' => get_current_user_id()
                                );
                                //Insert new payment history
                                $wpdb->insert('wp_payment_history', $payment_data);

                                $user_detail = get_userdata($selected_user);

                                $variables = array();
                                $variables['refill_amount'] = $refill_amount;
                                $variables['refill_message'] = $refill_message;

                                emailTemplateForUser($user_detail->user_email, REFILL_ACCOUNT_BY_ADMIN_TEMPLATE, $variables);
                                echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                        <p><strong>Added successfully.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                            } else {
                                echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                <p><strong>You are an unauthorized user for this request.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">You are an unauthorized user for this request.</span></button></div>';
                            }

                        } else {
                            echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                <p><strong>Please enter correct value in all fields.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

                        }
                    } else {
                        echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                <p><strong>Email id not exist..</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

                    }
                }
                catch (Exception $e) {
                    echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">'.
                                '<p><strong style="color:red">Error: '.$e->getMessage().'</strong></p>'.
                                '<button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button>'.
                                '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                }
            }
            ?>
            <p class="description">Refill customer or Linguist account.</p>
            <form id="setting_pannel" method="POST"
                  action="<?php echo admin_url('admin.php?page=freelinguist-admin-manual-refill&lang=en'); ?>">
                <table class="form-table">
                    <tbody>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Email address</th>
                        <td>
                            <input type="text" name="user_email_is" id="user_email_is" title="Email Address" size="60">

                            <script type="text/javascript">
                                jQuery(function () {
                                    jQuery("#user_email_is").autocomplete({
                                        source: '<?php echo get_site_url();?>/?action=get_user_list_by_autocomplete&lang=en',
                                        minLength: 1
                                    });
                                });
                            </script>
                            <p class="description">Email address.</p>
                        </td>
                        <td>
                        </td>
                    </tr>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Amount</th>
                        <td>
                            <input type="number" step="any" min="0" maxlength="50" name="refill_amount"
                                   id="refill_amount" title="refill amount" >
                            <p class="description">
                                Enter refill amount. Maximum Amount <b>1000000000</b>.
                            </p>
                        </td>
                        <td>
                        </td>
                    </tr>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Refill message</th>
                        <td>
                            <input type="text" maxlength="50" name="refill_message" id="refill_message"
                                   title="refill message" size="50">
                            <p class="description">
                                Enter refill message(maximum 50 characters).
                            </p>
                        </td>
                        <td>
                        </td>
                    </tr>

                    <tr class="user-rich-editing-wrap">
                        <th scope="row"><input class="button button-primary button-large" type="submit"
                                               name="save_refill_options" value="Add credit"></th>
                        <td>
                            <a class="button button-primary button-large"
                               href="<?php echo admin_url() . 'admin.php?page=freelinguist-admin-refill-history'; ?>"
                            >
                                Refill History</a>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </form>
        </div>
    </div>
    <?php
}