<?php

/*
    * current-php-code 2021-Jan-11
    * input-sanitized : lang
    * current-wp-template:  admin-screen  for content disputes
*/

function function_for_content_cases()
{
    global $wpdb;

    $lang = FLInput::get('lang,','en');
    $contestDisputes = $wpdb->get_results("SELECT * FROM wp_dispute_cases WHERE content_id IS NOT NULL order by id desc");
    ?>

    <script type="text/javascript">
        jQuery(function () {
            jQuery('#example').DataTable({
                "order": [[1, "desc"]]
            });
    </script>
    <span class="bold-and-blocking large-text">Manage Content Cases</span>
    <hr>
    <table id="example" class="table table-striped table-bordered" style="width:100%">
        <thead>
        <tr>
            <th scope="col" class="manage-column column-primary">Case #</th>
            <th scope="col" class="manage-column column-primary">Content Id</th>
            <th scope="col" class="manage-column">Customer</th>
            <th scope="col" class="manage-column">Linguist</th>
            <th scope="col" class="manage-column">Current Status</th>
            <th scope="col" class="manage-column">Change Status</th>
            <th scope="col" class="manage-column">Partial Approval</th>
            <th scope="col" class="manage-column">Freeze</th>
            <th scope="col" class="manage-column">Processed By</th>
            <th scope="col" class="manage-column">Open Date</th>
            <th scope="col" class="manage-column">Deadline Date</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $switcher = new FLSwitchUserHelper();
        foreach ($contestDisputes as $contestData) {
            $customer_info = get_userdata($contestData->customer_id);
            $linguist_info = get_userdata($contestData->linguist_id);
            $mediator_info = get_userdata($contestData->mediator_id);
            $resultcustomer = $customer_info->user_email;
            $resultlingusit = $linguist_info->user_email;

            $option_html = '';
            for ($i = 1; $i <= 100; $i++) {
                if ($contestData->approved_partially == $i) {
                    $selOpt = 'selected';
                } else {
                    $selOpt = '';
                }
                $option_html .= '<option ' . $selOpt . ' value="' . $i . '"> ' . $i . ' </option>';
            }
            $statusOptions = '';

            if ($contestData->status == "under_process") {
                $statusOptions = '<select statId="' . $contestData->ID . '" class="selectStatus" id="select_status_' . $contestData->ID . '">
											<option value="">--select--</option>
											<option value="approved">Full Approve</option>
											<option value="approved_partially">Approve Partial</option>
											<option value="rejected_by_mediator">Reject</option>
										</select>';
            }
            $currStatus = $contestData->status;
            if ($currStatus == 'approved') {
                $showStat = '<p style="color:green">Approved</p>';
            } elseif ($currStatus == 'approved_partially') {
                $showStat = '<p style="color:green">Approved Partially .' . $contestData->approved_partially . '%</p>';
            } elseif ($currStatus == 'rejected_by_mediator') {
                $showStat = '<p style="color:red">Rejected</p>';
            } else {
                $showStat = '<p style="color:black">Under Process</p>';
            }


            $parOptions = '<select id="partialShow' . $contestData->ID . '" statId="' . $contestData->ID . '" class="partialPay">' . $option_html . '</select><button class="partialPayButton" id="partialPayButton' . $contestData->ID . '" data-sid = "' . $contestData->ID . '">Ok</button>';

            $link_of_content_plain = get_site_url() . '/content/?lang=' . $lang . '&mode=view&content_id=' . base64_encode($contestData->content_id);
            $link_of_content = $switcher->generate_switch_redirect_url($contestData->customer_id,$link_of_content_plain);

            $freebutton = 'Frozen';

            if (!$contestData->freeze_job) {
                $freebutton = '<button statId="' . $contestData->ID . '" class="freeze_button">Freeze</button>';
            }

            echo '<tr>
					<td class="has-row-actions column-primary">' . $contestData->ID . '</td>
					<td class="has-row-actions column-primary"><a href="' . $link_of_content . '" target="_blank">' . $contestData->content_id . '</a></td>
					<td class="has-row-actions column-primary">' . $resultcustomer . '</td>
					<td class="has-row-actions column-primary">' . $resultlingusit . '</td>
					<td class="has-row-actions column-primary">' . $showStat . '</td>
					<td class="has-row-actions column-primary">' . $statusOptions . '</td>
					<td class="has-row-actions column-primary">' . $parOptions . '</td>
					<td class="has-row-actions column-primary">' . $freebutton . '</td>
					<td class="has-row-actions column-primary">' . ($mediator_info? ucfirst($mediator_info->user_nicename): '') . '</td>
					<td class="has-row-actions column-primary">' . date('Y-M-d', strtotime($contestData->post_date)) . '</td>
					<td class="has-row-actions column-primary">' . date("Y-M-d", strtotime($contestData->post_date . "+20 days")) . '</td>
				</tr>';
        }
        ?>
        </tbody>
        <tfoot>
        <tr>
            <th scope="col" class="manage-column column-primary">Case #</th>
            <th scope="col" class="manage-column column-primary">Content Id</th>
            <th scope="col" class="manage-column">Customer</th>
            <th scope="col" class="manage-column">Linguist</th>
            <th scope="col" class="manage-column">Current Status</th>
            <th scope="col" class="manage-column">Change Status</th>
            <th scope="col" class="manage-column">Partial Approval</th>
            <th scope="col" class="manage-column">Freeze</th>
            <th scope="col" class="manage-column">Processed By</th>
            <th scope="col" class="manage-column">Open Date</th>
            <th scope="col" class="manage-column">Deadline Date</th>
        </tr>
        </tfoot>
    </table>
    <script>
        jQuery(function () {

            jQuery('.partialPay').hide();
            jQuery('.partialPayButton').hide();
            jQuery('.selectStatus').change(function () {
                var selStatus = jQuery(this).val();
                var dbId = jQuery(this).attr('statId');
                var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
                var data = {
                    'action': 'hz_contentStatus_save',
                    'selStatus': selStatus,
                    'dbId': dbId,
                };

                if (selStatus === 'approved_partially') {
                    jQuery('#partialShow' + dbId).show();
                    jQuery('#partialPayButton' + dbId).show();
                } else {
                    jQuery.post(ajaxurl, data, function (/*response*/) {
                        setTimeout(function () {
                            window.location.reload(true);
                        }, 10);
                    });

                }

            });
            jQuery('.partialPayButton').click(function () {

                var con = confirm("Are you sure !");

                if (con) {
                    var sid = jQuery(this).data('sid');
                    var partial = jQuery('#partialShow' + sid).val();
                    var dbId = jQuery('#partialShow' + sid).attr('statId');
                    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
                    var data = {
                        'action': 'hz_contentpartialPay_save',
                        'partial': partial,
                        'dbId': dbId,
                    };
                    jQuery.post(ajaxurl, data, function (/*response*/) {
                        setTimeout(function () {
                            window.location.reload(true);
                            ;
                        }, 10);
                    });
                } else {
                    return false;
                }
            });

            /******* freeze button for content *********/

            jQuery('.freeze_button').click(function () {

                var con = confirm("Are you sure !");

                if (con) {
                    var dbId = jQuery(this).attr('statId');
                    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
                    var data = {
                        'action': 'hz_content_freeze',

                        'dbId': dbId,
                    };
                    jQuery.post(ajaxurl, data, function (/*response*/) {
                        setTimeout(function () {
                            window.location.reload(true);
                        }, 10);
                    });
                } else {
                    return false;
                }
            });

            /*************************************/
        });
    </script>
    <?php
}

