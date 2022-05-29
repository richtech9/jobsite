<?php
/*
 * Plugin Name: content Table
 * Description: Content_List_Table
 * Plugin URI: http://www.paulund.co,
 * Author: Lakhvidner
 * Author URI: http://www.Lakhvidner.com
 * Version: 1.0
 */

/*
    * current-php-code 2021-Jan-11
    * input-sanitized :
    * current-wp-template:  admin-screen  for content
*/


/**
 * Paulund_Wp_List_Table class will create the page to load the table
 */
class AdminPageTxn extends  FreelinguistDebugging
{
    //each inherited debugging needs their own controls
    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;

    const PAGE_STUB = 'freelinguist-admin-txn';
    public $parent_slug = null;
    public $position = null;
    /**
     * @var AdminTxForm $admin_form
     */
    protected $admin_form = null;
    /**
     * Constructor will create the menu item
     * @param string $parent_slug
     * @param int $position
     */
    public function __construct($parent_slug = null,$position = null)
    {
        $this->parent_slug = $parent_slug;
        $this->position = $position;
        $this->admin_form = new AdminTxForm();
        add_action('admin_menu', array($this, 'on_add_menu'));

    }

    /**
     * Menu item will allow us to load the page to display the table

     */
    public function on_add_menu()
    {
        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'All Transactions', 'All Transactions', 'manage_options',
                static::PAGE_STUB, array($this, 'create_admin_body'), $this->position);
        } else {
            add_menu_page('All Transactions', 'All Transactions', 'manage_options',
                static::PAGE_STUB, array($this, 'create_admin_body'), 'dashicons-list-view');
        }

    }




    public function create_admin_body()
    {
        ?>
        <style>
            span.fl-admin-small-id-line {
                display: block;
                font-size: 80%;
                color: lightgrey;
                padding-left: 1.5em;
            }

            /*noinspection CssUnusedSymbol*/
            th.manage-column.column-description {
                width: 25em;
            }

            /*noinspection CssUnusedSymbol*/
            th.manage-column.column-txn_id {
                width: 12em;
            }

            /*noinspection CssUnusedSymbol*/
            th.manage-column.column-type {
                width: 12em;
            }

            /*noinspection CssUnusedSymbol*/
            td.fl-admin-txn-show-pointer {
                cursor: pointer;
            }
        </style>
        <div class="wrap">
            <div class="wrap stuffbox" style="padding: 15px;">

            <span class="bold-and-blocking larger-text">Transactions</span>
            <hr>
            <?php
            $txn_table = null;
            try {
                $this->admin_form->process_form();
                $txn_table = new AdminTxnListTable($this->admin_form);
                $txn_table->prepare_items();
                $this->admin_form->print_form();
                $txn_table->display();
            } catch (Exception $e) {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p>Error Processing Form</p>
                    <p><?= $e->getMessage()?></p>
                </div>
                <?php
                static::log(static::LOG_ERROR,'Page Main',$e->getMessage());
            }
            ?>
            </div>
        </div>
        <div class="fl-txn-admin-description-edit-holder" style="display: none">
            <div class="fl-txn-admin-description-edit">
                <input type="hidden" name="remember-txn-id" value="">
                <textarea title="Edit Description" class="form-control" name="description-to-edit"></textarea>
            </div>
        </div>
        <script>
            var fl_page_args = {
                min_id: <?= $txn_table->min_data_id ?  $txn_table->min_data_id : 0?>,
                max_id: <?= $txn_table->max_data_id ?  $txn_table->max_data_id : 0 ?>,
                start_date_yy_mm_dd: '<?= trim($this->admin_form->start_date_yy_mm_dd)  ?>',
                end_date_yy_mm_dd: '<?= trim($this->admin_form->end_date_yy_mm_dd)  ?>',
                transaction_type: '<?= trim($this->admin_form->transaction_type)  ?>',
                raw_text_search: '<?= trim($this->admin_form->raw_text_search)  ?>'
            } ;

            jQuery(function($) {
                jQuery('a.next-page.button , a.prev-page.button , a.last-page.button , a.first-page.button').each(function() {
                    let das = $(this);
                    let old_url = das.attr('href');
                    let new_url = '<?= menu_page_url(AdminPageTxn::PAGE_STUB,false)?>';
                    let urlParams = new URLSearchParams(old_url);
                    let paged = urlParams.get('paged');
                    if (paged) {
                        new_url = new_url + '&paged='+paged;
                    }

                    for(let k in fl_page_args) {
                        if (!fl_page_args.hasOwnProperty(k)) {continue;}
                        new_url = new_url + '&'+ k +'='+fl_page_args[k];

                    }
                    das.attr('href',new_url);
                });

                // jQuery('a.prev-page.button').each(function() {
                //     let das = $(this);
                //     let old_url = das.attr('href');
                //     let new_url = old_url;
                //     for(let k in fl_page_args) {
                //         if (!fl_page_args.hasOwnProperty(k)) {continue;}
                //         if (new_url.indexOf(k) === -1) {
                //             new_url = new_url + '&'+ k +'='+fl_page_args[k];
                //         }
                //     }
                //     das.attr('href',new_url);
                // })

                //a.last-page.button

                //a.first-page.button
            });
        </script>
        <!-- txn admin form footer -->
        <?php
            $this->admin_form->print_js();

    }
}
AdminPageTxn::turn_on_debugging(FreelinguistDebugging::LOG_DEBUG);

// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class AdminTxForm extends  FreelinguistDebugging
{
    //each inherited debugging needs their own controls
    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;


    public $transaction_type = '';
    public $user_id = 0;
    public $txn_id = '';
    public $raw_text_search;

    public $start_date_yy_mm_dd;
    public $end_date_yy_mm_dd;

    public $is_new_search = false;

    //code-notes keys are alphabetically sorted a-z
    const TRANSACTION_TYPES = [
        'buy_content' => 'Content Bought',
        'claim_award' => 'Proposal Award Claimed',
        'contentDisputePartlyApproved' => 'Content Dispute Partly Approved',
        'contentDisputeRejected' => 'Content Dispute Rejected',
        'contentRejected' => 'Content Rejected',
        'contentWinner' => 'Content Bid Winner',
        'contestDisputeApproved' => 'Contest Dispute Approved',
        'contestDisputePartlyApproved' => 'Contest Dispute Partly Approved',
        'contestDisputeRejected' => 'Contest Dispute Rejected',
        'contestRejected' => 'Proposal Contest Rejected',
        'contestWinner' => 'Proposal Contest Winner',
        'contest_created' => 'Contest Created',
        'disputeRaise' => 'Project and Contest Dispute Created ',
        'disputeRaiseContent' => 'Content Dispute Created',
        'dispute_approved' => 'Project Dispute Approved',
        'dispute_partly_approved' => 'Project Dispute Partly Approved',
        'dispute_rejected' => 'Project Dispute Rejected',
        'hire' => 'Project Hire',
        'job_start' => 'Project Job Start',
        'milestone_completed' => 'Project Milestone Completed',
        'milestone_created_by_customer' => 'Project Milestone Created by Customer',
        'milestoneRejected' => 'Project Milestone Rejected',
        'refill' => 'Wallet Refill',
        'refill_fee' => 'Wallet Refill Fee',
        'refund' => 'Contest Refund',
        'seal_Files' => 'Proposal Seal Files',
        'undo_refund' => 'Contest Refund Undone',
        'view_Sealed' => 'Proposal View Sealed',
        'winner_added'  => 'Proposal Winner Added'
    ];

    public function __construct(){
        $this->is_new_search = false;
    }

    public function print_form() {
        $this->print_css();

        $sorted_types = static::TRANSACTION_TYPES;
        $sorted_types[''] = '(all)';
        asort($sorted_types);
        ?>
        <form id="automatic_job_canceled_days_f" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="start_date_yy_mm_dd" value="<?=$this->start_date_yy_mm_dd?>">
            <input type="hidden" name="end_date_yy_mm_dd" value="<?=$this->end_date_yy_mm_dd?>">
            <input type="hidden" name="transaction_type" value="<?=$this->transaction_type?>">
            <div class="">
                <div class="row">

                    <div class="col-md-2">
                        <label>
                            Start Date
                        </label>
                        <input type="text" value="<?= $this->start_date_yy_mm_dd ?>" name="start_date"
                               id="start_date" placeholder="Start Date" readonly="readonly"
                               autocomplete="off"
                               class="form-control calendar-icon">

                    </div>

                    <div class="col-md-2">
                        <label>
                            End Date
                        </label>
                        <input type="text" value="<?= $this->end_date_yy_mm_dd ?>" name="end_date"
                               id="end_date" placeholder="End Date" readonly="readonly"
                               autocomplete="off"
                               class="form-control calendar-icon">
                    </div>

                    <div class="col-md-2">
                        <label>
                           Transaction Type
                        </label>
                        <select id="transaction_type" name="transaction_type" title="type of transaction"
                                class="form-control " autocomplete="off"
                        >
                            <?php
                                foreach ($sorted_types as $key_type => $val_type) {
                                    $selected = '';
                                    if (trim($this->transaction_type) === $key_type) {
                                        $selected = "SELECTED";
                                    }
                                    ?>
                                    <option value="<?= $key_type?>" <?= $selected?>>
                                        <?= $val_type ?>
                                    </option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>
                            TXN or User ID|Email|Name
                        </label>
                        <input type="text" class="form-control" autocomplete="off" name="raw_text_search"
                                id = 'raw_text_search' value="<?= $this->raw_text_search?>" title="Search for User ID or TXN #"
                        >
                    </div>

                    <div class="col-md-2">
                        <label>
                            &nbsp;
                        </label>
                        <input type="submit" class="btn btn-primary form-control" name="new-search" value="Search">
                    </div>

                    <div class="col-md-2">
                        <label>
                            &nbsp;
                        </label>
                        <a role="button" class="btn btn-default form-control"
                            href="<?= menu_page_url(AdminPageTxn::PAGE_STUB,false) ?>"
                        >
                            Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>
        <?php
    }

    public function print_js() {
        ?>
        <script>
            jQuery(function ($) {

                jQuery("#start_date").datepicker({
                    dateFormat: "yy-mm-dd",
                    minDate: "2015-12-31",
                    changeMonth: true,
                    changeYear: true,
                    setDate: "<?= $this->start_date_yy_mm_dd ?>",
                    onSelect: function (dateText, /*inst*/) {
                        let input = $('input[name ="start_date_yy_mm_dd"]');
                        input.val(dateText);
                    }

                });

                jQuery("#end_date").datepicker({
                    dateFormat: "yy-mm-dd",
                    minDate: "2015-12-31",
                    changeMonth: true,
                    changeYear: true,
                    setDate: "<?= $this->end_date_yy_mm_dd ?>",
                    onSelect: function (dateText, /*inst*/) {
                        let input = $('input[name ="end_date_yy_mm_dd"]');
                        input.val(dateText);
                    }

                });

                let clickers_for_description = jQuery('span.fl-txn-admin-description').closest('td');
                clickers_for_description.addClass('fl-admin-txn-show-pointer');
                clickers_for_description.click(function(){
                    let that = $(this);
                    let that_span = that.find('span.fl-txn-admin-description');
                    let transaction_id = that_span.data('txid');
                    let words = that_span.text();
                    console.log(transaction_id,words);
                    let template = $('div.fl-txn-admin-description-edit-holder div.fl-txn-admin-description-edit');
                    if (template.length === 0) {
                        console.warn("no template found");
                        return;
                    }

                    let thing_jq = template.clone();
                    thing_jq.find('textarea').val(words);
                    thing_jq.find('input').val(transaction_id);

                    let thing = thing_jq[0];

                    // noinspection JSPotentiallyInvalidConstructorUsage
                    var modal = new tingle.modal({
                        footer: true,
                        stickyFooter: true,
                        closeMethods: ['overlay', 'button', 'escape'],
                        closeLabel: "Close",
                        cssClass: ['fl-txn-admin-description-popup'],
                        onOpen: function() {
                            //
                        },
                        onClose: function() {
                            // console.log('modal closed');
                        },
                        beforeClose: function() {
                            // here's goes some logic
                            // e.g. save content before closing the modal
                            return true; // close the modal
                            //return false; // nothing happens
                        }
                    });

                    // set content
                    modal.setContent(thing);

                    // add a button
                    modal.addFooterBtn('Cancel', 'tingle-btn tingle-btn--primary', function() {
                        // here goes some logic
                        modal.close();
                        modal.destroy();
                    });

                    // add another button
                    modal.addFooterBtn('Save', 'tingle-btn tingle-btn--default', function() {
                        let new_words_textarea =  $('div.fl-txn-admin-description-popup textarea[name ="description-to-edit"]');
                        let new_words_input =  $('div.fl-txn-admin-description-popup input[name ="remember-txn-id"]');
                        if (new_words_textarea.length >= 0 && new_words_input.length >=0) {
                            let new_words = new_words_textarea.val().trim();
                            let transaction_id = new_words_input.val();
                            if (new_words.length) {

                                //code-notes call new ajax and update the text without reloading
                                var data = {
                                    action: 'fl_update_txn_description',
                                    description: new_words,
                                    txn_id: transaction_id
                                };
                                $.post(ajaxurl, data, function(response_raw) {
                                    /**
                                     * @type {FreelinguistBasicAjaxResponse} response
                                     */
                                    let response = freelinguist_safe_cast_to_object(response_raw);
                                    if (response.status === true) {
                                        that_span.text(new_words);
                                    } else {
                                        will_handle_ajax_error('Update TXN Description', response.message);
                                    }

                                    modal.close();
                                    modal.destroy();

                                });

                                return
                            } else {
                                console.warn('cannot update empty length for txn description');
                            }
                        } else {
                            console.warn('Cannot find textarea and/or input for updating txn description');
                        }

                        modal.close();
                        modal.destroy();
                    });

                    // open modal
                    modal.open();

            });



            });
        </script>
        <?php
    }

    protected function print_css() {

    }

    public function process_form() {
        try {
            if (isset($_POST['new-search']) ) {
                FLInput::onlyPost(true);
                $this->is_new_search = true;
            }


            $this->start_date_yy_mm_dd
            = FLInput::get('start_date_yy_mm_dd',null,FLInput::YES_I_WANT_CONVESION,
                FLInput::NO_DB_ESCAPING, FLInput::NO_HTML_ENTITIES);

            $this->end_date_yy_mm_dd
            = FLInput::get('end_date_yy_mm_dd',null,FLInput::YES_I_WANT_CONVESION,
                FLInput::NO_DB_ESCAPING, FLInput::NO_HTML_ENTITIES);

            $this->transaction_type
            = FLInput::get('transaction_type',null,FLInput::YES_I_WANT_CONVESION,
                FLInput::NO_DB_ESCAPING, FLInput::NO_HTML_ENTITIES);

            $this->raw_text_search
            = FLInput::get('raw_text_search',null,FLInput::YES_I_WANT_CONVESION,
                FLInput::NO_DB_ESCAPING, FLInput::NO_HTML_ENTITIES);

            FLInput::onlyPost(false);
            if ($this->raw_text_search) {
                if (ctype_digit($this->raw_text_search)) {
                    $this->user_id = (int)$this->raw_text_search;
                    $this->txn_id = null;
                } else {
                    $test_user = get_user_by('email', $this->raw_text_search);
                    if (empty($test_user) || empty($test_user->ID)) {
                        $test_user = get_user_by('slug', $this->raw_text_search);
                    }

                    if ($test_user && $test_user->ID) {
                        $this->user_id = (int)$test_user->ID;
                        $this->txn_id = null;
                    } else {
                        $this->user_id = null;
                        $this->txn_id = $this->raw_text_search;
                    }

                }
            }

        } catch (Exception $e) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p>Error Processing Form</p>
                <p><?= $e->getMessage()?></p>
            </div>
            <?php static::log(static::LOG_ERROR,'Form',$e->getMessage()) ?>
            <?php
        }
    }
}

