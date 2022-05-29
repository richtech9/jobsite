<?php

use League\MimeTypeDetection\GeneratedExtensionToMimeTypeMap;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\FpdiException;
/*
  * current-php-code 2020-Oct-31
  * internal-call
  * input-sanitized :
  */

class FileUploadWhitelistException extends RuntimeException {}
/**
 * this class should be used for all the file upload ajax to use (they have different needs for different files)
 * There will be constants defined by the class which represent each type of thing it can check
 * The code using this class will enter an array of constants, and the file to check,
 * and the validator will return true or false, the mime type, and the correct extension
 * (because some file uploads use the wrong or even misleading extension)
 * it will  be up to the calling code to alter any wrong extensions
 *
 * @see https://github.com/thephpleague/mime-type-detection for the library to check mime types
 */
class FileUploadWhitelist extends  FreelinguistDebugging {
    /*
     * current-php-code 2020-Oct-28
     * internal-call
     * input-sanitized :
     */

    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;

    //list of recognized types we can deal with
    const TYPE_TEXT_PLAIN = 'TYPE_TEXT_PLAIN';
    const TYPE_TEXT_HTML = 'TYPE_TEXT_HTML';
    const TYPE_TEXT_XC = 'TYPE_TEXT_XC';
    const TYPE_TEXT_X_PHP = 'TYPE_TEXT_X_PHP';
    const TYPE_TEXT_X_PYTHON = 'TYPE_TEXT_X_PYTHON';
    const TYPE_TEXT_X_ALGOL68 = 'TYPE_TEXT_X_ALGOL68';
    const TYPE_TEXT_X_CPLUSPLUS = 'TYPE_TEXT_X_CPLUSPLUS';
    const TYPE_TEXT_X_RUBY = 'TYPE_TEXT_X_RUBY';
    const TYPE_TEXT_X_LISP = 'TYPE_TEXT_X_LISP';
    const TYPE_TEXT_X_OBJ_C = 'TYPE_TEXT_X_OBJ_C';
    const TYPE_TEXT_X_PERL = 'TYPE_TEXT_X_PERL';

    const PLAIN_TEXT_MIME_TYPE = 'text/plain';
    const HTML_TEXT_MIME_TYPE = 'text/html';

    const TYPE_TEXT_ANY = 'TYPE_TEXT_ANY'; # for allowing programming files to be uploaded, which have different text types
    const TYPE_TEXT_ANY_ALLOWED_MIMES = [
        self::TYPE_TEXT_HTML, self::TYPE_TEXT_PLAIN,self::TYPE_TEXT_XC,self::TYPE_TEXT_X_PHP,
        self::TYPE_TEXT_X_PYTHON,self::TYPE_TEXT_X_ALGOL68,self::TYPE_TEXT_X_CPLUSPLUS,self::TYPE_TEXT_X_RUBY,
        self::TYPE_TEXT_X_LISP,self::TYPE_TEXT_X_OBJ_C,self::TYPE_TEXT_X_PERL

    ]; //some plain text files have a different mime, if so, put them here, if not allowed then make entry in tolerated


    const TYPE_WORDPRESS_FALLBACK = 'TYPE_WORDPRESS_FALLBACK'; # if not on the list, then have WP take a look and approve or disapprove  //see https://wpengine.com/support/mime-types-wordpress

    const TYPE_PDF = 'TYPE_PDF';

    const TYPE_IMAGE_PNG = 'TYPE_IMAGE_PNG';
    const TYPE_IMAGE_JPG = 'TYPE_IMAGE_JPG';
    const TYPE_IMAGE_GIF = 'TYPE_IMAGE_GIF';


    const TYPE_AUDIO_MP3 = 'TYPE_AUDIO_MP3';
    const TYPE_AUDIO_WMV = 'TYPE_AUDIO_WMV';
    
    const TYPE_VIDEO_AVI = 'TYPE_VIDEO_AVI';
    const TYPE_VIDEO_FLV = 'TYPE_VIDEO_FLV';
   
    const TYPE_VIDEO_MP4 = 'TYPE_VIDEO_MP4';
    const TYPE_VIDEO_MOV = 'TYPE_VIDEO_MOV';
    const TYPE_VIDEO_3GP = 'TYPE_VIDEO_3GP';

