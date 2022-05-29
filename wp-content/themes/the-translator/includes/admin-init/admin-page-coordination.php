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
 * input-sanitized :refill_amount
 * current-wp-template:  admin-screen  for coordination
 */



/**
 * Paulund_Wp_List_Table class will create the page to load the table
 */
class AdminPageCoordination
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
        add_action('admin_menu', array($this, 'add_menu_Coordination_list_table_page'));
    }

    /**
     * Menu item will allow us to load the page to display the table

     */
    public function add_menu_Coordination_list_table_page()
    {
        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'Coordination', 'Coordination', 'manage_options',
                'freelinguist-admin-coordination', array($this, 'list_table_page'), $this->position);
        } else {
            add_menu_page('Coordination', 'Coordination', 'manage_options',
                'freelinguist-admin-coordination', array($this, 'list_table_page'), 'dashicons-format-aside');
        }

    }

    public function get_particular_super_sub_admin_country_list($user_id){
        $all_countries = get_countries();
        $assign_country_from = get_user_meta( $user_id, 'assign_country_from', true );
        $assign_country_to = get_user_meta( $user_id, 'assign_country_to', true );
        if(!empty($assign_country_to)){
            $user_country = array_slice($all_countries, $assign_country_from, $assign_country_to-$assign_country_from+1,true);
            $data  =array();
            $i = $assign_country_from;
            foreach ($user_country as $key => $value) {
                $data[$i] = $value;
                $i++;
            }
            $user_country = $data;
        }else{
            $user_country = array();
        }
        return $user_country;
    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page()
    { ?>
        <div class="wrap"><br>
        <span class="bold-and-blocking large-text">Sub Admin</span>
        <?php

        if (isset($_REQUEST['add_coverage_button'])) {
            if (isset($_REQUEST['sub_admin']) || isset($_REQUEST['country_from']) || isset($_REQUEST['country_to']) || isset($from_processing_id['from_processing_id']) || isset($_REQUEST['to_processing_id'])) {
                if ($_REQUEST['sub_admin'] != '' && $_REQUEST['country_from'] != '' && $_REQUEST['country_to'] != '' && $_REQUEST['from_processing_id'] != '' && $_REQUEST['to_processing_id'] != '') {
                    global $wpdb;
                    $reported_to = get_current_user_id();
                    $sub_admin = $_REQUEST['sub_admin'];
                    $country_from = $_REQUEST['country_from'];
                    $country_to = $_REQUEST['country_to'];
                    $from_processing_id = $_REQUEST['from_processing_id'];
                    $to_processing_id = $_REQUEST['to_processing_id'];

                    $wpdb->insert(
                        'wp_coordination',
                        array(
                            'reported_to' => $reported_to,
                            'user_id' => $sub_admin,
                            'country_from' => $country_from,
                            'country_to' => $country_to,
                            'from_processing_id' => $from_processing_id,
                            'to_processing_id' => $to_processing_id,
                            'date' => current_time('mysql')
                        )
                    );
                    echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                <p><strong style="">Updated Successfully.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

                } else {
                    echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                <p><strong style="color:red">All fields are required.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

                }
            } else {
                echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
            <p><strong style="color:red">All fields are required.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

            }
        }
        if (isset($_REQUEST['delete_coverage_area'])) {
            global $wpdb;
            $delete_coverage_area = $_REQUEST['delete_coverage_area'];
            $current_user = get_current_user_id();
            $data_count = $wpdb->get_var("SELECT COUNT(*) FROM wp_coordination where id=$delete_coverage_area and reported_to = $current_user");
            if ($data_count > 0) {
                $wpdb->delete('wp_coordination', array('id' => $delete_coverage_area));
            }
            $current_user = wp_get_current_user();
            $current_user_role = $current_user->roles[0];
            if ($current_user_role == 'administrator' || $current_user_role == 'administrator_for_client') {
                $wpdb->delete('wp_coordination', array('id' => $delete_coverage_area));
            }
        }

        $current_user = wp_get_current_user();
        $current_user_role = $current_user->roles[0];
        if ($current_user_role == 'administrator' || $current_user_role == 'administrator_for_client') {
            // echo '<div class="notice" style="padding:10px;color:#FFBA00"> <span style="height:50px;">Admin can only view the coordination detail.</span></div>';
            $args = array(
                'role' => '',
                'role__in' => array('cashier_sub_admin', 'evaluation_sub_admin', 'message_sub_admin', 'meditation_sub_admin'),
                'role__not_in' => array(),
                'meta_compare' => '',
                'meta_query' => array(),
                'date_query' => array(),
                'include' => array(),
                'exclude' => array(),
                'orderby' => 'login',
                'order' => 'ASC',
                'offset' => '',
                'search' => '',
                'number' => '',
                'count_total' => false,
                'fields' => 'all',
                'who' => ''
            );
            $sub_Admin_user = get_users($args);
        } else {
            $args = array(
                'role' => '',
                'role__in' => array(),
                'role__not_in' => array(),
                'meta_key' => 'reported_to',
                'meta_value' => get_current_user_id(),
                'meta_compare' => '',
                'meta_query' => array(),
                'date_query' => array(),
                'include' => array(),
                'exclude' => array(),
                'orderby' => 'login',
                'order' => 'ASC',
                'offset' => '',
                'search' => '',
                'number' => '',
                'count_total' => false,
                'fields' => 'all',
                'who' => ''
            );
            $sub_Admin_user = get_users($args);
        }
        ?>
        <select name="sub_admin" id="sub_admin" style="float:left" title="sub admin">
            <option value=""> -- Select sub admin --</option>
            <?php foreach ($sub_Admin_user as $key) { ?>
                <?php if (isset($_REQUEST['sub_admin']) && $_REQUEST['sub_admin'] == $key->ID) { ?>
                    <option selected
                            value="<?php echo $key->ID; ?>"><?php echo $key->user_email . ' (' . $key->user_login . ')'; ?></option>
                <?php } else { ?>
                    <option value="<?php echo $key->ID; ?>"><?php echo $key->user_email . ' (' . $key->user_login . ')'; ?></option>
                <?php } ?>
            <?php } ?>
        </select>
        <br><br>
        <script>
            jQuery(function () {
                jQuery("#sub_admin").change(function () {
                    var valueis = this.value;
                    if (valueis !== '') {
                        var url = '<?php echo admin_url(); ?>' + 'admin.php?page=freelinguist-admin-coordination&sub_admin=' + valueis;
                        window.location.href = url;
                    }
                    return false;
                });

            });
        </script>
        <?php
        $current_user = wp_get_current_user();
        $current_user_role = $current_user->roles[0];
        if ($current_user_role != 'administrator' && $current_user_role != 'administrator_for_client') {
            ?>
            <?php if (isset($_REQUEST['sub_admin'])) { ?>
                <?php $user_country = $this->get_particular_super_sub_admin_country_list(get_current_user_id()); ?>
                <?php $all_processing_id = get_processing_id(); ?>
                <style> .dont_use_selectpicker {
                        min-width: 350px;
                    } </style>
                <form action="" method="post" name="add_coverage_form">
                    <table>
                        <tr>
                            <td colspan="3"><span class="bold-and-blocking large-text"> Country</span></td>
                        </tr>
                        <tr>
                            <td>
                                <select onchange='getToCountryBySuperAdmin(this.value)' class="dont_use_selectpicker"
                                        name="country_from" title="Country From">
                                    <option value="">-- Select Country</option>
                                    <?php
                                    foreach ($user_country as $key => $value) {
                                        ?>
                                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <p class="description"> From country</p>
                            </td>
                            <td>
                                <div class="country_to_div">
                                    <select class="dont_use_selectpicker" name="country_to" id="country_to" title="Country To">
                                        <!-- <option value="All">-- Select coverage to</option> -->

                                    </select>
                                    <p class="description"> To country</p>

                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3"><span class="bold-and-blocking large-text"> Processing ID</span></td>
                        </tr>
                        <tr>
                            <td>
                                <select onchange='getToProcessingIdBySuperAdmin(this.value)' title="Processing ID"
                                        class="dont_use_selectpicker" name="from_processing_id">
                                    <option value="">-- Select Proccessing ID --</option>
                                    <?php
                                    foreach ($all_processing_id as $key => $value) {
                                        ?>
                                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <p class="description"> From Processing ID</p>
                            </td>
                            <td>
                                <div class="to_processing_id_div">
                                    <select class="dont_use_selectpicker" name="to_processing_id" id="to_processing_id" title="To Processing ID">
                                    </select>
                                    <p class="description"> To Processing ID</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><br>
                                <input type="submit" class="button button-primary" name="add_coverage_button"
                                       value="ADD" id="add_coverage_button">
                            </td>
                        </tr>
                    </table>
                </form>
                </div>
                <?php

            } else {
                echo "<table class='widefat fixed'><span style='text-align: center;background-color: #fff;    padding-bottom: 35px;'>".
                    "<span class='bold-and-blocking large-text'><br><br><br><br>Select any sub admin</span></span></table>";
            }
        }
        $Coordination_List_Table = new Coordination_List_Table();
        $Coordination_List_Table->prepare_items();
        $Coordination_List_Table->display();
    }
}


// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


/**
 * Create a new table class that will extend the WP_List_Table
 */
class Coordination_List_Table extends WP_List_Table
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
            'country_from' => 'Country From',
            'country_to' => 'Country To',
            'from_processing_id' => 'From processing id',
            'to_processing_id' => 'To processing id',
            'user_id' => 'User',
            'reported_to' => 'Reported To',
            'delete' => 'Action'
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
        return array();
    }

    /**
     * Get the table data
     *
     * @return array
     */
    private function table_data()
    {
        $data = array();
        global $wpdb;
        if (isset($_REQUEST['sub_admin'])) {
            $sub_admin_is = $_REQUEST['sub_admin'];
            $data = $wpdb->get_results("SELECT * FROM wp_coordination where user_id=$sub_admin_is ORDER BY date", ARRAY_A);
        }

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $sub_admin_is = $_REQUEST['sub_admin'];
                $delete_url = admin_url() . 'admin.php?page=freelinguist-admin-coordination&sub_admin=' . $sub_admin_is . '&delete_coverage_area=' . $value['id'];

                $data[$key]['country_from'] = get_country_by_index($data[$key]['country_from']);
                $data[$key]['country_to'] = get_country_by_index($data[$key]['country_to']);
                $user_info = get_userdata($data[$key]['user_id']);
                $data[$key]['user_id'] = $user_info->user_login . ')';
                $reported_user_info = get_userdata($data[$key]['reported_to']);
                $data[$key]['reported_to'] = $reported_user_info->user_login;
                $data[$key]['delete'] = '<a class="close dashicons dashicons-no" href="' . $delete_url . '"></a>';
            }
        }
        //echo get_current_user_id();
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
            case 'country_from':
            case 'country_to':
            case 'from_processing_id':
            case 'to_processing_id':
            case 'user_id':
            case 'reported_to':
            case 'delete':
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
        $order = 'asc';
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