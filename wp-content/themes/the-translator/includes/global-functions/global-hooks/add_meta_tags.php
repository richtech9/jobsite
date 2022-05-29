<?php

/*
 * Added as a hook in various templates
 */
function add_meta_tags() {

    /*
     * current-php-code 2020-Nov-11
     * current-hook
     * input-sanitized :
     */
    /*echo '<meta http-equiv="cache-control" content="max-age=0" />';

    echo '<meta http-equiv="cache-control" content="no-cache" />';

    echo '<meta http-equiv="expires" content="0" />';

    echo '<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />';

    echo '<meta http-equiv="pragma" content="no-cache" />';*/



    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

    header("Cache-Control: post-check=0, pre-check=0", false);

    header("Pragma: no-cache");

}