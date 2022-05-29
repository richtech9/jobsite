/***** Upload content files **************/
jQuery(function ($) {
    $(document).on( 'click', '#atc_files_content_middle', function(){
        //code-bookmark For content files, this is where the file gets uploaded first
        var data_to_ajax 					= [];

        let content_id = $(this).data('content_id');
        data_to_ajax.push({'name':'content_id', 'value':content_id});

        data_to_ajax.push({'name':'action', 'value':'cvf_upload_files_content_process'});





        $(this).fileupload({

            url: adminAjax.url,

            formData : data_to_ajax,

            dataType: 'json',

            global: false,

            add: function(e, data){

                var file_Size_limit = 1024 * 1024 * 50;

                if (data.files[0].size > file_Size_limit){

                    bootbox.alert( data.files[0].name + " File size is greater than 50MB" );

                }else{
                    $('#progress_middle').css( 'display','block');

                    data.submit();

                }

            },

            send: function(/*e, data*/){},


            fail: function (e, data) {
                let maybe_information = data.response().jqXHR.responseJSON;

                if (typeof maybe_information === 'object' && maybe_information !== null) {
                    will_handle_ajax_error('Problem Uploading File',maybe_information.message);
                }

                $('#progress_middle .progress-bar').css('display', 'none');

            },


            done: function(e, data){
                $('#atc_files_order').attr('disabled',false);
                /**
                 * @type {FreelinguistAddContentFileResponse} r
                 */
                var r = data.result;
                if(r.status===true){

                    let tr = $('<tr class="main-row"></tr>');
                    let td_file = $('<td class="large-text" style="padding: 8px 12px"></td>');
                    td_file.html(
                        '<!-- code-notes [download]  new download line -->\n' +
                        '                                                <div class="freelinguist-download-line">\n' +
                        '\n' +
                        '                                                    <span class="freelinguist-download-name">\n' +
                        '                                                        <i class="text-doc-icon larger-text"></i>\n' +
                        '                                                        <span class="freelinguist-download-name-itself enhanced-text">\n' +
                                                                                     r.public_name + '   \n' +
                        '                                                        </span>\n' +
                        '                                                    </span> <!-- /.freelinguist-download-name -->\n' +
                        '\n' +
                        '                                                    <a class="red-btn-no-hover freelinguist-download-button please-add-me enhanced-text"\n' +
                        '                                                       data-content_file_id = "'+r.content_file_id+'"\n' +
                        '                                                       download = "'+r.public_name+'"\n' +
                        '                                                       href="#">\n' +
                        '                                                        Download\n' +
                        '                                                    </a> <!-- /.freelinguist-download-button -->\n' +
                        '\n' +
                        '                                                </div><!-- /.freelinguist-download-line-->');

                    tr.append(td_file);
                    let td_delete = $('<td style="text-align: center;vertical-align: middle;"></td>');

                    td_delete.html(
                        ' <a class="delete_content_file large-text" id="'+r.content_file_id+'" href="#">\n' +
                        '     <i class="fa fa-trash"></i>\n' +
                        ' </a>');

                    tr.append(td_delete);


                    $("#content_files").append(tr);

                    jQuery( "a.freelinguist-download-button.please-add-me" ).one( "click", freelinguist_get_download );
                    $("#content_files").find('a.please-add-me').removeClass('please-add-me');

                    $('#progress_middle').css( 'display','none');

                    var htm=  '<input type="hidden" name="already_ins[]" value="'+r.content_file_id +'" id="'+r.new_name_with_time+'">';

                    $('#files_name_container').append(htm);

                    //code-notes add in public name to be passed back to container
                    htm =  '<input type="hidden" name="public_file_name[]" value="'+r.public_name +'" id="public_'+r.new_name_with_time+'">';
                    $('#files_name_container').append(htm);
                } else {
                    will_handle_ajax_error('Adding Content Files',r.message);
                }

            },

            progressall: function( e, data ){

                console.log( data );

                $('#progress_middle').css( 'display','block');

                // noinspection JSCheckFunctionSignatures
                var progress = parseInt(data.loaded / data.total * 100, 10);

                $('#atc_files_content').attr('disabled',false);

                $('#progress_middle .progress-bar').css( 'width', progress + '%' );


            }

        }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

    });

    //code-bookmark when the freelancer presses the button to upload text files to be chapters in content
    $(document).on( 'click', '#atc_text_files_content', function(){


        var percent 				= $('.percent');


        var data 					= [];

        data.push({'name':'action', 'value':'cvf_upload_text_files_content_process'});

        let content_id = $(this).data('content_id');
        data.push({'name':'content_id', 'value':content_id});


        $(this).fileupload({

            url: adminAjax.url,

            formData : data,

            dataType: 'json',

            global: false,

            add: function(e, data){

                var file_Size_limit = 1024 * 1024 * 50;

                if (data.files[0].size > file_Size_limit){

                    bootbox.alert( data.files[0].name + " File size is greater than 50MB" );

                }else{
                    $('#progress').css( 'display','block');

                    data.submit();

                }

            },

            send: function(e, data){},

            fail: function (e, data) {
                let maybe_information = data.response().jqXHR.responseJSON;

                if (typeof maybe_information === 'object' && maybe_information !== null) {
                    will_handle_ajax_error('Problem Uploading File',maybe_information.message);
                }

                $('#progress').css('display', 'none');

            },

            done: function(e, data){
                $('#atc_files_order').attr('disabled',false);
                var r = data.result;
                if(r.status===true){
                    let words = r.uploaded_words;
                    let title = r.uploaded_title;

                   //code-notes new call the add more function with words
                    fl_add_new_chapter_editor(title,words,true);

                    $('#progress').css( 'display','none');

                } else {
                    will_handle_ajax_error('Adding Content Chapters',r.message);
                }

            },

            progressall: function( e, data ){

                console.log( data );

                $('#progress').css( 'display','block');

                var progress = parseInt(data.loaded / data.total * 100, 10);

                $('#atc_files_content').attr('disabled',false);

                $('#progress .progress-bar').css( 'width', progress + '%' );

                percent.html( progress + '%' );

            }

        }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

    });
});
