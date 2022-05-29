<?php
if (!defined('WPINC')) {
    die;
}

add_action("admin_menu", "freelinguist_make_admin_pages",8); //needs to run first before the other admin menu callbacks
freelinguist_start_admin_pages();



/*File for manage theme settings*/
function freelinguist_make_admin_pages()
{


    //code-notes new admin menus, commented out constructors are there for placement notes
    //settings

    add_menu_page("Freeling Options", "PeerOK Settings", "manage_options",
        "freelinguist-admin-options", "theme_settings_panel", null, 501);
    add_submenu_page("freelinguist-admin-options", "Payment Info", "Payment Info",
        "manage_options", "payment-info-val", "payment_info_val",1);
    add_submenu_page("freelinguist-admin-options", "Order Options", "Order Options",
        "manage_options", "theme-option-val", "theme_option_val_render",2);
    add_submenu_page("freelinguist-admin-options", "XMPP, Fee, Auto Action, etc", "XMPP, Fee, Auto Action, etc",
        "manage_options", "customer-theme-option-val", "customer_theme_option_val",3);
    add_submenu_page("freelinguist-admin-options", "Logo Setting", "Logo Setting",
        "manage_options", "customer-theme-logo-val", "customer_theme_logo_val",4);

    add_submenu_page("freelinguist-admin-options", "Society Setting", "Society Setting",
        "manage_options", "society-settings", "fl_admin_society_settings_panel",9);

//    new AdminPageEmailTemplates("freelinguist-admin-options",5);
//    new AdminPageMenuTranslation("freelinguist-admin-options",6);
//    new AdminPageStringTranslations("freelinguist-admin-options",7);


    add_submenu_page("freelinguist-admin-options","Reminder email Options", "Reminder email Opt.", "manage_options",
        "freelinguist-admin-reminders", "theme_reminder_email", 8);



    //Content Contests and Projects

    add_menu_page('Reports', 'PeerOK Jobs', 'manage_options',
        'freelinguist-admin-reports', 'function_for_reports', 'dashicons-welcome-widgets-menus',502);

//    new AdminPageContent('freelinguist-admin-reports',1);
    add_submenu_page('freelinguist-admin-reports','Content Cases', 'Content Cases', 'manage_options',
        'freelinguist-admin-content-cases', 'function_for_content_cases', 2);

    add_submenu_page('freelinguist-admin-reports','Contest Cases', 'Contest Cases', 'manage_options',
        'freelinguist-admin-contest-cases', 'function_for_contest_cases', 3);
//    new AdminPageCancelContestRequest('freelinguist-admin-reports',4);

//    new AdminPageProjectCases('freelinguist-admin-reports',5);







    //wallet
//    new AdminPageRefillHistory();
//    new AdminPageCheckWalletAmount();

    add_menu_page("Check wallet", "PeerOK Wallet", "manage_options",
        "freelinguist-admin-check-wallet", "check_wallet", 'dashicons-info', 503);

//    new AdminPageWithdrawalRequest("freelinguist-admin-check-wallet",1);
    add_submenu_page("freelinguist-admin-check-wallet","Refill Account Manually", "Refill Account Manually", "manage_options",
        "freelinguist-admin-manual-refill", "refill_account_manually", 2);

    //users

    add_menu_page("user send message", "PeerOK Users", "manage_options",
        "freelinguist-admin-send-cashier-message", "send_meesage_cashior", 'dashicons-email', 504);


//    new AdminPageChatBroadcast("freelinguist-admin-send-cashier-message",1);
//    new AdminPageMessageTo("freelinguist-admin-send-cashier-message",2);
//    new AdminPageEvaluations("freelinguist-admin-send-cashier-message",3);

//    new AdminPageSocialEmailContacts("freelinguist-admin-send-cashier-message",4);

//    new AdminPageCoordination("freelinguist-admin-send-cashier-message",5);
//    new AdminPageEvalutionHistory("freelinguist-admin-send-cashier-message",6);


//    new AdminUserPageSocialExport(); //no args and on user menu
//    new AdminPageUserMenuBatchChat();//no args and on user menu

    //tags  units and cron



    add_menu_page('Interest Tag Options', 'Elastic, Tags, Homepage Units', 'manage_options',
        'freelinguist-admin-tags', 'interest_tag_options','dashicons-tag', 505);

    //new AdminPageHomepageInterest('freelinguist-admin-tags',1);

//    new AdminPageTestUnits('freelinguist-admin-tags',2);

}

function freelinguist_start_admin_pages() {


    //settings
    new AdminPageEmailTemplates("freelinguist-admin-options",5);
    new AdminPageMenuTranslation("freelinguist-admin-options",6);
    new AdminPageStringTranslations("freelinguist-admin-options",7);
    new AdminMaintenance("freelinguist-admin-options",20);




    //Content Contests and Projects


    new AdminPageContent('freelinguist-admin-reports',1);

    new AdminPageCancelContestRequest('freelinguist-admin-reports',4);

    new AdminPageProjectCases('freelinguist-admin-reports',5);







    //wallet
    new AdminPageRefillHistory(); //code-notes always top level, no children
    new AdminPageCheckWalletAmount();//code-notes always top level, no children

    new AdminPageWithdrawalRequest("freelinguist-admin-check-wallet",1);
    new AdminPageTxn("freelinguist-admin-check-wallet",10);



    //users

    new AdminPageChatBroadcast("freelinguist-admin-send-cashier-message",1);
    new AdminPageMessageTo("freelinguist-admin-send-cashier-message",2);
    new AdminPageEvaluations("freelinguist-admin-send-cashier-message",3);

    new AdminPageSocialEmailContacts("freelinguist-admin-send-cashier-message",4);

    new AdminPageCoordination("freelinguist-admin-send-cashier-message",5);
    new AdminPageEvalutionHistory("freelinguist-admin-send-cashier-message",6);


    new AdminUserPageSocialExport(); //no args and on user menu
    new AdminPageUserMenuBatchChat();//no args and on user menu

    //tags  units and cron

    new AdminPageHomepageInterest('freelinguist-admin-tags',1);

    new AdminPageTestUnits('freelinguist-admin-tags',2);
}

















 




