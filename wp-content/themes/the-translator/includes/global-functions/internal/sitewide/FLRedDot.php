<?php

class FLRedDot extends  FreelinguistDebugging {
    /*
     * current-php-code 2020-Dec-19
     * internal-call
     * input-sanitized :
     */

    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;
    
    const TYPE_CONTENT = 'content';
    const TYPE_PROJECTS = 'projects';
    const TYPE_CONTESTS = 'contests';
    const TYPE_PROPOSAL = 'proposals';
    const ROLE_CUSTOMER = 'customer';
    const ROLE_FREELANCER = 'freelancer';

    const DISPLAY_DOT_WITH_NUMBERS = 1;

    //the different red dot events as set by the triggers
    const EVENT_PROJECT_WAS_HIRED = 'project_was_hired';
    const EVENT_PROJECT_REQUESTED_MILESTONE = 'project_requested_milestone';
    const EVENT_CONTEST_WAS_AWARDED = 'contest_was_awarded';
    const EVENT_CONTENT_ASKED_TO_COMPLETE = 'content_asked_to_complete';
    const EVENT_CONTENT_ASKED_TO_REVISE = 'content_asked_to_revise';
    const EVENT_CONTENT_ASKED_TO_REJECT = 'content_asked_to_reject';
    const EVENT_CONTENT_WAS_PURCHASED = 'content_was_purchased';
    const EVENT_PROJECT_ASKED_TO_COMPLETE = 'project_asked_to_complete';
    const EVENT_PROJECT_ASKED_TO_REJECT = 'project_asked_to_reject';
    const EVENT_CONTEST_ASKED_TO_COMPLETE = 'contest_asked_to_complete';
    const EVENT_CONTEST_WAS_PUT_IN_MEDIATION = 'contest_was_put_in_mediation';
    const EVENT_CONTEST_ASKED_TO_REJECT = 'contest_asked_to_reject';
    const EVENT_UNKNOWN = 'unkown';
    
    const EVENT_MESSAGES_TO_USER = [
        self::EVENT_PROJECT_WAS_HIRED => 'Hired',
        self::EVENT_PROJECT_REQUESTED_MILESTONE => 'Milestone Requested',
        self::EVENT_CONTEST_WAS_AWARDED => 'Awarded',
        self::EVENT_CONTENT_ASKED_TO_COMPLETE => 'Need Completion',
        self::EVENT_CONTENT_ASKED_TO_REVISE => 'Need Revision',
        self::EVENT_CONTENT_ASKED_TO_REJECT => 'Rejected',
        self::EVENT_CONTENT_WAS_PURCHASED => 'Purchase',
        self::EVENT_PROJECT_ASKED_TO_COMPLETE => 'Need Completion',
        self::EVENT_PROJECT_ASKED_TO_REJECT => 'Rejected',
        self::EVENT_CONTEST_ASKED_TO_COMPLETE => 'Need Completion',
        self::EVENT_CONTEST_WAS_PUT_IN_MEDIATION => 'Mediation Started',
        self::EVENT_CONTEST_ASKED_TO_REJECT => 'Rejected',
        self::EVENT_UNKNOWN => 'Unknown Event'
    ];

    
    
    public $id = null;
    public $is_red_dot = null;
    public $is_future_action = null;
    public $event_user_id_role = null;
    public $event_user_id = null;
    public $contest_id = null;
    public $project_id = null;
    public $content_id = null;
    public $milestone_id = null;
    public $job_id = null;
    public $proposal_id = null;
    public $other_user_id = null;
    public $discussion_id = null;
    public $event_timestamp_ts = null;
    public $future_timestamp_ts = null;

    /**
     * @var string $event_name 
     */
    public $event_name = null;

    /**
     * @var array[] $user_dot_cache
     */
    public static $user_dot_cache = [];

