<?php if (!defined('ABSPATH')) { exit; } ?>

<div style="display: none">
    <div title="<?php _e("Are you sure?", "gd-mail-queue"); ?>" id="gdmaq-dialog-log-delete-single">
        <div class="gdmaq-inner-content">
            <p><?php _e("Selected log entry will be removed from the database.", "gd-mail-queue"); ?></p>
            <p><?php _e("Are you sure you want to proceed? This operation is not reversable!", "gd-mail-queue"); ?></p>
        </div>
    </div>

    <div id="gdmaq-dialog-log-view-entry" title="<?php _e("Email Log Entry", "gd-mail-queue"); ?>">
        <div id="gdmaq-view-entry-inner"></div>
    </div>
</div>