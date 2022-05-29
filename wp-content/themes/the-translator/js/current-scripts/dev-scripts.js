if (!$) {
    var $ = jQuery;
}

jQuery(function(){
    $('[data-toggle="tooltip"]').tooltip();
});

jQuery(function ($) {

    $('p#user_switching_switch_on').each(function() {
       //only executes if the admin is switched, do a little pause to let all the things bind first
        setTimeout(function () {
            $('a:not(.freelinguist-download-button)').unbind('click');
            $('span').unbind('click');
            $('i').unbind('click');
            $('div').unbind('click');
            $('button').unbind('click');
            $('input').attr('disabled', 'disabled');
            $('form').submit(function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });

            jQuery('body').off( "click", "**" );
            jQuery('document').off( "click", "**" );
            jQuery(".tm-input").off( "tm", "**" );
        }, 100);

    });

    var max_l = 10000;


    var bid_note_val = document.getElementById("bid_note");

    if (bid_note_val != null) {

        bid_note_val.onpaste = function (e) {

            e.clipboardData.getData('text/plain').slice(0, max_l)

        };

    }


    $.validator.addMethod("customemail",

        function (value, element) {
            //task-future-work on the javascript side , there is an email filter which will reject some valid emails, this regex does not cover all the cases
            return /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(value);

        },

        reg_validation.valid_email
    );

    $.validator.addMethod("noSpace", function (value, element) {
        return value.indexOf(" ") < 0 && value !== "";
    }, "No space please and don't leave it empty");


    $.validator.addMethod("at_least_five_char",

        function validate(string) {

            var re = /^[\u3300-\u9fff\uf900-\ufaff]{2,}$/;

            return string.length >= 5 || string.match(re);

        },

        reg_validation.at_least_five_char);

    $("input[type=text]").attr('maxlength', '1000');

    //code-bookmark-js Here the user registration form is being validated by the js, and where we adjust the messages if the email is already used
    $("#linguist-registration").validate({

        ignore: [],

        rules: {


            password: {required: true, noSpace: true, minlength: 6},

            //code-notes removed js validation of confirm password

            email: {

                required: {

                    depends: function () {

                        $(this).val($.trim($(this).val()));

                        return true;

                    }

                },

                customemail: true,

                //code-navigation calls the reg_email handler in the ajax section
                remote: {

                    url: adminAjax.url, type: "post",

                    data: {

                        reg_email: function () {

                            return $("#ucheck_email").val();

                        }

                    },

                    global: false,

                }

            },

            usr_type: {required: true},

            agree: "required"

        },

        messages: {


            password: {

                required: reg_validation.password,

                noSpace: reg_validation.blank_space,

                minlength: reg_validation.valid_password


            },


            email: {

                required: reg_validation.valid_email,

                email: reg_validation.valid_email,

                remote: reg_validation.email_exist

            },

            usr_type: reg_validation.user_type,

            agree: reg_validation.policy

        },

        submitHandler: function (form) {

            userRegistraton();

        }

    });

    //code-bookmark-js the login form is being validated
    $("#freeling-login").validate({

        rules: {

            user_login: {required: true, email: true},

            user_password: "required",

        },

        messages: {

            user_login: {

                required: reg_validation.enter_email,

                email: reg_validation.valid_email,

            },

            user_password: reg_validation.password,

        }

    });


    //code-bookmark-js the customer project page going to hire
    $("#TranslaterHireForm").validate({

        ignore: [],

        rules: {

            bid_note: {required: true},

        },

        messages: {

            bid_note: {

                required: reg_validation.note_enter,

            },

        },

        submitHandler: function (form) {

            hireTranslate();

        }

    });


    //code-bookmark-js the translater's account education form entry
    $("#educationDetailForm").validate({

        ignore: [],

        rules: {

            "year_attended[]": {required: true, number: true, maxlength: 10},

            "institution[]": {required: true, maxlength: 50},

            "degree[]": {required: true, maxlength: 50}

        },

        messages: {

            "year_attended[]": {

                required: "<span class='error_spa'>" + reg_validation.linguist_valid_attended_year + "</span>",

                number: "<span class='error_spa'>" + reg_validation.linguist_valid_valid_attended_year + "</span>",

                maxlength: "<span class='error_spa'>" + reg_validation.linguist_valid_attended_length + "</span>"

            },

            "institution[]": {

                required: "<span class='error_spa'>" + reg_validation.linguist_valid_institutuin + "</span>",

                maxlength: "<span class='error_spa'>" + reg_validation.linguist_valid_institutuin_length + "</span>"

            },

            "degree[]": {

                required: "<span class='error_spa'>Please enter degree</span>",

                maxlength: "<span class='error_spa'>" + reg_validation.linguist_valid_expertise_length + "</span>"

            },

        },

        submitHandler: function (form) {

            educationDetailForm();

        }

    });


    //code-bookmark-js the translater's account certificate form entry
    $("#certificationDetailForm").validate({

        ignore: [],

        rules: {

            "year_recieved[]": {required: true, number: true, maxlength: 50},

            "recieved_from[]": {required: true, maxlength: 50},

            "certificate[]": {required: true, maxlength: 50}

        },

        messages: {

            "year_recieved[]": {

                required: "<span class='error_spa'>" + reg_validation.linguist_valid_attended_year + "</span>",

                number: "<span class='error_spa'>" + reg_validation.linguist_valid_valid_attended_year + "</span>",

                maxlength: "<span class='error_spa'>" + reg_validation.linguist_valid_attended_length + "</span>"

            },

            "recieved_from[]": {

                required: "<span class='error_spa'>" + reg_validation.linguist_valid_received_from + "</span>",

                maxlength: "<span class='error_spa'>" + reg_validation.linguist_valid_received_from_length + "</span>"

            },

            "certificate[]": {

                required: "<span class='error_spa'>" + reg_validation.linguist_valid_certificate + "</span>",

                maxlength: "<span class='error_spa'>" + reg_validation.linguist_valid_certificate_length + "</span>"

            },

        },


        submitHandler: function (form) {

            certificationDetailForm();

        }

    });


    //code-bookmark-js the translater's account related experience form entry
    $("#relatedExperinceDetailForm").validate({

        ignore: [],

        rules: {

            "year_in_service[]": {required: true, number: true, maxlength: 50},

            "employer[]": {required: true, maxlength: 50},

            "duties[]": {required: true, maxlength: 50}

        },

        messages: {

            "year_in_service[]": {

                required: "<span class='error_spa'>" + reg_validation.linguist_user_info_Year_in_service + "</span>",
                number: "<span class='error_spa'>" + reg_validation.linguist_valid_year_in_service_number + "</span>",
                maxlength: "<span class='error_spa'>" + reg_validation.linguist_valid_year_in_service_length + "</span>"

            },

            "employer[]": {

                required: "<span class='error_spa'>" + reg_validation.linguist_valid_employer + "</span>",

                maxlength: "<span class='error_spa'>" + reg_validation.linguist_valid_employer_length + "</span>"

            },

            "duties[]": {

                required: "<span class='error_spa'>" + reg_validation.linguist_valid_duties + "</span>",

                maxlength: "<span class='error_spa'>" + reg_validation.linguist_valid_duties_length + "</span>"

            },

        },

        submitHandler: function (form) {

            relatedExperinceDetailForm();

        }

    });


    //code-bookmark-js the translater's account language form entry
    $("#languageDetailForm").validate({

        ignore: [],

        rules: {

            "language[]": {required: true, maxlength: 50},

            "year_of_experince[]": {required: true, number: true, maxlength: 10},

            "areas_expertise[]": {required: true, maxlength: 50}

        },

        messages: {

            "language[]": {

                required: "<span class='error_spa'>" + reg_validation.linguist_valid_language + "</span>",

                maxlength: "<span class='error_spa'>" + reg_validation.linguist_valid_language_length + "</span>"

            },

            "year_of_experince[]": {

                required: "<span class='error_spa'>" + reg_validation.linguist_valid_year_of_experince + "</span>",

                number: "<span class='error_spa'>" + reg_validation.linguist_valid_year_of_experince_number + "</span>",

                maxlength: "<span class='error_spa'>" + reg_validation.linguist_valid_year_of_experince_length + "</span>"

            },

            "areas_expertise[]": {

                required: "<span class='error_spa'>" + reg_validation.linguist_valid_areas_expertise + "</span>",

                maxlength: "<span class='error_spa'>" + reg_validation.linguist_valid_areas_expertise_length + "</span>"

            },

        },

        submitHandler: function (form) {

            languageDetailForm('languageDetailForm');

        }

    });


    //code-bookmark-js when the save button is pressed for the contest on the first edit screen
    jQuery("button.fl-save-contest-description").click(function (e) {
        e.stopPropagation();
        e.preventDefault();
        let job_instruction = jQuery('textarea#job_instruction_editable').val();

        var job_id = $('#job_id').val();

        var author_id = $('#author').val();


        var data = jQuery("#editable_form_instruction").serializeArray();

        data.push(
            {'name': 'action', 'value': 'job_instruction_editable'},
            {'name': 'job_instruction', 'value': job_instruction},
            {'name': 'job_id', 'value': job_id},
            {'name': 'author_id', 'value': author_id});

        $.ajax({

            type: 'POST',

            url: adminAjax.url,

            data: data,

            global: false,

            success: function (response_raw) {


                /**
                 * @type {FreelinguistBasicAjaxResponse} response
                 */
                let response = freelinguist_safe_cast_to_object(response_raw);
                if (response.status === true) {
                    setTimeout(function () {
                        window.location.reload(true);
                    }, 10);
                } else {
                    will_handle_ajax_error('Contest Save', response.message);
                }

            }

        });

        return false;

    });


    //code-bookmark-js when the save description button is pressed on the project edit page
    $("button.fl-save-job-description").click(function () {

        //code-notes fixed this to be called when the save button is pressed

        var job_id = $('#job_id').val();

        var author_id = $('#author').val();

        let job_instruction = jQuery('textarea#job_description_editable').val();


        var data = jQuery("#editable_form_description").serializeArray();

        data.push(
            {'name': 'action', 'value': 'job_description_editable'},
            {'name': 'job_instruction', 'value': job_instruction},
            {'name': 'job_id', 'value': job_id},
            {'name': 'author_id', 'value': author_id});

        $.ajax({

            type: 'POST',

            url: adminAjax.url,

            data: data,

            global: false,

            success: function (response) {




                if (jQuery.trim(response) == 'true') {

                    //$("#result_reposne_title").html('Title updated successfully.').show().delay(5000).fadeOut();
                    window.location.reload();

                } else {

                    //$('#result_reposne_title').html('Unauthorized user.');
                    alert('Unauthorized user.');

                }

            }

        });

    });


    //code-bookmark-js when translator's account profile form is submitted
    $("#profiledetail").validate({

        ignore: [],

        rules: {

            display_name: {required: true},

            user_phone: {required: true, number: true, minlength: 9},

        },

        messages: {

            display_name: {

                required: reg_validation.profile_name_valid

            },

            user_phone: {

                required: reg_validation.phone_number_required,

                minlength: reg_validation.phone_number_digit,

            },

        },

        submitHandler: function (form) {

            update_personal_info();

        }

    });


    //code-bookmark-js when customer's account profile form is submitted
    //code-notes this is untested in the last year or so
    $("#customerProfiledetail").validate({

        ignore: [],

        rules: {

            display_name: {required: true},

            user_phone: {required: true, number: true, minlength: 9},

        },

        messages: {

            display_name: {

                required: reg_validation.profile_name_valid

            },

            user_phone: {

                required: reg_validation.phone_number_required,

                number: reg_validation.phone_number_valid,

                minlength: reg_validation.phone_number_digit,

            },

        },

        submitHandler: function (form) {

            customerProfiledetail();

        }

    });


    //code-bookmark-js when translator's account summary form is submitted
    $("#summaryinformation").validate({

        ignore: [],

        rules: {

            user_description: "required",

        },

        messages: {

            user_description: reg_validation.user_description,

        },

        submitHandler: function (form) {

            update_summary_info();

        }

    });


    //code-bookmark-js when translator account page requests an evaluation
    //code-notes this is not enabled at the moment but is the only way to start evaluations for the backend admin page dedicated to tit
    $("#RequestEvaluation").validate({

        ignore: [],

        rules: {

            user_description: "required",

        },

        messages: {

            user_description: reg_validation.user_description_reason,

        },

        submitHandler: function (form) {

            update_RequestEvaluation_info();

        }

    });


    //code-bookmark-js when the customer wallet submits a coupon
    $("#couponForm").validate({

        ignore: [],

        rules: {

            coupon: "required",

        },

        messages: {

            coupon: reg_validation.promo_code_enter,

        },

        submitHandler: function (form) {

            update_coupon_info();

        }

    });


    //code-bookmark-js when the freelancer wallet button for sending tax forms is pressed
    $('.email_the_form').click(function () {

        var form_id = $(this).attr('id');

        var data = {'action': 'email_the_form', 'form_id': form_id};

        $.ajax({

            type: 'POST',

            url: adminAjax.url,

            data: data,

            success: function (response) {

                if (jQuery.trim(response) == 'success') {

                    jQuery("#alert_message_model").removeClass().addClass('alert alert-success').html(reg_validation.email_sent_successfully);

                } else {

                    jQuery("#alert_message_model").removeClass().addClass('alert alert-success').html('<strong>Error!(a)</strong> Failed.');

                }

            }

        });


    });

    //code-bookmark-js when the freelancer wallet button Request for withdrawal form is submitted
    $("#requestWithdraw").validate({

        ignore: [],

        rules: {

            amount: {required: true, number: true,},

            request_payment_notify: {required: true},

            //withdrawal_message:{required:true}

        },

        messages: {

            amount: {required: reg_validation.please_enter_amount, number: reg_validation.please_enter_correct_amount},

            request_payment_notify: {required: reg_validation.select_payment_method},

            //withdrawal_message:{required:reg_validation.enter_withdrawal_message}

        },

        submitHandler: function (form) {

            requestWithdraw_info();

        }

    });


    //code-bookmark-js when the new project button at the top is clicked for the customer
    $('#add_new_job').click(function () {

        var data = {'action': 'create_new_job', 'lang': $(this).attr('name')};

        $.ajax({

            type: 'POST',

            url: adminAjax.url,

            data: data,

            global: false,

            success: function (response_raw) {



                var response = JSON.parse(response_raw);

                window.location = response.url;

            }

        });

    });


    //code-bookmark-js when the customer's second edit page for a contest has its text changed
    $("#order_page_project_description").keyup('change', function () {

        var project_description = $(this).val();

        var data = {'action': 'order_page_project_description', 'project_description': project_description};

        $.ajax({

            type: 'POST',

            url: adminAjax.url,

            data: data,

            global: false,

            success: function (response) {


            }

        });

    });


    //code-bookmark-js when the freelancer's wallet does an Upload Signed Tax Form
    $("#uploadSignedTaxForm").bind('change', function () {


        //console.log(this.files[0]);

        var fd = new FormData();

        var files_data = $('.wallet-wraper .signedfilesdata');

        $.each($(files_data), function (i, obj) {

            $.each(obj.files, function (j, file) {

                fd.append('files[' + j + ']', file);

            })

        });

        fd.append('action', 'uploadSignedTaxForm');

        fd.append('lang', reg_validation.dev_language);

        $.ajax({

            type: 'POST',
            url: adminAjax.url,
            data: fd,
            contentType: false,
            processData: false,
            cache: false,

            success: function (response_raw) {

                /**
                 * @type {FreelinguistBasicAjaxResponse} response
                 */
                let response = freelinguist_safe_cast_to_object(response_raw);
                if (response.status) {
                    bootbox.alert("Tax form is uploaded successfully");

                    window.location.reload(true);
                } else {
                    //code-notes show error
                    will_handle_ajax_error('Uploading Tax Form',response.message);
                    bootbox.alert(response.message);
                }
            } //end success function
        });//end ajax
    }); //end #uploadSignedTaxForm on change


    //code-bookmark-js when the header's new project button is clicked for the customer
    jQuery(document).on('click', '.redirect_to_order_page', function () {
        window.location.href = 'order-process';
    });


    //code-bookmark-js when the customer's second contest edit page is uploading files
    jQuery(document).on('change', '#atc_files_order', function () {
        $('#progress .progress-bar').css('width', '0%');
    });


//code-bookmark-js called when files are uploaded during the project edit page
    $(document).on('click', '#project_job_file_upload', function () {

        'use strict';

        var project_id = jQuery(this).data('id');

        var percent = $('.percent');

        var data = jQuery('#htrans-process').serializeArray();

        data.push({'name': 'action', 'value': 'project_job_file_upload'});

        data.push({'name': 'project_id', 'value': project_id});

        $(this).fileupload({

            url: adminAjax.url,

            formData: data,

            dataType: 'json',

            global: false,

            add: function (e, data) {

                var file_Size_limit = 1024 * 1024 * 50;

                if (data.files[0].size > file_Size_limit) {

                    bootbox.alert(data.files[0].name + " File size is greater than 50MB");

                } else {

                    data.submit();

                }

            },

            send: function (e, data) {

            },

            fail: function (e, data) {
                let maybe_information = data.response().jqXHR.responseJSON;

                if (typeof maybe_information === 'object' && maybe_information !== null) {
                    will_handle_ajax_error('Problem Uploading File',maybe_information.message);
                }

                $("#progress").css('display', 'none');

            },

            done: function (e, data) {

                $('#atc_files_order').attr('disabled', false);
                if (data.result != null) {

                    $.each(data.result.files, function (index, file) {


                        if (file.error !== undefined) {

                            bootbox.alert(file.name + ' ' + file.error);

                        }

                        $('#progress').css('display', 'none');

                        $('.percent').css('display', 'none');

                        window.location.reload(true);

                    });

                } else {

                    $("#progress").css('display', 'none');

                    $('#atc_files_order').attr('disabled', false);

                    bootbox.alert(reg_validation.general_file_upload_error_message);

                }

            },

            progressall: function (e, data) {

                $('#progress').css('display', 'block');

                var progress = parseInt(data.loaded / data.total * 100, 10);

                $('#atc_files_order').attr('disabled', false);

                $('#progress .progress-bar').css(
                    'width',

                    progress + '%'
                );

                percent.html(progress + '%');

            }

        }).prop('disabled', !$.support.fileInput)

            .parent().addClass($.support.fileInput ? undefined : 'disabled');

    });


//code-bookmark-js when the customer uploads files for a job
    $(document).on('click', '#project_single_job_file_upload', function () {

        'use strict';

        var project_id = jQuery(this).data('id');

        var job_id = jQuery(this).data('name');

        var percent = $('.percent');

        var data = jQuery('#htrans-process').serializeArray();

        data.push({'name': 'action', 'value': 'project_single_job_file_upload'});

        data.push({'name': 'job_id', 'value': job_id});

        data.push({'name': 'project_id', 'value': project_id});

        //code-bookmark this is the handler for the file upload on the customer single jobs page and calls project_single_job_file_upload

        $(this).fileupload({

            url: adminAjax.url,

            formData: data,

            dataType: 'json',

            global: false,

            add: function (e, data) {

                var file_Size_limit = 1024 * 1024 * 50;

                if (data.files[0].size > file_Size_limit) {

                    bootbox.alert(data.files[0].name + " File size is greater than 50MB");

                } else {

                    data.submit();

                }

            },

            send: function (e, data) {

            },

            fail: function (e, data) {
                let maybe_information = data.response().jqXHR.responseJSON;

                if (typeof maybe_information === 'object' && maybe_information !== null) {
                    will_handle_ajax_error('Problem Uploading File',maybe_information.message);
                }

                $("#progress").css('display', 'none');

            },

            done: function (e, data) {

                $('#atc_files_order').attr('disabled', false);

                if (data.result != null) {

                    $.each(data.result.files, function (index, file) {


                        if (file.error !== undefined) {

                            bootbox.alert(file.name + ' ' + file.error);

                        }

                        $('#progress').css('display', 'none');

                        $('.percent').css('display', 'none');

                        window.location.reload(true);

                    });

                } else {

                    $("#progress").css('display', 'none');

                    $('#atc_files_order').attr('disabled', false);

                    bootbox.alert(reg_validation.general_file_upload_error_message);

                }

            },

            progressall: function (e, data) {

                $('#progress').css('display', 'block');

                var progress = parseInt(data.loaded / data.total * 100, 10);

                $('#atc_files_order').attr('disabled', false);

                $('#progress .progress-bar').css(
                    'width',

                    progress + '%'
                );

                percent.html(progress + '%');

            }

        }).prop('disabled', !$.support.fileInput)

            .parent().addClass($.support.fileInput ? undefined : 'disabled');

    });


//code-bookmark-js when both the freelancer and customer are promted for a profile image and they use that form to upload one
    jQuery('#profilepicreminder_button').click(function () {
        var form = new FormData(jQuery("#profilepicreminder_form")[0]);

        jQuery.ajax({

            type: 'POST',

            url: adminAjax.url,

            data: form,

            contentType: false,

            processData: false,

            cache: false,

            success: function (response_raw) {

                /**
                 * @type {FreelinguistBasicAjaxResponse} response
                 */
                let response = freelinguist_safe_cast_to_object(response_raw);
                if (response.status === true) {
                    window.location.reload(true);
                } else {
                    will_handle_ajax_error('Uploading Profile Image', response.message);
                }


            }

        });
    });

//code-bookmark-js when both the freelancer and customer change their profile image the regular way
    $("#user_image").bind('change', function () {

        //console.log(this.files[0].name);

        var fileType = this.files[0].type;


        var filesize = this.files[0].size;

        var fname = this.files[0].name;

        //console.log(this.files[0]);

        var fd = new FormData();

        var files_data = $('.user_image_btn');

        $.each($(files_data), function (i, obj) {

            $.each(obj.files, function (j, file) {

                fd.append('files', file);

            })

        });


        fd.append('action', 'user_image_file');

        $.ajax({

            type: 'POST',

            url: adminAjax.url,

            data: fd,

            contentType: false,

            processData: false,

            cache: false,

            success: function (response_raw) {

                /**
                 * @type {FreelinguistBasicAjaxResponse} response
                 */
                let response = freelinguist_safe_cast_to_object(response_raw);
                if (response.status === true) {
                    window.location.reload(true);
                } else {
                    will_handle_ajax_error('Uploading Profile Image', response.message);
                }


            }

        });

    });


//code-bookmark-js when both the freelancer and customer change their address in the settings page form
    $("#address_details").validate({

        ignore: [],

        rules: {

            full_name: {required: true, maxlength: 50},

            address_line_1: {required: true, maxlength: 50},

            town_city: {required: true, maxlength: 50},

            state: {required: true, maxlength: 50},

            zip_postal_code: {

                number: true, required: true, maxlength: 8

            },

            country: "required",

            telephone_number: {

                number: true, minlength: 9

            }

        },

        messages: {

            full_name: {

                required: reg_validation.full_name_enter,

                maxlength: reg_validation.char_50_enter,

            },

            address_line_1: {

                required: reg_validation.address_line_1_enter,

                maxlength: reg_validation.char_50_enter,

            },

            town_city: {

                required: reg_validation.town_city_enter,

                maxlength: reg_validation.char_50_enter,

            },

            state: {

                maxlength: reg_validation.char_50_enter,

                required: reg_validation.state_enter,

            },

            zip_postal_code: {

                number: reg_validation.zip_code_enter,

                required: reg_validation.zip_code_enter,

                maxlength: reg_validation.char_8_enter,

            },

            country: reg_validation.country_enter,

            telephone_number: {

                number: reg_validation.telephone_number_enter,

                maxlength: reg_validation.char_9_enter,

            }

        },

        submitHandler: function (form) {

            update_address_details();

        }

    });


//code-bookmark-js when both the freelancer and customer change their payment accounts (paypal, alipay,other) in the settings page form
    $("#accountForm").validate({

        ignore: [],

        rules: {

            paypal_account: {

                maxlength: 50

            },

            alipay_account: {

                maxlength: 50

            }

        },

        messages: {

            paypal_account: {

                maxlength: reg_validation.correct_digit

            },

            alipay_account: {

                maxlength: reg_validation.correct_digit

            }

        },

        submitHandler: function (form) {

            update_payment_mehod_account_detail();

        }

    });


//code-bookmark-js when both the freelancer and customer change their display name in the settings page form
    $("#display_name_form").validate({

        ignore: [],

        rules: {

            display_name: {required: true},

        },

        messages: {

            display_name: {

                required: reg_validation.profile_name_valid

            },

        },

        submitHandler: function (form) {

            update_display_name();

        }

    });


//code-bookmark-js when both the freelancer and customer change their email in the settings page form
    $("#email_change_form").validate({

        ignore: [],

        rules: {

            new_email: {

                required: true,

                email: true,

            },

            confirm_new_email: {

                equalTo: '#new_email'

            }

        },

        messages: {

            new_email: {

                required: reg_validation.please_enter_email_id,

                email: reg_validation.please_enter_correctemail_id

            },

            confirm_new_email: reg_validation.please_enter_correct_conform_email_id,

        },

        submitHandler: function (form) {

            update_user_email();

        }

    });

//code-bookmark-js when both the freelancer and customer change their password in the settings page form
    $("#password_change_form").validate({

        ignore: [],

        rules: {

            old_password: "required",

            password:

                {

                    required: true,
                    noSpace: true,

                    minlength: 5,

                    maxlength: 30,
                    normalizer: function (value) {
                        //code-notes trim password
                        // see https://github.com/jquery-validation/jquery-validation/issues/1886
                        return $.trim(value);
                    }

                },

            confirm_password: {

                equalTo: '#password'

            }

        },

        messages: {

            old_password: reg_validation.please_enter_your_password,

            password: {

                required: reg_validation.please_enter_new_password

            },

            confirm_password: reg_validation.please_enter_same_password,

        },

        submitHandler: function (form) {

            update_user_password_change();

        }

    });

//code-bookmark when pressing forgot password on the forgot password page
    $("#freeling_reset_password").validate({

        ignore: [],

        rules: {

            password:

                {

                    required: true,

                    minlength: 5,

                    maxlength: 30,

                    noSpace: true,

                    normalizer: function (value) {
                        //code-notes trim password
                        // see https://github.com/jquery-validation/jquery-validation/issues/1886
                        return $.trim(value);
                    }

                },

            confirm_password: {

                equalTo: '#password'

            }

        },

        messages: {

            password: {

                required: reg_validation.please_enter_new_password

            },

            confirm_password: reg_validation.please_enter_same_password,

        },

        submitHandler: function (form) {

            freeling_reset_user_password();

        }

    });

//code-bookmark-js when changing the payment preference in the settings page form
    $("#payment_preference_form").validate({

        ignore: [],

        rules: {

            payment_notify: "required",

        },

        messages: {

            payment_notify: reg_validation.default_payment_enter,

        },

        submitHandler: function (form) {

            update_payment_preference();

        }

    });

//code-bookmark-js when changing the withdraw preference in the settings page form
    $("#withdraw_preference_form").validate({

        ignore: [],

        rules: {

            withdraw_pref: "required",

        },

        messages: {

            withdraw_pref: {

                required: reg_validation.default_payment_enter,

            }

        },

        submitHandler: function (form) {

            update_withdraw_preference();

        }

    });


//code-bookmark-js when changing the the freelancer's email notification rate in the settings page form
    //code-notes this is hidden now so will not be used
    $("#email_preference_form").validate({

        ignore: [],

        rules: {

            email_notify: "required",

        },

        messages: {

            email_notify: "Please select any one",

        },

        submitHandler: function (form) {

            update_email_preference();

        }

    });


});


