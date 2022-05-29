<?php

add_action('wp_ajax_getHomepageInterestList', ['FreelinguistUnitDisplay','getHomepageInterestList']);

add_action('wp_ajax_nopriv_getHomepageInterestList', ['FreelinguistUnitDisplay','getHomepageInterestList']);

function wrapGetHomepageInterestList() {
    /*
      * current-php-code 2020-Nov-19
      * ajax-endpoint  getHomepageInterestList
      * input-sanitized :
      * public-api
      */

    FreelinguistUnitDisplay::getHomepageInterestList();
}