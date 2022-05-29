<?php


/**
 * Class _FreelinguistIdType
 * @internal class for param passing
 */
class _FreelinguistIdType {
    public $id;
    public $type;
}
/**
 * Class FreelinguistUnitGenerator
 * This class generates twig templates for the top users and content
 * it is only class, used by the cron jobs, that makes the new units
 * @see FreelinguistCronTopUnitsGenerate
 *
 * and this stores the twig templates
 * in both the mysql and the elastic search
 *
 * This class also will clear out the old templates and recalculate which templates,
 * of the top content and users for each tag, by score, belong to the current top tags
 * So, there can be top users and content for Tag A, and if tag A is not on the top tag list
 * if the top tag list is tags (B,C,D); then the top content and users of tag A will not have templates
 *  made for them
 *
 * To help mark which templates should be made, this class
 * will update the is_top_tag column in the wp_display_unit_user_content table,
 * the only place that column is used is here
 *
 * Usage of this class:
 *  Clear the older templates:
 * @see FreelinguistUnitGenerator::clear_out_templates()
 *
 * Build the templates
 * @see FreelinguistUnitGenerator::generate_units()
 *
 * See the top tags
 * @see FreelinguistUnitGenerator::get_current_top_tags()
 *
 */
class FreelinguistUnitGenerator extends  FreelinguistDebugging {
    
    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;

    /*
     * used to control how many templates we make before creating sql update statements
    basically this controls the sql package size going to the server, a large number means more
    efficient writes, but larger packet size
    */
    const CACHE_THIS_MANY_TEMPATES_BEFORE_UPDATING_DB = 40;

    /**
     * sets the index in Elastic Search where the templates are stored
     * can rename this only after manually clearing the ES index
     * otherwise there will be an orphaned index in ES
     * but the code will be okay with any name here
     */
    const ELASTIC_INDEX_FOR_UNITS = 'unit';

    /**
     * In ES we have different data types, for the projects,contests,content and user indexes
     *   the data is called freelinguist, but this is a different format of data,
     *    so we have a new name for it
     *   can rename this after clearing the ES index first
     */
    const ELASTIC_UNIT_TYPE = 'unit';

    /**
     * Unit templates are from two different tables
     *  the top units represents the top-taggers
     *  then units can also be set by using the wp_homepage_interest_per_id table
     *  because units are mixed up in each tag, we combine both to be in the same places in the code
     *  This is a trade off, because this does produce clearer code outside the class,
     *  it increases the complexity inside this class
     *
     *  To tell the difference between the two types of units, we use the concept of a 'template type'
     *   and we have two types of templates right now: top and per
     *
     */
    const TYPE_TEMPLATE_TOP = 'top';
    const TYPE_TEMPLATE_PER = 'per';

    /**
     * Used to decide how many top tags there are
     * Normally , this is set in the freelinguist-limit-top-tags WP option
     * But, this is used as a fallback in case this is missing
     */
    const DEFAULT_TOP_TAG_LIMIT = 200;

    /**
     * @see FreelinguistCronTopUnitsGenerate
     * @return array
     */
    public static function get_current_top_tags() {
        global $wpdb;
        $limit_top_tags = (int)get_option('freelinguist-limit-top-tags', static::DEFAULT_TOP_TAG_LIMIT);
        $sql = "SELECT usage_count as da_count, ID as tag_id, tag_name
                FROM wp_interest_tags
                ORDER BY usage_count DESC
                LIMIT $limit_top_tags;";

        $res = $wpdb->get_results($sql);
        will_throw_on_wpdb_error($wpdb);
        $ret = [];
        foreach ($res as $row) {
            $tag_id = $row->tag_id;
            $tag_name = $row->tag_name;
            $tag_count = $row->da_count;
            $word = 'times';
            if ($row->da_count === 1) {$word = 'time';}
            $ret[] = "$tag_name [$tag_id] used $tag_count $word";
        }
        return $ret;
    }

