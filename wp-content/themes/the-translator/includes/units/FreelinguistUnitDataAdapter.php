<?php

/*
    * user and tag info are only a little different!
    * ----------
    * int[] tags
    * //skip int[] per_ids, always empty for dynamic creation , set in constructor
    * //string job_type  : translator|content  , set in constructor
    * int primary_id
    * string href
    * string nicename
    * string image
    * //skip string eye_image, its part of the basic html, constant get_template_directory_uri().'/images/eye-see.png'; , set in constructor
    * string title
    * string description
    * string name
    * int view_or_rating
    * string country
    * string rate_or_price_or_offer
    * //skip string mag_image, its also part of the basic html of the unit, constant get_template_directory_uri().'/images/mag.png'; , set in constructor
    * //skip string purchase_action (empty for user, 'buy' for content) , set in constructor
    */

class FreelinguistUnitDataAdapter extends  FreelinguistDebugging {

    //inherited from debugging and need to be overwritten
    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;


    const TYPE_USER = 'translator';
    const TYPE_CONTENT = 'content';
    const ACTION_USER = '';
    const ACTION_CONTENT = 'buy';
    static $dat_eye_image_url = '';
    static $dat_mag_image_url = '';


    /**
     * @var int[] $tags
     */
    public $tags = [];

    /**
     * @var string[] $tags
     */
    public $tag_names = [];

    /**
     * @var int[] $per_ids
     * always empty for dynamic creation , set in constructor
     */
    public $per_ids = [];

    /**
     * @var string
     * translator|content  , set in constructor
     */
    public $job_type = '';

    /**
     * @var int
     */
    public $primary_id  = 0;

    /**
     * @var string
     */
    public $href  = '';

    /**
     * @var string
     */
    public $nicename  = '';

    /**
     * @var string $image
     */
    public $image  = '';
    /**
     * @var string $eye_image
     * its part of the basic html, constant get_template_directory_uri().'/images/eye-see.png'; , set in constructor
     */
    public $eye_image  = '';

    /**
     * @var string $title
     */
    public $title  = '';

    /**
     * @var string $description
     */
    public $description  = '';

    /**
     * @var string $name
     */
    public $name  = '';

    /**
     * @var int $view_or_rating
     */
    public $view_or_rating  = 0;

    /**
     * @var string $country
     */
    public $country  = '';

    /**
     * @var string $rate_or_price_or_offer
     */
    public $rate_or_price_or_offer  = '';

    /**
     * @var string $mag_image
     * its also part of the basic html of the unit, constant get_template_directory_uri().'/images/mag.png'; , set in constructor
     */
    public $mag_image  = '';

    /**
     * @var string $purchase_action
     * (empty for user, 'buy' for content) , set in constructor
     */
    public $purchase_action  = '';

    /**
     * @var string $html_generated
     * used internally , not to be messed with here
     */
    public $html_generated = '';

    public function to_array() {return (array)$this;}

    public function __construct(string $what_type_am_i)
    {
        if (empty( static::$dat_eye_image_url)) {
            static::$dat_eye_image_url = get_template_directory_uri().'/images/eye-see.png';
            static::$dat_mag_image_url = get_template_directory_uri().'/images/mag.png';
        }

        $this->eye_image = static::$dat_eye_image_url;
        $this->mag_image = static::$dat_mag_image_url;
        $this->per_ids = [];
        $this->tags = [];
        $this->tag_names = [];

        switch ($what_type_am_i) {
            case static::TYPE_USER: {
                $this->job_type = static::TYPE_USER;
                $this->purchase_action = static::ACTION_USER;
                break;
            }
            case static::TYPE_CONTENT: {
                $this->job_type = static::TYPE_CONTENT;
                $this->purchase_action = static::ACTION_CONTENT;
                break;
            }
            default: {
                throw new InvalidArgumentException("Whoa there! You picked type $what_type_am_i which is unknown to FreelinguistUnitDataAdapter");
            }
        }
    }

    /**
     * Given the ids , will return an html string in random order
     * @param int[] $user_ids
     * @param int[] $content_ids
     * @return string[]
     */
    public static function generate_units_from_adapter($user_ids,$content_ids) {
        $ret = [];
        static::log(static::LOG_DEBUG,'Content Ids supplied ',$content_ids);
        static::log(static::LOG_DEBUG,'User ids given ',$user_ids);

        $content_info_array = static::get_content_info($log,$content_ids);
        static::log(static::LOG_DEBUG,'Content Info Array ',$content_info_array);
        $user_info_array = static::get_user_info($log,$user_ids);
        static::log(static::LOG_DEBUG,'User Info Array ',$user_info_array);

        /**
         * @var FreelinguistUnitDataAdapter[] $info_array
         */
        $info_array = array_merge($content_info_array,$user_info_array);

        $user_twig_vars = FreelinguistUnitDisplay::init_template_user_vars(null,true);
        foreach ($info_array as $info) {
            $template = FreelinguistUnitGenerator::generate_template($info->to_array());
            $info->html_generated = $template;
            $unit = FreelinguistUnitDisplay::make_single_unit($info,$user_twig_vars);
            ob_start();
            $unit->output_template(); //printed to standard output, to catch it into a variable
            $ret[] = ob_get_clean();

        }

        $b_do_the_hussle = shuffle($ret);
        if (!$b_do_the_hussle) {
            will_send_to_error_log("Failed to randomize the units",[],false,false,false,true);
        }
        static::log(static::LOG_DEBUG,'Dis count??!!! ',count($ret));
        return $ret;
    }

