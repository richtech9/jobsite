<?php
require_once( ABSPATH . '/wp-content/themes/the-translator/vendor/autoload.php');

/*
    * current-php-code 2021-Jan-8
    * input-sanitized :
    * current-wp-template:  admin-screen  for debugging units
*/
class AdminPageTestUnits {

    const THIS_ADMIN_STUB_NAME = 'freelinguist-admin-test-units';
    const REFERRER_NAME = 'fl_test_unit_admin';
    const DEBUG_MODE = true;
    const OUTPUT_LOG_TO_JS_CONSOLE = true;

    public const REWRITES = [
        'view-units' => ['part'=> 'view-units','endpoint'=>'view-units']
    ];

    public const ON_INIT = 'on_init';
    public const INIT_THEME = 'init_theme';
    public const END_THEME = 'end_theme';

    public const  UNITS_PER_ROW = 4;
    public const UNITS_PER_PAGE = 10;

    static protected $languge_verb = null;
    static protected $safety_nonce = null;

    static protected $search_source = '';
    static protected $search_tag = '';
    static protected $search_type = '';
    static protected $search_id = '';

    static protected $spoof_user_id = '';
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
        static::$languge_verb = isset($_REQUEST['lang']) ? 'lang='.$_REQUEST['lang'] . '&' : '';
        add_action('admin_menu', array($this, 'add_test_units_to_menu'));

