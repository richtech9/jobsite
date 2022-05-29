<?php


/**
 *  class will create the page to load the table
 */

/*
    * current-php-code 2021-Jan-11
    * input-sanitized :
    * current-wp-template:  admin-screen  for contest cancellation
*/
class AdminPageCancelContestRequest
{
    const THIS_ADMIN_STUB_NAME = 'freelinguist-admin-cancel-contest';

    /*
     * Names of the columns, Text is the value, the code name is the key
     * code-notes change column headers of approve and reject, to resume and cancel
     */
    const COLUMNS = [
       'cancel_id' => ['text'=>'#','is_sortable'=>true,'order_as'=>'cancel_id'], // id of request table
       'contest_id' => ['text'=>'Contest #','is_sortable'=>true,'order_as'=>'contest_id'], // id of the contest, and link to its page
       'customer' => ['text'=>'Customer','is_sortable'=>true,'order_as'=>'customer_email'], // email of the customer, and link to its profile page

        'extended' => ['text'=>'Extend','is_sortable'=>true,'order_as'=>'days_extended'], //  the number of days extended, in text if set, or an input box to enter number

        'approved' => ['text'=>'Cancel Job with Full Customer Refund','is_sortable'=>true,'order_as'=>'has_been_approved'], // either a link to approve it, or if action already taken, or a thumbs up if approved
        'rejected' => ['text'=>'Resume Job with Days Extended','is_sortable'=>true,'order_as'=>'has_been_approved'], // either a link to reject it, or if action already taken, or a thumbs down if already approved




       'partialed' => ['text'=>'Resume Job with Partial Prize % going back to customer','is_sortable'=>true,'order_as'=>'percentage'], // either a drop-down for percentages , or if action already taken, then what the percentage is in text
       'opened' => ['text'=>'Open Date','is_sortable'=>true,'order_as'=>'opened_ts'], // the date/time of the ticket
       'original_deadline' => ['text'=>'Deadline Date','is_sortable'=>true,'order_as'=>'original_deadline_ts'], // the date/time of the deadline
       'processed' => ['text'=>'Process Date','is_sortable'=>true,'order_as'=>'processed_ts'], // the date/time action was taken
       'posted_by' => ['text'=>'Mediator','is_sortable'=>true,'order_as'=>'posted_by_email']  // who processed it (email with profile link)
    ];

    const DEFAULT_PAGE_SIZE = 100;

    const REFERRER_NAME = 'fl_admin_contest_cancel';

    const DEBUG_MODE = true;

    const OUTPUT_LOG_TO_JS_CONSOLE = true;

    //code-notes DEFAULT_EXTEND_AMOUNT sets the default number of days the contest can be extended, it can be overridden by the WP option of _default_contest_extend_amount
    const DEFAULT_EXTEND_AMOUNT = 7;
    
    //code-notes DEFAULT_PERCENT_AMOUNT sets the default percentage of the partial refund going back to the client, it can be overridden by the WP option of _default_contest_percent_amount
    const DEFAULT_PERCENT_AMOUNT = 100;

    static public $languge_verb = null;

    static public $safety_nonce = null;

    public $parent_slug = null;
    public $position = null;
    /**
     * Constructor will create the menu item
     * @param string $parent_slug
     * @param int $position
     */
    public function __construct($parent_slug = null,$position = null)
    {
        $this->parent_slug = $parent_slug;
        $this->position = $position;
        static::$languge_verb = isset($_REQUEST['lang']) ? 'lang='.$_REQUEST['lang'] . '&' : '';
        add_action('admin_menu', array($this, 'add_menu_cancel_request_list_table_page'));
    }

