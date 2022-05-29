<?php

/*
 * current-php-code 2021-Jan-10
 * input-sanitized :refill_amount
 * current-wp-template:  admin-screen  for checking wallet
 */
function check_wallet()
{
    ?>
    <div class="freelng-set-panle">
        <div class="wrap">
            <h3>Check wallet</h3>
            <?php
            if (isset($_POST['check_wallet'])) {
                $user_email = trim($_REQUEST['w_user_email']);
                $user = get_user_by('email', $user_email);
                if (!empty($user)) {
                    $selected_user = $user->ID;
                    $current_user = wp_get_current_user();
                    if ( in_array('administrator',$current_user->roles) || in_array('administrator_for_client',$current_user->roles)) {
                        $url = admin_url('admin.php?page=freelinguist-admin-user-wallet&lang=en&author=' . $selected_user);
                        wp_redirect($url);
                        exit;
                    }
                    $author__in = getReportedUserByUserId();

                    if (in_array($selected_user, $author__in)) {
                        $url = admin_url('admin.php?page=freelinguist-admin-user-wallet&lang=en&author=' . $selected_user);
                        wp_redirect($url);
                        exit;

                    } else {
                        echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated"><p><strong>You are an unauthorized user for this request.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

                    }
                } else {
                    echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated"><p><strong>Email id not exist..</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

                }
            }
            ?>


            <form id="setting_pannel" method="POST"
                  action="<?php echo admin_url('admin.php?page=freelinguist-admin-check-wallet&lang=en'); ?>">
                <table class="form-table">
                    <tbody>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row">Email address</th>
                        <td>

                            <input type="text" name="w_user_email" id="w_user_email" title="Email Address" size="60">

                            <script type="text/javascript">
                                jQuery(function () {
                                    jQuery("#w_user_email").autocomplete({
                                        source: '<?php echo get_site_url();?>/?action=get_user_list_by_autocomplete&lang=en',
                                        minLength: 1
                                    });
                                });
                            </script>

                            <p class="description">
                                Email address.
                            </p>
                        </td>
                        <td>
                        </td>
                    </tr>


                    <tr class="user-rich-editing-wrap">
                        <th scope="row"><input class="button button-primary button-large" type="submit"
                                               name="check_wallet" value="Check wallet"></th>
                        <td>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </form>
        </div>
    </div>
    <?php
}