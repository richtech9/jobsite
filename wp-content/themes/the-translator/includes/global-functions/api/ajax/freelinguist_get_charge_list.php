<?php



add_action( 'wp_ajax_freelinguist_get_charge_list',  'freelinguist_get_charge_list'  );

/*
 * 




 * 
Creating contests: customer_creating_contest
    post competition fee
    processing fee
    competition fee
    insurance fee (sometimes)

Awarding another proposal: customer_awarding_another_proposal
    additional award prize amount
    Processing fee

Hiring linguist (from their profile page): customer_hiring_linguist
    referral fee for customer

Selecting winning bid: customer_selecting_winning_bid
    referral fee for customer

Customer Creating a milestone: customer_creating_milestone
    Processing fee for customer
    Milestone Amount

Customer Approving a milestone: customer_approving_milestone
    Processing fee for customer
    Milestone Amount

Freelancer Starting a job: freelancer_starting_job
    referral fee for freelancer

Freelancer Asking for Content Mediation: freelancer_asking_content_mediation
    hire mediator fee

Freelancer Asking for Proposal Mediation: : freelancer_asking_proposal_mediation
    hire mediator fee

Freelancer Asking for Project Mediation : : freelancer_asking_project_mediation
    hire mediator fee

Freelancer Seals Files in Proposal :  freelancer_sealing_own_proposal
    sealing fee

Freelancer Pays to see other proposals: : freelancer_viewing_other_proposals
    view sealed fee 

 */

