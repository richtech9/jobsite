<?php
//for the twig library
require_once( ABSPATH . '/wp-content/themes/the-translator/vendor/autoload.php');

/**
 * Class FreelinguistUnitDisplay
 * This is the class which displays the Units on the homepage
 * its main usages is from its static function of @see FreelinguistUnitDisplay::getHomepageInterestList
 *    so, if tracking this from the homepage, start there
 *
 *  the helper static method of @see FreelinguistUnitDisplay::init_template_user_vars()
 *      is also called by @see AdminPageTestUnits::draw_units()
 *
 * While this class will get and organize the units to display, all the actual rendering of the html is done through
 *      two helper classes also in this directory
 *
 * @uses FreelinguistUnitDisplayHomepageTagRow
 * @uses FreelinguistUnitDisplaySingleUnit
 *
 */
class FreelinguistUnitDisplay {

    /**
     * How many units to show for each tag, when displaying units
     * This can be modified by passing in parameters at @see FreelinguistUnitDisplay::display_units
     */
    const DEFAULT_HOW_MANY_UNITS_PER_TAGS = 12;

    /**
     * How many tags to show for each page, when displaying units
     * This can be modified by passing in parameters at @see FreelinguistUnitDisplay::display_units
     */
    const DEFAULT_HOW_MANY_TAGS_PER_PAGE = 10;

    /**
     * Turns on and off using the ES to get the unit templates
     * If off, will just get them from the database instead
     * If on, will try to use ES,and if cannot will use the DB as backup for the templates
     * @see FreelinguistUnitDisplay::display_units
     */
    const B_USE_ES_FOR_VIEWING = true;

    /**
     * Turns on extra information to be printed in the units when they are rendered.
     *  This is used when looking at the units in the admin page, as it makes it easy to track issues there
     *   And its turned off when showing the units on the home page
     * Used By:
     * @see FreelinguistUnitDisplayHomepageTagRow
     * @see FreelinguistUnitDisplaySingleUnit
     *
     * @var bool
     */
    public static $b_debug = true; //if true will output information

    /**
     * The companion setting for the @see FreelinguistUnitDisplay::$b_debug, is only used when that is on
     *
     * Determines which information is to be printed in the units when they are to show debug information
     *  level 2 sends the admin variables to the twig templates, which has some if statements in them to show things based on those values
     *  level 3 adds a new li element, in each unit, that displays the twig template itself (using html entities so that the < and > can be see in the browser)
     *  level 4 adds a new li element , in each unit, that displays the template vars going to the twig engine (like user or content information)
     *  and level 5, is, don't use level 5...ever (its not implemented, and never was). Seriously, its okay to go above these numbers, will just show the max setting defined

     * Used By:
     * @see FreelinguistUnitDisplayHomepageTagRow
     * @see FreelinguistUnitDisplaySingleUnit
     * @var int
     */
    public static $n_debug_level = 1; //how much information to output to screen or log


