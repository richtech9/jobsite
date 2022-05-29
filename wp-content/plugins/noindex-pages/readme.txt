=== Noindex Pages ===
Contributors: radgh
Donate link: https://paypal.me/radgh
Tags: pages, noindex, robots
Requires at least: 3.2
Tested up to: 4.4.1
Stable tag: 1.0.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Ask search engines not to index individual pages by checking an option in the publish post box.

== Description ==

This simple and lightweight plugin adds a "Hide from search engines" checkbox above the publish box to pages. By 
ticking this box, a meta tag will be placed into the `<head>` section of your page specifying that robots should
not index the page.

By default this only applies to pages. You may extend this functionality to posts or other custom post types with a small bit of code. See the FAQ's tab for further instructions.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/noindex-pages/`, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Edit a page, look for the checkbox above the "Publish" or "Update" button near the top-right.
1. (Optional) verify it works by viewing your page and then right-click, "View Source", and look for the meta tag with the name "robots" and value "noindex".
1. (Optional) Extend the functionality to posts or other custom post types by reading the FAQ's.

== Frequently Asked Questions ==

= Are there any advanced features, such as to block specific robots or add this code to archive/search pages? =

No, this is just a simple plugin.

= Can this be used for custom post types or posts? =

Yes, with a bit of PHP. Just use the following code in your functions.php, which will add support for "post" and "product":

`function noindex_for_cpts( $post_types ) {
	return $post_types + array( "post", "product" );
}
add_filter('noindex-pages-post-types', 'noindex_for_cpts');`

== Screenshots ==

1. The publish box when editing a page, the added checkbox is highlighted.
1. The source code for the page, the added meta tag is highlighted.

== Changelog ==

= 1.0.1 =
* Improved screenshot quality for wordpress.org
* Corrected "Tested up to" version number
* Corrected "Requires at least" version number

= 1.0.0 =
* First release
* Pre-release version notes can be found on Github

== Upgrade Notice ==

Nothing to worry about!