<?php
/*
 * Plugin Name: RequestEvalutaion Table
 * Description: RequestEvalutaion_Wp_List_Table
 * Plugin URI: http://www.paulund.co,
 * Author: Lakhvidner
 * Author URI: http://www.Lakhvidner.com
 * Version: 1.0
 */
//task-future-work Admin Evaluations the table that is shown in the bottom of admin evaluation is never filled in as nothing shows the dialog to request evaluation and set  request_evaluating .The dialog is hidden in the translators profile

/*
    * current-php-code 2021-Jan-11
    * input-sanitized :
    * current-wp-template:  admin-screen  for evaluations
*/


/**
 * Paulund_Wp_List_Table class will create the page to load the table
 * @uses update_RequestEvaluation_info();  which is only called by a dialog which has been hidden
 */
class AdminPageEvaluations
{
    public $parent_slug = null;
    public $position = null;
    /**
     * Constructor will create the menu item
     * @param string $parent_slug
     * @param int $position
     */
    public function __construct($parent_slug = null,$position = null)
    {
        $this->parent_slug = $parent_slug;
        $this->position = $position;
        add_action('admin_menu', array($this, 'add_menu_RequestEvalutaion_list_table_page'));
    }

    /**
     * Menu item will allow us to load the page to display the table

     */
    public function add_menu_RequestEvalutaion_list_table_page()
    {
        if ($this->parent_slug) {
            add_submenu_page($this->parent_slug,'Request Evaluation List', 'Request Evaluation List',
                'manage_options', 'freelinguist-admin-evaluation', array($this, 'list_table_page'), $this->position);
        } else {
            add_menu_page('Request Evaluation List', 'Request Evaluation List',
                'manage_options', 'freelinguist-admin-evaluation', array($this, 'list_table_page'), 'dashicons-list-view');
        }

    }



