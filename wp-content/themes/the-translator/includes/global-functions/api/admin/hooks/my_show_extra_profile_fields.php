<?php

add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );
function my_show_extra_profile_fields( $user ) {

    /*
     * current-php-code 2020-Jan-15
     * current-hook
     * input-sanitized :
     */

    $current_user       = wp_get_current_user();

    if(in_array('translator',$user->roles )  || in_array('customer',$user->roles )){
        ?>
        <hr>
        <table class="form-table">
            <tr>
                <th><label for="assign_country_from">Tax Form</label></th>
                <td>
                    <!--  <div class="upload-file upload-file-button" style="width: 30%;">
                                    <label><?php //echo "Upload Signed Tax Form"; ?> <b><i class="file-icon"></i> </b></label>
                                    <input type="file" class="signedfilesdata" name="files[]" id="uploadSignedTaxForm" class="files-data btn blue-btn update-btn email_the_form">
                                </div> -->
                    <?php
                    $tax_arr =  get_the_author_meta( FreelinguistUserHelper::META_KEY_NAME_TAX_FORM, $user->ID );
                    $upload_dir = wp_upload_dir();
                    $user_dirname = $upload_dir['basedir'];
                    $file_path = get_user_meta($user->ID,FreelinguistUserHelper::META_KEY_NAME_TAX_FORM,true);
                    $file = $user_dirname.'/'.$file_path;

                    if(file_exists($file)) {
                        if(!empty($tax_arr)){
                            $tax_arr = explode('/', $tax_arr);
                            $signedform_name = '<a href="'.get_site_url().'?action=download_tax_form&user_id='.$user->ID.'&lang=en" class="download-taxform large-text">'.$tax_arr[count($tax_arr)-1].'</a>';
                            echo $signedform_name;
                        }else{
                            echo "Not Exists";
                        }
                    }else{
                        echo "Not Exists";
                    }
                    ?>

                </td>

            </tr>
        </table>

        <table class="form-table">
            <tr>
                <th><label for="assign_country_from">User residence Country</label></th>
                <td>
                    <?php echo get_country_by_index(get_user_meta($user->ID,'user_residence_country',true)); ?>
                </td>

            </tr>
        </table>
        <?php
    }
    if (in_array('administrator', $current_user->roles) || in_array('administrator_for_client', $current_user->roles)) {?>
        <hr>
        <h3>Processing ID</h3>
        <table class="form-table">
            <tr>
                <th><label for="user_processing_id">User processing Id.</label></th>
                <td>
                    <select name="user_processing_id" id="user_processing_id">
                        <option>-- Select User --</option>
                        <?php $get_processing_ids = get_processing_id();
                        foreach ( $get_processing_ids as $key=>$value ) {
                            if(get_the_author_meta( 'user_processing_id', $user->ID ) == $value){ ?>
                                <option selected="selected" value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php }else{ ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                    <span class="description">Processing id.</span>
                </td>
            </tr>
        </table>
        <hr>
        <?php if(!empty(array_intersect($user->roles ,array('cashier_sub_admin','evaluation_sub_admin','message_sub_admin','meditation_sub_admin')))){ ?>
            <h3>Report info</h3>
            <table class="form-table">
                <tr>
                    <th><label for="reported_to">Reported to.</label></th>
                    <td>
                        <select name="reported_to" id="reported_to">
                            <option>-- Select User --</option>
                            <?php
                            $args = array(
                                'role__in'     => array('super_sub_admin')
                            );
                            $all_user = get_users( $args );
                            foreach ( $all_user as $user_info ) {
                                if(get_the_author_meta( 'reported_to', $user->ID ) == $user_info->ID){ ?>
                                    <option selected="selected" value="<?php echo $user_info->ID; ?>"><?php echo $user_info->user_login; ?></option>
                                <?php }else{ ?>
                                    <option value="<?php echo $user_info->ID; ?>"><?php echo $user_info->user_login; ?></option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                        <span class="description">select any user.</span>
                    </td>
                </tr>
            </table>
            <hr>
            <?php
        } ?>
        <?php if(in_array('super_sub_admin', $current_user->roles) ){ ?>
            <hr>
            <h3>Report info</h3>
            <table class="form-table">
                <tr>
                    <th><label for="assign_country_from">Processing Country From.</label></th>
                    <td>
                        <select  onchange='getAssingCountryTo(this.value)' name="assign_country_from" style="width:80%" id="assign_country_from">
                            <option value="">-- Select Country -- </option>
                            <?php
                            $countries = get_countries();
                            $i = 0;
                            foreach ($countries as $key) {
                                if(get_the_author_meta( 'assign_country_from', $user->ID ) == $i){ ?>
                                    <option selected="selected" value="<?php echo $i; ?>"><?php echo $key; ?></option>
                                <?php }else{ ?>
                                    <option value="<?php echo $i; ?>"><?php echo $key; ?></option>
                                <?php } ?>
                                <?php
                                $i++;
                            }

                            ?>
                        </select>
                    </td>
                    <th><label for="assign_country_to">Processing Country To.</label></th>
                    <td>
                        <div class="country_assign_to_div" id="country_assign_to_div">
                            <select name="assign_country_to" style="width:80%" id="assign_country_to">
                                <!-- <option value="">  -- Select Country --   </option> -->
                                <?php
                                $countries = get_countries();
                                $i = 0;
                                if(get_the_author_meta( 'assign_country_to', $user->ID ) >= 0){
                                    foreach ($countries as $key) {
                                        if(get_the_author_meta( 'assign_country_to', $user->ID ) <= $i){
                                            if(get_the_author_meta( 'assign_country_to', $user->ID ) == $i){ ?>
                                                <option selected="selected" value="<?php echo $i; ?>"><?php echo $key; ?></option>
                                            <?php }else{ ?>
                                                <option value="<?php echo $i; ?>"><?php echo $key; ?></option>
                                            <?php } ?>
                                            <?php
                                        }
                                        $i++;
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="2"><span style="text-align: center"><span class="description">First Select the from processing country</span></span></td>
                    <td></td>
                </tr>
            </table>
            <hr>
            <?php
        }
    }

    if( in_array('administrator', $current_user->roles) && in_array('translator', $user->roles)  ){

        ?>
        <h3>Account information</h3>
        <table class="form-table">
            <tr>
                <th><label for="Translation">Translation</label></th>
                <td>
                    <select name="user_translation_technical_level" id="user_translation_technical_level" title="Technical Level">
                        <option value="">--Select Level--</option>
                        <?php
                        for($i=1;$i<=10;$i++){
                            $value = 'T'.$i;
                            $option_value = 'technical_level_T'.$i;
                            $selected = (get_the_author_meta( 'user_translation_technical_level', $user->ID ) == $option_value) ? 'selected' : '';
                            echo '<option '.$selected.' value='.$option_value.'>'.$value.'</option>';
                        }
                        for($i=1;$i<=10;$i++){
                            $value = 'TL'.$i;
                            $option_value = 'technical_level_TL'.$i;
                            $selected = (get_the_author_meta( 'user_translation_technical_level', $user->ID ) == $option_value) ? 'selected' : '';
                            echo '<option '.$selected.' value='.$option_value.'>'.$value.'</option>';
                        }
                        ?>
                    </select>
                    <span class="description">Assign Translation level.</span>
                </td>
            </tr>
            <tr>
                <th><label for="Editing">Editing & proofreading</label></th>
                <td>
                    <select name="user_editing_technical_level" id="user_editing_technical_level" title="Technical level">
                        <option value="">--Select Level--</option>
                        <?php
                        for($i=1;$i<=10;$i++){
                            $value = 'E'.$i;
                            $option_value = 'technical_level_E'.$i;
                            $selected = (get_the_author_meta( 'user_editing_technical_level', $user->ID ) == $option_value) ? 'selected' : ' ';
                            echo '<option '.$selected.' value='.$option_value.'>'.$value.'</option>';
                        }
                        for($i=1;$i<=10;$i++){
                            $value = 'EL'.$i;
                            $option_value = 'technical_level_EL'.$i;
                            $selected = (get_the_author_meta( 'user_editing_technical_level', $user->ID ) == $option_value) ? 'selected' : '';
                            echo '<option '.$selected.' value='.$option_value.'>'.$value.'</option>';
                        }
                        ?>
                    </select>
                    <span class="description">Assign Editing level</span>
            </tr>
            <tr>
                <th><label for="Writing">Writing</label></th>
                <td>
                    <select name="user_writing_technical_level" id="user_writing_technical_level" title="Writing Technical Level">
                        <option value="">--Select Level--</option>
                        <?php
                        for($i=1;$i<=10;$i++){
                            $value = 'W'.$i;
                            $option_value = 'technical_level_W'.$i;
                            $selected = (get_the_author_meta( 'user_writing_technical_level', $user->ID ) == $option_value) ? 'selected' : '';
                            echo '<option '.$selected.' value='.$option_value.'>'.$value.'</option>';
                        }
                        for($i=1;$i<=10;$i++){
                            $value = 'WL'.$i;
                            $option_value = 'technical_level_WL'.$i;
                            $selected = (get_the_author_meta( 'user_writing_technical_level', $user->ID ) == $option_value) ? 'selected' : '';
                            echo '<option '.$selected.' value='.$option_value.'>'.$value.'</option>';
                        }
                        ?>
                    </select>
                    <span class="description">Assign Writing level.</span>
            </tr>
            <tr>
                <th><label for="Balance">Balance</label></th>
                <td>
                    <input type="text" title="User Balance" name="total_user_balance" readonly id="total_user_balance" value="<?php echo esc_attr( get_the_author_meta( 'total_user_balance', $user->ID ) ); ?>" class="regular-text" /><br />
                    <span class="description">User Balance is.</span>
                </td>
            </tr>

        </table>

        <h3>Files</h3>
        <table class="form-table">
        <?php

        global $wpdb;
        $lang = FLInput::get('lang','en');
        $resumes_files     = $wpdb->get_results("SELECT * FROM wp_files WHERE by_user = $user->ID AND type=".FLWPFileHelper::TYPE_USER_ASSOCIATED );
        //code-unused there are not any such types set right now, but could in the future in new code
        for($i=0;$i<count($resumes_files);$i++){
            if($i== 0){
                echo '<tr><th colspan="3"><h3>User Files</h3><br><hr></th></tr> ';
            }
            $file_id = $resumes_files[$i]->id;
            ?>
            <tr>
                <th><?php echo $i+1; ?>.) </th>
                <td>
                    <!-- code-notes [download]  new download line -->
                    <div class="freelinguist-download-line">

                            <span class="freelinguist-download-name">

                                <span class="freelinguist-download-name-itself breakall">
                                    <?= $resumes_files[$i]->file_name ?>
                                </span>
                            </span> <!-- /.freelinguist-download-name -->

                        <a class="red-btn-no-hover freelinguist-download-button enhanced-text"
                           data-job_file_id = "<?= $resumes_files[$i]->id ?>"
                           download = "<?= $resumes_files[$i]->file_name ?>"
                           href="#">
                            Download
                        </a> <!-- /.freelinguist-download-button -->

                    </div><!-- /.freelinguist-download-line-->


                </td>
                <td>
                    <a class="glyphicon glyphicon-remove deleteProFile" id="<?php echo $file_id; ?>" href="#">
                        Delete
                    </a>
                </td>
            </tr>
            <?php
        }
        ?>
        <tr>
            <th colspan="3">
                <?php $account_url = get_site_url()."/user-account/?profile_type=translator&lang=$lang&user=".$user->data->user_nicename; ?>
                <a class="user_other_info" id="" href="<?= $account_url ?>" target="_blank">
                    Click here to see User Public Profile
                </a>
            </th>
        </tr>
        </table>
        <hr>
    <?php }
}