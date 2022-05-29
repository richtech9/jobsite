<?php
add_action( 'wp_ajax_freelinguist_proposal_file_delete',  'freelinguist_proposal_file_delete'  );

/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistProposalFileDeleteResponse
 * @property {number} status
 * @property {string} message
 * @property {number} deleted_proposal_id
 * @property {number[]} deleted_file_ids
 * @property {number} error_proposal_id
 * @property {number[]} error_file_ids
 */

class FreelinguistProposalFileDeleteResponse {
    /**
     * @var bool $status
     */
    public $status = true;

    /**
     * @var string $message
     */
    public $message = '';

    /**
     * @var int $deleted_proposal_id
     */
    public $deleted_proposal_id = null;

    /**
     * @var int[] $deleted_file_ids
     */
    public $deleted_file_ids = [];

    /**
     * @var int $error_proposal_id
     */
    public $error_proposal_id = null;

    /**
     * @var int[] $error_file_ids
     */
    public $error_file_ids = [];

    /**
     * @var string[] $error_file_messages
     */
    public $error_file_messages = [];

    public function __construct()
    {
        $this->error_file_ids = $this->deleted_file_ids = $this->error_file_messages = [];
        $this->status = true;
    }
}

/**
 * Expected input is post of
 * int file_id
 * int proposal_id
 *
 *
 * Response is @see FreelinguistProposalFileDeleteResponse  outputted to json, and then the program dies
 *
 * If given a proposal id, then will delete all the files in the proposal, and then delete the proposal itself
 * If a file is given, will delete the file, if the proposal has no more files, then will also delete its parent proposal
 *
 * Its possible for some files in a proposal to have errors while deleting, if that is the case will delete the files it can
 * and give notice about the rest. If a proposal has any of its own files not deleted, then it cannot delete the proposal itself
 *
 * If there is an issue with deleting the proposal, then that reason should be in the message
 *
 * If there is a reason for not being able to remove a file, then the reason will be in the file error message
 *
 */
