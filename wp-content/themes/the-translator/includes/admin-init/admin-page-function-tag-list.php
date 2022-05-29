<?php

/*
    * current-php-code 2021-Jan-11
    * input-sanitized :
    * current-wp-template:  admin-screen  for tags
*/

//task-future-work improve the tag admin page
function interest_tag_options()
{

    global $wpdb;
    require_once(ABSPATH . 'wp-content/themes/the-translator/includes/admin-init/lib/pagination3.php');

    $rpp = 100; // results per page
    $adjacents = 2;

    $page = (!empty($_GET["paged"]) ? intval($_GET["paged"]) : 0);
    if ($page <= 0) {
        $page = 0;
    } else {
        $page = ($page - 1) * $rpp;
    }

    $reload = admin_url('admin.php?page=freelinguist-admin-tags');


    $orderBy = "ORDER BY ID";
    if (!empty($_REQUEST['sort']) && $_REQUEST['sort_by']) {
        $orderBy = "ORDER BY " . $_REQUEST['sort_by'] . " " . $_REQUEST['sort'];
    }

    $tcount = $wpdb->get_var(/** @lang text */
        "SELECT count(*) FROM `{$wpdb->prefix}interest_tags` wit");

    $sql_statment = /** @lang text */
        "
    SELECT
      interest.*,
      project_jobs.job_ids as project_ids,
      user_jobs.job_ids as profile_ids,
      content_jobs.job_ids as content_ids,
      contest_jobs.job_ids as contest_ids,
      (SELECT COUNT(*) FROM wp_tags_cache_job j  WHERE j.tag_id = interest.ID ) as count_used
    FROM {$wpdb->prefix}interest_tags interest
    
    
      LEFT JOIN (
        SELECT tag_id,GROUP_CONCAT(job_id ORDER BY job_id ) as job_ids  FROM {$wpdb->prefix}tags_cache_job WHERE type = " . FreelinguistTags::PROJECT_TAG_TYPE . "  GROUP BY tag_id
        ) as project_jobs ON project_jobs.tag_id = interest.ID
    
      LEFT JOIN (
                  SELECT tag_id,GROUP_CONCAT(job_id ORDER BY job_id ) as job_ids  FROM {$wpdb->prefix}tags_cache_job WHERE type = " . FreelinguistTags::CONTENT_TAG_TYPE . "  GROUP BY tag_id
                ) as content_jobs ON content_jobs.tag_id = interest.ID
    
      LEFT JOIN (
                  SELECT tag_id,GROUP_CONCAT(job_id ORDER BY job_id ) as job_ids  FROM {$wpdb->prefix}tags_cache_job WHERE type = " . FreelinguistTags::CONTEST_TAG_TYPE . "  GROUP BY tag_id
                ) as contest_jobs ON contest_jobs.tag_id = interest.ID
    
      LEFT JOIN (
                  SELECT tag_id,GROUP_CONCAT(job_id ORDER BY job_id ) as job_ids  FROM {$wpdb->prefix}tags_cache_job WHERE type = " . FreelinguistTags::USER_TAG_TYPE . "  GROUP BY tag_id
                ) as user_jobs ON user_jobs.tag_id = interest.ID
    $orderBy
    LIMIT $page,$rpp

";
    $default_row = $wpdb->get_results($sql_statment);
    will_throw_on_wpdb_error($wpdb);
    if (isset($_POST['addtagg'])) {
        global $wpdb;
        $default = array(
            'tag_name' => $_REQUEST['tag_name'],
            'created_at' => 'NOW()'
        );
        $item = shortcode_atts($default, $_REQUEST);
        will_send_to_error_log("in addtagg , item is ", $item);
        $sql_statment = /** @lang text */
            "SELECT count(id) FROM {$wpdb->prefix}interest_tags WHERE tag_name='" . $_REQUEST['tag_name'] . "'";
        $haveTag = $wpdb->get_var($sql_statment);
        will_throw_on_wpdb_error($wpdb);
        will_send_to_error_log("search in interest tag sql ", $wpdb->last_query);
        will_send_to_error_log("haveTag ", $haveTag);
        if ($haveTag == 0) {
            $wpdb->insert('wp_interest_tags', $item);
            will_throw_on_wpdb_error($wpdb);
            will_send_to_error_log("insert item sql ", $wpdb->last_query);
        } else {
            will_send_to_error_log("nothing done here");
        }
        wp_redirect(admin_url('admin.php?page=freelinguist-admin-tags'));
    }

    $tpages = ($tcount) ? ceil($tcount / $rpp) : 1; // total pages, last page number
    $tSort = '&sort=asc';
    if (!empty($_REQUEST['sort'])) {
        if (strtolower($_REQUEST['sort']) == 'asc') {
            $tSort = '&sort=desc';
        } else {
            $tSort = '&sort=asc';
        }
    }
    $pageReload = $reload . ($page ? '&paged=' . $page : '') . $tSort . (!empty($_REQUEST['sort_by']) ? '&sort_by=' . $_REQUEST['sort_by'] : '');
    ?>
    <?php add_thickbox(); ?>

    <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri().'/includes/admin-init/lib/paginate.css'; ?>">
    <div class="wrap">
        <div id="tag-edit" style="display:none;">
            <div class="form-element" style="display: inline-block;">
                <label for="ContactFormFirstName" style="display: block; padding: 0 0 5px 0;">Tag name</label>
                <input type="hidden" name="tag_id" value="">
                <input type="text" name="tag_namec" id="" placeholder="" value="">
            </div>
            <div class="form-element form-btn" style="display: inline-block;">
                <input type="submit" class="btn black" name="edit-tags" value="Update"
                       style="padding: 3px 10px; background: #23282d; border: none; color: #fff;">
            </div>
        </div>
        <div class="cust-d" style="border: 1px solid #e4e4e4; padding: 10px; background: #fff;">
            <form id="addtag" method="post" action="" class="validate">
                <div class="form-element" style="display: inline-block;">
                    <label for="ContactFormFirstName" style="display: block; padding: 0 0 5px 0;">Tag name</label>
                    <input type="text" name="tag_name" id="ContactFormFirstName" placeholder="" value="">
                </div>
                <div class="form-element form-btn" style="display: inline-block;">
                    <input type="submit" class="btn black" name="addtagg" value="Submit"
                           style="padding: 3px 10px; background: #23282d; border: none; color: #fff;">
                </div>
            </form>
        </div>
        <span class="wp-heading-inline bold-and-blocking larger-text">Interest Keywords</span>
        <hr class="wp-header-end">
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <input id="" class="button action tags-delete" value="Delete All" type="submit">
            </div>
            <div class="tablenav-pages-new" style="float:right;">
                <span class="displaying-num-new" style="float:left;"><?php echo $tcount; ?> items</span>
                <span class="pagination-links-new"
                      style="float:right;"><?php echo paginate_three($reload, $page, $tpages, $adjacents); ?></span>
            </div>
        </div>
        <table class="widefat fixed" cellspacing="0">
            <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                    <input id="cb-select-all-1" type="checkbox">
                </td>
                <th id="columnname" class="manage-column column-columnname" scope="col">S.No</th>
                <th id="columnname"
                    class="manage-column column-columnname <?php echo(!empty($_REQUEST['sort']) && !empty($_REQUEST['sort_by']) && $_REQUEST['sort_by'] == 'tag_name' ? 'sorted ' . strtolower($_REQUEST['sort']) : 'sortable desc'); //echo $tagSort; ?>"
                    scope="col">
                    <a href="<?php echo $pageReload; ?>&sort_by=tag_name">
                        <span>Keyword Name</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Profile Ids</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Project Ids</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Content Ids</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Contest Ids</th>
                <th id="columnname"
                    class="manage-column column-columnname <?php echo(!empty($_REQUEST['sort']) && !empty($_REQUEST['sort_by']) && $_REQUEST['sort_by'] == 'count_used' ? 'sorted ' . strtolower($_REQUEST['sort']) : 'sortable desc'); ?>"
                    scope="col">
                    <a href="<?php echo $pageReload; ?>&sort_by=count_used">
                        <span>Used Count</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
                <th id="columnname"
                    class="manage-column column-columnname <?php echo(!empty($_REQUEST['sort']) && !empty($_REQUEST['sort_by']) && $_REQUEST['sort_by'] == 'created_at' ? 'sorted ' . strtolower($_REQUEST['sort']) : 'sortable desc'); ?>"
                    scope="col">
                    <a href="<?php echo $pageReload; ?>&sort_by=created_at">
                        <span>Created At</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Action</th>

            </tr>
            </thead>
            <tfoot>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                    <input id="cb-select-all-1" type="checkbox">
                </td>
                <th id="columnname" class="manage-column column-columnname" scope="col">S.No</th>
                <th id="columnname"
                    class="manage-column column-columnname <?php echo(!empty($_REQUEST['sort']) && !empty($_REQUEST['sort_by']) && $_REQUEST['sort_by'] == 'tag_name' ? 'sorted ' . strtolower($_REQUEST['sort']) : 'sortable desc'); //echo $tagSort; ?>"
                    scope="col">
                    <a href="<?php echo $pageReload; ?>&sort_by=tag_name">
                        <span>Keyword Name</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Profile Ids</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Project Ids</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Content Ids</th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Contest Ids</th>
                <th id="columnname"
                    class="manage-column column-columnname <?php echo(!empty($_REQUEST['sort']) && !empty($_REQUEST['sort_by']) && $_REQUEST['sort_by'] == 'count_used' ? 'sorted ' . strtolower($_REQUEST['sort']) : 'sortable desc'); ?>"
                    scope="col">
                    <a href="<?php echo $pageReload; ?>&sort_by=count_used">
                        <span>Used Count</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
                <th id="columnname"
                    class="manage-column column-columnname <?php echo(!empty($_REQUEST['sort']) && !empty($_REQUEST['sort_by']) && $_REQUEST['sort_by'] == 'created_at' ? 'sorted ' . strtolower($_REQUEST['sort']) : 'sortable desc'); ?>"
                    scope="col">
                    <a href="<?php echo $pageReload; ?>&sort_by=created_at">
                        <span>Created At</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
                <th id="columnname" class="manage-column column-columnname" scope="col">Action</th>
            </tr>
            </tfoot>

            <tbody>
            <?php
            //$count = ((($page ? $page : 1)-1)*$rpp)+1;

            if ($page > 0) {
                $count = (($page)) + 1;
            } else {
                $count = 1;
            }

            foreach ($default_row as $row) { ?>
                <tr class="alternate">
                    <th scope="row" class="check-column">
                        <label class="screen-reader-text" for="user_1">Select admin</label>
                        <input name="tags[]" id="user_1" class="administrator selected-tags"
                               value="<?php echo $row->ID; ?>" type="checkbox">
                    </th>
                    <td class="column-columnname"><?php echo $count; ?></td>
                    <td class="column-columnname"><?php echo $row->tag_name; ?></td>

                    <td class="column-columnname fl-admin-tag-column-auto-hide" >
                        <?php if (fl_admin_page_tags_count_tags(explode(',',$row->profile_ids)) ) { ?>
                            <div class="fl-admin-tag-column-auto-hide-clicker">
                                <i class="fa fa-caret-down"></i>
                                <?= fl_admin_page_tags_count_tags(explode(',',$row->profile_ids))?> Tags
                            </div>
                            <div class="fl-admin-tag-auto-hide-target">
                                <?php echo $row->profile_ids; ?>
                            </div>
                        <?php } ?>
                    </td>

                    <td class="column-columnname fl-admin-tag-column-auto-hide">
                        <?php if (fl_admin_page_tags_count_tags(explode(',',$row->project_ids)) ) { ?>
                            <div class="fl-admin-tag-column-auto-hide-clicker">
                                <i class="fa fa-caret-down"></i>
                                <?= fl_admin_page_tags_count_tags(explode(',',$row->project_ids))?> Tags
                            </div>
                            <div class="fl-admin-tag-auto-hide-target">
                                <?php echo $row->project_ids; ?>
                            </div>
                        <?php } ?>
                    </td>

                    <td class="column-columnname fl-admin-tag-column-auto-hide">

                        <?php if (fl_admin_page_tags_count_tags(explode(',',$row->content_ids)) ) { ?>
                            <div class="fl-admin-tag-column-auto-hide-clicker">
                                <i class="fa fa-caret-down"></i>
                                <?= fl_admin_page_tags_count_tags(explode(',',$row->content_ids))?> Tags
                            </div>
                            <div class="fl-admin-tag-auto-hide-target">
                                <?php echo $row->content_ids; ?>
                            </div>
                        <?php } ?>
                    </td>

                    <td class="column-columnname fl-admin-tag-column-auto-hide">
                        <?php if (fl_admin_page_tags_count_tags(explode(',',$row->contest_ids)) ) { ?>
                            <div class="fl-admin-tag-column-auto-hide-clicker">
                                <i class="fa fa-caret-down"></i>
                                <?= fl_admin_page_tags_count_tags(explode(',',$row->contest_ids))?> Tags
                            </div>
                            <div class="fl-admin-tag-auto-hide-target">
                                <?php echo $row->contest_ids; ?>
                            </div>
                        <?php } ?>
                    </td>
                    <td class="column-columnname "><?php echo $row->count_used; ?></td>
                    <td class="column-columnname "><?php echo $row->created_at; ?></td>
                    <td class="column-columnname "><a data-id="<?php echo $row->ID; ?>"
                                                     data-name="<?php echo $row->tag_name; ?>"
                                                     hrefs="#" class="thickbox"
                                                     style="cursor:pointer;">Edit</a> | <a class="tag-delete"
                                                                                           href="#"
                                                                                           data-id="<?php echo $row->ID; ?>">Delete</a>
                    </td>
                </tr>
                <?php $count++;
            } ?>
            </tbody>
        </table>
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <input id="" class="button action tags-delete" value="Delete All" type="submit">
            </div>
            <div class="tablenav-pages-new" style="float:right;">
                <span class="displaying-num-new" style="float:left;"><?php echo $tcount; ?> items</span>
                <span class="pagination-links-new"
                      style="float:right;"><?php echo paginate_three($reload, $page, $tpages, $adjacents); ?></span>
            </div>
        </div>

    </div>

    <script>
        jQuery(function ($) {
            // tag-delete
            jQuery('.tag-delete').click(function () {
                var tagId = jQuery(this).attr('data-id');
                //alert(tagId);
                if (tagId) {
                    // var r = confirm("Are you sure?");
                    // if (r == true) {
                    jQuery.ajax({
                        type: "post",
                        dataType: "json",
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        data: {action: 'interest_tag_delete', tag_id: tagId},
                        success: function (response) {
                            if (response.status === 1) {
                                window.location.reload();
                            } else {
                                alert(response.message);
                            }
                        }
                    });
                    // }
                }
            });
            jQuery('.tags-delete').click(function () {
                var tagIDs = $(".selected-tags:checkbox:checked").map(function () {
                    return jQuery(this).val();
                }).get();
                if (tagIDs.length) {

                    console.log(tagIDs);
                    jQuery.ajax({
                        type: "post",
                        dataType: "json",
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        data: {action: 'interest_tags_delete', tag_id: tagIDs},
                        success: function (response) {
                            if (response.status === 1) {
                                window.location.reload();
                            } else {
                                alert(response.message);
                            }
                        }
                    });
                }
            });

            jQuery('input[name=edit-tags]').click(function () {
                var tagId = jQuery('input[name=tag_id]').val();
                var tag_name = jQuery('input[name=tag_namec]').val();
                jQuery('#TB_load').show();
                if (tagId) {
                    jQuery.ajax({
                        type: "post",
                        dataType: "json",
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        data: {action: 'interest_tag_update', tag_id: tagId, tag_name: tag_name},
                        success: function (response) {
                            if (response.status === 1) {
                                window.location.reload();
                            } else {
                                alert(response.message);
                            }
                        }
                    });
                }
            });

            jQuery('.thickbox').click(function () {
                // tb_init('my-content-id');
                var tagId = jQuery(this).attr('data-id');
                var tagName = jQuery(this).attr('data-name');

                //alert(tagId);
                if (tagId) {
                    jQuery('#tag-edit input[name=tag_namec]').val(tagName);
                    jQuery('#tag-edit input[name=tag_id]').val(tagId);
                    setTimeout(function() {
                        jQuery('#TB_load').hide();
                    },10);
                    tb_show('Tag Edit', '#TB_inline?&width=300&height=100&inlineId=tag-edit', false);


                }
            });

            jQuery('td.fl-admin-tag-column-auto-hide').click(function(){
               //never be clicked here yet
                $('div.fl-admin-tag-auto-hide-target').hide();
                let node = $(this);
                node.toggleClass('fl-admin-tag-auto-hide-self-on-second-click');
                if (node.hasClass('fl-admin-tag-auto-hide-self-on-second-click')) {
                    node.find('div.fl-admin-tag-auto-hide-target').show();
                }


            });
        }); //end jQuery load function
    </script>
    <?php
}

/**
 * @param $arr
 * @return int
 */
function fl_admin_page_tags_count_tags($arr) {
    if (!is_array($arr)) {return 0;}
    $count = 0;
    for($i = 0; $i< count($arr); $i++) {
        $what = (int)$arr[$i];
        if ($what) {$count++;}
    }

    return $count;
}