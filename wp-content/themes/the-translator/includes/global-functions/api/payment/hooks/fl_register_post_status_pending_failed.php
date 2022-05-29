<?php

add_action( 'init', 'fl_register_post_status_transaction' );

class FLTransactionPost {

    const TRANSACTION_NEW = 'new_transaction';
    const TRANSACTION_PENDING = 'pending_transaction';
    const TRANSACTION_FAILED = 'failed_transaction';
    const TRANSACTION_COMPLETE = 'publish';

    public static function payment_history_to_transaction_status($payment_status) {
        switch ($payment_status) {
            case FLPaymentHistoryIPN::FL_PAYMENT_STATUS_COMPLETE: {
                return static::TRANSACTION_COMPLETE;
            }
            case FLPaymentHistoryIPN::FL_PAYMENT_STATUS_PENDING: {
                return static::TRANSACTION_PENDING;
            }
            case FLPaymentHistoryIPN::FL_PAYMENT_STATUS_FAILED: {
                return static::TRANSACTION_FAILED;
            }
            default: {
                throw new RuntimeException("Invalid fl_payment state $payment_status");
            }
        }
    }
}

// Register Custom Post Status
function fl_register_post_status_transaction(){
    register_post_status( FLTransactionPost::TRANSACTION_PENDING, array(
        'label'                     => _x( 'Pending Transaction', 'post' ),
        'public'                    => false,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Pending Transaction <span class="count">(%s)</span>',
                                                    'Pending Transactions <span class="count">(%s)</span>' ),
    ) );


    register_post_status( FLTransactionPost::TRANSACTION_FAILED, array(
        'label'                     => _x( 'Failed Transaction', 'post' ),
        'public'                    => false,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Failed Transaction <span class="count">(%s)</span>',
                                                    'Failed Transactions <span class="count">(%s)</span>' ),
    ) );

    register_post_status( FLTransactionPost::TRANSACTION_NEW, array(
        'label'                     => _x( 'New Transaction', 'post' ),
        'public'                    => false,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'New Transaction <span class="count">(%s)</span>',
            'New Transactions <span class="count">(%s)</span>' ),
    ) );
}


// Display Custom Post Status Option in Post Edit
add_action('admin_footer', 'fl_display_post_status_transaction_pending');
function fl_display_post_status_transaction_pending(){
    global $post;
    //will_dump('me post',$post);
    if (empty($post)) {return;}
    $selected = '';
    if($post->post_type == 'wallet'){
        if($post->post_status == 'pending_transaction'){
            $selected = 'selected';
        }
        ?>
        <script>
            jQuery(function($){
                $("select#post_status").append('<option value="pending_transaction"<?= $selected ?> >Pending Transaction</option>');
                $(".misc-pub-section label").append('<span id="post-status-display"> Pending Transaction </span>');
            });
        </script>

        <?php

    }
}


add_action('admin_footer', 'fl_display_post_status_transaction_failed');
function fl_display_post_status_transaction_failed(){
    global $post;
    //will_dump('me post',$post);
    if (empty($post)) {return;}
    $selected = '';
    if($post->post_type == 'wallet'){
        if($post->post_status == 'failed_transaction'){
            $selected = 'selected';
        }
        ?>
        <script>
            jQuery(function($){
                $("select#post_status").append('<option value="failed_transaction"<?= $selected ?> >Failed Transaction</option>');
                $(".misc-pub-section label").append('<span id="post-status-display"> Failed Transaction </span>');
            });
        </script>

        <?php

    }
}

