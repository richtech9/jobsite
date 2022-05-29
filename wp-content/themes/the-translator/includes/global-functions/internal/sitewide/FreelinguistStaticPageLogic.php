<?php
class FreelinguistStaticPageLogic
{
    /*
    * current-php-code 2020-Oct-23
    * internal-call
    * input-sanitized :
    */

    public const REWRITES = [
        'tos' => ['part'=> 'tos','endpoint'=>'terms-of-service'],
        'contact' => ['part'=>'contact','endpoint'=>'contact-peerok'],
        'faq' => ['part'=>'faq','endpoint'=>'peerok-faq'],
        'about-us' => ['part'=>'about-us','endpoint'=>'about-peerok'],
        'careers' => ['part'=>'careers','endpoint'=>'careers-peerok'],
        'privacy' => ['part'=>'privacy','endpoint'=>'privacy-peerok'],

        'multilingual-websites' => [
            'part'=>'multilingual-websites',
            'endpoint'=>'peerok-multilingual-websites'
        ],

        'document-translation' => [
            'part'=>'document-translation',
            'endpoint'=>'peerok-document-translation'
        ],

        'personal-translation-services' => [
            'part'=>'personal-translation-services',
            'endpoint'=>'peerok-personal-translation-services'
        ],

        'video-translation-transcription-subtitling' => [
            'part'=>'video-translation-transcription-subtitling',
            'endpoint'=>'peerok-video-translation-transcription-subtitling'
        ],

        'gaming-translation' => [
            'part'=>'gaming-translation',
            'endpoint'=>'peerok-gaming-translation'
        ],

        'technical-translation' => [
            'part'=>'technical-translation',
            'endpoint'=>'peerok-technical-translation'
        ],

        'documentation-writing' => [
            'part'=>'documentation-writing',
            'endpoint'=>'peerok-documentation-writing'
        ],

        'article-writing-for-blogs-newsletters' => [
            'part'=>'article-writing-for-blogs-newsletters',
            'endpoint'=>'peerok-article-writing-for-blogs-newsletters'
        ],

        'resume-writing' => [
            'part'=>'resume-writing',
            'endpoint'=>'peerok-resume-writing'
        ],

        'editing-proofreading' => [
            'part'=>'editing-proofreading',
            'endpoint'=>'peerok-editing-proofreading'
        ],

        'press-release-and-speech-writing' => [
            'part'=>'press-release-and-speech-writing',
            'endpoint'=>'peerok-press-release-and-speech-writing'
        ],

        'localization-and-internationalization' => [
            'part'=>'localization-and-internationalization',
            'endpoint'=>'peerok-localization-and-internationalization'
        ],
    ];

    public const ON_INIT = 'on_init';
    public const INIT_THEME = 'init_theme';
    public const END_THEME = 'end_theme';

    public static function add_rewrite_endpoints($action) {

        switch($action) {
            case static::ON_INIT: {
                foreach (static::REWRITES as $key => $node) {
                    $thing = $node['endpoint'];
                    add_rewrite_endpoint($thing, EP_ALL);
                }
                break;
            }
            case static::INIT_THEME: {
                foreach (static::REWRITES as $key => $node) {
                    $thing = $node['endpoint'];
                    add_rewrite_endpoint($thing, EP_ALL);
                }
                // flush rewrite rules - only do this on activation as anything more frequent is bad!
                flush_rewrite_rules();
                break;
            }
            case static::END_THEME: {
                // flush rewrite rules when ending to remove custom urls
                flush_rewrite_rules();
                break;
            }
            default: {
                throw new RuntimeException("Does not recognize: $action in add_rewrite_endpoints");
            }
        }

    }

