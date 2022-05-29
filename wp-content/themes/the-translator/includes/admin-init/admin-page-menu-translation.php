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
 * current-wp-template:  admin-screen  custom header menu translation
 */



/**
 * Paulund_Wp_List_Table class will create the page to load the table
 */
class AdminPageMenuTranslation
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
        add_action('admin_menu', array($this, 'add_custom_string_translation'));
    }

    /**
     * Menu item will allow us to load the page to display the table

     */
    public function add_custom_string_translation()
    {
        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'Header Menu Translation', 'Header Menu Translation', 'manage_options',
                'freelinguist-admin-menu-translation', array($this, 'list_table_page'), $this->position);
        } else {
            add_menu_page('Header Menu Translation', 'Header Menu Translation', 'manage_options',
                'freelinguist-admin-menu-translation', array($this, 'list_table_page'), 'dashicons-format-aside');
        }


    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page()
    {
        $type = 1;
        ?>
        <div class="wrap">
            <span class="bold-and-blocking large-text">Header Menu Translation</span>
            <?php if (isset($_REQUEST['update_menu_custom_string_translation_button'])) {
                global $wpdb;
                $edit_id = $_REQUEST['id'];
                $english = $_REQUEST['english'];
                $chinese = $_REQUEST['chinese'];
                $russian = $_REQUEST['russian'];
                $japanese = $_REQUEST['japanese'];
                $german = $_REQUEST['german'];
                $spanish = $_REQUEST['spanish'];
                $french = $_REQUEST['french'];
                $portuguese = $_REQUEST['portuguese'];
                $italian = $_REQUEST['italian'];
                $polish = $_REQUEST['polish'];
                $turkish = $_REQUEST['turkish'];
                $persian = $_REQUEST['persian'];
                $chinese_traditional = $_REQUEST['chinese_traditional'];
                $danish = $_REQUEST['danish'];
                $dutch = $_REQUEST['dutch'];
                $hindi = $_REQUEST['hindi'];
                $arabic = $_REQUEST['arabic'];
                $korean = $_REQUEST['korean'];
                $czech = $_REQUEST['czech'];
                $vietnamese = $_REQUEST['vietnamese'];
                $indonesian = $_REQUEST['indonesian'];
                $swedish = $_REQUEST['swedish'];
                $malay = $_REQUEST['malay'];


                $wpdb->update(
                    'wp_custom_string_translation',
                    array(
                        'english' => $english,
                        'chinese' => trim($chinese),
                        'russian' => trim($russian),
                        'japanese' => trim($japanese),
                        'german' => trim($german),
                        'spanish' => trim($spanish),
                        'french' => trim($french),
                        'portuguese' => trim($portuguese),
                        'italian' => trim($italian),
                        'polish' => trim($polish),
                        'turkish' => trim($turkish),
                        'persian' => trim($persian),
                        'chinese_traditional' => trim($chinese_traditional),
                        'danish' => trim($danish),
                        'dutch' => trim($dutch),
                        'hindi' => trim($hindi),
                        'arabic' => trim($arabic),
                        'korean' => trim($korean),
                        'czech' => trim($czech),
                        'vietnamese' => trim($vietnamese),
                        'indonesian' => trim($indonesian),
                        'swedish' => trim($swedish),
                        'malay' => trim($malay),
                        'modified_date' => current_time('mysql')
                    ),
                    array('id' => $edit_id)
                ); ?>
            <?php } ?>
            <?php if (isset($_REQUEST['add_menu_custom_string_translation_button'])) {
                global $wpdb;
                $english = $_REQUEST['english'];
                $chinese = $_REQUEST['chinese'];
                $russian = $_REQUEST['russian'];
                $japanese = $_REQUEST['japanese'];
                $german = $_REQUEST['german'];
                $spanish = $_REQUEST['spanish'];
                $french = $_REQUEST['french'];
                $portuguese = $_REQUEST['portuguese'];
                $italian = $_REQUEST['italian'];
                $polish = $_REQUEST['polish'];
                $turkish = $_REQUEST['turkish'];
                $persian = $_REQUEST['persian'];
                $chinese_traditional = $_REQUEST['chinese_traditional'];
                $danish = $_REQUEST['danish'];
                $dutch = $_REQUEST['dutch'];
                $hindi = $_REQUEST['hindi'];
                $arabic = $_REQUEST['arabic'];
                $korean = $_REQUEST['korean'];
                $czech = $_REQUEST['czech'];
                $vietnamese = $_REQUEST['vietnamese'];
                $indonesian = $_REQUEST['indonesian'];
                $swedish = $_REQUEST['swedish'];
                $malay = $_REQUEST['malay'];

                $wpdb->insert(
                    'wp_custom_string_translation',
                    array(
                        'english' => trim($english),
                        'chinese' => trim($chinese),

                        'russian' => trim($russian),
                        'japanese' => trim($japanese),
                        'german' => trim($german),
                        'spanish' => trim($spanish),
                        'french' => trim($french),
                        'portuguese' => trim($portuguese),
                        'italian' => trim($italian),
                        'polish' => trim($polish),
                        'turkish' => trim($turkish),
                        'persian' => trim($persian),
                        'chinese_traditional' => trim($chinese_traditional),
                        'danish' => trim($danish),
                        'dutch' => trim($dutch),
                        'hindi' => trim($hindi),
                        'arabic' => trim($arabic),
                        'korean' => trim($korean),
                        'czech' => trim($czech),
                        'vietnamese' => trim($vietnamese),
                        'indonesian' => trim($indonesian),
                        'swedish' => trim($swedish),
                        'malay' => trim($malay),
                        'type' => $type,
                        'modified_date' => current_time('mysql')
                    )
                ); ?>
            <?php } ?>
            <?php if (isset($_REQUEST['edit_id'])) {
                $edit_id = $_REQUEST['edit_id'];
                global $wpdb;
                $row_data = $wpdb->get_row("SELECT * FROM wp_custom_string_translation where id = $edit_id"); ?>
                <form action="" method="post" name="update_custom_string_translation">
                    <table>
                        <tr>
                            <?php
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
                            ?>
                            <?php $i = 0; ?>
                            <?php foreach ($columns_lang

                            as $key => $value) { ?>
                            <td>
                                <?php if ($key == 'english') { ?>
                                    <input type="text" name="<?php echo $key; ?>"
                                           value="<?php echo $row_data->$key; ?>" title="key">
                                    <p class="description"><?php echo $value; ?></p>
                                <?php } else { ?>
                                    <input type="text" name="<?php echo $key; ?>" title="key"
                                           value="<?php echo $row_data->$key; ?>">
                                    <p class="description"><?php echo $value; ?></p>
                                <?php } ?>
                            </td>
                            <?php if ($i % 6 == 5) { ?>
                        </tr>
                        <tr>
                            <?php } ?>
                            <?php $i++; ?>

                            <?php } ?>
                        <tr>
                            <td>
                                <input type="hidden" name="id" value="<?php echo $row_data->id; ?>">
                                <input type="submit" class="button button-primary"
                                       name="update_menu_custom_string_translation_button" value=" UPDATE "
                                       id="update_custom_string_translation_button">
                                <p class="description">&nbsp;</p>
                            </td>
                        </tr>
                    </table>
                </form>
            <?php } ?>

            <?php if (isset($_REQUEST['add_new_string'])) { ?>
                <form action="" method="post" name="add_menu_custom_string_translation">
                    <table>
                        <tr>
                            <th colspan="5">
                                <h3 style="float:left">Add New Header Menu</h3><br>
                            </th>
                        </tr>
                        <?php
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

                        ?>
                        <tr>
                            <?php $i = 0; ?>
                            <?php foreach ($columns_lang

                            as $key => $value) { ?>
                            <td>
                                <input type="text" placeholder="<?php //echo $value; ?>" name="<?php echo $key; ?>"
                                       value="">
                                <p class="description"><?php echo $value; ?></p>
                            </td>
                            <?php if ($i % 6 == 5) { ?>
                        </tr>
                        <tr>
                            <?php } ?>
                            <?php $i++; ?>

                            <?php } ?>
                        <tr>
                            <td>
                                <input type="submit" class="button button-primary"
                                       name="add_menu_custom_string_translation_button" value=" Add "
                                       id="add_custom_string_translation_button">
                                <p class="description">&nbsp;</p>
                            </td>
                        </tr>
                    </table>
                </form>
            <?php } ?>
            <?php $add_link = admin_url() . 'admin.php?page=freelinguist-admin-menu-translation&add_new_string=true'; ?>
            <a style="float:right" class="button button-primary" href="<?php echo $add_link; ?>">Add New Header Menu</a>
            <?php
            $Custom_string_translation_List_Table = new Menu_Custom_string_translation_List_Table();
            $Custom_string_translation_List_Table->prepare_items();
            $Custom_string_translation_List_Table->display();
            ?>
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
class Menu_Custom_string_translation_List_Table extends WP_List_Table
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
        $perPage = 100;
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
            'edit_link' => 'Action',
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
            'malay' => 'Malay'
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
        $data = $wpdb->get_results("SELECT * FROM wp_custom_string_translation where type = 1", ARRAY_A);
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $edit_link = admin_url() . 'admin.php?page=freelinguist-admin-menu-translation&edit_id=' . $value['id'];
                $data[$key]['edit_link'] = '<a class="edit" href="' . $edit_link . '">Edit</a>';
            }
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
            case 'edit_link':
            case 'english':
            case 'chinese':
            case 'russian':
            case 'japanese':
            case 'german':
            case 'spanish':
            case 'french':
            case 'portuguese':
            case 'italian':
            case 'polish':
            case 'turkish':
            case 'persian':
            case 'chinese_traditional':
            case 'danish':
            case 'dutch':
            case 'hindi':
            case 'arabic':
            case 'korean':
            case 'czech':
            case 'vietnamese':
            case 'indonesian':
            case 'swedish':
            case 'malay':
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