<?php
//curl https://cdn.jsdelivr.net/gh/jshttp/mime-db@master/db.json --output D:\Codes\test.com\wp-content\themes\the-translator\current-mimes.json

//  http://peerok.com/wp-content/debug.log

/*
 Actively needed plugins (list needs work)
    Action Scheduler
    Disable XML-RPC
    PeerOK Pre Flight Check
    GD Mail Queue
    Noindex Pages
    Simple History
    User Role Editor
    WPML Multilingual CMS
    WPS Hide Login

Needed for website functionality (do not update without testing)
    Action Scheduler
    PeerOK Pre Flight Check
    GD Mail Queue
    WPML Multilingual CMS
    User Role Editor

Plugins for security: Okay to update
        Noindex Pages
        Simple History
        WPS Hide Login
        Disable XML-RPC

Plugins unknown, probably not needed, but may be referenced in the pages stored only as posts
Classic Editor
Contact Form 7
TinyMCE Advanced



Plugins not needed
    Easy WP SMTP: we do not use this for mail
    Import users from CSV with meta: a lot of our user data is not known to this
    Export Users to CSV: same as above, it does not know what we need for data
    WordPress Social Login: not actively maintained on our side
 */


/*
this-task-done 1. priority number fixed bug in the unit display code, in the es section, when it orders the tags


this-task-done 3.  in the admin page, check mark to show which tags will be displayed;

this-task-done 4. better description in the tag editing section;

this-task-done Update HTML unit when someone enters a per id.
this-task-done Update HTML unit when someone edits or changes user or content setting
this-task-done check ajax below to make sure things work with units



task-future-work double check insurance for contests and prize sharing, and check they do it accurately

task-future-work fix tooltip placement for editing project tags

task-future-work Discussion Date Times are not in local time zones


task-future-work There is no usage of free credits anywhere, when purchasing content, or deducting fees in other places

task-future-work The pdf php library of   MPDF57 works fine, but its old and not sure when it will break or be insecure

task-future-work the table in db wp_payforview_seal is not used anywhere

task-future-work set up after using beta program WeChat https://stripe.com/docs/sources/wechat-pay

task-future-work withdraw preference is not used anywhere, and there is no code to find out the settings to the admin or automate the process

--------------------------------------

give images to these users
fcustomer1+t1@gmail.com


https://www.formget.com/paypal-ipn-php/ db table example

https://developer.paypal.com/docs/api-basics/notifications/ipn/IPNTesting/#ipn-troubleshooting-tips
https://www.paypal.com/uk/smarthelp/article/how-do-i-add-paypal-checkout-to-my-custom-shopping-cart-ts1200

CREATE TABLE IF NOT EXISTS `ipn_data_tbl` (
`TID` int(11) NOT NULL AUTO_INCREMENT,
`item_name` varchar(255) NOT NULL,
`payer_email` varchar(150) NOT NULL,
`first_name` varchar(150) NOT NULL,
`last_name` varchar(150) NOT NULL,
`amount` float NOT NULL,
`currency` varchar(50) NOT NULL,
`country` varchar(50) NOT NULL,
`txn_id` varchar(100) NOT NULL,
`txn_type` varchar(100) NOT NULL,
`payment_status` varchar(100) NOT NULL,
`payment_method` varchar(100) NOT NULL,
`create_date` datetime NOT NULL,
`payment_date` datetime NOT NULL,
PRIMARY KEY (`TID`)
)


need to record:
action varchar(10) Can be more than one handler
version int each handler can have a different version
item_name varchar(30) what is being bought

[17-Jan-2021 04:19:42 UTC] FLPaypalHandler Sun Jan 17, 2021 4:19:42 : got response
Array
(
    *[action] => fl_paypal
    [payment_type] => instant
    [payment_date] => 22:18:58 Jan 15, 2021 PST
    [payment_status] => In-Progress
    [address_status] => confirmed
    [payer_status] => verified
    [first_name] => John
    [last_name] => Smith
    [payer_email] => buyer@paypalsandbox.com
    [payer_id] => TESTBUYERID01
    [address_name] => John Smith
    [address_country] => United States
    [address_country_code] => US
    [address_zip] => 95131
    [address_state] => CA
    [address_city] => San Jose
    [address_street] => 123 any street
    [business] => seller@paypalsandbox.com
    [receiver_email] => seller@paypalsandbox.com
    [receiver_id] => seller@paypalsandbox.com
    [residence_country] => US
    *[item_name] => something
    [item_number] => AK-1234
    [quantity] => 1
    [shipping] => 3.04
    [tax] => 2.02
    [mc_currency] => USD
    [mc_fee] => 0.44
    [mc_gross] => 12.34
    [mc_gross_1] => 12.34
    [txn_type] => web_accept
    [txn_id] => 508587247
    [notify_version] => 2.1
    [custom] => xyz123
    [invoice] => abc1234
    [test_ipn] => 1
    [verify_sign] => ASxxh5DTihPxlbiBeDFil0YFwAW1AN5GRy2dmbyIWH.Z.QuyYClruEJV
)
pending_transaction
[17-Jan-2021 04:20:31 UTC] FLPaypalHandler Sun Jan 17, 2021 4:20:31 : got response
Array
(
    [action] => fl_paypal
    [payment_type] => instant
    [payment_date] => 22:18:58 Jan 15, 2021 PST
    [payment_status] => Declined
    [address_status] => confirmed
    [payer_status] => verified
    [first_name] => John
    [last_name] => Smith
    [payer_email] => buyer@paypalsandbox.com
    [payer_id] => TESTBUYERID01
    [address_name] => John Smith
    [address_country] => United States
    [address_country_code] => US
    [address_zip] => 95131
    [address_state] => CA
    [address_city] => San Jose
    [address_street] => 123 any street
    [business] => seller@paypalsandbox.com
    [receiver_email] => seller@paypalsandbox.com
    [receiver_id] => seller@paypalsandbox.com
    [residence_country] => US
    [item_name] => something
    [item_number] => AK-1234
    [quantity] => 1
    [shipping] => 3.04
    [tax] => 2.02
    [mc_currency] => USD
    [mc_fee] => 0.44
    [mc_gross] => 12.34
    [mc_gross_1] => 12.34
    [txn_type] => web_accept
    [txn_id] => 508587247
    [notify_version] => 2.1
    [custom] => xyz123
    [invoice] => abc1234
    [test_ipn] => 1
    [verify_sign] => AXANmqRs8RbRuCGIHQ3o1YFRAPkMA2Sf54rA6KFIg4I9gb7X0h30AScu
)

https://github.com/paypal/ipn-code-samples/blob/master/php/example_usage_advanced.php
https://dashboard.ngrok.com/auth/your-authtoken
*/