    public static function process_custom_endpoints() {

        foreach (static::REWRITES as $key => $node) {
            $thing = '/'.$node['endpoint'];
            $b_found = strpos($_SERVER['REQUEST_URI'],$thing) !== false;
            if ($b_found) {
               $out = static::get_static_page($node['part']);
                http_response_code(200);
                switch($node['part']) {
                    case 'contact': {
                        $title = __('Contact Us');
                        static::print_standard_page($title,$out);
                        die();
                    }
                    case 'tos': {
                        $title = __('Terms of Service');
                        static::print_standard_page($title,$out);
                        die();
                    }
                    case 'faq': {
                        $title = __('FAQ');
                        static::print_standard_page($title,$out);
                        die();
                    }
                    case 'about-us': {
                        $title = __('About Us');
                        static::print_standard_page($title,$out);
                        die();
                    }
                    case 'careers': {
                        $title = __('Careers');
                        static::print_standard_page($title,$out);
                        die();
                    }
                    case 'privacy': {
                        $title = __('Privacy');
                        static::print_standard_page($title,$out);
                        die();
                    }
                    case 'multilingual-websites': {
                        $title = __('Multilingual Websites');
                        static::print_standard_page($title,$out);
                        die();
                    }
                    case 'document-translation': {
                        $title = __('Document Translation');
                        static::print_standard_page($title,$out);
                        die();
                    }
                    case 'personal-translation-services': {
                        $title = __('Voice Over and Voice Casting');
                        static::print_standard_page($title,$out);
                        die();
                    }

                    case 'video-translation-transcription-subtitling': {
                        $title = __('Video Translation, Transcription & Subtitling');
                        static::print_standard_page($title,$out);
                        die();
                    }

                    case 'gaming-translation': {
                        $title = __('Gaming Translation');
                        static::print_standard_page($title,$out);
                        die();
                    }

                    case 'technical-translation': {
                        $title = __('Audio Translation, Editing, and Production');
                        static::print_standard_page($title,$out);
                        die();
                    }

                    case 'documentation-writing': {
                        $title = __('Documentation Writing');
                        static::print_standard_page($title,$out);
                        die();
                    }

                    case 'article-writing-for-blogs-newsletters': {
                        $title = __('Article Writing for Blogs, Newsletters,…');
                        static::print_standard_page($title,$out);
                        die();
                    }

                    case 'resume-writing': {
                        $title = __('Resume Writing');
                        static::print_standard_page($title,$out);
                        die();
                    }

                    case 'editing-proofreading': {
                        $title = __('Editing / Proofreading');
                        static::print_standard_page($title,$out);
                        die();
                    }

                    case 'press-release-and-speech-writing': {
                        $title = __('Press Releases and Speech Writing');
                        static::print_standard_page($title,$out);
                        die();
                    }

                    case 'localization-and-internationalization': {
                        $title = __('Localization and Internationalization');
                        static::print_standard_page($title,$out);
                        die();
                    }



                    default: {
                        get_header();
                        print '<main role="main"><section>';
                        print $out;
                        print '</section></main>';
                        get_footer('homepagenew');
                        die();

                    }
                }

            }
        }
    }



