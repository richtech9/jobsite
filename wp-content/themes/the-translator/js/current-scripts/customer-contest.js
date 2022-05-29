jQuery(function ($) {
    //code-bookmark when the contest owner, on the second edit screen, uploads a job instruction
    $(document).on('click', '#atc_files_order', function () {

        var percent = $('.percent');

        var data = jQuery('#order-process_translation').serializeArray();

        var job_id = $(this).data('jid');

        var temp_id = $('#temp_id').val();

        if(!temp_id){
            temp_id = Math.floor(Math.random()*2000000);
            $('#temp_id').val(temp_id);
        }


        data.push({'name': 'action', 'value': 'cvf_upload_files_order_process_new'});
        data.push({'name': 'job_id', 'value': job_id});
        data.push({'name':'temp_id', 'value':temp_id});


        $(this).fileupload({

            url: adminAjax.url,

            formData: data,

            dataType: 'json',

            global: false,

            add: function (e, data) {


                $('#progress .progress-bar').css('width', '0%');

                var file_Size_limit = 1024 * 1024 * 50;

                if (data.files[0].size > file_Size_limit) {

                    bootbox.alert(data.files[0].name + " File size is greater than 50MB");

                } else {
                    $('#progress').css('display', 'block');

                    data.submit();

                }

            },

            fail: function (e, data) {
                let maybe_information = data.response().jqXHR.responseJSON;

                if (typeof maybe_information === 'object' && maybe_information !== null) {
                    will_handle_ajax_error('Problem Uploading File',maybe_information.message);
                }

                $("#progress").css('display', 'none');

            },

            send: function (e, data) {

                //console.log("send",e,data);

            },
            /* fileInput: function(e, data){

                $('#progress .progress-bar').css( 'width', '0%' );

            },
            */
            done: function (e, data) {


                $('#atc_files_order').attr('disabled', false);


                if (data.result != null) {

                    $.each(data.result.files, function (index, file) {


                        if (file.error !== undefined) {

                            alert(file.name + ' ' + file.error);

                        }


                        let new_file_id = data.result.attach_id;
                        let new_file_name = file.name;

                        let li = $('<li></li>');
                        let left_div = $('<div class="float-left enhanced-text" style="width: 90%;display: inline-block"></div>');
                        left_div.html(
                            '<!-- code-notes [download]  new download line -->\n' +
                            '                                                <div class="freelinguist-download-line">\n' +
                            '\n' +
                            '                                                    <span class="freelinguist-download-name">\n' +
                            '                                                        <i class="text-doc-icon larger-text"></i>\n' +
                            '                                                        <span class="freelinguist-download-name-itself enhanced-text">\n' +
                            new_file_name + '   \n' +
                            '                                                        </span>\n' +
                            '                                                    </span> <!-- /.freelinguist-download-name -->\n' +
                            '\n' +
                            '                                                    <a class="red-btn-no-hover freelinguist-download-button please-add-me enhanced-text"\n' +
                            '                                                       data-job_file_id = "' + new_file_id + '"\n' +
                            '                                                       download = "' + new_file_name + '"\n' +
                            '                                                       href="#">\n' +
                            '                                                        Download\n' +
                            '                                                    </a> <!-- /.freelinguist-download-button -->\n' +
                            '\n' +
                            '                                                </div><!-- /.freelinguist-download-line-->');

                        li.append(left_div);
                        let right_div = $('<div class="floatright"></div>');

                        right_div.html(
                            '<a href="#" class="cross-icon large-text" onclick="return single_remove_selected(this,\'' + new_file_id + '\')"></a>');
                        li.append(right_div);
                        $("ul.document-row").append(li);


                        jQuery("a.freelinguist-download-button.please-add-me").one("click", freelinguist_get_download);
                        $("ul.document-row").find('a.please-add-me').removeClass('please-add-me');


                        $("ul.document-row").append(li);

                        $('#progress').css('display', 'none');

                        $('#already_ins').val(file.name);


                        var count = jQuery('.document-row li').length;

                        $('#count').text(count);


                    });

                } else {

                    $("#progress").css('display', 'none');

                    $('#atc_files_order').attr('disabled', false);

                    // noinspection JSUnresolvedVariable
                    bootbox.alert(reg_validation.general_file_upload_error_message);

                }

            },

            progressall: function (e, data) {


                $('#progress').css('display', 'block');

                // noinspection JSCheckFunctionSignatures
                var progress = parseInt(data.loaded / data.total * 100, 10);

                $('#atc_files_order').attr('disabled', false);

                $('#progress .progress-bar').css('width', progress + '%');

                percent.html(progress + '%');

            }

        }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

    });
});