        add_action('admin_enqueue_scripts',function(){
            wp_enqueue_style('bootstrap-style', get_template_directory_uri() . '/css/lib/bootstrap.css', array(), '1.0', 'all');
        });
    }

    /**
     * Called from the admin_menu hook
     */
    public  function add_test_units_to_menu() {

        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'Test Units',
                'Test Units',
                'manage_options',
                static::THIS_ADMIN_STUB_NAME,
                array($this, 'draw_unit_admin_screen'),
                $this->position);
        } else {
            add_menu_page('Test Units',
                'Test Units',
                'manage_options',
                static::THIS_ADMIN_STUB_NAME,
                array($this, 'draw_unit_admin_screen'),
                'dashicons-editor-kitchensink');
        }

    }

    /**
     * Called from the init,after_switch_theme,switch_theme hooks
     * @param $action
     */
    public static function add_rewrite_endpoints($action) {

        switch($action) {
            case static::ON_INIT: {
                foreach (static::REWRITES as $key => $node) {
                    $thing = $node['endpoint'];
                    add_rewrite_endpoint($thing, EP_ALL);
                }
                break;
            }
            case static::INIT_THEME: {
                foreach (static::REWRITES as $key => $node) {
                    $thing = $node['endpoint'];
                    add_rewrite_endpoint($thing, EP_ALL);
                }
                // flush rewrite rules - only do this on activation as anything more frequent is bad!
                flush_rewrite_rules();
                break;
            }
            case static::END_THEME: {
                // flush rewrite rules when ending to remove custom urls
                flush_rewrite_rules();
                break;
            }
            default: {
                throw new RuntimeException("Does not recognize endpoint command: $action in AdminPanelTestUnits::add_rewrite_endpoints");
            }
        }

    }

    /**
     * Called from the template_redirect hook
     */
    public static function process_custom_endpoints() {

        foreach (static::REWRITES as $key => $node) {
            $thing = '/'.$node['endpoint'];
            $b_found = strpos($_SERVER['REQUEST_URI'],$thing) !== false;
            if ($b_found) {

                //check if logged in and admin, else bug out
                if( !current_user_can( 'administrator' ) ){
                    http_response_code(403);
                    die();
                }
                http_response_code(200);

                switch($key) {
                    case 'view-units': {
                        try {
                            $paged = 1;
                            static::fill_in_search_from_get();
                            if (isset($_GET['page'])) {
                                $paged = (int)$_GET['page'];
                            }
                            if ($paged < 1) {
                                $paged = 1;
                            }
                            $da_total_unit_count = static::get_total_unit_count();
                            $total_pages = (int)ceil($da_total_unit_count / static::UNITS_PER_PAGE);

                            $get_map = [];
                            if (count($_GET)) {
                                foreach ($_GET as $okey => $oval) {
                                    $get_map[$okey] = $oval;
                                }
                            }

                            $get_map['page'] = '%page%';

                            if (!isset($get_map['lang'])) {
                                $get_map['lang'] = 'en';
                            }
                            $nu_query_string = http_build_query($get_map);
                            $nu_query_string = str_replace('%25','%',$nu_query_string);


                            $url_template = get_site_url() . '/view-units/?'.$nu_query_string;
                            get_header();
                            static::draw_form();
                            freelinguist_print_pagination_bar($paged, $total_pages, $url_template, 'bottom');
                            static::draw_units($paged);
                            freelinguist_print_pagination_bar($paged, $total_pages, $url_template, 'bottom');
                            get_footer('homepagenew');
                        } catch (Exception $e) {
                            ?>
                            <div style="margin: 3em"><span class="error" style="color: #ee2b31; font-weight: bold">
                                    There was a problem: <?= $e->getMessage()?>
                                </span>
                            </div>
                            <?php
                        }
                        die();
                    }
                    default: {
                        get_header();
                        print '<main role="main"><section>';
                        print '</section></main>';
                        get_footer();
                        die();

                    }
                }

            }
        }
    }

    protected static function generate_form_goodies($data_key = null,$data_value=null) {
        
        if (empty(static::$safety_nonce)) {
            static::$safety_nonce =
                wp_create_nonce(static::REFERRER_NAME);
        }
        $out = [];
        $nonce = static::$safety_nonce;
        $out[] = "<input type='hidden' name='_ajax_nonce' value='$nonce'>";
        if ($data_key && (!is_null($data_value))) {
            $out[] = "<input type='hidden' name='$data_key' value='$data_value'>";
        } 

        return "\n".implode("\n",$out);
    }

    protected function do_posty_stuff() {
        $error_message = '';
        $log = [];
        try {

            if (!current_user_can( 'manage_options' ) ) {
                throw new RuntimeException("Current user cannot manage options");
            }

            if (is_array($_POST) && count($_POST)) {
                $referrer_ok = check_ajax_referer(static::REFERRER_NAME,false,false);
                if (!$referrer_ok) {
                    throw new RuntimeException("Please Refresh this page to be able to submit data");
                }

            }




            if (isset($_POST['do_the_units'])) {
                FreelinguistUnitGenerator::generate_units($log,[],[],false);
            }
        } catch (RuntimeException $e) {
            $error_message = $e->getMessage() ;
            if ($e->getCode()) {$error_message .= '['. $e->getCode() . ']';}
            will_dump('ERROR', $log,static::DEBUG_MODE);
            will_log_in_wp_log_and_js_console($log,static::OUTPUT_LOG_TO_JS_CONSOLE,true,true);
        }

        if (!empty($log) && empty($error_message)) {
            will_log_in_wp_log_and_js_console($log,static::OUTPUT_LOG_TO_JS_CONSOLE,false,false);
        }

        return $error_message;
    }

    /**
     * Called from the admin page action
     */
    public function draw_unit_admin_screen() {
        $url_is = admin_url().'admin.php?page='.static::THIS_ADMIN_STUB_NAME.'&lang=en';
        $error_message = $this->do_posty_stuff();
        ?>
        <div class="wrap">
            <span class="bold-and-blocking large-text">Test Unit Admin Page</span>
            <hr>
            <?php if ($error_message) { ?>
                <div class="fl-admin-error"><?= $error_message ?></div>
            <?php } //end if not error message?>

            <div class="fl-top-unit-test-panel">
               <form action="<?=$url_is?>" name="regenerate_units" method="post">
                    <input class="" type="submit" name="do_the_units" value="Regenerate Units">
                    <?= static::generate_form_goodies(null,null); ?>
                </form>
                <hr>
            </div> <!-- ./fl-top-unit-test-panel -->


            <div class="fl-bottom-unit-test-panel">
                <!-- this is where the units will be drawn -->
                <div class="row fl-unit-test-display">
                    <div class="col-sm-12 col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">View Units</div>
                            <div class="panel-body">
                                <a href="<?= site_url().'/view-units/?lang=en' ?>">Go to new display tab (units cannot be shown on admin side)</a>
                            </div>
                        </div>
                    </div>

                </div> <!-- ./fl-unit-test-display -->

            </div> <!-- ./fl-bottom-unit-test-panel -->

        </div> <!-- ./wrap -->
        <?php
    }

    protected static function fill_in_search_from_get() {
        if (is_array($_GET) && count($_GET) >2) {
            $referrer_ok = check_ajax_referer(static::REFERRER_NAME,false,false);
            if (!$referrer_ok) {
                throw new RuntimeException("Please Refresh this page to be able to submit data");
            }

            static::$search_id = '';
            if (isset($_GET['pk'])) {
                static::$search_id = (int) $_GET['pk'];
                if (empty(static::$search_id)){static::$search_id = '';}
            }

            static::$search_tag = '';
            if (isset($_GET['tag'])) {
                static::$search_tag =  $_GET['tag'];
            }

            static::$search_source = '';
            if (isset($_GET['source'])) {
                static::$search_source = $_GET['source'];
            }

            static::$search_type = '';
            if (isset($_GET['unit_type'])) {
                static::$search_type =  $_GET['unit_type'];
            }

            static::$spoof_user_id = '';
            if (isset($_GET['spoof_user_id'])) {
                static::$spoof_user_id =  $_GET['spoof_user_id'];
            }

            if (isset($_GET['uber_page'])) {
                $_GET['page'] = (int)$_GET['uber_page'];
            }

//            will_dump("form got",[
//                'type' => static::$search_type,
//                'source' => static::$search_source,
//                'id' => static::$search_id,
//                'tag' => static::$search_tag
//
//
//            ]);
        }
    }

    protected static function draw_form() {
        ?>
        <div class="row" style="margin-top: 2em; margin-left: 2em;margin-right: 2em">
            <form action="" name="search_units" method="get" class="fl-admin-unit-form">

                <div class="col-md-1">
                    <div class="form-group">
                        <label for="search-units">&nbsp;&nbsp;</label>
                        <input type="submit" class="form-control" id="search-units" name="search-units" value="Search" >
                        <?= static::generate_form_goodies(NULL,NULL)?>
                    </div>
                </div>

                <div class="col-md-2" style="background-color: darkblue">
                    <div class="form-group">
                        <label for="spoof_user_id" style="color: lightblue">Spoof User ID</label>
                        <input type="text" class="form-control" id="spoof_user_id" name="spoof_user_id" value="<?= static::$spoof_user_id ?>">
                    </div>
                </div>


               <div class="col-md-1"  style="background-color: darkgreen">
                   <div class="form-group">
                       <label for="source" style="color: limegreen">Unit Source:</label>
                       <select class="form-control" name="source" id="source">
                           <option value="" <?= ((static::$search_source === '')? 'SELECTED':'') ?> >All</option>
                           <option value="top-tag" <?= ((static::$search_source === 'top-tag')? 'SELECTED':'') ?> >Top Tags</option>
                           <option value="per-id" <?= ((static::$search_source === 'per-id')? 'SELECTED':'') ?> >Per ID</option>
                       </select>
                   </div>
               </div>

                <div class="col-md-2" style="background-color: darkgreen">
                    <div class="form-group">
                        <label for="tag" style="color: limegreen">Tag Name:</label>
                        <input type="text" class="form-control" id="tag" name="tag" value="<?= static::$search_tag ?>">
                    </div>
                </div>

                <div class="col-md-2" style="background-color: darkgreen">
                    <div class="form-group">
                        <label for="unit_type" style="color: limegreen">Unit Type:</label>
                        <select class="form-control" name="unit_type" id="unit_type">
                            <option value="" <?= ((static::$search_type === '')? 'SELECTED':'') ?> >All</option>
                            <option value="user" <?= ((static::$search_type === 'user')? 'SELECTED':'') ?> >Freelancers</option>
                            <option value="content" <?= ((static::$search_type === 'content')? 'SELECTED':'') ?> >Content</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2" style="background-color: darkgreen">
                    <div class="form-group">
                        <label for="pk" style="color: limegreen">ID (user or content):</label>
                        <input type="text" class="form-control" id="pk" name="pk" value="<?= static::$search_id ?>">
                    </div>
                </div>


            </form>
        </div>
        <?php
    }

    /**
     * @param string $type top-tag|per-id
     * @return string
     */
    protected static function generate_where_clause($type) {
        $where_parts = [];
        if (static::$search_source) {
            if ($type === 'top-tag' && static::$search_source === 'per-id') {
                $where_parts[] = ' 0 ';
            } elseif ($type === 'per-id' && static::$search_source === 'top-tag') {
                $where_parts[] = ' 0 ';
            }
        }

        if (static::$search_tag) {
            $escaped_thing = esc_sql(static::$search_tag);
            if ($type === 'top-tag') {
                $where_parts[] = "wit.tag_name like '%$escaped_thing%'";
            } elseif ($type === 'per-id') {
                $where_parts[] = "wit.tag_name like '%$escaped_thing%'";
            }
        }

        if (static::$search_type) {
            if ($type === 'top-tag') {
                if (static::$search_type === 'user') {
                    $where_parts[] = 'unit.content_id IS NULL';
                } elseif (static::$search_type === 'content') {
                    $where_parts[] = 'unit.user_id IS NULL';
                }

            } elseif ($type === 'per-id') {
                if (static::$search_type === 'user') {
                    $where_parts[] = 'per.job_id IS NULL';
                } elseif (static::$search_type === 'content') {
                    $where_parts[] = 'per.wp_user_id IS NULL';
                }
            }
        }

        if (static::$search_id) {
            $escaped_thing = intval(static::$search_id);
            if ($type === 'top-tag') {
                if (static::$search_type === 'user') {
                    $where_parts[] = 'unit.user_id = ' .$escaped_thing;
                } elseif (static::$search_type === 'content') {
                    $where_parts[] = 'unit.content_id =' .$escaped_thing;
                } else {
                    $where_parts[] = '( (unit.content_id =' .$escaped_thing . ') OR ( unit.user_id = ' .$escaped_thing . ') )';
                }

            } elseif ($type === 'per-id') {
                if (static::$search_type === 'user') {
                    $where_parts[] = 'per.wp_user_id =' .$escaped_thing;
                } elseif (static::$search_type === 'content') {
                    $where_parts[] = 'per.job_id =' .$escaped_thing;
                } else {
                    $where_parts[] = '( (per.job_id =' .$escaped_thing . ') OR ( per.wp_user_id = ' .$escaped_thing . ') )';
                }
            }
        }
        $ret = '';
        if (count($where_parts)) {
            $ret = ' AND ( ' . implode(' ) AND ( ',$where_parts) .' )';
        }
        return $ret;
    }

    protected static function get_total_unit_count() {
        global $wpdb;
        $da_total_count = 0;
        $where_clause_per_id = static::generate_where_clause('per-id');
        $where_clause_top = static::generate_where_clause('top-tag');
        $sql_to_get_top_templates = /** @lang text */
            "SELECT count(*) as da_count
                                FROM  wp_display_unit_user_content unit
                                INNER JOIN wp_interest_tags wit on unit.tag_id = wit.ID
                                WHERE when_html_updated IS NOT NULL AND unit.is_top_tag = 1
                                $where_clause_top
                                ORDER BY tag_id,unit.user_id,unit.content_id";

        $results_top_templates = $wpdb->get_results($sql_to_get_top_templates);
        will_throw_on_wpdb_error($wpdb);
//        will_dump('count top-tag',$wpdb->last_query);
        $da_total_count += $results_top_templates[0]->da_count;



        $sql_to_get_per_templates = /** @lang text */
            "SELECT  count(*) as da_count
                                FROM  wp_homepage_interest_per_id per
                                INNER JOIN wp_homepage_interest i on per.homepage_interest_id = i.id
                                INNER JOIN wp_interest_tags wit on i.tag_id = wit.ID
                                WHERE per.when_html_updated IS NOT NULL AND 
                                (per.wp_user_id IS NOT NULL OR per.job_id IS NOT NULL )
                                $where_clause_per_id
                                ORDER BY per.wp_user_id,per.job_id";

        $results_per_templates = $wpdb->get_results($sql_to_get_per_templates);
        will_throw_on_wpdb_error($wpdb);
//        will_dump('count per-id',$wpdb->last_query);
        $da_total_count += $results_per_templates[0]->da_count;
        return $da_total_count;

    }

    protected static function draw_units($page) {
       global $wpdb;

        if ($page === 1) {
            $offset = 0;
        } else {
            $offset = ($page-1) * static::UNITS_PER_PAGE;
        }

        $row_count = static::UNITS_PER_PAGE;
       $limit_clause = "LIMIT $offset , $row_count";

        $where_clause_per_id = static::generate_where_clause('per-id');
        $where_clause_top = static::generate_where_clause('top-tag');

       $sql_to_get_templates = /** @lang text */
           " SELECT 
            unit.id as pk,
            unit.user_id,unit.content_id,
            unit.html_generated,UNIX_TIMESTAMP(unit.when_html_updated) as when_ts,
            wit.tag_name,'".FreelinguistUnitGenerator::TYPE_TEMPLATE_TOP."' as template_type
            FROM  wp_display_unit_user_content unit
            INNER JOIN wp_interest_tags wit on unit.tag_id = wit.ID
            WHERE when_html_updated IS NOT NULL AND unit.is_top_tag = 1
            $where_clause_top
        UNION ALL 
            SELECT 
            per.id as pk,
            per.wp_user_id as user_id,per.job_id as content_id,
            per.html_generated,UNIX_TIMESTAMP(per.when_html_updated) as when_ts,
            wit.tag_name,'".FreelinguistUnitGenerator::TYPE_TEMPLATE_PER."' as template_type
            FROM  wp_homepage_interest_per_id per
            INNER JOIN wp_homepage_interest i on per.homepage_interest_id = i.id
            INNER JOIN wp_interest_tags wit on i.tag_id = wit.ID
            WHERE per.when_html_updated IS NOT NULL AND 
            (per.wp_user_id IS NOT NULL OR per.job_id IS NOT NULL )
            $where_clause_per_id
            ORDER BY tag_name,user_id,content_id
            $limit_clause
        ";

        $results_templates = $wpdb->get_results($sql_to_get_templates);
        will_throw_on_wpdb_error($wpdb);
//        will_dump('find units',$wpdb->last_query);



        if (static::$spoof_user_id && intval(static::$spoof_user_id)) {
            $template_vars = FreelinguistUnitDisplay::init_template_user_vars(static::$spoof_user_id);
        } else {
            $template_vars = FreelinguistUnitDisplay::init_template_user_vars(null);
        }
        $template_vars['display_admin_info'] = true;

        ?>
        <div style="margin-left: 2em">
            <h1>Units</h1>
        </div>


        <?php
            $counter = 0;
            foreach ($results_templates as $res) {
                foreach ($template_vars as $template_var_key => $template_var_value) {
                    $res->$template_var_key = $template_var_value;
                }
                static::output_template($res->html_generated,$counter,false,$res);
                $counter++;
            }
            static::output_template(null,$counter,true); //close out any open group
        ?>

        <!-- Process Slides with Javascript-->
        <script>
            jQuery(function(){
                jQuery('.flexslider').flexslider({
                    animation: "slide",
                    animationLoop: false,
                    slideshow: false,
                    itemWidth: 272,
                    itemMargin: 15,
                    after: function (slider) {
                        // console.log(12);
                        // nextSlide(slider);
                    },
                    end: function(slider){
                        nextSlide(slider);
                        // alert(1);
                        // console.log(slider.html());
                    }
                });

                jQuery(".a-timestamp-full-date-time").each(function () {
                    var qthis = $(this);
                    var ts = $(this).data('ts');
                    if (ts === 0 || ts === '0' || ts === undefined || ts === '') {
                        qthis.text('');
                    } else {
                        var ts_number = parseInt(ts.toString());
                        var m = moment(ts_number * 1000);
                        qthis.text(m.format('MMM D YYYY H:mm'));
                    }
                });
            });
        </script>
        <?php


    } //end draw units



    protected static function output_template($template, $counter, $b_maybe_close_only = false,$render_data = null) {

        /**
         * Logic translation:
         * if we are beginning a new grouping and this is not the very start, then end the last one
         * OR
         * If just wanting to close the last grouping, do so but only if we did not just close it or have not even begun
         */
        if (( $counter > 0 && ($counter % static::UNITS_PER_ROW === 0)) ||
            ($b_maybe_close_only && ($counter % static::UNITS_PER_ROW !== 0)) ) {
           print "
                </ul> <!-- ./slides -->
            </div> <!-- ./flexslider.toplinguist-grid -->
            ";
        }
        if($b_maybe_close_only) {return ;}

        if ($counter % static::UNITS_PER_ROW === 0) {
            ?>
            <div class="flexslider toplinguist-grid" style="margin-left: 2em; margin-bottom: 2em">
                <ul class="slides">
            <?php
        }
        try {
            static::render_template($template,$render_data);
        } catch (\Twig\Error\Error $e) {
            print "<li><span class='error'>Twig Error: ".  $e->getMessage()."</span></li>";
            print "<li><pre>". htmlentities($template) ."</pre></li>";
        } catch (Exception $e) {
            print "<li><span class='error'>General Error: ".  $e->getMessage()."</span></li>";
        }
    }

    /**
     * @param $template_string
     * @param object $render_data
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected static function render_template($template_string,$render_data=null) {
        if (!$template_string) {
            will_dump("Template string not provided");
            return;
        }
        $loader1 = new \Twig\Loader\ArrayLoader([
            'unit.html' => $template_string,
        ]);


        $loader = new \Twig\Loader\ChainLoader([$loader1]);

        $twig = new \Twig\Environment($loader, [
            //'cache' => '/path/to/compilation_cache',
            'debug' => true,
            'strict_variables' => false
        ]);

        $admin_vars = [
            'display_admin_info' => false,
            'pk' => '',
            'when_ts' => '',
            'tag_name'=> '',
            'template_type'=>'',
            'unit_type' => '',
            'unit_id' => ''
        ];

        $admin_props = [
            'pk' =>'unit_pk',
            'when_ts' => 'unit_ts',
            'tag_name'=> 'unit_tag',
            'template_type'=>'unit_source',
            'display_admin_info' => 'display_admin_info'
        ];
        if (!empty($render_data)) {
            foreach ($admin_props as $prop => $mapped_to) {
                if (property_exists($render_data,$prop)) {$admin_vars[$mapped_to] = $render_data->$prop;}
            }
        }


        if (property_exists($render_data,'user_id') && $render_data->user_id &&
            property_exists($render_data,'content_id') && $render_data->content_id) {
            $admin_vars['unit_type'] = 'Both Content And user';
            $admin_vars['unit_id'] = 'U='.$render_data->user_id . ', C=' . $render_data->content_id;
        }
        else if (property_exists($render_data,'user_id') && $render_data->user_id) {
            $admin_vars['unit_type'] = 'User';
            $admin_vars['unit_id'] = $render_data->user_id;
        }
        else if (property_exists($render_data,'content_id') && $render_data->content_id) {
            $admin_vars['unit_type'] = 'Content';
            $admin_vars['unit_id'] = $render_data->content_id;
        }

        $spoof_vars = [
            'favorite_content_array' => [],
            'favorite_users_array' => [],
            'user_logged_in' => false,
            'purchase_content_array' => [],
            'logged_in_user_id' => 0,
            'logged_in_user_email' => ''
        ];

        $spoof_props = [
            'favorite_content_array' =>'favorite_content_array',
            'favorite_users_array' => 'favorite_users_array',
            'user_logged_in'=> 'user_logged_in',
            'purchase_content_array'=>'purchase_content_array',
            'logged_in_user_id' =>  'logged_in_user_id',
            'logged_in_user_email' => 'logged_in_user_email'
        ];
        if (!empty($render_data)) {
            foreach ($spoof_props as $prop => $mapped_to) {
                if (property_exists($render_data,$prop)) {$spoof_vars[$mapped_to] = $render_data->$prop;}
            }
        }

        $vars_to_template = array_merge($spoof_vars,$admin_vars);

//        will_send_to_error_log('$vars_to_template in the render_template',$vars_to_template,false,true);
//        will_send_to_error_log('da template',$template_string);
        echo $twig->render('unit.html', $vars_to_template);

    }



} //end class

//create single instance of this class
