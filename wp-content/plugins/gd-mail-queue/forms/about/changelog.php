<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="d4p-group d4p-group-changelog">
    <h3><?php _e("Version", "gd-mail-queue"); ?> 3</h3>
    <div class="d4p-group-inner">
        <h4>Version: 3.6 / june 20 2020</h4>
        <ul>
            <li><strong>new</strong> dashboard widget to show latest email sending errors</li>
            <li><strong>new</strong> options to set sleep periods for batch and each email</li>
            <li><strong>new</strong> queue function now has support for 'from' field</li>
            <li><strong>new</strong> support for email types detection for Rank Math plugin</li>
            <li><strong>edit</strong> various improvements to queue test tool</li>
        </ul>

        <h4>Version: 3.5.1 / june 10 2020</h4>
        <ul>
            <li><strong>edit</strong> updated database schema due to the problem with column lengths</li>
            <li><strong>fix</strong> regression related to the cron job interval saving</li>
        </ul>

        <h4>Version: 3.5 / june 9 2020</h4>
        <ul>
            <li><strong>new</strong> phpmailer smtp services listed on same settings page</li>
            <li><strong>new</strong> support for email types detection for WP Members plugin</li>
            <li><strong>new</strong> bulk retry option in the email log for failed emails</li>
            <li><strong>new</strong> auto requeue locked emails not sent due to the server error</li>
            <li><strong>new</strong> reorganization of the plugin settings panels</li>
            <li><strong>new</strong> using SCSS file as a base for the CSS file</li>
            <li><strong>new</strong> reorganized CSS and JS files</li>
            <li><strong>edit</strong> improved queue box on the plugin dashboard with more information</li>
            <li><strong>edit</strong> improved htmlfy main method with additional arguments</li>
            <li><strong>edit</strong> improved bulk operation messages and counts displayed</li>
            <li><strong>edit</strong> various improvements to the JavaScript</li>
            <li><strong>edit</strong> retried emails have new retry status</li>
            <li><strong>edit</strong> d4pLib 2.8.10</li>
        </ul>

        <h4>Version: 3.4.2 / april 7 2020</h4>
        <ul>
            <li><strong>new</strong> tested with PHP 7.4</li>
            <li><strong>edit</strong> d4pLib 2.8.5</li>
            <li><strong>fix</strong> minor issue with with the PHP 7.4 deprecations</li>
        </ul>

        <h4>Version: 3.4.1 / november 2 2019</h4>
        <ul>
            <li><strong>fix</strong> email type detection related to the GD Topic Polls plugin</li>
        </ul>

        <h4>Version: 3.4 / september 28 2019</h4>
        <ul>
            <li><strong>new</strong> validate email object for missing attachments before queue processing</li>
            <li><strong>new</strong> color coded log rows for the failed and queued emails</li>
            <li><strong>new</strong> email log: action to retry sending emails that failed previously</li>
            <li><strong>edit</strong> various updates and expansions to the universal core email class</li>
            <li><strong>edit</strong> queue test is now sending proper from and from name values</li>
            <li><strong>edit</strong> various updates to the plugin readme file including more FAQ entries</li>
            <li><strong>edit</strong> improved queue error detection that happens before the sending attempt</li>
            <li><strong>edit</strong> few small updates to the emails log processing</li>
            <li><strong>edit</strong> d4pLib 2.7.8</li>
            <li><strong>fix</strong> adding to log can set wrong status for emails sent through queue</li>
            <li><strong>fix</strong> in some cases reply_to value doesn't get stored in the queue</li>
            <li><strong>fix</strong> some minor problems with logging the direct emails</li>
            <li><strong>fix</strong> add to log database method doesn't log message value</li>
        </ul>

        <h4>Version: 3.3 / july 22 2019</h4>
        <ul>
            <li><strong>new</strong> improved detection of the plain text email content</li>
            <li><strong>new</strong> option to control detection of the plain text email content</li>
            <li><strong>new</strong> option to fix the plugin content type when using HTML</li>
            <li><strong>new</strong> various additional new actions and filters for more control</li>
            <li><strong>new</strong> buddypress: force use of the wp_mail to send plain text emails only</li>
            <li><strong>edit</strong> updated plugin icon for the WordPress menus</li>
            <li><strong>edit</strong> remove some unused PHPMailer parameters from mirroring</li>
            <li><strong>edit</strong> d4pLib 2.7.5</li>
            <li><strong>fix</strong> saving failed message in log fails if message is too long</li>
        </ul>

        <h4>Version: 3.2 / june 26 2019</h4>
        <ul>
            <li><strong>new</strong> mail type detection: support for GD Topic Polls</li>
            <li><strong>new</strong> phpmailer updated to use core email class for email building</li>
            <li><strong>edit</strong> various updates to readme and extra plugin information</li>
            <li><strong>edit</strong> d4pLib 2.7.3</li>
        </ul>

        <h4>Version: 3.1 / june 18 2019</h4>
        <ul>
            <li><strong>new</strong> universal core email class for various operations</li>
            <li><strong>new</strong> set reply to email and name globaly in wp_mail</li>
            <li><strong>new</strong> htmlfy expanded with the website tagline tag</li>
            <li><strong>new</strong> htmlfy expanded with the website link tag</li>
            <li><strong>edit</strong> queue function: sets char set and content type if missing</li>
            <li><strong>edit</strong> queue test now sets char set to UTF-8</li>
            <li><strong>edit</strong> various minor tweaks and improvements</li>
            <li><strong>edit</strong> overall improved detection of the HTML emails</li>
            <li><strong>edit</strong> d4pLib 2.7.2</li>
            <li><strong>fix</strong> email log: HTML tag displayed for non-HTML emails</li>
            <li><strong>fix</strong> queue function: not setting the content type for the email</li>
            <li><strong>fix</strong> dashboard: incorrect status for the mailer intercept</li>
            <li><strong>fix</strong> from name global: invalid check for changing From Name</li>
        </ul>

        <h4>Version: 3.0.1 / june 15 2019</h4>
        <ul>
            <li><strong>edit</strong> fully updated about page for the version 3.0</li>
            <li><strong>edit</strong> various updates to the settings labels and information</li>
            <li><strong>fix</strong> missing core engines registration action point</li>
            <li><strong>fix</strong> missing PHPMailer services registration action point</li>
        </ul>

        <h4>Version: 3.0 / june 14 2019</h4>
        <ul>
            <li><strong>new</strong> option to pause email sending throug wp_mail</li>
            <li><strong>new</strong> plugin dashboard completly reorganized</li>
            <li><strong>new</strong> plugin dashboard: wp mail status box</li>
            <li><strong>new</strong> plugin dashboard: mail log status box</li>
            <li><strong>new</strong> database tables for emails, log and email/log relationship</li>
            <li><strong>new</strong> log emails send by wp_mail, queue or both</li>
            <li><strong>new</strong> emails log panel with overview of all logged emails</li>
            <li><strong>new</strong> emails log panel with option to delete from log</li>
            <li><strong>new</strong> emails log panel with popup dialog for email preview</li>
            <li><strong>new</strong> daily maintenance with support for log cleanup</li>
            <li><strong>new</strong> fake PHPMailer class now implements magic methods</li>
            <li><strong>new</strong> mirror PHPMailer class captures more information</li>
            <li><strong>new</strong> detect email type: support for WP error recovery mode email</li>
            <li><strong>new</strong> email preheader tag: choose the value to generate</li>
            <li><strong>new</strong> filter that can be used to pause wp_mail sending</li>
            <li><strong>new</strong> filter that can be used to control queue descision</li>
            <li><strong>new</strong> additional filters and actions for various things</li>
            <li><strong>edit</strong> additional information on the plugin dashboard</li>
            <li><strong>edit</strong> improved plugin settings organization</li>
            <li><strong>edit</strong> reset tool support for clearing the email log tables</li>
            <li><strong>edit</strong> d4pLib 2.7.1</li>
            <li><strong>fix</strong> email preheader tag set to wrong value</li>
        </ul>
    </div>
