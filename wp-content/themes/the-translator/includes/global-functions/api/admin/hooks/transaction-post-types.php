<?php

/*
 * current-php-code 2020-Jan-16
 * current-hook
 * input-sanitized :
 */
global $fl_payments;
function add_faq_post_type()
{
    $labels = array(
        'name' => _x('FAQ', 'Post Type General Name', 'translator'),
        'singular_name' => _x('FAQ', 'Post Type Singular Name', 'translator'),
        'menu_name' => __('FAQs', 'translator'),
        'parent_item_colon' => __('Parent FAQ', 'translator'),
        'all_items' => __('All FAQs', 'translator'),
        'view_item' => __('View FAQ', 'translator'),
        'add_new_item' => __('Add New FAQ', 'translator'),
        'add_new' => __('Add New', 'translator'),
        'edit_item' => __('Edit FAQ', 'translator'),
        'update_item' => __('Update FAQ', 'translator'),
        'search_items' => __('Search FAQ', 'translator'),
        'not_found' => __('Not Found', 'translator'),
        'not_found_in_trash' => __('Not found in Trash', 'translator'),
    );
    $args = array(
        'label' => __('FAQs', 'translator'),
        'description' => __('FAQ news and reviews', 'translator'),
        'labels' => $labels,
        //'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'custom-fields'),
        'supports' => array('title', 'editor', 'excerpt'),
        'public' => false,  // it's not public, it shouldn't have it's own permalink, and so on
        'publicly_queriable' => true,  // you should be able to query it
        'show_ui' => true,  // you should be able to edit it in wp-admin
        'exclude_from_search' => true,  // you should exclude it from search results
        'show_in_nav_menus' => false,  // you shouldn't be able to add it to menus
        'has_archive' => false,  // it shouldn't have archive page
        'rewrite' => false,  // it shouldn't have rewrite rules
        'menu_icon' => 'dashicons-category',
        'capability_type' => 'post',
    );
    register_post_type('faq', $args);
}

add_action('init', 'add_faq_post_type', 0);

