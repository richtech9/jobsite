<?php
/*
 * Plugin Name: CashierSentEmails_Wp_List_Table Table
 * Description: CashierSentEmails_Wp_List_Table
 * Plugin URI: http://www.Lakhvidner.co,
 * Author: Lakhvidner
 * Author URI: http://www.Lakhvidner.com
 * Version: 1.0
 */

/*
    * current-php-code 2021-Jan-12
    * input-sanitized :
    * current-wp-template:  admin-screen  for send message cashier
*/

function send_meesage_cashior()
{ ?>
    <?php

    if (isset($_REQUEST['send_message_form'])) {
        $message_content = $_REQUEST['message_content'];
        $title = $_REQUEST['title'];
        $user = $_REQUEST['user_is'];
        $current_user = get_userdata(get_current_user_id());
        if (in_array('evaluation_sub_admin', $current_user->roles)) {
            $type = EVALUTION_SEND_EMAIL_TO_USER;
        }
        else if (in_array('meditation_sub_admin', $current_user->roles)) {
            $type = EVALUTION_SEND_EMAIL_TO_USER;
        } elseif (in_array('cashier_sub_admin', $current_user->roles)) {
            $type = CASHIER_SEND_EMAIL_TO_USER;
        } else {
            $type = 'null';
        }
        $user_detail = get_userdata($user);
        global $wpdb;
        $prefix = $wpdb->prefix;
        $wpdb->insert(
            'wp_message_email_history',
            array(
                'sender_id' => get_current_user_id(),
                'receiver_id' => $user,
                'title' => $title,
                'content' => $message_content,
                'type' => $type,           // cashier_send_email_to_user
                'created_date' => current_time('mysql'),
                'modified_date' => current_time('mysql'),
            )
        );
        send_custom_message($user_detail->user_email, $title, $message_content);
        echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                <p><strong>Sent.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

    }
    ?>
    <div class="wrap stuffbox">
        <div class="inside">
            <span class="bold-and-blocking larger-text">Send Email</span>
            <hr>
            <form name="send_template_f" method="post" id="send_template_f" action="#">
                <table class="form-table">
                    <tbody>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Send message To</th>
                        <td>  <?php
                            $user = (isset($_REQUEST['user_is'])? $_REQUEST['user_is']: '');
                            $user_detail = get_userdata($user);
                            echo ($user_detail? $user_detail->user_email:''); ?>
                        </td>
                    </tr>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Title</th>
                        <td><input style="width:100%;" type="text" name="title" value="" id="title" title="title"></td>
                    </tr>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Email body content</th>
                        <?php
                        echo '<style type="text/css">
                                #message_content{ height:100px; width:100%}
                            </style>';
                        ?>
                        <td>
                            <?php wp_editor($content = '', $editor_id = 'message_content', $settings = array()); ?>  </td>
                    </tr>

                    <tr class="user-rich-editing-wrap">
                        <th scope="row">
                            <input type="submit" value="Update" name="send_message_form" class="button button-primary">
                        </th>
                        <td></td>
                    </tr>
                    </tbody>
                </table>

            </form>
        </div>
        <hr>
        <span class="bold-and-blocking larger-text">Sent Emails</span>
        <?php
        $CashierSentEmails_List_Table = new CashierSentEmails_List_Table();
        $CashierSentEmails_List_Table->prepare_items();
        $CashierSentEmails_List_Table->display();
        ?>
    </div>
    <?php
}


// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


/**
 * Create a new table class that will extend the WP_List_Table
 */
class CashierSentEmails_List_Table extends WP_List_Table
{
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
            'sender_id' => 'Sender',
            'receiver_id' => 'Receiver',
            'title' => 'Title',
            'content' => 'Content',
            'created_date' => 'Date',
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
        global $wpdb;
        $user_id = (int) FLInput::get('user_is');
        $user_info = get_userdata(get_current_user_id());
        if (isset($_REQUEST['type']) && $_REQUEST['type'] == EVALUTION_SEND_EMAIL_TO_USER && in_array('evaluation_sub_admin', $user_info->roles)) {
            $type = EVALUTION_SEND_EMAIL_TO_USER;
        } elseif (in_array('cashier_sub_admin', $user_info->roles)) {
            $type = CASHIER_SEND_EMAIL_TO_USER;
        } else {
            $type = 'null';
        }
        if ($user_id) {
            $data = $wpdb->get_results(
                "SELECT id,sender_id,receiver_id,title,content,created_date 
                        FROM wp_message_email_history 
                        WHERE receiver_id = $user_id and type = $type 
                       order by id desc ", ARRAY_A);
        } else {
            $data = [];
        }

        foreach ($data as $key => $value) {
            $sender = get_userdata($value['sender_id']);
            $receiver = get_userdata($value['receiver_id']);
            $sender_email = $sender->user_email;
            $receiver_email = $receiver->user_email;
            $data[$key]['sender_id'] = $sender_email;
            $data[$key]['receiver_id'] = $receiver_email;
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
            case 'sender_id':
            case 'receiver_id':
            case 'title':
            case 'content':
            case 'created_date':
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


class logo_according_to_language_List_Table extends WP_List_Table
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
        $perPage = 400;
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
            'language' => 'Language',
            'logo' => 'Logo',
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
        //return array('english');
        return array('language' => array('language', true));
    }

    /**
     * Get the table data
     *
     * @return array
     */
    private function table_data()
    {
        $data = array();
        $columns_lang = array(
            'english' => 'English',
            'chinese' => 'Chinese (Simplified)',
            'russian' => 'Russian',
            'japanese' => 'Japanese',
            'german' => 'German',
            'spanish' => 'Spanish',
            'french' => 'French',
            'portuguese' => 'Portuguese',
            'italian' => 'Italian',
            'polish' => 'Polish',
            'turkish' => 'Turkish',
            'persian' => 'Persian',
            'chinese_traditional' => 'Chinese (Traditional)',
            'danish' => 'Danish',
            'dutch' => 'Dutch',
            'hindi' => 'hindi',
            'arabic' => 'Arabic',
            'korean' => 'Korean',
            'czech' => 'Czech',
            'vietnamese' => 'Vietnamese',
            'indonesian' => 'Indonesian',
            'swedish' => 'Swedish',
            'malay' => 'Malay',
        );
        $uploads = wp_upload_dir();
        $upload_path = $uploads['baseurl'] . '/';
        foreach ($columns_lang as $key => $value) {
            $logo_name = $key . '_logo';
            if (get_option($logo_name) == '') {

                $logo_image = get_template_directory_uri() . '/images/logo-1000-by-200.png';
                $logo_image = '<img src="' . $logo_image . '" width="227px" height="54">';

            } else {
                $logo_image = '<img src="' . $upload_path . get_option($logo_name) . '" width="227px" height="54">';
            }
            $delete_link = admin_url() . 'admin.php?page=customer-theme-logo-val&del_key=' . $key;
            $delete = '<a class="edit" href="' . $delete_link . '">Delete</a>';
            $data[] = array(
                'language' => $key,
                'logo' => $logo_image,
                'action' => $delete
            );
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
            case 'language':
            case 'logo':

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
        $orderby = 'language';
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