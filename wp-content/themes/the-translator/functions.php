<?php
/*
Author: Wordprax
URL: http://www.wordprax.com/
*/

ob_start();


if (function_exists('add_theme_support'))
{
    add_theme_support('menus');
    add_theme_support( 'post-thumbnails', array( 'post', 'page') );

    load_theme_textdomain('translator', get_template_directory() . '/languages');
}



require_once ('fl_theme_setup.php');
FLThemeSetup::fl_require();


add_action('init', 'translator_header_scripts');
function translator_header_scripts(){
    FLThemeSetup::always_enqueue();
}

add_action( 'admin_enqueue_scripts', 'translator_admin_header_scripts' );
function translator_admin_header_scripts(){
    FLThemeSetup::enqueue_backend();
}


add_action('wp_enqueue_scripts', 'translator_styles');
function translator_styles()
{
    FLThemeSetup::enqueue_frontend();

}

add_action('init', 'register_trans_menu');
function register_trans_menu()
{
    register_nav_menus(array(
        'header-menu' => __('Header Menu', 'translator'),
        'sidebar-menu' => __('Sidebar Menu', 'translator'),
        'extra-menu' => __('Extra Menu', 'translator')
    ));
}

add_filter( 'template_include', function($template) {
    if (strpos($template,'themes/the-translator/index.php') !== false) {
        $template_to_use = get_template_directory().'/templates/home-tpl.php';
    } else {
        $template_to_use = $template;
    }
    return $template_to_use;
}, 998 );

add_filter('wp_nav_menu_args', 'my_wp_nav_menu_args');
function my_wp_nav_menu_args($args = [])
{
    $args['container'] = false;
    return $args;
}

function my_css_attributes_filter($var)
{
    return is_array($var) ? array() : '';
}



add_filter('body_class', 'add_slug_to_body_class');
function add_slug_to_body_class($classes)
{
    global $post;
    if (is_home()) {
        $key = array_search('blog', $classes);
        if ($key > -1) {
            unset($classes[$key]);
        }
    } elseif (is_page()) {
        $classes[] = sanitize_html_class($post->post_name);
    } elseif (is_singular()) {
        $classes[] = sanitize_html_class($post->post_name);
    }

    return $classes;
}

if (function_exists('register_sidebar'))
{
    register_sidebar(array(
        'name' => __('Widget Area 1', 'translator'),
        'description' => __('Description for this widget-area...', 'translator'),
        'id' => 'widget-area-1',
        'before_widget' => '<div id="%1$s" class="%2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>'
    ));
    register_sidebar(array(
        'name' => __('Languages Switcher', 'translator'),
        'description' => __('Set Language Switcher ...', 'translator'),
        'id' => 'language_st',
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '<h3 style="display:none;">',
        'after_title' => '</h3>'
    ));
}

function html5wp_index($length)//html5wp_excerpt('html5wp_index');
{
    will_do_nothing($length);
    return 20;
}

function html5wp_excerpt($length_callback = '', $more_callback = '')
{
    if (function_exists($length_callback)) {
        add_filter('excerpt_length', $length_callback);
    }
    if (function_exists($more_callback)) {
        add_filter('excerpt_more', $more_callback);
    }
    $output = get_the_excerpt();
    $output = apply_filters('wptexturize', $output);
    $output = apply_filters('convert_chars', $output);
    $output = '<p>' . $output . '</p>';
    echo $output;
}

function html5_blank_view_article($more)
{
    global $post;
    will_do_nothing($more);
    return /** @lang text */
        '... <a class="view-article" href="' . get_permalink($post->ID) . '">' . __('View Article', 'translator') . '</a>';
}

function remove_admin_bar()
{
    return false;
}

function html5_style_remove($tag)
{
    return preg_replace('~\s+type=["\'][^"\']++["\']~', '', $tag);
}

function remove_thumbnail_dimensions( $html )
{
    $html = preg_replace('/(width|height)=\"\d*\"\s/', "", $html);
    return $html;
}



