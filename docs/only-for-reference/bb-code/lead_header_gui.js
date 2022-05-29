jQuery(function($) {


    $('div.the-widget-top').on('click', '[data-toggle="lightbox"]', function (event) {
        event.preventDefault();
        $(this).ekkoLightbox();
    });

    function format_long_date_time(qthis,ts) {
        if (ts === 0 || ts === '0' || ts === undefined || ts === '') {
            qthis.text('');
        } else {
            var m = moment(ts * 1000);
            qthis.text(m.format('LLLL'));
        }
    }

    $("div.the-widget-top .a-timestamp-long-date-time").each(function () {
        var qthis = $(this);
        var ts = $(this).data('ts');
        format_long_date_time(qthis,ts);

    });

    $("div.the-widget-top .a-timestamp-short-date").each(function () {
        var qthis = $(this);
        var ts = $(this).data('ts');
        if (ts === 0 || ts === '0' || ts === undefined || ts === '') {
            qthis.text('');
        } else {
            var m = moment(ts * 1000);
            qthis.text(m.format('ll'));
        }
    });

    /**
     * All of these checkboxes are on if the value of the tag is false
     * some of these tags are not yet saved to the database, so the id may be null
     */
    $('div.the-widget-top input.ckb-boolean-tag-json[type="checkbox"]').click(function() {
        let checker = $(this);
        let tag_json_base_64_encoded = checker.data('tagjson');
        if (!tag_json_base_64_encoded) {
            console.error("Checkbox of type ckb-boolean-tag-json does not have tag json info. Cannot process");
            return;
        }
        let tag_json = window.atob(tag_json_base_64_encoded);
        let is_checked = false;
        /**
         * @type {SymLeadTag} tag
         */
        let tag = JSON.parse(tag_json);
        if(checker.is(':checked')) {
            is_checked = true;
            //set tag value to false
            tag.tag_value = '0';
        }
        else {
            //set it to true
            tag.tag_value = '1';
        }

        TheWidget.call_event('update_contact_tags',
            tag,
            (generated_html,css,before_js,after_js,php_response)=>
            {
                //reset stored tag info
                let new_data = window.btoa(php_response);
                checker.data('tagjson',new_data);
                //console.log(generated_html,css,before_js,after_js,php_response)
            },
            error => {
                is_checked = !is_checked;
                checker.prop('checked', is_checked);
                let parent = checker.closest('div.row');
                if (parent.length) {
                    parent.notify(error, { position:"bottom",clickToHide: true,autoHide: false, className: 'error' });
                } else {
                    console.error("No checker parent found for the error message",error);
                }

            }
        );
    });

    let public_editor;
    let private_editor ;
    //'div.the-widget-top div.leadhead-public-notes textarea'
    {
        let textarea = $('div.the-widget-top div.leadhead-public-notes textarea')[ 0 ];

         sceditor.create(textarea, {
            format: 'bbcode',
            style:  TheWidget.plugin_root()  + '/symphony-actions/node_modules/sceditor/' + 'minified/themes/content/default.min.css',
            toolbarExclude: 'maximize,email,unlink,youtube,date,time,ltr,rtl,cut,copy,paste,pastetext',

            emoticonsRoot: TheWidget.plugin_root()  + '/symphony-actions/node_modules/sceditor/'
        });
        public_editor = textarea._sceditor;
        let b_after_init = false;
        public_editor.bind('valuechange  keyup blur paste pasteraw',function(/* e */) {
            if (b_after_init) {
                mark_pending_notes();
            } else {
                b_after_init = true;
            }

            },false,false);
    }

    {
        let textarea = $('div.the-widget-top div.leadhead-private-notes textarea')[ 0 ];

        sceditor.create(textarea, {
            format: 'bbcode',
            style:  TheWidget.plugin_root()  + '/symphony-actions/node_modules/sceditor/' + 'minified/themes/content/default.min.css',
            toolbarExclude: 'maximize,email,unlink,youtube,date,time,ltr,rtl,cut,copy,paste,pastetext',

            emoticonsRoot: TheWidget.plugin_root()  + '/symphony-actions/node_modules/sceditor/'
        });
        private_editor = textarea._sceditor;
        let b_after_init = false; //selectionchanged
        private_editor.bind('valuechange  keyup blur paste pasteraw',function(/* e */) {
            if (b_after_init) {
                mark_pending_notes();
            } else {
                b_after_init = true;
            }
        },false,false);
    }




    let b_init_checking_notes = true;
    let b_do_checking = true;
    let private_textarea = $('div.the-widget-top div.leadhead-private-notes textarea.private-o-textarea');
    let public_textarea = $('div.the-widget-top div.leadhead-public-notes textarea.public-o-textarea');
    let private_notes = private_editor.val();
    let public_notes = public_editor.val();

    function mark_pending_notes() {
        $('div.leadhead-notes-last-save').addClass('pending-saved');
    }
    (function keep_checking_notes() {


        if (b_init_checking_notes) {
            $('div.the-widget-top div.leadhead-notes-last-save').click(function() {
                check_and_save_notes();
            });
            b_init_checking_notes = false;
        }

        function save_note(text_editor,textarea) {
            let b_do_saving = true; //making the save notes icon blink
            $('div.leadhead-notes-last-save').removeClass('pending-saved');
            $('div.leadhead-notes-last-save').removeClass('unsaved');
            let error_span = $('div.leadhead-notes-last-save span.leadhead-save-error');
            error_span.text('');
            function show_saving() {
                if (b_do_saving) {
                    let element = $('div.the-widget-top i.leadhead-save-indicator');
                    element.fadeIn(1000).fadeOut(1000).fadeIn(1000);
                    setTimeout(show_saving, 3000);
                }
            }

            let tag_json_base_64_encoded = textarea.data('tagjson');
            if (!tag_json_base_64_encoded) {
                console.error("textarea does not have tag json info. Cannot process");
                return;
            }
            let tag_json = window.atob(tag_json_base_64_encoded);
            /**
             * @type {SymLeadTag} tag
             */
            let tag = JSON.parse(tag_json);
            tag.tag_value = text_editor.val();
            show_saving();
            TheWidget.call_event('update_contact_tags',
                tag,
                (generated_html,css,before_js,after_js,php_response)=>
                {
                    b_do_saving = false; //stop the blinker
                    //reset stored tag info
                    let da_tag;
                    let da_return = JSON.parse(php_response);
                    if (Array.isArray(da_return)) {
                        da_tag = da_return[0];
                    } else {
                        da_tag = da_return;
                    }
                    let da_json_string = JSON.stringify(da_tag);
                    let new_data = window.btoa(da_json_string);
                    textarea.data('tagjson',new_data);
                    //console.log(generated_html,css,before_js,after_js,php_response)
                    update_note_timestamp();
                },
                error => {
                    b_do_saving = false; //stop the blinker
                    //change the icon to unsaved
                    $('div.leadhead-notes-last-save').addClass('unsaved');
                    //show notice
                    error_span.text('Not Saved');
                    let parent = textarea.closest('div.row');
                    if (parent.length) {
                        parent.notify(error, { position:"bottom",clickToHide: true,autoHide: false, className: 'error' });
                    } else {
                        console.error("No textarea parent found for the error message",error);
                    }

                }
            );
        }

        function check_and_save_notes() {

            let current_private_notes = private_editor.val();
            if (current_private_notes !== private_notes) {
                private_notes = current_private_notes;
                save_note(private_editor,private_textarea);
            }


            let current_public_notes = public_editor.val();
            if (current_public_notes !== public_notes) {
                public_notes = current_public_notes;
                save_note(public_editor,public_textarea);
            }
            $('div.leadhead-notes-last-save').removeClass('pending-saved');
        }

        if (b_do_checking) {
            check_and_save_notes();
            setTimeout(keep_checking_notes, 30000);
        }
    })();


    function update_note_timestamp(b_use_data) {

        let qthis = $('div.the-widget-top span.leadhead-last-time-saved');
        let ts ;
        qthis.data('ts');
        if (b_use_data) {
            ts = qthis.data('ts');
        } else {
            var date = new Date();
            ts = date.getTime()/1000;
        }
        format_long_date_time(qthis,ts);
    }


    update_note_timestamp(true);


});

