
-- tables to delete as they are no longer used (deleted plugins or old code)
/*

old code:
wp_order_price_per_word
wp_payforview_seal
wp_referral_history

old plugins:
wp_ewwwio_images
wp_icl_content_status
wp_icl_core_status
wp_icl_flags
wp_icl_languages
wp_icl_languages_translations
wp_icl_locale_map
wp_icl_message_status
wp_icl_node
wp_icl_reminders
wp_icl_string_packages
wp_icl_string_pages
wp_icl_string_positions
wp_icl_string_status
wp_icl_string_translations
wp_icl_string_urls
wp_icl_strings
wp_icl_translate
wp_icl_translate_job
wp_icl_translation_batches
wp_icl_translation_status
wp_icl_translations
 */


/*
The following wp_options keys should be trimmed on out as below
 */
UPDATE wp_options set option_value = '',autoload = 'no' WHERE option_name like '%freelinguist_cron%' OR option_name like 'freelinguist_rebuild_%';
delete from wp_options where option_name like 'wpml_%';
delete from wp_options where option_name in ('wp_installer_settings','prisna-google-website-translator-settings');