    /**
     * Weeds out 0 or non numeric values, takes care of that pesky null
     * @param mixed $an_int_array_from_dubious_origin
     * @return int[]
     */
    protected static function clean_mah_array( $an_int_array_from_dubious_origin) {
        if (empty($an_int_array_from_dubious_origin)) {return [];}
        if (!is_array($an_int_array_from_dubious_origin)) {
            will_send_to_error_log('Not an array',$an_int_array_from_dubious_origin,false,false,false,true);
            return [];
        }
        $ret = [];
        foreach ($an_int_array_from_dubious_origin as $lol) {
            $maybe = (int)$lol;
            if ($maybe) {
                $ret[] = $maybe;
            }
        }

        return $ret;
    }


    /**
     * @internal called by this class when generating new units
     * @see FreelinguistUnitDataAdapter::GenerateUnitsFromAdapter()
     *
     * @param array $log OUTREF
     * @param int[] $user_ids IN  0 or more ids from the wp_users table
     * @return  FreelinguistUnitDataAdapter[]
     *
     * This gets all the user info needed in the templates
     *
     * very similar in logic to the sister function @see FreelinguistUnitDataAdapter::get_content_info()
     */
    protected static function get_user_info(&$log,$user_ids ) {
        global $wpdb;

        $user_ids = static ::clean_mah_array($user_ids);
        if (empty($log) || !is_array($log)) {$log = [];}
        if (empty($user_ids)) {return [];}

        $comma_delimited_id_string = implode(',',$user_ids);
        $tag_type = FreelinguistTags::USER_TAG_TYPE;
        $job_type = static::TYPE_USER;

        //get the ids of the users, so we can grab all the information at once

        $sql_user_info =
            "
            SELECT
              look.user_id, look.rating_as_freelancer ,
              da_user.user_nicename, da_user.display_name,
              '$job_type' as job_type,
              GROUP_CONCAT(DISTINCT itag.tag_name ORDER BY itag.tag_name ASC) as tag_name_list,
              GROUP_CONCAT(DISTINCT meta_image.meta_value SEPARATOR '||') as image,
              GROUP_CONCAT(DISTINCT meta_description.meta_value) as description,
              GROUP_CONCAT(DISTINCT meta_country.meta_value SEPARATOR '||') as country,
              GROUP_CONCAT(DISTINCT meta_rate.meta_value SEPARATOR '||') as hourly_rate,
              GROUP_CONCAT(DISTINCT tags.tag_id SEPARATOR '||') as da_tags
            
            FROM wp_fl_user_data_lookup look
            
              INNER JOIN (
                SELECT user_id 
                FROM wp_fl_user_data_lookup look_no_hands
                WHERE user_id IN ($comma_delimited_id_string)
                ) as limit_users ON limit_users.user_id = look.user_id
            
            INNER JOIN wp_users da_user ON da_user.ID = look.user_id
            LEFT JOIN wp_tags_cache_job tags ON tags.job_id = look.user_id AND tags.type = $tag_type
            LEFT JOIN wp_interest_tags itag ON itag.ID = tags.tag_id
            LEFT JOIN wp_usermeta meta_image ON meta_image.user_id = look.user_id AND  meta_image.meta_key = 'user_image'
              LEFT JOIN wp_usermeta meta_description ON meta_description.user_id = look.user_id AND  meta_description.meta_key = 'description'
              LEFT JOIN wp_usermeta meta_country ON meta_country.user_id = look.user_id AND  meta_country.meta_key = 'user_residence_country'
              LEFT JOIN wp_usermeta meta_rate ON meta_rate.user_id = look.user_id AND  meta_rate.meta_key = 'user_hourly_rate'
            WHERE user_status = 0
            GROUP BY look.user_id
            ORDER BY look.user_id
            ;
    ";

        $results_user_info = $wpdb->get_results($sql_user_info);
        will_throw_on_wpdb_error($wpdb);
        static::log(static::LOG_DEBUG,'Adapter Generate Units: user info sql ',$wpdb->last_query);

        $ret = [];
        //fill in the user info now
        foreach ($results_user_info as $node) {

            $thing = new FreelinguistUnitDataAdapter($node->job_type);

            $thing->tags = explode('||',$node->da_tags);

            $thing->primary_id = $node->user_id;

            $thing->href = site_url().'/user-account/?lang=en&profile_type=translator&user='.$node->user_nicename;

            $thing->image = FreelinguistSizeImages::get_url_from_relative_to_upload_directory($node->image,FreelinguistSizeImages::SMALL,true);


            $thing->nicename = $node->user_nicename;
            $thing->title = $node->tag_name_list;

            $thing->description = $node->description;

            $thing->name = $node->display_name;

            $thing->view_or_rating = translater_rating( $node->user_id,17,'translator',
                false, $node->rating_as_freelancer);

            $country_id = explode('||',$node->country)[0];
            $thing->country = ($country_id) ?  get_countries()[$country_id] : '';

            $hourly_rate = explode('||',$node->hourly_rate)[0];
            $thing->rate_or_price_or_offer = ($hourly_rate ?  $hourly_rate. '/hour' : '');


            $ret[] = $thing;
        } //end loop to fill in user information

        return $ret;
    }

