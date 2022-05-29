<?php

add_action( 'init', 'freeling_rewrites_init' );
/*
 * current-php-code 2020-Jan-11
 * current-hook
 * input-sanitized :
 */
function freeling_rewrites_init(){

    /*
     * current-php-code 2020-Nov-11
     * internal-call
     * input-sanitized :
     */

    FreelinguistPostRewrite::add_rewrite_rule(FreelinguistPostRewrite::ON_INIT);
    //add in array of urls to register
    FreelinguistStaticPageLogic::add_rewrite_endpoints(FreelinguistStaticPageLogic::ON_INIT);
    AdminPageTestUnits::add_rewrite_endpoints(AdminPageTestUnits::ON_INIT);

}

//add in hook to intercept and display our page, if its called
add_action( 'template_redirect', function() {
    /*
     * current-php-code 2020-Nov-11
     * internal-call
     * input-sanitized :
     */

    FreelinguistStaticPageLogic::process_custom_endpoints();
    AdminPageTestUnits::process_custom_endpoints();
} );

add_action("after_switch_theme", function() {

    /*
     * current-php-code 2020-Nov-11
     * internal-call
     * input-sanitized :
     */

    FreelinguistPostRewrite::add_rewrite_rule(FreelinguistPostRewrite::INIT_THEME);
    FreelinguistStaticPageLogic::add_rewrite_endpoints(FreelinguistStaticPageLogic::INIT_THEME);
    AdminPageTestUnits::add_rewrite_endpoints(AdminPageTestUnits::INIT_THEME);
});

add_action('switch_theme', function() {

    /*
     * current-php-code 2020-Nov-11
     * internal-call
     * input-sanitized :
     */

    FreelinguistPostRewrite::add_rewrite_rule(FreelinguistPostRewrite::END_THEME);
    FreelinguistStaticPageLogic::add_rewrite_endpoints(FreelinguistStaticPageLogic::END_THEME);
    AdminPageTestUnits::add_rewrite_endpoints(AdminPageTestUnits::END_THEME);
});