    /**
     * Menu item will allow us to load the page to display the table

     */
    public function add_menu_cancel_request_list_table_page()
    {
        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'Cancel Contest Requests',
                'Contest Cancellation',
                'manage_options',
                static::THIS_ADMIN_STUB_NAME,
                array($this, 'list_table_page'),
                $this->position);
        } else {
            add_menu_page('Cancel Contest Requests',
                'Contest Cancellation',
                'manage_options',
                static::THIS_ADMIN_STUB_NAME,
                array($this, 'list_table_page'),
                'dashicons-list-view');
        }

    }


    /**
     * Display the list table page
     *
     * @return Void
     */

    public function list_table_page()
    {
        $error_message = '';
        $log = [];
        try {
            $contest_id = null;
            if (is_array($_POST) && count($_POST)) {
                $referrer_ok = check_ajax_referer(static::REFERRER_NAME,false,false);
                if (!$referrer_ok) {
                    throw new RuntimeException("Please Refresh this page to be able to submit data");
                }

                if (!isset($_POST['contest_id'])) {
                    throw new RuntimeException("Contest id is not set in form");
                }
                $contest_id = (int)$_POST['contest_id'];
            }

            if (!current_user_can( 'manage_options' ) ) {
                throw new RuntimeException("Current user cannot manage options");
            }


            if (isset($_POST['undo_cancel_request'])) {
                $cancel_id_undo = (int)$_POST['undo_cancel_request'];
                FreelinguistContestCancellation::undo_cancellation($cancel_id_undo,$log);
                will_dump('undo', $log,static::DEBUG_MODE);
            }

            //code-notes there is no days_extended_submit button now, so this will not run currently
            if (isset($_POST['days_extended_submit']) && isset($_POST['days_extended'])) {
                $days_extended = $_POST['days_extended'];
                FreelinguistContestCancellation::decide_cancellation($contest_id,true,$days_extended,0,$log);
                will_dump('Job Extended while being cancelled', $log,static::DEBUG_MODE);
                //code-notes this is an invalid state now
            }

            if (isset($_POST['approve_this_submit'])) {
                //code-notes if a contest is cancelled, it is not extended
                FreelinguistContestCancellation::decide_cancellation($contest_id,true,0,100,$log);
                will_dump('Job Cancelled', $log,static::DEBUG_MODE);
            }

// this is when admin clicks "resume job" button.
            if (isset($_POST['disapprove_this_submit'])) {
                //code-notes if a contest is resumed then it is extended
                $days_extended = (int)get_option('_DEFAULT_CONTEST_EXTEND_AMOUNT',
                    AdminPageCancelContestRequest::DEFAULT_EXTEND_AMOUNT);

                if (isset($_POST['days_extended_redux'])) {
                    $maybe_days = (int)$_POST['days_extended_redux'];
                    if ($maybe_days) {
                        $days_extended = $maybe_days;
                    }
                }

                FreelinguistContestCancellation::decide_cancellation($contest_id,false,$days_extended,0,$log);
                will_dump('Job Resumed', $log,static::DEBUG_MODE);
            }

// this is when admin clicks "partial job" button.
            if (isset($_POST['percentage_partial_submit']) && isset($_POST['percentage_partial'])) {

                $days_extended = (int)get_option('_default_contest_extend_amount',
                    AdminPageCancelContestRequest::DEFAULT_EXTEND_AMOUNT);

                if (isset($_POST['days_extended_redux'])) {
                    $maybe_days = (int)$_POST['days_extended_redux'];
                    if ($maybe_days) {
                        $days_extended = $maybe_days;
                    }
                }
                $percentage = $_POST['percentage_partial'];
                FreelinguistContestCancellation::decide_cancellation($contest_id,true,$days_extended,$percentage,$log);
                will_dump('Job Partially Resumed', $log,static::DEBUG_MODE);
            }

            if (isset($_POST['new_cancel_submit']) && isset($_POST['new_contest_id'])) {
                $new_id = FreelinguistContestCancellation::create_new_cancellation($_POST['new_contest_id'], $log, false);
                will_dump('new', $log,static::DEBUG_MODE);
                will_dump('id', $new_id,static::DEBUG_MODE);
            }
        } catch (RuntimeException $e) {
            $error_message = $e->getMessage() ;
            if ($e->getCode()) {$error_message .= '['. $e->getCode() . ']';}
            will_dump('ERROR', $log,static::DEBUG_MODE);
            will_log_in_wp_log_and_js_console($log,static::OUTPUT_LOG_TO_JS_CONSOLE,true,true);
        }

        //code-notes append the cancel log for possible debugging
        if (!empty($log) && $contest_id) {
            $older_log = get_post_meta($contest_id,'cancellation_processed_log',true);
            if (!$older_log) {$older_log = [];}
            $combined_log = array_merge($log,$older_log);
            update_post_meta($contest_id, 'cancellation_processed_log', $combined_log);
            will_log_in_wp_log_and_js_console($log,static::OUTPUT_LOG_TO_JS_CONSOLE,false,false);
        }



        ?>
        <div class="wrap">
            <span class="bold-and-blocking large-text">Manage Cancel requests for contests</span>
            <hr>

            <div class="view_and_add">
                <form class="form_ca" name="view_add_form" method="post" action="">
                    <input type="text" name="new_contest_id" placeholder="Enter contest id here"
                            id="new_contest_id">
                    <input type="submit"  value="Add a New Cancel Request" name="new_cancel_submit"
                           id="new_cancel_submit">
                    <?= CancelRequest_List_Table::generate_form_goodies(null) ?>
                </form>
            </div>

            <?php if ($error_message) { ?>
                <div class="fl-admin-error"><?= $error_message ?></div>
            <?php } ?>

            <?php
            $CancelRequestListTable = new CancelRequest_List_Table();
            $CancelRequestListTable->prepare_items();
            $CancelRequestListTable->display(); ?>
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
class CancelRequest_List_Table extends WP_List_Table
{
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
        $data = $this->table_data();
//        print "<pre>". will_dump($data). '</pre>';
        $perPage = AdminPageCancelContestRequest::DEFAULT_PAGE_SIZE;
        //$currentPage = $this->get_pagenum(); //todo see if we need this for pagiation or if the get stuff is ok
        $totalItems = FreelinguistContestCancellation::count_requests();
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage
        ));
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return array
     */
    public function get_columns()
    {
        $ret = [];
        foreach (AdminPageCancelContestRequest::COLUMNS as $column_key => $node) {
            $ret[$column_key] = $node['text'];
        }
        return $ret;
    }

    /**
     * Define which columns are hidden
     *
     * @return array
     */
    public function get_hidden_columns()
    {
        return [];
    }

    /**
     * Define the sortable columns
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        $ret = [];
        foreach (AdminPageCancelContestRequest::COLUMNS as $column_key => $node) {
            $ret[$column_key] = [$node['order_as'],$node['is_sortable']];
        }
        return $ret;
    }

    /**
     * Get the table data
     *
     * @return array
     */
    private function table_data()
    {
        $offset = 0;
        $limit = AdminPageCancelContestRequest::DEFAULT_PAGE_SIZE;
        if (isset($_REQUEST['offset'])) {
            $offset = intval($_REQUEST['offset']);
        }

        if (isset($_REQUEST['limit'])) {
            $limit = intval($_REQUEST['limit']);
        }

        // If order is set use this as the order
        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        } else {
            $order = '';
        }
        $order = FreelinguistContestCancellation::get_valid_sort_direction($order);

        $order_by = 'cancel_id';
        // If orderby is set, use this as the sort column
        if (!empty($_GET['orderby'])) {
            $orderby_maybe = strtolower($_GET['orderby']);
            if (array_key_exists($orderby_maybe,AdminPageCancelContestRequest::COLUMNS) ) {
                $order_by = AdminPageCancelContestRequest::COLUMNS[$orderby_maybe]['order_as'];
            }
        }


        //code-notes use the helper class to get the output for each column and set up callback to fill in urls
        $data = FreelinguistContestCancellation::list_data(null,$offset,$limit,$order,$order_by);
        return $data;

    }

    public static function generate_form_goodies($contest_id) {
        $contest_id = (int)$contest_id;
        if (empty(AdminPageCancelContestRequest::$safety_nonce)) {
            AdminPageCancelContestRequest::$safety_nonce =
                wp_create_nonce(AdminPageCancelContestRequest::REFERRER_NAME);
        }
        $out = [];
        $nonce = AdminPageCancelContestRequest::$safety_nonce;
        $out[] = "<input type='hidden' name='_ajax_nonce' value='$nonce'>";
        if ($contest_id) {
            $out[] = "<input type='hidden' name='contest_id' value='$contest_id'>";
        } else {
            $out[] = "<input type='hidden' name='contest_id' value='0'>";
        }

        return "\n".implode("\n",$out)."\n";
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  FreelinguistContestCancelNode $item
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default($item, $column_name)
    {

        //print "<pre>".will_dump($item)."</pre>";
        $stub = AdminPageCancelContestRequest::THIS_ADMIN_STUB_NAME;
        $url_is = admin_url().'admin.php?page='.$stub.'&lang=en';
        switch($column_name) {
            case 'cancel_id': {
                $id = $item->cancel_id;
                if ($item->has_been_approved === null) {
                    return "<div class='fl-cancel-id-wrapper'><span class='fl-cancel-request-id' > # $id</span></div>";
                } else {
                    $b_can_show_undo = FreelinguistContestCancellation::can_undo_contest($item->cancel_id,$undo_button_log);
                    will_log_in_wp_log_and_js_console($undo_button_log,true,false,false,'undo button log');
                    if ($b_can_show_undo) {
                        FreelinguistContestCancellation::is_contest_and_negative_wallet($item->contest_id, $other_log, $customer_balance, AdminPageCancelContestRequest::OUTPUT_LOG_TO_JS_CONSOLE);
                        $form_html = '<form class="fl-undo-cancel-request" action="' . $url_is . '" name="f_undo_' . $item->cancel_id . '" method="post"> ' . "\n";
                        $form_html .= '<span class="fl-cancel-request-id" > # ' . $id . '</span>' . "\n";
                        $form_html .= '<input type="hidden" value="' . $id . '" name="undo_cancel_request"  >' . "\n";
                        $form_html .= '<span class="fa fa-undo fl-undo-icon"></span>' . "\n";
                        $form_html .= static::generate_form_goodies($item->contest_id);
                        $form_html .= '</form>' . "\n";
                        return "<div 
                                        class='fl-undo-cancel-request'
                                        data-id='" . $id . "'
                                        data-cust_balance='" . $customer_balance . "' 
                                        data-budget='" . $item->original_budget . "'>
                                     $form_html
                                 </div>";
                    } else {
                        return "<div class='fl-cancel-id-wrapper fl-cannot-undo-cancel-decision'><span class='fl-cancel-request-id' > * $id </span></div>";
                    }
                }


            }
            case 'contest_id': {
                $title = $item->contest_title;
                $url = $item->contest_url;
               // $id = $item->contest_id;
                $modified_id = $item->modified_id;
                return "<a class='fl-contest-id-of-cancel' href='$url' target='_blank'>[$modified_id] $title</a>";
            }
            case 'customer': {
                $title = $item->customer_email;
                $url = $item->customer_url;
                $id = $item->customer_id;
                return "<a class='fl-cancel-customer-id' href='$url' target='_blank'>[$id] $title</a>";
            }
            case 'extended': {
                if ($item->has_been_approved === null) {
                    //settable , make input

                    $option_html = '';
                    $this_one_right_here = (int)get_option('_default_contest_extend_amount',
                                                        AdminPageCancelContestRequest::DEFAULT_EXTEND_AMOUNT);
                    for ($i=1; $i < 100 ; $i++) {
                        $default = '';
                        if ($i === $this_one_right_here) {
                            $default = 'SELECTED';
                        }

                        $option_html .= '<option value="'.$i.'" '.$default.'> '.$i.' </option>';
                    }

                    $select_html = '<form action="'.$url_is.'" name="f_'.$item->cancel_id.'" method="post"> '."\n";
                    $select_html .= '<select  name="days_extended" class="days-extended-for-redux-in-same-row"> '.$option_html.'</select> Days'."\n";
                    $select_html .= static::generate_form_goodies($item->contest_id);
                    $select_html .= '</form>'."\n";
                    return "<div class='fl-cancel-request-days'>\n$select_html</div>";
                } else {
                    $days = (int)$item->days_extended;
                    if ($days > 1) {
                        $days_text = "$days Days";
                    } else if ($days === 1) {
                        $days_text = "$days Day Only";
                    } else {
                        $days_text = "Not Extended";
                    }
                    return "<span class='fl-cancel-request-days' >$days_text</span>";
                }
            }
            case 'approved': {
                if ($item->has_been_approved === null) {
                    //return form (post to approve, not get)
                    $form_html = '<form action="'.$url_is.'" name="f_approve_'.$item->cancel_id.'" method="post"> '."\n";
                    $form_html .= '<input type="submit" name="approve_this_submit" value="Cancel Job">'."\n";
                    $form_html .= static::generate_form_goodies($item->contest_id);
                    $form_html .=  '<input type="hidden" name="days_extended_redux" class="days-extended-redux" value="">'."\n";
                    $form_html .= '</form>'."\n";
                    return "<div class='fl-cancel-request-approve-this'>\n$form_html</div>";
                } elseif ($item->has_been_approved) {
                    $extra_approve_class = '';
                    if (!$item->percentage) {
                        return "<span class='fl-cancel-request-approved $extra_approve_class'  >
                                Cancelled
                            </span>";
                    }
                } else {
                    return "<span class='fl-cancel-request-approved'  ></span>";
                }
            }
            case 'rejected': {
                if ($item->has_been_approved === null) {
                    //return form (post to approve, not get)
                    $form_html = '<form action="'.$url_is.'" name="f_disapprove_'.$item->cancel_id.'" method="post"> '."\n";
                    $form_html .= '<input type="submit" name="disapprove_this_submit" value="Resume Job">'."\n";
                    $form_html .= static::generate_form_goodies($item->contest_id);
                    $form_html .=  '<input type="hidden" name="days_extended_redux" class="days-extended-redux"  value="">'."\n";
                    $form_html .= '</form>'."\n";
                    return "<div class='fl-cancel-request-disapprove-this'>\n$form_html</div>";
                } elseif ($item->has_been_approved) {
                    return "<span class='fl-cancel-request-disapproved '  ></span>";
                } else {
                    return "<span class='fl-cancel-request-disapproved '  >Resumed</span>";
                }
            }
            case 'partialed': {
                //if the item has not been approved or disapproved or extended yet, (just check to see if approved is null)
                // then can set a partial form to give option to set. Else, show if a partial decision was made
                if ($item->has_been_approved === null) {
                    //return form (post to approve, not get)
                    $option_html = '';
                    $this_one_right_here = (int)get_option('_default_contest_percent_amount',
                        AdminPageCancelContestRequest::DEFAULT_PERCENT_AMOUNT);
                    for ($i=1; $i <= 100 ; $i++) {
                        $default = '';
                        if ($i === $this_one_right_here) {
                            $default = 'SELECTED';
                        }

                        $option_html .= '<option value="'.$i.'" '.$default.'> '.$i.' </option>';
                    }

                    $select_html = '<form action="'.$url_is.'" name="f_'.$item->cancel_id.'" method="post"> '."\n";
                    $select_html .= '<input type="submit" name="percentage_partial_submit" value="Partial Job">'."\n";
                    $select_html .= '<select  name="percentage_partial"> '.$option_html.'</select><span class="fl-unbreaking"> % to customer</span>'."\n";
                    $select_html .= static::generate_form_goodies($item->contest_id);
                    $select_html .=  '<input type="hidden" name="days_extended_redux" class="days-extended-redux" value="">'."\n";
                    $select_html .= '</form>'."\n";
                    return "<div class='fl-cancel-request-partial'>\n$select_html</div>";
                } elseif ($item->percentage) {
                    $value = $item->percentage;
                    return "<span class='fl-cancel-request-partial '  >Resumed at $value % to customer</span>";
                } else {
                    return "<span class='fl-cancel-request-partial '  ></span>";
                }
                break;
            }
            case 'opened': {
                $da_data = $item->opened_ts ? $item->opened_ts : '';
                return "<span class='fl-cancel-request-when-opened a-timestamp-full-date-time'
                                data-ts = '$da_data'
                        ></span>";
            }
            case 'original_deadline': {
                $da_data = $item->original_deadline_ts ? $item->original_deadline_ts : '';
                return "<span class='fl-cancel-request-when-original-deadline a-timestamp-full-date-time'
                                data-ts = '$da_data'
                        ></span>";
            }
            case 'processed': {
                $da_data = $item->processed_ts ? $item->processed_ts : '';
                return "<span class='fl-cancel-request-when-processed a-timestamp-full-date-time'
                                data-ts = '$da_data'
                        ></span>";
            }
            case 'posted_by': {
                $title = $item->posted_by_email;
                $url = $item->posted_by_url;
                $id = $item->posted_by_id;
                if ($id) {
                    return "<a class='fl-cancel-posted-id' href='$url' target='_blank'>[$id] $title</a>";
                }
                return "<span class='fl-cancel-posted-id'>No Action Yet</span>";
            }
            default: {
                if (array_key_exists($column_name,AdminPageCancelContestRequest::COLUMNS) ) {
                    if (property_exists($item,$column_name)) {return $item->$column_name;}
                }
                will_send_to_error_log("<!-- Unknown column of $column_name in ".__FILE__."-->");
                return "<!-- Unknown column of $column_name -->";
            }
                
        }

    }

}