//not all browsers do the same base64 decoding, especially some  mobile versions
//from https://developer.mozilla.org/en-US/docs/Web/API/WindowOrWorkerGlobalScope/atob
// From https://github.com/MaxArt2501/base64-js/blob/master/base64.js
(function() {
    if (window.atob) {
        // Some browsers' implementation of atob doesn't support whitespaces
        // in the encoded string (notably, IE). This wraps the native atob
        // in a function that strips the whitespaces.
        // The original function can be retrieved in atob.original
        try {
            window.atob(" ");
        } catch (e) {
            window.atob = (function(atob) {
                var func = function(string) {
                    return atob(String(string).replace(/[\t\n\f\r ]+/g, ""));
                };
                func.original = atob;
                return func;
            })(window.atob);
        }
        return;
    }

    // base64 character set, plus padding character (=)
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
        // Regular expression to check formal correctness of base64 encoded strings
        b64re = /^(?:[A-Za-z\d+\/]{4})*?(?:[A-Za-z\d+\/]{2}(?:==)?|[A-Za-z\d+\/]{3}=?)?$/;

    window.atob = function(string) {
        // atob can work with strings with whitespaces, even inside the encoded part,
        // but only \t, \n, \f, \r and ' ', which can be stripped.
        string = String(string).replace(/[\t\n\f\r ]+/g, "");
        if (!b64re.test(string))
            throw new TypeError("Failed to execute 'atob' on 'Window': The string to be decoded is not correctly encoded.");

        // Adding the padding if missing, for semplicity
        string += "==".slice(2 - (string.length & 3));
        var bitmap, result = "",
            r1, r2, i = 0;
        for (; i < string.length;) {
            bitmap = b64.indexOf(string.charAt(i++)) << 18 | b64.indexOf(string.charAt(i++)) << 12 |
                (r1 = b64.indexOf(string.charAt(i++))) << 6 | (r2 = b64.indexOf(string.charAt(i++)));

            result += r1 === 64 ? String.fromCharCode(bitmap >> 16 & 255) :
                r2 === 64 ? String.fromCharCode(bitmap >> 16 & 255, bitmap >> 8 & 255) :
                    String.fromCharCode(bitmap >> 16 & 255, bitmap >> 8 & 255, bitmap & 255);
        }
        return result;
    };

})();


