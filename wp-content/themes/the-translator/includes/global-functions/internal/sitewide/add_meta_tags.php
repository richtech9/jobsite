<?php
// used in a lot of top templates as a hook to the header
function add_meta_tags() {
    /*
     * current-php-code 2020-Oct-15
     * internal-call
     * input-sanitized :
    */

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

    header("Cache-Control: post-check=0, pre-check=0", false);

    header("Pragma: no-cache");

}