    public function updateOrApproveRequestEvalutionData($user_id)
    {
        $description = get_user_meta($user_id, 'description', true);
        update_user_meta($user_id, 'approve_description', $description);

        /********************Education****************************************/
        $total_edu = empty(get_user_meta($user_id, 'approve_education_counter', true)) ? 0 : get_user_meta($user_id, 'approve_education_counter', true);
        for ($i = 0; $i <= $total_edu - 1; $i++) {
            delete_user_meta($user_id, 'approve_year_attended_' . $i, true);
            delete_user_meta($user_id, 'approve_institution_' . $i, true);
            delete_user_meta($user_id, 'approve_degree_' . $i, true);
        }
        delete_user_meta($user_id, 'approve_education_counter', true);

        $total_edu = empty(get_user_meta($user_id, 'education_counter', true)) ? 0 : get_user_meta($user_id, 'education_counter', true);
        for ($i = 0; $i <= $total_edu - 1; $i++) {
            $year_attended = get_user_meta($user_id, 'year_attended_' . $i, true);
            $institution = get_user_meta($user_id, 'institution_' . $i, true);
            $degree = get_user_meta($user_id, 'degree_' . $i, true);

            update_user_meta($user_id, 'approve_year_attended_' . $i, $year_attended);
            update_user_meta($user_id, 'approve_institution_' . $i, $institution);
            update_user_meta($user_id, 'approve_degree_' . $i, $degree);
        }
        update_user_meta($user_id, 'approve_education_counter', $total_edu);
        /********************Education****************************************/


        /*********** Licenses/Certificates/Awards********************************/
        $total_edu = empty(get_user_meta($user_id, 'approve_certification_counter', true)) ? 0 : get_user_meta($user_id, 'approve_certification_counter', true);
        for ($i = 0; $i <= $total_edu - 1; $i++) {
            delete_user_meta($user_id, 'approve_year_recieved_' . $i, true);
            delete_user_meta($user_id, 'approve_recieved_from_' . $i, true);
            delete_user_meta($user_id, 'approve_certificate_' . $i, true);
        }
        delete_user_meta($user_id, 'approve_certification_counter', true);

        $total_edu = empty(get_user_meta($user_id, 'certification_counter', true)) ? 0 : get_user_meta($user_id, 'certification_counter', true);
        for ($i = 0; $i <= $total_edu - 1; $i++) {
            $year_recieved = get_user_meta($user_id, 'year_recieved_' . $i, true);
            $recieved_from = get_user_meta($user_id, 'recieved_from_' . $i, true);
            $certificate = get_user_meta($user_id, 'certificate_' . $i, true);
            update_user_meta($user_id, 'approve_year_recieved_' . $i, $year_recieved);
            update_user_meta($user_id, 'approve_recieved_from_' . $i, $recieved_from);
            update_user_meta($user_id, 'approve_certificate_' . $i, $certificate);
        }
        update_user_meta($user_id, 'approve_certification_counter', $total_edu);
        /*********** Licenses/Certificates/Awards********************************/


        /*********** rELATED eXPERIENCE********************************/
        $total_edu = empty(get_user_meta($user_id, 'approve_related_experience_counter', true)) ? 0 : get_user_meta($user_id, 'approve_related_experience_counter', true);
        for ($i = 0; $i <= $total_edu - 1; $i++) {
            delete_user_meta($user_id, 'approve_year_in_service_' . $i, true);
            delete_user_meta($user_id, 'approve_employer_' . $i, true);
            delete_user_meta($user_id, 'approve_duties_' . $i, true);
        }
        delete_user_meta($user_id, 'approve_related_experience_counter', true);

        $total_edu = empty(get_user_meta($user_id, 'related_experience_counter', true)) ? 0 : get_user_meta($user_id, 'related_experience_counter', true);
        for ($i = 0; $i <= $total_edu - 1; $i++) {
            $year_in_service_ = get_user_meta($user_id, 'year_in_service_' . $i, true);
            $employer_ = get_user_meta($user_id, 'employer_' . $i, true);
            $duties_ = get_user_meta($user_id, 'duties_' . $i, true);
            update_user_meta($user_id, 'approve_year_in_service_' . $i, $year_in_service_);
            update_user_meta($user_id, 'approve_employer_' . $i, $employer_);
            update_user_meta($user_id, 'approve_duties_' . $i, $duties_);
        }
        update_user_meta($user_id, 'approve_related_experience_counter', $total_edu);
        /*********** rELATED eXPERIENCE********************************/


        /*********** Languages********************************/
        $total_edu = empty(get_user_meta($user_id, 'approve_language_counter', true)) ? 0 : get_user_meta($user_id, 'approve_language_counter', true);
        for ($i = 0; $i <= $total_edu - 1; $i++) {
            delete_user_meta($user_id, 'approve_language_' . $i, true);
            delete_user_meta($user_id, 'approve_language_level_' . $i, true);
            delete_user_meta($user_id, 'approve_year_of_experince_' . $i);
            delete_user_meta($user_id, 'approve_areas_expertise_' . $i, true);
        }
        delete_user_meta($user_id, 'approve_language_counter', true);

        $total_edu = empty(get_user_meta($user_id, 'language_counter', true)) ? 0 : get_user_meta($user_id, 'language_counter', true);
        for ($i = 0; $i <= $total_edu - 1; $i++) {
            $approve_language_ = get_user_meta($user_id, 'language_' . $i, true);
            $approve_language_level_ = get_user_meta($user_id, 'language_level_' . $i, true);
            $approve_year_of_experince_ = get_user_meta($user_id, 'year_of_experince_' . $i, true);
            $approve_areas_expertise_ = get_user_meta($user_id, 'areas_expertise_' . $i, true);

            update_user_meta($user_id, 'approve_language_' . $i, $approve_language_);
            update_user_meta($user_id, 'approve_language_level_' . $i, $approve_language_level_);
            update_user_meta($user_id, 'approve_year_of_experince_' . $i, $approve_year_of_experince_);
            update_user_meta($user_id, 'approve_areas_expertise_' . $i, $approve_areas_expertise_);
        }
        update_user_meta($user_id, 'approve_language_counter', $total_edu);
        /***********  Languages********************************/


        /*********** Experience in translation********************************/
        $total_edu = empty(get_user_meta($user_id, 'approve_translation_language_counter', true)) ? 0 : get_user_meta($user_id, 'approve_translation_language_counter', true);
        for ($i = 0; $i <= $total_edu - 1; $i++) {
            delete_user_meta($user_id, 'approve_translation_language_' . $i, true);
            delete_user_meta($user_id, 'approve_translation_language_level_' . $i, true);
            delete_user_meta($user_id, 'approve_translation_year_of_experince_' . $i, true);
            delete_user_meta($user_id, 'approve_translation_areas_expertise_' . $i, true);
        }
        delete_user_meta($user_id, 'approve_translation_language_counter', true);

        $total_edu = empty(get_user_meta($user_id, 'translation_language_counter', true)) ? 0 : get_user_meta($user_id, 'translation_language_counter', true);
        for ($i = 0; $i <= $total_edu - 1; $i++) {
            $approve_translation_language_ = get_user_meta($user_id, 'translation_language_' . $i, true);
            $approve_translation_language_level_ = get_user_meta($user_id, 'translation_language_level_' . $i, true);
            $approve_translation_year_of_experince_ = get_user_meta($user_id, 'translation_year_of_experince_' . $i, true);
            $approve_translation_areas_expertise_ = get_user_meta($user_id, 'translation_areas_expertise_' . $i, true);

            update_user_meta($user_id, 'approve_translation_language_' . $i, $approve_translation_language_);
            update_user_meta($user_id, 'approve_translation_language_level_' . $i, $approve_translation_language_level_);
            update_user_meta($user_id, 'approve_translation_year_of_experince_' . $i, $approve_translation_year_of_experince_);
            update_user_meta($user_id, 'approve_translation_areas_expertise_' . $i, $approve_translation_areas_expertise_);
        }
        update_user_meta($user_id, 'approve_translation_language_counter', $total_edu);
        /***********  Experience in translation********************************/


        /*********** Experience in editing/proofreading********************************/
        $total_edu = empty(get_user_meta($user_id, 'approve_editing_language_counter', true)) ? 0 : get_user_meta($user_id, 'approve_editing_language_counter', true);
        for ($i = 0; $i <= $total_edu - 1; $i++) {
            delete_user_meta($user_id, 'approve_editing_language_' . $i, true);
            delete_user_meta($user_id, 'approve_editing_language_level_' . $i, true);
            delete_user_meta($user_id, 'approve_editing_year_of_experince_' . $i, true);
            delete_user_meta($user_id, 'approve_editing_areas_expertise_' . $i, true);
        }
        delete_user_meta($user_id, 'approve_editing_language_counter', true);

        $total_edu = empty(get_user_meta($user_id, 'editing_language_counter', true)) ? 0 : get_user_meta($user_id, 'editing_language_counter', true);
        for ($i = 0; $i <= $total_edu - 1; $i++) {
            $approve_editing_language_ = get_user_meta($user_id, 'editing_language_' . $i, true);
            $approve_editing_language_level_ = get_user_meta($user_id, 'editing_language_level_' . $i, true);
            $approve_editing_year_of_experince_ = get_user_meta($user_id, 'editing_year_of_experince_' . $i, true);
            $approve_editing_areas_expertise_ = get_user_meta($user_id, 'editing_areas_expertise_' . $i, true);

            update_user_meta($user_id, 'approve_editing_language_' . $i, $approve_editing_language_);
            update_user_meta($user_id, 'approve_editing_language_level_' . $i, $approve_editing_language_level_);
            update_user_meta($user_id, 'approve_editing_year_of_experince_' . $i, $approve_editing_year_of_experince_);
            update_user_meta($user_id, 'approve_editing_areas_expertise_' . $i, $approve_editing_areas_expertise_);
        }
        update_user_meta($user_id, 'approve_editing_language_counter', $total_edu);
        /***********  Experience in editing/proofreading********************************/


        /*********** Experience in writing********************************/
        $total_edu = empty(get_user_meta($user_id, 'approve_writing_language_counter', true)) ? 0 : get_user_meta($user_id, 'approve_writing_language_counter', true);
        for ($i = 0; $i <= $total_edu - 1; $i++) {
            delete_user_meta($user_id, 'approve_writing_language_' . $i, true);
            delete_user_meta($user_id, 'approve_writing_language_level_' . $i, true);
            delete_user_meta($user_id, 'approve_writing_year_of_experince_' . $i, true);
            delete_user_meta($user_id, 'approve_writing_areas_expertise_' . $i, true);
        }
        delete_user_meta($user_id, 'approve_writing_language_counter', true);

        $total_edu = empty(get_user_meta($user_id, 'writing_language_counter', true)) ? 0 : get_user_meta($user_id, 'writing_language_counter', true);
        for ($i = 0; $i <= $total_edu - 1; $i++) {
            $approve_writing_language_ = get_user_meta($user_id, 'writing_language_' . $i, true);
            $approve_writing_language_level_ = get_user_meta($user_id, 'writing_language_level_' . $i, true);
            $approve_writing_year_of_experince_ = get_user_meta($user_id, 'writing_year_of_experince_' . $i, true);
            $approve_writing_areas_expertise_ = get_user_meta($user_id, 'writing_areas_expertise_' . $i, true);

            update_user_meta($user_id, 'approve_writing_language_' . $i, $approve_writing_language_);
            update_user_meta($user_id, 'approve_writing_language_level_' . $i, $approve_writing_language_level_);
            update_user_meta($user_id, 'approve_writing_year_of_experince_' . $i, $approve_writing_year_of_experince_);
            update_user_meta($user_id, 'approve_writing_areas_expertise_' . $i, $approve_writing_areas_expertise_);
        }
        update_user_meta($user_id, 'approve_writing_language_counter', $total_edu);
        /***********  Experience in writing********************************/




    }




    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page()
    {

        if (isset($_REQUEST['user_detail_id'])) {
            $user_detail = get_userdata($_REQUEST['user_detail_id']);
            $user_id = $_REQUEST['user_detail_id'];
            if (!empty($user_detail)) { ?>
                <section class="account-headline large-text">
                    <section class="container">
                        <?php
                        $author_id = $user_id;
                        $user_data = get_user_by('id', $_REQUEST['user_detail_id']);
                        $user_role = $user_data->roles[0];
                        ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="account-info">
                                    <div class="thumnil">
                                        <div class="fig">
                                            <?php
                                            //code-notes [image-sizing]  using hz_get_profile_thumb for sized image
                                            $avatar = hz_get_profile_thumb($author_id, FreelinguistSizeImages::SMALL);
                                            echo '<img width="120px"  style="" src="' . $avatar . '">';
                                            ?>
                                        </div>
                                    </div>
                                    <div class="acc-info-inner">
                                        <div class="mail-info">
                                            <div class="personal-info large-text">
                                                <div class="name larger-text"><?php echo $user_detail->display_name; ?></div>
                                                <div class="rating">
                                                    <?php if ($user_role == 'translator') {
                                                        echo translater_rating($author_id, 17, 'translator');
                                                    } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <section>
                        <!-- START: Attachments -->
                        <div class="attached-file row">
                            <div class="attachhment-box">
                                <h4>Uploaded material and certificates</h4><br>
                                <hr>
                                <?php
                                global $wpdb;
                                //code-unused there are not any such types set right now, but could in the future in new code
                                $resumes_files = $wpdb->get_results("SELECT * FROM wp_files WHERE by_user = $author_id AND type=".FLWPFileHelper::TYPE_USER_ASSOCIATED);
                                for ($i = 0; $i < count($resumes_files); $i++) {
                                    $file_id = $resumes_files[$i]->id;
                                    ?>
                                    <div class="box resume-box">
                                        <a href="<?php echo get_site_url() . '?action=download_job_file&attach_id=' . $resumes_files[$i]->id; ?>&lang=en">
                                            <div class="box-inner">
                                                <a class="glyphicon glyphicon-remove small-text"
                                                   onclick="return delete_resume_attachment(this,<?php echo $file_id; ?>)"
                                                   id="delete_resume_attachment" href="#"></a>
                                                <img src="<?= bloginfo('template_url').'/images/attachdoc.png'; ?>">
                                            </div>
                                            <a href="<?php echo get_site_url() . '?action=download_job_file&attach_id=' . $resumes_files[$i]->id; ?>&lang=en">
                                                <p class="breakall"><?php echo $resumes_files[$i]->file_name; ?></p>
                                            </a>
                                        </a>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <hr>
                        </div>
                        <table class="form-table">
                            <tbody>
                            <?php
                            $user_id = $author_id;
                            $user_translation = empty(get_the_author_meta('user_translation_technical_level', $user_id)) ? 'Not exist' : get_the_author_meta('user_translation_technical_level', $user_id);
                            $user_editing = empty(get_the_author_meta('user_editing_technical_level', $user_id)) ? 'Not exist' : get_the_author_meta('user_editing_technical_level', $user_id);
                            $user_writing = empty(get_the_author_meta('user_writing_technical_level', $user_id)) ? 'Not exist' : get_the_author_meta('user_writing_technical_level', $user_id);
                            $total_user_balance = empty(get_the_author_meta('total_user_balance', $user_id)) ? '0.00' : get_the_author_meta('total_user_balance', $user_id);
                            $last_payment_recieved = empty(get_the_author_meta('last_payment_recieved', $user_id)) ? '0.00' : get_the_author_meta('last_payment_recieved', $user_id);
                            $bankName = empty(get_the_author_meta('bankName', $user_id)) ? 'Not exist' : get_the_author_meta('bankName', $user_id);
                            $accountHolder = empty(get_the_author_meta('accountHolder', $user_id)) ? 'Not exist' : get_the_author_meta('accountHolder', $user_id);
                            $accountNumber = empty(get_the_author_meta('accountNumber', $user_id)) ? 'Not exist' : get_the_author_meta('accountNumber', $user_id);
                            $accountType = empty(get_the_author_meta('accountType', $user_id)) ? 'Not exist' : get_the_author_meta('accountType', $user_id);
                            $user_phone = empty(get_the_author_meta('user_phone', $user_id)) ? 'Not exist' : get_the_author_meta('user_phone', $user_id);
                            $user_address = empty(get_the_author_meta('user_address', $user_id)) ? 'Not exist' : get_the_author_meta('user_address', $user_id);
                            $user_description = empty(get_the_author_meta('user_description', $user_id)) ? 'Not exist' : get_the_author_meta('user_description', $user_id);
                            $user_residence_country = empty(get_user_meta($author_id, 'user_residence_country', true)) ? 'Not Exist' : get_user_meta($author_id, 'user_residence_country', true);
                            if (!empty($user_residence_country)) {
                                $user_residence_country = get_country_by_index($user_residence_country);
                            }
                            ?>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">Display Name</th>
                                <td>
                                    <?php echo $user_detail->display_name; ?>
                                </td>
                                <th scope="row">Username</th>
                                <td>
                                    <?php echo $user_detail->user_login; ?>
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">User Email</th>
                                <td>
                                    <?php echo $user_detail->user_email; ?>
                                </td>
                                <th scope="row"></th>
                                <td>

                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">
                                    Current levels
                                </th>
                                <td colspan="3">
                                    <?php echo '<b>Translation Technical Level:</b> ' . $user_translation . '&nbsp;&nbsp;&nbsp;&nbsp;'; ?>
                                    <?php echo '<b>Editing Technical Level:</b> ' . $user_editing . '&nbsp;&nbsp;&nbsp;&nbsp;'; ?>
                                    <?php echo '<b>Writing Technical Level:</b> ' . $user_writing . '&nbsp;&nbsp;&nbsp;&nbsp;'; ?>
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">Last Payment received</th>
                                <td>
                                    $ <?php echo amount_format($last_payment_recieved); ?>
                                </td>
                                <th scope="row">User Total Amount</th>
                                <td>
                                    $ <?php echo amount_format($total_user_balance); ?>
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">User phone</th>
                                <td>
                                    <?php echo $user_phone; ?>
                                </td>
                                <th scope="row">User address</th>
                                <td>
                                    <?php echo $user_address; ?>
                                </td>
                            </tr>

                            <tr class="user-rich-editing-wrap">
                                <th scope="row">User residence country</th>
                                <td>
                                    <?php echo $user_residence_country; ?>
                                </td>
                                <th scope="row"></th>
                                <td>
                                    Demo Files were trimmed out of code before I got here - Will
                                </td>
                            </tr>

                            <tr class="user-rich-editing-wrap">
                                <th scope="row">Bank Name</th>
                                <td>
                                    <?php echo $bankName; ?>
                                </td>
                                <th scope="row">Account Holder</th>
                                <td>
                                    <?php echo $accountHolder; ?>
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">accountNumber</th>
                                <td>
                                    <?php echo $accountNumber; ?>
                                </td>
                                <th scope="row">accountType</th>
                                <td>
                                    <?php echo $accountType; ?>
                                </td>
                            </tr>
                            <tr class="user-rich-editing-wrap">
                                <th scope="row">User description</th>
                                <td colspan="2">
                                    <?php echo $user_description; ?>
                                </td>
                            </tr>

                            </tbody>
                        </table>
                    </section>
                </section>

                <section class="middle-content mid-account">
                    <div class="container">
                        <!-- START: Education related area-->
                        <div class="accdetal-table">
                            <h4>Education</h4>
                            <table class="data-table education-data">
                                <thead>
                                <tr>
                                    <th>Years attended</th>
                                    <th>Institution</th>
                                    <th>Degree</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $total_edu = empty(get_user_meta($author_id, 'education_counter', true)) ? 0 : get_user_meta($author_id, 'education_counter', true);
                                for ($i = 0; $i <= $total_edu - 1; $i++) {
                                    $year_attended = get_user_meta($author_id, 'year_attended_' . $i, true);
                                    $institution = get_user_meta($author_id, 'institution_' . $i, true);
                                    $degree = get_user_meta($author_id, 'degree_' . $i, true);
                                    if (!empty($institution) || !empty($year_attended) || !empty($degree)) {
                                        ?>
                                        <tr>
                                            <td><?php echo $year_attended; ?></td>
                                            <td><?php echo $institution; ?></td>
                                            <td><?php echo $degree; ?></td>
                                        </tr>
                                        <?php
                                    }
                                } ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- END: Education related area-->

                        <!-- START: Licences/certificates/award related area-->
                        <div class="accdetal-table">
                            <h4>Licenses/Certificates/Awards/</h4>
                            <table class="data-table license-data">
                                <thead>
                                <tr>
                                    <th>Year received</th>
                                    <th>Received From</th>
                                    <th>License/Certificate/Awards/…</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $total_edu = empty(get_user_meta($author_id, 'certification_counter', true)) ? 0 : get_user_meta($author_id, 'certification_counter', true);
                                for ($i = 0; $i <= $total_edu - 1; $i++) {
                                    $year_recieved_ = get_user_meta($author_id, 'year_recieved_' . $i, true);
                                    $recieved_from_ = get_user_meta($author_id, 'recieved_from_' . $i, true);
                                    $certificate = get_user_meta($author_id, 'certificate_' . $i, true);
                                    if (!empty($year_recieved_) || !empty($recieved_from_) || !empty($certificate)) { ?>
                                        <tr>
                                            <td><?php echo $year_recieved_; ?></td>
                                            <td><?php echo $recieved_from_; ?></td>
                                            <td><?php echo $certificate; ?></td>
                                        </tr>
                                        <?php
                                    }
                                } ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- END: Licences/certificates/award related area-->

                        <!-- START:Related work experinces-->
                        <div class="accdetal-table">
                            <h4>Related work experiences</h4>
                            <table class="data-table experience-data">
                                <thead>
                                <tr>
                                    <th>Year in service</th>
                                    <th>Employer</th>
                                    <th>Duties</th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php
                                $total_edu = empty(get_user_meta($author_id, 'related_experience_counter', true)) ? 0 : get_user_meta($author_id, 'related_experience_counter', true);
                                for ($i = 0; $i <= $total_edu - 1; $i++) {
                                    $year_in_service = get_user_meta($author_id, 'year_in_service_' . $i, true);
                                    $employer = get_user_meta($author_id, 'employer_' . $i, true);
                                    $duties = get_user_meta($author_id, 'duties_' . $i, true);
                                    if (!empty($year_in_service) || !empty($employer) || !empty($duties)) {
                                        ?>
                                        <tr>
                                            <td><?php echo $year_in_service; ?></td>
                                            <td><?php echo $employer; ?></td>
                                            <td><?php echo $duties; ?></td>
                                        </tr>
                                    <?php }
                                }
                                ?>

                                </tbody>
                            </table>
                        </div>
                        <!-- END:Related work experinces-->


                        <!-- START:Languages Related area-->
                        <div class="accdetal-table onehalf">
                            <h4>Languages</h4>
                            <?php $total_lang = empty(get_user_meta($author_id, 'language_counter', true)) ? 0 : get_user_meta($author_id, 'language_counter', true);
                            for ($i = 0; $i < $total_lang; $i++) {
                                $language = get_user_meta($author_id, 'language_' . $i, true);
                                $language_level = get_user_meta($author_id, 'language_level_' . $i, true);
                                $year_of_experince = get_user_meta($author_id, 'year_of_experince_' . $i, true);
                                $areas_expertise = get_user_meta($author_id, 'areas_expertise_' . $i, true);
                                if (!empty($language_level)) {
                                    ?>
                                    <div class="lang enhanced-text"><label>Language:</label><?php echo $language; ?>
                                    </div>
                                    <ul class="list enhanced-text">
                                        <li><label>Level</label><?php echo $language_level; ?></li>
                                        <li><label>Years of experience</label><?php echo $year_of_experince; ?></li>
                                        <li><label>Areas/expertise</label><?php echo $areas_expertise; ?></li>
                                    </ul>
                                <?php }
                            } ?>
                        </div>
                        <!-- END:Languages Related area-->

                        <!-- START:Experience in translation area-->
                        <div class="accdetal-table onehalf last">
                            <h4>Experience in translation </h4>
                            <?php
                            $total_lang_trans = empty(get_user_meta($author_id, 'translation_language_counter', true)) ? 0 : get_user_meta($author_id, 'translation_language_counter', true);
                            for ($i = 0; $i < $total_lang_trans; $i++) {
                                $language = get_user_meta($author_id, 'translation_language_' . $i, true);
                                $language_level = get_user_meta($author_id, 'translation_language_level_' . $i, true);
                                $year_of_experince = get_user_meta($author_id, 'translation_year_of_experince_' . $i, true);
                                $areas_expertise = get_user_meta($author_id, 'translation_areas_expertise_' . $i, true);
                                if (!empty($language_level)) {
                                    ?>
                                    <div class="lang enhanced-text"><label>Language:</label><?php echo $language; ?>
                                    </div>
                                    <ul class="list enhanced-text">
                                        <li><label>Level</label><?php echo $language_level; ?></li>
                                        <li><label>Years of experience</label><?php echo $year_of_experince; ?></li>
                                        <li><label>Areas/expertise</label><?php echo $areas_expertise; ?></li>
                                    </ul>
                                <?php }
                            } ?>
                        </div>
                        <!-- END:Experience in translation area-->

                        <!-- START:Experience in editing/proofreading area-->
                        <div class="accdetal-table onehalf">
                            <h4>Experience in editing/proofreading </h4>
                            <?php
                            $total_lang_edit = empty(get_user_meta($author_id, 'editing_language_counter', true)) ? 0 : get_user_meta($author_id, 'language_counter', true);
                            for ($i = 0; $i < $total_lang_edit; $i++) {
                                $language = get_user_meta($author_id, 'editing_language_' . $i, true);
                                $language_level = get_user_meta($author_id, 'editing_language_level_' . $i, true);
                                $year_of_experince = get_user_meta($author_id, 'editing_year_of_experince_' . $i, true);
                                $areas_expertise = get_user_meta($author_id, 'editing_areas_expertise_' . $i, true);
                                if (!empty($language_level)) {
                                    ?>
                                    <div class="lang enhanced-text"><label>Language:</label><?php echo $language; ?>
                                    </div>
                                    <ul class="list enhanced-text">
                                        <li><label>Level</label><?php echo $language_level; ?></li>
                                        <li><label>Years of experience</label><?php echo $year_of_experince; ?></li>
                                        <li><label>Areas/expertise</label><?php echo $areas_expertise; ?></li>
                                    </ul>
                                <?php }
                            } ?>
                        </div>
                        <!-- END:Experience in editing/proofreading area-->

                        <!-- START:Experience in writing area-->
                        <div class="accdetal-table onehalf last">
                            <h4>Experience in writing</h4>
                            <?php
                            $total_lang_writing = empty(get_user_meta($author_id, 'writing_language_counter', true)) ? 0 : get_user_meta($author_id, 'writing_language_counter', true);
                            for ($i = 0; $i < $total_lang_writing; $i++) {
                                $language = get_user_meta($author_id, 'writing_language_' . $i, true);
                                $language_level = get_user_meta($author_id, 'writing_language_level_' . $i, true);
                                $year_of_experince = get_user_meta($author_id, 'writing_year_of_experince_' . $i, true);
                                $areas_expertise = get_user_meta($author_id, 'writing_areas_expertise_' . $i, true);
                                if (!empty($language_level)) {
                                    ?>
                                    <div class="lang enhanced-text"><label>Language:</label><?php echo $language; ?>
                                    </div>
                                    <ul class="list enhanced-text">
                                        <li><label>Level</label><?php echo $language_level; ?></li>
                                        <li><label>Years of experience</label><?php echo $year_of_experince; ?></li>
                                        <li><label>Areas/expertise</label><?php echo $areas_expertise; ?></li>
                                    </ul>
                                    <?php
                                }
                            } ?>
                        </div>
                        <!-- END:Experience in writing area-->

                        <!-- START: Customer reviews -->
                        <div class="customer-review">
                            <?php
                            $feedback_exist = $wpdb->get_var("SELECT count(*) FROM wp_comments WHERE user_id = $user_id and comment_type ='feedback'  and comment_approved =1");
                            ?>
                            <h4>Customer reviews</h4>
                            <table class="data-table">
                                <thead>
                                <tr>
                                    <th class="enhanced-text" style="width: 165px;">Rating</th>
                                    <th class="enhanced-text">Comments</th>
                                    <th class="enhanced-text">Customer</th>
                                    <th class="enhanced-text">Job Id</th>
                                    <th class="enhanced-text">Date</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if ($feedback_exist >= 1) {
                                    $feedback_is = $wpdb->get_results("SELECT * FROM wp_comments WHERE user_id = $user_id and comment_type ='feedback'  and comment_approved =1");
                                    for ($i = 0; $i < count($feedback_is); $i++) {
                                        ?>
                                        <tr>
                                            <td>
                                                <?php
                                                $feedbak_rating = get_comment_meta($feedback_is[$i]->comment_ID, 'feedback_rating', true);
                                                echo job_rating($feedbak_rating);
                                                ?>
                                            </td>
                                            <td><?php echo $feedback_is[$i]->comment_content; ?></td>
                                            <td>
                                                <?php
                                                $post_data = get_post($feedback_is[$i]->comment_post_ID);
                                                $post_author = get_userdata($post_data->post_author);
                                                echo $post_author->user_email;
                                                ?>
                                            </td>
                                            <td><?php echo get_post_meta($feedback_is[$i]->comment_post_ID, 'modified_id', true); ?></td>
                                            <td><?php echo date_formatted($feedback_is[$i]->comment_date); ?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="5">No record exist</td></tr>';
                                } ?>

                                </tbody>
                            </table>
                        </div>
                        <!-- END: Customer reviews -->

                    </div>
                </section>
                <?php
            } else {
                echo 'Data not exist';
            }
        } else {


            ?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <?php
                $user_detail = isset($_REQUEST['user_id']) ? get_userdata($_REQUEST['user_id']) : '';
                if (isset($_REQUEST['user_id']) && !empty($user_detail)) { ?>
                    <?php
                    $user_id = $_REQUEST['user_id'];
                    if (isset($_REQUEST['request_change'])) {
                        $variables = array(
                            'request_change' => $_REQUEST['request_change_content']
                        );
                        emailTemplateForUser($user_detail->user_email, REQUEST_CHANGE, $variables);
                        echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
	                    <p><strong>Sent.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

                    }
                    if (isset($_REQUEST['update_request_evalution'])) {
                        $user_translation_technical_level = $_REQUEST['user_translation_technical_level'];
                        $user_editing_technical_level = $_REQUEST['user_editing_technical_level'];
                        $user_writing_technical_level = $_REQUEST['user_writing_technical_level'];
                        if (!empty($user_translation_technical_level) && !empty($user_editing_technical_level) && !empty($user_writing_technical_level)) {
                            update_user_meta($user_id, 'last_evalution_time', current_time('mysql'));
                            update_user_meta($user_id, 'user_translation_technical_level', $user_translation_technical_level);
                            update_user_meta($user_id, 'user_editing_technical_level', $user_editing_technical_level);
                            update_user_meta($user_id, 'user_writing_technical_level', $user_writing_technical_level);
                            update_user_meta($user_id, 'last_evaluated_by', get_current_user_id());
                            delete_user_meta($user_id, 'request_evaluating');

                            $translation_per_word_earning = get_option($user_translation_technical_level);
                            $editing_per_word_earning = get_option($user_editing_technical_level);
                            $writing_per_word_earning = get_option($user_writing_technical_level);

                            /********************percentage**************************/
                            $per_user_translation_technical_level = explode('_', $user_translation_technical_level);
                            $per_val_user_translation_technical_level = 'technical_level_percentage_' . $per_user_translation_technical_level[count($per_user_translation_technical_level) - 1];
                            $per_price_user_translation_technical_level = get_option($per_val_user_translation_technical_level);

                            $per_per_user_editing_technical_level = explode('_', $user_editing_technical_level);
                            $per_val_per_user_editing_technical_level = 'technical_level_percentage_' . $per_per_user_editing_technical_level[count($per_per_user_editing_technical_level) - 1];
                            $per_price_user_editing_technical_level = get_option($per_val_per_user_editing_technical_level);

                            $per_user_writing_technical_level = explode('_', $user_writing_technical_level);
                            $per_val_per_user_writing_technical_level = 'technical_level_percentage_' . $per_user_writing_technical_level[count($per_user_writing_technical_level) - 1];
                            $per_price_per_user_writing_technical_level = get_option($per_val_per_user_writing_technical_level);

                            $user_translation_technical_level = 'Level ' . intval(preg_replace('/[^0-9]+/', '', get_user_meta($user_id, 'user_translation_technical_level', true)), 10);
                            $user_editing_technical_level = 'Level ' . intval(preg_replace('/[^0-9]+/', '', get_user_meta($user_id, 'user_editing_technical_level', true)), 10);
                            $user_writing_technical_level = 'Level ' . intval(preg_replace('/[^0-9]+/', '', get_user_meta($user_id, 'user_writing_technical_level', true)), 10);


                            $variables = array(
                                'translation_level' => $user_translation_technical_level,
                                'translation_per_word_earning' => $translation_per_word_earning,
                                'translation_bonus_tip_percentage' => $per_price_user_translation_technical_level . ' ',
                                'editing_level' => $user_editing_technical_level,
                                'editing_per_word_earning' => $editing_per_word_earning,
                                'editing_bonus_tip_percentage' => $per_price_user_editing_technical_level . ' ',
                                'writing_level' => $user_writing_technical_level,
                                'writing_per_word_earning' => $writing_per_word_earning,
                                'writing_bonus_tip_percentage' => $per_price_per_user_writing_technical_level . ' '
                            );

                            /***************************** update public profile **********************************/
                            $this->updateOrApproveRequestEvalutionData($user_id);
                            /***************************** update public profile **********************************/

                            //echo "<pre>"; print_R($variables); exit;
                            emailTemplateForUser($user_detail->user_email, REQUEST_EVALUTION_ACCEPTED_TEMPLATE, $variables);
                            echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
	                    <p><strong>Settings saved.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

                        } else {
                            echo '  <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated">
	                    <p><strong style="color:red">All the fields are required.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                        }
                    }
                    ?>
                    <div class="wrap stuffbox">
                        <div class="inside">
                            <span class="bold-and-blocking larger-text">Edit request evalution</span>
                            <hr>
                            <form name="send_message_f" method="post" id="send_message_f"
                                  action="<?php echo admin_url() . 'admin.php?page=freelinguist-admin-evaluation&user_id=' . $user_id; ?>&lang=en">
                                <table class="form-table">
                                    <tbody>
                                    <?php
                                    $date = empty(get_user_meta($_REQUEST['user_id'], 'request_evaluation_date', true)) ? 'Not exist' : get_user_meta($_REQUEST['user_id'], 'request_evaluation_date', true);
                                    $evaluation_description = empty(get_user_meta($_REQUEST['user_id'], 'request_evaluation_description', true)) ? 'Not exist' : get_user_meta($_REQUEST['user_id'], 'request_evaluation_description', true);
                                    $user_translation = empty(get_the_author_meta('user_translation_technical_level', $user_id)) ? 'Not exist' : get_the_author_meta('user_translation_technical_level', $user_id);
                                    $user_editing = empty(get_the_author_meta('user_editing_technical_level', $user_id)) ? 'Not exist' : get_the_author_meta('user_editing_technical_level', $user_id);
                                    $user_writing = empty(get_the_author_meta('user_writing_technical_level', $user_id)) ? 'Not exist' : get_the_author_meta('user_writing_technical_level', $user_id);

                                    ?>
                                    <tr class="user-rich-editing-wrap">
                                        <th scope="row">Display Name</th>
                                        <td>
                                            <?php echo $user_detail->display_name; ?>
                                        </td>
                                        <th scope="row">Username</th>
                                        <td>
                                            <?php echo $user_detail->user_login; ?>
                                        </td>
                                    </tr>
                                    <tr class="user-rich-editing-wrap">
                                        <th scope="row">User Email</th>
                                        <td>
                                            <?php echo $user_detail->user_email; ?>
                                        </td>
                                        <th scope="row">Request Evaluation date</th>
                                        <td>
                                            <?php echo $date; ?>
                                        </td>
                                    </tr>
                                    <tr class="user-rich-editing-wrap">
                                        <th scope="row">
                                            Current levels
                                        </th>
                                        <td colspan="3">
                                            <?php echo '<b>Translation Technical Level:</b> ' . $user_translation . '&nbsp;&nbsp;&nbsp;&nbsp;'; ?>
                                            <?php echo '<b>Editing Technical Level:</b> ' . $user_editing . '&nbsp;&nbsp;&nbsp;&nbsp;'; ?>
                                            <?php echo '<b>Writing Technical Level:</b> ' . $user_writing . '&nbsp;&nbsp;&nbsp;&nbsp;'; ?>
                                        </td>
                                    </tr>

                                    <tr class="user-rich-editing-wrap">
                                        <th scope="row">
                                            Request Evaluation Description
                                        </th>
                                        <td colspan="3">
                                            <?php echo $evaluation_description; ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th><label for="Translation">Translation</label></th>
                                        <td>
                                            <select name="user_translation_technical_level"
                                                    id="user_translation_technical_level"
                                                    title="User Translation Technical LEvel"
                                            >
                                                <option value="">--Select Level--</option>
                                                <?php
                                                for ($i = 1; $i <= 10; $i++) {
                                                    $value = 'T' . $i;
                                                    $option_value = 'technical_level_T' . $i;
                                                    $selected = (get_the_author_meta('user_translation_technical_level', $user_id) == $option_value) ? 'selected' : '';
                                                    echo '<option ' . $selected . ' value=' . $option_value . '>' . $value . '</option>';
                                                }
                                                for ($i = 1; $i <= 10; $i++) {
                                                    $value = 'TL' . $i;
                                                    $option_value = 'technical_level_TL' . $i;
                                                    $selected = (get_the_author_meta('user_translation_technical_level', $user_id) == $option_value) ? 'selected' : '';
                                                    echo '<option ' . $selected . ' value=' . $option_value . '>' . $value . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <span class="description">Assign Translation level.</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="Editing">Editing & proofreading</label></th>
                                        <td>
                                            <select name="user_editing_technical_level" title="User Editing Technical Level"
                                                    id="user_editing_technical_level">
                                                <option value="">--Select Level--</option>
                                                <?php
                                                for ($i = 1; $i <= 10; $i++) {
                                                    $value = 'E' . $i;
                                                    $option_value = 'technical_level_E' . $i;
                                                    $selected = (get_the_author_meta('user_editing_technical_level', $user_id) == $option_value) ? 'selected' : ' ';
                                                    echo '<option ' . $selected . ' value=' . $option_value . '>' . $value . '</option>';
                                                }
                                                for ($i = 1; $i <= 10; $i++) {
                                                    $value = 'EL' . $i;
                                                    $option_value = 'technical_level_EL' . $i;
                                                    $selected = (get_the_author_meta('user_editing_technical_level', $user_id) == $option_value) ? 'selected' : '';
                                                    echo '<option ' . $selected . ' value=' . $option_value . '>' . $value . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <span class="description">Assign Editing level</span>
                                    </tr>
                                    <tr>
                                        <th><label for="Writing">Writing</label></th>
                                        <td>
                                            <select name="user_writing_technical_level" title="User Writing Technical Level"
                                                    id="user_writing_technical_level">
                                                <option value="">--Select Level--</option>
                                                <?php
                                                for ($i = 1; $i <= 10; $i++) {
                                                    $value = 'W' . $i;
                                                    $option_value = 'technical_level_W' . $i;
                                                    $selected = (get_the_author_meta('user_writing_technical_level', $user_id) == $option_value) ? 'selected' : '';
                                                    echo '<option ' . $selected . ' value=' . $option_value . '>' . $value . '</option>';
                                                }
                                                for ($i = 1; $i <= 10; $i++) {
                                                    $value = 'WL' . $i;
                                                    $option_value = 'technical_level_WL' . $i;
                                                    $selected = (get_the_author_meta('user_writing_technical_level', $user_id) == $option_value) ? 'selected' : '';
                                                    echo '<option ' . $selected . ' value=' . $option_value . '>' . $value . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <span class="description">Assign Writing level.</span>
                                    </tr>
                                    <?php if (!empty(get_user_meta($user_id, 'last_evaluated_by', true))) { ?>
                                        <tr>
                                            <th>
                                                <?php $user_d = get_userdata(get_user_meta($user_id, 'last_evaluated_by', true)); ?>
                                                <?php echo 'Last evaluated by '; ?>
                                            </th>
                                            <td>
                                                <?php echo $user_d->user_email; ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php if (!empty(get_user_meta($user_id, 'last_evalution_time', true))) { ?>
                                        <tr>
                                            <th>
                                                <?php echo 'Last evalution time '; ?>
                                            </th>
                                            <td>
                                                <?php echo get_user_meta($user_id, 'last_evalution_time', true); ?>
                                            </td>
                                        </tr>
                                    <?php } ?>

                                    <tr class="user-rich-editing-wrap">
                                        <th scope="row">
                                            <input type="submit" value="Update" name="update_request_evalution"
                                                   class="button button-primary">
                                            <br>
                                        </th>
                                        <td>
                                            <b><?php //echo !empty(get_user_meta($user_id,'last_evalution_time',true)) ? 'Last evalution time '.get_user_meta($user_id,'last_evalution_time',true) : '';
                                                ?></b>
                                            <?php $viewProfile = admin_url() . 'admin.php?page=freelinguist-admin-evaluation&user_detail_id=' . $user_id; ?>
                                            <a style="float:right" href="<?php echo $viewProfile; ?>"
                                               name="view_linguist_profile" class="button button-primary">Linguist
                                                Profile</a></td>
                                    </tr>
                                    </tbody>
                                </table>

                            </form>

                        </div>
                    </div>
                <hr>
                    <div class="wrap stuffbox">
                        <div class="inside">
                            <h3>Send an email to user</h3>
                            <form name="send_email_f" method="post" id="send_email_f"
                                  action="<?php echo admin_url() . 'admin.php?page=freelinguist-admin-evaluation&user_id=' . $user_id; ?>&lang=en">
                                <br>
                                <label>Request Change content</label><br>
                                <input type="text" style="width:50%" name="request_change_content"
                                       id="request_change_content" placeholder="Request Change content"><br><br>
                                <input type="submit" name="request_change" id="request_change"
                                       class="button button-primary" value="Request Change">
                            </form>
                            <hr>
                        </div>
                    </div>
                <hr>
                    <span class="bold-and-blocking larger-text">Request Evalution</span>
                <?php }else{ ?>
                    <span class="bold-and-blocking larger-text">Request Evalution<br><br>
	                        <input type="text" name="user_email_is" id="user_email_is" placeholder="Email address">

                                <script type="text/javascript">
                                    jQuery(function () {
                                        jQuery("#user_email_is").autocomplete({
                                            source: '<?php echo get_site_url();?>/?action=get_linguist_list_autocomplete&lang=en',
                                            minLength: 1
                                        });
                                    });

                                </script>                                                           
	                        <button class="page-title-action" id="add_new_r">View or Add New</button>
	                        </span>
                    <script>
                        jQuery(function ($) {
                            jQuery('#add_new_r').click(function () {
                                var input = jQuery('#user_email_is').val();
                                if (input === '') {
                                    alert('Please select any linguist from dropdown');
                                } else {
                                    var data = {
                                        'action': 'check_linguist_email_id_exist_or_not',
                                        'user_email_is': input
                                    };
                                    $.ajax({
                                        type: 'POST',
                                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                                        data: data,
                                        global: false,
                                        success: function (response) {
                                            console.log(response);
                                            if (response === 'false') {
                                                alert("Email id not Exist.");
                                            } else if (response === 'unauthorized') {

                                                alert("You are an unauthorized user for this request.");
                                            } else {
                                                var url = '<?php echo admin_url(); ?>' + 'admin.php?page=freelinguist-admin-evaluation&user_id=' + response;
                                                window.location.href = url;
                                            }
                                        }
                                    });
                                }
                                return false;
                            });
                        });
                    </script>
                <?php } ?>
                <ul class="subsubsub"></ul>
                <p class="search-box">
                    <input class="enhanced-text" type="search" id="r-search-input" name="s" value="" title="search text">
                    <input type="submit" id="search-u" class="button large-text" value="Search">
                </p>
                <script>
                    jQuery(function () {
                        jQuery('#search-u').click(function () {
                            var inputURL = jQuery('#r-search-input').val();
                            var url = '<?php echo admin_url(); ?>' + 'admin.php?page=freelinguist-admin-evaluation&s=' + inputURL;
                            //you dont need the .each, because you are selecting by id
                            //Redirects
                            window.location.href = url;
                            return false;
                        });
                    });
                </script>
                <?php
                $RequestEvalutaionListTable = new RequestEvalutaion_List_Table();
                $RequestEvalutaionListTable->prepare_items();
                $RequestEvalutaionListTable->display(); ?>
            </div>
            <?php
        }
    }
}


// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


/**
 * Create a new table class that will extend the WP_List_Table
 */
class RequestEvalutaion_List_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data();
        usort($data, array(&$this, 'sort_data'));
        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage
        ));
        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'ID' => 'User ID',
            'display_name' => 'Name',
            'user_email' => 'Email',
            'request_evaluation_description' => 'Request description',
            'request_evaluation_date' => 'date',
        );
        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        return array('ID' => array('ID', true), 'user_email' => array('user_email', true), 'display_name' => array('display_name', true));
    }

    /**
     * Get the table data
     *
     * @return array
     */
    private function table_data()
    {
       
        global $wpdb;
        

        $current_user = wp_get_current_user();
        if ( in_array('administrator',$current_user->roles) || in_array('administrator_for_client',$current_user->roles)) {
            $usersList = '';
        } else {
            $usersList = getReportedUserByUserId(array('translator'));
        }
        if (empty($usersList)) {
            $query_usersList = '1';
        } else {
            $usersList = implode("','", $usersList);
            $query_usersList = " users.ID IN ('" . $usersList . "')";
        }
        if (isset($_REQUEST['s']) && !empty($_REQUEST['s'])) {
            $search_d = $_REQUEST['s'];
            $data = $wpdb->get_results(
                    "SELECT users.ID,users.display_name,users.user_email 
                             FROM wp_users users 
                             JOIN wp_usermeta usermeta ON users.ID =usermeta.user_id 
                             where 
                                usermeta.meta_key IN ('request_evaluating') AND
                                 (
                                    users.user_email LIKE '%" . $search_d . "%' OR
                                    users.display_name LIKE '%" . $search_d . "%' 
                                 )
                                 AND $query_usersList 
                                  GROUP BY users.ID ORDER BY usermeta.user_id
                              ", ARRAY_A);
        } else {
            $data = $wpdb->get_results(
                    "SELECT users.ID,users.display_name,users.user_email 
                             FROM wp_users users 
                             JOIN wp_usermeta usermeta ON users.ID =usermeta.user_id 
                             where usermeta.meta_key IN ('request_evaluating') 
                                AND  $query_usersList 
                              GROUP BY users.ID ORDER BY usermeta.user_id
                              ", ARRAY_A);
        }
        foreach ($data as $key => $value) {
            $user_id = $value['ID'];
            $send_email_url = admin_url() . 'admin.php?page=freelinguist-admin-send-cashier-message&user_is=' . $user_id . '&type=' . EVALUTION_SEND_EMAIL_TO_USER;
            $RequestEvalutaion_url = get_site_url() . '/wp-admin/admin.php?page=freelinguist-admin-evaluation&user_id=' . $user_id;
            $data[$key]['display_name'] = '<a href="' . $RequestEvalutaion_url . '">' . $value['display_name'] . '</a>';
            $data[$key]['user_email'] = '<a href="' . $send_email_url . '">' . $value['user_email'] . '</a>';
            $data[$key]['request_evaluation_date'] = get_user_meta($value['ID'], 'request_evaluation_date', true);
            $data[$key]['request_evaluation_description'] = get_user_meta($value['ID'], 'request_evaluation_description', true);

        }
        return $data;

    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  array $item Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'ID':
            case 'display_name':
            case 'user_email':
            case 'request_evaluation_description':
            case 'request_evaluation_date':
            case 'action':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     * @param array $a
     * @param array $b
     * @return Mixed
     */
    private function sort_data($a, $b)
    {
        // Set defaults
        $orderby = 'ID';
        $order = 'asc';
        // If orderby is set, use this as the sort column
        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }
        // If order is set use this as the order
        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }
        $result = strcmp($a[$orderby], $b[$orderby]);
        if ($order === 'asc') {
            return $result;
        }
        return -$result;
    }
}

?>