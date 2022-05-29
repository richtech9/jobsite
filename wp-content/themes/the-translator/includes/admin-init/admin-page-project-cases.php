<?php
/*
 * Plugin Name: JobRejection Table
 * Description: JobRejection_Wp_List_Table
 * Author: Lakhvidner
 * Author URI: http://www.Lakhvidner.com
 * Version: 1.0
 */
/*
 * current-php-code 2021-Jan-10
 * input-sanitized :refill_amount
 * current-wp-template:  admin-screen  for managing milestone disputes
 */



/**
 * Paulund_Wp_List_Table class will create the page to load the table
 */
class AdminPageProjectCases
{
    public $parent_slug = null;
    public $position = null;
    /**F
     * Constructor will create the menu item
     * @param string $parent_slug
     * @param int $position
     */
    public function __construct($parent_slug = null,$position = null)
    {
        $this->parent_slug = $parent_slug;
        $this->position = $position;
        add_action('admin_menu', array($this, 'add_menu_JobRejection_list_table_page'));
    }

    /**
     * Menu item will allow us to load the page to display the table
     */
    public function add_menu_JobRejection_list_table_page()
    {
        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'Project Cases', 'Project Cases', 'manage_options',
                'freelinguist-admin-project-cases', array($this, 'list_table_page'), $this->position);
        } else {
            add_menu_page('Project Cases', 'Project Cases', 'manage_options',
                'freelinguist-admin-project-cases', array($this, 'list_table_page'), 'dashicons-list-view');
        }

    }


    public function fl_message_insert($message = "", $milestone_id = NULL, $proposal_id = NULL, $content_id = NULL, $customer = NULL, $freelancer = NULL)
    {


        global $wpdb;


        $data = array(

            'message' => $message,

            'milestone_id' => $milestone_id,

            'proposal_id' => $proposal_id,

            'content_id' => $content_id,

            'customer' => $customer,

            'freelancer' => $freelancer,

            'created_at' => date('Y-m-d'),
            'added_by' => get_current_user_id()

        );

        $inst = $wpdb->insert('wp_message_history', $data);

        if ($inst !== false) {

            return $inst;

        } else {

            return false;

        }

    }

    /**
     * Display the list table page
     *
     * @return Void
     */

    public function list_table_page()
    {
        //print_r(calculateLinguistPrice(18268));
        if (isset($_REQUEST['jobis'])) {
            //echo "<pre>"; print_R($_REQUEST); exit;
            $job_id_is = $_REQUEST['jobis'];

            if (isset($_REQUEST['approve'])) {

                $job_id = $_REQUEST['jobis'];
                $milestone_id = $_REQUEST['milestone_id'];
                $mediator_id = get_current_user_id();

                global $wpdb;

                $result = $wpdb->get_row($wpdb->prepare(/** @lang text */
                    "SELECT `author`, `linguist_id` FROM wp_fl_milestones WHERE `ID` = %d", array($milestone_id)), ARRAY_A);

                $linguist_id = $result['linguist_id'];



                $wpdb->query($wpdb->prepare(/** @lang text */
                    "UPDATE wp_dispute_cases SET status = %s, mediator_id = %d WHERE milestone_id = %d", "approved", $mediator_id, $milestone_id));

                /*Start Paying to Linguist*/
                $result = $wpdb->get_row($wpdb->prepare(/** @lang text */
                    "SELECT * FROM wp_fl_milestones WHERE ID = %d", array($milestone_id)), ARRAY_A);
                $milestone_price = $result['amount'];


                $total_user_balance = get_user_meta($linguist_id, 'total_user_balance', true);

                $percentage = get_option('linguist_flex_referral_fee') ? get_option('linguist_flex_referral_fee') : 15;

                $fee = ($milestone_price * $percentage) / 100;


                $net_amount = $milestone_price - $fee;

                $net_amount = max($net_amount, 0);

                $updated_balance = amount_format($total_user_balance + $net_amount);
                update_user_meta($linguist_id, 'total_user_balance', $updated_balance);

                fl_transaction_insert($net_amount,'done','dispute_approved',$linguist_id,get_current_user_id(),
                    'Milestone dispute: job approved by admin','','',$job_id,NULL,$milestone_id);
//                $this->insert_transaction(uniqid(), $net_amount . '', 'done', 'Milestone dispute: job approved by admin', 'dispute_approved',
//                    '', '', $linguist_id, get_current_user_id(), $job_id, NULL , $milestone_id);

                $wpdb->update('wp_fl_milestones', array('status' => 'completed'), array('ID' => $milestone_id));

                $this->fl_message_insert("Milestone dispute: job approved by admin", $milestone_id);




            }

            if (isset($_REQUEST['reject'])) {
                global $wpdb;
                $jobis = $_GET['jobis'];
                $milestone_id = $_GET['milestone_id'];
                $reject_status = $_GET['reject'];

                $mediator_id = get_current_user_id();

                if ($reject_status == 'yes') {
                     $wpdb->query($wpdb->prepare(/** @lang text */
                        "UPDATE wp_dispute_cases SET status = %s, mediator_id = %d WHERE milestone_id = %d", "rejected_by_mediator", $mediator_id, $milestone_id));

                    /*Start Paying to customer*/
                    $result = $wpdb->get_row($wpdb->prepare(/** @lang text */
                        "SELECT * FROM wp_fl_milestones WHERE ID = %d", array($milestone_id)), ARRAY_A);
                    $milestone_price = (float)$result['amount'];


                    $project_id = $result['project_id'];

                    $author = get_post_field('post_author', $project_id);


                    $total_user_balance = (float)get_user_meta($author, 'total_user_balance', true);


                    //$net_amount = $milestone_price+$fee;
                    $net_amount = $milestone_price;
                    $net_amount = max($net_amount, 0);

                    $updated_balance = amount_format($total_user_balance + $net_amount);
                    update_user_meta($author, 'total_user_balance', $updated_balance);

                    fl_transaction_insert($net_amount,'done','dispute_rejected', $author, get_current_user_id(),
                        'Milestone dispute: job cancelled by admin','','',$jobis,NULL,$milestone_id);
//                    $this->insert_transaction(uniqid(), $net_amount, 'done', 'Milestone dispute: job cancelled by admin',
//                        'dispute_rejected', '', '', $author, get_current_user_id(),  $jobis,NULL, $milestone_id);

                    $wpdb->update('wp_fl_milestones', array('status' => 'approved_rejection'), array('ID' => $milestone_id));

                    $this->fl_message_insert("Milestone dispute: job cancelled by admin", $milestone_id);

                }
            }

            if (isset($_REQUEST['partially_percentage'])) {

                $percentage = $_REQUEST['partially_percentage'];
                $milestone_id = $_REQUEST['milestone_id'];
                $mediator_id = get_current_user_id();

                global $wpdb;
                $result = $wpdb->get_row($wpdb->prepare(/** @lang text */
                    "SELECT * FROM wp_fl_milestones WHERE `ID` = %d", array($milestone_id)), ARRAY_A);
                $linguist_id = $result['linguist_id'];
                $project_id = $result['project_id'];


                $jobis = $_GET['jobis'];

                $variables = array();
                $variables['job_title'] = $milestone_id;
                $variables['partially_percentage'] = $percentage;


                $milestone_price = $result['amount'];

                $approved_price = ($percentage / 100) * $milestone_price;

                 $wpdb->query($wpdb->prepare(/** @lang text */
                    "UPDATE wp_dispute_cases SET status = %s, approved_partially = %s, mediator_id = %d WHERE milestone_id = %d", "approved_partially", $percentage, $mediator_id, $milestone_id));

                /***** payment flow ******/

                $freelancer_balance = get_user_meta($linguist_id, 'total_user_balance', true);

                $freelancer_percentage = get_option('linguist_flex_referral_fee') ? get_option('linguist_flex_referral_fee') : 15;

                $fee = ($approved_price * $freelancer_percentage) / 100;


                $net_amount = $approved_price - $fee;
                $net_amount = max($net_amount, 0);
                $updated_balance = amount_format($freelancer_balance + $net_amount);
                update_user_meta($linguist_id, 'total_user_balance', $updated_balance);

                fl_transaction_insert($net_amount, 'done','dispute_partly_approved',$linguist_id, get_current_user_id(),
                    'Earnings due to ' . $percentage . '% partial approval','','',$jobis,NULL,$milestone_id);

//                $this->insert_transaction(uniqid(), $net_amount, 'done',
//                    'Earnings due to ' . $percentage . '% partial approval',
//                    'dispute_partly_approved', '', '', $linguist_id, get_current_user_id(),  $jobis,NULL, $milestone_id);


                $this->fl_message_insert("Milestone dispute " . $percentage . "%  approved by admin", $milestone_id);

                /******** payment to customer ******/

                $author = get_post_field('post_author', $project_id);


                $total_user_balance = get_user_meta($author, 'total_user_balance', true);

                $client_percentage = get_option('client_flex_referral_fee') ? get_option('client_flex_referral_fee') : 2.5;

                $fee_on_complete_payment = ($milestone_price * $client_percentage) / 100;
                $fee_on_approved_payment = ($approved_price * $client_percentage) / 100;

                $net_amount_to_pay = ($milestone_price + $fee_on_complete_payment) - ($approved_price + $fee_on_approved_payment);

                $net_amount_to_pay = max($net_amount_to_pay, 0);

                update_user_meta($author, 'total_user_balance', amount_format($total_user_balance + $net_amount_to_pay));

                fl_transaction_insert($net_amount_to_pay,'done','dispute_partly_approved',$author, get_current_user_id(),
                    'Refund due to ' . $percentage . '% partial approval','','',$jobis,NULL,$milestone_id);

//                $this->insert_transaction(uniqid(), $net_amount_to_pay, 'done',
//                    'Refund due to ' . $percentage . '% partial approval',
//                    'dispute_partly_approved', '', '', $author, get_current_user_id(),  $jobis, NULL, $milestone_id);

                /************************************/

                $wpdb->update('wp_fl_milestones', array('status' => 'completed'), array('ID' => $milestone_id));

            }

            if (isset($_REQUEST['freeze'])) {

                $milestone_id = $_REQUEST['milestone_id'];

                global $wpdb;
                $result = $wpdb->get_row($wpdb->prepare(/** @lang text */
                    "SELECT * FROM wp_fl_milestones WHERE `ID` = %d", array($milestone_id)), ARRAY_A);
                $linguist_id = $result['linguist_id'];

                //if(empty(get_post_meta($job_id_is,'freeze',true))){
                if (!in_array($linguist_id, get_post_meta($job_id_is, 'job_freeze_user'))) {
                    add_post_meta($job_id_is, 'job_freeze_user', $linguist_id);
                    // update_post_meta($job_id_is,'job_freeze_date',date('Y-m-d'));
                    echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                    <p><strong>Settings saved.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                }


            }
        }

        ?>
        <div class="wrap">
            <span class="bold-and-blocking large-text">Project Cases</span>
            <hr>
            <div id="icon-users" class="icon32"></div>

            <div class="view_and_add">
                <form class="form_ca" name="view_add_form" method="post" action="">
                    <input type="text" name="view_add_job" placeholder="Enter job id here"
                           value="<?php if (isset($_REQUEST['view_add_job'])) {
                               echo $_REQUEST['view_add_job'];
                           } ?>" id="view_add_job">
                    <input type="submit" name="submit" value="View and Add new Case"
                           id="view_add_button">
                </form>
            </div>

            <ul class="subsubsub"></ul>
            <p class="search-box">
                <input class="enhanced-text" type="search" id="r-search-input" name="s" value="" title="Search">
                <input type="submit" id="search-u"  class="button large-text" value="Search">
            </p>
            <script>
                jQuery(function () {
                    jQuery('#search-u').click(function () {
                        var inputURL = jQuery('#r-search-input').val();
                        var url = '<?php echo admin_url(); ?>' + 'admin.php?page=freelinguist-admin-project-cases&lang=en&s=' + inputURL;
                        //you dont need the .each, because you are selecting by id
                        //Redirects
                        window.location.href = url;
                        return false;
                    });

                    jQuery('#view_add_button').click(function () {
                        var inputURL = jQuery('#view_add_job').val();
                        if (inputURL === '') {
                            alert('Please  enter job id');

                        } else {

                            var url = '<?php echo get_site_url(); ?>' + '/job/' + inputURL;
                            //you dont need the .each, because you are selecting by id
                            //Redirects
                            var win = window.open(url, '_blank');
                            if (win) {
                                //Browser has allowed it to be opened
                                win.focus();
                            } else {
                                //Browser has blocked it
                                alert('Please allow popups for this website');
                            }
                        }

                        // window.location.href = url;
                        return false;
                    });
                });
            </script>
            <?php
            $JobRejectionListTable = new JobRejection_List_Table();
            $JobRejectionListTable->prepare_items();
            $JobRejectionListTable->display(); ?>
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
class JobRejection_List_Table extends WP_List_Table
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
        usort($data, array(&$this, 'sort_data'));
        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage
        ));
        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);
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
        $columns = array(
            'case_id' => 'Case #',
            'job_name' => 'Job #',
            'milestone_id' => 'Milestone #',
            'customer' => 'Customer',
            'lingusit' => 'Linguist',
            'job_approve' => 'Approve',
            'job_reject' => 'Reject',
            'job_approve_partially' => 'Approve Partially',
            'job_freeze' => 'Freeze Job',
            'open_date' => 'Open Date',
            'deadline_date' => 'Deadline Date',
            'job_rejection_days' => 'Rejection Days',
            'mediator' => 'Mediator',
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
        return array('job_name' => array('job_name', true), 'open_date' => array('open_date', true), 'mediator' => array('mediator', true), 'deadline_date' => array('deadline_date', true));
    }

    /**
     * Get the table data
     *
     * @return array
     */
    private function table_data()
    {

        global $wpdb;

        $cases = $wpdb->get_results("SELECT
                                      dispute.ID as case_id,
                                      dispute.milestone_id,
                                      dispute.status as case_status,
                                      dispute.linguist_id,
                                      dispute.customer_id,
                                      dispute.approved_partially,
                                      dispute.freeze_job,
                                      dispute.post_date,
                                      dispute.post_modified,
                                      job.title as job_title,
                                      meta.meta_value as modified_id,
                                      (
                                        SELECT COUNT(f.id)+1 FROM wp_fl_milestones f 
                                        WHERE f.id < milestone.id AND f.job_id = milestone.job_id AND f.project_id = milestone.project_id
                                        ) AS da_milestone_index,
                                      milestone.*
                                    FROM
                                      wp_dispute_cases dispute
                                    INNER JOIN wp_fl_milestones milestone ON dispute.milestone_id = milestone.ID
                                    LEFT JOIN wp_posts project ON project.ID = milestone.project_id
                                     LEFT JOIN wp_postmeta meta ON meta.post_id = project.ID AND meta.meta_key = 'modified_id'
                                    LEFT JOIN wp_fl_job job ON job.ID = milestone.job_id
                                    ", ARRAY_A);



        $data = array();
        $option_html = '';
        for ($i = 1; $i < 100; $i++) {
            $option_html .= '<option value="' . $i . '"> ' . $i . ' </option>';
        }

        $switcher = new FLSwitchUserHelper();
        foreach ($cases as $key => $value) {

            $post_id = $value['project_id'];
            $case_status = $value['case_status'];

            $result['case_id'] = $value['case_id'];

            $result['ID'] = $value['job_id'];
            $milestone_id = $value['milestone_id'];
            $result['milestone_id'] = $value['da_milestone_index'] . '  <span class="fl-small-id">'. $milestone_id . '</span>';


            $url_is = admin_url() . 'admin.php?page=freelinguist-admin-project-cases&lang=en&jobis=' . $post_id . '&milestone_id=' . $milestone_id;

            $da_link_plain = get_permalink($post_id) . '&job_id=' . $value['job_title'];
            $da_link = $switcher->generate_switch_redirect_url($value['linguist_id'],$da_link_plain);
            $result['job_name'] =
                '<a target="_blank" class="fl-span-breaks" href="' . $da_link . '">'.
                '  <span> Project: ' . $value['modified_id'] . '</span>'.
                '  <span> Job# '.$value['job_title'].'</span>'.
                '</a>';

            $customer_info = get_userdata($value['customer_id']);
            $lingusit_info = get_userdata($value['linguist_id']);


            $result['customer'] = $customer_info->user_email;
            $result['lingusit'] = $lingusit_info->user_email;

            if ($case_status == 'approved') {
                $result['job_approve'] = '<span class="">Approved</span>';
                $result['job_reject'] = '<span class="">---</span>';
            } elseif ($case_status == 'rejected_by_mediator') {
                $result['job_approve'] = '<span class="">---</span>';
                $result['job_reject'] = '<span class="">Rejected</span>';
            } elseif ($case_status == 'approved_partially') {
                $result['job_approve'] = '<span class="">---</span>';
                $result['job_reject'] = '<span class="">---</span>';
            } else {
                $result['job_approve'] = /** @lang text */
                    '<a class="approve_rejection_by_mediator" id="' . $value["ID"] . '" href="' . $url_is . '&approve=yes">Approve</a>';
                $result['job_reject'] = /** @lang text */
                    '<a class="reject_rejection_by_mediator" id="' . $value["ID"] . '" href="' . $url_is . '&reject=yes">Reject</a>';
            }


            if ($case_status == 'approved' || $case_status == 'rejected_by_mediator') {
                $result['job_approve_partially'] = '---';
            } elseif ($case_status == 'approved_partially') {

                global $wpdb;

                $query = $wpdb->get_row($wpdb->prepare(/** @lang text */
                    "SELECT `approved_partially` FROM wp_dispute_cases WHERE ID = %d", array($value['case_id'])), ARRAY_A);
                $result['job_approve_partially'] = $query['approved_partially'] . '%';

            } else {
                $result['job_approve_partially'] = '<form action="' . $url_is . '" name="f_' . $value["ID"] . '" method="post"> <select style="width:75px" name="partially_percentage"> ' . $option_html . '</select>%<input type="submit" name="submit" value="Approve Partially"></form>';
            }

            if (in_array($value['linguist_id'], get_post_meta($post_id, 'job_freeze_user')) && !empty(get_post_meta($post_id, 'job_freeze_user'))) {
                $result['job_freeze'] = '<a class="freeze_rejection_by_mediator" id="' . $value["ID"] . '"  href="javascript:;">Frozen</a><br> ' . get_post_meta($post_id, 'job_freeze_date', true);
                //task-future-work in the admin project cases, there is an issue about saving the freeze date, the post meta job_freeze_date is never filled in the code elsewhere
            } else {
                $result['job_freeze'] = /** @lang text */
                    '<a class="freeze_rejection_by_mediator" id="' . $value["ID"] . '"  href="' . $url_is . '&freeze=yes">Freeze</a>';
            }


            $phpdate = strtotime($value['post_date']);
            $opendate = date('Y-M-d', $phpdate);

            $result['open_date'] = $opendate;
            $result['deadline_date'] = $fiveDays = date("Y-M-d", strtotime($opendate . "+20 days"));

            $result['job_rejection_days'] = '';
            $result['mediator'] = '';  //code-notes job_meditation_user, which used to hold this value, stopped being filled in elsewhere, but it used to be the user's user_login calculated from the user id


            $data[] = $result;
        }
        //echo "<pre>"; print_R( $data); exit;
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
            case 'case_id':
            case 'job_name':
            case 'milestone_id':
            case 'customer':
            case 'lingusit':
            case 'job_approve':
            case 'job_reject':
            case 'job_approve_partially':
            case 'job_freeze':
            case 'open_date':
            case 'deadline_date':
            case 'job_rejection_days':
            case 'mediator':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     * @param array $a
     * @param array $b
     * @return Mixed
     */
    private function sort_data($a, $b)
    {
        // Set defaults
        $orderby = 'deadline_date';
        $order = 'desc';
        // If orderby is set, use this as the sort column
        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }
        // If order is set use this as the order
        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }
        $result = strcmp($a[$orderby], $b[$orderby]);
        if ($order === 'asc') {
            return $result;
        }
        return -$result;
    }
}

?>