function add_custom_post_type()
{
    $labels = array(
        'name' => _x('Job', 'Post Type General Name', 'translator'),
        'singular_name' => _x('Job', 'Post Type Singular Name', 'translator'),
        'menu_name' => __('Jobs', 'translator'),
        'parent_item_colon' => __('Parent Job', 'translator'),
        'all_items' => __('All Jobs', 'translator'),
        'view_item' => __('View Job', 'translator'),
        'add_new_item' => __('Add New Job', 'translator'),
        'add_new' => __('Add New', 'translator'),
        'edit_item' => __('Edit Job', 'translator'),
        'update_item' => __('Update Job', 'translator'),
        'search_items' => __('Search Job', 'translator'),
        'not_found' => __('Not Found', 'translator'),
        'not_found_in_trash' => __('Not found in Trash', 'translator'),
    );
    $args = array(
        'label' => __('Jobs', 'translator'),
        'description' => __('Job news and reviews', 'translator'),
        'labels' => $labels,
        //'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'custom-fields'),
        'supports' => array('title', 'author', 'thumbnail', 'comments'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'can_export' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-universal-access-alt',
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'register_meta_box_cb' => 'add_job_metaboxes'
    );
    register_post_type('job', $args);
}

add_action('init', 'add_custom_post_type', 0);


function add_job_metaboxes()
{

    add_meta_box('wpt_job_job_created_date', 'Job created date', 'wpt_job_job_created_date', 'job', 'normal', 'high');
    add_meta_box('wpt_job_standard_delivery_date', 'Job standard delivery date', 'wpt_job_standard_delivery_date', 'job', 'normal', 'default');
    add_meta_box('wpt_modified_id', 'Modified Id', 'wpt_modified_id', 'job', 'normal', 'default');

    add_meta_box('wpt_job_instruction_file', 'Job instructions file', 'wpt_job_instruction_file', 'job', 'normal', 'default');
    add_meta_box('wpt_job_translating_file', 'Job translationg file', 'wpt_job_translating_file', 'job', 'normal', 'default');
    add_meta_box('wpt_job_translated_file', 'Job translated file', 'wpt_job_translated_file', 'job', 'normal', 'default');

}

function wpt_job_instruction_file()
{
    echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename" value="' .
        wp_create_nonce(plugin_basename(__FILE__)) . '" />';

}

function wpt_job_translating_file()
{
    global $post;
    global $wpdb;
    echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename" value="' .
        wp_create_nonce(plugin_basename(__FILE__)) . '" />';

    $trans_text_exist = $wpdb->get_results("SELECT * FROM wp_files where post_id=$post->ID and status=1");
    for ($i = 0; $i < count($trans_text_exist); $i++) { ?>
        <div class="col-md-12">
            <div class="doc-name">
                <a href="<?php echo get_site_url() . '?action=download_job_file&attach_id=' . $trans_text_exist[$i]->id; ?>&lang=all"><i
                            class="text-doc-icon"></i><?php echo $trans_text_exist[$i]->file_name; ?></a>
            </div>
        </div>
        <?php
    }
}

function wpt_job_translated_file()
{
    global $post;
    global $wpdb;
    $post_id = $post->ID;
    $Translated_files = $wpdb->get_results("SELECT * FROM wp_files where post_id=$post_id and status=2");
    for ($i = 0; $i < count($Translated_files); $i++) {
     ?>
        <div class="col-md-12">
            <div class="doc-name">
                <a href="<?php echo get_site_url() . '?action=download_job_file&attach_id=' . $Translated_files[$i]->id; ?>&lang=all"><i
                            class="text-doc-icon"></i><?php echo ' ' . $Translated_files[$i]->file_name; ?></a>
                <a href="#" class="cross-icon"
                   onclick="return remove_file_by_admin_file(<?php echo $Translated_files[$i]->id; ?>)"></a>
            </div>
        </div>
        <?php
    }
    if (count($Translated_files) == 0 && true) {
        echo 'No upload yet.';
    }
}

function wpt_job_job_created_date()
{
    global $post;
    echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename" value="' .
        wp_create_nonce(plugin_basename(__FILE__)) . '" />';
    $value = get_post_meta($post->ID, 'job_created_date', true);
    echo '<input type="text" name="job_created_date" value="' . $value . '" class="widefat" />';
}





function wpt_job_standard_delivery_date()
{
    global $post;
    echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename" value="' .
        wp_create_nonce(plugin_basename(__FILE__)) . '" />';
    $value = get_post_meta($post->ID, 'job_standard_delivery_date', true);
    echo '<input type="text" name="job_standard_delivery_date" value="' . $value . '" class="widefat" />';
}





function wpt_modified_id()
{
    global $post;
    echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename" value="' .
        wp_create_nonce(plugin_basename(__FILE__)) . '" />';
    $value = get_post_meta($post->ID, 'modified_id', true);
    echo '<input type="text" name="modified_id" value="' . $value . '" class="widefat" />';
}


/**
 * Kept as placeholder for future work
 * @param $post_id
 * @param $post
 * @return int
 */
function wpt_save_job_meta($post_id, $post)
{
    will_do_nothing($post_id);
    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if (!isset($_POST['eventmeta_noncename']) || !wp_verify_nonce($_POST['eventmeta_noncename'], plugin_basename(__FILE__))) {
        return $post->ID;
    }
    // Is the user allowed to edit the post or page?
    if (!current_user_can('edit_post', $post->ID))
        return $post->ID;
    // OK, we're authenticated: we need to find and save the data
    // We'll put it into an array to make it easier to loop though.

    $events_meta = [];
    // Add values of $events_meta as custom fields
    foreach ($events_meta as $key => $value) { // Cycle through the $events_meta array!
        if ($post->post_type == 'revision') return 0; // Don't store custom data twice
        $value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
        if (get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
            update_post_meta($post->ID, $key, $value);
        } else { // If the custom field doesn't have a value
            add_post_meta($post->ID, $key, $value);
        }
        if (!$value) delete_post_meta($post->ID, $key); // Delete if blank
    }
    return $post->ID;
}

add_action('save_post', 'wpt_save_job_meta', 1, 2); // save the custom fields


function my_remove_meta_boxes()
{
    remove_meta_box('commentstatusdiv', 'job', 'normal');
}

add_action('admin_menu', 'my_remove_meta_boxes');


function my_rem_editor_from_post_type()
{

    $labels = array(
        'name' => _x('Transaction', 'Post Type General Name', 'wallet'),
        'singular_name' => _x('Transaction', 'Post Type Singular Name', 'wallet'),
        'all_items' => __('All Transaction', 'wallet'),
        'view_item' => __('View Transaction', 'wallet'),
        'add_new_item' => __('Add New Transaction', 'wallet'),
        'add_new' => __('Add New', 'wallet'),
        'edit_item' => __('Edit Transaction', 'wallet'),
        'update_item' => __('Update Transaction', 'wallet'),
        'search_items' => __('Search (Only ID of Trans or Author)', 'wallet'),
        'not_found' => __('Not Found', 'wallet'),
        'not_found_in_trash' => __('Not found in Trash', 'wallet'),
    );
    $args = array(
        'label' => __('Transaction', 'wallet'),
        'description' => __('Transaction news and reviews', 'wallet'),
        'labels' => $labels,
        'supports' => array('title', 'author'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => false,
        'show_in_admin_bar' => false,
        'can_export' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-cart',
        'exclude_from_search' => false,
        'publicly_queryable' => false,
        'capability_type' => 'post',
        'register_meta_box_cb' => 'add_wallet_metaboxes'
    );
    register_post_type('wallet', $args);

}

add_action('init', 'my_rem_editor_from_post_type');


add_filter('posts_where', 'wallet_search_where');
function wallet_search_where($where)
{
    //code-notes used in wallet search on the transaction post pa
    global $pagenow, $wpdb;


    // I want the filter only when performing a search on edit page of Custom Post Type named "segnalazioni"
    if (is_admin() && $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'wallet' && isset($_GET['s']) && trim($_GET['s']) != '') {
        $where_1 = " look_at_me.post_id IS NOT NULL ";
        $where = preg_replace(
            "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            $where_1, $where);

    }

    /*echo get_local_time_by_timeZone($format = 'YYYY-MM-DD hh:mm A');
    exit;	*/
    //print_r($where); exit;
    return $where;
}

//code-notes used in wallet search on the transaction post page
function custom_posts_join($join)
{
    global $_REAL_GET;
    global $pagenow, $wpdb;

    $search_string = '';
    if (isset($_REAL_GET['s']) && trim($_REAL_GET['s'])) {
        $search_string = trim($_REAL_GET['s']);
    }

    if (is_admin() && $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'wallet' && $search_string) {
        // inner join to the $wpdb->posts.ID
       // $join .= " LEFT JOIN $wpdb->postmeta as meta_1 ON $wpdb->posts.ID = meta_1.post_id";
        $int_user = intval($search_string);
        $escaped_s = esc_sql($search_string);
        $liked_s = $escaped_s."%";
        $sql_inner = "SELECT look.post_id FROM wp_transaction_lookup look WHERE look.user_id = $int_user OR look.txn like '$liked_s'";
        $join .= " LEFT JOIN ($sql_inner) as look_at_me  ON $wpdb->posts.ID = look_at_me.post_id";
    }
    return $join;
}

add_filter('posts_join', 'custom_posts_join');


add_action('restrict_manage_posts', 'my_restrict_manage_posts');
//code-notes makes the select box to search types, and displays total for result sets
function my_restrict_manage_posts()
{
    global $typenow;//, $post, $post_id;
    if ($typenow == "wallet") {
        // Get the location data if its already been entered
        $location = isset($_REQUEST['_transactionType']) ? $_REQUEST['_transactionType'] : '';
        // Echo out the field
        echo '<select type="text" name="_transactionType">';
        echo '<option value=""> Transaction Type </option>';

        $option_values = FLTransactionLookup::TRANSACTION_TYPE_VALUES;
        foreach ($option_values as $key => $value) {
            if ($value == $location) {
                echo '<option value="' . $value . '" selected>' . ucfirst($value) . '</option>';
            } else {
                echo '<option value="' . $value . '">' . ucfirst($value) . '</option>';
            }
        }
        echo '</select>';

        global $wpdb;
        $q_query = $GLOBALS['wp_query']->request;
        $q_query_array = explode('LIMIT', $q_query);
        if (!empty($q_query_array)) {
            //code-notes show the total amount on the top, next to the select menu, if a search was done
            $result = $wpdb->get_results($q_query_array[0]); // print_R($result);
            $sum = 0;
            $sum_cu = 0;
            foreach ($result as $key => $value) {
                $found_sum = floatval(get_post_meta($value->ID, FLTransactionLookup::META_KEY_TRANSACTION_AMOUNT, true));
                $_transactionType_val = get_post_meta($value->ID, FLTransactionLookup::META_KEY_TRANSACTION_TYPE, true);
                if ($_transactionType_val == FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS] ||
                    $_transactionType_val== FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS_REFUND] ||
                    $_transactionType_val== FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS_USED])
                {

                    $sum_cu = $found_sum + $sum_cu ;

                } else {
                    $sum=  $found_sum + $sum;
                }

            }
            if ($sum_cu == 0) {
                echo '<div class="transaction_total large-text"><b>Total Amount</b>: ' . amount_format($sum) . '</div>';
            } else {
                echo '<div class="transaction_total large-text"><b>Total Amount</b>: ' . amount_format($sum) . '(' . amount_format($sum_cu) . ' credit)</div>';
            }
        }
        ?>
        <!--suppress CssUnusedSymbol -->
        <style>
            .transaction_total {
                width: 300px;
                text-align: center;
                float: right;
                font-weight: bold;
            }

            input#post-query-submit {
                float: left;
            }
        </style>
        <?php
    }

}


