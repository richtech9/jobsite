<?php
/*
* current-php-code 2020-Oct-16
* input-sanitized :
* current-wp-template:  settings for translator
*/
?>
<section class="middle-content setting-content">
	<div class="container" id="container-body">	

		<!-- START: >Email preference-->	
		<section class="email_prefrence setting-sec" style="display: none">
			<h4><?php get_custom_string('Email Preference',current_language()); ?></h4>
			<?php $email_preference = get_user_meta( get_current_user_id(), 'new_jobs_notifications', true ); ?>
			<div class="contnet-box">
				<h5><?php get_custom_string('How Often Would You Like To Receive Job Notifications?',current_language()); ?></h5>
				<div id="form_success_message_user_email_pref"></div>
				<form class="setting-form" method="post" name="email_preference_form" id="email_preference_form" action="<?php echo freeling_links('setting_page_url'); ?>" >
					<div class="box-inner">
						<label class="large-text"><input <?php echo ($email_preference == 'hourly_notify') ? 'checked' : ''; ?> type="radio"  name="email_notify" value="hourly_notify">
						<?php get_custom_string('Hourly emails about new jobs',current_language()); ?></label>
						<p><?php get_custom_string('This email shows you currently available jobs once every several hours',current_language()); ?></p>
					</div>
					<div class="box-inner">
						<label class="large-text"><input <?php echo ($email_preference == 'daily_notify') ? 'checked' : ''; ?> type="radio"  name="email_notify" value="daily_notify">
						<?php get_custom_string('Daily emails about new jobs',current_language()); ?></label>
						<p><?php get_custom_string('This email shows you currently available jobs once every several days',current_language()); ?></p>
					</div>
					<div class="box-inner">
						<label class="large-text"><input <?php echo ($email_preference == 'never_notify') ? 'checked' : ''; ?> type="radio"  name="email_notify" value="never_notify">
						<?php get_custom_string('Never',current_language()); ?></label>							
					</div>
					<input type="submit" value="<?php get_custom_string('Update'); ?>" class="btn-update large-text">
				</form>
			</div>
		</section>
		<!-- END: >Email preference-->	
		
		<!-- START: >Payment Prefrences-->
        <?php get_template_part('includes/user/setting/setting', 'gateway-payments'); ?>
		<!-- END: >Payment Prefrences-->	
		
		<!-- START: Withdrawal Prefrences-->
        <?php get_template_part('includes/user/setting/setting', 'gateway-withdraws'); ?>
		<!-- END: Withdrawal Prefrences-->	
		
		<!-- START: Address Details-->	
		<section class="address_prefrence setting-sec">
			<h4><?php get_custom_string('Address Details',current_language()); ?></h4>				
			<div class="contnet-box">
				<div id="form_success_message_user_update_address"></div>					
				<form class="setting-form" method="post" name="address_details" id="address_details" action="<?php echo freeling_links('setting_page_url'); ?>" >
					<div class="form-group">
						<label><?php get_custom_string('Full name',current_language()); ?></label>
						<?php $user_dedail = get_userdata(get_current_user_id()); ?>
						<input  title="Full Name"  type="text" value="<?php echo $user_dedail->display_name; ?>" name="full_name" id="full_name" class="form-control">
					</div>
					<div class="form-group">
						<label><?php get_custom_string('Address line 1',current_language()); ?></label>
						<input  title="Address line 1" value="<?php echo get_user_meta(get_current_user_id(),'user_address_line_1',true); ?>"  name="address_line_1" id="address_line_1"  type="text" class="form-control">
					</div>
					<div class="form-group">
						<label><?php get_custom_string('Address line 2',current_language()); ?></label>
						<input  title="Address Line 2" value="<?php echo get_user_meta(get_current_user_id(),'user_address_line_2',true); ?>"  name="address_line_2" id="address_line_2"  type="text" class="form-control">
					</div>
					<div class="form-group">
						<label><?php get_custom_string('Town/city',current_language()); ?></label>
						<input  title="City" value="<?php echo get_user_meta(get_current_user_id(),'user_town_city',true); ?>"  name="town_city" id="town_city"  type="text" class="form-control">
					</div>
					<div class="form-group">
						<label><?php get_custom_string('State/Prefecture',current_language()); ?></label>
						<input  title="State" value="<?php echo get_user_meta(get_current_user_id(),'user_state',true); ?>"  name="state" id="state"  type="text" class="form-control">
					</div>
					<div class="form-group">
						<label><?php get_custom_string('Zip/postal code',current_language()); ?></label>
						<input  title="Zip" value="<?php echo get_user_meta(get_current_user_id(),'user_zip_postal_code',true); ?>" name="zip_postal_code" id="zip_postal_code"  type="text" class="form-control">
					</div>
					<div class="form-group">
						<label><?php get_custom_string('Country',current_language()); ?></label>
						<select  title="Country" name="country" id="country"  class="selectpicker">
						  <option value="">-- Select Country</option>
                            <?php 
                                    $countries = get_countries(); 
                                    $i = 0;
                                    foreach ($countries as $key) { 
                                    	$user_country = get_user_meta(get_current_user_id(),'user_residence_country',true);
                                        ?>
                                        <?php if($user_country== $i){ ?>
                                        	<option selected="selected" value="<?php echo $i; ?>"><?php echo $key; ?></option>
                                        <?php }else{ ?>
                                        	<option value="<?php echo $i; ?>"><?php echo $key; ?></option>
                                        <?php } ?>

                                        <?php
                                        $i++;
                                    }
                                    ?>  
						</select>
					</div>
					<div class="form-group">
						<label><?php get_custom_string('Telephone number(optional)',current_language()); ?></label>
						<input  title="Phone" value="<?php echo get_user_meta(get_current_user_id(),'user_phone',true); ?>"  name="telephone_number" id="telephone_number"  type="tel" class="form-control">
					</div>						
					<input name="submit_address" id="submit_address"  type="submit" value="<?php get_custom_string('Update'); ?>" class="btn-update large-text">
				</form>					
			</div>
		</section>			
		<!-- END: Address Details-->

		<!-- START: Display name-->	
		<section class="displayname_prefrence setting-sec">
			<h4><?php get_custom_string('Display name',current_language()); ?></h4>
			<label class="disply-name enhanced-text"><?php echo get_da_name(get_current_user_id()); ?></label>
			<div class="contnet-box">
				<div id="form_success_message_user_display_form"></div>					
				<form class="setting-form" method="post" name="display_name_form" id="display_name_form" action="<?php echo freeling_links('setting_page_url'); ?>" >
					<h5><?php get_custom_string('Change display name',current_language()); ?></h5>						
					<div class="form-group">
						<label><?php get_custom_string('New display name',current_language()); ?></label>
						<input type="text" name="display_name" id="display_name" class="form-control" value="<?php echo get_da_name(get_current_user_id()); ?>" placeholder="Michael">
					</div>							
					<input type="submit" name="submit_display_name" value="<?php get_custom_string('Update'); ?>" class="btn-update large-text">
				</form>
			</div>
		</section>
		<!-- END: Display name-->

		<!-- START: Change email-->	
		<section class="email_prefrence setting-sec">
			<h4><?php get_custom_string('Email',current_language()); ?></h4>
			<?php $current_user = wp_get_current_user(); ?>
			<label class="disply-email"><?php echo $current_user->user_email; ?></label>				
			<div class="contnet-box">	
				<div id="form_success_message_user_email"></div>				
				<form class="setting-form" method="post" name="email_change_form" id="email_change_form" action="<?php echo freeling_links('setting_page_url'); ?>" >
					<h5><?php get_custom_string('Change email',current_language()); ?></h5>
					<strong class="note enhanced-text"><?php get_custom_string('You will receive a confirmation email',current_language()); ?>.<br>
					<?php get_custom_string('Please click the link inside to confirm you have received the email successfully',current_language()); ?>.
					</strong>
					<div class="form-group">
						<label><?php get_custom_string('Email',current_language()); ?></label>
						<input  title="New Email" type="text" id="new_email" name="new_email" class="form-control">
					</div>	
					<div class="form-group">
						<label><?php get_custom_string('Confirm email',current_language()); ?></label>
						<input  title="Conffirm New Email" type="text" id="confirm_new_email" name="confirm_new_email" class="form-control">
					</div>						
					<input name="submit_change_email_form" type="submit" value="<?php get_custom_string('Update'); ?>" class="btn-update large-text">
				</form>
			</div>
		</section>
		<!-- END: Change email-->


        <!-- START: Change email notification preferences -->
        <section class="email_prefrence setting-sec">
            <a name="notifications">
                <h4><?php get_custom_string('Email Notifications',current_language()); ?></h4>
            </a>
            <?php $email_send_all_notifications = (int)get_user_meta( get_current_user_id(), 'email_send_all_notifications', true ); ?>
            <div class="contnet-box">

                <form class="setting-form" method="post" name="email_notification_settings_form" id="email_notification_settings_form" action="<?php echo freeling_links('setting_page_url'); ?>" >
                    <div class="box-inner">
                        <label class="large-text">
                            <input <?php echo ($email_send_all_notifications ) ? 'checked' : ''; ?>
                                    type="radio"  name="email_send_all_notifications" value="1"
                                    autocomplete="off"
                            >
                            <?php get_custom_string('Send All Notifications',current_language()); ?>
                        </label>
                        <p>
                            <?php get_custom_string('You will recieve email notices for status changes',current_language()); ?>
                        </p>
                    </div>

                    <div class="box-inner">
                        <label class="large-text">
                            <input <?php echo (!$email_send_all_notifications ) ? 'checked' : ''; ?>
                                    type="radio"
                                    name="email_send_all_notifications" value="0"
                                    autocomplete="off"
                            >
                            <?php get_custom_string('Do Not Send Any Notifications',current_language()); ?>
                        </label>
                        <p>
                            <?php get_custom_string('You will NOT BE EMAILED about any new status changes',current_language()); ?>
                        </p>
                    </div>

                    <input type="submit" value="<?php get_custom_string('Update'); ?>" class="btn-update large-text">
                    <br>
                    <span style="display: block" class="email_send_all_notifications_status"></span>
                </form>
            </div>
        </section>
        <!-- END: Change email notification preferences -->
		
		<!-- START: Change password-->	
		<section class="password_prefrence setting-sec">
			<h4><?php get_custom_string('Password',current_language()); ?></h4>
			<div class="contnet-box">	
				<div id="form_success_message_user_password_form"></div>				
				<form class="setting-form" method="post" name="password_change_form" id="password_change_form" action="<?php echo freeling_links('setting_page_url'); ?>" >
					<h5><?php get_custom_string('Change password',current_language()); ?></h5>							
						<div class="form-group">
							<label><?php get_custom_string('Current password',current_language()); ?></label>
							<input  title="Old Password" name="old_password" id="old_password" type="password" class="form-control">
						</div>	
						<div class="form-group">
							<label><?php get_custom_string('New password',current_language()); ?></label>
							<input  title="New Password" name="password" id="password"  type="password" class="form-control">
						</div>
						<div class="form-group">
							<label><?php get_custom_string('New password(again)',current_language()); ?></label>
							<input  title="Confirm New Password" name="confirm_password" id="confirm_password"  type="password" class="form-control">
						</div>						
					<input name="submit_password_form" id="submit_password_form" type="submit" value="<?php get_custom_string('Update'); ?>" class="btn-update large-text">
				</form>
			</div>
		</section>
		<!-- END: Change password-->	
		
		<!-- START: Close your account-->	
		<section class="close-account">
			<label class="large-text"><?php get_custom_string('Close your account permanently',current_language()); ?></label>
			<p><?php get_custom_string('Before closing your account, you may contact us to solve potential issues',current_language()); ?>.</p>
			<?php $conf_message = '"'.get_custom_string_return("Do you really want to delete your account?").'"'; ?>									
			<?php $yes = '"'.get_custom_string_return("Yes").'"'; ?>									
			<?php $no = '"'.get_custom_string_return("No").'"'; ?>	
			<a href="javascript:void();" onclick='return delete_my_account(<?php echo $conf_message.",".$yes.",".$no; ?>)' class="link large-text"><?php get_custom_string('Close account',current_language()); ?></a>
            <div style="width: 100% ;height: 10em"></div>
        </section>
		<!-- END: Close your account-->	
	</div>
</section>