    const TOLERATED_TYPES = [
      self::TYPE_TEXT_HTML => self::HTML_TEXT_MIME_TYPE,
      self::TYPE_TEXT_XC => 'text/x-c',
      self::TYPE_TEXT_X_PHP => 'text/x-php',
      self::TYPE_TEXT_X_PYTHON => 'text/x-python',
      self::TYPE_TEXT_X_PYTHON => 'text/x-Algol68',
      self::TYPE_TEXT_X_CPLUSPLUS =>  'text/x-c++',
      self::TYPE_TEXT_X_RUBY =>  'text/x-ruby',
      self::TYPE_TEXT_X_LISP =>  'text/x-lisp',
      self::TYPE_TEXT_X_OBJ_C =>  'text/x-objective-c',
      self::TYPE_TEXT_X_PERL =>  'text/x-perl',

    ];

    const ALLOWED_TYPES = [
        'TYPE_AUDIO_MP3' => 'audio/mpeg',
        'TYPE_AUDIO_WMV' => 'audio/x-wav',

        'TYPE_PDF' => 'application/pdf',

        'TYPE_TEXT_PLAIN' => 'text/plain',

        'TYPE_IMAGE_PNG' => 'image/png',
        'TYPE_IMAGE_JPG' => 'image/jpeg',
    //    'TYPE_IMAGE_GIF' => 'image/gif',


        'TYPE_VIDEO_AVI' => 'video/x-msvideo',
        'TYPE_VIDEO_FLV' => 'video/x-flv',
        'TYPE_VIDEO_MP4' => 'video/mp4',
        'TYPE_VIDEO_MOV' => 'video/quicktime',
        'TYPE_VIDEO_3GP' => 'video/3gpp',

    ];
    /*

     */

    //collections

    const IMAGE_TYPES = 'IMAGE_TYPES';
    const TEXT_TYPES = 'TEXT_TYPES';
    const VIDEO_TYPES = 'VIDEO_TYPES';

    const COLLECTIONS = [

        'IMAGE_TYPES' => [
            self::TYPE_IMAGE_JPG,
            self::TYPE_IMAGE_PNG,
         //   self::TYPE_IMAGE_GIF
        ],

        'TEXT_TYPES' => [
            self::TYPE_TEXT_PLAIN
        ],
        
        'VIDEO_TYPES' => [
            self::TYPE_AUDIO_MP3,
            self::TYPE_AUDIO_WMV,

            self::TYPE_VIDEO_AVI,
            self::TYPE_VIDEO_FLV,
            self::TYPE_VIDEO_MP4,
            self::TYPE_VIDEO_MOV,
            self::TYPE_VIDEO_3GP,

        ]
    ];



    protected const INCONCLUSIVE_MIME_TYPES = ['application/x-empty', self::PLAIN_TEXT_MIME_TYPE, 'text/x-asm'];




