<?php
/*
Template Name: Login Template
*/

    /*
    * current-php-code 2020-Oct-10
    * input-sanitized : id,content_id,c_type,lang,redirect_to,remember,user_login ,user_password,
    *   checks existence redirect_to_order_process,sign-in
    * current-wp-template:  logging in
    * current-wp-top-template
    */
FLInput::onlyPost();
$lang = FLInput::get('lang', 'en');
$username = FLInput::get('user_login','',FLInput::YES_I_WANT_CONVESION,
    FLInput::NO_DB_ESCAPING,FLInput::NO_HTML_ENTITIES);

$password = FLInput::get('user_password','',FLInput::YES_I_WANT_CONVESION,
    FLInput::NO_DB_ESCAPING,FLInput::NO_HTML_ENTITIES);

$remember = FLInput::get('remember');

FLInput::onlyPost(false);
$redirect_to = FLInput::get('redirect_to','',FLInput::YES_I_WANT_CONVESION,
    FLInput::NO_DB_ESCAPING,FLInput::NO_HTML_ENTITIES);;


//we need to override the normal redirect to sometimes, but find out now as session is reset during login
if (isset($_SESSION['redirect_after_login'])) {
    $redirect_to = $_SESSION['redirect_after_login'];
    unset($_SESSION['redirect_after_login']);
}

$id = FLInput::get('id');
$content_id = FLInput::get('content_id');
$mode = FLInput::get('mode');
$c_type = FLInput::get('c_type');

$redirectTodashboard = home_url( '/dashboard/' );
if(is_user_logged_in()){

    wp_redirect($redirectTodashboard);
    exit;
}

$msg 			= "";
$resend_mail 	= "";
$redirect_to_order_process = FLInput::exists('redirect_to_order_process') ? 'true' : 'false';
$conf_message 	= '"'.get_custom_string_return("Do you really want to re-activate your account?").'"';


$yes 			= '"'.get_custom_string_return("Yes").'"';
$no 			= '"'.get_custom_string_return("No").'"';