function freelinguist_get_charge_list() {
    global $wpdb;
    /*
   * current-php-code 2020-Nov-6
   * ajax-endpoint  freelinguist_get_charge_list
   * input-sanitized : amount, charge_type, flag
   */

    $flag_unset_val = -99999;
    
    $charge_type = FLInput::get('charge_type');
    $amount = floatval(FLInput::get('amount',0));
    $flag = (int)FLInput::get('flag',$flag_unset_val);
    $id = (int) FLInput::get('id');

    $formatter = new NumberFormatter( 'en_US', NumberFormatter::CURRENCY );
    //$formatter->setAttribute( \NumberFormatter::MAX_FRACTION_DIGITS, 0 ) ;

    try {
        $dets = [];
        switch ($charge_type) {

            case 'customer_creating_contest': {
                /*
                * generateOrderByCustomerNew() IF jQuery('#fl-project-type').val() === 'contest'
                * amount ==> jQuery('#estimated_budgets').val(); //float
                * flag ==> (jQuery("#is_guaranted").prop('checked')) ? 1 : 0;
                */
                $text_title = "Posting Competition";
                if (!$amount) {throw new InvalidArgumentException("amount needs to be > 0");}
                if ($flag === $flag_unset_val) {throw new InvalidArgumentException("flag needs to be > 0");}
                $total = 0.0;

                $dets[] = [
                    'charge_name'=>'Competition Prize',
                    'amount'=>$amount,
                    'amount_formatted' => $formatter->formatCurrency($amount,'USD'),
                    'description'=>'',
                ];

                $total += $amount;

                $getFee1 = floatval(get_option('client_referral_fee') ? get_option('client_referral_fee') : 2);
                $getFee_percentage = floatval(get_option('client_flex_referral_fee') ? get_option('client_flex_referral_fee') : 2.5);
                $getFee2 = ($amount*$getFee_percentage)/100;

                $getFee = $getFee1 + $getFee2;
                $total += $getFee;

                $contestFee = floatval(get_option('contest_fee') ? get_option('contest_fee') : 0);
                $total += $contestFee;
                $combined_fee = $contestFee + $getFee;
                $dets[] = [
                    'charge_name'=>'Processing Fee',
                    'amount'=>$combined_fee,
                    'amount_formatted' => $formatter->formatCurrency($combined_fee,'USD'),
                    'description'=>'',
                ];





                if ($flag ) {

                    $base = floatval($amount);
                    $insuranceBaseFee  = get_option('contest_insurance_fee_base') ? floatval(get_option('contest_insurance_fee_base')) : 0;
                    $insuranceBasePercentage = get_option('contest_insurance_fee_rate') ? floatval(get_option('contest_insurance_fee_rate')) : 0;
                    $insurancePercentageUnrounded = $base * ($insuranceBasePercentage/100);
                    $insurancePercentage = round($insurancePercentageUnrounded,2);
                    $getInsuranceCost = $insuranceBaseFee + $insurancePercentage;
                    $total += $getInsuranceCost;

                    $dets[] = [
                        'charge_name' => 'Insurance Fee',
                        'amount' => $getInsuranceCost,
                        'amount_formatted' => $formatter->formatCurrency($getInsuranceCost,'USD'),
                        'description' => '',
                    ];
                }


                $total_formatted = $formatter->formatCurrency($total,'USD');
                $html_general_description = '';
                break;
            }






            case 'customer_awarding_another_proposal': {

                /*
                 * jQuery(document).on('click', '#yes_proposal_btn', function()
                 *  id ==> jQuery('#yes_proposal_btn').attr('contestId')
                 *
                 */
                if (!$id) {throw new InvalidArgumentException("id needs to be > 0");}
                $total = 0.0;
                $text_title = "Awarding Another Proposal";
                $estimated_budgets = floatval(get_post_meta($id,'estimated_budgets',true));
                $total += $estimated_budgets;
                $dets[] = [
                    'charge_name'=>'additional award prize amount',
                    'amount'=>$estimated_budgets,
                    'amount_formatted' => $formatter->formatCurrency($estimated_budgets,'USD'),
                    'description'=>'',
                ];

                $getFee1 = get_option('client_referral_fee') ? get_option('client_referral_fee') : 2;
                $getFee_percentage = get_option('client_flex_referral_fee') ? get_option('client_flex_referral_fee') : 2.5;
                $getFee2 = ($estimated_budgets*$getFee_percentage)/100;
                $getFee = $getFee1+$getFee2;
                $total += $getFee;

                $dets[] = [
                    'charge_name'=>'Processing fee',
                    'amount'=>$getFee,
                    'amount_formatted' => $formatter->formatCurrency($getFee,'USD'),
                    'description'=>'',
                ];


                $total_formatted = $formatter->formatCurrency($total,'USD');
                $html_general_description = '';
                break;
            }









            case 'customer_hiring_linguist': {

                /*
                 * hire_linguist
                 */
                $referral_fee       =  get_option('client_referral_fee');
                $text_title = "Hiring";

                $dets[] = [
                    'charge_name'=>'referral fee',
                    'amount'=>$referral_fee,
                    'amount_formatted' => $formatter->formatCurrency($referral_fee,'USD'),
                    'description'=>'',
                ];

                $total = $referral_fee;
                $total_formatted = $formatter->formatCurrency($total,'USD');
                $html_general_description = '';
                break;
            }






            case 'customer_selecting_winning_bid': {

                /*
                 * hireTranslate
                 */
                $referral_fee       =  floatval(get_option('client_referral_fee'));
                $text_title = "Selecting Winning Bid";
                $dets[] = [
                    'charge_name'=>'referral fee',
                    'amount'=>$referral_fee,
                    'amount_formatted' => $formatter->formatCurrency($referral_fee,'USD'),
                    'description'=>'',
                ];

                $total = $referral_fee;
                $total_formatted = $formatter->formatCurrency($total,'USD');
                $html_general_description = '';
                break;
            }







            case 'customer_creating_milestone': {

                /**
                 * jQuery("form#hz_create_milestone").on( 'submit', function(){
                 * amount ==> jQuery("input#ms_amount").val() (floatval)
                 */
                if (!$amount) {throw new InvalidArgumentException("amount needs to be > 0");}
                $text_title = "Creating Milestone";
                $total = 0.0;
                $total += $amount;

                $dets[] = [
                    'charge_name'=>'Milestone Amount',
                    'amount'=>$amount,
                    'amount_formatted' => $formatter->formatCurrency($amount,'USD'),
                    'description'=>'',
                ];

                $percentage = floatval(get_option('client_flex_referral_fee') ? get_option('client_flex_referral_fee') : 2.5); //Will manage from admin

                $fee 		= ( $amount * $percentage )/ 100.0;
                $total += $fee;

                $dets[] = [
                    'charge_name'=>'Processing Fee',
                    'amount'=>$fee,
                    'amount_formatted' => $formatter->formatCurrency($fee,'USD'),
                    'description'=>'',
                ];


                $total_formatted = $formatter->formatCurrency($total,'USD');
                $html_general_description = '';
                break;
            }





            case 'customer_approving_milestone': {

                /*
                 * hard coded at wp-content/themes/the-translator/includes/user/single-job/single-job-customer.php line 775
                 * hz_manage_milestone() IF fight ==='approve'
                 * id ==> param mid in hz_manage_milestone
                 *
                 */
                if (!$id) {throw new InvalidArgumentException("id needs to be > 0");}
                $text_title = "Approving Milestone";
                $sql_statement =
                    "select * from wp_fl_milestones where id = '" . $id . "'";
                $milestone = $wpdb->get_results($sql_statement);

                if (empty($milestone)) {
                    throw new InvalidArgumentException("Cannot find a milestone from id of '$id'");
                }
                $total = 0.0;
                $ms_amount = (float)$milestone[0]->amount;
                $total += $ms_amount;

                $dets[] = [
                    'charge_name'=>'Milestone Amount',
                    'amount'=>$ms_amount,
                    'amount_formatted' => $formatter->formatCurrency($ms_amount,'USD'),
                    'description'=>'',
                ];

                $percentage = floatval(get_option('client_flex_referral_fee') ? get_option('client_flex_referral_fee') : 2.5);
                $fee = ($ms_amount * $percentage) / 100;
                $total += $fee;

                $dets[] = [
                    'charge_name'=>'Processing Fee',
                    'amount'=>$fee,
                    'amount_formatted' => $formatter->formatCurrency($fee,'USD'),
                    'description'=>'',
                ];


                $total_formatted = $formatter->formatCurrency($total,'USD');
                $html_general_description = '';
                break;
            }







            case 'freelancer_starting_job': {

                /**
                 * when hz_start_job has act === 'start'
                 */
                $referral_fee  = floatval(get_option( 'linguist_referral_fee' ));
                $text_title = "Starting Job";
                $dets[] = [
                    'charge_name'=>'Referral Fee',
                    'amount'=>$referral_fee,
                    'amount_formatted' => $formatter->formatCurrency($referral_fee,'USD'),
                    'description'=>'',
                ];

                $total = $referral_fee;
                $total_formatted = $formatter->formatCurrency($total,'USD');
                $html_general_description = '';
                break;
            }







            case 'freelancer_asking_content_mediation': {

                /**
                 * jQuery('.change_content_status').click(function
                 *  listener when status === "hire_mediator"
                 */
                $text_title = "Start Mediation Process";
                $mediator_fee = floatval(get_option('hire_mediator_fee') ? get_option('hire_mediator_fee') : 14.99);
                $dets[] = [
                    'charge_name'=>'Mediation Fee',
                    'amount'=>$mediator_fee,
                    'amount_formatted' => $formatter->formatCurrency($mediator_fee,'USD'),
                    'description'=>'',
                ];

                $total = $mediator_fee;
                $total_formatted = $formatter->formatCurrency($total,'USD');
                $html_general_description = '';
                break;
            }







            case 'freelancer_asking_proposal_mediation': {

                /**
                 * for jQuery('.change_proposal_status') when attribute status === 'hire_mediator'
                 * hard coded into wp-content/themes/the-translator/includes/user/single-job/contest-translator-winner-proposal.php
                 */
                $text_title = "Start Mediation Process";
                $mediator_fee = floatval(get_option('hire_mediator_fee') ? get_option('hire_mediator_fee') : 14.99);
                $dets[] = [
                    'charge_name'=>'Mediation Fee',
                    'amount'=>$mediator_fee,
                    'amount_formatted' => $formatter->formatCurrency($mediator_fee,'USD'),
                    'description'=>'',
                ];

                $total = $mediator_fee;
                $total_formatted = $formatter->formatCurrency($total,'USD');
                $html_general_description = '';
                break;
            }








            case 'freelancer_asking_project_mediation': {

                /**
                 * hz_manage_milestone js function, if the fight==='hire_mediator'
                 * hard coded three times in the wp-content/themes/the-translator/includes/user/single-job/ind-translator.php
                 */
                $text_title = "Start Mediation Process";
                $mediator_fee = floatval(get_option('hire_mediator_fee') ? get_option('hire_mediator_fee') : 14.99);

                $dets[] = [
                    'charge_name'=>'Mediation Fee',
                    'amount'=>$mediator_fee,
                    'amount_formatted' => $formatter->formatCurrency($mediator_fee,'USD'),
                    'description'=>'',
                ];

                $total = $mediator_fee;
                $total_formatted = $formatter->formatCurrency($total,'USD');
                $html_general_description = '';
                break;
            }





            case 'freelancer_sealing_own_proposal': {

                /**
                 * button class fl-proposals-seal  listener
                 */
                $text_title = "Sealing Your Proposal";
                $seal_fee = floatval(get_option('seal_fee') ? get_option('seal_fee') : 10);

                $dets[] = [
                    'charge_name'=>'Sealing Fee',
                    'amount'=>$seal_fee,
                    'amount_formatted' => $formatter->formatCurrency($seal_fee,'USD'),
                    'description'=>'',
                ];

                $total = $seal_fee;
                $total_formatted = $formatter->formatCurrency($total,'USD');
                $html_general_description = '';
                break;
            }





            case 'freelancer_viewing_other_proposals': {

                /**
                 * button class fl-proposals-view  listener
                 */
                $text_title = "Viwing Other Proposals";
                $fee = floatval(get_option('view_other_proposals_fee',10));

                $dets[] = [
                    'charge_name'=>'View Sealed Fee',
                    'amount'=>$fee,
                    'amount_formatted' => $formatter->formatCurrency($fee,'USD'),
                    'description'=>'',
                ];

                $total = $fee;
                $total_formatted = $formatter->formatCurrency($total,'USD');
                $html_general_description = '';
                break;
            }




            default: {
                throw new InvalidArgumentException("Did not know about '$charge_type'");
            }


        }// end switch

        $wallet_balance = (float)get_user_meta(get_current_user_id(), 'total_user_balance', true);
        $wallet_balance_formatted = $formatter->formatCurrency($wallet_balance,'USD');

        $post_balance = $wallet_balance - $total;
        $post_balance_formatted = $formatter->formatCurrency($post_balance,'USD');

        $what_out = [
            'status' => 1,
            'message' => $text_title,
            'html_general_description'=> $html_general_description,
            'charges' => $dets,
            'total' => $total,
            'total_formatted' => $total_formatted,
            'wallet_balance' => $wallet_balance,
            'wallet_balance_formatted' => $wallet_balance_formatted,
            'post_balance' => $post_balance,
            'post_balance_formatted' => $post_balance_formatted,
        ];
        FreelinguistDebugFramework::note('charge-list-output',$what_out);
        wp_send_json($what_out);
        die(); //never reached but makes code easier to read by phpstorm and human
    } catch (Exception $e) {
        wp_send_json([
            'status' => 0,
            'message' => $e->getMessage()
        ]);
        die(); //never reached but makes code easier to read by phpstorm and human
    }
}