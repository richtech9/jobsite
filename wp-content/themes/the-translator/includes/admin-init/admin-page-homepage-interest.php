<?php
/*
 * Plugin Name: Homepage_Interest Table
 * Description: Homepage_Interest_Wp_List_Table
 * Plugin URI: http://www.paulund.co,
 * Author: Lakhvidner
 * Author URI: http://www.Lakhvidner.com
 * Version: 1.0
 */

/*
    * current-php-code 2021-Jan-11
    * input-sanitized : lang
    * current-wp-template:  admin-screen  for tags, units, and cron
*/



/**
 * Homepage_Interest_Wp_List_Table class will create the page to load the table
 */
class AdminPageHomepageInterest
{
    public $parent_slug = null;
    public $position = null;
    const PAGE_STUB = 'freelinguist-admin-homepage-interest';
    /**
     * Constructor will create the menu item
     * @param string $parent_slug
     * @param int $position
     */
    public function __construct($parent_slug = null,$position = null)
    {
        $this->parent_slug = $parent_slug;
        $this->position = $position;
        add_action('admin_menu', array($this, 'add_menu_Homepage_Interest_list_table_page'));
    }

    /**
     * Menu item will allow us to load the page to display the table

     */
    public function add_menu_Homepage_Interest_list_table_page()
    {
        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'PeerOK ElasticSearch,Tag, Homepage', 'PeerOK ES,Tag, Homepage Display',
                'manage_options',static::PAGE_STUB , array($this, 'list_table_page'), $this->position);
        } else {
            add_menu_page('PeerOK ElasticSearch,Tag, Homepage', 'PeerOK ES, Tags, Units, Cron',
                'manage_options', static::PAGE_STUB, array($this, 'list_table_page'), 'dashicons-format-chat',$this->position);
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
        <div class="wrap" id="">
            <div id="icon-users" class="icon32"></div>
            <?php

            if (isset($_REQUEST['send_interest_btn'])) {
                if (!empty($_REQUEST['title'])) {
                    global $wpdb;
                    $option = isset($_REQUEST['select_option']) ? $_REQUEST['select_option'] : '';
                    $title = isset($_REQUEST['title']) ? $_REQUEST['title'] : '';
                    $priority_number = isset($_REQUEST['priority_number']) ? $_REQUEST['priority_number'] : '';
                    $havePriorty = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}homepage_interest WHERE priority_number=$priority_number");
                    if ($havePriorty) {
                        $maxPriorty = $wpdb->get_row(/** @lang text */
                            "SELECT max(priority_number) max FROM {$wpdb->prefix}homepage_interest", ARRAY_A);
                        // print_r($maxPriorty);
                        $priority_number = $maxPriorty['max'] + 1;
                    }
                    $translation_type = isset($_REQUEST['translation_type']) ? $_REQUEST['translation_type'] : '';
                    $language = isset($_REQUEST['language']) ? $_REQUEST['language'] : '';
                    $rating = isset($_REQUEST['rating']) ? $_REQUEST['rating'] : '';
                    $success_rate = isset($_REQUEST['success_rate']) ? $_REQUEST['success_rate'] : '';
                    $tag_id = isset($_REQUEST['tag_id']) ? $_REQUEST['tag_id'] : '';

                    if ($rating == '') {
                        $wpdb->insert(
                            'wp_homepage_interest',
                            array(
                                'option' => strip_tags($option),
                                'title' => strip_tags($title),
                                'tag_id' => $tag_id,
                                'priority_number' => strip_tags($priority_number),
                                'translation_type' => strip_tags($translation_type),
                                'language' => strip_tags($language),
                            ));

                    } else {
                        $wpdb->insert(
                            'wp_homepage_interest',
                            array(
                                'option' => strip_tags($option),
                                'title' => strip_tags($title),
                                'tag_id' => $tag_id,
                                'priority_number' => strip_tags($priority_number),
                                'translation_type' => strip_tags($translation_type),
                                'language' => strip_tags($language),
                                'rating' => strip_tags($rating),
                                'success_rate' => strip_tags($success_rate)
                            ));
                    }
                    echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                                    <p><strong>Saved.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

                } else {
                    echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                                <p><strong style="color:red">All the fields are required.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                }
            }

            if (isset($_REQUEST['delete_interest'])) {
                global $wpdb;


                $delete_interest_id = (int)$_REQUEST['delete_interest'];

                //code-notes clearing out unit cache for the interest about to be removed
                FreelinguistUnitGenerator::remove_compiled_units_from_es_cache($log,$delete_interest_id,[]);
                $wpdb->delete('wp_homepage_interest', array('id' => $delete_interest_id));
                echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
                                    <p><strong>Deleted.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
            }


            //code-notes start new logic for doing top tag rebuild task

            if (isset($_POST['rebuild_top_tags'])) {

                if (isset($_POST['yes_i_want_to_rebuild_top_tags']) && !empty($_POST['yes_i_want_to_rebuild_top_tags'])) {

                    //code-notes clearing the unit cache here
                    try {

                        FreelinguistCronTopUnitsRebuildAll::run();
                        FreelinguistCronTopUnitsRebuildAll::clear_top_tags(); //special step before starting the task
                        $logs = FreelinguistCronTopUnitsRebuildAll::get_log();
                        ?>

                        <div class="notice notice-success is-dismissible">
                            <p>
                                Scheduled Cron Job to Rebuild Top Tags. It will start soon:
                                <?php foreach ($logs as $log) {?>
                                    <span class="cron-log-line"><?=$log ?></span>
                                <?php }?>
                            </p>
                        </div>
                        <?php
                    } catch (Exception $e) {
                        ?>

                        <div class="notice notice-success is-dismissible">
                            <p>
                                Error Starting Cron Job to Rebuild Top Tags :
                                <span class="cron-log-error"><?=$e->getMessage()?></span>
                            </p>
                        </div>
                        <?php
                    }


                } else {
                    ?>
                    <div class="notice notice-warning is-dismissible">
                        <p>Need to check the selection to Rebuild Top Tags</p>
                    </div>
                    <?php
                }
            }

            if (isset($_POST['cancel_rebuild_top_tags_cron'])) {
                FreelinguistCronTopUnitsRebuildAll::stop();
                $all_logs = FreelinguistCronTopUnitsRebuildAll::get_log();
                $log_size = count($all_logs);
                $log_line = '';
                if ($log_size) {
                    $log_line = $all_logs[$log_size-1];
                }
                ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Cron Job to Rebuild Top Tags will not run until started again
                        <span class="cron-log-line"><?=$log_line?></span>
                    </p>
                </div>
                <?php
            }

            if (isset($_POST['resume_rebuild_top_tags_cron'])) {
                FreelinguistCronTopUnitsRebuildAll::resume();
                $all_logs = FreelinguistCronTopUnitsRebuildAll::get_log();
                $log_size = count($all_logs);
                $log_line = '';
                if ($log_size) {
                    $log_line = $all_logs[$log_size-1];
                }

                ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Cron Job to Generate Top Tags will now resume
                        <span class="cron-log-line"><?=$log_line?></span>
                    </p>
                </div>
                <?php
            }

            //code-notes end new logic section for top tags



            if (isset($_POST['clear_unit_templates'])) {

                if (isset($_POST['yes_i_want_to_clear_units']) && !empty($_POST['yes_i_want_to_clear_units'])) {

                    //code-notes clearing the unit cache here
                    try {
                        FreelinguistCronTopUnitsClear::run();
                        $log_line = FreelinguistCronTopUnitsClear::get_log()[0];
                        ?>

                        <div class="notice notice-success is-dismissible">
                            <p>
                                Scheduled Cron Job to Clear Units. It will start soon:
                                <span class="cron-log-line"><?=$log_line?></span>
                            </p>
                        </div>
                        <?php
                    } catch (Exception $e) {
                        ?>

                        <div class="notice notice-success is-dismissible">
                            <p>
                                Error Starting Cron Job to Clear Units :
                                <span class="cron-log-error"><?=$e->getMessage()?></span>
                            </p>
                        </div>
                        <?php
                    }


                } else {
                    ?>
                    <div class="notice notice-warning is-dismissible">
                        <p>Need to check the selection to clear units</p>
                    </div>
                    <?php
                }
            }

            if (isset($_POST['cancel_unitclear_cron'])) {
                FreelinguistCronTopUnitsClear::stop();
                $all_logs = FreelinguistCronTopUnitsClear::get_log();
                $log_size = count($all_logs);
                $log_line = '';
                if ($log_size) {
                    $log_line = $all_logs[$log_size-1];
                }
                ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Cron Job to Clear Units will not run until started again
                        <span class="cron-log-line"><?=$log_line?></span>
                    </p>
                </div>
                <?php
            }

            if (isset($_POST['resume_unitclear_cron'])) {
                FreelinguistCronTopUnitsClear::resume();
                $all_logs = FreelinguistCronTopUnitsClear::get_log();
                $log_size = count($all_logs);
                $log_line = '';
                if ($log_size) {
                    $log_line = $all_logs[$log_size-1];
                }

                ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Cron Job to Clear Units will now resume
                        <span class="cron-log-line"><?=$log_line?></span>
                    </p>
                </div>
                <?php
            }


            if (isset($_POST['cancel_unit_cron'])) {
                FreelinguistCronTopUnitsGenerate::stop();
                $all_logs = FreelinguistCronTopUnitsGenerate::get_log();
                $log_size = count($all_logs);
                $log_line = '';
                if ($log_size) {
                    $log_line = $all_logs[$log_size-1];
                }
                ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Cron Job to Regeneate Units will not run until started again
                        <span class="cron-log-line"><?=$log_line?></span>
                    </p>
                </div>
                <?php
            }

            if (isset($_POST['resume_unit_cron'])) {
                FreelinguistCronTopUnitsGenerate::resume();
                $all_logs = FreelinguistCronTopUnitsGenerate::get_log();
                $log_size = count($all_logs);
                $log_line = '';
                if ($log_size) {
                    $log_line = $all_logs[$log_size-1];
                }

                ?>

                <div class="notice notice-success is-dismissible">
                    <p>
                        Cron Job to Build Units will now resume
                        <span class="cron-log-line"><?=$log_line?></span>
                    </p>
                </div>
                <?php
            }


            if (isset($_POST['rebuild_unit_templates'])) {


                if (isset($_POST['yes_i_want_to_rebuild_units']) && !empty($_POST['yes_i_want_to_rebuild_units'])) {

                    //code-notes rebuilding the unit cache here
                    try {
                        FreelinguistCronTopUnitsGenerate::run();
                        $log_line = FreelinguistCronTopUnitsGenerate::get_log()[0];
                        ?>

                        <div class="notice notice-success is-dismissible">
                            <p>
                                Scheduled Cron Job to Build Units. It will start soon:
                                <span class="cron-log-line"><?=$log_line?></span>
                            </p>
                        </div>
                        <?php
                    } catch (Exception $e) {
                        ?>

                        <div class="notice notice-success is-dismissible">
                            <p>
                                Error Starting Cron Job to Build :
                                <span class="cron-log-error"><?=$e->getMessage()?></span>
                            </p>
                        </div>
                        <?php
                    }

                } else {
                    ?>
                    <div class="notice notice-warning is-dismissible">
                        <p>Need to check the selection to build units</p>
                    </div>
                    <?php
                }
            }

            //cancel_red_dots_cron,resume_red_dots_cron,yes_i_want_to_update_red_dots_timer,red_dots_interval_in_seconds

            if (isset($_POST['yes_i_want_to_update_unit_timer']) ) {

                $unit_time_interval_in_seconds = (int)$_POST['units_rebuild_interval_in_seconds'];
                try {
                    FreelinguistCronTopUnitsTimerRepeat::set_timer_seconds($unit_time_interval_in_seconds);
                    if ($unit_time_interval_in_seconds) {
                        FreelinguistCronTopUnitsTimerRepeat::stop();
                        FreelinguistCronTopUnitsTimerRepeat::run();
                        ?>
                        <div class="notice notice-success is-dismissible">
                            <p>
                                The timed repeat has been scheduled to run every
                                <b><?= $unit_time_interval_in_seconds?></b> Seconds
                            </p>
                        </div>
                        <?php
                    } else {
                        try {
                            FreelinguistCronTopUnitsTimerRepeat::stop();
                        } catch (Exception $e) {
                            ?>

                            <div class="notice notice-success is-dismissible">
                                <p>
                                    Error Stopping Cron Job to Build :
                                    <span class="cron-log-error"><?=$e->getMessage()?></span>
                                </p>
                            </div>
                            <?php
                        }


                    }


                }   catch(Exception $e) {
                    ?>

                    <div class="notice notice-success is-dismissible">
                        <p>
                            Error setting the refresh time user :
                            <span class="cron-log-error"><?=$e->getMessage()?></span>
                        </p>
                    </div>
                    <?php
                }
            }

            if (isset($_POST['cancel_unitrepeat_cron'])) {
                try {
                    FreelinguistCronTopUnitsTimerRepeat::stop();

                    $all_logs = FreelinguistCronTopUnitsTimerRepeat::get_log();
                    $log_size = count($all_logs);
                    $log_line = '';
                    if ($log_size) {
                        $log_line = $all_logs[$log_size-1];
                    }
                    ?>

                    <div class="notice notice-success is-dismissible">
                        <p>
                            Cron Job to Build Units will not run until started again
                            <span class="cron-log-line"><?=$log_line?></span>
                        </p>
                    </div>
                    <?php

                } catch (Exception $e) {
                    ?>

                    <div class="notice notice-success is-dismissible">
                        <p>
                            Error Stopping Repeat Cron Job to Build Units :
                            <span class="cron-log-error"><?=$e->getMessage()?></span>
                        </p>
                    </div>
                    <?php
                }



            }

            if (isset($_POST['resume_unitrepeat_cron'])) {
                try {
                    FreelinguistCronTopUnitsTimerRepeat::resume();
                    $all_logs = FreelinguistCronTopUnitsTimerRepeat::get_log();
                    $log_size = count($all_logs);
                    $log_line = '';
                    if ($log_size) {
                        $log_line = $all_logs[$log_size - 1];
                    }

                    ?>

                    <div class="notice notice-success is-dismissible">
                        <p>
                            Cron Job to Generate Units Repeatedly  will now Resume
                            <span class="cron-log-line"><?= $log_line ?></span>
                        </p>
                    </div>
                    <?php
                } catch (Exception $e) {
                    ?>

                    <div class="notice notice-success is-dismissible">
                        <p>
                            Error Stopping Repeat Cron Job to Build Units :
                            <span class="cron-log-error"><?=$e->getMessage()?></span>
                        </p>
                    </div>
                    <?php
                }
            }

            FreelinguistCronRedDotActions::process_cron_controls();

            if (isset($_POST['clear_allindexes'])) {
                if (isset($_POST['clearallindexs'])) {
                    $clearallindex = $_POST['clearallindexs'];
                    if ($clearallindex == 1) {
                        $allindexarr = array('project', 'content', 'contest', 'translator');

                        if (count($allindexarr) > 0) {

                            try {

                                $es = new FreelinguistElasticSearchHelper();
                                $client = $es->get_client();
                                $params = [
                                    // 'index' => array('project','contest')
                                    'index' => implode(',',$allindexarr)
                                ];
                                $response = $client->indices()->delete($params);

                                if (isset($response['acknowledged']) && $response['acknowledged'] == 1) {
                                    ?>
                                    <div class="notice notice-success is-dismissible">
                                        <p>Deleteing indexes Success!!</p>
                                        <?php will_dump("ES says ", $response) ?>
                                    </div>
                                    <?php
                                } else {
                                    ?>
                                    <div class="notice notice-error is-dismissible">
                                        <p>Error in deleting the indexes!!</p>
                                        <p> Exception was not thrown, but issue with the return information</p>
                                        <?php will_dump("ES says ", $response) ?>
                                    </div>
                                    <?php
                                }

                            } catch (Exception $e) {
                                $connection_info = implode(", ", [
                                    FreelinguistElasticSearchHelper::$last_ip_used,
                                    FreelinguistElasticSearchHelper::$last_ip_used
                                ]);

                                ?>
                                <div class="notice notice-error is-dismissible">
                                    <p>Error in deleting the indexes!!</p>
                                    <p> Connection was <?= $connection_info ?> </p>
                                    <?php will_dump("Exception ", $e) ?>
                                </div>
                                <?php
                            }
                        }

                    }
                } else {
                    echo '<div class="notice notice-error is-dismissible">
									<p>Selection for clear index is required!!</p>
								</div>';
                }
            }


            if (isset($_POST['elastic_server_options']) && !empty($_POST['elastic_server_options'])) {
                $elastic_ip = '';
                $elastic_port = '';
                if (array_key_exists('elastic_server_ip', $_POST)) {
                    $elastic_ip = trim($_POST['elastic_server_ip']);
                }
                if (array_key_exists('elastic_server_port', $_POST)) {
                    $elastic_port = trim($_POST['elastic_server_port']);
                }
                if (!$elastic_ip || !$elastic_port) {
                    echo '<div class="notice notice-error is-dismissible">
											<p>Please set both the ip and the port for the Elastic Search Settings</p>
										</div>';
                } else {
                    $elastic_options = [
                        'elastic_server_ip' => $elastic_ip,
                        'elastic_server_port' => $elastic_port
                    ];
                    update_option('elasticsearch_option', $elastic_options);
                    echo '<div class="notice notice-success is-dismissible">
											<p>Success for setting Elastic Search Settings!!</p>
										</div>';
                }
            }//end if for elastic_server_options


            if (isset($_POST['basic_page_auth_options']) && !empty($_POST['basic_page_auth_options'])) {
                $basic_username = '';
                $basic_password = '';
                $b_log = false;
                if (array_key_exists('basic_page_auth_username', $_POST)) {
                    $basic_username = trim($_POST['basic_page_auth_username']);
                }
                if (array_key_exists('basic_page_auth_password', $_POST)) {
                    $basic_password = trim($_POST['basic_page_auth_password']);
                }
                if (array_key_exists('basic_page_auth_log', $_POST)) {
                    $b_log = $_POST['basic_page_auth_log'] ? true : false;
                }

                if (($basic_username && !$basic_password) || (!$basic_username && $basic_password)) {
                    echo '<div class="notice notice-error is-dismissible">
                                        <p>When setting the Page Authentication username and password, Please set both</p>
                                    </div>';
                } else {
                    $basic_auth_options = [
                        'username' => $basic_username,
                        'password' => $basic_password,
                        'log_curl' => $b_log
                    ];
                    update_option('freelinguist_basic_page_auth', $basic_auth_options);
                    echo '<div class="notice notice-success is-dismissible">
                                        <p>Success for setting page protection login!!</p>
                                    </div>';
                }
            }//end if for basic_page_auth_options

            //code-bookmark logic for settings the homepage interest top post settings
            if (isset($_POST['top_tag_limits']) && !empty($_POST['top_tag_limits'])) {

                if (array_key_exists('freelinguist-limit-top-per-tag', $_POST)) {
                    $top_per_tag = intval(trim($_POST['freelinguist-limit-top-per-tag']));
                    if ($top_per_tag) {
                        update_option('freelinguist-limit-top-per-tag', $top_per_tag);
                    }
                }

                if (array_key_exists('freelinguist-limit-top-tags', $_POST)) {
                    $top_per_tag = intval(trim($_POST['freelinguist-limit-top-tags']));
                    if ($top_per_tag) {
                        update_option('freelinguist-limit-top-tags', $top_per_tag);
                    }
                }
            }// end if for top_tag_limits


            ?>
            <style type="text/css">
                .user-rich-editing-wrap select, .user-rich-editing-wrap input {
                    width: 442px;
                }
            </style>


            <div class="wrap stuffbox">

                <div class="inside">
                    <span class="bold-and-blocking larger-text">Elastic Server</span>
                    <?php $options = get_option('elasticsearch_option', ['elastic_server_ip' => '', 'elastic_server_port' => '']); ?>
                    <form name="" method="post" id="form_elastic_settings" action="#">
                        <table class="form-table">
                            <tbody>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">Elastic Server IP</th>
                                <td>
                                    <input title="Server IP" type="text" name="elastic_server_ip" id="elastic_server_ip"
                                           value="<?php echo $options['elastic_server_ip']; ?>">
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">Server Port</th>
                                <td>
                                    <input title="Server Port" type="text" name="elastic_server_port"
                                           id="elastic_server_port"
                                           value="<?php echo $options['elastic_server_port']; ?>">
                                </td>
                            </tr>

                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                </th>
                                <td>
                                    <input type="submit" value="Update Elastic Settings" name="elastic_server_options"
                                           class="button  button-primary"/>
                                </td>
                            </tr>


                            </tbody>
                        </table>
                    </form>
                </div> <!-- /.inside -->
            </div>  <!-- /.wrap.stuffbox -->


            <div class="wrap stuffbox">

                <div class="inside">
                    <span class="bold-and-blocking larger-text">Page Authentication Settings when Website is Password Protected (Manual ES index creation below requires CURL access to web URL)</span>
                    <?php $basic_auth_options = get_option('freelinguist_basic_page_auth',
                        ['username' => '', 'password' => '', 'log_curl' => false]); ?>
                    <form name="" method="post" id="form_basic_page_auth_settings" action="#">
                        <table class="form-table">
                            <tbody>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">Page Authentication Username</th>
                                <td>
                                    <input title="Authentication Username" type="text" name="basic_page_auth_username"
                                           id="basic_page_auth_username"
                                           value="<?php echo $basic_auth_options['username']; ?>">
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">Page Authentication Password</th>
                                <td>
                                    <input title="Authentication Password" type="text" name="basic_page_auth_password"
                                           id="basic_page_auth_password"
                                           value="<?php echo $basic_auth_options['password']; ?>">
                                </td>
                            </tr>

                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                    Log Curl Call Details
                                </th>
                                <td>
                                    <?php
                                    $checked = '';
                                    if ($basic_auth_options['log_curl']) {
                                        $checked = 'CHECKED';
                                    }
                                    ?>
                                    <input title="Log Curl Auth Calls" type="checkbox" name="basic_page_auth_log"
                                           id="basic_page_auth_log" class="" value="1" <?= $checked ?>>
                                </td>
                            </tr>

                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                </th>
                                <td>
                                    <input type="submit" value="Update Page Authentication Settings"
                                           name="basic_page_auth_options" class="button  button-primary"/>
                                </td>
                            </tr>
                            <!--  -->

                            </tbody>
                        </table>
                    </form>
                </div> <!-- /.inside -->
            </div>  <!-- /.wrap.stuffbox -->

        <div class="freelinguist-log-panel">
            <div class="freelinguist-log-control">
                <button class="button  freelinguist-show-logs freelinguist-action-popup enhanced-text" type="button">
                    Show Notes and Instructions
                </button>
            </div>
            <div class="freelinguist-log-outer-container">
                <div class="freelinguist-log-area">


                    <p>

                        Each project/contest/content/freelancer profile has skill tags. They are displayed as relatvent jobs in homepage, pages of content, and pages of freelancer profiles.
                    </p>
                    <p>

                        Use cases: In homepage: Admin user enteres a list of homepage interest (tag) in the bottom section. For each tag, can choose to hide tag name or not. Besides, admin can also decide to manually enter the IDs for each tag or not, or use the HTML units described below.
                        Then, for each tag, display their corresponding contents/profiles.
                    </p>
                    <p>
                        In Content page/Freelancer profile page, for each tag of this content/profile, display the related top score contents/profiles.
                    </p>
                    <p>

                        Tables: wp_interest_tags: stores name of all tags. When user edit skill tags, if it's a new tag, it'll be inserted into table: wp_interest_tags.
                    </p>
                    <p>

                        To view/add/detelete them: Admin panel-> Interest Tag List
                    </p>
                    <p>

                        wp_tags_cache_job: for each tag, store each ID of project/contest/content/freelancer in a single row for easier usage and display.
                    </p>
                    <p>

                        wp_homepage_interest: This table stores all tags displayed on homepage.
                    </p>
                    <p>

                        wp_homepage_interest_per_id: Admin wants to manually enter the content/profiles that are displayed for each tag. For each tag in Homepage interest section (stored in wp_homepage_interest), the admin user can enter a list of content IDs, or freelancer emails. These will be displayed on homepage instead.
                    </p>
                    <p>

                        wp_display_unit_user_content:
                        For each tag, top 10 score users/contents are stored in wp_display_unit_user_content table.
                    </p>
                    <p>

                        The score is determined based on users's rating, last activity time, etc. Detailed formula can be found below.  For example:
                    </p>
                    <p>

                        User score: 	Score =  (Average rating+1) * last login timestamp;
                    </p>
                    <p>

                    Content score:  Score =  (Average rating+1) * creation/update timestamp;
                    </p>
                    <p>

                        An example:
                        5* 1598477510
                    </p>
                    <p>


                        #Step One: Cron Job.  executed once in an hour (configured below) using action-scheduler/cron job.
                        It'll get all top 200 tags based on their  Usage count in wp_tags_cache_job.
                    </p>
                    <p>

                    Then for each tag, it'll get users and content in wp_display_unit_user_content table (it stores only top 10 score content/users).
                    </p>

                    <p>
                        Cron Job:
                    </p>
                    <p>    sudo su - peerok </p>
                    <p>     crontab -e
                    </p>
                    <p>
                        */3 * * * * curl https://development:development@development.daiyan8.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1

                    </p>

                </div> <!-- /.freelingust-log-area -->
            </div> <!-- /.freelingust-log-outer-container-->
        </div> <!-- /.freelingust-log-panel-->


            <div class="wrap stuffbox">
                <div class="inside">
							<span class="bold-and-blocking larger-text">

                                Unit Refresh Time Interval (It refreshes becaues top 200 tags may change. For each tag, it's top 10 score contents/freelancers may also change in wp_display_unit_user_content.
                            </span>
                    <hr/>

                    <form name="" method="post" id="form_refresh_cache_time" action="#">
                        <table class="form-table">
                            <tbody>

                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                    Time in between Unit Refreshes
                                </th>
                                <td class="freelinguist-admin-hp-begining-cron-cell">
                                    <?php
                                    try {
                                        $current_timer_setting = FreelinguistCronTopUnitsTimerRepeat::get_timer_seconds();
                                    } catch (Exception $e) {
                                        $current_timer_setting = 0;
                                    }
                                    //will_dump('Timer setting',$current_timer_setting);
                                    ?>
                                    <select title="Unit Cache Timer" name="units_rebuild_interval_in_seconds">
                                        <option value="0" <?=
                                        ($current_timer_setting === 0) ? 'selected': ''
                                        ?> >Off</option>

                                        <option value="120" <?=
                                        ($current_timer_setting === 120) ? 'selected': ''
                                        ?> >2 Minutes</option>

                                        <option value="600" <?=
                                        ($current_timer_setting === 600) ? 'selected': ''
                                        ?> >10 Minutes</option>

                                        <option value="900" <?=
                                        ($current_timer_setting === 900) ? 'selected': ''
                                        ?> >15 Minutes</option>


                                        <?php
                                        for ($i = 1; $i <= 48; $i++) {
                                            $seconds = 30 * 60 * $i; //half hour increments
                                            $hour_int = $i - 1;
                                            $half_string = '';
                                            if ($i % 2) {
                                                $half_string = ' And a Half ';
                                            }
                                            $word = 'Hours';
                                            if ($hour_int === 1) {$word = 'Hour';}
                                            $full_option_string = "$hour_int $half_string $word ";
                                            ?>
                                            <option value="<?= $seconds ?>"
                                                <?=
                                                ($current_timer_setting === $seconds) ? 'selected': ''
                                                ?>
                                            >
                                                <?= $full_option_string ?>
                                            </option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <div style="height: 4.5em;width: 100%"></div>
                                    <input type="submit" value="Set Generation Interval "
                                           name="yes_i_want_to_update_unit_timer"
                                           class="button button-primary "/><br>
                                </td>
                                <!-- code-notes button and log area for repeat unit generation-->

                                <td class="freelinguist-admin-hp-middle-cron-cell">
                                    <input type="submit" value="Stop Repeat Generation" name="cancel_unitrepeat_cron"
                                           class="button button-primary cron-cancel"/>
                                    <span class="freelinguist-explain-cron-stop">
                                        Pressing this button will stop the repeat unit generation.
                                         After that, you can press resume to continue
                                    </span>

                                    <input type="submit" value="Resume Repeat Generation" name="resume_unitrepeat_cron"
                                           class="button button-primary cron-resume"/>
                                    <span class="freelinguist-explain-cron-resume">
                                        Pressing this button will resume the repeat unit generation
                                    </span>

                                </td>
                                <td class="freelinguist-admin-hp-ending-cron-cell fl-cell-30-percent freelinguist-log-panel">
                                    <div class="freelinguist-log-panel">
                                        <div class="freelinguist-log-control">
                                            <button class="button  freelinguist-show-logs freelinguist-action-popup" type="button">
                                                Show Logs
                                            </button>
                                        </div>
                                        <div class="freelinguist-log-outer-container">
                                            <div class="freelinguist-log-area">
                                                <?php
                                                $user_short_logs = FreelinguistCronTopUnitsTimerRepeat::get_last_n_logs(23);
                                                if (empty($user_short_logs)) {
                                                    $user_short_logs[] = "No logs";
                                                }
                                                foreach ($user_short_logs as $user_short_log) {
                                                    ?>
                                                    <span class="cron-log-line"><?= $user_short_log ?></span>
                                                    <?php
                                                }

                                                ?>
                                            </div> <!-- /.freelingust-log-area -->
                                        </div> <!-- /.freelingust-log-outer-container-->
                                    </div> <!-- /.freelingust-log-panel-->
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div> <!-- Unit Refresh Time Interval -->

            <div class="wrap stuffbox">
                <div class="inside">
                    <span class="bold-and-blocking larger-text">Clears the Display Unit Twig Templates from DB and Elastic Index</span>
                    <hr/>
                    <form name="" method="post" id="form_clear_cache" action="#">
                        <table class="form-table">
                            <tbody>
                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                    Clear Unit Templates
                                </th>
                                <td>
                                    <input title="Yes, Clear Units" type="checkbox" name="yes_i_want_to_clear_units"
                                           id="yes_i_want_to_clear_units" class="" value="1"><br>
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                </th>
                                <td class="freelinguist-admin-hp-begining-cron-cell">
                                    <input type="submit" value="Clear Units" name="clear_unit_templates"
                                           class="button button-primary"/><br>
                                </td>
                                <!--code-notes put in button and log area for clearing unit templates-->

                                <td class="freelinguist-admin-hp-middle-cron-cell">
                                    <input type="submit" value="Cancel Clearing Units" name="cancel_unitclear_cron"
                                           class="button button-primary cron-cancel"/>
                                    <span class="freelinguist-explain-cron-stop">
                                        Pressing this button will stop the unit building soon.
                                         After that, you can press resume to continue or start over again
                                    </span>

                                    <input type="submit" value="Resume Clearing Units" name="resume_unitclear_cron"
                                           class="button button-primary cron-resume"/>
                                    <span class="freelinguist-explain-cron-resume">
                                        Pressing this button will resume the unit clear cron from where it left off.
                                    </span>

                                </td>
                                <td class="freelinguist-admin-hp-ending-cron-cell freelinguist-log-panel">
                                    <div class="freelinguist-log-panel">
                                        <div class="freelinguist-log-control">
                                            <button class="button  freelinguist-show-logs freelinguist-action-popup" type="button">
                                                Show Logs
                                            </button>
                                        </div>
                                        <div class="freelinguist-log-outer-container">
                                            <div class="freelinguist-log-area">
                                                <?php
                                                $user_short_logs = FreelinguistCronTopUnitsClear::get_last_n_logs(23);
                                                if (empty($user_short_logs)) {
                                                    $user_short_logs[] = "No logs";
                                                }
                                                foreach ($user_short_logs as $user_short_log) {
                                                    ?>
                                                    <span class="cron-log-line"><?= $user_short_log ?></span>
                                                    <?php
                                                }

                                                ?>
                                            </div> <!-- /.freelingust-log-area -->
                                        </div> <!-- /.freelingust-log-outer-container-->
                                    </div> <!-- /.freelingust-log-panel-->
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div> <!-- Clears the Unit Twig Templates -->

            <div class="wrap stuffbox">
                <div class="inside">
                    <span class="bold-and-blocking larger-text">
          Recalcultes top 200 tags based on usage count. Then, for each tag, clears and rebuilds the 10+10=20 Display Unit Twig Templates,  (fast run)</span>
                    <hr/>
                    <form name="" method="post" id="form_clear_cache" action="#">
                        <table class="form-table">
                            <tbody>
                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                    Clear and Rebuild Display HTML Units (Click the Rebuild Top-Score Users/Contents button below first before creating HTML units)
                                </th>
                                <td>
                                    <input title="Rebuild Units" type="checkbox" name="yes_i_want_to_rebuild_units"
                                           id="yes_i_want_to_rebuild_units" class="" value="1"><br>
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                </th>
                                <td class="freelinguist-admin-hp-begining-cron-cell">
                                    <input type="submit" value="Rebuild Units" name="rebuild_unit_templates"
                                           class="button button-primary"/><br>
                                </td>
                                <!-- code-notes put in button and log area for generating units-->

                                <td class="freelinguist-admin-hp-middle-cron-cell">
                                    <input type="submit" value="Cancel Generating Units" name="cancel_unit_cron"
                                           class="button button-primary cron-cancel"/>
                                    <span class="freelinguist-explain-cron-stop">
                                        Pressing this button will stop the unit building soon.
                                         After that, you can press resume to continue or start over again
                                    </span>

                                    <input type="submit" value="Resume Building Units" name="resume_unit_cron"
                                           class="button button-primary cron-resume"/>
                                    <span class="freelinguist-explain-cron-resume">
                                        Pressing this button will resume the unit cron from where it left off.
                                    </span>

                                </td>
                                <td class="freelinguist-admin-hp-ending-cron-cell freelinguist-log-panel">
                                    <div class="freelinguist-log-panel">
                                        <div class="freelinguist-log-control">
                                            <button class="button  freelinguist-show-logs freelinguist-action-popup" type="button">
                                                Show Logs
                                            </button>
                                        </div>
                                        <div class="freelinguist-log-outer-container">
                                            <div class="freelinguist-log-area">
                                                <?php
                                                $user_short_logs = FreelinguistCronTopUnitsGenerate::get_last_n_logs(23,false);
                                                if (empty($user_short_logs)) {
                                                    $user_short_logs[] = "No logs";
                                                }
                                                foreach ($user_short_logs as $user_short_log) {
                                                    ?>
                                                    <span class="cron-log-line"><?= $user_short_log ?></span>
                                                    <?php
                                                }

                                                ?>
                                            </div> <!-- /.freelingust-log-area -->
                                        </div> <!-- /.freelingust-log-outer-container-->
                                    </div> <!-- /.freelingust-log-panel-->
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div> <!-- Rebuild the Unit Twig Templates -->

            <div class="wrap stuffbox">
                <div class="inside">
                    <span class="bold-and-blocking larger-text">wp_display_unit_user_content:
For each tag, store top 10 score users/contents. Clears this table tags table and rebuild it. This serves as the basis of the other section to build HTML display unit. </span>
                    <hr/>
                    <form name="" method="post" id="form_rebuild_top_tags" action="#">
                        <table class="form-table">
                            <tbody>
                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                    Rebuild Top-Score Users/Contents for All Tags
                                </th>
                                <td>
                                    <input
                                            title="Yes, Rebuild the Top Tags" type="checkbox"
                                            name="yes_i_want_to_rebuild_top_tags"
                                            id="yes_i_want_to_rebuild_top_tags"
                                            class=""
                                            value="1"
                                    >
                                    <br>
                                </td>
                            </tr>
                            <!-- code-notes NEW GUI for the rebuild of the top tags-->
                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                </th>
                                <td class="freelinguist-admin-hp-begining-cron-cell">
                                    <input type="submit" value="Rebuild Top Tags" name="rebuild_top_tags"
                                           class="button button-primary"/><br>
                                </td>
                                <!-- code-notes put in button and log area for rebuilding top tags table-->

                                <td class="freelinguist-admin-hp-middle-cron-cell">
                                    <input type="submit" value="Cancel Rebuilding Top Tags" name="cancel_rebuild_top_tags_cron"
                                           class="button button-primary cron-cancel"/>
                                    <span class="freelinguist-explain-cron-stop">
                                        Pressing this button will stop the top tag table soon soon.
                                         After that, you can press resume to continue or start over again
                                    </span>

                                    <input type="submit"
                                            value="Resume Rebuilding Top Units"
                                            name="resume_rebuild_top_tags_cron"
                                            class="button button-primary cron-resume"
                                    >
                                    <span class="freelinguist-explain-cron-resume">
                                        Pressing this button will resume the rebuilding top tags from where it left off.
                                    </span>

                                </td>
                                <td class="freelinguist-admin-hp-ending-cron-cell freelinguist-log-panel">
                                    <div class="freelinguist-log-panel">
                                        <div class="freelinguist-log-control">
                                            <button class="button  freelinguist-show-logs freelinguist-action-popup" type="button">
                                                Show Logs
                                            </button>
                                        </div>
                                        <div class="freelinguist-log-outer-container">
                                            <div class="freelinguist-log-area">
                                                <?php
                                                $user_short_logs = FreelinguistCronTopUnitsRebuildAll::get_last_n_logs(23);
                                                if (empty($user_short_logs)) {
                                                    $user_short_logs[] = "No logs";
                                                }
                                                foreach ($user_short_logs as $user_short_log) {
                                                    ?>
                                                    <span class="cron-log-line"><?= $user_short_log ?></span>
                                                    <?php
                                                }

                                                ?>
                                            </div> <!-- /.freelingust-log-area -->
                                        </div> <!-- /.freelingust-log-outer-container-->
                                    </div> <!-- /.freelingust-log-panel-->
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div> <!-- Rebuilds the top tags table -->

            <div class="wrap stuffbox">
                <div class="inside">
                    <span class="bold-and-blocking larger-text">Remove All Index of ElastiSearch</span>
                    <hr/>
                    <form name="" method="post" id="form_remove_indexes" action="#">
                        <table class="form-table">
                            <tbody>
                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                    Remove All Index
                                </th>
                                <td>
                                    <input title="Clear All Indexes" type="checkbox" name="clearallindexs"
                                           id="clearallindexs" class="" value="1"><br>
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                </th>
                                <td>
                                    <input type="submit" value="Remove Index" name="clear_allindexes"
                                           class="button button-primary"/><br>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div> <!-- Remove All Index of ElastiSearch -->



            <div class="wrap stuffbox">

                <div class="inside">
                    <span class="bold-and-blocking larger-text">Elastic Engine Reindex</span>
                    <hr>

                    <?php

                    if (isset($_POST['cancel_project_cron'])) {
                        FreelinguistCronESProjects::stop();
                        $all_logs = FreelinguistCronESProjects::get_log();
                        $log_size = count($all_logs);
                        $log_line = '';
                        if ($log_size) {
                            $log_line = $all_logs[$log_size-1];
                        }
                        ?>

                        <div class="notice notice-success is-dismissible">
                            <p>
                                Cron Job to Re-Index projects will not run until started again
                                <span class="cron-log-line"><?=$log_line?></span>
                            </p>
                        </div>
                        <?php
                    }

                    if (isset($_POST['cancel_contest_cron'])) {
                        FreelinguistCronESContests::stop();
                        $all_logs = FreelinguistCronESContests::get_log();
                        $log_size = count($all_logs);
                        $log_line = '';
                        if ($log_size) {
                            $log_line = $all_logs[$log_size-1];
                        }
                        ?>

                        <div class="notice notice-success is-dismissible">
                            <p>
                                Cron Job to Re-Index contests will not run until started again
                                <span class="cron-log-line"><?=$log_line?></span>
                            </p>
                        </div>
                        <?php
                    }



                    if (isset($_POST['cancel_user_cron'])) {
                        FreelinguistCronESUsers::stop();
                        $all_logs = FreelinguistCronESUsers::get_log();
                        $log_size = count($all_logs);
                        $log_line = '';
                        if ($log_size) {
                            $log_line = $all_logs[$log_size-1];
                        }
                        ?>

                        <div class="notice notice-success is-dismissible">
                            <p>
                                Cron Job to Re-Index users will not run until started again
                                <span class="cron-log-line"><?=$log_line?></span>
                            </p>
                        </div>
                        <?php
                    }

                    if (isset($_POST['cancel_content_cron'])) {
                        FreelinguistCronESContent::stop();
                        $all_logs = FreelinguistCronESContent::get_log();
                        $log_size = count($all_logs);
                        $log_line = '';
                        if ($log_size) {
                            $log_line = $all_logs[$log_size-1];
                        }
                        ?>

                        <div class="notice notice-success is-dismissible">
                            <p>
                                Cron Job to Re-Index Content will not run until started again
                                <span class="cron-log-line"><?=$log_line?></span>
                            </p>
                        </div>
                        <?php
                    }



                    if (isset($_POST['resume_project_cron'])) {
                        FreelinguistCronESProjects::resume();
                        $all_logs = FreelinguistCronESProjects::get_log();
                        $log_size = count($all_logs);
                        $log_line = '';
                        if ($log_size) {
                            $log_line = $all_logs[$log_size-1];
                        }

                        ?>

                        <div class="notice notice-success is-dismissible">
                            <p>
                                Cron Job to Re-Index projects will now resume
                                <span class="cron-log-line"><?=$log_line?></span>
                            </p>
                        </div>
                        <?php
                    }

                    if (isset($_POST['resume_contest_cron'])) {
                        FreelinguistCronESContests::resume();
                        $all_logs = FreelinguistCronESContests::get_log();
                        $log_size = count($all_logs);
                        $log_line = '';
                        if ($log_size) {
                            $log_line = $all_logs[$log_size-1];
                        }
                        ?>

                        <div class="notice notice-success is-dismissible">
                            <p>
                                Cron Job to Re-Index contests will now resume
                                <span class="cron-log-line"><?=$log_line?></span>
                            </p>
                        </div>
                        <?php
                    }

                    if (isset($_POST['resume_user_cron'])) {
                        FreelinguistCronESUsers::resume();
                        $all_logs = FreelinguistCronESUsers::get_log();
                        $log_size = count($all_logs);
                        $log_line = '';
                        if ($log_size) {
                            $log_line = $all_logs[$log_size-1];
                        }
                        ?>

                        <div class="notice notice-success is-dismissible">
                            <p>
                                Cron Job to Re-Index users will now resume
                                <span class="cron-log-line"><?=$log_line?></span>
                            </p>
                        </div>
                        <?php
                    }

                    if (isset($_POST['resume_content_cron'])) {
                        FreelinguistCronESContent::resume();
                        $all_logs = FreelinguistCronESContent::get_log();
                        $log_size = count($all_logs);
                        $log_line = '';
                        if ($log_size) {
                            $log_line = $all_logs[$log_size-1];
                        }
                        ?>

                        <div class="notice notice-success is-dismissible">
                            <p>
                                Cron Job to Re-Index Content will now resume
                                <span class="cron-log-line"><?=$log_line?></span>
                            </p>
                        </div>
                        <?php
                    }

                    ///above


                    if (isset($_POST['submit_setting'])) {

                        if (isset($_POST['reprofile']) || isset($_POST['recontest']) || isset($_POST['recontent']) || isset($_POST['reproject'])) {


                            if (isset($_POST['reproject']) && $_POST['reproject'] == 1) {

                                try {
                                    FreelinguistCronESProjects::run();
                                    $log_line = FreelinguistCronESProjects::get_log()[0];
                                    ?>

                                    <div class="notice notice-success is-dismissible">
                                        <p>
                                            Scheduled Cron Job to Re-Index Projects. It will start soon:
                                            <span class="cron-log-line"><?=$log_line?></span>
                                        </p>
                                    </div>
                                    <?php
                                } catch (Exception $e) {
                                    ?>

                                    <div class="notice notice-success is-dismissible">
                                        <p>
                                            Error Starting Cron Job to Re-Index Projects :
                                            <span class="cron-log-error"><?=$e->getMessage()?></span>
                                        </p>
                                    </div>
                                    <?php
                                }


                            }

                            if (isset($_POST['recontest']) && $_POST['recontest'] == 1) {

                                try {
                                    FreelinguistCronESContests::run();
                                    $log_line = FreelinguistCronESContests::get_log()[0];
                                    ?>

                                    <div class="notice notice-success is-dismissible">
                                        <p>
                                            Scheduled Cron Job to Re-Index Contests. It will start soon:
                                            <span class="cron-log-line"><?=$log_line?></span>
                                        </p>
                                    </div>
                                    <?php
                                } catch (Exception $e) {
                                    ?>

                                    <div class="notice notice-success is-dismissible">
                                        <p>
                                            Error Starting Cron Job to Re-Index Contest :
                                            <span class="cron-log-error"><?=$e->getMessage()?></span>
                                        </p>
                                    </div>
                                    <?php
                                }


                            }

                            if (isset($_POST['recontent']) && $_POST['recontent'] == 1) {

                                try {
                                    FreelinguistCronESContent::run();
                                    $log_line = FreelinguistCronESContent::get_log()[0];
                                    ?>

                                    <div class="notice notice-success is-dismissible">
                                        <p>
                                            Scheduled Cron Job to Re-Index Content. It will start soon:
                                            <span class="cron-log-line"><?=$log_line?></span>
                                        </p>
                                    </div>
                                    <?php
                                } catch (Exception $e) {
                                    ?>

                                    <div class="notice notice-success is-dismissible">
                                        <p>
                                            Error Starting Cron Job to Re-Index Content :
                                            <span class="cron-log-error"><?=$e->getMessage()?></span>
                                        </p>
                                    </div>
                                    <?php
                                }
                            }

                            if (isset($_POST['reprofile']) && $_POST['reprofile'] == 1) {

                                try {
                                    FreelinguistCronESUsers::run();
                                    $log_line = FreelinguistCronESUsers::get_log()[0];
                                    ?>

                                    <div class="notice notice-success is-dismissible">
                                        <p>
                                            Scheduled Cron Job to Re-Index Users. It will start soon:
                                            <span class="cron-log-line"><?=$log_line?></span>
                                        </p>
                                    </div>
                                    <?php
                                } catch (Exception $e) {
                                    ?>

                                    <div class="notice notice-success is-dismissible">
                                        <p>
                                            Error Starting Cron Job to Re-Index Users :
                                            <span class="cron-log-error"><?=$e->getMessage()?></span>
                                        </p>
                                    </div>
                                    <?php
                                }
                            }

                        } else {

                            echo '<div class="notice notice-error is-dismissible">
									<p>Selection for index is required!!</p>
								</div>';

                        }
                    }
                    ?>

                    <form name="" method="post"  action="#">
                        <table class="form-table">
                            <tbody>
                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                    Create Index For Project
                                </th>
                                <td class="freelinguist-admin-hp-begining-cron-cell">
                                    <input title="Create Project Index" type="checkbox" name="reproject" id="title"
                                           class="" value="1"><br>
                                </td>
                                <td class="freelinguist-admin-hp-middle-cron-cell">
                                    <input type="submit" value="Cancel Project Cron" name="cancel_project_cron"
                                           class="button button-primary cron-cancel"/>
                                    <span class="freelinguist-explain-cron-stop">
                                        Pressing this button will stop the job soon.
                                        After that, you can press resume to continue or start over again
                                    </span>

                                    <input type="submit" value="Resume Project Cron" name="resume_project_cron"
                                           class="button button-primary cron-resume"/>
                                    <span class="freelinguist-explain-cron-resume">
                                        Pressing this button will resume the project cron from where it left off.
                                    </span>

                                </td>
                                <td class="freelinguist-log-panel">
                                    <div class="freelinguist-log-panel">
                                        <div class="freelinguist-log-control">
                                            <button class="button  freelinguist-show-logs freelinguist-action-popup" type="button">
                                                Show Logs
                                            </button>
                                        </div>
                                        <div class="freelinguist-log-outer-container">
                                            <div class="freelinguist-log-area">
                                                <?php
                                                $project_short_logs = FreelinguistCronESProjects::get_last_n_logs(23);

                                                if (empty($project_short_logs)) {
                                                    $project_short_logs[] = "No logs";
                                                }
                                                foreach ($project_short_logs as $project_short_log) {
                                                    ?>
                                                        <span class="cron-log-line"><?= $project_short_log ?></span>
                                                    <?php
                                                }

                                                ?>
                                            </div> <!-- /.freelingust-log-area -->
                                        </div> <!-- /.freelingust-log-outer-container-->
                                    </div> <!-- /.freelingust-log-panel-->
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                    Create Index For Contest
                                </th>
                                <td class="freelinguist-admin-hp-begining-cron-cell">
                                    <input title="Create Contest Index" type="checkbox" name="recontest"
                                           id="contesttitle" class="" value="1"><br>
                                </td>
                                <td class="freelinguist-admin-hp-middle-cron-cell">
                                    <input type="submit" value="Cancel Contest Cron" name="cancel_contest_cron"
                                           class="button button-primary cron-cancel"/>
                                    <span class="freelinguist-explain-cron-stop">
                                        Pressing this button will stop the job soon.
                                         After that, you can press resume to continue or start over again
                                    </span>

                                    <input type="submit" value="Resume Contest Cron" name="resume_contest_cron"
                                           class="button button-primary cron-resume"/>
                                    <span class="freelinguist-explain-cron-resume">
                                        Pressing this button will resume the contest cron from where it left off.
                                    </span>

                                </td>
                                <td class="freelinguist-log-panel">
                                    <div class="freelinguist-log-panel">
                                        <div class="freelinguist-log-control">
                                            <button class="button  freelinguist-show-logs freelinguist-action-popup" type="button">
                                                Show Logs
                                            </button>
                                        </div>
                                        <div class="freelinguist-log-outer-container">
                                            <div class="freelinguist-log-area">
                                                <?php
                                                $contest_short_logs = FreelinguistCronESContests::get_last_n_logs(23);
                                                if (empty($contest_short_logs)) {
                                                    $contest_short_logs[] = "No logs";
                                                }
                                                foreach ($contest_short_logs as $contest_short_log) {
                                                    ?>
                                                    <span class="cron-log-line"><?= $contest_short_log ?></span>
                                                    <?php
                                                }

                                                ?>
                                            </div> <!-- /.freelingust-log-area -->
                                        </div> <!-- /.freelingust-log-outer-container-->
                                    </div> <!-- /.freelingust-log-panel-->
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                    Create Index For Content
                                </th>
                                <td class="freelinguist-admin-hp-begining-cron-cell">
                                    <input title="Create Content Index" type="checkbox" name="recontent"
                                           id="contenttitle" class="" value="1"><br>
                                </td>
                                <td class="freelinguist-admin-hp-middle-cron-cell">
                                    <input type="submit" value="Cancel Content Cron" name="cancel_content_cron"
                                           class="button button-primary cron-cancel"/>
                                    <span class="freelinguist-explain-cron-stop">
                                        Pressing this button will stop the job soon.
                                         After that, you can press resume to continue or start over again
                                    </span>

                                    <input type="submit" value="Resume Content Cron" name="resume_content_cron"
                                           class="button button-primary cron-resume"/>
                                    <span class="freelinguist-explain-cron-resume">
                                        Pressing this button will resume the content cron from where it left off.
                                    </span>

                                </td>
                                <td class="freelinguist-log-panel">
                                    <div class="freelinguist-log-panel">
                                        <div class="freelinguist-log-control">
                                            <button class="button  freelinguist-show-logs freelinguist-action-popup" type="button">
                                                Show Logs
                                            </button>
                                        </div>
                                        <div class="freelinguist-log-outer-container">
                                            <div class="freelinguist-log-area">
                                                <?php
                                                $content_short_logs = FreelinguistCronESContent::get_last_n_logs(23);
                                                if (empty($content_short_logs)) {
                                                    $content_short_logs[] = "No logs";
                                                }
                                                foreach ($content_short_logs as $one_content_log) {
                                                    ?>
                                                    <span class="cron-log-line"><?= $one_content_log ?></span>
                                                    <?php
                                                }

                                                ?>
                                            </div> <!-- /.freelingust-log-area -->
                                        </div> <!-- /.freelingust-log-outer-container-->
                                    </div> <!-- /.freelingust-log-panel-->
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                    Create Index For Profile
                                </th>
                                <td class="freelinguist-admin-hp-begining-cron-cell">
                                    <input title="Create Profile Index" type="checkbox" name="reprofile"
                                           id="profiletitle" class="" value="1"><br>
                                </td>
                                <td class="freelinguist-admin-hp-middle-cron-cell">
                                    <input type="submit" value="Cancel User Cron" name="cancel_user_cron"
                                           class="button button-primary cron-cancel"/>
                                    <span class="freelinguist-explain-cron-stop">
                                        Pressing this button will stop the job soon.
                                         After that, you can press resume to continue or start over again
                                    </span>

                                    <input type="submit" value="Resume User Cron" name="resume_user_cron"
                                           class="button button-primary cron-resume"/>
                                    <span class="freelinguist-explain-cron-resume">
                                        Pressing this button will resume the user cron from where it left off.
                                    </span>

                                </td>
                                <td class="freelinguist-log-panel">
                                    <div class="freelinguist-log-panel">
                                        <div class="freelinguist-log-control">
                                            <button class="button  freelinguist-show-logs freelinguist-action-popup" type="button">
                                                Show Logs
                                            </button>
                                        </div>
                                        <div class="freelinguist-log-outer-container">
                                            <div class="freelinguist-log-area">
                                                <?php
                                                $user_short_logs = FreelinguistCronESUsers::get_last_n_logs(23);
                                                if (empty($user_short_logs)) {
                                                    $user_short_logs[] = "No logs";
                                                }
                                                foreach ($user_short_logs as $user_short_log) {
                                                    ?>
                                                    <span class="cron-log-line"><?= $user_short_log ?></span>
                                                    <?php
                                                }

                                                ?>
                                            </div> <!-- /.freelingust-log-area -->
                                        </div> <!-- /.freelingust-log-outer-container-->
                                    </div> <!-- /.freelingust-log-panel-->
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <td></td>
                                <th scope="row">
                                    <input type="submit" value="Submit" name="submit_setting"
                                           class="button button-primary freelinguist-fix-submit-width"/>
                                </th>
                                <td>

                                </td>
                                <td>

                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div> <!-- clear and rebuild the 4 ES index using the cron jobs -->


            <!-- code-notes [red dot actions]  add in control for the new cron job for red dots here (might be moved to a new page later)-->

            <div class="wrap stuffbox">
                <a name="red-dots"> </a>
                <div class="inside">
							<span class="bold-and-blocking larger-text">
                                Red Dot Actions done in the background using cron jobs
                            </span>
                    <hr/>

                    <form name="" method="post" id="form_refresh_cache_time" action="#">
                        <table class="form-table">
                            <tbody>

                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                    Time in between Resolving Expired Red Dot Future Actions
                                </th>
                                <td class="freelinguist-admin-hp-begining-cron-cell">
                                    <?php
                                    try {
                                        $current_timer_setting = FreelinguistCronRedDotActions::get_timer_seconds();
                                    } catch (Exception $e) {
                                        $current_timer_setting = 0;
                                    }
                                    //will_dump('Timer setting',$current_timer_setting);
                                    ?>
                                    <select title="Red Dot Actions Timer" name="red_dots_interval_in_seconds">
                                        <option value="0" <?=
                                        ($current_timer_setting === 0) ? 'selected': ''
                                        ?> >Off</option>

                                        <option value="120" <?=
                                        ($current_timer_setting === 120) ? 'selected': ''
                                        ?> >2 Minutes</option>

                                        <option value="600" <?=
                                        ($current_timer_setting === 600) ? 'selected': ''
                                        ?> >10 Minutes</option>

                                        <option value="900" <?=
                                        ($current_timer_setting === 900) ? 'selected': ''
                                        ?> >15 Minutes</option>


                                        <?php
                                        for ($i = 1; $i <= 48; $i++) {
                                            $seconds = 30 * 60 * $i; //half hour increments
                                            $hour_int = $i - 1;
                                            $half_string = '';
                                            if ($i % 2) {
                                                $half_string = ' And a Half ';
                                            }
                                            $word = 'Hours';
                                            if ($hour_int === 1) {$word = 'Hour';}
                                            $full_option_string = "$hour_int $half_string $word ";
                                            ?>
                                            <option value="<?= $seconds ?>"
                                                <?=
                                                ($current_timer_setting === $seconds) ? 'selected': ''
                                                ?>
                                            >
                                                <?= $full_option_string ?>
                                            </option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <div style="height: 4.5em;width: 100%"></div>
                                    <input type="submit" value="Set repeating interval when red dot actions are run"
                                           name="yes_i_want_to_update_red_dots_timer"
                                           class="button button-primary "/><br>
                                </td>
                                <!-- code-notes button and log area for repeat red dots actions-->

                                <td class="freelinguist-admin-hp-middle-cron-cell">
                                    <input type="submit" value="Stop Red Dot Actions" name="cancel_red_dots_cron"
                                           class="button button-primary cron-cancel"/>
                                    <span class="freelinguist-explain-cron-stop">
                                        Pressing this button will stop the red dot actions from being processed.
                                         After that, you can press resume to continue
                                    </span>

                                    <input type="submit" value="Resume Red Dot Actions" name="resume_red_dots_cron"
                                           class="button button-primary cron-resume"/>
                                    <span class="freelinguist-explain-cron-resume">
                                        Pressing this button will resume the red dot actions
                                    </span>

                                </td>
                                <td class="freelinguist-admin-hp-ending-cron-cell fl-cell-30-percent freelinguist-log-panel">
                                    <div class="freelinguist-log-panel">
                                        <div class="freelinguist-log-control">
                                            <button class="button  freelinguist-show-logs freelinguist-action-popup" type="button" data-hash="red-dots">
                                                Show Logs
                                            </button>
                                        </div>
                                        <div class="freelinguist-log-outer-container">
                                            <div class="freelinguist-log-area">
                                                <?php
                                                $user_short_logs = FreelinguistCronRedDotActions::get_last_n_logs(100);
                                                if (empty($user_short_logs)) {
                                                    $user_short_logs[] = "No logs";
                                                }
                                                foreach ($user_short_logs as $user_short_log) {
                                                    ?>
                                                    <span class="cron-log-line"><?= $user_short_log ?></span>
                                                    <?php
                                                }

                                                ?>
                                            </div> <!-- /.freelingust-log-area -->
                                        </div> <!-- /.freelingust-log-outer-container-->
                                    </div> <!-- /.freelingust-log-panel-->
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div> <!-- Red Dot Actions Time Interval -->

            <!-- code-notes [red dot actions]  end new cron area-->

            <!--code-notes Added new options here-->

            <div class="wrap stuffbox">

                <div class="inside">
                    <span class="bold-and-blocking larger-text">Top Tags</span>
                    <hr>
                    <form name="" method="post" id="form_top_tag_limit" action="#">
                        <table class="form-table">
                            <tbody>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">Number of Top Things in Each Tag</th>
                                <td>
                                    <input title="Number of Top Things in Each Tag" type="text"
                                           name="freelinguist-limit-top-per-tag" id="freelinguist-limit-top-per-tag"
                                           value="<?= get_option('freelinguist-limit-top-per-tag', 10); ?>">
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">Number of Top Tags</th>
                                <td>
                                    <input title="Number of Top Tags" type="text" name="freelinguist-limit-top-tags"
                                           id="freelinguist-limit-top-tags"
                                           value="<?php echo get_option('freelinguist-limit-top-tags', 200); ?>">
                                </td>
                            </tr>


                            <tr class="user-rich-editing-wrap-">
                                <th scope="row">
                                </th>
                                <td>
                                    <input type="submit" value="Update Top Tag Limits" name="top_tag_limits"
                                           class="button  button-primary"/>
                                </td>
                            </tr>
                            <!--  -->

                            </tbody>
                        </table>
                    </form>
                </div> <!-- /.inside -->
            </div>  <!-- /.wrap.stuffbox Number of Top Things in Each Tag -->


            <!-- code-notes end new option section -->

            <div class="wrap stuffbox">
                <div class="inside">
                    <span class="bold-and-blocking larger-text">Homepage Interest Tags (Add Tags to be displayed on homepages. By default: For each tag, it'll read related content/freelancers from cache table and display on homepage. Or, manually enter IDs below for the tag after clicking the tag)</span>
                    <hr>
                    <form name="send_Homepage_Interest_f" method="post" id="send_Homepage_Interest_f"
                          action="<?php echo admin_url() . 'admin.php?page='.static::PAGE_STUB.'&send_Homepage_Interest=true'; ?>&lang=en">
                        <table class="form-table">
                            <tbody>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">
                                    Tag Title
                                </th>
                                <td>
                                    <input type="hidden" name="tag_id" id="" class=""><br>
                                    <input title="Search Tags" type="text" name="title" class="tag-search" autocomplete="off"><br>
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">
                                    Priority number that detminers the order of the tag displayed on homepage
                                </th>
                                <td>
                                    <select title="Priority Number" name="priority_number" id="priority_number">
                                        <?php
                                        for ($il = 1; $il <= 100; $il++) {
                                            echo '<option value="' . $il . '">' . $il . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                            <tr class="user-rich-editing-wrap">
                                <th scope="row">
                                    <input type="submit" value="Add Tag" name="send_interest_btn" id="send_interest_btn"
                                           class="button button-primary">
                                </th>
                                <td></td>
                            </tr>

                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
            <?php
            $Homepage_InterestListTable = new Homepage_Interest_List_Table();
            $Homepage_InterestListTable->prepare_items();
            ?>
            <div class="fl-homepage-tag-table" style="padding: 0;margin: 0"><!-- code-notes need to wrap table with div to be able to use it js by itself later -->
            <?php $Homepage_InterestListTable->display(); ?>
            </div><!-- /.fl-homepage-tag-table-->
            <div class="per-id-homepage">
                <span class="bold large-text per-id-parent-name"></span>
                <br>
                <div class="per-id-parent-name-holder" style="display: block;width: 100%">
                    <label>
                        <input type="checkbox" name="interest-check-is-title-hidden" id="interest-check-is-title-hidden"
                               class="" value="1">
                        Hide Tag Title in the Homepage for this tag?
                    </label>


                </div>

                <br>
                <div class="per-id-input-holder" >
                    <select title="Select Per ID Type" class="per-id-select-type">
                        <option value="content">Content</option>
                        <option value="freelancer_profile">Freelancer Profile</option>
                    </select>
                    <input type="submit" value="Add" name="per-id-new-btn" id="per-id-new-btn"
                           class="button button-primary">

                    <br>

                    <!--                        <input class="per-id-input-new">-->
                    <textarea type="text" name="per-id-textarea-new" rows="5" cols="60" class="per-id-textarea-new" autocomplete="off"
                              title="Add new users or content id here"></textarea>
                    <br>
                    <input type="hidden" name="per-id-hidden-remember-me" id="per-id-hidden-remember-me" value="">


                </div>

                <div class="per-id-list-holder">
                    <table class="wp-list-table fl-homepage-tag-table widefat fixed stripe">
                        <thead>
                        <tr>
                            <th>
                                ID
                            </th>
                            <th>
                                Type
                            </th>
                            <th>
                                Name
                            </th>
                            <th>
                                Delete
                            </th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                    <!--                        table lists the id, the name of the profile or content, and a delete icon-->
                </div>
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
class Homepage_Interest_List_Table extends WP_List_Table
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
            'id' => 'ID',
            'title' => 'Tag Name',
            'priority_number' => 'Priority Number',
            'is_top_tag'       => 'Top Tag (non top tag is not dispalyed unles it has manual selections)',
            'has_per_ids'       => 'Manual selection of content/profile (always displayed, even not a top tag). If it is also a top tag, auto and manual units will be merged together to display',
            'is_title_hidden'       => 'Tag Name itself is hidden. The units for it will still be displayed',
            'action' => /** @lang text */
            'Delete From Homepage View'
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
        return [
                'title' => array('title', true),
                'id' => array('id', true),
                'is_top_tag' => array('is_top_tag', true),
                'has_per_ids' => array('has_per_ids', true),
                'is_title_hidden' => array('is_title_hidden', true),
                'priority_number' => array('priority_number', true),
                'translation_type' => array('translation_type', true)
        ];
    }

    /**
     * Get the table data
     *
     * @return array
     */
    private function table_data()
    {
        global $wpdb;
        $data = $wpdb->get_results(
                "
                    SELECT
                      whi.title as title,whi.id,whi.priority_number,is_title_hidden,
                      IF(top_tags.da_count,1,0) as is_top_tag,
                      IF(what_pers.da_count,1,0)  as has_per_ids
                      
                    FROM wp_homepage_interest whi
                    
                    LEFT JOIN (
                              SELECT i.tag_name,c.tag_id,count(c.id) as da_count
                              from wp_display_unit_user_content c
                                INNER JOIN wp_interest_tags i ON i.ID = c.tag_id
                              WHERE c.is_top_tag = 1
                              GROUP BY c.tag_id
                    ) as top_tags ON top_tags.tag_id = whi.tag_id
                    
                    LEFT JOIN (
                    SELECT count(*)  as da_count ,
                    homepage_interest_id
                    from wp_homepage_interest_per_id
                    group by homepage_interest_id
                    ) as what_pers ON what_pers.homepage_interest_id = whi.id
                    
                    WHERE 1
                    ORDER BY whi.id desc;
                          ",
                ARRAY_A);
        $result = array();
        foreach ($data as $key => $value) {
            $u_url_del = admin_url() . 'admin.php?page=freelinguist-admin-homepage-interest&delete_interest=' . $value['id'];
            $value['action'] = '<a class="close dashicons dashicons-no" href="' . $u_url_del . '"></a>';

            $n_is_top_tag = (int)$value['is_top_tag'];
            if ($n_is_top_tag) {
                $value['is_top_tag'] = '<i class="fa fa-check freelinguist-check-success"></i>';
            } else {
                $value['is_top_tag'] = '';
            }


            $n_has_per_ids= (int)$value['has_per_ids'];
            if ($n_has_per_ids) {
                $value['has_per_ids'] = '<i class="fa fa-check freelinguist-check-success"></i>';
            } else {
                $value['has_per_ids'] = '';
            }

            $n_is_title_hidden = (int)$value['is_title_hidden'];
            if ($n_is_title_hidden) {
                $value['is_title_hidden'] = '<i class="fa fa-check freelinguist-check-success"></i>';
            } else {
                $value['is_title_hidden'] = '';
            }



            $result[] = $value;


        }
        return $result;
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
            case 'priority_number':
            case 'is_top_tag':
            case 'has_per_ids':
            case 'is_title_hidden':
            case 'action':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    /**
     * @param $a
     * @param $b
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return int
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