    /**
     * cache the red dots so we only query once wile working with multiple templates to make a page
     * @param bool $b_refresh_cache, default false. If true, then will not get any cached results, but will requery
     * @param int $force_role, if want to get the user's other role than the one logged in
     * @return FLRedDot[]
     */
    public static function get_dots_for_user($b_refresh_cache = false,$force_role=0) {
        $user_id = get_current_user_id();
        if (empty($force_role)) {
            $role_id = FreelinguistUserLookupDataHelpers::get_logged_in_role_id();
        } else {
            $role_id = $force_role;
        }
        $cache_key = "user-$user_id-role-$role_id";
        if (!$b_refresh_cache) {
            if (array_key_exists($cache_key,static::$user_dot_cache)) {
                return static::$user_dot_cache[$cache_key];
            }
        }



        static::$user_dot_cache[$cache_key] = static::list_red_dots_for_user_and_role($user_id,$role_id);
        return static::$user_dot_cache[$cache_key];
    }

    /**
     * Generates dots to see in the pages. The type of logic here depends on what is sent as params
     * This function always returns a single dot.
     *   But, the dot could be for many things, so will needs extra info in it
     *   Or, the dot could be for a single id, in which case info about that single thing may be included
     *
     * Unless we are just listening to specific ids (for example the
     * @param string[] $what_types , if empty then all types are used, else just filter for these types
     * @param int $context_id <p>
     *   returns a single dot for a single specific id, if there is no dot for that id, then will return null
     *   only used if the $what_types is a single type
     *   then the id will be understood to be either content, contest or project id
     *   if this is set, and there is not one valid type set in the other param, a logic exception will be thrown
     * </p>
     * @param int $force_role if want to get dots for the other user role, default false
     * @param int $options  OR'd options for dot display
     *  DISPLAY_DOT_WITH_NUMBERS If there are two or more dot entries for this single dot, then put a (n) after the dot, where n is the number
     *
     * @return string returns the html for the dot
     */
    public static function generate_dot_html_for_user($what_types = [],$context_id = null,
                                                      $force_role = 0,$options = self::DISPLAY_DOT_WITH_NUMBERS) {
        $dots_all = static::get_dots_for_user(false,$force_role);
        //get any cached dots, or find it if first time
        static::log(static::LOG_DEBUG,'starting generate_dot_html_for_user',[
            'dots' => $dots_all,
            '$what_types' => $what_types,
            '$context_id' => $context_id,
            '$force_role' => $force_role
        ]);
        /**
         * @var FLRedDot[] $dots_to_use
         */
        $dots_to_use = [];
        $b_use_secondary_types = false;
        if (in_array(static::TYPE_PROPOSAL,$what_types)) {
            $b_use_secondary_types = true;
        }
        $html = '';
        if (!empty($what_types) && is_array($what_types)) {
            if ($context_id && (count($what_types) !== 1)) {
                throw new LogicException("context id can only be set with a single red dot type");
            }
            $context_id = (int)$context_id;
            foreach ($dots_all as $a_dot) {
                $dot_type = $a_dot->get_type($b_use_secondary_types);
                foreach ($what_types as $what) {
                    if ($dot_type === $what) {
                        if ($context_id) {
                            switch ($what) {
                                case static::TYPE_PROJECTS:
                                    {
                                        if ($a_dot->project_id === $context_id) {$dots_to_use[] = $a_dot;}
                                        break;
                                    }
                                case static::TYPE_CONTENT:
                                    {
                                        if ($a_dot->content_id === $context_id) {$dots_to_use[] = $a_dot;}
                                        break;
                                    }

                                case static::TYPE_CONTESTS:
                                    {
                                        if ($a_dot->contest_id === $context_id) {$dots_to_use[] = $a_dot;}
                                        break;
                                    }

                                case static::TYPE_PROPOSAL:
                                    {
                                        if ($a_dot->proposal_id === $context_id) {$dots_to_use[] = $a_dot;}
                                        break;
                                    }


                                default: {
                                    throw new LogicException("Not expecting '$what' as a dot type");
                                }
                            }
                        }//end if context id
                        else {
                            $dots_to_use[] = $a_dot;
                        }
                    }//end if the dot type matches

                }//end for each type
            } //end for each all dots
        } else if (!empty($what_types)){throw new LogicException("dot types have to be listed in an array");}
        else {$dots_to_use = $dots_all;}

        static::log(static::LOG_DEBUG,'dots to use in  generate_dot_html_for_user',[
            '$dots_to_use' => $dots_to_use
        ]);
        //now we have the filtered dots that are available, can be >= 0
        if (empty($dots_to_use)) {return $html;}
        if (count($dots_to_use) === 1) {
            //return a dot for a single entry
            $title = $dots_to_use[0]->get_human_event_message();
            $count_to_show = 1;
            $id_string_comma_delimited = $dots_to_use[0]->id;
            ob_start();
            ?>
            <div class="fl-red-dot-holder" title="<?=$title?>" data-rids="<?= $id_string_comma_delimited ?>">
                <span class="fl-red-dot small-text"><?=$count_to_show ?></span>
            </div>
            <?php
            $html .= trim(ob_get_clean());
        } else {
            //return a summary dot
            $count_to_show = '';
            if ($options & static::DISPLAY_DOT_WITH_NUMBERS) {
                $count_to_show = count($dots_to_use);
            }

            $rem_titles = [];
            $id_string_comma_array = [];
            foreach ($dots_to_use as $dat_dot) {
                $dot_title = $dat_dot->get_human_event_message();
                if (!array_key_exists($dot_title,$rem_titles)) {
                    $rem_titles[$dot_title] = 0;
                }
                $rem_titles[$dot_title]++;
                $id_string_comma_array[] = $dat_dot->id;
            }
            $id_string_comma_delimited = implode(',',$id_string_comma_array);
            $title = implode(', ',array_keys($rem_titles));

            ob_start();
            ?>

            <div class="fl-red-dot-holder" title="<?=$title?>" data-rids="<?= $id_string_comma_delimited ?>">
                <span class="fl-red-dot small-text"><?=$count_to_show ?></span>
            </div>
            <?php
            $html .= trim(ob_get_clean());
        }

        return $html;

    }