    /**
     * @param string $full_file_path , the complete and full file path to check, must exist and be readable or exception thrown
     *
     * @param string[] $type_array, zero or more constants from the ALLOWED_TYPES or COLLECTIONS. Empty means use all
     *                      adding strings that are not in the two constant arrays will cause an exception to be thrown
     *
     *
     * @param string $mime_type INOUT REF , the mime type as found
     *
     * @param string $secondary_check , when failing the primary check, the secondary check is to find the mime via file extension
     *                                   sometimes the path does not have an extension, above,
     *                                   if need to use a different string than the path,when getting the mime type from the path, put  it here
     *                                   leave it blank initially to just check the path
     *
     * @param string $valid_extension OUTREF , the extension that is expected to go with the mime type. If extension does not exist, will be false
     *
     * @param string $copied_content_file_path OUTREF, for some mime types, the content will be copied to strip out some things
     *                                                  this is set to an empty string if there is no copying
     *                                                  but will be a temp file that will need to be released by the caller if it is copied
     *                                                  the temp file will not have an extension
     *
     * @return bool if true, then the file passes the filters, if false, then do not use this file
     * @throws FileUploadWhitelistException
     */
    public static function validate_file(string $full_file_path,  $secondary_check ,array $type_array,
                                         &$mime_type, &$valid_extension,&$copied_content_file_path) : bool {

        try {
            static::log(static::LOG_DEBUG,'validate-file: start ',[
                '$full_file_path' => $full_file_path,
                '$secondary_check' => $secondary_check,
                '$type_array' => $type_array
            ]);
            $copied_content_file_path = '';
            $valid_extension = '';
            $full_file_path = trim($full_file_path);
            if (!$full_file_path || !is_readable($full_file_path)) {
                throw new InvalidArgumentException("FileUploadWhitelist constructor cannot read the file of '$full_file_path' ");
            }

            $list_allowed_types = [];
            $collection_types = array_keys(static::COLLECTIONS);
            $single_types = array_keys(static::ALLOWED_TYPES);
            $b_flag_text_type_any = false;
            $b_flag_wordpress_fallback = false;
            if (empty($type_array)) {
                $list_allowed_types = $single_types;
            } else {
                foreach ($type_array as $what) {
                    if (in_array($what, $collection_types)) {
                        $list_allowed_types = array_merge($list_allowed_types, static::COLLECTIONS[$what]);
                    } else {
                        if (in_array($what, $single_types)) {
                            array_push($list_allowed_types,$what);
                        } else {
                            if ($what === static::TYPE_TEXT_ANY) {
                                $b_flag_text_type_any = true;
                                foreach (static::TYPE_TEXT_ANY_ALLOWED_MIMES as $ok_text_mime) {
                                    if (!in_array($ok_text_mime,$list_allowed_types)) {
                                        $list_allowed_types[] = $ok_text_mime;
                                    }
                                }

                            } else if ($what === static::TYPE_WORDPRESS_FALLBACK) {
                                $b_flag_wordpress_fallback = true;
                            } else {
                                throw new InvalidArgumentException("FileUploadWhitelist constructor does not recognize" .
                                    " '$what' as a valid type or collection name'");
                            }

                        }
                    }

                }
            } //end building the mime type array
            $const_allowed_types = $list_allowed_types;
            $list_allowed_types = [];
            foreach ($const_allowed_types as $const_type) {
                if (array_key_exists($const_type,static::ALLOWED_TYPES)) {
                    $list_allowed_types[] = static::ALLOWED_TYPES[$const_type];
                } else if (array_key_exists($const_type,static::TOLERATED_TYPES)) {
                    $list_allowed_types[] = static::TOLERATED_TYPES[$const_type];
                }

            }
            static::log(static::LOG_DEBUG,'validate-file: allowed types ',$list_allowed_types);


            $detector = new League\MimeTypeDetection\FinfoMimeTypeDetector();
            $mime_type = $detector->detectMimeTypeFromFile($full_file_path);

            $b_any_text_logic_test = false;
            foreach (static::TYPE_TEXT_ANY_ALLOWED_MIMES as $mime_flag) {

                if (array_key_exists($mime_flag,static::ALLOWED_TYPES)) {
                    $test_with_mime = static::ALLOWED_TYPES[$mime_flag];
                    //will_send_to_error_log("ALLOWED : testing '$mime_type' with '$test_with_mime'");
                    if ($mime_type === $test_with_mime) {
                        $b_any_text_logic_test = true;
                        break;
                    }
                } else if (array_key_exists($mime_flag,static::TOLERATED_TYPES)) {
                    $test_with_mime = static::TOLERATED_TYPES[$mime_flag];
                   // will_send_to_error_log("TOLERATED : testing '$mime_type' with '$test_with_mime'");
                    if ($mime_type === $test_with_mime) {
                        $b_any_text_logic_test = true;
                        break;
                    }
                }
            }

            static::log(static::LOG_DEBUG,'validate-file: first mime type ',
                [
                    'mime' =>$mime_type,
                    'inclusive_array' => self::INCONCLUSIVE_MIME_TYPES,
                    'is_inconclusive' => in_array($mime_type, self::INCONCLUSIVE_MIME_TYPES),
                    'is_tolerated' => in_array($mime_type, self::TOLERATED_TYPES),
                    '$b_flag_text_type_any' => $b_flag_text_type_any,
                    'any text logic test' => $b_any_text_logic_test


            ]);

            $use_this_mime_type_with_deepchecking = $mime_type;


            if (
                $mime_type &&
                in_array($mime_type, self::INCONCLUSIVE_MIME_TYPES) &&
                !$b_any_text_logic_test
            ) {
                $mime_type = null;
                static::log(static::LOG_DEBUG,'validate-file: resetting mime type ',[
                    '$mime_type' => $mime_type,
                    '$use_this_mime_type_with_deepchecking' => $use_this_mime_type_with_deepchecking
                ]);
            }
            if (!$mime_type) {
                $what_to_check = $secondary_check;
                if (!$what_to_check) {
                    $what_to_check = $full_file_path;
                }
                $mime_type_guessed_from_path = $detector->detectMimeTypeFromPath($what_to_check);
                static::log(static::LOG_DEBUG,'validate-file: getting secondary mime type ',[
                    'first mime type' => $mime_type,
                    'path mime type' => $mime_type_guessed_from_path,
                    'path we are checking' => $what_to_check
                ]);

                $mime_type = $mime_type_guessed_from_path;
                if (!$use_this_mime_type_with_deepchecking) {
                    $use_this_mime_type_with_deepchecking = $mime_type;
                }

            }
            static::log(static::LOG_DEBUG,'validate-file: final mime types ',[$mime_type,$use_this_mime_type_with_deepchecking]);

            if (!$mime_type) {
                static::log(static::LOG_DEBUG,'validate-file: returning false becausee no mime type  ');
                return false;
            }

            //get the extension
            $mime_list = GeneratedExtensionToMimeTypeMap::MIME_TYPES_FOR_EXTENSIONS;
            $valid_extension = array_search($mime_type, $mime_list);
            static::log(static::LOG_DEBUG,'validate-file: valid extension is  ',$valid_extension);

            if (!in_array($mime_type, $list_allowed_types)) {
                if ($b_flag_wordpress_fallback) {
                    static::log(static::LOG_DEBUG,"validate-file: Using Wordpress fallback of $mime_type");

                    $wp_filetype     = wp_check_filetype_and_ext($full_file_path, $secondary_check ); //code-notes did not put in mimes param as we want to allow all upload types on this WP
                    $ext             = empty( $wp_filetype['ext'] ) ? '' : $wp_filetype['ext'];
                    $type            = empty( $wp_filetype['type'] ) ? '' : $wp_filetype['type'];
                    $proper_filename = empty( $wp_filetype['proper_filename'] ) ? '' : $wp_filetype['proper_filename'];
                    //task-future-work, not needed now, but maybe later move file to $proper_filename and set that to $copied_content_file_path
                    if (  ! $type || ! $ext ) {
                        static::log(static::LOG_DEBUG,"validate-file: Wordpress does not like this file either, so rejected");
                        return false;
                    } else {
                        static::log(static::LOG_DEBUG,"validate-file: Wordpress Likes it, so trusting this and returning true now",[
                            '$proper_filename' => $proper_filename
                        ]);
                        return true;
                    }
                } else {
                    static::log(static::LOG_DEBUG,"validate-file: Rejecting mime type of $mime_type as not on list and no fallback");
                    return false;
                }

            }

            switch ($use_this_mime_type_with_deepchecking) {
                case 'image/jpeg' :
                    {
                        return static::validate_image_jpg($full_file_path);
                    }
                case 'image/png' :
                    {
                        return static::validate_image_png($full_file_path);
                    }
                case 'image/gif' :
                    {
                        return static::validate_image_gif($full_file_path);
                    }
                case 'text/x-c':
                case 'text/x-php':
                case 'text/x-python':
                case 'text/x-Algol68':
                case 'text/x-c++':
                case 'text/x-ruby':
                case 'text/x-lisp':
                case 'text/x-objective-c':
                case 'text/x-perl':
                case 'text/plain' :
                    {
                        return static::validate_text_plain($full_file_path);
                    }
                case 'text/html' :
                    {
                        return static::validate_text_html($full_file_path);
                    }
                case 'application/pdf':
                    {
                        $b_ok =  static::validate_and_copy_pdf($full_file_path, $copied_content_file_path);
                        static::log(static::LOG_DEBUG,'validate-file: got temp file of  ',$copied_content_file_path);
                        return $b_ok;
                    }
                case 'audio/mpeg':
                case 'audio/x-wav':
                case  'video/x-msvideo':
                case 'video/x-flv':
                case  'video/mp4':
                case  'video/quicktime':
                case  'video/3gpp': {
                    return true; //no validation now
                }
                default :
                    {
                        throw new LogicException("FileUploadWhitelist constructor does not have a validation for '$use_this_mime_type_with_deepchecking'");
                    }
            }
        } catch (InvalidArgumentException $ea) {
            static::log(static::LOG_WARNING,'validate-file',will_get_exception_string($ea));
            throw  $ea;
        } catch(LogicException $el) {
            static::log(static::LOG_WARNING,'validate-file',will_get_exception_string($el));
            throw  $el;
        }

    }

