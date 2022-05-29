<?php

/*

File Used for user Management

Add User

User Login

*/

if(isset($_POST['reg_email'])){

    /*
      * current-php-code 2021-march-23
      * ajax-endpoint  reg_email (not real ajax)
      * input-sanitized : reg_email
      */

    $user = $_POST['reg_email'];

    (email_exists($user)) ? $msg = 'false': $msg = 'true';

    echo $msg;

    exit;

}