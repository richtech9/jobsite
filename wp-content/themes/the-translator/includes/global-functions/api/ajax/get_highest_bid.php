<?php

add_action( 'wp_ajax_get_highest_bid',  'get_highest_bid'  );

function get_highest_bid(){

    /*
     * current-php-code 2020-Oct-14
     * ajax-endpoint  get_highest_bid
     * input-sanitized : content_id
     */

    global $wpdb;

    $content_id = (int)FLInput::get('content_id');
    try {

        $content = FreelinguistContentHelper::get_content_extended_information($content_id);
        if ($content['purchased_by']) {
            throw new RuntimeException("Cannot bid, content already purchased");
        }


        $price_array = array();
        $allOffers = unserialize($content['offersBy']);
        if (empty($allOffers)) {
            $allOffers = [];
        }

        foreach ($allOffers as $inOffers) {
            $offer_price = 0;
            if (isset($inOffers['amount'])) {
                $offer_price = (float)$inOffers['amount'];
            }

            if ($offer_price) {
                array_push($price_array, $inOffers['amount']);
            }

        }
        $msg = '';
        if (count($price_array)) {
            $msg = max($price_array);
        }
        $ret = [
            'status'=>1,
            'message' => $msg,
            'bid_floor' => $content['content_amount']
        ];

        wp_send_json($ret);
        exit;
    } catch (Exception $e) {
        $ret = [
            'status'=>0,
            'message' => $e->getMessage()
        ];

        wp_send_json($ret);
        exit;
    }


}