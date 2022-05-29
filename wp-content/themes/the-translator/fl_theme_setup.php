<?php
class FLThemeSetup
{

    public static function always_enqueue() {


        wp_enqueue_script('js-notify', get_template_directory_uri() . '/js/lib/notify.js', array('jquery'), '0.4.1');
        wp_enqueue_script('tag-manager', get_template_directory_uri() . '/js/lib/tagmanager.min.js', array('jquery'), '1.0.0',true);
        wp_enqueue_script('boot-typehead-script', get_template_directory_uri() . '/js/lib/bootstrap3-typeahead.min.js', array('jquery'), '1.0.0',true);
        wp_enqueue_script('custom', get_template_directory_uri() . '/js/current-scripts/custom.js', array('jquery'), '1.0.0');
        wp_enqueue_script('customer-content', get_template_directory_uri() . '/js/current-scripts/customer-content.js', array('jquery'), '1.0.0');
        wp_enqueue_script('slick', get_template_directory_uri() . '/js/slick-1.9.0/slick.min.js', array('jquery'), '1.9.0',true);
        wp_enqueue_style('tingle', get_template_directory_uri() . '/js/lib/tingle.css', array(),'0.15.2','all');
        wp_enqueue_script( 'tingle', get_template_directory_uri() . '/js/lib/tingle.js','', '0.15.2', true );
        wp_enqueue_script( 'uuid', get_template_directory_uri() . '/js/lib/uuid.min.js','', '8.3.2', true );
        wp_enqueue_script( 'numeric', get_template_directory_uri() . '/js/lib/numeric.js','', '0.1.0', false );
        wp_enqueue_script('freelinguist-ajax', get_template_directory_uri() . '/js/current-scripts/freelinguist_ajax.js', array('jquery'), '1.0.0');
    }

    public static function enqueue_frontend() {

        if($GLOBALS['pagenow'] != 'wp-login.php' ){
            wp_enqueue_script('jquery');


            wp_enqueue_script('bootstrap', get_template_directory_uri().'/js/lib/bootstrap.js', array('jquery'), '',true);
            wp_enqueue_script('html5', get_template_directory_uri() . '/js/lib/html5.js', array('jquery'), '');
            wp_enqueue_script('typeahead', get_template_directory_uri() . '/js/lib/typeahead.bundle.js', array('jquery'), '');
            wp_enqueue_script('validate-js', get_template_directory_uri() . '/js/lib/jquery.validate.js', array('jquery'), '');
            // wp_enqueue_script('bootstrap-js', get_template_directory_uri() . '/js/bootstrap.js', array('jquery'), '');
            wp_enqueue_script('moment-js', get_template_directory_uri() . '/js/lib/moment.js', array('jquery'), '');
            wp_enqueue_script('bootstrap-select', get_template_directory_uri() . '/js/lib/bootstrap-select.js', array('jquery'), '');
            wp_enqueue_script('bootbox', get_template_directory_uri() . '/js/lib/bootbox-min.js', array('jquery'), '',true);
            wp_enqueue_script('dev-script', get_template_directory_uri() . '/js/current-scripts/dev-scripts.js', array('jquery'), '5.1.3');
            wp_enqueue_script('dev-content', get_template_directory_uri() . '/js/current-scripts/dev-content.js', array('jquery'), '0.3.0');

            wp_enqueue_script('datepicker', get_template_directory_uri() . '/js/lib/datepickerjs.js', array('jquery'), '1.0.0');
            wp_enqueue_script('jquery.flexslider', get_template_directory_uri() . '/js/lib/jquery.flexslider.js', array('jquery'),'2.7.0',true);
            wp_enqueue_script('rateyo', get_template_directory_uri() . '/js/lib/jquery.rateyo.min.js', array('jquery'));

            wp_enqueue_script('datatables', get_template_directory_uri() . '/js/datatables/jquery.dataTables.js', array('jquery'),'1.10.19',true);
            //code-notes removed obsolete dropzone that was not used anywhere and was configured to run with non used code

          //wp_enqueue_script('fl_lodash', get_template_directory_uri() . '/js/lib/lodash.js', [],'4.17.15'); //code-notes add lodash, but make it not conflict with other things
            //wp_add_inline_script( 'fl_lodash', 'window.fl_lodash = _.noConflict();',  'after' )  ;
            //code-notes , the adminAjax object will have a nonce, and  form-keys . This is how the form key library javascript knows about info to send to the php side:  used on non-admin pages here
            wp_localize_script('dev-script', 'adminAjax', [
                    'url' => admin_url( 'admin-ajax.php' ),
                    'logged_in_user_id'=> (int)get_current_user_id(),
                    'login_url' => freeling_links('login_url'),
                    'nonce' => wp_create_nonce(FREELINGUIST_DEFAULT_NONCE_NAME),
                    'form_keys' => [
                        //some form keys have to be in the form itself, but others can simply get it from the global js object created here
                    ],
                    'flags' => [
                        'b_ignore_milestone_cancel_dialog' => false,
                        'b_ignore_proposal_cancel_dialog' => false,
                        'b_ignore_content_cancel_dialog' => false
                    ]
                ]
            );
            wp_localize_script( 'dev-script', 'devscript', array( 'template_url' => get_bloginfo('template_url'),'template'=>$_SERVER['REQUEST_URI'] ) );
            wp_localize_script( 'dev-script', 'devscript_getsiteurl', array( 'getsiteurl' => get_site_url() ) );
            wp_localize_script( 'dev-script', 'required_valid', array( 'required_validation' => get_custom_string_return("Please fill the required field") ) );
            wp_localize_script( 'dev-script', 'required_validation', array( 'required_payment_method' => get_custom_string_return("Please select a payment method") ) );

            $language_array = fl_generate_language_array_for_js_object();
            wp_localize_script( 'dev-script', 'reg_validation', $language_array);


            wp_enqueue_script('vendor', get_template_directory_uri() . '/js/file_upload/vendor/jquery.ui.widget.js', array('jquery'), '1.0.0');
            wp_enqueue_script('iframetranspor', get_template_directory_uri() . '/js/file_upload/jquery.iframe-transport.js', array('jquery'), '1.0.0');
            /*
            wp_enqueue_script('fileupload8', 'https://blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js');
            wp_enqueue_script('fileupload9', 'https://blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js'); */
            wp_enqueue_script('fileupload1', get_template_directory_uri() . '/js/file_upload/jquery.fileupload.js', array('jquery'), '1.0.0');

            wp_enqueue_script('fileupload2', get_template_directory_uri() . '/js/file_upload/jquery.fileupload-process.js', array('jquery'), '1.0.0');
            wp_enqueue_script('fileupload3', get_template_directory_uri() . '/js/file_upload/jquery.fileupload-image.js', array('jquery'), '1.0.0');
            wp_enqueue_script('fileupload4', get_template_directory_uri() . '/js/file_upload/jquery.fileupload-audio.js', array('jquery'), '1.0.0');
            wp_enqueue_script('fileupload5', get_template_directory_uri() . '/js/file_upload/jquery.fileupload-video.js', array('jquery'), '1.0.0');
            wp_enqueue_script('fileupload6', get_template_directory_uri() . '/js/file_upload/jquery.fileupload-validate.js', array('jquery'), '1.0.0');

            wp_enqueue_script('images-loaded', get_template_directory_uri() . '/js/lib/imagesloaded.js', [], '4.1.4');
            wp_enqueue_script('tooltip-functions', get_template_directory_uri() . '/js/current-scripts/fl-tooltips.js', array('jquery'), '0.0.1');
            wp_enqueue_script('wallet-wrappers', get_template_directory_uri() . '/js/current-scripts/wallet-wrappers.js', array('jquery'), '0.0.3',true);
            wp_enqueue_script('content-buy-dialogs', get_template_directory_uri() . '/js/current-scripts/content-buy-dialogs.js', array('jquery'), '0.0.1',true);
            wp_enqueue_script('freelancer-content', get_template_directory_uri() . '/js/current-scripts/freelancer-content.js', array('jquery'), '0.0.1',true);
            wp_enqueue_script('freelancer-project', get_template_directory_uri() . '/js/current-scripts/freelancer-project.js', array('jquery'), '0.0.1',true);
            wp_enqueue_script('customer-contest', get_template_directory_uri() . '/js/current-scripts/customer-contest.js', array('jquery'), '0.0.1',true);
            wp_enqueue_script('user-settings', get_template_directory_uri() . '/js/current-scripts/settings.js', array('jquery'), '0.0.1',true);
            wp_enqueue_script('wallet-scripts', get_template_directory_uri() . '/js/current-scripts/payment-wallet.js', array('jquery'), '0.0.5',true);
            wp_enqueue_script('society-scripts', get_template_directory_uri() . '/js/current-scripts/society.js', array('jquery'), '0.0.1',true);

            $additional_scripts = array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'themeurl' => get_stylesheet_directory_uri(),
            );
            wp_register_script('additional_scripts.js', get_template_directory_uri() . '/js/current-scripts/additional_scripts.js',[],'1.1');
            wp_localize_script('additional_scripts.js', 'getObj', $additional_scripts);
            wp_enqueue_script('additional_scripts.js');

            //code-notes bb code editor below
            wp_enqueue_style( 'sceditor', get_template_directory_uri() . '/js/sceditor/minified/themes/default.min.css', array(), '3.0.0', 'all' );
            wp_enqueue_script( 'sceditor', get_template_directory_uri() . '/js/sceditor/minified/sceditor.min.js', array('jquery'), '3.0.0', false );
            wp_enqueue_script( 'sceditor-bbcode-plugin', get_template_directory_uri() . '/js/sceditor/minified/formats/bbcode.js', array('sceditor'), '3.0.0', false );

        }
        //code-notes its very important to NOT change the order of these unless you know all the side effects
        wp_enqueue_style('reset', get_template_directory_uri() . '/css/current-code/reset.css', array(), '1.0', 'all');
        wp_enqueue_style('datepicker', get_template_directory_uri() . '/css/lib/datepicker.css', array(), '1.0', 'all');
        wp_enqueue_style('font-awesome', get_template_directory_uri() . '/css/font-awesome-4.7.0/css/font-awesome.min.css', array(), '4.7.0', 'all');

