<?php

/**
 * Class FreelinguistSizeImages
 *
 * Uses Zebra Library, for docs:
 * @see https://stefangabos.github.io/Zebra_Image/Zebra_Image/Zebra_Image.html
 * @see https://github.com/stefangabos/Zebra_Image
 */
class FreelinguistSizeImages extends FreelinguistDebugging {
    //inherited from debugging and need to be overwritten
    protected static $n_debug_level = self::LOG_ERROR;
    protected static $b_debug = false;

    const TINY_WIDTH = 55;
    const SMALL_WIDTH = 265;
    const LARGE_WIDTH = 500;


    const TINY = 'tiny';
    const SMALL = 'small';
    const LARGE = 'large';
    const CROPPED = 'cropped';

    const ALL_SIZES = [
        self::TINY => self::TINY_WIDTH,
        self::SMALL => self::SMALL_WIDTH,
        self::LARGE => self::LARGE_WIDTH,
        self::CROPPED => -1
    ];

    const RATIO_HEIGHT_DIVIDED_BY_WIDTH = 176/270 ; // 3/4;

    const RATIO_WIDTH_DIVIDED_BY_HEIGHT = 1.0/self::RATIO_HEIGHT_DIVIDED_BY_WIDTH;

    const ALLOWABLE_IMAGE_TYPES = [
        //IMAGETYPE_GIF,
        IMAGETYPE_JPEG,
        IMAGETYPE_PNG
    ];

    const BACKGROUND_COLOR = -1; //do -1 for keeping transparent , and otherwise make background color white, else put in an hex color

    const MISSING_IMAGE_FRAGMENT = '/images/for-missing/no-image-?.gif';


    protected $path_to_original_image = '';

    protected $width_original_image = 0;
    protected $height_original_image = 0;

    protected $path_to_cropped_image = '';

    protected $paths = [];

    public function get_paths() {return $this->paths;}

    /**
     * Given a url fragment, relative to the upload directory
     * will return the default image placeholder of that size, if the partial path is empty
     * @param string $partial_image_path
     * @param $size
     * @param bool $b_use_default_on_empty
     * @return string
     */
    public static function get_url_from_relative_to_upload_directory($partial_image_path,$size,$b_use_default_on_empty) {
        if (empty($partial_image_path)) {
            if ($b_use_default_on_empty) {
                return FreelinguistSizeImages::get_url_or_empty_string_from_path('',$size,$b_use_default_on_empty,false);
            } else {
                return '';
            }

        }

        $wp_upload_dir      = wp_upload_dir();
        $base               = $wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR;
        $calculated_path          = $base.$partial_image_path;
        return FreelinguistSizeImages::get_url_or_empty_string_from_path($calculated_path,$size,$b_use_default_on_empty,false);
    }

    public static function  abs_path_to_url( $path = '' ) {
        $url = str_replace(
                wp_normalize_path( untrailingslashit( ABSPATH ) ),
                site_url(),
                wp_normalize_path( $path )
            );
        return esc_url_raw( $url );
    }

    public static function get_url_or_empty_string_from_path($file_path,$size_name,$b_use_default_on_empty,$b_throw_me = false) {
        $maybe_file = static::get_path_or_empty_string_from_path($file_path,$size_name,$b_throw_me);
        if ($maybe_file) {
            return static::abs_path_to_url($maybe_file);
        }
        if ($b_use_default_on_empty) {
            $safe_name = strtolower(trim($size_name)); //invalid size can be here if no exception wanted
            if (array_key_exists($safe_name,static::ALL_SIZES)) {
                $fragment = str_replace('?', $size_name, static::MISSING_IMAGE_FRAGMENT);
                $url_out = get_template_directory_uri() . $fragment;
                return $url_out;
            }
        }
        return '';
    }

    /**
     * if a size variant asked for exists, will return that file path
     * @param string $file_path
     * @param string $size_name tiny|small|large
     * @param bool $b_throw_me default false
     * @return string
     */
    public static function get_path_or_empty_string_from_path($file_path, $size_name, $b_throw_me = false) {

        if (!$file_path) {
            if ($b_throw_me) {
                throw new RuntimeException("Empty File given for get_path_or_empty_string_from_path");
            }
            return '';
        }

        $safe_name = strtolower(trim($size_name));
        if (!array_key_exists($safe_name,static::ALL_SIZES)) {
            throw new RuntimeException("Did not recognized the size name of '$size_name' : need ".
                implode('|',array_keys(static::ALL_SIZES)));
        }

        //only check if derived path exists
        $path = pathinfo($file_path);

        $nu_name = $path['dirname'].DIRECTORY_SEPARATOR.$path['filename'].'-'.$size_name.'.'.$path['extension'];

        $checked_path = realpath($nu_name);
        if (!$checked_path) {
            if ($b_throw_me) {
                throw new RuntimeException("File at $file_path does not exist");
            }
            return '';
        }
        if (!is_readable($checked_path)) {
            if ($b_throw_me) {
                throw new RuntimeException("Cannot read file at $checked_path");
            }
            return '';
        }

        return $checked_path;

    }

