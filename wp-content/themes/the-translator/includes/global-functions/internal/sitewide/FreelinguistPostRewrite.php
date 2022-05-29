<?php
class FreelinguistPostRewrite
{
    /*
    * current-php-code 2020-Nov-11
    * internal-call
    * input-sanitized :
    */

    public const REWRITES = [
        'jobs' => ['rule'=> 'job/[0-9][0-9]([0-9]+)?$','query'=>'index.php?post_type=job&p=$matches[1]'],
    ];

    public const ON_INIT = 'on_init';
    public const INIT_THEME = 'init_theme';
    public const END_THEME = 'end_theme';

    public static function add_rewrite_rule($action) {

        switch($action) {
            case static::ON_INIT: {
                foreach (static::REWRITES as $key => $node) {
                    $rule = $node['rule'];
                    $query = $node['query'];
                    add_rewrite_rule($rule, $query);
                }
                break;
            }
            case static::INIT_THEME: {
                foreach (static::REWRITES as $key => $node) {
                    $rule = $node['rule'];
                    $query = $node['query'];
                    add_rewrite_rule($rule, $query);
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
                throw new RuntimeException("Does not recognize: $action in add_rewrite_endpoints");
            }
        }

    }

}