<?php
/*
 * Plugin Name: EmailTemplate Table
 * Description: EmailTemplate_Wp_List_Table
 * Plugin URI: http://www.paulund.co,
 * Author: Lakhvidner
 * Author URI: http://www.Lakhvidner.com
 * Version: 1.0
 */


/*
    * current-php-code 2021-Jan-11
    * input-sanitized :
    * current-wp-template:  admin-screen  for email templates
*/



/**
 * Paulund_Wp_List_Table class will create the page to load the table
 */
class AdminPageEmailTemplates
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
        add_action('admin_menu', array($this, 'add_menu_EmailTemplate_list_table_page'));
    }

    /**
     * Menu item will allow us to load the page to display the table

     */
    public function add_menu_EmailTemplate_list_table_page()
    {
        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'Email Templates', 'Email Templates', 'manage_options',
                'freelinguist-admin-email-templates', array($this, 'list_table_page'), $this->position);
        } else {
            add_menu_page('Email Templates', 'Email Templates', 'manage_options',
                'freelinguist-admin-email-templates', array($this, 'list_table_page'), 'dashicons-email-alt');
        }

    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page()
    {
        $EmailTemplateListTable = new EmailTemplate_List_Table();
        $EmailTemplateListTable->prepare_items();
        ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <?php
            $template_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
            global $wpdb;
            if (isset($_REQUEST['id'])) {
                $edit_data = $wpdb->get_row("SELECT * FROM wp_email_templates WHERE id = $template_id");
            } else {
                $edit_data = '';
            }
            if (isset($_REQUEST['id']) && !empty($edit_data)) { ?>
                <?php
                if (isset($_REQUEST['update_email_template'])) {
                    $wpdb->update(
                        'wp_email_templates',
                        array(
                            'subject' => $_REQUEST['subject'],  // string
                            'content' => $_REQUEST['template_content'],  // string
                        ),
                        array('ID' => $template_id)
                    );
                    echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                <p><strong>Settings saved.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                    if (isset($template_id)) {
                        $edit_data = $wpdb->get_row("SELECT * FROM wp_email_templates WHERE id = $template_id");
                    } else {
                        $edit_data = '';
                    }
                }
                ?>
                <div class="wrap stuffbox">
                    <div class="inside">
                        <span class="bold-and-blocking larger-text">Edit Template</span>
                        <hr>
                        <form name="send_template_f" method="post" id="send_template_f"
                              action="<?php echo admin_url() . 'admin.php?page=freelinguist-admin-email-templates&id=' . $template_id; ?>&lang=en">
                            <table class="form-table">
                                <tbody>
                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Template ID</th>
                                    <td>  <?php echo $edit_data->id; ?></td>
                                </tr>
                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Title</th>
                                    <td><input style="width:100%;" readonly type="text" name="title" title="title"
                                               value="<?php echo !empty($edit_data->title) ? $edit_data->title : ''; ?>"
                                               id="title"></td>
                                </tr>
                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Description</th>
                                    <td><textarea style="width:100%;height:170px;" readonly title="Description" autocomplete="off"
                                                  name="description"><?php echo !empty($edit_data->description) ? $edit_data->description : ''; ?></textarea>
                                    </td>
                                </tr>
                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Email Subject</th>
                                    <td><input style="width:100%;" type="text" name="subject" title="subject"
                                               value="<?php echo !empty($edit_data->subject) ? $edit_data->subject : ''; ?>"
                                               id="subject"></td>
                                </tr>
                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">Email body content</th>
                                    <?php $content = !empty($edit_data->content) ? stripslashes($edit_data->content) : ''; ?>
                                    <td><?php wp_editor($content, $editor_id = 'template_content', $settings = array()); ?>  </td>
                                </tr>

                                <tr class="user-rich-editing-wrap">
                                    <th scope="row">
                                        <input type="submit" value="Update" name="update_email_template"
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
                <span class="bold-and-blocking larger-text">Email Template</span>
            <?php } else { ?>
                <span class="bold-and-blocking larger-text">Email Template</span>
            <?php } ?>
            <ul class="subsubsub"></ul>
            <?php $EmailTemplateListTable->display(); ?>
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
class EmailTemplate_List_Table extends WP_List_Table
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
        $perPage = 60;
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
            'id' => 'Template ID',
            'title' => 'Title',
            'modified_date' => 'Modified date',

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
        //return array('id' => array('id', true),'title' => array('title', true),'modified_date' => array('modified_date', true));
        return [];
    }

    /**
     * Get the table data
     *
     * @return array
     */
    private function table_data()
    {
        global $wpdb;
        $data = $wpdb->get_results("SELECT id,title,modified_date FROM wp_email_templates", ARRAY_A);
        foreach ($data as $key => $value) {
            $id = $value['id'];
            $EmailTemplate_url = get_site_url() . '/wp-admin/admin.php?page=freelinguist-admin-email-templates&id=' . $id;
            $data[$key]['title'] = '<a href="' . $EmailTemplate_url . '">' . $value['title'] . '</a>';
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
            case 'title':
            case 'modified_date':
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