add_action('pre_get_posts', 'my_show_appropriate_posts');
//code-notes limits the posts displayed on the transaction post page, can be modified to hide or show more transactions based on status or what not
/**
 * @param WP_Query $query
 */
function my_show_appropriate_posts($query)
{
    $current_page = isset($_GET['post_type']) ? $_GET['post_type'] : '';
    /** Unset so that we can control exactly where this part of the query is included */
    if ('wallet' == $current_page && isset($_GET['_transactionType']) && $_GET['_transactionType'] != '') {
        $query->set('author', null);

        $_transactionType = $_GET['_transactionType'];
        /** Ensure that the relevant tables required for a meta query are included */
        $query->set('meta_query', array(
            array(
                'key' => FLTransactionLookup::META_KEY_TRANSACTION_TYPE,
                'value' => $_transactionType, // This cannot be empty because of a bug in WordPress
                'compare' => '='
            )
        ));
    }
}

function my_custom_posts_where($where = '')
{
    //code-notes unused but keep as template for later
    global $wpdb;
    /** Add our required conditions to the '$where' portion of the query */
    $where .= sprintf(
        ' AND ( %1$s.post_author = %2$s OR ( %3$s.meta_key = "_transactionType" AND %3$s.meta_value = %2$s ) )',//unused
        $wpdb->posts,
        get_current_user_id(),
        $wpdb->postmeta
    );
    /** Remove the filter to call this function, as we don't want it any more */
    remove_filter('posts_where', 'my_custom_posts_where');
    return $where;
}

