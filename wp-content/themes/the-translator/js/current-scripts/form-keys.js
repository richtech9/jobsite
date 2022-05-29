//code-bookmark-js security and checks

//code-notes free_linguist_security_mutexes is a global object where we keep track of which protected buttons should not be processed again, during the handling process
var free_linguist_security_mutexes = { };

//code-notes _mutex_for_freelinguist_freeze_ajax is used to keep track of disabling all form submit buttons and ajax start buttons on a page
var _mutex_for_freelinguist_freeze_ajax = {freeze: false, unfreze:false};

var FORMKEY_DEBUG = false;

(function() {

    //code-notes add to action_ignore_list any actions you do not want to be processed by the XMLHttpRequest overrides
    let action_ignore_list = [
        'get_room_user'

    ];

    //code-notes is_xml is a helper function to quickly see if the text is in xml form (instead of json or regular text)
    function is_xml(xml){
        if (!xml) {return false;}
        try {
            return xml.charAt(0) === '<';

        } catch (err) {
            // was not XML
            return false;
        }
    }

    /**
     * code-notes get_query_vars is used to convert a url encoded string into an object of key values
     * @param {string} query
     */
    function get_query_vars(query) {
        let match,
            pl = /\+/g,  // Regex for replacing addition symbol with a space
            search = /([^&=]+)=?([^&]*)/g,
            decode = function (s) {
                return decodeURIComponent(s.replace(pl, " "));
            }
        ;

        let urlParams = {};
        while (match = search.exec(query)) {
            urlParams[decode(match[1])] = decode(match[2]);
        }
        return urlParams;
    }

    //code-notes We override all XMLHttpRequest objects on this page so we can keep track of the opens and the returns


    var origOpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function() {

        //code-notes We listen to server errors so buttons and forms do not get locked up if there is an issue
        this.addEventListener('onerror ', function() {
            //code-notes unfreeze any buttons, and unlock request method again
            let sending_args = null;
            if (this.hasOwnProperty('data_sending_args')) {
                sending_args = this['data_sending_args'];
            }
            let form_key_name = null;
            if (sending_args && typeof sending_args === 'object' && sending_args.hasOwnProperty('_form_security_name')) {
                form_key_name = sending_args._form_security_name;
                if (free_linguist_security_mutexes.hasOwnProperty(form_key_name)) {
                    free_linguist_security_mutexes[form_key_name] = false;
                    freelinguist_freeze_ajax_and_form_buttons_on_page(false,form_key_name);
                }
            }
        });

        //code-notes when the server returns with information, we want to unlock the specific action that was triggered and unfreeze all the submit buttons
        this.addEventListener('load', function() {
            if (this.responseXML) {
                return;
            }
            let sending_args = null;
            if (this.hasOwnProperty('data_sending_args')) {
                sending_args = this['data_sending_args'];
            }
            let form_key_name = null;

            let wp_action = null;
            if (sending_args && typeof sending_args === 'object' && sending_args.hasOwnProperty('action')) {
                wp_action = sending_args.action;
                if (action_ignore_list.includes(wp_action)) {return;}
            }
            if (FORMKEY_DEBUG) { console.debug('request started!',arguments[0],arguments[1]); }
            if (FORMKEY_DEBUG) {  console.debug('xlr object is',this); }
            if (sending_args && typeof sending_args === 'object' && sending_args.hasOwnProperty('_form_security_name')) {
                form_key_name = sending_args._form_security_name;

                if (free_linguist_security_mutexes.hasOwnProperty(form_key_name)) {
                    free_linguist_security_mutexes[form_key_name] = false;
                    if (FORMKEY_DEBUG) { console.debug("unblocked "+form_key_name,free_linguist_security_mutexes );}
                    freelinguist_freeze_ajax_and_form_buttons_on_page(false,form_key_name);
                }
            }


            //code-notes If the server reports the form key is invalid, then the ajax or submit was not processed. Here, we show a dialog suggestion to refresh the page
            /**
             * @var {DaAjaxObjectResponse} response
             */
            let response = freelinguist_safe_cast_to_object((this.responseText));
            //code-notes there is also information sent to the console about information sent and processed through ajax
            if (FORMKEY_DEBUG) { console.debug('ajax hook sent/response',sending_args,response);}
            if (typeof response === 'object' && response !== null) {
                if (response.do_refresh_message) {
                    if (bootbox) {
                        //bootbox.alert("<span class='freelinguist-refresh-page'>"+response.msg+"</span>");
                        window.location.reload(true); //code-notes just refresh the page
                    } else {
                        console.warn("bootbox is not a global object here, cannot alert!",response.msg)
                    }
                }
            }
        });

        origOpen.apply(this, arguments);
    };



    XMLHttpRequest.prototype.realSend = XMLHttpRequest.prototype.send;
    //code-notes in the XMLHttpRequest we keep track of the data sent out, because we need to find the action when data returns, and things are not in order if multiple calls made
    // here "this" points to the XMLHttpRequest Object.
    var newSend = function(vData) {
        let string_data = null;
        //vData may be object, string of json, or url encoded string
        if (typeof vData === 'string' || vData instanceof String) {
            string_data = vData.toString(); //takes care of String objects too
            if (string_data !== decodeURIComponent(string_data) ) {
                string_data = decodeURIComponent(string_data);
            }
            //try to explode it on =
            if (string_data.indexOf('=') !== -1) { //true
                let obj = get_query_vars(string_data);
                string_data = JSON.stringify(obj);
            }
        } else if (vData) {
            if (Array.isArray(vData) || typeof vData === 'object') {
                string_data = JSON.stringify(vData);
            }
        }

        if (!is_xml(string_data)) {
            let flag = string_data;
            try {
                flag = JSON.parse(string_data);
            } catch (e) {
            }

            this.data_sending_args = flag;
        } else {
            this.data_sending_args = null;
        }

        this.realSend(vData);

    };
    XMLHttpRequest.prototype.send = newSend;


})();





