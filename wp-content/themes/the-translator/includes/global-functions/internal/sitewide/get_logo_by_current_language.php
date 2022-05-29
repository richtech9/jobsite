<?php

function get_logo_by_current_language(){

    /*
     * current-php-code 2020-Sep-30
     * internal-call
     * input-sanitized : lang
     */

    $lang = FLInput::get('lang','en') ;
    if($lang){

        if($lang == 'zh-hans'){

            $language = 'chinese_logo';

        }elseif($lang == 'ru'){

            $language = 'russian_logo';

        }elseif($lang == 'ja'){

            $language = 'japanese_logo';

        }elseif($lang == 'de'){

            $language = 'german_logo';

        }elseif($lang == 'es'){

            $language = 'spanish_logo';

        }elseif($lang == 'fr'){

            $language = 'french_logo';

        }elseif($lang == 'pt-pt'){

            $language = 'portuguese_logo';

        }elseif($lang == 'it'){

            $language = 'italian_logo';

        }elseif($lang == 'pl'){

            $language = 'polish_logo';

        }elseif($lang == 'tr'){

            $language = 'turkish_logo';

        }elseif($lang == 'fa'){

            $language = 'persian_logo';

        }elseif($lang == 'zh-hant'){

            $language = 'chinese_traditional_logo';

        }elseif($lang == 'da'){

            $language = 'danish_logo';

        }elseif($lang == 'nl'){

            $language = 'dutch_logo';

        }elseif($lang == 'hi'){

            $language = 'hindi_logo';

        }elseif($lang == 'ar'){

            $language = 'arabic_logo';

        }elseif($lang == 'ko'){

            $language = 'korean_logo';

        }elseif($lang == 'cs'){

            $language = 'czech_logo';

        }elseif($lang == 'vi'){

            $language = 'vietnamese_logo';

        }elseif($lang == 'id'){

            $language = 'indonesian_logo';

        }elseif($lang == 'sv'){

            $language = 'swedish_logo';

        }elseif($lang == 'ms'){

            $language = 'malay_logo';

        }else{

            $language = 'english_logo';

        }

    }else{

        $language = 'english_logo';

    }

    $uploads = wp_upload_dir();

    $upload_path = $uploads['baseurl'].'/';

    if(get_option($language) == ''){

        $logo_image = get_template_directory_uri().'/images/logo-1000-by-200.png';

    }else{

        $logo_image = $upload_path.get_option($language);
    }

    return fl_maybe_change_to_ngrok($logo_image);

}