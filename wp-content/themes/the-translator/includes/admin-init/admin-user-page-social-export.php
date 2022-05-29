<?php
/*
    * current-php-code 2021-Jan-13
    * input-sanitized :
    * current-wp-template:  admin-screen (under user menu) for exporting social contacts
*/

/**
 * CSV for social contacts
 *
 * @since 0.1
 **/
class AdminUserPageSocialExport
{

    /**
     * Class constructor
     *
     * @since 0.1
     **/
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_pages'));
        add_action('init', array($this, 'generate_csv'));
        add_filter('pp_eu_exclude_data', array($this, 'exclude_data'));
    }

    /**
     * Add administration menus
     *
     * @since 0.1
     **/
    public function add_admin_pages()
    {
        add_users_page(__('Export Social user to CSV', 'export-social-users-to-csv'), __('Export Social user to CSV', 'export-social-users-to-csv'), 'list_users', 'export-social-users-to-csv', array($this, 'users_page'));
    }

    /**
     * Process content of CSV file
     *
     * @since 0.1
     **/
    public function generate_csv()
    {
        if (isset($_POST['_wpnonce-pp-eu-export-users-users-page_export_social'])) {
            check_admin_referer('pp-eu-export-users-users-page_export', '_wpnonce-pp-eu-export-users-users-page_export_social');

            global $wpdb;
            $prefix = $wpdb->prefix;
            $table_file = $prefix . 'wsluserscontacts';
            $users = $wpdb->get_results("SELECT * FROM $table_file");

            $filename = 'social_users.' . date('Y-m-d-H-i-s') . '.csv';

            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Content-Type: text/csv; charset=' . get_option('blog_charset'), true);
            /*$fp = */ fopen('php://output', 'w');
            $fields = array("Friend of", "Provider", "Identifier", "Full name", "Email", "Profile url", "photo_url");
            $headers = array();
            foreach ($fields as $key => $field) {
                $headers[] = '"' . strtolower($field) . '"';
            }
            echo implode(',', $headers) . "\n";
            foreach ($users as $user) {
                $data = array();
                $value = $user->user_id;
                $user_info = get_userdata($value);
                if (!empty($user_info)) {
                    $value = $user_info->user_login;
                } else {
                    $value = '';
                }
                $value = is_array($value) ? serialize($value) : $value;
                $data[] = '"' . str_replace('"', '""', $value) . '"';

                $value = $user->provider;
                $value = is_array($value) ? serialize($value) : $value;
                $data[] = '"' . str_replace('"', '""', $value) . '"';


                $value = $user->identifier;
                $value = is_array($value) ? serialize($value) : $value;
                $data[] = '"' . str_replace('"', '""', $value) . '"';

                $value = $user->full_name;
                $value = is_array($value) ? serialize($value) : $value;
                $data[] = '"' . str_replace('"', '""', $value) . '"';


                $value = $user->email;
                $value = is_array($value) ? serialize($value) : $value;
                $data[] = '"' . str_replace('"', '""', $value) . '"';


                $value = $user->profile_url;
                $value = is_array($value) ? serialize($value) : $value;
                $data[] = '"' . str_replace('"', '""', $value) . '"';

                $value = $user->photo_url;
                $value = is_array($value) ? serialize($value) : $value;
                $data[] = '"' . str_replace('"', '""', $value) . '"';
                echo implode(',', $data) . "\n";
            }
            exit;
        }
    }

    /**
     * Content of the settings page
     *
     * @since 0.1
     **/
    public function users_page()
    {
        if (!current_user_can('list_users'))
            wp_die(__('You do not have sufficient permissions to access this page.', 'export-social-users-to-csv'));
        ?>

        <div class="wrap">
        <span class="bold-and-blocking large-text"><?php _e('Export Social users to a CSV file', 'export-social-users-to-csv'); ?></span>

        <form method="post" action="" enctype="multipart/form-data">
            <?php wp_nonce_field('pp-eu-export-users-users-page_export', '_wpnonce-pp-eu-export-users-users-page_export_social'); ?>

            <p class="submit">
                <input type="hidden" name="_wp_http_referer" value="<?php echo $_SERVER['REQUEST_URI'] ?>"/>
                <input type="submit" class="button-primary"
                       value="<?php _e('Export', 'export-social-users-to-csv'); ?>"/>
            </p>
        </form>
        <?php
    }

    public function exclude_data()
    {
        $exclude = array('user_pass', 'user_activation_key');

        return $exclude;
    }

    public function pre_user_query($user_search)
    {
        global $wpdb;

        $where = '';

        if (!empty($_POST['start_date']))
            $where .= $wpdb->prepare(" AND $wpdb->users.user_registered >= %s", date('Y-m-d', strtotime($_POST['start_date'])));

        if (!empty($_POST['end_date']))
            $where .= $wpdb->prepare(" AND $wpdb->users.user_registered < %s", date('Y-m-d', strtotime('+1 month', strtotime($_POST['end_date']))));

        if (!empty($where))
            $user_search->query_where = str_replace('WHERE 1=1', "WHERE 1=1$where", $user_search->query_where);

        return $user_search;
    }

}


