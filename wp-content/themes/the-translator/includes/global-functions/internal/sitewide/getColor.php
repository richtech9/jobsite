<?php

function getColor($s=0) {
    /*
     * current-php-code 2020-Oct-17
     * internal-call
     * input-sanitized : lang
     */
    return array(rand(0,255),rand($s,255),rand(0,255));
}