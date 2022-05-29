<?php

function viewOffersHtml($content_detail = array())
{
    /*
    * current-php-code 2020-Nov-25
    * internal-call
    * input-sanitized :
   */

    //code-bookmark displays the offers display that pops up on the linguist's content page, from the 'view all offers' button
    $html = '';
    if (get_current_user_id() == $content_detail['user_id']) {

        $allOffers = unserialize($content_detail['offersBy']);
//     will_send_to_error_log('$allOffers',$allOffers);
        if (!empty($content_detail['offersBy'])) {
            $purchased_by = $content_detail['purchased_by'];

            $userData = get_user_by('id', $purchased_by);
            if (!empty($userData) && property_exists($userData, 'id') && $userData->id != '') {

                $html .= '<p><strong>Buyer:</strong> ' . $userData->first_name . '' . $userData->last_name . '<p>';

            } else {


                $html .= '<a id=""  class="btn blue-btn next-btn postproject freelinguist-table-inline-button regular-text" href="#" data-toggle="modal" data-target="#viewAllOfferModel_' . $content_detail['id'] . '">

        <i class="fa fa-shopping-cart regular-text" style="color: #fff !important;"></i> View all offers</a>';

                $html .= '<div class="modal fade" id="viewAllOfferModel_' . $content_detail['id'] . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

        <div class="modal-dialog awardpop-box modal-lg">

        <div class="modal-content" style="width: fit-content;">

        <div class="modal-header">

        <button type="button" class="close huge-text" data-dismiss="modal">&times;</button>

        <h4 class="modal-title">List of Offers by Customers</h4>

        </div>

        <div class="modal-body offersTab">';

                $html .= '<div class="row">
        <div class="col-md-12">
        <div class="table-responsive">
        <table width="100%" class="table table-bordered viewAllOfferTable fl-freelancer-content-sell">

        <thead>
        <tr>
        <th>User</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Bid Time</th>
        <th>Action</th>

        </tr></thead><tbody>';


                foreach ($allOffers as $inOffers) {
                    $userDetail = get_userdata($inOffers['cust_id']);

                    //array_push($price_array, $inOffers['amount']);

                    $html .= '<tr>
                                   <td>' . $userDetail->display_name . ' ( ' . $userDetail->user_email . ' )</td>
                                   <td><span class="fl-bid-amount">$' . amount_format($inOffers['amount']) . '</span></td>
                                   <td>' . ucfirst($inOffers['status']) . ' <span class="respoReply' . $inOffers['cust_id'] . '"></span></td>
                                   <td><span class=" a-timestamp-full-date-time"
                                            data-ts="'.$inOffers['created_at'].'"></span></td>';
                    if ($inOffers['status'] == 'processing') {
                        $html .= '<td><a uid="' . $inOffers['cust_id'] . '" cid="' . $content_detail['id'] .
                            '" style=" width: 150px;" class="btn blue-btn next-btn postproject accpetrejOffer freelinguist-table-inline-button regular-text" '.
                            'todo="accept" href="#"><i class="fa fa-shopping-cart  regular-text" style="color: white !important;"></i> Accept Offer</a><a uid="' .
                            $inOffers['cust_id'] . '" cid="' . $content_detail['id'] .
                            '" style="margin-top: 3px; width: 150px;" '.
                            'class="btn blue-btn next-btn postproject accpetrejOffer freelinguist-table-inline-button grey regular-text" '.
                            'todo="reject" href="#"><i class="fa fa-shopping-cart regular-text" style="color: white !important;"></i> Reject</a></td>';
                    } else {
                        $html .= '<td></td>';
                    }

                    $html .= '</tr>';

                }


                $html .= '</tbody>
                       </table>
                      
                       </div>
                       </div>
                       </div>';


                $html .= '</div>
                
                       </div>
                
                       </div>
                
                       </div> ';

            }


        } else {

            $html .= '<span>No offers till now.</span>';

        }


        return $html;

    } else {
        return '<span>User not logged in</span>';
    }
}