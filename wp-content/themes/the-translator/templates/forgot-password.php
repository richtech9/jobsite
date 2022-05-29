<?php
/*
Template Name: Forgot Password Template
*/

/*
* current-php-code 2020-Oct-16
* input-sanitized :
* current-wp-template:  Forgot Password
 * current-wp-top-template
*/

$forgot_password = FLInput::get('forgot_password');
$user_login = FLInput::get('user_login','',FLInput::YES_I_WANT_CONVESION,
    FLInput::NO_DB_ESCAPING,FLInput::NO_HTML_ENTITIES);
$lang = FLInput::get('lang','en');
$reset_password_key = FLInput::get('reset_password_key','',FLInput::YES_I_WANT_CONVESION,
    FLInput::NO_DB_ESCAPING,FLInput::NO_HTML_ENTITIES);

$user_registration = (int)FLInput::get('user_registration');

if(is_user_logged_in()){
	wp_redirect(freeling_links('dashboard_url'));
	exit;
}
$msg = "";
function generateRandomString($length = 15) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
 global $wpdb;
    
    $error = '';
    $success = '';
    
    // check if we're in reset form
    if( $forgot_password)
    {
        $email = trim($user_login);
        if( empty( $email ) ) {
            $error = get_custom_string_return('Enter e-mail address');
        } else if( ! is_email( $email )) {
            $error = get_custom_string_return('Invalid e-mail address');
        } else if( ! email_exists($email) ) {
            $error = get_custom_string_return('There is no user registered with that email address');
        } else {
        
            $forget_password_key = generateRandomString(30);
            $user_data = get_user_by( 'email', trim( $email ) );  
            update_user_meta($user_data->ID,'forget_password_key',$forget_password_key);
            $opts       = get_option('freeling_option_pages');
            $forgot_password_url  = (isset($opts['forgot_password_url'])) ? $opts['forgot_password_url'] : "";
            $forget_password_path =add_query_arg(
                    [
                        'reset_password_key'=> $forget_password_key,
                        'user_registration' => $user_data->ID,
                        'lang'=>$lang
                    ], get_permalink($forgot_password_url)
            );


            $forget_password_link = /** @lang text */
                '<a href="'.$forget_password_path.'"> Click Here To Reset Password </a>';

            // if  update user return true then lets send user an email containing the new password
            if( $forget_password_link ) {
                $to = $email;
                
                $variables = array();
                $variables['password'] = $forget_password_link;

                //code-notes not queuing the forget password email, by not adding a dummy bcc
                emailTemplateForUser($to,FORGOT_PASSWORD_TEMPLATE,$variables,[],false);
                    $success = get_custom_string_return('Check your email inbox, and click the link in the email you received to reset your password');
            } else {
                $error = get_custom_string_return('Oops something went wrong updaing your account');
            }
        }   
    }

get_header('register');
?>

<?php if ($reset_password_key && $user_registration){
    ?>

            <div class="login-page">
            	<div class="form-container">
            		  <div class="vertical-alignment-helper">
            		<div class="access-form login-popup vertical-align-center">
            			<div class="modal-content">
            				<div class="modal-header larger-text">
            					<div class="modal-brandname">
                                    <a href="<?php bloginfo('url'); ?>">
                                        <!--suppress HtmlUnknownTarget -->
                                        <img src="<?php bloginfo('template_url'); ?>/images/logo-1000-by-200.png" width="227px">
                                    </a>
                                </div>
            				</div>
            				<div class="modal-body">
                            <?php if($reset_password_key == get_user_meta($user_registration,'forget_password_key',true)){ ?>
            					
            					   <div id="form_success_message_user_password_form"></div>
                					<form id="freeling_reset_password" name="form" action="#" method="post" class="popup-form login-form">
                						<div class="input-group">
                                            <input type="hidden" class="form-control" value="<?php echo $user_registration; ?>" id="user_registration" name="user_registration">
                                            <input type="hidden" class="form-control" value="<?php echo $reset_password_key; ?>"  name="reset_password_key">
                							<input type="password" class="form-control" placeholder="<?php get_custom_string('Password'); ?>" id="password" name="password">
                						</div>
                                        <div class="input-group">
                                            <input type="password" class="form-control" placeholder="<?php get_custom_string('Confirm Password'); ?>" id="confirm_password" name="confirm_password">
                                        </div>      							
                						<button type="submit" class="btn org-btn login-btn enhanced-text" value="reset_password" name="reset_password"><?php get_custom_string('Reset Password'); ?></button>
                					</form>
                            <?php }else{
                                    echo '<div class="alert alert-warning"> Incorrect link for reset password or link has been expired.</h4></div>';
                            }
                            ?>
            				</div>
            				<div class="modal-footer">
            					<div class="moddle-ftlink enhanced-text">
            						<a href="<?php echo freeling_links('forgot_password_url'); ?>" class="forget-pass"> <?php get_custom_string('Forgot Password?'); ?></a>
            					</div>
            				</div>
            			</div>
            		</div>
            		</div>
            	</div>
            </div>
<?php }else { ?> 
    <div class="login-page">
        <div class="form-container">
              <div class="vertical-alignment-helper">
            <div class="access-form login-popup vertical-align-center">
                <div class="modal-content">
                    <div class="modal-header larger-text">
                        <div class="modal-brandname">
                            <a href="<?php bloginfo('url'); ?>">
                                <!--suppress HtmlUnknownTarget -->
                                <img src="<?php bloginfo('template_url'); ?>/images/logo-1000-by-200.png" width="227px">
                            </a>
                        </div>
                    </div>
                    <div class="modal-body">

                        
                        <?php if($error){ echo '<p style="color:red">'.$error.'</p>'; } ?>
                        <?php if( ! empty( $success ) ) { echo '<div class="updated alert alert-success"> '. $success .'</div>';} ?>
                        <form id="freeling-forgot-password" name="form" action="<?php echo freeling_links('forgot_password_url'); ?>" method="post" class="popup-form login-form">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="<?php get_custom_string('Email'); ?>" name="user_login">
                            </div>                          
                            <button type="submit" class="btn org-btn login-btn enhanced-text" value="forgot_password" name="forgot_password"><?php get_custom_string('Forgot Password'); ?></button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <h6><?php get_custom_string('Or Login with'); ?></h6>

                        <?php do_action( 'wordpress_social_login' ); ?>
                        <div class="moddle-ftlink enhanced-text">
                            <a href="<?php echo freeling_links('login_url'); ?>"><?php get_custom_string('Already have an account?'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
<?php } ?>
	<!-- login-popup End-->




<?php get_footer(); ?>