        wp_enqueue_style('bootstrap-style', get_template_directory_uri() . '/css/lib/bootstrap.css', array(), '1.0', 'all');
        wp_enqueue_style('bootstrap-select', get_template_directory_uri() . '/css/lib/bootstrap-select.css', array(), '1.0', 'all');
        wp_enqueue_style('site-style', get_template_directory_uri() . '/css/current-code/style.css', array(), '1.1.1.3', 'all');
        wp_enqueue_style('site-style-new', get_template_directory_uri() . '/css/current-code/style-new.css', array(), '1.0.11', 'all');
        wp_enqueue_style('responsive', get_template_directory_uri() . '/css/current-code/responsive.css', array(), '2.0', 'all');
        wp_enqueue_style('dev-style', get_template_directory_uri() . '/style.css', array(), '1.1', 'all');
        wp_enqueue_style('fl-frontend-and-admin', get_template_directory_uri() . '/css/current-code/frontend-and-admin.css', array(), '1.1', 'all');


        //todo this needs below to be changed from dev-style so it can be included in the page, however there are errors in flexslider.css so need to fix it first
        wp_enqueue_style('dev-style', get_template_directory_uri() . '/css/lib/flexslider.css', array(), '1.0', 'all');

        wp_enqueue_style('additional_css.css', get_template_directory_uri() . '/css/current-code/additional_css.css', array(), '2.1', 'all');
        wp_enqueue_style('tagmanger.css', get_template_directory_uri() . '/css/lib/tagmanager.min.css', array(), '1.0', 'all');
        wp_enqueue_style('chat_style', get_template_directory_uri() . '/css/current-code/chat_style.css', array(), '1.0', 'all');
        wp_enqueue_style('icon', get_template_directory_uri() . '/css/lib/material-icon.css', array(), '1.0', 'all');
        wp_enqueue_style('site-fonts-roboto', get_template_directory_uri() . '/css/lib/roboto.css', array(), '1.0.1', 'all');
        wp_enqueue_script('hz-script', get_template_directory_uri() . '/js/current-scripts/hz-scripts.js', array('jquery'), '5.0.2');
        wp_enqueue_style('standards', get_template_directory_uri() . '/css/current-code/standards.css', array(), '1.0', 'all');