    /**
     * @see FreelinguistUnitDataAdapter::GenerateUnitsFromAdapter()
     *
     * @param array $log OUTREF
     * @param int[] int[] $content_ids IN  0 or more ids from the wp_linguist_content table
     *
     * This gets all the content info needed in the templates
     *
     * very similar in logic to the sister function @see FreelinguistUnitDataAdapter::get_user_info()
     * @return  FreelinguistUnitDataAdapter[]
     */
    protected static  function get_content_info(&$log,$content_ids) {
        global $wpdb;
        $content_ids = static ::clean_mah_array($content_ids);
        if (empty($log) || !is_array($log)) {$log = [];}
        if (empty($content_ids)) {return [];}

        $comma_delimited_id_string = implode(',',$content_ids);
        $tag_type = FreelinguistTags::CONTEST_TAG_TYPE;
        $job_type = static::TYPE_CONTENT;


        //get the ids of the content, so we can grab all the information at once

        $sql_content_info =
            "
            SELECT
              content.id as content_id, content.content_cover_image,content.content_title,content.content_summary,
              content.content_view,content.content_sale_type,content.content_amount,
              da_user.display_name,
              '$job_type' as job_type,
              GROUP_CONCAT(DISTINCT meta_country.meta_value SEPARATOR '||') as country,
              GROUP_CONCAT(DISTINCT tags.tag_id SEPARATOR '||') as da_tags,
              GROUP_CONCAT(DISTINCT itag.tag_name ORDER BY itag.tag_name ASC) as tag_name_list
            
            FROM wp_linguist_content content
            
              INNER JOIN (
                SELECT id as content_id
                FROM wp_linguist_content look_no_hands
                WHERE id IN ($comma_delimited_id_string) AND look_no_hands.user_id IS NOT NULL
                ) as limit_content ON limit_content.content_id = content.id
            
              INNER JOIN wp_users da_user ON da_user.ID = content.user_id
              LEFT JOIN wp_tags_cache_job tags ON tags.job_id = content.id AND tags.type = $tag_type
            LEFT JOIN wp_interest_tags itag ON itag.ID = tags.tag_id
              LEFT JOIN wp_usermeta meta_country ON meta_country.user_id = content.user_id AND  meta_country.meta_key = 'user_residence_country'
            WHERE content.user_id IS NOT NULL 
            GROUP BY content.id
            ORDER BY content.id
            ;
    ";

        $results_content_info = $wpdb->get_results($sql_content_info);
        will_throw_on_wpdb_error($wpdb);
        static::log(static::LOG_DEBUG,'Adapter Generate Units: content info sql ',$wpdb->last_query);


        $ret = [];

        //fill in the user info now
        foreach ($results_content_info as $node) {
            $thing = new FreelinguistUnitDataAdapter($node->job_type);

            $thing->tags = explode('||',$node->da_tags);
            $thing->tag_names =  explode('||',$node->tag_name_list);


            $thing->primary_id = $node->content_id;

            $thing->href = site_url().'/content/?lang=en&mode=view&content_id='.FreelinguistContentHelper::encode_id($node->content_id);

            //code-notes [image-sizing]  content getting small size for unit
            $thing->image = FreelinguistSizeImages::get_url_from_relative_to_upload_directory(
                $node->content_cover_image,FreelinguistSizeImages::SMALL,true);

            $thing->title = $node->content_title;

            $thing->description = $node->content_summary;

            $thing->name = $node->display_name;

            $thing->view_or_rating =($node->content_view ?  $node->content_view. '/Views' : '');

            $country_id = explode('||',$node->country)[0];
            $thing->country = ($country_id) ?  get_countries()[$country_id] : '';

            if($node->content_sale_type =='Fixed'){ $thing->rate_or_price_or_offer ='$' . $node->content_amount; }
            else if($node->content_sale_type =='Offer'){ $thing->rate_or_price_or_offer = 'Best Offer'; }
            else if($node->content_sale_type =='Free'){ $thing->rate_or_price_or_offer = '$0'; }

            $ret[] = $thing;
        } //end loop to fill in content information

        return $ret;
    }


}

FreelinguistUnitDataAdapter::turn_on_debugging(FreelinguistDebugging::LOG_WARNING);