    public static function validate_image_png(string $full_file_path) :bool {
        $im = @imagecreatefrompng($full_file_path);
        static::log(static::LOG_DEBUG,'validate_image_png  ',$im);
        if ($im) {
            static::log(static::LOG_DEBUG,'validate_image_png returning true ');
            return true;
        }
        static::log(static::LOG_DEBUG,'validate_image_png returning false ');
        return false;
    }

    public static function validate_image_gif(string $full_file_path) :bool {
        $im = @imagecreatefromgif($full_file_path);
        static::log(static::LOG_DEBUG,'validate_image_gif  ',$im);
        if ($im) {
            static::log(static::LOG_DEBUG,'validate_image_gif returning true ');
            return true;
        }
        static::log(static::LOG_DEBUG,'validate_image_gif returning false ');
        return false;
    }

    public static function validate_image_jpg(string $full_file_path) :bool {
        $im = @imagecreatefromjpeg($full_file_path);
        static::log(static::LOG_DEBUG,'validate_image_jpg  ',$im);
        if ($im) {
            static::log(static::LOG_DEBUG,'validate_image_jpg returning true ');
            return true;
        }

        static::log(static::LOG_DEBUG,'validate_image_jpg returning false ');
        return false;
    }

    public static function validate_text_plain(string $full_file_path) :bool {
        will_do_nothing($full_file_path);
        static::log(static::LOG_DEBUG,'validate_text_plain returning true ');
        return true; //at this point we are not deep checking text files if it already passed the first validation
    }

