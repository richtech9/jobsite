<?php

add_action('admin_head', 'disable_multiple_role_selection');

function disable_multiple_role_selection()
{
    /*
    * current-php-code 2020-Jan-15
    * current-hook
    * input-sanitized :
    */
    ?>
    <script type="text/javascript">
        jQuery(function () {
            jQuery("input[name='members_user_roles[]']").attr("type", 'radio');
            jQuery("#ure_select_other_roles").parentsUntil(".form-table").css({"display": "none"});
        });
    </script>
    <?php
}
