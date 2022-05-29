<?php

/**
 * shows the referral code or a button to make the referral code
 *  * @usage
 *      get_template_part('includes/user/society/society', 'style'); //above somewhere
 *      get_template_part('includes/user/society/society', 'referral-code');
 *  setting is optional to show referral code of a user_id, will default to the current user
 *  set_query_var( 'referral_code_of_user', <user_id> );
 *
 */

if (isset($referral_code_of_user)) {
    $referral_code_user_id = (int)$referral_code_of_user;
} else {
    $referral_code_user_id = get_current_user_id();
}

if (empty($referral_code_user_id)) {
    return;
}

if ($referral_code_user_id === get_current_user_id()) {

    $b_is_self = true;
} else {

    $b_is_self = false;
}

$referral_code = FreelinguistUserLookupDataHelpers::get_user_referral_code($referral_code_user_id);

$extra_ref_class = '';
if (empty($referral_code) ) {
    $extra_ref_class = 'fl-society-referral-no-code-shown';
    if ($b_is_self ) {
        $title = __("You have not yet created a PeerOK Society Referral Code");
    } else {
        $da_name = get_da_name($referral_code_user_id);
        $title = $da_name  . ' '. __("Has not created a PeerOK Society Referral Code");
    }
} else {
    if ($b_is_self) {
        $title = __("Referral Code:");
    } else {
        $da_name = get_da_name($referral_code_user_id);
        $title = __("PeerOK Society Referral Code for") . ' '. $da_name;
    }
}
?>
<div class="fl-society-referral-code-area">

    <span class="fl-society-referral-code-title">
        <?= $title ?>
    </span>

    <span class="fl-society-referral-code-shown <?= $extra_ref_class ?> large-text">
        <?= $referral_code ?>
    </span>

    <?php if ($b_is_self && empty($referral_code)) { ?>
        <button type="button" class="btn freelinguist-red-button fl-society-generate-user-referral-code">
            <?= __('Generate Referral Code') ?>
        </button>
    <?php } ?>
</div>
