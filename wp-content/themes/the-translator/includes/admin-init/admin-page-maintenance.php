<?php


/*
    * current-php-code 2021-March-30
    * input-sanitized :
    * current-wp-template:  admin-screen for writing maintenance notes
*/


/**
 * Paulund_Wp_List_Table class will create the page to load the table
 */
class AdminMaintenance extends  FreelinguistDebugging
{
    //each inherited debugging needs their own controls
    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;

    const PAGE_STUB = 'freelinguist-maintenance';
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
        add_action('admin_menu', array($this, 'on_add_menu'));

    }

    /**
     * Menu item will allow us to load the page to display the table

     */
    public function on_add_menu()
    {
        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'Maintenance Notes', 'Maintenance Manual', 'manage_options',
                static::PAGE_STUB, array($this, 'create_admin_body'), $this->position);
        } else {
            add_menu_page('Maintenance', 'All Transactions', 'manage_options',
                static::PAGE_STUB, array($this, 'create_admin_body'), 'dashicons-list-view');
        }

    }




    public function create_admin_body()
    {
        ?>
        <style>

        </style>


        <div class="wrap">
            <div class="wrap stuffbox" style="padding: 15px;">

                <h1>Notes (this is for adding detailed instructions for maintaining this website)</h1>

                <!-- todo add Notes here   -->

                <h2>Cron Jobs</h2>
                All cron jobs are scheduled by action scheduler. The logs can be viewed in Tools->Scheduled Actions. "Scheduled Date" is the interval from current time for the job scheduled.
                <p> To enable cron jobs:</p>
                <p>Linux:   */3 * * * * curl https://development:development@development.daiyan8.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1</p>
                <p>Windows:  "powershell -windowstyle hidden" "Invoke-WebRequest http://test.com/wp-cron.php?doing_wp_cron"</p>
                <p>Tools->Scheduled Actions->search "delete" to find related jobs scheduled and error reports. </p>
                <p>Disable WP cron: in wp-config.php, </p>
                <p>        define('DISABLE_WP_CRON', true); </p>

                <h2>ElasticSearch</h2>
                <p>Configure Server IP and Port.</p>

                <h2>Email</h2>
                GD Mail Queue is used to handle all email sending. Settings-> PhPMailer-> PhP Mail Function. We don't use any SMTP service.
                In Linux, use default mail send function.
                Ini Windows, use PaperCut for email send/receive service for development.


                <h2>Contact US</h2>

                To view the messages in Contact Us page, open Flamingo, which is a plugin for viewing messages received in Contact Form 7. .




        </div>


        <?php

    }
}

AdminMaintenance::turn_on_debugging(FreelinguistDebugging::LOG_DEBUG);