    /**
     * gets the logged in user id and role, gets current red dots
     * @param int $user_id
     * @param int $role_id
     * @param array $what_types, if want to show just some types
     * @return FLRedDot[]
     */
    protected static function list_red_dots_for_user_and_role($user_id, $role_id, $what_types = []) {
        global $wpdb;

        if (empty(intval($role_id))) {return [];}
        $other_where = '1';
        $action_where_array = [];
        if (!empty($what_types) && is_array($what_types)) {
            foreach ($what_types as $what) {
               switch ($what) {
                   case static::TYPE_PROJECTS: {
                       $action_where_array[] = "(d.project_id IS NOT NULL)";
                       $action_where_array[] = "(d.job_id IS NOT NULL)";
                       $action_where_array[] = "(d.milestone_id IS NOT NULL)";
                       break;
                   }
                   case static::TYPE_CONTENT: {
                       $action_where_array[] = "(d.content_id IS NOT NULL)";
                       break;
                   }

                   case static::TYPE_CONTESTS: {
                       $action_where_array[] = "(d.contest_id IS NOT NULL)";
                       $action_where_array[] = "(d.proposal_id IS NOT NULL)";;
                       break;
                   }
                   default: {
                       throw new LogicException("Not expecting '$what' as a dot type");
                   }
               } 
            }//end for each
            $other_where = ' AND ('.implode(' OR ',$action_where_array). ')';
        }
        
        $sql = "SELECT  
                d.id,
                d.is_red_dot,
                d.is_future_action,
                d.event_user_id_role,
                d.event_user_id,
                d.contest_id,
                d.project_id,
                d.content_id,
                d.milestone_id,
                d.job_id,
                d.proposal_id,
                d.other_user_id,
                d.discussion_id,
                UNIX_TIMESTAMP(event_timestamp) as event_timestamp_ts,
                UNIX_TIMESTAMP(future_timestamp) as future_timestamp_ts,
                event_name
                FROM wp_fl_red_dots d
                WHERE
                  event_user_id = $user_id AND event_user_id_role = $role_id AND
                   is_red_dot = 1  
                  AND $other_where
                  ORDER BY event_name";
        
        $res = $wpdb->get_results($sql);
        
        $ret = [];
        foreach ($res as $row) {
            $node = new FLRedDot();
            foreach ($row as $property => $value) {
                if (property_exists($node,$property)) {
                    $node->$property = empty($value) ? null : $value;
                    if (is_numeric($node->$property)) {
                        $node->$property = (int)$node->$property;
                    }
                }
            }//end for each value
            $ret[] = $node;
        }
        
        return $ret;
        
    }