add_filter('manage_edit-wallet_columns', 'add_new_wallet_columns');

//code-notes adds columns to the list of transactions
function add_new_wallet_columns($gallery_columns)
{
    global $wp_query,$fl_payments;
    $post_ids_of_interest = [];
    foreach ($wp_query->posts as $da_post) {
        $post_ids_of_interest[] = $da_post->ID;
    }
    $fl_payments = FLPaymentSummary::make_summaries($post_ids_of_interest);
    will_do_nothing($gallery_columns);
    $new_columns['cb'] = '<input type="checkbox" />';

    $new_columns['_modified_transaction_id'] = _x('Transaction ID', 'column name');
    $new_columns['title'] = __('Transaction Title');
    $new_columns['_transactionType'] = __('Transaction Type');
    $new_columns['_transactionAmount'] = __('Transaction Amount');
    $new_columns['_transactionRelatedTo'] = __('Transaction Related To');
    $new_columns['author'] = __('Author (Above search: enter ID only in Search Transaction)');
    $new_columns['date'] = _x('Date', 'column name');

    return $new_columns;
}

add_action('manage_wallet_posts_custom_column', 'manage_wallet_columns', 10, 2);

//code-notes fills in the custom columns
function manage_wallet_columns($column_name, $id)
{
    global $fl_payments;
    switch ($column_name) {
        case 'id':
            echo $id;
            break;
        case '_modified_transaction_id':
            $_modified_transaction_id = get_post_meta($id, FLTransactionLookup::META_KEY_MODIFIED_TRANSACTION_ID, true);
            echo $_modified_transaction_id;
            break;
        case '_transactionType':
            $_transactionType = get_post_meta($id, FLTransactionLookup::META_KEY_TRANSACTION_TYPE, true);
            if ($_transactionType == FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_WITHDRAW]) {
                $_transactionType = 'withdraw(' . get_post_meta($id, '_transactionWithdrawStatus', true) . ')';
            }
            echo $_transactionType;
            break;
        case '_transactionAmount':
            $_transactionAmount = get_post_meta($id, FLTransactionLookup::META_KEY_TRANSACTION_AMOUNT, true);     //  Total Amount
            //echo $_transactionAmount;

            $_transactionType = get_post_meta($id, FLTransactionLookup::META_KEY_TRANSACTION_TYPE, true);
            if ($_transactionType == FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS] ||
                $_transactionType== FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS_REFUND] ||
                $_transactionType== FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_FREE_CREDITS_USED])
            {
                echo $_transactionAmount . ' ( credit )';
            } else {
                echo $_transactionAmount;
            }
            break;

        case '_transactionRelatedTo':
           ?>
            <div class="fl-payment-summary">
            <?php
            if (array_key_exists($id,$fl_payments)) {
                //top transaction!
                $my_payment = $fl_payments[$id];
                $parent_txn = $my_payment->transaction_post_txn;
                $status = $my_payment->payment_status;
                ?>

                    <span class="fl-transaction-post-link  fl-parent-txn fl-transaction-status-<?= $status?>" data-pid="<?= $my_payment->transaction_post_id ?>">
                            <?= $parent_txn?>
                    </span>

                    <span class="fl-transaction-txn" >
                            <?= $my_payment->txn_id?>
                    </span>

                    <div class="fl-transaction-steps">
                        <?php
                        foreach($my_payment->ipn as $ipn) {
                            ?>
                            <div class="fl-transaction-step">
                                <span class="fl-transaction-step-status">
                                    <?= $ipn->payment_status ?>
                                </span>

                                <span class="fl-transaction-step-fl-status fl-transaction-status-<?= $ipn->fl_payment_status?>">
                                    <?= $ipn->fl_payment_status ?>
                                </span>
                                <br>
                                <span class="fl-transaction-step-txn">
                                    <?= $ipn->txn_id ?>
                                </span>
                            </div><!-- /.fl-transaction-step-->
                            <?php
                        }
                        ?>
                    </div><!-- /.fl-transaction-steps-->

                <?php
            }
            // Get number of images in gallery
            $job_id = get_post_meta($id, FLTransactionLookup::META_KEY_TRANSACTION_RELATED_TO, true);
            $modified_id = get_post_meta($job_id, 'modified_id', true);
            if (empty($modified_id)) {
                $modified_id = get_post_meta($job_id,FLTransactionLookup::META_KEY_MODIFIED_TRANSACTION_ID,true);
            }

            if ($modified_id) {
                ?>
                <span class="fl-transaction-post-link  fl-parent-txn" data-pid="<?= $job_id ?>">
                    <?= $modified_id?>
                </span>
                <?php
            }
            ?>
            </div> <!-- /.fl-payment-summary-->
            <?php
            break;
        default:
            break;
    } // end switch
}


