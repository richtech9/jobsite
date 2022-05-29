<?php

function get_stripe_lang(){
    /*
     * current-php-code 2021-Jan-7
     * internal-call
     * input-sanitized :
    */

    if(isset($_REQUEST['lang'])){
        $stripe_current_lang = $_REQUEST['lang'];
        if($stripe_current_lang == 'zh-hans'){
            $stripe_current_lang = 'zh';
        }elseif($stripe_current_lang == 'da'){
            $stripe_current_lang = 'da';
        }elseif($stripe_current_lang == 'nl'){
            $stripe_current_lang = 'nl';
        }elseif($stripe_current_lang == 'fr'){
            $stripe_current_lang = 'fr';
        }elseif($stripe_current_lang == 'fi'){
            $stripe_current_lang = 'fi';
        }elseif($stripe_current_lang == 'de'){
            $stripe_current_lang = 'de';
        }elseif($stripe_current_lang == 'it'){
            $stripe_current_lang = 'it';
        }elseif($stripe_current_lang == 'ja'){
            $stripe_current_lang = 'ja';
        }elseif($stripe_current_lang == 'es'){
            $stripe_current_lang = 'es';
        }elseif($stripe_current_lang == 'no'){
            $stripe_current_lang = 'no';
        }elseif($stripe_current_lang == 'sv'){
            $stripe_current_lang = 'sv';
        }else{
            $stripe_current_lang = 'en';
        }

    }else{
        $stripe_current_lang = 'en';
    }
    return $stripe_current_lang;
}