    /**
     * Removes Red Dot Rows,showing on the gui, associated with a user and flags and ids set in a red dot object
     * It only deletes the rows if the b_delete flag is true, and if this was a red dot still active, and this is not an active future action
     * else it marks the red dot as read
     * @param int $user_id
     * @param FLRedDot $red_dot
     * @param bool $b_delete, default false
     * @return int (number of rows deleted)
     */
    public static function remove_red_dots($user_id,$red_dot,$b_delete = false) {
        global $wpdb;
        $user_id = (int)$user_id;
        if (!$user_id ) {
            return 0;
            //throw new LogicException("Need user set in params for remove_red_dots. This is probably because an unlogged user was on a page ");
        }
        $where_parts = [];
        $where_parts[] = "event_user_id = $user_id";
        if ($red_dot->event_name) {
            $where_parts[] = "event_name = '".esc_sql($red_dot->event_name)."'";
        }

        $no_no_list = ['event_user_id','future_timestamp_ts','event_timestamp_ts'];
        foreach ($red_dot as $key => $value) {
            if (in_array($key,$no_no_list)  ) {
                if (!empty($value)) {
                    throw new LogicException("Cannot have $key property set when using the remove_red_dots");
                }

            }
            if ($key === 'event_name') {continue;}
            if (empty($value)) {continue;}
            $int_val = (int)$value;
            $where_parts[] = "$key = $int_val";
        }
        $where_string = '('.implode(') AND (',$where_parts).')';
        if (!$where_string) {throw new LogicException("Someting wrong with making where string in remove_red_dots");}

        if ($b_delete) {
            $sql = "DELETE FROM wp_fl_red_dots WHERE $where_string AND is_red_dot = 1 AND is_future_action <=0 ";
            static::log(static::LOG_DEBUG,'delete sql for removing red dots',$sql);
            $wpdb->query($sql);
            will_throw_on_wpdb_error($wpdb,'remove_red_dots::delete');
        }

        $sql = "UPDATE wp_fl_red_dots SET is_red_dot = -1 WHERE $where_string AND is_red_dot = 1 ";
        static::log(static::LOG_DEBUG,'update sql for hiding red dots',$sql);
        $wpdb->query($sql);
        will_throw_on_wpdb_error($wpdb,'remove_red_dots::update');

        return $wpdb->rows_affected;
    }


    /**
     * @param bool $b_use_secondary, force types that are hidden by the major types
     * @return null|string
     */
    public function get_type($b_use_secondary = false) {

        if ($b_use_secondary && $this->proposal_id ) {
            return static::TYPE_PROPOSAL;
        }

        if ($this->project_id || $this->job_id || $this->milestone_id) {
            return static::TYPE_PROJECTS;
        }

        if ($this->contest_id || $this->proposal_id ) {
            return static::TYPE_CONTESTS;
        }



        if ($this->content_id ) {
            return static::TYPE_CONTENT;
        }
        return null;
    }

    /**
     * @return string
     */
    public function get_human_event_message() {
        if (!array_key_exists($this->event_name,static::EVENT_MESSAGES_TO_USER)) {
            static::log(static::LOG_ERROR,'unknown event name',$this->event_name);
            return '';
        }
        return static::EVENT_MESSAGES_TO_USER[$this->event_name];
    }


}

FLRedDot::turn_on_debugging(FreelinguistDebugging::LOG_WARNING);