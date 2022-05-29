<?php
/*
 * Plugin Name: CheckWallet_Wp_List_Table Table
 * Description: checkWallet_Wp_List_Table
 * Plugin URI: http://www.paulund.co,
 * Author: Lakhvidner
 * Author URI: http://www.Lakhvidner.com
 * Version: 1.0
 */

/*
    * current-php-code 2021-Jan-8
    * input-sanitized :
    * current-wp-template:  admin-screen  for wallet amount
*/


/**
 * Paulund_Wp_List_Table class will create the page to load the table
 */
class AdminPageCheckWalletAmount
{
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
        add_action('admin_menu', array($this, 'add_menu_CheckWallet_list_table_page'));
    }

    /**
     * Menu item will allow us to load the page to display the table
     */
    public function add_menu_CheckWallet_list_table_page()
    {
        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'User Wallet', 'User Wallet', 'manage_options',
                'freelinguist-admin-user-wallet', array($this, 'list_table_page'), $this->position);
        } else {
            add_menu_page('User Wallet', 'User Wallet', 'manage_options',
                'freelinguist-admin-user-wallet', array($this, 'list_table_page'), 'dashicons-info');
        }

    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page()
    { ?>
        <style>
            /*noinspection CssUnusedSymbol*/
            a.fl_admin_wallet_transaction_log_txn {
                display: block;
                font-size: 80%;
                color: lightgrey;
            }
        </style>
        <div class="wrap"><br>
        <span class="bold-and-blocking large-text">Wallet Info</span>
        <?php
        if (isset($_REQUEST['author'])) {
            $user_id = (int)$_REQUEST['author'];
            $total_user_amount = get_user_meta($user_id, 'total_user_balance', true);
            $FREE_credits = get_user_meta($user_id, 'FREE_credits', true);
        } else {
            $total_user_amount = 0;
            $FREE_credits = 0;
        }
        ?>
        <div class="transaction_total large-text">
            <span style="text-align: center;">
                <b>Total Amount</b>: $ <?php echo amount_format($total_user_amount); ?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <b>Total Free Credit</b>: $ <?php echo amount_format($FREE_credits); ?>
            </span>
        </div>
        <?php
        $CheckWallet_List_Table = new CheckWallet_List_Table();
        $CheckWallet_List_Table->prepare_items();
        $CheckWallet_List_Table->display();
    }
}


// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


/**
 * Create a new table class that will extend the WP_List_Table
 */