remove_action('wp_head', 'feed_links_extra', 3); // Display the links to the extra feeds such as category feeds
remove_action('wp_head', 'feed_links', 2); // Display the links to the general feeds: Post and Comment Feed
remove_action('wp_head', 'rsd_link'); // Display the link to the Really Simple Discovery service endpoint, EditURI link
remove_action('wp_head', 'wlwmanifest_link'); // Display the link to the Windows Live Writer manifest file.
remove_action('wp_head', 'index_rel_link'); // Index link
remove_action('wp_head', 'parent_post_rel_link', 10); // Prev link
remove_action('wp_head', 'start_post_rel_link', 10); // Start link
remove_action('wp_head', 'adjacent_posts_rel_link', 10); // Display relational links for the posts adjacent to the current post.
remove_action('wp_head', 'wp_generator'); // Display the XHTML generator that is generated on the wp_head hook, WP version
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
remove_action('wp_head', 'rel_canonical');
remove_action('wp_head', 'wp_shortlink_wp_head', 10);

add_filter('widget_text', 'do_shortcode'); // Allow shortcodes in Dynamic Sidebar
add_filter('widget_text', 'shortcode_unautop'); // Remove <p> tags in Dynamic Sidebars (better!)


add_filter('the_excerpt', 'shortcode_unautop'); // Remove auto <p> tags in Excerpt (Manual Excerpts only)
add_filter('the_excerpt', 'do_shortcode'); // Allows Shortcodes to be executed in Excerpt (Manual Excerpts only)
add_filter('excerpt_more', 'html5_blank_view_article'); // Add 'View Article' button instead of [...] for Excerpts
add_filter('show_admin_bar', 'remove_admin_bar'); // Remove Admin bar
add_filter('style_loader_tag', 'html5_style_remove'); // Remove 'text/css' from enqueued stylesheet
add_filter('post_thumbnail_html', 'remove_thumbnail_dimensions', 10); // Remove width and height dynamic attributes to thumbnails

remove_filter('the_excerpt', 'wpautop'); // Remove <p> tags from Excerpt altogether
add_filter('wp_nav_menu_items','wpml_add_custom_menu', 10, 2);
function wpml_add_custom_menu( $items, $args ) {
    will_do_nothing($args);
    //code-notes  when disabling the WPML Multilingual CMS the icl_get_languages goes away
    if (function_exists('icl_get_languages') ) {
        /** @noinspection PhpDeprecationInspection */
        $languages = icl_get_languages('skip_missing=1');
    } else {
        $languages = [];
    }

    if( count( $languages ) > 1 )  {
        $dd = '<ul class="sub-menu">';
        foreach($languages as $language) {
            if(!$language['active']) {
                $dd .= /** @lang text */
                    '<li><a href="'.$language['url'].'">'.$language['native_name'].'</a></li>';
            }
        }
        $dd .= '</ul>';

        foreach($languages as $language) {
            if($language['active'])
                $items .= /** @lang text */
                    '<li class="menu-item menu-item-type-post_type menu-item-object-page '.
                    'current-menu-item page_item page-item-2965 current_page_item menu-item-has-children">'.
                    '<a href="'.$language['url'].'">'.$language['native_name'].
                    ' <span>'.
                    '<svg version="1.1" class="menu_icon" '.
                    'xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" '.
                    'x="0px" y="0px" width="7.998px" height="4.707px" viewBox="0 0 7.998 4.707" '.
                    'enable-background="new 0 0 7.998 4.707" xml:space="preserve">'.
                    '<rect x="1.854" y="-0.475" transform="matrix(0.7071 -0.7071 0.7071 0.7071 -0.9747 2.3534)" '.
                    'fill-rule="evenodd" clip-rule="evenodd" width="1" height="5.657"></rect>'.
                    '<rect x="2.817" y="1.854" transform="matrix(0.7071 -0.7071 0.7071 0.7071 -0.0107 4.6811)"'.
                    ' fill-rule="evenodd" clip-rule="evenodd" width="5.657" height="1"></rect>'.
                    '</svg></span></a>'.$dd.'</li>';
        }

    }

    return $items;
}




remove_filter( 'the_content', 'wpautop' );
remove_filter( 'the_excerpt', 'wpautop' );






$ejabberd = new EjabberdWrapper();

