<?php

/*

 * Author Name: Lakhvinder Singh

 * Method:      recieptInfo

 * Description: recieptInfo function using to make the reciepts related to job transacrion or refill related transactions

 *

 */

add_action('init','recieptInfo');

function recieptInfo(){



    /*
    * current-php-code 2020-December-10
    * ajax-endpoint  recieptInfo  (not a true ajax)
    * public-api
    * input-sanitized : action,receipt
    */

    $action = FLInput::get('action');
    $receipt_id = (int)FLInput::get('receipt',0);//post id
    $type                   = FLInput::get('type');
    $usd_formatter = numfmt_create( 'en_US', NumberFormatter::CURRENCY );


    $current_user_id = (int) get_current_user_id();
    if($current_user_id && $action && $action === 'recieptInfo'){

        if($receipt_id){

            //code-notes prevent anyone from seeing any receipt , also currently allows anyone in the world to monitor sales in real time



            $post_data              = get_post( $receipt_id );
            if (!$post_data) {die();}
            if (!$post_data->ID) {die();} //code-notes make sure to end download early if there is no receipt

            if (!current_user_can('administrator')) {
                $post_author = (int)$post_data->post_author;
                if ($post_author !== $current_user_id) {
                    die();//code-notes just die if no auth as this is a download
                }
            }



            $user_name              = get_display_name(get_current_user_id());

            $user_town_city         = get_user_meta(get_current_user_id(),'user_town_city',true);

            $user_state             = get_user_meta(get_current_user_id(),'user_state',true);

            $user_zip_postal_code   = get_user_meta(get_current_user_id(),'user_zip_postal_code',true);

            $user_address_line_1           = get_user_meta(get_current_user_id(),'user_address_line_1',true);

            $user_address_line_2           = get_user_meta(get_current_user_id(),'user_address_line_2',true);

            if(!empty($user_address_line_2)){

                $user_address_line_2 = $user_address_line_2.'<br/>';

            }

            $user_country           = get_user_meta(get_current_user_id(),'user_residence_country',true);

            if(!empty($user_country)){

                $user_country = get_country_by_index($user_country);

            }

            $logo_image_new             = get_template_directory_uri().'/images/logo-60-by-60.jpg';


            $privacy_url            = get_site_url().'/privacy-peerok';

            $terms_url              = get_site_url().'/terms-of-service';




            $post_date              = date_formatted($post_data->post_date).' (UTC) ';


            $modified_transaction_id= get_post_meta($receipt_id,FLTransactionLookup::META_KEY_MODIFIED_TRANSACTION_ID,true);

            $transactionReason      = get_post_meta($receipt_id,FLTransactionLookup::META_KEY_TRANSACTION_REASON,true);

            $transactionAmount      = get_post_meta($receipt_id,FLTransactionLookup::META_KEY_TRANSACTION_AMOUNT,true);




            //echo $transactionType; exit;

            $html_head = '

            <div style="width:800px; font-size:14px; color:#000;">

            <div style="border:1px solid #000; padding:20px;">

            <table style="padding:0; margin:0; border-spacing:0; border-collapse:collapse; width:100%;">

            <tr>

            <td><img src="'.$logo_image_new.'" alt="logo_image" /></td>

            <td style="width:40%; vertical-align:top;">

            <table style="padding:0; margin:0; border-spacing:0; border-collapse:collapse; width:100%;">

            <tr>

            <td style="width:20px; vertical-align:top; padding-right:15px;"><b>To:</b></td>

            <td>

            '.$user_name.'<br/>

            '.$user_address_line_1.'<br/>

            '.$user_address_line_2.

                $user_town_city.','.$user_state.','.$user_zip_postal_code.'<br/>

            '.$user_country.'

            </td>

            </tr>

            </table>

            </td>

            </tr>

            </table>

            <div style="background:#f7f7f7; padding:20px; margin-top:25px;">

            <div style="text-align:center; font-size:30px; font-weight:bold; color: #888; padding-top:10px; margin-bottom:30px;">Receipt</div>';

            if($type == 1){

                // For FREE CREDITS


                $html_body = '<div style="margin-bottom:20px;">

                <p style="margin:10px 0;"><b>Order placed:</b> '.$post_date.'</p>

                <p style="margin:10px 0;"><b>PeerOK.com transaction ID:</b> '.$modified_transaction_id.'</p>

                <p style="margin:10px 0;"><b>Order total:</b>  '.$usd_formatter->formatCurrency($transactionAmount,'USD').' '.$transactionAmount.'</p>

                </div>

                <div style=" font-size: 18px; padding:8px; font-weight:bold; border-top:1px solid #ddd; border-bottom:1px solid #ddd;">Items Ordered</div>

                <table style=" margin:0; border-spacing:0; padding:5px 0; width:100%; background:#fff;">

                <tr>

                <td colspan="2" style=" padding:10px; width:30%;"><b>'.$transactionReason.':</b></td>                                    

                </tr>

                <tr>

                <td style="padding:10px; width:30%;"><b>Total amount:</b></td>

                <td >$'.$usd_formatter->formatCurrency($transactionAmount,'USD').'</td>

                </tr>

                </table>

                <div style="height:30px;">&nbsp;</div>

                <div style=" font-size: 18px; padding:8px; font-weight:bold; border-top:1px solid #ddd; border-bottom:1px solid #ddd;">Payment information

                </div>

                <table style=" margin:0; border-spacing:0; padding:5px 0; width:100%; background:#fff;">

                <tr>

                <td style="padding:10px; width:30%;"><b>Total amount:</b></td>

                <td> '.$usd_formatter->formatCurrency($transactionAmount,'USD').'</td>

                </tr>

                <tr>

                <td style="padding:10px; width:30%;"><b>FREE Credits total:</b></td>

                <td > '.$usd_formatter->formatCurrency($transactionAmount,'USD').'</td>

                </tr>                                 

                </table>

                <div style=" text-align:right; font-size:18px; padding:8px; font-weight:bold; margin-top:20px;">FREE Credits total:  '.$usd_formatter->formatCurrency($transactionAmount,'USD').'</div>

                </div> 

                <div style="background:#f7f7f7; padding:20px; margin-top:10px;">

                <p style="margin:0; text-align:center; /* replaced fontsize 12 */">Note: In dollar amounts, "-" means credit decrease, and "+" means credit increase</p>

                <p style="text-align:center; /* replaced fontsize 12 */ margin-bottom:0;">
                <a href="'.$terms_url.'" style="text-decoration:none;">Terms of services</a> | 
                <a href="'.$privacy_url.'" style="text-decoration:none;">Privacy</a> | 
                <a href="'.get_site_url().'" style="text-decoration:none;">Copyright@PeerOK.com</a>
                </p>

                </div>  

                </div>

                </div>';

            }elseif($type == 2){

                // For Refill and withdraw


                $html_body = '<div style="margin-bottom:20px;">

                <p style="margin:10px 0;"><b>Order placed:</b> '.$post_date.'</p>

                <p style="margin:10px 0;"><b>PeerOK.com transaction ID:</b> '.$modified_transaction_id.'</p>

                <p style="margin:10px 0;"><b>Order total:</b>  '.$usd_formatter->formatCurrency($transactionAmount,'USD').'</p>

                </div>

                <div style=" font-size: 18px; padding:8px; font-weight:bold; border-top:1px solid #ddd; border-bottom:1px solid #ddd;">Items Ordered</div>

                <table style=" margin:0; border-spacing:0; padding:5px 0; width:100%; background:#fff;">

                <tr>

                <td colspan="2" style=" padding:10px; width:30%;"><b>'.$transactionReason.'</b></td>                                    

                </tr>

                <tr>

                <td style="padding:10px; width:30%;"><b>Total amount:</b></td>

                <td > '.$usd_formatter->formatCurrency($transactionAmount,'USD').'</td>

                </tr>

                </table>

                <div style="height:30px;">&nbsp;</div>

                <div style=" font-size: 18px; padding:8px; font-weight:bold; border-top:1px solid #ddd; border-bottom:1px solid #ddd;">Payment information

                </div>

                <table style=" margin:0; border-spacing:0; padding:5px 0; width:100%; background:#fff;">

                <tr>

                <td style="padding:10px; width:30%;"><b>Total amount:</b></td>

                <td> '.$usd_formatter->formatCurrency($transactionAmount,'USD').'</td>

                </tr>

                <tr>

                <td style="padding:10px; width:30%;"><b>Order Total:</b></td>

                <td > '.$usd_formatter->formatCurrency($transactionAmount,'USD').'</td>

                </tr>                                 

                </table>

                <div style=" text-align:right; font-size:18px; padding:8px; font-weight:bold; margin-top:20px;">Credit total:  '.$usd_formatter->formatCurrency($transactionAmount,'USD').'</div>

                </div> 

                <div style="background:#f7f7f7; padding:20px; margin-top:10px;">

                <p style="text-align:center; /* replaced fontsize 12 */ margin-bottom:0;">
                <a href="'.$terms_url.'" style="text-decoration:none;">Terms of services</a> | 
                <a href="'.$privacy_url.'" style="text-decoration:none;">Privacy</a> | 
                <a href="'.get_site_url().'" style="text-decoration:none;">Copyright@PeerOK.com</a>
                </p>

                </div>  

                </div>

                </div>';



            }else{

                $html_body = '';

            }

            $html = $html_head.$html_body;

            /** @noinspection PhpIncludeInspection */
            require_once(get_template_directory().'/includes/MPDF57/mpdf.php');

            $mpdf=new mPDF();

            $mpdf->showImageErrors = true;

            error_reporting(E_ERROR);

            $mpdf->WriteHTML($html);

            $mpdf->Output('Receipt.pdf', 'I');

            exit;


        }

    }

}