AdminTxForm::turn_on_debugging(FreelinguistDebugging::LOG_DEBUG);

/**
 * Create a new table class that will extend the WP_List_Table
 */
class AdminTxnListTable extends WP_List_Table
{
    const PER_PAGE = 15;
    /**
     * @var AdminTxForm $our_filters
     */
    protected $our_filters = null;

    protected $usd_formatter;

    protected $where_clauses = [];

    public $min_data_id ;
    public $max_data_id;
    /**
     * AdminTxnListTable constructor.
     * @param AdminTxForm $our_filters
     * @param array $args
     */
    public function __construct($our_filters = null,$args = [])
    {
        $this->our_filters = $our_filters;
        $this->usd_formatter =  numfmt_create( 'en_US', NumberFormatter::CURRENCY );
        parent::__construct($args);
    }

    public function get_pagenum() {
        if ($this->our_filters->is_new_search) {
            return 1;
        }
        return parent::get_pagenum();
    }

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
        $this->min_data_id = null;
        $this->max_data_id = null;
        foreach ($data as $row) {
            $dat_id = $row['transaction_id'];
            if (is_null($this->min_data_id)) {$this->min_data_id = $dat_id;}
            if (is_null($this->max_data_id)) {$this->max_data_id = $dat_id;}
            if ($dat_id < $this->min_data_id)  {$this->min_data_id = $dat_id;}
            if ($dat_id > $this->max_data_id)  {$this->max_data_id = $dat_id;}
        }

