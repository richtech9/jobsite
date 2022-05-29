<?php
/*
 * Plugin Name: SocialEmailContacts Table
 * Description: SocialEmailContacts
 * Plugin URI: http://www.lakhvinder.com
 * Author: Lakhvinder Singh
 * Author URI: http://www.lakhvinder.com
 * Version: 1.0
 */

/*
    * current-php-code 2021-Jan-12
    * input-sanitized : lang
    * current-wp-template:  admin-screen  for social contacts
*/



/**
 * Paulund_Wp_List_Table class will create the page to load the table
 */
class AdminPageSocialEmailContacts
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
        add_action('admin_menu', array($this, 'add_menu_SocialEmailContacts_list_table_page'));
    }

    /**
     * Menu item will allow us to load the page to display the table
     */
    public function add_menu_SocialEmailContacts_list_table_page()
    {
        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'Social Email List', 'Social Email List', 'manage_options',
                'freelinguist-admin-social-contacts', array($this, 'list_table_page'), $this->position);
        } else {
            add_menu_page('Social Email List', 'Social Email List', 'manage_options',
                'freelinguist-admin-social-contacts', array($this, 'list_table_page'), 'dashicons-email-alt');
        }

    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page()
    {
        $SocialEmailContactsListTable = new SocialEmailContacts_List_Table();
        $SocialEmailContactsListTable->prepare_items();
        ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <span class="bold-and-blocking larger-text">Social Email Contacts
                <a class="page-title-action"
                   href="<?php echo admin_url() . 'admin.php?page=freelinguist-admin-social-contacts&action=downloadexcel&lang=en'; ?>">Download Excel Sheet</a>
                </span>
            <ul class="subsubsub"></ul>
            <?php $SocialEmailContactsListTable->display(); ?>
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
class SocialEmailContacts_List_Table extends WP_List_Table
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
            'name' => 'Name',
            'email' => 'Email',
            'user_registered' => 'Registered'
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
        return array('name' => array('name', true), 'email' => array('email', true));
    }

    /**
     * Get the table data
     *
     * @return array
     */
    private function table_data()
    {
        global $wpdb;
        $data = $wpdb->get_results("
                SELECT wslusersprofiles_table.id,usertable.display_name as name,wslusersprofiles_table.email,usertable.user_registered 
                FROM wp_wslusersprofiles wslusersprofiles_table 
                JOIN wp_users usertable ON wslusersprofiles_table.user_id =usertable.id", ARRAY_A);
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
            case 'name':
            case 'email':
            case 'user_registered':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     * @param $a 
     * @param $b 
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


function downloadexcel()
{
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'downloadexcel') {
        // 

        global $wpdb;
        $dataku = $wpdb->get_results("SELECT * FROM wp_wsluserscontacts", ARRAY_A);
        $collectP = [];
        foreach ($dataku as $li) {

            $users = $wpdb->get_results("SELECT * FROM wp_users where ID=" . $li['user_id'] . " ", ARRAY_A);

            $display_name = (isset($users[0]['display_name'])) ? $users[0]['display_name'] : '';
            $collectP[] = [

                'full_name' => $li['full_name'],
                'email' => $li['email'],
                'provider' => $li['provider'],
                'display_name' => $display_name,
                'user_id' => $li['user_id']

            ];
        }


        $fh1 = @fopen('php://output', 'w');

        fputcsv($fh1, array('Full name', 'Email', 'Provider', 'Imported From', 'user_id'));

        foreach ($collectP as $data1) {

            // Put the data into the stream
            fputcsv($fh1, $data1);
        }
        // Close the file
        fclose($fh1);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="result.csv"');
        header('Cache-Control: max-age=0');
        // Make sure nothing else is sent, our file is done
        exit;


    }
}

add_action('init', 'downloadexcel');
?>