/*
 * ADMIN COLUMN - SORTING - MAKE HEADERS SORTABLE
 */
add_filter("manage_edit-wallet_sortable_columns", 'wallet_sort');
function wallet_sort($columns)
{
    $custom = array(
        '_transactionType' => '_transactionType',
        '_transactionRelatedTo' => '_transactionRelatedTo',
        '_transactionAmount' => '_transactionAmount',
        'author' => 'author',
        '_modified_transaction_id' => '_modified_transaction_id',
    );
    return wp_parse_args($custom, $columns);

}


/*
 * ADMIN COLUMN - SORTING - ORDERBY
 */
//code-notes sorts transactions
add_filter('request', '_transactionType_column_orderby');
function _transactionType_column_orderby($vars)
{
    if (isset($vars['orderby']) && '_transactionType' == $vars['orderby']) {
        $vars = array_merge($vars, array(
            'meta_key' => FLTransactionLookup::META_KEY_TRANSACTION_TYPE,
            //'orderby' => 'meta_value_num', // does not work
            'orderby' => 'meta_value'
            //'order' => 'asc' // don't use this; blocks toggle UI
        ));
    } elseif (isset($vars['orderby']) && '_transactionRelatedTo' == $vars['orderby']) {
        $vars = array_merge($vars, array(
            'meta_key' => FLTransactionLookup::META_KEY_TRANSACTION_RELATED_TO,
            //'orderby' => 'meta_value_num', // does not work
            'orderby' => 'meta_value'
            //'order' => 'asc' // don't use this; blocks toggle UI
        ));
    } elseif (isset($vars['orderby']) && '_transactionAmount' == $vars['orderby']) {
        $vars = array_merge($vars, array(
            'meta_key' => FLTransactionLookup::META_KEY_TRANSACTION_AMOUNT,
            //'orderby' => 'meta_value_num', // does not work convert('meta_value', decimal)
            'orderby' => 'meta_value_num' //CAST(meta_value AS INTEGER)
            //'order' => 'asc' // don't use this; blocks toggle UI
        ));
    } elseif (isset($vars['orderby']) && '_modified_transaction_id' == $vars['orderby']) {
        $vars = array_merge($vars, array(
            'meta_key' => FLTransactionLookup::META_KEY_MODIFIED_TRANSACTION_ID,
            //'orderby' => 'meta_value_num', // does not work
            'orderby' => 'meta_value'
            //'order' => 'asc' // don't use this; blocks toggle UI
        ));
    }
    //print_R($vars);
    return $vars;
}

