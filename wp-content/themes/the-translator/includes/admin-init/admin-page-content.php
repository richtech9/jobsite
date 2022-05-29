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
class AdminPageContent
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
        add_action('admin_menu', array($this, 'add_menu_content_list_table_page'));

        add_action('admin_footer', array(&$this, '_js_vars'));
    }

    /**
     * Menu item will allow us to load the page to display the table

     */
    public function add_menu_content_list_table_page()
    {
        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'Content List', 'Contents List', 'manage_options',
                'freelinguist-admin-content', array($this, 'list_table_page'), $this->position);
        } else {
            add_menu_page('Content List', 'Contents List', 'manage_options',
                'freelinguist-admin-content', array($this, 'list_table_page'), 'dashicons-format-chat');
        }

    }

    /**
     * Display the list table page
     *
     * @return Void
     */

    public function _js_vars()
    {
        echo '';
    }

    public function list_table_page()
    {
        ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <div class="wrap stuffbox" style="padding: 15px;">
                <form id="events-filter" method="post">
                    <!--input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" /-->
                    <?php

                    if (isset($_REQUEST['view_page'])) {
                        /********************************** View  pgae ***************************************/
                        ?>
                        <span class="bold-and-blocking larger-text">Content Detail </span>
                        <hr>
                        <div class="container container_custom">
                            <?php
                            global $wpdb;
                            $content_id = $_REQUEST['view_page'];
                            $content_detail = $wpdb->get_row("select * from wp_linguist_content where user_id IS NOT NULL AND id=$content_id", ARRAY_A);
                            if (!empty($content_detail)) {


                                $content_id = $content_detail['id'];
                                $content_chapter_detail = $wpdb->get_results("select * from wp_linguist_content_chapter where user_id IS NOT NULL AND linguist_content_id=$content_id", ARRAY_A);
                                $user_Detail = get_userdata($content_detail['user_id']);


                                ?>
                                <section class="pagetitle">
                                    <div class="container">
                                        <span class="bold-and-blocking large-text"><?= $content_detail['content_title'] ?></span>
                                        <div class="bredcum">
                                        </div>
                                    </div>
                                </section>
                                <div class="customers-content-sec">
                                    <div class="container">
                                        <div class="customers-content-left">
                                            <?php
                                            //code-notes [image-sizing]  content adding small thumbnail of content, but may need to change size after testing
                                            $content_url = FreelinguistSizeImages::get_url_from_relative_to_upload_directory(
                                                $content_detail['content_cover_image'], FreelinguistSizeImages::SMALL, true);
                                            ?>

                                            <img style="" src="<?= $content_url ?>">

                                        </div>
                                        <div class="customers-content-right">
                                            <div class="customers-content-right-top">
                                            <span class="bold-and-blocking large-text"><a
                                                        href="#"><?= $content_detail['content_title'] ?></a></span>
                                                <label class="large-text">$<?= amount_format($content_detail['content_amount']) ?></label>
                                            </div>
                                            <div class="customers-content-right-mid">
                                                <p>
                                                    <strong class="enhanced-text">Author:</strong> <?= $user_Detail->display_name ?>
                                                </p>
                                                <p>
                                                    <strong class="enhanced-text">Type:</strong> <?= $content_detail['publish_type'] ?>
                                                </p>
                                                <p><strong class="enhanced-text">Sale
                                                        Type:</strong> <?= $content_detail['content_sale_type'] ?></p>
                                                <p><strong class="enhanced-text">Number For
                                                        Sale:</strong> <?= $content_detail['max_to_be_sold'] ?></p>
                                                <p><strong class="enhanced-text">Content
                                                        Type:</strong> <?= $content_detail['content_type'] ?></p>
                                            </div>
                                            <div class="customers-content-right-bottom enhanced-text">
                                                <p>
                                                    <?php
                                                    echo substr($content_detail['content_summary'], 0, 500);
                                                    if (strlen($content_detail['content_summary']) > 500) {
                                                        echo '..';
                                                    }
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <section class="single-content">

                                    <div class="container">
                                        <div class="single-left" style="width:100%">
                                            <p><?php echo substr($content_detail['content_summary'], 500); ?></p>
                                            <?php foreach ($content_chapter_detail as $key => $value) { ?>
                                                <h3><?= $value['title'] ?></h3>
                                                <div><?= $value['content_html'] ?></div> <!-- code-notes temp fix to remove slashes for displaying html-->
                                            <?php } ?>
                                        </div>
                                    </div>
                                </section>
                                </div>


                                <?php
                            } //end if found content
                    } else if (isset($_REQUEST['action']) && $_REQUEST['action'] != "") {


                        if ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'bulk-delete')
                            || (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'bulk-delete')
                        ) {


                            $delete_ids = esc_sql($_REQUEST['bulk-delete']);

                            // loop over the array of record IDs and delete them
                            $error_messages = [];
                            foreach ($delete_ids as $id) {
                                try {
                                    FreelinguistContentHelper::delete_content($id,false);
                                } catch (Exception $e) {
                                    $error_messages[] = $e->getMessage();
                                }

                            }
                            if (count($error_messages)) {
                                will_dump('Error Deleting Content',$error_messages);
                                die();
                            }
                            wp_redirect(add_query_arg());
                            exit();

                        }

                        if ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'bulk-hide')
                            || (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'bulk-hide')
                        ) {


                            $delete_ids = esc_sql($_REQUEST['bulk-delete']);

                            // loop over the array of record IDs and delete them
                            foreach ($delete_ids as $id) {
                                global $wpdb;

                                $wpdb->query("update wp_linguist_content set show_content=0 where id=$id  AND parent_content_id IS NULL");

                            }
                            wp_redirect(add_query_arg());
                            exit();

                        }

                        if ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'bulk-show')
                            || (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'bulk-show')
                        ) {


                            $delete_ids = esc_sql($_REQUEST['bulk-delete']);

                            // loop over the array of record IDs and delete them
                            foreach ($delete_ids as $id) {
                                global $wpdb;

                                $wpdb->query("update wp_linguist_content set show_content=1 where id=$id AND parent_content_id IS NULL");

                            }

                            wp_redirect(add_query_arg());
                            exit();

                        }
                        if (isset($_REQUEST['id']) && $_REQUEST['id'] != "") {
                            $content_id = $_REQUEST['id'];
                            global $wpdb;
                            $content_detail = $wpdb->get_row("select * from wp_linguist_content where user_id IS NOT NULL AND id=$content_id", ARRAY_A);
                            if (!empty($content_detail)) {
                                $content_id = $content_detail['id'];
                            }

                            if ($_REQUEST['action'] == "delete") {
                                //code-notes now using new centralized deleting method
                                $error_messages = [];
                                try {
                                    FreelinguistContentHelper::delete_content($content_id,false);
                                } catch (Exception $e) {
                                    $error_messages[] = $e->getMessage();
                                }

                                if (count($error_messages)) {
                                    will_dump('Error Deleting Content',$error_messages);
                                    die();
                                }

                            } else if ($_REQUEST['action'] == "show") {
                                $wpdb->query("update wp_linguist_content set show_content=1 where id=$content_id");
                            } else if ($_REQUEST['action'] == "hide") {
                                $wpdb->query("update wp_linguist_content set show_content=0 where id=$content_id");
                            }


                            wp_redirect(add_query_arg());
                            exit();

                        }
                    } else {
                        ?>
                        <span class="bold-and-blocking larger-text">Contents</span>
                        <hr>
                        <?php
                        $contentListTable = new Cu_content_List_Table();
                        $contentListTable->prepare_items();
                        $contentListTable->display();
                    }
                    ?>
                </form>
            </div>
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
class cu_content_List_Table extends WP_List_Table
{
    const PER_PAGE = 10;
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