// Polyfill from  https://github.com/MaxArt2501/base64-js/blob/master/base64.js
(function() {
    // base64 character set, plus padding character (=)
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

        // Regular expression to check formal correctness of base64 encoded strings
        b64re = /^(?:[A-Za-z\d+\/]{4})*?(?:[A-Za-z\d+\/]{2}(?:==)?|[A-Za-z\d+\/]{3}=?)?$/;

    window.btoa = window.btoa || function(string) {
        string = String(string);
        var bitmap, a, b, c,
            result = "",
            i = 0,
            rest = string.length % 3; // To determine the final padding

        for (; i < string.length;) {
            if ((a = string.charCodeAt(i++)) > 255 ||
                (b = string.charCodeAt(i++)) > 255 ||
                (c = string.charCodeAt(i++)) > 255)
                throw new TypeError("Failed to execute 'btoa' on 'Window': The string to be encoded contains characters outside of the Latin1 range.");

            bitmap = (a << 16) | (b << 8) | c;
            result += b64.charAt(bitmap >> 18 & 63) + b64.charAt(bitmap >> 12 & 63) +
                b64.charAt(bitmap >> 6 & 63) + b64.charAt(bitmap & 63);
        }

        // If there's need of padding, replace the last 'A's with equal signs
        return rest ? result.slice(0, rest - 3) + "===".substring(rest) : result;
    };

    window.atob = window.atob || function(string) {
        // atob can work with strings with whitespaces, even inside the encoded part,
        // but only \t, \n, \f, \r and ' ', which can be stripped.
        string = String(string).replace(/[\t\n\f\r ]+/g, "");
        if (!b64re.test(string))
            throw new TypeError("Failed to execute 'atob' on 'Window': The string to be decoded is not correctly encoded.");

        // Adding the padding if missing, for semplicity
        string += "==".slice(2 - (string.length & 3));
        var bitmap, result = "",
            r1, r2, i = 0;
        for (; i < string.length;) {
            bitmap = b64.indexOf(string.charAt(i++)) << 18 | b64.indexOf(string.charAt(i++)) << 12 |
                (r1 = b64.indexOf(string.charAt(i++))) << 6 | (r2 = b64.indexOf(string.charAt(i++)));

            result += r1 === 64 ? String.fromCharCode(bitmap >> 16 & 255) :
                r2 === 64 ? String.fromCharCode(bitmap >> 16 & 255, bitmap >> 8 & 255) :
                    String.fromCharCode(bitmap >> 16 & 255, bitmap >> 8 & 255, bitmap & 255);
        }
        return result;
    };
})();