//code-notes adds detail to the wallet post page
//task-future-work add new meta box for total payment history
function add_wallet_metaboxes()
{
    global $post;
    add_meta_box('wpt_wallet_transactionType', 'Transaction Type', 'wpt_wallet_transactionType', 'wallet', 'normal', 'default');
    add_meta_box('wpt_wallet_transactionAmount', 'Transaction Amount', 'wpt_wallet_transactionAmount', 'wallet', 'normal', 'default');
    add_meta_box('wpt_wallet_transactionReason', 'Transaction Reason', 'wpt_wallet_transactionReason', 'wallet', 'normal', 'high');
    if (get_post_meta($post->ID, FLTransactionLookup::META_KEY_TRANSACTION_TYPE, true) ==
        FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_WITHDRAW])
    {
        add_meta_box('wpt_wallet_transactionWithdrawStatus', 'Transaction Withdraw Status', 'wpt_wallet_transactionWithdrawStatus', 'wallet', 'side', 'high');
    }
    if (get_post_meta($post->ID, FLTransactionLookup::META_KEY_TRANSACTION_TYPE, true) ==
        FLTransactionLookup::TRANSACTION_TYPE_VALUES[FLTransactionLookup::TRANSACTION_TYPE_REFILL] )
    {
        add_meta_box('wpt_wallet_payment_type', 'Transaction Payment Method', 'wpt_wallet_payment_type', 'wallet', 'normal', 'high');
        add_meta_box('wpt_wallet_txn_id', 'Transaction Txn Id', 'wpt_wallet_txn_id', 'wallet', 'normal', 'high');
    }
}

//code-notes wallet meta box callback
function wpt_wallet_transactionType()
{
    global $post;
    ?>
    <!--suppress CssUnusedSymbol -->
    <style>
        div#icl_div_config { display: none;}
    </style>
    <?php
    echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename" value="' .
        wp_create_nonce(plugin_basename(__FILE__)) . '" />';
    // Get the location data if its already been entered
    $location = get_post_meta($post->ID, FLTransactionLookup::META_KEY_TRANSACTION_TYPE, true);
    // Echo out the field
    echo '<select type="text" name="_transactionType">';
    $option_values = FLTransactionLookup::TRANSACTION_TYPE_VALUES;
    foreach ($option_values as $key => $value) {
        if ($value == $location) {
            echo '<option selected>' . $value . '</option>';
        } else {
            echo '<option>' . $value. '</option>';
        }
    }
    echo '</select>';
}

