<?php
/*
 * Plugin Name: WithdralRequest Table
 * Description: WithdralRequest_Wp_List_Table
 * Plugin URI: http://www.paulund.co,
 * Author: Lakhvidner
 * Author URI: http://www.Lakhvidner.com
 * Version: 1.0
 */

/*
    * current-php-code 2021-Jan-8
    * input-sanitized : add_request_withdrawal,add_withdrawal_req, filter_by_date_from,filter_by_date_to,findbyid,findbystatus, trans
    * current-wp-template:  admin-screen  for processing withdraw requests
*/

$add_request_withdrawal = FLInput::get('add_request_withdrawal');
$add_new_withdrawal_request = FLInput::get('add_withdrawal_req');

$filter_by_date_from = FLInput::get('filter_by_date_from');
$filter_by_date_to = FLInput::get('filter_by_date_to');
$find_by_id = FLInput::get('findbyid');
$find_by_status = FLInput::get('findbystatus');
$transaction_post_id = (int)FLInput::get('trans');



/**
 * Paulund_Wp_List_Table class will create the page to load the table
 */
class AdminPageWithdrawalRequest
{
    public $parent_slug = null;
    public $position = null;

    const PAGE_STUB = 'freelinguist-admin-widthdrawls';

    public static $my_url;
    /**
     * Constructor will create the menu item
     * @param string $parent_slug
     * @param int $position
     */
    public function __construct($parent_slug = null,$position = null)
    {
        $this->parent_slug = $parent_slug;
        $this->position = $position;
        add_action('admin_menu', array($this, 'add_menu_WithdralRequest_list_table_page'));
    }

