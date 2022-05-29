<?php

add_action( 'wp_ajax_get_latest_all_offers',  'get_latest_all_offers'  );

function get_latest_all_offers(){

      /*
       * current-php-code 2020-Oct-14
       * ajax-endpoint  get_latest_all_offers
       * input-sanitized : content_id
       */
    global $wpdb;



    $content_id = (int)FLInput::get('content_id');

    $contentOth = $wpdb->get_results("select * from wp_linguist_content where  user_id IS NOT NULL AND id = $content_id", ARRAY_A);

    $msg =  '<h2 class="modal-title">All offers</h2>';

    $price_array = array();
    $allOffers = unserialize($contentOth[0]['offersBy']);
    if (empty($allOffers)) { $allOffers = [];}
    $msg .= '<div class="row">
				<div class="col-md-12">
					<div class="table-responsive">
						<table width="100%" class="table table-bordered">
							<tbody>
							<tr>
								<th>User</th>
								<th>Amount</th>
								 <th>Status</th>
								  <th>Bid Time</th>
								
							</tr>';

    foreach ($allOffers as $inOffers) {
        $userDetail = get_userdata($inOffers['cust_id']);

        array_push($price_array,$inOffers['amount']);

        $msg .= '<tr>
								<td>'.$userDetail->display_name[0].'*****'.'</td>
								<td>$'.$inOffers['amount'].'</td>
								<td>'.ucfirst($inOffers['status']).'</td>
								<td>'.
                                    '<span class="freelinguist-date-block a-timestamp-full-date-time"
                                            data-ts="'.$inOffers['created_at'].'"></span>'.
                              '</td>
							</tr>';

    }


    $msg .='</tbody>
						</table>
						
						
							   

						   
					</div>
				</div>


			</div>';


    echo json_encode(array("msg"=>$msg));
    exit;



}