/**
 * code-notes each ajax or form handler on the js side need only add freelinguist_add_security_keys and its protected, as long as the form-key is added to the page elsewhere. A false return means this is locked
 * code-notes while the freelinguist_add_security_keys can tell if there is a lock, its up to the calling code to return. So a check for false, and a return of false should be added after this function is used
 * code-notes freelinguist_add_security_keys can handle data as a normal object, or as a serialized array (name value pairs in array format)
 * @param {*} data  array or object . Either an array of name:value objects, or a simple object
 * @param {string} key_name
 * @param {boolean} is_serialized_array . If this is serialized, then data needs to be an array
 * @returns {*}
 */
function freelinguist_add_security_keys(data,key_name,is_serialized_array) {
    try {

        //playing it safe here. If the ajax global function does not define form keys, or the form key is missing, get out of here and don't stop anything
        if (!adminAjax.hasOwnProperty('form_keys')) {
            return data;
        }

        if (!adminAjax.form_keys.hasOwnProperty(key_name)) {
            return data;
        }


        //one shall pass at a time
        if (!free_linguist_security_mutexes.hasOwnProperty(key_name)) {
            free_linguist_security_mutexes[key_name] = true;
            //code-notes freelinguist_add_security_keys will write to the console when its blocking, for easier debugging
            console.warn("blocked "+key_name,free_linguist_security_mutexes );
        } else {
            if (free_linguist_security_mutexes[key_name] === true) {
                console.warn("IS BLOCKING "+key_name,free_linguist_security_mutexes );
                return false;
            }
            free_linguist_security_mutexes[key_name] = true;
            console.warn("blocked "+key_name,free_linguist_security_mutexes );
        }
        freelinguist_freeze_ajax_and_form_buttons_on_page(true,key_name);
        is_serialized_array = !!is_serialized_array;
        if (!adminAjax.hasOwnProperty('form_keys')) {
            if (console.trace) {console.trace(); }
            throw new Error('adminAjax has no form keys property');
        }

        if (!adminAjax.hasOwnProperty('nonce')) {
            if (console.trace) {console.trace(); }
            throw new Error('adminAjax has no nonce property');
        }

        if (!adminAjax.form_keys.hasOwnProperty(key_name)) {
            if (console.trace) {console.trace(); }
            throw new Error('adminAjax.form_keys has no form key of ' + key_name);
        }

        if (is_serialized_array && Array.isArray(data)) {
            data.push({name: "_form_key", value: adminAjax.form_keys[key_name]});//
            data.push({name: "_wpnonce", value: adminAjax.nonce});
            data.push({name: "_form_security_name", value: key_name});
        } else {
            data._form_key = adminAjax.form_keys[key_name];
            data._wpnonce = adminAjax.nonce;
            data._form_security_name = key_name;
        }
    } catch (e) {
        console.error(e.toString());
        freelinguist_freeze_ajax_and_form_buttons_on_page(false,key_name);
        return false;
    }
    return data;

}