    public static function remove_associated_sizes_from_original_path($file_path) {

        $log = [];

        foreach (static::ALL_SIZES as $size_name => $size_in_pixels) {
            $maybe_file = static::get_path_or_empty_string_from_path($file_path,$size_name);
            if ($maybe_file) {
                if (!is_writable($maybe_file)) {
                    $log[] = "Cannot write to file of $maybe_file";
                    continue;
                }
                //allow wp hooks a chance to do extra cleanup
                 wp_delete_file($maybe_file);
            }

            if (!empty($log)) {
                static::log(static::LOG_ERROR, 'Issues with deleting files',$log);
            }
        }
    }

    public function __destruct()
    {
        //not used right now
    }

    /**
     * FreelinguistSizeImages constructor.
     * @param string $path_to_original_image
     * @throws Exception
     */
    public function __construct($path_to_original_image)
    {
        $this->paths = [];
        try {
            $this->path_to_original_image = realpath($path_to_original_image);
            if (!$this->path_to_original_image) {
                throw new InvalidArgumentException("FreelinguistSizeImages: File not found:$path_to_original_image ");
            }

            $size = getimagesize($this->path_to_original_image); // 0 = width, 1 = height, 2 = type

            $this->width_original_image = $size[0];
            $this->height_original_image = $size[1];

            // check to make sure source image is in allowable format
            if(!in_array($size[2], static::ALLOWABLE_IMAGE_TYPES)) {
                //assumes a more rigorous check already done for non image files
                throw new InvalidArgumentException("Image is not a png,gif, or png");
            }

            $this->do_crop();

            foreach (static::ALL_SIZES as $size_name => $size_in_pixels) {
                if ($size_in_pixels < 0) {continue;}
                $this->do_resize($size_in_pixels, $size_name);
            }

            static::log(static::LOG_DEBUG, 'paths are',$this->paths);
        } catch (Exception $e) {
            $out_message = will_get_exception_string($e);
            static::log(static::LOG_ERROR, $out_message);
            throw $e;
        }

    }

    protected function do_crop() {
        /*
        Length:Width: Ratio=  L0:W0= 4:3;
            A use uploads a picture of L1 in length, and W1 in width.
                We only need to keep it’s center area.
        So:
            If L1/W1> 4/3   // this picture is too long, remove left and right side
                L2  =  W1 * Ratio;  // L0/W0;  // this is the final length we need
                CropL= L1-L2;
        CropLeft (0: CropL /2);
                CropRight (  L1- CropL /2 :L1);
            Else     // this picture is too high, remove top and bottom
                W2  =  L1/Ratio; // * W0/L0;  // this is the final length we need
                CropW= W1-W2;
        CropTop (0: CropW /2);
                CropBottom (  W1- CropW /2 :W1);
            End

         */


        try {
            // instantiate the class
            $img = new Zebra_Image();

            // a source image
            $img->source_path = $this->path_to_original_image;

            // path to where should the resulting image be saved
            // note that by simply setting a different extension to the file will
            // instruct the script to create an image of that particular type

            $path = pathinfo($this->path_to_original_image);

            $this->path_to_cropped_image = $path['dirname'].DIRECTORY_SEPARATOR.$path['filename'].'-'.static::CROPPED.'.'.$path['extension'];

            $img->target_path = $this->path_to_cropped_image;

            /*
             * walk-through by example
             *   we have a width (x axis) of 100, and a height (y axis) of 150
             *   our width/height ratio is 4/3  so our RATIO_HEIGHT_DIVIDED_BY_WIDTH = 3/4 and our RATIO_WIDTH_DIVIDED_BY_HEIGHT = 4/3
             *
             *   so, is 150/100 > 3/4  ?  yes
             *     $desired_height = 100 * (3/4) = 75
             *     $total_height_to_remove = 150 - 75 = 75;
             *     $height_to_remove_each_side = floor(37.5) = 37
             *
             *     so we have a new image of
             *     $start_x = 0;
                    $start_y = 37;
                    $end_x = 99;
                    $end_y = 150 - 1 - 37; = 112

                     which makes our new height of 75 and our new width of 100, whose ratio is 100/75 = 1.333 = 4/3
                    so that works




                Now we reverse this
                *   we have a width (x axis) of 150, and a height (y axis) of 100
                *   our width/height ratio is 4/3  so our RATIO_HEIGHT_DIVIDED_BY_WIDTH = 3/4 and our RATIO_WIDTH_DIVIDED_BY_HEIGHT = 4/3
                 *    so, is 100/150 > 3/4  ?  no
                 *      we are doing the else now
                 *      $desired_width = (int)floor($this->height_original_image * static::RATIO_WIDTH_DIVIDED_BY_HEIGHT);
                 *      $desired_width = Floor(100 * 4/3) = 133  ( because 133/100 = the 4/3 ratio we want)
                 *      $total_width_to_remove = $this->width_original_image - $desired_width;
                 *       $total_width_to_remove = 150 - 133 = 17
                 *       $width_to_remove_each_side = floor(17/2) = 8
                 *
                 *  new image of
                 *      $start_x = $width_to_remove_each_side = 8;
                        $start_y = 0;
                        $end_x = $this->width_original_image - 1  - $width_to_remove_each_side = 150 - 1 - 8 = 141;
                        $end_y = $this->height_original_image - 1; = 100 - 1 = 99
                 *
                 *      which makes our new height of  100
                 *        and our new width of  (141 - 8) = 133
                 *      , whose ratio is     133/100  = 4/3
                 *
                 */


            if (($this->height_original_image/$this->width_original_image) > static::RATIO_HEIGHT_DIVIDED_BY_WIDTH)
            {
                //too high
                $desired_height = (int)floor($this->width_original_image * static::RATIO_HEIGHT_DIVIDED_BY_WIDTH);
                $total_height_to_remove = $this->height_original_image - $desired_height;
                $height_to_remove_each_side = (int)floor($total_height_to_remove/2);
                $start_x = 0;
                $start_y = $height_to_remove_each_side;
                $end_x = $this->width_original_image - 1;
                $end_y = $this->height_original_image - 1 - $height_to_remove_each_side;

            } else {
                //too wide, or if just right will not matter

                $desired_width = (int)floor($this->height_original_image * static::RATIO_WIDTH_DIVIDED_BY_HEIGHT);
                $total_width_to_remove = $this->width_original_image - $desired_width;
                $width_to_remove_each_side = (int)floor($total_width_to_remove/2);
                $start_x = $width_to_remove_each_side;
                $start_y = 0;
                $end_x = $this->width_original_image - 1  - $width_to_remove_each_side;
                $end_y = $this->height_original_image - 1;

            }

            $img->crop (  $start_x ,  $start_y ,  $end_x ,  $end_y  );
            $this->throw_if_image_error($img);
            $this->paths[static::CROPPED] = $this->path_to_cropped_image;
        } catch (Exception $e ) {
            throw new RuntimeException("Error in Cropping: ". will_get_exception_string($e),$e->getCode(),$e);
        }

    }

