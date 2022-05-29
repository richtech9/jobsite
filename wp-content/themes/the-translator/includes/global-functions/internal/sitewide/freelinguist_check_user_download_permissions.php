<?php

/**
 * internal function to check user permissions, returns partial files path if it is ok to use and it exists
 *
 * content files can be seen by the creator of the content or the buyer of the content
 * job files can be seen by the freelancer hired for that job, or the project owner
 * proposal files can be seen by the freelancer who created that proposal or the contest owner
 *
 *
 * @param int $user_id
 * @param int $job_file_id
 * @param int $content_file_id
 * @return string
 */
function check_user_download_permissions($user_id,$job_file_id,$content_file_id) {

    global $wpdb;
    $b_mah_debug = false;

//    will_send_to_error_log('checking permissions',[
//        '$user_id'=>$user_id,
//        '$job_file_id'=>$job_file_id,
//        '$content_file_id' => $content_file_id
//    ]);
    if ($content_file_id) {
        $sql_to_check_content = "
           SELECT f.file_path
            FROM wp_content_files f
              INNER JOIN  wp_linguist_content content ON f.content_id = content.id  AND content.user_id IS NOT NULL 
            WHERE
              f.id = $content_file_id AND
              (
                ( content.purchased_by = $user_id)
                OR
                (content.user_id = $user_id  )
                
              ); ";

        $check_content_res = $wpdb->get_results($sql_to_check_content);

        will_throw_on_wpdb_error($wpdb, 'Checking permissions for private content file download');
        if ($b_mah_debug) {will_send_to_error_log("Content download sql",$wpdb->last_query);}
        if (empty($check_content_res)) {
           return false;
        }
        return $check_content_res[0]->file_path;
    }

        if ($job_file_id) {
        //its a project or contest file
            $project_check_res = $wpdb->get_results(
                "
                SELECT  DISTINCT  f.file_path
                
                FROM wp_files f
                  LEFT JOIN wp_proposals proposal on f.proposal_id = proposal.id
                  LEFT JOIN wp_posts proposal_post ON proposal_post.ID = proposal.post_id
                  LEFT JOIN wp_posts direct_post ON direct_post.ID = f.post_id
                
                  LEFT JOIN wp_fl_job job on f.job_id = job.ID
                  LEFT JOIN wp_posts job_post ON job_post.ID = job.project_id
                  
                  LEFT JOIN wp_postmeta meta ON meta.post_id = f.post_id AND meta.meta_key =  'fl_job_type'
                
                  LEFT JOIN (
                              SELECT f_instructions.id as file_of_instruction_id,any_proposal.by_user,any_proposal.post_id
                              FROM wp_files f_instructions
                                INNER JOIN  wp_proposals any_proposal ON any_proposal.post_id = f_instructions.post_id
                              WHERE f_instructions.id = $job_file_id AND any_proposal.by_user = $user_id
                  ) as any_proposals_by_user ON any_proposals_by_user.file_of_instruction_id = f.id
                
                  LEFT JOIN (
                              SELECT f_instructions.id as file_of_instruction_id,any_job.linguist_id,any_job.project_id
                              FROM wp_files f_instructions
                                INNER JOIN  wp_fl_job any_job ON any_job.project_id = f_instructions.post_id
                              WHERE f_instructions.id = $job_file_id AND any_job.linguist_id = $user_id
                  ) as any_jobs_by_user ON any_jobs_by_user.file_of_instruction_id = f.id
                
                WHERE f.id = $job_file_id AND
                      (
                        (job.linguist_id = $user_id AND job.job_status = 'start')
                        OR
                        (job_post.post_author = $user_id)
                        OR
                        (proposal.by_user = $user_id  )
                        OR
                        ( proposal_post.post_author = $user_id)
                        OR
                        (direct_post.post_author = $user_id)
                        OR
                        (any_proposals_by_user.file_of_instruction_id IS NOT NULL)
                        OR
                        (any_jobs_by_user.file_of_instruction_id IS NOT NULL)
                        OR
                        (meta.meta_value = 'contest' AND proposal.id IS NULL) -- code-notes any public instruction file for the contest 
                        OR
                        (meta.meta_value = 'project' AND job.ID IS NULL) -- code-notes any public instruction file for the project
                      );
                    ;"
            );


            will_throw_on_wpdb_error($wpdb, 'Checking job for private project participation');
            if ($b_mah_debug) {will_send_to_error_log("Job download sql",$wpdb->last_query);}
            if (empty($project_check_res)) {
                return false;
            }
            return $project_check_res[0]->file_path;



        }



       throw new RuntimeException("Neither content or job id specified");





}