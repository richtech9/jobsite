<?php
/*
 * Plugin Name: RefillHistory Table
 * Description: RefillHistory_Wp_List_Table
 * Plugin URI: http://www.paulund.co,
 * Author: Lakhvidner
 * Author URI: http://www.Lakhvidner.com
 * Version: 1.0
 */

/*
 * current-php-code 2021-Jan-10
 * input-sanitized :
 * current-wp-template:  admin-screen  for looking at refill history
 */



/**
 * Paulund_Wp_List_Table class will create the page to load the table
 */
class AdminPageRefillHistory
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
        add_action('admin_menu', array($this, 'add_menu_RefillHistory_list_table_page'));
    }

    /**
     * Menu item will allow us to load the page to display the table
     */
    public function add_menu_RefillHistory_list_table_page()
    {
        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'Refill History List', 'Refill History List', 'manage_options',
                'freelinguist-admin-refill-history', array($this, 'list_table_page'), $this->position);
        } else {
            add_menu_page('Refill History List', 'Refill History List', 'manage_options',
                'freelinguist-admin-refill-history', array($this, 'list_table_page'), 'dashicons-info');
        }

    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page()
    { ?>
        <div class="wrap"><br>
            <span class="bold-and-blocking large-text">Refill History</span>

            <div class="data_filter" style="float:right">
                <input type="text" readonly id="dt1"
                       value="<?php echo isset($_REQUEST['filter_by_date_from']) ? $_REQUEST['filter_by_date_from'] : ''; ?>"
                       placeholder="From Date" name="dt1">
                <input type="text" readonly id="dt2"
                       value="<?php echo isset($_REQUEST['filter_by_date_to']) ? $_REQUEST['filter_by_date_to'] : ''; ?>"
                       placeholder="To Date" name="dt2">
                <input type="button" class="button" id="filter_by_date" name="filter_by_date" value="Filter">
                <script>
                    jQuery(function ($) {

                            $("#dt1").datepicker({
                                numberOfMonths: 1,
                                dateFormat: 'yy-mm-dd',
                                onSelect: function (selected) {
                                    var dt = new Date(selected);
                                    dt.setDate(dt.getDate() + 1);
                                    $("#dt2").datepicker("option", "minDate", dt);
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
                </script>
            </div>
        </div>
        <script>
            jQuery(function () {
                jQuery('#filter_by_date').click(function () {
                    var filter_by_date_from = jQuery('#dt1').val();
                    var filter_by_date_to = jQuery('#dt2').val();
                    var url = '<?php echo admin_url(); ?>' + 'admin.php?page=freelinguist-admin-refill-history&filter_by_date_from=' + filter_by_date_from + '&filter_by_date_to=' + filter_by_date_to;
                    window.location.href = url;
                    return false;
                });
            });
        </script>
        <?php
        $RefillHistory_List_Table = new RefillHistory_List_Table();
        $RefillHistory_List_Table->prepare_items();
        $RefillHistory_List_Table->display();
    }
}


// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


/**
 * Create a new table class that will extend the WP_List_Table
 */
class RefillHistory_List_Table extends WP_List_Table
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
        $perPage = 40;
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
            'id' => 'User ID',
            'display_name' => 'Name',
            'user_email' => 'Email',
            'payment_amount' => 'Amount',
            'description' => 'Description',
            'refill_by' => 'Refill By',
            'created_time' => 'Date',
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
        return array('user_email' => array('user_email', true), 'refill_by' => array('refill_by', true), 'created_time' => array('created_time', true));
    }

    /**
     * Get the table data
     *
     * @return array
     */
    private function table_data()
    {
        global $wpdb;
        $user_info = get_userdata(get_current_user_id());


        $user_query_and = '1';
        if (in_array('cashier_sub_admin', $user_info->roles) || in_array('evaluation_sub_admin', $user_info->roles) || in_array('message_sub_admin', $user_info->roles)) {
            $author__in = getReportedUserByUserId();
            $author__in = implode(',', $author__in);
            $user_query_and = " payment_history.user_id  IN ( $author__in ) ";
        }
        if (isset($_REQUEST['s']) && !empty($_REQUEST['s'])) {
            $search_d = $_REQUEST['s'];
            $data = $wpdb->get_results(
                    "SELECT  users.display_name,users.user_email,payment_history.description,payment_history.user_id as id,
                                      payment_history.payment_amount,payment_history.refill_by,payment_history.created_time 
                             FROM wp_payment_history payment_history 
                             JOIN wp_users users ON users.ID =payment_history.user_id 
                             WHERE $user_query_and AND  payment_history.refill_by AND
                                    (users.user_email LIKE '%" . $search_d . "%' OR users.display_name LIKE '%" . $search_d . "%' )
                             ORDER BY payment_history.id
                     ", ARRAY_A);
        } elseif (isset($_REQUEST['filter_by_date_from']) && !empty($_REQUEST['filter_by_date_from'])) {
            $filter_by_date_from = $_REQUEST['filter_by_date_from'] . ' 00:00:00';
            $filter_by_date_to = $_REQUEST['filter_by_date_to'] . ' 00:00:00';
            $data = $wpdb->get_results(
                    "SELECT users.display_name,users.user_email,payment_history.description,payment_history.user_id as id,
                                     payment_history.payment_amount,payment_history.refill_by,payment_history.created_time 
                             FROM wp_payment_history payment_history 
                             JOIN wp_users users ON users.ID =payment_history.user_id 
                             WHERE $user_query_and AND (payment_history.created_time BETWEEN '" . $filter_by_date_from . "' AND '" . $filter_by_date_to . "')
                             ORDER BY payment_history.created_time
                            ", ARRAY_A);
        } else {
            $data = $wpdb->get_results(
                    "SELECT users.display_name,users.user_email,payment_history.description,payment_history.user_id as id,
                                     payment_history.payment_amount,payment_history.refill_by,payment_history.created_time 
                             FROM wp_payment_history payment_history 
                             JOIN wp_users users ON users.ID =payment_history.user_id
                             where $user_query_and  
                             ORDER BY payment_history.id
                             ", ARRAY_A);
        }
        //echo "<pre>"; print_r($data); exit;
        foreach ($data as $key => $value) {
            if (!empty($value['refill_by']) || $value['refill_by'] != 0) {
                $user_info = get_userdata($value['refill_by']);
                $data[$key]['refill_by'] = $user_info->user_login;
            } else {
                $data[$key]['refill_by'] = '-';
            }
        }
        //exit;
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
            case 'id':
            case 'display_name':
            case 'user_email':
            case 'payment_amount':
            case 'description':
            case 'refill_by':
            case 'created_time':
            case 'action':
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
        $orderby = 'id';
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