    public static function get_static_page($page_name)
    {
        $static_folder = get_template_directory() . '/static-parts';
        $language = current_language();
        $root = $static_folder . "/$page_name";
        $language_folder = $root . '/' . $language;
        switch ($page_name) {
            case 'translatoreditorwriter': {
                $full_path = $language_folder .'/'.'translator-home-page.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }
            case 'faq': {
                $full_path = $language_folder .'/'.'faq.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }
            case 'about-us': {
                $full_path = $language_folder .'/'.'about-us.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }
            case 'careers': {
                $full_path = $language_folder .'/'.'careers.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }
            case 'contact': {
                $full_path = $language_folder.'/contact.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                ob_start();
                /** @noinspection PhpIncludeInspection */
                include $full_path;
                $da_page_before = ob_get_clean();
                $da_page = do_shortcode($da_page_before);
                break;
            }
            case 'tos':
                {
                    $skeleton_path = $root . '/skeleton.php';
                    if (!is_readable($skeleton_path)) {
                        throw new RuntimeException("Cannot find static page part $skeleton_path");
                    }
                    $da_page = file_get_contents($skeleton_path);
                    if ($da_page === false) {
                        throw new RuntimeException("Error reading static page part $skeleton_path");
                    }
                    $parts_folder = $root . '/' . $language;
                    if (!is_readable($parts_folder)) {
                        $parts_folder = $root . '/english';
                        if (!is_readable($parts_folder)) {
                            throw new RuntimeException("Cannot find default english parts folder for static $page_name");
                        }
                    }
                    $parts['terms-condition'] = ['filename' => "$parts_folder/terms-condition.php"];
                    $parts['linguist-agreement'] = ['filename' => "$parts_folder/linguist-agreement.php"];
                    $parts['linguist-nda'] = ['filename' => "$parts_folder/linguist-nda.php"];
                    foreach ($parts as $key => &$node) {
                        if (!is_readable($node['filename'])) {
                            throw new RuntimeException("Cannot find static page part" . $node['filename']);
                        }
                        $node['content'] = file_get_contents($node['filename']);
                        if ($node['content'] === false) {
                            throw new RuntimeException("Issue reading static page part " . $node['filename']);
                        }
                        $search_for_this = "<!--section-$key-part-->";
                        $da_page = str_replace($search_for_this, $node['content'], $da_page, $da_count);
                        if ($da_count === 0) {
                            throw new RuntimeException("Could not find $search_for_this in the static page part $skeleton_path");
                        }
                    }
                    break;
                }
            case 'privacy': {
                $full_path = $language_folder .'/'.'privacy.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }

            case 'multilingual-websites': {
                $full_path = $language_folder .'/'.'multilingual-websites.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }

            case 'document-translation': {
                $full_path = $language_folder .'/'.'document-translation.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }

            case 'personal-translation-services': {
                $full_path = $language_folder .'/'.'personal-translation-services.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }

            case 'video-translation-transcription-subtitling': {
                $full_path = $language_folder .'/'.'video-translation-transcription-subtitling.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }

            case 'gaming-translation': {
                $full_path = $language_folder .'/'.'gaming-translation.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }

            case 'technical-translation': {
                $full_path = $language_folder .'/'.'technical-translation.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }

            case 'documentation-writing': {
                $full_path = $language_folder .'/'.'documentation-writing.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }

            case 'article-writing-for-blogs-newsletters': {
                $full_path = $language_folder .'/'.'article-writing-for-blogs-newsletters.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }

            case 'resume-writing': {
                $full_path = $language_folder .'/'.'resume-writing.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }

            case 'editing-proofreading': {
                $full_path = $language_folder .'/'.'editing-proofreading.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }

            case 'press-release-and-speech-writing': {
                $full_path = $language_folder .'/'.'press-release-and-speech-writing.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }

            case 'localization-and-internationalization': {
                $full_path = $language_folder .'/'.'localization-and-internationalization.php';
                if (!is_readable($full_path)) {
                    throw new RuntimeException("Cannot find static page part $full_path");
                }
                $da_page = file_get_contents($full_path);
                return $da_page;
                break;
            }


            default:
                {
                    throw new RuntimeException("Did not recognize $page_name as a static page");
                }
        }

        //find all translatable strings through regular expression and then switch them out
        //will be XXX:.*:
        preg_match_all('/XXX:(.*):/',$da_page,$matches) ;
        for($i = 0; $i < count($matches[0]) && $i < count($matches[1]); $i++) {
            $original = $matches[0][$i];
            $bare = $matches[1  ][$i];
            $replaced = __($bare);
            $da_page = str_replace($original,$replaced,$da_page);
        }


        return $da_page;
    }


    public static function print_standard_page($title,$out) {
        get_header();
        ?>
        <section class="middle-content">
            <div class="container">
                <div class="row">
                    <article id="post-peerok-faq" class="page type-page status-publish hentry">
                        <span class="bold-and-blocking larger-text">
                           <?=$title ?>
                        </span>
                        <?= $out ?>
                    </article>

                </div>
            </div>
        </section>
        <?php get_footer('homepagenew');
    }
}