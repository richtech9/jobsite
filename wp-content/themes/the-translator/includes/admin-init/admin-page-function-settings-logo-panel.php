<?php

/*
    * current-php-code 2021-Jan-8
    * input-sanitized :
    * current-wp-template:  admin-screen  settings logo
*/

function customer_theme_logo_val()
{
    ?>
    <div class="wrap">
        <h3>Logo setting</h3>
        <?php
        if (isset($_REQUEST['del_key'])) {
            delete_option($_REQUEST['del_key']);
        }
        if (isset($_REQUEST['submit_logo'])) {
            //print_R($_FILES);
            $wp_upload_dir = wp_upload_dir();
            $path = $wp_upload_dir['basedir'] . '/logo/';
            $count = 0;
            foreach ($_FILES['files']['name'] as $f => $name) {
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $logo_name = $_REQUEST['language_is'] . '_logo';
                $new_filename = $logo_name . '.' . $extension;
                unlink($path . $new_filename);
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                // /echo $path.$new_filename; exit;
                if ($_FILES['files']['error'][$f] == 4) {
                    continue;
                }
                if ($_FILES['files']['error'][$f] == 0) {
                    if (move_uploaded_file($_FILES["files"]["tmp_name"][$f], $path . $new_filename)) {
                        $count++;
                        $file_path = 'logo/' . $new_filename;
                        if (isset($_REQUEST['language_is'])) {
                            $logo_name = $_REQUEST['language_is'] . '_logo';
                            update_option($logo_name, $file_path);
                            echo '  <div class="updated settings-error error notice is-dismissible" id="setting-error-settings_updated">
                <p><strong>Updated.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

                        } else {
                            echo '  <div class="updated settings-error error notice is-dismissible" id="setting-error-settings_updated">
                <p><strong>post data empty.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';

                        }
                    }
                }
            }
        }
        ?>
        <form id="setting_logo_form" method="POST" enctype="multipart/form-data"
              action="<?php echo admin_url('admin.php?page=customer-theme-logo-val'); ?>&lang=en">
            <table class="form-table">
                <tbody>
                <tr class="">
                    <th scope="row">Select language</th>
                    <td scope="row">
                        <select name="language_is" id="language_is" title="language">
                            <?php
                            $columns_lang = array(
                                'english' => 'English',
                                'chinese' => 'Chinese (Simplified)',
                                'russian' => 'Russian',
                                'japanese' => 'Japanese',
                                'german' => 'German',
                                'spanish' => 'Spanish',
                                'french' => 'French',
                                'portuguese' => 'Portuguese',
                                'italian' => 'Italian',
                                'polish' => 'Polish',
                                'turkish' => 'Turkish',
                                'persian' => 'Persian',
                                'chinese_traditional' => 'Chinese (Traditional)',
                                'danish' => 'Danish',
                                'dutch' => 'Dutch',
                                'hindi' => 'hindi',
                                'arabic' => 'Arabic',
                                'korean' => 'Korean',
                                'czech' => 'Czech',
                                'vietnamese' => 'Vietnamese',
                                'indonesian' => 'Indonesian',
                                'swedish' => 'Swedish',
                                'malay' => 'Malay',
                            );
                            ?>
                            <?php foreach ($columns_lang as $key => $value) { ?>
                                <option value='<?php echo $key ?>'> <?php echo $value; ?> </option>
                            <?php } ?>
                        </select>
                        <p class="description">Add or update logo image according to language</p>
                    </td>
                </tr>
                <tr class="">
                    <th scope="row">Upload logo</th>
                    <td scope="row">
                        <input type="file" name="files[]">
                        <p class="description"> Upload logo image size should be <b>227*54 px</b></p>
                    </td>
                </tr>
                <tr class="">
                    <th scope="row"></th>
                    <td scope="row">
                        <input type="submit" name="submit_logo" value="Submit">
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
    <?php

    $logo_according_to_language_List_Table = new logo_according_to_language_List_Table();
    $logo_according_to_language_List_Table->prepare_items();
    $logo_according_to_language_List_Table->display();
}