    /**
     *
     *  function to render a template with twig
     *  its marked as public, but only meant for the helper classes to use (php does not define package level access easily)
     *
     *  basically, it takes the variables given, initializes twig, and renders that template string with the variables
     *  its a thin wrapper for the twig engine, used by the functions that render the html to be outputted
     *
     * used by  @see FreelinguistUnitDisplayHomepageTagRow::output_template
     *          @see FreelinguistUnitDisplaySingleUnit::output_template
     *
     * @param $template_string
     * @param array $render_data
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public static function render_template($template_string,$render_data=[]) {
        if (!$template_string) {
            will_send_to_error_log("FreelinguistUnitDisplay::render_template Template string not provided");
            return;
        }
        $loader1 = new \Twig\Loader\ArrayLoader([
            'unit.html' => $template_string,
        ]);

        $loader = new \Twig\Loader\ChainLoader([$loader1]);

        $twig = new \Twig\Environment($loader, [
            //'cache' => '/path/to/compilation_cache',
            'debug' => false,
            'strict_variables' => false
        ]);

//        will_send_to_error_log('$vars_to_template in the render_template',$vars_to_template,false,true);
//        will_send_to_error_log('da template',$template_string);
        echo $twig->render('unit.html', $render_data);

    }

    /**
     * When the templates are rendered, the twig will display different things based on the user settings
     *  for example, whether this is a favorite or has been purchased by the user
     *
     * We only need to call this once, for all the units drawn on the page, and it has the following information
     * ------
 *
* 'favorite_content_array' => an array of favorite content ids , this is not as straightforward to get
                               * , as one might think, as the old code puts different formats into that meta, based on how many are there
 *
* 'favorite_users_array' => same as for content, but users
    * 'user_logged_in' => boolean, true if the user is logged in, will activate some if statements in the twig to try to use other data to fill in more dynamic sections
 *
* 'purchase_content_array' => an array of content ids the user has bought
 *
* 'logged_in_user_id'=>  the id of the user, if they are logged in
 *
* 'logged_in_user_email'=> the user email
 *
* 'display_admin_info' => whether to display the admin info. This is always false, and is changed by the debug settings above later, before the template is rendered
 * -------
     *
     * This is used by
     * @see AdminPageTestUnits::draw_units()
     * @see FreelinguistUnitDisplay::generate_html()
     *
     *

     * @param int $user_id (null if $b_use_logged_in_id is true)
     * @param bool $b_use_logged_in_id
     * @return array
     */
    public static function init_template_user_vars($user_id,$b_use_logged_in_id=false) {
        global $wpdb;

        $user_id = (int)$user_id;

        $user_logged_in = false;
        $user_email = '';
        $favorite_content_array = [];
        $favorite_users_array = [];
        $purchase_content_array = [];

        $user = false;
        if (!$user_id && $b_use_logged_in_id) {
            $user_id = get_current_user_id();
            if ($user_id) {
                $user = get_userdata($user_id);
            }
        }

        if ($user_id) {
            try {
                $user_logged_in = true;

                $sql_purchase_info = "SELECT id FROM wp_linguist_content WHERE purchased_by =  $user_id AND user_id IS NOT NULL";

                $results_purchase_info = $wpdb->get_results($sql_purchase_info);
                will_throw_on_wpdb_error($wpdb);
                foreach ($results_purchase_info as $purchased_content_object) {
                    $purchase_content_array[] = $purchased_content_object->id;
                };

                //will_send_to_error_log('!!! purchase content array',$purchase_content_array);

                $favContentIds = get_user_meta($user_id, '_favorite_content', true);
                if (is_array($favContentIds)) {
                    foreach ($favContentIds as $favc_key => $favc_val) {
                        if (is_array($favc_val)) {throw new LogicException("Cannot figure out _favorite_content subarray");}
                        $favc_val = trim($favc_val);
                        $favorite_content_array[] = (int)$favc_val;
                    }
                } else {
                    if (is_string($favContentIds)) {
                        $temp_array = explode(',',$favContentIds);
                        foreach ($temp_array as $favc_key => $favc_val) {
                            $favc_val = trim($favc_val);
                            $favorite_content_array[] = (int)$favc_val;
                        }
                    } else {
                        throw new LogicException("Cannot understand _favorite_content format");
                    }
                }
                $favTranslatorIds = get_user_meta($user_id, '_favorite_translator', true);
                if (is_array($favTranslatorIds)) {
                    foreach ($favTranslatorIds as $favc_key => $favc_val) {
                        if (is_array($favc_val)) {throw new LogicException("Cannot figure out _favorite_translator subarray");}
                        $favc_val = trim($favc_val);
                        $favorite_users_array[] = (int)$favc_val;
                    }
                } else {
                    if (is_string($favTranslatorIds)) {
                        $temp_array = explode(',',$favTranslatorIds);
                        foreach ($temp_array as $favc_key => $favc_val) {
                            $favc_val = trim($favc_val);
                            $favorite_users_array[] = (int)$favc_val;
                        }
                    } else {
                        throw new LogicException("Cannot understand _favorite_translator format");
                    }
                }

                if ($user) {
                    $user_email = $user->user_email;
                } else {
                    $sql_for_user_email = "SELECT user_email from wp_users WHERE ID = $user_id";
                    $results_user_email = $wpdb->get_results($sql_for_user_email);
                    will_throw_on_wpdb_error($wpdb);
                    if (count($results_user_email)) {
                        $user_email = $results_user_email[0]->user_email;
                    }
                }



            } catch (Exception $e) {
                will_send_to_error_log("could not fill in all the user info for the unit template", $e->getMessage());
            }

        }

        $spoof_vars = [
            'favorite_content_array' => $favorite_content_array,
            'favorite_users_array' => $favorite_users_array,
            'user_logged_in' => $user_logged_in,
            'purchase_content_array' => $purchase_content_array,
            'logged_in_user_id'=> $user_id,
            'logged_in_user_email'=>$user_email,
            'display_admin_info' => false
        ];


        return $spoof_vars;

    }

