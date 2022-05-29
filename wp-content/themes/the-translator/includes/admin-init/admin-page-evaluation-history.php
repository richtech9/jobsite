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
    * current-php-code 2021-Jan-11
    * input-sanitized :
    * current-wp-template:  admin-screen  for evaluation history
*/


/**
 * Paulund_Wp_List_Table class will create the page to load the table
 */
class AdminPageEvalutionHistory
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
        add_action('admin_menu', array($this, 'add_menu_EvalutionHistory_list_table_page'));
    }

    /**
     * Menu item will allow us to load the page to display the table

     */
    public function add_menu_EvalutionHistory_list_table_page()
    {
        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'Evalution History', 'Evalution History', 'manage_options',
                'freelinguist-admin-evaluation-history', array($this, 'list_table_page'), $this->position);
        } else {
            add_menu_page('Evalution History', 'Evalution History', 'manage_options',
                'freelinguist-admin-evaluation-history', array($this, 'list_table_page'), 'dashicons-info');
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
            <span class="bold-and-blocking large-text">Evalution History</span>
            <?php


            $user_list = array();
            $current_user = wp_get_current_user();
            if ( in_array('administrator',$current_user->roles) || in_array('administrator_for_client',$current_user->roles)) {
                $args = array(
                    'role__in' => array('evaluation_sub_admin', 'super_sub_admin'),
                    'orderby' => 'login',
                    'order' => 'ASC',
                    'count_total' => false,
                    'fields' => array('ID', 'user_email', 'user_login'),
                );
                $evaluation_sub_admin = get_users($args);
                foreach ($evaluation_sub_admin as $user) {
                    $user_list[] = $user->ID;
                }
            } elseif (in_array('super_sub_admin',$current_user->roles)) {
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
                foreach ($sub_Admin_user as $key) {
                    $eval_user_info = get_userdata($key->ID);
                    if ($eval_user_info->roles[0] == 'evaluation_sub_admin') {
                        $user_list[] = $key->ID;
                    }
                }

               
                $user_list[] = get_current_user_id();
            } else {
                $user_list[] = get_current_user_id();
            }

            /*echo "<pre>";   print_R($evaluation_sub_admin);    exit;*/


            ?>
            <select name="select_user_m" id="select_user_m" title="select user">
                <option value="#">Select evaluation sub admin</option>
                <?php
                foreach ($user_list as $user) {
                    $u_url = $user;
                    $user_info = get_userdata($user);
                    ?>
                    <option value="<?php echo $u_url; ?>" <?php echo (isset($_REQUEST['sub_admin']) && $_REQUEST['sub_admin'] == $user_info->ID) ? "selected" : ""; ?>> <?php echo esc_html($user_info->user_email) . ' (' . $user_info->user_login . ')'; ?></option>
                    <?php
                }
                ?>
            </select>
            <p class="search-box">
                <input class="enhanced-text" type="search" id="r-search-input"
                       placehoder="Search by username or user email" name="s" value="" title="search">
                <input type="submit" id="search-u"  class="button large-text" value="Search">
            </p>
            <div>
                <p style="float:left" class="description">Filter by evaluation sub admin. </p>
                <p style="float:right" class="description">Search by username or user email. </p>
            </div>
            <script>
                jQuery(function () {
                    jQuery('#search-u').click(function () {
                        var inputURL = jQuery('#r-search-input').val();
                        var url = '<?php echo admin_url(); ?>' + 'admin.php?page=freelinguist-admin-evaluation-history&s=' + inputURL;
                        //Redirects
                        window.location.href = url;
                        return false;
                    });

                    jQuery("#select_user_m").change(function () {
                        var valueis = this.value;
                        //alert(valueis);
                        if (valueis !== '') {
                            var url = '<?php echo admin_url(); ?>' + 'admin.php?page=freelinguist-admin-evaluation-history&sub_admin=' + valueis;
                            window.location.href = url;
                        }
                        return false;
                    });

                });
            </script>

        </div>
        <?php
        $EvalutionHistory_List_Table = new EvalutionHistory_List_Table();
        $EvalutionHistory_List_Table->prepare_items();
        $EvalutionHistory_List_Table->display();
    }
}


// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


/**
 * Create a new table class that will extend the WP_List_Table
 */
