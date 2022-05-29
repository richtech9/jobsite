<?php


/*
 * current-php-code 2021-Jan-10
 * input-sanitized :
 * current-wp-template:  admin-screen  for reports
 */

function function_for_reports()
{
    global $wpdb;
    $lang = FLInput::get('lang','en');
    $reports = $wpdb->get_results("SELECT * FROM wp_reports order by id desc");
    ?>


    <script type="text/javascript">
        jQuery(function () {
            jQuery('#example').DataTable({
                "order": [[1, "desc"]]
            });

        });
    </script>
    <span class="bold-and-blocking large-text">Reports</span>
    <hr>
    <table id="example" class="table table-striped table-bordered" style="width:100%">
        <thead>
        <tr>
            <th scope="col" class="manage-column column-primary">Id #</th>
            <th scope="col" class="manage-column column-primary">Time</th>
            <th scope="col" class="manage-column">Reported By</th>
            <th scope="col" class="manage-column">Content</th>
            <th scope="col" class="manage-column">Linguist</th>
            <th scope="col" class="manage-column">Project</th>
            <th scope="col" class="manage-column">Contest</th>
            <th scope="col" class="manage-column">Note</th>
            <th scope="col" class="manage-column">Status</th>
            <th scope="col" class="manage-column">Admin Note</th>
            <th scope="col" class="manage-column">Processed By</th>


        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($reports as $report) {


            if ($report->status == "pending") {
                $statusOptions = '<select statId="' . $report->id . '" class="selectStatus" id="select_status_' . $report->id . '">
											<option value="">--select--</option>
											<option value="pending">Pending</option>
											<option value="processed">Processed</option>
											
										</select>';
            } else {
                $statusOptions = 'Processed';
            }


            if ($report->content != "" && $report->content != 0) {
                $content_link = get_site_url() . '/content/?lang=' . $lang . '&mode=view&content_id=' . base64_encode($report->content) . '';
            } else {
                $content_link = "#";
            }
            $reported_user_data = get_userdata($report->reported_by);
            $processed_by_user_data = get_userdata($report->processed_by);
            $linguist_user_data = get_userdata($report->linguist);


            echo '<tr>
					<td class="has-row-actions column-primary">' . $report->id . '</td>
					<td class="has-row-actions column-primary">' . $report->time . '</td>
					<td class="has-row-actions column-primary">' . ($reported_user_data? $reported_user_data->user_email: '') . '</td>
					<td class="has-row-actions column-primary"><a href="' . $content_link . '" target="_blank">' . $report->content . '</a></td>
					<td class="has-row-actions column-primary">' . ($linguist_user_data? $linguist_user_data->user_email: '') . '</td>
					<td class="has-row-actions column-primary"><a href="' . get_the_permalink($report->project) . '" target="_blank">' . $report->project . '</a></td>
					<td class="has-row-actions column-primary"><a href="' . get_the_permalink($report->contest) . '" target="_blank">' . $report->contest . '</a></td>
					<td class="has-row-actions column-primary">' . $report->report_note . '</td>
					<td class="has-row-actions column-primary">' . $statusOptions . '</td>
					<td class="has-row-actions column-primary"><textarea  autocomplete="off" id="textarea_' . $report->id . '">' . $report->admin_comment . '</textarea><button class="save_admin_note" data-report_id="' . $report->id . '">Save</button></td>
					<td class="has-row-actions column-primary">' . ($processed_by_user_data? $processed_by_user_data->user_nicename: '')  . '</td>
				</tr>';
        }
        ?>
        </tbody>
        <tfoot>
        <tr>
            <th scope="col" class="manage-column column-primary">Id #</th>
            <th scope="col" class="manage-column column-primary">Time</th>
            <th scope="col" class="manage-column">Reported By</th>
            <th scope="col" class="manage-column">Content</th>
            <th scope="col" class="manage-column">Linguist</th>
            <th scope="col" class="manage-column">Project</th>
            <th scope="col" class="manage-column">Contest</th>
            <th scope="col" class="manage-column">Note</th>
            <th scope="col" class="manage-column">Status</th>
            <th scope="col" class="manage-column">Admin Note</th>
            <th scope="col" class="manage-column">Processed By</th>

        </tr>
        </tfoot>
    </table>
    <script>


        jQuery(function () {

            jQuery('.selectStatus').change(function () {


                var selStatus = jQuery(this).val();
                var dbId = jQuery(this).attr('statId');
                var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
                var data = {
                    'action': 'hz_reportStatus_save',
                    'selStatus': selStatus,
                    'dbId': dbId,
                };

                jQuery.post(ajaxurl, data, function (/*response*/) {
                    setTimeout(function () {
                        window.location.reload(true);
                    }, 10);
                    //alert(response);
                });


            });

            jQuery('.save_admin_note').click(function () {
                var id = jQuery(this).data('report_id');
                var value = jQuery('#textarea_' + id + '').val();
                var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
                var data = {
                    'action': 'hz_reportAdminnote_save',
                    'value': value,
                    'id': id,
                };

                jQuery.post(ajaxurl, data, function (/*response*/) {
                    setTimeout(function () {
                        window.location.reload(true);
                    }, 10);
                    //alert(response);
                });
            });


            /*************************************/
        });


    </script>
    <?php
}