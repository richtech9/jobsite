<?php

if (!defined('ABSPATH')) { exit; }

$status_labels = array(
    'pause' => __("Stopped", "gd-mail-queue"),
    'working' => __("Logging", "gd-mail-queue")
);

$status = gdmaq_logger()->active ? 'working' : 'pause';

?>
<div class="d4p-group d4p-group-dashboard-card d4p-group-dashboard-queue">
    <h3><?php _e("Mail Log Core", "gd-mail-queue"); ?></h3>
    <div class="d4p-group-stats">
        <ul class="d4p-items-list">
            <li>
                <strong><i class="fa fa-flag fa-fw"></i> <?php _e("Log Status", "gd-mail-queue"); ?></strong>
                <span class="gdmaq-label gdmaq-<?php echo $status; ?>"><?php echo $status_labels[$status]; ?></span>
            </li>
        </ul><div class="d4p-clearfix"></div>
    </div>
    <div class="d4p-group-inner">
        <h4><?php _e("Overall Log Statistics", "gd-mail-queue"); ?></h4>
        <ul class="d4p-items-list">
            <li>
                <strong><i class="fa fa-envelope fa-fw"></i> <?php _e("Total Log Entries", "gd-mail-queue"); ?></strong>
                <span><?php echo gdmaq_db()->email_log_count(); ?></span>
            </li>
            <li>
                <strong><i class="fa fa-envelope-o fa-fw"></i> <?php _e("Unique Email Addresses", "gd-mail-queue"); ?></strong>
                <span><?php echo gdmaq_db()->email_emails_count(); ?></span>
            </li>
        </ul><div class="d4p-clearfix"></div>
    </div>
    <div class="d4p-group-footer">
        <a href="admin.php?page=gd-mail-queue-log" class="button-primary"><?php _e("Log", "gd-mail-queue"); ?></a>
        <a href="admin.php?page=gd-mail-queue-settings&panel=log" class="button-primary"><?php _e("Log Settings", "gd-mail-queue"); ?></a>
    </div>
</div>
