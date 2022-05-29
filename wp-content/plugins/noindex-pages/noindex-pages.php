<?php
/*
Plugin Name: Noindex Pages
Version:     1.0.1
Plugin URI:  http://radgh.com/
Description: Ask search engines not to index individual pages by checking an option in the publish post box.
Author:      Radley Sustaire
Author URI:  mailto:radleygh@gmail.com
License:     GPL2
*/

/*
GNU GENERAL PUBLIC LICENSE

A WordPress plugin that allows you to mark pages with an option to hide 
from search engines, by adding a noindex meta tag to the single page's <head>
Copyright (C) 2015 Radley Sustaire

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>
*/

if( !defined( 'ABSPATH' ) ) exit;

// Filterable list of post types which will show the checkbox and add the meta tag
function noinp_post_types() {
	return apply_filters( 'noindex-pages-post-types', array( 'page' ) );
}

// Add our stylesheet only to the "Add/edit post" screens
function noinp_enqueue_css() {
	$screen = get_current_screen();
	
	if ( $screen->base == 'post' ) {
		wp_enqueue_style( 'noinp', plugins_url('noindex-pages.css', __FILE__) );
	}
}
add_action( 'admin_enqueue_scripts', 'noinp_enqueue_css' );

// Add the field to the Publish box displayed on any post type
function noinp_display_meta_checkbox() {
	global $post;
	if ( empty($post->ID) ) return;

	$noindex_enabled = get_post_meta( $post->ID, 'noindex', true );
	?>
<div class="misc-pub-section misc-noindex">
	<input type="checkbox" name="noindex_page" id="noindex_page" <?php checked($noindex_enabled); ?> />
	<label for="noindex_page">Hide from search engines</label>
</div>
	<?php
}
add_action( 'post_submitbox_misc_actions', 'noinp_display_meta_checkbox', 3 );

// Capture the "Save Post" event, and if the post specified is of type "post", save the ejournal_file checkbox state as meta-data
function noinp_save_meta( $post_id ) {
	if ( in_array( get_post_type( $post_id ), noinp_post_types() ) ) {
		$noindex_pages = empty($_REQUEST['noindex_page']) ? 0 : 1;
		
		if ( $noindex_pages ) {
			update_post_meta( $post_id, 'noindex', 1 );
		}else{
			delete_post_meta( $post_id, 'noindex' );
		}
	}
}
add_action( 'save_post', 'noinp_save_meta' );

// Display the meta tag
function noinp_display_meta_tag() {
	if ( !is_singular() ) return;
	
	$post_type = get_post_type();
	
	if ( $post_type && in_array( $post_type, noinp_post_types() ) ) {
		$noindex = get_post_meta( get_the_ID(), 'noindex', true );
		
		if ( (int) $noindex === 1 ) {
			echo '<meta name="robots" content="noindex" />' . "\n";
		}
	}
}
add_action( 'wp_head', 'noinp_display_meta_tag' );