    public static function validate_text_html(string $full_file_path) :bool {
        will_do_nothing($full_file_path);
        static::log(static::LOG_DEBUG,'validate_text_html returning true ');
        return true; //at this point we are not deep checking text files if it already passed the first validation
    }

    /**
     * string @param $full_file_path
     * string @param  string $copied_content_file_path OUTREF, if valid pdf this is the temp file the copied pdf is created in
     *                                                      calling code needs to unlink when done
     *                                                   else this is set to empty string
     * @return bool
     * @throws FileUploadWhitelistException if cannot create the temp file
     */
    public static function validate_and_copy_pdf($full_file_path,&$copied_content_file_path) :bool {
        $copied_content_file_path = '';
        try {
            static::log(static::LOG_DEBUG,'validate_and_copy_pdf start ');
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($full_file_path);
            static::log(static::LOG_DEBUG,'validate_and_copy_pdf page count ',$pageCount);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                // import a page
                $templateId = $pdf->importPage($pageNo);
                $s = $pdf->getTemplatesize($templateId);
                $pdf->AddPage($s['orientation'], $s);
                // use the imported page and adjust the page size
                $pdf->useTemplate($templateId);
                static::log(static::LOG_DEBUG,'validate_and_copy_pdf doing it ',[
                    $pageNo,$templateId,$s
                ]);

            }
            //create temp file path
            $copied_content_file_path = tempnam(sys_get_temp_dir(), 'pdf');
            static::log(static::LOG_DEBUG,'validate_and_copy_pdf temp file ',$copied_content_file_path);
            if ($copied_content_file_path === false) {
                $copied_content_file_path = '';
                throw new FileUploadWhitelistException("FileUploadWhitelist cannot create temporay file for pdf");
            }
            $pdf->Output($copied_content_file_path, 'F');
            return true;
        }
        catch (FpdiException $pe) {
            if ($copied_content_file_path) {unlink($copied_content_file_path); }
            static::log(static::LOG_ERROR,'fpdi',will_get_exception_string($pe));
        }
        catch (FileUploadWhitelistException $me) {
            static::log(static::LOG_ERROR,'temp-file',will_get_exception_string($me));
            throw $me;
        }
        catch (Exception $e) {
            if ($copied_content_file_path) {unlink($copied_content_file_path);}
            static::log(static::LOG_ERROR,'validate-and-copy',will_get_exception_string($e));
        }
        return false;
    }



}

FileUploadWhitelist::turn_on_debugging(FreelinguistDebugging::LOG_WARNING);
