<?php if (!defined('ABSPATH')) { exit; } ?>

<div class="d4p-group d4p-group-information">
    <h3><?php _e("Important", "gd-mail-queue"); ?></h3>
    <div class="d4p-group-inner">
        <?php _e("This tool adds test email directly to the queue.", "gd-mail-queue"); ?>
    </div>
</div>

<div class="d4p-group d4p-group-tools">
    <h3><?php _e("Configure test", "gd-mail-queue"); ?></h3>
    <div class="d4p-group-inner">
        <label><?php _e("Send To", "gd-mail-queue"); ?></label>
        <input type="text" class="widefat" name="gdmaqtools[queuetest][email]" value="<?php echo get_option('admin_email'); ?>" />
        <br/><br/>
        <label><?php _e("Subject", "gd-mail-queue"); ?></label>
        <input type="text" class="widefat" name="gdmaqtools[queuetest][subject]" value="<?php echo __("Test email", "gd-mail-queue").': '.get_option('blogname'); ?>" />
    </div>
</div>

<div class="d4p-group d4p-group-tools" id="d4p-tools-test-results" style="display: none;">
    <h3><?php _e("Test results", "gd-mail-queue"); ?></h3>
    <div class="d4p-group-inner">

    </div>
</div>