        $perPage = static::PER_PAGE;
        $totalItems = $this->get_total();
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage,
        ));
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    protected function get_total() {
        global $wpdb;

        if (empty($this->where_clauses)) {
            $sql = "SELECT count (*) as da_count from wp_fl_transaction WHERE 1";
        } else {
            $where_thing = ' (' . implode(' ) AND (', $this->where_clauses) . ' )';


            $sql = "SELECT 
                 count(f.id) as da_count
                 FROM wp_fl_transaction f
                 WHERE 1 
                  AND $where_thing
                ";
        }

        $res = $wpdb->get_results($sql);
        will_throw_on_wpdb_error($wpdb);
        $count = intval($res[0]->da_count);
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
            'txn_id' => 'TXN',
            'type' => 'Transaction Type',
            'da_time_ts' => 'When',
            'amount' => "Amount",
            'description' => 'Description (click to edit)',
            'user_id' => 'User',
            'da_project_id' => 'Project',
            'job_id' => 'Job',
            'milestone_id' => 'Milestone',
            'da_contest_id' => 'Contest',
            'content_id' => 'Content',
            'transaction_post_id' => 'Transaction'

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


    public function get_bulk_actions()
    {
        $actions = [];
        return $actions;
    }


    public function get_sortable_columns()
    {
        return array(
            'txn_id' => array('txn_id', true),
            'type' => array('type', true),
            'da_time_ts' => array('time', true),
            'amount' => array('amount', true)
        );
    }

    /**
     * Get the table data
     *
     * @return array
     */
    private function table_data()
    {
        global $wpdb;

        $per_page = static::PER_PAGE;
        $currentPage = $this->get_pagenum();
        $start_count = ($currentPage - 1) * $per_page;

        $orderby = FLInput::get('orderby','id');
        $order = FLInput::get('order','desc');

        $orderby = 'f.'.$orderby;


        $start_date_escaped = null;
        if ($this->our_filters->start_date_yy_mm_dd) {
            $start_date_escaped = esc_sql($this->our_filters->start_date_yy_mm_dd);
        }

        $end_date_escaped = null;
        if ($this->our_filters->end_date_yy_mm_dd) {
            $end_date_escaped = esc_sql($this->our_filters->end_date_yy_mm_dd);
        }

        if ($this->our_filters->start_date_yy_mm_dd && $this->our_filters->end_date_yy_mm_dd) {
            $where_date = " f.time BETWEEN '$start_date_escaped  00:00:01' AND '$end_date_escaped 23:59:59' ";
        } elseif ($this->our_filters->start_date_yy_mm_dd) {
            $where_date = " f.time >= '$start_date_escaped'  ";
        } elseif ($this->our_filters->end_date_yy_mm_dd) {
            $where_date = " f.time <= '$end_date_escaped'  ";
        } else {
            $where_date = '1';
        }
        $this->where_clauses[] = $where_date;

        if ($this->our_filters->transaction_type) {
            $escaped_type = esc_sql($this->our_filters->transaction_type);
            $where_type =  "f.type = '$escaped_type' ";
        } else {
            $where_type = '1';
        }
        $this->where_clauses[] = $where_type;

        if ($this->our_filters->user_id) {
            $escaped_user_id = $this->our_filters->user_id; //already cast to int
            $where_user =  "f.user_id = $escaped_user_id ";
        } else {
            $where_user = '1';
        }
        $this->where_clauses[] = $where_user;

        if ($this->our_filters->txn_id) {
            $escaped_txn = esc_sql($this->our_filters->txn_id);
            $where_type =  "f.txn_id = '$escaped_txn' ";
        } else {
            $where_type = '1';
        }
        $this->where_clauses[] = $where_type;

        $where_thing = ' ('. implode(' ) AND (',$this->where_clauses). ' )';


        $sql_for_data = "SELECT 
                 f.ID as transaction_id, 
                 f.txn_id, f.amount, f.payment_status, f.description, f.type, f.user_id, f.user_id_added_by, f.project_id,
                 f.job_id, f.milestone_id, f.content_id, f.proposal_id,
                  f.transaction_post_id, UNIX_TIMESTAMP(f.time) as da_time_ts,
                 meta_job_type.meta_value as fl_job_type,
                 meta_title.meta_value as fl_title,
                 content.content_title,
                 job.job_status, job.title as job_title,
                 job_meta.meta_value as bid_price,
                 milestone.status as milestone_status,
                 trans_user_id.user_nicename as user_nicename,
                 meta_trans_txn.meta_value as transaction_post_txn_id,
                 prop.by_user as proposal_user_id
                 FROM wp_fl_transaction f
                 LEFT JOIN wp_postmeta meta_job_type ON meta_job_type.post_id = f.project_id 
                          AND meta_job_type.meta_key =  'fl_job_type' 
                 LEFT JOIN wp_postmeta meta_title ON meta_title.post_id = f.project_id 
                          AND meta_title.meta_key =  'project_title' 
                  LEFT JOIN wp_postmeta meta_trans_txn ON meta_trans_txn.post_id = f.transaction_post_id 
                    AND meta_trans_txn.meta_key =  '".FLTransactionLookup::META_KEY_MODIFIED_TRANSACTION_ID."' 
                 LEFT JOIN wp_linguist_content content on f.content_id = content.id
                 LEFT JOIN  wp_fl_job job ON f.job_id = job.ID
                 LEFT JOIN wp_commentmeta job_meta ON job_meta.comment_id = job.bid_id AND job_meta.meta_key = 'bid_price'
                 LEFT JOIN  wp_fl_milestones milestone on f.milestone_id = milestone.ID
                 LEFT JOIN  wp_users trans_user_id  on f.user_id = trans_user_id.ID
                 LEFT JOIN  wp_proposals prop  on prop.id = f.proposal_id
                 WHERE 1 
                  AND $where_thing
                 ORDER BY $orderby $order
                 LIMIT $start_count, $per_page";

//        will_send_to_error_log('sql stuff',$sql_for_data);
        will_throw_on_wpdb_error($wpdb,'getting wp_fl_transaction page');
        $data = $wpdb->get_results($sql_for_data,ARRAY_A);

        $result = array();

        $switcher = new FLSwitchUserHelper();

        foreach ($data as $key => $row) {

            $integer_or_null_values_only = ['transaction_id','user_id','project_id','job_id','proposal_id',
                                            'milestone_id','content_id','transaction_post_id','da_time_ts',
                                            'proposal_user_id'];

            $float_or_null_values_only = ['amount','bid_price'];

            //code-notes wpdb returns strings for numbers and floats, lets normalize
            foreach ($row as $row_key => $row_value) {
                if (in_array($row_key,$integer_or_null_values_only)) {
                    $row[$row_key] = (is_null($row_value) ? null : intval($row_value));
                }

                if (in_array($row_key,$float_or_null_values_only)) {
                    $row[$row_key] = (is_null($row_value) ? null : floatval($row_value));
                }
            }
            $user_id_to_switch_to = $row['user_id'];
            $row['da_contest_id'] = null;
            $row['da_project_id'] = null;
            if ($row['fl_job_type'] === 'contest') { $row['da_contest_id'] = $row['project_id'];}
            if ($row['fl_job_type'] === 'project') { $row['da_project_id'] = $row['project_id'];}

            $row['calc_content_link'] = null;
            if ($row['content_id']) {
                $the_content_link = site_url() . '/content/?lang=en&mode=view&content_id=' . FreelinguistContentHelper::encode_id($row['content_id']) ;
                $row['calc_content_link']  = $switcher->generate_switch_redirect_url($user_id_to_switch_to,$the_content_link);
            }


            $row['calc_project_link'] = null;
            if ($row['da_project_id'] && $row['job_id'] && $row['job_title']) {
                $row['calc_project_link'] = get_the_permalink($row['da_project_id'])."&job_id=".$row['job_title'];
            } elseif ($row['da_project_id'] ) {
                $row['calc_project_link'] = get_the_permalink($row['da_project_id']);
            }
            $row['calc_project_link']  = $switcher->generate_switch_redirect_url($user_id_to_switch_to,$row['calc_project_link']);

            $row['calc_contest_link'] = null;
            if ($row['da_contest_id']) {
                $row['calc_contest_link'] = get_the_permalink($row['da_contest_id']).'&action=participants-proposals';
                $row['calc_contest_link']  = $switcher->generate_switch_redirect_url($user_id_to_switch_to,$row['calc_contest_link']);
            }

            $row['calc_user_link'] = null;
            if ($row['user_id'] && $row['user_nicename']) {
                $row['calc_user_link'] = site_url().'/user-account/?lang=en&profile_type=translator&user='.$row['user_nicename'];
            }

            $row['calc_transaction_post_link'] = null;
            if ($row['transaction_post_id']) {
                $row['calc_transaction_post_link'] = get_edit_post_link($row['calc_transaction_post_link']);
            }

            /*
            * contestRejected ,contestDisputeRejected,contestDisputePartlyApproved,contestRejected link is the customer view of the awarded proposal
            *   example: http://test.com/job/22327222/?lang=en&action=winner-proposal&linguist=3588&proposalId=152143
            *
            *
            * contestWinner, claim_award,contestDisputeApproved is the freelancer view of the awarded proposal
            *
            *  contestWinner ,contestDisputeApproved example: http://test.com/job/22327157/?lang=en&action=winner-proposal&proposal_id=152123
            * claim_award go to general freelancer proposal page, example:
            *      http://test.com/job/22327223/?lang=en&action=proposals&contest-pro=true&rate=&proposal_id=152145&linguist=3588
            *
            */

            $row['calc_proposal_link'] = null;
            $the_proposal_id = $row['proposal_id'];
            $proposal_user_id = $row['proposal_user_id'];
            if ($row['proposal_id']) {
                //make separate calc_proposal_link, put calc_contest_link into calc_contest_link_general and then set calc_contest_link to the calc_proposal_link
                switch ($row['type']) {
                    //user id is the customer
                    case 'contestRejected':
                    case 'contestDisputeRejected':
                    case 'contestDisputePartlyApproved': {
                        $row['calc_contest_link_general'] = $row['calc_contest_link'];
                        //example: http://test.com/job/22327222/?lang=en&action=winner-proposal&linguist=3588&proposalId=152143
                        $the_proposal_link = get_the_permalink($row['da_contest_id']).
                                "&action=winner-proposal&linguist=$proposal_user_id&proposalId=$the_proposal_id";

                        $row['calc_proposal_link'] = $switcher->generate_switch_redirect_url($user_id_to_switch_to,$the_proposal_link);
                        $row['calc_contest_link'] = $row['calc_proposal_link'];
                        break;
                    }

                    // user id is the freelancer

                    case 'contestDisputeApproved':
                    case 'disputeRaise':
                    case 'contestWinner': {
                        $row['calc_contest_link_general'] = $row['calc_contest_link'];
                        //example http://test.com/job/22327157/?lang=en&action=winner-proposal&proposal_id=152123
                        $the_proposal_link = get_the_permalink($row['da_contest_id']).
                                "&action=winner-proposal&proposal_id=$the_proposal_id";
                        $row['calc_proposal_link']= $switcher->generate_switch_redirect_url($user_id_to_switch_to,$the_proposal_link);
                        $row['calc_contest_link'] = $row['calc_proposal_link'];
                        break;
                    }

                    case 'claim_award': {
                        $row['calc_contest_link_general'] = $row['calc_contest_link'];
                        //example http://test.com/job/22327223/?lang=en&action=proposals&contest-pro=true&rate=&proposal_id=152145&linguist=3588
                        $the_proposal_link  = get_the_permalink($row['da_contest_id']).
                            "&action=proposals&&contest-pro=true&rate=&proposal_id=$the_proposal_id&linguist=$proposal_user_id";
                        $row['calc_proposal_link'] = $switcher->generate_switch_redirect_url($user_id_to_switch_to,$the_proposal_link);
                        $row['calc_contest_link'] = $row['calc_proposal_link'];
                    }
                    default: {
                        //keep contest link like it is
                    }
                }
            }




            $result[] = $row;
        }
        return $result;
    }

    /**
     * fills in for a 'cb' column name
     *
     * @param  array $item Data
     *
     */

    function column_cb($item) {}

    public function column_default($item, $column_name)
    {
        $my_wp_fl_transaction_id = $item['transaction_id'];
        switch ($column_name) {

            case 'txn_id':{

                    $my_txn_id = $item['txn_id']? $item['txn_id'] : '(no txn id)';
                    return "<span class='fl-admin-txn fl-transaction-id' data-txid='$my_wp_fl_transaction_id'>$my_txn_id</span>".
                            "<span class='fl-admin-small-id-line'>$my_wp_fl_transaction_id</span>";
                }
            case 'type': {
                    $my_type = $item['type'] ? $item['type'] : '(no type)';
                    return "<span class='fl-admin-txn'>$my_type</span>";
                }
            case 'da_time_ts': {
                    $my_da_time_ts = $item['da_time_ts'];
                    return "<span class='fl-admin-txn a-timestamp-full-date-time' data-ts='$my_da_time_ts'></span>";
                }
            case 'amount': {
                    $my_amount = $item['amount'];
                    $my_formatted_amount = $this->usd_formatter->formatCurrency($my_amount, 'USD');
                    return "<span class='fl-admin-txn'>$my_formatted_amount</span>";
                }
            case 'description': {
                    $my_description = $item['description'];
                    return "<span class='fl-admin-txn fl-txn-admin-description' data-txid='$my_wp_fl_transaction_id'>$my_description</span>";
                }
            case 'user_id': {
                    $my_user_id = $item['user_id'];
                    if (empty($my_user_id)) { return '';}
                    $my_nice_name = $item['user_nicename'];
                    $my_user_link = $item['calc_user_link'] ? $item['calc_user_link'] :  '#' ;
                    return "<span class='fl-admin-txn'><a href='$my_user_link' target='_blank' data-id='user_id' title='User Link'>$my_nice_name</a></span>".
                        "<span class='fl-admin-small-id-line'>$my_user_id</span>";
                }
            case 'da_project_id':   {
                    $my_project_id = $item['da_project_id'];
                    if (empty($my_project_id)) {return '';}
                    $my_project_title = $item['fl_title'] ? $item['fl_title'] : '(unnamed)';
                    $my_project_link = $item['calc_project_link'] ?  $item['calc_project_link'] : '#' ;
                    return "<span class='fl-admin-txn'><a href='$my_project_link' target='_blank' data-id='da_project_id' title='Project Link'>$my_project_title</a></span>";
                }
            case 'job_id': {
                $my_job_id = $item['job_id'];
                if (empty($my_job_id)) {return '';}
                $my_bid_amount = '(no bid set)';
                if ($item['bid_price']) {
                    $my_bid_amount = 'Bid: '.$this->usd_formatter->formatCurrency($item['bid_price'], 'USD');
                }
                return "<span class='fl-admin-txn'>$my_bid_amount</span>";
            }
            case 'milestone_id':  {
                $my_milestone_id = $item['milestone_id'];
                if (empty($my_milestone_id)) {return '';}
                $my_milestone_status = $item['milestone_status'] ? $item['milestone_status'] : '(no status)';
                return "<span class='fl-admin-txn'>$my_milestone_status</span>";
            }
            case 'da_contest_id': {
                $my_contest_id = $item['da_contest_id'];
                if (empty($my_contest_id)) {return '';}
                $my_contest_title = $item['fl_title'] ? $item['fl_title'] : '(unnamed)';
                $my_contest_link = $item['calc_contest_link'] ?  $item['calc_contest_link'] : '#' ;
                return "<span class='fl-admin-txn'><a href='$my_contest_link' target='_blank' data-id='$my_contest_id' title='Contest Link'>$my_contest_title</a></span>";
            }
            case 'content_id': {
                $my_content_id = $item['content_id'];
                if (empty($my_content_id)) {return '';}
                $my_content_title = $item['content_title'];
                $my_content_link = $item['calc_content_link']?  $item['calc_content_link']: '#';
                return "<span class='fl-admin-txn'><a href='$my_content_link' target='_blank' data-id='$my_content_id' title='Content Link'>$my_content_title</a></span>";
            }
            case 'transaction_post_id': {
                $my_transaction_post_id = $item['transaction_post_id'];
                if (empty($my_transaction_post_id)) {return '';}
                $my_transaction_post_guid = $item['transaction_post_txn_id'];
                $my_transaction_link = $item['calc_transaction_post_link']?  $item['calc_transaction_post_link']: '#';
                return "<span class='fl-admin-txn'><a href='$my_transaction_link' target='_blank' data-id='$my_transaction_post_id'  title='Transaction Post Link'>$my_transaction_post_guid</a></span>";
            }

            default:
                return '<pre>'.print_r($item, true).'</pre>';
        }
    }



}

?>