<?php

if (!defined('ABSPATH')) { exit; }

include(GDMAQ_PATH.'forms/shared/top.php');

?>

<div class="d4p-plugin-dashboard">
    <div class="d4p-content-left">
        <div class="d4p-dashboard-badge" style="background-color: #773355">
            <div aria-hidden="true" class="d4p-plugin-logo"><i class="d4p-icon d4p-plugin-icon-gd-mail-queue"></i></div>
            <h3>GD Mail Queue</h3>

            <h5>
                <?php

                _e("Version", "gd-mail-queue");
                echo': '.gdmaq_settings()->info->version;

                if (gdmaq_settings()->info->status != 'stable') {
                    echo ' - <span class="d4p-plugin-unstable" style="color: #fff; font-weight: 900;">'.strtoupper(gdmaq_settings()->info->status).'</span>';
                }

                ?>

            </h5>
        </div>

        <div class="d4p-buttons-group">
            <a class="button-secondary" href="admin.php?page=gd-mail-queue-settings"><i aria-hidden="true" class="fa fa-cogs fa-fw"></i> <?php _e("Settings", "gd-mail-queue"); ?></a>
            <a class="button-secondary" href="admin.php?page=gd-mail-queue-tools"><i aria-hidden="true" class="fa fa-wrench fa-fw"></i> <?php _e("Tools", "gd-mail-queue"); ?></a>
        </div>

        <div class="d4p-buttons-group">
            <a class="button-secondary" href="admin.php?page=gd-mail-queue-about"><i aria-hidden="true" class="fa fa-info-circle fa-fw"></i> <?php _e("About", "gd-mail-queue"); ?></a>
        </div>
    </div>
    <div class="d4p-content-right">
        <?php

        include(GDMAQ_PATH.'forms/dashboard/errors.php');

        include(GDMAQ_PATH.'forms/dashboard/mailer.php');
        include(GDMAQ_PATH.'forms/dashboard/queue.php');
        include(GDMAQ_PATH.'forms/dashboard/log.php');
        include(GDMAQ_PATH.'forms/dashboard/wpmail.php');
        include(GDMAQ_PATH.'forms/dashboard/last.php');
        include(GDMAQ_PATH.'forms/dashboard/stats.php');

        ?>
    </div>
</div>

<?php

include(GDMAQ_PATH.'forms/shared/bottom.php');