    /**
     * FreelinguistCronTopUnitsClear calls this to clear out the older templates
     * All it does is null out the twig template storage columns in the DB
     * and the ES
     *
     * For the db, it nulls out
     * wp_homepage_interest_per_id
     *      when_html_updated,html_generated
     *
     * wp_display_unit_user_content:
     *   when_html_updated,html_generated
     *
     * deletes and rebuilds the index for units in the ES
     *
     * @param $log
     * @see FreelinguistCronTopUnitsClear
     */
    public static function clear_out_templates(&$log) {
        global $wpdb;

        $sql_to_clear_html_top_tags = "
            UPDATE wp_display_unit_user_content
            SET when_html_updated = NULL,html_generated = NULL  WHERE 1;
        ";

        $wpdb->query($sql_to_clear_html_top_tags);
        will_throw_on_wpdb_error($wpdb);
        will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,'Cleared html for top tags'),$log);

        $sql_to_clear_html_for_per_id =
            "
                            UPDATE wp_homepage_interest_per_id 
                            SET when_html_updated = NULL,html_generated = NULL  WHERE 1;
                            ";

        $wpdb->query($sql_to_clear_html_for_per_id);
        will_throw_on_wpdb_error($wpdb);
        will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,'Cleared html for per_id sql'),$log);


        try{
            $es = new FreelinguistElasticSearchHelper();
            $es->clear_cache(static::ELASTIC_INDEX_FOR_UNITS,$log);
            will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,'Cleared the unit index for ES '),$log);
        }catch(Exception $e){
            will_send_to_error_log_and_array($log,
                "Error clearing/creating index for ElasticSearch ",
                will_get_exception_string($e));
            return;
        }
    }

    /**
     * @internal method for redoing the top tags
     * sets the is_top_tag column in wp_display_unit_user_content
     * to reflect what the usage_count column in wp_interest_tags says are the top tags
     *
     * it is the is_top_tag being set that determines which templates are generated and stored in the db and es
     *   the  per units are not affected by this, as their tags and units are displayed regardless of the top tag status
     *
     * @param $log
     * @param bool $b_clear_html_in_table
     */
    protected static function recalculate_top_tags(&$log,$b_clear_html_in_table=true) {
        global $wpdb;
        $limit_top_tags = (int)get_option('freelinguist-limit-top-tags', static::DEFAULT_TOP_TAG_LIMIT);

        $clear_html_fragment = '';
        if ($b_clear_html_in_table) {
            $clear_html_fragment =  " ,when_html_updated = null, html_generated = NULL ";
        }
        $sql_to_clear_flags_for_top_tags = /** @lang text */
            "
                            UPDATE wp_display_unit_user_content 
                            SET is_top_tag = 0 $clear_html_fragment WHERE 1;
                            ";

            /*
             * code-notes changing SQL to use the wp_interest_tags.usage_count
             */
        $sql_to_reset_flags_for_top_tags = "
                        UPDATE wp_display_unit_user_content AS unit
                        INNER JOIN (
                                     SELECT usage_count as da_count, ID as tag_id, tag_name
                                     FROM wp_interest_tags
                                     ORDER BY usage_count DESC
                                     LIMIT $limit_top_tags
                        
                                   ) as toppers ON toppers.tag_id = unit.tag_id
                        SET is_top_tag = 1
                        WHERE 1;
                        ";
        $wpdb->query($sql_to_clear_flags_for_top_tags);
        will_throw_on_wpdb_error($wpdb);
        will_send_to_error_log_and_array($log,'recalculate_top_tags: Clearing flag and html sql',$wpdb->last_query);

        $wpdb->query($sql_to_reset_flags_for_top_tags);
        will_throw_on_wpdb_error($wpdb);

        will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,'recalculate_top_tags: New settings flags for top tags sql',$wpdb->last_query),$log);

        $sql_to_clear_html_for_per_id =
            "
                            UPDATE wp_homepage_interest_per_id 
                            SET when_html_updated = NULL,html_generated = NULL  WHERE 1;
                            ";

        $wpdb->query($sql_to_clear_html_for_per_id);
        will_throw_on_wpdb_error($wpdb);
        will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,'recalculate_top_tags: Cleared html for per_id sql',$wpdb->last_query),$log);
    }

    /**
     * @internal called by this class when generating new units
     * @see FreelinguistUnitGenerator::generate_units_for_type()
     *
     * @param array $log OUTREF
     * @param array $user_info OUTREF
     * @param int[] $array_ids IN  set to limit the information to just these user ids
     *
     * This gets all the user info needed in the templates to be generated for both the top tags and the per-id
     * Saves from calling the db multiple times later. Instead, we get all the user info just once, which speeds things up considerably
     *
     * The user information is returned by the reference to the $user_info array in the params
     *  and is just a hash of the user info needed, so $user_info is an array of arrays
     */
    protected static function get_top_and_per_user_info(&$log,&$user_info,$array_ids = []) {
        global $wpdb;

        if (empty($user_info)) {$user_info = [];}

        $extra_where = '';
        if (is_array($array_ids) && count($array_ids)) {
            $mapped_ids = [];
            foreach ($array_ids as $an_array_id) {
                $an_array_id = (int)$an_array_id;
                if ($an_array_id) {
                    $mapped_ids[$an_array_id] = $an_array_id;
                }
            }

            if (count($mapped_ids)) {
                $id_string_with_commas = implode(',',$mapped_ids);
                $extra_where = "AND look.user_id IN ($id_string_with_commas)";
            }
        }
        //get the ids of the users, so we can grab all the information at once
        /*
        User info needed
        wp_fl_user_data_lookup : user_id,rating_as_freelancer
           wp_user : user_nicename,display_name
           wp_tags_cache_job: group_concat (tag_id joined with wp_interest_tags->tag_name)
           wp_usermeta :
               wp_fl_user_data_lookup.user_id->wp_usermeta.meta_key = 'user_image'
               wp_fl_user_data_lookup.user_id->wp_usermeta.meta_key  =  'description'
               wp_fl_user_data_lookup.user_id->wp_usermeta.meta_key = 'user_residence_country'
               wp_fl_user_data_lookup.user_id->wp_usermeta.meta_key = 'user_hourly_rate'
        */
        $sql_user_info = /** @lang text */
            "
            SELECT
              look.user_id, look.rating_as_freelancer ,
              da_user.user_nicename, da_user.display_name,
              GROUP_CONCAT(DISTINCT itag.tag_name ORDER BY itag.tag_name ASC) as tag_name_list,
              GROUP_CONCAT(DISTINCT meta_image.meta_value SEPARATOR '||') as image,
              GROUP_CONCAT(DISTINCT meta_description.meta_value) as description,
              GROUP_CONCAT(DISTINCT meta_country.meta_value SEPARATOR '||') as country,
              GROUP_CONCAT(DISTINCT meta_rate.meta_value SEPARATOR '||') as hourly_rate,
              GROUP_CONCAT(DISTINCT limit_users.da_type SEPARATOR '||') as da_type,
              GROUP_CONCAT(DISTINCT limit_users.tag_id SEPARATOR '||') as da_tags,
              GROUP_CONCAT(DISTINCT limit_users.home_page_interest_id SEPARATOR '||') as da_interest_ids
            
            FROM wp_fl_user_data_lookup look
            
              INNER JOIN (
                SELECT
                  user_id,'top-tagger' as da_type,unit.tag_id as tag_id,NULL as home_page_interest_id 
                FROM wp_display_unit_user_content unit
                WHERE is_top_tag = 1
                UNION
                  SELECT DISTINCT wp_user_id AS user_id, 'per-id' AS da_type  ,
                   NULL as tag_id, wp_homepage_interest_per_id.homepage_interest_id as  home_page_interest_id
                  FROM wp_homepage_interest_per_id WHERE wp_user_id IS NOT NULL
                ) as limit_users ON limit_users.user_id = look.user_id
            
            INNER JOIN wp_users da_user ON da_user.ID = look.user_id
            LEFT JOIN wp_tags_cache_job tags ON tags.job_id = look.user_id AND tags.type = 4
            LEFT JOIN wp_interest_tags itag ON itag.ID = tags.tag_id
            LEFT JOIN wp_usermeta meta_image ON meta_image.user_id = look.user_id AND  meta_image.meta_key = 'user_image'
              LEFT JOIN wp_usermeta meta_description ON meta_description.user_id = look.user_id AND  meta_description.meta_key = 'description'
              LEFT JOIN wp_usermeta meta_country ON meta_country.user_id = look.user_id AND  meta_country.meta_key = 'user_residence_country'
              LEFT JOIN wp_usermeta meta_rate ON meta_rate.user_id = look.user_id AND  meta_rate.meta_key = 'user_hourly_rate'
            WHERE user_status = 0
            $extra_where
            GROUP BY look.user_id
            ORDER BY look.user_id
            ;
    ";

        $results_user_info = $wpdb->get_results($sql_user_info);
        will_throw_on_wpdb_error($wpdb);
        will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,'Generate Units: user info sql ',$wpdb->last_query),$log);
        $user_info = [];
        //fill in the user info now
        foreach ($results_user_info as $node) {

            $thing = [];

            $thing['tags'] = explode('||',$node->da_tags);
            $thing['per_ids'] = explode('||',$node->da_interest_ids);

            $thing['job_type'] = 'translator';

            $thing['primary_id'] = $node->user_id;

            $thing['href'] = site_url().'/user-account/?lang=en&profile_type=translator&user='.$node->user_nicename;
            $thing['nicename'] = $node->user_nicename;

            //code-notes [image-sizing]  get small image for the user
            $thing['image'] = FreelinguistSizeImages::get_url_from_relative_to_upload_directory($node->image,FreelinguistSizeImages::SMALL,true);


            $thing['eye_image'] = get_template_directory_uri().'/images/eye-see.png';

            $thing['title'] = $node->tag_name_list;

            $thing['description'] = $node->description;

            $thing['name'] = $node->display_name;

            $thing['view_or_rating'] = translater_rating( $node->user_id,17,'translator',
                false, $node->rating_as_freelancer);

            $country_id = explode('||',$node->country)[0];
            $thing['country'] = ($country_id) ?  get_countries()[$country_id] : '';

            $hourly_rate = explode('||',$node->hourly_rate)[0];
            $thing['rate_or_price_or_offer'] = ($hourly_rate ?  $hourly_rate. '/hour' : '');

            $thing['mag_image'] = get_template_directory_uri().'/images/mag.png';

            $thing['purchase_action'] = '';

            $user_info[] = $thing;
        } //end loop to fill in user information


    }

    /**
     * @param array $log
     * @param array $content_info OUTREF
     * @param int[] $array_ids IN  set to limit the information to just these user ids
     *
     * @internal called by this class when generating new units
     * @see FreelinguistUnitGenerator::generate_units_for_type()
     *
     * Very similar in logic to the @see FreelinguistUnitGenerator::get_top_and_per_user_info()
     *
     * This gets all the content info needed in the templates to be generated for both the top tags and the per-id
     * Saves from calling the db multiple times later. Instead, we get all the content info just once, which speeds things up considerably
     *
     * The content information is returned by the reference to the $content_info array in the params
     *  and is just a hash of the content info needed, so $content_info is an array of arrays
     */
    protected static function get_top_and_per_content_info(&$log,&$content_info,$array_ids = []) {
        global $wpdb;
        will_do_nothing([$log]);
        if (empty($content_info)) {$content_info = [];}


        $extra_where = '';
        if (is_array($array_ids) && count($array_ids)) {
            $mapped_ids = [];
            foreach ($array_ids as $an_array_id) {
                $an_array_id = (int)$an_array_id;
                if ($an_array_id) {
                    $mapped_ids[$an_array_id] = $an_array_id;
                }
            }

            if (count($mapped_ids)) {
                $id_string_with_commas = implode(',',$mapped_ids);
                $extra_where = "AND content.id IN ($id_string_with_commas)";
            }
        }


        //get the ids of the content, so we can grab all the information at once
        /*
         Content info needed
         wp_linguist_content: id, content_cover_image,content_title,content_summary,content_view,content_sale_type,content_amount
           wp_users :  wp_linguist_content.user_id->display_name
           wp_usermeta:
               wp_linguist_content.user_id->wp_usermeta.meta_key = 'user_residence_country'
        */
        $sql_content_info = /** @lang text */
            "
            SELECT
              content.id as content_id, content.content_cover_image,content.content_title,content.content_summary,
              content.content_view,content.content_sale_type,content.content_amount,
              da_user.display_name,
              GROUP_CONCAT(DISTINCT meta_country.meta_value SEPARATOR '||') as country,
              GROUP_CONCAT(DISTINCT limit_content.da_type SEPARATOR '||') as da_type,
              GROUP_CONCAT(DISTINCT limit_content.tag_id SEPARATOR '||') as da_tags,
              GROUP_CONCAT(DISTINCT limit_content.home_page_interest_id SEPARATOR '||') as da_interest_ids
            
            FROM wp_linguist_content content
            
              INNER JOIN (
                SELECT
                  content_id,
                  'top-tagger' as da_type,
                  unit.tag_id as tag_id, NULL as home_page_interest_id
                FROM wp_display_unit_user_content unit
                WHERE is_top_tag = 1
                UNION
                  SELECT DISTINCT job_id AS content_id, 'per-id' as da_type ,
                  null as tag_id, wp_homepage_interest_per_id.homepage_interest_id as  home_page_interest_id 
                  FROM wp_homepage_interest_per_id WHERE job_id IS NOT NULL
                ) as limit_content ON limit_content.content_id = content.id
            
              INNER JOIN wp_users da_user ON da_user.ID = content.user_id
              LEFT JOIN wp_usermeta meta_country ON meta_country.user_id = content.user_id AND  meta_country.meta_key = 'user_residence_country'
            WHERE content.user_id IS NOT NULL 
            $extra_where
            GROUP BY content.id
            ORDER BY content.id
            ;
    ";

        $results_content_info = $wpdb->get_results($sql_content_info);
        will_throw_on_wpdb_error($wpdb);
//        will_send_to_error_log_and_array($log,'Generate Units: Content Info sql,results',
//            [$wpdb->last_query,$results_content_info]);


        //fill in the user info now
        foreach ($results_content_info as $node) {
            $thing = [];

            $thing['tags'] = explode('||',$node->da_tags);
            $thing['per_ids'] = explode('||',$node->da_interest_ids);

            $thing['job_type'] = 'content';

            $thing['primary_id'] = $node->content_id;

            $thing['href'] = site_url().'/content/?lang=en&mode=view&content_id='.FreelinguistContentHelper::encode_id($node->content_id);

            //code-notes [image-sizing]  content getting small size for unit
            $thing['image'] = FreelinguistSizeImages::get_url_from_relative_to_upload_directory(
               $node->content_cover_image,FreelinguistSizeImages::SMALL,true);
            $thing['eye_image'] = get_template_directory_uri().'/images/eye-see.png';

            $thing['title'] = $node->content_title;

            $thing['description'] = $node->content_summary;

            $thing['name'] = $node->display_name;

            $thing['view_or_rating'] =($node->content_view ?  $node->content_view. '/Views' : '');

            $country_id = explode('||',$node->country)[0];
            $thing['country'] = ($country_id) ?  get_countries()[$country_id] : '';

            if($node->content_sale_type =='Fixed'){ $thing['rate_or_price_or_offer'] ='$' . $node->content_amount; }
            else if($node->content_sale_type =='Offer'){ $thing['rate_or_price_or_offer'] = 'Best Offer'; }
            else if($node->content_sale_type =='Free'){ $thing['rate_or_price_or_offer'] = '$0'; }

            $thing['mag_image'] = get_template_directory_uri().'/images/mag.png';

            $thing['purchase_action'] = 'buy';

            $content_info[] = $thing;
        } //end loop to fill in content information
    }

    /**
     * @internal method of this class
     *
     * @param array $node
     * @return string
     *
     * called by @see FreelinguistUnitGenerator::generate_units_for_type()
     *
     * includes the template generator file with the given info,
     *  the information is passed by the global variable $unit_item_info
     *
     * and converts its standard output to a string,
     * then returns the string
     *  Here, the string is the template
     *
     * Basically, this is a thin wrapper
     */
    public static function generate_template($node) {
        global $unit_item_info;

        $unit_item_info = $node;
        ob_start();
        include(ABSPATH . '/wp-content/themes/the-translator/includes/units/twig-templates/content-freelancer-unit-template.twig.php');
        $template = ob_get_clean();
        return $template;
    }


    /**
     * @internal
     * called by @see FreelinguistUnitGenerator::generate_units_for_type
     * A one size fits all method for storing templates to the db, which is why its called 4 times, for each combination
     *
     * Makes SQL for inserting or updating templates (at this time only update)
     *
     * Called after the template is created
     *
     * @param array $log
     * @param $results_users_or_content
     * @param $b_is_user
     * @param $b_is_to_top_tags
     */
    protected static function save_templates_to_table(&$log,$results_users_or_content, $b_is_user, $b_is_to_top_tags) {
        global $wpdb;
        if (empty($log)) {$log = [];}
        if ($b_is_to_top_tags) {
            $name_of_thing_column = 'content_id';
            if ($b_is_user) {$name_of_thing_column = 'user_id';}
            $table_name = 'wp_display_unit_user_content';
            $name_of_unique_column = 'tag_id';
            $name_of_array_key = 'tags';
        } else {
            $name_of_thing_column = 'job_id';
            if ($b_is_user) {$name_of_thing_column = 'wp_user_id';}
            $name_of_unique_column = 'homepage_interest_id';
            $table_name = 'wp_homepage_interest_per_id';
            $name_of_array_key = 'per_ids';
        }



        //write sql and send it
        $out_sql_start = /** @lang text */
            "INSERT INTO $table_name($name_of_unique_column,$name_of_thing_column,when_html_updated,html_generated) VALUES \n ";


        $out_sql_parts = [];
        foreach ($results_users_or_content as $thing_id => $info) {
            $tags = $info[$name_of_array_key];
            $text_template = $info['template'];
            $template_escaped = esc_sql($text_template);
            $has_tags = true;
            if (empty($tags)) {$has_tags = false;}
            if ((count($tags) === 1) && (empty($tags[0]))) {$has_tags = false;}
            if ($has_tags) { //only include data that have non empty value in array key column
                foreach ($tags as $tag_index=> $tag_id) {
                    $sql_part = "( $tag_id, $thing_id, NOW(), '$template_escaped')";
                    $out_sql_parts[]= $sql_part;
                }
            }
        }
        if (!empty($out_sql_parts)) {
            $values = implode(",\n",$out_sql_parts);

            //this will always update due to unique key and the fact the tags are only filled for things in this row
            $sql = $out_sql_start . $values . "
                        ON DUPLICATE KEY UPDATE
                            when_html_updated=VALUES(when_html_updated),
                             html_generated=VALUES(html_generated)
                    ";

            will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,'SQL to insert units ', $sql),$log);
            $wpdb->query($sql);
            will_throw_on_wpdb_error($wpdb);
        }
    }

    /**
     * @internal method to this class
     * called by @see FreelinguistUnitGenerator::generate_units()
     *
     * @param array, $log OUTREF
     * @param $b_user , tells if we are making units for content or units
     * @param int[] $limit_to_these_ids_only optionally just generate for these ids
     *
     * The workhorse for this class, is where everything comes together and makes units
     * it can make units for either users or content, which is why its has the param of $b_user to let it know
     *   so, that is why its called twice by its parent, once for the units and then once for content
     *
     * Once it decides what kind of templates its making, it will make all the templates for that type (user or content)
     *
     * The order of logic here is:
     *   gets the information needed to make the templates. It calls one of the sister functions of
     *     @uses FreelinguistUnitGenerator::get_top_and_per_user_info()
     *     @uses FreelinguistUnitGenerator::get_top_and_per_content_info()
     *
     *   for each of the information gathered, a content or user, calls for the template to be created and it stores that in a string
     *     @uses FreelinguistUnitGenerator::generate_template()
     *
     *   Then, it saves up the generated templates until there are enough to send to the db as a group update for several rows
     *      while it saves them in two different arrays, one going to the per-id templates, and one going to the top-tags
     *    @uses FreelinguistUnitGenerator::save_templates_to_table()
     *
     * Please note that multiple copies of a template will be stored at each tag the template is associated with
     *
     * ES is not called here, as it will get its templates from what is being stored in the db here
     *
     */
    protected static function generate_units_for_type(&$log,$b_user ,$limit_to_these_ids_only=[]) {
        //build up the array of unit information
        if (empty($log)) {$log = [];}

        $dese_nodes = [];
        if ($b_user) {
            will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"generate_units_for_type getting user info", $limit_to_these_ids_only),$log);
            static::get_top_and_per_user_info($log,$dese_nodes,$limit_to_these_ids_only);
        } else {
           will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"generate_units_for_type getting content info", $limit_to_these_ids_only),$log);
            static::get_top_and_per_content_info($log,$dese_nodes,$limit_to_these_ids_only);
        }

        $results_top_tags_content = [];
        $results_per_id_content = [];
        foreach ($dese_nodes as $node) {
            $template = static::generate_template($node);

            $has_tags = true;
            if (empty($node['tags'])) {$has_tags = false;}
            if ((count($node['tags']) === 1) && (empty($node['tags'][0]))) {$has_tags = false;}
            if ($has_tags) {
                $results_top_tags_content[$node['primary_id']] = ['template'=>$template,'tags'=>$node['tags'] ];
            }

            $has_per = true;
            if (empty($node['per_ids'])) {$has_per = false;}
            if ((count($node['per_ids']) === 1) && (empty($node['per_ids'][0]))) {$has_per = false;}
            if ($has_per) {
                $results_per_id_content[$node['primary_id']] = ['template'=>$template,'per_ids'=>$node['per_ids'] ];
            }


            if (count($results_top_tags_content) >= static::CACHE_THIS_MANY_TEMPATES_BEFORE_UPDATING_DB) {
                static::save_templates_to_table($log,$results_top_tags_content,$b_user,true);
                $results_top_tags_content = [];
            }

            if (count($results_per_id_content) >= static::CACHE_THIS_MANY_TEMPATES_BEFORE_UPDATING_DB) {
                static::save_templates_to_table($log,$results_per_id_content,$b_user,false);
                $results_per_id_content = [];
            }
        }
        if (!empty($results_top_tags_content)) {
            static::save_templates_to_table($log,$results_top_tags_content,$b_user,true);
            /** @noinspection PhpUnusedLocalVariableInspection */
            $results_top_tags_content = [];
        }

        if (!empty($results_per_id_content)) {
            static::save_templates_to_table($log,$results_per_id_content,$b_user,false);
            /** @noinspection PhpUnusedLocalVariableInspection */
            $results_per_id_content = [];
        }
    }

    /**
     * gets data for changing or adding units in the ES
     * @param array $log
     * @param int[] $limiting_user_ids
     * @param int[] $limiting_content_ids
     *
     * @return object[]
     */
    protected static function get_setup_info_for_ids(&$log,$limiting_user_ids,$limiting_content_ids) {
        global $wpdb;

        if (empty($log)) {$log = [];}

        $user_id_string_with_commas = '';
        $content_id_string_with_commas = '';

        if (is_array($limiting_user_ids) && count($limiting_user_ids)) {
            $mapped_ids = [];
            foreach ($limiting_user_ids as $an_array_id) {
                $an_array_id = (int)$an_array_id;
                if ($an_array_id) {
                    $mapped_ids[$an_array_id] = $an_array_id;
                }
            }

            if (count($mapped_ids)) {
                $user_id_string_with_commas = implode(',',$mapped_ids);
            }
        }

        if (is_array($limiting_content_ids) && count($limiting_content_ids)) {
            $mapped_ids = [];
            foreach ($limiting_content_ids as $an_array_id) {
                $an_array_id = (int)$an_array_id;
                if ($an_array_id) {
                    $mapped_ids[$an_array_id] = $an_array_id;
                }
            }

            if (count($mapped_ids)) {
                $content_id_string_with_commas = implode(',',$mapped_ids);
            }
        }

        // top condition => AND (content_id in (6155,152070) OR user_id IN (156335,156600))
        $top_condition = '';

        //per condition = AND (per.job_id in (6155,152070) OR per.wp_user_id IN (156335,156600))
        $per_condition = '';

        if ($user_id_string_with_commas && $content_id_string_with_commas ) {
            $top_condition = "AND (content_id in ($content_id_string_with_commas) OR user_id IN ($user_id_string_with_commas))";
            $per_condition = "AND (per.job_id in ($content_id_string_with_commas) OR per.wp_user_id IN ($user_id_string_with_commas))";
        }elseif ($user_id_string_with_commas) {
            $top_condition = "AND ( user_id IN ($user_id_string_with_commas))";
            $per_condition = "AND ( per.wp_user_id IN ($user_id_string_with_commas))";
        } elseif ($content_id_string_with_commas) {
            $top_condition = "AND (content_id in ($content_id_string_with_commas) )";
            $per_condition = "AND (per.job_id in ($content_id_string_with_commas) )";
        }



        //get all the templates
        $sql_to_get_templates = /** @lang text */
            " SELECT 
            unit.id as pk,
            unit.user_id,unit.content_id,
            unit.html_generated,UNIX_TIMESTAMP(unit.when_html_updated) as when_ts,
            wit.tag_name,'".static::TYPE_TEMPLATE_TOP."' as template_type,
            wit.ID as tag_id,
            IF(i.priority_number IS NOT NULL,i.priority_number,0) as priority_number,
            IF(i.is_title_hidden IS NOT NULL,i.is_title_hidden,0) as is_title_hidden
            
            FROM  wp_display_unit_user_content unit
            INNER JOIN wp_interest_tags wit on unit.tag_id = wit.ID
            LEFT JOIN wp_homepage_interest i ON i.tag_id = wit.ID
            WHERE when_html_updated IS NOT NULL 
                AND unit.is_top_tag = 1
                $top_condition
        UNION ALL 
            SELECT 
            per.id as pk,
            per.wp_user_id as user_id,per.job_id as content_id,
            per.html_generated,UNIX_TIMESTAMP(per.when_html_updated) as when_ts,
            wit.tag_name,'".static::TYPE_TEMPLATE_PER."' as template_type,
            wit.ID as tag_id,
            i.priority_number,i.is_title_hidden
            
            FROM  wp_homepage_interest_per_id per
            INNER JOIN wp_homepage_interest i on per.homepage_interest_id = i.id
            INNER JOIN wp_interest_tags wit on i.tag_id = wit.ID
            WHERE per.when_html_updated IS NOT NULL
                AND (per.wp_user_id IS NOT NULL OR per.job_id IS NOT NULL )
                $per_condition
            ORDER BY tag_name,user_id,content_id
        ";

        $results_templates = $wpdb->get_results($sql_to_get_templates);
        will_throw_on_wpdb_error($wpdb);
        will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,'SQL to get all the templates '. static::ELASTIC_INDEX_FOR_UNITS . ' for elasticsearch',
            $wpdb->last_query),$log);


        foreach ($results_templates as &$row) {
            // figure out user or content as the type, and then the user or content id as the type_id
            // id should be: template_type - tag_name - type - type_id

            if ($row->user_id) {
                $the_type = 'user';
                $the_type_id = $row->user_id;
            } elseif ($row->content_id) {
                $the_type = 'content';
                $the_type_id = $row->content_id;
            } else {
                 will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,'get_setup_info_for_ids: Neither user or content? ',
                    $row),$log);
                continue;
            }

            if (!$row->template_type) {
                will_send_to_error_log_and_array($log,
                    'get_setup_info_for_ids: No Template type set ',
                    $row);
                throw new LogicException("No template type set!");
            }

            if (!$row->tag_name) {
                will_send_to_error_log_and_array($log,
                    'get_setup_info_for_ids: No tag name set! ',
                    $row);
                throw new LogicException("No tag name set!");
            }
            //template_type - tag_name - type - type_id

            /**
             * @example top-test-user-156512
             *              curl -XGET 127.0.0.1:9200/unit/unit/top-test-user-156512
             *
             * @example top-test-content-1
             *              curl -XGET 127.0.0.1:9200/unit/unit/top-test-content-1
             *
             * @example per-test-content-3
             *              curl -XGET 127.0.0.1:9200/unit/unit/per-test-content-3
             *
             * @example per-test-content-3
             *              curl -XGET 127.0.0.1:9200/unit/unit/per-test-user-3759
             */
            $da_es_id = $row->template_type . '-' . $row->tag_name . '-' . $the_type . '-' . $the_type_id;
            $row->es_id = $da_es_id;
            $row->es_type = $the_type;
            $row->es_type_id = $the_type_id;
        }

        return $results_templates;
    }

    /**
     *

     * Adds the existing templates to the ES index for units
     * It basically copies the current templates that are being used, in both the top tags and the per-id
     *   with enough information to give it context, and be able to run the template,
     *   without having to call the db to get extra information for each unit
     *
     * It clears the ES index @see FreelinguistUnitGenerator::ELASTIC_INDEX_FOR_UNITS
     *
     * then adds in all the records, batching the mass update of ES to every 1,000 records (hard coded limit)
     *
     * the es record has these fields:
     *
     'pk' => the id of the table which holds the template
    'user_id' =>  if this is a user template, the user id is here, otherwise its null
    'content_id' => if this is a content template, the content id is here, otherwise its null
    'unit_type' =>  will be 'user' or 'content'
    'type_id' => will be the id of the user (the ID in wp_users) or the id of the content (the id in wp_linguist_content)
    'template_type' => is this a top unit, or a per id unit ?
          @see FreelinguistUnitGenerator::TYPE_TEMPLATE_PER
          @see FreelinguistUnitGenerator::TYPE_TEMPLATE_TOP
    'tag_name' => the english name of the tag, a template can be repeated for each tag associated with it
    'tag_id' => the id of the tag
    'when_template_made_ts' => unix timestamp when this template was generated, that will be when it was stored in the db after it was created
    'template' => the twig template
    'recent_ts' => the unix time this was added to the ES
    'is_tag_title_hidden' => a per-id feature, tells if the tag name needs to be hidden on the homepage
    'priority_number' => a per-id feature, helps with ordering the units
     *
     *
     * @param array $log
     * @param int[] $limiting_user_ids
     * @param int[] $limiting_content_ids
     *
     * @throws
     */
    protected static function rebuild_elastic_search_unit_index(&$log,$limiting_user_ids,$limiting_content_ids) {
        //code-notes implementing limits for es rebuilding by setting conditional where for user and content
        global $wpdb;

        if (empty($log)) {$log = [];}
        $es = new FreelinguistElasticSearchHelper();

        //code-notes if no ids passed, then clear all the units from ES, else find the indexes and update per index and skip this step

        if (empty($limiting_user_ids) && empty($limiting_content_ids)) {
            try{
                $es->clear_cache(static::ELASTIC_INDEX_FOR_UNITS,$log);
            }catch(Exception $e){
                will_send_to_error_log_and_array($log,
                    "Error clearing all/creating index for ElasticSearch ",
                    will_get_exception_string($e));
                return;
            }
        }
        //else just update/add per below

        $results_templates = static::get_setup_info_for_ids($log,$limiting_user_ids,$limiting_content_ids);


        $params = [];
        $params['body'] = [];
        $count = 0;
        foreach ($results_templates as $row) {
            $count ++;
            // figure out user or content as the type, and then the user or content id as the type_id
            // id should be: template_type - tag_name - type - type_id


            $params['body'][] = [
                'index' => [
                    '_index' => static::ELASTIC_INDEX_FOR_UNITS,
                    '_type' => static::ELASTIC_UNIT_TYPE,
                    '_id' => $row->es_id,
                ]
            ];


            $params['body'][] = [
                'pk' => (int)$row->pk,
                'user_id' => (int)$row->user_id,
                'content_id' => (int)$row->content_id,
                'unit_type' => $row->es_type,
                'type_id' => (int)$row->es_type_id,
                'template_type' => $row->template_type,
                'tag_name' => $row->tag_name,
                'tag_id' => (int)$row->tag_id,
                'when_template_made_ts' => (int)$row->when_ts,
                'template' => $row->html_generated,
                'recent_ts' => time(),
                'is_tag_title_hidden' => (int)$row->is_title_hidden,
                'priority_number' => (int)$row->priority_number
            ];

            // Every 1000 documents stop and send the bulk request
            if (count($params['body']) > 999) {
                will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"about to send 1000 to ES",$params),$log);
                $es->bulk_add($params,$log);
                // erase the old bulk request
                $params = ['body' => []];

                // unset the bulk response when you are done to save memory
                will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"Current total [$count] ".'Sent a thousand docs to the index '. static::ELASTIC_INDEX_FOR_UNITS . ' for elasticsearch',
                    $wpdb->last_query),$log);
            }
        } //end for each
        // Send the last batch if it exists
        if (!empty($params['body'])) {
            $how_many = count($params['body']);
            FreelinguistDebugFramework::note("about to send Rest [$how_many] to ES ",$params);
            $es->bulk_add($params);
            will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"Current total [$count] ".'Sent last docs to the index '. static::ELASTIC_INDEX_FOR_UNITS . ' for elasticsearch',
                $wpdb->last_query),$log);
        }
    }

    /**
     * Removes specific units from the complied units
     * Does not change any sql, just ES units
     * Because deleting these users or contents will automatically remove them from everything else not ES
     *
     * @param $log
     * @param int $homepage_interest_id <p>
     *      if this is set, then will only remove the per-id units from es, for this tag associated with it, and with these ids
     *      if this is not set, then will remove all units from ES with these ids
     * </p>
     *
     * @param {_FreelinguistIdType[]} $limit_to_these_types_and_ids_only <p>
     *      if this is empty, and $homepage_interest_id is set, then will fill in the user and content ids
     * </p>
     *
     * @throws
     */
    public static function remove_compiled_units_from_es_cache(&$log,$homepage_interest_id,$limit_to_these_types_and_ids_only) {
        global $wpdb;
        if (empty($log)) {$log = [];}
        $homepage_interest_id = (int)$homepage_interest_id;
        $homepage_interest_tag_id = null;
        if ($homepage_interest_id) {
            $sql_statement = "SELECT tag_id FROM wp_homepage_interest WHERE id = $homepage_interest_id";
            $res_tag = $wpdb->get_results($sql_statement);
            if (count($res_tag)) { $homepage_interest_tag_id = (int)$res_tag[0]->tag_id;}
            else {
                will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"Homepage Interest ID does not have found tag. Id was $homepage_interest_id"),$log);
            }
        }

        $limiting_user_ids = [];
        $limiting_content_ids = [];
        if ($homepage_interest_id && empty($limit_to_these_types_and_ids_only)) {
            //get all the ids in the per list for this
            $limit_to_these_types_and_ids_only = [];
            $sql_statement = "SELECT wp_user_id,job_id FROM wp_homepage_interest_per_id where homepage_interest_id = $homepage_interest_id;";
            $res_ids = $wpdb->get_results($sql_statement);
            will_throw_on_wpdb_error($wpdb);
            foreach ($res_ids as $id_row) {
                $user_id = (int)$id_row->wp_user_id;
                $content_id = (int)$id_row->wp_user_id;
                if ($user_id) {$limiting_user_ids[] = $user_id;}
                if ($content_id) {$limiting_content_ids[] = $content_id;}
            }
        }

        if (!empty($limit_to_these_types_and_ids_only) && is_array($limit_to_these_types_and_ids_only)) {
            //code-notes filling in $limiting_user_ids and $limiting_content_ids from $limit_to_these_types_and_ids_only
            foreach ($limit_to_these_types_and_ids_only as $node) {
                $what = (int)$node->id;
                if (!$what) {
                    will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"empty ID in remove_units",$node),$log);
                    continue;
                }

                switch ($node->type) {
                    case 'user': {
                        $limiting_user_ids[$what] = $what;
                        break;
                    }
                    case 'content': {
                        $limiting_content_ids[$what] = $what;
                        break;
                    }
                    default: {
                        will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"Unknown type in remove_units: '{$node->type}'",$node),$log);
                    }
                }//end switch
            } //end foreach
            $limiting_content_ids = array_keys($limiting_content_ids);
            $limiting_user_ids = array_keys($limiting_user_ids);
        } //if not empty ids

         will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"remove_compiled_units_from_es_cache ids",[
            '$limiting_content_ids' => $limiting_content_ids,
            '$limiting_user_ids' => $limiting_user_ids
        ]),$log);

        //if still no ids, then log warning and exit, because we do not want to clear the entire cache
        if (empty($limiting_content_ids) && empty($limiting_user_ids)) {
            if ($homepage_interest_id) {
                 will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"no units or cotent to remove, using interest id of $homepage_interest_id"),$log);
            } else {
                 will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"Cannot run remove_units without something in the user or content list "),$log);
            }

            return;

        }


        $es = new FreelinguistElasticSearchHelper();
        $result_templates = static::get_setup_info_for_ids($log,$limiting_user_ids,$limiting_content_ids);

        //if the same thing is in both top and per, will show up twice , so ignored any top things
        foreach ($result_templates as $row) {
            $index_to_remove = $row->es_id;
            if ($row->template_type === 'top') {
                if ($homepage_interest_tag_id) {continue;} //not trimming top
                //only clearing the ES cache, not removing any html
            } elseif ($row->template_type === 'per') {
                if ($homepage_interest_tag_id) {
                    if ($homepage_interest_tag_id !== intval($row->tag_id)) { continue;} //not removing per id on differently scoped tag
                }
            } else {
               will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"remove units does not recognize the template type",$row),$log);
                continue;
            }

            $es->delete_id_inside_index(
                static::ELASTIC_UNIT_TYPE,
                static::ELASTIC_INDEX_FOR_UNITS,
                $index_to_remove,
                $log
            );
            //code-notes cleared the index from es
        }

        will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"remove_compiled_units logs from function itself"),$log);


    }

    /**
     * @param array $log
     * @param int[] $limiting_user_ids  if not empty then will generate only these ids
     * @param int[] $limiting_content_ids if not empty then will generate only these ids
     * @param bool $b_limiting_only , default true, if true then if both id arrays are empty do not do anything but warn
     * The main gateway to generating top tags, this is one of three public methods in the class
     *  Most of the functionality in this class in hidden from the outside world view by protected status
     *  the cron job FreelinguistCronTopUnitsGenerate will call this to make new top unit templates
     * @see FreelinguistCronTopUnitsGenerate
     *
     * Logic:
     * @uses FreelinguistUnitGenerator::recalculate_top_tags() to mark the templates is should make
     * @uses FreelinguistUnitGenerator::generate_units_for_type() twice ,
     *    to make the user units and the content unit templates and store them in the db
     *
     * @uses FreelinguistUnitGenerator::rebuild_elastic_search_unit_index() to copy those templates to the ES index
     */
    public static function generate_units(&$log,$limiting_user_ids=[],$limiting_content_ids=[],$b_limiting_only = true)
    {

        try {
            if (empty($log)) {
                $log = [];
            }

            will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"generate_units given ids", [
                '$limiting_content_ids' => $limiting_content_ids,
                '$limiting_user_ids' => $limiting_user_ids
            ]),$log);

            if (empty($limiting_user_ids)) {
                $limiting_user_ids = [];
            }

            if (empty($limiting_content_ids)) {
                $limiting_content_ids = [];
            }

            if (!is_array($limiting_content_ids) || !is_array($limiting_user_ids)) {
                will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"generate_units has non array, non empty params"),$log);
                will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"generate_units has non array, non empty params"),$log);
                return;
            }
            //id arrays must be arrays now

            $cleaned_user_ids = [];
            $cleaned_content_ids = [];

            if (is_array($limiting_user_ids) && count($limiting_user_ids)) {
                $mapped_ids = [];
                foreach ($limiting_user_ids as $an_array_id) {
                    $an_array_id = (int)$an_array_id;
                    if ($an_array_id) {
                        $mapped_ids[$an_array_id] = $an_array_id;
                    }
                }

                if (count($mapped_ids)) {
                    $cleaned_user_ids = array_keys($mapped_ids);
                }
            }


            if (is_array($limiting_content_ids) && count($limiting_content_ids)) {
                $mapped_ids = [];
                foreach ($limiting_content_ids as $an_array_id) {
                    $an_array_id = (int)$an_array_id;
                    if ($an_array_id) {
                        $mapped_ids[$an_array_id] = $an_array_id;
                    }
                }

                if (count($mapped_ids)) {
                    $cleaned_content_ids = array_keys($mapped_ids);
                }
            }

             will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"generate_units cleaned ids", [
                '$cleaned_content_ids' => $cleaned_content_ids,
                '$cleaned_user_ids' => $cleaned_user_ids
            ]),$log);

            if (count($cleaned_user_ids) !== count($limiting_user_ids)) {
                will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"generate_units has mismatch for cleaned and dirty user id array"),$log);
                return;
            }

            if (count($cleaned_content_ids) !== count($limiting_content_ids)) {
                will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"generate_units has mismatch for cleaned and dirty content id array"),$log);
                return;
            }

            if (empty($cleaned_content_ids) && empty($cleaned_user_ids) && $b_limiting_only) {
                will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"generate_units has empty id arrays while having flag necessitating them"),$log);
                return;
            }


            $b_flag_full_process = true;

            if (!empty($limiting_user_ids) || !empty($limiting_content_ids)) {
                $b_flag_full_process = false;
            }

            will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"generate_units $b_flag_full_process", $b_flag_full_process),$log);
            if ($b_flag_full_process) {
                /*
                Recalculate the top tags
                 */
                static::recalculate_top_tags($log, true);
            }


            $b_generate_content_units = false;
            $b_generate_user_units = false;
            if ($b_flag_full_process) {
                $b_generate_content_units = true;
                $b_generate_user_units = true;
            } else {
                if (!empty($limiting_user_ids)) {
                    $b_generate_user_units = true;
                }

                if (!empty($limiting_content_ids)) {
                    $b_generate_content_units = true;
                }
            }


            /*
             * Add in the new things: first for user, then for content
             */

            if ($b_generate_user_units) {
                static::generate_units_for_type($log,true, $cleaned_user_ids);
            }

            if ($b_generate_content_units) {
                static::generate_units_for_type($log,false, $cleaned_content_ids);
            }


            static::rebuild_elastic_search_unit_index($log, $cleaned_user_ids, $cleaned_content_ids);


        } finally {
            will_do_nothing('for debugging');
//            will_add_to_array_if_not_empty(static::log(static::LOG_DEBUG,"generate_units logs from function itself", [
//                'trace' => will_get_backtrace(), //contains the log
//            ]),$log);
        }

    } //end function to generate units



} // end class



