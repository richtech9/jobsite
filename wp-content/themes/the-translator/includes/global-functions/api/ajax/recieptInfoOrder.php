<?php

add_action('init','recieptInfoOrder');
function recieptInfoOrder(){
    /*
    * current-php-code 2020-Oct-14
    * ajax-endpoint  contentreciept  (not a true ajax)
    * public-api
    * input-sanitized : action,receipt
    */
    $action = FLInput::get('action');
    $transaction_id = (int)FLInput::get('receipt',0);
    //code-notes prevent anyone from seeing any receipt , also currently allows anyone in the world to monitor sales in real time
    $current_user_id = (int) get_current_user_id();

    if($action === 'contentreciept' && $current_user_id){
        if($transaction_id){
            require_once(ABSPATH . '/wp-content/themes/the-translator/includes/MPDF57/mpdf.php');
            global $wpdb;
            $privacy_url            = get_site_url().'/privacy-peerok';

            $terms_url              = get_site_url().'/terms-of-service';

            if (current_user_can('administrator')) {
                $maybe_user_check = '1';
            } else {
                $maybe_user_check = ' user_id = '.$current_user_id;
            }

            //code-notes if the logged in user is admin, then allow them to see any receipt, else must be logged in user
            $row 	= (array)$wpdb->get_row( "SELECT * FROM wp_fl_transaction WHERE  id=$transaction_id AND $maybe_user_check" );
            $small_logo = get_template_directory().'/images/logo-60-by-60.jpg';

            if(is_array($row) && count($row)){
                //code-bookmark this is where the receipt is made into a pdf
                $html_head = '

             <div style="width:800px;  font-size:14px; color:#000;">

             <div style="padding:20px;border:1px solid #000;">
             <table style="padding:0; margin:0; border-spacing:0; border-collapse:collapse; width:100%;">

             <tr>
             <td><img src="'.$small_logo.'" alt="logo_image" /></td>
             </tr>

             </table>
             <div style="text-align:center; font-size:30px; font-weight:bold; color: #888; padding-top:10px; margin-bottom:30px;">Receipt</div>';


                $html_body = '<div style="margin-bottom:20px;">

             <p style="margin:10px 0;"><b>Date:</b> '.date('d-m-Y',strtotime($row['time'])).'</p>

             <p style="margin:10px 0;"><b>Transaction ID:</b> '.$row['txn_id'].'</p>
             <p style="margin:10px 0;"><b>Description:</b> '.$row['description'].'</p>
             <p style="margin:10px 0;"><b>Total:</b> $ '.$row['amount'].'</p>

             </div>

             <div style="background:#f7f7f7; padding:20px; margin-top:10px;">



             <p style="text-align:center; /* replaced fontsize 12 */ margin-bottom:0;"><a href="'.
                    $terms_url.'" style="text-decoration:none;">Terms of services</a> | <a href="'.
                    $privacy_url.'" style="text-decoration:none;">Privacy</a> | <a href="'.get_site_url().
                    '" style="text-decoration:none;">Copyright@peerok.com</a></p>

             </div>
             ';
                $html = $html_head.$html_body.'</div></div>';



                $mpdf= @ new mPDF();

                $mpdf->showImageErrors = true;

                error_reporting(E_ERROR);

                $mpdf->WriteHTML($html);

                $mpdf->Output('Receipt.pdf', 'I');
                exit;
            }

        }

    }
}