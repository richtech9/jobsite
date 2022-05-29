<?php
/*
Template Name: Old Registration Template
*/
ob_start();
//code-bookmark Here is the registration template

/*
* current-php-code 2020-Oct-16
* input-sanitized :
* current-wp-template:  create new user, register
 * current-wp-top-template
*/

$key = FLInput::get('key');

if(is_user_logged_in()){
    wp_redirect(home_url());
    exit;
}
get_header('register');
?>
    <style>
        .row.language_drop {
            margin-left: 20%;
            margin-top: 21px;
            margin-right:0px !important;
        }
    </style>
<?php
$_SESSION['social_user_type'] = 'customer';
?>
    <script type="text/javascript">
        jQuery(function(){
            jQuery(".usr_type").change(function(){
                var data = jQuery("#linguist-registration").serializeArray();
                data.push({'name': 'action', 'value': 'socialUserTypeLoginChange'});
                jQuery.ajax({
                    type: 'POST',
                    url: adminAjax.url,
                    data: data,
                    global: false,
                    success: function(response){
                        console.log(response);

                    }
                });
            });
        });
    </script>
    <div class="register-page">
        <div class="form-container">
            <div class="row language_drop">
                <?php if( $_SERVER['SERVER_NAME'] != 'www.wenren8.com'){ ?>
                    <?php dynamic_sidebar('language_st'); ?>
                <?php } ?> <?php //print_r($_SESSION); ?>
            </div>
            <div class="vertical-alignment-helper">
                <div class="access-form signup-popup vertical-align-center">
                    <div class="modal-content">
                        <div class="modal-header larger-text">
                            <div class="modal-brandname">
                                <a href="<?php bloginfo('url'); ?>"><img class="freelinguist-max-width" src="<?php echo get_logo_by_current_language(); ?>"  ></a>
                            </div>
                        </div>
                        <div class="modal-body">
                            <label id="success_message" style=""></label>
                            <label id="error_message" style="color:red"></label>
                            <form id="linguist-registration" class="signup-form  text-regular">
                                <div class="input-group checkbox">
                                    <label><input  style=""   type="radio" class="usr_type" name="usr_type" value="customer"><span><?php get_custom_string('Customer'); ?> <span></label>
                                    <label><input style="" type="radio"  name="usr_type"  class="usr_type" checked="checked"  value="translator"><span><?php get_custom_string('Freelancer'); ?></span></label>
                                    <label id="usr_type-error" class="error" for="usr_type"></label>
                                </div>
                                <div class="input-group">
                                    <input type="text" name="email" id="ucheck_email" class="form-control" placeholder="<?php get_custom_string('Email Address'); ?> ">
                                </div>
                                <div class="input-group">
                                    <input type="password" id="upassword" name="password" class="form-control" placeholder="<?php get_custom_string('Password'); ?> ">
                                </div>
                                <button type="submit" class="btn blue-btn signup-btn enhanced-text"><?php get_custom_string('Sign Up'); ?></button>
                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <label id="agree-error" class="error" for="agree"></label>
                                            <input  title="Agree to Terms" checked type="checkbox" name="agree">
                                            <span></span>
                                            <?php get_custom_string('I agree with website'); ?>
                                            <!--suppress HtmlUnknownTarget -->
                                            <a href="../terms-of-service/">
                                                <?php get_custom_string('Terms'); ?>
                                            </a>
                                            <?php get_custom_string('and'); ?>
                                            <!--suppress HtmlUnknownTarget -->
                                            <a href="../privacy-peerok">
                                                <?php get_custom_string('Privacy Policy'); ?>
                                            </a>
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <h6 ><?php get_custom_string('OR'); ?> </h6>
                            <?php do_action( 'wordpress_social_login' ); ?>
                            <div class="moddle-ftlink enhanced-text"><a href="<?php echo freeling_links('login_url'); ?>"><?php get_custom_string('Already have an account?'); ?></a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php get_footer('homepagenew'); ?>