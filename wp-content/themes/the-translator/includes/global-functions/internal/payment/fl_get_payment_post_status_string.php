<?php

function fl_get_payment_post_status_string($post_id, $b_html = false,$post_status_raw = null,$payment_status = null) {
    $post_id = (int)$post_id;
    if (empty(trim($payment_status))) {$payment_status = '';}


    if ($post_status_raw === null) {
        $post_status_raw = get_post_status($post_id);
    }

    if ($post_status_raw === 'pending_transaction' ) {
        $post_status = 'Pending';
    } else if ($post_status_raw === 'publish' ) {
        $post_status = 'Completed';
    } else if ($post_status_raw === 'failed_transaction') {
        $post_status = 'Failed';
    } else {
        $post_status = ucwords($post_status_raw);
    }

    if ($b_html) {
        return
            '<span class="fl-transaction-post-status-line">'.
            '<span class="fl-transaction-post-status">'.$post_status.'</span> '.
            ' <span class="fl-transaction-payment-status">'.$payment_status.'</span>'.
            '</span>';
    } else {
        $parts = [$post_status,$payment_status];
        return implode(' : ',$parts);
    }

}