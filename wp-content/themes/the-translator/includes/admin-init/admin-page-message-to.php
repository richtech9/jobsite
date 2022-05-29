<?php
/*
 * Plugin Name: Message Table
 * Description: Message_Wp_List_Table
 * Plugin URI: http://www.paulund.co,
 * Author: Lakhvidner
 * Author URI: http://www.Lakhvidner.com
 * Version: 1.0
 */

/*
    * current-php-code 2021-Jan-9
    * input-sanitized :
    * current-wp-template:  admin-screen  for messages
*/



/**
 * Paulund_Wp_List_Table class will create the page to load the table
 */
class AdminPageMessageTo
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
        add_action('admin_menu', array($this, 'add_menu_message_list_table_page'));
    }

    /**
     * Menu item will allow us to load the page to display the table
     */
    public function add_menu_message_list_table_page()
    {
        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'Message List', 'Messages List', 'manage_options',
                'freelinguist-admin-message-to', array($this, 'list_table_page'), $this->position);
        } else {
            add_menu_page('Message List', 'Messages List', 'manage_options',
                'freelinguist-admin-message-to', array($this, 'list_table_page'), 'dashicons-format-chat');
        }

    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page()
    {

        ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <?php

            ?>
            <?php if (isset($_REQUEST['send_message']) && $_REQUEST['send_message'] == true) { ?>
                <?php
                if (isset($_REQUEST['send_message_btn'])) {
                    $select_user_m = $_REQUEST['select_user_m'];
                    $user = get_user_by('email',$select_user_m);
                    $message = $_REQUEST['message'];
                    if (!empty($message) && $user) {
                        global $wpdb;
                        $count =1;
                        if ($count == 1) {
                            $wpdb->insert(
                                'wp_messages',
                                array(
                                    'sender_id' => get_current_user_id(),
                                    'receiver_id' => $user->ID,
                                    'message' => strip_tags($message),
                                    'date' => current_time('mysql')
                                ));
                            if (in_array('customer', $user->roles) || in_array('translator', $user->roles)) {
                                $variables['message'] = $message;
                                emailTemplateForUser($user->user_email, SEND_MESSAGE, $variables);
                            }
                            echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated"> <p><strong>Settings saved.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                        } else {
                            echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated"><p><strong style="color:red">Message not sent.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

                        }
                    } else {
                        echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated"> <p><strong style="color:red">All the fields are required.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                    }
                }
                ?>
                <div class="wrap stuffbox">

                    <div class="inside">
                        <span class="bold-and-blocking larger-text">Send Message</span>
                        <hr>
                        <form name="send_message_f" method="post" id="send_message_f"
                              action="<?php echo admin_url() . 'admin.php?page=freelinguist-admin-message-to&send_message=true'; ?>&lang=en">
                            <table class="form-table">
                                <tbody>
                                <tr class="user-rich-editing-wrap ">
                                    <th scope="row">Send To</th>
                                    <td>

                                        <input type="text" title="User Email" name="select_user_m" id="select_user_m" size="50">

                                        <script type="text/javascript">
                                            jQuery(function () {
                                                jQuery("#select_user_m").autocomplete({ // myev
                                                    source: '<?php echo get_site_url();?>/?action=get_user_list_by_autocomplete',
                                                    minLength: 1
                                                });
                                            });

                                        </script>
                                    </td>
                                </tr>
                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">
                                        Message
                                    </th>
                                    <td>
                                        <textarea maxlength="1000" name="message" id="message" cols="50" rows="5" value="" title="message" autocomplete="off"></textarea><br>
                                    </td>
                                    <td></td>
                                </tr>

                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">
                                        <input type="submit" value="Send Message" name="send_message_btn"
                                               class="button button-primary">
                                    </th>
                                    <td></td>
                                </tr>
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
                <hr>
                <span class="bold-and-blocking larger-text">Messages</span>
            <?php } else { ?>
                <span class="bold-and-blocking larger-text">Messages
                        <a class="page-title-action"
                           href="<?php echo admin_url() . 'admin.php?page=freelinguist-admin-message-to&send_message=true'; ?>">Send Message</a>
                        </span>
            <?php } ?>
            <ul class="subsubsub"></ul>
            <form name="" method="post" id="search_messages" action="#">
                <p class="search-box">


                    <input type="text" title="User Email" name="search_email" id="search_email" size="60">

                    <script type="text/javascript">
                        jQuery(function () {
                            jQuery("#search_email").autocomplete({
                                source: '<?php echo get_site_url();?>/?action=get_user_list_by_autocomplete',
                                minLength: 1
                            });
                        });

                    </script>

                    <button type="submit">Search Email</button>
                </p>


            </form>
            <?php

            $messageListTable = new Message_List_Table();
            $messageListTable->prepare_items();
            $messageListTable->display(); ?>
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
class Message_List_Table extends WP_List_Table
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
            'id' => 'Message ID',
            'sender_email' => 'Sender Email',
            'receiver_email' => 'Receiver Email',
            'message' => 'Message',
            'date' => 'Date'
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
        return array('sender_email' => array('sender_email', true), 'receiver_email' => array('receiver_email', true), 'date' => array('date', true));
    }

    /**
     * Get the table data
     *
     * @return array
     */
    private function table_data()
    {
        global $wpdb;

        if (isset($_REQUEST['search_email'])) {

            $select_user_m = $_REQUEST['search_email'];
            $user = get_user_by('email',$select_user_m);

            if ($user) {
                $user_id = $user->ID;
            } else {
                $user_id = 0;
            }
            $cur_user = get_current_user_id();

            $user_info_a = get_userdata(get_current_user_id());
            if (in_array('administrator', $user_info_a->roles) || in_array('administrator_for_client', $user_info_a->roles)) {

                $data = $wpdb->get_results(
                        "
                                SELECT message_table.id,message_table.sender_id,message_table.receiver_id,usertable_1.user_email as sender_email,
                                        usertable_2.user_email as receiver_email,message_table.message,message_table.read_status,message_table.date 
                                FROM wp_messages message_table 
                                JOIN wp_users usertable_1 ON message_table.sender_id =usertable_1.id 
                                JOIN wp_users usertable_2 ON message_table.receiver_id =usertable_2.id 
                                WHERE receiver_id = $user_id OR sender_id = $user_id
                                ", ARRAY_A);

            } else {
                if ($cur_user == $user_id) {

                    $data = $wpdb->get_results(
                        "
                                SELECT message_table.id,message_table.sender_id,message_table.receiver_id,
                                        usertable_1.user_email as sender_email,usertable_2.user_email as receiver_email,
                                        message_table.message,message_table.read_status,message_table.date 
                                FROM wp_messages message_table 
                                JOIN wp_users usertable_1 ON message_table.sender_id =usertable_1.id 
                                JOIN wp_users usertable_2 ON message_table.receiver_id =usertable_2.id 
                                WHERE receiver_id = $cur_user OR sender_id = $cur_user
                                ", ARRAY_A);
                } else {

                    $data = $wpdb->get_results(
                        "SELECT message_table.id,message_table.sender_id,message_table.receiver_id,usertable_1.user_email as sender_email,
                                        usertable_2.user_email as receiver_email,message_table.message,message_table.read_status,message_table.date 
                                FROM wp_messages message_table 
                                JOIN wp_users usertable_1 ON message_table.sender_id =usertable_1.id 
                                JOIN wp_users usertable_2 ON message_table.receiver_id =usertable_2.id 
                                WHERE (sender_id = $user_id AND receiver_id = $cur_user) OR (sender_id = $cur_user AND receiver_id = $user_id)
                                ", ARRAY_A);
                }

            }
        } else {

            $data = $wpdb->get_results(
                    "SELECT message_table.id,message_table.sender_id,message_table.receiver_id,usertable_1.user_email as sender_email,
                                    usertable_2.user_email as receiver_email,message_table.message,message_table.read_status,message_table.date 
                            FROM wp_messages message_table 
                            JOIN wp_users usertable_1 ON message_table.sender_id =usertable_1.id 
                            JOIN wp_users usertable_2 ON message_table.receiver_id =usertable_2.id 
                            WHERE 1
                            ", ARRAY_A);
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
            case 'id':
            case 'sender_email':
            case 'receiver_email':
            case 'message':
            case 'date':
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