    /**
     * @internal method to this class
     *  called by @see FreelinguistUnitDisplay::getHomepageInterestList
     *   will call the functions that have the same param signature to display the units from the ES or the DB
     *
     * Will not crash the page if neither is working, will simply return no units then
     *
     * @uses FreelinguistUnitDisplay::display_units_from_mysql()
     * @uses FreelinguistUnitDisplay::display_units_from_es()
     *
     * Tries the Elastic Search first and if not having the data, will fallback to mysql
     *
     * @param int[]|null $array_only_these_tag_ids , if null then all tags, if empty array then no tags,
     *                                                  else intersection of list and what is found for tags
     *                          This can have two formats: the first is an array of tag id integers
     *                          The second is an array of arrays where each sub-array is ['tag_id'=> {int},'is_user_pref' => 0|1 ]
     *
     * @param bool $b_include_per_id
     * @param int $n_limit_per_tag
     * @param int $n_tags_per_page
     * @param int $n_override_page_number, if non zero will use this as the current page, else will get the page from $_REQUEST
     *
     * @return array
     */
    protected static function display_units($array_only_these_tag_ids,
                                         $b_include_per_id = true,
                                         $n_limit_per_tag = self::DEFAULT_HOW_MANY_UNITS_PER_TAGS,
                                         $n_tags_per_page = self::DEFAULT_HOW_MANY_TAGS_PER_PAGE,
                                         $n_override_page_number = 0
    ) {

        $b_ran_myql_once = false;
        try {
            if (static::B_USE_ES_FOR_VIEWING) {
                return static::display_units_from_es($array_only_these_tag_ids, $b_include_per_id,
                    $n_limit_per_tag, $n_tags_per_page, $n_override_page_number);
            } else {
                $b_ran_myql_once = true;
                return static::display_units_from_mysql($array_only_these_tag_ids,$b_include_per_id,
                    $n_limit_per_tag,$n_tags_per_page,$n_override_page_number);
            }
        } catch (Exception $e) {
            will_send_to_error_log('Cannot do ES units',will_get_exception_string($e));
            if (!$b_ran_myql_once) {
                try {
                    return static::display_units_from_mysql($array_only_these_tag_ids, $b_include_per_id,
                        $n_limit_per_tag, $n_tags_per_page, $n_override_page_number);
                } catch (Exception $innerE) {
                    will_send_to_error_log('mysql in unit view (run setup second) acting up',
                                                will_get_exception_string($innerE));
                    return [];
                }
            } else {
                will_send_to_error_log('Cannot use either ES or mysql to get units. 
                Tried to run mysql after ES fail, in unit view (run setup first).
                 But mysql acting up',will_get_exception_string($e));
                return [];
            }

        }

    }


    /**
     * @internal method to this class
     * Called by the @see FreelinguistUnitDisplay::display_units()
     *
     * It calls the @uses FreelinguistUnitDisplay::get_templates_via_elastic_search(),
     *      which returns a random assortment of $n_limit_per_tag units per tag
     *
     * And then modifies some of the fields used in the ES records to match those returned by the DB, and does some counting
     *  it @uses FreelinguistUnitDisplay::get_page_and_final() and
     *     @uses FreelinguistUnitDisplay::generate_pref_map()
     *
     *   both, to set some internal variables for
     *   the call to @uses FreelinguistUnitDisplay::generate_html() which takes the records given to it, and the internal data, and produces the html
     *
     * Will throw exception if there is not data there, or no connection, etc
     * @param int[]|null $array_only_these_tag_ids , if null then all tags, if empty array then no tags,
     *                                                  else intersection of list and what is found for tags
     *                          This can have two formats: the first is an array of tag id integers
     *                          The second is an array of arrays where each sub-array is ['tag_id'=> {int},'is_user_pref' => 0|1 ]
     *
     * @param bool $b_include_per_id
     * @param int $n_limit_per_tag
     * @param int $n_tags_per_page
     * @param int $n_override_page_number, if non zero will use this as the current page, else will get the page from $_REQUEST
     *
     * @return array
     * @throws
     */
    protected static function display_units_from_es($array_only_these_tag_ids,
                                                    $b_include_per_id = true,
                                                    $n_limit_per_tag = self::DEFAULT_HOW_MANY_UNITS_PER_TAGS,
                                                    $n_tags_per_page = self::DEFAULT_HOW_MANY_TAGS_PER_PAGE,
                                                    $n_override_page_number = 0
    ) {


        static::get_page_and_final(0,$n_tags_per_page,$n_override_page_number,$page,$finish,$total_pages);

        $units_from_es = static::get_templates_via_elastic_search($array_only_these_tag_ids,$n_limit_per_tag);

        //todo add //da_top_count,da_per_count,is_title_hidden,priority_number
        //map from is_tag_title_hidden
        $my_field_map = [
            'is_tag_title_hidden' => 'is_title_hidden',
            'template' => 'html_generated'
        ];

        //first count the top and the per
        $da_top_count = 0;
        $da_per_count = 0;

        foreach ($units_from_es as $tag_id => $array_of_units) {
            foreach ($array_of_units as $unit) {
                if (array_key_exists('template_type',$unit)) {
                    if ($unit['template_type'] === 'top') {
                        $da_top_count++;
                    } elseif ($unit['template_type'] === 'top' && $b_include_per_id) {
                        $da_per_count++;
                    }
                }
                }
            }


        $results_tags = [];
        foreach ($units_from_es as $tag_id => $array_of_units) {
            foreach ($array_of_units as $unit) {

                foreach ($my_field_map as $my_string => $export_string) {
                    if (array_key_exists($my_string,$unit)) {
                        $unit[$export_string]= $unit[$my_string];
                        unset($unit[$my_string]);
                    }
                } //end conversion

                $unit['da_per_count'] = $da_per_count;
                $unit['da_top_count'] = $da_top_count;


                //filter out per id units, if not wanted, else include
                if (!$b_include_per_id) {
                    if (array_key_exists('template_type',$unit)) {
                        if ($unit['template_type'] === 'top') {
                            $results_tags[] = (object)$unit;
                        }
                    }
                } else {
                    $results_tags[] = (object)$unit;
                }

            }
        }

        if (static::$b_debug) {
            $copy_me_string = json_encode($results_tags);
            $copy_me = json_decode($copy_me_string);
            foreach ($copy_me as &$copy_less) {
                if (property_exists($copy_less, 'html_generated')) {
                    unset($copy_less->html_generated);
                    $copy_less->has_html = true;
                } else {
                    $copy_less->has_html = false;
                }
            }
            //FreelinguistDebugFramework::note('units going out',$copy_me);
        }



        //will_send_to_error_log("raw results back from get_templates_via_elastic_search",$units_from_es);
        //code-notes after fixing up the above function to return the units, sort them out, pick the correct number and send them on
        if(empty($results_tags)){ //return like caller expects

            return array('html'=>'','status'=>0,'current_page'=>$page,'total_pages'=>$total_pages,'finish'=>1);
        }


        $is_user_pref_map = static::generate_pref_map($array_only_these_tag_ids);
        $html = static::generate_html($page,$n_tags_per_page,$n_limit_per_tag,$is_user_pref_map,$results_tags);


        return array('html'=>$html,'status'=>1,'current_page'=>$page,'total_pages'=>$total_pages,'finish'=>$finish);
    }

    /**
     * * @internal method to this class
     * Called by the @see FreelinguistUnitDisplay::display_units()
     *
     * It makes a single SQL query here that
     *      find a random assortment of $n_limit_per_tag units per tag
     *
     *
     *  it @uses FreelinguistUnitDisplay::get_page_and_final() and
     *     @uses FreelinguistUnitDisplay::generate_pref_map()
     *
     *   both, to set some internal variables for
     *   the call to @uses FreelinguistUnitDisplay::generate_html() which takes the records given to it, and the internal data, and produces the html
     *
     * will throw exception if issues
     * @param int[]|null $array_only_these_tag_ids , if null then all tags, if empty array then no tags,
     *                                                  else intersection of list and what is found for tags
     *                          This can have two formats: the first is an array of tag id integers
     *                          The second is an array of arrays where each sub-array is ['tag_id'=> {int},'is_user_pref' => 0|1 ]
     *
     * @param bool $b_include_per_id
     * @param int $n_limit_per_tag
     * @param int $n_tags_per_page
     * @param int $n_override_page_number, if non zero will use this as the current page, else will get the page from $_REQUEST
     *
     * @return array
     */
    protected static function display_units_from_mysql($array_only_these_tag_ids,
                                         $b_include_per_id = true,
                                         $n_limit_per_tag = self::DEFAULT_HOW_MANY_UNITS_PER_TAGS,
                                         $n_tags_per_page = self::DEFAULT_HOW_MANY_TAGS_PER_PAGE,
                                         $n_override_page_number = 0
                                         ) {
        global $wpdb;


        $where_tag_id = '';
        $results_tags = [];

       static::get_page_and_final(0,$n_tags_per_page,$n_override_page_number,$page,$finish,$total_pages);

        if (is_null($array_only_these_tag_ids)) {
            $where_tag_id = '';
            $b_search = false;
        }
        else if (empty($array_only_these_tag_ids)) {
            $b_search = true;
        }
        else {
            $intermediate = [];

            foreach ($array_only_these_tag_ids as $tag_id_index => $tag_id_or_array) {
                if (is_array($tag_id_or_array)) {
                    $tag_id_as_int = (int)$tag_id_or_array['tag_id'];
                    $intermediate[$tag_id_as_int] = $tag_id_as_int;
                } else {
                    $intermediate[(int)$tag_id_or_array] = (int)$tag_id_or_array;
                }

            }
            if (count($intermediate)) {
                $where_tag_id = 'AND wit.ID in ('. implode(',',array_keys($intermediate)) . ')';
                $b_search = true;
            } else {
                $b_search = false;
            }
        }

        $b_get_templates_from_db = true;

        if ($b_search) {

            $unit_template_fields = '';
            $top_template_fields = '';
            if ($b_get_templates_from_db) {
                $unit_template_fields = 'unit.html_generated,UNIX_TIMESTAMP(unit.when_html_updated) as when_ts,';
                $top_template_fields = 'per.html_generated,UNIX_TIMESTAMP(per.when_html_updated) as when_ts,';
            }

            //get the list of tags between the per and the top tags
            $sql_to_get_tags = /** @lang text */
                " 
            SELECT DISTINCT
            $unit_template_fields
            unit.user_id,unit.content_id,
            wit.tag_name,wit.ID as tag_id, count_deese.da_count da_top_count, 0 as da_per_count,
            IF(i.priority_number IS NOT NULL,i.priority_number,0) as priority_number,
            IF(i.is_title_hidden IS NOT NULL,i.is_title_hidden,0) as is_title_hidden,
            '".FreelinguistUnitGenerator::TYPE_TEMPLATE_TOP."' as template_type,
            unit.id as pk
            FROM  wp_display_unit_user_content unit
            INNER JOIN wp_interest_tags wit on unit.tag_id = wit.ID
            LEFT JOIN wp_homepage_interest i ON i.tag_id = wit.ID
            CROSS JOIN (
                SELECT count(distinct wit.ID) as da_count
                FROM  wp_display_unit_user_content unit
                INNER JOIN wp_interest_tags wit on unit.tag_id = wit.ID
                LEFT JOIN wp_homepage_interest i ON i.tag_id = wit.ID
                WHERE when_html_updated IS NOT NULL AND unit.is_top_tag = 1
                $where_tag_id
            ) as count_deese
            WHERE when_html_updated IS NOT NULL AND unit.is_top_tag = 1
            $where_tag_id ";

            if ($b_include_per_id) {
                $sql_to_get_tags .= /** @lang text */
                    "
                    
                    UNION DISTINCT 
            
            
            SELECT DISTINCT
            $top_template_fields
            per.wp_user_id as user_id,per.job_id as content_id,
            wit.tag_name,wit.ID as tag_id, 0 da_top_count, count_deese.da_count as da_per_count,
            i.priority_number,i.is_title_hidden,
            '".FreelinguistUnitGenerator::TYPE_TEMPLATE_PER."' as template_type,
            per.id as pk
            FROM  wp_homepage_interest_per_id per
            INNER JOIN wp_homepage_interest i on per.homepage_interest_id = i.id
            INNER JOIN wp_interest_tags wit on i.tag_id = wit.ID
            CROSS JOIN (
                SELECT count(distinct wit.ID) as da_count 
                FROM  wp_homepage_interest_per_id per
                INNER JOIN wp_homepage_interest i on per.homepage_interest_id = i.id
                INNER JOIN wp_interest_tags wit on i.tag_id = wit.ID
                WHERE per.when_html_updated IS NOT NULL AND 
                (per.wp_user_id IS NOT NULL OR per.job_id IS NOT NULL )
                $where_tag_id
            ) as count_deese
            WHERE per.when_html_updated IS NOT NULL AND 
            (per.wp_user_id IS NOT NULL OR per.job_id IS NOT NULL ) 
            $where_tag_id
            ORDER BY priority_number,tag_name
            
            ";
            }

            $results_tags = $wpdb->get_results($sql_to_get_tags);
            will_throw_on_wpdb_error($wpdb);

//                will_send_to_error_log('rows-> ',[
//        'sql' => $wpdb->last_query,
//        'results' => $results_tags,
//    ]);



        } //end if search

        if(empty($results_tags)){ //return like caller expects

            return array('html'=>'','status'=>0,'current_page'=>$page,'total_pages'=>$total_pages,'finish'=>1);
        }

        $is_user_pref_map = static::generate_pref_map($array_only_these_tag_ids);
        $html = static::generate_html($page,$n_tags_per_page,$n_limit_per_tag,$is_user_pref_map,$results_tags);


        return array('html'=>$html,'status'=>1,'current_page'=>$page,'total_pages'=>$total_pages,'finish'=>$finish);

    }

    /**
     * @internal method of the class
     *  This is called from the two functions that get the template data
     *      @see  FreelinguistUnitDisplay::display_units_from_es
     *      @see  FreelinguistUnitDisplay::display_units_from_mysql
     *
     * and sets up the page, finish flag, and total pages, which is used in the
     * @see FreelinguistUnitDisplay::generate_html which is also called by the above
     *
     * This method exists because their data they use to set the same logic comes from different sources
     *
     *
     * @param int $total_count (unused set to 0)
     * @param int $n_tags_per_page (the number tags per page)
     * @param int $n_override_page_number (if we want to override the page number)
     * @param int $page OUTREF
     * @param int $finish OUTREF
     * @param int $total_pages OUTREF
     */
    protected static function get_page_and_final($total_count,$n_tags_per_page,$n_override_page_number,&$page,&$finish,&$total_pages) {
        will_do_nothing([$total_count]);
        $page = 1;
        $total_pages = 0;
        if (isset($_REQUEST["paged"])) {
            $page = (int)$_REQUEST["paged"];
        }
        if ($n_override_page_number) {
            $page = $n_override_page_number;
        }

        if($page<=0){ $page = 1; }

        $finish = 0;
        if(($n_tags_per_page/$page)>$total_pages){
            $finish = 1;
        }
    }

    /**
     *  * @internal method of the class
     *  This is called from the two functions that get the template data
     *      @see  FreelinguistUnitDisplay::display_units_from_es
     *      @see  FreelinguistUnitDisplay::display_units_from_mysql
     *
     * and sets up the page, finish flag, and total pages, which is used in the
     * @see FreelinguistUnitDisplay::generate_html which is also called by the above
     *
     * This method exists to standardize the calling environment
     *
     * @param $array_only_these_tag_ids
     * @return array
     */
    protected static function generate_pref_map($array_only_these_tag_ids) {
        $is_user_pref_map = [];
        foreach ($array_only_these_tag_ids as $tag_id_index => $tag_id_or_array) {
            if (is_array($tag_id_or_array)) {
                $tag_id_as_int = (int)$tag_id_or_array['tag_id'];
                $is_user_pref_as_int = (int)$tag_id_or_array['is_user_pref'];
                $is_user_pref_map[$tag_id_as_int] = $is_user_pref_as_int;
            }
        }
        return $is_user_pref_map;
    }

    /**
     * @internal method of this class
     *
     *  This is called from the two functions that get the template data
     *      @see  FreelinguistUnitDisplay::display_units_from_es
     *      @see  FreelinguistUnitDisplay::display_units_from_mysql
     *
     * and sends the results data to the helper class which render the twig templates
     *
     * Logic:
     * @uses FreelinguistUnitDisplay::init_template_user_vars() to get the logged in user information,
     *      so the templates can be rendered for that user's information
     *
     * Given all the records, we decide which to display, based on the page number, and the amount per page
     *
     *   then, for each tag, it makes a new @see FreelinguistUnitDisplayHomepageTagRow which makes the html for each tag section
     *      and for each tag section, we put in the units that belong to it,
     *          and for each unit, we make a new @see FreelinguistUnitDisplaySingleUnit object
     *              which is created by the function @see FreelinguistUnitDisplay::make_single_unit()
     *
     *    Once we have all the units inside a section, we call the @see FreelinguistUnitDisplayHomepageTagRow::output_template which
     *          generates the html to make the tag area, and calls each of its @see FreelinguistUnitDisplaySingleUnit::output_template()
     *              to render the unit inside the tag area
     *
     *   We store all that html, as one string, and return it when all the rendering is done
     *
     *
     * @param int $page
     * @param int $n_tags_per_page, decides on how many tags per page, it uses this and the $page to decide which tag sections to output
     * @param int $n_limit_per_tag, decides how many tags to display in each section , this may already have been limited in the result tags
     * @param $is_user_pref_map, tells which tags are being displayed because of a user preference, if so tells the template that when rendering the tag area
     *                              so it displays a way for the user to turn off that preference
     *
     * @param array $results_tags, the info for each unit, includes the template
     * @return string (the rendered html)
     */
    protected static function generate_html($page,$n_tags_per_page,$n_limit_per_tag,$is_user_pref_map,$results_tags) {
        $html = '';

        if ($page === 1) {
            $offset = 0;
        } else {
            $offset = ($page-1) * $n_tags_per_page;
        }

        $end_number = $offset + $n_tags_per_page;

        $user_twig_vars = static::init_template_user_vars(null,true);

        $count_parts = ['top'=>0,'per'=>0];

        /**
         * @var FreelinguistUnitDisplayHomepageTagRow[] $cache_for_section
         */
        $cache_for_section = [];

        foreach ($results_tags as $tag_row) {
            if ($tag_row->da_top_count && !$count_parts['top']) { $count_parts['top'] =$tag_row->da_top_count; }
            if ($tag_row->da_per_count && !$count_parts['per']) { $count_parts['per'] =$tag_row->da_per_count; }
        }

        $total_count = $count_parts['top'] + $count_parts['per'];
        $total_pages = ($total_count) ? ceil($total_count/$n_tags_per_page) : 1;

        $cache_count = -1;
        $unit_count_per_tag = 1;

        //temp vars
        $last_tag_id = 0;
        $tag_count = 0;

        /**
         * @var FreelinguistUnitDisplayHomepageTagRow $existing_section
         */
        $existing_section = null;




        /**
         * Logic:
         *      we go through row,
         *          IF the row type and tag id change, and we have an existing section, then we add the old section to the html
         *          IF the row type and tag id do not change, then we add the unit template to the existing section unless we are over the tag count
         */
        foreach ($results_tags as $tag_row) {
            if ($last_tag_id !== $tag_row->tag_id) {
                $tag_count ++;
                $last_tag_id = $tag_row->tag_id;
            }

            if ( ($tag_count < $offset) || ($tag_count > $end_number)) {
                continue;
            }




            $cache_key = $tag_row->tag_name;
            if (array_key_exists($cache_key,$cache_for_section)) {
                if  ($unit_count_per_tag >= $n_limit_per_tag) { continue; }
                $section = $cache_for_section[$cache_key];
                $section->units[] =  static::make_single_unit($tag_row,$user_twig_vars);
                $unit_count_per_tag ++;

            } else {
                if ($existing_section) {
                    ob_start();
                    $existing_section->output_template();
                    $html .= ob_get_clean();
                    $existing_section = null;
                }
                $cache_count++;
                $unit_count_per_tag = 1;
                $section = new FreelinguistUnitDisplayHomepageTagRow();
                $cache_for_section[$cache_key] = $section;
                $existing_section = $section;

                $section->page_total = $total_pages;
                $section->page_number = $page;

                if ($tag_row->template_type === FreelinguistUnitGenerator::TYPE_TEMPLATE_PER) {
                    $section->is_new_style = 1;
                } else {
                    $section->is_new_style = 0;
                }

                $section->is_title_hidden = $tag_row->is_title_hidden;
                $section->tag_id = $tag_row->tag_id;
                $section->tag_name = $tag_row->tag_name;
                $section->background_color = '#' . ($cache_count % 2 != 0) ? 'f9f9f9' : 'ffffff';;
                $section->show_add_tag = 0;
                if (array_key_exists($section->tag_id,$is_user_pref_map) && $is_user_pref_map[$section->tag_id] ) {
                    $section->is_user_pref = 1;
                } else {
                    $section->is_user_pref = 0;
                }
                $section->units[] =  static::make_single_unit($tag_row,$user_twig_vars);
            } //end if, else section (if not in cache)
        } //end foreach row

        //print out last section
        if ($existing_section) {
            ob_start();
            $existing_section->output_template();
            $html .= ob_get_clean();
            $existing_section = null;
        }
        return $html;
    }

    /**
     * @internal method of this class
     * Its called by @see FreelinguistUnitDisplay::generate_html()
     *  And its whole purpose for existing is to add any extra variables to the twig template that is used for debugging
     *   (remember that debugging is not just for seeing when things go wrong, but for viewing the units in the admin page as a normal display feature)
     *
     * Once it initializes the admin vars, it calls the @uses  FreelinguistUnitDisplaySingleUnit and creates a new object of it, and returns it
     *
     * @param array|object $node
     * @param array $user_twig_vars
     * @return FreelinguistUnitDisplaySingleUnit
     */
    public static function make_single_unit($node,$user_twig_vars) {
        $tag_row = (object)$node;

        $admin_props_to_map = [
            'pk' =>'unit_pk',
            'when_ts' => 'unit_ts',
            'tag_name'=> 'unit_tag',
            'template_type'=>'unit_source',
            'display_admin_info' => 'display_admin_info'
        ];

        $admin_vars = [
            'pk' =>'unit_pk',
            'when_ts' => 'unit_ts',
            'tag_name'=> 'unit_tag',
            'template_type'=>'unit_source',
            'display_admin_info' => true
        ];

        foreach ($admin_props_to_map as $prop => $mapped_to) {
            if (property_exists($tag_row,$prop)) {$admin_vars[$mapped_to] = $tag_row->$prop;}
        }
        if (property_exists($tag_row,'user_id') && $tag_row->user_id &&
            property_exists($tag_row,'content_id') && $tag_row->content_id) {
            $admin_vars['unit_type'] = 'Both Content And user';
            $admin_vars['unit_id'] = 'U='.$tag_row->user_id . ', C=' . $tag_row->content_id;
        }
        else if (property_exists($tag_row,'user_id') && $tag_row->user_id) {
            $admin_vars['unit_type'] = 'User';
            $admin_vars['unit_id'] = $tag_row->user_id;
        }
        else if (property_exists($tag_row,'content_id') && $tag_row->content_id) {
            $admin_vars['unit_type'] = 'Content';
            $admin_vars['unit_id'] = $tag_row->content_id;
        }

        $ret = new FreelinguistUnitDisplaySingleUnit($tag_row->html_generated,$user_twig_vars,$admin_vars);
        return $ret;
    }


    /**
     * @internal method of this class
     * called by the @see FreelinguistUnitDisplay::display_units_from_es()
     *
     * This does the ES query to get ALL the units for only the tags requested, and then
     * @uses FreelinguistUnitDisplay::get_templates_from_es_results() to clean up the results which and repackage the data for the higher up functions to use
     *  and then it limits the results the the number asked for, and randomizes the result order inside of each tag category
     * it then returns an array of each result for each tag asked for
     *
     * @param int[] $tags
     * @param int $n_limit
     * @return array[]
     * @throws
     */
    protected static function get_templates_via_elastic_search($tags,$n_limit) {
        $tag_ids = [];
        foreach ($tags as $tag_thing) {
            if (is_array($tag_thing)) {
                if (isset($tag_thing['tag_id'])) {$tag_ids[] = (int)$tag_thing['tag_id']; }
            } else {
                if (intval($tag_thing) == $tag_thing) {
                    $tag_ids[] = (int)$tag_thing;
                }
            }
        }

        try {

            $matches_one_of_the_tags = implode(' OR ', $tag_ids);
            //search elasticsearch for $n_limit of each tag in results, if only want top, then exclude per-id
            //ELASTIC CONNECTION

            $es = new FreelinguistElasticSearchHelper();

            $params = [
                'index' => FreelinguistUnitGenerator::ELASTIC_INDEX_FOR_UNITS,
                'type' => FreelinguistUnitGenerator::ELASTIC_UNIT_TYPE,
                'body' => [
                    "from" => 0,
                    "size" => 3000,
                    "sort" => [
                        ["priority_number" => ["order" => "asc"]],
                        ["tag_id" => ["order" => "asc"]],
                        ["user_id" => ["order" => "asc"]],
                        ["content_id" => ["order" => "asc"]]
                    ],
                    'query' => [
                        'query_string' => [
                            "default_field" => "tag_id",
                            //'fields' => ['tag_id'],
                            'query' => $matches_one_of_the_tags,
                            //code-notes build query to return any units matching one of the tag ids

                        ]
                    ]
                ]
            ];

           // will_send_to_error_log('es q', $params);

            $hits = $es->get_client()->search($params);
            $template_nodes = static::get_templates_from_es_results($hits);
            $all = [];
            $ret = [];
            //get up to $n_limit from each 'tag_id'

            foreach ($template_nodes as $node) {
                $this_tag_id = (int)$node['tag_id'];
                if (!isset($all[$this_tag_id])) {$all[$this_tag_id] = [];}
                $all[$this_tag_id][] = $node;
            }

            $random = [];
            foreach ($all as $aindex => $aarray) {
                shuffle($aarray);
                $random[$aindex] = $aarray;
            }

            foreach ($random as $aindex => $aarray) {
                $ret[$aindex] = array_slice($aarray, 0, $n_limit);
            }
            return $ret;
        } catch (Exception $e) {
            will_send_to_error_log('ES error',will_get_exception_string($e));
            throw $e;
        }



    }

    /**
     * @internal method for this class
     *
     * It takes off the outer ES result data layer, and returns the nuggets of juicy data goodness
     * so, it returns the actual records, not the meta data from ES
     *
     * If it encounters a data setup it does not recognize, it will not return that data
     * So, if in the future, one needs to track why the ES is not returning the data expected, but the indexes are full, then check this function
     * this is particularly true if the version of ES is increased, and it has different meta data structure
     * @param array $res
     * @return array
     */
    protected static function get_templates_from_es_results($res) {
        $c = $res;
        $ret = [];
        if (is_array($c)) {
            if (isset($c['hits'])) {
                if (isset($c['hits']['hits'])) {
                    if (isset($c['hits']['hits'])) {
                        if (is_array($c['hits']['hits'])) {
                            foreach ($c['hits']['hits'] as $hkey => $hit) {
                                if (isset($hit['_source'])) {
                                    $node=  $hit['_source'];
                                    //$node['template'] = 'pppp';
                                    $ret[] = $node;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * Called by the ajax layer, specifically the ajax call of 'getHomepageInterestList'
     * this function is hooked into WP in the file of wp-content/themes/the-translator/includes/global-functions.php
     *
     * so, this is a PUBLIC ENTRY POINT for users, visitors, and search bots, etc
     *
     * It combines the tags that are supposed to normally be shown on the homepage, with any logged in user tag viewer preferences
     *  and then calls
     * @uses FreelinguistUnitDisplay::display_units and either returns the result directly, if this is called as normal function
     * OR
     * it prints out some json,with a json mime type in the header, and kills the process if this is called by ajax
     *
     *
     *
     * @return array
     */
    public static  function getHomepageInterestList() {

        /*
         * current-php-code 2020-Nov-19
         * internal-call
         * input-sanitized :
        */

        global $wpdb;

        /**
         * @var int[] $my_ids
         */
        $my_ids = [];

        try {
            if (is_user_logged_in()) {
                $my_id_map = [];
                $current_user_id = get_current_user_id();
                $my_tag_ids_raw = get_user_meta($current_user_id, '_user_default_tag_save', true);
                $my_tag_ids = will_get_one_dimensional_array_or_throw($my_tag_ids_raw, true, '_user_default_tag_save');
                foreach ($my_tag_ids as $my_tag_id) {
                    $my_id_map[$my_tag_id] = $my_tag_id;
                }
                $my_ids = array_keys($my_id_map);
            }
        } catch (Exception $e) {
            will_send_to_error_log("Issue getting homepage user set tags: ",$e->getMessage());
        }
        FreelinguistDebugFramework::note('user preference : my_ids',$my_ids);

        if (empty($my_ids)) {
            $wit_id_list = '-1';
        } else {
            $wit_id_list = implode(',',$my_ids);
        }

        $sql = "SELECT DISTINCT wit.ID as tag_id, wit.tag_name ,
              IF(wit.ID IN ($wit_id_list),1,0) as is_user_pref
            FROM wp_interest_tags wit 
            LEFT JOIN wp_homepage_interest whi ON wit.ID=whi.tag_id 
            WHERE whi.id IS NOT NULL OR wit.ID IN ($wit_id_list)
            ORDER BY whi.priority_number asc";

        $homepage_interest_ids = [];

        $results_hpi = $wpdb->get_results($sql,ARRAY_A);
        will_throw_on_wpdb_error($wpdb);
        foreach ($results_hpi as $row) {
            $homepage_interest_ids[] = $row;
        }

        FreelinguistDebugFramework::note('rows-> ',[
//        'sql' => $wpdb->last_query,
//        'results' => $results_hpi,
       // '$homepage_interest_ids' => $homepage_interest_ids,
            'count' => count($results_hpi)
    ]);

        $ret = FreelinguistUnitDisplay::display_units($homepage_interest_ids);
        if (defined('DOING_AJAX') && DOING_AJAX) {
            wp_send_json($ret);
            exit;
        }
        return $ret;
    }
}