if(FLInput::exists('sign-in')){
    global $wpdb;

    if($remember){$remember = true;}else{$remember = false;}
    $login_data = array();
    $login_data['user_login'] = $username;
    $login_data['user_password'] = $password;
    $login_data['remember'] = $remember;
    $user_verify = wp_signon( $login_data, false );
    if($remember == true){

        setcookie( "user_login",$username,time()+ (10 * 365 * 24 * 60 * 60) );
        setcookie( "user_password",$password,time()+ (10 * 365 * 24 * 60 * 60) );
    }

    if( is_wp_error($user_verify) ){
        if(isset($user_verify->errors['Account Not Active...']) && count($user_verify->errors['Account Not Active...']) == 1){
            $username = '"'.$username.'"';
            $msg = get_custom_string_return("Your account has been closed.") ." ". get_custom_string_return("Would you like to")." <strong><a href='#' onclick='return re_active_my_account(".$username.",".$conf_message.",".$yes.",".$no.")'> ".get_custom_string_return("reactivate")." </a></strong>". get_custom_string_return("your account and log in?");
        }else{
            $msg = get_custom_string_return("Invalid login credentials.");
        }
    }else{
        $key = get_user_meta($user_verify->ID,"has_to_be_activated", true);
        if($key){
            $msg = get_custom_string_return("Your account is not activated. Please check your email and activate your account.");
            $resend_mail = 'true';
            wp_logout();
        }else{
            $user_data = get_userdata($user_verify->ID);
            $user_residence_country = get_user_meta( $user_verify->ID, 'user_residence_country', true);
            if(empty($user_residence_country)){
                update_user_meta( $user_verify->ID, 'user_residence_country', '224');
            }


            $user_processing_id = get_user_meta( $user_verify->ID, 'user_processing_id', true);
            if(empty($user_processing_id)){
                update_user_meta( $user_verify->ID, 'user_processing_id', '0');
            }
            if($user_data->data->user_status == 0){

                if($redirect_to_order_process == 'true' && $user_data->roles[0] == "customer"){
                    if(isset($lang)){
                        $redirectTo = home_url('/order-process/');

                    }else{
                        $redirectTo = home_url('/order-process/');

                    }
                }else{
                    if($user_data->roles[0] == "customer"){
                        $redirectTo = home_url('/dashboar/');
                    }else{
                        $redirectTo = home_url('/dashboard/');
                    }
                }

                if(!empty($redirect_to)){
                    if($user_data->roles[0] == "customer"){
                        $redirectTo = $redirect_to;
                    }
                    else{


                        if(sizeof(explode('content',$redirect_to))==1){
                            $redirectTo = $redirect_to;
                        }else{
                            $redirectTo = $redirect_to;
                        }
                    }
                }else{
                    $redirectTo = home_url('/dashboard/');
                }
                addToFav($user_verify->ID);


                if($redirectTo=='&'){
                    $redirectTo = home_url('/dashboard/');
                }

                wp_redirect($redirectTo);
                exit;
            }else{
                wp_logout();
                $msg = get_custom_string_return("Your account has been deleted.");
            }
        }
    }
}
get_header('register');
?>
<?php //add_action( 'wp_head', 'add_meta_tags' , 2 ); ?>
    <style>
        .row.language_drop{ margin-left: 20%; margin-top: 21px; margin-right:0px !important;
        }
    </style>
    <div class="login-page">
        <div class="form-container">
            <div class="row language_drop">
                <?php
                if( $_SERVER['SERVER_NAME'] != 'www.wenren8.com' ){
                    dynamic_sidebar('language_st');
                }
                ?><br>
            </div>
            <div class="vertical-alignment-helper">
                <div class="access-form login-popup vertical-align-center">
                    <div class="modal-content">
                        <div class="modal-header larger-text">
                            <div class="modal-brandname"><a href="<?php bloginfo('url'); ?>">
                                    <img  src="<?php echo get_logo_by_current_language(); ?>" width="227px" height="" ></a>
                            </div>
                        </div>
                        <div class="modal-body">
                            <!-- <form class="popup-form login-form"> -->
                            <?php if(FLInput::exists('reg')){ ?>
                                <label id="success_message" style="">
							<span class="alert alert-success" style="display: block">
                                <?php echo get_custom_string_return(
                                    'You are successfully registered. Please check your email to verify the account.');
                                ?>
                            </span>
                                </label>
                            <?php } ?>
                            <?php
                            if(empty($password)){
                                $user_password = isset($_COOKIE["user_password"]) ? $_COOKIE["user_password"] : '';
                            }

                            if(empty($username)){
                                $username = isset($_COOKIE["user_login"]) ? $_COOKIE["user_login"] : '';
                            }

                            if($resend_mail == ""){
                                $resend_msg = '';
                            }else{
                                $resend_msg = '<a   href="javascript:void()" 
							                        onclick="return resendConfirmationEmail('."'".$username."'".')"
                                                >'.
                                    get_custom_string_return("Click here to resend email").
                                    '</a>
                                                <span class="fl-resend-email-block" >
                                                <span class="fl-resend-email-doing-work">
                                                    <span>Sending Email Now</span>
                                                    <i class="fa fa-spin fa-spinner"></i>
                                                </span>
                                               
                                                <span class="fl-resend-email-success-action">
                                                    <span>Email Sent Again!</span> 
                                               </span>
                                                <span class="fl-resend-email-error-action">
                                                    <span>There was an error sending you the email</span>
                                                </span>
                                            </span><!-- /.fl-resend-email-block--> ';
                            }
                            ?>
                            <?php if($msg){ echo '<p class="alert alert-info fl-no-bottom-padding">'.$msg.' '.$resend_msg.' </p>'; } ?>
                            <?php
                            if($lang){
                                $form_url = FLInput::exists('redirect_to_order_process') ? '&redirect_to_order_process=true' : '';
                            }else{
                                $form_url = FLInput::exists('redirect_to_order_process') ? '?redirect_to_order_process=true' : '';
                            }
                            ?>
                            <form id="freeling-login" name="form"
                                  action="<?php echo freeling_links('login_url').$form_url; ?>"
                                  method="post"
                                  class="popup-form login-form"
                            >
                                <input type="hidden" value='<?= $id ?>' name="id">
                                <input type="hidden" value='<?= $c_type ?>' name="c_type">
                                <?php
                                if(isset($redirect_to)){
                                    if($content_id){
                                        $redirect="content_id=".$content_id;
                                        if($mode){
                                            $redirect .="&mode=".$mode;
                                        }
                                    }
                                    if (isset($redirect) && $redirect ) {
                                        $redirect_to = $redirect_to.'&'.$redirect;
                                    }

                                }
                                ?>
                                <input type="hidden" value='<?php echo (!empty($redirect_to) ? $redirect_to : ''); ?>' name="redirect_to">
                                <div class="input-group">
                                    <input type="text" class="form-control"
                                           placeholder="<?php get_custom_string('Email'); ?>"
                                           value='<?php echo $username; ?>' name="user_login">
                                </div>

                                <div class="input-group">
                                    <input type="password" class="form-control"
                                           value="<?php echo $password; ?>"
                                           placeholder="<?php get_custom_string('Password'); ?>" name="user_password">
                                </div>
                                <button type="submit" class="btn org-btn login-btn enhanced-text" name="sign-in">
                                    <?php get_custom_string('Log in'); ?>
                                </button>

                                <div class="form-group forget-group">
                                    <label>
                                        <input type="checkbox" value="rememberme" name="remember">
                                        <?php get_custom_string('Remember Me'); ?>
                                    </label>
                                    <a href="<?php echo freeling_links('forgot_password_url'); ?>"
                                       class="forget-pass"
                                    >
                                        <?php get_custom_string('Forgot Password?'); ?>
                                    </a>
                                </div>
                            </form>

                        </div>
                        <div class="modal-footer">
                            <?php do_action( 'wordpress_social_login' ); ?>
                            <div class="moddle-ftlink enhanced-text">
                                <a class="go_signup" href="<?php echo freeling_links('registration_url'); ?>">
                                    <?php get_custom_string("Do not have an account yet?"); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- login-popup End-->

<?php get_footer(); ?>