    /**
     * @param int $max_width
     * @param string $add_to_name
     * @throws RuntimeException
     */
    protected function do_resize($max_width,$add_to_name) {

        if (empty($this->path_to_cropped_image)) {
            throw new RuntimeException("Do Cropped first");
        }
        $max_width = (int) $max_width;
        $max_hight = (int)floor(static::RATIO_HEIGHT_DIVIDED_BY_WIDTH * $max_width);

        $path = pathinfo($this->path_to_original_image);

        $nu_name = $path['dirname'].DIRECTORY_SEPARATOR.$path['filename'].'-'.$add_to_name.'.'.$path['extension'];

        // create a new instance of the class
        $image = new Zebra_Image();

        // if you handle image uploads from users and you have enabled exif-support with --enable-exif
        // (or, on a Windows machine you have enabled php_mbstring.dll and php_exif.dll in php.ini)
        // set this property to TRUE in order to fix rotation so you always see images in correct position
        $image->auto_handle_exif_orientation = false;

        // indicate a source image (a GIF, PNG, JPEG or WEBP file)
        $image->source_path = $this->path_to_cropped_image;

        // indicate a target image
        // note that there's no extra property to set in order to specify the target
        // image's type -simply by writing '.jpg' as extension will instruct the script
        // to create a 'jpg' file
        $image->target_path = $nu_name;

        // maybe have a jpeg file, let's set the output
        // image's quality
        $image->jpeg_quality = 100;

        // some additional properties that can be set
        // read about them in the documentation
        $image->preserve_aspect_ratio = true;
        $image->enlarge_smaller_images = true;
        $image->preserve_time = true;
        $image->handle_exif_orientation_tag = true;

        // resize the image to exactly 100x100 pixels by using the "crop from center" method
        // (read more in the overview section or in the documentation)
        //  and if there is an error, check what the error is about
        if (!$image->resize($max_width, $max_hight, ZEBRA_IMAGE_BOXED, static::BACKGROUND_COLOR)) {

            $this->throw_if_image_error($image);

        // if no errors
        } else {
            $this->paths[$add_to_name] = $nu_name;
        }

    }

    /**
     * @param Zebra_Image $image
     * @throws RuntimeException
     */
    protected function throw_if_image_error($image) {
        // if there was an error, let's see what the error is about
        switch ($image->error) {
            case 0:
                return ;
            case 1:
                throw new RuntimeException( 'Source file could not be found!');
            case 2:
                throw new RuntimeException( 'Source file is not readable!');
            case 3:
                throw new RuntimeException( 'Could not write target file!');
            case 4:
                throw new RuntimeException( 'Unsupported source file format!');
            case 5:
                throw new RuntimeException( 'Unsupported target file format!');
            case 6:
                throw new RuntimeException( 'GD library version does not support target file format!');
            case 7:
                throw new RuntimeException( 'GD library is not installed!');
            case 8:
                throw new RuntimeException( '"chmod" command is disabled via configuration!');
            case 9:
                throw new RuntimeException( '"exif_read_data" function is not available');
            default:
                throw new RuntimeException("Unknown Error for Zebra Library");

        }
    }




}

FreelinguistSizeImages::turn_on_debugging(FreelinguistDebugging::LOG_WARNING);