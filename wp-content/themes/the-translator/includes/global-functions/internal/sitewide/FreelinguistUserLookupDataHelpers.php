<?php
use Hashids\Hashids;

class FreelinguistUserLookupDataHelpers {
    const USER_LOOKUP_CAPABILITIES_BUYER = 1;
    const USER_LOOKUP_CAPABILITIES_FREELANCER = 2;
    const USER_LOOKUP_CAPABILITIES_BOTH = 3;


    //when generating referral hashes avoid similar looking characters
    const REFERRAL_CODE_ALPHABET = 'ABCDEFGHJKMNPQRSTWXYZ23456789';

    //when generating referral code hashes set the length
    const REFERRAL_CODE_LENGTH = 5;

    /**
     * @param bool $b_opposite , if set to true, then will return the role the user is not right now
     * @return int|null
     */
    static function get_logged_in_role_id($b_opposite = false) {
        $string_role = xt_user_role();

        if ($b_opposite) {
            if ($string_role === 'customer')
            {return static::USER_LOOKUP_CAPABILITIES_FREELANCER;}

            if ($string_role === 'translator')
            {return static::USER_LOOKUP_CAPABILITIES_BUYER;}
        } else {
            if ($string_role === 'customer')
            {return static::USER_LOOKUP_CAPABILITIES_BUYER;}

            if ($string_role === 'translator')
            {return static::USER_LOOKUP_CAPABILITIES_FREELANCER;}
        }


        return null;
    }

    static function set_referal_code_for_user_id($user_id) {
        global $wpdb;
        $user_id = (int)$user_id;
        if (!$user_id) {throw new RuntimeException("need a numeric user id to generatate the referral code");}

        $hashids = new Hashids(NONCE_SALT, static::REFERRAL_CODE_LENGTH,static::REFERRAL_CODE_ALPHABET);
        $new_hash = $hashids->encode($user_id);
        $new_hash_escaped = esc_sql($new_hash);
        $sql = "UPDATE wp_fl_user_data_lookup SET reference_code = '$new_hash_escaped' WHERE user_id = $user_id";
        $wpdb->query($sql);
        will_throw_on_wpdb_error($wpdb,'Could not set user referral code');
        return $new_hash;
    }

    static function find_user_with_referral_code($referral_code) {
        global $wpdb;
        $referrral_code_trimmed = trim($referral_code);
        if (!$referrral_code_trimmed) {return false;}

        $referral_code_escaped = esc_sql($referral_code);
        $sql = "SELECT user_id FROM wp_fl_user_data_lookup where reference_code = '$referral_code_escaped'";
        $res = $wpdb->get_results($sql);
        will_throw_on_wpdb_error($wpdb,'Trying to find user referral code');
        if (empty($res)) {return false;}
        return (int)$res[0]->user_id;
    }

    static function get_user_referral_code($user_id) {
        global $wpdb;
        $user_id = (int)$user_id;
        if (!$user_id) {throw new RuntimeException("need a numeric user id to generatate the referral code");}

        $sql = "SELECT reference_code FROM wp_fl_user_data_lookup where user_id = $user_id";
        $res = $wpdb->get_results($sql);
        will_throw_on_wpdb_error($wpdb,'Trying to get user referral code');
        if (empty($res)) {return false;}
        return $res[0]->reference_code;
    }
}