</div>

<div class="d4p-group d4p-group-changelog">
    <h3><?php _e("Version", "gd-mail-queue"); ?> 2</h3>
    <div class="d4p-group-inner">
        <h4>Version: 2.1.2 / may 30 2019</h4>
        <ul>
            <li><strong>fix</strong> wrong links for the update and install notifications in network mode</li>
            <li><strong>fix</strong> wrong admin menu action used when in the network mode</li>
        </ul>

        <h4>Version: 2.1.1 / may 26 2019</h4>
        <ul>
            <li><strong>fix</strong> wrong database table name for the queue cleanup process</li>
        </ul>

        <h4>Version: 2.1 / may 22 2019</h4>
        <ul>
            <li><strong>new</strong> option to use flexible limit when sending queued emails</li>
            <li><strong>new</strong> action run after each email has been sent through queue</li>
            <li><strong>new</strong> filter that can be used to pause the queue processing</li>
            <li><strong>new</strong> option on advanced settings panel to pause the queue processing</li>
            <li><strong>new</strong> export tool: select what to export: settings and/or statistics</li>
            <li><strong>edit</strong> export tool: improved import of settings from file as proper array</li>
            <li><strong>edit</strong> dashboard: improved display of the queue related information</li>
            <li><strong>edit</strong> improved the descriptions for various plugin settings</li>
            <li><strong>edit</strong> d4pLib 2.6.4</li>
            <li><strong>fix</strong> export tool: statistics data problem caused by the JSON import</li>
            <li><strong>fix</strong> export tool: wrong file name for the plugin settings export JSON file</li>
        </ul>

        <h4>Version: 2.0.1 / may 8 2019</h4>
        <ul>
            <li><strong>edit</strong> check if the template file exists before attempting to load</li>
            <li><strong>fix</strong> display of the last queue timestamp conversion error</li>
            <li><strong>fix</strong> default option for the HTML template was wrong</li>
        </ul>

        <h4>Version: 2.0 / may 6 2019</h4>
        <ul>
            <li><strong>new</strong> support for queue email send engines</li>
            <li><strong>new</strong> email send engine: phpmailer</li>
            <li><strong>new</strong> phpmailer support for using SMTP for sending</li>
            <li><strong>new</strong> set from email and name globaly in wp_mail</li>
            <li><strong>new</strong> additional information on the dashboard for queue</li>
            <li><strong>new</strong> tools to test email sending and adding to queue</li>
            <li><strong>new</strong> detect email type for emails sent by BuddyPress</li>
            <li><strong>new</strong> includes defuse encryption library</li>
            <li><strong>edit</strong> few changes in some of the filters and actions</li>
            <li><strong>edit</strong> better organization of the plugin settings panels</li>
            <li><strong>edit</strong> improvements to the function for adding to queue</li>
            <li><strong>edit</strong> various loading and initialization improvements</li>
            <li><strong>fix</strong> few issues when preparing email to send in queue</li>
            <li><strong>fix</strong> few problems with function for adding to queue</li>
            <li><strong>fix</strong> plugin settings export not working</li>
        </ul>
    </div>
</div>

<div class="d4p-group d4p-group-changelog">
    <h3><?php _e("Version", "gd-mail-queue"); ?> 1</h3>
    <div class="d4p-group-inner">
        <h4>Version: 1.0 / may 2 2019</h4>
        <ul>
            <li><strong>new</strong> first official version</li>
        </ul>
    </div>
</div>