/*
 * stripe notes (temporary)
 * https://stripe.com/docs/api/errors
 *
 * store the request id for all charges and modifications
 * use explicit versioning ( "stripe_version" => "2020-08-27") in all client creation requests
 *
 * https://stripe.com/docs/reports/balance-transaction-types for a list of payment status
 * https://stripe.com/docs/api/charges
 * and related https://stripe.com/docs/payments/accept-a-payment-charges
 * and for the front end js https://stripe.com/docs/js/elements_object/create_element?type=card
 *
 * save payment info ? https://stripe.com/docs/payments/save-during-payment
 *
 * https://stripe.com/docs/api/events
 *
 * https://stripe.com/docs/webhooks
 * https://stripe.com/docs/webhooks/test
 *
 * https://stripe.com/docs/api/webhook_endpoints  (we will be using these)
 *
 * https://stripe.com/docs/payments/payment-intents
 *
 * https://stripe.com/docs/payments/setup-intents (maybe)
 *
 * https://stripe.com/docs/refunds (future)
 *
 * https://stripe.com/docs/sources (alipay)  https://stripe.com/docs/sources/customers
 *
 * overview https://stripe.com/docs/payments/checkout
 *
 *   https://stripe.com/docs/payments/accept-a-payment?integration=checkout
 *
 *
 * https://stripe.com/docs/api/radar/early_fraud_warnings/object  setup hook, (should generate notice to admin)
 *
 * https://stripe.com/docs/testing more types of payment testing and cc card numbers
 *
 * https://stripe.com/docs/payments/payment-intents/verifying-status#checking-status-retrieve (future reference)
 *
 * aliPay
 *
 * https://stripe.com/docs/payments/alipay/accept-a-payment
 *

current unix time is 1614637475
stored time          1614637475000
 */