        wp_enqueue_script('form-keys', get_template_directory_uri() . '/js/current-scripts/form-keys.js', array('jquery'), '1.0.0');
        wp_enqueue_style('rate-yo', get_template_directory_uri() . '/css/lib/jquery.rateyo.min.css', array(), '1.0', 'all');
        wp_enqueue_style('datatables', get_template_directory_uri() . '/css/datatables/jquery.dataTables.css', array(),'1.10.19','all');
        wp_enqueue_style('slick', get_template_directory_uri() . '/js/slick-1.9.0/slick.css', array(),'1.9.0','all');
        $xmpp_credentials = EjabberdWrapper::get_xmpp_credentials();
        if (!empty($xmpp_credentials)) { //code-notes we are always loading in the chat scripts now, unlogged users will still have credentials
            wp_enqueue_script('strophe', get_template_directory_uri() . '/js/strophe/strophe.js', array('jquery'), '1.0.0');

            $options = get_option('xmpp_settings');
            //code-bookmark where the chat credentials are loaded
            $xmpp_domain = empty($options['xmpp_domain'])?'': $options['xmpp_domain'];
            $xmpp_port = empty($options['xmpp_port'])? 5443: (int)$options['xmpp_port'];
            wp_localize_script('strophe', 'xmpp_helper', array(
                'xmpp_host' => $xmpp_domain,
                'bosh_service_url' => 'https://' . $xmpp_domain . ":$xmpp_port/bosh",// site_url('/http-bind'),
                'conference_domain' => 'conference.' . $xmpp_domain  ,
                'username' => $xmpp_credentials['jid'],
                'password' => $xmpp_credentials['password'],
                'login_profile_image' => $xmpp_credentials['login_profile_image'],
                'logged_in_id' => $xmpp_credentials['logged_in_id'],
                'alert_sound' => get_site_url().'/wp-content/uploads/sounds/new_message.mp3',
                'alert_sound_system' => get_site_url().'/wp-content/uploads/sounds/announcement.mp3',
                'can_log_with_db' => (int)get_option('fl_log_chat_to_db',0)
            ));

            wp_enqueue_script('chatstates', get_template_directory_uri() . '/js/strophe/strophe.chatstates.js', array('strophe'), '1.0.0');
            wp_enqueue_script('disco', get_template_directory_uri() . '/js/strophe/strophe.disco.js', array('strophe'), '1.0.0');
            wp_enqueue_script('ping', get_template_directory_uri() . '/js/strophe/strophe.ping.js', array('strophe'), '1.0.0');
            wp_enqueue_script('register', get_template_directory_uri() . '/js/strophe/strophe.register.js', array('strophe'), '1.0.0');
            wp_enqueue_script('roster', get_template_directory_uri() . '/js/strophe/strophe.roster.js', array('strophe'), '1.0.0');
            wp_enqueue_script('pubsub', get_template_directory_uri() . '/js/strophe/strophe.pubsub.js', array('strophe'), '1.0.0');
            wp_enqueue_script('muc', get_template_directory_uri() . '/js/strophe/strophe.muc.js', array('strophe'), '1.0.0');

            wp_enqueue_script('chat', get_template_directory_uri() . '/js/current-scripts/chat/chat.js', array('strophe'), '1.0.1');

            wp_enqueue_script('js-cookie', get_template_directory_uri() . '/js/lib/js.cookie-2.2.1.min.js', array('chat'), '1.0.0');
            wp_enqueue_script('jquery-time-ago', get_template_directory_uri() . '/js/lib/time-ago-in-words.js', array('custom_xmpp'), '1.0.0');
            wp_enqueue_script('xml-2-json', get_template_directory_uri() . '/js/xtojs/xml2json.min.js', array('chat'), '1.2.0');
        }
        wp_enqueue_style( 'emoji-picker', get_template_directory_uri() . '/css/jquery.emojipicker.css', array(), '1.0.1', 'all' );
        wp_enqueue_style( 'emoji-picker-tw', get_template_directory_uri() . '/css/jquery.emojipicker.tw.css', array(), '1.0.1', 'all' );
//
        wp_enqueue_script( 'emojipicker', get_template_directory_uri() . '/js/emoji/jquery.emojipicker.js', array( 'jquery' ), '1.0.1', true );
        wp_enqueue_script( 'emojis', get_template_directory_uri() . '/js/emoji/jquery.emojis.js', array( 'jquery' ), '1.0.1', true);
        wp_enqueue_script( 'emoji-emojiconnect', get_template_directory_uri() . '/js/emoji/emojiconnect.js', array( 'jquery', 'emojis' ), '1.0.1', true);
        wp_enqueue_script('custom_xmpp', get_template_directory_uri() . '/js/current-scripts/chat/custom_xmpp.js', array('chat', 'emojipicker'), '1.0.3');
    }


    public static function enqueue_backend() {

        wp_enqueue_script( 'translator_admin_script', get_template_directory_uri() . '/includes/admin-init/js/script.js','', '1.0.0', true );
        wp_localize_script( 'translator_admin_script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
        wp_enqueue_script( 'translator-admin', get_template_directory_uri() . '/includes/admin-init/js/freelinguist-admin.js','', '1.0.1', true );
        wp_enqueue_script( 'translator_admin_per_id_script', get_template_directory_uri() . '/includes/admin-init/js/interest-per-id.js','', '1.0.1', true );
        wp_enqueue_script( 'translator_admin_cancel_contest_script', get_template_directory_uri() . '/includes/admin-init/js/contest-cancel.js','', '1.0.0', true );
        wp_enqueue_style( 'translator_admin_style', get_template_directory_uri() . '/includes/admin-init/css/admin-style.css', array(), '1.0.2', 'all');
        wp_enqueue_style( 'wallet-history', get_template_directory_uri() . '/includes/admin-init/css/wallet-history-table.css', array(), '1.0', 'all');

        wp_enqueue_style('font-awesome', get_template_directory_uri() . '/css/font-awesome-4.7.0/css/font-awesome.min.css', array(), '4.7.0', 'all');

        wp_enqueue_style( 'translator_admin_per_id_style', get_template_directory_uri() . '/includes/admin-init/css/interest-per-id.css', array(), '1.1', 'all');
        wp_enqueue_style( 'translator_admin_cron_health', get_template_directory_uri() . '/includes/admin-init/css/health-cron.css', array(), '1.0', 'all');

        wp_enqueue_style( 'ejabber-guest-style', get_template_directory_uri() . '/includes/admin-init/css/ejabber-guest.css', array(), '1.0', 'all');
        wp_enqueue_script('moment-js', get_template_directory_uri() . '/js/lib/moment.js', array('jquery'), '');
        wp_enqueue_style( 'translator_admin_concel_contest_style', get_template_directory_uri() . '/includes/admin-init/css/contest-cancel.css', array(), '1.0', 'all');
        wp_enqueue_script('datatables', get_template_directory_uri() . '/js/datatables/jquery.dataTables.js', array('jquery'),'1.10.19',true);
        wp_enqueue_style('datatables', get_template_directory_uri() . '/css/datatables/jquery.dataTables.css', array(),'1.10.19','all');

        wp_enqueue_style('tingle', get_template_directory_uri() . '/js/lib/tingle.css', array(),'0.15.2','all');
        wp_enqueue_script( 'tingle', get_template_directory_uri() . '/js/lib/tingle.js','', '0.15.2', true );


        wp_enqueue_style('fl-frontend-and-admin', get_template_directory_uri() . '/css/current-code/frontend-and-admin.css', array(), '1.1', 'all');

        $jquery_ui_screens = [
            'freelinguist-admin-widthdrawls',
            'freelinguist-admin-refill-history',
            'freelinguist-admin-evaluation',
            'freelinguist-admin-manual-refill',
            'freelinguist-admin-message-to',
            'freelinguist-admin-chat-broadcast',
            'freelinguist-admin-check-wallet',
            AdminPageTxn::PAGE_STUB
        ];
        if (!empty($_GET['page']) && in_array($_GET['page'],$jquery_ui_screens)) {
            //code-notes add last version of jquery-ui to these admin screens only
            wp_enqueue_script('jquery-ui', get_template_directory_uri() . '/js/jquery-ui/jquery-ui.js', array('jquery'), '1.12.1');
            wp_enqueue_style('jquery-ui', get_template_directory_uri() . '/js/jquery-ui/jquery-ui.css', array(), '1.12.1', 'all');
        }

        $date_picker_screens = [
            AdminPageTxn::PAGE_STUB
        ];

        if (!empty($_GET['page']) && in_array($_GET['page'],$date_picker_screens)) {
            //code-notes add last version of jquery-ui to these admin screens only
            wp_enqueue_script('datepicker', get_template_directory_uri() . '/js/lib/datepickerjs.js', array('jquery'), '1.0.0');
        }



        $bootstrap_screens = [
            'freelinguist-admin-reports',
            'freelinguist-admin-content-cases',
            'freelinguist-admin-contest-cases',
            'freelinguist-admin-homepage-interest',
            AdminPageTxn::PAGE_STUB
        ];

        if (!empty($_GET['page']) && in_array($_GET['page'],$bootstrap_screens)) {
            //code-notes add bootstrap  to these admin screens only
            wp_enqueue_style('datatables-bootstrap', get_template_directory_uri() . '/css/datatables/dataTables.bootstrap.css', array(), '1.12.1', 'all');
            wp_enqueue_script('bootstrap', get_template_directory_uri().'/js/lib/bootstrap.js', array('jquery'), '',true);
            wp_enqueue_style('bootstrap-style', get_template_directory_uri() . '/css/lib/bootstrap.css', array(), '1.0', 'all');
        }


        $datatable_bootstrap_screens = [
            'freelinguist-admin-reports',
            'freelinguist-admin-content-cases',
            'freelinguist-admin-contest-cases'
        ];

        if (!empty($_GET['page']) && in_array($_GET['page'],$datatable_bootstrap_screens)) {
            //code-notes add bootstrap data-tables to these admin screens only
            wp_enqueue_style('datatables-bootstrap', get_template_directory_uri() . '/css/datatables/dataTables.bootstrap.css', array(), '1.12.1', 'all');
            wp_enqueue_script('datatables-bootstrap', get_template_directory_uri() . '/js/datatables/dataTables.bootstrap.min.js', array('bootstrap'), '1.10.1',true);
        }
    }






    public static function fl_require()
    {
        static::require_start_here_first();
        static::require_internals();
        static::require_deprecated();
        static::require_hooks();
        static::require_api();
        static::require_units();
        static::require_cron();
        static::require_admin();

    }

    protected static function require_start_here_first() {
        require_once ( 'includes/constants.php');
        require_once('includes/global-functions/internal/sitewide/will-debug.php'); //before others
        require_once('includes/global-functions/internal/sitewide/FLInput.php');
        require_once('includes/global-functions/internal/sitewide/FreelinguistDebugging.php');
        require_once('includes/global-functions/internal/sitewide/FreelinguistDebugFramework.php');
    }
    protected static function require_internals() {
        require_once('includes/global-functions/internal/sitewide/will/JsonHelper.php');
        require_once('includes/global-functions/internal/sitewide/addToFav.php');
        require_once('includes/global-functions/internal/sitewide/amount_format.php');
        require_once('includes/global-functions/internal/sitewide/amountWithReferralProcessingFee.php');


        require_once('includes/global-functions/internal/sitewide/array_sort.php');
        require_once('includes/global-functions/internal/sitewide/budget_formatter.php');


        require_once('includes/global-functions/internal/sitewide/change_the_pending_job_id.php');
        require_once('includes/global-functions/internal/sitewide/change_the_project_id.php');

        require_once('includes/global-functions/internal/sitewide/contest-cancellation.php');

        require_once('includes/global-functions/internal/sitewide/change_transaction_id.php');
        require_once('includes/global-functions/internal/sitewide/check_bid_exist.php');
        require_once('includes/global-functions/internal/sitewide/check_login_redirection.php');
        require_once('includes/global-functions/internal/sitewide/check_login_redirection_home.php');
        include_once('includes/global-functions/internal/sitewide/class.pdf2text.php');;
        require_once('includes/global-functions/internal/sitewide/convert_rating.php');
        require_once('includes/global-functions/internal/sitewide/current_language.php');

        require_once('includes/global-functions/internal/sitewide/delete_logged_in_user_profile_image_internal.php');
        include_once('includes/global-functions/internal/sitewide/document-word-count-function.php');
        require_once('includes/global-functions/internal/sitewide/email-templates-function.php');
        require_once('includes/global-functions/internal/sitewide/getSocialUserType.php');

        require_once('includes/global-functions/internal/sitewide/fpdf.php');
        require_once('includes/global-functions/internal/sitewide/FileUploadWhitelist.php');


        require_once('includes/global-functions/internal/sitewide/fl_generate_language_array_for_js_object.php');
        require_once('includes/global-functions/internal/sitewide/fl_get_job_pay_status.php');
        require_once('includes/global-functions/internal/sitewide/fl_message_insert.php');
        require_once('includes/global-functions/internal/sitewide/fl_switch_user_helper.php');
        require_once('includes/global-functions/internal/sitewide/fl_transaction_insert.php');
        require_once('includes/global-functions/internal/sitewide/fl_transaction_lookup_helper.php');
        require_once('includes/global-functions/internal/sitewide/fl_wp_file_helper.php');
        require_once('includes/global-functions/internal/sitewide/FLPostLookupDataHelpers.php');

        require_once('includes/global-functions/internal/sitewide/FLRedDot.php');
        require_once('includes/global-functions/internal/sitewide/FLRedDotFutureActions.php');
        require_once('includes/global-functions/internal/sitewide/freeling_links.php');

        require_once('includes/global-functions/internal/sitewide/freelinguist_check_user_download_permissions.php');
        require_once('includes/global-functions/internal/sitewide/freelinguist_fa_icons.php');
        require_once('includes/global-functions/internal/sitewide/freelinguist_print_content_units.php');
        require_once('includes/global-functions/internal/sitewide/freelinguist_user_get_local_time.php');


        require_once('includes/global-functions/internal/sitewide/FreelinguistProjectAndContestHelper.php');


        require_once('includes/global-functions/internal/sitewide/FreelinguistSizeImages.php');
        require_once('includes/global-functions/internal/sitewide/FreelinguistStaticPageLogic.php');
        require_once('includes/global-functions/internal/sitewide/FreelinguistTags.php');
        require_once('includes/global-functions/internal/sitewide/FreelinguistUserHelper.php');

        require_once('includes/global-functions/internal/sitewide/FreelinguistUserLookupHelper.php');

        require_once('includes/global-functions/internal/sitewide/gen_pjob_title.php');
        require_once('includes/global-functions/internal/sitewide/generateTempJob.php');
        require_once('includes/global-functions/internal/sitewide/get_countries.php');

        require_once('includes/global-functions/internal/sitewide/get_custom_string.php');
        require_once('includes/global-functions/internal/sitewide/get_custom_string_return.php');


        require_once('includes/global-functions/internal/sitewide/get_all_bids_of_particular_job.php');
        require_once('includes/global-functions/internal/sitewide/get_country_by_index.php');
        require_once('includes/global-functions/internal/sitewide/get_custom_string_by_id.php');

        require_once('includes/global-functions/internal/sitewide/get_da_name.php');

        require_once('includes/global-functions/internal/sitewide/get_display_name.php');
        require_once('includes/global-functions/internal/sitewide/get_header_menu_string.php');
        require_once('includes/global-functions/internal/sitewide/get_index_by_country.php');
        require_once('includes/global-functions/internal/sitewide/get_job_delivery_date.php');

        require_once('includes/global-functions/internal/sitewide/get_logo_by_current_language.php');


        require_once('includes/global-functions/internal/sitewide/getColor.php');
        require_once('includes/global-functions/internal/sitewide/getLocationInfoByIp.php');

        require_once('includes/global-functions/internal/sitewide/getReferralProcessingCharges.php');

        require_once('includes/global-functions/internal/sitewide/hz_bid_exist.php');
        require_once('includes/global-functions/internal/sitewide/hz_bid_id_slug.php');

        require_once('includes/global-functions/internal/sitewide/hz_check_latest_milestone_status.php');
        require_once('includes/global-functions/internal/sitewide/hz_check_latest_proposal_status.php');



        require_once('includes/global-functions/internal/sitewide/fl_check_ssl_bundle.php');
        require_once('includes/global-functions/internal/sitewide/hz_fl_content_discussion_list_both.php');
        require_once('includes/global-functions/internal/sitewide/hz_fl_content_discussion_list_both2.php');
        require_once('includes/global-functions/internal/sitewide/hz_fl_content_discussion_list_public.php');


        require_once('includes/global-functions/internal/sitewide/hz_fl_discussion_list_both.php');
        require_once('includes/global-functions/internal/sitewide/hz_fl_discussion_list_both2_child.php');
        require_once('includes/global-functions/internal/sitewide/hz_fl_discussion_list_public.php');


        require_once('includes/global-functions/internal/sitewide/hz_get_job_data.php');

        require_once('includes/global-functions/internal/sitewide/hz_get_pro_name.php');
        require_once('includes/global-functions/internal/sitewide/hz_get_profile_thumb.php');

        require_once('includes/global-functions/internal/sitewide/hz_is_linguist_asg.php');


        require_once('includes/global-functions/internal/sitewide/hz_linguist_job_id.php');

        require_once('includes/global-functions/internal/sitewide/hz_project_asso_linguist.php');


        require_once('includes/global-functions/internal/sitewide/job_rating.php');


        require_once('includes/global-functions/internal/sitewide/m-date-formated-update-timezone.php');


        require_once('includes/global-functions/internal/sitewide/pagination.php');

        require_once('includes/global-functions/internal/sitewide/pdf.php');

        require_once('includes/global-functions/internal/sitewide/modify_this_site_query_string.php');


        require_once('includes/global-functions/internal/sitewide/refundBidAmount_JobCancelByCustomer.php');
        require_once('includes/global-functions/internal/sitewide/removePersonalInfo.php');
        require_once('includes/global-functions/internal/sitewide/FreelinguistSearchFromDB.php');
        require_once('includes/global-functions/internal/sitewide/set_cookie_for_language.php');


        require_once('includes/global-functions/internal/sitewide/transaction_updated.php');

        require_once('includes/global-functions/internal/sitewide/translater_rating.php');

        require_once('includes/global-functions/internal/sitewide/update_customer_average_rating.php');


        require_once('includes/global-functions/internal/sitewide/update_freelancer_average_rating.php');
        include_once('includes/global-functions/internal/sitewide/UploadHandler.php');

        require_once('includes/global-functions/internal/sitewide/FreelinguistUserLookupDataHelpers.php');

        require_once('includes/global-functions/internal/sitewide/viewOffersHtml.php');

        require_once('includes/global-functions/internal/sitewide/xt_user_role.php');
        require_once('includes/global-functions/internal/sitewide/z-curl-functions.php');


//admin functions


        require_once('includes/global-functions/internal/admin/get_message_users_for_message_table.php');


        require_once('includes/global-functions/internal/admin/get_processing_id.php');
        require_once('includes/global-functions/internal/admin/getReportedSubAdmin.php');
        require_once('includes/global-functions/internal/admin/getReportedSuperSubAdmin.php');
        require_once('includes/global-functions/internal/admin/getReportedSubAdminOfSuperSubAdmin.php');


//payment functions


        require_once('includes/global-functions/internal/payment/fl_get_payment_post_status_string.php');

        require_once('includes/global-functions/internal/payment/fl_maybe_change_to_ngrok.php');
        require_once('includes/global-functions/internal/payment/fl_payment_gateways.php');
        require_once('includes/global-functions/internal/payment/fl_payment_history.php');

        require_once('includes/global-functions/internal/payment/fl_payment_history_stripe_intent_map.php');
        require_once('includes/global-functions/internal/payment/fl_payment_history_stripe_dispute_map.php');
        require_once('includes/global-functions/internal/payment/fl_payment_history_stripe_refund_map.php');
        require_once('includes/global-functions/internal/payment/fl_payment_history_paypal_map.php');
        require_once('includes/global-functions/internal/payment/fl_payment_history_ipn.php');
        require_once('includes/global-functions/internal/payment/fl_payment_summary.php');

        require_once('includes/global-functions/internal/payment/get_refill_processing_charges.php');

        require_once('includes/global-functions/internal/payment/getRefillAmount.php');
        require_once('includes/global-functions/internal/payment/getReportedUserByUserId.php');
        require_once('includes/global-functions/internal/payment/get_stripe_lang.php');
    }

    protected static function require_hooks() {
        require_once('includes/global-functions/global-hooks/add_meta_tags.php');
        require_once('includes/global-functions/global-hooks/form-key.php');
        require_once('includes/global-functions/global-hooks/post_title_trim.php');
        require_once('includes/global-functions/global-hooks/mail-hooks.php');
        require_once('includes/global-functions/global-hooks/freelinguist_permalink_filter.php');
        require_once('includes/global-functions/global-hooks/FreelinguistPreFlightCheck.php');//called from plugin


        require_once('includes/global-functions/internal/sitewide/FreelinguistPostRewrite.php');
        require_once('includes/global-functions/global-hooks/redirects.php');
    }

    protected static function require_api() {
        require_once('includes/global-functions/api/ajax/add_favorite_content_cb.php');
        require_once('includes/global-functions/api/ajax/change_role_cus_ling.php');

        require_once('includes/global-functions/api/ajax/check_is_user_login.php');
        require_once('includes/global-functions/api/ajax/check_cart_empty_or_not.php');
        require_once('includes/global-functions/api/ajax/create_new_job.php');


        require_once('includes/global-functions/api/ajax/cvf_upload_files_content_process.php');
        require_once('includes/global-functions/api/ajax/cvf_upload_files_order_process.php');
        require_once('includes/global-functions/api/ajax/cvf_upload_files_order_process_new.php');
        require_once('includes/global-functions/api/ajax/cvf_upload_text_files_content_process.php');


        require_once('includes/global-functions/api/ajax/delete_content_chapter.php');
        require_once('includes/global-functions/api/ajax/delete_content_file.php');
        require_once('includes/global-functions/api/ajax/delete_job.php');
        require_once('includes/global-functions/api/ajax/delete_linguist_content.php');
        require_once('includes/global-functions/api/ajax/delete_linguist_content_multiple.php');


        require_once('includes/global-functions/api/ajax/delete_my_account.php');

        require_once('includes/global-functions/api/ajax/delete_user_profile_image.php');
        require_once('includes/global-functions/api/ajax/download_tax_form.php');


        include_once('includes/global-functions/api/ajax/EjabberdWrapper.php');

        include_once('includes/global-functions/api/ajax/email_the_form.php');
        include_once('includes/global-functions/api/ajax/fl_content_new_chapter_content_form_part.php');
        require_once('includes/global-functions/api/ajax/fl_generate_user_referral_code.php');
        require_once('includes/global-functions/api/ajax/fl_log_chat.php');
        require_once('includes/global-functions/api/ajax/FreelinguistContentHelper.php');
        require_once('includes/global-functions/api/ajax/freelinguist_get_charge_list.php');
        require_once('includes/global-functions/api/ajax/freeling_reset_user_password.php');

        require_once('includes/global-functions/api/ajax/freelinguist_get_file_download_url.php');

        require_once('includes/global-functions/api/ajax/freelinguist_get_wallet_amount.php');


        require_once('includes/global-functions/api/ajax/freelinguist_proposal_file_delete.php');


        require_once('includes/global-functions/api/ajax/freelinguist_proposal_list.php');

        require_once('includes/global-functions/api/ajax/freelinguist_proposal_seal.php');
        require_once('includes/global-functions/api/ajax/freelinguist_proposals_view.php');

        require_once('includes/global-functions/api/ajax/freelinguist_return_content_units.php');
        require_once('includes/global-functions/api/ajax/freelinguist_user_get_local_time_ajax.php');


        require_once('includes/global-functions/api/ajax/FreelinguistRefreshChatCredentials.php');

        require_once('includes/global-functions/api/ajax/generateOrderByCustomerNew.php');
        require_once('includes/global-functions/api/ajax/get_custom_tags.php');

//

        require_once('includes/global-functions/api/ajax/get_highest_bid.php');
        require_once('includes/global-functions/api/ajax/get_latest_all_offers.php');

        require_once('includes/global-functions/api/ajax/hide_job_ajax.php');


        require_once('includes/global-functions/api/ajax/hirelinguistByCustomer.php');
        require_once('includes/global-functions/api/ajax/hireTranslate.php');

        require_once('includes/global-functions/api/ajax/hz_add_participate_cb.php');
        require_once('includes/global-functions/api/ajax/hz_approve_milestone_cb.php');

        require_once('includes/global-functions/api/ajax/hz_awardprize_to_proposal_cb.php');

        require_once('includes/global-functions/api/ajax/hz_buy_content_ajxcback_cb.php');
        require_once('includes/global-functions/api/ajax/hz_change_status_content.php');

        require_once('includes/global-functions/api/ajax/hz_change_status_contest_proposal.php');
        require_once('includes/global-functions/api/ajax/hz_complete_contest_proposal_cb.php');


        require_once('includes/global-functions/api/ajax/hz_content_translator_feedback_cb.php');
        require_once('includes/global-functions/api/ajax/hz_content_customer_feedback_cb.php');


        require_once('includes/global-functions/api/ajax/hz_contest_data_proc_cb.php');


        require_once('includes/global-functions/api/ajax/hz_contest_new_proposal_data_proc_save.php');
        require_once('includes/global-functions/api/ajax/hz_contest_new_proposal_data_proc_cb.php');
        require_once('includes/global-functions/api/ajax/hz_contest_update_proposal_data_proc_cb.php');


        require_once('includes/global-functions/api/ajax/hz_create_milestone_cb.php');


        require_once('includes/global-functions/api/ajax/hz_manage_job_status_cb.php');
        require_once('includes/global-functions/api/ajax/hz_manage_milestone_cb.php');

        require_once('includes/global-functions/api/ajax/hz_Offer_accept_reject_cb.php');
        require_once('includes/global-functions/api/ajax/hz_Offer_send_cus_cb.php');
        require_once('includes/global-functions/api/ajax/hz_post_fl_content_discussion_cb.php');
        require_once('includes/global-functions/api/ajax/hz_post_fl_discussion_cb.php');

        require_once('includes/global-functions/api/ajax/hz_project_customer_feedback.php');
        require_once('includes/global-functions/api/ajax/hz_project_translator_feedback_cb.php');
        require_once('includes/global-functions/api/ajax/hz_proposal_customer_feedback_cb.php');
        require_once('includes/global-functions/api/ajax/hz_proposal_freelancer_feedback_cb.php');


//
        require_once('includes/global-functions/api/ajax/hz_submit_report_cb.php');


        require_once('includes/global-functions/api/ajax/job_description_editable.php');
        require_once('includes/global-functions/api/ajax/job_instruction_editable.php');

        require_once('includes/global-functions/api/ajax/job_tags_editable.php');
        require_once('includes/global-functions/api/ajax/logout_me.php');

        require_once('includes/global-functions/api/ajax/newUserResendConfirmationEmail.php');

        require_once('includes/global-functions/api/ajax/place_the_bid.php');
        require_once('includes/global-functions/api/ajax/project_job_file_upload.php');

        require_once('includes/global-functions/api/ajax/project_single_job_file_upload.php');


        require_once('includes/global-functions/api/ajax/reactive_my_account.php');
        require_once('includes/global-functions/api/ajax/recieptInfo.php');
        require_once('includes/global-functions/api/ajax/recieptInfoOrder.php');
        require_once('includes/global-functions/api/ajax/register_trans_user.php');
        require_once('includes/global-functions/api/ajax/reg_email.php');


        require_once('includes/global-functions/api/ajax/remove_all_files.php');


        require_once('includes/global-functions/api/ajax/selected_file_remove.php');

        require_once('includes/global-functions/api/ajax/show_job.php');
        require_once('includes/global-functions/api/ajax/showproposal_slider.php');
        require_once('includes/global-functions/api/ajax/socialUserTypeLoginChange.php');


        require_once('includes/global-functions/api/ajax/update_customer_personal_info_data.php');

        require_once('includes/global-functions/api/ajax/update_local_timezone.php');


        require_once('includes/global-functions/api/ajax/update_linguist_certification_info.php');


        require_once('includes/global-functions/api/ajax/update_address_details.php');
        require_once('includes/global-functions/api/ajax/update_display_name.php');


        require_once('includes/global-functions/api/ajax/update_email_preference.php');
        require_once('includes/global-functions/api/ajax/update_linguist_edu_info.php');
        require_once('includes/global-functions/api/ajax/update_linguist_language_info.php');
        require_once('includes/global-functions/api/ajax/update_linguist_related_experience_info.php');
        require_once('includes/global-functions/api/ajax/update_payment_preference.php');
        require_once('includes/global-functions/api/ajax/update_payment_mehod_account_detail.php');


        require_once('includes/global-functions/api/ajax/update_personal_info_data.php');
        require_once('includes/global-functions/api/ajax/update_price_by_date.php');
        require_once('includes/global-functions/api/ajax/update_proposal_rating.php');

        require_once('includes/global-functions/api/ajax/update_RequestEvaluation_info.php');
        require_once('includes/global-functions/api/ajax/update_summary_info_data.php');

        require_once('includes/global-functions/api/ajax/update_user_email.php');
        require_once('includes/global-functions/api/ajax/update_user_email_notifications.php');
        require_once('includes/global-functions/api/ajax/update_user_password_change.php');
        require_once('includes/global-functions/api/ajax/update_withdraw_preference.php');


        require_once('includes/global-functions/api/ajax/updateOrderByCustomer.php');


        require_once('includes/global-functions/api/ajax/uploadSignedTaxForm.php');

        require_once('includes/global-functions/api/ajax/user_add_favorite.php');
        require_once('includes/global-functions/api/ajax/user_image_file.php');
        require_once('includes/global-functions/api/ajax/user_image_file_reminder.php');


        require_once('includes/global-functions/api/ajax/settings/delete_certificate_info.php');
        require_once('includes/global-functions/api/ajax/settings/delete_education_info.php');
        require_once('includes/global-functions/api/ajax/settings/delete_language_info.php');
        require_once('includes/global-functions/api/ajax/settings/delete_related_work_experience.php');


//admin api


        require_once('includes/global-functions/api/admin/ajax/check_linguist_email_id_exist_or_not.php');
        require_once('includes/global-functions/api/admin/ajax/delete_profile_attachment.php');
        require_once('includes/global-functions/api/admin/ajax/delete_resume_attachment.php');

        require_once('includes/global-functions/api/admin/ajax/fl_update_txn_description.php');
        require_once('includes/global-functions/api/admin/ajax/get_linguist_list_autocomplete.php');
        require_once('includes/global-functions/api/admin/ajax/getToCountryBySuperAdmin.php');
        require_once('includes/global-functions/api/admin/ajax/getToProcessingIdBySuperAdmin.php');

        require_once('includes/global-functions/api/admin/ajax/getAssignCountryTo.php');
        require_once('includes/global-functions/api/admin/ajax/get_user_list_by_autocomplete.php');


        require_once('includes/global-functions/api/admin/ajax/hz_con_delete.php');

        require_once('includes/global-functions/api/admin/ajax/hz_con_show_hide.php');


        require_once('includes/global-functions/api/admin/ajax/hz_content_freeze.php');
        require_once('includes/global-functions/api/admin/ajax/hz_contentpartialPay_save.php');
        require_once('includes/global-functions/api/admin/ajax/hz_contentStatus_save.php');


        require_once('includes/global-functions/api/admin/ajax/hz_contest_freeze.php');

        require_once('includes/global-functions/api/admin/ajax/hz_conStatus_save.php');
        require_once('includes/global-functions/api/admin/ajax/hz_partialPay_save.php');
        require_once('includes/global-functions/api/admin/ajax/hz_reportAdminnote_save.php');
        require_once('includes/global-functions/api/admin/ajax/hz_reportStatus_save.php');


        require_once('includes/global-functions/api/admin/ajax/interest_is_title_hidden.php');
        require_once('includes/global-functions/api/admin/ajax/interest_tag_delete.php');
        require_once('includes/global-functions/api/admin/ajax/interest_tag_update.php');
        require_once('includes/global-functions/api/admin/ajax/interest_tags_delete.php');

        require_once('includes/global-functions/api/admin/ajax/per_id_interest_create.php');
        require_once('includes/global-functions/api/admin/ajax/per_id_interest_delete.php');
        require_once('includes/global-functions/api/admin/ajax/per_id_interest_list.php');

        require_once('includes/global-functions/api/admin/ajax/update_message_revison_history.php');



//admin hooks
        require_once('includes/global-functions/api/admin/hooks/admin_language.php');
        require_once('includes/global-functions/api/admin/hooks/disable_multiple_role_selection.php');
        require_once('includes/global-functions/api/admin/hooks/disable_new_posts.php');
        require_once('includes/global-functions/api/admin/hooks/fl_on_delete_wp_post.php');
        require_once('includes/global-functions/api/admin/hooks/fl_on_delete_wp_user.php');

        require_once('includes/global-functions/api/admin/hooks/my_show_extra_profile_fields.php');
        require_once('includes/global-functions/api/admin/hooks/mypo_parse_query_useronly.php');
        require_once('includes/global-functions/api/admin/hooks/new_contact_methods.php');
        require_once('includes/global-functions/api/admin/hooks/new_modify_user_table.php');
        require_once('includes/global-functions/api/admin/hooks/new_modify_user_table_row.php');


        require_once('includes/global-functions/api/admin/hooks/redirects.php');
        require_once('includes/global-functions/api/admin/hooks/remove_users_columns_for_all.php');
        require_once('includes/global-functions/api/admin/hooks/remove_dashboard_widgets.php');
        require_once('includes/global-functions/api/admin/hooks/remove_menus.php');
        require_once('includes/global-functions/api/admin/hooks/revise_comment_column.php');
        require_once('includes/global-functions/api/admin/hooks/revise_comment_columns.php');
        require_once('includes/global-functions/api/admin/hooks/save_extra_user_profile_fields.php');


        include_once('includes/global-functions/api/admin/hooks/transaction-post-types.php');


//payment api


        require_once('includes/global-functions/api/payment/ajax/fl_get_refill_processing_fee.php');
        require_once('includes/global-functions/api/payment/ajax/paypal-ipn-verify.php'); //code-notes before the paypal handler
        require_once('includes/global-functions/api/payment/ajax/fl_paypal_handler.php');
        require_once('includes/global-functions/api/payment/ajax/fl_stripe_create_payment_intent.php');
        require_once('includes/global-functions/api/payment/ajax/fl_stripe_webhook.php');

        require_once('includes/global-functions/api/payment/ajax/requestWithdraw_info.php');

        require_once('includes/global-functions/api/payment/ajax/update_coupon_info.php');


//payment hooks
        require_once('includes/global-functions/api/payment/hooks/fl_register_post_status_pending_failed.php');
    }

    protected static function require_units() {
        require_once('includes/units/FreelinguistUnitDataAdapter.php');
        require_once('includes/units/FreelinguistUnitDisplaySingleUnit.php');
        require_once('includes/units/FreelinguistUnitDisplayHomepageTagRow.php');
        require_once('includes/units/FreelinguistUnitGenerator.php');
        require_once('includes/units/FreelinguistUnitDisplay.php');
    }

    protected static function require_cron() {
        require_once ('includes/all-cron/elastic-search-helper.php');
        require_once('includes/all-cron/FreelinguistCronBase.php');
        require_once('includes/all-cron/FreelinguistCronDeleteTempData.php');
        require_once('includes/all-cron/FreelinguistCronESProjects.php');
        require_once('includes/all-cron/FreelinguistCronESUsers.php');
        require_once('includes/all-cron/FreelinguistCronESContests.php');
        require_once('includes/all-cron/FreelinguistCronESContent.php');
        require_once('includes/all-cron/FreelinguistCronESContent.php');
        require_once('includes/all-cron/FreelinguistCronTopUnitsClear.php');
        require_once('includes/all-cron/FreelinguistCronTopUnitsGenerate.php');
        require_once('includes/all-cron/FreelinguistCronBaseRepeat.php');
        require_once('includes/all-cron/FreelinguistCronTopUnitsTimerRepeat.php');
        require_once('includes/all-cron/FreelinguistCronTopUnitsRebuildAll.php');
        require_once('includes/all-cron/FreelinguistCronRedDotActions.php');
        require_once('includes/all-cron/FreelinguistCronHealthCheckRepeat.php');
    }

    protected static function require_admin() {
        require_once('includes/admin-init/admin-page-function-settings-theme-panel.php');
        require_once('includes/admin-init/admin-page-function-settings-logo-panel.php');
        require_once('includes/admin-init/admin-page-function-settings-payment-panel.php');
        require_once('includes/admin-init/admin-page-function-settings-society-panel.php');
        require_once('includes/admin-init/admin-page-function-settings-customer-order.php');
        require_once('includes/admin-init/admin-page-function-settings-theme-older.php');


        include_once('includes/admin-init/admin-page-message-to.php');
        require_once('includes/admin-init/admin-page-chat-broadcast.php');
        require_once('includes/admin-init/admin-page-homepage-interest.php');
        require_once('includes/admin-init/admin-page-maintenance.php');
        require_once('includes/admin-init/admin-page-social-email-contacts.php');
        require_once('includes/admin-init/admin-page-evaluations.php');
        require_once('includes/admin-init/admin-page-email-templates.php');
        require_once('includes/admin-init/admin-page-withdrawal.php');
        require_once('includes/admin-init/admin-page-refill-history.php');
        require_once('includes/admin-init/admin-page-function-check-wallet.php');

        require_once('includes/admin-init/admin-page-function-refill-manually.php');
        require_once('includes/admin-init/admin-page-function-reports.php');

        require_once('includes/admin-init/admin-page-function-send-message-cashier.php');
        require_once('includes/admin-init/admin-page-evaluation-history.php');
        require_once('includes/admin-init/admin-page-coordination.php');
        require_once('includes/admin-init/admin-page-string-translation.php');
        require_once('includes/admin-init/admin-page-menu-translation.php');
        require_once('includes/admin-init/admin-page-project-cases.php');
        require_once('includes/admin-init/admin-page-contest-cancel-requests.php');
        require_once('includes/admin-init/admin-page-test-units.php');
        require_once('includes/admin-init/admin-page-txn.php'); //code-notes new admin page for transactions
        require_once('includes/admin-init/admin-user-page-menu-batch-chat.php');

        require_once('includes/admin-init/admin-page-check-wallet-amount.php');
        require_once('includes/admin-init/admin-user-page-social-export.php');
        require_once('includes/admin-init/admin-page-content.php');

        require_once('includes/admin-init/admin-page-function-content-cases.php');
        require_once('includes/admin-init/admin-page-function-contest-cases.php');
        require_once('includes/admin-init/admin-page-function-tag-list.php');
        require_once('includes/admin-init/admin-page-function-reminders.php');


        require_once('includes/admin-init/freelinguist_make_admin_pages.php'); //needs to be after all the other admin pages
    }

    protected static function require_deprecated() {

// deprecated
        require_once('includes/global-functions/deprecated/download_job_file.php');
        require_once('includes/global-functions/deprecated/get_job_related_technical_level.php');
    }



}