function freelinguist_proposal_file_delete() {
    /*
       * current-php-code 2020-Oct-25
       * ajax-endpoint  freelinguist_proposal_file_delete
       * input-sanitized: file_id, proposal_id
     */
    global $wpdb;

    $proposal_id = (int)FLInput::get('proposal_id',0);
    $file_id = (int)FLInput::get('file_id',0);
    $user_id = get_current_user_id();

    $ret = new FreelinguistProposalFileDeleteResponse();

    try {

        $proposals_to_delete = [];
        $files_to_delete = [];
        $proposals_to_check = [];

        if ($proposal_id && $file_id) {

            //file id must belong to proposal id must belong to user id
            //add file id to the files to delete array
            //add proposal to the proposals to delete array


            $sql = "SELECT p.id as proposal_id,f.id as file_id
                FROM wp_proposals p 
                INNER JOIN wp_files f ON f.proposal_id = p.id
                WHERE p.by_user = $user_id AND p.id=$proposal_id AND f.id = $file_id";

            $rows = $wpdb->get_results($sql);
            will_throw_on_wpdb_error($wpdb);
            if (empty($rows)) {
                $err = "Proposal, File, User does not match up together";
                $ret->status = $err;
                $ret->error_proposal_id = $proposal_id;
                $ret->error_file_ids[] = $file_id;
                $ret->error_file_messages[] = $err;
                wp_send_json($ret);
                exit;
            }

            $proposals_to_delete[] = $proposal_id;
            $files_to_delete[] = $file_id;

        } elseif ($proposal_id) {
            //proposal id must belong to user id
            //find each file of the proposal
            //add proposal to the proposals to delete array
            //add found files  to the files to delete array

            $sql = "SELECT p.id as proposal_id,f.id as file_id
                FROM wp_proposals p 
                LEFT JOIN wp_files f ON f.proposal_id = p.id
                WHERE p.by_user = $user_id AND p.id=$proposal_id ";

            $rows = $wpdb->get_results($sql);
            will_throw_on_wpdb_error($wpdb);
            if (empty($rows)) {
                $err = "Proposal and User does not match up together";
                $ret->status = $err;
                $ret->error_proposal_id = $proposal_id;
                wp_send_json($ret);
                exit;
            }

            $proposals_to_delete[] = $proposal_id;

            foreach ($rows as $row) {
                $file_id_to_add = (int)$row->file_id;
                if ($file_id_to_add) {
                    $files_to_delete[] = $file_id_to_add;
                }
            }


        } elseif ($file_id) {
            //find proposal for this file
            //proposal must belong to user id
            //add file id to the files to delete array

            $sql = "SELECT p.id as proposal_id,f.id as file_id
                FROM wp_files f  
                INNER JOIN wp_proposals p ON f.proposal_id = p.id
                WHERE p.by_user = $user_id AND  f.id = $file_id";

            $rows = $wpdb->get_results($sql);
            will_throw_on_wpdb_error($wpdb);
            if (empty($rows)) {
                $err = " File, User does not exist inside proposal";
                $ret->status = $err;
                $ret->error_file_ids[] = $file_id;
                $ret->error_file_messages[] = $err;
                wp_send_json($ret);
                exit;
            }

            $files_to_delete[] = $file_id;
            $proposals_to_check[] = $rows[0]->proposal_id;//check to see if need to also remove proposal
        }


        $upload_dir = wp_upload_dir();
        $base_path = $upload_dir['basedir'] . '/';

        foreach ($files_to_delete as $file_id_to_delete) {
            try {
                //do one at a time to find errors
                $sql = "SELECT id,post_id,by_user,file_path,file_name,proposal_id
                   FROM wp_files WHERE id = $file_id_to_delete";
                $rows = $wpdb->get_results($sql);
                will_throw_on_wpdb_error($wpdb);
                if (empty($rows)) {
                    throw new RuntimeException("Cannot find file_id of $file_id_to_delete");
                }

                try {
                    if (!in_array($rows[0]->proposal_id, $proposals_to_check)) {
                        $proposals_to_check[] = $rows[0]->proposal_id;
                    }
                    $full_file_path = $base_path . $rows[0]->file_path;

                    $real_file_path = realpath($full_file_path);

                    if (!$real_file_path) {
                        throw new RuntimeException("File $real_file_path does not exist");
                    }

                    if (!is_readable($real_file_path)) {
                        throw new RuntimeException("File $real_file_path is not readable");
                    }
                    if (!is_writable($real_file_path)) {
                        throw new RuntimeException("File $real_file_path is not writable");
                    }
                    $what = unlink($real_file_path);

                    if (!$what) {
                        throw new RuntimeException("File $real_file_path cannot be deleted");
                    }
                } catch (Exception $ef) {
                    //continue after this to remove from row
                    $ret->error_file_ids[] = $file_id_to_delete;
                    $ret->error_file_messages[] = $ef->getMessage();
                }
                $sql = "DELETE FROM wp_files WHERE id = $file_id_to_delete";
                $what = (int)$wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                if (!$what) {
                    throw new RuntimeException("Could not remove file $file_id_to_delete from db");
                }
                $ret->deleted_file_ids[] = $file_id_to_delete;
                //done deleting file
            } catch (Exception $e) {
                $ret->error_file_ids[] = $file_id_to_delete;
                $ret->error_file_messages[] = $e->getMessage();
                $ret->status = false;
            }
        }

        foreach ($proposals_to_check as $proposal_id_to_check) {
            $sql = "SELECT p.id AS proposal_id, count(f.id) AS number_files
                FROM wp_proposals p
                LEFT JOIN wp_files f ON f.proposal_id = p.id
                WHERE p.id = $proposal_id_to_check
                GROUP BY p.id;";

            $rows = $wpdb->get_results($sql);
            will_throw_on_wpdb_error($wpdb);
            if (empty($rows) || (intval($rows[0]->number_files) === 0)) {
                if (!in_array($proposal_id_to_check, $proposals_to_delete)) {
                    $proposals_to_delete[] = $proposal_id_to_check;
                }
            }
        }

        $counter = 0;
        foreach ($proposals_to_delete as $proposal_id_to_delete) {
            try {
                //error if more than one proposal to delete, for now
                if ($counter > 0) {
                    $ret->message = "Cannot delete more than one proposal at a time";
                    $ret->error_proposal_id = $proposal_id_to_delete;
                    break;
                }
                $counter++;

                $sql = "DELETE FROM wp_proposals where ID = $proposal_id_to_delete";
                $what = (int)$wpdb->query($sql);
                will_throw_on_wpdb_error($wpdb);

                if (!$what) {
                    throw new RuntimeException("Could not remove proposal $proposal_id_to_delete from db");
                }
                $ret->deleted_proposal_id = $proposal_id_to_delete;

            } catch (Exception $e) {
                $ret->message = $e->getMessage();
                $ret->error_proposal_id = $proposal_id_to_delete;
                $ret->status = false;
            }

        }

    } catch (Exception $we) {
        $ret->status = false;
        $ret->message .= $we->getMessage();
    }
    wp_send_json($ret);
    exit; //above exists, but phpstorm gets confused, also clarifies we are existing
}