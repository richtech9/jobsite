<?php

/*
    * current-php-code 2021-Jan-8
    * input-sanitized :
    * current-wp-template:  admin-screen  settings groups/societies
*/



function fl_admin_society_settings_panel()
{

    ?>
    <style>
        input.fl-admin-small-input {
            width: 2em;
            vertical-align: bottom;
            padding-top: 0;
            padding-bottom: 0;
        }

        input.fl-admin-med-input {
            width: 4em;
            vertical-align: bottom;
            padding-top: 0;
            padding-bottom: 0;
        }

        form pre {
            line-height: 1.5;
        }
    </style>
    <div class="wrap">
        <h3>Society Settings</h3>

       
        <?php if (isset($_POST['submit_settings']))
        {

            $val = (int)$_POST['fl_society_min_size'];
            if ($val >= 0) {update_option('fl_society_min_size', $val);}

            $val = (float)$_POST['fl_society_z_work_bonus_percent'];
            if ($val > 0 && $val <1 ) {update_option('fl_society_z_work_bonus_percent',$val);}

            $val = (int)$_POST['fl_society_z_work_bonus_contents'];
            if ($val >= 0) {update_option('fl_society_z_work_bonus_contents',$val);}

            $val = (int)$_POST['fl_society_z_work_bonus_jobs'];
            if ($val >= 0) {update_option('fl_society_z_work_bonus_jobs',$val);}

            $val = (float)$_POST['fl_society_z_hire_bonus_percent'];
            if ($val > 0 && $val <1 ) {update_option('fl_society_z_hire_bonus_percent',$val);}

            $val = (int)$_POST['fl_society_z_hire_bonus_newbies'];
            if ($val >= 0) {update_option('fl_society_z_hire_bonus_newbies',$val);}

            $val = (int)$_POST['fl_society_z_hire_bonus_jobs'];
            if ($val >= 0) {update_option('fl_society_z_hire_bonus_jobs',$val);}

            $val = (float)$_POST['fl_society_z_team_bonus_percent'];
            if ($val > 0 && $val <1 ) {update_option('fl_society_z_team_bonus_percent',$val);}

            $val =(int)$_POST['fl_society_z_team_bonus_newbies'] ;
            if ($val >= 0) {update_option('fl_society_z_team_bonus_newbies',$val);}

            $val = (int)$_POST['fl_society_z_team_bonus_jobs'];
            if ($val >= 0) {update_option('fl_society_z_team_bonus_jobs',$val);}

            $val = (float)$_POST['fl_society_y_work_bonus_percent'];
            if ($val > 0 && $val <1 ) {update_option('fl_society_y_work_bonus_percent',$val);}

            $val = (int)$_POST['fl_society_y_work_bonus_contents'];
            if ($val >= 0) {update_option('fl_society_y_work_bonus_contents',$val);}

            $val = (int)$_POST['fl_society_y_work_bonus_jobs'];
            if ($val >= 0) {update_option('fl_society_y_work_bonus_jobs',$val);}

            $val = (float)$_POST['fl_society_y_hire_bonus_percent'];
            if ($val > 0 && $val <1 ) { update_option('fl_society_y_hire_bonus_percent',$val);}

            $val = (int)$_POST['fl_society_y_hire_bonus_newbies'] ;
            if ($val >= 0) {update_option('fl_society_y_hire_bonus_newbies',$val);}

            $val = (int)$_POST['fl_society_y_team_bonus_jobs'];
            if ($val >= 0) {update_option('fl_society_y_team_bonus_jobs',$val);}

            ?>
            <div class="updated notice is-dismissible" >
                <p>
                    <strong>Settings Updated.</strong>
                </p>
                <button class="notice-dismiss" type="button">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>

        <?php } ?>

        <?php
        /*
        get_option('fl_society_min_size',100)
        get_option('fl_society_z_work_bonus_percent',0.333)
        get_option('fl_society_z_work_bonus_contents',4)
        get_option('fl_society_z_work_bonus_jobs',1)
        get_option('fl_society_z_hire_bonus_percent',0.333)
        get_option('fl_society_z_hire_bonus_newbies',1)
        get_option('fl_society_z_hire_bonus_jobs',1)
        get_option('fl_society_z_team_bonus_percent',0.333)
        get_option('fl_society_z_team_bonus_newbies',2)
        get_option('fl_society_z_team_bonus_jobs',1)

        y options
        get_option('fl_society_y_work_bonus_percent',0.5)
        get_option('fl_society_y_work_bonus_contents',4)
         get_option('fl_society_y_work_bonus_jobs',1)
        get_option('fl_society_y_hire_bonus_percent',0.5)
        get_option('fl_society_y_hire_bonus_newbies',1)
        get_option('fl_society_y_team_bonus_jobs',1)
         */
        ?>



        <form id="society-settings" method="POST" enctype="multipart/form-data" action="">

            <pre>

        <input name="fl_society_min_size" type="text" title="" class="fl-admin-med-input" value="<?= get_option('fl_society_min_size',100) ?>" autocomplete="off" > members needed for a group to become a society

        -------------------------------------------------------------------------------

        %z options

        percent <input name="fl_society_z_work_bonus_percent" type="text"  title="" class="fl-admin-med-input"  value="<?= get_option('fl_society_z_work_bonus_percent',0.333) ?>" autocomplete="off" > % paid
        if <input name="fl_society_z_work_bonus_contents" type="text" title=""  class="fl-admin-small-input"   value="<?= get_option('fl_society_z_work_bonus_contents',4) ?>"  autocomplete="off"> new content for sale in this period
        AND
        complete at least <input name="fl_society_z_work_bonus_jobs" type="text" title=""  class="fl-admin-small-input"   value="<?= get_option('fl_society_z_work_bonus_jobs',1) ?>"  autocomplete="off"> new job as freelancer or client

        percent <input name="fl_society_z_hire_bonus_percent" type="text" title=""  class="fl-admin-med-input" value="<?= get_option('fl_society_z_hire_bonus_percent',0.333) ?>"  autocomplete="off"> % paid  if hired at least  <input name="fl_society_z_hire_bonus_newbies" type="text" title=""  class="fl-admin-small-input" value="<?= get_option('fl_society_z_hire_bonus_newbies',1) ?>"  autocomplete="off"> new members who have completed at least <input name="fl_society_z_hire_bonus_jobs" type="text" title=""  class="fl-admin-small-input" value="<?= get_option('fl_society_z_hire_bonus_jobs',1) ?>" autocomplete="off" > job this period

        percent <input name="fl_society_z_team_bonus_percent" type="text" title=""  class="fl-admin-med-input"  value="<?= get_option('fl_society_z_team_bonus_percent',0.333) ?>"  autocomplete="off" > % paid if group belongs in has hired at least <input name="fl_society_z_team_bonus_newbies" type="text" title=""  class="fl-admin-small-input"  value="<?= get_option('fl_society_z_team_bonus_newbies',2) ?>"  autocomplete="off"> new members who have completed at least <input name="fl_society_z_team_bonus_jobs" type="text" title=""  class="fl-admin-small-input"  value="<?= get_option('fl_society_z_team_bonus_jobs',1) ?>" autocomplete="off"> job this period


        -------------------------------------------------------------------------------
        %y and %x options

        percent <input name="fl_society_y_work_bonus_percent" type="text" title=""  class="fl-admin-med-input"  value="<?= get_option('fl_society_y_work_bonus_percent',0.5) ?>" autocomplete="off" > % paid
        if <input name="fl_society_y_work_bonus_contents" type="text" title=""  class="fl-admin-small-input"   value="<?= get_option('fl_society_y_work_bonus_contents',4) ?>"  autocomplete="off"> new content for sale in this period
        AND
        complete at least <input name="fl_society_y_work_bonus_jobs" type="text" title=""  class="fl-admin-small-input"    value="<?= get_option('fl_society_y_work_bonus_jobs',1) ?>"  autocomplete="off"> new job as freelancer or client

        percent <input name="fl_society_y_hire_bonus_percent" type="text" title=""  class="fl-admin-med-input" value="<?= get_option('fl_society_y_hire_bonus_percent',0.5) ?>"  autocomplete="off"> % paid  if hired at least  <input name="fl_society_y_hire_bonus_newbies" type="text" title=""  class="fl-admin-small-input"  value="<?= get_option('fl_society_y_hire_bonus_newbies',1) ?>"  autocomplete="off"> new members who have completed at least <input name="fl_society_y_team_bonus_jobs" type="text" title=""  class="fl-admin-small-input"  value="<?= get_option('fl_society_y_team_bonus_jobs',1) ?>" autocomplete="off"> job this period


       </pre>

            <table class="form-table">
                <tbody>
                <tr class="">
                    <th scope="row"></th>
                    <td scope="row">
                        <input type="submit" name="submit_settings" value="Submit">
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>

    <script>
        jQuery(function($) {

        });
    </script>

    <?php

}