class CheckWallet_List_Table extends WP_List_Table
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
        $this->per_page = 25;
        $this->current_page = $this->get_pagenum();

        $data = $this->table_data();
        usort($data, array(&$this, 'sort_data'));

        //print_r($currentPage); exit;
        $totalItems = $this->get_total();
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $this->per_page
        ));
        //$data = array_slice($data, (($this->current_page - 1) * $this->per_page), $this->per_page);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    protected function get_total() {
        global $wpdb;
        $user_id = (int)FLInput::get('author');
        $status_public_int = FLTransactionLookup::POST_STATUS_PUBLISH;
        $sql = "select count(*) as da_count FROM wp_transaction_lookup WHERE user_id =$user_id AND post_status = $status_public_int";

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
            'transaction_id' => 'Transaction Id',
            'transaction_title' => 'Transaction Title',
            'transactionType' => 'Transaction Type',
            'transactionStatus' => 'Transaction Status',
            'transactionAmount' => 'Transaction Amount',
            'transaction_related_to' => 'Transaction Related To',
            'author' => 'Author',
            'date' => 'Date',
            'post_id' => 'Post ID'
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
        return array(
            'transaction_id' => array('transaction_id', true),
            'transaction_title' => array('transaction_title', true),
            'transactionType' => array('transactionType', true),
            'transactionStatus' => array('transactionStatus', true),
            'transactionAmount' => array('transactionAmount', true),
            'date' => array('date', true),
            'post_id' => array('post_id', true),
        );
    }

    /**
     * Get the table data
     *
     * @return array
     */
    private function table_data()
    {
        $lang = FLInput::get('lang','en');
        $usd_formatter = numfmt_create( 'en_US', NumberFormatter::CURRENCY );
        $data_array = array();
        if (isset($_REQUEST['author'])) {
            $user_id = $_REQUEST['author'];
            $user_detail = get_userdata($user_id);
            if (!empty($user_detail)) {

                $wallet_history_user_id = $user_id;
                $wallet_history_page_size = $this->per_page;
                $wallet_history_page_number = $this->current_page;
                $wallet_history_sort = 'by_date_desc';
                $wallet_history_transaction_types = [];
                $wallet_history_post_status = [FLTransactionLookup::POST_STATUS_PUBLISH];
                $lookups = FLTransactionLookup::get_rows($wallet_history_user_id,$wallet_history_page_size,$wallet_history_page_number,
                    $wallet_history_sort,$wallet_history_transaction_types,$wallet_history_post_status,true);

                foreach ($lookups as $look) {
                    $transactionType = FLTransactionLookup::transaction_type_int_to_enum($look->transaction_type);


                    $related_url = '';
                    if ($look->related_post_id) {
                        $related_url =  add_query_arg([ 'lang' => $lang], get_edit_post_link($look->related_post_id));
                    }
                    $post_url =  add_query_arg([ 'lang' => $lang], get_edit_post_link($look->post_id));

                    $formatted_amount = $usd_formatter->formatCurrency($look->transaction_amount,'USD');

                    $author_info = get_da_name($look->user_id);

                    $post_status = FLTransactionLookup::post_status_int_to_enum($look->post_status);
                    $transaction_status = fl_get_payment_post_status_string($look->post_id,true,$post_status,null);
                    //http://test.com/wp-admin/admin.php?page=freelinguist-admin-txn&lang=en&raw_text_search=1615131254-u-156601
                    $transaction_log_url = '';
                    if ($look->related_fl_transaction_txn) {
                        $mini_me_txn_url_base = menu_page_url(AdminPageTxn::PAGE_STUB,false);
                        $mini_me_txn_url_base =  add_query_arg([ 'lang' => $lang], $mini_me_txn_url_base);
                        $transaction_log_url = $mini_me_txn_url_base.'&raw_text_search='.$look->related_fl_transaction_txn;
                    }

                    $data_array[] = array(
                        'transaction_id' => $look->txn,
                        'transaction_title' => $look->transaction_reason,
                        'transactionType' => $transactionType,
                        'transactionStatus' => $transaction_status,
                        'transactionAmount' => $formatted_amount,
                        'transaction_related_to' => $look->related_txn,
                        'transaction_related_to_link' => $related_url,
                        'author' =>  $author_info,
                        'date' => $look->post_created_at_ts,
                        'post_id' => $look->post_id,
                        'post_id_link' => $post_url,
                        'transaction_log_txn' => $look->related_fl_transaction_txn,
                        'transaction_log_id' => $look->related_fl_transaction_id,
                        'transaction_log_url' => $transaction_log_url
                    );
                    //echo "<pre>"; print_r($data_array); exit;
                }

            }
        }

        return $data_array;

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
            case 'transaction_id':{
                $tid_out = '<span style="display: block" >'.$item['transaction_id'].'</span>';;

                //transaction_log_txn  transaction_log_id transaction_log_url
                if ($item['transaction_related_to']) {
                    $tid_out .= '<a href = "' . $item['transaction_log_url'] .
                        '"  target="_blank" data-tlog_id="'.$item['transaction_log_id'].'"  class="fl_admin_wallet_transaction_log_txn">' .
                        $item['transaction_log_txn'] . '</a>';
                }
                return $tid_out;
            }
            case 'transaction_title':
            case 'transactionType':
            case 'transactionStatus':
            case 'transactionAmount':
            case 'author':
            case 'action':
                return $item[$column_name];
            case 'date':
                return '<span class="a-timestamp-full-date-time" data-ts="'.$item[$column_name].'"></span>';
                //http://test.com/wp-admin/post.php?post=335112&action=edit&lang=en
            case 'transaction_related_to':
                if ($item['transaction_related_to']) {
                    return '<a href = "'.$item['transaction_related_to_link'].'"  target="_blank" >'.$item['transaction_related_to'].'</a>';
                } else {
                    return '';
                }
            case 'post_id': {
                if ($item['post_id']) {
                    return '<a href = "'.$item['post_id_link'].'" target="_blank">'.$item['post_id'].'</a>';
                } else {
                    return '';
                }
            }
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
        $orderby = 'transaction_id';
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