/*

 * Author Name: Lakhvinder Singh

 * Method: 		remove_selected_file

 * Description: remove_selected_file

 *

 */

//code-bookmark-js when removing contest files (for all participants) by the customer on the second contest edit page
function remove_selected_file(e, attachid) {

    var data = {'action': 'selected_file_remove', 'attach_id': attachid};

    jQuery.post(adminAjax.url, data, function (response) {		//alert(response);

        jQuery('#order_files_content').load(window.location.href + ' #order_files_content', function (response, status, xhr) {

            if (status == "error") {

                var msg = "Sorry but there was an error: ";

                bootbox.alert(msg + xhr.status + " " + xhr.statusText);

            }

        });

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		remove_selected

 * Description: remove action to remove upload file on the order process page

 *

 */

//code-bookmark-js when removing files for projects and contests on different pages
function single_remove_selected(e, attach_id) {

    //e.preventDefault();

    var data = {'action': 'selected_file_remove', 'attach_id': attach_id};

    jQuery.post(adminAjax.url, data, function (response) {

        jQuery(e).closest('li').remove(); //code-notes this is used on pages with different dom structures

        var count = jQuery('.document-row li').length;

        jQuery('#count').text(count);

    });

}

//code-bookmark-js when removing project wide files for for the customer
function single_remove_selected_new(e, attach_id) {

    //e.preventDefault();

    var data = {'action': 'selected_file_remove', 'attach_id': attach_id};

    jQuery.post(adminAjax.url, data, function (response) {

        jQuery(e).parent().parent().parent().remove();

        var count = jQuery('.document-row li').length;

        jQuery('#count').text(count);

    });

}

//code-bookmark-js when removing freelancer winning contest files
function single_remove_selected_contest_file_handler(e, attach_id) {

    //e.preventDefault();

    var data = {'action': 'selected_file_remove', 'attach_id': attach_id};

    jQuery.post(adminAjax.url, data, function (response) {

        jQuery(e).parent().parent().parent().parent().remove();

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		remove_all_files

 * Description: On order process page remove all files

 *

 */

//code-bookmark-js when the 'Remove All Items' is clicked in the contest edit and new job forms
function remove_all_files(message_confirm, yes, no) {

    bootbox.confirm({

        message: 'Remove All files?',

        buttons: {

            confirm: {

                label: yes,

                className: 'btn-success'

            },

            cancel: {

                label: no,

                className: 'btn-danger'

            }

        },

        callback: function (result) {
            if (result === true) {

                var data = {'action': 'remove_all_files'};

                jQuery.post(adminAjax.url, data, function (response_from_server) {
                    /**
                     * @type {RemoveAllFilesAjaxResponse} response
                     */
                    let response = freelinguist_safe_cast_to_object(response_from_server);
                    for (let i = 0; i < response.removed_attachment_ids.length; i++) {
                        let removed_id = response.removed_attachment_ids[i];
                        // a[data-attach_id="58"]
                        let my_anchor = jQuery('.document-row li a[data-attach_id="' + removed_id + '"]');
                        let my_li = my_anchor.closest('li');
                        my_li.remove();
                    }
                    //window.location.reload(true);
                    let nu_count = jQuery('.document-row li').length;
                    jQuery('#count').html(nu_count + '');

                });
            }

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		check_second_order_process

 * Description: On order process page second lorder process handler

 *

 */

//code-bookmark-js on the admin evaluation page when the administrator is removing a resume attachement
function delete_resume_attachment(e, attach_id) {


    var data = {'action': 'delete_resume_attachment', 'attach_id': attach_id};

    jQuery.post(adminAjax.url, data, function (response_raw) {

        /**
         * @type {FreelinguistBasicAjaxResponse} response
         */
        let response = freelinguist_safe_cast_to_object(response_raw);
        if (response.status === true) {
            jQuery(e).parent().parent(".box").remove();
        } else {
            will_handle_ajax_error('Delete Resume Attachment', response.message);
        }


    });


}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		check_second_order_process

 * Description: On order process page second lorder process handler

 *

 */

//code-bookmark-js when the translator is deleting a profile image
function delete_profile_image(message_confirm, yes, no) {

    bootbox.confirm({

        message: message_confirm,

        buttons: {

            confirm: {

                label: yes,

                className: 'btn-success'

            },

            cancel: {

                label: no,

                className: 'btn-danger'

            }

        },

        callback: function (result) {


            if (result == true) {

                var data = {'action': 'delete_user_profile_image'};

                jQuery.post(adminAjax.url, data, function (response) {

                    if (jQuery.trim(response) == 'success') {

                        window.location.reload(true);

                    } else {

                        bootbox.alert('unauthorized user.');

                    }

                });


            }


        }

    });

}


//code-notes modified the ajax response to handle standard data
//code-bookmark-js when the customer wants to hide the contest or project and presses the button
function hide_publish_job(job_id, message_confirm, yes, no) {

    var data = {'action': 'hide_job', 'job_id': job_id};

    bootbox.confirm({

        message: message_confirm,

        buttons: {

            confirm: {

                label: yes,

                className: 'btn-success'

            },

            cancel: {

                label: no,

                className: 'btn-danger'

            }

        },

        callback: function (result) {


            if (result === true) {

                jQuery.post(adminAjax.url, data, function (response_raw) {

                    /**
                     * @type {FreelinguistBasicAjaxResponse} response
                     */
                    let response = freelinguist_safe_cast_to_object(response_raw);
                    if (response.status === true) {
                        window.location.reload();
                    } else {
                        will_handle_ajax_error('Hide Job', response.message);
                    }

                });


            }

        }

    });

}

//code-notes modified the ajax to handle standard return data
//code-bookmark-js when the show button is pressed for a contest or project
function show_publish_job(job_id, message_confirm, yes, no) {

    var data = {'action': 'show_job', 'job_id': job_id};

    bootbox.confirm({

        message: message_confirm,

        buttons: {

            confirm: {

                label: yes,

                className: 'btn-success'

            },

            cancel: {

                label: no,

                className: 'btn-danger'

            }

        },

        callback: function (result) {

            if (result === true) {

                jQuery.post(adminAjax.url, data, function (response_raw) {

                    /**
                     * @type {FreelinguistBasicAjaxResponse} response
                     */
                    let response = freelinguist_safe_cast_to_object(response_raw);
                    if (response.status === true) {
                        window.location.reload(true);
                    } else {
                        will_handle_ajax_error('Showing Job', response.message);
                    }


                });


            }

        }

    });

}

//code-bookmark js called after the delete button press for the contest or project
function delete_publish_job(job_id, message_confirm, yes, no) {

    var data = {'action': 'delete_job', 'job_id': job_id};

    bootbox.confirm({

        message: message_confirm,

        buttons: {

            confirm: {

                label: yes,

                className: 'btn-success'

            },

            cancel: {

                label: no,

                className: 'btn-danger'

            }

        },

        callback: function (result) {


            if (result == true) {

                jQuery.post(adminAjax.url, data, function (response) {

                    //if(jQuery.trim(response) == 'success'){

                    bootbox.alert('Project Deleted Successfully');

                    window.location.href = '/dashboard';

                    //}else{

                    //bootbox.alert('Project Deleted Successfully');

                    //}

                });


            }

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		userRegistraton

 * Description: userRegistraton

 *

 */

//code-bookmark-js called by the user entry registration code
function userRegistraton() {

    var data = {'action': 'reg_user', 'data': jQuery("#linguist-registration").serialize()};

    //code-notes add in immediate message after user clicks  button to register
    jQuery("#success_message").addClass("alert alert-info").html(reg_validation.pre_registering);
    jQuery.post(adminAjax.url, data, function (response) {



        var values = JSON.parse(response);

        //alert(values.msg);

        if (values.msg == 'success') {

            jQuery("#success_message").removeClass('alert-info').addClass("alert alert-success").html(reg_validation.successfully_register);

            jQuery("#linguist-registration")[0].reset();

            setTimeout(function () {

                window.location = values.redirect_to;

            }, 200);

        } else if (values.msg == 'already_exist') {

            //alert('Email id already exist.');

            jQuery("#success_message").addClass("alert alert-success").html(reg_validation.email_already_exist);

            jQuery("#linguist-registration")[0].reset();

        } else {

            bootbox.alert('Please use another email address.');

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		resendConfirmationEmail

 * Description: resendConfirmationEmail

 *

 */

//code-bookmark-js when on the login page, and asking for the email with the verification key to be sent again
function resendConfirmationEmail(email) {


    var data = {'action': 'newUserResendConfirmationEmail', 'email': email};

    //code-notes show spinner here
    $('span.fl-resend-email-success-action').hide();
    $('span.fl-resend-email-error-action').hide();
    $('span.fl-resend-email-doing-work').show();


    jQuery.post(adminAjax.url, data, function (response) {

        //code-notes turn off spinner here
        $('span.fl-resend-email-doing-work').hide();

        if (jQuery.trim(response) === 'success') {
            $('span.fl-resend-email-success-action').show();
            bootbox.alert(reg_validation.pleasechk_email);

        } else {
            $('span.fl-resend-email-error-action').show();
            bootbox.alert("unauthorized user");

        }



    });
    //code-notes add an error handler to turn off spinner if error

    return false;

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		submit_home_trans

 * Description: submit_home_trans

 *

 */

//code-bookmark-js called from homepage when creating a project on the fly, when pressing the 'post my project'
function submit_home_trans(e, thisform) {

    var project_description = jQuery('textarea#project_description').val();

    var project_title = jQuery('#project_title').val();

    jQuery(thisform).find('.next-btn').attr("disabled", "disabled");

    var data = {
        'action': 'check_cart_empty_or_not',
        'project_description': project_description,
        'project_title': project_title
    };

    jQuery.post(adminAjax.url, data, function (response) {


    }).done(function () {
        thisform.submit(); //code-notes will post to the new project form
    });

    return false;


}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		download_job_file

 * Description: download job file

 *

 */

//code-bookmark-js used in the evaluation page to download attachments, the admin edit page for posts, and a temporary link when the freelancer uploads a file on the project page
function download_job_file(attach_id) {

    if (attach_id == '') {

        bootbox.alert("Please contact Adminstrater");

    } else {

        var data = {'action': 'download_job_file', 'attach_id': attach_id};


        jQuery.post(adminAjax.url, data, function (response) {



        });


    }

}


var hire_button_flag = false;

//code-bookmark-js when the customer hires a linguist from his profile page or hire  button
function hire_linguist() {


    function callback() {
        if (hire_button_flag === true) return;
        hire_button_flag = true;
        var decimal = '/^[-+]?[0-9]+\.[0-9]/';
        var dateformat = '([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))';

        var standard_delivery = jQuery('input[name=standard_delivery]').val();

        var lang = jQuery('input[name=lang]').val();

        var description = jQuery('#hire_linguist_description').val();
        var linguist_id = jQuery('input[name=linguist_id]').val();
        var estimated_budgets = jQuery('#estimated_budgets').val();
        var delivery_date = jQuery('#delivery_date').val();
        var user = jQuery('input[name=user]').val();


        if (!linguist_id) {
            bootbox.alert("Please select freelancer first");
            return false;
        } else if (!description) {
            bootbox.alert("Please select description");
            return false;
        } else if (!estimated_budgets) {
            bootbox.alert("Please enter valid budget");
            return false;
        } else if (!delivery_date) {
            bootbox.alert("Please enter valid date");
            return false;
        } else if (parseFloat(estimated_budgets) < 5) {
            bootbox.alert("Your minimize budget should be $5.");
            return false;
        }


        /* if( delivery_date.match(dateformat)){

        }else{
            bootbox.alert("Please enter valid date");
            return false;
        } */

        var data = jQuery("#hire_linguist_by_customer").serializeArray();
        data.push({name: "action", value: "hirelinguistByCustomer"});
        data.push({name: "estimated_budgets", value: estimated_budgets});
        data.push({name: "project_description", value: description});
        data.push({name: "linguist_id", value: linguist_id});
        data.push({name: "user", value: user});
        data.push({name: "delivery_date", value: delivery_date});


        //return false;
        jQuery.ajax({
            url: adminAjax.url,
            type: 'POST',
            data: data,
            beforeSend: function (xhr) {
                jQuery('button[name=submit_order]').prop('disabled', true);

            },
            success: function (response) {
                hire_button_flag = false;
                jQuery('button[name=submit_order]').prop('disabled', true);
                var values = JSON.parse(response);

                if (values.result == 'success') {
                    jQuery('button[name=submit_hire]').prop('disabled', true);
                    window.location = values.url;

                } else if (values.result == 'job_id_not_exist') {
                    jQuery('button[name=submit_hire]').prop('disabled', false);

                    bootbox.alert(values.alert_message + ".");

                    setTimeout(function () {

                        window.location = values.url;

                    }, 500);

                } else if (values.result == 'required') {
                    jQuery('button[name=submit_hire]').prop('disabled', false);

                    bootbox.alert(values.alert_message + ".");

                } else {

                    bootbox.alert("USER Must me loggedin. To place the order");

                }
            }
        });
    }

    freelinguist_show_fee_box('customer_hiring_linguist', null, null, null, callback);
}


//code-bookmark-js when a new project or contest is created for the first time
function generateOrderByCustomerNew(e, thisform) {

    //code-notes check for tags, ,and if none return false after showing message
    let da_tags = jQuery("input[name='hidden-project_tags']").val();
    if (!da_tags) {
        let da_job_type = jQuery('#fl-project-type').val();
        bootbox.alert("Please include at least one skill for this " + da_job_type);
        return false;
    }

    if (e === 0) {


        var url = jQuery('.login-btn-n').attr('href');

        window.onload = function () {

            if (!localStorage.justOnce) {

                localStorage.setItem("justOnce", "true");

                window.location.reload();

            }

        };

        var lang_is = getParameterByName('lang', url);

        if (lang_is == null) {

            window.location = url + '?redirect_to_order_process=true';

        } else {

            window.location = url + '&redirect_to_order_process=true';

        }

        //url = siteUrl

    } else {

        var standard_delivery = jQuery('input[name=standard_delivery]').val();

        var lang = jQuery('input[name=lang]').val();

        var project_title = jQuery('input[name=project_title]').val();


        if (jQuery.trim(project_title) === '') {
            jQuery('.projectTitleError').html('<label class="error" for="project_title">Please input project title</label>');
            return false;
        }

        var project_description = jQuery('textarea[name=project_description]').val();
        if (jQuery.trim(project_description) === '') {
            jQuery('.projectDescriptionError').html('<label class="error" for="project_title">Please input project description</label>');
            return false;
        }

        var tags = jQuery('input[name=hidden-project_tags]').val();


        var estimated_budgets = jQuery('#estimated_budgets').val();

        var job_type = jQuery('#fl-project-type').val();
        var temp_id = jQuery('#temp_id').val();

        var is_guaranted = (jQuery("#is_guaranted").prop('checked')) ? 1 : 0;


        var already_ins = jQuery('#already_ins').val();

        var fValidSuccess = true;
        var fFocused = false;

        if (standard_delivery = '') {

            jQuery("#std_del").html(required_valid.required_validation);

        } else {


            standard_delivery = jQuery('input[name=standard_delivery]').val();
            var data;
            if (lang === '') {

                data = {
                    'action': 'generateOrderByCustomerNew',
                    'already_ins': already_ins,
                    'estimated_budgets': estimated_budgets,
                    'project_title': project_title,
                    'project_description': project_description,
                    'tags': tags,
                    'standard_delivery': standard_delivery,
                    'job_type': job_type,
                    'is_guaranted': is_guaranted,
                    'temp_id': temp_id
                };

            } else {

                data = {
                    'action': 'generateOrderByCustomerNew',
                    'already_ins': already_ins,
                    'lang': lang,
                    'estimated_budgets': estimated_budgets,
                    'project_title': project_title,
                    'project_description': project_description,
                    'tags': tags,
                    'standard_delivery': standard_delivery,
                    'job_type': job_type,
                    'is_guaranted': is_guaranted,
                    'temp_id': temp_id
                };

            }

            // project title check
            if (project_title === '') {
                jQuery('.projectTitleError').html('<label class="error" for="project_title">Please input project title</label>');
                if (!fFocused)
                    jQuery("#order_page_project_title--").focus();
                fValidSuccess = false;
                fFocused = true;
            }

            // project description check
            if (project_description === '') {
                jQuery('.projectDescriptionError').html('<label class="error" for="project_description">Please input project description</label>');
                if (!fFocused)
                    jQuery("#order_page_project_description--").focus();
                fValidSuccess = false;
                fFocused = true;
            }

            if (!fValidSuccess) return false;


            jQuery.ajax({
                url: adminAjax.url,
                type: 'POST',
                data: data,
                beforeSend: function (xhr) {
                    jQuery('button[name=submit_order]').prop('disabled', true);

                },
                success: function (response) {
                    jQuery('button[name=submit_order]').prop('disabled', true);
                    var values = JSON.parse(response);

                    if (values.result == 'success') {
                        jQuery('button[name=submit_order]').prop('disabled', true);
                        window.location = values.url;

                    } else if (values.result == 'job_id_not_exist') {
                        jQuery('button[name=submit_order]').prop('disabled', false);

                        bootbox.alert(values.alert_message + ".");

                        setTimeout(function () {

                            window.location = values.url;

                        }, 500);

                    } else if (values.result == 'required') {
                        jQuery('button[name=submit_order]').prop('disabled', false);

                        bootbox.alert(values.alert_message + ".");

                    } else {

                        bootbox.alert("USER Must me loggedin. To place the order");

                    }
                }
            });


        }

    }

    return false;

}

/*

 * Author Name: Aarvik Infotech

 * Method: 		updateOrderByCustomer

 * Description: call the  updateOrderByCustomer action

 *

 */

//code-notes js for updating the customer contest
//code-bookmark-js This is run when the customer presses 'update competition' button
function updateOrderByCustomer(e) {


    if (e === 0) {

        var url = jQuery('.login-btn-n').attr('href');

        window.onload = function () {

            if (!localStorage.justOnce) {

                localStorage.setItem("justOnce", "true");

                window.location.reload();

            }

        };

        window.location = url + '?redirect_to_order_process=true';


    } else {


        var standard_delivery = jQuery('input[name=standard_delivery]').val();

        var lang = jQuery('input[name=lang]').val();

        var project_id = jQuery('input[name=project_id]').val();

        var project_title = jQuery('input[name=project_title]').val();

        var project_description = jQuery('textarea[name=project_description]').val();

        var tags = jQuery('input[name=hidden-project_tags]').val();


        var estimated_budgets = jQuery('#estimated_budgets').val();


        var temp_id = jQuery('#temp_id').val();

        var is_guaranted = (jQuery("#is_guaranted").prop('checked') === true) ? 1 : 0;


        var already_ins = jQuery('#already_ins').val();


        if (standard_delivery === '') {

            jQuery("#std_del").html(required_valid.required_validation);

        } else {


            standard_delivery = jQuery('input[name=standard_delivery]').val();

            var data;
            if (!lang) {

                data = {
                    'action': 'updateOrderByCustomer',
                    'already_ins': already_ins,
                    'estimated_budgets': estimated_budgets,

                    'project_title': project_title,
                    'project_description': project_description,
                    'tags': tags,
                    'standard_delivery': standard_delivery,
                    'is_guaranted': is_guaranted,
                    'project_id': project_id,
                    'temp_id': temp_id
                };

            } else {

                data = {
                    'action': 'updateOrderByCustomer',
                    'already_ins': already_ins,
                    'lang': lang,
                    'estimated_budgets': estimated_budgets,

                    'project_title': project_title,
                    'project_description': project_description,
                    'tags': tags,
                    'standard_delivery': standard_delivery,
                    'is_guaranted': is_guaranted,
                    'project_id': project_id,
                    'temp_id': temp_id
                };

            }

            jQuery.post(adminAjax.url, data, function (response_raw) {

                /**
                 * @type {FreelinguistLinkResponse} response
                 */
                var response = freelinguist_safe_cast_to_object(response_raw);
                if (response.status === true) {
                    window.location = response.url;
                } else {
                    will_handle_ajax_error('Updating Contest', response.message);
                    bootbox.alert(response.message + ".");
                    if (response.url) {
                        setTimeout(function () {
                            window.location = values.url;
                        }, 500);
                    }
                }

            });


        }

    }

    return false;

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		ajaxindicatorstart

 * Description: Ajax procedd indicator

 *

 */

//code-bookmark-js called at the start of adding a favorite from the homepage or a freelancer profile
function ajaxindicatorstart(text) {

    var templateUrl = devscript.template_url;
    if (devscript.template.indexOf('order-process') != -1 || devscript.template.indexOf('linguist-add-content') != -1 || devscript.template.indexOf('post-contest') != -1 || devscript.template.indexOf('my-account') != -1) {
        return false;
    }
    var loader_ajax = '<div id="resultLoading" style="display:none"><div>' + '<img src="' + templateUrl + '/images/ajax-loader.gif"><div>' + text + '</div></div><div class="bg"></div></div>';

    //alert(loader_ajax);

    if (jQuery('body').find('#resultLoading').attr('id') != 'resultLoading') {

        jQuery('body').append(loader_ajax);

    }


    jQuery('#resultLoading').css({

        'width': '100%',

        'height': '100%',

        'position': 'fixed',

        'z-index': '10000000',

        'top': '0',

        'left': '0',

        'right': '0',

        'bottom': '0',

        'margin': 'auto'

    });


    jQuery('#resultLoading .bg').css({

        'background': '#000000',

        'opacity': '0.7',

        'width': '100%',

        'height': '100%',

        'position': 'absolute',

        'top': '0'

    });


    jQuery('#resultLoading>div:first').css({

        'width': '250px',

        'height': '75px',

        'text-align': 'center',

        'position': 'fixed',

        'top': '0',

        'left': '0',

        'right': '0',

        'bottom': '0',

        'margin': 'auto',

        'z-index': '10',

        'color': '#ffffff'


    }).addClass('large-text');


    jQuery('#resultLoading .bg').height('100%');

    jQuery('#resultLoading').fadeIn(300);

    jQuery('body').css('cursor', 'wait');

}

//code-bookmark-js called at the end of adding a favorite from the homepage or a freelancer profile
function ajaxindicatorstop() {

    jQuery('#resultLoading .bg').height('100%');

    jQuery('#resultLoading').fadeOut(100);

    jQuery('body').css('cursor', 'default');

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		place_the_bid

 * Description: Place the bid by linguist

 *

 */

//code-bookmark-js called from any page the freelancer will bid on a project
function place_the_bid(thisform) {

    var formData = {};

    jQuery.each(jQuery(thisform).serializeArray(), function (i, field) {

        formData[field.name] = field.value;

    });

    if (formData['comment'] == undefined || formData['comment'] == '') {

        jQuery("#form_error_message").html(reg_validation.note_enter);
        bootbox.alert(reg_validation.note_enter);

        return false;

    } else if (formData['bidPrice'] == undefined || formData['bidPrice'] == '') {
        bootbox.alert("Please enter price");

        return false;
    } else if (formData['bidPrice'] <= 0 || isNaN(formData['bidPrice'])) {
        bootbox.alert("Please enter price greater than 0");

        return false;
    } else {

        var bid_comment = formData['comment'];
        var bidPrice = formData['bidPrice'];

        var data = jQuery(thisform).serializeArray();

        data.push({'name': 'action', 'value': 'place_the_bid'});
        jQuery.post(adminAjax.url, data, function (response_raw) {

            var response = freelinguist_safe_cast_to_object(response_raw);


            if (response.status_code === 'success') {

                if (jQuery('#bid_statement').length !== 0) {

                    jQuery('#bid_statement').html(bid_comment);

                    jQuery('.close').trigger('click');

                }

                if (bidPrice != 0 && bidPrice != '') {
                    jQuery('#bid_price').html('$' + bidPrice);
                }
                jQuery("#alert_message_model").removeClass().addClass('alert alert-success').html(response.message);

                setTimeout(function () {

                    jQuery("#alert_message_model").removeClass().html('');

                    jQuery('.close').trigger('click');

                }, 1500);

                window.location.reload(true);


            } else if (response.status_code == 'update') {

                if (jQuery('#bid_statement').length != 0) {

                    jQuery('#bid_statement').html(bid_comment);
                    if (bidPrice != 0 && bidPrice != '') {
                        jQuery('#bid_price').html('$' + bidPrice);
                    }

                    jQuery('.close').trigger('click');

                }

                jQuery("#alert_message_model").removeClass().addClass('alert alert-success').html(response.message);

                setTimeout(function () {

                    jQuery("#alert_message_model").removeClass().html('');

                    jQuery('.close').trigger('click');

                }, 1500);

            }
            else {

                jQuery("#alert_message_model").removeClass().addClass('alert alert-danger').html(response.message);

                setTimeout(function () {

                    jQuery("#alert_message_model").removeClass().html('');

                    jQuery('.close').trigger('click');


                }, 1500);

            }


        });

        return false;

    }

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		hireTranslate

 * Description: hire translator foem handle by hireTranslate()

 *

 */

//code-bookmark-js called as part of the hiring form validationwhen the customer will hire a bidding freelancer
function hireTranslate() {


    function callback() {
        var data = jQuery("#TranslaterHireForm").serializeArray();

        data.push({'name': 'action', 'value': 'hireTranslate'});

        jQuery('#TranslaterHireButton').attr('disabled', true);

        jQuery.post(adminAjax.url, data, function (response_raw) {

            /**
             * @type {FreelinguistBasicAjaxResponse} response
             */
            let response = freelinguist_safe_cast_to_object(response_raw);
            if (response.status === true) {
                setTimeout(function () {
                    window.location.reload(true);
                }, 10);
            } else {
                will_handle_ajax_error('Job Hire', response.message);
                bootbox.alert(response.message);
                jQuery('#TranslaterHireButton').attr('disabled', 'false');
            }

        });
    }

    freelinguist_show_fee_box('customer_selecting_winning_bid', null, null, null, callback);

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		getParameterByName

 * Description: getParameterByName

 *

 */

//code-bookmark-js a helper function to the creating a new project or job
function getParameterByName(name, url) {

    if (!url) url = window.location.href;

    name = name.replace(/[\[\]]/g, "\\$&");

    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),

        results = regex.exec(url);

    if (!results) return null;

    if (!results[2]) return '';

    return decodeURIComponent(results[2].replace(/\+/g, " "));

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		update_personal_info

 * Description: add update personal info

 *

 */

//code-bookmark-js called by the translator's account profile form handler
function update_personal_info() {

    var data = jQuery('#profiledetail').serializeArray();

    data.push({'name': 'action', 'value': 'update_personal_info_data'});

    //console.log(data);

    jQuery.post(adminAjax.url, data, function (response) {

        if (jQuery.trim(response) == 'success') {

            //alert("you are successfully updated");

            jQuery("#personal_info_message").removeClass().addClass('alert alert-success').html(reg_validation.you_are_successfull_updated);

            window.location.reload(true);

            return true;

        } else {

            jQuery("#personal_info_message").removeClass().addClass('alert alert-warning').html('Unauthorized user.');

            return false;

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		update_personal_info

 * Description: add update personal info

 *

 */

//code-bookmark-js called as part of the customer's account profile form verification
function customerProfiledetail() {

    var data = jQuery('#customerProfiledetail').serializeArray();

    data.push({'name': 'action', 'value': 'update_customer_personal_info_data'});

    //console.log(data);

    jQuery.post(adminAjax.url, data, function (response) {

        if (jQuery.trim(response) == 'success') {

            //alert("you are successfully updated");

            jQuery("#personal_info_message").removeClass().addClass('alert alert-success').html(reg_validation.you_are_successfull_updated);

            window.location.reload(true);

            return true;

        } else {

            jQuery("#personal_info_message").removeClass().addClass('alert alert-warning').html('Unauthorized user.');

            return false;

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		update_summary_info

 * Description: add update summary Info

 *

 */

//code-bookmark-js called as part of the form handle to update summary in profile for freelancer
function update_summary_info() {

    for (instance in CKEDITOR.instances) {
        CKEDITOR.instances[instance].updateElement();
    }

    //code-notes check for tags, ,and if none return false after showing message
    let da_tags = jQuery("input[name='hidden-project_tags']").val();
    if (!da_tags) {
        bootbox.alert("Please include at least one skill");
        return false;
    }
    var data = jQuery('#summaryinformation').serializeArray();

    /* console.log(data);
	return false; */

    data.push({'name': 'action', 'value': 'update_summary_info_data'});

    //console.log(data);

    jQuery.post(adminAjax.url, data, function (response_thing) {

        let response;
        if (typeof response === 'string' || response instanceof String) {
            response = JSON.parse(response_thing);
        } else {
            response = response_thing;
        }
        //
        if (response.success) {

            //alert("you are successfully updated");

            jQuery("#form_success_message").html(reg_validation.you_are_successfull_updated);
            window.location.reload(true);

            return true;

        } else {

            jQuery("#form_error_message").html(response.message);

            return false;

        }

    });

}


/**

 * Author Name: Lakhvinder Singh

 * Method:        update_RequestEvaluation_info

 * Description: add request evaluation

 *

 */
//code-bookmark-js called as part of the process where translator account page requests an evaluation
function update_RequestEvaluation_info() {

    var data = jQuery('#RequestEvaluation').serializeArray();

    data.push({'name': 'action', 'value': 'update_RequestEvaluation_info'});

    //console.log(data);

    jQuery.post(adminAjax.url, data, function (response) {

        if (jQuery.trim(response) == 'success') {

            //alert("you are successfully updated");

            jQuery("#form_success_message").html(reg_validation.you_are_successfull_updated);

            window.location.reload(true);

            return true;

        } else {

            jQuery("#form_error_message").html('Unauthorized user.');

            return false;

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		update_coupon_info

 * Description: update coupon

 *

 */

//code-bookmark-js as part of the process when submitting a coupon in the wallet
function update_coupon_info() {

    var data = jQuery('#couponForm').serializeArray();

    data.push({'name': 'action', 'value': 'update_coupon_info'});

    //console.log(data);

    jQuery.post(adminAjax.url, data, function (response) {

        if (jQuery.trim(response) == 'success') {

            bootbox.alert(reg_validation.you_are_successfull_updated);

            window.location.reload(true);

            return true;

        } else if (jQuery.trim(response) == 'not_available') {

            //alert("This promo code is not available.");

            jQuery("#couponErrors_message").addClass("alert-success").html(reg_validation.promo_code_not_available);

            return false;

        } else if (jQuery.trim(response) == 'already_used') {

            //alert("This promo code is already used.");

            jQuery("#couponErrors_message").addClass("alert-success").html(reg_validation.promo_code_already_used);

            return false;

        } else {

            jQuery("#couponErrors_message").addClass("alert-success").html(reg_validation.unauthorized_user);

            return false;

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		requestWithdraw_info

 * Description: Rquest to withdrawal amount by linguist

 *

 */

//code-bookmark-js called as part of the process when pressing the "Request Withdraw" button
function requestWithdraw_info() {

    var data = jQuery('#requestWithdraw').serializeArray();

    data.push({'name': 'action', 'value': 'requestWithdraw_info'});

    //console.log( data );

    jQuery.post(adminAjax.url, data, function (response) {

        /*alert( response );

		return false;*/

        if (jQuery.trim(response) === 'success') {

            jQuery("#success_error_requestWithdraw").addClass("alert-success").html(reg_validation.request_sent_successfully);

            window.location.reload(true);

            return true;

        } else if (jQuery.trim(response) == 'not_available') {

            //alert("This promo code is not available.");


            console.log("insuficient");
            jQuery("#success_error_requestWithdraw").addClass("alert-warning").html(reg_validation.insufficent_balance_left);

            //jQuery("#success_error_requestWithdraw").hide("slow");
            setTimeout(function () {
                jQuery("#success_error_requestWithdraw").html('');
            }, 3000);

            return false;

        } else if (jQuery.trim(response) == 'pending_job_exist') {

            //alert("This promo code is not available.");

            jQuery("#success_error_requestWithdraw").addClass("alert-warning").html(reg_validation.complete_job_before_withdrawal_request);
            setTimeout(function () {
                jQuery("#success_error_requestWithdraw").html('');
            }, 3000);

            return false;

        } else if (jQuery.trim(response) == 'already_used') {

            //alert("This promo code is already used.");

            jQuery("#success_error_requestWithdraw").addClass("alert-warning").html(reg_validation.you_already_withdrawal);
            setTimeout(function () {
                jQuery("#success_error_requestWithdraw").html('');
            }, 3000);

            return false;

        } else {

            jQuery("#success_error_requestWithdraw").addClass("alert-warning").html(reg_validation.unauthorized_user);
            setTimeout(function () {
                jQuery("#success_error_requestWithdraw").html('');
            }, 3000);

            return false;

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		update_address_details

 * Description: update the address info

 *

 */

//code-bookmark-js called as part of the process when updating the address in the settings
function update_address_details() {

    var data = jQuery('#address_details').serializeArray();

    data.push({'name': 'action', 'value': 'update_address_details'});

    //console.log(data);

    jQuery.post(adminAjax.url, data, function (response) {

        if (jQuery.trim(response) == 'success') {

            //alert("you are successfully updated");

            jQuery("#form_success_message_user_update_address").removeClass().addClass('alert alert-success').html(reg_validation.you_are_successfull_updated);

            setTimeout(function () {

                jQuery("#form_success_message_user_update_address").removeClass().html("");

            }, 5000);

            return false;

        } else {

            jQuery("#form_success_message_user_update_address").removeClass().addClass('alert alert-danger').html('<strong>Error!</strong> Unauthorized user.');

            return false;

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		update_payment_mehod_account_detail

 * Description: update payment mehod account detail

 *

 */

//code-bookmark-js called as part of the process when updating payment settings
function update_payment_mehod_account_detail() {

    var data = jQuery('#accountForm').serializeArray();

    data.push({'name': 'action', 'value': 'update_payment_mehod_account_detail'});

    //console.log(data);

    jQuery.post(adminAjax.url, data, function (response) {

        if (jQuery.trim(response) == 'success') {

            //alert("you are successfully updated");

            jQuery("#form_success_message_user_update_account").removeClass().addClass('alert alert-success').html(reg_validation.you_are_successfull_updated);

            setTimeout(function () {

                jQuery("#form_success_message_user_update_account").removeClass().html("");

            }, 5000);

            return false;

        } else if (jQuery.trim(response) == 'empty_data') {

            jQuery("#form_success_message_user_update_account").removeClass().addClass('alert alert-danger').html(reg_validation.please_enter_proper_account);

            return false;

        } else {

            jQuery("#form_success_message_user_update_account").removeClass().addClass('alert alert-danger').html(reg_validation.unauthorized_user);

            return false;

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		update_display_name

 * Description: add or change the display name

 *

 */

//code-bookmark-js called as part of the process when updating display name in the settings
function update_display_name() {

    var data = jQuery('#display_name_form').serializeArray();

    data.push({'name': 'action', 'value': 'update_display_name'});

    //console.log(data);

    var displyname = jQuery('.disply-name');

    var display_name = jQuery('#display_name').val();

    jQuery.post(adminAjax.url, data, function (response) {

        if (jQuery.trim(response) == 'success') {

            displyname.html(display_name);

            jQuery("#form_success_message_user_display_form").removeClass().addClass('alert alert-success').html(reg_validation.you_are_successfull_updated);

            setTimeout(function () {

                jQuery("#form_success_message_user_display_form").removeClass().html("");

            }, 5000);

            return false;

        } else {

            jQuery("#form_success_message_user_display_form").removeClass().addClass('alert alert-warning').html(reg_validation.unauthorized_user);

            setTimeout(function () {

                jQuery("#form_success_message_user_display_form").removeClass().html("");

            }, 5000);

            return false;

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		update_user_email

 * Description: update the user email

 *

 */

//code-bookmark-js called as part of the process when updating email in the settings
function update_user_email() {

    var data = jQuery('#email_change_form').serializeArray();

    data.push({'name': 'action', 'value': 'update_user_email'});

    //console.log(data);

    jQuery.post(adminAjax.url, data, function (response) {

        if (jQuery.trim(response) == 'success') {

            //alert("you are successfully updated");

            jQuery("#form_success_message_user_email").removeClass().addClass('alert alert-success').html(reg_validation.email_updated);

            setTimeout(function () {

                jQuery("#form_success_message_user_email").removeClass().html("");

            }, 5000);

            return false;

        } else {

            console.log(reg_validation.email_exist_another);

            jQuery("#form_success_message_user_email").removeClass().addClass('alert alert-warning').html(reg_validation.email_exist_another);

            setTimeout(function () {

                jQuery("#form_success_message_user_email").removeClass().html("");

            }, 5000);

            return false;

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		re_active_my_account

 * Description: when user want to delete the account

 *

 */

//code-bookmark-js called when pressing button to delete account in the settings
function delete_my_account(message_confirm, yes, no) {

    var data = {'action': 'delete_my_account'};

    bootbox.confirm({

        message: message_confirm,

        buttons: {

            confirm: {

                label: yes,

                className: 'btn-success'

            },

            cancel: {

                label: no,

                className: 'btn-danger'

            }

        },

        callback: function (result) {

            if (result == true) {

                jQuery.post(adminAjax.url, data, function (response) {

                    if (jQuery.trim(response) == 'success') {

                        window.location.reload(true);

                    } else {

                        bootbox.alert('unauthorized user.');

                    }

                });

            }

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		re_active_my_account

 * Description: re-active the account

 *

 */

//code-bookmark-js called from login page, if account not active
function re_active_my_account(email, message_confirm, yes, no) {

    var data = {'action': 'reactive_my_account', 'email': email};

    bootbox.confirm({

        message: message_confirm,

        buttons: {

            confirm: {

                label: yes,

                className: 'btn-success'

            },

            cancel: {

                label: no,

                className: 'btn-danger'

            }

        },

        callback: function (result) {

            if (result == true) {

                jQuery.post(adminAjax.url, data, function (response) {

                    if (jQuery.trim(response) == 'success') {

                        bootbox.alert('Your account has been re-activate now.');

                        window.location.reload(true);

                    } else {

                        bootbox.alert('unauthorized user.');

                    }

                });


            }

        }

    });


}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		update_user_password_change

 * Description: update_user_password_change

 *

 */

//code-bookmark-js called as part of the process when updating password in the settings
function update_user_password_change() {

    var data = jQuery('#password_change_form').serializeArray();

    data.push({'name': 'action', 'value': 'update_user_password_change'});

    //console.log(data);

    jQuery.post(adminAjax.url, data, function (response) {

        if (jQuery.trim(response) == 'success') {

            //alert("you are successfully updated");

            jQuery("#form_success_message_user_password_form").removeClass().addClass('alert alert-success').html(reg_validation.password_updated);

            setTimeout(function () {

                jQuery("#form_success_message_user_password_form").removeClass().html("");

            }, 5000);

            window.location.reload(true);

            return false;

        } else if (jQuery.trim(response) === 'wrong_old_password') {

            bootbox.alert("Wrong current password");

        } else {

            jQuery("#form_success_message_user_password_form").removeClass().addClass('alert alert-warning').html(reg_validation.field_all);

            setTimeout(function () {

                jQuery("#form_success_message_user_password_form").removeClass().html("");

            }, 5000);

            return false;

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		freeling_reset_user_password

 * Description: freeling_reset_user_password

 *

 */

//code-bookmark-js when pressing forgot password on the on the forgot password page
function freeling_reset_user_password() {

    var data = jQuery('#freeling_reset_password').serializeArray();

    data.push({'name': 'action', 'value': 'freeling_reset_user_password'});

    //console.log(data);

    jQuery.post(adminAjax.url, data, function (response_raw) {

        var response = JSON.parse(response_raw);

        if (jQuery.trim(response.message) == 'success') {

            //alert("you are successfully updated");

            jQuery("#form_success_message_user_password_form").removeClass().addClass('alert alert-success').html(reg_validation.password_updated);

            setTimeout(function () {

                jQuery("#form_success_message_user_password_form").removeClass().html("");

            }, 5000);

            window.location = response.url;

            return false;

        } else {

            jQuery("#form_success_message_user_password_form").removeClass().addClass('alert alert-warning').html(reg_validation.field_all);

            setTimeout(function () {

                jQuery("#form_success_message_user_password_form").removeClass().html("");

            }, 5000);

            return false;

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		update_payment_preference

 * Description: To update the update payment preference

 *

 */

//code-bookmark-js called as part of the process of changing the withdraw preference in the settings page form
function update_payment_preference() {

    var data = jQuery('#payment_preference_form').serializeArray();

    data.push({'name': 'action', 'value': 'update_payment_preference'});

    //console.log(data);

    jQuery.post(adminAjax.url, data, function (response) {

        if (jQuery.trim(response) == 'success') {

            //alert("you are successfully updated");

            //jQuery("#form_success_message_user_payment_pref").removeClass().addClass('alert alert-success').html(reg_validation.you_are_successfull_updated);
            jQuery("#form_success_message_user_payment_pref").removeClass().addClass('alert alert-success').html("Payment method is updated successfully");

            setTimeout(function () {

                jQuery("#form_success_message_user_payment_pref").removeClass().html("");

            }, 5000);

            return false;

        } else {

            jQuery("#form_success_message_user_payment_pref").removeClass().addClass('alert alert-warning').html('<strong>Error!</strong> Unauthorized user');

            return false;

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		update_withdraw_preference

 * Description: To update the update withdraw preference

 *

 */

//code-bookmark-js called as part of the process when updating withdraw preferences in the settings
function update_withdraw_preference() {

    var data = jQuery('#withdraw_preference_form').serializeArray();

    data.push({'name': 'action', 'value': 'update_withdraw_preference'});

    //console.log(data);

    jQuery.post(adminAjax.url, data, function (response) {

        if (jQuery.trim(response) == 'success') {

            //alert("you are successfully updated");

            jQuery("#form_success_message_user_withdraw_pref").removeClass().addClass('alert alert-success').html(reg_validation.you_are_successfull_updated);

            setTimeout(function () {

                jQuery("#form_success_message_user_withdraw_pref").removeClass().html("");

            }, 5000);

            return false;

        } else {

            jQuery("#form_success_message_user_withdraw_pref").removeClass().addClass('alert alert-warning').html('<strong>Error!</strong> Unauthorized user');

            return false;

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		update_email_preference

 * Description: To update the email preference

 *

 */

//code-bookmark-js called as part of changing the the freelancer's email notification rate in the settings page form
function update_email_preference() {

    var data = jQuery('#email_preference_form').serializeArray();

    data.push({'name': 'action', 'value': 'update_email_preference'});

    //console.log(data);

    jQuery.post(adminAjax.url, data, function (response) {

        if (jQuery.trim(response) == 'success') {

            //alert("you are successfully updated");

            jQuery("#form_success_message_user_email_pref").removeClass().addClass('alert alert-success').html(reg_validation.you_are_successfull_updated);

            setTimeout(function () {

                jQuery("#form_success_message_user_email_pref").removeClass().html("");

            }, 5000);

            return false;

        } else {

            jQuery("#form_success_message_user_email_pref").removeClass().addClass('alert alert-warning').html('<strong>Error!</strong> Unauthorized user');

            return false;

        }

    });

}


jQuery(function ($) {

    //code-bookmark-js when the new button is pressed in the freelancer profile for more education
    jQuery("#add_more_education").click(function () {

        var html_val = '<div class="col-md-3"><label for="year_attended">' + reg_validation.linguist_user_info_YEARSATTENDED + '</label><br><input class="form-control" type="text" name="year_attended[]" id="year_attended[]" ><label class="error"></label></div>';

        html_val += '<div class="col-md-4"><label for="institution">' + reg_validation.linguist_user_info_INSTITUTION + '</label><br><input class="form-control" type="text" name="institution[]" id="institution[]" ></div>';

        html_val += '<div class="col-md-4"><label for="degree">' + reg_validation.linguist_user_info_DEGREE + '</label><br><input class="form-control" type="text" name="degree[]" id="degree[]" ><label class="error"></label></div>';

        html_val += '<div class="col-md-1"><label for="degree"> </label><br><a class="delete_education_info glyphicon glyphicon-remove" name="" id="delete_education_info" href="#"></a><label class="error"></label></div>';

        html_val = '<div class="row">' + html_val + '</div>';

        jQuery("#education_mod").append(html_val);

    });

    //code-bookmark-js when the new button is pressed in the freelancer profile for more certification
    jQuery("#add_more_certification").click(function () {

        var html_val = '<div class="col-md-3"><label for="year_recieved">' + reg_validation.linguist_user_info_Year_recieved + '</label><br><input class="form-control" type="text" name="year_recieved[]" id="year_recieved[]" ><label class="error"></label></div>';

        html_val += '<div class="col-md-4"><label for="recieved_from">' + reg_validation.linguist_user_info_Recieved_from + '</label><br><input class="form-control" type="text" name="recieved_from[]" id="recieved_from[]" ><label class="error"></label></div>';

        html_val += '<div class="col-md-4"><label for="certificate">' + reg_validation.linguist_user_info_Certificate + '</label><br><input class="form-control" type="text" name="certificate[]" id="certificate[]" ><label class="error"></label></div>';

        html_val += '<div class="col-md-1"><label for="degree"> </label><br><a class="delete_certificate_info glyphicon glyphicon-remove" name="" id="delete_certificate_info" href="#"></a><label class="error"></label></div>';

        html_val = '<div class="row">' + html_val + '</div>';

        jQuery("#certification_mod").append(html_val);

    });

    //code-bookmark-js when the new button is pressed in the freelancer profile for more related work experience
    jQuery("#add_more_related_work").click(function () {

        var html_val = '<div class="col-md-3"><label for="year_in_service">' + reg_validation.linguist_user_info_Year_in_service + '</label><br><input class="form-control" type="text" name="year_in_service[]" id="year_in_service[]" ><label class="error"></label></div>';

        html_val += '<div class="col-md-4"><label for="employer">' + reg_validation.linguist_user_info_Employer + '</label><br><input class="form-control" type="text" name="employer[]" id="employer[]" ><label class="error"></label></div>';

        html_val += '<div class="col-md-4"><label for="duties">' + reg_validation.linguist_user_info_Duties + '</label><br><input class="form-control" type="text" name="duties[]" id="duties[]" ><label class="error"></label></div>';

        html_val += '<div class="col-md-1"><label for="degree"> </label><br><a class="delete_related_work_experience glyphicon glyphicon-remove" name="" id="delete_related_work_experience" href="#"></a></div>';

        html_val = '<div class="row">' + html_val + '</div>';

        jQuery("#related_experience_mod").append(html_val);

    });

    //code-bookmark-js when the new button is pressed in the freelancer profile for more language
    jQuery("#add_language").click(function () {

        var html_val = '<div class="col-md-3"><label for="Language">' + reg_validation.linguist_user_info_Language + '</label><br><input type="text" value="" id="language[]" name="language[]" class="form-control"><label class="error"></label></div>';

        html_val += '<div class="col-md-2"><label for="language_level">' + reg_validation.linguist_user_info_Level + '</label><br><select name="language_level[]" id="language_level[]" class="selecter_trans" aria-invalid="false"><option value="native">Native</option><option value="fluent">Fluent</option><option value="learner">Learner</option></select><label class="error" style="display: none;"></label></div>';



        html_val += '<div class="col-md-3"><label for="areas_expertise">' + reg_validation.linguist_user_info_Areas_expertise + '</label><br><input type="text" value="" class="form-control" name="areas_expertise[]" id="areas_expertise[]" class="valid" aria-invalid="false"><label class="error" style="display: none;"></label></div>';

        html_val += '<div class="col-md-3"><label for="year_of_experince">' + reg_validation.linguist_user_info_Years_of_experience + '</label><br><input type="text" value="" id="year_of_experince[]" name="year_of_experince[]" class="form-control valid" aria-invalid="false"> <label class="error" style="display: none;"></label></div>';

        html_val += '<div class="col-md-1"><label for="delete_language"></label><br><a class="delete_language glyphicon glyphicon-remove" name="delete_language"  href="#"></a><label class="error" style="display: none;"></label></div>';

        html_val = '<div class="row">' + html_val + '</div>';

        jQuery("#language_mod").append(html_val);

    });

    //code-bookmark-js when the delete button is pressed in the freelancer profile for language
    jQuery(document).on('click', '.delete_language', function () {

        var attribute_val = jQuery(this).attr('name');

        var data = {'action': 'delete_language_info', 'attribute': attribute_val};

        var el = jQuery(this).parent().parent(".row");

        jQuery.post(adminAjax.url, data, function (response) {



            if (jQuery.trim(response) == 'success') {

                //alert("you are successfully updated");

                el.remove();

                return true;

            } else {

                jQuery("#form_error_message").html('Unauthorized user.');

                return false;

            }

        });

    });

    //code-bookmark-js when the delete button is pressed in the freelancer profile for certificates
    jQuery(document).on('click', '.delete_certificate_info', function () {

        var attribute_val = jQuery(this).attr('name');

        var data = {'action': 'delete_certificate_info', 'attribute': attribute_val};

        var el = jQuery(this).parent().parent(".row");

        jQuery.post(adminAjax.url, data, function (response) {



            if (jQuery.trim(response) == 'success') {

                //alert("you are successfully updated");

                el.remove();

                return true;

            } else {

                jQuery("#form_error_message").html('Unauthorized user.');

                return false;

            }

        });

    });

    //code-bookmark-js when the delete button is pressed in the freelancer profile for education
    jQuery(document).on('click', ".delete_education_info", function () {

        var attribute_val = jQuery(this).attr('name');

        if (attribute_val == '') {

            let el = jQuery(this).parent().parent(".row");

            el.remove();

        } else {

            var data = {'action': 'delete_education_info', 'attribute': attribute_val};

            let el = jQuery(this).parent().parent(".row");

            jQuery.post(adminAjax.url, data, function (response) {



                if (jQuery.trim(response) == 'success') {

                    //alert("you are successfully updated");

                    el.remove();

                    return true;

                } else {

                    jQuery("#form_error_message").html('Unauthorized user.');

                    return false;

                }

            });

        }

    });

    //code-bookmark-js when the delete button is pressed in the freelancer profile for related experience
    jQuery(document).on('click', ".delete_related_work_experience", function () {


        var attribute_val = jQuery(this).attr('name');

        var data = {'action': 'delete_related_work_experience', 'attribute': attribute_val};

        var el = jQuery(this).parent().parent(".row");

        jQuery.post(adminAjax.url, data, function (response) {



            if (jQuery.trim(response) == 'success') {

                //alert("you are successfully updated");

                el.remove();

                return true;

            } else {

                jQuery("#form_error_message").html('Unauthorized user.');

                return false;

            }

        });

    });


});


/*

 * Author Name: Lakhvinder Singh

 * Method: 		educationDetailForm

 * Description: To update the linguist education information

 *

 */

//code-bookmark-js part of the translater's account education form entry
function educationDetailForm() {

    var data = jQuery("#educationDetailForm").serializeArray();

    data.push({'name': 'action', 'value': 'update_linguist_edu_info'});

    jQuery.post(adminAjax.url, data, function (response) {

        if (jQuery.trim(response) == 'success') {

            window.location.reload(true);

        } else {

            bootbox.alert("Faileds");

            return false;

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		certificationDetailForm

 * Description: To update the linguist certification information

 *

 */

//code-bookmark-js part of the translater's account for certification form entry
function certificationDetailForm() {

    var data = jQuery("#certificationDetailForm").serializeArray();

    data.push({'name': 'action', 'value': 'update_linguist_certification_info'});

    console.log(data);

    jQuery.post(adminAjax.url, data, function (response) {

        if (jQuery.trim(response) == 'success') {

            window.location.reload(true);

        } else {

            bootbox.alert("Faileds");

            return false;

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		relatedExperinceDetailForm

 * Description: To update the linguist related experience information

 *

 */

//code-bookmark-js part of the translater's account for related experience form entry
function relatedExperinceDetailForm() {

    var data = jQuery("#relatedExperinceDetailForm").serializeArray();

    data.push({'name': 'action', 'value': 'update_linguist_related_experience_info'});

    console.log(data);

    jQuery.post(adminAjax.url, data, function (response) {

        if (jQuery.trim(response) == 'success') {

            window.location.reload(true);

        } else {

            bootbox.alert("Faileds");

            return false;

        }

    });

}


/*

 * Author Name: Lakhvinder Singh

 * Method: 		languageDetailForm

 * Description: To update the linguist language detail information

 *

 */

//code-bookmark-js part of the translater's account for language form entry
function languageDetailForm(formval) {

    var data = jQuery('#' + formval).serializeArray();

    data.push({'name': 'action', 'value': 'update_linguist_language_info'});

    console.log(data);

    jQuery.post(adminAjax.url, data, function (response) {

        if (jQuery.trim(response) == 'success') {

            window.location.reload(true);

        } else {

            bootbox.alert("Faileds");

            return false;

        }

    });

}


//code-bookmark-js handler for start and reject buttons for freelancer project after hired
//code-notes modified the ajax return to handle standard data
function hz_start_job(act, job_id, message) {

    //alert( "action :"+act+" Job ID "+job_id )

    bootbox.confirm({

        message: message,

        buttons: {confirm: {label: 'yes', className: 'btn-success'}, cancel: {label: 'no', className: 'btn-danger'}},

        callback: function (result) {

            if (result) {

                var data = {'action': 'hz_manage_job_status', 'job_id': job_id, 'act': act};

                jQuery.ajax({
                    type: 'POST', url: adminAjax.url, data: data, global: false,

                    success: function (response_raw) {
                        /**
                         * @type {FreelinguistJobStatusResponse} response
                         */
                        let response = freelinguist_safe_cast_to_object(response_raw);
                        if (response.status) {
                            bootbox.dialog({

                                message: response.message,
                                closeButton: false,
                                buttons: {
                                    "success": {
                                        label: "Ok",
                                        className: "btn-success",
                                        callback: function () {
                                            if (response.redirect_to) {
                                                window.location = response.redirect_to;
                                            }
                                        }
                                    }
                                }
                            });


                        }
                        else {
                            will_handle_ajax_error('Managing Job', response.message)
                        }

                    }//end success

                }); //end ajax


            }

        }

    });

    return false;

}


//code-bookmark-js for managing a third party plugin language menu
jQuery(document).on('mouseleave', '#lang_sel_click', function () {

    jQuery('#lang_sel_click ul li ul').css('display', 'none');

});


//code-bookmark-js for managing a third party plugin language menu
jQuery(document).on('mouseenter', '#lang_sel_click', function () {

    jQuery('#lang_sel_click ul li ul').show();

    jQuery('#lang_sel_click ul li ul').css('display', 'block');

    jQuery('#lang_sel_click ul li ul').css('visibility', 'visible');

});


/******* Author: AArvik ****/
//code-bookmark-js for making the rating stars work on the customer contest when they are rating proposals
function highlightStar(obj, id) {
    removeHighlight(id);
    jQuery('.rating-img #proposal-' + id + ' li').each(function (index) {

        jQuery(this).addClass('highlight');
        if (index == jQuery('.rating-img #proposal-' + id + ' li').index(obj)) {
            return false;
        }
    });
}

//code-bookmark-js for making the rating stars work on the customer contest when they are rating proposals
function removeHighlight(id) {
    jQuery('.rating-img #proposal-' + id + ' li').removeClass('selected');
    jQuery('.rating-img #proposal-' + id + ' li').removeClass('highlight');
}

//code-bookmark-js for making the rating stars work on the customer contest when they are rating proposals
function addRating(obj, id) {

    //code-notes find the last star clicked to find rating number (0 + the index for the star clicked)
    jQuery('.rating-img #proposal-' + id + ' li').each(function (index) {
        jQuery(this).addClass('selected');
        jQuery('#proposal-' + id + ' #rating').val((index + 1));
        if (index === jQuery('.rating-img #proposal-' + id + ' li').index(obj)) {
            return false;
        }
    });


    var data = {'id': id, 'action': 'update_proposal_rating', rating: jQuery('#proposal-' + id + ' #rating').val()};

    jQuery.post(adminAjax.url, data, function (response_raw) {

        /**
         * @type {FreelinguistBasicAjaxResponse} response
         */
        let response = freelinguist_safe_cast_to_object(response_raw);
        if (response.status === true) {
            window.location.reload(true);
        } else {
            will_handle_ajax_error('Delete Resume Attachment', response.message);
        }

    });
}


//code-bookmark-js for making the rating stars work on the customer contest when they are rating proposals
function resetRating(id) {
    if (jQuery('#proposal-' + id + ' #rating').val() != 0) {
        jQuery('.rating-img #proposal-' + id + ' li').each(function (index) {
            jQuery(this).addClass('selected');
            if ((index + 1) == jQuery('#proposal-' + id + ' #rating').val()) {
                return false;
            }
        });
    }
}

//code-bookmark-js for having the linguist see files they are adding for content
function previewForContent(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            jQuery('#content-cover')
                .attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);
    }
}


