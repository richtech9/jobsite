<?php
/*
    * current-php-code 2020-Feb-25
    * input-sanitized :
    * current-wp-template:  for translator and customer wallet
*/
//the payment form for the main wallet page, there is sharing of the payment form elsewhere, modal and static refills should not be loaded at the same time
get_template_part('includes/user/wallet/wallet', 'payment-form');

