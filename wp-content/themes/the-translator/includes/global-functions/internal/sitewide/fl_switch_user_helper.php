<?php

class FLSwitchUserHelper {
    //associative array of urls with nonces made, indexed by user id
    protected $switch_urls_base_hash = [];

    //associative array of user objects
    protected $wp_user_hash = [];

    public function __construct(){
        $this->switch_urls_base_hash = [];
        $this->wp_user_hash = [];
    }

    /**
     * Gets the url to switch users, or if something happens returns the destination link, use the b_processed flag to tell which
     * @param int $user_id The user id to switch to
     * @param string $destination_link the link you want to go after the switch happens
     * @param bool $b_processed, OUTREF if true, then the return  is a link switching with nonce and redirect in the params, if false the return is the destination link
     * @return string , either the switch url if the user-switching plugin is active, or as a fallback just the $destination link
     */
    public function generate_switch_redirect_url($user_id,$destination_link,&$b_processed=false) {
        $lang = FLInput::get('lang','en');
        if (!class_exists('user_switching',false)) {
            $b_processed = false;
            //do not clutter log if plugin is turned off or missing
            return $destination_link;
        }
        if (!method_exists ('user_switching','maybe_switch_url')) {
            will_send_to_error_log("generate_switch_redirect_url: The User Switching plugin has updated its api, need to update this code");
            $b_processed = false;
            return $destination_link;
        }
        $user_id = (int)$user_id;
        if (!$user_id) {
            will_send_to_error_log("generate_switch_redirect_url: The User id passed in was empty");
            $b_processed = false;
            return $destination_link;
        }

        $wp_user_key = "user-$user_id";
        if (!array_key_exists($wp_user_key,$this->wp_user_hash)) {
            $wp_user = get_user_by('id',$user_id);
            if (!$wp_user || !$wp_user->ID) {
                will_send_to_error_log("generate_switch_redirect_url: Invalid User ID");
                $b_processed = false;
                return $destination_link;
            }
            $this->wp_user_hash[$wp_user_key] = $wp_user;
            $url_template = user_switching::maybe_switch_url($wp_user);
            $this->switch_urls_base_hash[$wp_user_key] = $url_template ;

        } else {
            $url_template = $this->switch_urls_base_hash[$wp_user_key];
        }

        $destination_link_escaped = rawurlencode($destination_link) ;
        $new_url = add_query_arg(  ['redirect_to'=> $destination_link_escaped,'lang'=>$lang],$url_template);
        return $new_url;

    }

}