//code-notes wallet meta box callback
function wpt_wallet_transactionAmount()
{
    global $post;
    echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename" value="' .
        wp_create_nonce(plugin_basename(__FILE__)) . '" />';
    // Get the location data if its already been entered
    $location = get_post_meta($post->ID, FLTransactionLookup::META_KEY_TRANSACTION_AMOUNT, true);
    // Echo out the field
    echo '<input type="text" name="_transactionAmount" value="' . $location . '" class="widefat" />';
}

//code-notes wallet meta box callback
function wpt_wallet_transactionReason()
{
    global $post;
    // noncename needed to verify where the data originated
    echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename1" value="' .
        wp_create_nonce(plugin_basename(__FILE__)) . '" />';
    // Get the location data if its already been entered
    $location = get_post_meta($post->ID, FLTransactionLookup::META_KEY_TRANSACTION_REASON, true);    // Echo out the field
    echo '<input type="text" name="_transactionReason" value="' . $location . '" class="widefat" />';
}

//code-notes wallet meta box callback
function wpt_wallet_transactionWithdrawStatus()
{
    global $post;
    // Noncename needed to verify where the data originated
    echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename1" value="' .
        wp_create_nonce(plugin_basename(__FILE__)) . '" />';
    // Get the location data if its already been entered
    $value = get_post_meta($post->ID, '_transactionWithdrawStatus', true);    // Echo out the field
    echo '<select name="_transactionWithdrawStatus" class="widefat" >';
    echo ($value == "pending") ? '<option value="pending" selected> Pending</option>' : '<option value="pending"> Pending</option>';
    echo ($value == "completed") ? '<option value="completed" selected>Completed </option>' : '<option value="completed"> Completed</option>';
    echo '</select>';
}


//code-notes wallet meta box callback
function wpt_wallet_payment_type()
{
    global $post;
    echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename" value="' .
        wp_create_nonce(plugin_basename(__FILE__)) . '" />';
    // Get the location data if its already been entered
    $location = get_post_meta($post->ID, FLTransactionLookup::META_KEY_PAYMENT_TYPE, true);
    // Echo out the field
    echo '<input type="text" name="_payment_type" value="' . $location . '" class="widefat" />';
}

//code-notes wallet meta box callback
function wpt_wallet_txn_id()
{
    global $post;
    echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename" value="' .
        wp_create_nonce(plugin_basename(__FILE__)) . '" />';
    // Get the location data if its already been entered
    $location = get_post_meta($post->ID, FLTransactionLookup::META_KEY_MODIFIED_TRANSACTION_ID, true);
    // Echo out the field
    echo '<input type="text" name="_modified_transaction_id" value="' . $location . '" class="widefat" />';
}



function wpt_save_wallet_meta($post_id, $post)
{
    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if (!isset($_POST['eventmeta_noncename']) || !wp_verify_nonce($_POST['eventmeta_noncename'], plugin_basename(__FILE__))) {
        return $post->ID;
    }
    will_send_to_error_log('this is the save handler but only saves withdraw status');
    // OK, we're authenticated: we need to find and save the data
    // We'll put it into an array to make it easier to loop though.
    if (get_post_meta($post_id, '_transactionWithdrawStatus', true) == 'pending' || get_post_meta($post_id, '_transactionWithdrawStatus', true) == 'pending') {
        $_transactionWithdrawStatus = ($_POST['_transactionWithdrawStatus'] == 'completed') ? 'completed' : 'pending';
        // Add values of $events_meta as custom fields
        $events_meta['_transactionWithdrawStatus'] = $_transactionWithdrawStatus;
        foreach ($events_meta as $key => $value) { // Cycle through the $events_meta array!

            update_post_meta($post->ID, $key, $value);

        }
    }

    return $post->ID;
}

add_action('save_post', 'wpt_save_wallet_meta', 1, 2); // save the custom fields


add_filter('post_type_link', 'freeling_job_type_slug', 1, 3);

/**
 * code-notes calculates url for job posts
 * @param $link
 * @param WP_Post $post
 * @return string
 */
function freeling_job_type_slug( $link, $post = null ){
    $lang = FLInput::get('lang','en');
    if ( $post && $post->post_type == 'job' ){
        $nu_url = home_url( 'job/' . $post->post_title );
        $nu_url_with_lang_for_sure = add_query_arg(  ['lang'=>$lang], $nu_url);
        return $nu_url_with_lang_for_sure;
    }else {
        return $link;
    }

}