class EvalutionHistory_List_Table extends WP_List_Table
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
            'username' => 'UserName',
            'user_email' => 'User Email',
            'evaluated_by_username' => 'Evaluated by(UserName)',
            'evaluated_by_email' => 'Evaluated by(Email)',
            'evalution_time' => 'Evaluation time',
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
        return array('evalution_time' => array('evalution_time', true));
        //return array();
    }

    /**
     * Get the table data
     *
     * @return array
     */
    private function table_data()
    {
        global $wpdb;

        $user_list = array();
        $current_user = wp_get_current_user();
        if ( in_array('administrator',$current_user->roles) || in_array('administrator_for_client',$current_user->roles)) {
            $user_list = array();
        } elseif (in_array('super_sub_admin',$current_user->roles) ) {
            $users = getReportedSubAdmin();
            foreach ($users as $key => $value) {
                $user_list[] = $value->ID;
            }
            $user_list[] = get_current_user_id();
        } else {
            $user_list[] = get_current_user_id();
        }

        if (isset($_REQUEST['s']) && !empty($_REQUEST['s'])) {
            $search_d = $_REQUEST['s'];
            $data = $wpdb->get_results(
                    "SELECT users.ID,users.user_login,users.user_email 
                            FROM wp_users users 
                            JOIN wp_usermeta usermeta ON users.ID =usermeta.user_id 
                            where usermeta.meta_key IN ('last_evaluated_by') AND 
                            (users.user_email LIKE '%" . $search_d . "%' OR users.user_login LIKE '%" . $search_d . "%' ) 
                            GROUP BY users.ID ORDER BY usermeta.user_id",
                    ARRAY_A);
            foreach ($data as $key => $value) {
                $data[$key]['evalution_time'] = get_user_meta($value['ID'], 'last_evalution_time', true);
                $last_evaluated_by = get_userdata(get_user_meta($value['ID'], 'last_evaluated_by', true));
                $data[$key]['evaluated_by_username'] = $last_evaluated_by->user_login;
                $data[$key]['evaluated_by_email'] = $last_evaluated_by->user_email;
                $data[$key]['username'] = $value['user_login'];
                $data[$key]['user_email'] = $value['user_email'];

            }
        } elseif (isset($_REQUEST['sub_admin']) && !empty($_REQUEST['sub_admin'])) {
            $sub_admin_is = $_REQUEST['sub_admin'];
            $args = array(
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => 'last_evaluated_by',
                        'value' => $sub_admin_is,
                        'compare' => '='
                    )
                )
            );
            $wp_user_query = new WP_User_Query($args);
            $authors = $wp_user_query->get_results();
            $data = array();
            $key = 0;
            foreach ($authors as $author) {
                // get all the user's data
                $author_info = get_userdata($author->ID);
                $user_id = $author->ID;
                $data[$key]['evalution_time'] = get_user_meta($user_id, 'last_evalution_time', true);
                $last_evaluated_by = get_userdata(get_user_meta($user_id, 'last_evaluated_by', true));
                $data[$key]['evaluated_by_username'] = $last_evaluated_by->user_login;
                $data[$key]['evaluated_by_email'] = $last_evaluated_by->user_email;
                $data[$key]['username'] = $author_info->user_login;
                $data[$key]['user_email'] = $author_info->user_email;
                $data[$key]['ID'] = $user_id;
                $key++;
            }
        } else {
            $args = array(
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => 'last_evaluated_by',
                        'value' => $user_list,
                        'compare' => 'IN'
                    )
                )
            );
            $wp_user_query = new WP_User_Query($args);
            $authors = $wp_user_query->get_results();
            $data = array();
            $key = 0;
            foreach ($authors as $author) {
                // get all the user's data
                $author_info = get_userdata($author->ID);
                $user_id = $author->ID;
                $data[$key]['evalution_time'] = get_user_meta($user_id, 'last_evalution_time', true);
                $last_evaluated_by = get_userdata(get_user_meta($user_id, 'last_evaluated_by', true));
                $data[$key]['evaluated_by_username'] = empty($last_evaluated_by) ? '' : $last_evaluated_by->user_login;
                $data[$key]['evaluated_by_email'] = empty($last_evaluated_by) ? '' : $last_evaluated_by->user_email;
                $data[$key]['username'] = $author_info->user_login;
                $data[$key]['user_email'] = $author_info->user_email;
                $data[$key]['ID'] = $user_id;
                $key++;
            }
        }
        //echo "<pre>"; print_R($user_list);  exit;

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
            case 'username':
            case 'user_email':
            case 'evaluated_by_username':
            case 'evaluated_by_email':
            case 'evalution_time':
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
        $orderby = 'ID';
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