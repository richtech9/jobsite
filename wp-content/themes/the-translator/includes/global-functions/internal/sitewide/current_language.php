<?php

function current_language(){

    /*
     * current-php-code 2020-Sep-30
     * internal-call
     * input-sanitized : lang
     */

    $lang = FLInput::get('lang','en') ;

    if(isset($lang)){

        if($lang == 'zh-hans'){

            $language = 'chinese';

        }elseif($lang == 'ru'){

            $language = 'russian';

        }elseif($lang == 'ja'){

            $language = 'japanese';

        }elseif($lang == 'de'){

            $language = 'german';

        }elseif($lang == 'es'){

            $language = 'spanish';

        }elseif($lang == 'fr'){

            $language = 'french';

        }elseif($lang == 'pt-pt'){

            $language = 'portuguese';

        }elseif($lang == 'it'){

            $language = 'italian';

        }elseif($lang == 'pl'){

            $language = 'polish';

        }elseif($lang == 'tr'){

            $language = 'turkish';

        }elseif($lang == 'fa'){

            $language = 'persian';

        }elseif($lang == 'zh-hant'){

            $language = 'chinese_traditional';

        }elseif($lang == 'da'){

            $language = 'danish';

        }elseif($lang == 'nl'){

            $language = 'dutch';

        }elseif($lang == 'hi'){

            $language = 'hindi';

        }elseif($lang == 'ar'){

            $language = 'arabic';

        }elseif($lang == 'ko'){

            $language = 'korean';

        }elseif($lang == 'cs'){

            $language = 'czech';

        }elseif($lang == 'vi'){

            $language = 'vietnamese';

        }elseif($lang == 'id'){

            $language = 'indonesian';

        }elseif($lang == 'sv'){

            $language = 'swedish';

        }elseif($lang == 'ms'){

            $language = 'malay';

        }else{

            $language = 'english';

        }

    }else{

        $language = 'english';

    }

    return $language;

}