        $perPage = static::PER_PAGE;
        $totalItems = $this->get_total_content();
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage
        ));
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    protected function get_total_content() {
        global $wpdb;
        $sql = "SELECT count(*) as da_count FROM wp_linguist_content WHERE user_id IS NOT NULL;";
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
            'cb' => '<input type="checkbox" />',
            'id' => 'ID',
            'number_sold' => "Sold",
            'parent_content_id' => 'Purchased',
            'user_email' => 'User',
            'content_title' => 'Title',
            'publish_type' => 'Content Type',
            'content_sale_type' => 'Sale type',
            'content_amount' => 'Price',
            'created_at' => 'Date',
            'action' => 'Action'
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
        $actions = [
            'bulk-delete' => 'Delete',
            'bulk-show' => 'Show',
            'bulk-hide' => 'Hide'
        ];

        return $actions;
    }

    public function get_sortable_columns()
    {
        return array(
                'content_title' => array('content_title', true),
                'user_email' => array('user_email', true),
                'parent_content_id' => array('parent_content_id', true),
                'created_at' => array('created_at', true),
                'publish_type' => array('publish_type', true),
                'content_sale_type' => array('content_sale_type', true)
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
        $user_list = array();
        $current_user = wp_get_current_user();

        $per_page = static::PER_PAGE;
        $currentPage = $this->get_pagenum();
        $start_count = ($currentPage - 1) * $per_page;

        $orderby = FLInput::get('orderby','id');
        $order = FLInput::get('order','desc');



        if (in_array('administrator', $current_user->roles) || in_array('administrator_for_client', $current_user->roles)) {
            $data = $wpdb->get_results(
                    "SELECT content_table.* ,usertable_1.user_email as user_email ,
                              (SELECT count(*) FROM wp_linguist_content paul where paul.parent_content_id = content_table.id and user_id IS NOT NULL) as number_sold
                              FROM wp_linguist_content content_table 
                              JOIN wp_users usertable_1 ON content_table.user_id =usertable_1.id 
                              WHERE content_table.user_id IS NOT NULL
                              ORDER BY $orderby $order
                              LIMIT $start_count, $per_page
                              ", ARRAY_A);



        } elseif (in_array('super_sub_admin', $current_user->roles)) {
            $users = getReportedSubAdmin();
            foreach ($users as $key => $value) {
                $user_list[] = $value->ID;
            }
            $user_list[] = get_current_user_id();
            $user_list = implode(',', $user_list);
            $data = $wpdb->get_results(
                    "SELECT content_table.* ,usertable_1.user_email as user_email ,
                          (SELECT count(*) FROM wp_linguist_content paul where paul.parent_content_id = content_table.id) as number_sold
                            FROM wp_linguist_content content_table 
                            JOIN wp_users usertable_1 ON content_table.user_id =usertable_1.id  
                            where content_table.user_id IN ($user_list)
                            ORDER BY $orderby $order
                             LIMIT $start_count, $per_page
                            ", ARRAY_A);
        } else {
            $user_list[] = get_current_user_id();
            $user_list = implode(',', $user_list);
            $data = $wpdb->get_results(
                    "SELECT content_table.* ,usertable_1.user_email as user_email ,
                              (SELECT count(*) FROM wp_linguist_content paul where paul.parent_content_id = content_table.id) as number_sold
                             FROM wp_linguist_content content_table 
                             JOIN wp_users usertable_1 ON content_table.user_id =usertable_1.id 
                              where content_table.user_id IN ($user_list) 
                              ORDER BY $orderby $order
                              LIMIT $start_count, $per_page
                              ", ARRAY_A);
        }
        $result = array();
        foreach ($data as $key => $value) {
            $val_co_url = admin_url() . 'admin.php?page=freelinguist-admin-content&view_page=' . $value['id'];
            $parent_id = intval($value['parent_content_id']);
            if ($parent_id) {
                $parent_href = site_url() . '/content/?lang=en&mode=view&content_id=' . FreelinguistContentHelper::encode_id($parent_id) ;
                $value['parent_url'] = "<a href='$parent_href' target='_blank'>" . $parent_id . "</a>";
                $value['action'] = '';

            } else {
                $value['parent_url'] = '';
                if ($value['show_content'] == 1) {
                    $value['action'] = '<a href="#" data-show_content="0" data-content_id = "' . $value['id'] . '"  class="show_hide_content" id="show_hide_' . $value['id'] . '">Hide</a>&nbsp;<a href="#" data-content_id = "' . $value['id'] . '" id="delete_content_' . $value['id'] . '" class="delete_content">Delete</a>';
                } else {
                    $value['action'] = '<a href="#" data-content_id = "' . $value['id'] . '" data-show_content="1" class="show_hide_content" id="show_hide_' . $value['id'] . '">Publish</a>&nbsp;<a href="#" data-content_id = "' . $value['id'] . '" id="delete_content_' . $value['id'] . '" class="delete_content">Delete</a>';
                }
            }





            $value['content_title'] = '<a target="_blank" href="' . $val_co_url . '">' . $value['content_title'] . '</a>';
            $href = site_url() . '/content/?lang=en&mode=view&content_id=' . FreelinguistContentHelper::encode_id($value['id']) ;
            $value['bare_id'] = $value['id'];
            $value['id'] = "<a href='$href' target='_blank'>" . $value['id'] . "</a>";

            $value['number_sold'] = (int)$value['number_sold'];
            if (empty($value['number_sold'])) {
                $value['number_sold'] = '';
            }

            $result[] = $value;
        }
        return $result;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  array $item Data
     *
     * @return Mixed
     */

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['bare_id']
        );
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'user_email':
            case 'content_title':
            case 'publish_type':
            case 'content_sale_type':
            case 'content_amount':
            case 'created_at':
            case 'action':
                return $item[$column_name];
            case 'parent_content_id':
                return $item['parent_url'];
            case 'number_sold':
                return $item['number_sold'];
            default:
                return print_r($item, true);
        }
    }



}

?>