/**
 * code-notes freelinguist_freeze_ajax_and_form_buttons_on_page:  When a protected ajax or form is processed we disabled ALL the submit buttons, and buttons in the page until the server returns. BUT we do not touch buttons that are disabled outside of this function
  Disables/Enables buttons that start ajax things
 * @param b_freeze true will disable, false will re-enable
 * @param {string} form_key_name
 * can only call alternately , so calling freeze twice will not do anything the second time. Need to call unfreeze first
 * and vice versa
 *
 * This is because there are some buttons that are supposed to be disabled, or enabled, that are not to be changed here
 * This code is designed to be put into existing code without changing any html classes or logic
 * The basic use will be for the ajax handler to call to freeze
 * and the universal ajax listener above, to unfreeze
 * The mutex removes issues when other ajax fires in the middle of an ajax code where we are doing this in, and the universal handler
 *  unfreezes things twice. This matters because we keep track here of what we are not supposed to disable or enable, and an extra firing can mess that up
 *
 */
function freelinguist_freeze_ajax_and_form_buttons_on_page(b_freeze, form_key_name ) {
    let $ = jQuery;
    //button.action-btns, input.bidreplysubmit
    let buttons = $('button.action-btns');
    let input_buttons = $('input.bidreplysubmit').add('input[type="submit"]');
    let disabled_buttons = buttons.not(':enabled');
    let disabled_input = input_buttons.not('enabled');
    let all_things = buttons.add(input_buttons);
    let all_things_to_not_touch = disabled_buttons.add(disabled_input);
    let all_things_to_touch = all_things.not(all_things_to_not_touch);

    let all_things_that_we_already_touched = all_things.filter('.freelinguist-disabled-status');
    b_freeze = !! b_freeze;
    if (b_freeze) {
        if (_mutex_for_freelinguist_freeze_ajax.freeze) {return;}
        _mutex_for_freelinguist_freeze_ajax.freeze = true;
        _mutex_for_freelinguist_freeze_ajax.unfreze = false;
        //find any already disabled and add a special class for them, as these will be managed by other code
        all_things_to_not_touch.addClass('do-not-disable');
        all_things_to_touch.addClass('freelinguist-disabled-status').prop('disabled', true);
    } else {
        if (_mutex_for_freelinguist_freeze_ajax.unfreze) {return;}
        _mutex_for_freelinguist_freeze_ajax.unfreze = true;
        _mutex_for_freelinguist_freeze_ajax.freze = false;
        all_things_that_we_already_touched.removeClass('freelinguist-disabled-status').prop('disabled', false);
        all_things_to_not_touch.removeClass('do-not-disable');
    }
}
var fl_user_interacted_with_window = false;

jQuery(function($) {

    function fun(){
        fl_user_interacted_with_window = true;
        $(window).unbind("scroll");
        $(window).unbind("click");
    }
    $(window).bind("scroll click", fun);
});