    /**
     * Menu item will allow us to load the page to display the table
     */
    public function add_menu_WithdralRequest_list_table_page()
    {
        $lang = FLInput::get('lang','en');
        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'Withdrawl Requests', 'Withdrawl Requests', 'manage_options',
                static::PAGE_STUB, array($this, 'list_table_page'), $this->position);
        } else {
            add_menu_page('Withdrawl Requests', 'Withdrawl Requests', 'manage_options',
                static::PAGE_STUB, array($this, 'list_table_page'), 'dashicons-book-alt');
        }

        AdminPageWithdrawalRequest::$my_url = menu_page_url(static::PAGE_STUB,false);
        AdminPageWithdrawalRequest::$my_url =  add_query_arg([ 'lang' => $lang], AdminPageWithdrawalRequest::$my_url );


    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page()
    {
        global $wpdb;

        $amount = (float)FLInput::get('amount');
        $add_request_withdrawal = FLInput::get('add_request_withdrawal');
        $add_new_withdrawal_request = FLInput::get('add_withdrawal_req');
        $approve_withdraw_id = FLInput::get('approve_withdraw_id');
        $cancel_withdraw_id = FLInput::get('cancel_withdraw_id');
        $filter_by_date_from = FLInput::get('filter_by_date_from');
        $filter_by_date_to = FLInput::get('filter_by_date_to');
        $request_payment_notify = FLInput::get('request_payment_notify');

        $send_email_to = FLInput::get('send_email_to');
        $transaction_post_id = (int)FLInput::get('trans');
        $transactionReason = FLInput::get('transactionReason');

        $user_email = FLInput::get('user_email_is','',FLInput::YES_I_WANT_CONVESION,
            FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);
        ?>
        <div class="wrap">

            <div id="icon-users" class="icon32"></div>
            <?php
            if ($add_request_withdrawal && $add_request_withdrawal == true) {
                if ($user_email &&
                    $transactionReason &&
                    $amount &&
                    $request_payment_notify
                ) {
                    $user = get_user_by('email', $user_email);

                    if (!empty($user)) {
                        $user_id = $user->ID;

                        $current_user = wp_get_current_user();
                        if ( in_array('administrator',$current_user->roles) || in_array('administrator_for_client',$current_user->roles)) {
                            $author__in = array();
                        } else {
                            $author__in = getReportedUserByUserId();
                        }
                        if (empty($author__in) || in_array($user_id, $author__in)) {
                            $total_user_balance = get_user_meta($user_id, 'total_user_balance', true);

                            if ($total_user_balance >= $amount) {
                                $payment_notify = $request_payment_notify;

                                $updated_amount = get_user_meta($user_id, 'total_user_balance', true) - amount_format($amount);
                                update_user_meta($user_id, 'total_user_balance', $updated_amount);
                                //transaction_updated($post_title,$current_user_id,$bonus_tips,$reason,$type,$job_id);

                                $transaction_id = transaction_updated('Withdrawl Amount: $' . $amount . ' ' . $payment_notify,
                                    $user_id, $amount, $transactionReason,
                                    FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_WITHDRAW],
                                    false,FLTransactionPost::TRANSACTION_PENDING);

                                update_post_meta($transaction_id, FLTransactionLookup::META_KEY_REQUEST_PAYMENT_NOTIFY, $request_payment_notify);

                                echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                        <p><strong>Updated.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                            } else {
                                echo '  <div class="updated settings-error notice error is-dismissible" id="setting-error-settings_updated">
                        <p><strong>Insufficent Balance.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                            }
                        } else {
                            echo '  <div class="updated settings-error error notice is-dismissible" id="setting-error-settings_updated">
                <p><strong>You  are unauthorized user for this request.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

                        }
                    } else {
                        echo '  <div class="updated settings-error error notice is-dismissible" id="setting-error-settings_updated">
                <p><strong>Email id not exist.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

                    }
                } else {
                    echo '  <div class="updated settings-error error notice is-dismissible" id="setting-error-settings_updated">
                <p><strong>All Field are required.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                }
            }

            if (FLInput::get('send_email_to_button')) {
                $message_content = FLInput::get('send_email','',FLInput::YES_I_WANT_CONVESION,
                                        FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);
                $title = FLInput::get('title','',FLInput::YES_I_WANT_CONVESION,
                                        FLInput::NO_DB_ESCAPING, FLInput::YES_I_WANT_HTML_ENTITIES);
                $user = FLInput::get('user_id');
                $user_detail = get_userdata($user);

                $wpdb->insert(
                    'wp_message_email_history',
                    array(
                        'sender_id' => get_current_user_id(),
                        'receiver_id' => $user,
                        'title' => $title,
                        'content' => $message_content,
                        'type' => CASHIER_SEND_EMAIL_TO_USER,           // cashier_send_email_to_user
                        'created_date' => current_time('mysql'),
                        'modified_date' => current_time('mysql'),
                    )
                );

                send_custom_message($user_detail->user_email, $title, $message_content);
                echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                            <p><strong>Sent.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

            }

            if ($approve_withdraw_id) {
                $trans_id = $approve_withdraw_id;
                $check_withdraw_status = get_post_meta($trans_id, FLTransactionLookup::META_KEY_TRANSACTION_WITHDRAW_STATUS, true);
                if (
                        ($check_withdraw_status != FLTransactionLookup::WITHDRAW_STATUS_VALUES[FLTransactionLookup::WITHDRAW_STATUS_COMPLETED]) &&
                        ($check_withdraw_status != FLTransactionLookup::WITHDRAW_STATUS_VALUES[FLTransactionLookup::WITHDRAW_STATUS_CANCELED])

                ) {
                    $transaction_withdraw_status = FLTransactionLookup::WITHDRAW_STATUS_VALUES[FLTransactionLookup::WITHDRAW_STATUS_COMPLETED];
                    // Add values of $events_meta as custom fields
                    update_post_meta($trans_id, FLTransactionLookup::META_KEY_TRANSACTION_WITHDRAW_STATUS, $transaction_withdraw_status);
                    wp_update_post(['ID'    =>  $trans_id, 'post_status'   => FLTransactionPost::TRANSACTION_COMPLETE]);
                    update_post_meta($trans_id, FLTransactionLookup::META_KEY_WITHDRAW_APPROVED_BY, get_current_user_id());

                    echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                        <p><strong>Updated.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                }

            }
            if ($cancel_withdraw_id) {
                $trans_id = $cancel_withdraw_id;
                $check_withdraw_status = get_post_meta($trans_id, FLTransactionLookup::META_KEY_TRANSACTION_WITHDRAW_STATUS, true);
                if (
                        ($check_withdraw_status != FLTransactionLookup::WITHDRAW_STATUS_VALUES[ FLTransactionLookup::WITHDRAW_STATUS_COMPLETED]) &&
                        ($check_withdraw_status != FLTransactionLookup::WITHDRAW_STATUS_VALUES[ FLTransactionLookup::WITHDRAW_STATUS_CANCELED])
                )
                {
                    //echo "<pre>"; print_r(get_post($trans_id)); exit;
                    $transaction_withdraw_status = FLTransactionLookup::WITHDRAW_STATUS_VALUES[ FLTransactionLookup::WITHDRAW_STATUS_CANCELED];
                    $trans_detail = get_post($trans_id);
                    if (isset($trans_detail->post_author)) {

                        $withdraw_message = FLInput::get('cancel_message');
                        if ($withdraw_message) {
                            update_post_meta($trans_id, FLTransactionLookup::META_KEY_WITHDRAW_CANCEL_MESSAGE, $withdraw_message);
                        }


                        $trans_amount = get_post_meta($trans_id, FLTransactionLookup::META_KEY_TRANSACTION_AMOUNT, true);
                        if (empty($trans_amount)) {$trans_amount = 0;}
                        $starting_user_balance = get_user_meta($trans_detail->post_author, 'total_user_balance', true);
                        if (empty($starting_user_balance)) {$starting_user_balance = 0;}
                        $updated_amount = amount_format($starting_user_balance + $trans_amount);
                        update_user_meta($trans_detail->post_author, 'total_user_balance', $updated_amount);

                        update_post_meta($trans_id, FLTransactionLookup::META_KEY_TRANSACTION_AMOUNT, 0.00);
                        update_post_meta($trans_id, FLTransactionLookup::META_KEY_TRANSACTION_WITHDRAW_STATUS, $transaction_withdraw_status);
                        wp_update_post(['ID'    =>  $trans_id, 'post_status'   => FLTransactionPost::TRANSACTION_FAILED]);
                        update_post_meta($trans_id, FLTransactionLookup::META_KEY_WITHDRAW_APPROVED_BY, get_current_user_id());
                    }
                    echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                        <p><strong>Updated.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                }
            }


            // edit withdrawal request
            $get_post = $transaction_post_id ? get_post($transaction_post_id) : '';
            if ($transaction_post_id) { ?>
                <?php
                $trans_id = $transaction_post_id;
                if (FLInput::get('update_request_withdrawal')) {
                    if (get_post_meta($trans_id, FLTransactionLookup::META_KEY_TRANSACTION_WITHDRAW_STATUS, true) ==
                        FLTransactionLookup::WITHDRAW_STATUS_VALUES[FLTransactionLookup::WITHDRAW_STATUS_PENDING]
                    )
                    {
                        $transaction_withdraw_status = FLInput::get('_transactionWithdrawStatus');
                        // Add values of $events_meta as custom fields
                        $trans_detail = get_post($trans_id);
                        if (isset($trans_detail->post_author)) {
                            {
                                if ($transaction_withdraw_status == FLTransactionLookup::WITHDRAW_STATUS_VALUES[FLTransactionLookup::WITHDRAW_STATUS_CANCELED]) {

                                    wp_update_post(['ID'    =>  $trans_id, 'post_status'   => FLTransactionPost::TRANSACTION_FAILED]);

                                    $withdraw_message = FLInput::get('cancel_message');
                                    if ($withdraw_message) {
                                        update_post_meta($trans_id, FLTransactionLookup::META_KEY_WITHDRAW_CANCEL_MESSAGE, $withdraw_message);
                                    }
                                    $trans_amount = get_post_meta($trans_id, FLTransactionLookup::META_KEY_TRANSACTION_AMOUNT, true);
                                    if (empty($trans_amount)) {$trans_amount = 0;}
                                    $starting_user_balance = get_user_meta($trans_detail->post_author, 'total_user_balance', true);
                                    if (empty($starting_user_balance)) {$starting_user_balance = 0;}
                                    $updated_amount = amount_format($starting_user_balance + $trans_amount);

                                    update_user_meta($trans_detail->post_author, 'total_user_balance', $updated_amount);

                                    update_post_meta($trans_id, FLTransactionLookup::META_KEY_TRANSACTION_AMOUNT, 0.00);
                                    update_post_meta($trans_id, FLTransactionLookup::META_KEY_TRANSACTION_WITHDRAW_STATUS, $transaction_withdraw_status);
                                    update_post_meta($trans_id, FLTransactionLookup::META_KEY_WITHDRAW_APPROVED_BY, get_current_user_id());
                                } elseif ($transaction_withdraw_status == FLTransactionLookup::WITHDRAW_STATUS_VALUES[FLTransactionLookup::WITHDRAW_STATUS_PENDING]) {

                                    wp_update_post(['ID'    =>  $trans_id, 'post_status'   => FLTransactionPost::TRANSACTION_PENDING]);
                                    update_post_meta($trans_id, FLTransactionLookup::META_KEY_TRANSACTION_WITHDRAW_STATUS, $transaction_withdraw_status);
                                    update_post_meta($trans_id, FLTransactionLookup::META_KEY_WITHDRAW_APPROVED_BY, get_current_user_id());
                                }
                            }

                        }
                        echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                    <p><strong>Settings saved.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

                    }
                }
                ?>
                <div class="wrap stuffbox">
                    <div class="inside">
                        <span class="bold-and-blocking larger-text">Edit request </span>
                        <hr>
                        <form name="withdrawal_f" method="post" id="withdrawal_f"
                              action="<?= AdminPageWithdrawalRequest::$my_url . '&trans=' . $trans_id; ?>">
                            <table class="form-table">
                                <tbody>
                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Title</th>
                                    <td>
                                        <?php echo $get_post->post_title; ?>
                                    </td>
                                </tr>

                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Transaction Amount</th>
                                    <td>
                                        <?php echo get_post_meta($get_post->ID, FLTransactionLookup::META_KEY_TRANSACTION_AMOUNT, true); ?>
                                    </td>
                                </tr>

                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Transaction Reason</th>
                                    <td>
                                        <?php echo get_post_meta($get_post->ID, FLTransactionLookup::META_KEY_TRANSACTION_REASON, true); ?>
                                    </td>
                                </tr>

                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Transaction Type</th>
                                    <td>
                                        <?php echo get_post_meta($get_post->ID, FLTransactionLookup::META_KEY_TRANSACTION_TYPE, true); ?>
                                    </td>
                                </tr>

                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Author</th>
                                    <td>
                                        <?php echo $get_post->post_author; ?>
                                    </td>
                                </tr>

                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Transaction Withdraw Status</th>
                                    <td>
                                        <?php
                                        $value = get_post_meta($get_post->ID, FLTransactionLookup::META_KEY_TRANSACTION_WITHDRAW_STATUS, true);
                                        $option_keys = [
                                                [
                                                    'words' => 'Pending',
                                                    'value' => FLTransactionLookup::WITHDRAW_STATUS_VALUES[FLTransactionLookup::WITHDRAW_STATUS_PENDING]
                                                ],
                                                [
                                                    'words' => 'Canceled',
                                                    'value' => FLTransactionLookup::WITHDRAW_STATUS_VALUES[FLTransactionLookup::WITHDRAW_STATUS_CANCELED]
                                                ],
                                                [
                                                    'words' => 'Completed',
                                                    'value' => FLTransactionLookup::WITHDRAW_STATUS_VALUES[FLTransactionLookup::WITHDRAW_STATUS_COMPLETED]
                                                ]
                                        ];

                                        ?>
                                        <select name="_transactionWithdrawStatus" class="widefat" title="Withdraw Status" autocomplete="off">
                                        <?php foreach ($option_keys as $option_node) {
                                                $option_value = $option_node['value'];
                                                $option_words = $option_node['words'];
                                                $option_selected_attribute = '';
                                                if ($value === $option_value) {$option_selected_attribute = 'selected';}
                                                ?>
                                                <option value="<?=$option_value?>" <?= $option_selected_attribute ?>> <?= $option_words ?></option>
                                        <?php } ?>
                                        </select>
                                    </td>
                                </tr>

                                <tr class="user-rich-editing-wrap fl-admin-withdraw-cancel-message" style="display: none">
                                    <th scope="row">Transaction Withdraw Status</th>
                                    <td>
                                        <textarea name = "cancel_message"
                                                    title="Cancellation Message"
                                                    autocomplete="off"
                                                    class="form-control"
                                        ></textarea>
                                    </td>
                                </tr>

                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">
                                        <?php if (get_post_meta($get_post->ID, FLTransactionLookup::META_KEY_TRANSACTION_WITHDRAW_STATUS, true) ==
                                            FLTransactionLookup::WITHDRAW_STATUS_VALUES[FLTransactionLookup::WITHDRAW_STATUS_PENDING])
                                        { ?>
                                            <input type="submit" value="Update" name="update_request_withdrawal"
                                                   class="button button-primary">
                                        <?php } else { ?>
                                            <input type="button" value="Updated" name="" class="button button-primary">
                                        <?php } ?>
                                    </th>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table>

                        </form>
                    </div>
                </div>
                <hr>
            <?php } ?>


            <?php if ($add_new_withdrawal_request) { ?>
                <div class="wrap stuffbox">
                    <div class="inside">
                        <span class="bold-and-blocking larger-text">ADD Withdrawl request </span>
                        <hr>
                        <form name="add_withdrawal_f" method="post" id="add_withdrawal_f"
                              action="<?= AdminPageWithdrawalRequest::$my_url . '&add_withdrawal_req=true'; ?>">
                            <table class="form-table">
                                <tbody>
                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Email</th>
                                    <td>
                                        <input type="text" title="User Email" name="user_email_is" id="user_email_is">

                                        <script type="text/javascript">
                                            jQuery(function () {
                                                jQuery("#user_email_is").autocomplete({
                                                    source: '<?php echo get_site_url();?>/?action=get_linguist_list_autocomplete',
                                                    minLength: 1
                                                });
                                            });

                                        </script>
                                        <p class="description">
                                            Email address.
                                        </p>
                                    </td>
                                    <td></td>
                                </tr>

                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Withdrawl Amount</th>
                                    <td>
                                        <input title="amount" step="any" type="number" class="form-control valid" name="amount"
                                               maxlength="1000" aria-required="true" aria-invalid="false">
                                    </td>
                                    <td></td>
                                </tr>

                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Payment Method</th>
                                    <td>
                                        <div class="radio-box-sec">
                                            <input title="PayPal" type="radio" name="request_payment_notify"
                                                   id="request_payment_notify" class="request_payment_notify" checked=""
                                                   value="<?= FLTransactionLookup::REQUEST_PAYMENT_VALUES[FLTransactionLookup::REQUEST_PAYMENT_NOTIFY_PAYPAL] ?>">
                                            <label>Paypal</label>
                                            <input title="AliPay" type="radio" name="request_payment_notify"
                                                   id="request_payment_notify" class="request_payment_notify"
                                                   value="<?= FLTransactionLookup::REQUEST_PAYMENT_VALUES[FLTransactionLookup::REQUEST_PAYMENT_NOTIFY_ALIPAY] ?>">
                                            <label>AliPay</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <label id="account_alipay_paypal"></label>
                                        </div>
                                    </td>
                                    <td>
                                    </td>
                                </tr>

                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Transaction note</th>
                                    <td>
                                        <div class="radio-box-sec">
                                            <input title="reason" type="text" name="transactionReason" id="transactionReason"
                                                   class="form-control">
                                        </div>
                                    </td>
                                    <td>
                                    </td>
                                </tr>

                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">
                                        <input type="submit" value="Add Request" name="add_request_withdrawal"
                                               class="button button-primary">
                                    </th>
                                    <td></td>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table>

                        </form>
                    </div>
                </div>
            <?php } ?>
            <?php if ($send_email_to) { ?>
                <div class="wrap stuffbox">
                    <div class="inside">
                        <span class="bold-and-blocking larger-text">Send email </span>
                        <hr>
                        <form name="send_template_f" method="post" id="send_template_f" action="#">
                            <table class="form-table">
                                <tbody>
                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Send Email To</th>
                                    <td>  <?php
                                        $user = $send_email_to;
                                        $user_detail = get_userdata($user);
                                        echo $user_detail->user_email; ?>
                                        <input type="hidden" value="<?php echo $user; ?>" name="user_id">
                                    </td>
                                </tr>
                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Title</th>
                                    <td><input title="title" style="width:100%;" type="text" name="title" value="" id="title"></td>
                                </tr>
                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Content</th>
                                    <?php $content = ''; ?>
                                    <td>
                                        <?php wp_editor($content, $editor_id = 'send_email', $settings = array()); ?>
                                    </td>
                                </tr>

                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">
                                        <input type="submit" value="Send email" name="send_email_to_button"
                                               class="button button-primary">
                                    </th>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table>

                        </form>
                    </div>
                </div>
                <?php
            } ?>
            <span class="bold-and-blocking larger-text">Withdrawl Request</span>
            <ul class="subsubsub"></ul>
            <select name="withdrawal_status" id="withdrawal_status" style="float:right" title="WidthDraw Status">
                <option value="">Select Status</option>
                <option value="<?= FLTransactionLookup::WITHDRAW_STATUS_VALUES[FLTransactionLookup::WITHDRAW_STATUS_PENDING] ?>">Pending</option>
                <option value="<?= FLTransactionLookup::WITHDRAW_STATUS_VALUES[FLTransactionLookup::WITHDRAW_STATUS_COMPLETED] ?>">Completed</option>
                <option value="<?= FLTransactionLookup::WITHDRAW_STATUS_VALUES[FLTransactionLookup::WITHDRAW_STATUS_CANCELED] ?>">Canceled</option>
            </select>


            <div class="data_filter" style="float:right">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="text" readonly id="dt1"
                       value="<?= $filter_by_date_from ?>"
                       placeholder="From Date" name="dt1">
                <input type="text" readonly id="dt2"
                       value="<?= $filter_by_date_to ?>"
                       placeholder="To Date" name="dt2">
                <input type="button" class="button" id="filter_by_date" name="filter_by_date" value="Filter">
                <script>
                    jQuery(function ($) {
                        $(function () {
                            $("#dt1").datepicker({
                                numberOfMonths: 1,
                                dateFormat: 'yy-mm-dd',
                                onSelect: function (selected) {
                                    var dt = new Date(selected);
                                    dt.setDate(dt.getDate() + 1);
                                    $("#dt2").datepicker("option", "minDate", dt);
                                    Math.floor(new Date(selected).getTime() / 1000)
                                }
                            });
                            $("#dt2").datepicker({
                                numberOfMonths: 1,
                                dateFormat: 'yy-mm-dd',
                                onSelect: function (selected) {
                                    var dt = new Date(selected);
                                    dt.setDate(dt.getDate() - 1);
                                    $("#dt1").datepicker("option", "maxDate", dt);
                                }
                            });
                        });
                    });
                </script>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </div>
            <p class="search-box">
                <input class="enhanced-text" type="search" placeholder="Search by transaction id" id="r-search-input"
                       name="s" value="">
                <input type="submit" id="search-u"  class="button large-text" value="Search">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </p>

            <script>
                jQuery(function ($) {
                    jQuery('#search-u').click(function () {
                        var inputURL = jQuery('#r-search-input').val();
                        var url = '<?= AdminPageWithdrawalRequest::$my_url ?>' + '&findbyid=' + inputURL;
                        window.location.href = url;
                        return false;
                    });
                    jQuery("#withdrawal_status").change(function () {
                        var valueis = this.value;
                        if (valueis !== '') {
                            var url = '<?= AdminPageWithdrawalRequest::$my_url ?>' + '&findbystatus=' + valueis;
                            window.location.href = url;
                        }
                        return false;
                    });
                    jQuery('#filter_by_date').click(function () {
                        var filter_by_date_from = jQuery('#dt1').val();
                        var filter_by_date_to = jQuery('#dt2').val();
                        var url = '<?= AdminPageWithdrawalRequest::$my_url ?>' + '&filter_by_date_from=' + filter_by_date_from + '&filter_by_date_to=' + filter_by_date_to;
                        window.location.href = url;
                        return false;
                    });

                    jQuery("select[name='_transactionWithdrawStatus']").change(function() {
                       let dat = $(this);
                       let val = dat.val();
                       if (val === '<?= FLTransactionLookup::WITHDRAW_STATUS_VALUES[FLTransactionLookup::WITHDRAW_STATUS_CANCELED]?>') {
                           $('.fl-admin-withdraw-cancel-message').show();
                       } else {
                           $('.fl-admin-withdraw-cancel-message').hide();
                       }
                    });

                });
            </script>
            <?php
            $WithdralRequestListTable = new WithdralRequest_List_Table();
            $WithdralRequestListTable->prepare_items();
            $WithdralRequestListTable->display(); ?>
            <a class="button button-primary"
               href="<?= AdminPageWithdrawalRequest::$my_url . '&add_withdrawal_req=true'; ?>"
            >
                Add withdrawal Request
            </a>

        </div>
        <?php
    }
}


// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


/**
 * Create a new table class that will extend the WP_List_Table
 */
class WithdralRequest_List_Table extends WP_List_Table
{
    var $per_page;
    var $current_page;

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->per_page = 10;
        $this->current_page = $this->get_pagenum();
        $data = $this->table_data();
        usort($data, array(&$this, 'sort_data'));
        $totalItems = $this->get_total();
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $this->per_page
        ));
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    protected function get_total() {
        $search = $this->get_search_object();
        $count = FLTransactionLookup::get_rows('none',$this->per_page,$this->current_page,'by_date_dsc',
            [FLTransactionLookup::TRANSACTION_TYPE_WITHDRAW],[],$search,false,true);

        return $count;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'transaction_id' => 'Transaction ID',
            'account_type' => 'Paypal or Alipay Account',
            'transactionAmount' => 'Withddrawl amount',
            'taxForm' => 'Tax Form',
            'delivery_amount' => 'Delivery Amount',
            'withdrawal_message' => 'Withdrawal Note',
            'email' => 'Send Email',
            'user_name' => 'User Name',
            'withdrawal_status' => 'Status',
            'withdraw_cancel_message' => 'Admin Note',
            'withdraw_approved_by' => 'Approved By',
            'date' => 'Date',
        );
        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        //return array();
        return array('date' => array('date', true));
    }

    protected function get_search_object() {
        $filter_by_date_from = FLInput::get('filter_by_date_from');
        $filter_by_date_to = FLInput::get('filter_by_date_to');
        $find_by_id = FLInput::get('findbyid');
        $find_by_status = FLInput::get('findbystatus');

        $search = [];
        if ($find_by_id) {
            $search['txn_like']=$find_by_id;
        } elseif ($find_by_status) {
            $search['withdraw_status']=[$find_by_status];
        } elseif ($filter_by_date_from && $filter_by_date_to) {
            $search['post_created_after'] = strtotime($filter_by_date_from) -1;
            $search['post_created_before'] = strtotime($filter_by_date_to) +1;
        } elseif ($filter_by_date_from) {
            $search['post_created_after'] = strtotime($filter_by_date_from) -1;
        } elseif ($filter_by_date_to) {
            $search['post_created_before'] = strtotime($filter_by_date_to) +1;
        }
        return $search;
    }

    /**
     * Get the table data
     *
     * @return array
     */
    private function table_data()
    {
        $find_by_status = FLInput::get('findbystatus');
        $usd_formatter = numfmt_create( 'en_US', NumberFormatter::CURRENCY );


        $search = $this->get_search_object();

        /**
         * @var FLTransactionLookup[] $newfangled_rows
         */
        $newfangled_rows = FLTransactionLookup::get_rows('none',$this->per_page,$this->current_page,'by_date_dsc',
            [FLTransactionLookup::TRANSACTION_TYPE_WITHDRAW],[],$search,false,false);
        $data = array();
        $withdrawal_fee_base = get_option('withdrawal_fee_base',0);
        if (empty($withdrawal_fee_base)) {$withdrawal_fee_base = 0;}
        $withdrawal_fee_percentage = get_option('withdrawal_fee_percentage',0);
        if (empty($withdrawal_fee_percentage)) {$withdrawal_fee_percentage = 0;}
        foreach ($newfangled_rows as $post) {
            $data_index = array();
            $id_is = $post->post_id;
            $post_author_is = $post->user_id;
            $post_date_ts = $post->post_created_at_ts;

            $url = AdminPageWithdrawalRequest::$my_url . '&trans=' . $id_is;
            $data_index['ID'] = $id_is;
            $data_index['transaction_id'] = '<a href="' . $url . '">' . $post->txn . '</a>';
            $data_index['transactionType'] = FLTransactionLookup::TRANSACTION_TYPE_VALUES[$post->transaction_type];
            $data_index['transactionAmount'] = $usd_formatter->formatCurrency($post->transaction_amount,'USD');

            $delivery_amount = $post->transaction_amount;
            if ($delivery_amount > 0) {
                $delivery_fee = floatval($withdrawal_fee_base + $withdrawal_fee_percentage / 100 * $delivery_amount);
                $data_index['delivery_amount'] = $usd_formatter->formatCurrency($delivery_amount - $delivery_fee,'USD');
            } else {
                $data_index['delivery_amount'] = $usd_formatter->formatCurrency($post->transaction_amount,'USD');
            }

            $tax_arr = get_the_author_meta(FreelinguistUserHelper::META_KEY_NAME_TAX_FORM, $post_author_is);
            if (empty($tax_arr)) {
                $data_index['taxForm'] = 'Not exist';
            } else {
                $tax_arr = explode('/', $tax_arr);
                $data_index['taxForm'] = '<a href="' . get_site_url() . '?action=download_tax_form&user_id=' . $post_author_is . '&lang=en" class="download-taxform large-text">' . $tax_arr[count($tax_arr) - 1] . '</a>';
            }

            $user_is = get_userdata($post_author_is);
            $send_email_url = AdminPageWithdrawalRequest::$my_url . '&send_email_to=' . $post_author_is;
            $data_index['email'] = '<a href="' . $send_email_url . '">' . $user_is->user_email . '</a>';

            if (empty(get_user_meta($user_is->ID, 'paypal_account', true))) {
                $paypal_account = 'Not Exist';
            } else {
                $paypal_account = get_user_meta($user_is->ID, 'paypal_account', true);
            }

            if (empty(get_user_meta($user_is->ID, 'alipay_account', true))) {
                $alipay_account = 'Not Exist';
            } else {
                $alipay_account = get_user_meta($user_is->ID, 'alipay_account', true);
            }


            if (get_post_meta($id_is, FLTransactionLookup::META_KEY_REQUEST_PAYMENT_NOTIFY, true) ==
                FLTransactionLookup::REQUEST_PAYMENT_VALUES[FLTransactionLookup::REQUEST_PAYMENT_NOTIFY_PAYPAL] )
            {
                $data_index['account_type'] = 'P: ' . $paypal_account;
            } else {
                $data_index['account_type'] = 'A: ' . $alipay_account;
            }
            $data_index['user_name'] = $user_is->user_login;
            $data_index['date'] = $post_date_ts;
            //
            if ($post->withdraw_status == FLTransactionLookup::WITHDRAW_STATUS_PENDING) {
                if ($find_by_status) {
                    $withdraw_url = AdminPageWithdrawalRequest::$my_url . '&findbystatus=' . $find_by_status . '&approve_withdraw_id=' . $id_is ;
                    $withdraw_url_Reject = AdminPageWithdrawalRequest::$my_url . '&findbystatus=' . $find_by_status . '&cancel_withdraw_id=' . $id_is;
                } else {
                    $withdraw_url = AdminPageWithdrawalRequest::$my_url . '&approve_withdraw_id=' . $id_is ;
                    $withdraw_url_Reject = AdminPageWithdrawalRequest::$my_url . '&cancel_withdraw_id=' . $id_is ;
                }
                $transactionWithdrawStatus = '<span>Pending</span>';
                $transactionWithdrawStatus .= '<div class="row-actions">';
                $transactionWithdrawStatus .= '<span class="edit"><a href="' . $withdraw_url . '">Approve</a> </span>&nbsp;&nbsp;&nbsp;&nbsp;';
                $transactionWithdrawStatus .= '<span class="cancel"><a href="' . $withdraw_url_Reject . '">Reject</a> </span>';
                $transactionWithdrawStatus .= '</div>';
            } else if ($post->withdraw_status == FLTransactionLookup::WITHDRAW_STATUS_CANCELED) {
                $transactionWithdrawStatus = '<span>Canceled</span>';
                $transactionWithdrawStatus .= '<div class="row-actions">';
                $transactionWithdrawStatus .= '<span class="approved"><a href="#">Canceled</a>  </span>';
                $transactionWithdrawStatus .= '</div>';
            } else if ($post->withdraw_status == FLTransactionLookup::WITHDRAW_STATUS_COMPLETED) {
                $transactionWithdrawStatus = '<span>Completed</span>';
                $transactionWithdrawStatus .= '<div class="row-actions">';
                $transactionWithdrawStatus .= '<span class="approved"><a href="#">Aprroved</a>  </span>';
                $transactionWithdrawStatus .= '</div>';
            } else {
                $transactionWithdrawStatus = '<span>Unknown Status ('.$post->withdraw_status.')</span>';
                $transactionWithdrawStatus .= '<div class="row-actions">';
                $transactionWithdrawStatus .= '<span class="approved"><a href="#">Aprroved</a>  </span>';
                $transactionWithdrawStatus .= '</div>';
            }
            $data_index['withdrawal_status'] = $transactionWithdrawStatus;


            if (empty($post->withdraw_approved_by)) {
                $data_index['withdraw_approved_by'] = '--';
            } else {
                $withdraw_approved_by = get_userdata($post->withdraw_approved_by);
                $data_index['withdraw_approved_by'] = $withdraw_approved_by->user_login;
            }

            $data_index['withdrawal_message'] = $post->withdrawal_message;
            $data_index['withdraw_cancel_message'] = $post->withdraw_cancel_message;
            $data[] = $data_index;
        }
        return $data;

    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  array $item Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'transaction_id':
            case 'account_type':
            case 'transactionAmount':
            case 'taxForm':
            case 'delivery_amount':
            case 'withdrawal_message':
            case 'withdraw_cancel_message':
            case 'email':
            case 'user_name':
            case 'withdrawal_status':
            case 'withdraw_approved_by':

                return $item[$column_name];

            case 'date':
                return '<span class="a-timestamp-full-date-time" data-ts="'.$item[$column_name].'"></span>';
            default:
                return print_r($item, true);
        }
    }

    /**
     * Allows you to sort the data by the variables set in the request
     * @param  array $a
     * @param array $b
     * @return Mixed
     */
    private function sort_data($a, $b)
    {
        $orderby = FLInput::get('orderby','date');
        $order = FLInput::get('order','DESC');

        $result = strcmp($a[$orderby], $b[$orderby]);
        if ($order === 'asc') {
            return $result;
        }
        return -$result;
    }
}

?>