<?php


/*
    * current-php-code 2021-Jan-15
    * input-sanitized :
    * current-wp-template:  admin-screen  settings older
*/

function theme_option_val_render()
{
    if (isset($_POST['save_bid_amount'])) {
        update_option('bid_security_amount', $_POST['bid_security_amount']);
        echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
<p><strong>Settings saved.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
    }


    if (isset($_POST['save_translation'])) {
        for ($i = 1; $i <= 10; $i++) {
            $translation = 'technical_level_T' . $i;
            $translation_tech_level = 'technical_level_TL' . $i;
            update_option($translation, $_POST[$translation]);
            update_option($translation_tech_level, $_POST[$translation_tech_level]);

            $translation_percentage = 'technical_level_percentage_T' . $i;
            $translation_tech_level_percentage = 'technical_level_percentage_TL' . $i;
            update_option($translation_percentage, $_POST[$translation_percentage]);
            update_option($translation_tech_level_percentage, $_POST[$translation_tech_level_percentage]);
        }
        echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
<p><strong>Settings saved.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
    }


    if (isset($_POST['save_editing'])) {
        for ($i = 1; $i <= 10; $i++) {
            $editing = 'technical_level_E' . $i;
            $editing_tech_level = 'technical_level_EL' . $i;
            update_option($editing, $_POST[$editing]);
            update_option($editing_tech_level, $_POST[$editing_tech_level]);

            $editing_percentage = 'technical_level_percentage_E' . $i;
            $editing_tech_level_percentage = 'technical_level_percentage_EL' . $i;
            update_option($editing_percentage, $_POST[$editing_percentage]);
            update_option($editing_tech_level_percentage, $_POST[$editing_tech_level_percentage]);
        }
        echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
<p><strong>Settings saved.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
    }


    if (isset($_POST['save_writing'])) {
        for ($i = 1; $i <= 10; $i++) {
            $writing = 'technical_level_W' . $i;
            $writing_tech_level = 'technical_level_WL' . $i;
            update_option($writing, $_POST[$writing]);
            update_option($writing_tech_level, $_POST[$writing_tech_level]);

            $writing_percentage = 'technical_level_percentage_W' . $i;
            $writing_tech_level_percentage = 'technical_level_percentage_WL' . $i;
            update_option($writing_percentage, $_POST[$writing_percentage]);
            update_option($writing_tech_level_percentage, $_POST[$writing_tech_level_percentage]);
        }
        echo '<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
<p><strong>Settings saved.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
    }
    ?>

    <div class="freelng-set-panle">
    <div class="wrap stuffbox">
        <div class="inside">
            <form id="setting_pannel" method="POST"
                  action="<?php echo admin_url('admin.php?page=theme-option-val&typis=translation'); ?>&lang=en">
                <table class="form-table">
                    <tbody>
                    <tr class="user-rich-editing-wrap ">
                        <th scope="row">Bidding Deposit</th>
                        <td><input type="text" name="bid_security_amount" id="bid_security_amount" title=""
                                   value="<?php echo get_option('bid_security_amount'); ?>"></td>
                    </tr>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row"><input type="submit" value="Submit" class="button button-primary"
                                               name="save_bid_amount"></th>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
    <div class="clear"></div>
    <div class="clear"></div>
    <div class="wrap">
        <?php
        if (isset($_REQUEST['typeis'])) {
            if ($_REQUEST['typeis'] == 'translation') {
                $trans_class = 'nav-tab nav-tab-active';
                $editing_class = 'nav-tab';
                $writing_class = 'nav-tab';
            } elseif ($_REQUEST['typeis'] == 'editing') {
                $trans_class = 'nav-tab';
                $editing_class = 'nav-tab nav-tab-active';
                $writing_class = 'nav-tab';
            } elseif ($_REQUEST['typeis'] == 'writing') {
                $trans_class = 'nav-tab';
                $editing_class = 'nav-tab';
                $writing_class = 'nav-tab nav-tab-active';
            } else {
                $trans_class = 'nav-tab nav-tab-active';
                $editing_class = 'nav-tab';
                $writing_class = 'nav-tab';
            }
        } else {
            $trans_class = 'nav-tab nav-tab-active';
            $editing_class = 'nav-tab';
            $writing_class = 'nav-tab';
        }
        ?>

        <a href="<?php echo admin_url('admin.php?page=theme-option-val&typeis=translation'); ?>" style=""
           class="<?php echo $trans_class; ?>">Translation</a>
        <a href="<?php echo admin_url('admin.php?page=theme-option-val&typeis=editing'); ?>" style=""
           class="<?php echo $editing_class; ?>">Editing</a>
        <a href="<?php echo admin_url('admin.php?page=theme-option-val&typeis=writing'); ?>" style=""
           class="<?php echo $writing_class; ?>">Writing</a>
        <div class="clear"></div>

        <!-- START: Translation technical levels -->
        <?php if ((isset($_REQUEST['typeis']) && $_REQUEST['typeis'] == 'translation') || !isset($_REQUEST['typeis'])) { ?>
            <div class="nav-tab1 nav-tab-active1">
                <form id="setting_pannel" method="POST"
                      action="<?php echo admin_url('admin.php?page=theme-option-val&typeis=translation'); ?>&lang=en">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <td colspan="6"><span
                                        class="bold-and-blocking larger-text">Translation Technical level</span></td>
                        </tr>
                        <?php for ($i = 1; $i <= 10; $i++) { ?>
                            <?php
                            $level = 'technical_level_T' . $i;
                            $tech_level = 'technical_level_TL' . $i;
                            $level_percentage = 'technical_level_percentage_T' . $i;
                            $tech_level_percentage = 'technical_level_percentage_TL' . $i;
                            $Label_level = 'Translation level T' . $i;
                            $Label_tech_level = 'Translation level TL' . $i;
                            ?>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row"><?php echo $Label_level; ?></th>
                                <td>
                                    <input type="number" step="any" name="<?php echo $level; ?>" title=""
                                           id="<?php echo $level; ?>" value="<?php echo get_option($level); ?>">
                                    <p class="description" id="">Base rate.</p>
                                </td>
                                <td>
                                    <input type="number" step="any" name="<?php echo $level_percentage; ?>"
                                           id="<?php echo $level_percentage; ?>" title=""
                                           value="<?php echo get_option($level_percentage); ?>">
                                    <p class="description" id="">Percentage.</p>

                                </td>
                                <th scope="row"><?php echo $Label_tech_level; ?></th>
                                <td>
                                    <input type="number" step="any" name="<?php echo $tech_level; ?>"
                                           id="<?php echo $tech_level; ?>" title=""
                                           value="<?php echo get_option($tech_level); ?>">
                                    <p class="description" id="">Base rate.</p>
                                </td>
                                <td>
                                    <input type="number" step="any" name="<?php echo $tech_level_percentage; ?>"
                                           id="<?php echo $tech_level_percentage; ?>" title=""
                                           value="<?php echo get_option($tech_level_percentage); ?>">
                                    <p class="description" id="">Percentage.</p>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="user-rich-editing-wrap">
                            <th scope="row"><input type="submit" value="Submit" class="button button-primary"
                                                   name="save_translation"></th>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        <?php } ?>
        <!-- END: Translation technical levels -->

        <!-- START: Editing technical levels -->
        <?php if (isset($_REQUEST['typeis']) && $_REQUEST['typeis'] == 'editing') { ?>
            <div class="nav-tab1 nav-tab-active1">
                <form id="setting_pannel" method="POST"
                      action="<?php echo admin_url('admin.php?page=theme-option-val&typeis=editing'); ?>&lang=en">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <td colspan="6"><span class="bold-and-blocking larger-text">Editing Technical level</span>
                            </td>
                        </tr>
                        <?php for ($i = 1; $i <= 10; $i++) { ?>
                            <?php
                            $level = 'technical_level_E' . $i;
                            $tech_level = 'technical_level_EL' . $i;
                            $level_percentage = 'technical_level_percentage_E' . $i;
                            $tech_level_percentage = 'technical_level_percentage_EL' . $i;
                            $Label_level = 'Editing level E' . $i;
                            $Label_tech_level = 'Editing level EL' . $i;
                            ?>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row"><?php echo $Label_level; ?></th>
                                <td>
                                    <input type="number" step="any" name="<?php echo $level; ?>"  title=""
                                           id="<?php echo $level; ?>" value="<?php echo get_option($level); ?>">
                                    <p class="description" id="">Base rate.</p>
                                </td>
                                <td>
                                    <input type="number" step="any" name="<?php echo $level_percentage; ?>"
                                           id="<?php echo $level_percentage; ?>" title=""
                                           value="<?php echo get_option($level_percentage); ?>">
                                    <p class="description" id="">Percentage.</p>

                                </td>
                                <th scope="row"><?php echo $Label_tech_level; ?></th>
                                <td>
                                    <input type="number" step="any" name="<?php echo $tech_level; ?>"
                                           id="<?php echo $tech_level; ?>" title=""
                                           value="<?php echo get_option($tech_level); ?>">
                                    <p class="description" id="">Base rate.</p>
                                </td>
                                <td>
                                    <input type="number" step="any" name="<?php echo $tech_level_percentage; ?>"
                                           id="<?php echo $tech_level_percentage; ?>" title=""
                                           value="<?php echo get_option($tech_level_percentage); ?>">
                                    <p class="description" id="">Percentage.</p>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="user-rich-editing-wrap">
                            <th scope="row"><input type="submit" value="Submit" class="button button-primary"
                                                   name="save_editing"></th>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        <?php } ?>
        <!-- END: Editing technical levels -->


        <!-- START: Writing technical levels -->
        <?php if (isset($_REQUEST['typeis']) && $_REQUEST['typeis'] == 'writing') { ?>
            <div class="nav-tab1 nav-tab-active1">
                <form id="setting_pannel" method="POST"
                      action="<?php echo admin_url('admin.php?page=theme-option-val&typeis=writing'); ?>&lang=en">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <td colspan="6"><span class="bold-and-blocking larger-text">Writing Technical level</span>
                            </td>
                        </tr>
                        <?php for ($i = 1; $i <= 10; $i++) { ?>
                            <?php
                            $level = 'technical_level_W' . $i;
                            $tech_level = 'technical_level_WL' . $i;
                            $level_percentage = 'technical_level_percentage_W' . $i;
                            $tech_level_percentage = 'technical_level_percentage_WL' . $i;
                            $Label_level = 'Writing Level W' . $i;
                            $Label_tech_level = 'Writing level WL' . $i;
                            ?>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row"><?php echo $Label_level; ?></th>
                                <td>
                                    <input type="number" step="any" name="<?php echo $level; ?>" title=""
                                           id="<?php echo $level; ?>" value="<?php echo get_option($level); ?>">
                                    <p class="description" id="">Base rate.</p>
                                </td>
                                <td>
                                    <input type="number" step="any" name="<?php echo $level_percentage; ?>"
                                           id="<?php echo $level_percentage; ?>" title=""
                                           value="<?php echo get_option($level_percentage); ?>">
                                    <p class="description" id="">Percentage.</p>

                                </td>
                                <th scope="row"><?php echo $Label_tech_level; ?></th>
                                <td>
                                    <input type="number" step="any" name="<?php echo $tech_level; ?>"
                                           id="<?php echo $tech_level; ?>" title=""
                                           value="<?php echo get_option($tech_level); ?>">
                                    <p class="description" id="">Base rate.</p>
                                </td>
                                <td>
                                    <input type="number" step="any" name="<?php echo $tech_level_percentage; ?>"
                                           id="<?php echo $tech_level_percentage; ?>" title=""
                                           value="<?php echo get_option($tech_level_percentage); ?>">
                                    <p class="description" id="">Percentage.</p>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="user-rich-editing-wrap">
                            <th scope="row"><input type="submit" value="Submit" class="button button-primary"
                                                   name="save_writing"></th>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        <?php } ?>
        <!-- END: Writing technical levels -->
    </div>
    <?php
}